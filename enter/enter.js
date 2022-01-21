////////////////////////////////////////////////////////////
////////// INITIALIZATION //////////////////////////////////
////////////////////////////////////////////////////////////

var globFormStatus = false;		// form can't be posted
var globFieldCache = Array();		// array to prevent unnecessary queries if searchphrase hasn't changed
var authors = Array();				// contains suggested authors, that have been parsed from a BibTex file
var organizationAmount = 0;		//


////////////////////////////////////////////////////////////
////////// FUNCTIONS TO CHECK ARTICLE DATA /////////////////
////////////////////////////////////////////////////////////


function checkAnthologiesBibTex (i, stop) {
	if(i == null)
		i = 0;

	if(stop == null)
		stop = bibtexFiles.length - 1;

	if(i > stop)
		return;

	setLoading('#bibtex_'+md5Hashes[i]+'_result', '../');

	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'checkAnthologiesBibTex',
			url: bibtexFiles[i],
			conference: $('#checkAnthologiesExistenceConference').val()
		}),
		success: function (html) {
			$('#bibtex_'+md5Hashes[i]+'_result').html(html);
		}
	});

	checkAnthologiesBibTex(i + 1, stop);
}

function checkAnthologiesExistence () {
	setLoading('#checkAnthologiesExistence', '../');

	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'checkAnthologiesExistence',
			url: $('#checkAnthologiesExistenceURL').val()
		}),
		success: function (html) {
			$('#checkAnthologiesExistence').html(html);
		}
	})
}

/**
 * Check if the form can be submited.
 */
function checkSubmittingStatus () {
	if(globFormStatus == false)
		alert('Sorry, something is wrong here. Either you didn\'t fill sufficient data or the article is yet in the database. Please double check and send again!');

	return globFormStatus;
}

/**
 * Triggers the parsing for linked BibTex files on an anthology page.
 */
function checkAnthology () {
	if($('#anthology').val() != ''){
		setLoading('#checkAnthology', '../');

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: {
				deliver: 'checkAnthology',
				url: $('#anthology').val()
			},
			success: function(html) {
				$('#checkAnthology').html(html);
			}
		});
	}else
		$('#anthology').val('Please enter an URL here!').focus();
}

/**
 * Triggers the parsing of necessary information from a BibTex file.
 */
function checkBibTex () {
	if($('#bibtex').val() != ''){
		setLoading('#checkBibTex', '../');

		$.ajax({
			url: 'deliver.php',
			data: {
				deliver: 'checkBibTex',
				url: $('#bibtex').val()
			},
			cache: false,
			success: function(html) {
				$('#checkBibTex').html(html);

				// call several functions, to check wether the parsed information is sufficient
				checkPDF();
				checkConference();
				checkArticlesExistence(false);
			}
		});
	}else
		$('#bibtex').val('You did not enter an URL.').focus();
}

/**
 * Triggers the check wether a conference does exist or not.
 */
function checkConference () {
	if($('#conference').val() != ''){
		setLoading('#checkConference', '../');

		$.ajax({
			url: 'deliver.php',
			data: {
				deliver: 'checkConference',
				conference: $('#conference').val(),
				year: $('#year').val()
			},
			cache: false,
			success: function(html) {
				$('#checkConference').html(html);
			}
		});
	}else
		$('#checkConference').html('You did not enter anything.');
}

/**
 * Triggers the check wether a given URL appears to be a PDF.
 */
function checkPDF () {
	if($('#url').val() != ''){
		setLoading('#checkPDF', '../');

		$.ajax({
			url: 'deliver.php',
			data: {
				deliver: 'checkPDF',
				url: $('#url').val()
			},
			cache: false,
			success: function(html) {
				$('#checkPDF').html(html);
			}
		});
	}else
		$('#checkPDF').html('You did not enter URL.');
}

/**
 * Triggers the check if an article is yet in the database or not.
 */
function checkArticlesExistence (clicked) {
	if(clicked == null)
		clicked = false;

	var year = $('#year').val();
	var conference = $('#conference').val();
	var articlenumber = $('#articlenumber').val();

	if(year != '' && conference != '' && articlenumber != ''){
		setLoading('#checkArticlesExistence', '../');

		$.ajax({
			url: 'deliver.php',
			data: ({
				deliver: 'checkArticlesExistence',
				year: year,
				conference: conference,
				articlenumber: articlenumber
			}),
			cache: false,
			success: function(answer) {
				if(answer == 'false'){
					$('#checkArticlesExistence').html('<strong class="failure">This proceeding is yet in the database.</strong> <a href="javascript:;" onclick="checkArticlesExistence(true)">Check again.</a>');
					globFormStatus = false;
				}else if(answer == 'true'){
					$('#checkArticlesExistence').html('<strong class="success">This proceeding is not yet in the database.</strong> <input id="submitForm" type="submit" value="save" />');
					$('#submitForm').button();
					globFormStatus = true;
				}
			}
		});
	}else{
		if(clicked == true)
			alert('You did not enter sufficient data. You have to select a year and fill conference and articlenumber!');
	}
}

////////////////////////////////////////////////////////////
////////// AUTHOR RELATED FUNCTIONS ////////////////////////
////////////////////////////////////////////////////////////

/**
 * Create an author.
 */
function createAuthor (firstname, name, mail, no, id) {
	if(no == null)
		no = 0;
	if(id == null)
		id = 0;

	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'createAuthor',
			firstname: firstname,
			name: name,
			mail: mail
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#authorCreateStatus').dialog({
				resizable: false,
				close: function () {
					$(this).remove();
				},
				buttons: {
					'Close': function () {
						$(this).dialog('close');
					}
				},
				width: 400
			});
			searchAuthors(no, id, name+', '+firstname);
		}
	})
}

/**
 * Get the form to create an author.
 */
function getAuthorCreateForm (no, id) {
	if(no == null)
		no = 0;
	if(id == null)
		id = 0;

	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'getAuthorCreateForm'
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#authorCreateForm').dialog({
				resizable: false,
				close: function () {
					$(this).remove();
				},
				buttons: {
					'create': function () {
						createAuthor($('#authorFirstname').val(), $('#authorName').val(), $('#authorMail').val(), no, id);
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 400
			});
		}
	})
}


/**
 * Search in the database for authors.
 */
function searchAuthors(no, id, query) {
	if(query.length >= 2){
		setLoading('#chooseAuthor_'+no+'_'+id, '../');

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'searchAuthors',
				no: no,
				id: id,
				query: query
			}),
			success: function (html) {
				$('#chooseAuthor_'+no+'_'+id).html(html);
			}
		})
	}
}

/**
 * Get a specific amount of fields for authors.
 */
function getFieldsForAuthors (no, amount) {
	if(authors.length == 0)
		authors = '';

	setLoading('#authorFieldsContainer_'+no, '../');

	$.ajax({
		url: 'deliver.php',
		data: ({
			deliver: 'getFieldsForAuthors',
			no: no,
			amount: amount,
			authors: authors
		}),
		dataType: 'html',
		cache: false,
		success: function(html) {
			$('#authorFieldsContainer_'+no).html(html);
		}
	});
}


/**
 * Check if the nation abbreviation is really a nation .
 */
function checkAuthorsNation (id, abbreviation) {
	if(abbreviation.length == 3){
		setLoading('#authorFlagContainer_'+id, '../');

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'checkAuthorsNation',
				id: id,
				abbreviation: abbreviation
			}),
			success: function (html) {
				$('#authorFlagContainer_'+id).html(html);
			}
		})

	}else
		$('#authorFlagContainer_'+id).empty();
}





////////////////////////////////////////////////////////////
////////// ORGANIZATION RELATED FUNCTIONS //////////////////
////////////////////////////////////////////////////////////

/**
 * Get a specific amount of blocks of fields for organizations.
 */
function getBlocks (amount) {
	amount = parseInt(amount);

	if(amount < 0){
		$('#organizationAmount').val('0');
		return;
	}

	var toAdd = amount - organizationAmount;

	if(toAdd > 0){
		$.ajax({
			url: 'deliver.php',
			data: {
				deliver: 'getBlocks',
				start: (amount - toAdd + 1),
				end: (amount)
			},
			cache: false,
			success: function(html) {
				$('#blockContainer').append(html);
			}
		});
	}

	if(toAdd < 0){
		if(confirm('This would remove the fields of the last '+(0 - toAdd)+' organization(s) from the form.\nAll information you have entered there will be lost!\nThe fields of '+(organizationAmount + toAdd)+' organization(s) will remain in the form.')){
			for(var i = organizationAmount; i > amount; i--)
				$('#organizationBlock_'+i).remove();
		}else{
			amount = organizationAmount;
			$('#organizationAmount').val(amount);
		}
	}

	organizationAmount = amount;
}


function createOrganization (name, abbreviation, nation, county, city, website) {
	if(name != '' && nation != ''){
		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'createOrganization',
				name: name,
				abbreviation: abbreviation,
				nation: nation,
				county: county,
				city: city,
				website: website
			}),
			success: function (html) {
				$('#tooltipContainer').append(html);
				$('#organizationCreateStatus').dialog({
					resizable: false,
					close: function () {
						$(this).remove();
					},
					buttons: {
						'Close': function () {
							$(this).dialog('close');
						}
					},
					width: 600
				})
			}
		})
	}else
		alert('Please fill at least the fields `name` and `nation`!');
}

/**
 * Check wether the entered abbreviation represents a nation.
 */
function checkOrganizationsNation (abbreviation) {
	if(abbreviation.length == 3){
		setLoading('#organizationFlagContainer', '../');

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'checkNation',
				abbreviation: abbreviation
			}),
			success: function (html) {
				$('#organizationFlagContainer').html(html);
			}
		})
	}else
		$('#organizationFlagContainer').empty();
}


function getOrganizationCreateForm () {
	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'getOrganizationCreateForm'
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#organizationCreateForm').dialog({
				resizable: false,
				close: function () {
					$(this).remove();
				},
				buttons: {
					'create': function () {
						createOrganization($('#organizationName').val(), $('#organizationAbbreviation').val(), $('#organizationNation').val(), $('#organizationCounty').val(), $('#organizationCity').val(), $('#organizationWebsite').val());
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 600
			});
		}
	})
}

/**
 * Search database for a query to select organization.
 */
function searchOrganizations (no, query) {
	if(query.length >= 2 && globFieldCache['organizationContainer_'+no] != query){
		globFieldCache['organizationContainer_'+no] = query;

		setLoading('#organizationContainer_'+no, '../');
		$('#instituteContainer_'+no).empty();

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'searchOrganizations',
				no: no,
				query: query
			}),
			success: function (html) {
				$('#organizationContainer_'+no).html(html);
				if($('#organization_'+no).is('select'))
					getInstitutes(no, $('#organization_'+no).val());
			}
		});
	}
}





////////////////////////////////////////////////////////////
////////// INSTITUT RELATED FUNCTIONS //////////////////////
////////////////////////////////////////////////////////////

/**
 * Search organizations to create an institute.
 */
function searchOrganizationsForInstituteCreation (query) {
	if(query.length >= 2 && query != globFieldCache['instituteOrganizationContainer']){
		globFieldCache['instituteOrganizationContainer'] = query;

		setLoading('#instituteOrganizationContainer', '../');

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'searchOrganizationsForInstituteCreation',
				query: query
			}),
			success: function (html) {
				$('#instituteOrganizationContainer').html(html);
			}
		});
	}
}

/**
 * Check the nation of an institute that shall be created.
 */
function checkInstitutesNation (abbreviation) {
	if(abbreviation.length == 3){
		setLoading('#instituteFlagContainer', '../');

		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'checkNation',
				abbreviation: abbreviation
			}),
			success: function (html) {
				$('#instituteFlagContainer').html(html);
			}
		})
	}else
		$('#instituteFlagContainer').empty();
}

/**
 * Get institutes of a specific organization.
 */
function getInstitutes (no, organizationId) {
	setLoading('#instituteContainer_'+no, '../');

	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'getInstitutes',
			no: no,
			organization: organizationId
		}),
		success: function (html) {
			$('#instituteContainer_'+no).html(html);
		}
	});
}

/**
 * Create an institute.
 */
function createInstitute (name, abbreviation, nation, county, city, website, organization) {
	if(name != '' && nation != '' && organization != 0 && organization != null){
		$.ajax({
			url: 'deliver.php',
			cache: false,
			data: ({
				deliver: 'createInstitute',
				name: name,
				abbreviation: abbreviation,
				nation: nation,
				county: county,
				city: city,
				website: website,
				organization: organization
			}),
			success: function (html) {
				$('#tooltipContainer').append(html);
				$('#instituteCreateStatus').dialog({
					resizable: false,
					close: function () {
						$(this).remove();
					},
					buttons: {
						'Close': function () {
							$(this).dialog('close');
						}
					},
					width: 600
				});
			}
		})
	}else
		alert('Please fill at least the fields `name` and `nation` and select an associated organization!');
}

/**
 * Get create form for an institute.
 */
function getInstituteCreateForm () {
	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'getInstituteCreateForm'
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#instituteCreateForm').dialog({
				resizable: false,
				close: function () {
					$(this).remove();
				},
				buttons: {
					'create': function () {
						createInstitute($('#instituteName').val(), $('#instituteAbbreviation').val(), $('#instituteNation').val(), $('#instituteCounty').val(), $('#instituteCity').val(), $('#instituteWebsite').val(), $('#instituteOrganization').val());
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 600
			});
		}
	})
}

/**
 * Create an author.
 */
function getNationList () {
	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: ({
			deliver: 'getNationList'
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#nationList').dialog({
				resizable: false,
				close: function () {
					$(this).remove();
				},
				buttons: {
					'Close': function () {
						$(this).dialog('close');
					}
				},
				width: 400
			});
		}
	})
}

function updateArticle (id, link) {
	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: {
			deliver: 'updateArticle',
			proceedingId: id,
			bibtex: link
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text, {
				header: json.title
			});

			if(json.further != '')
				$.jGrowl(json.further);

			checkBibTex();
			checkAnthology();
		}
	})
}

function updateArticleConfirm (id, link) {
	$.ajax({
		url: 'deliver.php',
		cache: false,
		data: {
			deliver: 'updateArticleConfirm',
			proceedingId: id,
			bibtex: link
		},
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#updateArticleConfirm_'+id).dialog({
				resizable: false,
				modal: true,
				close: function () {
					$(this).remove();
				},
				buttons: {
					'close': function () {
						$(this).dialog('close');
					}
				},
				width: 500
			});

			if($('#update_bibtex').is(':input') && $('#update_proceedingId').is(':input')){
				$('#updateArticleConfirm_'+id).dialog({
					buttons: {
						'Update!': function () {
							updateArticle(id, link);
							$(this).dialog('close');
						},
						'close': function () {
							$(this).dialog('close');
						}
					}
				});
			}
		}
	})
}