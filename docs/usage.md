# Utilisation

## Exécution d'un import

La commande d'import est la suivante :

```shell
php bin/console lephare:import [--no-load] [--] <config>
```

Exemple : `php bin/console lephare:import config/import/my_import.yaml`

## Création d'un import

Depuis Faros NG 2.5 il existe une commande utilisant Symfony Maker pour créer un fichier de configuration d'import à
partir d'un fichier CSV d'exemple.

`php bin/console make:import [options] [<name>]`

Exemple avec un fichier `my_file.csv` :

```csv
id,name
1,test
```

```bash
php bin/console make:import my_import --source my_file.csv --csv-separator ","
```

Génère ce fichier `config/import/my.import.yaml`

```yaml
source_dir: "var/exchange/input"
name: my_import
archive:
    enabled: true
    dir: "var/exchange/input/archives/my_import"
quarantine:
    enabled: true
    dir: "var/exchange/input/quarantine/my_import"

resources:
    my_import:
        tablename: import.my_import
        load:
            pattern: '^my_file\\.csv$'
            format_options:
                validate_headers: true
                with_header: true
                field_delimiter: ","
            fields:
                id: ~
                name: ~
        copy:
            target: <change_me>
            strategy: insert_or_update
            strategy_options:
                conflict_target: id
            mapping:
                id: <field_in_db>
                name: <field_in_db>
```