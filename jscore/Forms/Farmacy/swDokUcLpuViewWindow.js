/**
* swDokUcLpuViewWindow - просмотр документов учета медикаментов в ЛПУ .
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Андрей Марков
* @version      10.2010
* @comment      
*
*/
/*NO PARSE JSON*/
sw.Promed.swDokUcLpuViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['dokumentyi_ucheta_medikamentov'],
	layout: 'border',
	id: 'DokUcLpuViewWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	codeRefresh: true,
	objectName: 'swDokUcLpuViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokUcLpuViewWindow.js',
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
	openRecordEditWindow: function(action, gridCmp) {
		var grid = gridCmp.getGrid();
		var params = new Object();
		if (action == 'add' && this.Contragent_tid) {
			params.Contragent_tid = this.Contragent_tid;
		}
		params.callback = function() {
			gridCmp.ViewActions.action_refresh.execute();
		};
		getWnd(gridCmp.editformclassname).show(params);
	},
	show: function()
	{
		sw.Promed.swDokUcLpuViewWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('DokUcLpuViewWindow'), { msg: LOAD_WAIT });
		var current_window = this;
		var form = current_window.findById('dulvFilterForm');
		current_window.Contragent_tid = null;
		loadMask.show();
		if (arguments[0] && arguments[0].Contragent_tid) {
			current_window.Contragent_tid = arguments[0].Contragent_tid;
		}
		form.findById('dulvContragent_sid').getStore().baseParams.mode = 'sender';
		form.findById('dulvContragent_tid').getStore().baseParams.mode = 'receiver';
		current_window.doReset();
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		// Установка фильтров при открытии формы просмотра 
		current_window.DokUcLpuGrid.setReadOnly(this.viewOnly);
		current_window.DokUcLpuGrid.setActionHidden('action_add',this.viewOnly);
		current_window.DokUcLpuGrid.setActionHidden('action_edit',this.viewOnly);
		current_window.DokUcLpuGrid.setActionHidden('action_delete',this.viewOnly);
		loadMask.hide();
	},
	doReset: function() {
		var current_window = this;
		var form = current_window.findById('dulvFilterForm');
		form.getForm().reset();
		loadContragent(current_window, 'dulvContragent_sid');
		if (current_window.Contragent_tid) {
			form.findById('dulvContragent_tid').setValue(current_window.Contragent_tid);
			form.findById('dulvContragent_tid').disable();
		}
		loadContragent(current_window, 'dulvContragent_tid', null, function() {
			loadSprMol(current_window, 'dulvMol_tid','dulvContragent_tid');
		}.createDelegate(current_window));
		this.doSearch();
	},
	doSearch: function() {
		var form = this.findById('dulvFilterForm');
		var params = form.getForm().getValues();
		params.start = 0;
		params.limit = 100;
		if (!params.Mol_tid) //так как поле может блокироватся, необходимо значение по умолчанию
			params.Mol_tid = "";
		if (!params.Contragent_tid) {
			params.Contragent_tid = form.findById('dulvContragent_tid').getValue();
		}
		this.DokUcLpuGrid.loadData({globalFilters: params});
	},
	searchFieldKeydown: function(object, key) {
		if (key.keyCode == 13)
			Ext.getCmp('DokUcLpuViewWindow').doSearch();
	},
	initComponent: function()
	{
		var form = this;
		
		// Панль с фильтрами
		this.SearchPanel = new Ext.Panel({
			frame: true,
			animCollapse: false,
			bodyStyle: 'padding: 0px;',
			height: 250,
			minSize: 0,
			maxSize: 250,
			floatable: false,
			collapsible: true,
			border: true,
			title: lang['poisk'],
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			split: true,
			layoutConfig: {
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items:[{
				xtype: 'form',
				id: 'dulvFilterForm',
				labelAlign: 'right',
				labelWidth: 130,
				height: 218,
				layout: 'column',
				items: [{
					layout: 'column',						
					labelAlign: 'right',
					width: 1000,
					style: 'margin-top: 5px; padding-left: 0px;',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swcontragentcombo',
							disabled: false,
							width: 380,
							id: 'dulvContragent_sid',
							name: 'Contragent_sid',
							hiddenName:'Contragent_sid',
							fieldLabel: lang['postavschik'],
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}]
				}, {
					xtype: 'fieldset',
					autoHeight: true,
					width: 540,
					style: 'padding: 3px 0px 3px 0px; margin-bottom:8px; display:block;',
					title: lang['poluchatel'],
					items: [{
						xtype: 'swcontragentcombo',
						disabled: false,
						width: 380,
						id: 'dulvContragent_tid',
						name: 'Contragent_tid',
						hiddenName:'Contragent_tid',
						fieldLabel: lang['poluchatel'],
						listeners: {
							'keydown': this.searchFieldKeydown,
							'change': function(combo) {
								this.findById('dulvMol_tid').setDisabled(!(combo.getValue()>0));							
								if ((combo.getValue()>0) && ((combo.getFieldValue('ContragentType_id')==2) || (combo.getFieldValue('ContragentType_id')==3 && isFarmacyInterface) || (combo.getFieldValue('ContragentType_id')==5))) {
									this.findById('dulvMol_tid').enable();
									setFilterMol(this.findById('dulvMol_tid'), combo.getValue());
								} else {
									this.findById('dulvMol_tid').disable();									
									this.findById('dulvMol_tid').setValue(null);
								}
							}.createDelegate(this)
						}						
					}, {						
						width: 380,
						hiddenName: 'Mol_tid',
						fieldLabel: lang['mol_poluchatelya'],
						id: 'dulvMol_tid',
						lastQuery: '',
						linkedElements: [ ],						
						xtype: 'swmolcombo'
					}]
				}, {
					layout: 'column',						
					labelAlign: 'right',
					width: 1000,
					items: [{
						layout: 'form',
						items: [{
							xtype: 'textfield',
							width: 123,
							id: 'dulvSearch_Num',
							name: 'DocumentUc_Num',
							fieldLabel: lang['nomer_dokumenta'],
							value: '',
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}, {
						layout: 'form',
						items: [{
							xtype: 'swdatefield',
							disabled: false,
							width: 123,
							format: 'd.m.Y',
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
							id: 'dulvSearch_Date',
							name: 'DocumentUc_setDate',
							fieldLabel: lang['data_podpisaniya'],
							listeners: {'keydown': this.searchFieldKeydown}
						}]
					}]
				}, {
					layout: 'form',
					width: 1000,
					items:[{
						fieldLabel: lang['istochnik_finans'],
						xtype: 'swdrugfinancecombo',
						name: 'DrugFinance_id',
						width: 381
					}]
				}, {
					layout: 'form',
					width: 1000,
					items:[{
						fieldLabel: lang['statya_rashodov'],
						xtype: 'swwhsdocumentcostitemtypecombo',
						name: 'WhsDocumentCostItemType_id',
						width: 381
					}]
				}, {
					layout: 'column',						
					labelAlign: 'right',
					labelWidth: 120,
					width: 1000,
					style: "margin-top: 5px",
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 0px",
							xtype: 'button',
							id: 'dulvBtnSearch',
							text: lang['poisk'],
							iconCls: 'search16',
							handler: function() {
								var form = Ext.getCmp('DokUcLpuViewWindow');
								form.doSearch();
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: 'dulvBtnClear',
							text: lang['sbros'],
							iconCls: 'resetsearch16',
							handler: function() {								
								var form = Ext.getCmp('DokUcLpuViewWindow');																
								form.doReset();
							}
						}]
					}]
				}]
			}]
		});
		
		
		// Документы ввода остатков
		this.DokUcLpuGrid = new sw.Promed.ViewFrame(
		{
			id: 'DokUcLpuGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'DocumentUc',
			editformclassname: 'swDokUcLpuEditWindow',
			dataUrl: '/?c=Farmacy&m=load&method=DokUcLpu',
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'DocumentUc_Num', header: lang['№_dok-ta'], width: 100},
				{name: 'DocumentUc_setDate', header: lang['data_podpisaniya'], type: 'date', width: 100},
				{name: 'DocumentUc_txtdidDate', header: lang['data_postavki'], type: 'date', width: 100},
				{name: 'Contragent_sName', header: lang['postavschik'], width: 260},
				{id: 'autoexpand', name: 'Contragent_tName', header: lang['poluchatel'], width: 120},
				{name: 'DocumentUc_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'},
				{name: 'DrugFinance_Name', width: 110, header: lang['istochnik_finansirovaniya'], type: 'string'},
				{name: 'WhsDocumentCostItemType_Name', width: 110, header: lang['statya_rashodov'], type: 'string'},
				{name: 'DrugFinance_id', type: 'int', hidden:true, isparam:true},
				{name: 'WhsDocumentCostItemType_id', type: 'int', hidden:true}
				/*
				+lang['dlya']+ +lang['aptek']+ +lang['obe']+ +lang['stroki']+ - +lang['no']+ +lang['dlya']+ +lang['lpu']+ +lang['odna']+
				{name: 'DocumentUc_Sum', width: 110, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'},
				{name: 'DocumentUc_SumR', width: 110, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'}
				*/
			],
			actions:
			[
				{name:'action_add', handler: function() {this.openRecordEditWindow('add',this.DokUcLpuGrid);}.createDelegate(this)},
				{name:'action_delete'} // Вроде никаких дополнительных действий не планируется 
			], 
			onLoadData: function(result)
			{
				var win = Ext.getCmp('DokUcLpuViewWindow');
			},
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('DokUcLpuViewWindow');
				//win.DokUcLpuGrid.ViewActions.action_delete.setDisabled(true);
			}
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: 
			[

				{
					border: false,
					region: 'center',
					layout: 'border',
					defaults: {split: true},
					items: [
						form.SearchPanel,
						{
							border: false,
							region: 'center',
							layout: 'fit',
							items: [form.DokUcLpuGrid]
						}
					]
				}
			]
		});
		sw.Promed.swDokUcLpuViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
