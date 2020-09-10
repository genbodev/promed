/**
 * delDocsSearchWindow - Окно поиска по удаленным документам
 * common.delDocsSearchWindow
 * widget.delDocsSearchWindow
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 */

Ext6.define('common.delDocsSearchWindow', {
	noCloseOnTaskBar: false,
	extend: 'base.BaseForm',
	alias: 'widget.delDocsSearchWindow',
	maximized: true,
	refId: 'polkawp',
	findWindow: false,
	closable: true,
	frame: false,
	cls: 'arm-window-new',
	title: 'Удаленные документы',
	header: true,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	onDblClick: function () {
		var win = this;
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('Docs_id')) {
				win.openEditWindow('view');
			}
		}
	},
	onRecordSelect: function () {
		var win = this;

		win.mainGrid.down('#action_view').disable();
		
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('Docs_id')) {
				win.mainGrid.down('#action_view').enable();
			}
		}
	},
	setViewMode: function(action){
		var win = this;
		win.mainGrid.down('#action_view').setVisible(action == 'view');
	},
	getGrid: function () {
		return this.mainGrid;
	},
	getSelectedRecord: function () {
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('Docs_id')) {
				return record;
			}
		}
		return false;
	},
	show: function () {
		this.callParent(arguments);
		var win = this;
		var base_form = this.filterPanel.getForm();

		base_form.reset();
		
		if(arguments[0].action)
			this.action = arguments[0].action;

		win.setTitle('Удаленные документы')

		if(arguments[0].ArmType && arguments[0].ArmType == 'superadmin') {
			this.LpuAccess = true;
			this.filterPanel.getForm().findField('Lpu_id').setDisabled(false);
		}else{
			win.filterPanel.getForm().findField('Lpu_id').getStore().insert(0,{
				Lpu_id: getGlobalOptions().lpu_id, Lpu_Name: getGlobalOptions().lpu_name, Lpu_Nick: getGlobalOptions().lpu_nick
			});
			this.filterPanel.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			this.filterPanel.getForm().findField('Lpu_id').setDisabled(true);
		}
		win.doReset();
	},
	LpuAccess: false,
	doReset: function () {
		var base_form = this.filterPanel.getForm();
		base_form.reset();
		if(this.LpuAccess == false) {
			win.filterPanel.getForm().findField('Lpu_id').getStore().insert(0,{
				Lpu_id: getGlobalOptions().lpu_id, Lpu_Name: getGlobalOptions().lpu_name, Lpu_Nick: getGlobalOptions().lpu_nick
			});
			this.filterPanel.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
		}
		this.mainGrid.getStore().removeAll();
		this.setViewMode('view');
		this.doSearch();
	},
	doSearch: function (options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var base_form = this.filterPanel.getForm();
		
		var extraParams = base_form.getValues();
		extraParams.Lpu_id = extraParams.Lpu_id == 'null' ? '' : extraParams.Lpu_id;
		
		win.mainGrid.getStore().proxy.extraParams = extraParams;

		win.mainGrid.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	openEditWindow: function (action){
		var win = this;
		var record = this.getSelectedRecord();

		var params = {
			Person_BirthDay: record.data.Person_BirthDay,
			userMedStaffFact: win.userMedStaffFact,
			Person_id: record.data.Person_id,
			Server_id: record.data.Server_id,
			delDocsView: true,
			action: 'view'
		};

		switch(record.data.EvnClass_id){
			case '3':
				var windName = 'swEvnPLEditWindow';
				params.EvnPL_id = record.data.Docs_id;
				break;
			case '6':
				var windName = 'swEvnPLStomEditWindow';
				params.EvnPLStom_id = record.data.Docs_id;
				break;
			case '30':
				var windName = 'swEvnPSEditWindow';
				params.EvnPS_id = record.data.Docs_id;
				break;
			case '110':
				var windName = 'swCmpCallCardNewCloseCardWindow';
				params.CmpCloseCard_id = record.data.Docs_id;
				params.formParams = params;
				break;
			case '78':
				var windName = 'swEvnLabRequestEditWindow';
				params.EvnDirection_id = record.data.EvnDirection_id;
				params.MedService_id = record.data.MedService_id;
				params.EvnLabRequest_id = record.data.Docs_id;
				break;
			case '20':
				var windName = 'swEvnStickEditWindow';
				params.EvnStick_id = record.data.Docs_id;
				params.formParams = params;
				break;
		};
		
		getWnd(windName).show(params);
	},
	initComponent: function () {
		var win = this;

		win.filterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			region: 'west',
			width: 280,
			items: [{
				border: false,
				layout: 'column',
				defaults: {
					anchor: '100%',
					margin: '20 20 0 20',
					width: 240,
					vertical: true,
					labelAlign:'top'
				},
				items: [	
					Ext6.create('Ext6.date.RangeField', {
					xtype: 'daterangefield',
					fieldLabel: 'Период создания',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					name: 'CreateDocs_DateRange',
					listeners: {
						specialkey: function (field, e, eOpts) {
							if (e.getKey() == e.ENTER) {
								win.doSearch();
							}
						},
						change: function (checkbox, newVal, oldVal) {
							win.doSearch();
						}
					}
				}),
					Ext6.create('Ext6.date.RangeField', {
					xtype: 'daterangefield',
					fieldLabel: 'Период удаления',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					name: 'DeleteDocs_DateRange',
					value: new Date(),
					listeners: {
						specialkey: function (field, e, eOpts) {
							if (e.getKey() == e.ENTER) {
								win.doSearch();
							}
						},
						change: function (checkbox, newVal, oldVal) {
							win.doSearch();
						}
					}
				}),
				{
					xtype: 'swLpuCombo',
					fieldLabel: 'МО',
					name: 'Lpu_id',
					valueField: 'Lpu_id',
					displayField: 'Lpu_Nick',
					disabled: !isUserGroup('SuperAdmin'),
					autoLoad: false,
					listeners: {
						select: function(model, record, index) {
							if (record && record.get('Lpu_id')) {
								win.doSearch();
							}
						},
						'change': function(){
							if(win.LpuAccess == false) {
								win.filterPanel.getForm().findField('Lpu_id').getStore().insert(0,{
									Lpu_id: getGlobalOptions().lpu_id, Lpu_Name: getGlobalOptions().lpu_name, Lpu_Nick: getGlobalOptions().lpu_nick
								});
								win.filterPanel.getForm().findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
							}
						}
					}
				}, {
					xtype: 'baseCombobox',
					fieldLabel: 'Тип документа',
					name: 'DocsType_id',
					fieldName:'DocsType_Name',
					valueField: 'DocsType_id',
					displayField: 'DocsType_Name',
					value: 0,
					store: new Ext6.data.SimpleStore({
						key: 'DocsType_id',
						fields: [
							{name:'DocsType_id',	type:'int'},
							{name:'DocsType_Name',	type:'string'}
						],
						data: [
							['0', ''],
							['3', 'Талон амбулаторного пациента'],
							['6', 'Талон амбулаторного пациента (стоматология)'],
							['30', 'Карта выбывшего из стационара'],
							['110', 'Карта вызова 110у'],
							['78', 'Заявка на лабораторное исследование'],
							['20', 'Лист временной нетрудоспособности']
						]
					}),
					listeners: {
						select: function(model, record, index) {
							if (record && record.get('DocsType_id')) {
								win.doSearch();
							}
						}
					}
				}, {
					xtype: 'textfield',
					fieldLabel: 'Фамилия',
					name: 'Person_SurName'
					
				}, {
					xtype: 'textfield',
					fieldLabel: 'Имя',
					name: 'Person_FirName'

				}, {
					xtype: 'textfield',
					fieldLabel: 'Отчество',
					name: 'Person_SecName'

				}, {
					xtype: 'datefield',
					fieldLabel: 'Дата рождения',
					name: 'Person_BirthDay'
				}, {
					border: false,
					layout: 'column',
					margin: '40 20',
					items: [{
						xtype: 'button',
						text: langs('Поиск'),
						autoWidth: true,
						cls: 'button-primary',
						handler: function() {
							win.doSearch();
						}
					}, {
						xtype: 'button',
						text: langs('Очистить'),
						border: true,
						autoWidth: true,
						cls: 'button-secondary',
						style:'float:right',
						handler: function() {
							win.doReset();
						}
					}]
				}]		
			}]
		});

		win.mainGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			flex: 2,
			tbar: {
				xtype: 'pagingtoolbar',
				displayInfo: true,
				border: false,
				flex: 1,
				items: [{
					text: 'Открыть',
					xtype: 'button',
					iconCls: 'action_view',
					itemId: 'action_view',
					handler: function () {
						win.openEditWindow('view');
					}
				}]
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function (model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			listeners: {
				itemdblclick: function () {
					win.onDblClick();
				}
			},
			store: {
				fields: [
					{name: 'Docs_id', type: 'int'},
					{name: 'DocsType_Name', type: 'string'},
					{name: 'EvnDirection_id', type: 'string'},
					{name: 'MedService_id', type: 'string'},
					{name: 'Docs_Num', type: 'string'},
					{name: 'Lpu_id', type: 'int'},
					{name: 'Lpu_Name', type: 'string'},
					{name: 'Person_id', type: 'int'},
					{name: 'Person_Fio', type: 'string'},
					{name: 'Person_BirthDay', type: 'string'},
					{name: 'CreateDocs_Date', type: 'string'},
					{name: 'DeleteDocs_Date', type: 'string'},
					{name: 'Sort_Date', type: 'date'}
				],
				pageSize: 50,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=DelDocsSearch&m=LoadDelDocs',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [{
					property: 'Sort_Date',
					direction: 'desc'
				}],
				listeners: {
					load: function () {
						win.onRecordSelect();
					}
				}
			},
			columns: [
				{text: 'Ид документа', dataIndex: 'Docs_id', hidden: true },
				{text: 'Ид направления', dataIndex: 'EvnDirection_id', hidden: true },
				{text: 'Ид мед сервис', dataIndex: 'MedService_id', hidden: true },
				{text: 'Тип документа', minWidth: 140, dataIndex: 'DocsType_Name', flex: 1},
				{text: 'Номер документа', dataIndex: 'Docs_Num', flex: 1},
				{text: 'МО', dataIndex: 'Lpu_Name', flex: 2},
				{text: 'Ф.И.О. пациента', dataIndex: 'Person_Fio', flex: 2},
				{text: 'Дата рождения', width: 110, dataIndex: 'Person_BirthDay'},
				{text: 'Дата создания', width: 110, dataIndex: 'CreateDocs_Date'},
				{text: 'Дата удаления', width: 110, dataIndex: 'DeleteDocs_Date'}
			]
		});

		win.cardPanel = new Ext6.Panel({
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'border',
			activeItem: 0,
			border: false,
			items: [ win.filterPanel, win.mainGrid ]
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			items: [ win.cardPanel ]
		});

		Ext6.apply(win, {
			items: [ win.mainPanel, win.FormPanel ],
		});
		this.callParent(arguments);
	}
});
