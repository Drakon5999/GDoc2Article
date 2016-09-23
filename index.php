<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use drakon5999\gdoc2article\GoogleDocs;


if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}


$html = GoogleDocs::getArticle($_REQUEST['id']);



echo $html;
