/**
 * swRecordMasterWindow - мастер записи к врачу
 * Состоит из следующих шагов
 * 1. Выбор подразделения ЛПУ
 * 2а. Отображение списка врачей при выборе поликлинического подразделения
 * 2б. Отображение списка отделений при выборе стационарного подразделения
 * 2в. Отображение списка служб/услуг/ресурсов при выборе параклинического подразделения
 * 3а. Отображение расписания врача при выборе врачей
 * 3б. Отображение расписания коек в отделении при выборе отделения
 * 3в. Отображение раписания службы/услуги/ресурса при их соответствующем выборе
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009-2011, Swan.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @prefix       rmw
 * @tabindex     TABINDEX_RMW
 * @version      October 2011 - April 2012
 */
 
/*NO PARSE JSON*/

sw.Promed.swRecordMasterWindow = Ext.extend(sw.Promed.BaseForm,
{
	codeRefresh: true,
	objectName: 'swRecordMasterWindow',
	objectSrc: '/jscore/Forms/Reg/swRecordMasterWindow.js',

	closable: true,
	closeAction: 'hide',
	maximized: true,
	title: WND_RMW,
	iconCls: 'workplace-mp16',
	id: 'swRecordMasterWindow',
	readOnly: false,

	onDirection: Ext.emptyFn,
	onHide: Ext.emptyFn,
	listeners:
	{
		hide: function(win)
		{
            win.onHide();
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
			changeParams: false
		};
		
		this.Wizard.params.step = action;
		this.Wizard.params.isHimSelf = true;
		switch (action) {
			case "RecordTTGOneDay": 
				this.Wizard.TTGRecordPanel.MedStaffFact_id = params.MedStaffFact_id;
				break;
			case "RecordTTSOneDay":
				this.Wizard.TTSRecordPanel.LpuSection_id = params.LpuSection_id;
				break;
			case "RecordTTMSOneDay":
				this.Wizard.TTMSRecordPanel.MedService_id = params.MedService_id;
				this.Wizard.TTMSRecordPanel.UslugaComplexMedService_id = params.UslugaComplexMedService_id;
				this.Wizard.TTMSRecordPanel.MedServiceData = (params.MedServiceData)?params.MedServiceData:{};
			case "RecordTTROneDay":
				this.Wizard.TTRRecordPanel.Resource_id = params.Resource_id;
				this.Wizard.TTRRecordPanel.MedService_id = params.MedService_id;
				this.Wizard.TTRRecordPanel.ResourceData = (params.ResourceData)?params.ResourceData:{};
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
	
	/**
	 * Смена шага мастера
	 *
	 * @param string step - Название шага
	 */
	setStep: function(step, params)
	{
        var _this = this;
        log(step);
		_this.buttons[0].enable();
		this.Wizard.params.prevStep = this.Wizard.params.step;
		this.Wizard.params.step = step;
		this.Wizard.params.limit = 100;
		this.Wizard.params.start = 0;
		switch(step) {
			case "SelectLpuUnit": // Выбор подразделения ЛПУ
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);
				//log(this.Wizard.SelectLpuUnit);
			
				this.Wizard.SelectLpuUnit.show();
				this.Wizard.BottomPanel.hide();
				this.Wizard.SelectLpuUnit.removeAll({clearAll: true});
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params
				});
				
				this.doLayout();
                _this.buttons[0].disable();
				break;
			case "SelectMedServiceLpuLevel": // Выбор службы уровня ЛПУ
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem('rmwWizard_MedServiceLpuLevel');
				//log(this.Wizard.SelectLpuUnit);
				
				this.Wizard.SelectLpuUnit.removeAll({clearAll: true});
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				params.LpuUnitLevel = '1';
				
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
				var tip = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_Name') || "";
				if ( tip.indexOf('<img') > -1 && $(tip).attr('ext:qtip') ) {
					var tip_text = $(tip).attr('ext:qtip');
					var tip_data = tip_text.substring(tip_text.indexOf('<hr>') + 4).replace(',', '<br/>');
					tip_text = tip_text.substring(0, tip_text.indexOf('<hr>'));
					this.LpuUnitDescriptionPanel.el.update('<div class="x-panel-body x-panel-body-noheader" style="width: 100%"><div style="float:left; margin-left: 20px;">' + tip_text + '</div><div style="float:right; margin-right: 20px; padding-left: 20px; border-left: 1px dashed;"><img  src="/img/icons/info16.png"><br/>' + tip_data + '</div></div>');
					this.LpuUnitDescriptionPanel.setHeight(50);
				} else {
					this.LpuUnitDescriptionPanel.el.update('');
				}
				this.Wizard.params.date = getGlobalOptions().date;
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				
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
				
				this.refreshMedPersonalList();
				
				if (this.Wizard.params.prevStep == 'SelectLpuUnit') {
					this.Wizard.SelectLpuUnit.loadData({
						globalFilters: params
					});
				}
				
				this.doLayout();
				break;
			case "SelectLpuSection": // Выбор отделения
				
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);

				this.Wizard.BottomPanel.show();
				this.Wizard.BottomPanel.layout.setActiveItem(1);
				
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				// Если в списке подразделений уже выбрано подразделение, то берем данные из него
				if (this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id')) {
					params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
					this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				}
				if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id')) {
					params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
					this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				}
				
				this.refreshLpuSectionList();
				
				if (this.Wizard.params.prevStep == 'SelectLpuUnit') {
					this.Wizard.SelectLpuUnit.loadData({
						globalFilters: params
					});
				}
				
				this.Wizard.params['Lpu_id'] = params['Filter_Lpu_id'];
				this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['Lpu_did'] = params['Filter_Lpu_id'];
				this.Wizard.params.directionData['LpuUnit_did'] = params['LpuUnit_id'];
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnitType_SysNick');
				
				this.doLayout();
				break;
			case "SelectMedService": // Выбор службы/услуги
				
				this.Filters.show();
				this.Wizard.Panel.layout.setActiveItem(0);

				this.Wizard.BottomPanel.show();
				this.Wizard.BottomPanel.layout.setActiveItem(2);
				
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
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
					
				if (this.Wizard.params.prevStep == 'SelectLpuUnit') {
					this.Wizard.SelectLpuUnit.loadData({
						globalFilters: params
					});
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
				this.Wizard.Panel.layout.setActiveItem('TTGRecordPanel');
				this.doLayout();

                this.Wizard.TTGRecordPanel.userMedStaffFact = this.userMedStaffFact;
                this.Wizard.TTGRecordPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTGRecordPanel.callback = this.Wizard.params.callback;
				this.Wizard.TTGRecordPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'polka';
				this.Wizard.params.directionData['ARMType_id'] = this.Wizard.params.ARMType_id;

				if (this.Wizard.params.isHimSelf) { // если записываем к себе то directionData уже сформирован
					this.Wizard.TTGRecordPanel.MedStaffFact_id = this.Wizard.params.MedStaffFact_id;
				} else {
					this.Wizard.params.MedStaffFact_id = this.Wizard.SelectMedPersonal.getSelectedParam('MedStaffFact_id');
					this.Wizard.TTGRecordPanel.MedStaffFact_id = this.Wizard.params.MedStaffFact_id;
					this.Wizard.params.directionData['MedStaffFact_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('MedStaffFact_id');
					this.Wizard.params.directionData['From_MedStaffFact_id'] = this.userMedStaffFact.MedStaffFact_id||null;
					this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuUnit_id');
					this.Wizard.params.directionData['Lpu_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('Lpu_id');
					this.Wizard.params.directionData['MedPersonal_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_id');
					this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSection_id');
					this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_id');
					this.Wizard.params.directionData['LpuSectionAge_id'] = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionAge_id');
				}
				this.Wizard.TTGRecordPanel.directionData = this.Wizard.params.directionData;
				log(this.Wizard.TTGRecordPanel.directionData);
                this.Wizard.TTGRecordPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTGRecordPanel.loadSchedule(this.Wizard.params.date);
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
				this.Wizard.TTGRecordOneDayPanel.MedStaffFact_id = this.Wizard.TTGRecordPanel.MedStaffFact_id;
				this.Wizard.TTGRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTS": // Запись на койку
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTSRecordPanel');
				this.doLayout();
				this.Wizard.TTSRecordPanel.userMedStaffFact = this.userMedStaffFact;
				// Отделение для которого отображается расписание
				this.Wizard.TTSRecordPanel.LpuSection_id = this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_id');
				this.Wizard.TTSRecordPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTSRecordPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuSectionAge_id'] = this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionAge_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'stac';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTSRecordPanel.directionData = this.Wizard.params.directionData;
                this.Wizard.TTSRecordPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTSRecordPanel.loadSchedule(this.Wizard.params.date);
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
				this.Wizard.TTSRecordOneDayPanel.LpuSection_id = this.Wizard.TTSRecordPanel.LpuSection_id;
				this.Wizard.TTSRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTR": // Запись на ресурс
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTRRecordPanel');
				this.doLayout();
				this.Wizard.TTRRecordPanel.userMedStaffFact = this.userMedStaffFact;
				if (params && params.order) { // если переданы параметры по заказу, то часть данных нужно подменить
					this.Wizard.TTRRecordPanel.Resource_id = params.Resource_id;
					this.Wizard.TTRRecordPanel.MedService_id = params.MedService_id;
					this.Wizard.TTRRecordPanel.ResourceData = {
						'Resource_Name': params.Resource_Name,
						'MedService_Nick': params.MedService_Nick,
						'UslugaComplex_id': params.UslugaComplex_id,
						'LpuSection_id': params.LpuSection_id,
						'LpuSectionProfile_id': params.LpuSectionProfile_id,
						'Lpu_id': this.Wizard.params.Lpu_id
					};
				} else {
					this.Wizard.TTRRecordPanel.Resource_id = this.Wizard.SelectMedService.getSelectedParam('Resource_id');
					this.Wizard.TTRRecordPanel.MedService_id = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
					this.Wizard.TTRRecordPanel.MedServiceData = new Object({
						'Resource_Name': this.Wizard.SelectMedService.getSelectedParam('Resource_Name'),
						'MedService_Nick': this.Wizard.SelectMedService.getSelectedParam('MedService_Nick'),
						'UslugaComplex_id': this.Wizard.SelectMedService.getSelectedParam('UslugaComplex_id'),
						'LpuSection_id': this.Wizard.SelectMedService.getSelectedParam('LpuSection_id'),
						'LpuSectionProfile_id': this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id'),
						'Lpu_id': this.Wizard.params.Lpu_id
					});
				}
				this.Wizard.TTRRecordPanel.order = this.Wizard.params.order; // заказ услуги
				this.Wizard.TTRRecordPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTRRecordPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['Resource_id'] = this.Wizard.SelectMedService.getSelectedParam('Resource_id');
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedService.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTRRecordPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTRRecordPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTRRecordPanel.loadSchedule(this.Wizard.params.date);
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

				this.Wizard.TTRRecordOneDayPanel.Resource_id = this.Wizard.TTRRecordPanel.Resource_id;
				this.Wizard.TTRRecordOneDayPanel.MedService_id = this.Wizard.TTRRecordPanel.MedService_id;
				this.Wizard.TTRRecordOneDayPanel.ResourceData = this.Wizard.TTRRecordPanel.ResourceData;
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
				this.Wizard.TTRRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTMS": // Запись на услугу/службу
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSRecordPanel');
				this.doLayout();
                this.Wizard.TTMSRecordPanel.userMedStaffFact = this.userMedStaffFact;
                if (params && params.order) { // если переданы параметры по заказу, то часть данных нужно подменить
					this.Wizard.TTMSRecordPanel.MedService_id = params.MedService_id;
					this.Wizard.TTMSRecordPanel.UslugaComplexMedService_id = params.UslugaComplexMedService_id;
					this.Wizard.TTMSRecordPanel.MedServiceData = {
						'MedService_Nick': params.MedService_Nick,
						'UslugaComplex_id': params.UslugaComplex_id,
						'LpuSection_id': params.LpuSection_id,
						'LpuSectionProfile_id': params.LpuSectionProfile_id,
						'Lpu_id': this.Wizard.params.Lpu_id
					};
				} else {
					this.Wizard.TTMSRecordPanel.MedService_id = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
					this.Wizard.TTMSRecordPanel.UslugaComplexMedService_id = this.Wizard.SelectMedService.getSelectedParam('UslugaComplexMedService_id');
					this.Wizard.TTMSRecordPanel.MedServiceData = new Object({
						'MedService_Nick': this.Wizard.SelectMedService.getSelectedParam('MedService_Nick'),
						'UslugaComplex_id': this.Wizard.SelectMedService.getSelectedParam('UslugaComplex_id'),
						'LpuSection_id': this.Wizard.SelectMedService.getSelectedParam('LpuSection_id'),
						'LpuSectionProfile_id': this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id'),
						'Lpu_id': this.Wizard.params.Lpu_id
					});
				}
				this.Wizard.TTMSRecordPanel.order = this.Wizard.params.order; // заказ услуги
				this.Wizard.TTMSRecordPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTMSRecordPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedService.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = this.Wizard.params.LpuUnit_id;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = this.Wizard.SelectMedService.getSelectedParam('LpuSection_id');
				this.Wizard.params.directionData['LpuSectionProfile_id'] = this.Wizard.SelectMedService.getSelectedParam('LpuSectionProfile_id');
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTMSRecordPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTMSRecordPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTMSRecordPanel.loadSchedule(this.Wizard.params.date);
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

				this.Wizard.TTMSRecordOneDayPanel.MedService_id = this.Wizard.TTMSRecordPanel.MedService_id;
				this.Wizard.TTMSRecordOneDayPanel.UslugaComplexMedService_id = this.Wizard.TTMSRecordPanel.UslugaComplexMedService_id;
				this.Wizard.TTMSRecordOneDayPanel.MedServiceData = this.Wizard.TTMSRecordPanel.MedServiceData;
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
				this.Wizard.TTMSRecordOneDayPanel.loadSchedule(this.Wizard.params.date);
				break;
			case "RecordTTMSVK": // Запись на услугу/службу ВК или МСЕ
				this.Filters.hide();
				this.Wizard.Panel.layout.setActiveItem('TTMSRecordPanel');
				this.doLayout();

                this.Wizard.TTMSRecordPanel.userMedStaffFact = this.userMedStaffFact;
                this.Wizard.TTMSRecordPanel.MedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
				this.Wizard.TTMSRecordPanel.UslugaComplexMedService_id = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexMedService_id');
				this.Wizard.TTMSRecordPanel.MedServiceData = new Object({
					'MedService_Nick': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Nick'),
					'UslugaComplex_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplex_id'),
					'LpuSection_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSection_id'),
					'LpuSectionProfile_id': this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('LpuSectionProfile_id'),
					'Lpu_id': this.Wizard.params.Lpu_id
				});

				this.Wizard.TTMSRecordPanel.order = this.Wizard.params.order; // заказ услуги
				this.Wizard.TTMSRecordPanel.personData = this.Wizard.params.personData;
				this.Wizard.TTMSRecordPanel.onDirection = this.onDirection;
				this.Wizard.params.directionData['MedService_id'] = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_id');
				this.Wizard.params.directionData['LpuUnit_did'] = null;
				this.Wizard.params.directionData['Lpu_did'] = this.Wizard.params.Lpu_id;
				this.Wizard.params.directionData['LpuSection_did'] = null;
				this.Wizard.params.directionData['LpuSectionProfile_id'] = null;
				this.Wizard.params.directionData['LpuUnitType_SysNick'] = 'parka';
				this.Wizard.params.directionData['ARMType_id'] = this.userMedStaffFact.ARMType_id;
				this.Wizard.TTMSRecordPanel.directionData = this.Wizard.params.directionData;
				this.Wizard.TTMSRecordPanel.calendar.setValue(this.Wizard.params.date);
				this.Wizard.TTMSRecordPanel.loadSchedule(this.Wizard.params.date);
				break;
			default:
				
		}
		
		this.refreshWindowTitle(params);
		this.Wizard.Panel.doLayout();
	},
	/**
	 * Устанавливает фильтр комбобокса по профилю
	 * @grid object грид по которому нужно собрать профили
	 *
	 * К сожалению такой подход неправильный, после фильтрации по профилю у нас в фильтре будет только один профиль
	 * По идее список профилей для выбранного подразделения нужно передавать отдельно, but who cares
	 */
	setFilterLpuSectionProfile: function(grid) {
        var _this = this;
		var combo = this.Filters.getForm().findField('LpuSectionProfile_id');
		if (grid) {
			var id, yes = false;
			var filters = [];
			var lpu_section_ids = [];
			grid.getStore().each(function(r) {
				if (!Ext.isEmpty(r.get('LpuSectionProfile_id'))) {
					lpu_section_ids.push(r.get('LpuSection_id'));
				}
			});
			var params = {LpuSection_ids: Ext.util.JSON.encode(lpu_section_ids)};
            params['FormName'] = _this.id;
			Ext.Ajax.request({
				url: '/?c=LpuStructure&m=loadLpuSectionProfileList',
				params: params,
				callback: function(options, success, response)
				{
					if (success)
					{
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
					}
				}
			});
		} else {
			combo.getStore().clearFilter();
			combo.lastQuery = '';
		}
	},
	/**
	 * Обновление списка медперсонала
	 */
	refreshMedPersonalList: function() {
        var _this = this;
		var params = this.Filters.getFilters();
        params['FormName'] = _this.id;
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		params['LpuUnit_id'] = this.Wizard.params['LpuUnit_id'];
		params['Filter_Lpu_id'] = this.Wizard.params['Lpu_id'];
		
		if (!isUfa&&this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
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
        params['FormName'] = _this.id;
		params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
		params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
		var isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');
		if (!isUfa&&this.Wizard.params.personData && this.Wizard.params.personData.Person_Birthday && swGetPersonAge(this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
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
        params['FormName'] = _this.id;
		params['LpuUnit_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_id');
		params['Filter_Lpu_id'] = this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_id');
		this.Wizard.SelectMedService.loadData({
			globalFilters: params,
			callback: function() {
				this.setFilterLpuSectionProfile(this.Wizard.SelectMedService.getGrid());
			}.createDelegate(this)
		});
		this.Wizard.params['LpuUnit_id'] = params['LpuUnit_id'];
	},
	
	/**
	 * Возврат на предыдущий шаг мастера
	 */
	prevStep: function()
	{
        var _this = this;
        _this.buttons[0].enable();

		switch(this.Wizard.params.step) {
			case "SelectLpuUnit":
                _this.buttons[0].disable();
				break;
			case "SelectMedPersonal":
				this.setStep('SelectLpuUnit');
				
				break;
			case "SelectLpuSection":
				this.setStep('SelectLpuUnit');
			
				break;
			case "SelectMedService":
				this.setStep('SelectLpuUnit');
			
				break;
			case "SelectMedServiceLpuLevel":
				this.setStep('SelectLpuUnit');
			
				break;
			case "RecordTTG":
				this.setStep('SelectMedPersonal');
			
				break;
			case "RecordTTGOneDay":
				this.setStep('RecordTTG', {'changeParams':false});
			
				break;
			case "RecordTTS":
				this.setStep('SelectLpuSection');
			
				break;
			case "RecordTTSOneDay":
				this.setStep('RecordTTS');
			
				break;
			case "RecordTTR":
				this.setStep('SelectMedService');
				break;
			case "RecordTTROneDay":
				this.setStep(this.Wizard.params.prevStep);
				break;
			case "RecordTTMS":
				this.setStep('SelectMedService');
				break;
			case "RecordTTMSVK":
				this.setStep('SelectMedServiceLpuLevel');
				break;
			case "RecordTTMSOneDay":
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
			
			//log(123,this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick'),this.Wizard.params['Lpu_Nick'],params.Lpu_Nick)
			if (this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick')||(params && params.Lpu_Nick)) {
				this.Wizard.params['Lpu_Nick'] = (params && params.Lpu_Nick)?params.Lpu_Nick:this.Wizard.SelectLpuUnit.getSelectedParam('Lpu_Nick');
			}
			if(!this.Wizard.params.isHimSelf){
				
				this.Wizard.params['MedPersonal_FIO'] = (params && params.MedPersonal_FIO)?params.MedPersonal_FIO:this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_FIO');
				
			}
			this.Wizard.params['LpuSectionProfile_Name'] = (params && params.LpuSectionProfile_Name)?params.LpuSectionProfile_Name:this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_Name');
			if (this.Wizard.params['LpuSectionProfile_Name']) {
				this.Wizard.params['LpuSectionProfile_Name'] = this.Wizard.params['LpuSectionProfile_Name'].split("<br")[0];
			}
			this.Wizard.params['LpuUnit_Name'] = (params && params.LpuUnit_Name)?params.LpuUnit_Name:this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_Name');
			this.Wizard.params['LpuUnit_Address'] = (params && params.LpuUnit_Address)?params.LpuUnit_Address:this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnit_Address');
			this.Wizard.params['LpuRegion_Names'] = (params && params.LpuRegion_Names)?params.LpuRegion_Names:this.Wizard.SelectMedPersonal.getSelectedParam('LpuRegion_Names');
			this.Wizard.params['LpuSection_Name'] = (params && params.LpuSection_Name)?params.LpuSection_Name:this.Wizard.SelectLpuSection.getSelectedParam('LpuSection_Name');
			// бывает что нужно отображать расписание другой службы (пункта забора в частности вместо лаборатории)
			if (this.Wizard.SelectMedService.getSelectedParam('MedService_Name')) {
				var MedService_Name = this.Wizard.SelectMedService.getSelectedParam('MedService_Name');
				
			} else if (this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Name')) {
				MedService_Name = this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('MedService_Name');
			}
			this.Wizard.params['MedService_Name'] = (params && params.MedService_Name) ? params.MedService_Name : MedService_Name;
		}

		if (this.Wizard.params['Person_FIO']) {
			var title = WND_RMW + ' ' + this.Wizard.params['Person_FIO'];
		} else {
			var title = WND_RMW;
		}
			
		
		switch(this.Wizard.params.step) {
			case "SelectLpuUnit":
				this.setTitle(title + lang['|_vyibor_podrazdeleniya']);
				break;
			case "SelectMedPersonal":
				full_title = title + ' | ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'');
				} 
				full_title +=lang['>_vyibor_vracha'];
				this.setTitle(full_title);
				break;
			case "SelectLpuSection":
				full_title = title + ' | ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'');
				} 
				full_title +=lang['>_vyibor_otdeleniya'];
				this.setTitle(full_title);
				break;
			case "SelectMedService":
				full_title = title + ' | ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'');
				} 
				full_title +=lang['>_vyibor_slujbyi_uslugi'];
				this.setTitle(full_title);
				break;
			case "SelectMedServiceLpuLevel":
				this.setTitle(title + lang['|_vyibor_slujbyi_uslugi']);
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
			case "RecordTTS":
				this.Wizard.params['LpuSectionProfile_Name'] = (params && params.LpuSectionProfile_Name)?params.LpuSectionProfile_Name:this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionProfile_Name');
				
				this.setTitle(title + ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.LpuSection_Name + ' (' + this.Wizard.params.LpuSectionProfile_Name + ')' + ' > Выбор даты');
				break;
			case "RecordTTSOneDay":
				/*if (params.changeParams) {
					this.Wizard.params['LpuSectionProfile_Name'] = (params && params.LpuSectionProfile_Name)?params.LpuSectionProfile_Name:this.Wizard.SelectLpuSection.getSelectedParam('LpuSectionProfile_Name');
				}*/
				this.setTitle(title + ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.LpuSection_Name + ' (' + this.Wizard.params.LpuSectionProfile_Name + ')' + ' > ' + this.Wizard.params.date + ' > Список записанных');
				break;
			case "RecordTTR":
			case "RecordTTMS":
				var full_title = title + ' | ' + this.Wizard.params.Lpu_Nick;
				if (this.Wizard.params.LpuUnit_Name) {
					full_title += ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'');
				}
				full_title += ' > ' + this.Wizard.params.MedService_Name + lang['>_vyibor_vremeni'];
				this.setTitle(full_title);
				break;
			case "RecordTTROneDay":
			case "RecordTTMSOneDay":
				this.setTitle(title + ' | ' + this.Wizard.params.Lpu_Nick + ' > ' + this.Wizard.params.LpuUnit_Name + ((this.Wizard.params.LpuUnit_Address)?(', ' + this.Wizard.params.LpuUnit_Address):'') + ' > ' + this.Wizard.params.MedService_Name + ' > ' + this.Wizard.params.date + ' > Список записанных');
				break;
			case "RecordTTMSVK":
				var full_title = title + ' | ' + this.Wizard.params.Lpu_Nick;
				full_title += ' > ' + this.Wizard.params.MedService_Name + lang['>_vyibor_vremeni'];
				this.setTitle(full_title);
				break;
			default:
				
		}
		//console.warn('refreshWindowTitle:', this.title, params);
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
        var _this = this,
		    isUfa = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa');

		switch(this.Wizard.params.step) {
			case "SelectLpuUnit":
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}
					
				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params
				});
				
				break;
			case "SelectMedPersonal":
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}
					
				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params,
				    callback: function(){
                        if (!isUfa&&_this.Wizard.params.personData && _this.Wizard.params.personData.Person_Birthday && swGetPersonAge(_this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
                            params['WithoutChildLpuSectionAge'] = 1;
                        } else {
                            params['WithoutChildLpuSectionAge'] = 0;
                        }
                        if (_this.Wizard.SelectLpuUnit.getGrid().getStore().getCount() == 0 || Ext.isEmpty(_this.Wizard.SelectLpuUnit.getGrid().getSelectionModel().getSelected().get('LpuUnit_id'))) {
                            _this.Wizard.SelectMedPersonal.getGrid().getStore().removeAll();
                        } else {
                            _this.Wizard.SelectMedPersonal.loadData({
                                globalFilters: params
                            });
                        }
                    }
                });

				break;
			case "SelectLpuSection":
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}

				this.Wizard.SelectLpuUnit.loadData({
					globalFilters: params,
				    callback: function(){
                        if (!isUfa&&_this.Wizard.params.personData && _this.Wizard.params.personData.Person_Birthday && swGetPersonAge(_this.Wizard.params.personData.Person_Birthday, new Date()) >= 18) {
                            params['WithoutChildLpuSectionAge'] = 1;
                        } else {
                            params['WithoutChildLpuSectionAge'] = 0;
                        }
                        if (_this.Wizard.SelectLpuUnit.getGrid().getStore().getCount() == 0 || Ext.isEmpty(_this.Wizard.SelectLpuUnit.getGrid().getSelectionModel().getSelected().get('LpuUnit_id'))) {
                            _this.Wizard.SelectLpuSection.getGrid().getStore().removeAll();
                        } else {
                            _this.Wizard.SelectLpuSection.loadData({
                                globalFilters: params
                            });
                        }
                    }
                });
				
				break;
				
			case "SelectMedService":
				var params = this.Filters.getFilters();
                params['FormName'] = _this.id;
				if (params['Filter_LpuSectionProfile_id']) {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', false);
				} else {
					this.Wizard.SelectLpuUnit.setColumnHidden('FreeTime', true);
				}

                this.Wizard.SelectLpuUnit.loadData({
                    globalFilters: params,
                    callback: function(){
                        if (_this.Wizard.SelectLpuUnit.getGrid().getStore().getCount() == 0 || Ext.isEmpty(_this.Wizard.SelectLpuUnit.getGrid().getSelectionModel().getSelected().get('LpuUnit_id'))) {
                            _this.Wizard.SelectMedService.getGrid().getStore().removeAll();
                        } else {
                            _this.Wizard.SelectMedService.loadData({
                                globalFilters: params
                            });
                        }
                    }
                });

				break;
			default:
				
		}
	},

	/**
	 * Открывает форму заказа услуги в параклинике
	 */
	addOrder: function(record, nextstep) {

		var p = {};
		p.Resource_id = record.get('Resource_id'); //Ресурс, которому назначается оказание услуги
		p.MedService_id = record.get('MedService_id'); //Служба, которой назначается оказание услуги
		p.MedService_Nick = record.get('MedService_Nick');
		p.Lpu_uid = record.get('Lpu_id');
		p.LpuSection_uid = record.get('LpuSection_id');
		p.UslugaComplexMedService_id = record.get('UslugaComplexMedService_id');
		p.LpuSectionProfile_id = record.get('LpuSectionProfile_id');
		p.UslugaComplex_id = record.get('UslugaComplex_id');
		p.UslugaComplex_Name = record.get('UslugaComplex_Name');
		p.MedServiceType_SysNick = record.get('MedServiceType_SysNick');
		if (this.Wizard.params.personData && this.Wizard.params.personData.Person_id) {
			p.Person_id = this.Wizard.params.personData.Person_id;
			p.PersonEvn_id = this.Wizard.params.personData.PersonEvn_id;
			p.Server_id = this.Wizard.params.personData.Server_id;
		} else {
			p.Person_id = null;
			p.PersonEvn_id = null;
			p.Server_id = null;
		}

		// Назначенная услуга
		p.UslugaComplex_prescid = null;
		p.action = 'add';
		p.mode = 'nosave'; // просто возвращаем в калбэке данные
		p.fromRecordMaster = true;
		p.callback = function(scope, id, values) {
			// здесь показываем расписание на определенную выбранную службу
			this.Wizard.params.order = values;

			// подменяем данные на выбранные
			var params = {
				order: true,
				LpuSection_id: values.LpuSection_id,
				Resource_id: values.Resource_id,
				// службу выбираем из пункта забора, если он выбран
				MedService_id: (values.MedService_pzid>0)?values.MedService_pzid:values.MedService_id,
				MedService_Nick: (values.MedService_pzid>0)?values.MedService_pzNick:values.MedService_Nick,
				UslugaComplex_id: values.UslugaComplex_id,
				UslugaComplex_Name: values.UslugaComplex_Name,
				LpuSectionProfile_id: values.LpuSectionProfile_id,
				UslugaComplexMedService_id: values.UslugaComplexMedService_id
			};
			// todo: Если в форме выбора выбрали ПЗ вместо лаборатории, то вместо UslugaComplexMedService_id нужно выбрать связанную услугу
			// скорее всего выбор связанной услуги надо делать прямо на форме заказа, но пока связываемости услуг нет, поэтому просто обнуляем услугу
			if (values.MedService_pzid>0) { // Если в расписании выбрали лабораторию, а в форме заказа изменили на ПЗ
				params.UslugaComplexMedService_id = null; // то обнуляем услугу службы, если она была выбрана
				params.UslugaComplex_id = null;
				params.UslugaComplex_Name = null;
				// делаем подмену для правильного отображения заголовка окна
				params.MedService_Name = params.MedService_Nick;
				this.Wizard.params.MedService_id = params.MedService_id;
				this.Wizard.params.MedService_Name = params.MedService_Nick;
				this.Wizard.params.UslugaComplex_id = params.UslugaComplex_id;
				this.Wizard.params.UslugaComplex_Name = params.UslugaComplex_Name;
			}
			this.setStep(nextstep, params);

		}.createDelegate(this);
		
		if (isUserGroup('OuzSpec')) {
			
			this.setStep(nextstep, p);
		} else {
			getWnd('swEvnUslugaOrderEditWindow').show(p);
		}
	},
	
	reRecord: function(args) {
		// у нас есть отделение врача и сам врач
		var params = {
			Lpu_id: args.directionData.Lpu_did,
			LpuUnit_id: args.directionData.LpuUnit_did,
			LpuSection_id: args.directionData.LpuSection_did,
			MedPersonal_id: args.directionData.medpersonal_did,
			MedStaffFact_id: args.directionData.MedStaffFact_id,
			LpuSectionProfile_id:  args.directionData.LpuSectionProfile_id,
			LpuSection_Name: args.directionData.LpuSection_Name,
			MedPersonal_FIO: args.directionData.MedPersonal_Fio,
			MedService_id: args.directionData.MedService_id,
			MedService_Nick: null,
			type: args.directionData.type
		};
		this.Wizard.params = Ext.apply(this.Wizard.params,params);
		this.Wizard.params.step = 'RecordTTG';
		this.Wizard.params.isHimSelf = true;

		// сразу заполним directionData
		this.Wizard.params.directionData = args.directionData;
       /* this.Wizard.params.directionData['DirType_id'] = 16; // На поликлинический прием
        this.Wizard.params.directionData['MedStaffFact_id'] = params.MedStaffFact_id;
		this.Wizard.params.directionData['LpuUnit_did'] = params.LpuUnit_id;
		this.Wizard.params.directionData['Lpu_did'] = params.Lpu_id;
		this.Wizard.params.directionData['MedPersonal_did'] = params.MedPersonal_id;
		this.Wizard.params.directionData['MedPersonal_id'] = params.MedPersonal_id;
		this.Wizard.params.directionData['LpuSection_did'] = params.LpuSection_id;

		this.Wizard.params.directionData['LpuSectionProfile_id'] = params.LpuSectionProfile_id;
		this.Wizard.params.directionData['LpuSectionProfile_did'] = params.LpuSectionProfile_id;
		this.Wizard.params.directionData['Diag_id'] = args.Diag_id || '';*/
		this.Wizard.params.directionData['EvnDirection_Num'] = args.directionData.EvnDirection_Num;
		this.Wizard.params.directionData['EvnDirection_id'] = args.directionData.EvnDirection_id;
		this.Wizard.params.directionData['EvnDirection_pid'] = args.directionData.EvnDirection_pid;

		params.Lpu_Nick = args.directionData.Lpu_Nick;
		params.LpuUnit_Name = args.directionData.LpuUnit_Name;

		if (params.type == 'TimetableMedService') {
			this.setStep('RecordTTMS', params);
		} else {
			this.setStep('RecordTTG', params);
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
	recordWithParam: function(args) {
	var params = {
			Lpu_id: args.directionData.Lpu_did,
			LpuUnit_id: args.directionData.LpuUnit_did,
			LpuSection_id: args.directionData.LpuSection_did,
			MedPersonal_id: args.directionData.medpersonal_did,
			MedStaffFact_id: args.directionData.MedStaffFact_id,
			LpuSectionProfile_id:  args.directionData.LpuSectionProfile_id,
			LpuSection_Name: args.directionData.LpuSection_Name,
			MedPersonal_FIO: args.directionData.MedPersonal_Fio,
			MedService_id: null,
			MedService_Nick: null
		};
		this.Wizard.params = Ext.apply(this.Wizard.params,params);
		this.Wizard.params.directionData = args.directionData;
		this.Wizard.params.directionData['EvnDirection_Num'] = args.directionData.EvnDirection_Num;
		this.Wizard.params.directionData['EvnDirection_id'] = args.directionData.EvnDirection_id;
		this.Wizard.params.directionData['EvnDirection_pid'] = args.directionData.EvnDirection_pid;

		params.Lpu_Nick = args.directionData.Lpu_Nick;
		params.LpuUnit_Name = args.directionData.LpuUnit_Name;
		
		this.Filters.findById('rmwLpu_Nick').setValue(args.directionData.Lpu_Nick);
		this.Filters.findById('rmwLpuSectionProfile_id').setValue(args.directionData.LpuSectionProfile_id);
		this.setStep('SelectLpuUnit',params);
        this.refreshWindowTitle();
		//this.setStep('RecordTTG', params);
	},
	show: function()
	{
		sw.Promed.swRecordMasterWindow.superclass.show.apply(this, arguments);
		
		// todo: По идее еще до открытия нужно сбрасывать любые ранее открытые панели в null - нужно реализовать в listeners открытия.
		this.Wizard.params.step = ''; // очищаем шаг перед открытием
		this.Filters.clearFilters(true); // и сбрасываем 
		
		this.type = (arguments[0] && arguments[0].type)? arguments[0].type:'';
		
		// объект с параметрами АРМа, с которыми была открыта форма
		this.userMedStaffFact = (arguments[0] && arguments[0].userMedStaffFact) || {};
		
		// ФИО пациента. Желательно их передавать на форму поиска человека.
		if ( arguments[0] && typeof arguments[0].personData == 'object' ) {

			if (arguments[0] && arguments[0].userMedStaffFact && arguments[0].personData.AttachLpu_Name && arguments[0].userMedStaffFact.ARMType == "callcenter" && arguments[0].personData.AttachLpu_Name != lang['ne_prikreplen']) {
				this.Filters.findById('rmwLpu_Nick').setValue(arguments[0].personData.AttachLpu_Name);
			} else if (arguments[0] && arguments[0].userMedStaffFact && ['regpol','regpol6'].in_array(arguments[0].userMedStaffFact.ARMType)) {
				this.Filters.findById('rmwLpu_Nick').setValue(getGlobalOptions().lpu_nick);
			}

			this.Wizard.params.personData = new Object();
			this.Wizard.params.personData = arguments[0].personData;

			var personFIO = '';

			if ( this.Wizard.params.personData.Person_id ) {
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
			}
		} else {
			this.Wizard.params.personData = null;
			this.Wizard.params['Person_FIO'] = null;
		}
		
		this.Wizard.TTGRecordPanel.calendar.reset();
		this.Wizard.TTSRecordPanel.calendar.reset();
		this.Wizard.TTRRecordPanel.calendar.reset();
		this.Wizard.TTMSRecordPanel.calendar.reset();
		this.Wizard.TTMSVKRecordPanel.calendar.reset();
		
		this.Wizard.params.ARMType_id = this.userMedStaffFact.ARMType_id;
		
		// При открытии формы по умолчанию возвращаемся на первый шаг
		if (!this.type) {
			this.setStep('SelectLpuUnit');
			this.refreshWindowTitle();
		}
		
		var isKhak = (getGlobalOptions().region && getGlobalOptions().region.nick == 'khak');
		
		this.callback=(arguments[0] && arguments[0].callback) || Ext.emptyFn;
		this.Wizard.params.directionData = (arguments[0] && typeof arguments[0].directionData == 'object') ? arguments[0].directionData : new Object();
		this.onDirection = (arguments[0] && typeof arguments[0].onDirection == 'function') ? arguments[0].onDirection : Ext.emptyFn;
		this.onHide =  (arguments[0] && typeof arguments[0].onClose == 'function') ? arguments[0].onClose : Ext.emptyFn;

		if(this.type=="SMO" && isKhak){
			this.findById('rmwLpuUnitType_id').setValue(1);
			this.findById('rmwLpuUnitType_id').disable();
		}else{
			this.findById('rmwLpuUnitType_id').enable();
		}
		
		//присваиваем текущую дату
        this.Wizard.params.date = Ext.util.Format.date(new Date(), 'd.m.Y');

		if(this.type&&this.type.inlist(['rewrite']))
		{
			this.reRecord(arguments[0]);
		}
		if(this.type=='recwp')
		{
			this.recordWithParam(arguments[0]);
		}
		if ( this.type == 'LpuReg' )
		{
			// При открытии формы из регистратуры ЛПУ переходим сразу к выбору врача
			// Для автоматического выбора подразделения делается запрос к серверу.
			// Если выбран человек, то в первую очередь выбирается подразделение, где у него работает участковый врач
			// Если человек не выбран, то выбирается подразделение, к которому привязано текущее место работы
			// Если место работы привязано к поликлинике в целом, то не выбираем автоматически подразделение
			var params = new Object();
			if ( arguments[0] && arguments[0].personData ) {
				params.Person_id = arguments[0].personData.Person_id;
			}
			params.MedService_id = this.userMedStaffFact.MedService_id;
			Ext.Ajax.request(
				{
					url: C_REG_GETAPPRLU,
					params: params,
					failure: function(response, options)
					{
						this.getLoadMask().hide();
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_polucheniya_podrazdeleniya_s_servera']);
					}.createDelegate(this),
					success: function(response, action)
					{

						this.getLoadMask().hide();
						if (response.responseText)
						{
							var answer = Ext.util.JSON.decode(response.responseText);
							this.Wizard.params['Lpu_id'] = this.userMedStaffFact.Lpu_id;
							if (answer['LpuUnit_id']) {
								// Если удалось определить подразделение, сразу переходим на второй шаг
								this.Wizard.params['LpuUnit_id'] = answer['LpuUnit_id'];
								this.Wizard.params['MedStaffFact_id'] = answer['MedStaffFact_id'];
								this.Wizard.params.directionData['Lpu_did'] = this.userMedStaffFact.Lpu_id;
								this.Wizard.params.directionData['LpuUnit_did'] = answer['LpuUnit_id'];
								this.Wizard.params.directionData['LpuUnitType_SysNick'] = answer['LpuUnitType_SysNick'];

								this.Wizard.SelectLpuUnit.loadData({
									globalFilters: this.Wizard.params
								});

								this.Wizard.params.step = 'SelectMedPersonal';
								this.setStep('SelectMedPersonal');
								this.refreshWindowTitle();
							} else {
								this.Wizard.SelectLpuUnit.loadData({
									globalFilters: this.Wizard.params
								});
							}
						}
					}.createDelegate(this)
				});
		}
		else if ( this.type == 'HimSelf' ) { // врач записывает к себе
			this.recordHimSelf(arguments[0]);
		}
		else if ( this.type == 'SMO' ) { // просмотр расписания из АРМ СМО и АРМ ТФОМС
			
			this.setStep('SelectLpuUnit');
			this.refreshWindowTitle();
			
			// Нужно лочить запись
		} else if  ( this.type.inlist(['RecordTTGOneDay','RecordTTSOneDay','RecordTTMSOneDay']) ) {
			this.openDayListOnly(this.type, arguments[0].date);
		}
		//this.Wizard.TTGRecordPanel.loadSchedule(this.Wizard.TTGRecordPanel.calendar.value);
		
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
			TTGRecordPanel: null,
			/**
			 * Расписание коек в отделении на 3 недели
			 */
			TTSRecordPanel: null,
			/**
			 * Расписание служб/услуг на 2 недели
			 */
			TTMSRecordPanel: null,
			/**
			 * Расписание ресурсов на 2 недели
			 */
			TTRRecordPanel: null
		});
		
		// Панель расписания для записи в поликлинику
		this.Wizard.TTGRecordPanel = new sw.Promed.swTTGRecordPanel({
			id:'TTGRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) { // нужно показать расписание на день, если запись на бирку выполнена
				if (params.date)
					this.openDayListTTG(params.date);
			}.createDelegate(this),
			getOwner: function() {
				return this;
			}.createDelegate(this)
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
				if ( !person_id ) {
					return false;
				}
				var params = new Object();
				params.type = 'EvnPL';
				params.personId = person_id;
				params.TimetableGraf_id = TimetableGraf_id;

				switch ( getRegionNick() ) {
					case 'ufa':
						params.MedPersonal_id = this.Wizard.SelectMedPersonal.getSelectedParam('MedPersonal_id');
						params.LpuSectionProfile_id = this.Wizard.SelectMedPersonal.getSelectedParam('LpuSectionProfile_id');
						getWnd('swEvnPLBlankSettingsWindow').show(params);
					break;

					default:
						printEvnPLBlank(params);
					break;
				}
			}.createDelegate(this)
		});
		
		// Панель расписания для записи в стационар
		this.Wizard.TTSRecordPanel = new sw.Promed.swTTSRecordPanel({
			id:'TTSRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) { // нужно показать расписание на день, если запись на бирку выполнена
				if (params.date)
					this.openDayListTTS(params.date);
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

		// Панель расписания для записи на службу/услугу
		this.Wizard.TTMSRecordPanel = new sw.Promed.swTTMSRecordPanel({
			id:'TTMSRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) { // нужно показать расписание на день, если запись на бирку выполнена
				if (params.date)
					this.openDayListTTMS(params.date);
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

		// Панель расписания для записи на ресурс
		this.Wizard.TTRRecordPanel = new sw.Promed.swTTRRecordPanel({
			id:'TTRRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) { // нужно показать расписание на день, если запись на бирку выполнена
				if (params.date)
					this.openDayListTTR(params.date);
			}.createDelegate(this),
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
		
		// Панель расписания для записи на службу/услугу
		this.Wizard.TTMSVKRecordPanel = new sw.Promed.swTTMSRecordPanel({
			id:'TTMSVKRecordPanel',
			frame: false,
			border: false,
			region: 'center',
			onSaveRecord: function(params) { // нужно показать расписание на день, если запись на бирку выполнена
				if (params.date)
					this.openDayListTTMS(params.date);
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
				tabIndex: TABINDEX_RMW+11,
				xtype: 'button',
				id: 'rmwBtnMPSearch',
				text: lang['nayti'],
				iconCls: 'search16',
				handler: function()
				{
					this.applyFilter();
				}.createDelegate(this)
			},
			{
				tabIndex: TABINDEX_RMW+12,
				xtype: 'button',
				id: 'rmwBtnMPClear',
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
				labelWidth: 80,
				items: 
				[{
					fieldLabel: lang['nas_punkt'],
					allowBlank: true,
					anchor:'100%',
					enableKeyEvents: true,
					tabIndex: TABINDEX_RMW+1,
					hiddenName: 'KLTown_Name',
					id: 'rmwKLTown_Name',
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
					fieldLabel: lang['profil'],
					anchor:'100%',
					tabIndex: TABINDEX_RMW+4,
					hiddenName: 'LpuSectionProfile_id',
					id: 'rmwLpuSectionProfile_id',
					lastQuery: '',
					width : 200,
					xtype: 'swlpusectionprofilecombo',
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
					tabIndex: TABINDEX_RMW+7,
					anchor:'100%',
					hiddenName: 'LpuType_id',
					id: 'rmwLpuType_id',
					lastQuery: '',
					xtype: 'swlpuagetypecombo',
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
				labelWidth: 125,
				items: 
				[{
					fieldLabel: lang['ulitsa'],
					allowBlank: true,
					anchor:'100%',
					enableKeyEvents: true,
					tabIndex: TABINDEX_RMW+2,
					hiddenName: 'KLStreet_Name',
					id: 'rmwKLStreet_Name',
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
					fieldLabel: lang['fio_vracha'],
					allowBlank: true,
					anchor:'100%',
					enableKeyEvents: true,
					tabIndex: TABINDEX_RMW+5,
					hiddenName: 'MedPersonal_FIO',
					id: 'rmwMedPersonal_FIO',
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
					tabIndex: TABINDEX_RMW+8,
					anchor:'100%',
					hiddenName: 'LpuUnitType_id',
					id: 'rmwLpuUnitType_id',
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
				}]
			}, 
			{
				layout: 'form',
				columnWidth: .25,
				labelAlign: 'right',
				labelWidth: 90,
				items: 
				[{
					fieldLabel: lang['dom'],
					allowBlank: true,
					enableKeyEvents: true,
					tabIndex: TABINDEX_RMW+3,
					hiddenName: 'KLHouse',
					id: 'rmwKLHouse',
					anchor:'100%',
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
					fieldLabel: lang['mo'],
					allowBlank: true,
					enableKeyEvents: true,
					tabIndex: TABINDEX_RMW+6,
					hiddenName: 'Lpu_Nick',
					id: 'rmwLpu_Nick',
					anchor:'100%',
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
					fieldLabel: lang['adres_mo'],
					enableKeyEvents: true,
					tabIndex: TABINDEX_RMW+7,
					anchor:'100%',
					hiddenName: 'LpuUnit_Address',
					id: 'rmwLpuUnit_Address',
					lastQuery: '',
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
			},
			{
				layout: 'form',
				columnWidth: .25,
				labelAlign: 'right',
				labelWidth: 120,
				items:
				[{
					anchor: '100%',
					comboSubject: 'LpuRegionType',
					hiddenName: 'LpuRegionType_id',
					id: 'rmwLpuRegionType_id',
					fieldLabel: lang['tip_prikrepleniya'],
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
			}],
			
			/**
			 * Очистка фильтров с применением к спискам
			 */
			clearFilters: function(scheduleLoad)
			{
				this.Filters.getForm().reset();
				this.applyFilter();
				
			}.createDelegate(this),
			
			/**
			 * Получаем установленные фильтры
			 */
			getFilters: function(){
				return new Object({
					start: 0,
					limit: 100,
					Filter_Lpu_Nick: this.findById('rmwLpu_Nick').getValue(),
					Filter_LpuSectionProfile_id: this.findById('rmwLpuSectionProfile_id').getValue(),
					Filter_MedPersonal_FIO: this.findById('rmwMedPersonal_FIO').getValue(),
					Filter_KLTown_Name: this.findById('rmwKLTown_Name').getValue(),
					Filter_KLStreet_Name: this.findById('rmwKLStreet_Name').getValue(),
					Filter_KLHouse: this.findById('rmwKLHouse').getValue(),
					Filter_LpuUnitType_id: this.findById('rmwLpuUnitType_id').getValue(),
					Filter_LpuType_id: this.findById('rmwLpuType_id').getValue(),
					Filter_LpuUnit_Address: this.findById('rmwLpuUnit_Address').getValue(),
					Filter_LpuRegionType_id: this.findById('rmwLpuRegionType_id').getValue()
				});
			}
		});
		
		this.Wizard.SelectLpuUnit = new sw.Promed.ViewFrame(
		{
			id: 'rmwWizard_LpuUnit',
			disableCheckRole: true,
			region: 'center',
			object: 'LpuUnit',
			border: true,
			dataUrl: C_REG_RECORDLULIST,
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
				{name:'action_edit', text: lang['vyibrat'],
					handler: function() {

						// В зависимости от типа выбранного подразделения делаем разное
						var unit_type = this.Wizard.SelectLpuUnit.getSelectedParam('LpuUnitType_SysNick');
						switch(unit_type){
							case 'polka':
								this.setStep('SelectMedPersonal');
								break;
							case 'stac': case 'dstac': case 'hstac': case 'pstac': 
								this.setStep('SelectLpuSection');
								break;
							case 'parka':
							default:
								this.setStep('SelectMedService');
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
				if ((this.Wizard.params.LpuUnit_id) && (isData))
				{
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
		
		this.Wizard.SelectMedPersonal = new sw.Promed.ViewFrame(
		{
			id: 'rmwWizard_MedPersonal',
			disableCheckRole: true,
			region: 'center',
			object: 'MedPersonal',
			border: true,
			dataUrl: C_REG_RECORDMSFLIST,
			toolbar: true,
			autoLoadData: false,
			isScrollToTopOnLoad: false,

			stringfields:
			[
				{name: 'MedStaffFact_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'MedPersonal_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuSectionAge_id', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
				{name: 'Comments', width: 24, header: '...'},
				{name: 'MedPersonal_FIO', id: 'autoexpand', header: lang['vrach']},
				{name: 'LpuSectionProfile_Name', width: 250, header: lang['profil']},
				{name: 'LpuSectionAge_Name', width: 120, header: lang['vozrastnaya_gruppa']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'LpuRegion_Names', width: 100, header: lang['uchastki']},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {this.setStep('RecordTTG');}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				// После того как загрузили данные, надо снова выбрать предыдущую запись
				if ((this.Wizard.params.MedStaffFact_id) && (isData))
				{
					GridAtRecord(this.Wizard.SelectMedPersonal.getGrid(), 'MedStaffFact_id', this.Wizard.params.MedStaffFact_id, 2);
				}
				this.setFilterLpuSectionProfile(this.Wizard.SelectMedPersonal.getGrid());
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
		
		this.Wizard.SelectLpuSection = new sw.Promed.ViewFrame(
		{
			id: 'rmwWizard_LpuSection',
			disableCheckRole: true,
			region: 'center',
			object: 'LpuSection',
			border: true,
			dataUrl: C_REG_RECORDLSLIST,
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'LpuSection_id', type: 'int', header: 'ID', key: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSectionAge_id', type: 'int', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true, isparams: true},
				{name: 'Comments', width: 24, header: '...'},
				{name: 'LpuSection_Name', id: 'autoexpand', header: lang['otdelenie']},
				{name: 'LpuSectionProfile_Name', width: 250, header: lang['profil']},
				{name: 'LpuSectionAge_Name', width: 120, header: lang['vozrastnaya_gruppa']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'LpuSectionType_id', width: 100, header: lang['tip']},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {this.setStep('RecordTTS');}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				//
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
		
		this.Wizard.SelectMedService = new sw.Promed.ViewFrame(
		{
			id: 'rmwWizard_MedService',
			disableCheckRole: true,
			region: 'center',
			object: 'MedService',
			border: true,
			dataUrl: C_REG_RECORDMSLIST,
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'MedService_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplexMedService_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplexResource_id', type: 'int', header: 'ID', key: true},
				{name: 'allowDirection', hidden: true, type: 'int'},
				{name: 'Resource_id', hidden: true},
				{name: 'UslugaComplex_id', hidden: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuUnitType_id', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true},
				{name: 'MedServiceType_SysNick', hidden: true},
				{name: 'MedService_Nick', hidden: true},
				{name: 'MedService_Name', hidden: true},
				{name: 'MedService_Caption', width: 200, header: lang['slujba']},
				{name: 'UslugaComplex_Name', id: 'autoexpand', header: lang['usluga']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {
					var sysnick = this.Wizard.SelectMedService.getSelectedParam('MedServiceType_SysNick');
					var allow_direction = this.Wizard.SelectMedService.getSelectedParam('allowDirection');
					if (!sysnick || !allow_direction) {
						return false;
					}
					if (sysnick.inlist(['func']) && !Ext.isEmpty(this.Wizard.SelectMedServiceLpuLevel.getSelectedParam('UslugaComplexResource_id'))) {
						this.addOrder(this.Wizard.SelectMedService.getGrid().getSelectionModel().getSelected(), 'RecordTTR');
					} else {
						this.addOrder(this.Wizard.SelectMedService.getGrid().getSelectionModel().getSelected(), 'RecordTTMS');
					}
					return true;
				}.createDelegate(this) },
				//{name:'action_edit', disabled: false, hidden: true, handler: function() {this.setStep('RecordTTMS');}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				//
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
		
		this.Wizard.SelectMedServiceLpuLevel = new sw.Promed.ViewFrame(
		{
			id: 'rmwWizard_MedServiceLpuLevel',
			disableCheckRole: true,
			region: 'center',
			object: 'MedService',
			border: true,
			dataUrl: C_REG_RECORDMSLIST,
			toolbar: false,
			autoLoadData: false,

			stringfields:
			[
				{name: 'MedService_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplexMedService_id', type: 'int', header: 'ID', key: true},
				{name: 'UslugaComplex_id', hidden: true},
				{name: 'LpuUnit_id', hidden: true, isparams: true},
				{name: 'Lpu_id', hidden: true, isparams: true},
				{name: 'LpuSection_id', hidden: true},
				{name: 'LpuUnitType_id', hidden: true},
				{name: 'LpuSectionProfile_id', hidden: true},
				{name: 'MedService_Nick', hidden: true},
				{name: 'MedService_Name', hidden: true},
				{name: 'MedService_Caption', width: 200, header: lang['slujba']},
				{name: 'UslugaComplex_Name', id: 'autoexpand', header: lang['usluga']},
				{name: 'Queue', width: 80, header: lang['ochered']},
				{name: 'Dates', width: 500, header: lang['datyi_priema']}
			],
			actions:
			[
				{name:'action_add', disabled: true, hidden: true},
				{name:'action_edit', text: lang['vyibrat'], handler: function() {this.setStep('RecordTTMSVK');}.createDelegate(this) },
				//{name:'action_edit', disabled: false, hidden: true, handler: function() {this.setStep('RecordTTMS');}.createDelegate(this) },
				{name:'action_view', disabled: true, hidden: true},
				{name:'action_delete', disabled: true, hidden: true}
			],
			onLoadData: function(isData)
			{
				//
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
		
		this.LpuUnitDescriptionPanel = new Ext.Panel({
			id: 'RMW_LpuUnitDescriptionPanel',
			showTitle: false,
			region: 'north'
		});
		
		// Нижняя панель во втором шаге мастера. В зависимости от выбранного типа подразделения отображаются разные данные
		this.Wizard.BottomPanel = new Ext.Panel(
		{
			hidden: true,
			height: 300,
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
					defaults: {split: true},
					layout: 'border',
					items:
					[
						this.Wizard.SelectLpuUnit,
						this.LpuUnitDescriptionPanel,
						this.Wizard.BottomPanel
					],
					split: true
				},
				this.Wizard.SelectMedServiceLpuLevel,
				this.Wizard.TTGRecordPanel,
				this.Wizard.TTSRecordPanel,
				this.Wizard.TTMSRecordPanel,
				this.Wizard.TTGRecordOneDayPanel,
				this.Wizard.TTSRecordOneDayPanel,
				this.Wizard.TTMSRecordOneDayPanel,
				this.Wizard.TTRRecordPanel,
				this.Wizard.TTRRecordOneDayPanel,
			]
		});
		
		Ext.apply(this, 
		{
			layout: 'border',
			items: 
			[
				this.Filters,
				this.Wizard.Panel
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
					this.setStep('SelectLpuUnit'); 
				}.createDelegate(this)
			},
			/*{
				iconCls: 'medservice16',
				text: lang['slujbyi_lpu'],
				handler: function() { 
					this.setStep('SelectMedServiceLpuLevel'); 
				}.createDelegate(this)
			},*/
			{
				text: '-'
			}, 
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_RMW);
				}.createDelegate(this),
				tabIndex: TABINDEX_MPSCHED + 98
			},
			{
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE,
				handler: function() { this.hide(); }.createDelegate(this)
			}]
		});
		
		this.Wizard.params = new Object({
			'step': ''
		});
		
		//this.Wizard.Panel.doLayout();
		
		sw.Promed.swRecordMasterWindow.superclass.initComponent.apply(this, arguments);
	}
});
