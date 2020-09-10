/**
 * Панель рецептов
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
Ext6.define('common.EMK.EvnReceptPanel', {
	requires: [
		'sw.frames.EMD.swEMDPanel'
	],
	extend: 'swPanel',
	title: 'РЕЦЕПТЫ',
	allTimeExpandable: false,
	collapsed: true,
	collapseOnOnlyTitle: true,
	// Добавляем кнопку "добавить" в header и исполняемую функцию
	btnAddClickEnable: true,
	onBtnAddClick: function(){
		if(this.isKardio){
			if(this.plusMenu)
				this.plusMenu.showBy(this);
			if (this.plusMenu.hidden == false)
				this.btnAddClick.setStyle('visibility','visible');
		} else {
			this.openEvnReceptEditWindow('add');
		}
	},
	setParams: function(params) {
		var me = this;

		me.Evn_id = params.Evn_id;
		me.EvnClass_id = params.EvnClass_id;
		me.Evn_setDate = params.Evn_setDate;
		me.Diag_id = params.Diag_id;
		me.LpuSection_id = params.LpuSection_id;
		me.MedPersonal_id = params.MedPersonal_id;
		me.userMedStaffFact = params.userMedStaffFact;
		me.Person_id = params.Person_id;
		me.Server_id = params.Server_id;
		me.PersonEvn_id = params.PersonEvn_id;
		me.loaded = false;

		if (!me.collapsed) {
			me.load();
		}

		me.setAddMenuVisibility(params.isKardio);
	},
	setTitleCounter: function(count) {
		var me = this;
		me.callParent(arguments);
		me.up('window').query('evnxmleditor').forEach(function(editor) {
			editor.refreshSpecMarkerBlocksContent();
		});
	},
	setAddMenuVisibility: function(visible){
		var me = this;
		if(visible === undefined)
			visible = ('perm' == getRegionNick() && me.userMedStaffFact && me.userMedStaffFact.PostMed_Code && me.userMedStaffFact.PostMed_Code.inlist(['182','179','24']));
		// 	Регион Пермь:   Если  врач, указанный в случае АПЛ занимает должность «врач-кардиолог» или «врач кардиолог»,
		me.isKardio = visible;
		if(me.tools['plusmenu'])
			me.tools['plusmenu'].setVisible(!visible);
		if(me.tools['plusmenu-extend'])
			me.tools['plusmenu-extend'].setVisible(visible);
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
		this.EvnReceptGrid.getStore().load({
			params: {
				EvnRecept_pid: me.Evn_id
			}
		});
	},
	loadBothGrids: function(){
		this.load();
		this.reloadReceptsPanels();
	},
	deleteEvnRecept: function(ReceptRemoveCauseType_id) {
		var me = this;
		if(Ext6.isEmpty(ReceptRemoveCauseType_id)){
			sw.swMsg.alert("Ошибка", "Не выбрана причина удаления рецепта"); // так не может быть
			return false;
		}
		
		var EvnRecept_id = me.EvnReceptGrid.recordMenu.EvnRecept_id,
			DeleteType = me.EvnReceptGrid.recordMenu.DeleteType;
		
		if (EvnRecept_id) {
			checkDeleteRecord({
				callback: function () {
					me.mask('Удаление рецепта...');
					Ext6.Ajax.request({
						url: '/?c=EvnRecept&m=deleteEvnRecept', // == C_EVNREC_DEL
						params: {
							ReceptRemoveCauseType_id: ReceptRemoveCauseType_id,
							EvnRecept_id: EvnRecept_id,
							DeleteType: DeleteType
						},
						callback: function () {
							me.reloadReceptsPanels();
							me.unmask();
							me.load();
						}
					});
				}
			}, 'рецепт');
		}
	},
	printEvnRecept: function() {
		var me = this;

		var EvnRecept_id = me.EvnReceptGrid.recordMenu.EvnRecept_id;
		if (EvnRecept_id) {
			var evn_recept = new sw.Promed.EvnRecept({EvnRecept_id: EvnRecept_id});
			evn_recept.print();
			me.load();
		}
	},
	openEvnReceptEditWindow: function(action) {
		var me = this,
			wnd = '';

		var EvnRecept_id = 0;
		var Person_id = me.Person_id;
		var Server_id = me.Server_id;
		var PersonEvn_id = me.PersonEvn_id;
		// Если добавляем в этом методе - активируем обычный режим формы рецептов
		var isKardio = false; // (me.isKardio && action !== 'add');

		switch(action){
			case 'add':
				if (getGlobalOptions().drug_spr_using == 'dbo') {
					wnd = 'swEvnReceptEditWindow';
				} else {
					wnd = 'swEvnReceptRlsEditWindow';
				}
				break;
			default:
				keyId = me.EvnReceptGrid.recordMenu.keyId;
				if (!keyId) {
					return false;
				}

				var record = this.EvnReceptGrid.getStore().findRecord('keyId', keyId);
				if (!record) {
					return false;
				}
				// Открываем на просмотр или редактирование кардио рецепт - переводим форму в режим "кардио"
				EvnRecept_id = record.get('EvnRecept_id');
				isKardio = record.get('isKardio');
				Person_id = record.get('Person_id');
				Server_id = record.get('Server_id');
				PersonEvn_id = record.get('PersonEvn_id');

				if (record.get('EMDRegistry_ObjectName') == 'EvnReceptGeneral') {
					getWnd('swEvnReceptGeneralEditWindow').show({
						action: 'view',
						EvnReceptGeneral_id: EvnRecept_id,
						EvnCourseTreatDrug_id: null,
						Person_id: Person_id,
						Server_id: Server_id,
						PersonEvn_id: PersonEvn_id,
						userMedStaffFact: wnd.userMedStaffFact,
						onHide: Ext6.emptyFn
					});
					return true;
				} else if (!Ext6.isEmpty(record.get('Drug_id'))) {
					wnd = 'swEvnReceptEditWindow'; // для Перми
				} else if (!Ext6.isEmpty(record.get('Drug_rlsid')) || !Ext6.isEmpty(record.get('DrugComplexMnn_id'))) {
					wnd = 'swEvnReceptRlsEditWindow'; // для Уфы
				} else {
					sw.swMsg.alert("Ошибка", "Не выбран медикамент в рецепте"); // так не может быть
					return false;
				}
		}

		var diag_id = me.Diag_id;
		if(me.ownerPanel)
			diag_id = me.ownerPanel.getDiagId();

		if(wnd){
			getWnd(wnd).show({
				action: action,
				isKardio: isKardio,
				EvnRecept_id: EvnRecept_id,
				EvnRecept_pid: me.Evn_id,
				EvnClass_id: me.EvnClass_id,
				Diag_id: diag_id,
				Person_id: Person_id,
				Server_id: Server_id,
				PersonEvn_id: PersonEvn_id,
                userMedStaffFact: wnd.userMedStaffFact,
				callback: function(data) {
					if ( !data || !data.EvnReceptData ) {
						return false;
					}
					me.reloadReceptsPanels();
					me.load();
				}.createDelegate(this)
			});
		}
	},
	reloadReceptsPanels: function () {
		let emkPersonWindow = (this.ownerPanel && this.ownerPanel.ownerWin) ? this.ownerPanel.ownerWin : this.findParentByType('window');
		if(emkPersonWindow) {
			emkPersonWindow.reloadTree();
			let receptPanel = emkPersonWindow.down('panel[refId="PersonEvnReceptPanel"][Evn_id!=undefined][Evn_id!=null]');
			if(receptPanel) {
				receptPanel.load();
			}
		}
	},
	openEvnReceptKardioEditWindow: function(action,EvnRecept_id) {
		var me = this;
		var params = new Object();
		if (action == 'add') {
			// Проверяем для врача возможность выписывать рецепты (отключена для ДЛО Кардио)
			setMedStaffFactGlobalStoreFilter({
				id: this.userMedStaffFact.MedStaffFact_id
			});

			var diag_id = me.Diag_id;
			if(me.ownerPanel)
				diag_id = me.ownerPanel.getDiagId();

			params.EvnRecept_id = 0;
			params.EvnRecept_pid = me.Evn_id;
			params.EvnRecept_setDate = Date.parseDate(me.Evn_setDate, 'd.m.Y');
			params.Diag_id = diag_id;
			params.LpuSection_id = me.LpuSection_id;
			params.MedPersonal_id = me.MedPersonal_id;

			if(!me.Evn_setDate && !me.LpuSection_id && !me.MedPersonal_id)
				return false;
		} else {
			params.EvnRecept_id = EvnRecept_id;
		}

		params.callback = function(data) {
			if ( !data || !data.EvnReceptData ) {
				return false;
			}
			me.load();
		};
		if ('editEvnRecept' == action) {
			action = 'edit'
		}
		params.action = action;
		params.ARMType = 'common';
		params.from = 'workplace';
		params.onHide = Ext.emptyFn;
		params.isKardio = true;
		params.MedPersonal_id = this.userMedStaffFact.MedStaffFact_id;
		params.Person_id = me.Person_id;
		params.Server_id = me.Server_id;
		params.PersonEvn_id = me.PersonEvn_id;
		params.userMedStaffFact = me.userMedStaffFact;
		//если форма открываетсяиз посещения и в режиме добавления, то нужно сделать дополнительные проверки
		if (action == 'add' && !Ext.isEmpty(me.Evn_id) && !Ext.isEmpty(me.EvnClass_id) && me.EvnClass_id == '11') {
			//проверка, есть ли у пациента льгота, и если есть то можно ли добавлять рецепты
			me.checkKardioPrivilegeConsent({
				params: {
					Person_id:  me.Person_id
				},
				callback: function (data) {
					if (data.open_edit_form) {
						getWnd('swEvnReceptRlsEditWindow').show(params);
					}
				}
			});
		} else {
			getWnd('swEvnReceptRlsEditWindow').show(params);
		}
	},
	checkKardioPrivilegeConsent: function(options) {
		var me = this;
		var Person_id = null;
		var callback = Ext.emptyFn;

		if (options && options.params && options.callback) {
			if (!Ext.isEmpty(options.params.Person_id)) {
				Person_id = options.params.Person_id;
			}
			if (typeof options.callback == 'function') {
				callback = options.callback;
			}
		}

		if (!Ext.isEmpty(Person_id)) {
			me.mask('Получение данных о включении пациента в программу');
			Ext.Ajax.request({
				url: '/?c=Privilege&m=getKardioPrivilegeConsentData',
				params: {
					Person_id: Person_id
				},
				callback: function (options, success, response) {
					me.unmask();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.recept_edit_allowed && response_obj.recept_edit_allowed == '1') {
							if (response_obj.need_consent && response_obj.need_consent == '1' && response_obj.EvnPS_id && response_obj.EvnPS_id > 0) {
								getWnd('swPrivilegeConsentEditWindow').show({
									Person_id: Person_id,
									Evn_id: response_obj.EvnPS_id,
									EvnPS_disDate: response_obj.EvnPS_disDate,
									action: 'add',
									onSave: function(data) {
										if (!Ext.isEmpty(data.PersonPrivilege_id)) {
											callback.call(me, {
												open_edit_form: true
											});
										}
									}
								});
							} else {
								callback.call(me, {
									open_edit_form: true
								});
							}
						} else {
							var err_msg = '';
							if (response_obj.success) {
								err_msg = 'Пациент не может быть включен в програму, так как не соответствует модели пациента.  Для включения в программу у пациента должна быть КВС с  выпиской после 1 января 2019 г., в которой должны быть указаны основной или сопутствующий диагнозы и услуги, установленные приказом МЗ ПК.';
							} else {
								err_msg = 'При проверке данных пациента произошла ошибка';
							}
							sw.swMsg.alert(langs('Ошибка'), langs(err_msg));
							callback.call(me, {
								open_edit_form: false
							});
						}
					} else {
						callback.call(me, {
							open_edit_form: false
						});
					}
				}
			});
		} else {
			callback.call(me, {
				open_edit_form: false
			});
		}
	},

	initComponent: function() {
		var me = this;

		this.plusMenu = Ext6.create('Ext6.menu.Menu', {
			userCls: 'menuWithoutIcons',
			items: [{
				text: 'Добавить рецепт ЛЛО',
				handler: function() {
					me.openEvnReceptEditWindow('add');
				}
			}, {
				text: 'Добавить рецепт ЛКО (кардиопрограмма)',
				handler: function() {
					me.openEvnReceptKardioEditWindow('add');
				}
			}],
			listeners:{
				hide: function () {
					me.btnAddClick.setStyle('visibility','');
				}
			}
		});

		this.RemoveCauseTypeMenu = Ext6.create('swDynamicMenu',{
			parentPanel: me
		});

		this.EvnReceptGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			features: [{
				ftype: 'grouping',
				groupHeaderTpl: '{name}'
			}],
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					text: 'Просмотр',
					iconCls: 'panicon-view',
					handler: function() {
						me.openEvnReceptEditWindow('view');
					}
				}, {
					text: 'Печать',
					itemId: 'btnPrintRecept',
					iconCls: 'panicon-print',
					handler: function() {
						me.printEvnRecept();
					}
				}, {
					text: 'Удалить рецепт',
					itemId: 'btnDeleteRecept',
					iconCls: 'panicon-delete',
					menu: me.RemoveCauseTypeMenu
				}]
			}),
			showRecordMenu: function(el, rowIndex) {
				var record = this.getStore().getAt(rowIndex);
				this.recordMenu.queryById('btnPrintRecept').setVisible(record.get('EMDRegistry_ObjectName') == 'EvnRecept');
				this.recordMenu.queryById('btnDeleteRecept').setVisible(me.accessType == 'edit' && record.get('EMDRegistry_ObjectName') == 'EvnRecept');
				
				this.recordMenu.DeleteType = 0;//Пометка к удалению
				if (isSuperAdmin() || isLpuAdmin() || isUserGroup('ChiefLLO')) {
					this.recordMenu.DeleteType = 1;
				} else {
					if (record.get('ReceptType_Code') == 2 && record.get('EvnRecept_IsSigned') != 2 && record.get('EvnRecept_IsPrinted') != 2) { //Если тип рецепта - "На листе" и рецепт не подписан
						this.recordMenu.DeleteType = 1; //Удаление
					}
				}
				
				this.recordMenu.queryById('btnDeleteRecept').setText(this.recordMenu.DeleteType==1 ? 'Удалить рецепт':'Пометить рецепт к удалению');
				this.recordMenu.EvnRecept_id = record.get('EvnRecept_id');
				this.recordMenu.keyId = record.get('keyId');
				this.recordMenu.EMDRegistry_ObjectName = record.get('EMDRegistry_ObjectName');
				this.recordMenu.showBy(el);
			},
			columns: [{
				flex: 1,
				tdCls: 'padLeft20',
				minWidth: 100,
				dataIndex: 'EvnRecept_Data',
				renderer: function (value, metaData, record) {
					return '<span class="receptInfoIcon"></span>'
						+ record.get('ReceptForm_Name')
						+ " Серия: " + record.get('EvnRecept_Ser')
						+ ", номер: " + record.get('EvnRecept_Num')
						+ (!Ext6.isEmpty(record.get('EvnRecept_setDate'))? ", дата: " + record.get('EvnRecept_setDate') : "")
						+ (!Ext6.isEmpty(record.get('EvnRecept_IsDelivery')) ? ", " + record.get('EvnRecept_IsDelivery') : "")
						+ record.get('Drugs');
				}
			}, {
				width: 60,
				dataIndex: 'EvnRecept_Sign',
				tdCls: 'vertical-middle',
				xtype: 'widgetcolumn',
				widget: {
					xtype: 'swEMDPanel',
					bind: {
						EMDRegistry_ObjectName: '{record.EMDRegistry_ObjectName}',
						EMDRegistry_ObjectID: '{record.EvnRecept_id}',
						SignCount: '{record.EvnRecept_SignCount}',
						MinSignCount: '{record.EvnRecept_MinSignCount}',
						IsSigned: '{record.EvnRecept_IsSigned}',
						Hidden: '{record.SignHidden}'
					}
				}
			}, {
				width: 40,
				dataIndex: 'EvnRecept_Action',
				tdCls: 'vertical-middle',
				renderer: function(value, metaData, record) {
					//if (me.accessType == 'edit') {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.EvnReceptGrid.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
					//}
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				groupField: 'groupTitle',
				fields: [
					{ name: 'keyId', type: 'string' },
					{ name: 'groupTitle', type: 'string' },
					{ name: 'EMDRegistry_ObjectName', type: 'string' },
					{ name: 'EvnRecept_id', type: 'int' },
					{ name: 'ReceptType_Code', type: 'int' },
					{
						name: 'SignHidden',
						type: 'boolean',
						convert: function(val, row) {
							if (
								me.accessType == 'edit'
								&& (
									row.get('EMDRegistry_ObjectName') == 'EvnRecept'
									|| (
										row.get('EMDRegistry_ObjectName') == 'EvnReceptGeneral'
										&& row.get('ReceptType_Code') == 3
									)
								)
							) {
								return false;
							} else {
								return true;
							}
						}
					},
					{ name: 'Drug_id', type: 'int', allowNull: true },
					{ name: 'Drug_rlsid', type: 'int', allowNull: true },
					{ name: 'DrugComplexMnn_id', type: 'int', allowNull: true },
					{ name: 'Person_id', type: 'int' },
					{ name: 'Server_id', type: 'int' },
					{ name: 'PersonEvn_id', type: 'int' },
					{ name: 'ReceptForm_Name', type: 'string' },
					{ name: 'EvnRecept_Ser', type: 'string' },
					{ name: 'EvnRecept_Num', type: 'string' },
					{ name: 'Drug_Name', type: 'string' },
					{ name: 'EvnRecept_setDate', type: 'string' },
					{ name: 'Drugs', type: 'string' },
					{ name: 'pmUser_Name', type: 'string' },
					{ name: 'EvnRecept_IsSigned', type: 'int' },
					{ name: 'isKardio', type: 'int' },
					{ name: 'EvnRecept_Kolvo', type: 'float' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EvnRecept&m=loadEvnReceptPanel',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'EvnRecept_id'
				]
			})
		});

		Ext6.apply(this, {
			items: [
				this.EvnReceptGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function () {
					me.openEvnReceptEditWindow('add');
				}
			}]
		});

		this.callParent(arguments);
	}
});
// вернул менюху, которую убрали в задаче #162612 может привести к ошибке в стоматке
// @todo сломал голову, как перенести инициализацию компонента в другое место
Ext6.define('swDynamicMenu', {
	extend: 'Ext6.menu.Menu',
	alias: 'widget.swdynamicmenu',
	loaded: false,
	loadMsg: 'Loading...',
	store: new Ext6.data.JsonStore(
		{
			fields: [
				{name: 'ReceptRemoveCauseType_id', type:'int'},
				{name: 'ReceptRemoveCauseType_Name',  type:'string'},
				{name: 'ReceptRemoveCauseType_Code', type:'int'}
			],
			autoLoad: false,
			sorters: {
				property: 'ReceptRemoveCauseType_Code',
				direction: 'ASC'
			},
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url : '/?c=MongoDBWork&m=getData',
				reader: {
					type: 'json'
				},
				extraParams: {object:'ReceptRemoveCauseType', ReceptRemoveCauseType:'', ReceptRemoveCauseType_Code:'', ReceptRemoveCauseType_Name:''}
			},
			baseParams: {object:'ReceptRemoveCauseType', ReceptRemoveCauseType_id:'', ReceptRemoveCauseType_Code:'', ReceptRemoveCauseType_Name:''},
			tableName: 'ReceptRemoveCauseType',
			//mode: me.queryMode
		}),
	icon: '',
	constructor: function (config) {
		var me = this;
		Ext6.apply(me, config);

		me.callParent();
	},
	initComponent: function () {
		var me = this;
		me.callParent(arguments);
		me.on('show', me.onMenuLoad, me);
		var listeners = {
			scope: me,
			load: me.onLoad,
			beforeload: me.onBeforeLoad
		};
		me.mon(me.store, listeners);
	},
	onMenuLoad: function () { var me = this; if (!me.store.loaded) me.store.load(); },
	onBeforeLoad: function (store) { this.updateMenuItems(false); },
	onLoad: function (store, records) { this.updateMenuItems(true, records); },
	updateMenuItems: function (loadedState, records) {
		var me = this;
		me.removeAll();
		if (loadedState) {
			me.hide();
			me.setLoading(false, false);
			Ext6.Array.each(records, function (record, index, array) {
				me.add({
					text: record.get('ReceptRemoveCauseType_Name'),
					ReceptRemoveCauseType_Code: record.get('ReceptRemoveCauseType_Code'),
					handler: function(menuItem) {
						me.parentPanel.deleteEvnRecept(menuItem.ReceptRemoveCauseType_Code);
					}
				});
			});
			me.store.loaded = true;
			me.show();
		}
		else {
			me.add({ width: 75, height: 40 });
			me.setLoading(me.loadMsg, false);
		}
		me.loaded = loadedState;
	}
});