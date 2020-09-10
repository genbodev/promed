/**
 * swEgissoDataImportWindow - настраиваемое окно импорта
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Dlo
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Salakhov R.
 * @version      12.2018
 * @comment
 */
sw.Promed.swEgissoDataImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Экспорт ЕГИССО',
	layout: 'border',
	id: 'EgissoDataImportWindow',
	modal: true,
	shim: false,
	width: 350,
	height: 109,
	resizable: false,
	maximizable: false,
	maximized: false,
	doImport:  function() {
		var wnd = this;

		if( !this.form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все обязательные поля заполнены!<br />Обязательные к заполнению поля выделены особо.');
			return false;
		}

		//this.getLoadMask('Передача данных...').show();

        Ext.Ajax.request({
			params: {
                EvnRecept_setDate: this.form.findField('EvnRecept_setDate').getValue().format('Y-m-d')
			},
            callback: function(options, success, response) {
                var err = true;
                if (response.responseText != '') {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.success) {
                        err = false;
                    }
                }
                if (err) {
                    sw.swMsg.alert(langs('Ошибка'), langs('При передаче данных возникли ошибки'));
                } else {
                    sw.swMsg.alert(langs('Сообщение'), langs('Передача данных успешно завершена'));
                }
                wnd.hide();
            },
            url: '?c=Privilege&m=createEgissoData'
        });

		//this.getLoadMask().hide();
	},
	show: function() {
		var wnd = this;
		sw.Promed.swEgissoDataImportWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.title = 'Экспорт ЕГИССО';

		if ( arguments[0] ) {
            if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
                this.callback = arguments[0].callback;
            }
            if ( arguments[0].title ) {
                this.title = arguments[0].title;
            }
            if ( arguments[0].upload_url ) {
                this.upload_url = arguments[0].upload_url;
            }
            if ( arguments[0].format_message ) {
                this.format_message = arguments[0].format_message;
            }
            if ( arguments[0].owner ) {
                this.owner = arguments[0].owner;
            }
		}

		this.setTitle(this.title);
		this.form.reset();
		//this.form.setValues(arguments[0]);

		return true;
	},
	initComponent: function() {
		var wnd = this;

		var form = new Ext.form.FormPanel({
			url: null,
			region: 'center',
			autoHeight: true,
			frame: true,
			fileUpload: true,
			labelAlign: 'right',
			labelWidth: 60,
			bodyStyle: 'padding: 5px 5px 0',
			defaults: {
				anchor: '100%'
			},
			items: [{
				layout: 'form',
				items: [{
                    name: 'EvnRecept_setDate',
                    fieldLabel: 'Дата',
                    allowBlank: false,
                    xtype: 'swdatefield',
                    format: 'd.m.Y',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                    width: 200
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons: [
				{
					handler: function() {
						this.ownerCt.doImport();
					},
					iconCls: 'add16',
					text: 'Экспорт'
				},
				{
					text: '-'
				},
				HelpButton(this, 0),
				{
					handler: function() {
						this.ownerCt.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items:[
				form
			]
		});
		sw.Promed.swEgissoDataImportWindow.superclass.initComponent.apply(this, arguments);
		this.form = form.getForm();
	}
});