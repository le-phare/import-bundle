# Process

```mermaid
flowchart TB
    ImportInit["Import::init()"] -- POST_INIT --> ImportExecute["Import::execute()"]
    ImportExecute -- PRE_EXECUTE --> ImportLoad["Import::load()"]
     ImportLoad -- PRE_LOAD --> ImportLoadResource["Import::loadResource()"]
    ImportLoadResource -- VALIDATE_SOURCE\n\nPOST_LOAD -->
    ImportCopy["Import::copy()"] -- PRE_COPY\n\nPOST_COPY\n\nPOST_EXECUTE --> test(((x)))
```

## Init

Called by `Import::init()`. Loads and validates the configuration file.

## Exec

Import::execute()`. Loads and copies resources.

## Load

Loops over each loadable resource and loads their data into temporary tables.

## Copy

Loops over each copyable resource and copies their data from the temporary tables to the target tables.