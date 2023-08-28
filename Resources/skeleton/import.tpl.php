source_dir: "var/exchange/input"
name: <?= $name; ?>

archive:
    enabled: true
    dir: "var/exchange/input/archives/<?= $name; ?>"
quarantine:
    enabled: true
    dir: "var/exchange/input/quarantine/<?= $name; ?>"

resources:
    <?= $name; ?>:
        tablename: import.<?= $name; ?>

        load:
            pattern: '<?= $pattern ?>'
            format_options:
                validate_headers: true
                with_header: true
                field_delimiter: '<?= $separator ?>'
            fields:
<?php foreach ($columns as $column) : ?>
                <?= $column ?>: ~
<?php endforeach; ?>
        copy:
            target: <change_me>
            strategy: insert_or_update
            strategy_options:
                conflict_target: id
            mapping:
<?php foreach ($columns as $column) : ?>
                <?= $column ?>: <field_in_db>
<?php endforeach; ?>
