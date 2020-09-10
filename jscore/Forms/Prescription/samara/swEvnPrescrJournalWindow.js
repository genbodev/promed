/**
* swEvnPrescrJournalWindow - журнал назначений.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-19.10.2011
* @comment      Префикс для id компонентов EPRJF (EvnPrescrJournalForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrJournalWindow = Ext.extend(sw.Promed.swWorkPlaceWindow,/*sw.Promed.BaseForm,*/ {
	codeRefresh: true,
	objectName: 'swEvnPrescrJournalWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrJournalWindow.js',

	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	execEvnPrescr: function() {
		var grid = this.GridPanel;
		var selected_record = grid.getSelectionModel().getSelected();
		
		if ( !selected_record || !selected_record.get('EvnPrescr_id') || !selected_record.get('EvnPrescr_IsExec') || !selected_record.get('PrescriptionStatusType_id') ) {
			return false;
		}
		var conf = {
			ownerWindow: this
            ,btnId: 'EvnPrescrJournalWindowExecEvnPrescr'
            ,allowChangeTime: false
            //,parentEvnClass_SysNick: 'EvnSection'
			,EvnPrescr_setDate: selected_record.get('EvnPrescr_setDate')
			,Person_id: selected_record.get('Person_id')
			,PersonEvn_id: selected_record.get('PersonEvn_id')
			,Server_id: selected_record.get('Server_id')
		};
		conf.EvnPrescr_id = selected_record.get('EvnPrescrDay_id');
		conf.PrescriptionType_id = selected_record.get('PrescriptionType_id');
		conf.EvnPrescr_IsExec = selected_record.get('EvnPrescr_IsExec');
		conf.PrescriptionStatusType_id = selected_record.get('PrescriptionStatusType_id');
		conf.onExecSuccess = function(){
			selected_record.set('EvnPrescr_IsExec', 2);
			selected_record.set('IsExec_Name', 'Да');
			selected_record.commit();
			grid.getSelectionModel().selectRow(grid.getStore().indexOf(selected_record));
		};
		conf.onExecCancel = function(){
			grid.getSelectionModel().selectRow(grid.getStore().indexOf(selected_record));
		};
		//for 10
		conf.ObservTimeType_id = selected_record.get('ObservTimeType_id');
		//for 6,7,11,12
		conf.EvnPrescr_rid  = selected_record.get('EvnPrescr_rid');
		conf.EvnPrescr_pid = selected_record.get('EvnPrescr_pid');
		conf.Diag_id = selected_record.get('Diag_id');
		conf.UslugaId_List = selected_record.get('UslugaId_List');
		conf.TableUsluga_id = selected_record.get('TableUsluga_id');
		conf.PrescriptionType_Code = selected_record.get('PrescriptionType_Code');
		conf.Person_Birthday = selected_record.get('Person_Birthday');
		var person_fio = selected_record.get('Person_FIO');
		var person_fio_arr = person_fio.split(' ');
		conf.Person_Surname = person_fio_arr[0] || '';
		conf.Person_Firname = person_fio_arr[1] || '';
		conf.Person_Secname = person_fio_arr[2] || '';
		//log(conf);
		conf.EvnDirection_id = selected_record.get('EvnDirection_id');
		sw.Promed.EvnPrescr.exec(conf);
        return true;
	},
    /**
     * Отменить выполнение назначения
     * @return {Boolean}
     */
    unExecEvnPrescr: function() {
        var grid = this.GridPanel;
        var rec = grid.getSelectionModel().getSelected();
        if ( !rec || !rec.get('EvnPrescrDay_id')) {
            return false;
        }
        var conf = {
            ownerWindow: this
            ,EvnPrescrDay_id: rec.get('EvnPrescrDay_id')
            ,PrescriptionType_id: rec.get('PrescriptionType_id')
            ,EvnDirection_id: rec.get('EvnDirection_id')
            ,hasExec: (2==rec.get('EvnPrescr_IsExec'))
            ,PrescriptionStatusType_id: rec.get('PrescriptionStatusType_id')
            ,onSuccess: function() {
                rec.set('EvnPrescr_IsExec', 1);
                rec.set('IsExec_Name', 'Нет');
                rec.commit();
                grid.getSelectionModel().selectRow(grid.getStore().indexOf(rec));
            }
            ,onCancel: function(){
                grid.getSelectionModel().selectRow(grid.getStore().indexOf(rec));
            }
        };
        sw.Promed.EvnPrescr.unExec(conf);
        return true;
    },
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	height: 550,
	id: 'EvnPrescrJournalWindow',
	initComponent: function() {
		
		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: this,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: function(e) {
					this.doSearch();
				},
				scope: this,
				stopEvent: true
			}],
			filter: {
				title: 'Фильтр',
				collapsed: false,
				layout: 'form',
				border: false,
				defaults: {
					border: false
				},
				items: [
					{
						layout: 'column',
						items: [
							{
								layout: 'form',
								defaults: {
									anchor: '100%'
								},
								width: 300,
								labelWidth: 70,
								bodyStyle: 'padding-right: 5px;',
								items: [
									{
										fieldLabel: 'Фамилия',
										name: 'Person_SurName',
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: 'Имя',
										name: 'Person_FirName',
										xtype: 'textfieldpmw'
									}, {
										fieldLabel: 'Отчество',
										name: 'Person_SecName',
										xtype: 'textfieldpmw'
									}
								]
							}, {
								bodyStyle: 'padding-right: 5px;',
								width: 300,
								labelWidth: 90,
								layout: 'form',
								items: [
									{
										xtype: 'swdatefield',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'Person_BirthDay',
										fieldLabel: 'Д/Р'
									}, {
										anchor: '100%',
										listWidth: 400,
										allowBlank: true,
										xtype: 'swmedpersonalcombo'
									}, {
										comboSubject: 'YesNo',
										fieldLabel: 'Выполнено',
										hiddenName: 'EvnPrescr_IsExec',
										width: 100,
										xtype: 'swcommonsprcombo'
									}
								]
							}, {
								layout: 'form',
								labelWidth: 130,
								items: [
									{
										autoLoad: false,
										comboSubject: 'PrescriptionType',
										fieldLabel: 'Тип назначения',
										hiddenName: 'PrescriptionType_id',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												combo.fireEvent('select', combo, combo.getStore().getById(newValue));
											}.createDelegate(this),
											'select': function(combo, record) {
												var base_form = this.FilterPanel.getForm();

												if ( !record || !record.get('PrescriptionType_Code') || !record.get('PrescriptionType_Code').toString().inlist([ '3', '4', '5', '6', '7' ]) ) {
													// base_form.findField('Drug_id').clearValue();
													// base_form.findField('Drug_id').setContainerVisible(false);
													// base_form.findField('DrugPrepFas_id').clearValue();
													// base_form.findField('DrugPrepFas_id').setContainerVisible(false);
													base_form.findField('LpuSectionProfile_id').clearValue();
													base_form.findField('LpuSectionProfile_id').setContainerVisible(false);
													base_form.findField('Usluga_id').clearValue();
													base_form.findField('Usluga_id').setContainerVisible(false);
													base_form.findField('PrescriptionIntroType_id').clearValue();
													base_form.findField('PrescriptionIntroType_id').setContainerVisible(false);
													this.doLayout();
													return false;
												}
												
												base_form.findField('LpuSectionProfile_id').clearValue();
												base_form.findField('LpuSectionProfile_id').setContainerVisible(false);
												base_form.findField('Usluga_id').clearValue();
												base_form.findField('Usluga_id').setContainerVisible(false);
												base_form.findField('PrescriptionIntroType_id').clearValue();
												base_form.findField('PrescriptionIntroType_id').setContainerVisible(false);

												switch ( Number(record.get('PrescriptionType_Code')) ) {
													case 3:
													case 6:
													case 7:
														//base_form.findField('Usluga_id').setContainerVisible(true);

														// base_form.findField('Drug_id').clearValue();
														// base_form.findField('Drug_id').setContainerVisible(false);
														// base_form.findField('DrugPrepFas_id').clearValue();
														// base_form.findField('DrugPrepFas_id').setContainerVisible(false);
													break;

													case 4:
														base_form.findField('LpuSectionProfile_id').setContainerVisible(true);

														// base_form.findField('Drug_id').clearValue();
														// base_form.findField('Drug_id').setContainerVisible(false);
														// base_form.findField('DrugPrepFas_id').clearValue();
														// base_form.findField('DrugPrepFas_id').setContainerVisible(false);
													break;

													case 5:
														base_form.findField('PrescriptionIntroType_id').setContainerVisible(true);
														// base_form.findField('Drug_id').setContainerVisible(true);
														// base_form.findField('DrugPrepFas_id').setContainerVisible(true);
													break;
												}
												this.doLayout();
											}.createDelegate(this),
											'render': function(combo) {
												combo.getStore().load({
													params: {
														where: 'where PrescriptionType_id in (1, 2, 5, 6, 7, 10, 11, 12, 13)'
													}
												});
											}.createDelegate(this)
										},
										typeCode: 'int',
										width: 250,
										xtype: 'swcommonsprcombo'
									}, {
										//to-do для 6,7,11,12 используется UslugaComplex
										allowBlank: true,
										fieldLabel: 'Услуга',
										hiddenName: 'Usluga_id',
										listWidth: 600,
										// tabIndex: TABINDEX_EUCOMEF + 9,
										width: 350,
										xtype: 'swuslugacombo'
									}, {
										//to-do 4 временно закрыт
										allowBlank: true,
										comboSubject: 'LpuSectionProfile',
										fieldLabel: 'Профиль',
										hiddenName: 'LpuSectionProfile_id',
										listWidth: 600,
										// tabIndex: TABINDEX_EPREF + 1,
										width: 300,
										xtype: 'swcommonsprcombo'
									}, {
										hiddenName: 'PrescriptionIntroType_id',
										width: 300,
										fieldLabel: 'Метод введения',
										comboSubject: 'PrescriptionIntroType',
										xtype: 'swcommonsprcombo'
									}, {
										xtype: 'swlpusectionwardglobalcombo'
									}
								]
							}
						]
					}, {
						layout: 'column',
						style: 'padding: 3px;',
						items: [
							{
								layout: 'form',
								items: [
									{
										handler: function() {
											this.doSearch();
										}.createDelegate(this),
										xtype: 'button',
										iconCls: 'search16',
										text: BTN_FRMSEARCH
									}
								]
							}, {
								layout: 'form',
								style: 'margin-left: 5px;',
								items: [
									{
										handler: function() {
											this.doReset();
											this.doSearch();
										}.createDelegate(this),
										xtype: 'button',
										iconCls: 'resetsearch16',
										text: 'Сброс'
									}
								]
							}
						]
					}
				]
			}
		});
	
		this.reader = new Ext.data.JsonReader({
			id: 'EvnPrescr_key'
		}, [
			{ name: 'EvnPrescr_key' },
            { name: 'EvnPrescr_id' },
            { name: 'EvnPrescrDay_id' },
			{ name: 'EvnDirection_id' },
			{ name: 'Person_id' },
			{ name: 'PersonEvn_id' },
			{ name: 'Server_id' },
			{ name: 'PrescriptionType_id' },
			{ name: 'PrescriptionType_Code' },
			{ name: 'PrescriptionStatusType_id' },
			{ name: 'ObservTimeType_id' },
			{ name: 'EvnPrescr_IsCito' },
			{ name: 'EvnPrescr_IsExec' },
			{ name: 'EvnPrescr_setDate', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'EvnPrescr_setTime' },
			{ name: 'IsExec_Name' },
			{ name: 'PrescriptionType_Name' },
			{ name: 'Person_FIO' },
			{ name: 'Person_Birthday', type: 'date', dateFormat: 'd.m.Y' },
			{ name: 'LpuSectionWard_id' },
			{ name: 'Sex_id' },
			{ name: 'LpuSectionWard_Name' },
			{ name: 'EvnPrescr_Name' },
			{ name: 'PrescriptionStatusType_Name' },
			{ name: 'EvnSection_id' },
			{ name: 'EvnPrescr_pid' },
			{ name: 'EvnPrescr_rid' },
			{ name: 'Diag_id' },
			{ name: 'UslugaId_List', type: 'string' },
			{ name: 'EvnPrescrProcTimetable_id' },
			{ name: 'EvnPrescrTreatTimetable_id' },
			{ name: 'pmUser_insName' },
			{ name: 'MedPersonal_SignFIO' },
			{ name: 'EvnCourse_id' },
			{ name: 'EvnPrescr_insDT', type: 'date', dateFormat: 'd.m.Y H:i' }
		]);
		
		this.gridStore = new Ext.data.GroupingStore({
			autoLoad: false,
			groupField: 'EvnPrescr_setDate',
			reader: this.reader,
			sortInfo: {
				field: 'EvnPrescr_setTime',
				direction: 'ASC'
			},
			url: '/?c=EvnPrescr&m=loadEvnPrescrJournalGrid',
			listeners: {
				beforeload: function() {
					var toolbar = this.GridPanel.getTopToolbar();
					toolbar.items.items[3].disable();
					toolbar.items.items[4].disable();
					toolbar.items.items[5].disable();
				}.createDelegate(this)
			}
		});

		this.GridPanel = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand_prescr',
			autoExpandMin: 300,
			clearStore: function() {
				if ( this.getEl() ) {
/*
					if ( this.getTopToolbar().items.last() ) {
						this.getTopToolbar().items.last().el.innerHTML = '0 / 0';
					}
*/
					this.getStore().removeAll();
				}
			},
			columns: [{
				dataIndex: 'EvnPrescr_setDate',
				header: "Дата",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: true,
				sortable: true,
				width: 70
			}, {
				dataIndex: 'EvnPrescr_setTime',
				header: "Время",
				resizable: true,
				sortable: true,
				width: 45
			}, {
				dataIndex: 'IsExec_Name',
				header: "Выполнено",
				resizable: true,
				sortable: true,
				width: 45
            }, {
                dataIndex: 'pmUser_insName',
                header: "Назначил врач",
                resizable: true,
                sortable: true,
                width: 200
            }/*, {
                dataIndex: 'MedPersonal_SignFIO',
                header: "Назначил врач",
                resizable: true,
                sortable: true,
                width: 200
			}, {
				dataIndex: 'pmUser_insName',
				header: "Пользователь, создавший назначение",
				resizable: true,
				sortable: true,
				width: 200
			}*/, {
				dataIndex: 'Person_FIO',
				header: "Пациент",
				resizable: true,
				sortable: true,
				width: 250
			}, {
				dataIndex: 'Person_Birthday',
				header: "Дата рождения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y'),
				resizable: true,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'LpuSectionWard_id',
				hidden: true
			}, {
				dataIndex: 'Sex_id',
				hidden: true
			}, {
				dataIndex: 'EvnSection_id',
				hidden: true
			}, {
				dataIndex: 'EvnDirection_id',
				hidden: true
			}, {
				dataIndex: 'EvnPrescr_pid',
				hidden: true
			}, {
				dataIndex: 'EvnPrescr_rid',
				hidden: true
			}, {
				dataIndex: 'Diag_id',
				hidden: true
			}, {
				dataIndex: 'UslugaId_List',
				hidden: true
			}, {
				dataIndex: 'EvnPrescrTreatTimetable_id',
				hidden: true
			}, {
				dataIndex: 'EvnPrescrProcTimetable_id',
				hidden: true
			}, {
				dataIndex: 'PrescriptionType_Code',
				hidden: true
			}, {
				dataIndex: 'PrescriptionType_id',
				hidden: true
			}, {
				dataIndex: 'LpuSectionWard_Name',
				header: "Палата",
				resizable: true,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'PrescriptionType_Name',
				header: "Тип назначения",
				resizable: true,
				sortable: true,
				width: 100
			}, {
				dataIndex: 'EvnPrescr_Name',
				header: "Назначение",
				id: 'autoexpand_prescr',
				resizable: true,
				sortable: true
			/* }, {
				dataIndex: 'PrescriptionStatusType_Name',
				header: "Статус",
				resizable: true,
				sortable: true,
				width: 100 */
			}, {
				dataIndex: 'EvnPrescr_insDT',
				header: "Дата, время формирования назначения",
				renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'),
				resizable: true,
				sortable: true,
				width: 100
			}],
			focus: function () {
				if ( this.getStore().getCount() > 0 ) {
					this.getView().focusRow(0);
					this.getSelectionModel().selectFirstRow();
				}
			},
			frame: false,
			hasPersonData: function() {
				return this.getStore().fields.containsKey('Person_id') && this.getStore().fields.containsKey('Server_id');
			},
			layout: 'fit',
			loadMask: true,
			loadStore: function(params) {
				if ( !this.params ) {
					this.params = null;
				}

				if ( params ) {
					this.params = params;
				}

				this.clearStore();
				this.getStore().load({
					params: this.params
				});
			},
			region: 'center',
			sm: new Ext.grid.RowSelectionModel({
				listeners: {
					'rowselect': function(sm, rowIdx, record) {
						var EvnPrescr_id;
						var EvnPrescr_IsExec;
						var PrescriptionStatusType_id;
						var selected_record = sm.getSelected();
						var toolbar = this.GridPanel.getTopToolbar();

						if ( selected_record ) {
							EvnPrescr_id = selected_record.get('EvnPrescr_id');
							EvnPrescr_IsExec = selected_record.get('EvnPrescr_IsExec');
							PrescriptionStatusType_id = selected_record.get('PrescriptionStatusType_id');
							toolbar.items.items[3].enable();
							toolbar.items.items[4].enable();							
							toolbar.items.items[4].menu.items.each(function(item,i,l) {
								var equalityWard = item.LpuSectionWard_id == selected_record.get('LpuSectionWard_id');
								item.setVisible( (item.Sex_id == selected_record.get('Sex_id') && !equalityWard) ||	( item.Sex_id == null && !equalityWard ) );
							});
							toolbar.items.items[5].enable();
						}

						toolbar.items.items[0].disable();
						toolbar.items.items[1].disable();

						if ( EvnPrescr_IsExec == 2 ) {
							toolbar.items.items[1].enable();
						} else {
							toolbar.items.items[0].enable();
						}

                        if ( selected_record && String(selected_record.get('PrescriptionType_Code')).inlist(['1', '2']) ) {
                            toolbar.items.items[0].disable();
                            toolbar.items.items[1].disable();
                        }
					}.createDelegate(this),
					'rowdeselect': function(sm, rowIdx, record) {
						//
					}.createDelegate(this)
				},
				singleSelect: true
			}),
			store: this.gridStore,
			stripeRows: true,
			tbar: new sw.Promed.Toolbar({
				buttons: [{
					handler: function() {
						this.execEvnPrescr();
					}.createDelegate(this),
					iconCls: 'exec16',
                    id: 'EvnPrescrJournalWindowExecEvnPrescr',
                    text: 'Выполнить',
					tooltip: 'Отметить назначение как выполненое'
				}, {
					handler: function() {
						this.unExecEvnPrescr();
					}.createDelegate(this),
					iconCls: 'delete16',
					text: 'Отменить выполнение',
					tooltip: 'Отменить выполнение назначения'
				}, {
					xtype: 'tbseparator'
				}, {
					iconCls: 'open16',
					text: 'Открыть ЭМК',
					tooltip: 'Открыть электронную медицинскую карту пациента',
					handler: this.openEmk.createDelegate(this)
				}, {
					iconCls: 'update-ward16',
					text: 'Перевод в палату',
					tooltip: 'Перевести пациента в другую палату',
					menu: new Ext.menu.Menu()
				}, {
					text: 'Просмотр',
					tooltip: 'Смотреть назначение',
					iconCls: 'view16',
					handler: this.openEvnPrescr.createDelegate(this, ['view'])
				}, {
					xtype: 'tbseparator'
				}, {
					handler: function() {
						Ext.ux.GridPrinter.print(this.GridPanel, { tableHeaderText: 'Журнал назначений', pageTitle: 'Печать журнала назначений' });
					}.createDelegate(this),
					iconCls: 'print16',
					text: 'Печать журнала',
					tooltip: 'Печать журнала назначений'
				}, {
					handler: function() { // ipavelpetrov 
						var grid = this.GridPanel,
							selected_record = grid.getSelectionModel().getSelected();
						
	                    getWnd('swEvnPrescrPlanRestyleWindow').show({
	                    	action: 'view',
	                    	Person_id :  selected_record.get('Person_id'),
	                    	userMedStaffFact: this.userMedStaffFact,
							formParams: {
								"EvnPrescr_rid": selected_record.get('EvnPrescr_rid'),  
								"EvnPrescr_pid":  selected_record.get('EvnPrescr_pid') 
							}
						});
	                    
					}.createDelegate(this),
					iconCls: 'view16',
					text: 'Лист назначений',
					tooltip: 'Лист назначений'
				}, {
					id: 'EPJW_isclosefilter',
					text: 'Случай закончен: <b>Нет</b>',
					menu: new Ext.menu.Menu({
						items: [
							new Ext.Action({
								text: 'Все',
								handler: function() {
									Ext.getCmp('EPJW_isclosefilter').setText('Случай закончен: <b>Все</b>');
									this.isClose = null;
									this.doSearch();
								}.createDelegate(this)
							}),
							new Ext.Action({
								text: 'Нет',
								handler: function() {
									Ext.getCmp('EPJW_isclosefilter').setText('Случай закончен: <b>Нет</b>');
									this.isClose = 1;
									this.doSearch();
								}.createDelegate(this)
							}),
							new Ext.Action({
								text: 'Да',
								handler: function() {
									Ext.getCmp('EPJW_isclosefilter').setText('Случай закончен: <b>Да</b>');
									this.isClose = 2;
									this.doSearch();
								}.createDelegate(this)
							})
						]
					})
				}]
			}),
			view: new Ext.grid.GroupingView( {
				forceFit: false,
                enableGroupingMenu:false,
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length.inlist([2, 3, 4]) ? "записи" : (values.rs.length == 1 ? "запись" : "записей")]})'
			}),
			listeners: {
				rowdblclick: function() {
					this.GridPanel.getTopToolbar().items.items[5].handler();
				}.createDelegate(this)
			}
		});
		/*
		// Конфиги акшенов для левой панели
		var configActions = 
		{
			action_JourNotice: {nn:'action_JourNotice', text:'Журнал уведомлений', tooltip: 'Открыть журнал уведомлений', iconCls : 'notice32', handler: function() { getWnd('swMessagesViewWindow').show(); }.createDelegate(this)}
		}
		// Копируем все действия для создания панели кнопок
		this.PanelActions = {};
		for(var key in configActions)
		{
			var iconCls = configActions[key].iconCls;//.replace(/16/g, '32');
			var z = Ext.applyIf({cls: 'x-btn-large', iconCls: iconCls, text: ''}, configActions[key]);
			this.PanelActions[key] = new Ext.Action(z);
		}
		var actions_list = ['action_JourNotice'];
		// Создание кнопок для панели
		this.BtnActions = new Array();
		var i = 0;
		for(var key in this.PanelActions)
		{
			if (key.inlist(actions_list))
			{
				this.BtnActions.push(new Ext.Button(this.PanelActions[key]));
				i++;
			}
		}
		this.leftMenu = new Ext.Panel(
		{
			region: 'west',
			border: false,
			layout:'form',
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			items: this.BtnActions
		});
		
		this.leftPanel =
		{
			id: 'EPRJF_leftPanel',
			animCollapse: false,
			bodyStyle: 'padding: 5px',
			width: 60,
			minSize: 60,
			maxSize: 120,
			region: 'west',
			floatable: false,
			collapsible: true,
			layoutConfig:
			{
				titleCollapse: true,
				animate: true,
				activeOnTop: false
			},
			listeners:
			{
				collapse: function()
				{
					return;
				},
				resize: function (p,nW, nH, oW, oH)
				{
					return;
				}
			},
			border: true,
			title: ' ',
			split: true,
			items: [this.leftMenu]
		};
		*/
		this.LeftPanel = new sw.Promed.BaseWorkPlaceButtonsPanel({
			collapsible: true,
			titleCollapse: true,
			floatable: false,
			animCollapse: false,
			region: 'west',
			enableDefaultActions: true, 
			panelActions: this.buttonPanelActions
		});
		
		Ext.apply(this, {
			buttons: [/*{
			    handler: function() {
					this.doSearch();
				}.createDelegate(this),
			    iconCls: 'search16',
			    text: BTN_FRMSEARCH
			}, {
			    handler: function() {
					this.doReset();
					this.doSearch();
			    }.createDelegate(this),
			    iconCls: 'resetsearch16',
			    text: 'Сброс'
			}, */{
				text: '-'
			},
            {
                text: BTN_FRMHELP,
                iconCls: 'help16',
                handler: function() {
                    ShowHelp('Журнал назначений');
                }
            },
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					//
				}.createDelegate(this),
				// tabIndex: TABINDEX_ESTEF + 36,
				text: BTN_FRMCLOSE
			}],
			items: [
				this.leftPanel,
				this.FilterPanel,
				this./*PrescriptionGrid*/GridPanel
			],
			layout: 'border'
		});

		sw.Promed.swEvnPrescrJournalWindow.superclass.initComponent.apply(this, arguments);
	},
	isClose: 1,
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrJournalWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'border',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			//
		},
		'restore': function(win) {
			//
		}
	},
	
	createListLpuSectionWard: function() {
		sw.Promed.LpuSectionWard.createListLpuSectionWard({
			LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id,
			date: (new Date()).format('d.m.Y'),
			id: 'EPJW_WardMenu',
			getParams: function(){
				var params = {},
					sel_record = this.GridPanel.getSelectionModel().getSelected();
					
					if( sel_record ) {
						params.LpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
						params.ignore_sex = false;
						params.EvnSection_id = sel_record.get('EvnSection_id');
						params.Sex_id = sel_record.get('WardSex_id');
						params.Person_id = sel_record.get('Person_id');
						params.LpuSectionWardCur_id = sel_record.get('LpuSectionWard_id');
					}
				return params;
			}.createDelegate(this),
			callback: function(menu){
				this.GridPanel.getTopToolbar().items.items[4].menu = menu;
				var sm = this.GridPanel.getSelectionModel(),
					sel_record = sm.getSelected();
				if( sel_record ) {
					sm.fireEvent('rowselect', sm, this.GridPanel.getStore().indexOf(sel_record), sel_record); 
				}
			}.createDelegate(this),
			onSuccess: function(params) {
				var menu = this.GridPanel.getTopToolbar().items.items[4].menu,
					sel_record = this.GridPanel.getSelectionModel().getSelected();
				menu.items.each(function(item) {
					if( item.LpuSectionWard_id == params.LpuSectionWard_id ) {
						sel_record.set('LpuSectionWard_id', item.LpuSectionWard_id);
						sel_record.set('LpuSectionWard_Name', item.text);
					}
				});
				sel_record.commit();
				//также обновляем меню палат LpuSectionWard_id_10
				this.createListLpuSectionWard();
			}.createDelegate(this)
		});
	},
	
	openEmk: function() {
		var grid = this.GridPanel,
			record = grid.getSelectionModel().getSelected();
			
		if ( !record ) return false;
		
		getWnd('swPersonEmkWindow').show({
			Person_id: record.get('Person_id'),
			Server_id: record.get('Server_id'),
			PersonEvn_id: record.get('PersonEvn_id'),
			userMedStaffFact: this.userMedStaffFact,
			//TimetableGraf_id: record.get('TimetableGraf_id'),
			mode: 'workplace',
			//isMyOwnRecord: isMyOwnRecord,
			ARMType: 'common'//'stacnurse'
		});
	},
	
	doSearch: function(mode)
	{
		var params = this.FilterPanel.getForm().getValues();
		params.MedService_id = this.MedService_id;
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
		
		var begDate = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y'),
			endDate = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		
		params.EvnPrescr_setDate_Range = begDate + ' - ' + endDate;

		this.GridPanel.removeAll();
		params.limit = 100;
		params.start = 0;
		params.isClose = this.isClose;

		this.GridPanel.loadStore(params);
	},
    doReset: function()
    {
        var pt_combo = this.FilterPanel.getForm().findField('PrescriptionType_id');

        this.FilterPanel.getForm().reset();
        //var date = new Date();
        //base_form.findField('EvnPrescr_setDate_Range').setValue(Ext.util.Format.date(date, 'd.m.Y') + ' - ' + Ext.util.Format.date(date, 'd.m.Y'));
        pt_combo.setValue(null);
        pt_combo.fireEvent('change', pt_combo, pt_combo.getValue());
        this.GridPanel.params = {};
        this.GridPanel.getStore().baseParams = {};
        this.GridPanel.removeAll();
        this.GridPanel.clearStore();
        this.GridPanel.getTopToolbar().items.items[0].disable();
        this.GridPanel.getTopToolbar().items.items[1].disable();
        this.GridPanel.getTopToolbar().items.items[3].disable();
    },
	
	openEvnPrescr: function( action ) {
		var record = this.GridPanel.getSelectionModel().getSelected();
        if( !record ) return false;
        if( action != 'view' ) return false;
        var conf = {
            parentEvnClass_SysNick: 'EvnSection',
            userMedStaffFact: this.userMedStaffFact,
            action: action,
            PrescriptionType_id: record.get('PrescriptionType_id'),
            PrescriptionType_Code: record.get('PrescriptionType_Code'),
            data: {
                Evn_pid: record.get('EvnPrescr_pid'),
                EvnPrescr_id: record.get('EvnPrescr_id'),
                EvnCourse_id: record.get('EvnCourse_id')
            },
            callbackEditWindow: function() {
                //
            },
            onHideEditWindow: function() {
                //
            }
        };

        sw.Promed.EvnPrescr.openEvnCourseEditWindow(conf);
        //sw.Promed.EvnPrescr.openEditWindow(conf);
        return true;
	},

	loadMask: null,
	maximizable: true,
    maximized: false,
    gridPanelAutoLoad: false,
	minHeight: 550,
	minWidth: 750,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	buttonPanelActions: {
		action_JourNotice: {
			nn:'action_JourNotice',
			text:'Журнал уведомлений',
			tooltip: 'Открыть журнал уведомлений',
			iconCls : 'notice32',
			handler: function() { getWnd('swMessagesViewWindow').show(); }.createDelegate(this)
		}
		
	},
	
	show: function() {
		sw.Promed.swEvnPrescrJournalWindow.superclass.show.apply(this, arguments);
		this.restore();
		this.center();
		this.maximize();

		if(!arguments[0]) {
			arguments = [{}];
		}
		if(!arguments[0].userMedStaffFact) {
			arguments[0].userMedStaffFact = {};
		}
		
		if ( arguments[0].userMedStaffFact.LpuSection_Name ) {
			this.setTitle(WND_PRESCR_REG + ' - ' + arguments[0].userMedStaffFact.LpuSection_Name);
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}
		else {
			this.setTitle(WND_PRESCR_REG);
			this.userMedStaffFact = null;
		}
		
		var base_form = this.FilterPanel.getForm();
		
		this.FilterPanel.fieldSet.expand();
		base_form.findField('LpuSectionWard_id').setContainerVisible(false);
		
		/*
		* Форма может работать в нескольких режимах:
		* 1. как АРМ постовой медсестры
		* 2. как АРМ мед. сестры процедурного кабинета (стац. и пол-ка) - как служба уровеня отделения
		* 3. как журнал назначений для врача
		*/
		if(arguments[0].userMedStaffFact.ARMType) {
			switch(arguments[0].userMedStaffFact.ARMType) {
				case 'stacnurse':
					this.formMode = 'workplace';
					//this.setTitle('АРМ постовой медсестры - ' + this.userMedStaffFact.LpuSection_Name);
					sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
					this.LeftPanel.setVisible(true);
					//base_form.findField('LpuSectionProfile_id').setValue(this.userMedStaffFact.LpuSectionProfile_id);
					this.GridPanel.getTopToolbar().items.items[3].setVisible(true);
					this.GridPanel.getTopToolbar().items.items[4].setVisible(true);
					
					base_form.findField('LpuSectionWard_id').setContainerVisible(true);
					base_form.findField('LpuSectionWard_id').getStore().load({
						params: {
							LpuSection_id: sw.Promed.MedStaffFactByUser.current.LpuSection_id
						}
					});
					this.createListLpuSectionWard();
					
					break;
				default://'common','stac',null
					this.formMode = 'journal';
					this.LeftPanel.setVisible(false);
					this.GridPanel.getTopToolbar().items.items[3].setVisible(false);
					this.GridPanel.getTopToolbar().items.items[4].setVisible(false);
					break;
			}
		} else if( arguments[0].MedService_id && arguments[0].MedService_id > 0 ) {
			sw.Promed.MedStaffFactByUser.setMenuTitle(this, {
				MedService_id: arguments[0].MedService_id,
				MedService_Name: arguments[0].MedService_Name,
				MedPersonal_id: getGlobalOptions().CurMedPersonal_id
			});
			this.LeftPanel.setVisible(true);
			this.GridPanel.getTopToolbar().items.items[4].setVisible(false);
		}
		this.MedService_id = arguments[0].userMedStaffFact.MedService_id;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

        this.doReset();

		base_form.findField('MedPersonal_id').getStore().load();

        this.syncSize();
        this.doLayout();

        this.doSearch();
	},
	title: WND_PRESCR_REG,
	width: 850
});