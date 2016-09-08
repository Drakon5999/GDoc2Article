<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use drakon5999\gdoc2article\GoogleDocs;
use Groundskeeper\Groundskeeper;
use adevelop\htmlcleaner\HtmlCleaner;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}


$html = GoogleDocs::getArticle($_REQUEST['id']);

$groundskeeper = new Groundskeeper(array(
    'output' => 'pretty',
    'element-blacklist' => 'style,meta'
));



$cleanHtml = $groundskeeper->clean($html);


$allowed_tags = 'p[class],h1,h2,h3,a[href],table,tr,td,i';
$allow_href_js = false;

$cleaner = new HtmlCleaner($allowed_tags, $allow_href_js);

$cleanHtml = $cleaner->clean($cleanHtml);


echo $cleanHtml;
