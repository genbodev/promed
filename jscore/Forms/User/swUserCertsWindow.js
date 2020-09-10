/**
* swUserCertsWindow - форма просмотра сертификатов пользователя
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      User
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      06.11.2013
*/

sw.Promed.swUserCertsWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 700,
	height: 500,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	onCancel: function() {},	
	show: function() {
        sw.Promed.swUserCertsWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		
		this.Certs = [];
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if (arguments[0].onCancel && getPrimType(arguments[0].onCancel) == 'function' ) {
			this.onCancel = arguments[0].onCancel;
		}
		
		if (arguments[0].certs) {
			this.Certs = arguments[0].certs;
		}

		this.Person_Fio = '';
		if (arguments[0].Person_Fio) {
			this.Person_Fio = arguments[0].Person_Fio;
		}
		
		this.setTitle(lang['polzovatel_sertifikatyi']);
		
		this.UserCertsGrid.removeAll();
		this.UserCertsGrid.getGrid().getStore().loadData( win.Certs );
	
		this.center();
	},
	
	callback: Ext.emptyFn,
	
	doSave: function() {
		this.Certs = getStoreRecords(this.UserCertsGrid.getGrid().getStore());
		this.callback(this.Certs);
		this.hide();		
	},
	addCert: function() {
		var win = this;
		getWnd('swUserCertsUploadWindow').show({
			callback: function(newcert) {
				var cert_sn_g = newcert.cert_sn + ' ' + newcert.cert_g;
				if (getRegionNick() == 'vologda' && (!cert_sn_g || cert_sn_g.toLowerCase().replace(/ё/g, 'е').replace(/ /g, '_') != win.Person_Fio.toLowerCase().replace(/ё/g, 'е').replace(/ /g, '_'))) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								win.insertCertInGrid(newcert);
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: 'ФИО пользователя в системе и сертификате ЭП отличаются. Добавить данный сертификат пользователю?',
						title: 'Вопрос'
					});
				} else {
					win.insertCertInGrid(newcert);
				}
			}
		});
	},
	insertCertInGrid: function(newcert) {
		var win = this;
		var added = false;
		win.UserCertsGrid.getGrid().getStore().each(function(rec) {
			if (rec.get('cert_id') == newcert.cert_id) {
				added = true;
			}
		});

		if (added) {
			sw.swMsg.alert("Ошибка", "Данный сертификат уже добавлен");
			return false;
		}

		win.UserCertsGrid.getGrid().getStore().loadData( [ newcert ], true );
	},
	deleteCert: function() {
		var record = this.UserCertsGrid.getGrid().getSelectionModel().getSelected();
		if (!record) {
			return false;
		}
		
		this.UserCertsGrid.getGrid().getStore().remove(record);
	},
	initComponent: function() {
    	
		var win = this;
		
		this.UserCertsGrid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			toolbar: true,
			actions: [
				{ name: 'action_add', hidden: false, handler: function() {
					win.addCert();
				}},
				{ name: 'action_edit', hidden: true, disabled: true },
				{ name: 'action_view', hidden: true, disabled: true },
				{ name: 'action_refresh', hidden: true, disabled: true },
				{ name: 'action_delete', hidden: false, handler: function() {
					win.deleteCert();
				}}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			border: false,
			autoLoadData: false,
			useEmptyRecord: false,
			stringfields: [
				{name: 'cert_id', type: 'string', hidden: true, key: true},
				{name: 'cert_name', header: lang['nazvanie'], type: 'string', id: 'autoexpand'},
				{name: 'cert_sha1', header: 'SHA-1', type: 'string', width: 250},
				{name: 'cert_base64', header: 'BASE64', type: 'string', hidden: true},
				{name: 'cert_begdate', header: lang['data_nachala'], renderer: function(value) {
					if (!Ext.isEmpty(value)) {
						return new Date(value*1000).dateFormat('d.m.Y');
					}
				}, width: 80},
				{name: 'cert_enddate', header: lang['data_okonchaniya'], renderer: function(value) {
					if (!Ext.isEmpty(value)) {
						return new Date(value*1000).dateFormat('d.m.Y');
					}
				}, width: 80}
			]
		});
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['sohranit'],
				iconCls: 'ok16',
				handler: this.doSave.createDelegate(this)
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
			items: [this.UserCertsGrid]

		});
		
		sw.Promed.swUserCertsWindow.superclass.initComponent.apply(this, arguments);
	}
});