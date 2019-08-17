<?php

namespace App;

require __DIR__.'/../vendor/autoload.php';

use App\Models\Api;
use App\Models\Credentials;
use App\Models\PlanningCenter\PlanningCenter;
use App\Models\Spotify\Spotify;
use App\Models\Spotify\SpotifyAuthorization;

// Setup initial authorization
$credentialsFile = __DIR__.'/../storage/auth.json';
$credentials = new Credentials($credentialsFile);
$credentials->loadOrCreate();

// Setup Spotify user token
$spotifyAuth = new SpotifyAuthorization(
    $credentials->get('SPOTIFY_CLIENT_ID'),
    $credentials->get('SPOTIFY_CLIENT_SECRET'),
    $credentials->get('SPOTIFY_ACCESS_TOKEN'),
    $credentials->get('SPOTIFY_REFRESH_TOKEN'),
    new Api()
);

$spotifyAuthToken = $spotifyAuth->generateAuthToken();

// Store Spotify Authorization
$credentials->set('SPOTIFY_ACCESS_TOKEN', $spotifyAuth->getAccessToken());
$credentials->set('SPOTIFY_REFRESH_TOKEN', $spotifyAuth->getRefreshToken());

$spotify = new Spotify(
    $spotifyAuthToken,
    new Api()
);

// Get data from planning center
$planningCenter = new PlanningCenter(
    $credentials->get('PLANNING_CENTER_APPLICATION_ID'),
    $credentials->get('PLANNING_CENTER_SECRET'),
    new Api()
);

$serviceTypes = $planningCenter->getServiceTypes();

foreach ($serviceTypes as $serviceType) {
    $serviceType->syncServicePlansWithSpotify($spotify);
}
