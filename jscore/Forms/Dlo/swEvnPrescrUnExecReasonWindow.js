/**
 * swEvnPrescrUnExecReasonWindow - форма выбора причины невыполнения назначения
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

sw.Promed.swEvnPrescrUnExecReasonWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	setUnExecReason: function() {
		var base_form = this.FormPanel.getForm();
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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Сохранение..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
					}
					else {
						this.callback();
						this.hide();
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошла ошибка'));
				}
			}.createDelegate(this),
			params: {
				EvnPrescr_id: base_form.findField('EvnPrescr_id').getValue(),
				PrescrFailureType_id: base_form.findField('PrescrFailureType_id').getValue()
			},
			url: '/?c=EvnPrescr&m=saveEvnPrescrUnExecReason'
		});
	},
	draggable: true,
	id: 'EvnPrescrUnExecReasonWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnPrescrUnExecReasonForm',
			labelAlign: 'right',
			labelWidth: 160,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'EvnPrescr_id' },
				{ name: 'PrescrFailureType_id' }
			]),
			style: 'padding: 5px',

			items: [{
				name: 'EvnPrescr_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'PrescrFailureType',
				fieldLabel: langs('Причина невыполнения'),
				hiddenName: 'PrescrFailureType_id',
				width: 300,
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.setUnExecReason();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					//
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				text: langs('Сохранить')
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
						this.FormPanel.getForm().findField('PrescrFailureType_id').focus(true);
					}.createDelegate(this),
					text: BTN_FRMCANCEL
				}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnPrescrUnExecReasonWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrUnExecReasonWindow');

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
		sw.Promed.swEvnPrescrUnExecReasonWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		base_form.clearInvalid();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		if ( !arguments[0] || !arguments[0].EvnPrescr_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		base_form.findField('EvnPrescr_id').setValue(arguments[0].EvnPrescr_id);

		if (!Ext.isEmpty(arguments[0].PrescrFailureType_id)) {
			base_form.findField('PrescrFailureType_id').setValue(arguments[0].PrescrFailureType_id);
			this.setTitle(langs('Причина невыполнения назначения: Редактирование'));
		}
		else {
			this.setTitle(langs('Причина невыполнения назначения: Добавление'));
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		base_form.findField('PrescrFailureType_id').focus(true, 250);
	},
	width: 530
});