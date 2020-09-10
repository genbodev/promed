/**
 * swTableDirectViewWindow - окно просмотра справочников атрибутов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			17.07.2014
 */

/*NO PARSE JSON*/

sw.Promed.swTableDirectViewWindow = Ext.extend(sw.Promed.BaseForm, {
	maximized: true,
	modal: false,
	resizable: false,
	//plain: false,
	title: lang['bazovyie_spravochniki_atributov'],
	id: 'swTableDirectViewWindow',

	show: function() {
		sw.Promed.swTableDirectViewWindow.superclass.show.apply(this, arguments);

		this.TableDirectInfoGrid.loadData();
	},

	loadTableDirectGrid: function(sm, rIdx, rec) {
		var grid = this.TableDirectGrid.getGrid();
		var base_form = this.contentSearchPanel.getForm();

		base_form.findField('TableDirect_Name').setValue('');
		if ( Ext.isEmpty(rec.get('TableDirectInfo_id')) ) {
			grid.getStore().removeAll();
			this.TableDirectGrid.getAction('action_add').setDisabled(true);
			this.TableDirectGrid.getAction('action_refresh').setDisabled(true);
			return false;
		}

		this.TableDirectGridPanel.setTitle( rec.get('TableDirectInfo_Name') || lang['net_opisaniya'] );

		this.TableDirectGrid.loadData({ globalFilters: {
			TableDirectInfo_id: rec.get('TableDirectInfo_id'),
			start: 0,
			limit: 100,
			TableDirect_Name: ''
		} });

		return true;
	},

	doSearch: function(cb) {
		var base_form = this.searchPanel.getForm();
		var globalFilters = base_form.getValues();
		globalFilters.start = 0;
		globalFilters.limit = 100;
		this.TableDirectInfoGrid.loadData({ globalFilters: base_form.getValues(), callback: cb || Ext.emptyFn });
	},

	doSearchContent: function(cb) {
		var base_form = this.contentSearchPanel.getForm();
		var grid_info = this.TableDirectInfoGrid.getGrid();

		var globalFilters = base_form.getValues();
		globalFilters.TableDirectInfo_id = grid_info.getSelectionModel().getSelected().get('TableDirectInfo_id');
		globalFilters.start = 0;
		globalFilters.limit = 100;

		this.TableDirectGrid.loadData({ globalFilters: globalFilters, callback: cb || Ext.emptyFn });
	},

	openTableDirectInfoEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {return false;}

		var params = new Object();
		params.formParams = new Object();
		params.action = action;

		if (action != 'add') {
			var record = this.TableDirectInfoGrid.getGrid().getSelectionModel().getSelected();
			params.formParams.TableDirectInfo_id = record.get('TableDirectInfo_id');
		}

		params.callback = function() {
			this.TableDirectInfoGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swTableDirectInfoEditWindow').show(params);
	},

	openTableDirectEditWindow: function(action) {
		if (!action.inlist(['add','edit','view'])) {return false;}

		var params = new Object();
		params.formParams = new Object();
		params.action = action;

		if (action == 'add') {
			var record_info = this.TableDirectInfoGrid.getGrid().getSelectionModel().getSelected();
			params.formParams.TableDirectInfo_id = record_info.get('TableDirectInfo_id');
		} else {
			var record = this.TableDirectGrid.getGrid().getSelectionModel().getSelected();
			params.formParams.TableDirect_id = record.get('TableDirect_id');
		}

		params.callback = function() {
			this.TableDirectGrid.getAction('action_refresh').execute();
		}.createDelegate(this);

		getWnd('swTableDirectEditWindow').show(params);
	},

	deleteTableDirectInfo: function() {
		var grid = this.TableDirectInfoGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('TableDirectInfo_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									this.TableDirectInfoGrid.getAction('action_refresh').execute();
								}
							}
						}.createDelegate(this),
						params:{
							TableDirectInfo_id: record.get('TableDirectInfo_id')
						},
						url:'/?c=TableDirect&m=deleteTableDirectInfo'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_informatsiyu_o_bazovyih_spravochnikah'],
			title:lang['podtverjdenie']
		});
	},

	deleteTableDirect: function() {
		var grid = this.TableDirectGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();
		if (!record || Ext.isEmpty(record.get('TableDirect_id'))) {
			return false;
		}

		sw.swMsg.show({
			buttons:Ext.Msg.YESNO,
			fn:function (buttonId, text, obj) {
				if (buttonId == 'yes') {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg:lang['udalenie']});
					loadMask.show();
					Ext.Ajax.request({
						callback:function (options, success, response) {
							loadMask.hide();
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (response_obj.success == false) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_proizoshla_oshibka']);
								}
								else {
									this.TableDirectGrid.getAction('action_refresh').execute();
								}
							}
						}.createDelegate(this),
						params:{
							TableDirect_id: record.get('TableDirect_id')
						},
						url:'/?c=TableDirect&m=deleteTableDirect'
					});
				}
			}.createDelegate(this),
			icon:Ext.MessageBox.QUESTION,
			msg:lang['vyi_hotite_udalit_bazovyiy_spravochnik'],
			title:lang['podtverjdenie']
		});
	},

	initComponent: function() {

		this.TableDirectInfoGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: 'DTVW_TableDirectInfoGrid',
			border: false,
			object: 'TableDirectInfo',
			editformclassname: 'swTableDirectInfoEditWindow',
			root: 'data',
			region: 'center',
			autoScroll: true,
			autoLoadData: false,
			actions: [
				{ name: 'action_add', handler: function(){this.openTableDirectInfoEditWindow('add');}.createDelegate(this) },
				{ name: 'action_edit', handler: function(){this.openTableDirectInfoEditWindow('edit');}.createDelegate(this) },
				{ name: 'action_view', handler: function(){this.openTableDirectInfoEditWindow('view');}.createDelegate(this) },
				{ name: 'action_delete', handler: function(){this.deleteTableDirectInfo();}.createDelegate(this) }
			],
			stringfields: [
				{ name: 'TableDirectInfo_id', type: 'int', header: 'ID', key: true },
				{ name: 'TableDirectInfo_Code', type: 'int', header: lang['kod'], width: 60 },
				{ name: 'TableDirectInfo_Name', type: 'string', header: lang['naimenovanie'], width: 120 },
				{ name: 'TableDirectInfo_SysNick', type: 'string', header: lang['sistemnoe_naimenovanie'], width: 140 },
				{ name: 'TableDirectInfo_Descr', type: 'string', header: lang['opisanie'], id: 'autoexpand' }
			],
			dataUrl: '/?c=TableDirect&m=loadTableDirectInfoGrid'
		});

		this.TableDirectInfoGrid.ViewGridPanel.getSelectionModel().on('rowselect', this.loadTableDirectGrid, this);

		this.searchPanel = new Ext.FormPanel({
			layout: 'form',
			region: 'north',
			frame: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearch(f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			autoHeight: true,
			items: [{
				xtype: 'trigger',
				name: 'TableDirectInfo_Name',
				initTrigger: function(){
					var ts = this.trigger.select('.x-form-trigger', true);
					this.wrap.setStyle('overflow', 'hidden');
					var triggerField = this;
					ts.each(function(t, all, index){
						t.hide = function(){
							var w = triggerField.wrap.getWidth();
							this.dom.style.display = 'none';
							triggerField.el.setWidth(w-triggerField.trigger.getWidth());
						};
						t.show = function(){
							var w = triggerField.wrap.getWidth();
							this.dom.style.display = '';
							triggerField.el.setWidth(w-triggerField.trigger.getWidth());
						};
						var triggerIndex = 'Trigger'+(index+1);
						if(this['hide'+triggerIndex]){
							t.dom.style.display = 'none';
						}
						t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
						t.addClassOnOver('x-form-trigger-over');
						t.addClassOnClick('x-form-trigger-click');
					}, this);
					this.triggers = ts.elements;
				},
				onTrigger1Click: this.doSearch.createDelegate(this, []),
				onTrigger2Click: function() {
					this.reset();
				},
				triggerConfig: {
					tag:'span', cls:'x-form-twin-triggers', cn:[
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"},
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger"}
					]},
				anchor: '100%',
				fieldLabel: lang['naimenovanie']
			}]
		});
		this.contentSearchPanel = new Ext.FormPanel({
			layout: 'form',
			region: 'north',
			frame: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearchContent(f.focus.createDelegate(f));
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			autoHeight: true,
			items: [{
				xtype: 'trigger',
				name: 'TableDirect_Name',
				initTrigger: function(){
					var ts = this.trigger.select('.x-form-trigger', true);
					this.wrap.setStyle('overflow', 'hidden');
					var triggerField = this;
					ts.each(function(t, all, index){
						t.hide = function(){
							var w = triggerField.wrap.getWidth();
							this.dom.style.display = 'none';
							triggerField.el.setWidth(w-triggerField.trigger.getWidth());
						};
						t.show = function(){
							var w = triggerField.wrap.getWidth();
							this.dom.style.display = '';
							triggerField.el.setWidth(w-triggerField.trigger.getWidth());
						};
						var triggerIndex = 'Trigger'+(index+1);
						if(this['hide'+triggerIndex]){
							t.dom.style.display = 'none';
						}
						t.on("click", this['on'+triggerIndex+'Click'], this, {preventDefault:true});
						t.addClassOnOver('x-form-trigger-over');
						t.addClassOnClick('x-form-trigger-click');
					}, this);
					this.triggers = ts.elements;
				},
				onTrigger1Click: this.doSearchContent.createDelegate(this, []),
				onTrigger2Click: function() {
					this.reset();
				},
				triggerConfig: {
					tag:'span', cls:'x-form-twin-triggers', cn:[
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"},
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger"}
					]},
				anchor: '100%',
				fieldLabel: lang['naimenovanie']
			}]
		});

		this.TableDirectInfoGridPanel = new Ext.Panel({
			title: lang['spisok_spravochnikov'],
			floatable: false,
			autoScroll: true,
			collapsible: true,
			animCollapse: false,
			layout: 'border',
			listeners: {
				resize: function() {
					if(this.layout.layout)
						this.doLayout();
				}
			},
			titleCollapse: true,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			width: 550,
			minWidth: 350,
			maxWidth: 750,
			split: true,
			region: 'west',
			items: [this.searchPanel,this.TableDirectInfoGrid]
		});

		this.TableDirectGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			title: '',
			listeners: {
				resize: function() {
					if(this.layout.layout)
						this.doLayout();
				}
			},
			editformclassname: 'swTableDirectEditWindow',
			id: 'TDVW_TableDirectGrid',
			autoScroll: true,
			object: '',
			region: 'center',
			params: {
				callback: function() {
					log('callback');
					this.TableDirectGrid.ViewActions.action_refresh.execute();
				}.createDelegate(this)
			},
			autoLoadData: false,
			actions: [
				{ name: 'action_add', handler: function(){this.openTableDirectEditWindow('add');}.createDelegate(this) },
				{ name: 'action_edit', handler: function(){this.openTableDirectEditWindow('edit');}.createDelegate(this) },
				{ name: 'action_view', handler: function(){this.openTableDirectEditWindow('view');}.createDelegate(this) },
				{ name: 'action_delete', handler: function(){this.deleteTableDirect();}.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'TableDirect_id', type: 'int', header: 'ID', key: true },
				{ name: 'TableDirectInfo_id', type: 'int', hidden: true },
				{ name: 'TableDirect_Code', type: 'int', header: lang['kod'], width: 60 },
				{ name: 'TableDirect_Name', type: 'string', header: lang['naimenovanie'], id: 'autoexpand' },
				{ name: 'TableDirect_SysNick', type: 'string', header: lang['sistemnoe_naimenovanie'], width: 120 },
				{ name: 'TableDirect_begDate', type: 'date', header: lang['nachalo'], width: 80 },
				{ name: 'TableDirect_endDate', type: 'date', header: lang['okonchanie'], width: 80 },
			],
			root: 'data',
			totalProperty: 'totalCount',
			paging: true,
			pageSize: 100,
			dataUrl: '/?c=TableDirect&m=loadTableDirectGrid'
		});

		this.TableDirectGridPanel = new Ext.Panel({
			title: '...',
			floatable: false,
			autoScroll: true,
			collapsible: false,
			animCollapse: false,
			layout: 'border',
			listeners: {
				resize: function() {
					if(this.layout.layout)
						this.doLayout();
				}
			},
			width: 250,
			minWidth: 350,
			maxWidth: 750,
			split: true,
			region: 'center',
			items: [this.TableDirectGrid,this.contentSearchPanel]
		});

		Ext.apply(this, {
			layout: 'border',
			items: [this.TableDirectInfoGridPanel,
				this.TableDirectGridPanel
			],
			buttons: [{
				text: '-'
			},
				HelpButton(this),
				{
					iconCls: 'cancel16',
					text: BTN_FRMCLOSE,
					handler: this.hide.createDelegate(this, [])
				}],
			buttonAlign: 'right'
		});
		sw.Promed.swTableDirectViewWindow.superclass.initComponent.apply(this, arguments);
	}
});