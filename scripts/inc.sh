#!/bin/bash

dir=$(dirname "$0")

function sql-query() {
  mysql -h 127.0.0.1 -u root -proot "$1" -e "$2"
}

function sql-file() {
  mysql -h 127.0.0.1 -u root -proot "$1" < "$2"
}

function dropdb() {
  sql-query mysql 'drop database if exists foodsharing'
}

function createdb() {
  sql-query mysql 'create database if not exists foodsharing'
}

function recreatedb() {
  dropdb
  createdb
}

function migratedb() {
  sql-file foodsharing $dir/../migrations/initial.sql
}
