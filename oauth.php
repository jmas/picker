<?php

require(__DIR__ . '/service-base.php');

use OAuth\OAuth2\Service\Dropbox;
use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

$services = ['Dropbox','Facebook','Flickr','Google'];

$storage = new Session();

$successHtml = '<html><script>if (window.opener && window.opener.__resumeGetJson) { try { window.opener.__resumeGetJson(); } catch(e) { alert(e); } window.close(); } else { location.href = \'/picker/demo.html\'; }</script></html>';

// $storage->clearAllTokens();

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/Dropbox') {
	// Setup the credentials for the requests
	$credentials = new Credentials(
	    $servicesCredentials['dropbox']['key'],
	    $servicesCredentials['dropbox']['secret'],
	    $currentUri->getAbsoluteUri()
	);
	// Instantiate the Dropbox service using the credentials, http client and storage mechanism for the token
	/** @var $dropboxService Dropbox */
	$dropboxService = $serviceFactory->createService('dropbox', $credentials, $storage, array());
	if (!empty($_GET['code'])) {
	    // This was a callback request from Dropbox, get the token
	    try {
		    $token = $dropboxService->requestAccessToken($_GET['code']);
		} catch (\Exception $e) {}

	    echo $successHtml;

	    // Send a request with it
	    // $result = json_decode($dropboxService->request('/account/info'), true);
	    // Show some of the resultant data
	    // echo 'Your unique Dropbox user id is: ' . $result['uid'] . ' and your name is ' . $result['display_name'];
	// } elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
	    // $url = $dropboxService->getAuthorizationUri();
	    // header('Location: ' . $url);
	} else {
		$url = $dropboxService->getAuthorizationUri();
	    header('Location: ' . $url);
	    // $url = $currentUri->getRelativeUri() . '?go=go';
	    // echo "<a href='$url'>Login with Dropbox!</a>";
	}
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/Facebook') {
	// Setup the credentials for the requests
	$credentials = new Credentials(
	    $servicesCredentials['facebook']['key'],
	    $servicesCredentials['facebook']['secret'],
	    $currentUri->getAbsoluteUri()
	);
	// Instantiate the Dropbox service using the credentials, http client and storage mechanism for the token
	/** @var $dropboxService Dropbox */
	$dropboxService = $serviceFactory->createService('facebook', $credentials, $storage, array('user_photos'));
	if (!empty($_GET['code'])) {
	    // This was a callback request from Dropbox, get the token
	    try {
		    $token = $dropboxService->requestAccessToken($_GET['code']);
		} catch (\Exception $e) {}

	    echo $successHtml;

	    // Send a request with it
	    // $result = json_decode($dropboxService->request('/account/info'), true);
	    // Show some of the resultant data
	    // echo 'Your unique Dropbox user id is: ' . $result['uid'] . ' and your name is ' . $result['display_name'];
	// } elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
	    // $url = $dropboxService->getAuthorizationUri();
	    // header('Location: ' . $url);
	} else {
		$url = $dropboxService->getAuthorizationUri();
	    header('Location: ' . $url);
	    // $url = $currentUri->getRelativeUri() . '?go=go';
	    // echo "<a href='$url'>Login with Dropbox!</a>";
	}
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/Flickr') {
	// Setup the credentials for the requests
	$credentials = new Credentials(
		$servicesCredentials['flickr']['key'],
		$servicesCredentials['flickr']['secret'],
		$currentUri->getAbsoluteUri()
	);

	// Instantiate the Flickr service using the credentials, http client and storage mechanism for the token
	$flickrService = $serviceFactory->createService('Flickr', $credentials, $storage);

	$step = isset($_GET['step']) ? (int)$_GET['step'] : null;

	$oauth_token = isset($_GET['oauth_token']) ? $_GET['oauth_token'] : null;
	$oauth_verifier = isset($_GET['oauth_verifier']) ? $_GET['oauth_verifier'] : null;

	if($oauth_token && $oauth_verifier){
		$step = 2;
	}

	switch($step){
		default:		
		case 1:
			
			if($token = $flickrService->requestRequestToken()){
				$oauth_token = $token->getAccessToken();
				$secret = $token->getAccessTokenSecret();
				
				if($oauth_token && $secret){
					$url = $flickrService->getAuthorizationUri(array('oauth_token' => $oauth_token, 'perms' => 'write'));
					header('Location: '.$url);
				}
			}
			
			break;
		
		case 2:
			$token = $storage->retrieveAccessToken('Flickr');
			$secret = $token->getAccessTokenSecret();
			
			if($token = $flickrService->requestAccessToken($oauth_token, $oauth_verifier, $secret)){
				$oauth_token = $token->getAccessToken();
				$secret = $token->getAccessTokenSecret();
				
				$storage->storeAccessToken('Flickr', $token);
				
				header('Location: '.$currentUri->getAbsoluteUri().'?step=3');
			}
			break;
		
		case 3:
			echo $successHtml;
			// $xml = simplexml_load_string($flickrService->request('flickr.test.login'));
			// print "status: ".(string)$xml->attributes()->stat."\n";
			break;
	}
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/Google') {
	// Setup the credentials for the requests
	$credentials = new Credentials(
	    $servicesCredentials['google']['key'],
	    $servicesCredentials['google']['secret'],
	    $currentUri->getAbsoluteUri()
	);

	$googleService = $serviceFactory->createService('google', $credentials, $storage, array('googledrive','userinfo_email','userinfo_profile'));

	if (!empty($_GET['code'])) {
	    try {
		    $googleService->requestAccessToken($_GET['code']);
		} catch (\Exception $e) {}

	    echo $successHtml;
	} else {
		$url = $googleService->getAuthorizationUri();
	    header('Location: ' . $url);
	}
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] === '/status') {
	$activeServices = [];
	foreach ($services as $serviceName) {
		if ($storage->hasAccessToken($serviceName)) {
			$activeServices[] = $serviceName;
		}
	}
	echo json_encode([
		'result'=>$activeServices,
		'error'=>null,
	]);
}