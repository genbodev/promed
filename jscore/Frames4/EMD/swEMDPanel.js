/**
 * Панель подписи документов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('sw.frames.EMD.swEMDPanel', {
	extend: 'Ext6.Panel',
	alias: 'widget.swEMDPanel',
	bodyStyle: 'background: transparent;',
	border: false,
	cls: 'emd-panel',
	padding: 0,
	layout: 'card',
	tinyMode: false,
	readOnly: false,
	SignCount: 0,
	MinSignCount: 0,
	setReadOnly: function(readOnly) {
		this.readOnly = readOnly;

		if (readOnly) {
			this.EMDSignButton.setTooltip('Документ не подписан');
			this.EMDSegmentedButton.items.items[1].hide();
		} else {
			this.EMDSignButton.setTooltip('Подписать документ');
			this.EMDSegmentedButton.items.items[1].show();
		}
	},
	IsSigned: null,
	setIsSigned: function(IsSigned) {
		this.EMDSignedButton.setText('');
		this.EMDSignedButton.setUserCls('');
		this.IsSigned = parseInt(IsSigned);
		switch(this.IsSigned) {
			case 2: // подписан
				this.setActiveItem(1);
				if (!this.tinyMode && this.MinSignCount > 0 && this.SignCount < this.MinSignCount) {
					this.EMDSignedButton.setUserCls('button-with-sign-text');
					this.EMDSignedButton.setIconCls('doc-signed-blank');
					this.EMDSignedButton.setTooltip(this.SignCount + ' из ' + this.MinSignCount);
					this.EMDSignedButton.setText(this.SignCount);
				} else {
					this.EMDSignedButton.setIconCls('doc-signed' + (this.tinyMode ? '-tiny' : ''));
					this.EMDSignedButton.setTooltip('Документ подписан');
				}
				break;
			case 1: // не актуален
				this.setActiveItem(1);
				this.EMDSignedButton.setIconCls('doc-notactual' + (this.tinyMode ? '-tiny' : ''));
				this.EMDSignedButton.setTooltip('Документ не актуален');
				break;
			default: // не подписан
				if (!this.tinyMode && this.SignCount > 0 && this.MinSignCount > 0 && this.SignCount < this.MinSignCount) {
					this.setActiveItem(1);
					this.EMDSignedButton.setUserCls('button-with-sign-text');
					this.EMDSignedButton.setIconCls('doc-unsigned');
					this.EMDSignedButton.setTooltip(this.SignCount + ' из ' + this.MinSignCount);
					this.EMDSignedButton.setText(this.SignCount);
				} else {
					this.setActiveItem(0);
				}
				break;
		}
	},
	setParams: function(params) {
		this.EMDRegistry_ObjectName = params.EMDRegistry_ObjectName;
		this.EMDRegistry_ObjectID = params.EMDRegistry_ObjectID;
		this.beforeSign = params.beforeSign || null;
	},
	setEMDRegistry_ObjectName: function(value) {
		this.EMDRegistry_ObjectName = value;
	},
	setEMDRegistry_ObjectID: function(value) {
		this.EMDRegistry_ObjectID = value;
	},
	setEMDRegistry_ObjectID: function(value) {
		this.EMDRegistry_ObjectID = value;
	},
	setSignCount: function(value) {
		this.SignCount = value;
	},
	setMinSignCount: function(value) {
		this.MinSignCount = value;
	},
	loadData: function() {
		var me = this;
		var base_form = me.cardPanel.getForm();
		me.EMDMenu.mask(LOAD_WAIT);
		base_form.reset();
		base_form.load({
			params: {
				EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
				EMDRegistry_ObjectID: me.EMDRegistry_ObjectID
			},
			failure: function() {
				me.EMDMenu.unmask();
			},
			success: function() {
				me.EMDMenu.unmask();
			}
		});
	},
	onSignCallback: function(data) {
		var me = this;

		if (data.preloader) {
			me.setActiveItem(2);
		}

		if (data.success) {
			// получаем после подписи новое количество подписей
			Ext6.Ajax.request({
				url: '/?c=EMD&m=getSignStatus',
				params: {
					EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
					EMDRegistry_ObjectID: me.EMDRegistry_ObjectID
				},
				callback: function(options, success, response)  {
					if (success) {
						var result  = Ext6.util.JSON.decode(response.responseText);
						if (result.success) {
							me.setSignCount(result.SignCount);
							me.setMinSignCount(result.MinSignCount);
							me.setIsSigned(result.IsSigned);
						} else {
							me.setIsSigned(2);
						}
					} else {
						me.setIsSigned(2);
					}
				}
			});
		}

		if (data.error) {
			me.setIsSigned(me.IsSigned);
		}
	},
	doSign: function() {
		if (this.readOnly) {
			return false;
		}

		var me = this;

		if (typeof me.beforeSign == 'function') {
			if (!me.beforeSign()) {
				return false;
			}
		}

		if (!me.EMDRegistry_ObjectID) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Подписание не возможно, т.к. объект не сохранён'));
			return false;
		}

		var EMDRegistry_ObjectName =  me.EMDRegistry_ObjectName;
		var EMDRegistry_ObjectID =  me.EMDRegistry_ObjectID;
		getWnd('swEMDSignWindow').show({
			EMDRegistry_ObjectName: EMDRegistry_ObjectName,
			EMDRegistry_ObjectID: EMDRegistry_ObjectID,
			callback: function(data) {
				if (EMDRegistry_ObjectName == me.EMDRegistry_ObjectName && EMDRegistry_ObjectID == me.EMDRegistry_ObjectID) {
					me.onSignCallback(data);
				}
			}
		});
	},
	initComponent: function() {
		var me = this;

		this.EMDSignButton = Ext6.create('Ext6.button.Button', {
			userCls: 'button-without-frame',
			style: {
				'color': 'transparent'
			},
			iconCls: 'panicon-doc-sign doc-sign' + (this.tinyMode ? '-tiny' : ''),
			refId: 'emdButton',
			tooltip: langs('Подписать документ'),
			handler: function() {
				me.doSign();
			}
		});

		this.verifyPanel = Ext6.create('Ext6.panel.Panel', {
			title: 'Верификация документа',
			border: false,
			cls: 'emd-verify-panel',
			html: ''
		});

		this.cardPanel = Ext6.create('Ext6.form.Panel', {
			region: 'center',
			layout: 'card',
			bodyStyle: "border-width: 0 1px 0 0;",
			url: '/?c=EMD&m=loadSignaturesInfo',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields:[
						{name: 'pmUser_Name'},
						{name: 'EMDVersion_VersionNum'},
						{name: 'Signatures_insDate'},
						{name: 'Signatures_insTime'},
						{name: 'Document_Link'},
						{name: 'Signatures_Hash'}
					]
				})
			}),
			items: [{
				title: 'Информация о подписи',
				border: false,
				defaults: {
					labelWidth: 130,
					style: 'margin-left: 20px'
				},
				items: [{
					fieldLabel: 'Пользователь',
					name: 'pmUser_Name',
					xtype: 'displayfield'
				}, {
					fieldLabel: 'Версия',
					name: 'EMDVersion_VersionNum',
					xtype: 'displayfield'
				}, {
					layout: 'column',
					border: false,
					items: [{
						fieldLabel: 'Дата',
						style: 'margin-right: 20px',
						labelWidth: 130,
						name: 'Signatures_insDate',
						xtype: 'displayfield'
					}, {
						fieldLabel: 'Время',
						labelWidth: 50,
						name: 'Signatures_insTime',
						xtype: 'displayfield'
					}]
				}, {
					fieldLabel: 'Документ',
					name: 'Document_Link',
					xtype: 'displayfield'
				}, {
					fieldLabel: 'Хэш',
					width: 400,
					name: 'Signatures_Hash',
					xtype: 'displayfield'
				}]
			}, me.verifyPanel]
		});

		this.EMDSegmentedButton = Ext6.create('Ext6.button.Segmented', {
			dock: 'right',
			layout: 'vbox',
			width: 150,
			defaults: {
				height: 35,
				width: 150,
				textAlign: 'left'
			},
			items: [{
				handler: function() {
					me.cardPanel.setActiveItem(0);
				},
				pressed: true,
				itemId: 'aboutItem',
				text: 'Сертификат'
			}, {
				handler: function() {
					me.doSign();
				},
				text: 'Подписать'
			}, {
				handler: function() {
					getWnd('swEMDVersionViewWindow').show({
						EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
						EMDRegistry_ObjectID: me.EMDRegistry_ObjectID
					});
				},
				text: 'Версии'
			}]
		});

		this.EMDMenu = Ext6.create('Ext6.menu.Menu', {
			cls: 'emd-menu',
			height: 250,
			layout: 'border',
			listeners: {
				'beforeshow': function() {
					me.verifyPanel.setHtml('');
					me.cardPanel.setActiveItem(0);
					me.EMDMenu.down('#aboutItem').setPressed();
				},
				'show': function() {
					me.loadData();
				}
			},
			margin: 0,
			padding: 0,
			width: 650,
			dockedItems: [
				me.EMDSegmentedButton
			],
			items: [
				me.cardPanel
			]
		});

		this.EMDSignedButton = Ext6.create('Ext6.button.Button', {
			userCls: 'button-without-frame',
			style: {
				'color': 'transparent'
			},
			iconCls: 'doc-signed' + (this.tinyMode ? '-tiny' : ''),
			refId: 'emdButton',
			tooltip: langs('Документ подписан'),
			menu: me.EMDMenu
		});

		this.EMDPrealoader = Ext6.create('Ext6.panel.Panel', {
			bodyStyle: {
				background: 'transparent'
			},
			height: 25,
			width: 25,
			padding: 4,
			border: false,
			html: '<img src="/img/icons/2017/preloader.gif" width="16" height="16" />'
		});

		Ext6.apply(me, {
			items: [
				me.EMDSignButton,
				me.EMDSignedButton,
				me.EMDPrealoader
			]
		});

		me.callParent(arguments);
	}
});