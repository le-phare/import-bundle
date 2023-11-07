# Events

Events are emitted throughout the import process, and can be listened to in order to connect to them.
Each event has an instance of `ImportEvent` giving access to the import configuration and a logger
configuration and a logger (PSR-3).

| Name            | Trigger                                                                 | Examples of use                                                                                                                                                 |
|-----------------|-------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| POST_INIT       | Issued **after** validation of the configuration file                   | Editing import configuration from code                                                                                                                          |
| PRE_EXECUTE     | Issued place **before** any import operation                            | Additional functionality (e.g. import report module)                                                                                                            |
| PRE_LOAD        | Issued **after** creating temporary tables and before loading resources |                                                                                                                                                                 |
| VALIDATE_SOURCE | Issued **before** importing **each** file in order to validate them     | Application of custom file validation rules. To report a non-compliant file, call `$event->setValid(false)`, which will stop the import and raise an exception. |
| POST_LOAD       | Issued **after** loading **all** resources                              | Cleaning up data to be imported using SQL queries                                                                                                               |
| PRE_COPY        | Issued **before** copying **all** resources                             | Validation of imported data (elimination of duplicates, application of business rules)                                                                          |
| COPY            | Issued **after** copying **each** resource                              | Update an import table with the IDs of the rows newly created by the import                                                                                     |
| POST_COPY       | Issued **after** copying **all** resources                              | Operations on target tables (geocoding, translation). Enhancement of import report data.                                                                        |
| POST_EXECUTE    | Occurs **after** all import operations                                  | Additional functionality (e.g. import report module)                                                                                                            |
| EXCEPTION       | Issued when an exception is raised during import                        |                                                                                                                                                                 |