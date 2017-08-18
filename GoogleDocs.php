<?php
namespace drakon5999\gdoc2article;
use Google_Client;
use Google_Service_Drive;
use infrajs\path\Path;
use infrajs\router\Router;
use infrajs\access\Access;
use infrajs\rubrics\Rubrics;
use Groundskeeper\Groundskeeper;
use infrajs\template\Template;
use infrajs\view\View;
use adevelop\htmlcleaner\HtmlCleaner;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}



class GoogleDocs {
	public static $conf = array(
		'production' => 'kemppi-nn.ru', //Адрес продакшина, для замены ссылок из гуглдокс на ссылки относительно корня сайта
		'certificate' => '~client_secret.json' //Адрес файла с авторизацией гугла
	);
	/**
	 * @return html of articles or html-404 with http header
	 */
	public static function get($id) 
	{
		$html = GoogleDocs::getArticle($id);
		if ($html === false) {
			$html = Template::parse('-gdoc2article/layout.tpl', array(), '404');
			http_response_code(404);
		}
		return $html;
	}
	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	public static function getClient() 
	{
		putenv("GOOGLE_APPLICATION_CREDENTIALS=".Path::resolve(static::$conf['certificate']));
		$client = new Google_Client();
		$client->useApplicationDefaultCredentials();
		$client->setScopes(Google_Service_Drive::DRIVE);

		return $client;
	}
	public static function list($id) {
		return Access::cache(__FILE__.'getArticle', function () use ($id) {
			$client = GoogleDocs::getClient();
			$service = new \Google_Service_Drive($client);
			$list = array();
			try {
				$fileExport = $service->files->export($id, 'text/html');
			} catch (\Exception $e) {
				return $list;
			}
			
			
			return $list;
		});
	}
	public static function getArticle($id/*, $dir*/)
	{
		// Get the API client and construct the service object.
		return Access::cache(__FILE__.'getArticle', function () use ($id) {
			$client = GoogleDocs::getClient();
			$service = new \Google_Service_Drive($client);
			try {

				$fileExport = $service->files->export($id, 'text/html');
				
			} catch (\Exception $e) {
				return false;
			}

			$html = $fileExport->getBody();
			$html = GoogleDocs::cleanHtml($html);
			return $html;
		});
	}
	public static function cleanHtml($html) {
		$groundskeeper = new Groundskeeper(array(
		    'element-blacklist' => 'style,meta'
		));
		$html = $groundskeeper->clean($html);


		$allowed_tags = 'p,ul,li,ol,img[src],h1,h2,h3,a[href],table,tr,td,b,i';
		$allow_href_js = false;

		$html = preg_replace('/<span style="[^"]*font-style:italic[^"]*font-weight:700[^"]*">([^<]*)<\/span>/', '<b><i>$1</i></b>', $html);
		$html = preg_replace('/<span style="[^"]*font-weight:700[^"]*font-style:italic[^"]*">([^<]*)<\/span>/', '<b><i>$1</i></b>', $html);
		$html = preg_replace('/<span style="[^"]*font-weight:700[^"]*">([^<]*)<\/span>/', '<b>$1</b>', $html);
		$html = preg_replace('/<span style="[^"]*font-style:italic[^"]*">([^<]*)<\/span>/', '<i>$1</i>', $html);


		$cleaner = new HtmlCleaner($allowed_tags, $allow_href_js);

		$html = $cleaner->clean($html);


		$html = preg_replace('/\s+/u', ' ', $html);//Все невидимые символы неразрывного пробела удаляем
		//$html = preg_replace('/\s<\//', '</', $html);//Удаляем пробел перед закрывающим тегом <a>ссылки </a> 


		$host = View::getHost();
		$html = preg_replace('/https:\/\/www.google.com\/url\?q=https{0,1}:\/\/'.$host.'([^"]*)&amp;sa=[^"]*/','$1',$html);
		//https://www.google.com/url?q=https://kemppi-nn.ru/contacts&amp;sa=D&amp;ust=1474575413967000&amp;usg=AFQjCNEMpDDy_ykh9PcNwX14uTdVZ-zb4A
		$conf = GoogleDocs::$conf;
		if ($conf['production']) {
			$host = $conf['production'];
			$html = preg_replace('/https:\/\/www.google.com\/url\?q=https{0,1}:\/\/'.$host.'([^"]*)&amp;sa=[^"]*/','$1',$html);
		}
		$html = Rubrics::parse($html);
		return $html;
	}
	

	//FOLDER
	public static function buildTree(&$dirTree, $service, $path) {
		
		Path::mkdir($path);
		foreach ($dirTree as $id => &$subTree) {
			if ($subTree["mimeType"] == 'application/vnd.google-apps.folder') {
				$subTree["children"] = GoogleDocs::getChildren($subTree['id'], $service);
				Path::mkdir($path.$subTree['name'].'/');

				GoogleDocs::buildTree($subTree["children"], $service, $path.$subTree['name']."/");
			} else if ($subTree["mimeType"] == 'application/vnd.google-apps.document') {
				$fileExport = $service->files->export($subTree['id'], 'text/html');
				//print_r(get_class_methods(get_class($fileExport)));
				
				$src = Path::theme($path);
				file_put_contents($src.Path::tofs($subTree['name']).".html", $fileExport->getBody());
			}
		}
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

			$conf = static::$conf;
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
}

