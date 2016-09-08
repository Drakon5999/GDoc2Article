<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use drakon5999\gdoc2article\GoogleDocs;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}

if(isset($_REQUEST['id'])&&isset($_REQUEST['update'])){
	$content = GoogleDocs::updateArticle($_REQUEST['id'],'~GDoc2Article/');
	echo $content;
}elseif(isset($_REQUEST['id'])){
	$content = GoogleDocs::getArticle($_REQUEST['id'],'~GDoc2Article/');
	echo $content;
}
