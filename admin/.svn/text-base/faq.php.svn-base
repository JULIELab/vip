<?php
/**
 * This is the editor for FAQ entries.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package admin
 * @subpackage content
 * @version 1.0
 */

require '../__functions.php';
require '../_header.php';
require '../__connect.php';

if(count($_POST) == 0){
	$_POST = array (
		'faqId' => (int) 0,
		'faqQuestion' => (string) '',
		'faqAnswer' => (string) '',
		'faqPublic' => (int) 0
	);
}

switch($_GET['task']){
	case 'editor':
		if($_SERVER['REQUEST_METHOD'] == 'POST'){

			if($_POST['faqPublic'] == 'on')
				$_POST['faqPublic'] = 1;
			else
				$_POST['faqPublic'] = 0;

			if(is_numeric($_GET['edit'])){
				mysql_query("UPDATE `_faqContainer` SET
	`faqQuestion` = '".mysql_real_escape_string(stripslashes($_POST['faqQuestion']))."',
	`faqAnswer` = '".mysql_real_escape_string(stripslashes($_POST['faqAnswer']))."',
	`faqPublic` = '".$_POST['faqPublic']."'
WHERE
	`faqId` = ".((int) $_GET['edit'])."");
			}else{
				mysql_query("INSERT INTO `_faqContainer` (`faqQuestion`, `faqAnswer`, `faqPublic`) VALUES (
	'".mysql_real_escape_string(stripslashes($_POST['faqQuestion']))."',
	'".mysql_real_escape_string(stripslashes($_POST['faqAnswer']))."',
	'".$_POST['faqPublic']."'
)");
				$_GET['edit'] = mysql_insert_id();
			}
		}

		if(is_numeric($_GET['edit'])){
			$faq = mysql_query("SELECT * FROM `_faqContainer` WHERE `faqId` = ".((int) $_GET['edit']));
			$_POST = mysql_fetch_assoc($faq);
			$checked = (string) '';
			if($_POST['faqPublic'] == 1 or $_POST['faqPublic'] == 'on')
				$checked = 'checked="checked" ';
		}
?>

<form action="<?php echo PATH?>/admin/faq.php?task=editor<?php if(is_numeric($_GET['edit'])) echo '&amp;edit='.$_GET['edit']?>" method="post">
	<h2>Maintenance &raquo; FAQ Editor</h2>
	<div class="frame">
		<div style="float: right"><input type="checkbox" id="faqPublic" name="faqPublic" <?=$checked?>/> <label for="faqPublic">Public?</label></div>

		<label for="faqQuestion" class="block">Question?</label>
		<textarea id="faqQuestion" name="faqQuestion" rows="5" cols="50" style="width: 100%"><?=$_POST['faqQuestion']?></textarea>

		<label for="faqAnswer" class="block">Answer!</label>
		<textarea id="faqAnswer" name="faqAnswer" rows="5" cols="50" style="width: 100%"><?=$_POST['faqAnswer']?></textarea>
	</div>

	<div class="submit">
		<input type="submit" value="save" />
	</div>
</form>

<script type="text/javascript" src="<?php echo PATH?>/_lib/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	/* <![CDATA[ */
tinyMCE.init({
	mode : "exact",
	elements : 'faqAnswer',
	convert_urls : false,

	theme : "advanced",
	plugins : "spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

	theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",

	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",

	content_css : "<?php echo PATH?>/screen.css"
});

$('.submit input').button();
	/* ]]> */
</script>

<?php
	break;
	default:
		$faq = mysql_query("SELECT * FROM `_faqContainer` ORDER BY `faqId` ASC");
		if(mysql_num_rows($faq) > 0){
?>

<div class="subNav"><a href="<?php echo PATH?>/admin/faq.php?task=editor">Create a new FAQ</a></div>
<h2>Maintenance &raquo; FAQs</h2>

<div class="helptext">
	<strong>This page shows a list of FAQs.</strong><br />
	To edit a FAQ entry click on the pencil next to the entry you want to alter.
</div>

<table class="dataContainer">
	<tr>
		<th></th>
		<th>Question?</th>
		<th>Answer!</th>
		<th>Public?</th>
	</tr>

<?php
			$faqEntry = new stdClass();
			$i = (int) 0;
			while($faqEntry = mysql_fetch_object($faq)){
				if($faqEntry->faqPublic)
					$faqEntry->faqPublic = returnIcon('tick');
				else
					$faqEntry->faqPublic = returnIcon('cross');
?>

	<tr>
		<td><a href="<?php echo PATH?>/admin/faq.php?task=editor&amp;edit=<?=$faqEntry->faqId?>"><?php echo returnIcon('pencil')?></a></td>
		<td><?php echo $faqEntry->faqQuestion?></td>
		<td><?php echo $faqEntry->faqAnswer?></td>
		<td><?php echo $faqEntry->faqPublic?></td>
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