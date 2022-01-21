<?php
/**
 * File gathers all js in one file and minifies it.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package template
 * @version 1.0
 */

require '../../__functions.php';
require '../../_lib/jsmin.php';
if(!empty($_GET['js_0'])){
	$i = (int) 0;
	$files = array();
	$filesString = (string) '';
	$newestFile = (int) 0;

	do {
		if(file_exists($_GET['js_'.$i])){
			$filesString .= $_GET['js_'.$i].nl;
			$files[] = $_GET['js_'.$i];

			if($newestFile < filemtime($_GET['js_'.$i]))
				$newestFile = filemtime($_GET['js_'.$i]);
		}
		$i++;
	} while(!empty($_GET['js_'.$i]));

	if(count($files) > 0){
		header('Content-Type: text/javascript');
		echo '/*'.nl.$filesString.'*/';
		array_walk($files, 'trim');
		$js = (string) '';

		$hash = md5($filesString);
		if(file_exists('../../cache/'.$hash.'.js'))
			if(filemtime('../../cache/'.$hash.'.js') < $newestFile)
				unlink('../../cache/'.$hash.'.js');
			else
				exit(readfile('../../cache/'.$hash.'.js'));

		foreach($files as $file){
			if(file_exists($file))
				$js .= file_get_contents($file);
		}

		$js = JSMin::minify($js);
		$file = fopen('../../cache/'.$hash.'.js', 'w+');
		fwrite($file, $js);
		fclose($file);

		exit($js);
	}
}