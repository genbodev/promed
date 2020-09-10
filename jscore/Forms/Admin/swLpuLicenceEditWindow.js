/**
* swLpuLicenceEditWindow - окно редактирования/добавления лицензий МО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @version      05.10.2011
*/

sw.Promed.swLpuLicenceEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	//autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	resizable: true,
	closeAction: 'hide',
	draggable: true,
	setAddress: function(data) {
		var current_window = this;
		var country_id = data.KLCountry_id;
		var region_id = data.KLRgn_id ? data.KLRgn_id : '';
		var subregion_id = data.KLSubRGN_id ? data.KLSubRGN_id : '';
		var city_id = data.KLCity_id ? data.KLCity_id : '';
		var town_id = data.KLTown_id ? data.KLTown_id : '';
		var klarea_pid = 0;
		var level = 0;

		current_window.clearAddressCombo(current_window.findById('llef_CountryCombo').areaLevel);

		if (country_id != null) {
			current_window.findById('llef_CountryCombo').setValue(country_id);
		} else {
			return false;
		}

		current_window.findById('llef_RegionCombo').getStore().load({
			callback: function() {
				current_window.findById('llef_RegionCombo').setValue(region_id);
			},
			params: {
				country_id: country_id,
				level: 1,
				value: 0
			}
		});

		if (region_id.toString().length > 0) {
			klarea_pid = region_id;
			level = 1;
		}

		current_window.findById('llef_SubRegionCombo').getStore().load({
			callback: function() {
				current_window.findById('llef_SubRegionCombo').setValue(subregion_id);
			},
			params: {
				country_id: 0,
				level: 2,
				value: klarea_pid
			}
		});

		if (subregion_id.toString().length > 0) {
			klarea_pid = subregion_id;
			level = 2;
		}

		current_window.findById('llef_CityCombo').getStore().load({
			callback: function() {
				current_window.findById('llef_CityCombo').setValue(city_id);
			},
			params: {
				country_id: 0,
				level: 3,
				value: klarea_pid
			}
		});

		if (city_id.toString().length > 0) {
			klarea_pid = city_id;
			level = 3;
		}

		current_window.findById('llef_TownCombo').getStore().load({
			callback: function() {
				current_window.findById('llef_TownCombo').setValue(town_id);
			},
			params: {
				country_id: 0,
				level: 4,
				value: klarea_pid
			}
		});
	},
	loadAddressCombo: function(level, country_id, value, recursion) {
		var current_window = this;
		var target_combo = null;

		switch (level) {
			case 0:
				target_combo = current_window.findById('llef_RegionCombo');
				break;
			case 1:
				target_combo = current_window.findById('llef_SubRegionCombo');
				break;
			case 2:
				target_combo = current_window.findById('llef_CityCombo');
				break;
			case 3:
				target_combo = current_window.findById('llef_TownCombo');
				break;
			default:
				return false;
				break;
		}

		target_combo.clearValue();
		target_combo.getStore().removeAll();
		target_combo.getStore().load({
			params: {
				country_id: country_id,
				level: level + 1,
				value: value
			},
			callback: function(store, records, options) {
				if (level >= 0 && level <= 3 && recursion == true) {
					current_window.loadAddressCombo(level + 1, country_id, value, recursion);
				}
			}
		});
	},
	clearAddressCombo: function(level) {
		var current_window = this;

		var country_combo = current_window.findById('llef_CountryCombo');
		var region_combo = current_window.findById('llef_RegionCombo');
		var subregion_combo = current_window.findById('llef_SubRegionCombo');
		var city_combo = current_window.findById('llef_CityCombo');
		var town_combo = current_window.findById('llef_TownCombo');
		//var street_combo = current_window.findById('llef_StreetCombo');

		var klarea_pid = 0;

		switch (level) {
			case 0:
				country_combo.clearValue();
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				//street_combo.clearValue();
				region_combo.getStore().removeAll();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				//street_combo.getStore().removeAll();
				break;
			case 1:
				region_combo.clearValue();
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				//street_combo.clearValue();
				subregion_combo.getStore().removeAll();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				//street_combo.getStore().removeAll();
				break;
			case 2:
				subregion_combo.clearValue();
				city_combo.clearValue();
				town_combo.clearValue();
				//street_combo.clearValue();
				city_combo.getStore().removeAll();
				town_combo.getStore().removeAll();
				//street_combo.getStore().removeAll();
				if (region_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				}
				current_window.loadAddressCombo(level, 0, klarea_pid, true);
				break;
			case 3:
				city_combo.clearValue();
				town_combo.clearValue();
				//street_combo.clearValue();
				town_combo.getStore().removeAll();
				//street_combo.getStore().removeAll();
				if (subregion_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				} else if (region_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				}
				current_window.loadAddressCombo(level, 0, klarea_pid, true);
				break;

			case 4:
				town_combo.clearValue();
				//street_combo.clearValue();
				//street_combo.getStore().removeAll();
				if (city_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				} else if (subregion_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				} else if (region_combo.getValue() != null) {
					klarea_pid = region_combo.getValue();
				}
				current_window.loadAddressCombo(level, 0, klarea_pid, true);
				break;
		}
	},
	split: true,
	width: 600,
	height: 540,
	layout: 'form',
	id: 'LpuLicenceEditWindow',
	listeners:
	{
		hide: function()
		{
			this.onHide();
		}
	},
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	//resizable: false,
	doSave: function() {

		this.formStatus = 'save';
		var current_window = this;
		var form = this.LpuLicenceEditForm;
		var base_form = form.getForm();

		if (!base_form.isValid())
		{
			sw.swMsg.show( {
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begDate = base_form.findField('LpuLicence_begDate').getValue();
		var endDate = base_form.findField('LpuLicence_endDate').getValue();

		if ((begDate) && (endDate) && (begDate>endDate)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('LpuLicence_begDate').focus(false)
				},
				icon: Ext.Msg.ERROR,
				msg: lang['data_okonchaniya_ne_mojet_byit_menshe_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		if (base_form.findField('KLAreaStat_id').disabled){
			params.KLAreaStat_id = base_form.findField('KLAreaStat_id').getValue();
		}

		if (base_form.findField('KLCountry_id').disabled){
			params.KLCountry_id = base_form.findField('KLCountry_id').getValue();
		}

		if (base_form.findField('KLRgn_id').disabled){
			params.KLRgn_id = base_form.findField('KLRgn_id').getValue();
		}

		if (base_form.findField('KLSubRgn_id').disabled){
			params.KLSubRgn_id = base_form.findField('KLSubRgn_id').getValue();
		}

		if (base_form.findField('KLCity_id').disabled){
			params.KLCity_id = base_form.findField('KLCity_id').getValue();
		}

		if (base_form.findField('KLTown_id').disabled){
			params.KLTown_id = base_form.findField('KLTown_id').getValue();
		}

		// Собираем данные из грида видов лицензии
		var LpuLicenceProfileGrid = this.findById('LLEW_LpuLicenceProfileGrid').getGrid();
		LpuLicenceProfileGrid.getStore().clearFilter();

		if ( LpuLicenceProfileGrid.getStore().getCount() > 0 ) {
			var LpuLicenceProfileData = getStoreRecords(LpuLicenceProfileGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'LpuLicenceProfileType_Name'
				]
			});

			params.LpuLicenceProfileData = Ext.util.JSON.encode(LpuLicenceProfileData);
		}

		// Собираем данные из грида операций с лицензиями
		var LpuLicenceOperationLinkGrid = this.findById('LLEW_LpuLicenceOperationLinkGrid').getGrid();
		LpuLicenceOperationLinkGrid.getStore().clearFilter();

		if ( LpuLicenceOperationLinkGrid.getStore().getCount() > 0  ) {
			var LpuLicenceOperationLinkData = getStoreRecords(LpuLicenceOperationLinkGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'LicsOperation_Name'
				]
			});

			params.LpuLicenceOperationLinkData = Ext.util.JSON.encode(LpuLicenceOperationLinkData);
		}

		// Собираем данные из грида профилей лицензий
		var LpuLicenceLinkGrid = this.findById('LLEW_LpuLicenceLinkGrid').getGrid();
		LpuLicenceLinkGrid.getStore().clearFilter();

		if ( LpuLicenceLinkGrid.getStore().getCount() > 0  ) {
			var LpuLicenceLinkData = getStoreRecords(LpuLicenceLinkGrid.getStore(), {
				convertDateFields: true,
				exceptionFields: [
					'LpuSectionProfile_Code', 'LpuSectionProfile_Name'
				]
			});

			params.LpuLicenceLinkData = Ext.util.JSON.encode(LpuLicenceLinkData);
		}

		// Собираем данные из грида приложений к лицензиям для Казахстана
		if (getRegionNick() == 'kz') {
			var LpuLicenceDopGrid = this.findById('LLEW_LpuLicenceDopGrid').getGrid();
			LpuLicenceDopGrid.getStore().clearFilter();

			if ( LpuLicenceDopGrid.getStore().getCount() > 0  ) {
				var LpuLicenceDopData = getStoreRecords(LpuLicenceDopGrid.getStore(), {
					convertDateFields: true
				});

				params.LpuLicenceDopData = Ext.util.JSON.encode(LpuLicenceDopData);
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение услуги..." });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				current_window.formStatus = 'edit';
				loadMask.hide();
				current_window.hide();
				Ext.getCmp('LpuPassportEditWindow').findById('LPEW_LpuLicenceGrid').loadData();

			}
		});
	},
	profileGridRecordDelete: function() {
		var wnd = this;

		if ( this.action == 'view' ) {
			return false;
		}

		 sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = this.findById('LLEW_LpuLicenceProfileGrid').getGrid();
					var idField = 'LpuLicenceProfile_id';

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params.LpuLicenceProfile_id = record.get('LpuLicenceProfile_id');
					var url = "/?c=LpuPassport&m=deleteLpuLicenceProfile";
					var index = 0;
					if (record.get('LpuLicenceProfile_id') > 0) {
						index = 1;
					}

					switch (index) {
						case 0:
							grid.getStore().remove(record);
							break;
						case 1:
							if (!Ext.isEmpty(url)) {
								Ext.Ajax.request({
									callback: function(opt, scs, response) {
										if (scs) {
											grid.getStore().remove(record);
										}
									}.createDelegate(this),
									params: params,
									url: url
								});
							}
							//grid.getStore().remove(record);
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_dannyiy_vid_litsenzii'],
			title: lang['vopros']
		});
	},
	openLpuLicenceProfileEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swLpuLicenceProfileEditWindow').isVisible() ) {
			sw.swMsg.alert('Ошибка', 'Окно редактирования «Вид лицензии по профилю» уже открыто.');
			return false;
		}

		//var base_form = this.MainPanel.getForm();
		var deniedProfileTypeList = new Array();
		var formParams = new Object();
		var grid = this.findById('LLEW_LpuLicenceProfileGrid').getGrid();
		var params = new Object();
		params.LpuLicence_id = this.LpuLicence_id;
		var selectedRecord;

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuLicenceProfile_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {

			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceProfile_id') ) {
					deniedProfileTypeList.push(rec.get('LpuLicenceProfileType_id'));
				}
			});

		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceProfile_id') && selectedRecord.get('LpuLicenceProfile_id') != rec.get('LpuLicenceProfile_id') ) {
					deniedProfileTypeList.push(rec.get('LpuLicenceProfileType_id'));
				}
			});


			formParams = selectedRecord.data;
			params.LpuLicenceProfile_id = grid.getSelectionModel().getSelected().get('LpuLicenceProfile_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};

			if (params.LpuLicenceProfile_id < 0) {
				//params.LpuLicenceProfile_id = grid.getSelectionModel().getSelected().get('LpuLicenceProfile_id');
				params.LpuLicenceProfileType_Code = grid.getSelectionModel().getSelected().get('LpuLicenceProfileType_Code');
			}

		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuLicenceProfileData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.LpuLicenceProfileData.LpuLicenceProfile_id);

			if ( record ) {

				var grid_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.LpuLicenceProfileData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuLicenceProfile_id') ) {
					grid.getStore().removeAll();
				}

				data.LpuLicenceProfileData.LpuLicenceProfile_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.LpuLicenceProfileData ], true);
			}
		}.createDelegate(this);
		params.deniedProfileTypeList = deniedProfileTypeList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swLpuLicenceProfileEditWindow').show(params);
	},
	operationGridRecordDelete: function() {
		var wnd = this;

		if ( this.action == 'view' ) {
			return false;
		}

		 sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = this.findById('LLEW_LpuLicenceOperationLinkGrid').getGrid();
					var idField = 'LpuLicenceOperationLink_id';

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params.LpuLicenceOperationLink_id = record.get('LpuLicenceOperationLink_id');
					var url = "/?c=LpuPassport&m=deleteLpuLicenceOperationLink";
					var index = 0;
					if (record.get('LpuLicenceOperationLink_id') > 0) {
						index = 1;
					}

					switch (index) {
						case 0:
							grid.getStore().remove(record);
							break;
						case 1:
							if (!Ext.isEmpty(url)) {
								Ext.Ajax.request({
									callback: function(opt, scs, response) {
										if (scs) {
											grid.getStore().remove(record);
										}
									}.createDelegate(this),
									params: params,
									url: url
								});
							}
							//grid.getStore().remove(record);
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_dannuyu_operatsiyu_nad_litsenziey'],
			title: lang['vopros']
		});
	},
	openLpuLicenceLinkEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swLpuLicenceLinkEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_profilya_litsenzii_uje_otkryito']);
			return false;
		}

		var LpuLicenceLinkList = new Array();
		var formParams = new Object();
		var grid = this.findById('LLEW_LpuLicenceLinkGrid').getGrid();
		var params = new Object();
		params.LpuLicence_id = this.LpuLicence_id;
		var selectedRecord;

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuLicenceLink_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {

			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceLink_id') ) {
					LpuLicenceLinkList.push(rec.get('LpuSectionProfile_id'));
				}
			});

		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceLink_id') && selectedRecord.get('LpuLicenceLink_id') != rec.get('LpuLicenceLink_id') ) {
					LpuLicenceLinkList.push(rec.get('LpuSectionProfile_id'));
				}
			});


			formParams = selectedRecord.data;
			params.LpuLicenceLink_id = grid.getSelectionModel().getSelected().get('LpuLicenceLink_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};

			if (params.LpuLicenceLink_id < 0) {
				//params.LpuLicenceLink_id = grid.getSelectionModel().getSelected().get('LpuLicenceLink_id');
				params.LpuLicenceProfileType_Code = grid.getSelectionModel().getSelected().get('LpuLicenceProfileType_Code');
			}

		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuLicenceLinkData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.LpuLicenceLinkData.LpuLicenceLink_id);

			if ( record ) {

				var grid_fields = [];

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.LpuLicenceLinkData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuLicenceLink_id') ) {
					grid.getStore().removeAll();
				}

				data.LpuLicenceLinkData.LpuLicenceLink_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.LpuLicenceLinkData ], true);
			}
		}.createDelegate(this);
		params.LpuLicenceLinkList = LpuLicenceLinkList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swLpuLicenceLinkEditWindow').show(params);
	},
	LpuLicenceLinkGridRecordDelete: function() {
		var wnd = this;

		if ( this.action == 'view' ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = this.findById('LLEW_LpuLicenceLinkGrid').getGrid();
					var idField = 'LpuLicenceLink_id';

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params.LpuLicenceLink_id = record.get('LpuLicenceLink_id');
					var url = "/?c=LpuPassport&m=deleteLpuLicenceLink";
					var index = 0;
					if (record.get('LpuLicenceLink_id') > 0) {
						index = 1;
					}

					switch (index) {
						case 0:
							grid.getStore().remove(record);
							break;
						case 1:
							if (!Ext.isEmpty(url)) {
								Ext.Ajax.request({
									callback: function(opt, scs, response) {
										if (scs) {
											grid.getStore().remove(record);
										}
									}.createDelegate(this),
									params: params,
									url: url
								});
							}
							//grid.getStore().remove(record);
							break;
					}

					if ( grid.getStore().getCount() > 0 ) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_udalit_svyaz_profilya_i_litsenzii'],
			title: lang['vopros']
		});
	},
	openLpuLicenceOperationLinkEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swLpuLicenceOperationLinkEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_operatsii_s_litsenziey_uje_otkryito']);
			return false;
		}

		//var base_form = this.MainPanel.getForm();
		var deniedLicsOperationList = [],
			deniedLicenceOperationDateList = [],
			formParams = {},
			params = {},
			grid = this.findById('LLEW_LpuLicenceOperationLinkGrid').getGrid(),
			selectedRecord;

		params.LpuLicence_id = this.LpuLicence_id;


		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuLicenceOperationLink_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {

			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceOperationLink_id') ) {
					deniedLicsOperationList.push(rec.get('LicsOperation_id'));
					deniedLicenceOperationDateList.push(rec.get('LpuLicenceOperationLink_Date'));
				}
			});
		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceOperationLink_id') && selectedRecord.get('LpuLicenceOperationLink_id') != rec.get('LpuLicenceOperationLink_id') ) {
					deniedLicsOperationList.push(rec.get('LicsOperation_id'));
					deniedLicenceOperationDateList.push(rec.get('LpuLicenceOperationLink_Date'));
				}
			});


			formParams = selectedRecord.data;
			params.LpuLicenceOperationLink_id = grid.getSelectionModel().getSelected().get('LpuLicenceOperationLink_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};

			if (params.LpuLicenceOperationLink_id < 0) {
				params.LicsOperation_id = grid.getSelectionModel().getSelected().get('LicsOperation_id');
				params.LpuLicenceDop_Num = grid.getSelectionModel().getSelected().get('LpuLicenceDop_Num');
				params.LpuLicenceOperationLink_Date = grid.getSelectionModel().getSelected().get('LpuLicenceOperationLink_Date');
			}
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuLicenceOperationLinkData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.LpuLicenceOperationLinkData.LpuLicenceOperationLink_id);

			if ( record ) {

				var grid_fields = [];

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.LpuLicenceOperationLinkData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuLicenceOperationLink_id') ) {
					grid.getStore().removeAll();
				}

				data.LpuLicenceOperationLinkData.LpuLicenceOperationLink_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.LpuLicenceOperationLinkData ], true);
			}
		}.createDelegate(this);
		params.deniedLicsOperationList = deniedLicsOperationList;
		params.deniedLicenceOperationDateList = deniedLicenceOperationDateList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swLpuLicenceOperationLinkEditWindow').show(params);
	},
	openLpuLicenceDopEditWindow: function(action) {
		if ( typeof action != 'string' || !(action.inlist([ 'add', 'edit', 'view' ])) ) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swLpuLicenceDopEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_prilojeniya_k_litsenzii_uje_otkryito']);
			return false;
		}

		//var base_form = this.MainPanel.getForm();
		var deniedLicsOperationList = [],
			deniedLicenceOperationDateList = [],
			formParams = {},
			grid = this.findById('LLEW_LpuLicenceDopGrid').getGrid(),
			params = {},
			selectedRecord;

		params.LpuLicence_id = this.LpuLicence_id;

		if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('LpuLicenceDop_id') ) {
			selectedRecord = grid.getSelectionModel().getSelected();
		}

		if ( action == 'add' ) {

			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceOperationLink_id') ) {
					//deniedLicsOperationList.push(rec.get('LicsOperation_id'));
					deniedLicenceOperationDateList.push(rec.get('LpuLicenceDop_setDate'));
				}
			});
		}
		else {
			if ( !selectedRecord ) {
				return false;
			}

			grid.getStore().each(function(rec) {
				if ( rec.get('LpuLicenceDop_id') && selectedRecord.get('LpuLicenceDop_id') != rec.get('LpuLicenceDop_id') ) {
					//deniedLicsOperationList.push(rec.get('LicsOperation_id'));
					deniedLicenceOperationDateList.push(rec.get('LpuLicenceDop_setDate'));
				}
			});

			formParams = selectedRecord.data;
			params.LpuLicenceDop_id = grid.getSelectionModel().getSelected().get('LpuLicenceDop_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
			};

			if (params.LpuLicenceDop_id < 0) {
				//params.LicsOperation_id = grid.getSelectionModel().getSelected().get('LicsOperation_id');
				params.LpuLicenceDop_Num = grid.getSelectionModel().getSelected().get('LpuLicenceDop_Num');
				params.LpuLicenceDop_setDate = grid.getSelectionModel().getSelected().get('LpuLicenceDop_setDate');
			}
		}

		params.action = action;
		params.callback = function(data) {
			if ( typeof data != 'object' || typeof data.LpuLicenceDopData != 'object' ) {
				return false;
			}

			var record = grid.getStore().getById(data.LpuLicenceDopData.LpuLicenceDop_id);

			if ( record ) {

				var grid_fields = [];

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( var i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.LpuLicenceDopData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('LpuLicenceDop_id') ) {
					grid.getStore().removeAll();
				}

				data.LpuLicenceDopData.LpuLicenceDop_id = -swGenTempId(grid.getStore());

				grid.getStore().loadData([ data.LpuLicenceDopData ], true);
			}
		}.createDelegate(this);
		//params.deniedLicsOperationList = deniedLicsOperationList;
		params.deniedLicenceOperationDateList = deniedLicenceOperationDateList;
		params.formMode = 'local';
		params.formParams = formParams;

		getWnd('swLpuLicenceDopEditWindow').show(params);
	},
 show: function()
 {
  sw.Promed.swLpuLicenceEditWindow.superclass.show.apply(this, arguments);
  var current_window = this,
			form = this.findById('LpuLicenceEditForm'),
			base_form = form.getForm(),
			isKz = getRegionNick() == 'kz';

	if (!arguments[0]) {
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.ERROR,
			msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
			title: lang['oshibka'],
			fn: function() {
		 		this.hide();
			}
		});
	}

	this.focus();
	base_form.reset();

	this.findById('LLEW_LpuLicenceProfileGrid').removeAll({clearAll: true});
	this.findById('LLEW_LpuLicenceOperationLinkGrid').removeAll({clearAll: true});
	this.findById('LLEW_LpuLicenceLinkGrid').removeAll({clearAll: true});
	this.findById('LLEW_LpuLicenceDopGrid').removeAll({clearAll: true});

	this.callback = Ext.emptyFn;
	this.onHide = Ext.emptyFn;
	if (arguments[0].LpuLicence_id)
		this.LpuLicence_id = arguments[0].LpuLicence_id;
	else
		this.LpuLicence_id = null;
	if (arguments[0].Lpu_id)
		this.Lpu_id = arguments[0].Lpu_id;
	else
		this.Lpu_id = null;

	if (arguments[0].callback) {
		this.callback = arguments[0].callback;
	}
	if (arguments[0].owner)
	{
		this.owner = arguments[0].owner;
	}
	if (arguments[0].onHide)
	{
		this.onHide = arguments[0].onHide;
	}
	if (arguments[0].action)
	{
		this.action = arguments[0].action;
	}
	else
	{
		if ( ( this.LpuLicence_id ) && ( this.LpuLicence_id > 0 ) )
			this.action = "edit";
		else
			this.action = "add";
	}

	current_window.findById('LLEW_LpuLicenceProfile').collapse();
	current_window.findById('LLEW_LpuLicenceOperationLink').collapse();
		isKz?current_window.findById('LLEW_LpuLicenceOperationLink').collapse():Ext.emptyFn;

	current_window.syncShadow();
	current_window.center();

	this.findById('LLEW_LpuLicenceOperationLinkGrid').params = {
		LpuLicence_id: arguments[0].LpuLicence_id
	};

	this.findById('LLEW_LpuLicenceLinkGrid').params = {
		LpuLicence_id: arguments[0].LpuLicence_id
	};

	this.findById('LLEW_LpuLicenceProfileGrid').params = {
		LpuLicence_id: arguments[0].LpuLicence_id
	};

	isKz?this.findById('LLEW_LpuLicenceDopGrid').params = {LpuLicence_id: arguments[0].LpuLicence_id}:Ext.emptyFn;

	var form = this.findById('LpuLicenceEditForm');
	form.getForm().setValues(arguments[0]);

	var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
	loadMask.show();
	switch (this.action) {
		case 'add':
			this.setTitle(lang['litsenziya_mo_dobavlenie']);
			this.enableEdit(true);
			loadMask.hide();
			//form.getForm().clearInvalid();
			this.findById('LLEW_LpuLicenceOperationLinkGrid').setReadOnly(false);
			this.findById('LLEW_LpuLicenceLinkGrid').setReadOnly(false);
			this.findById('LLEW_LpuLicenceProfileGrid').setReadOnly(false);
			isKz?this.findById('LLEW_LpuLicenceDopGrid').setReadOnly(false):Ext.emptyFn;
		break;
		case 'edit':
			this.setTitle(lang['litsenziya_mo_redaktirovanie']);
			this.enableEdit(true);
			this.findById('LLEW_LpuLicenceOperationLinkGrid').setReadOnly(false);
			this.findById('LLEW_LpuLicenceLinkGrid').setReadOnly(false);
			this.findById('LLEW_LpuLicenceProfileGrid').setReadOnly(false);
			isKz?this.findById('LLEW_LpuLicenceDopGrid').setReadOnly(false):Ext.emptyFn;
		break;
		case 'view':
			this.setTitle(lang['litsenziya_mo_prosmotr']);
			this.enableEdit(false);
			this.findById('LLEW_LpuLicenceOperationLinkGrid').setReadOnly(true);
			this.findById('LLEW_LpuLicenceLinkGrid').setReadOnly(true);
			this.findById('LLEW_LpuLicenceProfileGrid').setReadOnly(true);
			isKz?this.findById('LLEW_LpuLicenceDopGrid').setReadOnly(true):Ext.emptyFn;
		break;
	}

  if (this.action != 'add') {
   form.getForm().load(
   {
	params:
	{
	 LpuLicence_id: current_window.LpuLicence_id,
	 Lpu_id: current_window.Lpu_id
	},
	failure: function(f, o, a)
	{
	 loadMask.hide();
	 sw.swMsg.show(
	 {
	  buttons: Ext.Msg.OK,
	  fn: function()
	  {
	   current_window.hide();
	  },
	  icon: Ext.Msg.ERROR,
	  msg: lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu'],
	  title: lang['oshibka']
	 });
	},
	success: function() {
	 loadMask.hide();

	 var country_id = current_window.findById('llef_CountryCombo').getValue();
	 var region_id = current_window.findById('llef_RegionCombo').getValue();
	 var subregion_id = current_window.findById('llef_SubRegionCombo').getValue();
	 var city_id = current_window.findById('llef_CityCombo').getValue();
	 var town_id = current_window.findById('llef_TownCombo').getValue();
	 var kl_area_stat_id = current_window.findById('llef_KLAreaStatCombo').getValue();
	 var klarea_pid = 0;
	 var level = 0;

	current_window.findById('llef_KLAreaStatCombo').getStore().load({
		callback: function() {
			current_window.findById('llef_KLAreaStatCombo').fireEvent('change', current_window.findById('llef_KLAreaStatCombo'), kl_area_stat_id, kl_area_stat_id);

			current_window.findById('llef_RegionCombo').getStore().load({
			callback: function() {
				current_window.findById('llef_RegionCombo').setValue(region_id);
			},
			params: {
				country_id: country_id,
				level: 1,
				value: 0
			}
			});

			if (region_id != null) {
				klarea_pid = region_id;
				level = 1;
			}

			current_window.findById('llef_SubRegionCombo').getStore().load({
				callback: function() {
				 current_window.findById('llef_SubRegionCombo').setValue(subregion_id);
				},
				params: {
					country_id: 0,
					level: 2,
					value: klarea_pid
				}
			});

			if (subregion_id != null) {
				klarea_pid = subregion_id;
				level = 2;
			}

			current_window.findById('llef_CityCombo').getStore().load({
				callback: function() {
				 current_window.findById('llef_CityCombo').setValue(city_id);
				},
				params: {
					country_id: 0,
					level: 3,
					value: klarea_pid
				}
			});

			if (city_id != null) {
			klarea_pid = city_id;
			level = 3;
			}

			current_window.findById('llef_TownCombo').getStore().load({
				callback: function() {
				 current_window.findById('llef_TownCombo').setValue(town_id);
				},
				params: {
					country_id: 0,
					level: 4,
					value: klarea_pid
				}
			});

			if (town_id != null) {
				klarea_pid = town_id;
				level = 4;
			}

		}
	});

	 var combo = current_window.findById('LLEW_Org_id');
	 combo.getStore().load({
		callback: function() {
			combo.setValue(combo.getValue());
			combo.fireEvent('change', combo);
		},
		params: {
			Org_id: combo.getValue(),
			OrgType: 'lic'
		}
	 });

	 current_window.findById('LLEW_LpuLicenceOperationLinkGrid').loadData({globalFilters:{LpuLicence_id: current_window.LpuLicence_id},params:{LpuLicence_id: current_window.LpuLicence_id}});
	 current_window.findById('LLEW_LpuLicenceLinkGrid').loadData({globalFilters:{LpuLicence_id: current_window.LpuLicence_id},params:{LpuLicence_id: current_window.LpuLicence_id}});
	 current_window.findById('LLEW_LpuLicenceProfileGrid').loadData({globalFilters:{LpuLicence_id: current_window.LpuLicence_id},params:{LpuLicence_id: current_window.LpuLicence_id}});
	 isKz?current_window.findById('LLEW_LpuLicenceDopGrid').loadData({globalFilters:{LpuLicence_id: current_window.LpuLicence_id},params:{LpuLicence_id: current_window.LpuLicence_id}}):Ext.emptyFn;
	},
	url: '/?c=LpuPassport&m=loadLpuLicence'
   });
  }
  if ( this.action != 'view' )
   Ext.getCmp('LLEW_LpuLicence_Ser').focus(true, 100);
  else
   this.buttons[3].focus();
 },
 initComponent: function()
 {
  // Форма с полями
  var current_window = this;

		// Сохранение основной формы
		this.MainRecordAdd = function()
		{
			var tf = Ext.getCmp('LpuLicenceEditForm');
			var tw = current_window;
			if (tf.getForm().isValid())
			{
				tw.submit(true);
			}
			return false;
		}

  this.LpuLicenceEditForm = new Ext.form.FormPanel(
  {
   autoHeight: true,
   bodyStyle: 'padding: 5px; overflow: auto;',
   border: false,
   buttonAlign: 'left',
   frame: true,
   id: 'LpuLicenceEditForm',
   labelAlign: 'right',
   labelWidth: 150,
   items:
   [{
	id: 'LLEW_Lpu_id',
	name: 'Lpu_id',
	value: 0,
	xtype: 'hidden'
   }, {
	id: 'LLEW_LpuLicence_id',
	name: 'LpuLicence_id',
	value: 0,
	xtype: 'hidden'
   }, {
	fieldLabel: lang['seriya_litsenzii'],
	id: 'LLEW_LpuLicence_Ser',
	xtype: 'textfield',
	tabIndex: TABINDEX_LPLICEW + 1,
	autoCreate: {tag: "input", maxLength: "30", autocomplete: "off"},
	//maskRe: (!getRegionNick().inlist([ 'astra', 'kareliya', 'pskov' ]) ? /[0-9]/ : null),
	anchor: '100%',
	name: 'LpuLicence_Ser'
   }, {
	fieldLabel: lang['nomer_litsenzii'],
	//plugins: (getGlobalOptions().region.nick != 'khak') ? [new Ext.ux.InputTextMask('99-99-999999', false)] : null,
	xtype: 'textfield',
				allowBlank: false,
	tabIndex: TABINDEX_LPLICEW + 2,
	autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
	//maskRe: (getGlobalOptions().region.nick != 'khak') ? /[0-9]/ : null,
	anchor: '100%',
	name: 'LpuLicence_Num'
   }, {
	fieldLabel: lang['vyidavshaya_organizatsiya'],
	xtype: 'sworgcombo',
				allowBlank: false,
	tabIndex: TABINDEX_LPLICEW + 3,
	autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
	anchor: '100%',
	id: 'LLEW_Org_id',
	hiddenName: 'Org_id',
	listeners:
	{
	 'change': function()
	 {
	  //
	 },
	 keydown: function(inp, e)
	 {
	  if (e.getKey() == e.DELETE || e.getKey() == e.F4 )
	  {
	   e.stopEvent();
	   if (e.browserEvent.stopPropagation)
	   {
		e.browserEvent.stopPropagation();
	   }
	   else
	   {
		e.browserEvent.cancelBubble = true;
	   }
	   if (e.browserEvent.preventDefault)
	   {
		e.browserEvent.preventDefault();
	   }
	   else
	   {
		e.browserEvent.returnValue = false;
	   }
	   e.returnValue = false;

	   if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							switch (e.getKey())
							{
								case e.DELETE:
									inp.clearValue();
									inp.ownerCt.ownerCt.findField('Org_id').setRawValue(null);
									break;
								case e.F4:
									inp.onTrigger1Click();
									break;
							}
						}
					}
				},
				onTrigger1Click: function()
				{
					var combo = this;
					getWnd('swOrgSearchWindow').show({
						object: 'lic',
						onSelect: function(orgData) {
							if ( orgData.Org_id > 0 )
							{
								combo.getStore().load({
									params: {
										OrgType:'lic',
										Org_id: orgData.Org_id,
										Org_Name:''
									},
									callback: function()
									{
										combo.setValue(orgData.Org_id);
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}
								});
							}
							getWnd('swOrgSearchWindow').hide();
						},
						onClose: function() {combo.focus(true, 200)}
					});
				}
			}, {
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPLICEW + 4,
				format: 'd.m.Y',
				fieldLabel: lang['data_vyidachi'],
				allowBlank: false,
				name: 'LpuLicence_setDate'
			}, {
				fieldLabel: lang['registratsionnyiy_nomer'],
				xtype: 'textfield',
				tabIndex: TABINDEX_LPLICEW + 5,
				autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
				//maskRe: getRegionNick().inlist([ 'astra', 'pskov' ]) ? null : /[0-9]/,
				anchor: '100%',
				name: 'LpuLicence_RegNum'
			}, {
				fieldLabel: lang['vid_deyatelnosti'],
				xtype: 'swcommonsprcombo',
				tabIndex: TABINDEX_LPLICEW + 6,
				comboSubject: 'VidDeat',
				anchor: '100%',
				name: 'VidDeat_id'
			}, {
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPLICEW + 7,
				format: 'd.m.Y',
				fieldLabel: lang['nachalo_deystviya'],
				name: 'LpuLicence_begDate'
			}, {
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
				tabIndex: TABINDEX_LPLICEW + 8,
				format: 'd.m.Y',
				fieldLabel: lang['okonchanie_deystviya'],
				name: 'LpuLicence_endDate'
			},{
				autoHeight: true,
				title: lang['territoriya_deystviya'],
				xtype: 'fieldset',
				items: [{
					codeField: 'KLAreaStat_Code',
					disabled: false,
					displayField: 'KLArea_Name',
					editable: true,
					enableKeyEvents: true,
					tabIndex: TABINDEX_LPLICEW + 9,
					fieldLabel: lang['territoriya'],
					hiddenName: 'KLAreaStat_id',
					id: 'llef_KLAreaStatCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var current_window = Ext.getCmp('LpuLicenceEditWindow');
							var current_record = combo.getStore().getById(newValue);
							current_window.findById('llef_CountryCombo').enable();
							current_window.findById('llef_RegionCombo').enable();
							current_window.findById('llef_SubRegionCombo').enable();
							current_window.findById('llef_CityCombo').enable();
							current_window.findById('llef_TownCombo').enable();
							//current_window.findById('llef_StreetCombo').enable();
							if (!current_record) {
								return false;
							}

							var country_id = current_record.get('KLCountry_id');
							var region_id = current_record.get('KLRGN_id');
							var subregion_id = current_record.get('KLSubRGN_id');
							var city_id = current_record.get('KLCity_id');
							var town_id = current_record.get('KLTown_id');
							var klarea_pid = 0;
							var level = 0;

							current_window.clearAddressCombo(current_window.findById('llef_CountryCombo').areaLevel);

							if (country_id != null) {
								current_window.findById('llef_CountryCombo').setValue(country_id);
								current_window.findById('llef_CountryCombo').disable();
							} else {
								return false;
							}

							current_window.findById('llef_RegionCombo').getStore().load({
								callback: function() {
									current_window.findById('llef_RegionCombo').setValue(region_id);
								},
								params: {
									country_id: country_id,
									level: 1,
									value: 0
								}
							});

							if (region_id.toString().length > 0) {
								klarea_pid = region_id;
								level = 1;
							}

							current_window.findById('llef_SubRegionCombo').getStore().load({
								callback: function() {
									current_window.findById('llef_SubRegionCombo').setValue(subregion_id);
								},
								params: {
									country_id: 0,
									level: 2,
									value: klarea_pid
								}
							});

							if (subregion_id.toString().length > 0) {
								klarea_pid = subregion_id;
								level = 2;
							}

							current_window.findById('llef_CityCombo').getStore().load({
								callback: function() {
									current_window.findById('llef_CityCombo').setValue(city_id);
								},
								params: {
									country_id: 0,
									level: 3,
									value: klarea_pid
								}
							});

							if (city_id.toString().length > 0) {
								klarea_pid = city_id;
								level = 3;
							}

							current_window.findById('llef_TownCombo').getStore().load({
								callback: function() {
									current_window.findById('llef_TownCombo').setValue(town_id);
								},
								params: {
									country_id: 0,
									level: 4,
									value: klarea_pid
								}
							});

							if (town_id.toString().length > 0) {
								klarea_pid = town_id;
								level = 4;
							}

							switch (level) {
								case 1:
									current_window.findById('llef_RegionCombo').disable();
									break;
								case 2:
									current_window.findById('llef_RegionCombo').disable();
									current_window.findById('llef_SubRegionCombo').disable();
									break;
								case 3:
									current_window.findById('llef_RegionCombo').disable();
									current_window.findById('llef_SubRegionCombo').disable();
									current_window.findById('llef_CityCombo').disable();
									break;
								case 4:
									current_window.findById('llef_RegionCombo').disable();
									current_window.findById('llef_SubRegionCombo').disable();
									current_window.findById('llef_CityCombo').disable();
									current_window.findById('llef_TownCombo').disable();
									break;
							}
						}
					},
					store: new Ext.db.AdapterStore({
						autoLoad: true,
						dbFile: 'Promed.db',
						fields: [
							{ name: 'KLAreaStat_id', type: 'int' },
							{ name: 'KLAreaStat_Code', type: 'int' },
							{ name: 'KLArea_Name', type: 'string' },
							{ name: 'KLCountry_id', type: 'int' },
							{ name: 'KLRGN_id', type: 'int' },
							{ name: 'KLSubRGN_id', type: 'int' },
							{ name: 'KLCity_id', type: 'int' },
							{ name: 'KLTown_id', type: 'int' }
						],
						key: 'KLAreaStat_id',
						sortInfo: {
							field: 'KLAreaStat_Code',
							direction: 'ASC'
						},
						tableName: 'KLAreaStat'
					}),
					// tabIndex: 1431,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
						'</div></tpl>'
					),
					valueField: 'KLAreaStat_id',
					width: 300,
					xtype: 'swbaselocalcombo'
				}, {
					areaLevel: 0,
					codeField: 'KLCountry_Code',
					disabled: false,
					displayField: 'KLCountry_Name',
					tabIndex: TABINDEX_LPLICEW + 10,
					editable: true,
					fieldLabel: lang['strana'],
					hiddenName: 'KLCountry_id',
					id: 'llef_CountryCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (newValue != null && combo.getRawValue().toString().length > 0) {
								Ext.getCmp('LpuLicenceEditWindow').loadAddressCombo(combo.areaLevel, combo.getValue(), 0, true);
							} else {
								Ext.getCmp('LpuLicenceEditWindow').clearAddressCombo(combo.areaLevel);
							}
						},
						'keydown': function(combo, e) {
							if (e.getKey() == e.DELETE) {
								if (combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
									combo.fireEvent('change', combo, null, combo.getValue());
								}
							}
						},
						'select': function(combo, record, index) {
							if (record.get('KLCountry_id') == combo.getValue()) {
								combo.collapse();
								return false;
							}
							combo.fireEvent('change', combo, record.get('KLArea_id'), null);
						}
					},
					store: new Ext.db.AdapterStore({
						autoLoad: true,
						dbFile: 'Promed.db',
						fields: [
							{ name: 'KLCountry_id', type: 'int' },
							{ name: 'KLCountry_Code', type: 'int' },
							{ name: 'KLCountry_Name', type: 'string' }
						],
						key: 'KLCountry_id',
						sortInfo: {
							field: 'KLCountry_Name'
						},
						tableName: 'KLCountry'
					}),
					// tabIndex: 1423,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
						'</div></tpl>'
					),
					valueField: 'KLCountry_id',
					width: 300,
					xtype: 'swbaselocalcombo'
				}, {
					areaLevel: 1,
					disabled: false,
					displayField: 'KLArea_Name',
					enableKeyEvents: true,
					fieldLabel: lang['region'],
					tabIndex: TABINDEX_LPLICEW + 11,
					hiddenName: 'KLRgn_id',
					id: 'llef_RegionCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (newValue != null && combo.getRawValue().toString().length > 0) {
								Ext.getCmp('LpuLicenceEditWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
							} else {
								Ext.getCmp('LpuLicenceEditWindow').clearAddressCombo(combo.areaLevel);
							}
						},
						'keydown': function(combo, e) {
							if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
								combo.fireEvent('change', combo, null, combo.getValue());
							}
						},
						'select': function(combo, record, index) {
							if (record.get('KLArea_id') == combo.getValue()) {
								combo.collapse();
								return false;
							}
							combo.fireEvent('change', combo, record.get('KLArea_id'));
						}
					},
					minChars: 0,
					mode: 'local',
					queryDelay: 250,
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'KLArea_id', type: 'int' },
							{ name: 'KLArea_Name', type: 'string' }
						],
						key: 'KLArea_id',
						sortInfo: {
							field: 'KLArea_Name'
						},
						url: C_LOAD_ADDRCOMBO
					}),
					// tabIndex: 1424,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{KLArea_Name}',
						'</div></tpl>'
					),
					triggerAction: 'all',
					valueField: 'KLArea_id',
					width: 300,
					xtype: 'combo'
				}, {
					areaLevel: 2,
					disabled: false,
					displayField: 'KLArea_Name',
					enableKeyEvents: true,
					fieldLabel: lang['rayon'],
					tabIndex: TABINDEX_LPLICEW + 12,
					hiddenName: 'KLSubRgn_id',
					id: 'llef_SubRegionCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (newValue != null && combo.getRawValue().toString().length > 0) {
								Ext.getCmp('LpuLicenceEditWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
							} else {
								Ext.getCmp('LpuLicenceEditWindow').clearAddressCombo(combo.areaLevel);
							}
						},
						'keydown': function(combo, e) {
							if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
								combo.fireEvent('change', combo, null, combo.getValue());
							}
						},
						'select': function(combo, record, index) {
							if (record.get('KLArea_id') == combo.getValue()) {
								combo.collapse();
								return false;
							}
							combo.fireEvent('change', combo, record.get('KLArea_id'));
						}
					},
					minChars: 0,
					mode: 'local',
					queryDelay: 250,
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'KLArea_id', type: 'int' },
							{ name: 'KLArea_Name', type: 'string' }
						],
						key: 'KLArea_id',
						sortInfo: {
							field: 'KLArea_Name'
						},
						url: C_LOAD_ADDRCOMBO
					}),
					// tabIndex: 1425,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{KLArea_Name}',
						'</div></tpl>'
					),
					triggerAction: 'all',
					valueField: 'KLArea_id',
					width: 300,
					xtype: 'combo'
				}, {
					areaLevel: 3,
					disabled: false,
					displayField: 'KLArea_Name',
					enableKeyEvents: true,
					fieldLabel: lang['gorod'],
					tabIndex: TABINDEX_LPLICEW + 13,
					hiddenName: 'KLCity_id',
					id: 'llef_CityCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (newValue != null && combo.getRawValue().toString().length > 0) {
								Ext.getCmp('LpuLicenceEditWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
							}
						},
						'keydown': function(combo, e) {
							if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
								combo.fireEvent('change', combo, null, combo.getValue());
							}
						},
						'select': function(combo, record, index) {
							if (record.get('KLArea_id') == combo.getValue()) {
								combo.collapse();
								return false;
							}
							combo.fireEvent('change', combo, record.get('KLArea_id'));
						}
					},
					minChars: 0,
					mode: 'local',
					queryDelay: 250,
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'KLArea_id', type: 'int' },
							{ name: 'KLArea_Name', type: 'string' }
						],
						key: 'KLArea_id',
						sortInfo: {
							field: 'KLArea_Name'
						},
						url: C_LOAD_ADDRCOMBO
					}),
					// tabIndex: 1426,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{KLArea_Name}',
						'</div></tpl>'
					),
					triggerAction: 'all',
					valueField: 'KLArea_id',
					width: 300,
					xtype: 'combo'
				}, {
					areaLevel: 4,
					disabled: false,
					displayField: 'KLArea_Name',
					enableKeyEvents: true,
					tabIndex: TABINDEX_LPLICEW + 14,
					fieldLabel: lang['naselennyiy_punkt'],
					hiddenName: 'KLTown_id',
					id: 'llef_TownCombo',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (newValue != null && combo.getRawValue().toString().length > 0) {
								Ext.getCmp('LpuLicenceEditWindow').loadAddressCombo(combo.areaLevel, 0, combo.getValue(), true);
							}
						},
						'keydown': function(combo, e) {
							if (e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0) {
								combo.fireEvent('change', combo, null, combo.getValue());
							}
						},
						'select': function(combo, record, index) {
							if (record.get('KLArea_id') == combo.getValue()) {
								combo.collapse();
								return false;
							}
							combo.fireEvent('change', combo, record.get('KLArea_id'));
						}
					},
					minChars: 0,
					mode: 'local',
					queryDelay: 250,
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'KLArea_id', type: 'int' },
							{ name: 'KLArea_Name', type: 'string' }
						],
						key: 'KLArea_id',
						sortInfo: {
							field: 'KLArea_Name'
						},
						url: C_LOAD_ADDRCOMBO
					}),
					// tabIndex: 1427,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{KLArea_Name}',
						'</div></tpl>'
					),
					triggerAction: 'all',
					valueField: 'KLArea_id',
					width: 300,
					xtype: 'combo'
				}]
			},
			new sw.Promed.Panel({
				autoHeight: true,
				style:'margin-bottom: 0.5em;',
				border: true,
				collapsible: true,
				//collapsed: true,
				id: 'LLEW_LpuLicenceProfile',
				layout: 'form',
				title: lang['vid_litsenzii_po_profilyu'],
				listeners: {
					collapse: function ()  {
						current_window.syncShadow();
					}.createDelegate(this)
				},
				items: [
					new sw.Promed.ViewFrame({
						actions: [
							{ name: 'action_add', handler: function() {
								var tf = Ext.getCmp('LpuLicenceEditForm');
								var tw = current_window;
								if (tf.getForm().isValid())
								{
									tw.submit(true);
								}
								return false;
							}/*, func: current_window.MainRecordAdd*/},
							{name: 'action_add', handler: function() { this.openLpuLicenceProfileEditWindow('add'); }.createDelegate(this) },
							{name: 'action_edit', handler: function() { this.openLpuLicenceProfileEditWindow('edit'); }.createDelegate(this) },
							{name: 'action_view', handler: function() { this.openLpuLicenceProfileEditWindow('view'); }.createDelegate(this) },
							{name: 'action_delete', handler: function() { this.profileGridRecordDelete(); }.createDelegate(this) },
							{ name: 'action_refresh', handler: function() {
								Ext.getCmp('LLEW_LpuLicenceProfileGrid').loadData({
									globalFilters:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()},
									params:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()}
								})
							} },
							{ name: 'action_print' }
						],
						autoExpandColumn: 'autoexpand',
						object: 'LpuLicenceProfile',
						transferLine: true,
						editformclassname: 'swLpuLicenceProfileEditWindow',
						autoExpandMin: 150,
						autoLoadData: false,
						border: false,
						scheme: 'fed',
						//params: {LpuLicense_id: this.LpuLicense},
						dataUrl: '/?c=LpuPassport&m=loadLpuLicenceProfile',
						id: 'LLEW_LpuLicenceProfileGrid',
						paging: false,
						region: 'center',
						toolbar: true,
						totalProperty: 'totalCount',
						stringfields: [
							{ name: 'LpuLicenceProfile_id', type: 'int', header: 'ID', key: true },
							{ name: 'LpuLicence_id', type: 'int', header: lang['id_litsenzii_lpu'], hidden: true },
							{ name: 'LpuLicenceProfileType_id', header: lang['id_vida_litsenzii'], hidden: true },
							{ name: 'LpuLicenceProfileType_Code', type: 'int', header: lang['kod_vida_litsenzii'], width: 120 },
							{ name: 'LpuLicenceProfileType_Name', type: 'string', header: lang['naimenovanie_vida_litsenzii'], id: 'autoexpand', width: 240 }
						]
					})
				]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				style:'margin-bottom: 0.5em;',
				border: true,
				collapsible: true,
				collapsed: true,
				id: 'LLEW_LpuLicenceLink',
				layout: 'form',
				title: lang['profili_v002'],
				listeners: {
					collapse: function ()  {
						current_window.syncShadow();
					}.createDelegate(this)
				},
				items: [
					new sw.Promed.ViewFrame({
						actions: [
							{name: 'action_add', handler: function() { this.openLpuLicenceLinkEditWindow('add'); }.createDelegate(this) },
							{name: 'action_edit', handler: function() { this.openLpuLicenceLinkEditWindow('edit'); }.createDelegate(this) },
							{name: 'action_view', handler: function() { this.openLpuLicenceLinkEditWindow('view'); }.createDelegate(this) },
							{name: 'action_delete', handler: function() { this.LpuLicenceLinkGridRecordDelete(); }.createDelegate(this) },
							{name: 'action_refresh', handler: function() {
								Ext.getCmp('LLEW_LpuLicenceLinkGrid').loadData({
									globalFilters:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()},
									params:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()}
								})
							}},
							{name: 'action_print' }
						],
						autoExpandColumn: 'autoexpand',
						object: 'LpuLicenceLink',
						editformclassname: 'swLpuLicenceLinkEditWindow',
						autoExpandMin: 150,
						autoLoadData: false,
						border: false,
						scheme: 'fed',
						dataUrl: '/?c=LpuPassport&m=loadLpuLicenceLink',
						id: 'LLEW_LpuLicenceLinkGrid',
						paging: false,
						region: 'center',
						toolbar: true,
						totalProperty: 'totalCount',
						stringfields: [
							{ name: 'LpuLicenceLink_id', type: 'int', header: 'ID', key: true },
							{ name: 'LpuSectionProfile_id', type: 'int', header: 'LpuSectionProfile_id', hidden: true },
							{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['naimenovanie_profilya'], width: 240 },
							{ name: 'LpuSectionProfile_Code', type: 'string', header: lang['kod_profilya'], width: 240 }
						]
					})
				]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				style:'margin-bottom: 0.5em;',
				border: true,
				collapsible: true,
				//collapsed: true,
				id: 'LLEW_LpuLicenceOperationLink',
				layout: 'form',
				title: lang['operatsii_provedennyie_s_litsenziey'],
				listeners: {
					collapse: function ()  {
						current_window.syncShadow();
					}.createDelegate(this)
					/*expand: function () {
						this.findById('LLEW_LpuLicenceGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
					}.createDelegate(this)*/
				},
				items: [
					new sw.Promed.ViewFrame({
						actions: [
							{name: 'action_add', handler: function() { this.openLpuLicenceOperationLinkEditWindow('add'); }.createDelegate(this) },
							{name: 'action_edit', handler: function() { this.openLpuLicenceOperationLinkEditWindow('edit'); }.createDelegate(this) },
							{name: 'action_view', handler: function() { this.openLpuLicenceOperationLinkEditWindow('view'); }.createDelegate(this) },
							{name: 'action_delete', handler: function() { this.operationGridRecordDelete(); }.createDelegate(this) },
							{ name: 'action_refresh', handler: function() {
								Ext.getCmp('LLEW_LpuLicenceOperationLinkGrid').loadData({
									globalFilters:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()},
									params:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()}
								})
							}},
							{ name: 'action_print' }
						],
						autoExpandColumn: 'autoexpand',
						object: 'LpuLicenceOperationLink',
						editformclassname: 'swLpuLicenceOperationLinkEditWindow',
						autoExpandMin: 150,
						autoLoadData: false,
						border: false,
						scheme: 'fed',
						dataUrl: '/?c=LpuPassport&m=loadLpuLicenceOperationLink',
						id: 'LLEW_LpuLicenceOperationLinkGrid',
						paging: false,
						region: 'center',
						toolbar: true,
						totalProperty: 'totalCount',
						stringfields: [
							{ name: 'LpuLicenceOperationLink_id', type: 'int', header: 'ID', key: true },
							{ name: 'LicsOperation_Name', type: 'string', header: lang['naimenovanie_operatsii'], width: 240 },
							{ name: 'LicsOperation_id', type: 'int', header: lang['id_operatsii'], hidden: true, width: 240 },
							{ name: 'LpuLicence_id', type: 'int', header: lang['id_litsenzii_lpu'], hidden: true },
							{ name: 'LpuLicenceOperationLink_Date', type: 'date', header: lang['data_operatsii'], width: 240 }
						]
					})
				]
			}),
			new sw.Promed.Panel({
				autoHeight: true,
				style:'margin-bottom: 0.5em;',
				border: true,
				hidden: getRegionNick() != 'kz',
				collapsible: true,
				collapsed: true,
				id: 'LLEW_LpuLicenceAdds',
				layout: 'form',
				title: lang['prilojeniya_k_litsenzii'],
				listeners: {
					collapse: function ()  {
						current_window.syncShadow();
					}.createDelegate(this)
					/*expand: function () {
						this.findById('LLEW_LpuLicenceGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
					}.createDelegate(this)*/
				},
				items: [
					new sw.Promed.ViewFrame({
						actions: [
							{name: 'action_add', handler: function() { this.openLpuLicenceDopEditWindow('add'); }.createDelegate(this) },
							{name: 'action_edit', handler: function() { this.openLpuLicenceDopEditWindow('edit'); }.createDelegate(this) },
							{name: 'action_view', handler: function() { this.openLpuLicenceDopEditWindow('view'); }.createDelegate(this) },
							{name: 'action_delete', handler: function() { this.operationGridRecordDelete(); }.createDelegate(this) },
							{ name: 'action_refresh', handler: function() {
								Ext.getCmp('LLEW_LpuLicenceDopGrid').loadData({
									globalFilters:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()},
									params:{LpuLicence_id: Ext.getCmp('LpuLicenceEditForm').findById('LLEW_LpuLicence_id').getValue()}
								})
							}},
							{ name: 'action_print' }
						],
						autoExpandColumn: 'autoexpand',
						object: 'LpuLicenceDop',
						editformclassname: 'swLpuLicenceDopEditWindow',
						autoExpandMin: 150,
						autoLoadData: false,
						border: false,
						scheme: 'fed',
						dataUrl: '/?c=LpuPassport&m=loadLpuLicenceDop',
						id: 'LLEW_LpuLicenceDopGrid',
						paging: false,
						region: 'center',
						toolbar: true,
						totalProperty: 'totalCount',
						stringfields: [
							{ name: 'LpuLicenceDop_id', type: 'int', header: 'ID', key: true },
							{ name: 'LpuLicenceDop_Num', type: 'string', header: lang['nomer_prilojeniya'], width: 240 },
							{ name: 'LpuLicenceDop_setDate', type: 'date', header: lang['data_vyidachi_prilojeniya'], width: 240 }
						]
					})
				]
			})
			],
			reader: new Ext.data.JsonReader(
			{
				success: function()
				{
					//
				}
			}, [
				{ name: 'Lpu_id' },
				{ name: 'LpuLicence_id' },
				{ name: 'LpuLicence_Ser' },
				{ name: 'LpuLicence_Num' },
				{ name: 'Org_id' },
				{ name: 'LpuLicence_setDate' },
				{ name: 'LpuLicence_RegNum' },
				{ name: 'VidDeat_id' },
				{ name: 'LpuLicence_begDate' },
				{ name: 'LpuLicence_endDate' },
				{ name: 'KLAreaStat_id' },
				{ name: 'KLCountry_id' },
				{ name: 'KLRgn_id' },
				{ name: 'KLSubRgn_id' },
				{ name: 'KLCity_id' },
				{ name: 'KLTown_id' }
			]),
			url: '/?c=LpuPassport&m=saveLpuLicence'
		});
		Ext.apply(this,
		{
			buttons:
			[{
				handler: function()
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_LPLICEW + 16,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				tabIndex: TABINDEX_LPLICEW + 17,
				text: BTN_FRMCANCEL
			}],
			bodyStyle: 'overflow: auto;',
			items: [this.LpuLicenceEditForm]
		});
		sw.Promed.swLpuLicenceEditWindow.superclass.initComponent.apply(this, arguments);
	}
	});