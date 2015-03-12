<?php

require(__DIR__ . '/service-base.php');

use OAuth\OAuth2\Service\Dropbox;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

$path = isset($_GET['path']) ? $_GET['path']: '';

if (empty($path)) {
	echo json_encode([
		'result' =>[
			[
				'name'=>'Dropbox',
				'path'=>'Dropbox',
				'folder'=>true,
			],
			[
				'name'=>'Facebook',
				'path'=>'Facebook',
				'folder'=>true,
			],
			[
				'name'=>'Google Drive',
				'path'=>'GoogleDrive',
				'folder'=>true,
			],
			[
				'name'=>'Flickr',
				'path'=>'Flickr',
				'folder'=>true,
			]
		],
		'error'=>null,
	]);
	die;
}

$storage = new Session();

if (strpos($path, 'Dropbox') === 0) {
	if (! $storage->hasAccessToken('Dropbox')) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}

	$path = substr($path, strlen('Dropbox') + 1);

	// Setup the credentials for the requests
	$credentials = new Credentials(
	    $servicesCredentials['dropbox']['key'],
	    $servicesCredentials['dropbox']['secret'],
	    $currentUri->getAbsoluteUri()
	);
	// Instantiate the Dropbox service using the credentials, http client and storage mechanism for the token
	/** @var $dropboxService Dropbox */
	$dropboxService = $serviceFactory->createService('dropbox', $credentials, $storage, array());

	$response = json_decode($dropboxService->request('/metadata/auto/' . $path), true);

	// $token = $dropboxService->requestAccessToken($_GET['code']);

	// var_dump(json_decode($dropboxService->request('/metadata/auto/'), true));
	// die;

	$token = $storage->retrieveAccessToken('Dropbox')->getAccessToken();

	$items = [];

	if (isset($response['contents'])) {
		foreach ($response['contents'] as $item) {
			$name = preg_replace('/^.*\//', '', $item['path']);
			$items[] = [
				'name'=>$name,
				'folder'=>$item['is_dir'],
				'path'=>'Dropbox' . $item['path'],
				'iconImage'=>! empty($item['mime_type']) && ($item['mime_type'] == 'image/jpeg' || $item['mime_type'] == 'image/png' || $item['mime_type'] == 'image/gif') ? 'https://api-content.dropbox.com/1/thumbnails/auto' . $item['path'] . '?size=m&access_token=' . $token: null,
				'iconClasses'=>$item['is_dir'] ? 'filespart-files-icon-folder': 'filespart-files-icon-image',
			];
		}
	}

	echo json_encode([
		'result'=>$items,
		'error'=>null,
	]);
}