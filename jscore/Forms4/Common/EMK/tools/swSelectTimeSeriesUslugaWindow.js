/**
 * swPacketPrescrCreateWindow - Создание пакета назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.tools.swSelectTimeSeriesUslugaWindow', {
	/* свойства */
	alias: 'widget.swSelectTimeSeriesUslugaWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	refId: 'swSelectTimeSeriesUslugaWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Добавление параметров',
	width: 600,
	autoHeight: true,
	data: {},
	show: function () {
		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		var win = this;
		if (arguments[0].callback) {
			win.callback = arguments[0].callback;
		} else {
			win.callback = Ext6.emptyFn;
		}

		this.callParent(arguments);
		win.UslugaComplexCombo.reset();
		win.UslugaComplexCombo.focus();
	},
	onSprLoad: function(arguments) {
		// Если что-то нужно дозагрузить
	},
	save: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		if ( !base_form.isValid() ) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var UslugaComplex_id = me.UslugaComplexCombo.getValue();
		me.callback(UslugaComplex_id);
		me.hide();
	},
	/* конструктор */
	initComponent: function() {
		var win = this;
		win.UslugaComplexCombo = Ext6.create('swUslugaComplexSearchCombo', {
			type: 'string',
			filterByValue: true,
			listConfig: {
				cls: 'choose-bound-list-menu update-scroller'
			},
			listeners: {
				'render': function (combo) {
					combo.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
				},
				'beforequery': function(queryPlan, eOpts ){
					this.getStore().proxy.extraParams = {};
					this.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['gost2011']);
					this.getStore().proxy.extraParams.uslugaCategoryList = Ext6.JSON.encode(['noprescr']);
					this.getStore().proxy.extraParams.PrescriptionType_Code = 11; // Лабораторная диагностика
					this.getStore().proxy.extraParams.to = 'EvnPrescrUslugaInputWindow';
					this.getStore().proxy.extraParams.formMode = 'ExtJS6';
					this.getStore().proxy.extraParams.allowedUslugaComplexAttributeList = Ext6.JSON.encode(['lab']);
				}
			},
			hideLabel: false,
			fieldLabel: 'Услуга',
			labelWidth: 42,
			minWidth: 42 + 450,
			emptyText: 'Поиск услуги по коду или наименованию'
		});
		this.formPanel = Ext6.create('Ext6.form.Panel', {
			layout: 'anchor',
			defaults: {
				anchor: '100%',
				labelWidth: 150
			},
			border: false,
			url: '/?c=PacketPrescr&m=loadEditPacketForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'PacketPrescr_id'},
						{name: 'PacketPrescr_Name'},
						{name: 'PacketPrescr_Descr'},
						{name: 'PacketPrescrVision_id'},
						{
							name: 'Diag_id',
							type: 'auto',
							convert: function (value_str) {
								var res = value_str.split(",");
								console.log(res);
								return res;
							}
						}
					]
				})
			}),
			items: [
				win.UslugaComplexCombo
			]
		});

		Ext6.apply(win, {
			layout: 'fit',
			bodyPadding: '20 30 10 30',
			border: false,
			items: [
				win.formPanel
			],
			buttons: ['->', {
				handler: function () {
					win.hide();
				},
				cls: 'buttonCancel',
				text: 'Отмена'
			}, {
				handler: function () {
					win.save();
				},
				cls: 'buttonAccept',
				text: 'Применить',
				margin: '0 20 0 0'
			}]
		});

		this.callParent(arguments);
	}
});