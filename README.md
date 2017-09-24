# INOWAS API

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/dbc76ad5-719f-4b3a-a3c0-5b9848309e90/mini.png)](https://insight.sensiolabs.com/projects/dbc76ad5-719f-4b3a-a3c0-5b9848309e90)
[![Build Status](https://travis-ci.org/inowas/inowas.svg?branch=dev)](https://travis-ci.org/inowas/inowas)
[![Coverage Status](https://coveralls.io/repos/github/inowas/inowas/badge.svg?branch=dev)](https://coveralls.io/github/inowas/inowas?branch=dev)
[![Code Climate](https://codeclimate.com/github/inowas/inowas/badges/gpa.svg)](https://codeclimate.com/github/inowas/inowas)
[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)

## INSTALLATION WITH DOCKER

We are providing docker-container and a docker-compose-script to build the entire backend.
Please clone the [repo](https://github.com/inowas/docker-inowas-api) and create a .env-file.

```
cp .env.dist .env
```

adapt the folders and database-credentials to the data in

```
./app/config/parameters.yml
```

Then go to the docker-inowas-api folder and run

```
docker-compose up -d
```

Connect to the php-machine an run the relevant scripts:

```
docker-compose exec php bash
```

Install compose packages

```
composer install
```

Create database-tables

```
./build/build.on.docker.sh
```

Run migrations

```
cs inowas:es:migrate 1
```

```
cs inowas:es:migrate 2
```

## API-DOCUMENTATION

The API-Documentation is the Swagger-File here: `/spec/swagger.yaml`

An easy way to render the Documentation with Sandbox is the swagger-editor [online](http://editor.swagger.io/#/)
You can clone the [repo](https://github.com/swagger-api/swagger-editor) and run

```
npm install
npm start
```

