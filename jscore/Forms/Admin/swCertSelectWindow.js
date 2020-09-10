/**
 * swCertSelectWindow - форма выбора сертификата
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      30.01.2015
 */

sw.Promed.swCertSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 900,
	height: 400,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	title: lang['vyibor_sertifikata'],
	onCancel: function() {},
	show: function() {
		sw.Promed.swCertSelectWindow.superclass.show.apply(this, arguments);

		var win = this;

		win.signType = 'cryptopro';

		if (arguments[0]) {
			if (arguments[0].callback && typeof arguments[0].callback == 'function') {
				win.callback = arguments[0].callback;
			}

			if (arguments[0].onCancel && typeof arguments[0].onCancel == 'function') {
				win.onCancel = arguments[0].onCancel;
			}

			if (arguments[0].signType) {
				win.signType = arguments[0].signType;
			}
		}

		win.CertGrid.removeAll();

		// получаем список связанных с пользователем сертификатов
		if (win.signType.inlist(['authapplet', 'authapi', 'authapitomee'])) {
			win.CertGrid.loadData();
		} else {
			Ext.Ajax.request({
				url: '/?c=ElectronicDigitalSign&m=getCertificateList',
				params: {},
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.certs) {
							// получаем список сертификатов с помощью КриптоПро
							var records = sw.Applets.CryptoPro.getCertList({
								allowedCertList: response_obj.certs,
								callback: function (records) {
									win.CertGrid.getGrid().getStore().loadData(records);
								}
							});
						}
					}
				}
			});
		}

		win.center();
	},

	callback: Ext.emptyFn,

	CertSelect: function() {
		var record = this.CertGrid.getGrid().getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('Cert_id'))) {
			return false;
		}

		this.callback(record.data);
		this.hide();
	},

	initComponent: function() {

		var win = this;

		this.CertGrid = new sw.Promed.ViewFrame({
			uniqueId: true,
			toolbar: false,
			dataUrl: '/?c=ElectronicDigitalSign&m=getCertificateGrid',
			onEnter: this.CertSelect.createDelegate(this),
			actions: [
				{ name: 'action_add', hidden: true, disabled: true },
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: true, disabled: true }
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{name: 'Cert_id', type: 'int', hidden: true, key: true},
				{name: 'Cert_Base64', type: 'string', hidden: true},
				{name: 'Cert_SubjectName', header: lang['komu_vyidan'], type: 'string', id: 'autoexpand'},
				{name: 'Cert_IssuerName', header: lang['kem_vyidan'], type: 'string', width: 200},
				{name: 'Cert_ValidFromDate', header: lang['data_nachala'], type: 'date', width: 80},
				{name: 'Cert_ValidToDate', header: lang['data_okonchaniya'], type: 'date', width: 80},
				{name: 'Cert_Thumbprint', header: lang['hesh'], type: 'string', width: 280},
			]
		});

		this.CertGrid.getGrid().on('rowdblclick', this.CertSelect.createDelegate(this));

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.CertSelect.createDelegate(this)
			},
				'-',
				{
					text: lang['zakryit'],
					iconCls: 'close16',
					handler: function(button, event) {
						win.onCancel();
						win.hide();
					}
				}],
			items: [this.CertGrid]

		});

		sw.Promed.swCertSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});