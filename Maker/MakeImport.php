<?php

namespace LePhare\ImportBundle\Maker;

use LePhare\Import\Import;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class MakeImport extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:import';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new import';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConf)
    {
        $command
            ->setDescription('Creates a new import')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the config file')
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Path to an example CSV file to generate the mapping from')
            ->addOption('csv-separator', null, InputOption::VALUE_REQUIRED, 'The separator for the CSV file', ';')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $importName = 'config/import/'.Str::asFilePath($input->getArgument('name')).'.yaml';
        $columns = ['<column_in_csv>'];
        $separator = $input->getOption('csv-separator');
        $source = $input->getOption('source');

        if (null !== $source) {
            $handle = fopen($source, 'r');

            if (false === $handle) {
                throw new \RuntimeException('Could not open source file');
            }

            $columns = fgetcsv($handle, 0, $separator);
            fclose($handle);

            if (false === $columns) {
                throw new \RuntimeException('Could not get source file header');
            }
        }

        $generator->generateFile(
            $importName,
            __DIR__.'/../Resources/skeleton/import.tpl.php',
            [
                'name' => $input->getArgument('name'),
                'columns' => $columns,
                'pattern' => '^'.str_replace('.', '\.', basename($source ?: 'change_me')).'$',
                'separator' => $separator,
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next: Open your new import file and start customizing it.',
            'Find the documentation at <fg=yellow>https://github.com/le-phare/import-bundle/docs</>',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Import::class,
            'lephare/import'
        );
    }
}
