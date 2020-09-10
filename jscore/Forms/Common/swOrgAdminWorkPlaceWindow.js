/**
 * swOrgAdminWorkPlaceWindow - окно рабочего места администратора ЛПУ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Chebukin Alexander
 * @prefix       lawpw
 * @version      март 2011 
 */
/*NO PARSE JSON*/


sw.Promed.swOrgAdminWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	objectName: 'swOrgAdminWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swOrgAdminWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	title: lang['rabochee_mesto_administratora_lpu'],
	iconCls: 'admin16',
	id: 'swOrgAdminWorkPlaceWindow',
	listeners: {
		hide: function() {
			this.FilterPanel.getForm().reset();
			this.setTitleFieldSet();
		}
	},
	show: function()
	{
		sw.Promed.swOrgAdminWorkPlaceWindow.superclass.show.apply(this, arguments);
		/*var loadMask = new Ext.LoadMask(Ext.get('swOrgAdminWorkPlaceWindow'), {msg: LOAD_WAIT});
		loadMask.show();
		var form = this;
		
		form.loadGridWithFilter(true);
		*/
		if (arguments[0]) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
		}
		this.ARMType = arguments[0].ARMType;
		
		if(!this.Grid.getAction('action_sync')) {
			this.Grid.addActions({ 
				name: 'action_sync',
				hidden: (!isAdmin && !isOrgAdmin()) ||  (getGlobalOptions().region.nick == 'ufa'),
				text: lang['perekeshirovat_dannyie'],
				tooltip: lang['perekeshirovat_dannyie'],
				handler: this.syncLdapAndCacheUserData.createDelegate(this)
			});
		}
		
		//loadMask.hide();
		var store = this.Grid.ViewGridPanel.getStore();
		store.baseParams.Org_id = getGlobalOptions().org_id;
		this.doSearch();
		
		
	},
	clearFilters: function ()
	{
		this.findById('lawpwOrg_Nick').setValue('');
		this.findById('lawpwOrg_Name').setValue('');
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear)
			form.clearFilters();
		var Org_id = getGlobalOptions().org_id;
		var OrgNick = this.findById('lawpwOrg_Nick').getValue();
		var OrgName = this.findById('lawpwOrg_Name').getValue();
		var filters = {Org_id: Org_id, Nick: OrgNick, Name: OrgName, start: 0, limit: 100, mode: 'lpu'};
		form.LpuGrid.loadData({globalFilters: filters});
	},
	
	editUser: function(action) {
		var grid = this.Grid.ViewGridPanel,
			record = grid.getSelectionModel().getSelected();
			
		if( !record && action !== 'add') return false;
		
		var user_login = ( action !== 'add' ) ? record.get('login') : 0;
		
		var params = {
			action: action,
			fields: {
				action: action,
				org_id: getGlobalOptions().org_id,
				user_login: user_login
			},
			owner: this,
			callback: function(owner) {
				this.Grid.ViewActions.action_refresh.execute();
			}.createDelegate(this),
			onClose: function() {
				//
			}
		}
		
		getWnd('swUserEditWindow').show(params);
	},
	
	deleteUser: function() {
		var grid = this.Grid.ViewGridPanel,
			record = grid.getSelectionModel().getSelected();
		if( !record ) return false;
		
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etogo_polzovatelya'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if (buttonId == 'yes') {
					Ext.Ajax.request({
						url: C_USER_DROP,
						params: {
							user_login: record.get('login')
						},
						callback: function(o, s, r) {													
							if(s) {
								// Поскольку релоадить негут, так как данные из лдапа читаются, то просто удаляем запись
								grid.getStore().remove(record);
							}
						}
					});
				}
			}
		});
	},
	
	doSearch: function() {
		// Ставим заголовок фильтра
		this.setTitleFieldSet();
		var form = this.FilterPanel.getForm(),
			params = form.getValues(),
			grid = this.Grid.ViewGridPanel;
		for(par in params)
			grid.getStore().baseParams[par] = params[par];
		grid.getStore().load({params: { start: 0, limit: 100 }});
	},
	
	doReset: function() {
		this.FilterPanel.getForm().reset();
		this.doSearch();
	},
	
	setTitleFieldSet: function() {
		var fset = this.FilterPanel.find('xtype', 'fieldset')[0],
			isfilter = false,
			title = lang['poisk_filtr'];
		
		fset.findBy(function(f) {
			if( f.xtype && f.xtype.inlist(['textfield', 'swusersgroupscombo']) ) {
				if( f.getValue() != '' && f.getValue() != null ) {
					isfilter = true;
				}
			}
		});
		
		fset.setTitle( title + ( isfilter == true ? '' : 'не ' ) + 'установлен' );
	},
	
	syncLdapAndCacheUserData: function() {
		sw.swMsg.show({
			title: lang['podtverjdenie_perekeshirovaniya'],
			msg: lang['vyi_deystvitelno_jelaete_perekeshirovat_dannyie'],
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId) {
				if (buttonId == 'yes') {
					this.getLoadMask(lang['podojdite_vyipolnyaetsya_perekeshirovanie_dannyih']).show();
					Ext.Ajax.request({
						url: '/?c=User&m=syncLdapAndCacheUserData',
						params: {
							Org_id: getGlobalOptions().org_id
						},
						callback: function(o, s, r) {													
							this.getLoadMask().hide();
							if(s) {
								this.doSearch();
							}
						}.createDelegate(this)
					});
				}
			}.createDelegate(this)
		});
	},
	
	initComponent: function()
	{
		var form = this;

		Ext.apply(sw.Promed.Actions, {
			DrugMnnCodeAction: {
				name: 'action_DrugMnnCodeSpr',
				text: lang['spravochnik_mnn'],
				iconCls : '',
				handler: function() {
					getWnd('swDrugMnnCodeViewWindow').show({readOnly: false});
				}.createDelegate(this)
			},
			DrugTorgCodeAction: {
				name: 'action_DrugTorgCodeSpr',
				text: lang['spravochnik_torgovyih_naimenovaniy'],
				iconCls : '',
				handler: function() {
					getWnd('swDrugTorgCodeViewWindow').show({readOnly: false});
				}.createDelegate(this)
			},
			DrugNonpropNamesAction: {
				name: 'action_DrugNonpropNames',
				tooltip: lang['nepatentovannye_naimenovaniya'],
				text: lang['nepatentovannye_naimenovaniya'],
				handler: function() {
					getWnd('swDrugNonpropNamesViewWindow').show();
				}.createDelegate(this)
			},
			ExtemporalAction: {
				name: 'action_Extemporal',
				tooltip: lang['extemporalnie_retseptury'],
				text: lang['extemporalnie_retseptury'],
				handler: function() {
					getWnd('swExtemporalViewWindow').show();
				}.createDelegate(this)
			},
			DrugMarkupAction: {
				name: 'action_DrugMarkup',
				hidden: getRegionNick().inlist(['by','kz']),
				text: lang['predelnyie_nadbavki_na_jnvlp'],
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swDrugMarkupViewWindow').show();
				}.createDelegate(this)
			},
			PriceJNVLPAction: {
				name: 'action_PriceJNVLP',
				hidden: getRegionNick().inlist(['by','kz']),
				text: lang['tsenyi_na_jnvlp'],
				iconCls : 'dlo16',
				handler: function() {
					getWnd('swJNVLPPriceViewWindow').show();
				}.createDelegate(this)
			},
			swPrepBlockSprAction: {
				text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
				tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
				handler: function() {
					getWnd('swPrepBlockViewWindow').show();
				}.createDelegate(this)
			},
			SprRlsAction: {
				text: getRLSTitle(),
				tooltip: getRLSTitle(),
				iconCls: 'rls16',
				handler: function() {
					getWnd('swRlsViewForm').show();
				}.createDelegate(this)
			}
		});

		// Формирование списка всех акшенов 
		var configActions = 
		{
			action_OrgPassport: {
				nn: 'action_OrgPassport',
				text: lang['pasport_organizatsii'],
				tooltip: lang['pasport_organizatsii'],
				iconCls: 'lpu-passport32',
				handler: function()
				{
					getWnd('swOrgEditWindow').show({
							action: 'edit',
							Org_id: getGlobalOptions().org_id
					});
				},
				hidden: !isAdmin && !isOrgAdmin()
			},	
			action_OrgStructureView: {
				nn: 'action_OrgStructureView',
				text: lang['struktura_organizatsii'],
				tooltip: lang['struktura_organizatsii'],
				iconCls : 'structure32',
				hidden: !isAdmin && !isOrgAdmin() && !isCadrUserView(),
				handler: function()
				{
					getWnd('swOrgStructureWindow').show();
				}
			},
            action_System:
			{
				nn: 'action_System',
				tooltip: lang['sistema'],
				text: lang['sistema'],
				iconCls : 'settings32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [{
						text: lang['nastroyki'],
						tooltip: lang['prosmotr_i_redaktirovanie_nastroek'],
						iconCls: 'settings16',
						handler: function () {
							getWnd('swOptionsWindow').show();
						}
					}, {
						text: lang['jurnal_avtorizatsiy_v_sisteme'],
						iconCls: '',
						handler: function () {
							getWnd('swUserSessionsViewForm').show({
								Org_id: getGlobalOptions().org_id
							});
						}
					}]
				})
			},
            action_OrgFarmacyByLpuView: {
                nn: 'action_OrgFarmacyByLpuView',
                tooltip: langs('Прикрепление аптек к МО'),
                text: langs('Прикрепление аптек к МО'),
                iconCls : 'therapy-plan32',
                disabled: false,
                handler: function(){
                    if (getRegionNick().inlist(['perm', 'ufa'])) {
                        getWnd('swOrgFarmacyByLpuViewWindow').show();
                    } else {
                        getWnd('swOrgFarmacyLinkedByLpuViewWindow').show({ARMType: form.ARMType});
                    }
                }
            },
			action_Spr: 
			{
				nn: 'action_Spr',
				tooltip: lang['spravochniki'],
				text: lang['spravochniki'],
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						{
							text: lang['spravochnik_mkb-10'],
							iconCls: 'spr-mkb16',
							handler: function() {
								getWnd('swMkb10SearchWindow').show({action: 'view'});
							}
						}, {
							name: 'action_DrugListSpr',
							text: 'Перечни медикаментов',
							iconCls : '',
							handler: function()
							{
								getWnd('swDrugListSprWindow').show({ARMType: this.ARMType});
							}.createDelegate(this)
						},
						sw.Promed.Actions.swDrugDocumentSprAction,
						{
							text: lang['nomenklaturnyiy_spravochnik'],
							iconCls : '',
							handler: function()
							{
								getWnd('swDrugNomenSprWindow').show({action: 'view'});
							}
						}, {
							text: lang['lekarstvennyie_sredstva'],
							tooltip: lang['lekarstvennyie_sredstva'],
							iconCls: '',
							menu: new Ext.menu.Menu({
								items:[
									sw.Promed.Actions.DrugMnnCodeAction,
									sw.Promed.Actions.DrugTorgCodeAction,
									sw.Promed.Actions.DrugNonpropNamesAction,
									sw.Promed.Actions.ExtemporalAction,
									sw.Promed.Actions.DrugMarkupAction,
									sw.Promed.Actions.PriceJNVLPAction,
									sw.Promed.Actions.swPrepBlockSprAction,
									sw.Promed.Actions.SprRlsAction
								]
							})
						}, {
							text: lang['naimenovaniya_mest_hraneniya'],
							iconCls : '',
							handler: function()
							{
								getWnd('swGoodsStorageViewWindow').show();
							}
						}, {
							text: lang['spravochnik_mnn'],
							iconCls : '',
							handler: function()
							{
								getWnd('swDrugMnnCodeViewWindow').show({action: 'view'});
							}
						}, {
							text: lang['spravochnik_torgovyih_naimenovaniy'],
							iconCls : '',
							handler: function()
							{
								getWnd('swDrugTorgCodeViewWindow').show({action: 'view'});
							}
						}, {
							text: lang['edinitsyi_izmereniya'],
							iconCls: '',
							handler: function()
							{
								getWnd('swUnitSprViewWindow').show({action: 'view'});
							}
						}, {
							text: lang['spravochniki_promed'],
							iconCls: 'spr-promed16',
							handler: function() {
								getWnd('swDirectoryViewWindow').show({action: 'view'});
							}
						}
					]
				})
			},
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy']
			},
			action_ReportEngine: 
			{
				nn: 'action_ReportEngine',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls : 'report32',
				disabled: false, 
				handler: function() 
				{
				if (sw.codeInfo.loadEngineReports)
					{
						getWnd('swReportEndUserWindow').show();
					}
					else
					{
						getWnd('reports').load(
							{
								callback: function(success)
								{
									sw.codeInfo.loadEngineReports = success;
									// здесь можно проверять только успешную загрузку
									getWnd('swReportEndUserWindow').show();
								}
							});
					}
				}
			},
			action_StoragePlacement:
			{
				nn: 'action_StoragePlacement',
				tooltip: lang['razmechenie_na_skladah'],
				text: lang['razmechenie_na_skladah'],
				iconCls : 'storage-place32',
				handler: function()
				{
					getWnd('swStorageZoneViewWindow').show();
				}
			}
		}
		// Копируем все действия для создания панели кнопок
		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_OrgPassport', 'action_OrgStructureView', 'action_OrgFarmacyByLpuView', 'action_Spr', 'action_JourNotice', 'action_ReportEngine', 'action_System', 'action_StoragePlacement'];
		// Создание кнопок для панели
		form.BtnActions = new Array();
		var i = 0;
		for(var key in form.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				form.BtnActions.push(new Ext.Button(form.PanelActions[key]));
				i++;
			}
		}
		
		this.leftMenu = new Ext.Panel(
		{
			region: 'center',
			id:form.id+'_hhd',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: form.BtnActions
		});
		
		this.leftPanel =
		{
			animCollapse: false,
			width: 52,
			minSize: 52,
			maxSize: 120,
			id: 'lawpwLeftPanel',
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					var el = null;
					el = form.findById(form.id+'_slid');
					if(el)
						el.setHeight(this.body.dom.clientHeight-42);
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [
				new Ext.Button(
				{	
					cls:'upbuttonArr',
					disabled: false,
					iconCls: 'uparrow',
					handler: function() 
					{
						var el = form.findById(form.id+'_hhd');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					layout:'border',
					id:form.id+'_slid',
					height:100,
					items:[this.leftMenu]
				},			
				new Ext.Button(
				{
				cls:'upbuttonArr',
				iconCls:'downarrow',
				style:{width:'48px'},
				disabled: false, 
				handler: function() 
				{
					var el = form.findById(form.id+'_hhd');
					var d = el.body.dom;
					d.scrollTop +=38;
					
				}
				})]
		};

		this.FilterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			region: 'north',
			frame: true,
			items: [
				{
					layout: 'form',
					xtype: 'fieldset',
					autoHeight: true,
					collapsible: true,
					listeners: {
						collapse: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this),
						expand: function() {
							this.FilterPanel.doLayout();
							this.doLayout();
						}.createDelegate(this)
					},
					labelAlign: 'right',
					title: lang['poisk_filtr_ne_ustanovlen'],
					items: [
						{
							layout: 'column',
							items: [
								{
									layout: 'form',
									defaults: {
										anchor: '100%'
									},
									labelWidth: 60,
									width: 300,
									items: [
										{
											xtype: 'textfield',
											name: 'Person_SurName',
											fieldLabel: lang['familiya']
										}, {
											xtype: 'textfield',
											name: 'Person_FirName',
											fieldLabel: lang['imya']
										}, {
											xtype: 'textfield',
											name: 'Person_SecName',
											fieldLabel: lang['otchestvo']
										}
									]
								}, {
									layout: 'form',
									width: 320,
									defaults: {
										anchor: '100%'
									},
									items: [
										{
											xtype: 'textfield',
											name: 'login',
											fieldLabel: lang['login']
										}, {
											xtype: 'swusersgroupscombo',
											hiddenName: 'group',
											listeners: {
												render: function() {
													if(this.getStore().getCount()==0)
														this.getStore().load();
												}
											},
											valueField: 'Group_Name',
											fieldLabel: lang['gruppa']
										}, {
											xtype: 'textfield',
											name: 'desc',
											fieldLabel: lang['opisanie']
										}
									]
								}
							]
						}, {
							layout: 'column',
							style: 'padding: 3px;',
							items: [
								{
									layout: 'form',
									items: [
										{
											handler: function() {
												this.doSearch();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'search16',
											text: BTN_FRMSEARCH
										}
									]
								}, {
									layout: 'form',
									style: 'margin-left: 5px;',
									items: [
										{
											handler: function() {
												this.doReset();
											}.createDelegate(this),
											xtype: 'button',
											iconCls: 'resetsearch16',
											text: lang['sbros']
										}
									]
								}
							]
						}
					]
				}
			],
			keys: [{
				fn: function(inp, e) {
					this.doSearch();
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}]
		});
		
		this.Grid = new sw.Promed.ViewFrame({
			id: this.id + '_Grid',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			region: 'center',
			pageSize: 100,
			actions: [
				{ name: 'action_add', handler: this.editUser.createDelegate(this, ['add']) },
				{ name: 'action_edit', handler: this.editUser.createDelegate(this, ['edit']) },
				{ name: 'action_view', hidden: true, handler: this.editUser.createDelegate(this, ['view']) },
				{ name: 'action_delete', handler: this.deleteUser.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'pmUser_id', key: true },
				{ name: 'login', header: lang['login'], width: 150 },
				{ name: 'surname', header: lang['familiya'], width: 150 },
				{ name: 'name', header: lang['imya'], width: 150 },
				{ name: 'secname', header: lang['otchestvo'], width: 150 },
				{ name: 'groups', header: lang['gruppyi'], renderer: function(value, cellEl, rec) {
					var result = '';
					if (!Ext.isEmpty(value)) {
						// разджейсониваем
						var groups = Ext.util.JSON.decode(value);
						for(var k in groups) {
							if (groups[k].name) {
								if (!Ext.isEmpty(result)) {
									result += ', ';
								}
								result += groups[k].name;
							}
						}
					}
					return result;
				}, width: 300 },
				{ name: 'desc', header: lang['opisanie'], id: "autoexpand" },
				{ name: 'IsMedPersonal', type: 'checkcolumn', header: lang['vrach'] }
			],
			paging: true,
			dataUrl: '/?c=User&m=getUsersListOfCache',
			root: 'data',
			totalProperty: 'totalCount'
		});
		
		this.Grid.ViewGridPanel.getStore().on('load', function() {
			this.Grid.ViewGridPanel.getSelectionModel().selectFirstRow();
		}.createDelegate(this));
		
		this.Grid.ViewGridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			var groups = (rec.get('groups'))?rec.get('groups').split(', '):[],
				actions = this.Grid.ViewActions,
				thereIsSuperAdmin = false;
			for(var i=0; i<groups.length; i++) {
				if(groups[i] == 'SuperAdmin') {
					thereIsSuperAdmin = true;
					break;
				}
			}
			actions.action_delete.setDisabled(!(isSuperAdmin() || isOrgAdmin()));
			actions.action_edit.setDisabled( thereIsSuperAdmin );
		}.createDelegate(this));
		
		Ext.apply(this, {
			layout: 'border',
			items: [
				this.FilterPanel,
				this.leftPanel,
				this.Grid
			],
			buttons: [{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: this.hide.createDelegate(this, [])
			}]
		});
		// -->
		
		sw.Promed.swOrgAdminWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
		
		
		
	}	
	
});

