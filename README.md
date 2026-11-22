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

php artisan serve          # ou via le vhost pms.diginova.cm
npm run dev                # HMR Vite
npm run build              # build de production
php artisan test           # tests
```

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

Le vhost, les droits fichiers et le certificat TLS sont installes par
[`deploy/provision.sh`](deploy/provision.sh), a lancer **une seule fois** avec sudo :

```bash
sudo bash ~/PROPERTY-MANAGEMENT-SYSTEM/deploy/provision.sh
```

Le script est idempotent : il deplace l'application vers `/var/www`, applique les
droits, cree le vhost `pms.diginova.cm`, puis demande un certificat Let's Encrypt.

## DNS

`pms.diginova.cm` et `www.pms.diginova.cm` -> `155.117.46.218` (propagation faite).
