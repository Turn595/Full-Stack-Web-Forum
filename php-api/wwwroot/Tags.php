<?php

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

require_once  '../init-db.php';
require_once '../entities/Tag.php';
require_once  'TagsController.php';

$dataFile = file_get_contents("php://input");
$requestData = !empty($dataFile) ? json_decode($dataFile, true) : $_REQUEST;

//A placeholder for our eventual result.
$resultToEncode = '';

//Manages the type of communication
switch ($_SERVER['REQUEST_METHOD']){
    case 'GET':
        $resultToEncode = TagsController::getTags($entityManager, $requestData);
        break;

    case 'POST'://Send data to the DB, can create the same resource multiple times.
        $resultToEncode = TagsController::postTags($entityManager, $requestData);
        break;
    case 'DELETE':
        $resultToEncode = TagsController::deleteTags($entityManager, $requestData,
            $entityManager->find(Tag::class, $requestData['id']));
        break;

    case 'OPTIONS'://Handle the options request often ussed by CORS to preform a preflights request. Check if the next request is allowed.
        http_response_code(204);//No content - all data is posted in headers
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');//allow the supported methods
        header('Access-Control-Allow-Headers: Content-type' );//allow Content-type header in request
        header('Access-Control-Max-Age: 86400');//1 day
        break;

    default:
        http_response_code(405);//methods not allowed
}

//By default webservers serve up html text files - we need to tell the browser that this is a JSON text file
header("Access-Control-Allow-Origin: *");//This bypasses security, should be don in webserver, NOT IN CODE.
header('Content-type:application/json');

//send out serialized JSON student array.
if (http_response_code() != 204){ //only output json if the return result is not 204 (empty)
    //Instantiate an instance of a serializer.
    $serializer = new Serializer([new ObjectNormalizer()],[new JsonEncoder()]);
    echo $serializer->serialize($resultToEncode, 'json');
}
