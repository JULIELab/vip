<?php
/**
 * Delivers dialogs, notifications and so on that are needed for annotation tasks.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package annotation
 * @version 1.0
 */

require '../__functions.php';
require '../__connect.php';

$conferenceSynonyms = array (
	'COLACL' => 'COLING-ACL',
	'ACLMain' => 'ACL',
	'ACLIJCNLP' => 'ACL',
	'NAACLHLT' => 'HLT-NAACL'
);

switch($_GET['deliver']){
	case 'updateArticle':
		$text = (string) 'An error occurred!';
		$further = (string) '';

		$article = mysql_query("SELECT * FROM `proceedings` WHERE `proceeding_id` = ".((int) $_GET['proceedingId']));

		if(is_url($_GET['bibtex']) and mysql_num_rows($article) == 1){
			$article = mysql_fetch_object($article);

			$resource = fopen($_GET['bibtex'], 'r');
			if($resource){
				$content = (string) '';
				$buffer = (string) '';

				while(!feof($resource)){
    				$buffer = fgets($resource, 4096);
    				$content .= $buffer."\n";
				}

				$matches = array();
				preg_match('~^\@InProceedings\{[^\:]*\:[0-9]*\:([^\,]*)\,~i', $content, $matches);
				if(!empty($matches[1])){
					$conference = str_replace(array_keys($conferenceSynonyms), array_values($conferenceSynonyms), preg_replace('~[0-9]~', '', $matches[1]));

					preg_match('~year\s*\=\s*\{?([^\}]*?)\}?\,~i', $content, $matches);
					$year = $matches[1];

					preg_match('~[A-Z][0-9]*\-([0-9]*)\.bib$~i', $_GET['bibtex'], $matches);
					$articlenumber = $matches[1];

					preg_match('~url\s*\=\s*\{([^\}]*)\}~i', $content, $matches);
					$url = $matches[1];
					$url = preg_replace('~\.pdf$~', '', $url).'.pdf';

					if($article->conference == $conference and $article->year = $year){
						mysql_query("UPDATE `proceedings` SET
	`articlenumber` = '".mysql_real_escape_string(stripslashes($articlenumber))."',
	`url` = '".mysql_real_escape_string(stripslashes($url))."',
	`bibtex` = '".mysql_real_escape_string(stripslashes($_GET['bibtex']))."'
WHERE
	`proceeding_id` = ".((int) $article->proceeding_id)."
LIMIT 1");
						if(mysql_errno() == 0)
							$text = '<p class="success">The article has been updated!</p>';
						else
							$text = mysql_error();
					}else{
						$text = '<br />Mismatch while checking data.';
						$further = 'Database:<br />'.$article->year.'-'.$article->conference.'-'.$article->articlenumber
							.'<br/>BibTex:<br />'.$year.'-'.$conference.'-'.$articlenumber;
					}
				}else $text .= '<br />Parsing failed.';
			}else $text .= '<br />Bibtex could not be fetched.';
		}else $text .= '<br />Insufficient data.';

		echo json_encode(
			array(
				'title' => 'Update article',
				'text' => $text,
				'further' => $further
			)
		);
	break;

	case 'updateArticleConfirm':
		$article = mysql_query("SELECT * FROM `proceedings` WHERE `proceeding_id` = ".((int) $_GET['proceedingId']));

		$text = (string) 'An error occured!';
		if(mysql_num_rows($article) == 1){
			$article = mysql_fetch_object($article);
			$text = '<p>Do you really want to update the article <strong>'.$article->year.'-'.$article->conference.'-'.$article->articlenumber.'</strong> (ID: '.$article->proceeding_id.') with the information provided at <a href="'.$_GET['bibtex'].'"><strong>'.htmlspecialchars($_GET['bibtex']).'</strong></a>?</p>';

			$text .= '<div style="float: right; width: 80%"><label for="update_bibtex" class="block">BibTex-Link</label><input type="text" id="update_bibtex" name="update_bibtex" value="'.htmlspecialchars($_GET['bibtex']).'" readonly="readonly" style="width: 100%" /></div>';
			$text .= '<label for="update_proceedingId" class="block">Proceeding</label><input type="text" id="update_proceedingId" name="update_proceedingId" value="'.$article->proceeding_id.'" readonly="readonly" style="width: 15%" />';
		}

		dialogCreate('updateArticleConfirm_'.((int) $_GET['proceedingId']), 'Update articles data', $text);
	break;

	case 'getNationList':
		$nations = mysql_query("SELECT * FROM `nations` ORDER BY `nationName`");
?>

<div id="nationList" title="List of nations">
	<div id="nationListContainer" style="height: 400px; overflow: scroll">
		<table id="nationListTable" class="dataContainer">
			<tr>
				<th style="width: 10%">Flag</th>
				<th style="width: 20%">Abbr</th>
				<th style="width: 60%">Name</th>
				<th style="width: 10%"> </th>
			</tr>
<?php
while($nation = mysql_fetch_object($nations)){
?>

			<tr>
				<td style="text-align: center"><?php echo getFlag($nation->nationAbbreviation, '../')?></td>
				<td style="text-align: center"><strong><?php echo $nation->nationAbbreviation?></strong></td>
				<td><strong><?php echo $nation->nationName?></strong></td>
				<td style="text-align: center"><em><?php echo $nation->nationAbbreviation2?></em></td>
			</tr>
<?php
}
?>

		</table>
	</div>
</div>
<?php
	break;

	case 'checkAnthologiesBibTex':
		if(is_url($_GET['url'])){
			$resource = fopen($_GET['url'], 'r');
			if($resource){
				$content = (string) '';
				$buffer = (string) '';

				while(!feof($resource)){
    				$buffer = fgets($resource, 4096);
    				$content .= $buffer."\n";
				}

				$matches = array();
				preg_match('~^\@InProceedings\{[^\:]*\:[0-9]*\:([^\,]*)\,~i', $content, $matches);
				if(!empty($matches[1])){
					$bibtexConference = $matches[1];
					$conference = $_GET['conference'];
					if(empty($conference))
						$conference = $bibtexConference;

					preg_match('~year\s*\=\s*\{?([^\}]*?)\}?\,~i', $content, $matches);
					$year = $matches[1];

					preg_match('~[A-Z][0-9]*\-([0-9]*)\.bib$~i', $_GET['url'], $matches);
					$articlenumber = $matches[1];

					preg_match('~url\s*\=\s*\{([^\}]*)\}~i', $content, $matches);
					$url = $matches[1];
					$url = preg_replace('~\.pdf$~', '', $url).'.pdf';
?>

<strong><?php echo $year.'-'.$conference.'-'.$articlenumber?></strong>
<?php
					$article = mysql_query("SELECT * FROM `proceedings` WHERE
	`year` = ".((int) $year)." AND
	`conference` = '".mysql_real_escape_string(stripslashes($conference))."' AND
	`articlenumber` = '".mysql_real_escape_string(stripslashes($articlenumber))."'");

					if(mysql_num_rows($article)){
						$article = mysql_fetch_object($article);
						echo ' '.returnIcon('tick').' Article is in the database!';

						if(empty($article->bibtex)){
							mysql_query("UPDATE `proceedings` SET `bibtex` = '".mysql_real_escape_string(stripslashes($_GET['url']))."' WHERE `proceeding_id` = ".((int) $article->proceeding_id)." LIMIT 1");
							echo '<br /><em>'.returnIcon('page-white-text').' Link to bibtex file has been saved to database.</em>';
							echo mysql_error();
						}

						if(empty($article->url)){
							mysql_query("UPDATE `proceedings` SET `url` = '".mysql_real_escape_string(stripslashes($url))."' WHERE `proceeding_id` = ".((int) $article->proceeding_id)." LIMIT 1");
							echo '<br /><em>'.returnIcon('page-white-acrobat').' Link to PDF has been saved to database.</em>';
							echo mysql_error();
						}
					}else{
?>

<strong class="failure"><?php echo returnIcon('cross')?> Article is not in the database!</strong>
<br /><a href="#addArticle" onclick="$('#bibtex').val('<?php echo htmlspecialchars($_GET['url'])?>'); checkBibTex()"><?php echo returnIcon('add')?> Add the article now!</a>
<?php
					}

					if($conference != $bibtexConference){
?>

<br /><em><?php echo returnIcon('error')?> Parsed conference was <strong><?php echo $bibtexConference?></strong></em>
<?php
					}
				}else
					echo '<strong class="failure">'.returnIcon('cross').' Could not be parsed!</strong>';
			}
		}
	break;

	case 'checkAnthologiesExistence':
		if(is_url($_GET['url'])){
			$resource = fopen($_GET['url'], 'r');
			if($resource){
?>

<a href="#anthologiesExistence" onclick="checkAnthologiesBibTex()" style="float: right" id="checkAnthologiesBibTex"><?php echo returnIcon('tick')?> Check all the bibtex files for existence in database.</a>
<?php
				$content = (string) '';
				$buffer = (string) '';

				while(!feof($resource)){
					$buffer = fgets($resource, 4096);
					$content .= $buffer."\n";
				}

				$matches = array();
				preg_match_all('~\=([^\.]*?\.bib)~i', $content, $matches, PREG_SET_ORDER);

				$remoteArray = array();
				$i = (int) 0;
				foreach($matches as $bibtex){
					$bibtex = trim($_GET['url'], '/').'/'.$bibtex[1];
					$remoteArray[] = $bibtex;
?>

<div id="bibtex_<?php echo md5($bibtex)?>" class="semanticSeparation" style="clear: both">
	<em style="float: right">
		<a href="#anthologiesExistence" onclick="checkAnthologiesBibTex(0, <?php echo $i++?>)">
			<?php echo returnIcon('arrow-right')?>
			Stop checking the list here
		</a>:
		<?php echo $bibtex?>
	</em>

	<div id="bibtex_<?php echo md5($bibtex)?>_result"></div>
</div>
<?php
				}

				$i = (int) 0;
				$bibtexList = (string) '';
				$md5Hashes = (string) '';
				foreach($remoteArray as $bibtex){
					if($i++ > 0){
						$bibtexList .= ', ';
						$md5Hashes .= ', ';
					}

					$bibtexList .= '"'.$bibtex.'"';
					$md5Hashes .= '"'.md5($bibtex).'"';
				}
?>

<script type="text/javascript">
	/* <![CDATA[ */
var bibtexFiles = Array(<?php echo $bibtexList?>);
var md5Hashes = Array(<?php echo $md5Hashes?>);

if(bibtexFiles.length == 0){
	$('#checkAnthologiesBibTex').hide();
	alert('No bibtex files were found!')
}
	/* ]]> */
</script>
<?php
			}
		}
	break;

	case 'checkAnthology':
?>

<a href="javascript:;" onclick="checkAnthology($('#anthology').val())" style="float: right"><?php echo returnIcon('tick')?> Check again!</a>
<?php
		if(is_url($_GET['url'])){
			$bibTexResult = mysql_query("SELECT * FROM `proceedings` WHERE `bibtex` != '' ORDER BY `bibtex`");
			$bibTexArray = array();
			while($bibTex = mysql_fetch_object($bibTexResult))
				$bibTexArray[] = basename($bibTex->bibtex);

			$remoteCount = (int) 0;
			$resource = fopen($_GET['url'], 'r');
			if($resource){
				$content = (string) '';
				$buffer = (string) '';

				while(!feof($resource)){
    				$buffer = fgets($resource, 4096);
    				$content .= $buffer."\n";
				}

				$matches = array();
				preg_match_all('~\=([^\.]*?\.bib)~i', $content, $matches, PREG_SET_ORDER);

				$remoteArray = array();
				foreach($matches as $match)
					$remoteArray[] = $match[1];
				$remoteCount = count($remoteArray);

				if($remoteCount > 0){
					$remoteArray = array_diff($remoteArray, $bibTexArray);
					if(count($remoteArray) > 0){
?>

<label for="anthologyEntry" class="block">Please select an entry!</label>
<select id="anthologyEntry" id="anthologyEntry" onchange="$('#bibtex').val($('#anthologyEntry').val()); checkBibTex()" onclick="">
	<option value="">Please choose!</option>
<?php
						foreach($remoteArray as $bibTex){
?>

	<option value="<?php echo $_GET['url'].$bibTex?>"><?php echo $bibTex?></option>
<?php
						}
?>

</select>
<em>
	<?php echo returnIcon('information')?>
	Found <strong><?php echo $remoteCount?> links</strong> to bibtex files.
<?php
					}

					if($remoteCount - count($remoteArray) > 0){
?>

	<br />
	<?php echo returnIcon('error')?>
	<strong><?php echo ($remoteCount - count($remoteArray))?> entries</strong> are yet in the database and therefore do not appear here!
<?php
					}
?>

</em>
<?php
				}else{
?>

<strong class="failure"><?php echo returnIcon('error')?> URL doesn't seem to contain BibTex links.</strong>
<?php
				}
			}else{
?>

<strong class="failure"><?php echo returnIcon('error')?> URL could not be opened.</strong>
<?php
			}
		}else{
?>

<strong class="failure"><?php echo returnIcon('error')?> This is not an URL.</strong>
<?php
		}
	break;

	case 'checkPDF':
		if(is_url($_GET['url'])){
			$resource = fopen($_GET['url'], 'r');
			if($resource){
				$information = stream_get_meta_data($resource);
				$type = (string) '';
				foreach($information['wrapper_data'] as $header)
					$matches = array();
					if(preg_match('~Content\-Type\:\s([^\s]*)~i', $header, $matches))
						$type = $matches[1];

				if($type == 'application/pdf')
					echo '<a href="'.$_GET['url'].'" style="float: right">'.returnIcon('page-white-acrobat').' Open it.</a><strong>'.returnIcon('tick').' URL seems to be a PDF!';
				else{
?>

<strong><?php echo returnIcon('cross')?> URL does NOT seem to be a PDF! Type given is <em><?php echo $type?></em>.</strong><br />
<?php echo returnIcon('information')?> <a href="javascript:;" onclick="$('#url').val($('#url').val()+'.pdf'); checkPDF()">Try appending .pdf to the URL first!</a>
<?php
				}
			}
		}
	break;

	case 'checkConference':
		if(!empty($_GET['conference'])){
			if(mysql_num_rows(mysql_query("SELECT `conference` FROM `proceedings` WHERE `conference` = '".mysql_real_escape_string(stripslashes($_GET['conference']))."' GROUP BY `conference`"))){
				echo '<strong>'.returnIcon('tick').' Conference exists.</strong>';
				echo '<br /><em>'.mysql_num_rows(mysql_query("SELECT * FROM `proceedings` WHERE `conference` = '".mysql_real_escape_string(stripslashes($_GET['conference']))."' AND `year` = ".((int) $_GET['year'])."")).' article(s) of this combination of conference and year are yet in the database.';
			}else{
				echo '<strong>'.returnIcon('cross').' Something might be wrong here. We do not have this conference in the database yet!</strong>';
			}
		}
	break;

	case 'checkBibTex':
?>

<a href="javascript:;" onclick="checkBibTex($('#bibtex').val())" style="float: right"><?php echo returnIcon('tick')?> Check again!</a>
<?php
		if(is_url($_GET['url'])){
			$resource = fopen($_GET['url'], 'r');
			if($resource){
				$content = (string) '';
				$buffer = (string) '';

				while(!feof($resource)){
    				$buffer = fgets($resource, 4096);
    				$content .= $buffer."\n";
				}

				$content = str_replace('\"{o}', 'ö', $content);

				$matches = array();
				preg_match('~^\@InProceedings\{[^\:]*\:[0-9]*\:([^\,]*)\,~i', $content, $matches);
				if(!empty($matches[1])){
					$conference = str_replace(array_keys($conferenceSynonyms), array_values($conferenceSynonyms), preg_replace('~[0-9]~', '', $matches[1]));

					preg_match('~year\s*\=\s*\{?([^\}]*?)\}?\,~i', $content, $matches);
					$year = $matches[1];

					preg_match('~[A-Z][0-9]*\-([0-9]*)\.bib$~i', $_GET['url'], $matches);
					$articlenumber = $matches[1];

					preg_match('~url\s*\=\s*\{([^\}]*)\}~i', $content, $matches);
					$url = $matches[1];
?>

<strong style="border-bottom: 1px solid #ccc; display: block; margin: 0 0 0.5em 0; padding: 0 0 0.25em 0">BibTex-information</strong>
<strong>Conference</strong>: <?php echo $conference?>, <?php echo $year?><br />
<strong>Article</strong>: <?php echo $articlenumber?><br />
<div id="suggestedAuthors">
<?php
					$authorList = (string) '';

					if(preg_match('~author\s*\=\s*\{([^\}]*)\}\,~i', $content, $matches)){
?>

	<a href="javascript:;" onclick="$('#suggestedAuthors').hide(); authors = Array();" style="float: right"><?php echo returnIcon('cross')?>Reject authors!</a>
<?php
						echo '<strong>Author';
						$authors = explode(' and ', $matches[1]);
						$i = (int) 0;

						$possibleDoublette = new stdClass();
						$proceedingIDs = array();
						if(count($authors) > 1){
							$evalString = (string) 'return array_intersect(';
							echo 's</strong>: <ul>';
							foreach($authors as $author){
								$author = trim($author);

								echo '<li>'.$author.'</li>';
								if($i++ > 0){
									$authorList .= ', ';
									$evalString .= ', ';
								}
								$authorList .= '"'.$author.'"';

								$result = mysql_query("SELECT * FROM `proceedings` proc, `allocations` allo, `authors` auth
WHERE `conference` = '".$conference."' AND `year` = ".$year."
AND proc.`proceeding_id` = allo.`proceeding` AND auth.`author_id` = allo.`author`
AND (
	`name` LIKE '%".mysql_real_escape_string(stripslashes($author))."%' OR
	`firstname` LIKE '%".mysql_real_escape_string(stripslashes($author))."%' OR
	CONCAT(`name`, ', ', `firstname`) LIKE '%".mysql_real_escape_string(stripslashes($author))."%' OR
	CONCAT(`firstname`, ' ', `name`) LIKE '%".mysql_real_escape_string(stripslashes($author))."%'
)
ORDER BY `articlenumber`");
								if(mysql_num_rows($result) > 0){
									while($possibleDoublette = mysql_fetch_object($result)){
										$proceedingIDs[$i][] = $possibleDoublette->proceeding_id;
									}
								}

								$evalString .= '$proceedingIDs['.$i.']';
							}
							echo '</ul>';
							$evalString .= ');';

							$possibleDoublettes = array();
							$possibleDoublettes = eval($evalString);

							if(count($possibleDoublettes) > 0){
?>

	<strong style="border-bottom: 1px solid #ccc; display: block; margin: 1em 0 0.5em 0; padding: 0 0 0.25em 0"><?php echo returnIcon('error')?>Articles from these authors in this anthology</strong>
	<em style="display: block; padding: 0 0.5em 0.5em 0.5em">Please be extra careful here! These authors have written articles in this very anthology. Propably they are just miscoded.</em>
<?php
								foreach($possibleDoublettes as $id){
									$possibleDoublette = mysql_fetch_object(mysql_query("SELECT * FROM `proceedings` WHERE `proceeding_id` = ".((int) $id)));
									$referenced = FALSE;
?>

	<?php echo returnIcon('database')?>
	<?php echo $possibleDoublette->year.'-'.$possibleDoublette->conference.'-'.$possibleDoublette->articlenumber;?>
<?php
									if($possibleDoublette->year == $year and $possibleDoublette->conference == $conference and $possibleDoublette->articlenumber == $articlenumber){
?>

	<?php echo returnIcon('tick')?>
	Article referenced by bibtex information
<?php
										$referenced = TRUE;
									}

									if(!$referenced or (empty($possibleDoublette->bibtex) or empty($possibleDoublette->url))){
?>

	<a href="javascript:;" onclick="updateArticleConfirm(<?php echo $possibleDoublette->proceeding_id?>, '<?php echo $_GET['url']?>')">
		<?php echo returnIcon('arrow-refresh')?> Update article with given bibtex!
	</a>
<?php
									}
?>

	<br />
<?php
								}
							}
						}else{
							echo '</strong>: '.$authors[0];
							$authorList = '"'.$authors[0].'"';

							$result = mysql_query("SELECT * FROM `proceedings` proc, `allocations` allo, `authors` auth
WHERE `conference` = '".$conference."' AND `year` = ".$year."
AND proc.`proceeding_id` = allo.`proceeding` AND auth.`author_id` = allo.`author`
AND (
	`name` LIKE '%".mysql_real_escape_string(stripslashes($authors[0]))."%' OR
	`firstname` LIKE '%".mysql_real_escape_string(stripslashes($authors[0]))."%' OR
	CONCAT(`name`, ', ', `firstname`) LIKE '%".mysql_real_escape_string(stripslashes($authors[0]))."%' OR
	CONCAT(`firstname`, ' ', `name`) LIKE '%".mysql_real_escape_string(stripslashes($authors[0]))."%'
)
ORDER BY `articlenumber`");

							if(mysql_num_rows($result) > 0){
?>

	<strong style="border-bottom: 1px solid #ccc; display: block; margin: 1em 0 0.5em 0; padding: 0 0 0.25em 0"><?php echo returnIcon('error')?>Articles from this author in this anthology</strong>
	<em style="display: block; padding: 0 0.5em 0.5em 0.5em">Please be extra careful here! This author has written articles in this very anthology. Propably they are just miscoded.</em>
<?php
								while($possibleDoublette = mysql_fetch_object($result)){
									$referenced = FALSE;
?>

	<?php echo returnIcon('database')?>
	<?php echo $possibleDoublette->year.'-'.$possibleDoublette->conference.'-'.$possibleDoublette->articlenumber;?>
<?php
									if($possibleDoublette->year == $year and $possibleDoublette->conference == $conference and $possibleDoublette->articlenumber == $articlenumber){
?>

	<?php echo returnIcon('tick')?>
	Article referenced by bibtex information
<?php
										$referenced = TRUE;
									}

									if(!$referenced or (empty($possibleDoublette->bibtex) or empty($possibleDoublette->url))){
?>

	<a href="javascript:;" onclick="updateArticleConfirm(<?php echo $possibleDoublette->proceeding_id?>, '<?php echo $_GET['url']?>')"><?php echo returnIcon('arrow-refresh')?>Update article with given bibtex!</a>
<?php
									}
?>
	<br />
<?php
								}
							}
						}
					}else{
?>

<strong>
	<?php echo returnIcon('cross')?>
	Something went wrong while parsing the authors! Please check yourself!
</strong>
<?php
					}
?>

</div>
<script type="text/javascript">
	/* <![CDATA[ */
var year = '<?php echo $year?>';
var conference = '<?php echo $conference?>';
var articlenumber = '<?php echo $articlenumber?>';
var url = '<?php echo $url?>';
authors = Array(<?php echo $authorList?>);

$('#year option[value="'+year+'"]').attr('selected', 'selected');
$('#conference').val(conference);
$('#articlenumber').val(articlenumber);
$('#url').val(url);
	/* ]]> */
</script>
<?php
				}else{
?>

<strong class="failure"><?php echo returnIcon('error')?> This does not seem to be BibTex-information.</strong>
<?php
				}
			}else{
?>

<strong class="failure"><?php echo returnIcon('error')?> URL could not be opened.</strong>
<?php
			}
		}else{
?>

<strong class="failure"><?php echo returnIcon('error')?> This is not an URL.</strong>
<?php
		}
	break;

	case 'checkAuthorsNation':
		if(strlen($_GET['abbreviation']) == 3){
			$_GET['abbreviation'] = mb_strtoupper($_GET['abbreviation']);
			$nation = mysql_query("SELECT * FROM `nations` WHERE `nationAbbreviation` = '".mysql_real_escape_string(stripslashes($_GET['abbreviation']))."'");

			if(mysql_num_rows($nation) == 1)
				echo '<em>'.getFlag($_GET['abbreviation'], '../').' '.getNation($_GET['abbreviation']).'</em>';
			else echo '<span class="failure">Wrong!</span>';
		}else echo 'Too short!';
	break;

	case 'checkArticlesExistence':
		$proceeding = mysql_query("SELECT * FROM `proceedings` WHERE
	`year` = ".((int) $_GET['year'])." AND
	`conference` = '".mysql_real_escape_string(stripslashes($_GET['conference']))."' AND
	`articlenumber` = '".mysql_real_escape_string(stripslashes($_GET['articlenumber']))."'");
		if(mysql_errno() != 0)
			echo mysql_error();

		if(mysql_num_rows($proceeding) > 0)
			echo 'false';
		else
			echo 'true';
	break;

	case 'getBlocks':
		for($i = $_GET['start']; $i <= $_GET['end']; $i++){
?>

<div id="organizationBlock_<?php echo $i?>" class="semanticSeparation">
	<h3>Organization no. <?php echo $i?></h3>
	<div style="padding: 0 1em">
		<input type="text" id="searchOrganizations_<?php echo $i?>" name="searchOrganizations_<?php echo $i?>" onmouseup="searchOrganizations(<?php echo $i?>, this.value)" onkeyup="delayRequest('searchOrganizations', Array(<?php echo $i?>, this.value))" style="width: 50%" />
		<label for="searchOrganizations_<?php echo $i?>"><?php echo returnIcon('database-go')?> enter searchphrase</label> at least 2 chars long to search database
		<div id="organizationContainer_<?php echo $i?>" style="margin: 1em 0 0 0"></div>
		<div id="instituteContainer_<?php echo $i?>"></div>
	</div>

	<table style="width: 100%">
		<tr>
			<td style="width: 30%">
				<input type="text" id="authorAmount_<?php echo $i?>" name="authorAmount_<?php echo $i?>" onkeyup="delayRequest('getFieldsForAuthors', Array(<?php echo $i?>, this.value));" size="3" maxlength="3" value="0" />
				<label for="authorAmount_<?php echo $i?>">assigned authors</label>
			</td>
			<td id="authorFieldsContainer_<?php echo $i?>" style="width: 70%">
			</td>
		</tr>
	</table>
</div>
<?php
		}
	break;

	case 'getInstitutes':
		if(is_numeric($_GET['organization'])){
			$institutes = mysql_query("SELECT * FROM `institutes` WHERE `institute_organization` = ".((int) $_GET['organization']));
			$amount = mysql_num_rows($institutes);
?>

<label for="institute_<?php echo $_GET['no']?>" class="block">Institute no. <?php echo $_GET['no']?> (<?php echo mysql_num_rows($institutes)?> results)</label>
<select id="institute_<?php echo $_GET['no']?>" name="institute[<?php echo $_GET['no']?>][id]" style="width: 50%">
	<option value="0">&lt;none&gt;</option>
<?php
			if($amount > 0){
				$institute = new stdClass;
				while($institute = mysql_fetch_object($institutes))
					echo '<option value="'.$institute->institute_id.'">'.$institute->institute_name.' ('.$institute->institute_abbreviation.')</option>';
			}
?>

</select>
<?php
		}
	break;

	case 'searchOrganizationsForInstituteCreation':
		$organizations = mysql_query("SELECT * FROM (SELECT *, MATCH(`organization_name`, `organization_abbreviation`, `organization_nation`, `organization_county`, `organization_city`, `organization_website`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['query']))."') AS `relevancy` FROM  `organizations` ORDER BY `relevancy` DESC, `organization_name`, `organization_abbreviation`) AS `rated_organizations` WHERE `relevancy` > 0");
		$amount = mysql_num_rows($organizations);

		if($amount > 0){
?>

<label for="instituteOrganization" class="block">Organization <?php if($amount > 1) echo '('.$amount.' results)';?></label>
<select id="instituteOrganization" name="instituteOrganization" style="width: 100%">';
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
			break;
		}else{
?>

Sorry! No organizations present that match your searchphrase.
<?php
		}
	break;

	case 'createInstitute':
		$str = (string) 'You did not enter sufficient data!';

		if(!empty($_GET['name']) and !empty($_GET['nation']) and $_GET['organization'] != 0){
			mysql_query("INSERT INTO `institutes` (
	`institute_name`,
	`institute_abbreviation`,
	`institute_organization`,
	`institute_nation`,
	`institute_county`,
	`institute_city`,
	`institute_website`,
	`editing_status`
) VALUES (
	'".mysql_real_escape_string(stripslashes(trim($_GET['name'])))."',
	'".mysql_real_escape_string(stripslashes(mb_strtoupper(trim($_GET['abbreviation']))))."',
	'".((int) $_GET['organization'])."',
	'".mysql_real_escape_string(stripslashes(mb_strtoupper(trim($_GET['nation']))))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['county'])))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['city'])))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['website'])))."',
	'filled'
);");
			$institute = mysql_fetch_object(mysql_query("SELECT * FROM `institutes` WHERE `institute_id` = LAST_INSERT_ID()"));
			$str = printInstituteData($institute);
		}
		dialogCreate('instituteCreateStatus', 'Institute created', $str);
	break;

	case 'getInstituteCreateForm':
		echo dialogCreate('instituteCreateForm', 'Create an institute', '<table>
	<tr>
		<td style="width: 70%">
			<label for="instituteName" class="block">Name*</label>
			<input type="text" id="instituteName" name="instituteName" style="width: 100%" />
		</td>
		<td style="width: 30%">
			<label for="instituteAbbreviation" class="block">Abbreviation</label>
			<input type="text" id="instituteAbbreviation" name="instituteAbbreviation" style="width: 100%" />
		</td>
	</tr>
</table>
<table>
	<tr>
		<td style="width: 40%">
			<label for="instituteNation" class="block">Nation*</label>
			<input type="text" id="instituteNation" name="instituteNation" size="3" onkeyup="checkInstitutesNation(this.value)" />
			<span id="instituteFlagContainer"></span>
		</td>
		<td style="width: 30%">
			<label for="instituteCounty" class="block">County</label>
			<input type="text" id="instituteCounty" name="instituteCounty" style="width: 100%" />
		</td>
		<td style="width: 30%">
			<label for="instituteCity" class="block">City</label>
			<input type="text" id="instituteCity" name="instituteCity" style="width: 100%" />
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<label for="instituteWebsite" class="block">Website</label>
			<input type="text" id="instituteWebsite" name="instituteWebsite" style="width: 100%" />
			<hr />
			<strong>Associated organization*</strong>
			<br /><br /><input type="text" id="instituteSearchOrganization" name="instituteSearchOrganization" onmouseup="searchOrganizationsForInstituteCreation(this.value)" onkeyup="delayRequest(\'searchOrganizationsForInstituteCreation\', Array(this.value))" style="width: 50%" />
			<label for="instituteSearchOrganization">searchphrase</label> at least 2 chars
			<div id="instituteOrganizationContainer" style="margin: 1em 0 0 0"></div>
		</td>
	</tr>
</table>');
	break;

	case 'getOrganizationCreateForm':
		echo dialogCreate('organizationCreateForm', 'Create an organization', '<table>
	<tr>
		<td colspan="2">
			<label for="organizationName" class="block">Name*</label>
			<input type="text" id="organizationName" name="organizationName" style="width: 100%" />
		</td>
		<td style="width: 30%">
			<label for="organizationAbbreviation" class="block">Abbreviation</label>
			<input type="text" id="organizationAbbreviation" name="organizationAbbreviation" style="width: 100%" />
		</td>
	</tr>
	<tr>
		<td style="width: 40%">
			<label for="organizationNation" class="block">Nation*</label>
			<input type="text" id="organizationNation" name="organizationNation" size="3" onkeyup="checkOrganizationsNation(this.value)" />
			<span id="organizationFlagContainer"></span>
		</td>
		<td style="width: 30%">
			<label for="organizationCounty" class="block">County</label>
			<input type="text" id="organizationCounty" name="organizationCounty" style="width: 100%" />
		</td>
		<td style="width: 30%">
			<label for="organizationCity" class="block">City</label>
			<input type="text" id="organizationCity" name="organizationCity" style="width: 100%" />
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<label for="organizationWebsite" class="block">Website</label>
			<input type="text" id="organizationWebsite" name="organizationWebsite" style="width: 100%" />
		</td>
	</tr>
</table>');
	break;

	case 'checkNation':
		if(strlen($_GET['abbreviation']) == 3){
			$_GET['abbreviation'] = mb_strtoupper($_GET['abbreviation']);
			$nation = mysql_query("SELECT * FROM `nations` WHERE `nationAbbreviation` = '".mysql_real_escape_string(stripslashes($_GET['abbreviation']))."'");

			if(mysql_num_rows($nation) == 1)
				echo '<em>'.getFlag($_GET['abbreviation'], '../').' '.getNation($_GET['abbreviation']).'</em>';
			else echo '<span class="failure">Wrong!</span>';
		}else echo 'Too short!';
	break;

	case 'createOrganization':
		$str = (string) 'You did not enter sufficient data!';

		if(!empty($_GET['name']) and !empty($_GET['nation'])){
			mysql_query("INSERT INTO `organizations` (
	`organization_name`,
	`organization_abbreviation`,
	`organization_nation`,
	`organization_county`,
	`organization_city`,
	`organization_website`
) VALUES (
	'".mysql_real_escape_string(stripslashes(trim($_GET['name'])))."',
	'".mysql_real_escape_string(stripslashes(mb_strtoupper(trim($_GET['abbreviation']))))."',
	'".mysql_real_escape_string(stripslashes(mb_strtoupper(trim($_GET['nation']))))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['county'])))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['city'])))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['website'])))."'
)");

			$organization = mysql_fetch_object(mysql_query("SELECT * FROM `organizations` WHERE `organization_id` = LAST_INSERT_ID()"));
			$str = printOrganizationData($organization);
		}

		echo dialogCreate('organizationCreateStatus', 'Organization created', $str);
	break;

	case 'searchOrganizations':
		$organizations = mysql_query("SELECT * FROM (SELECT *, MATCH(`organization_name`, `organization_abbreviation`, `organization_nation`, `organization_county`, `organization_city`, `organization_website`) AGAINST ('".mysql_real_escape_string(stripslashes($_GET['query']))."') AS `relevancy` FROM  `organizations` ORDER BY `relevancy` DESC, `organization_name`, `organization_abbreviation`) AS `rated_organizations` WHERE `relevancy` > 0");
		$amount = mysql_num_rows($organizations);

		if($amount > 0){
?>

<label for="organization_<?php echo $_GET['no']?>" class="block">Organization no. <?php echo $_GET['no']?> <?php if($amount > 1) echo '('.$amount.' results)';?></label>
<select id="organization_<?php echo $_GET['no']?>" name="organization[<?php echo $_GET['no']?>][id]" onchange="getInstitutes(<?php echo $_GET['no']?>, this.value);" onkeyup="delayRequest('getInstitutes', Array(<?php echo $_GET['no']?>, this.value));" style="width: 50%">';
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
			break;
		}else{
?>

Sorry! No organizations present that match your searchphrase. You can <a href="javascript:getOrganizationCreateForm();">create new ones</a> though!
<?php
		}
	break;

	case 'searchAuthors':
		$authors = mysql_query("SELECT * FROM `authors` WHERE
`name` LIKE '%".mysql_real_escape_string(stripslashes($_GET['query']))."%' OR
`firstname` LIKE '%".mysql_real_escape_string(stripslashes($_GET['query']))."%' OR
CONCAT(`name`, ', ', `firstname`) LIKE '%".mysql_real_escape_string(stripslashes($_GET['query']))."%' OR
CONCAT(`firstname`, ' ', `name`) LIKE '%".mysql_real_escape_string(stripslashes($_GET['query']))."%'
ORDER BY `name`, `firstname`");
		$amount = mysql_num_rows($authors);

		if($amount > 0){
			$label = (string) 'Author from database';
			if($amount > 1)
				$label = 'Authors from database ('.$amount.' results)';
?>

<table style="width: 100%">
	<tr>
		<td style="width: 60%; padding: 0 1em 0 0">
			<label for="author_<?php echo $_GET['no']?>_<?php echo $_GET['id']?>" class="block"><?php echo returnIcon('user')?> <?php echo $label?></label>
			<select id="author_<?php echo $_GET['no']?>_<?php echo $_GET['id']?>" name="author[<?php echo $_GET['no']?>][<?php echo $_GET['id']?>][id]">
<?php
			if($amount > 1)
				echo '				<option value="0">please choose</option>'.nl;
			$author = new stdClass;
			while($author = mysql_fetch_object($authors))
				echo '				<option value="'.$author->author_id.'">'.$author->name.', '.$author->firstname.'</option>'.nl;
?>

			</select>
		</td>
		<td style="padding: 0">
<?php
			if($_GET['no'] == 0){
?>

			<label for="author_<?php echo $_GET['no']?>_<?php echo $_GET['id']?>_nation" class="block">
				<?php echo returnIcon('world')?> Nation
			</label>
			<input type="text" id="author_<?php echo $_GET['no']?>_<?php echo $_GET['id']?>_nation" name="author[<?php echo $_GET['no']?>][<?php echo $_GET['id']?>][nation]" size="3" onkeyup="checkAuthorsNation(<?php echo $_GET['id']?>, this.value)" onmouseup="checkAuthorsNation(<?php echo $_GET['id']?>, this.value)" />
			<span id="authorFlagContainer_<?php echo $_GET['id']?>"></span>
<?php
			}
?>

		</td>
	</tr>
</table>
<?php
		}else{
?>

<strong><?php echo returnIcon('cross')?> No authors that match your searchphrase <em><?php echo htmlspecialchars($_GET['query'])?></em>.</strong><br />
You can <a href="javascript:;" onclick="getAuthorCreateForm(<?php echo $_GET['no']?>, <?php echo $_GET['id']?>)"><?php echo returnIcon('add')?> create a new one</a> and
<a href="javascript:;" onclick="searchAuthors(<?php echo $_GET['no']?>, <?php echo $_GET['id']?>, $('#author_<?php echo $_GET['no']?>_<?php echo $_GET['id']?>_search').val())"><?php echo returnIcon('arrow-refresh')?> refresh afterwards</a>.
<?php
		}
	break;

	case 'getFieldsForAuthors':
		if($_GET['amount'] > 0){
			for($i = 1; $i <= $_GET['amount']; $i++){
				$id = $_GET['no'].'_'.$i;
?>

<label for="author_<?php echo $id?>" class="block"><?php if($_GET['no'] == 0) echo 'Unassigned'; else echo 'Assigned'?> author no. <?php echo $i?></label>
<?php
				if(is_array($_GET['authors']) and count($_GET['authors']) > 0){
?>

<select id="author_<?php echo $id?>_search" name="author[<?php echo $_GET['no']?>][<?php echo $i?>][search]" style="width: 40%" onmouseup="searchAuthors(<?php echo $_GET['no']?>, <?php echo $i?>, this.value)" onkeyup="delayRequest('searchAuthors', Array(<?php echo $_GET['no']?>, <?php echo $i?>, this.value))">
	<option value="">Please choose!</option>
<?php
	foreach($_GET['authors'] as $author)
		echo '<option value="'.trim($author).'">'.trim($author).'</option>';
?>

</select>
<label for="author_<?php echo $id?>_search"><?php echo returnIcon('database-go')?> select an author</label> to search database
<?php
				}else{
?>

<input type="text" id="author_<?php echo $id?>_search" name="author[<?php echo $_GET['no']?>][<?php echo $i?>][search]" style="width: 40%" onmouseup="searchAuthors(<?php echo $_GET['no']?>, <?php echo $i?>, this.value)" onkeyup="delayRequest('searchAuthors', Array(<?php echo $_GET['no']?>, <?php echo $i?>, this.value));">
<label for="author_<?php echo $id?>_search"><?php echo returnIcon('database-go')?> enter serchphrase</label> at least 2 chars long to search database
<?php
				}
?>

<div id="chooseAuthor_<?php echo $_GET['no']?>_<?php echo $i?>" style="margin: 1em 0 0 0"></div>
<?php
			}
		}
	break;

	case 'getAuthorCreateForm':
		echo dialogCreate('authorCreateForm', 'Create an author', '<table>
	<tr>
		<td style="width: 50%">
			<label for="authorFirstname" class="block">Firstname*</label>
			<input type="text" id="authorFirstname" name="authorFirstname" style="width: 100%" />
		</td>
		<td style="width: 50%">
			<label for="authorName" class="block">Name*</label>
			<input type="text" id="authorName" name="authorName" style="width: 100%" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label for="authorMail" class="block">Mail</label>
			<input type="text" id="authorMail" name="authorMail" style="width: 100%" />
		</td>
	</tr>
</table>');
	break;

	case 'createAuthor':
		$str = (string) '';
		if(!empty($_GET['firstname']) and !empty($_GET['name']) and (empty($_GET['mail']) or is_mail($_GET['mail']))){
			mysql_query("INSERT INTO `authors` (
	`firstname`,
	`name`,
	`mail`
) VALUES (
	'".mysql_real_escape_string(stripslashes(trim($_GET['firstname'])))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['name'])))."',
	'".mysql_real_escape_string(stripslashes(trim($_GET['mail'])))."'
)");

			$author = mysql_query("SELECT * FROM `authors` WHERE `author_id` = LAST_INSERT_ID()");
			if(mysql_num_rows($author)){
				$author = mysql_fetch_object($author);
				$str .= '<strong class="success">Author has been created!</strong>';
				$str .= '<br /><br /><strong>'.$author->name.'</strong>, '.$author->firstname;
				$str .= '<br />'.returnIcon('email').' '.$author->mail;
			}else{
				$str .= '<strong class="failure">Author could not be created!</strong>';
				$str .= '<br /><strong>'.mysql_errno().'</strong>: '.mysql_error();
			}

		}else
			$str = '<strong class="failure">'.returnIcon('cross').' Your input did not meet the requirements.</strong>
<br />'.returnIcon('textfield_rename').' Fill all the fields that are marked with an Asterisk *!
<br />'.returnIcon('email').' If you enter a mail address it has to be valid!';

		echo dialogCreate('authorCreateStatus', 'Author created', $str);
	break;
}

require '../__close.php';