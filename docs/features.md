# Features

## Header validation

Only available if `format = csv`.

Validates that the fields declared in the YAML configuration are identical to those in the CSV file, and in the same
order.

## Archiving

Archiving is enabled by default.

When an import is complete, the imported files are moved to the archive folder (`<source_dir>/archives/<date>`
by default). By default, only the last 30 folders are kept.

## Quarantine

Quarantine is enabled by default.

If an exception is raised during an import, by default all import files loaded by at least one resource[^1] are
moved to the quarantine folder (`<source_dir>/quarantine/<date>` by default). Only the last 30 folders
are kept by default.

If the `unit_work` option is enabled, only the files that caused the error are moved to quarantine,
instead of all files loaded by the resource.

## Excel support

Support for .XLS files requires installation of the Composer package `phpoffice/phpspreadsheet`.

[^1]: Resources without a `load` configuration are ignored.
