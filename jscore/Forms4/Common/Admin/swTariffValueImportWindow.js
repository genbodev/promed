/**
 * swTariffValueImportWindow - Импорт тарифов ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.Admin.swTariffValueImportWindow', {
	/* свойства */
	alias: 'widget.swTariffValueImportWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'tariffvalueimportsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Импорт тарифов ТФОМС',
	width: 600,

	/* методы */
	import: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			Ext6.Msg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		base_form.submit({
			failure: function(fp, o) {
				if ( !Ext6.isEmpty(o.result.Error_Msg) ) {
					Ext6.Msg.alert(langs('Ошибка'), o.result.Error_Msg);
				}
				else {
					Ext6.Msg.alert(langs('Успех'), langs('Файл успешно загружен'));
				}
			},
			success: function(fp, o) {
				if ( !Ext6.isEmpty(o.result.Alert_Msg) ) {
					Ext6.Msg.alert(langs('Внимание'), o.result.Alert_Msg, function() { win.hide(); });
				}
				else if ( !Ext6.isEmpty(o.result.Error_Msg) ) {
					Ext6.Msg.alert(langs('Ошибка'), o.result.Error_Msg);
				}
				else {
					Ext6.Msg.alert(langs('Успех'), langs('Файл успешно загружен'), function() { win.hide(); });
				}
			},
			url: '/?c=TariffValue&m=import',
			waitMsg: 'Загрузка файла...'
		});
	},
	show: function() {
		this.callParent(arguments);

		var win = this;

		win.center();

		var base_form = win.FormPanel.getForm();
		base_form.reset();
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyStyle: 'padding: 5px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 50
			},
			items: [{
				allowBlank: false,
				buttonText: langs('Выбрать файл...'),
				fieldLabel: 'Файл',
				name: 'import_file',
				width: 550,
				xtype: 'filefield'
			}]
		});

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				handler:function () {
					win.import();
				},
				text: BTN_FRMIMPORT
			},
			'->',
			sw4.getHelpButton(win, -1),
			{
				handler:function () {
					win.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});

		this.callParent(arguments);
    }
});