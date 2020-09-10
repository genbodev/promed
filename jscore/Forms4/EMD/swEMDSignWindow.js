/**
 * swEMDSignWindow - Подписание данных ЭП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDSignWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDSignWindow',
	autoShow: false,
	maximized: false,
	width: 900,
	height: 500,
	resizable: false,
	maximizable: false,
	findWindow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding',
	title: 'Подписание данных ЭП',
	layout: 'border',
	modal: true,
	header: true,
	constrain: true,
	backgroundProcessing: true,
	show: function(data) {
		var me = this;
		this.callParent(arguments);

		if (!data || !data.EMDRegistry_ObjectName || (!data.EMDRegistry_ObjectID && !data.EMDRegistry_ObjectIDs)) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				me.hide();
			});
			return false;
		}

		var base_form = me.formPanel.getForm();

		base_form.reset();
		me.action = data.action;

       	if (data.callback) {
			me.callback = data.callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

        var msfCombo = base_form.findField('MedStaffFact_id');

		me.formPanel.enableEdit(true);
		me.EMDRegistry_ObjectName = data.EMDRegistry_ObjectName;

		me.EMDRegistry_ObjectIDs = [];
		if (data.EMDRegistry_ObjectIDs) {
			me.EMDRegistry_ObjectIDs = data.EMDRegistry_ObjectIDs;
		} else if (data.EMDRegistry_ObjectID) {
			me.EMDRegistry_ObjectIDs = [data.EMDRegistry_ObjectID];
		}

		me.backgroundProcessing = (data.backgroundProcessing) ? true : false;
        me.isMOSign = false;

		base_form.findField('EMDPersonRole_id').setAllowBlank(data.isMOSign !== undefined);
		if (data.isMOSign === undefined) {
            base_form.findField('EMDPersonRole_id').show();
            base_form.findField('EMDPersonRole_id').getStore().load();
			msfCombo.show();
			msfCombo.setAllowBlank(false);
		} else {
            base_form.findField('EMDPersonRole_id').hide();
			msfCombo.hide();
			msfCombo.setAllowBlank(true);
            me.isMOSign = data.isMOSign;
        }

        msfCombo.getStore().removeAll();

		// грузим форму с сервера
		me.mask(LOADING_MSG);
		me.Grid.getStore().removeAll();
		me.onRecordSelect();
		me.Grid.getStore().load({
			params: {
				EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
				EMDRegistry_ObjectIDs: Ext6.util.JSON.encode(me.EMDRegistry_ObjectIDs)
			},
			callback: function () {
				me.unmask();

				me.Grid.getSelectionModel().selectAll();

				if (data.isMOSign) {
                    base_form.findField('EMDCertificate_id').getStore().proxy.extraParams['isMOSign'] = data.isMOSign;
				} else {
                    base_form.findField('EMDCertificate_id').getStore().proxy.extraParams['isMOSign'] = null;
				}

				if (me.Grid.getStore().getAt(0) && me.Grid.getStore().getAt(0).get('EMDPersonRole_id') > 0) {
					base_form.findField('EMDPersonRole_id').setValue(me.Grid.getStore().getAt(0).get('EMDPersonRole_id'));
				}

				base_form.findField('EMDCertificate_id').getStore().load({
					callback: function() {
						var record = base_form.findField('EMDCertificate_id').getFirstRecord();
						if (record) {
							base_form.findField('EMDCertificate_id').setValue(record.get('EMDCertificate_id'));
						}
					}
				});

                if (!me.isMOSign) {
                	var params = {
						onDate: getGlobalOptions().date,
						withoutLpuSection: true
					};
                	if (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.MedStaffFact_id) {
						params.id = sw.Promed.MedStaffFactByUser.last.MedStaffFact_id;
					} else {
                		if (getGlobalOptions().medpersonal_id) {
							params.medPersonalIdList = [getGlobalOptions().medpersonal_id];
						}

						/*if (sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.last.LpuSection_id) {
							params.LpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id;
						}*/
					}
					setMedStaffFactGlobalStoreFilter(params, sw4.swMedStaffFactGlobalStore);
					base_form.findField('MedStaffFact_id').getStore().loadData(sw4.getStoreRecords(sw4.swMedStaffFactGlobalStore));
					if (base_form.findField('MedStaffFact_id').getStore().getCount() > 0) {
						base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id'));
					}
                }
			}
		});
	},
	/**
	 * Проверки перед подписью
	 */
	checkBeforeSign: function(params) {
		var me = this;
		var base_form = me.formPanel.getForm();
		me.mask('Проверка возможности подписания');
		Ext6.Ajax.request({
			url: '/?c=EMD&m=checkBeforeSign',
			params: {
				EMDRegistry_ObjectName: params.EMDRegistry_ObjectName,
				EMDRegistry_ObjectID: params.EMDRegistry_ObjectID,
				EMDPersonRole_id: params.EMDPersonRole_id,
				MedStaffFact_id: params.MedStaffFact_id,
				EMDCertificate_id: params.EMDCertificate_id,
				EMDVersion_id: params.EMDVersion_id,
				isMOSign: (me.isMOSign ? me.isMOSign : null)
			},
			callback: function(options, success, response) {
				me.unmask();

				if (success) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.success) {
						params.callback();
					}
				}
			}
		});
	},
	/**
	 * Фомирование файлов для подписи для всех выбранных в списке документов
	 */
	generateEMDRegistryForAll: function(preview) {
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

		var docs = [];
		me.Grid.getSelectionModel().getSelection().forEach(function(record) {
			docs.push({
				EMDRegistry_ObjectID: record.get('EMDRegistry_ObjectID'),
				ApprovalObjectList_id: record.get('ApprovalObjectList_id'),
				EMDVersion_id: record.get('EMDVersion_id')
			});
		});

		me.generateEMDRegistry(docs, preview);
	},
	/**
	 * Формирование файла для подписи, получение хэша
	 */
	generateEMDRegistry: function(docs, preview) {
		var me = this;
		var base_form = me.formPanel.getForm();

		if (docs.length > 0) {
			var doc = docs.shift();

			me.checkBeforeSign({
				EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
				EMDRegistry_ObjectID: doc.EMDRegistry_ObjectID,
				EMDVersion_id: doc.EMDVersion_id,
				EMDPersonRole_id: base_form.findField('EMDPersonRole_id').getValue(),
				MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
				EMDCertificate_id: base_form.findField('EMDCertificate_id').getValue(),
				callback: function() {
					if (me.backgroundProcessing) me.hide();

					if (!preview) {
						sw4.showInfoMsg({
							type: 'loading',
							text: 'Выполняется подписание документа'
						});

						me.callback({
							preloader: true
						});
					}

					var params = {
						EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
						EMDRegistry_ObjectID: doc.EMDRegistry_ObjectID,
						MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
						EMDCertificate_id: base_form.findField('EMDCertificate_id').getValue()
					};

					// получаем хэш PDF с сервера, попутно сохраняя PDF в EMDRegistry
					me.mask('Получение документа для подписи');
					var url = '/?c=EMD&m=generateEMDRegistry';

					if (
						!Ext6.isEmpty(doc.EMDVersion_id)
						&& (
							me.isMOSign // подпись МО
							|| !Ext6.isEmpty(doc.ApprovalObjectList_id) // или подпись с листом согласования
						)
					) {
						url = '/?c=EMD&m=getEMDVersionSignData';
						params.EMDVersion_id = doc.EMDVersion_id;
					}

					Ext6.Ajax.request({
						url: url,
						params: params,
						timeout: 300000,
						callback: function(options, success, response) {
							me.unmask();

							if (success) {
								var response_obj = Ext6.JSON.decode(response.responseText);
								if (response_obj.success && response_obj.toSign) {
									if (preview) {
										for (var k in response_obj.toSign) {
											if (response_obj.toSign[k].link) {
												window.open(response_obj.toSign[k].link, '_blank');
											}
										}
										me.generateEMDRegistry(docs, preview);
									} else {
										me.doSignAll(doc, response_obj.toSign, function() {
											me.generateEMDRegistry(docs, preview);
										});
									}
								}
							}
						}
					});
				}
			});
		} else if (!preview) {
			me.hide();

			sw4.showInfoMsg({
				type: 'success',
				text: 'Подписание документа успешно завершено'
			});

			me.callback({
				success: true
			});
		}
	},
	doSignAll: function(doc, toSign, callback) {
		var me = this;
		var base_form = me.formPanel.getForm();
		if (toSign.length > 0) {
			var one = toSign.shift();
			doc.Signatures_Hash = one.hashBase64;
			doc.EMDVersion_id = one.EMDVersion_id;
			me.doSign(doc, one.docBase64, function() {
				me.doSignAll(doc, toSign, callback);
			});
		} else {
			callback();
		}
	},
	/**
	 * Подписание и сохранение подписи
	 */
	doSign: function(doc, docBase64, callback) {
		log('doSign', doc, docBase64, callback);
		var me = this;
		var base_form = me.formPanel.getForm();

		if (!doc.EMDVersion_id || !doc.Signatures_Hash) {
			return false;
		}

		var doc_signtype = getOthersOptions().doc_signtype;
		if (getRegionNick() != 'ufa' && !doc_signtype.inlist(['cryptopro', 'vipnet', 'authapi'])) {
			doc_signtype = 'cryptopro'; // нужна подпись в формате cades, а это пока умеют только плагины КриптоПро, ViPNet и AuthApi.
		}

		if (doc_signtype && doc_signtype == 'vipnet') {
			sw.Applets.ViPNetPKI.signText({
				text: docBase64,
				Cert_Base64: base_form.findField('EMDCertificate_id').getFieldValue('EMDCertificate_OpenKey'),
				callback: function(Signatures_SignedData) {
					me.saveEMDSignatures(doc, doc_signtype, Signatures_SignedData, callback);
				},
				error: function() {
					me.onSignError();
				}
			});
		} else if (doc_signtype && doc_signtype == 'cryptopro') {
			sw.Applets.CryptoPro.signText({
				text: docBase64,
				Cert_Thumbprint: base_form.findField('EMDCertificate_id').getFieldValue('EMDCertificate_SHA1'),
				callback: function(Signatures_SignedData) {
					me.saveEMDSignatures(doc, doc_signtype, Signatures_SignedData, callback);
				},
				error: function() {
					me.onSignError();
				}
			});
		} else if (doc_signtype && doc_signtype.inlist(['authapi', 'authapitomee'])) {
			sw.Applets.AuthApi.signText({
				win: me,
				cades: true,
				text: docBase64,
				Cert_Thumbprint: base_form.findField('EMDCertificate_id').getFieldValue('EMDCertificate_SHA1'),
				callback: function(Signatures_SignedData) {
					me.saveEMDSignatures(doc, doc_signtype, Signatures_SignedData, callback);
				},
				error: function() {
					me.onSignError();
				}
			});
		} else {
			sw.Applets.AuthApplet.signText({
				text: docBase64,
				Cert_Thumbprint: base_form.findField('EMDCertificate_id').getFieldValue('EMDCertificate_SHA1'),
				callback: function (Signatures_SignedData) {
					me.saveEMDSignatures(doc, doc_signtype, Signatures_SignedData, callback);
				},
				error: function() {
					me.onSignError();
				}
			});
		}
	},
	onSignError: function() {
		var me = this;

		sw4.showInfoMsg({
			type: 'error',
			text: 'Ошибка подписания документа'
		});

		me.callback({
			error: true
		});
	},
	saveEMDSignatures: function(doc, doc_signtype, Signatures_SignedData, callback) {
		var me = this;
		var base_form = me.formPanel.getForm();

		me.mask('Сохранение подписи');
		Ext6.Ajax.request({
			url: '/?c=EMD&m=saveEMDSignatures',
			params: {
				EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
				EMDRegistry_ObjectID: doc.EMDRegistry_ObjectID,
				EMDVersion_id: doc.EMDVersion_id,
				Signatures_Hash: doc.Signatures_Hash,
				Signatures_SignedData: Signatures_SignedData,
				EMDCertificate_id: base_form.findField('EMDCertificate_id').getValue(),
				EMDPersonRole_id: base_form.findField('EMDPersonRole_id').getValue(),
				signType: doc_signtype,
				isMOSign: (me.isMOSign ? me.isMOSign : null),
				MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
                LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id,
                MedService_id: sw.Promed.MedStaffFactByUser.current.MedService_id
			},
			callback: function(options, success, response) {
				me.unmask();

				if (success) {
					var response_obj = Ext6.JSON.decode(response.responseText);
					if (response_obj.success) {
						callback();
					} else {
						me.onSignError();
					}
				} else {
					me.onSignError();
				}
			}
		});
	},
	onRecordSelect: function() {
		var me = this;

		var cnt = this.Grid.getSelectionModel().getSelection().length;
		if (cnt > 0) {
			me.selectedLabel.setText(ru_word_case('Выбран', 'Выбрано', 'Выбрано', cnt) + ' ' + cnt + ' ' + ru_word_case('документ', 'документа', 'документов', cnt));
			me.queryById('previewButton').enable();
			me.queryById('signButton').enable();
		} else {
			me.selectedLabel.setText('');
			me.queryById('previewButton').disable();
			me.queryById('signButton').disable();
		}
	},
	initComponent: function() {
		var me = this;

		me.selectedLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			text: ''
		});

		me.Grid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'grid-common',
			columns: [{
				width: 280,
				header: 'Документ',
				dataIndex: 'Document_Name'
			}, {
				width: 180,
				header: 'Номер',
				dataIndex: 'Document_Num'
			}, {
				width: 100,
				header: 'Дата',
				dataIndex: 'Document_Date',
				renderer: Ext6.util.Format.dateRenderer('d.m.Y')
			}],
			region: 'center',
			selModel: {
				selType: 'checkboxmodel',
				width: 65,
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
					},
					deselect: function(model, record, index) {
						me.onRecordSelect();
					}
				}
			},
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EMDRegistry_ObjectID', type: 'int' },
					{ name: 'EMDPersonRole_id', type: 'int', allowNull: true },
					{ name: 'ApprovalObjectList_id', type: 'int', allowNull: true },
					{ name: 'EMDVersion_id', type: 'int', allowNull: true },
					{ name: 'Document_Name', type: 'string' },
					{ name: 'Document_Num', type: 'string' },
					{ name: 'Document_Date', type: 'date', dateFormat: 'd.m.Y' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EMD&m=loadEMDSignWindow',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'Document_Date'
				]
			})
		});

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: true,
			autoHeight: true,
			bodyPadding: '0 10',
			layout: 'vbox',
			url: '/?c=EMD&m=loadEMDSignWindow',
			region: 'east',
			width: 300,
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: []
				})
			}),
			defaults: {
				minWidth: 275,
				width: 275
			},
			fieldDefaults: {
				labelAlign: 'top',
				msgTarget: 'side'
			},
			items: [{
                xtype: 'swMedStaffFactCombo',
                name : 'MedStaffFact_id',
                fieldLabel: 'Сотрудник',
                allowBlank: false,
				hidden: true,
				autoload: false
            },
			{
				xtype: 'swEMDPersonRole',
				name : 'EMDPersonRole_id',
				hidden: true,
				fieldLabel: 'Роль',
				allowBlank: false,
				listeners: {
					select: function(combo, newValue) {}
				}
			},{
				fieldLabel: 'Сертификат',
				allowBlank: false,
				name: 'EMDCertificate_id',
				xtype: 'swEMDCertificateCombo'
			}]
		});

		Ext6.apply(me, {
			items: [
				me.Grid,
				me.formPanel
			],
			buttons: [me.selectedLabel, '->', {
				cls: 'buttonCancel',
				margin: 0,
				handler: function() {
					me.hide();
				},
				text: 'Отмена'
			}, {
				handler: function() {
					me.generateEMDRegistryForAll(true);
				},
				itemId: 'previewButton',
				cls: 'buttonCancel',
				text: 'Предварительный просмотр'
			}, {
				handler: function() {
					me.generateEMDRegistryForAll();
				},
				itemId: 'signButton',
				cls: 'buttonAccept',
				margin: '0 19 0 0',
				text: 'Подписать'
			}]
		});

		this.callParent(arguments);
	}
});