/*
 * Новый АРМ диспетчера подстанции
*/

/* global getGlobalOptions, Ext */

Ext.define('common.DispatcherStationWP.swDispatcherStationWorkPlace', {
    extend: 'Ext.window.Window',
	alias: 'widget.swDispatcherStationWorkPlace',
	maximized: true,
	constrain: true,
	id: 'swDispatcherStationWorkPlace',
	refId : 'smpdispatchstation',
	//width: 1000,
	renderTo: Ext.getCmp('inPanel').body,
	closable: true,
	closeAction: 'hide',
	defaultFocus: 'dataview[cls=calls-panel]',
	baseCls: 'arm-window',
	onEsc: Ext.emptyFn,
	title: 'АРМ диспетчера подстанции СМП',
    header: false,
	swDispatcherCallWorkPlaceInstance: null,
	layout: {
        type: 'fit'
    },
    initComponent: function() {
        var me = this,
			curArm = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType,
			currMedStaffFact = sw.Promed.MedStaffFactByUser.current,
			defaultLabelWidth = 130;

		me.curArm = curArm;

		me.refId = sw.Promed.MedStaffFactByUser.current.ARMType || sw.Promed.MedStaffFactByUser.last.ARMType;
		me.title = sw.Promed.MedStaffFactByUser.current.ARMName || sw.Promed.MedStaffFactByUser.last.ARMName;

		me.isNmpArm = curArm.inlist(['dispnmp','dispcallnmp', 'dispdirnmp']);

		if(currMedStaffFact && currMedStaffFact.SmpUnitType_Code == 4 && getRegionNick().inlist(['perm']))
			me.setTitle(currMedStaffFact.ARMName);
		var storeTeams =  Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.EmergencyTeamStore');
		var storeCalls =  Ext.data.StoreManager.lookup('common.DispatcherStationWP.store.CmpCallsStore');

		me.topToolbar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'top',
			refId: 'DPTopToolbar',
			items: [
				{
					xtype: 'button',
					text: 'Новый вызов',
					hidden: getRegionNick().inlist(['ufa']), // Улучшение #137266 СМП. АРМ ДП. Необходимо убрать возможность создания нового вызова в АРМ ДП.
					iconCls: 'add16',
					refId: 'createNewCall'
				},
				{
					xtype: 'splitbutton',
					iconCls: 'ambulance_add16',
					refId: 'menuEmergencyTeam',
					text: 'Наряд',
					hidden: me.curArm.inlist(['dispcallnmp', 'dispdirnmp']),
					//tabIndex: 2,					
					menu: {
						xtype: 'menu',
						items: [
							{
								xtype: 'menuitem',
								text: 'Наряды',
								iconCls: 'eph-record16',
								refId: 'setEmergencyTeamDutyTime'
							},							
							{
								xtype: 'menuitem',
								text: 'Шаблоны нарядов',
								iconCls: 'inbox16',
								refId: 'editEmergencyTeamTemplate'
							},
							{
								xtype: 'menuitem',
								text: 'Автомобили',
								iconCls: 'ambulance16',
								refId: 'editEmergencyTeamAuto'
							},
							{
								xtype: 'menuitem',
								text: 'Текущий наряд',
								iconCls: 'eph-timetable-top16',
								refId: 'currentEmergencyTeamStuff',
								hidden: true //оставил, мало ли что
							},
							
//							,{
//								xtype: 'menuitem',
//								text: 'Выход на смену',
//								iconCls: 'ambulance16',
//								refId: 'saveEmergencyTeamsIsComingWindow'
//							},
//							{
//								xtype: 'menuitem',
//								text: 'Закрытие смен',
//								iconCls: 'ambulance16',
//								refId: 'saveEmergencyTeamsIsCloseWindow'
//							}
						]
					},
					listeners: {
						click: function(){
							this.showMenu();
						}
					}
				},
				{
					xtype: 'splitbutton',
					iconCls: 'ambulance_add16',
					refId: 'menuService',
					text: 'Сервис',
					//tabIndex: 3,					
					menu: {
						xtype: 'menu',
						items: [							
							{
								xtype: 'menuitem',
								text: 'Оперативная обстановка по диспетчерам',
								iconCls: 'emergency-list16',
								hidden: me.isNmpArm,
								refId: 'dispatchOperEnvWindow'
							},
							{
								xtype: 'menuitem',
								text: 'Учет Путевых листов',
								iconCls: 'reports16',
								hidden: me.isNmpArm,
								refId: 'smpWaybillsViewWindow'
							},
							{
								xtype: 'menuitem',
								// text: 'Справочник объектов',
								hidden: me.isNmpArm,
								text: 'Объекты СМП',
								iconCls: 'reports16',
								refId: 'unformalizedAddressDirectoryEditWindow'
							},
							{
								xtype: 'menuitem',
								text: 'Планшетные компьютеры',
								iconCls: 'reports16',
								refId: 'tabletComputersWindow'
							},
							{
								xtype: 'menuitem',
								text: 'Журнал активов',
								iconCls: 'reports16',
								refId: 'aktivSmp'
							},
							{
								xtype: 'menuitem',
								text: 'Отчеты',
								iconCls: 'reports16',
								refId: 'statisticReports',
								//hidden: me.curArm.inlist(['dispcallnmp', 'dispnmp'])
							}
							/*,
							{
								xtype: 'menuitem',
								text: 'Дерево решений',
								iconCls: 'structure16',
								refId: 'DecigionTreeEditWindow'
							}
							*/
						]
					},
					listeners: {
						click: function(){
							this.showMenu();
						}
					}
				},
				{
					xtype: 'button',
					text: 'Медикаменты',
					iconCls: 'dlo16',
					hidden: me.isNmpArm,
					refId: 'buttonDlo',
					//tabIndex: 4,
					handler: function() {
						if (sw.lostConnection) {
							lostConnectionAlert();
							return false;
						}

						Ext.create('sw.tools.swSmpFarmacyRegisterWindow').show();
					}
				},
				{
					xtype: 'button',
					text: 'Отметки о выходе на смену',
					hidden: me.curArm.inlist(['dispcallnmp', 'dispdirnmp']),
					refId: 'EmergencyTeamsDutyMarks',
					iconCls: 'address-book16'
				},
				{
					xtype: 'button',
					text: 'Поточный ввод 110у',					
					iconCls: 'address-book16',
					refId: 'addStreamCard',
					hidden: me.isNmpArm
				},
				{
					xtype: 'button',
					text: me.isNmpArm ? 'Аудиозаписи вызовов НМП' : 'Аудиозаписи вызовов СМП',
					iconCls: 'search16',
					refId: 'audioCalls'
				},
				{
					xtype: 'combo',
					hidden: me.isNmpArm,
					refId: 'sortCalls',
					fieldLabel: 'Сортировка',
					store: Ext.create('Ext.data.Store', {
						fields: ['id', 'name', 'mode'],
						data : [
							{"id":1, "name":"По срочности", "mode":"urgency"},
							{"id":2, "name":"По времени", "mode":"time"}
						]
					}),
					queryMode: 'local',
					name: 'displayMode',
					displayField: 'name',
					labelWidth: 60,
					valueField: 'id',
					value: getRegionNick().inlist(['ufa']) ? 2 : 1
				},
				{
					xtype: 'button',
					text: 'Wialon',
					hidden: true,
					//tabIndex: 6,
					refId: 'wialon_btn',
					iconCls: 'disp-search16',					
					handler: function() {
						Ext.Ajax.request({
							url: '/?c=Wialon&m=retriveAccessData',
							callback: function(opt, success, response) {
								if (success){	
									var res = Ext.JSON.decode(response.responseText);									
									var loc = 'http://195.128.137.36:8022/login_action.html?user='+res.MedService_WialonLogin+'&passw='+res.MedService_WialonPasswd+'&action=login&skip_auto=1&submit=Enter&store_cookie=on&lang=ru';
									Ext.create('sw.tools.swWialonWindow',{
										location: escape(loc)
									}).show();	
								} else {
									Ext.MessageBox.alert('Ошибка', 'Не установлены имя и пароль для wialon');
								}
							}.bind(this)
						});				
					}
				},
				{
					xtype: 'button',
					text: 'ГЛОНАСС',
					//tabIndex: 7,
					refId: 'glonass_btn',
					hidden: true,
					iconCls: 'disp-search16',					
					handler: function() {							
						Ext.Ajax.request({
							url: '/?c=Wialon&m=retriveAccessData',
							callback: function(opt, success, response) {
								if (success){		
									var res = Ext.JSON.decode(response.responseText);
									var location = 'http://195.128.137.36:8022/login_action.html?user='+res.MedService_WialonLogin+'&passw='+res.MedService_WialonPasswd+'&action=login&skip_auto=1&submit=Enter&store_cookie=on&lang=ru';
									var win = window.open(location);	
								} else {
									Ext.MessageBox.alert('Ошибка', 'Не установлены имя и пароль для wialon');
								}
							}.bind(this)
						});	
					}
				},'->',{
					// стандартный вид
					xtype: 'button',					
					text: '',
					enableToggle: true,
					pressed: true,
					//tabIndex: 8,
					toggleGroup: 'viewToggle',
					refId: 'vid1_btn',				
					iconCls: 'document16',
					allowDepress: false
				}, {
					// табличный вид
					xtype: 'button',					
					text: '',
					enableToggle: true,
					//tabIndex: 9,
					toggleGroup: 'viewToggle',
					refId: 'vid2_btn',					
					iconCls: 'window16',
					allowDepress: false
				}
			]
		});

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
									tabIndex: 40,
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
		
		var checkedColumnEmergencyTeamDutyTimeGrid = (getRegionNick().inlist(['ufa', 'krym', 'kz']))
		
		//грид групп бригад(фильтр)
		this.EmergencyTeamDutyTimeGrid = Ext.create('Ext.grid.Panel', {
			plugins: new Ext.grid.plugin.CellEditing({
				clicksToEdit: 1
			}),
			viewConfig: {
				loadingText: 'Загрузка',
				markDirty:false
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
				{ dataIndex: 'visible', text: '', width: 55, xtype: 'checkcolumn', hideable: false, sortable: false,
					renderTpl: [
						'<div id="{id}-titleEl" role="presentation" {tipMarkup}class="', Ext.baseCSSPrefix, 'column-header-inner',
							'<tpl if="empty"> ', Ext.baseCSSPrefix, 'column-header-inner-empty</tpl>">',
							'<div class="customCheckAll">',
								'<span class='+((getRegionNick().inlist(['ufa', 'krym', 'kz']))?"checkedall":"")+'>&nbsp;</span>',
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
				{ dataIndex: 'id', text: 'ID', key: true, hidden: true, hideable: false },
				{ dataIndex: 'name', text: 'Наименование', flex: 1, hidden: false, hideable: false }
			]
		})

		//грид бригады
		//storeTeams.group('EmergencyTeamStatus_FREE', 'desc'); нельзя делать это здесь

		this.TeamsView = Ext.create('Ext.view.View', {
			id: this.id+'_teamGrid',
			store: storeTeams,
			loadingText: 'Загрузка',
			loadMask: false,
			cls: 'teams-panel',
			overflowY: 'scroll',
			flex: 1,
			preserveScrollOnRefresh: true,
			itemSelector: 'div.teams-wrap',
			hideOtherTeams: true,
			collapsedGroups: {},
			tpl: new Ext.XTemplate(
				'<tpl for=".">',
				'{[ this.getOpenOtherTeamsWrapper(values, xindex, xcount, this) ]}',
				'{[ this.getOpenGroupWrapper(values, xindex, xcount, this) ]}',
				'{[ this.getTeamContent(values, xindex, xcount, this) ]}',
				'{[ this.getCloseGroupWrapper(values, xindex, xcount, this) ]}',
				'{[ this.getCloseOtherTeamsWrapper(values, xindex, xcount, this) ]}',
				'</tpl>',
				{
					getTeamContent: function(val,xindex){
						var tpl = '',
							prevTeam = storeTeams.getAt(xindex-2),
							nextTeam = storeTeams.getAt(xindex);

						if(this.isSeparator(val.EmergencyTeamStatus_id)) tpl += '<div class="teams-separator"></div>';

						tpl +=
						'<div class="teams-wrap ' + val.EmergencyTeam_isOnline +'">' +
							'<div class="teams-text" style="color:' + val.EmergencyTeamStatus_Color + '">' +
								'<div class="left">' +
									'<div class="top">' +
										(val.EmergencyTeam_Num ? '<p>' + val.EmergencyTeam_Num + '</p>' : '') +
									'</div>' +
									'<div class="middle">' +
										(val.EmergencyTeamSpec_Code ? '<p>' + val.EmergencyTeamSpec_Code + '</p>' : '') +
									'</div>' +
									'<div class="bottom">' +
										(val.EmergencyTeamDurationText ? '<span>' + val.EmergencyTeamDurationText + '</span></br>' : '') +
									'</div>' +
								'</div>' +
								'<div class="center">' +
									(val.EmergencyTeamStatus_Name ? '<p>' + val.EmergencyTeamStatus_Name + (val.HLpu_Nick ? '<p>(' + val.HLpu_Nick + ')</p>' : '') + this.getCmpCallCardNums(val) + '</p>' : '') +
									(val.Person_Fin ? '<p>' + val.Person_Fin + '</p>' : '') +
									this.getLastCheckinAddress(val) +
								'</div>' +
								'<div class="right">' +
									'<div class="countCallsOnTeam">' + (val.countcallsOnTeam>0 ? ('Вызовы ' + val.countcallsOnTeam) : '') + '</div>' +
									'<div id="onlinestatus' + val.EmergencyTeam_id + '" class="et-online" ' + (val.isOnline == 1 ? '' : 'style="display:none;"') + '></div>' +
									'<div class="cell-cmpcc-moreinfo"></div>' +
								'</div>' +
							'</div>' +
						'</div>';

						return tpl;

					},
					disableFormats: true,
					isSeparator: function(id){
						var parent = me.TeamsView;						
						if (id == 0 || id == 14){
							parent.separatorPos = false;
						}
						else 
						{if (parent.separatorPos == false)
						{
								parent.separatorPos = true;
								return 'true'
						}}
					}
				},
				{
					getDateFinish: function(val){
						if (val.EmergencyTeamDuty_DTFinish){
							var dateF = Ext.Date.parse(val.EmergencyTeamDuty_DTFinish, "Y-m-d H:i:s"),
								delta = dateF-new Date(),
								hours = Math.floor(delta/3600000);

							if (hours<24 && hours>=0){
								return '<p>до '+Ext.Date.format(dateF, 'H:i')+'</p>';
							}
						}
						return '';
					}
				},
				{
					getCmpCallCardNums: function(val){
						if (val.CmpCallCard_id){
							return '<p> К/Т ' + val.CmpCallCard_Numv + ' (' + val.CmpCallCard_Ngod + ')</p>';
						}
						return '';
					}
				},
				{
					getOpenOtherTeamsWrapper: function(val,xindex){
						var prevTeam = storeTeams.getAt(xindex-2);

						if (val.WorkAccess == 'false' && (!prevTeam || (prevTeam.get('WorkAccess') == 'true'))){
							return '<a href="javascript:void(0)" class="other-teams-btn" >' + (me.TeamsView.hideOtherTeams ? "Показать бригады других подстанций" : "Скрыть бригады других подстанций") +'</a><span class="other-team-wrapper" '+ (me.TeamsView.hideOtherTeams ? "hidden=true" : '') +'>';
						}
						return '';
					}
				},
				{
					getCloseOtherTeamsWrapper: function(val,xindex, xcount){

						if (val.WorkAccess == 'false' && xcount == xindex){

							return '</span >';
						}
						return '';
					}
				},
				{
					getOpenGroupWrapper: function(val,xindex, xcount){
						var prevTeam = storeTeams.getAt(xindex-2);

						if(val.WorkAccess == 'false' && (!prevTeam || (prevTeam && val.LpuBuilding_id != prevTeam.get('LpuBuilding_id')))) {
							return '<div class="group-header-' + val.LpuBuilding_id + '  x-grid-group-hd x-grid-group-hd-collapsible '+(me.TeamsView.collapsedGroups[val.LpuBuilding_id] ? "x-grid-group-hd-collapsed" : "")+'">' +
										'<div class="x-grid-group-title" data-id="' + val.LpuBuilding_id + '">' + val.LpuBuilding_Name + '</div>' +
									'</div>' +
									'<div class="group-wrapper-' + val.LpuBuilding_id + '" '+(me.TeamsView.collapsedGroups[val.LpuBuilding_id] ? "hidden=true" : "")+'>';
						}

						return '';
					}
				},
				{
					getCloseGroupWrapper: function(val,xindex, xcount){
						var prevTeam = storeTeams.getAt(xindex-2);
						if(val.WorkAccess == 'false' && prevTeam && val.LpuBuilding_id != prevTeam.get('LpuBuilding_id')){

							return '</div>';
						}
						return '';
					}
				},
				{
					getLastCheckinAddress: function(val){
						if(getRegionNick().inlist(['perm', 'kareliya'])){
							return val.lastCheckinAddress
						}
						return '';
					}
				}
			)
		});
		
		// storeCalls.group('TransmittedOrAccepted');
		//storeCalls.sort('TransmittedOrAccepted', 'asc');
		//грид вызовы
		this.CallsView = Ext.create('Ext.view.View', {
			id: this.id+'_callsGrid',
			store: storeCalls,
			cls: 'calls-panel',
			overflowY: 'scroll',
			loadingText: 'Загрузка',
			loadMask: false,
			flex: 1,
			itemSelector: 'div.calls-wrap',
			preserveScrollOnRefresh: true,
			separatorIsSet: false,
			tpl:  new Ext.XTemplate(
				'<tpl for=".">',
					'<tpl if="this.insertSeparator(CmpGroup_id)">',
						'<div class="teams-separator"></div>',
					'</tpl>',
					'<div class="calls-wrap {[ this.getBold(values) ]} {[ this.getisNewCall(values) ]} {[ this.getIsUnreasonCall(values) ]} {[ this.getSpecTeamClass(values) ]} {[ this.getHiddenClass(values) ]} {[ this.getEventDenyClass(values) ]}  ">',
						'<div class="calls-text">',
							'<div class="left">',
								'<tpl if="is112 == 2">',
								'<img src="/img/icons/ico112.png" height="20px"/>',
								'</tpl>',
								'<p>{Person_FIO}  {personAgeText}</p>',
								'<tpl if="CmpReason_Name">',
									'<p>{[ this.getReasonName(values) ]}</p>',
								'</tpl>',
							'</div>',
							'<div class="center">',
								'<div class="number">',
									'<p>',
										'{CmpCallCard_Numv} / ',
										'<span>{[ this.getNgod(values) ]} {[ this.getDate(values,"CmpCallCard_prmDate") ]}</span>',
										'<tpl if="Duplicate_Count"> Дубл.: {Duplicate_Count} </tpl>',
										'<tpl if="ActiveCall_Count"> Акт.зв.: {ActiveCall_Count} </tpl>',
									'</p>',
								'</div>',
								'<tpl if="Adress_Name">',
									'<p class="calls-wrapcard-address">{[this.getFullAdress(values)]}</p>',
								'</tpl>',
								'<tpl if="CmpCallCard_Comm">',
									'<p class="calls-wrapcard-address">{CmpCallCard_Comm}</p>',
								'</tpl>',
								(!getRegionNick().inlist(['ufa']))?'<tpl if="CmpCallCard_Telf"><p class="calls-wrapcard-address"> Тел. {CmpCallCard_Telf} </p></tpl>':'',
							'</div>',
							'<div class="cell-cmpcc-moreinfo"></div>',
							'<div class="right">',
								'<tpl if="EmergencyTeam_Num"><div style="float: left;"><span>Бриг:</span><h2>{EmergencyTeam_Num}</div></h2></tpl>',
								'<tpl if="CmpCallCard_Urgency"><div style="float: right;margin-right: 5px;">{[ this.getUrgency(values) ]}</tpl>',
								'<tpl if="CmpCallCard_DateTper && EmergencyTeam_Num"><div style="clear: both;"><span> {[ this.getDate(values,"CmpCallCard_DateTper") ]}</span></div></tpl>',
								'<div style="clear: both;"><span><tpl if="lastCallMessageText">{lastCallMessageText}</tpl> </span></div>',
							'</div>',
							'<div style="float: right; margin-right: 20px; width: 20%;">',

								'<tpl if="this.getEventDenyClass(values)">',
									'<tpl if="CmpCallCardStatusType_id == 21">',
										'<div> Решение диспетчера отправляющей части </div>',
									'<tpl else>',
										'<div>Решение диспетчера подстанции </div>',
									'</tpl>',
								'</tpl>',
								'<div> {CmpCallCardEventType_Name}',
								'<tpl if="EventWaitDuration">',
									': {[ this.getEventWaitDuration(values) ]}',
								'</tpl>',
								'</div>',
								'<tpl if="CmpCallCard_defCom">',
									'<div style="opacity: .5;"> {CmpCallCard_defCom} </div>',
								'</tpl>',
								'<tpl if="EmergencyTeamDelayType_Name">',
									'<div> Причина задержки: {EmergencyTeamDelayType_Name} </div>',
								'</tpl>',
								'<tpl if="CmpPPDResult_Name">',
									'<div>{CmpPPDResult_Name}</div>',
								'</tpl>',
								'<tpl if="CmpCallCard_IsExtra == 1">',
									'{[ this.getExtraForNmpArm(values) ]}',
								'</tpl>',
								'<tpl if="CmpCallCard_IsExtra == 2">',
									'<div class="extrablue">неотложный</div>',
								'</tpl>',
								'<tpl if="CmpCallCard_IsExtra == 3">',
									'<div class="extrgreen">вызов врача на дом</div>',
								'</tpl>',
								'<tpl if="CmpCallCard_IsExtra == 4">',
									'<div class="extrgreen">обращение в поликлинику</div>',
								'</tpl>',
								'<tpl if="CmpCallType_Code == 19">',
									'<div class="extrgreen">актив</div>',
								'</tpl>',
								'<tpl if="this.getSpecTeamClass(values)">',
									'<div class="extrared">для спец. бригады</div>',
									//'<div class="extrared">{CmpCallType_clearName}</div>',
								'</tpl>',
								'<tpl if="CmpGroup_id == 0">',
									'{[ this.getFirstCardLink(values) ]}',
									//'<b>{CmpCallType_clearName} <a href="javascript:void(0)" class="select-rid" data-rid="{CmpCallCard_rid}">№ {ridNum}</a></b>',
								'</tpl>',
							'</div>',
							'<div class="right">',

								/*
								'<tpl if="CmpGroup_id == 1">',
									'<div class="top">',
										'<button class="cell-cmpcc-accept"> Принять </button>',
									'</div>',
									'<div class="bottom">',
										'<button class="cell-cmpcc-reject"> Отменить </button>',
									'</div>',
								'</tpl>',

								'<tpl if="CmpGroup_id == 2">',
								'</tpl>',
								'<tpl if="CmpGroup_id == 3">',
									'<button class="cell-cmpcc-close"> Закрыть </button>',
								'</tpl>',
								'<tpl if="CmpGroup_id == 4">',
									'<button class="cell-cmpcc-showclosed"> Просмотр </button>',
									'<button class="cell-cmpcc-showprint"> Печать </button>',
								'</tpl>',
								*/
							'</div>',
						'</div>',
					'</div>',

				'</tpl>',
				{
					getFullAdress: function(val) {
						var region = getGlobalOptions().region.nick;

						switch (region) {
							case 'astra':{
								if (val.AstraAdress_Name) {
									return val.AstraAdress_Name
								} else {
									return val.Adress_Name
								}
								break
							}
							default: {
								return val.Adress_Name
								break
							}
						}
					},
					getUrgency: function(val){
						if(me.isNmpArm || val.CmpCallCard_Urgency == 99) return '</div>';
						return '<span>Сроч:</span><h2>' + val.CmpCallCard_Urgency + '</h2></div>';
					},
					getNgod: function(val){
						return (!getRegionNick().inlist(['ufa', 'krym', 'kz'])) ? val.CmpCallCard_Ngod+' / ' : '';
					},
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
					insertSeparator: function(CmpGroup_id) {
						if (!this.CallsView.separatorIsSet) {
							if (CmpGroup_id == 3) {
								this.CallsView.separatorIsSet = true;
								return true;
							}
						}
						return false;
					}.bind(this),
					getTime: function(val){
						var delta = new Date() - new Date(Date.parse(val.CmpCallCard_prmDate)),
							appendix = delta%3600000,
							hours = chkzero(Math.floor(delta/3600000)),
							min = chkzero(Math.floor(appendix/60000)),
							sec = chkzero(Math.floor((appendix%100000/1000)%60)),
							result = '';
					
						function chkzero(num){
							var str = num.toString();
								if (str.length==1)
								return '0'+str
								else
								return str
						}
						
						if (hours>24){
							return ''
						}
						else{
							if (hours>0){
								result = hours+':'+min;//+':'+sec;
							}
							else {result = min+':'+sec;}
						}			
						return result
					}
				},
				{
					getUrgencyVal: function(val){
						var delta = new Date() - new Date(Date.parse(val.CmpCallCard_prmDate)),
							mins = Math.floor(delta/60000),
							updateTimeMinutes = 15,
							result = Math.floor(mins/updateTimeMinutes),
							urgencyVal = val.CmpCallCard_Urgency - result;

						if (val.Urgency != '') return val.Urgency
						if (urgencyVal > 0){
							return urgencyVal
						}
						return val.CmpCallCard_Urgency
					}
				},
				{
					getEventWaitDuration: function(val){
						return me.getEventWaitDuration(val);
					}
				},
				{
					getFirstCardLink: function(val){
						if(val.ridNum)
							return '<b>' + val.CmpCallType_clearName + ' <a href="javascript:void(0)" class="select-rid" data-rid="'+ val.CmpCallCard_rid + '">№ ' + val.ridNum + '</a></b>';
						else
							return '';
					}
				},
				{
					getDate: function(val,typeTime){
						var dateF = '';
						switch(typeTime){
							case 'CmpCallCard_TimeTper':
								dateF = new Date(Date.parse(val.CmpCallCard_TimeTper));
								break;
							case 'CmpCallCard_prmDate':
								dateF = new Date(Date.parse(val.CmpCallCard_prmDate));
								break;
							case 'CmpCallCard_DateTper':
								dateF = new Date(Date.parse(val.CmpCallCard_DateTper));
								break;
						}
						if(dateF != '')
						{
							if(!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2)
								dateF = Ext.Date.format(dateF, 'H:i');
							else
								dateF = Ext.Date.format(dateF, 'H:i:s');
						}
						return dateF;
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
					getBold: function(val) {						
						if(
							//( (val.CmpCallType_Code) === '14' ) ||
							( val.timeEventBreak === 'true' )
						)return 'bold';
						else return '';
					}
				},{
					getisNewCall: function (val) {
						return (val.isNewCall) ? 'new-call' : '';
					}
				},
				{
					getIsUnreasonCall: function (val) {
						return (val.CmpPPDResult_id) ? 'unreason-call' : '';
					}
				},
				{
					getExtraForNmpArm: function (val) {
						return (me.isNmpArm) ? '<div class="extrared">экстренный</div>' : '';
					}
				},
				{
					getSpecTeamClass: function (val) {
						return (!me.isNmpArm && val.CmpCallType_Code == 9) ? 'spec-team' : '';
					}
				},
				{
					getHiddenClass: function (val) {
						return (val.CmpCallCard_id) ? '' : 'hidden';
					}
				},
				{
					getEventDenyClass: function (val) {
						return (currMedStaffFact && currMedStaffFact.SmpUnitType_Code == 4 && val.hasEventDeny && val.CmpCallCardStatusType_id == 21 || val.CmpCallCardStatusType_id == 22) ? 'deny-call' : '';
					}
				}
			)
		});

		me.teamsFilterPanel = Ext.create('Ext.form.FieldSet', {
			xtype: 'fieldset',
			title: 'Поиск бригад',
			//collapsed: true,
			collapsible: true,
			refId: 'teamsFilterPanel',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			padding: '0 10 5 0',
			margin: 3,
			fieldDefaults: {
				margin: 2,
				labelWidth: 120
			},
			listeners: {
				render: function () {
					this.collapse();
				}
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
							xtype: 'textfield',
							fieldLabel: '№ Бригады',
							labelAlign: 'right',
							width: 265,
							enableKeyEvents : true,
							name: 'FilterTeamNum'
						}
					]
				},
				{
					xtype: 'container',
					margin: '10 0 10 126',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'button',
							iconCls: 'search16',
							refId: 'searchAndFocusTeamButton',
							text: 'Найти',
							width: 70,
							margin: '0'
						},
						{
							xtype: 'button',
							refId: 'resetBtn',
							iconCls: 'reset16',
							width: 70,
							text: 'Сброс',
							margin: '0 5',
							handler: function(){
								me.down('BaseForm').getForm().reset();
								me.down('BaseForm').getForm().isValid();
							}
						}
					]
				}
			]
		});

		me.callsFilterPanel = Ext.create('Ext.form.FieldSet', {
			xtype: 'fieldset',
			title: 'Поиск вызовов',
			//collapsed: true,
			collapsible: true,
			refId: 'callsFilterPanel',
			//style: 'font-size: 11px !important;',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			padding: '0 10 5 0',
			margin: 3,
			//flex: 1,
			fieldDefaults: {
				margin: 2,
				labelWidth: 120
			},
			listeners:{
				render: function(){
					this.collapse();
				}
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
							xtype: 'dCityCombo',
							width: 482,
							name: 'FilterCityCombo',
							listeners:{
								select: function(inp, e){
									var streetsCombo = me.down('form').getForm().findField('FilterStreetsCombo');

									streetsCombo.bigStore.getProxy().extraParams = {
										town_id: e[0].get('Town_id'),
										Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
									};
									streetsCombo.reset();
									streetsCombo.bigStore.load();
								}
							}
						},
						{
							xtype: 'swStreetsSpeedCombo',
							name: 'FilterStreetsCombo',
							labelAlign: 'right',
							width: 400,
							labelWidth: 77,
							fieldLabel: 'Улица'
						}
					]
				},
				{
					xtype: 'container',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'textfield',
							fieldLabel: 'Дом',
							labelAlign: 'right',
							width: 265,
							enableKeyEvents : true,
							name: 'FilterCmpCallCard_Dom'
						},
						{
							xtype: 'textfield',
							fieldLabel: 'Корпус',
							labelAlign: 'right',
							width: 213,
							labelWidth: 77,
							enableKeyEvents : true,
							name: 'FilterCmpCallCard_Korp'
						},
						{
							xtype: 'numberfield',
							fieldLabel: 'Квартира',
							labelAlign: 'right',
							width: 222,
							labelWidth: 77,
							enableKeyEvents : true,
							name: 'FilterCmpCallCard_Kvar',
							hideTrigger: true
						}
					]
				},
				{
					xtype: 'container',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'numberfield',
							hideTrigger: true,
							fieldLabel: '№ вызова (д)',
							labelAlign: 'right',
							name: 'FilterCmpCallCard_Numv',
							enableKeyEvents : true,
							width: 213
						},
						{
							xtype: 'numberfield',
							hideTrigger: true,
							fieldLabel: '№ вызова (год):',
							labelAlign: 'right',
							name: 'FilterCmpCallCard_Ngod',
							enableKeyEvents : true,
							labelWidth: 128,
							width: 265
						}
					]
				},
				{
					xtype: 'container',
					margin: '10 0 10 126',
					layout: {
						type: 'hbox',
						align: 'stretch'
					},
					items: [
						{
							xtype: 'button',
							iconCls: 'search16',
							refId: 'searchAndFocusCallButton',
							text: 'Найти',
							width: 70,
							margin: '0'
						},
						{
							xtype: 'button',
							refId: 'resetBtn',
							iconCls: 'reset16',
							width: 70,
							text: 'Сброс',
							margin: '0 5',
							handler: function(){
								me.down('BaseForm').getForm().reset();
								me.down('BaseForm').getForm().isValid();

							}
						}
					]
				}
			]
		});

		this.mapPanel = Ext.create('sw.Smp.MapPanel',{
			layout: {
				align: 'stretch',
				pack: 'center',
				type: 'hbox'
			},
			height: 0,
			toggledButtons: true,
			header: false,
			callMarker: null,
			showCloseHelpButtons: false,
			title: 'Карта'
		});
		
		
		this.shortViewPanel = Ext.create('Ext.grid.Panel', {
			autoScroll: true,
			stripeRows: true,
			refId: 'callsCardsShortGrid',
			keepState: true,
			collapsedGroups: {},
			sortableColumns: false,
			viewConfig: {
				loadingText: 'Загрузка',
				rowTpl: Ext.create('Ext.XTemplate',
					'{%',
					'var dataRowCls = values.recordIndex === -1 ? "" : " ' + Ext.baseCSSPrefix + 'grid-data-row";',
					'%}',
					'{[ this.getMultiGroupHeader(values, xindex) ]}',
					'<tr {[values.rowId ? ("id=\\"" + values.rowId + "\\"") : ""]} ',
					'data-boundView="{view.id}" ',
					'data-recordId="{record.internalId}" ',
					'data-recordIndex="{recordIndex}" ',
					'style="{[this.getDisplayRec(values)]}"',
					'class="{[values.itemClasses.join(" ")]} {[values.rowClasses.join(" ")]} ',
					'<tpl if="values.rowId">',
					'{[dataRowCls]} ',
					'</tpl>',
					'{[ this.getMultiGroupClass(values) ]}" ',
					'{rowAttr:attributes} tabIndex="-1">',
					'<tpl for="columns">' +
					'{%',
					'parent.view.renderCell(values, parent.record, parent.recordIndex, xindex - 1, out, parent)',
					'%}',
					'</tpl>',
					'</tr>',


					{
						priority: 0,
						getMultiGroupHeader: function(val, xindex){
							var prevRec = storeCalls.getAt(val.recordIndex - 1);

							if(getRegionNick().inlist(['astra']) && val.record && val.record.data.CmpCallCard_id && val.record.data.CmpGroupTable_id.inlist([1,3])){
								if(!prevRec || !prevRec.data.CmpCallCard_id || (val.record.data.LpuBuilding_id != prevRec.data.LpuBuilding_id)){
									return '<tr><td colspan="'+val.columns.length+'"><div style="padding-left: 20px" class="multi-group-header-'+ val.record.data.CmpGroupTable_id + '_' + val.record.data.LpuBuilding_id +
										' x-grid-group-hd x-grid-group-hd-collapsible '+(me.shortViewPanel.collapsedGroups[val.record.data.CmpGroupTable_id + '_' + val.record.data.LpuBuilding_id] ? "x-grid-group-hd-collapsed" : "")+'">' +
										'<div class="multi-group-title x-grid-group-title" data-id="'+ val.record.data.CmpGroupTable_id + '_' + val.record.data.LpuBuilding_id + '">' + val.record.data.LpuBuilding_Nick + '</div>' +
										'</div></td></tr>';
								}

							}
							return '';

						},
						getMultiGroupClass: function(val){
							if(val.record && val.record.data.CmpCallCard_id && val.record.data.CmpGroupTable_id.inlist([1,3])){
								return 'multi-group-record-' + val.record.data.CmpGroupTable_id + '_' + val.record.data.LpuBuilding_id;
							}
						},
						getDisplayRec: function(val){
							return me.shortViewPanel.collapsedGroups[val.record.data.CmpGroupTable_id + '_' + val.record.data.LpuBuilding_id] ? "display:none" : ""
						}
					}
				),
				cellTpl: Ext.create('Ext.XTemplate',
					'<td role="gridcell" class="{tdCls}" {tdAttr} id="{[Ext.id()]}"' +
					'style="'+
					'<tpl if="this.checkIsNewCall(record)">',
					' background-color: #f6d7d7;',
					'</tpl>',
					'<tpl if="this.getIsUnreasonCall(record)">',
					' background-color: #ffc578;',
					'</tpl>',
					'<tpl if="this.checkIsSpecTeam(record)">',
					' background-color: #f6d7d7;border-top:1px solid #FF0000; border-bottom:1px solid #FF0000;',
					'</tpl>',
					'<tpl if="this.checkIsDenyCall(record)">',
					' background-color: #ffff00;',
					'</tpl>',
					'">'+
					'<div {unselectableAttr} class="' + Ext.baseCSSPrefix + 'grid-cell-inner {innerCls}"'+
					'style="text-align:{align};<tpl if="style">{style}</tpl>'+
						'<tpl if="this.checkBold(record)">',
						' font-weight: bold;',
						'</tpl>',
					'">{value}</div>'+
					'</td>',

					{
						priority: 0,
						checkBold: function(record) {
							if(
								//( (record.get('CmpCallType_Code')) === '14' ) ||
								//( (record.get('breakLimitMinTimeSMP')) === 'true' ) ||
								( (record.get('timeEventBreak')) === 'true' )
							)return true;
						},
						checkIsNewCall: function(record){
							return (record.raw.isNewCall);
						},
						getIsUnreasonCall: function (record) {
							return (record.raw.CmpPPDResult_id);
						},
						checkIsSpecTeam: function(record){
							return (!me.isNmpArm && record.raw.CmpCallType_Code == 9);
						},
						checkIsDenyCall: function(record){

							return (currMedStaffFact && currMedStaffFact.SmpUnitType_Code == 4 && record.raw.hasEventDeny && record.raw.CmpCallCardStatusType_id == 21 || val.CmpCallCardStatusType_id == 22);
						}
					}
				),
				moveColumn: function(fromIdx, toIdx, colsToMove) {
					var me = this,
						fragment = (colsToMove > 1) ? document.createDocumentFragment() : undefined,
						destinationCellIdx = toIdx,
						colCount = me.getGridColumns().length,
						lastIndex = colCount - 1,
						doFirstLastClasses = (me.firstCls || me.lastCls) && (toIdx === 0 || toIdx == colCount || fromIdx === 0 || fromIdx == lastIndex),
						i,
						j,
						rows, len, tr, cells,
						tables;



					if (me.rendered && toIdx !== fromIdx) {


						rows = me.el.query(me.getDataRowSelector());

						if (toIdx > fromIdx && fragment) {
							destinationCellIdx -= colsToMove;
						}

						for (i = 0, len = rows.length; i < len; i++) {
							tr = rows[i];
							cells = tr.childNodes;


							//Переопределено ради этого условия (#165497)
							if(cells.length === 1){
								continue;
							}
							if (doFirstLastClasses) {

								if (cells.length === 1) {
									Ext.fly(cells[0]).addCls(me.firstCls);
									Ext.fly(cells[0]).addCls(me.lastCls);
									continue;
								}
								if (fromIdx === 0) {
									Ext.fly(cells[0]).removeCls(me.firstCls);
									Ext.fly(cells[1]).addCls(me.firstCls);
								} else if (fromIdx === lastIndex) {
									Ext.fly(cells[lastIndex]).removeCls(me.lastCls);
									Ext.fly(cells[lastIndex - 1]).addCls(me.lastCls);
								}
								if (toIdx === 0) {
									Ext.fly(cells[0]).removeCls(me.firstCls);
									Ext.fly(cells[fromIdx]).addCls(me.firstCls);
								} else if (toIdx === colCount) {
									Ext.fly(cells[lastIndex]).removeCls(me.lastCls);
									Ext.fly(cells[fromIdx]).addCls(me.lastCls);
								}
							}

							if (fragment) {
								for (j = 0; j < colsToMove; j++) {
									fragment.appendChild(cells[fromIdx]);
								}
								tr.insertBefore(fragment, cells[destinationCellIdx] || null);
							} else {
								tr.insertBefore(cells[fromIdx], cells[destinationCellIdx] || null);
							}
						}


						tables = me.el.query(me.getBodySelector());
						for (i = 0, len = tables.length; i < len; i++) {
							tr = tables[i];
							if (fragment) {
								for (j = 0; j < colsToMove; j++) {
									fragment.appendChild(tr.childNodes[fromIdx]);
								}
								tr.insertBefore(fragment, tr.childNodes[destinationCellIdx] || null);
							} else {
								tr.insertBefore(tr.childNodes[fromIdx], tr.childNodes[destinationCellIdx] || null);
							}
						}
					}
				},
				getRowClass: function(record, rowIndex, rowParams, store) {
					if(record && !record.get('CmpCallCard_id')) return 'hidden-row';
				},
			},
			requires: [
				'Ext.grid.feature.Grouping'
			],
			features: [{
				ftype: 'grouping',
				id: 'GroupingTableGridFeature',
				refId: 'GroupingTableGridFeature',
				enableGroupingMenu: false,
				groupHeaderTpl: Ext.create('Ext.XTemplate',
					'<div>{name:this.formatName} ({[this.getCount(values)]})</div>',
					{
						formatName: function(name) {
							var groupname = '';
							switch (name)
							{
								case 1: {groupname = 'Поступившие вызовы'; break;}
								case 2: {
									if(!me.isNmpArm)
										groupname = 'Поступившие из 112';
									break;
								}
								case 3: {groupname = 'Вызовы на обслуживании'; break;}
								case 4: {
									if(!me.isNmpArm)
										groupname = 'Исполненные вызовы';
									else
										groupname = 'Обслуженные вызовы';
									break;
								}
								case 7: {groupname = 'Отложенные вызовы'; break;}
								case 5: {groupname = 'Закрытые вызовы'; break;}
								case 6: {groupname = 'Отменены'; break;}
								case 0: {
									if(getRegionNick().inlist(['perm'])){
										groupname = 'Ожидание решения диспетчера'; break;
									}
									groupname = 'Ожидание решения диспетчера отправляющей части '; break;
								}
								default: {groupname = 'Неизвестный статус'; break;}
							}
							return groupname
						},
					 	getCount: function(group) {
							var store = this.shortViewPanel.store,
								filters = store.filters,
								activeFilters = 0;

							filters.each(function(filter){
								if(filter.value) activeFilters++;
							})

							if(activeFilters) {
								return group.children.length;
							}else{
								return group.children.length - 1;
							}
						}.bind(me)
					}
				),
				hideGroupedHeader: true,
				startCollapsed: true,
				//collapsible: true
			}],
			store: storeCalls,
			plugins: [Ext.create('Ext.ux.GridHeaderFilters',{enableTooltip: false,reloadOnChange:true})],
			columns: [
				{itemId: 'CmpCallCard_id', dataIndex: 'CmpCallCard_id', text: 'ID', key: true, hidden: true, hideable: false},
				{itemId: 'Person_id', dataIndex: 'Person_id', hidden: true, hideable: false},
				{itemId: 'countCardByGroup', dataIndex: 'countCardByGroup', hidden: true, hideable: false},
				{itemId: 'PersonEvn_id', dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
				{itemId: 'Server_id', dataIndex: 'Server_id', hidden: true, hideable: false},
				{itemId: 'Person_Surname', dataIndex: 'Person_Surname', hidden: true, hideable: false},
				{itemId: 'Person_Firname', dataIndex: 'Person_Firname', hidden: true, hideable: false},
				{itemId: 'Person_Secname', dataIndex: 'Person_Secname', hidden: true, hideable: false},
				{itemId: 'pmUser_insID', dataIndex: 'pmUser_insID', hidden: true, hideable: false},
				{itemId: 'CmpCallCard_isLocked', dataIndex: 'CmpCallCard_isLocked', hidden: true, hideable: false},
				{
					itemId: 'is112',
					dataIndex: 'is112',
					text: ' ', 
					hidden: me.isNmpArm,
					hideable: !me.isNmpArm, 
					width: 50,
					renderer: function(v){
						if(v == 2) return '<img src="/img/icons/ico112.png" height="20px"/>'
					}
				},
				{
					itemId: 'CmpIllegalAct_byPerson', 
					dataIndex: 'CmpIllegalAct_byPerson', 
					hidden: me.isNmpArm, 
					hideable: !me.isNmpArm, 
					text: ' ', 
					width: 50, 
					renderer: function(value, attr, rec){
						if(!value || !rec.get('CmpIllegalAct_Comment') || !rec.get('CmpIllegalAct_prmDT')) return '';
	
						var dangerUrl = 'extjs4/resources/images/danger.png',
							obj = (value == 2)? 'пациенту ' : 'адресу ',
							txt = 'По данному '+obj+rec.get('CmpIllegalAct_prmDT')+' зарегистрирован случай противоправного действия в отношении персонала СМП. Комментарий: ' + rec.get('CmpIllegalAct_Comment');
	
						return '<img src='+dangerUrl+' height="20px" title="'+txt+'"/>'
					}
				},
				{
					itemId: 'CmpCallCard_prmDate',
					dataIndex: 'CmpCallCard_prmDate',
					hidden: true, hideable: false
				},
				{
					itemId: 'CmpCallCard_prmDateStr',
					dataIndex: 'CmpCallCard_prmDateStr',
					hidden: true, hideable: false
				},
				{
					itemId: 'CmpCallCard_prmDateFormat',
					dataIndex: 'CmpCallCard_prmDateFormat',
					text: 'ДАТА',
					width: 120,
					sortable: true,
					xtype: 'datecolumn',
					format: (!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2) ? 'd.m.Y H:i':'d.m.Y H:i:s',
					type: 'date',
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

				},
				{itemId: 'CmpCallCard_Numv', dataIndex: 'CmpCallCard_Numv', sortable: true,  text: '№ В/Д', width: 50, filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'CmpCallCard_Ngod', dataIndex: 'CmpCallCard_Ngod', sortable: true, text: '№ В/Г', width: 50, hidden: getRegionNick().inlist(['ufa', 'krym', 'kz']), filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'EmergencyTeam_Num', dataIndex: 'EmergencyTeam_Num', sortable: true, text: 'БР', width: 50, renderer:function(v){if(v)return v;}, filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'Person_FIO', dataIndex: 'Person_FIO', sortable: true, text: 'Пациент', minWidth: 100, flex:1, filter: {xtype: 'transFieldDelbut', translate: false}},
				//{itemId: 'personAgeText', dataIndex: 'personAgeText', sortable: true, text: 'Возраст', width: 55, renderer:function(v){if(v)return v;}, filter: {xtype: 'transFieldDelbut', translate: false}},
				{
					itemId: 'Person_Birthday',
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
					itemId: 'CmpCallCard_IsExtraText',
					dataIndex: 'CmpCallCard_IsExtraText',
					text: 'Вид вызова',
					width: 150,
					filter: {xtype: 'swCmpCallTypeIsExtraCombo'},
					renderer:function(v){
						if(v){return (v == 'Экстренный')?'<span style="color:red;font-weight:bold">Экстренный</span>' : v;}
					}
				},
				{itemId: 'CmpCallCard_IsExtra', dataIndex: 'CmpCallCard_IsExtra',hidden: true, hideable: false},
				{itemId: 'CmpCallType_Name', dataIndex: 'CmpCallType_Name', sortable: true, text: 'Тип вызова', width: 80,
					filter: {
						xtype: 'swCmpCallTypeCombo',
						matchFieldWidth: false,
						displayTpl: '<tpl for=".">{CmpCallType_Name}</tpl>',
						listeners:{
						render:function(cmp){
							cmp.store = Ext.getCmp('CmpCallTypeComboHidden').store
						}}
					}
				},
				{itemId: 'CmpReason_Name', dataIndex: 'CmpReason_Name',sortable: true, text: 'Повод', minWidth: 60, flex:1,
					filter: {
						xtype: 'cmpReasonCombo', translate: false,
						defaultListConfig: {minWidth: 300, width: 300},
						listeners:{
							render:function(cmp){
								cmp.store = Ext.getCmp('CmpReasonComboStore').store
							}}
				}},
				{
					itemId: 'CmpCallCardAcceptor_Code', 
					dataIndex: 'CmpCallCardAcceptor_Code',
					text: 'СМП / НМП',
					width: 70,
					filter: {xtype: 'transFieldDelbut', translate: false},
					cls:'multiline',
					hidden: !getRegionNick().inlist(['astra'])
				},
				{
					itemId: 'LpuBuilding_Code', 
					dataIndex: 'LpuBuilding_Code',
					text: 'Подстанция',
					hidden: !getRegionNick().inlist(['astra']),
					width: 150,
					// filter: {xtype: 'transFieldDelbut', translate: false}
					filter: {
						xtype: 'smpUnitsNestedCombo',
						displayTpl: '<tpl for=".">{LpuBuilding_Code}</tpl>',
						tpl: '<tpl for="."><div class="x-boundlist-item">'+
						'{LpuBuilding_Code}'+ +' ' + '{LpuBuilding_Name}'+
						'</div></tpl>'
					}
				},
				{itemId: 'CmpCallCardEventType_Name', dataIndex: 'CmpCallCardEventType_Name', text: 'Событие', width: 120,
					filter: {
						xtype: 'swCmpCallCardEventTypeCombo', translate: false,
						matchFieldWidth: false,
						listeners:{
							render:function(cmp){
								cmp.store = Ext.getCmp('CmpCallCardEventTypeHidden').store
							}}
				}},
				{itemId: 'EventWaitDuration', dataIndex: 'EventWaitDuration', text: 'Время', width: 80,
					renderer: function(v){
					if(v){
						var newVal = {
							EventWaitDuration: 0
							};
						if(parseInt(v)) newVal.EventWaitDuration = parseInt(v);
						return me.getEventWaitDuration(newVal);
					}}, filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'EmergencyTeamDelayType_Name', dataIndex: 'EmergencyTeamDelayType_Name', text: 'Причина задержки', width: 140, hidden: true},
				{itemId: 'Adress_Name', dataIndex: 'Adress_Name',sortable: true, text: 'Адрес', minWidth: 120, flex:1, filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'EmergencyTeamSpec_Code', dataIndex: 'EmergencyTeamSpec_Code',sortable: true, text: 'ПР', width: 40, filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'countcallsOnTeam', dataIndex: 'countcallsOnTeam',sortable: true, text: 'Кол-во вызовов', width: 100,  renderer: function(v){ if(v>0)return v; }, filter: {xtype: 'transFieldDelbut', translate: false}},
				{itemId: 'DuplicateAndActiveCall_Count', dataIndex: 'DuplicateAndActiveCall_Count',sortable: true, text: 'Дубли / Акт. зв.', width: 60,cls:'multiline', hidden: me.isNmpArm},
				{itemId: 'CmpCallCard_IsQuarantineText', dataIndex: 'CmpCallCard_IsQuarantineText', text: 'Карантин', width: 100, hideable: true, //hidden: getRegionNick() != 'ufa',
					filter: {
						xtype: 'swYesNoCombo',
						editable: true,
						listeners: {
							render: function (cmp) {
								cmp.store = me.down('[name=CmpCallCard_IsQuarantine]').store
							}
						}
					}
				},
				{itemId: 'CmpCallCard_PlanDT', dataIndex: 'CmpCallCard_PlanDT',type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), text: 'Плановое время доезда', width: 100, hidden: (getRegionNick() != 'krym')},
				{itemId: 'CmpCallCard_FactDT', dataIndex: 'CmpCallCard_FactDT',type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), text: 'Фактическое время доезда', width: 100, hidden: (getRegionNick() != 'krym')},
				{itemId: 'isLate', dataIndex: 'isLate', text: 'Доезд с опозданием', width: 100, renderer: function(v){if(v == 1) return '<span style="color:red;font-weight:bold">V</span>'}, hidden: (getRegionNick() != 'krym')},
				{itemId: 'CmpPPDResult_Name', dataIndex: 'CmpPPDResult_Name', sortable: true, text: 'Результат обслуж. НМП', hidden: !me.isNmpArm, minWidth: 150, flex:1, filter: {xtype: 'transFieldDelbut', translate: false}},

				/*
				{itemId: 'KLCity_Name', dataIndex: 'KLCity_Name',sortable: false,  text: 'Город', flex:1},
				{itemId: 'KLTown_FullName', dataIndex: 'KLTown_FullName',sortable: false,  text: 'Нп', flex:1},
				{itemId: 'KLStreet_FullName', dataIndex: 'KLStreet_FullName',sortable: false,  text: 'Ул', flex:1},
				{itemId: 'CmpCallCard_Dom', dataIndex: 'CmpCallCard_Dom',sortable: false,  text: 'Д',  width: 50},
				{itemId: 'CmpCallCard_Korp', dataIndex: 'CmpCallCard_Korp',sortable: false,  text: 'Кп', width: 50},
				{itemId: 'CmpCallCard_Kvar', dataIndex: 'CmpCallCard_Kvar',sortable: false,  text: 'Кв', width: 50},
				{itemId: 'CmpCallCard_Room', dataIndex: 'CmpCallCard_Room',sortable: false,  text: 'Км',  width: 50},
				*/
				{itemId: 'isNewCall', dataIndex: 'isNewCall', hidden: true, hideable: false},
				{itemId: 'LpuBuilding_Nick', dataIndex: 'LpuBuilding_Nick', hidden: true, hideable: false},
				{itemId: 'LpuBuilding_id', dataIndex: 'LpuBuilding_id', hidden: true, hideable: false},
				{itemId: 'CmpGroup_id', dataIndex: 'CmpGroup_id', hidden: true, hideable: false},
				{itemId: 'CmpGroupName_id', dataIndex: 'CmpGroupName_id', hidden: true, hideable: false}
			]
		});

		var smpUnitsNestedCombo = Ext.create('sw.SmpUnitsNested', {
			name: 'LpuBuilding_id',
			labelWidth: 90,
			flex: 1,
			fieldLabel: 'Подразделение СМП',
			hidden: me.isNmpArm,
			labelAlign: 'right',
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
				labelWidth: 130,
				flex: 1,
				fieldLabel: 'Подразделение СМП',
				hidden: me.isNmpArm,
				labelAlign: 'right',
				displayTpl: '<tpl for=".">{LpuBuilding_Name}/{Lpu_Nick}</tpl>',
				tpl: '<tpl for="."><div class="x-boundlist-item">'+
				'{LpuBuilding_Name}/{Lpu_Nick}'+
				'</div></tpl>'
			}
		);

		Ext.applyIf(me, {
			items: [
				Ext.create('Ext.tab.Panel', {
					refId: 'mainTabPanelDP',
					border: false,
					items: [
						{
							xtype: 'BaseForm',
							id: this.id+'_mainPanel',
							title: 'Вызовы на обслуживании',
							layout: {
								type: 'fit'
							},
							items: [
								{
									xtype: 'swCmpCallTypeCombo',
									name: 'CmpCallType',
									id: 'CmpCallTypeComboHidden',
									fieldLabel: 'Тип вызова',
									hidden: true,
									editable: false
								},
								{
									xtype: 'swCmpCallCardEventTypeCombo',
									name: 'CmpCallCardEventType',
									id: 'CmpCallCardEventTypeHidden',
									fieldLabel: 'Тип события вызова',
									hidden: true,
									editable: false
								},
								{
									xtype: 'swYesNoCombo',
									name: 'CmpCallCard_IsQuarantine',
									fieldLabel: 'Карантин',
									//disabled: getRegionNick() != 'ufa',
									hidden: true
								},
								{
									xtype: 'container',
									layout: {
										type: 'hbox',
										align: 'stretch'
									},
									items: [
										{
											xtype: 'label',
											fieldLabel: 'Audio File',
											html:'<audio id="DispatchStantionWP_newCmpCallCardAudio"><source src="/audio/web/WavLibraryNet_Sound5825.mp3" type="audio/mpeg"></audio>'
										},
										this.topMenu,
										{
											xtype: 'panel',
											flex: 3,
											//width: 400,
											title: 'Вызовы',
											layout: {
												type: 'vbox',
												align: 'stretch'
											},
											id: this.id+'_callsWin',
											tools: [{
												type: 'customtool',
												width: 'auto',
												itemId: 'loadtool',
												renderTpl: [
													'<span class="loading-paneltool hiddentool"></span>'
												],
											}],
											items: [
												me.callsFilterPanel,
												//this.CallsGrid
												this.CallsView
											]
										},
										{
											xtype: 'container',
											refId: 'hrSplitterContainer',
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
											id: this.id+'_teamsWin',
											tools:[
												{
													type: 'gear',
													tooltip: 'Показать панель фильтров',
													refId: 'showTeamsFilterPanelBtn',
													// hidden:true,
													hidden: me.isNmpArm,
													handler: function(event, toolEl, panelHeader) {
														// this.EmergencyTeamDutyTimeGrid.toggleCollapse();
														if( checkedColumnEmergencyTeamDutyTimeGrid ) return;
														var me = this;
														var storeETDTG = me.EmergencyTeamDutyTimeGrid.getStore();
														if( storeETDTG.totalCount > 0 ){
															me.EmergencyTeamDutyTimeGrid.toggleCollapse();
														}else{
															// стор может быть еще не загружен
															storeETDTG.load({
																callback: function(){
																	me.EmergencyTeamDutyTimeGrid.toggleCollapse();
																}
															});
														}														
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
												me.teamsFilterPanel,
												this.TeamsView
											],
											dockedItems:[
												this.EmergencyTeamDutyTimeGrid
											]
										},
										{
											flex:1,
											height: 360,
											padding: 2,
											tbar: [
												//Ext.create('sw.datePrevDay'),
												Ext.create('sw.datePickerRange',{
														setExtraParams: true,
														dateFrom: Ext.Date.clearTime(Ext.Date.add((new Date()), Ext.Date.DAY, -1))
													}),
												//Ext.create('sw.dateNextDay'),
												Ext.create('sw.dateCurrentDay'),
												Ext.create('sw.dateCurrentWeek'),
												Ext.create('sw.dateCurrentMonth'),
												{ xtype: 'tbseparator'},
												{
													xtype: 'button',
													itemId: 'showCard',
													iconCls: 'view16',
													text: 'Просмотр',
													handler: function(){
														try
														{
															var cardid = me.shortViewPanel.getView().getSelectionModel().selected.items[0].get('CmpCallCard_id');
															if (cardid)
															{
																Ext.create('sw.callCardWindow',
																	{
																		view: 'view',
																		card_id: cardid
																	}).show()
															}
														}
														catch(e){
															Ext.MessageBox.alert('Ошибка', 'Не выбрана карта вызова!');
														}


													}
												},
												{
													xtype: 'button',
													itemId: 'refreshGrid',
													text: 'Обновить',
													iconCls: 'refresh16',
													handler: function(){
														me.shortViewPanel.store.reload();
														me.TeamsView.store.reload();
													}
												},
												{
													xtype: 'button',
													itemId: 'printListCards',
													text: 'Печать списка вызовов',
													iconCls: 'print16',
													handler: function(){
														Ext.ux.grid.Printer.print(me.shortViewPanel)
													}
												},
											],
											refId: 'shortViewPanelWrapper',
											hidden: true,
											layout:'border',
											items: [
												{
													xtype: 'panel',
													title: 'Вызовы',
													flex: 2,
													region: 'center',
													refId: 'CallsTableView',
													split: true,
													layout: {
														type: 'fit',
														align: 'stretch'
													},
													items: [
														this.shortViewPanel
													]
												},
												{
													xtype: 'gridpanel',
													flex: 1,
													split: true,
													region: 'west',
													title: 'Бригады',
													refId: 'teamsShortGrid',
													keepState: true,
													viewConfig: {
														loadingText: 'Загрузка',
														preserveScrollOnRefresh: true
													},
													store: storeTeams,
													requires: [
														'Ext.grid.feature.Grouping',
														'Ext.ux.grid.GridPrinter'
													],
													features: [{
														ftype: 'grouping',
														enableGroupingMenu: false,
														groupHeaderTpl: Ext.create('Ext.XTemplate',
//												'<div>{name:this.formatName} ({rows.length})</div>',
															'<div>{groupValue:this.getName(values)} ({name:this.formatName} / {rows.length})</div>',
															{
																getName: function(values){
																	return storeTeams.findRecord('LpuBuilding_id',values).get('LpuBuilding_Name')
																},
																formatName: function(name) {
																	/*
																	 var groupname = '';
																	 switch (name)
																	 {
																	 case 1: {groupname = 'Важные вызовы'; break;}
																	 case 2: {groupname = 'Внимание'; break;}
																	 case 3: {groupname = 'В работе'; break;}
																	 default: {groupname = 'Разное'; break;}
																	 }
																	 return name;
																	 */
																	var freeBrigades = 0;
																	storeTeams.each(function(record,index){
																		if(name == record.get('LpuBuilding_id') && record.get('EmergencyTeamStatus_FREE') == 'true'){
																			freeBrigades++;
																		}
																	});
																	return freeBrigades;
																}
															}
														),
														hideGroupedHeader: false,
														startCollapsed: false
													}],
													plugins: [Ext.create('Ext.ux.GridHeaderFilters',{enableTooltip: false,reloadOnChange:true,})],
													columns: [
														{
															dataIndex: 'EmergencyTeam_id',
															hidden: true
														},
														{
															dataIndex: 'EmergencyTeam_Num',
															text: 'БР',
															sortable: true,
															width: 40,
															filter: {xtype: 'transFieldDelbut', translate: false}
														},
														{
															dataIndex: 'EmergencyTeamStatus_Name',
															text: 'СТАТУС',
															sortable: true,
															width: 60,
															filter: {xtype: 'swEmergencyTeamStatuses', translate: false, matchFieldWidth: false,}
														},
														{
															dataIndex: 'EmergencyTeamStatusHistory_insDT',
															text: 'ВР (мин)',
															sortable: true,
															width: 55,
															filter: {xtype: 'transFieldDelbut', translate: false}
														},
														{
															dataIndex: 'Person_Fin',
															text: 'ФИО Врача',
															sortable: true,
															minWidth: 70,
															flex:1,
															filter: {xtype: 'transFieldDelbut', translate: false}
														},
														/*{
															dataIndex: 'CalculateAddress',
															text: 'МЕСТО',
															sortable: true,
															flex:1,
															filter: {xtype: 'transFieldDelbut', translate: false}
														},*/
														{
															dataIndex: 'lastCheckinAddress',
															text: 'МЕСТО',
															sortable: true,
															flex:1,
															hidden: !getRegionNick().inlist(['perm', 'kareliya']),
															filter: {xtype: 'transFieldDelbut', translate: false}
														},
														{
															dataIndex: 'EmergencyTeamSpec_Code',
															text: 'ПР',
															sortable: true,
															width: 40,
															filter: {xtype: 'transFieldDelbut', translate: false}
														},
													]
												},
												{
													xtype: 'container',
													region: 'east',
													refId: 'hrSplitterContainerTableView',
													layout: {
														type: 'hbox',
														align: 'middle'
													},
													items: [
														{
															xtype: 'button',
															iconCls: 'right-splitter',
															refId: 'hr-splitterTableView',
															height: 40,
															width: 13
														}
													]
												},
												{
													xtype: 'form',
													refId: 'CallDetailPanel',
													id: this.id+'_CallDetailPanel',
													flex: 1,
													//autoScroll: true,
													preserveScrollOnRefresh : true,
													blockRefresh: true,
													overflowY: 'scroll',
													title: 'Информация о вызове',
													layout: {type: 'vbox', align: 'stretch'},
													bodyPadding: '10 2',
													isLoading: false,
													//split: true,
													region: 'east',
													items: [
														{
															xtype: 'container',
															layout: 'hbox',
															margin: '4 0 4 0',
															items: [
																{
																	xtype: 'container',
																	layout: 'vbox',
																	minWidth: 220,
																	flex: 1,
																	margin: '4 0 4 0',
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Дата вызова',
																			labelAlign: 'right',
																			labelWidth: defaultLabelWidth + 10,
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			name: 'CmpCallCard_prmDate',
																			readOnly: true,
																			disabled: true,
																			flex: 1,
																			maxWidth: 220
																		},
																		{
																			xtype: 'datefield',
																			name: 'CmpCallCard_prmTime',
																			fieldLabel: 'Время',
																			format: 'H:i:s',
																			hideTrigger: true,
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
																			plugins: [new Ux.InputTextMask('99:99:99')],
																			labelAlign: 'right',
																			labelWidth: defaultLabelWidth + 10,
																			readOnly: true,
																			disabled: true,
																			flex: 1,
																			maxWidth: 220
																		},
																	]
																},
																{
																	xtype: 'container',
																	layout: 'vbox',
																	flex: 1,
																	margin: '4 0 4 0',
																	items: [
																		{
																			xtype: 'button',
																			text: 'Аудиозапись звонка',
																			hidden: true,
																			name: 'showAudioCallRecordWindow'
																		}
																	]
																},
															]
														},

														{
															xtype: 'container',
															layout: 'hbox',
															margin: '-6 0 4 0',
															items: [
																{
																	xtype: 'numberfield',
																	hideTrigger: true,
																	keyNavEnabled: false,
																	mouseWheelEnabled: false,
																	fieldLabel: '№ вызова (год):',
																	labelAlign: 'right',
																	labelWidth: 90,
																	name: 'CmpCallCard_Ngod',
																	readOnly: true,
																	disabled: true,
																	flex: 1,
																	hidden: true,
																	maxWidth: 220
																},
																{
																	xtype: 'numberfield',
																	hideTrigger: true,
																	keyNavEnabled: false,
																	mouseWheelEnabled: false,
																	fieldLabel: '№ вызова (д)',
																	labelAlign: 'right',
																	labelWidth: defaultLabelWidth + 10,
																	name: 'CmpCallCard_Numv',
																	readOnly: true,
																	disabled: true,
																	flex: 1,
																	maxWidth: 220
																}
															]
														},
														{
															xtype: 'fieldset',
															layout: {
																align: 'stretch',
																type: 'vbox'
															},
															title: 'Дата и время',
															refId: 'dateTimeFieldsetBlock',
															onTriggerClick: function(fieldcontainer, forceSet) {
																var timefield = fieldcontainer.child('[xtype="timefield"]');
																var datefield = fieldcontainer.child('[xtype="datefield"]');

																if((forceSet == true) || (timefield.getValue() == null && datefield.getValue() == null) ){
																	timefield.setValue(Ext.Date.format(new Date(), 'H:i'));
																	datefield.setValue(Ext.Date.format(new Date(), 'd.m.Y'));
																}
															},
															items: [
																{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Передачи выз. бриг.',
																			labelAlign: 'right',
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_DateTper',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			name: 'CmpCallCard_DateTperTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	]
																},
																{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Выезда бригады',
																			labelAlign: 'right',
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_DateVyez',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			name: 'CmpCallCard_DateVyezTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	]
																},
																{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Доезда бриг. до выз.',
																			labelAlign: 'right',
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_DatePrzd',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			name: 'CmpCallCard_DatePrzdTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	]
																},
																/*{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	hidden: me.isNmpArm,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Отъезда бриг. с выз.',
																			labelAlign: 'right',
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_DateTgsp',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			name: 'CmpCallCard_DateTgspTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ:CC',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	]
																},*/
																{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Закрытия вызова',
																			labelAlign: 'right',
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_DateTisp',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			name: 'CmpCallCard_TispTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	]
																},
																{
																	xtype: 'lpuAllLocalCombo',
																	flex: 1,
																	hidden: me.isNmpArm,
																	name: 'Lpu_hid',
																	fieldLabel: 'МО госпитализации',
																	bigFont: false,
																	displayTpl: '<tpl for=".">{Org_Nick}</tpl>',
																	tpl: '<tpl for=".">' +
																	'<div class="x-boundlist-item">' +
																	'{Org_Nick}' +
																	'</div></tpl>',
																	labelWidth: defaultLabelWidth
																},
																{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	hidden: me.isNmpArm,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Госпитализации',
																			labelAlign: 'right',
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_HospitalizedTime',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			//@todo потом поле будет другое
																			name: 'CmpCallCard_HospitalizedTimeTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	],
																	style: 'margin-bottom: -30px'
																},
																{
																	xtype: 'hidden',
																	name: 'CmpCallCard_IsPoli'
																},
																/*{
																	xtype: 'fieldcontainer',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	items: [
																		{
																			xtype: 'datefield',
																			fieldLabel: 'Отмены',
																			labelAlign: 'right',
																			labelWidth: 130,
																			format: 'd.m.Y',
																			plugins: [new Ux.InputTextMask('99.99.9999')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			name: 'CmpCallCard_cancelDate',
																			flex: 1,
																			allowBlank: true,
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			}
																		},
																		{
																			xtype: 'timefield',
																			name: 'CmpCallCard_cancelTime',
																			format: 'H:i',
																			invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
																			plugins: [new Ux.InputTextMask('99:99')],
																			validateOnBlur: false,
																			validateOnChange: false,
																			flex: 1,
																			maxWidth: 60,
																			allowBlank: true,
																			alias: 'widget.timeGetCurrentTimeCombo',
																			triggerCls: 'x-form-clock-trigger',
																			cls: 'stateCombo',
																			listeners: {
																				'focus': function (inp, e) {
																					e.stopEvent();
																					inp.ownerCt.ownerCt.onTriggerClick(inp.ownerCt);
																				}
																			},
																			onTriggerClick: function(e) {
																				e.stopEvent();
																				this.ownerCt.ownerCt.onTriggerClick(this.ownerCt,true);
																			}
																		}
																	]
																},
																*/
																{
																	xtype: 'hidden',
																	name: 'CmpCallCard_id'
																},
																{
																	xtype: 'hidden',
																	name: 'CmpCallCardStatusType_id'
																},
																{
																	xtype: 'hidden',
																	name: 'CmpCallCard_rid'
																},
																{
																	xtype: 'hidden',
																	name: 'pcCmpCallCard_Numv'
																},
																{
																	xtype: 'hidden',
																	name: 'CmpCallCardDubl_id'
																},
																{
																	xtype: 'hidden',
																	name: 'CmpCallRecord_id'
																},
																{
																	xtype: 'hidden',
																	name: 'Person_id'
																}
															]
														},
														{
															xtype: 'fieldset',
															layout: {
																align: 'stretch',
																type: 'vbox'
															},
															title: 'Место вызова',
															defaults: {
																labelWidth: defaultLabelWidth
															},
															items: [
																{
																	xtype: 'dCityCombo',
																	name: 'dCityCombo',
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	flex: 1,
																	listeners:{
																		select: function(inp, e){
																			var streetsCombo = me.down('form').getForm().findField('dStreetsCombo');
																			var secondStreetCombo = me.down('form').getForm().findField('secondStreetCombo');
																			streetsCombo.bigStore.getProxy().extraParams = {
																				town_id: e[0].get('Town_id'),
																				Lpu_id: sw.Promed.MedStaffFactByUser.current.Lpu_id
																			};
																			streetsCombo.reset();
																			streetsCombo.bigStore.load({
																				callback: function(recs){
																					secondStreetCombo.bigStore.loadData(recs);
																				}
																			});
																		}
																	}
																},
																{
																	xtype: 'swStreetsSpeedCombo',
																	name:'dStreetsCombo',
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	labelAlign: 'right',
																	fieldLabel: 'Улица',
																	flex: 1,
																	tpl: new Ext.XTemplate(
																		'<tpl for="."><div class="x-boundlist-item" style="font: 14px tahoma,arial,verdana,sans-serif;">'+
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
																	)
																},
																{
																	xtype: 'swStreetsSpeedCombo',
																	name:'secondStreetCombo',
																	enableKeyEvents : true,
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	labelAlign: 'right',
																	//hidden: true,
																	fieldLabel: 'Улица',
																	flex: 1,
																	tpl: new Ext.XTemplate(
																		'<tpl for="."><div class="x-boundlist-item" style="font: 14px tahoma,arial,verdana,sans-serif;">'+
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
																	)
																},
																{
																	xtype: 'textfield',
																	plugins: [new Ux.Translit(true, true)],
																	fieldLabel: 'Дом',
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	labelAlign: 'right',
																	name: 'CmpCallCard_Dom',
																	enableKeyEvents : true,
																	flex: 1
																},
																{
																	xtype: 'textfield',
																	plugins: [new Ux.Translit(true, true)],
																	fieldLabel: 'Корп',
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	enforceMaxLength: true,
																	maxLength: 5,
																	// hidden: (!getRegionNick().inlist(['ufa', 'krym', 'kz'])),
																	labelAlign: 'right',
																	name: 'CmpCallCard_Korp',
																	enableKeyEvents : true,
																	flex: 1
																},
																{
																	xtype: 'fieldcontainer',
																	margin: '4 0',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'textfield',
																			//maskRe: /[0-9:]/,
																			enforceMaxLength: true,
																			maxLength: 5,
																			plugins: [new Ux.Translit(true, true)],
																			fieldLabel: 'Кв.',
																			readOnly: getRegionNick().inlist(['ufa']),
																			disabled: getRegionNick().inlist(['ufa']),
																			labelAlign: 'right',
																			name: 'CmpCallCard_Kvar',
																			enableKeyEvents : true,
																			flex: 1
																		},
																		{
																			xtype: 'textfield',
																			maskRe: /[0-9:]/,
																			fieldLabel: 'Под.',
																			readOnly: getRegionNick().inlist(['ufa']),
																			disabled: getRegionNick().inlist(['ufa']),
																			labelAlign: 'right',
																			name: 'CmpCallCard_Podz',
																			enableKeyEvents : true,
																			labelWidth: 60,
																			flex: 1
																		}

																	]
																},
																{
																	xtype: 'fieldcontainer',
																	margin: '4 0',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	defaults: {
																		labelWidth: defaultLabelWidth
																	},
																	items: [
																		{
																			xtype: 'textfield',
																			maskRe: /[0-9:]/,
																			fieldLabel: 'Этаж',
																			readOnly: getRegionNick().inlist(['ufa']),
																			disabled: getRegionNick().inlist(['ufa']),
																			labelAlign: 'right',
																			name: 'CmpCallCard_Etaj',
																			enableKeyEvents : true,
																			flex: 1
																		},
																		{
																			xtype: 'textfield',
																			fieldLabel: 'Код',
																			readOnly: getRegionNick().inlist(['ufa']),
																			disabled: getRegionNick().inlist(['ufa']),
																			labelAlign: 'right',
																			name: 'CmpCallCard_Kodp',
																			enableKeyEvents : true,
																			labelWidth: 60,
																			flex: 1
																		}
																	]
																},
																{
																	xtype: 'swCmpCallPlaceType',
																	name:'CmpCallPlaceType_id',
																	fieldLabel: 'Тип места',
																	labelAlign: 'right',
																	value: 1,
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	triggerClear: true,
																	hideTrigger:true,
																	flex: 1,
																	displayTpl: '<tpl for="."> {CmpCallPlaceType_Code}. {CmpCallPlaceType_Name} </tpl>',
																	tpl: '<tpl for="."><div class="x-boundlist-item">'+
																	'<font color="red">{CmpCallPlaceType_Code}</font> {CmpCallPlaceType_Name}'+
																	'</div></tpl>'
																},
																{
																	xtype: 'swCmpCallerTypeCombo',
																	name: 'CmpCallerType_id',
																	triggerClear: true,
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	hideTrigger:true,
																	autoFilter: false,
																	forceSelection: false,
																	autoSelect: false,
																	labelAlign: 'right',
																	flex: 1,
																	fieldLabel: 'Кто выз.',
																	minChars:2,
																	tpl: '<tpl for="."><div class="x-boundlist-item">'+
																	'{CmpCallerType_Name}'+
																	'</div></tpl>'
																},
																{
																	xtype: 'textfield',
																	fieldLabel: 'Телефон',
																	enableKeyEvents : true,
																	maskRe: /[0-9:]/,
																	readOnly: getRegionNick().inlist(['ufa']),
																	disabled: getRegionNick().inlist(['ufa']),
																	hidden: getRegionNick().inlist(['ufa']),
																	labelAlign: 'right',
																	name: 'CmpCallCard_Telf',
																	flex: 1,
																	/*
																	 //cls: 'x-form-table-div',
																	 //triggerCls: 'x-form-eye-trigger-default',
																	 //inputType: 'password',
																	 listeners: {

																	 },
																	 onTriggerClick: function() {
																	 var input = this.inputEl.dom;
																	 if(!(input.getAttribute('disabled') == 'disabled'
																	 && !getRegionNick().inlist(['ufa', 'krym', 'kz'])))
																	 {
																	 var toPass = (input.getAttribute('type') == 'text'),
																	 val = toPass?'password':'text';
																	 this.triggerEl.elements[0].dom.classList.toggle('x-form-eye-open-trigger');
																	 input.setAttribute('type',val);
																	 }
																	 }
																	 */
																}
															]
														},
														{
															xtype: 'fieldset',
															layout: {
																type: 'vbox',
																align: 'stretch'
															},
															title: 'Пациент',
															defaults: {
																labelWidth: defaultLabelWidth
															},
															items: [
																{
																	xtype: 'textfield',
																	plugins: [new Ux.Translit(true, true)],
																	flex: 1,
																	fieldLabel: 'Фамилия',
																	labelAlign: 'right',
																	name: 'Person_SurName',
																	enableKeyEvents : true
																},
																{
																	xtype: 'textfield',
																	plugins: [new Ux.Translit(true, true)],
																	flex: 1,
																	fieldLabel: 'Имя',
																	labelAlign: 'right',
																	name: 'Person_FirName',
																	enableKeyEvents : true
																},
																{
																	xtype: 'textfield',
																	plugins: [new Ux.Translit(true, true)],
																	flex: 1,
																	fieldLabel: 'Отчество',
																	labelAlign: 'right',
																	name: 'Person_SecName'
																},
																{
																	xtype: 'fieldcontainer',
																	margin: '4 0',
																	flex: 1,
																	layout: {
																		align: 'stretch',
																		type: 'hbox'
																	},
																	items: [
																		{
																			xtype: 'hidden',
																			name: 'Person_AgeInt'
																		},
																		{
																			xtype: 'hidden',
																			name: 'Person_Birthday'
																		},
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
																			flex: 1,
																			labelWidth: defaultLabelWidth //не проставилось в defaults fieldsetA
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
																			width: 100,
																			triggerClear: false
																		}
																	]
																},
																{
																	xtype: 'sexCombo',
																	labelAlign: 'right',
																	flex: 1,
																	name: "Sex_id"
																},
																/*{
																	xtype: 'swDSexCombo',
																	labelAlign: 'right',
																	flex: 1,
																	labelWidth: 90
																},*/
																{
																	xtype: 'textfield',
																	fieldLabel: '№ полиса',
																	labelAlign: 'right',
																	flex: 1,
																	name: 'Polis_Num',
																	disabled: true
																},
																/*{
																 xtype: 'container',
																 flex: 1,
																 margin: '4 5 10',
																 layout: {
																 type: 'hbox',
																 align: 'stretch'
																 },
																 items: [
																 {
																 xtype: 'button',
																 refId: 'identPersonBtn',
																 name: 'identPersonBtn',
																 text: 'Идентифицировать',
																 margin: '5 5',
																 height: 27
																 }, {
																 xtype: 'button',
																 name: 'searchPersonBtn',
																 refId: 'searchPersonBtn',
																 text: 'Поиск',
																 iconCls: 'search16',
																 margin: '5 5',
																 height: 27
																 }
																 ]
																 }*/
															]
														},
														{
															xtype: 'fieldset',
															layout: {
																align: 'stretch',
																type: 'vbox'
															},
															defaults: {
																labelWidth: defaultLabelWidth
															},
															title: 'Вызов',
															items: [
																{
																	xtype: 'swCmpCallTypeCombo',
																	name: 'CmpCallType_id',
																	flex: 1,
																	fieldLabel: 'Тип вызова',
																	refId: 'ShortCardCallType'
																},
																{
																	xtype: 'swCmpCallTypeIsExtraCombo',
																	fieldLabel: 'Вид вызова',
																	labelAlign: 'right',
																	enableKeyEvents : true,
																	flex: 1,
																	name: 'CmpCallCard_IsExtra',
																	refId: 'ShortCardIsExtra'
																},
																{
																	xtype: 'cmpReasonCombo',
																	name: 'CmpReason_id',
																	id: 'CmpReasonComboStore',
																	flex: 1,
																	refId: 'ShortCardReasonCombo'
																},
																{
																	xtype: 'textfield',
																	name: 'CmpCallCard_Urgency',
																	flex: 1,
																	fieldLabel: 'Срочность',
																	enableKeyEvents : true,
																	labelAlign: 'right',
																	hidden: me.isNmpArm
																},
																{
																	xtype: 'textfield',
																	fieldLabel: 'Профиль',
																	labelAlign: 'right',
																	//name: 'CmpCallCard_Profile',
																	name: 'EmergencyTeamSpec_Code',
																	enableKeyEvents : true,
																	flex: 1
																},
																{
																	// xtype: 'smpDutyAmbulanceTeamCombo',
																	xtype: 'textfield',
																	fieldLabel: '№ бригады',
																	labelAlign: 'right',
																	name: 'EmergencyTeam_Num',
																	flex: 1,
																	// displayTpl: '<tpl for="."> {EmergencyTeam_Num}. {Person_Fin} </tpl>',
																	enableKeyEvents : true
																},
																{
																	xtype: 'hidden',
																	name: 'EmergencyTeam_id'
																},
																{
																	xtype: 'textfield',
																	name:'EmergencyTeam_HeadDocName',
																	fieldLabel: 'Старший бригады',
																	labelAlign: 'right',
																	enableKeyEvents : true,
																	flex: 1
																},
																{
																	xtype: 'swmedpersonalcombo',
																	name: 'DPMedPersonal_id',
																	fieldLabel: 'Диспетчер вызова',
																	labelAlign: 'right',
																	enableKeyEvents : true,
																	flex: 1,
																	displayTpl: '<tpl for=".">{Person_Fin} </tpl>',
																},
																{
																	xtype: 'checkbox',
																	flex: 1,
																	hidden: me.isNmpArm,
																	name: 'CmpCallCard_IsPoli',
																	boxLabel: 'Вызов передан в поликлинику по телефону (рации)',
																	margin: '0 0 0 95'
																},
																{
																	xtype: 'lpuLocalCombo',
																	name: 'Lpu_ppdid',
																	flex: 1,
																	validateOnBlur: false,
																	fieldLabel: 'МО передачи (НМП)'
																},
																{
																	fieldLabel: 'Служба НМП',
																	allowBlank: true,
																	flex: 1,
																	xtype: 'selectNmpCombo',
																	hiddenName: 'MedService_id',
																	isClose: 1
																},
																{
																	xtype: 'checkbox',
																	flex: 1,
																	hidden: me.isNmpArm,
																	margin: '0 0 0 95',
																	name: 'CmpCallCard_IsPassSSMP',
																	boxLabel: 'Вызов передан в другую ССМП по телефону (рации)'
																},
																{
																	xtype: 'lpuAllLocalCombo',
																	name: 'Lpu_smpid',
																	hidden: me.isNmpArm,
																	flex: 1,
																	fieldLabel: 'МО передачи (СМП)'
																},
																(getGlobalOptions().smp_allow_transfer_of_calls_to_another_MO != 1) ? smpUnitsNestedCombo : smpRegionUnitsCombo,


																/*{
																 xtype: 'lpuLocalCombo',
																 name: 'Lpu_id',
																 flex: 1,
																 fieldLabel: 'ЛПУ',
																 labelWidth: 90,
																 displayTpl: '<tpl for=".">{MedService_Nick} / {Lpu_Nick}</tpl>',
																 tpl: '<tpl for=".">' +
																 '<div class="x-boundlist-item">' +
																 '{MedService_Nick}' +
																 ' / ' +
																 '{Lpu_Nick}' +
																 '</div></tpl>'
																 },*/
																{
																	xtype: 'textareafield',
																	flex: 1,
																	margin: '4 0',
																	//plugins: [new Ux.Translit(true)],
																	minHeight: 50,
																	fieldLabel: 'Доп. информация:',
																	enableKeyEvents : true,
																	labelAlign: 'right',
																	name: 'CmpCallCard_Comm'
																}
															]
														}
													],
													dockedItems: [
														{
															xtype: 'toolbar',
															dock: 'bottom',
															items: [
																{ xtype: 'tbfill' },
																{
																	xtype: 'button',
																	refId: 'saveShortCardBtn',
																	iconCls: 'save16',
																	text: 'Сохранить'
																}
															]
														}
													]
												}
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
										},
										{
											xtype: 'button',
											text: 'refresh',
											refId: 'refresh',
											height: 10,
											width: 40,
											hidden: true
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
							refId: 'servedCallsListTab',
							//id: 'callsListDP',
							title: 'Обслуженные вызовы',
							hidden: me.isNmpArm,
							items: [
								Ext.create('sw.CmpServedCallsList')
							],
							listeners: {
								activate: function(container) {
									if(container.tab.hasCls('attentionale')){
										container.tab.removeCls('attentionale');
									}
								},
								deactivate: function(container){
									if(container.initialConfig.title != container.tab.text){
										if(!container.tab.hasCls('attentionale')){
											container.tab.addCls('attentionale');
										}
									}
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
							//id: 'callsListDP',
							title: 'Журнал вызовов',
							items: [
								Ext.create('sw.CmpCallsList', {armtype: 'smpdispatchstation'})
							],
							listeners: {
								activate: function (tab) {
									var journal = tab.child();
									if(journal.store) journal.getStore().load();
								}
							}
						}
					]
				})
			]
		});
		me.callParent(arguments);
	},
	getEventWaitDuration: function(val){
		var time;
		function addZero(time){
			if(time<10)
				time = '0' + time;
			return time;
		}
		function formatTime(time){
			var nTime;
			if(time<60)
				nTime = '00:' + addZero(time);
			else
				nTime = addZero(Math.floor((time/60))) + ':' + addZero(time%60);
			return nTime
		}
		time = formatTime(val.EventWaitDuration);
		if( val.timeEventBreak === 'true' )
			return '<span style="color: red;">' + time + '</span>';
		else
			return'<span>' + time + '</span>';

	}
});

