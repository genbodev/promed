/**
 * swTariffValueListWindow - Тарифы ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.Admin.swTariffValueListWindow', {
	/* свойства */
	alias: 'widget.swTariffValueListWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	layout: 'border',
	maximized: true,
	refId: 'tariffvaluelistsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Тарифы ТФОМС',
	width: 1000,

	/* методы */
	delete: function() {
		if ( !isSuperAdmin() ) {
			return false;
		}

		var win = this;
		
		if ( !win.Grid.getSelectionModel().hasSelection()) {
			return false;
		}

		var record = win.Grid.getSelectionModel().getSelection()[0];

		if ( typeof record != 'object' || Ext6.isEmpty(record.get('TariffValue_id')) ) {
			return false;
		}

		// задаём вопрос
		Ext6.Msg.show({
			title: 'Вопрос',
			msg: 'Вы действительно хотите удалить тариф?',
			buttons: Ext6.Msg.YESNO,
			icon: Ext6.Msg.QUESTION,
			fn: function(btn) {
				if ( btn === 'yes' ) {
					win.mask(LOAD_WAIT_DELETE);

					Ext6.Ajax.request({
						callback: function(opt, success, response) {
							win.unmask();

							if ( success && response.responseText != '' ) {
								win.Grid.getStore().reload();
							}
						},
						params: {
							TariffValue_id: record.get('TariffValue_id')
						},
						url: '/?c=TariffValue&m=delete'
					});
				}
			}
		});
	},
	doReset: function () {
		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		base_form.findField('Lpu_id').enable();
		
		if ( !isSuperAdmin() || this.ARMType != 'superadmin' ) {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_id').disable();
		}

		this.doSearch();
	},
	doSearch: function (mode) {
		var
			base_form = this.FilterPanel.getForm(),
			params = base_form.getValues();

		params.limit = this.GridStore.pageSize;
		params.start = 0;

		if ( base_form.findField('Lpu_id').disabled ) {
			params.Lpu_id = base_form.findField('Lpu_id').getValue();
		}

		this.Grid.getStore().removeAll();
		Ext6.apply(this.Grid.getStore().proxy.extraParams, params);
		this.Grid.getStore().load();
	},
	import: function() {
		getWnd('swTariffValueImportWindow').show();
	},
	onLoadGrid: function() {

	},
	onRecordSelect: function() {

	},
	open: function(action) {
		if ( typeof action != 'string' || (action != 'add' && action != 'edit') ) {
			return false;
		}

		var
			formParams = new Object(),
			params = new Object(),
			win = this;
		
		if ( action == 'edit' ) {
			if ( win.Grid.getSelectionModel().hasSelection()) {
				var record = win.Grid.getSelectionModel().getSelection()[0];

				if ( typeof record != 'object' || Ext6.isEmpty(record.get('TariffValue_id')) ) {
					return false;
				}

				formParams.TariffValue_id = record.get('TariffValue_id');
			}
			else {
				return false;
			}
		}

		params.action = action;
		params.callback = function() {
			win.Grid.getStore().reload();
		};
		params.formParams = formParams;

		getWnd('swTariffValueEditWindow').show(params);
	},
	show: function() {
		this.callParent(arguments);

		this.ARMType = (arguments[0] && arguments[0].ARMType ? arguments[0].ARMType : null);

		if ( isSuperAdmin() && this.ARMType == 'superadmin' ) {
			this.Grid.down("#addbutton").enable();
			this.Grid.down("#editbutton").enable();
			this.Grid.down("#deletebutton").enable();
		}
		else {
			this.Grid.down("#addbutton").disable();
			this.Grid.down("#editbutton").disable();
			this.Grid.down("#deletebutton").disable();
		}

		this.doReset();
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		win.FilterPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 5px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 200,
				listeners: {
					specialkey: function(field, e, eOpts) {
						if (e.getKey() == e.ENTER) {
							setTimeout(function() { // таймаут, т.к. specialkey срабатывает быстрее чем селектится значение в комбике по forceSelection.. надо думать как реализовать по нормальному
								win.doSearch();
							}, 200);
						}
					}
				}
			},
			region: 'north',
			items: [{
				listConfig: {
					minWidth: 800,
					resizable: true
				},
				name: 'Lpu_id',
				width: 800,
				xtype: 'swLpuCombo'
			}, {
				border: false,
				defaults: {
					labelAlign: 'right',
					listeners: {
						specialkey: function(field, e, eOpts) {
							if (e.getKey() == e.ENTER) {
								setTimeout(function() {
									win.doSearch();
								}, 200);
							}
						}
					}
				},
				layout: 'column',
				items: [{
					fieldLabel: 'Период даты начала: с',
					format: 'd.m.Y',
					labelWidth: 200,
					name: 'TariffValue_begDT_From',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					width: 340,
					xtype: 'datefield'
				}, {
					fieldLabel: 'по',
					format: 'd.m.Y',
					labelWidth: 30,
					name: 'TariffValue_begDT_To',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					width: 170,
					xtype: 'datefield'
				}]
			}, {
				border: false,
				defaults: {
					labelAlign: 'right',
					listeners: {
						specialkey: function(field, e, eOpts) {
							if (e.getKey() == e.ENTER) {
								setTimeout(function() {
									win.doSearch();
								}, 200);
							}
						}
					}
				},
				layout: 'column',
				style: 'margin: 5px 0px;',
				items: [{
					fieldLabel: 'Период даты окончания: с',
					format: 'd.m.Y',
					labelWidth: 200,
					name: 'TariffValue_endDT_From',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					width: 340,
					xtype: 'datefield'
				}, {
					fieldLabel: 'по',
					format: 'd.m.Y',
					labelWidth: 30,
					name: 'TariffValue_endDT_To',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					width: 170,
					xtype: 'datefield'
				}]
			}, {
				fieldLabel: 'Код',
				name: 'TariffValue_Code',
				width: 400,
				xtype: 'textfield'
			}, {
				border: false,
				layout: 'column',
				items: [{
					style: 'margin-left: 205px;',
					text: 'Найти',
					xtype: 'button',
					iconCls: 'search16',
					handler: function() {
						win.doSearch();
					}
				}, {
					text: 'Сброс',
					xtype: 'button',
					style: 'margin-left: 10px;',
					iconCls: 'reset16',
					handler: function() {
						win.doReset();
					}
				}]
			}]
		});

		win.GridStore = Ext6.create('Ext.data.Store', {
			groupField: 'ARMType_Name',
			fields: [
				{ name: 'TariffValue_id', type: 'int' },
				{ name: 'TariffValue_Code', type: 'string' },
				{ name: 'TariffValue_Value', type: 'float' },
				{ name: 'TariffValue_begDT', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'TariffValue_endDT', type: 'date', dateFormat: 'd.m.Y' }
			],
			pageSize: 100,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=TariffValue&m=loadList',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			sorters: [
				'TariffValue_Code'
			],
			listeners: {
				load: function() {
					win.onLoadGrid();
				}
			}
		});

		win.Grid = new Ext6.grid.Panel({
			columns: [
				{text: 'Код', flex: 1, minWidth: 100, dataIndex: 'TariffValue_Code'},
				{text: 'Значение', width: 200, dataIndex: 'TariffValue_Value', renderer: function(value) {
					return (!Ext.isEmpty(value) ? value.toFixed(2) : '');
				}},
				{text: 'Дата начала', width: 100, dataIndex: 'TariffValue_begDT', formatter: 'date("d.m.Y")'},
				{text: 'Дата окончания', width: 100, dataIndex: 'TariffValue_endDT', formatter: 'date("d.m.Y")'}
			],
			dockedItems: [{
				displayInfo: true,
				dock: 'bottom',
				store: win.GridStore,
				xtype: 'pagingtoolbar'
			}],
			features: [ ],
			region: 'center',
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			store: win.GridStore,
			tbar: Ext6.create('Ext.toolbar.Toolbar', {
				items: [{
					iconCls: 'add16',
					itemId: 'addbutton',
					handler: function() {
						win.open('add');
					},
					text: 'Добавить',
					xtype: 'button'
				}, {
					iconCls: 'edit16',
					itemId: 'editbutton',
					handler: function() {
						win.open('edit');
					},
					text: 'Изменить',
					xtype: 'button'
				}, {
					iconCls: 'delete16',
					itemId: 'deletebutton',
					handler: function() {
						win.delete();
					},
					text: 'Удалить',
					xtype: 'button'
				}, {
					disabled: (!isSuperAdmin() && !isLpuAdmin(getGlobalOptions().lpu_id)),
					iconCls: 'actions16',
					itemId: 'importbutton',
					handler: function() {
						win.import();
					},
					text: 'Импорт',
					xtype: 'button'
				}]
			})
		});

        Ext6.apply(win, {
			items: [
				win.FilterPanel,
				win.Grid
			],
			buttons: [
				'->',
				sw4.getHelpButton(win, -1),
				{
					handler:function () {
						win.hide();
					},
					text: BTN_FRMCLOSE
				}
			]
		});

		this.callParent(arguments);
    }
});