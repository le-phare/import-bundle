# Configuration

## Configuration du bundle

Le bundle peut être configuré par un fichier YAML dans la configuration du projet Symfony (
ex: `config/packages/lephare_import.yaml`).

```yaml
lephare_import:
    email_report:
        recipients:
            <preset_name>: ["test@lephare.com", "test2@lephare.com"]
```

Remplacer `<preset_name>` par le nom du paramétrage, suivi d'une liste d'emails.

## Configuration d'un import

Un import est décrit par un fichier YAML.

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

# Inutilisé
label: my_import
```

| Nom            | Obligatoire (par défaut) | Type                               |                                                          |
| -------------- | ------------------------ | ---------------------------------- | -------------------------------------------------------- |
| `name`         | ✅                       | string                             | Nom de l'import                                          |
| `log_dir`      | ➖ (`null`)              | ?string                            | Chemin vers le dossier où stocker les logs               |
| `source_dir`   | ✅                       | string                             | Chemin vers le dossier contenant les fichiers à importer |
| `archive`      | ➖                       | Voir [archive](#archive)           | Options d'archivage                                      |
| `quarantine`   | ➖                       | Voir [quarantine](#quarantine)     | Options de mise en quarantine                            |
| `email_report` | ➖                       | Voir [email_report](#email_report) | Options d'envoi d'email                                  |
| `resources`    | ✅                       | Voir [resources](#resources)       | Liste de ressources                                      |

### Options dépréciées

| Nom                   | Obligatoire (par défaut) | Type    | Remplacement          |                                               |
| --------------------- | ------------------------ | ------- | --------------------- | --------------------------------------------- |
| `archive_rotation`    | ➖                       | integer | `archive.rotation`    | Nombre de dossiers d'archivage à conserver    |
| `quarantine_rotation` | ➖                       | integer | `quarantine.rotation` | Nombre de dossiers de quarantaine à conserver |

### Options inutilisées

| Nom     | Obligatoire (par défaut) | Type    |     |
| ------- | ------------------------ | ------- | --- |
| `label` | ➖ (`null`)              | ?string |     |

### archive

| Nom        | Obligatoire (par défaut) | Type    |                                                                                       |
| ---------- | ------------------------ | ------- | ------------------------------------------------------------------------------------- |
| `dir`      | ➖ (`null`)              | ?string | Chemin vers le dossier où stocker les fichiers d'import en cas de mise en quarantaine |
| `enabled`  | ➖ (`true`)              | boolean | Activer/désactiver l'archivage                                                        |
| `rotation` | ➖ (`30`)                | integer | Nombre de dossiers d'archivage à conserver                                            |

### quarantine

| Nom        | Obligatoire (par défaut) | Type    |                                                                                       |
| ---------- | ------------------------ | ------- | ------------------------------------------------------------------------------------- |
| `dir`      | ➖ (`null`)              | ?string | Chemin vers le dossier où stocker les fichiers d'import en cas de mise en quarantaine |
| `enabled`  | ➖ (`true`)              | boolean | Activer/désactiver la quarantaine                                                     |
| `rotation` | ➖ (`30`)                | integer | Nombre de dossiers de quarantaine à conserver                                         |

### email_report

```yaml
email_report:
    email_from: import@lephare.com
    recipients: lephare_import.email_report.recipients
    subject_pattern: "[%status%] Rapport d'import my_import"
    email_template: null
```

| Nom               | Obligatoire (par défaut)                 | Type                   |                                               |
| ----------------- | ---------------------------------------- | ---------------------- | --------------------------------------------- |
| `email_from`      | ✅                                       | ?string                | Email de l'expéditeur                         |
| `recipients`      | ➖ (`[]`)                                | string[] &#124; string | Email(s) du/des destinataire(s)               |
| `subject_pattern` | ➖ (`[%status%] Import report : %name%`) | string                 | Objet du mail                                 |
| `email_template`  | ➖ (`null`)                              | ?string                | Chemin vers un fichier Twig de modèle d'email |

### resources

Une ressource peut charger et/ou copier des données.

```yaml
<name>:
    tablename: user
    load:
        # ...
    copy:
        # ...
```

| Nom         | Obligatoire (par défaut) | Type   |                                                                                                                            |
| ----------- | ------------------------ | ------ | -------------------------------------------------------------------------------------------------------------------------- |
| `<name>`    | ✅                       | string | Remplacer `<name>` par un nom de ressource                                                                                 |
| `tablename` | ✅                       | string | Nom de la table temporaire créée pour l'import                                                                             |
| `load`      | ✅                       |        | Décrit comment charger les données dans la table temporaire, voir [Charger les données](configure/load.md)                 |
| `copy`      | ✅                       |        | Décrit comment copier les données de la table temporaire vers la table cible, voir [Copier les données](configure/copy.md) |