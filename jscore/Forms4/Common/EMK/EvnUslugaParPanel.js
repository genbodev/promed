/**
 * Панель исследований
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
Ext6.define('common.EMK.EvnUslugaParPanel', {
	extend: 'swPanel',
	title: 'ИССЛЕДОВАНИЯ',
	layout: 'border',
	setParams: function (params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.Person_id = params.Person_id;
		me.userMedStaffFact = params.userMedStaffFact;
		me.EvnUslugaParGrid.params = {
			EvnUslugaPar_pid: me.Evn_id,
			Person_id: me.Person_id
		};
		me.loaded = false;

		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	loaded: false,
	load: function () {
		var me = this;
		me.loaded = true;
		me.EvnUslugaParGrid.params = {
			EvnUslugaPar_pid: me.Evn_id,
			Person_id: me.Person_id
		};
		this.EvnUslugaParGrid.getStore().removeAll();
		this.EvnUslugaParGrid.reload();
	},
	openUslugaResultWindow: function () {
		var me = this,
			record = me.EvnUslugaParGrid.getSelectionModel().getSelectedRecord();
		if (!record) {
			return false;
		}

		if(record.get('EvnUslugaPar_id')){//yl только выполненные
			getWnd('uslugaResultWindow').show({
				Evn_id: record.get('EvnUslugaPar_id'),
				object: 'EvnUslugaPar',
				object_id: 'EvnUslugaPar_id',
				userMedStaffFact: me.userMedStaffFact
			});
		};
	},
	openDynamicsTestOfResultWindow: function () {
		var me = this,
			record = me.EvnUslugaParGrid.getSelectionModel().getSelectedRecord();
		if (!record) {
			return false;
		}

		if(record.get('EvnUslugaPar_id')){//yl только выполненные
			getWnd('uslugaDynamicsOfTestResultsWindow').show({
				Evn_id: record.get('EvnUslugaPar_id'),
				object: 'EvnUslugaPar',
				object_id: 'EvnUslugaPar_id',
				userMedStaffFact: me.userMedStaffFact
			});
		};
	},
	initComponent: function () {
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
					me.EvnUslugaParGrid.getStore().clearFilter();
					var filters = [
						new Ext6.util.Filter({
							filterFn: function(rec) {
								var arrFilterFields = [
									'UslugaComplex_Name',
									'EvnUslugaPar_setDate',
									'Lpu_Name',
									'MedService_Name'
								],
									BreakException = {},
									filter = false;

								try {
									arrFilterFields.forEach(function(fname){
										var val = rec.get(fname);
										if(val && fname == 'EvnUslugaPar_setDate') // для даты нужно искать по форматированной строке
											val = val.format('d.m.Y');
										if(val && (val.toLowerCase().indexOf(newValue.toLowerCase()) + 1)){//yl:регистро-независимый поиск
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
					me.EvnUslugaParGrid.getStore().filter(filters);
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
					// text: 'Открыть',
					userCls: 'button-without-frame',
					iconCls: 'panicon-open-view',
					tooltip: langs('Открыть'),
					handler: function () {
						me.openUslugaResultWindow();
					}
				}, {
					// text: 'Добавить в осмотр',
					userCls: 'button-without-frame',
					iconCls: 'panicon-add-to-view',
					tooltip: langs('Добавить в осмотр'),
					handler: function () {
						inDevelopmentAlert();
					}
				}, {
					// text: 'Просмотр динамики тестов',
					userCls: 'button-without-frame',
					iconCls: 'panicon-disp',
					tooltip: langs('Просмотр динамики тестов'),
					handler: function () {
						me.openDynamicsTestOfResultWindow();
					}
				}, {
					// text: 'Печать',
					userCls: 'button-without-frame',
					iconCls: 'panicon-print',
					tooltip: langs('Печать'),
					handler: function () {
						inDevelopmentAlert();
					}
				}
			]
		});

		this.EvnUslugaParGrid = Ext6.create('swGridWithBtnAddRecords', {
			border: false,
			region: 'center',
			tbar: me.toolPanel,
			cls: 'EMKBottomPanelGrid',
			withBtnShowMore: false,
			viewConfig: {
				getRowClass: function (record, rowIndex, rowParams, store) {
					var cls = '';
					if (record.get('EvnUslugaPar_rid') == me.Evn_id) {
						cls = cls + 'x-grid-rowbold ';
					}
					return cls;
				}
			},
			params: {
				EvnUslugaPar_pid: me.Evn_id,
				Person_id: me.Person_id
			},
			columns: [{
				flex: 2,
				height: 32,
				text: 'Исследование',
				minWidth: 100,
				dataIndex: 'UslugaComplex_Name'
			}, {
				flex: 1,
				text: 'Дата',
				height: 32,

				minWidth: 100,
				formatter: 'date("d.m.Y")',
				dataIndex: 'EvnUslugaPar_setDate'
			}, {
				flex: 1,
				text: 'Статус',
				height: 32,
				minWidth: 90,
				dataIndex: 'EvnUslugaPar_id',//yl: id оказанного лабораторного исследования
				renderer: function (value, metaData, record) {
					//yl:в renderStatus текст ячейки
					record.set("renderStatus", (!!value ? "Выполнено" : "Назначено"));
					return record.get("renderStatus");
				},
				sorter: function (item1, item2) {//yl:сортировка по тексту ячейки
					var l = item1.get("renderStatus"), r = item2.get("renderStatus");
					return (l > r) ? 1 : (l < r ? -1 : 0);
				}
			}, {
				flex: 4,
				text: 'Место оказания',
				minWidth: 100,
				height: 32,
				dataIndex: 'MedService_Name',
				renderer: function (value, metaData, record) {
					var lu = record.get('Lpu_Name'), msn = record.get('MedService_Name');
					//yl:в renderMedServiceName текст ячейки
					record.set("renderMedServiceName", (lu ? lu : '') + (lu && msn ? " / " : '') + (msn ? msn : ''));
					return record.get("renderMedServiceName");
				},
				sorter: function (item1, item2) {//yl:сортировка по тексту ячейки
					var l = item1.get("renderMedServiceName"), r = item2.get("renderMedServiceName");
					return (l > r) ? 1 : (l < r ? -1 : 0);
				}
			}],
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'EvnUslugaPar_id', type: 'int'},
					{name: 'EvnUslugaPar_rid', type: 'int'},
					{name: 'EvnUslugaPar_setDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'UslugaComplex_Name', type: 'string'},
					{name: 'Lpu_Name', type: 'string'},
					{name: 'MedService_Name', type: 'string'},
					{name: 'EvnXml_id', type: 'int', allowNull: true}
				],
				listeners: {
					'load': function (store, records, successful, operation, eOpts) {
						var cnt = 0;
						store.each(function (record) {
							if (record.get('EvnUslugaPar_rid') == me.Evn_id) {
								cnt++;
							}
						});
						me.setTitleCounter(cnt);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUslugaPar&m=loadEvnUslugaParPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnUslugaPar_setDate',
					direction: 'DESC'
				}
			}),
			listeners: {
				itemdblclick: function (cmp, record) {
					me.openUslugaResultWindow();
				}
			}
		});

		Ext6.apply(this, {
			items: [
				this.EvnUslugaParGrid
			]
		});

		this.callParent(arguments);
	}
});