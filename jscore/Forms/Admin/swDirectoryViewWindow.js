/**
* swDirectoryViewWindow - окно управления содержимым справочников
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011-2012 Swan Ltd.
* @author       Storozhev Dmitry
* @version      11.10.2012
*/

sw.Promed.swDirectoryViewWindow = Ext.extend(sw.Promed.BaseForm, {
	maximized: true,
	modal: false,
	resizable: false,
	//plain: false,
	title: lang['spravochniki'],

	show: function() {
		sw.Promed.swDirectoryViewWindow.superclass.show.apply(this, arguments);
        var _this = this;

		this.action = 'edit';
		if (arguments[0] && arguments[0].action) {
			this.action = arguments[0].action;
		}

        this.DirectoryListGrid.addActions({
            iconCls: 'x-btn-text',
            name:'refresh_Directory',
            align: 'left',
            text:lang['import_dannyih'],
            handler: function()
            {
                if ( getWnd('swDirectoryImportWindow').isVisible() ) {
                    sw.swMsg.alert(lang['oshibka'], lang['importa_dannyih_spravochnika_uje_otkryito']);
                    return false;
                }

                getWnd('swDirectoryImportWindow').show({
                    Directory_Name: _this.DirectoryListGrid.getGrid().getSelectionModel().getSelected().data.LocalDbList_name,
                    Directory_Schema: _this.DirectoryListGrid.getGrid().getSelectionModel().getSelected().data.LocalDbList_schema
                });

                /*Ext.Ajax.request({
                    params: {
                        Directory_Name: _this.DirectoryListGrid.getGrid().getSelectionModel().getSelected().data.LocalDbList_name,
                        Directory_Schema: _this.DirectoryListGrid.getGrid().getSelectionModel().getSelected().data.LocalDbList_schema
                    },
                    url: '/?c=MongoDBWork&m=loadDirectoryFieldList',
                    scope: this,
                    callback: function(options, success, response) {
                        this.getLoadMask().hide();
                        if(success && response) {
                            var response_obj = Ext.util.JSON.decode(response.responseText),
                                DirectoryColumnNames = [];

                            //log(response_obj);
                            log('response_obj[0]');
                            log(response_obj[0]);

                            if (!Ext.isEmpty(response_obj[1])) {

                                log('response_obj[1]');
                                log(response_obj[1]);

                                response_obj[0].forEach(function(element, index, array) {
                                    DirectoryColumnNames.push(element['column_name']);
                                });

                                var DirectoryColumnGroupNames = [];
                                response_obj[1].forEach(function(element, index, array) {
                                    DirectoryColumnGroupNames.push(element['column_name']);
                                });
                            } else {
                                log(lang['pusto']);
                                response_obj[0].forEach(function(element, index, array) {
                                    log(element);
                                    DirectoryColumnNames.push(element['column_name']);
                                });
                            }

                            getWnd('swDirectoryImportWindow').show({DirectoryColumnNames: DirectoryColumnNames, DirectoryColumnGroupNames: DirectoryColumnGroupNames, Directory_Name: _this.DirectoryListGrid.getGrid().getSelectionModel().getSelected().data.LocalDbList_name});
                        }
                    }
                });*/
            }.createDelegate(this)
        });

		this.DirectoryListGrid.setReadOnly(this.action != 'edit');
		this.DirectoryListGrid.getAction('refresh_Directory').setDisabled(this.action != 'edit');
		this.DirectoryContentGrid.setReadOnly(this.action != 'edit');

		this.DirectoryListGrid.loadData();
	},

	getDirectoryContentGrid: function() {
		return this.DirectoryContentGrid.ViewGridPanel;
	},
	
	isNoEditDirectory: function(r) {
		return r.get('LocalDbList_prefix') != r.get('LocalDbList_nick');
	},
	
	loadDirectoryContentGrid: function(sm, rIdx, rec) {
		if ( rec && rec.get('LocalDbList_name') && rec.get('LocalDbList_name').inlist(['RefTableRegistry','RefTableRegistryVersion','RefTableRegistryVersionFile']) ) {
			this.directoryContentSearchPanelByOid.show();
			
			var proportion = [ 0.5, 0.5, 0 ];
			
			if ( rec.get('LocalDbList_name') == 'RefTableRegistry' ) proportion = [ 0.4, 0.3, 0.3 ];
			
			this.directoryContentSearchPanelBySystemName.show();
			this.directoryContentSearchPanelByName.setWidth( this.directoryContentSearchPanel.getInnerWidth() * proportion[0] );
			this.directoryContentSearchPanelByOid.setWidth( this.directoryContentSearchPanel.getInnerWidth() * proportion[1] );
			this.directoryContentSearchPanelBySystemName.setWidth( this.directoryContentSearchPanel.getInnerWidth() * proportion[2] );
		} else {
			this.directoryContentSearchPanelByOid.hide();
			this.directoryContentSearchPanelBySystemName.hide();
			this.directoryContentSearchPanelByName.setWidth( this.directoryContentSearchPanel.getInnerWidth() );
		}

		var wnd = this;
		var grid = wnd.getDirectoryContentGrid();
		var store = grid.getStore();
		var actions = wnd.DirectoryContentGrid.ViewActions;

		wnd.directoryContentSearchPanelByName.getForm().findField('filterElementsByName').setValue('');
		wnd.directoryContentSearchPanelByOid.getForm().findField('filterElementsByName').setValue('');
		wnd.DirectoryContentGrid.removeAll();

		if ( Ext.isEmpty(rec.get('LocalDbList_id')) ) {
			actions.action_add.setDisabled(true);
			actions.action_refresh.setDisabled(true);
			return false;
		}
		wnd.DirectoryContentGridPanel.setTitle( rec.get('LocalDbList_Descr') || langs('Нет описания') );
		wnd.DirectoryContentGrid['object'] = rec.get('LocalDbList_schema') + '.' + rec.get('LocalDbList_name');

		if( wnd.isNoEditDirectory(rec) || wnd.action == 'view') {
			wnd.DirectoryContentGrid.setReadOnly(true);
		} else {
			wnd.DirectoryContentGrid.setReadOnly(false);
		}

		store.setDefaultSort();
		
		Ext.Ajax.request({
			url: '/?c=MongoDBWork&m=getDirectoryFields',
			params: {
				Directory_Name: rec.get('LocalDbList_name'),
				Directory_Schema: rec.get('LocalDbList_schema')
			},
			callback: function(options, success, response) {
				var responseObj = Ext.util.JSON.decode(response.responseText);

				if (!success || responseObj.success === false) {
					return;
				}

				var fields = [];
				var columns = [];

				responseObj.forEach(function(item) {
					var field = {
						name: item.name
					};

					if (item.type == 'int' || item.type == 'bigint') {
						field.type = 'int';
					}

					fields.push(field);

					var column = {
						dataIndex: item.name,
						header: !Ext.isEmpty(item.descr) ? item.descr.slice(0,1).toUpperCase()+item.descr.slice(1) : '',
						hidden: item.hidden || false,
						sortable: true,
						width: 180
					};

					if (item.type == 'date') {
						column.renderer = Ext.util.Format.dateRenderer('d.m.Y');
					}
					if (item.type.inlist(['datetime', 'timestamp'])) {
						column.renderer = Ext.util.Format.dateRenderer('d.m.Y H:i:s');
					}

					columns.push(column);
				});

				var tmpStore = new Ext.data.JsonStore({
					autoLoad: false,
					root: 'data',
					totalProperty: 'totalCount',
					url: '/?c=MongoDBWork&m=getDirectoryData',
					fields: fields
				});

				var colModel = new Ext.grid.ColumnModel(columns);

				store.reader = tmpStore.reader;
				store.fields = tmpStore.fields;

				grid.reconfigure(store, colModel);

				wnd.DirectoryContentGrid.loadData({
					globalFilters: {
						Directory_Name: rec.get('LocalDbList_name'),
						Directory_Schema: rec.get('LocalDbList_schema'),
						start: 0,
						limit: wnd.DirectoryContentGrid.pageSize,
						filterElementsByName: ''
					}, params: {
						Directory_Name: rec.get('LocalDbList_name'),
						Directory_Schema: rec.get('LocalDbList_schema')
					}
				});
			}
		});
	},

	doSearch: function(cb) {
		var form = this.searchPanel.getForm();
		this.DirectoryListGrid.loadData({ globalFilters: form.getValues(), callback: cb || Ext.emptyFn });
	},

	doSearchDirectoryContent: function( cb, type ) {

		var gfilter = this.directoryContentSearchPanelByName.getForm().getValues();

		if ( type == 'byOid' ) gfilter = this.directoryContentSearchPanelByOid.getForm().getValues();
		if ( type == 'bySystemName' ) gfilter = this.directoryContentSearchPanelBySystemName.getForm().getValues();

		gfilter.Directory_Name = this.DirectoryListGrid.getGrid().getSelectionModel().getSelected().get('LocalDbList_name');
		gfilter.directoryContentSearchPanelType = type;
		gfilter.start = 0;
		gfilter.limit = this.DirectoryContentGrid.pageSize;

		this.DirectoryContentGrid.loadData({globalFilters: gfilter, callback: cb || Ext.emptyFn });
	},

	deleteDirectoryRecord: function() {
		var dir_record = this.DirectoryListGrid.getGrid().getSelectionModel().getSelected();
		var sel_record = this.getDirectoryContentGrid().getSelectionModel().getSelected();
		if( !dir_record || !sel_record ) return;
		
		Ext.Msg.show({
			title: lang['vnimanie'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_zapis_iz_spravochnika'],
			buttons: Ext.Msg.YESNO,
			fn: function(btn) {
				if (btn === 'yes') {
					this.getLoadMask(lang['udalenie_zapisi']).show();
					Ext.Ajax.request({
						params: {
							scheme: dir_record.get('LocalDbList_schema'),
							table: dir_record.get('LocalDbList_name'),
							keyName: sel_record.get('keyName'),
							keyValue: sel_record.get('id')
						},
						url: '/?c=MongoDBWork&m=deleteDirectoryRecord',
						scope: this,
						callback: function(options, success, response) {	
							this.getLoadMask().hide();
							if(success) {
								this.DirectoryContentGrid.ViewActions.action_refresh.execute();
							}
						}
					});
				}
			},
			scope: this,
			icon: Ext.MessageBox.QUESTION
		});
	},

	initComponent: function() {
		var that = this;
		this.DirectoryListGrid = new sw.Promed.ViewFrame({
			autoExpandColumn: 'autoexpand',
			id: this.id + '_ListGrid',
			border: false,
			object: 'LocalDBTables',
			editformclassname: 'swDBLocalDbListEditWindow',
			region: 'center',
			autoScroll: true,
			autoLoadData: false,
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', url: '/?c=MongoDBWork&m=deleteLocalDbList' },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'LocalDbList_id', type: 'int', hidden: true, key: true },
				{ name: 'LocalDbList_schema', header: lang['shema'], hidden: true, isparams: true },
				{ name: 'LocalDbList_prefix', header: lang['prefiks'], hidden: true, isparams: true },
				{ name: 'LocalDbList_nick', header: lang['alias'], hidden: true, isparams: true },
				{ name: 'LocalDbList_sql', header: 'SQL', hidden: true, isparams: true },
				{ name: 'LocalDbList_module', header: lang['modul'], hidden: true, isparams: true },
				{ name: 'LocalDbList_key', header: lang['klyuch'], hidden: true, isparams: true },
				{ name: 'LocalDbList_name', type: 'string', header: lang['naimenovanie'], isparams: true, width: 140 },
				{ name: 'LocalDbList_Descr', type: 'string', header: lang['opisanie'], isparams: true, id: 'autoexpand' },
				{ name: 'LocalDbList_insDT', type: 'string', header: lang['dobavlen'], isparams: true, width: 80 },
				{ name: 'LocalDbList_updDT', type: 'string', header: lang['izmenen'], isparams: true, width: 80 }
			],
			dataUrl: '/?c=MongoDBWork&m=loadDirectoryListGrid'
		});

		this.DirectoryListGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				if ( this.isNoEditDirectory(row) )
					cls = cls+'x-grid-rowgray ';
				return cls;
			}.createDelegate(this)
		});
		
		this.DirectoryListGrid.ViewGridPanel.getSelectionModel().on('rowselect', this.loadDirectoryContentGrid, this);

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
				name: 'Directory_Name',
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
					that.doSearch();
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

		this.directoryContentSearchPanelByName = new Ext.FormPanel({
			layout: 'form',
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearchDirectoryContent(f.focus.createDelegate(f),'byName');
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			columnWidth: 1.0,
			items: [{
				xtype: 'trigger',
				name: 'filterElementsByName',
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
				onTrigger1Click: function(e) {
					var f = Ext.get(e.getTarget());
					that.doSearchDirectoryContent(f.focus.createDelegate(f),'byName');
				},
				onTrigger2Click: function() {
					this.reset();
					that.doSearchDirectoryContent();
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

		this.directoryContentSearchPanelByOid = new Ext.FormPanel({
			layout: 'form',
			hidden: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearchDirectoryContent(f.focus.createDelegate(f),'byOid');
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			items: [{
				xtype: 'trigger',
				name: 'filterElementsByName',
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
				onTrigger1Click: function(e) {
					var f = Ext.get(e.getTarget());
					that.doSearchDirectoryContent(f.focus.createDelegate(f),'byOid');
				},
				onTrigger2Click: function() {
					this.reset();
					that.doSearchDirectoryContent();
				},
				triggerConfig: {
					tag:'span', cls:'x-form-twin-triggers', cn:[
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"},
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger"}
					]},
				anchor: '100%',
				fieldLabel: 'OID'
			}]
		});
		
		this.directoryContentSearchPanelBySystemName = new Ext.FormPanel({
			layout: 'form',
			hidden: true,
			keys: [{
				fn: function(inp, e) {
					var f = Ext.get(e.getTarget());
					this.doSearchDirectoryContent(f.focus.createDelegate(f),'bySystemName');
				},
				key: [ Ext.EventObject.ENTER ],
				scope: this,
				stopEvent: true
			}],
			labelAlign: 'right',
			items: [{
				xtype: 'trigger',
				name: 'filterElementsByName',
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
				onTrigger1Click: function(e) {
					var f = Ext.get(e.getTarget());
					that.doSearchDirectoryContent(f.focus.createDelegate(f),'bySystemName');
				},
				onTrigger2Click: function() {
					this.reset();
					that.doSearchDirectoryContent();
				},
				triggerConfig: {
					tag:'span', cls:'x-form-twin-triggers', cn:[
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger"},
						{tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger"}
					]},
				anchor: '100%',
				fieldLabel: 'Системное наименование'
			}]
		});

		this.WrapListGridPanel = new Ext.Panel({
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
			items: [this.searchPanel,this.DirectoryListGrid]
		});

		this.DirectoryContentGrid = new sw.Promed.ViewFrame({
			border: false,
			autoExpandColumn: 'autoexpand',
			title: '',
			listeners: {
				 resize: function() {
					if(this.layout.layout)
						this.doLayout();
				 }
			},
			editformclassname: 'swDirectoryEditWindow',
			id: this.id + '_ContentGrid',
			autoScroll: true,
			remoteSort: getRegionNick() == 'vologda',
			object: '',
			region: 'center',
			params: {
				callback: function() {
					this.DirectoryContentGrid.ViewActions.action_refresh.execute();
				}.createDelegate(this)
			},
			autoLoadData: false,
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view' },
				{ name: 'action_delete', handler: this.deleteDirectoryRecord.createDelegate(this) },
				{ name: 'action_refresh' },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ name: 'id', type: 'int', hidden: false, header: lang['identifikator'], key: true },
				{ name: 'dirName', type: 'string', hidden: true, isparams: true },
				{ name: 'keyName', type: 'string', hidden: true, isparams: true },
				{ name: 'Code', type: 'string', header: langs('Код'), width: 60 },
				{ name: 'Name', type: 'string', header: langs('Наименование'), width: 150 },
				{ name: 'Nick', type: 'string', header: langs('Краткое наименование'), hidden: true, width: 150 },
				{ name: 'SysNick', type: 'string', header: langs('Сокращение'), width: 150 },
				{ name: 'Editor', type: 'string', header: langs('Редактор'), width: 250 },
				{ name: 'updDT', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s'), header: langs('Изменен'), width: 140 }
			],
			root: 'data',
			totalProperty: 'totalCount',
			paging: true,
			pageSize: 50,
			dataUrl: '/?c=MongoDBWork&m=getDirectoryData'
		});

		this.directoryContentSearchPanel = new Ext.Panel({
			frame: true,
			border: false,
			floatable: false,
			autoScroll: true,
			collapsible: false,
			animCollapse: false,
			layout: 'column',
			listeners: {
				resize: function() {
					if(this.layout.layout)
						this.doLayout();
				}
			},
			width: 250,
			minWidth: 350,
			maxWidth: 750,
			autoHeight: true,
			region: 'north',
			items: [this.directoryContentSearchPanelByName, this.directoryContentSearchPanelByOid, this.directoryContentSearchPanelBySystemName]
		});


		this.DirectoryContentGridPanel = new Ext.Panel({
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
			items: [this.DirectoryContentGrid,this.directoryContentSearchPanel]
		});

    	Ext.apply(this, {
			layout: 'border',
			items: [this.WrapListGridPanel,
				this.DirectoryContentGridPanel
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
		sw.Promed.swDirectoryViewWindow.superclass.initComponent.apply(this, arguments);
	}
});