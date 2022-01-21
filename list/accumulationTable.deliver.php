<?php
/**
 * Delivers specific requests to visualize the spreading of articles.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package list
 * @version 1.0
 */
require '../__functions.php';
require '../__connect.php';

switch($_GET['deliver']){
	case 'getArticles':
		$sql = (string) "";

		if(!empty($_GET['conference'])){
			$sql .= " AND proc.`conference` = '".mysql_real_escape_string(stripslashes($_GET['conference']))."'";
		}

		if(!empty($_GET['year'])){
			$sql .= " AND proc.`year` = ".((int) $_GET['year']);
		}

		$sql = "SELECT proc.*, auth.*, allo.*, orga.*, inst.*, 1 AS `points`
	FROM `proceedings` proc, `authors` auth, `allocations` allo, `organizations` orga, `institutes` inst
	WHERE allo.`proceeding` = proc.`proceeding_id` AND allo.`author` = auth.`author_id` AND allo.`organization` = orga.`organization_id` AND allo.`institute` = inst.`institute_id`".$sql."
	ORDER BY `year` ASC, `conference` ASC, `articlenumber` ASC, `name` ASC, `firstname` ASC";

		$articles = mysql_query($sql);

		if(mysql_num_rows($articles) > 0){
?>

<h3 id="result">List of articles</h3>
<table id="articleList" class="dataContainer">
	<tr>
		<th style="width: 20%">Article</th>
		<th style="width: 20%">Author</th>
		<th style="width: 60%">Organization/Institute</th>
	</tr>
<?php
			$article = new stdClass;
			$lastArticle = (string) '';
			while($article = mysql_fetch_object($articles)){
				$article->identificiation = (string) ' ';

				if($lastArticle != $article->year.'-'.$article->conference.'-'.$article->articlenumber){
					$article->identification = $article->year.'-'.$article->conference.'-'.$article->articlenumber;
					if(!empty($article->url))
						$article->identification .= ' <a href="'.$article->url.'">'.returnIcon('page-white-acrobat').'</a>';
				}

				if(empty($article->organization_name))
					$article->organization_name = $article->organization_abbreviation;
				if(!empty($article->organization_website))
					$article->organization_name = '<a href="'.$article->organization_website.'">'.returnIcon('world').' '.$article->organization_name.'</a>';

				if(empty($article->institute_name))
					$article->institute_name = $article->institute_abbreviation;
				if(!empty($article->institute_website))
					$article->institute_name = '<a href="'.$article->insistute_website.'">'.returnIcon('world').' '.$article->institute_name.'</a>';
?>

	<tr>
		<td style="text-align: right"><strong><?php echo $article->identification?></strong></td>
		<td><strong><em><?php echo $article->name?></em>, <?php echo $article->firstname?></strong></td>
		<td>
			<?php echo $article->organization_name?><br />
			&rArr; <?php echo $article->institute_name?>
		</td>
	</tr>
<?php
				$lastArticle = $article->year.'-'.$article->conference.'-'.$article->articlenumber;
			}
?>

</table>
<script type="text/javascript">
	/* <![CDATA[ */
$('#articleList').floatingTableHead();
	/* ]]> */
</script>
<?php
		}
	break;

	case 'chart':
		$counts = array();
		$years = array();

		$articles = mysql_query("SELECT `year`, COUNT(*) AS `count` FROM `proceedings` GROUP BY `year` ORDER BY `year`");

		$pair = new stdClass();
		while($pair = mysql_fetch_object($articles)){
			$counts[] = $pair->count;
			$years[] = $pair->year;
		}

		/* pChart library inclusions */
		include('../pChart/pData.class.php');
		include('../pChart/pDraw.class.php');
		include('../pChart/pImage.class.php');
		include('../pChart/pCache.class.php');

		/* Create and populate the pData object */
		$myData = new pData();
		$myData->addPoints($counts, 'Articles');
		$myData->setAxisName(0, 'Number of articles');

		$myData->addPoints($years, 'Labels');
		$myData->setSerieDescription('Labels', 'Years');
		$myData->setAbscissa('Labels');

		#$cacheSettings = array ('CacheFolder' => '../pChart/cache');
		#$myCache = new pCache($cacheSettings);
		#$cacheHash = $myCache->getHash($myData);

		#if($myCache->isInCache($cacheHash)){
		#	$myCache->saveFromCache($myCache->getHash($myData), 'cache.png');
		#}else{
			$black = array('R' => 0, 'G' => 0, 'B' => 0);
			$titleFont = array('FontName' => '../pChart/fonts/forgotte.ttf', 'FontSize' => 11);
			$textFont = array('FontName' => '../pChart/fonts/tahoma.ttf', 'FontSize' => 6);
			$scaleSettings = array('XMargin' => 10, 'YMargin' => 10, 'Floating' => TRUE, 'GridR' => 200, 'GridG' => 200, 'GridB' => 200, 'DrawSubTicks' => TRUE, 'CycleBackground' => TRUE);

			$myPicture = new pImage(700, 230, $myData);
			$myPicture->Antialias = FALSE;

			$myPicture->drawRectangle(0, 0, 699, 229, $black);

			$myPicture->setFontProperties($titleFont);
			$myPicture->drawText(150, 35, 'Articles over years', array('FontSize' => 20, 'Align' => TEXT_ALIGN_BOTTOMMIDDLE));

			$myPicture->setFontProperties($textFont);

			$myPicture->setGraphArea(60, 40, 650, 200);

			$myPicture->drawScale($scaleSettings);

			$myPicture->Antialias = TRUE;

			$myPicture->drawLineChart();
			$myPicture->drawLegend(540, 20, array('Style' => LEGEND_NOBORDER, 'Mode' => LEGEND_HORIZONTAL));

			#$myCache->writeToCache($cacheHash, '../pChart/cache/cache.png');

			$myPicture->Stroke();
		#}
	break;
}

require '../__close.php';