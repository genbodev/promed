/***
swECORegistryAddUsl - добавление услуги случаю ЭКО
**/
sw.Promed.swECORegistryAddUsl = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Добавление услуги случаю с применением ВРТ',
    layout: 'border',
    id: 'swECORegistryAddUsl',
    modal: true,
    onHide: Ext.emptyFn,
    onSelect:  Ext.emptyFn,
    shim: false,
    width: 740,
    height: 130,
    resizable: false,
    maximizable: false,
    maximized: false,
    region: 'center',
    eco_usl_id: '',
    listeners:{
        hide:function () {
            this.onHide();
        }
    },
    
    show: function(params) {		
        sw.Promed.swECORegistryAddUsl.superclass.show.apply(this, arguments);
        var wnd = this;
		this.Date_add = params.Date_add;
		this.Res_date = params.Res_date;		
        wnd.onHide = Ext.emptyFn;
        if (typeof params.eco_usl_id == 'undefined'){
			Ext.getCmp('pers_id').setValue(params.pers_id);
			Ext.getCmp('date_usl').setValue((new Date()).format('d.m.Y'));
			Ext.getCmp('usl_comb').setValue();
		}else{
			wnd.eco_usl_id = params.eco_usl_id;
			Ext.getCmp('pers_id').setValue(params.pers_id);
			Ext.getCmp('date_usl').setValue(params.DateUslStr);			
			
			usluga_complex_id = params.usl_id;
			var combo = Ext.getCmp('usl_comb');
			combo.setValue(usluga_complex_id);
			
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
        
        if (params.oper==1){
            Ext.getCmp('usl_comb').setAllowedUslugaComplexAttributeList([ 'oper' ]);
			Ext.getCmp('usl_comb').setUslugaCategoryList(['gost2011']);
            wnd.setTitle('Выполнение операции: Добавление');
        } 
        else{
            Ext.getCmp('usl_comb').setAllowedUslugaComplexAttributeList([ 'lab','func','endoscop','laser','ray','inject','xray','registry']);
			Ext.getCmp('usl_comb').setUslugaCategoryList(['gost2011']);
            wnd.setTitle('Выполнение общей услуги: Добавление');
        };
		
        
    },
    
    addUsl: function(){
        var form = this;
        var data_form = form.MainPanel.getForm();
        var sDateUsl = Ext.util.Format.date(data_form.findField('date_usl').getValue(), 'd.m.Y');            
        var sUslId = data_form.findField('usl_comb').getValue();
        var sUsl = data_form.findField('usl_comb').getRawValue();
        var sUslCode = sUsl.substr(0,sUsl.indexOf(' '));
        sUsl = sUsl.substr(sUsl.indexOf(' ')+1,sUsl.length);
        
        
        if (!data_form.isValid()){
            Ext.Msg.alert('Ошибка', 'Заполните все обязательные поля!');
            return false;                
        }
		
		if (this.Date_add > data_form.findField('date_usl').getValue()){
            Ext.Msg.alert('Ошибка', 'Поле "Дата" не может быть ранее даты включения случая!');
            return false;
		}
		if (this.Res_date != "" && this.Res_date < data_form.findField('date_usl').getValue()){
            Ext.Msg.alert('Ошибка', 'Поле "Дата" не может превышать дату включения случая!');
            return false;
		}		
		
        var nRecord = Ext.getCmp('uslGrid').getGrid().store.getCount();
        Ext.getCmp('uslGrid').getGrid().getStore().insert(nRecord, [new Ext.data.Record({
                                usl_id: sUslId,
                                DateUslStr: sDateUsl,
                                CodeUsl: sUslCode,
                                NameUsl: sUsl
                                })]);
                            
        //form.hide();
        
        this.saveUsl();
    },
    
    saveUsl: function(){
        var pers_id = Ext.getCmp('pers_id').getValue();
        var date = Ext.getCmp('date_usl').getValue();
        var code_usl = Ext.getCmp('usl_comb').getValue();
        var name_usl = Ext.getCmp('usl_comb').getRawValue();
        var eco_usl_id = this.eco_usl_id;
		
        Ext.Ajax.request({ 
            url: '/?c=Eco&m=addEcoUsl', 
            params: { 
				EcoUsluga_id: eco_usl_id,
                persID: pers_id,
                dateUsl: date,
                codeUsl: code_usl,
                pmUser: getGlobalOptions().pmuser_id

            } 
        });
		Ext.getCmp("swECORegistryEditWindow").checkCrossing();
		Ext.getCmp("swECORegistryEditWindow").loadUslList();
        
		var form = Ext.getCmp('mainPanelUsl').getForm();
		form.reset();
		Ext.getCmp('swECORegistryAddUsl').hide();		
		
//        sw.swMsg.show(
//        {
//            buttons: Ext.Msg.OK,
//                fn: function()
//                {
//                    var form = Ext.getCmp('mainPanelUsl').getForm();
//                    form.reset();
//                    Ext.getCmp('swECORegistryAddUsl').hide();
//                },
//            icon: Ext.Msg.INFO,
//            msg: 'Услуга добавлена',
//            title: 'Успешно'
//        });
        
    },
    
    initComponent: function() {
        var wnd = this;

        this.MainPanel = new Ext.FormPanel({
            title: null,
            bodySyle:'margin:5px;',
            layout: 'form',  
            id: 'mainPanelUsl',
            labelWidth: 90,
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
                    id : 'date_usl',                                          
                    name: 'Date_usl',
                    width: 95,
                    labelSeparator : ':',
                    fieldLabel: 'Дата',
                    allowBlank: false
                },
                {
                    fieldLabel: 'Услуга',
                    id : 'usl_comb',
                    xtype: 'swuslugacomplexnewcombo',
                    name: 'Usl_comb',
                    width: 560,
                    listWidth: 600,
                    triggerAction: 'all',
                    allowBlank: false                    
                }
            ]
        });


        Ext.apply(this, {
            layout: 'border',
            buttons:
                [{
                    handler: function() {
                        this.ownerCt.addUsl();					
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
        sw.Promed.swECORegistryAddUsl.superclass.initComponent.apply(this, arguments);
    }	
});