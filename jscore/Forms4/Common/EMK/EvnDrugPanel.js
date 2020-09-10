/**
 * Панель использования медикаментов
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
Ext6.define('common.EMK.EvnDrugPanel', {
	extend: 'swPanel',
	title: 'ИСПОЛЬЗОВАНИЕ МЕДИКАМЕНТОВ',
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	setParams: function(params) {
		var me = this;
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
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		this.openEvnDrugEditWindow('add');
	},
	listeners: {
		'expand': function() {
			if (!this.loaded) {
				this.load();
			}
		}
	},
	loaded: false,
	load: function() {
		var me = this;
		me.loaded = true;
		this.EvnDrugGrid.getStore().load({
			params: {
				EvnDrug_pid: me.Evn_id
			}
		});
	},
	deleteEvnDrug: function() {
		var me = this;
		
		var EvnDrug_id = me.EvnDrugGrid.recordMenu.EvnDrug_id;
		if (EvnDrug_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление случая использования медикаментов...');
					Ext6.Ajax.request({
						url: '/?c=EvnDrug&m=deleteEvnDrug',
						params: {
							EvnDrug_id: EvnDrug_id
						},
						callback: function () {
							me.unmask();
							me.load();
						}
					})
				}
			}, 'случай использования медикаментов');
		}
	},
	openEvnDrugEditWindow: function(action) {
		var me = this;

		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.EvnDrug_id = 0;
			formParams.EvnDrug_pid = me.Evn_id;
			formParams.PersonEvn_id = me.PersonEvn_id;
			formParams.Person_id = me.Person_id;
			formParams.Server_id = me.Server_id;
		}
		else {
			formParams.EvnDrug_id = me.EvnDrugGrid.recordMenu.EvnDrug_id;
			if (!formParams.EvnDrug_id) {
				return false;
			}

			var record = this.EvnDrugGrid.getStore().findRecord('EvnDrug_id', formParams.EvnDrug_id);
			if (!record) {
				return false;
			}

			formParams.PersonEvn_id = record.get('PersonEvn_id');
			formParams.Person_id = record.get('Person_id');
			formParams.Server_id = record.get('Server_id');
		}

		var evnData = me.ownerPanel.getEvnData();

		var my_params = new Object({
			action: action,
			callback: function(data) {
				me.load();
			},
			formParams: formParams,
			parentEvnComboData: [{
				Evn_id: me.Evn_id,
				Evn_Name: evnData.Evn_setDate + ' / ' + evnData.LpuSection_Name + ' / ' + evnData.MedPersonal_Fin,
				Evn_setDate: Date.parseDate(evnData.Evn_setDate, 'd.m.Y'),
				Evn_setDate: Date.parseDate(evnData.Evn_disDate, 'd.m.Y'),
				MedStaffFact_id: evnData.MedStaffFact_id,
				Lpu_id: evnData.Lpu_id,
				LpuSection_id: evnData.LpuSection_id,
				MedPersonal_id: evnData.MedPersonal_id
			}]
		});

		var piPanel = me.ownerWin.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			my_params.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			my_params.Person_Surname = piPanel.getFieldValue('Person_Surname');
			my_params.Person_Firname = piPanel.getFieldValue('Person_Firname');
			my_params.Person_Secname = piPanel.getFieldValue('Person_Secname');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		my_params.onHide = Ext.emptyFn;
		my_params.type='PL'

		getWnd(getEvnDrugEditWindowName()).show(my_params);
	},
	initComponent: function() {
		var me = this;
		
		this.EvnDrugGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Редактировать',
					iconCls: 'panicon-edit',
					handler: function() {
						me.openEvnDrugEditWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls: 'panicon-delete',
					handler: function() {
						me.deleteEvnDrug();
					}
				}]
			}),
			showRecordMenu: function(el, EvnDrug_id, rowIndex) {
				this.recordMenu.EvnDrug_id = EvnDrug_id;
				this.recordMenu.rowIndex = rowIndex;
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				minWidth: 100,
				tdCls: 'padLeft20',
				dataIndex: 'EvnDrug_Data',
				renderer: function (value, metaData, record) {
					var text = record.get('EvnDrug_setDate') + ' / ' + record.get('Drug_Code') + " " + record.get('Drug_Name') + " / Количество: " + record.get('EvnDrug_Kolvo');

					return text;
				}
			}, {
				width: 40,
				dataIndex: 'EvnDrug_Action',
				tdCls: 'vertical-middle',
				renderer: function (value, metaData, record) {
					if (me.accessType == 'edit') {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnDrugGrid.id + "\").showRecordMenu(this, " + record.get('EvnDrug_id') + ", " + metaData.rowIndex + ");'></div>";
					}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnDrug_id', type: 'int' },
					{ name: 'EvnDrug_Kolvo', type: 'string' },
					{ name: 'Drug_Code', type: 'string' },
					{ name: 'Drug_Name', type: 'string' },
					{ name: 'EvnDrug_setDate', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnDrug&m=loadEvnDrugPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnDrug_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnDrugGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function () {
					me.openEvnDrugEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});