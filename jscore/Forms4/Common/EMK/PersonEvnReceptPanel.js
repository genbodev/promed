/**
 * Панель рецептов пациента
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
Ext6.define('common.EMK.PersonEvnReceptPanel', {
	extend: 'swPanel',
	title: 'РЕЦЕПТЫ',
	//allTimeExpandable: false,
	layout: 'border',
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.EvnClass_id = params.EvnClass_id;
		me.userMedStaffFact = params.userMedStaffFact;
		me.Person_id = params.Person_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.ownerCt.collapsed && me.isVisible()) {
			me.load();
		}
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		var grid = this.EvnReceptGrid;
		grid.params = {
			EvnReceptGeneral_rid: me.Evn_id,
			EvnRecept_pid: me.Evn_id,
			Person_id: me.Person_id,
			limit: 50
		};
		grid.getStore().load({
			params: grid.params
		});
	},
	printRecept: function(EvnReceptGeneral_id) {
		Ext.Ajax.request({
			url: '/?c=EvnRecept&m=saveEvnReceptGeneralIsPrinted',
			params: {
				EvnReceptGeneral_id: EvnReceptGeneral_id
			},
			callback: function () {
				if (getRegionNick() == 'kz') {
					printBirt({
						'Report_FileName': 'EvnReceptMoney_print.rptdesign',
						'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'EvnReceptMoney_Oborot_print.rptdesign',
						'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
						'Report_Format': 'pdf'
					});
				} else {
					printBirt({
						'Report_FileName': 'EvnReceptGenprint.rptdesign',
						'Report_Params': '&paramEvnRecept=' + EvnReceptGeneral_id,
						'Report_Format': 'pdf'
					});
					printBirt({
						'Report_FileName': 'EvnReceptGenPrintOb.rptdesign',
						'Report_Params': '',
						'Report_Format': 'pdf'
					});
				}
			}
		});
	},
	printEvnRecept: function(record) {
		var me = this;
		var EvnRecept_id = record.get('EvnRecept_id');
		var EvnReceptGeneral_id = record.get('EvnReceptGeneral_id');
		var isGeneral = (record.get('isGeneral') && record.get('isGeneral') == 'privilege');
		if (isGeneral && EvnRecept_id) {
			var evn_recept = new sw.Promed.EvnRecept({EvnRecept_id: EvnRecept_id});
			evn_recept.print();
		} else {
			if (EvnReceptGeneral_id) {
				me.printRecept(EvnReceptGeneral_id);
			} else if (EvnRecept_id) {
				me.printRecept(EvnRecept_id);
			}
		}
	},
	addEvnRecept: function() {
		this.openEvnReceptEditWindow('add');
	},
	openEvnReceptEditWindow: function(action, record) {
		var me = this;

		var params = {
			action: action,
			EvnRecept_id: 0,
			EvnRecept_pid: me.Evn_id,
			EvnClass_id: me.EvnClass_id,
			Person_id: me.Person_id,
			Server_id: me.Server_id,
			PersonEvn_id: me.PersonEvn_id,
			callback: function(data) {
				if ( !data || !data.EvnReceptData ) {
					return false;
				}

				this.EvnReceptGrid.getStore().reload();
			}.createDelegate(this)
		};
		if (action != 'add') {
			params.EvnRecept_id = record.get('EvnRecept_id');
			if (!params.EvnRecept_id) {
				return false;
			}

			params.Person_id = record.get('Person_id');
			params.Server_id = record.get('Server_id');
			params.PersonEvn_id = record.get('PersonEvn_id');

			if (!Ext6.isEmpty(record.get('Drug_id'))) {
				wnd = 'swEvnReceptEditWindow'; // для Перми
			} else if (!Ext6.isEmpty(record.get('Drug_rlsid')) || !Ext6.isEmpty(record.get('DrugComplexMnn_id'))) {
				wnd = 'swEvnReceptRlsEditWindow'; // для Уфы
			} else {
				sw.swMsg.alert("Ошибка", "Не выбран медикамент в рецепте"); // так не может быть
				return false;
			}
			if(!Ext6.isEmpty(record.get('isGeneral')) && record.get('isGeneral') == 'general'){
				wnd = 'swEvnReceptGeneralEditWindow'; // для Уфы
				params.EvnReceptGeneral_id = record.get('EvnRecept_id');
			}
		} else {
			if (getGlobalOptions().drug_spr_using == 'dbo') {
				wnd = 'swEvnReceptEditWindow';
			} else {
				wnd = 'swEvnReceptRlsEditWindow';
			}
		}
		getWnd(wnd).show(params);
	},
	initComponent: function() {
		var me = this;

		me.allTimeExpandable = ( this.ownerWin.objectName == 'swPersonEmkWindowExt6' )?true:false;//условия, чтобы список рецептов не исчезал при получении первого пустого в ЭМК

		me.ext6 = 0;//использовать новую форму ЛВН (true) или старую (false)

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
					me.EvnReceptGrid.getStore().clearFilter();
					var filters = [
						new Ext6.util.Filter({
							filterFn: function(rec) {
								var arrFilterFields = [
										'EvnRecept_Ser',
										'EvnRecept_Num',
										'EvnRecept_setDate',
										'Drug_Name',
										'Lpu_Name',
										'EvnRecept_Kolvo',
										'isGeneral'
									],
									BreakException = {},
									filter = false;

								try {
									arrFilterFields.forEach(function(fname){
										var val = rec.get(fname);
										if(fname == 'isGeneral' && val == 'privilege')
											val = 'Льготный';
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
					me.EvnReceptGrid.getStore().filter(filters);
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
					text: 'Открыть',
					userCls: 'button-without-frame',
					iconCls: 'panicon-open-view',
					tooltip: langs('Открыть'),
					handler: function () {
						var record = me.EvnReceptGrid.getSelectionModel().getSelectedRecord();
						if (record) {
							me.openEvnReceptEditWindow('view', record);
						}
					}
				}, {
					text: 'Печать',
					userCls: 'button-without-frame',
					iconCls: 'panicon-print',
					tooltip: langs('Печать'),
					handler: function () {
						var record = me.EvnReceptGrid.getSelectionModel().getSelectedRecord();
						if (record) {
							me.printEvnRecept(record);
						}
					}
				}
			]
		});
		let dateDifferenceInDays = function (date1, date2) {return Math.abs(date1 - date2)/3600000/24;};

		this.EvnReceptGrid = Ext6.create('swGridWithBtnShowAllRecordsThisYear', {
			border: false,
			region: 'center',
			tbar: me.toolPanel,
			itemId: 'EvnReceptGrid',
			cls: 'EMKBottomPanelGrid',
			withBtnShowAll: true,
			withAutoAddRecordsEndScroll: false,
			viewConfig: {
				getRowClass: function (record, rowIndex, rowParams, store) {
					var cls = '';
					if (record.get('EvnRecept_rid') == me.Evn_id) {
						cls = cls + 'x-grid-rowbold ';
					}
					return cls;
				}
			},
			params: {
				EvnReceptGeneral_rid: me.Evn_id,
				EvnRecept_pid: me.Evn_id,
				Person_id: me.Person_id
			},
			columns: [{
				width: 120,
				height: 32,
				text: 'Серия',
				minWidth: 100,
				dataIndex: 'EvnRecept_Ser'
			}, {
				width: 120,
				height: 32,
				text: 'Номер',
				minWidth: 100,
				dataIndex: 'EvnRecept_Num'
			}, {
				width: 120,
				height: 32,
				text: 'МНН',
				minWidth: 100,
				dataIndex: 'DrugComplexMnn_RusName'
			}, {
				width: 120,
				type: 'date',
				formatter: 'date("d.m.Y")',
				text: 'Дата',
				height: 32,
				minWidth: 100,
				dataIndex: 'EvnRecept_setDate'
			}, {
				width: 120,
				text: 'Льгота',
				minWidth: 100,
				height: 32,
				dataIndex: 'isGeneral',
				renderer: function (value, metaData, record) {
					return ((record.get('isGeneral') && record.get('isGeneral') == 'privilege') ? 'Льготный' : 'Рецепт');
				}
			}, {
				width: 270,
				text: 'МО, выписавшая рецепт',
				minWidth: 100,
				height:32,
				dataIndex: 'Lpu_Name'
			}, {
				flex: 2,
				height: 32,
				text: 'Состав',
				minWidth: 100,
				dataIndex: 'Drug_Name'
			}, {
				flex: 1,
				height: 32,
				text: 'Выдан',
				minWidth: 170,
				dataIndex: 'EvnRecept_IsDelivery'
			}],
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnRecept_id', type: 'int' },
					{ name: 'EvnRecept_rid', type: 'int' },
					{ name: 'Drug_id', type: 'int', allowNull: true },
					{ name: 'Drug_rlsid', type: 'int', allowNull: true },
					{ name: 'DrugComplexMnn_id', type: 'int', allowNull: true },
					{ name: 'DrugComplexMnn_Name', type: 'int', allowNull: true },
					{ name: 'Person_id', type: 'int' },
					{ name: 'Server_id', type: 'int' },
					{ name: 'PersonEvn_id', type: 'int' },
					{ name: 'EvnRecept_Ser', type: 'string' },
					{ name: 'EvnRecept_Num', type: 'string' },
					{ name: 'Drug_Name', type: 'string' },
					{ name: 'Lpu_Name', type: 'string' },
					{ name: 'EvnRecept_setDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'pmUser_Name', type: 'string' },
					{ name: 'EvnRecept_IsSigned', type: 'int' },
					{ name: 'EvnRecept_Kolvo', type: 'float' }
				],
				filters: [
					function(record) {
						return dateDifferenceInDays(record.get('EvnRecept_setDate'), new Date()) <= 365;
					}
				],
				listeners: {
					'load': function(store, records) {
						var cnt = 0;
						store.each(function(record) {
							if (record.get('EvnRecept_rid') == me.Evn_id) {
								cnt++;
							}
						});
						me.setTitleCounter(cnt);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnRecept&m=loadPersonEvnReceptPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: {
					property: 'EvnRecept_setDate',
					direction: 'DESC'
				}
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnReceptGrid
			]
		});

		this.callParent(arguments);
	}
});