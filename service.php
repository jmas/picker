<?php

require(__DIR__ . '/service-base.php');

use OAuth\OAuth2\Service\Dropbox;
use OAuth\OAuth2\Service\Facebook;
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
				'iconClasses'=>'filespart-files-icon-dropbox',
			],
			[
				'name'=>'Facebook',
				'path'=>'Facebook',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-facebook',
			],
			[
				'name'=>'Google',
				'path'=>'Google',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-googledrive',
			],
			[
				'name'=>'Flickr',
				'path'=>'Flickr',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-flickr',
			],
			[
				'name'=>'Instagram',
				'path'=>'Instagram',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-instagram',
			],
			[
				'name'=>'Twitter',
				'path'=>'Twitter',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-twitter',
			],
			[
				'name'=>'Tumblr',
				'path'=>'Tumblr',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-tumblr',
			],
			[
				'name'=>'Vkontakte',
				'path'=>'Vkontakte',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-vk',
			],
			[
				'name'=>'Microsoft',
				'path'=>'Microsoft',
				'folder'=>true,
				'iconClasses'=>'filespart-files-icon-onedrive',
			],
			// [
			// 	'name'=>'Recent',
			// 	'path'=>'Recent',
			// 	'folder'=>true,
			// ],
		],
		'error'=>null,
	]);
	die;
}

$storage = new Session();
// $storage->clearToken('Facebook');
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
	$service = $serviceFactory->createService('dropbox', $credentials, $storage, array());
	try {
		$response = $service->request('/metadata/auto/' . $path);
	} catch(\Exception $e) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}
	$response = json_decode($response, true);

	// $token = $service->requestAccessToken($_GET['code']);

	// var_dump(json_decode($service->request('/metadata/auto/'), true));
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

if (strpos($path, 'Facebook') === 0) {
	// $storage->clearToken('Facebook');
	if (! $storage->hasAccessToken('Facebook')) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}

	$path = substr($path, strlen('Facebook') + 1);
	$path = $path ? $path: '';

	// Setup the credentials for the requests
	$credentials = new Credentials(
	    $servicesCredentials['facebook']['key'],
	    $servicesCredentials['facebook']['secret'],
	    $currentUri->getAbsoluteUri()
	);
	// Instantiate the Dropbox service using the credentials, http client and storage mechanism for the token
	/** @var $dropboxService Dropbox */
	$service = $serviceFactory->createService('facebook', $credentials, $storage, array('user_photos'));

	$items = [];

	try {
		$response = $service->request('/me/albums');
	} catch(\Exception $e) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}

	$response = json_decode($response, true);

	if (empty($path)) {
		
		
		if (isset($response['data'])) {
			foreach ($response['data'] as $item) {
				$items[] = [
					'name'=>$item['name'],
					'folder'=>true,
					'path'=>'Facebook/' . $item['name'],
					// 'iconImage'=>! empty($item['mime_type']) && ($item['mime_type'] == 'image/jpeg' || $item['mime_type'] == 'image/png' || $item['mime_type'] == 'image/gif') ? 'https://api-content.dropbox.com/1/thumbnails/auto' . $item['path'] . '?size=m&access_token=' . $token: null,
					'iconClasses'=>'filespart-files-icon-folder',
				];
			}
		}
	} else {
		if (isset($response['data'])) {
			$founded = null;
			foreach ($response['data'] as $item) {
				if ($item['name'] === $path) {
					$founded = $item;
					break;
				}
			}
			try {
				$response = json_decode($service->request('/' . $item['id'] . '/photos'), true);
			} catch(\Exception $e) {
				header('HTTP/1.1 401 Unauthorized', true, 401);
				die;
			}
			if (isset($response['data'])) {
				// var_dump($response['data']); die;
				foreach ($response['data'] as $item) {
					$items[] = [
						'name'=>empty($item['name']) ? 'Photo': $item['name'],
						'folder'=>false,
						'path'=>'Facebook/' . $path . '/' . $item['id'],
						'iconImage'=>$item['images'][count($item['images'])-1]['source'],
						'iconClasses'=>'filespart-files-icon-image',
					];
				}
			}
		}
	}

	// $token = $dropboxService->requestAccessToken($_GET['code']);

	// var_dump(json_decode($dropboxService->request('/metadata/auto/'), true));
	// die;

	//$token = $storage->retrieveAccessToken('Facebook')->getAccessToken();

	echo json_encode([
		'result'=>$items,
		'error'=>null,
	]);
}

if (strpos($path, 'Flickr') === 0) {
	// $storage->clearToken('Facebook');
	if (! $storage->hasAccessToken('Flickr')) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}

	// Setup the credentials for the requests
	$credentials = new Credentials(
		$servicesCredentials['flickr']['key'],
		$servicesCredentials['flickr']['secret'],
		$currentUri->getAbsoluteUri()
	);

	$flickrService = $serviceFactory->createService('Flickr', $credentials, $storage);

	$response = json_decode($flickrService->request('flickr.photos.search', 'GET', null, [], ['user_id'=>'67862293@N05','format'=>'json','nojsoncallback'=>1]), true);

	// var_dump($response); die;

	$items = [];

	if (isset($response['photos']) && isset($response['photos']['photo'])) {
		foreach ($response['photos']['photo'] as $item) {
			$items[] = [
				'name'=>empty($item['title']) ? 'Photo': $item['title'],
				'folder'=>false,
				'path'=>'Flickr/' . $item['id'],
				'iconImage'=>'https://farm'.$item['farm'].'.staticflickr.com/'.$item['server'].'/'.$item['id'].'_'.$item['secret'].'_m.jpg',
				'iconClasses'=>'filespart-files-icon-image',
			];
		}
	}

	echo json_encode([
		'result'=>$items,
		'error'=>null,
	]);
}

if (strpos($path, 'Google') === 0) {
	// $storage->clearToken('Facebook');
	if (! $storage->hasAccessToken('Google')) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}

	$credentials = new Credentials(
	    $servicesCredentials['google']['key'],
	    $servicesCredentials['google']['secret'],
	    $currentUri->getAbsoluteUri()
	);

	$googleService = $serviceFactory->createService('google', $credentials, $storage, array('googledrive'));
	try {
		$response = $googleService->request('https://www.googleapis.com/drive/v2/files');
	} catch(\Exception $e) {
		header('HTTP/1.1 401 Unauthorized', true, 401);
		die;
	}
	$response = json_decode($response, true);

	// var_dump();

	$items = [];

	if (isset($response['items'])) {
		foreach ($response['items'] as $item) {
			$items[] = [
				'name'=>empty($item['title']) ? 'File': $item['title'],
				'folder'=>false,
				'path'=>'Google/' . $item['id'],
				'iconImage'=>isset($item['thumbnailLink']) ? $item['thumbnailLink']: null,
				'iconClasses'=>'filespart-files-icon-image',
			];
		}
	}

	echo json_encode([
		'result'=>$items,
		'error'=>null,
	]);
}