<?php
/**
 * This file updates authors data with an CSV-file.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package admin
 * @version 1.0
 */

require '../__functions.php';
require '../_header.php';
require '../__connect.php';
?>

<h2>Maintenance &raquo; Update authors data</h2>

<div class="helptext">
	<strong>On this page you can update the poll related data of authors.</strong><br />
	Simply upload an CSV file with rows in format:<br />
	<pre>ID; 'Mail'</pre><br /><br />
	The authors will be updated and afterwards set as candidates for poll.
</div>
<?php
$data = array();
$row = (string) '';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$data = file($_FILES['file']['tmp_name']);
	reset($data);
?>

<table class="dataContainer">
	<tr>
		<th>ID</th>
		<th>Mail</th>
		<th>Done?</th>
		<th>Error</th>
	</tr>

<?php
	$i = (int) 0;
	while($row = each($data)){
		$row = $row['value'];
		$row = explode(';', $row);
		$row[0] = trim($row[0]);
		$row[1] = trim($row[1]);

		$row[1] = str_replace("\n", '', str_replace("\r", '', $row[1]));

		$class = 'every';
		if($i++ % 2 == 0)
			$class = 'other';
?>

	<tr class="<?=$class?>">
		<td><?=$row[0]?></td>
		<td><?=$row[1]?></td>
		<td><? if(mysql_query("UPDATE `authors` SET `mail` = '".$row[1]."', `candidateForPoll` = 1 WHERE `author_id` = ".$row[0])) echo 'yes';?></td>
		<td><?=mysql_error()?></td>
	</tr>

<?php
	}
?>

</table>

<?php
}
?>


<form enctype="multipart/form-data" action="<?php echo PATH?>/admin/updateAuthorsData.php" method="POST">
	<div>
		<input type="file" id="file" name="file" />
		<input type="submit" value="read" />
	</div>
</form>

<?php
require '../__close.php';
require '../_footer.php';
?>