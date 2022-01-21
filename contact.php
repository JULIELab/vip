<?php
/**
 * Simple wrapper to display the contact page.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package template
 * @subpackage dynamicPages
 * @version 1.0
 */

require '__functions.php';
require '_header.php';
require '__connect.php';

$resource = getTextResource('page_contact');
echo $resource->textContent;

require '__close.php';
require '_footer.php';