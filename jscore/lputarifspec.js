/**
* Загрузчик модуля с обработчиком onReady
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Init
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
*               Bykov Stas aka Savage (savage1981@gmail.com)
* @version      10.04.2009
*/

// TODO: Требует тщательного причесывания и продумывания логики скрытия/отображения меню (+ потому что меню надо будет выводить согласно правам).

var is_ready = false;
var taskbar = null;
Ext.ns('sw.codeInfo');
sw.codeInfo = {};
	//var $isFarmacy = false;
/*
var swLpuBuildingGlobalStore = null;
var swLpuSectionGlobalStore = null;
var swLpuSectionWardGlobalStore = null;
var swLpuUnitGlobalStore = null;
var swMedStaffFactGlobalStore = null;
var swProMedUserGlobalStore = null;
*/

// Функция загрузки модулей Промед
function loadModules()
{
	is_ready = true;

	// TODO: Проблема определения админа на клиенте в том, что эти данные всегда можно подделать для клиента
	// С одной стороны это хорошо, потому что позволяет набором инструкций получать доступ к определенному функционалу для тестирования в случае ошибки 
	isAdmin = (/SuperAdmin/.test(getGlobalOptions().groups));

	// Акшены
	sw.Promed.Actions =
	{
		LpuStructureViewAction: new Ext.Action(
		{
			text: MM_LPUSTRUC,
			tooltip: 'Структура МО',
			iconCls : 'lpu-struc16',
			hidden: !isAdmin && !isLpuAdmin() &&  !isCadrUserView() && !isUserGroup('OuzSpec'),
			handler: function()
			{
				getWnd('swLpuStructureViewForm').show();
			}
		}),
		ReportStaffAction: new Ext.Action(
		{
			text: 'Отчет: Штатное расписание',
			tooltip: 'Отчет: Штатное расписание',
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'staff');
			}/*,
			hidden: !isAdmin*/
		}),
		ReportTarifAction: new Ext.Action(
		{
			text: 'Отчет: Тарификационные списки',
			tooltip: 'Отчет: Тарификационные списки',
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'tarif');
			}/*,
			hidden: !isAdmin*/
		}),
		ReportLabourAction: new Ext.Action(
		{
			text: 'Сигнальная ведомость: Процент надбавки за стаж',
			tooltip: 'Сигнальная ведомость: Процент надбавки за стаж',
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'labour');
			}/*,
			hidden: !isAdmin*/
		}),
		ReportCategoryAction: new Ext.Action(
		{
			text: 'Сигнальная ведомость: Срок действия категории',
			tooltip: 'Сигнальная ведомость: Срок действия категории',
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'category');
			}/*,
			hidden: !isAdmin*/
		}),
		UserProfileAction: new Ext.Action(
		{
			text: 'Профиль пользователя',
			tooltip: 'Профиль пользователя',
			iconCls : 'test16',
			hidden: (!isAdmin && IS_DEBUG == 1),
			handler: function()
			{
				getWnd('swUserProfileEditWindow').show();
			}
		}),
		PromedHelp: new Ext.Action(
		{
			text: 'Вызов справки',
			tooltip: 'Помощь по программе',
			iconCls : 'help16',
			handler: function()
			{
				ShowHelp('Содержание');
			}
		}),
		PromedForum: new Ext.Action(
		{
			text: 'Форум поддержки',
			iconCls: 'support16',
			xtype: 'tbbutton',
			handler: function() {
				window.open('http://172.22.99.1/forum/index.php?showforum=2');
			}
		}),
		PromedAbout: new Ext.Action(
		{
			text: 'О программе',
			tooltip: 'Информация о программе',
			iconCls : 'promed16',
			handler: function()
			{
				getWnd('swAboutWindow').show();
			}
		}),
		PromedExit: new Ext.Action( {
			text:'Выход',
			iconCls: 'exit16',
			handler: function()
			{
				sw.swMsg.show({
					title: 'Подтвердите выход',
					msg: 'Вы действительно хотите выйти?',
					buttons: Ext.Msg.YESNO,
					fn: function ( buttonId ) {
						if ( buttonId == 'yes' ) {
							window.onbeforeunload = null;
							window.location=C_LOGOUT;
						}
					}
			});
			}
		}),
		swOptionsViewAction: new Ext.Action(
		{
			text: 'Настройки',
			tooltip: 'Просмотр и редактирование настроек',
			iconCls : 'settings16',
			handler: function()
			{
				getWnd('swOptionsWindow').show();
			}
		}),
		swLpuSelectAction: new Ext.Action(
		{
			text: 'Выбор МО',
			tooltip: 'Выбор МО',
			iconCls: 'lpu-select16',
			handler: function()
			{
				Ext.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						wnd.hide();
					}
				});
				getWnd('swSelectLpuWindow').show({});
			},
			hidden: !( String(getGlobalOptions().groups).indexOf('RosZdrNadzorView', 0) >= 0 ) // проверяем так же просмотр медперсонала
		}),

		WorkPlace: new Ext.Action({text: 'Места работы',tooltip: 'Места работы',iconCls: '',handler: function(){window.gwtBridge.runDictionary(getPromedUserInfo(), 'WorkPlace', main_center_panel);}}),
		Staff: new Ext.Action({text: 'Строки штатного расписания',tooltip: 'Строки штатного расписания',iconCls: '',handler: function(){window.gwtBridge.runDictionary(getPromedUserInfo(), 'Staff', main_center_panel);}}),
		PaymentKind: new Ext.Action({text: 'Виды выплат',tooltip: 'Виды выплат',iconCls: '',handler: function(){window.gwtBridge.runDictionary(getPromedUserInfo(), 'PaymentKind', main_center_panel);}}),
		ProfessionalGroup: new Ext.Action({text: 'Профессиональные группы',tooltip: 'Профессиональные группы',iconCls: '',handler: function(){window.gwtBridge.runDictionary(getPromedUserInfo(), 'ProfessionalGroup', main_center_panel);}})
	};

		
		// Формирование обычного меню 
		this.menu_passport_lpu = new Ext.menu.Menu(
		{
			id: 'menu_passport_lpu',
			items:
			[
				sw.Promed.Actions.LpuStructureViewAction/*,
				{
					text: 'Медицинский персонал',
					hidden: !isAdmin && !isLpuAdmin(),
					iconCls : 'staff16',
					menu: new Ext.menu.Menu(
					{
						id: 'menu_spr_mp',
						items:
						[
							sw.Promed.Actions.MedPersonalPlaceAction,
							sw.Promed.Actions.MedPersonalSearchAction
						]
					})
				},*/
			]
		});
		
		this.menu_reports = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_reports',
			items: [
				sw.Promed.Actions.ReportStaffAction,
				sw.Promed.Actions.ReportTarifAction,
				sw.Promed.Actions.ReportLabourAction,
				sw.Promed.Actions.ReportCategoryAction
			]
		});

		this.menu_1 = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_1',
			items: [
			 	//Места работы		 window.gwtBridge.runDictionary(window.promedUserInfo, 'WorkPlace', main_center_panel);
				sw.Promed.Actions.WorkPlace,
				//Строки штатного расписания		 window.gwtBridge.runDictionary(window.promedUserInfo, 'Staff', main_center_panel);
				sw.Promed.Actions.Staff,
				//Виды выплат		 window.gwtBridge.runDictionary(window.promedUserInfo, 'PaymentKind', main_center_panel);
				sw.Promed.Actions.PaymentKind,
				//Профессиональные группы		 window.gwtBridge.runDictionary(window.promedUserInfo, 'ProfessionalGroup', main_center_panel);
				sw.Promed.Actions.ProfessionalGroup
			]
		});

		this.menu_windows = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_windows',
			items: [
				'-'
			]
		});

		this.menu_help = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_help',
			items:
			[
				sw.Promed.Actions.PromedHelp,
				sw.Promed.Actions.PromedForum,
				'-',
				sw.Promed.Actions.PromedAbout
			]
		});

		this.menu_exit = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_help',
			items:
			[
				sw.Promed.Actions.PromedHelp, sw.Promed.Actions.PromedAbout
			]
		});
		if ( isFarmacyUser() ) {
			this.user_menu = new Ext.menu.Menu(
			{
				//plain: true,
				id: 'user_menu',
				items:
				[
					{
						disabled: true,
						iconCls: 'user16',
						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'Аптека : '+Ext.globalOptions.globals.OrgFarmacy_Nick,
						xtype: 'tbtext'
					}
				]
			});
		} else {
			this.user_menu = new Ext.menu.Menu(
			{
				//plain: true,
				id: 'user_menu',
				items:
				[
					{
						disabled: true,
						iconCls: 'user16',
						text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
						xtype: 'tbtext'
					}
				]
			});
		}		

	// панель меню
	main_menu_panel = new sw.Promed.Toolbar({
		autoHeight: true,
		region: 'north',
		items:
		[
			{
				text: MM_LPUSTRUC,
				tooltip: 'Структура МО',
				iconCls : 'lpu-struc16',
				hidden: !isAdmin && !isLpuAdmin() &&  !isCadrUserView(),
				menu: this.menu_1,
				tabIndex: -1
			},
			//sw.Promed.Actions.LpuStructureViewAction,
			sw.Promed.Actions.swLpuSelectAction,
			'-',
			{
				text:'Отчеты',
				iconCls: 'reports16',
				menu: this.menu_reports,
				tabIndex: -1
			},
			'-',
			
			{
				text: 'Окна',
				iconCls: 'windows16',
				listeners: {
					'click': function(e) {
						var menu = Ext.menu.MenuMgr.get('menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
                        		if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: 'Открытых окон нет',
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
							menu.add(new Ext.menu.Separator());
       						menu.add(new Ext.menu.Item(
								{
									text: 'Закрыть все окна',
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
				        						wnd.hide();
											}
										});
									}
								})
							);
						}
					},
					'mouseover': function() {
						var menu = Ext.menu.MenuMgr.get('menu_windows');
						menu.removeAll();
						var number = 1;
						Ext.WindowMgr.each(function(wnd){
							if ( wnd.isVisible() )
							{
        						if ( Ext.WindowMgr.getActive().id == wnd.id )
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'checked16',
											checked: true,
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
								else
								{
									menu.add(new Ext.menu.Item(
										{
											text: number + ". " + wnd.title,
											iconCls : 'x-btn-text',
											handler: function()
											{
												Ext.getCmp(wnd.id).toFront();
											}
										})
									);
									number++;
								}
							}
						});
						if ( menu.items.getCount() == 0 )
							menu.add({
								text: 'Открытых окон нет',
								iconCls : 'x-btn-text',
								handler: function()
								{
								}
							});
						else
						{
    						menu.add(new Ext.menu.Separator());
							menu.add(new Ext.menu.Item(
								{
									text: 'Закрыть все окна',
									iconCls : 'close16',
									handler: function()
									{
										Ext.WindowMgr.each(function(wnd){
											if ( wnd.isVisible() )
											{
				        						wnd.hide();
											}
										});
									}
								})
							);
						}
					}
				},
				menu: this.menu_windows,
				tabIndex: -1
			},
			{
				text:'Помощь',
				iconCls: 'help16',
				menu: this.menu_help,
				tabIndex: -1
			},
			{
				xtype : "tbfill"
			},
			{
				iconCls: 'progress16',
				text: '',
				hidden: true,
				id: 'progress_item',
				tabIndex: -1
			},
			{
				iconCls: 'user16',
				text: UserLogin,
				menu: this.user_menu,
				tabIndex: -1
			},
			'-',
			sw.Promed.Actions.PromedExit
		]
	});
	/*
	main_menu_panel = new sw.Promed.Toolbar({
		autoHeight: true,
		region: 'north',
		items: [
			{
				iconCls: 'progress16',
				text: '',
				hidden: true,
				id: 'progress_item',
				tabIndex: -1
			},
			new Ext.Action(
			{
				text: 'Выбор МО',
				tooltip: 'Выбор МО',
				iconCls: 'lpu-select16',
				handler: function()
				{
					Ext.WindowMgr.each(function(wnd){
						if ( wnd.isVisible() )
						{
							wnd.hide();
						}
					});
					getWnd('swSelectLpuWindow').show({});
				},
				hidden: !getGlobalOptions().superadmin && !( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ) // проверяем так же просмотр медперсонала
			}),
			{
				iconCls: 'user16',
				text: UserLogin,
				menu: this.user_menu,
				tabIndex: -1
			},
			new Ext.Action( {
				text:'Выход',
				iconCls: 'exit16',
				handler: function()
				{
					sw.swMsg.show({
						title: 'Подтвердите выход',
						msg: 'Вы действительно хотите выйти?',
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								window.onbeforeunload = null;
								window.location=C_LOGOUT;
							}
						}
					});
				}
			})
		]
	});
	*/
  
	main_promed_toolbar = main_menu_panel;


	// центральная панель
	main_center_panel = new Ext.Panel({
		id: 'main-center-panel',
		layout: 'fit',
        tbar: main_promed_toolbar,
		region: 'center',
		bodyStyle:'width:100%;height:100%;background:#aaa;padding:0;'
	});

	main_tabs_panel = new Ext.TabPanel({
		id: 'main-tabs-panel',
		bodyStyle:'background:#aaa;',
		resizeTabs: true,
		minTabWidth: 115,
		tabWidth: 135,
		enableTabScroll: true,
		defaults: {autoScroll: true},
		html: '<div></div>'
	});

	main_center_panel.add(main_tabs_panel);

	main_frame = new Ext.Viewport({
		layout:'border',
		items: [
			main_center_panel/*,
			left_panel
			new Ext.Panel({
				region: 'south',
				title: '_',
				height: 1,
				id: 'ajax_state'
			})*/
		]
	});

   main_frame.doLayout();
}


Ext.onReady(function (){
	if ( is_ready )
	{
		return;
	}
	
	// Запускалка
	sw.Promed.tasks = new Ext.util.TaskRunner();
	// Маска поверх всех окон
	var mask = Ext.getBody().mask();
	mask.setStyle('z-index', Ext.WindowMgr.zseed + 10000);
	// log(Ext.WindowMgr.zseed);
	// log(Ext.WindowMgr);
	sw.Promed.mask = new Ext.LoadMask(Ext.getBody(), {msg: LOAD_WAIT});
	sw.Promed.mask.hide();
	
	Ext.Ajax.timeout = 600000;
	
	// Значения по умолчанию
	loadPromed( function() {
		// Инициализация всплывыющих подсказок
		Ext.QuickTips.init();
		
		// http://172.19.61.24:85/issues/show/2264
		function unload_page(event) {
			// еще выше надо будет добавить признак изменения документов, который должен лежать в глобальной области
			/*if (event)
				event.returnValue = "Вы хотите завершить работу с системой или перезагрузить страницу.";
			else */
				return "Вы хотите завершить работу с системой или перезагрузить страницу."
		};
		if (Ext.isIE)
		{
			var root = window.addEventListener || window.attachEvent ? window : document.addEventListener ? document : null;
			if (typeof(root.onbeforeunload) != "undefined") root.onbeforeunload = unload_page;
		}
		else 
		{
			window.onbeforeunload = unload_page;
		}
		
		// собственно загрузка меню и прочего 
		// TODO: Надо оптимизировать и разделить функционал этой функции для более быстрой загрузки 
		loadModules();
		
	
		// Выбор МО в случае, если их несколько у человека
		Ext.Ajax.request({
			failure: function(response, options) {
				Ext.Msg.alert('Ошибка', 'Произошла ошибка при входе в систему, повторите попытку через некоторое время.');
			},
			success: function(resp, options) {
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj.length>1) { // || getGlobalOptions().superadmin // больше одной МО у человека
					var lpuarr = [];
					for (i=0; i < response_obj.length; i++) {
						if (response_obj[i]['lpu_id']!=undefined) {
							lpuarr.push(response_obj[i]['lpu_id']);
						}
					}
					//swSelectLpuWindow = new sw.Promed.swSelectLpuWindow();
					//main_frame.add(swSelectLpuWindow);
					//main_frame.doLayout();
					getWnd('swSelectLpuWindow').show( {params : lpuarr} );
				}
				else { // В случае, если МО одна, то прогружаем список медперсонала и отделения сразу
					/*
					loadLpuBuildingGlobalStore();
					loadLpuSectionGlobalStore();
					loadLpuSectionWardGlobalStore();
					loadLpuUnitGlobalStore();
					loadMedStaffFactGlobalStore();
					getCountNewDemand();
					*/
				}
			},
			url: C_USER_GETOWNLPU_LIST
		});
		
		if ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 )
			getWnd('swSelectLpuWindow').show({});
	});
});

