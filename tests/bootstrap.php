<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

/*
 * Ce repertoire sert a la fois la production et le developpement : le code est
 * servi par Apache depuis /var/www et c'est aussi la copie de travail.
 *
 * Quand la configuration est mise en cache (php artisan config:cache), Laravel
 * ignore .env et les variables de phpunit.xml. Les tests viseraient alors la
 * base applicative, que RefreshDatabase viderait sans avertissement.
 *
 * On refuse donc de demarrer dans cet etat, plutot que de detruire des donnees.
 */
if (file_exists(dirname(__DIR__).'/bootstrap/cache/config.php')) {
    fwrite(STDERR, <<<'TXT'

  REFUS : la configuration est en cache.

  Les tests liraient la base applicative au lieu de la base de test, et
  RefreshDatabase la viderait.

  Lancer :  composer test
  (equivaut a : php artisan config:clear && php artisan test && php artisan config:cache)


TXT);

    exit(1);
}

/*
 * Seconde barriere, independante de la premiere : si une base est imposee par
 * l'environnement, elle doit etre une base de test.
 */
$database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');

if (is_string($database) && $database !== '' && ! str_ends_with($database, '_test')) {
    fwrite(STDERR, sprintf(
        "
  REFUS : DB_DATABASE vaut \"%s\", qui n'est pas une base de test.
"
        ."  Les bases de test se terminent par _test (ex. pms_test).

",
        $database
    ));

    exit(1);
}
