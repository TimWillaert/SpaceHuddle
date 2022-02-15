<?php

// Production environment

$settings["error"]["display_error_details"] = false;
$settings["logger"]["level"] = \Monolog\Logger::INFO;

// Database
$settings["db"]["database"] = "spacehuddle";

// Application
$settings["application"]["baseUrl"] = "https://spacehuddle.io";

$settings["error"]["display_error_details"] = true;
