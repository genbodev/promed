/**
* swOrgViewForm - форма просмотра организаций.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Андрей Марков
* @version      12.2009
* @comment      
*
*/

sw.Promed.swOrgViewForm = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['spravochnik_organizatsiy'],
	layout: 'border',
	id: 'OrgViewForm',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	buttons:
	[
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	show: function()
	{
		sw.Promed.swOrgViewForm.superclass.show.apply(this, arguments);
		
		this.mode = null;
		this.setTitle(lang['spravochnik_organizatsiy']);
		
		// устанавливаем режим работы формы, и меняем заголовок, если что пока работаем только с МО
		if ( arguments[0] && arguments[0].mode && arguments[0].mode == 'lpu' )
		{
			this.setTitle(lang['spravochnik_mo']);
			this.mode = arguments[0].mode;
		}
		
		var loadMask = new Ext.LoadMask(Ext.get('OrgViewForm'), {msg: LOAD_WAIT});
		loadMask.show();
		var form = this;
		//form.enableLpuValues(false);
		
		// Установка фильтров при открытии формы просмотра
		// form.findById('ovOrgStatus_id').setValue('');
		
		form.loadGridWithFilter(true);
		loadMask.hide();
		
		this.OrgGrid.addActions({
			name:'action_new',
			hidden: false,
			text:lang['deystviya'],
			menu: [{
				text: lang['obyedinenie'],
				tooltip: lang['dobavit_zapis_k_obyedineniyu'],
				handler: function() {
					AddRecordToUnion(
						Ext.getCmp('OrgGridPanel').ViewGridPanel.getSelectionModel().getSelected(),
						'Org',
						lang['organizatsii'],
						function () {
							Ext.getCmp('OrgGridPanel').loadData();
						}
					)
				},
				iconCls: 'union16'
			}]
		});
		
		if (isSuperAdmin()) {
			this.OrgGrid.addActions({
				name:'action_orgstructure',
				hidden: false,
				text:lang['struktura_organizatsii'],
				handler: function() {
					var record = form.OrgGrid.getGrid().getSelectionModel().getSelected();

					if (record && record.get('Org_IsAccess') == 'true') {
						getWnd('swOrgStructureWindow').show({
							Org_id: record.get('Org_id')
						});
					}
				},
				iconCls: 'lpu-struc16'
			});
			
			this.OrgGrid.addActions({
				name:'action_grantaccess',
				hidden: false,
				text:lang['razreshit_dostup'],
				handler: function() {
					var record = form.OrgGrid.getGrid().getSelectionModel().getSelected();
					if (record) {
						Ext.Ajax.request({
							url: '/?c=Org&m=giveOrgAccess',
							params: {
								Org_id: record.get('Org_id'),
								grant: 2
							},
							callback: function(options, success, response) {
								if (success) {
									var result = Ext.util.JSON.decode(response.responseText);
									if (result.success) {
										form.OrgGrid.getGrid().getStore().reload();
									} else if (result.Error_Code && result.Error_Code == 1) {
										var params = {
											action: 'add',
											formMode: 'orgaccess',
											fields: {
												action: 'add',
												org_id: record.get('Org_id')
											},
											onClose: function() {
												form.OrgGrid.getGrid().getStore().reload();
											}
										}
										getWnd('swUserEditWindow').show(params);
									}
								}
							}
						});
					}
				},
				iconCls: 'spr-org16'
			});
			
			this.OrgGrid.addActions({
				name:'action_denyaccess',
				hidden: false,
				text:lang['zapretit_dostup'],
				handler: function() {
					var record = form.OrgGrid.getGrid().getSelectionModel().getSelected();
					if (record) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									Ext.Ajax.request({
										url : '/?c=Org&m=giveOrgAccess',
										params : {
											Org_id: record.get('Org_id'),
											grant: 1
										},
										callback : function() {													
											form.OrgGrid.getGrid().getStore().reload();
										}
									});
								}
							},
							msg: lang['vyi_uverenyi_chto_namerenyi_zapretit_dostup_k_sisteme_vsem_polzovatelyam']+record.get('Org_Nick')+'?',
							title: lang['podtverjdenie']
						});
					}
				},
				iconCls: 'org-denied16'
			});
		}
	},
	/*
	loadIsOnko: function()
	{
		if (getGlobalOptions().isOnko == undefined)
		{
			Ext.Ajax.request(
			{
				url: '/?c=Org&m=isOnko',
				callback: function(options, success, response) 
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
							getGlobalOptions().isOnko = result.result;
					}
				}
			});
		}
	},*/
	clearFilters: function ()
	{
		this.findById('ovOrg_Nick').setValue('');
		this.findById('ovOrg_Name').setValue('');
		this.findById('ovOrg_Type').setValue('');
		this.findById('ovOnlyOrgStac').setValue(false);
	},
	enableLpuValues: function (flag)
	{
		if (flag)
		{
			//this.EditLpuPanel.setVisible(true);
			//this.doLayout();
		}
		else 
		{
			//this.EditLpuPanel.setVisible(false);
			//this.doLayout();
			this.findById('ovFedLgotCount').setValue('');
			this.findById('ovRegLgotCount').setValue('');
		}
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear)
			form.clearFilters();
		var OrgNick = this.findById('ovOrg_Nick').getValue();
		var OrgName = this.findById('ovOrg_Name').getValue();
		var Org_Type = this.findById('ovOrg_Type').getValue();
		var OnlyOrgStac = (this.findById('ovOnlyOrgStac').getValue() == true ? 2 : 1);
		var filters = {Nick: OrgNick, Name: OrgName, Type: Org_Type, start: 0, limit: 100, OnlyOrgStac: OnlyOrgStac};
		if ( this.mode )
			filters.mode = this.mode;
		else
			filters.mode = null;
		form.OrgGrid.loadData({globalFilters: filters});
		//form.OrgGrid.removeAll(true);
		//var Lpu_id = this.findById('ovLpu_id').getValue() || getGlobalOptions().lpu_id;
		/*if (clear)
		{
			//form.clearFilters();
			form.OrgGrid.loadData({globalFilters: {Nick: '', Name: ''}});
		}
		else 
		{
			var OrgPeriod_id = this.findById('ovOrgPeriod_id').getValue() || '';
			var MedPersonal_id = this.findById('ovMedPersonal_id').getValue() || '';
			var OrgStatus_id = this.findById('ovOrgStatus_id').getValue() || '';
			var LpuSection_id = this.findById('ovLpuSection_id').getValue() || '';
			
			if (OrgPeriod_id==0)
			{
				sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ovOrgPeriod_id').focus();});
				return false;
			}
			form.getLpuClose();
			form.getLpuUt();
			// params: {Lpu_id: Lpu_id, MedPersonal_id: MedPersonal_id, OrgPeriod_id: OrgPeriod_id, OrgStatus_id: OrgStatus_id, LpuSection_id: LpuSection_id},
			form.OrgGrid.loadData({globalFilters: {Lpu_id: Lpu_id, MedPersonal_id: MedPersonal_id, OrgPeriod_id: OrgPeriod_id, OrgStatus_id: OrgStatus_id, LpuSection_id: LpuSection_id}});
		}
		*/
	},
	saveLpuValue: function()
	{
		var form = this;
		var OrgPeriod_id = form.findById('ovOrgPeriod_id').getValue() || 0;
		var OrgTotalStatus_IsClose = form.isCloseLpu;
		var OrgTotalStatus_FedLgotCount = form.findById('ovOrgTotalStatus_FedLgotCount').getValue();
		var OrgTotalStatus_RegLgotCount = form.findById('ovOrgTotalStatus_RegLgotCount').getValue();
		var Lpu_id = form.findById('ovLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (OrgPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ovOrgPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=Org&m=index&method=saveOrgLpu',
			params: 
			{	
				OrgPeriod_id: OrgPeriod_id,
				OrgTotalStatus_IsClose: OrgTotalStatus_IsClose,
				Lpu_id: Lpu_id,
				OrgTotalStatus_FedLgotCount:OrgTotalStatus_FedLgotCount,
				OrgTotalStatus_RegLgotCount:OrgTotalStatus_RegLgotCount
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					sw.swMsg.alert(lang['soobschenie'], lang['dannyie_dlya_rascheta_limita_uspeshno_sohranenyi']);
				}
				else 
				{
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshla_oshibka_poprobuyte_povtorit_sohranenie']);
				}
			}
		});
	},
	setLpuClose: function()
	{
		var form = this;
		var OrgPeriod_id = form.findById('ovOrgPeriod_id').getValue() || 0;
		var OrgTotalStatus_IsClose = (form.isCloseLpu==1)?2:1;
		var Lpu_id = form.findById('ovLpu_id').getValue() || getGlobalOptions().lpu_id;
		if (OrgPeriod_id==0)
		{
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_obyazatelno_ukazat_filtr_po_polyu_period'], function() {form.findById('ovOrgPeriod_id').focus();});
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=Org&m=index&method=setOrgLpuClose',
			params: 
			{	
				OrgPeriod_id: OrgPeriod_id,
				OrgTotalStatus_IsClose: OrgTotalStatus_IsClose,
				Lpu_id: Lpu_id
			},
			callback: function(options, success, response) 
			{
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.OrgTotalStatus_IsClose)
					{
						form.setVisualLpuClose((result.OrgTotalStatus_IsClose==2));
						form.loadGridWithFilter();
					}
					else 
					{
						form.setVisualLpuClose(false);
					}
				}
			}
		});
	},
	initComponent: function()
	{
		var form = this;
		this.FilterPanel = new Ext.FormPanel(
		{
			autoHeight: true,
			frame: true,
			region: 'north',
			border: false,
			items:  [
				new Ext.form.FieldSet({
					bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
					border: true,
					autoHeight: true,
					layout: 'column',
					title: lang['filtryi'],
					id: 'OrgFilterPanel',
					items: 
					[{
						// Левая часть фильтров
						labelAlign: 'top',
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-right:5px;',
						columnWidth: .25,
						items: 
						[{
							name: 'Org_Name',
							anchor: '100%',
							disabled: false,
							fieldLabel: lang['naimenovanie_organizatsii'],
							tabIndex: 0,
							xtype: 'textfield',
							id: 'ovOrg_Name'
						},
						{
							xtype: 'hidden',
							anchor: '100%'
						}]
					},
					{
						// Средняя часть фильтров
						labelAlign: 'top',
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-left:5px;',
						columnWidth: .25,
						items:
						[{
							name: 'Org_Nick',
							anchor: '100%',
							disabled: false,
							fieldLabel: lang['kratkoe_naimenovanie'],
							tabIndex: 0,
							xtype: 'textfield',
							id: 'ovOrg_Nick'
						},
						{
							xtype: 'hidden',
							anchor: '100%'
						}]
					},
					{
						labelAlign: 'top',
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-left:5px;',
						columnWidth: .20,
						items:
						[{
							allowBlank: true,
							comboSubject: 'OrgType',
							anchor: '100%',
							disabled: false,
							typeCode: 'int',
							id: 'ovOrg_Type',
							hiddenName: 'OrgType_id',
							fieldLabel: lang['tip'],
							tabIndex: 0,
							xtype: 'swcommonsprcombo'
						},
						{
							xtype: 'hidden',
							anchor: '100%'
						}]
					},
					{
						labelAlign: 'top',
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-left:5px;',
						columnWidth: .15,
						items:
						[{
							boxLabel: lang['statsionarnyie_uchrejdeniya'],
							fieldLabel: '',
							labelSeparator: '',
							id: 'ovOnlyOrgStac',
							name: 'OnlyOrgStac',
							xtype: 'checkbox'
						},
						{
							xtype: 'hidden',
							anchor: '100%'
						}]
					},
					{
						// Правая часть фильтров (кнопка)
						layout: 'form',
						border: false,
						bodyStyle:'background:#DFE8F6;padding-left:5px;',
						columnWidth: .15,
						items:
						[{
							xtype: 'button',
							text: lang['ustanovit'],
							tabIndex: 4217,
							minWidth: 110,
							disabled: false,
							topLevel: true,
							allowBlank:true, 
							id: 'ovButtonSetFilter',
							handler: function ()
							{
								Ext.getCmp('OrgViewForm').loadGridWithFilter();
							}
						},
						{
							xtype: 'button',
							text: lang['otmenit'],
							tabIndex: 4218,
							minWidth: 110,
							disabled: false,
							topLevel: true,
							allowBlank:true, 
							id: 'ovButtonUnSetFilter',
							handler: function ()
							{
								Ext.getCmp('OrgViewForm').loadGridWithFilter(true);
							}
						}]
					}],
					keys: [{
						key: [
							Ext.EventObject.ENTER
						],
						fn: function(inp, e) {
							e.stopEvent();

							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}
							
							Ext.getCmp('OrgViewForm').loadGridWithFilter();
						},
						stopEvent: true
					}]
				})
			]
		});
		
		// Организации
		this.OrgGrid = new sw.Promed.ViewFrame(
		{
			id: 'OrgGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'Org',
			editformclassname: 'swOrgEditForm',
			dataUrl: '/?c=Org&m=getOrgView',
			keys: [{
				key: [
					Ext.EventObject.F6
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					var grid = Ext.getCmp('OrgGridPanel');
					if (!grid.getAction('action_new').isDisabled()) {
						if (e.altKey) {
							AddRecordToUnion(
								grid.getGrid().getSelectionModel().getSelected(),
								'Org',
								lang['organizatsii'],
								function () {
									grid.loadData();
								}
							)
						}
					}
				},
				stopEvent: true
			}],
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'Org_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Org_IsAccess', type:'checkbox', header: langs('Доступ в систему'), width: 60},
				{name: 'Org_Type', header: langs('Тип'), width: 35},
				{name: 'Org_Name', id: 'autoexpand', header: langs('Полное наименование')},
				{name: 'Org_Nick', header: langs('Краткое наимнование'), width: 300},
				// Поля для отображения в дополнительной панели
				{name: 'UAddress_Address', hidden: true},
				{name: 'PAddress_Address', hidden: true},
				{name: 'OrgType_id', hidden:true},
				{name: 'Org_External', type:'checkbox', header: 'Внешний источник', width: 120}
			],
			deniedDeleteRecord: function(){
				var access = true;
				if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') )
					access = false;
				else
					sw.swMsg.alert(lang['oshibka'], lang['obratites_k_adminu_spravochnika_org']+getGlobalOptions().contact_info);
				return access;
			},
			actions:
			[
				{name:'action_add', handler: function(){

						if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') ){
							getWnd('swOrgEditWindow').show({
								action: 'add',
								orgType: 'all',
								org_add: true
							});
						}
						else{
							sw.swMsg.alert(lang['oshibka'], lang['obratites_k_adminu_spravochnika_org']+getGlobalOptions().contact_info);
							return false;
						}
					}
				},
				{name:'action_edit', handler: function(){
						if(!(getGlobalOptions().enable_action_reference_by_admref_group) || isUserGroup('AdminOrgReference') ){
						getWnd('swOrgEditWindow').show({
							action: 'edit',
							Org_id: Ext.getCmp('OrgGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Org_id'),
							orgType: 'all'
						});
						}
						else{
							sw.swMsg.alert(lang['oshibka'], lang['obratites_k_adminu_spravochnika_org']+getGlobalOptions().contact_info);
							return false;
						}
					}
				},
				{name:'action_view', handler: function()
					{
						getWnd('swOrgEditWindow').show({
							action: 'view',
							Org_id: Ext.getCmp('OrgGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Org_id'),
							orgType: 'all'
						});
					}
				},
				{name:'action_delete', url: C_RECORD_DEL},
				{name: 'action_refresh'},
				{name: 'action_print'}
			], 
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('OrgViewForm');
				var form = Ext.getCmp('OrgGridPanel');
				if ( win.mode && win.mode == 'lpu')
				{
					var Lpu_id = form.ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
					form.getAction('action_edit').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
					form.getAction('action_view').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );

					if(getRegionNick() == 'ekb'){
						form.getAction('action_edit').setDisabled( !(isLpuAdmin() || isSuperAdmin() || isUserGroup('LpuAdmin')) );
					}
				}
				var UAddress_Address = record.get('UAddress_Address');
				var PAddress_Address = record.get('PAddress_Address');
				
				if (isSuperAdmin()) {
					this.getAction('action_grantaccess').disable();
					this.getAction('action_denyaccess').disable();
					this.getAction('action_orgstructure').disable();
					
					if (record.get('Org_IsAccess') == 'true') {
						this.getAction('action_denyaccess').enable();
						this.getAction('action_orgstructure').enable();
					} else {
						this.getAction('action_grantaccess').enable();
					}
				}
				
				win.detailTpl.overwrite(win.detailPanel.body, {UAddress_Address:UAddress_Address, PAddress_Address:PAddress_Address}); 
				
			}
		});	

		var detailTplMark = 
		[
			'<div style="height:44px;">'+
				'<div>Юридический адрес: <b>{UAddress_Address}</b></div>'+
				'<div>Фактический адрес: <b>{PAddress_Address}</b></div>'+
			'</div>'
		];
		this.detailTpl = new Ext.Template(detailTplMark);
		this.detailPanel = new Ext.Panel(
		{
			id: 'detailPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: true,
			height: 44,
			maxSize: 44,
			html: ''
		});

		
		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: 
			[
				form.FilterPanel,
				{
					border: false,
					region: 'center',
					layout: 'border',
					defaults: {split: true},
					items: 
					[
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.OrgGrid]
						}
					]
				},
				form.detailPanel
			]
			/*
			items:
			[
				form.FilterPanel,
				form.OrgGrid
			]
			*/
		});
		sw.Promed.swOrgViewForm.superclass.initComponent.apply(this, arguments);
	}

});
