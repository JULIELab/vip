var results = 0;
function showOrganizations () {
	setLoading('#organizationContainer', '../');

	var status = $('#showOrganizationsWithEditingStatus').val();
	var name = $('#showOrganizationsWithName').val();

	if(findSimilarTab({'status': status, 'name': name}) == false){
		$.ajax({
			url: 'organizationAdministration.deliver.php',
			cache: false,
			data: ({
				deliver: 'showOrganizations',
				name: name,
				editing_status: status
			}),
			success: function (html) {
				addTab(++results, {'status': status, 'name': name}, html);
			}
		})
	}
}

function getOrganizationEditForm (id) {
	$.ajax({
		url: 'organizationAdministration.deliver.php',
		cache: false,
		data: ({
			deliver: 'getEditForm',
			id: id
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#organizationEditForm_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				buttons: {
					'save': function () {
						editOrganization(id, $('#organizationName_'+id).val(), $('#organizationAbbreviation_'+id).val(), $('#organizationNation_'+id).val(), $('#organizationCounty_'+id).val(), $('#organizationCity_'+id).val(), $('#organizationWebsite_'+id).val(), $('#organizationEditingStatus_'+id).val());
						$(this).dialog('close');
					},
					'cancel': function () {
						$(this).dialog('close');
					}
				},
				width: 600
			});

			checkOrganizationsNation(id, $('#organizationNation_'+id).val());
		}
	})
}

function editOrganization (id, name, abbreviation, nation, county, city, website, editing_status) {
	$.ajax({
		url: 'organizationAdministration.deliver.php',
		cache: false,
		data: ({
			deliver: 'editOrganization',
			id: id,
			name: name,
			abbreviation: abbreviation,
			nation: nation,
			county: county,
			city: city,
			website: website,
			editing_status: editing_status
		}),
		success: function (html) {
			$('#tooltipContainer').append(html);
			$('#organizationEditingStatus_'+id).dialog({
				close: function () {
					$(this).remove();
				},
				buttons: {
					'Ok': function () {
						$(this).dialog('close');
					}
				},
				width: 400
			});
			showOrganizations();
		}
	})
}

function checkOrganizationsNation (id, abbreviation) {
	if(abbreviation.length == 3){
		setLoading('#organizationFlagContainer_'+id, '../');
		$.ajax({
			url: 'organizationAdministration.deliver.php',
			cache: false,
			data: ({
				deliver: 'checkOrganizationsNation',
				id: id,
				abbreviation: abbreviation
			}),
			success: function (html) {
				$('#organizationFlagContainer_'+id).html(html);
			}
		});
	}else
		$('#organizationFlagContainer_'+id).html('');
}