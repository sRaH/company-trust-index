# CompanyTrustIndex

A CompanyTrustIndex egy Symfony-alapú webalkalmazás cégek közösségi értékelésére. A felhasználók értékelést küldhetnek, a cégekhez tartozó átlagok és véleményszámok megtekinthetők, valamint a felület magyar és angol nyelven is használható.

## Követelmények

- PHP 8.5 vagy újabb
- Composer
- Node.js és npm
- Symfony CLI (ajánlott)
- PostgreSQL vagy MySQL adatbázis

## Telepítés

```bash
composer install
npm install
```

Állítsd be a lokális környezetet a `.env.local` fájlban, különösen a `DATABASE_URL` és az `APP_SECRET` értékét.

## Futtatás

Symfony CLI használatával:

```bash
symfony serve
```

Vite fejlesztői szerver külön terminálban:

```bash
npm run dev
```

Production assetek buildelése:

```bash
npm run build
```

A Makefile rövid parancsai szintén használhatók:

```bash
make install
make server
make build
```

## Adatbázis

Adatbázis létrehozása, majd a migrációk futtatása:

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate -n
```

Fejlesztési adatok betöltése:

```bash
php bin/console doctrine:fixtures:load -n
```

Ugyanezekre használhatók a következő Make célok:

```bash
make migrate
make fixtures
```

## Ellenőrzések

```bash
make test
make test-e2e
make lint
make coverage
```

## GitHub Actions

A GitHub Actions workflow minden pull requesten és `main` branchre történő push esetén lefuttatja az asset buildet, a lintet, a statikus elemzést és a PHPUnit teszteket. Sikeres `main` build után a Docker image `latest` taggel a GitHub Container Registry-be kerül:

```text
ghcr.io/<owner>/<repository>:latest
```

## Munkaidő napló

| Feladat                                   |          Idő |
|-------------------------------------------|-------------:|
| Projekt felmérése és alapkonfiguráció     |       ~1 óra |
| Backend, validáció és adatbázis-funkciók  |     ~1,5 óra |
| Felület, lokalizáció és stimulus funkciók |       ~1 óra |
| Tesztek                                   |       ~1 óra |
| **Összesen**                              | **~4,5 óra** |
