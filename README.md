<img src="/admin/assets/images/replication-110x110.png">

replication 3.0.0.0.b1
===========
pour version joomla 1.7, 2.5 et 3.x
-----------

  com_replication est un compossant joomla de replication de site et de base de donnée


"Réplication" est le seul composant Joomla (à ce jour) qui permet répliquer le site Web (fichiers, répertoires) et/ou base de donnée.

En effet, il permet de créer un environnement de pré-production et de production sur un seul serveur (ou deux) et fonctionne sur un environnement de serveur dédier ou mutualiser (exemple OVH mutualiser).

Ainsi, vous pouvez avoir un site miroir tout en créant des exceptions de réplication comme par exemple fichier de configuration.php ou recupérer les tables des commentaire utilisateur sur votre sitre de pre-production.

Le paramettage est simple :

    Site "A" vers le site "B"
    Base de "A" vers base "B"
    gestion des exceptions pour la réplication de fichiers/répertoires
    gestion des exceptions pour la réplication base de donnée


ATTENTION : s’il vous plaît, avant d'utiliser le composant, veuillez sauvegarder toutes les données (structure, fichiers, base de donnée), une erreur de configutaion vite arrivé et il n'y aura pas de deuxième essai ;).


http://composant.gires.net/

Version for joomla 1.7, 2.5 and 3.x
-----------

The com_replication extension for Joomla! will replicate your site's files and database.

"Replication" was the first Joomla extension to replicated the entire Joomla! website (files, directories) and / or database to or from another site.

It can be used to copy a pre-production environment to or from a production environment on the same or separate servers, and on dedicated or shared servers inc. commercial hosting environments.

You can mirror your site whilst creating exceptions such as your configuration.php file or not overwriting your production comment tables.

The settings are simple:

    "A" site to site "B"
    Base "A" base vers "B"
    exception handling for replication of files / directories
    exception handling for the base data replication


NOTE: Before using the component, please ensure that you have a complete and current back-up of both source and target sites i.e. of the structure, files and database. When you run Replication, it will overwrite the existing site and the original files and database will be gone.

http://composant.gires.net/
