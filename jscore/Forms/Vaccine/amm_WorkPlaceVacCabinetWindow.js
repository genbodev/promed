/**
* АРМ медсестры процедурного кабинета
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Tagir Nigmatullin, Ufa
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      октябрь 2013
*/

var PageSize = 50;
var test = 0;
//var arg;

var swPromedActions = {
	
	VacPresence: {
            
					text: 'Наличие вакцин',
					tooltip: 'Наличие вакцин',
					iconCls : 'vac-plan16',
					handler: function()
					{
						getWnd('amm_PresenceVacForm').show();
					}
				},
				PresenceVacForm: {
					text: 'Национальный календарь прививок',
					tooltip: 'Национальный календарь прививок',
					iconCls : 'pol-immuno16',
					handler: function()
					{
						getWnd('amm_SprNacCalForm').show();
					}
				},
				SprVaccineForm: {
					text: 'Справочник вакцин',
					tooltip: 'Справочник вакцин',
					iconCls : 'pol-immuno16',
					handler: function()
					{
						getWnd('amm_SprVaccineForm').show();
					}
				}	
		};
			 
sw.Promed.amm_WorkPlaceVacCabinetWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,
{
	id: 'amm_WorkPlaceVacCabinetWindow',
        ARMType: '',
		
		  listeners: {
			'success': function(source, params) {
				Ext.getCmp('Grid4CabVac').ViewGridPanel.getStore().reload();
					}
		  },
	
        doSearch: function(mode){
		//alert ('doSearch');
		
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
		
//                console.log('FilterPanel', this.FilterPanel);
//		if ( this.FilterPanel.isEmpty() ) {
//			/*sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function() {
//			});
//			*/
//                        alert('Ошибка');	
//                this.doReset(); // ничего не задали - ничего не нашли
//			return false;
//		}
		
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
		//alert ('this.dateMenu.getValue1()');
                
		params.begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		params.limit = 50;
		params.start = 0;
                params.Filter = Ext.getCmp('Grid4CabVac').ViewGridModel.grid.store.baseParams.Filter;
                if ( Ext.getCmp('amm_WorkPlaceVacCabinetWindow').ARMType == 'vac') 
                    {params.MedService_id = this.MedService_id;}
                else
                    {params.MedService_id =  Ext.getCmp('CabVac_ComboMedServiceVac').getValue()};
                this.GridPanel.removeAll({clearAll:true});
		this.GridPanel.loadData({globalFilters: params});
	},
                
               buttons: [
		
                {
				text : 'Сбросить фильтр',  //BTN_FRMRESET,
                                style: 'color:red!important',
                                id: 'VacCabinet', 
				handler : function(button, event) {
                                    //alert('Ok');  targetCellName
                                    // Сброс выделения 
                                    Ext.getCmp('swFilterGridPlugin').setHeader('passive');
                                   //  Ext.getCmp('Grid4CabVac').FilterSettings[Ext.getCmp('swFilterGridPlugin').targetCellName] = false;
                                                          
                                    Ext.getCmp('Grid4CabVac').ViewGridModel.grid.store.baseParams.Filter = '{}';
                                    //Ext.getCmp('Grid4CabVac').ViewGridPanel.getStore().reload();
                                    Ext.getCmp('amm_WorkPlaceVacCabinetWindow').doSearch();
                                    if (Ext.getCmp('Grid4CabVac').FilterSettings != undefined) {
                                        var obj = Ext.getCmp('Grid4CabVac').FilterSettings;
                                        for (var col in obj) {
                                            Ext.getCmp('Grid4CabVac').FilterSettings[col] = false;
                                        }
                                    }
                                }
                        },
                '-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : 'Закрыть',
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
               
	show: function()
	{
		var curWnd = this;
                sw.Promed.amm_WorkPlaceVacCabinetWindow.superclass.show.apply(this, arguments);
		// Свои функции при открытии 
               Ext.getCmp('amm_WorkPlaceVacCabinetWindow').ARMType = arguments[0].ARMType;
		if ( Ext.getCmp('amm_WorkPlaceVacCabinetWindow').ARMType == 'vac') {
			this.MedService_id = arguments[0].MedService_id;
                      
                        if (test == 0) {
                        Ext.getCmp('CabVac_FormMedServiceVac').hide();
                       
                        }
                        /*
                        Ext.getCmp('CabVac_ComboMedServiceVac').getStore().load ({
                                 params:{
                                                 Lpu_id: getGlobalOptions().lpu_id
                                 }
                                 ,
                                 callback: function() {
                                                 Ext.getCmp('CabVac_ComboMedServiceVac').reset();
                                                 Ext.getCmp('CabVac_ComboMedServiceVac').setValue(0)
                                                 //Ext.getCmp('amm_WorkPlaceVacCabinetWindow').doSearch('day');
                                 }           
                             })
                        */
		} else {
			// Не понятно, что за АРМ открывается 
//			return false;
                        this.MedService_id = 0;
                         //alert('show' );
                     
                if (test == 0) { 
                //Ext.getCmp('CabVac_FormMedServiceVac').show();
                      
                    Ext.getCmp('CabVac_ComboMedServiceVac').getStore().load ({
                                 params:{
                                                 Lpu_id: getGlobalOptions().lpu_id
                                 }
                                 ,
                                 callback: function() {
                                                 Ext.getCmp('CabVac_ComboMedServiceVac').reset();
                                                 Ext.getCmp('CabVac_ComboMedServiceVac').setValue(0)
                                                 //Ext.getCmp('amm_WorkPlaceVacCabinetWindow').doSearch('day');
                                 }           
                             })
                }
		}
                
                
                
             
		//this.MedPersonal_id = arguments[0].MedPersonal_id || null;

		this.searchParams = {'lpu_id': getGlobalOptions().lpu_id, 'MedService_id':this.MedService_id, 'wnd_id':this.id, 'start':0, 'limit':Ext.getCmp('Grid4CabVac').pageSize}; // для фильтрации направлений по службе
		this.doSearch();
   
                with(this.LeftPanel.actions) {
                         action_Report.setHidden(true);
		}
                
			  
	},
		   
	buttonPanelActions: {
		  action_Vaccination:
			{
				nn: 'action_Vaccination',
				tooltip: 'Открыть карту профилактических прививок',
				text: 'Иммунопрофилактика',
				iconCls : 'vac-plan32',
				handler: function()
				{
					var grid = Ext.getCmp('amm_WorkPlaceVacCabinetWindow').GridPanel.getGrid(),
						selected_record = grid.getSelectionModel().getSelected();
					if (!selected_record || !selected_record.get('Person_id')) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Выберите человека',
							title: ERR_WND_TIT
						});
						return false;
					}
					getWnd('amm_Kard063').show({person_id: selected_record.get('Person_id')});
				}
			},		   
			action_mmunoprof: 
			{
				nn: 'action_mmunoprof',
				tooltip: 'Иммунопрофилактика',
				text: 'Иммунопрофилактика',
				iconCls : 'immunoprof32',
				disabled: false, 
				menuAlign: 'tr?' ,
				menu: new Ext.menu.Menu({
					items: [
						swPromedActions.VacPresence,
						swPromedActions.PresenceVacForm,
						swPromedActions.SprVaccineForm												]
				})							  
			},
			action_immunojournal: 
			{
				nn: 'action_vacSpr',
				tooltip: 'Журнал вакцинации',
				text: 'Журнал вакцинации',
				iconCls : 'reports32',
				disabled: false, 
				menuAlign: 'tr?', 
				handler: function() {
					var lpu_id = getGlobalOptions().lpu_id;
					var dt1 = Ext.getCmp('Grid4CabVac').ViewGridStore.baseParams.begDate;
					var dt2 = Ext.getCmp('Grid4CabVac').ViewGridStore.baseParams.endDate;
					var paramMedServise = Ext.getCmp('amm_WorkPlaceVacCabinetWindow').MedService_id;
					if (paramMedServise == -1) 
						{paramMedServise = '';}
					else 
						{ paramMedServise = '&paramMedServise=' + paramMedServise};
					var url = ((getGlobalOptions().birtpath)?getGlobalOptions().birtpath:'')+'/run?__report=report/vac_repDaily.rptdesign&paramLpu=' + lpu_id 
						//+ '&paramMedServise=' 
						+ paramMedServise
						+ '&paramBegDate=' + dt1 + '&paramEndDate=' + dt2 + '&__format=pdf';	
					window.open(url, '_blank');
				}							  
			},
			send_for_inspection:
			{
				nn: 'send_for_inspection',
				tooltip: 'Направить на осмотр',
				text: 'Направить на осмотр',
				iconCls : 'document32',
				disabled: false, 
				menuAlign: 'tr?',
				hidden: (!getRegionNick().inlist(['perm', 'astra', 'penza', 'krym'])),
				handler: function() {
					var grid = Ext.getCmp('amm_WorkPlaceVacCabinetWindow').GridPanel.getGrid(),
						selected_record = grid.getSelectionModel().getSelected();
					if (!selected_record || !selected_record.get('Person_id')) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: Ext.emptyFn,
							icon: Ext.Msg.WARNING,
							msg: 'Запись не выбрана',
							title: ERR_WND_TIT
						});
						return false;
					}
					var dirTypeData = {
						DirType_Code: 12,
						DirType_Name: "На поликлинический прием",
						DirType_id: 16,
					};
					var personData = {
						PersonEvn_id: selected_record.get('PersonEvn_id'),
						Person_Surname: selected_record.get('SurName'),
						Person_Firname: selected_record.get('FirName'),
						Person_Secname: selected_record.get('SecName'),
						Person_Birthday: selected_record.get('BirthDay'),
						Person_id: selected_record.get('Person_id'),
						Server_id: selected_record.get('Server_id'),
						Person_IsDead: null
					}
					var user = sw.Promed.MedStaffFactByUser.last;
					var directionData = {
						ARMType_id: user.ARMType_id,
						Diag_id: null,
						DirType_id: 16,
						DopDispInfoConsent_id: null,
						// EvnDirection_pid: ???
						LpuSection_id: user.LpuSection_id,
						Lpu_sid: getGlobalOptions().lpu_id,
						MedPersonal_id: getGlobalOptions().medpersonal_id,
						MedService_id: this.MedService_id,
						MedStaffFact_id: user.MedStaffFact_id,
						PersonEvn_id: selected_record.get('Person_id'),
						Person_id: selected_record.get('Person_id'),
						Server_id: selected_record.get('Server_id'),
						withDirection: true
					}

					getWnd('swDirectionMasterWindow').show({dirTypeData: dirTypeData, personData: personData, directionData: directionData});
				}.createDelegate(this)
			},
			action_MedicalExaminationOfMigrants: 
			{
				nn: 'action_vacSpr',
				tooltip: 'Медицинское освидетельствование мигрантов',
				text: 'Медицинское освидетельствование мигрантов',
				iconCls : 'pol-dopdisp16',
				menuAlign: 'tr?', 
				hidden: (!isUserGroup('MedOsvMigr') || getRegionNick() != 'buryatiya'),
				handler: function() {
					getWnd('swEvnPLDispMigrSearchWindow').show();
				}							  
			}
		},
			   
	  initComponent: function() {
                        var curWnd = this;
			this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch();
			}
		}.createDelegate(this);
               
                        this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: curWnd,
			labelWidth: 120,
			filter: {
				title: 'Фильтры',
				layout: 'form',
				items: [{
					name: 'SearchFormType',
					value: 'PersonCallCenter',
					xtype: 'hidden'
				}, {
					name: 'AddressStateType_id',
					value: 1,
					xtype: 'hidden'
				}, 
                                    {
					layout: 'form',
                                        id: 'CabVac_FormMedServiceVac',
                                        labelWidth: 120,
					items: [{
                                            fieldLabel: 'Служба',
                                            id: 'CabVac_ComboMedServiceVac',
                                            listWidth: 600,
                                            //tabIndex: TABINDEX_VACPRPFRM + 26,
                                            width: 200,
                                            emptyText: VAC_EMPTY_TEXT,
                                            xtype: 'amm_ComboMedServiceVacExtended',
                                            listeners: {
                                                   'select': function(combo, record, index) {
                                                       Ext.getCmp('amm_WorkPlaceVacCabinetWindow').MedService_id = combo.getValue();
                                                   }.createDelegate(this)
                                                }
                                            }
                                            ]},
                                    {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							fieldLabel: 'Фамилия',
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Search_SurName',
							width: 200,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: 'Имя',
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Search_FirName',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							fieldLabel: 'Отчество',
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Search_SecName',
							width: 120,
							xtype: 'textfieldpmw'
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							fieldLabel: 'ДР',
							format: 'd.m.Y',
							listeners: {
								'keydown': curWnd.onKeyDown
							},
							name: 'Search_BirthDay',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							xtype: 'swdatefield'
						}]
					},
                                        {
						layout: 'form',
						items: [
                                                    {
							style: "padding-left: 20px",
							xtype: 'button',
							id: curWnd.id + 'BtnSearch',
							text: 'Найти',
							iconCls: 'search16',
							handler: function() {
                                                           var counter = 0;
								for (var i in curWnd.FilterPanel.getForm().getValues()){
									if (curWnd.FilterPanel.getForm().getValues()[i] != '') {
									counter++;
									}
								}
								if ( counter <= 0){
									sw.swMsg.alert('Ошибка', 'Не заполнено ни одно поле', function() {});
									return false;
								}
								curWnd.doSearch();
                                                              curWnd.GridPanel.setParam('start', 0);
                                                              }
						}]
					}
                                        , {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: curWnd.id + 'BtnClear',
							text: 'Сброс',
							iconCls: 'reset16',
							handler: function() {
								curWnd.doReset();
                                curWnd.GridPanel.removeAll ();//({clearAll:true});
                                curWnd.doSearch();
             				}
                                    }]
                            }

                            ]
                        }                           
                    ]
                    }
		});
                
              
		this.GridPanel =  new sw.Promed.ViewFrame({
			id: 'Grid4CabVac',
			region: 'center',
			autoExpandColumn: 'autoexpand',
			paging: true,
			totalProperty: 'totalCount',		
			actions:
			[
				{name:'action_add', hidden: true},
				{name:'action_edit', tooltip: 'Исполнить',
                                        handler: function() {
                                           var grid = Ext.getCmp('amm_WorkPlaceVacCabinetWindow').GridPanel.getGrid(),
                                                        selected_record = grid.getSelectionModel().getSelected();  
                                                        selected_record.set_parent_id = 1;
                                                        selected_record.parent_id = 'amm_WorkPlaceVacCabinetWindow';
                                                switch(selected_record.data.type_rec) {		
                                                        case 1://Прививки    
                                                                switch(selected_record.data.StatusType_id) {
                                                                                        case 0://Назначено
                                                                                                                        // Исполнение прививки:
                                                                                        sw.Promed.vac.utils.callVacWindow({
                                                                                                        record: selected_record,
                                                                                                        gridType: 'VacAssigned'
                                                                                                                }, this);
                                                                                          break;

                                                                                          case 1://Исполнено
//                                                        getWnd('amm_ImplVacForm').show(params);             

//									record.Vaccine_id = rowSelected.data.Vaccine_id;
                                                                                                                        sw.Promed.vac.utils.callVacWindow({
                                                                                                                                        record: selected_record,
                                                                                                                                        gridType: 'VacRegistr'
                                                                                                                        }, this);

                                                                                          break;

                                                                        }
                                                break;
                                                case 2://Манту    
//                                                      selected_record.fix_tub_id = selected_record.JournalMantu_id;
                                                          switch(selected_record.data.StatusType_id) {
                                                                                        case 0://Назначено
                                                                                        // Исполнение манту:
                                                                                        sw.Promed.vac.utils.callVacWindow({
                                                                                                        record: selected_record,
                                                                                                        gridType: 'TubAssigned'
                                                                                                                                                                                                                                        }, this);
                                                                                          break;

                                                                                          case 1://Исполнено
//                                                        getWnd('amm_ImplVacForm').show(params);             

//									record.Vaccine_id = rowSelected.data.Vaccine_id;
                                                                                                                        sw.Promed.vac.utils.callVacWindow({
                                                                                                                                        record: selected_record,
                                                                                                                                        gridType: 'TubReaction'
                                                                                                                        }, this);

                                                                                          break;

                                                                        };
                                                                        break
                                                }      

                                        }
							 
					
					},
								{name:'action_view',hidden: true},
				{name:'action_delete', hidden: true},
								//				{name: 'action_refresh', handler: function () {
								//                                        Ext.getCmp('Grid4CabVac').ViewGridPanel.getStore().reload({
								//                     params: {
								//                         start:0
								//                     }
								//                 });
								//                                }},
				{name: 'action_print'}
			],
				   
			autoLoadData: false,
			onRowSelect: function(sm,index,record)
			{
				if(!getRegionNick().inlist(['perm','penza','krym','astra'])) return false;
				var DocumentUcStr_id = record.get('DocumentUcStr_id');
				if(!DocumentUcStr_id){
					this.getAction('action_edit').disable();
				}else{
					this.getAction('action_edit').enable();
				}
			},
			onLoadData: function(sm, index, record) {
				//
			},
			pageSize: PageSize,
				//start:0,                        
				root: 'data',
				cls: 'txtwrap',
			stringfields:
			[
				{name: 'JournalVac_id', type: 'int', header: 'ID', key: true},   	
				{name: 'fix_tub_id', type: 'int', header: 'fix_tub_id', hidden: true}, 	
				{name: 'vacJournalAccount_id', type: 'int', header: 'vacJournalAccount_id',  hidden: true},
				{name: 'DocumentUcStr_id', type: 'int', header: 'DocumentUcStr_id', hidden: true},   
				{name: 'type_rec', type: 'int', header: 'type_rec',  hidden: true},    	
				//{name: 'vacJournalAccount_id', type: 'int', header: 'ID', key: true},        
				{name: 'DatePurpose', type: 'date', header: 'Дата назначения', width: 70},
				{name: 'DateVac', type: 'date', header: 'Дата вакцинации', width: 70},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'Server_id', type: 'int', header: 'Server_id', hidden: true},
				{name: 'PersonEvn_id', type: 'int', header: 'PersonEvn_id', hidden: true},
				{name: 'SurName', type: 'string', header: 'Фамилия', width: 85},
				{name: 'FirName', type: 'string', header: 'Имя', width: 85},
				{name: 'SecName', type: 'string', header: 'Отчество', width: 85},
				{name: 'BirthDay', type: 'date', header: 'Дата рождения', width: 90},
				{name: 'Age', type: 'string', header: 'Возраст', width: 50},
				{name: 'Lpu_Nick', type: 'string', header: 'МО вакцинации', width: 90, hidden: false},
				{name: 'Lpu_atNick', type: 'string', header: 'МО прикрепления'},
				{name: 'uch', type: 'string', header: VAC_TIT_UCH, width: 55},
				{name: 'Infection', type: 'string', header: 'Назначение', width: 150},
				{name: 'Vaccine_Name', type: 'string', header: 'Наименование вакцины', width: 150, id: 'autoexpand'},
				{name: 'Dose', type: 'string', header: 'Доза', width: 50},
				{name: 'WayPlace', type: 'string', header: 'Способ и место введения', width: 150, hidden: false},
				{name: 'StatusSrok_id', type: 'int', header: 'StatusSrok_id',  width: 50, hidden: true},
				{name: 'StatusType_id', type: 'int', header: 'StatusType_id', hidden: true},
				{name: 'MedService_Nick', type: 'string', header: 'Служба', width: 100},
				{name: 'StatusType_Name', type: 'string', header: 'Статус записи', width:100},
			],
			dataUrl: '/?c=VaccineCtrl&m=GetVacAssigned4CabVac',
			title: 'Список  прививок'
		});
                
                 //Интеграция фильтра к Grid
                getGlobalRegistryData  = {};
              
		columnsFilter = ['SurName', 'FirName', 'SecName', 'Infection', 'Vaccine_Name', 'MedService_Nick','StatusType_Name'];
		configParams = {url : '/?c=VaccineCtrlFilterGrid&m=GetVacAssigned4CabVacFilter'} 
                //console.log('ViewFrameVacPresence', Ext.getCmp('amm_PresenceVacForm').getGrid());
               //_addFilterToGrid(Ext.getCmp('Grid4CabVac').getGrid(), columnsFilter, configParams);
                _addFilterToGrid(Ext.getCmp('amm_WorkPlaceVacCabinetWindow').GridPanel, columnsFilter, configParams);  //  Ext.getCmp('Grid4CabVac')
			
		
				this.GridPanel.getGrid().view = new Ext.grid.GridView(
		{

			getRowClass : function (row, index){

				
				var arrCls = [];
				switch (row.get('StatusType_id')){
					case 0:// назначено
						arrCls.push('x-grid-rowbold');
					  break;
					case 1:// исполнено
						arrCls.push('x-grid-rowgreen');
						//arrCls.push('x-grid-rowbold');
					  break;
				}
				
				switch (row.get('StatusSrok_id')){
					case -1:// просрочено
						arrCls.length = 0;
						arrCls.push('x-grid-rowred');
					  break;
					case 0:// норм
					  break;
				}
				return arrCls.join(' ');
			}
		});
                
               
                
    
		   
		sw.Promed.amm_WorkPlaceVacCabinetWindow.superclass.initComponent.apply(this, arguments);
	}
});