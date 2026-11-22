#!/usr/bin/env bash
#
# Provisionne pms.diginova.cm sur ce serveur. Idempotent : relancable sans risque.
# Usage : sudo bash deploy/provision.sh
#
set -euo pipefail

DOMAIN=pms.diginova.cm
APP_USER=saturnin
# Adresse de notification Let's Encrypt (expiration de certificat).
# A surcharger : LE_EMAIL=vous@domaine.cm sudo -E bash deploy/provision.sh
LE_EMAIL="${LE_EMAIL:-admin@diginova.cm}"
SRC="/home/${APP_USER}/PROPERTY-MANAGEMENT-SYSTEM"
DEST=/var/www/PROPERTY-MANAGEMENT-SYSTEM
VHOST=/etc/apache2/sites-available/${DOMAIN}.conf

[ "$(id -u)" -eq 0 ] || { echo "A lancer avec sudo." >&2; exit 1; }

echo "==> 1/5 Emplacement de l'application"
if [ -d "$DEST" ]; then
  echo "    $DEST existe deja, rien a deplacer."
elif [ -d "$SRC" ]; then
  mv "$SRC" "$DEST"
  echo "    deplace : $SRC -> $DEST"
else
  echo "    ERREUR : ni $DEST ni $SRC n'existent." >&2; exit 1
fi

echo "==> 2/5 Droits fichiers"
chown -R "${APP_USER}:www-data" "$DEST"
find "$DEST" -type d -exec chmod 2775 {} +
find "$DEST" -type f -exec chmod 0664 {} +
chmod 0775 "$DEST/artisan"
chmod -R 2775 "$DEST/storage" "$DEST/bootstrap/cache"
chmod 0600 "$DEST/.env"
chown "${APP_USER}:${APP_USER}" "$DEST/.env"

echo "==> 3/5 Vhost Apache"
cat > "$VHOST" <<VHOSTEOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    DocumentRoot ${DEST}/public

    <Directory ${DEST}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/pms_error.log
    CustomLog \${APACHE_LOG_DIR}/pms_access.log combined
</VirtualHost>
VHOSTEOF
a2ensite "${DOMAIN}.conf" >/dev/null
apache2ctl configtest
systemctl reload apache2
echo "    vhost actif, Apache recharge"

echo "==> 4/5 Certificat TLS (Let's Encrypt)"
if [ -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
  echo "    certificat deja present, renouvellement gere par certbot.timer"
else
  certbot --apache -d "${DOMAIN}" -d "www.${DOMAIN}" \
    --non-interactive --agree-tos --redirect -m "$LE_EMAIL"
fi

echo "==> 5/5 Optimisation Laravel"
sudo -u "${APP_USER}" bash -c "cd '${DEST}' && php artisan config:cache && php artisan route:cache && php artisan view:cache"

echo
echo "Termine. Verifier : curl -I https://${DOMAIN}/"
