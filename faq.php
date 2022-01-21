<?php
/**
 * Shows the faq entries publicly.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package faq
 * @version 1.0
 */

require '__functions.php';
require '_header.php';
require '__connect.php';
?>

<h2>Frequently Asked Questions <span class="annotation">FAQ</span></h2>

<?php
$faq = mysql_query("SELECT * FROM `_faqContainer` WHERE `faqPublic` = 1 ORDER BY `faqId` ASC");
$faqEntry = new stdClass();
if(mysql_num_rows($faq) > 0){
?>

<div class="contentOverview">
<?php
	while($faqEntry = mysql_fetch_object($faq)){
?>

	<a href="#faqEntry_<?php echo $faqEntry->faqId?>" id="faqEntry_<?php echo $faqEntry->faqId?>">#</a>
	<strong><?php echo $faqEntry->faqQuestion?></strong><br />
<?php
	}
?>

</div>

<?php

	$faq = mysql_query("SELECT * FROM `_faqContainer` WHERE `faqPublic` = 1 ORDER BY `faqId` ASC");
	while($faqEntry = mysql_fetch_object($faq)){
?>

	<h3><a href="#faqEntry_<?php echo $faqEntry->faqId?>" id="faqEntry_<?php echo $faqEntry->faqId?>">#</a> <?php echo $faqEntry->faqQuestion?></h3>
	<p><?php echo $faqEntry->faqAnswer?></p>

<?php
	}
}else
	echo '<h2 class="failure">No FAQ entries in database... Sorry!</h2>';

require '__close.php';
require '_footer.php';