/**
 * swYesNoWindow - Окно для выбора одного из двух ответов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.swYesNoWindow', {
	/* свойства */
	autoShow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	resizable: false,
	title: '',
	bodyPadding: '20 30',
	show: function (data) {
		this.callParent(arguments);
		var me = this;
		me.yesButton.focus();
		if (!data || !data.callback || !data.title || !data.msg) {
			this.errorInParams();
			return false;
		}

		me.callback = data.callback;
		me.setTitle(data.title);
		me.msgLabel.setHtml(data.msg);

		if (data.yesObject) {
			me.yesButton.setText(data.yesObject.text + (data.yesObject.descr ? "<br><span class='black-text' style='font-size: 13px'>" + data.yesObject.descr : "</span>"));
		} else {
			me.yesButton.setText('Да')
		}

		if (data.noObject) {
			me.noButton.setText(data.noObject.text + (data.noObject.descr ? "<br><span class='black-text'>" + data.noObject.descr : "</span>"));
		} else {
			me.noButton.setText('Нет')
		}
	},
	initComponent: function() {
		var me = this;

		me.msgLabel = Ext6.create('Ext6.form.Label', {
			text: '',
			cls: 'label-text'
		});

		me.yesButton = Ext6.create('Ext6.button.Button', {
			text: 'Да',
			cls: 'select-button',
			iconCls: 'select-icon',
			textAlign: 'left',
			anchor: '100%',
			margin: "10px 0 0 0",
			handler: function() {
				me.hide();
				me.callback(2);
			}
		});

		me.noButton = Ext6.create('Ext6.button.Button', {
			text: 'Нет',
			cls: 'select-button',
			iconCls: 'select-icon',
			textAlign: 'left',
			anchor: '100%',
			margin: "10px 0 0 0",
			handler: function() {
				me.hide();
				me.callback(1);
			}
		});

		Ext6.apply(me, {
			border: false,
			width: 500,
			autoHeight: true,
			layout: 'anchor',
			cls: 'yes-no-window-panel',
			items: [me.msgLabel, me.yesButton, me.noButton],
			buttons: ['->', {
				handler: function () {
					me.hide();
				},
				cls: 'buttonCancel',
				text: 'Отмена',
				margin: '0 19 0 0'
			}]
		});

		this.callParent(arguments);
	}
});