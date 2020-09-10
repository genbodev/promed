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
		/*LpuStructureViewAction: new Ext.Action(
		{
			text: MM_LPUSTRUC,
			tooltip: lang['struktura_mo'],
			iconCls : 'lpu-struc16',
			hidden: !isAdmin && !isLpuAdmin() &&  !isCadrUserView(),
			handler: function()
			{
				getWnd('swLpuStructureViewForm').show();
			}
		}),*/
		swLpuPassportAction: {
			text: lang['pasport_mo'],
			tooltip: lang['pasport_mo'],
			iconCls: 'lpu-passport16',
			handler: function()
			{
				getWnd('swLpuPassportEditWindow').show({
						action: (isUserGroup('PmuSpec'))?'edit':'view',
						Lpu_id: getGlobalOptions().lpu_id
				});
			},
			hidden: false
		},
		ReportStaffAction: new Ext.Action(
		{
			text: lang['otchet_shtatnoe_raspisanie'],
			tooltip: lang['otchet_shtatnoe_raspisanie'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'staff');
			},
			hidden: false
		}),
		ReportTarifAction: new Ext.Action(
		{
			text: lang['otchet_tarifikatsionnyie_spiski'],
			tooltip: lang['otchet_tarifikatsionnyie_spiski'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'tarif');
			},
			hidden: false
		}),
		/*ReportLabourAction: new Ext.Action(
		{
			text: lang['signalnaya_vedomost_protsent_nadbavki_za_staj'],
			tooltip: lang['signalnaya_vedomost_protsent_nadbavki_za_staj'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'labour');
			}
		}),
		ReportCategoryAction: new Ext.Action(
		{
			text: lang['signalnaya_vedomost_srok_deystviya_kategorii'],
			tooltip: lang['signalnaya_vedomost_srok_deystviya_kategorii'],
			iconCls: '',
			handler: function()
			{
				window.gwtBridge.runReport(getPromedUserInfo(), 'category');
			}
		}),
		UserProfileAction: new Ext.Action(
		{
			text: lang['profil_polzovatelya'],
			tooltip: lang['profil_polzovatelya'],
			iconCls : 'test16',
			hidden: (!isAdmin && IS_DEBUG == 1),
			handler: function()
			{
				getWnd('swUserProfileEditWindow').show();
			}
		}),*/
		PromedHelp: new Ext.Action(
		{
			text: lang['vyizov_spravki'],
			tooltip: lang['pomosch_po_programme'],
			iconCls : 'help16',
			handler: function()
			{
				ShowHelp(lang['soderjanie']);
			}
		}),
		PromedForum: new Ext.Action(
		{
			text: lang['forum_podderjki'],
			iconCls: 'support16',
			xtype: 'tbbutton',
			handler: function() {
				window.open(ForumLink);
			}
		}),
		PromedAbout: new Ext.Action(
		{
			text: lang['o_programme'],
			tooltip: lang['informatsiya_o_programme'],
			iconCls : 'promed16',
			handler: function()
			{
				getWnd('swAboutWindow').show();
			}
		}),
		PromedExit: new Ext.Action( {
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
		}),
		swOptionsViewAction: new Ext.Action(
		{
			text: lang['nastroyki'],
			tooltip: lang['prosmotr_i_redaktirovanie_nastroek'],
			iconCls : 'settings16',
			handler: function()
			{
				getWnd('swOptionsWindow').show();
			}
		}),
		swLpuSelectAction: new Ext.Action(
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
			hidden: isUserGroup('PmuSpec')
		}),
		ReportMIInMOViewAction: {
			text: lang['svedeniya_ob_ispolzovanii_ekspluatatsii_mi_v_mo'],
			tooltip: lang['svedeniya_ob_ispolzovanii_ekspluatatsii_mi_v_mo'],
			iconCls : 'reports16',
			hidden : isUserGroup('PmuSpec'),
			handler: function()
			{
				// Пример предварительной загрузки блока кода 
				if (sw.codeInfo.loadEngineReports)
				{
					sw.ParamFactory.getReportContent(0,getRegionNick() == 'ufa' ? 286 : 3947,function(err,data){
						if(err){
							Ext.Msg.alert(lang['oshibka'],err);
						} else {
							var win = new sw.reports.designer.ui.forms.ReportTester({
								reportId   : getRegionNick() == 'ufa' ? 286 : 3947,
								reportCaption: lang['svedeniya_ob_ispolzovanii_ekspluatatsii_mi_v_mo'],
								reportData : data,
								serverId   : 0
							});
							win.show();
						}
					})
				}
				else 
				{
                    Ext.Ajax.request({
                        success: function(response, options) {
                            getWnd('reports').load(
                                {
                                    callback: function(success)
                                    {
                                        sw.codeInfo.loadEngineReports = success;
										sw.ParamFactory.getReportContent(0,getRegionNick() == 'ufa' ? 286 : 3947,function(err,data){
											if(err){
												Ext.Msg.alert(lang['oshibka'],err);
											} else {
												var win = new sw.reports.designer.ui.forms.ReportTester({
													reportId   : getRegionNick() == 'ufa' ? 286 : 3947,
													reportCaption: lang['svedeniya_ob_ispolzovanii_ekspluatatsii_mi_v_mo'],
													reportData : data,
													serverId   : 0
												});
												win.show();
											}
										})
                                    }
                                });
                        },
                        url: '?c=User&m=getMSFList'
                    });
				}
			}
		},
		ReportStatViewAction: {
			text: lang['statisticheskaya_otchetnost'],
			tooltip: lang['statisticheskaya_otchetnost'],
			iconCls : 'reports16',
			hidden : false,
			handler: function()
			{
				// Пример предварительной загрузки блока кода
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEndUserWindow').show();
				}
				else
				{
                    Ext.Ajax.request({ //Если список АРМов для пользователя не подгрузился, то отображаются все отчеты, независимо от прав пользователя.
                                        // Поэтому сделал принудительный вызов метода перед открытием отчетов http://redmine.swan.perm.ru/issues/35151
                        success: function(response, options) {
                            getWnd('reports').load(
                                {
                                    callback: function(success)
                                    {
                                        sw.codeInfo.loadEngineReports = success;
                                        // здесь можно проверять только успешную загрузку
                                        getWnd('swReportEndUserWindow').show();
                                    }
                                });
                        },
                        url: '?c=User&m=getMSFList'
                    });

					/*getWnd('reports').load(
					{
						callback: function(success)
						{
							sw.codeInfo.loadEngineReports = success;
							// здесь можно проверять только успешную загрузку
							getWnd('swReportEndUserWindow').show();
						}
					});*/
				}
			}
		},
		swF14OMSPerAction: {
			text: lang['forma_f14_oms_pokazateli'],
			tooltip: lang['pokazateli_dlya_formyi_f14_oms'],
			iconCls: 'rep-f14oms-per16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
            hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swF14OMSAction: {
			text: lang['forma_f14_oms'],
			tooltip: lang['forma_f14_oms'],
			iconCls: 'rep-f14oms16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
            hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		},
		swF14OMSFinAction: {
			text: lang['forma_f14_oms_prilojenie_1'],
			tooltip: lang['forma_f14_oms_prilojenie_1'],
			iconCls: 'rep-f14oms-fin16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
            hidden: !(getGlobalOptions().superadmin && IS_DEBUG)
		}
	};

		
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

		this.menu_reports = new Ext.menu.Menu(
		{
			//plain: true,
			id: 'menu_reports',
			items:
			[
                sw.Promed.Actions.ReportStatViewAction
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
			sw.Promed.Actions.swLpuPassportAction,
			'-',
			sw.Promed.Actions.swLpuSelectAction,
			'-',
			{
				text: lang['otchetyi'],
				tooltip: lang['statisticheskaya_otchetnost'],
				iconCls : 'reports16',
				hidden : isUserGroup('PmuSpec'),
				menu: this.menu_reports
			},
			{
				text: lang['okna'],
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
	
		// Выбор МО в случае если их несколько у человека
		// Выбор МО в случае если их несколько у человека
		Ext.Ajax.request({
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['proizoshla_oshibka_pri_vhode_v_sistemu_povtorite_popyitku_cherez_nekotoroe_vremya']);
			},
			success: function(resp, options) {
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj.length>1) { // || getGlobalOptions().superadmin // больше одного МО у человека
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
				else { // В случае если МО одно, то прогружаем список медперсонала и отделения сразу
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

