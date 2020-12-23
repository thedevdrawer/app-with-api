<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once $_SERVER['DOCUMENT_ROOT'].'/api/config/database.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/api/controllers/pages.php';

$database = new Database();
$db = $database->getConnection();

$item = new Pages($db);
$data = json_decode(file_get_contents('php://input'));
$item->id = $data->id;

if($item->delete()):
    http_response_code(200);
    echo json_encode(
        array(
            "type"=>"success",
            "title"=>"Success",
            "message"=>"The page was deleted successfully."
        )
    );
else:
    http_response_code(404);
    echo json_encode(
        array(
            "type"=>"danger",
            "title"=>"Failed",
            "message"=>"The page was not deleted successfully. Please try again."
        )
    );
endif;