/**
* swRegistryViewWindow - окно просмотра и редактирования реестров.
*
* PromedWeb - The New Generation of Medical Statistic Software
* https://rtmis.ru
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2020 RT MIS Ltd.
* @author       Stanislav Bykov
* @version      29.04.2020
* @comment      Префикс для id компонентов regv (RegistryViewWindow)
*/

sw.Promed.swRegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	firstTabIndex: 15800,
	height: 500,
	id: 'RegistryViewWindow',
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			this.findById('regvRightPanel').setVisible(false);
		}
	},
	maximized: true,
	modal: false,
	resizable: false,
	title: WND_ADMIN_REGISTRYLIST,
	width: 800,

	/* методы */
	constructYearsMenu: function(params) {
		if ( !params ) {
			return false;
		}

		let win = this;

		win.AccountGrid.getAction('action_yearfilter').setText('фильтр по году: <b>за ' + (new Date()).getFullYear() + ' год</b>');
		win.AccountGrid.ViewGridPanel.getStore().baseParams['Registry_accYear'] = (new Date()).getFullYear();

		Ext.Ajax.request({
			callback: function(o, s, r) {
				if ( s ) {
					let reg_years = Ext.util.JSON.decode(r.responseText);

					// сортируем в обратном порядке
					reg_years.sort(function(a, b) {
						if (a['reg_year'] > b['reg_year']) return -1;
						if (a['reg_year'] < b['reg_year']) return 1;
					});

					let
						grid = win.AccountGrid.ViewGridPanel,
						menuactions = new Ext.menu.Menu(),
						parentAction = grid.getTopToolbar().items.items[10]; // TODO: уточнить индекс

					reg_years.push({
						reg_year: 0
					});

					for ( let i in reg_years ) {
						if ( getPrimType(reg_years[i]) == 'object' ) {
							let act = new Ext.Action({
								text: reg_years[i]['reg_year'] > 0 ? 'за ' + reg_years[i]['reg_year'] + ' год' : 'за все время'
							});

							act.value = reg_years[i]['reg_year'];
							act.setHandler(function(parAct, grid) {
								parAct.setText('фильтр по году: <b>' + this.getText() + '</b>');
								grid.getStore().load({
									params: {
										Registry_accYear: this.value
									}
								});
								parAct.menu.items.each(function(item) {
									item.setVisible(true);
								});
								this.setHidden(true);
								parAct.menu.hide();
							}.createDelegate(act, [ parentAction, grid ]));

							menuactions.add(act);
						}
					}

					parentAction.menu = menuactions;

					if ( new RegExp((new Date()).getFullYear(), 'ig').test(parentAction.menu.items.items[0].text) ) {
						parentAction.menu.items.items[0].setVisible(false);
					}
				}
			},
			params: params,
			url: '/?c=Registry&m=getYearsList'
		});
	},
	deleteRegistryData: function(grid,deleteAll) {
		let win = this;
		let selections = grid.getGrid().getSelectionModel().getSelections();
		let reestr = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !selections && !reestr ) {
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.');
			return false;
		}

		let EvnArray = [];
		let EvnField = 'Evn_rid';
		let msg;
		let RegistryData_deleted;
		let toDelete = 0;
		let toRevive = 0;

		if ( reestr.get('RegistryType_id').inlist([ 1, 6 ]) ) {
			EvnField = 'Evn_id';
		}

		for ( let key in selections ) {
			if (
				typeof(selections[key]) == 'object'
				&& !Ext.isEmpty(selections[key].get(EvnField))
				&& !selections[key].get(EvnField).inlist([ EvnArray ])
			) {
				EvnArray.push(selections[key].get(EvnField));

				if ( selections[key].get('RegistryData_deleted') == 2 ) {
					toRevive++;
				}
				else {
					toDelete++;
				}
			}
		}

		if ( toRevive > 0 && toDelete > 0 ) {
			return false;
		}
		else if ( toRevive > 0 ) {
			RegistryData_deleted = 2;
		}
		else if ( toDelete > 0 ) {
			RegistryData_deleted = 1;
		}

		if ( RegistryData_deleted != 2 ) {
			if(grid.id =='RegistryViewWindowDataBadVol'){
				msg = '<b>Вы действительно хотите удалить выбранные записи из реестра?</b><br/><br/>'+
					'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: запись будет удалена из реестра при пересчете или при выгрузке реестра в XML. </span>';
			}else {
				msg = '<b>Вы действительно хотите удалить выбранные записи <br/>из реестра?</b><br/><br/>' +
					'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Выбранные записи пометятся как удаленные<br/>' +
					'и будут удалены из реестра при выгрузке (отправке) реестра.<br/>' +
					'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
			}
		}else if (deleteAll && RegistryData_deleted != 2) {
			msg = '<b>Вы действительно хотите удалить все случаи с превышением плановых объемов из реестра?</b><br/><br/>'+
				'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данные записи будут удалены из реестра при пересчете или при выгрузке реестра в XML.</span>';
		}else {
			msg = '<b>Хотите восстановить помеченные на удаление записи?</b>';
		}

		let params = {
			Registry_id: reestr.get('Registry_id'),
			RegistryType_id: reestr.get('RegistryType_id'),
			RegistryData_deleted: RegistryData_deleted
		};

		if (deleteAll) {
			var records = new Array();

			grid.getGrid().getStore().each(function(record) {
				if(!Ext.isEmpty(record.get('Evn_id'))) {
					records.push(record.get('Evn_id'));
				}
			});

			params.Evn_ids = Ext.util.JSON.encode(records);
		}
		else if ( reestr.get('RegistryType_id').inlist([ 1, 6 ]) && !deleteAll) {
			params.Evn_ids = Ext.util.JSON.encode(EvnArray);
		}
		else {
			params.Evn_rids = Ext.util.JSON.encode(EvnArray);
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: win,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryData',
						params: params,
						callback: function(options, success, response) {
							if (success) {
								// let result = Ext.util.JSON.decode(response.responseText);
								// Перечитываем грид, чтобы обновить данные по счетам
								win.DataGrid.getGrid().getStore().reload();

								if ( grid != win.DataGrid ) {
									grid.getGrid().getStore().reload();
								}
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: msg,
			title: 'Вопрос'
		});
	},
	deleteRegistryQueue: function() {
		let
			record = this.AccountGrid.getGrid().getSelectionModel().getSelected(),
			win = this;

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		let
			Registry_id = record.get('Registry_id'),
			RegistryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: win,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryQueue',
						params: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id
						},
						callback: function(options, success, response) {
							if ( success ) {
								let result = Ext.util.JSON.decode(response.responseText);
								// Перечитываем грид, чтобы обновить данные по счетам
								win.AccountGrid.loadData();
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Удалить текущий реестр из очереди на формирование?',
			title: 'Вопрос'
		});
	},
	exportRegistryToXml: function(type) {
		let
			grid,
			record,
			win = this;

		if (type == 'simple') {
			grid = win.AccountGrid;
		}
		else if (type == 'union') {
			grid = win.UnionRegistryGrid;
		}
		else {
			sw.swMsg.alert('Ошибка', 'Неверный тип реестра.');
			return false;
		}

		record = grid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		if ( record.get('Registry_Count') == 0 && !isSuperAdmin() ) {
			sw.swMsg.alert('Ошибка', 'Экспорт реестра невозможен, нет случаев для экспорта.');
			return false;
		}

		if ( record.get('Registry_IsNeedReform') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Реестр нуждается в переформировании, экспорт невозможен.');
			return false;
		}

		getWnd('swRegistryXmlWindow').show({
			onHide: function() {
				grid.loadData();
			},
			Registry_id: record.get('Registry_id'),
			url: '/?c=Registry&m=exportRegistryToXml'
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите... '});
		}
		return this.loadMask;
	},
	getReplicationInfo: function () {
		var win = this;
		if (win.buttons[0].isVisible()) {
			win.getLoadMask().show();
			getReplicationInfo('registry', function(text) {
				win.getLoadMask().hide();
				win.buttons[0].setText(text);
			});
		}
	},
	getParamsForEvnClass: function(record) {
		let config = {};

		config.open_form = 'swEvnPLEditWindow';
		config.key = 'EvnPL_id';

		if ( !record ) {
			return config;
		}

		if ( !Ext.isEmpty(record.get('DispClass_id')) ) {
			switch ( record.get('DispClass_id') ) {
				case 6:
					config.key = 'EvnPLDispTeenInspection_id';
					config.open_form = 'swEvnPLDispTeenInspectionEditWindow';
					break;

				case 9:
					config.key = 'EvnPLDispTeenInspection_id';
					config.open_form = 'swEvnPLDispTeenInspectionPredEditWindow';
					break;

				case 10:
					config.key = 'EvnPLDispTeenInspection_id';
					config.open_form = 'swEvnPLDispTeenInspectionProfEditWindow';
					break;
			}
		}
		else {
			switch ( record.get('EvnClass_id') ) {
				case 6:
				case 13:
					config.open_form = 'swEvnPLStomEditWindow';
					config.key = 'EvnPLStom_id';
					break;
				case 32:
					config.open_form = 'swEvnPSEditWindow';
					config.key = 'EvnPS_id';
					break;
			}
		}

		return config;
	},
	importRegistry: function(importType) {
		let
			record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected(),
			win = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		getWnd('swRegistryImportXMLWindow').show({
			callback: function() {
				win.UnionRegistryGrid.loadData();
			},
			importType: importType,
			Registry_id: record.get('Registry_id')
		});
	},
	onIsRunQueue: function (RegistryQueue_Position) {
		let win = this;

		win.getLoadMask(LOAD_WAIT).show();

		if ( RegistryQueue_Position === undefined ) {
			Ext.Ajax.request({
				url: '/?c=Registry&m=loadRegistryQueue',
				params: {
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function(options, success, response) {
					if ( success ) {
						let result = Ext.util.JSON.decode(response.responseText);
						win.showRunQueue(result.RegistryQueue_Position);
					}
				}
			});
		}
		else {
			win.showRunQueue(RegistryQueue_Position);
		}
	},
	onRegistrySelect: function (Registry_id, RegistryType_id, nofocus, record) {
		let
			win = this,
			RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;

		if ( win.AccountGrid.getCount() > 0 ) {
			switch ( win.DataTab.getActiveTab().id ) {
				case 'tab_data':
					win.DataGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							RegistryStatus_id: RegistryStatus_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_commonerr':
					win.ErrorComGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_dataerr':
					win.ErrorGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							MedPersonal_id: null,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_datanopolis':
					win.NoPolisGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_databadvol':
					win.DataBadVolGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_datapersonerr':
					win.BDZErrorGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_datatfomserr':
					win.TFOMSErrorGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;
			}
		}
		else {
			switch ( win.DataTab.getActiveTab().id ) {
				case 'tab_data':
					win.DataGrid.removeAll(true);
					break;

				case 'tab_commonerr':
					win.ErrorComGrid.removeAll(true);
					break;

				case 'tab_dataerr':
					win.ErrorGrid.removeAll(true);
					break;

				case 'tab_datanopolis':
					win.NoPolisGrid.removeAll(true);
					break;

				case 'tab_databadvol':
					win.DataBadVolGrid.removeAll(true);
					break;

				case 'tab_datapersonerr':
					win.BDZErrorGrid.removeAll(true);
					break;

				case 'tab_datatfomserr':
					win.TFOMSErrorGrid.removeAll(true);
					break;
			}
		}

		return true;
	},
	onTreeClick: function(node, e) {
		let
			Lpu_id,
			win = this,
			level = node.getDepth();

		switch ( level ) {
			case 0: // root
			case 1: // МО
				win.findById('regvRightPanel').setVisible(false);
				break;

			case 2: // Объединенные реестры
				win.findById('regvRightPanel').setVisible(true);
				win.findById('regvRightPanel').getLayout().setActiveItem(1);
				Lpu_id = node.parentNode.attributes.object_value;
				win.UnionRegistryGrid.loadData({
					params: {
						Lpu_id: Lpu_id
					},
					globalFilters: {
						Lpu_id: Lpu_id,
						start: 0,
						limit: 100
					}
				});
				break;

			case 3: // Типы предварительных реестров
				win.findById('regvRightPanel').setVisible(false);
				break;

			case 4: // Статусы предварительных реестров
				win.findById('regvRightPanel').setVisible(true);
				win.findById('regvRightPanel').getLayout().setActiveItem(0);

				Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;

				let
					RegistryType_id = node.parentNode.attributes.object_value,
					RegistryStatus_id = node.attributes.object_value;

				if ( RegistryType_id == 1 || RegistryType_id == 14 ) {
					win.DataGrid.setColumnHeader('Evn_setDate', 'Дата поступления');
					win.DataGrid.setColumnHeader('Evn_disDate', 'Дата выписки');
					win.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата поступления');
					win.ErrorGrid.setColumnHeader('Evn_disDate', 'Дата выписки');
				}
				/*else if ( RegistryType_id == 2 || RegistryType_id == 16 ) {
					win.DataGrid.setColumnHeader('Evn_setDate', 'Дата первого посещения');
					win.DataGrid.setColumnHeader('Evn_disDate', 'Дата последнего посещения');
					//win.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата первого посещения');
					//win.ErrorGrid.setColumnHeader('Evn_disDate', 'Дата последнего посещения');
				}*/
				else {
					win.DataGrid.setColumnHeader('Evn_setDate', 'Дата начала случая');
					win.DataGrid.setColumnHeader('Evn_disDate', 'Дата окончания случая');
					win.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата начала случая');
					win.ErrorGrid.setColumnHeader('Evn_disDate', 'Дата окончания случая');
				}

				win.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id != 3 || win.readOnly));
				win.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id != 3 || win.readOnly));

				if ( 6 == RegistryStatus_id ) {
					win.AccountGrid.deletedRegistriesSelected = true;
				}
				else {
					win.AccountGrid.deletedRegistriesSelected = false;
				}

				win.setMenuActions(win.AccountGrid, RegistryStatus_id, RegistryType_id);

				win.AccountGrid.getAction('action_yearfilter').setHidden(RegistryStatus_id != 4 && RegistryStatus_id != 6);
				win.AccountGrid.getAction('action_new').setHidden(win.readOnly);

				if ( 4 == RegistryStatus_id || 6 == RegistryStatus_id ) {
					win.constructYearsMenu({
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id
					});
				}

				win.AccountGrid.loadData({
					params: {
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id
					},
					globalFilters: {
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id
					}
				});
				break;
		}
	},
	openForm: function (object, oparams, frm) {
		let win = this;

		// В зависимости от типа выбираем форму, которую будем открывать
		// Типы лежат в RegistryType
		let record = object.getGrid().getSelectionModel().getSelected();

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}

		let config;
		let RegistryType_id = win.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		let type = record.get('RegistryType_id');

		if ( !type ) {
			type = RegistryType_id;
		}

		if ( object.id == win.id + 'TFOMSError' || object.id == win.id + 'BDZError' || object.id == win.id + 'Data' || object.id == win.id + 'Error' || object.id == win.id + 'DataBadVol') {
			if ( frm == 'OpenPerson' ) {
				type = 108;
			}
		}

		if ( frm == 'OpenVolume' ) {
			type = 109;
		}
		
		let
			id = record.get('Evn_rid') || record.get('Evn_id'), // Вызываем родителя, а, если родитель пустой, то основное
			open_form = '',
			key = '',
			params = {
				action: (win.readOnly ? 'view' : 'edit'),
				Person_id: record.get('Person_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				Server_id: record.get('Server_id'),
				RegistryType_id: RegistryType_id
			};

		params = Ext.apply(params || {}, oparams || {});

		switch ( type ) {
			case 1:
			case 14:
				open_form = 'swEvnPSEditWindow';
				key = 'EvnPS_id';
				break;

			case 2:
			case 16:
				config = win.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;
				break;

			case 6:
				open_form = 'swCmpCallCardNewCloseCardWindow';
				key = 'CmpCloseCard_id';
				break;

			case 7:
				open_form = 'swEvnPLDispDop13EditWindow';
				key = 'EvnPLDispDop13_id';
				params.DispClass_id = record.get('DispClass_id');
				break;

			case 9:
				open_form = 'swEvnPLDispOrp13EditWindow';
				key = 'EvnPLDispOrp_id';
				params.DispClass_id = record.get('DispClass_id');
				break;

			case 11:
				open_form = 'swEvnPLDispProfEditWindow';
				key = 'EvnPLDispProf_id';
				break;

			case 12:
				config = win.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;
				break;

			case 15:
				open_form = 'swEvnUslugaParEditWindow';
				key = 'EvnUslugaPar_id';
				id = record.get('Evn_id');
				break;

			case 108:
				open_form = 'swPersonEditWindow';
				key = 'Person_id';
				id = record.get('Person_id');
				break;

			case 109:
				open_form = 'swAttributeValueEditWindow';
				params.AttributeVision_TableName = 'dbo.VolumeType';
				params.AttributeVision_TablePKey = record.get('VolumeType_id');
				params.AttributeValue_id = record.get('AttributeValue_id');
				break;
				
			default:
				sw.swMsg.alert('Ошибка', 'Вызываемая форма неизвестна!');
				return false;
				break;
		}

		if ( id ) {
			params[key] = id;
		}

		if ( open_form == 'swCmpCallCardNewCloseCardWindow' ) { // карты вызова
			params.formParams = Ext.apply(params);
		}

		getWnd(open_form).show(params);
	},
	overwriteRegistryTpl: function(record) {
		let win = this;

		let sparams = {
			Registry_Num: record.get('Registry_Num'),
			KatNasel_Name: record.get('KatNasel_Name'),
			Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'),
			Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'),
			Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'),
			ReformTime: record.get('ReformTime'),
			Registry_Count: '<div style="padding:2px;font-size: 12px;">Количество записей в реестре: ' + record.get('Registry_Count') + '</div>',
			Registry_Sum: sw.Promed.Format.rurMoney(record.get('Registry_Sum'))
		};

		win.RegistryTpl.overwrite(win.RegistryPanel.body, sparams);
	},
	refreshRegistry: function() {
		let
			win = this,
			record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		win.getLoadMask('Пересчёт реестра').show();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: win,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							win.getLoadMask().hide();

							if ( success ) {
								win.AccountGrid.loadData();
							}

							return true;
						},
						params: {
							Registry_id: record.get('Registry_id'),
							RegistryType_id: record.get('RegistryType_id')
						},
						url: '/?c=Registry&m=refreshRegistryData'
					});
				}
				else {
					win.getLoadMask().hide();
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Хотите удалить из реестра все помеченные на удаление записи <br/>и пересчитать суммы?',
			title: 'Вопрос'
		});
	},
	reformRegistry: function() {
		let
			win = this,
			record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) || Ext.isEmpty(record.get('RegistryType_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		let loadMask = new Ext.LoadMask(Ext.get(win.id), {msg: 'Подождите, идет переформирование реестра...'});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					let result = Ext.util.JSON.decode(response.responseText);

					if ( result.Error_Msg == '' || result.Error_Msg == null || result.Error_Msg == 'null' ) {
						// Выводим сообщение о постановке в очередь
						win.onIsRunQueue(result.RegistryQueue_Position);
						// Перечитываем грид, чтобы обновить данные по счетам
						win.AccountGrid.loadData();
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Во время переформирования произошла ошибка<br/>');
				}
			},
			params: {
				Registry_id: record.get('Registry_id'),
				RegistryType_id: record.get('RegistryType_id')
			},
			url: '/?c=Registry&m=reformRegistry',
			timeout: 600000
		});
	},
	registryRevive: function() {
		let record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		let win = this;

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		let Registry_id = record.get('Registry_id');

		Ext.Ajax.request({
			url: '/?c=Registry&m=reviveRegistry',
			params: {
				Registry_id: Registry_id
			},
			callback: function(options, success, response) {
				if ( success ) {
					let result = Ext.util.JSON.decode(response.responseText);
					// Перечитываем грид, чтобы обновить данные по счетам
					win.AccountGrid.loadData();
				}
			}
		});
	},
	setMenuActions: function (object, RegistryStatus_id) {
		let
			win = this,
			menu = [];

		if ( !win.menu ) {
			win.menu = new Ext.menu.Menu({
				id: 'RegistryMenu'
			});
		}

		object.addActions({
			name: 'action_yearfilter',
			menu: new Ext.menu.Menu()
		});

		object.addActions({
			disabled: win.readOnly,
			name: 'action_new',
			text: 'Действия',
			iconCls: 'actions16',
			menu: win.menu
		});

		switch ( RegistryStatus_id ) {
			case 6:
				// Удаленные
				menu = [{
					text: 'Восстановить',
					tooltip: 'Восстановить удаленный реестр',
					disabled: true,
					handler: function() {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							scope: win,
							fn: function(buttonId) {
								if ( buttonId == 'yes' ) {
									win.registryRevive();
								}
							},
							icon: Ext.Msg.QUESTION,
							msg: 'Вы действительно хотите восстановить выбранный реестр?',
							title: 'Восстановление реестра'
						});
					}
				}];
				break;

			case 5:
				// В очереди
				menu = [{
					text: 'Удалить реестр из очереди',
					tooltip: 'Удалить реестр из очереди',
					handler: function() {
						win.deleteRegistryQueue();
					}
				}];
				break;

			case 3:
				// В работе
				menu = [{
					text: 'Отметить к оплате',
					tooltip: 'Отметить к оплате',
					handler: function() {
						win.setRegistryStatus(2);
					}
				}, {
					text: 'Переформировать',
					tooltip: 'Переформировать реестр',
					handler: function() {
						win.reformRegistry();
					}
				}, {
					text: 'Пересчитать',
					tooltip: 'Пересчитать реестр',
					handler: function() {
						win.refreshRegistry();
					}
				}];
				break;

			case 2: // К оплате
				menu = [{
					text: 'Экспорт в XML',
					tooltip: 'Экспорт в XML',
					handler: function() {
						win.exportRegistryToXml('simple');
					}
				}, {
					text: 'Пересчитать',
					tooltip: 'Пересчитать реестр',
					handler: function() {
						win.refreshRegistry();
					}
				}, {
					text: 'Снять отметку «к оплате»',
					tooltip: 'Снять отметку «к оплате» (вернуть в работу)',
					handler: function() {
						win.setRegistryStatus(3);
					}
				}, {
					text: 'Отметить, как оплаченный',
					tooltip: 'Отметить, как оплаченный',
					handler: function() {
						win.setRegistryStatus(4);
					}
				}];
				break;

			case 4: // Оплаченные
				menu = [{
					text: 'Снять отметку «оплачен»',
					tooltip: 'Снять отметку «оплачен»',
					handler: function() {
						win.setRegistryStatus(2);
					}
				}, {
					text: 'Экспорт в XML',
					tooltip: 'Экспорт в XML',
					handler: function() {
						win.exportRegistryToXml('simple');
					}
				}];
				break;

			default:
				sw.swMsg.alert('Ошибка', 'Значение статуса неизвестно! (' + RegistryStatus_id + ')');
				break;
		}

		win.menu.removeAll();

		for ( key in menu ) {
			win.menu.add(menu[key]);
		}

		return true;
	},
	setRegistryStatus: function(RegistryStatus_id) {
		let
			win = this,
			record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		win.getLoadMask().show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryStatus',
			params: {
				Registry_ids: Ext.util.JSON.encode([ record.get('Registry_id') ]),
				RegistryStatus_id: RegistryStatus_id
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					let result = Ext.util.JSON.decode(response.responseText);

					if ( result.RegistryStatus_id == RegistryStatus_id ) {
						// Перечитываем грид, чтобы обновить данные по счетам
						win.AccountGrid.loadData();
					}
				}
			}
		});
	},
	setUnionRegistryMenuActions: function() {
		let win = this;

		win.UnionRegistryGrid.setActionHidden('action_print', true);

		win.UnionRegistryGrid.addActions({
			name: 'action_actions',
			text: 'Действия',
			iconCls: 'actions16',
			menu: [
				{name:'action_export', text: 'Экспорт в XML', handler: function() { win.exportRegistryToXml('union'); }},
				{name:'action_import_tfoms', text: 'Импорт ответа ТФОМС', handler: function() { win.importRegistry('tfoms'); }},
				{name:'action_import_smo', text: 'Импорт ответа СМО', handler: function() { win.importRegistry('smo'); }}
			]
		});

		win.UnionRegistryGrid.addActions({
			name: 'action_print_alt',
			text: 'Печать',
			iconCls: 'print16',
			menu: [
				{
					name: 'printRegistryScore',
					hidden: false,
					handler: function () {
						let record = win.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();

						if (!record || Ext.isEmpty(record.get('Registry_id'))) {
							sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
							return false;
						}

						printBirt({
							'Report_FileName': 'printSchet_smo_union.rptdesign',
							'Report_Params': '&paramRegistry=' + record.get('Registry_id'),
							'Report_Format': 'xls'
						});
					},
					text: 'Печать счета'
				}
			]
		});
	},
	show: function() {
		sw.Promed.swRegistryViewWindow.superclass.show.apply(this, arguments);

		if ( !isUserGroup([ 'LpuAdmin', 'RegistryUser', 'RegistryUserReadOnly', 'SuperAdmin' ]) ) {
			sw.swMsg.alert(langs('Ошибка'), langs('Функционал недоступен'));
			this.hide();
			return false;
		}

		this.readOnly = !isUserGroup([ 'LpuAdmin', 'RegistryUser', 'SuperAdmin' ]);

		this.getLoadMask().show();

		if ( this.firstRun == true ) {
			this.firstRun = false;
		}
		else {
			// При открытии если Root Node уже открыта - перечитываем
			let root = this.Tree.getRootNode();

			if ( root ) {
				if ( root.isExpanded() ) {
					this.Tree.getLoader().load(root);
				}
			}
		}

		this.maximize();

		if ( isLpuAdmin() || isSuperAdmin() || isRegistryUser() ) {
			this.getReplicationInfo();
		}

		this.setUnionRegistryMenuActions();

		this.AccountGrid.removeAll();
		this.UnionRegistryGrid.removeAll();

		this.getLoadMask().hide();
	},
	/**
	 * Функция проверяет на наличие реестров в очереди. И в случае если они там, есть выводит номер очереди и сообщение
	 * Если номер передан в функцию, то вывод сообщения происходит без обращения к серверу.
	 * (скорее всего также надо дисаблить все события на форме)
	 */
	showRunQueue: function(RegistryQueue_Position) {
		let form = this;

		this.getLoadMask().hide();

		if ( RegistryQueue_Position === undefined ) {
			// Ошибка запроса к серверу
			sw.swMsg.alert('Ошибка',
				'При отправке запроса к серверу произошла ошибка!<br/>'+
				'Попробуйте обновить страницу, нажав клавиши Ctrl+R.<br/>'+
				'Если ошибка повторится - обратитесь к разработчикам.');
			return false;
		}

		if ( RegistryQueue_Position > 0 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				closable:false,
				scope: form,
				fn: function(buttonId){
					//
				},
				icon: Ext.Msg.WARNING,
				msg: 'Ваш запрос на формирование реестра находится в очереди.<br/>'+
					'Позиция вашего запроса в очереди на формирование: <b>'+RegistryQueue_Position+'</b> место.<br/>',
				title: 'Сообщение'
			});
		}
		else {
			// Позиция нулевая, значит запрос был выполнен
			// form.AccountGrid.loadData();
		}
	},

	/* конструктор */
	initComponent: function() {
		let win = this;
		let readOnly = !isUserGroup([ 'LpuAdmin', 'RegistryUser', 'SuperAdmin' ]);

		win.TreeToolbar = new Ext.Toolbar({
			id: win.id + 'Toolbar',
			items: [{
				xtype : "tbseparator"
			}]
		});

		win.Tree = new Ext.tree.TreePanel({
			animate: false,
			autoScroll: true,
			id: win.id + 'RegistryTree',
			loader: new Ext.tree.TreeLoader({
				dataUrl: '/?c=Registry&m=loadRegistryTree',
				listeners: {
					'beforeload': function (loader, node) {
						loader.baseParams.level = node.getDepth();
					},
					'load': function (loader, node) {
						// Если это родитель, то накладываем фокус на дерево взависимости от настроек
						if ( node.id == 'root' ) {
							if ( node.getOwnerTree().rootVisible == false && node.hasChildNodes() == true ) {
								let child = node.findChild('object', 'Lpu');

								if ( child ) {
									node.getOwnerTree().fireEvent('click', child);
									child.select();
									child.expand();
								}
							}
							else {
								node.getOwnerTree().fireEvent('click', node);
								node.select();
							}
						}
					}
				}
			}),
			region: 'west',
			root: {
				id: 'root',
				nodeType: 'async',
				text: 'Реестры',
				expanded: true
			},
			rootVisible: false,
			split: true,
			tbar: win.TreeToolbar,
			width: 250
		});

		// Выбор ноды click-ом
		win.Tree.on('click', function(node, e) {
			win.onTreeClick(node, e);
		});

		win.AccountGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete', url: '/?c=Registry&m=deleteRegistry', msg: '<b style="color:maroon;">В большинстве случаев гораздо удобнее переформировать реестр, <br/>предварительно исправив ошибки, чем удалить и создать новый.</b> <br/>Вы действительно хотите удалить реестр?'},
				{name: 'action_print'}
			],
			afterSaveEditForm: function(RegistryQueue_id, records) {
				let r = records.RegistryQueue_Position;
				win.onIsRunQueue(r);
			},
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistry',
			editformclassname: 'swRegistryEditWindow',
			height: 203,
			id: win.id + 'Account',
			object: 'Registry',
			onLoadData: function() {
				if  ( this.getAction('action_new') ) {
					this.getAction('action_new').setDisabled(this.getCount() == 0);
				}
			},
			onRowSelect: function(sm, index, record) {
				if ( this.getCount() > 0 && !Ext.isEmpty(record.get('Registry_id')) ) {
					let
						Registry_id = record.get('Registry_id'),
						RegistryType_id = record.get('RegistryType_id'),
						RegistryStatus_id = record.get('RegistryStatus_id');

					win.onRegistrySelect(Registry_id, RegistryType_id, false, record);

					this.setActionDisabled('action_edit', RegistryStatus_id != 3 || readOnly);
					this.setActionDisabled('action_delete', RegistryStatus_id != 3 || readOnly);
					this.setActionDisabled('action_view', false);

					// В прогрессе
					if ( record.get('Registry_IsProgress') == 1 ) {
						this.setActionDisabled('action_edit', true);
						this.setActionDisabled('action_delete', true);
						this.setActionDisabled('action_view', true);
					}

					win.RegistryPanel.show();
					win.overwriteRegistryTpl(record);
				}
				else {
					switch ( win.DataTab.getActiveTab().id ) {
						case 'tab_registry':
							win.RegistryPanel.hide();
							break;
						case 'tab_data':
							win.DataGrid.removeAll(true);
							break;
						case 'tab_commonerr':
							win.ErrorComGrid.removeAll(true);
							break;
						case 'tab_dataerr':
							win.ErrorGrid.removeAll(true);
							break;
						case 'tab_datanopolis':
							win.NoPolisGrid.removeAll(true);
							break;
						case 'tab_databadvol':
							win.DataBadVolGrid.removeAll(true);
							break;
						case 'tab_datapersonerr':
							win.BDZErrorGrid.removeAll(true);
							break;
						case 'tab_datatfomserr':
							win.TFOMSErrorGrid.removeAll(true);
							break;
					}
				}

				// информируем о данных на вкладках
				win.DataTab.getItem('tab_registry').setIconClass(record.get('Registry_IsNeedReform') == 2 ? 'delete16' : 'info16');
				win.DataTab.getItem('tab_commonerr').setIconClass(record.get('RegistryErrorCom_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_dataerr').setIconClass(record.get('RegistryError_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datanopolis').setIconClass(record.get('RegistryNoPolis_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_databadvol').setIconClass((record.get('RegistryDataBadVol_IsData') == 1) ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datapersonerr').setIconClass(record.get('RegistryErrorBDZ_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datatfomserr').setIconClass(record.get('RegistryErrorTFOMS_IsData') == 1 ? 'usluga-notok16' : 'good');

				win.DataTab.syncSize();
			},
			region: 'north',
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden: !isSuperAdmin()},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'OrgSMO_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'ReformTime', hidden: true},
				{name: 'MedPersonalList', type: 'string', hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'PayType_SysNick', type: 'string', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorCom_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPolis_IsData', type: 'int', hidden: true},
				{name: 'RegistryDataBadVol_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorBDZ_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'Registry_Num', header: langs('Номер счета'), width: 80},
				{name: 'Registry_accDate', type: 'date', header: langs('Дата счёта'), width: 80},
				{name: 'Registry_begDate', type: 'date', header: langs('Начало периода'), width: 100},
				{name: 'Registry_endDate', type: 'date', header: langs('Окончание периода'), width: 110},
				{name: 'PayType_Name', header: langs('Вид оплаты'), width: 100},
				{name: 'KatNasel_Name', header: langs('Категория населения'), width: 200},
				{name: 'OrgSMO_Name', header: langs('СМО'), width: 200},
				{name: 'Registry_Count', type: 'int', header: langs('Количество'), width: 100},
				{name: 'Registry_Sum', type:'money', header: langs('Итоговая сумма'), width: 100},
				{name: 'Registry_SumPaid', type:'money', header: langs('Сумма к оплате'), width: 100},
				{name: 'Registry_updDate', header: langs('Дата изменения'), width: 110}
			],
			title: langs('Счет')
		});

		win.AccountGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				let cls = '';

				if ( row.get('Registry_IsActive') == 2 ) {
					cls = cls+'x-grid-rowselect ';
				}

				if ( row.get('Registry_IsProgress') == 1 ) {
					cls = cls+'x-grid-rowgray ';
				}
				else if ( row.get('Registry_IsNeedReform') == 2 ) {
					cls = cls+'x-grid-rowblue ';
				}

				if ( cls.length == 0 ) {
					cls = 'x-grid-panel'; 
				}

				return cls;
			}
		});

		let RegTplMark = [
			'<div style="padding:2px;font-size: 12px;font-weight:bold;">Реестр № {Registry_Num}</div>'+
			'<div style="padding:2px;font-size: 12px;">Вид оплаты: {PayType_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Категория населения: {KatNasel_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата переформирования реестра: {ReformTime}</div>'+
			'<div style="padding:2px;font-size: 12px;">Итоговая сумма: {Registry_ItogSum}</div>'+
			'<div style="padding:2px;font-size: 12px;">Сумма к оплате: {Registry_Sum}</div>'+
			'{Registry_Count}'
		];
		win.RegistryTpl = new Ext.XTemplate(RegTplMark);
		
		win.RegistryPanel = new Ext.Panel({
			id: 'RegistryPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: false,
			height: 28,
			maxSize: 28,
			html: ''
		});

		win.DataGridSearch = function() {
			let filtersForm = win.RegistryDataFiltersPanel.getForm();

			let registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			let Registry_id = registry.get('Registry_id');
			let RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				win.DataGrid.loadData({
					globalFilters: {
						Registry_id: Registry_id, 
						RegistryType_id: RegistryType_id,
						Person_SurName: filtersForm.findField('Person_SurName').getValue(),
						Person_FirName: filtersForm.findField('Person_FirName').getValue(),
						Person_SecName: filtersForm.findField('Person_SecName').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						start: 0,
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		};

		// Кнопка "Поиск"
		let rvnwDGBtnSearch = new Ext.Button({
			disabled: false,
			icon: 'img/icons/search16.png',
			iconCls: 'x-btn-text',
			id: 'rvnwDGBtnSearch',
			text: BTN_FRMSEARCH,
			tooltip: BTN_FRMSEARCH_TIP,
			handler: function() {
				win.DataGridSearch();
			}
		});

		rvnwDGBtnSearch.tabIndex = win.firstTabIndex + 19;

		win.DataGridReset = function() {
			win.RegistryDataFiltersPanel.getForm().reset();
			win.DataGrid.removeAll(true);
			win.DataGridSearch();
		};

		// Кнопка Сброс
		let rvnwDGBtnReset = new Ext.Button({
			disabled: false,
			icon: 'img/icons/reset16.png',
			iconCls : 'x-btn-text',
			style: 'margin-left: 4px;',
			text: BTN_FRMRESET,
			handler: function(){
				win.DataGridReset();
			}
		});

		rvnwDGBtnReset.tabIndex = win.firstTabIndex + 20;
		
		win.RegistryDataFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
			border: true,
			collapsible: false,
			height: 55,
			layout: 'form',
			region: 'north',
			id: win.id + 'RegistryDataFiltersPanel',
			keys: [{
				fn: function(e) {
					win.DataGridSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
				border: false,
				defaults: {
					bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'
				},
				layout: 'column',
				items: [{
					border: false,
					width: 210,
					labelWidth: 100,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvnwDGPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 10
					}]
				}, {
					border: false,
					width: 180,
					labelWidth: 30,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvnwDGPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 11
					}]
				}, {
					border: false,
					width: 210,
					labelWidth: 60,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvnwDGPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 12
					}]
				}]
			}, {
				bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
				border: false,
				defaults: {
					bodyStyle: 'padding-left: 4px; background: #DFE8F6;'
				},
				layout: 'column',
				items: [{
					border: false,
					width: 210,
					hidden: !isAdmin,
					labelWidth: 100,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						id: 'rvnwDGPolis_Num',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: win.firstTabIndex + 13
					}]
				}, {
					border: false,
					width: 210,
					labelWidth: 65,
					layout: 'form',
					items: [{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: win.firstTabIndex + 14,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding-left: 4px; background: #DFE8F6;',
					items: [
						rvnwDGBtnSearch
					]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding-left: 4px; background: #DFE8F6;',
					items: [
						rvnwDGBtnReset
					]
				}]
			}]
		});
		
		// Данные реестра 
		win.DataGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: readOnly, handler: function() { win.openForm(win.DataGrid, {}); }},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: false, handler: function() { win.deleteRegistryData(win.DataGrid); }},
				{name:'action_print'},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() { win.openForm(win.DataGrid, {}, 'OpenPerson'); }}
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryData',
			id: win.id + 'Data',
			object: 'RegistryData',
			onDblClick: function() {
				if ( win.Tree.selModel.selNode.attributes.object_value == 4 ) {
					return false;
				}

				win.openForm(win.DataGrid, {});
			},
			onEnter: function() {
				if ( win.Tree.selModel.selNode.attributes.object_value == 4 ) {
					return false;
				}

				win.openForm(win.DataGrid, {});
			},
			onLoadData: function() {
				let RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 2 && RegistryStatus_id != 3) || readOnly);
			},
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить отмеченные случаи' : 'Удалить случаи из реестра');

				if ( this.getCount() > 0 ) {
					this.setActionDisabled('action_openperson', readOnly);
				}
			},
			paging: true,
			passPersonEvn: true,
			selectionModel: 'multiselect',
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'ИД случая', key: true, hidden: false, hideable: true},
				{name: 'Evn_rid', hidden: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id'},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'NumCard', header: '№ талона', width: 60},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Дата начала случая', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Дата окончания случая', width: 80},
				{name: 'RegistryData_Tariff', header: 'Тариф', type: 'money', width: 80},
				{name: 'RegistryData_Sum', header: 'Сумма к оплате', type: 'money', width: 80},
				{name: 'RegistryData_deleted', hidden: true},
				{name: 'Err_Count', hidden: true},
				{name: 'ErrTfoms_Count', hidden: true}
			],
			region: 'center',
			root: 'data',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		win.DataGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				let cls = '';
				if (row.get('Err_Count') > 0 || row.get('ErrTfoms_Count') > 0)
					cls = cls+'x-grid-rowred ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			},
			listeners: {
				'rowupdated': function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		// 2. Общие ошибки
		win.ErrorComGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', disabled: true},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryErrorCom',
			id: win.id + 'ErrorCom',
			object: 'RegistryErrorCom',
			root: 'data',
			stringfields: [
				{name: 'RegistryErrorType_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', id: 'autoexpand', header: 'Наименование'},
				{name: 'RegistryErrorType_Descr', header: 'Описание', width: 250},
				{name: 'RegistryErrorClass_id', type: 'int', hidden: true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			title: 'Общие ошибки',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		win.ErrorComGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				let cls = '';
				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-rowselect ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});

		win.ErrorGridSearch = function() {
			let filtersForm = win.RegistryErrorFiltersPanel.getForm();

			let registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			let Registry_id = registry.get('Registry_id');
			let RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				win.ErrorGrid.loadData({
					globalFilters: {
						Registry_id: Registry_id, 
						RegistryType_id: RegistryType_id, 
						Person_SurName: filtersForm.findField('Person_SurName').getValue(),
						Person_FirName: filtersForm.findField('Person_FirName').getValue(),
						Person_SecName: filtersForm.findField('Person_SecName').getValue(),
						RegistryErrorType_id: filtersForm.findField('RegistryErrorType_id').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						start: 0, 
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		};

		let rvnwREBtnSearch = new Ext.Button({
			disabled: false,
			icon: 'img/icons/search16.png',
			iconCls: 'x-btn-text',
			id: 'rvnwREBtnSearch',
			text: BTN_FRMSEARCH,
			tooltip: BTN_FRMSEARCH_TIP,
			handler: function() {
				win.ErrorGridSearch();
			}
		});

		rvnwREBtnSearch.tabIndex = win.firstTabIndex + 23;

		win.RegistryErrorFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
			border: true,
			collapsible: false,
			height: 55,
			layout: 'form',
			region: 'north',
			id: win.id + 'RegistryErrorFiltersPanel',
			keys: [{
				fn: function(e) {
					win.ErrorGridSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
				border: false,
				defaults: {
					bodyStyle: 'padding-left: 4px; padding-top: 4px; background: #DFE8F6;'
				},
				layout: 'column',
				items: [{
					border: false,
					width: 210,
					labelWidth: 100,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvnwREPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 18
					}]
				}, {
					border: false,
					width: 180,
					labelWidth: 30,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvnwREPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 19
					}]
				}, {
					border: false,
					width: 210,
					labelWidth: 60,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvnwREPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 20
					}]
				}]
			}, {
				bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
				border: false,
				defaults: {
					bodyStyle: 'padding-left: 4px; background: #DFE8F6;'
				},
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 50,
					items: [{
						anchor: '100%',
						fieldLabel: 'Ошибка',
						id: 'rvnwRERegistryErrorType_id',
						name: 'RegistryErrorType_id',
						xtype: 'swregistryerrortypecombo',
						tabIndex: win.firstTabIndex + 21
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 65,
					items: [{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: win.firstTabIndex + 22,
						xtype: 'numberfield'
					}]
				}, {
					bodyStyle: 'padding-left: 4px; background: #DFE8F6;',
					border: false,
					width: 110,
					layout: 'form',
					items: [
						rvnwREBtnSearch
					]
				}]
			}]
		});

		// 3. Ошибки данных 
		win.ErrorGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryError',
			id: win.id + 'Error',
			object: 'RegistryError',
			paging: true,
			passPersonEvn: true,
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
					{ field: 'RegistryErrorType_Name', tpl: '{RegistryErrorType_Name}' }
				])
			],
			root: 'data',
			stringfields: [
				{name: 'RegistryError_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', header: 'ИД случая', hidden: false},
				{name: 'Evn_rid', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id'},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden: true},
				{name: 'RegistryData_notexist', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryErrorType_id', type: 'int', hidden: true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorClass_id', type: 'int', hidden: true},
				{name: 'RegistryErrorClass_Name', width: 80, header: 'Тип'},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryErrorType_Descr', header: 'Описание', width: 200},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 40},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70}
			],
			title: 'Ошибки данных',
			toolbar: false,
			totalProperty: 'totalCount',

			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', disabled: readOnly, text: '<b>Исправить</b>', handler: function() { win.openForm(win.ErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случаи из реестра', handler: function() { win.deleteRegistryData(win.ErrorGrid); }},
				{name:'action_refresh'},
				{name:'action_print'},
				{name:'action_openperson', disabled: readOnly, hidden: readOnly, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() { win.openForm(win.ErrorGrid, {}, 'OpenPerson');}}
			],
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить отмеченные случаи' : 'Удалить случаи из реестра');
			},
			onLoadData: function() {
				let RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 2 && RegistryStatus_id != 3) || readOnly);
			}
		});

		win.ErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				let cls = '';
				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-row ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_notexist') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});

		// 4. Незастрахованные
		win.NoPolisGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: readOnly, handler: function() { win.openForm(win.NoPolisGrid, {}, 'OpenPerson'); }},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryNoPolis',
			id: win.id + 'NoPolis',
			object: 'RegistryNoPolis',
			paging: true,
			passPersonEvn: true,
			root: 'data',
			stringfields:[
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200}
			],
			title: 'Незастрахованные',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		win.DataBadVolGridSearch = function() {
			let filtersForm = win.RegistryDataBadVolFiltersPanel.getForm();

			let registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			let Registry_id = registry.get('Registry_id');
			let RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				win.DataBadVolGrid.loadData({
					globalFilters: {
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id,
						Person_SurName: filtersForm.findField('Person_SurName').getValue(),
						Person_FirName: filtersForm.findField('Person_FirName').getValue(),
						Person_SecName: filtersForm.findField('Person_SecName').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad: false
				});
			}
		};

		win.DataBadVolGridReset = function() {
			win.RegistryDataBadVolFiltersPanel.getForm().reset();
			win.DataBadVolGrid.removeAll(true);
			win.DataBadVolGridSearch();
		};

		win.RegistryDataBadVolFiltersPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			height: 30,
			id: 'RegistryDataBadVolFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					win.DataBadVolGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					border: false,
					width: 210,
					labelWidth: 60,
					layout: 'form',
					items: [{
						anchor: '90%',
						fieldLabel: 'Фамилия',
						id: 'rvwRDBVPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 25
					}]
				}, {
					border: false,
					width: 180,
					labelWidth: 30,
					layout: 'form',
					items: [{
						anchor: '90%',
						fieldLabel: 'Имя',
						id: 'rvwRDBVPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 26
					}]
				}, {
					border: false,
					width: 210,
					labelWidth: 60,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvwRDBVPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 27
					}]
				}, {
					border: false,
					width: 230,
					labelWidth: 80,
					layout: 'form',
					items: [{
						anchor: '90%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: win.firstTabIndex + 28,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 80,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: win.firstTabIndex + 29,
						disabled: false,
						handler: function () {
							win.DataBadVolGridSearch();
						}
					}]
				}, {
					layout: 'form',
					border: false,
					width: 80,
					items: [{
						disabled: false,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						tabIndex: win.firstTabIndex + 30,
						text: BTN_FRMRESET,
						tooltip: BTN_FRMRESET_TIP,
						xtype: 'button',
						handler: function () {
							win.DataBadVolGridReset();
						}
					}]
				}]
			}]
		});

		// 5. Превышение плановых объёмов
		win.DataBadVolGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: true, hidden: true},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', text: 'Удалить случаи из реестра', handler: function() { win.deleteRegistryData(win.DataBadVolGrid); }},
				{name:'action_print'},
				{name:'action_deleteall', disabled: true, visible: !isAdmin, tooltip: 'Удалить все случаи с превышением', icon: 'img/icons/delete16.png',text: 'Удалить все случаи с превышением', handler: function() { win.deleteRegistryData(win.DataBadVolGrid, true); }},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() { win.openForm(win.DataBadVolGrid, {}, 'OpenEvn');}},
				{name:'action_openvolume', disabled: true, visible: !isAdmin, tooltip: 'Открыть объем', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть объем', handler: function() { win.openForm(win.DataBadVolGrid, {}, 'OpenVolume');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, tooltip: 'Открыть данные пациента', icon: 'img/icons/patient16.png', text: 'Открыть данные пациента', handler: function() { win.openForm(win.DataBadVolGrid, {}, 'OpenPerson');}}
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryDataBadVol',
			id: win.id + 'DataBadVol',
			object: 'RegistryData',
			onLoadData: function() {
				let RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 2 && RegistryStatus_id != 3) || readOnly);
				this.setActionDisabled('action_deleteall', (RegistryStatus_id != 2 && RegistryStatus_id != 3) || readOnly);
			},
			onRowSelect: function(sm,rowIdx,rec) {
				this.getAction('action_delete').setText((rec.get('RegistryData_deleted') == 2) ? 'Восстановить отмеченные случаи' : 'Удалить случаи из реестра');
				this.getAction('action_deleteall').setText((rec.get('RegistryData_deleted') == 2) ? 'Восстановить все случаи с превышением' : 'Удалить все случаи с превышением');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', readOnly);
					this.setActionDisabled('action_openevn', readOnly);
					this.setActionDisabled('action_openvolume', readOnly);
					this.setActionDisabled('action_deleteall', readOnly);
				}
			},
			paging: true,
			passPersonEvn: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'RegistryDataBadVol_id', type: 'int', key: true, hidden: true},
				{name: 'VolumeType_Name', header: 'Тип превышения', width: 300},
				{name: 'VolumeType_id', type: 'int', header: 'VolumeType_id',  hidden: true},
				{name: 'AttributeValue_id', type: 'int', header: 'AttributeValue_id',  hidden: true},
				{name: 'Volume_Period', header: 'Период действия объёма', width: 200},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: false, hideable: true},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Дата начала случая', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Дата окончания случая', width: 80},
				{name: 'RegistryData_deleted', hidden: true}
			],
			title: 'Превышение плановых объёмов',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		this.DataBadVolGrid.ViewGridPanel.view = new Ext.grid.GridView(
			{
				getRowClass : function (row, index)
				{
					var cls = '';
					if (row.get('RegistryData_deleted') == 2)
						cls = cls+'x-grid-rowdeleted ';
					return cls;
				}
			});
		
		// 6. Ошибки перс. данных 
		win.BDZErrorGrid = new sw.Promed.ViewFrame({
			id: win.id + 'BDZError',
			title: 'Ошибки перс. данных',
			object: 'RegistryErrorTFOMS',
			dataUrl: '/?c=Registry&m=loadRegistryErrorBDZ',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			region: 'center',
			passPersonEvn: true,
			selectionModel: 'multiselect',
			split: true,
			useEmptyRecord: false,
			stringfields: [
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden: true},
				{name: 'RegistryData_notexist', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id'},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorType_Name', header: 'Наименование', id: 'autoexpand'},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Person_SurName', header: 'Фамилия', width: 150},
				{name: 'Person_FirName', header: 'Имя', width: 150},
				{name: 'Person_SecName', header: 'Отчество', width: 150}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
					{ field: 'RegistryErrorTFOMS_Comment', tpl: '{RegistryErrorTFOMS_Comment}' }
				])
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', disabled: true, hidden: true },
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true },
				{name:'action_refresh'},
				{name:'action_print'},
				{name:'-'},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() { win.openForm(win.BDZErrorGrid, {}, 'OpenPerson');}}
			],
			onRowSelect: function(sm, rowIdx, record) {
				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', readOnly);
				}
			}
		});

		win.BDZErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				let cls = '';

				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';

				if (row.get('RegistryData_notexist') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)

					cls = 'x-grid-panel';

				return cls;
			}
		});

		win.TFOMSGridSearch = function() {
			let filtersForm = win.RegistryTFOMSFiltersPanel.getForm();

			let registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			let Registry_id = registry.get('Registry_id');
			let RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				win.TFOMSErrorGrid.loadData({
					globalFilters: {
						Person_FIO: filtersForm.findField('Person_FIO').getValue(),
						RegistryErrorType_Code: filtersForm.findField('TFOMSError').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id, 
						start: 0, 
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		};
		
		// Кнопка "Поиск"
		let rvnwTFOMSBtnSearch = new Ext.Button({
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvnwTFOMSBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png', 
			iconCls : 'x-btn-text',
			disabled: false, 
			handler: function() {
				win.TFOMSGridSearch();
			}
		});

		rvnwTFOMSBtnSearch.tabIndex = win.firstTabIndex + 34;

		win.RegistryTFOMSFiltersPanel = new Ext.form.FormPanel({
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			id: win.id + 'RegistryTFOMSFiltersPanel',
			keys: [{
				fn: function(e) {
					win.TFOMSGridSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
				defaults: {
					bodyStyle: 'padding-left: 4px; background:#DFE8F6;'
				},
				items: [{
					layout: 'form',
					border: false,
					width: 180,
					labelWidth: 40,
					items: [{
						anchor: '100%',
						fieldLabel: 'ФИО',
						name: 'Person_FIO',
						xtype: 'textfieldpmw',
						id: 'rvnwTFOMSPersonFIO',
						tabIndex: win.firstTabIndex + 31
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 50,
					items: [{
						anchor: '100%',
						fieldLabel: 'Ошибка',
						name: 'TFOMSError',
						id: 'rvnwTFOMSError',
						xtype: 'textfield',
						tabIndex: win.firstTabIndex + 32
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 70,
					items: [{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: win.firstTabIndex + 33,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding-left: 4px; background: #DFE8F6;',
					width: 110,
					items: [
						rvnwTFOMSBtnSearch
					]
				}]
			}]
		});

		// 7. Итоги проверки СМО / ТФОМС
		win.TFOMSErrorGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', disabled: readOnly, text: '<b>Исправить</b>', handler: function() {win.openForm(win.TFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случаи из реестра', handler: function() { win.deleteRegistryData(win.TFOMSErrorGrid); }},
				{name:'action_refresh'},
				{name:'-'},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() { win.openForm(win.TFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() { win.openForm(win.TFOMSErrorGrid, {}, 'OpenPerson');}}
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryErrorTFOMS',
			id: win.id + 'TFOMSError',
			object: 'RegistryErrorTFOMS',
			onLoadData: function() {
				let RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 2 && RegistryStatus_id != 3) || readOnly);
			},
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить отмеченные случаи' : 'Удалить случаи из реестра');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', readOnly);
					this.setActionDisabled('action_openevn', readOnly);
				}
			},
			paging: true,
			passPersonEvn: true,
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
					{ field: 'RegistryErrorTfomsType_Descr', tpl: '{RegistryErrorTfomsType_Descr}' }
				])
			],
			region: 'center',
			root: 'data',
			selectionModel: 'multiselect',
			split: true,
			stringfields: [
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden: true},
				{name: 'RegistryData_notexist', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: false},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: false},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorType_Name', header: 'Наименование', id: 'autoexpand'},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpec_Name', header: 'Специальность', width: 200}
			],
			title: 'Итоги проверки СМО / ТФОМС',
			toolbar: false,
			totalProperty: 'totalCount',
			useEmptyRecord: false
		});

		win.TFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				let cls = '';

				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';

				if (row.get('RegistryData_notexist') == 2)
					cls = cls+'x-grid-rowdeleted ';

				if (cls.length == 0)
					cls = 'x-grid-panel'; 

				return cls;
			}
		});

		let tabNum = 0;

		win.DataTab = new Ext.TabPanel({
			activeTab: 0,
			autoScroll: true,
			border: false,
			defaults: {
				bodyStyle: 'width: 100%;'
			},
			enableTabScroll: true,
			id: win.id + 'DataTab',
			layoutOnTabChange: true,
			listeners: {
				'tabchange': function(tab, panel) {
					let record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

					if ( record ) {
						win.onRegistrySelect(record.get('Registry_id'), record.get('RegistryType_id'), true, record);
					}
				}
			},
			region: 'center',

			items: [{
				border: false,
				frame: true,
				iconCls: 'info16',
				id: 'tab_registry',
				layout: 'fit',
				title: (tabNum++) + '. Реестр',
				items: [
					win.RegistryPanel
				]
			}, {
				border: false,
				id: 'tab_data',
				layout: 'fit',
				title: (tabNum++) + '. Данные',
				items: [{
					border: false,
					layout:'border',
					region: 'center',
					items: [
						win.RegistryDataFiltersPanel,
						win.DataGrid
					]
				}]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_commonerr',
				layout: 'fit',
				title: (tabNum++) + '. Общие ошибки',
				items: [
					win.ErrorComGrid
				]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_dataerr',
				layout: 'fit',
				title: (tabNum++) + '. Ошибки данных',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.RegistryErrorFiltersPanel,
						win.ErrorGrid
					]
				}]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_datanopolis',
				layout: 'fit',
				title: (tabNum++) + '. Незастрахованные',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.NoPolisGrid
					]
				}]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_databadvol',
				layout: 'fit',
				title: (tabNum++) + '. Превышение плановых объёмов',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.RegistryDataBadVolFiltersPanel,
						win.DataBadVolGrid
					]
				}]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_datapersonerr',
				layout: 'fit',
				title: (tabNum++) + '. Ошибки перс. данных',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.BDZErrorGrid
					]
				}]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_datatfomserr',
				layout: 'fit',
				title: (tabNum++) + '. Итоги проверки СМО / ТФОМС',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.RegistryTFOMSFiltersPanel,
						win.TFOMSErrorGrid
					]
				}]
			}]
		});

		win.RegistryListPanel = new sw.Promed.Panel({
			border: false,
			defaults: {
				split: true
			},
			layout: 'border',
			items: [
				win.AccountGrid,
				win.DataTab
			]
		});

		win.UnionRegistryGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name:'action_add' },
				{ name:'action_edit' },
				{ name:'action_view' },
				{ name:'action_delete', url: '/?c=Registry&m=deleteUnionRegistry', msg: 'Вы действительно хотите удалить объединённый реестр?' }
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadUnionRegistryGrid',
			editformclassname: 'swUnionRegistryEditWindow',
			height: 250,
			id: win.id + 'UnionRegistryGrid',
			object: 'Registry',
			onRowSelect: function(sm, rowIdx, record) {
				win.UnionRegistryChildGrid.removeAll();

				win.UnionRegistryGrid.setActionDisabled('action_actions', Ext.isEmpty(record.get('Registry_id')));
				win.UnionRegistryGrid.setActionDisabled('action_print_alt', Ext.isEmpty(record.get('Registry_id')));

				win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[0].disable();
				win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[1].disable();
				win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[2].disable();
				win.UnionRegistryGrid.getAction('action_print_alt').items[0].menu.items.items[0].disable();

				if (!Ext.isEmpty(record.get('Registry_id'))) {
					win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[0].enable();

					if (
						record.get('RegistryGroupType_id') == 12
						&& !Ext.isEmpty(record.get('KatNasel_SysNick'))
						&& record.get('KatNasel_SysNick').inlist(['inog','oblast'])
					) {
						win.UnionRegistryGrid.getAction('action_print_alt').items[0].menu.items.items[0].enable();
					}

					if (!Ext.isEmpty(record.get('Registry_xmlExportPath')) && !Ext.isEmpty(record.get('KatNasel_SysNick'))) {
						if (record.get('KatNasel_SysNick').inlist(['all','inog'])) {
							win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[1].enable();
						}
						else if (record.get('KatNasel_SysNick') == 'oblast') {
							win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[2].enable();
						}
					}

					let Registry_id = record.get('Registry_id');

					win.UnionRegistryChildGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: false
					});
				}
			},
			paging: true,
			passPersonEvn: true,
			region: 'north',
			root: 'data',
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden: !isSuperAdmin()},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'PayType_id', type: 'int', hidden: true},
				{name: 'PayType_SysNick', type: 'string', hidden: true},
				{name: 'RegistryGroupType_id', type: 'int', hidden: true},
				{name: 'Registry_xmlExportPath', type:'string', hidden: true},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type: 'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type: 'date', header: 'Окончание периода', width: 110},
				{name: 'RegistryGroupType_Name', header: 'Тип реестра', width: 200},
				{name: 'PayType_Name', header: langs('Вид оплаты'), width: 100},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'OrgSMO_Name', header: 'СМО', width: 200},
				{name: 'Registry_Count', type: 'int', header: langs('Количество'), width: 100},
				{name: 'Registry_Sum', type: 'money', header: langs('Итоговая сумма'), width: 100},
				{name: 'Registry_SumPaid', type: 'money', header: langs('Сумма к оплате'), width: 100},
				{name: 'Registry_updDate', header: langs('Дата изменения'), width: 110}
			],
			title: 'Объединённые реестры',
			toolbar: true,
			totalProperty: 'totalCount'
		});

		win.UnionRegistryChildGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', disabled: true, hidden: true },
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadUnionRegistryChildGrid',
			id: win.id + 'UnionRegistryChildGrid',
			object: 'Registry',
			paging: true,
			passPersonEvn: true,
			region: 'center',
			root: 'data',
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden: !isSuperAdmin()},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'RegistryType_Name', header: 'Тип реестра', width: 130},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'OrgSMO_Name', header: 'СМО', width: 200},
				{name: 'Registry_Count', type: 'int', header: langs('Количество'), width: 100},
				{name: 'Registry_Sum', type: 'money', header: langs('Итоговая сумма'), width: 100},
				{name: 'Registry_SumPaid', type: 'money', header: langs('Сумма к оплате'), width: 100},
				{name: 'Registry_updDate', header: langs('Дата изменения'), width: 110}
			],
			title: 'Реестры',
			toolbar: true,
			totalProperty: 'totalCount'
		});

		win.UnionActionsMenu = new Ext.menu.Menu({
			items: [
				{name:'action_export', text: 'Экспорт в XML', handler: function() { win.exportRegistryToXml('union'); }},
				{name:'action_import_tfoms', text: 'Импорт ответа ТФОМС', handler: function() { win.importRegistry('tfoms'); }},
				{name:'action_import_smo', text: 'Импорт ответа СМО', handler: function() { win.importRegistry('smo'); }}
			]
		});

		win.UnionRegistryListPanel = new sw.Promed.Panel({
			border: false,
			layout:'border',
			defaults: {split: true},
			items: [
				win.UnionRegistryGrid,
				win.UnionRegistryChildGrid
			]
		});

		Ext.apply(this, {
			buttons: [{
				hidden: false,
				handler: function() {
					win.getReplicationInfo();
				},
				iconCls: 'ok16',
				text: 'Актуальность данных: (неизвестно)'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			defaults: {
				split: true
			},
			layout: 'border',
			items: [ win.Tree, {
				activeItem: 0,
				border: false,
				defaults: {
					split: true
				},
				id: 'regvRightPanel',
				layout: 'card',
				region: 'center',
				items: [
					win.RegistryListPanel,
					win.UnionRegistryListPanel
				]
			}]
		});

		sw.Promed.swRegistryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});