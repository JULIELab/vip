<?php
/**
 * Base file that contains a table to get an overview of spreading of articles.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package list
 * @version 1.0
 */

require '../__functions.php';
require '../_header.php';
require '../__connect.php';

$conferences = array();
$counts = array();
$conferenceTotal = array();

$result = mysql_query("SELECT * FROM `proceedings` GROUP BY `conference` ORDER BY `conference`");
while($conference = mysql_fetch_object($result))
	$conferences[] = $conference->conference;

$result = mysql_query("SELECT * FROM `proceedings` GROUP BY `year` ORDER BY `year`");
while($year = mysql_fetch_object($result))
	foreach($conferences as $conference){
		$counts[$year->year][$conference] = 0;
		$conferenceTotal[$conference] = 0;
}

$result = mysql_query("SELECT `year`, `conference`, COUNT(*) AS `count` FROM `proceedings` GROUP BY `year`, `conference` ORDER BY `year`, `conference`");
while($allocation = mysql_fetch_object($result)){
	$counts[$allocation->year][$allocation->conference] = $allocation->count;
}

?>

<h2>Spreading of articles over conferences and years</h2>
<div class="helptext">
	<strong>On this page you can get an overview of the database content.</strong><br />
	Feel free to click on any number that's clickable to get a listing of articles.<br/>
	Be aware that a bigger number of articles may require a lot of time.
</div>

<table id="articleSpreading" class="dataContainer">
	<tr>
		<th style="width: 5%">Years</th>
<?php
foreach($conferences as $conference){
?>

		<th style="width: <?php echo round(95 / (count($conferences) + 1), 2)?>%"><?=$conference?></th>
<?php
}
?>

		<th style="text-align: center; width: <?php echo round(95 / (count($conferences) + 1), 2)?>%">Total</td>
	</tr>
<?php
$i = (int) 0;

while($year = each($counts)){
	$yearsTotal = (int) 0;
?>

	<tr>
		<td style="background: #fff; color: #000; font-weight: bold; text-align: right"><?php echo $year['key']?></td>
<?php
	while($conference = each($year['value'])){
		$yearsTotal += $conference['value'];
		$conferenceTotal[$conference['key']] += $conference['value'];

		echo '<td style="text-align: center; border-right: 1px solid #000">';
		if($conference['value'] !== 0)
			echo '<a href="javascript:;" onclick="getArticles(\''.$conference['key'].'\', '.$year['key'].')">'.$conference['value'];
		else
			echo ' ';
		echo '</td>';
	}
?>

		<td style="text-align: center"><a href="javascript:;" onclick="getArticles(0, <?php echo $year['key']?>)"><?php echo $yearsTotal?></a></td>

	</tr>
<?php
}
?>

	<tr>
		<td class="foot" style="font-weight: bold; text-align: right">Total</td>
<?php
while($conference = each($conferenceTotal)){
?>

		<td class="foot"><a href="javascript:;" onclick="getArticles('<?php echo $conference['key']?>', 0)"><?php echo $conference['value']?></a></td>
<?php
}
?>

		<td class="foot"><a href="javascript:;" onclick="getArticles(0, 0)"><?php echo array_sum($conferenceTotal)?></a></td>
	</tr>
</table>

<div id="articleContainer"></div>
<script type="text/javascript" src="accumulationTable.js"></script>
<script type="text/javascript">
	/* <![CDATA[ */
$('#articleSpreading').floatingTableHead();
	/* ]]> */
</script>
<?php
require '../__close.php';
require '../_footer.php';