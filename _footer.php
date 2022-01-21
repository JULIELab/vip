<?php
/**
 * This file contains the footer of the template.
 *
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package template
 * @version 1.0
 */
?>
			</div>
			<div id="footer">
				<a href="<?php echo PATH?>/contact.php">
					Privacy <?php echo returnIcon('user-gray');?>,
					Disclaimer &amp;
					Contact <?php echo returnIcon('email');?>
				</a>

				| <a href="http://validator.w3.org/unicorn/">
					Validity
					<?php echo returnIcon('xhtml-valid');?>
					<?php echo returnIcon('css-valid');?>
				</a>

				| <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">
					License
					<img alt="Creative Commons Lizenzvertrag" style="border-width:0" src="<?php echo PATH?>/_resources/images/cc.png" width="80" height="15" />
				</a>
			</div>
		</div>
		<div id="jQueryLoading"><img src="<?php echo PATH?>/_resources/images/pending.gif" alt="loading" width="16" height="11" />&nbsp;Actions pending <span id="jQueryLoadingAmount"></span></div>
		<div id="tooltipContainer"></div>
		<script type="text/javascript">
			/* <![CDATA[ */
var jQueryLoading = 0;

$('#jQueryLoading').bind('ajaxSend', function() {
	$('body').css('cursor', 'wait');
	if(jQueryLoading == 0)
		$(this).show();
	jQueryLoading++;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');
}).bind('ajaxComplete', function(){
	$('body').css('cursor', 'auto');
	jQueryLoading--;
	$('#jQueryLoadingAmount').html('('+jQueryLoading+')');

	if(jQueryLoading == 0)
		$(this).hide('fade');
});

$('.helptext').hide();
if($('.helptext').length == 0)
	$('#helptextToggler').hide();

$('#jQueryLoading').hide('slow');
$('#maintenanceMenuNavigation').hide();

$.jGrowl.defaults.position = 'bottom-right';
$.jGrowl.defaults.life = 10000;
			/* ]]> */
		</script>
	</body>
</html>