/**
* swRegistryViewWindow - окно просмотра и редактирования реестров.
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
* @comment      Префикс для id компонентов regv (RegistryViewWindow)
*/

sw.Promed.swRegistryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstTabIndex: 15800,
	height: 500,
	id: 'RegistryViewWindow',
	layout: 'border',
	maximized: true,
	modal: false,
	resizable: false,
	title: WND_ADMIN_REGISTRYLIST, 
	width: 800,

	/* методы */
	constructYearsMenu: function( params ) {
		if ( !params ) {
			return false;
		}
		
		this.AccountGrid.getAction('action_yearfilter').setText('фильтр по году: <b>за ' + (new Date()).getFullYear() + ' год</b>');
		this.AccountGrid.ViewGridPanel.getStore().baseParams['Registry_accYear'] = (new Date()).getFullYear();

		Ext.Ajax.request({
			url: '/?c=Registry&m=getYearsList',
			params: params,
			callback: function(o, s, r) {
				if ( s ) {
					var reg_years = Ext.util.JSON.decode(r.responseText);

					// сортируем в обратном порядке
					reg_years.sort(function(a, b) {
						if (a['reg_year'] > b['reg_year']) return -1;
						if (a['reg_year'] < b['reg_year']) return 1;
					});

					var grid = this.AccountGrid.ViewGridPanel,
						menuactions = new Ext.menu.Menu(),
						parentAction = grid.getTopToolbar().items.items[10];

					reg_years.push({
						reg_year: 0
					});
					
					for ( i in reg_years ) {
						if ( getPrimType(reg_years[i]) == 'object' ) {
							var act = new Ext.Action({
								text:  reg_years[i]['reg_year'] > 0 ? 'за ' + reg_years[i]['reg_year'] + ' год' : 'за все время'
							});
							act.value = reg_years[i]['reg_year'];
							act.setHandler(function(parAct, grid) {
								parAct.setText('фильтр по году: <b>' + this.getText() + '</b>');
								grid.getStore().load({params: {Registry_accYear: this.value}});
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
			}.createDelegate(this)
		});
	},
	deleteRegistryQueue: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryQueue',
						params: {	
							Registry_id: record.get('Registry_id')
						},
						callback: function(options, success, response) {
							if ( success ) {
								var result = Ext.util.JSON.decode(response.responseText);
								// Перечитываем грид, чтобы обновить данные по счетам
								form.AccountGrid.loadData();
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
	exportRegistryToXml: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}
		
		if ( record.get('Registry_Count') == 0 && !isSuperAdmin() ) {
			sw.swMsg.alert('Ошибка', 'Экспорт реестра невозможен, нет случаев для экспорта.');
			return false;
		}
		
		if ( record.get('Registry_IsNeedReform') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Реестр нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр и повторите действие.');
			return false;
		}
		
		getWnd('swRegistryXmlWindow').show({
			onHide: function() {
				this.AccountGrid.loadData();
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			KatNasel_id: record.get('KatNasel_id'),
			RegistryType_id: record.get('RegistryType_id'),
			url:'/?c=Registry&m=exportRegistryToXml'
		});
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
	importRegistryFLK: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		getWnd('swRegistryImportFLKWindow').show({
			callback: function() {
				//
			},
			onHide: function() {
				//
			},
			Registry_id: record.get('Registry_id'),
			KatNasel_SysNick: record.get('KatNasel_SysNick')
		});
	},
	importRegistryTFOMS: function() {
		var win = this;

		var record = win.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		getWnd('swRegistryImportXMLFromTFOMSWindow').show({
			onHide: function() {
				//
			},
			callback: function() {
				switch ( mode ) {
					case 'simple':
						win.AccountGrid.getGrid().getStore().reload();
					break;

					case 'union':
						win.UnionRegistryGrid.getGrid().getStore().reload();
					break;
				}
			}.createDelegate(this),
			Registry_id: record.get('Registry_id')
		});
	},
	onRegistrySelect: function (Registry_id, RegistryType_id, nofocus) {
		var form = this;
		var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;

		if ( form.AccountGrid.getCount() > 0 ) {
			switch ( form.DataTab.getActiveTab().id ) {
				case 'tab_registry':
					// бряк!
					break;
					
				case 'tab_data':
					if ((form.DataGrid.getParam('Registry_id')!=Registry_id) || (form.DataGrid.getCount()==0)) {
						form.DataGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_commonerr':
					if ((form.ErrorComGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorComGrid.getCount()==0)) {
						form.ErrorComGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_dataerr':
					form.loadErrTypeSpr(Registry_id);
					var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					var begDate = Ext.util.Format.date(reestr.get('Registry_begDate'),'d.m.Y');
					var endDate = Ext.util.Format.date(reestr.get('Registry_endDate'),'d.m.Y');

					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id,
						dateFrom: begDate,
						dateTo: endDate
					});
					form.RegistryErrorFiltersPanel.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());

					if ((form.ErrorGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorGrid.getCount()==0))
					{
						form.ErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datanopolis':
					if ((form.NoPolisGrid.getParam('Registry_id')!=Registry_id) || (form.NoPolisGrid.getCount()==0)) {
						form.NoPolisGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datanopay': 
					if ((form.NoPayGrid.getParam('Registry_id')!=Registry_id) || (form.NoPayGrid.getCount()==0)) {
						form.NoPayGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datapersonerr':
					if ((form.PersonErrorGrid.getParam('Registry_id')!=Registry_id) || (form.PersonErrorGrid.getCount()==0)) {
						form.PersonErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datatfomserr':
					if ((form.TFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.TFOMSErrorGrid.getCount()==0)) {
						form.TFOMSErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datavizitdouble':
					if ((form.DoubleVizitGrid.getParam('Registry_id')!=Registry_id) || (form.DoubleVizitGrid.getCount()==0)) {
						form.DoubleVizitGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
			}
		}
		else {
			switch ( form.DataTab.getActiveTab().id ) {
				case 'tab_data':
					form.DataGrid.removeAll(true);
					break;

				case 'tab_commonerr':
					form.ErrorComGrid.removeAll(true);
					break;

				case 'tab_dataerr':
					form.ErrorGrid.removeAll(true);
					break;

				case 'tab_datanopolis':
					form.NoPolisGrid.removeAll(true);
					break;

				case 'tab_datanopay':
					form.NoPayGrid.removeAll(true);
					break;

				case 'tab_datapersonerr':
					form.PersonErrorGrid.removeAll(true);
					break;

				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.removeAll(true);
					break;

				case 'tab_datavizitdouble':
					form.DoubleVizitGrid.removeAll(true);
					break;
			}
		}

		return true;
	},
	onTreeClick: function(node, e) {
		var
			level = node.getDepth(),
			win = this;

		win.RegistryErrorFiltersPanel.getForm().reset();

		switch ( level ) {
			case 0:
			case 1:
				win.findById('regvRightPanel').setVisible(false);
				break;

			case 2:
				win.findById('regvRightPanel').setVisible(false);
				break;

			case 3:
				win.findById('regvRightPanel').setVisible(true);

				var Lpu_id = node.parentNode.parentNode.attributes.object_value;
				var RegistryType_id = node.parentNode.attributes.object_value;
				var RegistryStatus_id = node.attributes.object_value;

				win.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id != 3));
				win.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id != 3));
				
				// скрываем/открываем колонку
				win.AccountGrid.setColumnHidden('RegistryStacType_Name', (RegistryType_id != 1));
				
				// Меняем колонки и отображение 
				if ( RegistryType_id == 1 || RegistryType_id == 14 ) {
					// Для стаца одни названия 
					win.DataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
					win.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');
					win.DataGrid.setColumnHidden('EvnPS_disDate', false);
					win.DataGrid.setColumnHidden('RegistryData_KdPay', false);
					win.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
					
					// без оплаты 
					//win.DataGrid.setColumnHeader('Evn_setDate', 'Поступление');
					//win.NoPayGrid.setColumnHidden('Evn_disDate', false);
					win.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
					win.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);
				}
				else {
					// Для остальных - другие 
					win.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
					win.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');
					win.DataGrid.setColumnHidden('EvnPS_disDate', true);
					win.DataGrid.setColumnHidden('RegistryData_KdPay', true);
					win.DataGrid.setColumnHidden('RegistryData_KdPlan', true);
					
					// без оплаты 
					//win.DataGrid.setColumnHeader('Evn_setDate', 'Посещение');
					//win.NoPayGrid.setColumnHidden('Evn_disDate', true);
					win.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', true);
					win.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', true);
				}

				win.AccountGrid.setActionDisabled('action_print', RegistryStatus_id != 4);

				var account = win.AccountGrid;

				if ( 6 == RegistryStatus_id ){
					account.deletedRegistriesSelected = true;
				}
				else {
					account.deletedRegistriesSelected = false;
				}
				
				win.setMenuActions(win.AccountGrid, RegistryStatus_id, RegistryType_id);
				
				win.AccountGrid.getAction('action_yearfilter').setHidden( RegistryStatus_id != 4 && RegistryStatus_id != 6 );

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
	reformRegistry: function(record) {
		var win = this;

		if ( record.Registry_id > 0 ) {
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите, идет переформирование реестра...' });
			loadMask.show();

			Ext.Ajax.request({
				url: '/?c=Registry&m=reformRegistry',
				params: {	
					Registry_id: record.Registry_id,
					RegistryType_id: record.RegistryType_id
				},
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ){
						var result = Ext.util.JSON.decode(response.responseText);

						if ( result.Error_Msg == '' || result.Error_Msg == null || result.Error_Msg == 'null' ) {
							// Выводим сообщение о постановке в очередь
							win.onIsRunQueue(result.RegistryQueue_Position);
							// Перечитываем грид, чтобы обновить данные по счетам
							win.AccountGrid.loadData();
						}
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время переформирования произошла ошибка');
					}
				},
				timeout: 600000
			});
		}
	},
	/**
	 * Пересчет реестра
	 */
	refreshRegistry: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		form.getLoadMask().show();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=refreshRegistryData',
						params: {
							Registry_id: record.get('Registry_id'), 
							RegistryType_id: record.get('RegistryType_id')
						},
						callback: function(options, success, response) {
							form.getLoadMask().hide();

							if ( success ) {
								form.AccountGrid.loadData();
							}

							return true;
						}
					});
				}
				else {
					form.getLoadMask().hide();
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Хотите удалить из реестра все помеченные на удаление записи <br/>и пересчитать суммы?',
			title: 'Вопрос'
		});
	},
	registryRevive: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
	
		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}
		
		Ext.Ajax.request({
			url: '/?c=Registry&m=reviveRegistry',
			params: {	
				Registry_id: record.get('Registry_id')
			},
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);
					// Перечитываем грид, чтобы обновить данные по счетам
					form.AccountGrid.loadData();
				}
			}
		});
	},
	setMenuActions: function (object, RegistryStatus_id, RegistryType_id) {
		var form = this;
		var menu = new Array();
		
		if ( !this.menu ) {
			this.menu = new Ext.menu.Menu({id:'RegistryMenu'});
		}

		object.addActions({
			name: 'action_yearfilter',
			menu: new Ext.menu.Menu()
		});

		object.addActions({
			name:'action_new',
			text:'Действия',
			iconCls: 'actions16',
			menu: this.menu
		});

		switch ( RegistryStatus_id ) {
			case 5: 
				// В очереди 
				menu = [{
					text: 'Удалить реестр из очереди',
					tooltip: 'Удалить реестр из очереди',
					handler: function() {
						form.deleteRegistryQueue();
					}
				}];
				break;
		
			case 3: 
				// В работе 
				menu = [{
					text: 'Отметить к оплате',
					tooltip: 'Отметить к оплате',
					handler: function() {
						form.setRegistryStatus(2);
					}
				}, {
					text: 'Переформировать весь реестр',
					tooltip: 'Переформировать весь реестр',
					handler: function() {
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

						if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
							sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
							return false;
						}

						var rec = {
							Registry_id: record.get('Registry_id'),
							RegistryType_id: record.get('RegistryType_id')
						};

						form.reformRegistry(rec);
					}
				}, {
					text: 'Пересчитать реестр',
					tooltip: 'Пересчитать реестр',
					handler: function() {
						form.refreshRegistry();
					}
				}];
				break;

			case 2: // К оплате
				menu = [{
					text: 'Экспорт в XML',
					tooltip: 'Экспорт в XML',
					handler: function() {
						form.exportRegistryToXml();
					}
				}, {
					text: 'Пересчитать реестр',
					tooltip: 'Пересчитать реестр',
					handler: function() {
						form.refreshRegistry();
					}
				}, {
					text: 'Импорт реестра из ТФОМС/СМО',
					tooltip: 'Импорт реестра из ТФОМС/СМО',
					handler: function() {
						form.importRegistryTFOMS();
					}
				}, {
					text: 'Импорт протоколов ФЛК',
					tooltip: 'Импорт протоколов ФЛК',
					handler: function() {
						form.importRegistryFLK();
					}
				}, {
					text: 'Снять отметку "к оплате"',
					tooltip: 'Снять отметку "к оплате"',
					handler: function() {
						form.setRegistryStatus(3);
					}
				}, {
					text: 'Отметить как оплаченный',
					tooltip: 'Отметить как оплаченный',
					handler: function() {
						form.setRegistryStatus(4);
					}
				}];
				break;

			case 4: // Оплаченные 
				menu = [{
					text: 'Снять активность',
					tooltip: 'Снять активность',
					handler: function() {
						form.setRegistryActive();
					}
				}, {
					text: 'Снять отметку "оплачен"',
					tooltip: 'Снять отметку "оплачен"',
					handler: function() {
						form.setRegistryStatus(2);
					}
				}];
				break;

			case 6: 
				// Удаленные 
				menu = [{
					text: 'Восстановить',
					tooltip: 'Восстановить удаленный реестр',
					handler: function() {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							scope : Ext.getCmp('RegistryViewWindow'),
							fn: function(buttonId) {
								if ( buttonId == 'yes' ) {
									form.registryRevive();
								}
							},
							icon: Ext.Msg.QUESTION,
							msg: 'Вы действительно хотите восстановить выбранный реестр?',
							title: 'Восстановление реестра'
						});
					}
				}]; 
				break;

			default:
				sw.swMsg.alert('Ошибка', 'Значение статуса неизвестно! Значение статуса: ' + RegistryStatus_id);
				break;
		}
		
		this.menu.removeAll();

		for ( key in menu ) {
			if ( key != 'remove' ) {
				this.menu.add(menu[key]);
			}
		}

		return true;
	},
	setRegistryActive: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var Registry_IsActive = 1;

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryActive',
			params: {	
				Registry_id: record.get('Registry_id'),
				Registry_IsActive: Registry_IsActive
			},
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.Registry_IsActive == Registry_IsActive ) {
						// Перечитываем грид, чтобы обновить данные по счетам
						// form.AccountGrid.loadData();
						// или без перечитывания так: 
						record.set('Registry_IsActive',Registry_IsActive);
						record.commit();
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(true);
					}
				}
			}
		});
	},
	setRegistryStatus: function(RegistryStatus_id) {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryStatus',
			params: {	
				Registry_id: record.get('Registry_id'),
				RegistryStatus_id: RegistryStatus_id
			},
			callback: function(options, success, response) {
				if (success) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.RegistryStatus_id==RegistryStatus_id ) {
						// Перечитываем грид, чтобы обновить данные по счетам
						form.AccountGrid.loadData();
					}
				}
			}
		});
	},

	/**
	* Функция проверяет на наличие реестров в очереди. И в случае если они там, есть выводит номер очереди и сообщение 
	* Если номер передан в функцию, то вывод сообщения происходит без обращения к серверу.
	* (скорее всего также надо дисаблить все события на форме)
	*/
	showRunQueue: function (RegistryQueue_Position)
	{
		var form = this;
		this.getLoadMask().hide();
		if (RegistryQueue_Position===undefined)
		{
			// Ошибка запроса к серверу
			Ext.Msg.alert('Ошибка', 
				'При отправке запроса к серверу произошла ошибка!<br/>'+
				'Попробуйте обновить страницу, нажав клавиши Ctrl+R.<br/>'+
				'Если ошибка повторится - обратитесь к разработчикам.');
			return false;
		}
		if (RegistryQueue_Position>0)
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				closable:false,
				scope : Ext.getCmp('RegistryViewWindow'),
				fn: function(buttonId) 
				{
					/*
					if ( buttonId == 'ok' )
					{
						// Может быть повторный опрос :)
						//Ext.getCmp('RegistryViewWindow').onIsRunQueue();
					}
					else 
					{
						Ext.getCmp('RegistryViewWindow').hide();
					}*/
				},
				icon: Ext.Msg.WARNING,
				msg: 'Ваш запрос на формирование реестра находится в очереди.<br/>'+
				'Позиция вашего запроса в очереди на формирование: <b>'+RegistryQueue_Position+'</b> место.<br/>',
				//+'Для того, чтобы перечитать позицию в очереди нажмите "Да",<br/>'+
				//'для закрытия формы реестров, нажмите "Нет".',
				title: 'Сообщение'
			});
		}
		else 
		{
			// Позиция нулевая, значит запрос был выполнен
			// form.AccountGrid.loadData();
		}
	},
	
	loadErrTypeSpr: function(Registry_id)
	{
		this.findById('rvwRERegistryErrorType_id').getStore().load({params: {Registry_id:Registry_id}});
	},
	onIsRunQueue: function (RegistryQueue_Position)
	{
		var form = this;
		this.getLoadMask().show();
		if (RegistryQueue_Position===undefined)
		{
			Ext.Ajax.request(
			{
				url: '/?c=Registry&m=loadRegistryQueue',
				params: 
				{
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function(options, success, response) 
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						form.showRunQueue(result.RegistryQueue_Position);
					}
				}
			});
		}
		else 
		{
			form.showRunQueue(RegistryQueue_Position);
		}
		
	},
	/** Удаляем запись из реестра
	*/
	deleteRegistryData: function()
	{
		var record = this.DataGrid.getGrid().getSelectionModel().getSelected();
		var reestr = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record && !reestr)
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.<br/>');
			return false;
		}
		var Evn_id = record.get('Evn_id');
		var Registry_id = reestr.get('Registry_id');
		var RegistryType_id = reestr.get('RegistryType_id');
		var RegistryData_deleted = record.get('RegistryData_deleted');
		if (RegistryData_deleted!=2) {
			var msg = '<b>Вы действительно хотите удалить выбранную запись <br/>из реестра?</b><br/><br/>'+
					 '<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данная запись пометится как удаленная <br/>'+
					 'и будет удалена из реестра при выгрузке (отправке) реестра.<br/>'+
					 'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
		} else {
			var msg = '<b>Хотите восстановить помеченную на удаление запись?</b>';
		}
		
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			scope : Ext.getCmp('RegistryViewWindow'),
			fn: function(buttonId) 
			{
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request(
					{
						url: '/?c=Registry&m=deleteRegistryData',
						params: 
						{
							Evn_id: Evn_id,
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id,
							RegistryData_deleted: RegistryData_deleted
						},
						callback: function(options, success, response) 
						{
							if (success)
							{
								var result = Ext.util.JSON.decode(response.responseText);
								// Перечитываем грид, чтобы обновить данные по счетам
								form.DataGrid.getGrid().getStore().reload();
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
	
	setNeedReform: function(record)
	{
		/*if (record)
		{
			var params  = {};
			params.Registry_id = record.get('Registry_id');
			params.Evn_id = record.get('Evn_id');
			
			Ext.Ajax.request(
			{
				url: '/?c=Registry&m=setNeedReform',
				params: params,
				callback: function(options, success, response) 
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.needReform!=record.get('needReform'))
						{
							record.set('needReform', 2);
							record.commit();
						}
					}
				}
			});
		}*/
	},
	setPerson: function()
	{
		var record = Ext.getCmp('RegistryViewWindow').DataGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}
		var Evn_id = record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное 
		var person_id = record.get('Person_id');
		// для теста 
		//person_id  = 518413; // ОБУХОВА	ТАМАРА	ЕРМОЛАЕВНА
		if (person_id>0)
		{
			var of = 'swRegistryDataPersonEditWindow';
			var params = {action: 'edit', Evn_id:Evn_id, Person_id: person_id, Server_id: 0}; //, Person_id: this.Person_id, Server_id: this.Server_id
			getWnd(of).show(params);
		}
		//getWnd('swRegistryDataPersonEditWindow');
		
	},
	deletePerson: function()
	{
		var grid = this.DataGrid.getGrid();
		if (!grid)
		{
			return false;
		}
		else if (!grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('Person_id')) 
		{
			return false;
		}
		var Person_id = grid.getSelectionModel().getSelected().get('Person_id');
		var Evn_id = grid.getSelectionModel().getSelected().get('Evn_id');
		sw.swMsg.show(
		{
			icon: Ext.MessageBox.QUESTION,
			msg: 'Вы хотите удалить данные для реестра по человеку?',
			title: 'Подтверждение',
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj)
			{
				if ('yes' == buttonId)
				{
					Ext.Ajax.request(
					{
						url: '/?c=Registry&m=deletePersonEdit',
						params: {object:'RegistryDataLgot', Person_id: Person_id, Evn_id: Evn_id},
						failure: function(response, options)
						{
							Ext.Msg.alert('Ошибка', 'При удалении произошла ошибка!');
						},
						success: function(response, action)
						{
							if (response.responseText)
							{
								var answer = Ext.util.JSON.decode(response.responseText);
								if (!answer.success)
								{
									if (answer.Error_Code)
									{
										Ext.Msg.alert('Ошибка #'+answer.Error_Code, answer.Error_Message);
									}
									else
										if (!answer.Error_Msg) // если не автоматически выводится
										{
											Ext.Msg.alert('Ошибка', 'Удаление невозможно!');
										}
								}
								else 
								{
									Ext.Msg.alert('Сообщение', 'Данные успешно удалены!');
								}
							}
							else
							{
								Ext.Msg.alert('Ошибка', 'Ошибка при удалении! Отсутствует ответ сервера.');
							}
						}
					});
				}
			}
		});
		
	},
	
	doPersonUnion: function () {
		// Формируем список двойников, основная запись Person2_id
		
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			scope : this,
			fn: function(buttonId) 
			{
				if ( buttonId == 'yes' )
				{
					var record = this.PersonErrorGrid.getGrid().getSelectionModel().getSelected();
					if (record && record.get('isNoEdit')==2)
					{
						return false;
					}
					var records = [];
					records[0] = {IsMainRec:1, Person_id:record.get('Person2_id')};
					records[1] = {IsMainRec:0, Person_id:record.get('Person_id')};
					
					if (records.length<2) {
						sw.swMsg.alert('Внимание','Для объединения должны быть хотя бы 2 записи!');
						return false;
					}
					this.getLoadMask().show();
					
					controlStoreRequest = Ext.Ajax.request({
						url: '/?c=Registry&m=doPersonUnionFromRegistry',
						success: function(result){
							if ( result.responseText.length > 0 ) {
								var resp_obj = Ext.util.JSON.decode(result.responseText);
								if (resp_obj.success == true) {
									// Успешно, помечаем запись чтобы они видели что поправили
									this.PersonErrorGrid.setNoEdit(record);
								}
							}
							this.getLoadMask().hide();
						}.createDelegate(this),
						params: {
								'Records': Ext.util.JSON.encode(records),
								'fromRegistry': 1
							},
						failure: function(result){
							this.getLoadMask().hide();
						}.createDelegate(this),
						method: 'POST',
						timeout: 120000
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Хотите объединить выбранных людей?',
			title: 'Вопрос'
		});
	},
	/** Открытие информационного окна с технической информацией
	 *
	 */
	openInfoForm: function (object) {
		var record = object.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана ни одна запись с ошибкой ТФОМС!');
			return false;
		}
		var msg = 
			'Имя поля: '+((record.get('RegistryErrorTFOMS_FieldName'))?record.get('RegistryErrorTFOMS_FieldName'):'')+'<br>'+
			'Базовый элемент: '+((record.get('RegistryErrorTFOMS_BaseElement'))?record.get('RegistryErrorTFOMS_BaseElement'):'')+'<br>'+
			'Комментарий: '+((record.get('RegistryErrorTFOMS_Comment'))?record.get('RegistryErrorTFOMS_Comment'):'')+'<br>';
			;
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			closable:false,
			scope : Ext.getCmp('RegistryViewWindow'),
			fn: function(buttonId) {
				//
			},
			icon: Ext.Msg.INFO,
			msg: msg,
			title: 'Технические подробности'
		});
	},
	getParamsForEvnClass: function(record) {
		var config = new Object();
		
		// по умолчанию полка.
		config.open_form = 'swEvnPLEditWindow';
		config.key = 'EvnPL_id';
				
		if (!record) {
			return config;
		}
		
		var evnclass_id = record.get('EvnClass_id');

		switch (evnclass_id)
		{
			case 13:
				config.open_form = 'swEvnPLStomEditWindow';
				config.key = 'EvnPLStom_id';
				break;
			case 32:
				config.open_form = 'swEvnPSEditWindow';
				config.key = 'EvnPS_id';
				break;
			case 35:
				config.open_form = 'EvnPLWOWEditWindow';
				config.key = 'EvnPLWOW_id';
				break;
			case 8:
				config.open_form = 'swEvnPLDispDopEditWindow';
				config.key = 'EvnPLDispDop_id';
				break;
			case 9:
				config.open_form = 'swEvnPLDispOrpEditWindow';
				config.key = 'EvnPLDispOrp_id';
				break;

			case 104:
				config.key = 'EvnPLDispTeenInspection_id';

				switch ( record.get('DispClass_id') ) {
					case 6:
						config.open_form = 'swEvnPLDispTeenInspectionEditWindow';
					break;

					case 9:
						config.open_form = 'swEvnPLDispTeenInspectionPredEditWindow';
					break;

					case 10:
						config.open_form = 'swEvnPLDispTeenInspectionProfEditWindow';
					break;
				}
			break;
		}
		
		return config;
	},
	openForm: function (object, oparams, frm)
	{
		var form = this;
		// Взависимости от типа выбираем форму которую будем открывать 
		// Типы лежат в RegistryType
		var record = object.getGrid().getSelectionModel().getSelected();

		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}
		var RegistryType_id = this.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');

		var type = record.get('RegistryType_id');
		if (!type)
			type = this.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		
		if (object.id == this.id+'Error' || object.id == this.id+'Data') // Если это с грида "Ошибки данных" или Данные
		{
			if (!frm)
				frm = record.get('RegistryErrorType_Form');
			switch (frm)
			{
				case 'Person':
				case 'OpenPerson':
					type = 108;
					break;
			}
		}
		if (object.id == this.id+'NoPolis') // Если это с грида "Нет полиса"
		{
			type = 108;
		}
		if (frm=='OpenPerson') {
			type = 108;
		}
		var id = record.get('Evn_rid') || record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное 
		var isNoEdit = record.get('isNoEdit'); // Если не редактируется 
		var Person_id = record.get('Person_id');
		var Server_id = record.get('Server_id');
		var PersonEvn_id = null;
		var usePersonEvn = null;
		var MedPersonal_id = 0;
		var DispClass_id;
		if (isNoEdit==2)
		{
			return false;
		}
		if (record.get('PersonEvn_id'))
		{
			PersonEvn_id = record.get('PersonEvn_id');
			usePersonEvn = true;
		}
		var open_form = '';
		var key = '';
		
		switch (type)
		{
			case 1:
			case 14:
				open_form = 'swEvnPSEditWindow';
				key = 'EvnPS_id';
				break;
			case 2:
				var config = form.getParamsForEvnClass(record);
				
				open_form = config.open_form;
				key = config.key;
				
				// для CmpCallCard нет EvnClass, определяем открываемую форму по типу реестра.
				if (RegistryType_id == '6') {
					open_form = 'swCmpCallCardNewCloseCardWindow';
					key = 'CmpCloseCard_id';
				}
				
				if (!id) {
					open_form = 'swLpuPassportEditWindow';
					key = 'Lpu_id';
				}
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
				break;
			case 11:
				open_form = 'swEvnPLDispProfEditWindow';
				key = 'EvnPLDispProf_id';
				break;
			case 12:
				var config = form.getParamsForEvnClass(record);
				
				open_form = config.open_form;
				key = config.key;
				break;
			case 108:
				open_form = 'swPersonEditWindow';
				key = 'Person_id';
				id = record.get('Person_id');
				break;
			default:
				Ext.Msg.alert('Ошибка', 'Вызываемая форма неизвестна!');
				return false;
				break;
		}
		
		var params = {action: 'edit', Person_id: Person_id, Server_id: Server_id, PersonEvn_id: PersonEvn_id, usePersonEvn: usePersonEvn}; //, Person_id: this.Person_id, Server_id: this.Server_id
		params = Ext.apply(params || {}, oparams || {});
		if (id)
			params[key] = id;
		// на сохранение формы мы проставляем изменения по этой записи реестра
		if (object.id != this.id+'NoPolis')
		{
			params['callback'] = function () {this.setNeedReform(record);}.createDelegate(this);
		}
		if ( !Ext.isEmpty(DispClass_id) ) {
			params.DispClass_id = DispClass_id;
		}
		if (MedPersonal_id>0)
		{
			params['MedPersonal_id'] = MedPersonal_id;
		}
		if (open_form == 'swCmpCallCardNewCloseCardWindow') { // карты вызова
			params.formParams = Ext.apply(params);
		}
		getWnd(open_form).show(params);
	},
	listeners: 
	{
		beforeshow: function()
		{
			this.findById('regvRightPanel').setVisible(false);
		}
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите... '});
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swRegistryViewWindow.superclass.show.apply(this, arguments);
		this.getLoadMask().show();
		// При открытии если Root Node уже открыта - перечитываем
		var root = this.Tree.getRootNode();
		if (root)
		{
			if (root.isExpanded()) 
			{
				this.Tree.getLoader().load(root);
				// Дальше отрабатывает логика на load
			}
		}
		this.maximize();
		if (isLpuAdmin() || isSuperAdmin()) {
			this.getReplicationInfo();
		}
		// Также грид "Счета" сбрасываем
		this.AccountGrid.removeAll();
		//this.onIsRunQueue();
		this.getLoadMask().hide();
	},
	deleteRegistryDouble: function(mode) {
		var grid = this.DoubleVizitGrid.ViewGridPanel,
			rec = grid.getSelectionModel().getSelected(),
			msg = 'Вы действительно хотите удалить';
		if( !rec && mode == 'current' ) return false;
		
		switch(mode) {
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
			title: 'Внимание!',
			msg: msg,
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask('Удаление...').show();
					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryDouble',
						params: {
							mode: mode,
							Evn_id: rec.get('Evn_id') || null,
							Registry_id: this.DoubleVizitGrid.getParam('Registry_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if( success ) {
								grid.getStore().remove(rec);
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},
	
	printRegistryError: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		if ( !Registry_id )
			return false;
		var id_salt = Math.random();
		var win_id = 'print_registryerror' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=Registry&m=printRegistryError&Registry_id=' + Registry_id, win_id);
	},
	/**
	 * Групповое удаление/восстановление записей из реестра
	 */
	deleteGroupRegistryData: function(grid, act)
	{
		
		var showMessage = false;
		var record = grid.getGrid().getSelectionModel().getSelected();
		var records = grid.getGrid().getSelectionModel().selections.items;
		var reestr = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var RegistryType_id = reestr.get('RegistryType_id');
		var Registry_id = reestr.get('Registry_id');
		
		var form = this;
		
		var params = {
			RegistryType_id: RegistryType_id,
			Registry_id : Registry_id,
			RegistryData_deleted: null,
			evn_ids : null
		};
		
		
		if (!record && !reestr)
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.<br/>');
			return false;
		}

		var evn_ids_delete = [],
			evn_ids_recovery = [],
			count_delete_evn = 0,
			count_recovery_evn = 0;
		
		for(var rec in records){
			if(typeof(records[rec].data) == 'object'){
				if(records[rec].data.RegistryData_deleted == 1 || records[rec].data.RegistryData_deleted == ''){
					count_delete_evn++;
					evn_ids_delete.push(records[rec].data.Evn_id)
				}
				else if(records[rec].data.RegistryData_deleted == 2){
					count_recovery_evn++;
					evn_ids_recovery.push(records[rec].data.Evn_id)
				}
			}
		}
		var msg = '';
		if(records){
			//если выделена одна запись
			if(Object.keys(records).length == 1 && act =='delete'){
				if(record.data.RegistryData_deleted == 2){
					act = 'unDelete';
				}
			}
			
			params.Filter = null;
			
			if(act == 'deleteAllSelected' || act == 'delete'){
				if(count_delete_evn == 0){
					Ext.Msg.alert('Сообщение', 'Нет записей нуждающихся в удалении или записи уже помечены на удаление');
					return;
				}
				params.Type_select = 0;
				params.RegistryData_deleted = 1;
				params.Evn_ids = Ext.util.JSON.encode(evn_ids_delete);
				
				msg = '<b>Вы действительно хотите удалить выбранные записи <br/>из реестра?</b><br/><br/>'+
					'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Выбранные записи ' +
					'пометятся как удаленные и будут удалены из реестра при выгрузке (отправке) реестра. ' +
					'Сумма реестра будет пересчитана также при выгрузке (отправке) реестра </span>';
			}
			else if(act == 'unDeleteAllSelected' || act == 'unDelete'){
				if(count_recovery_evn == 0){
					Ext.Msg.alert('Сообщение', 'Нет записей нуждающихся в восстановлении');
					return;
				}
				params.RegistryData_deleted = 2;
				params.Type_select = 0;
				params.Evn_ids = Ext.util.JSON.encode(evn_ids_recovery);
				
				msg = '<b>Хотите восстановить помеченные на удаление записи?</b><br/><br/>';
			}
			showMessage = true;
			
		}
		
		//delete params.Filter;
		//log('PARAMS', params);
		
		if(showMessage && msg){
			sw.swMsg.show(
				{
					buttons: Ext.Msg.YESNO,
					scope: form,
					fn: function(buttonId)
					{
						if ( buttonId == 'yes' )
						{
							Ext.Ajax.request(
								{
									url: '/?c=Registry&m=deleteRegistryData',
									params: params,
									callback: function(options, success, response)
									{
										if (success)
										{
											// Перечитываем грид, чтобы обновить данные по счетам
											grid.loadData();
										}
									}
								});
						}
					},
					icon: Ext.Msg.QUESTION,
					msg: msg,
					title: 'Вопрос'
				});
		}
	},
	/* конструктор */
	initComponent: function()  {
		var form = this;

		this.TreeToolbar = new Ext.Toolbar({
			id : form.id+'Toolbar',
			items: [{
				xtype: "tbseparator"
			}]
		});
		
		this.Tree = new Ext.tree.TreePanel({
			id: form.id+'RegistryTree',
			animate: false,
			autoScroll: true,
			split: true,
			region: 'west',
			root: {
				id: 'root',
				nodeType: 'async',
				text: 'Реестры',
				expanded: true
			},
			rootVisible: false,
			tbar: form.TreeToolbar,
			width: 250,
			loader: new Ext.tree.TreeLoader({
				dataUrl: '/?c=Registry&m=loadRegistryTree',
				listeners: {
					beforeload: function (loader, node) {
						loader.baseParams.level = node.getDepth();
					},
					load: function (loader, node) {
						// Если это родитель, то накладываем фокус на дерево взависимости от настроек
						if ( node.id == 'root' ) {
							if ((node.getOwnerTree().rootVisible == false) && (node.hasChildNodes() == true)) {
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
			})
		});

		// Выбор ноды click-ом
		this.Tree.on('click', function(node, e) {
			form.onTreeClick(node, e);
		});

		this.AccountGrid = new sw.Promed.ViewFrame({
			id: form.id+'Account',
			region: 'north',
			height: 203,
			title:'Счет',
			object: 'Registry',
			editformclassname: 'swRegistryEditWindow',
			dataUrl: '/?c=Registry&m=loadRegistry',
			autoLoadData: false,
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden: !isSuperAdmin()},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_RecordPaidCount', type: 'int', hidden: true},
				{name: 'ReformTime',hidden: true},
				{name: 'Registry_KdCount', type: 'int', hidden: true},
				{name: 'Registry_KdPaidCount', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'RegistryErrorCom_IsData', type: 'int', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryPerson_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPolis_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPay_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPay_Count', type: 'int', hidden: true},
				{name: 'RegistryNoPay_UKLSum', type: 'float', hidden: true},
				{name: 'RegistryCheckStatus_id', hidden: true},
				{name: 'issetDouble', hidden: true},
				{name: 'Registry_Num', header: 'Номер счета', id: 'autoexpand'},
				{name: 'Registry_accDate', type:'date', header: 'Дата счета', width: 80},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 100},
				{name: 'Registry_Sum', type:'money', header: 'Сумма к оплате', width: 110},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 150},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
				{name: 'RegistryStacType_Name', header: 'Тип стац.', width: 140},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110},
				{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200},
				{name: 'KatNasel_SysNick',hidden: true}
			],
			actions: [
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete', url: '/?c=Registry&m=deleteRegistry', msg: '<b style="color:maroon;">В большинстве случаев гораздо удобнее переформировать реестр, <br/>предварительно исправив ошибки, чем удалить и создать новый.</b> <br/>Вы действительно хотите удалить реестр?'},
				{
					name:'action_print',
					hidden: false,
					handler: function() {
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

						if ( typeof record != 'object' && Ext.isEmpty(record.get('Registry_id')) ) {
							sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
							return false;
						}

						var id_salt = Math.random();
						var win_id = 'print_schet' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=Registry&m=printRegistry&Registry_id=' + record.get('Registry_id'), win_id);
					}, 
					text: 'Печать счета'
				}
			],
			afterSaveEditForm: function(RegistryQueue_id, records) {
				var r = records.RegistryQueue_Position;
				form.onIsRunQueue(r);
			},
			onLoadData: function() {
				if  ( this.getAction('action_new') ) {
					this.getAction('action_new').setDisabled(this.getCount()==0);
				}
			},
			onRowSelect: function(sm, index, record) {
				if ( this.getCount() > 0 ) {
					if ( record.get('RegistryStatus_id') == 3 ) {
						var sel_node = form.Tree.getSelectionModel().getSelectedNode();
						// Только если выбран раздел "в работе"
						if ( sel_node.attributes.object_value && sel_node.attributes.object_value == 3 ) {
							this.ViewActions.action_new.initialConfig.menu.items.items[0].setVisible( record.get('issetDouble') == 0 );
						}
					}

					var Registry_id = record.get('Registry_id');
					var RegistryType_id = record.get('RegistryType_id');
					var RegistryStatus_id = record.get('RegistryStatus_id');

					// Убрать кнопку Печать счета иногородним в полке и стаце (refs #1595)
					if ( record.get('KatNasel_id') == 2 ) {
						this.setActionDisabled('action_print', true);
					}
					else {
						this.setActionDisabled('action_print', RegistryStatus_id != 4);
					}
					
					form.onRegistrySelect(Registry_id, RegistryType_id,  false);

					this.setActionDisabled('action_edit', (record.get('RegistryStatus_id')!=3)); // #61531
					this.setActionDisabled('action_delete', (record.get('RegistryStatus_id')!=3)); // #61531
					this.setActionDisabled('action_view', false);

					if ( record.get('RegistryStatus_id') != 6 ) {
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(false);
					}

					// В прогрессе
					if ( record.get('Registry_IsProgress') == 1 ) {
						this.setActionDisabled('action_edit', true);
						this.setActionDisabled('action_delete', true);
						this.setActionDisabled('action_view', true);

						if ( record.get('RegistryStatus_id') != 6 ) {
							Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(true);
						}
					}

					if ( record.get('RegistryStatus_id') == 4 ) {
						// разрешить-запретить снятие активности
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(!(record.get('Registry_IsActive')==2));
					}
					
					// Дисаблим акшены по статусу отправки 
					// Если полка или стац
					// Если к оплате 
					if (record.get('RegistryStatus_id')=='2' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setDisabled((record.get('RegistryCheckStatus_id').inlist(['1','2']) && !isAdmin));
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setDisabled((record.get('RegistryCheckStatus_id').inlist(['1','2']) && !isAdmin));
					}
					// Если в работе
					if (record.get('RegistryStatus_id')=='3' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setDisabled((record.get('RegistryCheckStatus_id').inlist(['1','2']) && !isAdmin));
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setDisabled((record.get('RegistryCheckStatus_id').inlist(['1','2']) && !isAdmin));
					}
					
					// Для папки с удаленными реестрами дизаблим контролы
					var deletedRegistriesSelected = this.deletedRegistriesSelected;

					if ( deletedRegistriesSelected ) {
						this.setActionDisabled('action_add',true);
						this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete',true);
						this.setActionDisabled('action_view',true);
					}

					form.RegistryPanel.show();

					var sparams = {
						Registry_Num: record.get('Registry_Num'),
						Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'),
						Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'),
						Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'),
						ReformTime:record.get('ReformTime'),
						Registry_Count: '<div style="padding:2px;font-size: 12px;">Количество записей в реестре: '+record.get('Registry_Count')+'</div>',
						Registry_Sum: sw.Promed.Format.rurMoney(record.get('Registry_Sum'))
					};

					if ( RegistryType_id == 1 ) {
						sparams['Registry_Count'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество госпитализаций, факт: '+record.get('Registry_Count')+'</div>';
						sparams['Registry_RecordPaidCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество госпитализаций, к оплате: '+record.get('Registry_RecordPaidCount')+'</div>';
						sparams['Registry_KdCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество койкодней, факт: '+record.get('Registry_KdCount')+'</div>';
						sparams['Registry_KdPaidCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество койкодней, к оплате: '+record.get('Registry_KdPaidCount')+'</div>';
					}

					sparams['RegistryNoPay_UKLSum'] = '<div style="padding:2px;font-size: 12px;color:maroon;">Сумма без оплаты: '+sw.Promed.Format.rurMoney(record.get('RegistryNoPay_UKLSum'))+'</div>';
					sparams['RegistryNoPay_Count'] = '<div style="padding:2px;font-size: 12px;color:maroon;">Количество записей без оплаты: '+record.get('RegistryNoPay_Count')+'</div>';

					form.RegistryTpl.overwrite(form.RegistryPanel.body, sparams);
				}
				else {
					this.setActionDisabled('action_print',true);

					switch ( form.DataTab.getActiveTab().id ) {
						case 'tab_registry':
							form.RegistryPanel.hide();
							break;

						case 'tab_data':
							form.DataGrid.removeAll(true);
							break;

						case 'tab_commonerr':
							form.ErrorComGrid.removeAll(true);
							break;

						case 'tab_dataerr':
							form.ErrorGrid.removeAll(true);
							break;

						case 'tab_datanopolis':
							form.NoPolisGrid.removeAll(true);
							break;

						case 'tab_datanopay':
							form.NoPayGrid.removeAll(true);
							break;

						case 'tab_datapersonerr':
							form.PersonErrorGrid.removeAll(true);
							break;

						case 'tab_datatfomserr':
							form.TFOMSErrorGrid.removeAll(true);
							break;

						case 'tab_datavizitdouble':
							form.DoubleVizitGrid.removeAll(true);
							break;
					}
				}
				
				// информируем о данных на вкладках
				form.DataTab.getItem('tab_commonerr').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_dataerr').setIconClass((record.get('RegistryError_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datapersonerr').setIconClass((record.get('RegistryPerson_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopolis').setIconClass((record.get('RegistryNoPolis_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopay').setIconClass((record.get('RegistryNoPay_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datavizitdouble').setIconClass((record.get('issetDouble')==1)?'usluga-notok16':'good');

				if(record.get('RegistryType_id')!=2) {
					form.DataTab.hideTabStripItem('tab_datavizitdouble');
				}else{
					form.DataTab.unhideTabStripItem('tab_datavizitdouble');
				}

				form.DataTab.syncSize();
			}
		});

		this.AccountGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if (row.get('Registry_IsActive')==2)
					cls = cls+'x-grid-rowselect ';
				if (row.get('Registry_IsProgress')==1)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 

			return cls;
			}
		});

		var RegTplMark = [
			'<div style="padding:2px;font-size: 12px;font-weight:bold;">Реестр № {Registry_Num}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата формирования: {Registry_accDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата переформирования реестра: {ReformTime}</div>'+
			'<div style="padding:2px;font-size: 12px;">Сумма к оплате: {Registry_Sum}</div>'+
			'{Registry_Count}'+
			'{Registry_RecordPaidCount}'+
			'{Registry_KdCount}'+
			'{Registry_KdPaidCount}'+
			'{RegistryNoPay_UKLSum}'+
			'{RegistryNoPay_Count}'
		];
		this.RegistryTpl = new Ext.Template(RegTplMark);
		
		this.RegistryPanel = new Ext.Panel({
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

		this.DataGridSearch = function() {
			var filtersForm = form.RegistryDataFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				form.DataGrid.loadData({
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
		}
		
		// Кнопка "Поиск"
		var rvwDGBtnSearch = new Ext.Button({
			tooltip: BTN_FRMSEARCH_TIP,
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png',
			iconCls : 'x-btn-text',
			disabled: false,
			tabIndex: form.firstTabIndex++,
			handler: function()  {
				form.DataGridSearch();
			}
		});

		this.DataGridReset = function(){
			var filtersForm = form.RegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.DataGrid.removeAll(true);
			form.DataGridSearch();
		};

		// Кнопка Сброс
		var rvwDGBtnReset = new Ext.Button({
			tooltip: BTN_FRMSEARCH_TIP,
			text: BTN_FRMRESET,
			icon: 'img/icons/reset16.png',
			iconCls : 'x-btn-text',
			disabled: false,
			style: 'margin-left: 4px;',
			tabIndex: form.firstTabIndex++,
			handler: function() {
				form.DataGridReset();
			}
		});
		
		this.RegistryDataFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%; background:#DFE8F6; padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			id: 'RegistryDataFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e)  {
					form.DataGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvwDGPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvwDGPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 60,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvwDGPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					hidden: !isSuperAdmin(),
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						id: 'rvwDGPolis_Num',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 65,
					items: [{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding: 4px;background:#DFE8F6;',
					items: [
						rvwDGBtnSearch
					]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding: 4px;background:#DFE8F6;',
					items: [
						rvwDGBtnReset
					]
				}]
			}]
		});
		
		// Данные реестра 
		this.DataGrid = new sw.Promed.ViewFrame({
			id: form.id+'Data',
			title: 'Реестр ОМС',
			object: 'RegistryData',
			region: 'center',
			dataUrl: '/?c=Registry&m=loadRegistryData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			selectionModel: 'multiselect',
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'ИД случая', key: true, hidden: false},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'IsRDL', type: 'int', hidden:true},
				{name: 'needReform', type: 'int', hidden:true},
				{name: 'isNoEdit', type: 'int', hidden:true},
				{name: 'Err_Count', hidden:true},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'EvnPL_NumCard', header: '№ талона', width: 60},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'RegistryData_Tariff', type: 'money', header: 'Тариф', width: 70},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90}
			],
			listeners: {
				render: function(grid) {
					
					var action_delete_all_records = {
						name:'action_delete_all_records',
						text:'Удалить отмеченные случаи',
						icon: 'img/icons/delete16.png',
						handler: this.deleteGroupRegistryData.createDelegate(this, [grid, 'deleteAllSelected'])
					};
					if(!grid.getAction('action_delete_all_records')){
						grid.ViewActions[action_delete_all_records.name] = new Ext.Action(action_delete_all_records);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_delete_all_records.name]);
					}
					
					var action_undelete_all_records = {
						name:'action_undelete_all_records',
						text:'Восстановить отмеченные случаи',
						icon: 'img/icons/refresh16.png',
						handler: this.deleteGroupRegistryData.createDelegate(this, [grid, 'unDeleteAllSelected'])
					};
					if(!form.DataGrid.getAction('action_undelete_all_records')){
						form.DataGrid.ViewActions[action_undelete_all_records.name] = new Ext.Action(action_undelete_all_records);
						form.DataGrid.ViewContextMenu.add(form.DataGrid.ViewActions[action_undelete_all_records.name]);
					}
					
				}.createDelegate(this)
				
			},
			actions: [
				//{name:'action_setperson', disabled: !isSuperAdmin(), hidden: !isSuperAdmin(), tooltip: 'Редактировать данные человека в реестре', text: '<b>Редактировать данные человека</b>', handler: function() { form.setPerson(); }},
				//{name:'action_delperson', disabled: !isSuperAdmin(), hidden: !isSuperAdmin(), tooltip: 'Удалить созданные данные человека в реестре', text: 'Удалить данные человека', handler: function() { form.deletePerson(); }},
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() { form.openForm(form.DataGrid, {}); }},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: false, handler: function() { this.deleteRegistryData(); }.createDelegate(this)},
				{name:'action_openperson', hidden: !isSuperAdmin(), icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() { form.openForm(form.DataGrid, {}, 'OpenPerson'); }}
			],
			callbackPersonEdit: function(person, record) {
				if ( this.selectedRecord ) {
					record = this.selectedRecord;
				}

				if ( !record ) {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}

				if ( !record ) {
					return false;
				}

				form.setNeedReform(record);
			},
			onLoadData: function() {
				if ( isSuperAdmin() ) {
					this.setActionDisabled('action_setperson',(this.getCount()==0));
					this.setActionDisabled('action_delperson',true);
				}
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 3));
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
			},
			onDblClick: function() {
				form.openForm(form.DataGrid, {});
			},
			onEnter: function() {
				form.openForm(form.DataGrid, {});
			},
			onRowSelect: function(sm, rowIdx, record) {
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				var records = form.DataGrid.getGrid().getSelectionModel().selections.items;
				var disabled = false;
				records.forEach(function(rec){
					if(rec.get('RegistryData_deleted')!=2 || RegistryStatus_id!=3){
						disabled = true;
					}
				});
				form.DataGrid.setActionDisabled('action_undelete_all_records',disabled);
				
				if ( isSuperAdmin() ) {
					this.setActionDisabled('action_setperson',(this.getCount()==0));
					this.setActionDisabled('action_delperson',(record.get('IsRDL')==0));
				}

				// Меняем текст акшена удаления в зависимости от данных
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');
			},
			onRowDeSelect: function(sm,rowIdx,record)
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				var records = this.getGrid().getSelectionModel().selections.items;
				var disabled = false;
				records.forEach(function(rec){
					if(rec.get('RegistryData_deleted')!=2 || RegistryStatus_id!=3){
						disabled = true;
					}
				});
				this.setActionDisabled('action_undelete_all_records',disabled);
				
			}
		});
		
		this.DataGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ((row.get('IsRDL')>0) && isSuperAdmin())
					cls = cls+'x-grid-rowblue ';
				if (row.get('Err_Count') > 0)
					cls = cls+'x-grid-rowred ';
				if (row.get('needReform') == 2)
					cls = cls+'x-grid-rowselect ';
				if (row.get('isNoEdit') == 2)
					cls = cls+'x-grid-rowgray ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 

				return cls;
			},
			listeners: {
				rowupdated: function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});
		
		// Общие ошибки
		this.ErrorComGrid = new sw.Promed.ViewFrame({
			id: form.id+'ErrorCom',
			title:'Общие ошибки',
			object: 'RegistryErrorCom',
			dataUrl: '/?c=Registry&m=loadRegistryErrorCom',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			stringfields: [
				{name: 'RegistryErrorType_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'RegistryErrorClass_id', type: 'int', hidden: true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', id: 'autoexpand', header: 'Наименование'},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', disabled: true},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function() {
				//
			}
		});

		this.ErrorComGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
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

		this.ErrorGridSearch = function(){
			var filtersForm = form.RegistryErrorFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				form.ErrorGrid.loadData({
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
		
		var rvwREBtnSearch = new Ext.Button({
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwREBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png', 
			iconCls : 'x-btn-text',
			disabled: false,
			tabIndex: form.firstTabIndex++,
			handler: function() {
				form.ErrorGridSearch();
			}
		});
		
		this.RegistryErrorFiltersPanel = new Ext.form.FormPanel({
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			id: 'RegistryErrorFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.ErrorGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {
					bodyStyle:'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'
				},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvwREPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvwREPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 60,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvwREPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				}]
			}, {
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {
					bodyStyle:'padding-left: 4px; background:#DFE8F6;'
				},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 50,
					items: [{
						anchor: '100%',
						fieldLabel: 'Ошибка',
						id: 'rvwRERegistryErrorType_id',
						name: 'RegistryErrorType_id',
						xtype: 'swregistryerrortypecombo',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 65,
					items: [{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						hiddenName: 'MedPersonal_id',
						lastQuery: '',
						listWidth: 650,
						editable: true,
						tabIndex: form.firstTabIndex++,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					columnWidth: .1,
					items: [
						rvwREBtnSearch
					]
				}]
			}]
		});
		
		// Ошибки данных 
		this.ErrorGrid = new sw.Promed.ViewFrame({
			id: form.id+'Error',
			title:'Ошибки данных',
			object: 'RegistryError',
			dataUrl: '/?c=Registry&m=loadRegistryError',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'RegistryError_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', header: 'ИД случая', hidden: false},
				{name: 'Person_id', header:'Person_id', type: 'int', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Form', hidden:true},
				{name: 'isNoEdit', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'LpuSection_id', type: 'int', hidden:true},
				{name: 'LpuUnit_id', type: 'int', hidden:true},
				{name: 'MedStaffFact_id', type: 'int', hidden:true},
				{name: 'MedPersonal_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryError_Desc', header: 'Комментарий', width: 250},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
					{ field: 'RegistryErrorType_Name', tpl: '{RegistryErrorType_Name}' }
				])
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {form.openForm(form.ErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true },
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryError(); }},
				{name:'-'},
				{name:'action_openevn', visible: !isAdmin, tooltip: 'Открыть талон', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть талон', handler: function() {form.openForm(form.ErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.ErrorGrid, {}, 'OpenPerson');}}
				
			],
			callbackPersonEdit: function(person, record) {
				if (this.selectedRecord) {
					record = this.selectedRecord;
				}
				if (!record) {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if (!record) {
					return false;
				}
				form.setNeedReform(record);
			},
			onLoadData: function() {
				//
			}
		});

		this.ErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';

				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-row ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (row.get('isNoEdit') == 2)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 

				return cls;
			}
		});
		
		// Незастрахованные
		this.NoPolisGrid = new sw.Promed.ViewFrame({
			id: form.id+'NoPolis',
			title:'Незастрахованные',
			object: 'RegistryNoPolis',
			dataUrl: '/?c=Registry&m=loadRegistryNoPolis',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'LpuSection_Name', header: 'Отделение', width: 150}, // Не работает
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', handler: function() {form.openForm(form.NoPolisGrid, {}, 'OpenPerson');}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function() {
				//
			}
		});

		// Случаи без оплаты
		this.NoPayGrid = new sw.Promed.ViewFrame( {
			id: form.id+'NoPay',
			title:'Случаи без оплаты',
			object: 'RegistryNoPay',
			dataUrl: '/?c=Registry&m=loadRegistryNoPay',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 150},
				{name: 'RegistryNoPay_KdFact', header: 'Факт. к-дни', width: 70},
				{name: 'RegistryNoPay_KdPlan', header: 'Норм. к-дни', width: 70},
				{name: 'RegistryNoPay_Tariff', type: 'money', header: 'Тариф', width: 70},
				{name: 'RegistryNoPay_UKLSum', type: 'money', header: 'Сумма', width: 90}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', handler: function() {form.openForm(form.NoPayGrid, {}, 'OpenPerson');}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function() {
				//
			}
		});
		
		// Ошибки персданных
		this.PersonErrorGrid = new sw.Promed.ViewFrame({
			id: form.id+'PersonError',
			title: 'Ошибки персональных данных',
			object: 'RegistryPerson',
			editformclassname: 'swPersonEditWindow',
			dataUrl: '/?c=Registry&m=loadRegistryPerson',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			stringfields: [
				{name: 'MaxEvnPerson_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'Person_id', isparams: true, type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Person2_id', isparams: true, type: 'int', header: 'Person2_id', hidden:!isSuperAdmin()},
				{name: 'isNoEdit', type: 'int', hidden:true},
				{name: 'Person_SurName', id: 'autoexpand', header: 'Фамилия'},
				{name: 'Person_FirName', header: 'Имя', width: 180},
				{name: 'Person_SecName', header: 'Имя', width: 180},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis',  header: 'Серия, № полиса', width: 130},
				{name: 'Person_PolisDate',  header: 'Период действия полиса', width: 150},
				{name: 'Person_EvnDate',  header: 'Период лечения', width: 150},
				{name: 'Person_OrgSmo',  header: 'СМО', width: 180}, 
				{name: 'isNoEdit', hidden:true} 
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: 'Объединить', handler: function() {form.doPersonUnion(form.PersonErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onRowSelect: function (sm,index,record) {
				this.setActionDisabled('action_edit',((this.getCount()==0) || (record.get('isNoEdit')==2)));
			},
			callbackPersonEdit: function(person, record) {
				if ( this.selectedRecord ) {
					record = this.selectedRecord;
				}
				if ( !record ) {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if ( !record ) {
					return false;
				}
				form.setNeedReform(record);
			},
			setNoEdit: function (record) {
				record.set('isNoEdit', 2);
				record.commit();
			},
			onLoadData: function() {
				//
			}
		});
		
		this.PersonErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';

				if (row.get('isNoEdit') == 2)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 

				return cls;
			}
		});
		
		// Ошибки ФЛК
		this.TFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: form.id+'TFOMSError',
			title:'Ошибки ФЛК',
			object: 'RegistryErrorTFOMS',
			dataUrl: '/?c=Registry&m=loadRegistryErrorTFOMS',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden:false},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'RegistryErrorTFOMS_FieldName', hidden:true},
				{name: 'RegistryErrorTFOMS_BaseElement', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryError_FieldName', header: 'Ошибка', width: 250},
				{name: 'RegistryError_Comment', header: 'Описание ошибки', autoexpand: true},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
					{ field: 'RegistryError_Comment', tpl: '{RegistryError_Comment}' }
				])
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true },
				{name:'-', visible: !isAdmin},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть талон', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть талон', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenPerson');}},
				{name:'-', visible: !isAdmin},
				{name:'action_tehinfo', disabled: true, visible: !isAdmin, icon: 'img/icons/info16.png', tooltip: 'Технические подробности', text: 'Технические подробности', handler: function() {Ext.getCmp('RegistryViewWindow').openInfoForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid)}}
				
			],
			callbackPersonEdit: function(person, record) {
				if ( this.selectedRecord ) {
					record = this.selectedRecord;
				}
				if ( !record ) {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if ( !record ) {
					return false;
				}
				//form.setNeedReform(record);
			},
			onLoadData: function() {
				//
			}, 
			onRowSelect: function() {
				if ( this.getCount() > 0 ) {
					this.setActionDisabled('action_openperson',!isAdmin);
					this.setActionDisabled('action_openevn',!isAdmin);
					this.setActionDisabled('action_tehinfo',!isAdmin);
				}
			}
		});

		// Дубли посещений
		this.DoubleVizitGrid = new sw.Promed.ViewFrame({
			id: form.id+'DoubleVizit',
			title:'Дубли посещений',
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
			listeners: {
				render: function(grid) {
					if ( !grid.getAction('action_delete_all') ) {
						var action_delete_all = {
							name:'action_delete_all',
							text:'Удалить все',
							icon: 'img/icons/delete16.png',
							handler: this.deleteRegistryDouble.createDelegate(this, ['all'])
						};
						grid.ViewActions[action_delete_all.name] = new Ext.Action(action_delete_all);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_delete_all.name]);
					}
				}.createDelegate(this)
			},
			actions: [
				{name:'action_add', hidden: true },
				{name:'action_edit', hidden: true/*, handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DoubleVizitGrid, {}, 'OpenPerson');}*/},
				{name:'action_view', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DoubleVizitGrid, {action: 'view'}, 'swEvnPLEditWindow');} },
				{name:'action_delete', handler: this.deleteRegistryDouble.createDelegate(this, ['current']) }
			],
			onLoadData: function() {
				//
			}
		});
		
		this.DataTab = new Ext.TabPanel({
			border: false,
			region: 'center',
			id: form.id + 'DataTab',
			activeTab: 0,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {
				bodyStyle: 'width:100%;'
			},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(tab, panel) {
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

					if ( record ) {
						form.onRegistrySelect(record.get('Registry_id'), record.get('RegistryType_id'), true);
					}
				}
			},
			items: [{
				title: '0. Реестр',
				layout: 'fit',
				id: 'tab_registry',
				frame: true,
				border:false,
				items: [
					form.RegistryPanel
				]
			}, {
				title: '1. Данные',
				layout: 'fit',
				id: 'tab_data',
				//iconCls: 'info16',
				border:false,
				items: [{
					border: false,
					layout:'border',
					region: 'center',
					items: [
						form.RegistryDataFiltersPanel,
						form.DataGrid
					]
				}]
			}, {
				title: '2. Общие ошибки',
				layout: 'fit',
				id: 'tab_commonerr',
				iconCls: 'good',
				border:false,
				items: [
					form.ErrorComGrid
				]
			}, {
				title: '3. Ошибки данных',
				layout: 'fit',
				id: 'tab_dataerr',
				iconCls: 'good',
				border:false,
				items: [{
					border: false,
					layout:'border',
					region: 'center',
					items: [
						form.RegistryErrorFiltersPanel,
						form.ErrorGrid
					]
				}]
			}, {
				title: '4. Незастрахованные',
				layout: 'fit',
				id: 'tab_datanopolis',
				iconCls: 'good',
				border: false,
				items: [
					form.NoPolisGrid
				]
			}, {
				title: '5. Случаи без оплаты',
				layout: 'fit',
				id: 'tab_datanopay',
				iconCls: 'good',
				border: false,
				items: [
					form.NoPayGrid
				]
			}, {
				title: '6. Ошибки перс/данных',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_datapersonerr',
				border: false,
				items: [
					form.PersonErrorGrid
				]
			}, {
				title: '7. Ошибки ФЛК',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_datatfomserr',
				border: false,
				items: [
					form.TFOMSErrorGrid
				]
			}, {
				title: '8. Дубли посещений',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_datavizitdouble',
				border: false,
				items: [
					form.DoubleVizitGrid
				]
			}]
		});

		Ext.apply(this, {
			layout:'border',
			defaults: {
				split: true
			},
			buttons: [{
				hidden: false,
				handler: function() {
					form.getReplicationInfo();
				},
				iconCls: 'ok16',
				text: 'Актуальность данных: (неизвестно)'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					form.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: [
				form.Tree,
				{
					border: false,
					xtype: 'panel',
					region: 'center',
					layout:'border',
					id: 'regvRightPanel',
					defaults: {
						split: true
					},
					items: [
						form.AccountGrid,
						form.DataTab
					]
				}
			]
		});

		sw.Promed.swRegistryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});