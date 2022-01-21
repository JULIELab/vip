<?php
require '../__functions.php';
require '../_header.php';
require '../__connect.php';
?>

<h2>List of authors</h2>

<div class="helptext">On this page you can edit data of authors. Simply enter a search query and select the author you want to edit. Than fill the data and hit 'save'!</div>

<label for="authorSearch" class="block">Search for an author</label>
<input type="text" id="authorSearch" name="authorSearch" style="width: 50%" />
<table id="authorContainer" class="dataContainer" style="display: none;"></table>

<script type="text/javascript">
	/* <![CDATA[ */
function editAuthor (author_id, firstname, name, mail) {
	$.ajax({
		'url': 'authorAdministration.deliver.php',
		'data': {
			'task': 'editAuthor',
			'author_id': author_id,
			'firstname': firstname,
			'name': name,
			'mail': mail
		},
		'dataType': 'json',
		'success': function (json) {
			$.jGrowl(json.text);
			searchAuthors($('#authorSearch').val());
		}
	});
}

function getAuthorEditForm (author_id) {
	$.ajax({
		'url': 'authorAdministration.deliver.php',
		'data': {
			'task': 'getAuthorEditForm',
			'author_id': author_id
		},
		'success': function (html) {
			$('#tooltipContainer').append(html);
			$('#authorEditForm_'+author_id).dialog({
				'width': 500,
				'buttons': {
					'save': function () {
						editAuthor(author_id, $('#authorFirstname').val(), $('#authorName').val(), $('#authorMail').val());
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				'modal': true,
				'close': function () {
					$(this).remove();
				}
			});
		}
	});
}

function searchAuthors (query) {
	$.ajax({
		'url': 'authorAdministration.deliver.php',
		'data': {
			'task': 'searchAuthors',
			'query': query
		},
		'dataType': 'json',
		'success': function (json) {
			if(json.length > 0){
				$('#authorContainer').html('<tr><th></th><th>Name</th><th>Mail</th></tr>').show();
				$.each(json, function (dummy, author) {
					$('#authorContainer').append('<tr><td><a href="javascript:;" onclick="getAuthorEditForm('+author.author_id+');"><span class="silk-icon silk-icon-user-edit"></span></a></td><td><strong>'+author.name+'</strong>, '+author.firstname+'</td><td>'+author.mail+'</td></tr>');
				});
			}else
				$('#authorContainer').empty();
		}
	});
}

$(function () {
	$('#authorSearch').bind('keyup mouseup', function () {
		delayRequest('searchAuthors', Array($('#authorSearch').val()));
	});
});
	/* ]]> */
</script>
<?php
require '../__close.php';
require '../_footer.php';