<?php
/**
 * Main file for annotation tasks.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package annotation
 * @version 1.0
 */

require '../__functions.php';
require '../_header.php';
require '../__connect.php';

function deleteYetWrittenData ($proceeding, $allocations) {

	// Delete yet written data of the proceeding...
	mysql_query("DELETE FROM `proceedings` WHERE `proceeding_id` = ".$proceeding);
	if(mysql_affected_rows() == 1)
		echo '<div class="success">Written data of the proceeding was deleted!</div>';
	else
		echo '<div class="failure">Written data of the proceeding could not be deleted. See MySQL-Error for details: '.mysql_error().'</div>';

	// Delete yet written allocations...
	foreach($allocations as $allocation){
		mysql_query("DELETE FROM `allocations` WHERE `id` = ".$allocation);

		if(mysql_affected_rows() == 1)
			echo '<div class="success">Written allocation '.$allocation.' was deleted!</div>';
		else
			echo '<div class="failure">Written data of the allocation '.$allocation.' could not be deleted. See MySQL-Error for details: '.mysql_error().'</div>';
	}
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	// If form was posted ...

	// Set cookies that save specific inserted data...
	setcookie('enterYear', $_POST['year']);
	setcookie('enterConference', $_POST['conference']);
	setcookie('enterAnthology', $_POST['anthology']);

	// Initialize several variables...
	$inputErrors = array();
	$authors = array();
	$organizations = array();
	$institutes = array();
	$allocations = array();





	////////// CREATE PROCEEDING

	if(!empty($_POST['articlenumber']) and !empty($_POST['conference']) and !empty($_POST['year']) and ($_POST['authorAmount_0'] > 0 or $_POST['organizationAmount'] > 0)){
		mysql_query("INSERT INTO `proceedings` (
	`articlenumber`,
	`conference`,
	`year`,
	`url`,
	`bibtex`
) VALUES (
	'".mysql_real_escape_string(stripslashes($_POST['articlenumber']))."',
	'".mysql_real_escape_string(stripslashes($_POST['conference']))."',
	".mysql_real_escape_string(stripslashes($_POST['year'])).",
	'".mysql_real_escape_string(stripslashes($_POST['url']))."',
	'".mysql_real_escape_string(stripslashes($_POST['bibtex']))."'
)");
		$proceeding = mysql_insert_id();

	}else
		$inputErrors[] = 'You did not enter complete data for the proceeding!';






	////////// SAVE UNASSIGNED AUTHORS

	if($_POST['authorAmount_0'] > 0 and count($inputErrors) == 0){
		for($i = 1; $i <= $_POST['authorAmount_0']; $i++){

			if($_POST['author'][0][$i]['nation'] == 'NAT'){
				$inputErrors[] = 'You did not enter a correct nations abbreviation for unassigned author no. '.$i.'!';
				break;
			}

			if(isset($_POST['author'][0][$i]['id']) and is_numeric($_POST['author'][0][$i]['id'])){
				if(mysql_num_rows(mysql_query("SELECT * FROM `authors` WHERE `author_id` = ".((int) $_POST['author'][0][$i]['id']))) == 1){
					mysql_query("INSERT INTO `allocations` (
	`proceeding`,
	`author`,
	`nation`
) VALUES (
	'".$proceeding."',
	'".((int) $_POST['author'][0][$i]['id'])."',
	'".mysql_real_escape_string(stripslashes($_POST['author'][0][$i]['nation']))."'
)");
					$allocations[] = mysql_insert_id();
				}else
					$inputErrors[] = 'We could not find your unassigned author no. '.$i.'. He is not in the database.';
			}else
				$inputErrors[] = 'You did not enter complete data for unassigned author no. '.$i.'!';
		}
	}





	////////// SAVE organizationS, THEIR INSTITUTS AND THEIR ASSIGNED AUTHORS

	if($_POST['organizationAmount'] > 0 and count($inputErrors) == 0){
		for($i = 1; $i <= $_POST['organizationAmount']; $i++){

			if(isset($_POST['organization'][$i]['id']) and is_numeric($_POST['organization'][$i]['id'])){
				$res = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $_POST['organization'][$i]['id']));

				if(mysql_num_rows($res) == 1){
					$dummy = mysql_fetch_object($res);

					$organizations[$i] = array (
						'id' => ((int) $_POST['organization'][$i]['id']),
						'nation' => $dummy->organization_nation
					);
				}else
					$inputErrors[] = 'We could not find your organization no. '.$i.'. It is not in the database.';
			}else
				$inputErrors[] = 'You did not enter complete data for organization no. '.$i.'!';


			if(isset($_POST['institute'][$i]['id']) and is_numeric($_POST['institute'][$i]['id'])){
				$res = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_POST['institute'][$i]['id']));

				if(mysql_num_rows($res) == 1){
					$dummy = mysql_fetch_object($res);

					$institutes[$i] = array (
						'id' => ((int) $_POST['institute'][$i]['id']),
						'nation' => $dummy->institute_nation
					);
				}else
					$inputErrors[] = 'We could not find your institute no. '.$i.'. It is not in the database.';
			}else
				$inputErrors[] = 'You did not enter complete data for institute no. '.$i.'!';

			for($i2 = 1; $i2 <= $_POST['authorAmount_'.$i]; $i2++){
				if(isset($_POST['author'][$i][$i2]['id']) and is_numeric($_POST['author'][$i][$i2]['id'])){
					if(mysql_num_rows(mysql_query("SELECT * FROM `authors` WHERE `author_id` = ".((int) $_POST['author'][$i][$i2]['id']))) == 1){
						$authors[$i][$i2] = array (
							'id' => ((int) $_POST['author'][$i][$i2]['id']),
						);
					}else
						$inputErrors[] = 'We could not find your assigned author no. '.$i.'! He is not in the database.';
				}else
					$inputErrors[] = 'You didn not enter complete data for assigned author no. '.$i.'!';
			}

			for($i2 = 1; $i2 <= count($authors[$i]); $i2++){
				$nation = $organizations[$i]['nation'];
				if(!empty($institutes[$i]['nation']))
					$nation = $institutes[$i]['nation'];

				mysql_query("INSERT INTO `allocations` (
	`proceeding`,
	`organization`,
	`institute`,
	`author`,
	`nation`
) VALUES (
	'".$proceeding."',
	'".$organizations[$i]['id']."',
	'".$institutes[$i]['id']."',
	'".$authors[$i][$i2]['id']."',
	'".$nation."'
)");
				$allocations[] = mysql_insert_id();
			}
		}
	}

	if(count($inputErrors) > 0){
		echo '<h2 class="failure">Errors occured!</h1>';
		echo '<ul>';
		foreach($inputErrors as $error)
			echo '<li>'.$error.'</li>'.nl;
		echo '</ul>';

		deleteYetWrittenData($proceeding, $allocations);
	}elseif(count($allocations) > 0){
?>

<form action="<?php echo PATH?>/enter/delete.php" method="post">
			<h2>Written data</h2>
			<div class="frame">
				<h3>General data of the proceeding</h3>
<?php
		$result = mysql_query("SELECT * FROM `proceedings` WHERE `proceeding_id` = ".$proceeding);
		$proceeding = mysql_fetch_object($result);
?>

				<strong>Year</strong>: <?=$proceeding->year?>, <strong>Conference</strong>: <?=$proceeding->conference?>, <strong>Articlenumber</strong>: <?=$proceeding->articlenumber?>, <strong>URL to the PDF</strong>: <a href="<?=$proceeding->url?>"><?=$proceeding->url?></a>
				<input type="hidden" id="proceeding" name="proceeding" value="<?=$proceeding->proceeding_id?>" />

				<h3>Allocations</h3>
				<table>
					<tr>
						<th>Author</th>
						<th>organization</th>
						<th>Institute</th>
						<th>Nation</th>
					</tr>
<?php
		foreach($allocations as $allocation){
			static $i = 0;
			$result = mysql_query("SELECT allo.*, auth.*
FROM `allocations` allo, `authors` auth
WHERE allo.`author` = auth.`author_id` AND allo.`id` = ".$allocation." LIMIT 1");
			$allo = mysql_fetch_object($result);

			$result = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".$allo->organization);
			if(mysql_num_rows($result) == 1)
				$orga = mysql_fetch_object($result);
			if(empty($orga->organization_abbreviation))
				$orga->organization_abbreviation = 'none';


			$result = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".$allo->institute);
			if(mysql_num_rows($result) == 1)
				$inst = mysql_fetch_object($result);
			if(empty($inst->institute_abbreviation))
				$inst->institute_abbreviation = 'none';
?>

					<tr>
						<td>
							<input type="hidden" id="allocation_<?=++$i?>" name="allocation[<?=$i?>]" value="<?=$allocation?>" />
							<?=$allo->name?>, <?=$allo->firstname?>
						</td>
						<td><?=$orga->organization_name?> (<?=$orga->organization_abbreviation?>)</td>
						<td><?=$inst->institute_name?> (<?=$inst->institute_abbreviation?>)</td>
						<td><?=$allo->nation?></td>
					</tr>
<?php
		}
?>

				</table>
				<input type="hidden" id="allocations" name="allocations" value="<?=$i?>" />
			</div>

			<div class="submit">
				<input type="submit" value="delete" style="background: #f00; color: #fff; font-weight: bold" />
				<a href="<?php echo PATH?>/enter/"><strong>[Enter next article]</strong></a>
			</div>
		</form>
<?php
	}
}else{
?>

<strong class="notificationBlock">
	<?php echo returnIcon('information');?> Please keep in mind to use the <a href="<?php echo PATH?>/enter/codebook.php"><?php echo returnIcon('report');?> codebook</a> if you are not sure about annotating.
</strong>
<form action="<?php echo PATH?>/enter/" method="post" onsubmit="return checkSubmittingStatus();" id="addArticle">
	<h2>Add an article</h2>
	<div class="frame">
		<div class="subNav">
			<a href="javascript:;" onclick="getOrganizationCreateForm()">[Create organization]</a>
			<a href="javascript:;" onclick="getInstituteCreateForm()">[Create institute]</a>
			<a href="javascript:;" onclick="getAuthorCreateForm();">[Create author]</a>
			<a href="javascript:;" onclick="getNationList();">[Get list of nations]</a>
		</div>

		<h3>I. General data of the article</h3>
		<table>
			<tr class="helptext">
				<td colspan="4">
					<strong>1. Check BibTex-information</strong><br />
					You can parse one complete anthology website and extract all bibtex links. Or you can simply give on specific bibtex via its URL.<br />
					If you have any BibTex-information, you can put the URL to the BibTex-information in the appropriate field, to parse necessary information.<br />
					I'll fill all the fields i can with the information i find. You can of course reject any information and overwrite it.
				</td>
			</tr>

			<tr class="semanticSeparation">
				<td colspan="3">
					<label for="anthology" class="block">URL of anthology</label>
					<input type="text" id="anthology" name="anthology" style="width: 100%" value="<?php echo $_COOKIE['enterAnthology']?>" />
				</td>
				<td id="checkAnthology">
					<a href="javascript:;" onclick="checkAnthology()"><?php echo returnIcon('tick');?> Check anthology!</a>
				</td>
			</tr>

			<tr class="semanticSeparation">
				<td colspan="3">
					<label for="bibtex" class="block">URL of BibTex information</label>
					<input type="text" id="bibtex" name="bibtex" style="width: 100%" />
				</td>
				<td id="checkBibTex">
					<a href="javascript:;" onclick="checkBibTex()"><?php echo returnIcon('tick');?> Check BibTex-information!</a>
				</td>
			</tr>

			<tr class="helptext">
				<td colspan="4">
					<strong>2. Fill general information</strong><br />
					If you have parsed BibTex-information please double check the information that has been filled with this information.<br />
					If you didn't parse a BibTex file you'll have to enter all information necessary on your own from the respectice PDF.
				</td>
			</tr>

			<tr class="semanticSeparation">
				<td style="width: 10%">
					<label for="year" class="block">Year</label>
					<select id="year" name="year" style="width: 100%; text-align: right" onmouseup="checkArticlesExistence(false); checkConference()" onkeyup="delayRequest('checkArticlesExistence', Array(false)); delayRequest('checkConference')">
<?php
	$selectedYear = $_COOKIE['enterYear'];
	if(!is_numeric($selectedYear) or $selectedYear < 1990)
		$selectedYear = date('Y');

	for($i = 1990; $i <= date('Y'); $i++){
		echo '<option value="'.$i.'"';
		if($selectedYear == $i)
			echo 'selected="selected"';
		echo '>'.$i.'&nbsp;</option>'.nl;
	}
?>

					</select>
				</td>
				<td style="width: 30%">
					<label for="conference" class="block">Conference</label>
					<input type="text" id="conference" name="conference" style="width: 100%" value="<?php echo $_COOKIE['enterConference']?>" onmouseup="checkArticlesExistence(false); checkConference()" onkeyup="delayRequest('checkArticlesExistence', Array(false)); delayRequest('checkConference')" />
					<div id="checkConference" style="padding: 0.5em"></div>
				</td>
				<td style="width: 10%">
					<label for="articlenumber" class="block">Article</label>
					<input type="text" id="articlenumber" name="articlenumber" style="width: 100%" onmouseup="checkArticlesExistence(false)" onkeyup="delayRequest('checkArticlesExistence', Array(false));" />
				</td>
				<td style="width: 50%">
					<label for="url" class="block">URL to the PDF</label>
					<input type="text" id="url" name="url" style="width: 100%" onmouseup="checkPDF()" onkeyup="delayRequest('checkPDF')" />
					<div id="checkPDF" style="padding: 0.5em"></div>
				</td>
			</tr>

			<tr class="semanticSeparation">
				<td colspan="4" id="checkArticlesExistence" class="submit"><a href="javascript:;" onclick="checkArticlesExistence(true)"><?php echo returnIcon('tick');?> Check wether article is yet in database.</a></td>
			</tr>
		</table>

		<h3>II. Article's authors</h3>
		<table>
			<tr class="helptext">
				<td>
					<strong>3. Enter organized authors</strong> (common cases)<br />
					Count the amount of institutions and organizations wherein authors are organized.
					If an organization with more than one institution is involved you have to count this organization twice.
					Afterwards enter the authors for the organizations.
				</td>
			</tr>

			<tr class="semanticSeparation">
				<td>
					<label for="organizationAmount" class="block">Amount of organizations</label>
					<input type="text" id="organizationAmount" name="organizationAmount" size="4" value="0" readonly="readonly" />
					<a href="javascript:;" onmouseup="$('#organizationAmount').val(parseInt($('#organizationAmount').val()) + 1)"><?php echo returnIcon('add');?> Increase</a>
					<a href="javascript:;" onmouseup="if($('#organizationAmount').val() > 0) $('#organizationAmount').val(parseInt($('#organizationAmount').val()) - 1)"><?php echo returnIcon('delete');?> Decrase</a>
					<a href="javascript:;" onmouseup="getBlocks($('#organizationAmount').val())"><?php echo returnIcon('tick');?> Get fields for this amount of organizations.</a>
				</td>
			</tr>
		</table>
		<div id="blockContainer"></div>

		<table>
			<tr  class="helptext">
				<td colspan="2">
					<strong>4. Enter private authors</strong> (very uncommon)<br />
					This is the section for the private authors, that did not write their article for an organization. Count them and enter the amount in the input field.
				</td>
			</tr>

			<tr class="semanticSeparation">
				<td style="width: 30%">
					<input type="text" id="authorAmount_0" name="authorAmount_0" onkeyup="delayRequest('getFieldsForAuthors', Array(0, this.value));" size="3" maxlength="3" value="0" />
					<label for="authorAmount_0">unassigned author</label>(s)
				</td>
				<td id="authorFieldsContainer_0" style="width: 70%"></td>
			</tr>
		</table>
	</div>
</form>

<div id="anthologiesExistence" class="contentContainer">
	<h3>Check existence of an anthology</h3>
	<div class="frame">
		<div class="helptext">
			<strong>In this section you can check if an anthology is in the database.</strong><br />
			You have to provide the URL of an anthology. Optionally you can provide the abbreviation of the expected conference to ensure that the database checking works correctly.
			You will be notified if a conference value gets overwritten.
		</div>

		<div style="float: left; width: 50%">
			<label for="checkAnthologiesExistenceURL" class="block">URL of anthology*</label>
			<input type="text" id="checkAnthologiesExistenceURL" name="checkAnthologiesExistenceURL" style="width: 100%" />
		</div>
		<div style="float: left; padding: 0 0 0 1em; width: 40%">
			<label for="checkAnthologiesExistenceConference" class="block">Expected Conference</label>
			<a href="javascript:;" onclick="checkAnthologiesExistence()" style="float: right"><?php echo returnIcon('tick');?> Check for bibtex files!</a>
			<input type="text" id="checkAnthologiesExistenceConference" name="checkAnthologiesExistenceConference" style="width: 50%" />
		</div>

		<div id="checkAnthologiesExistence" style="clear: both; padding: 2em 0"></div>
	</div>
</div>

<script type="text/javascript" src="enter.js"></script>
<?php
}

require '../__close.php';
require '../_footer.php';
?>
