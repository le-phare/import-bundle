# IDE Integration

Beyond validating YAML syntax in your IDE, you can validate the definition of an import configuration using the JSON schema https://raw.githubusercontent.com/le-phare/import/refs/heads/master/lephare-import.schema.json.

This also provides contextual help for autocompletion and key hover.
## Language Server

Add this comment as the first line of your import configuration (useful if your IDE is compatible with YAML Language Server):

```yaml
# yaml-language-server: $schema=https://raw.githubusercontent.com/le-phare/import/refs/heads/master/lephare-import.schema.json
```

## JetBrains PhpStorm

- Go to "Settings" > "Languages & Frameworks" > "Schemas and DTDs" > "JSON Schema Mappings".
- Click on "Add".
- Fill in the following:
    - Name: Lephare Import
    - Schema file or URL: the path to a local copy or the URL: [lephare-import.schema.json](https://raw.githubusercontent.com/le-phare/import/refs/heads/master/lephare-import.schema.json).
    - Version: JSON Schema 2020.12
    - File pattern: `config/import/*.yaml`. Adjust according to your Symfony architecture.
- Click on "OK".

## Visual Studio Code

- Install the [YAML](https://open-vsx.org/vscode/item?itemName=redhat.vscode-yaml) extension to enable YAML Language Server support;
- Optionally: to enable validation on all import files, add this JSON to the user settings file `settings.json`:
```json
"yaml.schemas": {
    "https://raw.githubusercontent.com/le-phare/import/refs/heads/master/lephare-import.schema.json": "config/import/*.yaml"
}
```
