<?php
/**
 * Initializes the system and provides several functions.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package functions
 * @version 1.0
 */

define('PATH', '/vip');		// Define the root path to the script.
define('STANDARD_PAGE', '/poll');		// Define the standard page.
define('MAIL_SENDER_NAME', 'ViP Poll Team');		// Define the name that will be used for sending mails.
define('MAIL_SENDER_ADDRESS', 'vip-julielab@listserv.uni-jena.de');		// Define the adress that will be used for sending mails.
define('ROOT_PATH', str_replace('\\', '/', preg_replace('~'.preg_quote(dirname($_SERVER['PHP_SELF']), '$~').'~', '', dirname(__FILE__), 1)).'/');

date_default_timezone_set('Europe/Berlin');		// Set default timezone.

/**
 * Enable GZ-compression and output-buffering.
 */
ini_set('zlib.output_compression', 'On');
ob_start();
header('Content-Type: text/html; charset=utf-8');

/**
 * Set memory handling.
 */
ini_set('memory_limit', '2048M');
$systemMemoryUsage = memory_get_usage(TRUE);

/**
 * Set error handling.
 */
error_reporting(E_ALL ^ E_NOTICE);
/*ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('ignore_repeated_errors', 1);
ini_set('ignore_repeated_source', 0);
ini_set('report_memleaks', 1);
ini_set('track_errors', 0);
ini_set('html_errors', 0);
ini_set('error_prepend_string', 'An error occured!');
ini_set('error_prepend_string', 'Please return to homepage!');
ini_set('error_log', str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']).'errors.log');*/

/**
 * Refresh cache contents at least once per hour.
 */
$cacheContent = scandir(ROOT_PATH.'cache/');
foreach($cacheContent as $object){
	if($object{0} == '.' or $object == '..' or $object == 'index.php')
		continue;

	if(filemtime(ROOT_PATH.'cache/'.$object) < time() - 3600)
		unlink(ROOT_PATH.'cache/'.$object);
}

/**
 * Define some constants needed for output formatting.
 */
define('nl', PHP_EOL);
define('tab', "\t");

/**
 * Define some globally needed variables.
 */
$jsFiles = array();

/**
 * Send a text/plain mail.
 * @param string $recipientName Name of recipient.
 * @param string $recipientMail Mail address of recipient.
 * @param string $subject Subject of mail.
 * @param string $message Message text of mail.
 * @param string $senderName Name of sender.
 * @param string $senderMail Mail address of sender.
 * @return bool Wether the mail has been sent or not.
 */
function mail_text ($recipientName, $recipientMail, $subject, $message) {
	$senderName = MAIL_SENDER_NAME;
	$senderMail = MAIL_SENDER_ADDRESS;

	$to = $recipientName.' <'.$recipientMail.'>';

	$headers = 'From: '.$senderName.' <'.$senderMail.'>'."\n"
		.'Content-Type: text/plain; charset=utf8'."\n"
		.'Reply-To: '.$senderName.' <'.$senderMail.'>'."\n"
		.'Return-Path: '.$senderName.' <'.$senderMail.'>'."\n"
		.'Message-Id: <'.md5($message).'@'.str_replace('http://', '', str_replace('www.', '', DOMAIN)).'>';

	return @imap_mail($to, $subject, $message, $headers);
}

/**
 * Check if a string is a mail address
 * @param string $mail Mail address that shall be checked.
 * @return bool TRUE if string is a mail address, FALSE otherwise.
 */
function is_mail ($mail) {
	if(strlen($mail) <= 340)
		return preg_match("~^[a-z0-9!$'*+\-_]+(\.[a-z0-9!$'*+\-_]+)*@([a-z0-9]+(-+[a-z0-9]+)*\.)+([a-z]{2}|aero|arpa|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|travel)$~i", $mail);

	return FALSE;
}

/**
 * Check if a string is an URL.
 * @param string $url URL that shall be checked.
 * @return bool TRUE if string is url, FALSE otherwise.
 */
function is_url ($url) {
	return preg_match('!^(([\w]+:)?//)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?!', $url);
}

/**
 * Get the name of an author by his/her ID.
 * @param int $authorId ID of author
 * @return string Name of author.
 */
function getAuthorsName ($authorId) {
	if(is_numeric($authorId)){
		$author = mysql_query("SELECT * FROM `authors` WHERE `author_id` = ".((int) $authorId));
		if(mysql_num_rows($author) > 0){
			$author = mysql_fetch_object($author);
			return (string) $author->name.', '.$author->firstname;
		}
	}

	return FALSE;
}

/**
 * Get the name of an organization by its ID.
 * @param int $organizationId ID of organization.
 * @return string Name of organization.
 */
function getOrganizationsName ($organizationId) {
	if(is_numeric($organizationId)){
		$organization = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $organizationId));
		if(mysql_num_rows($organization) > 0){
			$organization = mysql_fetch_object($organization);
			return $organization->organization_name.', '.$organization->organization_abbreviation;
		}
	}

	return FALSE;
}

/**
 * Translate ISO-3166-alpha3 to ISO-3166-alpha2.
 * @staticvar array $nation2 Cache for ISO-3166-alpha2 codes.
 * @param string $nation3 Nation abbreviation coded as ISO-3166-alpha3.
 * @return string Nation abbreviation coded as ISO-3166-alpha2.
 */
function getNation2 ($nation3) {
	static $nation2 = array();

	if(empty($nation2[$nation3])){
		$result = mysql_query("SELECT * FROM `nations` WHERE `nationAbbreviation` = '".myEscape($nation3)."' LIMIT 1");
		if(mysql_num_rows($result) == 1){
			$result = mysql_fetch_object($result);
			$nation2[$result->nationAbbreviation] = $result->nationAbbreviation2;
		}else
			$nation2[$result->nationAbbreviation] = '&nbsp;';
	}

	return $nation2[$nation3];
}

/**
 * Get the name of a nation by its abbreviation
 * @staticvar array $nationsCache
 * @param string $nationAbbreviation Abbreviation of nation.
 * @return string Name of a nation.
 */
function getNation ($nationAbbreviation) {
	static $nationsCache = array();

	if(empty($nationsCache[$nationAbbreviation])){
		$dummy = new stdClass;
		$nation = mysql_query("SELECT * FROM `nations` WHERE `nationAbbreviation` = '".$nationAbbreviation."'");
		if(mysql_num_rows($nation) == 1){
			$dummy = mysql_fetch_object($nation);
			$nationsCache[$nationAbbreviation] = $dummy->nationName;

		}else
			$nationsCache[$nationAbbreviation] = $nationAbbreviation;
	}

	return $nationsCache[$nationAbbreviation];
}

/**
 * Get HTML-snippet that displays the flag of a country.
 * @param string $nationAbbreviation
 * @param type $pathCorrection
 * @param type $code
 * @return type
 */
function getFlag ($nationAbbreviation, $pathCorrection = './', $code = 2) {
	if($code == 2 and file_exists($pathCorrection.'_resources/images/flags/'.strtolower(getNation2($nationAbbreviation)).'.png'))
		return '<img src="'.$pathCorrection.'_resources/images/flags/'.strtolower(getNation2($nationAbbreviation)).'.png" alt="Flag of '.getNation($nationAbbreviation).'" title="Flag of '.getNation($nationAbbreviation).'" class="flag2" />';

	if($code == 3 and file_exists($pathCorrection.'_resources/images/flags/'.strtoupper($nationAbbreviation).'.png'))
		return '<img src="'.$pathCorrection.'_resources/images/flags/'.strtoupper($nationAbbreviation).'.png" alt="Flag of '.getNation($nationAbbreviation).'" title="Flag of '.getNation($nationAbbreviation).'" class="flag3" />';

	return '&nbsp;';
}

/**
 * Get a text resource.
 * @param string $textId The ID of the text resource.
 * @return mixed The resource object on success or FALSE on error.
 */
function getTextResource ($textId) {
	$resource = mysql_query("SELECT * FROM `__textResources` WHERE `textId` = '".myEscape($textId)."'");
	if(mysql_num_rows($resource)){
		$resource = mysql_fetch_object($resource);

		return $resource;
	}

	return FALSE;
}

/**
 * Generate a random string.
 * @param int $length Length of the random string.
 * @return string A random string.
 */
function generateRandomString ($length = 50) {
	$chars = array (
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'_'
	);

	$str = (string) '';

	for($i = 1; $i <= $length; $i++)
		$str .= $chars[mt_rand(0, count($chars) - 1)];

	return $str;
}

/**
 * Output seconds as human readable string.
 * @param int $seconds Amount of seconds.
 * @return string Amount of seconds output as string.
 */
function secondsToReadableString ($seconds) {
	$hours = $minutes  = (int) 0;

	if(floor($seconds / 3600) > 0){
		$hours = floor($seconds / 3600);
		$seconds -= $hours * 3600;
	}

	if(floor($seconds / 60) > 0){
		$minutes = floor($seconds / 60);
		$seconds -= $minutes * 60;
	}

	return $hours.'h '.$minutes.'min '.$seconds.'s';
}

/**
 * Create an HTML-snippet that represents a dialog.
 * @param string $id ID of the div.
 * @param string $title Title of the dialog.
 * @param string $text Text of the dialog.
 */
function dialogCreate ($id, $title, $text) {
	echo '<div id="'.$id.'" title="'.$title.'" class="ui-dialog">'.$text.'</div>';
}

/**
 * Compare two objects by amount of points and name.
 * @param stdClass $a Object #1.
 * @param stdClass $b Object #2.
 * @return int Comparison result.
 */
function sortIt ($a, $b) {
	$a->points = round($a->points, 2);
	$b->points = round($b->points, 2);

	if($a->points > $b->points)
		return -1;
	elseif($a->points < $b->points)
		return +1;
	else{
		if($a->name > $b->name)
			return +1;
		elseif($a->name < $b->name)
			return -1;
		else
			return 0;
	}
}

/**
 * Walk an array and search the objects for a given ID.
 * @param array $array Array containing objects.
 * @param int $queryId ID that is searched for.
 * @return mixed Key of the position inside the array, or FALSE otherwise.
 */
function searchIt (array $array, $queryId) {
	foreach($array as $pos => $row)
		if($row->id == $queryId)
			return $pos;

	return FALSE;
}

/**
 * Escape a string for usage in MySQL query.
 * @param string $string Component of MySQL query that shall be escaped.
 * @return string Escaped string.
 */
function myEscape ($string) {
	return mysql_real_escape_string(stripslashes($string));
}

/**
 * Print the data of an organization. This functions is for standardization purposes.
 * @param mixed $organization The object that represents a MySQL result or the id of an organization.
 * @return string Printed data of an organization.
 */
function printOrganizationData ($organization) {
	static $cache = array();

	if(!is_object($organization)){
		$organization = (int) $organization;

		if(empty($cache[$organization]))
			$cache[$organization] = mysql_fetch_object(mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".$organization));

		$organization = $cache[$organization];
	}

	if(!empty($organization->organization_website) and is_url($organization->organization_website))
		$organization->organization_website = '<a href="'.$organization->organization_website.'">'.returnIcon('world').' '.$organization->organization_website.'</a>';

	return '<strong>'.$organization->organization_name.'</strong> ('.$organization->organization_abbreviation.')<br />'
		.$organization->organization_city.' <strong>'.$organization->organization_county.'</strong> '.getFlag($organization->organization_nation, '../').getNation($organization->organization_nation).'<br />'
		.$organization->organization_website;
}

/**
 * Print the data of an institution. This functions is for standardization purposes.
 * @param mixed $institute The object that represents a MySQL result or the id of an institute.
 * @return string Printed data of an institute.
 */
function printInstituteData ($institute) {
	static $cache = array();

	if(!is_object($institute)){
		$institute = (int) $institute;

		if(empty($cache[$institute]))
			$cache[$institute] = mysql_fetch_object(mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".$institute));

		$institute = $cache[$institute];
	}

	if(!empty($institute->institute_website) and is_url($institute->institute_website))
		$institute->institute_website = '<a href="'.$institute->institute_website.'">'.returnIcon('world').' '.$institute->institute_website.'</a>';

	return '<strong>'.$institute->institute_name.'</strong> ('.$institute->institute_abbreviation.')<br />'
		.$institute->institute_city.' <strong>'.$institute->institute_county.'</strong> '.getFlag($institute->institute_nation, '../').getNation($institute->institute_nation).'<br />'
		.$institute->institute_website;
}

/**
 * Print the reference to an icon.
 * @param string $icon Identification of the icon.
 * @param string $type Type of the printed icon.
 * @return string Reference to icon.
 */
function returnIcon ($icon, $type = 'sprite') {
	if($type == 'sprite')
		return '<span class="silk-icon silk-icon-'.htmlspecialchars($icon).'">&nbsp;</span>';
}

/**
 * Print the maintenance menu.
 */
function printMaintenanceMenu () {
	$request = explode('/', preg_replace('~^'.PATH.'~', '', $_SERVER['REQUEST_URI']));
	if(in_array($request[1], array('admin', 'enter', 'list'))){
?>

<div id="maintenanceMenu" onmouseover="$('#maintenanceMenuNavigation').show()" onmouseout="$('#maintenanceMenuNavigation').hide()">
	<strong id="maintenanceMenuTitle"><?php echo returnIcon('wrench');?> Maintenance menu</strong>
	<div id="maintenanceMenuNavigation">
		<strong>Annotation</strong>
		<a href="<?php echo PATH?>/enter/index.php"><?php echo returnIcon('keyboard');?> Articles</a>
		<a href="<?php echo PATH?>/enter/codebook.php"><?php echo returnIcon('report');?> Codebook</a>

		<strong>Content maintenance</strong>
		<a href="<?php echo PATH?>/admin/faq.php"><?php echo returnIcon('help');?> FAQs</a>
		<a href="<?php echo PATH?>/admin/textResources.php"><?php echo returnIcon('page-white-text');?> Text resources</a>

		<strong>Database maintenance</strong>
		<a href="<?php echo PATH?>/admin/databaseConsistency.php"><?php echo returnIcon('database-error');?> Consistency checks</a>
		<a href="<?php echo PATH?>/admin/cachedQueries.php"><?php echo returnIcon('database-save');?> Cached queries</a>

		<a href="<?php echo PATH?>/admin/organizationAdministration.php" style="margin: 0.5em 0 0 0"><?php echo returnIcon('bricks');?> Edit organizations</a>
		<a href="<?php echo PATH?>/admin/instituteAdministration.php"><?php echo returnIcon('brick');?> Edit institutes</a>
		<a href="<?php echo PATH?>/admin/authorAdministration.php"><?php echo returnIcon('group')?> Edit authors</a>
		<a href="<?php echo PATH?>/admin/doubled_authors.php"><?php echo returnIcon('arrow-join');?> Merge authors</a>

		<a href="<?php echo PATH?>/admin/articlesList.php" style="margin: 0.5em 0 0 0"><?php echo returnIcon('book');?> List articles</a>


		<strong>Poll related</strong>
		<a href="<?php echo PATH?>/admin/generatePasswords.php"><?php echo returnIcon('user');?> Generate passwords</a>
		<a href="<?php echo PATH?>/admin/updateAuthorsData.php"><?php echo returnIcon('user-edit');?> Update authors data</a>
		<a href="<?php echo PATH?>/admin/sendInvitationMails.php"><?php echo returnIcon('email');?> Send invitations</a>
	</div>
</div>
<?php
	}
}