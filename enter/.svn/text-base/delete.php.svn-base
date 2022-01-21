<?php
/**
 * If something went wrong while annotating the annotator can delete the written data by triggering this file.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package annotation
 * @version 1.0
 */
require '../__functions.php';
require '../_header.php';
require '../__connect.php';
?>

		<h2>Delete</h2>
<?php
if(is_numeric($_POST['proceeding'])){
	mysql_query("DELETE FROM `proceedings` WHERE `proceeding_id` = ".myEscape($_POST['proceeding']));
	if(mysql_affected_rows() == 1)
		echo 'proceeding has been deleted!<br />';
}
$allocations = 0;
for($i = 1; $i <= $_POST['allocations']; $i++){
	if(is_numeric($_POST['allocation'][$i])){
		mysql_query("DELETE FROM `allocations` WHERE `id` = ".myEscape($_POST['allocation'][$i]));
		if(mysql_affected_rows() == 1)
			$allocations++;
	}
}
echo $allocations.' allocation(s) has/have been deleted!<br />';

require '../__close.php';
require '../_footer.php';