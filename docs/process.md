# Processus d'exécution

```mermaid
flowchart TB
    ImportInit["Import::init()"] -- POST_INIT --> ImportExecute["Import::execute()"]
    ImportExecute -- PRE_EXECUTE --> ImportLoad["Import::load()"]
     ImportLoad -- PRE_LOAD --> ImportLoadResource["Import::loadResource()"]
    ImportLoadResource -- VALIDATE_SOURCE\n\nPOST_LOAD -->
    ImportCopy["Import::copy()"] -- PRE_COPY\n\nPOST_COPY\n\nPOST_EXECUTE --> test(((x)))
```

## Initialisation

Appelé par `Import::init()`. Charge et valide le fichier de configuration.

## Exécution

`Import::execute()`. Charge et copie les ressources.

## Chargement

Boucle sur chaque ressource chargeable et charge leurs données dans des tables temporaires.

## Copie

Boucle sur chaque ressource copiable et copie leurs données des tables temporaires vers les tables cibles.