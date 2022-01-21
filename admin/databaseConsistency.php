<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';
?>

<h2>Database consistency</h2>

<div class="helptext">
	<strong>You can check cross table references on this page.</strong>
</div>

<div id="checkDatabaseConsistencyContainer" style="border-top: 2px solid #000; margin: 1em 0 0 0; padding: 1em 0 0 0;">
	<div style="float: right">
		<input type="submit" id="checkDatabaseTrigger" onclick="checkDatabaseConsistency()" value="Run consistency checks" />

		and
		<select id="deleteInconsistencies" name="deleteInconsistencies">
			<option value="0">don't delete</option>
			<option value="1">delete</option>
		</select>
		inconsistencies!
	</div>
	<br style="clear: both" />
</div>

<script type="text/javascript">
	/* <![CDATA[ */
var consistencyChecks = Array('Article missing', 'Article without allocation', 'Author missing', 'Author without allocation', 'Organization missing', 'Organization without allocation', 'Institute missing', 'Institute without allocation', 'Institute without organization');

function checkDatabaseConsistencyKey (key, value, del) {
	$('#checkDatabaseConsistencyContainer').append('<div id="check_'+key+'"></div>');
	setLoading('#check_'+key, '../', value);

	$.ajax({
		url: 'databaseConsistency.deliver.php',
		cache: false,
		data: {
			check: value,
			del: del
		},
		success: function (html) {
			$('#check_'+key).html(html);
		}
	})
}

function checkDatabaseConsistency () {
	var del = $('#deleteInconsistencies').val();

	$('#checkDatabaseConsistencyContainer').html('');
	$.each(consistencyChecks, function (key, value) {
		checkDatabaseConsistencyKey(key, value, del);
	})
}

$('#checkDatabaseTrigger').button();
	/* ]]> */
</script>
<?php

require '../__close.php';
require '../_footer.php';