<?php
/**
 * Delivery-file for institute administration tasks.
 *
 * Contains all the responses to ajax calls to manipulate institution related data.
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package administration
 * @subpackage instituteAdministration
 * @version 1.0
 */

/**
 * Standard inclusions.
 */
require '../__functions.php';
require ROOT_PATH.'__connect.php';

/**
 * Input validation.
 */
$_GET['id'] = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

/**
 * Standard definition for drop-down lists.
 */
$editingStates = array (
	'pending',
	'filled',
	'checked',
	'unclear'
);

$title = (string) 'Error';
$status = (string) 'false';
$text = (string) 'An error occured';

/**
 * Edit the data of an institution.
 * Shows as jGrowl notification.
 */
if($_GET['deliver'] == 'editInstitute'){
	if(!empty($_GET['id']) and !empty($_GET['name']) and !empty($_GET['nation'])){
		mysql_query("UPDATE `institutes` SET
	`institute_name` = '".myEscape(trim($_GET['name']))."',
	`institute_abbreviation` = '".myEscape(mb_strtoupper(trim($_GET['abbreviation'])))."',
	`institute_nation` = '".myEscape(trim(mb_strtoupper($_GET['nation']))."',
	`institute_county` = '".myEscape(trim($_GET['county']))."',
	`institute_city` = '".myEscape($_GET['city']))."',
	`institute_website` = '".myEscape(trim($_GET['website']))."',
	`editing_status` = '".myEscape(trim($_GET['editing_status']))."'
WHERE
	`institute_id` = '".$_GET['id']."'
LIMIT 1");

		$title = 'Success';
		$status = true;
		$text = 'Your changes have been saved!';
	}else
		$text = 'You did not fill all of the required fields.';

	echo json_encode (
		array (
			'title' => $title,
			'status' => $status,
			'text' => $text
		)
	);
}

/**
 * Get the edit form for an institute.
 * Shows as jQuery-UI dialog.
 */
if($_GET['deliver'] == 'getInstituteEditForm'){
	$_GET['id'] = (int) $_GET['id'];
	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".$_GET['id']);
	if(mysql_num_rows($institute)){
		$institute = mysql_fetch_object($institute);
		$organization = mysql_fetch_object(mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $institute->institute_organization)));

		/**
		 * Overwrite not existing institution data with data from the organization.
		 */
		if(empty($institute->institute_county))
			$institute->institute_county = $organization->organization_county;
		if(empty($institute->institute_city))
			$institute->institute_city = $organization->organization_city;
		if(empty($institute->institute_website))
			$institute->institute_website = $organization->organization_website;
		if(empty($institute->institute_nation))
			$institute->institute_nation = $organization->organization_nation;

		$text = '<h4>Editable data</h4>
<table>
<tr>
<td colspan="2">
	<label for="instituteName_'.$_GET['id'].'" class="block">Name*</label>
	<input type="text" id="instituteName_'.$_GET['id'].'" name="instituteName_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($institute->institute_name).'" />
</td>
<td style="width: 30%">
	<label for="instituteAbbreviation_'.$_GET['id'].'" class="block">Abbreviation</label>
	<input type="text" id="instituteAbbreviation_'.$_GET['id'].'" name="instituteAbbreviation_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($institute->institute_abbreviation).'" />
</td>
</tr>';
		$text .= '<tr>
<td style="width: 40%">
	<label for="instituteNation_'.$_GET['id'].'" class="block">Nation*</label>
	<input type="text" id="instituteNation_'.$_GET['id'].'" name="instituteNation_'.$_GET['id'].'" size="3" value="'.htmlspecialchars($institute->institute_nation).'" onkeyup="checkInstitutesNation(\''.$_GET['id'].'\', this.value)" />
	<span id="instituteFlagContainer_'.$_GET['id'].'"></span>
</td>
<td style="width: 30%">
	<label for="instituteCounty_'.$_GET['id'].'" class="block">County</label>
	<input type="text" id="instituteCounty_'.$_GET['id'].'" name="instituteCounty_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($institute->institute_county).'" />
</td>
<td style="width: 30%">
	<label for="instituteCity_'.$_GET['id'].'" class="block">City</label>
	<input type="text" id="instituteCity_'.$_GET['id'].'" name="instituteCity_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($institute->institute_city).'" />
</td>
</tr>';
		$text .= '<tr>
<td colspan="2">
	<label for="instituteWebsite_'.$_GET['id'].'" class="block">Website</label>
	<input type="text" id="instituteWebsite_'.$_GET['id'].'" name="instituteWebsite_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($institute->institute_website).'" />
</td>
<td>
	<label for="instituteEditingStatus_'.$_GET['id'].'" class="block">Editing status</label>
	<select id="instituteEditingStatus_'.$_GET['id'].'" name="instituteEditingStatus_'.$_GET['id'].'" style="width: 100%">';

		foreach($editingStates as $state){
			$text .= '<option value="'.$state.'"';
			if($institute->editing_status == $state)
				$text .= ' selected="selected"';
			$text .= '>'.$state.'</option>';
		}

		$text .= '</select>
</td>
</tr>
<tr>
</table>
<h4 style="display: block;" class="semanticSeparation">Organization associated</h4>
'.printOrganizationData($organization)
.'<br /><br />'.returnIcon('information').' To change the associated organization you have to click <strong>'.returnIcon('arrow-right').' Move</strong> instead!';
	}

	dialogCreate('getInstituteEditForm_'.$_GET['id'], 'Institution editor', $text);
}

/**
 * If confirmed the institution can be grown.
 * Shows as jGrowl notification.
 */
if($_GET['deliver'] == 'growInstitute'){
	/**
	 * To grow an institution several database updates are needed.
	 * 1. Transfer the data from the institution to a new organization.
	 * 2. Rewrite the allocations from the old institute to the new organization.
	 * 3. Delete the institution.
	 */

	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");
	if(mysql_num_rows($institute) == 1){
		$institute = mysql_fetch_object($institute);

		mysql_query("INSERT INTO `organizations` (
	`organization_name`,
	`organization_abbreviation`,
	`organization_nation`,
	`organization_county`,
	`organization_city`,
	`organization_website`,
	`editing_status`
) VALUES (
	'".myEscape($institute->institute_name)."',
	'".myEscape($institute->institute_abbreviation)."',
	'".myEscape($institute->institute_nation)."',
	'".myEscape($institute->institute_county)."',
	'".myEscape($institute->institute_city)."',
	'".myEscape($institute->institute_website)."',
	'".myEscape($institute->editing_status)."'
)");
		$organization = mysql_insert_id();

		$allocations = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `institute` = ".((int) $_GET['id'])));
		mysql_query("UPDATE `allocations` SET `organization` = ".((int) $organization).", `institute` = 0 WHERE `institute` = ".((int) $_GET['id'])." LIMIT ".$allocations);
		mysql_query("DELETE FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id']));

		$title = 'Success';
		$status = 'true';
		$text = 'Institute was moved!';
	}

	echo json_encode(
		array(
			'title' => $title,
			'text' => $text
		)
	);
}

/**
 * Get the form to confirm that one wants to grow an institution.
 * Shows as jQuery-UI dialog.
 */
if($_GET['deliver'] == 'growInstituteConfirm'){
	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");

	$text = (string) 'An error occurred';
	if(mysql_num_rows($institute)){
		$institute = mysql_fetch_object($institute);

		$text = '<h4>Institution to be grown</h4>'.printInstituteData($institute);
		$text .= '<h4 class="semanticSeparation">Organization associated</h4>'.printOrganizationData($institute->institute_organization);
		$text .= '<br /><br /><div class="semanticSeparation"><strong>'.returnIcon('error').' Do you really want to grow this institution to be an organization?</strong></div>';
	}

	dialogCreate('growInstituteConfirm_'.((int) $_GET['id']), 'Grow institution', $text);
}

/**
 * If confirmed the institution can be moved from one organization to another.
 * Shows as jGrowl notification.
 */
if($_GET['deliver'] == 'moveInstitute'){
	/**
	 * To move an institution several database updates are needed.
	 * 1. Set the institutions organization id to the id of the new organization.
	 * 2. Update all allocations from new old organizations id to the new one.
	 */

	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id']));
	if(mysql_num_rows($institute) == 1 and !empty($_GET['organization'])){
		$institute = mysql_fetch_object($institute);

		$organization = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $_GET['organization']));
		if(mysql_num_rows($organization) == 1){
			$organization = mysql_fetch_object($organization);
			$allocations = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `institute` = ".((int) $institute->institute_id)));

			/* 1. */ mysql_query("UPDATE `institutes` SET `institute_organization` = ".((int) $organization->organization_id)." WHERE `institute_id` = ".((int) $institute->institute_id)." LIMIT 1");
			/* 2. */ mysql_query("UPDATE `allocations` SET `organization` = ".((int) $organization->organization_id)." WHERE `institute` = ".((int) $institute->institute_id)." LIMIT ".((int) $allocations));

			$title = 'Success';
			$text = 'Institute has been moved.<br />'.$allocations.' allocations were affected.';
			$status = 'true';
		}
	}

	echo json_encode (
		array (
			'title' => $title,
			'text' => $text,
			'status' => $status
		)
	);
}

/**
 * Get the form to confirm that one wants to move an institution.
 * Shows as jQuery-UI dialog.
 */
if($_GET['deliver'] == 'moveInstituteConfirm'){
	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id']));

	if(mysql_num_rows($institute)){
		$institute = mysql_fetch_object($institute);
		$organization = mysql_fetch_object(mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $institute->institute_organization)));

		$text = '<h4>Institution to be moved</h4>'.printInstituteData($institute);
		$text .= '<h4 class="semanticSeparation">Organization associated</h4>'.printOrganizationData($institute->institute_organization);

		$text .= '<h4 class="semanticSeparation">Select new organization associated</h4>';
		$text .= '<label for="instituteSearch_'.((int) $institute->institute_id).'" class="block">Searchphrase</label>';
		$text .= '<input type="text" id="instituteSearch_'.((int) $institute->institute_id).'" name="instituteSearch_'.((int) $institute->institute_id).'" onmouseup="searchOrganizations('.((int) $institute->institute_id).', this.value)" onkeyup="delayRequest(\'searchOrganizations\', Array('.((int) $institute->institute_id).', this.value))" style="width: 100%" />';
		$text .= '<div id="instituteSearchResult_'.((int) $institute->institute_id).'" style="margin: 1em 0 0 0"></div>';
	}

	dialogCreate('moveInstituteConfirm_'.((int) $_GET['id']), 'Move institution', $text);
}

/**
 * Search organizations to select one an institution shall be moved to.
 * Shows as HTML-snippet.
 */
if($_GET['deliver'] == 'searchOrganizations'){
	$organizations = mysql_query("SELECT * FROM (SELECT *, MATCH(`organization_name`, `organization_abbreviation`, `organization_nation`, `organization_county`, `organization_city`, `organization_website`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['query']))."') AS `relevancy` FROM  `organizations` ORDER BY `relevancy` DESC, `organization_name`, `organization_abbreviation`) AS `rated_organizations` WHERE `relevancy` > 0");
	$amount = mysql_num_rows($organizations);

	if($amount > 0){
?>

<label for="instituteOrganization_<?php echo $_GET['id']?>" class="block">Organization <?php if($amount > 1) echo '('.$amount.' results)';?></label>
<select id="instituteOrganization_<?php echo $_GET['id']?>" name="instituteOrganization_<?php echo $_GET['id']?>" style="width: 100%">';
<?php
		if($amount > 1){
?>

<option value="0">please choose</option>
<?php
		}
		$organization = new stdClass;
		while($organization = mysql_fetch_object($organizations)){
?>

<option value="<?php echo $organization->organization_id?>"><?php echo $organization->organization_name?> (<?php echo $organization->organization_abbreviation?>), <?php echo $organization->organization_nation?> [<?php echo round($organization->relevancy, 2)?>]</option>
<?php
		}
?>

</select>
<?php
	}else{
?>

<div class="notificationBlock"><strong>We are sorry!</strong> We have no organizations in the database that match your searchphrase.</div>
<?php
	}
}

/**
 * If confirmed two institutes can be merged.
 * Shows as jGrowl notification.
 */
if($_GET['deliver'] == 'mergeInstitute'){
	/**
	 * To merge two institutes several database updates are needed.
	 * 1. Update all allocations from the old institute to the new one.
	 * 2. Delete the merged institution.
	 */

	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");
	$target = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['target'])." LIMIT 1");
	if(mysql_num_rows($institute) == 1 and mysql_num_rows($target) == 1){
		$institute = mysql_fetch_object($institute);
		$target = mysql_fetch_object($target);

		$allocations = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `institute` = ".((int) $_GET['id'])));
		if($institute->institute_organization == $target->institute_organization){
			mysql_query("UPDATE `allocations` SET `institute` = ".((int) $_GET['target'])." WHERE `institute` = ".((int) $_GET['id'])." LIMIT ".((int) $allocations));
			mysql_query("DELETE FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");
			$title = 'Success';
			$text = 'Merging was successful.<br />'.$allocations.' allocations were affected!';
			$status = 'true';
		}
	}

	echo json_encode (
		array (
			'title' => $title,
			'text' => $text,
			'status' => $status
		)
	);
}

/**
 * Get the form to confirm that one really wants to merge two institutes.
 * Shows as jQuery-UI dialog.
 */
if($_GET['deliver'] == 'mergeInstituteConfirm'){
	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");
	if(mysql_num_rows($institute)){
		$institute = mysql_fetch_object($institute);

		$text = '<h4>Institution to be merged</h4>';
		$text .= printInstituteData($institute);
		$text .= '<br /><br /><strong>'.returnIcon('error').' This institution will be deleted when you trigger the merge.</strong>';

		$text .= '<h4 class="semanticSeparation">Organization associated</h4>';
		$text .= printOrganizationData($institute->institute_organization);

		$siblings = mysql_query("SELECT * FROM `institutes` WHERE `institute_organization` = ".((int) $institute->institute_organization)." AND `institute_id` != ".((int) $institute->institute_id)." ORDER BY `institute_name`");
		if(mysql_num_rows($siblings) > 0){
			$text .= '<h4 class="semanticSeparation">Institute to merge with</h4>';
			$text .= '<label for="mergeInstituteConfirm_secondId_'.((int) $_GET['id']).'" class="block">Siblings ('.mysql_num_rows($siblings).')</label>';
			$text .= '<select id="mergeInstituteConfirm_secondId_'.((int) $_GET['id']).'" name="mergeInstituteConfirm_secondId_'.((int) $_GET['id']).'" style="width: 100%">';
			$text .= '<option value="0">please choose</option>';
			while($sibling = mysql_fetch_object($siblings))
				$text .= '<option value="'.$sibling->institute_id.'">'.$sibling->institute_name.' ('.$sibling->institute_abbreviation.') ['.$sibling->institute_id.']</option>';
			$text .= '</select>';
		}else
			$text .= '<div class="notificationBlock">The associated organization has no further institutes.</div>';

		$text .= '<br /><br /><em>For safety reasons you can only merge institutions that are associated to the same organization!</em>';
	}

	dialogCreate('mergeInstituteConfirm_'.((int) $_GET['id']), 'Merge institution', $text);
}

if($_GET['deliver'] == 'deleteInstitute'){
	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");

	if(mysql_num_rows($institute) == 1){
		$institute = mysql_fetch_object($institute);
		$allocations = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `institute` = ".((int) $institute->institute_id)));
		if($allocations == 0){
			mysql_query("DELETE FROM `institutes` WHERE `institute_id` = ".((int) $institute->institute_id)." LIMIT 1");
			$title = 'Success';
			$status = 'true';
			$text = 'Institute <strong>'.$institute->institute_name.'</strong> ('.$institute->institute_abbreviation.') was deleted!';
		}else
			$text = 'Institute has allocations. It can not be deleted!';
	}

	exit(json_encode(array(
		'title' => $title,
		'status' => $status,
		'text' => $text
	)));
}

/**
 * Get the form to confirm that one wants to delete an institute.
 * Shows as jQuery-UI dialog.
 */
if($_GET['deliver'] == 'deleteInstituteConfirm'){
	$institute = mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = ".((int) $_GET['id'])." LIMIT 1");

	$text = (string) 'An error occured!';

	if(mysql_num_rows($institute) == 1){
		$institute = mysql_fetch_object($institute);
		$text = '<h4>Institute that you want do delete</h4>';
		$text .= printInstituteData($institute);
		$text .= '<h4>Associated organization</h4>';
		$text .= printOrganizationData($institute->institute_organization);

		$allocations = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `institute` = ".((int) $institute->institute_id)));
		$text .= '<h4>Allocations</h4><p>This institute has <strong><span id="deleteInstituteConfirm_allocations_'.$_GET['id'].'">'.$allocations. ' allocation(s)</strong>.</p>';
		if($allocations > 0)
			$text .= '<div class="error">The institute can not be deleted because it still has allocations. Another option is to merge this institute with another institute.</div>'
				.'<p>In this process the institute will be deleted too and the allocations are moved to another institute.</p>'
				.'<p>You can get to the merge form by clicking the <strong>Merge</strong> button below.</p>';
		else
			$text .= '<div class="notice">Are you sure that you want to delete the institute?</div>';
	}

	dialogCreate('deleteInstituteConfirm_'.$_GET['id'], 'Delete institute', $text);
}

/**
 * Get a list of institutions.
 */
if($_GET['deliver'] == 'showInstitutes'){
	$sql = (string) "WHERE `institute_id` > 0";

	if(in_array($_GET['editing_status'], array('pending', 'filled', 'unclear', 'checked')))
		$sql .= " AND `editing_status` = '".$_GET['editing_status']."'";
	else
		$_GET['editing_status'] = 'all';

	$organization = null;
	if(is_numeric($_GET['organization']) and $_GET['organization'] != 0){
		$organization = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $_GET['organization'])." LIMIT 1");
		if(mysql_num_rows($organization) == 1){
			$organization = mysql_fetch_object($organization);
			$sql .= " AND `institute_organization` = ".((int) $_GET['organization']);
		}
	}

	if(!is_object($organization)){
		$organization = new stdClass();
		$organization->organization_name = 'all';
	}

	$instituteList = mysql_query("SELECT * FROM `institutes` ".$sql." ORDER BY `institute_name`, `institute_abbreviation`");

	if(mysql_num_rows($instituteList) > 0){
?>

<div class="notice"><strong>Editing status</strong>: <?php echo $_GET['editing_status']?><br /><strong>Organization</strong>: <?php echo $organization->organization_name?></div>
<table class="dataContainer">
<tr>
	<th style="width: 25%"></th>
	<th style="width: 40%">Name</th>
	<th style="width: 15%">Nation</th>
	<th style="width: 10%">Status</th>
	<th style="width: 10%"> </th>
</tr>
<?php
		$institute = new stdClass();
		while($institute = mysql_fetch_object($instituteList)){
			$image = (string) 'help';
			if($institute->editing_status == 'pending')
				$image = 'warning';
			if($institute->editing_status == 'checked')
				$image = 'tick';
			if($institute->editing_status == 'filled')
				$image = 'textfield-rename';

			$institute->editing_status = returnIcon($image).'<em>'.$institute->editing_status.'</em>';
?>

<tr>
	<td style="text-align: center">
		<a href="javascript:;" onclick="getInstituteEditForm(<?php echo $institute->institute_id?>)"><?php echo returnIcon('pencil');?><em>Edit</em></a>
		<a href="javascript:;" onclick="moveInstituteConfirm(<?php echo $institute->institute_id?>)"><?php echo returnIcon('arrow-right');?><em>Move</em></a>
		<a href="javascript:;" onclick="growInstituteConfirm(<?php echo $institute->institute_id?>)"><?php echo returnIcon('arrow-up');?><em>Grow</em></a>
		<a href="javascript:;" onclick="mergeInstituteConfirm(<?php echo $institute->institute_id?>)"><?php echo returnIcon('arrow-join');?><em>Merge</em></a>
	</td>
	<td>
		<a href="javascript:;" onclick="$('#organizationData_<?php echo $institute->institute_id?>').toggle()" style="font-size: 0.8em; float: right;">[Toggle]</a>
		<strong><?php echo htmlspecialchars($institute->institute_name)?></strong> (<?php echo htmlspecialchars($institute->institute_abbreviation)?>)
		<div id="organizationData_<?php echo $institute->institute_id?>" style="display: none; padding-top: 0.5em"><?php echo printOrganizationData($institute->institute_organization)?></div>
	</td>
	<td><?php echo getFlag($institute->institute_nation, '../')?><em><?php echo getNation($institute->institute_nation)?></em></td>
	<td style="text-align: center"><?php echo $institute->editing_status?></td>
	<td style="text-align: center"><a href="javascript:;" onclick="deleteInstituteConfirm(<?php echo $institute->institute_id?>)"><?php echo returnIcon('delete');?><em>Delete</em></a></td>
</tr>
<?php
		}
?>

</table>
<?php
		if(!empty($organization->organization_id))
			echo '<div class="notice">The following image shows the organigram of the selected organization. The sizes of the institutes are relative to the amount of published articles.</div><img src="instituteAdministration.deliver.php?deliver=getOrganizationChart&amp;id='.$organization->organization_id.'" alt="Organigram" style="display: block; margin: 20px auto" />';
	}else
		echo '<div class="notice">There are no institutes that match your query!</div>';
}

if($_GET['deliver'] == 'getOrganizationChart' and !empty($_GET['id'])){
	$organization = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".((int) $_GET['id'])." LIMIT 1");

	if(mysql_num_rows($organization) == 1){
		$organization = mysql_fetch_object($organization);
		if(empty($organization->name))
			$organization->name = $organization->abbreviation;
		$articles = array();
		$articles[0] = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `organization` = ".((int) $organization->organization_id)." GROUP BY `proceeding`"));

		$institutes = mysql_query("SELECT * FROM `institutes` WHERE `institute_organization` = ".((int) $organization->organization_id));

		require ROOT_PATH.'_lib/pChart/class/pData.class.php';
		require ROOT_PATH.'_lib/pChart/class/pDraw.class.php';
		require ROOT_PATH.'_lib/pChart/class/pSpring.class.php';
		require ROOT_PATH.'_lib/pChart/class/pImage.class.php';

		$myPicture = new pImage(900, 600);
		$myPicture->setFontProperties(array('FontName'=>ROOT_PATH.'_resources/fonts/delicious.otf','FontSize'=>12));
		$myPicture->drawFilledRectangle(0,0,900,600,array('R'=>200,'G'=>200,'B'=>200));
		$myPicture->drawFilledRectangle(0,0,900,30,array('R'=>0,'G'=>0,'B'=>0));
		$myPicture->drawText(10,24,'Organigram: '.$organization->organization_name,array('R'=>255,'G'=>255,'B'=>255));
		$myPicture->setFontProperties(array('FontName'=>ROOT_PATH.'_resources/fonts/delicious.otf','FontSize'=>8));

		$myPicture->setGraphArea(20,50,880,580);
		$myPicture->setShadow(TRUE,array('X'=>5,'Y'=>5,'R'=>0,'G'=>0,'B'=>0,'Alpha'=>20));

		$SpringChart = new pSpring();

		$SpringChart->addNode(0,array('Name'=>$organization->organization_name,'Shape'=>NODE_SHAPE_SQUARE,'FreeZone'=>200,'Size'=>50,'NodeType'=>NODE_TYPE_CENTRAL));
		$i = (int) 1;
		while($institute = mysql_fetch_object($institutes)){
			if(empty($institute->institute_name))
				$institute->institute_name = $institute->institute_abbreviation;

			$articles[$i] = mysql_num_rows(mysql_query("SELECT * FROM `allocations` WHERE `institute` = ".((int) $institute->institute_id)." GROUP BY `proceeding`"));
			$size = 50 / $articles[0] * $articles[$i];
			$SpringChart->addNode($i,array('Name'=>$institute->institute_name.' ('.round($articles[$i]/$articles[0]*100,1).'%)','FreeZone'=>($size*2),'Size'=>$size,'Connections'=>'0'));
			$SpringChart->linkProperties(0,$i,array('R'=>0,'G'=>0,'B'=>0,'Ticks'=>0));

			$i++;
		}

		/* Define the nodes color */
		$SpringChart->setNodesColor(0,array("R"=>215,"G"=>163,"B"=>121,"BorderR"=>166,"BorderG"=>115,"BorderB"=>74));
		$SpringChart->setNodesColor(array(1,2),array("R"=>150,"G"=>215,"B"=>121));

		/* Customize some relations */
		$SpringChart->linkProperties(0,1,array("R"=>255,"G"=>0,"B"=>0,"Ticks"=>2));
		$SpringChart->linkProperties(0,2,array("R"=>255,"G"=>0,"B"=>0,"Ticks"=>2));

		/* Render the spring chart */
		$Result = $SpringChart->drawSpring($myPicture);

		/* Render the picture */
		$myPicture->stroke();
	}
}

/**
 * Check th given nation abbreviation against the database content.
 */
if($_GET['deliver'] == 'checkInstitutesNation'){
	if(strlen($_GET['abbreviation']) == 3){
		$nation = mysql_query("SELECT * FROM `nations` WHERE `nationAbbreviation` = '".myEscape($_GET['abbreviation'])."'");

		if(mysql_num_rows($nation) == 1)
			echo '<em>'.getFlag($_GET['abbreviation'], '../').' '.getNation($_GET['abbreviation']).'</em>';
		else echo '<span class="failure">Wrong!</span>';
	}else echo 'Too short!';
}

require ROOT_PATH.'__close.php';