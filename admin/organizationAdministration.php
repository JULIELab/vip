<?php
require '../__functions.php';
require ROOT_PATH.'_header.php';
require ROOT_PATH.'__connect.php';

?>

<h2>List of organizations</h2>
<h3>Options</h3>
<div id="showOrganizationsForm">
	<div style="width: 20%; float: left;">
		<label for="showOrganizationsWithName" class="block">Name filled</label>
		<select id="showOrganizationsWithName" name="showOrganizationsWithName" style="width: 90%">
			<option value="yes">yes</option>
			<option value="no">no</option>
		</select>
	</div>

	<div style="width: 30%; float: left;">
		<label for="showOrganizationsWithEditingStatus" class="block">Editing status</label>
		<select id="showOrganizationsWithEditingStatus" name="showOrganizationsWithEditingStatus" style="width: 90%">
			<option value="filled">filled</option>
			<option value="checked">checked</option>
			<option value="pending">pending</option>
			<option value="unclear">unclear</option>
		</select>
	</div>

	<div style="width: 30%; float: left; padding-top: 1em"><button id="showOrganizations" onclick="showOrganizations()">Get result!</button></div>

	<br style="clear: both;" />
</div>

<div class="helptext"><strong>This page shows organizations in several listing options.</strong></div>

<h3>Result</h3>
<ul id="resultList"></ul>
<div id="resultContainer"></div>

<script type="text/javascript" src="organizationAdministration.js"></script>
<script type="text/javascript">/* <![CDATA[ */ $('#showOrganizations').button(); showOrganizations(); /* ]]> */</script>
<?php

require ROOT_PATH.'__close.php';
require ROOT_PATH.'_footer.php';
?>
