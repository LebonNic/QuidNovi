# QuidNovi

Application de gestion de flux RSS et Atom utilisant PHP et AngularJS

## Installation et mise en marche
Tout d'abord, le projet peut être récupéré avec les commande suivantes :

    git clone https://www.github.com/Dramloc/QuidNovi.git
    cd QuidNovi

QuidNovi utilise les extensions sqlite et openssl (récupération via https) de php. Il est nécessaire de les activer dans php.ini.

QuidNovi utilise composer pour la gestion des dépendances. Pour installer composer et les dépendances du projet, entrer les commandes suivantes :
    
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
    
QuidNovi peut fonctionner sur le serveur intégré à php, celui-ci peut donc être lancé avec la commande suivante :
    
    php -S localhost:8080 -t web
    
La base de données sqlite sera créée automatiquement dans le fichier `database.sqlite3`
    
La mise à jour des flux utilise un script séparé : `update_feeds.php`. Celui-ci peut être lancé avec la commande suivante :

    php update_feeds.php
    
Il est conseillé d'ajouter une tâche planifiée pour l'exécution de script. Pour cron, on ajoutera par exemple la ligne suivante pour une actualisation des flux toutes les 10 minutes :

    */10 * * * * /usr/bin/php /path/to/update_feeds.php

## Client
Le client a été développé avec AngularJS, il utilise la bibliothèque de composants Material Design[1] pour AngularJS.
Les routes de l'application sont les suivantes:

* /                       : redirection sur /#/entries.
* /#/entries              : affichage des nouvelles entrées triées par date.
* /#/entries?read=false   : affichage des entrées non-lues.
* /#/entries?saved=true   : affichage des entrées sauvegardées.
* /#/feeds/:id            : affichage des entrées du feed spécifié, possibilité de l'éditer et de se désabonner.
* /#/categories/:id       : affichage des entrées d'une catégorie, possibilité d'éditer et de supprimer la catégorie.

L'application permet de s'abonner à de nouveaux flux et de créer des catégories. Les catégories et les flux peuvent être organisés dans des catégories et sous-catégories. Les entrées de la liste peuvent être marquées comme lues ou non lues, sauvegardées ou non sauvegardées. Les entrées sauvegardées seront placées dans une section spéciale pour être plus facilement retrouvées.

Le chargement des entrées de chaque catégorie est fait en une seule fois, une amélioration possible serait de faire un chargement progressif des données lors du défilement de la page afin d'améliorer les performances.

[1]: https://material.angularjs.org/#/      "Material Design"

## API REST
Étant donné que l'application client est développée entièrement avec Angular, la récupération de contenu et les actions se font via l'API REST du serveur voici les routes et les fonctionnalités de celle-ci :

### Categories
* POST   /categories     : crée une nouvelle catégorie {name: `name`, containerId: `containerId`}.
* GET    /categories     : obtient une représentation hiérarchique des catégories.
* GET    /categories/:id : obtient la catégorie spécifiée.
* PATCH  /categories/:id : renomme {name: `name`} ou déplace {containerId: `containerId`} la catégorie spécifiée.
* DELETE /categories/:id : supprime la catégorie, ses sous-catégories et les flux contenus.

### Entries
* GET    /entries?read=`true/false`&saved=`true/false`&feed=`feedId`&category=`categoryId` : récupère les entrées avec les filtres spécifiés.
* GET    /entries/:id    : récupère l'entrée spécifiée.
* PATCH  /entries/:id    : marque une entrée comme lue ou non lue {read: `true/false`}, sauvegardée ou non sauvegardée {saved: `true/false`}.

### Feeds
* POST   /feeds          : abonne au flux spécifié. {name: `name`, containerId: `containerId`, source: `source`}.
* GET    /feeds          : récupère tous les flux.
* GET    /feeds/:id      : récupère le flux spécifié.
* PATCH  /feeds/:id      : renomme {name: `name`} ou déplace {containerId: `containerId`} le flux spécifié.
* DELETE /feeds/:id      : désabonne le flux spécifié.

##Serveur
L'application serveur est entièrement codée en PHP et se base sur le framework Slim. Ce dernier permet de facilement gérer la mise en place de routes dans le but de consulter les ressources délivrables par le serveur. Ces ressources sont accessibles via l'API REST présentées ci-dessus.

### Classes métiers
Dans le but de gérer les flux auxquels s'est abonné l'utilisateur, ainsi que les entrées de ces flux, plusieurs classes métiers ont été modélisées puis implémentées.

### Gestion de la persistance
L'application serveur offre une solution pour gérer la transformation des classes métiers qu'elle utilise en données exploitables par un SGBD. Pour cela, elle fait appel au patron de conception Data Mapper. Ce dernier consiste à implémenter, pour chaque classe métier, une classe duale chargée uniquement de contenir les traitements permettant la sauvegarde de la classe métier en base. Ces mappers font appels à l'API PDO pour communiquer avec la BDD.

De manière symétrique aux mappers, les finders se présentent sous la forme de classes dont l'interface permet de transformer les données d'une base en objets métiers. En d'autres termes ils permettent de recharger les objets stockés par les mappers. A ce moment, il est fait usage d'une technique particulière appelée lazy loading. Effectivement, certains objets métiers contiennent parfois des collections d'autres objets en attribut. Au moment du chargement, il n'est pas forcément nécessaire que celles-ci soient tout de suite récupérées. De ce fait, derrière les accesseurs qui permettent d'accéder à ces collections se cachent des closures. Celles-ci sont mises en place par les finders au moment de la création des objets métiers et se déclenchent lors des appels aux accesseurs.
