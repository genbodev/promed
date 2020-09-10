/**
* swDrugRequestPeriodEditWindow - окно редактирования "Справочник медикаментов: период заявки"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Rustam Salakhov
* @version      07.2012
* @comment      
*/
sw.Promed.swVolWarningWnd = Ext.extend(sw.Promed.BaseForm, {
	title: 'ВНИМАНИЕ!',
	layout: 'fit',
	id: 'idVolWarningWnd',
	modal: true,
	shim: false,
	width: 550,
	height: 350,
	resizable: false,
	maximizable: false,
	maximized: false,
	listeners: {
            hide: function() {
                this.onHide();
            }
	},
	onHide: Ext.emptyFn,
	show: function() {
            var wnd = this;
            sw.Promed.swVolWarningWnd.superclass.show.apply(this, arguments);
            this.action = '';
            this.callback = Ext.emptyFn;
            
            
            this.VolPeriod_id = 0;
            if ( !arguments[0] ) {
                sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
            }
            //form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
            Ext.getCmp('idWarningLbl').getEl().dom.innerHTML = arguments[0].text;
            //this.form.getEl().dom.innerHTML = arguments[0].text;
            //Ext.getCmp('idWarningLbl').setText(arguments[0].text);
            if ( arguments[0].action ) {
                    this.action = arguments[0].action;
            }
            if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
                    this.callback = arguments[0].callback;
            }
            if ( arguments[0].owner ) {
                    this.owner = arguments[0].owner;
            }
            if ( arguments[0].VolPeriod_id ) {
                    this.VolPeriod_id = arguments[0].VolPeriod_id;
            }
            this.DoLayout;
	},
	initComponent: function() {
		var wnd = this;
                
		var form = new Ext.Panel({
                        id: 'idWarningBody',
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: true,
			region: 'center',
			height: 150,
			labelAlign: 'right',
			items: [
                                { 
                                    xtype: 'panel', 
                                    html: '111',
                                    name: 'warningText', 
                                    id: 'idWarningLbl',
                                }
//                            { 
//                                xtype: 'label', 
//                                text: '',
//                                name: 'labelmo', 
//                                id: 'idWarningLbl',
//                                style: 'font-size: 12px;font-weight:normal;margin: 2px 2px 2px 0px;'
//                            }
                        ],
		});

		Ext.apply(this, {
			layout: 'border',
			buttons:
			[
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() 
				{
                                    this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				form
			]
		});
		sw.Promed.swVolWarningWnd.superclass.initComponent.apply(this, arguments);
	}	
});