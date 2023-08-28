# Installation

## Première étape : télécharger le bundle

```bash
composer require lephare/import-bundle
```

Cette commande nécessite l'installation globale de Composer ou la présence d'un spell.

## Deuxième étape : activer le bundle

Votre bundle devrait être automatiquement activé par Flex. Si vous n'utilisez pas Flex, vous devez activer manuellement
le paquet en ajoutant la ligne suivante dans le fichier config/bundles.php de votre projet :

```php
<?php
// config/bundles.php

return [
    // ...
    LePhare\ImportBundle\LePhareImportBundle::class => ['all' => true],
    // ...
];
```