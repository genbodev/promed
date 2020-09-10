/**
 * swDrugDocumentStatusViewWindow - окно просмотра статусов заявок на медикаменты
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

sw.Promed.swDrugDocumentStatusViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swDrugDocumentStatusViewWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	maximizable: true,
	title: lang['statusyi_zayavok_na_medikamentyi'],

	DrugDocumentType_id: null,

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

		if (this.DrugDocumentType_id) {
			params.DrugDocumentType_id = this.DrugDocumentType_id;
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
		var question = options.question;
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
								if (response_obj.success) {
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
		sw.Promed.swDrugDocumentStatusViewWindow.superclass.show.apply(this, arguments);

		this.center();

		var grid = this.GridPanel.getGrid();

		//Загрузка списка статусов для внутренних заявок
		this.DrugDocumentType_id = 9;

		var params = new Object();
		if (this.DrugDocumentType_id) {
			params.DrugDocumentType_id = this.DrugDocumentType_id;
		}

		grid.getStore().load({params: params});
	},

	initComponent: function() {
		var wnd = this;

		wnd.GridPanel = new sw.Promed.ViewFrame({
			id: 'DDSVW_DrugDocumentStatusGrid',
			region: 'center',
			dataUrl: '/?c=DrugDocument&m=loadDrugDocumentStatusGrid',
			editformclassname: 'swDrugDocumentStatusEditWindow',
			autoLoadData: false,
			root: 'data',

			stringfields:
				[
					{name: 'DrugDocumentStatus_id', header: 'ID', key: true},
					{name: 'DrugDocumentType_id', type: 'int', hidden: true},
					{name: 'DrugDocumentStatus_Code', header: lang['kod'], type: 'int', width: 120},
					{name: 'DrugDocumentStatus_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'}
				],
			actions:
				[
					{name:'action_add', handler: function(){wnd.openRecordEditWindow('add', wnd.GridPanel);}},
					{name:'action_edit', handler: function(){wnd.openRecordEditWindow('edit', wnd.GridPanel);}},
					{name:'action_view', handler: function(){wnd.openRecordEditWindow('view', wnd.GridPanel);}},
					{name:'action_delete', handler: function (){
						var options = { url: '/?c=DrugDocument&m=deleteDrugDocumentStatus', question: lang['udalit_status'] };
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
					id: 'DDSVW_CancelButton',
					text: lang['zakryit']
				}
			]
		});

		sw.Promed.swDrugDocumentStatusViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
