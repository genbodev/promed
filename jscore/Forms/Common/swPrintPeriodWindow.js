/**
* swPrintPeriodWindow - окно выбора периода печати
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Kurakin A
* @version      12.2016
* @comment      
*/
sw.Promed.swPrintPeriodWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Выбор периода',
	layout: 'border',
	id: 'swPrintPeriodWindow',
	modal: true,
	shim: false,
	width: 400,
	height: 130,
	resizable: false,
	maximizable: false,
	doPrint: function() {
		var begDate = this.form.findField('Period_range').getValue1();
		if(begDate){
			begDate = Ext.util.Format.date(begDate,'d.m.Y');
		}
		var endDate = this.form.findField('Period_range').getValue2();
		if(endDate){
			endDate = Ext.util.Format.date(endDate,'d.m.Y');
		}
		var params = {
			paramEvn: this.paramEvn,
			paramBegDate: begDate,
			paramEndDate: endDate
		};
		this.callback(params);
		this.hide();
	},
	show: function() {
        var wnd = this;
		sw.Promed.swPrintPeriodWindow.superclass.show.apply(this, arguments);		
		this.callback = Ext.emptyFn;
		this.paramEvn = null;
        if ( !arguments[0]  ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
        if ( !arguments[0].paramEvn  ) {
            sw.swMsg.alert('Ошибка', 'Не указан идентификатор движения/посещения', function() { wnd.hide(); });
            return false;
        }
		
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].paramEvn ) {
			this.paramEvn = arguments[0].paramEvn;
		}
		this.form.reset();
		var date = new Date();
		var val2 = Ext.util.Format.date(date,'d.m.Y');
		Ext.Ajax.request({
            params: {
                Evn_id: wnd.paramEvn
            },
            url: '/?c=Evn&m=getEvnData',
            callback: function(options, success, response) {
            	var val1 = '';
                if (response.responseText != '') {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj && response_obj[0] && response_obj[0].Evn_setDate) {
                    	val1 = response_obj[0].Evn_setDate;
                    }
                }
                wnd.form.findField('Period_range').setValue(val1+' - '+val2);
            }
        });
	},
	initComponent: function() {
		var wnd = this;		
		
		var form = new Ext.Panel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			height: 70,
			border: false,			
			frame: true,
			region: 'center',
			labelAlign: 'right',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'PrintPeriodForm',
				style: 'margin-bottom: 0.5em;',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: true,
				labelWidth: 80,
				collapsible: true,
				items: [{
					fieldLabel: lang['period'],
					allowBlank: false,
					xtype: 'daterangefield',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ],
					width: 180,
					name: 'Period_range'
				}]
			}]
		});
		Ext.apply(this, {
			layout: 'border',
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doPrint();
				},
				iconCls: 'print16',
				text: BTN_FRMPRINT
			}, 
			{
				text: '-'
			},
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[form]
		});
		sw.Promed.swPrintPeriodWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('PrintPeriodForm').getForm();
	}	
});