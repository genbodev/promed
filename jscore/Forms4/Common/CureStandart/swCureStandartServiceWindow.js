/**
 * swCureStandartServiceWindow - Клиническая рекомендация: услуга
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

Ext6.define('common.CureStandart.swCureStandartServiceWindow', {
	/* свойства */
	alias: 'widget.swCureStandartServiceWindow',
	addCodeRefresh: Ext.emptyFn,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 208,
	cls: 'arm-window-new',
	extend: 'base.BaseForm',
	layout: 'border',
	modal: true,
	renderTo: main_center_panel.body.dom,
	resizable: false,
	title: 'Стандарты лечения: Услуга',

	show: function() {
		var win = this;
		var base_form = win.MainPanel.getForm();
		base_form.reset();

		this.callParent(arguments);
		if(arguments[0].action)
			this.action = arguments[0].action;
		else {
			this.hide();
			return false;
		}

		this.callback = arguments[0].callback;

		switch(this.action) {
			case 'add':
				//this.setTitle(this.title + '');
				break;
			case 'view':
			case 'edit':
				if(arguments[0].data) {
					base_form.findField('subject').setValue(arguments[0].data.id);
					base_form.findField('freq').setValue(arguments[0].data.freq);
					base_form.findField('avenum').setValue(arguments[0].data.avenum);
				}
				break;
		}

		if (win.action == 'view') {
			win.saveButton.disable();
			win.MainPanel.enableEdit(false);
		} else {
			win.saveButton.enable();
			win.MainPanel.enableEdit(true);
		}
	},
	initComponent: function() {
		var win = this;

		win.MainPanel = new Ext6.form.FormPanel({
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 29px;',

			items: [{
				border: false,
				layout: 'vbox',
				defaults: {
					padding: '0px 0px 5px 0px'
				},
				items: [{
					border: false,
					layout: 'hbox',
					items: [{
						name: 'subject',
						fieldLabel: 'Услуга',
						xtype: 'swUslugaComplexComboExt',
						labelWidth: 170,
						width: 700,
						allowBlank: false,
						listeners: {
							'beforeQuery': function( queryPlan ) {
								this.getStore().getProxy().setExtraParam('code', LetterChange(queryPlan.query) ); //чтобы искать по коду услуги, в любых раскладках
							}
						}
					}]
				}, {
					border: false,
					layout: 'hbox',
					items: [{
						name : 'freq',
						xtype: 'numberfield',
						fieldLabel: 'Частота предоставления',
						labelWidth: 170,
						width: 300,
						height: 32,
						minValue: 0,
						value: 1.0,
						step: 0.05,
						allowBlank: false
					}, {
						name : 'avenum',
						xtype: 'numberfield',
						fieldLabel: 'Среднее количество',
						style: 'margin-left: 100px;',
						width: 300,
						height: 32,
						labelWidth: 170,
						minValue: 0,
						value: 1,
						step: 0.05,
						allowBlank: false
					}]
				}
				]
			}]
		});

		Ext6.apply(win, {
			layout: 'anchor',
			items: [
				win.MainPanel
			],
			buttons: ['->',
			{
				handler:function () {
					win.hide();
				},
				text: BTN_FRMCANCEL
			}, win.saveButton = Ext6.create('Ext6.button.Button', {
				handler: function() {
					var data = new Object();
					var base_form = win.MainPanel.getForm();
					if(!base_form.isValid()) {
						Ext6.MessageBox.show({
							title: 'Проверка данных формы',
							msg: 'Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.',
							buttons: Ext6.Msg.OK,
							icon: Ext6.Msg.WARNING
						});
					} else {
						//данные для возврата в форму "Клиническая рекомендация"
						data.subject = base_form.findField('subject').getSelection().data;

						data.freq = base_form.findField('freq').value;
						data.avenum = base_form.findField('avenum').value;

						win.callback(data);
						win.hide();
					}
				},
				cls: 'flat-button-primary',
				text: langs('Сохранить')
			})]
		});

		this.callParent(arguments);
	}
});