/**
 * Панель диспансерного учёта
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
Ext6.define('common.EMK.SignalInfo.PersonDispPanel', {
	requires: [
		'sw.frames.EMD.swEMDPanel'
	],
	extend: 'swPanel',
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	onBtnAddClick: function(){
		var common = this.findParentBy(function(w) {if(w.refId=='common') return w;});
		if(common && common.PersonInfoPanel && (typeof common.PersonInfoPanel.action_New_PersonDisp=='function')) common.PersonInfoPanel.action_New_PersonDisp();
	},
	title: 'ДИСПАНСЕРНЫЙ УЧЁТ',
	allTimeExpandable: false,
	collapsed: true,
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}
	},
	loaded: false,
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	load: function() {
		var me = this;
		this.loaded = true;
		this.PersonDispGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
	},
	openPersonDispEditWindow: function(action) {
		var me = this;
		var PersonDisp_id = me.PersonDispGrid.recordMenu.data_id;
		if(!PersonDisp_id) return;
		if (getWnd('swPersonDispEditWindowExt6').isVisible())
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Окно редактирования диспансерной карты пациента уже открыто'));
			return false;
		}
		var params = {
			action: 'edit',
			formParams: {
				Person_id: me.Person_id,
				Server_id: me.Server_id,
				PersonDisp_id: PersonDisp_id
			}
		}
		
		params.callback = function() {
			me.load();
		}.createDelegate(this);
		
		getWnd('swPersonDispEditWindowExt6').show(params);
	},
	deletePersonDisp: function() {
		var me = this;
		var PersonDisp_id = me.PersonDispGrid.recordMenu.data_id;
		if(!PersonDisp_id) return;
		if (getWnd('swPersonDispEditWindowExt6').isVisible())
		{
			Ext6.Msg.alert(langs('Сообщение'), langs('Окно редактирования диспансерной карты пациента уже открыто'));
			return false;
		}	
		
		if (PersonDisp_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление записи...');
					Ext6.Ajax.request({
						url: '?c=PersonDisp&m=deletePersonDisp',
						params: {
							PersonDisp_id: PersonDisp_id
						},
						callback: function () {
							me.unmask();
							me.load();
						}
					})
				}
			});
		}
	},
	initComponent: function() {
		var me = this;
		
		this.PersonDispGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			columns: [{
				width: 120,
				header: 'Дата',
				dataIndex: 'PersonDisp_setDate'
			}, {
				width: 200,
				header: 'МО',
				dataIndex: 'Lpu_Nick'
			}, {
				width: 120,
				flex: 1,
				header: 'Диагноз',
				dataIndex: 'Diag_Name',
				renderer: function (val, metaData, rec) {
					var str = val?val:'';
					if(rec.get('Diag_Code')){
						str = rec.get('Diag_Code') + ' ' + str;
						return str;
					}
				}
			}, {
				width: 60,
				header: 'ЭЦП',
				dataIndex: 'PersonDisp_Sign',
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: 'PersonDisp',
						EMDRegistry_ObjectID: '{record.PersonDisp_id}',
						IsSigned: '{record.PersonDisp_IsSignedEP}',
						Hidden: '{record.SignHidden}'
					}
				}
			}, {
				width: 40,
				dataIndex: 'PersonDisp_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonDispGrid.id + "\").showRecordMenu(this, " + record.get('PersonDisp_id') + ");'></div>";
				}
			}],
			showRecordMenu: function(el, PersonDisp_id) {
				this.recordMenu.data_id = PersonDisp_id;
				this.recordMenu.showBy(el);
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					handler: function() {
						me.openPersonDispEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					handler: function() {
						me.deletePersonDisp();
					}
				}]
			}),
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'PersonDisp_id', type: 'int' },
					{
						name: 'SignHidden',
						type: 'boolean',
						convert: function(val, row) {
							if (row.get('signAccess') == 'edit') {
								return false;
							} else {
								return true;
							}
						}
					},
					{ name: 'PersonDisp_IsSignedEP', type: 'int' },
					{ name: 'PersonDisp_setDate', type: 'string' },
					{ name: 'Lpu_Nick', type: 'string' },
					{ name: 'Diag_Name', type: 'string' },
					{ name: 'Diag_Code', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadPersonDispPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'PersonDisp_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.PersonDispGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					var common = me.findParentBy(function(w) {if(w.refId=='common') return w;});
					if(common && common.PersonInfoPanel && (typeof common.PersonInfoPanel.action_New_PersonDisp=='function')) common.PersonInfoPanel.action_New_PersonDisp();
				}
			}]
		});

		this.callParent(arguments);
	}
});