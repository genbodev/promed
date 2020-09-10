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
		
		if(node.id.indexOf('PayType.1.mbudtrans') !== -1){
			PayType_SysNick = 'mbudtrans';
		}
		if(node.id.indexOf('PayType.1.mbudtrans_mbud') !== -1){
			PayType_SysNick = 'mbudtrans_mbud';
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
				owner.UnionRegistryChildGrid.setColumnHidden('KatNasel_Name', (PayType_SysNick=='mbudtrans'));
				
				var Lpu_id = node.parentNode.parentNode.attributes.object_value;
				form.UnionRegistryGrid.loadData({
					params: {Lpu_id: Lpu_id, Registry_IsNew: owner.Registry_IsNew, PayType_SysNick: PayType_SysNick},
					globalFilters: {Lpu_id: Lpu_id, Registry_IsNew: owner.Registry_IsNew, PayType_SysNick: PayType_SysNick, start: 0, limit: 100}
				});
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
				owner.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id!=3) || !(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
				//log(this.AccountGrid.getGrid().getColumnModel());

				// скрываем/открываем колонку
				owner.AccountGrid.setColumnHidden('RegistryUnion_Num', !RegistryStatus_id.inlist([4,5]));
				owner.AccountGrid.setColumnHidden('RegistryStacType_Name', (PayType_SysNick=='mbudtrans' || (RegistryType_id!=1 && RegistryType_id!=14)));

				owner.AccountGrid.setColumnHidden('LpuBuilding_Name', (RegistryType_id.inlist([7,8,9,10,11,12])));
				owner.AccountGrid.setColumnHidden('KatNasel_Name', (PayType_SysNick=='mbudtrans' || RegistryType_id.inlist([8,9,10])));
				owner.AccountGrid.setColumnHidden('DispClass_Name', (RegistryType_id.inlist([1,2,6,8,10,14,15,16]))); // открыто при 7,9,11,12

				owner.DataGrid.setColumnHidden('RegistryHealDepResType_id', PayType_SysNick != 'bud');
				owner.DataGrid.setColumnHidden('Person_IsBDZ', PayType_SysNick == 'bud');

				if (RegistryType_id==6) {
					owner.DataGrid.setColumnHeader('LpuSection_name', 'Профиль бригады');
					owner.ErrorGrid.setColumnHeader('LpuSection_name', 'Профиль бригады');
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', 'Профиль бригады');
					owner.NoPayGrid.setColumnHeader('LpuSection_Name', 'Профиль бригады');
				} else {
					owner.DataGrid.setColumnHeader('LpuSection_name', 'Отделение');
					owner.ErrorGrid.setColumnHeader('LpuSection_name', 'Отделение');
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', 'Отделение');
					owner.NoPayGrid.setColumnHeader('LpuSection_Name', 'Отделение');
				}

				if(PayType_SysNick==='mbudtrans'){
					form.DataTab.unhideTabStripItem('tab_datanopolis');
					form.DataTab.hideTabStripItem('tab_datanopay');
				}else{
					form.DataTab.unhideTabStripItem('tab_datanopolis');
					form.DataTab.unhideTabStripItem('tab_datanopay');
				}
				if (RegistryType_id==2) {
					owner.DataTab.unhideTabStripItem('tab_evnplcrossed');
					owner.DataTab.unhideTabStripItem('tab_datapldouble');
				}
				else {
					if (owner.DataTab.getActiveTab().id == 'tab_evnplcrossed' || owner.DataTab.getActiveTab().id == 'tab_datapldouble') {
						owner.DataTab.setActiveTab(0);
					}
					owner.DataTab.hideTabStripItem('tab_evnplcrossed');
					owner.DataTab.hideTabStripItem('tab_datapldouble');
				}

				// скрываем/открываем колонку статуса для пол-ки и стаца и дд (по крайней мере, пока)
				owner.AccountGrid.setColumnHidden('RegistryCheckStatus_Name', (!RegistryType_id.inlist(['1','2','6','7','8','9','10','11','12','14','15','16'])));

				// Показваем ошибки ТФОМС только для полки и стаца и СМП и дд (по крайней мере, пока)
				if (!RegistryType_id.inlist(['1','2','6','7','8','9','10','11','12','14','15','16'])) {
					if (owner.DataTab.getActiveTab().id == 'tab_datatfomserr' || owner.DataTab.getActiveTab().id == 'tab_datamzerr') {
						owner.DataTab.setActiveTab(0);
					}
					owner.DataTab.hideTabStripItem('tab_datatfomserr');
					owner.DataTab.hideTabStripItem('tab_datamzerr');
				} else {
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

				owner.AccountGrid.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12,14,15,16])))); // !!! Пока только для полки, потом поправить обратно

				if (12 == RegistryStatus_id) {
					owner.AccountGrid.deletedRegistriesSelected = true;
				} else {
					owner.AccountGrid.deletedRegistriesSelected = false;
				}

				owner.setMenuActions(owner.AccountGrid, RegistryStatus_id, RegistryType_id);

				owner.AccountGrid.getAction('action_yearfilter').setHidden( !RegistryStatus_id.inlist([4,12]) );
				if( RegistryStatus_id.inlist([4,12]) ) {
					owner.constructYearsMenu({RegistryType_id: RegistryType_id, RegistryStatus_id: RegistryStatus_id, Lpu_id: Lpu_id});
				}

				owner.AccountGrid.showArchive = false;
				owner.AccountGrid.loadData({
					params: {
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id,
						Registry_IsNew: owner.Registry_IsNew,
						PayType_SysNick: PayType_SysNick
					},
					globalFilters: {
						RegistryType_id: RegistryType_id,
						RegistryStatus_id: RegistryStatus_id,
						Lpu_id: Lpu_id,
						Registry_IsNew: owner.Registry_IsNew,
						PayType_SysNick: PayType_SysNick
					}
				});
				owner.AccountGrid.getAction('action_new').setHidden(!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
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
				scope : form,
				fn: function(buttonId)
				{
					/*
					if ( buttonId == 'ok' )
					{
						// Может быть повторный опрос :)
						//form.onIsRunQueue();
					}
					else
					{
					 form.hide();
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

		RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;

		var unchekedImg = '<span class="flag-off16" style="padding-left: 16px; padding-top: 2px;" title="Контроль не производился" /></span>';
		var chekedImg = '<span class="flag-ok16" style="padding-left: 16px; padding-top: 2px;" title="Контроль проведён, ошибок нет" /></span>';
		var warningImg = '<span class="flag-warn16" style="padding-left: 16px; padding-top: 2px;" title="Контроль проведен, обнаружены ошибки" /></span>';

		var flkImage = unchekedImg;
		var bdzImage = unchekedImg;
		var mekImage = unchekedImg;

		if (record) {
			if (record.get('FlkErrors_IsData') && record.get('FlkErrors_IsData') == 1) {
				flkImage = warningImg;
			} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([2, 3, 4, 6, 7, 8])) {
				flkImage = chekedImg;
			}

			if (record.get('BdzErrors_IsData') && record.get('BdzErrors_IsData') == 1) {
				bdzImage = warningImg;
			} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([3, 6, 7, 8])) {
				bdzImage = chekedImg;
			}

			if (record.get('MekErrors_IsData') && record.get('MekErrors_IsData') == 1) {
				mekImage = warningImg;
			} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([3, 8])) {
				mekImage = chekedImg;
			}
		}

		var TFOMSTitle = 'Пройденные стадии проверки в ТФОМС: '+flkImage+'ФЛК '+bdzImage+'БДЗ '+mekImage+'МЭК ';

		if (form.TFOMSHeader.getEl()) {
			form.TFOMSHeader.getEl().update(TFOMSTitle);
		}
		// form.TFOMSErrorGrid.ViewGridPanel.setTitle(TFOMSTitle);

        if (!Ext.isEmpty(Registry_id))
		{
			var archiveRecord = 0;
			if (record.get('archiveRecord') && record.get('archiveRecord') == 1) {
				archiveRecord = 1;
			}
			switch (form.DataTab.getActiveTab().id)
			{
				case 'tab_registry':
					// бряк!
					break;

				case 'tab_data':
					if ((form.DataGrid.getParam('Registry_id')!=Registry_id) || (form.DataGrid.getCount()==0))
					{
                        form.DataGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_commonerr':
					if ((form.ErrorComGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorComGrid.getCount()==0))
					{
						form.ErrorComGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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

					if ((form.ErrorGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorGrid.getCount()==0))
					{
						form.ErrorGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datanopolis':
					if ((form.NoPolisGrid.getParam('Registry_id')!=Registry_id || form.NoPolisGrid.getCount()==0) && record.get('PayType_SysNick') != 'mbudtrans')
					{
						form.NoPolisGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}else if(record.get('PayType_SysNick') == 'mbudtrans'){
						form.NoPolisGrid.removeAll();
					}
					break;
				case 'tab_datanopay':
					if ((form.NoPayGrid.getParam('Registry_id')!=Registry_id) || (form.NoPayGrid.getCount()==0))
					{
						form.NoPayGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datapersonerr':
					if ((form.PersonErrorGrid.getParam('Registry_id')!=Registry_id) || (form.PersonErrorGrid.getCount()==0))
					{
						form.PersonErrorGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datatfomserr':
					var filter_form = this.RegistryTFOMSFiltersPanel.getForm();
					if ((form.TFOMSErrorGrid.getParam('Registry_id')!=Registry_id) || (form.TFOMSErrorGrid.getCount()==0))
					{
						form.TFOMSErrorGrid.loadData({callback: function() {
							// если записей нет, нужно грид скрыть
							if (form.TFOMSErrorGrid.getGrid().getStore().getCount() > 0) {
								form.TFOMSErrorGrid.show();
								// если тфомс показывается то высота бдз = 200
								form.BDZErrorGrid.setHeight(200);
							} else {
								form.TFOMSErrorGrid.hide();
								// если тфомс скрывается то высота бдз = высоте родительского контейнера
								form.BDZErrorGrid.setHeight(form.BDZErrorGrid.ownerCt.getEl().getHeight()-55);
							}
							form.TFOMSErrorGrid.ownerCt.doLayout();
						}, globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					if ((form.BDZErrorGrid.getParam('Registry_id')!=Registry_id) || (form.BDZErrorGrid.getCount()==0))
					{
						form.BDZErrorGrid.loadData({callback: function() {
							// если записей нет, нужно грид скрыть
							if (form.BDZErrorGrid.getGrid().getStore().getCount() > 0) {
								form.BDZErrorGrid.show();
								// filter_form.findField('RegistryErrorBDZType_id').showContainer();
							} else {
								form.BDZErrorGrid.hide();
								// filter_form.findField('RegistryErrorBDZType_id').hideContainer();
							}
							form.BDZErrorGrid.ownerCt.doLayout();
						}, globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datamzerr':
					var filter_form = this.RegistryHealDepResErrFiltersPanel.getForm();
					if ((form.RegistryHealDepResErrGrid.getParam('Registry_id')!=Registry_id) || (form.RegistryHealDepResErrGrid.getCount()==0))
					{
						form.RegistryHealDepResErrGrid.loadData({
							callback: function() {
								form.RegistryHealDepResErrGrid.ownerCt.doLayout();
							},
							globalFilters: {
								Registry_id: Registry_id,
								RegistryType_id: RegistryType_id,
								start: 0,
								limit: 100
							},
							noFocusOnLoad: !nofocus
						});
					}
					break;
				case 'tab_evnplcrossed':
					if ((form.EvnPLCrossedGrid.getParam('Registry_id')!=Registry_id) || (form.EvnPLCrossedGrid.getCount()==0))
					{
						form.EvnPLCrossedGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datapldouble':
					if ((form.DoublePLGrid.getParam('Registry_id')!=Registry_id) || (form.DoublePLGrid.getCount()==0))
					{
						form.DoublePLGrid.loadData({globalFilters:{archiveRecord: archiveRecord, Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
				case 'tab_datanopay':
					form.NoPayGrid.removeAll(true);
					break;
				case 'tab_datapersonerr':
					form.PersonErrorGrid.removeAll(true);
					break;
				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.hide();
					form.BDZErrorGrid.hide();
					form.TFOMSErrorGrid.removeAll(true);
					form.BDZErrorGrid.removeAll(true);
					break;
				case 'tab_datamzerr':
					form.RegistryHealDepResErrGrid.removeAll(true);
					break;
				case 'tab_evnplcrossed':
					form.EvnPLCrossedGrid.removeAll(true);
					break;
				case 'tab_datapldouble':
					form.DoublePLGrid.removeAll(true);
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
		var bdzImage = unchekedImg;
		var mekImage = unchekedImg;

		if (record) {
			if (record.get('FlkErrors_IsData') && record.get('FlkErrors_IsData') == 1) {
				flkImage = warningImg;
			} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([2, 3, 4, 6, 7, 8])) {
				flkImage = chekedImg;
			}

			if (record.get('BdzErrors_IsData') && record.get('BdzErrors_IsData') == 1) {
				bdzImage = warningImg;
			} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([3, 6, 7, 8])) {
				bdzImage = chekedImg;
			}

			if (record.get('MekErrors_IsData') && record.get('MekErrors_IsData') == 1) {
				mekImage = warningImg;
			} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([3, 8])) {
				mekImage = chekedImg;
			}
		}

		var TFOMSTitle = 'Пройденные стадии проверки в ТФОМС: '+flkImage+'ФЛК '+bdzImage+'БДЗ '+mekImage+'МЭК ';

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
						form.UnionTFOMSErrorGrid.loadData({callback: function() {
							// если записей нет, нужно грид скрыть, не нужно!
							if (true || form.UnionTFOMSErrorGrid.getGrid().getStore().getCount() > 0) {
								form.UnionTFOMSErrorGrid.show();
								// если тфомс показывается то высота бдз = 200
								form.UnionBDZErrorGrid.setHeight(200);
							} else {
								form.UnionTFOMSErrorGrid.hide();
								// если тфомс скрывается то высота бдз = высоте родительского контейнера
								form.UnionBDZErrorGrid.setHeight(form.UnionBDZErrorGrid.ownerCt.getEl().getHeight()-30);
							}
							form.UnionTFOMSErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					if ((form.UnionBDZErrorGrid.getParam('Registry_id')!=Registry_id) || (form.UnionBDZErrorGrid.getCount()==0))
					{
						form.UnionBDZErrorGrid.loadData({callback: function() {
							// если записей нет, нужно грид скрыть, не нужно!
							if (true || form.UnionBDZErrorGrid.getGrid().getStore().getCount() > 0) {
								form.UnionBDZErrorGrid.show();
								// filter_form.findField('RegistryErrorBDZType_id').showContainer();
							} else {
								form.UnionBDZErrorGrid.hide();
								// filter_form.findField('RegistryErrorBDZType_id').hideContainer();
							}
							form.UnionBDZErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
	deleteRegistryErrorTFOMS: function(grid) {
		var record = grid.getGrid().getSelectionModel().getSelected();
		if (!record || !record.get('RegistryErrorTFOMS_id'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбрана ни одна запись.<br/>');
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId)
			{
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryErrorTFOMS',
						params: {
							Registry_id: record.get('Registry_id'),
							RegistryErrorTFOMS_id: record.get('RegistryErrorTFOMS_id')
						},
						callback: function(options, success, response)
						{
							if (success)
							{
								grid.loadData();
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Вы действительно хотите удалить выбранную ошибку?',
			title: 'Вопрос'
		});
	},
	/**
	 * Показать информацию о дублирующих ТАП
	 */
	showEvnPLDoublesInfo: function() {
		var
			form = this,
			record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('RegistryErrorType_Code')) || record.get('RegistryErrorType_Code') != '7965' ) {
			return false;
		}

		Ext.Ajax.request({
			url: '/?c=EvnVizit&m=getEvnVizitPLDoubles',
			callback: function (options, success, response) {
				if (success) {
					var
						errorText,
						EvnPL_NumCard,
						i,
						tapList = [],
						response_obj = Ext.util.JSON.decode(response.responseText);

					log(response_obj.doublesEvnPL);

					if ( typeof response_obj.doublesEvnPL == 'object' ) {
						EvnPL_NumCard = response_obj.doublesEvnPL[0]['EvnPL_NumCard'] || 'не определен';

						for ( i in response_obj.doublesEvnPL ) {
							if ( typeof response_obj.doublesEvnPL[i] == 'object' && !Ext.isEmpty(response_obj.doublesEvnPL[i]['EvnPLDouble_NumCard']) && !response_obj.doublesEvnPL[i]['EvnPLDouble_NumCard'].inlist(tapList) ) {
								tapList.push(response_obj.doublesEvnPL[i]['EvnPLDouble_NumCard']);
							}
						}
					}

					if ( tapList.length == 0 ) {
						tapList.push('не найдены');
					}

					errorText = '<div>Номер ТАП ' + EvnPL_NumCard + '</div><div>ТАП с дублирующими посещениями: ' + tapList.join(', ') + '</div>';

					sw.swMsg.alert('Сообщение', errorText);
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса к серверу.');
				}
			},
			params: {
				EvnVizitPL_id: record.get('Evn_id')
			}
		});

		return true;
	},
	deleteProcedureIsRunning: false,
	/**
	 * Удалить запись из реестра по всем ошибкам
	 */
	deleteRegistryDataAll: function(grid) {
		if ( this.deleteProcedureIsRunning == true ) {
			sw.swMsg.alert('Ошибка', 'Процедура удаления уже запущена.');
			return false;
		}

		var form = this;
		var record = grid.getGrid().getSelectionModel().getSelected();
		var reestr = null;
		var node = form.Tree.getSelectionModel().getSelectedNode();
		var RegistryType_id = null;

		form.deleteProcedureIsRunning = true;

		if (node.attributes.object == 'RegistryType' && node.attributes.object_value == 13) {
			reestr = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			RegistryType_id = node.attributes.object_value;
		} else {
			reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			RegistryType_id = reestr.get('RegistryType_id');
		}

		if (!record && !reestr) {
			form.deleteProcedureIsRunning = false;
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.');
			return false;
		}
		var Evn_id = record.get('Evn_id');
		var Registry_id = reestr.get('Registry_id');
		var RegistryData_deleted = 1;

		if (!Ext.isEmpty(record.get('RegistryData_deleted'))) {
			RegistryData_deleted = record.get('RegistryData_deleted');
		}

		if (RegistryData_deleted!=2) {
			var msg = '<b>Вы действительно хотите удалить все записи по ошибкам <br/>из реестра?</b><br/><br/>'+
				'<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данные записи пометятся как удаленные <br/>'+
				'и будут удалены из реестра при выгрузке (отправке) реестра.<br/>'+
				'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
		} else {
			var msg = '<b>Хотите восстановить все помеченные на удаление записи по ошибкам?</b>';
		}

		var params = {
			Registry_id: Registry_id,
			RegistryType_id: RegistryType_id,
			RegistryData_deleted: RegistryData_deleted,
			type: grid.object
		};

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId)
			{
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(Ext.get(form.id), { msg: 'Выполнение запроса...' });
					loadMask.show();

					Ext.Ajax.request({
						url: '/?c=Registry&m=deleteRegistryDataAll',
						params: params,
						callback: function(options, success, response)
						{
							form.deleteProcedureIsRunning = false;
							loadMask.hide();

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
	/**
	 * Удаляем запись из реестра
	 */
	deleteRegistryData: function(grid, deleteAll)
	{
		if ( this.deleteProcedureIsRunning == true ) {
			sw.swMsg.alert('Ошибка', 'Процедура удаления уже запущена.');
			return false;
		}

		var form = this;
		var record = grid.getGrid().getSelectionModel().getSelected();
		var reestr = null;
		var node = form.Tree.getSelectionModel().getSelectedNode();
		var RegistryType_id = null;

		form.deleteProcedureIsRunning = true;

		if (node.attributes.object == 'RegistryType' && node.attributes.object_value == 13) {
			reestr = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			RegistryType_id = node.attributes.object_value;
		} else {
			reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			RegistryType_id = reestr.get('RegistryType_id');
		}

		if (!record && !reestr) {
			form.deleteProcedureIsRunning = false;
			sw.swMsg.alert('Ошибка', 'Не выбрана ни одна запись в реестре.<br/>');
			return false;
		}

		var Evn_id = record.get('Evn_id');
		var Registry_id = reestr.get('Registry_id');
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
			scope : form,
			fn: function(buttonId)
			{
				if ( buttonId == 'yes' )
				{
					var loadMask = new Ext.LoadMask(Ext.get(form.id), { msg: 'Выполнение запроса...' });
					loadMask.show();

					Ext.Ajax.request(
					{
						url: '/?c=Registry&m=deleteRegistryData',
						params: params,
						callback: function(options, success, response)
						{
							form.deleteProcedureIsRunning = false;
							loadMask.hide();

							if (success)
							{
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
			scope : form,
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

		form.getLoadMask().show();

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
				form.getLoadMask().hide();
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
			msg: 'Вы уверены, что хотите отметить реестр оплаченным?<br/>Снятие отметки «Оплачен» будет невозможно. Настоятельно рекомендуем проверить факт проведения контролей в ТФОМС/СМО.',
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
	createAndSendDBF: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		sw.swMsg.show(
		{
			buttons: Ext.Msg.OK,
			icon: Ext.Msg.ERROR,
			msg: 'Внимание! Автоматическая отправка реестров в ПКФОМС временно не работает!',
			title: 'Отправка реестров'
		});
		return false;

		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		form.getLoadMask().show();
		Ext.Ajax.request(
		{
			url: '/?c=Registry&m=exportRegistryToDbf',
			params:
			{
				Registry_id: Registry_id,
				send: 1
			},
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				var r = '';
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);

					if (result.success)
					{
						if (result.Error_Msg)
							r = result.Error_Msg;
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: 'Реестр успешно выгружен и отправлен.',
							title: ''
						});
						return true;
					}
				}

				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: 'При формировании или отправке реестра произошла ошибка!\n\r'+r,
					title: ''
				});
				return false;
			}
		});
	},
	/** Формирование и отправка файла в ТФОМС
	 *
	 */
	createAndSendXML: function(addParams)
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

		var params = {
				Registry_id: Registry_id,
				KatNasel_id: record.get('KatNasel_id'),
				RegistryType_id: record.get('RegistryType_id'),
				send: 1
			};

		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}

		Ext.Ajax.request(
		{
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

						if (result.Error_Code && result.Error_Code == '10') { // Статус реестра "Проведен контроль ФЛК"
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' )
									{
										var newParams = addParams;
										newParams.OverrideControlFlkStatus = 1;
										form.createAndSendXML(newParams);
									}
								},
								msg: 'Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?',
								title: 'Подтверждение'
							});

							return false;
						}

						if (result.Error_Code && result.Error_Code == '11') { // Уже есть выгруженный XML
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' )
									{
										var newParams = addParams;
										newParams.OverrideExportOneMoreOrUseExist = 2;
										form.createAndSendXML(newParams);
									} else {
										var newParams = addParams;
										newParams.OverrideExportOneMoreOrUseExist = 1;
										form.createAndSendXML(newParams);
									}
								},
								msg: 'Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)',
								title: 'Подтверждение'
							});

							return false;
						}

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
						record.set('RegistryCheckStatus_Code', 0);
						record.set('RegistryCheckStatus_SysNick', 'ReadyTFOMS');
						record.set('RegistryCheckStatus_Name',"<a href='#' onClick='getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:"+record.get('Registry_id')+"});'>Готов к отправке в ТФОМС</a>");
						record.commit();
						form.AccountGrid.onRowSelect(form.AccountGrid.getGrid().getSelectionModel(), 0, record);
						return true;
					}
				}
			}
		});
	},
	/**
	 * Пересчет реестра
	 */
	refreshRegistry: function() {
		var
			form = this,
			record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !record || Ext.isEmpty(record.get('Registry_id')) ) {
			sw.swMsg.alert('Ошибка', 'Не выбран ни один счет/реестр.');
			return false;
		}
		else if ( record.get('Registry_IsRecalc') == 2 ) {
			sw.swMsg.alert('Ошибка', 'Реестр уже пересчитывается.');
			return false;
		}

		form.getLoadMask().show();

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
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
								form.DataGrid.removeAll();
								form.AccountGrid.loadData();
							}
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
	exportRegistryToXml: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		log(1,record);
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		if(record.get('Registry_IsProgress')==1){
			Ext.Msg.alert('Ошибка', 'Реестр переформировывается, отправка и экспорт не возможны.<br/>Дождитесь переформирования и повторите действие.<br/>');
			return false;
		}
		if (record.get('Registry_Count') == 0 && !isSuperAdmin())
		{
			Ext.Msg.alert('Ошибка', 'Экспорт реестра невозможен, нет случаев для экспорта.<br/>');
			return false;
		}

		if (record.get('Registry_IsNeedReform') == 2) {
			Ext.Msg.alert('Ошибка', 'Реестр нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр и повторите действие.<br/>');
			return false;
		}

		var fd = 'swRegistryXmlWindow';
		var params = {onHide: function() { this.AccountGrid.loadData(); }.createDelegate(this),RegistryCheckStatus_id:record.get('RegistryCheckStatus_id'), Registry_id: record.get('Registry_id'), KatNasel_id: record.get('KatNasel_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=Registry&m=exportRegistryToXml'};
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
					Registry_IsNew: current_window.Registry_IsNew,
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
			case 12:
				// Удаленные
				menu = [
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
				menu = [
					//form.menuActions.exportToDbf,
					form.menuActions.exportToXml,
					form.menuActions.registrySetPay,
					'-',
					form.menuActions.reformRegistry,
					'-',
					form.menuActions.refreshRegistry
				];
				break;
			case 2: // К оплате
				menu = [
					form.menuActions.refreshRegistry,
					//form.menuActions.exportToDbf,
					form.menuActions.exportToXml,
					form.menuActions.sendRegistryToTFOMS,
					'-',
					form.menuActions.registrySetWork,
					form.menuActions.registrySign,
					form.menuActions.sendRegistryToMZ
				];
				break;

			case 5: // Проверенные ПКФОМС
				menu = [
					form.menuActions.registrySetWork,
					//form.menuActions.exportToDbf,
					form.menuActions.exportToXml
				];
				break;

			case 6: // Проверенные МЗ
				menu = [
					form.menuActions.registrySetWork,
					form.menuActions.registrySetPaid,
					form.menuActions.exportToXml
				];
				break;

			case 4: // Оплаченные
				menu =
				[
					//form.menuActions.exportToDbf,
					form.menuActions.exportToXml,
					form.menuActions.registrySetPay
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
	changePerson: function() {
		if ( !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin() ) {
			return false;
		}

		var form = this, grid, RegistryType_id;

		if ( form.findById('regvRightPanel').getLayout().activeItem.id == form.id + 'UnionRegistryListPanel' ) {
			RegistryType_id = form.UnionDataGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
			grid = form.UnionDataGrid.getGrid();
		}
		else {
			RegistryType_id = form.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
			grid = form.DataGrid.getGrid();
		}

		if ( Ext.isEmpty(RegistryType_id) ) {
			sw.swMsg.alert('Ошибка', 'Не определен тип реестра.');
			return false;
		}
		else if ( !RegistryType_id.toString().inlist([ '1', '2', '6', '7', '8', '9', '10', '11', '12', '14', '15', '16' ]) ) {
			sw.swMsg.alert('Ошибка', 'Функция доступна только для реестров поликлиники, стационара, дд и скорой помощи.');
			return false;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		else if ( RegistryType_id.toString().inlist([ '1', '2', '7', '8', '9', '10', '11', '12', '14', '15', '16' ]) && !grid.getSelectionModel().getSelected().get('Evn_rid') ) {
			return false;
		}
		else if ( RegistryType_id.toString().inlist([ '6' ]) && !grid.getSelectionModel().getSelected().get('Evn_id') ) {
			return false;
		}

		var params = new Object();
		var record = grid.getSelectionModel().getSelected();

		switch ( RegistryType_id.toString() ) {
			case '1':
			case '2':
			case '7':
			case '8':
			case '9':
			case '10':
			case '11':
			case '12':
			case '14':
			case '15':
			case '16':
				params.Evn_id = record.get('Evn_rid');
			break;

			case '6':
				params.CmpCallCard_id = record.get('Evn_id');
			break;
		}

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				params.Person_id = person_data.Person_id;
				params.PersonEvn_id = person_data.PersonEvn_id;
				params.Server_id = person_data.Server_id;

				form.setAnotherPersonForDocument(params);
			},
			searchMode: 'all'
		});
	},
	setAnotherPersonForDocument: function(params) {
		var form = this;
		var grid = this.DataGrid.getGrid();

		var loadMask = new Ext.LoadMask(getWnd('swPersonSearchWindow').getEl(), { msg: "Переоформление документа на другого человека..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg ? response_obj.Error_Msg : 'Ошибка при переоформлении документа на другого человека');
					}
					else if ( response_obj.Alert_Msg ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									switch ( response_obj.Alert_Code ) {
										case 1:
											params.allowEvnStickTransfer = 2;
										case 2:
											params.ignoreAgeFioCheck = 2;
										break;
									}

									form.setAnotherPersonForDocument(params);
								}
							},
							msg: response_obj.Alert_Msg,
							title: 'Вопрос'
						});
					}
					else {
						getWnd('swPersonSearchWindow').hide();
                        var info_msg = 'Документ успешно переоформлен на другого человека';
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
                        sw.swMsg.alert('Сообщение', info_msg, function() {
							if ( response_obj.Registry_IsNeedReform == 2 ) {
								form.AccountGrid.getGrid().getStore().reload();
								grid.getStore().reload();
							}
						});
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'При переоформлении документа на другого человека произошли ошибки');
				}
			},
			params: params,
			url: C_CHANGEPERSONFORDOC
		});
	},
	setPerson: function()
	{
		// https://redmine.swan.perm.ru/issues/12624
		// Для Перми этот функционал закрыт
		return false;

		var form = this;
		var record = form.DataGrid.getGrid().getSelectionModel().getSelected();

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
		// https://redmine.swan.perm.ru/issues/12624
		// Для Перми этот функционал закрыт
		return false;

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
									grid.getGrid().getStore().remove(record);
								}

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									var registryRecord = this.AccountGrid.getGrid().getSelectionModel().getSelected();
									if ( typeof registryRecord == 'object' ) {
										registryRecord.set('RegistryPerson_IsData', 0);
										registryRecord.commit();
										this.DataTab.getItem('tab_datapersonerr').setIconClass(registryRecord.get('RegistryPerson_IsData')==1?'usluga-notok16':'good');
									}
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
		var record = grid.getGrid().getSelectionModel().getSelected();

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Person_id')) ) {
			return false;
		}

		// Формируем список двойников, основная запись Person2_id

		sw.swMsg.show(
		{
			buttons: Ext.Msg.YESNO,
			scope : this,
			fn: function(buttonId)
			{
				if ( buttonId == 'yes' )
				{
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
								/*if (resp_obj.success == true) {
									// Успешно, помечаем запись чтобы они видели что поправили
									this.PersonErrorGrid.setNoEdit(record);
								}*/
								if (resp_obj.success == true) {
									grid.getGrid().getStore().remove(record);
								}

								if ( grid.getGrid().getStore().getCount() == 0 ) {
									var registryRecord = this.AccountGrid.getGrid().getSelectionModel().getSelected();
									if ( typeof registryRecord == 'object' ) {
										registryRecord.set('RegistryPerson_IsData', 0);
										registryRecord.commit();
										this.DataTab.getItem('tab_datapersonerr').setIconClass(registryRecord.get('RegistryPerson_IsData')==1?'usluga-notok16':'good');
									}
								}
							}
							this.getLoadMask().hide();
						}.createDelegate(this),
						params: {
								'Records': Ext.util.JSON.encode(records),
								'fromRegistry': 1,
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
			msg: 'Хотите объединить выбранных людей?',
			title: 'Вопрос'
		});
	},
	/** Открытие информационного окна с технической информацией
	 *
	 */
	openInfoForm: function (object) {
		var form = this;
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
			scope : form,
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
		if ( typeof object != 'object' ) {
			return false;
		}
		var form = this;
		// Взависимости от типа выбираем форму которую будем открывать
		// Типы лежат в RegistryType
		var record = object.getGrid().getSelectionModel().getSelected();
		if ( typeof record != 'object' || Ext.isEmpty(record.get('Evn_id')) )
		{
			sw.swMsg.alert('Ошибка', 'Ошибка выбора записи!');
			return false;
		}

		var RegistryType_id, type = record.get('RegistryType_id');
		var archiveRecord = 0;

		if ( form.findById('regvRightPanel').getLayout().activeItem.id == form.id + 'UnionRegistryListPanel' ) {
			RegistryType_id = record.get('RegistryType_id');
		}
		else {
			RegistryType_id = form.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
			if (getGlobalOptions().archive_database_enable) {
				archiveRecord = form.AccountGrid.getGrid().getSelectionModel().getSelected().get('archiveRecord');
			}
		}

		if ( !type && form.findById('regvRightPanel').getLayout().activeItem.id == form.id + 'RegistryListPanel' ) {
			type = RegistryType_id;
		}

		// Если это с грида "Ошибки данных" или Данные, + дочерние гриды для объединенного реестра
		if ( object.id.inlist([ form.id+'Error', form.id+'Data', form.id+'UnionData', form.id+'UnionTFOMSError', form.id+'UnionBDZError' ]) )
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
					break;
				*/
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
		if ( type == 108 && Ext.isEmpty(record.get('Person_id')) ) {
			return false;
		}

		var id = record.get('Evn_rid') ||  record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное
		var isNoEdit = record.get('isNoEdit'); // Если не редактируется
		var Person_id = record.get('Person_id');
		var Server_id = record.get('Server_id');
		var PersonEvn_id = null;
		var usePersonEvn = null;
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

		var
			showMessageBeforeOpen = false,
			messageText;

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
			case 16:
				var config = form.getParamsForEvnClass(record);

				open_form = config.open_form;
				key = config.key;

				// для CmpCallCard нет EvnClass, определяем открываемую форму по типу реестра.
				if (RegistryType_id == '6') {
					open_form = 'swCmpCallCardEditWindow';
					key = 'CmpCallCard_id';
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
				if ( !Ext.isEmpty(record.get('CmpCloseCard_id')) ) {
					id = record.get('CmpCloseCard_id');
					open_form = 'swCmpCallCardNewCloseCardWindow';
					key = 'CmpCloseCard_id';
				}
				else {
					open_form = 'swCmpCallCardEditWindow';
					key = 'CmpCallCard_id';
					id = record.get('CmpCallCard_id') || record.get('Evn_id');

					if ( !Ext.isEmpty(record.get('CmpCallCardInputType_id')) ) {
						params.CmpCallCard_isShortEditVersion = record.get('CmpCallCardInputType_id');
					}
				}
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

				if ( record.get('IsGroupEvn') == 2 ) {
					params.action = 'view';
					showMessageBeforeOpen = true;
					messageText = 'Сгруппированный случай лечения. Данные доступны только для просмотра. Редактирование возможно из форм поточного ввода или ЭМК.';
				}
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
		if (open_form.inlist([ 'swCmpCallCardEditWindow', 'swCmpCallCardNewCloseCardWindow', 'swEvnUslugaTelemedEditWindow' ])) { // карты вызова, телемедицинские услуги
			params.formParams = Ext.apply(params);
		}
		if (getGlobalOptions().archive_database_enable) {
			if (record.get('archiveRecord') && record.get('archiveRecord') == 1) {
				archiveRecord = 1;
			}

			params.archiveRecord = archiveRecord;
		}

		if ( showMessageBeforeOpen == true )  {
			sw.swMsg.alert('Сообщение', messageText, function() {
				getWnd(open_form).show(params);
			});
		}
		else {
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
	doubleControl: function() {
		var win = this;
		var record = win.DoublePLGrid.getGrid().getSelectionModel().getSelected();
		if (record && record.get('Evn_id')) {
			// получаем EvnVizitPLDoublesData
			win.getLoadMask('Получение дублей').show();
			Ext.Ajax.request({
				url: '/?c=EvnVizit&m=getEvnVizitPLDoubles',
				callback: function (options, success, response) {
					win.getLoadMask().hide();
					if (success) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						getWnd('swEvnVizitPLDoublesWindow').show({
							EvnVizitPLDoublesData: response_obj.doublesEvnPL,
							callback: function (data) {
								// сохраняем data.EvnVizitPLDoublesData
								win.getLoadMask('Сохранение').show();
								Ext.Ajax.request({
									url: '/?c=EvnVizit&m=saveEvnVizitPLDoubles',
									callback: function (options, success, response) {
										win.getLoadMask().hide();
									},
									params: {
										EvnVizitPLDoublesData: data.EvnVizitPLDoublesData
									}
								});
							}.createDelegate(this)
						});
					}
				},
				params: {
					EvnVizitPL_id: record.get('Evn_id')
				}
			});
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
	addMekSmo: function()
	{
		var form = this;
		var record = this.UnionDataGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		getWnd('swRegistryErrorTFOMSEditWindow').show({
			onHide: function()
			{
				form.UnionTFOMSErrorGrid.getGrid().getStore().baseParams.Registry_id = null; // зануляем, чтобы грид обновился при переходе на данную вкладку.
			},
			callback: function()
			{

			},
			action: 'add',
			Registry_id: record.get('Registry_id'),
			Evn_id: record.get('Evn_id')
		});
	},
	importMekSmo: function()
	{
		var form = this;
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var fd = 'swRegistryImportXMLWindow';
		var params =
		{
			onHide: function()
			{
				form.UnionTFOMSErrorGrid.getGrid().getStore().baseParams.Registry_id = null; // зануляем, чтобы грид обновился при переходе на данную вкладку.
				form.onUnionRegistrySelect(record.get('Registry_id'), false, record);
			},
			callback: function()
			{

			},
			Registry_id: record.get('Registry_id')
		};
		getWnd(fd).show(params);
	},
	correctErrors: function()
	{
		var win = this;
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		sw.swMsg.prompt('Введите смещение',
			'Введите смещение записей в реестре:',
			function(btnId, newValue){
				if (btnId != 'ok') {
					return false;
				}

				// выполняем запрос
				win.getLoadMask('Корректировка ошибок...').show();
				Ext.Ajax.request({
					url: '/?c=Registry&m=correctErrors',
					params: {
						Registry_id: record.get('Registry_id'),
						offset: newValue
					},
					callback: function (options, success, response) {
						win.getLoadMask().hide();
						if (success) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.success) {
								win.UnionTFOMSErrorGrid.getGrid().getStore().baseParams.Registry_id = null; // зануляем, чтобы грид обновился при переходе на данную вкладку.
								win.onUnionRegistrySelect(record.get('Registry_id'), false, record);

								sw.swMsg.alert('Внимание', 'Корректировка ошибок успешно завершена');
							}
						}
					}
				});

				return true;
			},
			this,
			false,
			1
		);
	},
	exportOnko: function() {
		getWnd('swExportOnkoWindow').show();
	},
	exportUnionRegistryToXml: function(mode)
	{
		var form = this;
		/**
		 * mode
		 * 1 - экспорт
		 * 2 - отправка в ТФОМС
		 */
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		if (mode == 1) {
			var fd = 'swUnionRegistryXmlWindow';
			var params = {
				onHide: function () {
					this.UnionRegistryGrid.loadData();
				}.createDelegate(this),
				Registry_id: record.get('Registry_id'),
				url: '/?c=Registry&m=exportUnionRegistryToXml'
			};
			getWnd(fd).show(params);
		} else if (mode == 2) {
			// подписать
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				scope : form,
				fn: function(buttonId)
				{
					if (buttonId=='yes')
						form.createAndSignUnionXML();
				},
				icon: Ext.Msg.QUESTION,
				msg: 'Сформировать файл реестра и подписать?',
				title: 'Вопрос'
			});
		}  else if (mode == 3) {
			// отправить в ТФОМС
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				scope : form,
				fn: function(buttonId)
				{
					if (buttonId=='yes') {
						form.getLoadMask('Отправка в ТФОМС').show();
						Ext.Ajax.request({
							url: '/?c=Registry&m=sendUnionRegistryToTFOMS',
							params: {
								Registry_id: record.get('Registry_id')
							},
							callback: function (options, success, response) {
								form.getLoadMask().hide();
								if (success) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										record.set('RegistryCheckStatus_Code', 0);
										record.set('RegistryCheckStatus_Name', "<a href='#' onClick='getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:" + record.get('Registry_id') + "});'>Готов к отправке в ТФОМС</a>");
										record.commit();
										form.UnionRegistryGrid.onRowSelect(form.UnionRegistryGrid.getGrid().getSelectionModel(), 0, record);
									}
								}
							}
						});
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: 'Отправить реестр в ТФОМС?',
				title: 'Вопрос'
			});
		}
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
									record.set('RegistryCheckStatus_Code', 13);
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
										record.set('RegistryCheckStatus_Code', 12);
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
	createAndSignUnionXML: function(addParams)
	{
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		form.getLoadMask('Создание XML-файла').show();

		var params = {
			Registry_id: Registry_id,
			send: 1
		};

		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}

		Ext.Ajax.request({
			url: '/?c=Registry&m=exportUnionRegistryToXML',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				var r = '';
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);

					if (result.success === false)
					{
						if (result.Error_Code && result.Error_Code == '11') { // Уже есть выгруженный XML
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									if ( buttonId == 'yes' )
									{
										var newParams = addParams;
										newParams.OverrideExportOneMoreOrUseExist = 2;
										form.createAndSignUnionXML(newParams);
									} else {
										var newParams = addParams;
										newParams.OverrideExportOneMoreOrUseExist = 1;
										form.createAndSignUnionXML(newParams);
									}
								},
								msg: 'Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если использовать существующий нажмите (Нет)',
								title: 'Подтверждение'
							});

							return false;
						}

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
							if (isSuperAdmin()) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' )
										{
											params.documentSigned = 'notvalid';
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
															record.set('RegistryCheckStatus_Code', 12);
															record.set('RegistryCheckStatus_Name', "<a href='#' onClick='getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:" + record.get('Registry_id') + "});'>Подписан (ЭЦП)</a>");
															record.commit();
															form.UnionRegistryGrid.onRowSelect(form.UnionRegistryGrid.getGrid().getSelectionModel(), 0, record);
														}
													}
												}
											});
										} else {
											params.filebase64 = result.filebase64;
											form.doSignRegistry(params, record);
										}
									},
									msg: 'Подписать реестр невалидной подписью?',
									title: 'Подтверждение'
								});
							} else {
								params.filebase64 = result.filebase64;
								form.doSignRegistry(params, record);
							}
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
	show: function() {
		sw.Promed.swRegistryViewWindow.superclass.show.apply(this, arguments);

		this.getLoadMask().show();

		this.deleteProcedureIsRunning = false;

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
		//if (isLpuAdmin() || isSuperAdmin()) {
			this.getReplicationInfo();
		//}

		this.Registry_IsNew = 1;
		if (arguments && arguments[0] && arguments[0].Registry_IsNew) {
			this.Registry_IsNew = arguments[0].Registry_IsNew;
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
		var form = this,
			grid = this.EvnPLCrossedGrid.ViewGridPanel,
			rec = grid.getSelectionModel().getSelected(),
			msg = 'Вы действительно хотите подать ' + (mode == 'current' ? 'выбранную запись' : 'все записи') + ' на оплату?';
		if( !rec && mode == 'current' ) return false;

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
							Registry_id: this.EvnPLCrossedGrid.getParam('Registry_id')
						},
						callback: function(options, success, response) {
							this.getLoadMask().hide();
							if( success ) {
								if (mode == 'all') {
									grid.getStore().removeAll();
								} else if (rec) {
									grid.getStore().remove(rec);
								}

								form.DataGrid.loadData();

								if ( grid.getStore().getCount() == 0 || Ext.isEmpty(grid.getStore().getAt(0).get('Evn_id')) ) {
									form.DataTab.getItem('tab_evnplcrossed').setIconClass('good');

									if ( typeof form.AccountGrid.getGrid().getSelectionModel().getSelected() == 'object' ) {
										form.AccountGrid.getGrid().getSelectionModel().getSelected().set('issetDouble', 0);
										form.AccountGrid.getGrid().getSelectionModel().getSelected().commit();
									}
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
			Registry_IsRepeated: (record.get('Registry_IsRepeated') == 2 ? 'Да' : 'Нет'),
			KatNasel_Name: record.get('KatNasel_Name'),
			LpuBuilding_Name: record.get('LpuBuilding_Name'),
			DispClass_Descr: '',
			RegistryCheckStatus_Name: record.get('RegistryCheckStatus_Name'),
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
			RegistryNoPaid_Count: '<div style="padding:2px;font-size: 12px;color:maroon;">Количество записей без оплаты: '+record.get('RegistryNoPaid_Count')+'</div>',
			createError: record.get('createError')
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
			exportToXml: new Ext.Action({
				text: 'Экспорт в XML',
				tooltip: 'Экспорт в XML',
				handler: function() {
					form.exportRegistryToXml('simple');
				}
			}),
			/*exportToDbf: new Ext.Action({
				text: 'Экспорт в DBF',
				tooltip: 'Экспорт в DBF',
				hidden: false,
				handler: function() {
					form.exportRegistry();
				}
			}),*/
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
			sendRegistryToTFOMS: new Ext.Action({
				text: 'Отправить в ТФОМС',
				tooltip: 'Экспорт в XML и отправка в TФОМС',
				handler: function()
				{
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

					if (!record)
					{
						Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
						return false;
					}

					if (record.get('Registry_Count') == 0 && !isSuperAdmin())
					{
						Ext.Msg.alert('Ошибка', 'Отправка реестра в ТФОМС невозможна, нет случаев для экспорта.<br/>');
						return false;
					}

					if (record.get('Registry_IsNeedReform') == 2) {
						Ext.Msg.alert('Ошибка', 'Реестр нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр и повторите действие.<br/>');
						return false;
					}

					sw.swMsg.show(
						{
							buttons: Ext.Msg.YESNO,
							scope : form,
							fn: function(buttonId)
							{
								if (buttonId=='yes')
									form.createAndSendXML();
							},
							icon: Ext.Msg.QUESTION,
							msg: 'Сформировать файл и отправить в ТФОМС?',
							title: 'Вопрос'
						});
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
			useArchive: 1,
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
				{name: 'Registry_IsRepeated', type: 'int', hidden: true},
				{name: 'Registry_IsRecalc', type: 'int', hidden: true},
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsNew', type: 'int',  hidden: true, isparams: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'RegistryUnion_Num', header: '№ объед. реестра', width: 80},
				{name: 'Registry_Num', header: 'Номер счета', width: 80},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'Registry_accDate', type:'date', header: 'Дата счета', width: 80},
				{name: 'ReformTime',hidden: true},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 80},
				{name: 'Registry_RecordPaidCount', type: 'int', hidden: true},
				{name: 'Registry_KdCount', type: 'int', hidden: true},
				{name: 'Registry_KdPaidCount', type: 'int', hidden: true},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_NoErrSum', type:'money', header: 'Сумма без ошибок', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
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
				{name: 'RegistryCheckStatus_Code', type: 'int', hidden: true},
				{name: 'BdzErrors_IsData', hidden: true},
				{name: 'FlkErrors_IsData', hidden: true},
				{name: 'MekErrors_IsData', hidden: true},
				{name: 'RegistryCheckStatus_id', hidden: true},
				{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200},
				{name: 'issetDouble', hidden: true},
				{name: 'issetDoublePl', hidden: true},
				{name: 'PayType_SysNick', hidden: true, type: 'string'},
				{name: 'RegistryCheckStatus_SysNick', hidden: true, type: 'string'},
				{name: 'RegistryErrorTFOMSType_id', hidden:true},
				{name: 'createError', hidden: true},
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
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
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
				if (record && record.get('Registry_id'))
				{
					var Registry_id = record.get('Registry_id');
					var RegistryType_id = record.get('RegistryType_id');
					var RegistryStatus_id = record.get('RegistryStatus_id');
					var xDate = new Date(2016, 0, 1);

					// Меняем колонки и отображение
					if (record.get('PayType_SysNick').inlist(['bud', 'fbud'])) {
						form.DataGrid.setColumnHidden('Person_IsBDZ', true);
						form.DataGrid.setColumnHidden('EvnVizitPL_setDate', true);
						form.DataGrid.setColumnHidden('Evn_disDate', true);
						form.DataGrid.setColumnHidden('RegistryData_Uet', true);
						form.DataGrid.setColumnHidden('RegistryData_KdPlan', true);
						form.DataGrid.setColumnHidden('RegistryData_KdPay', true);
					} else {
						form.DataGrid.setColumnHidden('Person_IsBDZ', false);
						form.DataGrid.setColumnHidden('EvnVizitPL_setDate', false);
						form.DataGrid.setColumnHidden('Evn_disDate', false);
					}

					if (RegistryType_id==1 || RegistryType_id==14) {
						// Для стаца одни названия
						form.DataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
						form.DataGrid.setColumnHeader('RegistryData_KdPay', 'К/д к оплате');
						form.DataGrid.setColumnHeader('RegistryData_KdPlan', 'К/д норматив');
						form.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');
						if (!record.get('PayType_SysNick').inlist(['bud', 'fbud'])) {
							form.DataGrid.setColumnHidden('RegistryData_Uet', false);
							form.DataGrid.setColumnHidden('EvnPS_disDate', false);
							form.DataGrid.setColumnHidden('RegistryData_KdPay', false);
							form.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
						}

						// без оплаты
						//form.DataGrid.setColumnHeader('Evn_setDate', 'Поступление');
						//form.NoPayGrid.setColumnHidden('Evn_disDate', false);
						form.NoPayGrid.setColumnHeader('RegistryNoPay_KdPay', 'К/д к оплате');
						form.NoPayGrid.setColumnHeader('RegistryNoPay_KdFact', 'К/д факт');
						form.NoPayGrid.setColumnHeader('RegistryNoPay_KdPlan', 'К/д норматив');
						form.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', false);
						form.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
						form.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);
					} else {
						// Для остальных - другие
						form.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ факт');
						form.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');
						form.DataGrid.setColumnHidden('EvnPS_disDate', true);

						if ((RegistryType_id==2 && record.get('Registry_begDate') < xDate) || RegistryType_id==15 || RegistryType_id==16) {
							form.DataGrid.setColumnHeader('RegistryData_KdPay', 'УЕТ к оплате');
							form.DataGrid.setColumnHeader('RegistryData_KdPlan', 'УЕТ норматив');
							if (!record.get('PayType_SysNick').inlist(['bud', 'fbud'])) {
								form.DataGrid.setColumnHidden('RegistryData_Uet', false);
								form.DataGrid.setColumnHidden('RegistryData_KdPay', false);
								form.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
							}

							// без оплаты
							form.NoPayGrid.setColumnHeader('RegistryNoPay_KdPay', 'УЕТ к оплате');
							form.NoPayGrid.setColumnHeader('RegistryNoPay_KdFact', 'УЕТ факт');
							form.NoPayGrid.setColumnHeader('RegistryNoPay_KdPlan', 'УЕТ норматив');
							form.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', false);
							form.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
							form.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);
						} else {
							form.DataGrid.setColumnHidden('RegistryData_Uet', true);
							form.DataGrid.setColumnHidden('RegistryData_KdPay', true);
							form.DataGrid.setColumnHidden('RegistryData_KdPlan', true);
							// без оплаты
							form.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', true);
							form.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', true);
							form.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', true);
						}
					}

					// Убрать кнопку Печать счета иногородним в полке и стаце (refs #1595) ЗАКОММЕНТИЛ В РАМКАХ ЗАДАЧИ  26006
					/*if (RegistryType_id.inlist([1,2]) && record.get('KatNasel_id') == 2) {
						this.setActionDisabled('action_print', true);
					} else {
						this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12]))));
					}*/
					this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12,14,15,16]))));

					this.setActionDisabled('action_edit',
						( !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())
						|| record.get('RegistryStatus_id') != 3 // #61531
						|| record.get('Registry_IsProgress') == 1
						|| (record.get('RegistryCheckStatus_Code').inlist([8]) && !isAdmin)
					);

					this.setActionDisabled('action_view',
						record.get('Registry_IsProgress') == 1
						|| record.get('RegistryStatus_id') == 12 // если уже удалён
					);

					this.setActionDisabled('action_delete',
						( !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())
						|| record.get('RegistryStatus_id') != 3 // #61531
						|| record.get('Registry_IsProgress') == 1
						|| record.get('RegistryCheckStatus_Code').inlist([0,1,3,8])
					);

					if ( record.get('RegistryStatus_id') != 11 && record.get('RegistryStatus_id') != 12 ) { // не в очереди и не удален
						//form.menuActions.exportToDbf.setDisabled(record.get('Registry_IsProgress') == 1);
						form.menuActions.registrySetPay.setDisabled(record.get('Registry_IsProgress') == 1);
					}

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
								//form.menuActions.exportToDbf.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registryErrorExport.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								form.menuActions.reformRegistry.setDisabled((record.get('RegistryCheckStatus_Code').inlist([0,1]) && !isAdmin)); // "Переформировать весь реестр"
								break;
							case 2: // к оплате
								//form.menuActions.exportToDbf.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registrySign.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.sendRegistryToMZ.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.sendRegistryToTFOMS.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.refreshRegistry.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.importRegistryFromTFOMS.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registryErrorExport.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								//form.menuActions.sendRegistryToTFOMS.setDisabled((record.get('RegistryCheckStatus_Code').inlist([0,1,4]) && !isAdmin) || record.get('PayType_SysNick') != 'oms');  // "Отправить в ТФОМС"
								form.menuActions.refreshRegistry.setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin));  // "Пересчитать реестр"
								form.menuActions.registrySign.setDisabled(!Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')));
								form.menuActions.sendRegistryToMZ.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'SignECP');
								form.menuActions.registrySetWork.setDisabled(
									(record.get('RegistryCheckStatus_Code').inlist([1,4]) && !isAdmin)
									|| (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && !Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) && record.get('RegistryCheckStatus_SysNick') != 'SignECP')
								);
								break;
							case 4: // оплаченные
								//form.menuActions.exportToDbf.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.registrySetPay.setHidden(!isSuperAdmin() || !Ext.isEmpty(record.get('RegistryUnion_Num')) || (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])));
								form.menuActions.registryErrorExport.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));

								form.menuActions.setRegistryActive.setDisabled(record.get('Registry_IsActive') != 2);
								break;
							case 5: // проверенные ТФОМС
								form.menuActions.registrySetWork.setHidden(false);
								//form.menuActions.exportToDbf.setHidden(false);
								form.menuActions.exportToXml.setHidden(false);

								form.menuActions.registrySetWork.setDisabled(!Ext.isEmpty(record.get('RegistryUnion_Num')));
								//form.menuActions.exportToDbf.setDisabled(false);
								form.menuActions.exportToXml.setDisabled(false);
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

					if ( record.get('RegistryErrorTFOMS_IsData') > 0 || record.get('BdzErrors_IsData') > 0 ) {
						form.menuActions.registryErrorExport.enable();
					} else {
						form.menuActions.registryErrorExport.disable();
					}

					form.overwriteRegistryTpl(record);
				}
				else
				{
					this.setActionDisabled('action_print',true);

					form.RegistryPanel.hide();
				}

				form.onRegistrySelect(Registry_id, RegistryType_id,  false, record);

				// информируем о данных на вкладках
				if ( record.get('Registry_IsNeedReform') == 2 ) {
					form.DataTab.getItem('tab_registry').setIconClass('delete16');
				}
				else if ( record.get('createError') == 2 ) {
					form.DataTab.getItem('tab_registry').setIconClass('usluga-notok16');
				}
				else {
					form.DataTab.getItem('tab_registry').setIconClass('info16');
				}
				form.DataTab.getItem('tab_commonerr').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_dataerr').setIconClass((record.get('RegistryError_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datapersonerr').setIconClass((record.get('RegistryPerson_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopolis').setIconClass((record.get('RegistryNoPolis_IsData')==1 && record.get('PayType_SysNick') != 'mbudtrans')?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datanopay').setIconClass((record.get('RegistryNoPay_IsData')==1)?'usluga-notok16':'good');
				if ((record.get('RegistryErrorTFOMS_IsData')==1 || record.get('BdzErrors_IsData')==1) && (record.get('RegistryCheckStatus_Code').inlist([3,8]))) {
					form.DataTab.getItem('tab_datatfomserr').setIconClass('usluga-notok16');
				} else if (record.get('RegistryErrorTFOMS_IsData')==1 || record.get('BdzErrors_IsData')==1) {
					form.DataTab.getItem('tab_datatfomserr').setIconClass('delete16');
				} else {
					form.DataTab.getItem('tab_datatfomserr').setIconClass('good');
				}
				form.DataTab.getItem('tab_datamzerr').setIconClass((record.get('RegistryErrorMZ_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_evnplcrossed').setIconClass((record.get('issetDouble')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datapldouble').setIconClass((record.get('issetDoublePl')==1)?'usluga-notok16':'good');

				form.DataTab.syncSize();
			}
		});

		this.AccountGrid.ViewGridPanel.view.getRowClass = function (row, index) {
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

			/*if ( row.get('createError') == 2 ) {
				cls = cls+'x-grid-redborderrow ';
			}*/

			if ( cls.length == 0 ) {
				cls = 'x-grid-panel';
			}

			return cls;
		};

		var RegTplMark =
		[
			'<div style="padding:2px;font-size: 12px;font-weight:bold;">Реестр № {Registry_Num}<tpl if="Registry_IsNeedReform == 2"> <span style="color: red;">(НУЖДАЕТСЯ В ПЕРЕФОРМИРОВАНИИ!)</span></tpl></div>'+
			'<div style="padding:2px;font-size: 12px;">Вид оплаты: {PayType_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Категория населения: {KatNasel_Name}</div>'+
			'<div style="padding:2px;font-size: 12px;">Подразделение: {LpuBuilding_Name}</div>'+
			'{DispClass_Descr}'+
			'<div style="padding:2px;font-size: 12px;">Статус: {RegistryCheckStatus_Name}</div>'+
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
			'{RegistryNoPaid_Count}' +
			'<div style="padding:2px;font-size: 12px;">Повторная подача: {Registry_IsRepeated}</div>'+
			'<tpl if="createError == 2"><div style="padding:2px;font-size: 12px; color: red;">Внимание! При формировании реестра возникла ошибка, для устранения проблемы обратитесь в службу технической поддержки по телефону: (342) 261-61-61</div></tpl>'
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
						MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						filterRecords: filtersForm.findField('filterRecords').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
			}
		};

		this.UnionDataGridSearch = function()
		{
			var form = this;
			var filtersForm = form.UnionRegistryDataFiltersPanel.getForm();

			var registry = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			if (Registry_id > 0)
			{
				form.UnionDataGrid.loadData(
				{
					globalFilters:
					{
						Registry_id: Registry_id,
						Person_id:filtersForm.findField('Person_id').getValue(),
						MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(),
						LpuSection_id:filtersForm.findField('LpuSection_id').getValue(),
						LpuSectionProfile_id:filtersForm.findField('LpuSectionProfile_id').getValue(),
						NumCard:filtersForm.findField('NumCard').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						filterRecords: filtersForm.findField('filterRecords').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
			}
		};

		this.UnionDataGridReset = function()
		{
			var form = this;
			var filtersForm = form.UnionRegistryDataFiltersPanel.getForm();

			filtersForm.reset();
			form.UnionDataGrid.removeAll(true);
		};

		this.RegistryDataFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
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
					form.DataGridSearch();
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
				},{
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
				},
				{
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					//columnWidth: .05,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls : 'x-btn-text',
						tabIndex: form.firstTabIndex+16,
						disabled: false,
						handler: function()
						{
							form.DataGridSearch();
						}
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					//columnWidth: .05,
					items: [{
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls : 'x-btn-text',
						tabIndex: form.firstTabIndex+17,
						disabled: false,
						style: 'margin-left: 4px;',
						handler: function(){
							var filtersForm = form.RegistryDataFiltersPanel.getForm();
							filtersForm.reset();
							form.UnionDataGrid.removeAll(true);
							form.DataGridSearch();
						}
					}]
				}]
			}]
		});

		this.UnionRegistryDataFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 85,
			//title: 'Ввод',
			listeners: {
				render: function() {
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
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					form.UnionDataGridSearch();
				},
				stopEvent: true
			}],
			items:
			[{
				layout: 'column',
				bodyStyle:'background:transparent;',
				defaults: { bodyStyle:'padding-left: 4px; padding-top: 4px; background:transparent;' },
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
						tabIndex:form.firstTabIndex+10
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
						tabIndex:form.firstTabIndex+12
					}]
				}]
			}, {
				layout: 'column',
				bodyStyle:'background:transparent;',
				defaults: { bodyStyle:'padding-left: 4px; background:transparent;' },
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
						tabIndex:form.firstTabIndex+13
					}, {
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex:form.firstTabIndex+13
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
						tabIndex:form.firstTabIndex+13
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
						tabIndex:form.firstTabIndex+13
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
						tabIndex:form.firstTabIndex+15
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
						iconCls : 'x-btn-text',
						tabIndex: form.firstTabIndex+16,
						disabled: false,
						handler: function()
						{
							form.UnionDataGridReset();
						}
					}, {
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls : 'x-btn-text',
						tabIndex: form.firstTabIndex+16,
						disabled: false,
						handler: function()
						{
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
				form.ErrorGridSearch();
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
					form.ErrorGridSearch();
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
			auditOptions: {
				field: 'Evn_id',
				key: 'Evn_id',
				deleted: false
			},
			stringfields:
			[
				{name: 'Evn_id', type: 'int', hidden:true},
				{name: 'EvnSectionKSG_id', type: 'int', hidden:true},
				{name: 'Evn_idValue', renderer: function(value, meta, rec) {
					var v = rec.get('Evn_id');
					if (!Ext.isEmpty(rec.get('EvnSectionKSG_id'))) {
						v = rec.get('EvnSectionKSG_id') + '  (' + rec.get('Evn_id') + ')';
					}
					return v;
				}, header: 'ИД случая', hidden: false},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', hidden:true},
                {name: 'CmpCloseCard_id', type: 'int', hidden: true},
                {name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'archiveRecord', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'IsRDL', type: 'int', hidden:true},
				{name: 'needReform', type: 'int', hidden:true},
				{name: 'isNoEdit', type: 'int', hidden:true},
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
				{name: 'Person_IsBDZ',  header: 'БДЗ', type: 'checkbox', width: 40},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'К/д факт', width: 70},
				{name: 'RegistryData_KdPlan', header: 'К/д норматив', width: 70},
				{name: 'RegistryData_KdPay', header: 'К/д к оплате', width: 70},
				{name: 'RegistryData_Tariff', type: 'money', header: 'Тариф', width: 70},
				{name: 'RegistryData_ItogSum', type: 'money', header: 'Сумма к оплате', width: 90},
				{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
				//{name: 'timeReform',id:'timeReform', type: 'datetimesec', header: 'Изменена', width: 100},
				{name: 'Err_Count', hidden:true},
				{name: 'EvnPLCrossed_Count', hidden:true},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'RegistryData_IsPaid', type: 'int', hidden:true},
				{name: 'IsGroupEvn', type: 'int', hidden:true},
				{name: 'MaxEvn_id', type: 'int', hidden:true, group: true, key: true},
				{name: 'isEvnFuncRequest', type: 'bool', hidden:true},
				{name: 'isEvnLabRequest', type: 'bool', hidden:true},
				{name: 'MedService_id', type: 'int', hidden:true},
				{name: 'EvnDirection_id', type: 'int', hidden:true}
			],
			listeners: {
				render: function(grid) {

					//this.getAction('action_add_all').setDisabled(this.getParam('RegistryStatus_id')!=3);


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
				// https://redmine.swan.perm.ru/issues/12624
				// Для Перми редактирование данных и удаление человека закрыто
				{name:'action_setperson', disabled: true, hidden: true, tooltip: 'Редактировать данные человека в реестре', text: '<b>Редактировать данные человека</b>', handler: function() {form.setPerson();}},
				{name:'action_delperson', disabled: true, hidden: true, tooltip: 'Удалить созданные данные человека в реестре', text: 'Удалить данные человека', handler: function() {form.deletePerson();}},
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {form.openForm(form.DataGrid, {});}},
				{name:'action_view', disabled: false, hidden: false, text: 'Сменить пациента в учетном документе', tooltip: 'Изменить пациента в учетном документе', icon: 'img/icons/doubles16.png', handler: function() { form.changePerson(); } },
				{name:'action_delete', disabled: false, handler: function() { form.deleteRegistryData(form.DataGrid, false); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.DataGrid, {}, 'OpenPerson');}}

			],
			callbackPersonEdit: function(person, record)
			{
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
				if (isAdmin)
				{
					this.setActionDisabled('action_setperson',(this.getCount()==0 || !(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())));
					this.setActionDisabled('action_delperson',(!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())));
				}
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3 || !(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())));
				this.setActionDisabled('action_edit',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
				this.setActionDisabled('action_view',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
				if(this.showGroup){
					form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
				}else{
					form.DataGrid.getGrid().getStore().clearGrouping()
				}
			},
			onDblClick: function()
			{
				form.openForm(form.DataGrid, {});
			},
			onEnter: function()
			{
				form.openForm(form.DataGrid, {});
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				var form = this;
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				var records = form.DataGrid.getGrid().getSelectionModel().selections.items;
				var disabled = false;
				records.forEach(function(rec){
					if(rec.get('RegistryData_deleted')!=2 || RegistryStatus_id!=3){
						disabled = true;
					}
				});
				form.DataGrid.setActionDisabled('action_undelete_all_records',disabled);
				// Меняем текст акшена удаления в зависимости от данных
				form.DataGrid.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');

				var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();

				if ( (isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())
					&& reestr && reestr.get('Registry_id') && reestr.get('RegistryStatus_id') == 3
					&& reestr.get('RegistryType_id').toString().inlist([ '1', '2', '6', '7', '8', '9', '10', '11', '12', '14', '15', '16' ])
				) {
					form.DataGrid.setActionDisabled('action_view', false);
				}
				else {
					form.DataGrid.setActionDisabled('action_view', true);
				}
			}.createDelegate(this),
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
				if (row.get('Err_Count') > 0 || row.get('RegistryData_IsPaid') == 1 || row.get('EvnPLCrossed_Count') > 0 || row.get('RegistryHealDepResType_id') == 2)
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
		this.UnionDataGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'UnionData',
			title:'Данные',
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
			auditOptions: {
				field: 'Evn_id',
				key: 'Evn_id',
				deleted: false
			},
			stringfields:
			[
				{name: 'keyField', key: true},
				{name: 'Evn_id', type: 'int', hidden:true},
				{name: 'EvnSectionKSG_id', type: 'int', hidden:true},
				{name: 'Evn_idValue', renderer: function(value, meta, rec) {
					var v = rec.get('Evn_id');
					if (!Ext.isEmpty(rec.get('EvnSectionKSG_id'))) {
						v = rec.get('EvnSectionKSG_id') + '  (' + rec.get('Evn_id') + ')';
					}
					return v;
				}, header: 'ИД случая', hidden: false},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', hidden:true},
                {name: 'CmpCloseCard_id', type: 'int', hidden: true},
                {name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryCheckStatus_Code', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'archiveRecord', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
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
				{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
				//{name: 'timeReform',id:'timeReform', type: 'datetimesec', header: 'Изменена', width: 100},
				{name: 'ErrTfoms_Count', hidden:true},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'RegistryData_IsPaid', type: 'int', hidden:true},
				{name: 'IsGroupEvn', type: 'int', hidden:true}
			],
			actions:
			[
				// https://redmine.swan.perm.ru/issues/12624
				// Для Перми редактирование данных и удаление человека закрыто
				{name:'action_setperson', disabled: true, hidden: true, tooltip: 'Редактировать данные человека в реестре', text: '<b>Редактировать данные человека</b>', handler: function() {form.setPerson();}},
				{name:'action_delperson', disabled: true, hidden: true, tooltip: 'Удалить созданные данные человека в реестре', text: 'Удалить данные человека', handler: function() {form.deletePerson();}},
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {form.openForm(form.UnionDataGrid, {});}},
				{name:'action_view', disabled: ( !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin()), hidden: false, text: 'Сменить пациента в учетном документе', tooltip: 'Изменить пациента в учетном документе', icon: 'img/icons/doubles16.png', handler: function() { form.changePerson(); } },
				{name:'action_delete', disabled: true, handler: function() { form.deleteRegistryData(form.UnionDataGrid, false); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.UnionDataGrid, {}, 'OpenPerson');}},
				{name:'action_addmeksmo', text:'Добавить ошибку МЭК от СМО', handler: function() { this.addMekSmo(); }.createDelegate(this)}

			],
			callbackPersonEdit: function(person, record)
			{
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
			onDblClick: function()
			{
				form.openForm(form.UnionDataGrid, {});
			},
			onEnter: function()
			{
				form.openForm(form.UnionDataGrid, {});
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				var form = this;

				var unionRecord = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
				form.UnionDataGrid.setActionDisabled('action_addmeksmo',(unionRecord.get('RegistryCheckStatus_Code')!=10));

				// Меняем текст акшена удаления в зависимости от данных
				form.UnionDataGrid.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');

				if ( (isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())
					&& record && record.get('RegistryType_id') && record.get('RegistryType_id').toString().inlist([ '1', '2', '6', '7', '8', '9', '10', '11', '12', '14', '15', '16' ])
				) {
					form.UnionDataGrid.setActionDisabled('action_view', false);
				}
				else {
					form.UnionDataGrid.setActionDisabled('action_view', true);
				}
			}.createDelegate(this)
		});

		this.UnionDataGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';

				if ((row.get('IsRDL')>0) && (isAdmin))
					cls = cls+'x-grid-rowblue ';
				if (row.get('ErrTfoms_Count') > 0)
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
				{name: 'Evn_id', type: 'int', hidden:true},
				{name: 'EvnSectionKSG_id', type: 'int', hidden:true},
				{name: 'Evn_idValue', renderer: function(value, meta, rec) {
					var v = rec.get('Evn_id');
					if (!Ext.isEmpty(rec.get('EvnSectionKSG_id'))) {
						v = rec.get('EvnSectionKSG_id') + '  (' + rec.get('Evn_id') + ')';
					}
					return v;
				}, header: 'ИД случая', hidden: false},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', header:'Person_id', type: 'int', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
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
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true},
				{name: 'IsGroupEvn', type: 'int', hidden:true}
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
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {form.openForm(form.ErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.ErrorGrid, false); }},
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryError(); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryDataAll(form.ErrorGrid); }},
				{name:'action_openevn', visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() {form.openForm(form.ErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.ErrorGrid, {}, 'OpenPerson');}},
				{name:'action_showdoublesinfo', icon: 'img/icons/warning16.png', text: 'ТАП с дублем', handler: function() { form.showEvnPLDoublesInfo(); } }

			],
			callbackPersonEdit: function(person, record)
			{
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
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');
				this.setActionDisabled('action_delete',((RegistryStatus_id!=3||!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin())) || (record.get('RegistryData_notexist')==2) || (record.get('RegistryData_deleted')==2)));

				this.getAction('action_showdoublesinfo').setHidden(record.get('RegistryErrorType_Code') != '7965');
			},
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
				if(record)
					this.setActionDisabled('action_delete',((RegistryStatus_id!=3||!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin())) || (record.get('RegistryData_notexist')==2) || (record.get('RegistryData_deleted')==2)));

				this.setActionDisabled('action_deleteall',(RegistryStatus_id!=3||!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin())));
				this.setActionDisabled('action_edit', !(isUserGroup([ 'RegistryUser' ])||isSuperAdmin()));
				this.setActionDisabled('action_openevn',!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin()));
				this.setActionDisabled('action_openperson',!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin()));
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
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
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
				{name:'action_edit', handler: function() {form.openForm(form.NoPolisGrid, {}, 'OpenPerson');}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function()
			{
				this.setActionDisabled('action_edit',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
			}
		});


		// 5. Случаи без оплаты
		this.NoPayGrid = new sw.Promed.ViewFrame(
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
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
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
				{name:'action_edit', handler: function() {form.openForm(form.NoPayGrid, {}, 'OpenPerson');}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', disabled: true, hidden: true }
			],
			onLoadData: function()
			{
			}
		});

		// 6. Ошибки перс/данных
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
				{name: 'Person_id', isparams: true, type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
				//{name: 'Server_id', isparams: true, type: 'int', hidden:true, value: 0},
				//{name: 'PersonEvn_id', isparams: true, type: 'int', hidden:true},
				{name: 'Person2_id', isparams: true, type: 'int', header: 'Person2_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
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
				this.setActionDisabled('action_edit',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
				this.setActionDisabled('action_view',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
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

		this.showRegistryErrorTFOMSHistory = function()
		{
			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');

			if (Registry_id > 0) {
				// открываем окно с историе ошибок
				getWnd('swRegistryErrorTFOMSHistoryWindow').show({
					Registry_id: Registry_id
				});
			}
		}

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
					callback: function() {
						// если записей нет, нужно грид скрыть
						if (form.TFOMSErrorGrid.getGrid().getStore().getCount() > 0) {
							form.TFOMSErrorGrid.show();
							// если тфомс показывается то высота бдз = 200
							form.BDZErrorGrid.setHeight(200);
						} else {
							form.TFOMSErrorGrid.hide();
							// если тфомс скрывается то высота бдз = высоте родительского контейнера
							form.BDZErrorGrid.setHeight(form.BDZErrorGrid.ownerCt.getEl().getHeight()-55);
						}
						form.TFOMSErrorGrid.ownerCt.doLayout();
					},
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

				form.BDZErrorGrid.loadData(
				{
					callback: function() {
						// если записей нет, нужно грид скрыть
						if (form.BDZErrorGrid.getGrid().getStore().getCount() > 0) {
							form.BDZErrorGrid.show();
							// filter_form.findField('RegistryErrorBDZType_id').showContainer();
						} else {
							form.BDZErrorGrid.hide();
							// filter_form.findField('RegistryErrorBDZType_id').hideContainer();
						}
						form.BDZErrorGrid.ownerCt.doLayout();
					},
					globalFilters:
					{
						Person_FIO:filtersForm.findField('Person_FIO').getValue(),
						RegistryErrorType_Code:filtersForm.findField('TFOMSError').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						Registry_id: Registry_id,
						RegistryType_id: RegistryType_id,
						RegistryErrorBDZType_id: filtersForm.findField('RegistryErrorBDZType_id').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad:true
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
				form.UnionTFOMSErrorGrid.loadData(
				{
					callback: function() {
						// если записей нет, нужно грид скрыть, не нужно!
						if (true || form.UnionTFOMSErrorGrid.getGrid().getStore().getCount() > 0) {
							form.UnionTFOMSErrorGrid.show();
							// если тфомс показывается то высота бдз = 200
							form.UnionBDZErrorGrid.setHeight(200);
						} else {
							form.UnionTFOMSErrorGrid.hide();
							// если тфомс скрывается то высота бдз = высоте родительского контейнера
							form.UnionBDZErrorGrid.setHeight(form.UnionBDZErrorGrid.ownerCt.getEl().getHeight()-30);
						}
						form.UnionTFOMSErrorGrid.ownerCt.doLayout();
					},
					globalFilters:
					{
						Person_FIO:filtersForm.findField('Person_FIO').getValue(),
						RegistryErrorType_Code:filtersForm.findField('TFOMSError').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						Registry_id: Registry_id,
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});

				form.UnionBDZErrorGrid.loadData(
				{
					callback: function() {
						// если записей нет, нужно грид скрыть, не нужно!
						if (true || form.UnionBDZErrorGrid.getGrid().getStore().getCount() > 0) {
							form.UnionBDZErrorGrid.show();
							// filter_form.findField('RegistryErrorBDZType_id').showContainer();
						} else {
							form.UnionBDZErrorGrid.hide();
							// filter_form.findField('RegistryErrorBDZType_id').hideContainer();
						}
						form.UnionBDZErrorGrid.ownerCt.doLayout();
					},
					globalFilters:
					{
						Person_FIO:filtersForm.findField('Person_FIO').getValue(),
						RegistryErrorType_Code:filtersForm.findField('TFOMSError').getValue(),
						Evn_id:filtersForm.findField('Evn_id').getValue(),
						Registry_id: Registry_id,
						RegistryErrorBDZType_id: filtersForm.findField('RegistryErrorBDZType_id').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad:true
				});
			}
		};

		this.TFOMSHeader = new Ext.form.Label({
			html: ""
		});

		this.UnionTFOMSHeader = new Ext.form.Label({
			html: ""
		});

		this.RegistryTFOMSFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					form.TFOMSGridSearch();
				},
				stopEvent: true
			}],
			items:
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				items:
				[
					form.TFOMSHeader
				]
			},
			{
				layout: 'column',
				border: false,
				bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle:'width:160%;padding-left: 4px; background:#DFE8F6;'},
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
					columnWidth: .25,
					labelWidth: 80,
					items:
					[{
						anchor: '100%',
						allowBlank: false,
						editable: false,
						fieldLabel: 'Ошибки БДЗ',
						hiddenName: 'RegistryErrorBDZType_id',
						tabIndex: form.firstTabIndex + 26,
						valueField: 'RegistryErrorBDZType_id',
						displayField: 'RegistryErrorBDZType_Name',
						value: 3,
						store: new Ext.data.SimpleStore({
							autoLoad: false,
							fields: [
								{name: 'RegistryErrorBDZType_id', type: 'int'},
								{name: 'RegistryErrorBDZType_Name', type: 'string'},
							],
							data: [
								[1, 'Не идентифицированные'],
								[2, 'Идентифицированные'],
								[3, 'Все']
							],
							key: 'RegistryErrorBDZType_id',
							sortInfo: {
								field: 'RegistryErrorBDZType_id'
							}
						}),
						tpl: '<tpl for="."><div class="x-combo-list-item" style="white-space:normal;">'+
							'{RegistryErrorBDZType_Name}&nbsp;'+
							'</div></tpl>',
						xtype: 'swbaselocalcombo'
					}]
				},
				{
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex+26,
						disabled: false,
						handler: function()
						{
							form.TFOMSGridSearch();
						}
					}]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					items: [{
						tooltip: 'История ошибок',
						xtype: 'button',
						text: 'История ошибок',
						icon: 'img/icons/history16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex+26,
						disabled: false,
						handler: function()
						{
							form.showRegistryErrorTFOMSHistory();
						}
					}]
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

		this.UnionRegistryTFOMSFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
			//title: 'Ввод',
			keys:
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e)
				{
					form.UnionTFOMSGridSearch();
				},
				stopEvent: true
			}],
			items:
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				items:
				[
					form.UnionTFOMSHeader
				]
			},
			{
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
					columnWidth: .25,
					labelWidth: 80,
					items:
					[{
						anchor: '100%',
						allowBlank: false,
						editable: false,
						fieldLabel: 'Ошибки БДЗ',
						hiddenName: 'RegistryErrorBDZType_id',
						tabIndex: form.firstTabIndex + 26,
						valueField: 'RegistryErrorBDZType_id',
						displayField: 'RegistryErrorBDZType_Name',
						value: 3,
						store: new Ext.data.SimpleStore({
							autoLoad: false,
							fields: [
								{name: 'RegistryErrorBDZType_id', type: 'int'},
								{name: 'RegistryErrorBDZType_Name', type: 'string'},
							],
							data: [
								[1, 'Не идентифицированные'],
								[2, 'Идентифицированные'],
								[3, 'Все']
							],
							key: 'RegistryErrorBDZType_id',
							sortInfo: {
								field: 'RegistryErrorBDZType_id'
							}
						}),
						tpl: '<tpl for="."><div class="x-combo-list-item" style="white-space:normal;">'+
							'{RegistryErrorBDZType_Name}&nbsp;'+
							'</div></tpl>',
						xtype: 'swbaselocalcombo'
					}]
				},
				{
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					columnWidth: .1,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex+26,
						disabled: false,
						handler: function()
						{
							form.UnionTFOMSGridSearch();
						}
					}]
				}]
			}]
		});

		// Ошибки БДЗ
		this.BDZErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'BDZError',
			title:'Ошибки стадии распределения по СМО (БДЗ)',
			object: 'RegistryErrorBDZ',
			dataUrl: '/?c=Registry&m=loadRegistryErrorBDZ',
			paging: true,
			root: 'data',
			height: 200,
			region: 'south',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			split: true,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'RegistryErrorBDZ_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'Evn_id', hidden:!isSuperAdmin()},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', isparams: true, type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
				{name: 'Person2_id', isparams: true, type: 'int', header: 'Person2_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'RegistryError_Comment', header: 'Описание ошибки', autoexpand: true},
				{name: 'RegistryErrorBDZ_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis',  header: 'Серия, № полиса', width: 130},
				// {name: 'Person_PolisDate',  header: 'Период действия полиса', width: 150},
				{name: 'Person_EvnDate',  header: 'Период лечения', width: 150},
				{name: 'Person_OrgSmo',  header: 'СМО', width: 180},
				{name: 'RegistryErrorBDZ_FieldName', hidden:true},
				{name: 'RegistryErrorBDZ_BaseElement', hidden:true}
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
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: 'Объединить', hidden: true, handler: function() {/*form.doPersonUnion(form.BDZErrorGrid, {});*/}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.BDZErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.BDZErrorGrid, true); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.ErrorGrid, {}, 'OpenPerson');}}
			],
			listeners: {
				'resize': function() {
					if (form.TFOMSErrorGrid.hidden) {
						form.BDZErrorGrid.setHeight(form.BDZErrorGrid.ownerCt.getEl().getHeight()-55);
					}
				}
			},
			callbackPersonEdit: function(person, record)
			{
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
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');
			},
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3||!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin())));
				this.setActionDisabled('action_deleteall',(RegistryStatus_id!=3||!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin())));
			}
		});
		this.BDZErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
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
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: (!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
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
			title:'Ошибки стадий ФЛК и МЭК',
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
				{name: 'Evn_id', type: 'int', hidden:true},
				{name: 'EvnSectionKSG_id', type: 'int', hidden:true},
				{name: 'Evn_idValue', renderer: function(value, meta, rec) {
					var v = rec.get('Evn_id');
					if (!Ext.isEmpty(rec.get('EvnSectionKSG_id'))) {
						v = rec.get('EvnSectionKSG_id') + '  (' + rec.get('Evn_id') + ')';
					}
					return v;
				}, header: 'ИД случая', hidden: false},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorTFOMSLevel_Name', header: 'Уровень ошибки', width: 120},
				{name: 'RegistryError_FieldName', header: 'Ошибка', width: 250},
				{name: 'RegistryError_Comment', header: 'Рекомендации по исправлению', autoexpand: true},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},

				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'RegistryErrorTFOMS_Source', type: 'string', header: 'Источник', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'UslugaComplex_Code', type: 'string', header: 'Код услуги', width: 100},
				{name: 'UslugaComplex_Name', type: 'string', header: 'Наименование услуги', width: 200},
				{name: 'RegistryErrorTFOMS_FieldName', hidden:true},
				{name: 'RegistryErrorTFOMS_BaseElement', hidden:true},
				{name: 'IsGroupEvn', type: 'int', hidden:true}
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
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {form.openForm(form.TFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.TFOMSErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.TFOMSErrorGrid, true); }},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() {form.openForm(form.TFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.TFOMSErrorGrid, {}, 'OpenPerson');}},
				{name:'-', visible: !isAdmin},
				{name:'action_tehinfo', disabled: true, visible: true, icon: 'img/icons/info16.png', tooltip: 'Технические подробности', text: 'Технические подробности', handler: function() {form.openInfoForm(form.TFOMSErrorGrid)}}

			],
			callbackPersonEdit: function(person, record)
			{
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
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');

				if (this.getCount()>0)
				{
					this.setActionDisabled('action_openperson',!isAdmin);
					this.setActionDisabled('action_openevn',!isAdmin);
					this.setActionDisabled('action_tehinfo',false);
					this.setActionDisabled('action_tehinfo',record.get(''));
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

		// Ошибки БДЗ
		this.UnionBDZErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'UnionBDZError',
			title:'Ошибки стадии распределения по СМО (БДЗ)',
			object: 'RegistryErrorBDZ',
			dataUrl: '/?c=Registry&m=loadUnionRegistryErrorBDZ',
			paging: true,
			root: 'data',
			height: 200,
			region: 'south',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			split: true,
			useEmptyRecord: false,
			stringfields:
			[
				{name: 'RegistryErrorBDZ_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: 'Evn_id', hidden:!isSuperAdmin()},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', isparams: true, type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
				{name: 'Person2_id', isparams: true, type: 'int', header: 'Person2_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'RegistryError_Comment', header: 'Описание ошибки', autoexpand: true},
				{name: 'RegistryErrorBDZ_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', header: 'Дата рождения', width: 90},
				{name: 'Person_Polis',  header: 'Серия, № полиса', width: 130},
				// {name: 'Person_PolisDate',  header: 'Период действия полиса', width: 150},
				{name: 'Person_EvnDate',  header: 'Период лечения', width: 150},
				{name: 'Person_OrgSmo',  header: 'СМО', width: 180},
				{name: 'RegistryErrorBDZ_FieldName', hidden:true},
				{name: 'RegistryErrorBDZ_BaseElement', hidden:true}
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
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: 'Объединить', hidden: true, handler: function() {/*form.doPersonUnion(form.BDZErrorGrid, {});*/}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.UnionBDZErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.UnionBDZErrorGrid, true); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.UnionBDZErrorGrid, {}, 'OpenPerson');}}
			],
			listeners: {
				'resize': function() {
					if (form.UnionTFOMSErrorGrid.hidden) {
						form.UnionBDZErrorGrid.setHeight(form.UnionBDZErrorGrid.ownerCt.getEl().getHeight()-30);
					}
				}
			},
			callbackPersonEdit: function(person, record)
			{
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');
			}
		});
		this.UnionBDZErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
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
		this.UnionTFOMSErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'UnionTFOMSError',
			title:'Ошибки стадий ФЛК и МЭК',
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
				{name: 'Evn_id', type: 'int', hidden:true},
				{name: 'EvnSectionKSG_id', type: 'int', hidden:true},
				{name: 'Evn_idValue', renderer: function(value, meta, rec) {
					var v = rec.get('Evn_id');
					if (!Ext.isEmpty(rec.get('EvnSectionKSG_id'))) {
						v = rec.get('EvnSectionKSG_id') + '  (' + rec.get('Evn_id') + ')';
					}
					return v;
				}, header: 'ИД случая', hidden: false},
				{name: 'CmpCloseCard_id', type: 'int', hidden: true},
				{name: 'CmpCallCardInputType_id', type: 'int', hidden: true},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: 'Код ошибки', width: 80},
				{name: 'RegistryErrorTFOMSLevel_Name', header: 'Уровень ошибки', width: 120},
				{name: 'RegistryError_FieldName', header: 'Ошибка', width: 250},
				{name: 'RegistryError_Comment', header: 'Рекомендации по исправлению', autoexpand: true},
				{name: 'RegistryErrorTFOMS_Comment', header: 'Комментарий', width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'OrgSMO_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: 'ФИО пациента', width: 250},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'RegistryErrorTFOMS_Source', type: 'string', header: 'Источник', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'UslugaComplex_Code', type: 'string', header: 'Код услуги', width: 100},
				{name: 'UslugaComplex_Name', type: 'string', header: 'Наименование услуги', width: 200},
				{name: 'RegistryErrorTFOMS_FieldName', hidden:true},
				{name: 'RegistryErrorTFOMS_BaseElement', hidden:true},
				{name: 'IsGroupEvn', type: 'int', hidden:true}
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
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: '<b>Исправить</b>', handler: function() {form.openForm(form.UnionTFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.UnionTFOMSErrorGrid, false); }},
				{name:'action_deleteerror', icon: 'img/icons/delete16.png', disabled: true, text: 'Удалить ошибку', handler: function() { form.deleteRegistryErrorTFOMS(form.UnionTFOMSErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.UnionTFOMSErrorGrid, true); }},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: 'Открыть учетный документ', icon: 'img/icons/pol-eplstream16.png',  text: 'Открыть учетный документ', handler: function() {form.openForm(form.UnionTFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.UnionTFOMSErrorGrid, {}, 'OpenPerson');}},
				{name:'-', visible: !isAdmin},
				{name:'action_tehinfo', disabled: true, visible: true, icon: 'img/icons/info16.png', tooltip: 'Технические подробности', text: 'Технические подробности', handler: function() {form.openInfoForm(form.UnionTFOMSErrorGrid)}}

			],
			callbackPersonEdit: function(person, record)
			{
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');

				if (this.getCount()>0)
				{
					this.setActionDisabled('action_deleteerror',!Ext.isEmpty(record.get('OrgSMO_id')));
					this.setActionDisabled('action_openperson',!isAdmin);
					this.setActionDisabled('action_openevn',!isAdmin);
					this.setActionDisabled('action_tehinfo',false);
				}
			}
		});
		this.UnionTFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView(
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

		this.EvnPLCrossedGrid = new sw.Promed.ViewFrame({
			id: form.id+'EvnPLCrossed',
			title:'Пересечение ТАП',
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
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden: true },
				{name: 'PersonEvn_id', type: 'int', hidden: true },
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'EvnPL_NumCard', header: '№ талона', width: 80},
				{name: 'EvnPLCrossed_NumCards', header: '№ талонов, с которыми имеется пересечение', width: 200}
			],
			listeners: {
				render: function(grid) {
					var action_delete_rd = {
							name: 'action_delete_rd',
							text: 'Удалить случай из реестра',
							icon: 'img/icons/delete16.png',
							handler: function() {
								form.deleteRegistryData(form.EvnPLCrossedGrid);
							}
						},
						action_delete_rd_all = {
							name: 'action_delete_rd_all',
							text: 'Удалить все случаи с пересечениями из реестра',
							icon: 'img/icons/delete16.png',
							handler: function() {
								form.deleteRegistryData(form.EvnPLCrossedGrid, true);
							}
						};

					grid.ViewActions[action_delete_rd.name] = new Ext.Action(action_delete_rd);
					grid.ViewActions[action_delete_rd_all.name] = new Ext.Action(action_delete_rd_all);
					grid.ViewContextMenu.addSeparator();
					grid.ViewContextMenu.add(grid.ViewActions[action_delete_rd.name]);
					grid.ViewContextMenu.add(grid.ViewActions[action_delete_rd_all.name]);
				}.createDelegate(this)
			},
			actions:
			[
				{name:'action_add', hidden: true },
				{name:'action_edit', hidden: true },
				{name:'action_view', handler: function() {form.openForm(form.EvnPLCrossedGrid, {action: 'view'}, 'swEvnPLEditWindow');} },
				{name:'action_delete', text: 'Подать случай на оплату', handler: this.deleteRegistryDouble.createDelegate(this, ['current']) }
			],
			onLoadData: function() {
				//
			},
			onRowSelect: function(sm, rowIdx, record) {
				this.EvnPLCrossedGrid.getAction('action_delete').setDisabled(record.get('RegistryData_deleted') == 2);
				// Меняем текст акшена удаления в зависимости от данных
				this.EvnPLCrossedGrid.getAction('action_delete_rd').setText(record.get('RegistryData_deleted') == 2 ? 'Восстановить случай в реестре' : 'Удалить случай из реестра');
			}.createDelegate(this)
		});

		this.EvnPLCrossedGrid.ViewGridPanel.view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			}
		});

		this.DoublePLGrid = new sw.Promed.ViewFrame({
			id: form.id+'DoublePL',
			title:'Дубли посещений',
			object: 'RegistryDoublePL',
			dataUrl: '/?c=Registry&m=loadRegistryDoublePL',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields:
			[
				{name: 'Evn_id', type: 'int', header: 'Evn_id', key: true, hidden:!isSuperAdmin()},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:(!isUserGroup([ 'RegistryUserReadOnly' ]) && !isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())},
                {name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden: true },
				{name: 'PersonEvn_id', type: 'int', hidden: true },
				{name: 'EvnPL_NumCard', header: '№ талона', width: 80},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'LpuSection_FullName', header: 'Отделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 250},
				{name: 'EvnPL_setDate', header: 'Дата начала'},
				{name: 'EvnPL_disDate', header: 'Дата окончания'}
			],
			actions: [
				{name: 'action_add', hidden: true, disabled: true},
				{
					name: 'action_edit', text: 'Управление дублями', hidden: false, handler: function () {
						form.doubleControl();
					}
				},
				{name: 'action_view', hidden: true, disabled: true},
				{name: 'action_delete', hidden: true, disabled: true}
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
						this.hideTabStripItem(this.getItem('tab_evnplcrossed'));
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
				{
					title: '5. Случаи без оплаты',
					layout: 'fit',
					id: 'tab_datanopay',
					iconCls: 'good',
					border:false,
					items: [form.NoPayGrid]
				},
				{
					title: '6. Ошибки перс/данных',
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datapersonerr',
					border:false,
					items: [form.PersonErrorGrid]
				},
				{
					title: '7. Итоги проверки ТФОМС',
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
							form.BDZErrorGrid,
							form.TFOMSErrorGrid
						]
					}]
				},
				{
					title: '7. Итоги проверки МЗ',
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
					title: '8. Пересечение ТАП',
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_evnplcrossed',
					border:false,
					items: [
						form.EvnPLCrossedGrid
					]
				},
				{
					title: '9. Дубли посещений',
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datapldouble',
					border:false,
					items: [form.DoublePLGrid]
				}]
		});

		this.RegistryListPanel = new sw.Promed.Panel({
			border: false,
			id: form.id+'RegistryListPanel',
			layout:'border',
			defaults: {split: true},
			items: [form.AccountGrid, form.DataTab]
		});

		this.UnionRegistryGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'UnionRegistryGrid',
			region: 'north',
			height: 203,
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
				var Registry_id = record.get('Registry_id');
				form.onUnionRegistrySelect(Registry_id, false, record);

				form.UnionActionsMenu.items.items[1].disable();
				form.UnionActionsMenu.items.items[2].disable();
				form.UnionActionsMenu.items.items[3].disable();
				form.UnionActionsMenu.items.items[4].disable();
				form.UnionActionsMenu.items.items[5].disable();
				form.UnionActionsMenu.items.items[6].disable();
				form.UnionActionsMenu.items.items[7].hide();
				
				if(Registry_id && record.get('PayType_SysNick') !== "mbudtrans"){
					form.UnionActionsMenu.items.items[7].show();
				}

				form.UnionActionsMenu.items.items[3].show();
				form.UnionActionsMenu.items.items[4].hide();
				if (record.get('RegistryStatus_id') == 4) {
					// form.UnionActionsMenu.items.items[3].hide();
					// form.UnionActionsMenu.items.items[4].show();
				}

				if (!Ext.isEmpty(Registry_id)) {
					form.UnionActionsMenu.items.items[6].enable();
				}

				if (Ext.isEmpty(record.get('RegistryCheckStatus_Code'))) {
					form.UnionActionsMenu.items.items[1].enable();
				}

				if (record.get('RegistryCheckStatus_Code') == '12') {
					form.UnionActionsMenu.items.items[2].enable();
				}

				if (record.get('RegistryCheckStatus_Code') == '10') {
					if (isSuperAdmin() || isUserGroup(['RegistryUser'])) {
						form.UnionActionsMenu.items.items[3].enable(); // отметить как оплаченный
					}
					form.UnionActionsMenu.items.items[4].enable();
					form.UnionActionsMenu.items.items[5].enable();
				}
			},
			stringfields:
				[
					{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
					{name: 'Registry_Num', header: 'Номер', width: 80},
					{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
					{name: 'Registry_accDate', type: 'date', header: 'Дата', width: 90},
					{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
					{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
					{name: 'Registry_Count', type:'int', header: 'Количество', width: 110},
					{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
					{name: 'Registry_NoErrSum', type:'money', header: 'Сумма без ошибок', width: 100},
					{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
					{name: 'Registry_updDT', type:'date', header: 'Дата изменения', width: 110},
					{name: 'Registry_sendDate', header: 'Последняя отправка', width: 110},
					{name: 'BdzErrors_IsData', hidden: true},
					{name: 'MekErrors_IsData', hidden: true},
					{name: 'FlkErrors_IsData', hidden: true},
					{name: 'RegistryStatus_id', type: 'int', hidden: true},
					{name: 'Registry_IsNew', type: 'int',  hidden: true, isparams: true},
					{name: 'PayType_SysNick', type: 'string',  hidden: true, isparams: true},
					{name: 'RegistryCheckStatus_id', hidden: true},
					{name: 'RegistryCheckStatus_Code', hidden: true},
					{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200}
				],
			actions:
				[
					{name:'action_add' },
					{name:'action_edit' },
					{name:'action_view' },
					{name:'action_delete', url: '/?c=Registry&m=deleteUnionRegistry' }
				]
		});

		this.UnionActionsMenu = new Ext.menu.Menu({
			items: [
				{name:'action_expxml', text:'Экспорт в XML', handler: function() { this.exportUnionRegistryToXml(1); }.createDelegate(this)},
				{name:'action_expxmlsign', text:'Подписать реестр (ЭЦП)', handler: function() { this.exportUnionRegistryToXml(2); }.createDelegate(this)},
				{name:'action_expxmlsend', text:'Отправить в ТФОМС', handler: function() { this.exportUnionRegistryToXml(3); }.createDelegate(this)},
				{name:'action_setpaid', text:'Отметить как оплаченный', handler: function() { this.setUnionRegistryStatus(4); }.createDelegate(this)},
				{name:'action_setunpaid', text:'Снять отметку "оплачен"', handler: function() { this.setUnionRegistryStatus(2); }.createDelegate(this)},
				{name:'action_importmeksmo', text:'Импорт ошибок МЭК от СМО', handler: function() { this.importMekSmo(); }.createDelegate(this)},
				{name:'action_correcterrors', text:'Задать смещение для ошибок', hidden: !isSuperAdmin(), handler: function() { this.correctErrors(); }.createDelegate(this)},
				{name:'action_exportonko', text:'Выгрузка ОНКО случаев', handler: function() { this.exportOnko(); }.createDelegate(this)}
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
					{name: 'Registry_Count', type: 'int', header: 'Количество', width: 80},
					{name: 'KatNasel_Name', header: 'Категория населения', width: 130},
					{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
					{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
					{name: 'Registry_NoErrSum', type:'money', header: 'Сумма без ошибок', width: 100},
					{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
					{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
					{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
					{name: 'Registry_updDate', header: 'Дата изменения', width: 110},
					{name: 'Registry_sendDate', header: 'Последняя отправка', width: 110},
					{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200}
				],
			actions:
				[
					{name:'action_add', disabled: true, hidden: true },
					{name:'action_edit', disabled: true, hidden: true },
					{name:'action_view', disabled: true, hidden: true },
					{name:'action_delete', disabled: true, hidden: true }
				]
		});

		this.UnionDataTab = new Ext.TabPanel(
		{
			//resizeTabs:true,
			border: false,
			region: 'center',
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
					var record = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
					if (record)
					{
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
				border:false,
				items: [form.UnionRegistryChildGrid]
			}, {
				title: '1. Данные',
				layout: 'fit',
				id: 'tab_uniondata',
				//iconCls: 'info16',
				border:false,
				items:
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.UnionRegistryDataFiltersPanel,form.UnionDataGrid]
					}]
			}, {
				title: '2. Итоги проверки ТФОМС',
				layout: 'fit',
				iconCls: 'good',
				id: 'tab_uniondatatfomserr',
				border:false,
				items:
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [
							form.UnionRegistryTFOMSFiltersPanel,
							form.UnionBDZErrorGrid,
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
			items: [form.UnionRegistryGrid, form.UnionDataTab]
		});

		Ext.apply(this,
		{
			layout:'border',
			defaults: {split: true},
			buttons:
			[{
				hidden: false,
				handler: function() {
					form.getReplicationInfo();
				},
				iconCls: 'ok16',
				text: 'Актуальность данных: (неизвестно)'
			},{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					form.hide();
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