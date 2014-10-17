<img src="logo_replication.png">

replication
===========

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
