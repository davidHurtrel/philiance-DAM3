# SYMFONY

## NOUVEAU PROJET

- ouvrir un terminal
- se rendre dans le dossier où l'on veut créer le projet (ex.: MAMP/htdocs) :
```
cd chemin_vers_le_dossier
```
- créer le projet avec Symfony CLI (pas besoin de créer le dossier du projet) :
```
symfony new --webapp nom_du_projet --version=6.1
```
- créer le projet avec Composer (pas besoin de créer le dossier du projet) :
```
composer create-project symfony/website-skeleton nom_du_projet ^6.1.*
```

## GIT

- créer un dépôt Git sur GitHub
- avec un terminal, se rendre dans le dossier du projet :
```
cd chemin_du_dossier
```
- initialiser un dépôt local :
```
git init
```
- lier le dépôt local au dépôt distant :
```
git remote add origin https://github.com/nom_d_utilisateur/nom_du_depot_distant.git
```
- ajouter les fichiers :
```
git add *
```
- donner un nom au commit :
```
git commit -m "message_du_commit"
```
- récupérer les dernières modifications :
```
git pull origin main
```
- envoyer les modifications :
```
git push origin main
```
- voir la liste des commits :
```
git log
```

## RÉCUPÉRER UN PROJET

- télécharger le zip ou faire un pull
- recréer le fichier .env.local à la racine du projet (avec ses propres informations), les informations importantes sont APP_ENV, APP_SECRET et MAILER_DSN (éventuellement MAILER_URL)
- mettre à jour le projet (installer les dépendances, générer le cache, ...) :
```
composer install
```
- créer la base de données (si cela n'est pas déjà fait) :
```
symfony console doctrine:database:create
```
- mettre à jour la base de données (créer, modifier, supprimer les tables) :
```
symfony console doctrine:migrations:migrate
```

## SYMFONY SERVER

- démarrer le serveur Symfony (Ctrl + C pour quitter) :
```
symfony serve
```
- démarrer le serveur en arrière-plan :
```
symfony server:start -d
```
- arrêter les erveur en arrière-plan :
```
symfony server:stop
```
- installer un certificat SSL/TLS local :
```
symfony server:ca:install
```

## APACHE-PACK

- suite d'outils pour Apache (barre de débug, routing, .htaccess)
- dans le terminal :
```
composer require symfony/apache-pack
```

## CONTROLLER

- créer un controller (et le template associé) :
```
symfony console make:controller nom_du_controller
```

## VARIABLES GLOBALES

- variables utilisables dans tout fichier Twig
- dans le fichier config/packages/twig.yaml :
```YAML
twig:
    ...
    globals:
        nom_de_la_variable: 'valeur_de_la_variable'
```
- utilisation dans tout fichier Twig comme n'importe quelle donnée venant d'un controller :
```
{{ nom_de_la_variable }}
```

## BASE DE DONNÉES

- .env.local :
```
DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/nom_de_la_base_de_donnees?serverVersion=5.7&charset=utf8mb4"
```
- créer la base de données :
```
symfony console doctrine:database:create
```
- supprimer la base de données :
```
symfony console doctrine:database:drop --force
```
- créer une entité (table) ou ajouter des champs à une entité :
```
symfony console make:entity nom_de_l_entite
```
- créer l'entité User :
```
symfony console make:user
```
- migration :
```
symfony console make:migration
symfony console doctrine:migrations:migrate
```
- exécuter une requête sql :
```
symfony console doctrine:query:sql "la_requete_a_executer"
```

## FIXTURES

- installer le bundle :
```
composer require --dev orm-fixtures (ou composer require doctrine/doctrine-fixtures-bundle)
```
- compléter le fichier src/DataFixtures/AppFixtures.php
- persist()
- flush()
- envoyer en base de données (en écrasant) :
```
symfony console doctrine:fixtures:load
```
- envoyer en base de données (en ajoutant à la suite) :
```
symfony console doctrine:fixtures:load --append
```
- bundle pour générer de fausses données :
```
composer require fakerphp/faker
```

## FORMULAIRE

- créer un formulaire :
```
symfony console make:form nom_du_formulaire (puis préciser le nom de l'entité associée)
```
- mise en form des formulaires avec un thème (config/packages/twig.yaml)
```YAML
twig:
    form_themes: ['bootstrap_5_layout.html.twig']
```

## MESSAGES FLASH

- dans un controller (avant redirection) :
```PHP
$this->AddFlash('le_label', 'le_message_a_afficher');
```
- à l'endroit où l'on veut afficher le message (template) :
```HTML
{% for label, messages in app.flashes %}
    {% for message in messages %}
        <div class="flash-{{ label }}">
            {{ message }}
        </div>
    {% endfor %}
{% endfor %}
```

## EMAIL

- installer le mailer (si nécessaire) :
```
composer require symfony/mailer
```
- installer le package tiers :
```
composer require symfony/google-mailer
```
- dans les paramètres de compte Google => Sécurité => Connexion à Google : activer la validation en deux étapes pour pouvoir accéder aux Mots de passe des applications
- créer un nouveau mot de passe d'application
- .env.local :
```
MAILER_DSN=gmail://USERNAME:PASWWORD@default
```
- pour que les messages (emails) s'envoient directement (synchrone), config/packages/messenger.yaml :
```YAML
framework:
    messenger:
        ...
        transports:
            async:
                ...
            ...
            sync: 'sync://' # décommenter cette ligne pour donner la possibilité d'envoyer des messages de manière synchrone
        routing:
            Symfony\Component\Mailer\Messenger\SendEmailMessage: sync # envoit les emails de manière synchrone
            ...
```

## LOGIN

- créer l'authentification :
```
symfony console make:auth
```
- 1
- LoginFormAuthenticator
- SecurityController
- yes
- pour se déconnecter :
```HTML
<a href="{{ path('app_logout') }}"></a>
```

## REGISTER

- créer l'entité User :
```
symfony console make:user
```
- ajouter des champs à l'entité User:
```
symfony console make:entity User
```
- migration
- créer le formulaire d'inscription :
```
symfony console make:registration-form
```
- si on choisit d'activer la validation des adresses mail : faire une migration (pour le champ is_verified)
- installer le bundle de vérification d'email :
```
composer require symfonycasts/verify-email-bundle
```
- modifier la dernière redirection après la validation de l'adresse email (RegistrationController::verifyUserEmail())
- gérer l'affichage des messa   ges flash (register.html.twig, ...)
- personnaliser le formaulaire, le controller et les templates
- migration pour générer la propriété User->isVerified


- gestion du mot de passe

## SÉCURITÉ - DROITS - ACCÈS - HIÉRARCHIE

- dans config/packages/security.yaml :
```YAML
access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
    ...
role_hierarchy:
    ROLE_ADMIN: ROLE_USER
    ROLE_SUPER_ADMIN: ROLE_ADMIN
```
- afficher du code selon un rôle :
```
{% if is_granted('LE_ROLE') %}
    le_code_ici
{% endif %}
```

## PAIEMENT STRIPE

- créer un controller (et la vue associée) :
```
symfony console make:controller Payment
```
- créer un compte Stripe (si ce n'est pas déjà fait) : https://stripe.com/fr
- installer le bundle (back) :
```
composer require stripe/stripe-php
```
- ajouter le script à la page de paiement (front) :
```HTML
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('LA_CLE_PUBLIQUE');
    stripe.redirectToCheckout({
        sessionId: '{{ sessionId }}'
    })
</script>
```

## HÉBERGEMENT

- vérifier si PHP 8 et MySQL 5.7
- si possible accès SSH

### FTP

- sur le site en local => passer en prod et vider le cache
- envoyer les fichiers via FileZilla (cPanel) dans le dossier www (ou htdocs ou public_html) de l'hébergeur
- modifier les infos dans le .Env hébergé (accès base de données, ...)
- importer la base de données (si MySQL 5.6 => changer roles pour l'entité User en text et non json puis migration)
- tester

### SSH

- dans un nouveau terminal, se connecter au serveur :
```
ssh LE_LIEN_SSH_DONNE_AVEC_L_HEBERGEMENT
```
- vérifier la présence de Git (git --version) et de Composer (composer --version)
- si nécessaire, installer Composer à la racine de l'hébergement : se rendre sur getcomposer.org/download et exécuter les 4 commandes
- se rendre dans le dossier qui contiendra l'application :
```
cd LE/CHEMIN/DU/DOSSIER (ex.: cd www/projet)
```
- initialiser le dépôt git local (sur le serveur, la première fois uniquement) :
```
git init
```
- lier le dépôt local au dépôt distant (la première fois uniquement) :
```
git remote add origin LIEN_DU_DEPOT_GITHUB
```
- télécharger le contenu du dépôt GitHub :
```
git pull origin main
```
- vérifier/modifier le .env (prod, BDD, ...)
- installer les dépendances :
```
composer install (ou php ~/composer.phar install, selon l'hébergeur)
```
- si MySQL 5.6, la première fois, mettre à jour le schéma de la base de données (pour le champ roles en json) :
```
php bin/console doctrine:schema:update --force
```
- mettre à jour la base de données :
```
php bin/console doctrine:migrations:migrate
```
- vider le cache :
```
php bin/console cache:clear
```

## COMMANDES UTILES

- vider le cache (Symfony) :
```
symfony console cache:clear
```





## À VOIR

[x] envoyer un mail avec Google (contact et register)
[x] messages flash
[x] renforcer le mot de passe (RollerWorks)
[x] gestion du panier en session (CartService)
[ ] tunnel d'achat (gestion adresse, paiement avec Stripe)
[ ] bases de Git
