<?php

namespace LePhare\ImportBundle\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Faker\Factory;
use LePhare\Import\Event\ImportEvent;
use LePhare\Import\ImportConfiguration;
use LePhare\Import\ImportEvents;
use LePhare\ImportBundle\EventSubscriber\EmailReportSubscriber;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @covers \LePhare\ImportBundle\EventSubscriber\EmailReportSubscriber
 *
 * @uses \LePhare\Import\Event\ImportEvent
 * @uses \LePhare\Import\ImportConfiguration
 */
class EmailReportSubscriberTest extends TestCase
{
    use ProphecyTrait;
    /** @var ObjectProphecy<MailerInterface> */
    private ObjectProphecy $mailer;

    /** @var ObjectProphecy<Logger> */
    private ObjectProphecy $logger;

    private Processor $configProcessor;

    public function setUp(): void
    {
        $this->configProcessor = new Processor();
        $this->logger = $this->prophesize(Logger::class);
        $this->logger->getHandlers()->willReturn([
        ]);
        $this->mailer = $this->prophesize(MailerInterface::class);
    }

    private function createSubscriber(array $emailRecipients = []): EmailReportSubscriber
    {
        return new EmailReportSubscriber($this->mailer->reveal(), $emailRecipients);
    }

    public function testInstanceOfEventSubscriberInterface(): void
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->createSubscriber());
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame([
            ImportEvents::POST_EXECUTE => 'onPostExecute',
            ImportEvents::EXCEPTION => 'onException',
        ], EmailReportSubscriber::getSubscribedEvents());
    }

    private function processConfiguration(array $config): Collection
    {
        $f = Factory::create();

        $config['name'] ??= $f->word();
        $config['source_dir'] = $config['name'] ?? $f->file();
        $config['resources'] ??= [
            'foo' => [
                'tablename' => 'bar',
            ],
        ];

        return new ArrayCollection(
            $this->configProcessor->processConfiguration(
                new ImportConfiguration(),
                ['lephare_import' => $config]
            )
        );
    }

    private function createImportEvent(array $config): ImportEvent
    {
        return new ImportEvent(
            $this->processConfiguration($config),
            $this->logger->reveal()
        );
    }

    /**
     * @uses \LePhare\Import\Event\ImportEvent
     * @uses \LePhare\Import\ImportConfiguration
     */
    public function testPostExecuteWithoutEmailRecipients(): void
    {
        $f = Factory::create();
        $event = $this->createImportEvent([
            'email_report' => [
                'email_from' => $f->email(),
                'recipients' => [],
            ],
        ]);
        $this->mailer->send(Argument::cetera())->shouldNotBeCalled();
        $this->createSubscriber()->onPostExecute($event);
    }

    /**
     * @uses \LePhare\Import\Event\ImportEvent
     * @uses \LePhare\Import\ImportConfiguration
     */
    public function testOnExceptionWithoutEmailRecipients(): void
    {
        $f = Factory::create();

        $event = $this->createImportEvent([
            'email_report' => [
                'email_from' => $f->email(),
                'recipients' => [],
            ],
        ]);
        $this->mailer->send(Argument::cetera())->shouldNotBeCalled();
        $this->createSubscriber()->onException($event);
    }

    /**
     * @uses \LePhare\Import\Event\ImportEvent
     * @uses \LePhare\Import\ImportConfiguration
     */
    public function testPostExecuteWithEmailRecipients(): void
    {
        $f = Factory::create();
        $event = $this->createImportEvent([
            'email_report' => [
                'email_from' => $f->email(),
                'recipients' => [
                    $f->email(),
                    $f->email(),
                    $f->email(),
                ],
            ],
        ]);
        $this->mailer->send(Argument::type(Email::class))->shouldBeCalled();
        $this->createSubscriber()->onPostExecute($event);
    }

    public function provideEmails(): iterable
    {
        $f = Factory::create();

        yield 'array of emails' => [
            [
                $f->email(),
                $f->email(),
                $f->email(),
            ],
        ];

        yield 'single email' => [$f->email()];
    }

    /**
     * @param array|string $recipientsLists
     *
     * @dataProvider provideEmails
     *
     * @uses \LePhare\Import\Event\ImportEvent
     * @uses \LePhare\Import\ImportConfiguration
     */
    public function testPostExecuteWithEmailRecipientsList($recipientsLists): void
    {
        $f = Factory::create();
        $extraEmail = $f->email();
        $expectedEmails = array_map([Address::class, 'create'], array_merge([$extraEmail], is_array($recipientsLists) ? $recipientsLists : [$recipientsLists]));

        $event = $this->createImportEvent([
            'email_report' => [
                'email_from' => $f->email(),
                'recipients' => [
                    $extraEmail,
                    'my_list',
                ],
            ],
        ]);
        $this->mailer->send(Argument::that(function ($arg) use ($expectedEmails): bool {
            $this->assertInstanceOf(Email::class, $arg);
            $this->assertEquals($expectedEmails, $arg->getTo());

            return true;
        }))->shouldBeCalled();
        $this->createSubscriber([
            'my_list' => $recipientsLists,
        ])->onPostExecute($event);
    }

    /**
     * @uses \LePhare\Import\Event\ImportEvent
     * @uses \LePhare\Import\ImportConfiguration
     */
    public function testOnExceptionWithEmailRecipients(): void
    {
        $f = Factory::create();

        $event = $this->createImportEvent([
            'email_report' => [
                'email_from' => $f->email(),
                'recipients' => [
                    $f->email(),
                    $f->email(),
                    $f->email(),
                ],
            ],
        ]);
        $this->mailer->send(Argument::type(Email::class))->shouldBeCalled();
        $this->createSubscriber()->onException($event);
    }
}
