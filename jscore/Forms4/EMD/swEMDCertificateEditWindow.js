/**
 * swEMDCertificateEditWindow - Сертификат электронной подписи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDCertificateEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDCertificateEditWindow',
	autoShow: false,
	maximized: false,
	width: 900,
	autoHeight: true,
	resizable: false,
	maximizable: false,
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'Сертификат электронной подписи',
	modal: true,
	header: true,
	constrain: true,
	show: function(data) {
		var me = this;
		this.callParent(arguments);

		if (!data || !data.action) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				me.hide();
			});
			return false;
		}

		var base_form = me.formPanel.getForm();
		base_form.reset();
		base_form.findField('UserCertFile').setRawValue('');
		me.action = data.action;

		if (data.callback) {
			me.callback = data.callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

		if (data.pmUser_id) {
			base_form.findField('pmUser_id').setValue(data.pmUser_id);
		}

		me.formPanel.enableEdit(true);

		if (data.EMDCertificate_id) {
			base_form.findField('EMDCertificate_id').setValue(data.EMDCertificate_id);
			base_form.findField('UserCertFile').disable();

			// грузим форму с сервера
			me.mask(LOADING_MSG);
			base_form.load({
				params: {
					EMDCertificate_id: data.EMDCertificate_id
				},
				failure: function (form, action) {
					me.unmask();
					me.hide();
				},
				success: function (form, action) {
					me.unmask();
				}
			});
		}
	},
	doSave: function(data) {
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

		if (Ext6.isEmpty(base_form.findField('EMDCertificate_begDate').getValue())) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					base_form.findField('UserCertFile').focus(false);
				},
				icon: Ext6.Msg.WARNING,
				msg: 'Не выбран сертификат',
				title: ERR_INVFIELDS_TIT
			});

			return false;
		}

		var params = {};
		if (data && data.bypassStrictCommonName) {
            params.bypassStrictCommonName = data.bypassStrictCommonName;
		}

		me.mask('Сохранение сертификата...');
		base_form.submit({
			url: '/?c=EMD&m=saveEMDCertificate',
			params: params,
			failure: function (form, action) {
				me.unmask();
				if (action.result) {
					if (action.result.isNotEqual) {

                        Ext6.Msg.show({
                            buttons: Ext6.Msg.YESNO,
                            title: langs('Предупреждение'),
                            msg: "Этот сертификат выдан не на текущего пользователя системы. Сохранить данный сертификат?",
                            icon: Ext6.MessageBox.WARNING,
                            fn: function (buttonId, text, obj) {
                                if (buttonId == 'yes') me.doSave({bypassStrictCommonName: true}); else return false;
                            },
                            buttonText: {
                                yes: 'Сохранить',
                                no: 'Отмена'
                            }
                        });

					} else if (action.result.Error_Msg) {
						Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
					} else {
						Ext6.Msg.alert(langs('Ошибка'), langs('Во время сохранения сертификата произошла ошибка.'));
                    }
				}
			},
			success: function (form, action) {
				me.unmask();
				me.callback();
				me.hide();
			}
		});
	},
	onSelectCert: function() {
		var me = this;
		var base_form = me.formPanel.getForm();
		me.mask('Загрузка сертификата...');
		base_form.submit({
			clientValidation: false,
			url: '/?c=EMD&m=getCertificateFileInfo',
			failure: function (form, action) {
				me.unmask();
				if (action.result) {
					if (action.result.Error_Msg) {
						Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
					} else {
						Ext6.Msg.alert(langs('Ошибка'), langs('Во время загрузки сертификата произошла ошибка.'));
					}
				}
			},
			success: function (form, action) {
				me.unmask();
				if (action.result) {
					base_form.setValues(action.result);
				}
			}
		});
	},
	initComponent: function() {
		var me = this;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			autoHeight: true,
			layout: 'column',
			url: '/?c=EMD&m=loadEMDCertificateEditWindow',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'EMDCertificate_id'},
						{name: 'EMDCertificate_Version'},
						{name: 'EMDCertificate_Serial'},
						{name: 'EMDCertificate_begDate'},
						{name: 'EMDCertificate_endDate'},
						{name: 'EMDCertificate_begTime'},
						{name: 'EMDCertificate_endTime'},
						{name: 'EMDCertificate_Publisher'},
						{name: 'EMDCertificate_CommonName'},
						{name: 'EMDCertificate_SurName'},
						{name: 'EMDCertificate_FirName'},
						{name: 'EMDCertificate_Post'},
						{name: 'EMDCertificate_Org'},
						{name: 'EMDCertificate_Unit'},
						{name: 'EMDCertificate_SignAlgorithm'},
						{name: 'EMDCertificate_OpenKey'},
						{name: 'EMDCertificate_SHA256'},
						{name: 'EMDCertificate_SHA1'},
						{name: 'EMDCertificate_PublisherUID'},
						{name: 'EMDCertificate_SubjectUID'},
						{name: 'Org_id'},
						{name: 'pmUser_id'},
						{name: 'EMDCertificate_IsNotUse'},
						{name: 'EMDCertificate_Name'},
                        {name: 'EMDCertificate_OGRN'}
					]
				})
			}),
			items: [{
				name: 'EMDCertificate_id',
				xtype: 'hidden'
			}, {
				name: 'pmUser_id',
				xtype: 'hidden'
			}, {
				bodyPadding: '20 20 0 20',
				border: false,
				columnWidth: 0.5,
				defaults: {
					labelWidth: 150,
					anchor: '-10'
				},
				layout: 'anchor',
				items: [{
					fieldLabel: 'Сертификат',
					name: 'UserCertFile',
					clearOnSubmit: false,
					xtype: 'filefield',
					buttonText: 'Открыть',
					listeners: {
						'change': function() {
							me.onSelectCert();
						}
					}
				}, {
					allowBlank: false,
					fieldLabel: 'Наименование',
					name: 'EMDCertificate_Name',
					xtype: 'textfield'
				}, {
					fieldLabel: 'Версия',
					name: 'EMDCertificate_Version',
					readOnly: true,
					xtype: 'textfield'
				}, {
					fieldLabel: 'Алгоритм подписи',
					name: 'EMDCertificate_SignAlgorithm',
					readOnly: true,
					xtype: 'textfield'
				}, {
					anchor: '100%',
					padding: '0 10 10 10',
					defaults: {
						labelWidth: 140,
						anchor: '100%'
					},
					title: 'Кому выдан',
					xtype: 'fieldset',
					items: [{
						fieldLabel: 'Открытый ключ',
						name: 'EMDCertificate_OpenKey',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Общее имя (CN)',
						name: 'EMDCertificate_CommonName',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Фамилия (SN)',
						name: 'EMDCertificate_SurName',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Имя Отчество (GN)',
						name: 'EMDCertificate_FirName',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Организация (O)',
						name: 'EMDCertificate_Org',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Организация МИС',
						name: 'Org_id',
						xtype: 'swOrgCombo'
					}, {
						fieldLabel: 'Подразделение (OU)',
						name: 'EMDCertificate_Unit',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Должность',
						name: 'EMDCertificate_Post',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'Серийный номер',
						name: 'EMDCertificate_Serial',
						readOnly: true,
						xtype: 'textfield'
					}, {
                        fieldLabel: 'ОГРН',
                        name: 'EMDCertificate_OGRN',
                        readOnly: true,
                        xtype: 'textfield'
                    }]
				}]
			}, {
				bodyPadding: '20 20 0 20',
				border: false,
				columnWidth: 0.5,
				defaults: {
					labelWidth: 150,
					anchor: '-10'
				},
				layout: 'anchor',
				items: [{
					anchor: '100%',
					padding: '0 10 10 10',
					defaults: {
						labelWidth: 140,
						anchor: '100%'
					},
					title: 'Кем выдан',
					xtype: 'fieldset',
					items: [{
						fieldLabel: 'Общее имя (CN)',
						name: 'EMDCertificate_Publisher',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'УИД',
						name: 'EMDCertificate_PublisherUID',
						readOnly: true,
						xtype: 'textfield'
					}]
				}, {
					anchor: '100%',
					padding: '0 10 10 10',
					defaults: {
						labelWidth: 140,
						anchor: '100%'
					},
					title: 'Срок действия',
					xtype: 'fieldset',
					items: [{
						layout: 'column',
						border: false,
						defaults: {
							border: false,
							labelWidth: 140
						},
						items: [{
							readOnly: true,
							xtype: 'textfield',
							fieldLabel: 'Действителен с',
							style: 'margin-right: 10px;',
							width: 270,
							name: 'EMDCertificate_begDate'
						}, {
							readOnly: true,
							xtype: 'textfield',
							hideLabel: true,
							width: 90,
							name: 'EMDCertificate_begTime'
						}]
					}, {
						layout: 'column',
						border: false,
						defaults: {
							border: false,
							labelWidth: 140
						},
						items: [{
							readOnly: true,
							xtype: 'textfield',
							fieldLabel: 'Действителен по',
							style: 'margin-right: 10px;',
							width: 270,
							name: 'EMDCertificate_endDate'
						}, {
							readOnly: true,
							xtype: 'textfield',
							hideLabel: true,
							width: 90,
							name: 'EMDCertificate_endTime'
						}]
					}]
				}, {
					anchor: '100%',
					padding: '0 10 10 10',
					defaults: {
						labelWidth: 140,
						anchor: '100%'
					},
					title: 'Отпечаток',
					xtype: 'fieldset',
					items: [{
						fieldLabel: 'SHA-1',
						name: 'EMDCertificate_SHA1',
						readOnly: true,
						xtype: 'textfield'
					}, {
						fieldLabel: 'SHA-256',
						name: 'EMDCertificate_SHA256',
						readOnly: true,
						xtype: 'textfield'
					}]
				}, {
					boxLabel: 'Не использовать',
					hideLabel: true,
					name: 'EMDCertificate_IsNotUse',
					xtype: 'checkbox'
				}]
			}]
		});

		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: ['->', {
				handler: function() {
					me.hide();
				},
				text: 'Отмена'
			}, {
				handler: function() {
					me.doSave();
				},
				cls: 'flat-button-primary',
				text: 'Сохранить'
			}]
		});

		this.callParent(arguments);
	}
});