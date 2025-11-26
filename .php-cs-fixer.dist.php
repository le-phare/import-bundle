<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('Resources')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@DoctrineAnnotation' => true,
        'no_unused_imports' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache') // forward compatibility with 3.x line
    ->setUnsupportedPhpVersionAllowed(true)
;
