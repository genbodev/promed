/**
* swLabRequestForm.js - Регистрационный журнал
* 
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
* 
*/

sw.Promed.swLabRequestForm = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['registratsionnyiy_jurnal'],
	layout: 'border',
	id: 'LabRequestForm',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	buttons:
	[
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	show: function()
	{
		sw.Promed.swLabRequestForm.superclass.show.apply(this, arguments);
		
		this.mode = null;
		this.setTitle(lang['registratsionnyiy_jurnal']);
		
	},
	openLabRequestEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		
		var grid = this.findById('LabRequestGridPanel').getGrid();
		
		var params = new Object();

		params.action = action;
		params.callback = function(data) {}
		
		if ( action == 'add' ) {

			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd('swEvnLabRequestEditWindow').show(params);
				},
				searchMode: 'all'
			});
			
		} else {
			
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnLabRequest_id') ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zayavka_iz_spiska']);
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.EvnLabRequest_id = record.get('EvnLabRequest_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnLabRequestEditWindow').show(params);
			
		}
	},
	initComponent: function()
	{
		var form = this;
		
		this.dateMenu = new Ext.form.DateRangeField(
		{
			width: 150,
			fieldLabel: lang['period'],
			plugins: 
			[
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});
		
		this.dateMenu.addListener('keydown',function (inp, e) 
		{
			var form = Ext.getCmp('swMPWorkPlaceWindow');
			if (e.getKey() == Ext.EventObject.ENTER)
			{
				e.stopEvent();
				form.scheduleLoad('period');
			}
		});
		this.dateMenu.addListener('select',function () 
		{
			// Читаем расписание за период
			var form = Ext.getCmp('swMPWorkPlaceWindow');
			form.scheduleLoad('period');
		});
		
		this.formActions = new Array();
		this.formActions.selectDate = new Ext.Action(
		{
			text: ''
		});
		this.formActions.prev = new Ext.Action(
		{
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function()
			{
				// на один день назад
				this.prevDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.next = new Ext.Action(
		{
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function()
			{
				// на один день вперед
				this.nextDay();
				this.scheduleLoad('range');
			}.createDelegate(this)
		});
		this.formActions.day = new Ext.Action(
		{
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			pressed: true,
			handler: function()
			{
				this.currentDay();
				this.scheduleLoad('day');
			}.createDelegate(this)
		});
		this.formActions.week = new Ext.Action(
		{
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			handler: function()
			{
				this.currentWeek();
				this.scheduleLoad('week');
			}.createDelegate(this)
		});
		this.formActions.month = new Ext.Action(
		{
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function()
			{
				this.currentMonth();
				this.scheduleLoad('month');
			}.createDelegate(this)
		});
		this.formActions.range = new Ext.Action(
		{
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function()
			{
				this.scheduleLoad('range');
			}.createDelegate(this)
		});

		this.DoctorToolbar = new Ext.Toolbar(
		{
			items: 
			[
				this.formActions.prev, 
				{
					xtype : "tbseparator"
				},
				this.dateMenu,
				//this.dateText,
				{
					xtype : "tbseparator"
				},
				this.formActions.next, 
				{
					xtype : "tbseparator"
				},
				this.formActions.day, 
				this.formActions.week, 
				this.formActions.month,
				this.formActions.range,
				{
					xtype: 'tbfill'
				},
				'Cito!:',
				{
					name: 'Org_Name',
					anchor: '100%',
					disabled: false,
					fieldLabel: 'Cito!',
					tabIndex: 0,
					xtype: 'swyesnocombo'
				}
			]
		});
		
		this.FilterPanel = new Ext.Panel({
			//bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			autoHeight: true,
			region: 'north',
			layout: 'column',
			//title: 'Фильтры',
			tbar: this.DoctorToolbar,
			id: 'OrgLpuFilterPanel'
		});

		this.LabRequestGrid = new sw.Promed.ViewFrame(
		{
			id: 'LabRequestGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'Org',
			dataUrl: '/?c=EvnLabRequest&m=loadEvnLabRequestList',
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'EvnLabRequest_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnDirection_IsCito', header: 'Cito', type: 'checkbox', width: 40},
				{name: 'EvnDirection_Descr', header: lang['usluga'], width: 280},
				{name: 'EvnDirection_Num', header: lang['nomer_napravleniya'], width: 120},
				{name: 'EvnDirection_setDate', dateFormat: 'd.m.Y', type: 'date', header: lang['data_napravleniya'], width: 120},
				{name: 'PrehospType_Name', header: lang['kem_napravlen'], width: 320},
				{name: 'Person_FIO', header: lang['fio_patsienta'], width: 240}
			],
			actions:
			[
				{name:'action_add', text: lang['dobavit_bez_zapisi'], handler: function() { this.openLabRequestEditWindow('add'); }.createDelegate(this) },
				{name:'action_edit', handler: function() { this.openLabRequestEditWindow('edit'); }.createDelegate(this) },
				{name:'action_view', hidden: true},
				{name:'action_delete', url: '/?c=Org&m=index&method=deleteOrg'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			]
		});	

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items:
			[
				form.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					defaults: {split: true},
					items: 
					[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.LabRequestGrid]
						}
					]
				}
			]
		});
		sw.Promed.swLabRequestForm.superclass.initComponent.apply(this, arguments);
	}

});
