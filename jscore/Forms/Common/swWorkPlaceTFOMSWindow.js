/**
* АРМ пользователя ТФОМС
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      январь.2014
*/
sw.Promed.swWorkPlaceTFOMSWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.GridPanel.removeAll({
			clearAll: true
		});
		this.FilterPanel.getForm().findField('EvnDirection_setDate').setValue(getGlobalOptions().date);
	},
	doSearch: function() {
		if ( this.formMode == 'search' ) {
			return false;
		}

		this.formMode = 'search';

		var base_form = this.FilterPanel.getForm();

		var
			 EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue()
			,EvnPS_setDate = base_form.findField('EvnPS_setDate').getValue()
			,OrgSmo_id = base_form.findField('OrgSmo_id').getValue()
			,Lpu_did = base_form.findField('Lpu_did').getValue()
			,LpuSectionProfile_did = base_form.findField('LpuSectionProfile_did').getValue()
			,Lpu_sid = base_form.findField('Lpu_sid').getValue()
			,Over20DaysInQueue = (base_form.findField('Over20DaysInQueueCheckBox').getValue() == true ? 1 : null);

		if ( Ext.isEmpty(Over20DaysInQueue) && Ext.isEmpty(EvnDirection_setDate) && Ext.isEmpty(EvnPS_setDate) ) {
			this.formMode = 'iddle';
			sw.swMsg.alert(lang['oshibka'], lang['odno_iz_poley_data_vyipiski_napravleniya_data_gospitalizatsii_doljno_byit_zapolneno']);
			return false;
		}

		if ( Ext.isEmpty(OrgSmo_id) && Ext.isEmpty(Lpu_did) && Ext.isEmpty(LpuSectionProfile_did) && Ext.isEmpty(Lpu_sid) ) {
			this.formMode = 'iddle';
			sw.swMsg.alert(lang['oshibka'], lang['odno_iz_poley_smo_mo_napravleniya_mo_gospitalizatsii_profil_doljno_byit_zapolneno']);
			return false;
		}

		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {});

		params.Over20DaysInQueue = Over20DaysInQueue;

		params.limit = 100;
		params.start = 0;

		this.GridPanel.removeAll({
			clearAll: true
		});

		this.GridPanel.loadData({
			callback: function() {
				this.formMode = 'iddle';
			}.createDelegate(this),
			globalFilters: params
		});
	},
	addEvnDirection: function() {
		var win = this;
		var grid = this.GridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();
		var params = new Object({
			userMedStaffFact: this.userMedStaffFact,
			onDirection: function(data) {

			}
		});
		var personData = new Object();

		if ( grid.getSelectionModel().getSelected() && !Ext.isEmpty(grid.getSelectionModel().getSelected().get('Person_id')) ) {
			if (record.get('Person_IsDead') == "true") {
				params.isDead = true;
			}
			personData.Person_Firname = record.get('Person_Firname');
			personData.Person_id = record.get('Person_id');
			personData.PersonEvn_id = record.get('PersonEvn_id');
			personData.Server_id = record.get('Server_id');
			personData.Person_IsDead = record.get('Person_IsDead');
			personData.Person_Secname = record.get('Person_Secname');
			personData.Person_Surname = record.get('Person_Surname');
			personData.AttachLpu_Name = record.get('AttachLpu_Name');
			personData.Person_Birthday = record.get('Person_Birthday');
			params.personData = personData;
		}

		getWnd('swDirectionMasterWindow').show(params);
	},
	gridPanelAutoLoad: false,
	id: 'swWorkPlaceTFOMSWindow',
	initComponent: function() {
		var form = this;

		this.buttonPanelActions = {
            action_emk: {
                nn: 'action_emk',
                tooltip: lang['otkryit_emk'],
                text: lang['otkryit_emk'],
                iconCls: 'emc-evnps32',
                disabled: false,
                handler: function() {
					var params = {
						searchMode: 'all',
						onSelect: function(PersonData) {
							getWnd('swPersonSearchWindow').hide();

							this.openPersonEmkWindow(PersonData);
						}.createDelegate(this)
					};

					getWnd('swPersonSearchWindow').show(params);
                }.createDelegate(this)
            },
            action_Timetable: {
                nn: 'action_Timetable',
                tooltip: lang['rabota_s_raspisaniem'],
                text: lang['raspisanie'],
                iconCls: 'mp-timetable32',
                disabled: false,
                handler: function() {
                    getWnd('swDirectionMasterWindow').show({
                        type: 'SMO'
                    });
                }.createDelegate(this)
            },
            action_MOList: {
                nn: 'action_MOList',
                tooltip: lang['pasport_mo'],
                text: lang['pasport_mo'],
                iconCls: 'org32',
                disabled: false,
                hidden: !(getGlobalOptions().region.nick=='perm'),
                handler: function() {
                    getWnd('swMOListWindow').show();
                }.createDelegate(this)
            },
            action_MOExport: {
                nn: 'action_MOExport',
                tooltip: lang['vyigruzka_pasportov_mo'],
                text: lang['vyigruzka_pasportov_mo'],
                iconCls: 'database-export32',
                disabled: false,
                hidden: !(getGlobalOptions().region.nick=='perm'),
                handler: function() {
                    sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
                }.createDelegate(this)
            },
            action_TFOMSexport: {
                nn: 'action_TFOMSexport',
                tooltip: lang['eksport_dannyih'],
                text: lang['eksport_dannyih_dlya_tfoms'],
                iconCls : 'database-export32',
                disabled: false,
                hidden: !getRegionNick().inlist(['khak']),
                handler: function() {
                    getWnd('swHospDataExportForTfomsWindow').show({ARMType: this.ARMType});
                }.createDelegate(this)
			},
			action_TFOMSQueryList: {
				nn: 'action_TFOMSQueryList',
				//hidden: getRegionNick().inlist(['by']),
				text: 'Запросы на просмотр ЭМК',
				tooltip: 'Запросы на просмотр ЭМК',
				iconCls: 'tfoms-query32',
				handler: function () {
					getWnd('swTFOMSQueryWindow').show({ARMType: form.ARMType});
            }
			}
        };

		form.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				form.doSearch();
			}
		}.createDelegate(this);
		
		form.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			filter: {
				title: lang['filtr'],
				layout: 'form',
				items: [{
					border: false,
					labelWidth: 145,
					layout: 'form',
					items: [{
						fieldLabel: lang['smo'],
						hiddenName: 'OrgSmo_id',
						id: 'WPTFOMS_OrgSmo_id',
						width: 425,
						xtype: 'sworgsmocombo'
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						bodyStyle: 'background-color: transparent; padding-left: 5px;',
						defaults: {
							width: 120
						},
						layout: 'form',
						labelWidth: 140,
						items: [{
							fieldLabel: lang['familiya'],
							name: 'Person_Surname',
							xtype: 'textfield'
						}, {
							fieldLabel: lang['imya'],
							name: 'Person_Firname',
							xtype: 'textfield'
						}, {
							fieldLabel: lang['otchestvo'],
							name: 'Person_Secname',
							xtype: 'textfield'
						}, {
							fieldLabel: lang['data_rojdeniya'],
							format: 'd.m.Y',
							name: 'Person_Birthday',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}]
					}, {
						layout: 'form',
						labelAlign: 'right',
						labelWidth: 200,
						border: false,
						bodyStyle: 'background-color: transparent; padding-left: 5px;',
						items: [{
							fieldLabel: lang['data_vyipiski_napravleniya'],
							format: 'd.m.Y',
							name: 'EvnDirection_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}, {
							hiddenName: 'DirType_id',
							width: 200,
							xtype: 'swdirtypecombo'
						}, {
							allowBlank: true,
							fieldLabel: lang['mo_napravleniya'],
							hiddenName: 'Lpu_sid',
							listeners: {
								'render': function(combo) {
									combo.setBaseFilter(function(rec) {
										return !rec.get('Lpu_id').inlist([ 100, 101 ]);
									});
								}
							},
							listWidth: 350,
							width: 200,
							xtype: 'swlpulocalcombo'
						}, {
							fieldLabel: lang['profil'],
							hiddenName: 'LpuSectionProfile_did',
							listWidth: 350,
							width: 200,
							xtype: 'swlpusectionprofilecombo'
						}, {
							fieldLabel: lang['data_otmenyi_napravleniya'],
							format: 'd.m.Y',
							name: 'EvnDirection_failDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}, {
							fieldLabel: lang['prichina_otmenyi_napravleniya'],
							hiddenName: 'DirFailType_id',
							listWidth: 350,
							width: 200,
							xtype: 'swdirfailtypecombo'
						}, {
							fieldLabel: lang['ochered_bolee_20_dney'],
							name: 'Over20DaysInQueueCheckBox',
							xtype: 'checkbox'
						}]
					}, {
						border: false,
						bodyStyle: 'background-color: transparent; padding-left: 5px;',
						defaults: {
							width: 300
						},
						layout: 'form',
						labelWidth: 170,
						items: [{
							allowBlank: true,
							fieldLabel: lang['mo_gospitalizatsii'],
							hiddenName: 'Lpu_did',
							listeners: {
								'render': function(combo) {
									combo.setBaseFilter(function(rec) {
										return !rec.get('Lpu_id').inlist([ 100, 101 ]);
									});
								}
							},
							xtype: 'swlpulocalcombo'
						}, {
							fieldLabel: lang['data_gospitalizatsii'],
							format: 'd.m.Y',
							name: 'EvnPS_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						}, {
							fieldLabel: lang['tip_gospitalizatsii'],
							hiddenName: 'PrehospType_id',
							xtype: 'swprehosptypecombo'
						}, {
							fieldLabel: lang['kem_dostavlen'],
							hiddenName: 'PrehospArrive_id',
							xtype: 'swprehosparrivecombo'
						}, {
							fieldLabel: lang['ishod_gospitalizatsii'],
							hiddenName: 'LeaveType_id',
							xtype: 'swleavetypecombo'
						}, {
							fieldLabel: lang['data_okonchaniya_sluchaya'],
							format: 'd.m.Y',
							name: 'EvnPS_disDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							width: 100,
							xtype: 'swdatefield'
						}]
					}]
				}]
			}
		});
		
		form.GridPanel = new sw.Promed.ViewFrame({
			object: 'EvnDirection',
			dataUrl: '/?c=EvnDirection&m=loadSMOWorkplaceJournal',
			layout: 'fit',
			region: 'center',
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			allowedPersonKeys: false,
			autoLoadData: false,
			stringfields: [
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Person_Fio', autoexpand: true, type: 'string', header: lang['fio_patsienta']},
				{name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'DirType_Name', header: lang['tip_napravleniya'], width: 150},
				{name: 'LpuDir_Name', header: lang['napravivshaya_mo'], width: 120},
				{name:'LpuSection', header: lang['otdelenie'], width:120},
				{name: 'EvnDirection_setDate', type: 'date', header: lang['data_vyipiski_napravleniya'], width: 90},
				{name: 'EvnDirection_failDate', type: 'date', header: lang['data_otmenyi_napravleniya'], width: 90},
				{name: 'DirFailType_Name', header: lang['prichina_otmenyi_napravleniya'], width: 100},
				{name: 'LpuFail_Name', header: lang['mo_otmenivshaya_napravlenie'], width: 120},
				{name: 'LpuSectionProfile_Name', header: lang['profil_napravleniya'], width: 120},
				{name: 'MedPersonal_Fio', header: lang['vrach'], width: 120},
				{name: 'Lpu_Name', header: lang['mo_gospitalizatsii'], width: 80},
				{name: 'TimetableStac_setDate', header: lang['planovaya_data_gospitalizatsii'], width: 80},
				{name: 'WaitingDays', header: lang['ojidanie_dney'], width: 80},
				{name: 'PrehospType_Name', header: lang['tip_gospitalizatsii'], width: 100},
				{name: 'PrehospArrive_Name', header: lang['kem_dostavlen'], width: 100},
				{name: 'EvnPS_OutcomeDate', type: 'date', header: lang['data_otkaza'], width: 90},
				{name: 'PrehospWaifRefuseCause_Name', header: lang['prichina_otkaza'], width: 100},
				{name: 'EvnPS_setDate', type: 'date', header: lang['data_gospitalizatsii'], width: 90},
				{name: 'EvnPS_setTime', header: lang['vremya_gospitalizatsii'], width: 50},
				{name: 'EvnPS_NumCard', header: lang['№_kartyi_statsionarnogo_bolnogo'], width: 50},
				{name: 'Diag_Name', header: lang['diagnoz_priemnogo_otdeleniya'], width: 150},
				{name: 'EvnPS_disDate', type: 'date', header: lang['data_okonchaniya_sluchaya'], width: 90}
			],
			actions: [
				{ name: 'action_add', text: langs('Записать'), handler: function() {form.addEvnDirection();}, hidden: getRegionNick()=='kz' },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			onRowSelect: function(sm, rowIdx, record) {
				if ( !Ext.isEmpty(record.get('Person_id')) ) {
					this.GridPanel.ViewActions['open_emk'].setDisabled(false);
					this.GridPanel.ViewActions['open_person'].setDisabled(false);
					this.GridPanel.ViewActions['open_personcard'].setDisabled(false);
				}
				else {
					this.GridPanel.ViewActions['open_emk'].setDisabled(true);
					this.GridPanel.ViewActions['open_person'].setDisabled(true);
					this.GridPanel.ViewActions['open_personcard'].setDisabled(true);
				}

				if ( !Ext.isEmpty(record.get('EvnDirection_id')) ) {
					this.GridPanel.ViewActions['open_direction'].setDisabled(false);
				}
				else {
					this.GridPanel.ViewActions['open_direction'].setDisabled(true);
				}
			}.createDelegate(this)
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					form.doSearch();
				},
				iconCls: 'search16',
				text: lang['poisk'],
				xtype: 'button'
			}, {
				handler: function() {
					form.doReset();
				},
				iconCls: 'resetsearch16',
				text: lang['sbros'],
				xtype: 'button'
			}, {
				text: '-'
			}, {
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(this.title);
				}.createDelegate(this)
			}, {
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {
					form.hide();
				}.createDelegate(this)
			}]
		});

		sw.Promed.swWorkPlaceTFOMSWindow.superclass.initComponent.apply(this, arguments);
	},
	openEvnDirectionViewWindow: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('EvnDirection_id')) ) {
			return false;
		}

		sw.Promed.Direction.print({
			EvnDirection_id: record.get('EvnDirection_id')
		});

		return true;
	},
	openPersonCardViewWindow: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_id')) ) {
			return false;
		}

		var fio = new Array();

		if ( !Ext.isEmpty(record.get('Person_Fio')) ) {
			var fio = record.get('Person_Fio').split(' ');
		}

		getWnd('swPersonCardHistoryWindow').show({
			 action: 'view'
			,Person_id: record.get('Person_id')
			,Server_id: record.get('Server_id')
			,PersonEvn_id: record.get('PersonEvn_id')
			,Person_Surname: (!Ext.isEmpty(fio[0]) ? fio[0] : '')
			,Person_Firname: (!Ext.isEmpty(fio[1]) ? fio[1] : '')
			,Person_Secname: (!Ext.isEmpty(fio[2]) ? fio[2] : '')
			,Person_Birthday: record.get('Person_Birthday')
		});

		return true;
	},
	openPersonEmkWindow: function(PersonData) {
		if (!PersonData || !PersonData.Person_id) {
			var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

			if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_id')) ) {
				return false;
			}
			PersonData = {
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id')
			};
		}

		getWnd('swPersonEmkWindow').show({
			Person_id: PersonData.Person_id,
			Server_id: PersonData.Server_id,
			PersonEvn_id: PersonData.PersonEvn_id,
			readOnly: true,
			ARMType: 'common'
		});

		return true;
	},
	openPersonViewWindow: function() {
		var record = this.GridPanel.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_id')) ) {
			return false;
		}

		getWnd('swPersonEditWindow').show({
			action: 'view',
			readOnly: true,
			Person_id: record.get('Person_id'),
			formParams: {
				Person_id: record.get('Person_id')
			}
		});

		return true;
	},
	checkAccessTfomsToFunctionalEMK: function(org_id){
		var win = this;
		// проверка органицации на доступ к функционалу ЭМК 
		var btnREMK = win.find('nn', 'action_emk'),
			btnPEMK = win.GridPanel.ViewActions['open_emk'],
			btnRQuery = win.find('nn', 'action_TFOMSQueryList');
		if(btnREMK) btnREMK[0].hide();
		if(btnPEMK) btnPEMK.hide();
		if(btnRQuery) btnRQuery[0].hide();

		if(!org_id) return false;
		Ext.Ajax.request({
			params: {
				Org_id: org_id
			},
			callback: function(options, success, response) {
				if (success){
					var response_obj  = Ext.util.JSON.decode(response.responseText);
					
					for (var i=0; i < response_obj.length; i++) {
						var access = response_obj[i].access;
						switch(access) {
							case 'mes':
								// Если появятся справочники МЭС
								break;
							case 'emk':
								if(btnREMK) btnREMK[0].show();
								if(btnPEMK) btnPEMK.show();
								break;
							case 'query':
								if(btnRQuery) btnRQuery[0].show();
								break;
			 		}
				}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при проверке СМО'));
				}
			},
			url: '/?c=AccessRights&m=checkArmSmoAccess'
		});
	},
	show: function() {
		sw.Promed.swWorkPlaceTFOMSWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_argumentyi'], function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}

		var OrgSMOCombo = this.findById('WPTFOMS_OrgSmo_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.lastQuery = lang['stroka_kotoruyu_nikto_ne_dodumaetsya_vvodit_v_kachestve_filtra_ibo_eto_bred_iskat_smo_po_takoy_stroke'];

		OrgSMOCombo.getStore().filterBy(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getGlobalOptions().region.number && rec.get('OrgSMO_IsTFOMS') != 2);
		});

		OrgSMOCombo.setBaseFilter(function(rec) {
			return (Ext.isEmpty(rec.get('OrgSMO_endDate')) && rec.get('KLRgn_id') == getGlobalOptions().region.number && rec.get('OrgSMO_IsTFOMS') != 2);
		});

		this.formMode = 'iddle';
		this.Org_id = arguments[0].Org_id || null;
		this.ARMType = arguments[0].ARMType || null;

        log(this.ARMType);

		this.GridPanel.addActions({
			handler: function() {
				this.openPersonEmkWindow();
			}.createDelegate(this),
			iconCls: 'open16',
			name: 'open_emk',
			text: lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			disabled: true
		});

		this.GridPanel.addActions({
			handler: function() {
				this.openEvnDirectionViewWindow();
			}.createDelegate(this),
			iconCls: 'pol-directions16',
			name: 'open_direction',
			text: lang['napravlenie'],
			tooltip: lang['napravlenie'],
			disabled: true
		});

		this.GridPanel.addActions({
			handler: function() {
				this.openPersonCardViewWindow();
			}.createDelegate(this),
			iconCls: 'pers-card16',
			name: 'open_personcard',
			text: lang['prikreplenie'],
			tooltip: lang['prikreplenie'],
			disabled: true
		});

		this.GridPanel.addActions({
			handler: function() {
				this.openPersonViewWindow();
			}.createDelegate(this),
			iconCls: 'patient16',
			name: 'open_person',
			text: lang['patsient'],
			tooltip: lang['patsient'],
			disabled: true
		});

		this.checkAccessTfomsToFunctionalEMK(this.Org_id);

		this.doReset();
	},
	showToolbar: false
});