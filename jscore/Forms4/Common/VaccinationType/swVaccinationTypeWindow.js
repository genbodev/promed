/**
 * swVaccinationTypeWindow - Окно отображения списка видов профилактических прививок
 * common.VaccinationType.swVaccinationTypeWindow
 * widget.swVaccinationTypeWindo
 * PromedWeb - The New Generation of Medical Statistic Software
 * https://rtmis.ru/
 *
 *
 * @package      Common
 * @access       public
 */

Ext6.define('common.VaccinationType.swVaccinationTypeWindow', {
	noCloseOnTaskBar: false,
	extend: 'base.BaseForm',
	alias: 'widget.swVaccinationTypeWindow',
	maximized: true,
	refId: 'polkawp',
	findWindow: false,
	closable: true,
	frame: false,
	cls: 'arm-window-new',
	title: 'Виды профилактических прививок',
	header: true,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	onDblClick: function () {
		var win = this;
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('VaccinationType_id')) {
				if (!win.mainGrid.down('#action_edit').isDisabled()) {
					win.openEditWindow((this.action == 'view')?'view':'edit');
				}
			}
		}
	},
	onRecordSelect: function () {
		var win = this;

		win.mainGrid.down('#action_edit').disable();
		win.mainGrid.down('#action_view').disable();
		win.mainGrid.down('#action_delete').disable();

		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];

			if (record.get('VaccinationType_id')) {
				win.mainGrid.down('#action_edit').enable();
				win.mainGrid.down('#action_view').enable();
				win.mainGrid.down('#action_delete').enable();
			}
		}
	},
	setViewMode: function(action){
		var win = this;

		win.mainGrid.down('#action_add').setVisible(action !== 'view');
		win.mainGrid.down('#action_edit').setVisible(action !== 'view');
		win.mainGrid.down('#action_view').setVisible(action == 'view');
		win.mainGrid.down('#action_delete').setVisible(action !== 'view');
	},
	getGrid: function () {
		return this.mainGrid;
	},
	getSelectedRecord: function () {
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('VaccinationType_id')) {
				return record;
			}
		}
		return false;
	},
	show: function () {
		this.callParent(arguments);
		var win = this;

		if(arguments[0].action)
			this.action = arguments[0].action;

		win.doReset();
	},
	doReset: function () {
		var base_form = this.filterPanel.getForm();
		base_form.reset();
		this.mainGrid.getStore().removeAll();
		this.setViewMode(this.action);
		this.onRecordSelect();
		this.doSearch();
	},
	doSearch: function (options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var base_form = this.filterPanel.getForm();
		var extraParams = base_form.getValues();

		win.mainGrid.getStore().proxy.extraParams = extraParams;

		win.mainGrid.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	openEditWindow: function (action) {
		var win = this;
		var record = this.getSelectedRecord();

		if (!record && action !== 'add') return false;

		if( action == 'edit' || action == 'view' ) {
			var fields = {
				VaccinationType_id: (record.get('VaccinationType_id')) ? record.get('VaccinationType_id') : '',
				VaccinationType_Code: (record.get('VaccinationType_Code')) ? record.get('VaccinationType_Code') : '',
				VaccinationType_Name: (record.get('VaccinationType_Name')) ? record.get('VaccinationType_Name') : '',
				VaccinationType_isReaction: (record.get('VaccinationType_isReaction')) ? record.get('VaccinationType_isReaction') : '',
				VaccinationType_begDate: (record.get('VaccinationType_begDate')) ? record.get('VaccinationType_begDate') : '',
				VaccinationType_endDate: (record.get('VaccinationType_endDate')) ? record.get('VaccinationType_endDate') : '',
			}
		}

		var params = {
			action: action,
			fields: fields,
			owner: this,
			callback: function (owner) {
				win.mainGrid.getStore().reload();
			}
		}

		if( action == 'add' )
			getWnd('swVaccinationTypeAddWindow').show(params);
		else
			getWnd('swVaccinationTypeEditWindow').show(params);

	},
	deleteVaccinationType: function () {
		var win = this;
		var record = this.getSelectedRecord();

		if (!record) return false;

		Ext6.Msg.show({
			title: langs('Подтверждение удаления'),
			msg: langs('Вы действительно желаете удалить этот вид вакцинации?'),
			buttons: Ext6.Msg.YESNO,
			fn: function (buttonId) {
				if (buttonId == 'yes') {
					win.mask('Удаление вида вакцинации');
					Ext6.Ajax.request({
						url: '/?c=VaccinationType&m=deleteVaccinationType',
						params: { VaccinationType_id: record.get('VaccinationType_id') },
						callback: function (o, s, r) {
							win.unmask();
							if (s) { win.mainGrid.getStore().reload(); }
						}
					});
				}
			}
		});
	},
	initComponent: function () {
		var win = this;

		win.filterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			border: false,
			cls: 'vaccinationtype-search-input-panel',
			region: 'north',
			items: [{
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'anchor',
					items: [
						Ext6.create('Ext6.date.RangeField', {
							labelWidth: 55,
							width: 265,
							height:32,
							xtype: 'daterangefield',
							fieldLabel: 'Период',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
							name: 'VaccinationType_DateRange',
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
						})
					]
				},{
					border: false,
					layout: 'anchor',
					margin: '0 0 0 20',
					items: [{
						boxLabel: langs('Национальный календарь'),
						xtype: 'checkbox',
						inputValue: 1,
						name: 'Vaccination_isNacCal',
						labelWidth: 65,
						width: 190,
						listeners: {
							change: function (checkbox, newVal, oldVal) {
								win.doSearch();
							}
						}
					}]
				}, {
					border: false,
					layout: 'anchor',
					items: [{
						boxLabel: langs('Эпидемиологические показания'),
						xtype: 'checkbox',
						inputValue: 1,
						name: 'Vaccination_isEpidemic',
						labelWidth: 65,
						width: 230,
						listeners: {
							change: function (checkbox, newVal, oldVal) {
								win.doSearch();
							}
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
			tbar: {
				xtype: 'toolbar',
				padding: '0 0',
				defaults: {	margin: '0 4 0 0' },
				height: 40,
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [
					win.filterPanel,
					'->',
				{
					text: 'Добавить',
					itemId: 'action_add',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_add',
					handler: function () {
						win.openEditWindow('add');
					}
				}, {
					text: 'Редактировать',
					itemId: 'action_edit',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_edit',
					handler: function () {
						win.openEditWindow('edit');
					}
				}, {
					text: 'Посмотреть',
					itemId: 'action_view',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_view',
					handler: function () {
						win.openEditWindow('view');
					}
				}, {
					text: 'Удалить',
					itemId: 'action_delete',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_delete',
					handler: function () {
						win.deleteVaccinationType();
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
					{name: 'VaccinationType_id', type: 'int'},
					{name: 'VaccinationType_Code'},
					{name: 'VaccinationType_Name'},
					{name: 'Vaccination_isNacCal'},
					{name: 'Vaccination_isEpidemic'},
					{name: 'Vaccination_isReaction'},
					{name: 'ExamString'},
					{name: 'minAge'},
					{name: 'VaccinationType_begDate', type: 'string'},
					{name: 'VaccinationType_endDate', type: 'string'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=VaccinationType&m=loadVaccinationTypes',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'VaccinationType_id'
				],
				listeners: {
					load: function () {
						win.onRecordSelect();
					}
				}
			},
			columns: [
				{text: 'Код', tdCls: 'padLeft', width: 150, dataIndex: 'VaccinationType_Code', flex: 2},
				{text: 'Вид прививки / реакции', width: 300, dataIndex: 'VaccinationType_Name', flex: 4},
				{text: 'Прививка / Реакция', width: 300, dataIndex: 'VaccinationType_isReaction', hidden: true},
				{text: 'Нац. календарь', width: 120, dataIndex: 'Vaccination_isNacCal', flex: 1, renderer: function(v) {
						if (v=='true') return "<div style='text-align: center;'><img src='/img/icons/checked16.png' /></div>";
					}},
				{text: 'Эпид. показания', width: 120, dataIndex: 'Vaccination_isEpidemic', flex: 1, renderer: function(v) {
						if (v=='true') return "<div style='text-align: center;'><img src='/img/icons/checked16.png' /></div>";
					}},
				{text: 'Необходимость осмотра', width: 170, dataIndex: 'ExamString', flex:3},
				{text: 'Возраст первой прививки', width: 100, minWidth: 100, dataIndex: 'minAge', flex: 1},
				{text: 'Начало', width: 90, minWidth: 90, dataIndex: 'VaccinationType_begDate', flex: 1},
				{text: 'Окончание', width: 90, minWidth: 90, dataIndex: 'VaccinationType_endDate', flex: 1}
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
			items: [ win.mainGrid ]
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
