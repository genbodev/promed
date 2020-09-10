/**
* swRegistryViewWindow - окно просмотра и редактирования реестров.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @region       Vologda
* @access       public
* @copyright    Copyright (c) 2018 Swan Ltd.
* @author       Stanislav Bykov
* @version      06.11.2018
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
	listeners: {
		beforeshow: function() {
			this.findById('regvRightPanel').setVisible(false);
		}
	},
	maximized: true,
	modal: false,
	resizable: false,
	title: WND_ADMIN_REGISTRYLIST, 
	width: 800,
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
								if (mode == 'all') {
									grid.getStore().removeAll();
								} else if (rec) {
									grid.getStore().remove(rec);
								}
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION
		});
	},
	/* методы */
	constructYearsMenu: function( params ) {
		if( !params ) return false;

		this.AccountGrid.getAction('action_yearfilter').setText('фильтр по году: <b>за ' + (new Date()).getFullYear() + ' год</b>');
		this.AccountGrid.ViewGridPanel.getStore().baseParams['Registry_accYear'] = (new Date()).getFullYear();

		Ext.Ajax.request({
			url: '/?c=Registry&m=getYearsList',
			params: params,
			callback: function(o, s, r) {
				if(s) {
					var reg_years = Ext.util.JSON.decode(r.responseText);
					// сортируем в обратном порядке
					reg_years.sort(function(a, b) {
						if (a['reg_year'] > b['reg_year']) return -1;
						if (a['reg_year'] < b['reg_year']) return 1;
					});
					var grid = this.AccountGrid.ViewGridPanel,
						menuactions = new Ext.menu.Menu(),
						parentAction = grid.getTopToolbar().items.items[12];
					reg_years.push({
						reg_year: 0
					});

					for( i in reg_years ) {
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
							}.createDelegate(act, [parentAction, grid]));
							menuactions.add(act);
						}
					}
					parentAction.menu = menuactions;
					if( new RegExp((new Date()).getFullYear(), 'ig').test(parentAction.menu.items.items[0].text) ) {
						parentAction.menu.items.items[0].setVisible(false);
					}
				}
			}.createDelegate(this)
		});
	},
	/**
	 * Удаляем запись из реестра
	 */
	deleteRegistryData: function(grid, deleteAll) {
		var record = grid.getGrid().getSelectionModel().getSelected();
		var reestr = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record && !reestr)
		{
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.<br/>');
			return false;
		}
		var Evn_id = record.get('Evn_id');
		var Registry_id = reestr.get('Registry_id');
		var RegistryType_id = reestr.get('RegistryType_id');
		var RegistryData_deleted = 1;
		
		if (!Ext.isEmpty(record.get('RegistryData_deleted'))) {
			RegistryData_deleted = record.get('RegistryData_deleted');
		}
		
		if (RegistryData_deleted!=2) {
			var msg = '<b>Вы действительно хотите удалить выбранную запись <br/>из реестра?</b><br/><br/>'+
					 '<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данная запись пометится как удаленная <br/>'+
					 'и будет удалена из реестра при выгрузке (отправке) реестра.<br/>'+
					 'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
		} else {
			var msg = '<b>Хотите восстановить помеченную на удаление запись?</b>';
		}
		
		if (deleteAll) {
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
		
		if (deleteAll) {
			var records = new Array();
			
			grid.getGrid().getStore().each(function(record) {
				if(!Ext.isEmpty(record.get('Evn_id'))) {
					records.push(record.get('Evn_id'));
				}
			});
			
			params.Evn_ids = Ext.util.JSON.encode(records);
		} else {
			params.Evn_id = Evn_id;
		}
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryData',
						params: params,
						callback: function(options, success, response) {
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								// Перечитываем грид, чтобы обновить данные по счетам
								form.DataGrid.loadData();

								if (grid != form.DataGrid) {
									grid.loadData();
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
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var
			Registry_id = record.get('Registry_id'),
			RegistryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: form,
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
	deleteUnionRegistryWithData: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var
			Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					form.getLoadMask('Удаление реестра (с удалением из предварительных)').show();

					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteUnionRegistryWithData',
						params: {
							Registry_id: Registry_id,
							RegistryType_id: registryType_id
						},
						callback: function(options, success, response) {
							form.getLoadMask().hide();

							if ( success ) {
								// Перечитываем грид, чтобы обновить данные по счетам
								form.UnionRegistryGrid.loadData();
							}
						},
						failure: function(){
							form.getLoadMask().hide();
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Внимание! При выполнении действия произойдет удаление реестра '+record.get('OrgSmo_Name')+' №'+record.get('Registry_Num')+' и всех случаев из предварительных реестров, связанных с данной СМО. <b>Продолжить?</b>',
			title: 'Вопрос'
		});
	},
	deleteUnionRegistryData: function(grid) {
		if ( !grid ) {
			return false;
		}

		var record = grid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record || Ext.isEmpty(record.get('Evn_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран случай.');
			return false;
		}

		var
			Evn_id = record.get('Evn_id'),
			Registry_id = record.get('Registry_id'),
			RegistryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					form.getLoadMask('Удаление случая (с удалением из предварительного реестра)').show();

					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteUnionRegistryData',
						params: {
							Evn_id: Evn_id,
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id
						},
						callback: function(options, success, response) {
							form.getLoadMask().hide();

							if ( success ) {
								// Перечитываем грид
								form.UnionDataGrid.loadData();
							}
						},
						failure: function(){
							form.getLoadMask().hide();
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: '<b>Вы действительно хотите удалить выбранную запись из реестра? Обратите внимание:</b><br /> Данная запись удалится из реестра по СМО, отметится на удаление в предварительном реестре и будет удалена при выгрузке предварительного реестра или переводе реестра в оплаченные.',
			title: 'Вопрос'
		});
	},
	exportRegistryToXml: function(mode) {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}
		
		if ( record.get('Registry_IsNeedReform') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Реестр нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр и повторите действие.');
			return false;		
		}

		var params = {
			onHide: Ext.emptyFn,
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id'),
			url:'/?c=Registry&m=exportRegistryToXml'
		};

		if ( mode && mode == 2 ) {
			params.withSign = true;
		}

		getWnd('swRegistryXmlWindow').show(params);
	},
	exportUnionRegistryToXml: function() {
		var form = this;
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var params = {
			onHide: function () {
				this.UnionRegistryGrid.loadData();
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id'),
			url: '/?c=Registry&m=exportUnionRegistryToXml'
		};

		getWnd('swRegistryXmlWindow').show(params);
	},
	filterOrgSMOCombo: function() {
		var date = new Date();
		var filtersForm = this.RegistryDataFiltersPanel.getForm();
		var OrgSMOCombo = filtersForm.findField('OrgSmo_id');

		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function(rec) {
			if ( (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date) && rec.get('KLRgn_id') == 35 ) {
				return true;
			}
			else {
				return false;
			}
		}

		OrgSMOCombo.getStore().filterBy(function(rec) {
			if ( (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date) && rec.get('KLRgn_id') == 35 ) {
				return true;
			}
			else {
				return false;
			}
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
	getParamsForEvnClass: function(record) {
		var config = new Object();
		
		// по умолчанию полка.
		config.open_form = 'swEvnPLEditWindow';
		config.key = 'EvnPL_id';
				
		if ( !record ) {
			return config;
		}
		
		var evnclass_id = record.get('EvnClass_id');

		if (!Ext.isEmpty(record.get('DispClass_id'))) {
			switch(record.get('DispClass_id')) {
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
		} else {
			switch(record.get('EvnClass_id')) {
				case 6:
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
				case 47:
					config.open_form = 'swEvnUslugaParEditWindow';
					config.key = 'EvnUslugaPar_id';
					break;
			}
		}
		
		return config;
	},
	importRegistryFromTFOMS: function(mode) {
		if ( typeof mode != 'string' || !mode.inlist([ 'simple', 'union' ]) ) {
			return false;
		}

		var win = this;
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

		var params = {
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
		};

		getWnd('swRegistryImportXMLFromTFOMSWindow').show(params);
	},
	importUnionRegistryXML: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var form = this;
		var params = {
			callback: function() {
				if ( form.UnionTFOMSErrorGrid && form.UnionTFOMSErrorGrid.rendered && form.UnionTFOMSErrorGrid.ViewGridStore ) {
					form.UnionTFOMSErrorGrid.ViewGridStore.reload();
				}
				form.UnionRegistryGrid.getGrid().getStore().reload();
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id')
		};

		getWnd('swRegistryImportXMLWindow').show(params);
	},
	onIsRunQueue: function (RegistryQueue_Position) {
		var form = this;

		this.getLoadMask(LOAD_WAIT).show();

		if ( RegistryQueue_Position === undefined ) {
			Ext.Ajax.request({
				url: '/?c=Registry&m=loadRegistryQueue',
				params: {
					Lpu_id: getGlobalOptions().lpu_id
				},
				callback: function(options, success, response) {
					if ( success ) {
						var result = Ext.util.JSON.decode(response.responseText);
						form.showRunQueue(result.RegistryQueue_Position);
					}
				}
			});
		}
		else {
			form.showRunQueue(RegistryQueue_Position);
		}
		
	},
	onRegistrySelect: function (Registry_id, RegistryType_id, nofocus) {
		var form = this;

		if ( form.AccountGrid.getCount() > 0 ) {
			switch ( form.DataTab.getActiveTab().id ) {
				case 'tab_registry':
					// бряк!
					break;

				case 'tab_data':
					if ((form.DataGrid.getParam('Registry_id')!=Registry_id) || (form.DataGrid.getCount()==0))
					{
						form.DataGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_commonerr':
					if ((form.ErrorComGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorComGrid.getCount()==0))
					{
						form.ErrorComGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_dataerr':
					if ((form.ErrorGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorGrid.getCount()==0))
					{
						form.ErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datanopolis':
					if ((form.NoPolisGrid.getParam('Registry_id')!=Registry_id) || (form.NoPolisGrid.getCount()==0))
					{
						form.NoPolisGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_datatfomserr':
					if ((form.TFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.TFOMSErrorGrid.getCount()==0))
					{
						form.TFOMSErrorGrid.loadData({callback: function() {
							form.TFOMSErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_databdzerr':
					if ((form.BDZErrorGrid.getParam('Registry_id')!=Registry_id) || (form.BDZErrorGrid.getCount()==0))
					{
						form.BDZErrorGrid.loadData({callback: function() {
							form.BDZErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datavizitdouble':
					//if ((form.DoubleVizitGrid.getParam('Registry_id')!=Registry_id) || (form.DoubleVizitGrid.getCount()==0))
					{
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

				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.removeAll(true);
					break;

				case 'tab_databdzerr':
					form.BDZErrorGrid.removeAll(true);
					break;
				case 'tab_datavizitdouble':
					form.DoubleVizitGrid.removeAll(true);
					break;
			}
		}

		return true;
	},
	onTreeClick: function(node, e) {
		var win = this;
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;

		owner.RegistryErrorFiltersPanel.getForm().reset();

		switch ( level ) {
			case 0:
			case 1:
				owner.findById('regvRightPanel').setVisible(false);
				break;

			case 2:
				g_RegistryType_id = node.attributes.object_value;
				owner.findById('regvRightPanel').setVisible(false);
				break;

			case 3:
				owner.findById('regvRightPanel').setVisible(false);
				g_RegistryType_id = node.parentNode.attributes.object_value;
				break;

			case 4:
				var RegistrySubType_id = node.parentNode.attributes.object_value;
				g_RegistryType_id = node.parentNode.parentNode.attributes.object_value;

				switch ( RegistrySubType_id ) {
					case 2:
						owner.findById('regvRightPanel').setVisible(true);
						owner.findById('regvRightPanel').getLayout().setActiveItem(1);

						var Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
						var RegistryType_id = node.parentNode.parentNode.attributes.object_value;
						var RegistryStatus_id = node.attributes.object_value;

						//owner.UnionRegistryGrid.setActionDisabled('action_add', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'SuperAdmin', 'RegistryUser' ])));
						//owner.UnionRegistryGrid.setActionDisabled('action_edit', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'SuperAdmin', 'RegistryUser' ])));
						//owner.UnionRegistryGrid.setActionDisabled('action_delete', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'SuperAdmin', 'RegistryUser' ])));

						owner.UnionRegistryGrid.setColumnHidden('DispClass_Name', RegistryType_id != 7 && RegistryType_id != 9);
						owner.UnionRegistryGrid.setColumnHidden('Registry_IsZNO', RegistryType_id != 2);

						// Меняем колонки и отображение
						if ( RegistryType_id == 1 || RegistryType_id==14 ) {
							// Для стаца одни названия
							owner.UnionDataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
							owner.UnionDataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');
							owner.UnionDataGrid.setColumnHidden('Evn_disDate', false);
						}
						else {
							// Для остальных - другие
							owner.UnionDataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
							owner.UnionDataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');
							owner.UnionDataGrid.setColumnHidden('Evn_disDate', true);
						}

						owner.UnionDataGrid.setColumnHidden('Mes_Code', true);
						owner.UnionDataGrid.setColumnHidden('HTMedicalCareClass_GroupCode', RegistryType_id!=14);
						owner.UnionDataGrid.setColumnHidden('HTMedicalCareClass_Name', RegistryType_id!=14);
						owner.UnionDataGrid.setColumnHidden('UslugaComplex_Code', RegistryType_id == 1 || RegistryType_id == 14);

						owner.UnionRegistryGrid.loadData({
							params: {
								RegistryType_id: RegistryType_id,
								RegistryStatus_id: RegistryStatus_id,
								Lpu_id: Lpu_id
							},
							globalFilters: {
								RegistryType_id: RegistryType_id,
								RegistryStatus_id: RegistryStatus_id,
								Lpu_id: Lpu_id,
								start: 0,
								limit: 100
							}
						});
						break;

					case 1:
					case 3:
						owner.findById('regvRightPanel').setVisible(true);
						owner.findById('regvRightPanel').getLayout().setActiveItem(0);

						var Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
						var RegistryType_id = node.parentNode.parentNode.attributes.object_value;
						var RegistryStatus_id = node.attributes.object_value;

						win.AccountGrid.setColumnHidden('DispClass_Name', RegistryType_id != 7 && RegistryType_id != 9);
						win.AccountGrid.setColumnHidden('Registry_IsZNO', RegistrySubType_id == 3 || RegistryType_id != 2);

						if (!win.DataTab.getActiveTab().id.inlist(['tab_registry', 'tab_data'])) {
							win.DataTab.setActiveTab('tab_data');
						}

						// Меняем колонки и отображение
						if ( RegistryType_id == 1 || RegistryType_id == 14 ) {
							owner.DataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
						}
						else {
							owner.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
						}

						//owner.AccountGrid.setActionDisabled('action_add', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'SuperAdmin', 'RegistryUser' ])));
						//owner.AccountGrid.setActionDisabled('action_edit', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'SuperAdmin', 'RegistryUser' ])));

						owner.setMenuActions(owner.AccountGrid, RegistryStatus_id);

						owner.AccountGrid.getAction('action_yearfilter').setHidden(RegistryStatus_id != 4);

						if ( 4 == RegistryStatus_id ) {
							owner.constructYearsMenu({RegistryType_id: RegistryType_id, RegistryStatus_id: RegistryStatus_id, Lpu_id: Lpu_id});
						}

						owner.AccountGrid.loadData({
							params: {
								RegistryType_id: RegistryType_id,
								RegistrySubType_id: RegistrySubType_id,
								RegistryStatus_id: RegistryStatus_id,
								Lpu_id: Lpu_id
							},
							globalFilters: {
								RegistryType_id: RegistryType_id,
								RegistrySubType_id: RegistrySubType_id,
								RegistryStatus_id: RegistryStatus_id,
								Lpu_id: Lpu_id
							}
						});
						break;
				}
				break;
		}
	},
	onUnionRegistrySelect: function (Registry_id, nofocus, record, RegistryType_id) {
		var form = this;

		if ( !RegistryType_id ) {
			RegistryType_id = record.get('RegistryType_id');
		}

		if ( form.UnionRegistryGrid.getCount() > 0 ) {
			switch ( form.UnionDataTab.getActiveTab().id ) {
				case 'tab_registrys':
					if ((form.UnionRegistryChildGrid.getParam('Registry_id')!=Registry_id) || (form.UnionRegistryChildGrid.getCount()==0))
					{
						form.UnionRegistryChildGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id: RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_uniondata':
					if ((form.UnionDataGrid.getParam('Registry_id')!=Registry_id) || (form.UnionDataGrid.getCount()==0))
					{
						form.UnionDataGrid.removeAll(true);
						form.UnionDataGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id: RegistryType_id,start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_uniondatatfomserr':
					var filter_form = this.UnionRegistryTFOMSFiltersPanel.getForm();
					if ((form.UnionTFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.UnionTFOMSErrorGrid.getCount()==0))
					{
						form.UnionTFOMSErrorGrid.loadData({callback: function() {
							form.UnionTFOMSErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, RegistryType_id: RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
			}
		}
		else {
			switch ( form.UnionDataTab.getActiveTab().id ) {
				case 'tab_registrys':
					form.UnionRegistryChildGrid.removeAll(true);
					break;

				case 'tab_uniondata':
					form.UnionDataGrid.removeAll(true);
					break;

				case 'tab_uniondatatfomserr':
					form.UnionTFOMSErrorGrid.removeAll(true);
					break;
			}
		}

		return true;
	},
	openForm: function (object, oparams, frm) {
		var form = this;

		// Взависимости от типа выбираем форму которую будем открывать 
		// Типы лежат в RegistryType
		var record = object.getGrid().getSelectionModel().getSelected();
		
		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}

		if ( form.Tree.selModel.selNode.parentNode.attributes.object == 'RegistrySubType' && form.Tree.selModel.selNode.parentNode.attributes.object_value == 2 ) {
			var RegistryType_id = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		}
		else {
			var RegistryType_id = this.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		}

		var type = record.get('RegistryType_id');

		if ( !type ) {
			type = RegistryType_id;
		}

		if ( object.id == this.id + 'TFOMSError' || object.id == this.id + 'BDZError' || object.id == this.id + 'Data' || object.id == this.id + 'Error') // Если это с грида "Ошибки ТФОМС", "Ошибки перс. данных", "Данные" или "Ошибки данных"
		{
			if ( frm == 'OpenPerson' ) {
				type = 108;
			}
		}

		var id = record.get('Evn_rid') ||  record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное 
		var person_id = record.get('Person_id');
		
		var open_form = '';
		var key = '';
		var params = {action: 'edit', Person_id: person_id, Server_id: 0, RegistryType_id: RegistryType_id}; //, Person_id: this.Person_id, Server_id: this.Server_id

		params = Ext.apply(params || {}, oparams || {});

		switch ( type ) {
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
				params.DispClass_id = record.get('DispClass_id');
				break;

			case 11:
				open_form = 'swEvnPLDispProfEditWindow';
				key = 'EvnPLDispProf_id';
				break;

			case 12:
			case 20:
				var config = form.getParamsForEvnClass(record);
				
				open_form = config.open_form;
				key = config.key;
				if (key == 'EvnUslugaPar_id') {
					id = record.get('Evn_id');
				}
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

		getWnd(open_form).show(params);
	},
	printRegistry: function(mode, type) {
		if ( typeof mode != 'string' || !mode.inlist([ 'simple', 'union' ]) ) {
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

		if (type == 2) {
			switch(record.get('RegistryType_id')) {
				case 2:
					if ( !Ext.isEmpty(record.get('PayType_SysNick')) && record.get('PayType_SysNick').inlist([ 'dms', 'contract' ]) ) {
						template = 'printSvodVed_polka_SpecKont.rptdesign';
					}
					else {
						template = 'printSvodVed_polka.rptdesign';
					}
					break;
				case 7:
					if (record.get('DispClass_id') == 2) {
						template = 'Registry_EvnPLDD13_2stage_svod_ved.rptdesign';
					} else {
						template = 'Registry_EvnPLDD13_svod_ved.rptdesign';
					}
					break;
				case 12:
					template = 'Registry_ProfTeen_svod_ved.rptdesign';
					break;
				case 20:
					template = 'printSvodVed_parusl.rptdesign';
					break;
				case 11:
					template = 'Registry_EvnPLProf_svod_ved.rptdesign';
					break;
			}
		} else {
			if (mode == 'union') {
				template = 'printSchet_smo.rptdesign';
			} else {
				template = 'printSchet_tfoms.rptdesign';
			}
		}

		printBirt({
			'Report_FileName': template,
			'Report_Params': '&paramRegistry=' + record.get('Registry_id'),
			'Report_Format': format
		});
	},
	printRegistryData: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var
			Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');
		if ( !Registry_id ) {
			return false;
		}

		var id_salt = Math.random();
		var win_id = 'print_registrydata' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=Registry&m=printRegistryData&Registry_id=' + Registry_id + '&RegistryType_id=' + registryType_id, win_id);
	},
	reformRegistry: function(Registry_id, RegistryType_id) {
		if ( Ext.isEmpty(Registry_id) || Ext.isEmpty(RegistryType_id) ) {
			return false;
		}

		var current_window = this;

		var loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите, идет переформирование реестра...'});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=reformRegistry',
			params: {	
				Registry_id: Registry_id, 
				RegistryType_id: RegistryType_id
			},
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.Error_Msg == '' || result.Error_Msg == null || result.Error_Msg == 'null' ) {
						// Выводим сообщение о постановке в очередь
						current_window.onIsRunQueue(result.RegistryQueue_Position);
						// Перечитываем грид, чтобы обновить данные по счетам
						current_window.AccountGrid.loadData();
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Во время переформирования произошла ошибка<br/>');
				}
			},
			timeout: 600000
		});
	},
	reformUnionRegistry: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var
			Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					form.getLoadMask('Переформирование реестра').show();

					Ext.Ajax.request({
						url: '/?c=Registry&m=reformUnionRegistry',
						params: {
							Registry_id: Registry_id,
							RegistryType_id: registryType_id
						},
						callback: function(options, success, response) {
							form.getLoadMask().hide();

							if ( success ) {
								// Перечитываем грид, чтобы обновить данные по счетам
								form.UnionRegistryGrid.loadData();

								// обновить список входящих реестров
								form.UnionRegistryChildGrid.getGrid().getStore().reload();
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Вы уверены, что хотите переформировать реестр?',
			title: 'Вопрос'
		});
	},
	/**
	 * Пересчет реестра
	 */
	refreshRegistry: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var Registry_id = record.get('Registry_id');

		form.getLoadMask('Пересчёт реестра').show();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=refreshRegistryData',
						params: {
							Registry_id: Registry_id, 
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
	setMenuActions: function (object, RegistryStatus_id) {
		var form = this;
		var menu = new Array();

		if ( !this.menu ) {
			this.menu = new Ext.menu.Menu({
				id: 'RegistryMenu'
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
					disabled: true,
					handler: function() {   
						form.setRegistryStatus(2);
					}
				}, {
					text: 'Переформировать',
					tooltip: 'Переформировать реестр',
					disabled: true,
					handler: function() {
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

						if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
							sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
							return false;
						}

						form.reformRegistry(record.get('Registry_id'), record.get('RegistryType_id'));
					}
				}, {
					text: 'Пересчитать реестр',
					tooltip: 'Пересчитать реестр',
					disabled: true,
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
						form.exportRegistryToXml(1);
					}
				}, {
					text: 'Импорт результата ФЛК (ТФОМС)',
					tooltip: 'Импорт результата ФЛК (ТФОМС)',
					disabled: true,
					handler: function() {
						form.importRegistryFromTFOMS('simple');
					}
				}, {
					text: 'Снять отметку "К оплате"',
					tooltip: 'Снять отметку "К оплате"',
					disabled: true,
					handler: function() {
						form.setRegistryStatus(3);
					}
				}, {
					text: 'Отметить как оплаченный',
					tooltip: 'Отметить как оплаченный',
					disabled: true,
					handler: function() {
						form.setRegistryStatus(4);
					}
				}];
				break;

			case 4: // Оплаченные 
				menu = [{
					text: 'Снять отметку "оплачен"',
					tooltip: 'Снять отметку "оплачен"',
					disabled: true,
					handler: function() {
						form.setRegistryStatus(2);
					}
				}];
				break;

			default:
				sw.swMsg.alert('Ошибка', 'Значение статуса неизвестно!');
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
	setRegistryStatus: function(RegistryStatus_id) {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var
			registryType_id = record.get('RegistryType_id'),
			Registry_id = record.get('Registry_id');

		var Registry_ids = [];
		var selections = this.AccountGrid.getGrid().getSelectionModel().getSelections();
		for (key in selections) {
			if ( typeof(selections[key]) == 'object' ) {
				Registry_ids.push(selections[key].get('Registry_id'));
			}
		}

		form.getLoadMask('Установка статуса...').show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryStatus',
			params: {	
				Registry_ids: Ext.util.JSON.encode(Registry_ids),
				RegistryStatus_id: RegistryStatus_id,
				RegistryType_id: registryType_id
			},
			callback: function(options, success, response) {
				form.getLoadMask().hide();

				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.RegistryStatus_id == RegistryStatus_id ) {
						// Перечитываем грид, чтобы обновить данные по счетам
						form.AccountGrid.loadData();
					}
				}
			}
		});
	},
	setUnionRegistryStatus: function(RegistryStatus_id) {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var
			Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');

		var Registry_ids = [];
		var selections = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelections();

		for (key in selections) {
			if (typeof(selections[key]) == 'object') {
				Registry_ids.push(selections[key].get('Registry_id'));
			}
		}

		form.getLoadMask('Установка статуса...').show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=setUnionRegistryStatus',
			params: {
				Registry_ids: Ext.util.JSON.encode(Registry_ids),
				RegistryStatus_id: RegistryStatus_id,
				RegistryType_id: registryType_id
			},
			callback: function(options, success, response) {
				form.getLoadMask().hide();

				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);

					if ( result.RegistryStatus_id == RegistryStatus_id ) {
						// Перечитываем грид, чтобы обновить данные по счетам
						form.UnionRegistryGrid.loadData();
					}
				}
			}
		});
	},
	show: function() {
		sw.Promed.swRegistryViewWindow.superclass.show.apply(this, arguments);
		
		this.getLoadMask().show();

		// При открытии если Root Node уже открыта - перечитываем
		var root = this.Tree.getRootNode();

		if ( root ) {
			if ( root.isExpanded() && root.childNodes && root.childNodes.length > 0 && root.childNodes[0].loaded ) {
				this.Tree.getLoader().load(root);
			}
		}

		this.maximize();

		// Также грид "Счета" сбрасываем
		this.AccountGrid.removeAll();
		this.UnionRegistryGrid.removeAll();

		// Добавляем менюшку с действиями для объединённых реестров
		this.UnionRegistryGrid.addActions({name:'action_isp', iconCls: 'actions16', text: 'Действия', menu: this.UnionActionsMenu});
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
		var form = this;

		form.TreeToolbar = new Ext.Toolbar({
			id: form.id + 'Toolbar',
			items: [{
				xtype : "tbseparator"
			}]
		});
		
		form.Tree = new Ext.tree.TreePanel({
			animate: false,
			autoScroll: true,
			id: form.id + 'RegistryTree',
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
			tbar: form.TreeToolbar,
			width: 250
		});

		// Выбор ноды click-ом
		form.Tree.on('click', function(node, e) {
			form.onTreeClick(node, e);
		});

		form.AccountGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistry',
			editformclassname: 'swRegistryEditWindow',
			height: 203,
			id: form.id + 'Account',
			object: 'Registry',
			//paging: true,
			region: 'north',
			title: 'Счет',

			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'OrgSmo_id', type: 'int', hidden: true},
				{name: 'OrgSmo_Name', id: 'autoexpand', header: 'СМО'},
				{name: 'Registry_Num', header: 'Номер счета', width: 120},
				{name: 'Registry_accDate', type:'date', header: 'Дата счета', width: 80},
				{name: 'Registry_insDT', type:'date', header: '', width: 80,hidden:true},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Оконч. периода', width: 100},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 100},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_ErrorCount', hidden: true},
				{name: 'Registry_updDate', type: 'datetimesec', header: 'Дата изменения', width: 110},
				{name: 'Registry_isPersFin', header: 'Подушевое финансирование', type: 'checkbox', width: 180},
				{name: 'Registry_IsZNO', header: 'ЗНО', type: 'checkbox', width: 50},
				{name: 'Registry_IsRepeated', header: 'Повторная подача', type: 'checkbox', width: 50},
				{name: 'PayType_Name', header: 'Вид оплаты', type: 'string', width: 100},
				{name: 'PayType_SysNick', type: 'string', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPolis_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorBDZ_IsData', type: 'int', hidden: true},
				{name: 'RegistryDouble_IsData', type: 'int', hidden: true},
				{name: 'Registry_kd_good', type: 'int',  hidden: true},
				{name: 'Registry_kd_err', type: 'int',  hidden: true},
				{name: 'RegistrySubType_id', type: 'int',  hidden: true},
				{name: 'ReformTime', hidden: true}
			],
			actions: [
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_view'},
				{name:'action_delete', disabled: true, url: '/?c=Registry&m=deleteRegistry'},
				{
					name: 'action_print',
					menuConfig: {
						printRegistry: { text: 'Печать счёта', handler: function(){ this.printRegistry('simple', 1); }.createDelegate(this) },
						printRegistrySvod: { text: 'Печать сводной ведомости ', handler: function(){ this.printRegistry('simple', 2); }.createDelegate(this) }
					}
				}
			],
			afterDeleteRecord: function() {
				form.DataTab.fireEvent('tabchange', form.DataTab.getActiveTab(), form.DataTab);
			},
			afterSaveEditForm: function(RegistryQueue_id, records) {
				var r = records.RegistryQueue_Position;
				form.onIsRunQueue(r);
			},
			onLoadData: function() {   

			},
			onRowSelect: function(sm, index, record) {  
				if ( this.getCount() > 0 ) {
					var
						Registry_id = record.get('Registry_id'),
						RegistryType_id = record.get('RegistryType_id'),
						RegistryStatus_id = record.get('RegistryStatus_id'),
						OrgSmo_id = record.get('OrgSmo_id'),
						RegistrySubType_id = record.get('RegistrySubType_id');

					form.onRegistrySelect(Registry_id, RegistryType_id, false);

					//this.setActionDisabled('action_edit', (RegistryStatus_id != 3 || Ext.isEmpty(RegistrySubType_id)));
					//this.setActionDisabled('action_delete', (RegistryStatus_id != 3));
					this.setActionDisabled('action_view', false);

					// В прогрессе 
					if ( record.get('Registry_IsProgress') == 1 ) {
						this.setActionDisabled('action_edit', true);
						this.setActionDisabled('action_view', true);
						this.setActionDisabled('action_delete', true);
					}

					if ( (record.get('RegistryStatus_id') == 4) || (record.get('RegistryStatus_id') == 2) ) {
						this.setActionDisabled('action_delete',true); // не давать удалять, если реестр находится в разделе К оплате или Оплаченные
					}

					if (record.get('RegistryStatus_id') == 4) {
						this.getAction('action_print').menu.printRegistry.setHidden(false);
					} else {
						this.getAction('action_print').menu.printRegistry.setHidden(true);
					}
					if (record.get('RegistryType_id') && record.get('RegistryType_id').inlist([2,7,12,20,11])) {
						this.getAction('action_print').menu.printRegistrySvod.setHidden(record.get('RegistryStatus_id') == 5);
					} else {
						this.getAction('action_print').menu.printRegistrySvod.setHidden(true);
					}

					form.RegistryPanel.show();
					form.RegistryTpl.overwrite(form.RegistryPanel.body, {
						Registry_Num: record.get('Registry_Num'), 
						Registry_insDT: Ext.util.Format.date(record.get('Registry_insDT'),'d.m.Y'), 
						Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'), 
						Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'), 
						Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'), 
						Registry_Count: record.get('Registry_Count'),
						ReformTime:record.get('ReformTime'),
						Registry_ErrorCount: record.get('Registry_ErrorCount'), 
						Registry_NoErrorCount: record.get('Registry_Count') - record.get('Registry_ErrorCount'), 
						Registry_IsNeedReform: record.get('Registry_IsNeedReform')
					});
				}
				else  {
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

						case 'tab_datatfomserr':
							form.TFOMSErrorGrid.removeAll(true);
							break;

						case 'tab_databdzerr':
							form.BDZErrorGrid.removeAll(true);
							break;
					}
				}

				// информируем о данных на вкладках
				form.DataTab.getItem('tab_registry').setIconClass((record.get('Registry_IsNeedReform')==2)?'usluga-notok16':'info16');
				form.DataTab.getItem('tab_data').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_commonerr').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_dataerr').setIconClass((record.get('RegistryError_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopolis').setIconClass((record.get('RegistryNoPolis_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_databdzerr').setIconClass((record.get('RegistryErrorBDZ_IsData')==1)?'usluga-notok16':'good');
			}
		});

		form.AccountGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
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
			'<div style="padding:4px;font-weight:bold;">Реестр № {Registry_Num}<tpl if="Registry_IsNeedReform == 2"> <span style="color: red;">(НУЖДАЕТСЯ В ПЕРЕФОРМИРОВАНИИ!)</span></tpl></div>'+
			'<div style="padding:4px;">Дата формирования: {Registry_insDT}</div>'+
			'<div style="padding:4px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:4px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:4px">Дата переформирования реестра: {ReformTime}</div>'+
			'<div style="padding:4px;">Количество записей в реестре: {Registry_Count}</div>'+
			'<div style="padding:4px;">Количество записей с ошибками данных: {Registry_ErrorCount}</div>'+
			'<div style="padding:4px;">Записей без ошибок: {Registry_NoErrorCount}</div>'
		];

		form.RegistryTpl = new Ext.XTemplate(RegTplMark);
		
		form.RegistryPanel = new Ext.Panel({
			id: 'RegistryPanel',
			bodyStyle: 'padding:2px;  overflow: auto;',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: false,
			height: 28,
			maxSize: 28,
			html: ''
		});

		form.DataGridSearch = function() {
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
						MedPersonal_id: filtersForm.findField('MedPersonal_id').getValue(), 
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						OrgSmo_id: filtersForm.findField('OrgSmo_id').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						start: 0,
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}

		form.UnionDataGridSearch = function() {
			var filtersForm = form.UnionRegistryDataFiltersPanel.getForm();

			var registry = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');

			if ( Registry_id > 0 ) {
				form.UnionDataGrid.loadData({
					globalFilters: {
						Registry_id: Registry_id,
						Person_SurName: filtersForm.findField('Person_SurName').getValue(),
						Person_FirName: filtersForm.findField('Person_FirName').getValue(),
						Person_SecName: filtersForm.findField('Person_SecName').getValue(),
						MedPersonal_id: filtersForm.findField('MedPersonal_id').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
			}
		};

		form.UnionDataGridReset = function() {
			var filtersForm = form.UnionRegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.UnionDataGrid.removeAll(true);
			form.UnionDataGridSearch();
		};

		form.DataGridReset = function() {
			var filtersForm = form.RegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.UnionDataGrid.removeAll(true);
			form.DataGridSearch();
		};

		form.firstTabIndex = form.firstTabIndex + 8;

		form.RegistryDataFiltersPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			height: 55,
			id: 'RegistryDataFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					form.DataGridSearch();
				},
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
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {
					bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'
				},
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 8
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex - 4,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 7
					}, {
						anchor: '100%',
						allowBlank: true,
						fieldLabel: 'СМО',
						listeners: {
							'render': function() {
								form.filterOrgSMOCombo();
							}
						},
						hiddenName: 'OrgSmo_id',
						editable: true,
						triggerAction: 'all',
						forceSelection: true,
						listWidth: 400,
						tabIndex: form.firstTabIndex - 3,
						withoutTrigger: true,
						xtype: 'sworgsmocombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 6
					}, {
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex - 2
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 90,
					items:[{
						anchor: '100%',
						hiddenName: 'MedPersonal_id',
						lastQuery: '',
						listWidth: 650,
						tabIndex: form.firstTabIndex - 5,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						style: 'margin-left: 4px;',
						tabIndex: form.firstTabIndex - 1,
						disabled: false,
						handler: function () {
							form.DataGridSearch();
						}
					}, {
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex,
						disabled: false,
						style: 'margin-left: 4px;',
						handler: function () {
							form.DataGridReset();
						}
					}]
				}]
			}]
		});

		form.firstTabIndex = form.firstTabIndex + 7;

		form.UnionRegistryDataFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			height: 55,
			layout: 'form',
			region: 'north',
			listeners: {
				'render': function () {
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					form.UnionDataGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				border: false,
				bodyStyle: 'background:transparent;',
				defaults: {bodyStyle: 'padding-top: 4px; padding-left: 4px; background:transparent;'},
				labelAlign: 'right',
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					width: 240,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 7
					}, {
						anchor: '100%',
						fieldLabel: 'Врач',
						allowBlank: true,
						editable: true,
						forceSelection: true,
						listWidth: 400,
						hiddenName: 'MedPersonal_id',
						xtype: 'swmedpersonalcombo',
						tabIndex: form.firstTabIndex - 4
					}]
				}, {
					layout: 'form',
					border: false,
					width: 240,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 6
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex - 3,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 240,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 5
					}, {
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex - 2
					}]
				}, {
					layout: 'form',
					border: false,
					width: 240,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex - 1,
						disabled: false,
						handler: function () {
							form.UnionDataGridSearch();
						}
					}, {
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex,
						disabled: false,
						handler: function () {
							form.UnionDataGridReset();
						}
					}]
				}]
			}]
		});
		
		form.ErrorGridSearch = function() {
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
						RegistryError_Code: filtersForm.findField('RegistryError_Code').getValue(),
						MedPersonal_id: filtersForm.findField('MedPersonal_id').getValue(), 
						Evn_id: filtersForm.findField('Evn_id').getValue(), 
						start: 0, 
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}
		
		form.firstTabIndex = form.firstTabIndex + 7;

		form.RegistryErrorFiltersPanel = new Ext.form.FormPanel({
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			height: 55,
			layout: 'form',
			id: 'RegistryErrorFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.ErrorGridSearch();
				},
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
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 6
					}, {
						anchor: '100%',
						fieldLabel: 'Ошибка',
						name: 'RegistryError_Code',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 3
					}]
				}, {
					layout: 'form',
					border: false,
					width: 180,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 5
					}, {
						anchor: '100%',
						hiddenName: 'MedPersonal_id',
						lastQuery: '',
						listWidth: 650,
						tabIndex: form.firstTabIndex - 2,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 240,
					labelWidth: 70,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex - 4
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex - 1,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					width: 210,
					labelWidth: 80,
					items: [{
						handler: function() {
							form.ErrorGridSearch();
						},
						icon: 'img/icons/search16.png', 
						iconCls : 'x-btn-text',
						id: 'rvwREBtnSearch',
						tabIndex: form.firstTabIndex,
						text: BTN_FRMSEARCH,
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button'
					}]
				}]
			}]
		});
	
		form.TFOMSGridSearch = function() {
			var filtersForm = form.RegistryTFOMSFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');

			if ( Registry_id > 0 ) {
				form.TFOMSErrorGrid.loadData({
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

		form.UnionTFOMSGridSearch = function () {
			var filtersForm = form.UnionRegistryTFOMSFiltersPanel.getForm();

			var registry = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');

			if ( Registry_id > 0 ) {
				form.UnionTFOMSErrorGrid.loadData({
					globalFilters: {
						Person_FIO: filtersForm.findField('Person_FIO').getValue(),
						RegistryErrorType_Code: filtersForm.findField('TFOMSError').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						Registry_id: Registry_id,
						start: 0,
						limit: 100
					},
					noFocusOnLoad: false
				});
			}
		};

		// Кнопка "Поиск"
		var rvwTFOMSBtnSearch = new Ext.Button({
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwTFOMSBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png',
			iconCls: 'x-btn-text',
			disabled: false,
			tabIndex: form.firstTabIndex++,
			handler: function () {
				form.TFOMSGridSearch();
			}
		});

		form.RegistryTFOMSFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:4px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			id: 'RegistryTFOMSFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.TFOMSGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {
					bodyStyle:'padding-left: 4px; background:#DFE8F6;'
				},
				layout: 'column',
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
						id: 'rvwTFOMSPersonFIO',
						tabIndex: form.firstTabIndex++
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
						id: 'rvwTFOMSError',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					width: 110,
					items: [
						rvwTFOMSBtnSearch
					]
				}]
			}]
		});

		form.UnionRegistryTFOMSFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:4px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					form.UnionTFOMSGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {
					bodyStyle: 'padding-left: 4px; background:#DFE8F6;'
				},
				layout: 'column',
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
						tabIndex: form.firstTabIndex++
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
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle: 'padding-left: 4px;background:#DFE8F6;',
					width: 110,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex++,
						disabled: false,
						handler: function () {
							form.UnionTFOMSGridSearch();
						}
					}]
				}]
			}]
		});
		
		// Данные реестра 
		form.DataGrid = new sw.Promed.ViewFrame({
			id: form.id + 'Data',
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
				{name: 'Polis_Num', header: 'Номер полиса', width: 80},
				{name: 'RegistryData_ItogSum', header: 'Сумма', type: 'money', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'RegistryData_Uet', header: 'УЕТ', type: 'float', width: 60},
				{name: 'Paid', header: 'Оплата', width: 60},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'Err_Count', hidden:true}
			],
			actions: [    
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() { form.openForm(form.DataGrid, {});}},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true, handler: function() { form.deleteRegistryData(form.DataGrid, false); } },
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryData(); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.DataGrid, {}, 'OpenPerson');}}
			],
			onLoadData: function() {
				var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;

				//this.setActionDisabled('action_delete', RegistryStatus_id != 3);
			},
			onRowSelect: function(sm,rowIdx,record) {
				// Меняем текст акшена удаления в зависимости от данных
				form.DataGrid.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');
			} 
		});

		form.DataGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
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
				rowupdated: function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		// Данные реестра
		form.UnionDataGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadUnionRegistryData',
			id: form.id + 'UnionData',
			object: 'RegistryData',
			region: 'center',
			paging: true,
			passPersonEvn: true,
			root: 'data',
			title: 'Данные',
			toolbar: false,
			totalProperty: 'totalCount',
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
				{name: 'Polis_Num', header: 'Номер полиса', width: 80},
				{name: 'RegistryData_ItogSum', header: 'Сумма', type: 'money', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'RegistryData_Uet', header: 'УЕТ', type: 'float', width: 60},
				{name: 'Paid', header: 'Оплата', width: 60},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'ErrTfoms_Count', hidden:true}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', handler: function() { form.openForm(form.UnionDataGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text:'Удалить случай (с удалением из предварительного реестра)', disabled: true, handler: function() { form.deleteUnionRegistryData(form.UnionDataGrid); } },
				{name:'action_print', text:'Печатать текущую страницу'}
			],
			onLoadData: function() {
				
			},
			onRowSelect: function(sm, rowIdx, record) {
				
			}
		});

		form.UnionDataGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';

				if ((row.get('IsRDL') > 0) && (isAdmin))
					cls = cls + 'x-grid-rowblue ';
				if (row.get('ErrTfoms_Count') > 0)
					cls = cls + 'x-grid-rowred ';
				if (row.get('needReform') == 2)
					cls = cls + 'x-grid-rowselect ';
				if (row.get('isNoEdit') == 2)
					cls = cls + 'x-grid-rowgray ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls + 'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel';

				return cls;
			},
			listeners: {
				rowupdated: function (view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		// Общие ошибки
		form.ErrorComGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryErrorCom',
			id: form.id+'ErrorCom',
			object: 'RegistryErrorCom',
			paging: true,
			root: 'data',
			title: 'Общие ошибки',
			toolbar: false,
			totalProperty: 'totalCount',
			stringfields: [
				{name: 'RegistryErrorType_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', id: 'autoexpand', header: 'Наименование'},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			actions: [
				{name:'action_add', disabled: true},
				{name:'action_edit', text: '<b>Исправить</b>', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
			],
			onLoadData: function() {
				
			}
		});

		form.ErrorComGrid.ViewGridPanel.view = new Ext.grid.GridView({
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

		// Ошибки данных
		form.ErrorGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			dataUrl: '/?c=Registry&m=loadRegistryError',
			id: form.id + 'Error',
			object: 'RegistryError',
			paging: true,
			root: 'data',
			title: 'Ошибки данных',
			totalProperty: 'totalCount',
			toolbar: false,
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
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'},
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true},
				{name: 'Mes_Code', header: 'МЭС', width: 80}
			],
			actions: [
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
					record.set('RegistryData_IsCorrected', 2);
					form.openForm(form.ErrorGrid, {});}
				},
				{name:'action_view', disabled: true},
				{name:'action_delete', text: 'Удалить случай из реестра', disabled: true, handler: function() { form.deleteRegistryData(form.ErrorGrid, false); }},
				{name:'action_print', text: 'Печатать текущую страницу'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', disabled: true, handler: function() { form.deleteRegistryData(form.ErrorGrid, true); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.ErrorGrid, {}, 'OpenPerson');}}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				//this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				//this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');
			},
			onLoadData: function()
			{
				//var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				//this.setActionDisabled('action_delete',(RegistryStatus_id!=3));
				//this.setActionDisabled('action_deleteall',(RegistryStatus_id!=3));
			}
		});

		form.ErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass : function (row, index) {
				var cls = '';
				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-row ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_notexist') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_IsCorrected') == 2)
					cls = cls+'x-grid-rowbackgreen ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		// 4. Незастрахованные
		this.NoPolisGrid = new sw.Promed.ViewFrame({
			id: form.id + 'NoPolis',
			title: 'Незастрахованные',
			object: 'RegistryNoPolis',
			dataUrl: '/?c=Registry&m=loadRegistryNoPolis',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{
					name: 'action_edit', handler: function() {
						form.openForm(form.NoPolisGrid, {}, 'OpenPerson');
					}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function() {
				this.setActionDisabled('action_edit', !(isUserGroup(['RegistryUser']) || isSuperAdmin()));
			}
		});

		// 5. Ошибки ТФОМС
		form.TFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: form.id + 'TFOMSError',
			title: 'Итоги проверки ТФОМС',
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
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type: 'date', header: 'Окончание', width: 70}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{
					name: 'action_edit', text: '<b>Исправить</b>', handler: function () {
					form.openForm(form.TFOMSErrorGrid, {});
				}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{
					name: 'action_delete', text: 'Удалить случай из реестра', disabled: true, handler: function () {
					form.deleteRegistryData(form.TFOMSErrorGrid, false);
				}
				},
				{name: '-'},
				{
					name: 'action_deleteall',
					icon: 'img/icons/delete16.png',
					text: 'Удалить случаи по всем ошибкам',
					disabled: true,
					handler: function () {
						form.deleteRegistryData(form.TFOMSErrorGrid, true);
					}
				},
				{
					name: 'action_openevn',
					disabled: true,
					visible: !isAdmin,
					tooltip: 'Открыть учетный документ',
					icon: 'img/icons/pol-eplstream16.png',
					text: 'Открыть учетный документ',
					handler: function () {
						form.openForm(form.TFOMSErrorGrid, {}, 'OpenEvn');
					}
				},
				{
					name: 'action_openperson',
					disabled: true,
					visible: !isAdmin,
					icon: 'img/icons/patient16.png',
					tooltip: 'Открыть данные человека',
					text: 'Открыть данные человека',
					handler: function () {
						form.openForm(form.TFOMSErrorGrid, {}, 'OpenPerson');
					}
				}

			],
			callbackPersonEdit: function (person, record) {
				if (this.selectedRecord) {
					record = this.selectedRecord;
				}
				if (!record) {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if (!record) {
					return false;
				}
				//form.setNeedReform(record);
			},
			onRowSelect: function (sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
				}
			},
			onLoadData: function () {
				//var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				//this.setActionDisabled('action_delete', (RegistryStatus_id != 3));
				//this.setActionDisabled('action_deleteall', (RegistryStatus_id != 3));
			}
		});

		form.TFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
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

		// 6. Ошибки перс. данных
		form.BDZErrorGrid = new sw.Promed.ViewFrame({
			id: form.id + 'BDZError',
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
				{ name: 'action_edit', text: '<b>Исправить</b>', handler: function () { form.openForm(form.BDZErrorGrid, {}, 'OpenPerson'); } },
				{ name: 'action_view', disabled: true, hidden: true },
				{ name: 'action_delete', disabled: true, hidden: true }
			]
		});

		form.BDZErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
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

		// Ошибки ТФОМС
		form.UnionTFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionTFOMSError',
			title: 'Итоги проверки ТФОМС / СМО',
			object: 'RegistryErrorTFOMS',
			dataUrl: '/?c=Registry&m=loadUnionRegistryErrorTFOMS',
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
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type: 'date', header: 'Окончание', width: 70}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{
					name: 'action_edit', text: '<b>Исправить</b>', handler: function () {
						form.openForm(form.UnionTFOMSErrorGrid, {});
					}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{
					name:'action_delete', text:'Удалить случай (с удалением из предварительного реестра)', disabled: true, handler: function() {
						form.deleteUnionRegistryData(form.UnionTFOMSErrorGrid);
					}
				},
				{
					hidden: true,
					name: 'action_deleteerror',
					icon: 'img/icons/delete16.png',
					disabled: true,
					text: 'Удалить ошибку',
					handler: function () {
						form.deleteRegistryErrorTFOMS(form.UnionTFOMSErrorGrid, false);
					}
				},
				{name: '-'},
				{
					hidden: true,
					name: 'action_deleteall',
					icon: 'img/icons/delete16.png',
					text: 'Удалить случаи по всем ошибкам',
					disabled: true,
					handler: function () {
						form.deleteRegistryData(form.UnionTFOMSErrorGrid, true);
					}
				},
				{
					name: 'action_openevn',
					disabled: true,
					visible: !isAdmin,
					tooltip: 'Открыть учетный документ',
					icon: 'img/icons/pol-eplstream16.png',
					text: 'Открыть учетный документ',
					handler: function () {
						form.openForm(form.UnionTFOMSErrorGrid, {}, 'OpenEvn');
					}
				},
				{
					name: 'action_openperson',
					disabled: true,
					visible: !isAdmin,
					icon: 'img/icons/patient16.png',
					tooltip: 'Открыть данные человека',
					text: 'Открыть данные человека',
					handler: function () {
						form.openForm(form.UnionTFOMSErrorGrid, {}, 'OpenPerson');
					}
				},
				{name: '-', visible: !isAdmin},
				{
					hidden: true,
					name: 'action_tehinfo',
					disabled: true,
					visible: true,
					icon: 'img/icons/info16.png',
					tooltip: 'Технические подробности',
					text: 'Технические подробности',
					handler: function () {
						form.openInfoForm(form.UnionTFOMSErrorGrid)
					}
				}

			],
			callbackPersonEdit: function (person, record) {
				
			},
			onRowSelect: function (sm, rowIdx, record) {
				//this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');
				//this.getAction('action_deleteall').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случаи по всем ошибкам' : 'Удалить случаи по всем ошибкам');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_deleteerror', !Ext.isEmpty(record.get('OrgSMO_id')));
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
					this.setActionDisabled('action_tehinfo', false);
				}
			}
		});

		form.UnionTFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
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
			stringfields:
				[
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
							disabled: true,
							handler: this.deleteRegistryDouble.createDelegate(this, ['all'])
						};
						grid.ViewActions[action_delete_all.name] = new Ext.Action(action_delete_all);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_delete_all.name]);
					}
				}.createDelegate(this)
			},
			actions:
				[
					{name:'action_add', hidden: true },
					{name:'action_edit', hidden: true/*, handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DoubleVizitGrid, {}, 'OpenPerson');}*/},
					{name:'action_view', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DoubleVizitGrid, {action: 'view'}, 'swEvnPLEditWindow');} },
					{name:'action_delete', disabled: true, handler: this.deleteRegistryDouble.createDelegate(this, ['current']) }
				],
			onLoadData: function()
			{
			}
		});

		form.DataTab = new Ext.TabPanel({
			border: false,
			region: 'center',
			id: form.id + 'DataTab',
			activeTab:0,
			autoScroll: true,
			defaults: {bodyStyle:'width:100%;'},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(tab, panel) {
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					if (record)
					{
						var Registry_id = record.get('Registry_id');
						var RegistryType_id = record.get('RegistryType_id');
						form.onRegistrySelect(Registry_id, RegistryType_id, true);
					}
				}
			},
			items: [{
				border: false,
				frame: true,
				iconCls: 'info16',
				id: 'tab_registry',
				layout: 'fit',
				title: '0. Реестр',
				items: [
					form.RegistryPanel
				]
			}, {
				title: '1. Данные',
				layout: 'fit',
				id: 'tab_data',
				iconCls: 'good',
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
				border:false,
				items: [form.NoPolisGrid]
			}, {
				title: '5. Итоги проверки ТФОМС',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_datatfomserr',
				border: false,
				items: [{
					border: false,
					layout:'border',
					region: 'center',
					items: [
						form.RegistryTFOMSFiltersPanel,
						form.TFOMSErrorGrid
					]
				}]
			}, {
				title: '6. Ошибки перс. данных',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_databdzerr',
				border:false,
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						form.BDZErrorGrid
					]
				}]
			}, {
				title: '7. Дубли посещений',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_datavizitdouble',
				border: false,
				items: [form.DoubleVizitGrid]
			}]
		});

		form.RegistryListPanel = new sw.Promed.Panel({
			border: false,
			id: form.id+'RegistryListPanel',
			layout:'border',
			defaults: {split: true},
			items: [
				form.AccountGrid,
				form.DataTab
			]
		});

		form.UnionRegistryGrid = new sw.Promed.ViewFrame({
			id: form.id+'UnionRegistryGrid',
			region: 'north',
			height: 203,
			title:'Реестры по СМО',
			object: 'Registry',
			editformclassname: 'swUnionRegistryEditWindow',
			dataUrl: '/?c=Registry&m=loadUnionRegistryGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm, rowIdx, record) {
				var
					Registry_id = record.get('Registry_id'),
					RegistryType_id = record.get('RegistryType_id');

				form.onUnionRegistrySelect(Registry_id, false, record, RegistryType_id);

				form.UnionActionsMenu.items.items[0].hide();
				form.UnionActionsMenu.items.items[1].hide();
				form.UnionActionsMenu.items.items[2].hide();
				form.UnionActionsMenu.items.items[3].hide();
				form.UnionActionsMenu.items.items[4].hide();
				form.UnionActionsMenu.items.items[5].hide();
				form.UnionActionsMenu.items.items[6].hide();
				form.UnionActionsMenu.items.items[7].hide();

				if ( !Ext.isEmpty(record.get('RegistryStatus_id')) ) {
					if (record.get('RegistryStatus_id') == 2) {
						form.UnionActionsMenu.items.items[2].show(); // Экспорт реестров
						form.UnionActionsMenu.items.items[4].show(); // Отметить как оплаченный
						form.UnionActionsMenu.items.items[5].show(); // Снять отметку "к оплате
						form.UnionActionsMenu.items.items[3].show(); // Импорт реестра из СМО
					}

					if (record.get('RegistryStatus_id') == 3) {
						form.UnionActionsMenu.items.items[0].show(); // Переформировать
						form.UnionActionsMenu.items.items[1].show(); // К оплате
						form.UnionActionsMenu.items.items[7].show(); // Удалить реестр (с удалением случаев из предварительных реестров)
					}

					if (record.get('RegistryStatus_id') == 4) {
						form.UnionActionsMenu.items.items[6].show(); // Снять отметку "оплачен"
					}
				}

				if (record.get('RegistryStatus_id') == 4) {
					this.getAction('action_print').menu.printRegistry.setHidden(false);
				} else {
					this.getAction('action_print').menu.printRegistry.setHidden(true);
				}
				if (record.get('RegistryType_id') && record.get('RegistryType_id').inlist([2,7,12,20,11])) {
					this.getAction('action_print').menu.printRegistrySvod.setHidden(false);
				} else {
					this.getAction('action_print').menu.printRegistrySvod.setHidden(true);
				}

				if(record.get('RegistryType_id')==20){
					this.getAction('action_print').menu.printApprovalSheet.setHidden(false);
				} else {
					this.getAction('action_print').menu.printApprovalSheet.setHidden(true);
				}

				form.UnionDataTab.getItem('tab_uniondatatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
			},
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'OrgSmo_Name', id: 'autoexpand', header: 'СМО'},
				/*{name: 'Registry_IsNotInsur', header: 'Незастрахованные', renderer: function(value, cellEl, rec) {
					if (value == 2) {
						return 'Да';
					} else {
						return '';
					}
				}, width: 100},*/
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата счета', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Count', type:'int', header: 'Количество', width: 110},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_updDT', type: 'datetimesec', header: 'Дата изменения', width: 110},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'DispClass_id',  type: 'int', hidden: true},
				{name: 'DispClass_Name',  header: 'Тип диспансеризации', width: 200},
				{name: 'Registry_isPersFin', header: 'Подушевое финансирование', type: 'checkbox', width: 180},
				{name: 'Registry_IsZNO', header: 'ЗНО', type: 'checkbox', width: 50},
				{name: 'Registry_IsRepeated', header: 'Повторная подача', type: 'checkbox', width: 50},
				{name: 'PayType_Name', header: 'Вид оплаты', type: 'string', width: 100},
				{name: 'KatNasel_Name', header: 'Категория населения', type: 'string', width: 100},
				{name: 'PayType_SysNick', type: 'string', hidden: true}
			],
			actions: [
				{ name: 'action_add', disabled: true},
				{ name: 'action_edit', disabled: true},
				{ name: 'action_view' },
				{ name: 'action_delete', hidden: true, disabled: true },
				{
					name: 'action_print',
					menuConfig: {
						printRegistry: { text: 'Печать счёта', handler: function(){ this.printRegistry('union', 1); }.createDelegate(this) },
						printRegistrySvod: { text: 'Печать сводной ведомости ', handler: function(){ this.printRegistry('union', 2); }.createDelegate(this) },
						printApprovalSheet: { text: 'Печать листа согласования',
							handler: function(){
								var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
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
			]
		});

		form.UnionActionsMenu = new Ext.menu.Menu({
			items: [
				{name:'action_reform', text:'Переформировать', disabled: true, handler: function() { this.reformUnionRegistry(); }.createDelegate(this)},
				{name:'action_topay', text:'Отметить к оплате', disabled: true, handler: function() { this.setUnionRegistryStatus(2); }.createDelegate(this)},
				{name:'action_export', text:'Экспорт в XML', handler: function() { this.exportUnionRegistryToXml(); }.createDelegate(this)},
				//{name:'action_importflk', text:'Импорт результата ФЛК (СМО)', handler: function() { this.importRegistryFromTFOMS('union'); }.createDelegate(this)},
				{name:'action_import', text:'Импорт реестра из СМО/ТФОМС', disabled: true, handler: function() { this.importUnionRegistryXML(); }.createDelegate(this)},
				{name:'action_topaid', text:'Отметить как оплаченный', disabled: true, handler: function() { this.setUnionRegistryStatus(4); }.createDelegate(this)},
				{name:'action_frompay', text:'Снять отметку "к оплате"', disabled: true, handler: function() { this.setUnionRegistryStatus(3); }.createDelegate(this)},
				{name:'action_frompaid', text:'Снять отметку "оплачен"', disabled: true, handler: function() { this.setUnionRegistryStatus(2); }.createDelegate(this)},
				{name:'action_deletewithdata', text:'Удалить реестр (с удалением случаев из предварительных реестров)', disabled: true, handler: function() { this.deleteUnionRegistryWithData(); }.createDelegate(this)}
			]
		});


		form.UnionRegistryChildGrid = new sw.Promed.ViewFrame({
			id: form.id+'UnionRegistryChildGrid',
			region: 'center',
			title:'Реестры',
			object: 'Registry',
			dataUrl: '/?c=Registry&m=loadUnionRegistryChildGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 80},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
				{name: 'Registry_updDate', type: 'date', header: 'Дата изменения', width: 110}
			],
			actions: [
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', disabled: true, hidden: true },
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			]
		});

		form.UnionDataTab = new Ext.TabPanel({
			border: false,
			region: 'center',
			activeTab:0,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle:'width:100%;'},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function(tab, panel) {
					var record = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();

					if ( record ) {
						var
							Registry_id = record.get('Registry_id'),
							RegistryType_id = record.get('RegistryType_id');

						form.onUnionRegistrySelect(Registry_id, true, record, RegistryType_id);
					}
				}
			},
			items: [{
				title: '0. Реестры',
				layout: 'fit',
				id: 'tab_registrys',
				iconCls: 'info16',
				border: false,
				items: [
					form.UnionRegistryChildGrid
				]
			}, {
				title: '1. Данные',
				layout: 'fit',
				id: 'tab_uniondata',
				iconCls: 'good',
				border: false,
				items: [{
					border: false,
					layout:'border',
					region: 'center',
					items: [
						form.UnionRegistryDataFiltersPanel,
						form.UnionDataGrid
					]
				}]
			}, {
				title: '2. Итоги проверки ТФОМС / СМО',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_uniondatatfomserr',
				border: false,
				items: [{
					border: false,
					layout:'border',
					region: 'center',
					items: [
						form.UnionRegistryTFOMSFiltersPanel,
						form.UnionTFOMSErrorGrid
					]
				}]
			}]
		});


		form.UnionRegistryListPanel = new sw.Promed.Panel({
			border: false,
			id: form.id + 'UnionRegistryListPanel',
			layout:'border',
			defaults: {split: true},
			items: [form.UnionRegistryGrid, form.UnionDataTab]
		});

		Ext.apply(form, {
			layout:'border',
			defaults: {split: true},
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
				handler: function()  {
					form.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: [ 
				form.Tree, {
					border: false,
					region: 'center',
					layout:'card',
					activeItem: 0,
					id: 'regvRightPanel',
					defaults: {split: true},
					items: [
						form.RegistryListPanel,
						form.UnionRegistryListPanel
					]
				}
			]
		});

		sw.Promed.swRegistryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});