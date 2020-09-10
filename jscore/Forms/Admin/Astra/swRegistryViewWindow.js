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

	/*
	openRegistryEditWindow: function(action)
	{
		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}

		var current_window = this;

		if ( getWnd('swRegistryEditWindow').isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования реестра уже открыто');
			return false;
		}

		var params = new Object();
		var registry_grid = current_window.findById('regvRegistryGrid');
		var registry_tree = current_window.findById('regvRegistryTree');

		if ( !registry_tree.getSelectionModel().getSelectedNode() ) {
			return false;
		}

		if ( registry_tree.getSelectionModel().getSelectedNode().getDepth() != 3 ) {
			return false;
		}

		params.callback = function(data) {
			if ( !data || !data.RegistryData ) {
				registry_grid.getStore().reload();
			}
			else {
				// Добавить или обновить запись в registry_grid
				var record = registry_grid.getStore().getById(data.RegistryData.Registry_id);

				if ( record ) {
					//
				}
			}
		};


	},*/
	onTreeClick: function(node,e)
	{
		var form = this;
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;
		owner.RegistryErrorFiltersPanel.getForm().reset();

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
				form.UnionRegistryGrid.loadData({params:{Lpu_id:Lpu_id}, globalFilters:{Lpu_id:Lpu_id, start: 0, limit: 100}});
				break;
			case 4:
				/*
				owner.findById('regvRightPanel').setVisible(true);
				var Lpu_id = node.parentNode.attributes.object_value;
				var RegistryType_id = node.attributes.object_value;
				owner.AccountGrid.loadData({params:{RegistryType_id:RegistryType_id, Lpu_id:Lpu_id}, globalFilters:{RegistryType_id:RegistryType_id, Lpu_id:Lpu_id}});
				*/
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
				owner.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id!=3));
				owner.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id!=3));
				//log(this.AccountGrid.getGrid().getColumnModel());

				owner.AccountGrid.auditOptions.deleted = (RegistryStatus_id == 6);

				// скрываем/открываем колонку
				owner.AccountGrid.setColumnHidden('RegistryStacType_Name', (RegistryType_id!=1 && RegistryType_id!=14));
				owner.AccountGrid.setColumnHidden('DispClass_Name', (RegistryType_id.inlist([1,2,6,8,10,11,14,15]))); // открыто при 7,9,12
				owner.AccountGrid.setColumnHidden('KatNasel_Name', (RegistryType_id == 1));

				owner.DataGrid.setColumnHidden('Mes_Code', (PayType_SysNick == 'bud' || RegistryType_id != 1));
				owner.DataGrid.setColumnHidden('UslugaComplex_Code', (PayType_SysNick == 'bud' || RegistryType_id != 1));
				owner.DataGrid.setColumnHidden('Diag_Code', PayType_SysNick == 'bud');
				owner.DataGrid.setColumnHidden('RegistryHealDepResType_id', PayType_SysNick != 'bud');
				owner.DataGrid.setColumnHidden('Person_IsBDZ', PayType_SysNick == 'bud');
				owner.DataGrid.setColumnHidden('EvnPS_HTMTicketNum', (PayType_SysNick == 'bud' || RegistryType_id != 14));
				owner.DataGrid.setColumnHidden('EvnPS_HTMBegDate', (PayType_SysNick == 'bud' || RegistryType_id != 14));
				owner.DataGrid.setColumnHidden('EvnPS_HTMHospDate', (PayType_SysNick == 'bud' || RegistryType_id != 14));

				owner.TFOMSErrorGrid.setColumnHidden('Mes_Code', (PayType_SysNick == 'bud' || RegistryType_id != 1));
				owner.TFOMSErrorGrid.setColumnHidden('UslugaComplex_Code', (PayType_SysNick == 'bud' || RegistryType_id != 1));
				owner.TFOMSErrorGrid.setColumnHidden('Diag_Code', PayType_SysNick == 'bud');

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

					// без оплаты
					//owner.DataGrid.setColumnHeader('Evn_setDate', 'Поступление');
					//owner.NoPayGrid.setColumnHidden('Evn_disDate', false);
					/*owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPay', 'К/д к оплате');
					owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdFact', 'К/д факт');
					owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPlan', 'К/д норматив');
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', false);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);*/
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

						// без оплаты
						/*owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPay', 'УЕТ к оплате');
						owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdFact', 'УЕТ факт');
						owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPlan', 'УЕТ норматив');
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', false);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);*/
					} else {
						owner.DataGrid.setColumnHidden('RegistryData_KdPay', true);
						owner.DataGrid.setColumnHidden('RegistryData_KdPlan', true);
						// без оплаты
						/*owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', true);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', true);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', true);*/
					}
				}

				if (RegistryType_id==6) {
					owner.DataGrid.setColumnHeader('LpuSection_name', 'Профиль бригады');
					owner.ErrorGrid.setColumnHeader('LpuSection_name', 'Профиль бригады');
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', 'Профиль бригады');
					// owner.NoPayGrid.setColumnHeader('LpuSection_Name', 'Профиль бригады');
					owner.DoubleVizitGrid.setColumnHeader('LpuSection_FullName', 'Профиль бригады');
				} else {
					owner.DataGrid.setColumnHeader('LpuSection_name', 'Отделение');
					owner.ErrorGrid.setColumnHeader('LpuSection_name', 'Отделение');
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', 'Отделение');
					// owner.NoPayGrid.setColumnHeader('LpuSection_Name', 'Отделение');
					owner.DoubleVizitGrid.setColumnHeader('LpuSection_FullName', 'Отделение');
				}
				
				owner.AccountGrid.setColumnHidden('Registry_IsZNO', PayType_SysNick != 'oms');

				// скрываем/открываем колонку статуса для пол-ки и стаца (по крайней мере, пока)
				// owner.AccountGrid.setColumnHidden('RegistryCheckStatus_Name', (!RegistryType_id.inlist(['1','2','6'])));

				if (!RegistryType_id.inlist(['1','2','6','7','9','11','12','14','15'])) {
					if (owner.DataTab.getActiveTab().id == 'tab_datatfomserr') {
						owner.DataTab.setActiveTab(0);
					}
					owner.DataTab.hideTabStripItem('tab_datatfomserr');
				} else {
					owner.DataTab.unhideTabStripItem('tab_datatfomserr');

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
				}

				owner.AccountGrid.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,14,15])))); // !!! Пока только для полки, потом поправить обратно

				var dataTab = Ext.getCmp('RegistryViewWindowDataTab');
				var account = Ext.getCmp('RegistryViewWindowAccount');
				if (12 == RegistryStatus_id) {
					account.deletedRegistriesSelected = true;
				} else {
					account.deletedRegistriesSelected = false;
				}

				owner.setMenuActions(owner.AccountGrid, RegistryStatus_id, RegistryType_id);

				owner.AccountGrid.getAction('action_yearfilter').setHidden( RegistryStatus_id != 4 && RegistryStatus_id != 12 );
				if( 4 == RegistryStatus_id || 12 == RegistryStatus_id ) {
					owner.constructYearsMenu({RegistryType_id: RegistryType_id, RegistryStatus_id: RegistryStatus_id, Lpu_id: Lpu_id, PayType_SysNick: PayType_SysNick});
				}

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
					form.DataGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					break;
				case 'tab_commonerr':
					form.ErrorComGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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

					form.ErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					break;
				case 'tab_datanopolis':
					form.NoPolisGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					break;
				/*case 'tab_datanopay':
					if ((form.NoPayGrid.getParam('Registry_id')!=Registry_id) || (form.NoPayGrid.getCount()==0))
					{
						form.NoPayGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;*/
				case 'tab_datapersonerr':
					form.PersonErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					break;
				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.loadData({callback: function() {
						form.TFOMSErrorGrid.ownerCt.doLayout();
					}, globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					break;
				case 'tab_datamzerr':
					form.RegistryHealDepResErrGrid.loadData({callback: function() {
						form.RegistryHealDepResErrGrid.ownerCt.doLayout();
					}, globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					break;
				case 'tab_datavizitdouble':
					form.DoubleVizitGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
				/*case 'tab_datanopay':
					form.NoPayGrid.removeAll(true);
					break;*/
				case 'tab_datapersonerr':
					form.PersonErrorGrid.removeAll(true);
					break;
				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.removeAll(true);
					break;
				case 'tab_datamzerr':
					form.RegistryHealDepResErrGrid.removeAll(true);
					break;
				case 'tab_datavizitdouble':
					form.DoubleVizitGrid.removeAll(true);
					break;
			}
		}
		return true;
	},
	onUnionRegistrySelect: function (Registry_id, nofocus, record)
	{
		var form = this;

		var unchekedImg = '<span class="flag-off16" style="padding-left: 16px; padding-top: 2px;" title="Контроль не производился" /></span>';
		var chekedImg = '<span class="flag-ok16" style="padding-left: 16px; padding-top: 2px;" title="Контроль проведён, ошибок нет" /></span>';
		var warningImg = '<span class="flag-warn16" style="padding-left: 16px; padding-top: 2px;" title="Контроль проведен, обнаружены ошибки" /></span>';

		var flkImage = unchekedImg;
		var mekImage = unchekedImg;

		if (record) {
			if (record.get('FlkErrors_IsData') && record.get('FlkErrors_IsData') == 1) {
				flkImage = warningImg;
			}

			if (record.get('MekErrors_IsData') && record.get('MekErrors_IsData') == 1) {
				mekImage = warningImg;
			}
		}

		var TFOMSTitle = 'Пройденные стадии проверки в ТФОМС: '+flkImage+'ФЛК '+mekImage+'МЭК ';

		if (form.UnionTFOMSHeader.getEl()) {
			form.UnionTFOMSHeader.getEl().update(TFOMSTitle);
		}
		// form.TFOMSErrorGrid.ViewGridPanel.setTitle(TFOMSTitle);

		if (form.UnionRegistryGrid.getCount()>0)
		{
			switch (form.UnionDataTab.getActiveTab().id)
			{
				case 'tab_registrys':
					if ((form.UnionRegistryChildGrid.getParam('Registry_id')!=Registry_id) || (form.UnionRegistryChildGrid.getCount()==0))
					{
						form.UnionRegistryChildGrid.loadData({globalFilters:{Registry_id:Registry_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;

				case 'tab_uniondata':
					if ((form.UnionDataGrid.getParam('Registry_id')!=Registry_id) || (form.UnionDataGrid.getCount()==0))
					{
						form.UnionDataGrid.removeAll(true);
						// form.UnionDataGrid.loadData({globalFilters:{Registry_id:Registry_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_uniondatatfomserr':
					var filter_form = this.UnionRegistryTFOMSFiltersPanel.getForm();
					if ((form.UnionTFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.UnionTFOMSErrorGrid.getCount()==0))
					{
						form.UnionTFOMSErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
			}
		}
		else
		{
			switch (form.UnionDataTab.getActiveTab().id)
			{
				case 'tab_registrys':
					form.UnionRegistryChildGrid.removeAll(true);
					break;
				case 'tab_uniondata':
					form.UnionDataGrid.removeAll(true);
					break;
				case 'tab_uniondatatfomserr':
					form.UnionTFOMSErrorGrid.removeAll(true);
					form.UnionBDZErrorGrid.removeAll(true);
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
		var MaxEvn_id = record.get('MaxEvn_id');
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
		} else if (RegistryType_id == 20) {
			var records = new Array();
			
			grid.getGrid().getStore().each(function (record) {
				if (!Ext.isEmpty(record.get('Evn_id'))
					&& !Ext.isEmpty(record.get('MaxEvn_id'))
					&& record.get('MaxEvn_id') == MaxEvn_id) {
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
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var Registry_id = record.get('Registry_id');
		var RegistryType_id = record.get('RegistryType_id');

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
	setUnionRegistryStatus: function(RegistryStatus_id)
	{
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId)
			{
				if (buttonId=='yes') {
					form.getLoadMask().show();
					Ext.Ajax.request({
						url: '/?c=Registry&m=setUnionRegistryStatus',
						params:
							{
								Registry_id: Registry_id,
								RegistryStatus_id: RegistryStatus_id
							},
						callback: function(options, success, response)
						{
							form.getLoadMask().hide();
							if (success)
							{
								var result = Ext.util.JSON.decode(response.responseText);
								if (result.RegistryStatus_id==RegistryStatus_id)
								{
									// Перечитываем грид, чтобы обновить данные по счетам
									form.UnionRegistryGrid.loadData();
								}
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Вы уверены, что хотите отметить реестр оплаченным?<br/>Снятие отметки «Оплачен» будет невозможно.',
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
		var params = {onHide: function() { this.AccountGrid.loadData(); }.createDelegate(this), Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id')};
		getWnd(fd).show(params);
	},
	exportRegistryInog: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		if ( record.get('Registry_IsNeedReform') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Реестр нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр и повторите действие.');
			return false;
		}

		getWnd('swExportRegistryInogWindow').show({
			onHide: function() {
				this.AccountGrid.loadData();
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id')
		});
	},
	importRegistryInog: function() {

		var win = this;

		var params =
		{
			callback: function() {
				win.AccountGrid.getGrid().getStore().reload();
			}.createDelegate(this)
		};

		getWnd('swImportRegistryInogWindow').show(params);

	},
	exportRegistryToXml: function(mode) {
		if ( typeof mode != 'string' || !mode.inlist([ 'simple', 'union' ]) ) {
			return false;
		}

		switch ( mode ) {
			case 'simple':
				var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
				break;

			case 'union':
				var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
				break;
		}

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		if ( record.get('Registry_Count') == 0 && !isSuperAdmin() ) {
			sw.swMsg.alert('Ошибка', 'Экспорт реестра невозможен, нет случаев для экспорта.<br/>');
			return false;
		}

		if ( record.get('Registry_IsNeedReform') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Часть реестров нуждается в переформировании, экспорт не возможен.');
			return false;
		}

		var fd = 'swRegistryXmlWindow';
		var params = {
			onHide: function() {
				switch ( mode ) {
					case 'simple':
						this.AccountGrid.loadData();
						break;

					case 'union':
						this.UnionRegistryGrid.loadData();
						break;
				}
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id'),
			url: '/?c=Registry&m=exportRegistryToXml'
		};

		getWnd(fd).show(params);
	},
    openScetSelectSMO: function()
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

		if ( (record.get('KatNasel_SysNick') == 'oblast' ) || record.get('RegistryType_id') == 1) {
			var params = {
				Registry_id: Registry_id
			};

			getWnd('swScetSelectSMOWindow').show(params);
		}
		else if ( record.get('KatNasel_SysNick') && record.get('KatNasel_SysNick').inlist(['inog', 'allinog']) ) {
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
							'Report_FileName': 'ScetPrintInog.rptdesign',
							'Report_Params': '&paramRegistry_id=' + Registry_id,
							'Report_Format': 'pdf'
						});
					}

					if (btn == 'no'){
						printBirt({
							'Report_FileName': 'ScetPrintInog.rptdesign',
							'Report_Params': '&paramRegistry_id=' + Registry_id,
							'Report_Format': 'xls'
						});
					}
				}
			});
		}
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

		if ( typeof record != 'object' ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var params =
		{
			onHide: function()
			{
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
			KatNasel_id: record.get('KatNasel_id'),
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

		if (!this.printmenu)
			this.printmenu = new Ext.menu.Menu({id:'PrintMenu'});

		var printMenu = [
				{
					text: 'Счет',
					tooltip: 'Счет',
					iconCls: 'print16',
					handler: function()
					{
						form.openScetSelectSMO();
					}
				},
				{
					text: 'Счет-фактура',
					tooltip: 'Счет-фактура',
					iconCls: 'print16',
					handler: function()
					{
						//Ext.Msg.alert('Ошибка', 'Данный функционал находится в разработке.<br/>');
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
										'Report_FileName': 'ScetFactura_Print.rptdesign',
										'Report_Params': '&paramRegistry_id=' + Registry_id,
										'Report_Format': 'pdf'
									});
								}

								if (btn == 'no'){
									printBirt({
										'Report_FileName': 'ScetFactura_Print.rptdesign',
										'Report_Params': '&paramRegistry_id=' + Registry_id,
										'Report_Format': 'xls'
									});
								}
							}
						});
					}
				},
				{
					text: 'Приложение 14',
					tooltip: 'Приложение 14',
					disabled: true,
					iconCls: 'print16',
					handler: function()
					{
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
										'Report_FileName': 'RegistryDataPrint_Inog.rptdesign',
										'Report_Params': '&paramRegistry=' + Registry_id,
										'Report_Format': 'pdf'
									});
								}
								else if (btn == 'no'){
									printBirt({
										'Report_FileName': 'RegistryDataPrint_Inog.rptdesign',
										'Report_Params': '&paramRegistry=' + Registry_id,
										'Report_Format': 'xls'
									});
								}
							}
						});
					}
				}
            ];

		this.printmenu.removeAll();

		for (key in printMenu)
		{
			if (key!='remove')
				this.printmenu.add(printMenu[key]);
		}


        object.addActions({
            name:'action_docs',
            text:'Печать документов',
            iconCls: 'print16',
            menu: this.printmenu
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
				menu =
				[
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
					form.menuActions.refreshRegistry,
					form.menuActions.exportToXml,
					form.menuActions.importRegistryFromTFOMS,,
					'-',
					form.menuActions.registrySetWork,
					form.menuActions.registrySetPaid,
					form.menuActions.exportRegistryInog,
					form.menuActions.importRegistryInog,
					form.menuActions.registrySign,
					form.menuActions.sendRegistryToMZ
				];
				break;

			case 4: // Оплаченные
				menu =
				[
					form.menuActions.exportToXml,
					form.menuActions.setRegistryActive,
					form.menuActions.registrySetUnpaid
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
				Ext.Msg.alert('Ошибка', 'Значение статуса неизвестно! Значение статуса: ' + RegistryStatus_id);
		}

		if (RegistryStatus_id != 12 && RegistryStatus_id != 11) {
			menu.push('-');
			menu.push(form.menuActions.registryErrorExport);
		}

		this.menu.removeAll();
		for (key in menu)
		{
			if (key!='remove')
				this.menu.add(menu[key]);
		}

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
			case 13:
				config.open_form = 'swEvnPLStomEditWindow';
				config.key = 'EvnPLStom_id';
				break;
			case 30:
				config.open_form = 'swEvnPSEditWindow';
				config.key = 'EvnPS_id';
				break;
			case 32:
				config.open_form = 'swEvnPSEditWindow';
				config.key = 'EvnPS_id';
				break;
			case 35:
				config.open_form = 'EvnPLWOWEditWindow';
				config.key = 'EvnPLWOW_id';
				break;
			case 47:
				config.open_form = 'swEvnUslugaParEditWindow';
				if(record.get('isEvnFuncRequest') == true){
					config.open_form = 'swEvnUslugaFuncRequestDicomViewerEditWindow';
				}
				else if(record.get('isEvnLabRequest') == true){
					config.open_form = 'swEvnLabRequestEditWindow';
					config.MedService_id = record.get('MedService_id');
					config.EvnDirection_id = record.get('EvnDirection_id');
				}
				config.key = 'EvnUslugaPar_id';
				break;
			case 8:
				config.open_form = 'swEvnPLDispDopEditWindow';
				config.key = 'EvnPLDispDop_id';
				break;
			case 9:
			case 24:
				config.key = 'EvnPLDispOrp_id';

				switch ( record.get('DispClass_id') ) {
					case 3:
					case 7:
						config.open_form = 'swEvnPLDispOrp13EditWindow';
					break;

					case 4:
					case 8:
						config.open_form = 'swEvnPLDispOrp13SecEditWindow';
					break;
				}
				break;

			case 23:
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

					case 11:
						config.open_form = 'swEvnPLDispTeenInspectionPredSecEditWindow';
					break;

					case 12:
						config.open_form = 'swEvnPLDispTeenInspectionProfSecEditWindow';
					break;
				}
			break;
			case 160:
				config.open_form = 'swEvnUslugaTelemedEditWindow';
				config.key = 'EvnUslugaTelemed_id';
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
				/*case 'OpenEvn':
					type = 2;
					break;*/
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
		var Evn_setDT = null;
		var MedPersonal_id = 0;
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
			case 20:
				var config = form.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;

				// для СМП нет EvnClass, определяем открываемую форму по типу реестра.
				if (RegistryType_id == '6') {
					open_form = 'swCmpCallCardNewCloseCardWindow';
					key = 'CmpCloseCard_id';
				}

				if ( record.get('EvnClass_id') == 160 ) {
					id = record.get('Evn_id');
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
				if(Ext.isEmpty(record.get('CmpCloseCard_id'))){
					id = record.get('Evn_id');
				}
				break;
			case 7:
				open_form = 'swEvnPLDispDop13EditWindow';
				key = 'EvnPLDispDop13_id';
				params.DispClass_id = record.get('DispClass_id');
				break;
			case 9:
				var config = form.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;
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
				params.MedService_id = config.MedService_id;
				params.EvnDirection_id = config.EvnDirection_id;

				id = record.get('Evn_id');
				break;
			case 108:
				open_form = 'swPersonEditWindow';
				key = 'Person_id';
				id = record.get('Person_id');
				if (!Ext.isEmpty(record.get('Evn_setDT'))) {
					Evn_setDT = Ext.util.Format.date(record.get('Evn_setDT'), 'd.m.Y');
				}
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
		if (Evn_setDT) {
			params['Evn_setDT'] = Evn_setDT;
		}
		if (open_form.inlist([ 'swCmpCallCardNewCloseCardWindow', 'swEvnUslugaTelemedEditWindow' ])) { // карты вызова, телемедицинские услуги
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
		this.UnionRegistryGrid.removeAll();

		// Добавляем менюшку с действиями для объединённых реестров
		this.UnionRegistryGrid.addActions({name:'action_isp', iconCls: 'actions16', text: 'Действия', menu: this.UnionActionsMenu});
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
						parentAction = grid.getTopToolbar().items.items[9];
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
			//KatNasel_Name: record.get('KatNasel_Name'),
			LpuBuilding_Name: record.get('LpuBuilding_Name'),
			// RegistryCheckStatus_Name: record.get('RegistryCheckStatus_Name'),
			//PayType_Name: record.get('PayType_Name'),
			Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'),
			Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'),
			Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'),
			ReformTime:record.get('ReformTime'),
			Registry_Count: '<div style="padding:2px;font-size: 12px;">Количество записей в реестре: '+record.get('Registry_Count')+'</div>',
			Registry_Sum: sw.Promed.Format.rurMoney(record.get('Registry_Sum')),
			Registry_SumPaid: sw.Promed.Format.rurMoney(record.get('Registry_SumPaid')),
			RegistryPaid_Count: record.get('Registry_Count') - record.get('RegistryNoPaid_Count'),
			Registry_IsNeedReform: record.get('Registry_IsNeedReform'),
			RegistryNoPay_UKLSum: '<div style="padding:2px;font-size: 12px;color:maroon;">Сумма без оплаты: '+ sw.Promed.Format.rurMoney(record.get('Registry_Sum') - record.get('Registry_SumPaid')) + '</div>',//sw.Promed.Format.rurMoney(record.get('Registry_Sum') - sw.Promed.Format.rurMoney(record.get('Registry_SumPaid')))+'</div>',
			RegistryNoPaid_Count: '<div style="padding:2px;font-size: 12px;color:maroon;">Количество записей без оплаты: '+record.get('RegistryNoPaid_Count')+'</div>'
		};
		if (record.get('RegistryType_id')==1 || record.get('RegistryType_id')==14)
		{
			sparams['Registry_Count'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество госпитализаций, факт: '+record.get('Registry_Count')+'</div>';
			sparams['Registry_RecordPaidCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество госпитализаций, к оплате: '+record.get('Registry_RecordPaidCount')+'</div>';
			sparams['Registry_KdCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество койкодней, факт: '+record.get('Registry_KdCount')+'</div>';
			sparams['Registry_KdPaidCount'] = '<div style="padding:2px;font-size: 12px;color:darkblue;">Количество койкодней, к оплате: '+record.get('Registry_KdPaidCount')+'</div>';
		}
		// Для реестров с типом профосмотры, медосмотры, двн и ддс показываем строку "Тип дисп/медосмотра" на вкладке 0.Реестры
		if (record.get('RegistryType_id').inlist([7,9,11,12]))
		{
			sparams['DispClass_Descr'] = '<div style="padding:2px;font-size: 12px;">Тип дисп/медосмотра: '+record.get('DispClass_Name')+'</div>';
		}

		if (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])) {
			sparams['Registry_RecordPaidCount'] = '<div style="padding:2px;font-size: 12px;">Количество принятых случаев: '+record.get('RegistryHealDepCheckJournal_AccRecCount')+'</div>';
			sparams['Registry_RecordPaidCount'] += '<div style="padding:2px;font-size: 12px;">Количество отклонённых случаев: '+record.get('RegistryHealDepCheckJournal_DecRecCount')+'</div>';
			sparams['Registry_RecordPaidCount'] += '<div style="padding:2px;font-size: 12px;">Количество непроверенных случаев: '+record.get('RegistryHealDepCheckJournal_UncRecCount')+'</div>';
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
					if (RegistryType_id == 20) {
						grid.getGrid().getStore().each(function (r) {
							if (!Ext.isEmpty(r.get('Evn_id'))
								&& !Ext.isEmpty(r.get('MaxEvn_id'))
								&& r.get('MaxEvn_id') == records[rec].data.MaxEvn_id
								&& !r.get('Evn_id').inlist(evn_ids_delete)) {
								count_delete_evn++;
								evn_ids_delete.push(r.get('Evn_id'));
							}
						});
					} else {
						count_delete_evn++;
						evn_ids_delete.push(records[rec].data.Evn_id);
					}
					
				}
				else if(records[rec].data.RegistryData_deleted == 2){
					if (RegistryType_id == 20) {
						grid.getGrid().getStore().each(function (r) {
							if (!Ext.isEmpty(r.get('Evn_id'))
								&& !Ext.isEmpty(r.get('MaxEvn_id'))
								&& r.get('MaxEvn_id') == records[rec].data.MaxEvn_id
								&& !r.get('Evn_id').inlist(evn_ids_recovery)) {
								count_recovery_evn++;
								evn_ids_recovery.push(r.get('Evn_id'));
								
							}
						});
					} else {
						count_recovery_evn++;
						evn_ids_recovery.push(records[rec].data.Evn_id);
						
					}
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
			registrySetUnpaid: new Ext.Action({
				text: 'Снять отметку «оплачен»',
				tooltip: 'Снять отметку «оплачен»',
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
					form.importRegistryFromTFOMS('simple');
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
			exportRegistryInog: new Ext.Action({
				text: 'Экспорт сведений по иногородним',
				tooltip: 'Экспорт сведений по иногородним',
				handler: function() {
					form.exportRegistryInog();
				}
			}),
			importRegistryInog: new Ext.Action({
				text: 'Импорт сведений по иногородним',
				tooltip: 'Импорт сведений по иногородним',
				handler: function() {
					form.importRegistryInog();
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
			//useArrows: false,
			width: 250,
/*
				columns:[{
					dataIndex: 'leafName',
					header: '',
					width: 200
				}, {
					header: '',
					width: 50,
					dataIndex: 'regCount'
				}],
*/
			/*listeners:
			{
				'expandnode': function(node)
				{
						if ( node.id == 'root' ) {
							this.getSelectionModel().select(node.firstChild);
							this.fireEvent('click', node.firstChild);
						}
					}
			},
			*/
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
			auditOptions: {
				field: 'Registry_id',
				key: 'Registry_id',
				deleted: false
			},
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
				//{name: 'KatNasel_id', type: 'int', hidden: true},
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
				{name: 'Registry_IsZNO', type: 'string', header: 'ЗНО', width: 50},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 80},
				{name: 'Registry_RecordPaidCount', type: 'int', hidden: true},
				{name: 'Registry_KdCount', type: 'int', hidden: true},
				{name: 'Registry_KdPaidCount', type: 'int', hidden: true},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				//{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'DispClass_Name', header: 'Тип дисп/медосмотра', width: 140},
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
				{name: 'RegistryCheckStatus_SysNick', type: 'string', hidden: true},
				{name: 'PayType_SysNick', type: 'string', hidden: true},
				{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200},
				{name: 'issetDouble', hidden: true},
				{name: 'RegistryErrorTFOMSType_id', hidden:true},
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
					hidden: true,
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
					/*if (RegistryType_id.inlist([1,2]) && record.get('KatNasel_id') == 2) {
						this.setActionDisabled('action_print', true);
					} else {
						this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6]))));
					}*/

					this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,14,15]))));

					form.onRegistrySelect(Registry_id, RegistryType_id,  false, record);
					this.setActionDisabled('action_edit',(record.get('RegistryStatus_id') != 3)); // #61531
					this.setActionDisabled('action_delete',(record.get('RegistryStatus_id') != 3)); // #61531
					this.setActionDisabled('action_view',false);
					// В прогрессе
					if (record.get('Registry_IsProgress')==1)
					{
						this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete',true);
						this.setActionDisabled('action_view',true);
					}

					var account = Ext.getCmp('RegistryViewWindowAccount');
					var deletedRegistriesSelected = account.deletedRegistriesSelected;

					Ext.menu.MenuMgr.get('PrintMenu').items.itemAt(2).setDisabled(!record.get('KatNasel_SysNick') || !record.get('KatNasel_SysNick').inlist(['inog', 'allinog']));

					//Для папки с удаленными реестрами дизаблим контролы
					if (deletedRegistriesSelected){
						account.setActionDisabled('action_add',true);
						account.setActionDisabled('action_edit',true);
						account.setActionDisabled('action_delete',true);
						account.setActionDisabled('action_view',true);
					} else {
						switch (record.get('RegistryStatus_id')) {
							case 3: // в работе
								form.menuActions.registryErrorExport.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								break;
							case 2: // к оплате
								form.menuActions.registrySetPaid.setHidden(
									(!isSuperAdmin() && !isUserGroup(['RegistryUser']))
									|| (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']))
								);
								form.menuActions.exportRegistryInog.setHidden(
									(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']))
									|| (record.get('RegistryType_id') != 1 && (!record.get('KatNasel_SysNick') || !record.get('KatNasel_SysNick').inlist(['inog', 'allinog'])))
								);
								form.menuActions.importRegistryInog.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registrySign.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.sendRegistryToMZ.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.refreshRegistry.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.importRegistryFromTFOMS.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registryErrorExport.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								form.menuActions.registrySign.setDisabled(!Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')));
								form.menuActions.sendRegistryToMZ.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'SignECP');
								form.menuActions.registrySetWork.setDisabled(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && !Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) && record.get('RegistryCheckStatus_SysNick') != 'SignECP');
								break;
							case 4: // оплаченные
								form.menuActions.setRegistryActive.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registrySetUnpaid.setHidden(!isSuperAdmin() || (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])));
								form.menuActions.registryErrorExport.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								form.menuActions.setRegistryActive.setDisabled(record.get('Registry_IsActive') != 2);
								break;
							case 6: // проверенные МЗ
								form.menuActions.registryErrorExport.setHidden(true);
								form.menuActions.registrySetPaid.setHidden(false);

								form.menuActions.registrySetWork.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'RejectMZ');
								form.menuActions.registrySetPaid.setDisabled(Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) || !record.get('RegistryCheckStatus_SysNick').inlist(['HalfAcceptMZ', 'AcceptMZ']));
								break;
						}
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
						/*case 'tab_datanopay':
							form.NoPayGrid.removeAll(true);
							break;*/
						case 'tab_datapersonerr':
							form.PersonErrorGrid.removeAll(true);
							break;
						case 'tab_datatfomserr':
							form.TFOMSErrorGrid.removeAll(true);
							break;
						case 'tab_datamzerr':
							form.RegistryHealDepResErrGrid.removeAll(true);
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
				// form.DataTab.getItem('tab_datanopay').setIconClass((record.get('RegistryNoPay_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datamzerr').setIconClass((record.get('RegistryErrorMZ_IsData')==1)?'usluga-notok16':'good');
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
			//'<div style="padding:2px;font-size: 12px;">Вид оплаты: {PayType_Name}</div>'+
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

		this.DataGridReset = function(){
			var form = this;
			var filtersForm = form.RegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.UnionDataGrid.removeAll(true);
			form.DataGridSearch();
		};

		this.UnionDataGridSearch = function () {
			var form = this;
			var filtersForm = form.UnionRegistryDataFiltersPanel.getForm();

			var registry = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			if (Registry_id > 0) {
				form.UnionDataGrid.loadData(
					{
						globalFilters:
							{
								Registry_id: Registry_id,
								Person_id: filtersForm.findField('Person_id').getValue(),
								MedPersonal_id: filtersForm.findField('MedPersonal_id').getValue(),
								LpuSection_id: filtersForm.findField('LpuSection_id').getValue(),
								LpuSectionProfile_id: filtersForm.findField('LpuSectionProfile_id').getValue(),
								NumCard: filtersForm.findField('NumCard').getValue(),
								Polis_Num: filtersForm.findField('Polis_Num').getValue(),
								filterRecords: filtersForm.findField('filterRecords').getValue(),
								Evn_id: filtersForm.findField('Evn_id').getValue(),
								start: 0,
								limit: 100
							},
						noFocusOnLoad: false
					});
			}
		};

		// Кнопка "Поиск"
		var rvwDGBtnSearch =  new Ext.Button({
			tooltip: BTN_FRMSEARCH_TIP,
			text: BTN_FRMSEARCH,
			icon: 'img/icons/search16.png',
			iconCls: 'x-btn-text',
			tabIndex: form.firstTabIndex + 16,
			disabled: false,
			handler: function () {
				form.DataGridSearch();
			}
		});

		this.UnionDataGridReset = function () {
			var form = this;
			var filtersForm = form.UnionRegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.UnionDataGrid.removeAll(true);
		};
		// Кнопка Сброс
		var rvwDGBtnReset = new Ext.Button({
			text: BTN_FRMRESET,
			icon: 'img/icons/reset16.png',
			iconCls: 'x-btn-text',
			disabled: false,
			style: 'margin-left: 4px;',
			handler: function () {
				form.DataGridReset();
			}
		});
		rvwDGBtnReset.tabIndex = form.firstTabIndex+17;

		this.RegistryDataFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					form.DataGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
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
							tabIndex: form.firstTabIndex + 10
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
								tabIndex: form.firstTabIndex + 11
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
								tabIndex: form.firstTabIndex + 12
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
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; background:#DFE8F6;'},
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
							tabIndex: form.firstTabIndex + 13
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
								tabIndex: form.firstTabIndex + 15
							}]
					},
					{
						layout: 'form',
						border: false,
						bodyStyle: 'padding-left: 4px;background:#DFE8F6;',
						items: [rvwDGBtnSearch]
					},
					{
						layout: 'form',
						border: false,
						bodyStyle: 'padding-left: 4px;background:#DFE8F6;',
						items: [rvwDGBtnReset]
					}]
			}]
		});

		this.UnionRegistryDataFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 85,
			//title: 'Ввод',
			listeners: {
				render: function () {
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());

					setLpuSectionGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
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
				layout: 'column',
				bodyStyle: 'background:transparent;',
				defaults: {bodyStyle: 'padding-left: 4px; padding-top: 4px; background:transparent;'},
				border: false,
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .5,
					labelWidth: 100,
					labelAlign: 'right',
					items:
						[{
							anchor: '100%',
							fieldLabel: 'ФИО',
							readOnly: true,
							hiddenName: 'Person_id',
							xtype: 'swpersoncombo',
							onTrigger1Click: function () {
								if (this.disabled) return false;
								var combo = this;
								getWnd('swPersonSearchWindow').show({
									onSelect: function (personData) {
										if (personData.Person_id > 0) {
											combo.getStore().loadData([{
												Person_id: personData.Person_id,
												Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
											}]);
											combo.setValue(personData.Person_id);
											combo.collapse();
											combo.focus(true, 500);
											combo.fireEvent('change', combo);
										}
										getWnd('swPersonSearchWindow').hide();
									},
									onClose: function () {
										combo.focus(true, 500)
									}
								});
							},
							tabIndex: form.firstTabIndex + 10
						}]
				},
					{
						layout: 'form',
						border: false,
						columnWidth: .3,
						labelWidth: 100,
						labelAlign: 'right',
						items:
							[{
								anchor: '100%',
								fieldLabel: 'Номер карты',
								name: 'NumCard',
								xtype: 'textfield',
								tabIndex: form.firstTabIndex + 12
							}]
					}]
			}, {
				layout: 'column',
				bodyStyle: 'background:transparent;',
				defaults: {bodyStyle: 'padding-left: 4px; background:transparent;'},
				border: false,
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .25,
					hidden: !isAdmin,
					labelWidth: 100,
					labelAlign: 'right',
					items: [{
						anchor: '100%',
						fieldLabel: 'Отделение',
						listWidth: 400,
						hiddenName: 'LpuSection_id',
						xtype: 'swlpusectioncombo',
						tabIndex: form.firstTabIndex + 13
					}, {
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex + 13
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .25,
					labelWidth: 100,
					labelAlign: 'right',
					items: [{
						anchor: '100%',
						fieldLabel: 'Врач',
						allowBlank: true,
						editable: true,
						forceSelection: true,
						listWidth: 400,
						hiddenName: 'MedPersonal_id',
						xtype: 'swmedpersonalcombo',
						tabIndex: form.firstTabIndex + 13
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex + 14,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .3,
					labelWidth: 100,
					labelAlign: 'right',
					items: [{
						anchor: '100%',
						fieldLabel: 'Профиль',
						listWidth: 400,
						hiddenName: 'LpuSectionProfile_id',
						xtype: 'swlpusectionprofilecombo',
						tabIndex: form.firstTabIndex + 13
					}, {
						anchor: '100%',
						xtype: 'combo',
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
						tabIndex: form.firstTabIndex + 15
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 100,
					labelAlign: 'right',
					items: [{
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex + 16,
						disabled: false,
						handler: function () {
							form.UnionDataGridReset();
						}
					}, {
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex + 16,
						disabled: false,
						handler: function () {
							form.UnionDataGridSearch();
						}
					}]
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
			listeners: {
				render: function() {
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
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
				{name: 'CmpCallCard_id', type: 'int', hidden:true},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
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
				{name: 'EvnPL_NumCard', header: '№ талона', width: 60},
				{name: 'EvnPS_HTMTicketNum', header: '№ талона на ВМП', width: 60},
				{name: 'EvnPS_HTMBegDate', type: 'date', header: 'Дата выдачи талона на ВМП', width: 100},
				{name: 'EvnPS_HTMHospDate', type: 'date', header: 'Дата планируемой госпитализации (ВМП)', width: 100},
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
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'К/д факт', width: 70},
				{name: 'RegistryData_KdPlan', header: 'К/д норматив', width: 70},
				{name: 'RegistryData_KdPay', header: 'К/д к оплате', width: 70},
				{name: 'Diag_Code', header: 'Диагноз', width: 70},
				{name: 'UslugaComplex_Code', header: 'Услуга', width: 70},
				{name: 'Mes_Code', header: 'КСГ', width: 50},
				{name: 'RegistryData_TotalSum', type: 'money', header: 'Общая сумма', width: 70},
				{name: 'RegistryData_Tariff', type: 'money', header: 'Тариф (5 статей+содержание)', width: 70},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма (5 статей+содержание)', width: 90},
				{name: 'RegistryData_Tariff2', type: 'money', header: 'Тариф (стимулирующая)', width: 70},
				{name: 'RegistryData_ItogSum2', type: 'money', header: 'Сумма (стимулирующая)', width: 90},
				{name: 'PayMedType_Code', header: 'Код способа оплаты', width: 90},
				{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
				//{name: 'timeReform', type: 'datetimesec', header: 'Изменена', width: 100},
				{name: 'Err_Count', hidden:true},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'RegistryData_IsPaid', type: 'int', hidden:true},
				{name: 'MaxEvn_id', type: 'int', hidden:true, group: true},
				{name: 'isEvnFuncRequest', type: 'bool', hidden:true},
				{name: 'isEvnLabRequest', type: 'bool', hidden:true},
				{name: 'MedService_id', type: 'int', hidden:true},
				{name: 'EvnDirection_id', type: 'int', hidden:true}
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
				if(this.showGroup){
					form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
				}else{
					form.DataGrid.getGrid().getStore().clearGrouping()
				}
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
				form.DataGrid.getGrid().getSelectionModel().clearSelections();
			},
			onDblClick: function()
			{
				Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});
			},
			onEnter: function()
			{
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

				if ((row.get('IsRDL')>0) && (isAdmin))
					cls = cls+'x-grid-rowblue ';
				if (row.get('Err_Count') > 0 || row.get('RegistryData_IsPaid') == 1 || row.get('RegistryHealDepResType_id') == 2)
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

		// Данные реестра
		this.UnionDataGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionData',
			title: 'Данные',
			object: 'RegistryData',
			region: 'center',
			//editformclassname: 'swLpuSectionShiftEditForm',
			dataUrl: '/?c=Registry&m=loadUnionRegistryData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Evn_id', type: 'int', header: 'ИД случая', key: true, hidden: false},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
				{name: 'Evn_rid', hidden: true},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden: true},
				{name: 'RegistryCheckStatus_SysNick', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'DispClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'archiveRecord', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'IsRDL', type: 'int', hidden: true},
				{name: 'needReform', type: 'int', hidden: true},
				{name: 'isNoEdit', type: 'int', hidden: true},
				{name: 'EvnPL_NumCard', header: '№ талона', width: 60},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 80},
				{name: 'Person_IsBDZ', header: 'БДЗ', type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'К/д факт', width: 70},
				{name: 'RegistryData_KdPlan', header: 'К/д норматив', width: 70},
				{name: 'RegistryData_KdPay', header: 'К/д к оплате', width: 70},
				{name: 'RegistryData_Tariff', type: 'money', header: 'Тариф', width: 70},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
				{
					name: 'checkReform',
					header: '<img src="/img/grid/hourglass.gif" />',
					width: 35,
					renderer: sw.Promed.Format.waitColumn
				},
				//{name: 'timeReform',id:'timeReform', type: 'datetimesec', header: 'Изменена', width: 100},
				{name: 'ErrTfoms_Count', hidden: true},
				{name: 'RegistryData_deleted', hidden: true},
				{name: 'RegistryData_IsPaid', type: 'int', hidden: true},
				{name: 'IsGroupEvn', type: 'int', hidden: true}
			],
			actions:
				[
					// https://redmine.swan.perm.ru/issues/12624
					// Для Перми редактирование данных и удаление человека закрыто
					{
						name: 'action_setperson',
						disabled: true,
						hidden: true,
						tooltip: 'Редактировать данные человека в реестре',
						text: '<b>Редактировать данные человека</b>',
						handler: function () {
							form.setPerson();
						}
					},
					{
						name: 'action_delperson',
						disabled: true,
						hidden: true,
						tooltip: 'Удалить созданные данные человека в реестре',
						text: 'Удалить данные человека',
						handler: function () {
							form.deletePerson();
						}
					},
					{name: 'action_add', disabled: true, hidden: true},
					{
						name: 'action_edit', handler: function () {
							form.openForm(form.UnionDataGrid, {});
						}
					},
					{
						name: 'action_view',
						disabled: (!isUserGroup(['RegistryUser']) && !isSuperAdmin()),
						hidden: false,
						text: 'Сменить пациента в учетном документе',
						tooltip: 'Изменить пациента в учетном документе',
						icon: 'img/icons/doubles16.png',
						handler: function () {
							form.changePerson();
						}
					},
					{
						name: 'action_delete', disabled: true, handler: function () {
							form.deleteRegistryData(form.UnionDataGrid, false);
						}
					},
					{
						name: 'action_openperson',
						visible: !isAdmin,
						icon: 'img/icons/patient16.png',
						tooltip: 'Открыть данные человека',
						text: 'Открыть данные человека',
						handler: function () {
							form.openForm(form.UnionDataGrid, {}, 'OpenPerson');
						}
					},
					{
						name: 'action_addmeksmo', text: 'Добавить ошибку МЭК от СМО', handler: function () {
							this.addMekSmo();
						}.createDelegate(this)
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
				form.setNeedReform(record);
			},
			onDblClick: function () {
				form.openForm(form.UnionDataGrid, {});
			},
			onEnter: function () {
				form.openForm(form.UnionDataGrid, {});
			},
			onRowSelect: function (sm, rowIdx, record) {
				var form = this;

				var unionRecord = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
				form.UnionDataGrid.setActionDisabled('action_addmeksmo', (unionRecord.get('RegistryCheckStatus_SysNick') != 10));

				// Меняем текст акшена удаления в зависимости от данных
				form.UnionDataGrid.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить' : 'Удалить');

				if ((isUserGroup(['RegistryUser']) || isSuperAdmin())
					&& record && record.get('RegistryType_id') && record.get('RegistryType_id').toString().inlist(['1', '2', '6', '7', '8', '9', '10', '11', '12', '14', '15', '16'])
				) {
					form.UnionDataGrid.setActionDisabled('action_view', false);
				}
				else {
					form.UnionDataGrid.setActionDisabled('action_view', true);
				}
			}.createDelegate(this)
		});

		this.UnionDataGrid.getGrid().view = new Ext.grid.GridView({
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
			listeners:
				{
					rowupdated: function (view, first, record) {
						//log('update');
						view.getRowClass(record);
					}
				}
		});

		// 2. Общие ошибки
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
				{name: 'Evn_setDT', type: 'datetime', hidden:true},
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
			}
		});


		// 5. Случаи без оплаты
		/* this.NoPayGrid = new sw.Promed.ViewFrame(
		{
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
			stringfields:
			[
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
				//{name: 'Evn_setDate',  type: 'date', header: 'Посещение', width: 150},
				//{name: 'Evn_disDate',  type: 'date', header: 'Выписка', width: 150},
				{name: 'RegistryNoPay_KdFact', header: 'К/д факт', width: 70},
				{name: 'RegistryNoPay_KdPlan', header: 'К/д норматив', width: 70},
				{name: 'RegistryNoPay_KdPay', header: 'К/д к оплате', width: 70},
				{name: 'RegistryNoPay_Tariff', type: 'money', header: 'Тариф', width: 70},
				{name: 'RegistryNoPay_UKLSum', type: 'money', header: 'Сумма', width: 90}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').NoPayGrid, {}, 'OpenPerson');}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function()
			{
			}
		});*/

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
				//{name: 'Server_id', isparams: true, type: 'int', hidden:true, value: 0},
				//{name: 'PersonEvn_id', isparams: true, type: 'int', hidden:true},
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
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id,
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
			}
		};

		this.RegistryHealDepResErrGridSearch = function() {
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
		};

		this.UnionTFOMSGridSearch = function()
		{
			var form = this;
			var filtersForm = form.UnionRegistryTFOMSFiltersPanel.getForm();

			var registry = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			if (Registry_id > 0)
			{
				form.UnionTFOMSErrorGrid.loadData({
					callback: function () {
						form.UnionTFOMSErrorGrid.ownerCt.doLayout();
					},
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

		this.UnionTFOMSHeader = new Ext.form.Label({
			html: ""
		});

		this.UnionRegistryTFOMSFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					form.UnionTFOMSGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'form',
				border: false,
				bodyStyle: 'padding: 4px;background:#DFE8F6;',
				items:
					[
						form.UnionTFOMSHeader
					]
			}, {
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; background:#DFE8F6;'},
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
							tabIndex: form.firstTabIndex + 23
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
							xtype: 'textfield',
							tabIndex: form.firstTabIndex + 24
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
					bodyStyle: 'padding-left: 4px;background:#DFE8F6;',
					columnWidth: .1,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex + 26,
						disabled: false,
						handler: function () {
							form.UnionTFOMSGridSearch();
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
		this.TFOMSErrorGrid = new sw.Promed.ViewFrame(
		{
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
			useEmptyRecord: false,
			selectionModel: 'multiselect',
			stringfields:
			[
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden:false},
				{name: 'CmpCallCard_id', type: 'int', hidden:true},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorType_Name', header: 'Наименование ошибки', width: 200},
				{name: 'RegistryErrorType_Descr', header: 'Описание ошибки', width: 200},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'RegistryErrorTFOMS_CommentCalc', header: 'Расчет ТФОМС', width: 200},
				{name: 'RegistryErrorTFOMS_FieldName', header: 'Имя поля', width: 80},
				{name: 'RegistryErrorTFOMS_BaseElement', header: 'Базовый элемент', width: 80},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'Diag_Code', header: 'Диагноз', width: 70},
				{name: 'UslugaComplex_Code', header: 'Услуга', width: 70},
				{name: 'Mes_Code', header: 'КСГ', width: 50},
				/*,
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30}
				*/
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

		// Ошибки ТФОМС
		this.UnionTFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionTFOMSError',
			title: 'Ошибки стадий ФЛК и МЭК',
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
			stringfields:
				[
					{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
					{name: 'Evn_id', type: 'int', header: 'ИД случая', hidden: false},
					{name: 'CmpCloseCard_id', type: 'int', hidden: true},
					{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
					{name: 'RegistryData_deleted', type: 'int', hidden: true},
					{name: 'RegistryData_notexist', type: 'int', hidden: true},
					{name: 'Person_id', type: 'int', header: 'Person_id', hidden: !isSuperAdmin()},
					{name: 'Evn_rid', type: 'int', hidden: true},
					{name: 'EvnClass_id', type: 'int', hidden: true},
					{name: 'DispClass_id', type: 'int', hidden: true},
					{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
					{name: 'RegistryErrorTFOMSLevel_Name', header: 'Уровень ошибки', width: 120},
					{name: 'RegistryError_FieldName', header: 'Ошибка', width: 250},
					{name: 'RegistryError_Comment', header: 'Описание ошибки', autoexpand: true},
					{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
					{name: 'Registry_id', type: 'int', hidden: true},
					{name: 'RegistryType_id', type: 'int', hidden: true},
					{name: 'OrgSMO_id', type: 'int', hidden: true},
					{name: 'Server_id', type: 'int', hidden: true},
					{name: 'PersonEvn_id', type: 'int', hidden: true},
					{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
					{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
					{name: 'RegistryErrorTFOMS_Source', type: 'string', header: 'Источник', width: 150},
					{name: 'UslugaComplex_Code', type: 'string', header: 'Код услуги', width: 100},
					{name: 'UslugaComplex_Name', type: 'string', header: 'Наименование услуги', width: 200},
					{name: 'RegistryErrorTFOMS_FieldName', hidden: true},
					{name: 'RegistryErrorTFOMS_BaseElement', hidden: true},
					{name: 'IsGroupEvn', type: 'int', hidden: true}
					/*,
					{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 30}
					*/
				],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
					[
						{field: 'RegistryError_Comment', tpl: '{RegistryError_Comment}'}
					])
			],
			actions:
				[
					{name: 'action_add', disabled: true, hidden: true},
					{
						name: 'action_edit', text: '<b>Исправить</b>', handler: function () {
							form.openForm(form.UnionTFOMSErrorGrid, {});
						}
					},
					{name: 'action_view', disabled: true, hidden: true},
					{
						name: 'action_delete', text: 'Удалить случай из реестра', handler: function () {
							form.deleteRegistryData(form.UnionTFOMSErrorGrid, false);
						}
					},
					{
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
						name: 'action_deleteall',
						icon: 'img/icons/delete16.png',
						text: 'Удалить случаи по всем ошибкам',
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
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случаи по всем ошибкам' : 'Удалить случаи по всем ошибкам');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_deleteerror', !Ext.isEmpty(record.get('OrgSMO_id')));
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
					this.setActionDisabled('action_tehinfo', false);
				}
			}
		});
		this.UnionTFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
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
                {name: 'CmpCallCard_id', type: 'int', hidden: true},
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
				}/*,
				render: function() {
					if(!isAdmin)
						this.hideTabStripItem(this.getItem('tab_datavizitdouble'));
				}*/
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
					items: [form.NoPolisGrid]
				},
/*				{
					title: '5. Случаи без оплаты',
					layout: 'fit',
					id: 'tab_datanopay',
					iconCls: 'good',
					border:false,
					items: [form.NoPayGrid]
				},
*/
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
					title: '6. Итоги проверки МЗ',
					layout: 'fit',
					iconCls: 'good',
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
			id: form.id+'RegistryListPanel',
			layout:'border',
			defaults: {split: true},
			items: [form.AccountGrid, form.DataTab]
		});

		this.UnionRegistryGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionRegistryGrid',
			region: 'north',
			height: 203,
			title: 'Объединённые реестры',
			object: 'Registry',
			editformclassname: 'swUnionRegistryEditWindow',
			dataUrl: '/?c=Registry&m=loadUnionRegistryGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function (sm, rowIdx, record) {
				var Registry_id = record.get('Registry_id');
				form.onUnionRegistrySelect(Registry_id, false, record);

				form.UnionActionsMenu.items.items[0].disable();
				form.UnionActionsMenu.items.items[1].disable();
				form.UnionActionsMenu.items.items[2].disable();

				if (!Ext.isEmpty(Registry_id)) {
					form.UnionActionsMenu.items.items[0].enable();
					if (record.get('RegistryStatus_id') != 4) {
						form.UnionActionsMenu.items.items[1].enable();
						if (isUserGroup(['RegistryUser']) || isSuperAdmin()) {
							form.UnionActionsMenu.items.items[2].enable(); // Отметить как оплаченный
						}
					}
				}
			},
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type: 'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type: 'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_IsZNO', type: 'string', header: 'ЗНО', width: 50},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 110},
				{name: 'Registry_Sum', type: 'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_NoErrSum', type: 'money', header: 'Сумма без ошибок', width: 100},
				{name: 'Registry_SumPaid', type: 'money', header: 'Сумма к оплате', width: 100},
				{name: 'Registry_updDT', type: 'date', header: 'Дата изменения', width: 110},
				{name: 'Registry_sendDate', header: 'Последняя отправка', width: 110},
				{name: 'MekErrors_IsData', hidden: true},
				{name: 'FlkErrors_IsData', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'RegistryCheckStatus_id', hidden: true},
				{name: 'RegistryCheckStatus_SysNick', hidden: true},
				{name: 'RegistryStatus_Name', header: 'Статус', width: 200}
			],
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', url: '/?c=Registry&m=deleteUnionRegistry'}
			]
		});

		this.UnionActionsMenu = new Ext.menu.Menu({
			items: [
				{name:'action_expxml', text:'Экспорт в XML', handler: function() { this.exportRegistryToXml('union'); }.createDelegate(this)},
				{name:'action_impxml', text:'Импорт реестра из ТФОМС', handler: function() { this.importRegistryFromTFOMS('union'); }.createDelegate(this)},
				{name:'action_setpaid', text:'Отметить как оплаченный', handler: function() { this.setUnionRegistryStatus(4); }.createDelegate(this)}
			]
		});

		this.UnionRegistryChildGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionRegistryChildGrid',
			region: 'center',
			title: 'Реестры',
			object: 'Registry',
			dataUrl: '/?c=Registry&m=loadUnionRegistryChildGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields: [
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden: !isSuperAdmin()},
				{name: 'Registry_Num', header: 'Номер', width: 80},
				{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
				{name: 'Registry_begDate', type: 'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type: 'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 80},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
				{name: 'Registry_Sum', type: 'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_NoErrSum', type: 'money', header: 'Сумма без ошибок', width: 100},
				{name: 'Registry_SumPaid', type: 'money', header: 'Сумма к оплате', width: 100},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
				{name: 'Registry_updDate', header: 'Дата изменения', width: 110},
				{name: 'Registry_sendDate', header: 'Последняя отправка', width: 110},
				{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200}
			],
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{name: 'action_edit', disabled: true, hidden: true},
				{name: 'action_view', disabled: true, hidden: true},
				{name: 'action_delete', disabled: true, hidden: true}
			]
		});

		this.UnionDataTab = new Ext.TabPanel({
			//resizeTabs:true,
			border: false,
			region: 'center',
			activeTab: 0,
			//minTabWidth: 140,
			enableTabScroll: true,
			autoScroll: true,
			defaults: {bodyStyle: 'width:100%;'},
			layoutOnTabChange: true,
			listeners: {
				tabchange: function (tab, panel) {
					var record = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
					if (record) {
						var Registry_id = record.get('Registry_id');
						form.onUnionRegistrySelect(Registry_id, true, record);
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
				//iconCls: 'info16',
				border: false,
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						form.UnionRegistryDataFiltersPanel,
						form.UnionDataGrid
					]
				}]
			}, {
				title: '2. Итоги проверки ТФОМС',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_uniondatatfomserr',
				border: false,
				items: [{
					border: false,
					layout: 'border',
					region: 'center',
					items: [
						form.UnionRegistryTFOMSFiltersPanel,
						form.UnionTFOMSErrorGrid
					]
				}]
			}]
		});


		this.UnionRegistryListPanel = new sw.Promed.Panel({
			border: false,
			id: form.id + 'UnionRegistryListPanel',
			layout:'border',
			defaults: {split: true},
			items: [
				form.UnionRegistryGrid,
				form.UnionDataTab
			]
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