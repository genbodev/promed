/**
* swLpuPassportEditWindow - окно редактирования пасспорта МО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      27.01.2010
* @comment      Префикс для id компонентов LPEW (LpuPassportEditWindow)
*/

sw.Promed.swLpuPassportEditWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	height: 550,
	title: langs('Паспорт МО'),
	width: 800,
	id: 'LpuPassportEditWindow',
	searchInProgress: false,
	doSearchEquipmentAndTransport: function(reset) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
			this.useSearchForm = true;
		}

		var thisWindow = this;
		var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
		var store = grid.getStore();
		var Mask = new Ext.LoadMask(Ext.get('LPEW_MedProductCardGrid'), { msg: SEARCH_WAIT});
		var searchForm = this.findById('EquipmentAndTransportFilter').getForm();
		var params = searchForm.getValues();
		params.Lpu_id = this.Lpu_id;
		params.MedProductCard_IsNotFRMO = this.findById('mpwpSearch_NedostypnoDliaFRMO').checked ? "2" : "1";
		if(!Ext.isEmpty(params.AccountingData_setDate)) {
			params.AccountingData_setDate = Date.parse( params.AccountingData_setDate ).toString().trim();
		}

		grid.store.clearFilter(true);

		Mask.show();

		Object.filter = function( obj ) {
			var result = {}; 
			Object.keys(obj).forEach(function(key) {
				if (!obj[key].length === 0 || obj[key].trim()) {
					result[key] = obj[key]; 
				}
			});
			return result;
		};
		
		var filterFields = Object.filter(params);

		store.filterBy(function(item) {
			
			var itemKeys = item.data;
			var result = [];
			var check = [];
			
			for(var key in itemKeys) {
				for(var filter in filterFields) {
					if(key == filter) {
						if(itemKeys[key] == filterFields[filter]) {
							check.push(true);
							result.push(true);
						}
					}
				}
			}

			var filteredKeys = Object.keys(filterFields);

			function isTrue(element) {
				return element === true;
			}

			return result.length != 0 && result.length == check.length && result.length == (filteredKeys.length - 1) && result.every(isTrue);
		});

		if (this.searchInProgress) {
			this.searchInProgress = false;
		}

		Mask.hide();
		return false;
	},
	resetSearchEquipmentAndTransport: function() {
		var thisWindow = this;
		var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
		var searchForm = this.findById('EquipmentAndTransportFilter').getForm();
		var params = {};

		searchForm.reset();
		grid.store.clearFilter();
		
		params.Lpu_id = this.Lpu_id;
		params.MedProductCard_IsNotFRMO = this.findById('mpwpSearch_NedostypnoDliaFRMO').checked = false;

		thisWindow.searchInProgress = false;
		return false;
	},
	doSearchComputerEquipment: function(reset) {

		var ComputerEquipmentFilterForm = this.findById('LpuComputerEquipFilter').getForm();
		var ComputerEquipmentGrid = this.findById('LPEW_ComputerEquipmentGrid').getGrid();

		if (reset) {

			ComputerEquipmentFilterForm.reset();
			var year_combo = this.findById('ComputerEquip_YearCombo'),
				device_combo = this.findById('ComputerEquip_Device_id'),
				lpu_id = this.Lpu_id;

			year_combo.loadYearComboStore(year_combo, lpu_id);
			device_combo.onChangeDeviceCatField('');
		}

		var params = ComputerEquipmentFilterForm.getValues();

		ComputerEquipmentGrid.getStore().load({

			params: params,

			callback: function (data) {

				if (!data || data && data.length == 0) {

					ComputerEquipmentGrid.getStore().removeAll();
				}
			}
		});

	},
	deleteOrgHead: function(){
		var grid = this.findById('LPEW_OrgHeadGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('OrgHead_id') )
			return;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success) {
									grid.getStore().remove(record);

									if ( grid.getStore().getCount() == 0 ) {
										LoadEmptyRow(grid);
									}

									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении руководителя возникли ошибки'));
							}
						},
						params: {
							OrgHead_id: record.get('OrgHead_id')
						},
						url: '/?c=Org&m=deleteOrgHead'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить руководителя'),
			title: langs('Вопрос')
		});
	},
	deleteLpuBuildingPass: function(){
		var grid = this.findById('LPEW_LpuBuilding').getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('LpuBuildingPass_id') )
			return;

		var
			LpuBuildingPass_id = record.get('LpuBuildingPass_id'),
			LPBcombo = this.findById('LPEW_LpuBuildingPass_mid'),
			LpuBuildingPass_mid = LPBcombo.getValue();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if(!obj.success) {
									return false;
								}
								grid.getStore().remove(record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();

								if ( !Ext.isEmpty(LpuBuildingPass_mid) && LpuBuildingPass_mid == LpuBuildingPass_id ) {
									LPBcombo.clearValue();
								}

								var index = LPBcombo.getStore().findBy(function(rec) {
									return (rec.get('LpuBuildingPass_id') == LpuBuildingPass_id);
								});

								if ( index >= 0 ) {
									LPBcombo.getStore().remove(LPBcombo.getStore().getAt(index));
								}
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении здания МО возникли ошибки'));
							}
						},
						params: {
							LpuBuildingPass_id: record.get('LpuBuildingPass_id')
						},
						url: '/?c=LpuPassport&m=deleteLpuBuildingPass'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить здание МО?'),
			title: langs('Вопрос')
		});
	},
	openTransportConnectEditWindow: function(action) {
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

        if ( getWnd('swTransportConnectEditWindow').isVisible() ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Окно редактирования связи с транспортным узлом уже открыто.'));
            return false;
        }

        var formParams = {},
            grid = this.findById('LPEW_TransportConnectGrid').getGrid(),
            params = {},
            selectedRecord;

        params.Lpu_id = this.Lpu_id;

        if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('TransportConnect_id') ) {
            selectedRecord = grid.getSelectionModel().getSelected();
        }

        if ( action != 'add' ) {
            if ( !selectedRecord ) {
                return false;
            }

            formParams = selectedRecord.data;
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selectedRecord));
            };
        }

        params.action = action;
        params.callback = function(data) {
            grid.getStore().loadData();
        };
        params.formMode = 'local';
        params.formParams = formParams;

        getWnd('swTransportConnectEditWindow').show(params);
    },
	deleteOrgRSchet: function(){
		var grid = this.findById('LPEW_OrgRSchetGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();
		if ( !record || !record.get('OrgRSchet_id') )
			return;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var obj = Ext.util.JSON.decode(response.responseText);
								if(!obj.success) {
									return false;
								}
								grid.getStore().remove(record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении счета возникли ошибки'));
							}
						},
						params: {
							OrgRSchet_id: record.get('OrgRSchet_id')
						},
						url: '/?c=Org&m=deleteOrgRSchet'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить расчетный счет'),
			title: langs('Вопрос')
		});
	},
	
	doSearchLpuFSSContract: function(reset) {
		var grid = this.findById('LPEW_FSSContractGrid').getGrid();

		if (reset) {
			Ext.getCmp('LpuFSSContractType_id_Filter').setValue('');
			Ext.getCmp('LpuFSSContract_Num_Filter').setValue('');
		}

		var params = {
			LpuFSSContractType_id: Ext.getCmp('LpuFSSContractType_id_Filter').getValue(),
			LpuFSSContract_Num: Ext.getCmp('LpuFSSContract_Num_Filter').getValue(),
			Lpu_id: Ext.getCmp('LPEW_Lpu_id').getValue()
		};

		grid.getStore().load({
			params: params,
			callback: function (data) {
				if (!data || data && data.length == 0) {
					grid.getStore().removeAll();
				}
			}
		});
	},
	
	addCloseFilterMenu: function(gridCmp, defaultValue){
		var form = this;
		var grid = gridCmp;

		if ( !grid.getAction('action_isclosefilter_'+grid.id) ) {
			var menuIsCloseFilter = new Ext.menu.Menu({
				items: [
					new Ext.Action({
						text: langs('Все'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = null;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Все</b>'));
							grid.getGrid().getStore().baseParams.isClose = null;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: langs('Открытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 1;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Открытые</b>'));
							grid.getGrid().getStore().baseParams.isClose = 1;
							grid.getGrid().getStore().reload();
						}
					}),
					new Ext.Action({
						text: langs('Закрытые'),
						handler: function() {
							if (grid.gFilters) {
								grid.gFilters.isClose = 2;
							}
							grid.getAction('action_isclosefilter_'+grid.id).setText(langs('Показывать: <b>Закрытые</b>'));
							grid.getGrid().getStore().baseParams.isClose = 2;
							grid.getGrid().getStore().reload();
						}
					})
				]
			});

			if (defaultValue == 'all') {
				grid.addActions({
					isClose: null,
					name: 'action_isclosefilter_' + grid.id,
					text: langs('Показывать: <b>Все</b>'),
					menu: menuIsCloseFilter
				});
				grid.getGrid().getStore().baseParams.isClose = null;
			} else {
				grid.addActions({
					isClose: 1,
					name: 'action_isclosefilter_' + grid.id,
					text: langs('Показывать: <b>Открытые</b>'),
					menu: menuIsCloseFilter
				});
				grid.getGrid().getStore().baseParams.isClose = 1;
			}
		}

		return true;
	},

	numberRenderer: function(v){
		return (v) ? Number(v.slice(0,-2)) : null;
	},
	
	initComponent: function(){
		var isUfa = ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && !getGlobalOptions().superadmin ),
	        _this = this,
			win = this;

		Ext.apply(this, {
			xtype: 'panel',
			region: 'center',
			layout:'border',
			buttons: [{
				handler: function() {
					if ( _this.formAction == 'save' ) {
						return false;
					}

					_this.formAction = 'save';

					var form = Ext.getCmp('LPEW_panelForm');
					var base_form = form.getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								_this.formAction = 'edit';
								log(form.getFirstInvalidEl().id);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

					form = Ext.getCmp('Lpu_IdentificationPanel');
					base_form = form.getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								_this.formAction = 'edit';
								log(form.getFirstInvalidEl().id);
								Ext.getCmp('LpuPassportEditWindowTab').setActiveTab(0);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

					form = Ext.getCmp('Lpu_SupInfoPanel');
					base_form = form.getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								_this.formAction = 'edit';
								Ext.getCmp('LpuPassportEditWindowTab').setActiveTab(1);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

					form = Ext.getCmp('Lpu_PopulationPanel');
					base_form = form.getForm();

					if ( !base_form.isValid() ) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								_this.formAction = 'edit';
								Ext.getCmp('LpuPassportEditWindowTab').setActiveTab(7);
								form.getFirstInvalidEl().focus(false);
							}.createDelegate(this),
							icon: Ext.Msg.WARNING,
							msg: ERR_INVFIELDS_MSG,
							title: ERR_INVFIELDS_TIT
						});
						return false;
					}

//					if (Ext.getCmp('LPEW_Lpu_HasLocalPacsServer').checked) {
//						ipRegExp = new RegExp(/^(1\d{2}\.|[1-9]\d?\.|2[0-4]\d\.|25[0-5]\.){3}(\d{1,2}|1\d{2}|2[0-4]\d|25[0-5])$/);
//						var localPacsIp = Ext.getCmp('LPEW_Lpu_LocalPacsServerIP').getValue();
//						if (!ipRegExp.test(Ext.getCmp('LPEW_Lpu_LocalPacsServerIP').getValue())) {
//							sw.swMsg.alert('Сообщение', 'Введите корректный IP адрес локального PACS-а' );
//							return false;
//						}
//
//					}

					var loadMask = new Ext.LoadMask(Ext.get('LpuPassportEditWindow'), {msg: "Подождите, идет сохранение..."});
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=LpuPassport&m=saveLpuPassport',
						params: {

							Lpu_id: Ext.getCmp('LPEW_Lpu_id').getValue(),
							Server_id: Ext.getCmp('LPEW_Server_id').getValue(),
							Lpu_Name: Ext.getCmp('LPEW_Lpu_Name').getValue(),
							Lpu_Nick: Ext.getCmp('LPEW_Lpu_Nick').getValue(),
							Lpu_Ouz: Ext.getCmp('LPEW_Lpu_Ouz').getValue(),
							Lpu_f003mcod: Ext.getCmp('LPEW_Lpu_f003mcod').getValue(),
							Lpu_RegNomN2: Ext.getCmp('LPEW_Lpu_RegNomN2').getValue(),

							Oktmo_id: Ext.getCmp('LPEW_Oktmo_id').getValue(),
							LpuPmuType_id: Ext.getCmp('LPEW_LpuPmuType_id').getValue(),
							LpuPmuClass_id: Ext.getCmp('LPEW_LpuPmuClass_id').getValue(),
							LpuType_id: Ext.getCmp('LPEW_LpuType_id').getValue(),
							LpuAgeType_id: Ext.getCmp('LPEW_LpuAgeType_id').getValue(),
                            DepartAffilType_id: Ext.getCmp('LPEW_DepartAffilType_id').getValue(),
                            TOUZType_id: Ext.getCmp('LPEW_TOUZType_id').getValue(),
                            Org_tid: Ext.getCmp('LPEW_Org_tid').getValue(),
							Lpu_begDate: Ext.getCmp('LPEW_Lpu_begDate').getRawValue(),
							Lpu_endDate: Ext.getCmp('LPEW_Lpu_endDate').getRawValue(),
							Lpu_pid: Ext.getCmp('LPEW_Lpu_pid').getValue(),
							Lpu_nid: Ext.getCmp('LPEW_Lpu_nid').getValue(),
							Lpu_StickNick: Ext.getCmp('LPEW_Lpu_StickNick').getValue(),
							Lpu_StickAddress: Ext.getCmp('LPEW_Lpu_StickAddress').getValue(),
							Lpu_DistrictRate: Ext.getCmp('LPEW_Lpu_DistrictRate').getValue(),
							PassportToken_tid: Ext.getCmp('LPEW_PassportToken_tid').getValue(),

                            Lpu_IsSecret: (Ext.getCmp('LPEW_Lpu_IsSecret').checked) ? '2' : '1',

							Lpu_Www: Ext.getCmp('LPEW_Lpu_Www').getValue(),
							Lpu_Email: Ext.getCmp('LPEW_Lpu_Email').getValue(),
							Lpu_Phone: Ext.getCmp('LPEW_Lpu_Phone').getValue(),
							Lpu_Worktime: Ext.getCmp('LPEW_Lpu_Worktime').getValue(),

                            PasportMO_id: Ext.getCmp('LPEW_PasportMO_id').getValue(),
							UAddress_id: Ext.getCmp('LPEW_UAddress_id').getValue(),
							UAddress_Zip: Ext.getCmp('LPEW_UAddress_Zip').getValue(),
							UKLCountry_id: Ext.getCmp('LPEW_UKLCountry_id').getValue(),
							UKLRGN_id: Ext.getCmp('LPEW_UKLRGN_id').getValue(),
							UKLSubRGN_id: Ext.getCmp('LPEW_UKLSubRGN_id').getValue(),
							UKLCity_id: Ext.getCmp('LPEW_UKLCity_id').getValue(),
							UKLTown_id: Ext.getCmp('LPEW_UKLTown_id').getValue(),
							UKLStreet_id: Ext.getCmp('LPEW_UKLStreet_id').getValue(),
							UAddress_House: Ext.getCmp('LPEW_UAddress_House').getValue(),
							UAddress_Corpus: Ext.getCmp('LPEW_UAddress_Corpus').getValue(),
							UAddress_Flat: Ext.getCmp('LPEW_UAddress_Flat').getValue(),
							UAddress_Address: Ext.getCmp('LPEW_UAddress_Address').getValue(),

							PAddress_id: Ext.getCmp('LPEW_PAddress_id').getValue(),
							PAddress_Zip: Ext.getCmp('LPEW_PAddress_Zip').getValue(),
							PKLCountry_id: Ext.getCmp('LPEW_PKLCountry_id').getValue(),
							PKLRGN_id: Ext.getCmp('LPEW_PKLRGN_id').getValue(),
							PKLSubRGN_id: Ext.getCmp('LPEW_PKLSubRGN_id').getValue(),
							PKLCity_id: Ext.getCmp('LPEW_PKLCity_id').getValue(),
							PKLTown_id: Ext.getCmp('LPEW_PKLTown_id').getValue(),
							PKLStreet_id: Ext.getCmp('LPEW_PKLStreet_id').getValue(),
							PAddress_House: Ext.getCmp('LPEW_PAddress_House').getValue(),
							PAddress_Corpus: Ext.getCmp('LPEW_PAddress_Corpus').getValue(),
							PAddress_Flat: Ext.getCmp('LPEW_PAddress_Flat').getValue(),
							PAddress_Address: Ext.getCmp('LPEW_PAddress_Address').getValue(),

							Okopf_id: Ext.getCmp('LPEW_Okopf_id').getValue(),
							Okved_id: Ext.getCmp('LPEW_Okved_id').getValue(),
							Okogu_id: Ext.getCmp('LPEW_Okogu_id').getValue(),
							Okfs_id: Ext.getCmp('LPEW_Okfs_id').getValue(),
							Org_INN: Ext.getCmp('LPEW_Org_INN').getValue(),
							Org_OGRN: Ext.getCmp('LPEW_Org_OGRN').getValue(),
							Org_KPP: Ext.getCmp('LPEW_Org_KPP').getValue(),
							Org_OKPO: Ext.getCmp('LPEW_Org_OKPO').getValue(),
							Lpu_Okato: Ext.getCmp('LPEW_Lpu_Okato').getValue(),
							Org_OKDP: Ext.getCmp('LPEW_Org_OKDP').getValue(),

							Org_lid: Ext.getCmp('LPEW_Org_lid').getValue(),
							Lpu_RegDate: Ext.getCmp('LPEW_Lpu_RegDate').getRawValue(),
							Lpu_PensRegNum: Ext.getCmp('LPEW_Lpu_PensRegNum').getValue(),
							Lpu_RegNum: Ext.getCmp('LPEW_Lpu_RegNum').getValue(),
							Lpu_FSSRegNum: Ext.getCmp('LPEW_Lpu_FSSRegNum').getValue(),
                            Lpu_DocReg: Ext.getCmp('LPEW_Lpu_DocReg').getValue(),

							LpuSubjectionLevel_id: Ext.getCmp('LPEW_LpuSubjectionLevel_id').getValue(),
							LpuLevel_id: Ext.getCmp('LPEW_LpuLevel_id').getValue(),
							FedLpuLevel_id: Ext.getCmp('LPEW_FedLpuLevel_id').getValue(),
							LpuLevel_cid: Ext.getCmp('LPEW_LpuLevel_cid').getValue(),
							LpuLevelType_id: Ext.getCmp('LPEW_LpuLevelType_id').getValue(),
							LevelType_id: Ext.getCmp('LPEW_LevelType_id').getValue(),
							Lpu_VizitFact: Ext.getCmp('LPEW_Lpu_VizitFact').getValue(),
							Lpu_KoikiFact: Ext.getCmp('LPEW_Lpu_KoikiFact').getValue(),
							Lpu_AmbulanceCount: Ext.getCmp('LPEW_Lpu_AmbulanceCount').getValue(),
							InstitutionLevel_id: Ext.getCmp('LPEW_InstitutionLevel_id').getValue(),
							Lpu_IsLab: Ext.getCmp('LPEW_Lpu_IsLab').getValue() === true ? 2 : 1,
							Lpu_FondOsn: Ext.getCmp('LPEW_Lpu_FondOsn').getValue(),
							Lpu_FondEquip: Ext.getCmp('LPEW_Lpu_FondEquip').getValue(),
							Lpu_gid: Ext.getCmp('LPEW_Lpu_gid').getValue(),

							Lpu_isCMP: Ext.getCmp('LPEW_Lpu_isCMP').getValue(),
							OftenCallers_CallTimes: Ext.getCmp('LPEW_Lpu_OftenCallers_CallTimes').getValue(),
							OftenCallers_SearchDays: Ext.getCmp('LPEW_Lpu_OftenCallers_SearchDays').getValue(),
							OftenCallers_FreeDays: Ext.getCmp('LPEW_Lpu_OftenCallers_FreeDays').getValue(),

//							Lpu_HasLocalPacsServer: (Ext.getCmp('LPEW_Lpu_HasLocalPacsServer').checked) ? '2' : '1',
//							Lpu_LocalPacsServerIP: (Ext.getCmp('LPEW_Lpu_HasLocalPacsServer').checked) ? Ext.getCmp('LPEW_Lpu_LocalPacsServerIP').getValue():'',
//							Lpu_LocalPacsServerAetitle: (Ext.getCmp('LPEW_Lpu_HasLocalPacsServer').checked) ? Ext.getCmp('LPEW_Lpu_LocalPacsServerAetitle').getValue(): '',
//							Lpu_LocalPacsServerPort: (Ext.getCmp('LPEW_Lpu_HasLocalPacsServer').checked) ? Ext.getCmp('LPEW_Lpu_LocalPacsServerPort').getValue() : '',
//							Lpu_LocalPacsServerWadoPort:(Ext.getCmp('LPEW_Lpu_HasLocalPacsServer').checked) ?  Ext.getCmp('LPEW_Lpu_LocalPacsServerWadoPort').getValue() :'',

							Lpu_ErInfo: Ext.getCmp('LPEW_Lpu_ErInfo').getValue(),
							Lpu_IsAllowInternetModeration: (Ext.getCmp('LPEW_IsAllowInternetModeration').checked) ? '2' : '1',
							//PasportMO_IsAssignNasel: (Ext.getCmp('LPEW_PasportMO_IsAssignNasel').checked) ? '2' : '1',
							Lpu_MedCare: Ext.getCmp('LPEW_Lpu_MedCare').getValue(),

							/*CmpStationCategory_id: Ext.getCmp('LPEW_Lpu_CmpStationCategory_id').getValue(),*/

							PasportMO_KolServ: Ext.getCmp('LPEW_PasportMO_KolServ').getValue(),
							PasportMO_KolServSel: Ext.getCmp('LPEW_PasportMO_KolServSel').getValue(),
							PasportMO_KolServDet: Ext.getCmp('LPEW_PasportMO_KolServDet').getValue(),
							PasportMO_KolCmpMes: Ext.getCmp('LPEW_PasportMO_KolCmpMes').getValue(),
							PasportMO_KolCmpPay: Ext.getCmp('LPEW_PasportMO_KolCmpPay').getValue(),
							PasportMO_KolCmpWage: Ext.getCmp('LPEW_PasportMO_KolCmpWage').getValue(),

                             PasportMO_IsTerLimited: Ext.getCmp('LPEW_PasportMO_IsTerLimited').getValue(),
                             PasportMO_MaxDistansePoint: Ext.getCmp('LPEW_PasportMO_MaxDistansePoint').getValue(),
                             PasportMO_IsFenceTer: Ext.getCmp('LPEW_PasportMO_IsFenceTer').getValue(),
                             PasportMO_IsNoFRMP: Ext.getCmp('LPEW_PasportMO_IsNoFRMP').getValue(),
                             PasportMO_IsSecur: Ext.getCmp('LPEW_PasportMO_IsSecur').getValue(),
                             DLocationLpu_id: Ext.getCmp('LPEW_DLocationLpu_id').getValue(),
                             PasportMO_IsMetalDoors: Ext.getCmp('LPEW_PasportMO_IsMetalDoors').getValue(),
                             PasportMO_IsAssignNasel: Ext.getCmp('LPEW_PasportMO_IsAssignNasel').getValue(),
                             PasportMO_IsVideo: Ext.getCmp('LPEW_PasportMO_IsVideo').getValue(),
                             PasportMO_IsAccompanying: Ext.getCmp('LPEW_PasportMO_IsAccompanying').getValue(),

                            Lpu_Founder: Ext.getCmp('LPEW_Lpu_Founder').getValue(),
							LpuBuildingPass_mid: Ext.getCmp('LPEW_LpuBuildingPass_mid').getValue(),
							MOAreaFeature_id: Ext.getCmp('LPEW_MOAreaFeature_id').getValue(),
							LpuOwnership_id: Ext.getCmp('LPEW_LpuOwnership_id').getValue()

						},
						callback: function(options, success, response) {
							loadMask.hide();
							_this.formAction = 'edit';
							if (success)
							{
								if ( response.responseText.length > 0 )
								{
									var resp_obj = Ext.util.JSON.decode(response.responseText);
									if (resp_obj.success == true)
									{

										getGlobalOptions().lpu_email = Ext.getCmp('LPEW_Lpu_Email').getValue()
										if(Ext.getCmp('LPEW_Lpu_id').getValue() == getGlobalOptions().lpu_id){
											getGlobalOptions().lpu_isLab = Ext.getCmp('LPEW_Lpu_IsLab').getValue() === true ? 2 : 1;
										}
										Ext.getCmp('LpuPassportEditWindow').hide();

									}
									else
									{

									}
								}
							}
						}
					});
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'LPEW_SaveButton',
				tabIndex: TABINDEX_LPEW + 12,
				text: langs('Сохранить')
			}, {
				text: langs('Печать'),
				iconCls: 'print16',
				tabIndex: TABINDEX_LPEW + 12,
				menu: [{
					handler: function() {
						window.open('/?c=LpuPassport&m=printLpuPassportER&Lpu_id=' + this.Lpu_id, '_blank');
					}.createDelegate(this),
					iconCls: 'print16',
					id: 'LPEW_PrintButton',
					text: langs('Печать данных по регистратуре')
				}, {
					handler: function() {

						// var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/PassportLpu.rptdesign&Lpu_id='+this.Lpu_id+'&__format=xls';
						// window.open(url, '_blank');

						printBirt({
							'Report_FileName': 'PassportLpu.rptdesign',
							'Report_Params': '&Lpu_id='+this.Lpu_id,
							'Report_Format': 'xls'
						});

					}.createDelegate(this),
					iconCls: 'print16',
					id: 'LPEW_LpuPassportReportButton',
					text: langs('Мониторинг паспортов МО')
				}]
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'LPEW_CancelButton',
/*
				onShiftTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
*/
				onTabAction: function () {
					//this.findById('ERPSIF_EvnRecept_Ser').focus(true, 100);
				}.createDelegate(this),
				tabIndex: TABINDEX_LPEW + 12,
				text: langs('Закрыть')
			}],
			items: [new Ext.Panel({
				frame: true,
				layout: 'column',
				region: 'north',
				id: 'LPEW_panel',
				bodyStyle: 'padding: 5px',
				autoHeight: true,
				items:
				[new Ext.form.FormPanel({
					border: false,
					id: 'LPEW_panelForm',
					//bodyStyle:'background:#DFE8F6;padding-right:5px;',
					columnWidth: .50,
					labelWidth: 240,
					items:
					[{
						id: 'LPEW_Lpu_id',
						name: 'Lpu_id',
						xtype: 'hidden',
						value: '0'
					},{
						id: 'LPEW_Org_id',
						name: 'Org_id',
						xtype: 'hidden'
					},{
						id: 'LPEW_Lpu_isCMP',
						name: 'Lpu_isCMP',
						xtype: 'hidden',
						value: '0'
					}, {
						id: 'LPEW_Server_id',
						name: 'Server_id',
						xtype: 'hidden',
						value: '0'
					}, {
						disabled: true,
						allowBlank: false,
						fieldLabel: langs('Наименование МО'),
						id: 'LPEW_Lpu_Name',
						name: 'Lpu_Name',
						xtype: 'textfield',
						anchor: '100%',
						tabIndex: 990
					}, {
						fieldLabel: langs('Краткое наименование МО'),
						disabled: true,
						allowBlank: false,
						id: 'LPEW_Lpu_Nick',
						name: 'Lpu_Nick',
						xtype: 'textfield',
						anchor: '100%',
						tabIndex: 990
					}, {
						fieldLabel: langs('Код ОУЗ'),
						disabled: true,
						allowBlank: true,
						id: 'LPEW_Lpu_Ouz',
						name: 'Lpu_Ouz',
						autoCreate: {tag: "input", maxLength: "7", autocomplete: "off"},
						maskRe: /[0-9]/,
						xtype: 'textfield',
						anchor: '100%',
						tabIndex: 990
					}, {
						fieldLabel: langs('Федеральный реестровый код МО'),
						disabled: true,
						allowBlank: true,
						autoCreate: {tag: "input",  maxLength: "6", autocomplete: "off"},
						id: 'LPEW_Lpu_f003mcod',
						name: 'Lpu_f003mcod',
						listeners:{
							change: function(field,value){
								if(getGlobalOptions().region.nick == 'ekb')
								{
									var RegNomN2 = Ext.getCmp('LPEW_Lpu_RegNomN2').getValue();
									if(Ext.isEmpty(RegNomN2))
									{
										Ext.getCmp('LPEW_Lpu_RegNomN2').setValue(value.slice(2));
									}
								}
							}
						},
						xtype: 'textfield',
						anchor: '100%',
						tabIndex: 995
					}, {
						fieldLabel: langs('Региональный реестровый код МО'),
						disabled: true,
						allowBlank: true,
						autoCreate: {tag: "input",  maxLength: "6", autocomplete: "off"},
						id: 'LPEW_Lpu_RegNomN2',
						name: 'Lpu_RegNomN2',
						xtype: 'textfield',
						anchor: '100%',
						listeners:{
							change: function(field,value){
								if(getGlobalOptions().region.nick == 'ekb')
								{
									var f003mcod = Ext.getCmp('LPEW_Lpu_f003mcod').getValue();
									if(Ext.isEmpty(value) && !Ext.isEmpty(f003mcod))
									{
										Ext.getCmp('LPEW_Lpu_RegNomN2').setValue(f003mcod.slice(2));
									}
								}
							}
						},
						tabIndex: 995
					}]
				}),
				{
					// Правая часть
					layout: 'form',
					border: false,
					bodyStyle:'padding-left:5px;',
					columnWidth: .40,
					labelWidth: 180,
					items:
					[]
				}]
			}),
			new Ext.TabPanel({
				border: false,
				region: 'center',
				id: 'LpuPassportEditWindowTab',
				activeTab: 0,
				autoScroll: true,
				enableTabScroll:true,
				defaults: {bodyStyle:'width:100%;'},
				layoutOnTabChange: true,
				items:
				[{
					title: langs('1. Идентификация'),
					layout: 'fit',
					id: 'tab_identification',
					iconCls: 'info16',
					//bodyStyle:'padding: 5px 5px 0',
					border:false,
					items:
					new Ext.form.FormPanel({
						autoScroll: true,
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						frame: false,
						id: 'Lpu_IdentificationPanel',
						labelAlign: 'right',
						labelWidth: 180,
						items: [
						new sw.Promed.Panel({
							autoHeight: true,
							style: 'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							region: 'north',
							id: 'Lpu_IdentificationEditForm',
							layout: 'form',
							title: langs('1. Идентификация'),
							items: [{
								xtype: 'panel',
								layout: 'column',
								border: false,
								bodyStyle:'background:#DFE8F6;padding:5px;',
								items: [{// Левая часть
									layout: 'form',
									border: false,
									bodyStyle:'background:#DFE8F6;padding-right:5px;',
									columnWidth: .50,
									labelWidth: 180,
									items:
									[{
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										disabled: true,
										allowBlank: false,
										fieldLabel: langs('Дата начала деятельности'),
										format: 'd.m.Y',
										id: 'LPEW_Lpu_begDate',
										name: 'Lpu_begDate',
										tabIndex: 1100
									}, {
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										disabled: true,
										fieldLabel: langs('Дата закрытия'),
										format: 'd.m.Y',
										id: 'LPEW_Lpu_endDate',
										name: 'Lpu_endDate',
										tabIndex: 1100
									}, {
										fieldLabel: langs('Правопреемник'),
										allowBlank: true,
										id: 'LPEW_Lpu_pid',
										hiddenName: 'Lpu_pid',
										disabled: true,
										xtype: 'swlpulocalcombo',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: langs('Наследователь'),
										allowBlank: true,
										id: 'LPEW_Lpu_nid',
										hiddenName: 'Lpu_nid',
										disabled: true,
										xtype: 'swlpulocalcombo',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: langs('Адрес электронной почты'),
										id: 'LPEW_Lpu_Email',
										name: 'Lpu_Email',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: langs('Адрес сайта'),
										id: 'LPEW_Lpu_Www',
										name: 'Lpu_Www',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									},
									{
										fieldLabel: langs('Телефон'),
										id: 'LPEW_Lpu_Phone',
										name: 'Lpu_Phone',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									},
									{
										fieldLabel: langs('Время работы'),
										id: 'LPEW_Lpu_Worktime',
										name: 'Lpu_Worktime',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: langs('Наименование МО для ЛВН'),
										allowBlank: true, //!(getRegionNick().inlist([ 'astra' ])),
										id: 'LPEW_Lpu_StickNick',
										name: 'Lpu_StickNick',
										xtype: 'textfield',
										anchor: '100%',
										autoCreate: {tag: "input", maxLength: "38", autocomplete: "off"},
										tabIndex: 1100
									}, {
										fieldLabel: langs('Адрес МО для ЛВН'),
										allowBlank: true, //!(getRegionNick().inlist([ 'astra', 'ufa' ])),
										id: 'LPEW_Lpu_StickAddress',
										name: 'Lpu_StickAddress',
										xtype: 'textfield',
										disabled: true,
										autoCreate: {tag: "input", maxLength: "38", autocomplete: "off"},
										anchor: '100%',
										tabIndex: 1100
									}, {
										fieldLabel: langs('Код по ОКАТО'),
										allowBlank: false,
										id: 'LPEW_Lpu_Okato',
										name: 'Lpu_Okato',
										xtype: 'textfield',
										disabled: true,
										autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1100
									}/*, {
										fieldLabel: langs('Тип МО'),
										allowBlank: false,
										id: 'LPEW_LpuType_id',
										hiddenName: 'LpuType_id',
										disabled: true,
										xtype: 'swlputypecombo',
										anchor: '100%',
										tabIndex: 1100
									}*/, {
										id: 'LPEW_LpuType_id',
										name: 'LpuType_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_LpuPmuType_id',
										name: 'LpuPmuType_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_LpuPmuClass_id',
										name: 'LpuPmuClass_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_Oktmo_id',
										name: 'Oktmo_id',
										xtype: 'hidden'
									}, {
										allowBlank: false,
										allowLowLevelRecordsOnly: false,
										anchor: '100%',
										fieldLabel: langs('Код ОКТМО'),
										id: 'LPEW_Oktmo_Name',
										name: 'Oktmo_Name',
										object: 'Oktmo',
										selectionWindowParams: {
											height: 500,
											title: langs('Код ОКТМО'),
											width: 600
										},
										showCodeMode: 2,
										useCodeOnly: true,
										useNameWithPath: false,
										valueFieldId: 'LPEW_Oktmo_id',
										xtype: 'swtreeselectionfield'
									}, {
										allowBlank: true, //!(getRegionNick().inlist([ 'astra' ])),
										anchor: '100%',
										disabled: true,
										fieldLabel: langs('Тип МО'),
										id: 'LPEW_LpuType_Name',
										name: 'LpuType_Name',
										object: 'LpuType',
										selectionWindowParams: {
											height: 500,
											title: langs('Тип МО'),
											width: 600
										},
										// useNameWithPath: false,
										valueFieldId: 'LPEW_LpuType_id',
										xtype: 'swtreeselectionfield'
									}, {
										//allowBlank: false,
										anchor: '100%',
										fieldLabel: langs('Тип МО (ПМУ/ФРМП)'),
										id: 'LPEW_LpuPmuType_Name',
										name: 'LpuPmuType_Name',
										object: 'LpuPmuType',
										scheme: 'fed',
										selectionWindowParams: {
											height: 500,
											onlyActual: true,
											title: langs('Тип МО (ПМУ/ФРМП)'),
											treeSortMode: '0012',
											width: 600
										},
										// useNameWithPath: false,
										valueFieldId: 'LPEW_LpuPmuType_id',
										xtype: 'swtreeselectionfield'
									}, {
										//allowBlank: false,
										anchor: '100%',
										fieldLabel: langs('Тип МО (ИЭМК)'),
										id: 'LPEW_LpuPmuClass_Name',
										name: 'LpuPmuClass_Name',
										object: 'LpuPmuClass',
										scheme: 'nsi',
										selectionWindowParams: {
											height: 500,
											title: langs('Тип МО (ПМУ/ФРМП)'),
											width: 600
										},
										// useNameWithPath: false,
										valueFieldId: 'LPEW_LpuPmuClass_id',
										xtype: 'swtreeselectionfield'
									}, {
										fieldLabel: langs('Тип МО по возрасту'),
										allowBlank: true,
										id: 'LPEW_LpuAgeType_id',
										hiddenName: 'LpuAgeType_id',
										disabled: true,
										xtype: 'swlpuagetypecombo',
										anchor: '100%',
										tabIndex: 1100
									},{
                                        anchor: '100%',
                                        comboSubject: 'DepartAffilType',
                                        fieldLabel: langs('Ведомственная принадлежность'),
                                        hiddenName: 'DepartAffilType_id',
                                        id: 'LPEW_DepartAffilType_id',
                                        tabIndex: 1100,
                                        allowBlank: false,
                                        xtype: 'swcommonsprcombo'
									},{
										disabled: true,
										fieldLabel: langs('Внутренний код'),
										id: 'LPEW_Lpu_InterCode',
										name: 'Lpu_InterCode',
										width: 100,
										xtype: 'textfield'
									},{
                                        layout: 'form',
                                        border: false,
                                        hidden: getRegionNick() != 'perm',
                                        bodyStyle:'background:#DFE8F6;padding-right:0px;',
                                        //labelWidth: 180,
                                        items: [{
											fieldLabel: langs('ТОУЗ'),
											xtype: 'sworgcombo',
											autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
											anchor: '60%',
											id: 'LPEW_Org_tid',
											hiddenName: 'Org_tid',
											tabIndex: 1110,
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
																inp.ownerCt.ownerCt.findField('OrgLic_id').setRawValue(null);
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
													object: '',
													OrgType_id: 15,
													onSelect: function(orgData) {
														if ( orgData.Org_id > 0 )
														{
															combo.getStore().load({
																params: {
																	OrgType: '',
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
										}]
                                    },{
										layout: 'form',
										border: false,
										hidden: !getRegionNick().inlist(['perm','msk']),
										bodyStyle:'background:#DFE8F6;padding-right:0px;',
										items: [{
											id: 'LPEW_TOUZType_id',
											allowBlank: true,
											comboSubject: 'TOUZType',
											prefix: 'passport_',
											tabIndex: 1109,
											anchor: '60%',
											hiddenName: 'TOUZType_id',
											fieldLabel: langs('Территория (отдел) ТОУЗ'),
											xtype: 'swcommonsprcombo'
										}]
									}]
								},
								{
									// Правая часть
									layout: 'form',
									border: false,
									bodyStyle:'background:#DFE8F6;padding-right:5px;',
									columnWidth: .50,
									labelWidth: 180,
									items:
									[new sw.Promed.TripleTriggerField ({
										//xtype: 'trigger',
										anchor: '100%',
										enableKeyEvents: true,
										disabled: true,
										fieldLabel: langs('Юридический адрес'),
										id: 'LPEW_UAddress_AddressText',
										listeners: {
											'keydown': function(inp, e) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
													if ( e.F4 == e.getKey() )
														inp.onTrigger1Click();
													if ( e.F2 == e.getKey() )
														inp.onTrigger2Click();
													if ( e.DELETE == e.getKey() && e.altKey)
														inp.onTrigger3Click();

													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.browserEvent.returnValue = false;
													e.returnValue = false;

													if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											},
											'keyup': function( inp, e ) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.browserEvent.returnValue = false;
													e.returnValue = false;

												if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										name: 'UAddress_AddressText',
										onTrigger2Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_UAddress_Zip').setValue(ownerForm.findById('LPEW_PAddress_Zip').getValue());
											ownerForm.findById('LPEW_UKLCountry_id').setValue(ownerForm.findById('LPEW_PKLCountry_id').getValue());
											ownerForm.findById('LPEW_UKLRGN_id').setValue(ownerForm.findById('LPEW_PKLRGN_id').getValue());
											ownerForm.findById('LPEW_UKLSubRGN_id').setValue(ownerForm.findById('LPEW_PKLSubRGN_id').getValue());
											ownerForm.findById('LPEW_UKLCity_id').setValue(ownerForm.findById('LPEW_PKLCity_id').getValue());
											ownerForm.findById('LPEW_UKLTown_id').setValue(ownerForm.findById('LPEW_PKLTown_id').getValue());
											ownerForm.findById('LPEW_UKLStreet_id').setValue(ownerForm.findById('LPEW_PKLStreet_id').getValue());
											ownerForm.findById('LPEW_UAddress_House').setValue(ownerForm.findById('LPEW_PAddress_House').getValue());
											ownerForm.findById('LPEW_UAddress_Corpus').setValue(ownerForm.findById('LPEW_PAddress_Corpus').getValue());
											ownerForm.findById('LPEW_UAddress_Flat').setValue(ownerForm.findById('LPEW_PAddress_Flat').getValue());
											ownerForm.findById('LPEW_UAddress_Address').setValue(ownerForm.findById('LPEW_PAddress_Address').getValue());
											ownerForm.findById('LPEW_UAddress_AddressText').setValue(ownerForm.findById('LPEW_PAddress_AddressText').getValue());
										},
										onTrigger3Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_UAddress_Zip').setValue('');
											ownerForm.findById('LPEW_UKLCountry_id').setValue('');
											ownerForm.findById('LPEW_UKLRGN_id').setValue('');
											ownerForm.findById('LPEW_UKLSubRGN_id').setValue('');
											ownerForm.findById('LPEW_UKLCity_id').setValue('');
											ownerForm.findById('LPEW_UKLTown_id').setValue('');
											ownerForm.findById('LPEW_UKLStreet_id').setValue('');
											ownerForm.findById('LPEW_UAddress_House').setValue('');
											ownerForm.findById('LPEW_UAddress_Corpus').setValue('');
											ownerForm.findById('LPEW_UAddress_Flat').setValue('');
											ownerForm.findById('LPEW_UAddress_Address').setValue('');
											ownerForm.findById('LPEW_UAddress_AddressText').setValue('');
										},
										onTrigger1Click: function() {
											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											getWnd('swAddressEditWindow').show({
												fields: {
													Address_ZipEdit: ownerForm.findById('LPEW_UAddress_Zip').value,
													KLCountry_idEdit: ownerForm.findById('LPEW_UKLCountry_id').value,
													KLRgn_idEdit: ownerForm.findById('LPEW_UKLRGN_id').value,
													KLSubRGN_idEdit: ownerForm.findById('LPEW_UKLSubRGN_id').value,
													KLCity_idEdit: ownerForm.findById('LPEW_UKLCity_id').value,
													KLTown_idEdit: ownerForm.findById('LPEW_UKLTown_id').value,
													KLStreet_idEdit: ownerForm.findById('LPEW_UKLStreet_id').value,
													Address_HouseEdit: ownerForm.findById('LPEW_UAddress_House').value,
													Address_CorpusEdit: ownerForm.findById('LPEW_UAddress_Corpus').value,
													Address_FlatEdit: ownerForm.findById('LPEW_UAddress_Flat').value,
													Address_AddressEdit: ownerForm.findById('LPEW_UAddress_Address').value
												},
												callback: function(values) {
													ownerForm.findById('LPEW_UAddress_Zip').setValue(values.Address_ZipEdit);
													ownerForm.findById('LPEW_UKLCountry_id').setValue(values.KLCountry_idEdit);
													ownerForm.findById('LPEW_UKLRGN_id').setValue(values.KLRgn_idEdit);
													ownerForm.findById('LPEW_UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
													ownerForm.findById('LPEW_UKLCity_id').setValue(values.KLCity_idEdit);
													ownerForm.findById('LPEW_UKLTown_id').setValue(values.KLTown_idEdit);
													ownerForm.findById('LPEW_UKLStreet_id').setValue(values.KLStreet_idEdit);
													ownerForm.findById('LPEW_UAddress_House').setValue(values.Address_HouseEdit);
													ownerForm.findById('LPEW_UAddress_Corpus').setValue(values.Address_CorpusEdit);
													ownerForm.findById('LPEW_UAddress_Flat').setValue(values.Address_FlatEdit);
													ownerForm.findById('LPEW_UAddress_Address').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_UAddress_AddressText').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_UAddress_AddressText').focus(true, 500);
												},
												onClose: function() {
													ownerForm.findById('LPEW_UAddress_AddressText').focus(true, 500);
												}
											})
										},
										readOnly: true,
										tabIndex: 1105,
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-equil-trigger',
										trigger3Class: 'x-form-clear-trigger',
										width: 395
									}),
									new sw.Promed.TripleTriggerField ({
										//xtype: 'trigger',
										anchor: '100%',
										enableKeyEvents: true,
										disabled: true,
										fieldLabel: langs('Фактический адрес'),
										id: 'LPEW_PAddress_AddressText',
										listeners: {
											'keydown': function(inp, e) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
													if ( e.F4 == e.getKey() )
														inp.onTrigger1Click();
													if ( e.F2 == e.getKey() )
														inp.onTrigger2Click();
													if ( e.DELETE == e.getKey() && e.altKey)
														inp.onTrigger3Click();

													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.browserEvent.returnValue = false;
													e.returnValue = false;

													if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											},
											'keyup': function( inp, e ) {
												if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
													if ( e.browserEvent.stopPropagation )
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;

													if ( e.browserEvent.preventDefault )
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;

													e.browserEvent.returnValue = false;
													e.returnValue = false;

													if ( Ext.isIE ) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										name: 'PAddress_AddressText',
										onTrigger1Click: function() {
											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											getWnd('swAddressEditWindow').show({
												fields: {
													Address_ZipEdit: ownerForm.findById('LPEW_PAddress_Zip').value,
													KLCountry_idEdit: ownerForm.findById('LPEW_PKLCountry_id').value,
													KLRgn_idEdit: ownerForm.findById('LPEW_PKLRGN_id').value,
													KLSubRGN_idEdit: ownerForm.findById('LPEW_PKLSubRGN_id').value,
													KLCity_idEdit: ownerForm.findById('LPEW_PKLCity_id').value,
													KLTown_idEdit: ownerForm.findById('LPEW_PKLTown_id').value,
													KLStreet_idEdit: ownerForm.findById('LPEW_PKLStreet_id').value,
													Address_HouseEdit: ownerForm.findById('LPEW_PAddress_House').value,
													Address_CorpusEdit: ownerForm.findById('LPEW_PAddress_Corpus').value,
													Address_FlatEdit: ownerForm.findById('LPEW_PAddress_Flat').value,
													Address_AddressEdit: ownerForm.findById('LPEW_PAddress_Address').value
												},
												callback: function(values) {
													ownerForm.findById('LPEW_PAddress_Zip').setValue(values.Address_ZipEdit);
													ownerForm.findById('LPEW_PKLCountry_id').setValue(values.KLCountry_idEdit);
													ownerForm.findById('LPEW_PKLRGN_id').setValue(values.KLRgn_idEdit);
													ownerForm.findById('LPEW_PKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
													ownerForm.findById('LPEW_PKLCity_id').setValue(values.KLCity_idEdit);
													ownerForm.findById('LPEW_PKLTown_id').setValue(values.KLTown_idEdit);
													ownerForm.findById('LPEW_PKLStreet_id').setValue(values.KLStreet_idEdit);
													ownerForm.findById('LPEW_PAddress_House').setValue(values.Address_HouseEdit);
													ownerForm.findById('LPEW_PAddress_Corpus').setValue(values.Address_CorpusEdit);
													ownerForm.findById('LPEW_PAddress_Flat').setValue(values.Address_FlatEdit);
													ownerForm.findById('LPEW_PAddress_Address').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_PAddress_AddressText').setValue(values.Address_AddressEdit);
													ownerForm.findById('LPEW_PAddress_AddressText').focus(true, 500);
												},
												onClose: function() {
													ownerForm.findById('LPEW_PAddress_AddressText').focus(true, 500);
												}
											})
										},
										onTrigger2Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_PAddress_Zip').setValue(ownerForm.findById('LPEW_UAddress_Zip').getValue());
											ownerForm.findById('LPEW_PKLCountry_id').setValue(ownerForm.findById('LPEW_UKLCountry_id').getValue());
											ownerForm.findById('LPEW_PKLRGN_id').setValue(ownerForm.findById('LPEW_UKLRGN_id').getValue());
											ownerForm.findById('LPEW_PKLSubRGN_id').setValue(ownerForm.findById('LPEW_UKLSubRGN_id').getValue());
											ownerForm.findById('LPEW_PKLCity_id').setValue(ownerForm.findById('LPEW_UKLCity_id').getValue());
											ownerForm.findById('LPEW_PKLTown_id').setValue(ownerForm.findById('LPEW_UKLTown_id').getValue());
											ownerForm.findById('LPEW_PKLStreet_id').setValue(ownerForm.findById('LPEW_UKLStreet_id').getValue());
											ownerForm.findById('LPEW_PAddress_House').setValue(ownerForm.findById('LPEW_UAddress_House').getValue());
											ownerForm.findById('LPEW_PAddress_Corpus').setValue(ownerForm.findById('LPEW_UAddress_Corpus').getValue());
											ownerForm.findById('LPEW_PAddress_Flat').setValue(ownerForm.findById('LPEW_UAddress_Flat').getValue());
											ownerForm.findById('LPEW_PAddress_Address').setValue(ownerForm.findById('LPEW_UAddress_Address').getValue());
											ownerForm.findById('LPEW_PAddress_AddressText').setValue(ownerForm.findById('LPEW_UAddress_AddressText').getValue());
										},
										onTrigger3Click: function() {
											var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
											ownerForm.findById('LPEW_PAddress_Zip').setValue('');
											ownerForm.findById('LPEW_PKLCountry_id').setValue('');
											ownerForm.findById('LPEW_PKLRGN_id').setValue('');
											ownerForm.findById('LPEW_PKLSubRGN_id').setValue('');
											ownerForm.findById('LPEW_PKLCity_id').setValue('');
											ownerForm.findById('LPEW_PKLTown_id').setValue('');
											ownerForm.findById('LPEW_PKLStreet_id').setValue('');
											ownerForm.findById('LPEW_PAddress_House').setValue('');
											ownerForm.findById('LPEW_PAddress_Corpus').setValue('');
											ownerForm.findById('LPEW_PAddress_Flat').setValue('');
											ownerForm.findById('LPEW_PAddress_Address').setValue('');
											ownerForm.findById('LPEW_PAddress_AddressText').setValue('');
										},
										readOnly: true,
										tabIndex: 1105,
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-equil-trigger',
										trigger3Class: 'x-form-clear-trigger',
										width: 395
									}),
									{
										id: 'LPEW_PAddress_id',
										name: 'PAddress_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Zip',
										name: 'PAddress_Zip',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLCountry_id',
										name: 'PKLCountry_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLRGN_id',
										name: 'PKLRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLSubRGN_id',
										name: 'PKLSubRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLCity_id',
										name: 'PKLCity_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLTown_id',
										name: 'PKLTown_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PKLStreet_id',
										name: 'PKLStreet_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_House',
										name: 'PAddress_House',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Corpus',
										name: 'PAddress_Corpus',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Flat',
										name: 'PAddress_Flat',
										xtype: 'hidden'
									}, {
										id: 'LPEW_PAddress_Address',
										name: 'PAddress_Address',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_id',
										name: 'UAddress_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Zip',
										name: 'UAddress_Zip',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLCountry_id',
										name: 'UKLCountry_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLRGN_id',
										name: 'UKLRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLSubRGN_id',
										name: 'UKLSubRGN_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLCity_id',
										name: 'UKLCity_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLTown_id',
										name: 'UKLTown_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UKLStreet_id',
										name: 'UKLStreet_id',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_House',
										name: 'UAddress_House',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Corpus',
										name: 'UAddress_Corpus',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Flat',
										name: 'UAddress_Flat',
										xtype: 'hidden'
									}, {
										id: 'LPEW_UAddress_Address',
										name: 'UAddress_Address',
										xtype: 'hidden'
									}, {
										fieldLabel: langs('ОКФС'),
										disabled: true,
										allowBlank: false,
										id: 'LPEW_Okfs_id',
										hiddenName: 'Okfs_id',
										xtype: 'swokfscombo',
										anchor: '100%',
										tabIndex: 1105
									}, {
										fieldLabel: langs('ОКОПФ'),
										disabled: true,
										allowBlank: false,
										id: 'LPEW_Okopf_id',
										hiddenName: 'Okopf_id',
										xtype: 'swokopfcombo',
										anchor: '100%',
										tabIndex: 1105
									}, {
										fieldLabel: langs('ОКПО'),
										id: 'LPEW_Org_OKPO',
										allowBlank: false,
										name: 'Org_OKPO',
										autoCreate: {tag: "input",  maxLength: "10", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1105
									}, {
										fieldLabel: langs('ИНН'),
										id: 'LPEW_Org_INN',
										allowBlank: false,
										name: 'Org_INN',
										disabled: true,
										autoCreate: {tag: "input",  maxLength: "12", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1105
									}, {
										fieldLabel: langs('КПП'),
										id: 'LPEW_Org_KPP',
										allowBlank: false,
										name: 'Org_KPP',
										disabled: true,
										autoCreate: {tag: "input", maxLength: "9", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										minLength: 9,
										minLengthText: langs('Длина поля должна быть равна 9 символам'),
										tabIndex: 1105
									}, {
										fieldLabel: langs('ОГРН'),
										id: 'LPEW_Org_OGRN',
										allowBlank: false,
										name: 'Org_OGRN',
										disabled: true,
										autoCreate: {tag: "input",  maxLength: "15", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1105
									}, {
										fieldLabel: langs('ОКДП'),
										id: 'LPEW_Org_OKDP',
										name: 'Org_OKDP',
										autoCreate: {tag: "input",  maxLength: "7", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										anchor: '100%',
										tabIndex: 1105
									}, {
										fieldLabel: langs('ОКОГУ'),
										id: 'LPEW_Okogu_id',
										allowBlank: true, // Необязательное согласно https://redmine.swan.perm.ru/issues/10300
										hiddenName: 'Okogu_id',
										tabIndex: 1105,
										xtype: 'swokogucombo',
										anchor: '100%'
									}, {
										fieldLabel: langs('ОКВЭД'),
										id: 'LPEW_Okved_id',
										allowBlank: false,
										hiddenName: 'Okved_id',
										tabIndex: 1105,
										xtype: 'swokvedcombo',
										anchor: '100%'
									}, {
										fieldLabel: langs('Районный коэффициент'),
										id: 'LPEW_Lpu_DistrictRate',
										name: 'Lpu_DistrictRate',
										maskRe: /[0-9]/,
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1105
									},
									{
										fieldLabel: langs('ОИД'),
										id: 'LPEW_PassportToken_tid',
										name: 'PassportToken_tid',
                                        disabled: true,
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1105
									},
									{
										fieldLabel: langs('Код ОНМСЗ'),
										id: 'LPEW_Org_ONMSZCode',
										name: 'Org_ONMSZCode',
										disabled: true,
										hidden: getRegionNick() == 'kz',
										xtype: 'textfield',
										anchor: '100%',
										tabIndex: 1105
									},
									{
										name: 'LpuOwnership_tid',
										id: 'LPEW_LpuOwnership_id',
										tabIndex: 1105,
										anchor: '100%',
										allowBlank: false,
										xtype: 'swLpuOwnershipCombo',
										fieldLabel: 'Организационная форма',
										listeners:{
											change: function(field,value){
												/*
												var FounderCombo = Ext.getCmp('LPEW_Lpu_Founder');
												var LpugidCombo = Ext.getCmp('LPEW_Lpu_gid');
												var DepartAffilTypeCombo = Ext.getCmp('LPEW_DepartAffilType_id');
												//для поля "учредитель"
												var setAB = (field.lastSelectionText == 'Частная') ? false : true;
												FounderCombo.setAllowBlank(setAB);
												//для поля "ведомственная принадлежность"
												if( value==1 && !LpugidCombo.getValue() ){
													DepartAffilTypeCombo.setAllowBlank(false);
												}else{
													DepartAffilTypeCombo.setAllowBlank(true);
												}
												*/
												var DepartAffilTypeCombo = Ext.getCmp('LPEW_DepartAffilType_id');	//ведомственная принадлежность
												var FounderCombo = Ext.getCmp('LPEW_Lpu_Founder');					//Учредитель
												var LpugidCombo = Ext.getCmp('LPEW_Lpu_gid');						//головное учреждение
												if(value >1){
													FounderCombo.enable();
													FounderCombo.setAllowBlank(false);
													DepartAffilTypeCombo.setAllowBlank(true);
												}else{
													FounderCombo.disable();
													FounderCombo.setAllowBlank(true);
													if(!LpugidCombo.getValue()){
														DepartAffilTypeCombo.setAllowBlank(false);
													}else{
														DepartAffilTypeCombo.setAllowBlank(true);
													}
												}
											}
										},
									}, 
									{
										name: 'MOAreaFeature_tid',
										id: 'LPEW_MOAreaFeature_id',
										tabIndex: 1105,
										anchor: '100%',
										allowBlank: false,
										xtype: 'swMOAreaFeatureCombo',
									},  
									{
										hiddenName: 'LpuBuildingPass_mid',
										id: 'LPEW_LpuBuildingPass_mid',
										tabIndex: 1105,
										anchor: '100%',
										xtype: 'swLpuBuildingPasscombo'
									},
									{
                                        id: 'LPEW_Lpu_IsSecret',
                                        fieldLabel: langs('Особый статус'),
                                        tabIndex: 1105,
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'Lpu_IsSecret',
										listeners: {
                                        	'change': function(field, value) {
												var msg = value
													?'Установка флага для всей МО изменит сохраненные настройки в поле "СПИД-центр" для всех подразделений этой МО. Продолжить?'
													:'Снятие флага для всей МО изменит сохраненные настройки в поле "СПИД-центр" для всех подразделений этой МО. Продолжить?';
												sw.swMsg.show({
													buttons: sw.swMsg.YESNO,
													icon: sw.swMsg.WARNING,
													title: langs('Внимание'),
													msg: langs(msg),
													fn: function(button) {
														if (button == 'no') {
															field.setValue(!value);
														}
													}
												});
											}
										}
                                    }]
								},{
									autoHeight: true,
									title: langs('Данные о регистрации'),
									bodyStyle:'padding: 10px;',
									xtype: 'fieldset',
									columnWidth: 1,
									labelWidth: 180,
									items:
									[{
										fieldLabel: langs('Орган'),
										xtype: 'sworgcombo',
										autoCreate: {tag: "input", maxLength: "20", autocomplete: "off"},
										anchor: '60%',
										id: 'LPEW_Org_lid',
										hiddenName: 'Org_lid',
										tabIndex: 1110,
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
															inp.ownerCt.ownerCt.findField('OrgLic_id').setRawValue(null);
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
												object: getRegionNick()=='kareliya' ? '' : 'lic',
												enableOrgType: getRegionNick()=='kareliya' ? true : undefined,
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														combo.getStore().load({
															params: {
																OrgType: getRegionNick()=='kareliya' ? '' : 'lic',
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
									},{
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										fieldLabel: langs('Дата регистрации'),
										format: 'd.m.Y',
										id: 'LPEW_Lpu_RegDate',
										name: 'Lpu_RegDate',
										tabIndex: 1110
									},{
                                        fieldLabel: langs('Наименование регистрационного документа'),
                                        xtype: 'textfield',
                                        id: 'LPEW_Lpu_DocReg',
                                        name: 'Lpu_DocReg',
                                        anchor: '60%',
                                        tabIndex: 1110
                                    },{
										fieldLabel: langs('Рег. Номер'),
										autoCreate: {tag: "input",  maxLength: "13", autocomplete: "off"},
										xtype: 'textfield',
										maskRe: /[0-9]/,
										id: 'LPEW_Lpu_RegNum',
										name: 'Lpu_RegNum',
										anchor: '60%',
										tabIndex: 1110
									},{
										fieldLabel: langs('Рег. Номер в ПФ РФ'),
										autoCreate: {tag: "input",  maxLength: "12", autocomplete: "off"},
										xtype: 'textfield',
										id: 'LPEW_Lpu_PensRegNum',
										name: 'Lpu_PensRegNum',
										maskRe: /[0-9]/,
										anchor: '60%',
										tabIndex: 1110
									},{
										fieldLabel: langs('Рег. Номер в ФСС'),
										autoCreate: {tag: "input",  maxLength: "10", autocomplete: "off"},
										xtype: 'textfield',
										id: 'LPEW_Lpu_FSSRegNum',
										name: 'Lpu_FSSRegNum',
										maskRe: /[0-9]/,
										anchor: '60%',
										tabIndex: 1110
									},{
										fieldLabel: 'Учредитель',
										xtype: 'textfield',
										id: 'LPEW_Lpu_Founder',
										name: 'Lpu_Founder',
										anchor: '60%',
										tabIndex: 1110
									}]
								}]
							}]
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							style:'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							collapsed: true,
							region: 'north',
							id: 'LPEW_Lpu_OMSPanel',
							layout: 'form',
							title: langs('2. ОМС'),
							listeners: {
								expand: function () {
									this.findById('LPEW_OMSPeriodGrid').removeAll({clearAll: true});
									this.findById('LPEW_OMSPeriodGrid').removeAll({clearAll: true});
									this.findById('LPEW_OMSPeriodGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									autoExpandColumn: 'autoexpand',
									object: 'LpuPeriodOMS',
									editformclassname: 'swLpuPeriodOMSEditWindow',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodOMSGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_OMSPeriodGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodOMS_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodOMS_begDate', type: 'string', header: langs('Дата включения'), width: 120},
										{name: 'LpuPeriodOMS_endDate', type: 'string', header: langs('Дата исключения'), width: 120}
									],
									onLoadData: function () {
										var form = Ext.getCmp('LpuPassportEditWindow');

										if ( form.findById('LPEW_OMSPeriodGrid').getCount() && isSuperAdmin() ) {
											form.findById('LPEW_Lpu_f003mcod').allowBlank = false;
										} else {
											form.findById('LPEW_Lpu_f003mcod').allowBlank = true;
										}

										if ( getRegionNick() == 'astra' ) {
											if ( form.findById('LPEW_OMSPeriodGrid').getCount() > 0 && !Ext.isEmpty(form.findById('LPEW_OMSPeriodGrid').getGrid().getStore().getAt(0).get('LpuPeriodOMS_id'))) {
												form.findById('LPEW_LevelType_id').setAllowBlank(false);
											} else {
												form.findById('LPEW_LevelType_id').setAllowBlank(true);
											}
										}
									}.createDelegate(this),
									onRowSelect: function (sm, index, record) {
									var rec = sm.getSelected();
									if (rec.get('LpuPeriodOMS_id') > 0) {
										var s = _this.findById('LPEW_OMSGrid').getGrid().getStore();
										s.removeAll();
										s.baseParams = {LpuPeriodOMS_pid: rec.get('LpuPeriodOMS_id')};
										s.load({callback:function(q){

											}
										});													//загружаем грид с выбраным справочником
									}
									}
								}),
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add',handler:function(){_this.openLpuOMSWindow('add')}},
										{name: 'action_edit',handler:function(){_this.openLpuOMSWindow('edit')}},
										{name: 'action_view',handler:function(){_this.openLpuOMSWindow('view')}},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									autoExpandColumn: 'autoexpand',
									object: 'LpuPeriodOMS',
									editformclassname: 'swLpuOMSEditWindow',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuOMSGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_OMSGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodOMS_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodOMS_DogNum', type: 'string', header: langs('Номер договора'), width: 120},
										{name: 'LpuPeriodOMS_begDate', type: 'string', header: langs('Дата договора'), width: 120},
										{name: 'Org_Nick', type: 'string', header: langs('Организация'), width:120},
										{name: 'LpuPeriodOMS_RegNumC', type: 'string', header: langs('Код территории МО'), width: 240},
										{name: 'LpuPeriodOMS_RegNumN', type: 'string', header: langs('Регистрационный номер МО'), width: 240},
										{name: 'LpuPeriodOMS_Descr', type: 'string', header: langs('Примечание к договору'), width: 240}
									],
									onLoadData: function () {
										/*var form = Ext.getCmp('LpuPassportEditWindow');

										if ( form.findById('LPEW_OMSPeriodGrid').getCount() && isSuperAdmin() ) {
											form.findById('LPEW_Lpu_f003mcod').allowBlank = false;
										} else {
											form.findById('LPEW_Lpu_f003mcod').allowBlank = true;
										}

										if ( getRegionNick() == 'astra' ) {
											if ( form.findById('LPEW_OMSPeriodGrid').getCount() > 0 && !Ext.isEmpty(form.findById('LPEW_OMSPeriodGrid').getGrid().getStore().getAt(0).get('LpuPeriodOMS_id'))) {
												form.findById('LPEW_LevelType_id').setAllowBlank(false);
											} else {
												form.findById('LPEW_LevelType_id').setAllowBlank(true);
											}
										}*/
									}.createDelegate(this)

								})
							]
						}),
						new sw.Promed.Panel({
							autoHeight: true,
							style:'margin-bottom: 0.5em;',
							border: true,
							collapsible: true,
							collapsed: true,
							region: 'north',
							id: 'LPEW_Lpu_DLOPanel',
							layout: 'form',
							title: langs('3. ЛЛО'),
							listeners: {
								expand: function () {
									this.findById('LPEW_DLOGrid').removeAll({clearAll: true});
									this.findById('LPEW_DLOGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'LpuPeriodDLO',
									editformclassname: 'swLpuPeriodDLOEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodDLOGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_DLOGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodDLO_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodDLO_begDate', type: 'string', header: langs('Дата включения'), width: 120},
										{name: 'LpuPeriodDLO_endDate', type: 'string', header: langs('Дата исключения'), width: 120},
										{name: 'LpuPeriodDLO_Code', type: 'string', hidden: !getRegionNick().inlist(['ufa','msk']), header: langs('Код ЛЛО'), width: 120},
										{name: 'LpuPeriodDLO_Name', type: 'string', hidden: !getRegionNick().inlist(['msk']), header: langs('Наименование'), width: 300}
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
							id: 'LPEW_Lpu_DMSPanel',
							layout: 'form',
							region: 'center',
							title: langs('4. ДМС'),
							listeners: {
								expand: function () {
									this.findById('LPEW_DMSGrid').removeAll({clearAll: true});
									this.findById('LPEW_DMSGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'LpuPeriodDMS',
									editformclassname: 'swLpuPeriodDMSEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodDMSGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_DMSGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodDMS_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodDMS_begDate', type: 'string', header: langs('Дата включения'), width: 120},
										{name: 'LpuPeriodDMS_endDate', type: 'string', header: langs('Дата исключения'), width: 120},
										{name: 'LpuPeriodDMS_DogNum', type: 'string', header: langs('Номер договора'), width: 120}
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
							id: 'LPEW_Lpu_FondHolderPanel',
							layout: 'form',
							title: (getGlobalOptions().region.nick == 'perm') ? (langs('5. Фондодержание')) : (langs('5. Участковая служба')),
							listeners: {
								expand: function () {
									this.findById('LPEW_FondHolderGrid').removeAll({clearAll: true});
									this.findById('LPEW_FondHolderGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'LpuPeriodFondHolder',
									editformclassname: 'swLpuPeriodFondHolderEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadLpuPeriodFondHolderGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_FondHolderGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'LpuPeriodFondHolder_id', type: 'int', header: 'ID', key: true},
										{name: 'LpuPeriodFondHolder_begDate', type: 'string', header: langs('Дата включения'), width: 120},
										{name: 'LpuPeriodFondHolder_endDate', type: 'string', header: langs('Дата исключения'), width: 120},
										{name: 'LpuRegionType_Name', type: 'string', header: langs('Тип участка'), width: 250}
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
							hidden:(getRegionNick()!='astra'),
							region: 'north',
							id: 'LPEW_Lpu_OrgWorkPeriodPanel',
							layout: 'form',
							title: langs('6. Периоды работы в системе Промед'),
							listeners: {
								expand: function () {
									this.findById('LPEW_OrgWorkPeriodGrid').removeAll({clearAll: true});
									this.findById('LPEW_OrgWorkPeriodGrid').loadData({globalFilters:{Org_id: this.Org_id}, params:{Org_id: this.Org_id}});
								}.createDelegate(this)
							},
							items: [
								new sw.Promed.ViewFrame({
									actions: [
										{name: 'action_add'},
										{name: 'action_edit'},
										{name: 'action_view'},
										{name: 'action_delete'},
										{name: 'action_print'}
									],
									object: 'OrgWorkPeriod',
									editformclassname: 'swOrgWorkPeriodEditWindow',
									autoExpandColumn: 'autoexpand',
									autoExpandMin: 150,
									autoLoadData: false,
									border: false,
									dataUrl: '/?c=LpuPassport&m=loadOrgWorkPeriodGrid',
									focusOn: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									focusPrev: {
										name: 'LPEW_CancelButton',
										type: 'button'
									},
									id: 'LPEW_OrgWorkPeriodGrid',
									//pageSize: 100,
									paging: false,
									region: 'center',
									//root: 'data',
									stringfields: [
										{name: 'OrgWorkPeriod_id', type: 'int', header: 'ID', key: true},
										{name: 'OrgWorkPeriod_begDate', type: 'string', header: langs('Дата начала'), width: 120},
										{name: 'OrgWorkPeriod_endDate', type: 'string', header: langs('Дата окончания'), width: 120}
									]
								})
							]
						})

					]})
				},
				{
					title: langs('2. Справочная информация'),
					layout: 'fit',
					id: 'tab_sprav',
					iconCls: 'info16',
					border:false,
					items: [
						new Ext.form.FormPanel({
							autoScroll: true,
							bodyBorder: false,
							bodyStyle: 'padding: 5px 5px 0',
							border: false,
							frame: false,
							id: 'Lpu_SupInfoPanel',
							labelAlign: 'right',
							labelWidth: 180,
							items: [
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									id: 'Lpu_SupInfoEditForm',
									layout: 'form',
									title: langs('1. Справочная информация'),
									items: [{
										xtype: 'panel',
										layout: 'form',
										border: false,
										bodyStyle:'background:#DFE8F6;padding:5px;',
										items: [{
											xtype: 'panel',
											layout: 'column',
											border: false,
											bodyStyle:'background:#DFE8F6;padding:5px;',
											items: [{// Левая часть
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:5px;',
												columnWidth: .50,
												labelWidth: 180,
												items:
												[{
													id: 'LPEW_LpuLevelType_id',
													name: 'LpuLevelType_id',
													xtype: 'hidden'
												}, {
													id: 'LPEW_LpuSubjectionLevel_id',
													moreFields: [
														{ name: 'LpuSubjectionLevel_pid', mapping: 'LpuSubjectionLevel_pid' }
													],
													fieldLabel: langs('Уровень подчиненности'),
													tabIndex: 1120,
													disabled: true,
													allowBlank: true, //!(getRegionNick().inlist([ 'astra' ])),
													comboSubject: 'LpuSubjectionLevel',
													xtype: 'swcommonsprcombo',
													anchor: '100%',
													lastQuery: '',
													typeCode: 'int',
													name: 'LpuSubjectionLevel_id'
												}, {
													id: 'LPEW_LpuLevel_id',
													fieldLabel: langs('Уровень МО'),
													tabIndex: 1120,
													disabled: !(getRegionNick().inlist([ 'krym', 'penza', 'pskov', 'ufa' ]) && (isSuperAdmin() || isLpuAdmin())),
													allowBlank: true, //!(getRegionNick().inlist([ 'astra' ])),
													comboSubject: 'LpuLevel',
													xtype: 'swcommonsprcombo',
													anchor: '100%',
													hiddenName: 'LpuLevel_id'
												}, {
													bodyStyle:'background:#DFE8F6;padding-right:0px;',
													border: false,
													hidden: (getRegionNick() != 'ufa'),
													layout: 'form',
													items: [{
														allowBlank: true,
														anchor: '100%',
														comboSubject: 'LpuLevel',
														disabled: true,
														fieldLabel: langs('Уровень МО (СМП)'),
														hiddenName: 'LpuLevel_cid',
														id: 'LPEW_LpuLevel_cid',
														tabIndex: 1120,
														xtype: 'swcommonsprcombo'
													}]
												}, {
													id: 'LPEW_LevelType_id',
													fieldLabel: langs('Уровень оказания МП'),
													tabIndex: 1120,
													disabled: true,
													//allowBlank: !getRegionNick().inlist([ 'astra' ]),
													comboSubject: 'LevelType',
													xtype: 'swcommonsprcombo',
													anchor: '100%',
													hiddenName: 'LevelType_id'
												}, {
													tabIndex: 1120,
													id: 'LPEW_FedLpuLevel_id',
													allowBlank: false,
													displayField: 'LpuLevel_Name',
													valueField: 'LpuLevel_id',
													moreFields: [
														{name: 'LpuLevel_id', mapping: 'LpuLevel_id'},
														{name: 'LpuLevel_Code', mapping: 'LpuLevel_Code'},
														{name: 'LpuLevel_Name', mapping: 'LpuLevel_Name'}
													],
													comboSubject: 'FedLpuLevel',
													fieldLabel: langs('ФРМО. Уровень МО'),
													hiddenName: 'FedLpuLevel_id',
													xtype: 'swcommonsprcombo',
													tpl: new Ext.XTemplate(
														'<tpl for="."><div class="x-combo-list-item">',
														'{LpuLevel_Name}',
														'</div></tpl>'
													)
												}, {
													fieldLabel: langs('Тестовая МО'),
													xtype: 'checkbox',
													disabled: true,
													anchor: '100%',
													name: 'test_MO',
													id: 'LPEW_Test_MO'
												}, {
													id: 'LPEW_Lpu_VizitFact',
													fieldLabel: langs('Посещений в смену'),
													tabIndex: 1120,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "4", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_VizitFact'
												}, {
													id: 'LPEW_Lpu_KoikiFact',
													fieldLabel: langs('Число коек'),
													tabIndex: 1120,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "4", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_KoikiFact'
												}, {
													id: 'LPEW_Lpu_AmbulanceCount',
													fieldLabel: langs('Число выездных бригад ВОВ'),
													tabIndex: 1120,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "2", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_AmbulanceCount'
												},{
                                                    comboSubject: 'InstitutionLevel',
                                                    width: 150,
                                                    hiddenName: 'InstitutionLevel_id',
                                                    id: 'LPEW_InstitutionLevel_id',
													fieldLabel: langs('Уровень учреждения в иерархии сети'),
													tabIndex: 1122,
                                                    xtype: 'swcommonsprcombo'
												},{
													fieldLabel: langs('Лаборатория'),
													xtype: 'checkbox',
													disabled: !isSuperAdmin(),
													anchor: '100%',
													name: 'Lpu_IsLab',
													id: 'LPEW_Lpu_IsLab',
													tabIndex: TABINDEX_LPEEW + 1,
													listeners: {
														check: function (checkbox, newValue) {

															win.refreshTabsVisibility();
															var FRMP = Ext.getCmp("Lpu_SupInfoEditForm").findById("LPEW_PasportMO_IsNoFRMP");
															if (newValue == true) {
																FRMP.setValue(true);
																FRMP.setDisabled(true);
															} else {
																FRMP.setDisabled(false);
															}
														}
													}

												},{
                                                    layout: 'form',
                                                    border: false,
                                                    hidden: !getRegionNick().inlist([ 'astra', 'kareliya', 'krym', 'penza', 'pskov', 'buryatiya', 'vologda', 'khak', 'yakutiya' ]),
                                                    bodyStyle:'background:#DFE8F6;padding-right:0px;',
                                                    //labelWidth: 180,
                                                    items: [{
                                                        id: 'LPEW_PasportMO_IsAssignNasel',
                                                        //allowBlank: (getRegionNick().inlist([ 'perm' ])),
                                                        tabIndex: 1125,
                                                        name: 'PasportMO_IsAssignNasel',
                                                        fieldLabel: langs('МО имеет приписное население'),
                                                        xtype: 'checkbox'
                                                    }]
                                                }]
											},{// Правая часть
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:5px;',
												columnWidth: .50,
												labelWidth: 240,
												items:
												[{
													id: 'LPEW_Lpu_FondOsn',
													fieldLabel: langs('Фондооснащенность на 1 кв.м (')+getCurrencyType()+langs(') (Отношение стоимости основных фондов к площади организации)'),
													tabIndex: 1125,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "6", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_FondOsn'
												}, {
													id: 'LPEW_Lpu_FondEquip',
													fieldLabel: langs('Фондовооруженность на 1 врача (')+getCurrencyType()+langs(') (Отношение стоимости основных фондов к численности врачей)'),
													tabIndex: 1125,
													xtype: 'textfield',
													autoCreate: {tag: "input",  maxLength: "8", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_FondEquip'
												},{
                                                    fieldLabel: langs('Не учитывать при выгрузке ФРМР'),
                                                    xtype: 'checkbox',
                                                    //anchor: '100%',
                                                    name: 'PasportMO_IsNoFRMP',
                                                    id: 'LPEW_PasportMO_IsNoFRMP',
                                                    tabIndex: TABINDEX_LPEEW + 1
                                                },{
                                                    fieldLabel: langs('Головное учреждение'),
                                                    hiddenName: 'Lpu_gid',
                                                    id: 'LPEW_Lpu_gid',
                                                    listWidth: 400,
													anchor: '100%',
                                                    xtype: 'swlpucombo',
                                                    listeners:{
														change: function(field,value){
															/*
															//var LpuOwnersCombo = Ext.getCmp('LPEW_LpuOwnership_id');
															var OrganizationalForm = Ext.getCmp('LPEW_OrganizationalForm_id');
															var DepartAffilTypeCombo = Ext.getCmp('LPEW_DepartAffilType_id');
															//для поля "ведомственная принадлежность"
															if( OrganizationalForm.getValue()==1 && !value ){
																DepartAffilTypeCombo.setAllowBlank(false);
															}else{
																DepartAffilTypeCombo.setAllowBlank(true);
															}
															*/
															var LpuOwnersCombo = Ext.getCmp('LPEW_LpuOwnership_id');
															var DepartAffilTypeCombo = Ext.getCmp('LPEW_DepartAffilType_id');
															//для поля "ведомственная принадлежность"
															if( LpuOwnersCombo.getValue()==1 && !value ){
																DepartAffilTypeCombo.setAllowBlank(false);
															}else{
																DepartAffilTypeCombo.setAllowBlank(true);
															}
														}
													}
                                                }]
											}]
										},{
											xtype: 'fieldset',
											layout: 'column',
											border: true,
											bodyStyle:'background:#DFE8F6;padding:5px;',
											title: langs('Опции автоматического занесения в регистр часто обращающихся'),
											height: 70,
											id: 'LPEW_Lpu_OftenCallers_Panel',
											items: [
												{
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												//labelWidth: 180,
												items: [{
													id: 'LPEW_Lpu_OftenCallers_CallTimes',
													fieldLabel: langs('Количество обращений'),
													tabIndex: 1130,
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_OftenCallers_CallTimes'
												}]
											},{
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												labelWidth: 15,
												items: [{
													id: 'LPEW_Lpu_OftenCallers_SearchDays',
													fieldLabel: langs(' за '),
													tabIndex: 1135,
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_OftenCallers_SearchDays'
												}]
											},{
												xtype: 'label',
												html:langs('дней для включения в регистр.'),
												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;'
											},{
												layout: 'form',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												labelWidth: 250,
												items: [{
													id: 'LPEW_Lpu_OftenCallers_FreeDays',
													fieldLabel: langs('Дней без обращений для снятия статуса: '),
													tabIndex: 1140,
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%',
													name: 'Lpu_OftenCallers_FreeDays'
												}]
											}/*,{
                                                fieldLabel: langs('МО имеет приписное население'),
                                                xtype: 'checkbox',
                                                //anchor: '100%',
                                                name: 'PasportMO_IsAssignNasel',
                                                id: 'LPEW_PasportMO_IsAssignNasel',
                                                tabIndex: 1142
                                            }*/]
										}
										/*{
											xtype: 'fieldset',
											layout: 'column',
											border: true,
											autoHeight : true,
											title: langs('Настройки локального PACS сервера'),
											id: 'LPEW_Lpu_LocalPacs_Panel',
											style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',

											items: [{
												layout: 'form',
												border: false,
												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
												bodyStyle: ' background:#DFE8F6;padding-right:0px;',
												items:[{
													id: 'LPEW_Lpu_HasLocalPacsServer',
													fieldLabel: langs('Установлен локальный PACS'),
													tabIndex: 1120,
													xtype: 'checkbox',
													style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
													name: 'Lpu_HasLocalPacsServer',
													listeners: {
														check: function(checkbox,checked){
															//log(checkbox.getValue());
															this.findById('LPEW_Lpu_LocalPacsServerIP').setDisabled(!checked);
															this.findById('LPEW_Lpu_LocalPacsServerAetitle').setDisabled(!checked);
															this.findById('LPEW_Lpu_LocalPacsServerPort').setDisabled(!checked);
															this.findById('LPEW_Lpu_LocalPacsServerWadoPort').setDisabled(!checked);
														}.createDelegate(this)
													}
												}]
											},{
												layout: 'form',
												border: false,
												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
												bodyStyle: ' background:#DFE8F6;padding-right:0px;',
												items:[{
													id: 'LPEW_Lpu_LocalPacsServerIP',
													fieldLabel: langs('IP-адрес локального PACS-а:'),
													tabIndex: 1120,
													style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
													name: 'Lpu_LocalPacsServerIP',
													xtype: 'textfield',
													labelSeparator: '',
													regex: new RegExp(/^(1\d{2}\.|[1-9]\d?\.|2[0-4]\d\.|25[0-5]\.){3}(\d{1,2}|1\d{2}|2[0-4]\d|25[0-5])$/),
													msgTarget :'under',
													//plugins: [ new Ext.ux.InputTextMask('999.999.999.999', false) ],
													anchor: '100%'
												}]
											},{
												layout: 'form',
												border: false,

												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
												bodyStyle: ' background:#DFE8F6;padding-right:0px;',
												items:[{
													id: 'LPEW_Lpu_LocalPacsServerAetitle',
													fieldLabel: langs('AETITLE локального PACS-а:'),
													tabIndex: 1120,
													style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
													name: 'Lpu_LocalPacsServerAetitle',
													xtype: 'textfield',
													labelSeparator: '',
													anchor: '100%'
												}]
											},{
												layout: 'form',
												border: false,
//												labelWidth: 200,
												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
												bodyStyle: ' background:#DFE8F6;padding-right:0px;',
												items:[{
													id: 'LPEW_Lpu_LocalPacsServerPort',
													fieldLabel: langs('Порт локального PACS-а:'),
													tabIndex: 1120,
													style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
													name: 'Lpu_LocalPacsServerPort',
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%'
												}]
											},{
												layout: 'form',
												border: false,
//												labelWidth: 200,
												style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
												bodyStyle: ' background:#DFE8F6;padding-right:0px;',
												items:[{
													id: 'LPEW_Lpu_LocalPacsServerWadoPort',
													fieldLabel: langs('Wado-порт локального PACS-а:'),
													tabIndex: 1120,
													style: 'font-size: 12px;padding-top: 4px;padding-left: 3px;',
													name: 'Lpu_LocalPacsServerWadoPort',
													xtype: 'textfield',
													labelSeparator: '',
													autoCreate: {tag: "input",  maxLength: "5", autocomplete: "off"},
													maskRe: /[0-9]/,
													anchor: '100%'
												}]
											}]
										}*/
									]
									}]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_Licence',
									layout: 'form',
									title: langs('2. Лицензии МО'),
									listeners: {
										expand: function () {
											this.findById('LPEW_LpuLicenceGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_edit'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'LpuLicence',
											editformclassname: 'swLpuLicenceEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=LpuPassport&m=loadLpuLicenceGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_LpuLicenceGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'LpuLicence_id', type: 'int', header: 'ID', key: true},
												{name: 'LpuLicence_Num', type: 'string', header: langs('Номер лицензии'), width: 120},
												{name: 'LpuLicence_setDate', type: 'date', header: langs('Дата выдачи'), width: 120},
												{name: 'LpuLicence_RegNum', type: 'string', header: langs('Регистрационный номер'), width: 180},
												{name: 'LpuLicence_begDate', type: 'date', header: langs('Начало действия'), width: 120},
												{name: 'LpuLicence_endDate', type: 'date', header: langs('Окончание действия'), width: 120}
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
									id: 'Lpu_RSchet',
									layout: 'form',
									title: langs('3. Расчетный счет'),
									listeners: {
										expand: function () {
											this.findById('LPEW_OrgRSchetGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add', handler: function() {this.openOrgRSchetEditWindow('add', this.Lpu_id);}.createDelegate(this)},
												{name: 'action_edit', handler: function() {this.openOrgRSchetEditWindow('edit', this.Lpu_id);}.createDelegate(this)},
												{name: 'action_view', handler: function() {this.openOrgRSchetEditWindow('view', this.Lpu_id);}.createDelegate(this)},
												{name: 'action_delete', handler: function() {this.deleteOrgRSchet();}.createDelegate(this)},
												{name: 'action_refresh', handler: function() {Ext.getCmp('LPEW_OrgRSchetGrid').getGrid().getStore().removeAll();Ext.getCmp('LPEW_OrgRSchetGrid').getGrid().getStore().load();}.createDelegate(this)},
												{name: 'action_print'}
											],
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=Org&m=loadOrgRSchetGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_OrgRSchetGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'OrgRSchet_id', type: 'int', header: 'ID', key: true},
												{name: 'OrgRSchet_Name', type: 'string', header: langs('Наименование'), width: 270},
												{name: 'OrgBank_Name', type: 'string', header: langs('Банк'), width: 270},
												{name: 'OrgRSchet_RSchet', type: 'string', header: langs('Номер счета'), width: 270}
											],
											totalProperty: 'totalCount'
										})
									]
								}),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_MOInfoSys',
                                    layout: 'form',
                                    title: langs('4. Информационная система'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_MOInfoSysGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id},  params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'MOInfoSys',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
                                            dataUrl: '/?c=LpuPassport&m=loadMOInfoSys',
                                            editformclassname: 'swMOInfoSysEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_MOInfoSysGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'MOInfoSys_id', type: 'int', header: 'ID', key: true},
                                                {name: 'MOInfoSys_Name', type: 'string', header: langs('Наименование ИС'), width: 270},
                                                {name: 'DInfSys_Name', type: 'string', header: langs('Тип ИС'), width: 270, id: 'autoexpand'},
                                                {name: 'MOInfoSys_Cost', type: 'float', header: langs('Стоимость ИС, ')+getCurrencyType()+'', width: 270},
                                                {name: 'MOInfoSys_CostYear', type: 'float', header: langs('Стоимость сопровождения ИС в год, ')+getCurrencyType()+'', width: 270},
                                                {name: 'MOInfoSys_IntroDT', type: 'date', header: langs('Дата внедрения'), width: 270},
                                                {name: 'MOInfoSys_IsMainten', type: 'checkcolumn', header: langs('Признак сопровождения'), width: 180},
                                                {name: 'MOInfoSys_NameDeveloper', type: 'string', header: langs('Наименование разработчика'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_SpecializationMO',
                                    layout: 'form',
                                    title: langs('5. Специализация организации'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_SpecializationMOGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'SpecializationMO',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
                                            dataUrl: '/?c=LpuPassport&m=loadSpecializationMO',
                                            editformclassname: 'swSpecializationMOEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_SpecializationMOGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'SpecializationMO_id', type: 'int', header: 'ID', key: true},
                                                {name: 'Mkb10Code_id', type: 'int', hidden: true},
                                                {name: 'Mkb10CodeClass_Name', type: 'string', header: langs('Класс МКБ-10'), width: 270},
                                                {name: 'SpecializationMO_MedProfile', type: 'string', header: langs('Медицинский профиль'), width: 270},
                                                {name: 'LpuLicence_Num', type: 'string', header: langs('Номер лицензии'), width: 270},
                                                {name: 'SpecializationMO_IsDepAftercare', type: 'checkcolumn', header: langs('Наличие отделения долечивания'), width: 190}
                                             ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_MedUsluga',
                                    layout: 'form',
                                    title: langs('6. Медицинские услуги'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_MedUslugaGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
											auditOptions: {
												field: 'MedUsluga_id',
												key: 'MedUsluga_id',
												schema: 'fed'
											},
                                            object: 'MedUsluga',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
											transferLine: true,
                                            dataUrl: '/?c=LpuPassport&m=loadMedUsluga',
                                            editformclassname: 'swMedUslugaEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_MedUslugaGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'MedUsluga_id', type: 'int', header: 'ID', hidden: true, key: true},
                                                {name: 'DUslugi_id', type: 'int', header: 'ID', hidden: true},
                                                {name: 'DUslugi_Name', type: 'string', id: 'autoexpand', header: langs('Наименование услуги'), width: 270},
                                                {name: 'MedUsluga_LicenseNum', type: 'string', header: langs('Номер лицензии'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_MedTechnology',
                                    layout: 'form',
                                    title: langs('7. Медицинские технологии'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_MedTechnologyGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'MedTechnology',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
                                            dataUrl: '/?c=LpuPassport&m=loadMedTechnology',
                                            editformclassname: 'swMedTechnologyEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_MedTechnologyGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'MedTechnology_id', type: 'int', header: 'ID', key: true},
                                                {name: 'MedTechnology_Name', type: 'string', header: langs('Наименование медицинской технологии'), width: 270},
                                                {name: 'TechnologyClass_Name', type: 'string', header: langs('Класс технологии'), width: 270},
                                                {name: 'LpuBuildingPass_Name', type: 'string', header: langs('Идентификатор здания'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_UslugaComplexLpu',
                                    layout: 'form',
                                    title: langs('8. Направления оказания медицинской помощи'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_UslugaComplexLpuGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'UslugaComplexLpu',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'passport',
                                            dataUrl: '/?c=LpuPassport&m=loadUslugaComplexLpu',
                                            editformclassname: 'swUslugaComplexLpuEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_UslugaComplexLpuGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'UslugaComplexLpu_id', type: 'int', header: 'ID', key: true},
                                                {name: 'UslugaComplex_id', header: langs('Id услуги'), hidden: true},
                                                {name: 'UslugaComplex_Code', type: 'string', header: langs('Код услуги'), width: 270},
                                                {name: 'UslugaComplex_Name', type: 'string', header: langs('Наименование услуги'), width: 270},
                                                {name: 'UslugaComplexLpu_begDate', type: 'date', header: langs('Дата начала оказания услуги'), width: 270},
                                                {name: 'UslugaComplexLpu_endDate', type: 'date', header: langs('Дата окончания оказания услуги'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
								new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_LpuPeriodStom',
                                    layout: 'form',
                                    title: langs('9. Периоды обслуживания стомат. вызовов на дому'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_LpuPeriodStomGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'LpuPeriodStom',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'dbo',
                                            dataUrl: '/?c=LpuPassport&m=loadLpuPeriodStom',
                                            editformclassname: 'swLpuPeriodStomEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_LpuPeriodStomGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'LpuPeriodStom_id', type: 'int', header: 'ID', key: true},
                                                {name: 'LpuPeriodStom_begDate', type: 'date', header: langs('Дата начала периода'), width: 270},
                                                {name: 'LpuPeriodStom_endDate', type: 'date', header: langs('Дата окончания периода'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_PitanFormTypeLink',
                                    layout: 'form',
                                    title: langs('10. Питание'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_PitanFormTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'PitanFormTypeLink',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
                                            dataUrl: '/?c=LpuPassport&m=loadPitanFormTypeLink',
                                            editformclassname: 'swPitanFormTypeLinkEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_PitanFormTypeLinkGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'PitanFormTypeLink_id', type: 'int', header: 'ID', key: true},
                                                {name: 'VidPitan_Name', type: 'string', header: langs('Вид питания'), width: 270},
                                                {name: 'PitanCnt_Name', type: 'string', header: langs('Кратность питания'), width: 270},
                                                {name: 'PitanForm_Name', type: 'string', header: langs('Форма питания'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_PlfDocTypeLink',
                                    layout: 'form',
                                    title: langs('11. Природные лечебные факторы'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_PlfDocTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'PlfDocTypeLink',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
                                            dataUrl: '/?c=LpuPassport&m=loadPlfDocTypeLink',
                                            editformclassname: 'swPlfDocTypeLinkEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_PlfDocTypeLinkGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'PlfDocTypeLink_id', type: 'int', header: 'ID', key: true},
                                                {name: 'Plf_Name', type: 'string', header: langs('Наименование фактора'), width: 270},
                                                {name: 'PlfType_Name', type: 'string', header: langs('Тип фактора'), width: 270},
                                                {name: 'DocTypeUsePlf_Name', type: 'string', header: langs('Документ'), width: 270},
                                                {name: 'PlfDocTypeLink_Num', type: 'string', header: langs('Номер документа'), width: 270},
                                                {name: 'PlfDocTypeLink_GetDT', type: 'date', header: langs('Дата выдачи документа'), width: 270},
                                                {name: 'PlfDocTypeLink_BegDT', type: 'date', header: langs('Дата начала действия фактора'), width: 270},
                                                {name: 'PlfDocTypeLink_EndDT', type: 'date', header: langs('Дата окончания действия фактора'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
                                new sw.Promed.Panel({
                                    autoHeight: true,
                                    style:'margin-bottom: 0.5em;',
                                    border: true,
                                    collapsible: true,
                                    collapsed: true,
                                    id: 'Lpu_PlfObjectCount',
                                    layout: 'form',
                                    title: langs('12. Объекты/места использования природных лечебных факторов'),
                                    listeners: {
                                        expand: function () {
                                            this.findById('LPEW_PlfObjectCountGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                        }.createDelegate(this)
                                    },
                                    items: [
                                        new sw.Promed.ViewFrame({
                                            actions: [
                                                {name: 'action_add'},
                                                {name: 'action_view'},
                                                {name: 'action_delete'},
                                                {name: 'action_refresh'},
                                                {name: 'action_print'}
                                            ],
                                            object: 'PlfObjectCount',
                                            autoExpandColumn: 'autoexpand',
                                            autoExpandMin: 150,
                                            autoLoadData: false,
                                            border: false,
                                            scheme: 'fed',
                                            dataUrl: '/?c=LpuPassport&m=loadPlfObjectCount',
                                            editformclassname: 'swPlfObjectCountEditWindow',
                                            focusOn: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            focusPrev: {
                                                name: 'LPEW_CancelButton',
                                                type: 'button'
                                            },
                                            id: 'LPEW_PlfObjectCountGrid',
                                            //pageSize: 100,
                                            paging: false,
                                            region: 'center',
                                            //root: 'data',
                                            stringfields: [
                                                {name: 'PlfObjectCount_id', type: 'int', header: 'ID', key: true},
                                                {name: 'PlfObjects_Name', type: 'string', header: langs('Наименование объекта'), width: 270},
                                                {name: 'PlfObjectCount_Count', type: 'int', header: langs('Количество объектов по использованию'), width: 270}
                                            ],
                                            totalProperty: 'totalCount'
                                        })
                                    ]
                                }),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_LpuMobileTeam',
									layout: 'form',
									title: langs('13. Мобильные бригады'),
									listeners: {
										expand: function () {
											this.findById('LPEW_LpuMobileTeamGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_edit', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_view'},
												{name: 'action_delete', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'LpuMobileTeam',
											editformclassname: 'swLpuMobileTeamEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=LpuPassport&m=loadLpuMobileTeamGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_LpuMobileTeamGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'LpuMobileTeam_id', type: 'int', header: 'ID', key: true},
												{name: 'LpuMobileTeam_begDate', type: 'date', header: langs('Дата начала'), width: 100},
												{name: 'LpuMobileTeam_endDate', type: 'date', header: langs('Дата окончания'), width: 100},
												{name: 'LpuMobileTeam_Count', type: 'int', header: langs('Количество бригад'), width: 100},
												{name: 'DispClass_Name', type: 'string', header: langs('Тип бригады'), width: 200, id: 'autoexpand'}
											],
											totalProperty: 'totalCount',
											toolbar: true
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_FunctionTime',
									layout: 'form',
									title: langs('14. Периоды функционирования'),
									listeners: {
										expand: function () {
											this.findById('LPEW_FunctionTimeGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_edit', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_view'},
												{name: 'action_delete', disabled: !isLpuAdmin() && !isSuperAdmin},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'FunctionTime',
											editformclassname: 'swFunctionTimeEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
                                            scheme: 'passport',
											dataUrl: '/?c=LpuPassport&m=loadFunctionTime',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_FunctionTimeGrid',
											//pageSize: 100,
											paging: false,
											region: 'center',
											//root: 'data',
											stringfields: [
												{name: 'FunctionTime_id', type: 'int', header: 'ID', key: true},
												{name: 'InstitutionFunction_id', type: 'int', hidden: true},
												{name: 'InstitutionFunction_Name', type: 'string', id: 'autoexpand', header: langs('Период функционирования учреждения')},
												{name: 'FunctionTime_begDate', type: 'date', header: langs('Дата начала периода'), width: 150},
												{name: 'FunctionTime_endDate', type: 'date', header: langs('Дата окончания периода'), width: 150}
											],
											totalProperty: 'totalCount',
											toolbar: true
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_CmpStation',
									layout: 'form',
									labelWidth: 250,
									title: langs('15. СМП'),
									listeners: {
										expand: function () {
											this.findById('LPEW_CmpSubstationGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
										}.createDelegate(this)
									},
									items: [
										{
											xtype: 'panel',
											layout: 'form',
											border: false,
											bodyStyle:'background:#DFE8F6;padding:5px;',
											labelWidth: 250,
											items: [
												{
													autoHeight: true,
													title: langs('Численность обслуживаемого населения'),
													//bodyStyle:'padding: 10px;',
													xtype: 'fieldset',
													columnWidth: 0.3,
													layout: 'column',
													defaults: {
														xtype: 'container',
														autoEl: {},
														layout: 'form',
														columnWidth: 0.3
													},
													items: [
														{
															items: {
																fieldLabel: langs('Всего'),
																xtype: 'numberfield',
																width: 100,
																name: 'PasportMO_KolServ',
																id: 'LPEW_PasportMO_KolServ',
																maxLength: 8,
																minValue: 0,
																allowDecimals: false,
																autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '8'}
															}
														}, {
															items: {
																fieldLabel: langs('из них: сельского'),
																xtype: 'numberfield',
																width: 100,
																name: 'PasportMO_KolServSel',
																id: 'LPEW_PasportMO_KolServSel',
																maxLength: 8,
																minValue: 0,
																allowDecimals: false,
																autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '8'}
															}
														},
														{
															items: {
																fieldLabel: langs('детского (0-17)'),
																xtype: 'numberfield',
																width: 100,
																name: 'PasportMO_KolServDet',
																id: 'LPEW_PasportMO_KolServDet',
																maxLength: 8,
																minValue: 0,
																allowDecimals: false,
																autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '8'}
															}
														}
													]
												},
												{
													autoHeight: true,
													title: langs('Число самостоятельных станций СМП'),
													//bodyStyle:'padding: 10px;',
													xtype: 'fieldset',
													columnWidth: 0.3,
													layout: 'column',
													defaults: {
														xtype: 'container',
														autoEl: {},
														layout: 'form',
														columnWidth: 0.3
													},
													items: [
														{
															items: {
																fieldLabel: langs('применяющих МЭС'),
																xtype: 'numberfield',
																width: 100,
																name: 'PasportMO_KolCmpMes',
																id: 'LPEW_PasportMO_KolCmpMes',
																maxLength: 8,
																minValue: 0,
																allowDecimals: false,
																autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '8'}
															}
														}, {
															items: {
																fieldLabel: langs('переведенных на оплату МП по результату деятельности'),
																xtype: 'numberfield',
																width: 100,
																name: 'PasportMO_KolCmpPay',
																id: 'LPEW_PasportMO_KolCmpPay',
																maxLength: 8,
																minValue: 0,
																allowDecimals: false,
																autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '8'}
															}
														},
														{
															items: {
																fieldLabel: langs('переведенных на отраслевую систему оплаты труда'),
																xtype: 'numberfield',
																width: 100,
																name: 'PasportMO_KolCmpWage',
																id: 'LPEW_PasportMO_KolCmpWage',
																maxLength: 8,
																minValue: 0,
																allowDecimals: false,
																autoCreate: {tag: 'input', type: 'text', size: '20', autocomplete: 'off', maxlength: '8'}
															}
														}
													]
												}
											]
										},
										new sw.Promed.ViewFrame({
											actions: [
												{name: 'action_add'},
												{name: 'action_edit'},
												{name: 'action_view'},
												{name: 'action_delete'},
												{name: 'action_refresh'},
												{name: 'action_print'}
											],
											object: 'CmpSubstation',
											editformclassname: 'swCmpSubstationEditWindow',
											autoExpandColumn: 'autoexpand',
											autoExpandMin: 150,
											autoLoadData: false,
											border: false,
											dataUrl: '/?c=LpuPassport&m=loadCmpSubstationGrid',
											focusOn: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											focusPrev: {
												name: 'LPEW_CancelButton',
												type: 'button'
											},
											id: 'LPEW_CmpSubstationGrid',
											paging: false,
											region: 'center',
											root: 'data',
											stringfields: [
												{name: 'CmpSubstation_id', type: 'int', header: 'ID', key: true},
												{name: 'CmpSubstation_Code', type: 'string', header: langs('Код'), width: 150},
												{name: 'CmpSubstation_Name', type: 'string', header: langs('Нименование'), id: 'autoexpand'},
												{name: 'LpuStructure_Name', type: 'string', header: langs('Уровень структуры'), width: 320}
											],
											toolbar: true
										})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									bodyStyle: 'background:#DFE8F6',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_Filial',
									layout: 'form',
									title: langs('16. Филиалы'),
									listeners: {
										expand: function () {

											Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FilialGrid').removeAll({clearAll: true});
											this.findById('LPEW_FilialGrid').loadData(
												{
													globalFilters: {Lpu_id: this.Lpu_id},
													params: {
																Lpu_id: this.Lpu_id,
																Oktmo_id: this.findById('LPEW_Oktmo_id').getValue(),
																Oktmo_Name: this.findById('LPEW_Oktmo_Name').getValue()
															}
												});
										}.createDelegate(this)
									},
									items: [
										{
											id: 'LpuFilial_Filter',
											autoHeight: true,
											style: 'margin: 5px 0px 10px 10px; display:inline-block;',
											bodyStyle: 'background:#DFE8F6',
											title: langs('Фильтр'),
											layout: 'form',
											xtype: 'fieldset',
											collapsible: true,
											collapsed: true,
											labelAlign: 'right',
											items: [
												{
													layout: 'column',
													border: false,
													bodyStyle: 'background:#DFE8F6;padding-right:0px',
													items: [
														{
															layout: 'form',
															border: false,
															bodyStyle: 'background:#DFE8F6;margin-right:0px;',
															items: [
																{
																	id: 'LpuFilial_begDate_Filter',
																	allowBlank: true,
																	xtype: 'swdatefield',
																	fieldLabel: langs('Дата начала'),
																	format: 'd.m.Y',
																	name: 'LpuFilial_begDate',
																	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
																	// обработчики событий для живого поиска
																	/*listeners: {
																		select: function () {
																			return this.filter();
																		},

																		change: function ()	{
																			return this.filter();
																		}
																	},

																	filter: function () {
																		this.findById('LpuFilial_Filter').getGridAfterFilter(true);
																	}.createDelegate(this)
																	*/
																}
															]
														},
														{
															layout: 'form',
															bodyStyle: 'background:#DFE8F6;margin-left:-60px;',
															border: false,
															items: [
																{
																	id: 'LpuFilial_endDate_Filter',
																	allowBlank: true,
																	xtype: 'swdatefield',
																	fieldLabel: langs('Дата окончания'),
																	format: 'd.m.Y',
																	name: 'LpuFilial_endDate',
																	plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
																	// обработчики событий для живого поиска
																	/*listeners: {
																		select: function () {
																			return this.filter();
																		},

																		change: function ()	{
																			return this.filter();
																		}
																	},

																	filter: function () {
																		this.findById('LpuFilial_Filter').getGridAfterFilter(true);
																	}.createDelegate(this)
																	*/
																}
															]
														}
													]
												},
												{
													layout: 'form',
													bodyStyle: 'background:#DFE8F6;padding-right:0px;',
													border: false,
													items: [
														{
															id: 'LpuFilial_Name_Filter',
															fieldLabel: langs('Наименование'),
															allowBlank: true,
															name: 'LpuFilial_Name',
															xtype: 'textfield',
															autoCreate: {tag: 'input', maxLength: '300', autocomplete: 'off'},
															width: 335,
															//enableKeyEvents: true,
															// обработчики событий для живого поиска
															/*listeners: {
																keyup: function (eventObject) {

																	this.findById('LpuFilial_Filter').getGridAfterFilter(true);

																}.createDelegate(this)

															}*/
														},
														{
															id: 'LpuFilialFilterButtons',
															layout: 'column',
															border: false,
															//style: 'margin-left: 30em',
															bodyStyle: 'background:#DFE8F6;padding-right:0px;',
															items: [
																{
																	layout: 'form',
																	border: false,
																	bodyStyle: 'background:#DFE8F6;padding-right:0px;',
																	items: [
																		{
																			id: 'LpuFilial_Nick_Filter',
																			allowBlank: true,
																			fieldLabel: langs('Краткое наименование'),
																			name: 'LpuFilial_Nick',
																			xtype: 'textfield',
																			width: 100,
																			autoCreate: {
																				tag: 'input',
																				maxLength: 300,
																				autocomplete: 'off'
																			},
																			//enableKeyEvents: true,
																			// обработчики событий для живого поиска
																			/*
																			listeners: {
																				keyup: function (eventObject) {

																					this.findById('LpuFilial_Filter').getGridAfterFilter(true);

																				}.createDelegate(this)

																			}*/
																		}
																	]
																},

																{
																	layout: 'form',
																	border: false,
																	bodyStyle: 'background:#DFE8F6;',
																	items: [{
																		id: 'LpuFilial_Filter_SearchButton',
																		xtype: 'button',
																		text: BTN_FRMSEARCH,
																		iconCls: 'search16',
																		style: 'margin-left: 20px',

																		handler: function () {

																			this.findById('LpuFilial_Filter').getGridAfterFilter(true);

																		}.createDelegate(this)

																	}]
																},
																{
																	layout: 'form',
																	border: false,
																	bodyStyle: 'background:#DFE8F6;padding-right:0px;',
																	items: [
																		{
																			id: 'LpuFilial_Filter_ResetButton',
																			text: BTN_FRMRESET,
																			xtype: 'button',
																			iconCls: 'resetsearch16',
																			style: 'margin-left: 20px;',

																			handler: function () {

																				this.findById('LpuFilial_Filter').getGridAfterFilter();

																			}.createDelegate(this)
																		}
																	]
																}
															]
														}
													]

												}
											],

											getGridAfterFilter: function (filter) {

												if (filter === undefined)
												{
													this.resetFilterFields();
												}

												var wnd = Ext.getCmp('LpuPassportEditWindow'),
													Lpu_id = wnd.findById('LPEW_Lpu_id').getValue(),
													Oktmo_id = wnd.findById('LPEW_Oktmo_id').getValue(),
													Oktmo_Name = wnd.findById('LPEW_Oktmo_Name').getValue();

												var paramsToQuery = this.getFilters(),
													paramsToWindow = {

														Lpu_id: Lpu_id,
														Oktmo_id: Oktmo_id,
														Oktmo_Name: Oktmo_Name
													};

												paramsToQuery.Lpu_id = Lpu_id;


												Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FilialGrid').removeAll({clearAll: true});
												Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FilialGrid').loadData({

													globalFilters: paramsToQuery,
													params: paramsToWindow,
													noFocusOnLoad: true
												});

											},

											getFilters: function() {

												var params = this.getFilterFields();

												for (key in params)
												{
													if (params[key].id.indexOf("Date") === -1 )
													{
														params[key] = params[key].getValue();
													} else
													{
														params[key] = Ext.util.Format.date(params[key].getValue(), 'd.m.Y')
													}
												}

												return params;
											},

											getFilterFields: function() {

												var fields = {
													LpuFilial_begDate: this.findById('LpuFilial_begDate_Filter'),
													LpuFilial_endDate: this.findById('LpuFilial_endDate_Filter'),
													LpuFilial_Name: this.findById('LpuFilial_Name_Filter'),
													LpuFilial_Nick: this.findById('LpuFilial_Nick_Filter')
												};

												return fields;

											},

											resetFilterFields: function () {

												var fields = this.getFilterFields();

												for (k in fields)
												{
													fields[k].setValue('');
												}
											}
										},

										new sw.Promed.ViewFrame({

												id: 'LPEW_FilialGrid',
												object: 'LpuFilial',
												obj_isEvn: false,
												actions: [
													{name: 'action_add'},
													{name: 'action_edit'},
													{name: 'action_view'},
													{name: 'action_delete', url: '/?c=LpuPassport&m=deleteLpuFilialRecord'},
													{name: 'action_refresh'},
													{name: 'action_print', hidden: true}
												],
												getMoreParamsForEdit: function() {
													return {
														PassportToken_tid: Ext.getCmp('LPEW_PassportToken_tid').getValue()
													};
												},
												editformclassname: 'swLpuFilialEditWindow',
												autoExpandColumn: 'autoexpand',
												autoExpandMin: 150,
												autoLoadData: false,
												border: false,
												scheme: 'dbo',
												linkedTables: 'LpuBuilding',
												dataUrl: '/?c=LpuPassport&m=getLpuFilialGrid',
												focusOn: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												focusPrev: {
													name: 'LPEW_CancelButton',
													type: 'button'
												},
												paging: false,
												region: 'center',
												stringfields: [
													{
														name: 'LpuFilial_id',
														type: 'int',
														header: 'ID',
														hidden: true

													},
													{
														name: 'LpuFilial_Name',
														type: 'string',
														header: langs('Наименование'),
														width: 270
													},
													{
														name: 'LpuFilial_Nick',
														type: 'string',
														header: langs('Краткое наименование'),
														width: 270
													},
													{
														name: 'Oktmo_Name',
														type: 'string',
														header: langs('Код ОКТМО'),
														width: 270
													},
													{
														name: 'LpuFilial_begDate',
														type: 'date',
														header: langs('Дата начала'),
														width: 270
													},
													{
														name: 'LpuFilial_endDate',
														type: 'date',
														header: langs('Дата окончания'),
														width: 270
													}

												]
											})
									]
								}),
								new sw.Promed.Panel({
									autoHeight: true,
									style:'margin-bottom: 0.5em;',
									bodyStyle: 'background:#DFE8F6',
									border: true,
									collapsible: true,
									collapsed: true,
									id: 'Lpu_FSSContract',
									layout: 'form',
									hidden: getRegionNick() == 'kz',
									title: langs('17. Договоры с ФСС'),
									listeners: {
										expand: function () {
											Ext.getCmp('LpuPassportEditWindow').findById('LPEW_FSSContractGrid').removeAll({clearAll: true});
											this.findById('LPEW_FSSContractGrid').loadData({
												globalFilters: {Lpu_id: this.Lpu_id},
												params: {Lpu_id: this.Lpu_id}
											});
										}.createDelegate(this)
									},
									items: [{
										id: 'LpuFSSContract_Filter',
										autoHeight: true,
										style: 'margin: 5px 0px 10px 10px; display:inline-block;',
										bodyStyle: 'background:#DFE8F6',
										title: langs('Фильтр'),
										layout: 'form',
										xtype: 'fieldset',
										collapsible: true,
										collapsed: true,
										labelAlign: 'right',
										items: [{
											layout: 'column',
											border: false,
											bodyStyle: 'background:#DFE8F6;padding-right:0px',
											items: [{
												layout: 'form',
												border: false,
												bodyStyle: 'background:#DFE8F6;margin-right:0px;',
												items: [{
													id: 'LpuFSSContractType_id_Filter',
													xtype: 'swcommonsprcombo',
													width: 350,
													listWidth: 550,
													comboSubject: 'LpuFSSContractType',
													fieldLabel: 'Вид услуг по договору'
												}]
											}, {
												layout: 'form',
												bodyStyle: 'background:#DFE8F6;margin-left:-60px;',
												border: false,
												items: [{
													id: 'LpuFSSContract_Num_Filter',
													xtype: 'textfield',
													fieldLabel: 'Номер договора',
													name: 'LpuFSSContract_Num',
												}]
											}, {
												layout: 'form',
												border: false,
												bodyStyle: 'background:#DFE8F6;padding-right:0px;',
												items: [{
													style: 'margin-left: 20px',
													xtype: 'button',
													iconCls: 'search16',
													id: 'LpuFSSContract_SearchButton',
													text: BTN_FRMSEARCH,
													handler: function () {
														this.doSearchLpuFSSContract();
													}.createDelegate(this),
												}],
											}, {
												layout: 'form',
												border: false,
												bodyStyle: 'background:#DFE8F6;padding-right:0px;',
												items: [{
													style: 'margin-left: 20px',
													handler: function () {
														this.doSearchLpuFSSContract(true);
													}.createDelegate(this),
													xtype: 'button',
													iconCls: 'resetsearch16',
													id: 'LpuFSSContract_ResetButton',
													text: BTN_FRMRESET
												}]
											}]
										}]
									},

									this.FSSContractGrid = new sw.Promed.ViewFrame({
										id: 'LPEW_FSSContractGrid',
										object: 'LpuFSSContract',
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete', url: '/?c=LpuFSSContract&m=delete', msg: 'Удалить договор?'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										editformclassname: 'swLpuFSSContractEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										dataUrl: '/?c=LpuFSSContract&m=loadList',
										paging: false,
										region: 'center',
										stringfields: [
											{name: 'LpuFSSContract_id', type: 'int', header: 'ID', key: true},
											{name: 'LpuFSSContractType_id', type: 'int', hidden: true},
											{name: 'LpuFSSContractType_Name', type: 'string', header: 'Вид услуг по договору', width: 270, id: 'autoexpand'},
											{name: 'LpuFSSContract_Num', type: 'string', header: 'Номер договора', width: 270},
											{name: 'LpuFSSContract_begDate', type: 'string', header: 'Дата начала действия договора', width: 270},
											{name: 'LpuFSSContract_endDate', type: 'string', header: 'Дата окончания действия договора', width: 270}
										]
									})]
								})
							]
						})
					],
					listeners: {
						activate: function(){
							if (!Ext.isEmpty(this.Lpu_id)) {
								if (!Ext.getCmp('Lpu_Licence').collapsed)
									Ext.getCmp('LPEW_LpuLicenceGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_RSchet').collapsed)
									Ext.getCmp('LPEW_OrgRSchetGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_MOInfoSys').collapsed)
									Ext.getCmp('LPEW_MOInfoSysGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_SpecializationMO').collapsed)
									Ext.getCmp('LPEW_SpecializationMOGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_MedUsluga').collapsed)
									Ext.getCmp('LPEW_MedUslugaGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_MedTechnology').collapsed)
									Ext.getCmp('LPEW_MedTechnologyGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_UslugaComplexLpu').collapsed)
									Ext.getCmp('LPEW_UslugaComplexLpuGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_LpuPeriodStom').collapsed)
									Ext.getCmp('LPEW_LpuPeriodStomGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_PitanFormTypeLink').collapsed)
									Ext.getCmp('LPEW_PitanFormTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_PlfDocTypeLink').collapsed)
									Ext.getCmp('LPEW_PlfDocTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_PlfObjectCount').collapsed)
									Ext.getCmp('LPEW_PlfObjectCountGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_LpuMobileTeam').collapsed)
									Ext.getCmp('LPEW_LpuMobileTeamGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_FunctionTime').collapsed)
									Ext.getCmp('LPEW_FunctionTimeGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: langs('3. Руководство'),
					layout: 'fit',
					id: 'tab_ruk',
					iconCls: 'info16',
					border:false,
					items: [
						new sw.Promed.ViewFrame({
							actions: [
								{name: 'action_add', handler: function() {this.openOrgHeadEditWindow('add');}.createDelegate(this)},
								{name: 'action_edit', handler: function() {this.openOrgHeadEditWindow('edit');}.createDelegate(this)},
								{name: 'action_view', handler: function() {this.openOrgHeadEditWindow('view');}.createDelegate(this)},
								{name: 'action_delete', handler: function() {this.deleteOrgHead();}.createDelegate(this)},
								{name: 'action_refresh', handler: function() {Ext.getCmp('LPEW_OrgHeadGrid').getGrid().getStore().removeAll();Ext.getCmp('LPEW_OrgHeadGrid').getGrid().getStore().load();}.createDelegate(this)},
								{name: 'action_print'/*, handler: function() { this.printEvnRP(); }.createDelegate(this)*/}
							],
							autoExpandColumn: 'autoexpand',
							autoExpandMin: 150,
							autoLoadData: false,
							dataUrl: '/?c=Org&m=loadOrgHeadGrid',
							focusOn: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							focusPrev: {
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							id: 'LPEW_OrgHeadGrid',
							//pageSize: 100,
							paging: false,
							region: 'center',
							//root: 'data',
							stringfields: [
								{name: 'OrgHead_id', type: 'int', header: 'ID', key: true},
								{name: 'Person_id', type: 'int', hidden: true},
								{name: 'OrgHeadPerson_Fio', type: 'string', header: langs('ФИО'), width: 270},
								{name: 'OrgHeadPost_Name', type: 'string', header: langs('Должность'), width: 270},
								{name: 'OrgHead_Phone', type: 'string', header: langs('Телефон(ы)'), width: 270},
								{name: 'OrgHead_Fax', type: 'string', header: langs('Факс'), width: 270}
							],
							title: langs('Руководство'),
							toolbar: true,
							totalProperty: 'totalCount'
						})
					],
					listeners: {
						activate: function() {
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_OrgHeadGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: langs('4. Договоры по сторонним специалистам'),
					layout: 'fit',
					id: 'tab_dogdd',
					iconCls: 'info16',
					border:false,
					items:
					[
						new sw.Promed.ViewFrame(
						{
							autoLoadData: false,
							dataUrl: '/?c=LpuPassport&m=loadLpuDispContract',
							focusOn:
							{
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							focusPrev:
							{
								name: 'LPEW_CancelButton',
								type: 'button'
							},
							id: 'LPEW_DogDDGrid',
							paging: false,
							region: 'center',
							object: 'LpuDispContract',
							editformclassname: 'swLpuDispContractEditWindow',
							stringfields:
							[
								{name: 'LpuDispContract_id', type: 'int', header: 'ID', key: true},
								{name: 'LpuDispContract_setDate', type: 'date', header: 'Дата начала договора'},
								{name: 'LpuDispContract_disDate', type: 'date', header: 'Дата окончания договора'},
								{name: 'SideContractType_Name', type: 'string', header: 'Сторона договора', width: 150},
								{name: 'LpuDispContract_Num', type: 'string', header: langs('Номер')},
								{name: 'Lpu_oid', type: 'int', hidden: true},
								{id: 'autoexpand', name: 'Lpu_Nick', type: 'string', header: langs('МО'), width: 250},
								{name: 'LpuSectionProfile_Code', type: 'string', header: langs('Код профиля')},
								{name: 'LpuSection_id', type: 'int', hidden: true},
								{name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 220}
							],
							title: langs('Договоры по сторонним специалистам'),
							toolbar: true
						})
					],
					listeners:
					{
						activate: function() {
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_DogDDGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: langs('5. Электронная регистратура'),
					layout: 'fit',
					id: 'tab_er',
					bodyStyle:'background:#DFE8F6;padding:5px;',
					iconCls: 'info16',
                    scrolable: true,
					border:false,
					items: [
						{
							xtype: 'checkbox',
							autoHeight:true,
							boxLabel: langs('Разрешить модерацию записей из интернет'),
							id:'LPEW_IsAllowInternetModeration',
							name:'Lpu_IsAllowInternetModeration'
						},
						{
						xtype: 'panel',
						layout: 'form',
						border: false,
						id: 'LPEW_Lpu_ERPanel',
						bodyStyle:'background:#DFE8F6;padding:5px;',
						labelWidth: 170,
						labelAlign: 'top',
						items:
						[{
							xtype: 'textarea',
							//autoCreate: {tag: "input", size:5, maxLength: "3", autocomplete: "off"},
							fieldLabel: langs('Информация о возможности записи пациентов организацией, оказывающей услугу "Единая регистратура"'),
							name: 'Lpu_ErInfo',
							id: 'LPEW_Lpu_ErInfo',
							height: 200,
							width: 800
						}],
						listeners:
						{
							activate: function() {
								//Ext.getCmp('LpuPassportEditWindow').findById('LPEW_Lpu_AmbulanceCount').focus(100);
							}
						}
					}]
				},
				{
					title: langs('6. Здания МО'),
					layout: 'fit',
					id: 'tab_zdanie',
					iconCls: 'info16',
                    autoScroll: true,
					border:false,
					items: [
                        new Ext.form.FormPanel({
                        autoScroll: true,
                        bodyBorder: false,
                        bodyStyle: 'padding: 5px 5px 0',
                        border: false,
                        frame: false,
                        id: 'Lpu_PasportMOPanel',
                        labelAlign: 'right',
                        labelWidth: 180,
                        items: [
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style:'margin-bottom: 0.5em;',
                                //border: true,
                                collapsible: true,
                                collapsed: true,
                                id: 'Lpu_PasportMO',
                                layout: 'form',
                                title: langs('1. Общая информация'),
                                items: [{
                                    xtype: 'panel',
                                    layout: 'form',
                                    labelWidth: 220,
                                    border: false,
                                    bodyStyle:'background:#DFE8F6;padding:5px;',
                                    items: [{
                                        id: 'LPEW_PasportMO_id',
                                        xtype: 'hidden',
                                        name: 'PasportMO_id'
                                    },{
                                        id: 'LPEW_DLocationLpu_id',
                                        comboSubject: 'DLocationLpu',
                                        fieldLabel: langs('Местоположение'),
                                        tabIndex: 1120,
                                        xtype: 'swcommonsprcombo',
                                        width: 150,
                                        hiddenName: 'DLocationLpu_id'
                                    },{
                                        fieldLabel: langs('Ограждение территории'),
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'PasportMO_IsFenceTer',
                                        id: 'LPEW_PasportMO_IsFenceTer',
                                        tabIndex: TABINDEX_LPEEW + 1
                                    },{
                                        fieldLabel: langs('Наличие охраны'),
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'PasportMO_IsSecur',
                                        id: 'LPEW_PasportMO_IsSecur',
                                        tabIndex: TABINDEX_LPEEW + 1
                                    },{
                                        fieldLabel: langs('Наличие металлических входных дверей в здание'),
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'PasportMO_IsMetalDoors',
                                        id: 'LPEW_PasportMO_IsMetalDoors',
                                        tabIndex: TABINDEX_LPEEW + 1
                                    },{
                                        fieldLabel: langs('Видеонаблюдение территорий и помещений для здания'),
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'PasportMO_IsVideo',
                                        id: 'LPEW_PasportMO_IsVideo',
                                        tabIndex: TABINDEX_LPEEW + 1
                                    },{
                                        fieldLabel: langs('Проживание сопровождающих лиц'),
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'PasportMO_IsAccompanying',
                                        id: 'LPEW_PasportMO_IsAccompanying',
                                        tabIndex: TABINDEX_LPEEW + 1
                                    },{
                                        fieldLabel: langs('Приспособленность территории для пациентов с ограниченными возможностями'),
                                        xtype: 'checkbox',
                                        anchor: '100%',
                                        name: 'PasportMO_IsTerLimited',
                                        tabIndex: TABINDEX_LPEEW + 1,
                                        id: 'LPEW_PasportMO_IsTerLimited'
                                    }]
                                }]
                            }),
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style: 'margin-bottom: 0.5em;',
                                border: true,
                                collapsible: true,
                                collapsed: true,
                                id: 'LPEW_MOArea',
                                layout: 'form',
                                title: langs('2. Площадка, занимаемая организацией'),
                                listeners: {
                                    expand: function () {
                                        this.findById('LPEW_MOAreaGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add'},
                                            {name: 'action_edit'},
                                            {name: 'action_view'},
                                            {name: 'action_delete'},
                                            {name: 'action_refresh'},
                                            {name: 'action_print'}
                                        ],
                                        object: 'MOArea',
                                        editformclassname: 'swMOAreaEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        border: false,
                                        scheme: 'fed',
                                        dataUrl: '/?c=LpuPassport&m=loadMOArea',
                                        focusOn: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        focusPrev: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        id: 'LPEW_MOAreaGrid',
                                        //pageSize: 100,
                                        paging: false,
                                        region: 'center',
                                        //root: 'data',
                                        stringfields: [
                                            {name: 'MOArea_id', type: 'int', header: 'id', hidden: true},
                                            {name: 'MOArea_Name', type: 'string', header: langs('Наименование площадки'), width: 200},
                                            {name: 'MOArea_Member', type: 'string', header: langs('Идентификатор участка'), width: 200},
                                            {name: 'MoArea_Right', type: 'string', header: langs('Право на земельный участок'), width: 200},
                                            {name: 'MoArea_Space', header: langs('Площадь участка, га'), width: 100, renderer: this.numberRenderer },
                                            {name: 'MoArea_KodTer', type: 'string', header: langs('Код территории'), width: 100},
                                            {name: 'MoArea_OrgDT', type: 'date', header: langs('Дата организации'), width: 100},
                                            {name: 'MoArea_AreaSite', header: langs('Площадь площадки, га'), width: 100, renderer: this.numberRenderer },
                                            {name: 'MoArea_OKATO', type: 'string', header: langs('Код ОКАТО'), width: 100},
                                            {name: 'Address_Address', type: 'string', header: langs('Адрес'), id: 'autoexpand', width: 200}
                                        ],
                                        toolbar: true,
                                        totalProperty: 'totalCount'
                                    })
                                ]
                            }),
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style:'margin-bottom: 0.5em;',
                                //border: true,
                                collapsible: true,
                                collapsed: true,
                                id: 'Lpu_Svyaz',
                                layout: 'form',
                                title: langs('3. Связь с транспортными узлами'),
                                listeners: {
                                    expand: function () {
                                        this.findById('LPEW_TransportConnectGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add', handler: function() { _this.openTransportConnectEditWindow('add')} },
                                            {name: 'action_edit', handler: function() { _this.openTransportConnectEditWindow('edit')} },
                                            {name: 'action_view', handler: function() { _this.openTransportConnectEditWindow('view')} },
                                            {name: 'action_delete'},
                                            {name: 'action_refresh'},
                                            {name: 'action_print'}
                                        ],
                                        object: 'TransportConnect',
                                        editformclassname: 'swTransportConnectEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        border: false,
                                        scheme: 'passport',
                                        dataUrl: '/?c=LpuPassport&m=loadTransportConnect',
                                        focusOn: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        focusPrev: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        id: 'LPEW_TransportConnectGrid',
                                        //pageSize: 100,
                                        paging: false,
                                        region: 'center',
                                        //root: 'data',
                                        stringfields: [
                                            {name: 'TransportConnect_id', type: 'int', header: 'id', hidden: true},
                                            {name: 'MOArea_id', type: 'int',   header: 'id', hidden: true, width: 200},
                                            {name: 'MOArea_Name', type: 'string', header: langs('Наименование площадки, занимаемой учреждением'), width: 200},
                                            //{name: 'TransportConnect_AreaIdent', type: 'string', header: 'Идентификатор участка ', width: 200},
                                            {name: 'TransportConnect_Station', type: 'string', header: langs('Ближайшая станция'), width: 200},
                                            {name: 'TransportConnect_DisStation', header: langs('Расстояние до ближайшей станции (км)'), width: 200, renderer: this.numberRenderer },
                                            {name: 'TransportConnect_Airport', type: 'string', header: langs('Ближайший аэропорт'), width: 200},
                                            {name: 'TransportConnect_DisAirport', header: langs('Расстояние до аэропорта (км)'), width: 200, renderer: this.numberRenderer },
                                            {name: 'TransportConnect_Railway', type: 'string', header: langs('Ближайший автовокзал'), width: 200},
                                            {name: 'TransportConnect_DisRailway', header: langs('Расстояние до автовокзала (км)'), width: 200, renderer: this.numberRenderer },
                                            {name: 'TransportConnect_Heliport', type: 'string', header: langs('Ближайшая вертолетная площадка'), width: 200},
                                            {name: 'TransportConnect_DisHeliport', header: langs('Расстояние до вертолетной площадки (км)'), width: 200, renderer: this.numberRenderer },
                                            {name: 'TransportConnect_MainRoad', type: 'string', header: langs('Главная дорога'), width: 200}
                                        ],
                                        toolbar: true,
                                        totalProperty: 'totalCount'
                                    })]
                            }),
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style: 'margin-bottom: 0.5em;',
                                border: true,
                                collapsible: true,
                                collapsed: true,
                                id: 'LPEW_LpuBuilding_id',
                                layout: 'form',
                                title: langs('4. Здания МО'),
                                listeners: {
                                    expand: function () {
                                        this.findById('LPEW_LpuBuilding').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add'},
                                            {name: 'action_edit'},
                                            {name: 'action_view'},
                                            {name: 'action_delete', handler: function() {_this.deleteLpuBuildingPass();}},
                                            {name: 'action_refresh'},
                                            {name: 'action_print'}
                                        ],
                                        object: 'LpuBuildingPass',
                                        editformclassname: 'swLpuBuildingEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        dataUrl: '/?c=LpuPassport&m=loadLpuBuilding',
                                        focusOn: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        focusPrev: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        id: 'LPEW_LpuBuilding',
                                        //pageSize: 100,
                                        paging: false,
                                        region: 'center',
                                        //root: 'data',
                                        stringfields: [
                                            {name: 'LpuBuildingPass_id', type: 'int', header: 'ID', key: true},
                                            {name: 'LpuBuildingPass_Name', type: 'string', header: langs('Наименование'), id: 'autoexpand'},
                                            {name: 'LpuBuildingPass_BuildingIdent', type: 'string', header: langs('Идентификатор'), width: 120},
                                            {name: 'LpuBuildingType_Name', type: 'string', header: langs('Тип'), width: 220},
                                            {name: 'BuildingAppointmentType_Name', type: 'string', header: langs('Назначение'), width: 180},
                                            {name: 'LpuBuildingPass_YearBuilt', type: 'date', header: langs('Дата постройки'), width: 120},
                                            {name: 'LpuBuildingPass_TotalArea', header: langs('Общая площадь'), width: 180, renderer: this.numberRenderer},
                                            {name: 'LpuBuildingPass_RegionArea', type: 'string', hidden: true, header: langs('Площадь участка'), width: 180}
                                        ],
                                        toolbar: true,
                                        totalProperty: 'totalCount'
                                    })
                                ]
                            }),
                            new sw.Promed.Panel({
                                autoHeight: true,
                                style: 'margin-bottom: 0.5em;',
                                border: true,
                                collapsible: true,
                                collapsed: true,
                                id: 'LPEW_MOAreaObject',
                                layout: 'form',
                                title: langs('5. Объекты инфраструктуры'),
                                listeners: {
                                    expand: function () {
                                        this.findById('LPEW_MOAreaObjectGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                    }.createDelegate(this)
                                },
                                items: [
                                    new sw.Promed.ViewFrame({
                                        actions: [
                                            {name: 'action_add'},
                                            {name: 'action_edit'},
                                            {name: 'action_view'},
                                            {name: 'action_delete'},
                                            {name: 'action_refresh'},
                                            {name: 'action_print'}
                                        ],
                                        object: 'MOAreaObject',
                                        editformclassname: 'swMOAreaObjectEditWindow',
                                        autoExpandColumn: 'autoexpand',
                                        autoExpandMin: 150,
                                        autoLoadData: false,
                                        border: false,
                                        scheme: 'fed',
                                        dataUrl: '/?c=LpuPassport&m=loadMOAreaObject',
                                        focusOn: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        focusPrev: {
                                            name: 'LPEW_CancelButton',
                                            type: 'button'
                                        },
                                        id: 'LPEW_MOAreaObjectGrid',
                                        //pageSize: 100,
                                        paging: false,
                                        region: 'center',
                                        //root: 'data',
                                        stringfields: [
                                            {name: 'MOAreaObject_id', type: 'int', header: 'id', hidden: true},
                                            {name: 'DObjInfrastructure_Name', type: 'string', header: langs('Наименование объекта'), width: 270},
                                            {name: 'MOAreaObject_Count', type: 'int', header: langs('Количество объектов'), width: 270},
                                            {name: 'MOAreaObject_Member', type: 'string', header: langs('Идентификатор участка')}
                                        ],
                                        toolbar: true,
                                        totalProperty: 'totalCount'
                                    })
                                ]
                            })

                    ]
                })],
					listeners:
					{
						activate: function() {
							if (!Ext.isEmpty(this.Lpu_id)) {
								if (!Ext.getCmp('LPEW_MOArea').collapsed)
									Ext.getCmp('LPEW_MOAreaGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('Lpu_Svyaz').collapsed)
									Ext.getCmp('LPEW_TransportConnectGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('LPEW_LpuBuilding_id').collapsed)
									Ext.getCmp('LPEW_LpuBuilding').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								if (!Ext.getCmp('LPEW_MOAreaObject').collapsed)
									Ext.getCmp('LPEW_MOAreaObjectGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
                },
					{
					title: langs('7. Оборудование и транспорт'),
					layout: 'fit',
					id: 'tab_medprod',
					iconCls: 'info16',
					border:false,
					items: [
						new Ext.form.FormPanel({
							autoHeight: true,
							border: true,
							collapsible: true,
							collapsed: true,
							layout: 'form',
							title: 'Фильтры',
							autoScroll: true,
							frame: true,
							id: 'EquipmentAndTransportFilter',
							labelAlign: 'right',
							labelWidth: 250,
							bodyStyle:'background:#DFE8F6; max-height: 200px; padding: 10px;',
							items: [{
								border: false,
                                layout: 'column',
								//labelWidth: 150,
                                items: [{
                                    border: false,
                                    columnWidth: .40,
                                    layout: 'form',
									items: [{
										xtype: 'textfield',
										width: 300,
										id: 'mpwpSearch_InventarniyNomer',
										name: 'AccountingData_InventNumber',
										fieldLabel: lang['inventarnyiy_nomer'],
										editable: true
									},{
										xtype: 'textfield',
										width: 300,
										id: 'mpwpSearch_NaimenovanieMI',
										name: 'MedProductClass_Name',
										fieldLabel: lang['naimenovanie_mi'],
										editable: true
									},{
										xtype: 'textfield',
										width: 300,
										id: 'mpwpSearch_ModelMI',
										name: 'MedProductClass_Model',
										fieldLabel: lang['model_mi'],
										editable: true
									},{
										xtype: 'textfield',
										width: 300,
										id: 'mpwpSearch_SerienieNomer',
										name: 'MedProductCard_SerialNumber',
										fieldLabel: lang['seriynyiy_nomer'],
										editable: true
									},{
										xtype: 'swcommonsprcombo',
										comboSubject: 'CardType',
										prefix: 'passport_',
										width: 300,
										id: 'mpwpSearch_TipMeditsinskogoIzdelia',
										name: 'CardType_Name',
										fieldLabel: lang['tip_meditsinskogo_izdeliya'],
										editable: true
									},{
										id: 'mpwpSearch_TipMeditsinskogoOborudovaniaFrmo',
										name: 'FRMOEquipment_id',
										fieldLabel: lang['tip_meditsinskogo_oborudovania_frmo'],
										xtype: 'swcommonsprcombo',
										width: 300,
										comboSubject: 'FRMOEquipment',
										prefix: 'passport_',
										editable: true
									},{
										xtype: 'swcommonsprcombo',
										comboSubject: 'ClassRiskType',
										prefix: 'passport_',
										width: 300,
										id: 'mpwpSearch_KlassRiskaPrimeneniya',
										name: 'ClassRiskType_Name',
										fieldLabel: lang['klass_riska_primeneniya'],
										editable: true
									},{
										xtype: 'swcommonsprcombo',
										comboSubject: 'FuncPurpType',
										prefix: 'passport_',
										width: 300,
										id: 'mpwpSearch_FunctionalinoeNaznachenie',
										name: 'FuncPurpType_Name',
										fieldLabel: lang['functionalinoe_naznachenie'],
										editable: true
									}]
								},{
									border: false,
                                    columnWidth: .40,
                                    layout: 'form',
									items: [{
										xtype: 'swcommonsprcombo',
										comboSubject: 'UseAreaType',
										prefix: 'passport_',
										width: 300,
										id: 'mpwpSearch_OblastPrimeneniya',
										name: 'UseAreaType_Name',
										fieldLabel: lang['oblast_primeneniya'],
										editable: true
									},{
										xtype: 'swcommonsprcombo',
										comboSubject: 'UseSphereType',
										prefix: 'passport_',
										width: 300,
										id: 'mpwpSearch_SferaPrimeneniya',
										name: 'UseSphereType_Name',
										fieldLabel: lang['sfera_primeneniya'],
										editable: true
									},{
										anchor: '100%',
										Lpu_id: function() {
											var searchForm = this.findById('EquipmentAndTransportFilter').getForm();
											return searchForm.findById('mpwpSearch_LpuBuilding_Name').Lpu_id = _this.Lpu_id;
										},
										allowLowLevelRecordsOnly: false,
										fieldLabel: lang['podrazdelenie'],
										id: 'mpwpSearch_LpuBuilding_Name',
										name: 'LpuBuilding_Name',
										object: 'SubDivision',
										tabIndex: TABINDEX_SPEF + 5,
										Sub_SysNick: '',
										selectionWindowParams: {
											height: 500,
											title: lang['podrazdelenie'],
											width: 600
										},
										valueFieldId: 'mpwpSearch_LpuBuilding_id',
										xtype: 'swtreeselectionfield',
										load: function() {
											var searchForm = this.findById('EquipmentAndTransportFilter').getForm();

											searchForm.findField('LpuBuilding_Name').Sub_SysNick = 'LpuBuilding';
											searchForm.findField('MPCE_SubSection_id').setValue(responseText[0].LpuBuilding_id);
											searchForm.findField('LpuBuilding_Name').setNameWithPath();
										}									
									},{
										id: 'mpwpSearch_SubSection_id',
										name: 'SubSection_id',
										xtype: 'hidden'
									}, {
										id: 'mpwpSearch_LpuBuilding_id',
										name: 'LpuBuilding_id',
										xtype: 'hidden'
									}, {
										id: 'mpwpSearch_LpuUnit_id',
										name: 'LpuUnit_id',
										xtype: 'hidden'
									}, {
										id: 'mpwpSearch_LpuSection_id',
										name: 'LpuSection_id',
										xtype: 'hidden'
									},{
										xtype: 'swcommonsprcombo',
										comboSubject: 'FinancingType',
										width: 300,
										id: 'mpwpSearch_ProgrammaZakupki',
										name: 'FinancingType_Name',
										fieldLabel: lang['programma_zakupki'],
										editable: true
									},{
										xtype: 'swdatefield',
										//width: 80,
										id: 'mpwpSearch_DataVvodaVEkspluataciu',
										name: 'AccountingData_setDate',
										fieldLabel: lang['data_vvoda_v_ekspluataciu'],
										format: 'd.m.Y',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										editable: true
									},{
										xtype: 'textfield',
										width: 300,
										id: 'mpwpSearch_RegistratsionnyiyZnakDlyaAvtomobiley',
										name: 'AccountingData_RegNumber',
										fieldLabel: lang['registratsionnyiy_znak_dlya_avtomobiley'],
										editable: true
									},{
										xtype: 'textfield',
										width: 300,
										id: 'mpwpSearch_BortovoyNomer',
										name: 'MedProductCard_BoardNumber',
										fieldLabel: lang['bortovoy_nomer'],
										editable: true
									},{
										xtype: 'checkbox',
										width: 300,
										id: 'mpwpSearch_NedostypnoDliaFRMO',
										name: 'MedProductCard_IsNotFRMO',
										fieldLabel: lang['nedostypno_dlia_FRMO'],
										editable: true
									}]
								}]
							},{			
								layout: 'column',
								border: false,
								style: 'margin-left: 325px',
								bodyStyle: 'background:#DFE8F6;padding-right:0px;',
								items: [{
										style: 'margin-left: 20px',
										xtype: 'button',
										iconCls: 'search16',
										id: 'EquipmentAndTransport__SearchButton',
										text: BTN_FRMSEARCH,
										listeners:
										{
											'click': function () {
												_this.doSearchEquipmentAndTransport();
											}
										}
									},{
										style: 'margin-left: 20px',
										xtype: 'button',
										iconCls: 'resetsearch16',
										id: 'EquipmentAndTransport__ResetButton',
										text: BTN_FRMRESET,
										listeners:
										{
											'click': function () {
												_this.resetSearchEquipmentAndTransport();
											}
										},
									}
								]
							}],
							keys: [{
								fn: function(e) {
									_this.doSearchEquipmentAndTransport();
								},
								key: Ext.EventObject.ENTER,
								stopEvent: true
							}]
						}),
                        new sw.Promed.ViewFrame({
                            actions: [
                                {name: 'action_add', handler: function() {this.openMedProductCardEditWindow('add', this.Lpu_id);}.createDelegate(this)},
                                {name: 'action_edit', handler: function() {this.openMedProductCardEditWindow('edit', this.Lpu_id);}.createDelegate(this)},
                                {name: 'action_view', handler: function() {this.openMedProductCardEditWindow('view', this.Lpu_id);}.createDelegate(this)},
                                {name: 'action_delete', handler: function() {this.deleteMedProductCard();}.createDelegate(this), hidden: !(isSuperAdmin() || getGlobalOptions().groups.indexOf('LpuAdmin') != -1 || getGlobalOptions().groups.indexOf('MPCModer') != -1 )},
                                {name: 'action_refresh'},
                                {name: 'action_print'}
                            ],
                            object: 'MedProductCard',
                            scheme: 'passport',
                            editformclassname: 'swMedProductCardEditWindow',
                            autoExpandColumn: 'autoexpand',
                            //autoExpandMin: 150,
                            autoLoadData: false,
							border: false,
							//layout: 'fit',
							Lpu_id: getGlobalOptions().lpu_id,
                            dataUrl: '/?c=LpuPassport&m=loadMedProductCard',
                            focusOn: {
                                name: 'LPEW_CancelButton',
                                type: 'button'
                            },
                            focusPrev: {
                                name: 'LPEW_CancelButton',
                                type: 'button'
                            },
							id: 'LPEW_MedProductCardGrid',
							scrolable: true,
							height: 720,
							minHeight: 400,
							paging: false,
                            title: langs('Медицинские изделия'),
							region: 'center',
                            stringfields: [
                                {name: 'MedProductCard_id', type: 'int', header: 'ID', key: true},
                                //{name: 'LpuEquipmentPacs_id', type: 'int', header: 'pacs', hidden: true},
                                { name: 'AccountingData_InventNumber', type: 'string', header: langs('Инвентарный номер'), width: 150 },
                                { name: 'MedProductClass_Name', type: 'string', header: langs('Наименование МИ'), width: 150 },
                                { name: 'MedProductClass_Model', type: 'string', header: langs('Модель МИ'), width: 150 },
								{ name: 'MedProductCard_SerialNumber', type: 'string', header: langs('Серийный номер'), width: 150 },
								{ name: 'FRMOEquipment_Name', type: 'string', header: langs('Тип медицинского оборудования (ФРМО)'), width: 150 },
								{ name: 'CardType_id', type: 'int', hidden: true },
								{ name: 'CardType_Name', type: 'string', header: langs('Тип медицинского изделия'), width: 150 },
								{ name: 'ClassRiskType_id', type: 'int', hidden: true },
								{ name: 'ClassRiskType_Name', type: 'string', header: langs('Класс риска применения'), width: 150 },
								{ name: 'FuncPurpType_id', type: 'int', hidden: true },
								{ name: 'FuncPurpType_Name', type: 'string', header: langs('Функциональное назначение'), width: 150 },
								{ name: 'UseAreaType_id', type: 'int', hidden: true },
								{ name: 'UseAreaType_Name', type: 'string', header: langs('Область применения'), width: 150 },
								{ name: 'UseSphereType_id', type: 'int', hidden: true },
								{ name: 'UseSphereType_Name', type: 'string', header: langs('Сфера применения'), width: 150 },
								{ name: 'PrincipleWorkTYpe_id', type: 'int', hidden: true },
								{ name: 'PrincipleWorkType_Name', type: 'string', header: 'Принцип работы', width: 150 },
                                { name: 'LpuBuilding_Name', type: 'string', header: langs('Код подразделения'), width: 150 },
								{ name: 'LpuBuilding_id', type: 'int', hidden: true },
                                { name: 'Org_Nick', type: 'string', header: langs('Производитель'), width: 150 },
								{ name: 'MedProductCard_begDate', type: 'string', header: langs('Дата выпуска'), width: 150 },
								{ name: 'FinancingType_id', type: 'int', hidden: true },
								{ name: 'FinancingType_Name', type: 'string', header: langs('Программа закупки'), width: 150 },
                                { name: 'AccountingData_setDate', type: 'date', header: langs('Дата ввода в эксплуатацию'), width: 150 },
                                { name: 'AccountingData_BuyCost', type: 'string', header: langs('Стоимость приобретения'), width: 150 },
                                { name: 'GosContract_Number', type: 'string', header: langs('Номер гос. контракта'), width: 150 },
                                { name: 'GosContract_setDate', type: 'string', header: langs('Дата заключения контракта'), width: 150, renderer: Ext.util.Format.dateRenderer('d.m.Y') },
								{ name: 'AccountingData_RegNumber', type: 'string', header: langs('Регистрационный знак'), width: 150 },
								{ name: 'MedProductCard_BoardNumber', type: 'string', header: langs('Бортовой номер'), width: 150 },
								{ name: 'MedProductCard_IsNotFRMO', type: 'checkbox', header: langs('Не передавать на ФРМО'), width: 150 }
							],
                            toolbar: true,
							//totalProperty: 'totalCount',
						})
					],
					listeners: {
						activate: function() {
							Ext.getCmp('LPEW_MedProductCardGrid').loadData({
								globalFilters:{Lpu_id: this.Lpu_id}, 
								params:{Lpu_id: this.Lpu_id},
							});
						}.createDelegate(this),
					}
				},
					{
					title: '8. PACS',
					layout: 'fit',
					id: 'tab_pacs_equipment',
					iconCls: 'info16',
					border:false,
					items: [
                        new sw.Promed.ViewFrame({
                            actions: [
                                {name: 'action_add', handler: function() {this.openEquipmentEditWindow('add', this.Lpu_id);}.createDelegate(this)},
                                {name: 'action_edit', handler: function() {this.openEquipmentEditWindow('edit', this.Lpu_id);}.createDelegate(this)},
                                {name: 'action_view', handler: function() {this.openEquipmentEditWindow('view', this.Lpu_id);}.createDelegate(this)},
                                {name: 'action_delete', handler: function() {this.deleteEquipment();}.createDelegate(this)},
                                {name: 'action_refresh'},
                                {name: 'action_print'}
                            ],
                            object: 'LpuEquipment',
                            editformclassname: 'swLpuEquipmentEditWindow',
                            autoExpandColumn: 'autoexpand',
                            autoExpandMin: 150,
                            autoLoadData: false,
                            border: false,
                            dataUrl: '/?c=LpuPassport&m=loadLpuEquipment',
                            focusOn: {
                                name: 'LPEW_CancelButton',
                                type: 'button'
                            },
                            focusPrev: {
                                name: 'LPEW_CancelButton',
                                type: 'button'
                            },
                            id: 'LPEW_EquipmentGrid',
                            //pageSize: 100,
                            paging: false,
                            title: langs('Оборудование PACS'),
                            region: 'center',
                            //root: 'data',
                            stringfields: [
                                {name: 'id', type: 'int', header: 'ID', key: true},
                                {name: 'LpuEquipment_id', type: 'int', header: 'eq', hidden: true},
                                {name: 'LpuEquipmentPacs_id', type: 'int', header: 'pacs', hidden: true},
                                {name: 'LpuEquipment_Name', type: 'string', header: langs('Наименование'), width: 270},
                                {name: 'LpuEquipment_Model', type: 'string', header: langs('Модель'), width: 270},
                                {name: 'LpuEquipment_InvNum', type: 'string', header: langs('Инвентарный номер'), width: 270}
                            ],
                            toolbar: true,
                            totalProperty: 'totalCount'
                        })
					],
					listeners:
					{
						activate: function() {
							Ext.getCmp('LPEW_EquipmentGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
						}.createDelegate(this)
					}
				},
					{
					title: '9. ' + langs('Обслуживаемое население'),
					layout: 'fit',
					id: 'tab_population',
					iconCls: 'info16',
					border:false,
					items: [{
						autoScroll: true,
						bodyBorder: false,
						bodyStyle: 'padding: 5px 5px 0',
						border: false,
						frame: false,
						labelAlign: 'right',
						labelWidth: 250,
						items:
						[
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								layout: 'form',
								title: '1. ' + langs('Обслуживаемое население'),
								bodyStyle:'background:#DFE8F6;',
								items: [new Ext.form.FormPanel({
										border: false,
										bodyStyle:'background:#DFE8F6; padding: 5px;',
										labelWidth: 200,
										items:
										[{
                                            fieldLabel: langs('Прикрепленное население'),
                                            xtype: 'numberfield',
                                            allowDecimals: true,
                                            maxLength:24,
                                            width: 300,
                                            name: 'PasportMO_Popul',
                                            id: 'LPEW_PasportMO_Popul',
                                            disabled: true
                                        }, {
                                            fieldLabel: langs('Городское население'),
                                            xtype: 'numberfield',
                                            allowDecimals: true,
                                            maxLength:24,
                                            width: 300,
                                            name: 'PasportMO_CityPopul',
                                            id: 'LPEW_PasportMO_CityPopul',
                                            disabled: true
                                        }, {
                                            fieldLabel: langs('Сельское население'),
                                            xtype: 'numberfield',
                                            allowDecimals: true,
                                            maxLength:24,
                                            width: 300,
                                            name: 'PasportMO_TownPopul',
                                            id: 'LPEW_PasportMO_TownPopul',
                                            disabled: true
                                        }, {
                                            fieldLabel: langs('Дата актуальности'),
                                            xtype: 'datefield',
                                            width: 150,
                                            name: 'PasportMO_calcDate',
                                            id: 'LPEW_PasportMO_calcDate',
                                            disabled: true
                                        }]
									})]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								layout: 'form',
								title: langs('2. Территории обслуживания'),
								listeners: {
									expand: function () {
										this.findById('LPEW_OrgServiceTerrGrid').loadData({globalFilters:{Org_id: this.Org_id}, params:{Org_id: this.Org_id}});
									}.createDelegate(this)
								},
								bodyStyle:'background:#DFE8F6;',
								items: [
									new Ext.form.FormPanel({
										border: false,
										bodyStyle:'background:#DFE8F6; padding: 5px;',
										id: 'Lpu_PopulationPanel',
										labelWidth: 300,
										items:
										[{
                                            fieldLabel: langs('Расстояние до наиболее удаленной точки территориального обслуживания, км'),
                                            xtype: 'numberfield',
                                            allowDecimals: true,
                                            decimalPrecision: 5,
                                            maxLength:24,
                                            width: 300,
                                            name: 'PasportMO_MaxDistansePoint',
                                            id: 'LPEW_PasportMO_MaxDistansePoint',
                                            tabIndex: TABINDEX_LPEEW + 3
                                        }]
									}),
									// грид обслуживаемое население
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'OrgServiceTerr',
										editformclassname: 'swOrgServiceTerrEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										dataUrl: '/?c=OrgServiceTerr&m=loadOrgServiceTerrGrid',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_OrgServiceTerrGrid',
										paging: false,
										region: 'center',
										stringfields: [
											{name: 'OrgServiceTerr_id', type: 'int', header: 'ID', key: true},
											{name: 'KLCountry_Name', type: 'string', header: langs('Страна'), width: 120},
											{name: 'KLRgn_Name', type: 'string', header: langs('Регион'), width: 120},
											{name: 'KLSubRgn_Name', type: 'string', header: langs('Район'), width: 120},
											{name: 'KLCity_Name', type: 'string', header: langs('Город'), width: 120},
											{name: 'KLTown_Name', type: 'string', header: langs('Населенный пункт'), width: 120},
											{name: 'KLAreaType_Name', type: 'string', header: langs('Тип населенного пункта'), width: 120}
										],
										totalProperty: 'totalCount'
									})
								]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								style: 'margin-bottom: 0.5em;',
								border: true,
								collapsible: true,
								layout: 'form',
								title: langs('3. Расчетные квоты'),
								listeners: {
									expand: function () {
										this.findById('LPEW_LpuQuoteGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									}.createDelegate(this)
								},
								items: [
									new sw.Promed.ViewFrame({
										actions: [
											{name: 'action_add'},
											{name: 'action_edit'},
											{name: 'action_view'},
											{name: 'action_delete'},
											{name: 'action_refresh'},
											{name: 'action_print'}
										],
										object: 'LpuQuote',
										editformclassname: 'swLpuQuoteEditWindow',
										autoExpandColumn: 'autoexpand',
										autoExpandMin: 150,
										autoLoadData: false,
										border: false,
										dataUrl: '/?c=LpuPassport&m=loadLpuQuote',
										focusOn: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										focusPrev: {
											name: 'LPEW_CancelButton',
											type: 'button'
										},
										id: 'LPEW_LpuQuoteGrid',
										//pageSize: 100,
										paging: false,
										region: 'center',
										//root: 'data',
										stringfields: [
											{name: 'LpuQuote_id', type: 'int', header: 'ID', key: true},
											{name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 120},
											{name: 'LpuQuote_HospCount', type: 'string', header: langs('Кол-во госпитализаций'), width: 120},
											{name: 'LpuQuote_BedDaysCount', type: 'string', header: langs('Кол-во койко-дней'), width: 120},
											{name: 'LpuQuote_VizitCount', type: 'string', header: langs('Кол-во посещений'), width: 120},
											{name: 'LpuQuote_begDate', type: 'string', header: langs('Начало'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')},
											{name: 'LpuQuote_endDate', type: 'string', header: langs('Окончание'), width: 120, renderer: Ext.util.Format.dateRenderer('d.m.Y')}
										],
										totalProperty: 'totalCount'
									})
								]
							})
						]
					}],
					listeners:
					{
						activate: function() {
							if (!Ext.isEmpty(this.Org_id)) {
								Ext.getCmp('LPEW_OrgServiceTerrGrid').loadData({globalFilters:{Org_id: this.Org_id}, params:{Org_id: this.Org_id}});
							}
							if (!Ext.isEmpty(this.Lpu_id)) {
								Ext.getCmp('LPEW_LpuQuoteGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
							}
						}.createDelegate(this)
					}
				},
				{
					title: langs('10. Виды помощи'),
					layout: 'fit',
					id: 'tab_med_care',
					iconCls: 'info16',
					bodyStyle:'background:#DFE8F6;',
					border:false,
					items: [
						new Ext.form.HtmlEditor({
							hideLabel: true,
							name: 'Lpu_MedCare',
							id: 'LPEW_Lpu_MedCare',
							defaultValue: ''
						})
					]
				},
					{
                        title: langs('11. Санаторно-курортное лечение'),
                        layout: 'fit',
                        id: 'tab_sanatoriumtreatment',
                        iconCls: 'info16',
                        border:false,
                        items: [
                            new Ext.form.FormPanel({
                                autoScroll: true,
                                bodyBorder: false,
                                bodyStyle: 'padding: 5px 5px 0',
                                border: false,
                                frame: false,
                                id: 'STForm',
                                labelAlign: 'right',
                                labelWidth: 180,
                                items: [
                                    new sw.Promed.Panel({
                                        autoHeight: true,
                                        style: 'margin-bottom: 0.5em;',
                                        border: true,
                                        collapsible: true,
                                        id: 'LPEW_KurortStatus',
                                        layout: 'form',
                                        title: langs('1. Статус курорта'),
                                        listeners: {
                                            expand: function () {
                                                this.findById('LPEW_KurortStatusGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                            }.createDelegate(this)
                                        },
                                        items: [
                                            new sw.Promed.ViewFrame({
                                                actions: [
                                                    {name: 'action_add'},
                                                    {name: 'action_edit'},
                                                    {name: 'action_view'},
                                                    {name: 'action_delete'},
                                                    {name: 'action_refresh'},
                                                    {name: 'action_print'}
                                                ],
                                                object: 'KurortStatusDoc',
                                                scheme: 'fed',
                                                editformclassname: 'swKurortStatusEditWindow',
                                                autoExpandColumn: 'autoexpand',
                                                autoExpandMin: 150,
                                                autoLoadData: false,
                                                border: false,
                                                dataUrl: '/?c=LpuPassport&m=loadKurortStatus',
                                                focusOn: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                focusPrev: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                id: 'LPEW_KurortStatusGrid',
                                                //pageSize: 100,
                                                paging: false,
                                                region: 'center',
                                                //root: 'data',
                                                stringfields: [
                                                    {name: 'KurortStatusDoc_id', type: 'int', hidden: true},
                                                    {name: 'KurortStatusDoc_id', type: 'int', header: 'ID', key: true},
                                                    {name: 'KurortStatusDoc_IsStatus', type: 'checkcolumn', header: langs('Наличие статуса курорта'), width: 160},
                                                    {name: 'KurortStatus_Name', type: 'string', header: langs('Статус курорта'), width: 270},
                                                    {name: 'KurortStatusDoc_Doc', type: 'string', header: langs('Документ'), width: 270},
                                                    {name: 'KurortStatusDoc_Num', type: 'string', header: langs('Номер документа'), width: 270},
                                                    {name: 'KurortStatusDoc_Date', type: 'date', header: langs('Дата документа')}
                                                ],
                                                toolbar: true
                                                //totalProperty: 'totalCount'
                                            })
                                        ]
                                    }),
                                    new sw.Promed.Panel({
                                        autoHeight: true,
                                        style: 'margin-bottom: 0.5em;',
                                        border: true,
                                        collapsible: true,
                                        //id: 'Lpu_Transport',//Округ горно-санитарной охраны id
                                        layout: 'form',
                                        id: 'LPEW_DisSanProtection',
                                        title: langs('2. Округ горно-санитарной охраны'),
                                        listeners: {
                                            expand: function () {
                                                this.findById('LPEW_DisSanProtectionGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                            }.createDelegate(this)
                                        },
                                        items: [
                                            new sw.Promed.ViewFrame({
                                                actions: [
                                                    {name: 'action_add'},
                                                    {name: 'action_edit'},
                                                    {name: 'action_view'},
                                                    {name: 'action_delete'},
                                                    {name: 'action_refresh'},
                                                    {name: 'action_print'}
                                                ],
                                                object: 'DisSanProtection',
                                                editformclassname: 'swDisSanProtectionEditWindow',
                                                autoExpandColumn: 'autoexpand',
                                                autoExpandMin: 150,
                                                autoLoadData: false,
                                                border: false,
                                                scheme: 'fed',
                                                dataUrl: '/?c=LpuPassport&m=loadDisSanProtection',
                                                focusOn: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                focusPrev: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                id: 'LPEW_DisSanProtectionGrid',
                                                //pageSize: 100,
                                                paging: false,
                                                region: 'center',
                                                //root: 'data',
                                                stringfields: [
                                                    {name: 'DisSanProtection_id', type: 'int', header: 'ID', key: true},
                                                    {name: 'DisSanProtection_IsProtection', type: 'checkcolumn', header: langs('Признак наличия округа'), width: 150},
                                                    {name: 'DisSanProtection_Doc', type: 'string', header: langs('Документ'), width: 270},
                                                    {name: 'DisSanProtection_Num', type: 'string', header: langs('Номер документа'), width: 270},
                                                    {name: 'DisSanProtection_Date', type: 'date', header: langs('Дата документа'), width: 270}
                                                ],
                                                toolbar: true
                                                //totalProperty: 'totalCount'
                                            })
                                        ]
                                    }),
                                    new sw.Promed.Panel({
                                        autoHeight: true,
                                        style: 'margin-bottom: 0.5em;',
                                        border: true,
                                        collapsible: true,
                                        id: 'LPEW_KurortTypeLink',
                                        layout: 'form',
                                        title: langs('3. Тип курорта'),
                                        listeners: {
                                            expand: function () {
                                                this.findById('LPEW_KurortTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                            }.createDelegate(this)
                                        },
                                        items: [
                                            new sw.Promed.ViewFrame({
                                                actions: [
                                                    {name: 'action_add'},
                                                    {name: 'action_edit'},
                                                    {name: 'action_view'},
                                                    {name: 'action_delete'},
                                                    {name: 'action_refresh'},
                                                    {name: 'action_print'},
                                                ],
                                                object: 'KurortTypeLink',
                                                editformclassname: 'swKurortTypeLinkEditWindow',
                                                autoExpandColumn: 'autoexpand',
                                                autoExpandMin: 150,
                                                autoLoadData: false,
                                                border: false,
                                                scheme: 'fed',
                                                dataUrl: '/?c=LpuPassport&m=loadKurortTypeLink',
                                                focusOn: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                focusPrev: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                id: 'LPEW_KurortTypeLinkGrid',
                                                //pageSize: 100,
                                                paging: false,
                                                region: 'center',
                                                //root: 'data',
                                                stringfields: [
                                                    {name: 'KurortTypeLink_id', type: 'int', header: 'ID', key: true},
                                                    {name: 'KurortTypeLink_IsKurortTypeLink', type: 'checkcolumn', header: langs('Наличие типа курорта'), width: 150},
                                                    {name: 'KurortType_Name', type: 'string', header: langs('Тип курорта'), width: 270, id: 'autoexpand'},
                                                    {name: 'KurortTypeLink_Doc', type: 'string', header: langs('Документ'), width: 270},
                                                    {name: 'KurortTypeLink_Num', type: 'string', header: langs('Номер документа'), width: 270},
                                                    {name: 'KurortTypeLink_Date', type: 'date', header: langs('Дата документа'), width: 270}
                                                ],
                                                toolbar: true
                                                //totalProperty: 'totalCount'
                                            })
                                        ]
                                    }),
                                    new sw.Promed.Panel({
                                        autoHeight: true,
                                        style: 'margin-bottom: 0.5em;',
                                        border: true,
                                        collapsible: true,
                                        id: 'LPEW_MOArrival',
                                        layout: 'form',
                                        title: langs('4. Заезды'),
                                        listeners: {
                                            expand: function () {
                                                this.findById('LPEW_MOArrivalGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
                                            }.createDelegate(this)
                                        },
                                        items: [
                                            new sw.Promed.ViewFrame({
                                                actions: [
                                                    {name: 'action_add'},
                                                    {name: 'action_edit'},
                                                    {name: 'action_view'},
                                                    {name: 'action_delete'},
                                                    {name: 'action_refresh'},
                                                    {name: 'action_print'}
                                                ],
                                                object: 'MOArrival',
                                                editformclassname: 'swMOArrivalEditWindow',
                                                autoExpandColumn: 'autoexpand',
                                                autoExpandMin: 150,
                                                autoLoadData: false,
                                                border: false,
                                                scheme: 'fed',
                                                dataUrl: '/?c=LpuPassport&m=loadMOArrival',
                                                focusOn: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                focusPrev: {
                                                    name: 'LPEW_CancelButton',
                                                    type: 'button'
                                                },
                                                id: 'LPEW_MOArrivalGrid',
                                                //pageSize: 100,
                                                paging: false,
                                                region: 'center',
                                                //root: 'data',
                                                stringfields: [
                                                    {name: 'MOArrival_id', type: 'int', header: 'ID', hidden: true},
                                                    {name: 'MOArrival_CountPerson', type: 'int', header: langs('Количество человек в заезде'), width: 270},
                                                    {name: 'MOArrival_TreatDis', type: 'int', header: langs('Длительность лечения'), width: 270},
                                                    {name: 'MOArrival_EndDT', type: 'date', header: langs('Дата окончания заезда'), width: 270}

                                                ],
                                                toolbar: true,
                                                totalProperty: 'totalCount'
                                            })
                                        ]
                                    })
                                ]
                            })
                        ],
                        listeners:
                        {
                            activate: function() {
								if (!Ext.isEmpty(this.Lpu_id)) {
									if (!Ext.getCmp('LPEW_KurortStatus').collapsed)
										Ext.getCmp('LPEW_KurortStatusGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									if (!Ext.getCmp('LPEW_DisSanProtection').collapsed)
										Ext.getCmp('LPEW_DisSanProtectionGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									if (!Ext.getCmp('LPEW_KurortTypeLink').collapsed)
										Ext.getCmp('LPEW_KurortTypeLinkGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
									if (!Ext.getCmp('LPEW_MOArrival').collapsed)
										Ext.getCmp('LPEW_MOArrivalGrid').loadData({globalFilters:{Lpu_id: this.Lpu_id}, params:{Lpu_id: this.Lpu_id}});
								}
                            }.createDelegate(this)
                        }
                    },
					{
						title: '12. ' + langs('Оснащенность компьютерным оборудованием'),
						id: 'tab_computer_equipment',
						iconCls: 'info16',
						bodyStyle:'background:#DFE8F6;',
						layout: 'anchor',
						anchorSize: {width:100, height: 100},
						//autoHeight:true,
						border:false,
						items:
							[
								//new sw.Promed.Panel({
								//border: false,
								//collapsible: false,
								//title: langs('Фильтр'),
								//id: 'ComputerEquipmentFilterPanel',
								//bodyStyle:'background:#DFE8F6;',
								////anchor: '100%',
								//items: [
									new Ext.form.FormPanel({
										hidden:false,
										bodyStyle:'background:#DFE8F6; padding: 5px;',
										id: 'LpuComputerEquipFilter',
										labelWidth: 200,
										labelAlign: 'right',
										title: langs('Фильтр'),
										collapsible: true,
										border: false,
										height: 160,
										items:
											[{
												layout: 'column',
												border: false,
												bodyStyle:'background:#DFE8F6;padding-right:0px;',
												items: [{
													layout: 'form',
													border: false,
													labelWidth: 200,
													bodyStyle:'background:#DFE8F6;padding-right:0px;',
													items: [{
														fieldLabel: langs('Показать за отчетный период'),
														hiddenName: 'ComputerEquip_Year',
														id: 'ComputerEquip_YearCombo',
														allowBlank: false,
														width: 135,
														xtype: 'combo',
														store:
															new sw.Promed.Store({
																autoLoad: false,
																url: '/?c=LpuPassport&m=loadLpuComputerEquipmentYearsUniq',
																fields: [
																	{name: 'ComputerEquip_Year', type: 'string'}],
																key: 'ComputerEquip_Year',
															}),
														valueField: 'ComputerEquip_Year',
														displayField: 'ComputerEquip_Year',
														triggerAction: 'all',
														editable: false,
														initComponent: function()
														{
															//чтобы пустое поле было широкое, а не узкое
															if(!this.tpl) {
																this.tpl = new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">{',  this.displayField , ':this.blank}</div></tpl>', {
																	blank: function(value){
																		return value==='' ? '&nbsp' : value;
																	}
																});
															}

														},
														loadYearComboStore: function(combo, lpu_id) {

															combo.getStore().baseParams.Lpu_id = lpu_id;
															combo.getStore().load(
																{
																	callback: function(store)
																	{
																		if (store.length > 0) {
																			combo.setValue(store.shift().data.ComputerEquip_Year);
																			_this.doSearchComputerEquipment();
																		} else {
																			combo.setValue(new Date().getFullYear());
																		}
																	}
																});
														},
														listeners:
														{
															'select': function(){

																_this.doSearchComputerEquipment();
															}
														}
													}]
												},{
													layout: 'form',
													border: false,
													labelWidth: 20,
													bodyStyle:'background:#DFE8F6;padding-right:0px;',
													items: [{
														width: 140,
														labelSeparator: '',
														editable: false,
														comboSubject: 'Period',
														hiddenName: 'Period_id',
														xtype: 'swcommonsprcombo',
														listeners:
														{
						 									'select': function(){

																_this.doSearchComputerEquipment();
															}
														}
													}]
												}]
											},{
												fieldLabel: langs('Категория'),
												hiddenName: 'Device_pid',
												width: 300,
												xtype: 'combo',
												resizable: true,
												store:
													new sw.Promed.Store({
														autoLoad: false,
														url: '/?c=LpuPassport&m=loadLpuComputerEquipmentDevicesCat',
														fields: [,
															{name: 'Device_id', type: 'int'},
															{name: 'Device_Name', type: 'string'},
															{name:'Device_Code', type:'string'},
														],
														key: 'DeviceCat_id',
													}),
												valueField: 'Device_id',
												displayField: 'Device_Name',
												triggerAction: 'all',
												editable: false,
												tpl:
												'<tpl for="."><div class="x-combo-list-item">'+
												'<font color="red">{Device_Code}</font>&nbsp;{Device_Name}'+
												'</div></tpl>',
												listeners: {
													'change': function(combo) {

														//combo.fireEvent('select', combo);
													},
													'select': function(combo) {

														_this.findById('LpuComputerEquipFilter').getForm().findField('Device_id').onChangeDeviceCatField(combo.getValue());
													}
												}
											},{
												fieldLabel: langs('Наименование устройства'),
												hiddenName: 'Device_id',
												id: 'ComputerEquip_Device_id',
												width: 300,
												xtype: 'combo',
												resizable: true,
												store:
													new sw.Promed.Store({
														autoLoad: false,
														url: '/?c=LpuPassport&m=loadLpuComputerEquipmentDevices',
														fields: [,
															{name: 'Device_id', type: 'int'},
															{name: 'Device_Name', type: 'string'},
															{name:'Device_Code', type:'string'},
														],
														key: 'Device_id',
													}),
												valueField: 'Device_id',
												displayField: 'Device_Name',
												triggerAction: 'all',
												allowBlank: false,
												editable: false,
												tpl:
												'<tpl for="."><div class="x-combo-list-item">'+
												'<font color="red">{Device_Code}</font>&nbsp;{Device_Name}'+
												'</div></tpl>',
												listeners: {
													'change': function (combo) {

														// если поле изменено
														//combo.fireEvent('select', combo);
													},
													'select': function(){

														_this.doSearchComputerEquipment();
													}
												},
												onChangeDeviceCatField: function(parent_selected_value) {

													var	child_combo = this;
													child_combo.allowBlank = (parent_selected_value) ? false : true;
													child_combo.getStore().removeAll();

													if (parent_selected_value) {

														child_combo.getStore().baseParams.parent_id = parent_selected_value || null;
														child_combo.getStore().load({

															callback: function () {

																if (child_combo.getStore().getById(parent_selected_value))
																	child_combo.setValue(parent_selected_value);
																else
																	child_combo.clearValue();

																child_combo.fireEvent('select', child_combo);
															}
														});
													} else {

														child_combo.clearValue();
														child_combo.fireEvent('select', child_combo);
													}
												}
											},{
												autoLoad: false,
												width: 300,
												xtype:  'SwComputerEquipUsageCombo',
												triggerAction: 'all',
												hiddenName: 'ComputerEquip_UsageColumn',
												listeners:
												{
													'select': function(){

														_this.doSearchComputerEquipment();
													}
												}
											},
												{
													layout: 'column',
													border: false,
													style: 'margin-left: 325px',
													bodyStyle: 'background:#DFE8F6;padding-right:0px;',
													items: [{
														layout: 'form',
														border: false,
														bodyStyle: 'background:#DFE8F6;padding-right:0px;',
														items: [{
															style: 'margin-left: 20px',
															handler: function () {

																_this.doSearchComputerEquipment();
															}.createDelegate(this),
															xtype: 'button',
															iconCls: 'search16',
															id: 'ComputerEquip__SearchButton',
															text: BTN_FRMSEARCH,
															listeners:
															{
																'click': function(){
																	_this.doSearchComputerEquipment();
																}
															}
														}],
													}, {
														layout: 'form',
														border: false,
														bodyStyle: 'background:#DFE8F6;padding-right:0px;',
														items: [{
															style: 'margin-left: 20px',
															handler: function () {

																this.doSearchComputerEquipment(true);

															}.createDelegate(this),
															xtype: 'button',
															iconCls: 'resetsearch16',
															id: 'ComputerEquip__ResetButton',
															text: BTN_FRMRESET
														}]
													}]
												}
											],
										listeners:
										{
											'collapse': function(){

												//if (isDebug())
												//    console.log('collapsed');
                                                //
												//_this.ComputerEquipmentGrid.anchor = '100% 100%';
                                                //
												//var tab = _this.findById('tab_computer_equipment');
												//tab.doLayout();
											},
											'expand': function(){

												//if (isDebug())
												//	console.log('collapsed');
                                                //
												//_this.ComputerEquipmentGrid.anchor = '100% -161';
                                                //
												//var tab = _this.findById('tab_computer_equipment');
												//tab.doLayout();
											},
										}
							}),
								//new sw.Promed.Panel({
								//	layout: 'anchor',
								//	anchor: '100% 100%',
								//	items: [
										this.ComputerEquipmentGrid = new sw.Promed.ViewFrame({

											id: 'LPEW_ComputerEquipmentGrid',
											title: langs('Оснащенность компьютерным оборудованием'),
											editformclassname: 'swLpuComputerEquipmentEditWindow',
											autoExpandColumn: 'autoexpand',
											//layout: 'fit',
											anchor: '100% -161',
											paging: false,

											actions: [
												{name: 'action_add'},
												{name: 'action_edit'},
												{name: 'action_view'},
												{name: 'action_delete',

													handler: function() {

														this.deleteComputerEquip();

													}.createDelegate(this)
												},
												{name: 'action_refresh',

													handler: function() {

														this.refreshComputerEquip();

													}.createDelegate(this)
												},
												{
													name: 'action_print',
													handler: function() {
														var filter_form = _this.findById('LpuComputerEquipFilter').getForm(),
															salt = Math.random(),
															wnd_id = 'print_act' + Math.floor(salt * 10000),
															lpu_id = _this.Lpu_id,
															computer_equip_year = filter_form.findField('ComputerEquip_Year').getValue(),
															print_win = window.open('/?c=LpuPassport&m=printLpuComputerEquipment&Lpu_id='+ lpu_id + '&ComputerEquip_Year=' + computer_equip_year, wnd_id);
													}
												}
											],

											object: 'ComputerEquip',
											autoLoadData: false,
											scheme: 'passport',
											dataUrl: '/?c=LpuPassport&m=loadLpuComputerEquipment',

											stringfields: [
												{name: 'ComputerEquip_id', type: 'int', header: 'ID', hidden: true},
												{name: 'Device_pid', type: 'int', header: 'device_pid', hidden: true},
												{name: 'Device_Cat', id:'DeviceCatGroup', type: 'string', hidden: true, group: true, sort: true, direction: [
													{field: 'Device_Cat', direction:'ASC'},
												]},
												{
													name: 'Device_Name',
													id: 'Device_Name',
													header: langs('Наименование устройства'),
													width: 300,
													type: 'string',
													align: 'left'
												},
												{
													name: 'ComputerEquip_AHDAmb',
													header: langs('амбулаторно'),
													width    : 150,
													type: 'int',
													align: 'center',
												},
												{
													name: 'ComputerEquip_AHDStac',
													header: langs('в стационарах'),
													width    : 150,
													type: 'int',
													align: 'center',
												},
												{
													name: 'ComputerEquip_MedPAmb',
													header: langs('амбулаторно'),
													width    : 150,
													type: 'int',
													align: 'center',
												},
												{
													name: 'ComputerEquip_MedPStac',
													header: langs('в стационарах'),
													width    : 150,
													type: 'int',
													align: 'center',
												},
												{
													name: 'ComputerEquip_other',
													header: langs('Прочие'),
													width    : 150,
													type: 'int',
													align: 'center'
												},
												{
													name: 'ComputerEquip_Total',
													header: langs('Всего'),
													width    : 150,
													type: 'int',
													align: 'center',
												},
												{
													name: 'ComputerEquip_MedStatCab',
													header: langs('Кабинеты медицинской статистики'),
													width    : 200,
													type: 'int',
													align: 'center',
												},
												{
													name: 'ComputerEquip_Year',
													header: langs('Год'),
													width    : 70,
													type: 'string',
													sortable: false,
													align: 'center',
												},
												{
													name: 'Period_Name',
													header: langs('Период'),
													width    : 150,
													align: 'center',
												}
											],
											// для объединения заголовков полей грида (для ext v2)
											gridplugins: [new Ext.ux.plugins.GroupHeaderGrid({
												rows: [
													[
														{},
														{},
														{},
														{},
														{header: langs('Для нужд АХД'), colspan:2, width: 300, align: 'center', id: 'AhdColspan'},
														{header: langs('Для медицинского персонала'), colspan:2, width: 300, align: 'center', id: 'MPColspan'},
														{},
														{},
														{},
														{},
														{}
													]
												],
												hierarchicalColMenu: false
											})],
											// для объединения заголовков полей грида
											headergrouping: true,
											grouping: true,
											groupingView: {
												showGroupName: false,
												showGroupsText: false
											},
											groupSortInfo: {
												field: 'Device_Cat',
												direction:'ASC'
											},
											toolbar: true
										})
								//	],
								//})
							],
						listeners:
						{
							activate: function() {

								var grid = _this.findById('LPEW_ComputerEquipmentGrid'),
									yearCombo = _this.findById('ComputerEquip_YearCombo'),
									lpu_id = _this.Lpu_id;

								_this.loadGridData(grid);
								yearCombo.loadYearComboStore(yearCombo, lpu_id);

							}.createDelegate(this),

							show: function(){

								var grid = _this.findById('LPEW_ComputerEquipmentGrid'),
									cModel = grid.getColumnModel(),
									columns = cModel.config,
									// если нужно меню выпадающее на названии столбца
									colsMenuEnable = [

										'Device_Name'
									];

								columns.forEach(function (col){

									//if (!col.id.inlist(colsMenuEnable))
										col.menuDisabled = true;
								})
							}
						}
					},
					{
						title: '13. ' + langs('Домовые хозяйства'),
						layout: 'fit',
						id: 'tab_household',
						iconCls: 'info16',
						border: false,
						items: [
							new sw.Promed.ViewFrame({
								actions: [
									{name: 'action_add'},
									{name: 'action_edit'},
									{name: 'action_view'},
									{name: 'action_delete'},
									{name: 'action_refresh'},
									{name: 'action_print'}
								],
								object: 'LpuHousehold',
								scheme: 'fed',
								editformclassname: 'swLpuHouseholdEditWindow',
								autoExpandColumn: 'autoexpand',
								autoExpandMin: 150,
								autoLoadData: false,
								border: false,
								dataUrl: '/?c=LpuPassport&m=loadLpuHouseholdGrid',
								focusOn: {
									name: 'LPEW_CancelButton',
									type: 'button'
								},
								focusPrev: {
									name: 'LPEW_CancelButton',
									type: 'button'
								},
								id: 'LPEW_HouseholdGrid',
								paging: false,
								title: langs('Домовые хозяйства'),
								region: 'center',
								stringfields: [
									{name: 'LpuHousehold_id', type: 'int', header: 'ID', hidden: true},
									{name: 'LPEW_Lpu_id', type: 'int', hidden: true},
									{
										name: 'LpuHousehold_Name',
										type: 'string',
										header: langs('Наименование'),
										width: 270
									},{
										name: 'LpuHousehold_ContactPerson',
										type: 'string',
										header: langs('Контактное лицо'),
										width: 270
									},{
										name: 'LpuHousehold_ContactPhone',
										type: 'string',
										header: langs('Контактный телефон'),
										width: 270
									},
									{
										name: 'LpuHousehold_CadNumber',
										type: 'string',
										header: langs('Кадастровый номер'),
										width: 270
									},
									{
										name: 'LpuHousehold_CoordLat',
										type: 'string',
										header: langs('Координаты (широта)'),
										width: 270
									},{
										name: 'LpuHousehold_CoordLon',
										type: 'string',
										header: langs('Координаты (долгота)'),
										width: 270
									},{
										name: 'LpuHousehold_Address',
										type: 'string',
										header: langs('Адрес хозяйства'),
										width: 270
									}
								],
								toolbar: true,
							})
						],
						listeners: {
							activate: function () {
								Ext.getCmp('LPEW_HouseholdGrid').loadData({
									globalFilters: {LPEW_Lpu_id: this.Lpu_id},
									params: {LPEW_Lpu_id: this.Lpu_id}
								});
							}.createDelegate(this)
						}
					}
				]
			})]
		});

		this.FSSContractGrid.ViewToolbar.on('render', function(vt){return this.addCloseFilterMenu(this.FSSContractGrid, 'all');}.createDelegate(this));


		sw.Promed.swLpuPassportEditWindow.superclass.initComponent.apply(this, arguments);
		//this.findById('LPEW_OrgRSchetGrid').addListenersFocusOnFields();
		//this.findById('LPEW_OrgHeadGrid').addListenersFocusOnFields();
	},
	keys: [{
		alt: true, 
		fn: function(inp, e) {
			Ext.getCmp('LpuPassportEditWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	loadGridData: function(grd) {

		var grid = grd;

		if (!Ext.isEmpty(this.Lpu_id)) {

				grid.loadData({

					globalFilters: {
						Lpu_id: this.Lpu_id
					},
					params: {
						Lpu_id: this.Lpu_id
					},
					callback: function (data) {

						if (!data || data && data.length == 0) {

							grid.getGrid().getStore().removeAll();
						}
					}
				});
		}

	},
 	enableEdit: function(enable)
	{
		var form = this;

        if (isSuperAdmin()) {
            form.findById('LPEW_Lpu_IsSecret').setDisabled(!enable);
        } else {
            form.findById('LPEW_Lpu_IsSecret').setDisabled(true);
        }
		
		if (enable) 
		{
			var isPerm = ( getRegionNick() == 'perm' );
			var isPskov = ( getRegionNick() == 'pskov' );
			var isAstra = ( getRegionNick() == 'astra' );
			var isUfa = ( getRegionNick() == 'ufa' );
			var ufaSAdm = (isUfa && !getGlobalOptions().superadmin);
			var isKareliya = ( getRegionNick() == 'kareliya' );
			if ( isSuperAdmin() || isUserGroup('LpuAdmin') || ((isUserGroup('OrgAdmin')) && isUfa ) || isUserGroup('OuzSpecMPC') || isUserGroup('OuzSpec')) {
				// https://redmine.swan.perm.ru/issues/66332
				if ( isPerm == false || isSuperAdmin() == true ) {
					form.findById('LPEW_Lpu_Name').enable();
					form.findById('LPEW_Lpu_Nick').enable();
					form.findById('LPEW_Lpu_Ouz').enable();
					form.findById('LPEW_Lpu_f003mcod').enable();
					form.findById('LPEW_Lpu_begDate').enable();
					form.findById('LPEW_Lpu_endDate').enable();
					form.findById('LPEW_Lpu_pid').enable();
					form.findById('LPEW_Lpu_nid').enable();
					form.findById('LPEW_Lpu_Okato').enable();
					form.findById('LPEW_Okfs_id').enable();
					form.findById('LPEW_Okopf_id').enable();
					form.findById('LPEW_Org_INN').enable();
					form.findById('LPEW_Org_KPP').enable();
					form.findById('LPEW_Org_OGRN').enable();
					form.findById('LPEW_LpuType_Name').enable();
					form.findById('LPEW_PAddress_AddressText').enable();
					form.findById('LPEW_UAddress_AddressText').enable();
					form.findById('LPEW_LpuSubjectionLevel_id').enable();

					form.findById('LPEW_OMSGrid').setReadOnly(false);
					form.findById('LPEW_OMSPeriodGrid').setReadOnly(false);
					form.findById('LPEW_DLOGrid').setReadOnly(false);
					form.findById('LPEW_DMSGrid').setReadOnly(false);
					form.findById('LPEW_FondHolderGrid').setReadOnly(false);
				}
				else {
					form.findById('LPEW_OMSGrid').setReadOnly(true);
					form.findById('LPEW_OMSPeriodGrid').setReadOnly(true);
					form.findById('LPEW_DLOGrid').setReadOnly(true);
					form.findById('LPEW_DMSGrid').setReadOnly(true);
					form.findById('LPEW_FondHolderGrid').setReadOnly(true);
				}

				form.findById('LPEW_Lpu_RegNomN2').enable();

				// form.findById('LPEW_LpuType_id').enable();
				form.findById('LPEW_Oktmo_Name').enable();
				form.findById('LPEW_LpuPmuType_Name').enable();
				form.findById('LPEW_LpuPmuClass_Name').enable();
				form.findById('LPEW_LpuAgeType_id').enable();
				form.findById('LPEW_DepartAffilType_id').enable();

				form.findById('LPEW_LpuLevel_cid').enable();
				form.findById('LPEW_LevelType_id').enable();
			} 
			else 
			{
				form.findById('LPEW_OMSGrid').setReadOnly(true);
				form.findById('LPEW_OMSPeriodGrid').setReadOnly(true);
				form.findById('LPEW_DLOGrid').setReadOnly(true);
				form.findById('LPEW_DMSGrid').setReadOnly(true);
				form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			}
			
			if ( isLpuAdmin(form.findById('LPEW_Lpu_id').getValue()) )
			{
				// https://redmine.swan.perm.ru/issues/67110
				if ( isPerm != true ) {
					form.findById('LPEW_Org_KPP').enable();
				}
			}

			// https://redmine.swan.perm.ru/issues/36345
			// https://redmine.swan.perm.ru/issues/68746
			if ( getRegionNick().inlist([ 'krym', 'perm', 'pskov', 'ufa' ]) && (isSuperAdmin() || isLpuAdmin(form.Lpu_id)) ) {
				form.findById('LPEW_LpuLevel_id').enable();
				form.findById('LPEW_FedLpuLevel_id').enable();
			}
			
			form.findById('LPEW_Lpu_StickNick').enable();

			form.findById('LPEW_Lpu_Email').enable();
			form.findById('LPEW_Lpu_Www').enable();
			form.findById('LPEW_Lpu_Phone').enable();
			form.findById('LPEW_Lpu_Worktime').enable();
			form.findById('LPEW_Lpu_StickAddress').setDisabled(ufaSAdm);
			form.findById('LPEW_Lpu_DistrictRate').enable();

			form.findById('LPEW_Org_OKPO').enable();
			form.findById('LPEW_Org_OKDP').enable();
			form.findById('LPEW_Okogu_id').enable();
			form.findById('LPEW_Okved_id').enable();	
			
			form.findById('LPEW_Org_lid').enable();
			form.findById('LPEW_Lpu_RegDate').enable();
			form.findById('LPEW_Lpu_RegNum').enable();
			form.findById('LPEW_Lpu_DocReg').enable();
			form.findById('LPEW_Lpu_PensRegNum').enable();
			form.findById('LPEW_Lpu_FSSRegNum').enable();

			form.findById('LPEW_Lpu_VizitFact').enable();
			form.findById('LPEW_Lpu_KoikiFact').enable();
			form.findById('LPEW_Lpu_AmbulanceCount').enable();
			form.findById('LPEW_Lpu_FondOsn').enable();
			form.findById('LPEW_Lpu_FondEquip').enable();
			
			
			form.findById('LPEW_Lpu_OftenCallers_FreeDays').enable();
			form.findById('LPEW_PasportMO_IsAssignNasel').enable();
			form.findById('LPEW_Lpu_OftenCallers_SearchDays').enable();
			form.findById('LPEW_Lpu_OftenCallers_CallTimes').enable();
			
//			form.findById('LPEW_Lpu_HasLocalPacsServer').enable();
//			form.findById('LPEW_Lpu_LocalPacsServerIP').enable();
//			form.findById('LPEW_Lpu_LocalPacsServerAetitle').enable();
//			form.findById('LPEW_Lpu_LocalPacsServerPort').enable();
//			form.findById('LPEW_Lpu_LocalPacsServerWadoPort').enable();			

			form.findById('LPEW_Lpu_ErInfo').enable();
			if(isSuperAdmin())
				form.findById('LPEW_IsAllowInternetModeration').enable();
			else
				form.findById('LPEW_IsAllowInternetModeration').disable();

			form.findById('LPEW_Lpu_MedCare').enable();
						
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(false);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(false);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(false);
			form.findById('LPEW_DogDDGrid').setReadOnly(false);
			form.findById('LPEW_LpuBuilding').setReadOnly(false);
			
			form.findById('LPEW_MedProductCardGrid').setReadOnly(false);form.findById('LPEW_MedProductCardGrid').setReadOnly(false);
			//form.findById('LPEW_TransportGrid').setReadOnly(false);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(false);
			
			form.findById('LPEW_MOInfoSysGrid').setReadOnly(false);
			form.findById('LPEW_SpecializationMOGrid').setReadOnly(false);
			form.findById('LPEW_MedUslugaGrid').setReadOnly(false);
			form.findById('LPEW_MedTechnologyGrid').setReadOnly(false);
			form.findById('LPEW_LpuPeriodStomGrid').setReadOnly(false);
			form.findById('LPEW_PitanFormTypeLinkGrid').setReadOnly(false);
			form.findById('LPEW_PlfDocTypeLinkGrid').setReadOnly(false);
			form.findById('LPEW_PlfObjectCountGrid').setReadOnly(false);
			form.findById('LPEW_LpuMobileTeamGrid').setReadOnly(false);
			form.findById('LPEW_MOAreaGrid').setReadOnly(false);
			form.findById('LPEW_MOAreaObjectGrid').setReadOnly(false);
			form.findById('LPEW_EquipmentGrid').setReadOnly(false);
			form.findById('LPEW_OrgServiceTerrGrid').setReadOnly(false);
			form.findById('LPEW_KurortStatusGrid').setReadOnly(false);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(false);
			form.findById('LPEW_MOArrivalGrid').setReadOnly(false);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(false);
			
			this.buttons[0].enable();

			if (getRegionNick() == 'ufa'){
				['LPEW_Lpu_Name', 'LPEW_Lpu_Nick', 'LPEW_Lpu_begDate', 'LPEW_Lpu_endDate', 'LPEW_Lpu_pid', 'LPEW_Lpu_nid', 'LPEW_Org_OGRN'].forEach(function(rec){
					form.findById(rec).setDisabled(!isSuperAdmin());
				});
			}
		}
		else
		{
			form.findById('LPEW_Lpu_Name').disable();
			form.findById('LPEW_Lpu_Nick').disable();
			form.findById('LPEW_Lpu_Ouz').disable();
			form.findById('LPEW_Lpu_f003mcod').disable();
			form.findById('LPEW_Lpu_RegNomN2').disable();
			
			form.findById('LPEW_Lpu_StickNick').disable();
			
			form.findById('LPEW_Lpu_begDate').disable();
			form.findById('LPEW_Lpu_endDate').disable();
			form.findById('LPEW_Lpu_pid').disable();
			form.findById('LPEW_Lpu_nid').disable();
			
			form.findById('LPEW_Lpu_Email').disable();
			form.findById('LPEW_Lpu_Www').disable();
			form.findById('LPEW_Lpu_Phone').disable();
			form.findById('LPEW_Lpu_Worktime').disable();
			form.findById('LPEW_Lpu_StickAddress').disable();
			form.findById('LPEW_Lpu_Okato').disable();
			// form.findById('LPEW_LpuType_id').disable();
			form.findById('LPEW_Oktmo_Name').disable();
			form.findById('LPEW_LpuPmuType_Name').disable();
			form.findById('LPEW_LpuPmuClass_Name').disable();
			form.findById('LPEW_LpuType_Name').disable();
			form.findById('LPEW_LpuAgeType_id').disable();
			form.findById('LPEW_DepartAffilType_id').disable();
			form.findById('LPEW_Lpu_DistrictRate').disable();
			
			form.findById('LPEW_PAddress_AddressText').disable();
			form.findById('LPEW_UAddress_AddressText').disable();
			
			form.findById('LPEW_Okfs_id').disable();
			form.findById('LPEW_Okopf_id').disable();
			form.findById('LPEW_Org_OKPO').disable();
			form.findById('LPEW_Org_INN').disable();
			form.findById('LPEW_Org_KPP').disable();
			form.findById('LPEW_Org_OGRN').disable();
			form.findById('LPEW_Org_OKDP').disable();
			form.findById('LPEW_Okogu_id').disable();
			form.findById('LPEW_Okved_id').disable();	
			
			form.findById('LPEW_Org_lid').disable();
			form.findById('LPEW_Lpu_RegDate').disable();
			form.findById('LPEW_Lpu_RegNum').disable();
			form.findById('LPEW_Lpu_DocReg').disable();
			form.findById('LPEW_Lpu_PensRegNum').disable();
			form.findById('LPEW_Lpu_FSSRegNum').disable();

			form.findById('LPEW_LpuSubjectionLevel_id').disable();
			form.findById('LPEW_LpuLevel_id').disable();
			form.findById('LPEW_FedLpuLevel_id').disable();
			form.findById('LPEW_LpuLevel_cid').disable();
			form.findById('LPEW_LevelType_id').disable();
			form.findById('LPEW_Lpu_VizitFact').disable();
			form.findById('LPEW_Lpu_KoikiFact').disable();
			form.findById('LPEW_Lpu_AmbulanceCount').disable();
			form.findById('LPEW_Lpu_FondOsn').disable();
			form.findById('LPEW_Lpu_FondEquip').disable();
			
			form.findById('LPEW_Lpu_OftenCallers_FreeDays').disable();
			form.findById('LPEW_PasportMO_IsAssignNasel').disable();
			form.findById('LPEW_Lpu_OftenCallers_SearchDays').disable();
			form.findById('LPEW_Lpu_OftenCallers_CallTimes').disable();
			
//			form.findById('LPEW_Lpu_HasLocalPacsServer').disable();
//			form.findById('LPEW_Lpu_LocalPacsServerIP').disable();
//			form.findById('LPEW_Lpu_LocalPacsServerAetitle').disable();
//			form.findById('LPEW_Lpu_LocalPacsServerPort').disable();
//			form.findById('LPEW_Lpu_LocalPacsServerWadoPort').disable();
			
			form.findById('LPEW_Lpu_ErInfo').disable();
			form.findById('LPEW_IsAllowInternetModeration').disable();
			form.findById('LPEW_Lpu_MedCare').disable();
			
			form.findById('LPEW_OMSGrid').setReadOnly(true);
			form.findById('LPEW_OMSPeriodGrid').setReadOnly(true);
			form.findById('LPEW_DLOGrid').setReadOnly(true);
			form.findById('LPEW_DMSGrid').setReadOnly(true);
			form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(true);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(true);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(true);
			form.findById('LPEW_DogDDGrid').setReadOnly(true);
			form.findById('LPEW_LpuBuilding').setReadOnly(true);
			form.findById('LPEW_MedProductCardGrid').setReadOnly(true);
			//form.findById('LPEW_TransportGrid').setReadOnly(true);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(true);
			
			form.findById('LPEW_MOInfoSysGrid').setReadOnly(true);
			form.findById('LPEW_SpecializationMOGrid').setReadOnly(true);
			form.findById('LPEW_MedUslugaGrid').setReadOnly(true);
            form.findById('LPEW_UslugaComplexLpuGrid').setReadOnly(true);
			form.findById('LPEW_MedTechnologyGrid').setReadOnly(true);
			form.findById('LPEW_LpuPeriodStomGrid').setReadOnly(true);
			form.findById('LPEW_PitanFormTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfDocTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfObjectCountGrid').setReadOnly(true);
			form.findById('LPEW_LpuMobileTeamGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaObjectGrid').setReadOnly(true);
			form.findById('LPEW_EquipmentGrid').setReadOnly(true);
			form.findById('LPEW_OrgServiceTerrGrid').setReadOnly(true);
			form.findById('LPEW_KurortStatusGrid').setReadOnly(true);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(true);
			form.findById('LPEW_MOArrivalGrid').setReadOnly(true);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(true);
			form.findById('LPEW_FunctionTimeGrid').setReadOnly(true);
			form.findById('LPEW_CmpSubstationGrid').setReadOnly(true);
            form.findById('LPEW_KurortTypeLinkGrid').setReadOnly(true);
            form.findById('LPEW_TransportConnectGrid').setReadOnly(true);
			this.buttons[0].disable();
		}
		
		if (isUserGroup('OuzSpec') || isUserGroup('OuzSpecMPC')) {
			form.findById('LPEW_OMSGrid').setReadOnly(true);
			form.findById('LPEW_OMSPeriodGrid').setReadOnly(true);
			form.findById('LPEW_DLOGrid').setReadOnly(true);
			form.findById('LPEW_DMSGrid').setReadOnly(true);
			form.findById('LPEW_FondHolderGrid').setReadOnly(true);
			
			form.findById('LPEW_LpuLicenceGrid').setReadOnly(true);
			form.findById('LPEW_OrgRSchetGrid').setReadOnly(true);
			
			form.findById('LPEW_OrgHeadGrid').setReadOnly(true);
			form.findById('LPEW_DogDDGrid').setReadOnly(true);
			form.findById('LPEW_LpuBuilding').setReadOnly(true);
			form.findById('LPEW_LpuQuoteGrid').setReadOnly(true);
			
			form.findById('LPEW_MedProductCardGrid').setReadOnly(true);
			form.findById('LPEW_MOInfoSysGrid').setReadOnly(true);
			form.findById('LPEW_SpecializationMOGrid').setReadOnly(true);
			form.findById('LPEW_MedUslugaGrid').setReadOnly(true);
			form.findById('LPEW_MedTechnologyGrid').setReadOnly(true);
			form.findById('LPEW_LpuPeriodStomGrid').setReadOnly(true);
			form.findById('LPEW_PitanFormTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfDocTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_PlfObjectCountGrid').setReadOnly(true);
			form.findById('LPEW_LpuMobileTeamGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaGrid').setReadOnly(true);
			form.findById('LPEW_MOAreaObjectGrid').setReadOnly(true);
			form.findById('LPEW_EquipmentGrid').setReadOnly(true);
			form.findById('LPEW_OrgServiceTerrGrid').setReadOnly(true);
			form.findById('LPEW_KurortStatusGrid').setReadOnly(true);
			form.findById('LPEW_KurortTypeLinkGrid').setReadOnly(true);
			form.findById('LPEW_MOArrivalGrid').setReadOnly(true);
			form.findById('LPEW_DisSanProtectionGrid').setReadOnly(true);

			form.findById('LPEW_UslugaComplexLpuGrid').setReadOnly(true);
			form.findById('LPEW_FunctionTimeGrid').setReadOnly(true);
			form.findById('LPEW_CmpSubstationGrid').setReadOnly(true);
			form.findById('LPEW_TransportConnectGrid').setReadOnly(true);

			this.buttons[0].disable();	
		}
	},
	viewFrameList: [
		'LPEW_OMSGrid',
		'LPEW_OMSPeriodGrid',
		'LPEW_DLOGrid',
		'LPEW_DMSGrid',
		'LPEW_FondHolderGrid',
		'LPEW_LpuLicenceGrid',
		'LPEW_OrgRSchetGrid',
		'LPEW_MOInfoSysGrid',
		'LPEW_SpecializationMOGrid',
		'LPEW_MedUslugaGrid',
		'LPEW_MedTechnologyGrid',
		'LPEW_UslugaComplexLpuGrid',
		'LPEW_LpuPeriodStomGrid',
		'LPEW_PitanFormTypeLinkGrid',
		'LPEW_PlfDocTypeLinkGrid',
		'LPEW_PlfObjectCountGrid',
		'LPEW_LpuMobileTeamGrid',
		'LPEW_FunctionTimeGrid',
		'LPEW_CmpSubstationGrid',
		'LPEW_OrgHeadGrid',
		'LPEW_DogDDGrid',
		'LPEW_MOAreaGrid',
		'LPEW_TransportConnectGrid',
		'LPEW_LpuBuilding',
		'LPEW_MOAreaObjectGrid',
		'LPEW_MedProductCardGrid',
		'LPEW_EquipmentGrid',
		'LPEW_OrgServiceTerrGrid',
		'LPEW_LpuQuoteGrid',
		'LPEW_KurortStatusGrid',
		'LPEW_DisSanProtectionGrid',
		'LPEW_KurortTypeLinkGrid',
		'LPEW_MOArrivalGrid',
		'LPEW_ComputerEquipmentGrid',
		'LPEW_HouseholdGrid',
		'LPEW_FilialGrid'
	],
	
	resetForm:  function () {
	
			Ext.getCmp('LPEW_panelForm').getForm().reset();	
			Ext.getCmp('Lpu_IdentificationPanel').getForm().reset();	
			Ext.getCmp('Lpu_SupInfoPanel').getForm().reset();	
			
			var form  = Ext.getCmp('LpuPassportEditWindow');			
			
			form.findById('LPEW_OMSGrid').removeAll({clearAll: true});
			form.findById('LPEW_OMSPeriodGrid').removeAll({clearAll: true});
			form.findById('LPEW_DLOGrid').removeAll({clearAll: true});
			form.findById('LPEW_DMSGrid').removeAll({clearAll: true});
			form.findById('LPEW_FondHolderGrid').removeAll({clearAll: true});
			
			form.findById('LPEW_LpuLicenceGrid').removeAll({clearAll: true});
			form.findById('LPEW_OrgRSchetGrid').removeAll({clearAll: true});
			
			form.findById('LPEW_OrgHeadGrid').removeAll({clearAll: true});
			form.findById('LPEW_DogDDGrid').removeAll({clearAll: true});
			form.findById('LPEW_LpuBuilding').removeAll({clearAll: true});
			
			form.findById('LPEW_MedProductCardGrid').removeAll({clearAll: true});
			//form.findById('LPEW_TransportGrid').removeAll({clearAll: true});
			form.findById('LPEW_LpuQuoteGrid').removeAll({clearAll: true});
	},
	disableGrids: function () {		
		for ( var i in this.viewFrameList ) {
			if ( typeof this.viewFrameList[i] == 'string' && typeof this.findById(this.viewFrameList[i]) == 'object' ) {
				this.findById(this.viewFrameList[i]).setReadOnly(true);
			}
		}
	},
	getLpuInfo: function (Lpu_id)
	{
		var win = this;
		Ext.getCmp("tab_dogdd").hide().setDisabled(true);
		Ext.getCmp("tab_er").hide().setDisabled(true);
		Ext.getCmp("tab_zdanie").hide().setDisabled(true);
		if(!isUserGroup('MPCModer')){
			Ext.getCmp("tab_medprod").hide().setDisabled(true);
		}
		Ext.getCmp("tab_pacs_equipment").hide().setDisabled(true);
		Ext.getCmp("tab_population").hide().setDisabled(true);
		Ext.getCmp("tab_med_care").hide().setDisabled(true);
		Ext.getCmp("tab_sanatoriumtreatment").hide().setDisabled(true);
		Ext.Ajax.request(
		{
			params: 
			{
				Lpu_id: Lpu_id
			},
			url: '/?c=LpuPassport&m=getLpuPassport',
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					var form  = Ext.getCmp('LpuPassportEditWindow');
                    form.findById('LPEW_Lpu_IsSecret').setValue((result[0].Lpu_IsSecret == '2'));
					form.findById('LPEW_Lpu_id').setValue(result[0].Lpu_id);
					win.Org_id = result[0].Org_id;
					win.Lpu_IsMse = (result[0].Lpu_IsMse == 2);
					form.findById('LPEW_Org_id').setValue(result[0].Org_id);
					form.findById('LPEW_Server_id').setValue(result[0].Server_id);
					form.findById('LPEW_Lpu_Name').setValue(result[0].Lpu_Name);
					form.findById('LPEW_Lpu_Nick').setValue(result[0].Lpu_Nick);
					form.findById('LPEW_Lpu_Ouz').setValue(result[0].Lpu_Ouz);
					form.findById('LPEW_Lpu_f003mcod').setValue(result[0].Lpu_f003mcod);
					form.findById('LPEW_Lpu_RegNomN2').setValue(result[0].Lpu_RegNomN2);
					
					form.findById('LPEW_Lpu_StickNick').setValue(result[0].Lpu_StickNick);
					//form.findById('LPEW_Lpu_IsEmailFixed').setValue(result[0].Lpu_IsEmailFixed);
					
					form.findById('LPEW_Lpu_begDate').setValue(result[0].Lpu_begDate);
					form.findById('LPEW_Lpu_endDate').setValue(result[0].Lpu_endDate);
					form.findById('LPEW_Lpu_pid').setValue(result[0].Lpu_pid);
					//form.findById('LPEW_Lpu_nid').setValue(result[0].Lpu_nid);

					form.findById('LPEW_Lpu_Email').setValue(result[0].Lpu_Email);
					form.findById('LPEW_Lpu_Www').setValue(result[0].Lpu_Www);
					form.findById('LPEW_Lpu_Phone').setValue(result[0].Lpu_Phone);
					form.findById('LPEW_Lpu_Worktime').setValue(result[0].Lpu_Worktime);
					form.findById('LPEW_Lpu_StickAddress').setValue(result[0].Lpu_StickAddress);
					form.findById('LPEW_Lpu_Okato').setValue(result[0].Lpu_Okato);
					form.findById('LPEW_Oktmo_id').setValue(result[0].Oktmo_id);
					form.findById('LPEW_Oktmo_Name').setValue(result[0].Oktmo_Name);
					form.findById('LPEW_LpuPmuType_id').setValue(result[0].LpuPmuType_id);
					form.findById('LPEW_LpuPmuType_Name').setValue(result[0].LpuPmuType_Name);
					form.findById('LPEW_LpuPmuClass_id').setValue(result[0].LpuPmuClass_id);
					form.findById('LPEW_LpuPmuClass_Name').setValue(result[0].LpuPmuClass_Name);
					form.findById('LPEW_LpuType_id').setValue(result[0].LpuType_id);
					form.findById('LPEW_LpuType_Name').setValue(result[0].LpuType_Name);
					form.findById('LPEW_LpuAgeType_id').setValue(result[0].MesAgeLpuType_id);
					form.findById('LPEW_DepartAffilType_id').setValue(result[0].DepartAffilType_id);
					form.findById('LPEW_Lpu_DistrictRate').setValue(result[0].Lpu_DistrictRate);
					form.findById('LPEW_PassportToken_tid').setValue(result[0].PassportToken_tid);
					form.findById('LPEW_Org_ONMSZCode').setValue(result[0].Org_ONMSZCode);

					if ( getRegionNick().inlist(['perm','msk']) ) {
						form.findById('LPEW_Lpu_InterCode').setValue(result[0].Lpu_InterCode);
						form.findById('LPEW_TOUZType_id').setValue(result[0].TOUZType_id);
						form.findById('LPEW_Org_tid').setValue(result[0].Org_tid);
					} else if ( getRegionNick().inlist([ 'astra', 'kareliya', 'krym', 'penza', 'pskov', 'buryatiya', 'vologda','khak', 'yakutiya' ]) ) {
					    form.findById('LPEW_PasportMO_IsAssignNasel').setValue(result[0].PasportMO_IsAssignNasel);
                    }

					if ( !Ext.isEmpty(form.findById('LPEW_Oktmo_id').getValue()) && form.findById('LPEW_Oktmo_Name').useNameWithPath == true ) {
						// Тянем полное наименование ОКТМО
						form.findById('LPEW_Oktmo_Name').setNameWithPath();
					}
					
					if ( !Ext.isEmpty(form.findById('LPEW_LpuPmuType_id').getValue()) && form.findById('LPEW_LpuPmuType_Name').useNameWithPath == true ) {
						// Тянем полное наименование типа МО для ПМУ
						form.findById('LPEW_LpuPmuType_Name').setNameWithPath();
					}

					if ( !Ext.isEmpty(form.findById('LPEW_LpuPmuClass_id').getValue()) && form.findById('LPEW_LpuPmuClass_Name').useNameWithPath == true ) {
						// Тянем полное наименование типа МО для ПМУ
						form.findById('LPEW_LpuPmuClass_Name').setNameWithPath();
					}

					if ( !Ext.isEmpty(form.findById('LPEW_LpuType_id').getValue()) && form.findById('LPEW_LpuType_Name').useNameWithPath == true ) {
						// Тянем полное наименование типа МО
						form.findById('LPEW_LpuType_Name').setNameWithPath();
					}
					
					form.findById('LPEW_Okfs_id').setValue(result[0].Okfs_id);
					form.findById('LPEW_Okopf_id').setValue(result[0].Okopf_id);
					form.findById('LPEW_Org_OKPO').setValue(result[0].Org_OKPO);
					form.findById('LPEW_Org_INN').setValue(result[0].Org_INN);
					form.findById('LPEW_Org_KPP').setValue(result[0].Org_KPP);
					form.findById('LPEW_Org_OGRN').setValue(result[0].Org_OGRN);
					form.findById('LPEW_Org_OKDP').setValue(result[0].Org_OKDP);
					form.findById('LPEW_Okogu_id').setValue(result[0].Okogu_id);
					form.findById('LPEW_Okved_id').setValue(result[0].Okved_id);	
					
					form.findById('LPEW_Org_lid').setValue(result[0].Org_lid);

					var combo = form.findById('LPEW_Org_lid');
					if (!Ext.isEmpty(result[0].Org_lid)) {
						combo.setValue(result[0].Org_lid);
						combo.getStore().load(
						{
							callback: function() 
							{
								var form  = Ext.getCmp('LpuPassportEditWindow');
								var combo = form.findById('LPEW_Org_lid');
								combo.setValue(combo.getValue());
								combo.fireEvent('change', combo);
							},
							params: 
							{
								Org_id: result[0].Org_lid,
								OrgType: getRegionNick()=='kareliya'?'':'lic'
							}
						});
					}
					
					var combo = form.findById('LPEW_Org_tid');
					if (!Ext.isEmpty(combo.getValue())) {
						combo.getStore().load(
						{
							callback: function() 
							{
								combo.setValue(combo.getValue());
								combo.fireEvent('change', combo);
							},
							params: 
							{
								Org_id: combo.getValue(),
								OrgType: ''
							}
						});
					}

					form.findById('LPEW_LpuOwnership_id').setValue(result[0].LpuOwnership_id);
					form.findById('LPEW_MOAreaFeature_id').setValue(result[0].MOAreaFeature_id);
					form.findById('LPEW_Lpu_Founder').setValue(result[0].lpu_founder);

					var LPBcombo = form.findById('LPEW_LpuBuildingPass_mid');
					LPBcombo.getStore().load(
					{
						callback: function() 
						{
							if(result[0].LpuBuildingPass_mid) LPBcombo.setValue(result[0].LpuBuildingPass_mid);
						},
						params: { Lpu_id: result[0].Lpu_id }
					});

					form.findById('LPEW_Lpu_RegDate').setValue(result[0].Lpu_RegDate);
					form.findById('LPEW_Lpu_RegNum').setValue(result[0].Lpu_RegNum);
					form.findById('LPEW_Lpu_DocReg').setValue(result[0].Lpu_DocReg);
					form.findById('LPEW_Lpu_PensRegNum').setValue(result[0].Lpu_PensRegNum);
					form.findById('LPEW_Lpu_FSSRegNum').setValue(result[0].Lpu_FSSRegNum);

					form.findById('LPEW_UAddress_id').setValue(result[0].UAddress_id);
					form.findById('LPEW_UAddress_Zip').setValue(result[0].UAddress_Zip);
					form.findById('LPEW_UKLCountry_id').setValue(result[0].UKLCountry_id);
					form.findById('LPEW_UKLRGN_id').setValue(result[0].UKLRGN_id);
					form.findById('LPEW_UKLSubRGN_id').setValue(result[0].UKLSubRGN_id);
					form.findById('LPEW_UKLCity_id').setValue(result[0].UKLCity_id);
					form.findById('LPEW_UKLTown_id').setValue(result[0].UKLTown_id);
					form.findById('LPEW_UKLStreet_id').setValue(result[0].UKLStreet_id);
					form.findById('LPEW_UAddress_House').setValue(result[0].UAddress_House);
					form.findById('LPEW_UAddress_Corpus').setValue(result[0].UAddress_Corpus);
					form.findById('LPEW_UAddress_Flat').setValue(result[0].UAddress_Flat);
					form.findById('LPEW_UAddress_Address').setValue(result[0].UAddress_Address);
					form.findById('LPEW_UAddress_AddressText').setValue(result[0].UAddress_AddressText);
					form.findById('LPEW_PAddress_id').setValue(result[0].PAddress_id);
					form.findById('LPEW_PAddress_Zip').setValue(result[0].PAddress_Zip);
					form.findById('LPEW_PKLCountry_id').setValue(result[0].PKLCountry_id);
					form.findById('LPEW_PKLRGN_id').setValue(result[0].PKLRGN_id);
					form.findById('LPEW_PKLSubRGN_id').setValue(result[0].PKLSubRGN_id);
					form.findById('LPEW_PKLCity_id').setValue(result[0].PKLCity_id);
					form.findById('LPEW_PKLTown_id').setValue(result[0].PKLTown_id);
					form.findById('LPEW_PKLStreet_id').setValue(result[0].PKLStreet_id);
					form.findById('LPEW_PAddress_House').setValue(result[0].PAddress_House);
					form.findById('LPEW_PAddress_Corpus').setValue(result[0].PAddress_Corpus);
					form.findById('LPEW_PAddress_Flat').setValue(result[0].PAddress_Flat);
					form.findById('LPEW_PAddress_Address').setValue(result[0].PAddress_Address);
					form.findById('LPEW_PAddress_AddressText').setValue(result[0].PAddress_AddressText);

                    form.findById('LPEW_PasportMO_id').setValue(result[0].PasportMO_id);
                    form.findById('LPEW_PasportMO_MaxDistansePoint').setValue(result[0].PasportMO_MaxDistansePoint);
                    form.findById('LPEW_PasportMO_IsFenceTer').setValue(result[0].PasportMO_IsFenceTer);
                    form.findById('LPEW_PasportMO_IsNoFRMP').setValue(result[0].PasportMO_IsNoFRMP);
                    form.findById('LPEW_DLocationLpu_id').setValue(result[0].DLocationLpu_id);
                    form.findById('LPEW_PasportMO_IsSecur').setValue(result[0].PasportMO_IsSecur);
                    form.findById('LPEW_PasportMO_IsMetalDoors').setValue(result[0].PasportMO_IsMetalDoors);
                    form.findById('LPEW_PasportMO_IsVideo').setValue(result[0].PasportMO_IsVideo);
                    form.findById('LPEW_PasportMO_IsTerLimited').setValue(result[0].PasportMO_IsTerLimited);
                    form.findById('LPEW_PasportMO_IsAccompanying').setValue(result[0].PasportMO_IsAccompanying);
                    form.findById('LPEW_PasportMO_Popul').setValue(result[0].PasportMO_Popul);
                    form.findById('LPEW_PasportMO_CityPopul').setValue(result[0].PasportMO_CityPopul);
                    form.findById('LPEW_PasportMO_TownPopul').setValue(result[0].PasportMO_TownPopul);
                    form.findById('LPEW_PasportMO_calcDate').setRawValue(result[0].PasportMO_calcDate);

					form.findById('LPEW_LpuSubjectionLevel_id').setValue(result[0].LpuSubjectionLevel_id);
					form.findById('LPEW_LpuLevel_id').setValue(result[0].LpuLevel_id);
					form.findById('LPEW_FedLpuLevel_id').setValue(result[0].FedLpuLevel_id);
					form.findById('LPEW_LpuLevel_cid').setValue(result[0].LpuLevel_cid);
					form.findById('LPEW_LpuLevelType_id').setValue(result[0].LpuLevelType_id);
					form.findById('LPEW_LevelType_id').setValue(result[0].LevelType_id);
					form.findById('LPEW_Lpu_VizitFact').setValue(result[0].Lpu_VizitFact);
					form.findById('LPEW_Lpu_KoikiFact').setValue(result[0].Lpu_KoikiFact);
					form.findById('LPEW_Lpu_AmbulanceCount').setValue(result[0].Lpu_AmbulanceCount);
					form.findById('LPEW_InstitutionLevel_id').setValue(result[0].InstitutionLevel_id);
					form.findById('LPEW_Lpu_FondOsn').setValue(result[0].Lpu_FondOsn);
					form.findById('LPEW_Lpu_FondEquip').setValue(result[0].Lpu_FondEquip);
					form.findById('LPEW_Lpu_gid').setValue(result[0].Lpu_gid);

					if (result[0].isCMP == 1) {
						form.findById('LPEW_Lpu_OftenCallers_Panel').show();
					} else {
						form.findById('LPEW_Lpu_OftenCallers_Panel').hide();
					}
					
					form.findById('LPEW_Lpu_isCMP').setValue(result[0].isCMP);
					
					form.findById('LPEW_Lpu_OftenCallers_CallTimes').setValue(result[0].OftenCallers_CallTimes);
					form.findById('LPEW_Lpu_OftenCallers_SearchDays').setValue(result[0].OftenCallers_SearchDays);					
					form.findById('LPEW_Lpu_OftenCallers_FreeDays').setValue(result[0].OftenCallers_FreeDays);

					/*form.findById('LPEW_Lpu_CmpStationCategory_id').setValue(result[0].CmpStationCategory_id);*/
					form.findById('LPEW_PasportMO_KolServ').setValue(result[0].PasportMO_KolServ);
					form.findById('LPEW_PasportMO_KolServSel').setValue(result[0].PasportMO_KolServSel);
					form.findById('LPEW_PasportMO_KolServDet').setValue(result[0].PasportMO_KolServDet);
					form.findById('LPEW_PasportMO_KolCmpMes').setValue(result[0].PasportMO_KolCmpMes);
					form.findById('LPEW_PasportMO_KolCmpPay').setValue(result[0].PasportMO_KolCmpPay);
					form.findById('LPEW_PasportMO_KolCmpWage').setValue(result[0].PasportMO_KolCmpWage);

//					if (result[0].Lpu_HasLocalPacsServer == '2') {
//						form.findById('LPEW_Lpu_LocalPacsServerIP').setValue(result[0].Lpu_LocalPacsServerIP);
//						form.findById('LPEW_Lpu_LocalPacsServerAetitle').setValue(result[0].Lpu_LocalPacsServerAetitle);
//						form.findById('LPEW_Lpu_LocalPacsServerPort').setValue(result[0].Lpu_LocalPacsServerPort);
//						form.findById('LPEW_Lpu_LocalPacsServerWadoPort').setValue(result[0].Lpu_LocalPacsServerWadoPort);					
//					}
//					form.findById('LPEW_Lpu_HasLocalPacsServer').setValue((result[0].Lpu_HasLocalPacsServer == '2'));
//					var checkbox = form.findById('LPEW_Lpu_HasLocalPacsServer');
//					form.findById('LPEW_Lpu_HasLocalPacsServer').fireEvent('check',checkbox,(result[0].Lpu_HasLocalPacsServer == '2'));
					

					form.findById('LPEW_Lpu_ErInfo').setValue(result[0].Lpu_ErInfo);

					if (result[0].Lpu_IsAllowInternetModeration == '2')
						form.findById('LPEW_IsAllowInternetModeration').setValue(1);
					else
						form.findById('LPEW_IsAllowInternetModeration').setValue(0);

					form.findById('LPEW_Lpu_MedCare').setValue(result[0].Lpu_MedCare);
					//form.findById('LPEW_Lpu_Email').setDisabled((form.findById('LPEW_Lpu_IsEmailFixed').getValue()==2));
					form.findById('LPEW_Lpu_IsLab').setValue((result[0].Lpu_IsLab == "2"));

					form.findById('LPEW_Test_MO').setValue((result[0].Lpu_IsTest == "2"));
					form.findById('LPEW_Test_MO').setContainerVisible(isSuperAdmin());
					
					if(result[0].Lpu_IsLab != "2"){
						Ext.getCmp("tab_dogdd").hide().setDisabled(false);
						Ext.getCmp("tab_er").hide().setDisabled(false);
						Ext.getCmp("tab_zdanie").hide().setDisabled(false);
						if(!isUserGroup('MPCModer')){
							Ext.getCmp("tab_medprod").hide().setDisabled(false);
						}
						Ext.getCmp("tab_pacs_equipment").hide().setDisabled(false);
						Ext.getCmp("tab_population").hide().setDisabled(false);
						Ext.getCmp("tab_med_care").hide().setDisabled(false);
						Ext.getCmp("tab_sanatoriumtreatment").hide().setDisabled(false);
					}

					if (!Ext.getCmp('LPEW_Lpu_OMSPanel').collapsed) {
						Ext.getCmp('LPEW_OMSPeriodGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_OMSGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_OMSPeriodGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					} else {
						Ext.getCmp('LPEW_Lpu_OMSPanel').expand();
						Ext.getCmp('LPEW_Lpu_OMSPanel').collapse();
					}
					
					if (!Ext.getCmp('LPEW_Lpu_DLOPanel').collapsed) {
						Ext.getCmp('LPEW_DLOGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_DLOGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					}
					if (!Ext.getCmp('LPEW_Lpu_OrgWorkPeriodPanel').collapsed&&getRegionNick()=='astra') {
						Ext.getCmp('LPEW_OrgWorkPeriodGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_OrgWorkPeriodGrid').loadData({globalFilters:{Org_id: win.Org_id}, params:{Org_id: win.Org_id}});
					}
					if (!Ext.getCmp('LPEW_Lpu_DMSPanel').collapsed) {
						Ext.getCmp('LPEW_DMSGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_DMSGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					}
					if (!Ext.getCmp('LPEW_Lpu_FondHolderPanel').collapsed) {
						Ext.getCmp('LPEW_FondHolderGrid').removeAll({clearAll: true});
						Ext.getCmp('LPEW_FondHolderGrid').loadData({globalFilters:{Lpu_id: result[0].Lpu_id}, params:{Lpu_id: result[0].Lpu_id}});
					}

					win.refreshTabsVisibility();
				}
			}
		});
	},
	openOrgHeadEditWindow: function(action)
	{
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swOrgHeadEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования руководства уже открыто'));
			return false;
		}

		var grid = this.findById('LPEW_OrgHeadGrid').getGrid();		

		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}
			var grid = current_window.findById('LPEW_OrgHeadGrid').getGrid();			
			var record = grid.getStore().getById(data.OrgHead_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgHead_id') > 0) ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([ data ], true);
			}
			else {
				var head = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					head.push(key);
				});

				for ( i = 0; i < head.length; i++ ) {
					record.set(head[i], data[head[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		var Lpu_id = this.Lpu_id;
		if ( action == 'add' ) {
			params.OrgHead_id = 0;
			params.onHide = function() {
				current_window.findById('LPEW_OrgHeadGrid').focus(true, 100);
			};
			// ищем человека и передаем его
			if (getWnd('swPersonSearchWindow').isVisible())
			{
				current_window.showMessage(langs('Сообщение'), langs('Окно поиска человека уже открыто'));
				return false;
			}
			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					//current_window.refreshPersonDispSearchGrid();
				},
				onSelect: function(person_data) {
					getWnd('swPersonSearchWindow').hide();
					params.Person_id = person_data.Person_id;
					params.Lpu_id = Lpu_id;
					getWnd('swOrgHeadEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else
		{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.OrgHead_id = grid.getSelectionModel().getSelected().get('OrgHead_id');
			params.Person_id = grid.getSelectionModel().getSelected().get('Person_id');
			params.Lpu_id = this.Lpu_id;
			params.onHide = function() {
				current_window.findById('LPEW_OrgHeadGrid').focus(true, 100);
			};
			getWnd('swOrgHeadEditWindow').show(params);
		}
	},
	deleteComputerEquipRequest: function(ajax_params, grid, row) {

		var _this = this;

		Ext.Ajax.request({

			url: '/?c=LpuPassport&m=deleteLpuComputerEquipment',
			params: ajax_params,

			callback: function (options, success, response) {

				if (success) {

					var store = grid.getStore();
					store.remove(row);

					if (store.getCount() == 0) {

						_this.doSearchComputerEquipment(true);
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}
				else
					sw.swMsg.alert(langs('Ошибка'), langs('При удалении возникли ошибки'));
			}
		});

	},
	checkBeforeDeleteComputerEquip: function(id_name,id_value){

		var ajax_input_params = {};
		ajax_input_params[id_name] = id_value;

		// подготовим этот прамис:
		return new Promise(function(resolve, reject) {

			Ext.Ajax.request({

				params: ajax_input_params,
				url: '/?c=LpuPassport&m=checkBeforeDeleteComputerEquip',

				success: function(response) {

					var data = JSON.parse(response.responseText);

					// мы можем удалить запись, если дочерних устройств не найдено
					if (data.length == 0)
						resolve(true);
					else
						resolve(false);
				},
				failure: function(response) {reject(response)}
			})

		})
	},
	refreshComputerEquip: function(){

		// init
		var wnd = this,
			gridName = 'LPEW_ComputerEquipmentGrid',
			grid = this.findById(gridName);

		if (grid.getCount() == 0)
		{
			grid.getGrid().getStore().removeAll();
		} else {

			this.ComputerEquipmentGrid.refreshRecords(null,0);
		}
	},
	deleteComputerEquip: function(){

		// init
		var wnd = this,
			gridName = 'LPEW_ComputerEquipmentGrid',
			rowIdName = 'ComputerEquip_id',
			grid = this.findById(gridName).getGrid(),
			row = grid.getSelectionModel().getSelected(),
			rowId = '',
			is_parent = (row.get('Device_pid') == '') ? true : false;


		// check
		if (!row || !row.get(rowIdName))
			return;
		else
			rowId = row.get(rowIdName);

		// message
		sw.swMsg.show({

			buttons: Ext.Msg.YESNO,
			icon: Ext.MessageBox.QUESTION,
			title: langs('Подтверждение'),
			msg: langs('Вы хотите удалить запись?'),

			fn: function(buttonId, text, obj) {

				if ( buttonId == 'yes' ) {

					var ajax_input_params = {};
					ajax_input_params[rowIdName] = rowId;

					// если родительская категория проверяем => удаляем
					if (is_parent) {

						//promise
						wnd.checkBeforeDeleteComputerEquip(rowIdName, rowId).then(

							function (res) {

								// если дочерних записей нет, удаляем
								if (res) {
									wnd.deleteComputerEquipRequest(ajax_input_params, grid, row);

								// если есть, ругаемся
								} else {

									sw.swMsg.show({

										icon: Ext.MessageBox.ERROR,
										buttons: Ext.Msg.OK,
										title: langs('Ошибка'),
										msg: langs('Невозможно удалить запись на заданный период, пока есть хотя бы одна запись на соответствующий период для устройства, являющегося дочерним'),

										fn: function (buttonId) {

											//grid.getView().focusRow(rowId);
										}

									});

								}
							}
						);

					// если дочерняя категория => удаляем
					} else {
						wnd.deleteComputerEquipRequest(ajax_input_params, grid, row);
					}
				}
			}
		});

	},
	deleteEquipment: function(){
		var grid = this.findById('LPEW_EquipmentGrid').getGrid();
		var record = grid.getSelectionModel().getSelected();

		if ( !record || (!record.get('LpuEquipment_id') && !record.get('LpuEquipmentPacs_id')) )
			return;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								grid.getStore().remove(record);

								if ( grid.getStore().getCount() == 0 ) {
									LoadEmptyRow(grid);
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При удалении возникли ошибки'));
							}
						},
						params: {
							LpuEquipment_id: record.get('LpuEquipment_id'),
							LpuEquipmentPacs_id: record.get('LpuEquipmentPacs_id')
						},
						url: '/?c=LpuPassport&m=deleteEquipment'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: langs('Удалить оборудование'),
			title: langs('Вопрос')
		});
	},
	openLpuOMSWindow:function(type){
		var grid = this.findById('LPEW_OMSGrid').getGrid();
		var parent_rec = this.findById('LPEW_OMSPeriodGrid').getGrid().getSelectionModel().getSelected();
		
		var params = {}; 
		if(parent_rec.get('LpuPeriodOMS_id')&&parent_rec.get('LpuPeriodOMS_id')>0){
			params.LpuPeriodOMS_pid = parent_rec.get('LpuPeriodOMS_id');
		}else{
			Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана Родительская запись!'));
				return false;
		}
		switch(type){
			case'add':
				params.action = type;
				getWnd('swLpuOMSEditWindow').show(params);
				break;
			case 'edit':
			case 'view':
				if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('LpuPeriodOMS_id') )
				{
					Ext.Msg.alert(langs('Ошибка'), langs('Не выбрана запись!'));
					return false;
				}
				var selected_record = grid.getSelectionModel().getSelected();
				params.action = type;
				params.callback = Ext.emptyFn;
				params.LpuPeriodOMS_id = selected_record.data.LpuPeriodOMS_id
				getWnd('swLpuOMSEditWindow').show(params);
				break;
		}

	},
	openEquipmentEditWindow: function(action, Lpu_id)
	{
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}
		var grid = this.findById('LPEW_EquipmentGrid').getGrid();
		var params = {};
		params.action = action;
		params.Lpu_id = Lpu_id;
		params.callback = function(data) {
//			if ( !data ) {
//				return false;
//			}
//			var grid = current_window.findById('LPEW_OrgRSchetGrid').getGrid();			
//			var record = grid.getStore().getById(data.OrgRSchet_id);
//
//			if ( !record ) {
//				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgRSchet_id') > 0) ) {
//					grid.getStore().removeAll();
//				}
//				grid.getStore().loadData([ data ], true);
//			}
//			else {
//				var schet = new Array();
//
//				grid.getStore().fields.eachKey(function(key, item) {
//					schet.push(key);
//				});
//
//				for ( i = 0; i < schet.length; i++ ) {
//					record.set(schet[i], data[schet[i]]);
//				}
//
//				record.commit();
//			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.LpuEquipment_id = 0;
			params.onHide = function() {
				current_window.findById('LPEW_EquipmentGrid').focus(true, 100);
			};
			getWnd('swLpuEquipmentEditWindow').show(params);
		} else	{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.LpuEquipment_id = grid.getSelectionModel().getSelected().get('LpuEquipment_id');
			params.LpuEquipmentPacs_id = grid.getSelectionModel().getSelected().get('LpuEquipmentPacs_id');
			params.Lpu_id = Lpu_id;		
			params.onHide = function() {
				current_window.findById('LPEW_EquipmentGrid').focus(true, 100);
			};
			getWnd('swLpuEquipmentEditWindow').show(params);
		}
	},
	openMedProductCardEditWindow: function(action, Lpu_id)
	{
		var _this = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
		var params = new Object();
		params.action = action;
		params.Lpu_id = Lpu_id;
		params.callback = function(data) {
            _this.findById('LPEW_MedProductCardGrid').loadData();
            //if ( !data ) {
            //	return false;
            //}
            //var grid = current_window.findById('LPEW_OrgRSchetGrid').getGrid();
            //var record = grid.getStore().getById(data.OrgRSchet_id);
            //
            //if ( !record ) {
            //	if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgRSchet_id') > 0) ) {
            //		grid.getStore().removeAll();
            //	}
            //	grid.getStore().loadData([ data ], true);
            //}
            //else {
            //	var schet = new Array();
            //
            //	grid.getStore().fields.eachKey(function(key, item) {
            //		schet.push(key);
            //	});
            //
            //	for ( i = 0; i < schet.length; i++ ) {
            //		record.set(schet[i], data[schet[i]]);
            //	}
            //
            //	record.commit();
            //}
		}

		if ( action == 'add' ) {
			params.LpuEquipment_id = 0;
			params.onHide = function() {
				_this.findById('LPEW_MedProductCardGrid').focus(true, 100);
			};
			getWnd('swMedProductCardEditWindow').show(params);
		} else	{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.MedProductCard_id = grid.getSelectionModel().getSelected().get('MedProductCard_id');
			params.Lpu_id = Lpu_id;
			params.LpuBuilding_id = grid.getSelectionModel().getSelected().get('LpuBuilding_id');
			params.onHide = function() {
				_this.findById('LPEW_MedProductCardGrid').focus(true, 100);
			};
			getWnd('swMedProductCardEditWindow').show(params);
		}
	},
    deleteMedProductCard: function() {

    var grid = this.findById('LPEW_MedProductCardGrid').getGrid();
    var record = grid.getSelectionModel().getSelected();
    if ( !record || !record.get('MedProductCard_id') )
        return;

    sw.swMsg.show({
        buttons: Ext.Msg.YESNO,
        fn: function(buttonId, text, obj) {
            if ( buttonId == 'yes' ) {
                Ext.Ajax.request({
                    callback: function(options, success, response) {
                        if ( success ) {
                            var obj = Ext.util.JSON.decode(response.responseText);
                            if(!obj.success) {
                                return false;
                            }
                            grid.getStore().remove(record);

                            if ( grid.getStore().getCount() == 0 ) {
                                LoadEmptyRow(grid);
                            }

                            grid.getView().focusRow(0);
                            grid.getSelectionModel().selectFirstRow();
                        }
                        else {
                            sw.swMsg.alert(langs('Ошибка'), langs('При удалении карточки медицинского изделия возникли ошибки'));
                        }
                    },
                    params: {
                        MedProductCard_id: record.get('MedProductCard_id')
                    },
                    url: '/?c=LpuPassport&m=deleteMedProductCard'
                });
            }
        },
        icon: Ext.MessageBox.QUESTION,
        msg: langs('Вы действительно хотите удалить запись?'),
        title: langs('Вопрос')
    });
    },
	openOrgRSchetEditWindow: function(action, Lpu_id)
	{
		var current_window = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swOrgRSchetEditWindow').isVisible() ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Окно редактирования счета уже открыто'));
			return false;
		}

		var grid = this.findById('LPEW_OrgRSchetGrid').getGrid();		

		var params = new Object();

		params.action = action;
		params.Lpu_id = Lpu_id;
		params.callback = function(data) {
			if ( !data ) {
				return false;
			}
			var grid = current_window.findById('LPEW_OrgRSchetGrid').getGrid();			
			var record = grid.getStore().getById(data.OrgRSchet_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !(grid.getStore().getAt(0).get('OrgRSchet_id') > 0) ) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData([ data ], true);
			}
			else {
				var schet = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					schet.push(key);
				});

				for ( i = 0; i < schet.length; i++ ) {
					record.set(schet[i], data[schet[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);

		if ( action == 'add' ) {
			params.OrgRSchet_id = 0;
			params.onHide = function() {
				current_window.findById('LPEW_OrgRSchetGrid').focus(true, 100);
			};
			getWnd('swOrgRSchetEditWindow').show(params);
		}
		else
		{
			if ( !grid.getSelectionModel().getSelected() )
				return;
			params.OrgRSchet_id = grid.getSelectionModel().getSelected().get('OrgRSchet_id');
			params.onHide = function() {
				current_window.findById('LPEW_OrgRSchetGrid').focus(true, 100);
			};
			getWnd('swOrgRSchetEditWindow').show(params);
		}
	},
	plain: true,
	resizable: false,
	refreshTabsVisibility: function() {
		var tabs_panel = Ext.getCmp('LpuPassportEditWindowTab');
		var isLab = Ext.getCmp('LPEW_Lpu_IsLab').getValue();
		var isMse = this.Lpu_IsMse;
		
		var MseMap = {
			tab_identification: {
				Lpu_IdentificationPanel: {
					Lpu_IdentificationEditForm: null
				}
			},
			tab_sprav: {
				Lpu_SupInfoPanel: {
					Lpu_SupInfoEditForm: null,
					Lpu_RSchet: null,
					Lpu_MOInfoSys: null
				}
			},
			tab_ruk: null,
			tab_population: null
		};

		var LabMap = {
			tab_identification: {
				Lpu_IdentificationPanel: {
					Lpu_IdentificationEditForm: null,
					LPEW_Lpu_OMSPanel: null,
					LPEW_Lpu_DLOPanel: null,
					LPEW_Lpu_DMSPanel: null,
					LPEW_Lpu_FondHolderPanel: null
				}
			},
			tab_sprav: {
				Lpu_SupInfoPanel:{
					Lpu_SupInfoEditForm: null,
					Lpu_Licence:null,
					Lpu_RSchet:null,
					Lpu_MOInfoSys:null,
					Lpu_Filial: null
				}
			},
			tab_ruk: null,
			tab_computer_equipment: null,
			tab_household: null
		};

		var crossMap = function(map1, map2) {
			var map = {};
			for(var key in map1) {
				if( map2[key] !== undefined ) {
					if(map1[key] !== null && map2[key] != null) {
						map[key] = crossMap(map1[key], map2[key])
					} else {
						map[key] = map1[key]; 
					}
				}
			}
			return map;
		}

		var map;
		if (isLab && isMse) {
			map = crossMap(MseMap, LabMap);
		} else if (isLab) {
			map = LabMap;
		} else {
			map = MseMap;
		}


		tabs_panel.items.each(function(tab) {

			if ( (!isMse && !isLab ) || tab.id.inlist(Object.keys(map)) ) {
				tabs_panel.unhideTabStripItem(tab);
			} else {
				tabs_panel.hideTabStripItem(tab);
			}
		});

		var refreshVisibilityPanels = function(map) {
			if (!map) return;
			for (panelId in map) {
				var panel = Ext.getCmp(panelId);
				var childrenMap = map[panelId];

				panel.items.each(function(childPanel) {
					if ( (isMse || isLab) && childrenMap && !childPanel.id.inlist(Object.keys(childrenMap)) ) {
						childPanel.hide();
					} else {
						childPanel.show();
						refreshVisibilityPanels(childrenMap);
					}
				});
			}
		};

		refreshVisibilityPanels(map);

		if( tabs_panel.activeTab.hidden ) {
			tabs_panel.setActiveTab(Object.keys(map)[0] || 0);
		}
		
	},
	show: function() {
		sw.Promed.swLpuPassportEditWindow.superclass.show.apply(this, arguments);

		this.formAction = 'edit';

		this.maximize();
		this.findById('LpuPassportEditWindowTab').setActiveTab(11);
		this.findById('LpuPassportEditWindowTab').setActiveTab(10);
		this.findById('LpuPassportEditWindowTab').setActiveTab(9);
		this.findById('LpuPassportEditWindowTab').setActiveTab(8);
		this.findById('LpuPassportEditWindowTab').setActiveTab(7);
		this.findById('LpuPassportEditWindowTab').setActiveTab(6);
		this.findById('LpuPassportEditWindowTab').setActiveTab(5);
		this.findById('LpuPassportEditWindowTab').setActiveTab(4);
		this.findById('LpuPassportEditWindowTab').setActiveTab(3);
		this.findById('LpuPassportEditWindowTab').setActiveTab(2);
		this.findById('LpuPassportEditWindowTab').setActiveTab(1);
		this.findById('LpuPassportEditWindowTab').setActiveTab(0);

		//Поле "Тип МО по возрасту" фильтруем, убирая значение "Все МО". refs #11505
		this.findById('LPEW_LpuAgeType_id').setFilter([1,2,3]);
		/* // закрывали специально, чтобы кроме суперадмина никто не видел функционала 
		if (isAdmin)
			this.findById('LpuPassportEditWindowTab').unhideTabStripItem(2);
		else 
			this.findById('LpuPassportEditWindowTab').hideTabStripItem(2);
		*/ 
		this.findById('LPEW_IsAllowInternetModeration').setVisible(false);
		var isPerm = ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ),
		    isUfa = ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ),
            _this = this;
		//var isAstra = ( getGlobalOptions().region && getGlobalOptions().region.nick == 'astra' );
		if (isUfa)
			this.findById('LPEW_IsAllowInternetModeration').setVisible(true);

		if ( !isPerm ) {
			this.findById('LPEW_Lpu_InterCode').setContainerVisible(false);
		}

		/*if ( !isAstra ) {
			this.findById('LPEW_PasportMO_IsAssignNasel').setContainerVisible(false);
		}*/

		this.findById('LPEW_LevelType_id').setContainerVisible(getRegionNick().inlist([ 'astra' ]));

		var wnd = this;
		
		this.Org_id = null;
		this.Lpu_IsMse = false;
		
		if (arguments[0].Lpu_id)
			this.Lpu_id = arguments[0].Lpu_id;
		else 
			this.Lpu_id = null;
			
		if (arguments[0].action) 
		{
			this.action = arguments[0].action;
		}
		else 
		{
			if ( ( this.Lpu_id ) && ( this.Lpu_id > 0 ) )
				this.action = "edit";
			else 
				this.action = "add";
		}

		// https://redmine.swan.perm.ru/issues/15894
		var LpuSubjectionLevelPidList = new Array();
		var supInfoBaseForm = this.findById('Lpu_SupInfoPanel').getForm();

		// Получаем список родительских идентификаторов
		supInfoBaseForm.findField('LpuSubjectionLevel_id').getStore().each(function(rec) {
			if ( !Ext.isEmpty(rec.get('LpuSubjectionLevel_pid')) && !rec.get('LpuSubjectionLevel_pid').inlist(LpuSubjectionLevelPidList) ) {
				LpuSubjectionLevelPidList.push(rec.get('LpuSubjectionLevel_pid'));
			}
		});

		// Фильтруем список, оставляя только записи нижнего уровня
		supInfoBaseForm.findField('LpuSubjectionLevel_id').getStore().filterBy(function(rec) {
			return (!rec.get('LpuSubjectionLevel_id').inlist(LpuSubjectionLevelPidList));
		});

		this.resetForm();

		this.findById('LPEW_LevelType_id').setAllowBlank(true);
		this.findById('LPEW_LpuBuildingPass_mid').setAllowBlank(true);
		this.findById('LPEW_LpuBuildingPass_mid').getStore().removeAll();

		switch (this.action)
		{
			case 'add':
				this.enableEdit(true);
				this.disableGrids();
				break;
			case 'edit':
				this.enableEdit(true);
				this.getLpuInfo(this.Lpu_id);
				break;
			case 'view':
				this.enableEdit(false);
				this.getLpuInfo(this.Lpu_id);
				break;
		}

		var tabs = [
			'tab_identification',
			'tab_sprav',
			'tab_ruk',
			'tab_dogdd',
			'tab_er',
			'tab_zdanie',
			'tab_pacs_equipment',
			'tab_population',
			'tab_med_care',
			'tab_sanatoriumtreatment',
			'tab_computer_equipment',
			'tab_household',
			'tab_medprod'
		];

        //Скрывам вкладки в зависимости от прав пользователей
        if (
			getGlobalOptions().groups
			&& getGlobalOptions().groups.toString().indexOf('MPCModer') != -1
			&& getGlobalOptions().groups.toString().indexOf('Admin') == -1
			&& getGlobalOptions().groups.toString().indexOf('SuperAdmin') == -1
		) {
			tabs.forEach(function(tab) {
                _this.findById('LpuPassportEditWindowTab').hideTabStripItem(tab);
            });

			this.findById('LpuPassportEditWindowTab').unhideTabStripItem('tab_medprod');
		    this.findById('LpuPassportEditWindowTab').setActiveTab('tab_medprod');
			Ext.getCmp('LPEW_LpuPassportReportButton').hide();

        } else {

			tabs.forEach(function(tab){
                _this.findById('LpuPassportEditWindowTab').unhideTabStripItem(tab);
            });

            this.findById('LpuPassportEditWindowTab').setActiveTab('tab_identification');
			Ext.getCmp('LPEW_LpuPassportReportButton').show();
        }

		var org_rschet_grid = Ext.getCmp('LPEW_OrgRSchetGrid');
		var org_head = Ext.getCmp('LPEW_OrgHeadGrid');
		org_rschet_grid.focus();
	}
});