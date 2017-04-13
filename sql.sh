#!/bin/bash

source config/environment.sh

cat sql/drop_tables.sql sql/create_tables.sql sql/add_test_data.sql | psql -U $DB_USERNAME