/**
 * Панель заболеваний
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
Ext6.define('common.EMK.EvnDiagPLStomPanel', {
	extend: 'swPanel',
	title: 'ЗАБОЛЕВАНИЯ',
	collapsed: true,
	collapseOnOnlyTitle: true,
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		this.openEvnDiagPLStomEditWindow('add');
	},
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.EvnClass_id = params.EvnClass_id;
		me.ownerPanel = params.ownerPanel;
		me.userMedStaffFact = params.userMedStaffFact;
		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.PersonEvn_id = params.PersonEvn_id;

		if (!me.collapsed) {
			me.load();
		}
		if (getRegionNick() == 'buryatiya' && params.diagCount > 0) {
			me.btnAddClick.disable();
			me.tools.plusmenu.disable();
		} else {
			me.btnAddClick.enable();
			me.tools.plusmenu.enable();
		}
	},
	listeners: {
		'expand': function() {
			this.load();
		}
	},
	load: function() {
		var me = this;
		this.EvnDiagPLStomGrid.getStore().load({
			params: {
				EvnDiagPLStom_pid: me.Evn_id
			}
		});
	},
	deleteEvnDiagPLStom: function() {
		var me = this;

		var EvnDiagPLStom_id = me.EvnDiagPLStomGrid.recordMenu.EvnDiagPLStom_id;
		var record = this.EvnDiagPLStomGrid.getStore().findRecord('EvnDiagPLStom_id', EvnDiagPLStom_id);
		if (!record) {
			return false;
		}

		if (EvnDiagPLStom_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление заболевания...');
					Ext6.Ajax.request({
						url: '/?c=Evn&m=deleteEvn',
						params: {
							Evn_id: EvnDiagPLStom_id
						},
						callback: function () {
							me.unmask();
							me.load();
						}
					})
				}
			}, 'заболевание');
		}
	},
	printControlCardZno: function() {
		var me = this;

		var EvnDiagPLStom_id = me.EvnDiagPLStomGrid.recordMenu.EvnDiagPLStom_id;

		if (EvnDiagPLStom_id) {
			printControlCardZno(EvnDiagPLStom_id);
		}
	},
	printControlCardOnko: function() {
		var me = this;

		var EvnDiagPLStom_id = me.EvnDiagPLStomGrid.recordMenu.EvnDiagPLStom_id;

		if (EvnDiagPLStom_id) {
			printControlCardOnko(EvnDiagPLStom_id);
		}
	},
	openEvnDiagPLStomEditWindow: function(action) {
		var me = this;

		//yl:176490 на сервере тоже проверяется - здесь обойти можно
		if (action == "add") {

			//запрет добавлять заболевания в случай другого врача - выбран в селекте
			var sel_medstafffac_id = me.ownerPanel.formPanel.getForm().findField("MedStaffFact_id").getValue();
			if (sel_medstafffac_id && sel_medstafffac_id != getGlobalOptions().CurMedStaffFact_id) {
				Ext6.Msg.alert(langs("Сообщение"), langs("Нельзя добавлять заболевание в случай другого врача."));
				return false;
			}

			//запрет добавлять заболевания в закрытый случай
			if (me.ownerPanel.EvnPLStomFormPanel.getForm().findField("EvnPLStom_IsFinish").getValue() == 2) {
				Ext6.Msg.alert(langs("Сообщение"), langs("Случай стоматологического лечения закрыт!"));
				return false;
			}
		}

		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.EvnDiagPLStom_id = 0;
			formParams.EvnDiagPLStom_pid = me.Evn_id;
			formParams.PersonEvn_id = me.PersonEvn_id;
			formParams.Person_id = me.Person_id;
			formParams.Server_id = me.Server_id;
		}
		else {
			formParams.EvnDiagPLStom_id = me.EvnDiagPLStomGrid.recordMenu.EvnDiagPLStom_id;
			if (!formParams.EvnDiagPLStom_id) {
				return false;
			}

			var record = this.EvnDiagPLStomGrid.getStore().findRecord('EvnDiagPLStom_id', formParams.EvnDiagPLStom_id);
			if (!record) {
				return false;
			}

			formParams.PersonEvn_id = record.get('PersonEvn_id');
			formParams.Person_id = record.get('Person_id');
			formParams.Server_id = record.get('Server_id');
		}

		var params = new Object();

		var piPanel = me.ownerWin.PersonInfoPanel;
		if (piPanel && piPanel.getFieldValue('Person_Surname')) {
			params.Person_Birthday = piPanel.getFieldValue('Person_Birthday');
			params.Person_Surname = piPanel.getFieldValue('Person_Surname');
			params.Person_Firname = piPanel.getFieldValue('Person_Firname');
			params.Person_Secname = piPanel.getFieldValue('Person_Secname');
		} else {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить данные о человеке'));
			return false;
		}

		params.action = action;
		params.Person_id = me.Person_id;
		params.callback = function(data) {
			if ( !data || !data.evnDiagPLStomData ) {
				return false;
			}

			me.load();

			if (typeof me.onSaveEvnDiagPLStom == 'function') {
				me.onSaveEvnDiagPLStom();
			}
		};
		params.onHide = Ext6.emptyFn;
		params.parentClass = 'EvnVizit';

		var evnData = me.ownerPanel.getEvnData();

		// данные для ParentEvnCombo
		params.evnVizitData = {
			EvnVizitPLStom_id: me.Evn_id,
			EvnVizitPLStom_setDate: evnData.Evn_setDate,
			LpuSection_id: evnData.LpuSection_id,
			LpuSectionProfile_id: evnData.LpuSectionProfile_id,
			MedStaffFact_id: evnData.MedStaffFact_id,
			MedPersonal_id: evnData.MedPersonal_id,
			PayType_id: evnData.PayType_id,
			MesEkb_id: evnData.Mes_id
		};

		params.formMode = 'morbus';
		formParams.EvnDiagPLStom_rid = evnData.EvnPLStom_id;
		formParams.EvnDiagPLStom_setDate = Date.parseDate(evnData.Evn_setDate, 'd.m.Y');

		params.formParams = formParams;
		getWnd('swEvnDiagPLStomEditWindow').show(params);

		return true;
	},
	initComponent: function() {
		var me = this;

		this.EvnDiagPLStomGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				viewModel: {
					data: {
						EvnDiagPLStom_SysNick: ''
					}
				},
				items: [{
					text: 'Редактировать',
					itemId: 'action_edit',
					iconCls: 'panicon-edit',
					handler: function() {
						me.openEvnDiagPLStomEditWindow('edit');
					}
				}, {
					text: 'Печать КЛУ при ЗНО',
					itemId: 'action_print',
					iconCls: 'panicon-print',
					handler: function() {
						me.printControlCardZno();
					}
				}, {
					text: 'Печать выписки при онкологии',
					itemId: 'action_print',
					iconCls: 'panicon-print',
					hidden: getRegionNick() != 'ekb',
					handler: function() {
						me.printControlCardOnko();
					}
				}, {
					text: 'Удалить заболевание',
					itemId: 'action_delete',
					iconCls: 'panicon-delete',
					handler: function() {
						me.deleteEvnDiagPLStom();
					}
				}]
			}),
			showRecordMenu: function(el, EvnDiagPLStom_id) {
				var rec = this.getStore().findRecord('EvnDiagPLStom_id', EvnDiagPLStom_id);
				if (me.accessType == 'edit' && rec && rec.get('accessType') == 'edit') {
					this.recordMenu.down('#action_edit').enable();
					this.recordMenu.down('#action_delete').enable();
				} else {
					this.recordMenu.down('#action_edit').disable();
					this.recordMenu.down('#action_delete').disable();
				}

				this.recordMenu.EvnDiagPLStom_id = EvnDiagPLStom_id;
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				tdCls: 'padLeft20',
				minWidth: 100,
				dataIndex: 'EvnDiagPLStom_Data',
				renderer: function (value, metaData, record) {
					return '<p style="display: table-cell;">Дата начала: ' + record.get('EvnDiagPLStom_setDate') + "; Заболевание закрыто " + ((record.get('EvnDiagPLStom_IsClosed') == 2) ? 'Да' : 'Нет') + '; Диагноз: <b>' + record.get('Diag_Code') + ' ' + record.get('Diag_Name') + ';</b></span> <p>Номер зуба: ' + record.get('Tooth_Code') + '; КСГ: ' + record.get('Mes_Name') + '</p>'
				}
			}, {
				width: 40,
				dataIndex: 'EvnDiagPLStom_Action',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnDiagPLStomGrid.id + "\").showRecordMenu(this, " + record.get('EvnDiagPLStom_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnDiagPLStom_id', type: 'int' },
					{ name: 'EvnDiagPLStom_setDate', type: 'string' },
					{ name: 'EvnDiagPLStom_IsClosed', type: 'int' },
					{ name: 'Diag_Code', type: 'string' },
					{ name: 'Diag_Name', type: 'string' },
					{ name: 'PersonEvn_id', type: 'int' },
					{ name: 'Person_id', type: 'int' },
					{ name: 'Server_id', type: 'int' },
					{ name: 'accessType', type: 'string' },
					{ name: 'Tooth_Code', type: 'int' },
					{ name: 'Mes_Name', type: 'string' },
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);

						if( getRegionNick() == 'buryatiya' && records.length > 0 ) {
							me.btnAddClick.disable();
							me.tools.plusmenu.disable();
							
						} else {
							me.btnAddClick.enable();
							me.tools.plusmenu.enable();
							
						}
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnDiagPLStom&m=loadEvnDiagPLStomPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnDiagPLStom_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnDiagPLStomGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function () {
					me.openEvnDiagPLStomEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});