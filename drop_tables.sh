#!/bin/bash

source config/environment.sh

echo "Poistetaan tietokantataulut..."

ssh $USERNAME@$SERVER -p $PORT "
cd $REMOTE_DIR/$PROJECT_FOLDER/sql
psql -U $DB_USERNAME < drop_tables.sql
exit"

echo "Valmis!"
