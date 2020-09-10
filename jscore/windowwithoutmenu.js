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
Ext.ns('sw.adminnotices');
sw.codeInfo = {};
sw.notices = [];
sw.adminnotices = [];
sw.firstadminnotice = 1;
var taskbar = null;

var is_ready = false;
var swSelectFarmacyWindow = null;
var swUsersTreeViewWindow = null;
var isAdmin = (UserLogin=='admin');
var isFarmacyInterface = true;
var isTestLpu = (UserLogin=='testpol');

function loadWindowModule()
{
    is_ready = true;

    // Акшены
    sw.Promed.Actions =
    {
		swPrepBlockSprAction: {
			text: langs('Справочник фальсификатов и забракованных серий ЛС'),
			tooltip: langs('Справочник фальсификатов и забракованных серий ЛС'),
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
        },
        PersonDispSearchAction: {
            text: WND_POL_PERSDISPSEARCH,
            tooltip: langs('Поиск диспансерной карты пациента'),
            iconCls : 'disp-search16',
            handler: function()
            {
                getWnd('swPersonDispSearchWindow').show();
            },
            hidden: false//!(isAdmin || isTestLpu)
        },
        PersonDispViewAction: {
            text: WND_POL_PERSDISPSEARCHVIEW,
            tooltip: langs('Просмотр диспансерной карты пациента'),
            iconCls : 'disp-view16',
            handler: function()
            {
                getWnd('swPersonDispViewWindow').show({mode: 'view'});
            },
            hidden: false//!(isAdmin || isTestLpu)
        },
        EvnPLDispScreenSearchAction: {
            text: MM_POL_EPLDSSEARCH,
            tooltip: MM_POL_EPLDSSEARCH,
            iconCls : 'dopdisp-epl-search16',
            handler: function()
            {
                getWnd('swEvnPLDispScreenSearchWindow').show();
            },
            hidden: false //!isAdmin
        },
		EvnDirectionMorfoHistologicViewAction: {
			text: 'Направления на патоморфогистологическое исследование',
			tooltip: 'Журнал направлений на патоморфогистологическое исследование',
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnDirectionMorfoHistologicViewWindow').show();
			},
			hidden: false
		},
		EvnMorfoHistologicProtoViewAction: {
			text: 'Протоколы патоморфогистологических исследований',
			tooltip: 'Журнал протоколов патоморфогистологических исследований',
			iconCls : 'pathomorph16',
			handler: function() {
				getWnd('swEvnMorfoHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		DirectionsForCytologicalDiagnosticExaminationViewAction: {
			text: langs('Направления на цитологическое диагностическое исследование'),
			tooltip: langs('Направления на цитологическое диагностическое исследование'),
			iconCls : 'cytologica16',
			handler: function() {
				getWnd('swEvnDirectionCytologicViewWindows').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
			},
			hidden: (getRegionNick() == 'kz')
		},
		CytologicalDiagnosticTestProtocolsViewAction: {
			text: langs('Протоколы цитологических диагностических исследований'),
			tooltip: langs('Протоколы цитологических диагностических исследований'),
			iconCls : 'cytologica16',
			handler: function() {
				getWnd('swEvnCytologicProtoViewWindow').show({curentMedStaffFactByUser: sw.Promed.MedStaffFactByUser.current});
			},
			hidden: (getRegionNick() == 'kz')
		},
		EvnHistologicProtoViewAction: {
			text: 'Протоколы патологогистологических исследований',
			tooltip: 'Журнал протоколов патологогистологических исследований',
			iconCls : 'pathohistproto16',
			handler: function() {
				getWnd('swEvnHistologicProtoViewWindow').show();
			},
			hidden: false
		},
		EvnDirectionHistologicViewAction: {
			text: 'Направления на патологогистологическое исследование',
			tooltip: 'Журнал направлений на патологогистологическое исследование',
			iconCls : 'pathohist16',
			handler: function() {
				getWnd('swEvnDirectionHistologicViewWindow').show();
			},
			hidden: false
		},
        EvnPLDispScreenChildSearchAction: {
            text: MM_POL_EPLDSCSEARCH,
            tooltip: MM_POL_EPLDSCSEARCH,
            iconCls : 'dopdisp-epl-search16',
            handler: function()
            {
                getWnd('swEvnPLDispScreenChildSearchWindow').show();
            },
            hidden: false //!isAdmin
        },
		swMESOldAction: {
			text: getMESAlias(),
			tooltip: langs('Справочник') + getMESAlias(),
			iconCls: 'spr-mes16',
			handler: function()
			{
				getWnd('swMesOldSearchWindow').show();
			},
			hidden: getRegionNick().inlist(['by']) // TODO: После тестирования доступ должен быть для всех
		},
		SprRlsAction: {
			text: getRLSTitle(),
			tooltip: getRLSTitle(),
			iconCls: 'rls16',
			handler: function()
			{
				getWnd('swRlsViewForm').show();
			},
			hidden: false
		},
		VideoChatBtn: {
				iconCls: 'VideoChatWindowIcon',
				hidden: true,
				style: 'background-color: #1976d2;',
				tooltip: 'Видеочат',
				handler: function() {
					getWnd('swVideoChatWindow').show();
				}
		}
    }
    this.user_menu = new Ext.menu.Menu(
        {
            //plain: true,
            id: 'user_menu',
            items:
                [
                    {
                        disabled: true,
                        iconCls: 'user16',
                        text: '<br/>'+'Имя : '+UserName+'<br/>'+'E-mail : '+UserEmail+'<br/>'+'Описание : '+UserDescr+'<br/>'+'МО : '+Ext.globalOptions.globals.lpu_nick,
                        xtype: 'tbtext'
                    },
					{
						text: langs('Помощь'),
						tooltip: langs('Помощь по программе'),
						iconCls : 'help',
						handler: function()
						{
							ShowHelp(langs('Содержание'));
						}
					}
                ]
        });
    // панель меню
    main_menu_panel = new sw.Promed.Toolbar({
        autoHeight: true,
		hidden: (!Ext.isEmpty(getGlobalOptions().showTop) && getGlobalOptions().showTop==1),
        region: 'north',
        items:
            [
                {
                    text: '<p style="font-size: 15px;">Сменить рабочее место</p>',
                    title: langs('АРМ'),
                    tooltip: langs('Сменить рабочее место'),
                    iconCls: 'workplace-mp16',
					id: 'change_workplace',
                    handler: function()
                    {
						sw.Promed.MedStaffFactByUser._showMenu(this.id);
					},
                    hidden: ((getGlobalOptions().medstafffact == undefined) && (getGlobalOptions().lpu_id>0))
                },
				{
					xtype : 'tbfill'
				},
				{
					id: 'CmkLpuBuilding_label',
					xtype: 'label',
					text: 'Оперативный отдел МО',
					style: 'margin-right: 10px',
					hidden: true
				},
				{
					id: 'CmkLpuBuilding_combo',
					name: 'LpuBuilding_id',
					baseParams: { SmpUnitType_Code: 4, form: 'cmk' },
					xtype: 'swsmpunitscombo',
					tpl:new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{values.LpuBuilding_Name}&nbsp{[(!Ext.isEmpty(values.Lpu_Nick)) ? "(" + values.Lpu_Nick +")" : ""]}',
						'</div></tpl>'
					),
					LpuBuildingType_id: 27,
					autoLoad: true,
					width: 200,
					listWidth: 300,
					hidden: true,
					store: new Ext.data.JsonStore({
						autoLoad: true,
						baseParams: this.baseParams,
						fields: [
							{name: 'LpuBuilding_id', type: 'int'},
							{name: 'LpuBuilding_Code', type: 'int'},
							{name: 'LpuBuilding_Name', type: 'string'},
							{name: 'Lpu_id', type: 'int'},
							{name: 'Lpu_Nick', type: 'string'}
						],
						key: 'LpuBuilding_id',
						sortInfo: {
							field: 'LpuBuilding_Name'
						},
						url: '/?c=CmpCallCard&m=loadSmpUnits',
						listeners: {
							beforeload: function() {
								if (Ext.isEmpty(getGlobalOptions().lpu_id)) {
									return false;
								}
							}
						}
					}),
					listeners: {
						change: function(combo,newValue) {
							if( !newValue ) return;

							Ext.Ajax.request({
								url: '/?c=Options&m=saveLpuBuildingForTimingCmk',
								params: { LpuBuilding_id: newValue }
							})


							Ext.Ajax.request({
								url: '/?c=LpuStructure&m=getLpuBuildingData',
								params: { LpuBuilding_id: newValue },
								callback: function(options, success, response) {
									var win = swWorkPlaceCenterDisasterMedicineWindow;
									if( !success || !win ) return;

									var result = Ext.util.JSON.decode(response.responseText);

									if ( !result && !result[0]) return;

									for( prop in win.SmpTiming ) {
										win.SmpTiming[prop] = parseInt(result[0][prop]);
									}
								}
							})
						}
					}
				},
				new Ext.Action( {
					width: 100,
					text:langs('Управление МО'),
					hidden: true,
					id: 'select_mo_win',
					iconCls: 'settings16',
					handler: function()
					{
						var callback = Ext.emptyFn;
						if(getRegionNick() == 'ufa') {
							lpuBuildingCombo = Ext.getCmp('CmkLpuBuilding_combo');
							if(!lpuBuildingCombo.hidden)
								callback = function() {
									lpuBuildingCombo.getStore().reload()
								};
						}
						getWnd('swSelectMOToControlWindow').show({callback: callback});
					}
				}),
                {
                    //iconCls: 'user16',
                    id: '_user_menu',
                    text: '<b>'+UserName + '</b>',
                    menu: this.user_menu,
                    tabIndex: -1,
                    hidden: false
                },
				sw.Promed.Actions.VideoChatBtn,
                new Ext.Action( {
					width: 100,
                    text:langs('Выход'),
                    iconCls: 'exit16',
                    handler: function()
                    {
                        sw.swMsg.show({
                            title: langs('Подтвердите выход'),
                            msg: langs('Вы действительно хотите выйти?'),
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

    log(langs('Подключаем плагин КриптоПро'));
    sw.Applets.CryptoPro.initCryptoPro();

    if(Ext.globalOptions.others.enable_uecreader) {
        log(langs('Подключаем апплет УЭК'));
        sw.Applets.uec.initUec();
    }
    if(Ext.globalOptions.others.enable_bdzreader) {
        log(langs('Подключаем апплет BDZ'));
        sw.Applets.bdz.initBdz();
    }

    if ( Ext.globalOptions.others.enable_barcodereader ) {
        log(langs('Подключаем апплет для сканера штрих-кодов'));
        sw.Applets.BarcodeScaner.initBarcodeScaner();
    }

	main_taskbar_panel = new Ext.Panel({
		id: 'ux-taskbar',
		layout: 'fit',
		region: 'south',
		hidden: true,
		autoWidth: true,
		html: '<div id="ux-taskbuttons-panel"></div><div class="x-clear"></div>'
	});

	//if(Ext.isEmpty(getGlobalOptions().showTop) && getGlobalOptions().showTop == 1)
	main_top_panel = new Ext.Panel({
		id: 'main_top_panel',
		layout: 'fit',
		tbar: main_menu_panel,
		items: [
			main_taskbar_panel
		],
		region: 'center'
	});

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
    // центральная панель
    main_center_panel = new Ext.Panel({
		id: 'main-center-panel',
        region: 'center',
        bodyStyle:'width:100%;height:100%;background:#16334a;padding:0;',
		tbar: main_top_panel
    });
	main_center_panel.add(main_messages_panel);
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
        ],
		listeners:
		{
			resize: function(){
				main_messages_panel.hideOver(main_messages_panel.mLeft);
			}
		}
    });
    if (getAppearanceOptions().taskbar_enabled) {
	    taskbar = new Ext.ux.TaskBar();
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

		function unload_page(event) {
			sw.Applets.BarcodeScaner.stopBarcodeScaner();
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

        // собственно загрузка модуля
        loadWindowModule();
        setPromedInfo(' / '+getGlobalOptions().region.name,'promed-region');

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
	
		var globals_lpu = getGlobalOptions().lpu;
		var index = globals_lpu.indexOf('');
		if(index > -1)
			globals_lpu.splice(index, 1);
		if ( globals_lpu ) {
			if ( globals_lpu.length>1 ) { // Выбор МО в случае, если их несколько у пользователя
				getWnd('swSelectLpuWindow').show( {params : globals_lpu} );
			} else {
				if ( globals_lpu.length==1 ) {
					// Если у пользователя только 1 МО, то загрузким данные по этой МО
					loadGlobalStores({
						callback: function () {
							// Открытие АРМа по умолчанию
							if (getGlobalOptions().se_techinfo) {
								openWindowsByTechInfo();
							} else {
								sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
							}
						}
					});
					getCountNewDemand();
				}
                else
                {
					initGlobalStores();
					if (isUserGroup('OuzSpec') || isUserGroup('Communic')) {
						sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
					}
                }
			}
		} else {
			initGlobalStores();
			// У пользователя нет ни одной МО, загружать нечего не нужно :) 
			// Открытие АРМа по умолчанию для пользователя организации
			if (getGlobalOptions().se_techinfo) {
				openWindowsByTechInfo();
			} else {
				sw.Promed.MedStaffFactByUser.openDefaultWorkPlace();
			}
		}

		if (getRegionNick() == 'ufa') {
			//Инициализация видеосвязи
			Ext6.require('videoChat.lib.Engine');
		}
    } );

});

