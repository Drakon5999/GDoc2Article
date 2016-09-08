<?php
use infrajs\router\Router;
use infrajs\ans\Ans;
use drakon5999\gdoc2article\GoogleDocs;
use Groundskeeper\Groundskeeper;

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



$AllowedTags1 = array("br", "col", "hr", "img"); 

$AllowedTags2 = array("a", "b", "blink", "blockquote", "caption", "center", "col", "colgroup", 
  "em", "font", "h1", "h2", "h3", "h4", "h5", "h6", "hr", "li", "marquee", "ol", "p", "pre", "s", 
  "small", "span", "strike", "strong", "sub", "sup", "table", "tbody", "td", "tfoot", "th", 
  "thead", "tr", "tt", "u", "ul"); 

$AllowedAttr = array("abbr", "align", "alt", "axis", "background", "behavior", "bgcolor", "border", "bordercolor", 
  "bordercolordark", "bordercolorlight", "bottompadding", "cellpadding", "cellspacing", "char", 
  "charoff", "cite", "clear", "color", "cols", "direction", "face", "font-weight", "headers", 
  "height", "href", "hspace", "leftpadding", "loop", "noshade", "nowrap", "point-size", "rel", 
  "rev", "rightpadding", "rowspan", "rules", "scope", "scrollamount", "scrolldelay", "size", 
  "span", "src", "start", "summary", "target", "title", "toppadding", "type", "valign", 
  "value", "vspace", "width", "wrap"); 



$t1='/<['.implode('|',$AllowedTags1).'].*>/i'; 
$t2='/<('.implode('|',$AllowedTags2).')([\s\t\n]*)(.*)([\s\t\n]*)>(.*)<\/('.implode('|',$AllowedTags2).')([\s\t\n]*)>/is'; 
$a='['.implode ('|',$AllowedAttr).']'; 

function ins($match){ 
    //echo '<br>'; 
    //echo htmlspecialchars($match[0]); 
}

preg_replace_callback($t2, 'ins', $cleanHtml); 



echo $cleanHtml;
