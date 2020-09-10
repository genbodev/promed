/**
* swAnamnezViewWindow - окно просмотра и редактирования анамнеза.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      02.12.2009
*/

sw.Promed.swAnamnezViewWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	anamnezType: 0,
	id: 'swAnamnezViewWindow',	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	deleteAnamnez: function() {
		var current_window = this;
		var grid = current_window.findById('AVW_AnamnezGrid').ViewGridPanel;

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		var anamnez_id = selected_record.get('Anamnez_id');
		var anamnez_type_id = selected_record.get('AnamnezType_id');

		if ( !anamnez_id ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_zapisi_voznikli_oshibki']);
						},
						params: {
							Anamnez_id: anamnez_id,
							AnamnezType_id: anamnez_type_id
						},
						success: function(response, options) {
							grid.getStore().remove(selected_record);

							if ( grid.getStore().getCount() > 0 ) {
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						},
						url: C_ANAMNEZ_DEL
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_zapis'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;

		current_window.findById('AVW_AnamnezEditForm').getForm().reset();
		current_window.findById('AVW_SearchFilterForm').getForm().reset();
		current_window.findById('AVW_AnamnezGrid').removeAll();
		current_window.setWindowAction('add');
/*
		current_window.findById('AVW_AnamnezGrid').ViewGridPanel.getStore().loadData([{
			Anamnez_id: 1,
			AnamnezType_id: 1,
			Anamnez_Title: lang['testovyiy_zagolovok_dlya_anamneza']
		}]);
*/
	},
	doSave: function() {
		var current_window = this;
		var params = new Object();

		params.anamnezType = current_window.anamnezType;
	},
	doSearch: function() {
		var current_window = this;

		current_window.findById('AVW_AnamnezGrid').removeAll();
		current_window.findById('AVW_AnamnezGrid').loadData({
			Anamnez_Text: current_window.findById('AVW_SearchFilterForm').getForm().findField('Anamnez_Text').getRawValue(),
			Anamnez_Title: current_window.findById('AVW_SearchFilterForm').getForm().findField('Anamnez_Title').getRawValue(),
			AnamnezType_id: 1
		});
	},
	doSelect: function() {
		var current_window = this;
		var params = new Object();

		params.Anamnez_id = current_window.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_id').getValue();
		params.Anamnez_Text = current_window.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_Text').getValue();
		params.Anamnez_Title = current_window.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_Title').getValue();

		current_window.callback(params);
		current_window.hide();
	},
	draggable: true,
	height: 500,
	id: 'AnamnezViewWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: "Сохранить"
			}, {
				handler: function() {
					this.ownerCt.doSelect();
				},
				iconCls: 'ok16',
				text: "Выбрать"
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [ new Ext.form.FormPanel({
				bodyStyle: 'padding-top: 3px;',
				buttonAlign: 'left',
				buttons: [{
					handler: function() {
						this.ownerCt.ownerCt.doSearch();
					},
					iconCls: 'search16',
					text: BTN_FRMSEARCH
				}],
				frame: true,
				height: 120,
				id: 'AVW_SearchFilterForm',
				items: [{
					fieldLabel: lang['zagolovok'],
					name: 'Anamnez_Title',
					width: 450,
					xtype: 'textfield'
				}, {
					fieldLabel: lang['tekst'],
					name: 'Anamnez_Text',
					width: 450,
					xtype: 'textfield'
				}],
				labelAlign: 'right',
				region: 'north',
				title: lang['poisk']
			}),
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('AnamnezViewWindow').setWindowAction('add'); } },
					{ name: 'action_edit', disabled: true },
					{ name: 'action_view', disabled: true },
					{ name: 'action_delete', handler: function() { Ext.getCmp('AnamnezViewWindow').deleteAnamnez(); } },
					{ name: 'action_refresh', disabled: true },
					{ name: 'action_print' }
				],
				autoLoadData: false,
				autoExpandColumn: 'autoexpand',
				border: false,
				dataUrl: C_ANAMNEZ_LIST,
				id: 'AVW_AnamnezGrid',
				onRowSelect: function(sm, rowIdx, record) {
					var current_window = Ext.getCmp('AnamnezViewWindow');
					current_window.setWindowAction('edit');
					current_window.buttons[1].enable();
					current_window.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_id').setRawValue(record.get('Anamnez_id'));
					current_window.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_Title').setRawValue(record.get('Anamnez_Title'));
					// + загрузить значение поля Anamnez_Text
					current_window.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_Text').setRawValue('<Текст загружается с сервера>');
				},
				region: 'center',
				stringfields: [
					{ name: 'Anamnez_id', type: 'int', header: 'ID', key: true },
					{ name: 'AnamnezType_id', type: 'int', hidden: true },
					{ name: 'Anamnez_Title', id: 'autoexpand', type: 'string', header: lang['anamnez'] }
				],
				toolbar: true
			}),
			new Ext.form.FormPanel({
				bodyStyle: 'padding-top: 3px;',
				height: 150,
				id: 'AVW_AnamnezEditForm',
				items: [{
					name: 'Anamnez_id',
					value: 0,
					xtype: 'hidden'
				}, {
					fieldLabel: lang['zagolovok'],
					name: 'Anamnez_Title',
					width: 450,
					xtype: 'textfield'
				}, {
					fieldLabel: lang['tekst'],
					height: 80,
					name: 'Anamnez_Text',
					width: 450,
					xtype: 'textarea'
				}],
				labelAlign: 'right',
				region: 'south',
				title: lang['dobavlenie']
			})]
		});
		sw.Promed.swAnamnezViewWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			if ( e.getKey() == Ext.EventObject.P ) {				var current_window = Ext.getCmp('AnamnezViewWindow');
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
			this.findById('AVW_AnamnezGrid').ViewGridPanel.getStore().removeAll();
			this.onHide();
		}
	},
	maximizable: true,
	minHeight: 500,
	minWidth: 600,
	modal: true,
	plain: true,
	resizable: true,
	setWindowAction: function(action) {
		if ( action != 'add' && action != 'edit' ) {
			return false;
		}

		this.findById('AVW_AnamnezEditForm').getForm().reset();

		switch ( action ) {
			case 'add':
				this.buttons[1].disable();
				this.findById('AVW_AnamnezEditForm').setTitle(lang['_dobavlenie']);
				this.findById('AVW_AnamnezEditForm').getForm().findField('Anamnez_Title').focus(100);
			break;

			case 'edit':
				this.buttons[1].enable();
				this.findById('AVW_AnamnezEditForm').setTitle(lang['_redaktirovanie']);
			break;
		}

		return true;
	},
	show: function() {
		sw.Promed.swAnamnezViewWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.anamnezType = 0;
		current_window.doSelect = Ext.emptyFn;
		current_window.onHide = Ext.emptyFn;

		if ( arguments[0] ) {
			if ( arguments[0].anamnezType ) {
				current_window.anamnezType = arguments[0].anamnezType;
			}

			if ( arguments[0].callback ) {
				current_window.callback = arguments[0].callback;
			}

			if ( arguments[0].onHide ) {
				current_window.onHide = arguments[0].onHide;
			}
		}

		current_window.restore();
		current_window.center();

		current_window.doReset();

		switch ( current_window.anamnezType ) {
			case 1:
				current_window.setTitle(lang['anamnez_obyektivnyie_dannyie']);
			break;

			case 2:
				current_window.setTitle(lang['anamnez_obsledovanie']);
			break;

			case 3:
				current_window.setTitle(lang['anamnez_naznachennoe_lechenie']);
			break;

			case 4:
				current_window.setTitle(lang['anamnez_rekomendatsii']);
			break;

			default:
				current_window.hide();
			break;
		}
	},
	width: 600
});
