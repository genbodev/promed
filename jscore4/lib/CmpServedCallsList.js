//Форма Обслуженные вызововы

Ext.define('sw.CmpServedCallsList',{
    extend: 'Ext.grid.Panel',
    alias: 'widget.CmpServedCallsList',
    cls: 'CmpServedCallsList',
	refId: 'CmpServedCallsList',
    flex: 1,
    viewConfig: {
        loadMask: true,
        loadingText: 'Загрузка..',
        preserveScrollOnRefresh: true
    },
    columns: [
        {
            dataIndex: 'CmpCallCard_id',
            text: 'ИД карты вызова',
            hidden: true
        },
        {
            dataIndex: 'CmpCallCard_prmDate',
            text: 'Дата и время',
            width: 120,
            xtype:'datecolumn',
            format: 'd.m.Y H:i:s',
			filter: {xtype: 'datefield',
				format: 'd.m.Y',
				allowBlank: true,
				translate: false,
				filterName:'CmpCallCard_prmDateStr',
				onTriggerClick: function() {
					var dt1 = this;
					Ext.form.DateField.prototype.onTriggerClick.apply(this, arguments);

					if(!this.clearBtn){
						this.clearBtn = new Ext.Component({
							autoEl: {
								tag: 'div',
								cls: 'clearDatefieldsButton',
							},
							listeners: {
								el: {
									click: function() {
										dt1.reset();
									}
								}
							}
						});
					}
					//dt1.clearBtn.render(dt1.bodyEl);
					//dt1.bodyEl.addCls('inputClearDatefieldsButton');
				},
				listeners: {

				}
			}
			//filter: {xtype: 'transFieldDelbut', translate: false}
        },
        {
            dataIndex: 'CmpCallCard_Numv',
            text: '№ В/Д',
            width: 60,
            filter: {xtype: 'textfield'}
        },
        {
            dataIndex: 'CmpCallCard_Ngod',
            text: '№ В/Г',
            width: 60,
            filter: {xtype: 'textfield'}
        },
        {
            dataIndex: 'Person_FIO',
            text: 'Пациент',
            width: 180,
            filter: {xtype: 'transFieldDelbut', translate: false}
        },
		{
			dataIndex: 'Person_Birthday',
			xtype: 'datecolumn',
			sortable: true,
			text: 'Возраст',
			width: 55,
			renderer:function(birthday){
				var result,
					now = new Date();
				if (Ext.isEmpty(birthday)) {
					result = '';
				} else {
					var years = swGetPersonAge(birthday, now);

					if (years > 0) {
						result = years + ' лет.';
					} else {
						var days = Math.floor(Math.abs((now - birthday)/(1000 * 3600 * 24))),
							months = Math.floor(Math.abs(now.getMonthsBetween(birthday)));

						if (months > 0) {
							result = months + ' мес.';
						} else {
							result = days + ' дн.';
						}
					}
				}

				return result;
			},
			//format: 'd.m.Y',
			filter: {
				xtype: 'transFieldDelbut',
				translate: false,
				filterMap: 'personAgeText'
			}
		},
        {
            dataIndex: 'Adress_Name',
            text: 'Адрес',
            width: 180,
            filter: {xtype: 'textfield'}
        },
        {
            dataIndex: 'CmpCallType_Name',
            text: 'Тип вызова',
            width: 150,
            filter: {xtype: 'swCmpCallTypeCombo',listeners:{render:function(cmp){cmp.store = Ext.getCmp('CmpCallTypeComboServed').store}}}
        },
        {
            dataIndex: 'CmpCallCard_IsExtraText',
            text: 'Вид вызова',
            width: 150,
             filter: {xtype: 'swCmpCallTypeIsExtraCombo'}
        },
        {
            dataIndex: 'CmpReason_Name',
            text: 'Повод',
            width: 150,
            filter: {xtype: 'cmpReasonCombo',listeners:{render:function(cmp){cmp.store = Ext.getCmp('cmpReasonComboServed').store}}}
        },
        {
            dataIndex: 'CmpCallCardStatusType_Name',
            text: 'Статус вызова',
            width: 120,
			filter: {xtype: 'swCmpCallCardStatusTypeCombo', listeners:{render:function(cmp){cmp.store = Ext.getCmp('CmpCallCardStatusTypeComboServed').store}}}
//			filter: {xtype: 'swCmpCallCardStatusTypeCombo',listeners:{render:function(cmp){cmp.store = 
//				Ext.create('Ext.data.Store', {
//					fields: ['swCmpCallCardStatusTypeCombo_Name'],
//					data: [
//						{
//						'swCmpCallCardStatusTypeCombo_Name': 'Ожидание'
//						}
//					]
//				})				 
//			}}}
        },
        {
            dataIndex: 'CmpCallCard_Comm',
            text: 'Доп. информация',
            width: 170,
            filter: {xtype: 'textfield'}
        },
        {
            dataIndex: 'CmpCallCard_IsExtra',
            text: 'СМП / НМП',
            width: 70,
            filter: {xtype: 'textfield'}
        },
        {
            dataIndex: 'Diag',
            text: 'Диагноз',
            width: 130,
            filter: {xtype: 'textfield'}
        },
        {
            dataIndex: 'LpuBuilding_Name',
            text: 'Подразделение СМП',
            width: 130,
            filter: {xtype: 'smpUnitsNestedCombo',displayTpl: '<tpl for=".">{LpuBuilding_fullName}</tpl>'}
        },
        {
            dataIndex: 'EmergencyTeam_Num',
            text: 'Бригада',
            width: 70,
            filter: {xtype: 'textfield'}
        },
		{
           dataIndex: '',
			renderer: function(v, p, r) {
				//return "<a href='javascript:Ext.getCmp(\"CmpServedCallsList\").showCmpCallCard(" + r.get('CmpCallCard_id') + ");'>Закрыть</a>";
				return "<a href='javascript:Ext.ComponentQuery.query(\"grid[refId=CmpServedCallsList]\")[0].showCmpCallCard(" + r.get('CmpCallCard_id') + "," + r.get('CmpCloseCard_id') + ");'>Закрыть</a>";
			},
			text: '',
			width: 100
        }
    ],
    requires: [
        'Ext.ux.GridHeaderFilters'
    ],
    plugins: [Ext.create('Ext.ux.GridHeaderFilters',{enableTooltip: false,reloadOnChange:true})],
	showCmpCallCard: function(card_id, closecard_id){
        var action = closecard_id ? 'edit' : 'add',
            title = (action == 'edit') ? 'Карта вызова: Редактирование' : 'Карта вызова: Закрытие';

		new Ext.Window({
			id: "myFFFrameServed",
			title: title,
			header: false,
			extend: 'sw.standartToolsWindow',
			toFrontOnShow: true,
			//width : '100%',
			//modal: true,
			style: {
				'z-index': 90000
			},
			//height: '90%',
			//layout : 'fit',
			layout: {
				type: 'fit',
				align: 'stretch'
			},
			maximized: true,
			constrain: true,
			renderTo: Ext.getCmp('inPanel').body,
			items : [{
				xtype : "component",
				autoEl : {
					tag : "iframe",
					src : "/?c=promed&getwnd=swCmpCallCardNewCloseCardWindow&act=" + action + "&showTop=1&cccid="+card_id
				}
			}]
		}).show();
		
	},
    initComponent: function(){
        var me = this;

        Ext.define('CmpServedCallsListModel', {   
            extend: 'Ext.data.Model',
            idProperty: 'CmpCallCard_id',
            fields: [
                {
                    name: 'CmpCallCard_id',
                    type: 'int'
                },
                {
                    name: 'CmpCloseCard_id',
                    type: 'int'
                },
                {
                    name: 'CmpCallCard_prmDate',
                    //type: 'string',
                    type: 'date',
                    convert : function(dt) {
                        return new Date(dt.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
                    }
                },
				{
					name: 'CmpCallCard_prmDateStr',
					type: 'string'
				},
                {
                    name: 'CmpCallCard_Numv',
                    type: 'int'
                },
                {
                    name: 'CmpCallCard_Ngod',
                    type: 'int'
                },
                {
                    name: 'Person_FIO',
                    type: 'string'
                },
                {
                    name: 'personAgeText',
                    type: 'string'
                },
				{
					name: 'Person_Birthday',
					type: 'date'
				},
                {
                    name: 'Adress_Name',
                    type: 'string'
                },
                {
                    name: 'CmpCallType_Name',
                    type: 'string'
                },
                {
                    name: 'CmpCallCard_IsExtraText',
                    type: 'string'
                },
                {
                    name: 'CmpReason_Name',
                    type: 'string'
                },
                {
                    name: 'CmpCallCardStatusType_id',
                    type: 'int'
                },
                {
                    name: 'CmpCallCardStatusType_Name',
                    type: 'string'
                },
                {
                    name: 'CmpCallCard_Comm',
                    type: 'string'
                },
                {
                    name: 'CmpCallCard_IsExtra',
                    type: 'string'
                },
                {
                    name: 'Diag',
                    type: 'string'
                },
                {
                    name: 'LpuBuilding_Name',
                    type: 'string'
                },
                {
                    name: 'EmergencyTeam_Num',
                    type: 'string'
                },
                {
                    name: 'CmpCallRecord_id',
                    type: 'int'
                }

            ]
        });

        me.store = Ext.create('Ext.data.Store', {
			storeId: me.id + '_DispatcherCallWP_CmpServedCallsListStore',
			model: 'CmpServedCallsListModel',
			autoLoad: false,
			stripeRows: true,
			numLoad: 0,
			pageSize: 100,
			sorters: [
				// {
				//     sorterFn: function(o1, o2){
				//         var CmpCallCard_prmDate1 = o1.get('CmpCallCard_prmDate'),
				//             CmpCallCard_prmDate2 = o2.get('CmpCallCard_prmDate');

				//         return CmpCallCard_prmDate1 > CmpCallCard_prmDate2 ? -1 : 1;
				//     }
				// },
				{
					property: 'CmpCallCard_prmDate',
					direction: 'DESC'
				}
			],
			proxy: {
				type: 'ajax',
				url: '/?c=CmpCallCard4E&m=loadDispatcherCallsServedList',
				reader: {
					type: 'json',
					successProperty: 'success',
					totalProperty: 'totalCount',
					root: 'data'
				},
				//limitParam: undefined,
				//startParam: undefined,
				//paramName: undefined,
				//pageParam: undefined,

				actionMethods: {
					create: 'POST',
					read: 'POST',
					update: 'POST',
					destroy: 'POST'
				}
			},
			listeners: {
				load: function (store, request) {
					var	originiTitle = me.up('container').initialConfig.title,
						tabb = me.up('container').tab;

					if(store.getCount() > 0){
						tabb.setText(originiTitle + " ("+store.getCount()+")")
					}else{
						tabb.setText(originiTitle);
					}
				}
			}
        });

        me.bbar = Ext.create('Ext.PagingToolbar', {
            store: me.store,
            displayInfo: true,
            pageSize: 100,
            beforePageText: 'Страница',
            afterPageText: 'из {0}',
            displayMsg: 'показано {0} - {1} из {2}'
        });

		var streetsCombo = Ext.create('sw.streetsSpeedCombo', {
			mainAddressField: true,
			name:'dStreetsCombo',
			fieldLabel: 'Улица',
            width: 222,
            labelWidth: 77,
			labelAlign: 'right',
			listConfig: {minWidth: 800, width: 800},
			defaultListConfig: {minWidth: 800, width: 800},
			forceSelection: (!getRegionNick().inlist(['krym'])),
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-boundlist-item">'+
					'{[ this.addressObj(values) ]} '+
				'</div></tpl>',
				{
					addressObj: function(val){						
						var city = val.Address_Name+' ';
						
						if(val.UnformalizedAddressDirectory_id){
							return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
						}else{
							return val.AddressOfTheObject +', ' + val.StreetAndUnformalizedAddressDirectory_Name + ' <span style="color:gray">' + val.Socr_Nick +'</span>';
						}
					}
				}
			),
			displayTpl: new Ext.XTemplate(
				'<tpl for=".">' +
					'{[ this.getDateFinish(values) ]} ',
					'<tpl if="xindex < xcount">' + me.delimiter + '</tpl>' +
				'</tpl>',
				{
					getDateFinish: function(val){
						if (val.UnformalizedAddressDirectory_id){							
							return val.AddressOfTheObject + ', ' + val.StreetAndUnformalizedAddressDirectory_Name;
						}
						else{
							return val.Socr_Nick + " " + val.StreetAndUnformalizedAddressDirectory_Name;
						}
					}
				}	
			),
			listeners: {}
		});

        me.tbar = {
                xtype: 'BaseForm',
                id: me.id+'_CmpServedCallsListFilterForm',
                dock: 'top',
                items: [{
                xtype: 'container',
                items: [
                    {
                        xtype: 'toolbar',
                        layout: {
                            type: 'hbox',
                            align: 'stretch'
                        },
                        margin: '5',
                        border: false,
                        items: [
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'left'
                                },
                                defaults:{
                                    labelAlign: 'right',
                                    width: 200,
                                    labelWidth: 80
                                },
                                items: [
                                    {
                                        xtype: 'swdatefield',
                                        fieldLabel: 'Дата c',
                                        name: 'begDate',
                                        allowBlank: false
                                    },
									{
										xtype: 'datefield',
										fieldLabel: 'Время c',
										format: 'H:i',
										hideTrigger: true,
										invalidText: 'Неправильный формат времени. Время должно быть указано в формате ЧЧ:ММ',
										plugins: [new Ux.InputTextMask('99:99')],
										name: 'begTime',
										allowBlank: true
									},
                                    {
                                        xtype: 'swdatefield',
                                        fieldLabel: 'Дата по',
                                        name: 'endDate',
                                        allowBlank: false
                                    },
                                    {
                                        xtype: 'datefield',
                                        fieldLabel: 'Время по',
                                        format: 'H:i',
                                        hideTrigger: true,
                                        invalidText: 'Неправильный формат времени. Время должно быть указано в формате ЧЧ:ММ',
                                        plugins: [new Ux.InputTextMask('99:99')],
                                        name: 'endTime',
										allowBlank: true
                                    },{
                                        xtype: 'cmpReasonCombo',
                                        id: 'cmpReasonComboServed',
                                        name: 'CmpReason',
										hidden: true
                                    },
                                    {
                                        xtype: 'swCmpCallTypeCombo',
                                        name: 'CmpCallType',
                                        id: 'CmpCallTypeComboServed',
										hidden: true
                                    },
                                    {
                                        xtype: 'swCmpCallCardStatusTypeCombo',
                                        name: 'CmpCallType',
                                        id: 'CmpCallCardStatusTypeComboServed',
										hidden: true
                                    },
									{
                                        xtype: 'button',
                                        refId: 'searchBtn',
                                        iconCls: 'search16',
                                        text: 'Найти',
                                        width: 70,
                                        margin: '0 10',
                                        handler: function(){
                                            me.searchCmpCalls()
                                        }
                                    },
									{
                                        xtype: 'button',
                                        refId: 'resetBtn',
                                        iconCls: 'reset16',
                                        width: 70,
                                        text: 'Сброс',
                                        margin: '0',
                                        handler: function(){
											var baseForm = me.down('BaseForm').getForm();
											
											Ext.Ajax.request({
												url: '/?c=CmpCallCard&m=getDatesToNumbersDayYear',
												callback: function (opt, success, response) {
													var datesParams = Ext.JSON.decode(response.responseText);

													if(datesParams.data){
														var Dt_from = Ext.Date.parse(datesParams.data.startDateTime,'Y-m-d H:i:s'),
															Dt_to = Ext.Date.parse(datesParams.data.endDateTime,'Y-m-d H:i:s');

														baseForm.findField('endDate').setValue(Ext.Date.format(Dt_to,'d.m.Y'));
														baseForm.findField('endTime').setValue(Ext.Date.format(Dt_to,'H:i'));
														baseForm.findField('begDate').setValue(Ext.Date.format(Dt_from,'d.m.Y'));
														baseForm.findField('begTime').setValue(Ext.Date.format(Dt_from,'H:i'));
													}
													me.searchCmpCalls()
												}
											});

                                        }
                                    }
                                ]
                            },
                            '->',
                            {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'right'
                                },
                                items: [
                                    {
                                        xtype: 'splitbutton',
                                        iconCls: 'print16',
                                        padding: 3,
                                        text: 'Печать',
                                        menu: {
                                            xtype: 'menu',
                                            items: [
                                                {
                                                    xtype: 'menuitem',
                                                    iconCls: 'print16',
                                                    text: 'Печать',
                                                    handler: function () {
                                                        Ext.ux.grid.Printer.print(me)
                                                    }
                                                },
                                                {
                                                    xtype: 'menuitem',
                                                    iconCls: 'print16',
                                                    text: 'Печать всего списка',
                                                    handler: function () {
                                                        var params = me.store.proxy.extraParams,
                                                            strParams = '';
                                                        if(!params)
                                                            return;
                                                        for(var param in params){
                                                            if (params.hasOwnProperty(param) && !Ext.isEmpty(params[param])) {
                                                                strParams += '&' + param + '=' + params[param];
                                                            }
                                                        }
                                                        var location = '/?c=CmpCallCard4E&m=printCmpCallsList' + strParams;
                                                        var win = window.open(location);

                                                    }
                                                }]
                                        },
                                        listeners: {
                                            click: function(){
                                                this.showMenu();
                                            }
                                        }
                                    }
                                ]
                            }

                        ]
                    }
                    ]
            }
            ]
        };

        me.dockedItems = [
            {
                xtype: 'toolbar',
                margin: '0 0 20 0',
                dock: 'bottom',
                items: [
                    '->',
                    {
                        xtype: 'button',
                        text: 'Помощь',
                        iconCls: 'help16',
                        handler: function()
                        {
                            ShowHelp(me.up('container').title);
                        }
                    }
                ]
            }
        ];

        me.callParent(arguments)
    },
    listeners: {
        render: function(){
            var me = this,
                baseForm = me.down('BaseForm').getForm();
           
            Ext.Ajax.request({
                url: '/?c=CmpCallCard&m=getDatesToNumbersDayYear',
                callback: function (opt, success, response) {
                    var datesParams = Ext.JSON.decode(response.responseText);

                    if(datesParams.data){
                        var Dt_from = Ext.Date.parse(datesParams.data.startDateTime,'Y-m-d H:i:s'),
                            Dt_to = Ext.Date.parse(datesParams.data.endDateTime,'Y-m-d H:i:s');

                        baseForm.findField('endDate').setValue(Ext.Date.format(Dt_to,'d.m.Y'));
                        baseForm.findField('endTime').setValue(Ext.Date.format(Dt_to,'H:i'));
                        baseForm.findField('begDate').setValue(Ext.Date.format(Dt_from,'d.m.Y'));
                        baseForm.findField('begTime').setValue(Ext.Date.format(Dt_from,'H:i'));
                    }
                    me.searchCmpCalls()
                }
            });


        },
        afterrender: function(){
            var me = this;
            var pressedkeyg = new Ext.util.KeyMap({
                target: me.el,
                binding: [
                    {
                        key: [Ext.EventObject.ENTER],
                        fn: function(){me.searchCmpCalls()}
                    }
                ]
            });
        },
        itemcontextmenu: function(grid, record, item, index, event, eOpts){
            var cntr = this;


            event.preventDefault();
            event.stopPropagation();
            cntr.showSubMenu(event.getX(), event.getY());
        }
    },
    searchCmpCalls: function(){
        var me = this,
            baseForm = me.down('BaseForm'),
            dateTo = baseForm.down('[name=begDate]').getValue(),
            timeTo = baseForm.down('[name=begTime]').getValue(),
            dateFrom = baseForm.down('[name=endDate]').getValue(),
            timeFrom = baseForm.down('[name=endTime]').getValue(),
            params = baseForm.getValues();            
		
        if(!Ext.isDate(dateTo) || !Ext.isDate(dateFrom)){
            return ;
        }

        params.CmpCallCard_IsExtra = params.IsExtra;
        params.CmpCallType_id = params.CmpCallType;
        params.CmpReason_id = params.CmpReason;

        me.store.proxy.extraParams = params;

        me.store.reload();
    },
    clearPersonFields: function(){
        var baseForm = this.down('BaseForm');
            baseForm.down('[name=Person_FIO]').reset();
            baseForm.down('[name=Person_id]').reset();
            baseForm.down('[name=Sex_id]').reset();

    },
    setPatient: function(personInfo) {
        var baseForm = this.down('BaseForm');
        baseForm.down('[name=Person_FIO]').setValue(personInfo.PersonSurName_SurName + ' ' + personInfo.PersonFirName_FirName + ' ' + personInfo.PersonSecName_SecName);
        baseForm.down('[name=Person_id]').setValue(personInfo.Person_id);
        baseForm.down('[name=Sex_id]').setValue(personInfo.Sex_id);
    },
    showSubMenu: function(x,y){
        var me = this,
            recCard = me.getSelectionModel().getSelection()[0];
        var subMenu = Ext.create('Ext.menu.Menu', {
            plain: true,
            renderTo: Ext.getBody(),
            items: [
                {
                    text: 'Прослушать аудиозапись',
                    hidden: !(recCard.get('CmpCallRecord_id')),
                    handler: function(){
                        subMenu.close();

                        Ext.create('common.tools.swCmpCallRecordListenerWindow',{
                            record_id : recCard.get('CmpCallRecord_id')
                        }).show();
                    }.bind(this)
                },
                {
                    text: 'История вызова',
                    handler: function(){
                        subMenu.close();

                        var callCardHistoryWindow = Ext.create('sw.tools.swCmpCallCardHistory',{
                            card_id: recCard.get('CmpCallCard_id')
                        });
                        callCardHistoryWindow.show();
                    }.bind(this)
                }
            ]
        });
        subMenu.showAt(x,y);
    },
});                              