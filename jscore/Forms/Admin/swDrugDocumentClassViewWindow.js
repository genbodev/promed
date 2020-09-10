/**
 * swDrugDocumentClassViewWindow - окно просмотра видов заявок на медикаменты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			21.01.2014
 */

/*NO PARSE JSON*/

sw.Promed.swDrugDocumentClassViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugDocumentClassViewWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	maximizable: true,
	title: lang['vidyi_zayavok_na_medikamentyi'],

	openRecordEditWindow: function(action, gridCmp) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}
		var wnd = this;

		var grid = gridCmp.getGrid();
		var idField = grid.getStore().idProperty;

		var params = new Object();

		var record = grid.getSelectionModel().getSelected();
		if (action.inlist(['edit','view'])) {
			params[idField] = record.get(idField);
		}

		params.action = action;

		params.callback = function(data) {
			gridCmp.ViewActions.action_refresh.execute();
		}.createDelegate(this);

		getWnd(gridCmp.editformclassname).show(params);
	},

	deleteRecord: function(gridCmp, options) {
		if (!options || !options.url) {
			return;
		}
		var wnd = this;
		var question = lang['udalit_vid_zayavki_na_medikamentyi'];
		var grid = gridCmp.getGrid();
		var idField = grid.getStore().idProperty;

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();

					var params = new Object();
					params[idField] = record.get(idField);

					wnd.getLoadMask("Удаление записи...").show();

					Ext.Ajax.request({
						callback:function (options, success, response) {
							wnd.getLoadMask().hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
						},
						params: params,
						url: options.url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},

	show: function() {
		sw.Promed.swDrugDocumentClassViewWindow.superclass.show.apply(this, arguments);

		this.center();

		var grid = this.GridPanel.getGrid();

		grid.getStore().load();
	},

	initComponent: function() {
		var wnd = this;

		wnd.GridPanel = new sw.Promed.ViewFrame({
			id: 'DDCVW_DrugDocumentClassGrid',
			region: 'center',
			dataUrl: '/?c=DrugDocument&m=loadDrugDocumentClassGrid',
			editformclassname: 'swDrugDocumentClassEditWindow',
			autoLoadData: false,
			root: 'data',

			stringfields:
				[
					{name: 'DrugDocumentClass_id', header: 'ID', key: true},
					{name: 'DrugDocumentClass_Code', header: lang['kod'], type: 'int', width: 120},
					{name: 'DrugDocumentClass_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
					{name: 'DrugDocumentClass_Nick', header: lang['kratkoe_nimenovanie'], type: 'string', width: 300}
				],
			actions:
				[
					{name:'action_add', handler: function(){wnd.openRecordEditWindow('add', wnd.GridPanel);}},
					{name:'action_edit', handler: function(){wnd.openRecordEditWindow('edit', wnd.GridPanel);}},
					{name:'action_view', handler: function(){wnd.openRecordEditWindow('view', wnd.GridPanel);}},
					{name:'action_delete', handler: function (){
						var options = {url: '/?c=DrugDocument&m=deleteDrugDocumentClass'};
						wnd.deleteRecord(wnd.GridPanel, options);
					}},
					{name:'action_refresh'},
					{name:'action_print'}
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
					id: 'DDCVW_CancelButton',
					text: lang['zakryit']
				}
			]
		});

		sw.Promed.swDrugDocumentClassViewWindow.superclass.initComponent.apply(this, arguments);
	}
});

