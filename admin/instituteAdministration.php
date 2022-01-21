<?php
/**
 * Base file for institute administration tasks.
 *
 * Contains the base structure for institute administration tasks.
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package administration
 * @subpackage instituteAdministration
 * @version 1.0
 */

require '../__functions.php';
$jsFiles[] = '../../admin/instituteAdministration.js';
require ROOT_PATH.'_header.php';
require ROOT_PATH.'__connect.php';
?>

<h2>List of institutions</h2>
<h3>Options</h3>
<div id="showInstitutesForm">
	<div style="float: left; width: 20%">
		<label for="showInstitutesWithEditingStatus" class="block">Editing status</label>
		<select id="showInstitutesWithEditingStatus" name="showInstitutesWithEditingStatus" style="width: 90%">
			<option value="all">all</option>
			<option value="filled">filled</option>
			<option value="checked">checked</option>
			<option value="pending">pending</option>
			<option value="unclear" selected="selected">unclear</option>
		</select>
	</div>

	<div style="float: left; width: 40%">
		<label for="showInstitutesFromOrganization" class="block">Organization</label>
		<select id="showInstitutesFromOrganization" name="showInstitutesFromOrganization" style="width: 90%">
			<option value="0">all</option>
<?php
$organizationList = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` != 0 ORDER BY `organization_name`");
$organization = new stdClass();
$abbreviations = (string) '';
while($organization = mysql_fetch_object($organizationList)){
	if(empty($organization->organization_abbreviation))
		$organization->organization_abbreviation = $organization->organization_id;

	echo '<option value="'.$organization->organization_id.'">'.htmlspecialchars($organization->organization_name).' ['.htmlspecialchars($organization->organization_abbreviation).']</option>';
}
?>

		</select>
	</div>

	<div style="float: left; padding-top: 1em; width: 10;">
		<button id="showInstitutes" onclick="showInstitutes()">Get list!</button>
	</div>

	<div id="showInstitutes_allConfirm" style="display: none; float: right; width: 25%; border: 1px solid #faa; background: #ffa; color: #000; padding: 0 0.5em; font-size: 0.8em;">
		<strong>You are requesting to show all institutions.</strong><br />
		<em><?php echo returnIcon('information')?> That can take a lot of time and slow down your computer.</em><br />
		<a href="javascript:;" onclick="showInstitutes(true)"><?php echo returnIcon('tick')?> Yes! Really show all institutions!</a><br />
		<a href="javascript:;" onclick="$('#showInstitutes_allConfirm').hide('highlight')"><?php echo returnIcon('cross')?> No, thank you.</a>
	</div>
</div>
<br style="clear: both;" />
<div class="helptext">
	<strong>This page shows institutes in several listing options.</strong><br /><br />
	You can limit the result by editing status and associated organization of the institutions.<br />
	You then have the following options for actions on the data: <br /><br />
	<strong><?php echo returnIcon('pencil');?> Edit</strong> the data of an institution.<br />
	<strong><?php echo returnIcon('arrow-right');?> Move</strong> an institution from one organization to another one.<br />
	<strong><?php echo returnIcon('arrow-up');?> Grow</strong> an institution to be an organization.<br />
	<strong><?php echo returnIcon('arrow-join');?> Merge</strong> an institution with another institution.<br /><br />
	<strong><?php echo returnIcon('delete');?> Delete</strong> an institution.
</div>

<h3>Results</h3>
<ul id="resultList"></ul>
<div id="resultContainer"></div>

<script type="text/javascript">/* <![CDATA[ */ $('#showInstitutes').button(); showInstitutes(); /* ]]> */</script>
<?php

require ROOT_PATH.'__close.php';
require ROOT_PATH.'_footer.php';