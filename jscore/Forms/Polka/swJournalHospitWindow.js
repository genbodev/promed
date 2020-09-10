/**
* swJournalHospitWindow - форма журнала госпитализаций
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @class        sw.Promed.swJournalHospitWindow
* @extends      sw.Promed.BaseForm
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      06.10.2010
* @comment      Префикс для id компонентов EJHW. 
*/

/*NO PARSE JSON*/
sw.Promed.swJournalHospitWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'EJHW',
	maximized: true,
	//autoHeight: true,
	//autoWidth: true,
	border: false,
	buttonAlign: 'right',
	closable: true,
	closeAction: 'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: langs('Журнал госпитализации'),
	//объект с параметрами рабочего места, с которыми была открыта форма АРМа
	userMedStaffFact: null,
	setEvnPSPrehospAcceptRefuse: function(flag) {
		var record = this.grid.getGrid().getSelectionModel().getSelected();

		if ( !record ) {
			return false;
		}
		else if ( flag == true && parseInt(record.get('EvnPS_IsPrehospAcceptRefuse')) == 2 ) {
			return false;
		}
		else if ( flag == false && (!record.get('EvnPS_IsPrehospAcceptRefuse') || parseInt(record.get('EvnPS_IsPrehospAcceptRefuse')) == 1) ) {
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Изменение признака 'Отказ в подтверждении госпитализации'..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(opt, success, response) {
				loadMask.hide();

				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg);
					}
					else {
						this.grid.getGrid().getStore().each(function(rec) {
							if ( rec.get('EvnPS_id') == record.get('EvnPS_id') ) {
								rec.set('EvnPS_IsPrehospAcceptRefuse', (flag == true ? 2 : 1));
								rec.set('EvnPS_IsPrehospAcceptRefuse_Name', (flag == true ? 'true' : 'false'));
								rec.commit();
							}
						});

						this.grid.getGrid().getView().focusRow(this.grid.getGrid().getStore().indexOf(record));
						this.grid.getGrid().getSelectionModel().selectRow(this.grid.getGrid().getStore().indexOf(record));
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka'] + response.status + ': ' + response.statusText);
				}
			}.createDelegate(this),
			params: {
				 EvnPS_id: record.get('EvnPS_id')
				,EvnPS_IsPrehospAcceptRefuse: (flag == true ? 2 : 1)
				,EvnSection_id: record.get('EvnSection_id')
			},
			url: '/?c=EvnPS&m=setEvnPSPrehospAcceptRefuse'
		});

		return true;
	},
	openEPHForm: function()
	{
		var record = this.grid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		if (getWnd('swPersonEmkWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['forma_emk_epz_v_dannyiy_moment_otkryita']);
			return false;
		}
		else 
		{
			var params = {
				userMedStaffFact: this.userMedStaffFact,
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				mode: 'workplace',
				ARMType: 'common'
			};
			getWnd('swPersonEmkWindow').show(params);
		}
	},
	openForm: function(action)
	{
		var record = this.grid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		var id = record.get('EvnPS_id'); 
		var Person_id = record.get('Person_id');
		var PersonEvn_id = record.get('PersonEvn_id');
		var Server_id = record.get('Server_id');
		var open_form = 'swEvnPSEditWindow';
		var params = {action: action, Person_id: Person_id, PersonEvn_id: PersonEvn_id, Server_id: Server_id, EvnPS_id: id};
		getWnd(open_form).show(params);
		
	},
	resetForm: function(isLoad) 
	{
		this.findById('EJHW_EvnPS_setDateTime_Start').setValue(null);
		this.findById('EJHW_EvnPS_setDateTime_End').setValue(null);
		this.findById('EJHW_isEvnDirection').setValue(null);
		this.findById('EJHW_EvnPS_IsNeglectedCase').setValue(null);
		this.findById('EJHW_PrehospType_id').setValue(null);
		this.findById('EJHW_PrehospArrive_id').setValue(null);
		this.findById('EJHW_Org_oid').setValue(null);
		//this.findById('EJHW_EvnPS_disDateTime_Start').setValue(null);
		//this.findById('EJHW_EvnPS_disDateTime_End').setValue(null);
		this.findById('EJHW_LeaveType_id').setValue(null);
		this.findById('EJHW_Person_Surname').setValue(null);
		this.findById('EJHW_Person_Birthday').setValue(null);
		this.findById('EJHW_Person_Firname').setValue(null);
		this.findById('EJHW_Person_Secname').setValue(null);
		this.findById('EJHW_Person_Birthday_Range').setValue(null);
		//this.findById('EJHW_ResultDesease_id').setValue(null);
		//this.findById('EJHW_LeaveCause_id').setValue(null);
		//this.findById('EJHW_Search_FIO').setValue(null);
		//this.findById('EJHW_Search_BirthDay').setValue(null);
		this.findById('EJHW_LpuRegion_id').setValue(null);
		var grid = this.grid.getGrid();
		//grid.getTopToolbar().items.items[1].disable();//просмотр
		//grid.getTopToolbar().items.items[7].disable();//печать 
		//grid.getTopToolbar().items.items[12].el.innerHTML = '0 / 0';
		var lpu_att_combo = this.findById('EJHW_Lpu_aid');
		lpu_att_combo.setValue(null);
		if ( lpu_att_combo.getStore().getCount() == 0 )
		{
			lpu_att_combo.getStore().load({
				callback: function(r,o,s)
				{
					if ( !isSuperAdmin() ) {
						lpu_att_combo.setValue(getGlobalOptions().lpu_id);
						lpu_att_combo.fireEvent('change', lpu_att_combo, getGlobalOptions().lpu_id);
					}
					this.datePeriodToolbar.onShow(isLoad);
				}.createDelegate(this)
			});
		}
		else
		{
			if ( !isSuperAdmin() ) {
				lpu_att_combo.setValue(getGlobalOptions().lpu_id);
				lpu_att_combo.fireEvent('change', lpu_att_combo, getGlobalOptions().lpu_id);
			}
			this.datePeriodToolbar.onShow(isLoad);
		}

	},
	/*
	begDate: null,
	setBegDate: function(isLoad) {
		if ( this.begDate )
		{
			this.findById('EJHW_EvnPS_setDateTime_Start').setValue(this.begDate);
			if (isLoad) this.doSearch();
		}
		else
		{
			Ext.Ajax.request({
				callback: function(opt, success, response) {
					if ( success && response.responseText != '' ) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						var begDate = Date.parseDate(response_obj.begDate, 'd.m.Y');
						//begDate.setMonth(begDate.getMonth() - 1 );
						begDate.setDate(begDate.getDate() - 7 );
						this.begDate = begDate.format('d.m.Y');
						this.findById('EJHW_EvnPS_setDateTime_Start').setValue(this.begDate);
						if (isLoad) this.doSearch();
					}
				}.createDelegate(this),
				url: C_LOAD_CURTIME
			});
		}
	},
	*/
	doSearch: function() {
		this.loadGridWithFilter(false);
	},
	loadGridWithFilter: function(clear) {
		var grid = this.grid;
		var EvnPS_setDateTime_Start = this.findById('EJHW_EvnPS_setDateTime_Start').getValue() || '';
		var EvnPS_setDateTime_End = this.findById('EJHW_EvnPS_setDateTime_End').getValue() || '';
		var isEvnDirection = this.findById('EJHW_isEvnDirection').getValue() || null;
		var EvnPS_IsNeglectedCase = this.findById('EJHW_EvnPS_IsNeglectedCase').getValue() || null;
		var PrehospType_id = this.findById('EJHW_PrehospType_id').getValue() || null;
		var PrehospArrive_id = this.findById('EJHW_PrehospArrive_id').getValue() || null;
		//#176191 //var LpuSectionTransType_id = this.findById('EJHW_LpuSectionTransType_id').getValue() || null;
		var Org_oid = this.findById('EJHW_Org_oid').getValue() || null;
		//var EvnPS_disDateTime_Start = this.findById('EJHW_EvnPS_disDateTime_Start').getValue() || '';
		//var EvnPS_disDateTime_End = this.findById('EJHW_EvnPS_disDateTime_End').getValue() || '';
		var LeaveType_id = this.findById('EJHW_LeaveType_id').getValue() || null;
		var Person_Surname = this.findById('EJHW_Person_Surname').getValue()||'';
		var Person_Firname = this.findById('EJHW_Person_Firname').getValue()||'';
		var Person_Secname = this.findById('EJHW_Person_Secname').getValue()||'';
		var Person_Birthday =Ext.util.Format.date(this.findById('EJHW_Person_Birthday').getValue(), 'd.m.Y')||'';
		var Person_Birthday_Range_0 = Ext.util.Format.date(this.findById('EJHW_Person_Birthday_Range').getValue1(), 'd.m.Y')||'';
		var Person_Birthday_Range_1 = Ext.util.Format.date(this.findById('EJHW_Person_Birthday_Range').getValue2(), 'd.m.Y')||'';
		//var ResultDesease_id = this.findById('EJHW_ResultDesease_id').getValue() || null;
		//var LeaveCause_id = this.findById('EJHW_LeaveCause_id').getValue() || null;
		var Lpu_aid = this.findById('EJHW_Lpu_aid').getValue() || null;
		var LpuRegion_id = this.findById('EJHW_LpuRegion_id').getValue() || null;
		var MedPersonal_id = this.findById('EJHW_MedPersonal_id').getValue() || null;
		var NotLeave = this.findById('NotLeave').getValue();
		grid.loadData({
			globalFilters: {
				limit: 100,
				start: 0,
				EvnPS_setDateTime_Start: EvnPS_setDateTime_Start,
				EvnPS_setDateTime_End: EvnPS_setDateTime_End,
				isEvnDirection: isEvnDirection,
				EvnPS_IsNeglectedCase: EvnPS_IsNeglectedCase,
				PrehospType_id: PrehospType_id,
				PrehospArrive_id: PrehospArrive_id,
				Org_oid: Org_oid,
				MedPersonal_id: MedPersonal_id,
				//EvnPS_disDateTime_Start: EvnPS_disDateTime_Start,
				//EvnPS_disDateTime_End: EvnPS_disDateTime_End,
				LeaveType_id: LeaveType_id,
				Person_Surname:Person_Surname,
				Person_Firname:Person_Firname,
				Person_Secname:Person_Secname,
				Person_Birthday:Person_Birthday,
				Person_Birthday_Range_0:Person_Birthday_Range_0,
				Person_Birthday_Range_1:Person_Birthday_Range_1,
				//ResultDesease_id: ResultDesease_id,
				//LeaveCause_id: LeaveCause_id,
				Lpu_aid: Lpu_aid,
				LpuRegion_id: LpuRegion_id,
				NotLeave: NotLeave
			}
		});
	},
	show: function() 
	{
		sw.Promed.swJournalHospitWindow.superclass.show.apply(this, arguments);
		if ((!arguments[0]) || (!arguments[0].userMedStaffFact))
		{
			this.hide();
			Ext.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указаны параметры АРМа врача.');
		} else {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		this.center();
		this.resetForm(isSuperAdmin());
		win = this;
		lpu_region_combo = this.findById('EJHW_LpuRegion_id');
		if (!isSuperAdmin())
			win.first = true;
		else 
			win.first = false;
		if ( lpu_region_combo.getStore().getCount() == 0 )
		{
			/*
			lpu_region_combo.disable();
			if ( isSuperAdmin() )
			{
				// загружаем участки текущего ЛПУ
				lpu_region_combo.getStore().load(
				{
					params: { Lpu_id: getGlobalOptions().lpu_id },
					callback: function(r,o,s)
					{
						lpu_region_combo.enable();
					}
				});
			}
			else
			{
				// загружаем участки текущего врача
				lpu_region_combo.getStore().load(
				{
					params: { MedPersonal_id: getGlobalOptions().medpersonal_id },
					callback: function(r,o,s)
					{
						lpu_region_combo.enable();
					}
				});
			}
			*/
		}

		this.grid.addActions({
			handler: function() {
				this.setEvnPSPrehospAcceptRefuse(false);
			}.createDelegate(this),
			iconCls: 'delete16',
			name: 'cancel_prehosp_accept_refuse',
			text: lang['otmena_otkaza_v_podtverjdenii_gospitalizatsii'],
			tooltip: lang['otmena_otkaza_v_podtverjdenii_gospitalizatsii'],
			disabled: true
		});

		this.grid.addActions({
			handler: function() {
				this.setEvnPSPrehospAcceptRefuse(true);
			}.createDelegate(this),
			iconCls: 'delete16',
			name: 'set_prehosp_accept_refuse',
			text: lang['otkaz_v_podtverjdenii_gospitalizatsii'],
			tooltip: lang['otkaz_v_podtverjdenii_gospitalizatsii'],
			disabled: true
		});

		this.grid.addActions({
			handler: function() {
				win.openEPHForm();
			}.createDelegate(this),
			iconCls: 'open16',
			name: 'open_emk',
			text: lang['otkryit_emk'],
			tooltip: lang['otkryit_elektronnuyu_meditsinskuyu_kartu_patsienta'],
			disabled: true
		});

		this.findById('EJHW_EvnPS_setDateTime_Start').focus(true, 100);
	},
	initComponent: function() 
	{
		var win = this;
		
		this.datePeriodToolbar = new sw.Promed.datePeriodToolbar({
			curDate: getGlobalOptions().date,
			mode: 'week',
			onSelectPeriod: function(begDate,endDate,allowLoad)
			{
				this.findById('EJHW_EvnPS_setDateTime_Start').setValue(begDate.format('d.m.Y'));
				this.findById('EJHW_EvnPS_setDateTime_End').setValue(endDate.format('d.m.Y'));
				if(allowLoad)
					this.doSearch();
			}.createDelegate(this)
		});
		
		this.datePeriodToolbar.dateMenu.addListener('blur', 
			function () {
				this.datePeriodToolbar.onSelectMode('range',false);
			}.createDelegate(this)
		);
		
		this.filter = new Ext.form.FormPanel(
		{
			frame: true,
			border: false,
			region: 'north',
			autoHeight: true,
			title: lang['data_postupleniya'],
			layout: 'column',
			tbar: this.datePeriodToolbar,
			keys: [
				{
					alt: true,
					fn: function(inp, e) {
						this.doSearch();
					}.createDelegate(this),
					key: [
						Ext.EventObject.ENTER
					],
					stopEvent: true
				}, {
					fn: function(inp, e) {
						this.doSearch();
					}.createDelegate(this),
					key: [
						Ext.EventObject.ENTER//,Ext.EventObject.S #115170 не позволяет ввести ы
					],
					stopEvent: true
				}
			],
			items: 
/*
			}, 
			{
				layout: 'form',
				border: false,
				bodyStyle: 'background-color: transparent;',
				items: 
				[{
					style: "padding-left: 20px",
					xtype: 'button',
					id: 'EJHW_BtnClear',
					text: lang['sbros'],
					iconCls: 'clear16',
					handler: function()
					{
						var form = Ext.getCmp('EJHW');
						form.findById('EJHW_Search_FIO').setValue(null);
						form.findById('EJHW_Search_BirthDay').setValue(null);
						// Действие
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle: 'background-color: transparent;',
				items: 
				[{
					style: "padding-left: 20px",
					xtype: 'button',
					id: 'EJHW_BtnSearch',
					text: lang['nayti'],
					iconCls: 'search16',
					handler: function()
					{
						var form = Ext.getCmp('EJHW');
						// Действие
					}
				}]
*/
			[{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 170,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items: 
				[/*{
					fieldLabel: lang['data_postupleniya_s'],
					allowBlank: true,
					disabled: false,
					tabIndex: TABINDEX_EJHW + 1,
					name: 'EvnPS_setDateTime_Start',
					id: 'EJHW_EvnPS_setDateTime_Start',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					//width: 180,
					xtype: 'swdatefield'
				},
				{
					fieldLabel: lang['data_postupleniya_po'],
					allowBlank: true,
					disabled: false,
					tabIndex: TABINDEX_EJHW + 2,
					name: 'EvnPS_setDateTime_End',
					id: 'EJHW_EvnPS_setDateTime_End',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					//width: 180,
					xtype: 'swdatefield'

				},*/
				{
					name: 'EvnPS_setDateTime_Start',
					id: 'EJHW_EvnPS_setDateTime_Start',
					xtype: 'hidden'
				},
				{
					name: 'EvnPS_setDateTime_End',
					id: 'EJHW_EvnPS_setDateTime_End',
					xtype: 'hidden'

				},
				new sw.Promed.SwYesNoCombo({
					allowBlank: true,
					disabled: false,
					fieldLabel: lang['c_napravleniem'],
					id: 'EJHW_isEvnDirection',
					hiddenName: 'isEvnDirection',
					tabIndex: TABINDEX_EJHW + 3,
					width: 150
				}),
				{
					fieldLabel: lang['tip_gospitalizatsii'],
					hiddenName: 'PrehospType_id',
					id: 'EJHW_PrehospType_id',
					listWidth: 300,
					tabIndex: TABINDEX_EJHW + 4,
					width: 150,
					xtype: 'swprehosptypecombo'
				},
				{
					fieldLabel: lang['kem_dostavlen'],
					hiddenName: 'PrehospArrive_id',
					id: 'EJHW_PrehospArrive_id',
					tabIndex: TABINDEX_EJHW + 5,
					width: 150,
					xtype: 'swprehosparrivecombo'
				},
				/*{
					xtype: 'textfieldpmw',
					width: 150,
					id: 'EJHW_Search_FIO',
					fieldLabel: lang['fio'],
					tabIndex: TABINDEX_EJHW + 6,
					listeners: 
					{
						'keydown': function (inp, e) 
						{
							var form = Ext.getCmp('EJHW');
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								e.stopEvent();
								// Действие
							}
						}
					}
				},*/
				{
					displayField: 'Org_Name',
					editable: false,
					enableKeyEvents: true,
					fieldLabel: lang['lpu_kuda_gospitalizirovan'],
					hiddenName: 'Org_oid',
					id: 'EJHW_Org_oid',
					listeners: {
						'keydown': function( inp, e ) {
							if ( inp.disabled )
								return;

							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								inp.onTrigger1Click();
								return false;
							}
						},
						'keyup': function(inp, e) {
							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
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
					mode: 'local',
					onTrigger1Click: function() {
						var combo = Ext.getCmp('EJHW').findById('EJHW_Org_oid');
						if ( combo.disabled ) {
							return false;
						}

						getWnd('swOrgSearchWindow').show({
							OrgType_id: 11,
							onClose: function() {
								combo.focus(true, 200)
							},
							onSelect: function(org_data) {
								if ( org_data.Org_id > 0 ) {
									combo.getStore().loadData([{
										Org_id: org_data.Org_id,
										Org_Name: org_data.Org_Name
									}]);
									combo.setValue(org_data.Org_id);
									getWnd('swOrgSearchWindow').hide();
									combo.collapse();
								}
							}
						});
					}.createDelegate(this),
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'Org_id', type: 'int' },
							{ name: 'Org_Name', type: 'string' }
						],
						key: 'Org_id',
						sortInfo: {
							field: 'Org_Name'
						},
						url: C_ORG_LIST
					}),
					tabIndex: TABINDEX_EJHW + 7,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{Org_Name}',
						'</div></tpl>'
					),
					trigger1Class: 'x-form-search-trigger',
					triggerAction: 'none',
					valueField: 'Org_id',
					width: 150,
					xtype: 'swbaseremotecombo'
				},
				{
					fieldLabel: lang['tolko_ne_vyipisannyie'],
					Name: 'NotLeave',
					id: 'NotLeave',
					tabIndex: TABINDEX_EJHW + 8,
					width: 150,
					xtype: 'checkbox',
					listeners: {
						'check': function(checkbox,checked)
						{
							if (checked)
							{
								this.findById('EJHW_LeaveType_id').disable();
							}
							else
							{
								this.findById('EJHW_LeaveType_id').enable();
							}
						}.createDelegate(this)
					}
				}]
			}, 
			{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 170,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items:
				[/*{
					fieldLabel: lang['data_vyipiski_s'],
					allowBlank: true,
					disabled: false,
					tabIndex: TABINDEX_EJHW + 8,
					name: 'EvnPS_disDateTime_Start',
					id: 'EJHW_EvnPS_disDateTime_Start',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				},
				{
					fieldLabel: lang['data_vyipiski_po'],
					allowBlank: true,
					disabled: false,
					tabIndex: TABINDEX_EJHW + 9,
					name: 'EvnPS_disDateTime_End',
					id: 'EJHW_EvnPS_disDateTime_End',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				},*/
				{
					fieldLabel: lang['ishod_gospitalizatsii'],
					hiddenName: 'LeaveType_id',
					id: 'EJHW_LeaveType_id',
					tabIndex: TABINDEX_EJHW + 10,
					listWidth: 350,
					width: 300,
					xtype: 'swleavetypecombo'
				},
				/*{
					fieldLabel: lang['rezultat_gospitalizatsii'],
					hiddenName: 'ResultDesease_id',
					id: 'EJHW_ResultDesease_id',
					listeners: {
						'render': function(combo) {
							combo.getStore().load();
						}
					},
					listWidth: 670,
					tabIndex: TABINDEX_EJHW + 11,
					width: 300,
					xtype: 'swresultdeseasecombo'
				},
				*/
				/*{
					fieldLabel: lang['prich_vyip_perevoda'],
					hiddenName: 'LeaveCause_id',
					id: 'EJHW_LeaveCause_id',
					listeners: {
						'render': function(combo) {
							combo.getStore().load();
						}
					},
					tabIndex: TABINDEX_EJHW + 12,
					width: 300,
					xtype: 'swleavecausecombo'/*
				},
				*/
				
				/*{
					xtype: 'swdatefield',
					renderer: Ext.util.Format.dateRenderer('d.m.Y'),
					id: 'EJHW_Search_BirthDay',
					fieldLabel: lang['dr'],
					tabIndex: TABINDEX_EJHW + 13,
					listeners: 
					{
						'keydown': function (inp, e) 
						{
							var form = Ext.getCmp('EJHW');
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								e.stopEvent();
								// Действие
							}
						}
					}
				},*/
					
				{
					//выбор лпу и участков в этих лпу только под суперадмином
					allowBlank: true,
					autoLoad: true,
					disabled: ! isSuperAdmin(),
					tabIndex: TABINDEX_EJHW + 13,
					width: 300,
					listWidth: 350,
					fieldLabel: lang['lpu_prikrepleniya'],
					id: 'EJHW_Lpu_aid',
					hiddenName: 'Lpu_aid',
					xtype: 'swlpulocalcombo',
					listeners: {
						'change': function(combo, value){
								var lpu_id = value;
								if (lpu_id)
								{
								
									win.findById('EJHW_MedPersonal_id').setValue(null);
									win.findById('EJHW_MedPersonal_id').getStore().load(
									{
										params: { Lpu_id: lpu_id },
										callback: function ()
										{
											if ((getGlobalOptions().medpersonal_id>0) && (!isSuperAdmin()))
											{
												win.findById('EJHW_MedPersonal_id').setFieldValue('MedPersonal_id', getGlobalOptions().medpersonal_id);
												//win.findById('EJHW_MedPersonal_id').setValue(getGlobalOptions().medpersonal_id);
											}
										}.createDelegate(this)
									});
							}
						}
					}
				},
				{
					// врач
					fieldLabel: lang['vrach'],
					width: 300,
					listWidth: 450,
					name: 'MedPersonal_id',
					id: 'EJHW_MedPersonal_id',
					tabIndex: TABINDEX_EJHW + 14,
					xtype: 'swmedpersonalwithlpuregioncombo',
					listeners: {
						'change': function(combo, value){
							var medpersonal_id = value;
							var lpu_id = Ext.getCmp('EJHW').findById('EJHW_Lpu_aid').getValue();
							var lpuregion_field = win.findById('EJHW_LpuRegion_id');
							if ((medpersonal_id) && (medpersonal_id>0))
							{
								lpuregion_field.setValue(null);
								lpuregion_field.getStore().load(
								{
									params: { MedPersonal_id: medpersonal_id },
									callback: function(r,o,s){
										if(r.length > 0)
										{
											lpuregion_field.setValue(r[0].get('LpuRegion_id'));
											if (win.first)
											{
												win.datePeriodToolbar.onShow(true);
												win.first = false;
											}
										}
									}
								});
							}
							else 
							{
								lpuregion_field.setValue(null);
								lpuregion_field.getStore().load(
								{
									params: { Lpu_id: lpu_id }
								});
								if (win.first)
								{
									win.datePeriodToolbar.onShow(true);
									win.first = false;
								}
							}
						}
					},
					allowBlank: true
				},
				{
					// для врача только фильтрация по своим участкам если их несколько
					fieldLabel: lang['uchastok'],
					width: 300,
					name: 'LpuRegion_id',
					id: 'EJHW_LpuRegion_id',
					tabIndex: TABINDEX_EJHW + 15,
					xtype: 'swlpuregioncombo',
					allowBlank: true
				}, {
					allowBlank: true,
					fieldLabel: lang['sluchay_zapuschen'],
					hiddenName: 'EvnPS_IsNeglectedCase',
					id: 'EJHW_EvnPS_IsNeglectedCase',
					tabIndex: TABINDEX_EPSEF + 15,
					width: 100,
					xtype: 'swyesnocombo'
				}]
			},
			{
				layout: 'form',
				labelAlign: 'right',
				labelWidth: 170,
				border: false,
				bodyStyle: 'background-color: transparent; padding-left: 5px;',
				items:
				[
				{
					xtype: 'textfield',
					hiddenName: 'Person_Surname',
					id: 'EJHW_Person_Surname',
					fieldLabel: lang['familiya'],
					tabIndex: TABINDEX_EJHW + 11					
				},
				{
					xtype: 'textfield',
					hiddenName: 'Person_Firname',
					id: 'EJHW_Person_Firname',
					fieldLabel: lang['imya'],
					tabIndex: TABINDEX_EJHW + 11					
				},
				{
					xtype: 'textfield',
					hiddenName: 'Person_Secname',
					id: 'EJHW_Person_Secname',
					fieldLabel: lang['otchestvo'],
					tabIndex: TABINDEX_EJHW + 11					
				},
				{
					fieldLabel: lang['data_rojdeniya'],
					tabIndex: TABINDEX_EJHW + 8,
					hiddenName: 'Person_Birthday',
					id: 'EJHW_Person_Birthday',
					format: 'd.m.Y',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					xtype: 'swdatefield'
				},
				{
					fieldLabel: lang['diapazon_dat_rojdeniya'],
					name: 'Person_Birthday_Range',
					id: 'EJHW_Person_Birthday_Range',
					plugins: [
						new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
					],
					tabIndex: TABINDEX_PERSCARDSW + 6,
					width: 170,
					xtype: 'daterangefield'					
				}
				]
		    }]
		});
		
		
		this.grid = new sw.Promed.ViewFrame(
		{
			id: 'EJHW_HospitalizationsGrid',
			object: 'EvnPS',
			dataUrl: '/?c=EvnPS&m=loadHospitalizationsGrid',
			layout: 'fit',
			region: 'center',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'EvnSection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnPS_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Lpu_did', type: 'int', hidden: true},
				{name: 'PrehospType_id', type: 'int', hidden: true},
				//{name: 'MedPersonal_aid', type: 'int', hidden: true},
				{name: 'MedPersonal_did', type: 'int', hidden: true},
				{name: 'MedPersonal_zdid', type: 'int', hidden: true},
				{name: 'DaysDiff', type: 'int', hidden: true},
				{name: 'EvnPS_IsPrehospAcceptRefuse', type: 'int', hidden: true},
				{name: 'EvnPS_setDateTime', type: 'datetime', header: lang['postupil'], width: 90},
				{name: 'Person_Fio', autoexpand: true, type: 'string', header: lang['fio']},
				{name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'EvnDirection_Num', header: lang['napravlenie'], width: 90},
				{name: 'IsEvnDirection', header: lang['napravlenie'], renderer: sw.Promed.Format.dirColumn, hidden: true, hideable: false},
				{name: 'EvnDirection_id', type: 'int', header: 'EvnDirection_id', hidden: true, hideable: false},
				{name: 'LpuDir_Name', header: lang['lpu_napravleniya'], width: 120},
				{name: 'Lpu_Name', header: lang['lpu_gospitalizatsii'], width: 120},
				{name: 'LpuSections_Name', header: lang['otdelenie'], width: 120},
				{name: 'EvnSection_Count', type:'int', header: lang['kol-vo_otdeleniy'], width: 120},
				{name: 'PrehospType_Name', header: lang['tip_gospitalizatsii'], width: 100},
				{name: 'PrehospDirect_Name', header: lang['kem_napravlen'], width: 100},
				{name: 'PrehospArrive_Name', header: lang['kem_dostavlen'], width: 100},
				// {name: 'ActualCost', header: 'Фактическая стоимость, руб.', width: 100},
				// {name: 'PlannedCost', header: 'Плановая стоимость, руб.', width: 100},
				{name: 'Diag_Name', header: lang['diagnoz'], width: 150},
				{name: 'EvnPS_disDateTime', type: 'datetime', header: lang['vyipisan'], width: 100},
				// {name: 'LeaveCause_Name', header: 'Причина выписки', width: 100},
				{name: 'LeaveType_Name', header: lang['ishod_gospitalizatsii'], width: 100},
				// {name: 'ResultDesease_Name', header: 'Результат госпитализации', width: 100},
				{name: 'LpuAtt_Name', header: lang['lpu_prikrepleniya'], width: 80},
				{name: 'LpuRegion_Name', header: lang['uchastok'], width: 80},
				{name: 'EvnPS_IsNeglectedCase', type: 'checkbox', header: lang['sluchay_zapuschen'], width: 80},
				{name: 'EvnPS_IsPrehospAcceptRefuse_Name', type: 'checkbox', header: lang['otkaz_v_podtverjdenii_gospitalizatsii'], width: 80}
			],
			actions:
			[
				{name:'action_add', hidden: true, disabled: true},
				{name:'action_view', text: lang['prosmotr'], handler: function() {this.openForm('view')}.createDelegate(this)},
				{name:'action_edit', hidden: true, disabled: true, handler: function() {this.openForm('view')}.createDelegate(this)},
				{name:'action_delete', hidden: true, disabled: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onCellClick: function(grid,rowIdx,colIdx,e) {
				var record = grid.getStore().getAt(rowIdx);
				if ( !record ) {
					return false;
				}
                var fieldName = grid.getColumnModel().getDataIndex(colIdx);
				// Открываем просмотр направления по клику по иконке направления
				if (fieldName == 'EvnDirection_Num' && record.data.IsEvnDirection && record.data.EvnDirection_id)
				{
					getWnd('swEvnDirectionEditWindow').show({
						action: 'view',
						formParams: new Object(),
						EvnDirection_id: record.data.EvnDirection_id
					}); 
				}
				return true;
			},
			onRowSelect: function(sm,rowIdx,record) {
				
				if (record.get('Person_id')) {
					this.grid.ViewActions['open_emk'].setDisabled(false);			
				} else {
					this.grid.ViewActions['open_emk'].setDisabled(true);
				}

				if ( record.get('Lpu_id') != record.get('Lpu_did')
					&& parseInt(record.get('PrehospType_id')) == 2
					&& record.get('EvnPS_IsPrehospAcceptRefuse') != 2
					&& parseInt(record.get('DaysDiff')) <= 5
					// && (/*getGlobalOptions().medpersonal_id == record.get('MedPersonal_aid') ||*/ getGlobalOptions().medpersonal_id == record.get('MedPersonal_did') || getGlobalOptions().medpersonal_id == record.get('MedPersonal_zdid'))
				) {
					this.grid.ViewActions['set_prehosp_accept_refuse'].setDisabled(false);
				}
				else {
					this.grid.ViewActions['set_prehosp_accept_refuse'].setDisabled(true);
				}

				if ( parseInt(record.get('EvnPS_IsPrehospAcceptRefuse')) == 2 ) {
					this.grid.ViewActions['cancel_prehosp_accept_refuse'].setDisabled(false);
				}
				else {
					this.grid.ViewActions['cancel_prehosp_accept_refuse'].setDisabled(true);
				}
			}.createDelegate(this)
		});

		this.grid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				//if ( row.get('Lpu_id') != row.get('Lpu_did') && parseInt(row.get('PrehospType_id')) == 2 ) {
				//#8935
				if ( parseInt(row.get('PrehospType_id')) == 1 ) {
					cls = cls + 'x-grid-rowbold ';
				}

				if ( cls.length == 0 ) {
					cls = 'x-grid-panel';
				}

				return cls;
			}
		});

		Ext.apply(this, 
		{
			region: 'center',
			layout: 'border',
			items: [
				this.filter,
				this.grid
			],
			buttons: [{
				id: 'EJHW_BtnSearch',
				text: lang['nayti'],
				tabIndex: TABINDEX_EJHW + 19,
				iconCls: 'search16',
				handler: function()
				{
					this.doSearch();
				}.createDelegate(this)
			},
			{
				id: 'EJHW_BtnClear1',
				text: lang['sbros'],
				tabIndex: TABINDEX_EJHW + 21,
				iconCls: 'resetsearch16',
				handler: function()
				{
					this.resetForm(true);
				}.createDelegate(this)
			},
			{
				text: '-'
			},
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				id: 'EJHW_HelpButton',
				handler: function(button, event) 
				{
					ShowHelp(this.title);
				}.createDelegate(this)
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				tabIndex: TABINDEX_EJHW + 50,
				handler: function() {
					this.hide();
				}.createDelegate(this)
			}],
			enableKeyEvents: true,
			keys: 
			[{
				alt: true,
				fn: function(inp, e) 
				{
					if (e.getKey() == Ext.EventObject.ESC)
					{
						Ext.getCmp('EJHW').hide();
						return false;
					}
				},
				key: [ Ext.EventObject.ESC ],
				scope: this,
				stopEvent: false
			}]
		});
		sw.Promed.swJournalHospitWindow.superclass.initComponent.apply(this, arguments);
	}
});