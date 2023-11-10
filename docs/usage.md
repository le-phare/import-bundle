# Usage

## Performing an import

The import command is as follows:

```shell
php bin/console lephare:import [--no-load] [--] <config>
```

Example: `php bin/console lephare:import config/import/my_import.yaml`

## Creating an import

This bundle includes a Symfony Maker command to create an import configuration file based on a CSV example file.

`php bin/console make:import [options] [<name>]`

Example with `my_file.csv`:

```csv
id,name
1,test
```

```bash
php bin/console make:import my_import --source my_file.csv --csv-separator ","
```

Generates the file `config/import/my.import.yaml`

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
