# Property Management System

SaaS de gestion hoteliere multi-hotels, destine au Cameroun et a l'Afrique
francophone : reception, restaurant et comptabilite depuis une seule application.

En ligne sur <https://pms.diginova.cm>.

## Etat actuel

Seules l'authentification et le socle multi-hotels sont implementes.

| Implemente | Pas encore |
| --- | --- |
| Connexion e-mail / mot de passe, « rester connecte » | Planning des chambres, reservations |
| Refus des comptes desactives et des hotels suspendus | Restaurant, blanchisserie, banquets |
| Limitation a 5 tentatives par minute (e-mail + IP) | Comptabilite et pieces justificatives |
| Mot de passe oublie et reinitialisation, mails en francais | Paiements carte / Orange Money / MTN MoMo |
| Tableau de bord minimal, deconnexion | Creation de compte en ligne (`/register` affiche « Bientot disponible ») |
| Interface francais / anglais | Roles reellement appliques (les valeurs existent, aucune autorisation n'en depend encore) |

## Stack

| Couche | Choix |
| --- | --- |
| Back-end | Laravel 12 (PHP 8.3) |
| Front-end | Vue 3.5 + Inertia 2 + TypeScript |
| Styles | Tailwind CSS 3.4, police Inter, palette de marque vert emeraude |
| Build | Vite 6 |
| Base de donnees | MySQL 8.0 |
| Tests | Pest 3 |
| Serveur web | Apache 2.4 (mod_php) |

Ce choix suit l'infrastructure existante : le serveur heberge deja une vingtaine
d'applications Laravel/Inertia derriere Apache, sans Node en production.

## Nom du produit

Le nom affiche (logo, encart d'essai, pied de page, titre de l'onglet) vient d'un
seul endroit : `config/branding.php`, surchargeable par la variable
d'environnement `BRAND_NAME`. Aucun composant ne contient le nom du produit.

```dotenv
BRAND_NAME="Votre marque"
```

## Identite visuelle

La palette officielle est definie une seule fois, en haut de
[`tailwind.config.js`](tailwind.config.js). Les composants n'utilisent que les
classes correspondantes, donc changer une couleur ne demande qu'une edition de
ce fichier.

| Role | Valeur | Classes |
| --- | --- | --- |
| Bleu marine | `#082b55` | `brand-800` |
| Bleu profond | `#0d3768` | `brand-700` |
| Or | `#f2ae18` | `gold-500` |
| Gris clair | `#eef3fa` | `field` |
| Fond de page | `#f6f9fd` | `page` |
| Texte secondaire | `#526b8c` | `ink` |

La palette or s'appelle `gold` et non `accent` : le preset shadcn possede deja
une cle `accent`, et la lui reprendre casserait tous les composants qui en
dependent.

Police : Montserrat, chargee depuis fonts.bunny.net.

### Fichiers de marque

- `public/images/pms-logo.png` : logo, utilise par `BrandLogo.vue`.
- `public/images/login-cover.jpg` : photographie du panneau de connexion.

Les deux viennent du pack d'elements graphiques et sont des extractions de la
maquette, pas des originaux haute resolution. Une version vectorielle du logo
et la photographie source les remplaceraient avantageusement.

## Developpement

Le developpement se fait **directement sur le serveur** (`ssh website-server`),
pour disposer du meme PHP, de la meme base et du meme Apache que la production.

```bash
ssh website-server
cd /var/www/PROPERTY-MANAGEMENT-SYSTEM

npm run dev                # HMR Vite
npm run build              # build de production
composer test              # tests
npm run lint               # lint du front
npm run format             # formatage du front
```

Le site est servi par Apache : pas besoin de `php artisan serve`. Apres
modification d'un fichier de configuration, d'une route ou de `.env`, rafraichir
les caches : `php artisan optimize` (ou `php artisan optimize:clear`).

### Base de donnees

MySQL tourne dans un conteneur dedie, expose sur `127.0.0.1:3318` :

```bash
docker ps --filter name=pms-db
docker start pms-db        # si arrete
```

Base applicative `pms`, base de test `pms_test`, utilisateur `pms`. Le mot de
passe applicatif est dans `.env`, non versionne ; le mot de passe root du
conteneur est dans `~/.pms-db-root.txt` (hors depot, chmod 600).

Migrations et donnees de demonstration :

```bash
php artisan migrate
php artisan db:seed
```

### Comptes de demonstration

`DemoSeeder` cree l'hotel « Hotel Demo Yaounde » (en essai, 14 jours) et trois
comptes partageant le mot de passe `password` :

| Compte | Role | Etat |
| --- | --- | --- |
| `owner@demo.test` | owner | actif |
| `reception@demo.test` | receptionist | actif |
| `inactif@demo.test` | receptionist | desactive, sert a verifier le refus |

> Ces comptes ont un mot de passe connu de tous. Ils sont faits pour une
> demonstration, pas pour une mise en service reelle : supprimez-les avant
> d'ouvrir l'application a de vrais clients
> (`php artisan tinker --execute="App\Models\User::where('email','like','%@demo.test')->delete();"`).

### Tests : toujours `composer test`

Le repertoire sert a la fois la production et le developpement. La configuration
y est mise en cache, et dans cet etat Laravel ignore `phpunit.xml` : un
`php artisan test` direct viserait la base applicative `pms`, que
`RefreshDatabase` viderait.

`tests/bootstrap.php` refuse donc de demarrer tant que le cache est present, et
`composer test` enchaine `optimize:clear`, les tests, puis `optimize`. La purge
porte sur tous les caches, pas seulement la configuration : un cache de routes
perime ferait tourner les tests sur des routes qui ne sont plus celles du code.
Seconde barriere : toute base dont le nom ne finit pas par `_test` est rejetee.

## Traductions

Aucun texte visible n'est ecrit dans un composant. Les lignes vivent dans
`lang/fr` et `lang/en` ; `HandleInertiaRequests` partage les groupes utiles au
front, et le composable
[`useTranslations`](resources/js/composables/useTranslations.ts) expose `t()`.
Le placeholder `:brand` est toujours disponible.

Le selecteur FR / EN appelle `GET /locale/{locale}`, qui memorise le choix en
session. A defaut, la langue du compte puis `APP_LOCALE` s'appliquent.

## Mise en production

L'application est en ligne sur <https://pms.diginova.cm> (HTTP redirige en 301,
certificat Let's Encrypt renouvele automatiquement par `certbot.timer`).

Le vhost, les droits fichiers et le certificat TLS ont ete installes par
[`deploy/provision.sh`](deploy/provision.sh), a lancer **une seule fois** avec sudo :

```bash
sudo bash /var/www/PROPERTY-MANAGEMENT-SYSTEM/deploy/provision.sh
```

Le script est idempotent : il deplace l'application vers `/var/www`, applique les
droits, cree le vhost `pms.diginova.cm`, puis demande un certificat Let's Encrypt.
Pour recevoir les alertes d'expiration ailleurs :
`LE_EMAIL=vous@domaine.cm sudo -E bash deploy/provision.sh`.

## DNS

`pms.diginova.cm` et `www.pms.diginova.cm` -> `155.117.46.218` (propagation faite).

Sur ce serveur mutualise, un domaine sans vhost dedie est servi par le premier
vhost `*:80` charge. Un sous-domaine peut donc sembler « deja en ligne » alors
qu'il affiche une autre application.
