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
use infrajs\rest\Rest;
use infrajs\load\Load;
use infrajs\cache\Cache;
use infrajs\doc\Docx;
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
	public static function getService() 
	{
		$client = GoogleDocs::getClient();
		$service = new \Google_Service_Drive($client);
		return $service;
	}
	public static function getPublicFolder($pub, $id) {
		$list = GoogleDocs::getFolder($id);
		
		$path = '~'.$pub.'/';
		$dir = Path::theme($path);
		if(!$dir) return $list;

		array_map(function ($file) use ($pub, $path, &$list) {
			if ($file{0} == '.') return;
			$file = Path::toutf($file);
			$fd = Load::nameInfo($file);
			if (!in_array($fd['ext'],['docx','tpl'])) return;
			$name = $fd['name'];
			if (!isset($list[$name])) $list[$name] = array(
				'body' => '',
				'images' => array()
			);
			$src = $path.$fd['file'];
			$page = Rubrics::info($src);
			$body = Rubrics::article($src);

			if(!empty($page['preview'])) $list[$name]['preview'] = $page['preview'];
			if(!empty($page['images'])) $list[$name]['images'] = array_merge($list[$name]['images'], $page['images']);
			$list[$name]['heading'] = $page['heading'];
			$list[$name]['body'] = $body.$list[$name]['body'];
			
		}, scandir($dir));

		return $list;
	}
	public static function getFolder($id) {
		return Cache::exec(array(),__FILE__.'getFolder', function ($id) {
			return GoogleDocs::_getFolder($id);
		}, array($id), isset($_GET['re']));
	}
	public static function html($name = 'WHAT', $clean = false) {
		$public = GoogleDocs::$conf['public'];
		if ($clean) $html = Template::parse('-gdoc2article/layout.tpl', array('public' => $public), $name);
		else $html = Rest::parse('-gdoc2article/layout.tpl', array('public' => $public), $name);
		return $html;
	}
	public static function _getFolder($id) {
		$service = GoogleDocs::getService();
		$result = array();
		$pageToken = NULL;

		do {
			try {
			  $parameters = array('q' => "'".$id."' in parents and trashed=false");
			  if ($pageToken) {
					$parameters['pageToken'] = $pageToken;
			  }
			  $files = $service->files->listFiles($parameters);
			  $result = array_merge($result, $files->files);
			  $pageToken = $files->getNextPageToken();
			} catch (\Exception $e) {
			  //print "An error occurred: " . $e->getMessage();
			  $pageToken = NULL;
			}
		} while ($pageToken);

		$list = array();
		foreach ($result as $k => $file) {
			if ($file['mimeType'] != 'application/vnd.google-apps.document') continue;
			$fd = Load::nameInfo($file['name']);
			if (!$fd['id']) continue;
			$name = $fd['id'];
			$data = array();
			$data['name'] = $name;
			$data['heading'] = $fd['name'];
			$data['id'] = $file['id'];
			$data['body'] = GoogleDocs::getArticle($file['id']);

			preg_match_all("/src=\"([^\"]*)\"/", $data['body'], $match);
			$data['images'] = $match[1];

			$r = preg_match("/<h1>([^<]*)<\/h1>/", $data['body'], $match);
			if ($r) $data['heading'] = $match[1];
			$data['preview'] = Docx::getPreview($data['body']);
			$list[$name] = $data;
		}

		return $list;
	}
	public static function getArticle($id/*, $dir*/)
	{
		// Get the API client and construct the service object.
		return Access::cache(__FILE__.'getArticle', function ($id) {
			$service = GoogleDocs::getService();
			try {

				$fileExport = $service->files->export($id, 'text/html');
				
			} catch (\Exception $e) {
				return false;
			}

			$html = $fileExport->getBody();
			$html = GoogleDocs::cleanHtml($html);
			return $html;
		}, array($id));
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

		$r = explode('###', $html, 2);
		$html = $r[0];

		$cleaner = new HtmlCleaner($allowed_tags, $allow_href_js);

		$html = $cleaner->clean($html);


		$html = preg_replace('/\s+/u', ' ', $html);//Все невидимые символы неразрывного пробела удаляем
		//$html = preg_replace('/\s<\//', '</', $html);//Удаляем пробел перед закрывающим тегом <a>ссылки </a> 


		
		//https://www.google.com/url?q=https://kemppi-nn.ru/contacts&amp;sa=D&amp;ust=1474575413967000&amp;usg=AFQjCNEMpDDy_ykh9PcNwX14uTdVZ-zb4A
		$conf = GoogleDocs::$conf;
		if ($conf['production']) {
			$host = $conf['production'];
			$html = preg_replace('/https:\/\/www.google.com\/url\?q=https{0,1}:\/\/'.$host.'([^"]*)&amp;sa=[^"]*/','$1',$html);
		} else {
			$host = View::getHost();
			$html = preg_replace('/https:\/\/www.google.com\/url\?q=https{0,1}:\/\/'.$host.'([^"]*)&amp;sa=[^"]*/','$1',$html);
		}
		$html = preg_replace('/https:\/\/www.google.com\/url\?q=(https{0,1}:\/\/[^"]*)&amp;sa=[^"]*/','$1',$html);
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

