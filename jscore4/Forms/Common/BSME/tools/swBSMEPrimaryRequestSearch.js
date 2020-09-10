Ext.define('common.BSME.tools.swBSMEPrimaryRequestSearch', {
    extend: 'Ext.window.Window',
    minHeight: 500,
    width: 1000,
    layout: 'fit',
    title: 'Поиск экспертизы',
	//id: 'ForenPersSearch',
	cls: 'ForenPersSearch',
	modal: true,
	//defaultFocus: 'textfield[name=Person_SurName]',
	listeners: {
		close: function(win) {
			if  (typeof win.initialConfig.callback == 'function') {
				win.initialConfig.callback()
			}
		}
	},
	
	EvnForensicNumSearchField: function(){
		return Ext.create('Ext.form.TextField',{
			fieldLabel: '№',
			name: 'EvnForensic_Num',
			labelWidth: '25px',
			width:  75,
			enableKeyEvents: true,
			listeners: {
				scope: this,
				change: function(field, nV, oV) {
					this.EvnForensicSubStore.getProxy().setExtraParam(field.name,nV);
				//	this.EvnForensicSubStore.abort().load();
				},
				keypress: function(t,e,o){				
					if (e.getKey() == 13) {
						this.EvnForensicSubStore.abort().load();						
					}
				}
			}
		});
	},
	
	EvnForensicDateSearchField: function(){
		return {
			xtype: 'swdatefield',
			fieldLabel: 'Дата заявки',
			name: 'Evn_insDT',
			format: 'd.m.Y',
			invalidText: 'Неправильный формат даты. Дата должна быть указана в формате ДД.ММ.ГГГГ',
			plugins: [new Ux.InputTextMask('99.99.9999')],
			labelWidth: '80px',
			width:  200,
			enableKeyEvents: true,
			listeners: {
				scope: this,
				change: function(field, nV, oV) {
					if ( nV == '__.__.____' ) {
						nV = null;
					}
					this.EvnForensicSubStore.getProxy().setExtraParam(field.name,nV);
				//	this.EvnForensicSubStore.abort().load();
				},
				keypress: function(t,e,o){				
					if (e.getKey() == 13) {
						this.EvnForensicSubStore.abort().load();
					}
				}
			}
		};
	},
		
	PersonSurNameSearchField: function(){
		return Ext.create('Ext.form.TextField',{
			fieldLabel: 'Подэкспертный',
			emptyText: 'Фамилия',
			name: 'Person_SurName',
			labelWidth: '100px',
			width:  245,
			enableKeyEvents: true,
			listeners: {
				scope: this,
				change: function(field, nV, oV) {
					this.EvnForensicSubStore.getProxy().setExtraParam(field.name,nV);
				//	this.EvnForensicSubStore.abort().load();
				},
				keypress: function(t,e,o){				
					if (e.getKey() == 13) {
						this.EvnForensicSubStore.abort().load();
					}
				}
			}
		});
	},
		
	PersonFirNameSearchField: function(){
		return Ext.create('Ext.form.TextField',{
			emptyText: 'Имя',
			name: 'Person_FirName',
			labelWidth: '15px',
			width:  150,
			enableKeyEvents: true,
			listeners: {
				scope: this,
				change: function(field, nV, oV) {
					this.EvnForensicSubStore.getProxy().setExtraParam(field.name,nV);
				},
				keypress: function (t, e, o) {
					if (e.getKey() == 13){
						this.EvnForensicSubStore.abort().load();
					}
				}
			}
		});
	},
		
	PersonSecNameSearchField: function(){
		return Ext.create('Ext.form.TextField',{
			emptyText: 'Отчество',
			name: 'Person_SecName',
			labelWidth: '15px',
			width:  150,
			enableKeyEvents: true,
			listeners: {
				scope: this,
				change: function(field, nV, oV) {
					this.EvnForensicSubStore.getProxy().setExtraParam(field.name,nV);
					//this.EvnForensicSubStore.abort().load();
				},
				keypress: function(t,e,o){				
					if ( (e.getKey()==13) )
					{
						this.EvnForensicSubStore.abort().load();						
					}
				}
			}
		});
	},
	
	ExpertSearchField: function(){
		// Если пользователь является экспертом, запретим ему поиск экспертиз
		// среди других экспертов. Но если он так же является заведующим, то
		// оставим эту возможность
		var group_list = getGlobalOptions().groups.split('|'),
			isBsmeExpert = Ext.Array.contains(group_list,'bsmeexpert'),
			isDprtHead = Ext.Array.contains(group_list,'bsmedprthead'),
			value = isBsmeExpert ? parseInt(getGlobalOptions().CurMedPersonal_id) : null;
		
		return Ext.create('sw.MedPersonalExpertsCombo',{
			name: 'Expert_id',
			fieldLabel: 'Эксперт',
			labelWidth: '50px',
			width:  250,
			value: value,
			readOnly: ( isBsmeExpert && !isDprtHead ),
			listeners: {
				scope: this,
				change: function(field, nV, oV) {
					this.EvnForensicSubStore.getProxy().setExtraParam(field.name,nV);
				},
				keypress: function(t,e,o){				
					if (e.getKey() == 13) {
						this.EvnForensicSubStore.abort().load();
					}
				},
				beforerender: function(combo,eOpts){
					if ( isBsmeExpert ) {
						log(value);
						this.EvnForensicSubStore.getProxy().setExtraParam('Expert_id', value);
//						combo.getStore().getProxy().setExtraParam('Expert_id', value);
					}
				}
			}
		});
	},
		
	ButtonSearch: function(){
		return Ext.create('Ext.button.Button', {
			text: 'Поиск',
			iconCls: 'search16',
			name: 'Person_Search',
			margin: '0 5 0 0',
			scope: this,
			handler : function() {
				this.EvnForensicSubStore.abort().load();
			}
		});
	},
	
    initComponent: function() {
        var win = this,
			conf = this.initialConfig;
			
		this.addEvents({
			selectEvnForensic: true
		});
		
		var params = [{'EFS.EvnForensicSub_Num': conf.EvnForensic_Num}];
		
		this.EvnForensicSubStore = Ext.create('sw.ExtendedStore',{
			autoLoad: false,
			pageSize: 10,
			storeId: this.id + 'EvnForensicSubStore',
			fields: [
				{name: 'EvnForensic_id', type: 'int'},
				{name: 'EvnForensic_Num', type: 'string'},
				{name: 'Expert_Fio', type: 'string'},
				{name: 'Expert_id', type: 'int'},
				{name: 'EvnForensicType_Name', type: 'string'},
				{name: 'Person_Fio', type: 'string'},
				{name: 'Person_id', type: 'int'},
				{name: 'Evn_insDT', type: 'string'},
				{name: 'EvnStatus_SysNick', type: 'string'}
			],				
			proxy: {
//				limitParam: undefined,
//				startParam: undefined,
//				paramName: undefined,
//				pageParam: undefined,
				type: 'ajax',
				url: '/?c=BSME&m=getJournalRequestList',
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				actionMethods: {
					create : 'POST',
					read   : 'POST',
					update : 'POST',
					destroy: 'POST'
				},
				extraParams:{
					EvnStatus_SysNick: 'Done',
					JournalType: 'EvnForensicSub',
					filters: Ext.encode(params)
				}
			},
			listeners: {
				load: function (t, recs) {
					if (recs && recs.length) {
						win.searchPersonForm.down('grid').getSelectionModel().select(0);
					}
				}
			}
		});
		
		win.searchPersonForm = Ext.create('sw.BaseForm', {
			//id: 'searchPersonForm',
			border: false,
			frame: true,
			layout: 'auto',
			bodyBorder: false,
			items: [
					{
						xtype: 'grid',
						maxHeight: 250,
						border: false,
						autoScroll: true,
						viewConfig: {
							loadingText: 'Загрузка',
							listeners:{
								itemkeydown:function(view, record, item, index, e){
									if ( (e.getKey()==13) )
									{
										win.down('button[refId=chooseEvnForensic]').handler();
									}
								}
							}
						},
    
						renderIcon: function(val) {
							if (val != 'false'){
								if (val=='true'){val='on'}
								return '<div class="x-grid3-check-'+val+' x-grid3-cc-ext-gen2118"></div>'
							}
						},
						columns: [
							{ text: 'EvnForensic_id',  dataIndex: 'EvnForensic_id', hidden: true, hideable: false },
							{ text: 'Номер заявки',  dataIndex: 'EvnForensic_Num', width: 200 },
							{ text: 'Дата экспертизы', dataIndex: 'Evn_insDT', width: 200 },
							{ text: 'Эксперт', dataIndex: 'Expert_Fio', flex: 1 },
							{ text: 'Подэкспертный', dataIndex: 'Person_Fio', flex: 1 },
							{ text: 'Person_id', dataIndex: 'Person_id', hidden: true, hideable: false }
						],
						store: win.EvnForensicSubStore,
						listeners: {
							scope: this,
							beforecellclick: function( grid, td, cellIndex, record, tr, rowIndex, e, eOpts ){
								this.down('button[refId=chooseEvnForensic]').setDisabled(false);
							},
							itemdblclick: function(grid, record, item, index, e, eOpts) {
								this.down('button[refId=chooseEvnForensic]').handler();
							}
						}
					}
				]
			}
		);
		
		this.filterToolbar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'top',
			xtype: 'toolbar',
			defaults: {
				padding: '0 5 0 0'
			},
			items: [
				this.EvnForensicNumSearchField(),
				this.EvnForensicDateSearchField(),
				this.ExpertSearchField()
			]
		});
		this.filterToolbar2 = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'top',
			xtype: 'toolbar',
			defaults: {
				padding: '0 5 0 0'
			},
			items: [
				this.PersonSurNameSearchField(),
				this.PersonFirNameSearchField(),
				this.PersonSecNameSearchField(),
				this.ButtonSearch()
			]
		});
		
        Ext.applyIf(win, {
            items: [
                 win.searchPersonForm
            ],
            dockedItems: [
				this.filterToolbar,
				this.filterToolbar2,
                {
                    xtype: 'container',
                    dock: 'bottom',
                    layout: {
                        type: 'hbox',
                        align: 'stretch',
                        padding: 4
                    },
                    items: [
                        {
                            xtype: 'container',
                            layout: 'column',
                            items: [
								{
                                    xtype: 'button',
									refId: 'chooseEvnForensic',
                                    text: 'Выбрать',
									iconCls: 'ok16',
									handler: function(){
										var grd = win.down('grid');
										
										win.fireEvent('selectEvnForensic', grd.getSelectionModel().getSelection()[0]);
										win.close();
									}
                                }
                            ]
                        },
                        {
                            xtype: 'container',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'stretch',
                                pack: 'end'
                            },
                            items: [								
								{
									xtype: 'button',
									//id: 'helpEmergencyTeamDutyTimeGrid',
									text: 'Помощь',
									margin: '0 5 0 0',
									iconCls   : 'help16',
									handler   : function()
									{
										//ShowHelp(this.ownerCt.title);
									}
								},
								{
									xtype: 'button',
									//id: 'cancelEmergencyTeamDutyTimeGrid',
									iconCls: 'cancel16',
									text: 'Закрыть',
									handler: function(){
										win.close()
									}
								}
                            ]
                        }
                    ]
                }
            ]
        });

        this.callParent(arguments);
	},
	
	show: function(){
		var args = arguments[0] || {};

		this.filterToolbar2.items.each(function(item,index,len){
			if (item.name != 'Person_Search') {
				if ( item.getName() == 'Person_SurName' && args.Search_Person_SurName ) {
					item.setValue( args.Search_Person_SurName );
				}
				if ( item.getName() == 'Person_FirName' && args.Search_Person_FirName ) {
					item.setValue( args.Search_Person_FirName );
				}
				if ( item.getName() == 'Person_SecName' && args.Search_Person_SecName ) {
					item.setValue( args.Search_Person_SecName );
				}
			}
		});
		
		this.callParent(arguments);
	}

});
