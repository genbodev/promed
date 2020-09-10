/**
* amm_ImplVacForm - окно просмотра формы Исполнение прививки
*
* PromedWeb - The New Generation of Medical Statistic Software
*
* @copyright    Copyright (c) 2012 
* @author       
* @version      16.05.2012
* @comment      
*/

var formStoreRecord;

sw.Promed.amm_ImplVacForm = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_ImplVacForm',
	title: "",
        titleBase: "Исполнение прививки",
	width: 800,
	height: 500,
	maximizable: false,  
	codeRefresh: true,
	modal:true,
//---нету---  
	layout: 'border',
	border: false,
	closeAction: 'hide',
//  planTmpId: null,
//---есть---  
//	layout: 'fit',
	objectName: 'amm_ImplVacForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_ImplVacForm.js',
//	shim: false,
	onHide: Ext.emptyFn,
	listeners: {
		'show': function(c) {
			this.setTitle('');
		},
		'readyFrmType': function(frmType, actType) {
			var pars = {
				modeType: actType,
				formType: frmType
			};
			this.setTitle(this.titleExt.init(pars).getTitle());
		}
	},
	initCombo:  function (parent, Params) {
		/* Функция инициализации комбобоксов
		* Параметр parent:
		*  'quikImpl_LpuCombo' - изменение ЛПУ
		*  'quikImpl_LpuBuildingCombo' - изменение подразделения
		*  'quikImpl_MedPersonalCombo' - изменение службы
		*/
		if (parent == 'impl_LpuCombo') {
			//  Выводим список подразделений
			Ext.getCmp('impl_LpuBuildingCombo').getStore().load ({
				params: Params,
				callback: function() {
					if(Params.LpuBuilding_id != null){
						Ext.getCmp('impl_LpuBuildingCombo').setValue(Params.LpuBuilding_id);
					}else{
						Ext.getCmp('impl_LpuBuildingCombo').reset();
					}
				}
			});
		};
		if ((parent == 'impl_LpuCombo') || (parent == 'impl_LpuBuildingCombo')) {
			//  Выводим список служб
			Ext.getCmp('impl_ComboMedServiceVac').getStore().load ({
				params: Params,
				callback: function() {
					if (Params.Medservice_id != null) {
						Ext.getCmp('impl_ComboMedServiceVac').setValue(Params.Medservice_id);
					}else{
						Ext.getCmp('impl_ComboMedServiceVac').reset();
					}
				}
			});
		};
		//  Выводим список сотрудников
		Params['form'] = 'amm_ImplVacForm';

		if(getRegionNick().inlist(['perm','penza','krym','astra'])){
			//Params['isMidMedPersonalOnly'] = 1;
			Ext.getCmp('impl__MedPersonalCombo').setFieldLabel('Медсестра');
		}
		
		Ext.getCmp('impl__MedPersonalCombo').getStore().load ({
			params: Params,
			callback: function(c,a,i) {
				if (Params.MedPers_id_impl != null && Ext.getCmp('impl__MedPersonalCombo').findRecord('MedPersonal_id', Params.MedPers_id_impl)) {
					Ext.getCmp('impl__MedPersonalCombo').setValue(Params.MedPers_id_impl);
				}else{
					Ext.getCmp('impl__MedPersonalCombo').reset();
				}
			}
		});
	},
//---  
	initComponent: function() {
		var params = new Object();
		var form = this;
		//объект для именования формы:
		this.titleExt = sw.Promed.vac.utils.getFormTitleObj();
		//объект для контроля дат формы:
		this.validateVacImplementDate = sw.Promed.vac.utils.getValidateObj({
			formId: 'vacImplEditForm',
			fieldName: 'vacImplementDate'
		});
		
		/*
		* хранилище для доп сведений
		*/
		this.formStore = new Ext.data.JsonStore({
			// fields: ['vacJournalAccount_id', 'VaccineWay_id', 'Doza', 'StatusType_id',
				fields: ['vacJournalAccount_id', 'VaccineWay_name', 'Doza', 'StatusType_id', 
				'MedPers_id_user', 'MedPers_id_impl', 'MedPers_id_purp', 'Date_impl',
				'ReactLocalDesc', 'ReactGeneralDesc'
				,'Date_Purpose','NAME_TYPE_VAC','BirthDay','vac_name','Vaccine_id','Person_id'
				,'Seria', 'vacPeriod', 'Vaccine_AgeBegin','Vaccine_AgeEnd', 'Lpu_id', 'Medservice_id', 'vacOther','LpuBuilding_id',
				'EvnVizitPL_id', 'DocumentUcStr_id'
			],
			url: '/?c=VaccineCtrl&m=loadImplFormInfo',
			key: 'vacJournalAccount_id',
			root: 'data'
		});
		
		this.PersonInfoPanel  = new sw.Promed.PersonInfoPanel({
			titleCollapse: true,
			floatable: false,
			collapsible: true,
			collapsed: true,
			border: true,
			plugins: [ Ext.ux.PanelCollapsedTitle ],
			region: 'north'
		});

		Ext.apply(this, {
			formParams: null,
			buttons: [
			{ text: 'Сохранить',
				iconCls: 'save16',
				id: 'ImplVacForm_ButtonSave',
				tabIndex: TABINDEX_VACIMPFRM + 20,
				handler: function(b) {
					/*
					if(!this.activityButtonSave_DependsOnExistenceMedicines()){
						Ext.Msg.alert('Внимание', 'Отсутствует запись об списании медикамента!');
						return false;
					}
					*/
					b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
					//return false;
					var vacImplForm = Ext.getCmp('vacImplEditForm');
					if (!vacImplForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						b.setDisabled(false);
						return false;
					}					
					var formParam = '';
					formParam = vacImplForm.form.findField('vacImplementDate').getValue();
					if (formParam != undefined && formParam != '') {
						formParam = formParam.format('d.m.Y');
					}
					var vacDate = formParam;
					
					var objPars = new Object();
					
					var idx = vacImplForm.form.findField('VaccineSeria_id').getStore().findBy(function(rec) { return rec.get('VacPresence_id') == vacImplForm.form.findField('VaccineSeria_id').getValue(); });
					var seriaRecord = vacImplForm.form.findField('VaccineSeria_id').getStore().getAt(idx);
					if (typeof(seriaRecord) == 'object') {
					//            this.formParams.vac_seria = seriaRecord.get('Seria');
					//            this.formParams.vac_period = seriaRecord.get('Period');
						objPars.vac_seria = seriaRecord.get('Seria');
						objPars.vac_period = seriaRecord.get('Period');
					} else {
					// this.formParams.vac_seria = vacImplForm.form.findField('VaccineSeria_id').getRawValue();
						objPars.vac_seria = '*' + vacImplForm.form.findField('VaccineSeria_id').getRawValue();
						//objPars.vac_period = '01.01.1900';
						//new Date(1900, 0, 1);
					}
					
					objPars.vac_jaccount_id = Ext.getCmp('amm_ImplVacForm').formParams.vac_jaccount_id;
					//objPars.med_staff_impl_id = vacImplForm.form.findField('MedStaffImpl_id').getValue();
					objPars.med_staff_impl_id = Ext.getCmp('impl__MedPersonalCombo').getValue();
                                        
					objPars.date_vac = vacDate;
					objPars.react_local_desc = vacImplForm.form.findField('vacReactLocalDesc').getValue();
					objPars.react_general_desc = vacImplForm.form.findField('vacReactGeneralDesc').getValue();
					objPars.vaccine_way_place_id = vacImplForm.form.findField('VaccineWay_id').getValue();
					//objPars.VaccineWay_id = vacImplForm.form.findField('VaccineWay_id').getValue();
					objPars.Lpu_id = vacImplForm.form.findField('Lpu_id').getValue();
					objPars.medservice_id = Ext.getCmp('impl_ComboMedServiceVac').getValue();
                                                
					if (objPars.Lpu_id == '') {
						objPars.Lpu_id = -1;
					}
					if (objPars.med_staff_impl_id == '') {
						objPars.med_staff_impl_id = -1;
					}
					//alert (objPars.med_staff_impl_id);
					//alert (objPars.Lpu_id);

					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение ..."});
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=VaccineCtrl&m=savePrivivImplement',
						method: 'POST',
						params: objPars,
						success: function(response, opts) {
							loadMask.hide();
							sw.Promed.vac.utils.consoleLog(response);

							if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
								//alert(this.formParams.parent_id);		
								Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'amm_ImplVacForm', {keys: [Ext.getCmp('amm_ImplVacForm').formParams.vac_jaccount_id]});
							}
							form.hide();
						}.createDelegate(this),
						failure: function(response, opts) {
							loadMask.hide();
						}
					});
				}.createDelegate(this)
			}, {
				text: 'Изменить вакцину',
				iconCls: 'edit16',
				id: 'ImplVacForm_ButtonEdit',
				//hidden: true,
				tabIndex: TABINDEX_VACIMPFRM + 20,
				handler: function(b) {
					b.setDisabled(true);//деактивируем кнопку (исключен повторных нажатий)
					Ext.getCmp('amm_ImplVacForm').hide();
					// var record = formStoreRecord;
					//record.vacJournalAccount_id = formStoreRecord.data.vacJournalAccount_id;
					//record.Person_id = 1;
					//record.data.planTmpId = -2;
					//alert(formStoreRecord.get('Person_id'));
					var Record = formStoreRecord;
					//Record.data.Scheme_num = 2
					//alert (Ext.getCmp('vacImplInfo').getValue());
					Record.data.Name = Ext.getCmp('vacImplInfo').getValue();
					sw.Promed.vac.utils.consoleLog( Record.data);
					sw.Promed.vac.utils.callVacWindow({
						record: Record,
						gridType: 'VacEdit'
					}, this);
				}
			},
			{
				text : '-'
			}, 
				//  HelpButton(this, TABINDEX_VACIMPFRM + 21),
				{text: BTN_FRMHELP,
				iconCls: 'help16',
				tabIndex : TABINDEX_VACMAINFRM + 21,
				handler: function(button, event){
					ShowHelp(this.ownerCt.titleBase);
					//  ShowHelp(Ext.getCmp('journalsVaccine').titleBase);
				}
			},
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				tabIndex: TABINDEX_VACIMPFRM + 21,
				onTabAction: function () {
					this.vacEditForm.form.findField('vacImplementDate').focus();
					//	Ext.getCmp('vacImplEditForm').form.findField('vacImplementDate').focus();
				}.createDelegate(this),
				text: '<u>З</u>акрыть'
			}],
			
			items: [
				this.PersonInfoPanel,
				this.vacEditForm = new Ext.form.FormPanel({
					autoScroll: true,
					region: 'center',
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'vacImplEditForm',
					name: 'vacEditForm',
					labelAlign: 'right',
					//autohight: true,
					//height: 300,
					labelWidth: 100,
					layout: 'form',
					items: [
						//  this.PersonInfoPanel,
						{
							height:5,
							border: false
						},
						{
							
						border: false,
						layout: 'column',
						defaults: {
							// xtype: 'form',
							columnWidth:0.5,
							bodyBorder: false,
							//  labelWidth: 100,
							//  labelAlign: 'left',
							anchor: '100%'
						},
						items: [{//столбец 1
							layout: 'form',
							items: [{
								fieldLabel: 'Тип прививки',
								tabIndex: TABINDEX_VACIMPFRM + 1,
								xtype: 'textarea',
								name: 'vacImplInfo',
								id: 'vacImplInfo',
								grow: true,
								growMax: 60,
								growMin: 20,
								width: 260,
								disabled: true,
								readOnly: true
							}, {
								fieldLabel: 'Вакцина',
								tabIndex: TABINDEX_VACIMPFRM + 2,
								name: 'vacImplName',
								width: 260,
								disabled: true,
								readOnly: true,
								xtype: 'textfield'
							}, {
								fieldLabel: 'Доза',
								tabIndex: TABINDEX_VACIMPFRM + 3,
								name: 'vacImplDoze',
								width: 260,
								disabled: true,
								readOnly: true,
								xtype: 'textfield'
							},

							sw.Promed.vac.objects.comboVaccineSeria({
								id: 'impl_comboVaccineSeria',
								allowBlank: true,
								tabIndex: TABINDEX_VACIMPFRM + 13
								,hiddenName: 'VaccineSeria_id'
							}),

							sw.Promed.vac.objects.comboVaccineWay({
								id: 'impl_comboVaccineWay',
								allowBlank: true,
								tabIndex: TABINDEX_VACIMPFRM + 4,
								hiddenName: 'VaccineWay_id'
							}),

							//              }, {
							//                fieldLabel: 'Способ и место введения',
							//                tabIndex: TABINDEX_VACIMPFRM + 4,
							//                name: 'VaccineWay_name',
							//                width: 260,
							//								disabled: true,
							//                readOnly: true,
							//                xtype: 'textfield'

							{
								fieldLabel: 'Врач (назначил)',
								tabIndex: TABINDEX_VACIMPFRM + 5,
								hiddenName: 'MedStaffFact_id',
								//id: 'impl_purpMedPersonalCombo',
								// parentElementId: 'impl_LpuSectionCombo',
								listWidth: 600,
								width: 260,
								// readOnly: true,
								// editable: false,
								disabled: true,
								xtype: 'swmedstafffactglobalcombo'
							}]
						
						}, {//столбец 2
							layout: 'form',
							items: [{

								autoHeight: true,
								// layout: 'form',
								style: 'margin: 5px; padding: 5px;',
								title: 'Исполнение прививки',
								xtype: 'fieldset',
								items: [{
										// minValue: '01.04.2013',
										// maxValue: '21.04.2013',
										fieldLabel: 'Дата исполнения',
											tabIndex: TABINDEX_VACIMPFRM + 10,
											allowBlank: false,
											xtype: 'swdatefield',
											format: 'd.m.Y',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											name: 'vacImplementDate',
											id: 'vacImplementDate'
										}, 
										//  Вставил Тагир
										sw.Promed.vac.objects.comboLpu({
											//idPrefix: 'quikImpl',
											hiddenName: 'Lpu_id',
											id: 'impl_LpuCombo',
											//disabled: true,
											tabIndex: TABINDEX_VACIMPFRM + 11,
											listeners: {
												'select': function(combo, record, index) {
													var Params = new Object();
													Params.Lpu_id = combo.getValue();
													Ext.getCmp('amm_ImplVacForm').initCombo ('impl_LpuCombo', Params);
												}
											}
										}),
										// End  Тагир
										/*
										{
											id: 'impl_LpuBuildingCombo',
											listWidth: 600,
											// parentElementId: 'impl_LpuCombo',
											disabled: true,
											linkedElements: [
													'impl_LpuSectionCombo'
											],
											tabIndex: TABINDEX_VACIMPFRM + 11,
											width: 260,
											xtype: 'swlpubuildingglobalcombo'
										}, 
                                                                               
                                                                                {
											id: 'impl_LpuSectionCombo',
											linkedElements: [
													'impl_MedPersonalCombo'
											],
											listWidth: 600,
											parentElementId: 'impl_LpuBuildingCombo',
											tabIndex: TABINDEX_VACIMPFRM + 12,
											width: 260,
											xtype: 'swlpusectionglobalcombo'
										},
                                                                                */
										{
											id: 'impl_LpuBuildingCombo',
											listWidth: 600,
											// parentElementId: 'impl_LpuCombo',
											//disabled: true,
											tabIndex: TABINDEX_VACIMPFRM + 12,
											width: 260,
											xtype: 'amm_BuildingComboServiceVac',
											listeners: {
												'select': function(combo, record, index) {
													var Params = new Object();
													Params.LpuBuilding_id = combo.getValue();
													Ext.getCmp('amm_ImplVacForm').initCombo ('impl_LpuBuildingCombo', Params);
												}.createDelegate(this)
											}
										}, 
										{
											fieldLabel: 'Служба',
											id: 'impl_ComboMedServiceVac',
											listWidth: 600,
											tabIndex: TABINDEX_VACIMPFRM + 13,
											width: 260,
											emptyText: VAC_EMPTY_TEXT,
											xtype: 'amm_ComboMedServiceVac',
											listeners: {
												'select': function(combo, record, index) {
													var Params = new Object();
													Params.MedService_id = combo.getValue();
													Ext.getCmp('amm_ImplVacForm').initCombo ('impl_ComboMedServiceVac', Params);
												}.createDelegate(this)
											}
										},
										{
											fieldLabel: 'Врач (исполнил)',
											id: 'impl__MedPersonalCombo',
											listWidth: 600,
											tabIndex: TABINDEX_VACIMPFRM + 14,
											width: 260,
											emptyText: VAC_EMPTY_TEXT,
											xtype: 'amm_ComboVacMedPersonal'
											//allowBlank: false,
										},
										/*
										{
											allowBlank: false,
											fieldLabel: 'Врач (исполнил)',
											hiddenName: 'MedStaffImpl_id',
											id: 'impl_MedPersonalCombo',
											//parentElementId: 'impl_LpuSectionCombo',
											listWidth: 600,
											tabIndex: TABINDEX_VACIMPFRM + 13,
											width: 260,
											emptyText: VAC_EMPTY_TEXT,
											xtype: 'swmedstafffactglobalcombo'
										}
										*/										
									]
								},
								{
									autoHeight: true,
									style: 'margin: 0px; padding: 0px;',
									title: 'Медикамент',
									xtype: 'fieldset',
									id: 'amm_ContainerMedicines',
									hidden: (!getRegionNick().inlist(['perm','penza','krym','astra'])),
									items: [
										new sw.Promed.ViewFrame({
											height: 100,
											actions: [
												{ name: 'action_add', hidden: false, handler:function(){
													this.addContainerMedicines();
												}.createDelegate(this) },
												{ name: 'action_edit', hidden: false, handler:function(){
													this.editContainerMedicines();
												}.createDelegate(this) },
												{ name: 'action_delete', hidden: false, handler:function(){
													this.deleteMedicines();
												}.createDelegate(this) },
												{ name: 'action_print', hidden: true},
												{ name: 'action_refresh', hidden: true},
												{ name: 'action_view', disabled: false, hidden: true}
											],
											autoExpandColumn: 'autoexpand',
											autoLoadData: false,
											border: true,
											dataUrl: '/?c=VaccineCtrl&m=loadContainerMedicinesViewGrid',
											region: 'center',
											id: 'ContainerMedicinesView',
											paging: false,
											totalProperty: 'totalCount',
											style: 'margin: 0px',
											dataEvnVizitPL: false,
											stringfields: [
												{name: 'DocumentUcStr_id', type: 'int', header: 'ID', key: true},
												{name: 'EvnDrug_id', type: 'int', hidden: true},
												{name: 'DrugPrep_Name', type: 'string', header: langs('Наименование'), id: 'autoexpand'},
												{name: 'PrepSeries_Ser', type: 'string', header: langs('Серия'), width: 40},
												{name: 'Doza', type: 'string', header: langs('Доза'), width: 40}
											],
											onLoadData: function(sm, index, record) {
												if(this.getCount()>0){
													this.getAction('action_add').disable();
												}else{
													this.getAction('action_add').enable();
												}
											}
										}),
										{
											id: 'containerMedicines_EvnVizitPL_id',
											name: 'EvnVizitPL_id',
											xtype: 'hidden'
										},
										{
											id: 'containerMedicines_DocumentUcStr_id',
											name: 'DocumentUcStr_id',
											xtype: 'hidden'
										}
									]
								}
								]
							}]
							
					}, 
					{
						layout: 'form',
						border: false,
						items: [{

							autoHeight: true,
							// layout: 'form',
							style: 'margin: 5px; padding: 5px;',
							title: 'Реакция на прививку',
							xtype: 'fieldset',
							items: 
							[
								{
									border: false,
									layout: 'column',
									defaults: {
										columnWidth:0.5,
										bodyBorder: false
										,anchor: '100%'
									},
									items: [{//столбец 1
										layout: 'form',
										items: [{
											fieldLabel: 'Местная реакция',
											tabIndex: TABINDEX_VACIMPFRM + 20,
											xtype: 'textarea',
											name: 'vacReactLocalDesc',
											grow: true,
											growMax: 100,
											growMin: 60,
											width: 260
										}]
									}, {//столбец 2
										layout: 'form',
										items: [{
											fieldLabel: 'Общая реакция',
											tabIndex: TABINDEX_VACIMPFRM + 21,
											xtype: 'textarea',
											name: 'vacReactGeneralDesc',
											grow: true,
											growMax: 100,
											growMin: 60,
											width: 260
										}]
									}]
								}
							]
						}]
					}			
				]
				})
			]
			
		});
		sw.Promed.amm_ImplVacForm.superclass.initComponent.apply(this, arguments);
	},
	activityButtonSave_DependsOnExistenceMedicines: function(){
		var win = this;
		if(!getRegionNick().inlist(['perm','penza','krym','astra']) || !win.vacEditForm.findById('ContainerMedicinesView').isVisible()){
			return true;
		}	
		var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();
		var rec = mediciGrid.getStore().getAt(0);
		var existenceMedicines = (rec && rec.get('EvnDrug_id')) ? true : false;
		return existenceMedicines;
	},
	
	show: function(record) {
		sw.Promed.amm_ImplVacForm.superclass.show.apply(this, arguments);
		this.vacEditForm.getForm().reset();
		this.formParams = record;
//		Ext.getCmp('amm_ImplVacForm').setTitle('Исполнение прививки');
		
//		debugger;
//		if (record.mode_type == 'EDIT') {
//			this.formStore.url = '/?c=VaccineCtrl&m=loadImplFormInfoEdit';
//		} else {
//			this.formStore.url = '/?c=VaccineCtrl&m=loadImplFormInfo';
//		}
		
		this.formStore.load({
			params: {
				vac_jaccount_id: record.vac_jaccount_id,
				user_id: getGlobalOptions().pmuser_id
			},
			callback: function(){
				//var formStoreRecord = this.formStore.getAt(0);
				formStoreRecord = this.formStore.getAt(0);
				sw.Promed.vac.utils.consoleLog(formStoreRecord);
				sw.Promed.vac.utils.consoleLog('StatusType_id='+formStoreRecord.get('StatusType_id'));
				
				if (formStoreRecord.get('StatusType_id') == 1){
					this.formParams.actType = sw.Promed.vac.cons.actType.EDITING;
					/// this.vacEditForm.getForm().findField('MedStaffImpl_id').allowBlank = true;  !!!
					//	Ext.getCmp('amm_ImplVacForm').setTitle('Исполнение прививки: Редактирование');

					Ext.getCmp('ImplVacForm_ButtonSave').setText('Сохранить');
					Ext.getCmp('ImplVacForm_ButtonEdit').hide ();
				} else {    
					this.formParams.actType = sw.Promed.vac.cons.actType.IMPLEMENTING;
					Ext.getCmp('ImplVacForm_ButtonSave').setText('Исполнить прививку');
					if (formStoreRecord.get('vacOther') == 1) {  //  Если прочие прививки
						Ext.getCmp('ImplVacForm_ButtonEdit').hide ();
					} else {
						Ext.getCmp('ImplVacForm_ButtonEdit').show ();
						Ext.getCmp('ImplVacForm_ButtonEdit').enable();
					}
				}
				
				this.fireEvent(//событие "Тип формы определён"
					'readyFrmType',
					sw.Promed.vac.cons.formType.VACCINE,
					this.formParams.actType
				);
				
				if (formStoreRecord.get('StatusType_id') == 1 && this.formParams.source == 'VacAssigned') {
					Ext.Msg.alert('Внимание', 'Выбранная вакцина уже была исполнена!');
					this.hide();
					return false;
				}

				//  this.formParams.vac_way_name = formStoreRecord.get('VaccineWay_id');
				this.formParams.vac_way_name = formStoreRecord.get('VaccineWay_name');
				
				this.formParams.vac_doze = formStoreRecord.get('Doza');
				
				this.formParams.vac_jaccount_id = formStoreRecord.get('vacJournalAccount_id');
				
				//	this.formParams.date_purpose = sw.Promed.vac.utils.nvlDate(formStoreRecord.get('Date_Purpose'));
				//  this.formParams.date_purpose = formStoreRecord.get('Date_Purpose');
				this.formParams.vac_info = formStoreRecord.get('NAME_TYPE_VAC');//.replace( /<br \/>/g, '');
                                
				//	this.formParams.birthday = sw.Promed.vac.utils.nvlDate(formStoreRecord.get('BirthDay'));
				this.formParams.birthday = formStoreRecord.get('BirthDay');
				this.formParams.vac_name = formStoreRecord.get('vac_name');
				this.formParams.vaccine_id = formStoreRecord.get('Vaccine_id');
				this.formParams.person_id = formStoreRecord.get('Person_id');
				this.formParams.vacAgeBegin = formStoreRecord.get('Vaccine_AgeBegin');
				this.formParams.vacAgeEnd = formStoreRecord.get('Vaccine_AgeEnd');
															
				//  var ImplVacForm = Ext.getCmp('vacImplEditForm');
				//  this.formParams.Lpu_id = ImplVacForm.form.findField('Lpu_id').getValue(),
				//  alert ('Lpu_id2 =  ' + this.formParams.Lpu_id);
				//  impl_LpuCombo
				//	this.formParams.vac_doze = formStoreRecord.get('VACCINE_DOZA');


				//контроль диапазона дат:
				this.validateVacImplementDate.init(function(o){
					var dateRangeBegin = sw.Promed.vac.utils.strToDate(o.birthday);
					var dateRangeEnd = sw.Promed.vac.utils.strToDate(o.birthday);
					dateRangeBegin.setFullYear(dateRangeBegin.getFullYear() + o.vacAgeBegin);	
																				dateRangeEnd.setFullYear(dateRangeEnd.getFullYear() + o.vacAgeEnd);
					var resObj = {};
					if (o.birthday != undefined) resObj.personBirthday = o.birthday;
					if (o.vacAgeBegin != undefined) resObj.dateRangeBegin = dateRangeBegin;
					if (o.vacAgeEnd != undefined) resObj.dateRangeEnd = dateRangeEnd;
					return resObj;
				}(this.formParams));

				this.validateVacImplementDate.getMinDate();
				this.validateVacImplementDate.getMaxDate();

				//---формируем дату назначения (исп-ся в комбобоксах)
				var implDate = null;
				if (this.formParams.mode_type == 'EDIT') {
					implDate = formStoreRecord.get('Date_impl');
				} else {
					implDate = new Date;
				}
				
				if (formStoreRecord.get('Date_Purpose') != undefined)
					this.formParams.date_purpose = formStoreRecord.get('Date_Purpose');
				else this.formParams.date_purpose = implDate;				
				//---/формируем дату назначения---

				//	var comboVacSeria = Ext.getCmp(obj.idComboVaccineSeria);
				sw.Promed.vac.utils.consoleLog('this.formParams:');
				sw.Promed.vac.utils.consoleLog(this.formParams);
				var comboVacSeria = this.vacEditForm.getForm().findField('VaccineSeria_id');
				comboVacSeria.store.load({
					params: this.formParams,
					callback: function(){
                                            var Seria;
                                            if (comboVacSeria.getStore().getCount() > 0)
							if ((!!this.formParams.row_plan_parent) || (comboVacSeria.getStore().getCount() == 1))
								comboVacSeria.setValue(comboVacSeria.getStore().getAt(0).get('VacPresence_id'));
						else comboVacSeria.setValue('');
                                                        
						if ((formStoreRecord.get('StatusType_id') == 1) || (formStoreRecord.get('Seria')!=undefined)) { //режим редактирования
							Seria = formStoreRecord.get('Seria');
							if (formStoreRecord.get('vacPeriod')!=undefined) {
								Seria += ' - ' + formStoreRecord.get('vacPeriod');
							}
							comboVacSeria.setValue(Seria);
							//+ ' - ' + formStoreRecord.get('vacPeriod')); //серию из базы
						}
					}.createDelegate(this)
				});
				
//				var comboVacWay = Ext.getCmp(obj.idComboVaccineWay);
				var comboVacWay = this.vacEditForm.getForm().findField('VaccineWay_id');
				comboVacWay.reset();
				comboVacWay.store.load({
					params: this.formParams,
					callback: function(){
						if (comboVacWay.getStore().getCount() > 0)
//							if (!!this.formParams.row_plan_parent)
							if ((!!this.formParams.row_plan_parent) || (comboVacWay.getStore().getCount() == 1))
								comboVacWay.setValue(comboVacWay.getStore().getAt(0).get('VaccineWayPlace_id'));
					}.createDelegate(this)
				});
				
				sw.Promed.vac.utils.consoleLog(record);
				//this.vacEditForm.form.findField('vacImplementDate').setValue(record.date_purpose);
				//alert (implDate);
				this.vacEditForm.form.findField('vacImplementDate').setValue(implDate);
				
				this.vacEditForm.form.findField('vacReactLocalDesc').setValue(formStoreRecord.get('ReactLocalDesc'));
				this.vacEditForm.form.findField('vacReactGeneralDesc').setValue(formStoreRecord.get('ReactGeneralDesc'));
				this.vacEditForm.form.findField('vacImplName').setValue(this.formParams.vac_name);
				this.vacEditForm.form.findField('vacImplInfo').setValue(this.formParams.vac_info);
				
				this.PersonInfoPanel.load({
					callback: function() {
						this.PersonInfoPanel.setPersonTitle();
						var Person_deadDT = Ext.getCmp('amm_ImplVacForm').PersonInfoPanel.getFieldValue('Person_deadDT');
						if (Person_deadDT != undefined) {
							//alert('Person_deadDT = ' + Person_deadDT );
							Ext.getCmp('vacImplementDate').setMaxValue(Person_deadDT);
							// Ext.getCmp('amm_ImplVacForm').form.findField('vacImplementDate').setMaxValue(Person_deadDT);
							// Ext.getCmp('vacImplEditForm').setMaxValue(Person_deadDT);
						}
						else { this.validateVacImplementDate.getMaxDate(); }
					}.createDelegate(this),
					loadFromDB: true,
					Person_id: this.formParams.person_id
					,Server_id: this.formParams.Server_id
				});

				sw.Promed.vac.utils.consoleLog(record);
                                 
				var comboLpu = this.vacEditForm.form.findField('Lpu_id');
				comboLpu.getStore().load({
					callback: function() {
						comboLpu.setValue(formStoreRecord.get('Lpu_id'));
					}.createDelegate(this)
				});
                                 
				var Params = new Object();
				Params.Lpu_id =formStoreRecord.get('Lpu_id')
				Params.MedPers_id_impl = formStoreRecord.get('MedPers_id_impl');
				Params.Medservice_id =  formStoreRecord.get('Medservice_id');
				Params.LpuBuilding_id = formStoreRecord.get('LpuBuilding_id');

				Ext.getCmp('amm_ImplVacForm').initCombo ('impl_LpuCombo', Params);
				sw.Promed.vac.utils.consoleLog('MedPers_id_impl = ' + formStoreRecord.get('MedPers_id_impl'));

//                                if (formStoreRecord.get('MedPers_id_impl') != null) {
//					 Ext.getCmp('impl__MedPersonalCombo').setValue(formStoreRecord.get('MedPers_id_impl'));
//                                }
//                                alert ('Ok');
                                
                              
				var comboMedStaffPurp = this.vacEditForm.form.findField('MedStaffFact_id');
				// alert('MedPers_id_purp='+formStoreRecord.get('MedPers_id_purp'));
				comboMedStaffPurp.getStore().load({
					callback: function() {
						// this.vacEditForm.form.findField('MedStaffFact_id').setValue(record.med_staff_fact_id);
						comboMedStaffPurp.setValue(formStoreRecord.get('MedPers_id_purp'));
//          }.createDelegate(this)

						/*
							var comboMedStaff = this.vacEditForm.form.findField('MedStaffImpl_id');
							comboMedStaff.getStore().load({
								callback: function() {

										if (formStoreRecord.get('MedPers_id_impl') != null) {
											comboMedStaff.setValue(formStoreRecord.get('MedPers_id_impl'));
										} else if (formStoreRecord.get('StatusType_id') !== 1) {
											//если не редактирование, то подставляем юзера
											comboMedStaff.setValue(formStoreRecord.get('MedPers_id_user'));
										}

								}
							});
						*/

					}.createDelegate(this)
				});
				
				this.vacEditForm.form.findField('vacImplDoze').setValue(this.formParams.vac_doze);
				//this.vacEditForm.form.findField('VaccineWay_id').setValue(this.formParams.vac_way_name);
				for (var i = 0; i < Ext.getCmp('impl_comboVaccineWay').getStore().data.length; i++) {
					obj = Ext.getCmp('impl_comboVaccineWay').getStore().data.items[i].data;
					if (obj.VaccineWayPlace_Name == this.formParams.vac_way_name) {
						this.vacEditForm.form.findField('VaccineWay_id').setValue(obj.VaccineWayPlace_id);
					}
				}

				
				this.vacEditForm.form.findField('vacImplementDate').focus(true, 100);
				Ext.getCmp('ImplVacForm_ButtonSave').enable();
                                
				this.setContainerMedicines();
			}.createDelegate(this)
		});		
	},
	setContainerMedicines: function(){
		var win = this;
		win.vacEditForm.findById('ContainerMedicinesView').dataEvnVizitPL = false;
		if(!getRegionNick().inlist(['perm','penza','krym','astra'])) {
			return false;
		}
		
		var formStoreRecord = this.formStore.getAt(0);
		win.vacEditForm.form.findField('EvnVizitPL_id').setValue( formStoreRecord.get('EvnVizitPL_id') );
		win.vacEditForm.form.findField('DocumentUcStr_id').setValue( formStoreRecord.get('DocumentUcStr_id') );
		this.loadParamsEvn();
		this.loadMedicinesViewGrid();
	},
	getParamsDrugEdit: function(){
		var win = this;
		var UserMedStaffFact_id = sw.Promed.MedStaffFactByUser.current.MedService_id;
		var UserLpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		var containerMedicinesView = win.vacEditForm.findById('ContainerMedicinesView');
		if(!containerMedicinesView.dataEvnVizitPL) return false;
		
		var params = {
			action: 'add',
			onPersonChange: function(data) {
				//...
			},
			onHide: function() {
				this.loadMedicinesViewGrid();
			}.createDelegate(this),
			PersonEvn_id: containerMedicinesView.dataEvnVizitPL.PersonEvn_id,
			Person_id: containerMedicinesView.dataEvnVizitPL.Person_id,
			Server_id: containerMedicinesView.dataEvnVizitPL.Server_id,
			Person_Firname: containerMedicinesView.dataEvnVizitPL.Person_Firname,
			Person_Surname: containerMedicinesView.dataEvnVizitPL.Person_Surname,
			Person_Secname: containerMedicinesView.dataEvnVizitPL.Person_Secname,
			Person_Birthday: containerMedicinesView.dataEvnVizitPL.Person_Birthday,
			UserMedStaffFact_id: UserMedStaffFact_id,
			UserLpuSection_id: UserLpuSection_id,
			userMedStaffFact: sw.Promed.MedStaffFactByUser,
			from: sw.Promed.MedStaffFactByUser.current.ARMForm,
			ARMType: sw.Promed.MedStaffFactByUser.current.ARMType,
			TimetableGraf_id: containerMedicinesView.dataEvnVizitPL.TimetableGraf_id,
			formParams: {
				EvnDrug_id: 0,
				EvnDrug_pid: containerMedicinesView.dataEvnVizitPL.EvnVizitPL_id,
				PersonEvn_id: containerMedicinesView.dataEvnVizitPL.PersonEvn_id,
				Person_id: containerMedicinesView.dataEvnVizitPL.Person_id,
				Server_id: containerMedicinesView.dataEvnVizitPL.Server_id
			},
			personData: {
				PersonEvn_id: containerMedicinesView.dataEvnVizitPL.PersonEvn_id,
				Person_id: containerMedicinesView.dataEvnVizitPL.Person_id,
				Server_id: containerMedicinesView.dataEvnVizitPL.Server_id,
				Person_Firname:  containerMedicinesView.dataEvnVizitPL.Person_Firname,
				Person_Surname: containerMedicinesView.dataEvnVizitPL.Person_Surname,
				Person_Secname: containerMedicinesView.dataEvnVizitPL.Person_Secname,
				Person_Birthday: containerMedicinesView.dataEvnVizitPL.Person_Birthday
			},
			parentEvnComboData: [{
				Evn_id: containerMedicinesView.dataEvnVizitPL.EvnVizitPL_id,
				Evn_Name: containerMedicinesView.dataEvnVizitPL.EvnVizitPL_setDate +' / '+ sw.Promed.MedStaffFactByUser.current.LpuSection_Nick +' / '+ sw.Promed.MedStaffFactByUser.current.MedPersonal_FIO,
				Evn_setDate: Date.parseDate(containerMedicinesView.dataEvnVizitPL.EvnVizitPL_setDate, 'd.m.Y'),
				Evn_disDate: Date.parseDate(containerMedicinesView.dataEvnVizitPL.EvnVizitPL_disDate, 'd.m.Y'),
				MedStaffFact_id:  sw.Promed.MedStaffFactByUser.current.MedStaffFact_id,
				Lpu_id:  sw.Promed.MedStaffFactByUser.current.Lpu_id,
				LpuSection_id:  sw.Promed.MedStaffFactByUser.current.LpuSection_id,
				MedPersonal_id:  sw.Promed.MedStaffFactByUser.current.MedPersonal_id
			}],
			callback: function(data){
				this.getDocumentUcStr(data);
			}.bind(this)
		};
		return params;
	},
	addContainerMedicines: function(){
		var win = this;
		var UserMedStaffFact_id = sw.Promed.MedStaffFactByUser.current.MedService_id;
		var UserLpuSection_id = sw.Promed.MedStaffFactByUser.current.LpuSection_id;
		var containerMedicinesView = win.vacEditForm.findById('ContainerMedicinesView');
		if(!containerMedicinesView.dataEvnVizitPL) {
			Ext.Msg.alert('Внимание', 'Отсутствует случай лечения!');
			return false;
		}
		
		var params = win.getParamsDrugEdit();
		params.action = 'add';
		params.formParams.EvnDrug_id = 0;
		var form_name = getEvnDrugEditWindowName();
		getWnd(form_name).show(params);
	},
	editContainerMedicines: function(){
		var win = this;
		
		var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();
		var rec = mediciGrid.getStore().getAt(0);
		if(rec.get('EvnDrug_id')){
			var params = win.getParamsDrugEdit();
			params.action = 'edit';
			params.formParams.EvnDrug_id = rec.get('EvnDrug_id');
			var form_name = getEvnDrugEditWindowName();
			getWnd(form_name).show(params);
		}
	},
	loadParamsEvn: function(){
		var win = this;
		var EvnVizitPL_id = win.vacEditForm.form.findField('EvnVizitPL_id').getValue();
		if(!EvnVizitPL_id) return false;
		Ext.Ajax.request({
			url: '/?c=EvnVizit&m=loadDataEvnVizitPL',
			method: 'POST',
			params: {EvnVizitPL_id: EvnVizitPL_id},
			success: function(response, opts) {
				var response_obj = Ext.util.JSON.decode(response.responseText);
				var containerMedicinesView = this.vacEditForm.findById('ContainerMedicinesView');
				if(response_obj[0] && containerMedicinesView) containerMedicinesView.dataEvnVizitPL = response_obj[0];
			}.createDelegate(this),
			failure: function(response, opts) {
				console.warn('error loadParamsEvn');
			}
		});
	},
	loadMedicinesViewGrid: function(){
		var win = this;
		var DocumentUcStr_id = win.vacEditForm.form.findField('DocumentUcStr_id').getValue();
		var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();
		mediciGrid.getStore().removeAll();
		
		if(!DocumentUcStr_id) return false;
		var baseParams = {DocumentUcStr_id: DocumentUcStr_id};
	    mediciGrid.getStore().baseParams = baseParams;
	   
		mediciGrid.getStore().load({
			callback: function(){
				console.log('loadMedicinesViewGrid');
			}.createDelegate(this)
		});
	},
	getDocumentUcStr: function(data){
		var win = this;
		var params = data || false;
		var EvnVizitPL_id = win.vacEditForm.form.findField('EvnVizitPL_id').getValue();
		if(!params || !params.evnDrugData || !params.evnDrugData.EvnDrug_id || !EvnVizitPL_id) return false;
		Ext.Ajax.request({
			url: '/?c=EvnDrug&m=loadEvnDrugView',
			method: 'POST',
			params: {
				EvnDrug_id: params.evnDrugData.EvnDrug_id,
				EvnDrug_pid: EvnVizitPL_id
			},
			success: function(response, opts) {
				var win = this;
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj && response_obj[0] && response_obj[0]['DocumentUcStr_id']){
					win.vacEditForm.form.findField('DocumentUcStr_id').setValue( response_obj[0]['DocumentUcStr_id'] );

					var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();
					if(mediciGrid.getStore().getCount() > 0 && mediciGrid.getStore().getAt(0).get('DocumentUcStr_id') == response_obj[0]['DocumentUcStr_id']){
						return true;
					}
					win.loadMedicinesViewGrid();
					win.saveDocumentUcStrID();
				}
			}.createDelegate(this),
			failure: function(response, opts) {
				console.warn('error getDocumentUcStr');
			}
		});
	},
	saveDocumentUcStrID: function(){
		var win = this;
		var DocumentUcStr_id = win.vacEditForm.form.findField('DocumentUcStr_id').getValue();
		var vacJournalAccount_id = formStoreRecord.get('vacJournalAccount_id');

		if(!vacJournalAccount_id || !DocumentUcStr_id) return false;
		var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();

		Ext.Ajax.request({
			url: '/?c=VaccineCtrl&m=saveDocumentUcStrIDforJournalAccount',
			method: 'POST',
			params: {
				DocumentUcStr_id: DocumentUcStr_id,
				vacJournalAccount_id: vacJournalAccount_id
			},
			success: function(response, opts) {
				console.log('saveDocumentUcStrID');
			}.createDelegate(this),
			failure: function(response, opts) {
				console.warn('error saveDocumentUcStrID');
			}
		});
	},
	deleteMedicines: function(){
		var win = this;
		var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();
		var rec = mediciGrid.getStore().getAt(0);
		if(!rec.get('EvnDrug_id')) return false;
		Ext.Ajax.request({
			url: '/?c=EvnDrug&m=deleteEvnDrug',
			method: 'POST',
			params: {
				EvnDrug_id: rec.get('EvnDrug_id')
			},
			success: function(response, opts) {
				console.log('ok deleteMedicines');
				var win = this;
				var mediciGrid = win.vacEditForm.findById('ContainerMedicinesView').getGrid();
				mediciGrid.getStore().removeAll();
				win.vacEditForm.form.findField('DocumentUcStr_id').setValue();
			}.createDelegate(this),
			failure: function(response, opts) {
				console.warn('error deleteMedicines');
			}
		});
	}
});