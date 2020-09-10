/*
 * Новый АРМ диспетчера направлений
 * это ВИД не писать сюда вещи, не относящиеся к виду
 */

Ext.define('common.DispatcherDirectWP.swDispatcherDirectWorkPlace', {
	extend: 'Ext.window.Window',
	alias: 'widget.swDispatcherDirectWorkPlace',
	maximized: true,
	constrain: true,
	refId: 'smpdispatchdirect',
	title: 'АРМ диспетчера направлений СМП',
	//width: 1000,
	renderTo: Ext.getCmp('inPanel').body,
	closable: true,
	defaultFocus: 'callsGrid',
	baseCls: 'arm-window',
	header: false,
	onEsc: Ext.emptyFn,
	layout: {
		type: 'fit'
	},
	id:'swDispatcherDirectWorkPlace',
	initComponent: function() {
		var me = this,
			storeTeams = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.EmergencyTeamStore'),
			storeCalls = Ext.data.StoreManager.lookup('common.DispatcherDirectWP.store.CmpCallsStore');
		
		//@todo ОБЯЗАТЕЛЬНО разобраться с веткой регионов!
		if(getRegionNick().inlist(['ufa', 'krym'])){
			me.topToolbar = Ext.create('Ext.toolbar.Toolbar',{
				dock: 'top',
				refId: 'DDTopToolbar',
				items: [
					{
						xtype: 'button',
						text: 'Добавить',
						iconCls: 'add16',
						tabIndex: 1,
						handler: function() {
							getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
								swDispatcherCallWorkPlaceInstance_modal: true
							});
						}
					}
				]
			})
		}

		
		me.bottomToolBar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'bottom',
			layout: 'fit',
			items: [
				{
					xtype: 'container',
					dock: 'bottom',
					refId: 'bottomButtons',
					margin: '5 4',
					layout: {
						align: 'top',
						pack: 'end',
						type: 'hbox'
					},
					items: [
						{
							xtype: 'container',
							flex: 1,
							items: [								
								//comps.leftButtons
							]
						},
						{
							xtype: 'container',
							layout: {
								type: 'hbox',
								align: 'middle'
							},
							items: [
								 {
									xtype: 'button',
									text: 'Помощь',
									iconCls   : 'help16',
									tabIndex: 30,
									handler   : function()
									{
										ShowHelp(this.up('window').title);								
									}
								}
							]
						}
						
					]
				}
			]
		});
		//грид групп бригад(фильтр)
		this.EmergencyTeamDutyTimeGrid = Ext.create('Ext.grid.Panel', {
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty: false
			},
			flex: 1,
			height: 200,
			animCollapse: false,
			collapsed: true,
			header: false,
			stripeRows: true,
			refId: 'emergencyTeamGroupFilterGrid',
			store: Ext.data.StoreManager.lookup('stores.smp.GeoserviceTransportGroupStore'),
			columns: [
				{dataIndex: 'visible', text: '', width: 55, xtype: 'checkcolumn', hideable: false, sortable: false,
					renderTpl: [
						'<div id="{id}-titleEl" role="presentation" {tipMarkup}class="', Ext.baseCSSPrefix, 'column-header-inner',
						'<tpl if="empty"> ', Ext.baseCSSPrefix, 'column-header-inner-empty</tpl>">',
						'<div class="customCheckAll">',
						'<span class="checkedall">&nbsp;</span>',
						'</div>',
						'<span id="{id}-textEl" class="', Ext.baseCSSPrefix, 'column-header-text',
						'{childElCls}">',
						'{text}',
						'</span>',
						'<tpl if="!menuDisabled">',
						'<div id="{id}-triggerEl" role="presentation" class="', Ext.baseCSSPrefix, 'column-header-trigger',
						'{childElCls}"></div>',
						'</tpl>',
						'</div>',
						'{%this.renderContainer(out,values)%}'
					],
					listeners: {
					}
				},
				{dataIndex: 'id', text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'name', text: 'Наименование', flex: 1, hidden: false, hideable: false},
			]
		});

		//грид бригады
		this.TeamsView = Ext.create('Ext.view.View', {
			id: 'teamGrid',
			store: storeTeams,
			xtype: 'dataview',
			cls: 'teams-panel',
			overflowY: 'scroll',
			loadingText: 'Загрузка',
			loadMask: false,
			flex: 1,
			preserveScrollOnRefresh: true,
			itemSelector: 'div.teams-wrap',
			tpl: [
				'<tpl for=".">',
				'<tpl if="this.isSeparator(EmergencyTeamStatus_id)">',
				'<div class="teams-separator"></div>',
				'</tpl>',
				'<div class="teams-wrap ',
				'<tpl if="EmergencyTeam_isOnline">',
				'{EmergencyTeam_isOnline}',
				'</tpl>',
				'" style="color:{EmergencyTeamStatus_Color}">',
				'<div class="teams-text">',
				'<div class="left">',
				'<div class="top">',
				'<tpl if="EmergencyTeam_Num">',
				'<p>{EmergencyTeam_Num}</p>',
				'</tpl>',
				'</div>',
				'<div class="middle">',
				'<tpl if="EmergencyTeamSpec_Code">',
				'<p>{EmergencyTeamSpec_Code}</p>',
				'</tpl>',
				'</div>',
				'<div class="bottom">',
				'<tpl if="EmergencyTeamDurationText">',
				'<span>{EmergencyTeamDurationText}</span></br>',
				'</tpl>',
				'</div>',
				'</div>',
				'<div class="center">',
				'<tpl if="EmergencyTeamStatus_Name">',
						'<p>',
							'{EmergencyTeamStatus_Name}',
							'<tpl if="CmpCallCard_Numv">',
								' К/Т {CmpCallCard_Numv} ({CmpCallCard_Ngod})',
							'</tpl>',
						'</p>',
				'</tpl>',
				'<tpl if="Person_Fin">',
				'<p>{Person_Fin} + <span>{medPersonCount}</span> ({EmergencyTeamBuildingName}) </p>',
				'</tpl>',
				'{[ this.getDateFinish(values) ]}',
				'<tpl if="GeoserviceTransport_name">',
					'{GeoserviceTransport_name}',	
				'</tpl>',
				'</div>',
				'<div class="right">',
				'<div class="cell-cmpcc-moreinfo"></div>',
				'</div>',
				'</div>',
				'</div>',
				'</tpl>',
				{
					disableFormats: true,
					isSeparator: function(id) {
						var parent = me.TeamsView;
						if (id == 0 || id == 14) {
							parent.separatorPos = false;
						}
						else
						{
							if (parent.separatorPos == false)
							{
								parent.separatorPos = true;
								return 'true'
							}
						}
					}
				},
				{
					getDateFinish: function(val) {
						if (val.EmergencyTeamDuty_DTFinish) {
							var dateF = Ext.Date.parse(val.EmergencyTeamDuty_DTFinish, "Y-m-d H:i:s"),
									delta = dateF - new Date(),
									hours = Math.floor(delta / 3600000);

							if (hours < 24 && hours >= 0) {
								return '<p>до ' + Ext.Date.format(dateF, 'H:i') + '</p>';
							}
						}
					}
				}
			]
		});

		//грид вызовы
		var separatorIsSet = false;
		this.CallsView = Ext.create('Ext.view.View', {
			id: 'callsGrid',
			store: storeCalls,
			preserveScrollOnRefresh: true,
			loadingText: 'Загрузка',
			loadMask: false,
			xtype: 'dataview',
			cls: 'calls-panel',
			overflowY: 'scroll',
			flex: 1,
			loadingText: 'Загрузка вызовов',
			itemSelector: 'div.calls-wrap',
			tpl: [
				'{%separatorIsSet=false%}',
				'<tpl for=".">',
					'<tpl if="this.insertSeparator(CmpCallCardStatusType_id)">',
						'<div class="teams-separator">Остальные вызовы</div>',
					'</tpl>',
					'<tpl if="CmpCallCard_BoostTime">',
					'</tpl>',
					'<div class="calls-wrap {[ this.getBoostBorder(values) ]}">',
						'<div class="calls-text">',
							'<div class="left">',
								'<p>{Person_FIO}',
									'<tpl if="Person_Age &gt; 0">',
										', {Person_Age} лет',
									'</tpl>',
								'</p>',
								'<tpl if="CmpReason_Name">',
									'<p>{[ this.getReasonName(values) ]}</p>',
								'</tpl>',
							'</div>',
							'<div class="center">',
								'<div class="number">',
									'<p>{CmpCallCard_Numv} / <span>{CmpCallCard_Ngod} / {[ this.getDate(values) ]} </span></p>',
								'</div>',
								'<tpl if="Adress_Name">',
									'<p>{Adress_Name}</p>',
								'</tpl>',
							'</div>',
							'<div class="cell-cmpcc-moreinfo"></div>',
							'<tpl if="CmpCallCardStatusType_id != 1">',
								'<div class="right">',
									'<h2>БР: <tpl if="EmergencyTeam_Num"> {EmergencyTeam_Num}</tpl></h2>',
								'</div>',
							'</tpl>',
							'<div class="right">',
								'<div class="top {[ this.getUrgencyClass(values) ]}">',
									'<tpl if="SendLpu_Nick">',									
										'<p>{PPD_WaitingTime}</p>',
										'<p>{SendLpu_Nick}</p>',
									'<tpl else>',
										'<tpl if="CmpCallCard_Urgency">',
											'<p>CP. {[ this.getUrgencyVal(values) ]}</p>',
										'</tpl>',
									'</tpl>',
								'</div>',
								'<div class="bottom">',
									'<tpl if="SendLpu_Nick == null">',		
										'<tpl if="CmpCallCard_prmDate">',
											'<p class="cccounter"> {[ this.getTime(values) ]}</p>',
										'</tpl>',
									'</tpl>',
								'</div>',
							'</div>',
						'</div>',
					'</div>',
				'</tpl>',
				{
					getUrgencyClass: function(val) {
						var urgency = this.getUrgencyVal(val);
						switch (urgency) {
							case 1:
							case 2:
								return 'urgency-1-2';
								break;
							case 3:
							case 4:
								return 'urgency-3-4';
								break;
							case 5:
							case 6:
								return 'urgency-5-6';
								break;
							case 7:
								return 'urgency-7';
								break;
							default:
								break;
						}
					},
					getTime: function(val) {
						var delta = new Date() - new Date(Date.parse(val.CmpCallCard_prmDate)),
								appendix = delta % 3600000,
								hours = chkzero(Math.floor(delta / 3600000)),
								min = chkzero(Math.floor(appendix / 60000)),
								sec = chkzero(Math.floor((appendix % 100000 / 1000) % 60)),
								roundSec = sec>30?'30':'00',
								result = '';
						
						function chkzero(num) {
							var str = num.toString();
							if (str.length == 1)
								return '0' + str
							else
								return str
						}

						if (hours > 24) {
							return ''
						}
						else {
							if (hours > 0) {
								result = hours + ':' + min;//+':'+sec;
							}
							else {
								result = min + ':' + roundSec;
							}
						}
						return result
					},
					insertSeparator: function(CmpCallCardStatusType_id){
						// Устанавливаем разделитель если у нас загружены вызовы по кнопке "Показать все"
						if ( !separatorIsSet && CmpCallCardStatusType_id != 1 ) {
							separatorIsSet = true;
							return true;
						}
						return false;
					}
				},
				{
					getUrgencyVal: function(val) {
						var delta = new Date() - new Date(Date.parse(val.CmpCallCard_prmDate)),
								mins = Math.floor(delta / 60000),
								updateTimeMinutes = 15,
								result = Math.floor(mins / updateTimeMinutes),
								urgencyVal = val.CmpCallCard_Urgency - result;

						//return val.CmpCallCard_Urgency+'-'+result;
						if (val.Urgency != '') return val.Urgency
						if (urgencyVal > 0) {
							return urgencyVal
						}
						return 1
					}
				},
				{
					getDate: function(val) {
						var dateF = new Date(Date.parse(val.CmpCallCard_prmDate));
						return Ext.Date.format(dateF, 'H:i d-m-Y')
					}
				},
				{
					getReasonName: function(val) {
						if (val.CmpSecondReason_Name) {
							return val.CmpSecondReason_Name
						}
						else {
							return val.CmpReason_Name
						}
					}
				},
				{
					getBoostBorder: function(val) {
						var diffDeltaTime = new Date() - val.CmpCallCard_BoostTime,
								mins = Math.floor(diffDeltaTime / 60000);

						if (mins < 5) {
							return 'boostCall'
						}
					}
				}
			],
			listeners: {
				render: function(c) {

				}
			}
		});
		
		this.mapPanel = Ext.create('sw.Smp.MapPanel', {
			height: 0,
			toggledButtons: true,
			header: false,
			showCloseHelpButtons: false,
			callMarker: null,
			layout: {
				align: 'stretch',
				pack: 'center',
				type: 'hbox'
			}
		});
		
		this.EmergencyTeamSortingTypeRadioGroup = Ext.create('Ext.form.RadioGroup',{
			columns: 2,
			vertical: true,
			fieldLabel: 'Сортировать по времени',
			labelWidth: 150,
			margin: '0 0 0 10',
			name: 'EmergencyTeamSortingTypeRadioGroup',
			items: [
				{ boxLabel: 'доезда', name: 'EmergencyTeamSortingTypeRadioGroup', inputValue: '1'},
				{ boxLabel: 'простоя', name: 'EmergencyTeamSortingTypeRadioGroup', inputValue: '2', checked: true },
			]
		});

		Ext.applyIf(me, {
			items: [
				{
					xtype: 'BaseForm',
					id: 'mainPanel',
					layout: {
						type: 'fit'
					},
					items: [
						{
							xtype: 'container',
							layout: {
								type: 'hbox',
								align: 'stretch'
							},
							items: [
								{
									xtype: 'panel',
									flex: 3,
									title: 'Вызовы',
									layout: {
										type: 'vbox',
										align: 'stretch'
									},
									id: 'callsWin',
									items: [
										this.CallsView
									],
									tools: [{
										type: 'customtool',
										width: 'auto',
										itemId: 'loadtool',
										renderTpl: [
											'<span class="loading-paneltool hiddentool"></span>'
										],
									}],
									tbar: {
										itemId: 'tbar',
										items:['->',
										{
										xtype: 'container',
										layout: 'column',
										items: [
                                        
											{
												xtype: 'checkbox',
												boxLabel: 'Показать все',
												itemId: 'showExceptClosed',
												//hideLabel: true,
												margin: '0 10 0 0',
												inputValue: 'true',
												value: 'false',
												listeners: {
													change: function (cb, newValue, oldValue) {
														this.showExceptClosed();
													}
												},
												showExceptClosed: function(){
													separatorIsSet = false;
													if (this.checked) {
														storeCalls.load({
															params: {
																begDate: '01.06.2013',
																appendExceptClosed: true // Добавить к выводу карты находящиеся в обслуживании у бригад СМП
															}
														});
													} else {
														// @todo Вынести в отдельный метод
														storeCalls.load({
															params: {
																begDate: '01.06.2013',
																//endDate: lastDayMonth,
																CmpGroup_id: 1
															}
														});
													}
												}
											},{
												xtype: 'radiogroup',
												columns: 2,
												margin: '0 10 0 0',												
												id: 'tbar_sorting',
												name: 'tbar_sorting',
												defaults: {
													name: 'tbar_sorting'
												},
												items: [{
													boxLabel: 'По времени',
													inputValue: '1',
													width: 90
												}, {
													boxLabel: 'По срочности', // Сортировка по умолчанию
													inputValue: '0',
													width: 100,
													checked: true
												}],
												listeners: {
													change: function(radio, newValue, oldValue, eOpts){
														if ( newValue.tbar_sorting == '1' ) {
															storeCalls.sort([{
																property: 'CmpCallCard_prmDate',
																direction: 'ASC',
																sorterFn: function(v1,v2){
																	var date1 = new Date(Date.parse(v1.get('CmpCallCard_prmDate'))),
																		date2 = new Date(Date.parse(v2.get('CmpCallCard_prmDate')));
																	if ( date1 < date2 ) {
																		return -1;
																	} else if ( date1 == date2 ) {
																		return 0;
																	} else {
																		return 1;
																	}
																}
															}]);
														} else {
															storeCalls.sorters.clear();
															me.down('#showExceptClosed').showExceptClosed();
														}
													}
												}
											}
										]}
									]}
								},
								{
									xtype: 'container',
									layout: {
										type: 'hbox',
										align: 'middle'
									},
									items: [
										{
											xtype: 'button',
											iconCls: 'left-splitter',
											refId: 'hr-splitter',
											height: 40,
											width: 13
										}
									]
								},
								{
									xtype: 'panel',
									flex: 1,
									title: 'Бригады',
									cls: 'short-view',
									layout: {
										type: 'vbox',
										align: 'stretch'
									},
									id: 'teamsWin',
									tools: [
										{
											type: 'gear',
											tooltip: 'Показать панель фильтров',
											// hidden:true,
											handler: function(event, toolEl, panelHeader) {
												this.EmergencyTeamDutyTimeGrid.toggleCollapse();
											}.bind(this)
										},
										{
											type: 'customtool',
											width: 'auto',
											itemId: 'loadtool',
											renderTpl: [
												'<span class="loading-paneltool hiddentool"></span>'
											],
										}
										],
									items: [
										this.EmergencyTeamSortingTypeRadioGroup,
										this.TeamsView
									],
									dockedItems: [
										this.EmergencyTeamDutyTimeGrid										
									]
								}
							]
						}
					],
					dockedItems: [
						me.topToolbar,
						me.bottomToolBar,
						{
							xtype: 'container',
							dock: 'bottom',
							layout: {
								type: 'vbox',
								align: 'stretch'
							},
							items: [
								this.mapPanel
							]
						},
						{
							xtype: 'container',
							dock: 'bottom',
							layout: {
								type: 'hbox',
								height: 30,
								align: 'middle',
								pack: 'center'
							},
							items: [
								{
									xtype: 'button',
									iconCls: 'top-splitter',
									refId: 'vr-splitter',
									height: 13,
									width: 40
								}
							]
						}
					]
				}
			]
		});
		me.callParent(arguments);
	}

});

