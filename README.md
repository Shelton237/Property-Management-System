# Property Management System

Application de gestion immobiliere (biens, bailleurs, locataires, baux, quittances,
encaissements, charges, relances). Le perimetre fonctionnel detaille reste a cadrer :
ce depot contient pour l'instant le socle technique et l'authentification.

## Stack

| Couche | Choix |
| --- | --- |
| Back-end | Laravel 12 (PHP 8.3) |
| Front-end | Vue 3.5 + Inertia 2 + TypeScript |
| Styles | Tailwind CSS 3.4 + composants shadcn-vue |
| Build | Vite 6 |
| Base de donnees | MySQL 8.0 |
| Serveur web | Apache 2.4 (mod_php) |

Ce choix suit l'infrastructure existante : le serveur heberge deja une vingtaine
d'applications Laravel/Inertia derriere Apache, sans Node en production.

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
```

Le site est servi par Apache sur <https://pms.diginova.cm> : pas besoin de
`php artisan serve`. Apres modification d'un fichier de configuration ou de
`.env`, rafraichir le cache : `php artisan config:cache`.

### Tests : toujours `composer test`

Le repertoire sert a la fois la production et le developpement. La configuration
y est mise en cache, et dans cet etat Laravel ignore `phpunit.xml` : un
`php artisan test` direct viserait la base applicative `pms`, que
`RefreshDatabase` viderait.

`tests/bootstrap.php` refuse donc de demarrer tant que le cache est present, et
`composer test` enchaine proprement `config:clear`, les tests, puis
`config:cache`. Seconde barriere : toute base dont le nom ne finit pas par
`_test` est rejetee.

### Base de donnees

En developpement, MySQL tourne dans un conteneur dedie :

```bash
docker ps --filter name=pms-db
docker start pms-db        # si arrete
```

Il ecoute sur `127.0.0.1:3318` (base `pms`, utilisateur `pms`). Le mot de passe
applicatif est dans `.env`, non versionne. Le mot de passe root du conteneur est
dans `~/.pms-db-root.txt` (hors depot, chmod 600).

## Mise en production

L'application est en ligne sur <https://pms.diginova.cm> (HTTP redirige en 301,
certificat Let's Encrypt renouvele automatiquement par `certbot.timer`).

Le vhost, les droits fichiers et le certificat TLS ont ete installes par
[`deploy/provision.sh`](deploy/provision.sh), a lancer **une seule fois** avec sudo :

```bash
sudo bash ~/PROPERTY-MANAGEMENT-SYSTEM/deploy/provision.sh
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
