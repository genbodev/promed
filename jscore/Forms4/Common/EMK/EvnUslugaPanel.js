/**
 * Панель услуг
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
Ext6.define('common.EMK.EvnUslugaPanel', {
	extend: 'swPanel',
	title: 'УСЛУГИ',
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		if(this.plusMenu)
			this.plusMenu.showBy(this);
		if (this.plusMenu.hidden == false)
			this.btnAddClick.setStyle('visibility','visible');
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
		me.loaded = true;
		this.EvnUslugaGrid.getStore().load({
			params: {
				EvnUsluga_pid: me.Evn_id
			}
		});
	},
	deleteEvnUsluga: function() {
		var me = this;

		var EvnUsluga_id = me.EvnUslugaGrid.recordMenu.EvnUsluga_id;
		var record = this.EvnUslugaGrid.getStore().findRecord('EvnUsluga_id', EvnUsluga_id);
		if (!record) {
			return false;
		}

		if (EvnUsluga_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление услуги...');
					Ext6.Ajax.request({
						url: '/?c=EvnUsluga&m=deleteEvnUsluga',
						params: {
							id: EvnUsluga_id,
							class: record.get('EvnClass_SysNick')
						},
						callback: function () {
							me.unmask();
							me.EvnUslugaGrid.getStore().reload();
							me.callback({
								EvnUsluga_id: null
							});
							if (me.ownerPanel && me.ownerPanel.checkMesOldUslugaComplexFields) {
								me.ownerPanel.checkMesOldUslugaComplexFields();
							}
						}
					})
				}
			}, 'услугу');
		}
	},
	printEvnUsluga: function() {
		var me = this;
		var EvnUsluga_id = me.EvnUslugaGrid.recordMenu.EvnUsluga_id;
		if (!EvnUsluga_id) {
			return false;
		}

		var params = {};

		params.object =	'EvnUslugaCommon';
		params.object_id = 'EvnUslugaCommon_id';
		params.object_value	=  EvnUsluga_id;
		params.view_section = 'main';

		me.mask('Получение данных для печати...');
		Ext6.Ajax.request({
			failure: function(response, options) {
				Ext6.Msg.alert(langs('Ошибка'), langs('При печати услуги произошла ошибка.'));
			},
			params: params,
			success: function(response, options) {
				me.unmask();

				if (response.responseText) {
					var result = Ext6.JSON.decode(response.responseText);
					if (result.html) {
						var id_salt = Math.random(),
							win_id = 'printEvent' + Math.floor(id_salt * 10000),
							win = window.open('', win_id);

						win.document.write('<html><head><title>Печатная форма</title><link href="/css/emk.css?' + id_salt + '" rel="stylesheet" type="text/css" /></head><body id="rightEmkPanelPrint">' + result.html + '</body></html>');

					} else {
						Ext6.Msg.alert(langs('Ошибка'), langs('Не удалось получить содержание услуги.'));
						return false;
					}
				} else {
					Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при печати услуги.'));
					return false;
				}
			},
			url: '/?c=Template&m=getEvnForm'
		});
	},
	openEvnUslugaEditWindow: function(action, EvnClass_SysNick) {
		var me = this;

		var formParams = new Object();

		if ( action == 'add' ) {
			formParams.EvnUsluga_id = 0;
			formParams.EvnUsluga_pid = me.Evn_id;
			formParams.PersonEvn_id = me.PersonEvn_id;
			formParams.Person_id = me.Person_id;
			formParams.Server_id = me.Server_id;
		}
		else {
			formParams.EvnUsluga_id = me.EvnUslugaGrid.recordMenu.EvnUsluga_id;
			if (!formParams.EvnUsluga_id) {
				return false;
			}

			var record = this.EvnUslugaGrid.getStore().findRecord('EvnUsluga_id', formParams.EvnUsluga_id);
			if (!record) {
				return false;
			}

			formParams.PersonEvn_id = record.get('PersonEvn_id');
			formParams.Person_id = record.get('Person_id');
			formParams.Server_id = record.get('Server_id');
		}

		var params = new Object();

		params.action = action;
		params.Person_id = me.Person_id;
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				return false;
			}

			this.EvnUslugaGrid.getStore().load({params:{EvnUsluga_pid: me.Evn_id}});
			me.callback({
				EvnUsluga_id: data.evnUslugaData.EvnUsluga_id
			});
			if (me.ownerPanel && me.ownerPanel.checkMesOldUslugaComplexFields) {
				me.ownerPanel.checkMesOldUslugaComplexFields();
			}
		}.createDelegate(this);
		params.onHide = Ext6.emptyFn;
		params.parentClass = 'EvnVizit';

		var evnData = me.ownerPanel.getEvnData();

		// данные для ParentEvnCombo
		params.parentEvnComboData = [{
			Evn_id: me.Evn_id,
			Evn_Name: evnData.Evn_setDate + ' / ' + evnData.LpuSection_Name + ' / ' + evnData.MedPersonal_Fin,
			Evn_setDate: Date.parseDate(evnData.Evn_setDate, 'd.m.Y'),
			Evn_setTime: evnData.Evn_setTime,
			MedStaffFact_id: evnData.MedStaffFact_id,
			LpuSection_id: evnData.LpuSection_id,
			LpuSectionProfile_id: evnData.LpuSectionProfile_id,
			MedPersonal_id: evnData.MedPersonal_id,
			ServiceType_SysNick: evnData.ServiceType_SysNick,
			VizitType_SysNick: evnData.VizitType_SysNick,
			Diag_id: evnData.Diag_id,
			UslugaComplex_Code: evnData.UslugaComplex_Code
		}];

		switch ( action ) {
			case 'add':
				params.formParams = formParams;
				switch ( EvnClass_SysNick ) {
					case 'EvnUslugaCommon':
						getWnd('EvnUslugaCommonEditWindow').show(params);
						break;

					case 'EvnUslugaOper':
						getWnd('EvnUslugaOperEditWindow').show(params);
						break;

					case 'EvnUslugaStom':
						getWnd('swEvnUslugaStomEditWindow').show(params);
						break;

					default:
						return false;
						break;
				}
				break;

			case 'edit':
			case 'view':
				EvnClass_SysNick = record.get('EvnClass_SysNick');
				// Открываем форму редактирования услуги (в зависимости от EvnClass_SysNick)
				switch ( EvnClass_SysNick ) {
					case 'EvnUslugaCommon':
						params.formParams = {
							EvnUslugaCommon_id: formParams.EvnUsluga_id
						};
						getWnd('EvnUslugaCommonEditWindow').show(params);
						break;

					case 'EvnUslugaOper':
						params.formParams = {
							EvnUslugaOper_id: formParams.EvnUsluga_id,
							MedStaffFact_id: evnData.MedStaffFact_id

						};
						getWnd('EvnUslugaOperEditWindow').show(params);
						break;

					case 'EvnUslugaStom':
						params.formParams = {
							EvnUslugaStom_id: formParams.EvnUsluga_id,
							MedStaffFact_id: evnData.MedStaffFact_id

						};
						getWnd('swEvnUslugaStomEditWindow').show(params);
						break;

					case 'EvnUslugaPar':
						if (action == 'view') {
							getWnd('uslugaResultWindow').show({
								Evn_id: formParams.EvnUsluga_id,
								object: 'EvnUslugaPar',
								object_id: 'EvnUslugaPar_id',
								userMedStaffFact: me.userMedStaffFact
							});
						} else {
							params.formParams = {
								EvnUslugaPar_id: formParams.EvnUsluga_id
							};
							getWnd('swEvnUslugaParSimpleEditWindow').show(params);
						}
						break;

					default:
						return false;
						break;
				}
				break;
		}
		return true;
	},
	initComponent: function() {
		var me = this;

		this.plusMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Добавить оперативную услугу',
				handler: function() {
					me.openEvnUslugaEditWindow('add', 'EvnUslugaOper');
				}
			}, {
				text: 'Добавить общую услугу',
				handler: function() {
					me.openEvnUslugaEditWindow('add', 'EvnUslugaCommon');
				}
			}],
			listeners:{
				hide: function () {
					me.btnAddClick.setStyle('visibility','');
				}
			}
		});

		this.EvnUslugaGrid = Ext6.create('Ext6.grid.Panel', {
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
					text: 'Смотреть результаты',
					bind: {hidden: '{EvnClass_SysNick === "EvnUslugaStom"}'},
					iconCls: 'panicon-view',
					handler: function() {
						me.openEvnUslugaEditWindow('view');
					}
				}, {
					text: 'Печать',
					bind: {hidden: '{EvnClass_SysNick === "EvnUslugaOper" || EvnClass_SysNick === "EvnUslugaStom" || EvnClass_SysNick === "EvnUslugaPar"}'}, // у оперативной услуги не было функции печати
					iconCls: 'panicon-print',
					handler: function() {
						me.printEvnUsluga();
					}
				}, {
					text: 'Удалить услугу',
					iconCls: 'panicon-delete',
					handler: function() {
						me.deleteEvnUsluga();
					}
				}]
			}),
			showRecordMenu: function(el, EvnUsluga_id) {
				var rec = this.getStore().findRecord('EvnUsluga_id', EvnUsluga_id);
				if (rec)
				{	// у оперативной услуги не было функции печати
					this.recordMenu.getViewModel().set('EvnClass_SysNick', rec.get('EvnClass_SysNick'));
				}
				this.recordMenu.EvnUsluga_id = EvnUsluga_id;
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				tdCls: 'padLeft20',
				minWidth: 100,
				dataIndex: 'EvnUsluga_Data',
				renderer: function (value, metaData, record) {
					return '<span class="uslugaInfoIcon" style="display: table-cell"></span>' + '<span style="display: table-cell;">'+record.get('UslugaComplex_Name') + '<br>' + record.get('EvnUsluga_setDate') + " кол-во " + record.get('EvnUsluga_Kolvo')+'</span>'
				}
			}, {
				width: 40,
				dataIndex: 'EvnUsluga_Action',
				renderer: function (value, metaData, record) {
					if (me.accessType == 'edit' && record && Ext6.isEmpty(record.get('EvnDiagPLStom_id'))) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnUslugaGrid.id + "\").showRecordMenu(this, " + record.get('EvnUsluga_id') + ");'></div>";
					}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{ name: 'EvnUsluga_id', type: 'int' },
					{ name: 'EvnClass_SysNick', type: 'string' },
					{ name: 'UslugaComplex_Name', type: 'string' },
					{ name: 'EvnUsluga_setDate', type: 'string' },
					{ name: 'EvnUsluga_Count', type: 'string' },
					{ name: 'EvnUsluga_Kolvo', type: 'string' },
					{ name: 'EvnDiagPLStom_id', type: 'int', allowNull: true }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnUsluga&m=loadEvnUslugaPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnUsluga_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnUslugaGrid
			]
		});

		this.callParent(arguments);
	}
});