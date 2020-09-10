/**
* swTimetableGrafModerationWindow - Модерация интернет-записи
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin
* @version      
* @comment      Префикс для id компонентов TTMGW (TimetableGrafModerationWindow)
*
*/
sw.Promed.swTimetableGrafModerationWindow = Ext.extend(sw.Promed.BaseForm, {codeRefresh: true,
	id: 'swTimetableGrafModerationWindow',
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	getButtonSearch: function() {
		return Ext.getCmp(this.id + 'BtnSearch');
	},
	doReset: function() {
		
		var base_form = this.FilterPanel.getForm();
		base_form.reset();
		base_form.findField('ModerateType_id').setValue(0);

		if(!isSuperAdmin() && !isCallCenterAdmin()){
			base_form.findField('TTGLpu_id').setValue(getGlobalOptions().lpu_id);
		}
	},
	doSearch: function(params) {
		
		var base_form = this.FilterPanel.getForm();
		
		if (typeof params != 'object') {
			params = {};
		}
		if ( !params.firstLoad && this.findById('TimetableGrafModerationForm').isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var grid = this.RootViewFrame.getGrid();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет поиск..."});
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('TimetableGrafModerationForm'));

		post.limit = 100;
		post.start = 0;
		
		//log(post);

		if ( base_form.isValid() ) {
			this.RootViewFrame.ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			grid.getStore().load({
				callback: function(records, options, success) {
					loadMask.hide();
				},
				params: post
			});
		}
		
	},
	height: 550,
	openWindow: function(action) {
		/*
		это копипаст левого кода
		if (!action || !action.toString().inlist(['view'])) {
			return false;
		}

		if (action == 'add' && getWnd('swEvnOnkoNotifyViewWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_uje_otkryito']);
			return false;
		}

		var grid = this.RootViewFrame.getGrid();
		var params = new Object();
		
		params.action = action;
		params.callback = function(data) {
			
		}
		

		var selected_record = grid.getSelectionModel().getSelected();
		if (!selected_record) {
			return false;
		}

		params.formParams = selected_record.data;
		params.EvnOnkoNotify_id = selected_record.data.EvnOnkoNotify_id;

		getWnd('swEvnOnkoNotifyViewWindow').show(params);
		*/
	},
	doFilterLpuByAddress: function() {
		var base_form = this.FilterPanel.getForm();
		var params = base_form.getValues();

		var lpu_combo = base_form.findField('TTGLpu_id');
		lpu_combo.getStore().clearFilter();

		if (!params.KLCity_id && !params.KLTown_id) { return; }

		Ext.Ajax.request({
			url: '/?c=LpuStructure&m=getLpuListByAddress',
			params: {
				KLCity_id: params.KLCity_id,
				KLTown_id: params.KLTown_id
			},
			success: function(response) {
				var result = Ext.util.JSON.decode(response.responseText);
				var lpu_id_list = [];

				for (var i=0; i<result.length; i++) {
					lpu_id_list.push(result[i].Lpu_id);
				}
				lpu_combo.getStore().filterBy(function(rec){
					return (rec.get('Lpu_id').inlist(lpu_id_list));
				});
			}
		});
	},
	initComponent: function() {
		
		var curWnd = this;

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			id: 'TimetableGrafModerationForm',
			owner: curWnd,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					curWnd.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			labelWidth: 120,
			filter: {
				title: lang['filtryi'],
				layout: 'form',
				items: [{
					name: 'SearchFormType',
					value: 'WorkPlacePolkaReg',
					xtype: 'hidden'
				}, {
					name: 'AddressStateType_id',
					value: 1,
					xtype: 'hidden'
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: lang['familiya'],
							name: 'Person_Surname',
							width: 200,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: lang['imya'],
							name: 'Person_Firname',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 130,
						items: [{
							fieldLabel: lang['otchestvo'],
							name: 'Person_Secname',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['telefon'],
							name: 'Person_Phone',
							width: 120,
							xtype: 'textfield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowBlank: true,
							codeField: 'ModerateType_Code',
							displayField: 'ModerateType_Name',
							editable: false,
							fieldLabel: lang['pokazyivat_zapisi'],
							hiddenName: 'ModerateType_id',
							hideEmptyRow: true,
							listeners: {
								'blur': function(combo)  {
									if ( combo.value == '' )
										combo.setValue(0);
								}
							},
							store: new Ext.data.SimpleStore({
								autoLoad: true,
								data: [
									[ 0, 0, lang['vse'] ],
									[ 1, 1, lang['odobrennyie'] ],
									[ 2, 2, lang['neodobrennyie'] ],
									[ 3, 3, lang['neodobrennyie_i_preduprejdennyie'] ],
									[ 4, 4, lang['neodobrennyie_i_nepreduprejdennyie'] ]
								],
								fields: [
									{ name: 'ModerateType_id', type: 'int'},
									{ name: 'ModerateType_Code', type: 'int'},
									{ name: 'ModerateType_Name', type: 'string'}
								],
								key: 'ModerateType_id',
								sortInfo: { field: 'ModerateType_Code' }
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'<font color="red">{ModerateType_Code}</font>&nbsp;{ModerateType_Name}',
								'</div></tpl>'
							),
							value: 0,
							valueField: 'ModerateType_id',
							width: 200,
							xtype: 'swbaselocalcombo'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						width: 215,
						items: [{
							fieldLabel: lang['data_zapisi'],
							format: 'd.m.Y',
							name: 'StartDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}]
					}, {
						layout: 'form',
						labelWidth: 140,
						width: 260,
						items: [{
							fieldLabel: lang['na_kakuyu_datu_zapisan'],
							format: 'd.m.Y',
							name: 'ZapDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							areaLevel: 3,
							enableKeyEvents: true,
							fieldLabel: lang['gorod'],
							hiddenName: 'KLCity_id',
							valueField: 'KLArea_id',
							displayField: 'KLArea_Name',
							mode: 'local',
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' },
									{ name: 'KLAreaLevel_id', type: 'int' },
									{ name: 'KLArea_pid', type: 'int' }
								],
								key: 'KLArea_id',
								sortInfo: {
									field: 'KLArea_Name'
								},
								url: '/?c=Address&m=getKLAreaList'
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							listeners: {
								'select': function(combo) {
									var base_form = this.FilterPanel.getForm();
									var town_combo = base_form.findField('KLTown_id');

									town_combo.clearValue();
									town_combo.fireEvent('select', town_combo);

									var value = combo.getValue();
									if (Ext.isEmpty(value)) {
										value = getGlobalOptions().region ? getGlobalOptions().region.number : null;
									}
									town_combo.getStore().removeAll();
									town_combo.getStore().load({
										params: {KLArea_pid: value},
										callback: function() {
											if (town_combo.getStore().getCount() > 0) {
												town_combo.enable();
											} else {
												town_combo.disable();
											}
										}.createDelegate(this)
									});
								}.createDelegate(this)
							},
							width: 200,
							xtype: 'swbaseremotecombosingletrigger'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							areaLevel: 4,
							enableKeyEvents: true,
							fieldLabel: lang['nas_punkt'],
							hiddenName: 'KLTown_id',
							valueField: 'KLArea_id',
							displayField: 'KLArea_Name',
							mode: 'local',
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'KLArea_id', type: 'int' },
									{ name: 'KLArea_Name', type: 'string' },
									{ name: 'KLAreaLevel_id', type: 'int' },
									{ name: 'KLArea_pid', type: 'int' }
								],
								key: 'KLArea_id',
								url: '/?c=Address&m=getKLAreaList'
							}),
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{KLArea_Name}',
								'</div></tpl>'
							),
							triggerAction: 'all',
							listeners: {
								'select': function(combo) {
									var base_form = this.FilterPanel.getForm();
									var lpu_combo = base_form.findField('TTGLpu_id');
									var old_lpu_id = lpu_combo.getValue();
									if(lpu_combo.enabled){
										lpu_combo.clearValue();
										lpu_combo.fireEvent('change', lpu_combo, old_lpu_id, lpu_combo.getValue());
										this.doFilterLpuByAddress();
									}
								}.createDelegate(this)
							},
							width: 180,
							xtype: 'swbaseremotecombosingletrigger'
						}]
					}, {
						layout: 'form',
						labelWidth: 40,
						items: [{
							id: 'swlpucombo_123',
							lastQuery: '',
							width: 200,
							fieldLabel: lang['mo'],
							hiddenName: 'TTGLpu_id',
							xtype: 'swlpucombo',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.FilterPanel.getForm();
									base_form.findField('MedPersonal_id').clearValue();
									if (oldValue != newValue)  {
										base_form.findField('MedPersonal_id').getStore().load({
											params: {Lpu_id: combo.getValue()}
										});
									}
								}.createDelegate(this)
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 60,
						items: [{
							width: 300,
							allowBlank: true,
							anchor: false,
							editable: true,
							lastQuery: '',
							fieldLabel: lang['vrach'],
							name: 'MedPersonal_id',
							xtype: 'swmedpersonalcombo'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: curWnd.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								curWnd.doSearch();
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: curWnd.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								curWnd.doReset();
							}
						}]
					}]
				}]
			}
		});		
		
		this.RootViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, iconCls: 'x-btn-text', icon: 'img/icons/ok16.png', text: lang['odobrit'], tooltip: lang['odobrit'], handler: function() {
					var sm = this.RootViewFrame.getGrid().getSelectionModel();

					if (!sm.hasSelection()) {
						return false;
					}

					var TimetableGraf_ids = [];

					sm.each(function(rec) {
						TimetableGraf_ids.push(rec.get('TimetableGraf_id'));
					});

					var params = new Object();
					params.TimetableGraf_ids = Ext.util.JSON.encode(TimetableGraf_ids);

					var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
					loadMask.show();
					Ext.Ajax.request({
						failure:function (response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						},
						params: params,
						success:function (response, options) {
							loadMask.hide();
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.success == false) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else if (Ext.isEmpty(response_obj.error_list)) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									msg: lang['vyibrannyie_patsientyi_preduprejdenyi'],
									title: lang['zapis_patsientov_uspeshno_odobrena']
								});
							} else {
								curWnd.proceedErrorList(response_obj.error_list, lang['zapis_patsientov_uspeshno_odobrena']);
								curWnd.RootViewFrame.getGrid().getStore().reload();
							}
							/*else if (( selected_record.get('UserNotify_AcceptIsEmail') == 1 ) || ( selected_record.get('UserNotify_AcceptIsSMS') == 1 )) {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									msg: lang['patsient_preduprejden'],
									title: lang['zapis_patsienta_uspeshno_odobrena']
								});
								this.RootViewFrame.getGrid().getStore().reload();
							}
							else {
								sw.swMsg.show({
									buttons: Ext.Msg.OK,
									msg: lang['patsient_ne_preduprejden_polzovatel_ne_ukazal_sposob_polucheniya_uvedomleniy'],
									title: lang['zapis_patsienta_uspeshno_odobrena']
								});
								this.RootViewFrame.getGrid().getStore().reload();
							}*/
						}.createDelegate(this),
						url:'/?c=TimetableGraf&m=acceptMultiRecTTGModeration'
					});
				}.createDelegate(this)},
				{name: 'action_edit', disabled: true, text: lang['preduprejden'], tooltip: lang['preduprejden'], handler: function() {
					var sm = this.RootViewFrame.getGrid().getSelectionModel();

					if (!sm.hasSelection()) {
						return false;
					}

					var TimetableGraf_ids = [];

					sm.each(function(rec) {
						TimetableGraf_ids.push(rec.get('TimetableGraf_id'));
					});

					getWnd('swTimetableGrafSetConfirmWindow').show({
						TimetableGraf_ids: TimetableGraf_ids,
						callback: function() {
							curWnd.RootViewFrame.getGrid().getStore().reload();
						}.createDelegate(this),
						proceedErrorList: function(error_list) {
							curWnd.proceedErrorList(error_list, lang['oshibki_zapisi']);
						}.createDelegate(this)
					});
				}.createDelegate(this)},
				{name: 'action_view', hidden: true},
				{name: 'action_delete', handler: function() { 
					var selected_record = this.RootViewFrame.getGrid().getSelectionModel().getSelected();
					if (!selected_record) {
						return false;
					}
					getWnd('swTimetableGrafSetFailWindow').show({
						TimetableGraf_id: selected_record.data.TimetableGraf_id, 
						callback: function() {
							this.RootViewFrame.getGrid().getStore().reload();
						}.createDelegate(this)
					});
				}.createDelegate(this)},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=TimetableGraf&m=getTTGForModeration',
			id: 'TTMGW_TimetableGrafModerationSearchGrid',
			object: 'TimetableGraf',
			pageSize: 100,
			paging: true,
			region: 'center',
			root: 'data',
			useEmptyRecord: false,
			selectionModel: 'multiselect',
			stringfields: [
				{name: 'TimetableGraf_id', type: 'int', header: 'ID', key: true},
				{name: 'TimetableGraf_updDT', header: lang['vremya_zapisi'], type: 'datetimesec', width: 120},	
				{name: 'TimetableGraf_begTime', header: lang['vremya_birki'], type: 'datetimesec', width: 120},
				{name: 'Lpu_id', hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo'], width: 150},
				{name: 'LpuUnit_Name', type: 'string', header: lang['podrazdelenie'], width: 200},
				{name: 'LpuUnit_Address', type: 'string', header: lang['adres_mo'], width: 200},
				{name: 'MedPersonFullName', type: 'string', header: lang['vrach'], width: 200},
				{name: 'LpuSectionProfile_Name', type: 'string', header: lang['spetsialnost'], width: 200},
				{name: 'MedLpuRegion_Name', type: 'string', header: lang['uchastok_vracha'], width: 100},
				{name: 'LpuRegion_Name', type: 'string', header: lang['uchastok_prikrepleniya'], width: 100},
				{name: 'PrikLpu_id', hidden: true},
				{name: 'PrikLpu_Nick', type: 'string', header: lang['mo_prikrepleniya'], width: 150},
				{name: 'LpuRegion_Name_Pr', type: 'string', header: lang['uchastok_po_adresu'], width: 100},
				{name: 'PersonFullName', type: 'string', header: lang['patsient'], width: 200},
				{name: 'Person_BirthDay', type: 'date', format: 'd.m.Y', header: lang['data_rojdeniya'], width: 120},
				{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkcolumn', width: 30},
				{name: 'PAddress_Address', type: 'string', header: lang['adres_projivaniya'], width: 320},
				{name: 'UAddress_Address', type: 'string', header: lang['adres_registratsii'], width: 320},
				{name: 'Person_Phone', type: 'string', header: lang['telefon'], width: 200},
				{name: 'MedstaffFact_Descr', type: 'string', header: lang['primechanie_vracha'], width: 200},
				{name: 'Login', type: 'string', header: lang['akkaunt'], width: 200},
				{name: 'TimetableGraf_IsModerated', hidden: true},
				{name: 'UserNotify_AcceptIsSMS', hidden: true},
				{name: 'UserNotify_AcceptIsEmail', hidden: true}
			],
			toolbar: true,
			totalProperty: 'totalCount', 
			onBeforeLoadData: function() {
				this.RootViewFrame.getAction('action_add').setDisabled( false );
				this.getButtonSearch().disable();
			}.createDelegate(this),
			onLoadData: function() {
				this.getButtonSearch().enable();
			}.createDelegate(this),
			onRowSelectionChange: function(sm) {
				var disableAdd = false;
				var disableEdit = false;
				var disableDelete = false;

				if (sm.getCount() > 0) {
					sm.each(function(rec) {
						if (!rec.get('TimetableGraf_id') || rec.get('TimetableGraf_IsModerated') == 1) {
							disableAdd = true;
						} else
						if (!rec.get('TimetableGraf_id') || rec.get('TimetableGraf_IsModerated') == 2) {
							disableEdit = true;
						}
					});
					if (sm.getCount() != 1) {
						disableDelete = true;
					}
				} else {
					disableAdd = true;
					disableEdit = true;
					disableDelete = true;
				}

				this.getAction('action_add').setDisabled(disableAdd);
				this.getAction('action_edit').setDisabled(disableEdit);
				this.getAction('action_delete').setDisabled(disableDelete);
			},
			onRowSelect: function(sm,index,record) {
				record.set('set', 1);
				this.onRowSelectionChange(sm);
			},
			onRowDeSelect: function(sm,index,record) {
				record.set('set', 0);
				this.onRowSelectionChange(sm);
			},
			onDblClick: function() {
				if (!this.RootViewFrame.getAction('action_add').isDisabled()) {
					this.RootViewFrame.getAction('action_add').execute();
				}
			}.createDelegate(this)
		});

		this.RootViewFrame.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('set') == 0 || row.get('set') == undefined) {
					if ( row.get('Lpu_id') != row.get('PrikLpu_id') ) {
						cls = cls + 'x-grid-rowbackred';
					} else if ( row.get('TimetableGraf_IsModerated') == 1 ) {
						cls = cls + 'x-grid-rowbackgreen';
					} else if ( row.get('TimetableGraf_IsModerated') == 2 ) {
						cls = cls + 'x-grid-rowbackblue';
					} else if ( Ext.isEmpty(row.get('TimetableGraf_IsModerated')) ){
						cls = cls + 'x-grid-rowback';
					} else {
						cls = 'x-grid-panel';
					}
				}

				return cls;
			}
		});		

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_TTMGW + 124,
				text: BTN_FRMCLOSE
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('TimetableGrafModerationForm');
				}
				return this.filterForm;
			},
			menuPrintForm: null,
			items: [this.FilterPanel,this.RootViewFrame]
		});
		
		sw.Promed.swTimetableGrafModerationWindow.superclass.initComponent.apply(this, arguments);
		
		
	},
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		}
		/*'maximize': function(win) {
			win.getFilterForm().doLayout();
		},
		'restore': function(win) {
			win.getFilterForm().doLayout();
		},
        'resize': function (win, nW, nH, oW, oH) {
			win.getFilterForm().setWidth(nW - 5);
		}*/
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	plain: true,
	proceedErrorList: function(error_list, title) {
		if (Ext.isEmpty(error_list)) {
			return;
		}
		var grid = this.RootViewFrame.getGrid();
		var msg = '';
		for(var i=0;i<error_list.length;i++) {
			var index = grid.getStore().findBy(function(rec) { return rec.get('TimetableGraf_id') == error_list[i]['TimetableGraf_id']; });
			var record = grid.getStore().getAt(index);
			var time = Ext.util.Format.date(record.get('TimetableGraf_begTime'), 'd.m.Y H:i:s');
			msg += 'Пациент '+record.get('PersonFullName')+', время '+time+':<br>';
			msg += error_list[i]['Error_Msg']+'<br/>';
		}
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			msg: msg,
			title: title
		});
	},
	resizable: true,
	show: function() {
		sw.Promed.swTimetableGrafModerationWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		this.FilterPanel.fieldSet.expand();
		this.RootViewFrame.addActions({
			name:"action_legend",
			text:lang['legenda'],
			tooltip: lang['legenda'],
			menu:new Ext.menu.Menu({
				width: 300,
				items:[
					{
						//id: -1,
						text: lang['neobrabotannyie_moderatorom_zapisi'],
						iconCls: 'no-icon',
						style: "background-color:#FFFFFF"
					},
					{
						//id: -2,
						text: lang['odobrennyie_zapisi'],
						iconCls: 'no-icon',
						style: "background-color:#CFC"
					},
					{
						//id: -3,
						text: lang['neobrabotannyie_moderatorom_zapisi_i_mo_ne_sootvetstvuet_mo_prikrepleniya_patsienta'],
						iconCls: 'no-icon',
						style: "background-color:#FCC"
					},
					{
						//id: -4,
						text: lang['zapisi_po_kotoryim_patsient_preduprejden'],
						iconCls: 'no-icon',
						style: "background-color:#c0ceff"
					}
				]
			})
		});

		if (arguments && arguments[0] && arguments[0].userMedStaffFact)
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		} else {
			if (sw.Promed.MedStaffFactByUser.last)
			{
				this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
			}
			else
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: arguments[0].ARMType,
					onSelect: function(data) {
						this.userMedStaffFact = data;
					}.createDelegate(this)
				});
			}
		}
		
		base_form.findField('ModerateType_id').setValue(2);

		base_form.findField('MedPersonal_id').getStore().load();

		var city_combo = base_form.findField('KLCity_id');
		var town_combo = base_form.findField('KLTown_id');

		var region_number = getGlobalOptions().region ? getGlobalOptions().region.number : null;

		city_combo.getStore().baseParams.KLAreaLevel_id = city_combo.areaLevel;
		city_combo.getStore().load({params: {KLArea_pid: region_number}});

		town_combo.getStore().baseParams.KLAreaLevel_id = town_combo.areaLevel;
		town_combo.getStore().load({params: {KLArea_pid: region_number}});

		if(!isSuperAdmin() && !isCallCenterAdmin()){
			var lpu_combo = base_form.findField('TTGLpu_id');
			lpu_combo.fireEvent('change', lpu_combo, getGlobalOptions().lpu_id,null);
			lpu_combo.setValue(getGlobalOptions().lpu_id);
			lpu_combo.disable();
		}
		this.doLayout();
	},
	title: lang['moderatsiya_internet-zapisi'],
	width: 800
});