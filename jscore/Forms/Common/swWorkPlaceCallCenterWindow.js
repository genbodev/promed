/**
* АРМ оператора call-центра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/


sw.Promed.swWorkPlaceCallCenterWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
    //объект с параметрами АРМа, с которыми была открыта форма
    userMedStaffFact: null,
	useUecReader: true,
	buttons: [
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['podojdite'] });
		}

		return this.loadMask;
	},
	id: 'swWorkPlaceCallCenterWindow',
	openDirectionMasterWindow: function() {
		var win = this;
		var grid = this.MainViewFrame.getGrid();
		var record = grid.getSelectionModel().getSelected();
		var params = new Object({
			userMedStaffFact: this.userMedStaffFact,
			onDirection: function(data) {
				if (record && record.get('Person_id') ) {
					win.bj.doSearch({Person_id: record.get('Person_id')});
				}
			}
		});
		var personData = new Object();

		if ( grid.getSelectionModel().getSelected() && !Ext.isEmpty(grid.getSelectionModel().getSelected().get('Person_id')) ) {
			if (record.get('Person_IsDead') == "true") {
				params.isDead = true;
			}
			personData.Person_Firname = record.get('Person_Firname');
			personData.Person_id = record.get('Person_id');
			personData.PersonEvn_id = record.get('PersonEvn_id');
			personData.Server_id = record.get('Server_id');
			personData.Person_IsDead = record.get('Person_IsDead');
			personData.Person_Secname = record.get('Person_Secname');
			personData.Person_Surname = record.get('Person_Surname');
			personData.AttachLpu_Name = record.get('AttachLpu_Name');
			personData.Person_Birthday = record.get('Person_Birthday');
			params.personData = personData;
		}

		getWnd('swDirectionMasterWindow').show(params);
	},
	openNewslatterAcceptEditWindow: function(NewslatterAccept_id, Person_id) {
	
		if (!isUserGroup('Newslatter')) {
			return false;
		}
		
		var win = this;
	    var grid = this.MainViewFrame.getGrid();
		var params = {};
		params.NewslatterAccept_id = NewslatterAccept_id;
		params.Person_id = Person_id;
		params.action = Ext.isEmpty(NewslatterAccept_id) ? 'add' : 'edit';
		params.callback = function(options, success, response) {
		    grid.getStore().reload();
			if (success == true && response) {
				win.askPrintNewslatterAccept(response);
			}
		}

        getWnd('swNewslatterAcceptEditForm').show(params);
    },
    askPrintNewslatterAccept: function(params) {
	
		if (!params || !params.NewslatterAccept_id) {
			return false;
		}
		
		var win = this;
		
		if (Ext.isEmpty(params.NewslatterAccept_endDate)) {	
			
			sw.swMsg.show({
				title: 'Вопрос',
				msg: 'Распечатать документ?',
				icon: Ext.MessageBox.QUESTION,
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					}
				}
			});	
		} else {
			
			sw.swMsg.show({
				title: 'Вопрос',
				msg: 'Распечатать документ?',
				icon: Ext.MessageBox.QUESTION,
				buttons: {
					yes: 'Печать Согласия',
					no: 'Печать Отказа',
					cancel: 'Отмена'
				},
				fn: function( buttonId ) {				
					if ( buttonId == 'yes') {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					} else if ( buttonId == 'no') {
						win.printNewslatterAccept('printDenial', params.NewslatterAccept_id);
					}
				}
			});
		}		
    },
    printNewslatterAccept: function(method, NewslatterAccept_id) { 
		
		if (!method || !NewslatterAccept_id) {
			return false;
		}
	
		window.open('/?c=NewslatterAccept&m=' + method + '&NewslatterAccept_id=' + NewslatterAccept_id, '_blank');
	},
	show: function() {
		sw.Promed.swWorkPlaceCallCenterWindow.superclass.show.apply(this, arguments);
        this.userMedStaffFact = arguments[0];
        this.FilterPanel.fieldSet.expand();
		var wnd = this;

		loadComboOnce(this.FilterPanel.getForm().findField('AttachLpu_id'), lang['lpu']);
		// Закрыл, ибо https://redmine.swan.perm.ru/issues/30520
		/*
		if ( getGlobalOptions().lpu_id > 0 ) {
			this.FilterPanel.getForm().findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		}
		*/
		this.MainViewFrame.setParam('limit', 50);
		this.MainViewFrame.setParam('start', 0);
		//this.hist = false;
		this.MainViewFrame.getGrid().getStore().removeAll();

		this.bj.RecordMenu.items.items[1].disable();

		if ( !this.bj.mainGrid.getAction('show_history') ) {
			this.bj.mainGrid.addActions({
					name: 'show_history',
					iconCls: 'journal16',
					disabled:true,
					hidden:!(isAdmin||isCallCenterAdmin()),
					//disabled:(!isAdmin),
					tooltip: lang['pokazat_skryit_istoriyu'],
					text: lang['pokazat_istoriyu'],
					handler: function() {

						var selected_record = wnd.MainViewFrame.getGrid().getSelectionModel().getSelected();
						if (!selected_record){
							return false;
						}

						var person_data = wnd.MainViewFrame.getParamsIfHasPersonData();

						if(Ext.isEmpty(wnd.bj.loadAddFields)){
							wnd.bj.mainGrid.setActionText('show_history', lang['skryit_istoriyu']);
							wnd.bj.loadAddFields = 1;
							wnd.bj.mainGrid.setColumnHidden('pmUser_Name',false);
							wnd.bj.mainGrid.setColumnHidden('EvnDirection_insDT',false);
							wnd.bj.doSearch({Person_id: selected_record.get('Person_id'), person_data:person_data});
						}else{
							//wnd.bj.hist = 1;
							wnd.bj.mainGrid.setActionText('show_history', lang['pokazat_istoriyu']);
							wnd.bj.loadAddFields = null;
							wnd.bj.mainGrid.setColumnHidden('pmUser_Name',true);
							wnd.bj.mainGrid.setColumnHidden('EvnDirection_insDT',true);
							wnd.bj.doSearch({Person_id: selected_record.get('Person_id'), person_data:person_data});
						}
					}
			});
		}
        if(getRegionNick() == 'perm' || getRegionNick() == 'ufa'){
            Ext.getCmp(wnd.id + 'CallCenterWorkPlacePanel').getGrid().getColumnModel().setColumnHeader(18,lang['tip_osnovnogo_uchastka']);
            Ext.getCmp(wnd.id + 'CallCenterWorkPlacePanel').getGrid().getColumnModel().setColumnHeader(19,lang['osnovnoy_uchastok']);
        }
        else
        {
            Ext.getCmp(wnd.id + 'CallCenterWorkPlacePanel').getGrid().getColumnModel().setColumnHeader(18,lang['tip_uchastka']);
            Ext.getCmp(wnd.id + 'CallCenterWorkPlacePanel').getGrid().getColumnModel().setColumnHeader(19,lang['uchastok']);
        }
		this.doReset();
	},
	showHist:function(show){
		if(show){
			this.hist = true;
			this.TtgViewFrame.setColumnHidden('pmUser_Name',false);
			this.TtgViewFrame.setColumnHidden('EvnDirection_insDT',false);
		}else{
			this.hist = false;
			this.TtgViewFrame.setColumnHidden('pmUser_Name',false);
			this.TtgViewFrame.setColumnHidden('EvnDirection_insDT',true);	
		}
	},
	/**
	 * Очищает поля фильтра и гриды
	 * Перекрывает родительский метод
	 */
	doReset: function()
	{
		sw.Promed.swWorkPlaceCallCenterWindow.superclass.doReset.apply(this, arguments); // выполняем базовый метод
		this.MainViewFrame.removeAll({clearAll:true});
		this.MainViewFrame.onRowSelect(null, null, null);
		this.bj.mainGrid.removeAll();
	},
	doSearch: function(mode){
		
		/*
		var w = Ext.WindowMgr.getActive();
		// Не выполняем если открыто модальное окно. Иначе при обновлении списка,
		// выделение с текущего элемента снимается и устанавливается на первом элементе
		// в списке. В свою очередь все рабочие места получают не верные данные из
		// выделенного объекта, вместо ранее выделенного пользователем.
		// @todo Проверка неудачная. Необходимо найти другое решение.
		
		// Текущее активное окно является модальным?
		if ( w.modal ) {
			return;
		}
		*/

		if ( this.FilterPanel.isEmpty() ) {
			/*sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			*/
			this.doReset(); // ничего не задали - ничего не нашли
			return false;
		}
		
		var params = Ext.apply(this.FilterPanel.getForm().getValues(), this.searchParams || {});
		var btn = this.getPeriodToggle(mode);
		if (btn) {
			if (mode != 'range') {
				if (this.mode == mode) {
					btn.toggle(true);
					if (mode != 'day') // чтобы при повторном открытии тоже происходила загрузка списка записанных на этот день
						return false;
				} else {
					this.mode = mode;
				}
			}
			else {
				btn.toggle(true);
				this.mode = mode;
			}
		}
		
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 50;
		params.start = 0;
		params.dontShowUnknowns = 1;// #158923 не показывать неизвестных
		this.MainViewFrame.removeAll({clearAll:true});
		this.MainViewFrame.loadData({globalFilters: params});
	},
    scheduleDelete:function() {
        var form = this;
        var grid = this.TtgViewFrame.getGrid();
        if (!grid)
        {
            Ext.Msg.alert(lang['oshibka'], lang['spisok_zapisey_patsienta_ne_nayden']);
            return false;
        }
        var rec = grid.getSelectionModel().getSelected();
        if ( !rec || !rec.get('Item_id') )
        {
            Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
            return false;
        }
        var data = rec.get('Item_id').split('_');
        var id = data[1];
        if(data[0] == 'TimetableGraf') {
            sw.swMsg.show({
                icon: Ext.MessageBox.QUESTION,
                msg: lang['vyi_hotite_osvobodit_vremya_priema'],
                title: lang['vopros'],
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId, text, obj)
                {
                    if ('yes' == buttonId)
                    {
                        submitClearTime(
                            {
                                id: id,
                                type: 'polka',
                                DirFailType_id: null,
                                EvnComment_Comment: null
                            },
                            function(response) {
                                if (response.responseText)
                                {
                                    var answer = Ext.util.JSON.decode(response.responseText);
                                    if (!answer.success)
                                    {
                                        if (answer.Error_Code)
                                        {
                                            Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
                                        }
                                        else
                                        if (!answer.Error_Msg)
                                        {
                                            Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_osvobojdenie_priema_nevozmojno']);
                                        }
                                    }
                                    else
                                    {
                                        grid.getStore().reload();
                                    }
                                }
                                else
                                {
                                    Ext.Msg.alert(lang['oshibka'], lang['pri_vyipolnenii_operatsii_osvobojdeniya_vremeni_priemaproizoshla_oshibka_otsutstvuet_otvet_servera']);
                                }
                            }.createDelegate(this),
                            null
                        );
                    }
                    else
                    {
                        if (grid.getStore().getCount()>0)
                        {
                            grid.getView().focusRow(0);
                        }
                    }
                }
            });
        }
		if(data[0] == 'EvnQueue') {
			// "Убрать из очереди" с показом формы "Причина отмены направления из очереди"
			getWnd('swMPQueueSelectFailWindow').show({
				onSelectValue: function(responseData) {
					var evnqueue_id = id;

					if (evnqueue_id && responseData.val != 7) {
						Ext.Ajax.request({
							url: '/?c=Queue&m=cancelQueueRecord',
							callback: function(options, success, response)  {
								if (success) {
									getWnd('swMPQueueSelectFailWindow').hide();
									form.TtgViewFrame.ViewActions.action_refresh.execute();
								} else
									sw.swMsg.alert(lang['oshibka'], lang['pri_otmene_napravleniya_iz_ocheredi_proizoshla_oshibka']);
							},
							params: {
								EvnQueue_id: evnqueue_id,
								EvnComment_Comment: responseData.comment,
								QueueFailCause_id: responseData.val
							}
						});
					}

					if (responseData.val == 7) {
						getWnd('swMPQueueSelectFailWindow').hide();
						form.recordPerson(evnqueue_id, false);
					}
				}
			});
		}
        if(data[0] == 'TimetableMedService') {
            // Удаление услуги
            var evnqueue_id = id;
            sw.swMsg.show({
                icon: Ext.MessageBox.QUESTION,
                msg: lang['vyi_hotite_osvobodit_vremya_priema'],
                title: lang['vopros'],
                buttons: Ext.Msg.YESNO,
                fn: function(buttonId, text, obj)
                {
                    if ('yes' == buttonId)
                    {
                        Ext.Ajax.request({
                            url: '/?c=TimetableMedService&m=Clear',
                            callback: function(options, success, response)  {
                                if (success) {
                                    form.TtgViewFrame.ViewActions.action_refresh.execute();
                                } else
                                    Ext.Msg.alert(lang['oshibka'], lang['pri_otmene_napravleniya_iz_ocheredi_proizoshla_oshibka']);
                            },
                            params: {TimetableMedService_id: evnqueue_id, LpuUnitType_SysNick: 'medservice'}
                        });
                    }
                }
            });
        }
		if(data[0] == 'TimetableStac') {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyi_hotite_osvobodit_zapis_na_koyku'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj)
				{
					if ('yes' == buttonId)
					{
						submitClearTime(
							{
								id: id,
								type: 'stac',
								DirFailType_id: null,
								EvnComment_Comment: null
							},
							function(response) {
								if (response.responseText)
								{
									var answer = Ext.util.JSON.decode(response.responseText);
									if (!answer.success)
									{
										if (answer.Error_Code)
										{
											Ext.Msg.alert(lang['oshibka_#']+answer.Error_Code, answer.Error_Message);
										}
										else
										if (!answer.Error_Msg)
										{
											Ext.Msg.alert(lang['oshibka'], lang['pri_otmene_zapisi_na_koyku_proizoshla_oshibka_osvobojdenie_zapisi_nevozmojno']);
										}
									}
									else
									{
										grid.getStore().reload();
									}
								}
								else
								{
									Ext.Msg.alert(lang['oshibka'], lang['pri_otmene_zapisi_na_koyku_proizoshla_oshibka_otsutstvuet_otvet_servera']);
								}
							}.createDelegate(this),
							null
						);
					}
					else
					{
						if (grid.getStore().getCount()>0)
						{
							grid.getView().focusRow(0);
						}
					}
				}
			});
		}
    },
	initComponent: function() {
		var curWnd = this;

		this.gridPanelAutoLoad = false;
		this.showToolbar = false;
		
		this.buttonPanelActions = {
			action_RecordPerson: {
				handler: function() {
					curWnd.openDirectionMasterWindow();
				},
				iconCls : 'record-new32',
				nn: 'action_RecordPerson',
				text: lang['zapis_k_vrachu'],
				tooltip: lang['zapis_k_vrachu']
			},
			action_EditSchedule: {
				handler: function() {
					getWnd('swScheduleEditMasterWindow').show({type:'call'});
				},
				iconCls: 'schedule32',
				nn: 'action_EditSchedule',
				text: lang['vedenie_raspisaniya'],
				hidden: !isCallCenterAdmin(),
				tooltip: lang['vedenie_raspisaniya']
			},
			action_HomeVisit: {
				handler: function() {
					getWnd('swHomeVisitListWindow').show();
				},
				iconCls : 'mp-region32',
				nn: 'action_HomeVisit',
				text: lang['vyizovyi_na_dom'],
				tooltip: lang['jurnal_vyizovov_na_dom']
			},
			action_ProfileQueue: {
				handler: function() {
					getWnd('swMPQueueWindow').show({
						ARMType: 'callcenter',
						mode: 'view',
						userMedStaffFact: this.userMedStaffFact,
						onSelect: function(data) { // на тот случай если из режима просмотра очереди будет сделана запись
							getWnd('swMPQueueWindow').hide();
							getWnd('swMPRecordWindow').hide();
							// Ext.getCmp('swMPWorkPlaceWindow').scheduleSave(data);
						}
					});
				}.createDelegate(this),
				hidden: !isCallCenterAdmin(),
				iconCls : 'mp-queue32',
				nn: 'action_ProfileQueue',
				text: WND_DIRECTION_JOURNAL,
				tooltip: WND_DIRECTION_JOURNAL
			},
			action_PersonSearch:
			{
				nn: 'action_PersonSearch',
				tooltip: 'Поиск пациента',
				text: 'Поиск пациента',
				iconCls : 'patient-search32',
				disabled: false,
				handler: function()
				{
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							getWnd('swPersonEditWindow').show({
								Person_id: person_data.Person_id,
								Server_id: person_data.Server_id
							});
						},
						searchMode: 'all'
					});
				}
			},
			action_directories: {
				nn: 'action_directories',
				text: "Справочники",
				tooltip: "Справочники",
				iconCls: 'book32',
				menuAlign: 'tr',
				menu: new Ext.menu.Menu({
					items: [
						{
							tooltip: 'Справочник МЭС',
							text: 'Справочник МЭС',
							iconCls: 'report32',
							handler: function() {
								getWnd('swMesOldSearchWindow').show({action: 'view'});
							}.createDelegate(this)
						},
						{
							text: 'Справочник услуг',
							tooltip: 'Справочник услуг',
							iconCls: 'services-complex16',
							handler: function() {
								getWnd('swUslugaTreeWindow').show({action: 'view'});
							}
						},
						sw.Promed.Actions.swDrugDocumentSprAction,
						{
							text: 'МНН: Ввод латинских наименований',
							tooltip: 'МНН: Ввод латинских наименований',
							iconCls : 'drug-viewmnn16',
							handler: function() {
								getWnd('swDrugMnnViewWindow').show({privilegeType: 'all',action: 'view'});
							}
						},
						{
							text: 'Торг.наим.: Ввод латинских наименований',
							tooltip: 'Торг.наим.: Ввод латинских наименований',
							iconCls : 'drug-viewtorg16',
							handler: function() {
								getWnd('swDrugTorgViewWindow').show({action: 'view'});
							}
						},
						{
							text: 'Справочник медикаментов в ' + getCountryName('predl'),
							tooltip: 'Справочник медикаментов в ' + getCountryName('predl'),
							iconCls: 'rls16',
							handler: function()
							{
								getWnd('swRlsViewForm').show({action: 'view'});
							},
							hidden: false
						},
						{
							text: 'Глоссарий',
							tooltip: 'Глоссарий',
							iconCls : 'glossary16',
							handler: function()
							{
								getWnd('swGlossarySearchWindow').show({action: 'view'});
							}
						}
					]
				})
			},
            action_Moderation: {
                nn: 'action_Moderation',
				hidden: !isUserGroup('InetModer'),
                iconCls: 'web-record32',
                text: lang['moderatsiya'],
                tooltip: lang['moderatsiya'],
                menu: new Ext.menu.Menu({
                    items: [
                        {
                            handler: function() {
                                getWnd('swTimetableGrafModerationWindow').show();
                            }.createDelegate(this),
                            iconCls: 'web-record32',
                            nn: 'action_TimetableMModeration',
                            id:'InternetModeration',
                            text: lang['moderatsiya_internet-zapisi'],
                            tooltip: lang['moderatsiya_internet-zapisi']
                        },{
                            handler: function() {
                                getWnd('swInetPersonModerationWindow').show();
                            }.createDelegate(this),
                            iconCls: 'web-record32',
                            nn: 'action_InetPersonModeration',
                            id:'InetPersonModeration',
                            text: lang['moderatsiya_lyudey'],
                            tooltip: lang['moderatsiya_lyudey_s_portala_samozapisi']
                        }
                    ]
                })
            },
			action_JourNotice: {
				handler: function() {
					getWnd('swMessagesViewWindow').show();
				}.createDelegate(this),
				iconCls: 'notice32',
				nn: 'action_JourNotice',
				text: lang['jurnal_uvedomleniy'],
				tooltip: lang['jurnal_uvedomleniy']
			},
			action_PersonCardSearch: {
				handler: function() {
					getWnd('swPersonCardSearchWindow').show();
				},
				iconCls : 'card-search32',
				nn: 'action_PersonCardSearch',
				text: WND_POL_PERSCARDSEARCH,
				tooltip: lang['rpn_poisk']
			},
			action_FindRegions: {
				handler: function() {
					getWnd('swFindRegionsWindow').show({ARMType:'callcenter'});
				},
				iconCls : 'mp-region32',
				nn: 'action_FindRegions',
				text: WND_FRW,
				tooltip: lang['poisk_uchastkov_i_vrachey_po_adresu']
			},
			actions_settings: {
				nn: 'actions_settings',
				iconCls: 'settings32',
				text: 'Сервис',
				tooltip: 'Сервис',
				listeners: {
					'click': function(){
						var menu = Ext.menu.MenuMgr.get('wpsw_menu_windows');
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
				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_UserProfile',
							text: 'Мой профиль',
							tooltip: 'Профиль пользователя',
							iconCls : 'user16',
							hidden: false,
							handler: function()
							{
								args = {};
								args.action = 'edit';
								getWnd('swUserProfileEditWindow').show(args);
							}
						},
						{
							nn: 'action_settings',
							text: 'Настройки',
							tooltip: 'Просмотр и редактирование настроек',
							iconCls : 'settings16',
							handler: function()
							{
								getWnd('swOptionsWindow').show();
							}
						},
						{
							nn: 'action_selectMO',
							text: 'Выбор МО',
							tooltip: 'Выбор МО',
							hidden: !isSuperAdmin(),
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
							}
						},
						{
							text:'Помощь',
							nn: 'action_help',
							iconCls: 'help16',
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'menu_help',
									items:
										[
											{
												text: 'Вызов справки',
												tooltip: 'Помощь по программе',
												iconCls : 'help16',
												handler: function()
												{
													ShowHelp('Содержание');
												}
											},
											{
												text: 'Форум поддержки',
												iconCls: 'support16',
												xtype: 'tbbutton',
												handler: function() {
													window.open(ForumLink);
												}
											},
											{
												text: 'О программе',
												tooltip: 'Информация о программе',
												iconCls : 'promed16',
												testId: 'mainmenu_help_about',
												handler: function()
												{
													getWnd('swAboutWindow').show();
												}
											}
										]
								}),
							tabIndex: -1
						},
						{
							//text: 'Информация о пользователе',
							text: 'Данные об учетной записи пользователя',
							nn: 'action_user_about',
							iconCls: 'user16',
							menu: new Ext.menu.Menu(
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
								})
						},
						{
							text: 'Окна',
							nn: 'action_windows',
							iconCls: 'windows16',
							listeners: {
								'click': function(e) {
									var menu = Ext.menu.MenuMgr.get('wpsw_menu_windows');
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
									var menu = Ext.menu.MenuMgr.get('wpsw_menu_windows');
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
							menu: new Ext.menu.Menu(
								{
									//plain: true,
									id: 'wpsw_menu_windows',
									items: [
										'-'
									]
								}),
							tabIndex: -1
						}/*,
						 {
						 nn: 'action_exit',
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
						 }*/
					]
				})
			},
            action_Newslatter: {
                handler: function() {
                    getWnd('swNewslatterListWindow').show();
                }.createDelegate(this),
				hidden: !isUserGroup('Newslatter'),
                iconCls: 'mail32',
                nn: 'action_Newslatter',
				id: 'wpprw_action_Newslatter',
                text: lang['upravlenie_rassyilkami'],
                tooltip: lang['upravlenie_rassyilkami']
            },
			action_TFOMSQueryList: {
				nn: 'action_TFOMSQueryList',
				//hidden: getRegionNick().inlist(['by']),
				text: 'Запросы на просмотр ЭМК',
				tooltip: 'Запросы на просмотр ЭМК',
				iconCls: 'tfoms-query32',
				handler: function () {
					getWnd('swTFOMSQueryWindow').show({ARMType: 'mstat'});
				}
			},
			action_Treatments: {
				nn: 'action_Treatments',
				text: 'Обращения',
				menuAlign: 'tr',
				tooltip: 'Обращения',
				iconCls: 'reports32',
				menu: new Ext.menu.Menu({
					items: [
						{
							nn: 'action_swTreatmentSearchAction',
							handler: function() {
								if ( ! getWnd('swTreatmentSearchWindow').isVisible() ){
									getWnd('swTreatmentSearchWindow').show();
								}
							},
							iconCls : 'petition-search16',
							text: langs('Регистрация обращений: Поиск'),
							tooltip: langs('Регистрация обращений: Поиск')
							//hidden: !isAccessTreatment()
						},
						{
							nn: 'action_LpuScheduleWorkDoctor',
							handler: function() {
								if ( ! getWnd('swTreatmentReportWindow').isVisible() ){
									getWnd('swTreatmentReportWindow').show();
								}
							},
							iconCls : 'petition-report16',
							text: langs('Регистрация обращений: Отчетность'),
							tooltip: langs('Регистрация обращений: Отчетность')
							//hidden: !isAccessTreatment()
						}
					]
				})
			}
		};
		
		this.onKeyDown = function (inp, e) {
		
			if ( e.getKey() == Ext.EventObject.ENTER ) {
				e.stopEvent();

				var counter = 0;
				for (var i in curWnd.FilterPanel.getForm().getValues()){
					if (curWnd.FilterPanel.getForm().getValues()[i] != '') {
					counter++;
					}
				}
				if ( counter <= 2){
					sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {});
					return false;
				}
				curWnd.doSearch();
				curWnd.MainViewFrame.setParam('start', 0);
			}

		};

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: curWnd,
			labelWidth: 120,
			filter: {
				title: lang['filtryi'],
				layout: 'form',
				items: [{
					name: 'SearchFormType',
					value: 'PersonCallCenter',
					xtype: 'hidden'
				}, {
					name: 'AddressStateType_id',
					value: 1,
					xtype: 'hidden'
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Person_Surname',
							width: 200,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Person_Firname',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: lang['otchestvo'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Person_Secname',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							fieldLabel: lang['dr'],
							format: 'd.m.Y',
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Person_Birthday',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['ulitsa'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Address_Street',
							width: 200,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['dom'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Address_House',
							width: 100,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['god_rojdeniya'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Person_BirthdayYear',
							width: 120,
                            maskRe: /\d/,
							xtype: 'textfield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['nomer_amb_kartyi'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'PersonCard_Code',
							width: 200,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: lang['lpu'],
							hiddenName: 'AttachLpu_id',
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							listWidth: 400,
							width: 345,
							xtype: 'swlpucombo'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['seriya_polisa'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Polis_Ser',
							width: 100,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 200,
						items: [{
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: lang['nomer_polisa'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Polis_Num',
							width: 120,
							xtype: 'numberfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowNegative: false,
							enableKeyEvents: true,
							fieldLabel: lang['ed_nomer'],
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Person_Code',
							width: 120,
							xtype: 'numberfield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: curWnd.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								var counter = 0;
								for (var i in curWnd.FilterPanel.getForm().getValues()){
									if (curWnd.FilterPanel.getForm().getValues()[i] != '') {
									counter++;
									}
								}
								if ( counter <= 2){
									sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {});
									return false;
								}
								curWnd.doSearch();
								curWnd.MainViewFrame.setParam('start', 0);
							}
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: curWnd.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								curWnd.doReset();
                                curWnd.MainViewFrame.getGrid().getStore().removeAll();
                                curWnd.TtgViewFrame.getGrid().getStore().removeAll();
							}
						}]
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: lang['schitat_s_kartyi'],
								iconCls: 'idcard16',
								handler: function()
								{
									curWnd.readFromCard();
								}
							}]
					}]
				}]
			}
		});

		this.MainViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ 
					name: 'action_add',
					handler:function (){
						getWnd('swPersonEditWindow').show({
							action: 'add',
							fields: {
								// не стал убирать передачу этих параметров, вдруг захотят обратно вернуть
								// :) захотели (refs #12322)
								'Person_SurName': curWnd.FilterPanel.getForm().findField('Person_Surname').getValue().toUpperCase(),
								'Person_FirName': curWnd.FilterPanel.getForm().findField('Person_Firname').getValue().toUpperCase(),
								'Person_SecName': curWnd.FilterPanel.getForm().findField('Person_Secname').getValue().toUpperCase(),
								'Person_BirthDay': curWnd.FilterPanel.getForm().findField('Person_Birthday').getValue()
							}
						}
						)
					}
				},
				{ 
					name: 'action_edit',
					handler:function(){
						var sr = curWnd.MainViewFrame.ViewGridPanel.getSelectionModel().getSelected();
						getWnd('swPersonEditWindow').show({
									action: 'edit',
									Person_id: sr.get('Person_id'),
									Server_id: sr.get('Server_id')
						});
					}
				},
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_refresh' },
				{ name: 'action_print', text: lang['pechat_spiska'] }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_SEARCH,
			id: curWnd.id + 'CallCenterWorkPlacePanel',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				curWnd.openDirectionMasterWindow();
			},
			onLoadData: function(sm, index, record) {
				//
			},
			onRowSelect: function(sm, index, record) {
				var person_data = this.getParamsIfHasPersonData();

				curWnd.bj.mainGrid.removeAll({clearAll:true});
				curWnd.bj.mainGrid.setActionDisabled('action_adddirection', true);
				curWnd.bj.mainGrid.setActionDisabled('show_history', !( record && !Ext.isEmpty(record.get('Person_id')) ));

				if ( record && !Ext.isEmpty(record.get('Person_id')) ) {
					this.setActionDisabled('action_view', false);
					this.setActionDisabled('print_evnpl_blank', false);
					curWnd.bj.mainGrid.setActionDisabled('action_adddirection', false);
					curWnd.bj.doSearch({Person_id: record.get('Person_id'),person_data:person_data});
				}
				else {
					this.setActionDisabled('action_view', true);
					this.setActionDisabled('print_evnpl_blank', true);
				}
				curWnd.TtgViewFrame.removeAll({clearAll:true});
			},
			pageSize: 50,
			paging: true,
			region: 'center',
			root: 'data',
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'Person_id', type: 'int', header: 'ID', key: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'Person_IsDead', type: 'string', hidden: true },
				{ name: 'PersonCard_Code', type: 'string', header: lang['№_amb_kartyi'], width: 80, hidden:true },
				{ name: 'Person_Surname', type: 'string', header: lang['familiya'], id: 'autoexpand', width: 100 },
				{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 100 },
				{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 100 },
				{ name: 'Person_Birthday', type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'Person_deadDT', type: 'date', header: lang['data_smerti'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'Person_Age', type: 'int', header: lang['vozrast'] },
				{ name: 'Person_PolisInfo', type: 'string', header: lang['polis'], width: 160 },
				{ name: 'Person_PAddress', type: 'string', header: lang['adres_projivaniya'], width: 320 },
                { name: 'Person_Phone', type: 'string', header: lang['telefon'], width: 120},
				{ name: 'AttachLpu_Name', type: 'string', header: lang['mo_prik'] },
				{ name: 'PersonCard_begDate', type: 'date', header: lang['prikreplenie'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'PersonCard_endDate', type: 'date', header: lang['otkreplenie'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'LpuAttachType_Name', type: 'string', header: lang['tip_prikrepleniya'], width: 200 },
				{ name: 'LpuRegionType_Name', type: 'string', header: lang['tip_uchastka'], width: 200 },
				{ name: 'LpuRegion_Name', type: 'string', header: lang['uchastok'] },
                { name: 'LpuRegion_FapName', type: 'string', header: lang['fap_uchastok'],width:100, hidden: (getRegionNick() != 'perm' && getRegionNick() != 'ufa' && getRegionNick() != 'penza')},
				{ name: 'PersonCard_IsAttachCondit',  header: lang['usl_prikrepl'], type: 'checkbox' },
				{ name: 'Person_IsBDZ', header: lang['bdz'], type: 'checkbox', width: 40 },
				{ name: 'Person_IsFedLgot', header: lang['fed_lg'], type: 'checkbox', width: 40 },
				{ name: 'Person_IsRefuse', header: lang['otkaz'], type: 'checkbox', width: 40 },
				{ name: 'Person_IsRegLgot', header: lang['reg_lg'], type: 'checkbox', width: 40 },
				{ name: 'Person_Is7Noz', header: lang['7_noz'], type: 'checkbox', width: 40 },
				{ name: 'Person_UAddress', header: lang['adres_registratsii'], type: 'string', width: 240 }
			],
			title: lang['jurnal_rabochego_mesta'],
			totalProperty: 'totalCount',
            onLoadData: function(sm, index, records) {
				this.MainViewFrame.getGrid().getStore().each(function(rec,idx,count) {
					var naHref;
					if (naHref = rec.get('NewslatterAccept')) {
						naHref = '<a href="javascript://" onClick="Ext.getCmp(\''+this.id+'\').openNewslatterAcceptEditWindow(\''+rec.get('NewslatterAccept_id')+'\', \''+rec.get('Person_id')+'\')">'+rec.get('NewslatterAccept')+'</a>';							
						rec.set('NewslatterAccept', naHref);
						rec.commit();
					}
				}.createDelegate(this));
            }.createDelegate(this),
			getParamsIfHasPersonData: function() {
				var viewframe = this;
				var selected_record = viewframe.ViewGridPanel.getSelectionModel().getSelected();
				
				// Собираем информацию о человеке в случае, если в гриде есть поля по человеку
				if (viewframe.hasPersonData() && selected_record != undefined)
				{
					var params = new Object();
					//log('hasPersonData selected record');
					params.Person_id = selected_record.get('Person_id');
					params.Server_id = selected_record.get('Server_id');
					params.PersonEvn_id = selected_record.get('PersonEvn_id');
					params.AttachLpu_Name = selected_record.get('AttachLpu_Name');
					// некоторые именуют как в базе, но почему-то изначально выбрано не такое именование
					// но так-то надо в гридах переделать
					if ( selected_record.get('Person_Birthday') )
						params.Person_Birthday = selected_record.get('Person_Birthday');
					else
						params.Person_Birthday = selected_record.get('Person_BirthDay');
					if ( selected_record.get('Person_Surname') )
						params.Person_Surname = selected_record.get('Person_Surname');
					else
						params.Person_Surname = selected_record.get('Person_SurName');
					if ( selected_record.get('Person_Firname') )
						params.Person_Firname = selected_record.get('Person_Firname');
					else
						params.Person_Firname = selected_record.get('Person_FirName');
					if ( selected_record.get('Person_Secname') )
						params.Person_Secname = selected_record.get('Person_Secname');
					else
						params.Person_Secname = selected_record.get('Person_SecName');
					params.onHide = function()
					{
						var index = viewframe.ViewGridPanel.getStore().findBy(function(rec) { return rec.get(viewframe.jsonData['key_id']) == selected_record.data[viewframe.jsonData['key_id']]; });
						viewframe.ViewGridPanel.focus();
						viewframe.ViewGridPanel.getView().focusRow(index);
						viewframe.ViewGridPanel.getSelectionModel().selectRow(index);
					}
					if (viewframe.callbackPersonEdit)
					{
						viewframe.selectedRecord = selected_record;
						params.callback = function() {this.callbackPersonEdit()}.createDelegate(viewframe);
					}
					return params;
				}
				return false;
			}
		});
		
		this.TtgViewFrame = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', text: lang['prostavit_yavku'], hidden: (!getGlobalOptions().region || !getGlobalOptions().region.nick.inlist(['msk','khak'])), handler: function() { curWnd.setUnSetPersonMarkAppear(); } },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', text: lang['osvobodit_zapis'], hidden: false, handler: function() { curWnd.scheduleDelete(); } },
				{ name: 'action_refresh', hidden: true }, // рефреш здесь не нужен, поскольку обновление грида происходит по выбору строки в основном гриде
				{ name: 'action_print', text: lang['pechat_spiska'] }
			],
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_REG_GETRECBYPERSON,
			id: curWnd.id + 'TimetableGrafPanel',
			onDblClick: function() {
				this.onEnter();
			},
			onEnter: function() {
				if ( this.ViewActions.action_edit.isHidden() == false && getGlobalOptions().region && getGlobalOptions().region.nick.inlist(['msk','khak']) ) {
					this.getAction('action_edit').execute();
				}
			},
			onLoadData: function(sm, index, record) {
				//
			},
			onRowSelect: function(sm, index, record) {
				var text = (record.get('TimetableGraf_isVizit') == 'true')?lang['otmenit_yavku']:lang['prostavit_yavku'];
				this.ViewActions.action_edit.setText(text);
				text = ( record.get('EvnQueue_id') )?lang['isklyuchit_iz_ocheredi']:lang['osvobodit_zapis'];
                this.ViewActions.action_delete.setText(text);
				var del = (record.get('pmUser_id')==getGlobalOptions().pmuser_id&&record.get('recDate')>=Date.parseDate(getGlobalOptions().date,'d.m.Y'))?false:true;
				this.ViewActions.action_delete.setDisabled(del);
				this.ViewActions.action_edit.setDisabled( !record.get('TimetableGraf_id') );
			},
			paging: false,
			region: 'south',
			auditOptions: {
				maskRe: new RegExp('^([a-z]+)_(\\d+)$', 'i'),
				maskParams: ['key_field', 'key_id'],
				needIdSuffix: true
			},
			stringfields: [
				// Поля для отображение в гриде
				{ name: 'Item_id', type: 'string', header: 'ID', key: true },
				{ name: 'TimetableGraf_id', type: 'int', hidden: true },
				{ name: 'EvnQueue_id', type: 'int', hidden: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'TimetableGraf_Mark', type: 'int', hidden: true },
				{ name: 'RecType_Name', type: 'string', header: lang['tip_zapisi'], width: 100 },
				{ name: 'DLpu_Name', type: 'string', header: lang['napravivshee_mo'], width: 150 },
				{ name: 'DLpuUnit_Name', type: 'string', header: lang['podrazdelenie'], width: 150 },
				{ name: 'LpuSectionProfile_Name', type: 'string', header: lang['profil'], width: 150 },
				{ name: 'recDate', type: 'date', header: lang['data'], renderer: Ext.util.Format.dateRenderer('d.m.Y') },
				{ name: 'recTime', type: 'string', header: lang['vremya'], width: 80 },
				{ name: 'Lpu_Name', type: 'string', header: lang['mo'], width: 150 },
				{ name: 'LpuSection_Name', type: 'string', header: lang['otdelenie'], width: 250 },
				{ name: 'MedPersonal_Name', type: 'string', header: lang['vrach'], id: 'autoexpand', width: 100 },
				{name: 'pmUser_id',hidden:true, type: 'int', header: "pmUser_id"},
                {name: 'pmUser_Name',hidden:false, type: 'string', header: lang['sozdal'], width: 150},
				{name: 'EvnDirection_insDT',hidden:true, type: 'date',renderer: Ext.util.Format.dateRenderer('d.m.Y'), header: "Дата записи", width: 150}
				//,{ name: 'TimetableGraf_isVizit',  header: 'Прием', type: 'checkbox' }
			],
			title: lang['zapisi_patsienta']
		});
		this.TtgViewFrame.getGrid().on('keypress', this.onkeypress);
		this.TtgViewFrame.getGrid().keys = {
			key: 188,
			ctrl: true,
			handler: function() {
				curWnd.doReset();
				curWnd.FilterPanel.getForm().findField('Person_Surname').focus(1);
			}
		};
		this.bj= new sw.Promed.BaseJournal({
			region: 'south',
			height:300,
			ARMType: 'callcenter',
			winType: 'call',
			title: lang['napravleniya_i_zapisi'],
			ownerWindow:curWnd,
			actions:[
				'action_add',
				'action_delete',
				'action_leave_queue',
				'action_in_queue',
				'action_redirect',
				'action_rewrite',
				'action_view',
				'action_print',
				'show_history'
			]
		});
		this.GridPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			layout: 'border',
			defaults: {split: true},
			items: [
				this.MainViewFrame,
				/*this.TtgViewFrame,*/
				this.bj
			]
		});
		
		sw.Promed.swWorkPlaceCallCenterWindow.superclass.initComponent.apply(this, arguments);
	}
});