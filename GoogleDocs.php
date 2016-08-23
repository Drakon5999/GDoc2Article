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


define('APPLICATION_NAME', 'Drive API PHP Quickstart');
define('MIME_DIR', 'application/vnd.google-apps.folder');
define('MIME_DOC', 'application/vnd.google-apps.document');
define('MIME_HTML', 'text/html');

$conf = Config::get('gdoc2article');
putenv("GOOGLE_APPLICATION_CREDENTIALS=".Path::resolve($conf['certificate']));


class GoogleDocs {
	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
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

		if (count($rootDirList) != 1) {

			$conf = Config::get('gdoc2article');
			//Обработка ошибок ленивым способом, текст сообщения внутри функции, возвращённая любая строка это значит ошибка.
			return 'Папка '.$googleDir.' не найдена. У авторизованного пользователя. '.$conf['certificate'];
		}
		$childs = GoogleDocs::getChildren($rootDirList[0]['id'], $service);
		GoogleDocs::buildTree($childs, $service, $dir);
	}
	public static function getChildren($parentID, $service){
		$childrenParams = array(
		  'q' => '"'.$parentID.'" in parents'
		);
		$list = $service->files->listFiles($childrenParams);
		return $list->getFiles();
	}
	public static function buildTree(&$dirTree, $service, $path) {
		
		Path::mkdir($path);
		foreach ($dirTree as $id => &$subTree) {
			if ($subTree["mimeType"] == MIME_DIR) {
				$subTree["children"] = GoogleDocs::getChildren($subTree['id'], $service);
				Path::mkdir($path.$subTree['name'].'/');

				GoogleDocs::buildTree($subTree["children"], $service, $path.$subTree['name']."/");
			} else if ($subTree["mimeType"] == MIME_DOC) {
				$fileExport = $service->files->export($subTree['id'], MIME_HTML);
				//print_r(get_class_methods(get_class($fileExport)));
				
				$src = Path::theme($path);
				file_put_contents($src.Path::tofs($subTree['name']).".html", $fileExport->getBody());
			}
		}
	}
}





