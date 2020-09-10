/**
* swRegistryReceptListWindow - список реестров рецептов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      RegistryRecept
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      20.12.2012
* @comment      
*/
/*NO PARSE JSON*/
sw.Promed.swRegistryReceptListWindow = Ext.extend(sw.Promed.BaseForm,
{
	maximizable: true,
	maximized: true,
	height: 500,
	width: 800,
	id: 'RegistryReceptListWindow',
	title: WND_REGISTRYRECEPT_LIST, 
	layout: 'border',
	resizable: true,
	showRegistryReceptView: function() {
		var params = new Object();
		var grid = this.RegistryGrid.ViewGridPanel;
		var rec = grid.getSelectionModel().getSelected();
		if (!rec || !rec.get('RegistryRecept_id')) {
			return false;
		}
		params.RegistryRecept_id = rec.get('RegistryRecept_id');
		
		getWnd('swRegistryReceptViewWindow').show(params);
	},
	importRegistryRecept: function() {
		getWnd('swRegistryReceptImportWindow').show();
	},
	doFilter: function() {
		var base_form = this.filtersPanel.getForm();
		var filters = base_form.getValues();
		filters.start = 0;
		filters.limit = 100;
		filters.ReceptUploadLog_id = this.ReceptUploadLog_id;
		filters.PrivilegeType_Code = base_form.findField('PrivilegeType_id').getFieldValue('PrivilegeType_Code');
		
		this.ErrorsGrid.removeAll();
		this.RegistryGrid.loadData({ globalFilters: filters });
	},
	doResetFilters: function() {
		this.filtersPanel.getForm().reset();
	},
	initComponent: function() 
	{
		var form = this;
		
		this.RegistryGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'RegistryGrid',
			title:'',
			object: 'RegistryRecept',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptList',
			autoLoadData: false,
			// selectionModel: 'multiselect',
			region: 'center',
			toolbar: true,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'RegistryRecept_id', type: 'int', header: 'ID', key: true, hidden: false},
				{name: 'ReceptStatusFLKMEK_Name', header: lang['status_retsepta'], width: 60},
				{name: 'RegistryReceptType_Name', header: lang['tip_zapisi'], width: 60},
				{name: 'RegistryRecept_Recent', header: lang['seriya_nomer_retsepta'], width: 150},
				{name: 'RegistryRecept_setDT', type:'date', header: lang['data_vyipiski'], width: 60},
				{name: 'RegistryRecept_ProtoKEK', header: lang['vk'], width: 60},
				{name: 'RegistryRecept_Snils', header: lang['snils'], width: 120, renderer: snilsRenderer},
				{name: 'RegistryReceptPerson_FIO', header: lang['fio'], width: 120},
				{name: 'RegistryReceptPerson_Sex', header: lang['pol'], width: 60},
				{name: 'RegistryReceptPerson_BirthDay', type:'date', header: lang['data_rojdeniya'], width: 80},
				{name: 'RegistryRecept_Diag', header: lang['diagnoz'], width: 60},
				{name: 'RegistryReceptPerson_Privilege', header: lang['lgota'], width: 60},
				{name: 'RegistryRecept_Persent', header: lang['protsent_lgotyi'], width: 60},
				{name: 'RegistryRecept_RecentFinance', header: lang['istochnik_finansirovaniya'], width: 60},
				{name: 'RegistryRecept_obrDate', type:'date', header: lang['data_obrascheniya'], width: 80},
				{name: 'RegistryRecept_otpDate', type:'date', header: lang['data_otpucka'], width: 80},
				{name: 'RegistryRecept_DrugNomCode', header: lang['kod_ls'], width: 60},
				{name: 'Drug_Name', header: lang['ls'], width: 80},
				{name: 'Drug_NameOld', header: lang['ls_reg'], width: 80},
				{name: 'RegistryRecept_DrugKolvo', header: lang['kolichestvo'], width: 60},
				{name: 'RegistryRecept_PriceOne', header: lang['tsena'], width: 60},
				{name: 'RegistryRecept_Price', header: lang['summa'], width: 60},
				{name: 'RegistryRecept_SupplyNum', header: lang['nomer_goskontrakta'], width: 150},
				{name: 'RegistryRecept_MedPersonalCode', header: lang['kod_vracha'], width: 120},
				{name: 'MedPersonal_Name', header: lang['vrach'], width: 60},
				{name: 'RegistryRecept_LpuMod', header: lang['kod_mo'], width: 120},
				{name: 'Lpu_Name', header: lang['mo'], width: 60},
				{name: 'RegistryRecept_FarmacyACode', header: lang['kod_apteki'], width: 120},
				{name: 'OrgFarmacy_Name', header: lang['apteka'], width: 60},
				{name: 'RegistryRecept_SchetType', header: lang['tip_reestra'], width: 60}
			],
			onDblClick: function() {
				form.showRegistryReceptView();
			},
			onRowSelect: function(sm,index,record)
			{
				if (!Ext.isEmpty(record.get('RegistryRecept_id')))
				{
					var filters = {};
					filters.start = 0;
					filters.limit = 100;
					filters.RegistryRecept_id = record.get('RegistryRecept_id');
					form.ErrorsGrid.loadData({ globalFilters: filters });
				}
			},
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_print', disabled: false},
				{name:'action_view', disabled: false, handler: function() {
					form.showRegistryReceptView();
				}},
				{name:'action_delete', disabled: true}
			]
		});
		
		this.ErrorsGrid = new sw.Promed.ViewFrame(
		{
			id: form.id+'ErrorsGrid',
			title:lang['oshibki_po_reestru'],
			object: 'RegistryReceptError',
			dataUrl: '/?c=RegistryRecept&m=loadRegistryReceptErrorList',
			autoLoadData: false,
			// selectionModel: 'multiselect',
			region: 'south',
			height: 200,
			toolbar: true,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			stringfields:
			[
				{name: 'RegistryReceptError_id', type: 'int', header: 'ID', key: true, hidden: false},
				{name: 'RegistryReceptErrorType_Type', header: lang['kod_oshibki'], width: 60},
				{name: 'RegistryReceptErrorType_Name', header: lang['naimenovanie_oshibki'], id: 'autoexpand', width: 60},
				{name: 'EvnRecept_Num', header: lang['nomer_identifitsirovannogo_retsepta'], width: 150}
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_edit', disabled: true},
				{name:'action_print', disabled: false},
				{name:'action_view', disabled: true},
				{name:'action_delete', disabled: true}
			]
		});
		
		this.filtersPanel = new Ext.FormPanel(
		{
			xtype: 'form',
			region: 'north',
			labelAlign: 'right',
			layout: 'form',
			autoHeight: true,
			labelWidth: 50,
			bodyStyle:'background:#DFE8F6;',
			defaults:{bodyStyle:'background:#DFE8F6;'}, 
			border: false,
			keys: 
			[{
				key: Ext.EventObject.ENTER,
				fn: function(e) 
				{
					form.doFilter();
				},
				stopEvent: true
			}],
			items: [{
				listeners: {
					collapse: function(p) {
						form.doLayout();
					},
					expand: function(p) {
						form.doLayout();
					}
				},
				title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
				titleCollapse: true,
				collapsible: true,
				animCollapse: false,
				floatable: false,
				autoHeight: true,
				labelWidth: 80,
				layout: 'form',
				border: false,
				defaults:{bodyStyle:'background:#DFE8F6;'}, 
				items:
				[{
					border: false,
					layout: 'column',
					defaults:{bodyStyle:'width:100%;height:100%;background:#DFE8F6;padding:4px;'}, //
					anchor: '-10',
					items: [{
						layout: 'form',
						columnWidth: .33,
						border: false,
						items: [{
							hiddenName: 'RegistryRecept_Snils',
							fieldLabel: lang['snils'],
							tabindex: TABINDEX_RRLW + 0,
							anchor: '-5',
							xtype: 'swsnilsfield'
						}, {
							name: 'RegistryRecept_Fio',
							fieldLabel: lang['fio'],
							tabindex: TABINDEX_RRLW + 1,
							anchor: '-10',
							xtype: 'textfield'
						}, {
							hiddenName: 'PrivilegeType_id',
							fieldLabel: lang['lgota'],
							listWidth: 350,
							anchor: '-10',
							tabindex: TABINDEX_RRLW + 2,
							xtype: 'swprivilegetypecombo'
						}]
					}, {
						layout: 'form',
						columnWidth: .33,
						border: false,
						labelWidth: 100,
						defaults:{bodyStyle:'background:#DFE8F6;'},
						items: [{
							border: false,
							layout: 'column',
							defaults:{bodyStyle:'background:#DFE8F6;'},
							anchor: '-10',
							items: [{
								layout: 'form',
								border: false,
								width: 170,
								items: [{
									name: 'RegistryRecept_Ser',
									fieldLabel: lang['retsept_seriya'],
									tabindex: TABINDEX_RRLW + 3,
									anchor: '-10',
									xtype: 'textfield'
								}]
							}, {
								layout: 'form',
								border: false,
								labelWidth: 40,
								width: 110,
								items: [{
									name: 'RegistryRecept_Num',
									fieldLabel: lang['nomer'],
									tabindex: TABINDEX_RRLW + 4,
									anchor: '100%',
									xtype: 'textfield'
								}]						
							}]
						}, {
							name: 'MedPersonal_Name',
							fieldLabel: lang['vrach'],
							tabindex: TABINDEX_RRLW + 5,
							anchor: '-10',
							listWidth: 350,
							xtype: 'textfield'
						}, {
							hiddenName: 'Lpu_id',
							fieldLabel: lang['mo'],
							listWidth: 350,
							tabindex: TABINDEX_RRLW + 6,
							anchor: '-10',
							xtype: 'swlpucombo'
						}]						
					}, {
						layout: 'form',
						columnWidth: .33,
						border: false,
						labelWidth: 100,
						items: [{
							hiddenName: 'OrgFarmacy_id',
							fieldLabel: lang['apteka'],
							tabindex: TABINDEX_RRLW + 7,
							anchor: '-10',
							xtype: 'sworgfarmacyadvcombo',
							onTrigger1Click: function() 
							{
								if (this.disabled)
									return false;
								var combo = this;
								if (!this.formList)
								{
									this.formList = new sw.Promed.swListSearchWindow(
									{
										title: lang['poisk_apteki'],
										id: 'OrgFarmacySearch',
										object: 'OrgFarmacy',
										editformclassname: 'swOrgFarmacyEditWindow',
										store: this.getStore()
									});
								}
								this.formList.show(
								{
									onSelect: function(data) 
									{
										if (data.OrgFarmacy_id) {
											combo.getStore().load(
											{
												callback: function() 
												{
													combo.setValue(data.OrgFarmacy_id);
												},
												params: 
												{
													OrgFarmacy_id: data.OrgFarmacy_id
												}
											});
										}
									}, 
									onHide: function() 
									{
										combo.focus(false);
									}
								});
								return false;
							}
						}, {
							hiddenName: 'Drug_id',
							fieldLabel: lang['ls'],
							tabindex: TABINDEX_RRLW + 8,
							anchor: '-10',
							xtype: 'swdrugsimplecombo'
						}, {
							hiddenName: 'RegistryReceptType_id',
							fieldLabel: lang['tip_zapisi'],
							listWidth: 350,
							anchor: '-10',
							tabindex: TABINDEX_RRLW + 9,
							comboSubject: 'RegistryReceptType',
							xtype: 'swcommonsprcombo'
						}]						
					}]
				}]
			}]
		});
		
		this.formPanel = new Ext.Panel(
		{
			region: 'center',
			labelAlign: 'right',
			layout: 'border',
			labelWidth: 50,
			border: false,
			items:
			[
				this.filtersPanel,
				this.RegistryGrid,
				this.ErrorsGrid
			]
		});
		
		Ext.apply(this, 
		{
			items: 
			[ 
				form.formPanel
			],
			buttons:
			[{
				text: BTN_FIND,
				tabIndex: TABINDEX_RRLW + 10,
				handler: function() {
					form.doFilter();
				},
				iconCls: 'search16'
			}, 
			{
				text: BTN_RESETFILTER,
				tabIndex: TABINDEX_RRLW + 11,
				handler: function() {
					form.doResetFilters();
					form.doFilter();
				},
				iconCls: 'resetsearch16'
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_RRLW + 13),
			{
				iconCls: 'close16',
				tabIndex: TABINDEX_RRLW + 14,
				onTabAction: function()
				{
					form.filtersPanel.getForm().findField('RegistryRecept_Snils').focus();
				},
				handler: function() {
					form.hide();
				},
				text: BTN_FRMCLOSE
			}]
		});
		sw.Promed.swRegistryReceptListWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function() {
		sw.Promed.swRegistryReceptListWindow.superclass.show.apply(this, arguments);

		if ( !arguments[0] || !arguments[0].ReceptUploadLog_id ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.ReceptUploadLog_id = arguments[0].ReceptUploadLog_id;

		this.doResetFilters();
		this.doFilter();
	}
});