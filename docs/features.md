# Fonctionnalités

## Validation des entêtes

Uniquement disponible si `format = csv`.

Valide que les champs déclarés dans la configuration YAML sont identiques à ceux du fichier CSV, ainsi que dans le même
ordre.

## Archivage

L'archivage est activé par défaut.

Au terme d'un import, les fichiers importés sont déplacés dans le dossier d'archivage (`<source_dir>/archives/<date>`
par défaut). Seuls les 30 derniers dossiers sont conservés par défaut.

## Quarantaine

La quarantaine est activée par défaut.

Si une exception est levée lors d'un import, tous les fichiers d'import chargés par au moins une ressource[^1] sont
déplacés dans le dossier de quarantaine (`<source_dir>/quarantine/<date>` par défaut). Seuls les 30 derniers dossiers
sont conservés par défaut.

## Support Excel

Le support des fichiers .XLS nécessite l'installation du paquet Composer `phpoffice/phpspreadsheet`

[^1]: Les ressources ne comprenant pas de configuration `load` sont ignorées.