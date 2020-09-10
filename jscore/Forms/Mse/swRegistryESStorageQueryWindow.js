/**
 * swRegistryESStorageQueryWindow - окно просмотра реестров ЛВН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package            Mse
 * @access            public
 * @copyright        Copyright (c) 2014 Swan Ltd.
 * @author            Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version            26.09.2014
 */

/*NO PARSE JSON*/

sw.Promed.swRegistryESStorageQueryWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swRegistryESStorageQueryWindow',
	width: 500,
	autoHeight: true,
	maximized: false,
	maximizable: false,
	layout: 'form',
	title: 'Запрос получения номеров ЭЛН',
	doQuery: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();

		if ( !base_form.isValid() )
		{
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = {
			RegistryESStorage_NumQuery: base_form.findField('RegistryESStorage_NumQuery').getValue(),
			RegistryESStorage_Count: base_form.findField('RegistryESStorage_Count').getValue()
		};

		var doc_signtype = getOthersOptions().doc_signtype;

		// выбираем сертификат
		getWnd('swCertSelectWindow').show({
			signType: doc_signtype,
			callback: function (cert) {
				params.certhash = cert.Cert_Thumbprint;
				params.certbase64 = cert.Cert_Base64;

				if (doc_signtype && doc_signtype.inlist(['authapplet', 'authapi', 'authapitomee'])) {
					params.needHash = 1;
				}

				win.getLoadMask('Создание запроса номеров ЭЛН').show();
				Ext.Ajax.request({
					params: params,
					callback: function (options, success, response) {
						win.getLoadMask().hide();
						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if (response_obj.xml) {
								if (doc_signtype && doc_signtype == 'authapplet') {
									// хотим подписать с помощью AuthApplet
									sw.Applets.AuthApplet.signText({
										text: response_obj.Base64ToSign,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
											params.signType = doc_signtype;
											params.xml = response_obj.xml;
											params.Hash = response_obj.Hash;
											params.SignedData = sSignedData;

											win.getLoadMask('Выполняется обмен с ФСС, пожалуйста подождите...').show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=RegistryESStorage&m=queryRegistryESStorage',
												callback: function(options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														win.callback();
														win.hide();
													}
												}
											});
										}
									});
								} else if (doc_signtype && doc_signtype.inlist(['authapi', 'authapitomee'])) {
									// хотим подписать с помощью AuthApi
									sw.Applets.AuthApi.signText({
										win: win,
										text: response_obj.Base64ToSign,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function (sSignedData) {
											params.signType = doc_signtype;
											params.xml = response_obj.xml;
											params.Hash = response_obj.Hash;
											params.SignedData = sSignedData;

											win.getLoadMask('Выполняется обмен с ФСС, пожалуйста подождите...').show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=RegistryESStorage&m=queryRegistryESStorage',
												callback: function(options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														win.callback();
														win.hide();
													}
												}
											});
										}
									});
								} else {
									// подписываем файл экспорта с помощью КриптоПро
									sw.Applets.CryptoPro.signXML({
										xml: response_obj.xml,
										Cert_Thumbprint: cert.Cert_Thumbprint,
										callback: function(sSignedData) {
											params.signType = 'cryptopro';
											params.xml = sSignedData;
											// 3. пробуем отправить в ФСС
											win.getLoadMask('Выполняется обмен с ФСС, пожалуйста подождите...').show();
											Ext.Ajax.request({
												params: params,
												url: '/?c=RegistryESStorage&m=queryRegistryESStorage',
												callback: function(options, success, response) {
													win.getLoadMask().hide();
													if (success) {
														win.callback();
														win.hide();
													}
												}
											});
										}
									});
								}
							}
						}
					},
					url: '/?c=RegistryESStorage&m=getRegistryESStorageQuery'
				});
			}
		});
	},
	show: function() {
		sw.Promed.swRegistryESStorageQueryWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.callback = Ext.emptyFn;
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		var base_form = win.FormPanel.getForm();
		base_form.reset();

		win.getLoadMask('Получение номера запроса').show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.RegistryESStorage_NumQuery ) {
						base_form.findField('RegistryESStorage_NumQuery').setValue(response_obj.RegistryESStorage_NumQuery);
					} else {
						win.callback();
						win.hide();
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка расчёта суммарного сердечно-сосудистого риска');
				}

				base_form.findField('RegistryESStorage_Count').focus();
			},
			url: '/?c=RegistryESStorage&m=loadRegistryESStorageNumQuery'
		});
	},
	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 250,
			items: [{
				allowBlank: false,
				fieldLabel: 'Номер запроса',
				disabled: true,
				name: 'RegistryESStorage_NumQuery',
				anchor: '100%',
				xtype: 'textfield'
			}, {
				allowBlank: false,
				fieldLabel: 'Запрашиваемое количество номеров',
				name: 'RegistryESStorage_Count',
				anchor: '100%',
				minValue: 1,
				maxValue: 1000,
				allowDecimals: false,
				allowNegative: false,
				xtype: 'numberfield'
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					handler: function () {
						this.doQuery();
					}.createDelegate(this),
					iconCls: 'save16',
					text: 'Запросить'
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
					text: lang['zakryit']
				}]
		});

		sw.Promed.swRegistryESStorageQueryWindow.superclass.initComponent.apply(this, arguments);
	}
});
