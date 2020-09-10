/**
* swRegistryViewWindow - окно просмотра и редактирования реестров for Ufa.
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
	Registry_IsNew: null,
	setZNOFilterVisibility: function(node) {
		var win = this;
		var RegistrySubType_id, RegistryType_id;
		var PayType_SysNick = 'oms';

		if ( !node ) {
			node = win.Tree.selModel.selNode;
		}

		if ( !node ) {
			return false;
		}
		else if ( node.id.indexOf('PayType.1.bud') >= 0 ) {
			PayType_SysNick = 'bud';
		}

		var level = node.getDepth();

		if ( PayType_SysNick == 'bud' ) {
			level++;
		}

		if ( level != 5 ) {
			return false;
		}

		RegistrySubType_id = node.parentNode.attributes.object_value;
		RegistryType_id = node.parentNode.parentNode.attributes.object_value;

		switch ( win.DataTab.getActiveTab().id ) {
			case 'tab_data':
				if ( typeof win.RegistryDataFiltersPanel.getForm().findField('filterIsZNO') == 'object' ) {
					win.RegistryDataFiltersPanel.getForm().findField('filterIsZNO').setContainerVisible(PayType_SysNick == 'oms' && RegistrySubType_id == 1 && (RegistryType_id == 1 || RegistryType_id == 2 || RegistryType_id == 6) && win.Registry_IsNew == 2);
				}
				break;

			case 'tab_dataerr':
				if ( typeof win.RegistryErrorFiltersPanel.getForm().findField('filterIsZNO') == 'object' ) {
					win.RegistryErrorFiltersPanel.getForm().findField('filterIsZNO').setContainerVisible(PayType_SysNick == 'oms' && RegistrySubType_id == 1 && (RegistryType_id == 1 || RegistryType_id == 2 || RegistryType_id == 6) && win.Registry_IsNew == 2);
				}
				break;

			case 'tab_datatfomserr':
				if ( typeof win.RegistryTFOMSFiltersPanel.getForm().findField('filterIsZNO') == 'object' ) {
					win.RegistryTFOMSFiltersPanel.getForm().findField('filterIsZNO').setContainerVisible(PayType_SysNick == 'oms' && RegistrySubType_id == 1 && (RegistryType_id == 1 || RegistryType_id == 2 || RegistryType_id == 6) && win.Registry_IsNew == 2);
				}
				break;

			case 'tab_datavizitdouble':
				if ( typeof win.DoubleVizitFiltersPanel.getForm().findField('filterIsZNO') == 'object' ) {
					win.DoubleVizitFiltersPanel.getForm().findField('filterIsZNO').setContainerVisible(PayType_SysNick == 'oms' && RegistrySubType_id == 1 && (RegistryType_id == 1 || RegistryType_id == 2 || RegistryType_id == 6) && win.Registry_IsNew == 2);
				}
				break;

			case 'tab_databadvol':
				if ( typeof win.RegistryDataBadVolFiltersPanel.getForm().findField('filterIsZNO') == 'object' ) {
					win.RegistryDataBadVolFiltersPanel.getForm().findField('filterIsZNO').setContainerVisible(PayType_SysNick == 'oms' && RegistrySubType_id == 1 && (RegistryType_id == 1 || RegistryType_id == 2 || RegistryType_id == 6) && win.Registry_IsNew == 2);
				}
				break;
		}
	},
	onTreeClick: function(node,e)
	{
		var win = this;
		var level = node.getDepth();
		var owner = node.getOwnerTree().ownerCt;
		owner.RegistryErrorFiltersPanel.getForm().reset();
                //задача #86094
                var colname;
                var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
                //

		var PayType_SysNick = 'oms';
		if (node.id.indexOf('PayType.1.bud') >= 0 && level >= 4) {
			level++;
			PayType_SysNick = 'bud';
		}

		switch (level)
		{
			case 0: case 1: case 2:
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 3:
				g_RegistryType_id = node.attributes.object_value;
				owner.findById('regvRightPanel').setVisible(false);
				break;
			case 4:
				owner.findById('regvRightPanel').setVisible(false);
				g_RegistryType_id = node.parentNode.parentNode.attributes.object_value;
				break;
			case 5:
				if (PayType_SysNick == 'bud') {
					var RegistrySubType_id = 1;
					g_RegistryType_id = node.parentNode.attributes.object_value;
				} else {
					var RegistrySubType_id = node.parentNode.attributes.object_value;
					g_RegistryType_id = node.parentNode.parentNode.attributes.object_value;
				}
				switch(RegistrySubType_id) {
					case 2:
						owner.findById('regvRightPanel').setVisible(true);
						owner.findById('regvRightPanel').getLayout().setActiveItem(1);
						var Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
						var RegistryType_id = node.parentNode.parentNode.attributes.object_value;
						var RegistryStatus_id = node.attributes.object_value;

						//Формирование глобального объекта Task#18011
						getGlobalRegistryData = {
							Lpu_id            : node.parentNode.parentNode.parentNode.attributes.object_value,
							RegistryType_id   : node.parentNode.parentNode.attributes.object_value,
							RegistrySubType_id: RegistrySubType_id,
							RegistryStatus_id : node.attributes.object_value,
							PayType_SysNick : PayType_SysNick
						}

						owner.UnionRegistryGrid.setActionDisabled('action_add', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'LpuPowerUser', 'SuperAdmin' ]) && (win.Registry_IsNew == 2 || isUserGroup([ 'SuperAdmin' ]))));
						owner.UnionRegistryGrid.setActionDisabled('action_edit', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'LpuPowerUser', 'SuperAdmin' ])));
						owner.UnionRegistryGrid.setActionDisabled('action_delete', !(RegistryStatus_id == 3 && isUserGroup([ 'LpuAdmin', 'LpuPowerUser', 'SuperAdmin' ])));

						// Меняем колонки и отображение
						owner.UnionRegistryGrid.setColumnHidden('DispClass_Name', (RegistryType_id != 7 && RegistryType_id != 17) || owner.Registry_IsNew != 2);
						owner.UnionRegistryGrid.setColumnHidden('LpuContragent_Name', RegistryType_id != 19);
						owner.UnionRegistryGrid.setColumnHidden('LpuUnitSet_Code', RegistryType_id == 19);
						owner.UnionRegistryGrid.setColumnHidden('Registry_IsZNO', (RegistryType_id != 1 && RegistryType_id != 2 && RegistryType_id != 6) || owner.Registry_IsNew != 2);

						owner.UnionDataGrid.setColumnHidden('Diag_Code', RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('Evn_disDate', RegistryType_id != 1 && RegistryType_id != 14);
						owner.UnionDataGrid.setColumnHidden('HTMedicalCareClass_GroupCode', RegistryType_id != 14);
						owner.UnionDataGrid.setColumnHidden('HTMedicalCareClass_Name', RegistryType_id != 14);
						owner.UnionDataGrid.setColumnHidden('LpuSectionProfile_Name', RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('MedPersonal_LabFIO', RegistryType_id != 19);
						owner.UnionDataGrid.setColumnHidden('MedSpecOms_Name', RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('Mes_Code', true);
						owner.UnionDataGrid.setColumnHidden('Mes_Code_KSG', RegistryType_id != 1 && RegistryType_id != 14);
						owner.UnionDataGrid.setColumnHidden('Mes_Code_KPG', RegistryType_id != 1 && RegistryType_id != 14);
						owner.UnionDataGrid.setColumnHidden('RegistryData_IsEarlier', RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('RegistryData_Uet', RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('Usluga_Code', RegistryType_id == 1 || RegistryType_id == 14 || RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('UslugaComplex_Code', RegistryType_id != 19);
						owner.UnionDataGrid.setColumnHidden('UslugaComplex_Name', RegistryType_id != 19);
						owner.UnionDataGrid.setColumnHidden('VolumeType_Code', RegistryType_id == 19);
						owner.UnionDataGrid.setColumnHidden('VolumeType_Code2', RegistryType_id != 1 && RegistryType_id != 14);

						owner.UnionTFOMSErrorGrid.setColumnHidden('Evn_disDate', RegistryType_id == 19);
						owner.UnionTFOMSErrorGrid.setColumnHidden('LpuSectionProfile_Name', RegistryType_id == 19);
						owner.UnionTFOMSErrorGrid.setColumnHidden('MedPersonal_Fio', RegistryType_id == 19);
						owner.UnionTFOMSErrorGrid.setColumnHidden('MedPersonal_LabFIO', RegistryType_id != 19);
						owner.UnionTFOMSErrorGrid.setColumnHidden('MedSpecOms_Name', RegistryType_id == 19);

						if (RegistryType_id == 1 || RegistryType_id == 14) {
							// Для стаца одни названия
							owner.UnionDataGrid.setColumnHeader('EvnPL_NumCard', '№ карты');
							owner.UnionDataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
							owner.UnionDataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');

							owner.UnionTFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Начало');
						}
						else if (RegistryType_id == 19) {
							owner.UnionDataGrid.setColumnHeader('EvnPL_NumCard', '№ направления');
							owner.UnionDataGrid.setColumnHeader('EvnVizitPL_setDate', 'Дата выполнения');

							owner.UnionTFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Дата выполнения');
						}
						else {
							// Для остальных - другие
							owner.UnionDataGrid.setColumnHeader('EvnPL_NumCard', '№ талона');
							owner.UnionDataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
							owner.UnionDataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');

							owner.UnionTFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Начало');
						}

						owner.UnionRegistryGrid.loadData({params:{RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, Lpu_id:Lpu_id, Registry_IsNew: owner.Registry_IsNew}, globalFilters:{RegistryType_id:RegistryType_id, RegistryStatus_id:RegistryStatus_id, Lpu_id:Lpu_id, Registry_IsNew: owner.Registry_IsNew, start: 0, limit: 100}});

						break;

					case 1:
					case 3:
						owner.findById('regvRightPanel').setVisible(true);
						owner.findById('regvRightPanel').getLayout().setActiveItem(0);

						if (PayType_SysNick == 'bud') {
							var Lpu_id = node.parentNode.parentNode.parentNode.attributes.object_value;
							var RegistryType_id = node.parentNode.attributes.object_value;
						} else {
							var Lpu_id = node.parentNode.parentNode.parentNode.parentNode.attributes.object_value;
							var RegistryType_id = node.parentNode.parentNode.attributes.object_value;
						}
						var RegistryStatus_id = node.attributes.object_value;

						win.AccountGrid.setColumnHidden('Registry_NoErrSum', PayType_SysNick != 'bud');
						win.AccountGrid.setColumnHidden('Registry_SumPaid', PayType_SysNick != 'bud');

						if (RegistrySubType_id == 3) {
							// Для реестров из папки «Реестры для контроля объемов МП» отображаются только вкладки: 0. Реестр, 1. Данные, 2. Превышение объемов МП
							win.DataTab.hideTabStripItem('tab_commonerr');
							win.DataTab.hideTabStripItem('tab_dataerr');
							win.DataTab.hideTabStripItem('tab_datatfomserr');
							win.DataTab.hideTabStripItem('tab_datamzerr');
							win.DataTab.hideTabStripItem('tab_datavizitdouble');
							win.DataTab.setTabStripItemTitle('tab_databadvol', '3. Превышение объёма МП');
							win.AccountGrid.setColumnHidden('OrgSmo_Name', true);
							win.AccountGrid.setColumnHidden('LpuUnitSet_Code', true);
							win.AccountGrid.setColumnHidden('Lpu_Nick', false);
							win.AccountGrid.setColumnHidden('DispClass_Name', true);
						} else {
							win.DataTab.unhideTabStripItem('tab_commonerr');
							win.DataTab.unhideTabStripItem('tab_dataerr');
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
							win.DataTab.unhideTabStripItem('tab_datavizitdouble');
							win.DataTab.setTabStripItemTitle('tab_databadvol', '6. Превышение объёма МП');
							win.AccountGrid.setColumnHidden('OrgSmo_Name', false);
							win.AccountGrid.setColumnHidden('LpuUnitSet_Code', RegistryType_id == 19);
							win.AccountGrid.setColumnHidden('Lpu_Nick', true);
							win.AccountGrid.setColumnHidden('DispClass_Name', (RegistryType_id != 7 && RegistryType_id != 17) || owner.Registry_IsNew != 2);
							win.AccountGrid.setColumnHidden('LpuContragent_Name', RegistryType_id != 19);
						}

						if (!win.DataTab.getActiveTab().id.inlist(['tab_registry', 'tab_data', 'tab_databadvol'])) {
							win.DataTab.setActiveTab('tab_data');
						}

						win.setZNOFilterVisibility(node);

						//Формирование глобального объекта Task#18011
						getGlobalRegistryData = {
							Lpu_id            : node.parentNode.parentNode.parentNode.attributes.object_value,
							RegistryType_id   : node.parentNode.parentNode.attributes.object_value,
							RegistrySubType_id: RegistrySubType_id,
							RegistryStatus_id : node.attributes.object_value
						};

						//end Task#18011

						//Task# Групповое исключение/восстановление записей реестра. отключение пунктов контекстного меню
						if(getGlobalRegistryData.RegistryStatus_id !=3){
							this.DataGrid.setActionDisabled('action_delete_all_records',true);
							this.DataGrid.setActionDisabled('action_undelete_all_records',true);
							this.DataGrid.setActionDisabled('action_delete_all_records_in_filter',true);
							this.DataGrid.setActionDisabled('action_undelete_all_records_in_filter',true);
						}
						else{
							this.DataGrid.setActionDisabled('action_delete_all_records',false);
							this.DataGrid.setActionDisabled('action_undelete_all_records',false);
							this.DataGrid.setActionDisabled('action_delete_all_records_in_filter',false);
							this.DataGrid.setActionDisabled('action_undelete_all_records_in_filter',false);
						}

						// Меняем колонки и отображение
						owner.DataGrid.setColumnHidden('Diag_Code', RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('Evn_disDate', RegistryType_id != 1 && RegistryType_id != 14);
						owner.DataGrid.setColumnHidden('HTMedicalCareClass_GroupCode', RegistryType_id != 14);
						owner.DataGrid.setColumnHidden('HTMedicalCareClass_Name', RegistryType_id != 14);
						owner.DataGrid.setColumnHidden('LpuSectionProfile_Name', RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('MedPersonal_LabFIO', RegistryType_id != 19);
						owner.DataGrid.setColumnHidden('MedSpecOms_Name', RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('Mes_Code', true);
						owner.DataGrid.setColumnHidden('Mes_Code_KSG', RegistryType_id != 1 && RegistryType_id != 14);
						owner.DataGrid.setColumnHidden('Mes_Code_KPG', RegistryType_id != 1 && RegistryType_id != 14);
						owner.DataGrid.setColumnHidden('RegistryData_IsEarlier', RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('RegistryData_Uet', RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('RegistryHealDepResType_id', PayType_SysNick != 'bud');
						owner.DataGrid.setColumnHidden('Usluga_Code', RegistryType_id == 1 || RegistryType_id == 14 || RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('UslugaComplex_Code', RegistryType_id != 19);
						owner.DataGrid.setColumnHidden('UslugaComplex_Name', RegistryType_id != 19);
						owner.DataGrid.setColumnHidden('VolumeType_Code', RegistryType_id == 19);
						owner.DataGrid.setColumnHidden('VolumeType_Code2', RegistryType_id != 1 && RegistryType_id != 14);

						owner.ErrorGrid.setColumnHidden('Evn_disDate', RegistryType_id == 19);
						owner.ErrorGrid.setColumnHidden('LpuSectionProfile_Name', RegistryType_id == 19);
						owner.ErrorGrid.setColumnHidden('MedPersonal_Fio', RegistryType_id == 19);
						owner.ErrorGrid.setColumnHidden('MedPersonal_LabFIO', RegistryType_id != 19);
						owner.ErrorGrid.setColumnHidden('MedSpecOms_Name', RegistryType_id == 19);
						owner.ErrorGrid.setColumnHidden('Mes_Code', RegistryType_id == 1 || RegistryType_id == 14 || RegistryType_id == 19);
						owner.ErrorGrid.setColumnHidden('Usluga_Code', RegistryType_id != 1 && RegistryType_id != 14);
						owner.ErrorGrid.setColumnHidden('UslugaComplex_Code', RegistryType_id != 19);
						owner.ErrorGrid.setColumnHidden('UslugaComplex_Name', RegistryType_id != 19);
						owner.ErrorGrid.setColumnHidden('RegistryError_Desc', RegistryType_id == 1 || RegistryType_id == 14 || RegistryType_id == 19);

						owner.TFOMSErrorGrid.setColumnHidden('Evn_disDate', RegistryType_id == 19);
						owner.TFOMSErrorGrid.setColumnHidden('LpuSectionProfile_Name', RegistryType_id == 19);
						owner.TFOMSErrorGrid.setColumnHidden('MedPersonal_Fio', RegistryType_id == 19);
						owner.TFOMSErrorGrid.setColumnHidden('MedPersonal_LabFIO', RegistryType_id != 19);
						owner.TFOMSErrorGrid.setColumnHidden('MedSpecOms_Name', RegistryType_id == 19);

						if (RegistryType_id == 1 || RegistryType_id == 14) {
							owner.DataGrid.setColumnHeader('EvnPL_NumCard', '№ карты');
							owner.DataGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
							owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');

							owner.DataBadVolGrid.setColumnHeader('RegistryData_Uet', 'К/д факт');
							owner.DataBadVolGrid.setColumnHeader('EvnVizitPL_setDate', 'Поступление');
							owner.DataBadVolGrid.setColumnHidden('Evn_disDate', false);
							owner.DataBadVolGrid.setColumnHidden('VolumeType_Code2', false);

							owner.ErrorGrid.setColumnHeader('Evn_setDate', 'Начало');

							owner.TFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Начало');
						}
						else if (RegistryType_id == 19) {
							owner.DataGrid.setColumnHeader('EvnPL_NumCard', '№ направления');
							owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Дата выполнения');

							owner.ErrorGrid.setColumnHeader('Evn_setDate', 'Дата выполнения');

							owner.TFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Дата выполнения');
						}
						else {
							owner.DataGrid.setColumnHeader('EvnPL_NumCard', '№ талона');
							owner.DataGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
							owner.DataGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');

							owner.DataBadVolGrid.setColumnHeader('RegistryData_Uet', 'УЕТ');
							owner.DataBadVolGrid.setColumnHeader('EvnVizitPL_setDate', 'Посещение');
							owner.DataBadVolGrid.setColumnHidden('Evn_disDate', true);
							owner.DataBadVolGrid.setColumnHidden('VolumeType_Code2', true);

							owner.ErrorGrid.setColumnHeader('Evn_setDate', 'Начало');

							owner.TFOMSErrorGrid.setColumnHeader('Evn_setDate', 'Начало');
						}

						owner.DataBadVolGrid.setColumnHidden('Mes_Code', true);
						owner.DataBadVolGrid.setColumnHidden('Mes_Code_KSG', RegistryType_id!=1 && RegistryType_id!=14);
						owner.DataBadVolGrid.setColumnHidden('Mes_Code_KPG', RegistryType_id!=1 && RegistryType_id!=14);
						owner.DataBadVolGrid.setColumnHidden('HTMedicalCareClass_GroupCode', RegistryType_id!=14);
						owner.DataBadVolGrid.setColumnHidden('HTMedicalCareClass_Name', RegistryType_id!=14);
						owner.DataBadVolGrid.setColumnHidden('Usluga_Code', RegistryType_id==1 || RegistryType_id==14);

						owner.AccountGrid.setActionDisabled('action_add', !(RegistryStatus_id == 3 && (win.Registry_IsNew == 2 || isUserGroup([ 'SuperAdmin' ]))));
						owner.AccountGrid.setActionDisabled('action_edit', (RegistryStatus_id!=3));
						//owner.AccountGrid.setActionDisabled('action_print', ((RegistryStatus_id!=4) || !(RegistryType_id.inlist([1,2,4,5,6,7,8,9,10,11,12,14])))); // !!! Пока только для полки, потом поправить обратно

						if (12 == RegistryStatus_id) {
							owner.AccountGrid.deletedRegistriesSelected = true;
						} else {
							owner.AccountGrid.deletedRegistriesSelected = false;
						}

						owner.setMenuActions(owner.AccountGrid, RegistryStatus_id);

						owner.AccountGrid.getAction('action_yearfilter').setHidden( RegistryStatus_id != 4 );
						if( 4 == RegistryStatus_id ) {
							owner.constructYearsMenu({RegistryType_id: RegistryType_id, RegistryStatus_id: RegistryStatus_id, Lpu_id: Lpu_id});
						}

						owner.AccountGrid.loadData({
							params: {
								RegistryType_id: RegistryType_id,
								RegistrySubType_id: RegistrySubType_id,
								RegistryStatus_id: RegistryStatus_id,
								Lpu_id: Lpu_id,
								Registry_IsNew: owner.Registry_IsNew,
								PayType_SysNick: PayType_SysNick
							},
							globalFilters: {
								RegistryType_id: RegistryType_id,
								RegistrySubType_id: RegistrySubType_id,
								RegistryStatus_id: RegistryStatus_id,
								Lpu_id: Lpu_id,
								Registry_IsNew: owner.Registry_IsNew,
								PayType_SysNick: PayType_SysNick
							}
						});
						break;
				}
				break;
		}
	},
	constructYearsMenu: function( params ) {
		if( !params ) return false;

		this.AccountGrid.getAction('action_yearfilter').setText('фильтр по году: <b>за ' + (new Date()).getFullYear() + ' год</b>');
		this.AccountGrid.ViewGridPanel.getStore().baseParams['Registry_accYear'] = (new Date()).getFullYear();
		Ext.Ajax.request({
			url: '/?c=RegistryUfa&m=getYearsList',
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
		this.getLoadMask(LOAD_WAIT).show();
		if (RegistryQueue_Position===undefined)
		{
			Ext.Ajax.request(
			{
				url: '/?c=RegistryUfa&m=loadRegistryQueue',
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
					if ((form.ErrorGrid.getParam('Registry_id')!=Registry_id) || (form.ErrorGrid.getCount()==0))
					{
						form.ErrorGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
				case 'tab_datamzerr':
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
				case 'tab_datavizitdouble':
					if ((form.DoubleVizitGrid.getParam('Registry_id')!=Registry_id) || (form.DoubleVizitGrid.getCount()==0))
					{
						form.DoubleVizitGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
					}
					break;
				case 'tab_databadvol':
					if ((form.DataBadVolGrid.getParam('Registry_id')!=Registry_id) || (form.DataBadVolGrid.getCount()==0))
					{
						form.DataBadVolGrid.loadData({globalFilters:{Registry_id:Registry_id, RegistryType_id:RegistryType_id, start: 0, limit: 100}, noFocusOnLoad:!nofocus});
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
				case 'tab_datatfomserr':
					form.TFOMSErrorGrid.removeAll(true);
					break;
				case 'tab_datamzerr':
					form.RegistryHealDepResErrGrid.removeAll(true);
					break;
				case 'tab_datavizitdouble':
					form.DoubleVizitGrid.removeAll(true);
					break;
				case 'tab_databadvol':
					form.DataBadVolGrid.removeAll(true);
					break;
			}
		}
		return true;
	},
	onUnionRegistrySelect: function (Registry_id, nofocus, record, RegistryType_id)
	{
		var form = this;
		if(RegistryType_id)
			RegistryType_id = record.data.RegistryType_id

		if (form.UnionRegistryGrid.getCount()>0)
		{
			switch (form.UnionDataTab.getActiveTab().id)
			{
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
		var Registry_id = record.get('Registry_id'),
			RegistryType_id = record.get('RegistryType_id');
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
						url: '/?c=RegistryUfa&m=deleteRegistryQueue',
						params: 
						{	
							Registry_id: Registry_id,
							RegistryType_id: RegistryType_id
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
    startMek : function(){
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один реестр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id'),
			RegistryType_id = record.get('RegistryType_id');
        
		Ext.Ajax.request(
		{
			url: '/?c=RegistryUfa&m=startMek',
			params: 
			{	
				Registry_id: Registry_id,
				RegistryType_id: RegistryType_id
				/*Lpu_id: Lpu_id*/
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					
                    //log('RES>', result)
                    
                    if(result.success){
                    	Ext.Msg.alert('Внимание!', result.Message);
                    }
                    /**    
                    if (result.RegistryStatus_id==RegistryStatus_id)
					{
						// Перечитываем грид, чтобы обновить данные по счетам
						form.AccountGrid.loadData();
					}
                    */
				}
			}
		});   
    },
	deleteUnionRegistrys: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record || !record.get('Registry_id'))
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
					form.getLoadMask('Удаление связанных реестров...').show();
					Ext.Ajax.request({
						url: '/?c=RegistryUfa&m=deleteUnionRegistrys',
						params: {
							Registry_id: record.get('Registry_id'),
							RegistryType_id: record.get('RegistryType_id')
						},
						callback: function (options, success, response) {
							form.getLoadMask().hide();
							if (success && response.responseText != '') {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success) {
									sw.swMsg.show({
										buttons: Ext.Msg.OK,
										icon: Ext.Msg.INFO,
										msg: 'Связанные реестры успешно удалены.',
										title: ''
									});
								}
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Вы хотите удалить связанные реестры по СМО?',
			title: 'Вопрос'
		});
	},
	deleteOrgSmoRegistryData: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record || !record.get('Registry_id'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var registryType_id = record.get('RegistryType_id');
		// Для удаления пользователю необходимо указать конкретную СМО. В результате из предварительного реестра удалятся все случаи указанной СМО.
		getWnd('swSelectOrgSmoWindow').show({
			RegistryType_id: registryType_id,
			onSelect: function(data) {
				form.getLoadMask('Удаление случаев по СМО...').show();
				Ext.Ajax.request({
					url: '/?c=RegistryUfa&m=deleteOrgSmoRegistryData',
					params: {
						Registry_id: record.get('Registry_id'),
						OrgSmo_ids: data.OrgSmo_ids,
						Registry_IsNotInsur: data.Registry_IsNotInsur,
						Registry_IsZNO: data.Registry_IsZNO,
						RegistryType_id: registryType_id
					},
					callback: function (options, success, response) {
						form.getLoadMask().hide();

						if (form.DataGrid && form.DataGrid.rendered && form.DataGrid.getGrid().getStore()) {
							form.DataGrid.getGrid().getStore().reload();
						}

						if (form.DataBadVolGrid && form.DataBadVolGrid.rendered && form.DataBadVolGrid.getGrid().getStore()) {
							form.DataBadVolGrid.getGrid().getStore().reload();
						}
					}
				});
			}
		});
	},
	checkIncludeInUnioinRegistry: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record || !record.get('Registry_id'))
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var registryType_id = record.get('RegistryType_id');
		form.getLoadMask('Проверка случаев на включение в реестры по СМО...').show();
		Ext.Ajax.request({
			url: '/?c=RegistryUfa&m=checkIncludeInUnioinRegistry',
			params: {
				Registry_id: record.get('Registry_id'),
				RegistryType_id: registryType_id
			},
			callback: function (options, success, response) {
				form.getLoadMask().hide();
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success && response_obj.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: response_obj.Alert_Msg,
							title: ''
						});
					}
				}
			}
		});
	},
	registryRevive: function() {
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id');
		//var Lpu_id = record.get('Lpu_id');

		Ext.Ajax.request({
			url: '/?c=Registry&m=reviveRegistry',
			params: {
				Registry_id: Registry_id
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
	setRegistryStatus: function(RegistryStatus_id)
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var registryType_id = record.get('RegistryType_id'),
			Registry_id = record.get('Registry_id');

		var Registry_ids = [];
		var selections = this.AccountGrid.getGrid().getSelectionModel().getSelections();
		for (key in selections) {
			if (typeof(selections[key]) == 'object') {
				Registry_ids.push(selections[key].get('Registry_id'));
			}
		}

		form.getLoadMask('Установка статуса...').show();
		Ext.Ajax.request(
		{
			url: '/?c=RegistryUfa&m=setRegistryStatus',
			params: 
			{	
				Registry_ids: Ext.util.JSON.encode(Registry_ids),
				RegistryStatus_id: RegistryStatus_id,
				RegistryType_id: registryType_id
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
	exportRegistryErrorDataToDbf: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		
		var fd = 'swRegistryErrorDataToDbfWindow';
		var params = {onHide: Ext.emptyFn, Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=RegistryUfa&m=exportRegistryErrorDataToDbf'};
		getWnd(fd).show(params);
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
		var params = {onHide: Ext.emptyFn, Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=RegistryUfa&m=exportRegistryToDbf'};
		getWnd(fd).show(params);
	},
	//Task#18694  Для группового экспорта XML отмеченных реестров "К оплате"
	exportRegistryGroupToXml : function(){
		//Получить все отмеченные строки
		var records = this.AccountGrid.ViewGridModel.selections.items;  
		//Если не одна строка не выбрана 
		if(records.length == 0){
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;        
		}
		//Если одна строка отмечена - запустить родной метод
		else if(records.length == 1){
			this.exportRegistryToXml(1);
		}
		//Если отмечено более 1 строки
		else{ 
			 //Массив для коллекции реестров, которые нуждаются в переформеровании
			 var needReform = [];
			 var Registry_ids = [];
			 var RegistryType_ids = [];
			 var Registry_Nums = [];
			 //Пробежимся по каждому элементу массива
			 for(var i=0; i<records.length; i++){
				 //Если есть реестры, которые нуждается в переформировании - соберём их номера для дальнейшего инфлормирования пользователя 
				 if(records[i].data.Registry_IsNeedReform == 2){
					 needReform[i] = records[i].data.Registry_Num;
				 }
				 else{
					Registry_Nums[i] = records[i].data.Registry_Num;
					Registry_ids[i] = records[i].data.Registry_id;
					RegistryType_ids[i] = records[i].data.RegistryType_id; 
				 }
				 //if(Registry_ids.length == 1){
				 //   Registry_ids = Registry_ids[0];
				 //   RegistryType_ids = RegistryType_ids[0];
				 //}
			 }
			 
			 //Если есть реестры, необходимые в переформировании - покажем намера этих реестров и остоновим действие
			 if(needReform.length > 0){
				 //Соберём список номеров реестров в строку
				 var numbers = '';
				 for(var j = 0; j<needReform.length; j++){
					 //log(numbers);
					 numbers = numbers + '№' + needReform[j] + ', ';
				 }
				 
				  Ext.Msg.alert('Ошибка', 'Реестр(ы) ' + numbers + '  нуждается в переформировании, отправка и экспорт не возможны.<br/>Переформируйте реестр(ы) и повторите действие.<br/>');
				 return false;
			 }
			 //Передаём параметры и открываем окошко
			 else{
				 //log(Registry_ids);
				 //log(RegistryType_ids);
				 
				 var fd = 'swRegistryGroupXmlWindow';
				
				 var params = {
								onHide: Ext.emptyFn, 
								Registry_Num: Registry_Nums, 
								Registry_id: Registry_ids, 
								RegistryType_id: RegistryType_ids, 
								url:'/?c=RegistryUfa&m=exportRegistryGroupToXml'
							  };
				 
				 //log('BEFORE PARAMS');
				 //log(params);
				 getWnd(fd).show(params);               
				 //ShowWindow(fd, params);
			 }              	
		} 
		
		//log(records.length);       
	},
	//end Task
	exportRegistryToXml: function(mode)
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

		var fd = 'swRegistryXmlWindow';
		var params = {onHide: Ext.emptyFn, Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=RegistryUfa&m=exportRegistryToXml'};

		if (mode && mode == 2) {
			params.withSign = true;
		}

		getWnd(fd).show(params);
	},
	exportRegistryToXmlCheckVolume: function()
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
		
		var fd = 'swRegistryXmlWindow';
		var params = {onHide: Ext.emptyFn, Registry_id: record.get('Registry_id'), RegistryType_id: record.get('RegistryType_id'), url:'/?c=RegistryUfa&m=exportRegistryToXmlCheckVolume'};
		getWnd(fd).show(params);
	},
	importRegistryDBF: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		
		var fd = 'swRegistryImportWindow';
		var params =
		{
			onHide: function()
			{
				var form = Ext.getCmp('RegistryViewWindow');
				if (form.ErrorGrid && form.ErrorGrid.rendered && form.ErrorGrid.ViewGridStore) {
					form.ErrorGrid.ViewGridStore.reload();
				}
			},
			callback: function()
			{

			},
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id')
		};
		getWnd(fd).show(params);
	},
	importUnionRegistryXML: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}

		var form = this;
		var fd = 'swRegistryImportXMLWindow';
		var params =
		{
			onHide: function()
			{
				if (form.UnionTFOMSErrorGrid && form.UnionTFOMSErrorGrid.rendered && form.UnionTFOMSErrorGrid.ViewGridStore) {
					form.UnionTFOMSErrorGrid.ViewGridStore.reload();
				}
			},
			callback: function()
			{

			},
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id')
		};
		getWnd(fd).show(params);
	},
	importRegistryXML: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
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
				var form = Ext.getCmp('RegistryViewWindow');
				if (form.ErrorGrid && form.ErrorGrid.rendered && form.ErrorGrid.ViewGridStore) {
					form.ErrorGrid.ViewGridStore.reload();
				}
			},
			callback: function()
			{

			},
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id')
		};
		getWnd(fd).show(params);
	},
	reformUnionRegistry: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId)
			{
				if (buttonId=='yes') {
					form.getLoadMask('Переформирование реестра').show();
					Ext.Ajax.request({
						url: '/?c=RegistryUfa&m=reformUnionRegistry',
						params:
						{
							Registry_id: Registry_id,
							RegistryType_id: registryType_id
						},
						callback: function(options, success, response)
						{
							form.getLoadMask().hide();
							if (success)
							{
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
	setRegistryPackNum: function(viewframe) {
		var record = viewframe.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		getWnd("swOrderDateRegistryModUfa").show({
			Registry_orderDate: record.get('Registry_orderDate'),
			Registry_pack: record.get('Registry_pack'),
			Registry_id: record.get('Registry_id'),
			callback: function() {
				viewframe.loadData();
			}
		});
	},
	deleteUnionRegistryWithData: function() {
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		var form = this;
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope : form,
			fn: function(buttonId)
			{
				if (buttonId=='yes') {
					form.getLoadMask('Переформирование реестра').show();
					Ext.Ajax.request({
						url: '/?c=RegistryUfa&m=deleteUnionRegistryWithData',
						params:
						{
							Registry_id: Registry_id,
							RegistryType_id: registryType_id
						},
						callback: function(options, success, response)
						{
							form.getLoadMask().hide();
							if (success)
							{
								// Перечитываем грид, чтобы обновить данные по счетам
								form.UnionRegistryGrid.loadData();
							}
						}
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: 'Внимание. При выполнении действия произойдет удаление реестра по '+record.get('OrgSmo_Name')+' №'+record.get('Registry_Num')+' и всех случаев из предварительных реестров, связанных с данной СМО. Продолжить?',
			title: 'Вопрос'
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
		var Registry_id = record.get('Registry_id'),
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
			url: '/?c=RegistryUfa&m=setUnionRegistryStatus',
			params:
			{
				Registry_ids: Ext.util.JSON.encode(Registry_ids),
				RegistryStatus_id: RegistryStatus_id,
				RegistryType_id: registryType_id
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
	},
	reformRegistry: function(record) {
		var current_window = this;

		if ( record.Registry_id > 0 ) {
			var loadMask = new Ext.LoadMask(Ext.get(this.id), {msg: 'Подождите, идет переформирование реестра...'});
			loadMask.show();
			Ext.Ajax.request(
			{
				url: '/?c=RegistryUfa&m=reformRegistry',
				params: 
				{
					Registry_id: record.Registry_id,
					RegistryType_id: record.RegistryType_id,
					Registry_IsNew: current_window.Registry_IsNew
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
			KatNasel_id: record.get('KatNasel_id'),
			OverrideExportOneMoreOrUseExist: options.OverrideExportOneMoreOrUseExist,
			forSign: 1
		};

		Ext.Ajax.request({
			url: '/?c=RegistryUfa&m=exportRegistryToXML',
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
		object.addActions({
			name: 'action_yearfilter',
			menu: new Ext.menu.Menu()
		});
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
				menu = [
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
					form.menuActions.doMEK,
					form.menuActions.exportToDbf,
					form.menuActions.exportToXml,
					form.menuActions.registrySetPay,
					'-',
					form.menuActions.reformRegistry,
					'-',
					form.menuActions.refreshRegistry,
					form.menuActions.deleteOrgSmoRegistryData,
					form.menuActions.refreshRegistryVolumes
				];
				break;

			case 2: // К оплате
				menu = 
				[
					form.menuActions.refreshRegistry,
					form.menuActions.exportToDbf,
					form.menuActions.exportToXml,
					form.menuActions.setRegistryAccDate,
					form.menuActions.setRegistryPackNum,
					form.menuActions.exportToXmlCheckVolume,
					form.menuActions.importRegistryDBF,
					form.menuActions.exportRegistryErrorDataToDbf,
					'-',
					form.menuActions.registrySetWork,
					form.menuActions.registrySetPaid,
					form.menuActions.deleteUnionRegistrys,
					form.menuActions.checkIncludeInUnioinRegistry,
					form.menuActions.deleteOrgSmoRegistryData,
					form.menuActions.registrySign,
					form.menuActions.sendRegistryToMZ
				];
				break;

			case 4: // Оплаченные 
				menu = 
				[
					form.menuActions.exportToXml,
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
			case 14:
				switch ( record.get('DispClass_id') ) {
					case 5:
						config.open_form = 'swEvnPLDispProfEditWindow';
						config.key = 'EvnPLDispProf_id';
						break;

					default:
						config.open_form = 'swEvnPLDispTeenInspectionProfEditWindow';
						config.key = 'EvnPLDispTeenInspection_id';
						break;
				}
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

			case 103:
				config.open_form = 'swEvnPLDispProfEditWindow';
				config.key = 'EvnPLDispProf_id';
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
	setIsBadVol: function(object, RegistryData_IsBadVol) {
		var form = this;
		var record = form[object].getGrid().getSelectionModel().getSelected();
		if (record) {
			var selections = form[object].getGrid().getSelectionModel().getSelections();

			var loadText = 'Снятие превышения объёма МП...';
			if (RegistryData_IsBadVol == 2) {
				loadText = 'Добавление превышения объёма МП...'
			}
			form.getLoadMask(loadText).show();

			var ids = [];

			for (key in selections) {
				if (typeof(selections[key]) == 'object') {
					ids.push(selections[key].get('Evn_id'));
				}
			}

			Ext.Ajax.request({
				url: '/?c=RegistryUfa&m=setIsBadVol',
				params: {
					Registry_id: record.get('Registry_id'),
					RegistryType_id: record.get('RegistryType_id'),
					Evn_ids: Ext.util.JSON.encode(ids),
					RegistryData_IsBadVol: RegistryData_IsBadVol
				},
				callback: function () {
					form.getLoadMask().hide();

					if (form.DataGrid && form.DataGrid.rendered && form.DataGrid.getGrid().getStore()) {
						if (object == 'DataGrid') {
							form.DataGrid.getGrid().getStore().reload();
						} else {
							form.DataGrid.removeAll(true);
						}
					}

					if (form.DataBadVolGrid && form.DataBadVolGrid.rendered && form.DataBadVolGrid.getGrid().getStore()) {
						if (object == 'DataBadVolGrid') {
							form.DataBadVolGrid.getGrid().getStore().reload();
						} else {
							form.DataBadVolGrid.removeAll(true);
						}
					}

					if (form.UnionDataGrid && form.UnionDataGrid.rendered && form.UnionDataGrid.getGrid().getStore()) {
						if (object == 'UnionDataGrid') {
							form.UnionDataGrid.getGrid().getStore().reload();
						} else {
							form.UnionDataGrid.removeAll(true);
						}
					}
				}
			});
		}
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
		if ( form.Tree.selModel.selNode.parentNode.attributes.object == 'RegistrySubType' && form.Tree.selModel.selNode.parentNode.attributes.object_value == 2 ) {
			var RegistryType_id = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		}
		else {
			var RegistryType_id = this.AccountGrid.getGrid().getSelectionModel().getSelected().get('RegistryType_id');
		}

		var type = record.get('RegistryType_id');
		if (!type)
			type = RegistryType_id;

		if (object.id == 'RegistryViewWindowTFOMSError' || object.id == this.id+'Data' || object.id == this.id+'Error') // Если это с грида "Ошибки ТФОМС" или "Данные" или "Ошибки данных"
		{
			if (frm=='OpenPerson') {
				type = 108;
			}
		}

		var id = record.get('Evn_rid') ||  record.get('Evn_id'); // Вызываем родителя , а если родитель пустой то основное 
		var person_id = record.get('Person_id');
		
		var open_form = '';
		var key = '';
		var params = {action: 'edit', Person_id: person_id, Server_id: 0, RegistryType_id: RegistryType_id}; //, Person_id: this.Person_id, Server_id: this.Server_id
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
				/*open_form = 'swEvnPLDispDopEditWindow';
				key = 'EvnPLDispDop_id';*/
				open_form = 'swEvnPLDispSomeAdultEditWindow';
				key = 'EvnPL_id';
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
			case 17:
				var config = form.getParamsForEvnClass(record);
				
				open_form = config.open_form;
				key = config.key;
				break;
			case 19:
				open_form = 'swEvnUslugaParEditWindow';
				key = 'EvnUslugaPar_id';
				id = record.get('Evn_id');
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
		
		if (id)
			params[key] = id;
		//log(params);
		if (open_form == 'swCmpCallCardNewCloseCardWindow') { // карты вызова
			params.formParams = Ext.apply(params);
		}
		getWnd(open_form).show(params);
	},
	exportUnionRegistryToXml: function()
	{
		var form = this;
		var record = this.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
		if (!record) {
			sw.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.');
			return false;
		}

		var fd = 'swRegistryXmlWindow';
		var params = {
			onHide: function () {
				this.UnionRegistryGrid.loadData();
			}.createDelegate(this),
			Registry_id: record.get('Registry_id'),
			RegistryType_id: record.get('RegistryType_id'),
			url: '/?c=RegistryUfa&m=exportUnionRegistryToXml'
		};
		getWnd(fd).show(params);
	},
	exportUnionRegistryGroupToXml: function()
	{
		var form = this;
		var records = this.UnionRegistryGrid.ViewGridModel.selections.items;
		if(records.length == 0){
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		//Если одна строка отмечена - запустить родной метод
		else if(records.length == 1){
			this.exportUnionRegistryToXml();
		}
		//Если отмечено более 1 строки
		else{
			var Registry_ids = [];
			var RegistryType_ids = [];
			var Registry_Nums = [];

			for(var i=0; i<records.length; i++){
				Registry_Nums[i] = records[i].data.Registry_Num;
				Registry_ids[i] = records[i].data.Registry_id;
				RegistryType_ids[i] = records[i].data.RegistryType_id;
			}

			var fd = 'swRegistryGroupXmlWindow';

			var params = {
				onHide: Ext.emptyFn,
				Registry_Num: Registry_Nums,
				Registry_id: Registry_ids,
				RegistryType_id: RegistryType_ids,
				url:'/?c=RegistryUfa&m=exportRegistryGroupToXml'
			};

			getWnd(fd).show(params);
		}
	},
	listeners: 
	{
		beforeshow: function()
		{
			this.findById('regvRightPanel').setVisible(false);
		}
	},
	filterOrgSMOCombo: function()
	{
		var date = new Date();
		var filtersForm = this.RegistryDataFiltersPanel.getForm();
		var OrgSMOCombo = filtersForm.findField('OrgSmo_id');
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function(rec)
		{
			if ( /.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		};

		OrgSMOCombo.getStore().filterBy(function(rec) {
			if ( /.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		});
	},
	filterOrgSMOComboBadVol: function()
	{
		var date = new Date();

		var filtersForm = this.RegistryDataBadVolFiltersPanel.getForm();
		var OrgSMOCombo = filtersForm.findField('OrgSmo_id');
		OrgSMOCombo.getStore().clearFilter();
		OrgSMOCombo.baseFilterFn = function(rec)
		{
			if ( /.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		};

		OrgSMOCombo.getStore().filterBy(function(rec) {
			if ( /.+/.test(rec.get('OrgSMO_RegNomC')) && (rec.get('OrgSMO_endDate') == '' || Date.parseDate(rec.get('OrgSMO_endDate'), 'd.m.Y') >= date )) {
				return true;
			} else {
				return false;
			}
		});
	},
	show: function() 
	{
		sw.Promed.swRegistryViewWindow.superclass.show.apply(this, arguments);
		
		// Проверяем наличие параметра Registry_IsNew
		this.Registry_IsNew = (arguments && arguments[0] && arguments[0].Registry_IsNew)?arguments[0].Registry_IsNew:null;
		this.setTitle(WND_ADMIN_REGISTRYLIST + ((this.Registry_IsNew==2)?' (новые)':''));
		
		this.getLoadMask().show();
		// При открытии если Root Node уже открыта - перечитываем
		var root = this.Tree.getRootNode();
		if (root)
		{
			if (root.isExpanded() && root.childNodes && root.childNodes.length > 0 && root.childNodes[0].loaded)
			{
				this.Tree.getLoader().load(root);
				// Дальше отрабатывает логика на load
			}
		}
		this.maximize();
		this.getReplicationInfo();
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
						url: '/?c=RegistryUfa&m=deleteRegistryDouble',
						params: {
							mode: mode,
							Evn_id: rec.get('Evn_id') || null,
							Registry_id: this.DoubleVizitGrid.getParam('Registry_id'),
							RegistryType_id: this.DoubleVizitGrid.getParam('RegistryType_id')
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
		form.getLoadMask('Пересчёт реестра').show();
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			scope: form,
			fn: function(buttonId) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						url: '/?c=RegistryUfa&m=refreshRegistryData',
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
	/**
	 * Пересчет объемов МП
	 */
	refreshRegistryVolumes: function() {
		var
			record = this.AccountGrid.getGrid().getSelectionModel().getSelected(),
			form = this;

		if ( typeof record != 'object' || Ext.isEmpty(record.get('Registry_id')) || (record.get('RegistrySubType_id') != 1 && record.get('RegistrySubType_id') != 3)) {
			return false;
		}

		form.getLoadMask('Пересчёт объемов МП...').show();

		Ext.Ajax.request({
			url: '/?c=RegistryUfa&m=refreshRegistryVolumes',
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
			scope: form,
			fn: function(buttonId) 
			{
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request(
					{
						url: '/?c=RegistryUfa&m=deleteRegistryData',
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

	/** 
	* Task# групповое удаление/восстановление записей из реестра
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

		//log('params.Filter', params.Filter);

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

		for(rec in records){
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
				/*
				else{
					Ext.Msg.alert('Сообщение', 'Запись не нуждается в восстановлении!');
					return;
				}
				*/
			}
			/**
			if(Object.keys(records).length == 1 && !act.inlist(['unDeleteAllSelected_in_filter','deleteAllSelected_in_filter'])){
				if(act == 'deleteAllSelected'){
					//log('Одиночное удаление');
					this.deleteRegistryData(grid);
				}
				else if(act == 'unDeleteAllSelected'){
					if(record.data.RegistryData_deleted != 2){
						Ext.Msg.alert('Сообщение', 'Запись не нуждается в восстановлении!');
						return;
					}
					//log('Одиночное восстановление');
					this.deleteRegistryData(grid);
				}
				// возможно + 2 условия - со всеми записями (предполагается использование фильтра)
			}*/ 
			//else{
			   
				params.Filter = null;

				if(act == 'deleteAllSelected' || act == 'delete'){
				   if(count_delete_evn == 0){
					   Ext.Msg.alert('Сообщение', 'Нет записей нуждающихся в удалении или записи уже помечены на удаление');  
					   return;            
				   }
				   //log('Удалить все отмеченные');
				   params.Type_select = 0;
				   params.RegistryData_deleted = 2;
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
				   params.RegistryData_deleted = 1;
				   params.Type_select = 0;
				   params.Evn_ids = Ext.util.JSON.encode(evn_ids_recovery);                 
									
				   var msg = '<b>Хотите восстановить помеченные на удаление записи?</b><br/><br/>';
				} 
				else if(act == 'deleteAllSelected_in_filter'){
					var msg = '<b>Вы собираетесь удалить все записи <br/>из реестра, предполагается, что ранее вы использовали фильтр.</b><br/><br/>'+
							  '<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данные записи пометятся как удаленные '+
							  'и будут удалены из реестра при выгрузке/отправке реестра (так же будут удалены все записи <b>по номерам талонов</b>).<br/>'+
							  'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
					//log('Удалить все после фильтра');
					params.evn_ids = null;
					params.Type_select = 1;
					//params.Filter = (typeof(grid.FilterSettings) != 'undefined') ? Ext.util.JSON.encode(grid.FilterSettings) : false;
					params.Filter = grid.getGrid().store.baseParams.Filter;
					params.RegistryData_deleted = 2;
				}           
				else if(act == 'unDeleteAllSelected_in_filter'){
					var msg = '<b>Вы собираетесь восстановить все записи <br/>из реестра, предполагается, что ранее вы использовали фильтр.</b><br/><br/>'+
							  '<span style="font-size:12px;color:#444;"><b>Обратите внимание</b>: Данные  будут восстановлены в реестре (так же будут восстановлены '+
							  'все записи <b>по номерам талонов</b>) при выгрузке/отправке реестра.<br/>'+
							  'Cумма реестра будет пересчитана также при выгрузке (отправке) реестра. </span>';
					//log('Восстановить все после фильтра');
					params.evn_ids = null;
					params.Type_select = 1;
					//params.Filter = (typeof(grid.FilterSettings) != 'undefined') ? Ext.util.JSON.encode(grid.FilterSettings) : false;
					params.Filter = grid.getGrid().store.baseParams.Filter;
					params.RegistryData_deleted = 1;
				}
				showMessage = true;
			//}

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
							url: '/?c=RegistryUfa&m=deleteRegistryGroupData',
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
	
	printRegistryError: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');
		if ( !Registry_id )
			return false;
		var id_salt = Math.random();
		var win_id = 'print_registryerror' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=RegistryUfa&m=printRegistryError&Registry_id=' + Registry_id + '&RegistryType_id=' + registryType_id, win_id);
	},
	printRegistryData: function()
	{
		var record = this.AccountGrid.getGrid().getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert('Ошибка', 'Не выбран ни один счет/регистр.<br/>');
			return false;
		}
		var Registry_id = record.get('Registry_id'),
			registryType_id = record.get('RegistryType_id');
		if ( !Registry_id )
			return false;
		var sort = Ext.util.JSON.encode(this.DataGrid.sortInfo);
		var id_salt = Math.random();
		var win_id = 'print_registrydata' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=RegistryUfa&m=printRegistryData&Registry_id=' + Registry_id + '&RegistryType_id=' + registryType_id + '&sort=' + sort, win_id);
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
			doMEK: new Ext.Action({
				text: 'Провести МЭК (только для разработчиков)',
				tooltip: 'Провести МЭК',
				//Временно
				hidden : (IS_DEBUG != '1'),
				handler: function()
				{
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					//console.log((record))

					if((/^Выполнено/gi).test(record.json.Registry_State)){
						Ext.Msg.alert('Предупреждение', 'Реестр уже прошёл все этапы МЭК, <br/>необходимо принять меры к устранению выявленных ошибок и переформировать реестр!');
					}
					else{
						form.startMek();
					}
				}
			}),
			exportToXml: new Ext.Action({
				text: 'Экспорт в XML',
				tooltip: 'Экспорт в XML',
				handler: function() {
					form.exportRegistryToXml(1);
				}
			}),
			exportGroupToXml: new Ext.Action({
				text: 'Экспорт всех выбранных в XML',
				tooltip: 'Экспорт всех выбранных в XML',
				handler: function()
				{
					form.exportRegistryGroupToXml();
				},
				listeners : {
					render : function(){
						this.disabled = false;
					}
				}
			}),
			exportToXmlCheckVolume: new Ext.Action({
				text: 'Экспорт в XML (проверка на объёмы)',
				tooltip: 'Экспорт в XML (проверка на объёмы)',
				hidden: true,
				handler: function()
				{
					form.exportRegistryToXmlCheckVolume();
				}
			}),
			importRegistryDBF: new Ext.Action({
				text: 'Загрузить реестр-ответ (DBF)',
				tooltip: 'Загрузить ответ в формате DBF',
				handler: function()
				{
					form.importRegistryDBF();
				}
			}),
			importRegistryXML: new Ext.Action({
				text: 'Загрузить реестр-ответ (XML)',
				tooltip: 'Загрузить ответ в формате XML',
				handler: function()
				{
					form.importRegistryXML();
				}
			}),
			exportRegistryErrorDataToDbf: new Ext.Action({
				text: 'Выгрузить данные для сверки с СБЗ',
				tooltip: 'Выгрузить данные для сверки с СБЗ',
				handler: function()
				{
					form.exportRegistryErrorDataToDbf();
				}
			}),
			setRegistryAccDate: new Ext.Action({
				text: '&raquo; Смена отчётной даты реестра',
				tooltip: 'Смена отчётной даты реестра',
				handler: function()
				{
					form.setRegistryPackNum(form.AccountGrid);
				}
			}),
			setRegistryPackNum: new Ext.Action({
				text: '&raquo; Установка номера пачки реестра',
				tooltip: 'Установка номера пачки реестра',
				handler: function()
				{
					form.setRegistryPackNum(form.AccountGrid);
				}
			}),
			exportToDbf: new Ext.Action({
				text: 'Экспорт в DBF',
				tooltip: 'Экспорт в DBF',
				handler: function() {
					form.exportRegistry();
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
			deleteOrgSmoRegistryData: new Ext.Action({
				text: 'Удалить все случаи по СМО',
				tooltip: 'Удалить все случаи по СМО',
				handler: function() {
					form.deleteOrgSmoRegistryData();
				}
			}),
			refreshRegistryVolumes: new Ext.Action({
				text: 'Пересчитать объемы МП',
				tooltip: 'Пересчитать объемы МП',
				handler: function() {
					form.refreshRegistryVolumes();
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
			deleteUnionRegistrys: new Ext.Action({
				text: 'Удалить все связанные реестры по СМО',
				tooltip: 'Удалить все связанные реестры по СМО',
				handler: function()
				{
					form.deleteUnionRegistrys();
				}
			}),
			checkIncludeInUnioinRegistry: new Ext.Action({
				text: 'Проверить случаи реестра на включение в реестры по СМО',
				tooltip: 'Проверить случаи реестра на включение в реестры по СМО',
				handler: function()
				{
					form.checkIncludeInUnioinRegistry();
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
				dataUrl: '/?c=RegistryUfa&m=loadRegistryTree',
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
			dataUrl: '/?c=RegistryUfa&m=loadRegistry',
			/*paging: true,*/
			autoLoadData: false,
			stringfields:
			[
				{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
				{name: 'RegistryType_id', type: 'int', hidden: true},
				{name: 'RegistryStatus_id', type: 'int', hidden: true},
				{name: 'Registry_IsActive', type: 'int', hidden: true},
				{name: 'Registry_IsProgress', type: 'int', hidden: true},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'OrgSmo_id', type: 'int', hidden: true},
				{name: 'OrgSmo_Name', id: 'autoexpand', header: 'СМО'},
				{name: 'LpuUnitSet_Code', header: 'Код подразделения', width: 150},
				{name: 'Lpu_Nick', header: 'Структурное подразделение МО', width: 200},
				{name: 'LpuContragent_Name', header: 'МО-контрагент', width: 200},
				{name: 'Registry_Num', header: 'Номер счета', width: 120},
				{name: 'ReformTime',hidden: true},
				{name: 'Registry_accDate', type:'date', header: 'Дата счета', width: 80},
                {name: 'Registry_insDT', type:'date', header: '', width: 80,hidden:true},
				{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
				{name: 'Registry_endDate', type:'date', header: 'Оконч. периода', width: 100},
				{name: 'Registry_Count', type: 'int', header: 'Количество', width: 100},
				{name: 'Registry_CountIsBadVol', type: 'int', header: 'Количество с превышением объема', width: 100},
				{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
				{name: 'Registry_NoErrSum', type:'money', header: 'Сумма без ошибок', width: 100},
				{name: 'Registry_SumPaid', type:'money', header: 'Сумма к оплате', width: 100},
				{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
				{name: 'Registry_ErrorCount', hidden: true},
				{name: 'Registry_updDate', type: 'datetimesec', header: 'Дата изменения', width: 110},
				{name: 'Registry_IsNotInsurC',  header: 'Незастрахованные лица', type: 'checkbox', width: 100},
				{name: 'DispClass_Name',  header: 'Тип дисп-ции / медосмотра', width: 200},
				{name: 'Registry_IsNeedReform', type: 'int', hidden: true},
				{name: 'Registry_IsNotInsur', type: 'int', hidden: true},
				//Task# по плану 1.11 Установка отчётного месяца и года реестра + установка номера пачки
				//Для остальных регионов будут пустыми
				{name: 'Registry_pack', type: 'int', hidden: true},

				{name: 'RegistryErrorCom_IsData', type: 'int', hidden: true},
				{name: 'RegistryError_IsData', type: 'int', hidden: true},
				{name: 'RegistryErrorTFOMS_IsData', type: 'int', hidden: true},
				{name: 'RegistryDouble_IsData', type: 'int', hidden: true},
				{name: 'RegistryDataBadVol_IsData', type: 'int', hidden: true},

				{name: 'Registry_orderDate', type: 'date',  hidden: true},
                {name: 'Registry_kd_good', type: 'int',  hidden: true},
                {name: 'Registry_kd_err', type: 'int',  hidden: true},
                {name: 'Registry_Comments', type: 'string',  hidden: true},
				{name: 'Registry_IsNew', type: 'int',  hidden: true, isparams: true},
				{name: 'RegistrySubType_id', type: 'int',  hidden: true},
				{name: 'PayType_SysNick', hidden: true},
				{name: 'RegistryCheckStatus_SysNick', type: 'string', hidden: true},
				{name: 'RegistryCheckStatus_Name', header: 'Статус', width: 200},
				{name: 'RegistryHealDepCheckJournal_AccRecCount', type: 'int', hidden: true},
				{name: 'RegistryHealDepCheckJournal_DecRecCount', type: 'int', hidden: true},
				{name: 'RegistryHealDepCheckJournal_UncRecCount', type: 'int', hidden: true}
			],
			actions:
			[
				{name:'action_add'},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete', url: '/?c=RegistryUfa&m=deleteRegistry'}/*,
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
						var Registry_id = record.get('Registry_id'),
							registryType_id = record.get('RegistryType_id');
						if ( !Registry_id )
							return false;
						var id_salt = Math.random();
						var win_id = 'print_schet' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=RegistryUfa&m=printRegistry&Registry_id=' + Registry_id + '&RegistryType_id=' + registryType_id, win_id);
					}, 
					text: 'Печать счета'
				}*/
			],
			afterDeleteRecord: function() {
				var form = Ext.getCmp('RegistryViewWindow');
				form.DataTab.fireEvent('tabchange', form.DataTab.getActiveTab(), form.DataTab);
			},
			afterSaveEditForm: function(RegistryQueue_id, records)
			{
				var form = Ext.getCmp('RegistryViewWindow');
				var r = records.RegistryQueue_Position;
				form.onIsRunQueue(r);
			},
			//Task# по плану 1.11 Установка отчётного месяца и года реестра + установка номера пачки
			getMoreInfo : function(grid){  
				//Task# по плану 1.11 добавление сведений в вкладку 0 реестра: кол-во койко-мест, сумма принятая, сумма не принятая
			   if(grid.ViewGridPanel.getSelectionModel().getSelected()){
					//log('Данные отправляются!', grid);
					
					
					
					var record = grid.ViewGridPanel.getSelectionModel().getSelected();
					var params = {
							Registry_id : record.get('Registry_id'),
							RegistryType_id : record.get('RegistryType_id')   
					}
			
					Ext.Ajax.request({
							url: '/?c=RegistryUfa&m=getMoreInfoRegistry',
							params: params,
							callback: function(options, success, response) {
								if (success === true) {
									var moreinfo = Ext.util.JSON.decode(response.responseText);

                                    //console.log('>>>>>',moreinfo);
                                    
									grid.moreInfo = {};
									grid.moreInfo = {
                                    	count : moreinfo.data[2]['sum'],
										sum_good : (moreinfo.data[0]['sum'] == null) ? 0 :moreinfo.data[0]['sum'],
										sum_nogood : (moreinfo.data[1]['sum'] == null) ? 0 :moreinfo.data[1]['sum'],
										sum_all : (moreinfo.data[3]['sum']) ? moreinfo.data[3]['sum'] : 0,
                                        reg_state : (moreinfo.data[4]['sum'] == '') ? 'Готов к МЭК' : moreinfo.data[4]['sum'],
                                        reg_kd_good : moreinfo.data[5]['sum'], 
                                        reg_kd_err : moreinfo.data[6]['sum'],
							            reg_type_id : moreinfo.data[7]['sum'],
							            uetOMS : moreinfo.data[8]['uet']
                                    };

									grid.mi_data.Registry_bedSpace = (moreinfo.data[7]['sum'] == 1 || moreinfo.data[7]['sum'] == 14 ) ? moreinfo.data[2]['sum'] : moreinfo.data[10]['sum'];
									grid.mi_data.Registry_sum_good = (moreinfo.data[0]['sum'] == null) ? 0 :moreinfo.data[0]['sum'];
									grid.mi_data.Registry_sum_nogood = (moreinfo.data[1]['sum'] == null) ? 0 :moreinfo.data[1]['sum'];
									grid.mi_data.Registry_sum_all = (moreinfo.data[3]['sum']) ? moreinfo.data[3]['sum'] : 0;
									grid.mi_data.Registry_state = (moreinfo.data[4]['sum'] == '') ? 'Необходим МЭК' : moreinfo.data[4]['sum'];
                                    grid.mi_data.Registry_kd_good = (moreinfo.data[7]['sum'] == 1 || moreinfo.data[7]['sum'] == 14 ) ? moreinfo.data[5]['sum'] : '-'; 
                                    grid.mi_data.Registry_kd_err = (moreinfo.data[7]['sum'] == 1 || moreinfo.data[7]['sum'] == 14 ) ? moreinfo.data[6]['sum'] : '-';
                                    grid.mi_data.Registry_nameCount = (moreinfo.data[7]['sum'] == 1 || moreinfo.data[7]['sum'] == 14 ) ? 'Количество КВС' : 'Кол-во уникальных пациентов';
                                    grid.mi_data.TFOMS_err_evn_count = (moreinfo.data[9]['sum'] == '') ? '0' : moreinfo.data[9]['sum'];
                                    grid.mi_data.uetOMS = (moreinfo.data[8]['uet'] == 0)? '-':moreinfo.data[8]['uet'];

									var Registry_RecordPaidCount = '';
									if (record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])) {
										Registry_RecordPaidCount = '<div style="padding:2px;font-size: 12px;">Количество принятых случаев: '+record.get('RegistryHealDepCheckJournal_AccRecCount')+'</div>';
										Registry_RecordPaidCount += '<div style="padding:2px;font-size: 12px;">Количество отклонённых случаев: '+record.get('RegistryHealDepCheckJournal_DecRecCount')+'</div>';
										Registry_RecordPaidCount += '<div style="padding:2px;font-size: 12px;">Количество непроверенных случаев: '+record.get('RegistryHealDepCheckJournal_UncRecCount')+'</div>';
									}
                                    grid.mi_data.Registry_RecordPaidCount = Registry_RecordPaidCount;

                                      

									var form = Ext.getCmp('RegistryViewWindow');
									
									form.RegistryTpl.overwrite(form.RegistryPanel.body, grid.mi_data);

								}
								else{
									grid.moreInfo = {
										count : 'Не определено',
										sum_good : 'Не определено',
										sum_nogood : 'Не определено'
									}
								}
                        }
			
					});       
				}
				else{           
					grid.moreInfo = {
						count : 'Не определено',
						sum_good : 'Не определено',
						sum_nogood : 'Не определено'
					}
				}
			},            
			//Форматирование даты
			getFormatedDate : function(data){ //shorev https://redmine.swan.perm.ru/issues/82130
				data = Ext.util.Format.date(data,'d.m.Y');
				var d = parseInt(data.substr(0,2));
				var m = parseInt(data.substr(3,2));
				var y = parseInt(data.substr(6,4));
				if( d < 20){
					if(m==1)
					{
						m = 12;
						y--;
					}
					else
						m--;
				}
				switch(m){
					case 1: m = 'Январь'; break;
					case 2: m = 'Февраль'; break;
					case 3: m = 'Март'; break;
					case 4: m = 'Апрель'; break;
					case 5: m = 'Май'; break;
					case 6: m = 'Июнь'; break;
					case 7: m = 'Июль'; break;
					case 8: m = 'Август'; break;
					case 9: m = 'Сентябрь'; break;
					case 10: m = 'Октябрь'; break;
					case 11: m = 'Ноябрь'; break;
					case 12: m = 'Декабрь'; break;
				}

				return '<i>' + m + ' ' + y + '</i>';
			},
			onLoadData: function()
			{   
				//console.debug('ONLOAD ACCOUNT GRID');
				//Local task #378 Корректировка объёмов
				this.addActions({
					name:'action_edit_volume',
					id : 'add_edit_volume',
					hidden: true,
					//disabled : false,
					text:'Корректировка объёмов',
					tooltip: 'Корректировка объёмов',
					iconCls : 'x-btn-text',
					icon: 'img/icons/edit16.png',
					handler: function() {
						//Получили выбранные реестры
						var selections = Ext.getCmp('RegistryViewWindow').AccountGrid.getGrid().getSelectionModel().getSelections();
						var ids = [];

						for(key in selections){
						  if(typeof(selections[key]) == 'object'){
							//log(selections[key].id)
							ids[key] = selections[key].id;
						  }
						}

						Ext.getCmp('RegistryViewWindow').ids_json = Ext.util.JSON.encode(ids);

						var newform = getWnd('swRegistryLimitVolumeDataWindow');
						newform.show();
					}.createDelegate(this)
				});
		
				//Task#18011 Создание дополнительной кнопки в панеле для создания всех реестров сразу 
				this.addActions({
					name: 'action_add_all',
					id: 'add_all_registers',
					hidden: false,
					disabled: false,
					text: 'Группа реестров',
					tooltip: 'Групповое добавление реестров',
					iconCls: 'x-btn-text',
					icon: 'img/icons/add16.png',
					handler: function () {
						getWnd('swRegistryEditWindowVE').show({Registry_IsNew: form.Registry_IsNew});
					}
				});

				this.getAction('action_add_all').setHidden(this.getParam('PayType_SysNick') == 'bud');
				this.getAction('action_add_all').setDisabled(this.getParam('RegistryStatus_id') != 3 || this.getParam('RegistrySubType_id') == 3 || (form.Registry_IsNew != 2 && !isSuperAdmin()));
				this.getAction('action_edit_volume').setDisabled(this.getParam('RegistryStatus_id') != 3 || this.getParam('RegistrySubType_id') == 3);
				//end Task#180011  
				
				if  (this.getAction('action_new'))
				{
					this.getAction('action_new').setDisabled(this.getCount()==0);
				}
				//В соответствие с задачей 16102 закрываем кнопки для неадминов и неповерюзеров в Уфе
				var isLpuPowerUser = getGlobalOptions().groups.toString().indexOf('LpuPowerUser') != -1;
				var isLpuAdmin = getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1;
				if (!isLpuAdmin && !isSuperAdmin() && !isLpuPowerUser)
				{
					this.getAction('action_new').setDisabled(true);
				}
			},
			onRowSelect: function(sm,index,record)
			{  
				//log(this.id+'.onRowSelect');
				var form = Ext.getCmp('RegistryViewWindow');
				if (this.getCount()>0)
				{
					//info
					form.AccountGrid.getMoreInfo(form.AccountGrid); 
					//console.debug('onRowSelect',form.AccountGrid) 
					//console.debug('onRowSelect',form.AccountGrid.moreInfo)  
					var Registry_id = record.get('Registry_id');
					var RegistryType_id = record.get('RegistryType_id');
					var RegistryStatus_id = record.get('RegistryStatus_id');
					var OrgSmo_id = record.get('OrgSmo_id');
					var RegistrySubType_id = record.get('RegistrySubType_id');

					form.onRegistrySelect(Registry_id, RegistryType_id,  false);
					this.setActionDisabled('action_edit',(RegistryStatus_id != 3 || Ext.isEmpty(RegistrySubType_id))); // #61531
					this.setActionDisabled('action_delete',(RegistryStatus_id != 3)); // #61531
					this.setActionDisabled('action_view',false);

					// В прогрессе 
					if (record.get('Registry_IsProgress')==1)
					{
						this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete',true);
						this.setActionDisabled('action_view',true);
					}
					if ((record.get('RegistryStatus_id')==4) || (record.get('RegistryStatus_id')==2))
						this.setActionDisabled('action_delete',true); // не давать удалять если реестр находится в разделе К оплате или Оплаченные #18220

					//В соответствие с задачей 16102 закрываем кнопки для неадминов и неповерюзеров в Уфе
					var isLpuPowerUser = getGlobalOptions().groups.toString().indexOf('LpuPowerUser') != -1;
					var isLpuAdmin = getGlobalOptions().groups.toString().indexOf('LpuAdmin') != -1;
					if (!isLpuAdmin && !isSuperAdmin() && !isLpuPowerUser)
					{
						this.setActionDisabled('action_add',true);
						this.setActionDisabled('action_edit',true);
						this.setActionDisabled('action_delete',true);
						this.setActionDisabled('action_new',true);
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
								form.menuActions.deleteOrgSmoRegistryData.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.refreshRegistryVolumes.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.exportToDbf.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);

								form.menuActions.doMEK.setDisabled(RegistrySubType_id == 3);
								form.menuActions.registrySetPay.setDisabled(RegistrySubType_id == 3);
								form.menuActions.reformRegistry.setDisabled(Ext.isEmpty(RegistrySubType_id));
								form.menuActions.refreshRegistry.setDisabled(RegistrySubType_id == 3);
								form.menuActions.deleteOrgSmoRegistryData.setDisabled(Ext.isEmpty(RegistrySubType_id) || RegistrySubType_id == 3);
								form.menuActions.refreshRegistryVolumes.setDisabled(Ext.isEmpty(RegistrySubType_id));
								form.menuActions.exportToDbf.setDisabled(!RegistryType_id.inlist([ 1, 14 ]));
								form.menuActions.exportToXml.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']) || record.get('RegistryType_id') == 19);
								break;
							case 2: // к оплате
								form.menuActions.registrySign.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']) || record.get('RegistryType_id') == 19);
								form.menuActions.sendRegistryToMZ.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']) || record.get('RegistryType_id') == 19);
								form.menuActions.refreshRegistry.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.registrySetPaid.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.exportToDbf.setHidden((record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])) || record.get('RegistryType_id') == 19);
								form.menuActions.setRegistryAccDate.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.setRegistryPackNum.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.exportRegistryErrorDataToDbf.setHidden((record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])) || record.get('RegistryType_id') == 19);
								form.menuActions.deleteUnionRegistrys.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.checkIncludeInUnioinRegistry.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.deleteOrgSmoRegistryData.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && record.get('RegistryType_id') != 19);
								form.menuActions.importRegistryDBF.setHidden((record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud'])) || record.get('RegistryType_id') == 19);

								form.menuActions.exportToXml.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']) || record.get('RegistryType_id') == 19);
								form.menuActions.exportToXmlCheckVolume.setDisabled(RegistrySubType_id == 1);
								form.menuActions.deleteUnionRegistrys.setDisabled(Ext.isEmpty(RegistrySubType_id));
								form.menuActions.checkIncludeInUnioinRegistry.setDisabled(Ext.isEmpty(RegistrySubType_id));
								form.menuActions.deleteOrgSmoRegistryData.setDisabled(Ext.isEmpty(RegistrySubType_id));
								form.menuActions.registrySign.setDisabled(!Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')));
								form.menuActions.sendRegistryToMZ.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'SignECP');
								form.menuActions.registrySetWork.setDisabled(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']) && !Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) && record.get('RegistryCheckStatus_SysNick') != 'SignECP');
								break;
							case 4: // оплаченные
								form.menuActions.registrySetPay.setHidden(record.get('PayType_SysNick') && record.get('PayType_SysNick').inlist(['bud', 'fbud']));
								form.menuActions.exportToXml.setHidden(!record.get('PayType_SysNick') || !record.get('PayType_SysNick').inlist(['bud', 'fbud']) || record.get('RegistryType_id') == 19);
								break;
							case 6: // проверенные МЗ
								form.menuActions.registrySetPaid.setHidden(false);
								
								form.menuActions.registrySetWork.setDisabled(record.get('RegistryCheckStatus_SysNick') != 'RejectMZ');
								form.menuActions.registrySetPaid.setDisabled(Ext.isEmpty(record.get('RegistryCheckStatus_SysNick')) || !record.get('RegistryCheckStatus_SysNick').inlist(['HalfAcceptMZ', 'AcceptMZ']));
								break;
						}
					}

					//Task# по плану 1.11 Установка отчётного месяца и года реестра + установка номера пачки
                    if(Ext.getCmp('RegistryViewWindow').AccountGrid.moreInfo){
                        //log('>>', Ext.getCmp('RegistryViewWindow').AccountGrid.moreInfo);
                    }

					var Registry_orderDate = this.getFormatedDate(record.get('Registry_accDate')); //shorev https://redmine.swan.perm.ru/issues/82130
					var data = {
						Registry_Num: record.get('Registry_Num'), 
						Registry_state :'...',
						Registry_begDate: Ext.util.Format.date(record.get('Registry_begDate'),'d.m.Y'), 
						Registry_endDate: Ext.util.Format.date(record.get('Registry_endDate'),'d.m.Y'), 
						Registry_accDate: Ext.util.Format.date(record.get('Registry_accDate'),'d.m.Y'),
                        Registry_insDT: Ext.util.Format.date(record.get('Registry_insDT'),'d.m.Y'),
						ReformTime:record.get('ReformTime'),
						Registry_Count: record.get('Registry_Count'), 
						Registry_ErrorCount: record.get('Registry_ErrorCount'), 
						Registry_NoErrorCount: record.get('Registry_Count') - record.get('Registry_ErrorCount'), 
						Registry_Sum: record.get('Registry_Sum'),
						Registry_IsNeedReform: record.get('Registry_IsNeedReform'),
						Registry_pack: record.get('Registry_pack'),
						Registry_orderDate:  Registry_orderDate,//(record.get('Registry_orderDate') && record.get('Registry_orderDate').getTime() == record.get('Registry_endDate').getTime())?this.getFormatedDate(record.get('Registry_orderDate')) + ' (дкп)': this.getFormatedDate(record.get('Registry_orderDate')),
						Registry_kd_good : record.get('Registry_kd_good'),
						Registry_kd_err : record.get('Registry_kd_err'),
						Registry_Comments : record.get('Registry_Comments'),
						Registry_nameCount : record.get('Registry_nameCount')
					}

					if (
						(record.get('Registry_pack') == 1 && RegistryType_id.inlist([1,2,6,17]))
						|| (record.get('Registry_pack') == 8 && RegistryType_id.inlist([9]))
						|| (record.get('Registry_pack') == 9 && RegistryType_id.inlist([4,7,14]))
					) {
						data.Registry_pack = data.Registry_pack + ' (<i>по умолчанию</i>)';
					}

					this.mi_data = data;

					form.RegistryPanel.show();
				}
				else 
				{
					//this.setActionDisabled('action_print',true);
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
						case 'tab_datatfomserr':
							form.TFOMSErrorGrid.removeAll(true);
							break;
						case 'tab_datamzerr':
							form.RegistryHealDepResErrGrid.removeAll(true);
							break;
						case 'tab_datavizitdouble':
							form.DoubleVizitGrid.removeAll(true);
							break;
						case 'tab_databadvol':
							form.DataBadVolGrid.removeAll(true);
							break;
					}
				}

				// информируем о данных на вкладках
				form.DataTab.getItem('tab_registry').setIconClass((record.get('Registry_IsNeedReform')==2)?'delete16':'info16');

				form.DataTab.getItem('tab_commonerr').setIconClass((record.get('RegistryErrorCom_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_dataerr').setIconClass((record.get('RegistryError_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datatfomserr').setIconClass((record.get('RegistryErrorTFOMS_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datamzerr').setIconClass((record.get('RegistryErrorMZ_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_datavizitdouble').setIconClass((record.get('RegistryDouble_IsData')==1)?'usluga-notok16':'good');
				form.DataTab.getItem('tab_databadvol').setIconClass((record.get('RegistryDataBadVol_IsData')==1)?'usluga-notok16':'good');
			}
		});

		this.AccountGrid.ViewGridModel.singleSelect = false;

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
 
		var addColumn = 
            //'<div style="padding:4px;">Состояние: {Registry_state}</div>' +
            '<div style="padding:4px;">Количество записей по результатам проверки ТФОМС: {TFOMS_err_evn_count}</div>' +
			'<div style="padding:4px;" id="micount">{Registry_nameCount}: {Registry_bedSpace}</div>' +
			'<div style="padding:4px;" id="uet">Количество УЕТ: <span style="color:green">{uetOMS}</span></div>' +
			'<div style="padding:4px;" id="misum_good">Сумма принята: <span style="color:green">{Registry_sum_good}</span></div>' +
			'<div style="padding:4px;" id="misum_nogood">Сумма не принята: <span style="color:red">{Registry_sum_nogood}</span></div>' +
			'<div style="padding:4px;" id="misum_all">Общая сумма: {Registry_sum_all}</div>' +
            '<div style="padding:4px;">Принятых к/д : <span style="color:green">{Registry_kd_good}</span></div>' +
            '<div style="padding:4px;">Не принятых к/д : <span style="color:red">{Registry_kd_err}</span></div>' +
			'<div style="padding:4px;">Номер пачки: {Registry_pack}</div>' +
			'<div style="padding:4px;">Отчётный месяц / год: {Registry_orderDate}</div>' +
			'<div style="padding:4px;">Комментарий: {Registry_Comments}</div>';

		var RegTplMark = 
		[
			'<div style="padding:4px;font-weight:bold;">Реестр № {Registry_Num}<tpl if="Registry_IsNeedReform == 2"> <span style="color: red;">(НУЖДАЕТСЯ В ПЕРЕФОРМИРОВАНИИ!)</span></tpl></div>'+
			'<div style="padding:4px;">Дата формирования: {Registry_insDT}</div>'+
			'<div style="padding:4px;">Дата начала периода: {Registry_begDate}</div>'+
			'<div style="padding:4px;">Дата окончания периода: {Registry_endDate}</div>'+
			'<div style="padding:4px">Дата переформирования реестра: {Registry_accDate}</div>'+
			'<div style="padding:4px;">Количество записей в реестре: {Registry_Count}</div>'+
			'<div style="padding:4px;">Количество записей с ошибками данных: {Registry_ErrorCount}</div>'+
			'<div style="padding:4px;">Записей без ошибок: {Registry_NoErrorCount}</div>' +
			'{Registry_RecordPaidCount}' + addColumn
			
		];
		this.RegistryTpl = new Ext.XTemplate(RegTplMark);
		
		this.RegistryPanel = new Ext.Panel(
		{
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
						VolumeType_id:filtersForm.findField('VolumeType_id').getValue(),
						OrgSmo_id:filtersForm.findField('OrgSmo_id').getValue(),
						filterRecords: filtersForm.findField('filterRecords').getValue(),
						filterIsEarlier: filtersForm.findField('filterIsEarlier').getValue(),
						filterIsZNO: filtersForm.findField('filterIsZNO').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						start: 0,
						limit: 100
					}, 
					noFocusOnLoad:false
				});
			}
		}

		this.DataBadVolGridSearch = function()
		{
			var form = this;
			var filtersForm = form.RegistryDataBadVolFiltersPanel.getForm();

			var registry = form.AccountGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			var RegistryType_id = registry.get('RegistryType_id');
			if (Registry_id > 0)
			{
				form.DataBadVolGrid.loadData(
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
						VolumeType_id:filtersForm.findField('VolumeType_id').getValue(),
						OrgSmo_id:filtersForm.findField('OrgSmo_id').getValue(),
						filterIsEarlier: filtersForm.findField('filterIsEarlier').getValue(),
						filterIsZNO: filtersForm.findField('filterIsZNO').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
						start: 0,
						limit: 100
					},
					noFocusOnLoad:false
				});
			}
		}

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
							Person_SurName:filtersForm.findField('Person_SurName').getValue(),
							Person_FirName:filtersForm.findField('Person_FirName').getValue(),
							Person_SecName:filtersForm.findField('Person_SecName').getValue(),
							MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(),
							LpuSection_id:filtersForm.findField('LpuSection_id').getValue(),
							LpuSectionProfile_id:filtersForm.findField('LpuSectionProfile_id').getValue(),
							NumCard:filtersForm.findField('NumCard').getValue(),
							Polis_Num: filtersForm.findField('Polis_Num').getValue(),
							filterRecords: filtersForm.findField('filterRecords').getValue(),
							filterIsEarlier: filtersForm.findField('filterIsEarlier').getValue(),
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
			form.UnionDataGridSearch();
		};

		this.DataGridReset = function(){
			var form = this;
			var filtersForm = form.RegistryDataFiltersPanel.getForm();
			filtersForm.reset();
			form.UnionDataGrid.removeAll(true);
			form.DataGridSearch();
		};

		this.RegistryDataFiltersPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			//title: 'Ввод',
			height: 110,
			id: 'RegistryDataFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					Ext.getCmp('RegistryViewWindow').DataGridSearch();
				},
				stopEvent: true
			}],
			listeners: {
				'render': function () {
					swLpuBuildingGlobalStore.clearFilter();
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .25,
					labelWidth: 110,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						linkedElements: [],
						tabIndex: form.firstTabIndex++,
						xtype: 'swlpubuildingglobalcombo'
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
						tabIndex: form.firstTabIndex++,
						withoutTrigger: true,
						xtype: 'sworgsmocombo'
					}, {
						anchor: '100%',
						xtype: 'combo',
						listWidth: 200,
						fieldLabel: 'Подавался ранее',
						name: 'filterIsEarlier',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да'],
							[3, 'Нет']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .25,
					labelWidth: 50,
					items: [{
						anchor: '90%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '90%',
						hiddenName: 'MedPersonal_id',
						lastQuery: '',
						listWidth: 650,
						tabIndex: form.firstTabIndex++,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}, {
						anchor: '90%',
						allowBlank: true,
						fieldLabel: 'Объём',
						hiddenName: 'VolumeType_id',
						editable: true,
						triggerAction: 'all',
						forceSelection: true,
						listWidth: 400,
						comboSubject: 'VolumeType',
						tabIndex: form.firstTabIndex++,
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '90%',
						xtype: 'combo',
						listWidth: 200,
						fieldLabel: 'ЗНО',
						name: 'filterIsZNO',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					},{
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					//columnWidth: .05,
					items: [{
						xtype: 'checkbox',
						boxLabel: langs('Группировать по законченным случаям'),
						hideLabel: true,
						tabIndex: form.firstTabIndex++,
						listeners: {
							check: function (cmp, checked) {
								if(checked){
									form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
								}else{
									form.DataGrid.getGrid().getStore().clearGrouping()
								}
								form.DataGrid.showGroup = checked;
								form.DataGrid.getGrid().view.refresh(true);
							}
						}
					}, {
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex++,
						disabled: false,
						handler: function () {
							form.DataGridSearch();
						}
					}, {
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex++,
						disabled: false,
						handler: function () {
							form.DataGridReset();
						}
					}]
				}]
			}]
		});

		this.RegistryDataBadVolFiltersPanel = new Ext.form.FormPanel({
			border: true,
			collapsible: false,
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			//title: 'Ввод',
			height: 110,
			id: 'RegistryDataBadVolFiltersPanel',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					Ext.getCmp('RegistryViewWindow').DataBadVolGridSearch();
				},
				stopEvent: true
			}],
			listeners: {
				'render': function () {
					swLpuBuildingGlobalStore.clearFilter();
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
					this.getForm().findField('MedPersonal_id').getStore().loadData(getMedPersonalListFromGlobal());
				}
			},
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; padding-top: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .25,
					labelWidth: 110,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						linkedElements: [],
						tabIndex: form.firstTabIndex++,
						xtype: 'swlpubuildingglobalcombo'
					}, {
						anchor: '100%',
						allowBlank: true,
						fieldLabel: 'СМО',
						listeners: {
							'render': function() {
								form.filterOrgSMOComboBadVol();
							}
						},
						hiddenName: 'OrgSmo_id',
						editable: true,
						triggerAction: 'all',
						forceSelection: true,
						listWidth: 400,
						tabIndex: form.firstTabIndex++,
						withoutTrigger: true,
						xtype: 'sworgsmocombo'
					}, {
						anchor: '100%',
						xtype: 'combo',
						listWidth: 200,
						fieldLabel: 'Подавался ранее',
						name: 'filterIsEarlier',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да'],
							[3, 'Нет']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .25,
					labelWidth: 50,
					items: [{
						anchor: '90%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '90%',
						hiddenName: 'MedPersonal_id',
						lastQuery: '',
						listWidth: 650,
						tabIndex: form.firstTabIndex++,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}, {
						anchor: '90%',
						allowBlank: true,
						fieldLabel: 'Объём',
						hiddenName: 'VolumeType_id',
						editable: true,
						triggerAction: 'all',
						forceSelection: true,
						listWidth: 400,
						comboSubject: 'VolumeType',
						tabIndex: form.firstTabIndex++,
						xtype: 'swcommonsprcombo'
					}, {
						anchor: '90%',
						xtype: 'combo',
						fieldLabel: 'ЗНО',
						name: 'filterIsZNO',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}, {
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 60,
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex++,
						disabled: false,
						handler: function () {
							form.DataBadVolGridSearch();
						}
					}]
				}]
			}]
		});

		this.UnionRegistryDataFiltersPanel = new Ext.form.FormPanel({
			bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 110,
			//title: 'Ввод',
			listeners: {
				render: function () {
					setLpuSectionGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
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
				layout: 'column',
				border: false,
				bodyStyle: 'background:transparent;',
				defaults: {bodyStyle: 'padding-top: 4px; padding-left: 4px; background:transparent;'},
				labelAlign: 'right',
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 110,
					items:
						[{
							anchor: '100%',
							fieldLabel: 'Фамилия',
							name: 'Person_SurName',
							xtype: 'textfieldpmw',
							tabIndex:form.firstTabIndex++
						}]
				}, {
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
							tabIndex:form.firstTabIndex++
						}]
				}, {
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
							tabIndex:form.firstTabIndex++
						}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .3,
					labelWidth: 100,
					labelAlign: 'right',
					items: [{
						anchor: '100%',
						fieldLabel: 'Номер карты',
						name: 'NumCard',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
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
					labelWidth: 110,
					labelAlign: 'right',
					items: [{
						anchor: '100%',
						fieldLabel: 'Отделение',
						listWidth: 400,
						hiddenName: 'LpuSection_id',
						xtype: 'swlpusectioncombo',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						xtype: 'combo',
						listWidth: 200,
						fieldLabel: 'Подавался ранее',
						name: 'filterIsEarlier',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да'],
							[3, 'Нет']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++
					}, {
						anchor: '100%',
						allowBlank: true,
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: 'ИД случая',
						name: 'Evn_id',
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					},{
						xtype: 'checkbox',
						boxLabel: langs('Группировать по законченным случаям'),
						hideLabel: true,
						tabIndex: form.firstTabIndex++,
						listeners: {
							check: function (cmp, checked) {
								if(checked){
									form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
								}else{
									form.DataGrid.getGrid().getStore().clearGrouping()
								}
								form.UnionDataGrid.showGroup = checked;
								form.UnionDataGrid.getGrid().view.refresh(true);
							}
						}
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
						tabIndex: form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 100,
					labelAlign: 'right',
					items: [{
						tooltip: BTN_FRMSEARCH_TIP,
						xtype: 'button',
						text: BTN_FRMSEARCH,
						icon: 'img/icons/search16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex++,
						disabled: false,
						handler: function () {
							form.UnionDataGridSearch();
						}
					}, {
						xtype: 'button',
						text: BTN_FRMRESET,
						icon: 'img/icons/reset16.png',
						iconCls: 'x-btn-text',
						tabIndex: form.firstTabIndex++,
						disabled: false,
						handler: function () {
							form.UnionDataGridReset();
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
						Registry_id:Registry_id, 
						RegistryType_id:RegistryType_id, 
						Person_SurName:filtersForm.findField('Person_SurName').getValue(), 
						Person_FirName:filtersForm.findField('Person_FirName').getValue(), 
						Person_SecName:filtersForm.findField('Person_SecName').getValue(),
						RegistryError_Code:filtersForm.findField('RegistryError_Code').getValue(),
						LpuBuilding_id:filtersForm.findField('LpuBuilding_id').getValue(), 
						MedPersonal_id:filtersForm.findField('MedPersonal_id').getValue(), 
						Evn_id:filtersForm.findField('Evn_id').getValue(), 
						filterIsZNO: filtersForm.findField('filterIsZNO').getValue(),
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
		rvwREBtnSearch.tabIndex = form.firstTabIndex++;
		
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
					swLpuBuildingGlobalStore.clearFilter();
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
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
						tabIndex:form.firstTabIndex++
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
						tabIndex:form.firstTabIndex++
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
						tabIndex:form.firstTabIndex++
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
						tabIndex:form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++,
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
						tabIndex: form.firstTabIndex++,
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
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 30,
					items: [{
						anchor: '95%',
						xtype: 'combo',
						fieldLabel: 'ЗНО',
						name: 'filterIsZNO',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
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
						filterIsZNO: filtersForm.findField('filterIsZNO').getValue(),
						Person_SurName:filtersForm.findField('Person_SurName').getValue(),
						Person_FirName:filtersForm.findField('Person_FirName').getValue(),
						Person_SecName:filtersForm.findField('Person_SecName').getValue(),
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
		rvwDVBtnSearch.tabIndex = form.firstTabIndex++;
		
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
					swLpuBuildingGlobalStore.clearFilter();
					setMedStaffFactGlobalStoreFilter({
						Lpu_id: getGlobalOptions().lpu_id
					});
					this.getForm().findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));
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
					columnWidth: .17,
					labelWidth: 60,
					items: [{
						anchor: '100%',
						fieldLabel: 'Фамилия',
						name: 'Person_SurName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .1,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						fieldLabel: 'Имя',
						name: 'Person_FirName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .17,
					labelWidth: 60,
					items: [{
						anchor: '100%',
						fieldLabel: 'Отчество',
						name: 'Person_SecName',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				},
				{
					layout: 'form',
					border: false,
					columnWidth: .25,
					labelWidth: 100,
					items: [{
						anchor: '100%',
						hiddenName: 'LpuBuilding_id',
						fieldLabel: 'Подразделение',
						linkedElements: [],
						tabIndex: form.firstTabIndex++,
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
						tabIndex: form.firstTabIndex++,
						allowBlank: true,
						xtype: 'swmedpersonalcombo'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .1,
					labelWidth: 30,
					items: [{
						anchor: '95%',
						xtype: 'combo',
						fieldLabel: 'ЗНО',
						name: 'filterIsZNO',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					bodyStyle:'padding-left: 4px;background:#DFE8F6;',
					border: false,
					columnWidth: .1,
					items: [rvwDVBtnSearch]
				}]
			}]
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
							filterIsZNO: filtersForm.findField('filterIsZNO').getValue(),
							Polis_Num: filtersForm.findField('Polis_Num').getValue(),
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

		this.UnionTFOMSGridSearch = function () {
			var form = this;
			var filtersForm = form.UnionRegistryTFOMSFiltersPanel.getForm();

			var registry = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
			var Registry_id = registry.get('Registry_id');
			if (Registry_id > 0) {
				form.UnionTFOMSErrorGrid.loadData({
					globalFilters: {
						Person_FIO: filtersForm.findField('Person_FIO').getValue(),
						RegistryErrorType_Code: filtersForm.findField('TFOMSError').getValue(),
						Evn_id: filtersForm.findField('Evn_id').getValue(),
						Polis_Num: filtersForm.findField('Polis_Num').getValue(),
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
			handler: function () {
				Ext.getCmp('RegistryViewWindow').TFOMSGridSearch();
			}
		});
		rvwTFOMSBtnSearch.tabIndex = form.firstTabIndex++;

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
						tabIndex:form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 90,
					items:
					[{
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex:form.firstTabIndex++
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
						tabIndex:form.firstTabIndex++
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
						tabIndex: form.firstTabIndex++,
						xtype: 'numberfield'
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
					labelWidth: 30,
					items: [{
						anchor: '100%',
						xtype: 'combo',
						fieldLabel: 'ЗНО',
						name: 'filterIsZNO',
						triggerAction: 'all',
						forceSelection: true,
						editable: false,
						store: [
							[1, 'Все случаи'],
							[2, 'Да']
						],
						allowBlank: false,
						value: 1,
						tabIndex: form.firstTabIndex++
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

		this.UnionRegistryTFOMSFiltersPanel = new Ext.form.FormPanel({
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: true,
			collapsible: false,
			region: 'north',
			layout: 'form',
			height: 30,
			//title: 'Ввод',
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function (e) {
					form.UnionTFOMSGridSearch();
				},
				stopEvent: true
			}],
			items: [{
				layout: 'column',
				border: false,
				bodyStyle: 'width:100%;background:#DFE8F6;padding:0px;',
				defaults: {bodyStyle: 'padding-left: 4px; background:#DFE8F6;'},
				items: [{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 40,
					items: [{
						anchor: '100%',
						fieldLabel: 'ФИО',
						name: 'Person_FIO',
						xtype: 'textfieldpmw',
						tabIndex: form.firstTabIndex++
					}]
				},{
					layout: 'form',
					border: false,
					columnWidth: .15,
					labelWidth: 90,
					items: [{
						anchor: '100%',
						fieldLabel: 'Номер полиса',
						name: 'Polis_Num',
						xtype: 'textfield',
						tabIndex: form.firstTabIndex++
					}]
				}, {
					layout: 'form',
					border: false,
					columnWidth: .20,
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
					columnWidth: .20,
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
				},
					{
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

		this.DataGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'Data',
			title:'Данные',
			object: 'RegistryData',
			region: 'center',
			//editformclassname: 'swLpuSectionShiftEditForm',
			dataUrl: '/?c=RegistryUfa&m=loadRegistryData',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			grouping: true,
			showGroup: false,
			split: true,
			region: 'center',
			selectionModel: 'multiselect',
			groupSortInfo: {
				field: 'MaxEvn_id'
			},
			stringfields:
			[
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
				{name: 'Polis_Num', type: 'string', header: 'Номер полиса', width: 80},
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
				{name: 'VolumeType_Code', header: 'Код объёма', width: 150},
				{name: 'VolumeType_Code2', header: 'Код объёма_2', width: 150},
				{name: 'Usluga_Code', header: 'Код посещения', width: 80},
				{name: 'UslugaComplex_Code', header: 'Код услуги', width: 80},
				{name: 'UslugaComplex_Name', header: 'Наименование услуги', width: 150},
				{name: 'HTMedicalCareClass_GroupCode', header: 'Группа ВМП', width: 150},
				{name: 'HTMedicalCareClass_Name', header: 'Метод ВМП', width: 150},
				{name: 'RegistryData_Sum_R', header: 'Сумма', type: 'money', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'MedPersonal_LabFIO', header: 'Лаборант', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'УЕТ', type: 'float', width: 60},
				{name: 'Paid', header: 'Оплата', width: 60},
				{name: 'OrgSmo_Nick', header: 'СМО', width: 200},
				{name: 'Mes_Code_KSG', header: 'КСГ', width: 80},
				{name: 'Mes_Code_KPG', header: 'КПГ', width: 60},
				{name: 'Mes_Code', header: 'МЭС', width: 80},
				{name: 'RegistryData_IsEarlier', renderer: function(value, cellEl, rec) {
					if (value >= 2) {
						return 'Да';
					} else if (value == 1) {
						return 'Нет';
					}

					return '';
				}, header: 'Подавался ранее', width: 80},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'Err_Count', hidden:true},
				{name: 'MaxEvn_id', type: 'int', hidden:true, group: true}
			],
			//Task# Групповое исключение записей из реестра
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
					
					var action_delete_all_records_in_filter = {
						name:'action_delete_all_records_in_filter',
						text:'Удалить все (после фильтра)',
						icon: 'img/icons/delete16.png',
						handler: this.deleteGroupRegistryData.createDelegate(this, [grid, 'deleteAllSelected_in_filter'])
					};                        
					if(!grid.getAction('action_delete_all_records_in_filter')){                            
						grid.ViewActions[action_delete_all_records_in_filter.name] = new Ext.Action(action_delete_all_records_in_filter);
						grid.ViewContextMenu.addSeparator();
						grid.ViewContextMenu.add(grid.ViewActions[action_delete_all_records_in_filter.name]);
					}
			   
					var action_undelete_all_records_in_filter = {
						name:'action_undelete_all_records_in_filter',
						text:'Восстановить все (после фильтра)',
						icon: 'img/icons/refresh16.png',
						handler: this.deleteGroupRegistryData.createDelegate(this, [grid, 'unDeleteAllSelected_in_filter'])
					};
					if(!form.DataGrid.getAction('action_undelete_all_records_in_filter')){     
						form.DataGrid.ViewActions[action_undelete_all_records_in_filter.name] = new Ext.Action(action_undelete_all_records_in_filter);
						form.DataGrid.ViewContextMenu.add(form.DataGrid.ViewActions[action_undelete_all_records_in_filter.name]);                        
					}
					   /**
					if(!form.DataGrid.getAction('action_delete_all_records_in_filter')){
						var action_delete_all_records_in_filter = {
							name:'action_delete_all_records_in_filter',
							text:'Удалить все (после фильтра)',
							icon: 'img/icons/delete16.png',
							handler: function(){
										 form.deleteGroupRegistryData.createDelegate(form.DataGrid, 'deleteAllFiltered')
							}
						};
						form.DataGrid.ViewActions[action_delete_all_records_in_filter.name] = new Ext.Action(action_delete_all_records_in_filter);
						form.DataGrid.ViewContextMenu.addSeparator();
						form.DataGrid.ViewContextMenu.add(form.DataGrid.ViewActions[action_delete_all_records_in_filter.name]);
					}
					if(!form.DataGrid.getAction('action_undelete_all_records_in_filter')){
						var action_undelete_all_records_in_filter = {
							name:'action_undelete_all_records_in_filter',
							text:'Восстановить все',
							icon: 'img/icons/refresh16.png',
							handler: function(){
										 form.deleteGroupRegistryData.createDelegate(form.DataGrid, 'unDeleteAllFiltered')
							}
						};
						form.DataGrid.ViewActions[action_undelete_all_records_in_filter.name] = new Ext.Action(action_undelete_all_records_in_filter);
						form.DataGrid.ViewContextMenu.add(form.DataGrid.ViewActions[action_undelete_all_records_in_filter.name]);
					}
					*/
				}.createDelegate(this)
			},                    
			actions:
			[    
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() { form.openForm(form.DataGrid, {});}},
				{name:'action_view', disabled: true},
				{name:'action_delete', handler: function() { form.deleteGroupRegistryData(form.DataGrid, 'delete'); } },
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryData(); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {form.openForm(form.DataGrid, {}, 'OpenPerson');}},
				{name:'action_setisbadvol', tooltip: 'Добавить превышение объёма МП', text: 'Добавить превышение объёма МП', handler: function() {form.setIsBadVol('DataGrid', 2);}}

			],
			onLoadData: function()
			{
				var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',((record && record.get('RegistrySubType_id') == 3) || RegistryStatus_id!=3));
				this.setActionDisabled('action_delete_all_records',((record && record.get('RegistrySubType_id') == 3) || RegistryStatus_id!=3));
				this.setActionDisabled('action_undelete_all_records',true);
				this.setActionDisabled('action_delete_all_records_in_filter',((record && record.get('RegistrySubType_id') == 3) || RegistryStatus_id!=3));
				this.setActionDisabled('action_undelete_all_records_in_filter',((record && record.get('RegistrySubType_id') == 3) || RegistryStatus_id!=3));
				if(this.showGroup){
					form.DataGrid.getGrid().getStore().groupBy('MaxEvn_id')
				}else{
					form.DataGrid.getGrid().getStore().clearGrouping()
				}
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

				// Доступно для реестров без СМО в статусе "В работе"
				var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				this.setActionDisabled('action_setisbadvol', ((record && record.get('RegistrySubType_id') == 3) || (record && record.get('OrgSmo_id')) || (record.get('RegistryStatus_id') != 3)));
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

		
		//Task Исключение группы записей из реестра
		this.DataGrid.ViewGridModel.singleSelect = false; 
		 
		//Task# фильтр грида
                //Интеграция фильтра к Grid            задача  #86094
                columnsFilter = ['Evn_id', 'EvnPL_NumCard', 'Person_FIO', 'VolumeType_Code', 'Usluga_Code', 'Diag_Code', 'LpuSection_name', 'LpuSectionProfile_Name', 'LpuBuilding_Name', 'MedPersonal_Fio', 'MedSpecOms_Name', 'Paid', 'OrgSmo_Nick', 'Mes_Code', 'Polis_Num'];
                configParams = {url: '/?c=RegistryUfa&m=loadRegistryDataFilter'}
                _addFilterToGrid(this.DataGrid, columnsFilter, configParams);
                //

		this.DataGrid.getGrid().view = new Ext.grid.GroupingView({
			//forceFit:true,
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Err_Count') > 0  || row.get('RegistryHealDepResType_id') == 2)
					cls = cls+'x-grid-rowred ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_IsEarlier') == 3) {
					cls = cls+'x-grid-rowgreen ';
				} else if (row.get('RegistryData_IsEarlier') == 2) {
					cls = cls+'x-grid-roworange ';
				}
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
		this.DataBadVolGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'DataBadVol',
			title:'Превышение объёма МП',
			object: 'RegistryData',
			region: 'center',
			dataUrl: '/?c=RegistryUfa&m=loadRegistryDataBadVol',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields:
			[
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
				{name: 'Polis_Num', type: 'string', header: 'Номер полиса', width: 80},
				{name: 'VolumeType_Code', header: 'Код превышенного объёма', width: 150},
				{name: 'VolumeType_Code2', header: 'Код объёма_2', width: 150},
				{name: 'Usluga_Code', header: 'Код посещения', width: 80, hidden: true},
				{name: 'HTMedicalCareClass_GroupCode', header: 'Группа ВМП', width: 150, hidden: true},
				{name: 'HTMedicalCareClass_Name', header: 'Метод ВМП', width: 150, hidden: true},
				{name: 'RegistryData_Sum_R', header: 'Сумма', type: 'money', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'К/д факт', width: 60},
				{name: 'Paid', header: 'Оплата', width: 60, hidden: true},
				{name: 'OrgSmo_Nick', header: 'СМО', width: 200},
				{name: 'Mes_Code_KSG', header: 'КСГ', width: 80, hidden: true},
				{name: 'Mes_Code_KPG', header: 'КПГ', width: 60, hidden: true},
				{name: 'Mes_Code', header: 'МЭС', width: 80, hidden: true},
				{name: 'RegistryData_IsEarlier', renderer: function(value, cellEl, rec) {
					if (value >= 2) {
						return 'Да';
					} else if (value == 1) {
						return 'Нет';
					}

					return '';
				}, header: 'Подавался ранее', width: 80},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'Err_Count', hidden:true}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() { form.openForm(form.DataBadVolGrid, {});}},
				{name:'action_view', disabled: true},
				{name:'action_delete', handler: function() { form.deleteGroupRegistryData(form.DataBadVolGrid, 'delete'); } },
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryData(); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DataBadVolGrid, {}, 'OpenPerson');}},
				{name:'action_setisbadvol', tooltip: 'Снять превышение объёма МП', text: 'Снять превышение объёма МП', handler: function() {form.setIsBadVol('DataBadVolGrid', 1);}}

			],
			onLoadData: function()
			{
				var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete',((record && record.get('RegistrySubType_id') == 3) || RegistryStatus_id!=3));
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				// Меняем текст акшена удаления в зависимости от данных
				form.DataBadVolGrid.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить':'Удалить');

				// Доступно для реестров без СМО в статусе "В работе"
				var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
				this.setActionDisabled('action_setisbadvol', ((record && record.get('RegistrySubType_id') == 3) || (record && record.get('OrgSmo_id')) || (record.get('RegistryStatus_id') != 3)));
			}
		});

		this.DataBadVolGrid.ViewGridModel.singleSelect = false;
                //задача #86094
                columnsFilter = ['Evn_id', 'Evn_rid', 'Registry_id', 'EvnClass_id', 'DispClass_id', 'RegistryType_id', 'Person_id', 'Server_id', 'EvnPL_NumCard', 'Person_FIO',
                    'VolumeType_Code', 'Usluga_Code', 'HTMedicalCareClass_GroupCode', 'HTMedicalCareClass_Name', 'RegistryData_Sum_R', 'Diag_Code',
                    'LpuSection_name', 'LpuSectionProfile_Name', 'LpuBuilding_Name', 'MedPersonal_Fio', 'MedSpecOms_Name', 'RegistryData_Uet', 'Paid', 'OrgSmo_Nick', 'Mes_Code_KSG', 'Mes_Code_KPG',
                    'Mes_Code', 'RegistryData_deleted', 'Err_Count', 'Polis_Num'];
                configParams = {url: '/?c=RegistryUfa&m=loadRegistryDataBadVolFilter'}
                _addFilterToGrid(this.DataBadVolGrid, columnsFilter, configParams);
                //
		this.DataBadVolGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Err_Count') > 0)
					cls = cls+'x-grid-rowred ';
				if (row.get('RegistryData_deleted') == 2)
					cls = cls+'x-grid-rowdeleted ';
				if (row.get('RegistryData_IsEarlier') == 3) {
					cls = cls+'x-grid-rowgreen ';
				} else if (row.get('RegistryData_IsEarlier') == 2) {
					cls = cls+'x-grid-roworange ';
				}
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

		// Данные реестра
		this.UnionDataGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionData',
			title: 'Данные',
			object: 'RegistryData',
			region: 'center',
			//editformclassname: 'swLpuSectionShiftEditForm',
			dataUrl: '/?c=RegistryUfa&m=loadUnionRegistryData',
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
				{name: 'Polis_Num', type: 'string', header: 'Номер полиса', width: 80},
				{name: 'VolumeType_Code', header: 'Код объёма', width: 150},
				{name: 'VolumeType_Code2', header: 'Код объёма_2', width: 150},
				{name: 'Usluga_Code', header: 'Код посещения', width: 80},
				{name: 'UslugaComplex_Code', header: 'Код услуги', width: 80},
				{name: 'UslugaComplex_Name', header: 'Наименование услуги', width: 200},
				{name: 'HTMedicalCareClass_GroupCode', header: 'Группа ВМП', width: 150},
				{name: 'HTMedicalCareClass_Name', header: 'Метод ВМП', width: 150},
				{name: 'RegistryData_Sum_R', header: 'Сумма', type: 'money', width: 80},
				{name: 'Diag_Code', header: 'Код диагноза', width: 80},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'MedPersonal_LabFIO', header: 'Лаборант', width: 200},
				{name: 'EvnVizitPL_setDate', type: 'date', header: 'Посещение', width: 80},
				{name: 'Evn_disDate', type: 'date', header: 'Выписка', width: 80},
				{name: 'RegistryData_Uet', header: 'УЕТ', type: 'float', width: 60},
				{name: 'Paid', header: 'Оплата', width: 60},
				{name: 'Mes_Code_KSG', header: 'КСГ', width: 80},
				{name: 'Mes_Code_KPG', header: 'КПГ', width: 60},
				{name: 'Mes_Code', header: 'МЭС', width: 80},
				{name: 'RegistryData_IsEarlier', renderer: function(value, cellEl, rec) {
					if (value >= 2) {
						return 'Да';
					} else if (value == 1) {
						return 'Нет';
					}

					return '';
				}, header: 'Подавался ранее', width: 80},
				{name: 'RegistryData_deleted', hidden:true},
				{name: 'Err_Count', hidden:true},
				{name: 'MaxEvn_id', type: 'int', hidden:true, group: true}
			],
			actions:
				[
					{name:'action_add', disabled: true, hidden: true },
					{name:'action_edit', handler: function() { form.openForm(form.UnionDataGrid, {});}},
					{name:'action_view', disabled: true, hidden: true },
					{name:'action_delete', disabled: true, hidden: true },
					{name:'action_print', text:'Печатать текущую страницу'},
					{name:'action_setisbadvol', tooltip: 'Добавить превышение объёма МП', text: 'Добавить превышение объёма МП', handler: function() {form.setIsBadVol('UnionDataGrid', 2);}}
				],
			onLoadData: function()
			{
			},
			onRowSelect: function(sm,rowIdx,record)
			{
				// Доступно для объединённых реестров в статусе "В работе"
				var record = form.UnionRegistryGrid.getGrid().getSelectionModel().getSelected();
				this.setActionDisabled('action_setisbadvol', record.get('RegistryStatus_id') != 3);
			}
		});

		this.UnionDataGrid.ViewGridModel.singleSelect = false;

                //задача #86094
                columnsFilter = ['Evn_id', 'Evn_rid', 'Registry_id', 'EvnClass_id', 'DispClass_id', 'RegistryType_id', 'Person_id', 'Server_id', 'EvnPL_NumCard', 'Person_FIO',
                    'VolumeType_Code', 'Usluga_Code', 'HTMedicalCareClass_GroupCode', 'HTMedicalCareClass_Name', 'RegistryData_Sum_R', 'Diag_Code', 'LpuSection_name', 'LpuSectionProfile_Name',
                    'LpuBuilding_Name', 'MedPersonal_Fio', 'MedSpecOms_Name', 'RegistryData_Uet', 'Paid', 'Mes_Code_KSG', 'Mes_Code_KPG', 'Mes_Code', 'RegistryData_deleted', 'Err_Count', 'Polis_Num'];
                configParams = {url: '/?c=RegistryUfa&m=loadUnionRegistryDataFilter'}
                _addFilterToGrid(this.UnionDataGrid, columnsFilter, configParams);
                //

		this.UnionDataGrid.getGrid().view = new Ext.grid.GroupingView({
			//forceFit:true,
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
				if (row.get('RegistryData_IsEarlier') == 3) {
					cls = cls+'x-grid-rowgreen ';
				} else if (row.get('RegistryData_IsEarlier') == 2) {
					cls = cls+'x-grid-roworange ';
				}
				if (cls.length == 0)
					cls = 'x-grid-panel';
				return cls;
			},
			listeners: {
				rowupdated: function (view, first, record) {
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
						return (!form.UnionDataGrid.showGroup || Ext.isEmpty(val.rs[0].data.MaxEvn_id))
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
			dataUrl: '/?c=RegistryUfa&m=loadRegistryErrorCom',
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
				{name:'action_add', disabled: true},
				{name:'action_edit', text: '<b>Исправить</b>', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
			],
			onLoadData: function()
			{

			}
		});

		//Интеграция фильтра к Grid
		columnsFilter = ['RegistryErrorType_Code','RegistryErrorType_Name','RegistryErrorClass_Name','Paid'];
		configParams = {url : '/?c=RegistryUfa&m=loadRegistryErrorComFilter'}
		_addFilterToGrid(this.ErrorComGrid,columnsFilter,configParams);


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
			dataUrl: '/?c=RegistryUfa&m=loadRegistryError',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			stringfields:
			[
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
				{name: 'RegistryError_Desc', header: 'Комментарий', width: 250},
				{name: 'Person_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden:true},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Usluga_Code', header: 'Код посещения', width: 80},
				{name: 'UslugaComplex_Code', header: 'Код услуги', width: 80},
				{name: 'UslugaComplex_Name', header: 'Наименование услуги', width: 150},
				{name: 'LpuSection_name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'MedPersonal_LabFIO', header: 'Лаборант', width: 200},
				{name: 'Evn_setDate', type:'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type:'date', header: 'Окончание', width: 70},
				{name: 'RegistryErrorClass_id', type: 'int', hidden:true},
				{name: 'RegistryErrorClass_Name', width:80, header: 'Тип'},
				{name: 'LpuSectionProfile_Code', type: 'int', hidden:true},
				{name: 'Mes_Code', header: 'МЭС', width: 80}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', handler: function() {
					var record = form.ErrorGrid.getGrid().getSelectionModel().getSelected();
					record.set('RegistryData_IsCorrected', 2);
					Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {});}
				},
				{name:'action_view', disabled: true},
				{name:'action_delete', text: 'Удалить случай из реестра', handler: function() { form.deleteRegistryData(form.ErrorGrid, false); }},
				{name:'action_print', text:'Печатать текущую страницу'},
				{name:'action_printall', text:'Печатать весь список', tooltip: 'Печатать весь список', icon: 'img/icons/print16.png', handler: function() { form.printRegistryError(); }},
				{name:'action_deleteall', icon: 'img/icons/delete16.png', text: 'Удалить случаи по всем ошибкам', handler: function() { form.deleteRegistryData(form.ErrorGrid, true); }},
				{name:'action_openperson', visible: !isAdmin, icon: 'img/icons/patient16.png', tooltip: 'Открыть данные человека', text: 'Открыть данные человека', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').ErrorGrid, {}, 'OpenPerson');}}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				this.getAction('action_delete').setText((record.get('RegistryData_deleted')==2)?'Восстановить случай в рееестре':'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted')==2)?'Восстановить случаи по всем ошибкам':'Удалить случаи по всем ошибкам');
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
                         'Diag_Code','LpuSection_name', 'LpuSectionProfile_Name','LpuBuilding_Name','MedPersonal_Fio', 'MedSpecOms_Name','RegistryErrorClass_Name','Paid'];
		configParams = {url : '/?c=RegistryUfa&m=loadRegistryErrorFilter'}
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
			id: form.id + 'TFOMSError',
			title: 'Итоги проверки ТФОМС',
			object: 'RegistryErrorTFOMS',
			dataUrl: '/?c=RegistryUfa&m=loadRegistryErrorTFOMS',
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
				{name: 'Polis_Num', type: 'string', header: 'Номер полиса', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'MedPersonal_LabFIO', header: 'Лаборант', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type: 'date', header: 'Окончание', width: 70}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
					[
						{field: 'RegistryError_Comment', tpl: '{RegistryError_Comment}'}
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
			actions: [
				{name: 'action_add', disabled: true, hidden: true},
				{
					name: 'action_edit', text: '<b>Исправить</b>', handler: function () {
					Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {});
				}
				},
				{name: 'action_view', disabled: true, hidden: true},
				{
					name: 'action_delete', text: 'Удалить случай из реестра', handler: function () {
					form.deleteRegistryData(form.TFOMSErrorGrid, false);
				}
				},
				{name: '-'},
				{
					name: 'action_deleteall',
					icon: 'img/icons/delete16.png',
					text: 'Удалить случаи по всем ошибкам',
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
						Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenEvn');
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
						Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').TFOMSErrorGrid, {}, 'OpenPerson');
					}
				}

			],
			callbackPersonEdit: function (person, record) {
				var form = Ext.getCmp('RegistryViewWindow');
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
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				var records = this.getGrid().getSelectionModel().selections.items;
				var disabled = false;
				records.forEach(function(rec){
					if(rec.get('RegistryData_deleted')!=2 || RegistryStatus_id!=3){
						disabled = true;
					}
				});
				this.setActionDisabled('action_undelete_all_records',disabled);

				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
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
			onLoadData: function () {
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete', (RegistryStatus_id != 3));
				this.setActionDisabled('action_deleteall', (RegistryStatus_id != 3));
			}
		});

		//Интеграция фильтра к Grid
		columnsFilter = ['Evn_id','Person_id','RegistryErrorType_Code','RegistryErrorType_Name','Person_FIO',
			'LpuSection_Name', 'LpuSectionProfile_Name','LpuBuilding_Name','MedPersonal_Fio', 'MedSpecOms_Name', 'Polis_Num'];
		configParams = {url : '/?c=RegistryUfa&m=loadRegistryErrorTFOMSFilter'}
		_addFilterToGrid(this.TFOMSErrorGrid,columnsFilter,configParams);

		this.TFOMSErrorGrid.ViewGridPanel.view = new Ext.grid.GridView({
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
		this.UnionTFOMSErrorGrid = new sw.Promed.ViewFrame({
			id: form.id + 'UnionTFOMSError',
			title: 'Итоги проверки ТФОМС',
			object: 'RegistryErrorTFOMS',
			dataUrl: '/?c=RegistryUfa&m=loadUnionRegistryErrorTFOMS',
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
				{name: 'Polis_Num', type: 'string', header: 'Номер полиса', width: 90},
				{name: 'LpuSection_Name', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 200},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 200},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
				{name: 'MedPersonal_LabFIO', header: 'Лаборант', width: 200},
				{name: 'Evn_setDate', type: 'date', header: 'Начало', width: 70},
				{name: 'Evn_disDate', type: 'date', header: 'Окончание', width: 70}
			],
			plugins: [
				new Ext.ux.plugins.grid.CellToolTips(
					[
						{field: 'RegistryError_Comment', tpl: '{RegistryError_Comment}'}
					])
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
					hidden: true,
					name: 'action_delete', text: 'Удалить случай из реестра', handler: function () {
					form.deleteRegistryData(form.UnionTFOMSErrorGrid, false);
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
			onLoadData: function()
			{
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				this.setActionDisabled('action_delete_all_records',RegistryStatus_id!=3);
				this.setActionDisabled('action_undelete_all_records',true);
			},
			onRowSelect: function (sm, rowIdx, record) {
				var RegistryStatus_id = form.Tree.selModel.selNode.attributes.object_value;
				var records = this.getGrid().getSelectionModel().selections.items;
				var disabled = false;
				records.forEach(function(rec){
					if(rec.get('RegistryData_deleted')!=2 || RegistryStatus_id!=3){
						disabled = true;
					}
				});
				this.setActionDisabled('action_undelete_all_records',disabled);
				this.getAction('action_delete').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случай в рееестре' : 'Удалить случай из реестра');
				this.getAction('action_deleteall').setText((record.get('RegistryData_deleted') == 2) ? 'Восстановить случаи по всем ошибкам' : 'Удалить случаи по всем ошибкам');

				if (this.getCount() > 0) {
					this.setActionDisabled('action_deleteerror', !Ext.isEmpty(record.get('OrgSMO_id')));
					this.setActionDisabled('action_openperson', !isAdmin);
					this.setActionDisabled('action_openevn', !isAdmin);
					this.setActionDisabled('action_tehinfo', false);
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
		});
                //задача  #86094
                columnsFilter = ['RegistryErrorTFOMS_id', 'Evn_id', 'RegistryData_deleted', 'RegistryData_notexist', 'Person_id', 'Evn_rid', 'EvnClass_id', 'DispClass_id', 'RegistryErrorType_Code',
                    'RegistryErrorType_Name', 'Registry_id', 'RegistryType_id', 'Server_id', 'PersonEvn_id', 'Person_FIO', 'LpuSection_Name', 'LpuSectionProfile_Name',
                    'LpuBuilding_Name', 'MedPersonal_Fio', 'MedSpecOms_Name', 'Polis_Num'];
                configParams = {url: '/?c=RegistryUfa&m=loadUnionRegistryErrorTFOMSFilter'}
                _addFilterToGrid(this.UnionTFOMSErrorGrid, columnsFilter, configParams);
                //
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
			title: 'Дубли посещений',
			object: 'RegistryDouble',
			dataUrl: '/?c=RegistryUfa&m=loadRegistryDouble',
			paging: true,
			root: 'data',
			region: 'center',
			totalProperty: 'totalCount',
			toolbar: false,
			autoLoadData: false,
			passPersonEvn: true,
			stringfields:
			[
				{name: 'Evn_id', type: 'int', header: 'ИД случая', key: true, hidden: false, hideable: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden:!isSuperAdmin()},
				{name: 'Evn_rid', type: 'int', hidden: true},
				{name: 'Registry_id', type: 'int', hidden:true},
				{name: 'RegistryType_id', type: 'int', hidden:true},
				{name: 'Server_id', type: 'int', hidden: true },
				{name: 'PersonEvn_id', type: 'int', hidden: true },
				{name: 'EvnPL_NumCard', header: '№ талона', width: 80},
				{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО пациента'},
				{name: 'Person_BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Polis_Num', type: 'string', header: 'Номер полиса', width: 90},
				{name: 'LpuSection_FullName', header: 'Отделение', width: 200},
				{name: 'LpuSectionProfile_Name', header: 'Профиль', width: 200},
				{name: 'LpuBuilding_Name', header: 'Подразделение', width: 150},
				{name: 'MedPersonal_Fio', header: 'Врач', width: 250},
				{name: 'MedSpecOms_Name', header: 'Специальность', width: 200},
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
				{name:'action_add_all', hidden: true },
				{name:'action_add', hidden: true },
				{name:'action_edit', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DoubleVizitGrid, {action: 'edit'}, 'swEvnPLEditWindow')}},
				{name:'action_view', handler: function() {Ext.getCmp('RegistryViewWindow').openForm(Ext.getCmp('RegistryViewWindow').DoubleVizitGrid, {action: 'view'}, 'swEvnPLEditWindow')}},
				{name:'action_delete', handler: this.deleteRegistryDouble.createDelegate(this, ['current']) }
			],
			onLoadData: function()
			{
			}
		});

		//Интеграция фильтра к Grid
		columnsFilter = ['Evn_id','Person_id','EvnPL_NumCard','Person_FIO','Usluga_Code','LpuSection_FullName', 'LpuSectionProfile_Name', 'LpuBuilding_Name','MedPersonal_Fio', 'MedSpecOms_Name','Paid'];
		configParams = {url : '/?c=RegistryUfa&m=loadRegistryDoubleFilter'}
		_addFilterToGrid(this.DoubleVizitGrid,columnsFilter,configParams);           
		
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
					var record = form.AccountGrid.getGrid().getSelectionModel().getSelected();
					if ( typeof record == 'object' )
					{
						var Registry_id = record.get('Registry_id');
						var RegistryType_id = record.get('RegistryType_id');
						form.onRegistrySelect(Registry_id, RegistryType_id, true);
					}

					form.setZNOFilterVisibility();
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
				//autoscroll: true,
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
					title: '4. Итоги проверки ТФОМС',
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
					title: '4. Итоги проверки МЗ',
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
					title: '6. Превышение объёма МП',
					layout: 'fit',
					iconCls: 'info16',
					id: 'tab_databadvol',
					border: false,
					items:
					[{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.RegistryDataBadVolFiltersPanel,form.DataBadVolGrid]
					}]
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
			id: form.id+'UnionRegistryGrid',
			region: 'north',
			height: 203,
			title:'Реестры по СМО',
			object: 'Registry',
			editformclassname: 'swUnionRegistryEditWindow',
			dataUrl: '/?c=RegistryUfa&m=loadUnionRegistryGrid',
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			toolbar: true,
			autoLoadData: false,
			passPersonEvn: true,
			onRowSelect: function(sm,rowIdx,record)
			{
				var Registry_id = record.get('Registry_id'),
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
				form.UnionActionsMenu.items.items[8].hide();
				form.UnionActionsMenu.items.items[9].hide();

				if (!Ext.isEmpty(record.get('RegistryStatus_id'))) {
					if (record.get('RegistryStatus_id') == 2 && record.get('RegistryType_id') != 19) {
						form.UnionActionsMenu.items.items[4].show(); // Реестр-ответ
					}

					if (record.get('RegistryStatus_id').inlist([2])) {
						form.UnionActionsMenu.items.items[2].show(); // Экспорт реестров
						form.UnionActionsMenu.items.items[3].show(); // Экспорт реестров
					}

					if (record.get('RegistryStatus_id') == 3) {
						form.UnionActionsMenu.items.items[0].show(); // Переформировать
						form.UnionActionsMenu.items.items[1].show(); // К оплате
						form.UnionActionsMenu.items.items[8].show(); // Удалить реестр (с удалением случаев из предварительных реестров)
					}

					if (record.get('RegistryStatus_id') == 2) {
						form.UnionActionsMenu.items.items[5].show(); // Отметить как оплаченный
						form.UnionActionsMenu.items.items[6].show(); // Снять отметку "к оплате"
						form.UnionActionsMenu.items.items[9].show();
					}

					if (record.get('RegistryStatus_id') == 4) {
						form.UnionActionsMenu.items.items[7].show(); // Снять отметку "оплачен"
					}
				}
			},
			onLoadData: function() {
				this.addActions({
					name: 'action_add_all_SMO',
					id: 'add_all_registers_SMO',
					hidden: false,
					disabled: false,
					text: 'Группа реестров',
					tooltip: 'Групповое добавление реестров',
					iconCls: 'x-btn-text',
					icon: 'img/icons/add16.png',
					handler: function () {
						getWnd('swRegistryEditWindowVE_SMO').show({Registry_IsNew: form.Registry_IsNew});
					}
				});

				// блокируем кнопку для всех кроме "В работе"
				this.getAction('action_add_all_SMO').setDisabled(this.getParam('RegistryStatus_id') != 3 || this.getParam('RegistrySubType_id') == 3 || (form.Registry_IsNew != 2 && !isSuperAdmin()));
			},
			stringfields:
				[
					{name: 'Registry_id', type: 'int', header: 'Registry_id', key: true, hidden:!isSuperAdmin()},
					{name: 'RegistryType_id', type: 'int', hidden: true},
					{name: 'OrgSmo_Name', id: 'autoexpand', header: 'СМО'},
					{name: 'LpuUnitSet_Code', header: 'Код подразделения', width: 150},
					{name: 'LpuContragent_Name', header: 'МО-контрагент', width: 200},
					{name: 'Registry_IsNotInsur', header: 'Незастрахованные', renderer: function(value, cellEl, rec) {
						if (value == 2) {
							return 'Да';
						} else {
							return '';
						}
					}, width: 100},
					{name: 'Registry_Num', header: 'Номер счёта', width: 80},
					{name: 'Registry_IsZNO',  header: 'ЗНО', type: 'checkbox', width: 50},
					{name: 'Registry_accDate', type: 'date', header: 'Дата счета', width: 90},
					{name: 'Registry_begDate', type:'date', header: 'Начало периода', width: 100},
					{name: 'Registry_endDate', type:'date', header: 'Окончание периода', width: 110},
					{name: 'Registry_Count', type:'int', header: 'Количество', width: 110},
					{name: 'Registry_CountIsBadVol', type: 'int', header: 'Количество с превышением объема', width: 110},
					{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
					{name: 'Registry_updDT', type: 'datetimesec', header: 'Дата изменения', width: 110},
					{name: 'RegistryStatus_id', type: 'int', hidden: true},
					{name: 'Registry_IsNew', type: 'int',  hidden: true, isparams: true},
					{name: 'Registry_pack', type: 'int', hidden: true},
					{name: 'Registry_orderDate', type: 'date',  hidden: true},
					{name: 'DispClass_Name',  header: 'Тип дисп-ции / медосмотра', width: 200}
				],
			actions:
				[
					{name:'action_add' },
					{name:'action_edit' },
					{name:'action_view' },
					{name: 'action_print',
						menuConfig: {
							printScetStrah: {
								name: 'printScetStrah',
								text: 'Печать счета',
								handler: function () {
									var grid = form.UnionRegistryGrid.ViewGridPanel;
									var rec = grid.getSelectionModel().getSelected();
									var Registry_id = rec.get('Registry_id');
									var Registry_Num = rec.get('Registry_Num');
									var Registry_accDate = rec.get('Registry_accDate');
									getWnd('swRegistryPrintScetParamsWindow').show(
										{
											Registry_id: Registry_id,
											Registry_Num: Registry_Num,
											Registry_accDate: Registry_accDate
										}
									);
								}
							},
							printActAndAttachment: {
								name: 'printActAndAttachment',
								text: 'Печать акта и приложения к акту',
								handler: function () {
									var
										grid = form.UnionRegistryGrid.ViewGridPanel,
										rec = grid.getSelectionModel().getSelected(),
										Registry_id = rec.get('Registry_id');

									printBirt({
										'Report_FileName': 'Registry_Settlement.rptdesign',
										'Report_Params': '&paramRegistry=' + Registry_id,
										'Report_Format': 'pdf'
									});

									printBirt({
										'Report_FileName': 'Registry_Settlement_Usl.rptdesign',
										'Report_Params': '&paramRegistry=' + Registry_id,
										'Report_Format': 'pdf'
									});
								}
							}
						}
					},
					{name:'action_delete', url: '/?c=RegistryUfa&m=deleteUnionRegistry' }
				]
		});

		this.UnionRegistryGrid.ViewGridModel.singleSelect = false;

		this.UnionActionsMenu = new Ext.menu.Menu({
			items: [
				{name:'action_reform', text:'Переформировать', handler: function() { this.reformUnionRegistry(); }.createDelegate(this)},
				{name:'action_topay', text:'Отметить к оплате', handler: function() { this.setUnionRegistryStatus(2); }.createDelegate(this)},
				{name:'action_export', text:'Экспорт в XML', handler: function() { this.exportUnionRegistryToXml(); }.createDelegate(this)},
				{name:'action_exportgroup', text:'Экспорт всех выбранных в XML', handler: function() { this.exportUnionRegistryGroupToXml(); }.createDelegate(this)},
				{name:'action_import', text:'Загрузить реестр-ответ (XML)', handler: function() { this.importUnionRegistryXML(); }.createDelegate(this)},
				{name:'action_topaid', text:'Отметить как оплаченный', handler: function() { this.setUnionRegistryStatus(4); }.createDelegate(this)},
				{name:'action_frompay', text:'Снять отметку "к оплате"', handler: function() { this.setUnionRegistryStatus(3); }.createDelegate(this)},
				{name:'action_frompaid', text:'Снять отметку "оплачен"', handler: function() { this.setUnionRegistryStatus(2); }.createDelegate(this)},
				{name:'action_reform', text:'Удалить реестр (с удалением случаев из предварительных реестров)', handler: function() { this.deleteUnionRegistryWithData(); }.createDelegate(this)},
				{name:'action_setpacknum', text: 'Установка номера пачки реестра', handler: function() { this.setRegistryPackNum(form.UnionRegistryGrid); }.createDelegate(this)}
			]
		});

		this.UnionRegistryChildGrid = new sw.Promed.ViewFrame({
			id: form.id+'UnionRegistryChildGrid',
			region: 'center',
			title:'Реестры',
			object: 'Registry',
			dataUrl: '/?c=RegistryUfa&m=loadUnionRegistryChildGrid',
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
					{name: 'RegistryType_id', type: 'int', hidden: true},
					{name: 'RegistryType_Name', header: 'Вид реестра', width: 130},
					{name: 'Registry_Sum', type:'money', header: 'Итоговая сумма', width: 100},
					{name: 'PayType_Name', header: 'Вид оплаты', width: 80},
					{name: 'LpuBuilding_Name', header: 'Подразделение', width: 120},
					{name: 'Registry_updDate', type: 'date', header: 'Дата изменения', width: 110}
				],
			actions:
				[
					{name:'action_add', disabled: true, hidden: true },
					{name:'action_edit', disabled: true, hidden: true },
					{name:'action_view', disabled: true, hidden: true },
					{name:'action_delete', disabled: true, hidden: true }
				]
		});

		this.UnionDataTab = new Ext.TabPanel({
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
						var Registry_id = record.get('Registry_id'),
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