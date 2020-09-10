sw.Promed.swVolRequestMakeWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Формирование заявки',
	layout: 'border',
	id: 'swVolRequestMakeWindow',
	modal: true,
	onHide: Ext.emptyFn,
	onSelect:  Ext.emptyFn,
	shim: false,
	width: 610,
        height: 170,
	resizable: true,
	maximizable: false,
	maximized: false,
        region: 'center',
	listeners:{
		hide:function () {
                        this.onHide();
                        this.hide();
                        this.close();
                        window[this.objectName] = null;
                        delete sw.Promed[this.objectName];
                        //this.destroy();
		}
	},	
//        kill: function()
//        {
//            
//        },
	show: function() {		
		sw.Promed.swVolRequestMakeWindow.superclass.show.apply(this, arguments);
		var wnd = this;
//		wnd.onHide = this.kill();
                
                var panel = Ext.getCmp('drpeVolRequestMakeForm').getForm();
                panel.reset();
                
                
                
                
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
            
                		
	},
        buildRequest: function() {
            var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет создание заявки..."});
            loadMask.show();
            

            var plan_year = Ext.getCmp('idYear').getValue();
            var vid_mp_id = Ext.getCmp('idVidMpCBX').getValue();
            //var vol_period_id = Ext.getCmp('idUseFacts').getValue();
            var max_prc = Ext.getCmp('max_prc').getValue();
            
            Ext.Ajax.request({
                    timeout: 1800000, //30 mins
                    url: '/?c=VolPeriods&m=buildRequest',
                    params: {
                            //VolPeriod_id: vol_period_id,
                            Year: plan_year,
                            VidMp: vid_mp_id,
                            Prc: max_prc
                    },
                    success: function (response) {
                            Ext.getCmp('swVolRequestViewWindow').doSearch();
                            sw.swMsg.show( {
                                    buttons: Ext.Msg.OK,
                                    icon: Ext.Msg.INFO,
                                    msg: 'Заявка сформирована',
                                    title: 'Сообщение'
                            });
                            Ext.getCmp('swVolRequestMakeWindow').hide();
                            loadMask.hide();
                            Ext.getCmp('idVolRequestsGrid').getGrid().getStore().reload();
                    }
            });
        },
	initComponent: function() {
		var wnd = this; 
            
                this.Inputs = new Ext.Panel({
//                    id: 'idFormPanel',
//                    autoScroll: true,
//                    bodyBorder: false,
//                    bodyStyle: 'padding: 0',
//                    border: false,
//                    frame: true,
//                    region: 'center',
//                    collapsible: true,
//                    labelWidth: 50,
//                    anchor: '-10',
//                    layout: 'form',    
//                    height: 50,
//                    labelAlign: 'right',
                    
                    
                    
			bodyBorder: false,
                        id: 'idFormPanel',
                        layout: 'form',  
			//bodyStyle: 'padding: 0',
			border: true,
			frame: true,
                        region: 'center',
                        //height: 100,
                        //autoHeight: true,
			labelAlign: 'right',
                        items: [{
				xtype: 'form',
				id: 'drpeVolRequestMakeForm',
				style: '',
				bodyStyle:'background:#DFE8F6;padding:4px;',
				border: true,
				labelWidth: 170,
				collapsible: true,
				items:[{
					name: 'VolPeriod_id',
					xtype: 'hidden',
					value: 0
                                        }, 
                                        {
                                            fieldLabel: 'Год планирования',
                                            id: 'idYear',
                                            name: 'plan_year',
                                            width: 40,
                                            xtype: 'textfield',
                                            plugins: [new Ext.ux.InputTextMask('9999',false)],
                                            value: (new Date().getFullYear() + 1)
                                    },
                                    {
                                        xtype : 'swcombo',
                                        id : 'idVidMpCBX',
                                        mode: 'local',
                                        typeCode: 'string',
                                        orderBy: 'SprVidMp_id',
                                        resizable: true,
                                        editable: false,
                                        allowBlank: false,
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
                                        width : 395,
                                        fieldLabel: 'Вид МП'
                                    },
                                    {
                                            fieldLabel: 'Максимальный % отклонения',
                                            id: 'max_prc',
                                            name: 'percent',
                                            width: 40,
                                            xtype: 'numberfield',
                                            //plugins: [new Ext.ux.InputTextMask('9999',false)],
                                            value: 0
                                    }
                                ]
			}
                ]
                });

		
		Ext.apply(this, {
			layout: 'border',
			buttons:
                            [{
                                handler: function() 
				{
                                    this.ownerCt.doLayout();
					this.ownerCt.buildRequest();
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
				items: [wnd.Inputs]
			}]
		});
		sw.Promed.swVolRequestMakeWindow.superclass.initComponent.apply(this, arguments);
	}	
});

