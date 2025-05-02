# Copy data

The copy process copies data from the import tables to the application tables.

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

| Name               | Mandatory (default)    | Type                                       |                                                                                                     |
|--------------------|------------------------| ------------------------------------------ |-----------------------------------------------------------------------------------------------------|
| `target`           | ✅                      | string                                     | Name of the application's target table                                                              |
| `strategy`         | ➖ (`insert_or_update`) | Voir [strategy](#strategy)                 | Copy strategy                                                                                       |
| `strategy_options` | ➖                      | Voir [strategy_options](#strategy_options) |                                                                                                     |
| `mapping`          | ✅                      | Voir [mapping](#mapping)                   | Table showing the relationship between the columns in the import table and those in the application |

## strategy

Three strategies are available:

| Name              | Supported DBMS         | Behaviour                                                                                                                                          |
| ---------------- | ---------------------- |----------------------------------------------------------------------------------------------------------------------------------------------------|
| insert           | MySQL, PostgreSQL 9.4+ | The simplest strategy is to execute `INSERT INTO ... SELECT ...`. Does not handle `NOT NULL` or `UNIQUE` constraints.                              |
| insert_ignore    | MySQL, PostgreSQL 9.5+ | Ignores conflicting lines                                                                                                                          |
| insert_or_update | MySQL, PostgreSQL 9.5+ | Executes an `UPDATE` in the event of a conflict. Uses `INSERT ... ON DUPLICATE KEY` in MySQL and `INSERT ... ON CONLICT DO UPDATE` in PostgreSQL   |

## strategy_options

```yaml
<name>:
    copy:
        strategy_options:
            copy_condition: email_commercial IS NOT NULL
            distinct: true
            joins: INNER JOIN table ON (table.email = temp.email_commercial)
            conflict_target: email
            # OR
            conflict_target:
                sql: "(email)"
            non_updateable_fields: ["email", "username"]
```

| Name                    | Mandatory (default) | Type                    |                                                                                                                                                                                                                                                                                                                                                                                                          |
|-------------------------|---------------------|-------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `copy_condition`        | ➖                   | string                  | SQL condition to insert in the insertion query (omit `WHERE`)                                                                                                                                                                                                                                                                                                                                            |
| `distinct`              | ➖ (`false`)         | boolean                 | Inserts `DISTINCT` in the selection query                                                                                                                                                                                                                                                                                                                                                                |
| `joins`                 | ➖                   | string                  | SQL joins to be inserted in the selection query                                                                                                                                                                                                                                                                                                                                                          |
| `conflict_target`       | ➖                   | string; ['sql': string] | (PostgreSQL AND `strategy=insert_or_update&#124;insert_ignore` only, mandatory if `strategy=insert_or_update`). Column index(es) to be used in `ON CONFLICT`, see [PostgreSQL documentation](https://www.postgresql.org/docs/current/sql-insert.html#SQL-ON-CONFLICT). When passed as a string, parentheses are added around the value. When passed in the 'sql' array key, the value is inserted as-is. |
| `non_updateable_fields` | ➖ (`[]`)            | string[]                | List of fields not to be updated during imports                                                                                                                                                                                                                                                                                                                                                          |

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

| Name         | Mandatory (default)   | Type                   |                                                                         |
|--------------|-----------------------| ---------------------- |-------------------------------------------------------------------------|
| `<name>`     | ✅                     |                        | Replace `<name>` with the name of the column to be created              |
| `property`   | ➖                     | string[] &#124; string | List of columns in the application in which to insert the data          |
| `sql`        | ➖ (`null`)            | boolean                | SQL statement of the value to be inserted in the target column          |
| `update_sql` | ➖ (`null`)            | string                 | SQL statement `UPDATE                                                   |

## :warning: Handling database ID's without incrementing sequence

By default, when performing an import, each row will trigger an auto-increment on the `target` table. 

This can cause issues with recurring imports: running the same import multiple times will cause the ID sequence to increment for each row, even though no new insertion is actually made.

To prevent this, you should add a join statement on the existing table to retrieve the existing id.

```yaml
strategy_options:
    joins: LEFT JOIN my_table current ON current.code = temp.code
```

```yaml
mapping:
    id:
        property: id
        sql: COALESCE(current.id, nextval('my_table_id_seq'))
```
