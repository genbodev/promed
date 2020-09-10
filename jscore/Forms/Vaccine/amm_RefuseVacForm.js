/**
* amm_RefuseVacForm - окно просмотра формы Медотвода/отказа/согласия
*
* PromedWeb - The New Generation of Medical Statistic Software
*
* @copyright    Copyright (c) 2012 
* @author       
* @version      01.06.2012
* @comment      
*/

sw.Promed.amm_RefuseVacForm = Ext.extend(sw.Promed.BaseForm, {
	id: 'amm_RefuseVacForm',
	title: 'Медицинские отводы, согласия и отказы',
	titleBase: 'Медицинские отводы, согласия и отказы',
	border: false,
	width: 800,
	height: 500,
	maximizable: false,  
	closeAction: 'hide',
	layout: 'border',
	codeRefresh: true,
	modal:true,
	objectName: 'amm_RefuseVacForm',
	objectSrc: '/jscore/Forms/Vaccine/amm_RefuseVacForm.js',
	onHide: Ext.emptyFn,
	listeners: {
		'show': function(c) {
			//alert('listeners-show');
			this.setTitle('');
		},
		'readyFrmType': function(frmType, actType) {
			var pars = {
				modeType: actType,
				formType: frmType
			};
			this.setTitle(this.titleExt.init(pars).getTitle());
		}
//		'aftershow': function() {
//			var pars = {
//				modeType: this.formParams.actType,
//				formType: sw.Promed.vac.cons.formType.VAC_REFUSE
//			};
//			this.setTitle(this.titleExt.init(pars).getTitle());
			
//			this.titleExt.getTitle({
////				modeType: sw.Promed.vac.cons.actType,
//				modeType: this.formParams.actType,
////				params: this.formParams,
//				formType: sw.Promed.vac.cons.formType
//			});
//			if (this.formParams.actType)
				
//			alert(par);
//      var isLoad = this.formParams.tst;
//			switch(par) {
//				case 'PersonInfoPanel':
//					isLoad.load1 = true;
//					break;
//				case 'MedStaffRefuse':
//					isLoad.load2 = true;
//					break;
//				case 'RefusalType':
//					isLoad.load3 = true;
//					break;
//				case 'VaccineType':
//					isLoad.load4 = true;
//					break;
//			}
//			if (isLoad.load1 && isLoad.load2 && isLoad.load3 && isLoad.load4) {}
			
//			var formIsLoad = true;
//			for (var loadFlag in this.formParams.tst) {
//				if (this.formParams.tst.hasOwnProperty(loadFlag)) {
//					formIsLoad = this.formParams.tst[loadFlag] && formIsLoad;
//				}
//			}
//		}
	},
	
	initComponent: function() {
		var params = new Object();
		var form = this;
		//объект для контроля дат формы:
		this.validateRefuseVacDate = sw.Promed.vac.utils.getValidateObj({
			formId: 'vacRefuseEditForm',
			fieldName: 'vacRefuseDate'
		});
		
		this.titleExt = sw.Promed.vac.utils.getFormTitleObj();
		
		/*
		* хранилище для доп сведений
		*/
		this.formStore = new Ext.data.JsonStore({
			fields: ['refuse_id', 'VaccineType_id', 'MedPersonal_id', 'TypeRecord', 'Reason', 
				'RefuseDateBegin', 'RefuseDateEnd', 'BirthDay'],
			url: '/?c=VaccineCtrl&m=loadRefuseFormInfo',
			key: 'refuse_id',
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
			{text: 'Сохранить',
				iconCls: 'save16',
				id: 'btn_save',
				tabIndex: TABINDEX_REFUSEVACFRM + 30,
				handler: function() {
					var vacRefuseForm = Ext.getCmp('vacRefuseEditForm');
					if (!vacRefuseForm.form.isValid()) {
						sw.Promed.vac.utils.msgBoxNoValidForm();
						return false;
					}
				
					
					//var vacImplForm = this.findParentBy(function(p){return p.name==='vacEditForm';});
					//debugger;
					//var vacImplForm = this.findParentByType('amm_RefuseVacForm').findBy(function(p){return p.name==='vacEditForm';});
					var formParam = '';
					formParam = vacRefuseForm.form.findField('vacRefuseDate').getValue();
//          debugger;
					if (formParam != undefined && formParam != '') {
						formParam = formParam.format('d.m.Y');
					}
					var refuseDate = formParam;
					
//          formParam = vacRefuseForm.form.findField('VaccineType_id').getValue();
//          if (formParam != undefined) {
//            formParam = formParam.format('d.m.Y');
//          }
//          var vaccineTypeId = ;
//          var vaccineTypeId = nvl(vacRefuseForm.form.findField('vacRefuseDate').getValue()
//                             , vacRefuseForm.form.findField('vacRefuseDate').getValue().format('d.m.Y'));
					Ext.Ajax.request({
						url: '/?c=VaccineCtrl&m=savePrivivRefuse',
						method: 'POST',
						params: {
								'person_id': Ext.getCmp('amm_RefuseVacForm').formParams.person_id,
								'med_staff_refuse_id': vacRefuseForm.form.findField('MedStaffRefuse_id').getValue(),
								'refuse_date': refuseDate,
								'vaccine_type_id': vacRefuseForm.form.findField('VaccineType_id').getValue(),
								'date_refuse_range': vacRefuseForm.form.findField('Date_RefuseRange').value,
								'vac_refuse_cause': vacRefuseForm.form.findField('vacRefuseCause').getValue(),
								'refusal_type_id': vacRefuseForm.form.findField('RefusalType_id').getValue(),
								'user_id': getGlobalOptions().pmuser_id,
								'refuse_id': Ext.getCmp('amm_RefuseVacForm').formParams.refuse_id
						},
						success: function(response, opts) {
							//console.log('response='+response.responseText);
							sw.Promed.vac.utils.consoleLog(response);
							
							if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
								Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'amm_RefuseVacForm', {keys: [Ext.getCmp('amm_RefuseVacForm').formParams.person_id]});
								Ext.getCmp('btn_save').setDisabled( true );
																																form.hide();                                                    
							}
//              alert('this.formParams.parent_id='+this.formParams.parent_id);
//              Ext.getCmp('journalsGridRight').fireEvent('successPurpose', Ext.getCmp('gridSimilarRecords').store.keyList);
						}.createDelegate(this),
						failure: function(response, opts) {
							sw.Promed.vac.utils.consoleLog('server-side failure with status code: ' + response.status);
						}
					});
				}.createDelegate(this)
			},
			{
				text: '-'
			},
                        //HelpButton(this, TABINDEX_REFUSEVACFRM + 31),
			 {text: BTN_FRMHELP,
                        iconCls: 'help16',
                        tabIndex : TABINDEX_REFUSEVACFRM + 31,
                        handler: function(button, event)
                        {
                            ShowHelp(this.ownerCt.titleBase);    
//                            ShowHelp(Ext.getCmp('journalsVaccine').titleBase);
                        }
			},
                        {
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
//        id: 'vacImpl_CancelButton',
				onTabAction: function () {
//          this.findById('vacRefuseDate').focus(true, 100);
					this.vacEditForm.form.findField('vacRefuseDate').focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_REFUSEVACFRM + 32,
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
					id: 'vacRefuseEditForm',
					name: 'vacEditForm',
					labelAlign: 'right',
					//autohight: true,
					//height: 300,
					labelWidth: 100,
					layout: 'form',
					items: [
//            this.PersonInfoPanel,
						{
							height:5,
							border: false
						},
						{
							border: false,
							layout: 'form',
							items: [{
								autoHeight: true,
								style: 'margin: 5px; padding: 5px;',
								title: 'Решение о медотводе/отказе/согласии',
								xtype: 'fieldset',
								items: [{
									fieldLabel: 'Дата',
									tabIndex: TABINDEX_REFUSEVACFRM + 10,
									allowBlank: false,
									xtype: 'swdatefield',
									format: 'd.m.Y',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									name: 'vacRefuseDate'
								}, {
									id: 'Refuse_LpuBuildingCombo',
									//lastQuery: '',
									listWidth: 500,
									linkedElements: [
										'Refuse_LpuSectionCombo'
									],
									tabIndex: TABINDEX_REFUSEVACFRM + 11,
									width: 260,
									xtype: 'swlpubuildingglobalcombo'
								}, {
									id: 'Refuse_LpuSectionCombo',
									linkedElements: [
										'Refuse_MedPersonalCombo'
									],
									listWidth: 500,
									parentElementId: 'Refuse_LpuBuildingCombo',
									tabIndex: TABINDEX_REFUSEVACFRM + 12,
									width: 260,
									xtype: 'swlpusectionglobalcombo'
								}, {
									allowBlank: false,
									fieldLabel: 'Врач',
									hiddenName: 'MedStaffRefuse_id',
									id: 'Refuse_MedPersonalCombo',
									lastQuery: '',
									parentElementId: 'Refuse_LpuSectionCombo',
									listWidth: 500,
									tabIndex: TABINDEX_REFUSEVACFRM + 13,
									width: 260,
									readOnly: true,
									value: null,
									emptyText: VAC_EMPTY_TEXT,
									xtype: 'swmedstafffactglobalcombo'
								}]
							}, {
								
								autoHeight: true,
								layout: 'form',
								style: 'margin: 5px; padding: 5px;',
								title: 'Медотвод/отказ/согласие',
								xtype: 'fieldset',
								anchor:'-10',
								items: [{
									allowBlank: false,
									autoLoad: true,
									fieldLabel: 'Решение',
									tabIndex: TABINDEX_REFUSEVACFRM + 21,
									hiddenName: 'RefusalType_id',
									width: 260,
									emptyText: VAC_EMPTY_TEXT,
									xtype: 'amm_VaccineRefusalTypeCombo'
										
								}, {
									allowBlank: false,
									fieldLabel: 'Иммунизация',
									id: 'refuse_VaccineTypeCombo',
									autoLoad: true,
									hiddenName: 'VaccineType_id',
									tabIndex: TABINDEX_REFUSEVACFRM + 22,
									width: 260,
									xtype: 'amm_SprInoculationCombo'
							
								}, {
									allowBlank: false,
									name : "Date_RefuseRange",
									xtype : "daterangefield",
									width : 170,
									fieldLabel : 'Период',
									tabIndex: TABINDEX_REFUSEVACFRM + 23,
									plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)]
								}, {
									fieldLabel: 'Причина',
									tabIndex: TABINDEX_REFUSEVACFRM + 24,
									xtype: 'textarea',
									name: 'vacRefuseCause',
									grow: true,
									growMax: 100,
									growMin: 30,
									//width: 260,
									//anchor:'right 100%'
									anchor:'-10'
									//,disabled: true,
									//readOnly: true
								}]

							}]

//              }]
							
						}]
				})
			]
			
		});
		sw.Promed.amm_RefuseVacForm.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(record) {
		sw.Promed.vac.utils.consoleLog(record);
		//Ext.getCmp('impl_VaccineListCombo').vaccineParams = record;
		sw.Promed.amm_RefuseVacForm.superclass.show.apply(this, arguments);
		this.vacEditForm.getForm().reset();
		this.formParams = record;
		this.formStore.load({
			params: {
				'refuse_id': record.refuse_id
			},
			callback: function(){
				var formStoreRecord = this.formStore.getAt(0);
//        sw.Promed.vac.utils.consoleLog();
//        sw.Promed.vac.utils.consoleLog('StatusType_id='+formStoreRecord.get('StatusType_id'));
				var editParams = {};
				editParams.isEditMode = (formStoreRecord != null && formStoreRecord.get('refuse_id') != null);
				//вместо предшествующей строки:
				if (formStoreRecord != null && formStoreRecord.get('refuse_id') != null) {
					this.formParams.actType = sw.Promed.vac.cons.actType.EDITING;
				}
				
				switch (this.formParams.actType){
					case sw.Promed.vac.cons.actType.EDITING://редактирование
						editParams.medPersonalId = formStoreRecord.get('MedPersonal_id');
						editParams.typeRecord = formStoreRecord.get('TypeRecord');
						editParams.vaccineTypeId = formStoreRecord.get('VaccineType_id');
						editParams.reason = formStoreRecord.get('Reason');
	//					editParams.refuseDateBegin = (new Date(formStoreRecord.get('RefuseDateBegin'))).format('d.m.Y');
						editParams.refuseDateBegin = formStoreRecord.get('RefuseDateBegin');
						editParams.refuseDateEnd = formStoreRecord.get('RefuseDateEnd');
						break;
					default://добавление
						this.formParams.actType = sw.Promed.vac.cons.actType.ADDING;
						break;
				}
				
				this.fireEvent(//событие "Тип формы определён"
					'readyFrmType',
					sw.Promed.vac.cons.formType.VAC_REFUSE,
					this.formParams.actType
				);
					
//				if (editParams.isEditMode) {
//					this.titleExt.buildTitle(sw.Promed.vac.cons.actType.EDITING);
//					this.setTitle("Редактировать: Медотвод/отказ от прививки");
//          this.setTitle(this.titleExt.title);
//					editParams.medPersonalId = formStoreRecord.get('MedPersonal_id');
//					editParams.typeRecord = formStoreRecord.get('TypeRecord');
//					editParams.vaccineTypeId = formStoreRecord.get('VaccineType_id');
//					editParams.reason = formStoreRecord.get('Reason');
////					editParams.refuseDateBegin = (new Date(formStoreRecord.get('RefuseDateBegin'))).format('d.m.Y');
//					editParams.refuseDateBegin = formStoreRecord.get('RefuseDateBegin');
//					editParams.refuseDateEnd = formStoreRecord.get('RefuseDateEnd');
//        if (formStoreRecord.get('StatusType_id') == 1) {
//          Ext.Msg.alert('Внимание', 'Выбранная вакцина уже была исполнена!');
//          this.hide();
//          return false;
//        } else {
//					this.setTitle("Добавить: Медотвод/отказ от прививки");
//				}
				
//        this.formParams.vac_way_name = formStoreRecord.get('VaccineWay_name');
//        this.formParams.vac_jaccount_id = formStoreRecord.get('vacJournalAccount_id');
//        this.formParams.vac_doze = formStoreRecord.get('Doza');
//        
//        consoleLog(record);
//        this.vacEditForm.form.findField('vacImplementDate').setValue(record.date_purpose);
//        this.vacEditForm.form.findField('vacImplName').setValue(record.vac_name);
//
//        this.vacEditForm.form.findField('vacImplInfo').setValue(record.vac_info);

				//контроль диапазона дат:
				this.validateRefuseVacDate.init(function(o){
//					var dateRangeBegin = sw.Promed.vac.utils.strToDate(o.birthday);
//					var dateRangeEnd = sw.Promed.vac.utils.strToDate(o.birthday);
//					dateRangeBegin.setFullYear(dateRangeBegin.getFullYear() + o.vacAgeBegin);
//					dateRangeEnd.setFullYear(dateRangeEnd.getFullYear() + o.vacAgeEnd);
					var resObj = {};
					if (o.birthday != undefined) resObj.personBirthday = o.birthday;
//					if (o.vacAgeBegin != undefined) resObj.dateRangeBegin = dateRangeBegin;
//					if (o.vacAgeEnd != undefined) resObj.dateRangeEnd = dateRangeEnd;
					return resObj;
				}(this.formParams));
				this.validateRefuseVacDate.getMinDate();
				this.validateRefuseVacDate.getMaxDate();

				this.PersonInfoPanel.load({
					callback: function() {
						this.PersonInfoPanel.setPersonTitle();
					}.createDelegate(this),
					loadFromDB: true,
					Person_id: record.person_id
					,Server_id: this.formParams.Server_id
				});

//        consoleLog(record);
//        this.vacEditForm.form.findField('MedStaffFact_id').getStore().load();
//        this.vacEditForm.form.findField('MedStaffFact_id').setValue(record.med_staff_fact_id);
//        this.vacEditForm.form.findField('vacImplDoze').setValue(this.formParams.vac_doze);
//        this.vacEditForm.form.findField('VaccineWay_name').setValue(this.formParams.vac_way_name);
				

				this.vacEditForm.form.findField('LpuBuilding_id').getStore().load();
				this.vacEditForm.form.findField('LpuSection_id').getStore().load();
				this.vacEditForm.form.findField('MedStaffRefuse_id').getStore().load({
					callback: function() {
						if (editParams.isEditMode)
							this.vacEditForm.form.findField('MedStaffRefuse_id').setValue(editParams.medPersonalId);
						else {
                                                    var CurMedStaffFact_id = getGlobalOptions().CurMedStaffFact_id
                                                   //alert(CurMedStaffFact_id);
                                                    if (Ext.getCmp('Refuse_MedPersonalCombo').getValue () == Ext.getCmp('Refuse_MedPersonalCombo').lastSelectionText){
                                                        Ext.getCmp('Refuse_MedPersonalCombo').reset ();
                                                    }
                                                    else {
                                                        this.vacEditForm.form.findField('MedStaffRefuse_id').setValue(CurMedStaffFact_id);
                                                    }
                                                    //}
                                                        //this.vacEditForm.form.findField('MedStaffRefuse_id').setValue(getGlobalOptions().medstafffact);
                                                }
							
					}.createDelegate(this)
				});  
					
				this.vacEditForm.form.findField('vacRefuseDate').setValue(new Date());
				this.vacEditForm.form.findField('RefusalType_id').getStore().load({
					callback: function() {
						if (editParams.isEditMode)
							this.vacEditForm.form.findField('RefusalType_id').setValue(editParams.typeRecord);
						else
							this.vacEditForm.form.findField('RefusalType_id').setValue(1);
					}.createDelegate(this)
				});
//debugger;
				this.vacEditForm.form.findField('VaccineType_id').getStore().load({
					callback: function() {
						if (editParams.isEditMode)
							this.vacEditForm.form.findField('VaccineType_id').setValue(editParams.vaccineTypeId);
						else
							this.vacEditForm.form.findField('VaccineType_id').setValue(1000); //все прививки
					}.createDelegate(this)
				});
//				.setValue(dt.format('d.m.Y') + ' - ' + dt2.format('d.m.Y'));
//				this.vacEditForm.form.findField('Date_RefuseRange').getStore().load({
//          callback: function() {
						if (editParams.isEditMode)
							this.vacEditForm.form.findField('Date_RefuseRange').setValue(editParams.refuseDateBegin +' - ' + editParams.refuseDateEnd);
						else
							this.vacEditForm.form.findField('Date_RefuseRange').setValue((new Date()).format('d.m.Y')); //все прививки
//          }.createDelegate(this)
//        });
				
                                this.vacEditForm.form.findField('vacRefuseCause').setValue(editParams.reason);
				this.vacEditForm.form.findField('vacRefuseDate').focus(true, 100);

//				alert(7);
//debugger;
			}.createDelegate(this)
		});
		 Ext.getCmp('btn_save').setDisabled( false );
                 //Ext.getCmp('Refuse_MedPersonalCombo').reset();
                 //Ext.getCmp('Refuse_LpuSectionCombo').reset();
                 //Ext.getCmp('Refuse_LpuBuildingCombo').reset();
                 
                 
//     alert('Show');
	}
});
