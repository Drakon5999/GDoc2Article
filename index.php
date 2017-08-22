<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use infrajs\rest\Rest;
use drakon5999\gdoc2article\GoogleDocs;


if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}

return Rest::get( function () {

	$id = Ans::GET('id');
	if (!$id) {
		$html = GoogleDocs::html('WHAT');
	} else {
		$html = GoogleDocs::getArticle($id);
		if ($html === false) {
			http_response_code(404);
		}
	}
	return Ans::html($html);
}, 'folder', [function () {
		$html = GoogleDocs::html('WHAT');
		return Ans::html($html);
	}, function ($type, $id) {
		$list = GoogleDocs::getFolder($id);
		return Ans::ret($list);
}], 'public', [function () {
		$ans = array();
		$public = GoogleDocs::$conf['public'];
		$ans['data'] = $public;
		return Ans::ret($ans);
	}, [ function ($t, $pub) {
		$ans = array();
		$public = GoogleDocs::$conf['public'];
		if (empty($public[$pub])) return Ans::err($ans,'Ключ '.$pub.' не зарегистрирован');
		$id = $public[$pub];
		$list = GoogleDocs::getPublicFolder($pub, $id);
		foreach ($list as $k=>$v) {
			unset($list[$k]['body']);
			//$list[$k] = $v['body'];
		}
		$ans['data'] = $list;
		return Ans::ret($ans);
	}, [ function ($t, $pub, $name) {
			$ans = array();
			$public = GoogleDocs::$conf['public'];
			
			if (empty($public[$pub])) {
				return Ans::err($ans,'Ключ '.$pub.' не зарегистрирован');
			}

			$id = $public[$pub];

			$list = GoogleDocs::getPublicFolder($pub, $id);
			if (empty($list[$name])) {
				http_response_code(404);
				return;
			}
			unset($list[$name]['body']);
			$ans['data'] = $list[$name];
			return Ans::ret($ans);
		}, 'body', function ($t, $pub, $name, $prop) {
			$ans = array();
			$public = GoogleDocs::$conf['public'];
			if (empty($public[$pub])) {
				return Ans::err($ans,'Ключ '.$pub.' не зарегистрирован');
			}
			$id = $public[$pub];

			$list = GoogleDocs::getPublicFolder($pub, $id);
			if (empty($list[$name])) {
				http_response_code(404);
				return;
			}
			return Ans::html($list[$name][$prop]);
		}]]], 
function ($id) {
	$html = GoogleDocs::getArticle($id);
	if ($html === false) {
		http_response_code(404);
		return;
	}
	return Ans::html($html);
});







