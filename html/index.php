<?php
date_default_timezone_set('Europe/Lisbon');

require '../app/config.php';

// Start init files
require '../app/bootstrap.php';

// Database connection
require '../app/database.php';

// Route
require '../app/routes.php';


$app->run();