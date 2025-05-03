<?php
require '../vendor/autoload.php';


Flight::route('/', function() {
    echo 'Welcome to our Watch Store API!';
});

Flight::start();
?>


