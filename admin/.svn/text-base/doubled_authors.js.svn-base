var lastListingOfNames = '';
var lastSelectedName = '';
var lastAuthorOnMergePositionOne = '';
var pair = 0;

function similarAuthors () {
	setLoading('#list', '../');

	lastListingOfNames = 'similar';

	$.ajax({
		url: 'doubled_authors.deliver.php',
		cache: false,
		data: ({
			deliver: 'similarAuthors'
		}),
		success: function (html) {
			$('#list').html(html);
		}
	})
}

function unsimilarAuthors (one, two, id) {
	$.ajax({
		url: 'doubled_authors.deliver.php',
		cache: false,
		data: {
			deliver: 'unsimilarAuthors',
			one: one,
			two: two
		},
		success: function (html) {
			$('#pair_'+id).hide('slow');
		}
	})
}

function switchPositions () {
	if($('#mergeId_1').val() != null && $('#mergeId_2').val() != null){
		var dummy = $('#mergeId_1').val();
		positionAuthor($('#mergeId_2').val(), 1);
		positionAuthor(dummy, 2);
	}else
		alert('Two authors have to be selected, to switch positions.');
}

function getNames (type) {
	$('#list').html('<div id="firstnames" style="width: 50%; float: right;"></div><div id="names" style="width: 45%; float: left;"></div>');

	setLoading('#names', '../');

	lastListingOfNames = type;

	$.ajax({
		url: 'doubled_authors.deliver.php',
		cache: false,
		data: ({
			deliver: 'getAuthors',
			type: type
		}),
		success: function (html) {
			$('#names').html(html);
		}
	})
}

function getFirstnames (name) {
	setLoading('#firstnames', '../');

	lastSelectedName = name;
	
	$.ajax({
		url: 'doubled_authors.deliver.php',
		cache: false,
		data: ({
			deliver: 'getFirstnames',
			name: name
		}),
		success: function (html) {
			$('#firstnames').html(html);
		}
	})
}

function positionAuthor (id, position) {
	setLoading('#merge'+position, '../');

	if(position == 1)
		lastAuthorOnMergePositionOne = id;

	$.ajax({
		url: 'doubled_authors.deliver.php',
		cache: false,
		data: ({
			deliver: 'positionAuthor',
			id: id,
			position: position
		}),
		success: function (html) {
			$('#merge'+position).html(html);
		}
	})
}

function mergeAuthors () {
	author1 = $('#mergeId_1').val();
	author2 = $('#mergeId_2').val();
	
	if(author1 != null && author2 != null){
		if(author1 != author2){
			if(confirm('Do you really want to merge these authors? This step can _NOT_ be undone!')){
				setLoading('#merge2', '../');
				
				$.ajax({
					url: 'doubled_authors.deliver.php',
					cache: false,
					data: ({
						deliver: 'mergeAuthors',
						author1: author1,
						author2: author2
					}),
					success: function (html) {
						positionAuthor(lastAuthorOnMergePositionOne, 1);
						$('#merge2').html(html);

						if(lastListingOfNames == 'similar')
							$('#pair_'+pair).hide('slow');
						else
							getNames(lastListingOfNames);

						getFirstnames(lastSelectedName);
					}
				});
			}
		}else
			alert('You have to select two different authors!');
	}else
		alert('You have to select two authors that you want merged!');
}