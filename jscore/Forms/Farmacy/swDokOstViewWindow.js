/**
* swDokOstViewWindow - просмотр документов остатков.
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
/*NO PARSE JSON*/
sw.Promed.swDokOstViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['dokumentyi_vvoda_ostatkov'],
	layout: 'border',
	id: 'FarmacyDokOstViewWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	ARMType: null,
	codeRefresh: true,
	objectName: 'swDokOstViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokOstViewWindow.js',
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
    doSearch: function() {
        var wnd = this;
        var params = this.FilterPanel.getForm().getValues();
        var loadMask = new Ext.LoadMask(Ext.get('FarmacyDokOstViewWindow'), { msg: LOAD_WAIT });
        params.start = 0;
        params.limit = 100;

        loadMask.show();
        wnd.DokOstGrid.removeAll();
        wnd.DokOstGrid.loadData({
            globalFilters: params,
            callback: function() {
                loadMask.hide();
            }
        });
    },
    doReset: function() {
        this.FilterPanel.getForm().reset();
        if (this.Contragent_tid > 0) {
            this.FilterPanel.getForm().findField('Contragent_sid').setValue(this.Contragent_tid);
        }
    },
	show: function() {
		sw.Promed.swDokOstViewWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('FarmacyDokOstViewWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		var form = this;
        form.Contragent_sid = null;
        if (arguments[0] && arguments[0].Contragent_sid) {
            form.Contragent_sid = arguments[0].Contragent_sid;
        }
		if (arguments[0]) {
			if (arguments[0].ARMType) {
				this.ARMType = arguments[0].ARMType;
			}
			if (arguments[0].Contragent_tid) {
				this.Contragent_tid = arguments[0].Contragent_tid;
			}
		}
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		// Установка фильтров при открытии формы просмотра
		form.DokOstGrid.setReadOnly(this.viewOnly);
		form.DokOstGrid.setActionHidden('action_add',this.viewOnly);
		form.DokOstGrid.setActionHidden('action_edit',this.viewOnly);
		form.DokOstGrid.setActionHidden('action_delete',this.viewOnly);
		// Установка фильтров при открытии формы просмотра 
		
		// Читаем грид при открытии формы (если на форму добавлять фильтры, то можно будет не читать данные при открытии, а просто очищать грид)
		var gFilters = {start: 0, limit: 100};
		if (form.Contragent_tid) {
			gFilters.Contragent_tid = form.Contragent_tid;
		}
		form.DokOstGrid.loadData({globalFilters: gFilters});
		loadMask.hide();
	},
	initComponent: function()
	{
		var form = this;
        this.FilterClassPanel = new sw.Promed.Panel({
            layout: 'form',
            autoScroll: true,
            bodyBorder: false,
            labelAlign: 'right',
            labelWidth: 170,
            border: false,
            frame: true,
            items: [{
                xtype: 'swcontragentcombo',
                fieldLabel: lang['kontragent'],
                name: 'Contragent_tid',
                hiddenName: 'Contragent_tid',
                width: 500
            }, {
                fieldLabel: lang['period'],
                xtype: 'daterangefield',
                name: 'DocumentUc_setDate_range',
                plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                width: 250
            }, {
                fieldLabel: lang['istochnik_finans'],
                xtype: 'swdrugfinancecombo',
                name: 'DrugFinance_id',
                width: 250
            }, {
                fieldLabel: lang['statya_rashodov'],
                xtype: 'swwhsdocumentcostitemtypecombo',
                name: 'WhsDocumentCostItemType_id',
                width: 250
            }]
        });

        this.FilterButtonsPanel = new sw.Promed.Panel({
            autoScroll: true,
            bodyBorder: false,
            border: false,
            frame: true,
            items: [{
                layout: 'column',
                items: [{
                    layout: 'form',
                    items: [{
                        style: "padding-left: 10px",
                        xtype: 'button',
                        text: lang['nayti'],
                        iconCls: 'search16',
                        minWidth: 100,
                        handler: function() {
                            form.doSearch();
                        }.createDelegate(this)
                    }]
                }, {
                    layout: 'form',
                    items: [{
                        style: "padding-left: 10px",
                        xtype: 'button',
                        text: lang['sbros'],
                        iconCls: 'reset16',
                        minWidth: 100,
                        handler: function() {
                            form.doReset();
                            form.doSearch();
                        }.createDelegate(this)
                    }]
                }]
            }]
        });

        this.FilterPanel = getBaseFiltersFrame({
            region: 'north',
            defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
            ownerWindow: this,
            toolBar: this.WindowToolbar,
            items: [
                this.FilterClassPanel,
                this.FilterButtonsPanel
            ]
        });
		// в зависимости от выбранного интерфейса
		// постоянные поля 
		var sf = [
			{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
			{name: 'DocumentUc_Num', header: lang['№_dok-ta'], width: 100},
			{name: 'DocumentUc_setDate', type:'date', header: lang['data_podpisaniya'], width: 120},
			{name: 'DocumentUc_txtdidDate', type:'date', header: lang['data_postavki'], width: 120},
			{name: 'Contragent_tName', header: lang['poluchatel'], width: 120, id: 'autoexpand'},
			{name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], type: 'string', width: 120},
			{name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashodov'], type: 'string', width: 120}
		];
		if (isFarmacyInterface)
		{
			sf.push({name: 'DocumentUc_Sum', width: 110, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUc_SumR', width: 110, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'});
		}
		else 
		{
			sf.push({name: 'DocumentUc_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'});
		}
		// Документы ввода остатков
		this.DokOstGrid = new sw.Promed.ViewFrame(
		{
			id: 'DokOstGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'DocumentUc',
			editformclassname: 'swDokOstEditWindow',
			dataUrl: '/?c=Farmacy&m=load&method=DokOst',
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields: sf,
			actions:
			[
				{name:'action_add', handler: function() {this.openRecordEditWindow('add',this.DokOstGrid);}.createDelegate(this)},
				{name:'action_delete'} // Вроде никаких дополнительных действий не планируется 
			], 
			onLoadData: function(result)
			{
				var win = Ext.getCmp('FarmacyDokOstViewWindow');
			},
			onRowSelect: function(sm,index,record)
			{
				var win = Ext.getCmp('FarmacyDokOstViewWindow');
				//win.DokOstGrid.ViewActions.action_delete.setDisabled(true);
			}
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			/*items:
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
							items: [form.DokOstGrid]
						}
					]
				}
			]
			*/
			items:
			[
				form.FilterPanel,
				form.DokOstGrid
			]

		});
		sw.Promed.swDokOstViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
