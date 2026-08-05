<?php

header('Content-Type: application/json');

http_response_code(200);

echo json_encode([
    'status' => 'healthy',
    'service' => 'restaurant-web',
    'timestamp' => gmdate('c')
]);
