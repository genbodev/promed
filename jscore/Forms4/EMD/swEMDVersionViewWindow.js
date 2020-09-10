/**
 * swEMDVersionViewWindow - Версии документа
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDVersionViewWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDVersionViewWindow',
	autoShow: false,
	maximized: false,
	width: 900,
	height: 600,
	resizable: false,
	maximizable: false,
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'Версии документа',
	modal: true,
	header: true,
	layout: 'border',
	constrain: true,
	show: function(data) {
		var me = this;
		this.callParent(arguments);

		if (!data || !data.EMDRegistry_ObjectName || !data.EMDRegistry_ObjectID) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				me.hide();
			});
			return false;
		}

		me.EMDRegistry_ObjectName = data.EMDRegistry_ObjectName;
		me.EMDRegistry_ObjectID = data.EMDRegistry_ObjectID;
		me.DocPanel.setHtml('');

		this.doLoad();
	},
	onRecordSelect: function() {
		var me = this;
		// в правой части грузим PDF
		me.DocPanel.setHtml('');
		var record = this.Grid.getSelectionModel().getSelectedRecord();
		if (record) {
			if (record.get('EMDFileFormat_id') == 1 && typeof me.EMDRegistry_ObjectName == 'string' && me.EMDRegistry_ObjectName.inlist(['ReportRun', 'EvnPS', 'EvnMse', 'EvnVK', 'EvnPrescrMse', 'PersonPrivilegeReq', 'PersonPrivilegeReqAns'])) {
				if (record.get('EMDVersion_id')) {
					me.getLoadMask('Загрузка документа').show();
					Ext6.Ajax.request({
						url: '/?c=EMD&m=getStampedPdf',
						params: {
							EMDVersion_id: record.get('EMDVersion_id')
						},
						callback: function(options, success, response) {
							me.getLoadMask().hide();

							if (response && response.responseText) {
								var response_obj = Ext6.JSON.decode(response.responseText);
								if (response_obj.EMDVersion_FilePath) {
									me.showPdf(response_obj.EMDVersion_FilePath);
								}
							}
						}
					});
				}
			} else {
				if (record.get('EMDVersion_FilePath')) {
					if (record.get('EMDFileFormat_id') == 1) {
						me.showPdf(record.get('EMDVersion_FilePath'));
					} else {
						me.showXml(record.get('EMDVersion_FilePath'));
					}
				}
			}
		}
	},
	showPdf: function(EMDVersion_FilePath) {
		this.DocPanel.setHtml('<object data="' + EMDVersion_FilePath + '" type="application/pdf" width="100%" height="100%"><p><b>Внимание</b>: Ваш браузер не поддерживает просмотр PDF, пожалуйста откройте документ по ссылке: <a target="_blank" href="' + EMDVersion_FilePath + '">просмотреть документ</a>.</p></object>');
	},
	showXml: function(EMDVersion_FilePath) {
		this.DocPanel.setHtml('<iframe src="' + EMDVersion_FilePath + '" width="100%" height="100%" border="0"></iframe>');
	},
	onLoadGrid: function() {
		var me = this;

		me.Grid.getSelectionModel().select(0);
	},
	doLoad: function() {
		var me = this;

		this.Grid.getStore().removeAll();
		this.Grid.getStore().load({
			params: {
				EMDRegistry_ObjectName: me.EMDRegistry_ObjectName,
				EMDRegistry_ObjectID: me.EMDRegistry_ObjectID
			}
		});
	},
	doVerify: function() {
		var me = this;

		var record = me.Grid.getSelectionModel().getSelectedRecord();
		if (!record || !record.get('EMDSignatures_id')) {
			return false;
		}

		me.mask('Проверка подписи');
		Ext6.Ajax.request({
			url: '/?c=EMD&m=verifySignature',
			params: {
				EMDSignatures_id: record.get('EMDSignatures_id')
			},
			callback: function (opt, success, response) {
				me.unmask();
				var result = Ext6.JSON.decode(response.responseText);
				if (result.success) {
					if (!Ext6.isEmpty(result.valid)) {
						if (result.valid == 2) {
							sw.swMsg.alert(langs('Сообщение'), langs('Верная электронная цифровая подпись'));
						} else if (result.valid == 1) {
							sw.swMsg.alert(langs('Сообщение'), langs('Неверная электронная цифровая подпись'));
						}
					}
				}
			}
		});
	},
	doExport: function(options) {
		if (!options) {
			options = {};
		}

		var me = this;

		var record = me.Grid.getSelectionModel().getSelectedRecord();
		if (!record || !record.get('EMDSignatures_id')) {
			return false;
		}

		if (!options.ignoreNotActual && record.get('EMDVersion_IsActual') != 2) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.YESNO,
				fn: function(buttonId) {
					options.ignoreNotActual = true;
					if (buttonId == 'yes') {
						me.doExport(options);
					}
				},
				icon: Ext6.Msg.WARNING,
				msg: "Вы экспортируете неактуальную версию документа. Продолжить?",
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		window.open('/?c=EMD&m=exportSignature&EMDSignatures_id=' + record.get('EMDSignatures_id'), '_blank');
	},
	initComponent: function() {
		var me = this;

		me.Grid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			border: false,
			region: 'west',
			width: 250,
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
					}
				}
			},
			store: {
				fields: [
					{ name: 'EMDSignatures_id', type: 'int' },
					{ name: 'EMDVersion_id', type: 'int' },
					{ name: 'EMDVersion_insDT', type: 'date', dateFormat: 'd.m.Y H:i:s' },
					{ name: 'EMDVersion_VersionNum', type: 'int' },
					{ name: 'EMDVersion_IsActual', type: 'int' },
					{ name: 'pmUser_Name', type: 'string' },
					{ name: 'EMDVersion_FilePath', type: 'string' },
					{ name: 'EMDFileFormat_id', type: 'int' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EMD&m=loadEMDVersionList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: {
					property: 'EMDVersion_insDT',
					direction: 'DESC'
				},
				listeners: {
					load: function(grid, records) {
						me.onLoadGrid();
					}
				}
			},
			columns: [
				{flex: 1, dataIndex: 'EMDRegistry_Info', renderer: function(val, metaData, rec) {
					var s = '<span style="font-weight: 500;">Версия ' + rec.get('EMDVersion_VersionNum') + ' от ' + rec.get('EMDVersion_insDT').format('d.m.Y / H:i') + '</span><br>';
					s += '<span style="font-weight: 300;">' + rec.get('pmUser_Name') + '</span><br>';
					s += '<span style="font-weight: 300;">Формат: ' + rec.get('EMDFileFormat_Name') + '</span>';
					if (rec.get('EMDVersion_IsActual') == 2) {
						s += '<br><span style="font-weight: 500;">Актуальная версия</span>';
					}
					return s;
				}},
			]
		});

		this.DocPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'center',
			html: ""
		});

		Ext6.apply(me, {
			items: [
				me.Grid,
				me.DocPanel
			],
			buttons: [{
				handler: function () {
					me.doVerify();
				},
				text: 'Проверить подпись'
			}, {
				handler: function () {
					me.doExport();
				},
				text: 'Экспорт'
			}, '->', {
				handler: function () {
					me.hide();
				},
				cls: 'flat-button-primary',
				text: 'Закрыть'
			}]
		});

		this.callParent(arguments);
	}
});