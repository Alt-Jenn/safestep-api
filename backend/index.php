<?php

require_once __DIR__ . '/vendor/autoload.php';

header("Content-Type: application/json");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "a8Fz9KlmQxR2tY7uW5pLsD3vBnH6jKzX4";

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// =========================
// ROUTE LOGIN
// =========================
if (strpos($uri, "/login") !== false && $method === "POST") {

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->username) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Requête invalide"]);
        exit;
    }

    if ($data->username === "tech" && $data->password === "1234") {

        $payload = [
            "iss" => "safestep-api",
            "iat" => time(),
            "exp" => time() + 3600,
            "user" => $data->username
        ];

        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        http_response_code(200);
        echo json_encode(["token" => $jwt]);

    } else {
        http_response_code(401);
        echo json_encode(["message" => "Identifiants invalides"]);
    }
    exit;
}


// =========================
// ROUTE INVENTORY PROTÉGÉE
// =========================
if (strpos($uri, "/inventory") !== false && $method === "GET") {

    $headers = getallheaders();
    //foreach (getallheaders() as $name => $value) {
    //    echo "$name: $value\n";
    //}
    // 1️⃣ Token manquant
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["message" => "Token manquant"]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    $arr = explode(" ", $authHeader);

    // 2️⃣ Mauvais format
    if ($arr[0] !== "Bearer" || !isset($arr[1])) {
        http_response_code(401);
        echo json_encode(["message" => "Format token invalide"]);
        exit;
    }

    try {

        // 3️⃣ Vérification JWT
        JWT::decode($arr[1], new Key($secret_key, 'HS256'));

        // 4️⃣ Réponse OK
        http_response_code(200);
        echo json_encode([
            ["id" => 1, "item" => "Casque", "quantity" => 10],
            ["id" => 2, "item" => "Gants", "quantity" => 25],
            ["id" => 3, "item" => "Harnais", "quantity" => 5]
        ]);

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["message" => "Token invalide ou expiré"]);
    }

    exit;
}


// =========================
// ROUTE NON TROUVÉE
// =========================
http_response_code(404);
echo json_encode(["message" => "Route non trouvée"]);