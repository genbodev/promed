/**
* swRoutingProfileAddWindow - Добавление типа маршрутизации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Sharipov Fidan
* @version      11.2019

*/
sw.Promed.swRoutingProfileAddWindow = Ext.extend(sw.Promed.BaseForm, {
	modal: true,
	width: 400,
	height: 176,
	resizable: false,
	closeAction: 'hide',
	buttonAlign: 'right',
	url: '/?c=RoutingProfile&m=save',
	title: langs('Добавление типа маршрутизации'),
	listeners: {
		hide: function() {
			this.doReset();
		}
	},
	initComponent: function () {
		let wnd = this;

		let formItems = [{
			name: 'RoutingProfile_name',
			allowBlank: false,
			fieldLabel: 'Наименование',
			anchor: '100%',
			xtype: 'textfield',
			maxLength: 512
		}, {
			hiddenName: 'MorbusType_id',
			xtype: 'swcommonsprcombo',
			fieldLabel: 'Тип заболевания',
			anchor: '100%',
			comboSubject: 'MorbusType',
			listeners: {
				select: function(combo, record) {
					let nick = "", disabled = false;
					let nickField = wnd.formPanel.getForm().findField('RoutingProfile_sysnick');
					if (!Ext.isEmpty(record.json)) {
						nick = record.json.MorbusType_SysNick;
						disabled = true;
					}
					nickField.setValue(nick);
					nickField.setDisabled(disabled);
				}
			}
		}, {
			name: 'RoutingProfile_sysnick',
			allowBlank: false,
			fieldLabel: 'Системное наименование',
			xtype: 'textfield',
			anchor: '100%',
			maxLength: 30,
			style: {
				'margin-top': '7px'
			}
		}];

		wnd.formPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelAlign: 'left',
			labelWidth: 100,
			bodyStyle:'padding: 5px',
			items: formItems
		});

		Ext.apply(wnd, {
			items: [
				wnd.formPanel
			],
			buttons: [{
				text: BTN_FRMSAVE,
				iconCls: 'save16',
				handler: function () {
					wnd.doSave();
				}
			}, {
				text: BTN_FRMCLOSE,
				iconCls: 'close16',
				handler: function () {
					wnd.hide();
				}
			}
		],
		});
		sw.Promed.swRoutingProfileAddWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function () {
		let wnd = this;
		if (!Ext.isEmpty(arguments[0]) && !Ext.isEmpty(arguments[0].parentWnd)) {
			wnd.parentWnd = arguments[0].parentWnd;
		}
		sw.Promed.swRoutingProfileAddWindow.superclass.show.apply(this, arguments);
	},
	doReset: function () {
		let form = this.formPanel.getForm();
		form.findField('RoutingProfile_sysnick').setDisabled(false);
		form.reset();
	},
	doSave: function () {
		let wnd = this;
		let form = wnd.formPanel.getForm();
		if (!form.isValid()) {
			sw.swMsg.alert(ERR_INVFIELDS_TIT, ERR_INVFIELDS_MSG);
			return;
		}

		let params = form.getValues();
		if (!params.hasOwnProperty('RoutingProfile_sysnick')) {
			params.RoutingProfile_sysnick = form.findField('RoutingProfile_sysnick').getValue();
		}
		wnd.submit(params);
	},
	submit: function (params) {
		let wnd = this;
		Ext.Ajax.request({
			params: params,
			url: wnd.url,
			failure: function(response, options) {
				sw.swMsg.alert(lang['oshibka'], 'Не удалось сохранить данные');
			},
			success: function(response, options) {
				let resp = Ext.util.JSON.decode(response.responseText);
				if (!Ext.isEmpty(resp._id)) {
					wnd.parentWnd.RoutingProfileCombo.setValue(resp._id);
				}
				wnd.parentWnd.RoutingProfileCombo.getStore().reload();
				wnd.hide();
			}
		});
	}
});