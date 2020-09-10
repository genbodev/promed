/**
* Справочник РЛС: Форма поиска производителя лек. средства
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      30.11.2011
*/

sw.Promed.swRlsFirmsSearchWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: lang['spravochnik_proizvoditeley_lekarstvennyih_sredstv'],
	modal: true,
	shim: false,
	plain: true,
	maximized: true,
	resizable: false,
	onSelect: Ext.emptyFn,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swRlsFirmsSearchWindow',
	closeAction: 'hide',
	id: 'swRlsFirmsSearchWindow',
	objectSrc: '/jscore/Forms/Rls/swRlsFirmsSearchWindow.js',
	buttons: [
		{
			text: lang['vyibrat'],
			hidden: true,
			iconCls: 'ok16',
			handler: function(){
				this.ownerCt.doSelect();
			}
		},
		'-',
		{
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			w.buttons[0].setVisible(false);
			w.CommonForm.getForm().reset();
		}
	},
	
	show: function()
	{
		sw.Promed.swRlsFirmsSearchWindow.superclass.show.apply(this, arguments);
		
		if(arguments[0]){
			if(arguments[0].onSelect){
				this.onSelect = arguments[0].onSelect;
				this.buttons[0].setVisible(true);
			}
		}
		
		//this.doSearch();
		this.doLayout();
	},
	
	doSelect: function()
	{
		var record = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		this.onSelect(record.data);
		this.hide();
	},
	
	doSearch: function()
	{
		var form = this.CommonForm.getForm();
		var grid = this.Grid;
		grid.ViewGridPanel.getStore().baseParams = form.getValues();
		grid.ViewGridPanel.getStore().baseParams.start = 0;
		grid.ViewGridPanel.getStore().baseParams.limit = 50;
		grid.ViewActions.action_refresh.execute();
	},
	
	deleteFirm: function()
	{
		var win = this;
		var record = this.Grid.ViewGridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(button) {
				if ( button == 'yes' ) {
					var lm = win.getLoadMask(lang['udalenie_proizvoditelya']);
					lm.show();
					Ext.Ajax.request({
						url: '/?c=Rls&m=deleteFirm',
						params: {
							FIRMS_ID: record.get('FIRMS_ID')
						},
						callback: function(o, s, r){
							lm.hide();
							if(s) win.doSearch();
						}.createDelegate(this)
					});
				}
			},
			icon: Ext.Msg.QUESTION,
			msg: lang['vyi_deystvitelno_jelaete_udalit_vyibrannogo_proizvoditelya'],
			title: lang['podtverjdenie_udaleniya']
		});
	},
	
	initComponent: function()
	{
		var cur_win = this;
		
		this.Filter = new Ext.form.FieldSet({
			title: lang['filtryi'],
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north',
			layout: 'column',
			autoHeight: true,
			floatable: false,
			defaults: {
				border: false,
				bodyStyle: 'background:#DFE8F6; padding-left: 5px;',
				labelAlign: 'top',
				labelWidth: 150
			},
			titleCollapse: true,
			collapsible: true,
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

					if (Ext.isIE) {
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}
					this.doSearch();
				}.createDelegate(this),
				stopEvent: true
			}],
			items: [
				{
					layout: 'form',
					columnWidth: .44,
					items: [
						{
							xtype: 'hidden',
							name: 'FormSign',
							value: 'firmssearchform'
						}, {
							xtype: 'textfield',
							anchor: '100%',
							name: 'Firm_Name',
							fieldLabel: lang['naimenovanie_organizatsii']
						}
					]
				}, {
					layout: 'form',
					bodyStyle: 'background:#DFE8F6; padding-left: 10px;',
					columnWidth: .44,
					items: [
						{
							xtype: 'textfield',
							anchor: '100%',
							name: 'Firm_Address',
							fieldLabel: lang['adres']
						}
					]
				}, {
					layout: 'form',
					defaults: {
						minWidth: 110
					},
					columnWidth: .12,
					items: [
						{
							xtype: 'button',
							handler: function(){
								this.doSearch();
							}.createDelegate(this),
							text: lang['ustanovit']
						}, {
							xtype: 'button',
							handler: function(){
								this.CommonForm.getForm().reset();
								this.doSearch();
							}.createDelegate(this),
							text: lang['otmenit']
						}
					]
				}
			]
		});
		
		this.Grid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 100,
			pageSize: 50,
			id: 'rfsw_searchgrid',
			border: false,
			editformclassname: 'swRlsFirmsEditWindow',
			region: 'center',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view' },
				{ name: 'action_delete', disabled: true, handler: function(){this.deleteFirm();}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print' }
			],
			root: 'data',
			autoLoadData: false,
			stripeRows: true,
			stringfields: [
				{ name: 'FIRMS_ID', type: 'int', hidden: true, key: true },
				{ name: 'ProducerType_id', type: 'int', hidden: true },
				{ name: 'FIRMS_NAME', header: lang['polnoe_nazvanie'], width: 500, renderer: function(v, p, rec){
					if(v == null)
						return '';
					var re = /<\/?[^>]+>/g;
					if(v.match(re))
						v = v.replace(re, ' ');
					return v;
				} },
				{ name: 'FIRMS_ADRMAIN', header: lang['adres'], id: 'autoexpand', renderer: function(v, p, rec){
					if(v == null)
						return '';
					var re = /<\/?[^>]+>/g;
					if(v.match(re))
						v = v.replace(re, ' ');
					return v;
				}}
			],
			dataUrl: '/?c=Rls&m=searchData',
			paging: true,
			totalProperty: 'totalCount'
		});
		
		this.Grid.ViewGridPanel.getStore().baseParams.FormSign = 'firmssearchform';
		
		this.Grid.ViewGridPanel.getSelectionModel().on('rowselect', function(grid, rIdx, rec){
			var actions = cur_win.Grid.ViewActions;
			if(rec.get('ProducerType_id') == 1){
				actions.action_edit.disable();
				actions.action_delete.disable();
			} else {
				actions.action_edit.enable();
				actions.action_delete.enable();
			}
		});
		
		this.CommonForm = new Ext.form.FormPanel({
			autoScroll: true,
			layout: 'border',
			items: [this.Filter, this.Grid]
		});
		
		Ext.apply(this,	{
			items: [this.CommonForm]
		});
		sw.Promed.swRlsFirmsSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});