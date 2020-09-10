/**
 * swUnitSprViewWindow - справочник единиц измерения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Vlasenko Dmitriy
 * @version			28.01.2014
 */

/*NO PARSE JSON*/

sw.Promed.swUnitSprViewWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swUnitSprViewWindow',
	width: 800,
	height: 600,
	callback: Ext.emptyFn,
	layout: 'border',
	modal: true,
	maximizable: true,
	title: lang['edinitsyi_izmereniya'],

	openRecordEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {
			return;
		}
		var wnd = this;

		var grid = wnd.GridPanel.getGrid();

		var params = new Object();

		var record = grid.getSelectionModel().getSelected();
		if (action.inlist(['edit','view'])) {
			params['Okei_id'] = record.get('Okei_id');
			params['Unit_id'] = record.get('Unit_id');
			params['UnitSpr_id'] = record.get('UnitSpr_id');
		}

		params.action = action;

		params.callback = function(data) {
			wnd.GridPanel.ViewActions.action_refresh.execute();
		}.createDelegate(this);

		getWnd(wnd.GridPanel.editformclassname).show(params);
	},

	deleteRecord: function() {
		var wnd = this;
		var question = lang['udalit_edinitsu_izmereniya'];
		var grid = wnd.GridPanel.getGrid();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var params = new Object();
					var deleteUrl = '/?c=UnitSpr&m=deleteOkei';
					var record = grid.getSelectionModel().getSelected();
					if (record && !Ext.isEmpty(record.get('Okei_id'))) {
						params['Okei_id'] = record.get('Okei_id');
						deleteUrl = '/?c=UnitSpr&m=deleteOkei';
					} else if (record && !Ext.isEmpty(record.get('Unit_id'))) {
						params['Unit_id'] = record.get('Unit_id');
						deleteUrl = '/?c=UnitSpr&m=deleteUnit';
					} else {
						return false;
					}

					wnd.getLoadMask("Удаление записи...").show();

					Ext.Ajax.request({
						callback:function (options, success, response) {
							wnd.getLoadMask().hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == true) {
									grid.getStore().remove(record);
								}
								if (grid.getStore().getCount() > 0) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
						},
						params: params,
						url: deleteUrl
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},

	show: function() {
		sw.Promed.swUnitSprViewWindow.superclass.show.apply(this, arguments);

		this.action = 'edit';
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.GridPanel.setReadOnly(this.action != 'edit');

		this.center();

		var grid = this.GridPanel.getGrid();

		grid.getStore().load();
	},

	initComponent: function() {
		var wnd = this;

		wnd.GridPanel = new sw.Promed.ViewFrame({
			id: 'USVW_UnitSprGrid',
			region: 'center',
			dataUrl: '/?c=UnitSpr&m=loadUnitSprGrid',
			editformclassname: 'swUnitSprEditWindow',
			autoLoadData: false,
			stringfields:
				[
					{name: 'UnitSpr_id', header: 'ID', key: true},
					{name: 'Unit_id', header: 'Unit_id', hidden: true},
					{name: 'Okei_id', header: 'Okei_id', hidden: true},
					{name: 'UnitSpr_Code', header: lang['kod'], type: 'int', width: 60},
					{name: 'UnitType_Name', header: lang['tip_spravochnika'], type: 'string', width: 120},
					{name: 'UnitSpr_Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
					{name: 'IsLinked', header: lang['svyazannyie_znacheniya'], type: 'checkbox', width: 120},
					{name: 'UnitSpr_begDate', header: lang['data_nachala'], type: 'string', width: 120},
					{name: 'UnitSpr_endDate', header: lang['data_okonchaniya'], type: 'string', width: 120}
				],
			actions:
				[
					{name:'action_add', handler: function(){wnd.openRecordEditWindow('add');}},
					{name:'action_edit', handler: function(){wnd.openRecordEditWindow('edit');}},
					{name:'action_view', handler: function(){wnd.openRecordEditWindow('view');}},
					{name:'action_delete', handler: function (){
						wnd.deleteRecord();
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
					text: lang['zakryit']
				}
			]
		});

		sw.Promed.swUnitSprViewWindow.superclass.initComponent.apply(this, arguments);
	}
});

