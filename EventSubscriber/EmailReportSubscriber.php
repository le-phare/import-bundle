<?php

namespace LePhare\ImportBundle\EventSubscriber;

use LePhare\Import\Event\ImportEvent;
use LePhare\Import\ImportEvents;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailReportSubscriber implements EventSubscriberInterface
{
    private MailerInterface $mailer;

    /** @var mixed[]|mixed[][] */
    private $emailReportRecipients;

    public function __construct(MailerInterface $mailer, array $emailReportRecipients)
    {
        $this->mailer = $mailer;
        $this->emailReportRecipients = $emailReportRecipients;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImportEvents::POST_EXECUTE => 'onPostExecute',
            ImportEvents::EXCEPTION => 'onException',
        ];
    }

    private function getEmailReportConfig(ImportEvent $event, $entry)
    {
        $config = $event->getConfig();
        $emailConfig = $config['email_report'];

        return $emailConfig[$entry];
    }

    private function getEmailRecipients(ImportEvent $event)
    {
        /** @var string[] configRecipients */
        $configRecipients = $this->getEmailReportConfig($event, 'recipients');
        $recipients = $configRecipients;

        foreach ($configRecipients as $recipient) {
            // Replace recipients by lephare_import.email_report.recipients lists
            if (isset($this->emailReportRecipients[$recipient])) {
                if (!is_array($this->emailReportRecipients[$recipient])) {
                    $this->emailReportRecipients[$recipient] = [$this->emailReportRecipients[$recipient]];
                }

                // remove list from recipients and replace by values
                unset($recipients[$recipient]);
                $recipients = array_filter($recipients, fn ($r) => $recipient !== $r);
                $recipients = array_unique(array_merge($recipients, $this->emailReportRecipients[$recipient]));
            }
        }

        return $recipients;
    }

    private function buildEmailSubject(ImportEvent $event, string $statusLabel)
    {
        $configSubjectPattern = $this->getEmailReportConfig($event, 'subject_pattern');

        // Replace %status% placeholder
        $subject = str_replace('%status%', $statusLabel, $configSubjectPattern);

        // Replace optionnal %name% placeholder
        $config = $event->getConfig();

        return str_replace('%name%', $config['name'] ?: $config['identifier'], $subject);
    }

    private function sendReportEmail(ImportEvent $event, array $recipients, string $subject, ?string $content)
    {
        $email = new Email();
        $configTemplate = $this->getEmailReportConfig($event, 'email_template');
        if (null !== $configTemplate) {
            $email = new TemplatedEmail();
            $email->htmlTemplate($configTemplate);
            $email->context([
                'content' => $content,
            ]);
        } else {
            $email->text($content);
        }

        $email
            ->from(Address::create($this->getEmailReportConfig($event, 'email_from')))
            ->subject($subject)
        ;
        foreach ($recipients as $recipient) {
            $email->addTo(Address::create($recipient));
        }

        $this->mailer->send($email);
    }

    public function onPostExecute(ImportEvent $event)
    {
        $recipients = $this->getEmailRecipients($event);
        if (empty($recipients)) {
            return;
        }

        $subject = $this->buildEmailSubject($event, 'SUCCESS');

        $content = null;
        if (null !== ($logFile = $event->getLogFile())) {
            $content = file_get_contents($logFile);
        }

        $this->sendReportEmail($event, $recipients, $subject, $content);
    }

    public function onException(ImportEvent $event)
    {
        $recipients = $this->getEmailRecipients($event);
        if (empty($recipients)) {
            return;
        }

        $subject = $this->buildEmailSubject($event, 'ERROR');
        $content = null;
        if (null !== ($logFile = $event->getLogFile())) {
            $content = file_get_contents($logFile);
        }

        $this->sendReportEmail($event, $recipients, $subject, $content);
    }
}
