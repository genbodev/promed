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

Ext.ns('sw.codeInfo');
Ext.ns('sw.notices');
Ext.ns('sw.adminnotices');
sw.codeInfo = {};
sw.notices = [];
sw.adminnotices = [];
sw.firstadminnotice = 1;
	//var $isFarmacy = false;

var swLpuFilialGlobalStore = null;
var swLpuBuildingGlobalStore = null;
var swLpuSectionGlobalStore = null;
var swLpuSectionWardGlobalStore = null;
var swLpuUnitGlobalStore = null;
var swMedStaffFactGlobalStore = null;
var swProMedUserGlobalStore = null;
var swFederalKladrGlobalStore = null;

//uidesigner
/*	var phpSupport; // Flag is set in index.php to indicate whe support PHP
	var cookies = new Ext.state.CookieProvider();
	var options = cookies.get('Designer.Options') ||
	{
	dock : 1,
	compressed : 0,
	codepress : 1,
	autoresize : 1,
	floatheight : 480,
	floatwidth : 580,
	cmpfiles : "{0}ThirdParty.Components.json"
	};

	//Url based actions
	//var windowMode = Ext.ux.UTIL.getUrlAction('window',options.float)==1; //Change this flag to true if you want designer to be a window
	var docked = Ext.ux.UTIL.getUrlAction('docked',options.dock)==1;
	var nocache = Ext.ux.UTIL.getUrlAction('nocache',options.nocache)==1;
	var autoResize = Ext.ux.UTIL.getUrlAction('autoresize',options.autoresize)==1;
	var cmpfiles = (options.cmpfiles || "").replace('\r').split("\n");
	var designer; //Variable used to save the designer plugin
	
*/


var isTestLpu = UserLogin.inlist( ['testpol', 'permmsc1', 'permmsc9', 'permgkb2', 'permgkb7', 'permgdkb15','pr9trav1', 'pr9ter1', 'pr9gin1', 'pr9nevr1', 'pr9rodd1', 'Pech9s2', 'Pech9s4', 'permgdkb1', 'PermGDKB1_POR', 'PermGDKB1_BIL', 'PermGDKB1_BEV', 'PermGDKB1_LLV', 'PermGDKB1_AOV','pgp2test', 'msv', 'msv2' ] );

/** Функция, выполняемая перед загрузкой группы файлов 'promed'
 */
function initModules() {
	// эти меню используются для actions или для пунктов меню подгружаемых с сервера 
	if ( isFarmacyUser() ) {
		this.user_menu = new Ext.menu.Menu({
			id: 'user_menu',
			items:[{
				disabled: true,
				iconCls: 'user16',
				text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'Аптека : '+Ext.globalOptions.globals.OrgFarmacy_Nick,
				xtype: 'tbtext'
			}]
		});
	} else {
		this.user_menu = new Ext.menu.Menu({
			id: 'user_menu',
			items:[{
				disabled: true,
				iconCls: 'user16',
				text: '<b>Информация о пользователе</b><br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
				xtype: 'tbtext'
			}]
		});
	}
	this.menu_windows = new Ext.menu.Menu({
		id: 'menu_windows',
		items: ['-']
	});
	
}

// Функция загрузки модулей Промед
function loadModules()
{
	is_ready = true;
	
	/**
	* Запрос к серверу для получения списка доступных врачу служб
	* поскольку функция используется еще и на форме ЭМК, сделаю ее пока что глобальной (var не использовать!!!)
	*/
	openSelectServiceWindow = function(params) {
		if(!params) return false;
		Ext.Ajax.request({
			url: '/?c=MedService&m=defineMedServiceListOnMedPersonal',
			params: {MedPersonal_id: getGlobalOptions().medpersonal_id},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if(obj.MedPersonal_FIO) {
						getGlobalOptions().CurMedPersonal_FIO = obj.MedPersonal_FIO;
					}
					getGlobalOptions().medservices = obj.medservices;
					getWnd('swSelectMedServiceWindow').show({ARMType: params.ARMType, onSelect: params.onSelect });
				}
			}
		});
	};

	/* TODO: Этот функционал надо будет реализовать на правах пользователя
	if ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 ) {
		main_menu_panel = new sw.Promed.Toolbar({
			autoHeight: true,
			region: 'north',
			items: [
				sw.Promed.Actions.MedWorkersAction,
				{
					iconCls: 'progress16',
					text: '',
					hidden: true,
					id: '_menu_progress',
					tabIndex: -1
				},
				new Ext.Action(
				{
					text: lang['vyibor_mo'],
					tooltip: lang['vyibor_mo'],
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
					text:lang['vyihod'],
					iconCls: 'exit16',
					handler: function()
					{
						sw.swMsg.show({
							title: lang['podtverdite_vyihod'],
							msg: lang['vyi_deystvitelno_hotite_vyiyti'],
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
	}
	*/
    
	// Меню в виде "ленты". Включается опцией в настройках
	
	// Установка типа меню в зависимости от настройки пользователя
    if (Ext.globalOptions.appearance.menu_type == 'ribbon') {
		main_menu_ribbon = new sw.Promed.swTabToolbar(sw.Promed.menu);
        main_promed_toolbar = main_menu_ribbon;
    } else {
		// панель меню
		main_menu_panel = new sw.Promed.Toolbar({
			autoHeight: true,
			region: 'north',
			items: sw.Promed.menu
			/*[ TODO: Этот функционал надо весь проверить и листенерс окон перенести на акшены видимо 
				{
					text:lang['pasport_mo'],
					id: '_menu_lpu',
					iconCls: 'lpu16',
					menu: this.menu_passport_lpu,
					hidden: false, //!isAdmin && !isLpuAdmin(),
					tabIndex: -1
				},
				{
					text:lang['llo'],
					id: '_menu_dlo',
					iconCls: 'dlo16',
					menu: this.menu_dlo_main,
					tabIndex: -1
				},
				{
					text: lang['poliklinika'],
					id: '_menu_polka',
					iconCls: 'polyclinic16',
					menu: this.menu_polka_main,
					tabIndex: -1
				},
				{
					text: lang['statsionar'],
					id: '_menu_stac',
					iconCls: 'stac16',
					menu: this.menu_stac_main,
					tabIndex: -1,
					hidden: false
				},
				{
					text: lang['paraklinika'],
					id: '_menu_parka',
					iconCls: 'parka16',
					menu: this.menu_parka_main,
					tabIndex: -1,
					hidden: false
				},
				{
					text: lang['stomatologiya'],
					id: '_stomatka',
					iconCls: 'stomat16',
					menu: this.menu_stomat_main,
					tabIndex: -1,
					hidden: false
				},
				{
					text: lang['apteka'],
					id: '_menu_farmacy',
					iconCls: 'farmacy16',
					menu: this.menu_farmacy_main,
					tabIndex: -1,
					hidden: false
				},
				{
					text: lang['dokumentyi'],
					id: '_menu_documents',
					iconCls: 'documents16',
					menu: this.menu_documents,
					tabIndex: -1,
					hidden: false
				},
				{
					text:lang['servis'],
					id: '_menu_service',
					iconCls: 'service16',
					menu: this.menu_dlo_service,
					tabIndex: -1
				},
				{
					text:lang['otchetyi'],
					id: '_menu_reports',
					iconCls: 'reports16',
					menu: this.menu_reports,
					tabIndex: -1
				},
				{
					text: lang['okna'],
					id: '_menu_windows',
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
									text: lang['otkryityih_okon_net'],
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
										text: lang['zakryit_vse_okna'],
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
									text: lang['otkryityih_okon_net'],
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
										text: lang['zakryit_vse_okna'],
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
					text:lang['pomosch'],
					id: '_menu_help',
					iconCls: 'help16',
					menu: this.menu_help,
					tabIndex: -1
				},
				{
					id: '_menu_tbfill',
					xtype : "tbfill"
				},
				{
					iconCls: 'progress16',
					id: '_menu_progress',
					text: '',
					hidden: true,
					id: '_menu_progress',
					tabIndex: -1
				},
				{
					iconCls: 'user16',
					id: '_user_menu',
					text: UserLogin,
					menu: this.user_menu,
					tabIndex: -1
				},
				'-',
				sw.Promed.Actions.PromedExit
			]*/
		});
        main_promed_toolbar = main_menu_panel;
    }
	
    // End of main_menu_ribbon
	/*
	this.leftMenu = new Ext.Panel(
	{
		region: 'center',
		border: false,
		layout:'accordion',
		defaults:
		{
			bodyStyle: 'padding:5px'
		},
		layoutConfig:
		{
			titleCollapse: true,
			animate: true,
			activeOnTop: false
		},
		items:
		[{
			title: lang['skoraya_pomosch'],
			xtype: 'panel',
			border: false,
			items: [this.main_menu_ribbon],
			hidden: !isAdmin
		},
		{
			title: 'Panel 2',
			border: false,
			html: '<p>Panel content!</p>'
		},
		{
			title: 'Panel 3',
			border: false,
			html: '<p>Panel content!</p>'
		}]
	});
	
	var left_panel =
	{
		animCollapse: false,
		margins: '5 0 0 0',
		cmargins: '5 5 0 0',
		width: 200,
		minSize: 100,
		maxSize: 250,
		layout: 'border',
		id: 'left_panel',
		region: 'west',
		floatable: false,
        collapsible: true,
		listeners:
		{
			collapse: function()
			{
				main_center_panel.doLayout();
			},
			resize: function (p,nW, nH, oW, oH)
			{
				//main_center_panel.doLayout();
			}
		},
		border: true,
		title: lang['menyu'],
		split: true,
		items: [this.leftMenu]
	};
	*/
    

	// центральная панель
	main_center_panel = new Ext.Panel({
		id: 'main-center-panel',
		layout: 'fit',
        tbar: main_promed_toolbar,
		region: 'center',
		bodyStyle:'width:100%;height:100%;background:#aaa;padding:0;'
	});

	// проверяем настройку на сервере дя работы с Картридером
	
	/*$cardreader_is_enable = getGlobalOptions()['card_reader_is_enable'] ? getGlobalOptions()['card_reader_is_enable'] : false;
	if ( $cardreader_is_enable === true )
	{
		if (navigator.javaEnabled()) {
			var appl = Ext.getBody().createChild({
				name: 'apl',
				tag: 'applet',
				archive:'applets/swSmartCard_2ids.jar',
				code:'swan/SmartCard/swSmartCard',
				width: 0,
				height: 0,
				id: 'card_appl',
				style:'width:1px,height:1px'
			});
		} else {
			sw.swMsg.alert('Внимание', 'Отсутствует java машина. Работа с картами будет недоступна.<br/>Для установки java машины зайдите на сайт <a href=http://java.com/ru>http://java.com/ru</a>');
		}
	}*/
	/*
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
	*/
	
	main_messages_tpl = new Ext.XTemplate(
	 '<div onMouseOver="if(isMouseLeaveOrEnter(event, this)){this.style.display=&quot;block&quot;; Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(220);}" ',
		'onMouseOut="if(isMouseLeaveOrEnter(event, this)){this.style.display=&quot;block&quot;; Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(50);}" ',
		'onClick="getWnd(&quot;swMessagesViewWindow&quot;).show({mode: &quot;newMessages&quot;});" ',
		'style="background: silver; padding: 3px 0px 3px 5px; height: 48px; border: 1px solid gray; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -webkit-box-shadow: 1px 1px 1px #888888; box-shadow: 1px 1px 1px #888888;cursor:pointer;cursor:hand;">',
		'<div style="float: left; width: 48px; height: 48px;" class="mail48unread">',
		'<div style="float: left; font: bold 12px Tahoma; color:#444; margin-top:20px; opacity:0.8;filter: alpha(opacity=80); padding:1px; width:100%;text-align:center;">{count}</div>',
		'</div>',
		'<div style="margin-left: 56px;margin-top:6px;"><a style="font: normal 13px Tahoma; color: black;text-shadow: 1px 1px #cccccc;" href="#">У Вас <b>{count}</b> непрочитанн{okch} сообщен{ok}</a></div>',
		'</div>'
	);
	
	main_messages_panel = new Ext.Panel({
		id: 'main-messages-panel',
		hidden: true,
		style: 'position: absolute; z-index: 20000;',
		bodyStyle: 'background: none;',
		height: 56,
		mLeft: 50, // Сколько px панели видно слева =)
		mTop: 220, // Отступ сверху
		hideOver: function(shift) {
			this.setPosition(Ext.getBody().getWidth()-shift, this.mTop);
		},
		border: false,
		width: 225,
		html: '<div onMouseOver="Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(220);" '+
			'onMouseOut="Ext.getCmp(&quot;main-messages-panel&quot;).hideOver(50);" '+
			'onClick="getWnd(&quot;swMessagesViewWindow&quot;).show({mode: &quot;newMessages&quot;});" '+
			'style="background: silver; padding: 3px 0px 3px 5px; height: 48px; border: 1px solid gray; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; -webkit-box-shadow: 1px 1px 1px #888888; box-shadow: 1px 1px 1px #888888;">'+
			'<div style="float: left; width: 48px; height: 48px;" class="mail48">'+
			'<div style="float: left; font: bold 12px Tahoma; color:#444; margin-top:20px; opacity:0.8;filter: alpha(opacity=80); padding:1px; width:100%;text-align:center;"></div></div>'+
			'<div style="margin-left: 56px;margin-top:6px;"><a style="font: normal 13px Tahoma; color: black;text-shadow: 1px 1px #cccccc;" href="#">У Вас <b>нет</b> непрочитанных сообщений</a></div>'+
			'</div>'
	});
	main_center_panel.add(main_messages_panel);
	
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
		],
		listeners: {
			resize: function()
			{
				main_messages_panel.hideOver(main_messages_panel.mLeft);
			}
		}
	});

    if (Ext.globalOptions.appearance.menu_type == 'ribbon') {
        main_promed_toolbar.delegateUpdates();
    }

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
	//Ext.Element.setZIndex
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
		// Подменяем данные с локального хранилища при первом входе
		Ext.setLocalOptions();
		
		// Инициализируем различные переменные перед загрузкой группы файлов 
		initModules();
		
		getWnd('promed').load({callback: function() {
			// собственно загрузка меню и прочего 
			// TODO: Надо оптимизировать и разделить функционал этой функции для более быстрой загрузки 
			loadModules();
			// Глоссарий загружается после выбора драйвера для работы с локальным хранилищем, поскольку использует локальное хранилище
			//getWnd('glossary').load();
			
			// Старт тасков
			if (getNoticeOptions().is_popup_message) {
				this.taskTimer = function() {
					return {run: taskRun, interval: ((getGlobalOptions().message_time_limit)?getGlobalOptions().message_time_limit:5)*60*1000};
				}
				sw.Promed.tasks.start(this.taskTimer());
			}
			if(getNoticeOptions().is_extra_message) {
				this.extraTaskTimer = function() {
					return {run: extraTaskRun, interval: 300*1000};
				}
				sw.Promed.tasks.start(this.extraTaskTimer());
			}
			
			// Выбор МО в случае если их несколько у человека
			Ext.Ajax.request({
				failure: function(response, options) {
					Ext.Msg.alert(lang['oshibka'], lang['proizoshla_oshibka_pri_vhode_v_sistemu_povtorite_popyitku_cherez_nekotoroe_vremya']);
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
						loadLpuBuildingGlobalStore();
						loadLpuSectionGlobalStore();
						loadLpuSectionWardGlobalStore();
						loadLpuUnitGlobalStore();
						loadMedStaffFactGlobalStore();
						loadFederalKladrGlobalStore();
						getCountNewDemand();
					}
				},
				url: C_USER_GETOWNLPU_LIST
			});
			
			if ( String(getGlobalOptions().groups).indexOf('MedPersView', 0) >= 0 )
				getWnd('swSelectLpuWindow').show({});
			
			//main_messages_panel.setPosition(Ext.getBody().getWidth()-main_messages_panel.mLeft, main_messages_panel.mTop);
			
		}.createDelegate(this)});
	});
});

