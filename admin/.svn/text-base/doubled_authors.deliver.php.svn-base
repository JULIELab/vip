<?php
require '../__functions.php';
require '../__connect.php';

switch($_GET['deliver']){
	case 'unsimilarAuthors':
		mysql_query("INSERT INTO `authors_unsimilarPairs` (`authorOne`, `authorTwo`) VALUES (".((int) $_GET['one']).", ".((int) $_GET['two']).")");
		echo mysql_error();
	break;
	case 'similarAuthors':

		$row = new stdClass();
		$unsimilarAuthors = array();
		$unsimilarAuthorsResult = mysql_query("SELECT * FROM `authors_unsimilarPairs`");
		while($row = mysql_fetch_object($unsimilarAuthorsResult)){
			if(!is_array($unsimilarAuthors[$row->authorOne]))
				$unsimilarAuthors[$row->authorOne] = array();

			$unsimilarAuthors[$row->authorOne][] = $row->authorTwo;
		}

		$similarAuthorsResult = mysql_query("SELECT `author_id`, `second_id`, `name`, `name2`, `firstname`, `firstname2` FROM `authors` author1 LEFT JOIN (
	SELECT `name` AS `name2`, `firstname` AS `firstname2`, `author_id` AS `second_id` FROM `authors` ) author2
ON (
	SOUNDEX(author1.`name`) = SOUNDEX(author2.`name2`) AND
	SOUNDEX(author1.`firstname`) = SOUNDEX(author2.`firstname2`) AND
	author1.`author_id` < author2.`second_id`
) ORDER BY `name`, `firstname`");

		if(mysql_num_rows($similarAuthorsResult)){
			$pair = new stdClass();
			$i = (int) 0;
			$lastId = (int) 0;
			$lastSecondId = (int) 0;
			$skipped = (int) 0;
			$str = (string) '';
			while($pair = mysql_fetch_object($similarAuthorsResult)){
				if($pair->second_id == NULL)
					continue;

				if(in_array($pair->second_id, $unsimilarAuthors[$pair->author_id])){
					$skipped++;
					continue;
				}

				if($lastId != $pair->second_id and $lastSecondId != $pair->author_id){
					$str .= '<div id="pair_'.++$i.'" style="border-top: 1px solid #000; ; padding: 0.5em 0">';
					$str .= '<div id="pair_'.$i.'_two" style="float: right; width: 60%">
	<div style="font-size: 0.8em; float: right">
		<a href="javascript:;" onclick="unsimilarAuthors('.$pair->author_id.', '.$pair->second_id.', '.$i.')">'.returnIcon('cross').' unsimilar</a><br />
		<a href="#body" onclick="pair = '.$i.'; positionAuthor('.$pair->author_id.', 1);  positionAuthor('.$pair->second_id.', 2)">'.returnIcon('arrow-right').' position both</a>
	</div>
	<strong>'.$pair->name2.'</strong>, '.$pair->firstname2.'<br />
	<a href="javascript:;" onclick="pair = '.$i.'; positionAuthor('.$pair->second_id.', 1)">[1]</a>
	<a href="javascript:;" onclick="pair = '.$i.'; positionAuthor('.$pair->second_id.', 2)">[2]</a>
</div>';
					$str .= '<div id="pair_'.$i.'_one" style="float: left; width: 35%">
	<strong>'.$pair->name.'</strong>, '.$pair->firstname.'<br />
	<a href="javascript:;" onclick="pair = '.$i.'; positionAuthor('.$pair->author_id.', 1)">[1]</a>
	<a href="javascript:;" onclick="pair = '.$i.';positionAuthor('.$pair->author_id.', 2)">[2]</a>
</div>';
					$str .= '<br style="clear: both" /></div>';

					$lastId = $pair->author_id;
					$lastSecondId = $pair->second_id;
				}
			}

			if($skipped > 0)
				echo '<p>Skipped <strong>'.$skipped.' pairs</strong> because they have been marked as unsimilar earlier.</p>';
			echo $str;
		}
	break;
	case 'getAuthors':
		if($_GET['type'] == 'grouped')
			$authors = mysql_query("SELECT * FROM (SELECT *, COUNT(*) AS `count` FROM `authors` GROUP BY `name` ORDER BY `count` DESC, `name` ASC) AS auth WHERE auth.`count` > 1");
		elseif($_GET['type'] == 'alphabetically')
			$authors = mysql_query("SELECT *, COUNT(*) AS `count` FROM `authors` GROUP BY `name` ORDER BY `name`");

		if(mysql_num_rows($authors)){
?>

<strong>Listing: <?php echo $_GET['type']?></strong>
<table id="lastnames" class="dataContainer">
	<tr>
		<th>Name</th>
		<th>#</th>
	</tr>
<?php
			$author = new stdClass();
			while($author = mysql_fetch_object($authors)){
?>

	<tr>
		<td><a href="#body" onclick="getFirstnames('<?php echo $author->name?>')"><?=$author->name?></a></td>
		<td style="text-align: center"><?=$author->count?></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}
	break;

	case 'getFirstnames':
		$authors = mysql_query("SELECT * FROM `authors` WHERE `name` = '".mysql_real_escape_string(stripslashes($_GET['name']))."' ORDER BY `firstname`");

		if(mysql_num_rows($authors)){
?>

<strong>Lastname: <?php echo htmlspecialchars($_GET['name'])?></strong>
<table id="firstnames" class="dataContainer">
	<tr>
		<th style="width: 70%">Firstname</th>
		<th style="width: 30%">Pos.</th>
	</tr>
<?php
			$author = new stdClass();
			$lastFirstname = (string) '';
			while($author = mysql_fetch_object($authors)){
?>

	<tr>
		<td<?php if($author->firstname == $lastFirstname) echo ' style="font-weight: bold"'?>><?=$author->firstname?></td>
		<td style="text-align: center">
			<a href="#subnavbar" onclick="positionAuthor(<?=$author->author_id?>, 1)">[1]</a>
			<a href="#subnavbar" onclick="positionAuthor(<?=$author->author_id?>, 2)">[2]</a>
		</td>
	</tr>
<?php
				$lastFirstname = $author->firstname;
			}
?>

</table>
<?php
		}
	break;

	case 'positionAuthor':
		if(is_numeric($_GET['id'])){
			$author = mysql_query("SELECT * FROM `authors` WHERE `author_id` = ".((int) $_GET['id']));
			if(mysql_num_rows($author) == 1){
				$author = mysql_fetch_object($author);
				if(empty($author->mail))
					$author->mail = '<em>none given</em>';

				$color = '#f00';
				if($_GET['position'] == 1)
					$color = '#0a0';
?>

<div style="border: 2px solid <?php echo $color?>; padding: 0.5em">
<?php
				if($_GET['position'] == 2)
					echo '<strong>'.returnIcon('cross').' This author will be deleted!</strong><br /><br />';
				else
					echo '<em>'.returnIcon('tick').' This author will get the articles of the deleted one!</em><br /><br />';
?>

	<input type="text" size="4" id="mergeId_<?php echo $_GET['position']?>" name="mergeId_<?php echo $_GET['position']?>" readonly="readonly" value="<?=$author->author_id?>" style="display: inline; float: right; text-align: right" />
	<strong><?php echo $author->name?></strong>, <?php echo $author->firstname?><br />
	<?php echo returnIcon('email')?> <?php echo $author->mail?>
<?php
				$proceedings = mysql_query("SELECT proc.*, auth.*, allo.*, orga.*, inst.*, 1 AS `points`
	FROM `proceedings` proc, `authors` auth, `allocations` allo, `organizations` orga, `institutes` inst
	WHERE allo.`proceeding` = proc.`proceeding_id` AND allo.`author` = auth.`author_id` AND allo.`organization` = orga.`organization_id` AND allo.`institute` = inst.`institute_id` AND `author_id` = ".((int) $_GET['id'])."
	ORDER BY `year` DESC");
				if(mysql_num_rows($proceedings) > 0){
?>

	<br /><br /><strong>Articles</strong>
	<ol>
<?php
					$article = new stdClass();
					while($article = mysql_fetch_object($proceedings)){
						if(!empty($article->url))
							$article->url = ' <a href="'.$article->url.'">'.returnIcon('page-white-acrobat').'</a>';
						if(empty($article->organization_name))
							$article->organization_name = $article->organization_abbreviation;
						if(empty($article->institute_name))
							$article->institute_name = $article->institute_abbreviation;
?>

		<li>
			<strong><?php echo $article->conference.'-'.$article->year.'-'.$article->articlenumber.$article->url?></strong><br />
			<?php echo $article->organization_name?>, <?php echo $article->institute_name?>
		</li>
<?php
					}
?>

	</ol>
<?php
				}
?>

</div>
<?php
			}else{
?>

<div style="border: 2px solid #aa6; background: #ffa; color: #000; padding: 0.5em">
	<strong><?php echo returnIcon('error')?> Author doesn't exist!</strong>
</div>
<?php
			}
		}
	break;

	case 'mergeAuthors':
		if(is_numeric($_GET['author1']) and is_numeric($_GET['author2']) and $_GET['author1'] != $_GET['author2']){
			mysql_query("UPDATE `allocations` SET `author` = ".((int) $_GET['author1'])." WHERE `author` = ".((int) $_GET['author2']));
			$allocations = mysql_affected_rows();

			mysql_query("DELETE FROM `authors` WHERE `author_id` = ".((int) $_GET['author2']));
			$deletion = mysql_affected_rows();
?>

<div style="border: 1px solid #aaa; padding: 0.5em">
<strong>Merging</strong><br />
<strong><?php echo $allocations?> allocation(s)</strong> was/were affected by this merge.<br />
These allocations have been associated with the author you selected for that purpose.<br /><br />
<?php
			if($deletion === 1)
				echo returnIcon('tick').' The author you chose to be deleted, has been deleted!';
			else
				echo returnIcon('cross').' The author could <strong>not</strong> be deleted!';
		}else
			echo '<strong class="failure">'.returnIcon('cross').' Sorry! Something is wrong with your input!</strong>';
?>

</div>
<?php
	break;
}

require '../__close.php';
?>