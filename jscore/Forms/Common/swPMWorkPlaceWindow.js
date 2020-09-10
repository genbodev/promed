/**
 * swPMWorkPlaceWindow - окно рабочего места менеджера проекта
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009, Swan.
 * @author       Dmitry Vlasenko
 * @version      11.2019
 */
/*NO PARSE JSON*/

sw.Promed.swPMWorkPlaceWindow = Ext.extend(sw.Promed.BaseForm,
{
	objectName: 'swPMWorkPlaceWindow',
	objectSrc: '/jscore/Forms/Common/swPMWorkPlaceWindow.js',
	closable: true,
	closeAction: 'hide',
	layout: 'border',
	maximized: true,
	title: 'Рабочее место менеджера проекта',
	iconCls: 'admin16',
	id: 'swPMWorkPlaceWindow',
	show: function()
	{
		sw.Promed.swPMWorkPlaceWindow.superclass.show.apply(this, arguments);

		if (!this.LpuGrid.getAction('action_onlineUsers')) {
			this.LpuGrid.addActions({
				name: 'action_onlineUsers',
				text: 'Пользователи онлайн',
				handler: function() {
					getWnd('swOnlineUsersWindow').show();
				}.createDelegate(this)
			}, 3);
		}

		var loadMask = new Ext.LoadMask(Ext.get('swPMWorkPlaceWindow'), {msg: LOAD_WAIT});
		loadMask.show();
		var form = this;
		
		form.loadGridWithFilter(true);
		if (arguments[0]) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
			this.ARMType = arguments[0].ARMType;
		}
		
		//Очистим $_SESSION['TOUZLpuArr'] - https://redmine.swan.perm.ru/issues/104824
		 /*Ext.Ajax.request({
			callback: function(options, success, response) {
			},			
			url: '/?c=Common&m=clearTOUZLpuArr'
		});*/

		loadMask.hide();

	},
	clearFilters: function ()
	{
		this.findById('wppmOrg_Nick').setValue('');
		this.findById('wppmOrg_Name').setValue('');
	},
	loadGridWithFilter: function(clear)
	{
		var form = this;
		if (clear)
			form.clearFilters();
		var OrgNick = this.findById('wppmOrg_Nick').getValue();
		var OrgName = this.findById('wppmOrg_Name').getValue();
		//TOUZLpuArr
		var filters = {Nick: OrgNick, Name: OrgName, start: 0, limit: 100, mode: 'lpu'};
		if(getGlobalOptions().TOUZLpuArr && getGlobalOptions().TOUZLpuArr.length > 0)
		{
			filters.LpuArr = getGlobalOptions().TOUZLpuArr.join(',').replace('\'','');
		}
		form.LpuGrid.loadData({globalFilters: filters});
	},
	initComponent: function()
	{
		Ext.apply(sw.Promed.Actions, {
			ContragentsAction:{
				nn: 'ContragentsAction',
				tooltip: lang['spravochnik_kontragentyi'],
	            text: lang['spravochnik_kontragentyi'],
	            iconCls : 'org16',
	            disabled: false,
	            handler: function() {
	                getWnd('swContragentViewWindow').show({
	                    onlyView: true
	                });
	            }
			},
			DrugOstatRegistryAction:{
				nn: 'DrugOstatRegistryAction',
				tooltip: langs('Остатки медикаментов'),
				text: langs('Остатки медикаментов'),
				iconCls : 'rls-torg32',
	            disabled: false,
	            handler: function() {
					getWnd('swDrugOstatRegistryListWindow').show({
						mode: 'suppliers'
					});
	            }
			},
			DokNaklAction: {
				nn: 'DokNaklAction',
				text: lang['prihodnyie_nakladnyie'],
				tooltip: lang['prihodnyie_nakladnyie'],
				iconCls: 'doc-nak16',
				handler: function()
				{
					getWnd('swDokNakViewWindow').show({viewOnly: true});
				}
			},
			DocUcAction: {
				nn: 'DocUcAction',
				tooltip: lang['dokumentyi_ucheta_medikamentov'],
				text: lang['dokumentyi_ucheta_medikamentov'],
				iconCls : 'document16',
				disabled: false,
				handler: function(){
					getWnd('swDokUcLpuViewWindow').show({viewOnly: true});
				}
			},
			ActSpisAction: {
				nn: 'ActSpisAction',
				text: lang['aktyi_spisaniya_medikamentov'],
				tooltip: lang['aktyi_spisaniya_medikamentov'],
				iconCls: 'doc-spis16',
				handler: function()
				{
					getWnd('swDokSpisViewWindow').show({viewOnly: true});
				}
			},
			DocOstAction: {
				nn: 'DocOstAction',
				text: lang['dokumentyi_vvoda_ostatkov'],
				tooltip: lang['dokumentyi_vvoda_ostatkov'],
				iconCls: 'doc-ost16',
				handler: function()
				{
					getWnd('swDokOstViewWindow').show({viewOnly: true});
				}	
			},
			InvVedAction: {
				nn: 'InvVedAction',
				text: lang['inventarizatsionnyie_vedomosti'],
				tooltip: lang['inventarizatsionnyie_vedomosti'],
				iconCls: 'farm-inv16',
				handler: function()
				{
					getWnd('swDokInvViewWindow').show({viewOnly: true});
					//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
				}
			},
			MKB10Action: {
				nn: 'MKB10Action',
				text: lang['spravochnik_mkb-10'],
				tooltip: lang['spravochnik_mkb-10'],
				iconCls: 'spr-mkb16',
				handler: function()
				{
					getWnd('swMkb10SearchWindow').show();
				}
			},
			ClinicRecommendAction: {
				nn: 'ClinicRecommendAction',
				text: langs('Клинические рекомендации'),
				tooltip: langs('Клинические рекомендации'),
				iconCls: '',
				handler: function()
				{
					getWnd('swCureStandartListWindow').show({ARMType: this.ARMType});
				}
			},
			DrugDocumentSprAction: {
				nn: 'DrugDocumentSprAction',
				text: lang['spravochniki_sistemyi_ucheta_medikamentov'],
				tooltip: lang['spravochniki_sistemyi_ucheta_medikamentov'],
				iconCls: '',
				handler: function()
				{
					getWnd('swDrugDocumentSprWindow').show({ARMType: this.ARMType});
				}
			},
			DrugListAction: {
				name: 'DrugListAction',
				text: 'Перечни медикаментов',
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugListSprWindow').show({ARMType: this.ARMType});
				}.createDelegate(this)
			},
			DrugNomenAction: {
				nn: 'DrugNomenAction',
				text: lang['nomenklaturnyiy_spravochnik'],
				tooltip: lang['nomenklaturnyiy_spravochnik'],
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugNomenSprWindow').show({readOnly: true});
				}
			},
			DrugMNNAction: {
				nn: 'DrugMNNAction',
				text: lang['spravochnik_mnn'],
				tooltip: lang['spravochnik_mnn'],
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugMnnCodeViewWindow').show({action: 'view'});
				}
			},
			DrugTorgAction: {
				nn: 'DrugTorgAction',
				text: lang['spravochnik_torgovyih_naimenovaniy'],
				tooltip: lang['spravochnik_torgovyih_naimenovaniy'],
				iconCls : '',
				handler: function()
				{
					getWnd('swDrugTorgCodeViewWindow').show({action: 'view'});
				}
			},
			PriceJNVLPAction: {
				nn: 'PriceJNVLPAction',
				text: lang['tsenyi_na_jnvlp'],
				tooltip: lang['tsenyi_na_jnvlp'],
				iconCls : 'dlo16',
				handler: function() {
					getWnd('swJNVLPPriceViewWindow').show();
				}
			},
			DrugMarkupAction: {
				nn: 'DrugMarkupAction',
				text: lang['predelnyie_nadbavki_na_jnvlp'],
				tooltip: lang['predelnyie_nadbavki_na_jnvlp'],
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swDrugMarkupViewWindow').show({readOnly: true});
				}
			},
			DrugRMZAction: {
				nn: 'DrugRMZAction',
				text: lang['spravochnik_rzn'],
				tooltip: lang['spravochnik_rzn'],
                iconCls : 'view16',
                handler: function() {
                    getWnd('swDrugRMZViewWindow').show({action:'view'});
                }
			},
			TariffAction: {
				nn: 'TariffAction',
				text: lang['tarifyi_llo'],
				tooltip: lang['tarifyi_llo'],
				iconCls : 'lpu-finans16',
				handler: function() {
					getWnd('swUslugaComplexTariffLloViewWindow').show({viewOnly: true, allowEdit: true});
				}
			},
			PrepBlockSprAction: {
				nn: 'PrepBlockSprAction',
				text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
				tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
				handler: function()
				{
					getWnd('swPrepBlockViewWindow').show();
				}
			},
			GoodsUnitAction: {
				nn: 'GoodsUnitAction',
				text: lang['edinitsyi_izmereniya_tovara'],
				tooltip: lang['edinitsyi_izmereniya_tovara'],
				handler: function()
				{
					getWnd('swGoodsUnitViewWindow').show({allowImportFromRls: true, viewOnly: true});
				}
			}
		});

		var form = this;
		var configActions = 
		{
			action_selectLpu:
			{
				nn: 'action_selectLpu',
				tooltip: 'Выбрать МО просмотра',
				text: 'Выбрать МО просмотра',
				iconCls: 'lpu-select16',
				disabled: false, 
				handler: function() 
				{
					getWnd('swChangeLpuWindow').show();
				}
			},
			action_openRecordMaster: {
				nn: 'action_openRecordMaster',
				text: lang['raspisanie'],
				tooltip: lang['raspisanie'],
				iconCls: 'eph-timetable-top32',
				handler: function()
				{
					getWnd('swDirectionMasterWindow').show({
						userMedStaffFact: {ARMType: this.ARMType}
					});
				}.createDelegate(this)
			},
			action_reports:
			{
				nn: 'action_Report',
				tooltip: lang['prosmotr_otchetov'],
				text: lang['prosmotr_otchetov'],
				iconCls: 'report32',
				handler: function() {
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
			action_Farm: {
				nn: 'action_Farm',
				tooltip: lang['apteka'],
				text: lang['apteka'],
				iconCls: 'plan32',
				menu: new Ext.menu.Menu(
				{
					items:[
						sw.Promed.Actions.ContragentsAction, //Справочник контрагенты
						sw.Promed.Actions.DrugOstatRegistryAction,
						sw.Promed.Actions.DokNaklAction, //Приходные накладные
						sw.Promed.Actions.DocUcAction, //Документы учета медикаментов
						sw.Promed.Actions.ActSpisAction, //Акты списания медикаментов
						sw.Promed.Actions.DocOstAction, //Документы ввода остатков
						sw.Promed.Actions.InvVedAction //Инвентаризационные ведомости
					]
				})
			},
			action_Spr: {
				nn: 'action_Spr',
				tooltip: lang['spravochniki'],
				text: lang['spravochniki'],
				iconCls : 'book32',
				disabled: false,
				menuAlign: 'tr',
				menu: new Ext.menu.Menu(
				{
					items:
					[
						sw.Promed.Actions.MKB10Action, // Справочник МКБ-10
						sw.Promed.Actions.ClinicRecommendAction, // Клинические рекомендации
						sw.Promed.Actions.DrugDocumentSprAction, // Справочники системы учета медикаментов
						sw.Promed.Actions.DrugListAction, //Номенклатурный справочник
						sw.Promed.Actions.DrugNomenAction, //Номенклатурный справочник
						sw.Promed.Actions.DrugMNNAction, //Номенклатурный справочник
						sw.Promed.Actions.DrugTorgAction, //Справочник торговых наименований
						sw.Promed.Actions.PriceJNVLPAction, //Цены на ЖНВЛП
						sw.Promed.Actions.DrugMarkupAction, //Предельные надбавки на ЖНВЛП
						sw.Promed.Actions.DrugRMZAction, //Справочник РЗН
						sw.Promed.Actions.TariffAction, //Тарифы ЛЛО
						sw.Promed.Actions.PrepBlockSprAction, //Справочник фальсификатов и забракованных серий ЛС
						sw.Promed.Actions.GoodsUnitAction //Единицы измерения товара
					]
				})
			},
			action_JourNotice: {
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy'],
				iconCls: 'notice32',
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}
			}
		};

		form.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = [
			'action_selectLpu',
			'action_openRecordMaster',
			'action_reports',
			'action_Farm',
			'action_Spr',
			'action_JourNotice'
		];

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
			id: form.id + '_mz',
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
			bodyStyle: 'padding-left: 5px',
			width: 60,
			minSize: 60,
			maxSize: 120,
			id: 'awpwLeftPanel',
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
					el = form.findById(form.id + '_slid'); //_slid
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
					iconCls:'uparrow',
					disabled: false,
					handler: function() 
					{
						var el = form.findById(form.id + '_mz');
						var d = el.body.dom;
						d.scrollTop -=38;
					}
				}),
				{
					border: false,
					layout:'border',
					id: form.id + '_slid', //_slid
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
					var el = form.findById(form.id + '_mz');
					var d = el.body.dom;
					d.scrollTop +=38;
					
					
				}
				})]
		};


		this.LpuFilterPanel = new Ext.form.FieldSet(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:0px;',
			border: true,
			autoHeight: true,
			region: 'north',
			layout: 'column',
			title: lang['filtryi'],
			id: 'OrgLpuFilterPanel',
			items: 
			[{
				// Левая часть фильтров
				labelAlign: 'top',
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .44,
				items: 
				[{
					name: 'Org_Name',
					anchor: '100%',
					disabled: false,
					fieldLabel: lang['naimenovanie_organizatsii'],
					tabIndex: 0,
					xtype: 'textfield',
					id: 'wppmOrg_Name'
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
				columnWidth: .44,
				items:
				[{
					name: 'Org_Nick',
					anchor: '100%',
					disabled: false,
					fieldLabel: lang['kratkoe_naimenovanie'],
					tabIndex: 0,
					xtype: 'textfield',
					id: 'wppmOrg_Nick'
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
				columnWidth: .12,
				items:
				[{
					xtype: 'button',
					text: lang['nayti'],
					tabIndex: 4217,
					minWidth: 110,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'wppmButtonSetFilter',
					handler: function ()
					{
						Ext.getCmp('swPMWorkPlaceWindow').loadGridWithFilter();
					}
				},
				{
					xtype: 'button',
					text: lang['sbros'],
					tabIndex: 4218,
					minWidth: 110,
					disabled: false,
					topLevel: true,
					allowBlank:true, 
					id: 'wmpzButtonUnSetFilter',
					handler: function ()
					{
						Ext.getCmp('swPMWorkPlaceWindow').loadGridWithFilter(true);
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
					
					Ext.getCmp('swPMWorkPlaceWindow').loadGridWithFilter();
				},
				stopEvent: true
			}]
		});

		// Организации
		this.LpuGrid = new sw.Promed.ViewFrame(
		{
			id: 'wppmLpuGridPanel',
			tbar: this.gridToolbar,
			region: 'center',
			layout: 'fit',
			paging: true,
			object: 'Org',
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
					var grid = Ext.getCmp('wppmLpuGridPanel');
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
			//toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'Org_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', type: 'int', header: lang['id_lpu'], key: true},
				{name: 'Org_IsAccess', type:'checkbox', header: lang['dostup_v_sistemu'], width: 60},
				{name: 'DLO', type:'checkbox', header: lang['llo'], width: 40},
				{name: 'OMS', type:'checkbox', header: lang['oms'], width: 40},
				{id: 'Lpu_Ouz', name: 'Lpu_Ouz', header: lang['kod_ouz'], width: 80},
				{name: 'Org_Name', id: 'autoexpand', header: lang['polnoe_naimenovanie']},
				{name: 'Org_Nick', header: lang['kratkoe_naimenovanie'], width: 240},
				{name: 'KLArea_Name', header: lang['territoriya'], width: 160},
				{name: 'Org_OGRN', header: lang['ogrn'], width: 120},
				{name: 'Lpu_begDate', header: lang['data_nachala_deyatelnosti'], width: 80},
				{name: 'Lpu_endDate', header: lang['data_zakryitiya'], width: 80},
				// Поля для отображения в дополнительной панели
				{name: 'UAddress_Address', hidden: true},
				{name: 'PAddress_Address', hidden: true}
			],
			actions:
			[
				{name:'action_add', hidden: true},
				{name:'action_edit', iconCls : 'x-btn-text', icon: 'img/icons/lpu16.png', text: lang['pasport_mo'], handler: function()
					{
						this.Lpu_id = Ext.getCmp('wppmLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuPassportEditWindow').show({
							action: 'view',
							Lpu_id: this.Lpu_id
						});
					}
				},		
				{name:'action_view', iconCls : 'x-btn-text', icon: 'img/icons/lpu-struc16.png', text: lang['struktura_mo'], handler: function()
					{
						this.Lpu_id = Ext.getCmp('wppmLpuGridPanel').ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
						getWnd('swLpuStructureViewForm').show({
							action: 'view',
							Lpu_id: this.Lpu_id
						});
					}
				},
				{name:'action_delete', hidden: true},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('swPMWorkPlaceWindow');
				var form = Ext.getCmp('wppmLpuGridPanel');
				if ( win.mode && win.mode == 'lpu')
				/*{
					var Lpu_id = form.ViewGridPanel.getSelectionModel().getSelected().get('Lpu_id');
					form.getAction('action_edit').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
					form.getAction('action_view').setDisabled( Lpu_id != getGlobalOptions().lpu_id && !isSuperAdmin() );
				}*/
				var UAddress_Address = record.get('UAddress_Address');
				var PAddress_Address = record.get('PAddress_Address');
				win.LpuDetailTpl.overwrite(win.LpuDetailPanel.body, {UAddress_Address:UAddress_Address, PAddress_Address:PAddress_Address}); 
			}
		});


		this.LpuGrid.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('Lpu_endDate')!=null && row.get('Lpu_endDate').length > 0)
					cls = cls+'x-grid-rowgray ';
				return cls;
			}
		});		
		var LpuDetailTplMark = 
		[
			'<div style="height:44px;">'+
				'<div>Юридический адрес: <b>{UAddress_Address}</b></div>'+
				'<div>Фактический адрес: <b>{PAddress_Address}</b></div>'+
			'</div>'
		];
		this.LpuDetailTpl = new Ext.Template(LpuDetailTplMark);
		this.LpuDetailPanel = new Ext.Panel(
		{
			id: 'LpuDetailPanel',
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
			layout: 'border',
			items: 
			[
				this.LpuFilterPanel,
				this.leftPanel,
				{
					layout: 'fit',
					region: 'center',
					border: false,
					items:
					[
						this.LpuGrid
					]
				},
				this.LpuDetailPanel				
			],
			buttons: 
			[{
				text: '-'
			}, 
			HelpButton(this, TABINDEX_MPSCHED + 98), 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() {this.hide();}.createDelegate(this)
			}]
		});

		sw.Promed.swPMWorkPlaceWindow.superclass.initComponent.apply(this, arguments);
	}
});