/**
* swSignaViewWindow - окно просмотраb редактирования Сигны.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Ivan Pshenitcyn aka IVP (ipshon@rambler.ru)
* @version      18.09.2009
*/

sw.Promed.swSignaViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	deleteSigna: function() {
		var current_window = this;
		var grid = current_window.findById('SVW_SignaGrid').ViewGridPanel;

		if ( !grid || !grid.getSelectionModel().getSelected() )
		{
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var signa_id = selected_record.get('Signa_id');

		if ( !signa_id )
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_signa']);
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes')
				{
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_signa_voznikli_oshibki']);
						},
						params: {
							Signa_id: signa_id
						},
						success: function(response, options) {
							grid.getStore().remove(selected_record);

							if (grid.getStore().getCount() > 0)
							{
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						url: C_SIGNA_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_signa'],
			title: lang['vopros']
		});
	},
	draggable: true,
	height: 400,
	id: 'SignaViewWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}
			],
			items: [
				new sw.Promed.ViewFrame(
				{
					actions:
					[
						{ name: 'action_add', handler: function() { Ext.getCmp('SignaViewWindow').openSignaEditWindow('add'); } },
						{ name: 'action_edit' },
						{ name: 'action_view' },
						{ name: 'action_delete', handler: function() { Ext.getCmp('SignaViewWindow').deleteSigna(); } },
						{ name: 'action_refresh', disabled: true },
						{ name: 'action_print' }
					],
					autoLoadData: false,
					autoExpandColumn: 'autoexpand',
					border: false,
					dataUrl: C_SIGNA_LIST,
					id: 'SVW_SignaGrid',
					region: 'center',
					stringfields:
					[
						{ name: 'Signa_id', type: 'int', hidden: true },
						{ name: 'Signa_Name', id: 'autoexpand', type: 'string', header: lang['signa'] }
					],
					toolbar: true
				})
			]
		});
		sw.Promed.swSignaViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keyPressListener: function(e) {
		var current_window = Ext.getCmp('SignaViewWindow');
		var grid = current_window.findById('SVW_SignaGrid').ViewGridPanel;

		if ( !grid.getSelectionModel().getSelected() )
		{
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var signa_data = new Object();

		signa_data.Signa_id = selected_record.get('Signa_id');
		signa_data.Signa_Name = selected_record.get('Signa_Name');

		if ( e.getKey() == e.ENTER )
		{
			current_window.hide();
			current_window.callback({ SignaData: signa_data });
			e.stopEvent();
		}
		else if ( e.getKey() == e.F3 )
		{
			current_window.openSignaEditWindow('view');
			e.stopEvent();
		}
		else if ( e.getKey() == e.F4 )
		{
			current_window.openSignaEditWindow('edit');
			e.stopEvent();
		}
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			if ( e.getKey() == Ext.EventObject.P )
			{
				var current_window = Ext.getCmp('SignaViewWindow');
				current_window.hide();
			}
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function() {
			this.findById('SVW_SignaGrid').removeAll();
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 400,
	minWidth: 600,
	modal: true,
	openSignaEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view')
		{
			return false;
		}

		var current_window = this;

		if (getWnd('swSignaEditWindow').isVisible())
		{
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_signa_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var signa_grid = current_window.findById('SVW_SignaGrid').ViewGridPanel;

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.SignaData )
			{
				signa_grid.getStore().reload();
			}
			else
			{
				// Добавить или обновить запись в signa_grid
				var index = signa_grid.getStore().findBy(function(record, id) {
					if (data.SignaData.Signa_id == record.get('Signa_id'))
					{
						return true;
					}
					else
					{
						return false;
					}
				});
				var record = signa_grid.getStore().getAt(index);

				if (record)
				{
					// Обновление
					record.set('Signa_Name', data.SignaData.Signa_Name);

					record.commit();
				}
				else
				{
					// Добавление
					if (signa_grid.getStore().getCount() == 1 && !signa_grid.getStore().getAt(0).get('Signa_id'))
					{
						signa_grid.getStore().removeAll();
					}

					signa_grid.getStore().loadData([ data.SignaData ], true);
				}
			}
		};

		if (action == 'add')
		{
			params.onHide = function() {
				if ( signa_grid.getStore().getCount > 0 )
				{
					signa_grid.getSelectionModel().selectFirstRow();
					signa_grid.getView().focusRow(0);
				}
			};
			params.Signa_id = 0;

			getWnd('swSignaEditWindow').show( params );
		}
		else
		{
			if ( !signa_grid.getSelectionModel().getSelected() )
			{
				return false;
			}

			var selected_record = signa_grid.getSelectionModel().getSelected();

			params.onHide = function() {
				signa_grid.getView().focusRow(signa_grid.getStore().indexOf(selected_record));
			};
			params.Signa_id = selected_record.get('Signa_id');
			params.Signa_Name = selected_record.get('Signa_Name');

			getWnd('swSignaEditWindow').show( params );
		}
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swSignaViewWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.callback = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;

		if (arguments[0])
		{
			if (arguments[0].callback)
			{
				current_window.callback = arguments[0].callback;
			}

			if (arguments[0].onHide)
			{
				current_window.onHide = arguments[0].onHide;
			}
		}

		// загрузка списка
		var grid = current_window.findById('SVW_SignaGrid').ViewGridPanel;
		grid.getStore().removeAll();
		grid.getStore().load();

		// прописываем реакцию на дабл клик по строке и по клавише Enter
		grid.removeListener('rowdblclick');
		grid.on('rowdblclick', function(grd, index) {
			current_window.callback({
				SignaData: {
					Signa_id: grd.getStore().getAt(index).get('Signa_id'),
					Signa_Name: grd.getStore().getAt(index).get('Signa_Name')
				}
			});
			current_window.hide();
		});

		if ( !grid.ownerCt.ownerCt.ViewActions.action_edit.items[1].hasListener('click') )
		{
			grid.ownerCt.ownerCt.ViewActions.action_edit.items[1].on('click', function() { current_window.openSignaEditWindow('edit'); } );
		}

		if ( !grid.ownerCt.ownerCt.ViewActions.action_view.items[1].hasListener('click') )
		{
			grid.ownerCt.ownerCt.ViewActions.action_view.items[1].on('click', function() { current_window.openSignaEditWindow('view'); } );
		}

		grid.removeListener('keypress', current_window.keyPressListener);
		grid.on('keypress', current_window.keyPressListener);

		current_window.restore();
		current_window.center();
	},
	title: lang['signa_prosmotr'],
	width: 600
});
