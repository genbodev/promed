/**
* swEvnReceptDeleteWindow - форма выбора типа направления
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      05.05.2012
*/

sw.Promed.swEvnReceptDeleteWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteEvnRecept: function() {
		var base_form = this.FormPanel.getForm();
		var DeleteType = this.DeleteType;
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: getRegionNick() == 'msk' ? 'Аннулирование рецепта...' : 'Удаление записи...' });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_retsepta']);
					}
					else {
						this.callback();
						this.hide();
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_retsepta_voznikli_oshibki']);
				}
			}.createDelegate(this),
			params: {
				 EvnRecept_id: base_form.findField('EvnRecept_id').getValue()
				,ReceptRemoveCauseType_id: base_form.findField('ReceptRemoveCauseType_id').getValue()
				,DeleteType: DeleteType
			},
			url: C_EVNREC_DEL
		});
	},
	draggable: true,
	id: 'EvnReceptDeleteWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnReceptDeleteForm',
			labelAlign: 'right',
			labelWidth: 150,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'EvnRecept_id' },
				{ name: 'ReceptRemoveCauseType_id' }
			]),
			style: 'padding: 5px',

			items: [{
				name: 'EvnRecept_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'ReceptRemoveCauseType',
				fieldLabel: langs('Причина ' + (getRegionNick() == 'msk' ? 'аннулирования' : 'удаления')),
				hiddenName: 'ReceptRemoveCauseType_id',
				anchor: '95%',
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.deleteEvnRecept();
				}.createDelegate(this),
				iconCls: 'delete16',
				onShiftTabAction: function () {
					//
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				text: lang['udalit_retsept']
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.FormPanel.getForm().findField('ReceptRemoveCauseType_id').focus(true);
				}.createDelegate(this),
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnReceptDeleteWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnReceptDeleteWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnReceptDeleteWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.clearInvalid();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.DeleteType = 0;
		if ( !arguments[0] || !arguments[0].EvnRecept_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.findField('EvnRecept_id').setValue(arguments[0].EvnRecept_id);

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].DeleteType ) {
			this.DeleteType = arguments[0].DeleteType;
		}
		if(this.DeleteType == 0) {
			this.buttons[0].setText('Пометить рецепт к удалению');
		}
		else
		{
			this.buttons[0].setText((getRegionNick() == 'msk' ? 'Аннулировать' : 'Удалить') + ' рецепт');
		}

		base_form.findField('ReceptRemoveCauseType_id').focus(true, 250);
	},
	title: langs((getRegionNick() == 'msk' ? 'Аннулирование' : 'Удаление') + ' рецепта'),
	width: 600
});