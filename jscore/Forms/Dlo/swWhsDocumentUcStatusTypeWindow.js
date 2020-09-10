/**
* swLpuOperEnvWindow - Оперативная обстановка по выбранному ЛПУ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Vasinsky Igor, update Alexander Kurakin
* @version      09.06.2016
*/

sw.Promed.swWhsDocumentUcStatusTypeWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'swWhsDocumentUcStatusTypeWindow',
	modal: true,
	title: lang['izmeneniya_statusa_lota'], 
    buttonAlign: 'right',
	width: 500,
    bodyStyle: 'padding:10px;',
	autoHeight: true,
	onCancel: Ext.emptyFn,
	callback: Ext.emptyFn,
	onSelect: Ext.emptyFn,
	closeAction:'destroy',
	show: function() {
		sw.Promed.swWhsDocumentUcStatusTypeWindow.superclass.show.apply(this, arguments);

        if( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
            this.hide();
            return false;
        }

        if( !arguments[0].WhsDocumentUc_id ) {
            sw.swMsg.alert(lang['oshibka'], 'Не выбран лот');
            this.hide();
            return false;
        }

        this.WhsDocumentUc_id = arguments[0].WhsDocumentUc_id;
        if(arguments[0].callback){
            this.callback = arguments[0].callback;
        }
        
        var WhsDocumentUc_Num = typeof(arguments[0].WhsDocumentUc_Num) != 'undefined' ? arguments[0].WhsDocumentUc_Num : false;
        var combo = this.WhsDocumentUcStatusTypeCombo;
        var WhsDocumentUcStatusType_id = '';
        if(arguments[0].WhsDocumentUcStatusType_id){
            WhsDocumentUcStatusType_id = arguments[0].WhsDocumentUcStatusType_id;
        }
        combo.getStore().removeAll();
        combo.getStore().load({
            callback:function(){
                combo.setValue(WhsDocumentUcStatusType_id);
            }
        });
        this.WhsDocumentUcStatusTypeCombo.setFieldLabel(lang['lot_№'] +WhsDocumentUc_Num);
        
	},
    listeners : {
      'render' : function(){
        
      },
	  'hide': function() 
	  {

         main_menu_panel.setDisabled(false);

		  if (this.refresh)
			  this.onHide();
	  }
    },
    onEsc : function(){
        this.hide();
    },	
	refresh : function(){     
		sw.codeInfo.lastObjectName = this.objectName;
		sw.codeInfo.lastObjectClass = this.objectClass;
		if (sw.Promed.Actions.loadLastObjectCode)
		{
			sw.Promed.Actions.loadLastObjectCode.setHidden(false);
			sw.Promed.Actions.loadLastObjectCode.setText(lang['obnovit']+this.objectName+' ...');
		}
		// Удаляем полностью объект из DOM, функционал которого хотим обновить
		this.hide();
		this.close();
		window[this.objectName] = null;
		//delete sw.Promed[this.objectName];        
	}, 
    doSave: function(){
        var win = this;
        var WhsDocumentUcStatusType_id = this.WhsDocumentUcStatusTypeCombo.getValue();
        if( !(WhsDocumentUcStatusType_id > 0) ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
            return false;
        }
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет смена статуса..."});
        loadMask.show();
        Ext.Ajax.request({
            url: '/?c=Gku&m=changeWhsDocumentUcStatusType',
            params: {
                WhsDocumentUc_id: win.WhsDocumentUc_id,
                WhsDocumentUcStatusType_id: WhsDocumentUcStatusType_id 
            },
            callback: function(options, success, response) {
                loadMask.hide();
                if (success === true) {   
                    win.callback();   
                    win.hide();
                }
            }
        });
    },    
	initComponent: function() {
        
        this.WhsDocumentUcStatusTypeCombo = new Ext.form.ComboBox({
            id: 'WhsDocumentUcStatusTypeCombo',
            allowBlank: false,
            width: 350,
            mode: 'local',
            labelSeparator : ':',
            labelStyle: 'width:100px; padding:0px',
            fieldLabel: lang['lot_№'] + Ext.getCmp('swWhsDocumentUcStatusTypeWindow').WhsDocumentUc_Num,
        	forceSelection: true,
        	triggerAction: 'all',			
        	editable:false,
        	displayField:'WhsDocumentUcStatusType_Name',
            valueField: 'WhsDocumentUcStatusType_id',
            hiddenName: 'WhsDocumentUcStatusType_id',
            store : new Ext.data.JsonStore({
        		url:'/?c=Gku&m=getWhsDocumentUcStatusType',
                autoLoad: false,			
        		fields: ['WhsDocumentUcStatusType_id','WhsDocumentUcStatusType_Name'],
                listeners : {
                    'load' : function(){}
                }
            })          	
        });    
               
		Ext.apply(this, {
            buttons: [{
                handler: this.doSave,
                scope: this,
                iconCls: 'save16',
                text: lang['sohranit']
            },
            '-',
            HelpButton(this),
            {
                text: lang['otmena'],
                tabIndex: -1,
                tooltip: lang['otmena'],
                iconCls: 'cancel16',
                handler : function(){
                    Ext.getCmp('WhsDocumentUcStatusTypeCombo').setValue('');
                    Ext.getCmp('swWhsDocumentUcStatusTypeWindow').hide();
                }
            }],            	  
			items: [
		      this.WhsDocumentUcStatusTypeCombo
			]
		});
		sw.Promed.swWhsDocumentUcStatusTypeWindow.superclass.initComponent.apply(this, arguments);
	}
});