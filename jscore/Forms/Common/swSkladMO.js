/**
* swSkladMO - окно просмотра списка параметров
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009-2011 Swan Ltd.
* @author       Alexander Permyakov (alexpm)
* @version      07.2013
* @comment      
*/

/*NO PARSE JSON*/

sw.Promed.swSkladMO = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swSkladMO',
	objectSrc: '/jscore/Forms/Common/swSkladMO.js',
	type:null,
	buttonAlign: 'left',
	closeAction: 'hide',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	title: lang['sklad_ostatkov_mo'],
	draggable: true,
	id: 'swSkladMO',
	width: 700,
	height: 500,
	modal: true,
	plain: true,
	resizable: false,
	maximized: true,
	//входные параметры
	action: null,
	onSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	
	doReset: function() {
		var form = this.filterPanel.getForm(),
			grid = this.viewFrame.getGrid();
		form.reset();
		form.findField('SkladOstat_Sklad').focus(true, 250);
		grid.getStore().baseParams = {};
		this.viewFrame.removeAll(true);
		this.viewFrame.ViewGridPanel.getStore().removeAll();
		this.doSearch();
	},
	doSearch: function() 
	{
		var form = this.filterPanel.getForm();
			//grid = this.viewFrame.getGrid(),
		var	params = {};
		var param_name = form.findField('SkladOstat_Sklad').getValue();
		var param_gdmd = form.findField('SkladOstat_Gdmd').getValue();
		
		if (param_name) {
            params.SkladOstat_Sklad = param_name;
        }else{
			    params.SkladOstat_Sklad ="";
        
		}
        if (param_gdmd) {
			params.SkladOstat_Gdmd = param_gdmd;
		}else{
			params.SkladOstat_Gdmd = "";
		}
		this.viewFrame.removeAll(true);
		this.viewFrame.loadData({globalFilters:params});
	},

	showExportWindow:function(name){
		var win = this;
		var params = {}
		params.name = name
		getWnd('swExportMoDbfWindow').show(params);
	},
	initComponent: function() {
		var win = this;
		 var xg = Ext.grid;
		this.filterPanel = new Ext.form.FormPanel({
			autoHeight: true,
			buttonAlign: 'left',
			frame: true,
			id: 'SkladMOSearchForm',
			labelAlign: 'right',
			labelWidth: 160,
			region: 'north',
			items: [{
				fieldLabel: lang['nazvanie_sklada'],
				name: 'SkladOstat_Sklad',
				id: 'WIN_SkladOstat_Sklad',
                maskRe: new RegExp("^[а-яА-ЯёЁ 0-9]*$"),
				width: 200,
				enableKeyEvents: true,
				xtype: 'textfield'
			}, {
				fieldLabel: lang['naimenovanie_medikamenta'],
				name: 'SkladOstat_Gdmd',
				maskRe: new RegExp("^[а-яА-ЯёЁ]*$"),
				width: 200,
				enableKeyEvents: true,
				xtype: 'textfield'
			}],
            buttons: [{
                handler: function() {
                    win.doSearch();
                },
                iconCls: 'search16',
                text: BTN_FRMSEARCH
            }, {
                handler: function() {
                    win.doReset();
					win.doSearch();
                },
                iconCls: 'resetsearch16',
                text: BTN_FRMRESET
            }],
            keys: [{
				fn: function(e) {
					this.doSearch();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		this.viewFrame = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 250,
			autoLoadData: false,
			dataUrl: '/?c=SkladOstat&m=loadOstatGrid',
			id: 'SkladOstat',
			actions:
			[
				{name:'action_add', hidden:true },
				{name:'action_edit', hidden:true},
				{name:'action_view', hidden:true},
				{name:'action_delete', hidden:true},
			],
			totalProperty: 'totalCount',
			region: 'center',
			stringfields: [
				{ header: 'ID', type: 'int', name: 'SkladOstat_id', key: true },
                { header: langs('Название склада'),  type: 'string', name: 'SkladOstat_Sklad', id: 'autoexpand', isparams: true,width: 170 },
                { header: langs('Идентификатор склада'),  type: 'string', name: 'SkladOstat_SkladRn', width: 170, isparams: true },
				{ header: langs('Наименование медикамента'),  type: 'string', name: 'SkladOstat_Gdmd', width: 150 },
				{ header: langs('Идентификатор медикамента'),  type: 'string', name: 'SkladOstat_GdmdRn', width: 140 },
				{ header: langs('Наименование медикамента по РЛС'), name: 'SkladOstat_Rls', width: 100, type:'string' },
                { header: langs('Основная единица измерения'),  type: 'int', name: 'SkladOstat_Mea',  type:'string' },
                { header: langs('Количество'),  type: 'string', name: 'SkladOstat_Kol', type:'float', hidden: false }
			],
			toolbar: true,
			onRowSelect: function(sm,rowIdx,record) {
              
			},
			onDblClick: function(grid, rowIdx, colIdx, event) {
			
			},
			onEnter: function()
			{
				
			}
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'WIN_ParameterValue_Alias',
				text: BTN_FRMCLOSE
			}],
			items: [ 
				this.filterPanel,
				this.viewFrame
			]
		});
		sw.Promed.swSkladMO.superclass.initComponent.apply(this, arguments);
        this.viewFrame.ViewToolbar.on('render', function(vt){
            this.ViewActions['actions'] = new Ext.Action({
                name:'actions',
				tooltip: lang['deystviya'],
				iconCls : 'x-btn-text',
				icon: 'img/icons/actions16.png',
				handler: function() {},
				key: 'actions',
				text:lang['deystviya'],
				menu: [
				{
					name:'improtSOst',
					disabled: false,
					text:lang['import_ostatkov_mo'],
					tooltip: lang['import_ostatkov_mo'],
					/*iconCls : 'update-ward16',*/
					handler: function() {
						win.showExportWindow("SkladOst")
					}.createDelegate(this)
				},
				{
					name:'ImportLpu',
					disabled: false, 
					text:lang['import_dokumentov_ucheta_medikamentov'], 
					tooltip: lang['import_dokumentov_ucheta_medikamentov'], 
					/*iconCls : 'edit16',*/ 
					handler: function() {
						win.showExportWindow("LpuSectionOTD");
					}.createDelegate(this)
				},
			]
            });
            vt.insertButton(1,this.ViewActions['actions']);
            return true;
        }, this.viewFrame);
	},

	show: function() {
		sw.Promed.swSkladMO.superclass.show.apply(this, arguments);
		if (!arguments[0])
		{
			arguments = [{}];
		}else{
			if(arguments[0].action){
				this.action = arguments[0].action;
			}
		}
		
		if(this.action == 'view'){
			this.viewFrame.ViewToolbar.items.items[0].hide();
		}else{
			this.viewFrame.ViewToolbar.items.items[0].show();
		}
		this.doReset();
	}
});