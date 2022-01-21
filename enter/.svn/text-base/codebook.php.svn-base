<?php
/**
 * Simple wrapper to show the codebook on the page.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package annotation
 * @version 1.0
 */

require '../__functions.php';
require '../_header.php';
require '../__connect.php';

echo '<div class="subNav">If you have something to add you can <a href="'.PATH.'/admin/textResources.php?task=editor&amp;id=enter_codebook">'.returnIcon('pencil').' edit</a> the codebook.</div>';
$text = getTextResource('enter_codebook');
echo $text->textContent;

require '../__close.php';
require '../_footer.php';