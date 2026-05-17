<?php
// Start Google OAuth flow
session_start();

$config = require __DIR__ . '/google.php';

// generate and store state to mitigate CSRF
if (function_exists('random_bytes')) {
    $state = bin2hex(random_bytes(16));
} else {
    $state = bin2hex(openssl_random_pseudo_bytes(16));
}

$_SESSION['oauth2state'] = $state;

$params = [
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
    'scope' => $config['scope'],
    'state' => $state,
    'prompt' => 'select_account'
];

$authUrl = $config['auth_uri'] . '?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
