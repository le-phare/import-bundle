# Charger les données

Le processus de chargement ("load") permet de charger un ou plusieurs fichiers de données dans des tables temporaires.
Ces tables sont dites temporaires, car elles sont créées au début du processus d'importation.

```yaml
<name>:
    load:
        pattern: ".*activites.csv?$"
        add_file_line_number: true
        format: csv
        format_options:
            # ...
        strategy: first_by_name
        loop: false
        fields:
            # ...
        extra_fields:
            # ...
        indexes: [id, email]
```

| Nom                    | Obligatoire (par défaut)   | Type                                                   |                                                                                                                                               |
| ---------------------- | -------------------------- | ------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `pattern`              | ✅                         | string                                                 | Regex des noms de fichier à traiter                                                                                                           |
| `add_file_line_number` | ➖ (`true`)                | boolean                                                | (`format = csv` uniquement). Copie en plus des autres champs un champ `file_line_no` qui contient le numéro de ligne du fichier d'import.[^1] |
| `format`               | ➖ (`csv`)                 | `csv`&#124;`text`&#124;`xls`                           | Format des fichiers. `text` n'est supporté que sur PostgreSQL. Pour `xls`, voir [Support Excel](../features.md#support-excel).                |
| `format_options`       | ➖                         | Voir [format_options](#format_options)                 | Options de formatage. N'est utile que pour `format=csv`.                                                                                      |
| `loop`                 | ➖ (`false`)               | boolean                                                | Charger tous les fichiers, sinon uniquement le premier                                                                                        |
| `strategy`             | ➖ (`load_alphabetically`) | Voir [strategy](#strategy)                             | Stratégie de tri des fichiers à charger                                                                                                       |
| `fields`               | ✅                         | Voir [fields et extra_fields](#fields-et-extra_fields) | Champs à copier du fichier d'import vers la table d'import.                                                                                   |
| `extra_fields`         | ✅                         | Voir [fields et extra fields](#fields-et-extra_fields) | Champs supplémentaires à créer dans la table d'import.                                                                                        |
| `indexes`              | ➖                         | string[]                                               | Liste d'indexes à créer dans la table d'import                                                                                                |

## format_options

```yaml
<name>:
    load:
        format_options:
            with_header: true
            validate_headers: true
            null_string: "\n"
            field_delimiter: ";"
            quote_character: '"'
            line_delimiter: "\n"
            escape_character: "\n"
            # Inutilisé
            sheet_index: 0
            pgsql_format: "csv"
```

| Nom                | Obligatoire (par défaut) | Type    | `format`(s) supportés |                                                                                                                                                                      |
| ------------------ | ------------------------ | ------- | --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `with_header`      | ➖ (`true`)              | boolean | `csv`                 | Ignore les données présentes sur la 1ère ligne.                                                                                                                      |
| `validate_headers` | ➖ (`true`)              | boolean | `csv`                 | Valide les entêtes CSV, voir [Validation des entêtes](../features.md#validation-des-entêtes).                                                                        |
| `null_string`      | ➖                       | string  | `csv`                 | (PostgreSQL uniquement) Chaîne de caractère à considérer comme `NULL` en SQL, voir [documentation PostgreSQL](https://www.postgresql.org/docs/current/sql-copy.html) |
| `field_delimiter`  | ➖ (`;`)                 | string  | Tous                  | Délimiteur de champs                                                                                                                                                 |
| `quote_character`  | ➖ (`"`)                 | string  | `csv`                 | Caractère à considérer comme guillemets.                                                                                                                             |
| `line_delimiter`   | ➖ (`\n`)                | string  | `csv`, `xls`          | (MySQL uniquement) Délimiteur de ligne.                                                                                                                              |
| `escape_character` | ➖ (`\\`)                | string  | `csv`                 | Caractère d'échappement, voir [documentation PostgreSQL](https://www.postgresql.org/docs/current/sql-copy.html).                                                     |

## Options inutilisées

| Nom            | Obligatoire (par défaut) | Type    | `format`(s) supportés |                                          |
| -------------- | ------------------------ | ------- | --------------------- | ---------------------------------------- |
| `sheet_index`  | ➖ (`0`)                 | integer | `xls`                 | Index de la feuille de tableur à charger |
| `pgsql_format` | ➖ (`csv`)               | string  | `xls`                 | Format de copie de données PostgreSQL    |

## strategy

| Nom                           | Description                                                                            |
| ----------------------------- | -------------------------------------------------------------------------------------- |
| `load_alphabetically`         | (Anciennement `first_by_name`) Charge les fichiers dans l'ordre alphabétique           |
| `load_reverse_alphabetically` | (Anciennement `last_by_name`) Charge les fichiers dans l'ordre alphabétique inversé    |
| `load_newest_first`           | Charge les fichiers du plus récent au plus vieux selon leur date de changement (ctime) |
| `load_oldest_first`           | Charge les fichiers du plus vieux au plus récent selon leur date de changement (ctime) |

## fields et extra_fields

`fields` et `extra_fields` ont la même structure.

```yaml
<name>:
    load:
        fields | extra_fields:
            <field_1_name>: ~
            <field_2_name>: datetime
            <field_3_name>:
                type: datetime
                options:
                    default: now()
```

Ces 2 syntaxes sont équivalentes :

```yaml
            <field_1_name>: ~

            <field_1_name>:
                type: string
                options:
                    notnull: false
```

Syntaxe raccourcie :

| Nom            | Obligatoire (par défaut) | Type   |                                                                                       |
| -------------- | ------------------------ | ------ | ------------------------------------------------------------------------------------- |
| `<field_name>` | ✅ (`string`)            | string | Remplacer `<field_name>` par le nom du champ. La valeur doit être un type DBAL valide |

Syntaxe classique :

| Nom            | Obligatoire (par défaut) | Type   |                        |
| -------------- | ------------------------ | ------ | ---------------------- |
| `<field_name>` | ✅                       | string |                        |
| `type`         | ➖ (`string`)            | string | Type DBAL valide       |
| `options`      | ➖ (`{notnull: false}`)  |        | Tableau d'options DBAL |

[^1]: Problème connu : lorsque `add_file_line_number` = true et que le fichier importé contient des retours à la ligne
dans une colonne (par exemple dans du html), les numéros de ligne sont insérés dans le contenu de la colonne importée.