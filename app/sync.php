<?php

namespace App;

require __DIR__.'/../vendor/autoload.php';

use App\Models\Credentials;
use App\Models\PlanningCenter\PlanningCenter;
use App\Models\PlanningCenter\PlanningCenterApi;
use App\Models\Spotify\Spotify;
use App\Models\Spotify\SpotifyApi;
use App\Models\Spotify\SpotifyAuthorization;
use App\Models\Spotify\SpotifyAuthorizationApi;

// Setup initial authorization
$credentialsFile = __DIR__.'/../storage/auth.json';
$credentials = new Credentials($credentialsFile);
$credentials->loadOrCreate();

// Setup Spotify user token
$spotifyAuthorizationApi = new SpotifyAuthorizationApi(
    $credentials->get('SPOTIFY_CLIENT_ID'),
    $credentials->get('SPOTIFY_CLIENT_SECRET'),
);
$spotifyAuth = new SpotifyAuthorization(
    $credentials->get('SPOTIFY_CLIENT_ID'),
    $credentials->get('SPOTIFY_ACCESS_TOKEN'),
    $credentials->get('SPOTIFY_REFRESH_TOKEN'),
    $spotifyAuthorizationApi
);

$spotifyAuthToken = $spotifyAuth->generateAuthToken();

// Store Spotify Authorization
$credentials->set('SPOTIFY_ACCESS_TOKEN', $spotifyAuth->getAccessToken());
$credentials->set('SPOTIFY_REFRESH_TOKEN', $spotifyAuth->getRefreshToken());

$spotifyApi = new SpotifyApi($spotifyAuthToken);
$spotify = new Spotify($spotifyApi);

// Get data from planning center
$planningCenterApi = new PlanningCenterApi(
    $credentials->get('PLANNING_CENTER_APPLICATION_ID'),
    $credentials->get('PLANNING_CENTER_SECRET')
);

$planningCenter = new PlanningCenter($planningCenterApi);

$serviceTypes = $planningCenter->getServiceTypes();

foreach ($serviceTypes as $serviceType) {
    $serviceType->syncServicePlansWithSpotify($spotify);
}
