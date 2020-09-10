/**
* swDocumentUcViewWindow - просмотр учета медикаментов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Андрей Марков
* @version      05.2010
* @comment      Документ учета медикаментов в ЛПУ
*
*/

sw.Promed.swDocumentUcViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['uchet_medikamentov'],
	layout: 'border',
	id: 'DocumentUcViewWindow',
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
		sw.Promed.swDocumentUcViewWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('DocumentUcViewWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		var form = this;
		
		// Установка фильтров при открытии формы просмотра 
		
		// Читаем грид при открытии формы (если на форму добавлять фильтры, то можно будет не читать данные при открытии, а просто очищать грид)
		form.DocUcGrid.loadData({globalFilters: {start: 0, limit: 100}});
		loadMask.hide();
	},
	initComponent: function()
	{
		var form = this;
		// Документы Учета Медикаментов
		this.DocUcGrid = new sw.Promed.ViewFrame(
		{
			id: 'DocUcGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'DocumentUc',
			editformclassname: 'swDocumentUcEditWindow',
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
				{name: 'DocumentUc_setDate', header: lang['data_podpisaniya'], width: 120},
				{name: 'DocumentUc_didDate', header: lang['data_postavki'], width: 120},
				{id: 'autoexpand', name: 'Contragent_sName', header: lang['postavschik'], width: 120},
				{name: 'Contragent_tName', header: lang['potrebitel'], width: 280},
				{name: 'DocumentUc_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'}
			],
			actions:
			[
				{name:'action_delete'} // Вроде никаких дополнительных действий не планируется 
			], 
			onLoadData: function(result)
			{
				var win = Ext.getCmp('DocumentUcViewWindow');
			},
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('DocumentUcViewWindow');
				//win.DocUcGrid.ViewActions.action_delete.setDisabled(true);
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
							items: [form.DocUcGrid]
						}
					]
				}
			]
			/*
			items:
			[
				form.FilterPanel,
				form.DocUcGrid
			]
			*/
		});
		sw.Promed.swDocumentUcViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
