/**
 * swOnlineUsersWindow - Пользователи онлайн
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */
Ext6.define('common.swOnlineUsersWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swOnlineUsersWindow',
    autoShow: false,
	maximized: false,
	width: 800,
	height: 600,
	resizable: false,
	maximizable: true,
	refId: 'onlineusersw',
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'Пользователи онлайн',
    header: true,
	renderTo: main_center_panel.body.dom,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	show: function() {
		this.callParent(arguments);
		var win = this;

		var base_form = win.FilterPanel.getForm();
		base_form.findField('Org_id').getStore().proxy.extraParams.Org_IsAccess = 2;
		base_form.findField('OrgType_id').getStore().proxy.extraParams.Org_IsAccess = 2;
		base_form.findField('OrgType_id').getStore().load();

		this.doReset();
	},
	onRecordSelect: function() {

	},
	onLoadGrid: function() {
		var win = this;
		win.onRecordSelect();

		var cnt = 0;

		win.Grid.getStore().each(function(rec){
			if (rec.get('Users_Count') && rec.get('Users_Count') > 0) {
				cnt = cnt + rec.get('Users_Count');
			}
		});

		win.TotalPanel.body.setHtml('<div style="float:left;">Актуальность данных на ' + new Date().format('H:i:s d.m.Y') + '</div><div style="float:right">Всего пользователей онлайн: ' + cnt +'</div>');

		var base_form = win.FilterPanel.getForm();
		if (base_form.findField('showLpu').checked) {
			// раскрыть все группы
			win.Grid.features[0].expandAll();
		} else {
			// свернуть все группы
			win.Grid.features[0].collapseAll();
		}
	},
	doSearch: function (mode) {
		var params = this.FilterPanel.getForm().getValues();
		this.Grid.getStore().removeAll();
		this.Grid.getStore().load({params: params});
	},
	doReset: function () {
		var base_form = this.FilterPanel.getForm();
		base_form.reset();

		var record = base_form.findField('OrgType_id').getStore().findRecord('OrgType_id', base_form.findField('OrgType_id').getValue());
		base_form.findField('OrgType_id').fireEvent('select', base_form.findField('OrgType_id'), [record]);

		this.TotalPanel.body.setHtml('');
		this.doSearch();
	},
    initComponent: function() {
        var win = this;

		var groupingFeature = new Ext6.grid.feature.GroupingSummary({
			showSummaryRow: false,
			groupHeaderTpl: new Ext6.XTemplate(
				'<table class="x4-{rows:this.getViewId}-table x4-grid-table" cellspacing="0" cellpadding="0" border="0"><colgroup role="presentation"><col role="presentation" class="x4-grid-cell-headerId-{rows:this.getCol1Id}"></colgroup><colgroup role="presentation"><col role="presentation" class="x4-grid-cell-headerId-{rows:this.getCol2Id}"></colgroup><tbody><tr><td>{name} <span class=\'titleCount\'>{rows:this.formatRows}</span></td></tr></tbody></table>',
				{
					getCol1Id: function(rows) {
						return win.Grid.columns[0].id;
					},
					getCol2Id: function(rows) {
						return win.Grid.columns[1].id;
					},
					getViewId: function(rows) {
						return win.Grid.view.id;
					},
					formatRows: function(rows) {
						var cnt = 0;
						rows.forEach(function(rec) {
							if (rec.get('Users_Count') && rec.get('Users_Count') > 0) {
								cnt = cnt + rec.get('Users_Count');
							}
						});
						return cnt;
					}
				}
			)
		});

		win.Grid = new Ext6.grid.Panel({
			xtype: 'grid',
			region: 'center',
			features: [groupingFeature],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			tbar: Ext6.create('Ext.toolbar.Toolbar', {
				items: [{
					xtype: 'button',
					text: 'Обновить',
					iconCls: 'refresh16',
					handler: function(){
						win.doSearch();
					}
				}, {
					xtype: 'button',
					text: 'Печать',
					iconCls: 'print16',
					handler: function(){
						Ext6.ux.GridPrinter.print(win.Grid);
					}
				}]
			}),
			store: {
				groupField: 'ARMType_Name',
				fields: [
					{ name: 'OnlineUsers_id', type: 'int' },
					{ name: 'ARMType_Name', type: 'string' },
					{ name: 'Org_Nick', type: 'string' },
					{ name: 'Users_Count', type: 'int' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=User&m=loadOnlineUsersList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'Person_Fio'
				],
				listeners: {
					load: function() {
						win.onLoadGrid();
					}
				}
			},
			columns: [
				{text: 'Организация', flex: 1, minWidth: 100, dataIndex: 'Org_Nick', summaryType: function(records) {
					return "Всего";
				}},
				{text: 'Количество пользователей', width: 200, dataIndex: 'Users_Count', summaryType: 'sum'}
			]
		});

		win.FilterPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 10px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 110,
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
				xtype: 'swOrgCombo',
				width: 600,
				fieldLabel: 'Организация',
				listeners: {
					'select': function(combo, records, eOpts) {
						var base_form = win.FilterPanel.getForm();

						if (records[0]) {
							base_form.findField('OrgType_id').setValue(records[0].get('OrgType_id'));

							var record = base_form.findField('OrgType_id').getStore().findRecord('OrgType_id', base_form.findField('OrgType_id').getValue());
							base_form.findField('OrgType_id').fireEvent('select', base_form.findField('OrgType_id'), [record]);
						}
					}
				},
				onTrigger2Click: function() {
					if (this.disabled) return false;
					var combo = this;

					var base_form = win.FilterPanel.getForm();

					getWnd('swOrgSearchWindow').show({
						//object: 'org',
						enableOrgType: combo.enableOrgType,
						defaultOrgType: base_form.findField('OrgType_id').getValue(),
						allowEmptyUAddress: combo.allowEmptyUAddress,
						disableEdit: true,
						onHide: function() {
							combo.focus(false);
						},
						onSelect: function(orgData) {
							combo.getStore().removeAll();
							combo.getStore().loadData([{
								Org_id: orgData.Org_id,
								Org_Name: orgData.Org_Name,
								Org_Nick: orgData.Org_Nick,
								OrgType_id: orgData.OrgType_id
							}]);
							combo.setValue(orgData.Org_id);

							var index = combo.getStore().find('Org_id', orgData.Org_id);

							if (index == -1)
							{
								return false;
							}

							var record = combo.getStore().getAt(index);
							combo.fireEvent('select', combo, record, 0);
							combo.fireEvent('change', combo, combo.getValue());

							getWnd('swOrgSearchWindow').hide();
						}
					});
				},
				name: 'Org_id'
			}, {
				xtype: 'swOrgTypeCombo',
				width: 500,
				fieldLabel: 'Тип организации',
				listeners: {
					'select': function(combo, records, eOpts) {
						var newValue = null;
						if (records[0]) {
							var newValue = records[0].get('OrgType_id');
						}
						var base_form = win.FilterPanel.getForm();
						base_form.findField('Org_id').getStore().proxy.extraParams.OrgType_id = newValue;
						base_form.findField('Org_id').lastQuery = 'This query sample that is not will never appear';
						if (!Ext6.isEmpty(newValue) && base_form.findField('Org_id').getFieldValue('OrgType_id') != newValue) {
							base_form.findField('Org_id').clearValue();
						}
					}
				},
				name: 'OrgType_id'
			}, {
				xtype: 'swARMTypeCombo',
				width: 500,
				fieldLabel: 'АРМ',
				valueField: 'ARMType_SysNick',
				name: 'ARMType_SysNick'
			}, {
				border: false,
				layout: 'column',
				items: [{
					xtype: 'checkbox',
					boxLabel: 'Показать МО',
					fieldLabel: 'Показать МО',
					hideLabel: true,
					style: 'margin-left: 115px;',
					width: 100,
					name: 'showLpu',
					listeners: {
						'change': function() {
							var base_form = win.FilterPanel.getForm();
							if (base_form.findField('showLpu').checked) {
								// раскрыть все группы
								win.Grid.features[0].expandAll();
							} else {
								// свернуть все группы
								win.Grid.features[0].collapseAll();
							}
						}
					}
				}, {
					style: 'margin-left: 95px;',
					text: 'Найти',
					xtype: 'button',
					width: 90,
					iconCls: 'search16',
					handler: function() {
						win.doSearch();
					}
				}, {
					text: 'Сброс',
					xtype: 'button',
					width: 90,
					style: 'margin-left: 10px;',
					iconCls: 'reset16',
					handler: function() {
						win.doReset();
					}
				}]
			}]
		});

		win.TotalPanel = new Ext6.Panel({
			html: '',
			frame: true,
			region: 'south',
			border: false,
			padding: 5,
			height: 30
		});

        Ext6.apply(win, {
			items: [
				win.FilterPanel,
				win.Grid,
				win.TotalPanel
			],
			buttons: ['->', {
				handler:function () {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}]
		});

		this.callParent(arguments);
    }
});