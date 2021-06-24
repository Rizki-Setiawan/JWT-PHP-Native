<?php

require_once('vendor/autoload.php');
require_once('cors.php');

use Firebase\JWT\JWT;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  exit();
}


if(!isset($_COOKIE['refreshToken'])) {
  http_response_code(403);
  exit();
}

try {
 
  $refresh_payload = JWT::decode($_COOKIE['refreshToken'], $_ENV['REFRESH_TOKEN_SECRET'], ['HS256']);

  $waktu_kadaluarsa = time() + (15 * 60);

  
  $payload = [
    'email' => $refresh_payload->email,
    'exp' => $waktu_kadaluarsa
  ];
  

  $access_token = JWT::encode($payload, $_ENV['ACCESS_TOKEN_SECRET']);


  echo json_encode([
    'accessToken' => $access_token,
    'expiry' => date(DATE_ISO8601, $waktu_kadaluarsa)
  ]);
  
  $payload['exp'] = time() + (60 * 60);
  $refresh_token = JWT::encode($payload, $_ENV['REFRESH_TOKEN_SECRET']);
  

  setcookie('refreshToken', $refresh_token, $payload['exp'], '', '', false, true);
} catch (Exception $e) {

  http_response_code(401);
  exit();
}

?>