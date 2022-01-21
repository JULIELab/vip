<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';
?>

<h3>Maintenance &raquo; Merge authors</h3>
<div class="helptext">
	<strong>You can merge doubled authors on this page.</strong><br /><br />
	<strong>First way</strong><br />
	Firstly select a method to group lastnames. Then select a lastname and put a firstname into merge position 1 via [1].<br />
	Secondly select another author the same way and put him into merge position 2 via [2].<br />
	If you are sure that both authors are actually the same person you can merge them.<br /><br />
	<strong>Second way</strong><br />
	You can also get the list of most likely similar authors. You can position both of them by clicking "position both" in an appropriate row. Or you can select specific authors with the [1] and [2] links.<br />
	If you think that two authors are "unsimilar" you can press the link for that. It just hides the pair for now. So it will reappear next time you refresh the list of similar authors.<br /><br />
	<strong>Tips</strong><br />
	#1 If one of the authors contains specific data (e.g. mail address) put him into merge position one.<br />
	#2 You can switch the positions of two selected authors by clicking on "Switch positions!" on the right side beneath the button "Merge selected authors!".
</div>

<div class="semanticSeparation">
	<div style="display: inline; float: right; text-align: right">
		<button type="button" onclick="mergeAuthors()"><?php echo returnIcon('arrow-join')?> Merge selected authors!</button><br />
		<a href="javascript:;" onclick="switchPositions()" style="display: block; padding: 0.5em"><?php echo returnIcon('arrow-switch')?> Switch positions of authors!</a>
	</div>
	<ul>
		<li><a href="javascript:;" onclick="getNames('grouped');">View names grouped, ordered by count!</a></li>
		<li><a href="javascript:;" onclick="getNames('alphabetically');">View names alphabetically!</a></li>
		<li><a href="javascript:;" onclick="similarAuthors();">Get similar authors!</a> (May take some time...)</li>
	</ul>
</div>

<table id="mergeContainer" class="dataContainer">
	<tr>
		<th style="width: 40%">List</th>
		<th style="width: 30%">Author to merge in [1]</th>
		<th style="width: 30%">Author to be deleted [2]</th>
	</tr>

	<tr>
		<td id="list">
			<div id="firstnames" style="width: 50%; float: right;"></div>
			<div id="names" style="width: 45%; float: left;"></div>
		</td>
		<td id="merge1"></td>
		<td id="merge2"></td>
	</tr>
</table>

<script type="text/javascript" src="doubled_authors.js"></script>
<script type="text/javascript">
	/* <![CDATA[ */
$('#mergeContainer').floatingTableHead();
	/* ]]> */
</script>
<?php
require '../__close.php';
require '../_footer.php';