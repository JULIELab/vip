/**
 * Javascript for institute administration tasks.
 *
 * Contains all javascript functions to manipulate institution related data.
 * @author Karl-Philipp Wulfert <animungo@gmail.com>
 * @package instituteAdministration
 * @version 1.0
 */

/*
 * Edit the insitute with the data entered in the form.
 */
function editInstitute (id, name, abbreviation, nation, county, city, website, editing_status) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: ({
			deliver: 'editInstitute',
			id: id,
			name: name,
			abbreviation: abbreviation,
			nation: nation,
			county: county,
			city: city,
			website: website,
			editing_status: editing_status
		}),
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text, {
				header: json.title
			});
			if(json.status == 'true'){
				closeActiveTab();
				showInstitutes();
			}
		}
	})
}

/**
 * Get the form to edit the data of an institute.
 */
function getInstituteEditForm (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: ({
			deliver: 'getInstituteEditForm',
			id: id
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#getInstituteEditForm_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				modal: true,
				buttons: {
					'Save': function () {
						editInstitute(id, $('#instituteName_'+id).val(), $('#instituteAbbreviation_'+id).val(), $('#instituteNation_'+id).val(), $('#instituteCounty_'+id).val(), $('#instituteCity_'+id).val(), $('#instituteWebsite_'+id).val(), $('#instituteEditingStatus_'+id).val());
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 600
			});

			checkInstitutesNation(id, $('#instituteNation_'+id).val());
		}
	})
}

/**
 * Helper for institute edit form.
 * Check if the given nation abbreviation references an existing nation.
 */
function checkInstitutesNation (id, abbreviation) {
	if(abbreviation.length == 3){
		setLoading('#instituteFlagContainer_'+id, '../');
		$.ajax({
			url: 'instituteAdministration.deliver.php',
			cache: false,
			data: ({
				deliver: 'checkInstitutesNation',
				id: id,
				abbreviation: abbreviation
			}),
			success: function (html) {
				$('#instituteFlagContainer_'+id).html(html);
			}
		});
	}else
		$('#instituteFlagContainer_'+id).html('Wrong!');
}

/**
 * Helper for institute edit form.
 * Search organizations to pick one as parent of institute.
 */
function searchOrganizations (id, query) {
	if(query.length >= 2){
		setLoading('#instituteSearchResult_'+id, '../');
		$.ajax({
			url: 'instituteAdministration.deliver.php',
			cache: false,
			data: ({
				deliver: 'searchOrganizations',
				id: id,
				query: query
			}),
			success: function (html) {
				$('#instituteSearchResult_'+id).html(html);
			}
		});
	}
}

/**
 * Move an institute between organizations.
 */
function moveInstitute (id, organization) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: ({
			deliver: 'moveInstitute',
			id: id,
			organization: organization
		}),
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text, {
				header: json.title
			});

			if(json.status == 'success'){
				$('#showInstitutesWithEditingStatus').val('all');
				$('#showInstitutesFromOrganization').val(organization);
				showInstitutes();
				closeActiveTab();
			}
		}
	})
}

/**
 * Confirm that you want to move an institute between organizations.
 */
function moveInstituteConfirm (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'moveInstituteConfirm',
			id: id
		},
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#moveInstituteConfirm_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				modal: true,
				buttons: {
					'Move': function () {
						if($('#instituteOrganization_'+id).is('select') && $('#instituteOrganization_'+id).val() != '0' && confirm('Are you sure that you want to do this?')){
							moveInstitute(id, $('#instituteOrganization_'+id).val());
							$(this).dialog('close');
						}else
							$('#instituteSearchResult_'+id).html('<div class="notificationBlock failure">You have not selected a new organization!</div>')
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
 * Grow an institute.
 */
function growInstitute (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'growInstitute',
			id: id
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text, {
				header: json.title
			});
			closeActiveTab();
			showInstitutes();
		}
	});
}

/**
 * Confirm that you want to grow an institute.
 */
function growInstituteConfirm (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'growInstituteConfirm',
			id: id
		},
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#growInstituteConfirm_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				modal: true,
				buttons: {
					'Grow': function () {
						if(confirm('Are you sure that you want to do this?')){
							growInstitute(id);
							$(this).dialog('close');
						}
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
 * Merge two institutes.
 */
function mergeInstitute (id, target) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'mergeInstitute',
			id: id,
			target: target
		},
		dataType: 'json',
		success: function (json) {
			$.jGrowl(json.text, {
				header: json.title
			});
			closeActiveTab();
			showInstitutes();
		}
	})
}

/**
 * Confirm that you want to merge two institutes.
 */
function mergeInstituteConfirm (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'mergeInstituteConfirm',
			id: id
		},
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#mergeInstituteConfirm_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				modal: true,
				buttons: {
					'Merge': function () {
						if($('#mergeInstituteConfirm_secondId_'+id).val() != '0' && confirm('Are you sure that you want to do this?')){
							mergeInstitute(id, $('#mergeInstituteConfirm_secondId_'+id).val());
							$(this).dialog('close');
						}else
							alert('You have to select an institution to merge with.');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 500
			});
		}
	});
}

function deleteInstitute (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'deleteInstitute',
			id: id
		},
		dataType: 'json',
		success:	function (json) {
			$.jGrowl(json.text, {
				header: json.title
			});

			if(json.status == 'true'){
				closeActiveTab();
				showInstitutes();
			}
		}
	})
}

/**
 * Confirm that you want to delete an institute.
 */
function deleteInstituteConfirm (id) {
	$.ajax({
		url: 'instituteAdministration.deliver.php',
		cache: false,
		data: {
			deliver: 'deleteInstituteConfirm',
			id: id
		},
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#deleteInstituteConfirm_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				modal: true,
				buttons: {
					'Delete': function () {
						if(confirm('Are you sure that you want to do this?')){
							deleteInstitute(id);
							$(this).dialog('close');
						}
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 500
			})

			if(parseInt($('#deleteInstituteConfirm_allocations_'+id).html()) > 0){
				$('#deleteInstituteConfirm_'+id).dialog({
					buttons: {
						'Merge': function () {
							mergeInstituteConfirm(id);
							$(this).dialog('close');
						},
						'cancel': function () {
							$(this).dialog('close');
						}
					}
				})
			}
		}
	})
}

/*
 * Get the requested list of institutes.
 */
var results = 0;
function showInstitutes (all) {
	$('#showInstitutes_allConfirm').hide();
	if(all == null)
		all = false;

	if($('#showInstitutesWithEditingStatus').val() == 'all' && $('#showInstitutesFromOrganization').val() == '0' && !all){
		$('#showInstitutes_allConfirm').show('highlight');
		return;
	}

	setLoading('#instituteContainer', '../');
	var status = $('#showInstitutesWithEditingStatus').val();
	var organization = $('#showInstitutesFromOrganization').val();

	if(findSimilarTab({'status': status, 'organization': organization}) == false){
		$.ajax({
			url: 'instituteAdministration.deliver.php',
			cache: false,
			data: ({
				deliver: 'showInstitutes',
				editing_status: status,
				organization: organization
			}),
			success: function (html) {
				addTab(++results, {'status': status, 'organization': organization}, html);
			}
		})
	}
}