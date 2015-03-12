<?php

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/vendor/autoload.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('date.timezone', 'Europe/Amsterdam');

$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
$currentUri->setQuery('');

$servicesCredentials = array(
    'amazon' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'bitbucket' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'bitly' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'box' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'buffer' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'dailymotion' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'dropbox' => array(
        'key'       => DROPBOX_KEY,
        'secret'    => DROPBOX_SECRET,
    ),
    'etsy' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'facebook' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'fitbit' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'flickr' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'foursquare' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'github' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'google' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'instagram' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'linkedin' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'mailchimp' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'microsoft' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'paypal' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'pocket' => array(
        'key'       => '',
    ),
    'reddit' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'runkeeper' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'scoopit' => array(
        'key'       => '',
        'secret'    => ''
    ),
    'soundcloud' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'spotify' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'tumblr' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'twitter' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'ustream' => array(
        'key'       => '',
        'secret'    => '',
    ),
    'yahoo' => array(
        'key'       => '',
        'secret'    => ''
    ),
    'yammer' => array(
        'key'       => '',
        'secret'    => ''
    ),
);

$serviceFactory = new \OAuth\ServiceFactory();