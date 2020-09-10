/**
* swRegistryNewViewWindow - окно просмотра и редактирования реестров (новых).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      18.11.2009
* @comment      Префикс для id компонентов regnv (RegistryNewViewWindow)
*/

sw.Promed.swRegistryNewViewWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	firstTabIndex: 15800,
	height: 500,
	id: 'RegistryNewViewWindow',
	layout: 'border',
	listeners: {
		'beforeshow': function() {
			this.findById('regnvRightPanel').setVisible(false);
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

		var win = this;

		win.AccountGrid.getAction('action_yearfilter').setText('фильтр по году: <b>за ' + (new Date()).getFullYear() + ' год</b>');
		win.AccountGrid.ViewGridPanel.getStore().baseParams['Registry_accYear'] = (new Date()).getFullYear();

		Ext.Ajax.request({
			callback: function(o, s, r) {
				if ( s ) {
					var reg_years = Ext.util.JSON.decode(r.responseText);

					// сортируем в обратном порядке
					reg_years.sort(function(a, b) {
						if (a['reg_year'] > b['reg_year']) return -1;
						if (a['reg_year'] < b['reg_year']) return 1;
					});

					var
						grid = win.AccountGrid.ViewGridPanel,
						menuactions = new Ext.menu.Menu(),
						parentAction = grid.getTopToolbar().items.items[10];

					reg_years.push({
						reg_year: 0
					});

					for ( i in reg_years ) {
						if ( getPrimType(reg_years[i]) == 'object' ) {
							var act = new Ext.Action({
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
	deleteRegistryData: function(grid, deleteAll) {
		var
			win = this,
			record = grid.getGrid().getSelectionModel().getSelected(),
			reestr = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record && !reestr ) {
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.');
			return false;
		}

		var
			msg,
			Evn_id = record.get('Evn_id'),
			Registry_id = reestr.get('Registry_id'),
			RegistryType_id = reestr.get('RegistryType_id'),
			RegistryData_deleted = 1;

		if ( !Ext.isEmpty(record.get('RegistryData_deleted')) ) {
			RegistryData_deleted = record.get('RegistryData_deleted');
		}

		if ( RegistryData_deleted != 2 ) {
			msg = '<b>Вы действительно хотите удалить выбранную запись <br/>из реестра?</b><br/><br/>'+
				'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данная запись пометится как удаленная <br/>'+
				'и будет удалена из реестра при выгрузке (отправке) реестра.<br/>'+
				'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
		}
		else {
			msg = '<b>Хотите восстановить помеченную на удаление запись?</b>';
		}

		if ( deleteAll ) {
			msg = '<b>Вы действительно хотите удалить все записи по ошибкам <br/>из реестра?</b><br/><br/>'+
				'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данные записи пометятся как удаленные <br/>'+
				'и будут удалены из реестра при выгрузке (отправке) реестра.<br/>'+
				'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
		}

		var params = {
			Registry_id: Registry_id,
			RegistryType_id: RegistryType_id,
			RegistryData_deleted: RegistryData_deleted
		};

		if ( deleteAll ) {
			var records = [];

			grid.getGrid().getStore().each(function(record) {
				if(!Ext.isEmpty(record.get('Evn_id'))) {
					records.push(record.get('Evn_id'));
				}
			});

			params.Evn_ids = Ext.util.JSON.encode(records);
		}
		else {
			params.Evn_id = Evn_id;
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
								// var result = Ext.util.JSON.decode(response.responseText);
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
	deleteRegistryDouble: function(mode) {
		var
			win = this,
			grid = win.DoubleVizitGrid.ViewGridPanel,
			rec = grid.getSelectionModel().getSelected(),
			msg = 'Вы действительно хотите удалить';

		if ( !rec && mode == 'current' ) {
			return false;
		}

		switch ( mode ) {
			case 'current':
				msg += ' выбранную запись?';
				break;

			case 'all':
				msg += ' все записи?';
				break;

			default:
				return false;
				break;
		}

		Ext.Msg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if ( btn === 'yes' ) {
					win.getLoadMask('Удаление...').show();

					Ext.Ajax.request({
						callback: function(options, success, response) {
							win.getLoadMask().hide();
							if( success ) {
								if (mode == 'all') {
									grid.getStore().removeAll();
								} else if (rec) {
									grid.getStore().remove(rec);
								}
							}
						},
						params: {
							mode: mode,
							Evn_id: rec.get('Evn_id') || null,
							Registry_id: win.DoubleVizitGrid.getParam('Registry_id')
						},
						url: '/?c=Registry&m=deleteRegistryDouble'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: msg,
			title: 'Внимание!'
		});
	},
	deleteRegistryQueue: function() {
		var
			record = this.AccountGrid.getGrid().getSelectionModel().getSelected(),
			win = this;

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var
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
								var result = Ext.util.JSON.decode(response.responseText);
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
	exportRegistryToXml: function(mode) {
		if ( typeof mode != 'string' || !mode.inlist([ 'simple', 'union' ]) ) {
			return false;
		}

		var record, win = this;

		switch ( mode ) {
			case 'simple':
				var record = win.AccountGrid.getGrid().getSelectionModel().getSelected();
				break;

			case 'union':
				var record = win.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
				break;
		}

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		if ( record.get('Registry_Count') == 0 && !isSuperAdmin() ) {
			sw.swMsg.alert('Ошибка', 'Экспорт реестра невозможен, нет случаев для экспорта.');
			return false;
		}

		if ( record.get('Registry_IsNeedReform') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Часть реестров нуждается в переформировании, экспорт невозможен.');
			return false;
		}

		getWnd('swRegistryXmlWindow').show({
			onHide: function() {
				switch ( mode ) {
					case 'simple':
						win.AccountGrid.loadData();
						break;

					case 'union':
						win.UnionRegistryGrid.loadData();
						break;
				}
			},
			Registry_id: record.get('Registry_id'),
			Registry_IsNew: 2,
			KatNasel_id: record.get('KatNasel_id'),
			RegistryType_id: record.get('RegistryType_id'),
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
		var config = {};

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
				case 22:
					config.open_form = 'swEvnUslugaEditWindow';
					config.key = 'EvnUslugaCommon_id';
					break;
				case 29:
					config.open_form = 'swEvnUslugaStomEditWindow';
					config.key = 'EvnUslugaStom_id';
					break;
				case 32:
					config.open_form = 'swEvnPSEditWindow';
					config.key = 'EvnPS_id';
					break;
				case 43:
					config.open_form = 'swEvnUslugaOperEditWindow';
					config.key = 'EvnUslugaOper_id';
					break;
				case 47:
					config.open_form = 'swEvnUslugaParEditWindow';
					config.key = 'EvnUslugaPar_id';
					break;
			}
		}

		return config;
	},
	importUnionRegistryFLK: function() {
		var
			record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected(),
			win = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		getWnd('swRegistryImportXMLFromTFOMSWindow').show({
			callback: function() {
				win.UnionRegistryGrid.loadData();
			},
			Registry_id: record.get('Registry_id'),
			Registry_IsNew: 2
		});
	},
	importUnionRegistryXML: function() {
		var
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
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id'),
			Registry_IsNew: 2
		});
	},
	onIsRunQueue: function (RegistryQueue_Position) {
		var win = this;

		win.getLoadMask(LOAD_WAIT).show();

		if ( RegistryQueue_Position === undefined ) {
			Ext.Ajax.request({
				url: '/?c=Registry&m=loadRegistryQueue',
				params: {
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function(options, success, response) {
					if ( success ) {
						var result = Ext.util.JSON.decode(response.responseText);
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
		var
			win = this,
			RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;

		if ( win.AccountGrid.getCount() > 0 ) {
			switch ( win.DataTab.getActiveTab().id ) {
				case 'tab_registry_new':
					// бряк!
					break;

				case 'tab_data_new':
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
					setLpuSectionGlobalStoreFilter({ onDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y') });
					win.RegistryDataFiltersPanel.getForm().findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
					break;

				case 'tab_commonerr_new':
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

				case 'tab_dataerr_new':
					win.ErrorGrid.loadData({
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_datanopolis_new':
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

				case 'tab_datapersonerr_new':
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

				case 'tab_datatfomserr_new':
					win.TFOMSErrorGrid.loadData({
						callback: function() {
							win.TFOMSErrorGrid.ownerCt.doLayout();
						},
						globalFilters: {
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							start: 0,
							limit: 100
						},
						noFocusOnLoad: !nofocus
					});
					break;

				case 'tab_datavizitdouble_new':
					win.DoubleVizitGrid.loadData({
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
				case 'tab_data_new':
					win.DataGrid.removeAll(true);
					break;

				case 'tab_commonerr_new':
					win.ErrorComGrid.removeAll(true);
					break;

				case 'tab_dataerr_new':
					win.ErrorGrid.removeAll(true);
					break;

				case 'tab_datanopolis_new':
					win.NoPolisGrid.removeAll(true);
					break;

				case 'tab_datapersonerr_new':
					win.BDZErrorGrid.removeAll(true);
					break;

				case 'tab_datatfomserr_new':
					win.TFOMSErrorGrid.removeAll(true);
					break;

				case 'tab_datavizitdouble_new':
					win.DoubleVizitGrid.removeAll(true);
					break;
			}
		}

		return true;
	},
	onTreeClick: function(node, e) {
		var
			win = this,
			level = node.getDepth();

		win.RegistryErrorFiltersPanel.getForm().reset();

		switch ( level ) {
			case 0: case 1:
				win.findById('regnvRightPanel').setVisible(false);
				break;

			case 2:
				// отображение объединённых реестров
				var Lpu_id = node.parentNode.attributes.object_value;
				win.findById('regnvRightPanel').setVisible(true);
				win.findById('regnvRightPanel').getLayout().setActiveItem(1);
				win.UnionRegistryGrid.loadData({
					params: {
						Registry_IsNew: 2,
						Lpu_id: Lpu_id
					},
					globalFilters: {
						Registry_IsNew: 2,
						Lpu_id: Lpu_id,
						start: 0,
						limit: 100
					}
				});
				break;

			case 3:
				win.findById('regnvRightPanel').setVisible(false);
				break;

			case 4:
				win.findById('regnvRightPanel').setVisible(true);
				win.findById('regnvRightPanel').getLayout().setActiveItem(0);

				var
					Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value,
					RegistryType_id = node.parentNode.attributes.object_value,
					RegistryStatus_id = node.attributes.object_value;

				if ( RegistryType_id == 1 || RegistryType_id == 14 ) {
					win.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Дата поступления');
					win.DataGrid.setColumnHeader('Evn_disDate', 'Дата выписки');
					win.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата поступления');
					win.ErrorGrid.setColumnHeader('Evn_disDate', 'Дата выписки');
					win.TFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Дата поступления');
					win.TFOMSErrorGrid.setColumnHeader('Evn_disDate', 'Дата выписки');
				}
				else if ( RegistryType_id == 2 ) {
					win.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Дата первого посещения');
					win.DataGrid.setColumnHeader('Evn_disDate', 'Дата последнего посещения');
					win.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата первого посещения');
					win.ErrorGrid.setColumnHeader('Evn_disDate', 'Дата последнего посещения');
					win.TFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Дата первого посещения');
					win.TFOMSErrorGrid.setColumnHeader('Evn_disDate', 'Дата последнего посещения');
				}
				else {
					win.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Дата начала случая');
					win.DataGrid.setColumnHeader('Evn_disDate', 'Дата окончания случая');
					win.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата начала случая');
					win.ErrorGrid.setColumnHeader('Evn_disDate', 'Дата окончания случая');
					win.TFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Дата начала случая');
					win.TFOMSErrorGrid.setColumnHeader('Evn_disDate', 'Дата окончания случая');
				}

				win.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id != 3));
				win.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id != 3));

				if ( 6 == RegistryStatus_id ) {
					win.AccountGrid.deletedRegistriesSelected = true;
				}
				else {
					win.AccountGrid.deletedRegistriesSelected = false;
				}

				win.setMenuActions(win.AccountGrid, RegistryStatus_id, RegistryType_id);

				win.AccountGrid.getAction('action_yearfilter').setHidden(RegistryStatus_id != 4);

				if( 4 == RegistryStatus_id ) {
					win.constructYearsMenu({
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id
					});
				}

				win.AccountGrid.loadData({
					params: {
						Registry_IsNew: 2,
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id
					},
					globalFilters: {
						Registry_IsNew: 2,
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id
					}
				});
				break;
		}
	},
	openForm: function (object, oparams, frm) {
		var win = this;

		// В зависимости от типа выбираем форму, которую будем открывать
		// Типы лежат в RegistryType
		var record = object.getGrid().getSelectionModel().getSelected();

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}

		var RegistryType_id = win.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		var type = record.get('RegistryType_id');

		if ( !type ) {
			type = RegistryType_id;
		}

		if ( object.id == win.id + 'TFOMSError' || object.id == win.id + 'BDZError' || object.id == win.id + 'Data' || object.id == win.id + 'Error' ) {
			if ( frm == 'OpenPerson' ) {
				type = 108;
			}
		}

		var id = record.get('Evn_rid') || record.get('Evn_id'); // Вызываем родителя, а, если родитель пустой, то основное

		var open_form = '';
		var key = '';
		var params = {
			action: 'edit',
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
				var config = win.getParamsForEvnClass(record);

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

			case 20:
			case 12:
				var config = win.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;
				break;

			case 15:
				var config = win.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;

				id = record.get('Evn_id');
				break;

			case 108:
				open_form = 'swPersonEditWindow';
				key = 'Person_id';
				id = record.get('Person_id');
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

		if (open_form.inlist(['swEvnUslugaEditWindow', 'swEvnUslugaStomEditWindow', 'swEvnUslugaOperEditWindow'])) {
			// эти формы пока нормально открываются только из ТАП/КВС, поэтому найдем ТАП/КВС и откроем его.
			this.getLoadMask('Поиск ТАП/КВС, где добавлена услуга...').show();
			Ext.Ajax.request({
				url: '/?c=Evn&m=getParentEvn',
				params: {
					Evn_id: record.get('Evn_id')
				},
				callback: function(options, success, response) {
					this.getLoadMask().hide();
					if( success ) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success && result.Evn_id)
						{
							var open_form = 'swEvnPLEditWindow';
							var key = 'EvnPL_id';

							switch(result.EvnClass_SysNick) {
								case 'EvnPLStom':
									open_form = 'swEvnPLStomEditWindow';
									key = 'EvnPLStom_id';
									break;
								case 'EvnPLWow':
									open_form = 'EvnPLWOWEditWindow';
									key = 'EvnPLWOW_id';
									break;
								case 'EvnPS':
									open_form = 'swEvnPSEditWindow';
									key = 'EvnPS_id';
									break;
							}

							params[key] = result.Evn_id;

							getWnd(open_form).show(params);
						}
					}
				}.createDelegate(this)
			});
		} else {
			getWnd(open_form).show(params);
		}
	},
	overwriteRegistryTpl: function(record) {
		var win = this;

		var sparams = {
			Registry_Num: record.get('Registry_Num'),
			KatNasel_Name: record.get('KatNasel_Name'),
			PayType_Name: record.get('PayType_Name'),
			Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'),
			Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'),
			Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'),
			ReformTime: record.get('ReformTime'),
			Registry_Count: '<div style="padding:2px;font-size: 12px;">Количество записей в реестре: ' + record.get('Registry_Count') + '</div>',
			Registry_Sum: sw.Promed.Format.rurMoney(record.get('Registry_Sum'))
		};

		win.RegistryTpl.overwrite(win.RegistryPanel.body, sparams);
	},
	printRegistry: function(mode, type)
	{
		if ( typeof mode != 'string' || !mode.inlist([ 'simple','union']) ) {
			return false;
		}

		var format = 'xls', template, win = this;
		switch ( mode ) {
			case 'simple':
				var record = win.AccountGrid.getGrid().getSelectionModel().getSelected();
				break;
			case 'union':
				var record = win.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
				break;
		}

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		if ( type == 2 ) {
			switch ( record.get('RegistryType_id') ) {
				case 1:
					template = ['printSvodVed_Stac.rptdesign','printSvodVed_DStac.rptdesign'];
					break;
				case 2:
					if ( !Ext.isEmpty(record.get('PayType_SysNick')) && record.get('PayType_SysNick').inlist([ 'dms', 'contract' ]) ) {
						template = 'printSvodVed_polka_SpecKont.rptdesign';
					}
					else {
						template = 'printSvodVed_polka.rptdesign';
					}
					break;
				case 6:
					template = 'printSvodVed_Smp.rptdesign';
					break;
				case 7:
					if ( record.get('DispClass_id') == 2 ) {
						template = 'Registry_EvnPLDD13_2stage_svod_ved.rptdesign';
					}
					else {
						template = 'Registry_EvnPLDD13_svod_ved.rptdesign';
					}
					break;
				case 9:
					template = 'printSvodVed_DDS.rptdesign';
					break;
				case 12:
					template = 'Registry_ProfTeen_svod_ved.rptdesign';
					break;
				case 15:
				case 20:
					template = 'printSvodVed_parusl.rptdesign';
					break;
				case 11:
					template = 'Registry_EvnPLProf_svod_ved.rptdesign';
					break;
				case 14:
					template = 'Registry_HTM_svod_ved.rptdesign';
					break;
			}

			switch ( record.get('RegistryGroupType_id') ) {
				case 1:
					var 
						temp=[],
						filename= {
							'polka_SpecKont':'printSvodVed_polka_SpecKont.rptdesign',
							'polka':'printSvodVed_polka.rptdesign',
							'stac': ['printSvodVed_Stac.rptdesign', 'printSvodVed_DStac.rptdesign']
						};
					
					win.UnionRegistryChildGrid.getGrid().getStore().each(function(rec) {
						if (!Ext.isEmpty(rec.get('RegistryType_SysNick'))){
							
							if(rec.get('RegistryType_SysNick').inlist([ 'omspol' ]) && !Ext.isEmpty(rec.get('PayType_SysNick')) ){
								switch ( rec.get('PayType_SysNick') ) {
									case 'oms':
									case 'bud' :
									case 'speckont':
										if(typeof(temp['polka']) == "object" ) {
											temp['polka'].push(rec.get('Registry_id'));
										}else {
											temp['polka']=[rec.get('Registry_id')];
										}
										break;
									case 'dms':
									case 'contract' :
										if(typeof(temp['polka_SpecKont']) == "object" ) {
											temp['polka_SpecKont'].push(rec.get('Registry_id'));
										}else {
											temp['polka_SpecKont']=[rec.get('Registry_id')];
										}
										break;
								}
							}

							if(rec.get('RegistryType_SysNick').inlist([ 'omsstac' ]) && !Ext.isEmpty(rec.get('PayType_SysNick'))){
								switch ( rec.get('PayType_SysNick') ) {
									case 'oms':
										if(typeof(temp['stac']) == "object" ) {
											temp['stac'].push(rec.get('Registry_id'));
										}else {
											temp['stac']=[rec.get('Registry_id')];
										}
										break;
								}
							}

						}
					});
					if(Object.keys(temp).length==1) {
						template=filename[Object.keys(temp)[0]];
					}else {
						var keys = Object.keys(temp);
						for (var i = 0, l = keys.length; i < l; i++) {
							temp[keys[i]].forEach(function (entry) {
								printBirt({
									'Report_FileName': filename[keys[i]],
									'Report_Params': '&paramRegistry=' + entry,
									'Report_Format': format
								});
							});
						}
					}
					break;
				case 2:
					if(!Ext.isEmpty(record.get('PayType_SysNick')) && record.get('PayType_SysNick') =='oms') {
						template = 'Registry_HTM_svod_ved.rptdesign';
					}
					break;
				case 3:
						template = 'Registry_EvnPLDD13_svod_ved.rptdesign';
					break;
				case 4:
					template = 'Registry_EvnPLDD13_2stage_svod_ved.rptdesign';
					break;
				case 10:
					template = 'Registry_EvnPLProf_svod_ved.rptdesign';
					break;
				case 21:
					template = 'printSvodVed_Smp.rptdesign';
					break;
				case 27:
				case 29:
					template = 'printSvodVed_DDS.rptdesign';
					break;
				case 33:
					template = 'Registry_ProfTeen_svod_ved.rptdesign';
					break;
				case 34:
					template = 'printSvodVed_parusl.rptdesign';
					break;
			}
		} else {
			if ( record.get('PayType_SysNick') === 'oms' && record.get('KatNasel_SysNick') === 'oblast' ) {
				template = 'printSchet_smo.rptdesign';
			}
			else {
				template = 'printSchet_tfoms.rptdesign';
			}
		}

		if(template) {
			printBirt({
				'Report_FileName': template,
				'Report_Params': '&paramRegistry=' + record.get('Registry_id'),
				'Report_Format': format
			});
		}
	},
	refreshRegistry: function() {
		var
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
							RegistryType_id: record.get('RegistryType_id'),
							Registry_IsNew: 2
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
		var
			win = this,
			record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) || Ext.isEmpty(record.get('RegistryType_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get(win.id), {msg: 'Подождите, идет переформирование реестра...'});
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

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
				RegistryType_id: record.get('RegistryType_id'),
				Registry_IsNew: 2
			},
			url: '/?c=Registry&m=reformRegistry',
			timeout: 600000
		});
	},
	setMenuActions: function (object, RegistryStatus_id, RegistryType_id) {
		var
			win = this,
			menu = [];

		if ( !win.menu ) {
			win.menu = new Ext.menu.Menu({
				id: 'RegistryNewMenu'
			});
		}

		object.addActions({
			name: 'action_yearfilter',
			menu: new Ext.menu.Menu()
		});

		object.addActions({
			name: 'action_new',
			text: 'Действия',
			iconCls: 'actions16',
			menu: win.menu
		});

		switch ( RegistryStatus_id ) {
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
					text: 'Переформировать реестр',
					tooltip: 'Переформировать реестр',
					handler: function() {
						win.reformRegistry();
					}
				}, {
					text: 'Пересчитать реестр',
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
					text: 'Снять отметку "к оплате"',
					tooltip: 'Снять отметку "к оплате"',
					handler: function() {
						win.setRegistryStatus(3);
					}
				}, {
					text: 'Отметить как оплаченный',
					tooltip: 'Отметить как оплаченный',
					handler: function() {
						win.setRegistryStatus(4);
					}
				}];
				break;

			case 4: // Оплаченные
				menu = [{
					text: 'Снять отметку "оплачен"',
					tooltip: 'Снять отметку "оплачен"',
					handler: function() {
						win.setRegistryStatus(2);
					}
				}];
				break;

			default:
				sw.swMsg.alert('Ошибка', 'Значение статуса неизвестно! Значение статуса: ' + RegistryStatus_id);
				break;
		}

		win.menu.removeAll();

		for ( key in menu ) {
			win.menu.add(menu[key]);
		}

		return true;
	},
	setRegistryStatus: function(RegistryStatus_id) {
		var
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
				RegistryStatus_id: RegistryStatus_id,
				Registry_IsNew: 2
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.RegistryStatus_id == RegistryStatus_id ) {
						// Перечитываем грид, чтобы обновить данные по счетам
						win.AccountGrid.loadData();
					}
				}
			}
		});
	},
	setUnionRegistryMenuActions: function() {
		var win = this;

		win.UnionRegistryGrid.setActionHidden('action_print', true);

		win.UnionRegistryGrid.addActions({
			name: 'action_actions',
			text: 'Действия',
			iconCls: 'actions16',
			menu: [{
				text: 'Экспорт в XML',
				tooltip: 'Экспорт в XML',
				handler: function() {
					win.exportRegistryToXml('union');
				}
			}, {
				disabled: true,
				handler: function() {
					win.importUnionRegistryFLK();
				},
				text: 'Импорт результата ФЛК (ТФОМС)',
				tooltip: 'Импорт результата ФЛК (ТФОМС)'
			}, {
				disabled: true,
				handler: function() {
					win.importUnionRegistryXML();
				},
				text: 'Импорт реестра из СМО / ТФОМС',
				tooltip: 'Импорт реестра из СМО / ТФОМС'
			}]
		});

		win.UnionRegistryGrid.addActions({
			name: 'action_print_alt',
			text: 'Печать',
			iconCls: 'print16',
			menu: [
				{ name: 'printObject', text: langs('Печать'), handler: function() { win.UnionRegistryGrid.printObject(); }},
				{ name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function() { win.UnionRegistryGrid.printObjectList(); }},
				{ name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function() { win.UnionRegistryGrid.printObjectListFull(); }},
				{
					name: 'printRegistryScore',
					hidden: false,
					handler: function () {
						var current_window = Ext.getCmp('RegistryNewViewWindow');
						var record = current_window.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
						if (!record) {
							Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
							return false;
						}
						var Registry_id = record.get('Registry_id');
						if (!Registry_id)
							return false;
						printBirt({
							'Report_FileName': 'printSchet_smo_union.rptdesign',
							'Report_Params': '&paramRegistry=' + Registry_id,
							'Report_Format': 'xls'
						});
					},
					text: 'Печать счета'
				},
				{name: 'printRegistrySvod', text: langs('Печать сводной ведомости'), handler: function(){ this.printRegistry('union', 2); }.createDelegate(this) }
			]
		});
	},
	show: function() {
		sw.Promed.swRegistryNewViewWindow.superclass.show.apply(this, arguments);

		this.getLoadMask().show();

		if ( this.firstRun == true ) {
			this.firstRun = false;
		}
		else {
			// При открытии если Root Node уже открыта - перечитываем
			var root = this.Tree.getRootNode();

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
	showRunQueue: function (RegistryQueue_Position) {
		var form = this;

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
		var win = this;

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
						loader.baseParams.Registry_IsNew = 2;
					},
					'load': function (loader, node) {
						// Если это родитель, то накладываем фокус на дерево взависимости от настроек
						if ( node.id == 'root' ) {
							if ( node.getOwnerTree().rootVisible == false && node.hasChildNodes() == true ) {
								var child = node.findChild('object', 'Lpu');

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
				{
					name: 'action_print',
					menuConfig: {
						printRegistrySvod: { text: 'Печать сводной ведомости ', handler: function(){ this.printRegistry('simple', 2); }.createDelegate(this) },
						printRegistry: { text: 'Печать счёта', handler: function(){ this.printRegistry('simple', 1); }.createDelegate(this) },
						printApprovalSheet: { text: 'Печать листа согласования',
							handler: function(){
								var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
								var loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите, идет печать листа согласования...'});
								loadMask.show();
								Ext.Ajax.request({
									url: '/?c=Registry&m=getLpuSidList',
									params: {
										Registry_id: record.get('Registry_id'),
									},
									callback: function(options, success, response)
									{
										loadMask.hide();
										if (success)
										{
											var
												responseObj = Ext.util.JSON.decode(response.responseText),
												i=0;

											if(typeof responseObj == 'object'  && responseObj.length > 0){
												for(i=0; i<responseObj.length; i++) {
													printBirt({
														'Report_FileName': 'Registry_agreement.rptdesign',
														'Report_Params': '&paramRegistry=' + record.get('Registry_id') + '&paramLpuDid=' + responseObj[i]['Lpu_sid'],
														'Report_Format': 'xls'
													});
												}
											}else{
												sw.swMsg.alert('Внимание', 'Листа согласования нет<br/>');
											}
										}
										else {
											sw.swMsg.alert('Ошибка', 'Во время формирования листа согласования произошла ошибка<br/>');
										}
									}
								});
							}.createDelegate(this)
						}
					}
				}

			],
			afterSaveEditForm: function(RegistryQueue_id, records) {
				var r = records.RegistryQueue_Position;
				win.onIsRunQueue(r);
			},
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistry',
			editformclassname: 'swRegistryNewEditWindow',
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
					var
						Registry_id = record.get('Registry_id'),
						RegistryType_id = record.get('RegistryType_id'),
						RegistryStatus_id = record.get('RegistryStatus_id');

					win.onRegistrySelect(Registry_id, RegistryType_id, false, record);

					this.setActionDisabled('action_edit', RegistryStatus_id != 3);
					this.setActionDisabled('action_delete', RegistryStatus_id != 3);
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
					this.setActionDisabled('action_print', true);

					switch ( win.DataTab.getActiveTab().id ) {
						case 'tab_registry_new':
							win.RegistryPanel.hide();
							break;
						case 'tab_data_new':
							win.DataGrid.removeAll(true);
							break;
						case 'tab_commonerr_new':
							win.ErrorComGrid.removeAll(true);
							break;
						case 'tab_dataerr_new':
							win.ErrorGrid.removeAll(true);
							break;
						case 'tab_datanopolis_new':
							win.NoPolisGrid.removeAll(true);
							break;
						case 'tab_datapersonerr_new':
							win.BDZErrorGrid.removeAll(true);
							break;
						case 'tab_datatfomserr_new':
							win.TFOMSErrorGrid.removeAll(true);
							break;
						case 'tab_datavizitdouble_new':
							win.DoubleVizitGrid.removeAll(true);
							break;
					}
				}

				// информируем о данных на вкладках
				win.DataTab.getItem('tab_registry_new').setIconClass(record.get('Registry_IsNeedReform') == 2 ? 'delete16' : 'info16');
				win.DataTab.getItem('tab_commonerr_new').setIconClass(record.get('RegistryErrorCom_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_dataerr_new').setIconClass(record.get('RegistryError_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datapersonerr_new').setIconClass(record.get('RegistryErrorBDZ_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datanopolis_new').setIconClass(record.get('RegistryNoPolis_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datatfomserr_new').setIconClass(record.get('RegistryErrorTFOMS_IsData') == 1 ? 'usluga-notok16' : 'good');
				win.DataTab.getItem('tab_datavizitdouble_new').setIconClass(record.get('RegistryDouble_IsData') == 1 ? 'usluga-notok16' : 'good');

				win.DataTab.syncSize();

				this.getAction('action_print').menu.printRegistry.setHidden(record.get('RegistryStatus_id') != 4);

				if (
					record.get('RegistryStatus_id') != 5
					&& (
						(RegistryType_id == 1 || RegistryType_id == 14) && record.get('PayType_SysNick') == 'oms' && record.get('KatNasel_SysNick').inlist(['oblast','inog'])
						|| (RegistryType_id != 1 && RegistryType_id != 14)
					)
				) {
					this.getAction('action_print').menu.printRegistrySvod.setHidden(false);
				}
				else {
					this.getAction('action_print').menu.printRegistrySvod.setHidden(true);
				}

				if((record.get('RegistryStatus_id')==2 ||record.get('RegistryStatus_id')==4) && record.get('RegistryType_id')==20){
					this.getAction('action_print').menu.printApprovalSheet.setHidden(false);
				} else {
					this.getAction('action_print').menu.printApprovalSheet.setHidden(true);
				}

			},
			region: 'north',
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'ReformTime', hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'PayType_SysNick', type: 'string', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorCom_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPolis_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorBDZ_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'RegistryDouble_IsData', hidden: true},
				{name: 'RegistryCheckStatus_Code', type: 'int', hidden: true},
				{name: 'Registry_Num', header: 'Номер счета', width: 80},
				{name: 'Registry_accDate', type: 'date', header: langs('Дата счёта'), width: 80},
				{name: 'Registry_begDate', type: 'date', header: langs('Начало периода'), width: 100},
				{name: 'Registry_endDate', type: 'date', header: langs('Окончание периода'), width: 110},
				{name: 'Registry_Count', type: 'int', header: langs('Количество'), width: 80},
				{name: 'KatNasel_Name', header: langs('Категория населения'), width: 130},
				{name: 'Registry_IsZNO', type: 'checkbox', header: 'ЗНО', width: 50},
				{name: 'Registry_Sum', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'Registry_isPersFin', header: 'Подушевое финансирование', type: 'checkbox', width: 180},
				{name: 'Registry_IsFAP', header: 'ФАП', type: 'checkbox', width: 180},
				{name: 'Registry_IsRepeated', header: 'Повторная подача', type: 'checkbox', width: 50},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110}
			],
			title: langs('Счет')
		});

		win.AccountGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

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

		var RegTplMark = [
			'<div style="padding:2px;font-size: 12px;font-weight:bold;">Реестр № {Registry_Num}</div>'+
			'<div style="padding:2px;font-size: 12px;">Вид оплаты: {PayType_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Категория населения: {KatNasel_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата переформирования реестра: {ReformTime}</div>'+
			'<div style="padding:2px;font-size: 12px;">Сумма к оплате: {Registry_Sum}</div>'+
			'{Registry_Count}'
		];
		win.RegistryTpl = new Ext.XTemplate(RegTplMark);
		
		win.RegistryPanel = new Ext.Panel({
			id: 'RegistryNewPanel',
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
			var filtersForm = win.RegistryDataFiltersPanel.getForm();

			var registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			var Evn_disDate = filtersForm.findField('Evn_disDate').getValue();

			if ( Registry_id > 0 ) {
				win.DataGrid.loadData({
					globalFilters: {
						Registry_id: Registry_id, 
						Registry_IsNew: 2,
						RegistryType_id: RegistryType_id,
						Person_SurName: filtersForm.findField('Person_SurName').getValue(),
						Person_FirName: filtersForm.findField('Person_FirName').getValue(),
						Person_SecName: filtersForm.findField('Person_SecName').getValue(),
						MedPersonal_id: filtersForm.findField('MedPersonal_id').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						filterRecords: filtersForm.findField('filterRecords').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						NumCard: filtersForm.findField('NumCard').getValue(),
						LpuSection_id: filtersForm.findField('LpuSection_id').getValue(),
						Evn_disDate: !Ext.isEmpty(Evn_disDate) && typeof Evn_disDate == 'object' ? Evn_disDate.format('d.m.Y') : null,
						start: 0,
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}
		
		// Кнопка "Поиск"
		var rvnwDGBtnSearch = new Ext.Button({
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
		var rvnwDGBtnReset = new Ext.Button({
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
			height: 85,
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
			listeners: {
				'render': function () {
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
			items: [{
				bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
				border: false,
				defaults: {
					bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'
				},
				layout: 'column',
				items: [{
					border: false,
					columnWidth: .25,
					labelWidth: 100,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvnwDGPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 9
					}]
				}, {
					border: false,
					columnWidth: .25,
					labelWidth: 30,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvnwDGPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 10
					}]
				}, {
					border: false,
					сolumnWidth: .25,
					labelWidth: 60,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvnwDGPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: win.firstTabIndex + 11
					}]
				}, {
					columnWidth: .25,
					border: false,
					labelWidth: 60,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: 'Врач',
						name: 'MedPersonal_id',
						xtype: 'swmedpersonalcombo',
						tabIndex: win.firstTabIndex + 12,
						allowBlank: true
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
					columnWidth: .20,
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
					columnWidth: .20,
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
					border: false,
					columnWidth: .20,
					labelWidth: 90,
					layout: 'form',
					items: [{
						anchor: '100%',
						xtype: 'combo',
						id: 'rvnwDGPfilterRecords',
						listWidth: 200,
						name: 'filterRecords',
						fieldLabel: 'Статус оплаты',
						boxLabel: 'Все случаи',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Оплаченные случаи'],
							[3, 'Неоплаченные случаи']
						],
						allowBlank: false,
						value: 1,
						tabIndex: win.firstTabIndex + 15
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding: 4px; background: #DFE8F6;',
					items: [
						rvnwDGBtnSearch
					]
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
					columnWidth: .20,
					labelWidth: 100,
					layout: 'form',
					items: [{
						anchor: '100%',
						fieldLabel: '№ талона',
						name: 'NumCard',
						xtype: 'textfield',
						tabIndex: win.firstTabIndex + 16
					}]
				}, {
					border: false,
					columnWidth: .20,
					labelWidth: 65,
					layout: 'form',
					items: [{
						fieldLabel: 'Отделение',
						name: 'LpuSection_id',
						listWidth: 600,
						tabIndex: win.firstTabIndex + 17,
						anchor: '100%',
						xtype: 'swlpusectionglobalcombo'
					}]
				}, {
					border: false,
					columnWidth: .20,
					labelWidth: 82,
					layout: 'form',
					items: [{
						fieldLabel: 'Дата выписки',
						format: 'd.m.Y',
						name: 'Evn_disDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: win.firstTabIndex + 18,
						width: 93,
						xtype: 'swdatefield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding: 4px; background: #DFE8F6;',
					items: [
						rvnwDGBtnReset
					]
				}]
			}]
		});
		
		win.ErrorGridSearch = function() {
			var filtersForm = win.RegistryErrorFiltersPanel.getForm();

			var registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				win.ErrorGrid.loadData({
					globalFilters: {
						Registry_id: Registry_id, 
						RegistryType_id: RegistryType_id, 
						Person_SurName: filtersForm.findField('Person_SurName').getValue(),
						Person_FirName: filtersForm.findField('Person_FirName').getValue(),
						Person_SecName: filtersForm.findField('Person_SecName').getValue(),
						RegistryErrorType_id: filtersForm.findField('RegistryErrorType_id').getValue(),
						MedPersonal_id: filtersForm.findField('MedPersonal_id').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						start: 0, 
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}
		
		var rvnwREBtnSearch = new Ext.Button({
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

		rvnwREBtnSearch.tabIndex = win.firstTabIndex + 22;
		
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
			listeners: {
				'render': function() {
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
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
					layout: 'form',
					border: false,
					width: 180,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						hiddenName: 'MedPersonal_id',
						lastQuery: '',
						listWidth: 650,
						editable: true,
						tabIndex: win.firstTabIndex + 23,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
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
		
		// Данные реестра 
		win.DataGrid = new sw.Promed.ViewFrame({
			actions: [
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() { win.openForm(win.DataGrid, {}); }},
				{name:'action_view', handler: function() { win.openForm(win.DataGrid, {action: 'view'}); } },
				{name:'action_delete', disabled: false, handler: function() { win.deleteRegistryData(win.DataGrid, false); }},
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
				var RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;

				this.setActionDisabled('action_delete', RegistryStatus_id != 3);
				//this.setActionDisabled('action_delete_all_records', RegistryStatus_id != 3);
				//this.setActionDisabled('action_undelete_all_records',true);
			},
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');

				if ( this.getCount() > 0 ) {
					this.setActionDisabled('action_openperson', !isAdmin);
					//this.setActionDisabled('action_openevn', !isAdmin);
				}
			},
			paging: true,
			passPersonEvn: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'ИД случая', key: true, hidden: false, hideable: true},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Person_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'EvnPL_NumCard', header: '№ талона', width: 60},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'Person_Polis', header: 'Полис', width: 150},
				{name: 'RegistryData_ItogSum', header: 'Сумма', type: 'money', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Дата начала случая', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Дата окончания случая', width: 80},
				{name: 'RegistryData_Uet', header: 'УЕТ', type: 'float', width: 60},
				{name: 'Paid', header: 'Оплата', width: 60},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'Err_Count', hidden:true}
			],
			region: 'center',
			root: 'data',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		win.DataGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('Err_Count') > 0)
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
			paging: true,
			root: 'data',
			stringfields: [
				{name: 'RegistryErrorType_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', id: 'autoexpand', header: 'Наименование'},
				{name: 'RegistryErrorType_Descr', header: 'Описание', width: 250},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			title: 'Общие ошибки',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		win.ErrorComGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-rowselect ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
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
				{name: 'Evn_rid', hidden:true},
				{name: 'RegistryData_IsCorrected', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'Person_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis', header: 'Полис', width: 150},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				//{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'},
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true},
				{name: 'Mes_Code', header: 'МЭС', width: 80}
			],
			title: 'Ошибки данных',
			toolbar: false,
			totalProperty: 'totalCount',

			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() { win.openForm(win.ErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { win.deleteRegistryData(win.ErrorGrid, false); }},
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { win.deleteRegistryData(win.ErrorGrid, true); }},
				{name:'action_openevn', visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() {Ext.getCmp('RegistryNewViewWindow').openForm(Ext.getCmp('RegistryNewViewWindow').ErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryNewViewWindow').openForm(Ext.getCmp('RegistryNewViewWindow').ErrorGrid, {}, 'OpenPerson');}}
				
			],
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
			},
			onLoadData: function() {
				var RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', RegistryStatus_id != 3);
				this.setActionDisabled('action_deleteall', RegistryStatus_id != 3);
			}
		});

		win.ErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-row ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (row.get('isNoEdit') == 2)
					cls = cls+'x-grid-rowgray ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_notexist') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});

		win.NoPolisGridSearch = function() {
			var registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if ( Registry_id > 0 ) {
				win.NoPolisGrid.loadData({
					globalFilters: {
						Person_Polis: win.findById('rvnwNoPolisGridPolis').getValue(),
						Person_OrgSmo: win.findById('rvnwNoPolisGridOrgSmo').getValue(),
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id,
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
			}
		}

		// Кнопка "Поиск"
		var rvnwNoPolisGridSearch = new Ext.Button({
			disabled: false,
			handler: function() {
				win.NoPolisGridSearch();
			},
			icon: 'img/icons/search16.png',
			iconCls : 'x-btn-text',
			id: 'rvnwNoPolisGridSearch',
			text: BTN_FRMSEARCH,
			tooltip: BTN_FRMSEARCH_TIP
		});

		rvnwNoPolisGridSearch.tabIndex = win.firstTabIndex + 26;

		win.NoPolisGridFiltersPanel = new Ext.Panel({
			bodyStyle: 'width: 100%; background: #DFE8F6; padding: 0px;',
			border: true,
			collapsible: false,
			height: 30,
			id: win.id + 'NoPolisGridFiltersPanel',
			keys:[{
				fn: function(e) {
					win.NoPolisGridSearch();
				},
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			layout: 'column',
			region: 'north',
			items: [{
				bodyStyle: 'padding: 4px; background: #DFE8F6;',
				border: false,
				width: 210,
				labelWidth: 80,
				layout: 'form',
				items: [{
					anchor: '100%',
					fieldLabel: '№ полиса',
					name: 'Person_Polis',
					xtype: 'textfield',
					id: 'rvnwNoPolisGridPolis',
					tabIndex: win.firstTabIndex + 24
				}]
			}, {
				bodyStyle: 'padding: 4px; background: #DFE8F6;',
				border: false,
				width: 180,
				labelWidth: 40,
				layout: 'form',
				items: [{
					anchor: '100%',
					fieldLabel: 'СМО',
					name: 'Person_OrgSmo',
					id: 'rvnwNoPolisGridOrgSmo',
					xtype: 'textfield',
					tabIndex: win.firstTabIndex + 25
				}]
			}, {
				bodyStyle: 'padding: 4px; background: #DFE8F6;',
				border: false,
				width: 110,
				layout: 'form',
				items: [
					rvnwNoPolisGridSearch
				]
			}]
		});
		
		// 4. Незастрахованные
		win.NoPolisGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{
					name: 'action_edit', handler: function() {
						win.openForm(win.NoPolisGrid, {}, 'OpenPerson');
					}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryNoPolis',
			id: win.id + 'NoPolis',
			object: 'RegistryNoPolis',
			onLoadData: function() {
				this.setActionDisabled('action_edit', !(isUserGroup(['RegistryUser']) || isSuperAdmin()));
			},
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
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90}
			],
			title: 'Незастрахованные',
			toolbar: false,
			totalProperty: 'totalCount'
		});

		// 5. Ошибки перс. данных
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
			split: true,
			useEmptyRecord: false,
			stringfields: [
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'RegistryData_deleted', type: 'int', hidden: true},
				{name: 'RegistryData_notexist', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Person_SurName', header: 'Фамилия', width: 150},
				{name: 'Person_FirName', header: 'Имя', width: 150},
				{name: 'Person_SecName', header: 'Отчество', width: 150}
			],
			actions: [
				{ name: 'action_add', disabled: true, hidden: true },
				{ name: 'action_edit', text: '<b>Исправить</b>', handler: function () { win.openForm(win.BDZErrorGrid, {}, 'OpenPerson'); } },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true }
			]
		});

		win.BDZErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls + 'x-grid-rowdeleted ';
				if (row.get('RegistryData_notexist') == 2)
					cls = cls + 'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		win.TFOMSGridSearch = function() {
			var filtersForm = win.RegistryTFOMSFiltersPanel.getForm();

			var registry = win.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');

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
		}
		
		// Кнопка "Поиск"
		var rvnwTFOMSBtnSearch = new Ext.Button({
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

		rvnwTFOMSBtnSearch.tabIndex = win.firstTabIndex + 31;
		
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
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; background:#DFE8F6;'},
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
						tabIndex: win.firstTabIndex + 27
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
						tabIndex: win.firstTabIndex + 28
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
						tabIndex: win.firstTabIndex + 29,
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

		// Ошибки ТФОМС
		win.TFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: win.id + 'TFOMSError',
			title: 'Итоги проверки ТФОМС / СМО',
			object: 'RegistryErrorTFOMS',
			dataUrl: '/?c=Registry&m=loadRegistryErrorTFOMS',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			region: 'center',
			passPersonEvn: true,
			split: true,
			useEmptyRecord: false,
			stringfields: [
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: false},
				{name: 'RegistryData_deleted', type: 'int', hidden: true},
				{name: 'RegistryData_notexist', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis', header: 'Полис', width: 150},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type: 'date', header: 'Окончание', width: 70}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
					{ field: 'RegistryError_Comment', tpl: '{RegistryError_Comment}' }
				])
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {win.openForm(win.TFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { win.deleteRegistryData(win.TFOMSErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { win.deleteRegistryData(win.TFOMSErrorGrid, true); }},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() { win.openForm(win.TFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() { win.openForm(win.TFOMSErrorGrid, {}, 'OpenPerson');}}

			],
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
				}
			},
			onLoadData: function()
			{
				var RegistryStatus_id = win.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 3));
				this.setActionDisabled('action_deleteall', (RegistryStatus_id != 3));
			}
		});

		win.TFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_notexist') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});

		win.DoubleVizitGrid = new sw.Promed.ViewFrame({
			id: win.id + 'DoubleVizit',
			title: 'Дубли посещений',
			object: 'RegistryDouble',
			dataUrl: '/?c=Registry&m=loadRegistryDouble',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden: true },
				{name: 'PersonEvn_id', type: 'int', hidden: true },
				{name: 'EvnPL_NumCard', header: '№ талона', width: 80},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_FullName', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 250},
				{name: 'EvnVizitPL_setDate', header: 'Дата посещения'}
			],
			actions: [
				{name:'action_add', hidden: true },
				{name:'action_edit', hidden: true/*, handler: function() {win.openForm(win.DoubleVizitGrid, {}, 'OpenPerson');}*/},
				{name:'action_view', handler: function() {win.openForm(win.DoubleVizitGrid, {action: 'view'}, 'swEvnPLEditWindow');} },
				{name:'action_delete', handler: this.deleteRegistryDouble.createDelegate(this, ['current']) }
			]
		});
		
		win.DataTab = new Ext.TabPanel({
			activeTab: 0,
			autoScroll: true,
			border: false,
			defaults: {
				bodyStyle: 'width:100%;'
			},
			enableTabScroll: true,
			id: win.id + 'DataTab',
			layoutOnTabChange: true,
			listeners: {
				'tabchange': function(tab, panel) {
					var record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

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
				id: 'tab_registry_new',
				layout: 'fit',
				title: '0. Реестр',
				items: [
					win.RegistryPanel
				]
			}, {
				border: false,
				id: 'tab_data_new',
				layout: 'fit',
				title: '1. Данные',
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
				id: 'tab_commonerr_new',
				layout: 'fit',
				title: '2. Общие ошибки',
				items: [
					win.ErrorComGrid
				]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_dataerr_new',
				layout: 'fit',
				title: '3. Ошибки данных',
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
				id: 'tab_datanopolis_new',
				layout: 'fit',
				title: '4. Незастрахованные',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.NoPolisGridFiltersPanel,
						win.NoPolisGrid
					]
				}]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_datapersonerr_new',
				layout: 'fit',
				title: '5. Ошибки перс. данных',
				items: [
					win.BDZErrorGrid
				]
			}, {
				border: false,
				iconCls: 'good',
				id: 'tab_datatfomserr_new',
				layout: 'fit',
				title: '6. Итоги проверки ТФОМС / СМО',
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						win.RegistryTFOMSFiltersPanel,
						win.TFOMSErrorGrid
					]
				}]
			}, {
				border:false,
				iconCls: 'good',
				id: 'tab_datavizitdouble_new',
				layout: 'fit',
				title: '7. Дубли посещений',
				items: [
					win.DoubleVizitGrid
				]
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
				{name:'action_add' },
				{name:'action_edit' },
				{name:'action_view' },
				{name:'action_delete', url: '/?c=Registry&m=deleteUnionRegistry' }
			],
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadUnionRegistryGrid',
			editformclassname: 'swUnionRegistryNewEditWindow',
			height: 250,
			id: win.id + 'UnionRegistryGrid',
			object: 'Registry',
			onRowSelect: function(sm, rowIdx, record) {
				win.UnionRegistryChildGrid.removeAll();

				win.UnionRegistryGrid.setActionDisabled('action_actions', Ext.isEmpty(record.get('Registry_id')));
				win.UnionRegistryGrid.setActionDisabled('action_print_alt', Ext.isEmpty(record.get('Registry_id')));

				if ( !Ext.isEmpty(record.get('Registry_id')) ) {
					win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[1].setDisabled(!(record.get('KatNasel_SysNick') == 'all' && !Ext.isEmpty(record.get('Registry_xmlExportPath')) && record.get('Registry_xmlExportPath') != '1'));
					win.UnionRegistryGrid.getAction('action_actions').items[1].menu.items.items[1].setDisabled(!(record.get('KatNasel_SysNick') == 'all' && !Ext.isEmpty(record.get('Registry_xmlExportPath')) && record.get('Registry_xmlExportPath') != '1'));
					win.UnionRegistryGrid.getAction('action_actions').items[0].menu.items.items[2].setDisabled(!((record.get('KatNasel_SysNick') == 'oblast' || record.get('KatNasel_SysNick') == 'inog') && !Ext.isEmpty(record.get('Registry_xmlExportPath')) && record.get('Registry_xmlExportPath') != '1'));
					win.UnionRegistryGrid.getAction('action_actions').items[1].menu.items.items[2].setDisabled(!((record.get('KatNasel_SysNick') == 'oblast' || record.get('KatNasel_SysNick') == 'inog') && !Ext.isEmpty(record.get('Registry_xmlExportPath')) && record.get('Registry_xmlExportPath') != '1'));

					win.UnionRegistryChildGrid.loadData({
						params: {
							Registry_IsNew: 2,
							Registry_id: record.get('Registry_id')
						},
						globalFilters: {
							Registry_IsNew: 2,
							Registry_id: record.get('Registry_id'),
							start: 0,
							limit: 100
						}
					});

					win.UnionRegistryGrid.getAction('action_print_alt').items[0].menu.items.items[4].setDisabled(!(!Ext.isEmpty(record.get('RegistryGroupType_id'))  && record.get('RegistryGroupType_id').inlist([1,2,3,4,10,21,27,29,33,34])));
					win.UnionRegistryGrid.getAction('action_print_alt').items[1].menu.items.items[4].setDisabled(!(!Ext.isEmpty(record.get('RegistryGroupType_id'))  && record.get('RegistryGroupType_id').inlist([1,2,3,4,10,21,27,29,33,34])));
				}
			},
			paging: true,
			params: {
				Registry_IsNew: 2
			},
			passPersonEvn: true,
			region: 'north',
			root: 'data',
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'Registry_xmlExportPath', type: 'string', hidden: true},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'RegistryGroupType_id', type:'int', hidden: true},
				{name: 'RegistryGroupType_Name', header: 'Тип объединенного реестра', width: 200},
				{name: 'Registry_IsZNO', type: 'checkbox', header: 'ЗНО', width: 40},
				{name: 'OrgSMO_Name', header: 'СМО', width: 130},
				{name: 'Registry_Sum', type: 'money', header: 'Сумма к оплате', width: 100},
				{name: 'Registry_isPersFin', header: 'Подушевое финансирование', type: 'checkbox', width: 180},
				{name: 'Registry_IsFAP', header: 'ФАП', type: 'checkbox', width: 180},
				{name: 'Registry_IsRepeated', header: 'Повторная подача', type: 'checkbox', width: 50},
				{name: 'PayType_Name', type: 'string', header: 'Вид оплаты', width: 100},
				{name: 'PayType_SysNick', type: 'string', hidden: true}
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
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'RegistryType_SysNick', type: 'string', hidden: true},
				{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
				{name: 'Registry_Sum', type:'money', header: 'Сумма к оплате', width: 100},
				//{name: 'Registry_isPersFin', header: 'Подушевое финансирование', type: 'checkbox', width: 180},
				//{name: 'Registry_IsRepeated', header: 'Повторная подача', type: 'checkbox', width: 50},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'PayType_SysNick', type: 'string', hidden: true},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110}
			],
			title: 'Реестры',
			toolbar: true,
			totalProperty: 'totalCount'
		});

		win.UnionRegistryListPanel = new sw.Promed.Panel({
			border: false,
			defaults: {
				split: true
			},
			layout:'border',
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
				id: 'regnvRightPanel',
				layout: 'card',
				region: 'center',
				items: [
					win.RegistryListPanel,
					win.UnionRegistryListPanel
				]
			}]
		});

		sw.Promed.swRegistryNewViewWindow.superclass.initComponent.apply(this, arguments);
	}
});