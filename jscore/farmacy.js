/**
* Загрузчик модуля Аптеки 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Init
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Swan Coders
* @version      23.01.2010
*/

Ext.ns('sw.codeInfo');
Ext.ns('sw.notices');
sw.codeInfo = {};
sw.notices = [];
var taskbar = null;
var is_ready = false;
var swSelectFarmacyWindow = null;
var swUsersTreeViewWindow = null;
var isAdmin = (UserLogin=='admin');
var isFarmacyInterface = true;
var isTestLpu = (UserLogin=='testpol');

// Функция загрузки модуля аптеки 
function loadFarmacyModule()
{
	is_ready = true;

	// Акшены
	sw.Promed.Actions =
	{
		swOrgPassportAction: {
			text: lang['pasport_organizatsii'],
			tooltip: lang['pasport_organizatsii'],
			iconCls: 'lpu-passport16',
			handler: function()
			{
				getWnd('swOrgEditWindow').show({
						action: 'edit',
						mode: 'passport',
						Org_id: getGlobalOptions().org_id
				});
			},
			hidden: !isAdmin || !isDebug()
		},
		OrgStructureViewAction: {
			text: lang['struktura_organizatsii'],
			tooltip: lang['struktura_organizatsii'],
			iconCls : 'lpu-struc16',
			hidden: !isAdmin || !isDebug(),
			handler: function()
			{
				getWnd('swOrgStructureWindow').show();
			}
		},
		/*StorehouseOperatorWorkPlaceViewAction:  new Ext.Action({
			text: lang['mesto_rabotyi_operatora_rs'],
			tooltip: lang['mesto_rabotyi_operatora_rs'],
			iconCls : 'lpu-struc16',
			hidden: (getGlobalOptions().OrgFarmacy_id != 1), //временное решение, до тех пор пока не введены иные средства отличия оператора склада от оператора аптеки
			handler: function() {
				getWnd('swPharmacistWorkPlaceWindow').show({ARMType: 'storehouse'});
			}
		}),*/
		PharmacistWorkPlaceViewAction:  new Ext.Action({
			text: lang['mesto_rabotyi_aptekarya'],
			tooltip: lang['mesto_rabotyi_aptekarya'],
			iconCls : 'lpu-struc16',
			hidden: (!getGlobalOptions().superadmin)||(getGlobalOptions().OrgFarmacy_id == 1), //временное решение, до тех пор пока не введены иные средства отличия оператора склада от оператора аптеки
			handler: function() {
				getWnd('swPharmacistWorkPlaceWindow').show({ARMType: 'pharmacist'});
			}
		}),
		ReportStatViewAction: new Ext.Action(
		{
			text: lang['otchetnost'],
			tooltip: lang['otchetnost'],
			iconCls : 'reports16',
			hidden : !(getGlobalOptions().superadmin && IS_DEBUG),//sw.Promed.swReportViewWindow == null || sw.Promed.swReportViewWindow == undefined,
			handler: function()
			{
				//getWnd('swReportViewWindow').show();
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			}
		}),
		EventsWindowTestAction: new Ext.Action(
		{
			text: lang['test'],
			tooltip: lang['test'],
			iconCls : 'test16',
			hidden: !IS_DEBUG,
			handler: function()
			{
				getWnd('swTestEventsWindow').show();
			}
		}),
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
		swUsersTreeViewAction: new Ext.Action(
		{
			text: lang['polzovateli'],
			tooltip: lang['prosmotr_i_redaktirovanie_polzovateley'],
			iconCls : 'users16',
			hidden :  sw.Promed.swUsersTreeViewWindow == undefined,
			handler: function()
			{
				getWnd('swUsersTreeViewWindow').show();
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
		swGlobalOptionAction: new Ext.Action(
		{
			text: lang['obschie_nastroyki'],
			tooltip: lang['prosmotr_i_izmenenie_obschih_nastroek'],
			iconCls: 'settings-global16',
			handler: function()
			{
				getWnd('swGlobalOptionsWindow').show({ReadOnly: getGlobalOptions().superadmin ? 'false' : 'true'});
			},
			hidden: !getGlobalOptions().superadmin
		}),
		swFarmacySelectAction: new Ext.Action(
		{
			text: lang['vyibor_apteki'],
			tooltip: lang['vyibor_apteki'],
			iconCls: 'lpu-select16',
			handler: function()
			{
				Ext.WindowMgr.each(function(wnd){
					if ( wnd.isVisible() )
					{
						wnd.hide();
					}
				});
				swSelectFarmacyWindow.show({});
			},
			hidden: !isAdmin
		}),
		swMedicationSprAction: new Ext.Action(
		{
			text: lang['spravochnik_medikamentyi'],
			tooltip: lang['spravochnik_medikamentyi'],
			iconCls: 'farm-drugs16',
			handler: function()
			{
				sw.swMsg.alert(lang['vnimanie'],lang['dannyiy_funktsional_v_tekuschee_vremya_nedostupen_poskolku_nahoditsya_v_stadii_razrabotki']);
			},
			hidden: !(getGlobalOptions().superadmin && IS_DEBUG)//true
		}),
		swContractorsSprAction: new Ext.Action(
		{
			text: lang['spravochnik_kontragentyi'],
			tooltip: lang['spravochnik_kontragentyi'],
			iconCls: 'farm-partners16',
			handler: function()
			{
				getWnd('swContragentViewWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		swDokNakAction: new Ext.Action(
		{
			text: lang['prihodnyie_nakladnyie'],
			tooltip: lang['prihodnyie_nakladnyie'],
			iconCls: 'doc-nak16',
			handler: function()
			{
				getWnd('swDokNakViewWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		swDokUchAction: new Ext.Action(
		{
			text: lang['dokumentyi_ucheta_medikamentov'],
			tooltip: lang['dokumentyi_ucheta_medikamentov'],
			iconCls: 'doc-uch16',
			handler: function()
			{
				getWnd('swDokUcLpuViewWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		swAktSpisAction: new Ext.Action(
		{
			text: lang['aktyi_spisaniya_medikamentov'],
			tooltip: lang['aktyi_spisaniya_medikamentov'],
			iconCls: 'doc-spis16',
			handler: function()
			{
				getWnd('swDokSpisViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		swDokOstAction: new Ext.Action(
		{
			text: lang['dokumentyi_vvoda_ostatkov'],
			tooltip: lang['dokumentyi_vvoda_ostatkov'],
			iconCls: 'doc-ost16',
			handler: function()
			{
				getWnd('swDokOstViewWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		swInvVedAction: new Ext.Action(
		{
			text: lang['inventarizatsionnyie_vedomosti'],
			tooltip: lang['inventarizatsionnyie_vedomosti'],
			iconCls: 'farm-inv16',
			handler: function()
			{
				getWnd('swDokInvViewWindow').show();
				//sw.swMsg.alert('Внимание','Данный функционал в текущее время недоступен,<br/> поскольку находится в стадии разработки.');
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		swMedOstatAction: new Ext.Action(
		{
			text: lang['ostatki_medikamentov'],
			tooltip: lang['ostatki_medikamentov'],
			iconCls: 'farm-ostat16',
			handler: function()
			{
				getWnd('swMedOstatViewWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		EvnReceptProcessAction: new Ext.Action({
			text: lang['obrabotka_retseptov'],
			tooltip: lang['obrabotka_retseptov'],
			iconCls : 'receipt-process16',
			handler: function() {
				getWnd('swEvnReceptProcessWindow').show();
			},
			hidden: false
		}),
		EvnRPStreamInputAction: new Ext.Action({
			text: lang['obrabotka_retseptov'],
			tooltip: lang['obrabotka_retseptov'],
			iconCls : 'receipt-process16',
			handler: function() {
				getWnd('swEvnRPStreamInputWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		EvnReceptTrafficBookViewAction: new Ext.Action({
			text: lang['jurnal_dvijeniya_retseptov'],
			tooltip: lang['jurnal_dvijeniya_retseptov'],
			iconCls : 'receipt-delay16',
			handler: function() {
				getWnd('swEvnReceptTrafficBookViewWindow').show();
			}//,
			//hidden: (!getGlobalOptions().superadmin)
		}),
		EvnReceptInCorrectFindAction: {
			text: lang['jurnal_otsrochki'],
			tooltip: lang['jurnal_otsrochki'],
			iconCls : 'receipt-incorrect16',
			handler: function()
			{
				getWnd('swReceptInCorrectSearchWindow').show();
			}
		},
		OstAptekaViewAction: {
			text: MM_DLO_MEDAPT,
			tooltip: lang['rabota_s_ostatkami_medikamentov_po_aptekam'],
			iconCls : 'drug-farm16',
			handler: function()
			{
				getWnd('swDrugOstatByFarmacyViewWindow').show();
			}
		},
		OstDrugViewAction: {
			text: MM_DLO_MEDNAME,
			tooltip: lang['rabota_s_ostatkami_medikamentov_po_naimenovaniyu'],
			iconCls : 'drug-name16',
			handler: function()
			{
				getWnd('swDrugOstatViewWindow').show();
			}
		},
		swMedPersonalWorkPlaceAction: {
			text: lang['rabochee_mesto'],
			title: lang['arm'],
			tooltip: lang['rabochee_mesto_vracha'],
			iconCls: 'workplace-mp16',
			handler: function()
			{
				sw.Promed.MedStaffFactByUser.selectARM({
					ARMType: 'common',
					onSelect: null
				});
			},
			hidden: false//getGlobalOptions().medstafffact == undefined
		},
		swPrepBlockSprAction: {
			text: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
			tooltip: lang['spravochnik_falsifikatov_i_zabrakovannyih_seriy_ls'],
			handler: function()
			{
				getWnd('swPrepBlockViewWindow').show();
			}
		},
		swDrugDocumentSprAction: {
			text: 'Справочники системы учета медикаментов',
			tooltip: 'Справочники системы учета медикаментов',
			iconCls: '',
			handler: function()
			{
				getWnd('swDrugDocumentSprWindow').show();
			}
		}
	}
	
	this.menu_farmacy_service = new Ext.menu.Menu(
	{
		//plain: true,
		id: 'menu_farmacy_service',
		items:
		[
			sw.Promed.Actions.swOptionsViewAction,
			sw.Promed.Actions.swUsersTreeViewAction,
			sw.Promed.Actions.swGlobalOptionAction,
			//'-',
			sw.Promed.Actions.swFarmacySelectAction
		]
	});
	
	this.menu_farmacy_main = new Ext.menu.Menu(
	{
		id: 'menu_farmacy_main',
		items:(isSuperAdmin() || isDebug())?
			[
				sw.Promed.Actions.swMedPersonalWorkPlaceAction,
				'-',
				sw.Promed.Actions.OrgStructureViewAction,
				sw.Promed.Actions.swOrgPassportAction,
				//sw.Promed.Actions.StorehouseOperatorWorkPlaceViewAction,
				sw.Promed.Actions.PharmacistWorkPlaceViewAction,
				//sw.Promed.Actions.swMedicationSprAction,
				sw.Promed.Actions.swContractorsSprAction,
				'-',
				sw.Promed.Actions.swDokNakAction,
				sw.Promed.Actions.swDokUchAction,
				sw.Promed.Actions.swAktSpisAction,
				sw.Promed.Actions.swDokOstAction,
				sw.Promed.Actions.swInvVedAction,
				sw.Promed.Actions.swMedOstatAction,
				'-',
				//sw.Promed.Actions.EvnReceptProcessAction,
				sw.Promed.Actions.EvnRPStreamInputAction,
				sw.Promed.Actions.EvnReceptTrafficBookViewAction
			]
			:
			[
				sw.Promed.Actions.swMedPersonalWorkPlaceAction,
				'-',
				sw.Promed.Actions.OrgStructureViewAction,
				sw.Promed.Actions.swOrgPassportAction,
				//sw.Promed.Actions.StorehouseOperatorWorkPlaceViewAction,
				sw.Promed.Actions.PharmacistWorkPlaceViewAction
			]
	});

	this.menu_farmacy_reports = new Ext.menu.Menu(
	{
		//plain: true,
		id: 'menu_farmacy_reports',
		items: [
			sw.Promed.Actions.ReportStatViewAction
			//sw.Promed.Actions.EventsWindowTestAction
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
				text: lang['apteka'],
				iconCls: 'farmacy16',
				menu: this.menu_farmacy_main,
				tabIndex: -1,
				hidden: false
			},
			{
				text:lang['servis'],
				iconCls: 'service16',
				menu: this.menu_farmacy_service,
				tabIndex: -1
			},
			{
				text:lang['otchetyi'],
				iconCls: 'reports16',
				menu: this.menu_farmacy_reports,
				hidden : sw.Promed.swReportViewWindow == null || sw.Promed.swReportViewWindow == undefined,
				tabIndex: -1
			},
			{
				text: lang['okna'],
				iconCls: 'windows16',
				listeners: {
					'click': function() {
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
				id: 'menuFarmacyOtdel_Name',
				iconCls: 'farm-otdel16',
				text: lang['otdel'],
				tabIndex: -1,
				handler: function()
				{
					Ext.WindowMgr.each(function(wnd){
						if ( wnd.isVisible() )
						{
							wnd.hide();
						}
					});
					swSelectFarmacyWindow.show({});
				}
			},
				'-',
			{
				iconCls: 'user16',
				text: UserLogin,
				menu: this.user_menu,
				tabIndex: -1
			},
				'-',
			{
				text:lang['vyihod'],
				iconCls: 'exit16',
				handler: function()
				{
					sw.swMsg.show({
						title: lang['podtverdite_vyihod'],
						msg: lang['vyi_deystvitelno_hotite_vyiyti'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
								window.location=C_LOGOUT;
						}
					});
				},
				tabIndex: -1
			}
		]
	});

    if ( Ext.globalOptions.others.enable_barcodereader ) {
        log(lang['podklyuchaem_applet_dlya_skanera_shtrih-kodov']);
        sw.Applets.BarcodeScaner.initBarcodeScaner();
    }
    /*else
    {
        log(lang['podklyuchaem_applet_dlya_skanera_shtrih-kodov_test']);
        sw.Applets.BarcodeScaner.initBarcodeScaner();
    }*/

	// центральная панель
	main_center_panel = new Ext.Panel({
		region: 'center',
		bodyStyle:'width:100%;height:100%;background:#aaa;padding:0;'
	});
	main_frame = new Ext.Viewport({
		layout:'border',
		items: [
			main_menu_panel,
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

		// собственно загрузка модуля
		loadFarmacyModule();

		Ext.Ajax.request({
			failure: function(response, options) {
				Ext.Msg.alert(lang['oshibka'], lang['proizoshla_oshibka_pri_vhode_v_sistemu_povtorite_popyitku_cherez_nekotoroe_vremya']);
			},
			success: function(resp, options) {
				var response_obj = Ext.util.JSON.decode(resp.responseText);
				if (response_obj.length>1) { // || getGlobalOptions().superadmin // больше одного МО у человека
					var fararr = [];
					for (i=0; i < response_obj.length; i++) {
						if (response_obj[i]['lpu_id']!=undefined) {
							fararr.push(response_obj[i]['lpu_id']);
						}
					}
					//swalert(response_obj.length);
					getWnd('swSelectFarmacyWindow').show( {params : fararr} );
				} else {// В случае если МО одно, то прогружаем список медперсонала и отделения сразу
					loadLpuSectionGlobalStore();
					loadMedStaffFactGlobalStore();
					loadLpuBuildingGlobalStore();
				};
			},
			url: C_USER_GETOWNFARMACY_LIST
		});
	} );

});

