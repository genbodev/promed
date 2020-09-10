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
Ext6.define('common.EMK.tools.swPacketPrescrCreateWindow', {
	/* свойства */
	alias: 'widget.swPacketPrescrCreateWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new new-packet-create-window',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
	header: true,
	modal: true,
	layout: 'form',
	refId: 'swPacketPrescrCreateWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Сохранение назначений в шаблон (пакет)',
	width: 600,
	autoHeight: true,
	data: {},
	loadForm: function(options) {
		var me = this,
			form = me.formPanel.getForm();
		me.mask(LOADING_MSG);
		form.load({
			params: {
				PacketPrescr_id: me.PacketPrescr_id
			},
			success: function (form, action) {
				me.unmask();
				if (action.response && action.response.responseText) {
					var data = Ext6.JSON.decode(action.response.responseText);
					//if(data && data[0])
						//me.setTitle(data[0]['PacketPrescr_Name']);
				}
			},
			failure: function (form, action) {
				if (options && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
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

		win.Evn_id = arguments[0].Evn_id;
		win.Diag_id = arguments[0].Diag_id;
		win.MedPersonal_id = arguments[0].MedPersonal_id;
		win.PacketPrescr_id = arguments[0].PacketPrescr_id;
		this.callParent(arguments);
		var title = arguments[0].title?arguments[0].title:'Сохранение назначений в шаблон (пакет)';
		this.setTitle(title)
	},
	onSprLoad: function(arguments) {
		var win = this;
		var base_form = win.formPanel.getForm();
		base_form.reset();
		base_form.findField('Diag_id').setValue(win.Diag_id);
		if(win.PacketPrescr_id)
			win.loadForm(arguments);
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

		var params = {
			Evn_id: me.Evn_id,
			MedPersonal_id: me.MedPersonal_id,
			PacketPrescr_id: me.PacketPrescr_id
		};

		me.mask('Сохранение...');
		base_form.submit({
			url: '/?c=PacketPrescr&m=createPacketPrescr',
			params: params,
			success: function(result_form, action) {
				me.unmask();
				me.callback();
				me.hide();
			},
			failure: function(result_form, action) {
				me.unmask();
			}
		});
	},
	/* конструктор */
	initComponent: function() {
		var win = this;

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
						{name: 'Sex_id'},
						{name: 'PersonAgeGroup_id'},
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
				{
					name: 'PacketPrescr_id',
					xtype: 'textfield',
					hidden: true
				}, {
					fieldLabel: 'Название пакета',
					name: 'PacketPrescr_Name',
					allowBlank: false,
					xtype: 'textfield'
				}, {
					fieldLabel: 'Краткое описание',
					name: 'PacketPrescr_Descr',
					xtype: 'textareafield'
				}, {
					fieldLabel: 'Видимость',
					value: 1,
					name: 'PacketPrescrVision_id',
					comboSubject: 'PacketPrescrVision',
					displayCode: false,
					anchor: '60%',
					allowBlank: false,
					xtype: 'commonSprCombo'
				}, {
					fieldLabel: 'Возрастная группа',
					name: 'PersonAgeGroup_id',
					comboSubject: 'PersonAgeGroup',
					displayCode: false,
					anchor: '60%',
					//allowBlank: false,
					xtype: 'commonSprCombo',
					allowBlank: true,
					hideEmptyRow: false
				}, {
					fieldLabel: 'Пол',
					value: 3,
					name: 'Sex_id',
					comboSubject: 'Sex',
					displayCode: false,
					anchor: '60%',
					allowBlank: false,
					xtype: 'commonSprCombo'
				}, {
					fieldLabel: 'Диагноз',
					name: 'Diag_id',
					//allowBlank: false,
					xtype: 'swDiagTagCombo',
					cls: 'diagnoz-tag-input-field',
					listConfig: {
						cls: 'choose-bound-list-menu update-scroller'
					},
				}, {
					hideLabel: true,
					style: 'margin-left: 155px;',
					boxLabel: 'Запоминать места оказания услуг',
					name: 'PacketPrescr_SaveLocation',
					xtype: 'checkbox'
				}, {
					style: 'margin-left: 155px;',
					cls: 'infoLabel',
					html: 'Внимание! Даты и время оказания<br>в пакете услуг не сохраняются.',
					name: 'PacketPrescr_SaveLocation',
					xtype: 'label'
				}]
		});

		Ext6.apply(win, {
			layout: 'fit',
			bodyPadding: '20 30 30 30',
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
				text: 'Сохранить',
				margin: '0 20 0 0'
			}]
		});

		this.callParent(arguments);
	}
});