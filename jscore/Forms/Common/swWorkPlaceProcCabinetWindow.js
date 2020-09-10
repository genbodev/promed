/**
* АРМ медсестры процедурного кабинета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dmitry Vlasenko
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      15.01.2013
*/
sw.Promed.swWorkPlaceProcCabinetWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'swWorkPlaceProcCabinetWindow',
	gridPanelAutoLoad: false,

	// #175117
	// Идентификатор типа отделения, где работает пользователь, открывший форму:
    _LpuUnitType_id: undefined,
	
	// #175117
	// Кнопка для открытия формы "Журнал учета рабочего времени сотрудников"
	// (отображается на левой панели, только если отделение относится к типу, где
	// ведется учет смен):
    _btnTimeJournalOpen: undefined,
	
	listeners: {
		'beforehide': function()
		{
			sw.Applets.commonReader.stopReaders();
		}
	},
	getDataFromUec: function(uec_data, person_data) {
		var form = this;
		var grid = form.findById('WorkPlaceGridPanel').getGrid();
		var f = false;
		grid.getStore().each(function(record) {
			if (record.get('Person_id') == person_data.Person_id) {
				log(lang['nayden_v_gride']);
				var index = grid.getStore().indexOf(record);
				grid.getView().focusRow(index);
				grid.getSelectionModel().selectRow(index);
				form.openEvnFuncRequestEditWindow('edit', false);
				f = true;
				return;
			}
		});
		if (!f) { // Если не нашли в гриде
			// todo: Еще надо проверку в принципе на наличие такого человека в БД, и если нет - предлагать добавлять
			// Открываем на добавление
			var params = {};
			params.action = 'add';
			params.Person_id =  person_data.Person_id;
			params.PersonEvn_id = (person_data.PersonEvn_id)?person_data.PersonEvn_id:null;
			params.Server_id = (person_data.Server_id)?person_data.Server_id:null;
			params.swPersonSearchWindow = getWnd('swPersonSearchWindow');
			params.callback = function () {
				form.GridPanel.refreshRecords(null, 0);
			};
			params.onHide = function() {
				sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
			}
			getWnd('swEvnProcRequestEditWindow').show(params);
		}
	},
	show: function()
	{
		// #175117
		// Кнопку для открытия формы "Журнал учета рабочего времени сотрудников" делаем
		// видимой, только если в текущем отделении предусмотрен учет смен:
		this._LpuUnitType_id =
			arguments[0].LpuUnitType_id ||
			sw.Promed.MedStaffFactByUser.current.LpuUnitType_id;

		if (this._btnTimeJournalOpen)
			this._btnTimeJournalOpen.setVisible(isWorkShift(this._LpuUnitType_id));

		sw.Promed.swWorkPlaceProcCabinetWindow.superclass.show.apply(this, arguments);
		// Свои функции при открытии
		
		if ( arguments[0].MedService_id ) {
			this.MedService_id = arguments[0].MedService_id;
		} else {
			// Не понятно, что за АРМ открывается 
			return false;
		}

		sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});

		this.Lpu_id = arguments[0].Lpu_id || null;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		
		var usluga_combo = this.FilterPanel.getForm().findField('UslugaComplex_id');
		usluga_combo.getStore().removeAll();
		usluga_combo.getStore().baseParams.MedService_id = this.MedService_id;
		usluga_combo.getStore().baseParams['allowedUslugaComplexAttributeList'] = Ext.util.JSON.encode(['manproc']);
		usluga_combo.getStore().load();
		this.searchParams = {'MedService_id':this.MedService_id, 'wnd_id':this.id}; // для фильтрации направлений по службе
		this.doSearch('day');
		sw.Promed.MedStaffFactByUser.setMenuTitle(this, arguments[0]);
	},
	openUslugaWindow: function(params)
	{
		params.Lpu_id = this.Lpu_id;
		params.LpuSection_id = this.LpuSection_id;
		params.MedPersonal_id = this.MedPersonal_id;
		params.callback = function () {
			this.GridPanel.refreshRecords(null, 0);
		}.createDelegate(this);
		
		getWnd('swEvnUslugaProcRequestEditWindow').show(params);
	},
    openDocumentUcAddWindow: function(type_code) {
        var params = new Object();
        var edit_window_name = 'swNewDocumentUcEditWindow';

        switch(type_code) {
            case 2: //2 - Документ списания медикаментов
                params.DrugDocumentType_id = 2;
                params.FormParams = {
                    Contragent_sid: getGlobalOptions().Contragent_id
                };
                break
            case 3: //3 - Документ ввода остатков
                params.DrugDocumentType_id = 3;
                params.FormParams = {
                    Contragent_tid: getGlobalOptions().Contragent_id
                };
                break
            case 6: //6 - Приходная накладная
                params.DrugDocumentType_id = 6;
                params.FormParams = {
                    Contragent_tid: getGlobalOptions().Contragent_id
                };
                params.isSmpMainStorage = false;
                break
            case 15: //15 - Накладная на внутреннее перемещение
                params.DrugDocumentType_id = 15;
                params.FormParams = {
                    Contragent_sid: getGlobalOptions().Contragent_id
                };
                params.isSmpMainStorage = false;
                break
        }

        if (!Ext.isEmpty(params.DrugDocumentType_id)) {
            params.DrugDocumentType_Code = type_code;
            params.callback = function() { this.hide(); };
            params.action = 'add';
            params.userMedStaffFact = this.userMedStaffFact;

            getWnd(edit_window_name).show(params);
        }
    },
	buttonPanelActions: {
		action_Timetable: {
			nn: 'action_Timetable',
			tooltip: lang['rabota_s_raspisaniem'],
			text: lang['raspisanie'],
			iconCls : 'mp-timetable32',
			disabled: false, 
			handler: function()
			{
				getWnd('swTTMSScheduleEditWindow').show({
					MedService_id: Ext.getCmp('swWorkPlaceProcCabinetWindow').MedService_id,
					MedService_Name:getGlobalOptions().CurMedService_Name
				});
			}.createDelegate(this)
		},

		// #175117
		// Кнопка для открытия формы "Журнал учета рабочего времени сотрудников":
		action_TimeJournal:
		{
			nn: 'action_TimeJournal',
			itemId: 'btnTimeJournalOpen',
			text: langs('Журнал учета рабочего времени сотрудников'),
			tooltip: langs('Открыть журнал учета рабочего времени сотрудников'),
			iconCls: 'report32',
			disabled: false,
			
			handler:
				function()
				{
					var cur = sw.Promed.MedStaffFactByUser.current;

					getWnd('swTimeJournalWindow').show(
						{
							ARMType: (cur ? cur.ARMType : undefined),
							MedStaffFact_id: (cur ? cur.MedStaffFact_id : undefined),
							Lpu_id: (cur ? cur.Lpu_id : undefined)
						});
				}
		},

        action_MedOstat: {
            nn: 'action_MedOstat',
            tooltip: langs('Остатки'),
            text: langs('Остатки'),
            iconCls : 'rls-torg32',
            disabled: false,
            handler: function() {
                var wnd = Ext.getCmp('swWorkPlaceProcCabinetWindow');

                getWnd('swDrugOstatRegistryListWindow').show({
                    mode: 'suppliers',
                    userMedStaffFact: wnd.userMedStaffFact
                });
            }
        },
        action_CreateDoc: {
            nn: 'action_CreateDoc',
            tooltip: langs('Создать документ учета медикаментов'),
            text: langs('Создать документ учета медикаментов'),
            iconCls : 'document32',
            disabled: false,
            menuAlign: 'tr',
            menu: new Ext.menu.Menu({
                items: [{
                    text: langs('Заявка-требование'),
                    tooltip: langs('Заявка-требование'),
                    iconCls: 'document16',
                    handler: function() {
                        var wnd = Ext.getCmp('swWorkPlaceProcCabinetWindow');

                        getWnd('swWhsDocumentUcEditWindow').show({
                            action: 'add',
                            WhsDocumentClass_id: 2,
                            WhsDocumentClass_Code: 2,
                            userMedStaffFact: wnd.userMedStaffFact
                        });
                    }
                }, {
                    text: langs('Перемещение'),
                    tooltip: langs('Перемещение'),
                    iconCls: 'document16',
                    handler: function() {
                        var wnd = Ext.getCmp('swWorkPlaceProcCabinetWindow');
                        wnd.openDocumentUcAddWindow(15); //15 - Накладная на внутреннее перемещение
                    }
                }, {
                    text: langs('Списание'),
                    tooltip: langs('Списание'),
                    iconCls: 'document16',
                    handler: function() {
                        var wnd = Ext.getCmp('swWorkPlaceProcCabinetWindow');
                        wnd.openDocumentUcAddWindow(2); //2 - Документ списания медикаментов
                    }
                }, {
                    text: langs('Инвентаризация'),
                    tooltip: langs('Инвентаризация'),
                    iconCls: 'invent16',
                    menuAlign: 'tr?',
                    menu: new Ext.menu.Menu({
                        items: [{
                            tooltip: langs('Приказы на проведение инвентаризации'),
                            text: langs('Приказы на проведение инвентаризации'),
                            iconCls : 'document16',
                            handler: function() {
                                getWnd('swWhsDocumentUcInventOrderViewWindow').show({
                                    ARMType: 'merch'
                                });
                            }
                        }, {
                            tooltip: langs('Инвентаризационные ведомости'),
                            text: langs('Инвентаризационные ведомости'),
                            iconCls : 'document16',
                            disabled: false,
                            handler: function() {
                                var wnd = getWnd('swWorkPlaceProcCabinetWindow');
                                var wndParams = {
                                    ARMType: 'merch',
                                    MedService_id: wnd.userMedStaffFact.MedService_id,
                                    Lpu_id: wnd.userMedStaffFact.Lpu_id,
                                    LpuSection_id: wnd.userMedStaffFact.LpuSection_id,
                                    LpuBuilding_id: wnd.userMedStaffFact.LpuBuilding_id
                                };
                                if(getGlobalOptions().orgtype != 'lpu' && wnd.userMedStaffFact.MedService_id > 0){
                                    Ext.Ajax.request({
                                        params:{MedService_id:wnd.userMedStaffFact.MedService_id},
                                        callback: function(options, success, response) {
                                            if (success) {
                                                var response_obj = Ext.util.JSON.decode(response.responseText);
                                                if(response_obj[0] && response_obj[0].OrgStruct_id) {
                                                    wndParams.OrgStruct_id = response_obj[0].OrgStruct_id;
                                                }
                                            }
                                            getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
                                        },
                                        url: '/?c=MedService&m=loadEditForm'
                                    });
                                } else {
                                    getWnd('swWhsDocumentUcInventViewWindow').show(wndParams);
                                }
                            }
                        }]
                    })
                }, {
                    text: langs('Приход'),
                    tooltip: langs('Приход'),
                    iconCls: 'document16',
                    handler: function() {
                        var wnd = Ext.getCmp('swWorkPlaceProcCabinetWindow');
                        wnd.openDocumentUcAddWindow(6); //6 - Приходная накладная
                    }
                }, {
                    text: langs('Ввод остатков'),
                    tooltip: langs('Ввод остатков'),
                    iconCls: 'document16',
                    handler: function() {
                        var wnd = Ext.getCmp('swWorkPlaceProcCabinetWindow');
                        wnd.openDocumentUcAddWindow(3); //3 - Документ ввода остатков
                    }
                }]
            })
        },
        action_JourNotice: {
            nn: 'action_JourNotice',
            text: 'Журнал уведомлений',
            tooltip: 'Открыть журнал уведомлений',
            iconCls: 'notice32',
            handler: function () {
                getWnd('swMessagesViewWindow').show();
            }
        }
	},
	openEvnFuncRequestEditWindow: function(action, is_time) {
		var form = this;
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		sw.Applets.commonReader.stopReaders();

		var swPersonSearchWindow = getWnd('swPersonSearchWindow');
		if ( action == 'add' && swPersonSearchWindow.isVisible() ) {
			sw.swMsg.alert(lang['okno_poiska_cheloveka_uje_otkryito'], lang['dlya_prodoljeniya_neobhodimo_zakryit_okno_poiska_cheloveka']);
			return false;
		}
		
		var grid = this.findById('WorkPlaceGridPanel').getGrid();
		
		var params = new Object();

		params.MedService_id = this.MedService_id;
		
		params.action = action;
		params.callback = function(data) {};
        params.swWorkPlaceProcCabinetWindow = form;

		if ( action == 'add' ) {
			
			if ( grid.getSelectionModel().getSelected() && grid.getSelectionModel().getSelected().get('TimetableMedService_id') && is_time == true ) {
				var record = grid.getSelectionModel().getSelected();
				params.TimetableMedService_id = record.get('TimetableMedService_id');
			}
			
            swPersonSearchWindow.show({
				onClose: function() {
					sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						//grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;
                    params.swPersonSearchWindow = swPersonSearchWindow;
					params.callback = function () {
						form.GridPanel.refreshRecords(null, 0);
					};
					params.onHide = function() {
						if (swPersonSearchWindow.isVisible()) {
							//На форме поиска человека нет такого метода
							//sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
						}
					}
					this.hide(); // закрываем форму поиска человека
					getWnd('swEvnProcRequestEditWindow').show(params);
				},
				searchMode: 'all',
				needUecIdentification: true
			});
			
		} else {
			
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnDirection_id') ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zayavka_ili_napravlenie']);
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.EvnFuncRequest_id = record.get('EvnFuncRequest_id');
			params.EvnDirection_id = record.get('EvnDirection_id');
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');
			params.callback = function () {
				form.GridPanel.refreshRecords(null, 0);
			};
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));

				sw.Applets.commonReader.startReaders({callback: this.getDataFromUec.createDelegate(this)});
			}
			
			getWnd('swEvnProcRequestEditWindow').show(params);
			
		}
	},
	initComponent: function() {
		var form = this;
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
		
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form, 
			filter: {
				title: 'Фильтр',
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 150,
							name: 'Search_SurName',
							fieldLabel: 'Фамилия',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 45,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 150,
							name: 'Search_FirName',
							fieldLabel: 'Имя',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items:
						[{
							xtype: 'textfieldpmw',
							width: 150,
							name: 'Search_SecName',
							fieldLabel: 'Отчество',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 35,
						items:
						[{
							xtype:'swdatefield',
							format:'d.m.Y',
							plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Search_BirthDay',
							fieldLabel: 'ДР',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 145,
						items:
						[{
							xtype: 'textfield',
							width: 100,
							name: 'EvnDirection_Num',
							fieldLabel: 'Номер направления',
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 65,
						items: 
						[{
							xtype: 'swuslugacomplexpidcombo',
							width: 450,
							name: 'Search_Usluga',
							hiddenName: 'UslugaComplex_id',
							fieldLabel: 'Услуга',
							//allowBlank: false,
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 55,
						items: 
						[{
							fieldLabel: 'Cito',
							comboSubject: 'YesNo',
							name: 'EvnDirection_IsCito',
							hiddenName: 'EvnDirection_IsCito',
							xtype: 'swcommonsprcombo'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: 
						[{
							xtype: 'button',
							id: form.id+'BtnSearch',
							text: 'Найти',
							iconCls: 'search16',
							handler: function()
							{
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: 
						[{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id+'BtnClear',
							text: 'Сброс',
							iconCls: 'reset16',
							handler: function()
							{
								form.doReset();
							}.createDelegate(form)
						}]
					}]
				}]
			}
		});
		
		this.GridPanel = new sw.Promed.ViewFrame({
			id: 'WorkPlaceGridPanel',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			grouping: true,
			groupTextTpl: '{[values.text == "(Пусто)" ? "Очередь" : values.text]} ({[values.rs.length]} {[parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([1]) ?"заявка" :(parseInt(values.rs.length.toString().charAt(values.rs.length.toString().length-1)).inlist([2,3,4]) ? "заявки" : "заявок")]})',
			groupingView: {showGroupName: false, showGroupsText: true},
			actions:
			[
				{name:'action_add', handler: function() { this.openEvnFuncRequestEditWindow('add', false);}.createDelegate(this) },
				{name:'action_edit', handler: function() { this.openEvnFuncRequestEditWindow('edit', false);}.createDelegate(this) },
				{name:'action_view', handler: function() { this.openEvnFuncRequestEditWindow('view', false);}.createDelegate(this) },
				{name:'action_delete',text:'Отклонить'},
				{name: 'action_refresh'},
				{name: 'action_print'}
			],
			autoLoadData: false,
			pageSize: 20,
			useEmptyRecord: false,
			stringfields:
			[
				// Поля для отображение в гриде
				{name: 'EvnDirection_id', type: 'int', header: 'ID', key: true},
				{name: 'EvnFuncRequest_id', type: 'int', hidden:true},
				{name: 'TimetableMedService_id', type: 'int', hidden:true},
				{name: 'TimetableMedService_begDate', type: 'date', hidden: true, group: true, sort: true, direction: 'DESC' },
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'PersonEvn_id', type: 'int', hidden: true},
				{name: 'Server_id', type: 'int', hidden: true},
				{name: 'PersonQuarantine_IsOn', type: 'string', hidden: true},
				{name: 'EvnDirection_IsCito', header: 'Cito!', type: 'checkbox', width: 40},
				{name: 'FuncRequestState', header: 'Приём', type: 'checkbox', width: 60 },
				{name: 'EvnDirection_setDT', dateFormat: 'd.m.Y', type: 'date', header: 'Дата направления', width: 120},
				{name: 'TimetableMedService_begTime', type: 'string', header: 'Запись', width: 120/*, sort: true, direction: 'ASC'*/},
				{name: 'EvnUslugaPar_setDate', dateFormat: 'd.m.Y', type: 'date', header: 'Дата исследования', width: 120, hidden: (getGlobalOptions().region.nick != 'kz')},
				{name: 'TimetableMedServiceType', type: 'string', header: 'Расписание', width: 120},
				{name: 'EvnDirection_Num', header: 'Номер направления', type: 'string', width: 160},
				{name: 'Person_FIO', header: 'ФИО пациента', type: 'string', width: 320},
				{name: 'EvnFuncRequest_UslugaCache', header: 'Список услуг', renderer: function(value, cellEl, rec) {
					var result = '';
					if (!Ext.isEmpty(value)) {
						// разджейсониваем
						var uslugas = Ext.util.JSON.decode(value);
						for(var k in uslugas) {
							if (uslugas[k].UslugaComplex_Name) {
								if (!Ext.isEmpty(result)) {
									result += '<br />';
								}
								result += uslugas[k].UslugaComplex_Name;

								if (!Ext.isEmpty(uslugas[k].EvnUslugaPar_setDate)) {
									result += ' <a title="Отменить выполнение услуги" href="javascript://" onClick="Ext.getCmp(\'swWorkPlaceFuncDiagWindow\').cancelEvnUslugaPar({' +
									'EvnUslugaPar_id: ' + uslugas[k].EvnUslugaPar_id +
									'});"><img width="14" src="/img/icons/cancel_blue16.png" /></a>';
								}
							}
						}
					}
					return result;
                }, width: 420, id: 'autoexpand'},
				{name: 'pmUser_insID', type: 'int', hidden: true}
			],
			dataUrl: '/?c=EvnFuncRequestProc&m=loadEvnFuncRequestList',
			totalProperty: 'totalCount',
			title: 'Список заявок',
			onLoadData: function(sm, index, record)
			{
				if (!this.getGrid().getStore().totalLength) {
					this.getGrid().getStore().removeAll();
				}
				if( Ext.get(this.getGrid().getView().getGroupId('(Пусто)')) != null ) {
					this.getGrid().getView().toggleGroup(this.getGrid().getView().getGroupId('(Пусто)'), false);
				}
				
			},
			onDblClick: function(grid, number, object){
				this.onEnter();
			},
			onEnter: function()
			{
				var record = this.GridPanel.ViewGridPanel.getSelectionModel().getSelected();
				if (Ext.isEmpty(record.get('EvnDirection_id'))) {
					this.openEvnFuncRequestEditWindow('add', true);
				} else {
					this.openEvnFuncRequestEditWindow('edit', false);
				}
			}.createDelegate(this)
		});
		this.GridPanel.getGrid().getView().getRowClass = function(row, index) {
			var cls = '';
			if (row.get('PersonQuarantine_IsOn') == 'true') {
				cls = cls + 'x-grid-rowbackred ';
			}
			return cls;
		};
		sw.Promed.swWorkPlaceProcCabinetWindow.superclass.initComponent.apply(this, arguments);
    setTimeout(() => this._finishInitComponent(), 1);
	},

/******* _finishInitComponent *************************************************
 *
 ******************************************************************************/
  _finishInitComponent: function()
   {
    this.items.each(this._findComponents, this);
   },

/******* _findComponents ******************************************************
 *
 ******************************************************************************/
  _findComponents: function(item)
   {
    if (item.itemId)
     this['_' + item.itemId] = item;

    if (item.items)
     item.items.each(this._findComponents, this);
   }
});
