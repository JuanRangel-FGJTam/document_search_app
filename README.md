# DocumentLost App Admin

## Requirements

-   PHP 8.2
-   MySQL 8
-   NodeJS 20
-   npm 9.8.0
-   [Composer](https://getcomposer.org/)

## Frameworks

-   [Laravel 11.9](https://getcomposer.org/)
-   [VueJS 3.3.13](https://vuejs.org/guide/introduction.html)

## Composer Dependencies

-   [InertiaJS](https://inertiajs.com/)
-   [Sanctum](https://laravel.com/docs/11.x/sanctum)
-   [phpspreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/)
-   [Scout]

## NPM Dependencies

-   [Vue-Toastificacion](https://vue-toastification.maronato.dev/)

## Setup

-   Clone the project

```bash
    git clone https://github.com/fgjtam-dgtit-jhovan/document_lost_admin_app.git
```

-   Go to the project directory

```bash
    cd document_lost_admin_app
```

-   Create `.env` file

```bash
    cp .env.example .env
```

-   Create and configure database configuration file `docker-compose.override.yml`

```bash
    nano docker-compose.override.yml
```

-   Create Docker Image

```bash
    docker build --build-arg uid=1000 --build-arg user=myuser -t image_name .
```

-   Execute Docker Image

```bash
    docker compose up
    docker-compose exec app php artisan key:generate
    docker compose exec app bash
```

-   Run the migrations to create the tables in the database

```bash
    php artisan migrate
```

-   Install composer dependencies

```bash
    composer install
```

-   Install JavaScript dependencies

```bash
    npm install
```

-   Build JS assets (Production environment)

```bash
    npm run build
```

## Meilisearch

If you are installing Scout into an existing project, you may already have database records you need to import into your indexes. Scout provides a scout:import Artisan command that you may use to import all of your existing records into your search indexes:

```bash
php artisan scout:import "App\Models\Misplacement"

```

The flush command may be used to remove all of a model's records from your search indexes:

```bash
php artisan scout:flush "App\Models\Misplacement"

```

If you modify the `config/scout.php` configuration file you need to update the index:

```bash
php artisan scout:sync-index-settings
```

## Variables in .env file

-   Add variables in `.env` file for connection to the document_lost_app database

```bash
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=extravios
DB_USERNAME=remoteusr
DB_PASSWORD=

```

-   Replace value of variables in `.env` file to send emails

```bash
MAIL_MAILER=
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
```

-   Add variables in `.env` file

```bash
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=
MEILISEARCH_KEY=
```



