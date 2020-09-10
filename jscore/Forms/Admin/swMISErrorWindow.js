/**
 * swMISErrorWindow - окно просмотра списка ошибок отправки в МИС РБ
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

sw.Promed.swMISErrorWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swMISErrorWindow',
	width: 800,
	height: 450,
	maximizable: true,
	maximized: true,
	layout: 'border',
	title: 'Журнал ошибок передачи данных в ИЭМК',
	callback: Ext.emptyFn,
	show: function () {
		sw.Promed.swMISErrorWindow.superclass.show.apply(this, arguments);

		this.doResetFilters();
		this.doFilter();
	},
	doResetFilters: function () {
		var filtersForm = this.filtersPanel.getForm();
		filtersForm.reset();
		filtersForm.findField('MISError_setDT_From').setValue(getGlobalOptions().date);
		filtersForm.findField('MISError_setDT_To').setValue(getGlobalOptions().date);
	},
	doFilter: function () {
		var filtersForm = this.filtersPanel.getForm();
		var filters = filtersForm.getValues();
		filters.start = 0;
		filters.limit = 100;

		this.GridPanel.loadData({globalFilters: filters});
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
					labelWidth: 100,
					anchor: '-10',
					layout: 'form',
					items: [{
						border: false,
						layout: 'column',
						anchor: '-10',
						items: [{
							layout: 'form',
							border: false,
							width: 210,
							labelWidth: 100,
							items: [{
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								width: 100,
								name: 'MISError_setDT_From',
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
								name: 'MISError_setDT_To',
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
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true}
			],
			region: 'center',
			dataUrl: '/?c=MisRB&m=loadMISErrorGrid',
			object: 'MISError',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields: [
				{name: 'MISError_id', type: 'int', header: 'ID', key: true},
				{name: 'MISError_setDT', type: 'datetime', header: 'Дата/время запроса', width: 100},
				{name: 'Lpu_Nick', type: 'string', header: 'МО запуска', width: 200},
				{name: 'MISError_QueryName', type: 'string', header: 'Тип запроса', width: 100},
				{name: 'MISError_ErrorCode', type: 'string', header: 'Код ошибки', width: 100},
				{name: 'MISError_ErrorMessage', type: 'string', header: 'Текст ошибки', width: 200, id: 'autoexpand'},
				{name: 'Evn_id', type: 'string', header: 'ИД объекта', width: 200}
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

		sw.Promed.swMISErrorWindow.superclass.initComponent.apply(this, arguments);
	}
});