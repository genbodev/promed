/**
 * Панель файлов
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
Ext6.define('common.EMK.EvnMediaDataPanel', {
	extend: 'swPanel',
	title: 'ФАЙЛЫ',
	layout: 'border',
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.Person_id = params.Person_id;

		if (me.EvnMediaDataGrid) {//yl:для грид-скролла
			me.EvnMediaDataGrid.params = {
				Person_id: me.Person_id
			}
		}

		me.loaded = false;
		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		this.EvnMediaDataGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	addEvnMediaData: function() {
		var me = this;
		var callback = function(data){
			var response_obj = Ext.util.JSON.decode(data);
			if (response_obj[0]) {
				me.load();
			}
		};

		var params = new Object();
		params.enableFileDescription = true;
		params.saveUrl = '/?c=EvnMediaFiles&m=uploadFile';
		params.saveParams = {
			Evn_id: me.Evn_id
		};
		params.saveParams.saveOnce = true;
		params.callback = callback;

		getWnd('swFileUploadWindow').show(params);
	},
	openFile: function(EvnMediaData_FilePath){
		window.open(EvnMediaData_FilePath, '_blank');
	},
	deleteEvnMediaData: function() {//yl:179013
		var me = this, record = me.EvnMediaDataGrid.getSelectionModel().getSelectedRecord();
		if (!record) return false;

		Ext6.Msg.show({
			title: langs("Удалить?"),
			msg: "Удалить файл \""+record.get("EvnMediaData_FileName")+"\"?",
			icon: Ext6.MessageBox.QUESTION,
			buttons: Ext6.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						url: "/?c=EvnMediaFiles&m=deleteEvnMediaFile",
						params: {
							EvnMediaData_id: record.get("EvnMediaData_id"),
							file_name: (record.get("EvnMediaData_FilePath").match(/[^\\/]+\.[^\\/]+$/) || []).pop()//только файл, а тут путь
						},
						callback: function (options, success, response) {
							if (success && response && response.responseText) {
								var resp = Ext.util.JSON.decode(response.responseText);
								if (Ext.isEmpty(resp.Error_Msg)) {
									me.load();
								} else {
									sw.swMsg.alert(langs("Ошибка"), resp.Error_Msg);
								}
							} else {
								sw.swMsg.alert(langs("Ошибка"), "При удалении файла возникли ошибки");
							}
						}
					});
				}
			}
		});

	},
	initComponent: function() {
		var me = this;
		me.filterField = Ext6.create('Ext6.form.Text', {
			triggers: {
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {

					}
				}
			},
			listeners: {
				'change': function (combo, newValue, oldValue) {
					me.EvnMediaDataGrid.getStore().clearFilter();
					var filters = [
						new Ext6.util.Filter({
							filterFn: function(rec) {
								var arrFilterFields = [
										'EvnMediaData_FileName',
										'EvnMediaData_insDT',
										'EvnMediaData_Comment'
									],
									BreakException = {},
									filter = false;

								try {
									arrFilterFields.forEach(function(fname){
										var val = rec.get(fname);
										if(fname == 'EvnMediaData_insDT')
											val = val.format('d.m.Y');
										if(val && (val.toString().indexOf(newValue) + 1)){
											filter = true;
											// Если нашли совпадение по одному полю, зачем искать по остальным
											throw BreakException;
										}
									});
								} catch (e) {
									if (e !== BreakException) throw e;
								}
								return filter;
							}
						})
					];
					me.EvnMediaDataGrid.getStore().filter(filters);
				}
			},
			minWidth: 42 + 500,
			emptyText: 'Поиск'
		});

		me.toolPanel = Ext6.create('Ext6.Toolbar', {
			height: 50.5,
			width: 40,
			border: false,
			style: {
				background: '#f5f5f5'
			},
			margin: '0 3 0 0',
			noWrap: true,
			right: 0,
			items: [
				me.filterField,
				'->',
				{
					text: 'Добавить файл',
					userCls: 'button-without-frame',
					iconCls: 'panicon-add',
					tooltip: langs('Добавить файл'),
					handler: function () {
						me.addEvnMediaData();
					}
				}, {
					text: 'Открыть',
					userCls: 'button-without-frame',
					iconCls: 'panicon-open-view',
					tooltip: langs('Открыть'),
					handler: function () {
						var record = me.EvnMediaDataGrid.getSelectionModel().getSelectedRecord();
						if (record && !Ext6.isEmpty(record.get('EvnMediaData_FilePath'))) {
							me.openFile(record.get('EvnMediaData_FilePath'));
						}
					}
				}, {
					text: 'Удалить',
					userCls: 'button-without-frame',
					iconCls: 'panicon-del-prescr-item',
					tooltip: langs('Удалить'),
					handler: function () {
						me.deleteEvnMediaData();
					}
				}, {
					text: 'Печать',
					userCls: 'button-without-frame',
					iconCls: 'panicon-print',
					tooltip: langs('Печать'),
					handler: function () {
						inDevelopmentAlert();
					}
				}
			]
		});

		this.EvnMediaDataGrid = Ext6.create('swGridWithBtnAddRecords', {
			border: false,
			region: 'center',
			tbar: me.toolPanel,
			cls: 'EMKBottomPanelGrid',
			withBtnShowMore: false,
			params: {//yl:здесь не работает - ставится в setParams
				Person_id: me.Person_id
			},
			viewConfig: {//жирным в текущем случае
				getRowClass: function (record, rowIndex, rowParams, store) {
					var cls = "";
					if (record.get("Evn_id") == me.Evn_id) {
						cls = cls + "x-grid-rowbold ";
					}
					return cls;
				}
			},
			listeners: {
				itemdblclick: function (cmp, record) {
					var record = me.EvnMediaDataGrid.getSelectionModel().getSelectedRecord();
					if (record && !Ext6.isEmpty(record.get("EvnMediaData_FilePath"))) {
						me.openFile(record.get("EvnMediaData_FilePath"));
					}
				}
			},
			columns: [{
				flex: 2,
				height: 32,
				text: 'Файл',
				minWidth: 100,
				dataIndex: 'EvnMediaData_FileName'
			}, {
				width: 120,
				height: 32,
				text: 'Расширение',
				minWidth: 100,
				dataIndex: 'EvnMediaData_Extension'
			}, {
				width: 120,
				text: 'Дата',
				height: 32,
				minWidth: 100,
				formatter: 'date("d.m.Y")',
				dataIndex: 'EvnMediaData_insDT'
			}, {
				flex: 1,
				text: 'Комментарий',
				minWidth: 100,
				height: 32,
				dataIndex: 'EvnMediaData_Comment'
			}],
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnMediaData_id', type: 'int' },
					{ name: 'EvnMediaData_FileName', type: 'string' },
					{ name: 'EvnMediaData_FilePath', type: 'string' },
					{ name: 'EvnMediaData_Extension', type: 'string' },
					{ name: 'EvnMediaData_insDT', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'EvnMediaData_Comment', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnMediaFiles&m=loadEvnMediaDataPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnMediaData_insDT',
					direction: 'DESC'
				}
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnMediaDataGrid
			]
		});

		this.callParent(arguments);
	}
});