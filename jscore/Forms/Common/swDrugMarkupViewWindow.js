/**
 * swDrugMarkupEditWindow - окно просмотра списка величин надбавок
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      01.2014
 * @comment
 */
sw.Promed.swDrugMarkupViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: lang['predelnyie_nadbavki_na_jnvlp'],
	layout: 'border',
	id: 'DrugMarkupViewWindow',
	modal: false,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	show: function() {
		var wnd = this;
		sw.Promed.swDrugMarkupViewWindow.superclass.show.apply(this, arguments);

		this.readOnly = false;

		if (arguments[0] && arguments[0].readOnly) {
			this.readOnly = arguments[0].readOnly;
		}

		this.action = 'edit';

		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.DataGrid.setReadOnly(this.readOnly || this.action != 'edit');
	},
	initComponent: function() {
		var wnd = this;

		wnd.DataGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible()},
				{name: 'action_edit', hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible()},
				{name: 'action_view'},
				{name: 'action_delete', hidden: getWnd('swWorkPlaceSpecMEKLLOWindow').isVisible(), url: '\?c=DrugMarkup&m=delete'},
				{name: 'action_print'}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: true,
			border: true,
			dataUrl: '/?c=DrugMarkup&m=loadList',
			height: 180,
			region: 'center',
			object: 'DrugMarkup',
			editformclassname: 'swDrugMarkupEditWindow',
			id: 'DrugMarkupGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{name: 'DrugMarkup_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugMarkup_begDT', type: 'date', header: lang['nachalo_deystviya'], width: 120},
				{name: 'DrugMarkup_endDT', type: 'date', header: lang['okonchanie_deystviya'], width: 120},
				{name: 'DrugMarkup_MinPrice', type: 'money', header: lang['min_tsena'], width: 120},
				{name: 'DrugMarkup_MaxPrice', type: 'money', header: lang['maks_tsena'], width: 120},
				{name: 'DrugMarkup_Wholesale', type: 'float', header: lang['opt_nadb_%'], width: 120},
				{name: 'DrugMarkup_Retail', type: 'float', header: lang['rozn_nadb_%'], width: 120},
				{name: 'DrugMarkup_IsNarkoDrug_Name', type: 'string', header: lang['nark_preparat'], width: 120},
				{name: 'DrugMarkup_IsNarkoDrug', type: 'int', hidden: true},
				{name: 'Drugmarkup_Delivery', type: 'string', header: lang['zona_dostavki'], width: 120},
				{name: 'File_Name', type: 'string', header: lang['dokument'], width: 120, id: 'autoexpand'}
			],
			toolbar: true
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
				[{
					text: '-'
				},
					HelpButton(this, 0),
					{
						handler: function() {
							this.ownerCt.hide();
						},
						iconCls: 'cancel16',
						text: BTN_FRMCANCEL
					}],
			items:[
				wnd.DataGrid
			]
		});
		sw.Promed.swDrugMarkupViewWindow.superclass.initComponent.apply(this, arguments);
	}
});