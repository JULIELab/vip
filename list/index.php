<?php
/**
 * Base file for listing of database content.
 *
 * Contains the base structure for listing of database content.
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package list
 * @version 1.0
 */

require '../__functions.php';
require '../_header.php';
require '../__connect.php';

/**
 * Delete outdated (one day old) cached queries.
 */
mysql_query("DELETE FROM `__cachedQueries` WHERE `queryCurrency` < (UNIX_TIMESTAMP() - 86400)");

$possibleCachedQuery = mysql_query("SELECT * FROM `__cachedQueries` WHERE `queryHash` = '".md5($_SERVER['QUERY_STRING'])."'");
if(mysql_num_rows($possibleCachedQuery) == 0 or $_GET['bypassCaching'] == 1 or empty($_SERVER['QUERY_STRING'])){
	$points = array();
	$factors = array();
	$rankingSubject = (string) '';
	$rankingMethod = (string) '';

	switch($_GET['rankingMethod']){
		case 'simple':				$rankingMethod = 'Simple'; break;
		case 'individual':		$rankingMethod = 'Individual'; break;
		case 'accumulation':		$rankingMethod = 'Accumulation over a year'; break;
	}
	$userChoicesForQuery = (string) "";

	$yearsResult = mysql_query("SELECT `year`, COUNT(*) AS `count` FROM `proceedings` GROUP BY `year` ORDER BY `year`");
	$years = array();
	$row = new stdClass();
	while($row = mysql_fetch_object($yearsResult))
		$years[] = $row->year;

	$conferencesResult = mysql_query("SELECT `conference`, COUNT(*) AS `count` FROM `proceedings` GROUP BY `conference` ORDER BY `conference`");
	$conferencesCount = mysql_num_rows($conferencesResult);
	$conferences = array();
	$row = new stdClass();
	while($row = mysql_fetch_object($conferencesResult))
		$conferences[] = $row->conference;

	$authorCountsResult = mysql_query("SELECT `proceeding`, COUNT(*) AS `count` FROM `allocations` GROUP BY `proceeding`");
	$authorCounts = array();
	$row = new stdClass();
	while($row = mysql_fetch_object($authorCountsResult))
		$authorCounts[$row->proceeding] = $row->count;

	$year = (int) 0;
	$conference = (string) '';
	foreach($years as $year)
		foreach($conferences as $conference)
			$factors[$year][$conference] = (string) '';

	$yearsConferencesArticlesAmounts = array();
	$yearsArticlesAmounts = array();
	if($_GET['rankingMethod'] == 'accumulation'){
		// get amounts of articles per conference per year
		$yearsConferencesArticles = mysql_query("SELECT `year`, `conference`, COUNT(*) AS `count` FROM `proceedings` GROUP BY `year`, `conference` ORDER BY `year`, `conference`");
		$row = new stdClass();
		while($row = mysql_fetch_object($yearsConferencesArticles))
			$yearsConferencesArticlesAmounts[$row->year][$row->conference] = $row->count;

		// get amounts of articles per year
		$yearsArticles = mysql_query("SELECT `year`, COUNT(*) AS `count` FROM `proceedings` GROUP BY `year` ORDER BY `year`");
		$row = new stdClass();
		while($row = mysql_fetch_object($yearsArticles))
			$yearsArticlesAmounts[$row->year] = $row->count;
	}

	if(!empty($_GET['rankingMethod']) and !empty($_GET['rankingSubject'])){
		if(is_numeric($_GET['rankingYearsStart']) and $_GET['rankingYearsStart'] > reset($years))
			$userChoicesForQuery .= " AND `year` >= ".((int) $_GET['rankingYearsStart']);
		else
			unset($_GET['rankingYearsStart']);

		if(is_numeric($_GET['rankingYearsEnd']) and $_GET['rankingYearsEnd'] >= $_GET['rankingYearsStart'] and $_GET['rankingYearsEnd'] < end($years))
			$userChoicesForQuery .= " AND `year` <= ".((int) $_GET['rankingYearsEnd']);
		else
			unset($_GET['rankingYearsEnd']);

		if(count($_GET['conferences']) != $conferencesCount and count($_GET['conferences']) != 0){
			$i = (int) 0;
			$_userChoicesForQuery = (string) " AND (";

			$conference = array();
			while($conference = each($_GET['conferences'])){
				if(in_array($conference['key'], $conferences)){
					++$i;
					if($i > 1)
						$_userChoicesForQuery .= " OR ";

					$_userChoicesForQuery .= "`conference` = '".mysql_real_escape_string(stripslashes($conference['key']))."'";
				}
			}
			$_userChoicesForQuery .= ")";

			if($i > 0)
				$userChoicesForQuery .= $_userChoicesForQuery;
		}

		$dummy = (string) '';
		foreach($factors as $articlesYear => $articlesConferences){
			foreach($articlesConferences as $articlesConference => $dummy){
				switch($_GET['rankingMethod']){
					default:
					case 'simple':
						// set all factors to 1
						$factors[$articlesYear][$articlesConference] = 1;
					break;
					case 'individual':
						// set all factors to the users choice
						$factors[$articlesYear][$articlesConference] = (int) $_GET['individual'][$articlesConference];
					break;
					case 'accumulation':
						// calculate all the factors
						if(!empty($yearsConferencesArticlesAmounts[$articlesYear][$articlesConference]) and !empty($yearsArticlesAmounts[$articlesYear]))
							$factors[$articlesYear][$articlesConference] = 1 / ($yearsConferencesArticlesAmounts[$articlesYear][$articlesConference] / $yearsArticlesAmounts[$articlesYear]);
					break;
				}
			}
		}

		//normalize the factors
		$dummy = (int) 0;
		$complete = (int) 0;
		if($_GET['rankingMethod'] == 'accumulation'){
			foreach($factors as $year => $_conferences){
				$complete = array_sum($factors[$year]);
				foreach($_conferences as $conference => $dummy){
					if(!empty($dummy))
						$factors[$year][$conference] = round($factors[$year][$conference] / $complete, 3);
				}
			}
		}

		// get the needed articles
		$query = (string) "";

		$query = "SELECT proc.*, auth.*, allo.*, orga.*, inst.*, 1 AS `points` "
			."FROM `proceedings` proc, `authors` auth, `allocations` allo, `organizations` orga, `institutes` inst "
			."WHERE allo.`proceeding` = proc.`proceeding_id` AND allo.`author` = auth.`author_id` AND allo.`organization` = orga.`organization_id` AND allo.`institute` = inst.`institute_id` "
			.$userChoicesForQuery." "
			."ORDER BY `year` DESC";

		$proceedings = mysql_query($query);

		switch($_GET['rankingSubject']){
			case 'organizations':
				$rankingSubject = 'Organizations';

				$i = (int) 0;
				while($proceeding = mysql_fetch_object($proceedings)){
					if(!is_object($points[$proceeding->organization_id])){
						$points[$proceeding->organization_id] = new stdClass();
						$points[$proceeding->organization_id]->id = $proceeding->organization_id;

						if(empty($proceeding->organization_name))
							$proceeding->organization_name = $proceeding->organization_abbreviation;
						$proceeding->organization_name = htmlspecialchars($proceeding->organization_name);

						$points[$proceeding->organization_id]->name = getFlag($proceeding->organization_nation, '../').$proceeding->organization_name;
						if(!empty($proceeding->organization_website))
							$points[$proceeding->organization_id]->name .= ' <a href="'.$proceeding->organization_website.'">'.returnIcon('world').'</a>';
					}

					$thisPoints = $factors[$proceeding->year][$proceeding->conference] * ($proceeding->points / $authorCounts[$proceeding->proceeding_id]);
					$points[$proceeding->organization_id]->points += $thisPoints;
				}
			break;

			case 'nations':
				$rankingSubject = 'Nations';

				while($proceeding = mysql_fetch_object($proceedings)){
					if(!is_object($points[$proceeding->nation])){
						$points[$proceeding->nation] = new stdClass;
						$points[$proceeding->nation]->name = getFlag($proceeding->nation, '../').getNation($proceeding->nation);
					}

					$points[$proceeding->nation]->points += $factors[$proceeding->year][$proceeding->conference] * ($proceeding->points / $authorCounts[$proceeding->proceeding_id]);
				}
			break;

			case 'authors':
				$rankingSubject = (string) 'Authors';

				$i = (int) 0;
				while($proceeding = mysql_fetch_object($proceedings)){
					if(!is_object($points[$proceeding->author_id])){
						// if author isn't yet in the list, create a new entry for him
						$points[$proceeding->author_id] = new stdClass;
						$points[$proceeding->author_id]->further = array();
						$points[$proceeding->author_id]->id = $proceeding->author_id;
						$points[$proceeding->author_id]->csvName = $proceeding->firstname.' '.$proceeding->name;
						$points[$proceeding->author_id]->name = '<i>'.$proceeding->name.'</i>, '.$proceeding->firstname;
					}

					// count points
					$thisPoints = $factors[$proceeding->year][$proceeding->conference] * ($proceeding->points / $authorCounts[$proceeding->proceeding]);
					$points[$proceeding->author_id]->points += $thisPoints;
				}
			break;
		}

		usort($points, 'sortIt');
	}

	$_GET['page'] = (int) $_GET['page'];
	$_GET['perPage'] = (int) $_GET['perPage'];

	$perPage = 100;
	if(!empty($_GET['perPage']))
		$perPage = $_GET['perPage'];

	if(!is_numeric($_GET['page']))
		$_GET['page'] = 1;

	if(!empty($_GET['queryId']) and is_numeric($_GET['queryId'])){
		$position = searchIt($points, $_GET['queryId']);

		if($position !== FALSE){
			$perPage = 5;
			$_GET['page'] = floor($position / $perPage) + 1;
		}
	}

	$offset = ($_GET['page'] - 1) * $perPage;

	ob_start();

	$mainConferencesResult = mysql_query("SELECT `conference` FROM `proceedings` WHERE `conference` NOT LIKE '%-%' GROUP BY `conference` ORDER BY `conference`");
	$mainConferences = array();
	while($conference = mysql_fetch_object($mainConferencesResult))
		$mainConferences[] = $conference->conference;

	$jointConferencesResult = mysql_query("SELECT `conference` FROM `proceedings` WHERE `conference` LIKE '%-%' GROUP BY `conference` ORDER BY `conference`");
	$jointConferences = array();
	while($conference = mysql_fetch_object($jointConferencesResult))
		$jointConferences[] = $conference->conference;

	$conferences = array_merge($mainConferences, $jointConferences);
?>

<div class="subNav">Please refer to the <a href="<?php echo PATH?>/faq.php">FAQ section</a> for more information about this query form.</div>
<h2>Ranking</h2>
<p class="notificationBlock">
	<?php echo returnIcon('information');?> We currently have
	<strong><?php echo mysql_num_rows(mysql_query("SELECT * FROM `proceedings"))?> <a href="<?php echo PATH?>/list/accumulationTable.php">articles</a></strong> in the database that are spread over
	<strong><?php echo mysql_num_rows(mysql_query("SELECT * FROM `organizations"))?> organizations</strong> with
	<strong><?php echo mysql_num_rows(mysql_query("SELECT * FROM `institutes"))?> institutes</strong> and
	<strong><?php echo mysql_num_rows(mysql_query("SELECT * FROM `authors"))?> authors</strong>.
</p>
<form action="<?php echo PATH?>/list/#result" method="get">
	<h3>Please choose ...</h3>

	<div class="frame">
		<div style="display: inline; float: right; padding: 0 0 0 0.5em; width: 30%">
			<h4>... type of listing</h4>
			<div id="list" style="border: 1px solid #ddd; padding: 0.5em">
				<span style="float: right; width: 70%">
					<label for="perPage" class="block">Entries per page</label>
					<input type="text" id="perPage" name="perPage" value="<?php echo $perPage?>" size="2" style="width: 100%" />
				</span>

				<span style="float: left; width: 20%">
					<label for="page" class="block">Page</label>
<?php
	if(count($points) > 0){
?>

					<select id="page" name="page">
<?php
		for($i = 1; $i <= ceil(count($points) / $perPage); $i++){
?>

						<option value="<?=$i?>"<?php if($i == $_GET['page']) echo ' selected="selected"'; ?>><?=$i?></option>
<?php
		}
?>

					</select>
<?php
	}else{
?>

					<input type="text" id="page" name="page" value="1" size="4" style="width: 100%" />
<?php
	}
?>

				</span>
				<br style="clear: both" />
			</div>

			<div id="search" style="border: 1px solid #ddd; padding: 0.5em">
				<label for="q" class="block"><?php echo returnIcon('find');?> Enter query to search database</label>
				<input type="text" id="q" name="q" size="10" value="<?php echo $_GET['q']?>" style="width: 100%" onmouseup="searchEntry($('#q').val())" onkeyup="delayRequest('searchEntry', Array($('#q').val()))" />
				<span id="searchEntry"></span>
			</div>
		</div>

		<div style="display: inline; float: right; width: 25%">
			<h4>... conferences</h4>
			<div style="border: 1px solid #ddd; padding: 0.5em" id="checkConferences">
				<div style="display: inline; float: right; width: 50%">
					<label class="block">Joint</label>
<?php
	$i = (int) 0;
	while($conference = each($jointConferences)){
?>

					<br />
					<input type="checkbox" id="conference_<?=++$i?>" name="conferences[<?=$conference['value']?>]" value="1"<?php if($_GET['conferences'][$conference['value']] == 1) echo ' checked="checked"';?> />
					<label for="conference_<?=$i?>" style="font-weight: normal"><?=$conference['value']?></label>
<?php
	}
?>

				</div>

				<label class="block">Main</label>
<?php
	while($conference = each($mainConferences)){
?>

				<br />
				<input type="checkbox" id="conference_<?=++$i?>" name="conferences[<?=$conference['value']?>]" value="1"<?php if($_GET['conferences'][$conference['value']] == 1) echo ' checked="checked"';?> />
				<label for="conference_<?=$i?>"><?=$conference['value']?></label>
<?php
	}
?>

				<br /><br />
				<div style="text-align: center">
					<a href="javascript:;" onclick="checkConferences(true)">check</a> / <a href="javascript:;" onclick="checkConferences(false)">uncheck</a> all<br />
					<em>If no conference is selected,<br />all conference will be ranked.</em>
				</div>
			</div>
		</div>

		<div style="display: inline; float: left; width: 40%">
			<h4>... options</h4>
			<div style="border: 1px solid #ddd; padding: 0.5em">
				<div id="rankingSubject" style="text-align: center">
					<label for="rankingSubject" class="block">Subject - What to rank?</label>
<?php
$rankingSubjects = array(
	'authors' => 'Authors',
	'organizations' => 'Organizations',
	'nations' => 'Nations'
);

if(empty($_GET['rankingSubject']))
	$_GET['rankingSubject'] = 'authors';

foreach($rankingSubjects as $subjectIndex => $subjectTitle){
?>

					<label for="rankingSubject_<?php echo $subjectIndex?>"><?php echo $subjectTitle?></label>
					<input type="radio" id="rankingSubject_<?php echo $subjectIndex?>" name="rankingSubject" value="<?=$subjectIndex?>"<?php if($subjectIndex == $_GET['rankingSubject']) echo ' checked="checked"';?> />
<?php
}
?>
				</div>

				<div style="text-align: center">
					<label for="rankingMethod" class="block">Method - How to rank?</label>
					<select id="rankingMethod" name="rankingMethod" style="width: 80%">
						<option value="simple"<?php if($_GET['rankingMethod'] == 'simple') echo ' selected="selected"';?>>Simple</option>
						<option value="accumulation"<?php if($_GET['rankingMethod'] == 'accumulation') echo ' selected="selected"';?>>Accumulation over a year</option>
						<option value="individual"<?php if($_GET['rankingMethod'] == 'individual') echo ' selected="selected"';?>>Individual</option>

						<option value="experts" disabled="disabled">Experts judgement (not finished)</option>
						<option value="rejection" disabled="disabled">Rate of rejection (not finished)</option>
					</select>
				</div>
			</div>
<?php
	if(empty($_GET['rankingYearsStart']))
		$_GET['rankingYearsStart'] = reset($years);

	if(empty($_GET['rankingYearsEnd']))
		$_GET['rankingYearsEnd'] = end($years);
?>

			<div style="border: 1px solid #ddd; border-top: 0; padding: 0.5em">
				<label for="period" class="block">Period - Which years to rank?</label>
				<div id="period" style="margin: 1em"></div>
				<div style="text-align: center">
					<input id="rankingYearsEnd" name="rankingYearsEnd" readonly="readonly" value="<?php echo ((int) $_GET['rankingYearsEnd'])?>" style="display: none" />
					<input id="rankingYearsStart" name="rankingYearsStart" readonly="readonly" value="<?php echo ((int) $_GET['rankingYearsStart'])?>" style="display: none" />
					<div id="rankingYears" style="background: #eee; border: 1px solid #ccc; color: #000; margin: -0.5em auto 0; text-align: center; width: 50%">
						from <strong><?php echo ((int) $_GET['rankingYearsStart'])?></strong> to <strong><?php echo ((int) $_GET['rankingYearsEnd'])?></strong>
					</div>
				</div>
			</div>
		</div>

		<div id="individualFactors" style="clear: both; font-size: 0.8em; padding: 1em 0 0 0">
			<h4>... custom factors for conferences</h4>

			<div style="border: 1px solid #ddd; padding: 0.5em">
				<table class="formContainer">
					<tr>
<?php
	asort($conferences);

	$i = (int) 0;
	while($conference = each($conferences)){
		if($i % 8 == 0 and $i !== 0)
			echo '</tr><tr>';

		echo '<td style="width: '.round(100 / 8, 2).'%">';
		echo '<label for="individualConferenceFactor_'.++$i.'">'.$conference['value'].'</label>';
		echo '<input type="text" size="7" id="individualConferenceFactor_'.$i.'" name="individual['.$conference['value'].']" value="';

		if(empty($_GET['individual'][$conference['value']]))
			echo '1';
		else
			echo (string) ((int) $_GET['individual'][$conference['value']]);

		echo '" style="text-align: right; width: 100%" class="individualFactor" />';
		echo '</td>';
	}
?>

					</tr>
				</table>
			</div>
		</div>
		<br style="clear: both" />
	</div>

	<div class="submit">
		<div style="display: inline; float: left">
			<input type="checkbox" id="bypassCaching" name="bypassCaching" value="1" />
			<label for="bypassCaching"><?php echo returnIcon('tick');?> Bypass query caching?</label>
		</div>

		<input id="submit" type="submit" value="Send query" />
	</div>
</form>
<?php
	if(count($points) > 0){
?>

<div id="result" class="contentContainer">
<h3><?php echo returnIcon('star');?> Request summary</h3>
<div id="rankingLabel">
	<table>
		<tr>
			<td style="width: 20%"><strong>Subject</strong></td>
			<td style="width: 80%">
				<a href="javascript:;" onclick="$('#factors').toggle('slow')" style="float: right"><?php echo returnIcon('table');?> Factors</a>
				<em><?php echo $rankingSubject?></em>
			</td>
		</tr>
		<tr>
			<td><strong>Method</strong></td>
			<td><em><?php echo $rankingMethod?></em></td>
		</tr>

		<tr>
			<td><strong>Period</strong></td>
			<td>from <em><?php echo ((int) $_GET['rankingYearsStart'])?></em> to <em><?php echo ((int) $_GET['rankingYearsEnd'])?></em></td>
		</tr>


<?php
		if(!empty($_GET['queryId']) and is_numeric($_GET['queryId'])){
			echo '<tr><td><strong>Query</strong></td><td><em>';
			if($_GET['rankingSubject'] == 'authors')
				echo getAuthorsName($_GET['queryId']);
			else
				echo getOrganizationsName($_GET['queryId']);
			echo '</em></td></tr>';
		}else{
?>

		<tr>
			<td><strong>Results</strong></td>
			<td><em><?php echo ($offset + 1)?> - <?php if(($offset + $perPage) > count($points)) echo count($points); else echo ($offset + $perPage)?></em> of <?php echo count($points)?></td>
		</tr>
<?php
		}

		if(is_array($_GET['individual'])){
			echo '<tr><td><strong>Factors</strong></td><td>';
			ksort($_GET['individual']);

			$conference = (string) '';
			$factor = (float) 0;
			foreach($_GET['individual'] as $conference => $factor)
				echo '<em>'.$conference.':&nbsp;'.$factor.'</em> ';
			echo '</td></tr>';
		}
?>

	</table>

	<div id="factors" style="font-size: 0.8em;">
		<table class="dataContainer">
			<tr>
				<th> </th>
	<?php
			foreach($conferences as $conference)
				echo '<th><abbr title="'.$conference.'">'.mb_substr($conference, 0, 3).'</a></th>';
	?>

			</tr>
	<?php
			$i = (int) 0;
			foreach($factors as $_year => $_conferences){
				echo '<tr>';
				echo '<td style="font-weight: bold; text-align: center; width: '.round(100 / (count($conferences) + 1), 2).'%;">'.$_year.'</td>';
				$_conference = (string) '';
				foreach($_conferences as $_conference => $_factor)
					echo '<td style="text-align: center; width: '.round(100 / (count($conferences) + 1), 2).'%;">'.$_factor.'</td>';
				echo '</tr>';
			}
	?>

		</table>
	</div>
</div>

<h3>Result</h3>
<table id="resultTable" class="dataContainer">
	<tr>
		<th style="width: 10%">Position</th>
		<th style="width: 80%"> </th>
		<th style="width: 10%">Points</th>
	</tr>
<?php
		$i = $offset;
		$lastPoints = (int) 0;
		$lastRank = (int) 0;

		while($i < ($offset + $perPage) and is_object($points[$i])){
?>

	<tr id="result_<?php echo $i?>" <?php if(is_numeric($_GET['queryId']) and $points[$i]->id == $_GET['queryId']) echo ' style="background: #ffa; color: inherit"';?>>
		<td style="font-weight: bold; font-size: 2em; text-align: center">
<?php
			if($lastPoints != $points[$i]->points){
				echo ($i + 1);
				$lastRank = ($i + 1);
			}
?>

		</td>
		<td>
<?php
			if($_GET['rankingSubject'] == 'authors'){
?>

			<div style="float: right">
				Get
				<a href="javascript:;" onclick="getFurtherInformation(<?php echo $points[$i]->id?>, 'articles')" id="furtherInformationTrigger_<?php echo $points[$i]->id?>">articles</a>
				of author
			</div>
<?php
			}
			if($_GET['rankingSubject'] == 'organizations'){
?>

			<div style="float: right">
				Get
				<a href="javascript:;" onclick="getFurtherInformation(<?php echo $points[$i]->id?>, 'articles')" id="furtherInformationTrigger_<?php echo $points[$i]->id?>">articles</a>
				<a href="javascript:;" onclick="getFurtherInformation(<?php echo $points[$i]->id?>, 'institutes')" id="furtherInformationTrigger_<?php echo $points[$i]->id?>">institutes</a>
				of organization
			</div>
<?php
			}
?>

			<h4><?php echo $points[$i]->name?></h4>
			<div id="furtherInformation_<?php echo $points[$i]->id?>"></div>
		</td>
		<td style="font-size: 1.5em; text-align: center"><?php echo $points[$i]->points?></td>
	</tr>
<?php
			$lastPoints = $points[$i]->points;
			$i++;
		}
?>

	</table>
</div>
<?php
	}
?>

<script type="text/javascript">
	/* <![CDATA[ */
// functions
function getFurtherInformation (id, grouping) {
	setLoading('#furtherInformation_'+id, '../');

	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'further',
			id: id,
			rankingGrouping: grouping
		}),
		success: function (html) {
			$('#furtherInformation_'+id).html(html);
			$('#furtherInformationTable_'+id).show('fast');
		}
	})
}

function clearSearch () {
	if(!$('#list').is(':visible'))
		$('#list').show('fast');
	$('#searchResult').remove();
	$('#q').val('');
	$('#page').val('1');
	$('#perPage').val('100');
}

function searchEntry (q) {
	if(q != ''){
		setLoading('#searchEntry', '../');

		var rankingSubject = '';
		if($('#rankingSubject_authors').attr('checked') == true)
			rankingSubject = 'authors';
		if($('#rankingSubject_organizations').attr('checked') == true)
			rankingSubject = 'organizations';

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: rankingSubject,
				q: q
			}),
			success: function (html) {
				$('#searchEntry').html(html);
				if($('#queryId').is('select'))
					$('#list').hide('fast');
				else{
					$('#page').val('1');
					$('#perPage').val('100');
					$('#list').show('fast');
				}
			}
		})
	}
}

function checkConferences (check) {
	$('#checkConferences input:checkbox').each(function () {
		$(this).attr('checked', check);
	});
}

function toggleIndividualFactors () {
	if($('#rankingMethod').val() == 'individual'){
		$('#individualFactors').show('fast');
		$('.individualFactor').attr('disabled', false);
	}else{
		$('#individualFactors').hide('fast');
		$('.individualFactor').val('1').attr('disabled', true);
	}
}

function toggleSearch () {
	if($('#rankingSubject_nations').attr('checked') == true){
		clearSearch();
		$('#search').hide('fast');
	}else
		$('#search').show('fast');
}

$('#factors').hide();

// event listeners
$('#rankingSubject').change(function () {
	toggleSearch();
	searchEntry($('#q').val());
});

$('#rankingMethod').change(function () {
	toggleIndividualFactors();
});

// initial function calls to initialize form state
toggleIndividualFactors();
toggleSearch();

// initial search
searchEntry($('#q').val());

$('#submit').button();
$('#bypassCaching').button();
$('#rankingSubject').buttonset();

$('#period').slider({
	range: true,
	min: <?php echo reset($years)?>,
	max: <?php echo end($years)?>,
	values: [<?php echo $_GET['rankingYearsStart']?>, <?php echo $_GET['rankingYearsEnd']?>],
	slide: function(e, ui) {
		$('#rankingYearsStart').val(ui.values[0]);
		$('#rankingYearsEnd').val(ui.values[1]);
		$('#rankingYears').html('from <strong>' + ui.values[0] + '</strong> to <strong>' + ui.values[1] + '</strong>');
	}
});

$('#resultTable').floatingTableHead();
	/* ]]> */
</script>

	<?php
	if(!empty($_SERVER['QUERY_STRING'])){
		$cachedQuery = (string) ob_get_flush();

		mysql_query("DELETE FROM `__cachedQueries` WHERE `queryHash` = '".md5($_SERVER['QUERY_STRING'])."'");
		mysql_query("INSERT INTO `__cachedQueries` (`queryHash`, `queryContent`, `queryCurrency`) VALUES ('".md5($_SERVER['QUERY_STRING'])."', '".mysql_real_escape_string($cachedQuery)."', UNIX_TIMESTAMP())");
		mysql_query("OPTIMIZE TABLE `__cachedQueries`");
	}
}else{
	$cachedQuery = new stdClass();
	$cachedQuery = mysql_fetch_object($possibleCachedQuery);
?>

<div class="notificationBlock" style="position: fixed; top: 50px; right: 50px; width: 300px;">
	<strong style="display: block; margin: 0 0 0.5em 0;"><?php echo returnIcon('information');?> Information</strong>
	This is a cached result from:<br /><strong><?php echo date('r', $cachedQuery->queryCurrency)?></strong>.<br />
	You can <a href="?<?php echo $_SERVER['QUERY_STRING']?>&bypassCaching=1#result">
		<?php echo returnIcon('arrow-refresh');?> get
	</a> a fresh one.
</div>
<?php echo $cachedQuery->queryContent?>

<?php
}

require '../__close.php';
require '../_footer.php';
?>