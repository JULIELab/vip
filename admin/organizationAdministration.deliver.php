<?php
require '../__functions.php';
require '../__connect.php';

if(!is_numeric($_GET['id']))
	unset($_GET['id']);

$editingStates = array (
	'pending',
	'filled',
	'checked',
	'unclear'
);

switch($_GET['deliver']){
	case 'showOrganizations':
?>

<?php
		$sql = (string) "WHERE `organization_id` > 0 AND";

		if($_GET['name'] == 'no')
			$sql .= "`organization_name` = ''";
		else
			$sql .= "`organization_name` != ''";

		if(in_array($_GET['editing_status'], array('pending', 'filled', 'unclear', 'checked')))
			$sql .= " AND `editing_status` = '".$_GET['editing_status']."'";

		$organizationList = mysql_query("SELECT * FROM `organizations` ".$sql." ORDER BY `organization_name`, `organization_abbreviation`");

		if(mysql_num_rows($organizationList) > 0){
?>

<div class="notice"><strong>Editing status</strong>: <?php echo $_GET['editing_status']?><br /><strong>Name</strong>: <?php echo $_GET['name']?></div>
<table class="dataContainer">
	<tr>
		<th style="width: 20%"></th>
		<th style="width: 45%">Name</th>
		<th style="width: 15%">Nation</th>
		<th style="width: 10%">Status</th>
		<th style="width: 10%"> </th>
	</tr>
<?php
			$organization = new stdClass();
			while($organization = mysql_fetch_object($organizationList)){
				if($organization->editing_status == 'unclear')
					$organization->editing_status = returnIcon('help').'<em>'.$organization->editing_status.'</em>';

				if($organization->editing_status == 'pending')
					$organization->editing_status = returnIcon('hourglass').'<em>'.$organization->editing_status.'</em>';

				if($organization->editing_status == 'checked')
					$organization->editing_status = returnIcon('tick').'<em>'.$organization->editing_status.'</em>';
?>

	<tr>
		<td style="text-align: center">
			<a href="javascript:;" onclick="getOrganizationEditForm(<?php echo $organization->organization_id?>)"><?php echo returnIcon('pencil')?><em>Edit</em></a>
			<a href="javascript:;" onclick="shrinkOrganizationConfirm(<?php echo $organization->organization_id?>)"><?php echo returnIcon('arrow-down')?><em>Shrink</em></a>
			<a href="javascript:;" onclick="mergeOrganizationConfirm(<?php echo $organization->organization_id?>)"><?php echo returnIcon('arrow-join')?><em>Merge</em></a>
		</td>
		<td><?php echo htmlspecialchars($organization->organization_name)?> (<?php echo htmlspecialchars($organization->organization_abbreviation)?>)</td>
		<td><?php echo getFlag($organization->organization_nation, '../')?> <em><?php echo getNation($organization->organization_nation)?></em></td>
		<td><?php echo $organization->editing_status?></td>
		<td><a href="javascript:;" onclick="deleteOrganizationConfirm(<?php echo $organization->organization_id?>)"><?php echo returnIcon('delete')?><em>Delete</em></a></td>
	</tr>
<?php
			}
?>

</table>
<?php
		}else{
?>

<h3>No result</h3>
<div>There are no organizations that match your query!</div>
<?php
		}
	break;

	case 'editOrganization':
		$_GET['id'] = (int) $_GET['id'];

		if(!empty($_GET['name']) and !empty($_GET['nation'])){
			mysql_query("UPDATE `organizations` SET
	`organization_name` = '".mysql_real_escape_string(stripslashes(trim($_GET['name'])))."',
	`organization_abbreviation` = '".mysql_real_escape_string(stripslashes(mb_strtoupper(trim($_GET['abbreviation']))))."',
	`organization_nation` = '".mysql_real_escape_string(stripslashes(trim($_GET['nation'])))."',
	`organization_county` = '".mysql_real_escape_string(stripslashes(trim($_GET['county'])))."',
	`organization_city` = '".mysql_real_escape_string(stripslashes(trim($_GET['city'])))."',
	`organization_website` = '".mysql_real_escape_string(stripslashes(trim($_GET['website'])))."',
	`editing_status` = '".mysql_real_escape_string(stripslashes(trim($_GET['editing_status'])))."'
WHERE
	`organization_id` = '".$_GET['id']."'
LIMIT 1");
		}

		$organization = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = '".$_GET['id']."'");
		$organization = mysql_fetch_object($organization);

		$str = (string) '';
		$str .= '<strong class="success">Your changes have been saved!</strong>';
		$str .= '<p>';
		if(!empty($organization->organization_website))
			$str .= '<a href="'.$organization->organization_website.'">'.returnIcon('world').'</a> ';
		$str .= '<strong>'.htmlspecialchars($organization->organization_name).'</strong>';
		if(!empty($organization->organization_city))
			$str .= ', '.htmlspecialchars($organization->organization_city);
		$str .= ', '.getFlag($organization->organization_nation, '../').'<em>'.getNation($organization->organization_nation).'</em>';
		$str .= '</p>';

		echo dialogCreate('organizationEditingStatus_'.$_GET['id'], 'Organization editing status', $str);
	break;

	case 'getEditForm':
		$str = (string) '';
		$str .= 'Something went wrong!';
		if(is_numeric($_GET['id'])){
			$_GET['id'] = ((int) $_GET['id']);
			$organization = mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = ".$_GET['id']);

			if(mysql_num_rows($organization)){
				$organization = mysql_fetch_object($organization);

				$str = '<table>
	<tr>
		<td colspan="2">
			<label for="organizationName_'.$_GET['id'].'" class="block">Name*</label>
			<input type="text" id="organizationName_'.$_GET['id'].'" name="organizationName_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($organization->organization_name).'" />
		</td>
		<td style="width: 30%">
			<label for="organizationAbbreviation_'.$_GET['id'].'" class="block">Abbreviation</label>
			<input type="text" id="organizationAbbreviation_'.$_GET['id'].'" name="organizationAbbreviation_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($organization->organization_abbreviation).'" />
		</td>
	</tr>';
				$str .= '<tr>
		<td style="width: 40%">
			<label for="organizationNation_'.$_GET['id'].'" class="block">Nation*</label>
			<input type="text" id="organizationNation_'.$_GET['id'].'" name="organizationNation_'.$_GET['id'].'" size="3" value="'.htmlspecialchars($organization->organization_nation).'" onkeyup="checkOrganizationsNation(\''.$_GET['id'].'\', this.value)" />
			<span id="organizationFlagContainer_'.$_GET['id'].'"></span>
		</td>
		<td style="width: 30%">
			<label for="organizationCounty_'.$_GET['id'].'" class="block">County</label>
			<input type="text" id="organizationCounty_'.$_GET['id'].'" name="organizationCounty_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($organization->organization_county).'" />
		</td>
		<td style="width: 30%">
			<label for="organizationCity_'.$_GET['id'].'" class="block">City</label>
			<input type="text" id="organizationCity_'.$_GET['id'].'" name="organizationCity_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($organization->organization_city).'" />
		</td>
	</tr>';
				$str .= '<tr>
		<td colspan="2">
			<label for="organizationWebsite_'.$_GET['id'].'" class="block">Website</label>
			<input type="text" id="organizationWebsite_'.$_GET['id'].'" name="organizationWebsite_'.$_GET['id'].'" style="width: 100%" value="'.htmlspecialchars($organization->organization_website).'" />
		</td>
		<td>
			<label for="organizationEditingStatus_'.$_GET['id'].'" class="block">Editing status</label>
			<select id="organizationEditingStatus_'.$_GET['id'].'" name="organizationEditingStatus_'.$_GET['id'].'" style="width: 100%">';

				foreach($editingStates as $state){
					$str .= '<option value="'.$state.'"';
					if($organization->editing_status == $state)
						$str .= ' selected="selected"';
					$str .= '>'.$state.'</option>';
				}

				$str .= '</select>
		</td>
	</tr>
</table>';

			}
		}

		dialogCreate('organizationEditForm_'.$_GET['id'], 'Edit an organization', $str);
	break;

	case 'checkOrganizationsNation':
		if(strlen($_GET['abbreviation']) == 3){
			$nation = mysql_query("SELECT * FROM `nations` WHERE `nationAbbreviation` = '".mysql_real_escape_string(stripslashes($_GET['abbreviation']))."'");

			if(mysql_num_rows($nation) == 1)
				echo '<em>'.getFlag($_GET['abbreviation'], '../').' '.getNation($_GET['abbreviation']).'</em>';
			else echo '<span class="failure">Wrong!</span>';
		}else echo 'Too short!';
	break;
}

require '../__close.php';
?>
