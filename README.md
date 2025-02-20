[![Build Status](https://travis-ci.org/wmde/fundraising-application.svg?branch=main)](https://travis-ci.org/wmde/fundraising-application)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/wmde/FundraisingFrontend/?branch=main)

User facing application for the [Wikimedia Deutschland](https://wikimedia.de) fundraising.

<!-- toc -->

* [Installation](#installation)
* [Running the application](#running-the-application)
* [Configuration](#configuration)
* [Running the tests](#running-the-tests)
* [Emails](#emails)
* [Database](#database)
* [Frontend development](#frontend-development)
* [Updating the dependencies](#updating-the-dependencies)
* [Deployment](#deployment)
* [Project structure](#project-structure)
* [See also](#see-also)

<!-- tocstop -->

<!--
The table of contents is autogenerated with https://github.com/jonschlinkert/markdown-toc
Update the table of contents with the following command:
markdown-toc --maxdepth 2 --bullets '*' -i README.md
-->

## Installation

### First setup

For development, you need to have Docker and docker-compose installed. You need at least Docker Version >= 17.09.0 and docker-compose version >= 1.17.0. If your OS does not come with the right version, please use the official installation instructions for [Docker](https://docs.docker.com/install/) and [docker-compose](https://docs.docker.com/compose/install/). You don't need to install other dependencies (PHP, Node.js, MariaDB) on your machine.

Get a clone of our git repository and then run:

    make setup

This will

- Install PHP dependencies
- Copy a basic configuration file. See section [Configuration](#configuration) for more details on the configuration.
- Generate the [Doctrine Proxy classes](https://www.doctrine-project.org/projects/doctrine-orm/en/2.9/reference/advanced-configuration.html#proxy-objects)
- Download and install the frontend assets from
    [fundraising-app-frontend](https://gitlab.com/fun-tech/fundraising-app-frontend)

### Installing the current dependencies

	make install-php

Will install the dependencies currently specified in `composer.lock`. Use
this command whenever you check out a branch that has changes to
`composer.lock`.

## Running the application

    docker-compose up

You can now access the application at [http://localhost:8082/](http://localhost:8082/). Hit Ctrl-C to stop the server.

If you want to run the application in the background, use the commands

	make up-app

to start it and

	make down-app

to stop it.

To start the application with a debug port open, (see ["How to use XDebug and PHPStorm"](doc/HOWTO_Use_Xdebug_and_PHPStorm.md)) run,

	make up-debug

## Configuration

The web and CLI entry points of the application check for the `APP_ENV` environment variable.
If it not set, the application assumes the value `dev`. Each environment must have a corresponding configuration
file in `app/config`, following the name pattern of `config.ENVIRONMENTNAME.json`. See the section "Running in different
environments" below to see how to set `APP_ENV`.

You can add local modifications by adding a file that follows the name pattern of `config.ENVIRONMENTNAME.local.json`.

The application merges the values from the configuration files with the default values from the file
`app/config/config.dist.json`.

### Fronted development
If you want to work on the client-side code of the application, you need
to load it from a different source, e.g. the
development server of `fundraising-app-frontend` running on port 7072. The
configuration has the setting `assets-path` that you can point to a
different path or even a URL.

The following setting will point your application to the frontend
development server:

    "assets-path": "http://localhost:7072"

The application will show a blank white page if the browser can't find the
assets.

### Create a test configuration that uses the MariaDB database

To speed up the tests when running them locally, the tests use SQLite
instead of MariaDB. To run the tests with the real database, add the file
`app/config/config.test.local.json` with the following content:

```json
{
    "db": {
        "driver": "pdo_mysql",
        "user": "fundraising",
        "password": "INSECURE PASSWORD",
        "dbname": "fundraising",
        "host": "database",
        "port": 3306
    }
}
```

### Payments

For a fully working instance with all payment types and working templates you need to fill out the following
configuration data:

    "operator-email"
    "operator-displayname-organization"
    "operator-displayname-suborganization"
    "paypal-donation"
    "paypal-membership"
    "creditcard"

### Content

The application needs a copy of the content repository at https://github.com/wmde/fundraising-frontend-content to work properly.
In development, the content repository is a composer dev-dependency. If you *want* to put the content repository in another place, you need to configure the `i18n-base-path` to point to it.
The following example shows the configuration when the content repository is at the same level as the application directory:

    "i18n-base-path": "../fundraising-frontend-content/i18n"

### A/B test campaigns.

For more information on how to set up the campaigns see "[How to Create an A/B Test](doc/HOWTO_Create_an_a_b_test.md).

The campaign definitions are in the `app/config` directory. You can tell the application which files to use by editing
the `campaigns` value in `app/config/config.ENVIRONMENTNAME.json`. The campaign configuration files will be merged on
top of each other.

### Running in different environments
By default, the configuration environment is `dev` and the configuration file is `config.dev.json`. If you want to
change that, you have to pass the environment variable to `make`, `docker` and `docker-compose` commands.

    make ci APP_ENV=prod

For `docker-compose` you can either put create a file called `.env` in the application directory and, with the contents of

    APP_ENV=prod

If you want to override the defaults in the `.env` file, you set the variable in your shell like this:

    export APP_ENV=prod

If you run a single docker container, you can pass the variable with the `-e` flag:

    docker run -e APP_ENV=prod php some_script.php

Valid environment names are

* `dev` - development environment, mostly for local development
* `test` - unit testing environment
* `uat` - user acceptance testing
* `prod` - production

**Note:** PHPUnit tests are always run in the `test` environment configuration, regardless of `APP_ENV`!

## Running the tests

### Full CI run

    make ci

This will run the tests, check the code style, do the static analysis and
check the configuration files.

### Run only tests only

    make test

If you want to run a specific folder with tests or just one file, use the `TEST_DIR` parameter. Examples:

	# Run the unit tests
    make phpunit TEST_DIR=tests/Unit

	# Run a specific test file
    make phpunit TEST_DIR=tests/EdgeToEdge/Routes/AddDonationRouteTest.php


### Run only code style checks

    make cs

If you want to fix the code style violations, run

	make fix-cs

### phpstan

We perform static code analysis with [phpstan](https://github.com/phpstan/phpstan/) during runs of `make ci`.

In the absence of dev-dependencies (i.e. to simulate the vendor/ code on production) you can run phpstan with the commands

    docker build -t wmde/fundraising-frontend-phpstan build/phpstan
    docker run -v $PWD:/app --rm wmde/fundraising-frontend-phpstan analyse -c phpstan.neon --level 1 --no-progress cli/ contexts/ src/

These tasks are also performed during the [travis](.travis.yml) runs.

## Emails

You can inspect all emails sent by the application via [mailhog](https://github.com/mailhog/MailHog)
at [http://localhost:8025/](http://localhost:8025/)

## Database

### Resetting the database in your local environment

To drop the database and rebuild it from scratch the database, you need to stop the database container, delete the volume `db-storage` defined in `docker-compose.yml` and start the database container again.

You can shut down all containers and delete all volumes with the command

    docker-compose down -v

The next time you run `docker-compose up`, the database container will
process all SQL files in [.docker/database](.docker/database).

### Accessing the database with the command line client

To start the command line client, use the following commands:

	docker-compose up -d database
	docker-compose exec database mysql -u fundraising -p"INSECURE PASSWORD" fundraising

### Accessing the database from your host machine

If you want to use a different client for accessing the database, you need
to connect to port 3307.


### Database migrations

Out of the box, the database should be in a usable state for local development.

If you make changes to the database schema, you have to do two things:

1. Create a [Doctrine
   migration](https://www.doctrine-project.org/projects/migrations.html)
   script for the production database. Store the migration scripts in the
   `migrations` directory of the bounded context where you made the
   changes.
2. In your development environment, create the new database schema
   definitions with the `make generate-database-schema` command. This will
   refresh the file `./docker/database/01_Database_Schema.sql`. Then 
   restart the container environment while dropping the database volume.
   See section "Resetting the database in your local environment" below.

#### Migrations CLI and configuration

The configuration file for migrations is in `app/config/migrations.php`

The `bin/doctrine` CLI command comes with the pre-configured migrations
command for the Fundraising App. Wherever the Doctrine migrations
documentation mentions running the command
`vendor/bin/doctrine-migrations`, use the command `bin/doctrine` instead.
E.g. `bin/doctrine migrations:status`.

In your Docker-based development environment, run the command in the `app`
container, using `docker-compose exec`. The container environment must be
running for this to work. Example:

```
docker-compose exec app bin/doctrine migrations:status
```

#### Running migrations on the server

Have a look the [deployment documentation](https://github.com/wmde/fundraising-infrastructure/blob/master/docs/deployment/Fundraising_Application.md) on how to run the migrations on the server.

**Note:** If you're getting errors that the configuration file was nor found, make sure to set `APP_ENV` to the right value.
See section "Running in different environments" in this document.


### Accessing the database from a Docker image

If you want to connect to the database container from another docker
container that's not part of the `docker-compose.yml` configuration (for
example to use a tool like [Adminer](https://www.adminer.org/) or
[PHPMyAdmin](https://www.phpmyadmin.net/)), you need to put that container
in the same virtual network as the rest of the application containers.
With the command

    docker network ls

you can list all networks. There will be one network name ending in `fundraising_proxy` (the prefix is probably the
directory name where you checked out this repository).

Next up is finding out the full name of the database container with the command

    docker ps

The database container will be the one ending in `_database_1`. The prefix is probably the directory name where you checked out this repository.

Copy the full network name and container name and use them instead of the placeholders `__CONTAINER_NAME__` and
`__NETWORK_NAME__` in the following command to run PHPMyAdmin, port 8099:

    docker run -it --link __CONTAINER_NAME__:db --net __NETWORK_NAME__ -p 8099:80 phpmyadmin/phpmyadmin

### Importing the address completion data

To import the German postcode database, you need to place it in
`.docker/database/00.postcodes.sql`. The database container will pick it up
when running for the first time (when it creates the volume `db-storage`).
This happens when you run `docker-compose up` for the first time or when
you reset the database (see above). Depending on the speed of you machine,
the import will take up to 10 minutes. Watch the output of the
`database` container so see when the database has finished importing.


## Frontend development

By default, the application uses pre-built frontend assets from [fundraising-app-frontend](https://gitlab.com/fun-tech/fundraising-app-frontend). To update the assets to the newest version, run

    make download-assets

To get the pre-built assets from a specific branch of that repository,
run

    make download-assets ASSET_BRANCH=your_branch_name


If you want to load the assets from a different source, e.g. the
development server of `fundraising-app-frontend` running on port 7072, you
need to add the following line to your `config.dev.json` file:

    "assets-path": "http://localhost:7072"

The HTML templates will prefix every asset (CSS,
JavaScript) reference with the value of `assets-path`.

## Updating the dependencies

To update *all* the PHP dependencies, run

    make update-php

To update only the messages in the application and emails, update the
[fundraising-frontend-content dependency](https://github.com/wmde/fundraising-frontend-content) with the command

	make update-content

For updating an individual PHP dependency, use the command line

    docker run --rm -it -v $(pwd):/app -u $(id -u):$(id -g) registry.gitlab.com/fun-tech/fundraising-frontend-docker:composer composer update PACKAGE_NAME

and replace the `PACKAGE_NAME` placeholder with the name of your package.

## Deployment

For an in-depth documentation how to deploy the application on our servers,
see [the deployment documentation](https://github.com/wmde/fundraising-infrastructure/blob/master/docs/deployment/Fundraising_Application.md).


## Project structure

This app and its used Bounded Contexts follow the architecture rules outlined in [Clean Architecture + Bounded Contexts](https://www.entropywins.wtf/blog/2018/08/14/clean-architecture-bounded-contexts/).

![Architecture diagram](https://user-images.githubusercontent.com/146040/70377680-72409a00-1917-11ea-8d5f-edd75fb4c5cb.png)

Used Bounded Contexts:

* [Donation Context](https://github.com/wmde/fundraising-donations)
* [Membership Context](https://github.com/wmde/fundraising-memberships)
* [Payment Context](https://github.com/wmde/fundraising-payments)
* [Address Change Context](https://github.com/wmde/fundraising-address-change)
* [Subscription Context](https://github.com/wmde/fundraising-subscriptions)

### Production code layout

* `src/`: code not belonging to any Bounded Context, framework agnostic if
        possible
    * `Factories/`: application factories used by the framework, including top level factory `FFFactory`
    * `Presentation/`: presentation code, including the `Presenters/`
    * `Validation/`: validation code
* `vendor/wmde/$ContextName/src/`: framework agnostic code belonging to a specific Bounded Context
    * `Domain/`: domain model and domain services
    * `UseCases/`: one directory per use case
    * `DataAccess/`: implementations of services that binds to database, network, etc
    * `Infrastructure/`: implementations of services binding to cross cutting concerns, i.e. logging
* `web/`: web accessible code
    * `index.php`: HTTP entry point
    * `skins`: Asset files (CSS, JavaScript, images, fonts) for different
    skins
* `app/`: contains application-specific configuration and all framework (Symfony) dependent code
    * `Controllers/`: Symfony Controllers
	* `EventHandlers`: "Middleware" code that performs tasks before or
		after HTTP request handling
    * `config/`: configuration files
        * `config.dist.json`: default configuration
        * `config.test.json`: configuration used by integration and system tests (gets merged into default config)
        * `config.test.local.json`:  instance specific (gitignored) test config (gets merged into config.test.json)
        * `config.development.json`: instance specific (gitignored) production configuration (gets merged into default config)
* `config/`: Symfony configuration files
* `cli/`: Command line commands, integrated into the Symfony console
* `var/`: Ephemeral application data
    * `log/`: Log files (in debug mode, every request creates a log file)
    * `cache/`: Cache directory for Twig templates and Symfony DI
       containers

### Test code layout

The test directory structure (and namespace structure) mirrors the production code. Tests for code
in `src/` and `app/` is in `tests/`.

Tests are categorized by their type. To run only tests of a given type, you can use one of the
test suites defined in `phpunit.xml.dist`.

* `Unit/`: small isolated tests (one class or a small number of related classes)
* `Integration/`: tests combining several units
* `EdgeToEdge/`: edge-to-edge tests (fake HTTP requests to the framework)
* `System/`: tests involving outside systems (i.e., beyond our PHP app and database)
* `Fixtures/`: test doubles (stubs, spies and mocks)

If you need access the `FunFunFactory` in your non-unit tests, for instance to interact with
persistence, you should inherit from `KernelTestCase` and get the Factory
from the container.

#### Test type restrictions

<table>
    <tr>
        <th></th>
        <th>Network</th>
        <th>Framework (Symfony)</th>
        <th>Top level factory</th>
        <th>Database and disk</th>
    </tr>
    <tr>
        <th>Unit</th>
        <td>No</td>
        <td>No</td>
        <td>No</td>
        <td>No</td>
    </tr>
    <tr>
        <th>Integration</th>
        <td>No</td>
        <td>No</td>
        <td>Discouraged</td>
        <td>Yes</td>
    </tr>
    <tr>
        <th>EdgeToEdge</th>
        <td>No</td>
        <td>Yes</td>
        <td>Yes</td>
        <td>Yes</td>
    </tr>
    <tr>
        <th>System</th>
        <td>Yes</td>
        <td>Yes</td>
        <td>Yes</td>
        <td>Yes</td>
    </tr>
</table>

### Other directories

* `.docker/`: Configuration and Dockerfiles for the development environment

## See also

* [Rewriting the Wikimedia Deutschland fundraising](https://www.entropywins.wtf/blog/2016/11/24/rewriting-the-wikimedia-deutschland-funrdraising/) - blog post on why we created this codebase
* [Implementing the Clean Architecture](https://www.entropywins.wtf/blog/2016/11/24/implementing-the-clean-architecture/) - blog post on the architecture of this application
