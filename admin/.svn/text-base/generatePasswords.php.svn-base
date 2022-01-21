<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';

$allAuthors = mysql_query("SELECT * FROM `authors` ORDER BY RAND()");
$authorsWithoutHash = mysql_query("SELECT * FROM `authors` WHERE `identificationHash` = '' ORDER BY RAND()");

?>

<div class="subNav">
	<a href="generatePasswords.php?action=forAllAuthors">generate passwords for all authors (<?=mysql_num_rows($allAuthors)?>)</a>
	<a href="generatePasswords.php?action=forAuthorsWithoutHash">generate passwords for authors without a password (<?=mysql_num_rows($authorsWithoutHash)?>)</a>
</div>
<h2>Generate passwords</h2>

<?php
if($_GET['action'] == 'forAllAuthors' or $_GET['action'] == 'forAuthorsWithoutHash'){
	$authors = $authorsWithoutHash;

	if($_GET['action'] == 'forAllAuthors')
		$authors = $allAuthors;

	if(mysql_num_rows($authors) > 0){
?>

<table class="dataContainer">
	<tr>
		<th style="width: 10%">ID</th>
		<th style="width: 30%">Name</th>
		<th style="width: 20%">Firstname</th>
		<th style="width: 40%">Hash</th>
	</tr>

<?php
		while($author = mysql_fetch_object($authors)){
			static $i = 0;

			$class = 'every';
			if($i++ % 2 == 0)
				$class = 'other';

			$hash = (string) generateRandomString();

			mysql_query("UPDATE `authors` SET `identificationHash` = '".mysql_real_escape_string(stripslashes($hash))."' WHERE `author_id` = ".((int) $author->author_id));
?>

	<tr class="<?=$class?>">
		<td><?=$author->author_id?></td>
		<td><?=$author->name?></td>
		<td><?=$author->firstname?></td>
		<td style="font-family: Monospace"><?=$hash?> (<?=strlen($hash)?>)</td>
	</tr>

<?php
		}
?>

</table>

<?php
	}else{
?>

	No authors...

<?php
	}
}

require '../__close.php';
require '../_footer.php';
?>