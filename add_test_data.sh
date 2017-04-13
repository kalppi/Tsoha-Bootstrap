#!/bin/bash

source config/environment.sh

echo "Lisätään testidata..."

ssh $USERNAME@$SERVER -p $PORT "
cd $REMOTE_DIR/$PROJECT_FOLDER/sql
psql < add_test_data.sql
exit"

echo "Valmis!"
