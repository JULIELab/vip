<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';

$randomSample = mysql_query("SELECT * FROM (SELECT * FROM `proceedings` WHERE `conference` != 'EMNLP' and `year` <= 2010 ORDER BY RAND() LIMIT 200) AS `proceedings` ORDER BY `year`, `conference`, `articlenumber`");

if(mysql_num_rows($randomSample) > 1){
?>

<h2>Random sample</h2>
<pre><?=htmlspecialchars("SELECT * FROM (SELECT * FROM `proceedings` WHERE `conference` != 'EMNLP' and `year` <= 2010 ORDER BY RAND() LIMIT 200) AS `proceedings` ORDER BY `year`, `conference`, `articlenumber`")?></pre>
<table class="dataContainer">
	<tr>
		<th>Row#</th>
		<th>Year</th>
		<th>Conference</th>
		<th>Articlenumber</th>
		<th>URL (if present)</th>
	</tr>

<?php
	$i = (int) 0;
	while($proceeding = mysql_fetch_object($randomSample)){
		$class = 'every';
		if($i++ % 2 == 0)
			$class = 'other';
?>

	<tr class="<?=$class?>">
		<td><?=$i?></td>
		<td><?=$proceeding->year?></td>
		<td><?=$proceeding->conference?></td>
		<td><?=$proceeding->articlenumber?></td>
		<td><?=$proceeding->url?></td>
	</tr>

<?php
	}
?>

</table>

<?php
}

require '../__close.php';
require '../_footer.php';
?>
