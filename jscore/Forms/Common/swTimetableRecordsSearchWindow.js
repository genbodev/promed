/**
 * swTimetableRecordsSearchWindow - окно поиска записей на прием
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014, Swan.
 * @author       Abakhri Samir
 * @prefix       EPSRSW
 * @version      6.10.2014
 */
/*NO PARSE JSON*/

sw.Promed.swTimetableRecordsSearchWindow = Ext.extend(sw.Promed.BaseForm,
{
	useUecReader: true,
	codeRefresh: true,
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_DIR_SEARCH,
	iconCls: 'workplace-mp16',
	id: 'swTimetableRecordsSearchWindow',
	userMedStaffFact: null,
	readOnly: false,
	cancelEvnDirection: function() {
		if (this.readOnly) {
			return false;
		}
		var cancelType = 'decline'; // отклонение, т.к. не под суперадмином только входящие
		var win = this;
		var rec = this.getSelectedRecord(['Person_id']);
		if (!rec) {
			return false;
		}
		var grid = this.getGrid();
		var result = rec.id.split('_');
		if (isSuperAdmin()) {
			//отмена, если исходящее
			//отклонение, если входящее
		}
		var personData = {};
		personData.Person_Firname = rec.get('Person_FirName');
		personData.Person_Secname = rec.get('Person_SecName');
		personData.Person_Surname = rec.get('Person_SurName');
		personData.Person_id = rec.get('Person_id');
		personData.PersonEvn_id = rec.get('PersonEvn_id');
		personData.Server_id = rec.get('Server_id');
		personData.Person_IsDead = rec.get('Person_IsDead');
		personData.AttachLpu_Name = rec.get('AttachLpu_Name');
		return sw.Promed.Direction.cancel({
			cancelType: cancelType,
			ownerWindow: win,
			formType: 'reg',
			allowRedirect: true,
			userMedStaffFact: win.userMedStaffFact,
			EvnDirection_id: rec.get('EvnDirection_id')||null,
			DirType_Code: rec.get('DirType_Code')||null,
			TimetableGraf_id: (result[0]&&result[0]=='TimetableGraf') ? result[1] : null,
			TimetableStac_id: (result[0]&&result[0]=='TimetableStac') ? result[1] : null,
			EvnQueue_id: (result[0]&&result[0]=='EvnQueue') ? result[1] : null,
			personData: personData,
			callback: function(cfg) {
				grid.getStore().reload();
			}
		});
	},
	setMenu: function(first) {
		if(first)
			this.createListPrehospWaifRefuseCause();
	},
	show: function() {
		sw.Promed.swTimetableRecordsSearchWindow.superclass.show.apply(this, arguments);

		this.userMedStaffFact = arguments[0].userMedStaffFact || sw.Promed.MedStaffFactByUser.last;
        var _this = this,
            base_form = this.FilterPanel.getForm();

		this.resetFilter();
        _this.mainGrid.getStore().removeAll();

		if(isSuperAdmin()){
			base_form.findField('RecLpu_id').enable();
		}else{
			base_form.findField('RecLpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('RecLpu_id').disable();
		}

		base_form.findField('UserLpu_id').setContainerVisible(isCallCenterAdmin()/* || isSuperAdmin()*/);

		var params = {
			withoutPaging: true
		}

		if ( isCallCenterAdmin() /*|| isSuperAdmin()*/ ) {
			base_form.findField('UserLpu_id').setValue(getGlobalOptions().lpu_id);
		}
		else {
			params.org = getGlobalOptions().org_id;
		}

		this.getLoadMask(lang['zagruzka_spiska_polzovateley']).show();

		base_form.findField('pmUser_id').getStore().load({
			params: params,
			callback: function() {
				this.getLoadMask().hide();
				this.setUserListFilter();
			}.createDelegate(this)
		});

        setCurrentDateTime({
            dateField: base_form.findField('RecordDate_from'),
            loadMask: true,
            setDate: true,
            setDateMaxValue: false,
            setDateMinValue: false,
            windowId: 'swTimetableRecordsSearchWindow'
        });

        setCurrentDateTime({
            dateField: base_form.findField('RecordDate_to'),
            loadMask: true,
            setDate: true,
            setDateMaxValue: false,
            setDateMinValue: false,
            windowId: 'swTimetableRecordsSearchWindow'
        });

		this.syncSize();
	},
	getGrid: function () {
		return this.mainGrid;
	},	
	resetFilter: function () {
		var base_form = this.FilterPanel.getForm(), lpuId = base_form.findField('RecLpu_id').getValue();
		
		base_form.reset();
		if(base_form.findField('RecLpu_id').disabled){
			base_form.findField('RecLpu_id').setValue(lpuId);
		}
	},
	doSearch: function(option) {
		var _this = this,
            base_form = this.FilterPanel.getForm(),
            params = base_form.getValues();
		if(base_form.findField('RecLpu_id').disabled){
			params.RecLpu_id = base_form.findField('RecLpu_id').getValue();
		}

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					_this.FilterPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
            this.formStatus = 'edit';
			return false;
		}

		var callback = (option && option.onLoad) || null;
		this.getGrid().loadStore(params, callback);
	},
	getSelectedRecord: function(key_list) {
		var grid = this.getGrid();
		if (!grid)
		{
			Ext.Msg.alert(lang['oshibka'], lang['spisok_zapisey_ne_nayden']);
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
			return false;
		}
		if (key_list)
		{
			for(var i = 0;i < key_list.length;i++)
			{
				if(!record.get(key_list[i]))
				{
					Ext.Msg.alert(lang['oshibka'], lang['v_zapisi_otsutstvuet_odin_iz_neobhodimyih_parametrov']);
					return false;
				}
			}
		}
		return record;
	},
	
	openEvnDirectionEditWindow: function(option) {
        var record = this.getGrid().getSelectionModel().getSelected();

        getWnd('swEvnDirectionEditWindow').show({
			Person_id: record.data.Person_id,
            EvnDirection_id: record.get('EvnDirection_id'),
            action: option.action,
            formParams: {}
        });
    },
	openPersonRecordWindow: function() {
		if (this.readOnly) {
			return false;
		}
		var grid = this.getGrid(),
			record = this.getSelectedRecord(['Person_id','EvnDirection_id']);
		if (!record) {
			return false;
		}
		var win = this;
		var userMedStaffFact = win.userMedStaffFact;
		var result = record.id.split('_');
		var personData = {};
		personData.Person_Firname = record.get('Person_FirName');
		personData.Person_Secname = record.get('Person_SecName');
		personData.Person_Surname = record.get('Person_SurName');
		personData.Person_id = record.get('Person_id');
		personData.PersonEvn_id = record.get('PersonEvn_id');
		personData.Server_id = record.get('Server_id');
		personData.Person_IsDead = record.get('Person_IsDead');
		personData.AttachLpu_Name = record.get('AttachLpu_Name');
		if (personData.Person_IsDead == "true"){
			Ext.Msg.alert(lang['oshibka'], lang['zapis_nevozmojna_v_svyazi_so_smertyu_patsienta']);
			return false;
		}
		if (result[0]&&result[0]=='EvnQueue') {
			// если в очереди, то записать из очереди
			win.getLoadMask(lang['pojaluysta_podojdite']).show();
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=getDataEvnDirection',
				params: {
					EvnDirection_id: record.get('EvnDirection_id'),
					EvnQueue_id: result[1]
				},
				callback: function(options, success, response)  {
					win.getLoadMask().hide();
					if (success) {
						var result  = Ext.util.JSON.decode(response.responseText);
						var data = result[0];
						var params =
						{
							useCase: 'record_from_queue',
							Diag_id: data.Diag_id,
							personData: {
								Person_id: data.Person_id,
								Server_id: data.Server_id,
								PersonEvn_id: data.PersonEvn_id,
								Person_Birthday: data.Person_BirthDay,
								Person_Surname: data.Person_SurName,
								Person_Firname: data.Person_FirName,
								Person_Secname: data.Person_SecName
							},
							EvnQueue_id: data.EvnQueue_id,
							EvnDirection_pid: data.EvnDirection_pid || null,
							LpuSectionProfile_id: data.LpuSectionProfile_did,
							userMedStaffFact: userMedStaffFact,
							onDirection: function (data) {
								// обновляем грид
								grid.getStore().reload({
									callback: function () {
										var index = grid.getStore().findBy( function(r) {
											if (r.get('EvnDirection_id') == record.get('EvnDirection_id') ) {
												return true;
											}
										});
										if (index > -1) {
											grid.getView().focusRow(index);
											grid.getSelectionModel().selectRow(index);
										}
									}
								});
							},
							ARMType: (userMedStaffFact.ARMType)?userMedStaffFact.ARMType:'regpol'
						}

						if (data.type == 'EvnQueue' && data.Resource_id) {
							params.TimetableData = {
								type: 'TimetableResource',
								EvnQueue_id: record.get('EvnQueue_id'),
								EvnDirection_id: data.EvnDirection_id,
								EvnDirection_pid: data.EvnDirection_pid || null,
								EvnDirection_Num: data.EvnDirection_Num,
								EvnDirection_setDate: data.EvnDirection_setDate,
								EvnDirection_IsAuto: data.EvnDirection_IsAuto,
								EvnDirection_IsReceive: data.EvnDirection_IsReceive,
								MedStaffFact_id: data.MedStaffFact_id,
								From_MedStaffFact_id: data.From_MedStaffFact_id,
								LpuUnit_did: data.LpuUnit_did,
								Lpu_did: data.Lpu_did,
								MedPersonal_did: data.MedPersonal_did,
								LpuSection_did: data.LpuSection_did,
								LpuSectionProfile_id: data.LpuSectionProfile_id,
								DirType_id: data.DirType_id,
								DirType_Code: data.DirType_Code,
								ARMType_id: data.ARMType_id,
								MedServiceType_SysNick: data.MedServiceType_SysNick,
								MedService_id: data.MedService_id,
								isAllowRecToUslugaComplexMedService: false,
								UslugaComplexMedService_id: data.UslugaComplexMedService_id,
								MedService_Nick: data.MedService_Nick,
								Resource_id: data.Resource_id,
								Resource_Name: data.Resource_Name
							};
						} else if (data.type == 'EvnQueue' && data.MedService_id) {
							// для службы открываем сразу расписание
							params.TimetableData = {
								type: 'TimetableMedService',
								EvnQueue_id: record.get('EvnQueue_id'),
								EvnDirection_id: data.EvnDirection_id,
								EvnDirection_pid: data.EvnDirection_pid || null,
								EvnDirection_Num: data.EvnDirection_Num,
								EvnDirection_setDate: data.EvnDirection_setDate,
								EvnDirection_IsAuto: data.EvnDirection_IsAuto,
								EvnDirection_IsReceive: data.EvnDirection_IsReceive,
								MedStaffFact_id: data.MedStaffFact_id,
								From_MedStaffFact_id: data.From_MedStaffFact_id,
								LpuUnit_did: data.LpuUnit_did,
								Lpu_did: data.Lpu_did,
								MedPersonal_did: data.MedPersonal_did,
								LpuSection_did: data.LpuSection_did,
								LpuSectionProfile_id: data.LpuSectionProfile_id,
								DirType_id: data.DirType_id,
								DirType_Code: data.DirType_Code,
								ARMType_id: data.ARMType_id,
								MedServiceType_SysNick: data.MedServiceType_SysNick,
								MedService_id: data.MedService_id,
								isAllowRecToUslugaComplexMedService: data.isAllowRecToUslugaComplexMedService,
								UslugaComplexMedService_id: (data.isAllowRecToUslugaComplexMedService && data.UslugaComplexMedService_id) ? data.UslugaComplexMedService_id : null,
								MedService_Nick: data.MedService_Nick
							};
						} else {
							params.dirTypeData = {
								DirType_id: data.DirType_id,
								DirType_Code: data.DirType_Code,
								DirType_Name: data.DirType_Name
							};
							params.directionData = data;
							params.directionData['redirectEvnDirection'] = 600; // признак записи из очереди
						}
						
						getWnd('swDirectionMasterWindow').show(params);
					} else
						sw.swMsg.alert(lang['oshibka'], lang['proizoshla_oshibka']);
				}
			});
			return true;
		}
		if (result[0]&&(result[0]=='TimetableGraf'||result[0]=='TimetableStac')) {
			// если записан, то перезаписать
			sw.Promed.Direction.rewrite({
				loadMask: win.getLoadMask(lang['pojaluysta_podojdite']),
				userMedStaffFact: userMedStaffFact,
				EvnDirection_id: record.get('EvnDirection_id'),
				callback: function (data) {
					// обновляем грид
					grid.getStore().reload({
						callback: function () {
							if (data.EvnDirection_id) {
								var index = grid.getStore().findBy( function(record) {
									if( record.get('EvnDirection_id') == data.EvnDirection_id ) {
										return true;
									}
								});
								if (index > -1) {
									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
							}
						}
					});
				}
			});
			return true;
		}
		// иначе перезаписать
		return false;
	},
	printEvnDirection: function() {
		var grid = this.getGrid(),
			rec = grid.getSelectionModel().getSelected();

		if (!rec) {
			return false;
		}

		sw.Promed.Direction.print({
			EvnDirection_id: rec.get('EvnDirection_id')
		});
	},
	setActionDisabled: function(action, flag)
	{
		if (this.gridActions[action])
		{
			this.gridActions[action].initialConfig.initialDisabled = flag;
			this.gridActions[action].setDisabled(flag);
		}
	},
	curDate: null,
    onKeyDown: function (inp, e) {
		var _this = this;

        if ( e.getKey() == Ext.EventObject.ENTER ) {
            e.stopEvent();
            _this.doSearch();
        }

    },
	setUserListFilter: function() {
		var base_form = this.FilterPanel.getForm();

		var
			onlyCallCenterUsers = base_form.findField('onlyCallCenterUsers').getValue(),
			pmUserField = base_form.findField('pmUser_id'),
			pmUser = pmUserField.getValue(),
			UserLpu_id = base_form.findField('UserLpu_id').getValue();

		pmUserField.getStore().clearFilter();

		if ( onlyCallCenterUsers || !Ext.isEmpty(UserLpu_id) ) {
			pmUserField.getStore().filterBy(function(rec) {
				return (
					(
						!onlyCallCenterUsers
						|| (
							rec.get('groups')
							&& (rec.get('groups').indexOf('OperatorCallCenter') !== -1 || rec.get('groups').indexOf('CallCenterAdmin') !== -1)
						)
					)
					&& (
						Ext.isEmpty(UserLpu_id)
						|| rec.get('Lpu_id') == UserLpu_id
					)
				);
			});
		}

		checkValueInStore(base_form, 'pmUser_id', 'pmUser_id', pmUser);
	},
	initComponent: function()
	{
		
		this.formActions = [];

		var wnd = this;

		this.FilterPanel = new Ext.form.FormPanel({
			region: 'north',
			frame: true,
			border: false,
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 90,
			items: 
			[{
				xtype: 'fieldset',
				style:'padding: 0px 3px 3px 6px;',
				autoHeight: true,
				listeners: {
					expand: function() {
						this.ownerCt.doLayout();
						form.syncSize();
					},
					collapse: function() {
						form.syncSize();
					}
				},
				collapsible: true,
				collapsed: false,
				title: lang['filtr'],
				bodyStyle: 'background: #DFE8F6;',
				items:
                [{
					layout: 'column',
					items:
					[{
						layout: 'form',
						items:
						[{
							xtype: 'textfieldpmw',
							allowBlank: false,
							width: 150,
                            tabIndex: TABINDEX_EPSRSW + 1,
							id: 'EPSRSW_Search_SurName',
							name:'Person_SurName',
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': wnd.onKeyDown.createDelegate(this)
							}
						}]
					},{
						layout: 'form',
						labelWidth: 35,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 150,
                            tabIndex: TABINDEX_EPSRSW + 2,
							id: 'EPSRSW_Search_FirName',
							name:'Person_FirName',
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': wnd.onKeyDown.createDelegate(this)
							}
						}]
					},{
						layout: 'form',
						labelWidth: 80,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
                            tabIndex: TABINDEX_EPSRSW + 3,
							id: 'EPSRSW_Search_SecName',
							name:'Person_SecName',
							fieldLabel: lang['otchestvo'],
							listeners: {
								'keydown': wnd.onKeyDown.createDelegate(this)
							}
						}]
					},{
						layout: 'form',
						labelWidth: 30,
						items:
						[{
							xtype: 'swdatefield',
							format: 'd.m.Y',
                            tabIndex: TABINDEX_EPSRSW + 4,
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name:'Person_Birthday',
							id: 'EPSRSW_Search_BirthDay',
							fieldLabel: lang['dr'],
							listeners:
							{
								'keydown': wnd.onKeyDown.createDelegate(this)
							}
						}]
					}]
				},{
					layout: 'column',
					items:
					[{
						layout: 'form',
						items:
						[{
                            editable : true,
                            forceSelection: true,
							
							hiddenName: 'RecLpu_id',
                            fieldLabel: lang['mo_zapisi'],
                            allowBlank: true,
                            lastQuery : '',
                            listeners: {
								'keypress': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
                            },
                            listWidth : 500,
                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate /* Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y")"*/ + ")" : values.Lpu_Nick ]}&nbsp;',
                                '</div></tpl>'
                            ),
                            typeAhead: true,
							width: 340,
                            xtype : 'swlpulocalcombo'
						}]
					},{
						layout: 'form',
						labelWidth: 130,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 170,
                            tabIndex: TABINDEX_EPSRSW + 5,
							id: 'EPSRSW_EvnDirection_Num',
							name:'EvnDirection_Num',
                            maskRe: /\d/,
							fieldLabel: lang['nomer_napravleniya'],
							listeners:
							{
								'keypress': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					}]
				},{
					layout: 'column',
					items:
					[{
						layout: 'form',
						items: [{
							xtype: 'swpmusercombo',
							width: 170,
							allowBlank: true,
							tabIndex: TABINDEX_EPSRSW + 6,
							hiddenName:'pmUser_id',
							fieldLabel: lang['polzovatel'],
							listWidth: 300,
							listeners: {
								'keypress': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER) {
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this)
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 220,
						items:
						[{
							xtype:'checkbox',
							checked: false,
							tabIndex: TABINDEX_EPSRSW + 7,
							fieldLabel:lang['poisk_po_polzovatelyam_call-tsentra'],
							handler: function(value) {
								wnd.setUserListFilter();
							},
							name: 'onlyCallCenterUsers'
						}]
					}, {
						layout: 'form',
						labelWidth: 130,
						items:
						[{
							editable : true,
							forceSelection: true,
							hiddenName: 'UserLpu_id',
							fieldLabel: lang['mo_polzovatelya'],
							allowBlank: true,
							lastQuery : '',
							listeners: {
								'keypress': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										this.doSearch();
									}
								}.createDelegate(this),
								'change': function(combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('Lpu_id') == newValue);
									});
									combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
								},
								'select': function(combo, record, index) {
									wnd.setUserListFilter();
								}
							},
							listWidth : 500,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{[(values.Lpu_EndDate && values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыто "+ values.Lpu_EndDate /* Ext.util.Format.date(Date.parseDate(values.Lpu_EndDate.slice(0,10), "Y-m-d"), "d.m.Y")"*/ + ")" : values.Lpu_Nick ]}&nbsp;',
								'</div></tpl>'
							),
							typeAhead: true,
							width: 250,
							xtype : 'swlpulocalcombo'
						}]
					}]
				},{
					layout: 'column',
					items:
					[{
						layout: 'form',
						items:
						[{
                            xtype: 'swdatefield',
                            tabIndex: TABINDEX_EPSRSW + 8,
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                            width: 100,
                            listeners: {
                                render: function(e) {
                                  Ext.QuickTips.register({
                                    target: e.getEl(),
                                    text: lang['period_dat_zapisi']
                                  });
                                },
								'keydown': wnd.onKeyDown.createDelegate(this)
                            },
                            name: 'RecordDate_from',
                            fieldLabel: lang['data_zapisi_s']
                        }]
					},{
						layout: 'form',
						labelWidth: 30,
						items:
						[{
                            xtype: 'swdatefield',
                            tabIndex: TABINDEX_EPSRSW + 9,
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                            width: 100,
                            listeners: {
                                render: function(e) {
                                  Ext.QuickTips.register({
                                    target: e.getEl(),
                                    text: lang['period_dat_zapisi']
                                  });
                                },
								'keydown': wnd.onKeyDown.createDelegate(this)
                            },
                            name: 'RecordDate_to',
                            fieldLabel: lang['po']
                        }]
					},{
						layout: 'form',
						labelWidth: 130,
						items:
						[{
                            xtype: 'swdatefield',
                            tabIndex: TABINDEX_EPSRSW + 10,
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                            width: 100,
                            listeners: {
                                render: function(e) {
                                  Ext.QuickTips.register({
                                    target: e.getEl(),
                                    text: lang['period_dat_posescheniy']
                                  });
                                },
								'keydown': wnd.onKeyDown.createDelegate(this)
                            },
                            name: 'VizitDate_from',
                            fieldLabel: lang['data_posescheniya_s']
                        }]
					},{
						layout: 'form',
						labelWidth: 30,
						items:
						[{
                            xtype: 'swdatefield',
                            tabIndex: TABINDEX_EPSRSW + 11,
                            plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                            width: 100,
                            listeners: {
                                render: function(e) {
                                  Ext.QuickTips.register({
                                    target: e.getEl(),
                                    text: lang['period_dat_posescheniy']
                                  });
                                },
								'keydown': wnd.onKeyDown.createDelegate(this)
                            },
                            name: 'VizitDate_to',
                            fieldLabel: lang['po']
                        }]
					}]
				},{
					layout: 'column',
                    style:'margin: 10px 0;',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: wnd.id + 'BtnSearch',
                            tabIndex: TABINDEX_EPSRSW + 12,
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								wnd.doSearch();
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: wnd.id + 'BtnClear',
                            tabIndex: TABINDEX_EPSRSW + 13,
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								wnd.resetFilter();
                                wnd.mainGrid.getStore().removeAll();
							}
						}]
					}]
				}]
			}]
		});
	
		var Actions =
		[
			{name:'record_on_time', text:lang['zapis_na_vremya'], tooltip: lang['zapis_na_vremya'], disabled: true, iconCls : 'add16', handler: function() { this.openPersonRecordWindow(); }.createDelegate(this)},
			{name:'cancel', text:lang['otmena'], tooltip: lang['otmena_zapisi'], disabled: true, iconCls : 'cancel16', handler: function() { this.cancelEvnDirection(); }.createDelegate(this)},
			{name:'print_direction', text:lang['pechat_napravleniya'], tooltip: lang['pechat_napravleniya'], disabled: true, iconCls : 'print16', handler: function() { this.printEvnDirection(); }.createDelegate(this)},
			{name:'open_direction', text:lang['otkryit_napravlenie'], disabled: true, tooltip: lang['otkryit_napravlenie'], iconCls : 'reception-edit16', handler: function() { this.openEvnDirectionEditWindow({action: 'edit'}); }.createDelegate(this)}
		];

		this.gridActions = [];
		
		for (i=0; i < Actions.length; i++) {
			this.gridActions[Actions[i]['name']] = new Ext.Action(Actions[i]);
		}
		delete(Actions);

		// Создание popup - меню и кнопок в ToolBar. Формирование коллекции акшенов
		this.ViewContextMenu = new Ext.menu.Menu();
		this.toolItems = new Ext.util.MixedCollection(true);
		var i = 0;
		for (key in this.gridActions) {
			if (key.inlist(['record_on_time','cancel','print_direction','open_direction'])) {
				//evnsection
				this.toolItems.add(this.gridActions[key],key);
				if ((i == 1) || (i == 8) || (i == 9))
					this.ViewContextMenu.add('-');
				this.ViewContextMenu.add(this.gridActions[key]);
				i++;
			}
		}
		
		this.gridToolbar = new Ext.Toolbar(
		{
			id: 'EPSRSW_Toolbar',
			items:
			[
				this.gridActions.record_on_time,
				{ xtype : "tbseparator" },
				this.gridActions.cancel,
				{ xtype : "tbseparator" },
				this.gridActions.print_direction,
				{ xtype : "tbseparator" },
				this.gridActions.open_direction,
				{ xtype : "tbfill"},
				{ xtype : "tbseparator" },
				{
                    text: '0 / 0',
                    xtype: 'tbtext'
                }
			]
		});			

		this.reader = new Ext.data.JsonReader(
		{id: 'keyNote'},
		[
            {name:'PMUser_id'},
            {name:'Person_id'},
            {name:'RecType_Name'},
            {name:'Lpu_Nick'},
            {name:'LpuBuilding_Name'},
            {name:'Address_Address'},
            {name:'LpuSectionProfile_Name'},
            {name:'MedUnit_Name'},
            {name: 'AttachLpu_Name'},
            {name: 'DirType_Code'},
            {name: 'Person_isDead'},
            {name: 'Server_id'},
            {name:'Person_FIO'},
            {name:'Person_FirName'},
            {name:'Person_SecName'},
            {name:'Person_SurName'},
            {name:'Person_Phone'},
            {name:'PMUser_Name'},
            {name:'EvnDirection_id'},
            {name:'EvnDirection_Num'},
            {name:'PersonEvn_id'},
		    {name:'Person_BirthDay',type: 'date',dateFormat: 'd.m.Y'},
            {name:'RecordDate',type: 'date',dateFormat: 'd.m.Y H:i'},
            {name:'VizitDate',type: 'date',dateFormat: 'd.m.Y H:i'}
		]);

		this.gridStore = new Ext.data.GroupingStore({
			reader: this.reader,
			autoLoad: false,
			url: '/?c=EvnDirection&m=loadTimetableRecords',
			sortInfo: {
				field: 'Person_FIO',
				direction: 'DESC'
			},
			groupField: 'RecType_Name',
			listeners: {
				load: function(store, record, options) {
					callback: {
						var count = store.getCount();
						var form = Ext.getCmp('swTimetableRecordsSearchWindow');
						var grid = form.getGrid();
						if (count>0)
						{
							// Если ставится фокус при первом чтении или количество чтений больше 0
							if (!grid.getTopToolbar().hidden)
							{
								grid.getTopToolbar().items.last().el.innerHTML = '0 / '+count;
							}
						}
						else
						{
							grid.focus();
						}
					}
				},
				clear: function()
				{
					/*var form = Ext.getCmp('swTimetableRecordsSearchWindow');
					form.gridActions.open_emk.setDisabled(true);*/
				},
				beforeload: function()
				{

				}
			}
		});

		this.mainGrid = new Ext.grid.GridPanel({
			region: 'center',
			layout: 'fit',
			frame: true,
			tbar: this.gridToolbar,
			store: this.gridStore,
			loadMask: true,
			stripeRows: true,
			autoExpandColumn: 'autoexpand',
			columns:
			[
				{id:'keyNote', hidden: true, hideable: false, dataIndex: 'keyNote'},
				{hidden: true, hideable: false, dataIndex: 'AttachLpu_Name'},
				{hidden: true, hideable: false, dataIndex: 'Person_isDead'},
				{hidden: true, hideable: false, dataIndex: 'Server_id'},
				{hidden: true, hideable: false, dataIndex: 'Person_id'},
				{hidden: true, hideable: false, dataIndex: 'Person_FirName'},
				{hidden: true, hideable: false, dataIndex: 'Person_SecName'},
				{hidden: true, hideable: false, dataIndex: 'Person_SurName'},
				{hidden: true, hideable: false, dataIndex: 'PersonEvn_id'},
				{hidden: true, hideable: false, dataIndex: 'EvnDirection_id'},
				{hidden: true, hideable: false, dataIndex: 'DirType_Code'},
				{hidden: true, hideable: false, header: "Логин пользователя", dataIndex:'PMUser_id'},
				{hidden: true, hideable: false, header: "Тип записи", dataIndex:'RecType_Name'},
				{header: "МО Записи", width: 40, sortable: true, dataIndex:'Lpu_Nick'},
				{header: lang['podrazdelenie'], width: 50, sortable: true, dataIndex:'LpuBuilding_Name'},
				{header: lang['adres'], width: 50, sortable: true, dataIndex:'Address_Address'},
				{header: lang['profil'], width: 50, sortable: true, dataIndex:'LpuSectionProfile_Name'},
				{header: lang['vrach_slujba_otdelenie'], width: 50, sortable: true, dataIndex:'MedUnit_Name'},
				{header: "ФИО пациента", width: 50, sortable: true, dataIndex:'Person_FIO'},
				{header: "Дата рождения пациента", width: 50, sortable: true, dataIndex:'Person_BirthDay', renderer: Ext.util.Format.dateRenderer('d.m.Y'), css: 'color: #000079;'},
				{header: "Телефон пациента", width: 50, sortable: true, dataIndex:'Person_Phone'},
				{header: "Дата записи", width: 50, sortable: true, dataIndex: 'RecordDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), css: 'color: #000079;'},
				{header: "Дата посещения", width: 50, sortable: true, dataIndex: 'VizitDate', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), css: 'color: #000079;'},
				{header: lang['polzovatel'], width: 40, sortable: true, dataIndex:'PMUser_Name'},
				{header: "Номер направления", width: 50, sortable: true, dataIndex:'EvnDirection_Num'}
			],
			view: new Ext.grid.GroupingView(
			{
				forceFit: true,
				enableGroupingMenu : false,
				enableNoGroups : false,
				hideGroupedColumn : true, 
				groupTextTpl: '<span style="color: rgb(113,0,0); font-size: 14px;">{[values.rs[0].data.RecType_Name]}</span> ({[values.rs.length]} {[values.rs.length == 1 ? "запись" : (values.rs.length.inlist([2,3,4]) ? "записи" : "записей")]})'
			}),
			lastLoadGridDate: null,
			auto_refresh: null,
			loadStore: function(params,callback)
			{
				if (!this.params)
					this.params = null;
				if (params)
				{
					this.params = params;
				}
				if(typeof callback != 'function')
				{
					callback =  Ext.emptyFn;
				}
				this.clearStore();
				this.getStore().load({params: this.params, callback: callback});
			},
			clearStore: function()
			{
				if (this.getEl())
				{
					if (this.getTopToolbar().items.last())
						this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
					this.getStore().removeAll();
				}
			},
			focus: function () 
			{
				if (this.getStore().getCount()>0)
				{
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
				}
			},
			sm: new Ext.grid.RowSelectionModel(
			{
				singleSelect: true,
				listeners:
				{
					'rowselect': function(sm, rowIdx, record)
					{
						if ( !record ) {
							return false;
						}

						var form = Ext.getCmp('swTimetableRecordsSearchWindow');
						var count = this.grid.getStore().getCount();
						var rowNum = rowIdx + 1;
						if (!this.grid.getTopToolbar().hidden)
						{
							this.grid.getTopToolbar().items.last().el.innerHTML = rowNum+' / '+count;
						}

						if (!form.gridActions.record_on_time.initialConfig.initialDisabled)
							form.gridActions.record_on_time.setDisabled(record.get('RecType_Name') != lang['ochered']);

						if (!form.gridActions.cancel.initialConfig.initialDisabled)
							form.gridActions.cancel.setDisabled(!record.get('RecType_Name').inlist([lang['ochered'], lang['zapis'], lang['zapis_na_koyku']]));

						if (!form.gridActions.print_direction.initialConfig.initialDisabled)
							form.gridActions.print_direction.setDisabled(!(record.get('RecType_Name') == lang['po_napravleniyu'] && !Ext.isEmpty(record.get('EvnDirection_id'))));

						if (!form.gridActions.open_direction.initialConfig.initialDisabled)
							form.gridActions.open_direction.setDisabled(!(record.get('RecType_Name') == lang['po_napravleniyu'] && !Ext.isEmpty(record.get('EvnDirection_id'))));
					}
				}
			})
		});
		
		// Добавляем созданное popup-меню к гриду
		
		this.mainGrid.addListener('rowcontextmenu', onMessageContextMenu,this);
		this.mainGrid.on('rowcontextmenu', function(grid, rowIndex, event)
		{
			// На правый клик переходим на выделяемую запись
			grid.getSelectionModel().selectRow(rowIndex);
		});
		// Функция вывода меню по клику правой клавиши
		function onMessageContextMenu(grid, rowIndex, e)
		{
			e.stopEvent();
			var coords = e.getXY();
			this.ViewContextMenu.showAt([coords[0], coords[1]]);
		}
		// Даблклик
		this.mainGrid.on('celldblclick', function(grid, row, col, object)
		{
			var win = Ext.getCmp('swTimetableRecordsSearchWindow');
			var rec = win.getSelectedRecord();
			if (!rec)
			{
				return false;
			}
			if (rec.get('EvnDirection_id')) {
				win.openEvnDirectionEditWindow({action: 'edit'});
			} else if (!Ext.isEmpty(rec.get('Person_id')) && rec.get('RecType_Name') == lang['ochered']) {
				win.openPersonRecordWindow();
			}
		});

		var form = this;
		var actions_list = ['action_selfTreatment','action_logNotice', 'action_StacSvid','action_reports'];
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}

		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.FilterPanel,
				{
					layout: 'border',
					region: 'center',
					id: 'EPSRSW_SchedulePanel',
					items:
					[
						this.mainGrid
					]
				}
				
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		sw.Promed.swTimetableRecordsSearchWindow.superclass.initComponent.apply(this, arguments);
		
	}
});
