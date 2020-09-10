/**
 * swEMDCertificateViewWindow - Сертификаты
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDCertificateViewWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDCertificateViewWindow',
	autoShow: false,
	maximized: false,
	width: 900,
	height: 400,
	resizable: false,
	maximizable: false,
	findWindow: false,
	closable: true,
	cls: 'arm-window-new',
	title: 'Сертификаты',
	modal: true,
	header: true,
	layout: 'border',
	constrain: true,
	show: function(data) {
		var me = this;
		this.callParent(arguments);

		if (!data || !data.pmUser_id) {
			Ext6.Msg.alert(langs('Сообщение'), langs('Неверные параметры'), function() {
				me.hide();
			});
			return false;
		}

		if (data.callback) {
			me.callback = data.callback;
		} else {
			me.callback = Ext6.emptyFn;
		}

		me.pmUser_id = data.pmUser_id;

		this.doLoad();
	},
	onRecordSelect: function() {
		var me = this;
		me.Grid.down('#action_properties').disable();
		me.Grid.down('#action_delete').disable();

		var record = this.Grid.getSelectionModel().getSelectedRecord();
		if (record) {
			if (record.get('EMDCertificate_id')) {
				me.Grid.down('#action_properties').enable();
				me.Grid.down('#action_delete').enable();
			}
		}
	},
	onLoadGrid: function() {
		var me = this;
		me.filterGrid();
		me.onRecordSelect();
	},
	doLoad: function() {
		var me = this;

		this.Grid.getStore().removeAll();
		this.Grid.getStore().load({
			params: {
				pmUser_id: me.pmUser_id
			}
		});
	},
	openEMDCertificateEditWindow: function(action) {
		var me = this;

		var params = {
			action: action,
			pmUser_id: me.pmUser_id,
			callback: function() {
				me.Grid.getStore().reload();
				me.callback();
			}
		};

		if (action != 'add') {
			var record = me.Grid.getSelectionModel().getSelectedRecord();
			if (record && record.get('EMDCertificate_id')) {
				params.EMDCertificate_id = record.get('EMDCertificate_id');
			} else {
				return false;
			}
		}

		getWnd('swEMDCertificateEditWindow').show(params);
	},
	deleteEMDCertificate: function() {
		var me = this;

		var record = me.Grid.getSelectionModel().getSelectedRecord();

		if (record && record.get('EMDCertificate_id')) {
			checkDeleteRecord({
				callback: function() {
					me.mask('Удаление сертификата...');
					Ext6.Ajax.request({
						url: '/?c=EMD&m=deleteEMDCertificate',
						params: {
							EMDCertificate_id: record.get('EMDCertificate_id')
						},
						callback: function() {
							me.unmask();
							me.Grid.getStore().reload();
							me.callback();
						}
					})
				}
			}, 'сертификат');
		}
	},
	filterGrid: function() {
		var me = this;
		var filterType = me.queryById('filterType').getValue();
		var date = getValidDT(getGlobalOptions().date, '') || new Date();
		me.Grid.getStore().clearFilter();
		me.Grid.getStore().filterBy(function(rec) {
			if (filterType != 3 && rec.get('EMDCertificate_endDT') && rec.get('EMDCertificate_endDT') < date) {
				return false;
			}

			if (filterType == 2 && rec.get('EMDCertificate_IsNotUse') && rec.get('EMDCertificate_IsNotUse') == 2) {
				return false;
			}

			return true;
		});
	},
	initComponent: function() {
		var me = this;

		me.Grid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			border: false,
			region: 'center',
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						me.onRecordSelect();
					}
				}
			},
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					var date = getValidDT(getGlobalOptions().date, '') || new Date();
					var cls = '';
					if (record.get('EMDCertificate_endDT') && record.get('EMDCertificate_endDT') < date) {
						cls = cls + 'x-grid-rowgray ';
					}
					return cls;
				}
			},
			dockedItems: [{
				padding: "0 10px",
				xtype: 'toolbar',
				dock: 'top',
				cls: 'grid-toolbar',
				padding: 5,
				items: [{
					allowBlank: false,
					forceSelection: true,
					itemId: 'filterType',
					store: [
						[1, 'Действующие'],
						[2, 'Используемые'],
						[3, 'Все']
					],
					listeners: {
						'change': function() {
							me.filterGrid();
						}
					},
					value: 1,
					xtype: 'combo'
				}, '->', {
					xtype: 'button',
					text: 'Добавить',
					itemId: 'action_add',
					iconCls: 'action_add',
					handler: function(){
						me.openEMDCertificateEditWindow('add');
					}
				}, {
					xtype: 'button',
					text: 'Обновить',
					itemId: 'action_refresh',
					iconCls: 'action_refresh',
					handler: function(){
						me.doLoad();
					}
				}, {
					xtype: 'button',
					text: 'Свойства',
					itemId: 'action_properties',
					iconCls: 'action_properties',
					handler: function(){
						me.openEMDCertificateEditWindow('edit');
					}
				}, {
					xtype: 'button',
					text: 'Удалить',
					itemId: 'action_delete',
					iconCls: 'action_delete',
					handler: function(){
						me.deleteEMDCertificate();
					}
				}, {
					xtype: 'button',
					text: 'Печать',
					itemId: 'action_print',
					iconCls: 'action_print',
					handler: function(){
						Ext6.ux.GridPrinter.print(me.Grid);
					}
				}]
			}],
			listeners: {
				itemdblclick: function() {
					me.openEMDCertificateEditWindow('edit');
				}
			},
			store: {
				fields: [
					{ name: 'EMDCertificate_id', type: 'int' },
					{ name: 'EMDCertificate_IsNotUse', type: 'int' },
					{ name: 'EMDCertificate_Name', type: 'string' },
					{ name: 'EMDCertificate_begDT', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'EMDCertificate_endDT', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'EMDCertificate_CommonName', type: 'string' },
					{ name: 'EMDCertificate_SHA1', type: 'string' }
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EMD&m=loadEMDCertificateList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'EMDCertificate_Name'
				],
				listeners: {
					load: function(grid, records) {
						me.onLoadGrid();
					}
				}
			},
			columns: [
				{text: 'Статус', width: 150, dataIndex: 'EMDCertificate_IsNotUse', renderer: function(val, metaData, rec) {
					var date = getValidDT(getGlobalOptions().date, '') || new Date();
					if (rec.get('EMDCertificate_endDT') && rec.get('EMDCertificate_endDT') < date) {
						return '<img src="/img/icons/emd/cert_expired.png" /> Просрочен';
					}

					if (val == 2) {
						return '<img src="/img/icons/emd/cert_disabled.png" /> Не используется';
					} else {
						return '<img src="/img/icons/emd/cert_ok.png" /> Используется';
					}
				}},
				{text: 'Наименование', width: 119, dataIndex: 'EMDCertificate_Name', flex: 1},
				{text: 'Дата начала', width: 120, dataIndex: 'EMDCertificate_begDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
				{text: 'Дата окончания', width: 120, dataIndex: 'EMDCertificate_endDT', renderer: Ext6.util.Format.dateRenderer('d.m.Y')},
				{text: 'Владелец', width: 130, dataIndex: 'EMDCertificate_CommonName'},
				{text: 'SHA-1', width: 120, dataIndex: 'EMDCertificate_SHA1'}
			]
		});

		Ext6.apply(me, {
			items: [
				me.Grid
			],
			buttons: ['->', {
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