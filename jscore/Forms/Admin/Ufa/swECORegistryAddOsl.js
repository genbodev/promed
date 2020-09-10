/***
swECORegistryAddOsl - добавление осложнений случаю ЭКО
**/
sw.Promed.swECORegistryAddOsl = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Осложнение: Добавление',
    layout: 'border',
    id: 'swECORegistryAddOsl',
    modal: true,
    onHide: Ext.emptyFn,
    onSelect:  Ext.emptyFn,
    shim: false,
    width: 860,
    height: 160,
    resizable: false,
    maximizable: false,
    maximized: false,
    region: 'center',
    m_width_big: 500,
    m_width_min: 150,
    m_width_date: 95,
	EcoOsl_id: '',
    Action: 'Add',
	
    listeners:{
        hide:function () {
            this.onHide();
        }
    },
    
    show: function(params) {		
        sw.Promed.swECORegistryAddOsl.superclass.show.apply(this, arguments);
        var wnd = this;
        wnd.onHide = Ext.emptyFn;
        
        console.log(params);
        
		if (typeof params.Action != 'undefined' && params.Action == 'Edit'){
			wnd.Action = 'Edit';
		}else{
			wnd.Action = 'Add';
		}
		
        if (wnd.Action == 'Add'){
			Ext.getCmp('date_osl').setValue();
			Ext.getCmp('Osl').setValue();
			Ext.getCmp('Ds_osl').setValue();			
		}else{
			wnd.EcoOsl_id = params.EcoOsl_id;
			Ext.getCmp('pers_id').setValue(params.pers_id);
			Ext.getCmp('date_osl').setValue(params.DateOslStr);			
			
			usluga_complex_id = params.osl_id;
			var combo = Ext.getCmp('Osl');
			combo.setValue(usluga_complex_id);
			
			paramss = {};
			paramss.UslugaComplex_id = usluga_complex_id;			
						
			usluga_complex_id = params.ds_int;
			var combo = Ext.getCmp('Ds_osl');
			
			paramss = {};
			paramss.UslugaComplex_id = usluga_complex_id;			
			
			combo.getStore().load({
				callback: function() {
						combo.setValue(usluga_complex_id);
						combo.isload = true;
				}.createDelegate(this),
				params: paramss
			});				
		}		
		
        
        if (params.date){
            Ext.getCmp('date_osl').setValue(params.date);
        }       
        
        
    },
    
//Добавление осложнения
    onAddOls: function(){
        var form = this;
        var data_form = form.MainPanel.getForm();
        var sDateOsl = Ext.util.Format.date(data_form.findField('date_osl').getValue(), 'd.m.Y');            
        var sOslId = data_form.findField('Osl').getValue();
        var sOsl = data_form.findField('Osl').getRawValue();

        var diag_combo = Ext.getCmp('Ds_osl');
        var dsEco = Ext.getCmp('Ds_osl').getValue(); 

        var indexStore = diag_combo.getStore().findBy(function(rec) { return rec.get('Diag_id') == dsEco; });
        var Store = diag_combo.getStore();
        var rec = Store.getAt(indexStore);

        if(typeof rec == 'object'){
            var sDs = rec.get('Diag_Code');
            var sDs1 = rec.get('Diag_Name');  
        }  

        var nRecord = Ext.getCmp('oslGrid').getGrid().store.getCount();

        if (!data_form.isValid()){
            Ext.Msg.alert('Ошибка', 'Заполните все обязательные поля!');
            return false;                
        }
				
		if (form.Action == 'Edit'){
			record = Ext.getCmp('oslGrid').getGrid().getSelectionModel().getSelected();
			console.log(record);
			//Ext.getCmp('oslGrid').getGrid().getStore().remove(record);
			
							//record.set('EcoOsl_id', record.EcoOsl_id);
							record.set('Date_osl', sDateOsl);
							record.set('Osl_id', sOslId);							
							record.set('Ds_int', dsEco);							
							record.set('Osl', sOsl);
							record.set('Ds', sDs+' '+sDs1);							
							record.commit();			
			
		}else{

			Ext.getCmp('oslGrid').getGrid().getStore().insert(nRecord, [new Ext.data.Record({
									//EcoOsl_id: form.EcoOsl_id,
									Date_osl: sDateOsl,
									Osl_id: sOslId,
									Osl: sOsl,                                     
									Ds: sDs+' '+sDs1,
									Ds_int: dsEco 
									})]);				
		}

        diag_combo.setValue();
        data_form.findField('Osl').setValue();
        data_form.findField('date_osl').setValue();
        Ext.getCmp('swECORegistryAddOsl').hide();

    },
    
    initComponent: function() {
        var wnd = this;

        this.MainPanel = new Ext.FormPanel({
            title: null,
            //bodySyle:'padding: 5px 5px 5px 5x;',
            bodySyle:'margin:5px;',
            layout: 'form',  
            id: 'mainPanelOsl',
            labelWidth: 170,
            labelAlign: 'right',
            region: 'center',
            items: [
                {
                    xtype: 'textfield',
                    id : 'pers_id',
                    name: 'Pers_id',
                    labelSeparator : ':',
                    hidden: true,
                    hideLabel: true,
                    fieldLabel: 'перс ид'
                },
                {
                    xtype: 'swdatefield',
                    id : 'date_osl',                                          
                    name: 'Date_osl',
                    width: this.m_width_date,
                    labelSeparator : ':',
                    fieldLabel: 'Дата осложнения',
                    allowBlank: false
                },
               {
                    fieldLabel: 'Осложнения',
                    xtype: 'swbaselocalcombo',
                    id : 'Osl',
                    name: 'osl',
                    width: this.m_width_big,
                    labelWidth: 190,
                    allowBlank: false,
                    editable: false,
                    listWidth: 550,
                    mode: 'local',
                    displayField: 'name',
                    valueField: 'id',
                    codeField:'selection_code',                   
                    triggerAction: 'all',
                    tpl: new Ext.XTemplate(
                            '<tpl for="."><div class="x-combo-list-item">',
                            '<font color="red">{selection_code}</font>&nbsp;{name}',
                            '</div></tpl>'
                    ),
                    store: new Ext.data.SimpleStore({
                        autoLoad: true,
                        fields: [{name: 'id', type: 'int'}, 
                                {name:'selection_code', type: 'int'},
                                {name: 'name', type: 'string'}],
                        key: 'id',
                        data: [
                            [1, 1, 'Синдром гиперстимуляции яичников (средней и более тяжелой степени) '], 
                            [2, 2, 'Осложнения пункции фолликулов (кровотечения)'], 
                            [3, 3, 'Осложнения пункции фолликулов (инфекции)'], 
                            [4, 4, 'Осложнения пункции фолликулов (другое)']]
                    })
                },
                {
                    fieldLabel: 'Диагноз осложнения',
                    name: 'ds_osl',
                    id : 'Ds_osl',
                    allowBlank: false,
                    width: this.m_width_big,
                    xtype: 'swdiagcombo'
                }
            ]
        });


        Ext.apply(this, {
            layout: 'border',
            buttons:
                [{
                    handler: function() {
                        this.ownerCt.onAddOls();					
                    },
                iconCls: 'ok16',
                    text: lang['sohranit']
                }, {
                    text: '-'
                },
               
                {
                    handler: function()  {
                        this.ownerCt.hide();
                    },
                    iconCls: 'cancel16',
                    text: BTN_FRMCANCEL
                }],
            items:[wnd.MainPanel]
        });
        sw.Promed.swECORegistryAddOsl.superclass.initComponent.apply(this, arguments);
    }	
});