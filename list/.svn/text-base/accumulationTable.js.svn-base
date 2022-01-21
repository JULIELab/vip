function getArticles (conference, year) {
	$.ajax({
		url: 'accumulationTable.deliver.php',
		cache: false,
		data: ({
			deliver: 'getArticles',
			conference: conference,
			year: year
		}),
		success: function (html) {
			$('#articleContainer').html(html);
			window.location = '#result';
		}
	})
}