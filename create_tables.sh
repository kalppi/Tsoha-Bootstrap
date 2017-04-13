#!/bin/bash

source config/environment.sh

echo "Luodaan tietokantataulut..."

ssh $USERNAME@$SERVER -p $PORT "
cd $REMOTE_DIR/$PROJECT_FOLDER/sql
cat drop_tables.sql create_tables.sql | psql -U $DB_USERNAME -1 -f -
exit"

echo "Valmis!"
