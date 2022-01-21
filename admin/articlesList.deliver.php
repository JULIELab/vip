<?php
require '../__functions.php';
require '../__connect.php';

switch($_GET['deliver']){
	case 'deleteConfirm':
		$article = mysql_query("SELECT * FROM `proceedings` WHERE `proceeding_id` = ".((int) $_GET['id']));

		$title = (string) 'Confirm the delete';
		$text = (string) '<strong class="failure">An error occured...</strong>';

		if(mysql_num_rows($article) == 1){
			$article = mysql_fetch_object($article);

			$text = '<h3>'.$article->year.'-'.$article->conference.'-'.$article->articlenumber.'</h3>';

			$allocations = mysql_query("SELECT * FROM `allocations` allo, `organizations` orga, `institutes` inst, `authors` auth
WHERE allo.`author` = auth.`author_id` AND allo.`organization` = orga.`organization_id` AND allo.`institute` = inst.`institute_id`
AND `proceeding` = ".((int) $article->proceeding_id));

			if(mysql_num_rows($allocations)){
				$text .= '<br/>This article has <strong>'.mysql_num_rows($allocations).'</strong> allocations...';
				$text .= '<table class="dataContainer">';
				$text .= '<tr><th>Author</th><th>Organization</th><th>Institute</th></tr>';
				$allocation = new stdClass();
				while($allocation = mysql_fetch_object($allocations)){
					$text .= '<tr>';
					$text .= '<td>'.$allocation->firstname.' '.$allocation->name.'</td>';
					$text .= '<td>'.$allocation->organization_name.' ('.$allocation->organization_abbreviation.')</td>';
					$text .= '<td>'.$allocation->institute_name.' ('.$allocation->institute_abbreviation.')</td>';
					$text .= '</tr>';
				}
				$text .= '</table>';
			}
		}

		echo dialogCreate('deleteArticleConfirm_'.((int) $_GET['id']), $title, $text);
	break;

	case 'delete':
		$title = (string) 'Delete article';
		$text = (string) 'An error occured!';
		$state = (string) 'failure';

		$article = mysql_query("SELECT * FROM `proceedings` WHERE `proceeding_id` = ".((int) $_GET['id']));
		if(mysql_num_rows($article) == 1){
			$article = mysql_fetch_object($article);
			$allocations = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `proceeding` = ".((int) $_GET['id'])));

			if(mysql_query("DELETE FROM `proceedings` WHERE `proceeding_id` = ".((int) $article->proceeding_id)." LIMIT 1") and
				mysql_query("DELETE FROM `allocations` WHERE `proceeding` = ".((int) $article->proceeding_id))." LIMIT ".$allocations){
				$text = '<strong class="success">'.$article->year.'-'.$article->conference.'-'.$article->articlenumber.'</strong> and its <strong>'.$allocations.'</strong> allocations where deleted!';
				$state = 'success';
			}
		}

		echo json_encode(
			array(
				'title' => $title,
				'text' => $text,
				'state' => $state
			)
		);
	break;

	case 'list':
		$userChoices = (string) '';
		if(!empty($_GET['conference']))
			$userChoices .= " `conference` = '".mysql_real_escape_string(stripslashes($_GET['conference']))."'";

		if(!empty($_GET['year'])){
			if(!empty($userChoices))
				$userChoices .= " AND";
			$userChoices .= " `year` = '".((int) $_GET['year'])."'";
		}

		if(empty($_GET['offset']))
			$_GET['offset'] = 0;
		if(empty($_GET['limit']))
			$_GET['limit'] = 30;

		if(!empty($userChoices))
			$userChoices = " WHERE".$userChoices;

		$articles = mysql_query("SELECT * FROM `proceedings`".$userChoices." ORDER BY `year` DESC, `conference`, `articlenumber`, `entering_time` DESC LIMIT ".((int) $_GET['offset']).", ".((int) $_GET['limit']));
		if(mysql_num_rows($articles) > 0){
?>

<pre>Showing results <strong><?php echo $_GET['offset']?></strong> to <strong><?php echo ($_GET['offset'] + mysql_num_rows($articles))?></strong> of <?php echo mysql_num_rows(mysql_query("SELECT * FROM `proceedings`".$userChoices.""))?></pre>
<table class="dataContainer" style="display: none">
	<tr>
		<th style="width: 10%">&nbsp;</th>
		<th>Identification</th>
		<th>Entering time</th>
		<th style="width: 5%">&nbsp;</th>
	</tr>
<?php
			$article = new stdClass();
			while($article = mysql_fetch_object($articles)){
				if(!empty($article->url))
					$article->url = '<a href="'.$article->url.'">'.returnIcon('page-white-acrobat').'</a>';
				if(!empty($article->bibtex))
					$article->bibtex = ' <a href="'.$article->bibtex.'">'.returnIcon('page-white-text').'</a>';
?>

	<tr>
		<td><?php echo $article->url.$article->bibtex?></td>
		<td><strong><?php echo $article->year.'-'.$article->conference.'-'.$article->articlenumber?></strong></td>
		<td><?php echo $article->entering_time?></td>
		<td><a href="javascript:;" onclick="deleteArticleConfirm(<?php echo $article->proceeding_id?>)"><?php echo returnIcon('delete')?></a></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}else{
?>

<h3 class="failure">Query failed</h3>
<p>There are no articles in the database for your selection!</p>
<?php
		}
	break;
}

require '../__close.php';