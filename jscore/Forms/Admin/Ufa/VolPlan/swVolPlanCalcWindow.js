sw.Promed.swVolPlanCalcWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Свод фактических объёмов',
	layout: 'border',
	id: 'swVolPlanCalcWindow',
	modal: true,
	onHide: Ext.emptyFn,
	onSelect:  Ext.emptyFn,
	shim: false,
	width: 560,
        height: 140,
	resizable: true,
	maximizable: false,
	maximized: false,
        region: 'center',
	listeners:{
		hide:function () {
                    this.MainPanel.getForm().reset();
                    this.onHide();
		}
	},	
	show: function() {		
		sw.Promed.swVolPlanCalcWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		wnd.onHide = Ext.emptyFn;
//		if (arguments[0] && arguments[0].onHide) {
//			wnd.onHide = arguments[0].onHide;
//		}
//		if (arguments[0] && arguments[0].onSelect) {
//			wnd.onSelect = arguments[0].onSelect;
//			wnd.buttons[0].show();
//			wnd.mode = 'select';			
//		} else {
//			wnd.onSelect = Ext.emptyFn;
//			wnd.buttons[0].hide();
//			wnd.mode = 'view';
//		}

	},
        doSave:  function() {
            var wnd = this;
            var thispanel = wnd.MainPanel.getForm();
            var url_method = '';
            var vid_mp_id = Ext.getCmp('idVidMP1').getValue(); //thispanel.findField('idVidMP1').getValue();
            var vol_period_id = thispanel.findField('idVolPeriod').getValue();
            var loadMask = new Ext.LoadMask(wnd.getEl(), {msg: "Подождите, идет рассчет фактических объемов..."});
            loadMask.show();
            
            // проверим имеется ли свод фактических объемов за указанный период
            Ext.Ajax.request(
            {
                url: '/?c=VolPeriods&m=checkFact',
                params: 
                {
                    VolPeriod_id: vol_period_id,
                    VidMP_id: vid_mp_id
                },
                callback: function(options, success, response) 
                {
                    if (success) 
                    {
                        var result = Ext.util.JSON.decode(response.responseText);
                        // если имеется, то собирать не будем
                        if (result[0].Cnt > 0) 
                        {
                            loadMask.hide();
                            sw.swMsg.show(
                            {
                                title: lang['podtverjdenie_udaleniya'],
                                msg: 'Внимание! За выбранный период имеется свод фактических объемов. Желаете пересобрать?',
                                buttons: Ext.Msg.YESNO,
                                icon: Ext.Msg.WARNING,
                                fn: function(button) 
                                {
                                    if (button == 'yes') 
                                    {
                                        var tbl = '';
                                        switch (vid_mp_id)
                                        {
                                            case '1':
                                                tbl = 'FactVolStac';
                                            break;
                                            case '2':
                                                tbl = 'FactVolStacKSG';
                                            break;
                                            case '3':
                                                tbl = 'FactVolDS';
                                            break;
                                            case '4':
                                                tbl = 'FactVolDSKSG';
                                            break;
                                            case '5':
                                                tbl = 'FactVolVmp';
                                            break;
                                            case '6':
                                                tbl = 'FactVolAppNMP';
                                            break;
                                            case '7':
                                                tbl = 'FactVolLdi';
                                            break;
                                            case '8':
                                                tbl = 'FactVolDializ';
                                            break;
                                            case '9':
                                                tbl = 'FactVolEco';
                                            break;
                                            case '10':
                                                tbl = 'FactVolSmp';
                                            break;
                                            case '11':
                                                tbl = 'FactVolAppCons';
                                            break;
                                            case '12':
                                                tbl = 'FactVolAppProfNotAttach';
                                            break;
                                            case '13':
                                                tbl = 'FactVolAppTreatment';
                                            break;
                                            case '14':
                                                tbl = 'FactVolAppProf';
                                            break;
                                            case '15':
                                                tbl = 'FactVolAppProfDisp';
                                            break;
                                            case '16':
                                                tbl = 'FactVolAppProfAttach';
                                            break;
                                            case '17':
                                                tbl = 'FactVolAppProfCZ';
                                            break;
                                            case '18':
                                                tbl = 'FactVolAppProfAll';
                                            break;
                                        }
                                        Ext.Ajax.request(
                                        {
                                            url: '/?c=VolPeriods&m=deleteVolFact',
                                            params: 
                                            {
                                                VolPeriod_id: vol_period_id,
                                                Table: tbl
                                            },
                                            callback: function(options, success, response) 
                                            {
                                                if (success)
                                                {
                                                    switch (vid_mp_id) 
                                                    {
                                                        case '1':
                                                            url_method = '/?c=VolPeriods&m=collectFactsStac';
                                                        break;
                                                        case '2':
                                                            url_method = '/?c=VolPeriods&m=collectFactsStacKSG';
                                                        break;
                                                        case '3':
                                                            url_method = '/?c=VolPeriods&m=collectFactsDS';
                                                        break;
                                                        case '4':
                                                            url_method = '/?c=VolPeriods&m=collectFactsDSKSG';
                                                        break;
                                                        case '5':
                                                            url_method = '/?c=VolPeriods&m=collectFactsVMP';
                                                        break;
                                                        case '6':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppNmp';
                                                        break;
                                                        case '7':
                                                            url_method = '/?c=VolPeriods&m=collectFactsLdi';
                                                        break;
                                                        case '8':
                                                            url_method = '/?c=VolPeriods&m=collectFactsZpt';
                                                        break;
                                                        case '10':
                                                            url_method = '/?c=VolPeriods&m=collectFactsSMP';
                                                        break;
                                                        case '11':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppCons';
                                                        break;
                                                        case '12':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppProfNotAttach';
                                                        break;
                                                        case '13':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppTreatment';
                                                        break;
                                                        case '14':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppProf';
                                                        break;
                                                        case '15':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppProfDisp';
                                                        break;
                                                        case '16':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppProfAttach';
                                                        break;
                                                        case '17':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppProfCZ';
                                                        break;
                                                        case '18':
                                                            url_method = '/?c=VolPeriods&m=collectFactsAppProfAll';
                                                        break;
                                                    }
                                                    Ext.Ajax.request({
                                                        url: url_method,
                                                        params: {
                                                            VolPeriod_id: vol_period_id
                                                        },
                                                        success: function (response) {
                                                            loadMask.hide();
                                                            sw.swMsg.show( {
                                                                buttons: Ext.Msg.OK,
                                                                icon: Ext.Msg.INFO,
                                                                msg: 'Объемы по выбранному виду МП за указанный период собраны',
                                                                title: 'Сообщение'
                                                            });
                                                        },
                                                        failure: function(response) {
                                                            loadMask.hide();
                                                        }
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }
                        else
                        {
                            switch (vid_mp_id) 
                            {
                                case '1':
                                    url_method = '/?c=VolPeriods&m=collectFactsStac';
                                break;
                                case '2':
                                    url_method = '/?c=VolPeriods&m=collectFactsStacKSG';
                                break;
                                case '3':
                                    url_method = '/?c=VolPeriods&m=collectFactsDS';
                                break;
                                case '4':
                                    url_method = '/?c=VolPeriods&m=collectFactsDSKSG';
                                break;
                                case '5':
                                    url_method = '/?c=VolPeriods&m=collectFactsVMP';
                                break;
                                case '6':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppNmp';
                                break;
                                case '7':
                                    url_method = '/?c=VolPeriods&m=collectFactsLdi';
                                break;
                                case '8':
                                    url_method = '/?c=VolPeriods&m=collectFactsZpt';
                                break;
                                case '9':
                                    url_method = '/?c=VolPeriods&m=collectFactsEco';
                                break;
                                case '10':
                                    url_method = '/?c=VolPeriods&m=collectFactsSMP';
                                break;
                                case '11':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppCons';
                                break;
                                case '12':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppProfNotAttach';
                                break;
                                case '13':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppTreatment';
                                break;
                                case '14':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppProf';
                                break;
                                case '15':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppProfDisp';
                                break;
                                case '16':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppProfAttach';
                                break;
                                case '17':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppProfCZ';
                                break;
                                case '18':
                                    url_method = '/?c=VolPeriods&m=collectFactsAppProfAll';
                                break;
                            }
                            Ext.Ajax.request({
                                url: url_method,
                                params: {
                                    VolPeriod_id: vol_period_id
                                },
                                success: function (response) {
                                    loadMask.hide();
                                    sw.swMsg.show( {
                                        buttons: Ext.Msg.OK,
                                        icon: Ext.Msg.INFO,
                                        msg: 'Объемы по выбранному виду МП за указанный период собраны',
                                        title: 'Сообщение'
                                    });
                                },
                                failure: function(response) {
                                    loadMask.hide();
                                }
                            });
                        }
                    }
                }
            });
               
            //this.submit();
            return true;		
	},
	initComponent: function() {
		var wnd = this; 
                
                this.MainPanel = new Ext.FormPanel({
                        autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0',
			border: false,
			frame: true,
			region: 'center',
                        collapsible: true,
                        labelWidth: 50,
                        anchor: '-10',
                        layout: 'form',    
			height: 50,
			labelAlign: 'right',
                        items:[
                            {
                                xtype : 'swcombo',
                                id : 'idVidMP1',
                                mode: 'local',
                                typeCode: 'string',
                                orderBy: 'SprVidMp_id',
                                resizable: true,
                                editable: false,
                                displayField: 'SprVidMp_Name',
                                valueField: 'SprVidMp_id',
                                triggerAction: 'all', 
                                store: new Ext.data.Store({
                                    autoLoad: true,
                                    reader: new Ext.data.JsonReader({
                                            id: 'SprVidMp_id'
                                        },[
                                            { name: 'SprVidMp_id', mapping: 'SprVidMp_id' },
                                            { name: 'SprVidMp_Name', mapping: 'SprVidMp_Name' }
                                        ]),
                                    url:'/?c=VolPeriods&m=loadVidMPList'
                                }),
                                width : 470,
                                fieldLabel: 'Вид МП'
                            },
                            {
                                xtype : 'swcombo',
                                id : 'idVolPeriod',
                                mode: 'local',
                                typeCode: 'string',
                                orderBy: 'VolPeriod_id',
                                resizable: true,
                                editable: false,
                                displayField: 'VolPeriod_Name',
                                valueField: 'VolPeriod_id',
                                triggerAction: 'all',
                                store: new Ext.data.Store({
                                    autoLoad: true,
                                    reader: new Ext.data.JsonReader({
                                            id: 'VolPeriod_id'
                                        },[
                                            { name: 'VolPeriod_id', mapping: 'VolPeriod_id' },
                                            { name: 'VolPeriod_Name', mapping: 'VolPeriod_Name' }
                                        ]),
                                    url:'/?c=VolPeriods&m=loadVolPeriodList'
                                }),
                                width : 470,
                                fieldLabel: 'Период'
                            }                          
                        ]
                });

		
		Ext.apply(this, {
			layout: 'border',
			buttons:
                            [{
                                handler: function() 
				{
                                    
                                    this.ownerCt.doSave();
                                    
				},
                                iconCls: 'ok16',
                                text: 'Сформировать'
                            }, {
                                    text: '-'
                            },
                            HelpButton(this, 0),//todo проставить табиндексы
                            {
                                    handler: function()  {
                                            this.ownerCt.hide();
                                    },
                                    iconCls: 'cancel16',
                                    text: BTN_FRMCANCEL
                            }],
			//tbar: this.WindowToolbar,
			items:[{
				border: false,
				xtype: 'panel',
				region: 'center',
				layout: 'border',
				id: 'idMainPanel',
				items: [wnd.MainPanel]
			}]
		});
		sw.Promed.swVolPlanCalcWindow.superclass.initComponent.apply(this, arguments);
	}	
});