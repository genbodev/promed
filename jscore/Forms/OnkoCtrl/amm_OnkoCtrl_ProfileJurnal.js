
/**
 * amm_OnkoCtrl_ProfileJurnal - окно просмотра и редактирования справочника вакцин.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Nigmatullin Tagir (Ufa)
 * @version      17.09.2014
 */

 

sw.Promed.amm_OnkoCtrl_ProfileJurnal = Ext.extend(sw.Promed.BaseForm, {
    title: langs('Журнал анкетирования'),
    border: false,
    width: 725,
    height: 500,
    maximized: true,
    maximizable: true,
    codeRefresh: true,
    closeAction: 'hide',
    objectName: 'amm_OnkoCtrl_ProfileJurnal',
    id: 'amm_OnkoCtrl_ProfileJurnal',
    objectSrc: '/jscore/Forms/OnkoCtrl/amm_OnkoCtrl_ProfileJurnal.js',
    onHide: Ext.emptyFn,
    listeners: {
		'success': function(source, params) {
			var $flag_update = 0;   
		  
			var record =  Ext.getCmp('ViewFrameOnkoProfileJurnal').getGrid().getSelectionModel().getSelected();
			if (params.action == 'delete')
				Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(1);
			else if (params.action == 'edit')   
				$flag_update = 1;
			else if ((params.action == 'add') && (record.data.monitored == -1) && (params.Person_id == record.data.Person_id))  {
				// Проверяем событие и корректность параметров
				var $d0 = new Date 
				var $d = new Date ($d0.getFullYear() - 1, $d0.getMonth(), $d0.getDate())
				if (params.PersonOnkoProfile_DtBeg >= $d) {
					$flag_update = 1;
				}
			}

			if ($flag_update == 1) {
				record.set('PersonOnkoProfile_DtBeg', params.PersonOnkoProfile_DtBeg);
				record.set('PersonOnkoProfile_id', params.New_PersonOnkoProfile_id);
				if (record.MedStaffFact_id != params.MedStaffFact_id) {
					record.set('MedStaffFact_id', params.MedStaffFact_id);
					// Формируем ФИО врача
					var obj = params.MedPersonal_Fio.split (' ');
					var fio = (typeof obj[0] == 'undefined') ? '':obj [0]
					var name = (typeof obj[1] == 'undefined') ? '':obj [1].substring(0, 1)
					var secname = (typeof obj[2] == 'undefined') ? '':obj [2].substring(0, 1)
					fio = fio + ' ' +name + secname

					record.set('MedPersonal_fin', fio);
				}
				record.set('StatusOnkoProfile', langs('Анкета заполнена'));
				record.set('StatusOnkoProfile_id', 2);
				record.set('ProfileResult', params.ProfileResult);

				if ( this.ReportType == 'onko' ) {
					if ( params.ProfileResult.length > 0) {
						record.set('monitored_Name', langs('Необходим Онкоконтроль'));
						record.set('monitored', 2);
					}
					else {
						record.set('monitored_Name', langs('Не нужен Онкоконтроль'));
						record.set('monitored', 1);
					}
				}

				record.commit();
			}
		}
    },
    MOAccess: function()
    {
        var result = false;
        if ((getGlobalOptions().onkoctrlAccessAllLpu == 1) || isAdmin)
            result = true;
            return result;
    },
   
    Profile_ADD: function (params) 
    {      
			params.ReportType = this.ReportType;
            var form = Ext.getCmp('amm_OnkoCtrl_ProfileJurnal');
			if (this.ReportType != 'onko') {
				getWnd('amm_OnkoCtr_ProfileEditWindow').show(params);
			} else {
				var $add = true;
				form.formStore4add.load({
					params: params,
					callback: function() {
						var formStoreCount = form.formStore4add.getCount() > 0;
						//var params = {};

						if (formStoreCount) {
							var formStoreRecord = form.formStore4add.getAt(0);
							if  (formStoreRecord.get('Diag_id')) {
								//alert(formStoreRecord.get('Diag_Code'));
								//form.hide();
								//fields: ['PersonOnkoProfile_id', 'Person_id', 'PersonOnkoProfile_DtBeg', 'Lpu_id', 'Lpu_Nick', 'MedStaffFact_id', 'Diag_setDate', 'Diag_Code', 'Diag_Name', 'Diag_id'],
								var $str = "Пациенту " + formStoreRecord.get('Diag_setDate') + " был поставлен диагноз <br />" + formStoreRecord.get('Diag_Code') + ' - "' + formStoreRecord.get('Diag_Name') + '".<br />'
								Ext.MessageBox.show({
									//id: 'MessageBox1',
									title: langs('Внимание'),
									msg: $str + "Заполнение Анкеты не требуется.",
									width: 500,
									buttons: Ext.Msg.OK
								});
								// return false;
							} else{
								getWnd('amm_OnkoCtr_ProfileEditWindow').show(params);
							}
						}
					}
				});

			}
    },                                                                         
    initComponent: function() {
        
        this.onKeyDown = function(inp, e) {
             
            if (e.getKey() == Ext.EventObject.ENTER) {
                e.stopEvent();
                Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(1);
            } 
            else {
              sw.Promed.vac.utils.consoleLog('onKeyDown');  
              Ext.getCmp('ViewFrameOnkoProfileJurnal').initGridBaseParams();
            };
        };
        
        /*
         * хранилище для доп сведений
         */

        this.formStore4add = new Ext.data.JsonStore({
            fields: ['PersonOnkoProfile_id', 'Person_id', 'PersonOnkoProfile_DtBeg', 'Lpu_id', 'Lpu_Nick', 'MedStaffFact_id', 'Diag_setDate', 'Diag_Code', 'Diag_Name', 'Diag_id'],
            url: '/?c=OnkoCtrl&m=loadOnkoContrProfileFormInfo',
            key: 'PersonOnkoProfile_id',
            root: 'data'
        });
        

        //Формирование глобального объекта Task#18011
        getGlobalRegistryData = {
            Lpu_id: 0,
            RegistryType_id: 0,
            RegistryStatus_id: 0
        }

        var curWnd = this;
        this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
            owner: curWnd,
            labelWidth: 110,
            //autoScroll: true,
            //width: 700,
            id: 'OnkoCtrlJ_FilterPanel',
            filter: {
                title: langs('Фильтры'),
                collapsed: true,
                layout: 'form',
                items: [
                    {
                                layout: 'column',
                                //width: 600,
                                items: [
                    {
                        layout: 'form',
                        labelWidth: 120,
                        items: [{
                                autoLoad: true,
                                fieldLabel: langs('МО анкетирования'),
                                width: 190,
                                listWidth: 300,
                                hiddenName: 'Lpu_id',
                                id: 'Search_LpuListCombo',
                                xtype: 'swlpucombo',//amm_LpuListCombo - этот подгружает без учёта региона
                                allowBlank: false,
                                listeners: {
                                                    'keydown': curWnd.onKeyDown,
                                                    
                                            'select': function(combo) {
                                                Ext.getCmp('Search_uchListCombo').getStore().load({
                                                   params: {
                                                            lpu_id:  Ext.getCmp('Search_LpuListCombo').getValue()
                                                        },
                                                    callback: function() {
                                                        Ext.getCmp('Search_uchListCombo').setValue(-1);
                                                     }.createDelegate(this)
                                                     
                                            });
                                            Ext.getCmp('Search_MedPersonalCombo').reset();
                                            Ext.getCmp('Search_MedPersonalCombo').getStore().load({
                                               params: {
                                                        Lpu_id: Ext.getCmp('Search_LpuListCombo').getValue(),
                                                        LpuBuilding_id: 0,
                                                        MedService_id: 0
                                                                //getGlobalOptions().lpu_id
                                                }
                                           });

                                                }
                            }
                        }]
                    },
                    {
                        layout: 'form',
                        labelWidth: 70,
                        items: [
                            {autoLoad: true,
                                hiddenName: "uch_id",
                                width: 160,
                                xtype: "amm_uchListCombo",
                                id: "Search_uchListCombo",
								editable: false,
								listWidth: 190,
                                fieldLabel: langs('Участок'),
                                listeners: {
                                            'keydown': curWnd.onKeyDown
                                        }
                                //tabIndex : TABINDEX_VACMAINFRM + 6
                            }]
                    },
                       {
                                        layout: 'form',
                                        //                region: 'North', 

                                        labelWidth: 150,
                                        items: [{
//					Height : 70,
                                                width: 170,
                                                name: 'Search_PeriodRange',
                                                //labelStyle: "padding-left",
                                                fieldLabel: 'Период анкетирования', //Дата анкетирования',
                                                plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                                                xtype: "daterangefield",
                                                tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 4,
                                                listeners: {
                                                    'keydown': curWnd.onKeyDown
                                                }
                                            }]
                                    },
                                    
                                    {
                                        layout: 'form',
                                        labelWidth: 80,
                                        items: [
                                            {autoLoad: false,
                //                               hiddenName: "OnkoType_id",
                                                width: 165,
                                                xtype:  'amm_OnkoJournalTypeCombo',
                                                id: 'Search_OnkoJournalTypeCombo',
                                                
                                                //hide: true,
                                                //fieldLabel: "Участок",
                                                listeners: {
                                                            'keydown': curWnd.onKeyDown
                                                        }
                                                    }]
                                            },
                                            
                                            {layout: 'form',
                                    labelWidth: 100,
                                    items: [
                                        {
                                            autoLoad: true,
                                            fieldLabel: langs('Онкоконтроль'),
                                            width: 160,
                                            listWidth: 200,
                                            hiddenName: 'OnkoCtrComment_id',
                                            id: 'Search_OnkoCtrCommentCombo',
                                            xtype: 'amm_OnkoCtrCommentCombo',
                                            listeners: {
                                                    'keydown': curWnd.onKeyDown
                                                }
                                        }]
                                }
                    
                    ]
                },
                    {
                        //**********
                        layout: 'form',
                        items: [{
                                layout: 'column',
                                //width: 600,
                                items: [
                                    {
                                        layout: 'form',
                                        labelWidth: 120,
                                        items: [{
                                                fieldLabel: langs('Фамилия'),
                                                listeners: {
                                                    'keydown': curWnd.onKeyDown,
                                                            'blur' : function () {
                                                                //alert (333);
                                                            }
                                                },
                                                name: 'Search_SurName',
                                                width: 190,
                                                tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 1,
                                                xtype: 'textfieldpmw'
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        labelWidth: 70,
                                        items: [{
                                                fieldLabel: langs('Имя'),
                                                listeners: {
                                                    'keydown': curWnd.onKeyDown
                                                },
                                                name: 'Search_FirName',
                                                width: 160,
                                                tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 2,
                                                xtype: 'textfieldpmw'
                                            }]
                                    },
                                    {
                                        layout: 'form',
                                        labelWidth: 150,
                                        items: [{
                                                fieldLabel: langs('Отчество'),
                                                listeners: {
                                                    'keydown': curWnd.onKeyDown
                                                },
                                                name: 'Search_SecName',
                                                width: 170,
                                                tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 3,
                                                xtype: 'textfieldpmw'
                                            }]
                                    },
                                    {layout: 'form',
                                        labelWidth: 80,
                                        items: [{
                                                fieldLabel: langs('ДР'),
                                                width: 165,        
                                                tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 4,
                                                //allowBlank: false,
                                                xtype: 'swdatefield',
                                                format: 'd.m.Y',
                                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                                id: 'Search_BirthDay',
                                                listeners: {
                                                            'keydown': curWnd.onKeyDown
                                                        }
                                                
                                        }]
                                    },
                                    {
                                        layout: 'form',
                                        labelWidth: 100,
                                        items: [{
											autoLoad: true,
											width: 160,
											xtype: "swpersonsexcombo",
											id: "Search_PersonSexCombo",
											fieldLabel: langs('Пол'),
											listeners: {
												'keydown': curWnd.onKeyDown
											}
										}]
                                    }
                                ]
                            }
                        ]
                    },
                    {
                    layout: 'form',
                    labelWidth: 100,
					items: [{
						layout: 'column',
						//width: 900,
						items: [{
							layout: 'form',
							labelWidth: 120,
							items: [{
								autoLoad: true,
								fieldLabel: langs('Анкетирование'),
								width: 190,
								listWidth: 300,
								hiddenName: 'OnkoCtrlProfile_id',
								id: 'Search_OnkoCtrlProfileCombo',
								xtype: 'amm_OnkoCtrlProfileCombo',
								listeners: {
									'keydown': curWnd.onKeyDown
								}
							}]
						},
						{
							layout: 'form',
							id: 'Search_MedPersonalComboForm',
							labelWidth: 70,
							items: [{
								fieldLabel: langs('Врач'),
								autoLoad: false,
								 displayField: 'Person_Fin',
								name: 'Search_MedPersonalCombo',
								id: 'Search_MedPersonalCombo',
								width: 160,
								listWidth: 300,
								//tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 2,
								xtype: 'amm_ComboVacMedPersonalFull',  //SwMedPersonalCombo'
								listeners: {
									'keydown': curWnd.onKeyDown
								}
							}]
						},
						{
							layout: 'form',
							labelWidth: 150,
							items: [{
								autoLoad: true,
								fieldLabel: langs('Результат'),
								width: 170,
								listWidth: 200,
								hiddenName: 'OnkoQuestions_id',
								id: 'Search_OnkoCtrResultCombo',
								xtype: 'amm_OnkoCtrResultCombo',
								listeners: {
									'keydown': curWnd.onKeyDown
								}
							}]
						},
						{
							layout: 'form',
							labelWidth: 150,
							items: [new Ext.ux.Andrie.Select({
								fieldLabel: langs('Категория BI-RADS'),
								multiSelect: true,
								mode: 'local',
								width: 170,
								listWidth: 200,
								anchor: '100%',
								store: new Ext.db.AdapterStore({
									autoLoad: false,
									dbFile: 'Promed.db',
									fields: [
										{name: 'CategoryBIRADS_Name', mapping: 'CategoryBIRADS_Name'},
										{name: 'CategoryBIRADS_SortCode', mapping: 'CategoryBIRADS_SortCode'},
										{name: 'CategoryBIRADS_SysNick', mapping: 'CategoryBIRADS_SysNick'},
										{name: 'CategoryBIRADS_id', mapping: 'CategoryBIRADS_id'}
									],
									key: 'CategoryBIRADS_id',
									sortInfo: {field: 'CategoryBIRADS_SortCode'},
									tableName: 'CategoryBIRADS'
								}),
								codeField: 'CategoryBIRADS_SortCode',
								displayField: 'CategoryBIRADS_Name',
								valueField: 'CategoryBIRADS_id',
								id: 'Search_CategoryBIRADSCombo',
								hiddenName: 'CategoryBIRADS_id'
							})]
						},
						{
							layout: 'form',
							labelWidth: 100,
							items: [{
								comboSubject: 'AgeNotHindrance',
								fieldLabel: langs('Статус пациента'),
								hiddenName: 'AgeNotHindrance_id',
								id: 'Search_AgeNotHindranceCombo',
								listeners: {
									'keydown': curWnd.onKeyDown
								},
								listWidth: 200,
								width: 170,
								xtype: 'swcommonsprcombo'
							}]
						},
						{
							layout: 'form',
							labelWidth: 80,
							items: [{
								width: 165,
								name: 'Search_BirthDayRange',
								fieldLabel: langs('Период ДР'),
								plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
								xtype: "daterangefield",
								tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 4,
								listeners: {
									'keydown': curWnd.onKeyDown
								}
							}]
						},
						{
							layout: 'form',
							items: [{
								style: "padding-left: 105px",
								xtype: 'button',
								id: curWnd.id + 'BtnSearch',
								text: langs('Найти'),
								iconCls: 'search16',
								tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 5,
								handler: function() {
									var counter = 0;
									var values = this.FilterPanel.getForm().getValues();
									for (var i in values) {
										if (values[i] != '') {
											counter++;
										}
									}
									if (counter <= 0) {
										sw.swMsg.alert(langs('Ошибка'), langs('Не заполнено ни одно поле'), function() {
										});
										return false;
									}
									var filtr = Ext.getCmp('OnkoCtrlJ_FilterPanel');
										if (!filtr.form.isValid()) {
											sw.Promed.vac.utils.msgBoxNoValidForm();
											return false;
										}
									Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(1);
								}.createDelegate(this)
							}]
						},
						{
							layout: 'form',
							items: [{
								style: "padding-left: 25px",
								xtype: 'button',
								id: curWnd.id + 'BtnClear',
								text: langs('Сброс'),
								iconCls: 'reset16',
								tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 6,
								handler: function() {
									//  Очищаем фильтр на панеле фильтров
									Ext.getCmp('OnkoCtrlJ_FilterPanel').form.reset();
									Ext.getCmp('Search_LpuListCombo').setValue(getGlobalOptions().lpu_id);
									Ext.getCmp('Search_uchListCombo').setValue(-1);
									Ext.getCmp('Search_OnkoCtrResultCombo').setValue(-1);

									 //  Очищаем фильтр в гриде
									Ext.getCmp('ViewFrameOnkoProfileJurnal').initGridBaseParams();
									Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridModel.grid.store.baseParams.Filter = '{}';
									//Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().reload();
									Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(2);
									if (Ext.getCmp('ViewFrameOnkoProfileJurnal').FilterSettings != undefined) {
										var obj = Ext.getCmp('ViewFrameOnkoProfileJurnal').FilterSettings;
										 Ext.getCmp('swFilterGridPlugin').setHeader('passive');
										for (var col in obj) {
											Ext.getCmp('ViewFrameOnkoProfileJurnal').FilterSettings[col] = false;
										}
										 Ext.getCmp('swFilterGridPlugin').refreshDataTargetGrid();
										 Ext.getCmp('swFilterGridPlugin').refresh();
									 }
									//  Очищаем сам грид 
									Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(2);
								}
							}]
						}]
					}]
                }]
            }
        }); 

		this.ViewFrameOnkoProfileJurnal = new sw.Promed.ViewFrame({
			id: 'ViewFrameOnkoProfileJurnal',
			dataUrl: '/?c=OnkoCtrl&m=GetOnkoCtrlProfileJurnal',
			toolbar: true,
			setReadOnly: false,
			autoLoadData: false,
			cls: 'txtwrap',
			root: 'data',
			paging: true,
			totalProperty: 'totalCount',
			layout: 'form',
			region: 'center',
			buttonAlign: "right",
			height: 500,
			autowith: true,
			tabIndex: TABINDEX_LISTTASKFORMVAC + 2,
			stringfields: [
				{name: 'id', type: 'int', header: 'id', key: true},
				{name: 'Person_id', type: 'int', header: 'Person_id', hidden: true},
				{name: 'PersonOnkoProfile_id', type: 'int', header: 'PersonOnkoProfile_id', hidden: true},
				{name: 'SurName', type: 'string', header: langs('Фамилия'), width: 90, sortable: false},
				{name: 'FirName', type: 'string', header: langs('Имя'), width: 90, sortable: false},
				{name: 'SecName', type: 'string', header: langs('Отчество'), width: 90, sortable: false},
				{name: 'BirthDay', type: 'date', header: langs('Дата рождения'), width: 90, sortable: false},
				{name: 'sex', type: 'string', header: langs('Пол'), width: 40, sortable: false},
				{name: 'uch', type: 'string', header: langs('Участок'), width: 120, sortable: false},
				{name: 'StatusOnkoProfile_id', type: 'string', header: langs('Анкетирование'), hidden: true, sortable: false},
				{name: 'StatusOnkoProfile', type: 'string', header: langs('Анкетирование'), width: 120, sortable: false},
				{name: 'PersonOnkoProfile_DtBeg', type: 'date', header: langs('Дата'), width: 90, sortable: false},
				{name: 'MedPersonal_fin', type: 'string', header: langs('Врач'), width: 120, sortable: false},
				{name: 'PMUser_Name', type: 'string', header: langs('Пользователь, заполнивший анкету'), width: 220, sortable: false},
				{name: 'MedStaffFact_id', type: 'int', header: 'MedStaffFact_id',hidden: true},
				{name: 'monitored', type: 'int', header: 'monitored', hidden: true},
				{name: 'ProfileResult', type: 'string', header: langs('Результат'), width: 90, sortable: false},
				{name: 'CategoryBIRADS_Name', type: 'string', header: langs('Категория BI-RADS'), width: 120, sortable: false},
				{name: 'AgeNotHindrance_id', type: 'string', header: langs('Статус пациента'), hidden: true, sortable: false},
				{name: 'AgeNotHindrance_Name', type: 'string', header: langs('Статус пациента'), width: 120, sortable: false},
				{name: 'monitored_Name', type: 'string', header: langs('Онкоконтроль'), width: 110, sortable: false},
				{name: 'Lpu_Nick', type: 'string', header: langs('МО прикрепления'), width: 120, sortable: false},
				{name: 'LpuProfile_id', type: 'int', header: 'LpuProfile_id', hidden: true},
				{name: 'Address', type: 'string', header: langs('Адрес'), width: 220, sortable: false},
				{name: 'Person_dead', type: 'int', header: 'Person_dead', hidden: true}
			],
			actions: [{
				name:'action_refresh', 
				handler: function() {
					Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(1);
				}
			}, {
				name: 'action_add', 
				handler: function () {
					getWnd('swPersonSearchWindow').show({
						onSelect: function(person_data) {
							var params = new Object();

							this.hide();

							params.Person_id = person_data.Person_id;
							params.ReportType = curWnd.ReportType;
							params.action = 'add';
							var $person_dead = person_data.Person_dead;

							Ext.getCmp('amm_OnkoCtrl_ProfileJurnal').Profile_ADD(params);
						},
						searchMode: (curWnd.ReportType == 'geriatrics' ? 'geriatrics' : 'all')
					});
				}.createDelegate(this)
			}, {
				name: 'action_view',
				handler: function() {
					// вызываем карту анкетирования
					var record = Ext.getCmp('ViewFrameOnkoProfileJurnal').getGrid().getSelectionModel().getSelected();
					var params = new Object();
					params.Person_id = record.json.Person_id;
					params.ReportType = curWnd.ReportType;
					params.PersonOnkoProfile_id = record.json.PersonOnkoProfile_id;
					params.action = 'view';
					getWnd('amm_OnkoCtr_ProfileEditWindow').show(params);
				}.createDelegate(this)
			}, {
				name:'action_delete',
				handler: function() {
					var url = '/?c=' + sw.Promed.PersonOnkoProfile.getControllerName(curWnd.ReportType) + '&m=deleteOnkoProfile';

					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId === 'yes' ) {
								var obj = Ext.getCmp('ViewFrameOnkoProfileJurnal');
								var rowSelected = obj.getGrid().getSelectionModel().getSelected().data;
								//var record = this.findById('amm_PersonPersonMantu').getGrid().getSelectionModel().getSelected();
								var params = {
									'PersonOnkoProfile_id': rowSelected.PersonOnkoProfile_id
								};

								Ext.Ajax.request({
									url: url,
									method: 'POST',
									params: params,
									success: function(response, opts) {
										sw.Promed.vac.utils.consoleLog('response');
										sw.Promed.vac.utils.consoleLog(response);
										
										var obj = Ext.util.JSON.decode(response.responseText);
										console.log(obj);
										if (obj.rows[0].Error_Msg != '') {
											var $err_msg = obj.rows[0].Error_Msg
											Ext.Msg.alert(langs('Внимание'), $err_msg + '!');
											return false;
										}
										if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
											var $params = new Object();
											$params.action = 'delete';
											Ext.getCmp('amm_OnkoCtrl_ProfileJurnal').fireEvent('success', 'amm_OnkoCtrl_ProfileJurnal', $params);//, 'TubReaction', { } );
										} 
									}
								});
							}
						}.createDelegate(this),
						icon: Ext.MessageBox.QUESTION,
						msg: langs('Удалить анкету?'),
						title: langs('Удаление анкеты')
					});

				}.createDelegate(this)
			}, {
				name: 'action_save', hidden: true
			}, {
				name: 'action_delete', hidden: true
			}, {
				name: 'action_edit',
				handler: function() {
					// вызываем карту анкетирования
					var record = new Object();
					record = Ext.getCmp('ViewFrameOnkoProfileJurnal').getGrid().getSelectionModel().getSelected();
					var params = new Object();
					params.parent_id = 'amm_OnkoCtrl_ProfileJurnal';
					params.Person_id = record.data.Person_id;
					params.ReportType = curWnd.ReportType;
					//alert(params.Person_id);
					params.PersonOnkoProfile_id = record.data.PersonOnkoProfile_id;
					sw.Promed.vac.utils.consoleLog('record');
					sw.Promed.vac.utils.consoleLog(record.data);
					sw.Promed.vac.utils.consoleLog('PersonOnkoProfile_id');
					sw.Promed.vac.utils.consoleLog(params.PersonOnkoProfile_id);
					var $monitored = record.data.monitored;
					var $person_dead = record.data.Person_dead;
					if (Ext.getCmp('ViewFrameOnkoProfileJurnal').getAction('action_edit').isHidden() == 1)
						params.action = 'view';
					else if ($person_dead == 1) {  //  Если умер - то просмотр
						params.action = 'view';
					}
					else if ($monitored == -1) {
						params.action = 'add';
					}
					else if (($monitored == 1) || ($monitored == 2) || (params.ReportType != 'onko')) {
						params.action = 'edit';
					};
					if ( params.action == 'add') {
						Ext.getCmp('amm_OnkoCtrl_ProfileJurnal').Profile_ADD(params);
					} else {
						getWnd('amm_OnkoCtr_ProfileEditWindow').show(params);
					}
				}.createDelegate(this)
			}],
			updateContextMenu: function() {
				var obj = Ext.getCmp('ViewFrameOnkoProfileJurnal');
				var rowSelected = obj.getGrid().getSelectionModel().getSelected().data;
				console.log(rowSelected.MedStaffFact_id + " = " + getGlobalOptions().CurMedStaffFact_id);
				sw.Promed.vac.utils.consoleLog(rowSelected.StatusOnkoProfile_id);
				var actionObj = new Object();

				actionObj['action_edit'] = {isHidden: 1};
				if (rowSelected.Person_dead == 1) {
					actionObj['action_edit'].isHidden = 1;
				} 
				else if ((isLpuAdmin ()) && (rowSelected.LpuProfile_id == getGlobalOptions().lpu_id)) {
					actionObj['action_edit'].isHidden = 0;
				}
				else if ((rowSelected.MedStaffFact_id == getGlobalOptions().CurMedStaffFact_id)
						|| (isAdmin) || (rowSelected.MedStaffFact_id == "")) {
					actionObj['action_edit'].isHidden = 0;
				}
				else {
					actionObj['action_edit'].isHidden = 1;
				}
				
				if (curWnd.ReportType.inlist(['birads', 'recist'])) {
					actionObj['action_edit'].isHidden = 1;
				}

				obj.getAction('action_edit').setHidden(actionObj['action_edit'].isHidden);
			},
			initGridBaseParams: function() {
				var params = new Object();
				params.Lpu_id = Ext.getCmp('Search_LpuListCombo').getValue();
				params.limit = 100;
				//getGlobalOptions().lpu_id;
				//params.start = 0;
				var CurVal = Ext.getCmp('OnkoCtrlJ_FilterPanel').getForm().getValues();


				if (!Ext.isEmpty(CurVal.Search_SurName)) {
					params.SurName = CurVal.Search_SurName;
				}
				if (!Ext.isEmpty(CurVal.Search_FirName)) {
					params.FirName = CurVal.Search_FirName;
				}
				if (!Ext.isEmpty(CurVal.Search_SecName)) {
					params.SecName = CurVal.Search_SecName;
				}
				if (!Ext.isEmpty(CurVal.Search_BirthDayRange)) {
					params.BirthDayRange = CurVal.Search_BirthDayRange;
				}
				if (!Ext.isEmpty(CurVal.Search_BirthDay)) {
					params.BirthDay = CurVal.Search_BirthDay;
				}
				if (!Ext.isEmpty(CurVal.Search_PeriodRange)) {
					params.PeriodRange = CurVal.Search_PeriodRange;
				}
				
				sw.Promed.vac.utils.consoleLog('OnkoType_id');
				sw.Promed.vac.utils.consoleLog(Ext.getCmp('Search_OnkoCtrlProfileCombo').getValue());
				 
				params.OnkoType_id = Ext.getCmp('Search_OnkoJournalTypeCombo').getValue()
			   
				if( Ext.getCmp('Search_OnkoCtrlProfileCombo').getValue() != -1)
					params.StatusOnkoProfile_id = Ext.getCmp('Search_OnkoCtrlProfileCombo').getValue()
				
				if( Ext.getCmp('Search_OnkoCtrResultCombo').getValue() != -1)
					params.OnkoQuestions_id = Ext.getCmp('Search_OnkoCtrResultCombo').getValue()
				
				if( Ext.getCmp('Search_OnkoCtrCommentCombo').getValue() != -1)
					params.Monitored = Ext.getCmp('Search_OnkoCtrCommentCombo').getValue() 
				
				/*if( Ext.getCmp('Search_uchListCombo').getValue() == 0)
					params.Uch = '0';
				else if( Ext.getCmp('Search_uchListCombo').getValue() != -1)
					params.Uch = Ext.getCmp('Search_uchListCombo').lastSelectionText*/// необъяснимо
				var uch = Number(Ext.getCmp('Search_uchListCombo').getValue());
				if(!isNaN(uch) && uch >= 0){
					params.Uch = uch;
				}
				
				
				if( Ext.getCmp('Search_PersonSexCombo').getValue() != -1)
					params.Sex_id = Ext.getCmp('Search_PersonSexCombo').getValue()
				
				if( Ext.getCmp('Search_MedPersonalCombo').getValue() != 0)
					params.Doctor = Ext.getCmp('Search_MedPersonalCombo').getValue()

				if ( !Ext.isEmpty(Ext.getCmp('Search_AgeNotHindranceCombo').getValue()) ) {
					params.AgeNotHindrance_id = Ext.getCmp('Search_AgeNotHindranceCombo').getValue();
				}

				if ( !Ext.isEmpty(Ext.getCmp('Search_CategoryBIRADSCombo').getValue()) ) {
					params.CategoryBIRADS_id = Ext.getCmp('Search_CategoryBIRADSCombo').getValue();
				}

				params.Filter = Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridModel.grid.store.baseParams.Filter

				Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().baseParams = params;
			},                
			initGrid: function($load) {
				var params = new Object();
				var $getBottomToolbar = Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getBottomToolbar();

				params.Lpu_id = Ext.getCmp('Search_LpuListCombo').getValue();
				params.limit = 100;
				//getGlobalOptions().lpu_id;
				//params.start = 0;
				var CurVal = Ext.getCmp('OnkoCtrlJ_FilterPanel').getForm().getValues();


				if(!Ext.isEmpty(CurVal.Search_SurName)){
					params.SurName = CurVal.Search_SurName;
				}
				if(!Ext.isEmpty(CurVal.Search_FirName)){
					params.FirName = CurVal.Search_FirName;
				}
				if(!Ext.isEmpty(CurVal.Search_SecName)){
					params.SecName = CurVal.Search_SecName;
				}
				if(!Ext.isEmpty(CurVal.Search_BirthDayRange)){
					params.BirthDayRange = CurVal.Search_BirthDayRange;
				}
				if(!Ext.isEmpty(CurVal.Search_BirthDay)){
					params.BirthDay = CurVal.Search_BirthDay;
				}
				if(!Ext.isEmpty(CurVal.Search_PeriodRange)){
					params.PeriodRange = CurVal.Search_PeriodRange;
				}

				sw.Promed.vac.utils.consoleLog('OnkoType_id');
				sw.Promed.vac.utils.consoleLog(Ext.getCmp('Search_OnkoCtrlProfileCombo').getValue());
				 
				params.OnkoType_id = Ext.getCmp('Search_OnkoJournalTypeCombo').getValue()
			   
				if( Ext.getCmp('Search_OnkoCtrlProfileCombo').getValue() != -1)
					params.StatusOnkoProfile_id = Ext.getCmp('Search_OnkoCtrlProfileCombo').getValue()
				
				if( Ext.getCmp('Search_OnkoCtrResultCombo').getValue() != -1)
					params.OnkoQuestions_id = Ext.getCmp('Search_OnkoCtrResultCombo').getValue()
				
				if( Ext.getCmp('Search_OnkoCtrCommentCombo').getValue() != -1)
					params.Monitored = Ext.getCmp('Search_OnkoCtrCommentCombo').getValue()

				/*if( Ext.getCmp('Search_uchListCombo').getValue() == 0)
					params.Uch = '0';
				else if( Ext.getCmp('Search_uchListCombo').getValue() != -1)
					params.Uch = Ext.getCmp('Search_uchListCombo').lastSelectionText*/// необъяснимо
				var uch = Number(Ext.getCmp('Search_uchListCombo').getValue());
				if(!isNaN(uch) && uch >= 0){
					params.Uch = uch;
				}

				if( Ext.getCmp('Search_PersonSexCombo').getValue() != -1)
					params.Sex_id = Ext.getCmp('Search_PersonSexCombo').getValue()
				
				if( Ext.getCmp('Search_MedPersonalCombo').getValue() != 0)
					params.Doctor = Ext.getCmp('Search_MedPersonalCombo').getValue()
				
				if ( !Ext.isEmpty(Ext.getCmp('Search_AgeNotHindranceCombo').getValue()) ) {
					params.AgeNotHindrance_id = Ext.getCmp('Search_AgeNotHindranceCombo').getValue();
				}

				if ( !Ext.isEmpty(Ext.getCmp('Search_CategoryBIRADSCombo').getValue()) ) {
					params.CategoryBIRADS_id = Ext.getCmp('Search_CategoryBIRADSCombo').getValue();
				}

				params.Filter = Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridModel.grid.store.baseParams.Filter

				Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().baseParams = params;

				console.log ('$load = ' + $load);
				if ($load == 1) {
					Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().reload({
						callback: function(){
							Ext.getCmp('ViewFrameOnkoProfileJurnal').updateContextMenu();
						}
					});
				} 
				else  if ($load == 2) {
					params.Empty = 1;  //  Вернуть пустой грид

					Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().reload({
						callback: function() {
							Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().baseParams.Empty = 0;
						}
					});
				}
			}
		});
            
        this.ViewFrameOnkoProfileJurnal.getGrid().on(
			'headerclick',
			function(grid,index) {
				 Ext.getCmp('ViewFrameOnkoProfileJurnal').initGridBaseParams();
				 //return;
			}
        );

		this.ViewFrameOnkoProfileJurnal.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass: function(row, index)
			{
				var cls = '';
				if ( curWnd.ReportType == 'onko' ) {
					// Анкета заполнена
					//if (row.get('StatusOnkoProfile_id') == 1) {
					if (row.get('StatusOnkoProfile_id') == 2) {    
						cls = 'x-grid-rowbold ';
						//  Необходим онкоконтроль
						if ( row.get('monitored') == 2 )
							cls = cls + 'x-grid-rowred ';
						else
							cls = cls + 'x-grid-rowgreen ';
					}

					if (row.get('Person_dead') == 1) {
						cls = cls + 'x-grid-rowgray';
					}
				}

				return cls;
			}
		});


		// Интеграция фильтра к Grid
        columnsFilter = ['SurName', 'FirName', 'SecName', 'StatusOnkoProfile', 'monitored_Name', 'uch', 'ProfileResult', 'MedPersonal_fin', 'Lpu_Nick', 'sex'];
        configParams = {url: '/?c=OnkoCtrlFilterGrid&m=GetOnkoCtrlProfileJurnalFilter'}
        _addFilterToGrid(this.ViewFrameOnkoProfileJurnal, columnsFilter, configParams);

        Ext.apply(this, {
            lbodyBorder: true,
            layout: "border",
            cls: 'tg-label',
            buttons: [
                {
                    text: langs('Сбросить фильтр'), //BTN_FRMRESET,
                    iconCls: 'resetsearch16',
                    id: 'OnkoCtrlJ_ResetSearch',
                    hidden: true,
                    tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 7,
                    handler: function(button, event) {
                        Ext.getCmp('ViewFrameOnkoProfileJurnal').initGridBaseParams();
                        Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridModel.grid.store.baseParams.Filter = '{}';
                        //Ext.getCmp('ViewFrameOnkoProfileJurnal').ViewGridPanel.getStore().reload();
                        Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(2);
                        if (Ext.getCmp('ViewFrameOnkoProfileJurnal').FilterSettings != undefined) {
                            var obj = Ext.getCmp('ViewFrameOnkoProfileJurnal').FilterSettings;
                             Ext.getCmp('swFilterGridPlugin').setHeader('passive');
                            for (var col in obj) {
                                Ext.getCmp('ViewFrameOnkoProfileJurnal').FilterSettings[col] = false;
                            }
                             Ext.getCmp('swFilterGridPlugin').refreshDataTargetGrid();
                             Ext.getCmp('swFilterGridPlugin').refresh();
                         }
                     }
                },
                {
                    text: '-'
                },
                HelpButton(this, TABINDEX_ONKOCTRLPROFILEJOURNAL + 8),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'close16',
                    id: 'OnkoCtrlJ_CancelButton',
                    onTabAction: function() {
                        Ext.getCmp('OnkoCtrlJ_ResetSearch').focus(true, 50);
                    }.createDelegate(this),
                    tabIndex: TABINDEX_ONKOCTRLPROFILEJOURNAL + 9,
                    text: langs('Закрыть')
                }
            ],
            items: [
                this.FilterPanel,
                this.ViewFrameOnkoProfileJurnal
            ]
        });

        sw.Promed.amm_OnkoCtrl_ProfileJurnal.superclass.initComponent.apply(this, arguments);
    },
    show: function(record) {
        sw.Promed.amm_OnkoCtrl_ProfileJurnal.superclass.show.apply(this, arguments);
        this.formParams = record;
		
		this.ReportType = record.ReportType || 'onko';


		this.ViewFrameOnkoProfileJurnal.setDataUrl('/?c=' + sw.Promed.PersonOnkoProfile.getControllerName(this.ReportType) + '&m=GetOnkoCtrlProfileJurnal');
        
        var flag = Ext.getCmp('amm_OnkoCtrl_ProfileJurnal').MOAccess();
        
        var params = new Object();
        if (!flag)
            params.Lpu_id = getGlobalOptions().lpu_id;
        Ext.getCmp('Search_LpuListCombo').store.load({
            params:params,
            callback: function() {
                Ext.getCmp('Search_LpuListCombo').setValue(getGlobalOptions().lpu_id);
                Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(2);
                Ext.getCmp('Search_uchListCombo').getStore().load({
                   params: {
                            lpu_id: getGlobalOptions().lpu_id
                    },
                    callback: function() {
                                   Ext.getCmp('Search_uchListCombo').setValue(-1);
                                }.createDelegate(this)
       });
            }
       })
        Ext.getCmp('ViewFrameOnkoProfileJurnal').initGrid(0);
        Ext.getCmp('OnkoCtrlJ_ResetSearch').focus(true, 50);
        //load(this.formParams.Lpu_id)


         Ext.getCmp('ViewFrameOnkoProfileJurnal').getGrid().on(
			'cellclick',
                         Ext.getCmp('ViewFrameOnkoProfileJurnal').updateContextMenu
		);
         Ext.getCmp('ViewFrameOnkoProfileJurnal').getGrid().on(
                'cellcontextmenu',
                 Ext.getCmp('ViewFrameOnkoProfileJurnal').updateContextMenu
        );
            
		Ext.getCmp('Search_OnkoCtrResultCombo').getStore().load({
			callback: function() {
				Ext.getCmp('Search_OnkoCtrResultCombo').setValue(-1);
			}.createDelegate(this)
		});

        Ext.getCmp('Search_MedPersonalCombo').getStore().load({
           params: {
				lpu_id: Ext.getCmp('Search_LpuListCombo').getValue(),
				LpuBuilding_id: 0,
				MedService_id: 0
            }
		});
       
		Ext.getCmp('Search_OnkoCtrlProfileCombo').setValue(2);
		Ext.getCmp('Search_OnkoCtrCommentCombo').setValue(-1);
		Ext.getCmp('Search_OnkoJournalTypeCombo').setValue(1);
		Ext.getCmp('Search_OnkoJournalTypeCombo').setDisabled(true);
		Ext.getCmp('OnkoCtrlJ_FilterPanel').fieldSet.expand();
		
		var grid = this.ViewFrameOnkoProfileJurnal;
		var StatusOnkoProfile = grid.getGrid().getColumnModel().findColumnIndex('StatusOnkoProfile');
		var ProfileResult = grid.getGrid().getColumnModel().findColumnIndex('ProfileResult');
		var monitored_Name = grid.getGrid().getColumnModel().findColumnIndex('monitored_Name');
		var AgeNotHindrance = grid.getGrid().getColumnModel().findColumnIndex('AgeNotHindrance_Name');
		var CategoryBIRADS_Name = grid.getGrid().getColumnModel().findColumnIndex('CategoryBIRADS_Name');
		var PMUser_Name = grid.getGrid().getColumnModel().findColumnIndex('PMUser_Name');

		Ext.getCmp('Search_OnkoJournalTypeCombo').hideContainer();
		Ext.getCmp('Search_OnkoCtrCommentCombo').hideContainer();
		Ext.getCmp('Search_OnkoCtrlProfileCombo').hideContainer();
		Ext.getCmp('Search_OnkoCtrResultCombo').hideContainer();
		Ext.getCmp('Search_AgeNotHindranceCombo').hideContainer();
		Ext.getCmp('Search_CategoryBIRADSCombo').hideContainer();
		grid.getGrid().getColumnModel().setHidden(StatusOnkoProfile, true);
		grid.getGrid().getColumnModel().setHidden(ProfileResult, true);
		grid.getGrid().getColumnModel().setHidden(monitored_Name, true);
		grid.getGrid().getColumnModel().setHidden(AgeNotHindrance, true);
		grid.getGrid().getColumnModel().setHidden(PMUser_Name, true);

		if ( this.ReportType == 'onko' ) {
			Ext.getCmp('Search_OnkoJournalTypeCombo').showContainer();
			Ext.getCmp('Search_OnkoCtrCommentCombo').showContainer();
			Ext.getCmp('Search_OnkoCtrlProfileCombo').showContainer();
			Ext.getCmp('Search_OnkoCtrResultCombo').showContainer();
			grid.getGrid().getColumnModel().setHidden(StatusOnkoProfile, false);
			grid.getGrid().getColumnModel().setHidden(ProfileResult, false);
			grid.getGrid().getColumnModel().setHidden(monitored_Name, false);
			grid.getGrid().getColumnModel().setHidden(PMUser_Name, false);
			Ext.getCmp('Search_MedPersonalComboForm').getEl().setStyle('margin', '0');
		}

		if ( this.ReportType == 'geriatrics' ) {
			Ext.getCmp('Search_AgeNotHindranceCombo').showContainer();
			grid.getGrid().getColumnModel().setHidden(AgeNotHindrance, false);
			Ext.getCmp('Search_MedPersonalComboForm').getEl().setStyle('margin', '0 20px 0 50px');
		}

		if ( this.ReportType == 'palliat' ) {
			Ext.getCmp('Search_MedPersonalComboForm').getEl().setStyle('margin', '0 20px 0 50px');
		}

		if ( this.ReportType == 'birads' ) {
			Ext.getCmp('Search_CategoryBIRADSCombo').showContainer();
			grid.getAction('action_add').hide();
			grid.getAction('action_edit').hide();
			grid.getGrid().getColumnModel().setHidden(CategoryBIRADS_Name, false);
		} else {
			grid.getAction('action_add').show();
			grid.getAction('action_edit').setHidden(this.ReportType == 'previzit');
			grid.getGrid().getColumnModel().setHidden(CategoryBIRADS_Name, true);
		}
		if ( this.ReportType == 'recist' ) {
			grid.getAction('action_add').hide();
			grid.getAction('action_edit').hide();
		}
    }
});

