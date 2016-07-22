<?php

require_once __DIR__ . '/../vendor/autoload.php';

use ApiExploder\App;

$app = new App(getenv('API_EXPLODER_ENVIRONMENT'));

$app->run();
