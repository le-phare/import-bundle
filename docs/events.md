# Événements

Des événements sont émis tout au long du processus d'import, ils peuvent être écoutés afin de s'y brancher.
Chaque événement possède une instance de `ImportEvent` donnant accès à la configuration de l'import ainsi qu'à un logger
configuration and a logger (PSR-3).

| Nom             | Déclenchement                                                                           | Exemples d'utilisation                                                                                                                                                                                   |
| --------------- | --------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| POST_INIT       | Émis **après** la validation du fichier de configuration                                | Édition de la configuration d'import depuis le code                                                                                                                                                      |
| PRE_EXECUTE     | Intervient **avant** toute opération d'import                                           | Ajout de fonctionnalités supplémentaires (ex: module de rapport d'import)                                                                                                                                |
| PRE_LOAD        | Émis **après** la création des tables temporaires et avant le chargement des ressources |                                                                                                                                                                                                          |
| VALIDATE_SOURCE | Émis **avant** l'import de **chaque** fichier afin de les valider                       | Application de règles de validation de fichier personnalisées. Pour signaler un fichier non conforme, appeler `$event->setValid(false)`, cela a pour effet d'arrêter l'import et de lever une exception. |
| POST_LOAD       | Émis **après** le chargement de **toutes** les ressources                               | Nettoyage des données à importer à l'aide de requêtes SQL                                                                                                                                                |
| PRE_COPY        | Émis **avant** la copie de **toutes** les ressources                                    | Validation des données importées (élimination de doublons, application de règles métier)                                                                                                                 |
| COPY            | Émis **après** la copie de **chaque** ressource                                         | Mise à jour d'une table d'import avec les IDs des lignes nouvellement créées par l'import                                                                                                                |
| POST_COPY       | Émis **après** la copie de **toutes** les ressources                                    | Opérations sur les tables cibles (géocodage, traduction). Enrichissement des données du rapport d'import.                                                                                                |
| POST_EXECUTE    | Intervient **après** toutes les opérations d'import                                     | Ajout de fonctionnalités supplémentaires (ex: module de rapport d'import)                                                                                                                                |
| EXCEPTION       | Émis lorsqu'une exception est levée durant l'import                                     |                                                                                                                                                                                                          |