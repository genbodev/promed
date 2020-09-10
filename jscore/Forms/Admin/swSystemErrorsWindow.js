/**
 * swSystemErrorsWindow - окно просмотра списка ошибок системы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Admin
 * @access            public
 * @copyright        Copyright (c) 2014 Swan Ltd.
 * @author            Dmitriy Vlasenko
 * @version            30.12.2014
 */

/*NO PARSE JSON*/

sw.Promed.swSystemErrorsWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSystemErrorsWindow',
	width: 800,
	height: 450,
	maximizable: true,
	maximized: true,
	layout: 'border',
	title: lang['oshibki'],
	callback: Ext.emptyFn,
	show: function () {
		sw.Promed.swSystemErrorsWindow.superclass.show.apply(this, arguments);

		this.doResetFilters();
		this.doFilter();
	},
	doResetFilters: function () {
		var filtersForm = this.filtersPanel.getForm();
		filtersForm.reset();
		filtersForm.findField('SystemError_Date_From').setValue(getGlobalOptions().date);
		filtersForm.findField('SystemError_Date_To').setValue(getGlobalOptions().date);
	},
	doFilter: function () {
		var filtersForm = this.filtersPanel.getForm();
		var filters = filtersForm.getValues();
		filters.start = 0;
		filters.limit = 100;

		this.GridPanel.loadData({globalFilters: filters});
	},
	openSystemErrorsViewWindow: function () {
		var grid = this.GridPanel.getGrid();

		if (grid.getSelectionModel().getSelected()) {
			var record = grid.getSelectionModel().getSelected();
			getWnd('swSystemErrorsViewWindow').show({
				'SystemError_id': record.get('SystemError_id')
			});
		}
	},
	initComponent: function () {
		var win = this;

		this.filtersPanel = new Ext.FormPanel(
			{
				xtype: 'form',
				region: 'north',
				labelAlign: 'right',
				layout: 'form',
				autoHeight: true,
				labelWidth: 50,
				frame: true,
				border: false,
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function (e) {
						win.doFilter();
					},
					stopEvent: true
				}],
				items: [{
					listeners: {
						collapse: function (p) {
							win.doLayout();
						},
						expand: function (p) {
							win.doLayout();
						}
					},
					xtype: 'fieldset',
					style: 'margin: 5px 5px 5px 5px',
					title: lang['filtryi'],
					collapsible: true,
					autoHeight: true,
					labelWidth: 200,
					anchor: '-10',
					layout: 'form',
					items: [{
						fieldLabel: lang['kod_oshibki'],
						name: 'SystemError_Code',
						xtype: 'numberfield'
					}, {
						fieldLabel: lang['oshibka'],
						name: 'SystemError_Error',
						xtype: 'textfield'
					}, {
						border: false,
						layout: 'column',
						anchor: '-10',
						items: [{
							layout: 'form',
							border: false,
							width: 310,
							labelWidth: 200,
							items: [{
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								width: 100,
								name: 'SystemError_Date_From',
								fieldLabel: lang['period_ot']
							}]
						}, {
							layout: 'form',
							border: false,
							width: 125,
							labelWidth: 15,
							items: [{
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								width: 100,
								name: 'SystemError_Date_To',
								fieldLabel: lang['do']
							}]
						}]
					}],
					buttons: [
						{
							text: lang['filtr'],
							handler: function () {
								win.doFilter();
							},
							iconCls: 'search16'
						},
						{
							text: BTN_RESETFILTER,
							handler: function () {
								win.doResetFilters();
								win.doFilter();
							},
							iconCls: 'resetsearch16'
						},
						'-'
					]
				}]
			});

		this.GridPanel = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{name: 'action_edit', hidden: true, disabled: true},
				{
					name: 'action_view', handler: function () {
					win.openSystemErrorsViewWindow();
				}
				},
				{name: 'action_delete', hidden: true, disabled: true}
			],
			region: 'center',
			dataUrl: '/?c=Common&m=loadSystemErrorGrid',
			object: 'SystemError',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			saveFixed: function(record) {
				win.getLoadMask(lang['pojaluysta_podojdite']).show();
				Ext.Ajax.request({
					url: '/?c=Common&m=saveSystemErrorFixed',
					params: {
						SystemError_id: record.get('SystemError_id'),
						SystemError_Fixed: record.get('SystemError_Fixed')?1:0
					},
					callback: function(o, success, response) {
						win.getLoadMask().hide();
						if( success ) {
							var obj = Ext.decode(response.responseText);
							if( obj.success ) {
								record.set('SystemError_Fixed', record.get('SystemError_Fixed'));
								record.commit();
							}
						}
					}
				});
			},
			saveRecord: function(o) {
				var viewframe = this;
				var record = o.record;

				viewframe.saveFixed(record);
			},
			stringfields: [
				{name: 'SystemError_id', type: 'int', header: 'ID', key: true},
				{name: 'SystemError_Code', type: 'int', header: lang['kod_oshibki'], width: 50},
				{name: 'SystemError_Error', type: 'string', header: lang['oshibka'], width: 240, id: 'autoexpand'},
				{name: 'SystemError_Login', type: 'string', header: lang['login'], width: 100},
				{name: 'SystemError_Date', type: 'date', header: lang['data'], width: 100},
				{name: 'SystemError_Window', type: 'string', header: lang['forma'], width: 200},
				{name: 'SystemError_Url', type: 'string', header: lang['adres'], width: 200},
				{name: 'SystemError_Params', type: 'string', header: lang['parametryi'], width: 200},
				{name: 'SystemError_Count', type: 'int', header: lang['kolichestvo'], width: 50},
				{name: 'SystemError_OpenUrl', type: 'string', header: lang['ssyilka'], width: 150},
				{name: 'SystemError_Fixed', type: 'checkcolumnedit', header: lang['ispravleno'], width: 50}
			]
		});

		Ext.apply(this, {
			items: [
				this.filtersPanel,
				this.GridPanel
			],
			buttons: [
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'close16',
					text: BTN_FRMCLOSE
				}]
		});

		sw.Promed.swSystemErrorsWindow.superclass.initComponent.apply(this, arguments);
	}
});