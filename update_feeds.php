<?php

use QuidNovi\QuidNoviUpdater;

require_once __DIR__.'/vendor/autoload.php';

$app = new QuidNoviUpdater();
$app->update();