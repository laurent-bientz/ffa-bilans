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
docker-compose exec php php bin/console app:ffa:scrape {trialId} {year}
```
