<?php
require '../__functions.php';
require '../__connect.php';

$title = 'Error';
$text = 'An error occured.';
switch($_GET['task']){
	case 'editAuthor':
		if(is_numeric($_GET['author_id'])){
			$result = mysql_query("SELECT `author_id`, `firstname`, `name`, `mail` FROM `authors` WHERE `author_id` = ".((int) $_GET['author_id'])." LIMIT 1");
			if(mysql_num_rows($result) > 0){
				$author = mysql_fetch_object($result);

				if(!empty($_GET['firstname']) and !empty($_GET['name'])){
					$result = mysql_query("UPDATE `authors` SET
		`firstname` = '".mysql_real_escape_string(stripslashes($_GET['firstname']))."',
		`name` = '".mysql_real_escape_string(stripslashes($_GET['name']))."',
		`mail` = '".mysql_real_escape_string(stripslashes($_GET['mail']))."'
	WHERE
		`author_id` = ".((int) $_GET['author_id'])."
	LIMIT 1");

					if($result)
						$text = 'Author was edited!';
				}else
					$text = 'You have to fill name and firstname!';
			}
		}

		echo json_encode(array(
			'text' => $text
		));
	break;

	case 'getAuthorEditForm':
		if(is_numeric($_GET['author_id'])){
			$result = mysql_query("SELECT `author_id`, `firstname`, `name`, `mail` FROM `authors` WHERE `author_id` = ".((int) $_GET['author_id'])." LIMIT 1");
			if(mysql_num_rows($result) > 0){
				$author = mysql_fetch_object($result);
				$title = 'Edit author '.htmlspecialchars($author->name.', '.$author->firstname);
				$text = '<table style="width: 100%">
	<tr>
		<td style="width: 50%">
			<label for="authorFirstname" class="block">Firstname*</label>
			<input type="text" id="authorFirstname" name="authorFirstname" style="width: 100%" value="'.htmlspecialchars($author->firstname).'" />
		</td>
		<td style="width: 50%">
			<label for="authorName" class="block">Name*</label>
			<input type="text" id="authorName" name="authorName" style="width: 100%" value="'.htmlspecialchars($author->name).'" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label for="authorMail" class="block">Mail</label>
			<input type="text" id="authorMail" name="authorMail" style="width: 100%" value="'.htmlspecialchars($author->mail).'" />
		</td>
	</tr>
</table>';
			}
		}

		dialogCreate('authorEditForm_'.((int) $_GET['author_id']), $title, $text);
	break;

	case 'searchAuthors':
		$return = array();

		if(mb_strlen($_GET['query']) > 3){
			$result = mysql_query("SELECT `author_id`, `firstname`, `name`, `mail`, `relevancy` FROM (
		SELECT
			`author_id`,
			`firstname`,
			`name`,
			`mail`, (
				MATCH(`firstname`, `name`, `mail`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['query']))."')
			) AS `relevancy` FROM `authors`
		) fullTextSearch
	WHERE
		`relevancy` > 0
	ORDER BY
		`relevancy` DESC");
			if(mysql_num_rows($result) > 0)
				while($author = mysql_fetch_object($result))
					$return[] = $author;
		}

		echo json_encode($return);
	break;
}

require '../__close.php';