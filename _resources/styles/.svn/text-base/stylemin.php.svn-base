<?php
/**
 * File gathers all css in one file and minifies it. Wraps the functionality of cssmin for our needs.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package template
 * @version 1.0
 */
require '../../__functions.php';
require '../../_lib/cssmin.php';

if(!empty($_GET['css_0'])){
	$files = array();
	$i = (int) 0;
	$filesString = (string) '';
	$newestFile = (int) 0;

	/**
	 * Get the list of files.
	 */
	do {
		$filesString .= $_GET['css_'.$i].nl;
		$files[] = $_GET['css_'.$i];

		if($newestFile < filemtime($_GET['css_'.$i]))
			$newestFile = filemtime($_GET['css_'.$i]);

		$i++;
	} while(!empty($_GET['css_'.$i]));

	if(count($files) > 0) {
		header('Content-Type: text/css; charset=utf8');
		echo '/*'.nl.$filesString.'*/';
		array_walk($files, 'trim');
		$css = (string) '';

		/**
		 * Generate a hash from this combination.
		 */
		$hash = md5($filesString);

		/**
		 * Check if we have this combination yet and output that.
		 * Ensure we always use the newest versions of the source files.
		 * We do that to ensure we do not need to clear the cache manually every time we change something in the source files.
		 */
		if(file_exists('../../cache/'.$hash.'.css'))
			if(filemtime('../../cache/'.$hash.'.css') < $newestFile)
				unlink('../../cache/'.$hash.'.css');
			else
				exit(readfile('../../cache/'.$hash.'.css'));

		/**
		 * Generate a new minified css.
		 */
		foreach($files as $file) {
			if(file_exists($file))
				$cssContent = file_get_contents($file);

			/**
			 * Rewrite references to fonts, images etc. with the relative path to the source file.
			 */
			$cssContent = preg_replace('~url\(\'([^\)]*)\'\)~i', 'url($1)', $cssContent);		// replace url('link') with url(link)
			$cssContent = preg_replace('~url\(([^\)]*)\)~i', 'url(\''.dirname($file).'/$1\')', $cssContent);		// replace url(link) with url('path_to_css_file/link')
			$cssContent = preg_replace('~\/([a-z0-9_-]*)\/\.\.\/~i', '/', $cssContent);		// strip /dirname/../ from the content

			$css .= $cssContent;
		}

		/**
		 * Minify the css.
		 */
		$css = CssMin::minify($css);

		/**
		 * Cache the minified css.
		 */
		$file = fopen('../../cache/'.$hash.'.css', 'w+');
		fwrite($file, $css);
		fclose($file);

		exit($css);
	}
}