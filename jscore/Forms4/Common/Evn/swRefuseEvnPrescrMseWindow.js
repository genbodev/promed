/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.05.2009
*/

/**
 * swRefuseEvnPrescrMseWindow - окно для выбора причины отказа в направление на МСЭ
 *
 * @class sw.Promed.swRefuseEvnPrescrMseWindow
 * @extends sw.Promed.BaseForm
 */
Ext6.define('common.Evn.swRefuseEvnPrescrMseWindow', {
	extend: 'base.BaseForm',
	autoHeight: true,
	border: false,
	closable: true,
	closeAction:'hide',
	modal: true,
	plain: false,
	resizable: false,
	title: langs('Причина отказа в направление на МСЭ'),
	winTitle: langs('Причина отказа в направление на МСЭ'),
	width: 500,
	listeners: {
		'hide': function(win) {
			if (win._isCancel) win.onHideFunc();
		}
	},
	show: function() {
		this.callParent(arguments);
		this.setTitle(arguments[0].winTitle || this.winTitle);
		this._isCancel = true;
		// Функция вызывающаяся после выбора причины установки статуса
		this.callback = (typeof arguments[0].callback == 'function') ? arguments[0].callback : Ext6.emptyFn;
		// Функция вызывающаяся при отмене выбора причины установки статуса
		this.onHideFunc = (typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext6.emptyFn;
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		var EvnStatusHistory_Cause = '';
		if(arguments[0] && arguments[0].EvnVK_DecisionVK)
			EvnStatusHistory_Cause = arguments[0].EvnVK_DecisionVK.substr(0,199);
		base_form.findField('EvnStatusHistory_Cause').setValue(EvnStatusHistory_Cause);
	},
	onSprLoad: function(args) {

	},
	save: function() {
		var base_form = this.FormPanel.getForm();
		if (!base_form.isValid()) {
			Ext6.Msg.alert(langs('Ошибка заполнения формы'), langs('Проверьте правильность заполнения полей формы.'));
			return false;
		}
		this.callback({
			EvnStatusHistory_Cause: base_form.findField('EvnStatusHistory_Cause').getValue()
		});
		this._isCancel = false;
		this.hide();
		return true;
	},
	initComponent: function() {
		var win = this;
		
		this.FormPanel = Ext6.create('Ext6.form.FormPanel', {
			autoHeight: true,
			border: false,
			bodyPadding: 30,
			items : [{
				name: 'EvnStatusHistory_Cause',
				width: 430,
				height: 40,
				maxLength: 200,
				xtype: 'textarea'
			}]
		});
		
		Ext6.apply(this, {
			buttonAlign: "right",
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				text: 'Отмена',
				cls: 'buttonCancel'
			}, {
				handler: function () {
					win.save();
				},
				cls: 'buttonAccept',
				text: 'Сохранить'
			}],
			items : [
				win.FormPanel
			]
		});

		this.callParent(arguments);
	}
});