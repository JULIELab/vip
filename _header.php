<?php
/**
 * This file contains the footer of the template.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package template
 * @version 1.0
 */

/**
 * Declare all needed css files relative to _resources/styles/stylemin.php
 */
$cssFiles = array (
	'../../_lib/jQuery/jquery-ui.css',
	'../../_lib/jQuery/jquery.jgrowl.css',
	'all.css'
);
/**
 * Declare all needed js files relative to _resources/scripts/scriptmin.php
 */
$jsFiles[] = '../../_lib/jQuery/jquery.js';
$jsFiles[] = '../../_lib/jQuery/jquery-ui.js';
$jsFiles[] = '../../_lib/jQuery/jquery-plugins.js';
$jsFiles[] = '../../_lib/jQuery/jquery.jgrowl.js';
$jsFiles[] = '../../_lib/jQuery/jquery.json.js';
$jsFiles[] = 'global.js';


$cssQuery = (string) '';
foreach($cssFiles as $key => $file){
	if(!empty($cssQuery))
		$cssQuery .= '&amp;';
	$cssQuery .= 'css_'.$key.'='.urlencode($file);
}
$jsQuery = (string) '';
foreach($jsFiles as $key => $file){
	if(!empty($jsQuery))
		$jsQuery .= '&amp;';
	$jsQuery .= 'js_'.$key.'='.urlencode($file);
}
?><!DOCTYPE html>
<html>
	<head>
		<title>Visibility in Proceedings - JULIE Lab - FSU Jena</title>
		<link rel="shortcut icon" href="<?php echo PATH?>/_resources/images/julieLab.png" type="image/png" />
		<link rel="icon" href="<?php echo PATH?>/_resources/images/julieLab.png" type="image/png" />
		<link rel="start" href="<?php echo PATH?>" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo PATH?>/_resources/styles/stylemin.php?<?php echo $cssQuery?>" />
		<link rel="stylesheet" type="text/css" media="all" href="<?php echo PATH?>/_resources/sprite/silk-icons.css" />
		<script type="text/javascript" src="<?php echo PATH?>/_resources/scripts/scriptmin.php?<?php echo $jsQuery?>"></script>
		<!--[if lt IE 9]><script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
	</head>
	<body id="top">
		<?php printMaintenanceMenu();?>
		<div id="wrapper">
			<div id="logos"><a href="http://www.uni-jena.de/" title="University of Jena" style="float: right"><img src="<?php echo PATH?>/_resources/images/uniJena.png" alt="Friedrich-Schiller-University Jena" width="403" height="30" /></a><a href="http://www.julielab.de/" title="Julie Lab"><img src="<?php echo PATH?>/_resources/images/julieLab.png" alt="JulieLab" width="46" height="30" /> JULIE Lab</a></div>
			<noscript><div id="javascriptNotice" class="notificationBlock"><?php echo returnIcon('error');?> We are sorry, but you need to enable javascript to use all features of this page!</div></noscript>
			<div id="header">
				<h1><a href="<?php echo PATH?>"><span class="capital">V</span>isibility <span class="capital">i</span>n <span class="capital">P</span>roceedings</a></h1>
				<a href="<?php echo PATH?>/admin/" id="maintenanceMenuTrigger">Maintenance</a>
				<div id="annotation">a project associated to <a href="http://www.julielab.de/">JULIE Lab</a> at <a href="http://www.uni-jena.de/">Friedrich-Schiller-Universit&auml;t Jena</a></div>
			</div>
			<div id="pubMenu"><a id="helptextToggler" href="javascript:;" onclick="$('.helptext').toggle('fade')">Help <?php echo returnIcon('help');?></a><a href="<?php echo PATH?>/list/"><?php echo returnIcon('table-multiple');?> List</a><a href="<?php echo PATH?>/poll/"><?php echo returnIcon('chart-bar');?> Poll</a><a href="<?php echo PATH?>/faq.php"><?php echo returnIcon('comments');?> FAQ</a></div>
			<div id="body">