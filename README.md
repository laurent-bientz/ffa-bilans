# FFA - Bilans

## Setup

Lancer les commandes suivantes dans un terminal :

```console
git clone git@github.com:laurent-bientz/ffa-bilans.git
git config core.filemode false

sudo chown 1000:1000 -R .
sudo chmod 777 -R .

cp docker/db/.env.dist docker/db/.env
cp docker/php/.env.dist docker/php/.env
```

## Boostrap

Lancer les commandes suivantes dans un terminal :

```console
docker-compose up -d
docker-compose exec php composer install

docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:schema:drop -f
docker-compose exec php php bin/console doctrine:schema:update --force
docker-compose exec php php bin/console cache:clear
```

Le site doit être visible dans le navigateur sur `http://localhost`.

## Scrape

```console
docker-compose exec php php bin/console app:ffa:scrape {trialId} [{year}] [{gender}]
```

## Demo

➡️ [https://ffa.bientz.com](https://ffa.bientz.com)

## Crontabs

```console
0 0 1 9 * cd {docroot} && php bin/console app:ffa:scrape 299 $(date +%Y)
15 0 1 9 * cd {docroot} && php bin/console app:ffa:scrape 295 $(date +%Y) M
30 0 1 9 * cd {docroot} && php bin/console app:ffa:scrape 295 $(date +%Y) F
45 0 1 9 * cd {docroot} && php bin/console app:ffa:scrape 271 $(date +%Y) M
0 1 1 9 * cd {docroot} && php bin/console app:ffa:scrape 271 $(date +%Y) F
15 1 1 9 * cd {docroot} && php bin/console app:ffa:scrape 261 $(date +%Y) M
30 1 1 9 * cd {docroot} && php bin/console app:ffa:scrape 261 $(date +%Y) F
45 1 1 9 * cd {docroot} && php bin/console c:c && php bin/console app:cache:warmup && /etc/init.d/php8.4-fpm restart
```