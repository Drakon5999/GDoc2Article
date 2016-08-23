
<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Drive API PHP Quickstart');
define('CLIENT_SECRET_PATH', __DIR__ . '/data/client_secret.json');
define('MIME_DIR', 'application/vnd.google-apps.folder');
define('MIME_DOC', 'application/vnd.google-apps.document');
define('MIME_HTML', 'text/html');

putenv("GOOGLE_APPLICATION_CREDENTIALS=".CLIENT_SECRET_PATH);
	
/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
	$client = new Google_Client();
	$client->useApplicationDefaultCredentials();
	$client->setScopes(Google_Service_Drive::DRIVE);

	return $client;
}
function getChildren($parentID, $service){
	$childrenParams = array(
	  'q' => '"'.$parentID.'" in parents'
	);
	return (($service->files->listFiles($childrenParams))->getFiles());
}
function buildTree(&$dirTree, $service, $path=__DIR__ ."/cache/articles/"){
	foreach($dirTree as $id => &$subTree){
		if($subTree["mimeType"] == MIME_DIR){
			$subTree["children"] = getChildren($subTree['id'], $service);
			if (!is_dir($path.$subTree['name'])) {
				mkdir($path.$subTree['name']);
			}
			buildTree($subTree["children"], $service, $path.$subTree['name']."/");
		}
		elseif($subTree["mimeType"] == MIME_DOC)
		{
			$fileExport = $service->files->export($subTree['id'],MIME_HTML);
			//print_r(get_class_methods(get_class($fileExport)));
			file_put_contents($path.$subTree['name'].".html", $fileExport->getBody());
		}
	}
}

// инициализация
if (!is_dir(__DIR__ .'/cache')) {
	mkdir(__DIR__ .'/cache');
}
if (!is_dir(__DIR__ .'/cache/articles')) {
	mkdir(__DIR__ .'/cache/articles');
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

$rootParams = array(
	'q' => 'name="GDoc2Article"'
);

$rootDirList = (($service->files->listFiles($rootParams))->getFiles());

if(count($rootDirList)!=1){
	throw new Exception('Невозможно однозначно определить корневую папку.');
}
buildTree($rootDirList, $service);
// print_r($rootDirList);
