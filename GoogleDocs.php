<?php
namespace drakon5999\gdoc2article;
use Google_Client;
use Google_Service_Drive;
use infrajs\path\Path;
use infrajs\config\Config;
use infrajs\router\Router;
use drakon5999\gdoc2article\GoogleDocs;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}

$conf = Config::get('gdoc2article');
define('APPLICATION_NAME', 'Drive API PHP Quickstart');
define('MIME_DIR', 'application/vnd.google-apps.folder');
define('MIME_DOC', 'application/vnd.google-apps.document');
define('MIME_HTML', 'text/html');
putenv("GOOGLE_APPLICATION_CREDENTIALS=".Path::resolve($conf['certificate']));


class GoogleDocs {
	public static function getClient() 
	{
		$client = new Google_Client();
		$client->useApplicationDefaultCredentials();
		$client->setScopes(Google_Service_Drive::DRIVE);

		return $client;
	}
	public static function export($googleDir, $dir)
	{
		// Get the API client and construct the service object.
		$client = GoogleDocs::getClient();
		
		$service = new \Google_Service_Drive($client);
		$rootParams = array(
			'q' => 'name="'.$googleDir.'"'
		);

		$list = $service->files->listFiles($rootParams);

		$rootDirList = $list->getFiles();

		if (count($rootDirList)!=1) {
			throw new Exception('Невозможно однозначно определить корневую папку.');
		}
		buildTree($rootDirList, $service, $dir);
		// print_r($rootDirList);
	}
}



/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */

function getChildren($parentID, $service){
	$childrenParams = array(
	  'q' => '"'.$parentID.'" in parents'
	);
	$list = $service->files->listFiles($childrenParams);
	return $list->getFiles();
}
function buildTree(&$dirTree, $service, $path){
	foreach($dirTree as $id => &$subTree){
		if($subTree["mimeType"] == MIME_DIR){
			$subTree["children"] = getChildren($subTree['id'], $service);

			Path::mkdir($path.$subTree['name']);

			buildTree($subTree["children"], $service, $path.$subTree['name']."/");
		}
		elseif($subTree["mimeType"] == MIME_DOC)
		{
			$fileExport = $service->files->export($subTree['id'], MIME_HTML);
			//print_r(get_class_methods(get_class($fileExport)));

			$src = Path::theme($path);
			file_put_contents($src.$subTree['name'].".html", $fileExport->getBody());
		}
	}
}