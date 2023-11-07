# Install

## First step : download bundle

```bash
composer require lephare/import-bundle
```

This command requires global installation of Composer or the presence of an alias.

## Second step : enable the bundle

Your bundle should be automatically activated by Flex. If you're not using Flex, you'll need to manually activate
the bundle by adding the following line to your project's config/bundles.php file:

```php
<?php
// config/bundles.php

return [
    // ...
    LePhare\ImportBundle\LePhareImportBundle::class => ['all' => true],
    // ...
];
```