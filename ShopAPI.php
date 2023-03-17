<?php
require_once('vendor/autoload.php');

$mySqlHandler = new MySQLHandler("products");
$mySqlHandler->connect();

var_dump($mySqlHandler);
if (!$mySqlHandler) {
    $response = ["error" => "internal server error!"];
    // exit();
} else {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);

    if ($uri[2] !== 'products') {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
    $productId = null;
    if (isset($uri[3])) {
        $productId = (int) $uri[3];
    }
    $requestMethod = $_SERVER["REQUEST_METHOD"];
    switch ($requestMethod) {
        case 'GET':
            if ($productId) {
                if (!$mySqlHandler->search('id', $productId)) {
                    http_response_code(404);
                    $response = ["error" => "Resource doesn't exist"];
                } else {
                    $response = $mySqlHandler->get_record_by_id($productId);
                }
            } else {
                $response = $mySqlHandler->get_data();
            };
            break;
        case 'POST':
            $post = json_decode(file_get_contents('php://input'), true);
            $response = $mySqlHandler->save($post);
        case 'PUT':
            if ($productId) {
                if (!$mySqlHandler->search('id', $productId)) {
                    http_response_code(404);
                    $response = ["error" => "Resource doesn't exist"];
                } else {
                    $put = json_decode(file_get_contents('php://input'), true);
                    $response = $mySqlHandler->update($put, $productId);
                }
            }
            break;
        case 'DELETE':
            if ($productId) {
                if (!$mySqlHandler->search('id', $productId)) {
                    http_response_code(404);
                    $response = ["error" => "Resource doesn't exist"];
                } else {
                    $response = $mySqlHandler->delete($productId);
                }
            }
            break;
        default:
            http_response_code(405);
            $response = ["error" => "method not allowed!"];
            break;
    }
    echo json_encode($response);
}