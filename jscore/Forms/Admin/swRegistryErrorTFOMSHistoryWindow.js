/**
 * swRegistryErrorTFOMSHistoryWindow - окно просмотра списка ошибок системы
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

sw.Promed.swRegistryErrorTFOMSHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 800,
	height: 450,
	maximizable: true,
	maximized: true,
	layout: 'border',
	title: 'История ошибок',
	callback: Ext.emptyFn,
	show: function () {
		sw.Promed.swRegistryErrorTFOMSHistoryWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.Registry_id = arguments[0].Registry_id;

		this.doResetFilters();
		this.doFilter();
	},
	doResetFilters: function () {
		var filtersForm = this.filtersPanel.getForm();
		filtersForm.reset();
	},
	doFilter: function () {
		var filtersForm = this.filtersPanel.getForm();
		var filters = filtersForm.getValues();
		filters.Registry_id = this.Registry_id;
		filters.loadHistory = 1;
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
					labelWidth: 200,
					anchor: '-10',
					layout: 'form',
					items: [{
						border: false,
						layout: 'column',
						anchor: '-10',
						items: [{
							layout: 'form',
							border: false,
							labelWidth: 100,
							items: [{
								fieldLabel: lang['kod_oshibki'],
								name: 'RegistryErrorType_Code',
								xtype: 'numberfield'
							}]
						}, {
							layout: 'form',
							border: false,
							labelWidth: 50,
							items: [{
								xtype: 'swdatefield',
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								width: 100,
								name: 'Registry_CheckStatusDate',
								fieldLabel: 'Дата'
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
			dataUrl: '/?c=Registry&m=loadRegistryErrorTFOMS',
			object: 'RegistryErrorTFOMS',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields: [
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_CheckStatusDate', type: 'date', header: 'Дата', width: 80},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden:false},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
				{name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorTFOMSLevel_Name', header: 'Уровень ошибки', width: 120},
				{name: 'RegistryError_FieldName', header: 'Ошибка', width: 250},
				{name: 'RegistryError_Comment', header: 'Описание ошибки', autoexpand: true},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},

				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'RegistryErrorTFOMS_Source', type: 'string', header: 'Источник', width: 150},
				{name: 'UslugaComplex_Code', type: 'string', header: 'Код услуги', width: 100},
				{name: 'UslugaComplex_Name', type: 'string', header: 'Наименование услуги', width: 200},
				{name: 'RegistryErrorTFOMS_FieldName', hidden:true},
				{name: 'RegistryErrorTFOMS_BaseElement', hidden:true},
				{name: 'IsGroupEvn', type: 'int', hidden:true}
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

		sw.Promed.swRegistryErrorTFOMSHistoryWindow.superclass.initComponent.apply(this, arguments);
	}
});