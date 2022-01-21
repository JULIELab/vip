<?php
require '../__functions.php';
require '../__connect.php';
if(!empty($_GET['check'])){
	$inconsistency = array();
	$inconsistency['Article missing'] = "SELECT * FROM `allocations` WHERE `proceeding` NOT IN (SELECT `proceeding_id` FROM `proceedings`)";
	$inconsistency['Article without allocation'] = "SELECT * FROM `proceedings` WHERE `proceeding_id` NOT IN (SELECT `proceeding` FROM `allocations`)";

	$inconsistency['Author missing'] = "SELECT * FROM `allocations` WHERE `author` NOT IN (SELECT `author_id` FROM `authors`)";
	$inconsistency['Author without allocation'] = "SELECT * FROM `authors` WHERE `author_id` NOT IN (SELECT `author` FROM `allocations`)";

	$inconsistency['Organization missing'] = "SELECT * FROM `allocations` WHERE `organization` NOT IN (SELECT `organization_id` FROM `organizations`)";
	$inconsistency['Organization without allocation'] = "SELECT * FROM `organizations` WHERE `organization_id` NOT IN (SELECT `organization` FROM `allocations`)";

	$inconsistency['Institute missing'] = "SELECT * FROM `allocations` WHERE `institute` NOT IN (SELECT `institute_id` FROM `institutes`)";
	$inconsistency['Institute without allocation'] = "SELECT * FROM `institutes` WHERE `institute_id` NOT IN (SELECT `institute` FROM `allocations`)";
	$inconsistency['Institute without organization'] = "SELECT * FROM `institutes` WHERE `institute_organization` NOT IN (SELECT `organization_id` FROM `organizations`)";

	if(array_key_exists($_GET['check'], $inconsistency)){
		$result = mysql_query($inconsistency[$_GET['check']]);

		if(mysql_num_rows($result) > 0){
			echo '<h3 class="failure">'.$_GET['check'].' '.returnIcon('cross').'</h3><p> '.mysql_num_rows($result).' occurences</p>';

			$i = (int) 0;
			$row = new stdClass();
			while($row = mysql_fetch_object($result)){
				echo '<h4>Insonsistency #'.++$i.'</h4>';

				$delete = (int) 0;

				if($_GET['check'] == 'Article missing' and $_GET['del'] == 1){
					mysql_query("DELETE FROM `allocations` WHERE `id` = ".((int) $row->id)." LIMIT 1");
					$delete = mysql_affected_rows();
				}
				if($_GET['check'] == 'Article without allocation' and $_GET['del'] == 1){
					mysql_query("DELETE FROM `proceedings` WHERE `proceeding_id` = ".((int) $row->proceeding_id)." LIMIT 1");
					$delete = mysql_affected_rows();
				}
				if($_GET['check'] == 'Author without allocation' and $_GET['del'] == 1){
					mysql_query("DELETE FROM `authors` WHERE `author_id` = ".((int) $row->author_id)." LIMIT 1");
					$delete = mysql_affected_rows();
				}
				if($_GET['check'] == 'Institute without allocation' and $_GET['del'] == 1){
					mysql_query("DELETE FROM `institutes` WHERE `institute_id` = ".((int) $row->institute_id)." LIMIT 1");
					$delete = mysql_affected_rows();
				}
				if($_GET['check'] == 'Organization without allocation' and $_GET['del'] == 1){
					mysql_query("DELETE FROM `organizations` WHERE `organization_id` = ".((int) $row->organization_id)." LIMIT 1");
					$delete = mysql_affected_rows();
				}

				if($delete == 0)
					echo '<strong class="failure">'.returnIcon('cross').' Was not deleted!</strong><br/>';
				else
					echo '<strong class="success">'.returnIcon('tick').' Was deleted!</strong><br/>';


				echo '<pre>';
				foreach($row as $key => $value)
					echo '<em>'.$key.'</em> = <strong>'.$value.'</strong><br />';
				echo '</pre>';
			}
		}else
			echo '<h3 class="success">'.$_GET['check'].' '.returnIcon('tick').'</h3><p>0 occurences</p>';
	}
}
require '../__close.php';