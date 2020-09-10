/**
* swDokSpisViewWindow - просмотр документов списания медикаментов.
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
sw.Promed.swDokSpisViewWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swDokSpisViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokSpisViewWindow.js',
	title:lang['dokumentyi_spisaniya_medikamentov'],
	layout: 'border',
	id: 'FarmacyDokSpisViewWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	buttons: [{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	}, {
		text: BTN_FRMCLOSE,
		tabIndex: -1,
		tooltip: lang['zakryit'],
		iconCls: 'cancel16',
		handler: function() {
			this.ownerCt.hide();
		}
	}],
	returnFunc: function(owner) {},
	listeners: {
		hide: function() {
			this.returnFunc(this.owner, -1);
		}
	},
	openRecordEditWindow: function(action, gridCmp) {
		var grid = gridCmp.getGrid();
		var params = new Object();
		if (action == 'add' && this.Contragent_sid) {
			params.Contragent_sid = this.Contragent_sid;
		}
		params.callback = function() {
			gridCmp.ViewActions.action_refresh.execute();
		};
		getWnd(gridCmp.editformclassname).show(params);
	},
	doSearch: function() {
		var wnd = this;
		var params = this.FilterPanel.getForm().getValues();
		var loadMask = new Ext.LoadMask(Ext.get('FarmacyDokSpisViewWindow'), { msg: LOAD_WAIT });
		params.start = 0;
		params.limit = 100;

		loadMask.show();
		wnd.SearchGrid.removeAll();
		wnd.SearchGrid.loadData({
			globalFilters: params,
			callback: function() {
				loadMask.hide();
			}
		});
	},
	doReset: function() {
		this.FilterPanel.getForm().reset();
		if (this.Contragent_sid > 0) {
			this.FilterPanel.getForm().findField('Contragent_sid').setValue(this.Contragent_sid);
		}
	},
	show: function() {
		sw.Promed.swDokSpisViewWindow.superclass.show.apply(this, arguments);
		var wnd = this;

		wnd.Contragent_sid = null;
		if (arguments[0] && arguments[0].Contragent_sid) {
			wnd.Contragent_sid = arguments[0].Contragent_sid;
		}
		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		// Установка фильтров при открытии формы просмотра 
		wnd.SearchGrid.setActionHidden('action_add',this.viewOnly);
		wnd.SearchGrid.setActionHidden('action_edit',this.viewOnly);
		wnd.SearchGrid.setActionHidden('action_delete',this.viewOnly);
		wnd.SearchGrid.addActions({
			name: 'action_print_akt',
			text: lang['pechat_akta'],
			iconCls: 'print16',
			handler: function() {
				var grid = wnd.SearchGrid.getGrid();
				if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('DocumentUc_id') ) {
					return false;
				}
				var record = grid.getSelectionModel().getSelected();
				var doc_id = record.get('DocumentUc_id');
				var id_salt = Math.random();
				var win_id = 'print_act' + Math.floor(id_salt * 10000);
				var win = window.open('/?c=Farmacy&m=printDokSpisAkt&DocumentUc_id=' + doc_id, win_id);
			}
		});

		if (wnd.Contragent_sid > 0) {
			var c_combo = this.FilterPanel.getForm().findField('Contragent_sid');
			c_combo.getStore().load({
				callback: function() {
					c_combo.setValue(wnd.Contragent_sid);
					wnd.doSearch();
				}
			});
		} else {
			wnd.doSearch();
		}
	},
	initComponent: function() {
		var wnd = this;

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
				name: 'Contragent_sid',
				hiddenName: 'Contragent_sid',
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
							wnd.doSearch();
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
							wnd.doReset();
							wnd.doSearch();
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
			{name: 'Contragent_sName', header: lang['postavschik'], width: 120, id: 'autoexpand'},
			{name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], type: 'string', width: 120},
			{name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashodov'], type: 'string', width: 120},
			{name: 'DrugFinance_id', type: 'int', hidden:true},
			{name: 'WhsDocumentCostItemType_id', type: 'int', hidden:true}
		];
		if (isFarmacyInterface) {
			sf.push({name: 'DocumentUc_Sum', width: 110, header: lang['summa_opt_bez_nds'], type: 'money', align: 'right'});
			sf.push({name: 'DocumentUc_SumR', width: 110, header: lang['summa_rozn_s_nds'], type: 'money', align: 'right'});
		} else {
			sf.push({name: 'DocumentUc_SumR', width: 110, header: lang['summa'], type: 'money', align: 'right'});
		}
		
		// Документы списания медикаментов
		this.SearchGrid = new sw.Promed.ViewFrame({
			id: 'dsv_SearchGrid',
			region: 'center',
			height: 303,
			paging: true,
			object: 'DocumentUc',
			editformclassname: 'swDokSpisEditWindow',
			dataUrl: '/?c=Farmacy&m=load&method=DokSpis',
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields: sf,
			actions:[
				{name:'action_add', handler: function() {this.openRecordEditWindow('add',this.SearchGrid);}.createDelegate(this)},
				{name:'action_edit'},
				{name:'action_view'},
				{name:'action_delete'},
				{name:'action_refresh'},
				{name:'action_print'}
			]
		});
		
		Ext.apply(this, {
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: [
				this.FilterPanel,
				this.SearchGrid
			]
		});
		sw.Promed.swDokSpisViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
