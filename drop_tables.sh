#!/bin/bash

source config/environment.sh

echo "Poistetaan tietokantataulut..."

ssh $USERNAME@$SERVER -p $PORT "
cd htdocs/$PROJECT_FOLDER/sql
psql < drop_tables.sql
exit"

echo "Valmis!"
