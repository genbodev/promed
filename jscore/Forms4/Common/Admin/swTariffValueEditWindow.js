/**
 * swTariffValueEditWindow - Редактирование тарифа ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.Admin.swTariffValueEditWindow', {
	/* свойства */
	alias: 'widget.swTariffValueEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'tariffvalueeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Тариф ТФОМС',
	width: 600,

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			Ext6.Msg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);

		base_form.submit({
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}

				win.callback();
				win.hide();
			},
			failure: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
				}
				else {
					Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при сохранении тарифа'));
				}
			}
		});
	},
	show: function() {
		this.callParent(arguments);

		var win = this;

		win.action = (typeof arguments[0].action == 'string' ? arguments[0].action : 'add');
		win.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext6.emptyFn);
		win.formParams = (typeof arguments[0].formParams == 'object' ? arguments[0].formParams : {});

		win.center();
		win.setTitle('Тариф ТФОМС');

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(win.formParams);

		switch ( win.action ) {
			case 'add':
				win.setTitle(win.getTitle() + ': Добавление');
				base_form.findField('TariffValue_Code').focus();
				break;

			case 'edit':
				win.setTitle(win.getTitle() + ': Редактирование');

				win.mask(LOAD_WAIT);

				base_form.load({
					url: '/?c=TariffValue&m=load',
					params: {
						TariffValue_id: base_form.findField('TariffValue_id').getValue()
					},
					success: function(form, action) {
						win.unmask();
						base_form.findField('TariffValue_Code').focus();
					},
					failure: function() {
						win.unmask();
					}
				});
				break;
		}
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model',
			fields: [
				{name: 'TariffValue_id'},
				{name: 'TariffValue_Code'},
				{name: 'TariffValue_Value'},
				{name: 'TariffValue_begDT'},
				{name: 'TariffValue_endDT'}
			]
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyStyle: 'padding: 5px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 150
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=TariffValue&m=save',
			items: [{
				name: 'TariffValue_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: 'Код',
				name: 'TariffValue_Code',
				width: 350,
				xtype: 'textfield'
			}, {
				allowBlank: false,
				allowDecimals: true,
				decimalSeparator: '.',
				fieldLabel: 'Значение',
				minValue: 0,
				name: 'TariffValue_Value',
				negativeText: 'Значение не может быть меньше 0',
				width: 300,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата начала',
				format: 'd.m.Y',
				name: 'TariffValue_begDT',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				width: 300,
				xtype: 'datefield'
			}, {
				allowBlank: false,
				fieldLabel: 'Дата окончания',
				format: 'd.m.Y',
				name: 'TariffValue_endDT',
				plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
				width: 300,
				xtype: 'datefield'
			}]
		});

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				handler:function () {
					win.save();
				},
				text: BTN_FRMSAVE
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