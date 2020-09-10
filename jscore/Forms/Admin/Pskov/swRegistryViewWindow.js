/**
* swRegistryViewWindow - окно просмотра и редактирования реестров for Pskov.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      июль 2010
* @comment      Префикс для id компонентов regv (RegistryViewWindow)
*/

sw.Promed.swRegistryViewWindow = Ext.extend(sw.Promed.BaseForm, 
{
	border: false,
	buttonAlign: 'left',
	closeAction: 'hide',
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
		/*getGlobalRegistryData = {
			Lpu_id            : node.parentNode.parentNode.attributes.object_value,
			RegistryType_id   : node.parentNode.attributes.object_value,
			RegistryStatus_id : node.attributes.object_value
		}*/

		var PayType_SysNick = 'oms';
		if (node.id.indexOf('PayType.1.bud') >= 0 && level >= 3) {
			level++;
			PayType_SysNick = 'bud';
		}

		switch (level)
		{
			case 0: case 1: case 2:
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 3:
				// отображение объединённых реестров
				owner.findById('regvRightPanel').setVisible(true);
				owner.findById('regvRightPanel').getLayout().setActiveItem(1);
				var Lpu_id = node.parentNode.parentNode.attributes.object_value;
				win.UnionRegistryGrid.loadData({params:{Lpu_id:Lpu_id}, globalFilters:{Lpu_id:Lpu_id, start: 0, limit: 100}});
				break;
			case 4:
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 5:
				owner.findById('regvRightPanel').setVisible(true);
				owner.findById('regvRightPanel').getLayout().setActiveItem(0);
				if (PayType_SysNick == 'bud') {
					var Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
				} else {
					var Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
				}
				var RegistryType_id = node.parentNode.attributes.object_value;
				var RegistryStatus_id = node.attributes.object_value;

				owner.AccountGrid.setColumnHidden('DispClass_Name', (RegistryType_id.inlist([1,2,6,8,10,11,14,15]))); // открыто при 7,9,12

				// Меняем колонки и отображение
				if (RegistryType_id==1 || RegistryType_id==14)
				{
					// Для стаца одни названия
					owner.DataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');
					owner.DataGrid.setColumnHeader('Evn_disDate', 'Выписка');
					owner.DataGrid.setColumnHidden('Evn_disDate', false);
					owner.DataGrid.setColumnHidden('EvnVizitPL_Count', true);
					
					owner.DataGrid.setColumnHeader('Usluga_Code', 'МЭС');
					owner.ErrorGrid.setColumnHeader('Usluga_Code', 'МЭС');
				}
				else if (RegistryType_id==2 || RegistryType_id==15) {
					// Для поликлиники - другие
					owner.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение'); // Начало лечения
					owner.DataGrid.setColumnHeader('Evn_disDate', 'Окончание лечения');
					owner.DataGrid.setColumnHidden('Evn_disDate', true); // false
					owner.DataGrid.setColumnHidden('EvnVizitPL_Count', true); // false
					
					owner.DataGrid.setColumnHeader('Usluga_Code', 'Код посещения');
					owner.ErrorGrid.setColumnHeader('Usluga_Code', 'Код посещения');
				}
				else
				{
					// Для остальных - еще более другие
					owner.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');
					owner.DataGrid.setColumnHidden('Evn_disDate', true);
					owner.DataGrid.setColumnHidden('EvnVizitPL_Count', true);
					
					owner.DataGrid.setColumnHeader('Usluga_Code', 'Код посещения');
					owner.ErrorGrid.setColumnHeader('Usluga_Code', 'Код посещения');
				}

				owner.AccountGrid.setColumnHidden('KatNasel_Name', PayType_SysNick == 'bud');
				owner.AccountGrid.setColumnHidden('Registry_rid', PayType_SysNick == 'bud');
				owner.DataGrid.setColumnHidden('RegistryHealDepResType_id', PayType_SysNick != 'bud');

				if (PayType_SysNick == 'bud') {
					if (owner.DataTab.getActiveTab().id == 'tab_datatfomserr') {
						owner.DataTab.setActiveTab(0);
					}
					owner.DataTab.hideTabStripItem('tab_datatfomserr');
					owner.DataTab.unhideTabStripItem('tab_datamzerr');
				} else {
					if (owner.DataTab.getActiveTab().id == 'tab_datamzerr') {
						owner.DataTab.setActiveTab(0);
					}
					owner.DataTab.unhideTabStripItem('tab_datatfomserr');
					owner.DataTab.hideTabStripItem('tab_datamzerr');
				}

				owner.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id!=3));
				owner.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id!=3));
				owner.AccountGrid.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,14,15])))); // !!! Пока только для полки, потом поправить обратно
				if (12 == RegistryStatus_id) {
					owner.AccountGrid.deletedRegistriesSelected = true;
				} else {
					owner.AccountGrid.deletedRegistriesSelected = false;
				}

				owner.setMenuActions(owner.AccountGrid, RegistryStatus_id);

				owner.AccountGrid.loadData({
					params: {
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id,
						PayType_SysNick: PayType_SysNick
					},
					globalFilters: {
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id,
						PayType_SysNick: PayType_SysNick
					}
				});

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
	onRegistrySelect: function (Registry_id, RegistryType_id, nofocus)
	{
		var form = this;
		//log('onRegistrySelect/Registry_id='+Registry_id);
		if (form.AccountGrid.getCount()>0) 
		{
			switch (form.DataTab.getActiveTab().id)
			{
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
					var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					var begDate = Ext.util.Format.date(reestr.get('Registry_begDate'),'d.m.Y'); //https://redmine.swan.perm.ru/issues/51050
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
					if ((form.NoPolisGrid.getParam('Registry_id')!=Registry_id) || (form.NoPolisGrid.getCount()==0))
					{
						form.NoPolisGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datavizitdouble':
					if ((form.DoubleVizitGrid.getParam('Registry_id')!=Registry_id) || (form.DoubleVizitGrid.getCount()==0))
					{
						form.DoubleVizitGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datatfomserr':
					if ((form.TFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.TFOMSErrorGrid.getCount()==0))
					{
						form.TFOMSErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datamzerr':
					if ((form.RegistryHealDepResErrGrid.getParam('Registry_id')!=Registry_id) || (form.TFOMSErrorGrid.getCount()==0))
					{
						form.RegistryHealDepResErrGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
				case 'tab_datavizitdouble':
					form.DoubleVizitGrid.removeAll(true);
					break;
				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.removeAll(true);
					break;
				case 'tab_datamzerr':
					form.RegistryHealDepResErrGrid.removeAll(true);
					break;
			}
		}
		return true;
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
	setRegistryStatus: function(RegistryStatus_id)
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
			url: '/?c=Registry&m=setRegistryStatus',
			params: 
			{	
				Registry_id: Registry_id,
				RegistryStatus_id: RegistryStatus_id
				/*Lpu_id: Lpu_id*/
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.RegistryStatus_id==RegistryStatus_id)
					{
						// Перечитываем грид, чтобы обновить данные по счетам
						form.AccountGrid.loadData();
					}
				}
			}
		});
	},
	setNeedReform: function(record)
	{
		return false; // данного функционала на Пскове нет или пока нет

		if (record)
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
		}
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
						form.menuActions.setRegistryActive.setDisabled(true);
					}
				}
			}
		});
	},
	TFOMSGridSearch: function()
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
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id,
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
		}
	},
	RegistryHealDepResErrGridSearch: function() {
		var form = this;
		var filtersForm = form.RegistryHealDepResErrFiltersPanel.getForm();

		var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
		var Registry_id = registry.get('Registry_id');
		var RegistryType_id = registry.get('RegistryType_id');
		if (Registry_id > 0) {
			form.RegistryHealDepResErrGrid.loadData({
				callback: function() {
					form.RegistryHealDepResErrGrid.ownerCt.doLayout();
				},
				globalFilters:
					{
						Person_FIO: filtersForm.findField('Person_FIO').getValue(),
						RegistryHealDepErrorType_Code: filtersForm.findField('RegistryHealDepErrorType_Code').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id,
						start: 0,
						limit: 100
					},
				noFocusOnLoad: false
			});
		}
	},
	exportRegistry: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		
		if (record.get('Registry_IsNeedReform') == 2) {
			Ext.Msg.alert('Ошибка', 'Реестр нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр и повторите действие.<br/>');
			return false;		
		}
		
		var fd = 'swRegistryDbfWindow';
		var params = {onHide: Ext.emptyFn, Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=Registry&m=exportRegistryToDbf'};
		getWnd(fd).show(params);
	},
	exportRegistryToXml: function(type) {
		var grid = (type == 'union' ? this.UnionRegistryGrid : this.AccountGrid);

		var record = grid.getGrid().getSelectionModel().getSelected();
		
		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
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
			callback: function() {
				if (type == 'union' && (!record.get('RegistryCheckStatus_SysNick') || !record.get('RegistryCheckStatus_SysNick').inlist(['Blocked', 'UploadTFOMS']))) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								this.setRegistryCheckStatus(grid, 'Blocked');
							}
						}.createDelegate(this),
						msg: 'Заблокировать реестр?',
						title: 'Подтверждение'
					});
				}
			}.createDelegate(this),
			onHide: function() {
				grid.loadData();
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			KatNasel_id: record.get('KatNasel_id'),
			KatNasel_SysNick: record.get('KatNasel_SysNick'),
			RegistryType_id: record.get('RegistryType_id'),
			url:'/?c=Registry&m=exportRegistryToXml'
		});
	},
	importRegistryFromTFOMS: function()
	{
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var fd = 'swRegistryImportXMLFromTFOMSWindow';
		var params =
		{
			onHide: function()
			{
				var form = Ext.getCmp('RegistryViewWindow');
				if (form.TFOMSErrorGrid && form.TFOMSErrorGrid.ViewGridStore) {
					form.TFOMSErrorGrid.ViewGridStore.reload();
				}
			},
			callback: function()
			{

			},
			Registry_id: record.get('Registry_id')
		};
		getWnd(fd).show(params);
	},
	setUnionRegistryMenuActions: function(object)
	{
		var win = this;
		var menu = new Array();

		if ( !this.unionRegistryMenu ) {
			this.unionRegistryMenu = new Ext.menu.Menu({id:'UnionRegistryMenu'});
		}

		object.addActions({
			name: 'action_actions',
			text: 'Действия',
			iconCls: 'actions16',
			menu: this.unionRegistryMenu
		});

		menu = [{
			text: 'Экспорт в XML',
			tooltip: 'Экспорт в XML',
			handler: function()
			{
				win.exportRegistryToXml('union');
			}
		}, {
			text: 'Импорт ответа из ТФОМС',
			tooltip: 'Импорт ответа из ТФОМС',
			handler: function()
			{
				win.importRegistryFromTFOMS();
			}
		}, {
			text: 'Заблокировать реестр',
			tooltip: 'Заблокировать реестр',
			id: 'BlockRegistry',
			handler: function()
			{
				win.setRegistryCheckStatus(win.UnionRegistryGrid, 'Blocked');
			}
		}, {
			text: 'Разблокировать реестр',
			tooltip: 'Разблокировать реестр',
			id: 'UnblockRegistry',
			handler: function()
			{
				win.setRegistryCheckStatus(win.UnionRegistryGrid, null);
			}
		}];

		this.unionRegistryMenu.removeAll();

		for ( key in menu ) {
			if ( key != 'remove' ) {
				this.unionRegistryMenu.add(menu[key]);
			}
		}

		return true;
	},
	setRegistryCheckStatus: function(grid, RegistryCheckStatus_SysNick) {
		if ( typeof grid != 'object' ) {
			return false;
		}

		var record = grid.getGrid().getSelectionModel().getSelected();

		if ( !record && Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		this.getLoadMask().show();

		var params = {
			'Registry_id': record.get('Registry_id')
		}

		if ( !Ext.isEmpty(RegistryCheckStatus_SysNick) ) {
			params.RegistryCheckStatus_SysNick = RegistryCheckStatus_SysNick;
		}

		Ext.Ajax.request({
			failure: function(result){
				this.getLoadMask().hide();
			}.createDelegate(this),
			method: 'POST',
			params: params,
			success: function(result){
				this.getLoadMask().hide();

				if ( result.responseText.length > 0 ) {
					var resp_obj = Ext.util.JSON.decode(result.responseText);
					if (resp_obj.success == true) {
						grid.getGrid().getStore().reload();
					}
				}
			}.createDelegate(this),
			url: '/?c=Registry&m=setRegistryCheckStatus'
		});
	},
	reformRegistry: function(record) {
		var current_window = this;

		if ( record.Registry_id > 0 ) {
			var loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите, идет переформирование реестра...'});
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
	createAndSignXML: function(options)
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');

		if (!options || !options.OverrideExportOneMoreOrUseExist) {
			// проверяем, есть ли уже файл экспорта, если есть, то сразу подписываем существующий файл
			form.getLoadMask('Получение данных о реестре').show();
			Ext.Ajax.request({
				url: '/?c=Registry&m=checkRegistryXmlExportExists',
				params: {
					Registry_id: Registry_id
				},
				callback: function(options, success, response) {
					form.getLoadMask().hide();

					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.exists) {
							form.createAndSignXML({
								OverrideExportOneMoreOrUseExist: 1
							});
						} else {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								scope: form,
								fn: function(buttonId) {
									if (buttonId == 'yes')
										form.createAndSignXML({
											OverrideExportOneMoreOrUseExist: 2
										});
								},
								icon: Ext.Msg.QUESTION,
								msg: 'Сформировать файл реестра и подписать?',
								title: 'Вопрос'
							});
						}
					}
				}
			});
		}

		form.getLoadMask('Создание XML-файла').show();
		var params = {
			Registry_id: Registry_id,
			OverrideExportOneMoreOrUseExist: options.OverrideExportOneMoreOrUseExist,
			forSign: 1
		};

		Ext.Ajax.request({
			url: '/?c=Registry&m=exportRegistryToXML',
			params: params,
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				var r = '';
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);

					if (result.success === false)
					{
						if (result.Error_Code && result.Error_Code == '12') { // Неверная сумма по счёту и реестрам.
							// обновить форму
							form.AccountGrid.loadData();
						}

						if (result.Error_Msg)
							r = result.Error_Msg;

						var defmsg = 'При формировании/отправке реестра произошла ошибка!<br/>';

						if (result.WithoutDefaultMsg)
							defmsg = '';

						/*sw.swMsg.show(
						 {
						 buttons: Ext.Msg.OK,
						 icon: Ext.Msg.INFO,
						 msg: 'Реестр успешно выгружен и отправлен.',
						 title: ''
						 });
						 */
						sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: defmsg + r,
								title: ''
							});
						return false;
					} else {
						// получили хэш файл, его надо подписать
						if (result.filebase64) {
							params.filebase64 = result.filebase64;
							form.doSignRegistry(params, record);
						} else {
							log('Ошибка при получении хэша файла', result);
							sw.swMsg.alert('Ошибка', 'Ошибка при получении хэша файла');
						}

						return true;
					}
				}
			}
		});
	},
	doSignRegistry: function(params, record)
	{
		var form = this;
		// с помощью КриптоПро:
		// 1. показываем выбор сертификата
		getWnd('swCertSelectWindow').show({
			callback: function(cert) {
				sw.Applets.CryptoPro.signText({
					text: params.filebase64,
					Cert_Thumbprint: cert.Cert_Thumbprint,
					callback: function (sSignedData) {
						// сохраняем подпись в файл, помечаем реестр как готов к отправке в ТФОМС.
						params.documentSigned = sSignedData;
						form.getLoadMask('Подписание реестра').show();
						Ext.Ajax.request({
							url: '/?c=Registry&m=signRegistry',
							params: {
								Registry_id: params.Registry_id,
								documentSigned: params.documentSigned,
							},
							callback: function (options, success, response) {
								form.getLoadMask().hide();
								if (success) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										record.set('RegistryCheckStatus_SysNick', 'SignECP');
										record.set('RegistryCheckStatus_Name', "<a href='#' onClick='getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:" + record.get('Registry_id') + "});'>Подписан (ЭЦП)</a>");
										record.commit();
										form.AccountGrid.onRowSelect(form.AccountGrid.getGrid().getSelectionModel(), 0, record);
									}
								}
							}
						});
					}
				});
			}
		});
	},
	sendRegistryToMZ: function() {
		var form = this;
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId)
			{
				if (buttonId=='yes') {
					form.getLoadMask('Отправка в МЗ').show();
					Ext.Ajax.request({
						url: '/?c=Registry&m=sendRegistryToMZ',
						params: {
							Registry_id: record.get('Registry_id')
						},
						callback: function (options, success, response) {
							form.getLoadMask().hide();
							if (success) {
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.success) {
									record.set('RegistryCheckStatus_SysNick', 'SendMZ');
									record.set('RegistryCheckStatus_Name', "<a href='#' onClick='getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:" + record.get('Registry_id') + "});'>Отправлен в МЗ</a>");
									record.commit();
									form.AccountGrid.onRowSelect(form.AccountGrid.getGrid().getSelectionModel(), 0, record);
								}
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Отправить реестр на проверку в Минздрав?',
			title: 'Вопрос'
		});
	},
	setMenuActions: function (object, RegistryStatus_id)
	{
		var form = this;
		var menu = new Array();
		
		if (!this.menu) 
			this.menu = new Ext.menu.Menu({id:'RegistryMenu'});
		object.addActions(
		{
			name:'action_new',
			text:'Действия',
			iconCls: 'actions16',
			menu: this.menu
		});
		switch (RegistryStatus_id)
		{
			case 12:
				// Удаленные
				menu = 
				[
					form.menuActions.reviveRegistry
				];
				break;

			case 11:
				// В очереди
				menu = [
					form.menuActions.deleteRegistryQueue
				];
				break;
		
			case 3:
				// В работе 
				menu = 
				[
					form.menuActions.exportToXml,
					form.menuActions.registrySetPay,
					'-',
					form.menuActions.reformRegistry,
					'-',
					form.menuActions.refreshRegistry
				];
				break;
			case 2: // К оплате
				menu = 
				[
					form.menuActions.exportToXml,
					'-',
					form.menuActions.registrySetWork,
					form.menuActions.registrySetPaid,
					form.menuActions.registrySign,
					form.menuActions.sendRegistryToMZ
				];
				break;
			case 4: // Оплаченные 
				menu = 
				[
					form.menuActions.exportToDbf,
					form.menuActions.setRegistryActive,
					form.menuActions.registrySetPay
				];
				break;
			case 6:
				// Проверенные МЗ
				menu = [
					form.menuActions.registrySetWork,
					form.menuActions.registrySetPaid,
					form.menuActions.exportToXml
				];
				break;
		
			default:
				Ext.Msg.alert('Ошибка', 'Значение статуса неизвестно!');
		}
		
		this.menu.removeAll();

		for (key in menu)
		{
			if (key!='remove')
				this.menu.add(menu[key]);
		}

		return true;
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

		if (object.id == this.id+'NoPolis') // Если это с грида "Нет полиса"
		{
			type = 108;
		}

		if (!Ext.isEmpty(frm) && (object.id == this.id+'Error' || object.id == this.id+'TFOMSError' || object.id == this.id+'Data')) // Если это с грида "Ошибки данных" или Данные
		{
			switch (frm)
			{
				case 'Person': case 'OpenPerson':
					type = 108;
					break;
			}
		}

		var id = record.get('Evn_rid') ||  record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное 

		var Person_id = record.get('Person_id');
		var Server_id = record.get('Server_id');
		var PersonEvn_id = null;
		var usePersonEvn = null;
		var MedPersonal_id = 0;
		if (record.get('PersonEvn_id'))
		{
			PersonEvn_id = record.get('PersonEvn_id');
			usePersonEvn = true;
		}
		var open_form = '';
		var key = '';

		var params = {action: 'edit', Person_id: Person_id, Server_id: Server_id, PersonEvn_id: PersonEvn_id, usePersonEvn: usePersonEvn}; //, Person_id: this.Person_id, Server_id: this.Server_id
		params = Ext.apply(params || {}, oparams || {});

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
				params.DispClass_id = record.get('DispClass_id');
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
				open_form = 'swEvnUslugaParEditWindow';
				key = 'EvnUslugaPar_id';
				id = record.get('Evn_id');
				break;
			case 108:
				open_form = 'swPersonEditWindow';
				key = 'Person_id';
				id = record.get('Person_id');

				if ( Ext.isEmpty(id) ) {
					sw.swMsg.alert('Ошибка', 'Пациент не идентифицирован');
					return false;
				}
				break;
			default:
				Ext.Msg.alert('Ошибка', 'Вызываемая форма неизвестна!');
				return false;
				break;
		}

		if (id)
			params[key] = id;
		// на сохранение формы мы проставляем изменения по этой записи реестра
		if (object.id != this.id+'NoPolis')
		{
			params['callback'] = function () {this.setNeedReform(record);}.createDelegate(this);
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
	/**
	 * Открытие информационного окна с технической информацией
	 */
	openInfoForm: function (object) {
		var record = object.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' ) {
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись с ошибкой ТФОМС!');
			return false;
		}

		var msg = 
			'Имя поля: '+((record.get('RegistryErrorTFOMS_FieldName'))?record.get('RegistryErrorTFOMS_FieldName'):'')+'<br>'+
			'Базовый элемент: '+((record.get('RegistryErrorTFOMS_BaseElement'))?record.get('RegistryErrorTFOMS_BaseElement'):'')+'<br>'+
			'Комментарий: '+((record.get('RegistryErrorTFOMS_Comment'))?record.get('RegistryErrorTFOMS_Comment'):'')+'<br>';
		;

		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			closable: false,
			scope: Ext.getCmp('RegistryViewWindow'),
			fn: function(buttonId) {
				//
			},
			icon: Ext.Msg.INFO,
			msg: msg,
			title: 'Технические подробности'
		});
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
		this.getReplicationInfo();
		this.setUnionRegistryMenuActions(this.UnionRegistryGrid);
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
	/** Пересчет реестра
	 *
	 */
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
			scope: form,
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

		this.menuActions = {
			reviveRegistry: new Ext.Action({
				text: 'Восстановить',
				tooltip: 'Восстановить удаленный реестр',
				handler: function() {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						scope: Ext.getCmp('RegistryViewWindow'),
						fn: function(buttonId) {
							if (buttonId == 'yes') {
								form.registryRevive();
							}
						},
						icon: Ext.Msg.QUESTION,
						msg: 'Вы действительно хотите восстановить выбранный реестр?',
						title: 'Восстановление реестра'
					});
				}
			}),
			deleteRegistryQueue: new Ext.Action({
				text: 'Удалить реестр из очереди',
				tooltip: 'Удалить реестр из очереди',
				handler: function() {
					form.deleteRegistryQueue();
				}
			}),
			exportToDbf: new Ext.Action({
				text: 'Экспорт в DBF',
				tooltip: 'Экспорт в DBF',
				handler: function()
				{
					form.exportRegistry();
				}
			}),
			exportToXml: new Ext.Action({
				text: 'Экспорт в XML',
				tooltip: 'Экспорт в XML',
				handler: function() {
					form.exportRegistryToXml('simple');
				}
			}),
			registrySetPay: new Ext.Action({
				text: 'Отметить к оплате',
				tooltip: 'Отметить к оплате',
				handler: function() {
					form.setRegistryStatus(2);
				}
			}),
			reformRegistry: new Ext.Action({
				text: 'Переформировать весь реестр',
				tooltip: 'Переформировать весь реестр',
				handler: function() {
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

					if (!record || !(record.get('Registry_id') > 0)) {
						sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
						return false;
					} else {
						var rec = {
							Registry_id: record.get('Registry_id'),
							RegistryType_id: record.get('RegistryType_id')
						};
						form.reformRegistry(rec);
					}
				}
			}),
			refreshRegistry: new Ext.Action({
				text: 'Пересчитать реестр',
				tooltip: 'Пересчитать реестр',
				handler: function() {
					form.refreshRegistry();
				}
			}),
			importRegistryFromTFOMS: new Ext.Action({
				text: 'Импорт реестра из ТФОМС',
				tooltip: 'Импорт реестра из ТФОМС',
				handler: function() {
					form.importRegistryFromTFOMS();
				}
			}),
			registrySetWork: new Ext.Action({
				text: 'Перевести в работу',
				tooltip: 'Перевести в работу',
				handler: function() {
					form.setRegistryStatus(3);
				}
			}),
			registrySetPaid: new Ext.Action({
				text: 'Отметить как оплаченный',
				tooltip: 'Отметить как оплаченный',
				handler: function() {
					form.setRegistryStatus(4);
				}
			}),
			registrySign: new Ext.Action({
				text: 'Подписать ЭЦП',
				tooltip: 'Подписать ЭЦП',
				handler: function() {
					form.createAndSignXML();
				}
			}),
			sendRegistryToMZ: new Ext.Action({
				text: 'Отправить на проверку в МЗ',
				tooltip: 'Отправить на проверку в МЗ',
				handler: function() {
					form.sendRegistryToMZ();
				}
			}),
			setRegistryActive: new Ext.Action({
				text: 'Снять активность',
				tooltip: 'Снять активность',
				handler: function() {
					form.setRegistryActive();
				}
			}),
			registryErrorExport: new Ext.Action({
				text: 'Экспорт протоколов',
				tooltip: 'Экспорт протоколов',
				handler: function() {
					var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();

					if (!reestr) {
						sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.<br/>');
						return false;
					}

					var Registry_id = reestr.get('Registry_id');
					var RegistryErrorTFOMSType_id = reestr.get('RegistryErrorTFOMSType_id');

					getWnd('swRegistryErrorExportWindow').show({
						Registry_id: Registry_id,
						RegistryErrorTFOMSType_id: RegistryErrorTFOMSType_id
					});
				}
			})
		};

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
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'KatNasel_SysNick', type:'string', hidden: true},
				{name: 'KatNasel_Name', id: 'autoexpand', header: 'Категория населения'},
				{name: 'Registry_Num', header: 'Номер счета', width: 120},
				{name: 'Registry_accDate', type:'date', header: 'Дата счета', width: 80},
				{name: 'DispClass_Name', header: 'Тип дисп/медосмотра', width: 140},
				{name: 'ReformTime',hidden: true},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Оконч. периода', width: 100},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 100},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_ErrorCount', hidden: true},
				{name: 'Registry_CountErr', hidden: true},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110},
				{name: 'Registry_rid', type: 'int', header: 'Первичный реестр'},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'RegistryCheckStatus_id', type:'int', hidden: true},
				{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200},
				{name: 'PayType_SysNick', type:'string', hidden: true},
				{name: 'RegistryCheckStatus_SysNick', type:'string', hidden: true},
				{name: 'issetDouble', hidden: true},
				{name: 'RegistryErrorCom_IsData', type: 'int', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryNoPolis_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'RegistryHealDepCheckJournal_AccRecCount', type: 'int', hidden: true},
				{name: 'RegistryHealDepCheckJournal_DecRecCount', type: 'int', hidden: true},
				{name: 'RegistryHealDepCheckJournal_UncRecCount', type: 'int', hidden: true}
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
/*
				//В соответствие с задачей 16102 закрываем кнопки для неадминов и неповерюзеров в Уфе
				var isLpuPowerUser = getGlobalOptions().groups.toString().indexOf('LpuPowerUser') != -1;
				var isLpuAdmin = getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1;
				if (!isLpuAdmin && !isSuperAdmin() && !isLpuPowerUser)
				{
					this.getAction('action_new').setDisabled(true);
				}
*/
			},
			onRowSelect: function(sm,index,record)
			{
				//log(this.id+'.onRowSelect');
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.getCount()>0)
				{
					var Registry_id = record.get('Registry_id');
					var RegistryType_id = record.get('RegistryType_id');
					var RegistryStatus_id = record.get('RegistryStatus_id');
					
					// Убрать кнопку Печать счета иногородним в полке и стаце (refs #1595)
					if (RegistryType_id.inlist(['1','2','14']) && record.get('KatNasel_id') == 2) {
						this.setActionDisabled('action_print', true);
					} else {
						this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,14,15]))));
					}
					
					form.onRegistrySelect(Registry_id, RegistryType_id,  false);
					this.setActionDisabled('action_edit',record.get('RegistryStatus_id')!=3); // #61531
					this.setActionDisabled('action_delete',record.get('RegistryStatus_id')!=3); // #61531
					this.setActionDisabled('action_view',false);

					// В прогрессе 
					if (record.get('Registry_IsProgress')==1)
					{
						this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete',true);
						this.setActionDisabled('action_view',true);
					}
					
					this.setActionDisabled('action_delete',((record.get('RegistryStatus_id')==4) || (record.get('RegistryStatus_id')==2))); // не давать удалять если реестр находится в разделе К оплате или Оплаченные #18220

					var deletedRegistriesSelected = form.AccountGrid.deletedRegistriesSelected;

					// Для папки с удаленными реестрами дизаблим контролы
					if (deletedRegistriesSelected) {
						form.AccountGrid.setActionDisabled('action_add',true);
						form.AccountGrid.setActionDisabled('action_edit',true);
						form.AccountGrid.setActionDisabled('action_delete',true);
						form.AccountGrid.setActionDisabled('action_view',true);
					} else {
						switch (record.get('RegistryStatus_id')) {
							case 3: // в работе
								form.menuActions.exportToXml.setHidden(true);
								form.menuActions.reformRegistry.setDisabled(record.get('Registry_IsLocked') == 2 && !isAdmin);
								break;
							case 2: // к оплате
								form.menuActions.exportToXml.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registrySign.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.sendRegistryToMZ.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.refreshRegistry.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								form.menuActions.registrySign.setDisabled(!Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')));
								form.menuActions.sendRegistryToMZ.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'SignECP');
								form.menuActions.registrySetWork.setDisabled(
									(record.get('RegistryCheckStatus_SysNick') && record.get('RegistryCheckStatus_SysNick').inlist(['Blocked', 'UploadTFOMS']))
									|| (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && !Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) && record.get('RegistryCheckStatus_SysNick') != 'SignECP')
								);
								break;
							case 4: // оплаченные
								form.menuActions.exportToXml.setHidden(true);
								form.menuActions.exportToDbf.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.setRegistryActive.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registrySetPay.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								form.menuActions.setRegistryActive.setDisabled(record.get('Registry_IsActive') != 2);
								break;
							case 6: // проверенные МЗ
								form.menuActions.exportToXml.setHidden(true);
								form.menuActions.registrySetPaid.setHidden(false);

								form.menuActions.registrySetWork.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'RejectMZ');
								form.menuActions.registrySetPaid.setDisabled(Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) || !record.get('RegistryCheckStatus_SysNick').inlist(['HalfAcceptMZ', 'AcceptMZ']));
								break;
						}
					}

					var Registry_RecordPaidCount = '';
					if (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])) {
						Registry_RecordPaidCount = '<div style="padding:2px;font-size: 12px;">Количество принятых случаев: '+record.get('RegistryHealDepCheckJournal_AccRecCount')+'</div>';
						Registry_RecordPaidCount += '<div style="padding:2px;font-size: 12px;">Количество отклонённых случаев: '+record.get('RegistryHealDepCheckJournal_DecRecCount')+'</div>';
						Registry_RecordPaidCount += '<div style="padding:2px;font-size: 12px;">Количество непроверенных случаев: '+record.get('RegistryHealDepCheckJournal_UncRecCount')+'</div>';
					}

					form.RegistryPanel.show();
					form.RegistryTpl.overwrite(form.RegistryPanel.body, 
					{
						Registry_Num: record.get('Registry_Num'), 
						Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'), 
						Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'), 
						Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'), 
						ReformTime:record.get('ReformTime'),
						Registry_Count: record.get('Registry_Count'), 
						Registry_ErrorCount: record.get('Registry_ErrorCount'),
						Registry_CountErr: record.get('Registry_CountErr'),
						Registry_NoErrorCount: record.get('Registry_Count') - record.get('Registry_ErrorCount'),
						Registry_Sum: record.get('Registry_Sum'),
						Registry_IsNeedReform: record.get('Registry_IsNeedReform'),
						Registry_RecordPaidCount: Registry_RecordPaidCount
					});
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
						case 'tab_datavizitdouble':
							form.DoubleVizitGrid.removeAll(true);
							break;
						case 'tab_datatfomserr':
							form.TFOMSErrorGrid.removeAll(true);
							break;
						case 'tab_datamzerr':
							form.RegistryHealDepResErrGrid.removeAll(true);
							break;
					}
				}

				// информируем о данных на вкладках
				form.DataTab.getItem('tab_registry').setIconClass((record.get('Registry_IsNeedReform')==2)?'delete16':'info16');

				form.DataTab.getItem('tab_commonerr').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');

				form.DataTab.getItem('tab_dataerr').setIconClass((record.get('RegistryError_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopolis').setIconClass((record.get('RegistryNoPolis_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datavizitdouble').setIconClass((record.get('issetDouble')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datamzerr').setIconClass((record.get('RegistryErrorMZ_IsData')==1)?'usluga-notok16':'good');
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
			'<div style="padding:4px;font-weight:bold;">Реестр № {Registry_Num}<tpl if="Registry_IsNeedReform == 2"> <span style="color: red;">(НУЖДАЕТСЯ В ПЕРЕФОРМИРОВАНИИ!)</span></tpl></div>'+
			'<div style="padding:4px;">Дата формирования: {Registry_accDate}</div>'+
			'<div style="padding:4px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:4px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:4px">Дата переформирования реестра: {ReformTime}</div>'+
			'<div style="padding:4px;">Количество записей в реестре: {Registry_Count}</div>'+
			'<div style="padding:4px;">Количество записей в реестре с ошибками данных: {Registry_ErrorCount}</div>'+
			'<div style="padding:4px;">Количество записей с ошибками данных, не вошедших в реестр: {Registry_CountErr}</div>'+
			'<div style="padding:4px;">Записей без ошибок: {Registry_NoErrorCount}</div>'+
			'{Registry_RecordPaidCount}'
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
						Registry_id:Registry_id, 
						RegistryType_id:RegistryType_id, 
						Person_SurName:filtersForm.findField('Person_SurName').getValue(), 
						Person_FirName:filtersForm.findField('Person_FirName').getValue(), 
						Person_SecName:filtersForm.findField('Person_SecName').getValue(),
						LpuBuilding_id:filtersForm.findField('LpuBuilding_id').getValue(), 
						MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(), 
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
		rvwDGBtnSearch.tabIndex = form.firstTabIndex+15;

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
		rvwDGBtnReset.tabIndex = form.firstTabIndex+16;
		
		this.RegistryDataFiltersPanel = new Ext.form.FormPanel(
		{
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			//title: 'Ввод',
			height: 55,
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
			listeners: {
				'render': function() {
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
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
					labelWidth: 100,
					items: 
					[{
						anchor: '100%',
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						linkedElements: [],
						tabIndex: form.firstTabIndex + 13,
						xtype: 'swlpubuildingglobalcombo'
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
						tabIndex: form.firstTabIndex + 14,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
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
						tabIndex: form.firstTabIndex + 15,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					items: [rvwDGBtnSearch]
				}, {
					layout: 'form',
					border: false,
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
						Registry_id:Registry_id, 
						RegistryType_id:RegistryType_id, 
						Person_SurName:filtersForm.findField('Person_SurName').getValue(), 
						Person_FirName:filtersForm.findField('Person_FirName').getValue(), 
						Person_SecName:filtersForm.findField('Person_SecName').getValue(),
						RegistryError_Code:filtersForm.findField('RegistryError_Code').getValue(),
						LpuBuilding_id:filtersForm.findField('LpuBuilding_id').getValue(), 
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
		rvwREBtnSearch.tabIndex = form.firstTabIndex+21;
		
		this.RegistryErrorFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			height: 55,
			layout: 'form',
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
			listeners: {
				'render': function() {
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
				}
			},
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
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+15
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
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+16
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
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+17
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 80,
					items: 
					[{
						anchor: '100%',
						fieldLabel: 'Код ошибки',
						name: 'RegistryError_Code',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+18
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
					labelWidth: 100,
					items: 
					[{
						anchor: '100%',
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						linkedElements: [],
						tabIndex: form.firstTabIndex + 19,
						xtype: 'swlpubuildingglobalcombo'
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
						tabIndex: form.firstTabIndex + 20,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
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
					columnWidth: .1,
					items: [rvwREBtnSearch]
				}]
			}]
		});
	
		this.DoubleVizitGridSearch = function() 
		{
			var form = this;
			var filtersForm = form.DoubleVizitFiltersPanel.getForm();
			
			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if (Registry_id > 0)
			{
				form.DoubleVizitGrid.loadData(
				{
					globalFilters:
					{
						Registry_id:Registry_id, 
						RegistryType_id:RegistryType_id, 
						LpuBuilding_id:filtersForm.findField('LpuBuilding_id').getValue(), 
						MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(), 
						start: 0, 
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}
		
		var rvwDVBtnSearch = new Ext.Button(
		{
			tooltip: BTN_FRMSEARCH_TIP,
			id: 'rvwDVBtnSearch',
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png', 
			iconCls : 'x-btn-text',
			disabled: false, 
			handler: function() 
			{
				Ext.getCmp('RegistryViewWindow').DoubleVizitGridSearch();
			}
		});
		rvwDVBtnSearch.tabIndex = form.firstTabIndex+27;
		
		this.DoubleVizitFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			height: 30,
			layout: 'form',
			//title: 'Ввод',
			id: 'DoubleVizitFiltersPanel',
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					Ext.getCmp('RegistryViewWindow').DoubleVizitGridSearch();
				},
				stopEvent: true
			}],
			listeners: {
				'render': function() {
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
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
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						linkedElements: [],
						tabIndex: form.firstTabIndex + 22,
						xtype: 'swlpubuildingglobalcombo'
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
						tabIndex: form.firstTabIndex + 23,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .1,
					items: [rvwDVBtnSearch]
				}]
			}]
		});
		
		// Данные реестра 
		this.DataGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'Data',
			title:'Данные',
			object: 'RegistryData',
			region: 'center',
			//editformclassname: 'swLpuSectionShiftEditForm',
			dataUrl: '/?c=Registry&m=loadRegistryData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			grouping: true,
			showGroup: false,
			groupSortInfo: {
				field: 'MaxEvn_id'
			},
			selectionModel: 'multiselect',
			stringfields:
			[
				{name: 'Evn_ident', type: 'int', header: 'ИД случая', key: true, hidden: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: false},
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
				{name: 'RegistryHealDepResType_id',  header: 'Результат проверки', renderer: function(val) {
					if (val) {
						switch (parseInt(val)) {
							case 2: // Отклонён
								return "<img src='/img/icons/minus16.png' />";
								break;
							case 1: // Принят
								return "<img src='/img/icons/plus16.png' />";
								break;
						}
					}

					return '';
				}, width: 120},
				{name: 'Usluga_Code', header: 'Код посещения', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'EvnVizitPL_Count', header: 'Количество посещений', width: 80},
				{name: 'RegistryData_Uet', header: 'УЕТ', width: 60},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
				{name: 'Paid', header: 'Оплата', width: 60},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'Err_Count', hidden:true},
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
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() { form.openForm(form.DataGrid, {});}},
				{name:'action_view', disabled: true},
				{name:'action_delete', handler: function() { form.deleteRegistryData(form.DataGrid, false); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {}, 'OpenPerson');}}
			],
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_edit',(RegistryStatus_id==12));
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3));
				if(this.showGroup){
					form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
				}else{
					form.DataGrid.getGrid().getStore().clearGrouping()
				}
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
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
				form.DataGrid.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');
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
		this.DataGrid.getGrid().view = new Ext.grid.GroupingView({
			forceFit:true,
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Err_Count') > 0 || row.get('RegistryHealDepResType_id') == 2)
					cls = cls+'x-grid-rowred ';
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
		
		// Общие ошибки
		this.ErrorComGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'ErrorCom',
			title:'Общие ошибки',
			object: 'RegistryErrorCom',
			//editformclassname: 'swLpuSectionShiftEditForm',
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
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', text: '<b>Исправить</b>', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
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
		// Ошибки данных 
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
			stringfields:
			[
				{name: 'RegistryError_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', header:'ИД случая',hidden:false},
				{name: 'Evn_rid', hidden:true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код'},
				{name: 'RegistryErrorType_Name', header: 'Наименование', width: 200},
				{name: 'RegistryError_Desc', header: 'Комментарий', width: 250},
				{name: 'Person_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Usluga_Code', header: 'Код посещения', width: 80},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'},
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {});}},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true},
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryError(); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenPerson');}}
			],
			onLoadData: function()
			{
			}
		});
		this.ErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('RegistryErrorClass_id') == 2)
					cls = cls+'x-grid-row ';
				if (row.get('RegistryErrorClass_id') == 1)
					cls = cls+'x-grid-rowred ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
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
					{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90}
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
				this.setActionDisabled('action_edit',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
			}
		});
		
		this.DoubleVizitGrid = new sw.Promed.ViewFrame({
			id: form.id+'DoubleVizit',
			title: 'Дубли посещений',
			object: 'RegistryDouble',
			dataUrl: '/?c=Registry&m=loadRegistryDouble',
			paging: true,
			root: 'data',
			region: 'center',
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
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
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

		// Кнопка "Поиск"
		var rvwTFOMSBtnSearch = new Ext.Button({
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

		this.RegistryHealDepResErrFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					form.RegistryHealDepResErrGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 40,
					items: [{
						anchor: '100%',
						fieldLabel: 'ФИО',
						name: 'Person_FIO',
						xtype: 'textfieldpmw'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 50,
					items: [{
						anchor: '100%',
						fieldLabel: 'Ошибка',
						name: 'RegistryHealDepErrorType_Code',
						xtype: 'textfield'
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
							xtype: 'numberfield'
						}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						disabled: false,
						handler: function() {
							form.RegistryHealDepResErrGridSearch();
						}
					}]
				}]
			}]
		});

		// Ошибки МЗ
		this.RegistryHealDepResErrGrid = new sw.Promed.ViewFrame({
			id: form.id + 'RegistryHealDepResErrGrid',
			title: 'Ошибки МЗ',
			object: 'RegistryHealDepResErr',
			dataUrl: '/?c=Registry&m=loadRegistryHealDepResErrGrid',
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
				{name: 'RegistryHealDepResErr_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: false},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden: true},
				{name: 'RegistryData_notexist', type: 'int', hidden: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryHealDepErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryHealDepErrorType_Name', header: 'Ошибка', width: 250},
				{name: 'RegistryHealDepErrorType_Descr', header: 'Описание ошибки', autoexpand: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'IsGroupEvn', type: 'int', hidden: true}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{
					name: 'action_edit', text: '<b>Исправить</b>', handler: function() {
						form.openForm(form.RegistryHealDepResErrGrid, {});
					}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{
					name: 'action_delete', text: 'Удалить случай из реестра', handler: function() {
						form.deleteRegistryData(form.RegistryHealDepResErrGrid, false);
					}
				},
				{name: '-'},
				{
					name: 'action_deleteall',
					icon: 'img/icons/delete16.png',
					text: 'Удалить случаи по всем ошибкам',
					handler: function() {
						form.deleteRegistryData(form.RegistryHealDepResErrGrid, true);
					}
				},
				{
					name: 'action_openevn',
					disabled: true,
					visible: !isAdmin,
					tooltip: 'Открыть учетный документ',
					icon: 'img/icons/pol-eplstream16.png',
					text: 'Открыть учетный документ',
					handler: function() {
						form.openForm(form.RegistryHealDepResErrGrid, {}, 'OpenEvn');
					}
				},
				{
					name: 'action_openperson',
					disabled: true,
					visible: !isAdmin,
					icon: 'img/icons/patient16.png',
					tooltip: 'Открыть данные человека',
					text: 'Открыть данные человека',
					handler: function() {
						form.openForm(form.RegistryHealDepResErrGrid, {}, 'OpenPerson');
					}
				}
			],
			onRowSelect: function(sm, rowIdx, record) {
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случаи по всем ошибкам' : 'Удалить случаи по всем ошибкам');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
					this.setActionDisabled('action_tehinfo', false);
				}
			},
			onLoadData: function() {
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 3));
				this.setActionDisabled('action_deleteall', (RegistryStatus_id != 3));
			}
		});
		this.RegistryHealDepResErrGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function(row, index) {
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
		this.TFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: form.id+'TFOMSError',
			title:'Итоги проверки ТФОМС',
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
			useEmptyRecord: true,
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
				/*,
				 {name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30}
				 */
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips([
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
			}
		});

		this.TFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
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
		
		this.DataTab = new Ext.TabPanel(
		{
			//resizeTabs:true,
			border: false,
			region: 'center',
			id: form.id+'DataTab',
			activeTab:0,
			//minTabWidth: 140,
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
						form.onRegistrySelect(Registry_id, RegistryType_id, true);
					}
				}
			},
			items:
			[{
				title: '0. Реестр',
				layout: 'fit',
				id: 'tab_registry',
				frame: true,
				iconCls: 'info16',
				//header:false,
				border:false,
				items: [form.RegistryPanel]
				},
				{
					title: '1. Данные',
					layout: 'fit',
					id: 'tab_data',
					iconCls: 'info16',
					//header:false,
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
					iconCls: 'info16',
					border:false,
					items: [form.ErrorComGrid]
				},
				{
					title: '3. Ошибки данных',
					layout: 'fit',
					id: 'tab_dataerr',
					iconCls: 'info16',
					border:false,
					items: 
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.RegistryErrorFiltersPanel,form.ErrorGrid]
					}]
				}, {
					title: '4. Незастрахованные',
					layout: 'fit',
					id: 'tab_datanopolis',
					iconCls: 'info16',
					border:false,
					items: [form.NoPolisGrid]
				}, {
					title: '5. Дубли посещений',
					layout: 'fit',
					iconCls: 'info16',
					id: 'tab_datavizitdouble',
					border: false,
					items: 
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.DoubleVizitFiltersPanel, form.DoubleVizitGrid]
					}]
				}, {
					title: '6. Итоги проверки ТФОМС',
					layout: 'fit',
					iconCls: 'info16',
					id: 'tab_datatfomserr',
					border:false,
					items:
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.RegistryTFOMSFiltersPanel,form.TFOMSErrorGrid]
					}]
				}, {
					title: '6. Итоги проверки МЗ',
					layout: 'fit',
					iconCls: 'info16',
					id: 'tab_datamzerr',
					border:false,
					items:
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [
							form.RegistryHealDepResErrFiltersPanel,
							form.RegistryHealDepResErrGrid
						]
					}]
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
				form.UnionRegistryGrid.setActionDisabled('action_edit', true);
				form.UnionRegistryGrid.setActionDisabled('action_delete', true);
				Ext.getCmp('BlockRegistry').setVisible(false);
				Ext.getCmp('UnblockRegistry').setVisible(false);

				form.UnionRegistryChildGrid.removeAll();
				if (!Ext.isEmpty(record.get('Registry_id')))
				{
					if (record.get('RegistryCheckStatus_SysNick') == 'Blocked') {
						Ext.getCmp('UnblockRegistry').setVisible(true);
					} else if (record.get('RegistryCheckStatus_SysNick') != 'UploadTFOMS') {
						form.UnionRegistryGrid.setActionDisabled('action_edit', false);
						form.UnionRegistryGrid.setActionDisabled('action_delete', false);
						Ext.getCmp('BlockRegistry').setVisible(true);
					}

					form.UnionRegistryChildGrid.loadData({params:{Registry_pid:record.get('Registry_id')}, globalFilters:{Registry_pid:record.get('Registry_id'), start: 0, limit: 100}});
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
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'RegistryGroupType_Name', header: 'Тип объединенного реестра', width: 130},
				{name: 'RegistryGroupType_id', type:'int', hidden: true},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'RegistryCheckStatus_id', type:'int', hidden: true},
				{name: 'RegistryCheckStatus_SysNick', type:'string', hidden: true},
				{name: 'RegistryCheckStatus_Name', type:'string', header: 'Статус', width: 130}
			],
			actions:
			[
				{name:'action_add' },
				{name:'action_edit' },
				{name:'action_view' },
				{name:'action_delete', url: '/?c=Registry&m=deleteUnionRegistry' },
				{
					name: 'action_print',
					menuConfig: {
						printObject: {name: 'printObject', text: langs('Печать'), handler: function(){form.UnionRegistryGrid.printObject()}},
						printObjectList: {name: 'printObjectList', text: langs('Печать текущей страницы'), handler: function(){form.UnionRegistryGrid.printObjectList()}},
						printObjectListFull: {name: 'printObjectListFull', text: langs('Печать всего списка'), handler: function(){form.UnionRegistryGrid.printObjectListFull()}},
						printScetFactura : {
							text: 'Печать счёта-фактуры',
							tooltip: 'Счет-фактура',
							iconCls: 'print16',
							handler: function()
							{
								var current_window = Ext.getCmp('RegistryViewWindow'),
								record = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected(),
									tpl;
								if (!record)
								{
									Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
									return false;
								}
								var Registry_id = record.get('Registry_id');
								if ( !Registry_id )
									return false;

								switch(record.get('RegistryGroupType_id')){
									case 1:
										tpl = 'Registry_Invoice_Print';
										break;
									case 2:
										tpl = 'HTMReg_Invoice_Print';
										break;
								}
								if(!tpl)return false;

								Ext.Msg.show({
									title: 'Вопрос',
									buttons: {yes: "PDF", no: "XLS", cancel: "Отмена"},
									icon: Ext.MessageBox.QUESTION,
									msg: 'Выберите формат печати',
									fn: function(btn) {
										if( btn == 'cancel') {
											return;
										}
										if (btn == 'yes'){
											printBirt({
												'Report_FileName': tpl + '.rptdesign',
												'Report_Params': '&paramRegistryUnion=' + Registry_id,
												'Report_Format': 'pdf'
											});
										}

										if (btn == 'no'){
											printBirt({
												'Report_FileName': tpl + '.rptdesign',
												'Report_Params': '&paramRegistryUnion=' + Registry_id,
												'Report_Format': 'xls'
											});
										}
									}
								});
							}
						}
					}
				}
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
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110},
				{name: 'Registry_rid', type: 'int', header: 'Первичный реестр'}
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
					form.hide()
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