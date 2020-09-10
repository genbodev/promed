/**
 * swCureStandartNutrMixtureWindow - Клиническая рекомендация: питательные смеси
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 */

Ext6.define('common.CureStandart.swCureStandartNutrMixtureWindow', {
	/* свойства */
	alias: 'widget.swCureStandartNutrMixtureWindow',
	addCodeRefresh: Ext.emptyFn,
	closeToolText: 'Закрыть',
	closable: true,
	closeAction: 'hide',
	draggable: true,
	height: 319,
	cls: 'arm-window-new',
	extend: 'base.BaseForm',
	layout: 'border',
	modal: true,
	renderTo: main_center_panel.body.dom,
	resizable: false,
	title: 'Клиническая рекомендация: Питательные смеси',
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
					base_form.findField('ODD').setValue(arguments[0].data.ODD);
					base_form.findField('EKD').setValue(arguments[0].data.EKD);
					base_form.findField('avenum').setValue(arguments[0].data.avenum);
					base_form.findField('ODD_ed').setValue(arguments[0].data.ODD_ed);
					base_form.findField('EKD_ed').setValue(arguments[0].data.EKD_ed);
					base_form.findField('avenum_ed').setValue(arguments[0].data.avenum_ed);
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
						fieldLabel: 'Вид питательной смеси',
						xtype: 'swCureStandartSpr',
						comboSubject: 'TreatmentNutrMixture',
						labelWidth: 170,
						width: 550,
						allowBlank: false
					}]
				}, {
					border: false,
					layout: 'hbox',
					//~ defaults: {
						//~ padding: '0 5 0 0'
					//~ },
					items: [{
						name : 'ODD',
						allowBlank: false,
						xtype: 'numberfield',
						hideTrigger: true,
						fieldLabel: 'Дневная доза',
						labelWidth: 170,
						width: 300,
						height: 32
					}, {
						name : 'ODD_ed',
						allowBlank: false,
						style: 'margin-left: 50px;',
						xtype: 'swCureStandartSpr',
						comboSubject: 'DoseUnit',
						width: 200,
						value: 5
					}]
				}, {
					border: false,
					layout: 'hbox',
					//~ defaults: {
						//~ padding: '0 5 0 0'
					//~ },
					items: [{
						name : 'EKD',
						xtype: 'numberfield',
						hideTrigger: true,
						allowBlank: false,
						fieldLabel: 'Курсовая доза',
						labelWidth: 170,
						width: 300,
						height: 32
					}, {
						name : 'EKD_ed',
						allowBlank: false,
						style: 'margin-left: 50px;',
						xtype: 'swCureStandartSpr',
						comboSubject: 'DoseUnit',
						width: 200,
						value: 5
					}]
				}, {
					border: false,
					layout: 'hbox',
					//~ defaults: {
						//~ padding: '0 5 0 0'
					//~ },
					items: [{
						name : 'avenum',
						xtype: 'numberfield',
						hideTrigger: true,
						allowBlank: false,
						fieldLabel: 'Среднее количество',
						labelWidth: 170,
						width: 300,
						height: 32
					}, {
						name : 'avenum_ed',
						allowBlank: false,
						style: 'margin-left: 50px;',
						xtype: 'swCureStandartSpr',
						comboSubject: 'DoseUnit',
						width: 200,
						value: 5
					}]
				},{
					name : 'freq',
					xtype: 'numberfield',
					fieldLabel: 'Частота предоставления',
					minValue: 0,
					width: 300,
					value: 1,
					step: 0.05,
					allowBlank: false,
					labelWidth: 170,
					height: 32
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
						data.ODD = base_form.findField('ODD').value;
						data.EKD = base_form.findField('EKD').value;

						data.avenum_ed = base_form.findField('avenum_ed').value;
						data.ODD_ed = base_form.findField('ODD_ed').value;
						data.EKD_ed = base_form.findField('EKD_ed').value;

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