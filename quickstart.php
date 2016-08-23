<?php
use infrajs\router\Router;
use drakon5999\gdoc2article\GoogleDocs;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}



GoogleDocs::export('GDoc2Article','~GDoc2Article/');
	



