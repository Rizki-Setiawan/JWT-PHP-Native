<?php
require_once('vendor/autoload.php');
require_once('./cors.php');

use Firebase\JWT\JWT;
use Dotenv\Dotenv;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit();
}


$json = file_get_contents('php://input');
$input_user = json_decode($json);


if (!isset($input_user->email) || !isset($input_user->password)) {
  http_response_code(400);
  exit();
}

$user = [
  'email' => 'rizkisetiawan17@gmail.com',
  'password' => '170200'
];


header('Content-Type: application/json');

if ($input_user->email !== $user['email'] || $input_user->password !== $user['password']) {
  echo json_encode([
    'success' => false,
    'data' => null,
    'message' => 'Email atau password tidak sesuai'
  ]);
  exit();
}

$waktu_kadaluarsa = time() + (15 * 60);

$payload = [
  'email' => $input_user->email,
  'exp' => $waktu_kadaluarsa
];

$access_token = JWT::encode($payload, $_ENV['ACCESS_TOKEN_SECRET']);

echo json_encode([
  'success' => true,
  'data' => [
    'accessToken' => $access_token,
    'expiry' => date(DATE_ISO8601, $waktu_kadaluarsa)
  ],
  'message' => 'Login berhasil!'
]);


$payload['exp'] = time() + (60 * 60);
$refresh_token = JWT::encode($payload, $_ENV['REFRESH_TOKEN_SECRET']);


setcookie('refreshToken', $refresh_token, $payload['exp'], '', '', false, true);
