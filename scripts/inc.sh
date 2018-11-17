#!/bin/bash

# set -o errexit tells the shell to exit as soon as a command exits with non-zero status, i.e. fails
set -o errexit

# :- is an shell operator. If FS_ENV is set and not the empty string, use FS_ENV, otherwise use dev
export FS_ENV=${FS_ENV:-dev}

# user identification number of the current user
export UID

MYSQL_USERNAME=${MYSQL_USERNAME:-root}
MYSQL_PASSWORD=${MYSQL_PASSWORD:-root}

# BASH_SOURCE is an array with the filenames of the files that were called to get here
# so BASH_SOURCE[0] is the filename (with path) of this file
# different to $0 when this file is sourced with "." or source as in many of the scripts
dir=$(cd $(dirname ${BASH_SOURCE[0]}) && pwd)

function log-header() {
  # print a log header, take one argument as the printed title
  local text=$1;
  echo
  echo "============================================"
  echo "  $text"
  echo "============================================"
}

function dc() {
  $dir/docker-compose "$@"
}

function sql-query() {
  local database=$1 query=$2;
  dc exec -T db sh -c "mysql -p$MYSQL_PASSWORD $database -e \"$query\""
}

function sql-file() {
  local database=$1 filename=$2;
  echo "Executing sql file $FS_ENV/$database $filename"
  dc exec -T db sh -c "mysql -p$MYSQL_PASSWORD $database < /app/$filename"
}

function sql-dump() {
  dc exec -T db mysqldump -p$MYSQL_PASSWORD foodsharing "$@"
}

function exec-in-container() {
  local container=$1; shift;
  local command=$@;
  dc exec -T --user $(id -u):$(id -g) $container sh -c "HOME=./ $command"
}

function run-in-container() {
  local container=$1; shift;
  local command=$@;
  dc run --rm --user $(id -u):$(id -g) $container sh -c "HOME=./ $command"
}

function run-in-container-with-service-ports() {
  local container=$1; shift;
  local command=$@;
  dc run --rm --user $(id -u):$(id -g) --service-ports $container sh -c "HOME=./ $command"
}

function exec-in-container-asroot() {
  local container=$1; shift;
  local command=$@;
  dc exec --user root -T $container sh -c "$command"
}

function run-in-container-asroot() {
  local container=$1; shift;
  local command=$@;
  dc run --user root --rm $container sh -c "$command"
}

function dropdb() {
  local database=$1;
  echo "Dropping database $FS_ENV/$database"
  sql-query mysql "drop database if exists $database"
}

function createdb() {
  local database=$1;
  echo "Creating database $FS_ENV/$database"
  sql-query mysql "create database if not exists $database"
}

function recreatedb() {
  local database=$1;
  dropdb "$database"
  createdb "$database"
}

function migratedb() {
  local database=$1;
  echo "Migrating database $FS_ENV/$database"
  dest=migrations/_all.sql
  migration_files="\
      migrations/initial.sql \
      migrations/static.sql \
      migrations/27-profilchange.sql \
      migrations/27-verify.sql \
      migrations/incremental-* \
  "
  echo "" > $dest
  for f in $migration_files; do
    cat $f >> $dest
    echo ';' >> $dest
  done
  echo "COMMIT;" >> $dest

  # if running in ci we do not have a mounted folder so we need to
  # manually copy the generated migration file into the container
  docker cp $dest $(dc ps -q db):/app/$dest

  sql-file $database $dest

  dest=migrations/_reload_data.sql
  echo "set foreign_key_checks=0;" > $dest
  for T in `sql-query foodsharing "SHOW TABLES;" | tail -n+2`; do
    echo "TRUNCATE TABLE $T;" >> $dest
  done
  sql-dump --extended-insert --quick --no-create-info --single-transaction --disable-keys --no-autocommit --skip-add-locks >> $dest
  echo "set foreign_key_checks=1;" >> $dest
  docker cp $dest $(dc ps -q app):/app/$dest
}

function purge-db() {
  time sql-file foodsharing migrations/_reload_data.sql
}

function wait-for-mysql() {
  exec-in-container-asroot db "while ! mysql -p$MYSQL_PASSWORD --silent -e 'select 1' >/dev/null 2>&1; do sleep 1; done"
}

function install-chat-dependencies() {
  # TODO: move this into scripts/mkdirs when MR#97 is merged
  run-in-container-asroot chat \
    "mkdir -p node_modules && chown -R $(id -u):$(id -g) node_modules"

  # have to do run, not exec, as container will not start until
  # node_modules is installed, this will run up a fresh container and
  # just run npm install
  run-in-container chat yarn
}
