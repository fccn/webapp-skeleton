#!/bin/bash

cd "$(dirname ${BASH_SOURCE[0]})"
pwd

echo "Generating and updating database tables..."
php create_db_tables.php
