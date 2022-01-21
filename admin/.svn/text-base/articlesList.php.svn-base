<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';

$conferencesResult = mysql_query("SELECT * FROM `proceedings` GROUP BY `conference` ORDER BY `conference`");
$conferences = array();
$conference = new stdClass();
while($conference = mysql_fetch_object($conferencesResult))
	$conferences[] = $conference->conference;

$yearsResult = mysql_query("SELECT * FROM `proceedings` GROUP BY `year` ORDER BY `year`");
$years = array();
$year = new stdClass();
while($year = mysql_fetch_object($yearsResult))
	$years[] = $year->year;
?>

<div class="subNav">
<div style="float: right; padding: 1em 0 0 1em">
	<input id="submit" type="submit" value="Get list" tabindex="5" />
</div>
<div style="float: right; padding: 0 0 0 1em">
	<label for="conference" class="block">Conference</label>
	<select id="conference" name="conference" tabindex="4">
		<option value="">all</option>
<?php
foreach($conferences as $conference){
	echo '<option value="'.$conference.'">'.$conference.'</option>';
}
?>
	</select>
</div>
<div style="float: right; padding: 0 0 0 1em">
	<label for="year" class="block">Year</label>
	<select id="year" name="year" tabindex="3">
		<option value="">all</option>
<?php
foreach($years as $year){
	echo '<option value="'.$year.'">'.$year.'</option>';
}
?>
	</select>
</div>
<div style="float: right; padding: 0 0 0 1em">
	<label for="offset" class="block">Offset</label>
	<input type="text" id="offset" name="offset" value="0" style="width: 50px" tabindex="2" />
</div>

<div style="float: right; padding: 0 0 0 1em">
	<label for="limit" class="block">Limit</label>
	<input type="text" id="limit" name="limit" value="100" style="width: 50px" tabindex="1" />
</div>
</div>

<h2>List of articles</h2>

<div id="articleContainer"></div>
<script type="text/javascript">
	/* <![CDATA[ */
function deleteArticleConfirm (id) {
	$.ajax({
		url: 'articlesList.deliver.php',
		cache: false,
		data: {
			deliver: 'deleteConfirm',
			id: id
		},
		dataType: 'html',
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#deleteArticleConfirm_'+id).dialog({
				modal: true,
				buttons: ({
					'Delete!': function () {
						deleteArticle(id);
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				}),
				close: function () {
					$(this).remove();
				},
				width: 800
			});
		}
	})
}

function deleteArticle (id) {
	$.ajax({
		url: 'articlesList.deliver.php',
		cache: false,
		data: {
			deliver: 'delete',
			id: id
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text, {
				header: json.title,
				life: 10000
			});

			if(json.state == 'success')
				getArticles();
		}
	});
}

function getArticles () {
	$.ajax({
		url: 'articlesList.deliver.php',
		cache: false,
		data: {
			deliver: 'list',
			conference: $('#conference').val(),
			year: $('#year').val(),
			offset: $('#offset').val(),
			limit: $('#limit').val()
		},
		success: function (html) {
			$('#articleContainer').html(html);
			$('#articleContainer table').show('slow');
		}
	})
}

$('#conference').change(function () {getArticles()});
$('#year').change(function () {getArticles()});
$('#offset').change(function () {getArticles()});
$('#limit').change(function () {getArticles()});
$('#submit').button().click(function () {getArticles()});

getArticles();
	/* ]]> */
</script>
<?php
require '../__close.php';
require '../_footer.php';