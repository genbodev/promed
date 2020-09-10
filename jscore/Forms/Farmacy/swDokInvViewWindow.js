/**
* swDokInvViewWindow - просмотр инвентаризационных ведомостей.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Salakhov Rustam
* @version      08.02.2011
* @comment      
*
*/
/*NO PARSE JSON*/
sw.Promed.swDokInvViewWindow = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['inventarizatsionnyie_vedomosti'],
	layout: 'border',
	id: 'FarmacyDokInvViewWindow',
	maximized: true,
	maximizable: false,
	shim: false,
	buttonAlign : "right",
	codeRefresh: true,
	objectName: 'swDokInvViewWindow',
	objectSrc: '/jscore/Forms/Farmacy/swDokInvViewWindow.js',
	buttons: [{
		text: BTN_FRMHELP,
		iconCls: 'help16',
		handler: function(button, event) {
			ShowHelp(this.ownerCt.title);
		}
	}, {
		text      : BTN_FRMCLOSE,
		tabIndex  : -1,
		tooltip   : lang['zakryit'],
		iconCls   : 'cancel16',
		handler   : function() {
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
		params.action = action;
		if (action == 'add' && this.Contragent_sid) {
			params.Contragent_id = this.Contragent_sid;
		}
		getWnd(gridCmp.editformclassname).show(params);
	},
	show: function() {
		sw.Promed.swDokInvViewWindow.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('FarmacyDokInvViewWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		var form = this;
		if (arguments[0] && arguments[0].Contragent_sid) {
			form.Contragent_sid = arguments[0].Contragent_sid;
		}

		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;
		// Установка фильтров при открытии формы просмотра 
		form.DokInvGrid.setActionHidden('action_add',this.viewOnly);
		form.DokInvGrid.setActionHidden('action_edit',this.viewOnly);
		form.DokInvGrid.setActionHidden('action_delete',this.viewOnly);

		// Установка фильтров при открытии формы просмотра 
		
		// Читаем грид при открытии формы (если на форму добавлять фильтры, то можно будет не читать данные при открытии, а просто очищать грид)
		var gFilters = {start: 0, limit: 100};
		if (form.Contragent_sid) {
			gFilters.Contragent_sid = form.Contragent_sid;
		}
		form.DokInvGrid.loadData({globalFilters: gFilters});
		loadMask.hide();
	},
	initComponent: function() {
		var form = this;
		
		// в зависимости от выбранного интерфейса
		// постоянные поля 
		var sf = [
			{name: 'DocumentUc_id', type: 'int', header: 'ID', key: true},
			{name: 'DocumentUc_InvNum', header: lang['№_dok-ta'], width: 100},
			{name: 'DocumentUc_InvDate', type:'date', header: lang['data'], width: 120},
			{name: 'Contragent_sName', header: lang['poluchatel'], width: 120, id: 'autoexpand'},
			{name: 'DrugFinance_Name', header: lang['istochnik_finansirovaniya'], type: 'string', width: 120},
			{name: 'WhsDocumentCostItemType_Name', header: lang['statya_rashodov'], type: 'string', width: 120}
		];
		// список инвентаризационных ведомостей
		this.DokInvGrid = new sw.Promed.ViewFrame({
			id: 'DokInvGridPanel',
			region: 'center',
			height: 303,
			paging: true,
			object: 'DocumentUc',
			editformclassname: 'swDokInvEditWindow',
			dataUrl: '/?c=Farmacy&m=load&method=DokInv',
			toolbar: true,
			root: 'data',
			totalProperty: 'totalCount',
			autoLoadData: false,
			stringfields: sf,
			actions: [
				{name:'action_add', handler: function() {this.openRecordEditWindow('add',this.DokInvGrid);}.createDelegate(this)},
				{name:'action_delete'} // Вроде никаких дополнительных действий не планируется 
			], 
			onLoadData: function(result) {
				var win = Ext.getCmp('FarmacyDokInvViewWindow');
			},
			onRowSelect: function(sm,index,record) {
				var win = Ext.getCmp('FarmacyDokInvViewWindow');				
			}
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			layout:'border',
			items: [{
				border: false,
				region: 'center',
				layout: 'border',
				defaults: {split: true},
				items: [{
					border: false,
					region: 'center',
					layout: 'fit',
					items: [form.DokInvGrid]
				}]
			}]
		});
		sw.Promed.swDokInvViewWindow.superclass.initComponent.apply(this, arguments);
	}

});
