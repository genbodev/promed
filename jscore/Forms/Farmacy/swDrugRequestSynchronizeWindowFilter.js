/**
* swDrugRequestSynchronizeWindowFilter - окно синхронизации списка медикаментов с нормативным перечнем + доп. фильтры
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Farmacy
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Salakhov R.
* @version      17.09.2012
* @comment      
*/
sw.Promed.swDrugRequestSynchronizeWindowFilter = Ext.extend(sw.Promed.BaseForm, { 
	autoHeight: false,
	title: lang['medikamentyi_dlya_zayavki_potochnyiy_vvod'],
	layout: 'border',
	id: 'DrugRequestSynchronizeWindowFilter',
	modal: true,
	shim: false,
	width: 600,
	height: 288,
	resizable: false,
	maximizable: false,
	maximized: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	onSave: Ext.emptyFn,
	doSave: function() {
		var wnd = this;
		var data = wnd.DrugGrid.getChangedData();
        
        //console.log('data', data)
        
		if (data.length > 0) {
			wnd.onSave(data);
			this.hide();
		} else {
			sw.swMsg.alert(lang['oshibka'], lang['spisok_izmeneniy_pust']);
		}
		return true;		
	},
    
	doSaveAndContinue: function() {
		var wnd = this;
		var data = wnd.DrugGrid.getChangedData();
		if (data.length > 0) {
			wnd.onSave(data);



		} else {
			sw.swMsg.alert(lang['oshibka'], lang['spisok_izmeneniy_pust']);
		}
        
        this.clearFilter();
 
		return true;		
	},    
 
	doSynchronize: function(DrugNormativeList_id) {
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		
		this.DrugGrid.removeAll();
		if (DrugNormativeList_id > 0) {
			params.DrugNormativeList_id = DrugNormativeList_id;

			if (this.DrugComplexMnn_id_list && this.DrugComplexMnn_id_list != '')
				params.DrugComplexMnn_id_list = this.DrugComplexMnn_id_list;
			else
				params.DrugComplexMnn_id_list = null;

			this.DrugGrid.loadData({
				globalFilters: params
			});
		}
	},
	doSearch: function() {
		var params = new Object();
		params.limit = 100;
		params.start =  0;
		
        params.DrugNormativeList_id = this.list_combo.getValue();
        params.DrugComplexMnnName_Name = Ext.getCmp('DrugComplexMnnName_Name').getValue();
        params.CLSDRUGFORMS_fullname = Ext.getCmp('CLSDRUGFORMS_fullname').getValue();
        params.DrugComplexMnnDose_Name = Ext.getCmp('DrugComplexMnnDose_Name').getValue();
        params.DrugComplexMnnFas_Name = Ext.getCmp('DrugComplexMnnFas_Name').getValue();


			if (this.DrugComplexMnn_id_list && this.DrugComplexMnn_id_list != '')
				params.DrugComplexMnn_id_list = this.DrugComplexMnn_id_list;
			else
				params.DrugComplexMnn_id_list = null;

			this.DrugGrid.loadData({
				globalFilters: params
			});
		//}
       
	},    
	show: function() {
        var wnd = this;
        this.clearFilter();
		sw.Promed.swDrugRequestSynchronizeWindowFilter.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
		this.action = (arguments[0].action) ? arguments[0].action : 'add';
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		} else {
			wnd.onSave = Ext.emptyFn;
		}
		if ( arguments[0].DrugComplexMnn_id_list ) {
			this.DrugComplexMnn_id_list = arguments[0].DrugComplexMnn_id_list;
		} else {
			this.DrugComplexMnn_id_list = null;
		}
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		this.list_combo.getStore().load();
		this.list_combo.setValue(null);
		wnd.doSynchronize();

        //ОТЛАДКА
        /*
		this.DrugGrid.loadData({
			globalFilters: {
                    DrugComplexMnnName_Name : lang['a'],
                    CLSDRUGFORMS_fullname : lang['t'],
                    DrugComplexMnnDose_Name : 1,
                    DrugComplexMnnFas_Name : 1
                }
		});           
        */
		loadMask.hide();
	},
    clearFilter : function(){
        var wnd = this;
	    Ext.getCmp('DrugComplexMnnName_Name').setValue('');
        Ext.getCmp('CLSDRUGFORMS_fullname').setValue('');
        Ext.getCmp('DrugComplexMnnDose_Name').setValue('');
        Ext.getCmp('DrugComplexMnnFas_Name').setValue('');        
        wnd.DrugGrid.getGrid().getStore().removeAll();          
    },
	initComponent: function() {
		var wnd = this;		
		
		this.DrugGrid = new sw.Promed.ViewFrame({
			actions: [
				{name: 'action_add', hidden: true},
				{name: 'action_edit', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_refresh', hidden: true},
				{name: 'action_delete', hidden: true},
				{name: 'action_print', hidden: true}
			],
            focusOnFirstLoad:true,
            noFocusOnLoad : true,          
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
            multi : true,
            //selectionModel: 'multiselect',     
			border: true,
			dataUrl: '/?c=DrugRequestProperty&m=loadSynchronizeList',
			height: 280,
			region: 'center',
			id: 'drswfDrugGrid',
			paging: false,
			style: 'margin-bottom: 10px',
			stringfields: [
                {name: 'checked', header: lang['vyibrat'], width: 110, renderer: sw.Promed.Format.checkColumn},
				{name: 'DrugListRequest_id', type: 'int', header: 'ID', hidden: true, key: true},
				{name: 'state', type: 'string', header: 'state', hidden: true},
				{name: 'action_name', type: 'string', header: lang['deystvie'], width: 100, hidden: true},
				{name: 'DrugComplexMnn_id', type: 'string', header: lang['id_mnn'], hidden: true},
				{name: 'DrugComplexMnn_Code', type: 'string', hidden: true},
				{name: 'ATX_CODE_list', type: 'string', hidden: true},
				{name: 'STRONGGROUPID', type: 'string', hidden: true},
				{name: 'NARCOGROUPID', type: 'string', hidden: true},
				{name: 'ClsDrugForms_Name', type: 'string', hidden: true},
				/*{name: 'DrugComplexMnn_RusName', type: 'string', header: lang['mnn'], id: 'autoexpand', width: 160, hidden: true},*/
				{name: 'DrugComplexMnnName_Name', type: 'string',id: 'autoexpand', header: lang['naimenovanie'], width: 170},
                {name: 'FULLNAME', type: 'string', header: lang['lekarstvennaya_forma'], width: 170},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: lang['dozirovka'], width: 170},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: lang['fasovka'], width: 170},
				{name: 'NTFR_Name', hidden: true},
                {name: 'DrugListRequest_Number', type: 'string', header: lang['p_p'], width: 170, editor: new Ext.form.TextField({
                    listeners : {
                        'blur' : function(){

                        }
                    }
                })},
                {name: 'DrugListRequest_Comment', type: 'string', header: lang['primechanie'], width: 170, editor: new Ext.form.TextField({maxLength: 200})}
			],
			title: lang['medikamentyi'],
			toolbar: false,			
			getChangedData: function(){ //возвращает новые и измненные показатели
                /*
				var data = new Array();
                
                var selection = this.getGrid().getSelectionModel().getSelections();
                
                
                
                
                for(var k in selection){
                    if(typeof selection[k] == 'object'){
                        data.push(selection[k].data);
                    }
                }
				return data;
			    */
                var data = new Array();
				this.getGrid().getStore().clearFilter();
				this.getGrid().getStore().each(function(record) {
					if (record.get('checked')) {
						data.push(record.data);
					}
				});
				return data;                 
            },
          
			onDblClick: function(grid) {
				var record = grid.getSelectionModel().getSelected();
			        record.set('checked', !record.get('checked'));
                    record.set('state', 'add');
                    record.commit();
			},
      
            onAfterEditSelf : function(rec){
                var record = !rec ? this.getSelectionModel().getSelected() : rec.record;                
                if(!record.get('checked')){
                    record.set('checked', !record.get('checked'));
                    record.set('state', 'add');
				    record.commit();
                }
            }
		});


        this.DrugGrid.ViewActions.action_save.initialConfig.url = '/?c=DrugRequestProperty&m=loadSynchronizeList';
         
        
        this.DrugGrid.getGrid().getStore().on(
            'load', function(){
                 wnd.DrugGrid.getGrid().getSelectionModel().deselectRow(0);
            }
        );
/*
       this.DrugGrid.getGrid().on(
                'rowdblclick', function (grid, rowIdx, colIdx, event){
                    grid.getSelectionModel().selectRow(rowIdx);  
                }    
       ); 
*/              
        
		this.list_combo = new Ext.form.ComboBox({
			mode: 'local',
			store: new Ext.data.JsonStore({
				url: '/?c=DrugNormativeList&m=loadList',
				key: 'DrugNormativeList_id',
				autoLoad: false,
				fields: [
					{name: 'DrugNormativeList_id',    type:'int'},
					{name: 'DrugNormativeList_Name',  type:'string'}
				],
				sortInfo: {
					field: 'DrugNormativeList_Name'
				}
			}),
			displayField:'DrugNormativeList_Name',
			valueField: 'DrugNormativeList_id',
			fieldLabel: lang['normativnyiy_perechen'],
			triggerAction: 'all',
			width: 500,
            //layout: 'form',
            labelStyle : 'width:150px',
			tpl: '<tpl for="."><div class="x-combo-list-item">'+
				'{DrugNormativeList_Name}'+
			'</div></tpl>',
			listeners: {
				'select': function(combo) {					
					//wnd.doSynchronize(combo.getValue());
                    //console.log(this);
                }
			}
		});

		var form = new Ext.Panel({
			autoScroll: true,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,			
			frame: true,
			region: 'north',
			labelAlign: 'right',
			items: [
                {
    				xtype: 'form',
    				autoHeight: true,
    				id: 'drswfDrugRequestSynchronizeForm',
    				bodyStyle:'background:#DFE8F6;padding:5px;',
    				border: false,
    				labelWidth: 70,
    				collapsible: true,
    				items: [
                        this.list_combo,
                        {
                            xtype : 'textfield',
                            id : 'DrugComplexMnnName_Name',
                            fieldLabel : lang['mnn'],
                            labelSeparator : ':',
                            labelStyle : 'width:150px',
                            width : 500,
                         	listeners: {
                                'specialkey': function(field, e){
                                    //console.log(e)
                                    if (e.getKey() == e.ENTER) {
                                        Ext.getCmp('searchFilter').handler();
                                    }
                                }
                            }                                
                        },
                        {
                            xtype : 'textfield',
                            id : 'CLSDRUGFORMS_fullname',
                            fieldLabel : lang['lekarstvennaya_forma'],
                            labelSeparator : ':',
                            labelStyle : 'width:150px',
                            width : 500,
                         	listeners: {
                                'specialkey': function(field, e){
                                    //console.log(e)
                                    if (e.getKey() == e.ENTER) {
                                        Ext.getCmp('searchFilter').handler();
                                    }
                                }
                            } 
                        },
                        {
                            xtype : 'textfield',
                            id : 'DrugComplexMnnDose_Name',
                            fieldLabel : lang['dozirovka'],
                            labelSeparator : ':',
                            labelStyle : 'width:150px',
                            width : 500,
                         	listeners: {
                                'specialkey': function(field, e){
                                    //console.log(e)
                                    if (e.getKey() == e.ENTER) {
                                        Ext.getCmp('searchFilter').handler();
                                    }
                                }
                            } 
                        },
                        {
                            xtype : 'textfield',
                            id : 'DrugComplexMnnFas_Name',
                            fieldLabel : lang['fasovka'],
                            labelSeparator : ':',
                            labelStyle : 'width:150px',
                            width : 500,
                         	listeners: {
                                'specialkey': function(field, e){
                                    //console.log(e)
                                    if (e.getKey() == e.ENTER) {
                                        Ext.getCmp('searchFilter').handler();
                                    }
                                }
                            } 
                        }
                    ]
    			}
            ],
            buttonAlign : 'left',
			buttons:
			[{
				handler: function() 
				{
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['zagruzka']});
					loadMask.show();
					wnd.doSearch();
					loadMask.hide();
				},
				iconCls: 'search16',
                style: 'margin-left:495px',
				text: lang['poisk'],
                id : 'searchFilter'
              
			}, 
            {
				handler: function() 
				{
				    Ext.getCmp('DrugComplexMnnName_Name').setValue('');
                    Ext.getCmp('CLSDRUGFORMS_fullname').setValue('');
                    Ext.getCmp('DrugComplexMnnDose_Name').setValue('');
                    Ext.getCmp('DrugComplexMnnFas_Name').setValue('');
                    
                    wnd.DrugGrid.getGrid().getStore().removeAll();                 
                },
				iconCls: 'reset16',
				text: lang['sbros']
			}
            ]            
		});
		Ext.apply(this, {
			layout: 'border',
			bodyStyle: 'padding: 7px;',
			buttons:
			[{
				handler: function() 
				{
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['zagruzka']});
					loadMask.show();
					wnd.doSave();
					loadMask.hide();
				},
				iconCls: 'save16',
				text: lang['vnesti_izmeneniya']
			}, 
{
				handler: function() 
				{
					var loadMask = new Ext.LoadMask(wnd.getEl(), {msg:lang['zagruzka']});
					loadMask.show();
					wnd.doSaveAndContinue();
					loadMask.hide();
				},
				iconCls: 'save16',
				text: lang['vyibrat_i_prodoljit']
			},             
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form,this.DrugGrid]
		});
		sw.Promed.swDrugRequestSynchronizeWindowFilter.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('drswfDrugRequestSynchronizeForm').getForm();
	}	
});