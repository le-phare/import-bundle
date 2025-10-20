# Load data

The load process is used to load one or more data files into temporary tables.
These tables are called temporary because they are created at the start of the import process.

```yaml
<name>:
    load:
        pattern: ".*activites.csv?$"
        add_file_line_number: true
        fail_if_not_loaded: false
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

| Name                   | Mandatory (default)       | Type                                                      |                                                                                                                                               |
|------------------------|---------------------------|-----------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------|
| `pattern`              | ✅                         | string                                                    | Regex of file names to be processed                                                                                                           |
| `add_file_line_number` | ➖ (`true`)                | boolean                                                   | (`format = csv` only). Copies, in addition to the other fields, a `file_line_no` field which contains the line number of the import file.[^1] |
| `fail_if_not_loaded`   | ➖ (`false`)               | boolean                                                   | If `true`, the import will fail if no file is loaded. Added in `lephare/import` v2.4.0.                                                       |
| `format`               | ➖ (`csv`)                 | `csv`&#124;`text`&#124;`xls`                              | File format: `text` is only supported on PostgreSQL. For `xls`, see [Excel Support](../features.md#support-excel).                            |
| `format_options`       | ➖                         | See [format_options](#format_options)                    | Formatting options. Only useful for `format=csv`.                                                                                             |
| `loop`                 | ➖ (`false`)               | boolean                                                   | Load all files, otherwise only the first one                                                                                                  |
| `strategy`             | ➖ (`load_alphabetically`) | See [strategy](#strategy)                                | Upload file sorting strategy                                                                                                                  |
| `fields`               | ✅                         | See [fields and extra_fields](#fields-and-extra_fields)    | Fields to be copied from the import file to the import table.                                                                                 |
| `extra_fields`         | ✅                         | See [fields and extra fields](#fields-and-extra_fields)    | Additional fields to be created in the import table.                                                                                          |
| `indexes`              | ➖                         | string[]                                                  | List of indexes to create in the import table                                                                                                 |

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
            # Unused
            sheet_index: 0
            pgsql_format: "csv"
```

| Name                | Mandatory (default)   | Type    | Supported formats |                                                                                                                                                             |
| ------------------ |-----------------------| ------- |-----------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `with_header`      | ➖ (`true`)            | boolean | `csv`                 | Ignores data on the 1st line.                                                                                                                               |
| `validate_headers` | ➖ (`true`)            | boolean | `csv`                 | Validates CSV headers, see [Header validation](../features.md#header-validation).                                                                           |
| `null_string`      | ➖                     | string  | `csv`                 | (PostgreSQL only) Character string to be considered as `NULL` in SQL, see [PostgreSQL documentation](https://www.postgresql.org/docs/current/sql-copy.html) |
| `field_delimiter`  | ➖ (`;`)               | string  | All                   | Field delimiter                                                                                                                                             |
| `quote_character`  | ➖ (`"`)               | string  | `csv`                 | Character to be treated as inverted commas.                                                                                                                 |
| `line_delimiter`   | ➖ (`\n`)              | string  | `csv`, `xls`          | (MySQL only) Line delimiter.                                                                                                                                |
| `escape_character` | ➖ (`\\`)              | string  | `csv`                 | Escape character, see [PostgreSQL documentation](https://www.postgresql.org/docs/current/sql-copy.html).                                                    |

## Unused options

| Name           | Mandatory (default)      | Type    | Supported formats     |                                          |
| -------------- | ------------------------ | ------- | --------------------- |------------------------------------------|
| `sheet_index`  | ➖ (`0`)                 | integer | `xls`                 | Index of the spreadsheet to be loaded    |
| `pgsql_format` | ➖ (`csv`)               | string  | `xls`                 | PostgreSQL data copy format              |

## strategy

| Name                          | Description                                                                              |
| ----------------------------- | ---------------------------------------------------------------------------------------- |
| `load_alphabetically`         | (Formerly `first_by_name`) Loads files in alphabetical order                             |
| `load_reverse_alphabetically` | (Formerly `last_by_name`) Loads files in reverse alphabetical order                      |
| `load_newest_first`           | Loads files from the most recent to the oldest according to their date of change (ctime) |
| `load_oldest_first`           | Loads files from oldest to newest by date of change (ctime)                              |

## fields and extra_fields

`fields` and `extra_fields` have the same structure.

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

These 2 syntaxes are equivalent:

```yaml
            <field_1_name>: ~

            <field_1_name>:
                type: string
                options:
                    notnull: false
```

Shortened syntax:

| Name           | Mandatory (default)   | Type   |                                                                                        |
|----------------|-----------------------| ------ | -------------------------------------------------------------------------------------- |
| `<field_name>` | ✅ (`string`)         | string | Replace `<field_name>` with the name of the field. The value must be a valid DBAL type |

Classic syntax:

| Name            | Mandatory (default)     | Type   |                         |
| --------------- |-------------------------| ------ |-------------------------|
| `<field_name>`  | ✅                      | string |                         |
| `type`          | ➖ (`string`)           | string | Valid DBAL type         |
| `options`       | ➖ (`{notnull: false}`) | array  | DBAL options table      |

[^1]: Known issue: when `add_file_line_number` = true and the imported file contains line breaks
in a column (for example in html), the line numbers are inserted into the content of the imported column.
