/**
* swDokNakViewWindow - просмотр документов остатков.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Андрей Марков
* @version      01.2010
* @comment      
*
*/

sw.Promed.swDokNakViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['prihodnyie_nakladnyie'],
	layout: 'border',
	id: 'FarmacyDokNakViewWindow',
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
		sw.Promed.swDokNakViewWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('FarmacyDokNakViewWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		var form = this;
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		// Установка фильтров при открытии формы просмотра 
		form.DokNakGrid.setReadOnly(this.viewOnly);
		form.DokNakGrid.setActionHidden('action_add',this.viewOnly);
		form.DokNakGrid.setActionHidden('action_edit',this.viewOnly);
		form.DokNakGrid.setActionHidden('action_delete',this.viewOnly);
		// Читаем грид при открытии формы (если на форму добавлять фильтры, то можно будет не читать данные при открытии, а просто очищать грид)
		form.DokNakGrid.loadData({globalFilters: {start: 0, limit: 100}});
		loadMask.hide();
	},
	initComponent: function()
	{
		var form = this;
		// Документы ввода остатков
		this.DokNakGrid = new sw.Promed.ViewFrame(
		{
			id: 'DokNakGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'DocumentUc',
			editformclassname: 'swDokNakEditWindow',
			dataUrl: '/?c=Farmacy&m=load&method=DokNak',
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugDocumentStatus_id', hidden: true},
				{name: 'DrugDocumentStatus_Name', header: lang['status'], width: 70},
				{name: 'DocumentUc_Num', header: lang['№_dok-ta'], width: 100},
				{name: 'DocumentUc_txtdidDate', type:'date', header: lang['data_nakladnoy'], width: 120},
				{id: 'autoexpand', name: 'Contragent_sName', header: lang['postavschik']},
				{name: 'Contragent_tName', header: lang['poluchatel'], width: 300},
				{name: 'DocumentUc_Sum', width: 110, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'},
				{name: 'DocumentUc_SumR', width: 110, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'}
				
			],
			actions:
			[
				{name:'action_add', disabled: true},
				{name:'action_delete', disabled: true} 
			], 
			onLoadData: function(result)
			{
				var win = Ext.getCmp('FarmacyDokNakViewWindow');
			},
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('FarmacyDokNakViewWindow');
				//win.DokNakGrid.ViewActions.action_delete.setDisabled(true);
			}
		});
		
		this.DokNakGrid.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('DrugDocumentStatus_id')==1)
					cls = cls+'x-grid-rowselect ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: 
			[
				//form.FilterPanel,
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
							items: [form.DokNakGrid]
						}
					]
				}
			]
			/*
			items:
			[
				form.FilterPanel,
				form.DokNakGrid
			]
			*/
		});
		sw.Promed.swDokNakViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
