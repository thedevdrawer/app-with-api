<?php
require($_SERVER['DOCUMENT_ROOT'].'/api/vendor/autoload.php');
$openapi = \OpenApi\scan($_SERVER['DOCUMENT_ROOT'].'/api/controllers');
header('Content-Type: application/json');
echo $openapi->toJSON();