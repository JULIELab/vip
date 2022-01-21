<?php
/**
 * Establishes the connection to the database and initializes the connection.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package functions
 * @subpackage database
 * @version 1.0
 */

$databaseConnection = @mysql_connect('localhost', 'cl_vis', 'c1-v151');
$databaseSelection = @mysql_select_db('cl_vis');

if(!$databaseConnection or !$databaseSelection){
	echo '<h2>Database connection error</h2><p>Sorry, but we have no database connection at this moment. Please try again later.</p>';
	require '_footer.php';
	exit();
}

mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8'");