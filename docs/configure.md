# Configuration

## Bundle configuration

The bundle can be configured using a YAML file in the Symfony project configuration (
ex: `config/packages/le_phare_import.yaml`).

```yaml
le_phare_import:
    email_report:
        recipients:
            <preset_name>: ["test@lephare.com", "test2@lephare.com"]
```

Import report recipients presets can be defined globally. Replace `<preset_name>` with the name of the setting, followed by a list of emails. Each import configuration will then be able to use a preset.

## Configuring an import

An import is described in a YAML file.

```yaml
# config/import/my_import.yaml

name: my_import

log_dir: "%kernel.project_dir%/var/log/import"
source_dir: "var/exchange/input"

archive:
    enabled: true
    dir: "var/exchange/input/archives/my_import"

quarantine:
    enabled: true
    dir: "var/exchange/input/quarantine/my_import"
email_report:
    # ...
resources:
    # ...

# Unused
label: my_import
```

| Name           | Mandatory (default)  | Type                              |                                                        |
|----------------|----------------------|-----------------------------------|--------------------------------------------------------|
| `name`         | ✅                    | string                            | Import name                                            |
| `log_dir`      | ➖ (`null`)           | ?string                           | Path to the folder where the logs are stored           |
| `source_dir`   | ✅                    | string                            | Path to the folder containing the files to be imported |
| `archive`      | ➖                    | see [archive](#archive)           | Archiving options                                      |
| `quarantine`   | ➖                    | see [quarantine](#quarantine)     | Quarantine options                                     |
| `email_report` | ➖                    | see [email_report](#email_report) | Email sending options                                  |
| `resources`    | ✅                    | see [resources](#resources)       | List of resources                                      |

### Deprecated options

| Name                  | Mandatory (default) | Type    | Replacement           |                                        |
|-----------------------|---------------------| ------- |-----------------------|----------------------------------------|
| `archive_rotation`    | ➖                   | integer | `archive.rotation`    | Number of archiving files to be kept   |
| `quarantine_rotation` | ➖                   | integer | `quarantine.rotation` | Number of quarantine files to be kept  |

### Unused options

| Name    | Mandatory (default)   | Type    |     |
|---------|-----------------------| ------- | --- |
| `label` | ➖ (`null`)            | ?string |     |

### archive

| Name        | Mandatory (default)    | Type    |                                                                             |
| ---------- |------------------------| ------- |-----------------------------------------------------------------------------|
| `dir`      | ➖ (`null`)             | ?string | Path to the folder where import files are stored in the event of quarantine |
| `enabled`  | ➖ (`true`)             | boolean | Activate/deactivate archiving                                               |
| `rotation` | ➖ (`30`)               | integer | Number of archiving files to be kept                                        |

### quarantine

| Name        | Mandatory (default)   | Type    |                                                                             |
| ---------- |-----------------------| ------- |-----------------------------------------------------------------------------|
| `dir`      | ➖ (`null`)            | ?string | Path to the folder where import files are stored in the event of quarantine |
| `enabled`  | ➖ (`true`)            | boolean | Activate/deactivate quarantine                                              |
| `rotation` | ➖ (`30`)              | integer | Number of quarantine files to be kept                                       |

### email_report

```yaml
email_report:
    email_from: import@lephare.com
    recipients: <preset_name>
    subject_pattern: "[%status%] Import report : %name%"
    email_template: null
```

| Name              | Mandatory (default)                      | Type                   |                                                                                                  |
| ----------------- | ---------------------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------ |
| `email_from`      | ✅                                       | ?string                | Sender's email address                                                                           |
| `recipients`      | ➖ (`[]`)                                | string[] &#124; string | Email(s) of recipient(s) or preset name defined in [bundle configuration](#bundle-configuration) |
| `subject_pattern` | ➖ (`[%status%] Import report : %name%`) | string                 | Email subject. Placeholders `%name%` and `%status%` are replaced automatically                   |
| `email_template`  | ➖ (`null`)                              | ?string                | Path to a Twig email template file                                                               |

### Resources

A resource can load and/or copy data. An import can define one or more resources to be imported sequentially.

```yaml
<name>:
    tablename: user
    load:
        # ...
    copy:
        # ...
```

| Name         | Mandatory (default)   | Type     |                                                                                                                  |
| ----------- |-----------------------|----------|------------------------------------------------------------------------------------------------------------------|
| `<name>`    | ✅                     | string   | Replace `<name>` with a resource name                                                                            |
| `tablename` | ✅                     | string   | Name of the temporary table created for the import                                                               |
| `load`      | ✅                     |          | Describes how to load data into the temporary table, see [Load data](configure/load.md)                     |
| `copy`      | ✅                     |          | Describes how to copy data from the temporary table to the target table, see [Copy data](configure/copy.md) |
