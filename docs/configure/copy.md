# Copier les données

Le processus de copie ("copy") permet de copier les données des tables d'import vers les tables de l'application.

```yaml
<name>:
    copy:
        target: _user
        strategy: insert_or_update
        strategy_options:
            # ...
        mapping:
            # ...
```

| Nom                | Obligatoire (par défaut) | Type                                       |                                                                                         |
| ------------------ | ------------------------ | ------------------------------------------ | --------------------------------------------------------------------------------------- |
| `target`           | ✅                       | string                                     | Nom de la table cible de l'application                                                  |
| `strategy`         | ➖ (`insert_or_update`)  | Voir [strategy](#strategy)                 | Stratégie de copie                                                                      |
| `strategy_options` | ➖                       | Voir [strategy_options](#strategy_options) |                                                                                         |
| `mapping`          | ✅                       | Voir [mapping](#mapping)                   | Tableau mettant en relation les colonnes de la table d'import et celle de l'application |

## strategy

Trois stratégies sont disponibles :

| Nom              | SGBD supportés         | Comportement                                                                                                                             |
| ---------------- | ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| insert           | MySQL, PostgreSQL 9.4+ | Stratégie la plus simple, exécute `INSERT INTO ... SELECT ...`. Ne gère pas les contraintes `NOT NULL` ou `UNIQUE`.                      |
| insert_ignore    | MySQL, PostgreSQL 9.5+ | Ignore les lignes en conflit                                                                                                             |
| insert_or_update | MySQL, PostgreSQL 9.5+ | Exécute un `UPDATE` en cas de conflit. Utilise `INSERT ... ON DUPLICATE KEY` en MySQL et `INSERT ... ON CONLICT DO UPDATE` en PostgreSQL |

## strategy_options

```yaml
<name>:
    copy:
        strategy_options:
            copy_condition: email_commercial IS NOT NULL
            distinct: true
            joins: INNER JOIN table ON (table.email = temp.email_commercial)
            conflict_target: email
            non_updateable_fields: ["email", "username"]
```

| Nom                     | Obligatoire (par défaut) | Type     |                                                                                                                                                                                                                                                                                       |
| ----------------------- | ------------------------ | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `copy_condition`        | ➖                       | string   | Condition SQL à insérer dans la requête d'insertion (omettre `WHERE`)                                                                                                                                                                                                                 |
| `distinct`              | ➖ (`false`)             | boolean  | Insère `DISTINCT` dans la requête de sélection                                                                                                                                                                                                                                        |
| `joins`                 | ➖                       | string   | Jointures SQL à insérer dans la requête de sélection                                                                                                                                                                                                                                  |
| `conflict_target`       | ➖                       | string   | (PostgreSQL ET `strategy=insert_or_update&#124;insert_ignore`uniquement, obligatoire si `strategy=insert_or_update`). Index(es) de colonne(s) à utiliser dans `ON CONFLICT`, voir [documentation PostgreSQL](https://www.postgresql.org/docs/current/sql-insert.html#SQL-ON-CONFLICT) |
| `non_updateable_fields` | ➖ (`[]`)                | string[] | Liste de champs à ne pas mettre à jour lors des imports                                                                                                                                                                                                                               |

## mapping

```yaml
<name>:
    copy:
        mapping:
            <name_1>:
                property: [email, username]
            <name_2>: lastname
            <name_3>: firstname
            created_at:
                property: [created_at, updated_at]
            created_by:
                property: [created_by, updated_by]
            status:
                property: status
                sql: "'enabled'"
            type:
                property: type
                sql: "'commercial'"
            salt:
                property: salt
                sql: "'salt_bidon'"
            password_token:
                property: password_token
                sql: "'token_bidon'"
```

| Nom          | Obligatoire (par défaut) | Type                   |                                                                       |
| ------------ | ------------------------ | ---------------------- | --------------------------------------------------------------------- |
| `<name>`     | ✅                       |                        | Remplacer `<name>` par le nom de la colonne à créer                   |
| `property`   | ➖                       | string[] &#124; string | Liste des colonnes de l'application dans lesquelles insérer la donnée |
| `sql`        | ➖ (`null`)              | boolean                | Déclaration SQL de la valeur à insérer dans la colonne cible          |
| `update_sql` | ➖ (`null`)              | string                 | Déclaration SQL `UPDATE` #FIXME                                       |