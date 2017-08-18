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

Rest::get( function () {
	$id = Ans::GET('id');
	if (!$id) {
		$html = Rest::parse('-gdoc2article/layout.tpl', array(), 'WHAT');
	} else {
		$html = GoogleDocs::get($id);
	}
	echo $html;
}, 'folder', [function () {
		$html = Rest::parse('-gdoc2article/layout.tpl', array(), 'WHAT');
		echo $html;
	}, function ($type, $id) {

		$list = GoogleDocs::list($id);
		echo $type;
		
}], function ($id) {
	$html = GoogleDocs::get($id);
	echo $html;
});







