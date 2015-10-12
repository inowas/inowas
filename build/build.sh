#!/usr/bin/env bash

DIR=/var/www/html
SQL_DIR=$DIR/build/sql

cd $DIR
app/console doctrine:database:drop --force
app/console doctrine:database:create

su - postgres -c "psql inowas_entities < "$SQL_DIR"/structure.sql"

app/console doctrine:schema:create