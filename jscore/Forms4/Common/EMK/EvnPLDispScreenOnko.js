/**
 * Панель онкоскрининга
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
Ext6.define('common.EMK.EvnPLDispScreenOnko', {
	extend: 'swPanel',
	title: 'СКРИНИНГОВЫЕ ОБСЛЕДОВАНИЯ',
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		this.ownerWin.addNewEvnPLDispScreenOnko();
	},
	setParams: function(params) {
		var me = this;
		console.log('meOSS', me);
		console.log('paramsOSS', params);
		me.Evn_id = params.Evn_id;
		me.EvnClass_id = params.EvnClass_id;
		me.ownerPanel = params.ownerPanel;
		me.userMedStaffFact = params.userMedStaffFact;
		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	callback: Ext6.emptyFn,
	loaded: false,
	load: function() {
		var me = this;
		console.log('meOSSS', me);
		me.loaded = true;
		this.EvnPLDispScreenOnko.getStore().load({
			params: {
				EvnPLDispScreenOnko_pid: me.Evn_id
			}
		});
	},
	deleteEvnPLDispScreenOnko: function() {
		var me = this;

		var EvnPLDispScreenOnko_id = me.EvnPLDispScreenOnko.recordMenu.EvnPLDispScreenOnko_id;
		console.log('EvnPLDispScreenOnko_idOS',EvnPLDispScreenOnko_id);
		var record = this.EvnPLDispScreenOnko.getStore().findRecord('EvnPLDispScreenOnko_id', EvnPLDispScreenOnko_id);
		console.log('recordOS',record);

		if (!record) {
			return false;
		}

		if (EvnPLDispScreenOnko_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление услуги...');
					Ext6.Ajax.request({
						url: '/?c=EvnPLDispScreenOnko&m=deleteEvnPLDispScreenOnko',
						params: {
							EvnPLDispScreenOnko_id: EvnPLDispScreenOnko_id
						},
						callback: function () {
							me.unmask();
							me.EvnPLDispScreenOnko.getStore().reload();
							me.callback({
								EvnPLDispScreenOnko_id: null
							});
							// if (me.ownerPanel && me.ownerPanel.checkMesOldUslugaComplexFields) {
							// 	me.ownerPanel.checkMesOldUslugaComplexFields();
							// }
						}
					})
				}
			}, 'онкологический скрининг');
		}
	},
	openEvnUslugaEditWindow: function(action, EvnClass_SysNick) {
		var me = this;
		var EvnPLDispScreenOnko_id = me.EvnPLDispScreenOnko.recordMenu.EvnPLDispScreenOnko_id;
		me.ownerWin.loadEmkViewPanel('EvnPLDispScreenOnko', EvnPLDispScreenOnko_id, ''); // вызывает окно с окноскринингом
		console.log('meUsluga', me);
		// console.log('me.EvnPLDispScreenOnko.recordMenu', me.EvnPLDispScreenOnko.recordMenu);

	},
	initComponent: function() {
		var me = this;

		this.EvnPLDispScreenOnko = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				viewModel: {
					data: {
						EvnUsluga_SysNick: ''
					}
				},
				items: [{
					text: 'Редактировать',
					iconCls: 'panicon-edit',
					handler: function() {
						me.openEvnUslugaEditWindow('edit');
					}
				}, {
					text: 'Просмотр',
					bind: {hidden: '{EvnClass_SysNick === "EvnUslugaStom"}'},
					iconCls: 'panicon-view',
					handler: function() {
						me.openEvnUslugaEditWindow('view');
					}
				}, {
					text: 'Удалить',
					iconCls: 'panicon-delete',
					handler: function() {
						me.deleteEvnPLDispScreenOnko();
					}
				}]
			}),
			showRecordMenu: function(el, EvnPLDispScreenOnko_id) {
				var rec = this.getStore().findRecord('EvnPLDispScreenOnko_id', EvnPLDispScreenOnko_id);
				console.log('recSO',rec);
				if (rec)
				{	// у оперативной услуги не было функции печати
					this.recordMenu.getViewModel().set('EvnClass_SysNick', rec.get('EvnClass_SysNick'));
				}
				console.log('this.recordMenuSO',this.recordMenu);
				this.recordMenu.EvnPLDispScreenOnko_id = EvnPLDispScreenOnko_id;
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				tdCls: 'padLeft20',
				minWidth: 100,
				dataIndex: 'EvnPLDispScreenOnko_Data',
				renderer: function (value, metaData, record) {
					console.log('recordSO1', record);
					return '<span class="panicon-screen-onko-icon" style="display: table-cell"></span>' + '<span style="display: table-cell;">Первичный онкологический скрининг<p>' + record.get('EvnPLDispScreenOnko_setDate') + '</p></span>'
				}
			}, {
				width: 40,
				dataIndex: 'EvnPLDispScreenOnko_Action',
				renderer: function (value, metaData, record) {
						console.log('recordSO2',record);
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnPLDispScreenOnko.id + "\").showRecordMenu(this, " + record.get('EvnPLDispScreenOnko_id') + ");'></div>";

				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnPLDispScreenOnko_id', type: 'int' },
				],
				listeners: {
					'load': function(store, records) {
						console.log('recordsOnko',records);
						console.log('meOnko',me);
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnPLDispScreenOnko&m=loadEvnPLDispScreenOnko',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnPLDispScreenOnko_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnPLDispScreenOnko
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function (butt) {
					//console.log('this',this);
					console.log('me.ownerWin',me.ownerWin);
					console.log('butt',butt);
					me.ownerWin.addNewEvnPLDispScreenOnko({}, butt);
				}
			}]
		});

		this.callParent(arguments);
	}
});