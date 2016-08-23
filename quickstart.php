<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use drakon5999\gdoc2article\GoogleDocs;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}


$ans = array();

$msg = GoogleDocs::export('GDoc2Article','~GDoc2Article/');

if ($msg) return Ans::err($ans, $msg);

return Ans::ret($ans, 'Если ошибок на странице нет значит экспорт выполнен.');

