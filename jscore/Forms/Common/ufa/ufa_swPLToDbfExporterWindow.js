/**
 * Выгрузка результатов SQL-запросов в DBF
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @autor        Dmitry Storozhev aka nekto_O
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @version      16.12.2011
 */
sw.Promed.swPLToDbfExporterWindow = Ext.extend(sw.Promed.BaseForm, {
    title: "Выгрузка файдов 'P' и 'L'",
	    //lang['eksport_dannyih_v_dbase_*_dbf'],
    modal:true,
    height: 250,
    width:360,
//    shim:false,
//    plain:true,
    resizable:false,
//    onSelect:Ext.emptyFn,
    //layout:'fit',
    buttonAlign:"right",
    //objectName:'swPLToDbfExporterWindow',
    closeAction:'hide',
    id:'swPLToDbfExporterWindow',
    
    showLink: function (resp_obj){
        sw.swMsg.alert('Завершено', 'Экспорт успешно завершен. <a href="'+resp_obj.filename+'" target="blank" title="Щелкните, чтобы сохранить результаты на локальный диск">Скачать</a>');
    },
    
    initPeriodExp: function  () {
	var dt = new Date();
	var dt2 = new Date();
	dt = new Date(dt.format('Y'), dt.getMonth(), 1);
	dt2 = new Date(dt2.getFullYear(), dt2.getMonth() + 1, 0);
	//dt2.setUTCFullYear(dt2.format('Y') + 1);
	var dt_max = new Date(dt2.format('Y'), 11, 31); 
	Ext.getCmp('PLToDbf_PeriodExp').setValue(dt.format('d.m.Y') + ' - ' + dt2.format('d.m.Y'));
//	Ext.getCmp('PLToDbf_PeriodExp').setMinValue (  new Date( new Date().format('Y'), new Date().getMonth(), new Date().getDate() ) );
//	Ext.getCmp('PLToDbf_PeriodExp').setMaxValue (dt_max)
    },
    replaceAll: function(str, search, replace){
	return str.split(search).join(replace);
  },
    
    buttons:[
        {
            handler:function () {
		if (!Ext.getCmp('PLToDbf_FormPanel').form.isValid ()) {
		    //sw.Promed.vac.utils.msgBoxNoValidForm();
		    //b.setDisabled(false);
		    sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					//
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
		    return false;
			}	    
                var form = Ext.getCmp('swPLToDbfExporterWindow');
		var params = new Object();
		params.PeriodRange = Ext.getCmp('PLToDbf_PeriodExp').value;
		params.WhsDocumentCostItemType_id =  Ext.getCmp('DrugTurnover_WhsDocumentCostItemType').getValue();
		//params.path =  Ext.getCmp('DrugTurnover_WhsDocumentCostItemType').lastSelectionText.replace('.','_');
		
		params.path = '';
		switch (params.WhsDocumentCostItemType_id)
		{
		    case 1:
			params.path = 'ONLS';
			break;
		    case 2:
			params.path = 'RLO';
			break;
		    case 3:
			params.path = 'VZN';
			break;
		    case 34:
			params.path = 'SpecPit';
			break;
		    
		}
		
		params.path += "_" + form.replaceAll(params.PeriodRange, '.', '').replace(' - ', '_')
		// params.PeriodRange.replace('.', '').replace(' - ', 'по')
		console.log(params);
		if (1 == 1) { 
		    form.getLoadMask(lang['podgotovka_k_eksportu']).show();
                Ext.Ajax.request(
		    {
			params: params,
			callback: function (options, success, response){
			    if (success) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				console.log('response_obj');
				console.log(response_obj);
				if (response_obj.success){
				    form.getLoadMask().hide();
				    form.showLink(response_obj);
				} else {
				    var err = '';
				    if (response_obj.Error_Msg) {
					err = ' (' + response_obj.Error_Msg + ')';
				    }
				    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_arhivatsii'] + err);
				    form.getLoadMask().hide();
				}
			    } else {
				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_arhivatsii_nepravilnyiy_otvet_servera']);
				form.getLoadMask().hide();
			    }
			},
			url: '/?c=RegistryRecept&m=ufaExportPL2dbf'
		} )
	    }
            },
            iconCls:'ok16',
            text:lang['vyigruzit_v_dbf']
        },
        '-',
        {
            text:lang['zakryit'],
            tabIndex:-1,
            tooltip:lang['zakryit'],
            iconCls:'cancel16',
            handler:function () {
                this.ownerCt.hide();
            }
        }
    ],
    
    initComponent:function () {
        Ext.apply(this, {
            bodyStyle:'padding: 5px;',
	    labelWidth : 150,  
	    cls: 'tg-label',
	    layout : "form",
	    //frame: true,
            items:[
		new Ext.form.FormPanel({
		    frame: true,
		    height: 200,
		    id: 'PLToDbf_FormPanel',
		    items:[
		{
		    height : 10,
		    border : false
//		    frame: true

		}, 
		{
		    name : 'PLToDbf_PeriodExp',
		    id: 'PLToDbf_PeriodExp',
		    //style: 'margin: 10px;',
		    xtype : "daterangefield",
		    allowBlank: false,
		    width : 200,
		    fieldLabel : '   Период выгрузки',
		    plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
		    //tabIndex: TABINDEX_STARTVACFORMPLAN + 0
		},
		{
		    height : 10,
		    border : false
		}, 
		{
		    xtype: 'swwhsdocumentcostitemtypecombo',
		    allowBlank: false,
		    fieldLabel: lang['statya_rashoda'],
		    name: 'WhsDocumentCostItemType_id',
		    id: 'DrugTurnover_WhsDocumentCostItemType',
		    width: 200
		}
		     ]
		 })
            ]
        });
        sw.Promed.swPLToDbfExporterWindow.superclass.initComponent.apply(this, arguments);
    },
    
        show:function () {
        var form = this;
        sw.Promed.swPLToDbfExporterWindow.superclass.show.apply(this, arguments);
	
	Ext.getCmp('DrugTurnover_WhsDocumentCostItemType').getStore().load();
	form.initPeriodExp();
	
    },
});