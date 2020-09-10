/**
 * swDrugDocumentStatusHistoryWindow - окно просмотра истории статусов документа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Farmacy
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			20.01.2014
 */

/*NO PARSE JSON*/

sw.Promed.swDrugDocumentStatusHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugDocumentStatusHistoryWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	maximizable: true,
	title: lang['istoriya_zayavok_na_medikamentyi'],

	DocumentUc_id: null,

	show: function() {
		sw.Promed.swDrugDocumentStatusHistoryWindow.superclass.show.apply(this, arguments);

		var grid = this.GridPanel.getGrid();

		if (arguments[0].DocumentUc_id) {
			this.DocumentUc_id = arguments[0].DocumentUc_id;
		}

		grid.getStore().load({params: {DocumentUc_id: this.DocumentUc_id}});
	},

	initComponent: function() {
		var wnd = this;

		wnd.GridPanel = new sw.Promed.ViewFrame({
			id: 'DDSHW_DrugDocumentStatusHistoryGrid',
			region: 'center',
			dataUrl: '/?c=Farmacy&m=loadDrugDocumentStatusHistoryGrid',
			object: 'DrugDocumentStatusHistory',
			autoLoadData: false,
			root: 'data',

			stringfields:
				[
					{name: 'DrugDocumentStatusHistory_id', header: 'ID', key: true},
					{name: 'DocumentUc_id', type: 'int', hidden: true},
					{name: 'pmUser_userID', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_id', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_Code', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_Name', header: lang['status'], type: 'string', width: 240},
					{name: 'DrugDocumentStatusHistory_setDate', header: lang['data'], type: 'datetimesec', width: 200},
					{name: 'pmUser_Fio', header: lang['polzovatel'], type: 'string', id: 'autoexpand'}
				],
			actions:
				[
					{name:'action_add', disabled: true, hidden: true},
					{name:'action_edit', disabled: true, hidden: true},
					{name:'action_view', disabled: true, hidden: true},
					{name:'action_delete', disabled: true, hidden: true}
				]
		});

		Ext.apply(this, {
			items: [
				wnd.GridPanel
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
					id: 'DDSHW_CancelButton',
					text: '<cite>З</cite>акрыть'
				}
			]
		});

		sw.Promed.swDrugDocumentStatusHistoryWindow.superclass.initComponent.apply(this, arguments);
	}
});
