/**
 * swExpQueryViewWindow - окно просмотра списка файлов информационного обмена с АО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.11.2013
 */

sw.Promed.swExpQueryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	border : false,
	closeAction :'hide',
	height: 500,
	width: 800,
	id: 'ExpQueryViewWindow',
	title: langs('ЛЛО. Информационный обмен с АО. Список файлов'),
	layout: 'border',
	modal: true,
	maximized: true,

	openExpQueryEditWindow: function(action)
	{
		if (!action || !action.inlist(['add','edit','view'])) {
			return false;
		}

		if ( this.action == 'view' ) {
			if ( action == 'add' ) {
				return false;
			}
			else if ( action == 'edit' ) {
				action = 'view';
			}
		}

		if ( getWnd('swExpQueryEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['oshibka'], lang['okno_redaktirovaniya_fayla_informatsionnogo_obmena_uje_otkryito']);
			return false;
		}

		var grid = this.QueryGrid.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			this.QueryGrid.ViewActions.action_refresh.execute();
		}.createDelegate(this);

		params.formParams = new Object();

		if ( action == 'add' ) {
			params.onHide = function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
				}
			};
		} else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Query_id') ) {
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.formParams.Query_id = record.get('Query_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
			};
		}

		getWnd('swExpQueryEditWindow').show(params);
	},

	deleteQuery: function()
	{
		var wnd = this;

		question = lang['udalit_zapros_tak_je_budut_udalenyi_vse_polya_zaprosa'];

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var grid = wnd.QueryGrid.getGrid();

					var idField = 'Query_id';

					if ( !grid || !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(idField) ) {
						return false;
					}

					var record = grid.getSelectionModel().getSelected();

					var url = '/?c=exp_Query&m=deleteQuery';
					var record = grid.getSelectionModel().getSelected();
					var params = new Object();
					params.Query_id = record.get('Query_id');

					Ext.Ajax.request({
						callback: function(options, success, response) {
							if (success) {
								grid.getStore().load();
							} else {
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
							}
						}.createDelegate(this),
						params: params,
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	
	show: function() 
	{
		sw.Promed.swExpQueryViewWindow.superclass.show.apply(this, arguments);

		if (!isSuperAdmin() || getGlobalOptions().region.nick=='kz') {
			sw.swMsg.alert(lang['soobschenie'], lang['net_dostupa_k_forme'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var grid = this.QueryGrid.getGrid();

		grid.getStore().load();

		loadMask.hide();
	},

	initComponent: function() 
	{
		var wnd = this;

		this.QueryGrid = new sw.Promed.ViewFrame({
			id: 'EQVW_QueryGrid',
			region: 'center',
			object: 'exp_Query',
			dataUrl: '/?c=exp_Query&m=loadQueryGrid',
			paging: false,
			autoLoadData: false,
			root: 'data',
			stringfields:
				[
					{name: 'Query_id', type: 'int', header: 'ID', key: true},
					{name: 'Query_Nick', type: 'string', hidden: true},
					{name: 'Ord', header: lang['nomer'], type: 'int', width: 60},
					{name: 'Name', header: lang['naimenovanie'], type: 'string', id: 'autoexpand'},
					{name: 'Filename', header: lang['fayl'], type: 'string', width: 300}
				],
			actions:
				[
					{name:'action_add', handler: function (){wnd.openExpQueryEditWindow('add');}},
					{name:'action_edit', handler: function (){wnd.openExpQueryEditWindow('edit');}},
					{name:'action_view', handler: function (){wnd.openExpQueryEditWindow('view');}},
					{name:'action_delete', handler: function (){wnd.deleteQuery();}}
				],
			onDblClick: function()
			{
				if ( !this.ViewActions.action_edit.isDisabled() ) {
					this.ViewActions.action_edit.execute();
				}
			}
		});

		Ext.apply(this, {
			items : [
				this.QueryGrid
			],
			buttons : [
			'-',
			HelpButton(this, -1),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCANCEL
			}],
			buttonAlign : "right"
		});
		sw.Promed.swExpQueryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});