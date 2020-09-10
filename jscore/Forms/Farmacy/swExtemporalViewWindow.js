/**
* swExtemporalViewWindow - Экстемпоральные рецептуры окно просмотра списка
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Kurakin A.
* @version      07.2016
* @comment      
*/
sw.Promed.swExtemporalViewWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Экстемпоральные рецептуры',
	layout: 'border',
	id: 'ExtemporalViewWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	doSearch: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		var params = new Object();

		wnd.SearchGrid.removeAll();
		wnd.resetSpecGrid();
		params = form.getValues();
		// при пустых фильтрах список пуст
		var flag = false;
		for(var p in params){
			if(params[p])
				flag = true;
		}
		if(!flag)
			return false;

		params.start = 0;
		params.limit = 100;

		wnd.SearchGrid.loadData({params: params, globalFilters: params, 
			callback:function(){
				if( this.SearchGrid.getGrid().getStore().data.length == 0 || 
					(this.SearchGrid.getGrid().getStore().data.length == 1 && !this.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_id')) ) {
					this.resetSpecGrid();
				}
			}.createDelegate(this)
		});
	},
	resetSpecGrid: function() {
		var wnd = this;
		wnd.SpecGrid.removeAll();
		wnd.SpecGrid.ViewToolbar.items.items[0].disable();
		wnd.SpecGrid.ViewToolbar.items.items[6].disable();
	},
	loadSpecGrid: function() {
		var wnd = this;
		wnd.SpecGrid.removeAll();
		if(wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected() && wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_id')){
			var params = {};
			params.Extemporal_id = wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_id');
			params.Org_id = getGlobalOptions().org_id;
			wnd.SpecGrid.getGrid().getStore().load({params:params});
		}
	},
	doReset: function() {
		var wnd = this;
		var form = wnd.FilterPanel.getForm();
		form.reset();
		wnd.SearchGrid.removeAll();
		wnd.resetSpecGrid();
	},	
	show: function() {
        var wnd = this;
		sw.Promed.swExtemporalViewWindow.superclass.show.apply(this, arguments);
		this.readOnly = false;

		if (arguments[0] && arguments[0].readOnly) {
			this.readOnly = arguments[0].readOnly;
		}
		this.SearchGrid.setReadOnly(this.readOnly);
		this.SpecGrid.setReadOnly(this.readOnly);
		var copyAction = {
            name: 'copy_extemporal',
            text: 'Копировать',
            handler: function() {
                var record = this.SearchGrid.getGrid().getSelectionModel().getSelected();
                var owner = this.SearchGrid;
                if(record) {
                	wnd.getLoadMask('Копирование рецептуры').show();
                	Ext.Ajax.request({
						url: '/?c=Extemporal&m=copyExtemporal',
						params: { Extemporal_id: record.get('Extemporal_id')},
						callback: function(options, success, response) {
							wnd.getLoadMask().hide();
							if ( success ) {
								var result = Ext.util.JSON.decode(response.responseText);
								if(result[1]){
									var params = result[0][0];
									params.action = 'edit';
									params.copy = true;
									params.callback = function(){wnd.doSearch()};
									getWnd('swExtemporalEditWindow').show(params);
								}
							}
						}
					});
                } else {
                    sw.swMsg.alert('Ошибка', 'Не выбрана рецептура!');
                }
            }.createDelegate(this)
        };

        this.SearchGrid.addActions(copyAction);	
        this.SearchGrid.ViewToolbar.items.items[9].disable();	

		this.doReset();
		//this.doSearch();
	},
	deleteExtemporal: function() {
		var wnd = this;
		var record = this.SearchGrid.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		Ext.Msg.show({
			title: lang['vnimanie'],
			scope: this,
			msg: 'Вы действительно хотите удалить рецептуру?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					wnd.getLoadMask('Удаление рецептуры').show();
					Ext.Ajax.request({
						url: '/?c=Extemporal&m=deleteExtemporal',
						params: { Extemporal_id: record.get('Extemporal_id')},
						callback: function(options, success, response) {
							wnd.getLoadMask().hide();
							if ( success ) {
								var result = Ext.util.JSON.decode(response.responseText);
								if(result.Error_Code && result.Error_Code == '999'){
									Ext.Msg.alert(lang['soobschenie'], 'Удаление рецептуры не возможно, т.к. по этой рецептуре заданы нормы выхода и тарифы на изготовление');
 									return false;
								} else {
									wnd.SearchGrid.getGrid().getStore().removeAll();
									wnd.doSearch();
								}
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	deleteExtemporalCompStandart: function() {
		var wnd = this;
		var record = this.SpecGrid.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		Ext.Msg.show({
			title: lang['vnimanie'],
			scope: this,
			msg: 'Вы действительно хотите удалить запись?',
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					wnd.getLoadMask('Удаление тарифа и нормы выхода').show();
					Ext.Ajax.request({
						url: '/?c=Extemporal&m=deleteExtemporalCompStandart',
						params: { ExtemporalCompStandart_id: record.get('ExtemporalCompStandart_id')},
						callback: function(options, success, response) {
							wnd.getLoadMask().hide();
							if ( success ) {
								wnd.loadSpecGrid();
							}
						}
					});
				}
			},
			icon: Ext.MessageBox.QUESTION
		});
	},
	addExtemporalCompStandart: function() {
		var wnd = this;
		var record = this.SearchGrid.ViewGridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		var prams = {};
		prams.Extemporal_id = wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_id');
		prams.Org_id = getGlobalOptions().org_id;
		Ext.Ajax.request({
			url: '/?c=Extemporal&m=checkExtemporalCompStandart',
			params: prams,
			callback: function(options, success, response) {
				if ( success ) {
					var result = Ext.util.JSON.decode(response.responseText);
					if(result[0].cnt>0){
						Ext.Msg.alert(lang['soobschenie'], 'Добавление данных не возможно, т.к. в организации '+getGlobalOptions().org_nick+' для выбранной рецептуры уже указаны нормы выхода и тариф на изготовление ЛС');
 						return false;
					} else {
						var params = new Object();
						params.Extemporal_id = wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_id');
						params.Extemporal_Name = wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_Name');
						params.action = 'add';
						params.callback = function(){
							wnd.loadSpecGrid();
						};
						getWnd('swExtemporalCompStandartEditWindow').show(params);
						return true;
					}
				}
			}
		});
	},
	initComponent: function() {
		var wnd = this;

		this.FilterCommonPanel = new sw.Promed.Panel({
			layout: 'form',
			autoScroll: true,
			bodyBorder: false,
			labelAlign: 'right',
			labelWidth: 100,
			border: false,
			frame: true,
			items: [{
				layout: 'column',
				border: false,
				defaults: { border: false },
				autoHeight: true,
				labelWidth: 100,
				items:[{
					layout: 'form',
					defaults: {
						width: 185
					},
					items:[{
						xtype: 'textfield',
						fieldLabel: 'Код',
						name: 'Extemporal_Code',
						width:185
					}, {
						xtype: 'textfield',
						fieldLabel: 'Наименование',
						name: 'Extemporal_Name',
						width:185
					}]
				}, {
					layout: 'form',
					defaults: {
						width: 185
					},
					items:[{
						xtype: 'swcommonsprcombo',
						fieldLabel: 'Вид прописи',
						comboSubject: 'ExtemporalType'
					},{
						xtype: 'textfield',
						fieldLabel: 'Компонент',
						name: 'ExtemporalComp_Name',
						width:185
					}]
				}, {
					layout: 'form',
					defaults: {
						width: 185
					},
					items:[{
						xtype: 'textfield',
						fieldLabel: 'Организация',
						name: 'Org_Name',
						width:185
					}, {
						layout: 'column',
						width: 300,
						style:'padding-left:95px',
						items: [{
							layout:'form',
							items: [{
								style: "padding-left: 10px",
								xtype: 'button',
								text: 'Поиск',
								iconCls: 'search16',
								minWidth: 100,
								handler: function() {
									wnd.doSearch();
								}
							}]
						}, {
							layout:'form',
							items: [{
								style: "padding-left: 10px",
								xtype: 'button',
								text: 'Сброс',
								iconCls: 'reset16',
								minWidth: 100,
								handler: function() {
									wnd.doReset();
								}
							}]
						}]
					}]
				}]
			}]
		});

		this.FilterPanel = getBaseFiltersFrame({
			region: 'north',
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			ownerWindow: wnd,
			toolBar: this.WindowToolbar,
			items: [
				this.FilterCommonPanel
			]
		});

		this.SearchGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add'},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', hidden: !isSuperAdmin(), handler: function(){this.deleteExtemporal();}.createDelegate(this)},
				{name: 'action_print'}
			],
			region: 'north',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			autoScroll: true,
			dataUrl: '/?c=Extemporal&m=loadExtemporalList',
			height: 250,
			object: 'Extemporal',
			editformclassname: 'swExtemporalEditWindow',
			id: 'ExtemporalGrid',
			paging: true,
			root: 'data',
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'Extemporal_id', type: 'int', header: 'ID', key: true },
				{ name: 'Extemporal_Code', type: 'int', header: 'Код', width: 80 },
				{ name: 'Extemporal_Name', type: 'string', header: 'Наименование', width: 160 },
				{ name: 'Extemporal_Composition', type: 'string', header: 'Состав', id: 'autoexpand' },
				{ name: 'ExtemporalType_id', type: 'int', hidden: true },
				{ name: 'ExtemporalType_Name', type: 'string', header: 'Вид прописи', width: 120 },
				{ name: 'Extemporal_begDT', type: 'date', header: 'Дата начала', width: 120 },
				{ name: 'Extemporal_endDT', type: 'date', header: 'Дата окончания', width: 120 },
				{ name: 'RlsClsdrugforms_id', type: 'int', hidden: true },
				{ name: 'Extemporal_IsClean', type: 'int', hidden: true },
				{ name: 'Extemporal_daterange', type: 'string', hidden: true }
			],
			title: 'Экстемпоральные рецептуры',
			toolbar: true
		});

		this.SpecGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', handler:function(){this.addExtemporalCompStandart();}.createDelegate(this)},
				{name: 'action_edit'},
				{name: 'action_view'},
				{name: 'action_delete', handler:function(){this.deleteExtemporalCompStandart();}.createDelegate(this)},
				{name: 'action_print'}
			],
			region: 'center',
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 125,
			autoLoadData: false,
			border: true,
			dataUrl: '/?c=Extemporal&m=loadExtemporalCompStandartList',
			height: 180,
			object: 'ExtemporalCompStandart',
			editformclassname: 'swExtemporalCompStandartEditWindow',
			id: 'ExtemporalCompStandartGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
				{ name: 'ExtemporalCompStandart_id', type: 'int', header: 'ID', key: true },
				{ name: 'Extemporal_id', type: 'int', hidden: true },
				{ name: 'Extemporal_Name', type: 'string', hidden: true },
				{ name: 'Org_id', type: 'int', hidden: true },
				{ name: 'Org_Nick', type: 'string', header: 'Организация', width: 120 },
				{ name: 'ExtemporalCompStandart_Count', type: 'string', header: 'Норма выхода', hidden: true, width: 160 },
				{ name: 'Norma', type: 'string', header: 'Норма выхода', width: 160 },
				{ name: 'ExtemporalCompStandart_Tariff', type: 'string', header: 'Тариф на изготовление', width: 160 },
				{ name: 'GoodsUnit_id', type: 'int', hidden: true }
			],
			title: 'Норматив выхода и тариф на изготовление',
			toolbar: true
		});

		this.SearchGrid.getGrid().getSelectionModel().on('rowselect', function(sm, rIdx, rec) {
        	this.loadSpecGrid();
        	if(wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected() && wnd.SearchGrid.ViewGridPanel.getSelectionModel().getSelected().get('Extemporal_id')){
				this.SearchGrid.ViewToolbar.items.items[9].enable();
			} else {
				this.SearchGrid.ViewToolbar.items.items[9].disable();
			}
		}, this);

		this.CenterPanel = new Ext.form.FormPanel({
			region: 'center',
			border: false,
			layout: 'border',
			items: [this.SearchGrid,this.SpecGrid]
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				wnd.FilterPanel,this.CenterPanel
			]
		});
		sw.Promed.swExtemporalViewWindow.superclass.initComponent.apply(this, arguments);
	}	
});