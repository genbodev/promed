/**
* amm_ImplVacNoPurpForm - окно Исполнения минуя назначение
*lpu
* PromedWeb - The New Generation of Medical Statistic Software
*
* @copyright    Copyright (c) 2012 
* @author       
* @version      26.06.2012
* @comment      
*/

sw.Promed.amm_ImplVacNoPurpForm = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_ImplVacNoPurpForm',
	title: "Исполнение прививки",
	border: false,
	width: 800,
	height: 500,
	maximizable: false,  
	closeAction: 'hide',
	layout: 'border',
	codeRefresh: true,
	modal:true,
	objectName: 'amm_ImplVacNoPurpForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_ImplVacNoPurpForm.js',
	onHide: Ext.emptyFn,
	
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
                        Ext.getCmp('impl_LpuBuildingCombo').reset();
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
                                        }
                                        else
                                            {Ext.getCmp('impl_ComboMedServiceVac').reset();}

                             }           
                     });
           };
              //  Выводим список сотрудников  
              
              Ext.getCmp('impl__MedPersonalCombo').getStore().load ({
                                      params: Params,
                                    callback: function() {
                                        if (Params.MedPers_id_impl != null) {
                                            Ext.getCmp('impl__MedPersonalCombo').setValue(Params.MedPers_id_impl);  
                                        }
                                        else
                                            {Ext.getCmp('impl__MedPersonalCombo').reset();}

                                                 }           
                                         });
                 
    
        },
        
	initComponent: function() {
		var params = new Object();
		var form = this;
		//объект для контроля дат формы:
		this.validateVacImplementDate = sw.Promed.vac.utils.getValidateObj({
			formId: 'vacImplWithoutPurpEditForm',
			fieldName: 'vacImplementDate'
		});
		
		/*
		* хранилище для доп сведений
		*/
		this.formStore = new Ext.data.JsonStore({
			fields: ['Vaccine_id', 'Vaccine_AgeBegin', 'Vaccine_AgeEnd'],
			url: '/?c=VaccineCtrl&m=loadImplVacNoPurpFormInfo',
			key: 'Vaccine_id',
			root: 'data'
		});
		
		this.PersonInfoPanel  = new sw.Promed.PersonInfoPanel({
			//id: 'vacImpl_PersonInfoFrame',
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
			//HelpButton(this),
			{ text: 'Сохранить',
			iconCls: 'save16',
				tabIndex: TABINDEX_VACIMPNPURPFRM + 20,
				handler: function() {
					//var vacImplWithoutPurpForm = Ext.getCmp('vacImplWithoutPurpEditForm');
					var implForm = Ext.getCmp('vacImplWithoutPurpEditForm');
					if (!implForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						return false;
					}
					
					var implWin = Ext.getCmp('amm_ImplVacNoPurpForm');
					// var formParam = '';
					var formParam = implForm.form.findField('vacImplementDate').getValue();
					if (formParam != undefined && formParam != '') {
						formParam = formParam.format('d.m.Y');
					}
					implWin.formParams.date_vac = formParam;
					implWin.formParams.Lpu_id = Ext.getCmp('impl_LpuCombo').getValue();
					implWin.formParams.medservice_id = Ext.getCmp('impl_ComboMedServiceVac').getValue();
					implWin.formParams.med_staff_impl_id = Ext.getCmp('impl__MedPersonalCombo').getValue();
					//implForm.form.findField('MedStaffImpl_id').getValue(),
					//alert(implWin.formParams.med_staff_impl_id);
					//alert('key_list');
					//alert(Ext.getCmp('amm_ImplVacNoPurpForm').formParams.key_list);
					//alert('parent_id');
					//alert(Ext.getCmp('amm_ImplVacNoPurpForm').formParams.parent_id);
					//return;
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение ..."});
					loadMask.show();
					Ext.Ajax.request({
						url: '/?c=VaccineCtrl&m=saveImplWithoutPurp',
						method: 'POST',
						params: implWin.formParams,							
						success: function(response, opts) {
							loadMask.hide();
							sw.Promed.vac.utils.consoleLog('response');
							var obj = Ext.util.JSON.decode(response.responseText);
							sw.Promed.vac.utils.consoleLog(obj.rows[0].vacJournalAccount_id);//.rows[0].vacJournalAccount_id);
							if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
							//Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'amm_ImplVacNoPurpForm', {keys: [Ext.getCmp('amm_ImplVacNoPurpForm').formParams.key_list]});
							//Ext.getCmp('journalsGridRight').fireEvent('success', 'amm_PurposeVacForm', {keys: [Ext.getCmp('amm_ImplVacNoPurpForm').formParams.key_list]});
								if (Ext.getCmp('amm_VacPlan') != undefined)
									Ext.getCmp('amm_VacPlan').fireEvent('success', 'amm_PurposeVacForm', {keys: [Ext.getCmp('amm_ImplVacNoPurpForm').formParams.key_list]});
								if (Ext.getCmp('amm_Kard063') != undefined)
									Ext.getCmp('amm_Kard063').fireEvent('success', 'amm_PurposeVacForm');
							}
							form.hide();
						}.createDelegate(this),
						failure: function(response, opts) {
							loadMask.hide();
							sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
						}
					});
				}.createDelegate(this)
				
			},{
				text: '-'
			},{
				
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				tabIndex: TABINDEX_VACIMPNPURPFRM + 21,
				onTabAction: function () {
					this.vacEditForm.form.findField('vacImplementDate').focus();
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
					id: 'vacImplWithoutPurpEditForm',
					name: 'vacEditForm',
					labelAlign: 'right',
					labelWidth: 100,
					layout: 'form',
					items: [
					//this.PersonInfoPanel,
						{
							height:5,
							border: false
						},
						{
							border: false,
							layout: 'form',
							items: [{
								autoHeight: true,
								layout: 'form',
								style: 'margin: 5px; padding: 5px;',
								title: 'Исполнение прививки',
								xtype: 'fieldset',
								items: [
									{
										fieldLabel: 'Дата исполнения',
										tabIndex: TABINDEX_VACIMPNPURPFRM + 10,
										allowBlank: false,
										xtype: 'swdatefield',
										format: 'd.m.Y',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'vacImplementDate',
										id: 'vacImplementDate'
									}, {
										id: 'impl_LpuCombo',
										listWidth: 600,
										tabIndex: TABINDEX_VACIMPNPURPFRM + 11,
										width: 260,
										xtype: 'amm_LpuListCombo',
										listeners: {
											'select': function(combo)  {
												var Params = new Object();
												Params.Lpu_id = combo.getValue();
												Ext.getCmp('amm_ImplVacNoPurpForm').initCombo ('impl_LpuCombo', Params);
											}.createDelegate(this)
										}
									}, {
										id: 'impl_LpuBuildingCombo',
										//lastQuery: '',
										listWidth: 600,
										/*
										linkedElements: [
											'impl_LpuSectionCombo'
										],
										*/
										//parentElementId: 'impl_LpuCombo',
										tabIndex: TABINDEX_VACIMPNPURPFRM + 12,
										width: 260,
										xtype: 'amm_BuildingComboServiceVac',
										//xtype: 'swlpubuildingglobalcombo',  
										listeners: {
											'select': function(combo)  {
												var Params = new Object();
												Params.LpuBuilding_id = combo.getValue();
												Ext.getCmp('amm_ImplVacNoPurpForm').initCombo ('impl_LpuBuildingCombo', Params);
											}
										}
										//xtype: 'swlpubuildingcombo'
									}, {
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
												Ext.getCmp('amm_ImplVacNoPurpForm').initCombo ('impl_ComboMedServiceVac', Params);
											}.createDelegate(this)
										}
									}, {
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
										id: 'impl_LpuSectionCombo',
										linkedElements: [
												'impl_MedPersonalCombo'
										],
										listWidth: 600,
										parentElementId: 'impl_LpuBuildingCombo',
										tabIndex: TABINDEX_VACIMPNPURPFRM + 13,
										width: 260,
										xtype: 'swlpusectionglobalcombo',
										listeners: {
											'select': function(combo)  {
												Ext.getCmp('impl_MedPersonalCombo').reset();
											}
										}
									}, {
										//allowBlank: false,
										fieldLabel: 'Врач (исполнил)',
										hiddenName: 'MedStaffImpl_id',
										id: 'impl_MedPersonalCombo',
										parentElementId: 'impl_LpuSectionCombo',
										listWidth: 600,
										tabIndex: TABINDEX_VACIMPNPURPFRM + 14,
										width: 260,
										emptyText: VAC_EMPTY_TEXT,
										xtype: 'swmedstafffactglobalcombo'
									}
									*/
								]
							}]
						}]
				})
			]
			
		});
		sw.Promed.amm_ImplVacNoPurpForm.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(record) {
		//Ext.getCmp('impl_VaccineListCombo').vaccineParams = record;
		sw.Promed.amm_ImplVacNoPurpForm.superclass.show.apply(this, arguments);
		//sw.Promed.vac.utils.consoleLog('amm_ImplVacNoPurpForm record:');
		//sw.Promed.vac.utils.consoleLog(record);
		this.vacEditForm.getForm().reset();
		this.formParams = record;

		this.formStore.load({
			params: {
				vaccine_id: record.vaccine_id
			},
			callback: function(){
				var formStoreRecord = this.formStore.getAt(0);
				this.formParams.vacAgeBegin = formStoreRecord.get('Vaccine_AgeBegin');
				this.formParams.vacAgeEnd = formStoreRecord.get('Vaccine_AgeEnd');

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

				this.PersonInfoPanel.load({
					callback: function() {
						this.PersonInfoPanel.setPersonTitle();
						var Person_deadDT = Ext.getCmp('amm_ImplVacNoPurpForm').PersonInfoPanel.getFieldValue('Person_deadDT');
						if (Person_deadDT != undefined) {
							Ext.getCmp('vacImplementDate').setMaxValue(Person_deadDT);
						} 
					}.createDelegate(this),
					loadFromDB: true,
					Person_id: record.person_id
					,Server_id: this.formParams.Server_id
				});


				this.vacEditForm.form.findField('impl_LpuCombo').getStore().load({
					callback: function() {
						this.vacEditForm.form.findField('impl_LpuCombo').setValue(getGlobalOptions().lpu_id);
					}.createDelegate(this)
				});
                                
				var Params = new Object();
				Params.Lpu_id =getGlobalOptions().lpu_id;
				Ext.getCmp('amm_ImplVacNoPurpForm').initCombo ('impl_LpuCombo', Params);
				/*
				this.vacEditForm.form.findField('LpuBuilding_id').getStore().load({
		//      baseParams: { lpu_id: 35 }
					params: {Lpu_id: getGlobalOptions().lpu_id}
				});
				this.vacEditForm.form.findField('LpuSection_id').getStore().load({
					callback: function() {
						this.vacEditForm.form.findField('MedStaffImpl_id').getStore().load({
							callback: function() {
		//            if (getGlobalOptions().medstafffact[0]) {
		//              this.vacEditForm.form.findField('MedStaffImpl_id').setValue(getGlobalOptions().medstafffact[0]);
		//            }
							}.createDelegate(this)
						});
					}.createDelegate(this)
				});
				*/

				this.vacEditForm.form.findField('vacImplementDate').setValue(new Date());
				this.vacEditForm.form.findField('vacImplementDate').focus(true, 100);

			}.createDelegate(this)
		});

	}
});
