<?php

require(__DIR__ . '/service-base.php');

use OAuth\OAuth2\Service\Dropbox;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

$storage = new Session();
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

    echo '<html><script>window.opener && window.opener.__resumeGetJson && window.opener.__resumeGetJson(); window.close();</script></html>';

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