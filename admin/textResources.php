<?php
/**
 * Base file for editing semi-dynamic text-resources.
 *
 * Contains the all code needed to edit text-resources of the page.
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package admin
 * @package content
 * @version 1.0
 */

/**
 * Standard inclusions.
 */
require '../__functions.php';
require '../_header.php';
require '../__connect.php';

/**
 * Define which types are allowed for text-resources.
 */
$textTypes = array (
	'text/html',
	'text/plain'
);

switch($_GET['task']){
	case 'editor':
		$resource = mysql_query("SELECT * FROM `__textResources` WHERE `textId` = '".myEscape($_GET['id'])."' LIMIT 1");
		if(mysql_num_rows($resource)){
			$resource = mysql_fetch_object($resource);
?>

<form action="<?php echo PATH?>/admin/textResources.php?task=editor&amp;id=<?php echo $resource->textId?>" method="post">
	<h2>Text resources &raquo; Editor &raquo; <?php echo $resource->textId?></h2>
	<div class="frame">
<?php
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				$update = mysql_query("UPDATE `__textResources` SET
	`textContent` = '".myEscape(trim($_POST['textContent']))."',
	`textType` = '".myEscape(trim($_POST['textType']))."'
WHERE
	`textId` = '".myEscape($_GET['id'])."'");

				if(mysql_errno() != 0)
					echo '<h3 class="failure">'.mysql_error().'</h3>';

				if($update)
					echo '<h3 class="success">Text was saved.</h3>';

				$resource = mysql_query("SELECT * FROM `__textResources` WHERE `textId` = '".myEscape($_GET['id'])."' LIMIT 1");
				$resource = mysql_fetch_object($resource);
			}
?>

		<div style="display: inline; float: right; width: 20%">
			<label for="textType" class="block">Content-type</label>
			<select id="textType" name="textType">
<?php
			foreach($textTypes as $type){
				echo '<option value="'.$type.'"';
				if($resource->textType == $type)
					echo ' selected="selected"';
				echo '>'.$type.'</option>';
			}
?>

			</select>
		</div>

		<label for="textComment" class="block">Comment</label>
		<pre id="textComment" style="width: 75%"><?php echo $resource->textComment?></pre>

		<label for="textContent" class="block">Content</label>
		<textarea id="textContent" name="textContent" cols="20" rows="40" style="width: 100%"><?php echo htmlspecialchars($resource->textContent)?></textarea>
	</div>
	<div class="submit">
		<input type="submit" value="save" />
	</div>
</form>

<?php
			if($resource->textType != 'text/plain'){
?>

<script type="text/javascript" src="<?php echo PATH?>/_lib/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	/* <![CDATA[ */
tinyMCE.init({
	mode : "exact",
	elements : 'textContent',
	convert_urls : false,

	theme : "advanced",
	plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",

	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",

	relative_urls : false,
	remove_script_host : false

});
$('.submit input').button();
	/* ]]> */
</script>

<?php
			}
			break;
		}
	default:
?>

<h2>Text resources</h2>
<?php
		$resources = mysql_query("SELECT * FROM `__textResources` ORDER BY `textId`");
		if(mysql_num_rows($resources) > 0){
?>

<table class="dataContainer">
	<tr>
		<th style="width: 5%"> </th>
		<th style="width: 20%">ID</th>
		<th style="width: 55%">Text</th>
		<th style="width: 20%">Type</th>
	</tr>
<?php
			$resource = new stdClass();
			while($resource = mysql_fetch_object($resources)){
?>

	<tr>
		<td><a href="<?php echo PATH?>/admin/textResources.php?task=editor&amp;id=<?php echo $resource->textId?>"><?php echo returnIcon('pencil')?></a></td>
		<td><strong><?php echo $resource->textId?></strong></td>
		<td><?php echo mb_substr(htmlspecialchars($resource->textContent), 0, 100)?>...</td>
		<td><pre><?php echo $resource->textType?></pre></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}
}

require '../__close.php';
require '../_footer.php';