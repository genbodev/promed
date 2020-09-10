/**
* swMongoCacheViewWindow - окно управления кешем в MongoDB
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright	Copyright (c) 2014 Swan Ltd.
* @author		Марков Андрей <markov@swan.perm.ru>
* @version		07.2014
*/

sw.Promed.swMongoCacheViewWindow = Ext.extend(sw.Promed.BaseForm, {
	maximized: true,
	modal: false,
	resizable: false,
	//plain: false,
	title: lang['mongodb_kesh'],

	show: function() {
		sw.Promed.swMongoCacheViewWindow.superclass.show.apply(this, arguments);
        var _this = this;
		/*this.MongoCacheListGrid.addActions({
			iconCls: 'settings16',
			name:'action_settings',
			text:lang['nastroyki'],
			handler: function()
			{
				this.settingsMongoCache();
			}.createDelegate(this)
		});*/
	
		this.MongoCacheListGrid.addActions({
			name:'actions_clear',
			text:lang['ochistit'], 
			tooltip: lang['ochistit'],
			menu: [
				new Ext.Action({name:'clear_timeout', text:lang['ochistit_neaktualnyie'], tooltip: lang['ochistit_neaktualnyie'], handler: function() {_this.clearMongoCache('unactual');}}),
				new Ext.Action({name:'clear_all', text:lang['ochistit_vse'], tooltip: lang['ochistit_vse'], handler: function() {_this.clearMongoCache('all');}}),
			],
			iconCls : 'x-btn-text',
			icon: 'img/icons/actions16.png',
			handler: function() {}
		});
		this.MongoCacheListGrid.addActions({
			iconCls: 'actions16',
			name:'actions_recache',
			text:lang['peresobrat'],
			menu: [
				new Ext.Action({name:'recache_chg', text:lang['peresobrat_izmenennyie'], tooltip: lang['peresobrat_izmenennyie'], handler: function() {_this.recacheMongoCache('change');}}),
				new Ext.Action({name:'recache_id', text:lang['peresobrat_po_identifikatoru'], tooltip: lang['peresobrat_po_identifikatoru'], handler: function() {_this.recacheMongoCache('id');}}),
				new Ext.Action({name:'recache_all', text:lang['peresobrat_vse'], tooltip: lang['peresobrat_vse'], handler: function() {_this.recacheMongoCache('all');}})
			],
			handler: function(){}
		});
		this.MongoCacheListGrid.loadData();
	},

	loadMongoCacheContent: function(sm, index, record) {
		var params = {
			sysCache_object: record.get('sysCache_object'),
			panelId: this.MongoCacheContentPanel.id // отправляем идентификатор панели для правильной генерации HTML
		};
		this.MongoCacheContentPanel.setTitle(record.get('sysCache_name'));
		this.MongoCacheContentPanel.load({
			url: '/?c=MongoCache&m=loadMongoCacheContent',
			params: params,
			scripts:true,
			text: lang['podojdite_idet_zagruzka_zapisey_kesha'],
			callback: function () {
				// Очищаем массив выбранных бирок при перезагрузке расписания
				
			}.createDelegate(this),
			failure: function () {
				Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_zapisey_kesha_poprobuyte_esche_raz']);
			}
		});
		return true;
	},
	
	recacheMongoCache: function(type) {
		var selected = this.MongoCacheListGrid.getGrid().getSelectionModel().getSelected();
		var params = {};
		var msg;
		if( !selected ) return;
		if(!type)type='all';
		params = {
					sysCache_id: selected.get('sysCache_id'),
					sysCache_object: selected.get('sysCache_object')
				};
		if(type!='id'){
			switch(type){
				case 'all':
					params.type = 'all';
					msg=lang['hotite_peresobrat_kesh_po_vyibrannomu_obyektu']+selected.get('sysCache_object')+'?';
					break;
				case 'change':
					params.type = 'change';
					msg=lang['hotite_obnovit_vyibrannyiy_obyekt']+selected.get('sysCache_object')+'?';
					break;
			}
			Ext.Msg.show({
				title: lang['vnimanie'],
				msg:msg,
				buttons: Ext.Msg.YESNO,
				fn: function(btn) {
					if (btn === 'yes') {
						this.getLoadMask(lang['perekeshirovanie_dannyih_obyekta']).show();
						Ext.Ajax.request({
							params:params,
							url: '/?c=MongoCache&m=recacheMongoCache',
							scope: this,
							callback: function(options, success, response) {	
								this.getLoadMask().hide();
								if(success) {
									this.MongoCacheListGrid.loadData();
								}
							}
						});
					}
				},
				scope: this,
				icon: Ext.MessageBox.QUESTION
			});
			return true;
		}else{
			getWnd('swMongoRecacheIdWindow').show(params);
		}
	},
	clearMongoCache: function(type) {
		var selected = this.MongoCacheListGrid.getGrid().getSelectionModel().getSelected();
		var params = {};
		var msg;
		if( !selected ) return;
		if(!type)type='all';
		params = {
					sysCache_id: selected.get('sysCache_id'),
					sysCache_object: selected.get('sysCache_object')
				};
		
			switch(type){
				case 'all':
					params.type = 'all';
					msg=lang['vyi_deystvitelno_hotite_udalit_vse_zapisi_po_vyibrannomu_obyektu'];
					break;
				case 'unactual':
					params.type = 'unactual';
					msg=lang['vyi_deystvitelno_hotite_udalit_neaktualnyie_zapisi_po_vyibrannomu_obyektu'];
					break;
			}
			Ext.Msg.show({
				title: lang['vnimanie'],
				msg:msg,
				buttons: Ext.Msg.YESNO,
				fn: function(btn) {
					if (btn === 'yes') {
						this.getLoadMask(lang['ochistka_dannyih']).show();
						Ext.Ajax.request({
							params:params,
							url: '/?c=MongoCache&m=clearMongoCache',
							scope: this,
							callback: function(options, success, response) {	
								this.getLoadMask().hide();
								if(success) {
									this.MongoCacheListGrid.loadData();
								}
							}
						});
					}
				},
				scope: this,
				icon: Ext.MessageBox.QUESTION
			});
			return true;
		
	},
	
	doSearch: function(cb) {
		var form = this.searchPanel.getForm();
		var params = form.getValues();
		params['searchAuto'] = (params['searchAuto'])?params['searchAuto']:0;
		this.MongoCacheListGrid.loadData({ globalFilters: params, callback: cb || Ext.emptyFn });
	},

	initComponent: function() {
		
		this.MongoCacheListGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_ListGrid',
			border: false,
			object: 'sysCache',
			editformclassname: 'swMongoCacheEditWindow',
			region: 'center',
			autoScroll: true,
			autoLoadData: false,
			selectionModel: 'multiselect',
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', url: '/?c=MongoCache&m=deleteMongoCache' },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'sysCache_id', type: 'int', hidden: true, key: true },
				{ name: 'sysCache_settings', header: lang['nastroyki'], hidden: true, isparams: true },
				{ name: 'sysCache_ttl', header: lang['aktualnost_kesha'], hidden: true, isparams: true },
				{ name: 'sysCache_time', header: lang['vremya_poslednego_obnovleniya_kesha'], hidden: true, isparams: true },
				{ name: 'sysCache_auto', header: lang['avtosozdannyiy'], hidden: true, isparams: true },
				{ name: 'sysCache_sql', header: 'SQL', hidden: true, isparams: true },
				{ name: 'sysCache_name', type: 'string', header: lang['naimenovanie'], isparams: true, width: 200 },
				{ name: 'sysCache_object', header: lang['obyekt_bd'], isparams: true, width: 160 },
				{ name: 'sysCache_count', header: lang['kol-vo'], type: 'int', width: 80 },
				{ name: 'sysCache_insDT', type: 'string', header: lang['dobavlen'], isparams: true, width: 110 },
				{ name: 'sysCache_updDT', type: 'string', header: lang['izmenen'], isparams: true, width: 110 }
			],
			dataUrl: '/?c=MongoCache&m=loadMongoCacheList',
			onRowSelect: function(sm,rowIdx,record) {
				var records = sm.getSelections();
				this.MongoCacheListGrid.setActionDisabled('actions_recache', true);
				this.MongoCacheListGrid.setActionDisabled('actions_clear', false);
				if (records.length==1) {
					this.loadMongoCacheContent(sm,rowIdx,record);
					this.MongoCacheListGrid.setActionDisabled('actions_recache', (record.get('sysCache_sql')=='') || record.get('sysCache_auto')==1);
				}
				if (records.length>1) {
					this.MongoCacheListGrid.setActionDisabled('actions_clear', true);
				}
			}.createDelegate(this)
		});

		this.MongoCacheListGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				/*if ( this.isNoEditMongoCache(row) )
					cls = cls+'x-grid-rowgray ';
				*/
				return cls;
			}.createDelegate(this)
		});
		
		
		this.searchPanel = new Ext.FormPanel({
			layout: 'column',
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
			border: true,
			items: [{
				// Левая часть фильтров
				labelAlign: 'left',
				layout: 'form',
				border: false,
				labelWidth: 100,
				columnWidth: .65,
				items: [{
					xtype: 'trigger',
					name: 'searchName',
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
					anchor: '98%',
					fieldLabel: lang['naimenovanie']
				}]
			}, {
				// Правая часть фильтров
				labelAlign: 'left',
				labelWidth: 1,
				layout: 'form',
				border: false,
				columnWidth: .35,
				items: [{
					xtype: 'checkbox',
					id: 'searchAuto',
					name: 'searchAuto',
					labelSeparator: '',
					boxLabel: lang['vklyuchaya_sozdannyie_avtomaticheski']
				}]
			}]
		});
		
		this.MongoCacheContentPanel = new Ext.Panel({
			id: 'MongoCacheContentPanel',
			title: '...',
			bodyStyle: 'padding: 5px',
			autoScroll:true,
			region: 'center',
			frame: false,
			loadMask: true
		});

		this.WrapListGridPanel = new Ext.Panel({
			title: lang['keshiruemyie_obyektyi'],
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
			width: 690,
			minWidth: 480,
			maxWidth: 690,
			split: true,
			region: 'west',
			items: [this.searchPanel,this.MongoCacheListGrid]
		});

    	Ext.apply(this, {
			layout: 'border',
			items: [this.WrapListGridPanel,
				this.MongoCacheContentPanel
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
		sw.Promed.swMongoCacheViewWindow.superclass.initComponent.apply(this, arguments);
	}
});