/*
 * Новый АРМ старшего смены
 */

Ext.define('common.HeadDutyWP.swSmpHeadDutyWorkPlace', {
    extend: 'Ext.window.Window',
	alias: 'widget.swSmpHeadDutyWorkPlace',
    autoShow: true,
	maximized: true,
	width: 1000,
	refId: 'smpHeadDutyWorkPlace',
	closable: false,
	baseCls: 'arm-window',
	title: 'Рабочее место старшего смены СМП',
    header: false,
	renderTo: Ext.getCmp('inPanel').body,
	layout: {
        type: 'fit'
    },
	
	constrain: true,	
	onEsc: Ext.emptyFn,	
	
    initComponent: function() {
		var me = this;
		
		/*
		me.topToolbar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'top',
			refId: 'SSTopToolbar',
			items: [
				{
					xtype: 'button',
					text: 'Новый вызов',
					iconCls: 'add16',
					tabIndex: 1,
					handler: function() {
		 				getWnd('swWorkPlaceSMPDispatcherCallWindow').show({
		 					swDispatcherCallWorkPlaceInstance_modal: ture
					   });
					}
				},
				{
					xtype: 'splitbutton',
					iconCls: 'ambulance_add16',
					text: 'Наряд',
					tabIndex: 2,					
					menu: {
						xtype: 'menu',
						items: [
							{
								xtype: 'menuitem',
								text: 'Шаблоны',
								iconCls: 'inbox16',
								//refId: 'editEmergencyTeamTemplate'
								handler: function() {									
									//шаблоны
									var EmergencyTeamTemplateWindow = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamTemplateWindow', {
										layout: {
											type: 'fit',
											align: 'stretch'
										},
										maximized: true,
										constrain: true,
										renderTo: Ext.getCmp('inPanel').body
									});
									EmergencyTeamTemplateWindow.show();
								}
							},
							{
								xtype: 'menuitem',
								text: 'Текущий наряд',
								iconCls: 'eph-timetable-top16',
								//refId: 'currentEmergencyTeamStuff'
								handler: function() {		
									//текущий наряд
									var CurrentEmergencyTeamStuff = Ext.create('common.DispatcherStationWP.tools.swCurrentEmergencyTeamStuff',
									{
										layout: {
											type: 'fit',
											align: 'stretch'
										},
										maximized: true,
										constrain: true,
										renderTo: Ext.getCmp('inPanel').body
									});
									CurrentEmergencyTeamStuff.show();
								}
							},
							{
								xtype: 'menuitem',
								text: 'Формирование наряда',
								iconCls: 'eph-record16',
								//refId: 'setEmergencyTeamDutyTime'
								handler: function() {		
									//Формирование наряда
									var EmergencyTeamTemplateSetDuty = Ext.create('common.DispatcherStationWP.tools.swEmergencyTeamTemplateSetDuty',
									{
										layout: {
											type: 'fit',
											align: 'stretch'
										},
										maximized: true,
										constrain: true,
										renderTo: Ext.getCmp('inPanel').body
									});
									EmergencyTeamTemplateSetDuty.show();
								}
							}
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
					text: 'Сервис',
					tabIndex: 3,					
					menu: {
						xtype: 'menu',
						items: [							
							{
								xtype: 'menuitem',
								text: 'Оперативная обстановка по диспетчерам',
								iconCls: 'emergency-list16',
								//refId: 'dispatchOperEnvWindow'
								handler: function() {
									Ext.create('sw.tools.swDispatchOperEnvWindow').show();
								}
							},
							{
								xtype: 'menuitem',
								text: 'Учет Путевых листов',
								iconCls: 'reports16',
								//refId: 'smpWaybillsViewWindow'
								handler: function() {
									Ext.create('sw.tools.swWaybillsWindow').show();
								}
							},
							{
								xtype: 'menuitem',
								text: 'Справочник объектов',
								iconCls: 'reports16',
								//refId: 'unformalizedAddressDirectoryEditWindow'
								handler: function() {
									Ext.create('sw.tools.swUnformalizedAddressDirectoryEditWindow').show();
								}
							},
							{
								xtype: 'menuitem',
								text: 'Дерево решений',
								iconCls: 'structure16',
								//refId: 'DecigionTreeEditWindow'
								handler: function() {
									Ext.create('common.DispatcherStationWP.tools.swDecigionTreeEditWindow').show();
								}
							}
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
					tabIndex: 4,
					handler: function() {
						Ext.create('sw.tools.swSmpFarmacyRegisterWindow').show();
					}
				},
				{
					xtype: 'button',
					text: 'Отметки о выходе на смену',
					tabIndex: 5,
					iconCls: 'address-book16',
					handler: function() {
						Ext.create('sw.tools.swSmpEmergencyTeamSetDutyTimeWindow',{
							listeners:{
								reloadTeams: function(rec){
									storeTeams.reload();							
								}
							}
						}).show();
					}
				},
				{
					xtype: 'button',
					text: 'Wialon',
					tabIndex: 6,
					id: this.id+'wialon_btn',
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
					tabIndex: 7,
					id: this.id+'glonass_btn',
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
					xtype: 'button',
					text: '',
					enableToggle: true,										
					tabIndex: 7,
					toggleGroup: 'viewToggle',
					id: this.id+'vid1_btn',
					iconCls: 'document16',					
					handler: function() {
						me.hide();
					}
				}, {
					xtype: 'button',					
					text: '',
					enableToggle: true,
					tabIndex: 7,
					pressed: true,
					toggleGroup: 'viewToggle',
					id: this.id+'vid2_btn',
					iconCls: 'window16',					
					handler: function() {}
				}
			]
		});
		*/
		
		var callsCardsPanel = Ext.create('sw.callCardsPanel', 
		{
			actions: [/*'add', */'view', /*'edit', 'delete', */'refresh', 'print'/*, 'confirm', 'discard', 'served', 'closeCard', 'print110', 'abort', 'passToNMP'*/],
			tools: [
				//'farmacyRegisterWindow', 
				//'smpEmergencyTeamOperEnvWindow', 
				//'smpEmergencyTeamSetDutyWindow', 
				//'dispatchOperEnvWindow', 
				//'smpWaybillsViewWindow',
				//'unformalizedAddressDirectoryEditWindow',
				//'DecigionTreeEditWindow'
			]
		});
		
		me.bottomToolBar = Ext.create('Ext.toolbar.Toolbar',{
			dock: 'bottom',
			items: [
				{
					xtype: 'button',					
					text: 'Помощь',
					iconCls   : 'help16',
					tabIndex: 30,
					handler   : function()
					{
						ShowHelp(me.title);
						//window.open('/wiki/main/wiki/Карта_вызова:_Добавление');										
					}
				}				
			]
		});
		
		Ext.applyIf(me, {
            items: [
                {
                    xtype: 'BaseForm',
					id: 'smpHeadDutyWin',
					cls: 'smpHeadDutyWin',
                    items: [
                           callsCardsPanel
						]
					}
				],
			dockedItems: [
				//me.topToolbar,
				//me.bottomToolBar
			]	
		})
		Ext.apply(me)
		
		me.callParent(arguments);
    },
	
	listeners:{
		beforerender: function(cmp){
			//прикручиваем ноджс
			//connectNode(cmp);
		}
	}

});
