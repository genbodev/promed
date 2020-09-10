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
/*NO PARSE JSON*/
sw.Promed.swRegistryViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	firstRun: true,
	height: 500,
	width: 800,
	id: 'RegistryViewWindow',
	title: WND_ADMIN_REGISTRYLIST, 
	layout: 'border',
	//maximizable: true,
	maximized: true,
	modal: false,
	//plain: true,
	resizable: false,
	firstTabIndex: 15800,
	onTreeClick: function(node,e)
	{
		var win = this;
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;
		win.RegistryErrorFiltersPanel.getForm().reset();
		switch (level)
		{
			case 0: case 1:
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 2:
				// отображение объединённых реестров
				owner.findById('regvRightPanel').setVisible(true);
				owner.findById('regvRightPanel').getLayout().setActiveItem(1);
				var Lpu_id = node.parentNode.attributes.object_value;
				win.UnionRegistryGrid.loadData({params:{Lpu_id:Lpu_id}, globalFilters:{Lpu_id:Lpu_id, start: 0, limit: 100}});
				break;
			case 3:
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 4:
				owner.findById('regvRightPanel').setVisible(true);
				owner.findById('regvRightPanel').getLayout().setActiveItem(0);
				var Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
				var RegistryType_id = node.parentNode.attributes.object_value;
				var RegistryStatus_id = node.attributes.object_value;
				owner.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id!=3));
				owner.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id!=3));
				//log(this.AccountGrid.getGrid().getColumnModel());
				
				// скрываем/открываем колонку
				owner.AccountGrid.setColumnHidden('RegistryStacType_Name', (RegistryType_id!=1 && RegistryType_id!=14));
				
				// Меняем колонки и отображение 
				if (RegistryType_id==1 || RegistryType_id==14)
				{
					// Для стаца одни названия 
					owner.DataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
					owner.DataGrid.setColumnHeader('RegistryData_KdPay', 'К/д к оплате');
					owner.DataGrid.setColumnHeader('RegistryData_KdPlan', 'К/д норматив');
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');
					owner.DataGrid.setColumnHidden('EvnPS_disDate', false);
					owner.DataGrid.setColumnHidden('RegistryData_KdPay', false);
					owner.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
				}
				else 
				{
					// Для остальных - другие 
					owner.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ факт');
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');
					owner.DataGrid.setColumnHidden('EvnPS_disDate', true);
					if (RegistryType_id==2) {
						owner.DataGrid.setColumnHeader('RegistryData_KdPay', 'УЕТ к оплате');
						owner.DataGrid.setColumnHeader('RegistryData_KdPlan', 'УЕТ норматив');
						owner.DataGrid.setColumnHidden('RegistryData_KdPay', false);
						owner.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
					} else {
						owner.DataGrid.setColumnHidden('RegistryData_KdPay', true);
						owner.DataGrid.setColumnHidden('RegistryData_KdPlan', true);
					}
				}
				
				if (RegistryType_id==6) {
					owner.DataGrid.setColumnHeader('LpuSection_name', 'Профиль бригады');
					owner.ErrorGrid.setColumnHeader('LpuSection_name', 'Профиль бригады');
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', 'Профиль бригады');
					owner.DoubleVizitGrid.setColumnHeader('LpuSection_FullName', 'Профиль бригады');
				} else {
					owner.DataGrid.setColumnHeader('LpuSection_name', 'Отделение');
					owner.ErrorGrid.setColumnHeader('LpuSection_name', 'Отделение');
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', 'Отделение');
					owner.DoubleVizitGrid.setColumnHeader('LpuSection_FullName', 'Отделение');
				}

				owner.AccountGrid.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,14])))); // !!! Пока только для полки, потом поправить обратно 

				var dataTab = Ext.getCmp('RegistryViewWindowDataTab');
				var account = Ext.getCmp('RegistryViewWindowAccount');
				if (6 == RegistryStatus_id){
					account.deletedRegistriesSelected = true;
				}  else {
					account.deletedRegistriesSelected = false;
				}
				
				owner.setMenuActions(owner.AccountGrid, RegistryStatus_id, RegistryType_id);
				
				owner.AccountGrid.getAction('action_yearfilter').setHidden( RegistryStatus_id != 4 );
				if( 4 == RegistryStatus_id ) {
					owner.constructYearsMenu({RegistryType_id: RegistryType_id, RegistryStatus_id: RegistryStatus_id, Lpu_id: Lpu_id});
				}
				
				owner.AccountGrid.loadData({params:{RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, Lpu_id:Lpu_id}, globalFilters:{RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, Lpu_id:Lpu_id}});
				break;
		}
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
				},
				icon: Ext.Msg.WARNING,
				msg: 'Ваш запрос на формирование реестра находится в очереди.<br/>'+
				'Позиция вашего запроса в очереди на формирование: <b>'+RegistryQueue_Position+'</b> место.<br/>',
				title: 'Сообщение'
			});
		}
		else 
		{
		}
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
	onRegistrySelect: function (Registry_id, RegistryType_id, nofocus, record)
	{
		var form = this;
		//log('onRegistrySelect/Registry_id='+Registry_id);
		
		RegistryStatus_id = Ext.getCmp('RegistryViewWindowRegistryTree').selModel.selNode.attributes.object_value;
		
        if (form.AccountGrid.getCount()>0)
		{
			switch (form.DataTab.getActiveTab().id)
			{
				case 'tab_registry':
					// бряк!
					break;
					
				case 'tab_data':
					//if ((form.DataGrid.getParam('Registry_id')!=Registry_id) || (form.DataGrid.getCount()==0))
					{
                        form.DataGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_commonerr':
					//if ((form.ErrorComGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorComGrid.getCount()==0))
					{
						form.ErrorComGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_dataerr':
					form.loadErrTypeSpr(Registry_id);
					var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					var begDate = Ext.util.Format.date(reestr.get('Registry_begDate'),'d.m.Y'); //https://redmine.swan.perm.ru/issues/51050
					var endDate = Ext.util.Format.date(reestr.get('Registry_endDate'),'d.m.Y');

					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id,
						dateFrom: begDate,
						dateTo: endDate
					});
					form.RegistryErrorFiltersPanel.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());

					//if ((form.ErrorGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorGrid.getCount()==0))
					{
						form.ErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datanopolis':
					//if ((form.NoPolisGrid.getParam('Registry_id')!=Registry_id) || (form.NoPolisGrid.getCount()==0))
					{
						form.NoPolisGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datapersonerr':
					//if ((form.PersonErrorGrid.getParam('Registry_id')!=Registry_id) || (form.PersonErrorGrid.getCount()==0))
					{
						form.PersonErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datatfomserr':
					//if ((form.TFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.TFOMSErrorGrid.getCount()==0))
					{
						form.TFOMSErrorGrid.loadData({callback: function() {
							form.TFOMSErrorGrid.ownerCt.doLayout();
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
		else 
		{
			switch (form.DataTab.getActiveTab().id)
			{
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
	/** Удаляем запись из реестра
	*/
	deleteRegistryData: function(grid, deleteAll)
	{
		var record = grid.getGrid().getSelectionModel().getSelected();
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
						params: params,
						callback: function(options, success, response) 
						{
							if (success)
							{
								var result = Ext.util.JSON.decode(response.responseText);
								// Перечитываем грид, чтобы обновить данные по счетам
								form.DataGrid.getGrid().getStore().reload();
								if (grid != form.DataGrid) {
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
	deleteRegistryQueue: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		//var Lpu_id = record.get('Lpu_id');
		
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
						url: '/?c=Registry&m=deleteRegistryQueue',
						params: 
						{	
							Registry_id: Registry_id
						},
						callback: function(options, success, response) 
						{
							if (success)
							{
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
	registryRevive: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		//var Lpu_id = record.get('Lpu_id');

		Ext.Ajax.request(
		{
			url: '/?c=Registry&m=reviveRegistry',
			params: 
			{	
				Registry_id: Registry_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					// Перечитываем грид, чтобы обновить данные по счетам
					form.AccountGrid.loadData();
				}
			}
		});
	},
	
	setRegistryStatus: function(RegistryStatus_id) {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;

		if ( !record ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var Registry_id = record.get('Registry_id');

		form.getLoadMask().show();

		Ext.Ajax.request({
			url: '/?c=Registry&m=setRegistryStatus',
			params: {	
				Registry_id: Registry_id,
				RegistryStatus_id: RegistryStatus_id
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
	setRegistryActive: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		//var Lpu_id = record.get('Lpu_id');
		var Registry_IsActive = 1;
		Ext.Ajax.request(
		{
			url: '/?c=Registry&m=setRegistryActive',
			params: 
			{	
				Registry_id: Registry_id,
				Registry_IsActive: Registry_IsActive
				/*Lpu_id: Lpu_id*/
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.Registry_IsActive==Registry_IsActive)
					{
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
	// Пересчет реестра
	refreshRegistry: function() 
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		form.getLoadMask().show();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : Ext.getCmp('RegistryViewWindow'),
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=Registry&m=refreshRegistryData',
						params: 
						{
							Registry_id: Registry_id, 
							RegistryType_id: record.get('RegistryType_id')
						},
						callback: function(options, success, response) 
						{
							form.getLoadMask().hide();
							var r = '';
							if (success) 
							{
								form.AccountGrid.loadData();
								return true;
							}
						}
					});
				} else {
					form.getLoadMask().hide();
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Хотите удалить из реестра все помеченные на удаление записи <br/>и пересчитать суммы?',
			title: 'Вопрос'
		});
		
	},
	exportRegistryToXml: function()
	{
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		
		if (record.get('Registry_Count') == 0 && !isSuperAdmin())
		{
			Ext.Msg.alert('Ошибка', 'Экспорт реестра невозможен, нет случаев для экспорта.<br/>');
			return false;
		}
		
		if (record.get('Registry_IsNeedReform') == 2) {
			Ext.Msg.alert('Ошибка', 'Часть реестров нуждается в переформировании, экспорт не возможен.');
			return false;		
		}
		
		var fd = 'swRegistryXmlWindow';
		var params = {onHide: function() { this.UnionRegistryGrid.loadData(); }.createDelegate(this), Registry_id: record.get('Registry_id'), KatNasel_id: record.get('KatNasel_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=Registry&m=exportRegistryToXml'};
		getWnd(fd).show(params);
	},
	reformRegistry: function(record)
	{
		var current_window = this;
		if ( record.Registry_id > 0 )
		{
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите, идет переформирование реестра...' });
			loadMask.show();
			Ext.Ajax.request(
			{
				url: '/?c=Registry&m=reformRegistry',
				params: 
				{	
					Registry_id: record.Registry_id,
					RegistryType_id: record.RegistryType_id
				},
				callback: function(options, success, response) 
				{
					loadMask.hide();
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if ( result.Error_Msg == '' || result.Error_Msg == null || result.Error_Msg == 'null' )
						{
							// Выводим сообщение о постановке в очередь
							current_window.onIsRunQueue(result.RegistryQueue_Position);
							// Перечитываем грид, чтобы обновить данные по счетам
							current_window.AccountGrid.loadData();
						}
						/*
						else
						{
							Ext.Msg.alert('Ошибка', 'Во время переформирования произошла ошибка<br/>' + result.Error_Msg);
						}
						*/
						
					}
					else
					{
						Ext.Msg.alert('Ошибка', 'Во время переформирования произошла ошибка<br/>');
					}
				},
				timeout: 600000
			});
		}
	},
	reformErrRegistry: function(record)
	{
		var current_window = this;
		if ( record.Registry_id > 0 )
		{
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите, идет постановка реестра в очередь для переформирования по ошибками...' });
			loadMask.show();
			Ext.Ajax.request(
			{
				url: '/?c=Registry&m=reformErrRegistry',
				params: 
				{	
					Registry_id: record.Registry_id,
					RegistryType_id: record.RegistryType_id
				},
				callback: function(options, success, response) 
				{
					loadMask.hide();
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if ( result.Error_Msg == '' || result.Error_Msg == null || result.Error_Msg == 'null' )
						{
							// Выводим сообщение о постановке в очередь
							current_window.onIsRunQueue(result.RegistryQueue_Position);
							// Перечитываем грид, чтобы обновить данные по счетам
							current_window.AccountGrid.loadData();
						}
					}
					else
					{
						Ext.Msg.alert('Ошибка', 'Во время постановки в очередь на переформирование произошла ошибка<br/>');
					}
				},
				timeout: 600000
			});
		}
	},
	importRegistry: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		
		getWnd('swRegistryImportXMLWindow').show({
			callback: Ext.emptyFn,
			onHide: Ext.emptyFn,
			Registry_id: record.get('Registry_id')
		});
	},
	setUnionRegistryMenuActions: function()
	{
		var win = this;
		this.UnionRegistryGrid.setActionHidden('action_print', true);

		this.UnionRegistryGrid.addActions({
			name:'action_actions',
			text:'Действия',
			iconCls: 'actions16',
			menu: [{
				text: 'Экспорт в XML',
				tooltip: 'Экспорт в XML',
				handler: function()
				{
					win.exportRegistryToXml();
				}
			},
			{
				id: 'regvImportRegistry',
				text: 'Импорт ФЛК/МЭК',
				tooltip: 'Импорт ФЛК/МЭК',
				handler: function()
				{
					win.importRegistry();
				}
			}]
		});

		this.UnionRegistryGrid.addActions({
			name:'action_print_alt',
			text:'Печать',
			iconCls: 'print16',
			menu: [
				{name: 'printObject', text: langs('Печать'), handler: function(){win.UnionRegistryGrid.printObject()}},
				{name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function(){win.UnionRegistryGrid.printObjectList()}},
				{name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function(){win.UnionRegistryGrid.printObjectListFull()}},
				{name: 'printObjectAkt', text: langs('Печать акта приема-передачи реестров счетов'), handler: function(){win.showPrintObjectAktWindow()}}
			]
		});


	},
	showPrintObjectAktWindow: function(){

		var win = this,
			record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();

		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		this.printObjectAktWindow = new Ext.Window({
			title: langs('Акт приема-передачи реестров счетов'),
			modal: true,
			resizable: true,
			width: 400,
			layout: 'form',
			autoHeight: true,
			onEsc: function () {
				win.printObjectAktWindow.close();
			},
			items: [{
				xtype: 'form',
				bodyStyle: 'padding: 10px',
				autoHeight: true,
				items: [
					{
						disabledClass: 'field-disabled',
						fieldLabel: langs('Номер акта'),
						allowBlank: false,
						name: 'AktNum',
						xtype: 'textfield'
					}, {
						xtype: 'swdatefield',
						format: 'd.m.Y',
						allowBlank: false,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'AktDate',
						value: new Date(),
						fieldLabel: langs('Дата')
					}
				]
			}],
			renderTo: Ext.getBody(),
			buttonAlign: 'left',
			buttons: [{
				iconCls: 'print16',
				text: langs('Печать'),
				handler: function () {

					var form = win.printObjectAktWindow.findByType('form')[0].getForm();

					if(form.isValid()){
						printBirt({
							'Report_FileName': 'Akt_registry.rptdesign',
							'Report_Params': '&paramRegistry=' + record.get('Registry_id') + '&paramAktDate=' + form.findField('AktDate').getRawValue() + '&paramAktNum=' + form.findField('AktNum').getValue(),
							'Report_Format': 'pdf'
						});
						win.printObjectAktWindow.close();
					}
				}
				},
				{
					text: '-'
				},
				{
				iconCls: 'close16',
				text: langs('Закрыть'),
				handler: function () {
					win.printObjectAktWindow.close();
				}
			}]
		});
		win.printObjectAktWindow.show();
	},
	setMenuActions: function (object, RegistryStatus_id, RegistryType_id)
	{
		var form = this;
		var menu = new Array();
		
		if (!this.menu) 
			this.menu = new Ext.menu.Menu({id:'RegistryMenu'});
		
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
		switch (RegistryStatus_id)
		{
			case 6: 
				// Удаленные 
				menu = 
				[{
					text: 'Восстановить',
					tooltip: 'Восстановить удаленный реестр',
					handler: function() 
					{
						sw.swMsg.show(
							{
								buttons: Ext.Msg.YESNO,
								scope : Ext.getCmp('RegistryViewWindow'),
								fn: function(buttonId) 
								{
									if ( buttonId == 'yes' )
									{
										form.registryRevive();
									}
								},
								icon: Ext.Msg.QUESTION,
								msg: 'Вы действительно хотите восстановить выбранный реестр?',
								title: 'Восстановление реестра'
							}
						);
					}
				}]; 
				break;
				
			case 5: 
				// В очереди 
				menu = 
				[{
					text: 'Удалить реестр из очереди',
					tooltip: 'Удалить реестр из очереди',
					handler: function() 
					{
						form.deleteRegistryQueue();
					}
				}]; // Здесь надо сделать отмену очереди 
				break;
		
			case 3: 
				// В работе 
				menu = 
				[{
					text: 'Отметить к оплате',
					tooltip: 'Отметить к оплате',
					handler: function() 
					{
						form.setRegistryStatus(2);
					}
				}];
				if (RegistryType_id==2 && false) // (убрал refs #13862)
				{
					menu[1] = 
					{
						text: 'Переформировать по ошибкам',
						tooltip: 'Переформировать реестр по ошибкам',
						handler: function() 
						{
							var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
							if (!record || !(record.get('Registry_id') > 0) )
							{
								Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
								return false;
							}
							else
							{
								var rec = {Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id')};
								form.reformErrRegistry(record.get('Registry_id'));
							}
						}
					};
				}
				else 
				{
					menu[1] = '-';
				}
				menu[2] =  {
					text: 'Переформировать весь реестр',
					tooltip: 'Переформировать весь реестр',
					handler: function() 
					{
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
						if (!record || !(record.get('Registry_id') > 0) )
						{
							Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
							return false;
						}
						else
						{
							var rec = {Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id')};
							form.reformRegistry(rec);
						}
					}
				};
				menu[3] = '-';
				menu[4] =  {
					text: 'Пересчитать реестр',
					tooltip: 'Пересчитать реестр',
					handler: function() 
					{
						form.refreshRegistry();
					}
				};
				break;
			case 2: // К оплате
				menu = 
				[{
					text: 'Пересчитать реестр',
					tooltip: 'Пересчитать реестр',
					handler: function() 
					{
						form.refreshRegistry();
					}
				},
				'-',
				{
					text: 'Снять отметку "к оплате"',
					tooltip: 'Снять отметку "к оплате"',
					handler: function() 
					{
						form.setRegistryStatus(3);
					}
				},
				{
					text: 'Отметить как оплаченный',
					hidden: true,
					tooltip: 'Отметить как оплаченный',
					handler: function() 
					{
						form.setRegistryStatus(4);
					}
				}];
				break;
			case 4: // Оплаченные 
				menu = 
				[{
					text: 'Снять активность',
					tooltip: 'Снять активность',
					handler: function() 
					{
						form.setRegistryActive();
					}
				},
				{
					text: 'Снять отметку "оплачен"',
					hidden: true,
					tooltip: 'Снять отметку "оплачен"',
					handler: function() 
					{
						form.setRegistryStatus(2);
					}
				}];
				break;
			default:
				Ext.Msg.alert('Ошибка', 'Значение статуса неизвестно! Значение статуса: ' + RegistryStatus_id);
		}
		
		this.menu.removeAll();
		for (key in menu)
		{
			if (key!='remove')
				this.menu.add(menu[key]);
		}
		this.menu.add('-');
		this.menu.add({
			id: 'regvRegistryErrorExport',
			text: 'Экспорт протоколов',
			tooltip: 'Экспорт протоколов',
			handler: function() 
			{
				var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				if (!reestr)
				{
					Ext.Msg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.<br/>');
					return false;
				}
				var Registry_id = reestr.get('Registry_id');
				var RegistryErrorTFOMSType_id = reestr.get('RegistryErrorTFOMSType_id');
				getWnd('swRegistryErrorExportWindow').show({Registry_id: Registry_id, RegistryErrorTFOMSType_id :RegistryErrorTFOMSType_id});
			}
		});
		return true;
	},
	doRegistryPersonIsDifferent: function (grid) {
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			scope : this,
			fn: function(buttonId) 
			{
				if ( buttonId == 'yes' )
				{
					var record = grid.getGrid().getSelectionModel().getSelected();
					if (record && record.get('isNoEdit')==2)
					{
						return false;
					}
					
					this.getLoadMask().show();
					
					Ext.Ajax.request({
						url: '/?c=Registry&m=doRegistryPersonIsDifferent',
						success: function(result){
							if ( result.responseText.length > 0 ) {
								var resp_obj = Ext.util.JSON.decode(result.responseText);
								if (resp_obj.success == true) {
									grid.getGrid().getStore().reload();
								}
							}
							this.getLoadMask().hide();
						}.createDelegate(this),
						params: {
							'Registry_id': record.get('Registry_id'),
							'MaxEvnPerson_id': record.get('MaxEvnPerson_id')
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
			msg: 'Вы уверены что это не двойник?',
			title: 'Вопрос'
		});
	},
	
	doPersonUnion: function (grid) {

		// Формируем список двойников, основная запись Person2_id
		
		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			scope : this,
			fn: function(buttonId) 
			{
				if ( buttonId == 'yes' )
				{
					var record = grid.getGrid().getSelectionModel().getSelected();
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
			case 35:
				config.open_form = 'EvnPLWOWEditWindow';
				config.key = 'EvnPLWOW_id';
				break;
			case 43:
				config.open_form = 'swEvnUslugaOperEditWindow';
				config.key = 'EvnUslugaOper_id';
				break;
			case 47:
				config.open_form = 'swEvnUslugaParEditWindow';
				config.key = 'EvnUslugaPar_id';
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
				case 'Person': case 'OpenPerson':
					type = 108;
					break;
				case 'MedPersonal':
					type = 107;
					break;
				case 'OpenEvn':
					type = 2;
					break;
			}
		}
		
		if (object.id == this.id+'NoPolis') // Если это с грида "Нет полиса"
		{
			type = 108;
		}
		/*if (object.id == 'RegistryViewWindowTFOMSError') // Если это с грида "Ошибки ТФОМС"
		{
			if (frm=='OpenPerson') {
				type = 108;
			}
		}*/
		if (frm=='OpenPerson') {
			type = 108;
		}
		
		var id = record.get('Evn_rid') ||  record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное 
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
			case 16:
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
			case 3:
				open_form = 'swEvnReceptEditWindow';
				key = 'EvnRecept_id';
				break;
			case 4:
				open_form = 'swEvnPLDispDopEditWindow';
				key = 'EvnPLDispDop_id';
				break;
			case 5:
				open_form = 'swEvnPLDispOrpEditWindow';
				key = 'EvnPLDispOrp_id';
				break;
			case 6:
				open_form = 'swCmpCallCardNewCloseCardWindow';
				key = 'CmpCloseCard_id';
				break;
			case 7:
				open_form = 'swEvnPLDispDop13EditWindow';
				key = 'EvnPLDispDop13_id';
				DispClass_id = record.get('DispClass_id');
				break;
			case 8:
				open_form = 'swEvnPLDispDop13EditWindow';
				key = 'EvnPLDispDop13_id';
				break;
			case 9:
				open_form = 'swEvnPLDispOrp13EditWindow';
				key = 'EvnPLDispOrp_id';
				break;
			case 10:
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
			case 15:
				var config = form.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;

				id = record.get('Evn_id');
				break;
			case 108:
				open_form = 'swPersonEditWindow';
				key = 'Person_id';
				id = record.get('Person_id');
				break;
			case 107:
				open_form = 'swMedStaffFactEditWindow';
				
				var MedStaffFact_id = record.get('MedStaffFact_id');
				var MedPersonal_id = record.get('MedPersonal_id');
				var LpuSection_id = record.get('LpuUnit_id');
				var LpuUnit_id = record.get('LpuSection_id');
				var Lpu_id = getGlobalOptions().lpu_id;
				var lpuStruct = new Array();
				lpuStruct.LpuUnit_id = String(LpuUnit_id) == 'null' ? null : String(LpuUnit_id);
				lpuStruct.LpuSection_id = String(LpuSection_id) == 'null' ? null : String(LpuSection_id);
				lpuStruct.Lpu_id = String(Lpu_id) == 'null' ? null : String(Lpu_id);
				//lpuStruct.LpuBuilding_id = null;
				lpuStruct.description = '';
				if (MedStaffFact_id)
					window.gwtBridge.runWorkPlaceEditor(getPromedUserInfo(), String(MedStaffFact_id), lpuStruct, function(result) {});
				else 
					Ext.Msg.alert('Ошибка', '<b>Невозможно определить место работы врача</b>: <br/>Cкорее всего он уже не работает в том отделении, в котором был выполнен случай.');
				return;
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

		if ( this.firstRun == true ) {
			this.firstRun = false;
		}
		else {
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
		}

		this.maximize();
		if (isLpuAdmin() || isSuperAdmin() || isRegistryUser()) {
			this.getReplicationInfo();
		}
		this.setUnionRegistryMenuActions();
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
						parentAction = grid.getTopToolbar().items.items[10];
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
					if( new RegExp((new Date()).getFullYear(), 'ig').test(parentAction.menu.items.items[0].text) )
						parentAction.menu.items.items[0].setVisible(false);
				}
			}.createDelegate(this)
		});
	},
	overwriteRegistryTpl: function(record)
	{
		var form = this;
		
		var sparams = {
			Registry_Num: record.get('Registry_Num'),
			KatNasel_Name: record.get('KatNasel_Name'),
			LpuBuilding_Name: record.get('LpuBuilding_Name'),
			// RegistryCheckStatus_Name: record.get('RegistryCheckStatus_Name'),
			PayType_Name: record.get('PayType_Name'),
			Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'),
			Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'),
			Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'),
			ReformTime:record.get('ReformTime'),
			Registry_Count: '<div style="padding:2px;font-size: 12px;">Количество записей в реестре: '+record.get('Registry_Count')+'</div>',
			Registry_Sum: sw.Promed.Format.rurMoney(record.get('Registry_Sum')),
			Registry_SumPaid: sw.Promed.Format.rurMoney(record.get('Registry_SumPaid')),
			RegistryPaid_Count: record.get('Registry_Count') - record.get('RegistryNoPaid_Count'),
			Registry_IsNeedReform: record.get('Registry_IsNeedReform'),
			RegistryNoPay_UKLSum: '<div style="padding:2px;font-size: 12px;color:maroon;">Сумма без оплаты: '+sw.Promed.Format.rurMoney(record.get('Registry_Sum') - record.get('Registry_SumPaid'))+'</div>',
			RegistryNoPaid_Count: '<div style="padding:2px;font-size: 12px;color:maroon;">Количество записей без оплаты: '+record.get('RegistryNoPaid_Count')+'</div>'
		};
							
		if (record.get('RegistryType_id')==1 || record.get('RegistryType_id')==14)
		{
			sparams['Registry_Count'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество госпитализаций, факт: '+record.get('Registry_Count')+'</div>';
			sparams['Registry_RecordPaidCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество госпитализаций, к оплате: '+record.get('Registry_RecordPaidCount')+'</div>';
			sparams['Registry_KdCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество койкодней, факт: '+record.get('Registry_KdCount')+'</div>';
			sparams['Registry_KdPaidCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество койкодней, к оплате: '+record.get('Registry_KdPaidCount')+'</div>';
		}
							
		form.RegistryTpl.overwrite(form.RegistryPanel.body, sparams);
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

		var sortedRecords = [];
		var Evn_ids = [];
		var evn_ids = [];
		var evn_ids_delete = [];
		var evn_ids_recovery = [];
		var count_delete_evn = 0;
		var count_recovery_evn = 0;

		for(var rec in records){
			if(typeof(records[rec].data) == 'object'){
				//log(rec+'>> ', records[rec].data.Evn_id, records[rec].data.RegistryData_deleted);
				if(records[rec].data.RegistryData_deleted == 1 || records[rec].data.RegistryData_deleted == ''){
					count_delete_evn++;
					evn_ids_delete.push(records[rec].data.Evn_id)
				}
				else if(records[rec].data.RegistryData_deleted == 2){
					count_recovery_evn++;
					evn_ids_recovery.push(records[rec].data.Evn_id)
				}

				evn_ids.push(records[rec].data.Evn_id);
			}
		}

		if(records){
			//если выделена одна запись
			//log('count', Object.keys(records).length)
			if(Object.keys(records).length == 1 && act =='delete'){
				if(record.data.RegistryData_deleted == 2){
					act = 'unDelete';
					//log('delete', record.data.RegistryData_deleted);
				}
				else if(record.data.RegistryData_deleted == 1){
					act = 'delete';
					//log('unDelete', record.data.RegistryData_deleted);
				}
			}

			params.Filter = null;

			if(act == 'deleteAllSelected' || act == 'delete'){
				if(count_delete_evn == 0){
					Ext.Msg.alert('Сообщение', 'Нет записей нуждающихся в удалении или записи уже помечены на удаление');
					return;
				}
				//log('Удалить все отмеченные');
				params.Type_select = 0;
				params.RegistryData_deleted = 1;
				params.Evn_ids = Ext.util.JSON.encode(evn_ids_delete);

				var msg = '<b>Вы действительно хотите удалить выбранные записи <br/>из реестра?</b><br/><br/>'+
					'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Выбранные записи ' +
					'пометятся как удаленные и будут удалены из реестра при выгрузке (отправке) реестра. ' +
					'Сумма реестра будет пересчитана также при выгрузке (отправке) реестра </span>';
			}
			else if(act == 'unDeleteAllSelected' || act == 'unDelete'){
				if(count_recovery_evn == 0){
					Ext.Msg.alert('Сообщение', 'Нет записей нуждающихся в восстановлении');
					return;
				}
				//log('Восстановить все отмеченные');
				params.RegistryData_deleted = 2;
				params.Type_select = 0;
				params.Evn_ids = Ext.util.JSON.encode(evn_ids_recovery);

				var msg = '<b>Хотите восстановить помеченные на удаление записи?</b><br/><br/>';
			}
			showMessage = true;

		}

		//delete params.Filter;
		//log('PARAMS', params);

		if(showMessage){
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
											var result = Ext.util.JSON.decode(response.responseText);
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
	initComponent: function() 
	{
		var form = this;
		this.TreeToolbar = new Ext.Toolbar(
		{
			id : form.id+'Toolbar',
			items:
			[
				{
					xtype : "tbseparator"
				}
			]
		});
		
		this.Tree = new Ext.tree.TreePanel(
		{
			id: form.id+'RegistryTree',
			animate: false,
			autoScroll: true,
			split: true,
			region: 'west',
			root: 
			{
				id: 'root',
				nodeType: 'async',
				text: 'Реестры',
				expanded: true
			},
			rootVisible: false,
			tbar: form.TreeToolbar,
			width: 250,
			loader: new Ext.tree.TreeLoader(
			{
				dataUrl: '/?c=Registry&m=loadRegistryTree',
				listeners: 
				{
					beforeload: function (loader, node) 
					{
						loader.baseParams.level = node.getDepth();
					},
					load: function (loader, node) 
					{
						// Если это родитель, то накладываем фокус на дерево взависимости от настроек
						if (node.id == 'root')
						{
							if ((node.getOwnerTree().rootVisible == false) && (node.hasChildNodes() == true))
							{
								var child = node.findChild('object', 'Lpu');
								if (child)
								{
									node.getOwnerTree().fireEvent('click', child);
									child.select();
									child.expand();
								}
							}
							else 
							{
								node.getOwnerTree().fireEvent('click', node);
								node.select();
							}
						}
					}
				}
			})
		});
		// Выбор ноды click-ом
		this.Tree.on('click', function(node, e) 
		{
			form.onTreeClick(node, e);
		});
		
		
		this.AccountGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'Account',
			region: 'north',
			height: 203,
			title:'Счет',
			object: 'Registry',
			editformclassname: 'swRegistryEditWindow',
			dataUrl: '/?c=Registry&m=loadRegistry',
			/*paging: true,*/
			autoLoadData: false,
			stringfields:
			[
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
                {name: 'Lpu_id', type: 'int', header: 'Lpu_id', hidden:!isSuperAdmin()},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'Registry_Num', header: 'Номер счета', width: 80},
				{name: 'ReformTime',hidden: true},
				{name: 'Registry_accDate', type:'date', header: 'Дата счета', width: 80},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 80},
				{name: 'Registry_RecordPaidCount', type: 'int', hidden: true},
				{name: 'Registry_KdCount', type: 'int', hidden: true},
				{name: 'Registry_KdPaidCount', type: 'int', hidden: true},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130, hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
				{name: 'RegistryStacType_Name', header: 'Тип стац.', width: 140},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110},
				{name: 'Registry_sendDate', header: 'Последняя отправка', width: 110},
				{name: 'RegistryErrorCom_IsData', type: 'int', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryPerson_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPolis_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPay_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPaid_Count', type: 'int', hidden: true},
				{name: 'RegistryNoPay_UKLSum', type: 'float', hidden: true},
				{name: 'RegistryCheckStatus_Code', type: 'int', hidden: true},
				// {name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200},
				{name: 'issetDouble', hidden: true},
				{name: 'PayType_Name', hidden: true},
				{name: 'RegistryErrorTFOMSType_id', hidden:true}
				
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete', url: '/?c=Registry&m=deleteRegistry', msg: '<b style="color:maroon;">В большинстве случаев гораздо удобнее переформировать реестр, <br/>предварительно исправив ошибки, чем удалить и создать новый.</b> <br/>Вы действительно хотите удалить реестр?'},
				{
					name:'action_print',
					hidden: false,
					handler: function() {
						var current_window = Ext.getCmp('RegistryViewWindow');
						var record = current_window.AccountGrid.getGrid().getSelectionModel().getSelected();
						if (!record)
						{
							Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
							return false;
						}
						var Registry_id = record.get('Registry_id');
						if ( !Registry_id )
							return false;
						var id_salt = Math.random();
						var win_id = 'print_schet' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=Registry&m=printRegistry&Registry_id=' + Registry_id, win_id);
					}, 
					text: 'Печать счета'
				}
			],
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('RegistryViewWindow');
				var r = records.RegistryQueue_Position;
				form.onIsRunQueue(r);
			},
			onLoadData: function()
			{
				//this.getAction('action_add').setDisabled(this.getParam('RegistryStatus_id')!=3);
				if  (this.getAction('action_new'))
				{
					this.getAction('action_new').setDisabled(this.getCount()==0);
				}
			},
			onRowSelect: function(sm,index,record)
			{
				//log(this.id+'.onRowSelect');
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.getCount()>0)
				{
					if(record.get('RegistryStatus_id') == 3) {
						var sel_node = form.Tree.getSelectionModel().getSelectedNode();
						// Только если выбран раздел "в работе"
						if(sel_node.attributes.object_value && sel_node.attributes.object_value == 3) {
							this.ViewActions.action_new.initialConfig.menu.items.items[0].setVisible( record.get('issetDouble') == 0 );
						}
					}

					var Registry_id = record.get('Registry_id');
					var RegistryType_id = record.get('RegistryType_id');
					var RegistryStatus_id = record.get('RegistryStatus_id');
					
					// Убрать кнопку Печать счета иногородним в полке и стаце (refs #1595)
					// Пока убираем учет категории населения https://redmine.swan.perm.ru/issues/66261
					if (false && RegistryType_id.inlist([1,2,14]) && record.get('KatNasel_id') == 2) {
						this.setActionDisabled('action_print', true);
					} else {
						this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,14]))));
					}
					
					form.onRegistrySelect(Registry_id, RegistryType_id,  false, record);
					this.setActionDisabled('action_edit',(record.get('RegistryStatus_id')!=3)); // #61531
					this.setActionDisabled('action_delete',(record.get('RegistryStatus_id')!=3)); // #61531
					this.setActionDisabled('action_view',false);
					if (record.get('RegistryStatus_id')!=5)
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(false);
					// В прогрессе
					if (record.get('Registry_IsProgress')==1)
					{
						this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete',true);
						this.setActionDisabled('action_view',true);
						if (record.get('RegistryStatus_id')!=5)
							Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(true);
					}
					if (record.get('RegistryStatus_id')==4)
					{
						// разрешить-запретить снятие активности
						//log(form.AccountGrid.getAction('action_new').initialConfig.menu.items.itemAt(0));
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(!(record.get('Registry_IsActive')==2));
					}
					
					var account = Ext.getCmp('RegistryViewWindowAccount');
					var deletedRegistriesSelected = account.deletedRegistriesSelected;
					// Дисаблим акшены по статусу отправки 
					// Если полка или стац или смп
					// Если не в папке удаленных
					if (!deletedRegistriesSelected){
						if (record.get('RegistryType_id').inlist(['1','2','6','14'])) {
							// Если к оплате 
							if (record.get('RegistryStatus_id')=='2' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(3).setVisible(isSuperAdmin() || isRegistryUser() || isLpuAdmin(record.get('Lpu_id'))/* || record.get('KatNasel_SysNick') == 'inog'*/); // суперадмин или иногородние (refs #12116)
							}
							// Если оплачен
							if (record.get('RegistryStatus_id')=='4' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setVisible(isSuperAdmin() || isRegistryUser() || isLpuAdmin(record.get('Lpu_id'))/* || record.get('KatNasel_SysNick') == 'inog'*/); // суперадмин или иногородние (refs #12116)
							}
							// Если в работе
							if (record.get('RegistryStatus_id')=='3' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setDisabled(!Ext.isEmpty(record.get('RegistryCheckStatus_Code')) && record.get('RegistryCheckStatus_Code').inlist([0,1]) && !isAdmin);
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setDisabled(!Ext.isEmpty(record.get('RegistryCheckStatus_Code')) && record.get('RegistryCheckStatus_Code').inlist([0,1]) && !isAdmin);
							}
						} else {
							// Если к оплате 
							if (record.get('RegistryStatus_id')=='2' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(5)) {
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(3).setVisible(true);
							}
							// Если оплачен
							if (record.get('RegistryStatus_id')=='4' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setVisible(true);
							}
						}
					}

					//Для папки с удаленными реестрами дизаблим контролы
					if (deletedRegistriesSelected){
						account.setActionDisabled('action_add',true);
						account.setActionDisabled('action_edit',true);
						account.setActionDisabled('action_delete',true);
						account.setActionDisabled('action_view',true);
					}
					form.RegistryPanel.show();

					form.overwriteRegistryTpl(record);
				}
				else
				{
					this.setActionDisabled('action_print',true);
					switch (form.DataTab.getActiveTab().id)
					{
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
				form.DataTab.getItem('tab_registry').setIconClass((record.get('Registry_IsNeedReform')==2)?'delete16':'info16');
				form.DataTab.getItem('tab_commonerr').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_dataerr').setIconClass((record.get('RegistryError_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datapersonerr').setIconClass((record.get('RegistryPerson_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopolis').setIconClass((record.get('RegistryNoPolis_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datavizitdouble').setIconClass((record.get('issetDouble')==1)?'usluga-notok16':'good');
				
				form.DataTab.syncSize();
			}
		});

		this.AccountGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
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
		var RegTplMark = 
		[
			'<div style="padding:2px;font-size: 12px;font-weight:bold;">Реестр № {Registry_Num}<tpl if="Registry_IsNeedReform == 2"> <span style="color: red;">(НУЖДАЕТСЯ В ПЕРЕФОРМИРОВАНИИ!)</span></tpl></div>'+
			'<div style="padding:2px;font-size: 12px;">Вид оплаты: {PayType_Name}</div>'+
			//'<div style="padding:2px;font-size: 12px;">Категория населения: {KatNasel_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Подразделение: {LpuBuilding_Name}</div>'+
			// '<div style="padding:2px;font-size: 12px;">Статус: {RegistryCheckStatus_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:2px;font-size: 12px;">Дата переформирования реестра: {ReformTime}</div>'+
			'<div style="padding:2px;font-size: 12px;">Итоговая сумма: {Registry_Sum}</div>'+
			'<div style="padding:2px;font-size: 12px;">Сумма к оплате: {Registry_SumPaid}</div>'+
			'<div style="padding:2px;font-size: 12px;">Количество оплаченных случаев: {RegistryPaid_Count}</div>'+
			'{Registry_Count}'+
			'{Registry_RecordPaidCount}'+
			'{Registry_KdCount}'+
			'{Registry_KdPaidCount}'+
			'{RegistryNoPay_UKLSum}'+
			'{RegistryNoPaid_Count}'
		];
		this.RegistryTpl = new Ext.XTemplate(RegTplMark);
		
		this.RegistryPanel = new Ext.Panel(
		{
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
		this.DataGridSearch = function()
		{
			var form = this;
			var filtersForm = form.RegistryDataFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if (Registry_id > 0)
			{
				form.DataGrid.loadData(
				{
					globalFilters:
					{
						Registry_id: Registry_id, 
						RegistryType_id: RegistryType_id, 
						Person_SurName:filtersForm.findField('Person_SurName').getValue(), 
						Person_FirName:filtersForm.findField('Person_FirName').getValue(), 
						Person_SecName:filtersForm.findField('Person_SecName').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						filterRecords: filtersForm.findField('filterRecords').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(), 
						start: 0,
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}
		
		// Кнопка "Поиск"
		var rvwDGBtnSearch = new Ext.Button(
		{
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwDGBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png', 
			iconCls : 'x-btn-text',
			disabled: false, 
			handler: function() 
			{
				Ext.getCmp('RegistryViewWindow').DataGridSearch();
			}
		});
		rvwDGBtnSearch.tabIndex = form.firstTabIndex+16;

		this.DataGridReset = function(){
			var form = this;
			var filtersForm = form.RegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.DataGrid.removeAll(true);
			form.DataGridSearch();
		};
		// Кнопка Сброс
		var rvwDGBtnReset = new Ext.Button({
			text: BTN_FRMRESET,
			icon: 'img/icons/reset16.png',
			iconCls : 'x-btn-text',
			disabled: false,
			style: 'margin-left: 4px;',
			handler: function(){
				Ext.getCmp('RegistryViewWindow').DataGridReset();
			}
		});
		rvwDGBtnReset.tabIndex = form.firstTabIndex+17;
		
		this.RegistryDataFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
			id: 'RegistryDataFiltersPanel',
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					Ext.getCmp('RegistryViewWindow').DataGridSearch();
				},
				stopEvent: true
			}],
			items: 
			[{
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 100,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvwDGPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+10
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvwDGPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+11
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 60,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvwDGPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+12
					}]
				}, {
						layout: 'form',
						border: false,
						//columnWidth: .05,
						items: [{
							xtype: 'checkbox',
							boxLabel: langs('Группировать по законченным случаям'),
							hideLabel: true,
							tabIndex: form.firstTabIndex+12.5,
							listeners: {
								check: function(cmp, checked){
									if(checked){
										form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
									}else{
										form.DataGrid.getGrid().getStore().clearGrouping()
									}
									form.DataGrid.showGroup = checked;
									form.DataGrid.getGrid().view.refresh(true);
								}
							}
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
					hidden: !isAdmin,
					labelWidth: 100,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						id: 'rvwDGPolis_Num',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex:form.firstTabIndex+13
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 65,
					items: 
					[{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex + 14,
						xtype: 'numberfield'
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items: 
					[{
						anchor: '100%',
						xtype: 'combo',
						id: 'rvwDGPfilterRecords',
						listWidth: 200,
						hideLabel: true,
						name: 'filterRecords',
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
						tabIndex:form.firstTabIndex+15
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding: 4px;background:#DFE8F6;',
					items: [rvwDGBtnSearch]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding: 4px;background:#DFE8F6;',
					items: [rvwDGBtnReset]
				}]
			}]
		});
		
		this.ErrorGridSearch = function()
		{
			var form = this;
			var filtersForm = form.RegistryErrorFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if (Registry_id > 0)
			{
				form.ErrorGrid.loadData(
				{
					globalFilters:
					{
						Registry_id: Registry_id, 
						RegistryType_id: RegistryType_id, 
						Person_SurName:filtersForm.findField('Person_SurName').getValue(), 
						Person_FirName:filtersForm.findField('Person_FirName').getValue(), 
						Person_SecName:filtersForm.findField('Person_SecName').getValue(),
						RegistryErrorType_id:filtersForm.findField('RegistryErrorType_id').getValue(),
						MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						start: 0, 
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}
		
		var rvwREBtnSearch = new Ext.Button(
		{
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwREBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png', 
			iconCls : 'x-btn-text',
			disabled: false, 
			handler: function() 
			{
				Ext.getCmp('RegistryViewWindow').ErrorGridSearch();
			}
		});
		rvwREBtnSearch.tabIndex = form.firstTabIndex+22;
		
		this.RegistryErrorFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
			id: 'RegistryErrorFiltersPanel',
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					Ext.getCmp('RegistryViewWindow').ErrorGridSearch();
				},
				stopEvent: true
			}],
			items: 
			[{
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 100,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						id: 'rvwREPerson_SurName',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+17
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Имя',
						id: 'rvwREPerson_FirName',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+18
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 60,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Отчество',
						id: 'rvwREPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+19
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
					labelWidth: 50,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Ошибка',
						id: 'rvwRERegistryErrorType_id',
						name: 'RegistryErrorType_id',
						xtype: 'swregistryerrortypecombo',
						tabIndex:form.firstTabIndex+20
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 65,
					items: 
					[{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex + 21,
						xtype: 'numberfield'
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 30,
					items:
						[{
							anchor: '100%',
							hiddenName: 'MedPersonal_id',
							lastQuery: '',
							listWidth: 650,
							editable: true,
							tabIndex: form.firstTabIndex + 22,
							allowBlank: true,
							xtype: 'swmedpersonalcombo'
						}]
				},
				{
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					columnWidth: .1,
					items: [rvwREBtnSearch]
				}]
			}]
		});
		
		// Данные реестра 
		this.DataGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'Data',
			title:'Реестр ОМС',
			object: 'RegistryData',
			region: 'center',
			dataUrl: '/?c=Registry&m=loadRegistryData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			grouping: true,
			showGroup: false,
			groupSortInfo: {
				field: 'MaxEvn_id'
			},
			selectionModel: 'multiselect',
			stringfields:
			[
				{name: 'Evn_id', type: 'int', header: 'ИД случая', key: true, hidden:false},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},

				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'PersonEvn_startid', type: 'int', hidden:true},
				{name: 'IsRDL', type: 'int', hidden:true},
				{name: 'needReform', type: 'int', hidden:true},
				{name: 'isNoEdit', type: 'int', hidden:true},
				{name: 'EvnPL_NumCard', header: '№ талона', width: 60},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'К/д факт', width: 70},
				{name: 'RegistryData_KdPlan', header: 'К/д норматив', width: 70},
				{name: 'RegistryData_KdPay', header: 'К/д к оплате', width: 70},
				{name: 'RegistryData_Tariff', type: 'money', header: 'Тариф', width: 70},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
				{name: 'PayMedType_Code', header: 'Код способа оплаты', width: 90},
				{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
				{name: 'Err_Count', hidden:true},
				{name: 'ErrTfoms_Count', hidden:true},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'RegistryData_IsPaid', type: 'int', hidden:true},
				{name: 'MaxEvn_id', type: 'int', hidden:true, group: true}
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
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: false, handler: function() { form.deleteRegistryData(form.DataGrid, false); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {}, 'OpenPerson');}}
				
			],
			callbackPersonEdit: function(person, record)
			{
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.selectedRecord)
				{
					record = this.selectedRecord;
				}
				if (!record)
				{
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if (!record)
				{
					return false;
				}
				form.setNeedReform(record);
			},
			onLoadData: function()
			{
				var RegistryStatus_id = Ext.getCmp('RegistryViewWindowRegistryTree').selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3));
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
				if(this.showGroup){
					form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
				}else{
					form.DataGrid.getGrid().getStore().clearGrouping()
				}
				this.getGrid().getSelectionModel().clearSelections();
			},
			onDblClick: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				if(RegistryStatus_id==4) return false;
				Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});
			},
			onEnter: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				if(RegistryStatus_id==4) return false;
				Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});
			},
			onRowSelect: function(sm,rowIdx,record)
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

				// Меняем текст акшена удаления в зависимости от данных
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');
				this.setActionDisabled('action_edit',RegistryStatus_id==4);
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
		this.DataGrid.ViewGridModel.singleSelect = false;
		//Task# фильтр грида
		//Интеграция фильтра к Grid
		columnsFilter = ['Evn_id','EvnPL_NumCard','Person_FIO','LpuSection_name','MedPersonal_Fio'];
		configParams = {url : '/?c=Registry&m=loadRegistryDataFilter'};
		_addFilterToGrid(this.DataGrid,columnsFilter,configParams);

		this.DataGrid.getGrid().view = new Ext.grid.GroupingView({
			forceFit:true,
			getRowClass : function (row, index)
			{
				var cls = '';

				if ((row.get('IsRDL')>0) && (isAdmin))
					cls = cls+'x-grid-rowblue ';
				if (row.get('Err_Count') > 0 || row.get('ErrTfoms_Count') > 0 || row.get('RegistryData_IsPaid') == 1)
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
			listeners:
			{
				rowupdated: function(view, first, record)
				{
					//log('update');
					view.getRowClass(record);
				}
			},
			startGroup: new Ext.XTemplate(
				'<div id="{groupId}" class="x-grid-group {cls}" >',
				'<div id="{groupId}-hd" class="x-grid-group-hd" ' +
				'<tpl if="this.isGroup(values)">',
				'style="display: none"',
				'</tpl>',
				'><div> {[this.getGroupName(values)]} </div></div>',
				'<div id="{groupId}-bd" class="x-grid-group-body">',
				{
					isGroup: function(val){
						return (!form.DataGrid.showGroup || Ext.isEmpty(val.rs[0].data.MaxEvn_id))
					},
					getGroupName: function(val){
						var lastRecIndex = val.rs.length - 1;
						var groupName = val.rs[0].data.Person_FIO +
							' Дата рождения:' + (!Ext.isEmpty(val.rs[0].data.Person_BirthDay) ? val.rs[0].data.Person_BirthDay.format('d.m.Y') : '') +
							' Дата начала лечения:' + (!Ext.isEmpty(val.rs[0].data.EvnVizitPL_setDate) ? val.rs[0].data.EvnVizitPL_setDate.format('d.m.Y') : '') +
							' Дата окончания лечения:' + (!Ext.isEmpty(val.rs[lastRecIndex].data.Evn_disDate) ? val.rs[lastRecIndex].data.Evn_disDate.format('d.m.Y') : '');
						return groupName;
					}
				}
			)
		});

		// 2. Общие ошибки
		this.ErrorComGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'ErrorCom',
			title:'Общие ошибки',
			object: 'RegistryErrorCom',
			dataUrl: '/?c=Registry&m=loadRegistryErrorCom',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			stringfields:
			[
				{name: 'RegistryErrorType_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', id: 'autoexpand', header: 'Наименование'},
				{name: 'RegistryErrorType_Descr', header: 'Описание', width: 250},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', disabled: true},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function()
			{
			}
		});
		this.ErrorComGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
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
		this.ErrorGrid = new sw.Promed.ViewFrame(
		{
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
			stringfields:
			[
				{name: 'RegistryError_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', header:'ИД случая', hidden:false},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', header:'Person_id', type: 'int', hidden:!isSuperAdmin()},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Form', hidden:true},
				{name: 'isNoEdit', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryErrorType_Descr', header: 'Описание', width: 250},
				{name: 'RegistryError_Desc', header: 'Комментарий', width: 250},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'LpuSection_id', type: 'int', hidden:true},
				{name: 'LpuUnit_id', type: 'int', hidden:true},
				{name: 'MedStaffFact_id', type: 'int', hidden:true},
				{name: 'MedPersonal_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'},
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
				[
					{ field: 'RegistryErrorType_Name', tpl: '{RegistryErrorType_Name}' }
				])
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.ErrorGrid, false); }},
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryError(); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.ErrorGrid, true); }},
				{name:'action_openevn', visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenPerson');}}
				
			],
			callbackPersonEdit: function(person, record)
			{
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.selectedRecord)
				{
					record = this.selectedRecord;
				}
				if (!record)
				{
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if (!record)
				{
					return false;
				}
				form.setNeedReform(record);
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
			},
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3));
				this.setActionDisabled('action_deleteall',(RegistryStatus_id!=3));
			}
		});

		//Интеграция фильтра к Grid
		columnsFilter = ['Evn_id','RegistryErrorType_Code','RegistryErrorType_Name','Person_FIO','Usluga_Code',
			'Diag_Code','LpuSection_name','RegistryErrorClass_Name'];
		configParams = {url : '/?c=Registry&m=loadRegistryErrorFilter'};
		_addFilterToGrid(this.ErrorGrid,columnsFilter,configParams);

		this.ErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
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

		this.NoPolisGridSearch = function()
		{
			var form = this;
			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if (Registry_id > 0)
			{
				form.NoPolisGrid.loadData(
					{
						globalFilters:
						{
							Person_Polis:form.findById('rvwNoPolisGridPolis').getValue(),
							Person_OrgSmo:form.findById('rvwNoPolisGridOrgSmo').getValue(),
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
		var rvwNoPolisGridSearch = new Ext.Button(
		{
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwNoPolisGridSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png',
			iconCls : 'x-btn-text',
			disabled: false,
			handler: function()
			{
				Ext.getCmp('RegistryViewWindow').NoPolisGridSearch();
			}
		});
		rvwNoPolisGridSearch.tabIndex = form.firstTabIndex+13;

		this.NoPolisGridFiltersPanel = new Ext.Panel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			height: 30,
			minSize: 30,
			maxSize: 30,
			layout: 'column',
			//title: 'Ввод',
			id: 'NoPolisGridFiltersPanel',
			keys:
				[{
					key: Ext.EventObject.ENTER,
					fn: function(e)
					{
						Ext.getCmp('RegistryViewWindow').NoPolisGridSearch();
					},
					stopEvent: true
				}],
			items:
				[{
					layout: 'form',
					border: false,
					bodyStyle:'padding: 4px;background:#DFE8F6;',
					columnWidth: .20,
					labelWidth: 80,
					items:
						[{
							anchor: '100%',
							fieldLabel: '№ полиса',
							name: 'Person_Polis',
							xtype: 'textfield',
							id: 'rvwNoPolisGridPolis',
							tabIndex:form.firstTabIndex+10
						}]
				},
					{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						columnWidth: .15,
						labelWidth: 40,
						items:
							[{
								anchor: '100%',
								fieldLabel: 'СМО',
								name: 'Person_OrgSmo',
								id: 'rvwNoPolisGridOrgSmo',
								xtype: 'textfield',
								tabIndex:form.firstTabIndex+11
							}]
					},
					{
						layout: 'form',
						border: false,
						bodyStyle:'padding: 4px;background:#DFE8F6;',
						columnWidth: .1,
						items: [rvwNoPolisGridSearch]
					}]
		});
		
		// 4. Незастрахованные
		this.NoPolisGrid = new sw.Promed.ViewFrame(
		{
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
			stringfields:
			[
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'LpuSection_Name', header: 'Отделение', width: 150},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis',  header: 'Серия, № полиса', width: 130},
				{name: 'Person_PolisDate',  header: 'Период действия полиса', width: 150},
				{name: 'Person_OrgSmo',  header: 'СМО', width: 150}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').NoPolisGrid, {}, 'OpenPerson');}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function()
			{
			}
		});
		//Интеграция фильтра к Grid
		columnsFilter = ['Evn_id','Person_FIO','LpuSection_Name'];
		configParams = {url : '/?c=Registry&m=loadRegistryNoPolisFilter'};
		_addFilterToGrid(this.NoPolisGrid,columnsFilter,configParams);
		// 5. Ошибки перс/данных
		this.PersonErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'PersonError',
			title:'Ошибки персональных данных',
			object: 'RegistryPerson',
			editformclassname: 'swPersonEditWindow',
			dataUrl: '/?c=Registry&m=loadRegistryPerson',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			stringfields:
			[
				{name: 'MaxEvnPerson_id', type: 'int', header: 'ID', key: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'Person_id', isparams: true, type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Person2_id', isparams: true, type: 'int', header: 'Person2_id', hidden:!isSuperAdmin()},
				{name: 'isNoEdit', type: 'int', hidden:true},
				{name: 'Person_SurName', id: 'autoexpand', header: 'Фамилия'},
				{name: 'Person_FirName', header: 'Имя', width: 180},
				{name: 'Person_SecName', header: 'Отчество', width: 180},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis',  header: 'Серия, № полиса', width: 130},
				{name: 'Person_PolisDate',  header: 'Период действия полиса', width: 150},
				{name: 'Person_EvnDate',  header: 'Период лечения', width: 150},
				{name: 'Person_OrgSmo',  header: 'СМО', width: 180}, 
				{name: 'isNoEdit', hidden:true} 
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: 'Объединить', handler: function() { form.doPersonUnion(form.PersonErrorGrid, {});}},
				{name:'action_view', text: 'Это не двойник', handler: function() { form.doRegistryPersonIsDifferent(form.PersonErrorGrid, {});}},
				{name:'action_delete', disabled: true, hidden: true }
			],
			onRowSelect: function (sm,index,record)
			{
				this.setActionDisabled('action_edit',((this.getCount()==0) || (record.get('isNoEdit')==2)));
			},
			callbackPersonEdit: function(person, record)
			{
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.selectedRecord)
				{
					record = this.selectedRecord;
				}
				if (!record)
				{
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if (!record)
				{
					return false;
				}
				form.setNeedReform(record);
			},
			setNoEdit: function (record)
			{
				record.set('isNoEdit', 2);
				record.commit();
			},
			onLoadData: function()
			{
			}
		});
		
		this.PersonErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('isNoEdit') == 2)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		this.TFOMSGridSearch = function()
		{
			var form = this;
			var filtersForm = form.RegistryTFOMSFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if (Registry_id > 0)
			{
				form.TFOMSErrorGrid.loadData(
				{
					globalFilters:
					{
						Person_FIO:filtersForm.findField('Person_FIO').getValue(), 
						RegistryErrorType_Code:filtersForm.findField('TFOMSError').getValue(), 
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						RegistryErrorStageType_id:filtersForm.findField('RegistryErrorStageType_id').getValue(),
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
		var rvwTFOMSBtnSearch = new Ext.Button(
		{
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwTFOMSBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png', 
			iconCls : 'x-btn-text',
			disabled: false, 
			handler: function() 
			{
				Ext.getCmp('RegistryViewWindow').TFOMSGridSearch();
			}
		});
		rvwTFOMSBtnSearch.tabIndex = form.firstTabIndex+26;
		
		this.RegistryTFOMSFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			//title: 'Ввод',
			id: 'RegistryTFOMSFiltersPanel',
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					Ext.getCmp('RegistryViewWindow').TFOMSGridSearch();
				},
				stopEvent: true
			}],
			items: 
			[{
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 40,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'ФИО',
						name: 'Person_FIO',
						xtype: 'textfieldpmw',
						id: 'rvwTFOMSPersonFIO',
						tabIndex:form.firstTabIndex+23
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 50,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Ошибка',
						name: 'TFOMSError',
						id: 'rvwTFOMSError',
						xtype: 'textfield',
						tabIndex:form.firstTabIndex+24
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 70,
					items: 
					[{
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex + 25,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 80,
					items:
					[{
						anchor: '100%',
						allowBlank: true,
						autoLoad: false,
						fieldLabel: 'Тип ошибки',
						hiddenName: 'RegistryErrorStageType_id',
						tabIndex: form.firstTabIndex + 25,
						xtype: 'swregistryerrorstagetypecombo',
						loadParams: {params: {where: 'where RegistryErrorStageType_Code in (1,3)'}}
					}]
				},
				{
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					columnWidth: .1,
					items: [rvwTFOMSBtnSearch]
				}]
			}]
		});

		// Ошибки ТФОМС
		this.TFOMSErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'TFOMSError',
			title:'Итоги проверки СМО',
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
			selectionModel: 'multiselect',
			stringfields:
			[
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden:false},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorTFOMS_FieldName', header: 'Имя поля', width: 80},
				{name: 'RegistryErrorTFOMS_BaseElement', header: 'Базовый элемент', width: 80},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},

				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
				[
					{ field: 'RegistryError_Comment', tpl: '{RegistryError_Comment}' }
				])
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
					if(!grid.getAction('action_undelete_all_records')){
						grid.ViewActions[action_undelete_all_records.name] = new Ext.Action(action_undelete_all_records);
						grid.ViewContextMenu.add(grid.ViewActions[action_undelete_all_records.name]);
					}

				}.createDelegate(this)

			},
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.TFOMSErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.TFOMSErrorGrid, true); }},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenPerson');}},
				{name:'-', visible: !isAdmin},
				{name:'action_tehinfo', disabled: true, visible: true, icon: 'img/icons/info16.png', tooltip: 'Технические подробности', text: 'Технические подробности', handler: function() {Ext.getCmp('RegistryViewWindow').openInfoForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid)}}
				
			],
			callbackPersonEdit: function(person, record)
			{
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.selectedRecord)
				{
					record = this.selectedRecord;
				}
				if (!record)
				{
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				}
				if (!record)
				{
					return false;
				}
				//form.setNeedReform(record);
			},
			onRowSelect: function(sm,rowIdx,record)
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
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				
				if (this.getCount()>0) 
				{
					this.setActionDisabled('action_openperson',!isAdmin);
					this.setActionDisabled('action_openevn',!isAdmin);
					this.setActionDisabled('action_tehinfo',false);
				}
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

			},
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3));
				this.setActionDisabled('action_deleteall',(RegistryStatus_id!=3));
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
				this.getGrid().getSelectionModel().clearSelections();
			}
		});

		columnsFilter = ['Evn_id','RegistryErrorType_Code','Person_FIO','LpuSection_Name'];
		configParams = {url : '/?c=Registry&m=loadRegistryErrorTFOMSFilter'};
		_addFilterToGrid(this.TFOMSErrorGrid,columnsFilter,configParams);

		this.TFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
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
				{name:'action_delete', handler: this.deleteRegistryDouble.createDelegate(this, ['current']) }
			],
			onLoadData: function()
			{
			}
		});
		
		this.DataTab = new Ext.TabPanel(
		{
			//resizeTabs:true,
			border: false,
			region: 'center',
			id: form.id+'DataTab',
			activeTab:0,
			//minTabWidth: 140,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle:'width:100%;'},
			layoutOnTabChange: true,
			listeners:
			{
				tabchange: function(tab, panel)
				{
					//log('tabchange');
					var form = Ext.getCmp('RegistryViewWindow');
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					if (record)
					{
						var Registry_id = record.get('Registry_id');
						var RegistryType_id = record.get('RegistryType_id');
						form.onRegistrySelect(Registry_id, RegistryType_id, true, record);
					}
				}
			},
			items:
			[{
				title: '0. Реестр',
				layout: 'fit',
				id: 'tab_registry',
				iconCls: 'info16',
				frame: true,
				//header:false,
				border:false,
				items: [form.RegistryPanel]
				},
				{
					title: '1. Данные',
					layout: 'fit',
					id: 'tab_data',
					//iconCls: 'info16',
					border:false,
					items: 
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.RegistryDataFiltersPanel,form.DataGrid]
					}]
				},
				{
					title: '2. Общие ошибки',
					layout: 'fit',
					id: 'tab_commonerr',
					iconCls: 'good',
					border:false,
					items: [form.ErrorComGrid]
				},
				{
					title: '3. Ошибки данных',
					layout: 'fit',
					id: 'tab_dataerr',
					iconCls: 'good',
					border:false,
					items: 
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.RegistryErrorFiltersPanel,form.ErrorGrid]
					}]
				},
				{
					title: '4. Незастрахованные',
					layout: 'fit',
					id: 'tab_datanopolis',
					iconCls: 'good',
					border:false,
					items: [{
						border: false,
						layout:'border',
						region: 'center',
						items: [
							form.NoPolisGridFiltersPanel,
							form.NoPolisGrid
						]
					}]
				},
				{
					title: '5. Ошибки перс/данных',
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datapersonerr',
					border:false,
					items: [form.PersonErrorGrid]
				},
				{
					title: '6. Итоги проверки ТФОМС',
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datatfomserr',
					border:false,
					items: 
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [
							form.RegistryTFOMSFiltersPanel,
							form.TFOMSErrorGrid
						]
					}]
				},
				{
					title: '7. Дубли посещений',
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datavizitdouble',
					border:false,
					items: [form.DoubleVizitGrid]
				}]
		});
		
		this.RegistryListPanel = new sw.Promed.Panel({
			border: false,
			layout:'border',
			defaults: {split: true},
			items: [form.AccountGrid, form.DataTab]
		});
		
		this.UnionRegistryGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'UnionRegistryGrid',
			region: 'north',
			height: 250,
			title:'Объединённые реестры',
			object: 'Registry',
			editformclassname: 'swUnionRegistryEditWindow',
			dataUrl: '/?c=Registry&m=loadUnionRegistryGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm,rowIdx,record)
			{
				Ext.getCmp('regvImportRegistry').disable();
				form.UnionRegistryChildGrid.removeAll();
				if (!Ext.isEmpty(record.get('Registry_id')))
				{
					var act = form.UnionRegistryGrid.getAction('action_print_alt');
					form.UnionRegistryChildGrid.loadData({params:{Registry_pid:record.get('Registry_id')}, globalFilters:{Registry_pid:record.get('Registry_id'), start: 0, limit: 100}});

					act.each(function(q,w){

						var index = q.menu.items.findIndex( 'name', 'printObjectAkt'),
							item;
						if(index != -1){
							item = q.menu.items.itemAt(index)
						}
						item.setVisible((record.get('Registry_xmlExportPath') && record.get('Registry_xmlExportPath') != 1));
					})
				}
			},
			stringfields:
			[
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130, hidden: true},
				{name: 'RegistryGroupType_Name', header: 'Тип объединенного реестра', width: 200},
				//{name: 'OrgSMO_Nick', header: 'СМО', width: 130},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'Registry_xmlExportPath', type:'string', hidden: true}
			],
			actions:
			[
				{name:'action_add' },
				{name:'action_edit' },
				{name:'action_view' },
				{name:'action_delete', url: '/?c=Registry&m=deleteUnionRegistry' }
			]
		});
		
		this.UnionRegistryChildGrid = new sw.Promed.ViewFrame(
		{
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
			stringfields:
			[
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130, hidden: true},
				{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', disabled: true, hidden: true },
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			]
		});
		
		this.UnionRegistryListPanel = new sw.Promed.Panel({
			border: false,
			layout:'border',
			defaults: {split: true},
			items: [form.UnionRegistryGrid, form.UnionRegistryChildGrid]
		});
		
		Ext.apply(this, 
		{
			layout:'border',
			defaults: {split: true},
			buttons: 
			[{
				hidden: false,
				handler: function() 
				{
					form.getReplicationInfo();
				},
				iconCls: 'ok16',
				text: 'Актуальность данных: (неизвестно)'
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide()
				},
				iconCls: 'close16',
				text: BTN_FRMCLOSE
			}],
			items: 
			[ 
				form.Tree,
				{
					border: false,
					region: 'center',
					layout:'card',
					activeItem: 0,
					id: 'regvRightPanel',
					defaults: {split: true},
					items: [this.RegistryListPanel, this.UnionRegistryListPanel]
				}
			]
		});
		sw.Promed.swRegistryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});