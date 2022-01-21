<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';

if($_GET['action'] == 'truncate')
	mysql_query("TRUNCATE TABLE `__cachedQueries`");

$cachedQueriesSize = mysql_query("SHOW TABLE STATUS WHERE `Name` = '__cachedQueries'");
$cachedQueriesSize = mysql_fetch_object($cachedQueriesSize);
$cachedQueriesSize = $cachedQueriesSize->Data_length;

$cachedQueriesLength = mysql_query("SELECT SUM(LENGTH(`queryContent`)) AS `length` FROM `__cachedQueries`");
$cachedQueriesLength = mysql_fetch_object($cachedQueriesLength);
$cachedQueriesLength = $cachedQueriesLength->length;

if(empty($cachedQueriesLength))
	$cachedQueriesLength = 0;

$cachedQueries = mysql_query("SELECT *, LENGTH(`queryContent`) AS `length` FROM `__cachedQueries`");
?>

<h2>Maintenance &raquo; Cached queries</h2>

<div class="helptext">
	<strong>This page shows the cached query results.</strong><br />
	If you want, you can truncate the cached query table.
</div>

<div class="subNav">
	<a href="<?php echo PATH?>/admin/cachedQueries.php?action=truncate">[Truncate table]</a>
</div>

<strong>Fields length</strong>: <?php echo $cachedQueriesLength?><br />
<strong>Cached queries</strong>: <?php echo mysql_num_rows($cachedQueries)?>
<?php
if(mysql_num_rows($cachedQueries)){
?>

<table class="dataContainer">
	<tr>
		<th style="width: 30%">Hash</th>
		<th style="width: 30%">Size</th>
		<th style="width: 40%">Currency</th>
	</tr>
<?php
	$row = new stdClass();
	while($row = mysql_fetch_object($cachedQueries)){
?>

	<tr>
		<td style="text-align: right"><?php echo $row->queryHash?></td>
		<td style="text-align: center"><?php echo round($row->length / $cachedQueriesLength * $cachedQueriesSize / 1024, 2)?>KByte</td>
		<td><?php echo date('r', $row->queryCurrency)?></td>
	</tr>
<?php
	}
?>

	<tr class="semanticSeperation">
		<td> </td>
		<td style="text-align: center"><?php echo round($cachedQueriesSize / 1024 / 1024, 2)?>MByte</td>
		<td> </td>
	</tr>
</table>
<?php
}
require '../__close.php';
require '../_footer.php';
?>
