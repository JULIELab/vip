<?php
/**
 * Handles the more specific request for listing of database content.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package list
 * @version 1.0
 */

require '../__functions.php';
require '../__connect.php';

switch($_GET['deliver']){
	case 'further':
		$GET = array();
		$url = parse_url($_SERVER['HTTP_REFERER']);
		parse_str($url['query'], $GET);
		$_GET = array_merge($_GET, $GET);
		$allocationsToArticle = array();

		if(empty($_GET['rankingMethod']))
			$_GET['rankingMethod'] = 'simple';

		$userChoicesForQuery = (string) "";
		$factors = array();
		$further = array();

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

			if($_GET['rankingSubject'] == 'authors')
				$userChoicesForQuery .= " AND auth.`author_id` = ".((int) $_GET['id']);
			if($_GET['rankingSubject'] == 'organizations')
				$userChoicesForQuery .= " AND orga.`organization_id` = ".((int) $_GET['id']);

			// get the needed articles
			$query = (string) "";

			$query = "SELECT proc.*, auth.*, allo.*, orga.*, inst.*, 1 AS `points` "
				."FROM `proceedings` proc, `authors` auth, `allocations` allo, `organizations` orga, `institutes` inst "
				."WHERE allo.`proceeding` = proc.`proceeding_id` AND allo.`author` = auth.`author_id` AND allo.`organization` = orga.`organization_id` AND allo.`institute` = inst.`institute_id` "
				.$userChoicesForQuery." "
				."ORDER BY `year` DESC, `conference`, `articlenumber` DESC";

			$proceedings = mysql_query($query);

			// calculate the ranking
			switch($_GET['rankingSubject']){
				case 'organizations':
					while($proceeding = mysql_fetch_object($proceedings)){
						$thisPoints = $factors[$proceeding->year][$proceeding->conference] * ($proceeding->points / $authorCounts[$proceeding->proceeding_id]);

						if($_GET['rankingGrouping'] == 'institutes'){
							if(!is_object($further[$proceeding->institute_id])){
								$further[$proceeding->institute_id] = new stdClass();
								if(empty($proceeding->institute_name))
									$proceeding->institute_name = $proceeding->institute_abbreviation;

								if(!empty($proceeding->institute_website))
									$proceeding->institute_name = '<a href="'.$proceeding->institute_website.'">'.$proceeding->institute_name.'</a>';

								$further[$proceeding->institute_id]->name = getFlag($proceeding->institute_nation, '../').$proceeding->institute_name;
							}
							$further[$proceeding->institute_id]->points += $thisPoints;
						}

						if($_GET['rankingGrouping'] == 'articles'){
							#if($proceeding->organization_id == $_GET['id']){
								if(empty($allocationsToArticle[$proceeding->year.'-'.$proceeding->conference.'-'.$proceeding->articlenumber]))
									$allocationsToArticle[$proceeding->year.'-'.$proceeding->conference.'-'.$proceeding->articlenumber] = (int) 0;
								$allocationsToArticle[$proceeding->year.'-'.$proceeding->conference.'-'.$proceeding->articlenumber]++;

								if(!empty($proceeding->url))
									$proceeding->url = '<a href="'.$proceeding->url.'" title="Link to the proceeding" style="display: inline; float: right">'.returnIcon('page-white-acrobat').'</a>';

								if(empty($proceeding->organization_name))
									$proceeding->organization_name = $proceeding->organization_abbreviation;
								$proceeding->organization_name = htmlspecialchars($proceeding->organization_name);

								if(empty($proceeding->institute_name))
									$proceeding->institute_name = $proceeding->institute_abbreviation;
								$proceeding->institute_name = htmlspecialchars($proceeding->institute_name);

								$dummy = new stdClass();
								$dummy->year = $proceeding->year;
								$dummy->conference = $proceeding->conference;
								$dummy->articlenumber = $proceeding->articlenumber;
								$dummy->url = $proceeding->url;
								$dummy->points = $thisPoints;
								$dummy->organization = getFlag($proceeding->organization_nation, '../').$proceeding->organization_name;
								$dummy->institute = '';
								if($proceeding->organization_nation != $proceeding->institute_nation)
									$dummy->institute = getFlag($proceeding->institute_nation, '../');
								$dummy->institute .= $proceeding->institute_name;
								$further[] = $dummy;
							#}
						}
					}

					if($_GET['rankingGrouping'] == 'institutes')
						usort($further, 'sortIt');
				break;

				case 'authors':
					while($proceeding = mysql_fetch_object($proceedings)){
						if(empty($allocationsToArticle[$proceeding->year.'-'.$proceeding->conference.'-'.$proceeding->articlenumber]))
							$allocationsToArticle[$proceeding->year.'-'.$proceeding->conference.'-'.$proceeding->articlenumber] = (int) 0;
						$allocationsToArticle[$proceeding->year.'-'.$proceeding->conference.'-'.$proceeding->articlenumber]++;

						$thisPoints = $factors[$proceeding->year][$proceeding->conference] * ($proceeding->points / $authorCounts[$proceeding->proceeding]);

						if(!empty($proceeding->url))
							$proceeding->url = '<a href="'.$proceeding->url.'" title="Link to the proceeding" style="display: inline; float: right">'.returnIcon('page-white-acrobat').'</a>';

						if(empty($proceeding->organization_name))
							$proceeding->organization_name = $proceeding->organization_abbreviation;
						$proceeding->organization_name = htmlspecialchars($proceeding->organization_name);
						if(!empty($proceeding->organization_website))
							$proceeding->organization_name = '<a href="'.$proceeding->organization_website.'">'.$proceeding->organization_name.'</a>';

						if(empty($proceeding->institute_name))
							$proceeding->institute_name = $proceeding->institute_abbreviation;
						$proceeding->institute_name = htmlspecialchars($proceeding->institute_name);
						if(!empty($proceeding->institute_website))
							$proceeding->institute_name = '<a href="'.$proceeding->institute_website.'">'.$proceeding->institute_name.'</a>';

						$dummy = new stdClass();
						$dummy->year = $proceeding->year;
						$dummy->conference = $proceeding->conference;
						$dummy->articlenumber = $proceeding->articlenumber;
						$dummy->url = $proceeding->url;
						$dummy->points = $thisPoints;
						$dummy->organization = getFlag($proceeding->organization_nation, '../').$proceeding->organization_name;
						$dummy->institute = '';
						if($proceeding->organization_nation != $proceeding->institute_nation)
							$dummy->institute = getFlag($proceeding->institute_nation, '../');
						$dummy->institute .= $proceeding->institute_name;

						$further[] = $dummy;
					}
				break;
			}
		}

		if(is_array($further) and count($further) > 0){
			$sumPoints = (float) 0;
			foreach($further as $row)
				$sumPoints += $row->points;
?>

<div style="font-size: 0.8em;">
	<div style="float: right"><a href="javascript:;" onclick="$('#furtherInformation_<?php echo ((int) $_GET['id'])?>').html('')"><?php echo returnIcon('cross');?> Hide this table</a></div>
	<table class="dataContainer" id="furtherInformationTable_<?php echo ((int) $_GET['id'])?>" style="display: none;">
<?php
			if($_GET['rankingSubject'] == 'authors' or $_GET['rankingGrouping'] == 'articles'){
?>

		<tr>
			<th style="width: 20%">Article</th>
			<th style="width: 40%">Organization</th>
			<th style="width: 30%">Institute</th>
			<th style="width: 10%">Points</th>
		</tr>
<?php
				$lastProceeding = (string) '';
				$i = (int) 0;
				$style = (string) '';
				foreach($further as $row){
					$row->proceeding = $row->year.'-'.$row->conference.'-'.$row->articlenumber;
					//$style = (string) '';
					if($row->proceeding != $lastProceeding){
						$rowspan = (string) '';
						if($allocationsToArticle[$row->proceeding] > 1)
							$rowspan =  ' rowspan="'.$allocationsToArticle[$row->proceeding].'"';

						$style = 'background: #eee; border: 0; color: #000;';
						if($i++ % 2 == 0)
							$style = 'background: #fff; border: 0; color: #000;';

						echo '<tr style="'.$style.'"><td'.$rowspan.' style="'.$style.'">'.$row->url.'<strong>'.$row->proceeding.'</strong></td>';
					}else{
						echo '<tr style="'.$style.'">';
					}
?>

			<td style="<?php echo $style?>"><?php echo $row->organization?></td>
			<td style="<?php echo $style?>"><?php echo $row->institute?></td>
			<td style="<?php echo $style?>"><?php echo round($row->points, 2)?></td>
<?php
					echo '</tr>';
					$lastProceeding = $row->proceeding;
				}
			}elseif($_GET['rankingSubject'] == 'organizations'){
?>

		<tr>
			<th style="width: 70%">Institute</th>
			<th style="width: 15%">Points</th>
			<th style="width: 15%">Percent</th>
		</tr>
<?php
				$cum = (float) 0;
				foreach($further as $row){
					$row->percent = round($row->points / $sumPoints * 100, 2);
					$cum += $row->points;
?>

		<tr>
			<td><?php echo $row->name?></td>
			<td style="font-weight: bold; text-align: center"><?php echo round($row->points, 2)?></td>
			<td style="text-align: center"><?php echo $row->percent?>%</td>
		</tr>
<?php
				}
			}
?>

	</table>
</div>
<?php
		}
	break;
	case 'authors':
		$authors = mysql_query("SELECT * FROM `authors` WHERE
`name` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' OR
`firstname` LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' OR
CONCAT(`name`, ', ', `firstname`) LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%' OR
CONCAT(`firstname`, ' ', `name`) LIKE '%".mysql_real_escape_string(stripslashes($_GET['q']))."%'
ORDER BY `name`, `firstname`");
		$amount = mysql_num_rows($authors);
?>

<div id="searchResult">
<?php
		if($amount > 0){
			// if existing authors match the search query deliver the selectable list
?>

	<label for="author_<?php echo $_GET['no']?>_<?php echo $_GET['id']?>" class="block">
		<em style="float: right"><a href="javascript:;" onclick="clearSearch()"><?php echo returnIcon('cross');?> Clear search</a></em>
		Author name
	</label>
	<select id="queryId" name="queryId">
<?php
			$author = new stdClass;
			while($author = mysql_fetch_object($authors))
				echo '<option value="'.$author->author_id.'">'.$author->name.', '.$author->firstname.'</option>'.nl;
?>

	</select>
<?php
		}else{
?>

	Sorry! No author present that matches your searchphrase. Try changing your query ...
<?php
		}
?>

</div>
<?php
	break;

	case 'organizations':
		$organizations = mysql_query("SELECT * FROM (SELECT *, MATCH(`organization_name`, `organization_abbreviation`, `organization_nation`, `organization_county`, `organization_city`, `organization_website`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['q']))."') AS `relevancy` FROM  `organizations` ORDER BY `relevancy` DESC, `organization_name`, `organization_abbreviation`) AS `rated_organizations` WHERE `relevancy` > 0");
		$amount = mysql_num_rows($organizations);
?>

<div id="searchResult">
<?php
		if($amount > 0){
?>

	<label for="queryId" class="block">
		<em style="float: right"><a href="javascript:;" onclick="clearSearch()"><?php echo returnIcon('cross');?> Clear search</a></em>
		Organization name
	</label>
	<select id="queryId" name="queryId">
<?php
			$organization = new stdClass;
			while($organization = mysql_fetch_object($organizations)){
?>

		<option value="<?=$organization->organization_id?>"><?=$organization->organization_name?> (<?=$organization->organization_abbreviation?>), <?=$organization->organization_nation?></option>
<?php
			}
?>

	</select>
<?php
			break;
		}else{
?>

	Sorry! No organizations present that match your searchphrase. Try changing your query ...
<?php
		}
?>

</div>
<?php
	break;
}

require '../__close.php';