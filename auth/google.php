<?php
// Google OAuth2 configuration (auth folder)
// Prefer loading from environment (.env) when available
require_once __DIR__ . '/../config/env.php';
// try to load project .env in repository root
load_dotenv(__DIR__ . '/../.env');

$clientId = getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID';
$clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET';
$redirectUri = getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost/magang-last/auth/oauth_callback.php';

return [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    // Redirect URI must match the one configured in Google Cloud Console
    'redirect_uri' => $redirectUri,
    'auth_uri' => 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_uri' => 'https://oauth2.googleapis.com/token',
    'userinfo_uri' => 'https://openidconnect.googleapis.com/v1/userinfo',
    'scope' => 'openid email profile'
];
