<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use infrajs\rubrics\Rubrics;
use drakon5999\gdoc2article\GoogleDocs;
use Groundskeeper\Groundskeeper;
use infrajs\view\View;
use adevelop\htmlcleaner\HtmlCleaner;

if (!is_file('vendor/autoload.php')) {
	chdir('../../../');
	require_once('vendor/autoload.php');
	Router::init();
}


$html = GoogleDocs::getArticle($_REQUEST['id']);

$groundskeeper = new Groundskeeper(array(
    'element-blacklist' => 'style,meta'
));



$html = $groundskeeper->clean($html);


$allowed_tags = 'p[class],img[src],h1,h2,h3,a[href],table,tr,td,i';
$allow_href_js = false;

$cleaner = new HtmlCleaner($allowed_tags, $allow_href_js);

$html = $cleaner->clean($html);


$html = preg_replace('/\s+/u', ' ', $html);//Все невидимые символы неразрывного пробела удаляем
//$html = preg_replace('/\s<\//', '</', $html);//Удаляем пробел перед закрывающим тегом <a>ссылки </a> 



//$html = preg_replace('/https:\/\/www.google.com\/url\?q=http','',$html);
//https://www.google.com/url?q=https://kemppi-nn.ru/contacts&amp;sa=D&amp;ust=1474575413967000&amp;usg=AFQjCNEMpDDy_ykh9PcNwX14uTdVZ-zb4A


$html = Rubrics::parse($html);

echo $html;
