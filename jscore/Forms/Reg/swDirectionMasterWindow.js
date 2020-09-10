/**
 * swDirectionMasterWindow - мастер выписки направлений
 * Состоит из следующих шагов
 * 1. Выбор типа направления
 * 2. Выбор подразделения ЛПУ
 * 3а. Отображение списка врачей при выборе поликлинического подразделения
 * 3б. Отображение списка отделений при выборе стационарного подразделения
 * 3в. Отображение списка служб/услуг/ресурсов при выборе параклинического подразделения
 * 3г. Отображение списка служб МСЭ и ВК во всех ЛПУ
 * 4а. Отображение расписания врача при выборе врачей
 * 4б. Отображение расписания коек в отделении при выборе отделения
 * 4в. Отображение раписания службы/услуги/ресурса при их соответствующем выборе
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @prefix       dmw
 * @tabindex     TABINDEX_DMW
 * @version      October 2012
 */
 
/*NO PARSE JSON*/

sw.Promed.swDirectionMasterWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swDirectionMasterWindow',
	objectSrc: '/jscore/Forms/Reg/swDirectionMasterWindow.js',
	
	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_DMW,
	iconCls: 'workplace-mp16',
	id: 'swDirectionMasterWindow',
	readOnly: false,

	onDirection: Ext.emptyFn,
	onHide: Ext.emptyFn,
	listeners: 
	{
		hide: function()
		{
			this.onHide();
		}
	},

	/**
	 * Панель фильтров
	 */
	Filters: null,
	
	/**
	 * Мастер записи, содержит в себе все формы и выбранные данные
	 */
	Wizard: null,
	/**
	 * Открытие формы в режиме списка записанных
	 */
	openDayListOnly: function(action, date) {

		var params = {
			Lpu_id: this.userMedStaffFact.Lpu_id,
			Lpu_Nick: this.userMedStaffFact.Lpu_Nick,
			LpuUnit_id: this.userMedStaffFact.LpuUnit_id,
			LpuUnit_Name: this.userMedStaffFact.LpuUnit_Name,
			LpuUnit_Address: '',
			LpuRegion_Names: '',
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			LpuSection_Name: this.userMedStaffFact.LpuSection_Name,
			MedPersonal_id: getGlobalOptions().medpersonal_id,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			MedPersonal_FIO: this.userMedStaffFact.MedPersonal_FIO,
			LpuSectionProfile_id: this.userMedStaffFact.LpuSectionProfile_id,
			LpuSectionProfile_Name: this.userMedStaffFact.LpuSectionProfile_Name,
			UslugaComplexMedService_id: this.userMedStaffFact.UslugaComplexMedService_id,
			UslugaComplex_id: this.userMedStaffFact.UslugaComplex_id,
			UslugaComplex_Name: this.userMedStaffFact.UslugaComplex_Name,
			MedService_id: this.userMedStaffFact.MedService_id,
			MedService_Name: this.userMedStaffFact.MedService_Name,
			Resource_id: this.userMedStaffFact.Resource_id,
			changeParams: false
		};

		this.Wizard.params.step = action;
		this.Wizard.params.isHimSelf = true;
		switch (action) {
			case "RecordTTGOneDay":
			case "RecordTTGInGroup":
				this.Wizard.TTGDirectionPanel.MedStaffFact_id = params.MedStaffFact_id;
				break;
			case "RecordTTSOneDay":
				this.Wizard.TTSDirectionPanel.LpuSection_id = params.LpuSection_id;
				break;
			case "RecordTTMSOneDay":
				this.Wizard.TTMSDirectionPanel.MedService_id = params.MedService_id;
				this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = params.UslugaComplexMedService_id;
				this.Wizard.TTMSDirectionPanel.MedServiceData = (params.MedServiceData)?params.MedServiceData:{};
				break;
			case "RecordTTROneDay":
				this.Wizard.TTRDirectionPanel.Resource_id = params.Resource_id;
				this.Wizard.TTRDirectionPanel.MedService_id = params.MedService_id;
				this.Wizard.TTRDirectionPanel.ResourceData = (params.ResourceData)?params.ResourceData:{};
				break;
		}
		this.Wizard.params.date = date;
		this.Wizard.params = Ext.apply(this.Wizard.params, params);
		this.setStep(action, params);
		this.buttons[0].hide();
		this.buttons[1].hide();
	},
	/**
	 * Открытие списка записанных на выбранный день для поликлиники
	 */
	openDayListTTG: function(date)
	{
		this.Wizard.params.date = date;
		this.setStep('RecordTTGOneDay', {'changeParams': false});
	},
	/**
	 * Открытие списка записанных на выбранную бирку для поликлиники (
	 */
	openGroupListTTG: function(TimeTableGraf_id,Date, Time)
	{
		this.Wizard.params.TimeTableGraf_id = TimeTableGraf_id;
		this.Wizard.params.date = Date;
		this.setStep('RecordTTGInGroup', {'changeParams': false});
	},
	/**
	 * Открытие списка записанных на выбранный день для стационара
	 */
	openDayListTTS: function(date)
	{
		this.Wizard.params.date = date;
		this.setStep('RecordTTSOneDay');
	},
	/**
	 * Открытие списка записанных на выбранный день для службы/услуги
	 */
	openDayListTTMS: function(date)
	{
		this.Wizard.params.date = date;
		this.setStep('RecordTTMSOneDay');
	},
	/**
	 * Открытие списка записанных на выбранный день для ресурса
	 */
	openDayListTTR: function(date)
	{
		this.Wizard.params.date = date;
		this.setStep('RecordTTROneDay');
	},
	openDeadRecord: function(params) {
		var window = '';

		if (params.DirType_id == 7) {
			window ='swEvnDirectionHistologicEditWindow'
		}

		if (params.DirType_id == 18) {
			window = 'swEvnDirectionMorfoHistologicEditWindow';
		}

		if (window != '') {
			getWnd(window).show({action: 'add', formParams: this.Wizard.params.personData, userMedStaffFact: this.userMedStaffFact});
			this.hide();
		}
	},
	/**
	 * Смена шага мастера
	 *
	 * @param string step - Название шага
	 */
	setStep: function(step, params2)
	{
		log('setStep', step, params2);
		this.buttons[0].setDisabled(this.useCase.inlist(['record_from_queue']) && step.inlist(['SelectLpuUnit','SelectMedServiceLpuLevel'])  && this.Wizard.params['DirType_id']);
        var _this = this;
		this.Wizard.params.prevStep = this.Wizard.params.step;
		this.Wizard.params.step = step;
        var params = this.Filters.getFilters();
        params['FormName'] =_this.id;
		if (params2) {
			params = Ext.apply(params, params2);
		}
		if (
			(!!this.Wizard.params.directionData['withDirection'] || !Ext.isEmpty(this.userMedStaffFact.ARMType) && !this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) &&
			String(step).inlist(['SelectLpuUnit','SelectMedPersonal','SelectMedServiceLpuLevel','SelectMedService','SelectLpuSection']) &&
			this.Wizard.params.directionData['EvnDirection_IsReceive'] != 2
		) {
			this.DirectionButtonPanel.show();
		} else {
			this.DirectionButtonPanel.hide();
		}
		switch(step) {
			case "SelectDirType": // Выбор типа направления
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('dmwWizard_DirType');
				//log(this.Wizard.SelectLpuUnit);
				params.isDead = this.isDead;
				this.Wizard.BottomPanel.hide();
				this.Wizard.SelectLpuUnit.removeAll({clearAll: true});
				this.Wizard.SelectDirType.loadData({
					globalFilters: params
				});
				
				this.doLayout();
				break;
			case "SelectLpuUnit": // Выбор подразделения ЛПУ
				this.openDeadRecord(params);
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);
				this.Wizard.BottomPanel.hide();
				if(this.Wizard.params.prevStep == 'SelectMedPersonal' && this.Filters.getForm().findField('LpuSectionProfile_id').getValue() > 0) {
					this.Filters.getForm().findField('LpuSectionProfile_id').setValue(null);
					this.Filters.getForm().findField('LpuSectionProfile_id').fireEvent('change', this.Filters.getForm().findField('LpuSectionProfile_id'));
					this.applyFilter();
				}
				if (Ext.isEmpty(this.noReload) || this.noReload != true || (this.userMedStaffFact.ARMType == 'callcenter' && getRegionNick() == 'khak')) {
					this.Wizard.SelectLpuUnit.removeAll({clearAll: true});
					// Хакасия хочет сброс https://redmine.swan.perm.ru/issues/81496#note-30
					if (this.userMedStaffFact.ARMType == 'callcenter' && getRegionNick() == 'khak') {
						this.Filter_Lpu_Nick = null;
					}
					params['DirType_Code'] = this.Wizard.params['DirType_Code']||null;
					params['DirType_id'] = this.Wizard.params['DirType_id']||null;
					this.Filters.getForm().findField('Lpu_Nick').setValue(this.Filter_Lpu_Nick)
					params['Filter_Lpu_Nick'] = this.Filter_Lpu_Nick||null;
					this.Wizard.params.directionData['DirType_id'] = params['DirType_id'];
					params['ListForDirection'] = 1; // список для направлений
					//#110233
					if( (this.type=='ExtDirKVS' && this.userMedStaffFact.ARMType=='stac') || this.type=='ExtDirPriem') {
						if(this.type=='ExtDirPriem') {
							params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
							
						//	params['Filter_LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
						//	this.Wizard.params['Filter_LpuUnit_id'] = params['Filter_LpuUnit_id'];
							
						//	params['Filter_LpuUnitType_id'] = 17;
						//	this.Wizard.params['Filter_LpuUnitType_id'] = 17;
						
							params['Filter_LpuSection_id'] = this.userMedStaffFact.LpuSection_id;
							this.Wizard.params['Filter_LpuSection_id'] = params['Filter_LpuSection_id'];
							
							params['WithoutChildLpuSectionAge']=0;
						} else {
							this.Wizard.params['Filter_LpuUnit_id'] = params['Filter_LpuUnit_id'];
							params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
							params['Filter_LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
						}
						this.Filters.getForm().findField('Lpu_Nick').disable();
						this.Wizard.SelectLpuUnit.loadData({globalFilters: params});
					}//--110233
					else
					this.Wizard.SelectLpuUnit.loadData({
						globalFilters: params,
						callback: function() {
							if (this.userMedStaffFact.ARMType == 'regpol' || this.userMedStaffFact.ARMType == 'regpol6') {
								this.Wizard.SelectLpuUnit.getGrid().getSelectionModel().selectFirstRow();
								this.Wizard.SelectLpuUnit.getAction('action_edit').execute({firstLoad: true});			
							}
						}.createDelegate(this)
					});
				} else {
					this.noReload = false;
				}

				this.doLayout();
				break;
			case "SelectMedServiceLpuLevel": // Выбор службы уровня ЛПУ
				this.Filters.show();
				this.Wizard.params.directionData['DirType_id'] = this.Wizard.params['DirType_id'];
				this.Wizard.params.directionData['DirType_Code'] = this.Wizard.params['DirType_Code'];
				this.Wizard.Panel.layout.setActiveItem('dmwWizard_MedServiceLpuLevel');
				this.Wizard.SelectLpuUnit.removeAll({clearAll: true});
				this.Filters.getForm().findField('Lpu_Nick').setValue(this.Filter_Lpu_Nick)
				params['Filter_Lpu_Nick'] = this.Filter_Lpu_Nick||null;
                params['DirType_Code'] = this.Wizard.params['DirType_Code']||null;
                params['DirType_id'] = this.Wizard.params['DirType_id']||null;
				params['LpuUnitLevel'] = '1'; // WTF?
                params['ListForDirection'] = 1; // список для направлений
                params['isOnlyPolka'] = 0;
                // #110233
                if(this.type.inlist(['ExtDirDiag','ExtDirLab'])) {
					params['Filter_MedService_id'] = this.userMedStaffFact.MedService_id;
					
					this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
					params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
					this.Wizard.params['Lpu_Nick'] = this.Lpu_Nick;
					this.Filters.getForm().findField('Lpu_Nick').disable();
				}//--110233                
				if (params['DirType_Code'] == 10 && (this.userMedStaffFact.ARMType == 'regpol' || this.userMedStaffFact.ARMType == 'regpol6')) {
					//при создании направлений типа «В консультационный кабинет» регистратором поликлиники
					//могут быть доступны только службы типа «Консультационный кабинет» поликлинических отделений
					params['isOnlyPolka'] = 1;
				}
				if (params['DirType_Code'] == 2 && this.payType == 'money') {
					params['MedServiceType_SysNick'] = 'medosv';
					params['UslugaComplexMedService_IsPay'] = 2;
				}
				if (params['DirType_Code'] == 25) {
					params['MedServiceType_SysNick'] = 'profosmotr';
					if (this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday) {
						PersonAge = swGetPersonAge(this.Wizard.params.personData.Person_Birthday, getGlobalOptions().date);
						if (PersonAge && PersonAge > 17) {
							params['MedServiceType_SysNick'] = 'profosmotrvz';
						}
					}
				}
				// фильтрация по типу службы #101026
				if (params['DirType_Code'] == 15) {
					params['MedServiceType_SysNick'] = 'HTM';
				}
				params['groupByMedService'] = 1;
				this.Wizard.SelectMedServiceLpuLevel.setColumnHidden('UslugaComplex_Name', (this.Wizard.params['DirType_Code'] == 6));
				this.Wizard.SelectMedServiceLpuLevel.loadData({
					globalFilters: params
				});
				this.doLayout();
				break;
			case "SelectMedPersonal": // Выбор врача
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);

				this.Wizard.BottomPanel.show();
				this.Wizard.BottomPanel.layout.setActiveItem(0);
				// Если в списке подразделений уже выбрано подразделение, то берем данные из него
				if (this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id')) {
					params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
					this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				}
				if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id')) {
					params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
					this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				}
				
				this.Wizard.params.directionData['Lpu_did'] = params['Filter_Lpu_id'];
				this.Wizard.params.directionData['LpuUnit_did'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnitType_SysNick');

				if (params.firstLoad) {
					this.refreshMedPersonalList();
				}

				var arrPrevStep = ['RecordTTG', 'SelectLpuUnit', 'SelectMedPersonal'];
				if(arrPrevStep.indexOf(this.Wizard.params.prevStep) < 0){
					this.Filters.getForm().findField('LpuSectionProfile_id').setValue(null);
					this.Filters.getForm().findField('LpuSectionProfile_id').fireEvent('change', this.Filters.getForm().findField('LpuSectionProfile_id'));
				}
				
				if (!params.firstLoad) {
					this.Filters.getForm().findField('Lpu_Nick').setValue(Ext.util.Format.stripTags(this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick')));
					this.applyFilter();
				}
				
				this.doLayout();
				break;
			case "SelectLpuSection": // Выбор отделения
				
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);

				this.Wizard.BottomPanel.show();
				this.Wizard.BottomPanel.layout.setActiveItem(1);
				// Если в списке подразделений уже выбрано подразделение, то берем данные из него
				if (this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id')) {
					params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
					this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				}
				if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id')) {
					params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
					this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				}
				//#110233				
				if(this.type=='ExtDirKVS' && this.userMedStaffFact.ARMType=='stac') {
					
				//	params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
				//	this.Filters.getForm().findField('Lpu_Nick').disable();
					params['Filter_LpuSection_id'] = this.userMedStaffFact.LpuSection_id;
					this.Wizard.params['Filter_LpuSection_id'] = params['Filter_LpuSection_id'];
					this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
					this.Wizard.SelectLpuSection.loadData({
							globalFilters: params
						});
				}//--110233
				
				
				
				if (params.firstLoad) {
					this.refreshLpuSectionList();
				} else {
					this.Filters.getForm().findField('Lpu_Nick').setValue(Ext.util.Format.stripTags(this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick')));
					this.applyFilter();
				}
				
				this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['Lpu_did'] = params['Filter_Lpu_id'];
				this.Wizard.params.directionData['LpuUnit_did'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnitType_SysNick');
				
				//#110233 
				if(this.type.inlist(['ExtDirPriem'])) {
					this.Wizard.params['Filter_LpuSection_id'] = this.userMedStaffFact.LpuSection_id;
					params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
					params['Filter_LpuSection_id'] = this.userMedStaffFact.LpuSection_id;
					this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
					this.Wizard.params.directionData['LpuUnitType_SysNick'] = this.userMedStaffFact.Lpu_Nick;
					
					if(this.type=='ExtDirPriem') {
						params['Filter_LpuUnitType_id'] = 17;
						this.Wizard.params['Filter_LpuUnitType_id'] = 17;
						this.Wizard.params.directionData['Filter_LpuUnitType_id'] = 17;
					}
					
					this.Wizard.SelectLpuSection.loadData({
						globalFilters: params
					});
					this.applyFilter();
				}//--110233
				
				this.doLayout();
				break;
			case "SelectMedService": // Выбор службы/услуги
				// #110233
				if(this.type.inlist(['ExtDirDiag','ExtDirLab'])) {
					params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
					this.Filters.getForm().findField('Lpu_Nick').disable();
					this.Wizard.params['Lpu_Nick'] = this.Lpu_Nick;
					this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
					
					params['Filter_MedPersonal_FIO'] = this.userMedStaffFact.MedPersonal_FIO;
					this.Filters.getForm().findField('MedPersonal_FIO').disable();
					this.Wizard.params['MedPersonal_FIO'] = this.userMedStaffFact.MedPersonal_FIO;
					this.Filters.getForm().findField('MedPersonal_FIO').setValue(this.userMedStaffFact.MedPersonal_FIO);
				}
				if(this.type=='ExtDirPriem' /*&& this.userMedStaffFact.ARMType=='stacpriem'*/) {
					params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
					
					params['Filter_LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
					this.Wizard.params['Filter_LpuUnit_id'] = params['Filter_LpuUnit_id'];
					
					params['LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
					this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
										
					params['Filter_LpuUnitType_id'] = 17;
					this.Wizard.params['Filter_LpuUnitType_id'] = 17;
					
					//params['WithoutChildLpuSectionAge'] = 0;
					
					//this.Wizard.SelectLpuUnit.loadData({globalFilters: params});
				}//--110233				
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);

				this.Wizard.BottomPanel.show();
				this.Wizard.BottomPanel.layout.setActiveItem(2);
				
				// Если в списке подразделений уже выбрано подразделение, то берем данные из него
				if (this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id')) {
					params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
					this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				}
				if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id')) {
					params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
					this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				}
					
				this.refreshMedServiceList();
					
				if (!params.firstLoad) {
					if(this.type.inlist(['ExtDirDiag','ExtDirLab','ExtDirPriem'])) //#110233
						this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
					else
						this.Filters.getForm().findField('Lpu_Nick').setValue(Ext.util.Format.stripTags(this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick')));
					this.applyFilter();
				}
					
				this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['Lpu_did'] = params['Filter_Lpu_id'];
				this.Wizard.params.directionData['LpuUnit_did'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnitType_SysNick');
				
				this.doLayout();
				break;
			case "RecordTTG": // Запись к врачу
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTGDirectionPanel');
				this.doLayout();

				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'polka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id || this.Wizard.params.ARMType_id;

				if (this.Wizard.params.isHimSelf) { // если записываем к себе то directionData уже сформирован
					this.Wizard.TTGDirectionPanel.MedStaffFact_id = this.Wizard.params.MedStaffFact_id;
					this.Wizard.TTGDirectionPanel.isHimSelf = true;
				} else {
					this.Wizard.TTGDirectionPanel.MedStaffFact_id = this.Wizard.SelectMedPersonal.getSelectedParam('MedStaffFact_id');
					this.Wizard.TTGDirectionPanel.isHimSelf = false;
					this.Wizard.params.directionData['Resource_id'] = null;
					this.Wizard.params.directionData['MedService_id'] = null;
					this.Wizard.params.directionData['MedStaffFact_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('MedStaffFact_id');
					this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuUnit_id');
					this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('Lpu_id');
					this.Wizard.params.directionData['MedPersonal_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_id');
					this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSection_id');
					this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_id');
					this.Wizard.params.directionData['LpuSectionLpuSectionProfileList'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionLpuSectionProfileList');
					this.Wizard.params.directionData['LpuSectionAge_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionAge_id');
					this.Wizard.params.directionData['DirType_id'] = this.Wizard.params.DirType_id;
				}
				if(this.Wizard.params.directionData && this.Wizard.params.directionData.fromEMK)
					this.Wizard.TTGDirectionPanel.onSaveRecord = this.onDirection;
				this.Wizard.TTGDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTGDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTGDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTGDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTGDirectionPanel.loadSchedule(this.Wizard.TTGDirectionPanel.calendar.value);
				break;
			case "RecordTTGOneDay": // Запись к врачу на день
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTGRecordOneDayPanel');
				this.doLayout();
				this.Wizard.TTGRecordOneDayPanel.date =  this.Wizard.params.date;
				this.Wizard.TTGRecordOneDayPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTGRecordOneDayPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTGRecordOneDayPanel.onDirection = this.onDirection;
				// предполагаем что на момент открытия записи на день directionData уже существут
				/*
				 this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'polka';
				 this.Wizard.params.directionData['MedStaffFact_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('MedStaffFact_id');
				 this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuUnit_id');
				 this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('Lpu_id');
				 this.Wizard.params.directionData['MedPersonal_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_id');
				 this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSection_id');
				 this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_id');
				 this.Wizard.params.directionData['LpuSectionAge_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionAge_id');
				 */
				this.Wizard.TTGRecordOneDayPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTGRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				//this.Wizard.TTGRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTGRecordOneDayPanel.MedStaffFact_id = this.Wizard.TTGDirectionPanel.MedStaffFact_id;
				if (Ext.isEmpty(this.Wizard.params.Lpu_Nick)) {
					this.Wizard.params.Lpu_Nick = this.Wizard.params.directionData.Lpu_Nick;
					this.Wizard.params.LpuUnit_Name = this.Wizard.params.directionData.LpuUnit_Name;
					this.Wizard.params.MedPersonal_FIO = this.Wizard.params.directionData.MedPersonal_Fio;
					this.Wizard.params.LpuSectionProfile_Name = this.Wizard.params.directionData.LpuSectionProfile_Name;
				}
				this.Wizard.TTGRecordOneDayPanel.setUseCase(this.useCase);
				this.Wizard.TTGRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTGInGroup": // Запись к врачу на день
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTGRecordInGroupPanel');
				this.doLayout();
				this.Wizard.TTGRecordInGroupPanel.date =  this.Wizard.params.date;
				this.Wizard.TTGRecordInGroupPanel.TimeTableGraf_id =  this.Wizard.params.TimeTableGraf_id;
				this.Wizard.TTGRecordInGroupPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTGRecordInGroupPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTGRecordInGroupPanel.onDirection = this.onDirection;

				this.Wizard.TTGRecordInGroupPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTGRecordInGroupPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTGRecordInGroupPanel.MedStaffFact_id = this.Wizard.TTGDirectionPanel.MedStaffFact_id;
				if (Ext.isEmpty(this.Wizard.params.Lpu_Nick)) {
					this.Wizard.params.Lpu_Nick = this.Wizard.params.directionData.Lpu_Nick;
					this.Wizard.params.LpuUnit_Name = this.Wizard.params.directionData.LpuUnit_Name;
					this.Wizard.params.MedPersonal_FIO = this.Wizard.params.directionData.MedPersonal_Fio;
					this.Wizard.params.LpuSectionProfile_Name = this.Wizard.params.directionData.LpuSectionProfile_Name;
				}
				this.Wizard.TTGRecordInGroupPanel.setUseCase(this.useCase);
				this.Wizard.TTGRecordInGroupPanel.loadSchedule(this.Wizard.params.TimeTableGraf_id);
				break;
			case "RecordTTS": // Запись на койку
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTSDirectionPanel');
				this.doLayout();
				
				if (!this.Wizard.params.directionData['LpuSection_did']) {
					this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_id');
					this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionProfile_id');
					this.Wizard.params.directionData['LpuSectionAge_id'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionAge_id');
				}
				
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'stac';
				this.Wizard.params.directionData['DirType_id'] = this.Wizard.params.DirType_id;
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id || this.Wizard.params.ARMType_id;
				this.Wizard.params.directionData['LpuSectionLpuSectionProfileList'] =  this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionLpuSectionProfileList') || null;
				
				this.Wizard.TTSDirectionPanel.LpuSection_id = this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_id') || this.Wizard.params.directionData['LpuSection_did'];
				this.Wizard.TTSDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTSDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTSDirectionPanel.directionData = this.Wizard.params.directionData;
                this.Wizard.TTSDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTSDirectionPanel.loadSchedule(this.Wizard.TTSDirectionPanel.calendar.value);
				break;
			case "RecordTTSOneDay": // Запись к врачу на день
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTSRecordOneDayPanel');
				this.doLayout();
				this.Wizard.TTSRecordOneDayPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTSRecordOneDayPanel.date =  this.Wizard.params.date;
				this.Wizard.TTSRecordOneDayPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTSRecordOneDayPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuSectionAge_id'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionAge_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'stac';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTSRecordOneDayPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTSRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				//this.Wizard.TTSRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTSRecordOneDayPanel.LpuSection_id = this.Wizard.TTSDirectionPanel.LpuSection_id;
				this.Wizard.TTSRecordOneDayPanel.setUseCase(this.useCase);
				this.Wizard.TTSRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTR": // Запись на ресурс
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTRDirectionPanel');
				this.doLayout();
				this.Wizard.TTRDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				if (params && params.order) { // если переданы параметры по заказу, то часть данных нужно подменить
					this.Wizard.TTRDirectionPanel.Resource_id = params.Resource_id;
					this.Wizard.TTRDirectionPanel.MedService_id = params.MedService_id;
					this.Wizard.TTRDirectionPanel.ResourceData = {
						'Resource_Name': params.Resource_Name,
						'MedService_Nick': params.MedService_Nick,
						'UslugaComplex_id': params.UslugaComplex_id,
						'LpuSection_id': params.LpuSection_id,
						'LpuSectionProfile_id': params.LpuSectionProfile_id,
						'Lpu_id': this.Wizard.params.Lpu_id
					};
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = params.UslugaComplex_id;
					}
				} else {
					this.Wizard.TTRDirectionPanel.Resource_id = this.Wizard.SelectMedService.getSelectedParam('Resource_id');
					this.Wizard.TTRDirectionPanel.MedService_id = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
					this.Wizard.TTRDirectionPanel.MedServiceData = new Object({
						'Resource_Name': this.Wizard.SelectMedService.getSelectedParam('Resource_Name'),
						'MedService_Nick': this.Wizard.SelectMedService.getSelectedParam('MedService_Nick'),
						'UslugaComplex_id': this.Wizard.SelectMedService.getSelectedParam('UslugaComplex_id'),
						'LpuSection_id': this.Wizard.SelectMedService.getSelectedParam('LpuSection_id'),
						'LpuSectionProfile_id': this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id'),
						'Lpu_id': this.Wizard.params.Lpu_id
					});
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id');
					}
				}
				this.Wizard.TTRDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTRDirectionPanel.order = this.Wizard.params.order; // заказ услуги
				this.Wizard.TTRDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTRDirectionPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['Resource_id'] = this.Wizard.SelectMedService.getSelectedParam('Resource_id');
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedService.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTRDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTRDirectionPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTRDirectionPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTROneDay": // Запись к врачу на день
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTRRecordOneDayPanel');
				this.doLayout();
				this.Wizard.TTRRecordOneDayPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTRRecordOneDayPanel.date =  this.Wizard.params.date;
				this.Wizard.TTRRecordOneDayPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTRRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				//this.Wizard.TTRRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);

				this.Wizard.TTRRecordOneDayPanel.Resource_id = this.Wizard.TTRDirectionPanel.Resource_id;
				this.Wizard.TTRRecordOneDayPanel.MedService_id = this.Wizard.TTRDirectionPanel.MedService_id;
				this.Wizard.TTRRecordOneDayPanel.ResourceData = this.Wizard.TTRDirectionPanel.ResourceData;
				this.Wizard.TTRRecordOneDayPanel.order = this.Wizard.params.order; // заказ услуги

				this.Wizard.TTRRecordOneDayPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['Resource_id'] = this.Wizard.SelectMedService.getSelectedParam('Resource_id');
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedService.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTRRecordOneDayPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTRRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTRRecordOneDayPanel.setUseCase(this.useCase);
				this.Wizard.TTRRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTMS": // Запись на услугу/службу
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSDirectionPanel');
				this.doLayout();
				if (params && params.order) { // если переданы параметры по заказу, то часть данных нужно подменить
					this.Wizard.TTMSDirectionPanel.MedService_id = params.MedService_id;
					this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = params.UslugaComplexMedService_id;
					this.Wizard.TTMSDirectionPanel.MedServiceData = {
						'MedService_Nick': params.MedService_Nick,
						'UslugaComplex_id': params.UslugaComplex_id,
						'LpuSection_id': params.LpuSection_id,
						'LpuSectionProfile_id': params.LpuSectionProfile_id,
						'Lpu_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id')
					};
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = params.UslugaComplex_id;
					}
				} else {
					this.Wizard.TTMSDirectionPanel.MedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
					this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id');
					this.Wizard.TTMSDirectionPanel.MedServiceData = new Object({
						'MedService_Nick': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick'),
						'UslugaComplex_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'),
						'LpuSection_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id'),
						'LpuSectionProfile_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id'),
						'Lpu_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id')
					});
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id');
					}
				}

				// ищем в гриде this.Wizard.SelectMedServiceLpuLevel ту же услугу
				if (this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id) {
					var indexLLRec = this.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().findBy(function (rec) {
						if (rec.get('UslugaComplexMedService_id') == _this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id) {
							return true;
						}

						return false;
					});

					if (indexLLRec >= 0 && this.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().getAt(indexLLRec).get('useMedService') == 1) {
						// если у услуги нет своего расписания, тогда должно открываться расписание службы, даже если кликать на саму услугу
						this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = null;
					}
				}

				if (this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id
					&& !this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id')
				) {
					this.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().each(function(r) {
						if (r.get('MedService_id') == this.Wizard.TTMSDirectionPanel.MedService_id
							&& r.get('UslugaComplexMedService_id') == this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id
							&& -1 == r.get('Dates').indexOf('#ddffdd')
						) {
							// если у услуги выбранной через форму "Заказ комплексной услуги: Добавление" нет своего расписания, тогда должно открываться расписание службы
							this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = null;
							return false;
						}
						return true;
					}, this);
				}

				this.Wizard.TTMSDirectionPanel.order = this.Wizard.params.order; // заказ услуги

                this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
                this.Wizard.params.directionData['MedService_Nick'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick');
                this.Wizard.params.directionData['MedServiceType_SysNick'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedServiceType_SysNick');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuUnit_id');
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id');
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id');
                this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
                //this.Wizard.params.directionData['timetable'] = 'TimetablePar';
				this.Wizard.params.directionData['DirType_id'] = this.Wizard.params.DirType_id;
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id || this.Wizard.params.ARMType_id;
				
				this.Wizard.TTMSDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTMSDirectionPanel.personData = this.Wizard.params.personData;
                this.Wizard.TTMSDirectionPanel.directionData = this.Wizard.params.directionData;
                this.Wizard.TTMSDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTMSDirectionPanel.loadSchedule(this.Wizard.TTMSDirectionPanel.calendar.value);
				break;
			case "RecordTTMSOneDay": // Запись к врачу на день
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSRecordOneDayPanel');
				this.doLayout();
				this.Wizard.TTMSRecordOneDayPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTMSRecordOneDayPanel.date =  this.Wizard.params.date;
				this.Wizard.TTMSRecordOneDayPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTMSRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				//this.Wizard.TTMSRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);

				this.Wizard.TTMSRecordOneDayPanel.MedService_id = this.Wizard.TTMSDirectionPanel.MedService_id;
				this.Wizard.TTMSRecordOneDayPanel.UslugaComplexMedService_id = this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id;
				this.Wizard.TTMSRecordOneDayPanel.MedServiceData = this.Wizard.TTMSDirectionPanel.MedServiceData;
				this.Wizard.TTMSRecordOneDayPanel.order = this.Wizard.params.order; // заказ услуги

				this.Wizard.TTMSRecordOneDayPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedService.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTMSRecordOneDayPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTMSRecordOneDayPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTMSRecordOneDayPanel.setUseCase(this.useCase);
				this.Wizard.TTMSRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTRLpuLevel":
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('SelectTTRPanel');
				this.doLayout();
				if (params && params.order) { // если переданы параметры по заказу, то часть данных нужно подменить
					this.Wizard.TTRDirectionPanel.Resource_id = params.Resource_id;
					this.Wizard.TTRDirectionPanel.MedService_id = params.MedService_id;
					this.Wizard.TTRDirectionPanel.UslugaComplexMedService_id = params.UslugaComplexMedService_id;
					this.Wizard.TTRDirectionPanel.MedServiceData = {
						'Resource_Name': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Resource_Name'),
						'MedService_Nick': params.MedService_Nick,
						'UslugaComplex_id': params.UslugaComplex_id,
						'LpuSection_id': params.LpuSection_id,
						'LpuSectionProfile_id': params.LpuSectionProfile_id,
						'Lpu_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id')
					};
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = params.UslugaComplex_id;
					}
				} else {
					this.Wizard.TTRDirectionPanel.Resource_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Resource_id');
					this.Wizard.TTRDirectionPanel.MedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
					this.Wizard.TTRDirectionPanel.ResourceData = new Object({
						'Resource_Name': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Resource_Name'),
						'MedService_Nick': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick'),
						'UslugaComplex_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'),
						'LpuSection_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id'),
						'LpuSectionProfile_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id'),
						'Lpu_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id')
					});
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id');
					}
				}

				this.Wizard.TTRDirectionPanel.order = this.Wizard.params.order; // заказ услуги

				if (Ext.isEmpty(this.Wizard.TTRDirectionPanel.order) && !Ext.isEmpty(this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'))) {
					// если идёт направление на услугу, то формируем заказ услуги
					this.Wizard.TTRDirectionPanel.order = {
						LpuSectionProfile_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id')
						,UslugaComplex_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id')
						,checked: Ext.util.JSON.encode([])
						,Usluga_isCito: 1
						,UslugaComplex_Name: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_Name')
						,UslugaComplexMedService_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id')
						,MedService_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id')
					};
				}

				this.Wizard.params.directionData['Resource_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Resource_id');
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['MedService_Nick'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick');
				this.Wizard.params.directionData['MedServiceType_SysNick'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedServiceType_SysNick');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuUnit_id');
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id');
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				//this.Wizard.params.directionData['timetable'] = 'TimetablePar';
				this.Wizard.params.directionData['DirType_id'] = this.Wizard.params.DirType_id;
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id || this.Wizard.params.ARMType_id;

				this.Wizard.TTRDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTRDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTRDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTRDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTRDirectionPanel.loadSchedule(this.Wizard.TTRDirectionPanel.calendar.value);
				break;
			case "RecordTTMSLpuLevel": // Запись на услугу/службу на уровне отделения
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSDirectionPanel');
				this.doLayout();

				if (params && params.order) { // если переданы параметры по заказу, то часть данных нужно подменить
					this.Wizard.TTMSDirectionPanel.MedService_id = params.MedService_id;
					this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = params.UslugaComplexMedService_id;
					this.Wizard.TTMSDirectionPanel.PayType_id = params.PayType_id ? params.PayType_id : null;
					this.Wizard.TTMSDirectionPanel.MedServiceData = {
						'MedService_Nick': params.MedService_Nick,
						'UslugaComplex_id': params.UslugaComplex_id,
						'LpuSection_id': params.LpuSection_id,
						'LpuSectionProfile_id': params.LpuSectionProfile_id,
						'Lpu_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id'),
					};
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = params.UslugaComplex_id;
					}
				} else {
					this.Wizard.TTMSDirectionPanel.MedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
					this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id');
					this.Wizard.TTMSDirectionPanel.MedServiceData = new Object({
						'MedService_Nick': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick'),
						'UslugaComplex_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'),
						'LpuSection_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id'),
						'LpuSectionProfile_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id'),
						'Lpu_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id'),
					});
					if (getRegionNick() == 'kz') {
						this.Wizard.params.directionData['UslugaComplex_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id');
					}
				}

				// ищем в гриде this.Wizard.SelectMedServiceLpuLevel ту же услугу
				if (this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id) {
					var indexLLRec = this.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().findBy(function (rec) {
						if (rec.get('UslugaComplexMedService_id') == _this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id) {
							return true;
						}

						return false;
					});

					if (indexLLRec >= 0 && this.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().getAt(indexLLRec).get('useMedService') == 1) {
						// если у услуги нет своего расписания, тогда должно открываться расписание службы, даже если кликать на саму услугу
						this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = null;
					}
				}

				if (this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id
					&& !this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id')
				) {
					this.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().each(function(r) {
						if (r.get('MedService_id') == this.Wizard.TTMSDirectionPanel.MedService_id
							&& r.get('UslugaComplexMedService_id') == this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id
							&& this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('useMedService') == 1
						) {
							// если у услуги выбранной через форму "Заказ комплексной услуги: Добавление" нет своего расписания, тогда должно открываться расписание службы
							this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = null;
							return false;
						}
						return true;
					}, this);
				}

				this.Wizard.TTMSDirectionPanel.order = this.Wizard.params.order; // заказ услуги
				
				if (Ext.isEmpty(this.Wizard.TTMSDirectionPanel.order) && !Ext.isEmpty(this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'))) {
					// если идёт направление на услугу, то формируем заказ услуги
					this.Wizard.TTMSDirectionPanel.order = {
						LpuSectionProfile_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id')
						,UslugaComplex_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id')
						,checked: Ext.util.JSON.encode([])
						,Usluga_isCito: 1
						,UslugaComplex_Name: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_Name')
						,UslugaComplexMedService_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id')
						,MedService_id: this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id')
					};
				}
				if (!Ext.isEmpty(this.Wizard.TTMSDirectionPanel.PayType_id)) {
					this.Wizard.params.directionData['PayType_id'] = this.Wizard.TTMSDirectionPanel.PayType_id;
				}
                this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
                this.Wizard.params.directionData['MedService_Nick'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick');
                this.Wizard.params.directionData['MedServiceType_SysNick'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedServiceType_SysNick');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuUnit_id');
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id');
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id');
                this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
                //this.Wizard.params.directionData['timetable'] = 'TimetablePar';
				this.Wizard.params.directionData['DirType_id'] = this.Wizard.params.DirType_id;
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id || this.Wizard.params.ARMType_id;
				this.Wizard.params.directionData['Lpu_f003mcod'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_f003mcod');
		
				this.Wizard.TTMSDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTMSDirectionPanel.personData = this.Wizard.params.personData;
                this.Wizard.TTMSDirectionPanel.directionData = this.Wizard.params.directionData;
                this.Wizard.TTMSDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTMSDirectionPanel.loadSchedule(this.Wizard.TTMSDirectionPanel.calendar.value);
				break;
			case "RecordTTMSVK": // Запись на услугу/службу ВК или МСЕ
			
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSVKDirectionPanel');
				this.doLayout();
				
				this.Wizard.TTMSVKDirectionPanel.MedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
				this.Wizard.TTMSVKDirectionPanel.UslugaComplexMedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id');
				this.Wizard.TTMSVKDirectionPanel.MedServiceData = new Object({
					'MedService_Nick': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick'),
					'UslugaComplex_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'),
					'LpuSection_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id'),
					'LpuSectionProfile_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id'),
					'Lpu_id': this.Wizard.params.Lpu_id
				});

				this.Wizard.TTMSVKDirectionPanel.order = this.Wizard.params.order; // заказ услуги
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuUnit_id');
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_id');
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id');
				// Гениально
				this.Wizard.params.directionData['LpuUnitType_SysNick'] =  this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedServiceType_SysNick');
				this.Wizard.params.directionData['MedServiceType_SysNick'] =  this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedServiceType_SysNick');
				this.Wizard.params.directionData['DirType_id'] = this.Wizard.params.DirType_id;
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id || this.Wizard.params.ARMType_id;

				this.Wizard.TTMSVKDirectionPanel.setUseCase(this.useCase);
				this.Wizard.TTMSVKDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTMSVKDirectionPanel.directionData = this.Wizard.params.directionData;
                this.Wizard.TTMSVKDirectionPanel.userMedStaffFact = this.userMedStaffFact;
                this.Wizard.TTMSVKDirectionPanel.loadSchedule(this.Wizard.TTMSVKDirectionPanel.calendar.value);
				break;
			default:
				
		}
		this.refreshWindowTitle(params);
		this.Wizard.Panel.doLayout();
	},
	/**
	 * Устанавливает фильтр комбобокса по профилю
	 * @grid object грид по которому нужно собрать профили
	 */
	setFilterLpuSectionProfile: function(grid) {
        var _this = this;
		var id, yes = false;
		var combo = this.Filters.getForm().findField('LpuSectionProfile_id');
		var filters = [];
		var params = {LpuUnit_id: this.Wizard.params['LpuUnit_id']};
		params['FormName'] = _this.id;
		Ext.Ajax.request({
			url: '/?c=Common&m=loadLpuSectionProfileList',
			params: params,
			callback: function(options, success, response) {
				if (success) {
					var responseObj = Ext.util.JSON.decode(response.responseText);
					for(i=0; i<responseObj.length; i++) {
						filters.push(responseObj[i]['LpuSectionProfile_id']);
					}

					combo.getStore().clearFilter();
					combo.lastQuery = '';
					var id = combo.getValue();
					combo.getStore().filterBy(function(r) {
						if (r.get('LpuSectionProfile_id').inlist(filters)) {
							if (r.get('LpuSectionProfile_id') == id) {
								yes = true;
							}
							return true;
						} else {
							return false;
						}
					});
					combo.setValue((yes)?id:null);
					combo.fireEvent('change', combo, combo.getValue());
				}
			}
		});
	},
	/**
	 * Обновление списка медперсонала
	 */
	refreshMedPersonalList: function() {
        var _this = this;
		var params = this.Filters.getFilters();
        params['FormName'] =_this.id;
		if (this.Wizard.params['LpuUnit_id']) {
			params['LpuUnit_id'] = this.Wizard.params['LpuUnit_id'];
		}
		if (this.Wizard.params['Lpu_id']) {
			params['Filter_Lpu_id'] = this.Wizard.params['Lpu_id'];
		}
		//params['ListForDirection'] = 1; // список для направлений
		// тут будет другая логика фильтрации
		params['withDirection'] = this.Wizard.params.directionData['withDirection'] ? 1 : null;

		if (!getRegionNick().inlist(['ufa']) && this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
			params['WithoutChildLpuSectionAge'] = 1;
		} else {
			params['WithoutChildLpuSectionAge'] = 0;
		}
		
		this.Wizard.SelectMedPersonal.loadData({
			globalFilters: params,
			callback: function() {
				this.setFilterLpuSectionProfile(this.Wizard.SelectMedPersonal.getGrid());
			}.createDelegate(this)
		});
	},
	
	/**
	 * Обновление списка отделений
	 */
	refreshLpuSectionList: function() {
        var _this = this;
		var params = this.Filters.getFilters();
        params['FormName'] =_this.id;
		if (this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id')) {
			params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
		}
		if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id')) {
			params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
		}
		params['ListForDirection'] = 1; // список для направлений

		if (!getRegionNick().inlist(['ufa']) && this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
			params['WithoutChildLpuSectionAge'] = 1;
		} else {
			params['WithoutChildLpuSectionAge'] = 0;
		}
		
		this.Wizard.SelectLpuSection.loadData({
			globalFilters: params,
			callback: function() {
				this.setFilterLpuSectionProfile(this.Wizard.SelectLpuSection.getGrid());
			}.createDelegate(this)
		});
		this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
	},
	
	/**
	 * Обновление списка служб/услуг
	 */
	refreshMedServiceList: function() {
        var _this = this;
		var params = this.Filters.getFilters();
        params['FormName'] =_this.id;
		if (this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id')) {
			params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
		}
		if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id')) {
			params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
		}
		params['ListForDirection'] = 1; // список для направлений
		params['groupByMedService'] = 1;
		this.Wizard.SelectMedService.loadData({
			globalFilters: params,
			callback: function() {
				this.setFilterLpuSectionProfile(this.Wizard.SelectMedService.getGrid());
			}.createDelegate(this)
		});
		this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
	},

	loadResourceMedServiceGrid: function() {
		var params = {MedService_id: this.Wizard.params.directionData.MedService_id};

		if (this.Wizard.Panel.layout.activeItem.getId() == 'SelectTTRPanel') {
			params.Resource_begDate = this.Wizard.TTRDirectionPanel.date;
			params.TimetableResource_begDate = this.Wizard.TTRDirectionPanel.date;
		}

		if (this.Wizard.TTRDirectionPanel.ResourceData && this.Wizard.TTRDirectionPanel.ResourceData.UslugaComplexMedService_id) {
			params.UslugaComplexMedService_id = this.Wizard.TTRDirectionPanel.ResourceData.UslugaComplexMedService_id;
		}

		this.Wizard.ResourceMedServiceGrid.getStore().load({
			params: params,
			callback: function() {
				if (this.Wizard.ResourceMedServiceGrid.getStore().getCount() > 0) {
					var index = this.Wizard.ResourceMedServiceGrid.getStore().findBy(function(rec) { return rec.get('Resource_id') == this.Resource_id; }.createDelegate(this));

					if (index >= 0) {
						this.Wizard.ResourceMedServiceGrid.getSelectionModel().selectRow(index);
					} else {
						this.Wizard.ResourceMedServiceGrid.getSelectionModel().selectFirstRow();
					}
				} else {
					this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / 0';

					//this.Wizard.params.directionData['Resource_id'] = null;
					//this.Wizard.TTRDirectionPanel.Resource_id = null;
					this.Wizard.TTRDirectionPanel.loadSchedule(this.Wizard.TTRDirectionPanel.calendar.value);
				}
			}.createDelegate(this)
		});
	},
	
	/**
	 * Возврат на предыдущий шаг мастера
	 */
	prevStep: function()
	{
		switch(this.Wizard.params.step) {
			case "SelectLpuUnit":
				if ( this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'spec_mz'])  || this.Wizard.params.directionData['fromBj'] ) {
					this.setStep('SelectDirType');
				}
				break;
			case "SelectMedPersonal":
				this.noReload = true;
				this.setStep('SelectLpuUnit');

				break;
			case "SelectLpuSection":
				this.noReload = true;
				this.setStep('SelectLpuUnit');
			
				break;
			case "SelectMedService":
				this.noReload = true;
				this.setStep('SelectLpuUnit');
			
				break;
			case "SelectMedServiceLpuLevel":
				this.setStep('SelectDirType');
			
				break;
			case "RecordTTG":
				this.setStep('SelectMedPersonal');

				break;
			case "RecordTTGOneDay":
				this.setStep('RecordTTG', {'changeParams':false});

			case "RecordTTGInGroup":
				this.setStep('RecordTTG', {'changeParams':false});
				break;
			case "RecordTTS":
				this.setStep('SelectLpuSection');

				break;
			case "RecordTTSOneDay":
				this.setStep('RecordTTS');

				break;
			case "RecordTTMS":
				this.setStep('SelectMedService');
				break;

			case "RecordTTMSOneDay":
				this.setStep(this.Wizard.params.prevStep);
				break;

			case "RecordTTRLpuLevel":
				this.setStep('SelectMedServiceLpuLevel');
				break;
			case "RecordTTMSLpuLevel":
				this.setStep('SelectMedServiceLpuLevel');
				break;
			case "RecordTTMSVK":
				this.setStep('SelectMedServiceLpuLevel');
				break;

			case "RecordTTROneDay":
				this.setStep(this.Wizard.params.prevStep);
				break;

			default:
				
		}
	},
	
	/**
	 * Обновление заголовка окна
	 */
	refreshWindowTitle: function(params) {
		if (!params || params.changeParams == undefined || params.changeParams!==false) { // если изменять параметры заголовка не требуется, то не трогаем заголовки
			
			if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick')||(params && params.Lpu_Nick)) {
				this.Wizard.params['Lpu_Nick'] = (params && params.Lpu_Nick)?params.Lpu_Nick:this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick');
			} else if (this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_Nick')) {
				this.Wizard.params['Lpu_Nick'] = (params && params.Lpu_Nick)?params.Lpu_Nick:this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('Lpu_Nick');
			}
			this.Wizard.params['LpuUnit_Name'] = (params && params.LpuUnit_Name)?params.LpuUnit_Name:this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_Name');
			if (!this.Wizard.params.isHimSelf) {
				this.Wizard.params['MedPersonal_FIO'] = (params && params.MedPersonal_FIO) ? params.MedPersonal_FIO : this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_FIO');
			}
			this.Wizard.params['LpuSectionProfile_Name'] = (params && params.LpuSectionProfile_Name)?params.LpuSectionProfile_Name:this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_Name');
			if (this.Wizard.params['LpuSectionProfile_Name']) {
				this.Wizard.params['LpuSectionProfile_Name'] = this.Wizard.params['LpuSectionProfile_Name'].split("<br")[0];
			}
			this.Wizard.params['LpuSection_Name'] = (params && params.LpuSection_Name)?params.LpuSection_Name:this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_Name');
			this.Wizard.params['LpuUnit_Address'] = (params && params.LpuUnit_Address)?params.LpuUnit_Address:this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_Address');
			this.Wizard.params['LpuRegion_Names'] = (params && params.LpuRegion_Names)?params.LpuRegion_Names:this.Wizard.SelectMedPersonal.getSelectedParam('LpuRegion_Names');
			//log(1231231,this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_Address'))
			// бывает что нужно отображать расписание другой службы (пункта забора в частности вместо лаборатории)
			if (this.Wizard.SelectMedService.getSelectedParam('MedService_Name')) {
				var MedService_Name = this.Wizard.SelectMedService.getSelectedParam('MedService_Name');
			} else if (this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Name')) {
				MedService_Name = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Name');
			}
			this.Wizard.params['MedService_Name'] = (params && params.MedService_Name) ? params.MedService_Name : MedService_Name;
		}

		if (this.Wizard.params['Person_FIO']) {
			var title = WND_DMW + ' ' + this.Wizard.params['Person_FIO'];
		} else {
			var title = WND_DMW;
		}

		switch(this.Wizard.params.step) {
			case "SelectDirType":
				this.setTitle(title + lang['|_vyibor_tipa_napravleniya']);
				break;
			case "SelectLpuUnit":
				this.setTitle(title + ' | ' + this.Wizard.params.DirType_Name + lang['>_vyibor_podrazdeleniya']);
				break;
			case "SelectMedPersonal":
				full_title = title + ' | ' + this.Wizard.params.DirType_Name + ' ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name + ', ' + (!Ext.isEmpty(this.Wizard.params.LpuUnit_Address)?(this.Wizard.params.LpuUnit_Address):'');
				} 
				full_title +=lang['>_vyibor_vracha'];
				this.setTitle(full_title);
				break;
			case "SelectLpuSection":
				full_title = title + ' | ' + this.Wizard.params.DirType_Name + ' ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name;
					if(this.Wizard.params.LpuUnit_Address){
						full_title +=', ' + (!Ext.isEmpty(this.Wizard.params.LpuUnit_Address)?(this.Wizard.params.LpuUnit_Address):'');
					}
				} 
				full_title +=lang['>_vyibor_otdeleniya'];
				this.setTitle(full_title);
				break;
			case "SelectMedService":
				full_title = title + ' | ' + this.Wizard.params.DirType_Name + ' ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name + ', ' + (!Ext.isEmpty(this.Wizard.params.LpuUnit_Address)?(this.Wizard.params.LpuUnit_Address):'');
				} 
				full_title +=lang['>_vyibor_slujbyi_uslugi'];
				this.setTitle(full_title);
				break;
			case "SelectMedServiceLpuLevel":
				this.setTitle(title + ' | ' + this.Wizard.params.DirType_Name + lang['>_vyibor_slujbyi_uslugi']);
				break;
			case "RecordTTG":
				title += ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.MedPersonal_FIO + ((this.Wizard.params.LpuSectionProfile_Name)?' (' + this.Wizard.params.LpuSectionProfile_Name + ')':'');
				if (this.Wizard.params.LpuRegion_Names && this.Wizard.params.LpuRegion_Names != '') {
					title += ', ' + this.Wizard.params.LpuRegion_Names;
				}
				title += lang['>_vyibor_vremeni']
				this.setTitle(title);
				break;
			case "RecordTTGOneDay":
				title += ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.MedPersonal_FIO + ' (' + this.Wizard.params.LpuSectionProfile_Name + ')';
				if (this.Wizard.params.LpuRegion_Names && this.Wizard.params.LpuRegion_Names != '') {
					title += ', ' + this.Wizard.params.LpuRegion_Names;
				}
				title += ' > ' + this.Wizard.params.date + lang['>_spisok_zapisannyih'];
				this.setTitle(title);
				break;
			case "RecordTTGInGroup":
				title += ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.MedPersonal_FIO + ' (' + this.Wizard.params.LpuSectionProfile_Name + ')';
				if (this.Wizard.params.LpuRegion_Names && this.Wizard.params.LpuRegion_Names != '') {
					title += ', ' + this.Wizard.params.LpuRegion_Names;
				}
				title += ' > ' + this.Wizard.params.date + langs(' > Список записанных (множественная запись)');
				this.setTitle(title);
				break;
			case "RecordTTS":
				this.setTitle(title + ' | ' + this.Wizard.params.DirType_Name + ' ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ', ' + (!Ext.isEmpty(this.Wizard.params.LpuUnit_Address)?(this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.LpuSection_Name + ' > Выбор даты');
				break;
			case "RecordTTSOneDay":
				/*if (params.changeParams) {
				 this.Wizard.params['LpuSectionProfile_Name'] = (params && params.LpuSectionProfile_Name)?params.LpuSectionProfile_Name:this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionProfile_Name');
				 }*/
				this.setTitle(title + ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.LpuSection_Name + ' (' + this.Wizard.params.LpuSectionProfile_Name + ')' + ' > ' + this.Wizard.params.date + ' > Список записанных');
				break;
			case "RecordTTMS":
				var full_title = title + ' | ' + this.Wizard.params.DirType_Name + ' > ' + this.Wizard.params.Lpu_Nick;
				full_title += ' > ' + this.Wizard.params.MedService_Name + lang['>_vyibor_vremeni'];
				this.setTitle(full_title);
				break;
			case "RecordTTROneDay":
			case "RecordTTMSOneDay":
				this.setTitle(title + ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.MedService_Name + ' > ' + this.Wizard.params.date + ' > Список записанных');
				break;
			case "RecordTTRLpuLevel":
			case "RecordTTMSLpuLevel":
				var full_title = title + ' | ' + this.Wizard.params.DirType_Name + ' > ' + this.Wizard.params.Lpu_Nick;
				full_title += ' > ' + this.Wizard.params.MedService_Name + lang['>_vyibor_vremeni'];
				this.setTitle(full_title);
				break;
			case "RecordTTMSVK":
				var full_title = title + ' | ' + this.Wizard.params.DirType_Name + ' > ' + this.Wizard.params.Lpu_Nick;
				full_title += ' > ' + this.Wizard.params.MedService_Name + lang['>_vyibor_vremeni'];
				this.setTitle(full_title);
				break;
			default:
				
		}
	},
	
	/**
	 * Получение текущего шага мастера
	 */
	getStep: function()
	{
		return this.Wizard.params.step;
	},
	
	/**
	 * Применить фильтр
	 */
	applyFilter: function(){
        var _this = this;
		var params = this.Filters.getFilters();
		params.FormName = 'swDirectionMasterWindow';
		this.Filter_Lpu_Nick = params.Filter_Lpu_Nick;
		switch(this.Wizard.params.step) {
			case "SelectLpuUnit":
				
                params['FormName'] =_this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}

				if (!getRegionNick().inlist(['ufa']) && this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
					params['WithoutChildLpuSectionAge'] = 1;
				} else {
					params['WithoutChildLpuSectionAge'] = 0;
				}
				
				//#110233
				if(this.type=='ExtDirPriem') {
					params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
					
				//	params['Filter_LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
				//	this.Wizard.params['Filter_LpuUnit_id'] = params['Filter_LpuUnit_id'];
					
				//	params['Filter_LpuUnitType_id'] = 17;
				//	this.Wizard.params['Filter_LpuUnitType_id'] = 17;
					
					params['WithoutChildLpuSectionAge']=0;
				}//--110233
					
				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params
				});
				
				break;
			case "SelectMedPersonal":
                params['FormName'] =_this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}
				
				params['withDirection'] = this.Wizard.params.directionData['withDirection'] ? 1 : null;

				if (!getRegionNick().inlist(['ufa']) && this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
					params['WithoutChildLpuSectionAge'] = 1;
				} else {
					params['WithoutChildLpuSectionAge'] = 0;
				}
				this.Wizard.SelectLpuUnit.removeAll();
				this.Wizard.SelectMedPersonal.removeAll();

				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params,
					callback: function() {
						params.LpuUnit_id = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id') || null;

						if (!Ext.isEmpty(params.LpuUnit_id)) {
							this.Wizard.SelectMedPersonal.loadData({globalFilters: params});
						}
					}.createDelegate(this)
				});
				
				break;
			case "SelectLpuSection":
                params['FormName'] =_this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}

				if (!getRegionNick().inlist(['ufa']) && this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
					params['WithoutChildLpuSectionAge'] = 1;
				} else {
					params['WithoutChildLpuSectionAge'] = 0;
				}

				this.Wizard.SelectLpuUnit.removeAll();
				this.Wizard.SelectLpuSection.removeAll();

				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params,
					callback: function() {
						params.LpuUnit_id = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id') || null;

						if (!Ext.isEmpty(params.LpuUnit_id)) {
							this.Wizard.SelectLpuSection.loadData({globalFilters: params});
						}
					}.createDelegate(this)
				});
				
				break;
			case "SelectMedService":
                params['FormName'] =_this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}

				if (!getRegionNick().inlist(['ufa']) && this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
					params['WithoutChildLpuSectionAge'] = 1;
				} else {
					params['WithoutChildLpuSectionAge'] = 0;
				}
				
				//#110233
				if(this.type=='ExtDirPriem') {
					params['Filter_Lpu_Nick'] = this.userMedStaffFact.Lpu_Nick;
					
					params['Filter_LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
					this.Wizard.params['Filter_LpuUnit_id'] = params['Filter_LpuUnit_id'];
					
				/*	params['LpuUnit_id'] = this.userMedStaffFact.LpuUnit_id;
					this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];*/
					
					params['Filter_LpuUnitType_id'] = 17;
					this.Wizard.params['Filter_LpuUnitType_id'] = 17;
					
					params['WithoutChildLpuSectionAge']=0;
				}//--110233
					
				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params
				});
				
			/*	if(this.type=='ExtDirPriem') {
					params['Filter_LpuUnitType_id'] = null;
					this.Wizard.params['Filter_LpuUnitType_id'] = null;
				}*/
				
				params['groupByMedService'] = 1;
				this.Wizard.SelectMedService.loadData({
					globalFilters: params
				});
				
				break;
			case "SelectMedServiceLpuLevel":
                params['FormName'] =_this.id;
				params['groupByMedService'] = 1;
				this.Wizard.SelectMedServiceLpuLevel.loadData({
					globalFilters: params
				});
				
				break;
			default:
				
		}
	},
	
	/**
	 * Направление в другую МО
	 */
	addOtherLpuDirection: function() {

		var DirType_id = this.Wizard.params.directionData.DirType_id;
		// #101026 Для направлений «Направление на высокотехнологичную помощь»: Открывается форма «Направление на ВМП»
		if( DirType_id && DirType_id == 19 && getRegionNick() != 'kz'){
			
			getWnd('swDirectionOnHTMEditForm').show( this.Wizard.params.directionData );

		}else{

			var directionData = {
				userMedStaffFact: Ext.apply({}, this.userMedStaffFact),
				person: Ext.apply({}, this.Wizard.params.personData),
				direction: Ext.apply({}, this.Wizard.params.directionData),
				callback: function(data){
					this.onDirection(data);
					if (data) {
						if (data.EvnDirection_id) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								msg: lang['vyivesti_napravlenie_na_pechat'],
								title: lang['vopros'],
								icon: Ext.MessageBox.QUESTION,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										sw.Promed.Direction.print({
											EvnDirection_id: data.EvnDirection_id
										});
									}
								}.createDelegate(this)
							});
						}
						this.hide();
					}
				}.createDelegate(this),
				mode: 'nosave',
				windowId: this.getId()
			};
			switch (directionData.direction.DirType_id) {
				case 2:
				case 3:
				case 10:
					directionData.direction.LpuUnitType_SysNick = 'parka';
					break;
				case 1:
				case 6:
					directionData.direction.LpuUnitType_SysNick = 'stac';
					break;
				case 16:
				case 4:
					directionData.direction.LpuUnitType_SysNick = 'polka';
					break;
			}
			directionData.direction.LpuUnit_did = null;
			directionData.direction.isNotForSystem = true;
			sw.Promed.Direction.queuePerson(directionData);

		}
	},

	/**
	 * Открывает форму заказа услуги в параклинике
	 */
	addOrder: function(record, nextstep) {
		var callback = function(scope, id, values) {
			// здесь показываем расписание на определенную выбранную службу
			this.Wizard.params.order = values;

			// подменяем данные на выбранные
			var params = {
				order: true,
				LpuSection_id: values.LpuSection_id,
				Resource_id: values.Resource_id,
				PayType_id: values.PayType_id,
				// службу выбираем из пункта забора, если он выбран
				MedService_id: (values.MedService_pzid>0)?values.MedService_pzid:values.MedService_id,
				MedService_Nick: (values.MedService_pzid>0)?values.MedService_pzNick:values.MedService_Nick,
				UslugaComplex_id: values.UslugaComplex_id,
				UslugaComplex_Name: values.UslugaComplex_Name,
				LpuSectionProfile_id: values.LpuSectionProfile_id,
				UslugaComplexMedService_id: values.UslugaComplexMedService_id
			};

			if (!Ext.isEmpty(values.Usluga_isCito)) {
				this.Wizard.params.directionData['EvnDirection_IsCito'] = values.Usluga_isCito;
			}

			// Если выбрали пункт забора, то его надо передать в создание направления.
			if (values.MedService_pzid > 0) { // Если в расписании выбрали лабораторию, а в форме заказа изменили на ПЗ
				this.Wizard.params.directionData['MedService_pzid'] = values.MedService_pzid;
				this.Wizard.params.directionData['ignoreCanRecord'] = true;
				// делаем подмену для правильного отображения заголовка окна
				params.MedService_Name = params.MedService_Nick;
				this.Wizard.params.MedService_id = params.MedService_id;
				this.Wizard.params.MedService_Name = params.MedService_Nick;
				this.Wizard.params.UslugaComplex_id = params.UslugaComplex_id;
				this.Wizard.params.UslugaComplex_Name = params.UslugaComplex_Name;
			} else {
				this.Wizard.params.directionData['MedService_pzid'] = null;
				this.Wizard.params.directionData['ignoreCanRecord'] = null;
			}
			this.setStep(nextstep, params);

		}.createDelegate(this);
		var isReceive = this.Wizard.params.directionData['EvnDirection_IsReceive'];
		sw.Promed.Direction.createOrder(record, this.Wizard.params.personData, callback, true, null, null, null, isReceive);
    },

    _onSelectDirType: function(dirtype_code)
    {
		var base_form = this.Filters.getForm();
		// скрываем поля Тип прикрепления, Улица, Дом
		base_form.findField('LpuRegionType_id').hideContainer();
		base_form.findField('KLStreet_Name').hideContainer();
		base_form.findField('KLHouse').hideContainer();

		if (dirtype_code == '18') {
			// На консультацию в другую МИС
			if (this.Wizard.params.personData && this.Wizard.params.personData.Person_id) {
				var Person_Fio = this.Wizard.params.personData.Person_Surname + ' ' + this.Wizard.params.personData.Person_Firname + ' ' + this.Wizard.params.personData.Person_Secname;
				getWnd('swDirectionMasterMisRbWindow').show({
					personData: {
						Person_Fio: Person_Fio,
						Person_id: this.Wizard.params.personData.Person_id
					}
				});
				this.hide();
			} else {
				sw.swMsg.alert(lang['oshibka'], 'Необходимо выбрать пациента для записи на консультацию в другую МИС.');
			}
			return true;
		}

        this.Wizard.params['DirType_Code'] = dirtype_code;
        var lpu_unit_type_combo = base_form.findField('LpuUnitType_id');
        lpu_unit_type_combo.getStore().clearFilter();

		this.Wizard.ResourceMedServiceGrid.hide();
		this.Wizard.ResourceMedServiceGrid.removeAll();

        // В зависимости от типа направления доступны разные типы подразделений #21653
		switch(parseInt(dirtype_code)){
            case 1: case 5: case 6:
                // только в стационар
                lpu_unit_type_combo.getStore().filterBy(function(rec){
                    return rec.get('LpuUnitType_Code').inlist(['2','3','4','5']);
                });
                break;
			case 12:
            case 3:
                // только в поликлинику
                lpu_unit_type_combo.getStore().filterBy(function(rec){
                    return rec.get('LpuUnitType_Code').inlist(['1','7','11']);
                });

				// показываем поля Тип прикрепления, Улица, Дом
				base_form.findField('LpuRegionType_id').showContainer();
				base_form.findField('KLStreet_Name').showContainer();
				base_form.findField('KLHouse').showContainer();
                break;
            case 2:
				if (this.payType == 'oms') {
					lpu_unit_type_combo.getStore().filterBy(function(rec){
						return rec.get('LpuUnitType_Code').inlist(['1','2','3','5','4','7','11']);
					});

					// показываем поля Тип прикрепления, Улица, Дом
					base_form.findField('LpuRegionType_id').showContainer();
					base_form.findField('KLStreet_Name').showContainer();
					base_form.findField('KLHouse').showContainer();
				}
				break;
			case 4:
				// в стационар, в поликлинику
				lpu_unit_type_combo.getStore().filterBy(function(rec){
					return rec.get('LpuUnitType_Code').inlist(['1','2','3','5','4','7']);
				});

				// показываем поля Тип прикрепления, Улица, Дом
				base_form.findField('LpuRegionType_id').showContainer();
				base_form.findField('KLStreet_Name').showContainer();
				base_form.findField('KLHouse').showContainer();
                break;
            default:

                break;
        }
        // В зависимости от типа направления доступны разные типы служб
        switch(parseInt(dirtype_code)){
            case 8:
                // vk, mse
			 lpu_unit_type_combo.getStore().filterBy(function(rec){log(rec);
                    return rec.get('LpuUnitType_Code').inlist(['6']);
                });
                break;
            case 9:
				 lpu_unit_type_combo.getStore().filterBy(function(rec){log(rec);
                    return rec.get('LpuUnitType_Code').inlist(['6']);
                });
                // lab, func, pzm, reglab
                break;
            case 10:
				 lpu_unit_type_combo.getStore().filterBy(function(rec){log(rec);
                    return rec.get('LpuUnitType_Code').inlist(['6']);
                });
                // konsult
                break;
            case 11:
				 lpu_unit_type_combo.getStore().filterBy(function(rec){log(rec);
                    return rec.get('LpuUnitType_Code').inlist(['6']);
                });
                // prock, vac
                break;
        }
		// переходим к следующему шагу
		switch(true) {
			case (this.useCase.inlist(['rewrite'])
				&& 'TimetableGraf' == this.Wizard.params.directionData.type
				&& this.Wizard.params.directionData.MedStaffFact_did > 0
			):
				// открываем расписание врача
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTGDirectionPanel');
				this.Wizard.TTGDirectionPanel.setUseCase(this.useCase);
				this.doLayout();
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'polka';
				this.Wizard.params.directionData['redirectEvnDirection'] = 800; // признак перезаписи
				this.Wizard.TTGDirectionPanel.MedStaffFact_id = this.Wizard.params.directionData.MedStaffFact_did;
				this.Wizard.TTGDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTGDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTGDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTGDirectionPanel.loadSchedule(this.Wizard.TTGDirectionPanel.calendar.value);
				break;
			case (this.useCase.inlist(['rewrite'])
				&& 'TimetableMedService' == this.Wizard.params.directionData.type
				&& (this.Wizard.params.directionData.MedService_id > 0 || this.Wizard.params.directionData.UslugaComplexMedService_id > 0)
			):
				// открываем расписание службы/услуги
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSDirectionPanel');
				this.Wizard.TTMSDirectionPanel.setUseCase(this.useCase);
				this.doLayout();
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['redirectEvnDirection'] = 800; // признак перезаписи
				if (!Ext.isEmpty(this.Wizard.params.directionData.MedService_pzid)) {
					this.Wizard.TTMSDirectionPanel.MedService_id = this.Wizard.params.directionData.MedService_pzid;
				} else {
					this.Wizard.TTMSDirectionPanel.MedService_id = this.Wizard.params.directionData.MedService_id;
				}
				this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = (this.Wizard.params.directionData.isAllowRecToUslugaComplexMedService && this.Wizard.params.directionData.UslugaComplexMedService_id) ? this.Wizard.params.directionData.UslugaComplexMedService_id : null;
				this.Wizard.TTMSDirectionPanel.MedServiceData = new Object({
					'MedService_Nick': this.Wizard.params.directionData.MedService_Nick,
					'UslugaComplex_id': this.Wizard.params.directionData.UslugaComplex_id,
					'LpuSection_id': this.Wizard.params.directionData.LpuSection_did,
					'LpuSectionProfile_id': this.Wizard.params.directionData.LpuSectionProfile_id,
					'Lpu_id': this.Wizard.params.directionData.Lpu_did
				});
				this.Wizard.TTMSDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTMSDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTMSDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTMSDirectionPanel.loadSchedule(this.Wizard.TTMSDirectionPanel.calendar.value);
				break;
			case (this.useCase.inlist(['rewrite'])
				&& 'TimetableResource' == this.Wizard.params.directionData.type
				&& this.Wizard.params.directionData.Resource_id > 0
			):
				// открываем расписание службы/услуги
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('SelectTTRPanel');
				this.Wizard.ResourceMedServiceGrid.show();
				this.Wizard.TTRDirectionPanel.setUseCase(this.useCase);
				this.doLayout();
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['redirectEvnDirection'] = 800; // признак перезаписи
				//this.Wizard.TTRDirectionPanel.Resource_id = this.Wizard.params.directionData.Resource_id;
				this.Wizard.TTRDirectionPanel.MedService_id = this.Wizard.params.directionData.MedService_id;
				this.Wizard.TTRDirectionPanel.ResourceData = new Object({
					'Resource_Name': '',
					'MedService_Nick': this.Wizard.params.directionData.MedService_Nick,
					'UslugaComplex_id': this.Wizard.params.directionData.UslugaComplex_id,
					'UslugaComplexMedService_id': this.Wizard.params.directionData.UslugaComplexMedService_id,
					'LpuSection_id': this.Wizard.params.directionData.LpuSection_id,
					'LpuSectionProfile_id': this.Wizard.params.directionData.LpuSectionProfile_id,
					'Lpu_id': this.Wizard.params.directionData.Lpu_id
				});
				this.Wizard.TTRDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTRDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTRDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.loadResourceMedServiceGrid();
				break;
			case (this.useCase.inlist(['rewrite'])
				&& 'TimetableStac' == this.Wizard.params.directionData.type
				&& this.Wizard.params.directionData.LpuSection_did > 0
			):
				// открываем расписание службы
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTSDirectionPanel');
				this.Wizard.TTSDirectionPanel.setUseCase(this.useCase);
				this.doLayout();
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'stac';
				this.Wizard.params.directionData['redirectEvnDirection'] = 800; // признак перезаписи
				this.Wizard.TTSDirectionPanel.LpuSection_id = this.Wizard.params.directionData.LpuSection_did;
				this.Wizard.TTSDirectionPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTSDirectionPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTSDirectionPanel.userMedStaffFact = this.userMedStaffFact;
				this.Wizard.TTSDirectionPanel.loadSchedule(this.Wizard.TTSDirectionPanel.calendar.value);
				break;
			case (this.useCase.inlist(['rewrite'])):
				sw.swMsg.alert(lang['oshibka'], lang['proizoshla_oshibka_vyibora_raspisaniya']);
				this.hide();
				break;
			case (dirtype_code.toString()=='5'):
				// экстренная госпитализация
				getWnd('swEvnDirectionEditWindow').show({
					action: 'add',
					callback: function(data) {
						this.onDirection(data);
						if (data) {
							this.hide();
						}
					}.createDelegate(this),
					Person_id: this.Wizard.params.personData.Person_id,
					Person_Surname: this.Wizard.params.personData.Person_Surname,
					Person_Firname: this.Wizard.params.personData.Person_Firname,
					Person_Secname: this.Wizard.params.personData.Person_Secname,
					Person_Birthday: this.Wizard.params.personData.Person_Birthday,
					formParams: {
						DirType_id: 5
						,EvnDirection_pid:this.Wizard.params.personData.EvnSection_pid
						,Lpu_did: getGlobalOptions().lpu_id
						,Lpu_sid: getGlobalOptions().lpu_id
						,EvnDirection_IsReceive: this.Wizard.params.directionData['EvnDirection_IsReceive']
					}
				});
                break;
			case ((
					dirtype_code.toString().inlist(['1','4']) ||
					dirtype_code.toString().inlist(['2']) && this.payType == 'oms'
				)
				&& this.Wizard.params.directionData.LpuUnitType_did
				&& this.Wizard.params.directionData.LpuUnit_did
				&& this.Wizard.params.directionData.LpuSection_did
				&& this.Wizard.params.directionData.Lpu_did
			):
				// сразу после выбора типа направления переходить к расписанию выбранного отделения
				this.Wizard.params.Lpu_id = this.Wizard.params.directionData.Lpu_did;
				this.Wizard.params.LpuUnit_id = this.Wizard.params.directionData.LpuUnit_did;
				this.setStep('RecordTTS');
				break;
			case (dirtype_code.toString().inlist(['2']) && this.payType == 'money'):
				this.setStep('SelectMedServiceLpuLevel');
				this.findById('dmwMedService_CaptionForm').enable();
				this.findById('dmwMedService_CaptionForm').show();
				break;
			default:
				// В зависимости от типа направления выбираем подразделение или службу
				switch(parseInt(dirtype_code)){
					case 6: case 8: case 9: case 10: case 11: case 15: case 25:
						this.setStep('SelectMedServiceLpuLevel');
						this.findById('dmwMedService_CaptionForm').enable();
						this.findById('dmwMedService_CaptionForm').show();
						break;
					default:
						this.setStep('SelectLpuUnit');
						this.findById('dmwMedService_CaptionForm').disable();
						this.findById('dmwMedService_CaptionForm').hide();
						break;
				}
				break;
		}
    },
	/**
	 * Отображает расписание врача, под которым работаем
	 */
	recordHimSelf: function(args) {
		// у нас есть отделение врача и сам врач
		var params = {
			Lpu_id: this.userMedStaffFact.Lpu_id,
			LpuUnit_id: this.userMedStaffFact.LpuUnit_id,
			LpuSection_id: this.userMedStaffFact.LpuSection_id,
			MedPersonal_id: getGlobalOptions().medpersonal_id,
			MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id,
			LpuSectionProfile_id: this.userMedStaffFact.LpuSectionProfile_id,
			LpuSection_Name: this.userMedStaffFact.LpuSection_Name,
			MedPersonal_FIO: this.userMedStaffFact.MedPersonal_FIO,
			MedService_id: null,
			MedService_Nick: null
		};
		this.Wizard.params = Ext.apply(this.Wizard.params,params);
		this.Wizard.params.step = 'RecordTTG';
		this.Wizard.params.isHimSelf = true;

		// сразу заполним directionData
		this.Wizard.params.directionData['EvnDirection_IsReceive'] = 2; // к себе
		this.Wizard.params.directionData['DirType_id'] = 16; // На поликлинический прием
		this.Wizard.params.directionData['Lpu_sid'] = this.userMedStaffFact.Lpu_id;
		this.Wizard.params.directionData['LpuSection_id'] = this.userMedStaffFact.LpuSection_id;
		this.Wizard.params.directionData['MedStaffFact_id'] = this.userMedStaffFact.MedStaffFact_id;
		this.Wizard.params.directionData['From_MedStaffFact_id'] = params.MedStaffFact_id;
		this.Wizard.params.directionData['LpuUnit_did'] = params.LpuUnit_id;
		this.Wizard.params.directionData['Lpu_did'] = params.Lpu_id;
		this.Wizard.params.directionData['MedPersonal_did'] = params.MedPersonal_id;
		this.Wizard.params.directionData['MedPersonal_id'] = params.MedPersonal_id;
		this.Wizard.params.directionData['LpuSection_did'] = params.LpuSection_id;

		this.Wizard.params.directionData['LpuSectionProfile_id'] = params.LpuSectionProfile_id;
		this.Wizard.params.directionData['LpuSectionProfile_did'] = params.LpuSectionProfile_id;
		this.Wizard.params.directionData['Diag_id'] = args.Diag_id || '';

		this.Wizard.params.directionData['EvnDirection_pid'] = args.EvnDirection_pid;

		params.Lpu_Nick = this.userMedStaffFact.Lpu_Nick;
		params.LpuUnit_Name = this.userMedStaffFact.LpuUnit_Name;

		this.setStep('RecordTTG', params);
	},
	openMPQueueWindow: function(gridPanel) {
		var grid = gridPanel.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || !record.get('Lpu_id') || !(record.get('LpuSectionProfile_id') || record.get('MedService_id'))) {
			return false;
		}

		var params = {
			Lpu_id: record.get('Lpu_id'),
			userMedStaffFact: this.userMedStaffFact,
			ARMType: this.userMedStaffFact?this.userMedStaffFact.ARMType:null,
			dateRangeMode: 'allTime',
			resetRecordDate: false
		};

		if(this.Wizard.params.personData){// #142955
			params.personData = this.Wizard.params.personData;
		}else{
			params.dateRangeMode = 'day';
		}

		if (record.get('MedService_id')) {
			params.MedService_id = record.get('MedService_id');
		} else if (record.get('LpuSectionProfile_id')) {
			params.LpuSectionProfile_id = record.get('LpuSectionProfile_id');
		}

		getWnd('swMPQueueWindow').show(params);

		return true;
	},
	getQueueForGroup: function(options) {
		var params = {
			Filter_Lpu_id: options.Lpu_id
		};

		if (options.LpuSectionProfile_id) {
			params.LpuSectionProfile_id = options.LpuSectionProfile_id;
		}
		if (options.MedService_id) {
			params.MedService_id = options.MedService_id;
		}

		var win = this;
		var group = Ext.get(options.groupId);
		var getQueueEl = Ext.get(Ext.DomQuery.selectNode('.dm-get-queue-cnt', group.dom));
		var showQueueEl = Ext.get(Ext.DomQuery.selectNode('.dm-show-queue-cnt', group.dom));
		var expiredWarningEl = Ext.get(Ext.DomQuery.selectNode('.dm-group-hd-warning', group.dom));

		var tpl = new Ext.XTemplate('{cnt} пациентов');

		var url = '';
		if (params.LpuSectionProfile_id) {
			url = '/?c=Reg&m=getLpuUnitQueue';
		} else if (params.MedService_id) {
			url = '/?c=Reg&m=getMedServiceQueue';
		} else {
			return false;
		}

		Ext.Ajax.request({
			params: params,
			url: url,
			success: function(response) {
				var response_obj = Ext.util.JSON.decode(response.responseText);

				if (Ext.isArray(response_obj)) {
					if (response_obj.length == 0) {
						getQueueEl.setDisplayed(false);
						showQueueEl.setDisplayed(true);

						showQueueEl.down('.dm-queue-cnt').dom.innerText = tpl.apply({cnt: 0});
					} else if (response_obj.length > 0) {
						var resp = response_obj[0];

						getQueueEl.setDisplayed(false);
						showQueueEl.setDisplayed(true);

						showQueueEl.down('.dm-queue-cnt').dom.innerText = tpl.apply(resp);

						expiredWarningEl.setDisplayed(false);

						var days = 14;
						if (win.Wizard.params.directionData['DirType_id'] && win.Wizard.params.directionData['DirType_id'].inlist([16, 3])) { // На поликлинический прием и На консультацию
							days = parseInt(getGlobalOptions().promed_waiting_period_polka);
						} else if (win.Wizard.params.directionData['DirType_id'] && win.Wizard.params.directionData['DirType_id'].inlist([1, 5])) { // На госпитализацию плановую и На госпитализацию экстренную
							days = parseInt(getGlobalOptions().promed_waiting_period_stac);
						}

						if (days && !isNaN(days) && resp.days && resp.days > days) {
							var daysText = days + ' ' + ru_word_case('день', 'дня', 'дней', days);
							var text = 'Есть направления с периодом ожидания более ' + daysText + '!';

							expiredWarningEl.setDisplayed(true);
							expiredWarningEl.dom.innerHTML = text;
						}
					}
				}
			}
		});
		return true;
	},
	doFilterDirTypeList: function() {
		var grid = this.Wizard.SelectDirType.getGrid();

		grid.getStore().filterBy(function(rec) {
			if (this.dirTypeCodeExcList.length > 0) {
				if (rec.get('DirType_Code').inlist(this.dirTypeCodeExcList)) {
					return false;
				}
			}

			if (this.dirTypeCodeIncList.length > 0) {
				if (!rec.get('DirType_Code').inlist(this.dirTypeCodeIncList)) {
					return false;
				}
			}

			if (this.dirTypeCodeListByPayType.length > 0) {
				if (!rec.get('DirType_Code').inlist(this.dirTypeCodeListByPayType)) {
					return false;
				}
			}

			return true;
		}.createDelegate(this));
	},
	togglePayType: function() {
		if (this.payType == 'oms') {
			this.setPayType('money');
		} else {
			this.setPayType('oms');
		}
	},
	setPayType: function(payType) {
		var button = this.Wizard.SelectDirType.getAction('action_toggle_pay_type');

		this.payType = payType;

		this.dirTypeCodeListByPayType = [];

		switch(this.payType) {
			case 'oms':
				button.setText('Платно');
				//this.dirTypeCodeListByPayType = [1,2,3,4,5,6,9,10,11,12];
				break;
			case 'money':
				button.setText('ОМС');
				this.dirTypeCodeListByPayType = [2];
				break;
			default:
				button.setText('undefined');
		}

		this.doFilterDirTypeList();
	},
    show: function()
    {
		sw.Promed.swDirectionMasterWindow.superclass.show.apply(this, arguments);

		this.type = (arguments[0] && arguments[0].type)? arguments[0].type:'';
		this.payType = 'oms';
		this.dirTypeCodeListByPayType = [];
		// очищаем
		this.Wizard.params = new Object({
			'step': 'SelectDirType'
		});

        this.userMedStaffFact = sw.Promed.MedStaffFactByUser.last;
		if (  arguments[0] && arguments[0].userMedStaffFact )
		{
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		if ( !this.userMedStaffFact && Ext.isEmpty(getGlobalOptions().medpersonal_id) ) {
			this.userMedStaffFact = new Object();

			this.userMedStaffFact.ARMType = null;
			this.userMedStaffFact.ARMType_id = null;
			this.userMedStaffFact.Lpu_id = getGlobalOptions().lpu_id;
			this.userMedStaffFact.Lpu_Nick = getGlobalOptions().lpu_nick;
		}

		// ФИО пациента. Желательно их передавать на форму поиска человека.
		if ( arguments[0] && typeof arguments[0].personData == 'object' ) {
			this.Wizard.params.personData = arguments[0].personData;

			var personFIO = '';

            if ( this.Wizard.params.personData.Person_Surname ) {
                personFIO = personFIO + this.Wizard.params.personData.Person_Surname + ' ';
            }

            if ( this.Wizard.params.personData.Person_Firname ) {
                personFIO = personFIO + this.Wizard.params.personData.Person_Firname + ' ';
            }

            if ( this.Wizard.params.personData.Person_Secname ) {
                personFIO = personFIO + this.Wizard.params.personData.Person_Secname + ' ';
            }

            this.Wizard.params['Person_FIO'] = personFIO;
		} else {
			this.Wizard.params.personData = null;
			this.Wizard.params['Person_FIO'] = null;
		}
		
		if (isMseDepers()) {
			this.Wizard.params['Person_FIO'] = '***';
		}

		if (
			this.userMedStaffFact.ARMType && 
			this.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms'])
		) {
			this.Filter_Lpu_Nick = (this.Wizard.params.personData && this.Wizard.params.personData.AttachLpu_Name && this.Wizard.params.personData.AttachLpu_Name != lang['ne_prikreplen'])
				? this.Wizard.params.personData.AttachLpu_Name
				: null; 
		} else {
			this.Filter_Lpu_Nick = arguments[0].Filter_Lpu_Nick || this.userMedStaffFact.Lpu_Nick;; 
		}

		if (!this.Wizard.SelectDirType.getAction('action_toggle_pay_type')) {
			this.Wizard.SelectDirType.addActions({
				name: 'action_toggle_pay_type',
				text: 'Платно',
				handler: function() {
					this.togglePayType();
				}.createDelegate(this)
			}, 7);
		}
		this.setPayType('oms');

		if (!this.Wizard.SelectMedPersonal.getAction('action_mpqueue')) {
			this.Wizard.SelectMedPersonal.addActions({
				name: 'action_mpqueue',
				text: 'Журнал направлений',
				handler: function() {
					this.openMPQueueWindow(this.Wizard.SelectMedPersonal);
				}.createDelegate(this)
			});
		}
		if (!this.Wizard.SelectLpuSection.getAction('action_mpqueue')) {
			this.Wizard.SelectLpuSection.addActions({
				name: 'action_mpqueue',
				text: 'Журнал направлений',
				handler: function() {
					this.openMPQueueWindow(this.Wizard.SelectLpuSection);
				}.createDelegate(this)
			});
		}
		if (!this.Wizard.SelectMedService.getAction('action_mpqueue')) {
			this.Wizard.SelectMedService.addActions({
				name: 'action_mpqueue',
				text: 'Журнал направлений',
				handler: function() {
					this.openMPQueueWindow(this.Wizard.SelectMedService);
				}.createDelegate(this)
			});
		}
		if (!this.Wizard.SelectMedServiceLpuLevel.getAction('action_mpqueue')) {
			this.Wizard.SelectMedServiceLpuLevel.addActions({
				name: 'action_mpqueue',
				text: 'Журнал направлений',
				handler: function() {
					this.openMPQueueWindow(this.Wizard.SelectMedServiceLpuLevel);
				}.createDelegate(this)
			});
		}

		this.Wizard.ResourceMedServiceGrid.hide();
		this.Wizard.ResourceMedServiceGrid.removeAll();

		this.Wizard.TTGDirectionPanel.calendar.reset();
		this.Wizard.TTSDirectionPanel.calendar.reset();
		this.Wizard.TTRDirectionPanel.calendar.reset();
		this.Wizard.TTMSDirectionPanel.calendar.reset();
		this.Wizard.TTMSVKDirectionPanel.calendar.reset();
		this.Wizard.params.directionData = (arguments[0] && typeof arguments[0].directionData == 'object') ? arguments[0].directionData : new Object();
		this.onDirection = (arguments[0] && typeof arguments[0].onDirection == 'function') ? arguments[0].onDirection : Ext.emptyFn;
        this.onHide =  (arguments[0] && typeof arguments[0].onHide == 'function') ? arguments[0].onHide : Ext.emptyFn;
        this.dirTypeCodeExcList = (arguments[0] && arguments[0].dirTypeCodeExcList) || [];
        this.dirTypeCodeIncList = (arguments[0] && arguments[0].dirTypeCodeIncList) || [];
		this.useCase = (arguments[0] && arguments[0].useCase) || 'undefined';

		if (this.userMedStaffFact && this.userMedStaffFact.ARMType && this.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms'])) {
			this.dirTypeCodeIncList = ['2', '3','12'];
		}
		if (this.userMedStaffFact.ARMType == 'regpol' || this.userMedStaffFact.ARMType == 'regpol6') {
			// нельзя создавать направления на ВК / МСЭ из АРМа регистратора
			this.dirTypeCodeExcList = ['8'];
		}

        this.dirTypeCodeExcList.push('13'); // создать направление с типом "На удаленную консультацию" может только лечащий врач из случая лечения
        this.dirTypeCodeExcList.push('23'); // На МСЭ
        this.isDead = (arguments[0] && arguments[0].isDead) || false;
		// очищаем фильтры
		this.Filters.getForm().reset();
		this.LpuSectionProfile_id = null;
		this.LpuUnitType_id = null;
		this.Lpu_Nick = null;

		if ( arguments[0] && arguments[0].LpuSectionProfile_id ) {
			this.Filters.getForm().findField('LpuSectionProfile_id').setValue(arguments[0].LpuSectionProfile_id);
			this.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
		}
		if ( arguments[0] && arguments[0].LpuUnitType_id ) {
			this.Filters.getForm().findField('LpuUnitType_id').setValue(arguments[0].LpuUnitType_id);
			this.LpuUnitType_id = arguments[0].LpuUnitType_id;
		}

		this.buttons[0].show();
		this.buttons[1].show();

		if ( arguments[0] && typeof arguments[0].RedirTimetableData == 'object' ) {
			// в форму можно передать сразу данные для перенаправления, тогда переход сразу на шаг с выбором куда направить
			var ttdata = arguments[0].RedirTimetableData;
			this.Wizard.params.directionData['DirType_id'] = ttdata.DirType_id;
			this.Wizard.params.directionData['EvnDirection_id'] = ttdata.EvnDirection_id;
			this.Wizard.params.directionData['EvnDirection_Num'] = ttdata.EvnDirection_Num;
			this.Wizard.params['DirType_id'] = ttdata.DirType_id;
			this.Wizard.params['DirType_Code'] = ttdata.DirType_Code;
			this.Wizard.params['DirType_Name'] = ttdata.DirType_Name;
			this._onSelectDirType(ttdata.DirType_Code);
			//нужно в гриде типов направлений также выделить запись

			this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
			this.Lpu_Nick = this.userMedStaffFact.Lpu_Nick;
			if (ttdata.LpuSectionProfile_id) {
				this.Filters.getForm().findField('LpuSectionProfile_id').setValue(ttdata.LpuSectionProfile_id);
				this.LpuSectionProfile_id = arguments[0].LpuSectionProfile_id;
			}

			var filter = this.Filters.getFilters();
			filter.DirType_id = ttdata.DirType_id;
			filter.DirType_Code = ttdata.DirType_Code;
			this.Wizard.SelectLpuUnit.loadData({
				globalFilters: filter
			});
		} else if ( arguments[0] && typeof arguments[0].TimetableData == 'object' ) {
			// в форму можно передать сразу данные о том какое расписание открывать, тогда переход сразу на шаг с расписанием.
			var ttdata = arguments[0].TimetableData;

			switch (ttdata.type) {
				case 'TimetableGraf':
					this.Filters.hide();
					this.Wizard.Panel.layout.setActiveItem('TTGDirectionPanel');
					this.doLayout();

					this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'polka';
					this.Wizard.TTGDirectionPanel.MedStaffFact_id = ttdata.MedStaffFact_id;
					this.Wizard.params.directionData['EvnDirection_id'] = ttdata.EvnDirection_id;
					this.Wizard.params.directionData['EvnDirection_Num'] = ttdata.EvnDirection_Num;
					this.Wizard.params.directionData['EvnDirection_setDate'] = ttdata.EvnDirection_setDate;
					this.Wizard.params.directionData['EvnDirection_IsAuto'] = ttdata.EvnDirection_IsAuto;
					this.Wizard.params.directionData['EvnDirection_IsReceive'] = ttdata.EvnDirection_IsReceive;
					this.Wizard.params.directionData['MedService_id'] = ttdata.MedService_id;
					this.Wizard.params.directionData['MedService_Nick'] = ttdata.MedService_Nick;
					this.Wizard.params.directionData['MedServiceType_SysNick'] = ttdata.MedServiceType_SysNick;
					this.Wizard.params.directionData['MedPersonal_did'] = ttdata.MedPersonal_did;
					this.Wizard.params.directionData['LpuUnit_did'] = ttdata.LpuUnit_did;
					this.Wizard.params.directionData['Lpu_did'] = ttdata.Lpu_did;
					this.Wizard.params.directionData['LpuSection_did'] = ttdata.LpuSection_did;
					this.Wizard.params.directionData['LpuSectionProfile_id'] = ttdata.LpuSectionProfile_id;
					this.Wizard.params.directionData['DirType_id'] = ttdata.DirType_id;
					this.Wizard.params.directionData['ARMType_id'] = ttdata.ARMType_id;
					this.Wizard.params.directionData['From_MedStaffFact_id'] = ttdata.From_MedStaffFact_id;
					this.Wizard.params.directionData['MedStaffFact_id'] = ttdata.MedStaffFact_id;
					if ('record_from_queue' == this.useCase) {
						this.Wizard.params.directionData['EvnQueue_id'] = ttdata.EvnQueue_id;
						this.Wizard.params.directionData['redirectEvnDirection'] = 600; // признак записи из очереди
					}

					this.Wizard.TTGDirectionPanel.personData = this.Wizard.params.personData;
					this.Wizard.TTGDirectionPanel.directionData = this.Wizard.params.directionData;
					this.Wizard.TTGDirectionPanel.userMedStaffFact = this.userMedStaffFact;
					this.Wizard.TTGDirectionPanel.loadSchedule(this.Wizard.TTGDirectionPanel.calendar.value);
					break;

				case 'TimetableResource':
					this.Filters.hide();
					this.Wizard.Panel.layout.setActiveItem('SelectTTRPanel');
					this.Wizard.ResourceMedServiceGrid.show();
					this.doLayout();

					this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
					this.Wizard.TTRDirectionPanel.Resource_id = null;
					this.Wizard.TTRDirectionPanel.ResourceData = new Object({
						'Resource_Name': '',
						'MedService_Nick': ttdata.MedService_Nick,
						'UslugaComplex_id': ttdata.UslugaComplex_id,
						'UslugaComplexMedService_id': ttdata.UslugaComplexMedService_id,
						'LpuSection_id': ttdata.LpuSection_id,
						'LpuSectionProfile_id': ttdata.LpuSectionProfile_id,
						'Lpu_id': ttdata.Lpu_id
					});
					this.Wizard.params.directionData['EvnDirection_id'] = ttdata.EvnDirection_id;
					this.Wizard.params.directionData['EvnDirection_Num'] = ttdata.EvnDirection_Num;
					this.Wizard.params.directionData['EvnDirection_setDate'] = ttdata.EvnDirection_setDate;
					this.Wizard.params.directionData['EvnDirection_IsAuto'] = ttdata.EvnDirection_IsAuto;
					this.Wizard.params.directionData['EvnDirection_IsReceive'] = ttdata.EvnDirection_IsReceive;
					this.Wizard.params.directionData['MedService_id'] = ttdata.MedService_id;
					this.Wizard.params.directionData['MedService_Nick'] = ttdata.MedService_Nick;
					this.Wizard.params.directionData['MedServiceType_SysNick'] = ttdata.MedServiceType_SysNick;
					this.Wizard.params.directionData['MedPersonal_did'] = ttdata.MedPersonal_did;
					this.Wizard.params.directionData['LpuUnit_did'] = ttdata.LpuUnit_did;
					this.Wizard.params.directionData['Lpu_did'] = ttdata.Lpu_did;
					this.Wizard.params.directionData['LpuSection_did'] = ttdata.LpuSection_did;
					this.Wizard.params.directionData['LpuSectionProfile_id'] = ttdata.LpuSectionProfile_id;
					this.Wizard.params.directionData['DirType_id'] = ttdata.DirType_id;
					this.Wizard.params.directionData['ARMType_id'] = ttdata.ARMType_id;
					this.Wizard.params.directionData['From_MedStaffFact_id'] = ttdata.From_MedStaffFact_id;
					this.Wizard.params.directionData['MedStaffFact_id'] = ttdata.MedStaffFact_id;
					//this.Wizard.params.directionData['Resource_id'] = ttdata.Resource_id;
					if ('record_from_queue' == this.useCase) {
						this.Wizard.params.directionData['EvnQueue_id'] = ttdata.EvnQueue_id;
						this.Wizard.params.directionData['redirectEvnDirection'] = 600; // признак записи из очереди
					}
					this.Wizard.TTRDirectionPanel.setUseCase(this.useCase);
					this.Wizard.TTRDirectionPanel.personData = this.Wizard.params.personData;
					this.Wizard.TTRDirectionPanel.directionData = this.Wizard.params.directionData;
					this.Wizard.TTRDirectionPanel.userMedStaffFact = this.userMedStaffFact;
					this.loadResourceMedServiceGrid();
					break;

				case 'TimetableMedService':
					this.Filters.hide();
					this.Wizard.Panel.layout.setActiveItem('TTMSDirectionPanel');
					this.doLayout();

					this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
					this.Wizard.TTMSDirectionPanel.MedService_id = ttdata.MedService_id;
					this.Wizard.TTMSDirectionPanel.UslugaComplexMedService_id = (ttdata.isAllowRecToUslugaComplexMedService && ttdata.UslugaComplexMedService_id) ? ttdata.UslugaComplexMedService_id : null;
					this.Wizard.TTMSDirectionPanel.MedServiceData = new Object({
						'MedService_Nick': ttdata.MedService_Nick,
						'UslugaComplex_id': ttdata.UslugaComplex_id,
						'LpuSection_id': ttdata.LpuSection_id,
						'LpuSectionProfile_id': ttdata.LpuSectionProfile_id,
						'Lpu_id': ttdata.Lpu_id
					});
					this.Wizard.params.directionData['EvnDirection_id'] = ttdata.EvnDirection_id;
					this.Wizard.params.directionData['EvnDirection_Num'] = ttdata.EvnDirection_Num;
					this.Wizard.params.directionData['EvnDirection_setDate'] = ttdata.EvnDirection_setDate;
					this.Wizard.params.directionData['EvnDirection_IsAuto'] = ttdata.EvnDirection_IsAuto;
					this.Wizard.params.directionData['EvnDirection_IsReceive'] = ttdata.EvnDirection_IsReceive;
					this.Wizard.params.directionData['MedService_id'] = ttdata.MedService_id;
					this.Wizard.params.directionData['MedService_Nick'] = ttdata.MedService_Nick;
					this.Wizard.params.directionData['MedServiceType_SysNick'] = ttdata.MedServiceType_SysNick;
					this.Wizard.params.directionData['MedPersonal_did'] = ttdata.MedPersonal_did;
					this.Wizard.params.directionData['LpuUnit_did'] = ttdata.LpuUnit_did;
					this.Wizard.params.directionData['Lpu_did'] = ttdata.Lpu_did;
					this.Wizard.params.directionData['LpuSection_did'] = ttdata.LpuSection_did;
					this.Wizard.params.directionData['LpuSectionProfile_id'] = ttdata.LpuSectionProfile_id;
					this.Wizard.params.directionData['DirType_id'] = ttdata.DirType_id;
					this.Wizard.params.directionData['ARMType_id'] = ttdata.ARMType_id;
					this.Wizard.params.directionData['From_MedStaffFact_id'] = ttdata.From_MedStaffFact_id;
					this.Wizard.params.directionData['MedStaffFact_id'] = ttdata.MedStaffFact_id;
					if ('record_from_queue' == this.useCase) {
						this.Wizard.params.directionData['EvnQueue_id'] = ttdata.EvnQueue_id;
						this.Wizard.params.directionData['redirectEvnDirection'] = 600; // признак записи из очереди
					}
					this.Wizard.TTMSDirectionPanel.personData = this.Wizard.params.personData;
					this.Wizard.TTMSDirectionPanel.directionData = this.Wizard.params.directionData;
					this.Wizard.TTMSDirectionPanel.userMedStaffFact = this.userMedStaffFact;

					if(ttdata.order){
						//пока не знаю куда лучше пристроить эти параметры для физ кабинета
						this.Wizard.TTMSDirectionPanel.order = ttdata.order;
						this.Wizard.TTMSDirectionPanel.onDirection = this.onDirection;
						this.Wizard.params.directionData['EvnDirection_pid'] = ttdata.EvnDirection_pid;
					}
					this.Wizard.TTMSDirectionPanel.loadSchedule(this.Wizard.TTMSDirectionPanel.calendar.value);
					break;

				case 'TimetableStac':
					this.Filters.hide();
					this.Wizard.Panel.layout.setActiveItem('TTSDirectionPanel');
					this.doLayout();

					this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'stac';
					this.Wizard.TTSDirectionPanel.LpuSection_id = ttdata.LpuSection_did;
					this.Wizard.params.directionData['EvnDirection_id'] = ttdata.EvnDirection_id;
					this.Wizard.params.directionData['EvnDirection_Num'] = ttdata.EvnDirection_Num;
					this.Wizard.params.directionData['EvnDirection_setDate'] = ttdata.EvnDirection_setDate;
					this.Wizard.params.directionData['EvnDirection_IsAuto'] = ttdata.EvnDirection_IsAuto;
					this.Wizard.params.directionData['EvnDirection_IsReceive'] = ttdata.EvnDirection_IsReceive;
					this.Wizard.params.directionData['MedService_id'] = ttdata.MedService_id;
					this.Wizard.params.directionData['MedService_Nick'] = ttdata.MedService_Nick;
					this.Wizard.params.directionData['MedServiceType_SysNick'] = ttdata.MedServiceType_SysNick;
					this.Wizard.params.directionData['MedPersonal_did'] = ttdata.MedPersonal_did;
					this.Wizard.params.directionData['LpuUnit_did'] = ttdata.LpuUnit_did;
					this.Wizard.params.directionData['Lpu_did'] = ttdata.Lpu_did;
					this.Wizard.params.directionData['LpuSection_did'] = ttdata.LpuSection_did;
					this.Wizard.params.directionData['LpuSectionProfile_id'] = ttdata.LpuSectionProfile_id;
					this.Wizard.params.directionData['DirType_id'] = ttdata.DirType_id;
					this.Wizard.params.directionData['ARMType_id'] = ttdata.ARMType_id;
					this.Wizard.params.directionData['From_MedStaffFact_id'] = ttdata.From_MedStaffFact_id;
					this.Wizard.params.directionData['MedStaffFact_id'] = ttdata.MedStaffFact_id;
					if ('record_from_queue' == this.useCase) {
						this.Wizard.params.directionData['EvnQueue_id'] = ttdata.EvnQueue_id;
						this.Wizard.params.directionData['redirectEvnDirection'] = 600; // признак записи из очереди
					}

					this.Wizard.TTSDirectionPanel.personData = this.Wizard.params.personData;
					this.Wizard.TTSDirectionPanel.directionData = this.Wizard.params.directionData;
					this.Wizard.TTSDirectionPanel.userMedStaffFact = this.userMedStaffFact;
					this.Wizard.TTSDirectionPanel.loadSchedule(this.Wizard.TTSDirectionPanel.calendar.value);
					break;
			}

			this.buttons[0].hide();
			this.buttons[1].hide();
		} else if ( arguments[0] && typeof arguments[0].dirTypeData == 'object' ) {
			// В форму можно передать сразу тип направления, тогда произойдет переход на следующий шаг
			if (arguments[0].dirTypeData.EvnDirection_id) {
				this.Wizard.params.directionData['EvnDirection_id'] = arguments[0].dirTypeData.EvnDirection_id;
			}
			if (arguments[0].dirTypeData.EvnDirection_pid) {
				this.Wizard.params.directionData['EvnDirection_pid'] = arguments[0].dirTypeData.EvnDirection_pid;
			}
			if (arguments[0].dirTypeData.DopDispInfoConsent_id) {
				this.Wizard.params.directionData['DopDispInfoConsent_id'] = arguments[0].dirTypeData.DopDispInfoConsent_id;
			}
			if (arguments[0].dirTypeData.EvnQueue_id) {
				this.Wizard.params.directionData['EvnQueue_id'] = arguments[0].dirTypeData.EvnQueue_id;
			}
			if (arguments[0].dirTypeData.EvnDirection_Num) {
				this.Wizard.params.directionData['EvnDirection_Num'] = arguments[0].dirTypeData.EvnDirection_Num;
			}
			if (arguments[0].dirTypeData.EvnDirection_setDate) {
				this.Wizard.params.directionData['EvnDirection_setDate'] = arguments[0].dirTypeData.EvnDirection_setDate;
			}
            this.Wizard.params.directionData['DirType_id'] = arguments[0].dirTypeData.DirType_id;
            this.Wizard.params['DirType_id'] = arguments[0].dirTypeData.DirType_id;
			this.Wizard.params['DirType_Name'] = arguments[0].dirTypeData.DirType_Name;
            this._onSelectDirType(arguments[0].dirTypeData.DirType_Code);
			if (!this.useCase.inlist(['rewrite'])) {
				this.Filters.getForm().findField('Lpu_Nick').setValue(this.Filter_Lpu_Nick);
				this.Lpu_Nick = this.Filter_Lpu_Nick;
				var filter = this.Filters.getFilters();
				filter.DirType_id = arguments[0].dirTypeData.DirType_id;
				filter.DirType_Code = arguments[0].dirTypeData.DirType_Code;
				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: filter
				});
			}
			// #110233
			if(this.type.inlist(['ExtDirDiag','ExtDirLab'])) {
				if(!this.Wizard.params.directionData) this.Wizard.params.directionData=new Object;
				this.Wizard.params.directionData['EvnDirection_IsReceive'] = 2;
				if(this.userMedStaffFact.ARMType.inlist(['func','lab', 'reglab'])) {
					this.Lpu_Nick = this.userMedStaffFact.Lpu_Nick;
					
					this.Filter_Lpu_Nick = this.Lpu_Nick;
					
					this.Wizard.params['DirType_id'] = 10;
					this.Wizard.params['DirType_Name'] = langs('На исследование');
					this._onSelectDirType(9);
				}
			}
		} else if(this.type == 'ExtDirKVS' || this.type == 'ExtDirPriem' ) {
			this.dirTypeCodeIncList = ['1', '5'];
			if(!this.Wizard.params.directionData) this.Wizard.params.directionData=new Object;
			this.Wizard.params.directionData['EvnDirection_IsReceive'] = 2;
			//this.Wizard.params.directionData['MedPersonal_id'] = this.userMedStaffFact.MedPersonal_id;
			this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'stac';
			this.setStep('SelectDirType');
		} else if ( this.type == 'HimSelf' ) { // врач записывает к себе
			this.recordHimSelf(arguments[0]);
		} else if ( this.type == 'SMO' ) { // просмотр расписания из АРМ СМО и АРМ ТФОМС
			this.Wizard.params['DirType_Name'] = lang['na_gospitalizatsiyu_planovuyu'];
			this.setStep('SelectLpuUnit');
			this.refreshWindowTitle();
		} else if ( this.type.inlist(['RecordTTGOneDay','RecordTTGInGroup','RecordTTSOneDay','RecordTTMSOneDay','RecordTTROneDay']) ) {
			this.openDayListOnly(this.type, arguments[0].date);
		} else if (this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms']) && !this.Wizard.params.directionData['fromBj']) {
			this.Wizard.params['DirType_id'] = 16;
			this.Wizard.params['DirType_Name'] = lang['na_poliklinicheskiy_priem'];
			this._onSelectDirType(12);
		} else {
			// При открытии формы по умолчанию возвращаемся на первый шаг
			this.setStep('SelectDirType');
			this.refreshWindowTitle();
		}

		this.Filters.getForm().findField('LpuSectionProfile_id').setDisabled(this.useCase.inlist(['record_from_queue']) && this.Filters.getForm().findField('LpuSectionProfile_id').getValue() > 0);
		this.Filters.getForm().findField('Lpu_Nick').setDisabled(
			(this.useCase.inlist(['record_from_queue']) && this.Filters.getForm().findField('Lpu_Nick').getValue())
			|| (this.Wizard.params.directionData['EvnDirection_IsReceive'] && 2 == this.Wizard.params.directionData['EvnDirection_IsReceive'] && this.Filter_Lpu_Nick)
			// || (!this.Wizard.params.directionData['withDirection'] && (this.userMedStaffFact.ARMType == 'regpol' || this.userMedStaffFact.ARMType == 'regpol6'))
			|| (this.userMedStaffFact.ARMType == 'paidservice')
		);
		if (this.useCase.inlist(['record_from_queue','rewrite']) && this.Wizard.params['DirType_id']) {
			this.buttons[1].hide();
		}
		if (this.useCase.inlist(['rewrite']) && this.Wizard.params['DirType_id']) {
			this.buttons[0].hide();
		}
	},
	
	initComponent: function()
	{
		var win = this;
		
		this.Wizard = new Object({
			/**
			 * Параметры мастера
			 */
			params: null,
			/**
			 * Главная панель мастера
			 */
			Panel: null,
			
			/**
			 * Список подразделений ЛПУ
			 */
			SelectLpuUnit: null,
			/**
			 * Список отделений в подразделении
			 */
			SelectLpuSection: null,
			/**
			 * Список врачей в подразделении
			 */
			SelectMedPersonal: null,
			/**
			 * Список служб/услуг в подразделении
			 */
			SelectMedService: null,
			/**
			 * Расписание врача на 2 недели
			 */
			TTGDirectionPanel: null,
			/**
			 * Расписание коек в отделении на 3 недели
			 */
			TTSDirectionPanel: null,
			/**
			 * Расписание ресурсов на 2 недели
			 */
			TTRDirectionPanel: null,
			/**
			 * Расписание служб/услуг на 2 недели
			 */
			TTMSDirectionPanel: null
		});
		var win = this;
		// Панель расписания для записи в поликлинику
		this.Wizard.TTGDirectionPanel = new sw.Promed.swTTGDirectionPanel({
			id:'TTGDirectionPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(data) {
				if(win.fromEMK)
					win.onSaveRecord(data);
				// @todo openGroupListTTG
				if (data.params && data.params.time && this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) {
					this.openDayListTTG(Date.parseDate(data.params.time, 'd.m.Y H:i').format('d.m.Y'));	
				}
			}.createDelegate(this),
			onDirection: function(data) {
                this.Wizard.TTGDirectionPanel.loadSchedule(this.Wizard.TTGDirectionPanel.calendar.value);
                this.onDirection(data);
				if (data && !this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) {
					win.hide();
				}
			}.createDelegate(this),
			onQueue: function(answer) {
				if(answer && answer.success) {
					//Ext.Msg.alert('Сообщение', 'Пациент <b>'+this.Wizard.params.personData.Person_Surname+' '+this.Wizard.params.personData.Person_Firname+' '+this.Wizard.params.personData.Person_Secname+' '+'</b><br/> успешно поставлен в очередь!');
                    this.onDirection(answer);
					win.hide();
				} else if (!answer) { // если вообще нет ответа, выводим свою ошибку
					Ext.Msg.alert('Ошибка', 'При выполнении операции постановки в очередь<br/>произошла ошибка.');//: <b>отсутствует ответ сервера</b>
				}
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this),
			clearTime: function(time_id, evndirection_id, inet_user) {

				return sw.Promed.Direction.cancel({
					cancelType: 'cancel',
					ownerWindow: this,
					EvnDirection_id: evndirection_id,
					TimetableGraf_id: time_id,
					formType: 'DirMaster',
					callback: function (cfg) {
						this.loadSchedule();
					}.createDelegate(this)
				});
			}
		});

		// Панель расписания на один день для записи в поликлинику
		this.Wizard.TTGRecordOneDayPanel = new sw.Promed.swTTGRecordOneDayPanel({
			id:'TTGRecordOneDayPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) {
				Ext.getCmp('TTGRecordOneDayPanel').loadSchedule();
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this),
			printTAP: function(person_id, TimetableGraf_id)
			{
				var params = new Object();
				params.type = 'EvnPL';
				params.formType = 'DirMaster';
				params.personId = person_id;
				params.TimetableGraf_id = TimetableGraf_id;

				switch ( getRegionNick() ) {
					case 'ekb':
						printBirt({
							'Report_FileName': 'tap_66_timetable.rptdesign',
							'Report_Params': '&TimeTableGraf_id=' + TimetableGraf_id,
							'Report_Format': 'pdf'
						});
						break;

					case 'ufa':
						params.MedPersonal_id = this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_id');
						params.LpuSectionProfile_id = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_id');
						getWnd('swEvnPLBlankSettingsWindow').show(params);
						break;

					default:
						printEvnPLBlank(params);
						break;
				}
			}.createDelegate(this),
			printDir: function(EvnDirection_id)
			{
				if (!EvnDirection_id) {
					return false;
				}
				
				var params = {
					Evn_id: EvnDirection_id,
					fromBj: true,
					EvnClass_id: 27
				};
				
				getWnd('swPrintTemplateSelectWindow').show(params);
			},
			printMenu: function(el, person_id, TimetableGraf_id, EvnDirection_id)
			{
				if ( !person_id ) {
					return false;
				}
				
				var ttgMenu = new Ext.menu.Menu({
					items: [
						{
							id: 'print-tap',
							text: 'Печать ТАП'
						}, {
							id: 'print-dir',
							text: 'Печать шаблона документа'
						}
					],
					listeners: {
						itemclick: function(item) {
							switch (item.id) {
								case 'print-tap':
									this.printTAP(person_id, TimetableGraf_id);
								break;
										
								case 'print-dir':
									this.printDir(EvnDirection_id);
								break;
							}
						}.createDelegate(this)
					}
				});
				
				ttgMenu.show(el);
			},
			clearTime: function(time_id, evndirection_id, inet_user) {
				
				return sw.Promed.Direction.cancel({
					cancelType: 'cancel',
					ownerWindow: this,
					EvnDirection_id: evndirection_id,
					TimetableGraf_id: time_id,
					formType: 'DirMaster',
					callback: function (cfg) {
						this.loadSchedule();
					}.createDelegate(this)
				});
			}
		});
		// Панель расписания на группу для записи в поликлинику
		this.Wizard.TTGRecordInGroupPanel = new sw.Promed.swTTGRecordInGroupPanel({
			id:'TTGRecordInGroupPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) {
				Ext.getCmp('TTGRecordInGroupPanel').loadSchedule();
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this),
			printTAP: function(person_id, TimetableGraf_id)
			{
				var params = new Object();
				params.type = 'EvnPL';
				params.formType = 'DirMaster';
				params.personId = person_id;
				params.TimetableGraf_id = TimetableGraf_id;

				switch ( getRegionNick() ) {
					case 'ekb':
						printBirt({
							'Report_FileName': 'tap_66_timetable.rptdesign',
							'Report_Params': '&TimeTableGraf_id=' + TimetableGraf_id,
							'Report_Format': 'pdf'
						});
						break;

					case 'ufa':
						params.MedPersonal_id = this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_id');
						params.LpuSectionProfile_id = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_id');
						getWnd('swEvnPLBlankSettingsWindow').show(params);
						break;

					default:
						printEvnPLBlank(params);
						break;
				}
			}.createDelegate(this),
			printDir: function(EvnDirection_id)
			{
				if (!EvnDirection_id) {
					return false;
				}

				var params = {
					Evn_id: EvnDirection_id,
					fromBj: true,
					EvnClass_id: 27
				};

				getWnd('swPrintTemplateSelectWindow').show(params);
			},
			printMenu: function(el, person_id, TimetableGraf_id, EvnDirection_id)
			{
				if ( !person_id ) {
					return false;
				}

				var ttgMenu = new Ext.menu.Menu({
					items: [
						{
							id: 'print-tap',
							text: 'Печать ТАП'
						}, {
							id: 'print-dir',
							text: 'Печать шаблона документа'
						}
					],
					listeners: {
						itemclick: function(item) {
							switch (item.id) {
								case 'print-tap':
									this.printTAP(person_id, TimetableGraf_id);
									break;

								case 'print-dir':
									this.printDir(EvnDirection_id);
									break;
							}
						}.createDelegate(this)
					}
				});

				ttgMenu.show(el);
			},
			clearTime: function(time_id, evndirection_id, inet_user, person_id,time_reclist_id) {

				return sw.Promed.Direction.cancel({
					cancelType: 'cancel',
					ownerWindow: this,
					EvnDirection_id: evndirection_id,
					TimetableGraf_id: time_id,
					TimetableGrafRecList_id: time_reclist_id,
					formType: 'DirMaster',
					person_id: person_id,
					callback: function (cfg) {
						this.loadSchedule();
					}.createDelegate(this)
				});
			}
		});
		// Панель расписания для записи в стационар
		this.Wizard.TTSDirectionPanel = new sw.Promed.swTTSDirectionPanel({
			id:'TTSDirectionPanel',
			frame: false,
			border: false,
			region: 'center',
			onDirection: function(data) {
				this.Wizard.TTSDirectionPanel.loadSchedule(this.Wizard.TTSDirectionPanel.calendar.value);
                this.onDirection(data);
				if (data) {
					win.hide();
				}
			}.createDelegate(this),
			onQueue: function(answer) {
				if(answer && answer.success) {
					//Ext.Msg.alert('Сообщение', 'Пациент <b>'+this.Wizard.params.personData.Person_Surname+' '+this.Wizard.params.personData.Person_Firname+' '+this.Wizard.params.personData.Person_Secname+' '+'</b><br/> успешно поставлен в очередь!');
                    this.onDirection(answer);
					win.hide();
				} else if (!answer) { // если вообще нет ответа, выводим свою ошибку
					Ext.Msg.alert('Ошибка', 'При выполнении операции постановки в очередь<br/>произошла ошибка.');//: <b>отсутствует ответ сервера</b>
				}
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});

		// Панель расписания на один день для записи в стационар
		this.Wizard.TTSRecordOneDayPanel = new sw.Promed.swTTSRecordOneDayPanel({
			id:'TTSRecordOneDayPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) {
				Ext.getCmp('TTSRecordOneDayPanel').loadSchedule();
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});

		// Панель расписания для записи на ресурс
		this.Wizard.TTRDirectionPanel = new sw.Promed.swTTRDirectionPanel({
			id:'TTRDirectionPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(data) {
				if (data.params && data.params.time && this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) {
					this.openDayListTTR(Date.parseDate(data.params.time, 'd.m.Y H:i').format('d.m.Y'));	
				}
			}.createDelegate(this),
			onDateChange: function() {
				if (this.useCase.inlist(['rewrite','record_from_queue'])) {
					this.loadResourceMedServiceGrid();
				} else {
					this.Wizard.TTRDirectionPanel.loadSchedule(this.Wizard.TTRDirectionPanel.calendar.value);
				}
			}.createDelegate(this),
			onDirection: function(data) {
				this.Wizard.TTRDirectionPanel.loadSchedule(this.Wizard.TTRDirectionPanel.calendar.value);
                this.onDirection(data);
				if (data && !this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) {
					win.hide();
				}
			}.createDelegate(this),
			onQueue: function(answer) {
				if(answer && answer.success) {
					//Ext.Msg.alert('Сообщение', 'Пациент <b>'+this.Wizard.params.personData.Person_Surname+' '+this.Wizard.params.personData.Person_Firname+' '+this.Wizard.params.personData.Person_Secname+' '+'</b><br/> успешно поставлен в очередь!');
                    this.onDirection(answer);
					win.hide();
				} else if (!answer) { // если вообще нет ответа, выводим свою ошибку
					Ext.Msg.alert('Ошибка', 'При выполнении операции постановки в очередь<br/>произошла ошибка.');//: <b>отсутствует ответ сервера</b>
				}
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});

		// Панель расписания для записи на службу/услугу
		this.Wizard.TTMSDirectionPanel = new sw.Promed.swTTMSDirectionPanel({
			id:'TTMSDirectionPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(data) {
				if (data.params && data.params.time && this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) {
					this.openDayListTTMS(Date.parseDate(data.params.time, 'd.m.Y H:i').format('d.m.Y'));	
				}
			}.createDelegate(this),
			onDirection: function(data) {
				this.Wizard.TTMSDirectionPanel.loadSchedule(this.Wizard.TTMSDirectionPanel.calendar.value);
                this.onDirection(data);
				if (data && !this.userMedStaffFact.ARMType.inlist(['regpol', 'regpol6', 'callcenter', 'smo', 'tfoms'])) {
					win.hide();
				}
			}.createDelegate(this),
			onQueue: function(answer) {
				if(answer && answer.success) {
					//Ext.Msg.alert('Сообщение', 'Пациент <b>'+this.Wizard.params.personData.Person_Surname+' '+this.Wizard.params.personData.Person_Firname+' '+this.Wizard.params.personData.Person_Secname+' '+'</b><br/> успешно поставлен в очередь!');
                    this.onDirection(answer);
                    if (this.Wizard.params && this.Wizard.params.order) {
	                    if (this.Wizard.params.order.HIVContingentTypeFRMIS_id || this.Wizard.params.order.HormonalPhaseType_id || this.Wizard.params.order.CovidContingentType_id) {
		                    Ext.Ajax.request({
			                    params: {
				                    EvnDirection_id: answer.EvnDirection_id,
				                    HIVContingentTypeFRMIS_id: this.Wizard.params.order.HIVContingentTypeFRMIS_id,
				                    CovidContingentType_id: this.Wizard.params.order.CovidContingentType_id,
				                    HormonalPhaseType_id: this.Wizard.params.order.HormonalPhaseType_id
			                    },
			                    url: '/?c=PersonDetailEvnDirection&m=save'
		                    });
	                    }
                    }
					win.hide();
				} else if (!answer) { // если вообще нет ответа, выводим свою ошибку
					Ext.Msg.alert('Ошибка', 'При выполнении операции постановки в очередь<br/>произошла ошибка.');//: <b>отсутствует ответ сервера</b>
				}
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});

		// Панель расписания на один день для записи на службу/услугу
		this.Wizard.TTMSRecordOneDayPanel = new sw.Promed.swTTMSRecordOneDayPanel({
			id:'TTMSRecordOneDayPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) {
				Ext.getCmp('TTMSRecordOneDayPanel').loadSchedule();
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});
		
		
		// Панель расписания для записи на службу/услугу уровня ЛПУ
		this.Wizard.TTMSVKDirectionPanel = new sw.Promed.swTTMSDirectionPanel({
			id:'TTMSVKDirectionPanel',
			frame: false,
			border: false,
			region: 'center',
			onDirection: function(data) {
				this.Wizard.TTMSVKDirectionPanel.loadSchedule(this.Wizard.TTMSVKDirectionPanel.calendar.value);
                this.onDirection(data);
				if (data) {
					win.hide();
				}
			}.createDelegate(this),
			onQueue: function(answer) {
				if(answer && answer.success) {
					//Ext.Msg.alert('Сообщение', 'Пациент <b>'+this.Wizard.params.personData.Person_Surname+' '+this.Wizard.params.personData.Person_Firname+' '+this.Wizard.params.personData.Person_Secname+' '+'</b><br/> успешно поставлен в очередь!');
                    this.onDirection(answer);
					win.hide();
				} else if (!answer) { // если вообще нет ответа, выводим свою ошибку
					Ext.Msg.alert('Ошибка', 'При выполнении операции постановки в очередь<br/>произошла ошибка.');//: <b>отсутствует ответ сервера</b>
				}
			}.createDelegate(this),
			/**
			 * Освобождение времени
			 */
			clearTime: function(time_id) 
			{
				this.getLoadMask(lang['osvobojdenie_zapisi']).show();
				Ext.Ajax.request({
					url: '/?c=Mse&m=clearTimeMSOnEvnPrescrVK',
					params: {
						TimetableMedService_id: time_id
					},
					callback: function(o, s, r) {
						this.getLoadMask().hide();
						if(s) {
							this.Wizard.TTMSVKDirectionPanel.loadSchedule(this.Wizard.TTMSVKDirectionPanel.calendar.value);
						}
					}.createDelegate(this)
				});
			},
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});

		// Панель расписания на один день для записи на ресурс
		this.Wizard.TTRRecordOneDayPanel = new sw.Promed.swTTRRecordOneDayPanel({
			id:'TTRRecordOneDayPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) {
				Ext.getCmp('TTRRecordOneDayPanel').loadSchedule();
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
		});
		
		this.Filters = new Ext.FormPanel(
		{
			region: 'north',
			border: false,
			frame: true,
			//defaults: {bodyStyle:'background:#DFE8F6;'},
			xtype: 'form',
			autoHeight: true,
			layout: 'column',
			//style: 'padding: 5px;',
			bbar:
			[{
				tabIndex: TABINDEX_DMW+13,
				xtype: 'button',
				id: 'dmwBtnMPSearch',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function()
				{
					this.applyFilter();
				}.createDelegate(this)
			},
			{
				tabIndex: TABINDEX_DMW+14,
				xtype: 'button',
				id: 'dmwBtnMPClear',
				text: lang['sbros'],
				iconCls: 'resetsearch16',
				handler: function()
				{
					// Очистка полей фильтра И перезагрузка
					this.Filters.clearFilters(true);
				}.createDelegate(this)
			},
			{
				xtype: 'tbseparator'
			}
			],
			items:
			[{
				layout: 'form',
				columnWidth: .25,
				labelAlign: 'right',
				labelWidth: 120,
				items: 
				[
				{
					fieldLabel: lang['profil'],
					anchor:'100%',
					tabIndex: TABINDEX_DMW+1,
					hiddenName: 'LpuSectionProfile_id',
					lastQuery: '',
					width : 200,
					listWidth : 300,
					xtype: 'swlpusectionprofilecombo',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this),
						'change': function(combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function(rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
						},
						'select': function(combo, record, index) {
							if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
								win.Filters.getForm().findField('includeDopProfiles').enable();
							}
							else {
								win.Filters.getForm().findField('includeDopProfiles').setValue(false);
								win.Filters.getForm().findField('includeDopProfiles').disable();
							}
						}
					}
				},
				{
					boxLabel: 'Учитывать доп. профили',
					fieldLabel: '',
					labelSeparator: '',
					name: 'includeDopProfiles',
					tabIndex: TABINDEX_DMW+4,
					xtype: 'checkbox'
				},
				{
					fieldLabel: lang['nas_punkt'],
					allowBlank: true,
					anchor:'100%',
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+7,
					name: 'KLTown_Name',
					width : 200,
					xtype: 'textfield',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				},
				{
					fieldLabel: lang['tip_mo'],
					tabIndex: TABINDEX_DMW+10,
					anchor:'100%',
					hiddenName: 'LpuAgeType_id',
					lastQuery: '',
					xtype: 'swlpuagetypecombo',
					id: 'LpuAgeTypeId',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this),
						'select': function (obj, value)
						{
							Ext.getCmp('lpusearchcombo').clearValue();
						}.createDelegate(this)
					}
				}]
			}, 
			{
				layout: 'form',
				columnWidth: .25,
				labelAlign: 'right',
				labelWidth: 125,
				items: 
				[
				{
					fieldLabel: lang['fio_vracha'],
					allowBlank: true,
					anchor:'100%',
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+2,
					name: 'MedPersonal_FIO',
					width : 200,
					//typeAhead: true,
					xtype: 'textfield',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				},
				{
					fieldLabel: lang['tip_podrazdeleniya'],
					tabIndex: TABINDEX_DMW+5,
					anchor:'100%',
					hiddenName: 'LpuUnitType_id',
					lastQuery: '',
					xtype: 'swlpuunittypecombo',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				},
				{
					fieldLabel: lang['ulitsa'],
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+8,
					anchor:'100%',
					name: 'KLStreet_Name',
					xtype: 'textfield',
					listeners:
					{
						'keypress': function (inp, e)
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				},
				{
					fieldLabel: lang['tip_prikrepleniya'],
					tabIndex: TABINDEX_DMW+11,
					anchor:'100%',
					comboSubject: 'LpuRegionType',
					hiddenName: 'LpuRegionType_id',
					lastQuery: '',
					xtype: 'swcommonsprcombo',
					listeners:
					{
						'keypress': function (inp, e)
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				}]
			}, 
			{
				layout: 'form',
				columnWidth: .25,
				labelAlign: 'right',
				labelWidth: 90,
				items: 
				[
				{
					fieldLabel: lang['mo'],
					allowBlank: true,
					ctxSerach: true,
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+3,
					hiddenName: 'Lpu_Nick',
					name:'Lpu_Nick',
					anchor:'100%',
					// typeAhead: true,
					xtype: 'swmosearchcombo',
					id: 'lpusearchcombo',
					listeners:
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this),
						'expand': function (field) {
							var value = Ext.getCmp('LpuAgeTypeId').value;
							var cmp = Ext.getCmp('lpusearchcombo');
							if(value != '') {
								cmp.store.filter('mesagelputype_id', value);
							} else {
								cmp.store.clearFilter();
							}
						}.createDelegate(this)
					}
				},
				{
					fieldLabel: lang['adres_mo'],
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+6,
					anchor:'100%',
					hiddenName: 'LpuUnit_Address',
					id: 'LpuUnit_Address',
					xtype: 'textfield',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				},
				{
					fieldLabel: lang['dom'],
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+9,
					anchor:'100%',
					name: 'KLHouse',
					xtype: 'textfield',
					listeners:
					{
						'keypress': function (inp, e)
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				}]
			},{
				layout: 'form',
				columnWidth: .25,
				labelAlign: 'right',
				labelWidth: 90,
				id: 'dmwMedService_CaptionForm',
				items: 
				[
				{
					fieldLabel: lang['slujba'],
					allowBlank: true,
					enableKeyEvents: true,
					tabIndex: TABINDEX_DMW+6,
					name: 'MedService_Caption',
					id: 'dmwMedService_Caption',
					anchor:'100%',
					xtype: 'textfield',
					listeners: 
					{
						'keypress': function (inp, e) 
						{
							if (e.getKey() == Ext.EventObject.ENTER)
							{
								this.applyFilter();
							}
						}.createDelegate(this)
					}
				}]
			}],
			
			/**
			 * Очистка фильтров с применением к спискам
			 */
			clearFilters: function(scheduleLoad)
			{
				this.Filters.getForm().reset();
				// восстанавливаем фильтры, установленные при открытии окна
				this.Filters.getForm().findField('LpuUnitType_id').setValue(this.LpuUnitType_id);
				this.Filters.getForm().findField('LpuSectionProfile_id').setValue(this.LpuSectionProfile_id);
				//#110233
				if (this.type.inlist(['ExtDir', 'ExtDirPriem', 'ExtDirKVS', 'ExtDirDiag', 'ExtDirLab'])) {
					this.Filters.getForm().findField('Lpu_Nick').setValue(this.userMedStaffFact.Lpu_Nick);
				}//--110233
				//this.Filters.getForm().findField('Lpu_Nick').setValue(this.Lpu_Nick); // #73273
				this.applyFilter();
				
			}.createDelegate(this),
			
			/**
			 * Получаем установленные фильтры
			 */
			getFilters: function(){
				var base_form = this.getForm();
				return new Object({
					start: 0,
					limit: 100,
					Filter_Lpu_Nick: base_form.findField('Lpu_Nick').getValue(),
					Filter_LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
					Filter_includeDopProfiles: base_form.findField('includeDopProfiles').getValue() == true ? 1 : 0,
					Filter_MedPersonal_FIO: base_form.findField('MedPersonal_FIO').getValue(),
					Filter_LpuUnitType_id: base_form.findField('LpuUnitType_id').getValue(),
					Filter_LpuAgeType_id: base_form.findField('LpuAgeType_id').getValue(),
					Filter_LpuUnit_Address: base_form.findField('LpuUnit_Address').getValue(),
					Filter_LpuRegionType_id: base_form.findField('LpuRegionType_id').getValue(),
					Filter_KLTown_Name: base_form.findField('KLTown_Name').getValue(),
					Filter_KLStreet_Name: base_form.findField('KLStreet_Name').getValue(),
					Filter_KLHouse: base_form.findField('KLHouse').getValue(),
					MedService_Caption: base_form.findField('MedService_Caption').getValue(),
					ARMType: win.userMedStaffFact.ARMType
				});
			}
		});
		
		this.Wizard.SelectDirType = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_DirType',
			region: 'center',
			object: 'DirType',
			border: true,
			dataUrl: C_REG_DIRTYPELIST,
			toolbar: true,
			autoLoadData: false,
			paging: false,
			isScrollToTopOnLoad: false,

			stringfields:
			[
				{name: 'DirType_id', type: 'int', header: 'ID', key: true},
				{name: 'DirType_Code', hidden: true, isparams: true},
				{name: 'DirType_Name', id: 'autoexpand', header: lang['tip_napravleniya']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, 
					handler: function() {

						// В зависимости от типа выбранного типа направления делаем разное
						var dirtype_code = this.Wizard.SelectDirType.getSelectedParam('DirType_Code');
                        this.Wizard.params['DirType_id'] = this.Wizard.SelectDirType.getSelectedParam('DirType_id');
                        this.Wizard.params['DirType_Name'] = this.Wizard.SelectDirType.getSelectedParam('DirType_Name');
                        this._onSelectDirType(dirtype_code);

						this.Wizard.SelectDirType.getGrid().getStore().each(function(r) 
						{
							r.commit();
						});
					}.createDelegate(this) 
				},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				var grid = this.Wizard.SelectDirType.getGrid(), index;

				win.doFilterDirTypeList();

				// После того как загрузили данные, надо снова выбрать предыдущую запись
				if ((this.Wizard.params.DirType_id) && (isData))
				{
					GridAtRecord(grid, 'DirType_id', this.Wizard.params.DirType_id, 2);
				}
				this.refreshWindowTitle();
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		this.Wizard.SelectLpuUnit = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_LpuUnit',
			region: 'center',
			object: 'LpuUnit',
			border: true,
			dataUrl: C_REG_DIRLULIST,
			toolbar: false,
			autoLoadData: false,
			pageSize: 100,
			paging: true,
			root: 'data',
			totalProperty: 'totalCount',
			isScrollToTopOnLoad: false,

			stringfields:
			[
				{name: 'LpuUnit_id', type: 'int', header: 'ID', key: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuUnitType_SysNick', hidden: true, isparams: true},
				{name: 'Lpu_Nick', width: 250, header: lang['mo']},
				{name: 'LpuUnit_Name', id: 'autoexpand', header: lang['podrazdelenie']},
				{name: 'LpuUnit_Address', width: 250, header: lang['adres']},
				{name: 'LpuUnit_Phone', width: 250, header: lang['telefonyi']},
				{name: 'FreeTime', width: 180, header: lang['pervoe_svobodnoe_vremya'], hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, 
					handler: function(params) {

						// В зависимости от типа выбранного подразделения делаем разное
						var unit_type = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnitType_SysNick');
						switch(unit_type){
							case 'polka': case 'fap': case 'ccenter':
								this.setStep('SelectMedPersonal', params);
								break;
							case 'stac': case 'dstac': case 'hstac': case 'pstac': 
								this.setStep('SelectLpuSection', params);
								break;
							case 'parka':
							default:
								this.setStep('SelectMedService', params);
								break;
						}

						this.Wizard.SelectLpuUnit.getGrid().getStore().each(function(r) 
						{
							r.commit();
						});
					}.createDelegate(this) 
				},
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				// После того как загрузили данные, надо снова выбрать предыдущую запись
				if (isData)
				{
					if ( this.Wizard.params.LpuUnit_id ) {
						var selectedLpuUnitExists = false;

						this.Wizard.SelectLpuUnit.getGrid().getStore().each(function(rec) {
							if ( rec.get('LpuUnit_id') == this.Wizard.params.LpuUnit_id ) {
								selectedLpuUnitExists = true;
							}
						}.createDelegate(this));

						if ( selectedLpuUnitExists == false ) {
							this.Wizard.params.LpuUnit_id = this.Wizard.SelectLpuUnit.getGrid().getStore().getAt(0).get('LpuUnit_id');
						}
					}

					GridAtRecord(this.Wizard.SelectLpuUnit.getGrid(), 'LpuUnit_id', this.Wizard.params.LpuUnit_id, 2);
				}
				this.setFilterLpuSectionProfile();
				this.refreshWindowTitle();
			}.createDelegate(this),
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getSelectionModel().getSelected();
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		// Дополнительный хак для подсветки текущих выбранных подразделений
		this.Wizard.SelectLpuUnit.getGrid().view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('LpuUnit_id') == this.Wizard.params.LpuUnit_id)
					cls = cls+'x-grid-rowselect x-grid-rowbackgreen ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}.createDelegate(this)
		});

		var profileGroupRenderer = function(group) {
			var record = group.rs[0];
			var text = '';

			if (Ext.isEmpty(record.get('LpuSectionProfile_Name'))) {
				text += '<span class="dm-group-hd-name">Без профиля</span>';
			} else {
				text += '<span class="dm-group-hd-name">'+record.get('LpuSectionProfile_Name').trim()+'</span>';
			}

			if (!Ext.isEmpty(record.get('LpuSectionProfile_id'))) {
				var onclick = 'onclick="getWnd(\'swDirectionMasterWindow\').getQueueForGroup({'+
					'Lpu_id: '+record.get('Lpu_id')+','+
					'LpuSectionProfile_id: '+record.get('LpuSectionProfile_id')+','+
					'groupId: \''+group.groupId+'\''+
				'});"';

				text += '&nbsp;&nbsp;';
				text += '<span class="dm-link dm-get-queue-cnt" '+onclick+'>Показать очередь</span>';

				text += '<span class="dm-show-queue-cnt" style="display: none;">';
				text += 'Очередь: ';
				text += '<span class="dm-queue-cnt"></span>';
				text += '</span>';

				text += '&nbsp;&nbsp;';
				text += '<span class="dm-group-hd-warning" style="display: none;">';
				text += '</span>';
			}
			text += '</span>';

			return text;
		}.createDelegate(this);

		if(!getWnd('swWorkPlaceMZSpecWindow').isVisible())
			var recordLinkTpl = new Ext.XTemplate(
				'<table style="width: 100%;"><tr>',
				'<td style="overflow: hidden; text-overflow: ellipsis;">{value}</td>',
				'<td style="width: 55px;"><span class="dm-link" onClick="Ext.getCmp(\'{gridPanelId}\').doRecord(\'{id}\');">Записать<span></td>',
				'</tr></table>'
			);
		else
			var recordLinkTpl = new Ext.XTemplate(
				'<table style="width: 100%;"><tr>',
				'</tr></table>'
			);

		this.Wizard.SelectMedPersonal = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_MedPersonal',
			region: 'center',
			object: 'MedPersonal',
			border: true,
			dataUrl: C_REG_DIRMSFLIST,
			toolbar: true,
			autoLoadData: false,
			isScrollToTopOnLoad: false,
			useEmptyRecord: false,

			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			doGroupStart: function(buf, group) {
				group.groupId = Ext.id();
				group.cls = "dm-group";

				group.rs.forEach(function(record) {
					record._groupId = group.groupId;
				});

				group.text = profileGroupRenderer(group);

				tpl = new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd {cls}-hd" style="{style}"><div>', '{text}', '</div></div>',
					'<div id="{groupId}-bd" class="x-grid-group-body">'
				);

				buf[buf.length] = tpl.apply(group);
			},
			interceptMouse: function(e) {
				var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
				var link = e.getTarget('.dm-link', this.mainBody);
				if(hd && !link){
					e.stopEvent();
					this.toggleGroup(hd.parentNode);
				}
			},

			stringfields:
			[
				{name: 'MedStaffFact_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'MedPersonal_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuSectionAge_id', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', group: true, isparams: true},
				{name: 'LpuSectionProfile_Name', hidden: true, sort: true, direction: 'ASC'},
				{name: 'LpuSectionLpuSectionProfileList', hidden: true},
				{name: 'Comments', width: 24, header: '...'},
				{name: 'MedPersonal_FIO', width: 200, header: lang['vrach']},
				{name: 'LpuRegion_Names', width: 100, header: lang['uchastki']},
				{name: 'LpuSectionAge_Name', width: 120, header: lang['vozrastnaya_gruppa']},
				{name: 'LpuSection_Name', id: 'autoexpand', header: lang['otdelenie'], renderer: function(value, meta, record) {
					return Ext.isEmpty(record.id)?'':recordLinkTpl.apply({
						gridPanelId: 'dmwWizard_MedPersonal',
						value: !Ext.isEmpty(value)?value:'',
						id: record.id
					});
				}},
				{name: 'MainLpuSectionProfile_Name', header: 'Основной профиль отделения', type: 'string'},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, handler: function() {
					this.Wizard.SelectMedPersonal.doRecord();
				}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_print',
					menuConfig: {
						printPatientList: {name: 'printPatientList', text: 'Печать списка пациентов', handler: function() { this.Wizard.SelectMedPersonal.printPatientList() }.createDelegate(this)}
					}
				}
			],
			onLoadData: function(isData)
			{
				// После того как загрузили данные, надо снова выбрать предыдущую запись
				if ((this.Wizard.params.MedStaffFact_id) && (isData))
				{
					GridAtRecord(this.Wizard.SelectMedPersonal.getGrid(), 'MedStaffFact_id', this.Wizard.params.MedStaffFact_id, 2);
				}
				this.setFilterLpuSectionProfile();
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				this.selectedId = !Ext.isEmpty(record.id)?record.id:null;
				this.record = !Ext.isEmpty(record) ? record : null;
				if (win.userMedStaffFact && win.userMedStaffFact.ARMType && win.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms']) || record.get('Lpu_id') == win.userMedStaffFact.Lpu_id) {
					this.getAction('action_mpqueue').enable();
				} else {
					this.getAction('action_mpqueue').disable();
				}
			},

			selectedId: null,
			doRecord: function(id) {
				if (!Ext.isEmpty(id)) {
					this.selectedId = id;
				}
				win.setStep('RecordTTG');
			},
			printPatientList: function() {
				if (Ext.isEmpty(this.selectedId) || Ext.isEmpty(this.record.data)) {
					return false;
				}

				var d = new Date(),
					MedStaffFact_id = this.record.data.MedStaffFact_id,
					id_salt = Math.random(),
					win_id = 'print_pac_list' + Math.floor(id_salt * 10000);

				var datestring = ("0" + d.getDate()).slice(-2) + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." +
					d.getFullYear();


				window.open('/?c=TimetableGraf&m=printPacList&Day=' + datestring + '&MedStaffFact_id=' + MedStaffFact_id + '&isPeriod=' + 2, win_id);
			},
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getStore().getById(this.selectedId);
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		this.Wizard.SelectLpuSection = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_LpuSection',
			region: 'center',
			object: 'LpuSection',
			border: true,
			dataUrl: C_REG_DIRLSLIST,
			toolbar: true,
			autoLoadData: false,
			useEmptyRecord: false,

			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			doGroupStart: function(buf, group) {
				group.groupId = Ext.id();
				group.cls = "dm-group";

				group.rs.forEach(function(record) {
					record._groupId = group.groupId;
				});

				group.text = profileGroupRenderer(group);

				tpl = new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd {cls}-hd" style="{style}"><div>', '{text}', '</div></div>',
					'<div id="{groupId}-bd" class="x-grid-group-body">'
				);

				buf[buf.length] = tpl.apply(group);
			},
			interceptMouse: function(e) {
				var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
				var link = e.getTarget('.dm-link', this.mainBody);
				if(hd && !link){
					e.stopEvent();
					this.toggleGroup(hd.parentNode);
				}
			},

			stringfields:
			[
				{name: 'LpuSection_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSectionAge_id', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', group: true, isparams: true},
				{name: 'LpuSectionProfile_Name', hidden: true, sort: true, direction: 'ASC'},
				{name: 'LpuSectionLpuSectionProfileList', hidden: true},
				{name: 'Comments', width: 24, header: '...'},
				{name: 'LpuSection_Name', id: 'autoexpand', header: lang['otdelenie'], renderer: function(value, meta, record) {
					return Ext.isEmpty(record.id)?'':recordLinkTpl.apply({
						gridPanelId: 'dmwWizard_LpuSection',
						value: !Ext.isEmpty(value)?value:'',
						id: record.id
					});
				}},
				{name: 'MainLpuSectionProfile_Name', header: 'Основной профиль отделения', type: 'string'},
				{name: 'LpuSectionAge_Name', width: 120, header: lang['vozrastnaya_gruppa']},
				{name: 'LpuSectionType_Name', width: 200, header: lang['tip']},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, handler: function() {
					this.Wizard.SelectLpuSection.doRecord();
				}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_print',
					menuConfig: {
						printPatientList: {name: 'printPatientList', text: 'Печать списка пациентов', handler: function() { this.Wizard.SelectLpuSection.printPatientList() }.createDelegate(this)}
					}
				}
			],
			onLoadData: function(isData)
			{
				//
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				this.selectedId = !Ext.isEmpty(record.id)?record.id:null;
				this.record = !Ext.isEmpty(record) ? record : null;

				if (win.userMedStaffFact && win.userMedStaffFact.ARMType && win.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms']) || record.get('Lpu_id') == win.userMedStaffFact.Lpu_id) {
					this.getAction('action_mpqueue').enable();
				} else {
					this.getAction('action_mpqueue').disable();
				}
			},

			selectedId: null,
			doRecord: function(id) {
				if (!Ext.isEmpty(id)) {
					this.selectedId = id;
				}
				win.setStep('RecordTTS');
			},
			printPatientList: function() {
				if (Ext.isEmpty(this.selectedId) || Ext.isEmpty(this.record.data)) {
					return false;
				}

				var d = new Date(),
					LpuSection_id = this.record.data.LpuSection_id,
					id_salt = Math.random(),
					win_id = 'print_pac_list' + Math.floor(id_salt * 10000);

				var datestring = ("0" + d.getDate()).slice(-2) + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." +
					d.getFullYear();


				window.open('/?c=TimetableGraf&m=printPacList&begDate=' + datestring + '&LpuSection_id=' + LpuSection_id, win_id);
			},
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getStore().getById(this.selectedId);
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});

		var medServiceGroupRenderer = function(group) {
			var record = group.rs[0];
			var text = '';

			if (Ext.isEmpty(record.get('MedService_Nick'))) {
				text += '<span class="dm-group-hd-name">Служба не указана</span>';
			} else {
				text += '<span class="dm-group-hd-name">'+record.get('MedService_Nick').trim()+'</span>';
			}

			if (!Ext.isEmpty(record.get('MedService_id'))) {
				var onclick = 'onclick="getWnd(\'swDirectionMasterWindow\').getQueueForGroup({'+
					'Lpu_id: '+record.get('Lpu_id')+','+
					'MedService_id: '+record.get('MedService_id')+','+
					'groupId: \''+group.groupId+'\''+
				'});"';

				text += '&nbsp;&nbsp;';
				text += '<span class="dm-link dm-get-queue-cnt" '+onclick+'>Показать очередь</span>';
				
				if (this.Wizard.params['DirType_Code'] == 6) {
					var onclickrec = 'onclick="Ext.getCmp(\'dmwWizard_MedServiceLpuLevel\').doRecordOsmot(\''+record.get('MedService_id')+'\');"';
					text += '&nbsp;&nbsp;';
					text += '<span class="dm-link dm-get-queue-cnt" '+onclickrec+'>Записать</span>';
				}

				text += '<span class="dm-show-queue-cnt" style="display: none;">';
				text += 'Очередь: ';
				text += '<span class="dm-queue-cnt"></span>';
				text += '</span>';

				text += '&nbsp;&nbsp;';
				text += '<span class="dm-group-hd-warning" style="display: none;">';
				text += '</span>';
			}
			text += '</span>';

			return text;
		}.createDelegate(this);

		this.Wizard.SelectMedService = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_MedService',
			region: 'center',
			object: 'MedService',
			border: true,
			dataUrl: C_REG_DIRMSLIST,
			toolbar: true,
			autoLoadData: false,
			useEmptyRecord: false,

			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			doGroupStart: function(buf, group) {
				group.cls = "dm-group";

				group.rs.forEach(function(record) {
					record._groupId = group.groupId;
				});

				group.text = medServiceGroupRenderer(group);

				tpl = new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd {cls}-hd" style="{style} {[ values.rs[0].data["Group_id"]?"":"display:none;" ]}"><div>', '{text}', '</div></div>',
					'<div id="{groupId}-bd" class="x-grid-group-body">'
				);

				buf[buf.length] = tpl.apply(group);
			},
			interceptMouse: function(e) {
				var view = win.Wizard.SelectMedService.getGrid().getView();
				var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
				var link = e.getTarget('.dm-link', this.mainBody);
				if(hd && !link){
					e.stopEvent();
					var id = hd.id;
					var collapsed = e.getTarget('.x-grid-group-collapsed', view);
					if (collapsed && id.indexOf('Group_id') > -1) { // выполняем запрос только если свёрнуто
						// Извлекаем данные по группе
						id = id.substr(id.indexOf('Group_id'));
						id = id.split('-');
						var Group_id = id[1];
						// mb - для установки скролла на прежнюю позицию после загрузке и раскрытии группы
						var mb = view.scroller.dom.scrollTop;
						var initGroup = Group_id;
						var curGroupCount = 0;
						var groupsIds = [];
						var groups = {};
						win.Wizard.SelectMedService.getGrid().getStore().each(function(rec) {
							if (rec.get('Group_id') == Group_id) {
								// количество услуг в текущей группе
								curGroupCount++;
							}
							if (!rec.get('Group_id').inlist(groupsIds)) {
								// запись статуса групп до загрузки (закрыты/раскрыты)
								groupsIds.push(rec.get('Group_id'));
								var grop = view.getGroupId(rec.get('Group_id'));
								grp = Ext.getDom(grop);
								var gel = Ext.fly(grp);
								var exp = gel.hasClass('x-grid-group-collapsed');
								groups[grop] = exp;
							}
						});
						var MedService_id = Group_id;
						var baseParams = swCloneObject(win.Wizard.SelectMedService.getGrid().getStore().baseParams);
						// добавляем данные текущей группы для загрузки
						baseParams.MedService_id = MedService_id;

						win.getLoadMask(LOAD_WAIT).show();
						Ext.Ajax.request({
							failure: function() {
								win.getLoadMask().hide();
							},
							params: baseParams,
							success: function(response) {
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (answer.data) {
										// все полученные записи относятся к группе Group_id
										for (var k in answer.data) {
											answer.data[k]['Group_id'] = Group_id;
										}
									}

									var r = win.Wizard.SelectMedService.getGrid().getStore().reader.readRecords(answer);
									var options = {
										add: true,
										scope: win,
										callback: function() {
											// переформируем группы после подгрузки
											win.Wizard.SelectMedService.getGrid().getStore().groupBy('Group_id', true);
											// восстанавливаем состояние групп до загрузки
											for (var prop in groups) {
												var exp = groups[prop];
												if (exp) {
													exp = false;
												} else {
													exp = true;
												}
												var gel = Ext.get(prop);
												if (gel) view.toggleGroup(prop, exp);
											}
											// раскрываем текущую группу
											var grp = view.getGroupId(initGroup);
											view.toggleGroup(grp);
											view.scroller.dom.scrollTop = mb;
										}
									};

									// удаляем пустую запись с группой
									var index = win.Wizard.SelectMedService.getGrid().getStore().findBy(function(record) {
										if (record.get('UniqueKey_id') == MedService_id) {
											return true;
										} else {
											return false;
										}
									});
									if (index >= 0) {
										win.Wizard.SelectMedService.getGrid().getStore().remove(win.Wizard.SelectMedService.getGrid().getStore().getAt(index));
									}

									win.Wizard.SelectMedService.getGrid().getStore().loadRecords(r, options, true);
								}
								win.getLoadMask().hide();
							},
							url: C_REG_DIRMSLIST
						});
					} else {
					this.toggleGroup(hd.parentNode);
				}
				}
			},

			stringfields:
			[
				{name: 'UniqueKey_id', type: 'string', header: 'ID', key: true},
				{name: 'UslugaComplexMedService_id', type: 'int', header: 'ID', hidden: true},
				{name: 'UslugaComplexResource_id', type: 'int', header: 'ID', hidden: true},
				{name: 'allowDirection', hidden: true, type: 'int'},
				{name: 'Resource_id', hidden: true},
				{name: 'UslugaComplex_id', hidden: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuUnitType_id', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true},
				{name: 'MedServiceType_SysNick', hidden: true},
				{name: 'Group_id', type: 'string', hidden: true, group: true, sort: true, direction: 'ASC'},
				{name: 'MedService_id', hidden: true},
				{name: 'MedService_Nick', hidden: true},
				{name: 'MedService_Name', hidden: true},
				{name: 'MedService_Caption', width: 200, header: lang['slujba']},
				{name: 'UslugaComplex_Name', id: 'autoexpand', header: lang['usluga'], renderer: function(value, meta, record) {
					return Ext.isEmpty(record.id)?'':recordLinkTpl.apply({
						gridPanelId: 'dmwWizard_MedService',
						value: !Ext.isEmpty(value)?value:'',
						id: record.id
					});
				}},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, handler: function() {
					this.Wizard.SelectMedService.doRecord();
				}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				this.Wizard.SelectMedService.getGrid().getView().collapseAllGroups();
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				this.selectedId = !Ext.isEmpty(record.id)?record.id:null;

				if (win.userMedStaffFact && win.userMedStaffFact.ARMType && win.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms']) || record.get('Lpu_id') == win.userMedStaffFact.Lpu_id) {
					this.getAction('action_mpqueue').enable();
				} else {
					this.getAction('action_mpqueue').disable();
				}
			},

			selectedId: null,
			doRecord: function(id) {
				if (!Ext.isEmpty(id)) {
					this.selectedId = id;
				}
				win.addOrder(this.getGrid().getStore().getById(id), 'RecordTTMS');
			},
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getStore().getById(this.selectedId);
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});
		
		this.Wizard.SelectMedServiceLpuLevel = new sw.Promed.ViewFrame(
		{
			id: 'dmwWizard_MedServiceLpuLevel',
			region: 'center',
			object: 'MedService',
			border: true,
			dataUrl: C_REG_DIRMSLIST,
			toolbar: true,
			autoLoadData: false,
			useEmptyRecord: false,

			grouping: true,
			groupingView: {showGroupName: false, showGroupsText: true},
			doGroupStart: function(buf, group) {
				group.cls = "dm-group";

				group.rs.forEach(function(record) {
					record._groupId = group.groupId;
				});
				group.text = medServiceGroupRenderer(group);

				tpl = new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd {cls}-hd" style="{style} {[ values.rs[0].data["Group_id"]?"":"display:none;" ]}"><div>', '{text}', '</div></div>',
					'<div id="{groupId}-bd" class="x-grid-group-body">'
				);

				buf[buf.length] = tpl.apply(group);
			},
			interceptMouse: function(e) {
				var view = win.Wizard.SelectMedServiceLpuLevel.getGrid().getView();
				var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
				var link = e.getTarget('.dm-link', this.mainBody);
				if(hd && !link){
					e.stopEvent();
					var id = hd.id;
					var collapsed = e.getTarget('.x-grid-group-collapsed', view);
					if (collapsed && id.indexOf('Group_id') > -1) { // выполняем запрос только если свёрнуто
						// Извлекаем данные по группе
						id = id.substr(id.indexOf('Group_id'));
						id = id.split('-');
						var Group_id = id[1];
						// mb - для установки скролла на прежнюю позицию после загрузке и раскрытии группы
						var mb = view.scroller.dom.scrollTop;
						var initGroup = Group_id;
						var curGroupCount = 0;
						var groupsIds = [];
						var groups = {};
						win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().each(function(rec) {
							if (rec.get('Group_id') == Group_id) {
								// количество услуг в текущей группе
								curGroupCount++;
							}
							if (!rec.get('Group_id').inlist(groupsIds)) {
								// запись статуса групп до загрузки (закрыты/раскрыты)
								groupsIds.push(rec.get('Group_id'));
								var grop = view.getGroupId(rec.get('Group_id'));
								grp = Ext.getDom(grop);
								var gel = Ext.fly(grp);
								var exp = gel.hasClass('x-grid-group-collapsed');
								groups[grop] = exp;
							}
						});
						// Group_id может состоять из 2 компонентов (MedService_id и Resource_id)
						var MedService_id = Group_id;
						var baseParams = swCloneObject(win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().baseParams);
						// добавляем данные текущей группы для загрузки
						baseParams.MedService_id = MedService_id;

						win.getLoadMask(LOAD_WAIT).show();
						Ext.Ajax.request({
							failure: function() {
								win.getLoadMask().hide();
							},
							params: baseParams,
							success: function(response) {
								if (response.responseText) {
									var answer = Ext.util.JSON.decode(response.responseText);
									if (answer.data) {
										// все полученные записи относятся к группе Group_id
										for (var k in answer.data) {
											answer.data[k]['Group_id'] = Group_id;
										}
									}

									var r = win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().reader.readRecords(answer);
									var options = {
										add: true,
										scope: win,
										callback: function() {
											// переформируем группы после подгрузки
											win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().groupBy('Group_id', true);
											// восстанавливаем состояние групп до загрузки
											for (var prop in groups) {
												var exp = groups[prop];
												if (exp) {
													exp = false;
												} else {
													exp = true;
												}
												var gel = Ext.get(prop);
												if (gel) view.toggleGroup(prop, exp);
											}
											// раскрываем текущую группу
											var grp = view.getGroupId(initGroup);
											view.toggleGroup(grp);
											view.scroller.dom.scrollTop = mb;
										}
									};

									// удаляем пустую запись с группой
									var index = win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().findBy(function(record) {
										if (record.get('UniqueKey_id') == MedService_id) {
											return true;
										} else {
											return false;
										}
									});
									if (index >= 0) {
										win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().remove(win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().getAt(index));
									}

									win.Wizard.SelectMedServiceLpuLevel.getGrid().getStore().loadRecords(r, options, true);
								}
								win.getLoadMask().hide();
							},
							url: C_REG_DIRMSLIST
						});
					} else {
					this.toggleGroup(hd.parentNode);
				}
				}
			},

			stringfields:
			[
				{name: 'UniqueKey_id', type: 'string', header: 'ID', key: true},
				{name: 'UslugaComplexMedService_id', type: 'int', header: 'ID', hidden: true},
				{name: 'UslugaComplexResource_id', type: 'int', header: 'ID', hidden: true},
				{name: 'allowDirection', hidden: true, type: 'int'},
				{name: 'Resource_id', hidden: true},
				{name: 'UslugaComplex_id', hidden: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'Lpu_f003mcod', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuUnitType_id', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true},
				{name: 'MedService_id', hidden: true},
				{name: 'MedService_Nick', hidden: true},
				{name: 'MedService_Name', hidden: true},
				{name: 'MedServiceType_SysNick', hidden: true},
				{name: 'Group_id', type: 'string', hidden: true, group: true, sort: true, direction: 'ASC'},
				{name: 'Lpu_Nick', width: 200, header: langs('МО')},
				{name: 'MedService_Caption', width: 200, header: langs('Служба')},
				{name: 'UslugaComplex_Name', id: 'autoexpand', header: langs('Услуга'), renderer: function(value, meta, record) {
					return Ext.isEmpty(record.id)?'':recordLinkTpl.apply({
						gridPanelId: 'dmwWizard_MedServiceLpuLevel',
						value: !Ext.isEmpty(value)?value:'',
						id: record.id
					});
				}},
				{name: 'Dates', width: 500, header: lang['datyi_priema']},
				{name: 'useMedService', type: 'int', hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', disabled: false, hidden: true, handler: function() {
					this.Wizard.SelectMedServiceLpuLevel.doRecord();
                }.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true},
				{name:'action_print',
					menuConfig: {
						printPatientList: {name: 'printPatientList', text: 'Печать списка пациентов', handler: function() { this.Wizard.SelectMedServiceLpuLevel.printPatientList() }.createDelegate(this)}
					}
				}
			],
			onLoadData: function(isData)
			{
				this.Wizard.SelectMedServiceLpuLevel.getGrid().getView().collapseAllGroups();
			}.createDelegate(this),
			onRowSelect: function(sm, index, record) {
				this.selectedId = !Ext.isEmpty(record.id)?record.id:null;
				this.medServiceRec = !Ext.isEmpty(record) ? record : null;

				if (win.userMedStaffFact && win.userMedStaffFact.ARMType && win.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms']) || record.get('Lpu_id') == win.userMedStaffFact.Lpu_id) {
					this.getAction('action_mpqueue').enable();
				} else {
					this.getAction('action_mpqueue').disable();
				}
			},

			selectedId: null,
			doRecordOsmot: function(id) {
				if (!Ext.isEmpty(id)) {
					this.selectedId = id;
					if (!this.getSelectedParam('MedService_id')) {
						this.selectedId = id+'__null';
					}
				}
				win.setStep('RecordTTMSLpuLevel');
			},
			doRecord: function(id) {
				if (!Ext.isEmpty(id)) {
					this.selectedId = id;
				}
				var sysnick = this.getSelectedParam('MedServiceType_SysNick');
				var allow_direction = this.getSelectedParam('allowDirection');
				if (!sysnick || !allow_direction) {
					return false;
				}
				if (sysnick.inlist(['vk','mse'])) {
					win.setStep('RecordTTMSVK');
				} else if (sysnick.inlist(['func']) && !Ext.isEmpty(this.getSelectedParam('UslugaComplexResource_id'))) {
					win.addOrder(this.getGrid().getStore().getById(this.selectedId), 'RecordTTRLpuLevel');
				} else if (sysnick.inlist(['func','konsult']) && Ext.isEmpty(this.getSelectedParam('UslugaComplexMedService_id'))) {
					win.addOrder(this.getGrid().getStore().getById(this.selectedId), 'RecordTTMSLpuLevel');
				} else if (sysnick.inlist(['lab'])
					 || (getRegionNick()=='ekb' && sysnick=='pzm') ) {//#110233
					// для лаборатории всегда нужен заказ, не зависимо выбрана услуга или нет.
					win.addOrder(this.getGrid().getStore().getById(this.selectedId), 'RecordTTMSLpuLevel');
				} else {
					win.setStep('RecordTTMSLpuLevel');
				}
				return true;
			},
			printPatientList: function() {
				if (Ext.isEmpty(this.selectedId)) {
					return false;
				}

				var d = new Date(),
					MedService_id = this.medServiceRec.data.MedService_id,
					id_salt = Math.random(),
					win_id = 'print_pac_list' + Math.floor(id_salt * 10000),
					isPeriod = '';

				var datestring = ("0" + d.getDate()).slice(-2) + "." + ("0"+(d.getMonth()+1)).slice(-2) + "." +
					d.getFullYear();

				if (this.medServiceRec.data.MedServiceType_SysNick == 'func') {
					isPeriod = '&isPeriod=2'
				}

				window.open('/?c=TimetableGraf&m=printPacList&begDate=' + datestring + '&MedService_id=' + MedService_id + isPeriod, win_id);
			},
			/**
			 * Получение значение переданного поля текущей выбранной записи
			 */
			getSelectedParam: function(field) {
				var rec = this.getGrid().getStore().getById(this.selectedId);
				if (rec)
					return rec.get(field);
				else
					return false;
			}
		});

		this.Wizard.ResourceMedServiceGrid = new Ext.grid.GridPanel({
			autoExpandColumn: 'autoexpand',
			border: true,
			region: 'west',
			width: 250,
			split: true,
			header: false,
			hidden: true,
			id: 'DMW_ResourceMedServiceGrid',
			autoExpandMax: 2000,
			loadMask: true,
			stripeRows: true,
			enableKeyEvents: true,
			keys: [{
				key: [
					Ext.EventObject.TAB
				],
				fn: function(inp, e) {
					e.stopEvent();

					if ( e.browserEvent.stopPropagation )
						e.browserEvent.stopPropagation();
					else
						e.browserEvent.cancelBubble = true;

					if ( e.browserEvent.preventDefault )
						e.browserEvent.preventDefault();
					else
						e.browserEvent.returnValue = false;

					e.browserEvent.returnValue = false;
					e.returnValue = false;

					if (Ext.isIE)
					{
						e.browserEvent.keyCode = 0;
						e.browserEvent.which = 0;
					}

					switch (e.getKey())
					{
						case Ext.EventObject.TAB:
							if ( e.shiftKey )
							{
								this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.item('ResurceFilter').focus();
							} else {
								this.buttons[this.buttons.length - 2].focus(true);
							}
							break;
					}
				}.createDelegate(this),
				stopEvent: true
			}],
			store: new Ext.data.JsonStore({
				autoLoad: false,
				url: '/?c=Reg&m=getResourceListForSchedule',
				fields: [
					'Resource_id',
					'Resource_Name',
					{ name: 'TimetableResource_begDate', type: 'date', dateFormat: 'd.m.Y' }
				],
				listeners: {
					'load': function(store) {
						var field = this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.item('ResourceFilter');
						var exp = field.getValue();
						if (exp != "") {
							this.Wizard.ResourceMedServiceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
						}
						this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + store.getCount();
					}.createDelegate(this)
				}
			}),
			columns: [
				{dataIndex: 'Resource_id', hidden: true, hideable: false},
				{id: 'autoexpand', header: lang['resurs'], dataIndex: 'Resource_Name', sortable: true},
				{header: 'Ближайшая дата', dataIndex: 'TimetableResource_begDate', renderer: Ext.util.Format.dateRenderer('d.m.Y'), width: 100, sortable: true}
			],
			tbar: new sw.Promed.Toolbar({
				autoHeight: true,
				items: [{
					xtype: 'label',
					text: lang['filtr'],
					style: 'margin-left: 5px; font-weight: bold'
				}, {
					xtype: 'textfield',
					id: 'ResourceFilter',
					tabIndex: TABINDEX_SEMW + 5,
					style: 'margin-left: 5px',
					enableKeyEvents: true,
					listeners: {
						'keyup': function(field, e) {
							if (tm) {
								clearTimeout(tm);
							} else {
								var tm = null;
							}
							tm = setTimeout(function () {
								var field = this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.item('ResourceFilter');
								var exp = field.getValue();
								this.Wizard.ResourceMedServiceGrid.getStore().filter('Resource_Name', new RegExp(exp, "i"));
								this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = '0 / ' + this.Wizard.ResourceMedServiceGrid.getStore().getCount();
								field.focus();
							}.createDelegate(this),
								100
							);
						}.createDelegate(this),
						'keydown': function (inp, e) {
							if (e.getKey() == Ext.EventObject.TAB )
							{
								e.stopEvent();
								if  (e.shiftKey == false) {
									if ( this.Wizard.ResourceMedServiceGrid.getStore().getCount() > 0 )
									{
										this.Wizard.ResourceMedServiceGrid.getView().focusRow(0);
										this.Wizard.ResourceMedServiceGrid.getSelectionModel().selectFirstRow();
									}
								} /*else {
									this.StructureTree.focus();
								}*/
							}
						}.createDelegate(this)
					}
				},
					{
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
			}),
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'rowselect': function(sm, rowIdx, r) {
						this.Wizard.ResourceMedServiceGrid.getTopToolbar().items.items[3].el.innerHTML = (rowIdx + 1) + ' / ' + this.Wizard.ResourceMedServiceGrid.getStore().getCount();

						this.Wizard.params.directionData['Resource_id'] = r.data.Resource_id;
						this.Wizard.TTRDirectionPanel.Resource_id = r.data.Resource_id;
						this.Wizard.TTRDirectionPanel.loadSchedule(this.Wizard.TTRDirectionPanel.calendar.value);
					}.createDelegate(this)
				}
			})
		});
		
		
		// Нижняя панель во втором шаге мастера. В зависимости от выбранного типа подразделения отображаются разные данные
		this.Wizard.BottomPanel = new Ext.Panel(
		{
			hidden: true,
			height: 350,
			region: 'south',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				this.Wizard.SelectMedPersonal,
				this.Wizard.SelectLpuSection,
				this.Wizard.SelectMedService
			]
		}
		);

		this.Wizard.SelectTTRPanel = new Ext.Panel({
			id: 'SelectTTRPanel',
			region: 'center',
			layout: 'border',
			items: [
				this.Wizard.ResourceMedServiceGrid,
				this.Wizard.TTRDirectionPanel
			]
		});
		
		this.DirectionButtonPanel = new Ext.Panel({
			region: 'north',
			layout: 'form',
			height: 35,
			border: false,
			frame: true,
			items: [{
				xtype: 'button',
				id: '',
				text: 'Направление в другую МО',
				iconCls: 'add16',
				handler: function() {
					this.addOtherLpuDirection();
				}.createDelegate(this)
			}]
		});
		
		this.Wizard.Panel = new Ext.Panel(
		{
			region: 'center',
			layout: 'card',
			border: false,
			activeItem: 0, 
			defaults: 
			{
				border:false
			},
			items: 
			[
				{
					layout: 'border',
					items: [
						{
							defaults: {split: true},
							border: false,
							region: 'center',
							layout: 'border',
							items: [
								this.Wizard.SelectLpuUnit,
								this.Wizard.BottomPanel
							],
							split: true
						}
					]
				},
				this.Wizard.SelectDirType,
				this.Wizard.SelectMedServiceLpuLevel,
				this.Wizard.TTGDirectionPanel,
				this.Wizard.TTGRecordOneDayPanel,
				this.Wizard.TTGRecordInGroupPanel,
				this.Wizard.TTSDirectionPanel,
				this.Wizard.TTSRecordOneDayPanel,
				this.Wizard.TTRRecordOneDayPanel,
				this.Wizard.TTMSDirectionPanel,
				this.Wizard.TTMSRecordOneDayPanel,
				this.Wizard.TTMSVKDirectionPanel,
				this.Wizard.SelectTTRPanel
			]
		});

		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.Filters,
				{
					region: 'center',
					layout: 'border',
					border: false,
					items: [
						this.DirectionButtonPanel,
						this.Wizard.Panel
					]
				}
			],
			buttons: 
			[
			{
				iconCls: 'arrow-previous16',
				text: lang['nazad'],
				handler: function() { this.prevStep(); }.createDelegate(this)
			},
			{
				iconCls: 'home16',
				text: lang['v_nachalo'],
				handler: function() { 
					this.userMedStaffFact && this.userMedStaffFact.ARMType && this.userMedStaffFact.ARMType.inlist(['callcenter','smo','tfoms'])  && !this.Wizard.params.directionData['fromBj'] 
					? this.setStep('SelectLpuUnit')
					: this.setStep('SelectDirType');
				}.createDelegate(this)
			},
			{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_DMW);
				}.createDelegate(this),
				tabIndex: TABINDEX_DMW + 18
			}, 
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		//this.Wizard.Panel.doLayout();
		
		sw.Promed.swDirectionMasterWindow.superclass.initComponent.apply(this, arguments);
	}
});