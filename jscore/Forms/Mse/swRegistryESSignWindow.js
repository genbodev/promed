/**
 * swRegistryESSignWindow - окно подписи ЛВН в реестре ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Dmitriy Vlasenko
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESSignWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRegistryESSignWindow',
	layout: 'form',
	autoHeight: true,
	width: 700,
	action: 'view',
	title: 'Подпись уполномоченным лицом',
	selectCert: function(ExpertMedStaffType_id) {
		var grid = this.RegistryESIndividCertGrid.getGrid();
		var index = grid.getStore().findBy(function(rec) {
			return (rec.get('ExpertMedStaffType_id') == ExpertMedStaffType_id);
		});
		var record = grid.getStore().getAt(index);
		if (record) {
			getWnd('swCertSelectWindow').show({
				callback: function (cert) {
					record.set('pmUser_Name', getGlobalOptions().pmuser_name);
					record.set('RegistryESIndividCert_CertSubjectName', cert.Cert_SubjectName);
					record.set('RegistryESIndividCert_CertThumbprint', cert.Cert_Thumbprint);
					record.commit();
				}
			});
		}
	},
	resetCert: function(ExpertMedStaffType_id) {
		var grid = this.RegistryESIndividCertGrid.getGrid();
		var index = grid.getStore().findBy(function(rec) {
			return (rec.get('ExpertMedStaffType_id') == ExpertMedStaffType_id);
		});
		var record = grid.getStore().getAt(index);
		if (record) {
			record.set('pmUser_Name', '');
			record.set('RegistryESIndividCert_CertSubjectName', '');
			record.set('RegistryESIndividCert_CertThumbprint', '');
			record.commit();
		}
	},
	show: function(){
		sw.Promed.swRegistryESSignWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;

		var win = this;

		this.RegistryES_id = null;
		if (arguments[0] && arguments[0].RegistryES_id) {
			this.RegistryES_id = arguments[0].RegistryES_id;
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],
				title: lang['oshibka'],
				fn: function() {
					this.hide();
				}
			});
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		// прогрузить грид
		this.RegistryESIndividCertGrid.loadData({
			globalFilters: {
				RegistryES_id: win.RegistryES_id
			}
		});
	},
	doSign: function(data) {
		var win = this;
		// подписываем каждый ЛВН
		if (data && data.length > 0 && data[0].Evn_id) {
			var item = data.shift();
			var params = {
				SignObject: item.SignObject, // тип подписи
				Evn_id: item.Evn_id, // ид лвн или ид освобождения
				EvnStick_Num: item.EvnStick_Num // номер лвн
			};

			var cert = null;
			if (item.SignObject == 'VK') {
				if (win.certVKData != null) {
					cert = win.certVKData;
				} else {
					// идем к следующему
					win.doSign(data);
					return;
				}
			} else {
				if (win.certMOData != null) {
					cert = win.certMOData;
				} else {
					// идем к следующему
					win.doSign(data);
					return;
				}
			}

			var doc_signtype = getOthersOptions().doc_signtype;

			params.SignedToken = cert.Cert_Base64;

			if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
				params.needHash = 1;
			}

			win.getLoadMask('Получение данных для подписи ЛВН №' + item.EvnStick_Num).show();
			Ext.Ajax.request({
				url: '/?c=Stick&m=getWorkReleaseSslHash',
				params: params,
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.xml) {
							if (doc_signtype && doc_signtype == 'authapplet') {
								// хотим подписать с помощью AuthApplet
								sw.Applets.AuthApplet.signText({
									text: result.Base64ToSign,
									Cert_Thumbprint: cert.Cert_Thumbprint,
									callback: function (sSignedData) {
										params.signType = doc_signtype;
										params.Hash = result.Hash;
										params.SignedData = sSignedData;
										params.xml = result.xml;
										params.updateInRegistryESData = 1;
										win.getLoadMask('Подписание').show();
										Ext.Ajax.request({
											url: '/?c=Stick&m=signWorkRelease',
											params: params,
											callback: function (options, success, response) {
												win.getLoadMask().hide();
												if (success) {
													var result = Ext.util.JSON.decode(response.responseText);
													if (result.success) {
														win.doSign(data);
													}
												}
											}
										});
									}
								});
							} else if (doc_signtype && doc_signtype.inlist(['authapi', 'authapitomee'])) {
								// хотим подписать с помощью AuthApi
								sw.Applets.AuthApi.signText({
									win: win,
									text: result.Base64ToSign,
									Cert_Thumbprint: cert.Cert_Thumbprint,
									callback: function (sSignedData) {
										params.signType = doc_signtype;
										params.Hash = result.Hash;
										params.SignedData = sSignedData;
										params.xml = result.xml;
										params.updateInRegistryESData = 1;
										win.getLoadMask('Подписание').show();
										Ext.Ajax.request({
											url: '/?c=Stick&m=signWorkRelease',
											params: params,
											callback: function (options, success, response) {
												win.getLoadMask().hide();
												if (success) {
													var result = Ext.util.JSON.decode(response.responseText);
													if (result.success) {
														win.doSign(data);
													}
												}
											}
										});
									}
								});
							} else {
								sw.Applets.CryptoPro.signXML({
									xml: result.xml,
									Cert_Thumbprint: cert.Cert_Thumbprint,
									callback: function(sSignedData) {
										params.signType = 'cryptopro';
										params.xml = sSignedData;
										params.updateInRegistryESData = 1;
										win.getLoadMask('Подписание').show();
										Ext.Ajax.request({
											url: '/?c=Stick&m=signWorkRelease',
											params: params,
											callback: function(options, success, response) {
												win.getLoadMask().hide();
												if (success) {
													var result = Ext.util.JSON.decode(response.responseText);
													if (result.success) {
														win.doSign(data);
													}
												}
											}
										});
									}
								});
							}
						}
					}
				}
			});
		} else {
			// всё подписали.
			// проводим повторно проверку ФЛК, чтобы проставить статус реестру, либо обновить количество неподписанных ЛВН
			win.getLoadMask('Повторная проверка ФЛК реестра').show();
			Ext.Ajax.request({
				url: '/?c=RegistryES&m=doFLKControl',
				params: {
					RegistryES_id: win.RegistryES_id
				},
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					win.callback();
					win.hide();
				}
			});
		}
	},
	doSave: function() {
		var win = this;

		var grid = this.RegistryESIndividCertGrid.getGrid();
		var certVK = '';
		var certMO = '';
		var allowEmptyVK = false;
		var allowEmptyDoc = false;

		index = grid.getStore().findBy(function(rec) {
			return (rec.get('ExpertMedStaffType_id') == 1);
		});
		record = grid.getStore().getAt(index);
		if (record.get('RegistryESIndividCert_CertThumbprint') && record.get('RegistryESIndividCert_CertThumbprint').length > 0) {
			certVK = record.get('RegistryESIndividCert_CertThumbprint');
		} else {
			if (record.get('RegistryES_CountNoSign') > 0) {
				sw.swMsg.alert(lang['oshibka'], 'Не выбран сертификат для председателя ВК');
				return false;
			} else {
				allowEmptyVK = true;
			}
		}

		index = grid.getStore().findBy(function(rec) {
			return (rec.get('ExpertMedStaffType_id') == 3);
		});
		record = grid.getStore().getAt(index);
		if (record.get('RegistryESIndividCert_CertThumbprint') && record.get('RegistryESIndividCert_CertThumbprint').length > 0) {
			certMO = record.get('RegistryESIndividCert_CertThumbprint');
		} else {
			if (record.get('RegistryES_CountNoSign') > 0) {
				sw.swMsg.alert(lang['oshibka'], 'Не выбран сертификат для врача МО');
				return false;
			} else {
				allowEmptyDoc = true;
			}
		}

		win.certMOData = null;
		win.certVKData = null;

		var records = sw.Applets.CryptoPro.getCertList({
			allowedCertList: [certMO.toLowerCase(), certVK.toLowerCase()],
			callback: function(records) {
				for(var k in records) {
					if (records[k].Cert_Thumbprint && records[k].Cert_Thumbprint.toLowerCase() == certMO.toLowerCase()) {
						win.certMOData = records[k];
					}
					if (records[k].Cert_Thumbprint && records[k].Cert_Thumbprint.toLowerCase() == certVK.toLowerCase()) {
						win.certVKData = records[k];
					}
				}

				if (!allowEmptyDoc && win.certMOData == null) {
					sw.swMsg.alert(lang['oshibka'], 'Не найден сертификат для врача МО');
					return false;
				}

				if (!allowEmptyVK && win.certVKData == null) {
					sw.swMsg.alert(lang['oshibka'], 'Не найден сертификат для председателя ВК');
					return false;
				}

				// собираем данные из грида и посылаем на сервер, чтобы сохранить в истории
				win.getLoadMask('Сохранение выбранных сертификатов').show();
				Ext.Ajax.request({
					url: '/?c=RegistryES&m=saveRegistryESIndividCert',
					params: {
						RegistryESIndividCertGridData: Ext.util.JSON.encode(getStoreRecords( win.RegistryESIndividCertGrid.getGrid().getStore() )),
						RegistryES_id: win.RegistryES_id
					},
					callback: function (options, success, response) {
						win.getLoadMask().hide();
						if (success) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success) {
								// запрашиваем ид ЛВН/освобождений, которые необходимо подписать
								win.getLoadMask('Получение данных для подписи').show();
								Ext.Ajax.request({
									url: '/?c=RegistryES&m=getUnsignedData',
									params: {
										RegistryES_id: win.RegistryES_id
									},
									callback: function (options, success, response) {
										win.getLoadMask().hide();
										if (success) {
											var result = Ext.util.JSON.decode(response.responseText);
											win.doSign(result);
										}
									}
								});
							}
						}
					}
				});
			}
		});
	},

	initComponent: function() {
		var win = this;

		this.RegistryESIndividCertGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true, disable: true},
				{name: 'action_edit', hidden: true, disable: true},
				{name: 'action_view', hidden: true, disable: true},
				{name: 'action_delete', hidden: true, disable: true},
				{name: 'action_refresh', hidden: true, disable: true},
				{name: 'action_print' }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			height: 120,
			dataUrl: '/?c=RegistryES&m=loadRegistryESIndividCertGrid',
			uniqueId: true,
			paging: false,
			region: 'center',
			stringfields: [
				{name: 'ExpertMedStaffType_id', type: 'int', header: 'ID', key: true},
				{name: 'ExpertMedStaffType_Name', type: 'string', header: 'ЭП физического лица', width: 120},
				{name: 'pmUser_Name', type: 'string', header: 'Пользователь', width: 140},
				{name: 'RegistryESIndividCert_CertSubjectName', type: 'string', header: 'Сертификат', width: 140},
				{name: 'RegistryESIndividCert_CertThumbprint', type: 'string', header: 'Сертификат отпечаток', hidden: true},
				{name: 'RegistryES_CountNoSign', type: 'int', header: 'ЭЛН без подписи', width: 100},
				{name: 'RegistryESIndividCert_Buttons', renderer: function(v, p, r){
					var ExpertMedStaffType_id = r.get('ExpertMedStaffType_id');
					var disabled = false
					if (r.get('RegistryES_CountNoSign') == 0) {
						disabled = true;
					}
					if (ExpertMedStaffType_id == 3 && sw.Promed.MedStaffFactByUser && sw.Promed.MedStaffFactByUser.last && sw.Promed.MedStaffFactByUser.ARMType == 'vk') {
						disabled = true;
					}
					return '<input type="button" style="padding:2px; width:70px;" ' + ((disabled)?'disabled':'') + ' onClick="getWnd(\'swRegistryESSignWindow\').selectCert(' + ExpertMedStaffType_id + ');" value="Выбрать" /><input type="button" style="padding:2px; width:70px;" onClick="getWnd(\'swRegistryESSignWindow\').resetCert(' + ExpertMedStaffType_id + ');" value="Сбросить" />';
				}, header: '', width: 160}
			],
			toolbar: false,
			onLoadData: function() {

			}
		});

		this.FormPanel = new sw.Promed.FormPanel({
			border: true,
			bodyStyle:'width:100%;background:#DFE8F6;padding:5px;',
			autoHeight: true,
			labelWidth: 160,
			url: '/?c=RegistryES&m=saveRegistryES',
			timeout: 6000,
			items: [
				win.RegistryESIndividCertGrid
			]
		});

		Ext.apply(this, {
			items: [this.FormPanel],
			buttons: [
				{
					handler: function () {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'signature16',
					text: BTN_FRMSIGN
				},
				{
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: lang['otmena']
				}]
		});

		sw.Promed.swRegistryESViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
