<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use infrajs\rest\Rest;
use infrajs\load\Load;
use infrajs\path\Path;
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
}], 'table', [function () {
		$html = GoogleDocs::html('WHAT');
		return Ans::html($html);
	}, [ function () {
		$html = GoogleDocs::html('WHAT');
		return Ans::html($html);
}, function ($type, $id, $range) {
		$data = GoogleDocs::getTable($id, $range);
		if ($data && isset($_GET['reverse'])) {
			$data['data'] = array_reverse($data['data']);
		}
		$ans = array();
		$ans['data'] = $data;
		return Ans::ret($ans);
}]], 'public', [function () {
		$ans = array();
		$public = GoogleDocs::$conf['public'];
		$ans['data'] = $public;
		return Ans::ret($ans);
	}, [ function ($t, $pub) {
		$ans = array();
		$public = GoogleDocs::$conf['public'];
		if (empty($public[$pub])) return Ans::err($ans,'Ключ '.$pub.' не зарегистрирован');
		$id = $public[$pub];
		$data = GoogleDocs::getPublicFolder($pub, $id);
		$order = Ans::GET('order',['ascending','descending'], 'descending');
		Load::sort($data, $order);
		$list = array();
		foreach ($data as $k => $v) {
			unset($data[$k]['body']);
			$list[$v['id']] = $v;
			//$list[$k] = $v['body'];
		}
		


		$lim = Ans::GET('lim','string','0,200');
		list($start, $count) = explode(',', $lim);
		
		$list = array_slice($list, $start, $count);
		

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
				$ans['data'] = array(
					'id' => $name
				);
				return Ans::err($ans);
			} else {
				unset($list[$name]['body']);
				$ans['data'] = $list[$name];	
				return Ans::ret($ans);
			}
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







