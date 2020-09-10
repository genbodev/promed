/*
 * АРМ Старшего врача
 */
Ext.define('common.HeadDoctorWP.swHeadDoctorWorkPlace', {
    extend: 'Ext.window.Window',
    alias: 'widget.swHeadDoctorWorkPlace',
    maximized: true,
    closeAction: 'hide',
    refId: 'smpheaddoctor',
    closable: true,
    baseCls: 'arm-window',
    title: 'АРМ Старшего врача СМП',
    //defaultFocus: this.id+'_callsGridHD',
    header: false,
    renderTo: Ext.getCmp('inPanel').body,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    constrain: true,
    onEsc: function(){
        return false;
    },

    initComponent: function() {
		var me = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			currMedStaffFact = sw.Promed.MedStaffFactByUser.current;

		me.curArm = curArm;

		me.isNmpArm = curArm.inlist(['nmpgranddoc']);

		me.refId = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;

		me.title = sw.Promed.MedStaffFactByUser.current.ARMName || sw.Promed.MedStaffFactByUser.last.ARMName;

        var pacientSearchResText = Ext.create('Ext.panel.Panel', {
            margin: '5',
            width: 370,
            height: 27,
            name: 'status_panel',
            refId: 'pacientSearchResText',
            html: '',
            hidden: true
        });

        var diagnosesPersonOnDispText = Ext.create('Ext.panel.Panel', {
            width: 600,
            minHeight: 70,
            margin: '0 0 10 0',
            name: 'diagnoses_panel',
            refId: 'diagnosesPersonOnDispText',
            html: '',
            hidden: true
        });

        var smpUnitsNestedCombo = Ext.create('sw.SmpUnitsNested', {
            name: 'LpuBuilding_id',
            labelWidth: 121,
            flex: 1,
            labelAlign: 'right',
			hidden: me.isNmpArm,
            displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
            tpl: '<tpl for="."><div class="x-boundlist-item">' +
            '<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}' +
            '</div></tpl>',
            listeners: {
                render: function (cmp) {
                    cmp.store.proxy.url = '?c=CmpCallCard4E&m=loadSmpUnitsNestedALL';
                    cmp.store.load();
                }
            }
        });

        var smpRegionUnitsCombo = Ext.create('sw.RegionSmpUnits',{
                name: 'LpuBuilding_id',
                labelWidth: 121,
                flex: 1,
                labelAlign: 'right',
				hidden: me.isNmpArm,
                displayTpl: '<tpl for=".">{LpuBuilding_Name}/{Lpu_Nick}</tpl>',
                tpl: '<tpl for="."><div class="x-boundlist-item">'+
                '{LpuBuilding_Name}/{Lpu_Nick}'+
                '</div></tpl>'
            }
        );
        Ext.applyIf(me, {
            items: [
                Ext.create('Ext.tab.Panel', {
                    refId: 'mainTabPanelHD',
                    flex: 1,
                    height: 1000,
                    border: false,
                    items: [
                        {
                            xtype: 'panel',
                            title: 'Вызовы в работе',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'stretch'
                            },
                            items: [
                                {
                                    xtype: 'container',
                                    flex: 1,
                                    refs: 'leftSideContainerHD',
                                    layout: {
                                        type: 'hbox',
                                        align: 'stretch'
                                    },
                                    items: [
                                        {
                                            xtype: 'gridpanel',
                                            title: 'Вызовы',
                                            flex: 1,
											keepState: true,
                                            refId: 'callsGridHD',
                                            viewConfig: {
                                                loadMask: true,
                                                loadingText: 'Загрузка...',
                                                preserveScrollOnRefresh: true,
                                                //stripeRows: false,
                                                getRowClass: function(record, rowIndex, rowParams, store) {
													var cls = '';
													if(record && !record.get('CmpCallCard_id')) {
														cls = cls + 'hidden-row ';
													}
													if (record.get('PersonQuarantine_IsOn') == 'true') {
														cls = cls + 'x-grid-rowbackred ';
													}
													return cls;
                                                },
                                                listeners:{
                                                    groupexpand: function( view, node, group, eOpts ){

                                                        var bigGroups = view.panel.bigStore.groups.items;
                                                        var recs = bigGroups[group-1];

                                                        view.panel.store.loadData(recs.records, true);
                                                    }
                                                },
                                                cellTpl: Ext.create('Ext.XTemplate',
                                                    '<td role="gridcell" class="{tdCls}" {tdAttr} id="{[Ext.id()]}"' +
                                                    'style="'+
                                                    '<tpl if="this.checkIsSpecTeam(record)">',
                                                    ' background-color: #f6d7d7;border-top:1px solid #FF0000; border-bottom:1px solid #FF0000;',
                                                    '</tpl>',
                                                    '">'+
                                                    '<div {unselectableAttr} class="' + Ext.baseCSSPrefix + 'grid-cell-inner {innerCls}"'+
                                                    'style="text-align:{align};<tpl if="style">{style}</tpl>'+
                                                    '">{value}</div>'+
                                                    '</td>',
                                                    {
                                                        priority: 0,
                                                        checkIsSpecTeam: function(record){
                                                            return (!me.isNmpArm && record.raw.CmpCallType_Code == 9);
                                                        }
                                                    }
                                                ),
                                            },
                                            requires: [
                                                'Ext.grid.feature.Grouping'
                                            ],

                                            features: [{
                                                ftype: 'grouping',
                                                id: this.id + '_GroupingGridHDFeature',
												enableGroupingMenu: false,
                                                groupHeaderTpl: Ext.create('Ext.XTemplate',
                                                    '<div>{name:this.formatName} ({[ this.getCount(values, xindex, xcount, this) ]})</div>',
                                                    {
                                                        formatName: function (name, values) {
                                                            var groupname = '';
                                                            switch (name) {
                                                                case 1:
                                                                {
                                                                    groupname = 'Ожидание решения старшего врача';
                                                                    break;
                                                                }
                                                                case 2:
                                                                {
                                                                    groupname = 'Внимание';
                                                                    break;
                                                                }
                                                                case 3:
                                                                {
                                                                    groupname = 'В работе';
                                                                    break;
                                                                }
                                                                case 4:
                                                                {
                                                                    groupname = 'Прочие';
                                                                    break;
                                                                }
                                                                case 5:
                                                                {
                                                                    groupname = 'Исполненные';
                                                                    break;
                                                                }
																case 6:
                                                                {
                                                                    groupname = 'Закрытые';
                                                                    break;
                                                                }
                                                                case 7:
                                                                {
                                                                    groupname = 'Отмененные';
                                                                    break;
                                                                }
                                                                case 8:
                                                                {
                                                                    groupname = 'Отложенные';
                                                                    break;
                                                                }
                                                            }
                                                            return groupname;
                                                        },
                                                        getCount: function (values, xindex, xcount, cmp) {
                                                            var grid = this.down('grid[refId=callsGridHD]');

															//дело было в пятницу, очень хотелось домой
                                                            if(grid.store.isFiltered()) {
																var filters = grid.store.filters;

																filters.getCount()
																var recsInGroup = 0;
																grid.bigStore.queryBy(function(r){

																	if(r.get('CmpGroup_id') == values.groupValue){

																		var count = 0;

																		filters.each(function(a){
																			if(a.property && r.get('CmpCallCard_id')){
                                                                               // if((r.get(a.property) == a.value) || !a.value)
																				if(!a.value || (r.get(a.property).toString().toLowerCase().indexOf(a.value.toLowerCase()) != -1))
                                                                                    count = ++count;
																			}
																		})
																		if(count == filters.getCount()){
																			recsInGroup = ++recsInGroup;

																		}

																	}
																})
																return recsInGroup;

                                                                //return values.rows.length - 1;
                                                            }
                                                            return grid.bigStore.groups.items[values.groupValue-1].records.length - 1;
                                                            //return values.rows.length - 1;
                                                        }.bind(this)
                                                    }
                                                ),
                                                hideGroupedHeader: false,
                                                startCollapsed: true,
                                                enableGroupingMenu: false
                                            }],
                                            bigStore: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallsStore'),
                                            store: Ext.create('Ext.data.Store', {
                                                model: 'common.HeadDoctorWP.model.CmpCallCard',
                                                groupField: 'CmpGroup_id',
                                                autoLoad: false
                                            }),
                                            requires: [
                                                'Ext.ux.GridHeaderFilters'
                                            ],
                                            plugins: [Ext.create('Ext.ux.GridHeaderFilters',{enableTooltip: false,reloadOnChange:true, idProperty: 'CmpCallCard_id'})],
                                            columns: [
                                                {
                                                    dataIndex: 'CmpCallCard_id',
                                                    text: 'ИД карты вызова',
                                                    hidden: true,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
													dataIndex: 'CmpIllegalAct_byPerson',
													text: ' ',
													width: 50,
													hidden: me.isNmpArm,
													renderer: function(value, attr, rec){

														if(!value || !rec.get('CmpIllegalAct_Comment') || !rec.get('CmpIllegalAct_prmDT')) return '';

														var dangerUrl = 'extjs4/resources/images/danger.png',
															obj = (value == 2)? 'пациенту ' : 'адресу ',
															txt = 'По данному '+obj+rec.get('CmpIllegalAct_prmDT')+' зарегистрирован случай противоправного действия в отношении персонала СМП. Комментарий: ' + rec.get('CmpIllegalAct_Comment');

														return '<img src='+dangerUrl+' height="20px" title="'+txt+'"/>'
													}
                                                },
                                                {
                                                    dataIndex: 'CmpCallCard_prmDate',
                                                    text: 'ДАТА',
                                                    width: 120,
                                                    xtype: 'datecolumn',
                                                    format: (!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2) ? 'd.m.Y H:i':'d.m.Y H:i:s',
                                                    type: 'date',
                                                    // filter: {xtype: 'transFieldDelbut', translate: false, filterName:'CmpCallCard_prmDateStr'}

                                                    filter: {
                                                        xtype: 'datefield',
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

                                                },
                                                {
                                                    dataIndex: 'CmpCallCard_Numv',
                                                    text: '№ В/Д',
                                                    width: 50,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'Person_FIO',
                                                    text: 'ФИО',
                                                    flex: 1,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
												{
													dataIndex: 'Person_Birthday',
													xtype: 'datecolumn',
													sortable: true,
													text: 'ВОЗРАСТ',
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
                                                    dataIndex: 'CmpReason_Code',
                                                    text: 'ПОВОД',
                                                    // width: 50,
                                                    flex: 1,
                                                    filter: {xtype: 'transFieldDelbut', translate: false},
	                                                renderer: function (value, meta, rec) {
                                                    	if (getRegionNick().inlist(['astra'])) {
		                                                    return value + ' ' + rec.data.CmpReason_Name;
	                                                    } else return value;
	                                                }
                                                },
                                                {
                                                    dataIndex: 'CmpReason_Name',
                                                    text: 'Наименование повода',
                                                    // width: 50,
                                                    flex: 1,
	                                                hidden: true,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'Adress_Name',
                                                    text: 'АДРЕС',
                                                    flex: 2,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'CmpCallCard_Urgency',
                                                    text: 'СР',
                                                    flex: 2,
													renderer: function(val){
														if(val == 99) return '';
														else return val;
													},
													hidden: me.isNmpArm,
                                                    filter: {xtype: 'numberfield', hideTrigger: true, keyNavEnabled: false, mouseWheelEnabled: false}
                                                },
                                                {
                                                    dataIndex: 'EmergencyTeam_Num',
                                                    text: 'БР',
                                                    width: 50,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'LpuBuilding_Name',
                                                    text: 'Подстанция',
													hidden: me.isNmpArm,
                                                    width: 150,
                                                    // filter: {xtype: 'transFieldDelbut', translate: false}
                                                    filter: {
                                                        xtype: 'smpUnitsNestedCombo',
                                                        completeMatch: true,
                                                        displayTpl: '<tpl for=".">{LpuBuilding_filterName}</tpl>',
                                                        tpl: '<tpl for="."><div class="x-boundlist-item">'+
                                                            '{LpuBuilding_filterName}'+
                                                            '</div></tpl>'
                                                    }
                                                },
                                                {
                                                    dataIndex: 'CmpCallCardAcceptor_Code',
                                                    text: 'СМП / НМП',
                                                    width: 70,
                                                    filter: {xtype: 'transFieldDelbut', translate: false},
                                                    cls:'multiline',
                                                    hidden: getRegionNick().inlist(['ufa'])
                                                },
												{
													dataIndex: 'CmpCallCardEventType_Name',
													text: 'Событие',
													width: 120,
													filter: {
														xtype: 'swCmpCallCardEventTypeCombo',
														translate: false,
														matchFieldWidth: false,
														listeners:{
															render:function(cmp){
																cmp.store.load();
															}
														}
													}
												},
                                                {
                                                    dataIndex: 'EventWaitDuration',
                                                    text: 'Время',
                                                    width: 80,
                                                    renderer: function(v, meta, rec){
                                                        if(v){
                                                            return (rec.get('timeEventBreak') === 'true') ? '<span style="color: red;">' + v + '</span>' : v;
                                                        }
                                                    },
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'CmpCallCard_IsExtraText',
                                                    text: 'Вид вызова',
                                                    width: 150,
                                                    filter: {xtype: 'swCmpCallTypeIsExtraCombo'},
                                                    hidden: !(getRegionNick().inlist(['ufa']) || me.isNmpArm),
                                                    renderer:function(v){
                                                        if(v){
                                                            return (v == 'Экстренный')?'<span style="color:red;font-weight:bold">Экстренный</span>': v;
                                                        }
                                                    }
                                                },
                                                /*
                                                {
                                                    dataIndex: 'timeSMPExpiredReasonCode',
                                                    text: 'Превышено время',
                                                    width: 140,
                                                    renderer: function(value){
                                                        var text = '';
                                                        switch(value){
                                                            case 1: {text = 'стат. Передано СМП'; break;}
                                                            case 2: {text = 'стат. Принято СМП'; break;}
                                                            case 3: {text = 'стат. Передано НМП'; break;}
                                                            case 4: {text = 'стат. Принято НМП'; break;}
                                                        }
                                                        return text;
                                                    },
                                                    hidden: false,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                */
                                                {
                                                    dataIndex: 'DuplicateAndActiveCall_Count',
                                                    text: 'Дубли / Акт. зв.',
                                                    width: 60,
                                                    cls:'multiline'
                                                },
                                                {
                                                    dataIndex: 'CmpCallCardStatusType_id',
                                                    text: 'Статус вызова',
                                                    width: 70,
                                                    hidden: true,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'Lpu_hNick',
                                                    text: 'МО госпитализации',
                                                    width: 110,
													hidden: me.isNmpArm,
                                                    filter: {
                                                        xtype: 'lpuAllLocalCombo',
                                                        name: 'Lpu_hid',
                                                        hideLabel: true,
                                                        displayTpl: '<tpl for=".">{Org_Nick}</tpl>',
                                                        tpl: '<tpl for=".">' +
                                                        '<div class="x-boundlist-item">' +
                                                        '{Org_Nick}' +
                                                        '</div></tpl>'
                                                    },
                                                },
                                                {
                                                    dataIndex: 'PersonQuarantine_IsOn',
                                                    hidden: true,
                                                },
                                            ],
                                            dockedItems: [
                                                {
                                                    xtype: 'toolbar',
                                                    dock: 'top',
                                                    items: [
                                                        {
                                                            xtype: 'button',
                                                            text: 'Новый вызов',
                                                            iconCls: 'add16',
                                                            refId: 'createNewCall'
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            text: 'Закрыть карту вызова ',
                                                            refId: 'closeCmpCloseCard',
                                                            hidden: (getRegionNick().inlist(['ufa']) || me.isNmpArm),
                                                            disabled: true
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            text: 'Редактировать карту вызова ',
															hidden: (getRegionNick().inlist(['ufa']) || me.isNmpArm),
                                                            refId: 'editCmpCloseCard',
                                                            disabled: true
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            text: 'Оформить отказ',
                                                            refId: 'rejectCall',
															hidden: (!getRegionNick().inlist(['ufa']) || me.isNmpArm),
                                                            disabled: true
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            text: 'Вернуть в работу',
                                                            refId: 'backToWork',
															hidden: (getRegionNick().inlist(['ufa']) || me.isNmpArm),
                                                            disabled: true
                                                        },
                                                        {
                                                            xtype: 'combo',
                                                            refId: 'sortCalls',
                                                            fieldLabel: 'Сортировка',
															hidden: me.isNmpArm,
                                                            store: Ext.create('Ext.data.Store', {
                                                                fields: ['id', 'name', 'mode', 'field'],
                                                                data : [
                                                                    {"id":1, "name":"По срочности", "mode":"urgency", field: 'CmpCallCard_Urgency'},
                                                                    {"id":2, "name":"По времени", "mode":"time", field: "CmpCallCard_prmDate"}
                                                                ]
                                                            }),
                                                            queryMode: 'local',
                                                            name: 'displayMode',
                                                            displayField: 'name',
                                                            labelWidth: 60,
                                                            valueField: 'id',
                                                            value: 1
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            text: 'Показать на карте',
                                                            refId: 'showCardIntoTheMap',
                                                            iconCls: 'lpu-regiontype16',
                                                            disabled: true
                                                        }

                                                    ]
                                                }

                                            ]
                                        },
                                        /**/
                                        {
                                            xtype: 'BaseForm',
                                            id: me.id + '_teamDetailHDForm',
                                            flex: 1,
                                            title: 'Информация о бригаде',
                                            refId: 'teamDetailHD',
                                            layout: 'auto',
                                            isLoading: false,
                                            hidden: true,
                                            items: [
                                                {
                                                    xtype: 'fieldset',
                                                    title: 'Информация о бригаде',
                                                    refId: 'teamDetailHDteamBlock',
                                                    margin: '5',
                                                    layout: {
                                                        type: 'vbox',
                                                        align: 'stretch'
                                                    },
                                                    items: [
                                                        {
                                                            xtype: 'hidden',
                                                            name: 'EmergencyTeam_id'
                                                        },
                                                        {
                                                            xtype: 'container',
                                                            flex: 1,
                                                            //margin: '0 10',
                                                            layout: {
                                                                //type: 'hbox',
                                                                type: 'vbox',
                                                                align: 'stretch'
                                                            },
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    flex: 1,
                                                                    margin: '2 0',
                                                                    layout: {
                                                                        type: 'hbox',
                                                                        align: 'stretch'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'transFieldDelbut',
                                                                            fieldLabel: '№ бригады',
                                                                            labelAlign: 'right',
                                                                            labelWidth: 70,
                                                                            translate: false,
                                                                            allowBlank: false,
                                                                            name: 'EmergencyTeam_Num',
                                                                            maskRe: /[0-9:]/,
                                                                            flex: 1,
                                                                        },
                                                                        {
                                                                            xtype: 'swEmergencyTeamSpecCombo',
                                                                            labelAlign: 'right',
                                                                            labelWidth: 60,
                                                                            allowBlank: false,
                                                                            name: 'EmergencyTeamSpec_id',
                                                                            flex: 1,
                                                                        },
                                                                        {
                                                                            xtype: 'smpUnits',
                                                                            name: 'LpuBuilding_id',
                                                                            fieldLabel: 'П/С',
                                                                            //tabIndex: 22,
                                                                            labelWidth: 50,
                                                                            allowBlank: false,
                                                                            labelAlign: 'right',
                                                                            flex: 1,
																			hidden: me.isNmpArm
                                                                        }
                                                                    ]
                                                                },
                                                                {
                                                                    xtype: 'container',
                                                                    flex: 1,
                                                                    refId: 'stuffInfo',
                                                                    margin: '2 0',
                                                                    layout: {
                                                                        type: 'hbox',
                                                                        align: 'stretch'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'transFieldDelbut',
                                                                            fieldLabel: '№ машины',
                                                                            labelAlign: 'right',
                                                                            labelWidth: 70,
                                                                            translate: false,
                                                                            name: 'EmergencyTeam_CarNum',
                                                                            flex: 1
                                                                        },
                                                                        /*
                                                                         {
                                                                         xtype: 'transFieldDelbut',
                                                                         fieldLabel: 'Модель',
                                                                         labelAlign: 'right',
                                                                         labelWidth: 60,
                                                                         translate: false,
                                                                         name: 'EmergencyTeam_CarModel',
                                                                         flex: 1
                                                                         },
                                                                         */
                                                                        {
                                                                            xtype: 'transFieldDelbut',
                                                                            fieldLabel: 'Марка',
                                                                            labelAlign: 'right',
                                                                            labelWidth: 50,
                                                                            translate: false,
                                                                            name: 'EmergencyTeam_CarBrand',
                                                                            flex: 1
                                                                        }
                                                                    ]
                                                                },
                                                                {
                                                                    xtype: 'container',
                                                                    flex: 1,
                                                                    refId: 'stuffInfo',
                                                                    margin: '2 0',
                                                                    layout: {
                                                                        type: 'hbox',
                                                                        align: 'stretch'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: (getRegionNick().inlist(['kz'])) ? 'swEmergencyTeamTNCCombo' : 'swEmergencyTeamWialonCombo',
                                                                            labelAlign: 'right',
                                                                            fieldLabel: 'ГЛОНАСС',
                                                                            allowBlank: true,
                                                                            labelWidth: 70,
                                                                            name: 'GeoserviceTransport_id',
                                                                            flex: 1
                                                                        },
                                                                        {
                                                                            xtype: 'transFieldDelbut',
                                                                            labelAlign: 'right',
                                                                            fieldLabel: 'Статус',
                                                                            allowBlank: true,
                                                                            labelWidth: 60,
                                                                            name: 'EmergencyTeamStatus_Name',
                                                                            flex: 1
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            flex: 1,
                                                                            //refId: 'stuffInfo',
                                                                            //margin: '0 10',
                                                                            // width: 170,
                                                                            layout: {
                                                                                type: 'hbox',
                                                                                align: 'stretch'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    name: 'EmergencyTeamDuty_DTStart',
                                                                                    fieldLabel: 'Смена с',
                                                                                    format: 'H:i',
                                                                                    hideTrigger: true,
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    labelAlign: 'right',
                                                                                    minWidth: 100,
                                                                                    labelWidth: 55,
                                                                                    flex: 3
                                                                                }, {
                                                                                    xtype: 'datefield',
                                                                                    name: 'EmergencyTeamDuty_DTFinish',
                                                                                    fieldLabel: 'по',
                                                                                    format: 'H:i',
                                                                                    hideTrigger: true,
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    minWidth: 90,
                                                                                    flex: 2
                                                                                },
                                                                                {
                                                                                    xtype: 'transFieldDelbut',
                                                                                    labelAlign: 'right',
                                                                                    fieldLabel: 'ВЫЗОВОВ',
                                                                                    allowBlank: true,
                                                                                    labelWidth: 60,
                                                                                    name: 'CountCateredCmpCallCards',
                                                                                    flex: 3
                                                                                }
                                                                            ]
                                                                        },

                                                                    ]
                                                                }
                                                            ]
                                                        },
                                                        {
                                                            xtype: 'container',
                                                            flex: 1,
                                                            //margin: '0 10',
                                                            layout: {
                                                                type: 'vbox',
                                                                align: 'stretch'
                                                            },
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    flex: 1,
                                                                    layout: {
                                                                        type: 'vbox',
                                                                        align: 'stretch'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'container',
                                                                            layout: 'hbox',
                                                                            margin: '10 0 2 0',
                                                                            items: [
                                                                                {
                                                                                    xtype: 'swEmergencyFIOCombo',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 110,
                                                                                    store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.MedPersonalStore'),
                                                                                    fieldLabel: 'Старший бригады',
                                                                                    name: 'EmergencyTeam_HeadShift',
                                                                                    flex: 1
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    width: 100,
                                                                                    fieldLabel: 'С',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Head1StartTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    margins: '0 0 0 10',
                                                                                    width: 100,
                                                                                    fieldLabel: 'По',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Head1FinishTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            layout: 'hbox',
                                                                            margin: '2 0',
                                                                            items: [
                                                                                {
                                                                                    xtype: 'swEmergencyFIOCombo',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 110,
                                                                                    store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.MedPersonalStore'),
                                                                                    //fieldLabel: (getGlobalOptions().region.nick == 'perm')?'Второй работник':'Первый помощник',
                                                                                    fieldLabel: 'Помощник',
                                                                                    name: 'EmergencyTeam_Assistant1',
                                                                                    flex: 1
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    width: 100,
                                                                                    fieldLabel: 'С',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Assistant1StartTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    margins: '0 0 0 10',
                                                                                    width: 100,
                                                                                    fieldLabel: 'По',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Assistant1FinishTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            layout: 'hbox',
                                                                            margin: '2 0',
                                                                            items: [
                                                                                {
                                                                                    xtype: 'swEmergencyFIOCombo',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 110,
                                                                                    store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.MedPersonalStore'),
                                                                                    //fieldLabel: (getGlobalOptions().region.nick == 'perm')?'Третий работник':'Второй помощник',
                                                                                    fieldLabel: 'Помощник',
                                                                                    //allowBlank: false,
                                                                                    name: 'EmergencyTeam_Assistant2',
                                                                                    flex: 1
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    width: 100,
                                                                                    fieldLabel: 'С',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Assistant2StartTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    margins: '0 0 0 10',
                                                                                    width: 100,
                                                                                    fieldLabel: 'По',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Assistant2FinishTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            layout: 'hbox',
                                                                            margin: '2 0',
                                                                            items: [
                                                                                {
                                                                                    xtype: 'swEmergencyFIOCombo',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 110,
                                                                                    store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.MedPersonalStore'),
                                                                                    fieldLabel: 'Водитель',
                                                                                    //allowBlank: false,
                                                                                    name: 'EmergencyTeam_Driver',
                                                                                    flex: 1
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    width: 100,
                                                                                    fieldLabel: 'С',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Driver1StartTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    margins: '0 0 0 10',
                                                                                    width: 100,
                                                                                    fieldLabel: 'По',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Driver1FinishTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '2 0',
                                                                            layout: 'hbox',
                                                                           // hidden: (getRegionNick().inlist(['perm', 'ekb'])) ? true : false,
                                                                            items: [
                                                                                {
                                                                                    xtype: 'swEmergencyFIOCombo',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 110,
                                                                                    store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.MedPersonalStore'),
                                                                                    fieldLabel: 'Водитель',
                                                                                    name: 'EmergencyTeam_Driver2',
                                                                                    flex: 1
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    width: 100,
                                                                                    fieldLabel: 'С',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Driver2StartTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                },
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    margins: '0 0 0 10',
                                                                                    width: 100,
                                                                                    fieldLabel: 'По',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 20,
                                                                                    hideTrigger: true,
                                                                                    format: 'H:i',
                                                                                    name: 'EmergencyTeam_Driver2FinishTime',
                                                                                    plugins: [new Ux.InputTextMask('99:99')],
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'textfield',
                                                                            labelAlign: 'right',
                                                                            fieldLabel: 'Комментарий',
                                                                            labelWidth: 110,
                                                                            width: 700,
                                                                            name: 'EmergencyTeamDuty_ChangeComm'
                                                                        }
                                                                    ]
                                                                }
                                                            ]
                                                        }
                                                    ],
                                                    dockedItems: [
                                                        {
                                                            xtype: 'toolbar',
                                                            dock: 'bottom',
                                                            items: [
                                                                {xtype: 'tbfill'},
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'cancelBtn',
                                                                    iconCls: 'cancel16',
                                                                    text: 'Закрыть'
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                },
                                                {
                                                    xtype: 'fieldset',
                                                    title: 'Информация о вызове',
                                                    refId: 'teamDetailHDcallBlock',
                                                    hidden: true,
                                                    margin: '5',
                                                    layout: {
                                                        type: 'vbox',
                                                        align: 'stretch'
                                                    },
                                                    items: [
                                                        {
                                                            xtype: 'container',
                                                            layout: 'hbox',
                                                            margin: '2 0',
                                                            items: [
                                                                {
                                                                    xtype: 'textfield',
                                                                    flex: 3,
                                                                    fieldLabel: 'Адрес',
                                                                    name: 'Adress_Name',
                                                                    labelWidth: 110,
                                                                    labelAlign: 'right'
                                                                },
                                                                {
                                                                    xtype: 'cmpReasonCombo',
                                                                    name: 'CmpReason_id',
                                                                    flex: 2,
                                                                    labelWidth: 90,
                                                                },
                                                            ]
                                                        },
                                                        {
                                                            xtype: 'container',
                                                            layout: 'hbox',
                                                            margin: '2 0',
                                                            items: [
                                                                {

                                                                    xtype: 'textfield',
                                                                    hideTrigger: true,
                                                                    keyNavEnabled: false,
                                                                    mouseWheelEnabled: false,
                                                                    flex: 1,
                                                                    fieldLabel: '№ вызова (за год):',
                                                                    labelAlign: 'right',
                                                                    labelWidth: 110,
                                                                    name: 'CmpCallCard_Ngod'
                                                                },
                                                                {
                                                                    xtype: 'textfield',
                                                                    flex: 1,
                                                                    fieldLabel: 'Возраст',
                                                                    enableKeyEvents: true,
                                                                    labelAlign: 'right',
                                                                    name: 'Person_Age'
                                                                },
                                                                {
                                                                    xtype: 'textfield',
                                                                    flex: 1,
                                                                    fieldLabel: 'Срочность',
                                                                    enableKeyEvents: true,
                                                                    labelAlign: 'right',
                                                                    name: 'CmpCallCard_Urgency'
                                                                }
                                                            ]
                                                        },
                                                        {
                                                            xtype: 'hidden',
                                                            value: 0,
                                                            name: 'CmpCallCard_id'
                                                        }
                                                    ]
                                                },
                                                {
                                                    xtype: 'fieldset',
                                                    title: 'История бригады',
                                                    refId: 'teamDetailHDemergencyStatusHistory',
                                                    margin: '5',
                                                    layout: {
                                                        type: 'vbox',
                                                        align: 'stretch'
                                                    },
                                                    items: [
                                                        {
                                                            xtype: 'gridpanel',
                                                            flex: 1,
                                                            refId: 'teamDetailHDemergencyStatusHistoryGrid',
                                                            viewConfig: {
                                                                loadMask: false,
                                                            },
                                                            store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.EmergencyTeamStatusHistoryStore'),
                                                            columns: [
                                                                {
                                                                    dataIndex: 'EmergencyTeamStatusHistory_insDT',
                                                                    text: 'Время',
                                                                    width: 140
                                                                },
                                                                {
                                                                    dataIndex: 'EmergencyTeamStatus_Name',
                                                                    text: 'Статус',
                                                                    flex: 1
                                                                }, {
                                                                    dataIndex: 'CmpCallCard_Ngod',
                                                                    text: 'Номер вызова',
                                                                    flex: 1
                                                                }
                                                            ]
                                                        }
                                                    ]
                                                }
                                            ],

                                            dockedItems: [
                                                {
                                                    xtype: 'toolbar',
                                                    dock: 'bottom',
                                                    items: [
                                                        {xtype: 'tbfill'},
                                                        {
                                                            xtype: 'button',
                                                            refId: 'cancelBtn',
                                                            iconCls: 'cancel16',
                                                            text: 'Закрыть'
                                                        }
                                                    ]
                                                }
                                            ]

                                        },
                                    ]
                                },
                                {
                                    xtype: 'container',
                                    refId: 'hrSplitterContainer',
                                    cls: 'x-toolbar-default',
                                    layout: {
                                        type: 'hbox',
                                        align: 'middle'
                                    },
                                    items: [
                                        {
                                            xtype: 'button',
                                            iconCls: 'right-splitter',
                                            refId: 'hr-splitter',
                                            height: 40,
                                            width: 13
                                        }
                                    ]
                                },
                                {
                                    xtype: 'container',
                                    flex: 1,
                                    refs: 'rightSideContainerHD',
                                    layout: {
                                        type: 'hbox',
                                        align: 'stretch'
                                    },
                                    items: [
                                        {
                                            xtype: 'gridpanel',
                                            flex: 1,
                                            title: 'Бригады',
                                            //hidden: true,
											keepState: true,
                                            refId: 'teamsGridHD',
                                            viewConfig: {
                                                loadMask: false,
                                                preserveScrollOnRefresh: true
                                            },
                                            store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.EmergencyTeamStore'),
                                            requires: [
                                                'Ext.grid.feature.Grouping',
                                                'Ext.ux.grid.GridPrinter'
                                            ],
                                            features: [{
                                                ftype: 'grouping',
												enableGroupingMenu: false,
                                                groupHeaderTpl: Ext.create('Ext.XTemplate',
                                                    '<div>{name:this.formatName(values)} ({[ this.getCount(values) ]})</div>',
                                                    {
                                                        formatName: function (values) {
                                                        	var EmergencyTeamStore = Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.EmergencyTeamStore');
															return EmergencyTeamStore.findRecord('LpuBuilding_id',values).get('EmergencyTeamBuildingName')
                                                        },
                                                        getCount: function (values) {
                                                            var countFreeStatusCars = 0;
                                                            for (var i = 0; i < values.rows.length; i++) {
                                                                var rec = values.rows[i];
                                                                if (rec.get('EmergencyTeamStatus_Color') == 'blue')
                                                                    countFreeStatusCars++;
                                                            }
                                                            
															var countBusyStatusCars = values.rows.length - countFreeStatusCars;
															return '<span style="color: #00801d">'+countFreeStatusCars + '</span> / <span style="color: #ff0512">' + countBusyStatusCars + ' </span>/ '+ values.rows.length;
                                                        }
                                                    }
                                                ),
                                                hideGroupedHeader: false,
                                                startCollapsed: true
                                            }],
                                            requires: [
                                                'Ext.ux.GridHeaderFilters'
                                            ],
                                            plugins: [Ext.create('Ext.ux.GridHeaderFilters',{enableTooltip: false,reloadOnChange:true,})],
                                            columns: [
                                                {
                                                    dataIndex: 'EmergencyTeam_Num',
                                                    text: 'БР',
                                                    // width: 40,
                                                    flex: 1,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'EmergencyTeamSpec_Code',
                                                    text: 'ПР',
                                                    // width: 40,
                                                    flex: 1,                                                    
                                                    filter: {
                                                        xtype: 'swEmergencyTeamSpecCombo',
                                                        listeners:{
                                                            render:function(cmp){
                                                               cmp.store = Ext.ComponentQuery.query('swEmergencyTeamSpecCombo')[0].store;
                                                            }
                                                        },
                                                        displayTpl: '<tpl for=".">{EmergencyTeamSpec_Code}</tpl>',
                                                        tpl: '<tpl for="."><div class="x-boundlist-item">'+
                                                            '{EmergencyTeamSpec_Code}'+
                                                            '</div></tpl>'
                                                    }                                                    
                                                    // filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'EmergencyTeamBuildingName',
                                                    text: me.isNmpArm ? 'Подразделение НМП' : 'П/С',
                                                    // width: 60,
                                                    flex: 1,
                                                    filter: {
                                                        xtype: 'smpUnitsNestedCombo',
                                                        completeMatch: true,
                                                        translate: false,
                                                        displayTpl: '<tpl for=".">{LpuBuilding_filterName}</tpl>',
                                                        tpl: '<tpl for="."><div class="x-boundlist-item">'+
                                                            '{LpuBuilding_filterName}'+
                                                            '</div></tpl>'
                                                    }
                                                },
                                                {
                                                    dataIndex: 'Person_Fin',
                                                    text: 'СТАРШИЙ БРИГАДЫ',
                                                    flex: 2,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'EmergencyTeamStatus_Name',
                                                    text: 'СТАТУС',
                                                    // width: 70,
                                                    flex: 1,
                                                    renderer: function(value, meta, rec){
                                                        return rec.get('LpuHid_Nick') ? value + ' (' + rec.get('LpuHid_Nick') + ')' : value;
                                                    },
                                                    filter: {xtype: 'swEmergencyTeamStatuses', translate: false}
                                                },
												{
													dataIndex: 'lastCheckinAddress',
													text: 'МЕСТО',
													sortable: true,
													flex:1,
													hidden: !getRegionNick().inlist(['perm']),
													filter: {xtype: 'transFieldDelbut', translate: false}
												},
                                                {
                                                    dataIndex: 'CmpCallCard_Ngod',
                                                    text: 'ВЫЗОВ',
                                                    width: 100,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'lastChangedStatusTime',
                                                    text: 'ВР',
                                                    width: 100,
                                                    filter: {xtype: 'transFieldDelbut', translate: false}
                                                },
                                                {
                                                    dataIndex: 'EmergencyTeam_isOnline',
													renderer: function(value, meta, rec){
														if(value == 'online')
															return '<div class="hd-et-online"></div>';
													},
													text: 'Планшет'
                                                }
                                            ],
                                            dockedItems: [
                                                {
                                                    xtype: 'toolbar',
                                                    dock: 'top',
                                                    items: [
                                                        {
                                                            xtype: 'button',
                                                            text: 'Показать на карте',
                                                            refId: 'showInMapBtnHD',
                                                            iconCls: 'lpu-regiontype16 ',
                                                            disabled: true
                                                        },
                                                        {
                                                            xtype: 'button',
                                                            text: 'Печать',
                                                            refId: 'printEmergencyGridBtnHD',
                                                            iconCls: 'print16'
                                                        }
                                                    ]
                                                }
                                            ]
                                        },
                                        {
                                            xtype: 'tabpanel',
                                            layout: 'auto',
                                            flex: 1,
                                            layout: 'fit',
                                            cls: 'callDetailHD',
                                            hidden: true,
                                            overflowY: 'auto',
                                            refId: 'callDetailParentHD',
                                            items: [
                                                {
                                                    xtype: 'BaseForm',
                                                    id: me.id + '_callDetailHDForm',
                                                    flex: 1,
                                                    title: 'Информация о вызове',
                                                    header: false,
                                                    refId: 'callDetailHD',
                                                    layout: 'fit',
                                                    overflowY: 'auto',
                                                    //hidden: true,
                                                    isLoading: false,
                                                    trackResetOnLoad: true,
                                                    items: [
                                                        {
                                                            xtype: 'container',
                                                            layout: 'auto',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    floatable: false,
                                                                    region: 'north',
                                                                    splitterResize: false,
                                                                    height: 69,
                                                                    layout: {
                                                                        align: 'middle',
                                                                        type: 'hbox'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'fieldcontainer',
                                                                            margin: '0 0 0 10',
                                                                            flex: 1,
                                                                            // disabled: true,
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    fieldLabel: 'Дата вызова',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 135,
                                                                                    format: 'd.m.Y',
                                                                                    plugins: [new Ux.InputTextMask('99.99.9999')],
                                                                                    name: 'CmpCallCard_prmDate',
                                                                                    readOnly: true
                                                                                },
                                                                                {
                                                                                    xtype: 'numberfield',
                                                                                    hideTrigger: true,
                                                                                    keyNavEnabled: false,
                                                                                    mouseWheelEnabled: false,
                                                                                    flex: 1,
                                                                                    fieldLabel: '№ вызова (за день)',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 135,
                                                                                    name: 'CmpCallCard_Numv',
                                                                                    readOnly: true
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'fieldcontainer',
                                                                            margin: '0 27 0 10',
                                                                            // disabled: true,
                                                                            flex: 1,
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    name: 'CmpCallCard_prmTime',
                                                                                    fieldLabel: 'Время поступления вызова',
                                                                                    format: 'H:i:s',
                                                                                    hideTrigger: true,
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
                                                                                    plugins: [new Ux.InputTextMask('99:99:99')],
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 160,
                                                                                    readOnly: true
                                                                                },
                                                                                {
                                                                                    xtype: 'textfield',
                                                                                    hideTrigger: true,
                                                                                    keyNavEnabled: false,
                                                                                    mouseWheelEnabled: false,
                                                                                    flex: 1,
                                                                                    fieldLabel: '№ вызова (за год):',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 160,
                                                                                    name: 'CmpCallCard_Ngod',
                                                                                    readOnly: true
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            value: '',
                                                                            name: 'ARMType'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            value: 0,
                                                                            name: 'CmpCallCard_id'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_updDT'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            value: 0,
                                                                            name: 'CmpCallCard_rid'
                                                                        },
                                                                        {
                                                                            name: 'CmpLpu_Name',
                                                                            value: '',
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            name: 'CmpLpu_id',
                                                                            value: 0,
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            name: 'Person_id',
                                                                            value: 0,
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            name: 'Person_isOftenCaller',
                                                                            value: 1,
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_Ktov'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_CallLtd'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_CallLng'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'EmergencyTeam_id'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCardStatusType_id'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'EmergencyTeamStatus_Code'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'LogicRulesEmergencyTeamSpec_Code'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_IsDeterior'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'pcEmergencyTeam_id'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'pcEmergencyTeamStatus_Code'
                                                                        }
                                                                    ]
                                                                },
                                                                {
                                                                    xtype: 'container',
                                                                    region: 'center',
                                                                    layout: {
                                                                        type: 'auto'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    layout: {
                                                                                        align: 'stretch',
                                                                                        type: 'vbox'
                                                                                    },
                                                                                    title: 'Место вызова',
                                                                                    //disabled: getRegionNick().inlist(['ufa']),
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'fieldcontainer',
                                                                                            margin: '0 5',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                align: 'stretch',
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'dCityCombo',
                                                                                                    labelWidth: 120,
																									maxWidth: 300,
                                                                                                    flex: 2,
                                                                                                    disabled: getRegionNick().inlist(['ufa'])
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'swStreetsSpeedCombo',
                                                                                                    name: 'dStreetsCombo',
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 35,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    fieldLabel: 'Ул.',
                                                                                                    flex: 1
                                                                                                },
																								{
                                                                                                    xtype: 'swStreetsSpeedCombo',
                                                                                                    name: 'secondStreetCombo',
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 35,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    fieldLabel: 'Ул.',
																									enableKeyEvents: true,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    plugins: [new Ux.Translit(true, true)],
                                                                                                    fieldLabel: 'Дом',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Dom',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    plugins: [new Ux.Translit(true, true)],
                                                                                                    fieldLabel: 'Корп',
                                                                                                    enforceMaxLength: true,
                                                                                                    maxLength: 5,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    //hidden: (!getRegionNick().inlist(['ufa', 'krym', 'kz'])),
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Korp',
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'fieldcontainer',
                                                                                            margin: '4 5 0',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                align: 'stretch',
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    //maskRe: /[0-9:]/,
                                                                                                    enforceMaxLength: true,
                                                                                                    maxLength: 5,
                                                                                                    plugins: [new Ux.Translit(true, true)],
                                                                                                    fieldLabel: 'Кв.',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Kvar',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 120,
																									maxWidth: 300,
																									flex: 2,
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    maskRe: /[0-9:]/,
                                                                                                    fieldLabel: 'Пд.',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Podz',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    maskRe: /[0-9:]/,
                                                                                                    fieldLabel: 'Эт.',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Etaj',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    fieldLabel: 'Код',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Kodp',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'fieldcontainer',
                                                                                            margin: '4 5 10',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                align: 'stretch',
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swCmpCallPlaceType',
                                                                                                    name: 'CmpCallPlaceType_id',
                                                                                                    fieldLabel: 'Тип места',
                                                                                                    labelAlign: 'right',
                                                                                                    allowBlank: false,
                                                                                                    value: 1,
                                                                                                    labelWidth: 120,
																									maxWidth: 300,
																									flex: 2,
                                                                                                    triggerClear: true,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    hideTrigger: true,
                                                                                                    displayTpl: '<tpl for="."> {CmpCallPlaceType_Code}. {CmpCallPlaceType_Name} </tpl>',
                                                                                                    tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">' +
                                                                                                    '<font color="red">{CmpCallPlaceType_Code}</font> {CmpCallPlaceType_Name}' +
                                                                                                    '</div></tpl>'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'swCmpCallerTypeCombo',
                                                                                                    name: 'CmpCallerType_id',
                                                                                                    labelWidth: 35,
                                                                                                    triggerClear: true,
                                                                                                    hideTrigger: true,
                                                                                                    autoFilter: false,
                                                                                                    forceSelection: false,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    autoSelect: false,
                                                                                                    labelAlign: 'right',
                                                                                                    flex: 1,
                                                                                                    fieldLabel: 'Выз.',
                                                                                                    minChars: 2,
                                                                                                    tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">' +
                                                                                                    '{CmpCallerType_Name}' +
                                                                                                    '</div></tpl>'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'trigger',
                                                                                                    fieldLabel: 'Тел.',
                                                                                                    enableKeyEvents: true,
                                                                                                    maskRe: /[0-9:]/,
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Telf',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1,
                                                                                                    cls: 'x-form-table-div',
                                                                                                    triggerCls: 'x-form-eye-trigger-default',
                                                                                                    inputType: 'password'
                                                                                                }
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    layout: {
                                                                                        type: 'vbox',
                                                                                        align: 'stretch'
                                                                                    },
                                                                                    flex: 1,
                                                                                    title: 'Пациент',
                                                                                    // disabled: true,
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                type: 'vbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    flex: 1,
                                                                                                    margin: '0 5',
                                                                                                    layout: {
                                                                                                        type: 'hbox',
                                                                                                        align: 'stretch'
                                                                                                    },
                                                                                                    items: [
                                                                                                        {
                                                                                                            xtype: 'textfield',
                                                                                                            plugins: [new Ux.Translit(true, true)],
																											labelWidth: 120,
																											maxWidth: 300,
																											flex: 2,
                                                                                                            fieldLabel: 'Фамилия',
                                                                                                            labelAlign: 'right',
                                                                                                            name: 'Person_SurName',
                                                                                                            enableKeyEvents: true
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'textfield',
                                                                                                            plugins: [new Ux.Translit(true, true)],
                                                                                                            flex: 1,
                                                                                                            labelWidth: 35,
                                                                                                            fieldLabel: 'Имя',
                                                                                                            labelAlign: 'right',
                                                                                                            name: 'Person_FirName',
                                                                                                            enableKeyEvents: true
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'textfield',
                                                                                                            plugins: [new Ux.Translit(true, true)],
                                                                                                            flex: 1,
                                                                                                            labelWidth: 70,
                                                                                                            fieldLabel: 'Отч.',
                                                                                                            labelAlign: 'right',
                                                                                                            name: 'Person_SecName'
                                                                                                        }
                                                                                                    ]
                                                                                                }
                                                                                                /*,{
                                                                                                 xtype: 'container',
                                                                                                 //width: 150,
                                                                                                 margin: '0 10',
                                                                                                 layout: {
                                                                                                 type: 'vbox'
                                                                                                 },
                                                                                                 items: [
                                                                                                 {
                                                                                                 xtype: 'button',
                                                                                                 name: 'clearPersonFields',
                                                                                                 text: 'Сброс',
                                                                                                 iconCls: 'delete16'
                                                                                                 },
                                                                                                 {
                                                                                                 xtype: 'button',
                                                                                                 name: 'searchPersonBtn',
                                                                                                 text: 'Поиск',
                                                                                                 iconCls: 'search16',
                                                                                                 margin: '5 0'
                                                                                                 },
                                                                                                 {
                                                                                                 xtype: 'button',
                                                                                                 name: 'unknowPersonBtn',
                                                                                                 text: 'Неизвестен',
                                                                                                 iconCls: 'warning16'
                                                                                                 }
                                                                                                 ]
                                                                                                 }*/
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            margin: '4 5 10',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                               /* {
                                                                                                    xtype: 'hidden',
                                                                                                    name: 'Person_Age'
                                                                                                },
                                                                                                */
                                                                                                {
                                                                                                    //xtype: 'textfield',
                                                                                                    xtype: 'hidden',
                                                                                                    name: 'Person_Birthday'
                                                                                                },
																								{
                                                                                                    //xtype: 'textfield',
                                                                                                    xtype: 'hidden',
                                                                                                    name: 'Person_AgeInt'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    flex: 2,
                                                                                                    maxWidth: 300,
                                                                                                    layout: {
                                                                                                        type: 'hbox',
                                                                                                        align: 'stretch'
                                                                                                    },
                                                                                                    items: [
                                                                                                        {
                                                                                                            xtype: 'numberfield',
                                                                                                            fieldLabel: 'Возраст',
                                                                                                            hideTrigger: true,
                                                                                                            allowDecimals: false,
                                                                                                            allowNegative: false,
                                                                                                            enableKeyEvents: true,
                                                                                                            labelAlign: 'right',
                                                                                                            //name: 'Person_AgeText',
                                                                                                            name: 'Person_Age',
                                                                                                            flex: 3,
                                                                                                            labelWidth: 120,
                                                                                                            margin: '0 5 0 0',
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'swDAgeUnitCombo',
                                                                                                            tabIndex: 26,
                                                                                                            //value: 0,
                                                                                                            name: 'ageUnit_id',
                                                                                                            displayField: 'ageUnit_name',
                                                                                                            enableKeyEvents : true,
                                                                                                            //bigFont: true,
                                                                                                            valueField: 'ageUnit_id',
                                                                                                            flex: 1,
                                                                                                            triggerClear: false
                                                                                                        }
                                                                                                    ]
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'sexCombo',
                                                                                                    enableKeyEvents: true,
                                                                                                    tabIndex: 26,
                                                                                                    name: 'Sex_id',
                                                                                                    bigFont: false,
																									flex: 1,
                                                                                                    labelAlign: 'right',
                                                                                                    hideTrigger: true,
                                                                                                    labelWidth: 35
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    fieldLabel: '№ полиса',
                                                                                                    labelAlign: 'right',
                                                                                                    flex: 1,
                                                                                                    name: 'Polis_Num',
                                                                                                    labelWidth: 70
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                pacientSearchResText,
                                                                                                diagnosesPersonOnDispText
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            flex: 1,
                                                                            defaultAlign: 'left',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    layout: {
                                                                                        align: 'stretch',
                                                                                        type: 'vbox'
                                                                                    },
                                                                                    flex: 1,
                                                                                    title: 'Вызов',
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            // disabled: true,
                                                                                            flex: 1,
                                                                                            margin: '0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swCmpCallTypeCombo',
                                                                                                    name: 'CmpCallType_id',
                                                                                                    flex: 1,
                                                                                                    labelWidth: 120,
                                                                                                    fieldLabel: 'Тип вызова',
                                                                                                    autoFilter: getRegionNick() != 'ufa',
                                                                                                    triggerClear: getRegionNick() != 'ufa'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'cmpReasonCombo',
                                                                                                    name: 'CmpReason_id',
                                                                                                    flex: 1,
                                                                                                    allowBlank: false,
                                                                                                    forceSelection: true,
                                                                                                    //autoFilter: false
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swCmpCallTypeIsExtraCombo',
                                                                                                    fieldLabel: 'Вид вызова',
                                                                                                    allowBlank: false,
                                                                                                    enableKeyEvents: true,
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 120,
                                                                                                    flex: 1,
                                                                                                    name: 'CmpCallCard_IsExtra',
                                                                                                    listConfig: {}
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    name: 'CmpCallCard_Urgency',
                                                                                                    width: 90,
                                                                                                    fieldLabel: 'Ср',
                                                                                                    enableKeyEvents: true,
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 35,
																									hidden: me.isNmpArm
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'checkbox',
                                                                                                    flex: 1,
                                                                                                    name: 'CmpCallCard_IsPoli',
                                                                                                    boxLabel: 'Вызов передан в поликлинику по телефону (рации)',
                                                                                                    margin: '0 0 0 125',
																									hidden: me.isNmpArm
                                                                                                }

                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'lpuLocalCombo',
                                                                                                    name: 'Lpu_ppdid',
                                                                                                    labelWidth: 120,
                                                                                                    flex: 1,
                                                                                                    bigFont: false,
                                                                                                    validateOnBlur: false,
                                                                                                    fieldLabel: 'МО ' +
                                                                                                        ' (НМП)'
                                                                                                },
                                                                                                {
                                                                                                    fieldLabel: 'Служба НМП',
                                                                                                    allowBlank: true,
                                                                                                    flex: 1,
                                                                                                    xtype: 'selectNmpCombo',
                                                                                                    name: 'MedService_id',
                                                                                                    isClose: 1
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'checkbox',
                                                                                                    flex: 1,
                                                                                                    margin: '0 0 0 125',
                                                                                                    name: 'CmpCallCard_IsPassSSMP',
                                                                                                    hidden: (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) || me.isNmpArm,
                                                                                                    boxLabel: 'Вызов передан в другую ССМП по телефону (рации)',
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'vbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'lpuAllLocalCombo',
                                                                                                    name: 'Lpu_smpid',
                                                                                                    flex: 1,
                                                                                                    fieldLabel: 'МО передачи (СМП)',
                                                                                                    labelWidth: 120,
                                                                                                    hidden: true
                                                                                                },
                                                                                                {   xtype: (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? 'smpUnitsNestedCombo':'regionSmpUnits',
                                                                                                    name: 'LpuBuilding_id',
                                                                                                    labelWidth: 121,
                                                                                                    flex: 1,
                                                                                                    fieldLabel: 'Подразделение СМП',
                                                                                                    labelAlign: 'right',
                                                                                                    displayTpl: '<tpl for="."> {LpuBuilding_Code}. {LpuBuilding_Name} </tpl>',
                                                                                                    tpl: '<tpl for="."><div class="x-boundlist-item">' +
                                                                                                        '<font color="red">{LpuBuilding_Code}</font> {LpuBuilding_Name}' +
                                                                                                        '</div></tpl>'
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swEmergencyTeamSpecCombo',
                                                                                                    labelAlign: 'right',
                                                                                                    flex: 1,
                                                                                                    labelWidth: 120,
                                                                                                    name: 'EmergencyTeamSpec_id'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'transFieldDelbut',
                                                                                                    fieldLabel: 'Бригада',
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 100,
                                                                                                    flex: 1,
                                                                                                    translate: false,
                                                                                                    name: 'EmergencyTeam_Num',
                                                                                                    maskRe: /[0-9:]/
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    labelWidth: 120,
                                                                                                    flex: 1,
                                                                                                    fieldLabel: "Диспетчер",
                                                                                                    name: "DPMedPersonal_id",
																									cls: 'loadAfter',
                                                                                                    xtype: "swmedpersonalcombo",
                                                                                                    enableKeyEvents: true
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'textareafield',
                                                                                            flex: 1,
                                                                                            margin: '4 5 10',
                                                                                            minHeight: 100,
                                                                                            labelWidth: 120,
                                                                                            fieldLabel: 'Доп. информация:',
                                                                                            enableKeyEvents: true,
                                                                                            labelAlign: 'right',
                                                                                            name: 'CmpCallCard_Comm'
                                                                                        }
                                                                                    ]
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            flex: 1,
                                                                            defaultAlign: 'left',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    //история статусов

                                                                                    xtype: 'fieldset',
                                                                                    title: 'История вызова',
                                                                                    refId: 'callDetailHDcmpCallCardStatusHistory',
                                                                                    margin: '5',
                                                                                    layout: {
                                                                                        type: 'vbox',
                                                                                        align: 'stretch'
                                                                                    },
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'gridpanel',
                                                                                            flex: 1,
                                                                                            refId: 'callDetailHDcmpCallCardStatusHistoryGrid',
                                                                                            viewConfig: {
                                                                                                loadMask: false,
                                                                                            },
                                                                                            //store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallCardStatusHistoryStore'),
                                                                                            store: new Ext.data.Store({
                                                                                                extend: 'Ext.data.Store',
                                                                                                autoLoad: false,
                                                                                                storeId: 'cmpCallCardStatusHistoryStore',
                                                                                                fields: [
                                                                                                    {
                                                                                                        name: 'EventDT',
                                                                                                        type: 'datetime'
                                                                                                    },
                                                                                                    {
                                                                                                        name: 'CmpCallCardEventType_Name',
                                                                                                        type: 'string'
                                                                                                    },
                                                                                                    {
                                                                                                        name: 'pmUser_FIO',
                                                                                                        type: 'string'
                                                                                                    },
                                                                                                    {
                                                                                                        name: 'EventValue',
                                                                                                        type: 'string'
                                                                                                    }
                                                                                                ],
                                                                                                proxy: {
                                                                                                    limitParam: 100,
                                                                                                    startParam: undefined,
                                                                                                    paramName: undefined,
                                                                                                    pageParam: undefined,
                                                                                                    //noCache:false,
                                                                                                    type: 'ajax',
                                                                                                    url: '/?c=CmpCallCard4E&m=loadCmpCallCardEventHistory',
                                                                                                    reader: {
                                                                                                        type: 'json',
                                                                                                        successProperty: 'success',
                                                                                                        root: 'data'
                                                                                                    },
                                                                                                    actionMethods: {
                                                                                                        create: 'POST',
                                                                                                        read: 'POST',
                                                                                                        update: 'POST',
                                                                                                        destroy: 'POST'
                                                                                                    }
                                                                                                }
                                                                                            }),
                                                                                            columns: [
                                                                                                {
                                                                                                    dataIndex: 'EventDT',
                                                                                                    text: 'Дата и время',
                                                                                                    width: 140
                                                                                                },
                                                                                                {
                                                                                                    dataIndex: 'CmpCallCardEventType_Name',
                                                                                                    text: 'Событие',
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    dataIndex: 'pmUser_FIO',
                                                                                                    text: 'ФИО',
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    dataIndex: 'EventValue',
                                                                                                    text: 'Значение события',
                                                                                                    flex: 1
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 10',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            hidden: !getGlobalOptions().region.nick.inlist(['krym','buryatiya']),
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'button',
                                                                                                    refId: 'showTrackBtn',
                                                                                                    name: 'showTrackBtn',
                                                                                                    text: 'Просмотр трека',
																									hidden: me.isNmpArm
                                                                                                }
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            flex: 1,
                                                                            defaultAlign: 'left',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    title: 'Результаты вызова',
                                                                                    collapsible: true,
                                                                                    collapsed: true,
																					hidden: me.isNmpArm,
                                                                                    refId: 'callDetailHDcmpCallCardEmergencyResult',
                                                                                   // margin: '5',
                                                                                    layout: {
                                                                                        type: 'vbox',
                                                                                        align: 'stretch'
                                                                                    },
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            layout: {
                                                                                                type: 'vbox'                                                                                                
                                                                                            },
                                                                                            defaults: {
                                                                                                margin: '5 0'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    layout: {
                                                                                                        type: 'hbox'                                                                                                
                                                                                                    },
                                                                                                    defaults: {
                                                                                                        margin: '0 5'
                                                                                                    },
                                                                                                    items:[
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: 'Диагноз:',
                                                                                                            width: 140,
                                                                                                            style: {
                                                                                                                textAlign: 'right'
                                                                                                            }   
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: '',
                                                                                                            refId: 'diagLabel',
                                                                                                            style: {
                                                                                                                fontWeight: 'bold'
                                                                                                            }                                                                                                           
                                                                                                        }                                                                                                       
                                                                                                    ]
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    layout: {
                                                                                                        type: 'hbox'                                                                                                
                                                                                                    },
                                                                                                    defaults: {
                                                                                                        margin: '0 5'
                                                                                                    },
                                                                                                    hidden: !getRegionNick().inlist(['perm']),
                                                                                                    items:[
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: 'Уточненный д-з: ',
                                                                                                            width: 140,
                                                                                                            style: {
                                                                                                                textAlign: 'right'
                                                                                                            }   
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: '',
                                                                                                            refId: 'udiagLabel',
                                                                                                            style: {
                                                                                                                fontWeight: 'bold'
                                                                                                            }                                                                                                           
                                                                                                        }                                                                                                       
                                                                                                    ]
                                                                                                    
                                                                                                },                                                                                          
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    layout: {
                                                                                                        type: 'hbox'                                                                                                
                                                                                                    },
                                                                                                    defaults: {
                                                                                                        margin: '0 5'
                                                                                                    },
                                                                                                    hidden: !getRegionNick().inlist(['perm']),
                                                                                                    items:[
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: 'Сопутствующий д-з: ',
                                                                                                            width: 140,
                                                                                                            style: {
                                                                                                                textAlign: 'right'
                                                                                                            }   
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: '',
                                                                                                            refId: 'sdiagLabel',
                                                                                                            style: {
                                                                                                                fontWeight: 'bold'
                                                                                                            }                                                                                                           
                                                                                                        }                                                                                                       
                                                                                                    ]
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    layout: {
                                                                                                        type: 'hbox'
                                                                                                    },
                                                                                                    defaults: {
                                                                                                        margin: '0 5'
                                                                                                    },
                                                                                                    items:[
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: 'Результат выезда:',
                                                                                                            width: 140,
                                                                                                            style: {
                                                                                                                textAlign: 'right'
                                                                                                            }
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: '',
                                                                                                            refId: 'resultLabel',
                                                                                                            style: {
                                                                                                                fontWeight: 'bold'
                                                                                                            }
                                                                                                        }
                                                                                                    ]
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    layout: {
                                                                                                        type: 'hbox'
                                                                                                    },
                                                                                                    defaults: {
                                                                                                        margin: '0 5'
                                                                                                    },
                                                                                                    items:[
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: 'МО госпитализации:',
                                                                                                            width: 140,
                                                                                                            style: {
                                                                                                                textAlign: 'right'
                                                                                                            }
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'label',
                                                                                                            text: '',
                                                                                                            refId: 'LpuHidLabel',
                                                                                                            style: {
                                                                                                                fontWeight: 'bold'
                                                                                                            }
                                                                                                        }
                                                                                                    ]
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'fieldset',
                                                                                            title: 'Выполненные услуги',                                                                                            
                                                                                            margin: '5 0 0 0',
                                                                                            layout: {
                                                                                                type: 'vbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'gridpanel',
                                                                                                    flex: 1,
                                                                                                    refId: 'callDetailHDcmpCallCardUslugaGrid',
                                                                                                    viewConfig: {
                                                                                                        loadMask: false,
                                                                                                    },
                                                                                                    store: new Ext.data.Store({
                                                                                                        extend: 'Ext.data.Store',
                                                                                                        autoLoad: false,
                                                                                                        fields: [
                                                                                                            {
                                                                                                                name: 'CmpCallCardUsluga_id',
                                                                                                                type: 'int'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'UslugaComplex_Code',
                                                                                                                type: 'string'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'UslugaComplex_Name',
                                                                                                                type: 'string'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'CmpCallCardUsluga_Kolvo',
                                                                                                                type: 'int'
                                                                                                            }
                                                                                                        ],
                                                                                                        proxy: {
                                                                                                            limitParam: 100,
                                                                                                            startParam: undefined,
                                                                                                            paramName: undefined,
                                                                                                            pageParam: undefined,
                                                                                                            type: 'ajax',
                                                                                                            url: '/?c=CmpCallCard&m=loadCmpCallCardUslugaGrid',
                                                                                                            reader: {
                                                                                                                type: 'json',
                                                                                                                successProperty: 'success',
                                                                                                                root: 'data'
                                                                                                            },
                                                                                                            actionMethods: {
                                                                                                                create: 'POST',
                                                                                                                read: 'POST',
                                                                                                                update: 'POST',
                                                                                                                destroy: 'POST'
                                                                                                            }
                                                                                                        }
                                                                                                    }),
                                                                                                    columns: [
                                                                                                        {
                                                                                                            dataIndex: 'UslugaComplex_Code',
                                                                                                            text: 'Код',
                                                                                                            width: 100
                                                                                                        },
                                                                                                        {
                                                                                                            dataIndex: 'UslugaComplex_Name',
                                                                                                            text: 'Наименование',
                                                                                                            flex: 1
                                                                                                        },
                                                                                                        {
                                                                                                            dataIndex: 'CmpCallCardUsluga_Kolvo',
                                                                                                            text: 'Кол-во',
                                                                                                            width: 100
                                                                                                        }
                                                                                                    ]
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'fieldset',
                                                                                            title: 'Использованные медикаменты',                                                                                            
                                                                                            margin: '5 0',
                                                                                            layout: {
                                                                                                type: 'vbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'gridpanel',
                                                                                                    flex: 1,
                                                                                                    refId: 'callDetailHDcmpCallCardDrugsGrid',
                                                                                                    viewConfig: {
                                                                                                        loadMask: false,
                                                                                                    },
                                                                                                    store: new Ext.data.Store({
                                                                                                        extend: 'Ext.data.Store',
                                                                                                        autoLoad: false,
                                                                                                        fields: [
                                                                                                            {
                                                                                                                name: 'CmpCallCardDrug_id',
                                                                                                                type: 'int'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'DrugNomen_Code',
                                                                                                                type: 'string'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'Drug_Name',
                                                                                                                type: 'string'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'CmpCallCardDrug_KolvoUnit',
                                                                                                                type: 'int'
                                                                                                            },
                                                                                                            {
                                                                                                                name: 'GoodsUnit_Name',
                                                                                                                type: 'string'
                                                                                                            },
                                                                                                        ],
                                                                                                        proxy: {
                                                                                                            limitParam: 100,
                                                                                                            startParam: undefined,
                                                                                                            paramName: undefined,
                                                                                                            pageParam: undefined,
                                                                                                            type: 'ajax',
                                                                                                            url: '/?c=CmpCallCard&m=loadCmpCallCardDrugList',
                                                                                                            reader: {
                                                                                                                type: 'json',
                                                                                                                successProperty: 'success',
                                                                                                                root: 'data'
                                                                                                            },
                                                                                                            actionMethods: {
                                                                                                                create: 'POST',
                                                                                                                read: 'POST',
                                                                                                                update: 'POST',
                                                                                                                destroy: 'POST'
                                                                                                            }
                                                                                                        }
                                                                                                    }),
                                                                                                    columns: [
                                                                                                        {
                                                                                                            dataIndex: 'DrugNomen_Code',
                                                                                                            text: 'Код',
                                                                                                            width: 100
                                                                                                        },
                                                                                                        {
                                                                                                            dataIndex: 'Drug_Name',
                                                                                                            text: 'Наименование',
                                                                                                            flex: 1
                                                                                                        },
                                                                                                        {
                                                                                                            dataIndex: 'CmpCallCardDrug_KolvoUnit',
                                                                                                            text: 'Кол-во',
                                                                                                            width: 100
                                                                                                        },
                                                                                                        {
                                                                                                            dataIndex: 'GoodsUnit_Name',
                                                                                                            text: 'Ед. измерения',
                                                                                                            width: 100
                                                                                                        }
                                                                                                    ]
                                                                                                }
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }]
                                                                        }
                                                                    ]
                                                                }
                                                            ]
                                                        },

                                                    ],
                                                    dockedItems: [
                                                        {
                                                            xtype: 'toolbar',
                                                            dock: 'bottom',
                                                            items: [
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'saveBtn',
                                                                    iconCls: 'save16',
                                                                    text: 'Сохранить'
                                                                },
                                                                //Вызовы с поводом «Решение старшего врача»
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'saveBtnAccept',
                                                                    iconCls: 'save16',
                                                                    text: 'Сохранить'
                                                                },
                                                                //Если Тип вызова «Отмена вызова»
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'cancelmodeAcceptBtn',
                                                                    iconCls: 'save16',
                                                                    text: 'Разрешить'
                                                                },
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'cancelmodeDiscardBtn',
                                                                    iconCls: 'cancel16',
                                                                    text: 'Отклонить'
                                                                },
                                                                //Если Тип вызова «Дублирующее»
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'doublemodeAcceptBtn',
                                                                    iconCls: 'save16',
                                                                    text: 'Разрешить'
                                                                },
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'doublemodeDiscardBtn',
                                                                    iconCls: 'cancel16',
                                                                    text: 'Отклонить'
                                                                },
                                                                //Если на спец бригаду
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'spteammodeAcceptBtn',
                                                                    iconCls: 'save16',
                                                                    text: 'Разрешить'
                                                                },
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'spteammodeDiscardBtn',
                                                                    iconCls: 'cancel16',
                                                                    text: 'Отклонить'
                                                                },
                                                                //Если признак наблюдения старшим врачом
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'hdObservmodeAcceptBtn',
                                                                    iconCls: 'save16',
                                                                    text: 'Ознакомлен'
                                                                },
                                                                //Отклоненные вызовы
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'denyAcceptBtn',
                                                                    iconCls: 'save16',
                                                                    text: 'Разрешить'
                                                                },
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'denyDiscardBtn',
                                                                    iconCls: 'cancel16',
                                                                    text: 'Запретить'
                                                                },
                                                                //
                                                                {xtype: 'tbfill'},
                                                                {
                                                                    xtype: 'button',
                                                                    refId: 'cancelBtn',
                                                                    iconCls: 'cancel16',
                                                                    text: 'Закрыть'
                                                                },
                                                            ]
                                                        }
                                                    ]
                                                },
                                                {
                                                    xtype: 'BaseForm',
                                                    id: me.id + '_callDetailFirstHDForm',
                                                    flex: 1,
                                                    title: 'Информация о первичном вызове',
                                                    header: false,
                                                    refId: 'callDetailFirstHD',
                                                    layout: 'fit',
                                                    overflowY: 'auto',
                                                    //hidden: true,
                                                    isLoading: false,
                                                    trackResetOnLoad: true,
                                                    items: [
                                                        {
                                                            xtype: 'container',
                                                            layout: 'auto',
                                                            items: [
                                                                {
                                                                    xtype: 'container',
                                                                    floatable: false,
                                                                    region: 'north',
                                                                    splitterResize: false,
                                                                    height: 69,
                                                                    layout: {
                                                                        align: 'middle',
                                                                        type: 'hbox'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'fieldcontainer',
                                                                            margin: '0 0 0 10',
                                                                            flex: 1,
                                                                            // disabled: true,
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    fieldLabel: 'Дата вызова',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 120,
                                                                                    format: 'd.m.Y',
                                                                                    plugins: [new Ux.InputTextMask('99.99.9999')],
                                                                                    name: 'CmpCallCard_prmDate',
                                                                                    readOnly: true
                                                                                },
                                                                                {
                                                                                    xtype: 'numberfield',
                                                                                    hideTrigger: true,
                                                                                    keyNavEnabled: false,
                                                                                    mouseWheelEnabled: false,
                                                                                    flex: 1,
                                                                                    fieldLabel: '№ вызова (за день)',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 120,
                                                                                    name: 'CmpCallCard_Numv',
                                                                                    readOnly: true
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'fieldcontainer',
                                                                            margin: '0 10 0 20',
                                                                            // disabled: true,
                                                                            flex: 1,
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'datefield',
                                                                                    name: 'CmpCallCard_prmTime',
                                                                                    fieldLabel: 'Время поступления вызова',
                                                                                    format: 'H:i:s',
                                                                                    hideTrigger: true,
                                                                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
                                                                                    plugins: [new Ux.InputTextMask('99:99:99')],
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 170,
                                                                                    readOnly: true
                                                                                },
                                                                                {
                                                                                    xtype: 'textfield',
                                                                                    hideTrigger: true,
                                                                                    keyNavEnabled: false,
                                                                                    mouseWheelEnabled: false,
                                                                                    flex: 1,
                                                                                    fieldLabel: '№ вызова (за год):',
                                                                                    labelAlign: 'right',
                                                                                    labelWidth: 170,
                                                                                    name: 'CmpCallCard_Ngod',
                                                                                    readOnly: true
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            value: '',
                                                                            name: 'ARMType'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            value: 0,
                                                                            name: 'CmpCallCard_id'
                                                                        },
                                                                        {
                                                                            name: 'CmpLpu_Name',
                                                                            value: '',
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            name: 'CmpLpu_id',
                                                                            value: 0,
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            name: 'Person_id',
                                                                            value: 0,
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            name: 'Person_isOftenCaller',
                                                                            value: 1,
                                                                            xtype: 'hidden'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_Ktov'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_CallLtd'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'CmpCallCard_CallLng'
                                                                        },
                                                                        {
                                                                            xtype: 'hidden',
                                                                            name: 'EmergencyTeam_id'
                                                                        }
                                                                    ]
                                                                },
                                                                {
                                                                    xtype: 'container',
                                                                    region: 'center',
                                                                    layout: {
                                                                        type: 'auto'
                                                                    },
                                                                    items: [
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    layout: {
                                                                                        align: 'stretch',
                                                                                        type: 'vbox'
                                                                                    },
                                                                                    title: 'Место вызова',
                                                                                    //disabled: getRegionNick().inlist(['ufa']),
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'fieldcontainer',
                                                                                            margin: '0 5',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                align: 'stretch',
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'dCityCombo',
                                                                                                    labelWidth: 120,
                                                                                                    maxWidth: 300,
                                                                                                    flex: 2,
                                                                                                    disabled: getRegionNick().inlist(['ufa'])
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'swStreetsSpeedCombo',
                                                                                                    name: 'dStreetsCombo',
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 35,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    fieldLabel: 'Ул.',
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'swStreetsSpeedCombo',
                                                                                                    name: 'secondStreetCombo',
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 35,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    fieldLabel: 'Ул.',
                                                                                                    enableKeyEvents: true,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    plugins: [new Ux.Translit(true, true)],
                                                                                                    fieldLabel: 'Дом',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Dom',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    plugins: [new Ux.Translit(true, true)],
                                                                                                    fieldLabel: 'Корп',
                                                                                                    enforceMaxLength: true,
                                                                                                    maxLength: 5,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    // hidden: (!getRegionNick().inlist(['ufa', 'krym', 'kz'])),
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Korp',
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'fieldcontainer',
                                                                                            margin: '4 5 0',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                align: 'stretch',
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    //maskRe: /[0-9:]/,
                                                                                                    enforceMaxLength: true,
                                                                                                    maxLength: 5,
                                                                                                    plugins: [new Ux.Translit(true, true)],
                                                                                                    fieldLabel: 'Кв.',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Kvar',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 120,
                                                                                                    maxWidth: 300,
                                                                                                    flex: 2,
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    maskRe: /[0-9:]/,
                                                                                                    fieldLabel: 'Пд.',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Podz',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    maskRe: /[0-9:]/,
                                                                                                    fieldLabel: 'Эт.',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Etaj',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    fieldLabel: 'Код',
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Kodp',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    enableKeyEvents: true,
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'fieldcontainer',
                                                                                            margin: '4 5 10',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                align: 'stretch',
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swCmpCallPlaceType',
                                                                                                    name: 'CmpCallPlaceType_id',
                                                                                                    fieldLabel: 'Тип места',
                                                                                                    labelAlign: 'right',
                                                                                                    allowBlank: false,
                                                                                                    value: 1,
                                                                                                    labelWidth: 120,
                                                                                                    maxWidth: 300,
                                                                                                    flex: 2,
                                                                                                    triggerClear: true,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    hideTrigger: true,
                                                                                                    displayTpl: '<tpl for="."> {CmpCallPlaceType_Code}. {CmpCallPlaceType_Name} </tpl>',
                                                                                                    tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">' +
                                                                                                        '<font color="red">{CmpCallPlaceType_Code}</font> {CmpCallPlaceType_Name}' +
                                                                                                        '</div></tpl>'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'swCmpCallerTypeCombo',
                                                                                                    name: 'CmpCallerType_id',
                                                                                                    labelWidth: 35,
                                                                                                    triggerClear: true,
                                                                                                    hideTrigger: true,
                                                                                                    autoFilter: false,
                                                                                                    forceSelection: false,
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    autoSelect: false,
                                                                                                    labelAlign: 'right',
                                                                                                    flex: 1,
                                                                                                    fieldLabel: 'Выз.',
                                                                                                    minChars: 2,
                                                                                                    tpl: '<tpl for="."><div class="enlarged-font x-boundlist-item">' +
                                                                                                        '{CmpCallerType_Name}' +
                                                                                                        '</div></tpl>'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'trigger',
                                                                                                    fieldLabel: 'Тел.',
                                                                                                    enableKeyEvents: true,
                                                                                                    maskRe: /[0-9:]/,
                                                                                                    labelAlign: 'right',
                                                                                                    name: 'CmpCallCard_Telf',
                                                                                                    disabled: getRegionNick().inlist(['ufa']),
                                                                                                    labelWidth: 35,
                                                                                                    flex: 1,
                                                                                                    cls: 'x-form-table-div',
                                                                                                    triggerCls: 'x-form-eye-trigger-default',
                                                                                                    inputType: 'password'
                                                                                                }
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    layout: {
                                                                                        type: 'vbox',
                                                                                        align: 'stretch'
                                                                                    },
                                                                                    flex: 1,
                                                                                    title: 'Пациент',
                                                                                    // disabled: true,
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                type: 'vbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    flex: 1,
                                                                                                    margin: '0 5',
                                                                                                    layout: {
                                                                                                        type: 'hbox',
                                                                                                        align: 'stretch'
                                                                                                    },
                                                                                                    items: [
                                                                                                        {
                                                                                                            xtype: 'textfield',
                                                                                                            plugins: [new Ux.Translit(true, true)],
                                                                                                            labelWidth: 120,
                                                                                                            maxWidth: 300,
                                                                                                            flex: 2,
                                                                                                            fieldLabel: 'Фамилия',
                                                                                                            labelAlign: 'right',
                                                                                                            name: 'Person_SurName',
                                                                                                            enableKeyEvents: true
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'textfield',
                                                                                                            plugins: [new Ux.Translit(true, true)],
                                                                                                            flex: 1,
                                                                                                            labelWidth: 35,
                                                                                                            fieldLabel: 'Имя',
                                                                                                            labelAlign: 'right',
                                                                                                            name: 'Person_FirName',
                                                                                                            enableKeyEvents: true
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'textfield',
                                                                                                            plugins: [new Ux.Translit(true, true)],
                                                                                                            flex: 1,
                                                                                                            labelWidth: 70,
                                                                                                            fieldLabel: 'Отч.',
                                                                                                            labelAlign: 'right',
                                                                                                            name: 'Person_SecName'
                                                                                                        }
                                                                                                    ]
                                                                                                }
                                                                                                /*,{
                                                                                                 xtype: 'container',
                                                                                                 //width: 150,
                                                                                                 margin: '0 10',
                                                                                                 layout: {
                                                                                                 type: 'vbox'
                                                                                                 },
                                                                                                 items: [
                                                                                                 {
                                                                                                 xtype: 'button',
                                                                                                 name: 'clearPersonFields',
                                                                                                 text: 'Сброс',
                                                                                                 iconCls: 'delete16'
                                                                                                 },
                                                                                                 {
                                                                                                 xtype: 'button',
                                                                                                 name: 'searchPersonBtn',
                                                                                                 text: 'Поиск',
                                                                                                 iconCls: 'search16',
                                                                                                 margin: '5 0'
                                                                                                 },
                                                                                                 {
                                                                                                 xtype: 'button',
                                                                                                 name: 'unknowPersonBtn',
                                                                                                 text: 'Неизвестен',
                                                                                                 iconCls: 'warning16'
                                                                                                 }
                                                                                                 ]
                                                                                                 }*/
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            margin: '4 5 10',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                /* {
                                                                                                     xtype: 'hidden',
                                                                                                     name: 'Person_Age'
                                                                                                 },
                                                                                                 */
                                                                                                {
                                                                                                    //xtype: 'textfield',
                                                                                                    xtype: 'hidden',
                                                                                                    name: 'Person_Birthday'
                                                                                                },
                                                                                                {
                                                                                                    //xtype: 'textfield',
                                                                                                    xtype: 'hidden',
                                                                                                    name: 'Person_AgeInt'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'container',
                                                                                                    flex: 2,
                                                                                                    maxWidth: 300,
                                                                                                    layout: {
                                                                                                        type: 'hbox',
                                                                                                        align: 'stretch'
                                                                                                    },
                                                                                                    items: [
                                                                                                        {
                                                                                                            xtype: 'numberfield',
                                                                                                            fieldLabel: 'Возраст',
                                                                                                            hideTrigger: true,
                                                                                                            allowDecimals: false,
                                                                                                            allowNegative: false,
                                                                                                            enableKeyEvents: true,
                                                                                                            labelAlign: 'right',
                                                                                                            //name: 'Person_AgeText',
                                                                                                            name: 'Person_Age',
                                                                                                            flex: 3,
                                                                                                            labelWidth: 120,
                                                                                                            margin: '0 5 0 0',
                                                                                                        },
                                                                                                        {
                                                                                                            xtype: 'swDAgeUnitCombo',
                                                                                                            tabIndex: 26,
                                                                                                            //value: 0,
                                                                                                            name: 'ageUnit_id',
                                                                                                            displayField: 'ageUnit_name',
                                                                                                            enableKeyEvents : true,
                                                                                                            //bigFont: true,
                                                                                                            valueField: 'ageUnit_id',
                                                                                                            flex: 1,
                                                                                                            triggerClear: false
                                                                                                        }
                                                                                                    ]
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'sexCombo',
                                                                                                    enableKeyEvents: true,
                                                                                                    tabIndex: 26,
                                                                                                    name: 'Sex_id',
                                                                                                    bigFont: false,
                                                                                                    flex: 1,
                                                                                                    labelAlign: 'right',
                                                                                                    hideTrigger: true,
                                                                                                    labelWidth: 35
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    fieldLabel: '№ полиса',
                                                                                                    labelAlign: 'right',
                                                                                                    flex: 1,
                                                                                                    name: 'Polis_Num',
                                                                                                    labelWidth: 70
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            layout: {
                                                                                                type: 'hbox'
                                                                                            },
                                                                                            items: [
                                                                                                pacientSearchResText
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            flex: 1,
                                                                            defaultAlign: 'left',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    xtype: 'fieldset',
                                                                                    layout: {
                                                                                        align: 'stretch',
                                                                                        type: 'vbox'
                                                                                    },
                                                                                    flex: 1,
                                                                                    title: 'Вызов',
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            // disabled: true,
                                                                                            flex: 1,
                                                                                            margin: '0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swCmpCallTypeCombo',
                                                                                                    name: 'CmpCallType_id',
                                                                                                    flex: 1,
                                                                                                    labelWidth: 120,
                                                                                                    fieldLabel: 'Тип вызова'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'cmpReasonCombo',
                                                                                                    name: 'CmpReason_id',
                                                                                                    flex: 1,
                                                                                                    allowBlank: false,
                                                                                                    forceSelection: true,
                                                                                                    //autoFilter: false
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swCmpCallTypeIsExtraCombo',
                                                                                                    fieldLabel: 'Вид вызова',
                                                                                                    allowBlank: false,
                                                                                                    enableKeyEvents: true,
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 120,
                                                                                                    flex: 1,
                                                                                                    name: 'CmpCallCard_IsExtra',
                                                                                                    listConfig: {}
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'textfield',
                                                                                                    name: 'CmpCallCard_Urgency',
                                                                                                    width: 90,
                                                                                                    fieldLabel: 'Ср',
                                                                                                    enableKeyEvents: true,
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 35,
                                                                                                    hidden: me.isNmpArm
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'checkbox',
                                                                                                    flex: 1,
                                                                                                    name: 'CmpCallCard_IsPoli',
                                                                                                    boxLabel: 'Вызов передан в поликлинику по телефону (рации)',
                                                                                                    margin: '0 0 0 125',
                                                                                                    hidden: me.isNmpArm
                                                                                                }

                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'lpuLocalCombo',
                                                                                                    name: 'Lpu_ppdid',
                                                                                                    labelWidth: 120,
                                                                                                    flex: 1,
                                                                                                    bigFont: false,
                                                                                                    validateOnBlur: false,
                                                                                                    fieldLabel: 'МО ' +
                                                                                                        ' (НМП)'
                                                                                                },
                                                                                                {
                                                                                                    fieldLabel: 'Служба НМП',
                                                                                                    allowBlank: true,
                                                                                                    flex: 1,
                                                                                                    xtype: 'selectNmpCombo',
                                                                                                    name: 'MedService_id',
                                                                                                    isClose: 1
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'checkbox',
                                                                                                    flex: 1,
                                                                                                    margin: '0 0 0 125',
                                                                                                    name: 'CmpCallCard_IsPassSSMP',
                                                                                                    hidden: (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) || me.isNmpArm,
                                                                                                    boxLabel: 'Вызов передан в другую ССМП по телефону (рации)',
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'vbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'lpuAllLocalCombo',
                                                                                                    name: 'Lpu_smpid',
                                                                                                    flex: 1,
                                                                                                    fieldLabel: 'МО передачи (СМП)',
                                                                                                    labelWidth: 120,
                                                                                                    hidden: true
                                                                                                },
                                                                                                (getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? smpUnitsNestedCombo : smpRegionUnitsCombo,

                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    xtype: 'swEmergencyTeamSpecCombo',
                                                                                                    labelAlign: 'right',
                                                                                                    flex: 1,
                                                                                                    labelWidth: 120,
                                                                                                    name: 'EmergencyTeamSpec_id'
                                                                                                },
                                                                                                {
                                                                                                    xtype: 'transFieldDelbut',
                                                                                                    fieldLabel: 'Бригада',
                                                                                                    labelAlign: 'right',
                                                                                                    labelWidth: 100,
                                                                                                    flex: 1,
                                                                                                    translate: false,
                                                                                                    name: 'EmergencyTeam_Num',
                                                                                                    maskRe: /[0-9:]/
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'container',
                                                                                            flex: 1,
                                                                                            margin: '4 5 0 5',
                                                                                            layout: {
                                                                                                type: 'hbox',
                                                                                                align: 'stretch'
                                                                                            },
                                                                                            items: [
                                                                                                {
                                                                                                    labelWidth: 120,
                                                                                                    flex: 1,
                                                                                                    fieldLabel: "Диспетчер",
                                                                                                    name: "DPMedPersonal_id",
                                                                                                    cls: 'loadAfter',
                                                                                                    xtype: "swmedpersonalcombo",
                                                                                                    enableKeyEvents: true
                                                                                                }
                                                                                            ]
                                                                                        },
                                                                                        {
                                                                                            xtype: 'textareafield',
                                                                                            flex: 1,
                                                                                            margin: '4 5 10',
                                                                                            minHeight: 100,
                                                                                            labelWidth: 120,
                                                                                            fieldLabel: 'Доп. информация:',
                                                                                            enableKeyEvents: true,
                                                                                            labelAlign: 'right',
                                                                                            name: 'CmpCallCard_Comm'
                                                                                        }
                                                                                    ]
                                                                                }
                                                                            ]
                                                                        },
                                                                        {
                                                                            xtype: 'container',
                                                                            margin: '0 10',
                                                                            flex: 1,
                                                                            defaultAlign: 'left',
                                                                            layout: {
                                                                                align: 'stretch',
                                                                                type: 'vbox'
                                                                            },
                                                                            items: [
                                                                                {
                                                                                    //история статусов

                                                                                    xtype: 'fieldset',
                                                                                    title: 'История вызова',
                                                                                    refId: 'callDetailHDcmpCallCardStatusHistory',
                                                                                    margin: '5',
                                                                                    layout: {
                                                                                        type: 'vbox',
                                                                                        align: 'stretch'
                                                                                    },
                                                                                    items: [
                                                                                        {
                                                                                            xtype: 'gridpanel',
                                                                                            flex: 1,
                                                                                            refId: 'callDetailHDcmpCallCardStatusHistoryGrid',
                                                                                            viewConfig: {
                                                                                                loadMask: false,
                                                                                            },
                                                                                            //store: Ext.data.StoreManager.lookup('common.HeadDoctorWP.store.CmpCallCardStatusHistoryStore'),
                                                                                            store: new Ext.data.Store({
                                                                                                extend: 'Ext.data.Store',
                                                                                                autoLoad: false,
                                                                                                storeId: 'cmpCallCardStatusHistoryFirstStore',
                                                                                                fields: [
                                                                                                    {
                                                                                                        name: 'EventDT',
                                                                                                        type: 'datetime'
                                                                                                    },
                                                                                                    {
                                                                                                        name: 'CmpCallCardEventType_Name',
                                                                                                        type: 'string'
                                                                                                    },
                                                                                                    {
                                                                                                        name: 'pmUser_FIO',
                                                                                                        type: 'string'
                                                                                                    },
                                                                                                    {
                                                                                                        name: 'EventValue',
                                                                                                        type: 'string'
                                                                                                    }
                                                                                                ],
                                                                                                proxy: {
                                                                                                    limitParam: 100,
                                                                                                    startParam: undefined,
                                                                                                    paramName: undefined,
                                                                                                    pageParam: undefined,
                                                                                                    //noCache:false,
                                                                                                    type: 'ajax',
                                                                                                    url: '/?c=CmpCallCard4E&m=loadCmpCallCardEventHistory',
                                                                                                    reader: {
                                                                                                        type: 'json',
                                                                                                        successProperty: 'success',
                                                                                                        root: 'data'
                                                                                                    },
                                                                                                    actionMethods: {
                                                                                                        create: 'POST',
                                                                                                        read: 'POST',
                                                                                                        update: 'POST',
                                                                                                        destroy: 'POST'
                                                                                                    }
                                                                                                }
                                                                                            }),
                                                                                            columns: [
                                                                                                {
                                                                                                    dataIndex: 'EventDT',
                                                                                                    text: 'Дата и время',
                                                                                                    width: 140
                                                                                                },
                                                                                                {
                                                                                                    dataIndex: 'CmpCallCardEventType_Name',
                                                                                                    text: 'Событие',
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    dataIndex: 'pmUser_FIO',
                                                                                                    text: 'ФИО',
                                                                                                    flex: 1
                                                                                                },
                                                                                                {
                                                                                                    dataIndex: 'EventValue',
                                                                                                    text: 'Значение события',
                                                                                                    flex: 1
                                                                                                }
                                                                                            ]
                                                                                        }
                                                                                    ]
                                                                                }]
                                                                        }
                                                                    ]
                                                                }
                                                            ]
                                                        },

                                                    ],
                                                    dockedItems: [
                                                        {
                                                            xtype: 'toolbar',
                                                            dock: 'bottom',
                                                            items: [
                                                                /*{
                                                                 xtype: 'button',
                                                                 refId: 'saveBtn',
                                                                 iconCls: 'save16',
                                                                 text: 'Сохранить'
                                                                 },*/
                                                                {xtype: 'tbfill'},
                                                                /*{
                                                                 xtype: 'button',
                                                                 refId: 'cancelBtn',
                                                                 iconCls: 'cancel16',
                                                                 text: 'Закрыть'
                                                                 },*/
                                                            ]
                                                        }
                                                    ]
                                                },
                                                Ext.create('common.HeadDoctorWP.swWialonTrackPlayerPanel', {
                                                    id: me.id + '_callTrack',
                                                    title: 'Просмотр трека',
                                                    hidden: true,
                                                })
                                            ]
                                        },
                                    ]
                                },
                                {
                                    xtype: 'swsmpmappanel',
                                    hidden: true,
                                    flex: 1,
                                    toggledButtons: true,
                                    header: false,
                                    callMarker: null,
                                    showCloseHelpButtons: false,
                                    title: 'Карта'
                                }
                            ],
                            dockedItems: [
                                {
                                    xtype: 'toolbar',
                                    dock: 'top',
                                    flex: 1,
                                    items: [
                                        {
                                            xtype: 'button',
                                            text: 'Карта',
                                            iconCls: 'lpu-regiontype16 ',
                                            refId: 'mapBtnHD'
                                        },
										{
                                            xtype: 'button',
                                            text: 'Отчеты',
                                            iconCls: 'reports16 ',
                                            refId: 'statisticReports'
                                        },
                                        {
                                            xtype: 'button',
                                            text: 'Журнал активов в поликлинику',
                                            iconCls: 'reports16',
                                            refId: 'aktivSmp',
											hidden: me.isNmpArm
                                        },
                                        {
                                            xtype: 'button',
                                            text: 'Наряды',
                                            iconCls: 'eph-record16',
                                            refId: 'setEmergencyTeamDutyTime',
											hidden: me.isNmpArm
                                        },
                                        /* #110104
                                        {
                                            xtype: 'button',
                                            text: 'Отчеты',
                                            iconCls: 'documents16',
                                            handler: function () {

                                            }
                                        }, {
                                            xtype: 'button',
                                            text: 'Статистика',
                                            iconCls: 'reports16',
                                            handler: function () {

                                            }
                                        }
                                        */
                                    ]
                                },
                                {
                                    xtype: 'toolbar',
                                    dock: 'bottom',
                                    margin: '0 0 20 0',
                                    items: [
                                        {xtype: 'tbfill'},
                                        {
                                            xtype: 'button',
                                            text: 'Помощь',
                                            iconCls: 'help16',
                                            tabIndex: 30,
                                            handler: function () {
                                                ShowHelp(this.up('window').title);
                                            }
                                        },
                                        {
                                            xtype: 'button',
                                            text: 'Закрыть',
                                            refId: 'closeMapWin'
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'container',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'stretch'
                            },
                           // id: 'callsListHD',
                            title: 'Журнал вызовов',
                            items: [
                                Ext.create('sw.CmpCallsList', {armtype: 'smpheaddoctor'})
                            ],
                            listeners: {
                                activate: function (tab) {
                                    var journal = tab.child();
                                    if(journal.store) journal.getStore().load();
                                }
                            }
                        },
						{
							xtype: 'container',
							flex: 1,
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
                            refId: 'IsCallControllTab',
							title: 'Вызовы на контроле',

							items: [
								Ext.create('sw.CmpCallsUnderControlList', {armtype: 'smpheaddoctor'})
							]
						},
                        {
                            xtype: 'container',
                            flex: 1,
                            layout: {
                                type: 'hbox',
                                align: 'stretch'
                            },
							hidden: getGlobalOptions().smp_show_expert_tab_in_headdocwp != 1 || me.isNmpArm  ,
							title: 'Экспертная оценка',
							items: [
								Ext.create('sw.CmpCallsListExpertMark', {armtype: 'smpheaddoctor'})
							],
							listeners: {
								activate: function (tab) {
									//var journal = tab.child();
									//if(journal.store) journal.getStore().load();
								}
							}
						}
                    ]
                })
            ]

        });

        me.callParent(arguments);
    },



});
