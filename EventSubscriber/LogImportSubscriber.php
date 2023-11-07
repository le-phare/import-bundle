<?php

namespace LePhare\ImportBundle\EventSubscriber;

use LePhare\Import\Event\ImportEvent;
use LePhare\Import\Handler\RotatingFileHandler;
use LePhare\Import\ImportEvents;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogImportSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ImportEvents::PRE_EXECUTE => 'onImportPreExecute',
        ];
    }

    public function onImportPreExecute(ImportEvent $event)
    {
        if (null === $event->getConfig()['log_dir'] || !$event->getLogger() instanceof Logger) {
            return;
        }

        $logFile = sprintf('%s/%s.log',
            $event->getConfig()['log_dir'],
            $event->getConfig()['name']
        );

        // Save a PID related log file
        $handler = new RotatingFileHandler($logFile, 30, Logger::INFO);
        $handler->setFilenameFormat('{filename}-{date}-{pid}', 'Y-m-dTHis');
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $event->getLogger()->pushHandler($handler);
    }
}
