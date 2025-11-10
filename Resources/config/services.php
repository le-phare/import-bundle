<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use LePhare\ImportBundle\DependencyInjection\Compiler\ImportLoaderPass;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('lephare.import', \LePhare\Import\Import::class)
        ->public()
        ->tag('monolog.logger', ['channel' => 'import'])
        ->args([service('database_connection'), service('event_dispatcher'), service('lephare_import.strategy_repository'), service('lephare_import.load_strategy_repository'), service('lephare_import.configuration'), service('logger')->nullOnInvalid()]);

    $services->set('lephare_import.log_import_subscriber', \LePhare\ImportBundle\EventSubscriber\LogImportSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set('lephare_import.maker', \LePhare\ImportBundle\Maker\MakeImport::class)
        ->tag('maker.command');

    $services->set('lephare_import.command', \LePhare\ImportBundle\Command\ImportCommand::class)
        ->arg('$import', service(\LePhare\Import\ImportInterface::class))
        ->arg('$lockFactory', service('lock.default.factory'))
        ->tag('console.command');

    $services->set('lephare_import.configuration', \LePhare\Import\ImportConfiguration::class)
        ->args(['']);

    $services->set('lephare_import.strategy_repository', \LePhare\Import\Strategy\StrategyRepository::class);

    $services->set('lephare_import.strategy.insert_or_update', \LePhare\Import\Strategy\InsertOrUpdateStrategy::class)
        ->tag('import.strategy')
        ->args([service('database_connection')]);

    $services->set('lephare_import.strategy.insert_ignore', \LePhare\Import\Strategy\InsertIgnoreStrategy::class)
        ->tag('import.strategy')
        ->args([service('database_connection')]);

    $services->set('lephare_import.strategy.insert', \LePhare\Import\Strategy\InsertStrategy::class)
        ->tag('import.strategy')
        ->args([service('database_connection')]);

    $services->set('faros_import.strategy.update', \LePhare\Import\Strategy\UpdateStrategy::class)
        ->tag('import.strategy')
        ->args([service('database_connection')]);

    $services->set('lephare_import.email_report_subscriber', \LePhare\ImportBundle\EventSubscriber\EmailReportSubscriber::class)
        ->tag('kernel.event_subscriber')
        ->args([service(\Symfony\Component\Mailer\MailerInterface::class), '%lephare_import.email_report.recipients%']);

    $services->set('lephare_import.archive_subscriber', \LePhare\Import\Subscriber\ArchiveAndQuarantineSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set('lephare_import.validate_headers_subscriber', \LePhare\Import\Subscriber\ValidateCSVHeadersSubscriber::class)
        ->tag('kernel.event_subscriber');

    $services->set('lephare_import.load_strategy_repository', \LePhare\Import\LoadStrategy\LoadStrategyRepository::class);

    $services->set('lephare_import.load_strategy.load_newest_first', \LePhare\Import\LoadStrategy\LoadNewestFirstStrategy::class)
        ->tag('import.load_strategy');

    $services->set('lephare_import.load_strategy.load_oldest_first', \LePhare\Import\LoadStrategy\LoadOldestFirstStrategy::class)
        ->tag('import.load_strategy');

    $services->set('lephare_import.load_strategy.load_alphabetically', \LePhare\Import\LoadStrategy\LoadAlphabeticallyStrategy::class)
        ->tag('import.load_strategy');

    $services->set('lephare_import.load_strategy.load_reverse_alphabetically', \LePhare\Import\LoadStrategy\LoadReverseAlphabeticallyStrategy::class)
        ->tag('import.load_strategy');

    $services->set('lephare_import.credentials', \LePhare\Import\Configuration\Credentials::class)
        ->factory([\LePhare\Import\Configuration\Credentials::class, 'fromDatabaseUrl'])
        ->arg('$url', '')
    ;

    $services->alias(\LePhare\Import\Configuration\CredentialsInterface::class, 'lephare_import.credentials');
    $services->alias(\LePhare\Import\ImportInterface::class, 'lephare.import');

    $services->set('lephare_import.csv.loader', \LePhare\Import\Load\CsvLoader::class)
        ->args([service('database_connection'), service('lephare_import.credentials')])
        ->tag(ImportLoaderPass::LOADER_TAG)
    ;
    $services->set('lephare_import.text.loader', \LePhare\Import\Load\TextLoader::class)
        ->args([service('database_connection'), service('lephare_import.credentials')])
        ->tag(ImportLoaderPass::LOADER_TAG)
    ;
    $services->set('lephare_import.excel.loader', \LePhare\Import\Load\ExcelLoader::class)
        ->args([service('database_connection'), service('lephare_import.credentials')])
        ->tag(ImportLoaderPass::LOADER_TAG)
    ;
};
