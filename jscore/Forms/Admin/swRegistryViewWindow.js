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
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_reestra_uje_otkryito']);
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
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;
		owner.RegistryErrorFiltersPanel.getForm().reset();
		switch (level)
		{
			case 0: case 1:
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 2:
				/*
				owner.findById('regvRightPanel').setVisible(true);
				var Lpu_id = node.parentNode.attributes.object_value;
				var RegistryType_id = node.attributes.object_value;
				owner.AccountGrid.loadData({params:{RegistryType_id:RegistryType_id, Lpu_id:Lpu_id}, globalFilters:{RegistryType_id:RegistryType_id, Lpu_id:Lpu_id}});
				*/
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 3:
				owner.findById('regvRightPanel').setVisible(true);
				var Lpu_id = node.parentNode.parentNode.attributes.object_value;
				var RegistryType_id = node.parentNode.attributes.object_value;
				var RegistryStatus_id = node.attributes.object_value;
				owner.AccountGrid.setActionDisabled('action_add', (RegistryStatus_id!=3) || !(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
				//log(this.AccountGrid.getGrid().getColumnModel());
				
				// скрываем/открываем колонку
				owner.AccountGrid.setColumnHidden('RegistryStacType_Name', (RegistryType_id!=1 && RegistryType_id!=14));
				
				owner.AccountGrid.setColumnHidden('LpuBuilding_Name', (RegistryType_id.inlist([7,8,9,10,11,12])));
				owner.AccountGrid.setColumnHidden('KatNasel_Name', (RegistryType_id.inlist([8,9,10])));
				owner.AccountGrid.setColumnHidden('DispClass_Name', (RegistryType_id.inlist([1,2,6,8,10,14,15]))); // открыто при 7,9,11,12
				
				// Меняем колонки и отображение 
				if (RegistryType_id==1 || RegistryType_id==14)
				{
					// Для стаца одни названия 
					owner.DataGrid.setColumnHeader('RegistryData_Uet', lang['k_d_fakt']);
					owner.DataGrid.setColumnHeader('RegistryData_KdPay', lang['k_d_k_oplate']);
					owner.DataGrid.setColumnHeader('RegistryData_KdPlan', lang['k_d_normativ']);
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', lang['postuplenie']);
					owner.DataGrid.setColumnHidden('EvnPS_disDate', false);
					owner.DataGrid.setColumnHidden('RegistryData_KdPay', false);
					owner.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
					
					// без оплаты 
					//owner.DataGrid.setColumnHeader('Evn_setDate', 'Поступление');
					//owner.NoPayGrid.setColumnHidden('Evn_disDate', false);
					owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPay', lang['k_d_k_oplate']);
					owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdFact', lang['k_d_fakt']);
					owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPlan', lang['k_d_normativ']);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', false);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
					owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);
				}
				else 
				{
					// Для остальных - другие 
					owner.DataGrid.setColumnHeader('RegistryData_Uet', lang['uet_fakt']);
					owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', lang['poseschenie']);
					owner.DataGrid.setColumnHidden('EvnPS_disDate', true);
					if (RegistryType_id==2 || RegistryType_id==15) {
						owner.DataGrid.setColumnHeader('RegistryData_KdPay', lang['uet_k_oplate']);
						owner.DataGrid.setColumnHeader('RegistryData_KdPlan', lang['uet_normativ']);
						owner.DataGrid.setColumnHidden('RegistryData_KdPay', false);
						owner.DataGrid.setColumnHidden('RegistryData_KdPlan', false);
						
						// без оплаты 
						owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPay', lang['uet_k_oplate']);
						owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdFact', lang['uet_fakt']);
						owner.NoPayGrid.setColumnHeader('RegistryNoPay_KdPlan', lang['uet_normativ']);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', false);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', false);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', false);
					} else {
						owner.DataGrid.setColumnHidden('RegistryData_KdPay', true);
						owner.DataGrid.setColumnHidden('RegistryData_KdPlan', true);
						// без оплаты 
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPay', true);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdFact', true);
						owner.NoPayGrid.setColumnHidden('RegistryNoPay_KdPlan', true);
					}
				}
				
				if (RegistryType_id==6) {
					owner.DataGrid.setColumnHeader('LpuSection_name', lang['profil_brigadyi']);
					owner.ErrorGrid.setColumnHeader('LpuSection_name', lang['profil_brigadyi']);
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', lang['profil_brigadyi']);
					owner.NoPayGrid.setColumnHeader('LpuSection_Name', lang['profil_brigadyi']);
					owner.DoubleVizitGrid.setColumnHeader('LpuSection_FullName', lang['profil_brigadyi']);
				} else {
					owner.DataGrid.setColumnHeader('LpuSection_name', lang['otdelenie']);
					owner.ErrorGrid.setColumnHeader('LpuSection_name', lang['otdelenie']);
					owner.NoPolisGrid.setColumnHeader('LpuSection_Name', lang['otdelenie']);
					owner.NoPayGrid.setColumnHeader('LpuSection_Name', lang['otdelenie']);
					owner.DoubleVizitGrid.setColumnHeader('LpuSection_FullName', lang['otdelenie']);
				}
				
				// скрываем/открываем колонку статуса для пол-ки и стаца и дд (по крайней мере, пока)
				owner.AccountGrid.setColumnHidden('RegistryCheckStatus_Name', (!RegistryType_id.inlist(['1','2','6','7','8','9','10','11','12','14','15'])));
				
				// Показваем ошибки ТФОМС только для полки и стаца и СМП и дд (по крайней мере, пока)
				if (!RegistryType_id.inlist(['1','2','6','7','8','9','10','11','12','14','15'])) {
					if (owner.DataTab.getActiveTab().id == 'tab_datatfomserr') {
						owner.DataTab.setActiveTab(0);
					}
					owner.DataTab.hideTabStripItem('tab_datatfomserr');
				} else {
					owner.DataTab.unhideTabStripItem('tab_datatfomserr');
				}
				
				owner.AccountGrid.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12,14,15])))); // !!! Пока только для полки, потом поправить обратно 
				
				var dataTab = Ext.getCmp('RegistryViewWindowDataTab');
				var account = Ext.getCmp('RegistryViewWindowAccount');
				if (6 == RegistryStatus_id){
					/*Ничего не скрываем, но все-таки для себя запомним, что мы находимся в удаленных реестрах
					//для удаленных реестров скрываем таб с данными
					if (!dataTab.hidden) {
						dataTab.hide();
						dataTab.ownerCt.doLayout();
					}
					*/
					account.deletedRegistriesSelected = true;
				}  else {
					/*Ничего не скрываем, но все-таки для себя запомним, что мы находимся в удаленных реестрах
					//для всех остальных - показываем
					if (dataTab.hidden){
						dataTab.show();
						account.setHeight(203);
						dataTab.setHeight(250);
						dataTab.ownerCt.doLayout();
					}
					*/
					account.deletedRegistriesSelected = false;
				}
				
				owner.setMenuActions(owner.AccountGrid, RegistryStatus_id, RegistryType_id);
				
				owner.AccountGrid.getAction('action_yearfilter').setHidden( RegistryStatus_id != 4 );
				if( 4 == RegistryStatus_id ) {
					owner.constructYearsMenu({RegistryType_id: RegistryType_id, RegistryStatus_id: RegistryStatus_id, Lpu_id: Lpu_id});
				}
				
				owner.AccountGrid.loadData({params:{RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, Lpu_id:Lpu_id}, globalFilters:{RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, Lpu_id:Lpu_id}});
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
			Ext.Msg.alert(lang['oshibka'], 
				lang['pri_otpravke_zaprosa_k_serveru_proizoshla_oshibka']+
				lang['poprobuyte_obnovit_stranitsu_najav_klavishi_ctrl+r']+
				lang['esli_oshibka_povtoritsya_-_obratites_k_razrabotchikam']);
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
				msg: lang['vash_zapros_na_formirovanie_reestra_nahoditsya_v_ocheredi']+
				'Позиция вашего запроса в очереди на формирование: <b>'+RegistryQueue_Position+'</b> место.<br/>',
				//+'Для того, чтобы перечитать позицию в очереди нажмите "Да",<br/>'+
				//'для закрытия формы реестров, нажмите "Нет".',
				title: lang['soobschenie']
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
		
		var unchekedImg = '<span class="flag-off16" style="padding-left: 16px; padding-top: 2px;" title="Контроль не производился" /></span>';
		var chekedImg = '<span class="flag-ok16" style="padding-left: 16px; padding-top: 2px;" title="Контроль проведён, ошибок нет" /></span>';
		var warningImg = '<span class="flag-warn16" style="padding-left: 16px; padding-top: 2px;" title="Контроль проведен, обнаружены ошибки" /></span>';
		
		var flkImage = unchekedImg;
		var bdzImage = unchekedImg;
		var mekImage = unchekedImg;
		
		if (record.get('FlkErrors_IsData') && record.get('FlkErrors_IsData') == 1) {
			flkImage = warningImg;
		} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([2,3,4,6,7,8])) {
			flkImage = chekedImg;
		}

		if (record.get('BdzErrors_IsData')  && record.get('BdzErrors_IsData') == 1) {
			bdzImage = warningImg;
		} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([3,6,7,8])) {
			bdzImage = chekedImg;
		}

		if (record.get('MekErrors_IsData') && record.get('MekErrors_IsData') == 1) {
			mekImage = warningImg;
		} else if (record.get('RegistryCheckStatus_Code') && record.get('RegistryCheckStatus_Code').inlist([3,8])) {
			mekImage = chekedImg;
		}
		
		var TFOMSTitle = lang['proydennyie_stadii_proverki_v_tfoms']+' '+flkImage+' '+lang['flk']+' '+bdzImage+' '+lang['bdz']+' '+mekImage+' '+lang['mek'];
		
		if (form.TFOMSHeader.getEl()) {
			form.TFOMSHeader.getEl().update(TFOMSTitle);
		}
		// form.TFOMSErrorGrid.ViewGridPanel.setTitle(TFOMSTitle);

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
                        form.DataGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_commonerr':
					if ((form.ErrorComGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorComGrid.getCount()==0))
					{
						form.ErrorComGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_dataerr':
					form.loadErrTypeSpr(Registry_id);
					var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					var begDate = Ext.util.Format.date(reestr.get('Registry_begDate'),'Y-m-d'); //https://redmine.swan.perm.ru/issues/51050
					var endDate = Ext.util.Format.date(reestr.get('Registry_endDate'),'Y-m-d');
					form.RegistryErrorFiltersPanel.getForm().findField('MedPersonal_id').getStore().load({
						params: {
							Lpu_id: getGlobalOptions().lpu_id,
							begDate: begDate,
							endDate: endDate,
							fromRegistryViewForm: 2
						}
					});
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
				case 'tab_datanopay':
					if ((form.NoPayGrid.getParam('Registry_id')!=Registry_id) || (form.NoPayGrid.getCount()==0))
					{
						form.NoPayGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datapersonerr':
					if ((form.PersonErrorGrid.getParam('Registry_id')!=Registry_id) || (form.PersonErrorGrid.getCount()==0))
					{
						form.PersonErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
								form.BDZErrorGrid.setHeight(form.BDZErrorGrid.ownerCt.getEl().getHeight()-30);
							}
							form.TFOMSErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					if ((form.BDZErrorGrid.getParam('Registry_id')!=Registry_id) || (form.BDZErrorGrid.getCount()==0))
					{
						form.BDZErrorGrid.loadData({callback: function() {
							// если записей нет, нужно грид скрыть
							if (form.BDZErrorGrid.getGrid().getStore().getCount() > 0) {
								form.BDZErrorGrid.show();
								filter_form.findField('RegistryErrorBDZType_id').showContainer();
							} else {
								form.BDZErrorGrid.hide();
								filter_form.findField('RegistryErrorBDZType_id').hideContainer();
							}
							form.BDZErrorGrid.ownerCt.doLayout();
						}, globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_datavizitdouble':
					if ((form.DoubleVizitGrid.getParam('Registry_id')!=Registry_id) || (form.DoubleVizitGrid.getCount()==0))
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
				case 'tab_datanopay':
					form.NoPayGrid.removeAll(true);
					break;
				case 'tab_datapersonerr':
					form.PersonErrorGrid.removeAll(true);
					break;
				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.removeAll(true);
					form.BDZErrorGrid.removeAll(true);
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
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_ni_odna_zapis_v_reestre']);
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
			var msg = lang['vyi_deystvitelno_hotite_udalit_vyibrannuyu_zapis_iz_reestra']+
					 '<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данная запись пометится как удаленная <br/>'+
					 'и будет удалена из реестра при выгрузке (отправке) реестра.<br/>'+
					 'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
		} else {
			var msg = lang['hotite_vosstanovit_pomechennuyu_na_udalenie_zapis'];
		}
		
		if (deleteAll) {
			msg = lang['vyi_deystvitelno_hotite_udalit_vse_zapisi_po_oshibkam_iz_reestra']+
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
			title: lang['vopros']
		});
	},
	deleteRegistryQueue: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
			msg: lang['udalit_tekuschiy_reestr_iz_ocheredi_na_formirovanie'],
			title: lang['vopros']
		});
	},
	registryRevive: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(true); // "Снять активность"
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
			msg: lang['vnimanie_avtomaticheskaya_otpravka_reestrov_v_pkfoms_vremenno_ne_rabotaet'],
			title: lang['otpravka_reestrov']
		});
		return false;
				
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
							msg: lang['reestr_uspeshno_vyigrujen_i_otpravlen'],
							title: ''
						});
						return true;
					}
				}
				
				sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.ERROR,
					msg: lang['pri_formirovanii_ili_otpravke_reestra_proizoshla_oshibka_n_r']+r,
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
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
								msg: lang['status_reestra_proveden_kontrol_flk_vyi_uverenyi_chto_hotite_povtorono_otpravit_ego_v_tfoms'],
								title: lang['podtverjdenie']
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
								msg: lang['fayl_reestra_suschestvuet_na_servere_esli_vyi_hotite_sformirovat_novyiy_fayl_vyiberete_da_esli_hotite_skachat_fayl_s_servera_najmite_net'],
								title: lang['podtverjdenie']
							});
							
							return false;
						}
						
						if (result.Error_Code && result.Error_Code == '12') { // Неверная сумма по счёту и реестрам.
							// обновить форму
							form.AccountGrid.loadData();
						}
						
						if (result.Error_Msg)
							r = result.Error_Msg;
							
						var defmsg = lang['pri_formirovanii_otpravke_reestra_proizoshla_oshibka'];
						
						if (result.WithoutDefaultMsg)
							defmsg = '';
							
						/*sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: lang['reestr_uspeshno_vyigrujen_i_otpravlen'],
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
						record.set('RegistryCheckStatus_Code',0);
						record.set('RegistryCheckStatus_Name',"<a href='#' onClick='getWnd(\"swRegistryCheckStatusHistoryWindow\").show({Registry_id:"+record.get('Registry_id')+"});'>Готов к отправке в ТФОМС</a>");
						record.commit();
						form.overwriteRegistryTpl(record);
						// TODO: скорее всего правильнее будет все же обновить
						//this.AccountGrid.loadData();
						return true;
					}
				}
			}
		});
	},
	/** +lang['pereschet']+ +lang['reestra']+
	 *
	 */
	refreshRegistry: function() 
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
			msg: lang['hotite_udalit_iz_reestra_vse_pomechennyie_na_udalenie_zapisi_i_pereschitat_summyi'],
			title: lang['vopros']
		});
		
	},
	exportRegistry: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
			return false;
		}
		
		if (record.get('Registry_IsNeedReform') == 2) {
			Ext.Msg.alert(lang['oshibka'], lang['reestr_nujdaetsya_v_pereformirovanii_otpravka_i_eksport_ne_vozmojnyi_pereformiruyte_reestr_i_povtorite_deystvie']);
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
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
			return false;
		}
		if(record.get('Registry_IsProgress')==1){
			Ext.Msg.alert(lang['oshibka'], lang['reestr_pereformirovyivaetsya_otpravka_i_eksport_ne_vozmojnyi_dojdites_pereformirovaniya_i_povtorite_deystvie']);
			return false;
		}
		if (record.get('Registry_Count') == 0 && !isSuperAdmin())
		{
			Ext.Msg.alert(lang['oshibka'], lang['eksport_reestra_nevozmojen_net_sluchaev_dlya_eksporta']);
			return false;
		}
		
		if (record.get('Registry_IsNeedReform') == 2) {
			Ext.Msg.alert(lang['oshibka'], lang['reestr_nujdaetsya_v_pereformirovanii_otpravka_i_eksport_ne_vozmojnyi_pereformiruyte_reestr_i_povtorite_deystvie']);
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
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_pereformirovanie_reestra'] });
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
							Ext.Msg.alert(lang['oshibka'], lang['vo_vremya_pereformirovaniya_proizoshla_oshibka'] + result.Error_Msg);
						}
						*/
						
					}
					else
					{
						Ext.Msg.alert(lang['oshibka'], lang['vo_vremya_pereformirovaniya_proizoshla_oshibka']);
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
			var loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_postanovka_reestra_v_ochered_dlya_pereformirovaniya_po_oshibkami'] });
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
						Ext.Msg.alert(lang['oshibka'], lang['vo_vremya_postanovki_v_ochered_na_pereformirovanie_proizoshla_oshibka']);
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
			text:lang['deystviya'],
			iconCls: 'actions16',
			menu: this.menu
		});
		switch (RegistryStatus_id)
		{
			case 6: 
				// Удаленные 
				menu = 
				[{
					text: lang['vosstanovit'],
					tooltip: lang['vosstanovit_udalennyiy_reestr'],
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
								msg: lang['vyi_deystvitelno_hotite_vosstanovit_vyibrannyiy_reestr'],
								title: lang['vosstanovlenie_reestra']
							}
						);
					}
				}]; 
				break;
				
			case 5: 
				// В очереди 
				menu = 
				[{
					text: lang['udalit_reestr_iz_ocheredi'],
					tooltip: lang['udalit_reestr_iz_ocheredi'],
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
					text: lang['otmetit_k_oplate'],
					tooltip: lang['otmetit_k_oplate'],
					handler: function() 
					{
						form.setRegistryStatus(2);
					}
				}];
				if (RegistryType_id==2 && false) // (убрал refs #13862)
				{
					menu[1] = 
					{
						text: lang['pereformirovat_po_oshibkam'],
						tooltip: lang['pereformirovat_reestr_po_oshibkam'],
						handler: function() 
						{
							var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
							if (!record || !(record.get('Registry_id') > 0) )
							{
								Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
					text: lang['pereformirovat_ves_reestr'],
					tooltip: lang['pereformirovat_ves_reestr'],
					handler: function() 
					{
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
						if (!record || !(record.get('Registry_id') > 0) )
						{
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
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
				menu[4] = {
					text: lang['eksport_v_dbf'],
					tooltip: lang['eksport_v_dbf'],
					id: 'regvExportToDbf',
					hidden: !(getGlobalOptions().region.nick == 'perm'/* && RegistryType_id.inlist(['1','2','14'])*/),
					handler: function()
					{
						form.exportRegistry();
					}
				};
				menu[5] = {
					text: lang['eksport_v_xml'],
					tooltip: lang['eksport_v_xml'],
					handler: function() 
					{
						form.exportRegistryToXml();
					}
				};
				menu[6] = '-';
				menu[7] =  {
					text: lang['pereschitat_reestr'],
					tooltip: lang['pereschitat_reestr'],
					handler: function() 
					{
						form.refreshRegistry();
					}
				};
				break;
			case 2: // К оплате
				menu = 
				[{
					text: lang['eksport_v_dbf'],
					tooltip: lang['eksport_v_dbf'],
					id: 'regvExportToDbf',
					hidden: !(getGlobalOptions().region.nick == 'perm'/* && RegistryType_id.inlist(['1','2','14'])*/),
					handler: function() 
					{
						form.exportRegistry();
					}
				},
				{
					text: lang['eksport_v_xml'],
					tooltip: lang['eksport_v_xml'],
					handler: function() 
					{
						form.exportRegistryToXml();
					}
				},
				{
					text: lang['otpravit_v_tfoms'],
					tooltip: lang['eksport_v_xml_i_otpravka_v_tfoms'],
					hidden: (!RegistryType_id.inlist(['1','2','6','7','8','9','10','11','12','14','15'])),
					handler: function() 
					{
					
						var form = Ext.getCmp('RegistryViewWindow');
						var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();

						if (!record)
						{
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
							return false;
						}
						
						if (record.get('Registry_Count') == 0 && !isSuperAdmin())
						{
							Ext.Msg.alert(lang['oshibka'], lang['otpravka_reestra_v_tfoms_nevozmojna_net_sluchaev_dlya_eksporta']);
							return false;
						}
						
						if (record.get('Registry_IsNeedReform') == 2) {
							Ext.Msg.alert(lang['oshibka'], lang['reestr_nujdaetsya_v_pereformirovanii_otpravka_i_eksport_ne_vozmojnyi_pereformiruyte_reestr_i_povtorite_deystvie']);
							return false;		
						}
						
						sw.swMsg.show(
						{
							buttons: Ext.Msg.YESNO,
							scope : Ext.getCmp('RegistryViewWindow'),
							fn: function(buttonId) 
							{
								if (buttonId=='yes')
									form.createAndSendXML();
							},
							icon: Ext.Msg.QUESTION,
							msg: lang['sformirovat_fayl_i_otpravit_v_tfoms'],
							title: lang['vopros']
						});
					}
				},
				'-',
				{
					text: lang['pereschitat_reestr'],
					tooltip: lang['pereschitat_reestr'],
					handler: function() 
					{
						form.refreshRegistry();
					}
				},
				'-',
				{
					text: lang['snyat_otmetku_k_oplate'],
					tooltip: lang['snyat_otmetku_k_oplate'],
					handler: function() 
					{
						form.setRegistryStatus(3);
					}
				},
				{
					text: lang['otmetit_kak_oplachennyiy'],
					hidden: true,
					tooltip: lang['otmetit_kak_oplachennyiy'],
					handler: function() 
					{
						form.setRegistryStatus(4);
					}
				}];
				break;
			case 4: // Оплаченные 
				menu = 
				[{
					text: lang['snyat_aktivnost'],
					tooltip: lang['snyat_aktivnost'],
                    hidden: getRegionNick() == 'perm',
					handler: function() 
					{
						form.setRegistryActive();
					}
				},
				{
					text: lang['snyat_otmetku_oplachen'],
					hidden: true,
					tooltip: lang['snyat_otmetku_oplachen'],
					handler: function() 
					{
						form.setRegistryStatus(2);
					}
				},
				{
					text: lang['eksport_v_dbf'],
					tooltip: lang['eksport_v_dbf'],
					id: 'regvExportToDbf',
                    hidden: !(getGlobalOptions().region.nick == 'perm'/* && RegistryType_id.inlist(['1','2','14'])*/),
					handler: function() 
					{
						form.exportRegistry();
					}
				}, {
					text: lang['eksport_v_xml'],
					tooltip: lang['eksport_v_xml'],
					handler: function() 
					{
						form.exportRegistryToXml();
					}
				}];
				break;
			default:
				Ext.Msg.alert(lang['oshibka'], lang['znachenie_statusa_neizvestno_znachenie_statusa'] + RegistryStatus_id);
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
			text: lang['eksport_protokolov'],
			tooltip: lang['eksport_protokolov'],
			handler: function() 
			{
				var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				if (!reestr)
				{
					Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_ni_odna_zapis_v_reestre']);
					return false;
				}
				var Registry_id = reestr.get('Registry_id');
				var RegistryErrorTFOMSType_id = reestr.get('RegistryErrorTFOMSType_id');
				getWnd('swRegistryErrorExportWindow').show({Registry_id: Registry_id, RegistryErrorTFOMSType_id :RegistryErrorTFOMSType_id});
			}
		});
		return true;
	},
	changePerson: function() {
		if ( !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') || (!isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin()) ) {
			return false;
		}

		var form = this;
		var grid = this.DataGrid.getGrid();

		var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();

		if ( !reestr || !reestr.get('Registry_id') ) {
			sw.swMsg.alert(lang['oshibka'], lang['reestr_ne_vyibran']);
			return false;
		}
		else if ( !reestr.get('RegistryType_id').toString().inlist([ '1', '2', '6', '7', '8', '9', '10', '11', '12', '14', '15' ]) ) {
			sw.swMsg.alert(lang['oshibka'], lang['funktsiya_dostupna_tolko_dlya_reestrov_polikliniki_statsionara_dd_i_skoroy_pomoschi']);
			return false;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		else if ( reestr.get('RegistryType_id').toString().inlist([ '1', '2', '7', '8', '9', '10', '11', '12', '14', '15' ]) && !grid.getSelectionModel().getSelected().get('Evn_rid') ) {
			return false;
		}
		else if ( reestr.get('RegistryType_id').toString().inlist([ '6' ]) && !grid.getSelectionModel().getSelected().get('Evn_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		var params = new Object();

		switch ( reestr.get('RegistryType_id').toString() ) {
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
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_pereoformlenii_dokumenta_na_drugogo_cheloveka']);
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
							title: lang['vopros']
						});
					}
					else {
						getWnd('swPersonSearchWindow').hide();
                        var info_msg = lang['dokument_uspeshno_pereoformlen_na_drugogo_cheloveka'];
                        if (response_obj.Info_Msg) {
                            info_msg += '<br>' + response_obj.Info_Msg;
                        }
                        sw.swMsg.alert(lang['soobschenie'], info_msg, function() {
							if ( response_obj.Registry_IsNeedReform == 2 ) {
								form.AccountGrid.getGrid().getStore().reload();
								grid.getStore().reload();
							}
						});
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_pereoformlenii_dokumenta_na_drugogo_cheloveka_proizoshli_oshibki']);
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
		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
			return false;
		}

		var record = Ext.getCmp('RegistryViewWindow').DataGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
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
		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
			return false;
		}

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
			msg: lang['vyi_hotite_udalit_dannyie_dlya_reestra_po_cheloveku'],
			title: lang['podtverjdenie'],
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
							Ext.Msg.alert(lang['oshibka'], lang['pri_udalenii_proizoshla_oshibka']);
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
										Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
									}
									else
										if (!answer.Error_Msg) // если не автоматически выводится
										{
											Ext.Msg.alert(lang['oshibka'], lang['udalenie_nevozmojno']);
										}
								}
								else 
								{
									Ext.Msg.alert(lang['soobschenie'], lang['dannyie_uspeshno_udalenyi']);
								}
							}
							else
							{
								Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_udalenii_otsutstvuet_otvet_servera']);
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
			msg: lang['vyi_uverenyi_chto_eto_ne_dvoynik'],
			title: lang['vopros']
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
						sw.swMsg.alert(lang['vnimanie'],lang['dlya_obyedineniya_doljnyi_byit_hotya_byi_2_zapisi']);
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
			msg: lang['hotite_obyedinit_vyibrannyih_lyudey'],
			title: lang['vopros']
		});
	},
	/** Открытие информационного окна с технической информацией
	 *
	 */
	openInfoForm: function (object) {
		var record = object.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_ni_odna_zapis_s_oshibkoy_tfoms']);
			return false;
		}
		var msg = 
			lang['imya_polya']+((record.get('RegistryErrorTFOMS_FieldName'))?record.get('RegistryErrorTFOMS_FieldName'):'')+'<br>'+
			lang['bazovyiy_element']+((record.get('RegistryErrorTFOMS_BaseElement'))?record.get('RegistryErrorTFOMS_BaseElement'):'')+'<br>'+
			lang['kommentariy']+((record.get('RegistryErrorTFOMS_Comment'))?record.get('RegistryErrorTFOMS_Comment'):'')+'<br>';
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
			title: lang['tehnicheskie_podrobnosti']
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
			Ext.Msg.alert(lang['oshibka'], lang['oshibka_vyibora_zapisi']);
			return false;
		}
		var RegistryType_id = this.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		
		var type = record.get('RegistryType_id');
		if (!type)
			type = this.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		
		if (object.id == this.id+'Error'  || object.id == this.id+'Data') // Если это с грида "Ошибки данных" или Данные
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
				open_form = 'swCmpCallCardEditWindow';
				key = 'CmpCallCard_id';
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
					Ext.Msg.alert(lang['oshibka'], lang['nevozmojno_opredelit_mesto_rabotyi_vracha_ckoree_vsego_on_uje_ne_rabotaet_v_tom_otdelenii_v_kotorom_byil_vyipolnen_sluchay']);
				return;
				break;
			default:	
				Ext.Msg.alert(lang['oshibka'], lang['vyizyivaemaya_forma_ne_izvestna']);
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
		if (open_form == 'swCmpCallCardEditWindow') { // карты вызова
			params.formParams = Ext.apply(params);
		}
		if (getGlobalOptions().archive_database_enable) {
			if (record.get('archiveRecord')) {
				params.archiveRecord = record.get('archiveRecord');
			} else {
				params.archiveRecord = 0;
			}
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
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: lang['podojdite']});
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
			msg = lang['vyi_deystvitelno_hotite_udalit'];
		if( !rec && mode == 'current' ) return false;
		
		switch(mode) {
			case 'current':
				msg += lang['vyibrannuyu_zapis'];
				break;
			case 'all':
				msg += lang['vse_zapisi'];
				break;
			default:
				return false;
				break;
		}
		
		Ext.Msg.show({
			title: lang['vnimanie'],
			msg: msg,
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie']).show();
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
								parAct.setText(lang['filtr_po_godu'] + this.getText() + '</b>');
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
			Registry_IsRepeated: (record.get('Registry_IsRepeated') == 2 ? lang['da'] : lang['net']),
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
		
							
		form.RegistryTpl.overwrite(form.RegistryPanel.body, sparams);
	},
	printRegistryError: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
			return false;
		}
		var Registry_id = record.get('Registry_id');
		if ( !Registry_id )
			return false;
		var id_salt = Math.random();
		var win_id = 'print_registryerror' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=Registry&m=printRegistryError&Registry_id=' + Registry_id, win_id);
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
				text: lang['reestryi'],
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
			id: form.id+'Account',
			region: 'north',
			height: 203,
			title:lang['schet'],
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
				{name: 'KatNasel_id', type: 'int', hidden: true},
				{name: 'EvnClass_id', type: 'int', hidden: true},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'Registry_Num', header: lang['nomer_scheta'], width: 80},
				{name: 'Registry_accDate', type:'date', header: lang['data_scheta'], width: 80},
				{name: 'ReformTime',hidden: true},
				{name: 'Registry_begDate', type:'date', header: lang['nachalo_perioda'], width: 100},
				{name: 'Registry_endDate', type:'date', header: lang['okonchanie_perioda'], width: 110},
				{name: 'Registry_Count', type: 'int', header: lang['kolichestvo'], width: 80},
				{name: 'Registry_RecordPaidCount', type: 'int', hidden: true},
				{name: 'Registry_KdCount', type: 'int', hidden: true},
				{name: 'Registry_KdPaidCount', type: 'int', hidden: true},
				{name: 'Registry_Sum', type:'money', header: lang['itogovaya_summa'], width: 100},
				{name: 'Registry_SumPaid', type:'money', header: lang['summa_k_oplate'], width: 100},
				{name: 'KatNasel_Name', header: lang['kategoriya_naseleniya'], width: 130},
				{name: 'KatNasel_SysNick', type: 'string', hidden: true},
				{name: 'PayType_Name', header: lang['vid_oplatyi'], width: 80},
				{name: 'DispClass_Name', header: lang['tip_disp_medosmotra'], width: 140},
				{name: 'LpuBuilding_Name', header: lang['podrazdelenie'], width: 120},
				{name: 'RegistryStacType_Name', header: lang['tip_stats'], width: 140},
				{name: 'Registry_updDate', header: lang['data_izmeneniya'], width: 110},
				{name: 'Registry_sendDate', header: lang['poslednyaya_otpravka'], width: 110},
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
				{name: 'RegistryCheckStatus_Name', header: lang['status'], width: 200},
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
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_schet_registr']);
							return false;
						}
						var Registry_id = record.get('Registry_id');
						if ( !Registry_id )
							return false;
						var id_salt = Math.random();
						var win_id = 'print_schet' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=Registry&m=printRegistry&Registry_id=' + Registry_id, win_id);
					}, 
					text: lang['pechat_scheta']
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
					
					// Убрать кнопку Печать счета иногородним в полке и стаце (refs #1595) ЗАКОММЕНТИЛ В РАМКАХ ЗАДАЧИ  26006
					/*if (RegistryType_id.inlist([1,2]) && record.get('KatNasel_id') == 2) {
						this.setActionDisabled('action_print', true);
					} else {
						this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12]))));
					}*/
					this.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12,14,15]))));
					form.onRegistrySelect(Registry_id, RegistryType_id,  false, record);

					this.setActionDisabled('action_edit',
						(!isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())
						|| record.get('RegistryStatus_id') != 3 // #61531
						|| record.get('Registry_IsProgress') == 1
						|| (record.get('RegistryCheckStatus_Code').inlist([8]) && !isAdmin)
					);

					this.setActionDisabled('action_view', record.get('Registry_IsProgress') == 1);

					this.setActionDisabled('action_delete',
						(!isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin())
						|| record.get('RegistryStatus_id') != 3 // #61531
						|| record.get('Registry_IsProgress') == 1
						|| record.get('RegistryCheckStatus_Code').inlist([0,1,3,8]) 
					);

					if ( record.get('RegistryStatus_id') != 5 && record.get('RegistryStatus_id') != 6 ) { // не в очереди и не удален
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(false); // "Отметить к оплате"

						Ext.getCmp('regvExportToDbf').setDisabled(record.get('Registry_IsProgress') == 1);

						// В прогрессе
						if ( record.get('Registry_IsProgress') == 1 ) {
								Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(true); // "Отметить к оплате"
						}
					}

					if (record.get('RegistryStatus_id')==4) // оплаченные
					{
						// разрешить-запретить снятие активности
						//log(form.AccountGrid.getAction('action_new').initialConfig.menu.items.itemAt(0));
						Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setDisabled(!(record.get('Registry_IsActive')==2)); // "Снять активность"
					}
					
					var account = Ext.getCmp('RegistryViewWindowAccount');
					var deletedRegistriesSelected = account.deletedRegistriesSelected;
					
					// Дисаблим акшены по статусу отправки 
					// Если полка или стац
					// Если не в папке удаленных
					if (!deletedRegistriesSelected){
						if ( isUserGroup([ 'RegistryUser' ]) || isSuperAdmin() ) {
							if (record.get('RegistryType_id').inlist(['1','2','6','7','8','9','10','11','12','14','15'])) {
								// Если к оплате 
								if (record.get('RegistryStatus_id')=='2' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setDisabled((record.get('RegistryCheckStatus_Code').inlist([0,1]) && !isAdmin)); // "Экспорт в XML"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setDisabled((record.get('RegistryCheckStatus_Code').inlist([0,1,4]) && !isAdmin));  // "Отправить в ТФОМС"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(4).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin));  // "Пересчитать реестр"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(6).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin));  // "Снять отметку "к оплате""
									/*Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(7).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin)); // "Отметить как оплаченный"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(7).setVisible(isSuperAdmin() || record.get('KatNasel_SysNick') == 'inog'); // "Отметить как оплаченный" // суперадмин или иногородние (refs #12116)*/
									//Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(0).setVisible(!record.get('RegistryType_id').inlist([ '6', '12' ])); // "Экспорт в DBF"
								}
								// Если оплачен
								if (record.get('RegistryStatus_id')=='4' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setVisible(isSuperAdmin());  // "Снять отметку оплачен" // суперадмин или иногородние (refs #12116)
									//Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setVisible(!record.get('RegistryType_id').inlist([ '6', '12' ])); // "Экспорт в DBF"
								}
								// Если в работе
								if (record.get('RegistryStatus_id')=='3' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setDisabled((record.get('RegistryCheckStatus_Code').inlist([0,1]) && !isAdmin)); // "Переформировать по ошибкам"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setDisabled((record.get('RegistryCheckStatus_Code').inlist([0,1]) && !isAdmin)); // "Переформировать весь реестр"
								}
							}
							else {
								// Если к оплате 
								if (record.get('RegistryStatus_id')=='2' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(7)) {
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(7).setVisible(true); // "Отметить как оплаченный"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(2).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin)); // "Отправить в ТФОМС"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(4).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin)); // "Пересчитать реестр"
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(6).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin)); // "Снять отметку "к оплате""
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(7).setDisabled((record.get('RegistryCheckStatus_Code').inlist([4]) && !isAdmin)); // "Отметить как оплаченный"
								}
								// Если оплачен
								if (record.get('RegistryStatus_id')=='4' && Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1)) {
									Ext.menu.MenuMgr.get('RegistryMenu').items.itemAt(1).setVisible(true); // "Снять отметку оплачен"
								}
							}
						}
					}

					form.RegistryPanel.show();

					if ( record.get('RegistryErrorTFOMS_IsData') > 0 || record.get('BdzErrors_IsData') > 0 ) {
						form.menu.items.get('regvRegistryErrorExport').enable();
					} else {
						form.menu.items.get('regvRegistryErrorExport').disable();
					}

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
						case 'tab_datanopay':
							form.NoPayGrid.removeAll(true);
							break;
						case 'tab_datapersonerr':
							form.PersonErrorGrid.removeAll(true);
							break;
						case 'tab_datatfomserr':
							form.TFOMSErrorGrid.removeAll(true);
							form.BDZErrorGrid.removeAll(true);
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
				form.DataTab.getItem('tab_datanopay').setIconClass((record.get('RegistryNoPay_IsData')==1)?'usluga-notok16':'good');
				if ((record.get('RegistryErrorTFOMS_IsData')==1 || record.get('BdzErrors_IsData')==1) && (record.get('RegistryCheckStatus_Code').inlist([3,8]))) {
					form.DataTab.getItem('tab_datatfomserr').setIconClass('usluga-notok16');
				} else if (record.get('RegistryErrorTFOMS_IsData')==1 || record.get('BdzErrors_IsData')==1) {
					form.DataTab.getItem('tab_datatfomserr').setIconClass('delete16');
				} else {
					form.DataTab.getItem('tab_datatfomserr').setIconClass('good');
				}
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
			'<div style="padding:2px;font-size: 12px;">Повторная подача: {Registry_IsRepeated}</div>'
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
						fieldLabel: lang['familiya'],
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
						fieldLabel: lang['imya'],
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
						fieldLabel: lang['otchestvo'],
						id: 'rvwDGPerson_SecName',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex:form.firstTabIndex+12
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
						fieldLabel: lang['nomer_polisa'],
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
						fieldLabel: lang['id_sluchaya'],
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
						boxLabel: lang['vse_sluchai'],
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, lang['vse_sluchai']],
							[2, lang['oplachennyie_sluchai']],
							[3, lang['neoplachennyie_sluchai']]
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
					items: [rvwDGBtnSearch]
				}, {
					layout: 'form',
					border: false,
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
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
			listeners: {
				render: function() {
					this.getForm().findField('MedPersonal_id').getStore().load({
						params: {Lpu_id: getGlobalOptions().lpu_id}
					});
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
						fieldLabel: lang['familiya'],
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
						fieldLabel: lang['imya'],
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
						fieldLabel: lang['otchestvo'],
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
						fieldLabel: lang['oshibka'],
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
						fieldLabel: lang['id_sluchaya'],
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
			title:lang['reestr_oms'],
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
			stringfields:
			[
				{name: 'Evn_id', type: 'int', header: lang['id_sluchaya'], key: true, hidden:false},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', hidden:true},
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
				{name: 'EvnPL_NumCard', header: lang['№_talona'], width: 60},
				{name: 'Person_FIO', id: 'autoexpand', header: lang['fio_patsienta']},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 80},
				{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: lang['otdelenie'], width: 200},
				{name: 'MedPersonal_Fio', header: lang['vrach'], width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: lang['poseschenie'], width: 80},
				{name: 'Evn_disDate', type: 'date', header: lang['vyipiska'], width: 80},
				{name: 'RegistryData_Uet', header: lang['k_d_fakt'], width: 70},
				{name: 'RegistryData_KdPlan', header: lang['k_d_normativ'], width: 70},
				{name: 'RegistryData_KdPay', header: lang['k_d_k_oplate'], width: 70},
				{name: 'RegistryData_Tariff', type: 'money', header: lang['tarif'], width: 70},
				{name: 'RegistryData_ItogSum', type: 'money', header: lang['summa_k_oplate'], width: 90},
				{name: 'checkReform', header: '<img src="/img/grid/hourglass.gif" />', width: 35, renderer: sw.Promed.Format.waitColumn},
				//{name: 'timeReform',id:'timeReform', type: 'datetimesec', header: 'Изменена', width: 100},
				{name: 'Err_Count', hidden:true},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'RegistryData_IsPaid', type: 'int', hidden:true}
			],
			actions:
			[
				// https://redmine.swan.perm.ru/issues/12624
				// Для Перми редактирование данных и удаление человека закрыто
				{name:'action_setperson', disabled: (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'), hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'), tooltip: lang['redaktirovat_dannyie_cheloveka_v_reestre'], text: lang['redaktirovat_dannyie_cheloveka'], handler: function() {Ext.getCmp('RegistryViewWindow').setPerson();}},
				{name:'action_delperson', disabled: (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'), hidden: (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'), tooltip: lang['udalit_sozdannyie_dannyie_cheloveka_v_reestre'], text: lang['udalit_dannyie_cheloveka'], handler: function() {Ext.getCmp('RegistryViewWindow').deletePerson();}},
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});}},
				{name:'action_view', disabled: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') || (!isUserGroup([ 'RegistryUser' ]) && !isSuperAdmin()), hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'), text: lang['smenit_patsienta_v_uchetnom_dokumente'], tooltip: lang['izmenit_patsienta_v_uchetnom_dokumente'], icon: 'img/icons/doubles16.png', handler: function() { Ext.getCmp('RegistryViewWindow').changePerson(); } },
				{name:'action_delete', disabled: false, handler: function() { form.deleteRegistryData(form.DataGrid, false); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: lang['otkryit_dannyie_cheloveka'], text: lang['otkryit_dannyie_cheloveka'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {}, 'OpenPerson');}}
				
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
				if (isAdmin)
				{
					this.setActionDisabled('action_setperson',(this.getCount()==0 || !(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())));
					this.setActionDisabled('action_delperson',(!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())));
				}
				var RegistryStatus_id = Ext.getCmp('RegistryViewWindowRegistryTree').selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3 || !(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())));
				this.setActionDisabled('action_edit',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
				this.setActionDisabled('action_view',!(isUserGroup([ 'RegistryUser' ]) || isSuperAdmin()));
			},
			onDblClick: function()
			{
				if (isAdmin && getGlobalOptions().region && getGlobalOptions().region.nick != 'perm' ) // для Перми закрыт setPerson (?)
					Ext.getCmp('RegistryViewWindow').setPerson();
				else 
					Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});
			},
			onEnter: function()
			{
				if (isAdmin && getGlobalOptions().region && getGlobalOptions().region.nick != 'perm' ) // для Перми закрыт setPerson (?)
					Ext.getCmp('RegistryViewWindow').setPerson();
				else 
					Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataGrid, {});
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				var form = this;

				// https://redmine.swan.perm.ru/issues/12624
				// Добавлено условие для Перми
				if ( isAdmin && !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm') )
				{
					form.DataGrid.setActionDisabled('action_setperson',(form.DataGrid.getCount()==0));
					form.DataGrid.setActionDisabled('action_delperson',(record.get('IsRDL')==0));
				}
				// Меняем текст акшена удаления в зависимости от данных
				form.DataGrid.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit']:lang['udalit']);

				var reestr = form.AccountGrid.getGrid().getSelectionModel().getSelected();

				if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' && (isUserGroup([ 'RegistryUser' ]) || isSuperAdmin())
					&& reestr && reestr.get('Registry_id') && reestr.get('RegistryStatus_id') == 3
					&& reestr.get('RegistryType_id').toString().inlist([ '1', '2', '6', '7', '8', '9', '10', '11', '12', '14', '15' ])
				) {
					form.DataGrid.setActionDisabled('action_view', false);
				}
				else {
					form.DataGrid.setActionDisabled('action_view', true);
				}
			}.createDelegate(this)
		});
		
		this.DataGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				
				if ((row.get('IsRDL')>0) && (isAdmin))
					cls = cls+'x-grid-rowblue ';
				if (row.get('Err_Count') > 0 || row.get('RegistryData_IsPaid') == 1)
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
			title:lang['obschie_oshibki'],
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
				{name: 'RegistryErrorType_Code', header: lang['kod']},
				{name: 'RegistryErrorType_Name', id: 'autoexpand', header: lang['naimenovanie']},
				{name: 'RegistryErrorType_Descr', header: lang['opisanie'], width: 250},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: lang['tip']}
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
			title:lang['oshibki_dannyih'],
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
				{name: 'Evn_id', header:lang['id_sluchaya'], hidden:false},
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
				{name: 'RegistryErrorType_Code', header: lang['kod']},
				{name: 'RegistryErrorType_Name', header: lang['naimenovanie'], width: 200},
				{name: 'RegistryErrorType_Descr', header: lang['opisanie'], width: 250},
				{name: 'RegistryError_Desc', header: lang['kommentariy'], width: 250},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'LpuSection_id', type: 'int', hidden:true},
				{name: 'LpuUnit_id', type: 'int', hidden:true},
				{name: 'MedStaffFact_id', type: 'int', hidden:true},
				{name: 'MedPersonal_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: lang['fio_patsienta']},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30},
				{name: 'LpuSection_name', header: lang['otdelenie'], width: 200},
				{name: 'MedPersonal_Fio', header: lang['vrach'], width: 200},
				{name: 'Evn_setDate', type:'date', header: lang['nachalo'], width: 70},
				{name: 'Evn_disDate', type:'date', header: lang['okonchanie'], width: 70},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: lang['tip']},
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
				{name:'action_edit', text: lang['ispravit'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: lang['udalit_sluchay_iz_reestra'], handler: function() { form.deleteRegistryData(form.ErrorGrid, false); }},
				{name:'action_print', text:lang['pechatat_tekuschuyu_stranitsu']},
				{name:'action_printall', text:lang['pechatat_ves_spisok'], tooltip: lang['pechatat_ves_spisok'], icon: 'img/icons/print16.png', handler: function() { form.printRegistryError(); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: lang['udalit_sluchai_po_vsem_oshibkam'], handler: function() { form.deleteRegistryData(form.ErrorGrid, true); }},
				{name:'action_openevn', visible: !isAdmin, tooltip: lang['otkryit_uchetnyiy_dokument'], icon: 'img/icons/pol-eplstream16.png',  text: lang['otkryit_uchetnyiy_dokument'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: lang['otkryit_dannyie_cheloveka'], text: lang['otkryit_dannyie_cheloveka'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenPerson');}}
				
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
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit_sluchay_v_reeestre']:lang['udalit_sluchay_iz_reestra']);
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit_sluchai_po_vsem_oshibkam']:lang['udalit_sluchai_po_vsem_oshibkam']);
			},
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3||!(isUserGroup([ 'RegistryUser' ])||isSuperAdmin())));
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
			title:lang['nezastrahovannyie'],
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
				{name: 'Person_FIO', id: 'autoexpand', header: lang['fio_patsienta']},
				{name: 'LpuSection_Name', header: lang['otdelenie'], width: 150},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90}
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
		
		
		// 5. Случаи без оплаты
		this.NoPayGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'NoPay',
			title:lang['sluchai_bez_oplatyi'],
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
				{name: 'Person_FIO', id: 'autoexpand', header: lang['fio_patsienta']},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'LpuSection_Name', header: lang['otdelenie'], width: 150},
				//{name: 'Evn_setDate',  type: 'date', header: 'Посещение', width: 150},
				//{name: 'Evn_disDate',  type: 'date', header: 'Выписка', width: 150},
				{name: 'RegistryNoPay_KdFact', header: lang['k_d_fakt'], width: 70},
				{name: 'RegistryNoPay_KdPlan', header: lang['k_d_normativ'], width: 70},
				{name: 'RegistryNoPay_KdPay', header: lang['k_d_k_oplate'], width: 70},
				{name: 'RegistryNoPay_Tariff', type: 'money', header: lang['tarif'], width: 70},
				{name: 'RegistryNoPay_UKLSum', type: 'money', header: lang['summa'], width: 90}
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
		});
		
		// 6. Ошибки перс/данных
		this.PersonErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'PersonError',
			title:lang['oshibki_personalnyih_dannyih'],
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
				{name: 'Person_SurName', id: 'autoexpand', header: lang['familiya']},
				{name: 'Person_FirName', header: lang['imya'], width: 180},
				{name: 'Person_SecName', header: lang['otchestvo'], width: 180},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'Person_Polis',  header: lang['seriya_№_polisa'], width: 130},
				{name: 'Person_PolisDate',  header: lang['period_deystviya_polisa'], width: 150},
				{name: 'Person_EvnDate',  header: lang['period_lecheniya'], width: 150},
				{name: 'Person_OrgSmo',  header: lang['smo'], width: 180}, 
				{name: 'isNoEdit', hidden:true} 
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true },
				{name:'action_edit', text: lang['obyedinit'], handler: function() { form.doPersonUnion(form.PersonErrorGrid, {});}},
				{name:'action_view', text: lang['eto_ne_dvoynik'], handler: function() { form.doRegistryPersonIsDifferent(form.PersonErrorGrid, {});}},
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
				
				form.BDZErrorGrid.loadData(
				{
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
		
		this.TFOMSHeader = new Ext.form.Label({
			html: ""
		})
		
		this.RegistryTFOMSFiltersPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 55,
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
				defaults: {bodyStyle:'padding-left: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 40,
					items: 
					[{
						anchor: '100%',
						fieldLabel: lang['fio'],
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
						fieldLabel: lang['oshibka'],
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
						fieldLabel: lang['id_sluchaya'],
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
						fieldLabel: lang['oshibki_bdz'],
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
								[1, lang['ne_identifitsirovannyie']],
								[2, lang['identifitsirovannyie']],
								[3, lang['vse']]
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
					items: [rvwTFOMSBtnSearch]
				}]
			}]
		});

		// Ошибки БДЗ
		this.BDZErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'BDZError',
			title:lang['oshibki_stadii_raspredeleniya_po_smo_bdz'],
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
                {name: 'Person_id', isparams: true, type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Person2_id', isparams: true, type: 'int', header: 'Person2_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'RegistryError_Comment', header: lang['opisanie_oshibki'], autoexpand: true},
				{name: 'RegistryErrorBDZ_Comment', header: lang['kommentariy'], width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: lang['fio_patsienta'], width: 250},
				{name: 'Person_BirthDay', header: lang['data_rojdeniya'], width: 90},
				{name: 'Person_Polis',  header: lang['seriya_№_polisa'], width: 130},
				// {name: 'Person_PolisDate',  header: 'Период действия полиса', width: 150},
				{name: 'Person_EvnDate',  header: lang['period_lecheniya'], width: 150},
				{name: 'Person_OrgSmo',  header: lang['smo'], width: 180}, 
				{name: 'RegistryErrorBDZ_FieldName', hidden:true},
				{name: 'RegistryErrorBDZ_BaseElement', hidden:true}
				/*,
				{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30}
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
				{name:'action_edit', text: lang['obyedinit'], hidden: true, handler: function() {/*Ext.getCmp('RegistryViewWindow').doPersonUnion(Ext.getCmp('RegistryViewWindow').BDZErrorGrid, {});*/}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: lang['udalit_sluchay_iz_reestra'], handler: function() { form.deleteRegistryData(form.BDZErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: lang['udalit_sluchai_po_vsem_oshibkam'], handler: function() { form.deleteRegistryData(form.BDZErrorGrid, true); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: lang['otkryit_dannyie_cheloveka'], text: lang['otkryit_dannyie_cheloveka'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenPerson');}}
			],
			listeners: {
				'resize': function() {
					if (form.TFOMSErrorGrid.hidden) {
						form.BDZErrorGrid.setHeight(form.BDZErrorGrid.ownerCt.getEl().getHeight()-30);
					}
				}
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
				//form.setNeedReform(record);
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit_sluchay_v_reeestre']:lang['udalit_sluchay_iz_reestra']);
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit_sluchai_po_vsem_oshibkam']:lang['udalit_sluchai_po_vsem_oshibkam']);
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
		
		// Ошибки ТФОМС
		this.TFOMSErrorGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'TFOMSError',
			title:lang['oshibki_stadiy_flk_i_mek'],
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
			stringfields:
			[
				{name: 'RegistryErrorTFOMS_id', type: 'int', header: 'ID', key: true},
				{name: 'Evn_id', type: 'int', header: lang['id_sluchaya'], hidden:false},
				{name: 'RegistryData_deleted', type: 'int', hidden:true},
				{name: 'RegistryData_notexist', type: 'int', hidden:true},
                {name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
                {name: 'Evn_rid', type: 'int', hidden:true},
				{name: 'EvnClass_id', type: 'int', hidden:true},
				{name: 'DispClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorType_Code', header: lang['kod_oshibki'], width: 80},
				{name: 'RegistryErrorTFOMSLevel_Name', header: lang['uroven_oshibki'], width: 120},
				{name: 'RegistryError_FieldName', header: lang['oshibka'], width: 250},
				{name: 'RegistryError_Comment', header: lang['opisanie_oshibki'], autoexpand: true},
				{name: 'RegistryErrorTFOMS_Comment', header: lang['kommentariy'], width: 200},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},

				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'PersonEvn_id', type: 'int', hidden:true},
				{name: 'Person_FIO', header: lang['fio_patsienta'], width: 250},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'RegistryErrorTFOMS_FieldName', hidden:true},
				{name: 'RegistryErrorTFOMS_BaseElement', hidden:true}
				/*,
				{name: 'Person_IsBDZ',  header: lang['bdz'], type: 'checkbox', width: 30}
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
				{name:'action_edit', text: lang['ispravit'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {});}},
				{name:'action_view', disabled: true, hidden: true },
				{name:'action_delete', text: lang['udalit_sluchay_iz_reestra'], handler: function() { form.deleteRegistryData(form.TFOMSErrorGrid, false); }},
				{name:'-'},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: lang['udalit_sluchai_po_vsem_oshibkam'], handler: function() { form.deleteRegistryData(form.TFOMSErrorGrid, true); }},
				{name:'action_openevn', disabled: true, visible: !isAdmin, tooltip: lang['otkryit_uchetnyiy_dokument'], icon: 'img/icons/pol-eplstream16.png',  text: lang['otkryit_uchetnyiy_dokument'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenEvn');}},
				{name:'action_openperson', disabled: true, visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: lang['otkryit_dannyie_cheloveka'], text: lang['otkryit_dannyie_cheloveka'], handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenPerson');}},
				{name:'-', visible: !isAdmin},
				{name:'action_tehinfo', disabled: true, visible: true, icon: 'img/icons/info16.png', tooltip: lang['tehnicheskie_podrobnosti'], text: lang['tehnicheskie_podrobnosti'], handler: function() {Ext.getCmp('RegistryViewWindow').openInfoForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid)}}
				
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
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit_sluchay_v_reeestre']:lang['udalit_sluchay_iz_reestra']);
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?lang['vosstanovit_sluchai_po_vsem_oshibkam']:lang['udalit_sluchai_po_vsem_oshibkam']);
				
				if (this.getCount()>0) 
				{
					this.setActionDisabled('action_openperson',!isAdmin);
					this.setActionDisabled('action_openevn',!isAdmin);
					this.setActionDisabled('action_tehinfo',false);
				}
			},
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',(RegistryStatus_id!=3));
				this.setActionDisabled('action_deleteall',(RegistryStatus_id!=3));
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
		
		this.DoubleVizitGrid = new sw.Promed.ViewFrame({
			id: form.id+'DoubleVizit',
			title:lang['dubli_posescheniy'],
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
				{name: 'EvnPL_NumCard', header: lang['№_talona'], width: 80},
				{name: 'Person_FIO', id: 'autoexpand', header: lang['fio_patsienta']},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'LpuSection_FullName', header: lang['otdelenie'], width: 200},
				{name: 'MedPersonal_Fio', header: lang['vrach'], width: 250},
				{name: 'EvnVizitPL_setDate', header: lang['data_posescheniya']}
			],
			listeners: {
				render: function(grid) {
					if ( !grid.getAction('action_delete_all') ) {
						var action_delete_all = {
							name:'action_delete_all',
							text:lang['udalit_vse'],
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
				title: lang['0_reestr'],
				layout: 'fit',
				id: 'tab_registry',
				iconCls: 'info16',
				frame: true,
				//header:false,
				border:false,
				items: [form.RegistryPanel]
				},
				{
					title: lang['1_dannyie'],
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
					title: lang['2_obschie_oshibki'],
					layout: 'fit',
					id: 'tab_commonerr',
					iconCls: 'good',
					border:false,
					items: [form.ErrorComGrid]
				},
				{
					title: lang['3_oshibki_dannyih'],
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
					title: lang['4_nezastrahovannyie'],
					layout: 'fit',
					id: 'tab_datanopolis',
					iconCls: 'good',
					border:false,
					items: [form.NoPolisGrid]
				},
				{
					title: lang['5_sluchai_bez_oplatyi'],
					layout: 'fit',
					id: 'tab_datanopay',
					iconCls: 'good',
					border:false,
					items: [form.NoPayGrid]
				},
				{
					title: lang['6_oshibki_pers_dannyih'],
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datapersonerr',
					border:false,
					items: [form.PersonErrorGrid]
				},
				{
					title: lang['7_itogi_proverki_tfoms'],
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
					title: lang['8_dubli_posescheniy'],
					layout: 'fit',
					iconCls: 'good',
					id: 'tab_datavizitdouble',
					border:false,
					items: [form.DoubleVizitGrid]
				}]
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
					xtype: 'panel',
					region: 'center',
					layout:'border',
					id: 'regvRightPanel',
					defaults: {split: true},
					items: [form.AccountGrid, form.DataTab]
				}
			]
		});
		sw.Promed.swRegistryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});