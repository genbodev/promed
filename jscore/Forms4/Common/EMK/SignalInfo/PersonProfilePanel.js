/**
 * Панель опросов
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
Ext6.define('common.EMK.SignalInfo.PersonProfilePanel', {
	extend: 'swPanel',
	title: 'СПИСОК ОПРОСОВ',
	allTimeExpandable: false,
	btnAddClickEnable: true,
	collapseOnOnlyTitle: true,
	collapsed: true,
	setParams: function(params) {
		var me = this;

		me.Person_id = params.Person_id;
		me.Person_Birthday = params.Person_Birthday;
		me.userMedStaffFact = params.userMedStaffFact;
		me.loaded = false;
		
		Ext6.data.StoreManager.lookup('allMedicalForms').getProxy().setExtraParam('Person_id', me.Person_id);

		var addGeriatricsMenuItem = me.addMenu.down('menuitem[name=geriatrics]');
		if (addGeriatricsMenuItem) {
			addGeriatricsMenuItem.setVisible(
				getRegionNick() != 'kz' &&
				swGetPersonAge(me.Person_Birthday) >= 60
			);
		}

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
	onBtnAddClick: function(){
		this.addMenu.showBy(this, "tl-bl?");
	},
	load: function() {
		var me = this;
		this.loaded = true;
		this.PersonProfileGrid.getStore().load({
			params: {
				Person_id: me.Person_id
			}
		});
		Ext6.data.StoreManager.lookup('allMedicalForms').reload();
	},
	addPalliatNotify: function() {
		var me = this;
		getWnd('swEvnNotifyRegisterPalliatEditWindow').show({
			action: 'add',
			formParams: {
				Person_id: me.Person_id
			},
			callback: function() {
				if (me.collapsed) me.expand();
				if (me.loaded) me.load();
			}
		});
	},
	viewPalliatNotify: function(PalliatNotify_id) {
		var me = this;
		var grid = me.PersonProfileGrid;

		if (!PalliatNotify_id) {
			var PersonProfile_id = grid.recordMenu.PersonProfile_id;
			var record = grid.store.findRecord('PersonProfile_id', PersonProfile_id);

			if (!record || !record.get('PersonProfile_id') || !record.get('PalliatNotify_id')) {
				return;
			}
			
			PalliatNotify_id = record.get('PalliatNotify_id');
		}
		
		getWnd('swEvnNotifyRegisterPalliatEditWindow').show({
			action: 'view',
			formParams: {
				Person_id: me.Person_id,
				PalliatNotify_id: PalliatNotify_id
			}
		});
	},
	openPersonProfileEditWindow: function(action, reportType) {
		var me = this;
		var grid = me.PersonProfileGrid;

		if (!action || !action.inlist(['add','edit','view'])) {
			return;
		}

		var params = {
			action: action,
			Person_id: me.Person_id,
			userMedStaffFact: me.userMedStaffFact
		};

		if (me.ownerWin && me.ownerWin.PersonInfoPanel) {
			params.Sex_id = me.ownerWin.PersonInfoPanel.getFieldValue('Sex_id');
		}

		if (action == 'add') {
			Ext6.apply(params, {
				PersonProfile_id: null,
				ReportType: reportType
			});
		} else {
			var PersonProfile_id = grid.recordMenu.PersonProfile_id;

			var index = grid.store.find('PersonProfile_id', PersonProfile_id);
			var record = grid.store.getAt(index);

			if (!record || Ext6.isEmpty(record.get('PersonProfile_id'))) {
				return;
			}

			Ext6.apply(params, {
				PersonProfile_id: record.get('PersonProfile_id'),
				ReportType: record.get('ReportType')
			});
		}

		if (record && record.get('ReportType') == 'repositoryobserv') {
			// определяем тип открываемого наблюдения
			me.getLoadMask(langs('Получение данных о наблюдении')).show();
			Ext.Ajax.request({
				url: '/?c=RepositoryObserv&m=getUseCase',
				params: {
					RepositoryObserv_id: record.get('PersonProfile_id')
				},
				callback: function(options, success, response) {
					me.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.useCase) {
							params.RepositoryObserv_id = record.get('PersonProfile_id');
							params.useCase = response_obj.useCase;
							params.callback = function() {
								if (me.collapsed) me.expand();
								if (me.loaded) me.load();
							};
							getWnd('swRepositoryObservEditWindow').show(params);
						}
					}
				}
			});
		} else if (record && record.data && record.data.MedicalFormPerson_id) {
			getWnd('swMedicalFormEditWindow').show({
				MedicalFormPerson_id: record.data.MedicalFormPerson_id,
				MedicalForm_id: record.data.MedicalForm_id,
				action: 'view'
			});

		}else{
			params.callback = function() {
				if (me.collapsed) me.expand();
				if (me.loaded) me.load();
			};

			getWnd('swPersonProfileEditWindow').show(params);
		}
	},
	deletePersonProfile: function() {
		var me = this;
		var grid = me.PersonProfileGrid;

		var PersonProfile_id = grid.recordMenu.PersonProfile_id;

		var index = grid.store.find('PersonProfile_id', PersonProfile_id);
		var record = grid.store.getAt(index);

		if (!record || Ext6.isEmpty(record.get('PersonProfile_id'))) {
			return;
		}

		var reportType = record.get('ReportType');
		var contoller = sw.Promed.PersonOnkoProfile.getControllerName(reportType);

		checkDeleteRecord({
			callback: function() {
				me.mask('Удаление');
				Ext6.Ajax.request({
					url: '/?c=' + contoller + '&m=deleteOnkoProfile',
					params: {
						PersonOnkoProfile_id: record.get('PersonProfile_id')
					},
					success: function(response) {
						me.unmask();
						var responseObj = Ext6.decode(response.responseText);

						if (responseObj.success) {
							me.load();
						}
					},
					failure: function(response) {
						me.unmask();
					}
				});
			}
		});
	},
	printPersonProfile: function() {
		var me = this;
		var grid = me.PersonProfileGrid;
		
		if (me.ownerWin && me.ownerWin.PersonInfoPanel) {
			paramSex = me.ownerWin.PersonInfoPanel.getFieldValue('Sex_id');
		} else {
			paramSex = '';
		}

		var PersonProfile_id = grid.recordMenu.PersonProfile_id;

		var index = grid.store.find('PersonProfile_id', PersonProfile_id);
		var record = grid.store.getAt(index);

		if (!record || Ext6.isEmpty(record.get('PersonProfile_id'))) {
			return;
		}

		switch(record.get('ReportType')) {
			case 'onko':
			case 'previzit':
				printBirt({
					Report_FileName: 'onkoPersonProfile.rptdesign',
					Report_Params: '&paramPerson=' + me.Person_id + '&paramOnkoProfile=' + record.get('PersonProfile_id') + '&paramSex=' + paramSex,
					Report_Format: 'pdf'
				});
				break;
			case 'palliat':
				printBirt({
					Report_FileName: 'PalliatPersonProfile.rptdesign',
					Report_Params: '&paramPerson=' + me.Person_id + '&paramPalliatQuestion=' + record.get('PersonProfile_id'),
					Report_Format: 'pdf'
				});
				break;
			case 'geriatrics':
				printBirt({
					Report_FileName: 'GeriatricsAnketaprint.rptdesign',
					Report_Params: '&paramPerson=' + me.Person_id + '&paramGeriatricsQuestion=' + record.get('PersonProfile_id'),
					Report_Format: 'pdf'
				});
				break;
			case 'birads':
				printBirt({
					Report_FileName: 'Print_BIRADSQuestion.rptdesign',
					Report_Params: '&paramLpu=' + getGlobalOptions().lpu_id + '&paramBIRADSQuestion=' + record.get('PersonProfile_id'),
					Report_Format: 'pdf'
				});
				break;
		}
	},
	initComponent: function() {
		var me = this;

		me.PersonProfileGrid = Ext6.create('Ext6.grid.Panel', {
			border: true,
			cls: 'EmkGrid',
			padding: 10,
			viewConfig: {
				minHeight: 33
			},
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				items: [{
					name: 'view',
					text: 'Просмотр',
					handler: function() {
						me.openPersonProfileEditWindow('view');
					}
				}, {
					name: 'edit',
					text: 'Редактирование',
					handler: function() {
						me.openPersonProfileEditWindow('edit');
					}
				}, {
					name: 'delete',
					text: 'Удалить',
					handler: function() {
						me.deletePersonProfile();
					}
				}, {
					name: 'print',
					text: 'Печать',
					handler: function() {
						me.printPersonProfile();
					}
				}, {
					name: 'notify',
					text: 'Извещение',
					handler: function() {
						me.viewPalliatNotify();
					}
				}]
			}),
			showRecordMenu: function(el, PersonProfile_id) {
				var grid = this;
				var index = grid.store.find('PersonProfile_id', PersonProfile_id);
				var record = grid.store.getAt(index);

				if (!record || Ext.isEmpty(record.get('PersonProfile_id'))) {
					return;
				}

				var reportType = record.get('ReportType');

				if(getGlobalOptions().CurMedPersonal_id == record.get('MedPersonal_id') && getRegionNick() != 'kz'){
					grid.recordMenu.down('menuitem[name=edit]').setVisible(reportType.inlist(['palliat','geriatrics','previzit', 'onko']));
				}
				else{
					grid.recordMenu.down('menuitem[name=edit]').setVisible(reportType.inlist(['palliat','geriatrics','previzit']));
				}
				grid.recordMenu.down('menuitem[name=delete]').setVisible(reportType.inlist(['palliat','geriatrics','previzit']));
				grid.recordMenu.down('menuitem[name=print]').setVisible(!reportType.inlist(['previzit']));
				grid.recordMenu.down('menuitem[name=notify]').setVisible(reportType.inlist(['palliat']) && !!record.get('PalliatNotify_id'));

				this.recordMenu.PersonProfile_id = PersonProfile_id;
				this.recordMenu.showBy(el);
			},
			columns: [{
				width: 200,
				header: 'Дата проведения опроса',
				dataIndex: 'PersonProfile_setDate'
			}, {
				flex: 1,
				header: 'Тип опроса',
				dataIndex: 'PersonProfileType_Name',
				renderer: function (value, metaData, record) {
					if (record.get('ReportType') != 'palliat') return value;
					
					if (swGetPersonAge(me.Person_Birthday) < 18 || !record.get('PalliatQuestion_CountYes') || record.get('PalliatQuestion_CountYes') < 3) return value;
					
					if (!!record.get('PalliatNotify_id')) {
						return value + ' &nbsp; <a href="#" onclick="Ext6.getCmp(\'' + me.id + '\').viewPalliatNotify('+record.get('PalliatNotify_id')+');">Извещение создано '+Ext6.util.Format.date(record.get('EvnNotifyBase_setDate'), 'd.m.Y')+'</a>';
					} else {
						return value + ' &nbsp; <a href="#" onclick="Ext6.getCmp(\'' + me.id + '\').addPalliatNotify();">Создать извещение</a>';
					}
				}
			}, {
				flex: 1,
				header: 'Статус пациента',
				dataIndex: 'Monitored_Name'
			}, {
				width: 40,
				dataIndex: 'Actions',
				renderer: function (value, metaData, record) {
					return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + me.PersonProfileGrid.id + "\").showRecordMenu(this, " + record.get('PersonProfile_id') + ");'></div>";
				}
			}],
			disableSelection: true,
			store: Ext6.create('Ext6.data.Store', {
				storeId:'loadPersonOnkoProfileList',
				fields: [
					{ name: 'PersonProfile_id', mapping: 'PersonOnkoProfile_id', type: 'int' },
					{ name: 'PersonProfile_setDate', mapping: 'PersonOnkoProfile_setDate', type: 'string' },
					{ name: 'PersonProfileType_Name', type: 'string' },
					{ name: 'ReportType', type: 'string' },
					{ name: 'Monitored_Name', type: 'string' },
					{ name: 'PalliatQuestion_CountYes', type: 'int' },
					{ name: 'PalliatNotify_id', type: 'int' },
					{ name: 'EvnNotifyBase_setDate', type: 'date', dateFormat: 'd.m.Y' },
					{ name: 'displayEditBtn', type: 'string' },
					{ name: 'displayDelBtn', type: 'string' }
				],
				listeners: {
					'load': function(store, records) {
						me.setTitleCounter(records.length);
					}
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=OnkoCtrl&m=loadPersonOnkoProfileList',
					reader: {
						type: 'json'
					}
				},
				sorters: [
					'PersonProfile_id'
				]
			})
		});

		Ext6.create('Ext6.data.Store', {
			storeId: 'allMedicalForms',
			autoLoad: false,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=MedicalForm&m=getMedicalForms',
				reader: {
					type: 'json'
				}
			},
			listeners:{
				load: function (store,record,successful) {
					if(successful) {
						var del = [];//очистка меню, но только от динамически подружаемых пунктов
						me.addMenu.items.getRange().forEach(function(item) {
							if(!Ext6.isEmpty(item.MedicalForm_id)) del.push(item);
						});
						del.forEach(function(item) {
							me.addMenu.remove(item);
						});
						
						store.getRange().forEach(function(item) {
							me.addMenu.add({
								text: item.get('MedicalForm_Name'),
								MedicalForm_id: item.get('MedicalForm_id')
							}); 
						});
					}
				}
			}
		});



		me.addMenu = Ext6.create('Ext6.menu.Menu', {
			listeners: {
				click: function( menu, item) {
					if(!item.name){
						getWnd('swMedicalFormEditWindow').show({
							MedicalForm_id: item.MedicalForm_id,
							Person_id: me.Person_id,
							action: 'add'
						});
					}
				}
			},
			items: [{
				name: 'onko',
				text: 'Онкоконтроль',
				handler: function() {
					me.openPersonProfileEditWindow('add', this.name);
				}
			}, {
				name: 'palliat',
				text: 'Паллиативная помощь',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					me.openPersonProfileEditWindow('add', this.name);
				}
			}, {
				name: 'geriatrics',
				text: 'Возраст не помеха',
				hidden: getRegionNick() == 'kz',
				handler: function() {
					me.openPersonProfileEditWindow('add', this.name);
				}
			}, {
				name: 'previzit',
				text: 'Предварительное анкетирование',
				hidden: getRegionNick() != 'kz',
				handler: function() {
					me.openPersonProfileEditWindow('add', this.name);
				}
			}]
		});

		Ext6.apply(this, {
			items: [
				this.PersonProfileGrid
			],
			tools: [{
				type: 'plusmenu',
				tooltip: 'Добавить',
				minWidth: 23,
				handler: function() {
					me.addMenu.showBy(this, 'tr-br?');
				}
			}]
		});

		this.callParent(arguments);
	}
});