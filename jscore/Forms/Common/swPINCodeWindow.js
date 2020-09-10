/**
 * swPINCodeWindow - окно добавления/редактирования списка доступных локальных справочников
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      2012.08
 * @comment      Функционал для ввода пинкода
 *
 * @input data:
 * 		action - действие (add, edit, view)
 *     	LocalDbList_id - Id строки таблицы
 */
sw.Promed.swPINCodeWindow = Ext.extend(sw.Promed.BaseForm, {
	title: lang['pin-kod'],
	autoHeight: true,
	callback: Ext.emptyFn,
	onHide: Ext.emptyFn,
	width: 300,
	layout: 'form',
	id: 'swPINCodeWindow',
	modal: true,
	plain: true,
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	submit: function() {
		var w = this;
		if (!w.fieldForm.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					w.fieldForm.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['pin-kod_obyazatelen_dlya_zapolneniya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		this.getLoadMask(lang['podojdite']).show();
		this.onHide = Ext.emptyFn; // просто скрываем форму, без вызова onHide
		// todo: думать над скрытием формы !
		this.hide();
		if (typeof this.callback == 'function') {
			this.callback({'pin': w.fieldForm.getForm().findField('pin').getValue()});
			this.getLoadMask().hide();
		}
	},
	show: function() {
		sw.Promed.swPINCodeWindow.superclass.show.apply(this, arguments);
		var w = this;
		w.fieldForm.getForm().reset();
		w.callback = Ext.emptyFn;
		w.onHide = Ext.emptyFn;
		this.showMsg(null);
		if (arguments[0]) {
			if (arguments[0].callback) {
				w.callback = arguments[0].callback;
			}
			if (arguments[0].onHide) {
				w.onHide = arguments[0].onHide;
			}
			if (arguments[0].params) {
				this.showMsg((arguments[0].params.msg)?arguments[0].params.msg:null);
			}
		}


		this.fieldForm.getForm().findField('pin').focus(100, true);
	},
	showMsg: function(msg) {
		var p = (msg)?{text:msg}:{text:'&nbsp;'};
		this.TextTpl.overwrite(this.TextPanel.body, p);
		this.TextPanel.render();
		this.syncShadow();
	},
	initComponent: function() {
		// Форма с полями
		var form = this;

		var TextTplMark =[
			'<div style="font-size: 11px;color:red;font-weight:bold;">{text}</div>'
		];
		this.TextTpl = new Ext.Template(TextTplMark);

		this.TextPanel = new Ext.Panel({
			html: '&nbsp;',
			//style: 'margin-left:55px',
			id: 'pincodeTextPanel',
			autoHeight: true
		});

		this.fieldForm = new Ext.form.FormPanel({
			autoHeight: true,
			bodyStyle: 'padding: 5px',
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'DBLocalListEditForm',
			labelAlign: 'top',
			labelWidth: 50,
			items:[{
				xtype: 'textfield',
				anchor: '100%',
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['vvedite_pin-kod'],
				inputType: 'password',
				name: 'pin',
				tabIndex: 9
			}, this.TextPanel],
			keys: [{
				alt: false,
				fn: function(inp, e) {
					switch (e.getKey()) {
						case Ext.EventObject.ENTER:
							this.submit(false);
							break;
					}
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
					success: function() {
						//
					}
				},
				[{ name: 'pin' }])
		});
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.submit();
				},
				iconCls: 'ok16',
				text: lang['ok'],
				tabIndex: 10
			}, {
				text: '-'
			},
			HelpButton(this, 11), {
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				// tabIndex: 207,
				text: BTN_FRMCANCEL,
				tabIndex: 12
			}],
			items: [form.fieldForm]
		});
		sw.Promed.swPINCodeWindow.superclass.initComponent.apply(this, arguments);
	}
});