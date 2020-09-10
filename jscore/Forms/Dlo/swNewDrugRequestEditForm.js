/**
* swNewDrugRequestEditForm - форма ввода заявки (доработанная копия swDrugRequestEditForm).
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Andrew Markoff
* @version      09.2009
* @comment      
*
*
* @input data: 
               
               
*/
/*NO PARSE JSON*/
sw.Promed.swNewDrugRequestEditForm = Ext.extend(sw.Promed.BaseForm,
{
	title:lang['zayavka_na_lekarstvennyie_sredstva_novaya'],
	layout: 'border',
	id: 'NewDrugRequestEditForm',
	maximized: true,
	maximizable: false,
	shim: false,
	buttons:
	[
		{
			text: BTN_FRMSAVE,
			id: 'ndreButtonSave',
			tabIndex: 4131,
			tooltip: lang['sohranit'],
			iconCls: 'save16',
			handler: function()
			{
				this.ownerCt.DrugRequestSave();
				this.ownerCt.returnFunc(this.ownerCt.owner, 1);
				this.ownerCt.hide();
			}
		},
		{
			text: '-'
		},
		{
			text: BTN_FRMHELP,
			tabIndex: 4132,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		},
		{
			text: BTN_FRMCLOSE,
			id: 'ndreButtonCancel',
			tabIndex: 4133,
			tooltip: lang['zakryit'],
			iconCls: 'cancel16',
			handler: function()
			{
				this.ownerCt.hide();
				this.ownerCt.returnFunc(this.ownerCt.owner, -1);
			}
		}
	],
	returnFunc: function(owner) {},
	listeners:
	{
		hide: function()
		{
			this.returnFunc(this.owner, -1);
		}
	},
	setDrugScheme: function(drug_scheme) {
		if (drug_scheme != 'rls' && drug_scheme != 'dbo') { //установка схемы по умолчанию
			this.DrugScheme = 'rls';
		} else {
			this.DrugScheme = drug_scheme;
		}
	},
	updatePersonInformationPanel: function(mode, data) {
		var wnd = this;
		if (mode == 'show') {
			wnd.PersonInformationPanel.setData('medpersonal_fio', ' ');
			wnd.findById('ndreDrugRequestMedPersonalEditPanel').hide();
			wnd.findById('ndreDrugRequestMedPersonalEditPanelLeft').hide();
			wnd.PersonInformationPanel.show();
			wnd.PersonInformationPanel.showData();
		}
		if (mode == 'hide') {
			wnd.findById('ndreDrugRequestMedPersonalEditPanel').show();
			wnd.findById('ndreDrugRequestMedPersonalEditPanelLeft').show();
			wnd.PersonInformationPanel.hide();
		}
		if (mode == 'set_data' && data) {
			var workplace = '';
			if (data.MedPersonal_FIO)
				wnd.PersonInformationPanel.setData('medpersonal_fio', data.MedPersonal_FIO);
			if (data.DrugRequestPeriod_Name)
				wnd.PersonInformationPanel.setData('drugrequest_period', data.DrugRequestPeriod_Name);
			if (data.PersonRegisterType_Name)
				wnd.PersonInformationPanel.setData('person_register_type', ', '+data.PersonRegisterType_Name);
			if (data.DrugRequestKind_Name)
				wnd.PersonInformationPanel.setData('drugrequest_kind', ', '+data.DrugRequestKind_Name);
			if (data.DrugRequestStatus_Name)
				wnd.PersonInformationPanel.setData('drugrequest_status', data.DrugRequestStatus_Name);
			if (data.DrugRequest_YoungChildCount != null)
				wnd.PersonInformationPanel.setData('child_count', data.DrugRequest_YoungChildCount > 0 ? data.DrugRequest_YoungChildCount : lang['net']);

			if (data.Lpu_Name)
				workplace += ', '+data.Lpu_Name;
			if (data.LpuUnit_Name)
				workplace += ', '+data.LpuUnit_Name;
			if (data.LpuSection_Name)
				workplace += ', '+data.LpuSection_Name;

			if (workplace != '')
				wnd.PersonInformationPanel.setData('work_place', workplace);
			wnd.PersonInformationPanel.showData();
		}
	},
	doCalculate: function() {
		var wnd = this;
		var panel = wnd.InformationPanel;

		var request_data = new Object({
			FedDrugRequestQuota_Reserve: 0,
			RegDrugRequestQuota_Reserve: 0
		});
		Ext.apply(request_data, wnd.MoRequest_Data);

		var fed_reserv_summ = 0; //Сумма заявки МО (фед.)
		var reg_reserv_summ = 0; //Сумма заявки МО (рег.)
		var fed_limit_reserv = request_data.FedDrugRequestQuota_Reserve*1; //Лимит резерва (фед.)
		var reg_limit_reserv = request_data.RegDrugRequestQuota_Reserve*1; //Лимит резерва (рег.)
		var fed_person_summ = 0; //Сумма персональной заявки (фед.)
		var reg_person_summ = 0; //Сумма персональной заявки (рег.)
		var fed_limit_str = ""; //Сообщение о лимите (фед.)
		var reg_limit_str = ""; //Сообщение о лимите (рег.)
		var fed_overflow = 0; //Превышение (фед.)
		var reg_overflow = 0; //Превышение (рег.)
		var overflow = 0; //Превышение
		var overflow_display = "none"; //Отображение превышения

		var fed_limit_str = fed_limit_reserv > 0 ? "установлен "+fed_limit_reserv+"% от персональной заявки" : "не установлен";
		var reg_limit_str = reg_limit_reserv > 0 ? "установлен "+reg_limit_reserv+"% от персональной заявки" : "не установлен";

		panel.clearData();
		wnd.DrugReservePanel.getGrid().getStore().each(function(item) {
			if (item.get('DrugRequestRow_Summa') > 0) {
				if (item.get('DrugRequestType_id') == 1)
					fed_reserv_summ += item.get('DrugRequestRow_Summa')*1;
				else
					reg_reserv_summ += item.get('DrugRequestRow_Summa')*1;
			}
		});

		fed_person_summ = wnd.DrugRequest_SummaFedAll > 0 ? wnd.DrugRequest_SummaFedAll - fed_reserv_summ : 0;
		reg_person_summ = wnd.DrugRequest_SummaRegAll > 0 ? wnd.DrugRequest_SummaRegAll - reg_reserv_summ : 0;

		fed_overflow = fed_limit_reserv > 0 ? fed_reserv_summ-(fed_person_summ*fed_limit_reserv/100) : 0;
		reg_overflow = reg_limit_reserv > 0 ? reg_reserv_summ-(reg_person_summ*reg_limit_reserv/100) : 0;
		overflow = (fed_overflow > 0 ? fed_overflow : 0) + (reg_overflow > 0 ? reg_overflow : 0);

		if (overflow > 0)
			overflow_display = "block";

		panel.setData('reserv_summ', Math.round((fed_reserv_summ + reg_reserv_summ)*100)/100);
		panel.setData('fed_limit_str', fed_limit_str);
		panel.setData('reg_limit_str', reg_limit_str);
		panel.setData('fed_person_summ', fed_person_summ);
		panel.setData('reg_person_summ', reg_person_summ);
		panel.setData('overflow', Math.round(overflow*100)/100);
		panel.setData('overflow_display', overflow_display);
		panel.showData();
	},
	loadSprData: function()
	{
		frm = this;
		frm.findById('ndreDrugRequestPeriod_id').getStore().reload();
		
		frm.findById('ndreLpuUnit_id').getStore().load(
		{
			params:
			{
				Object: 'LpuUnit',
				LpuUnit_id: '',
				Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
				LpuUnit_Name: ''
			},
			callback: function()
			{
				if (frm.findById('ndreLpuSection_id').getValue()>0)
				{
					//form.findById('ndreLpuSection_id').getValue();
					frm.findById('ndreLpuSection_id').getStore().load(
					{
						params:
						{
							Object: 'LpuSection',
							LpuSection_id: '',
							Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
							LpuUnit_id: frm.findById('ndreLpuUnit_id').getValue(),
							LpuSection_Name: ''
						},
						callback: function()
						{
							frm.findById('ndreLpuSection_id').setValue(frm.findById('ndreLpuSection_id').getValue());
							// Заполним LpuUnit из LpuSection
							{
								var combo = frm.findById('ndreLpuSection_id');
								idx = combo.getStore().indexOfId(combo.getValue());
								if (idx<0)
									idx = combo.getStore().findBy(function(rec) { return rec.get('LpuSection_id') == combo.getValue(); });
								if (idx<0)
									return;
								var row = combo.getStore().getAt(idx);
								frm.findById('ndreLpuUnit_id').setValue(row.data.LpuUnit_id); 
							}
							frm.findById('ndreMedPersonal_id').getStore().load(
							{
								params:
								{
									LpuSection_id: frm.findById('ndreLpuSection_id').getValue(),
									Lpu_id: frm.Lpu_id || getGlobalOptions().lpu_id,
									IsDlo: (!getGlobalOptions().isOnko && !getGlobalOptions().isRA && frm.DrugScheme == 'dbo')?1:0
								},
								callback: function()
								{
									frm.findById('ndreMedPersonal_id').setValue(frm.findById('ndreMedPersonal_id').getValue());
								}
							});
						}
					});
					
				}
			}
		});
	},
	setEnabled: function()
	{
		var groups = getGlobalOptions().groups;

		if (this.action=='view' || this.action=='edit')
		{
			this.findById('ndreRegionDrugRequest_id').disable();
			this.findById('ndrePersonRegisterType_id').disable();
			this.findById('ndreDrugRequestPeriod_id').disable();
			this.findById('ndreLpuUnit_id').disable();
			this.findById('ndreLpuSection_id').disable();
			this.findById('ndreMedPersonal_id').disable();
			this.findById('ndreLpuUnitPanel').setVisible(false);
			this.findById('ndreYoungChildCountPanel').setVisible(true);
			this.Actions.action_DrugRequestPrint.setDisabled(false);
			this.Actions.action_DrugRequestSetStatus.setDisabled((this.action=='view') || (this.findById('ndreDrugRequestStatus_id').getValue()==3));
			this.PersonTab.unhideTabStripItem('tab_reserve');
		}
		else 
		{
			this.findById('ndreRegionDrugRequest_id').enable();
			this.findById('ndrePersonRegisterType_id').enable();
			this.findById('ndreDrugRequestPeriod_id').enable();
			this.findById('ndreLpuUnit_id').enable();
			this.findById('ndreLpuSection_id').enable();
			this.findById('ndreMedPersonal_id').enable();
			this.Actions.action_DrugRequestPrint.setDisabled(true);
			this.Actions.action_DrugRequestSetStatus.setDisabled(true);
		}
		
		if (this.action=='view') {
			this.buttons[0].disable();
			this.findById('ndreDrugRequest_YoungChildCount').disable();
			// В зависимости от статуса, при статусе равном трем доступность редактирования
			/*if ((this.findById('ndreDrugRequestStatus_id').getValue()==3) || (getGlobalOptions().isMinZdrav))
			{
				this.DrugPacientPanel.setReadOnly(false);
				this.DrugReservePanel.setReadOnly(false);
				this.PacientPanel.setReadOnly(false);
				this.EditPanel.findById('ndreDrugProtoMnn_id').enable();
				this.EditPanel.findById('ndreDrugRequestRow_Kolvo').enable();
				this.EditPanel.findById('ndreDrugRequestType_id').enable();
				this.EditPanel.findById('ndreButtonAdd').enable();
				this.EditPanel.findById('ndreIsDrug').setDisabled(!getGlobalOptions().isMinZdrav);
				this.EditReservePanel.findById('ndrerDrugProtoMnn_id').enable();
				this.EditReservePanel.findById('ndrerDrugRequestRow_Kolvo').enable();
				this.EditReservePanel.findById('ndrerDrugRequestType_id').enable();
				this.EditReservePanel.findById('ndrerButtonAdd').enable();
				this.EditReservePanel.findById('ndrerIsDrug').setDisabled(!getGlobalOptions().isMinZdrav);
			}
			else
			{*/
				this.DrugPacientPanel.setReadOnly(true);
				this.DrugReservePanel.setReadOnly(true);
				this.PacientPanel.setReadOnly(true);
				this.EditPanel.findById('ndreDrugProtoMnn_id').disable();
				this.EditPanel.findById('ndreDrugRequestRow_Kolvo').disable();
				this.EditPanel.findById('ndreDrugRequestType_id').disable();
				this.EditPanel.findById('ndreButtonAdd').disable();
				this.EditPanel.findById('ndreIsDrug').disable();
				this.EditReservePanel.findById('ndrerDrugProtoMnn_id').disable();
				this.EditReservePanel.findById('ndrerDrugRequestRow_Kolvo').disable();
				this.EditReservePanel.findById('ndrerDrugRequestType_id').disable();
				this.EditReservePanel.findById('ndrerButtonAdd').disable();
				this.EditReservePanel.findById('ndrerIsDrug').disable();
			/*}*/

			if ((this.findById('ndreDrugRequestStatus_id').getValue()==2))
			{
				this.Actions.action_DrugRequestSetStatus.setText(lang['redaktirovat']);
				if (!this.findById('ndreDrugRequestSetStatus').pressed)
					this.findById('ndreDrugRequestSetStatus').toggle();
			}
		} else {
			this.buttons[0].enable();
			this.PacientPanel.setReadOnly(false);
			this.DrugPacientPanel.setReadOnly(false);
			this.DrugReservePanel.setReadOnly(false);
			this.findById('ndreDrugRequest_YoungChildCount').enable();
			this.EditPanel.findById('ndreDrugProtoMnn_id').enable();
			this.EditPanel.findById('ndreDrugRequestRow_Kolvo').enable();
			this.EditPanel.findById('ndreDrugRequestType_id').enable();
			this.EditPanel.findById('ndreButtonAdd').enable();
			this.EditPanel.findById('ndreIsDrug').enable();
			this.EditReservePanel.findById('ndrerDrugProtoMnn_id').enable();
			this.EditReservePanel.findById('ndrerDrugRequestRow_Kolvo').enable();
			this.EditReservePanel.findById('ndrerDrugRequestType_id').enable();
			this.EditReservePanel.findById('ndrerButtonAdd').enable();
			this.EditReservePanel.findById('ndrerIsDrug').enable();
			this.Actions.action_DrugRequestSetStatus.setText(lang['sformirovat']);
			if (this.findById('ndreDrugRequestSetStatus').pressed)
			{
				this.findById('ndreDrugRequestSetStatus').toggle();
			}
			// Принудительно вызываем onRowSelect
			if (this.PacientPanel.getCount()>0)
			{
				//в новой версии
				//this.PacientPanel.focus();
				// в старой версии 
				if (this.PacientPanel.getGrid().getSelectionModel().getSelected())
					this.PacientPanel.onRowSelect(this.PacientPanel.getGrid().getSelectionModel(), 0, this.PacientPanel.getGrid().getSelectionModel().getSelected());
				else 
				{
					// Может быть на персоне муха не валялась, но на всякий случай почистим его от мухиных следов 
					this.clearValues();
				}
			}
		}

		if (
				this.action != 'view' &&
				this.findById('ndreDrugRequestStatus_id').getValue() == 1 &&
				(groups.indexOf("SuperAdmin") > -1 || groups.indexOf("LpuAdmin") > -1 || groups.indexOf("LpuUser") > -1) &&
				this.findById('ndreLpu_id').getValue() == getGlobalOptions().lpu_id
			) {
			this.PacientPanel.getAction('action_ndre_actions').enable();
		} else {
			this.PacientPanel.getAction('action_ndre_actions').disable();
		}
		
		// Заголовок формы
		switch (this.action)
		{
			case 'add':
				this.setTitle(lang['zayavka_na_lekarstvennyie_sredstva_dobavlenie']);
				break;
			case 'edit':
				this.setTitle(lang['zayavka_na_lekarstvennyie_sredstva_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(lang['zayavka_na_lekarstvennyie_sredstva_prosmotr']);
				break;
		}
	},
	clearValues: function ()
	{
		// Обнуление данных
		this.EditPanel.findById('ndreDrugRequestRow_id').setValue('');
		this.EditPanel.findById('ndrePerson_id').setValue('');
		this.EditPanel.findById('ndreDrugProtoMnn_id').setValue('');
		this.EditPanel.findById('ndreDrugRequestRow_Kolvo').setValue('');
		this.EditPanel.findById('ndreDrugRequestType_id').setValue('');
		this.findById('ndreButtonEndEdit').setVisible(false);
		this.findById('ndrerButtonEndEdit').setVisible(false);
		this.findById('ndreIsDrugPanel').setVisible(getGlobalOptions().isMinZdrav);
		if (this.findById('ndrerIsDrug').pressed)
			this.findById('ndrerIsDrug').toggle();
		if (this.findById('ndreIsDrug').pressed)
			this.findById('ndreIsDrug').toggle();
	},
	editDrugPanelRow: function(mode, action) {
		var wnd = this;
		var params = new Object();
		var panel = mode == 'pacient' ? wnd.DrugPacientPanel : wnd.DrugReservePanel;
		var record =  null;

		if(action == 'edit') {
			record = panel.getGrid().getSelectionModel().getSelected();
			if (record && record.get('DrugComplexMnn_id') > 0) {
				Ext.apply(params, record.data);
			} else {
				return false;
			}
		} else if (action == 'add' && mode == 'pacient') {
			params.DrugRequestType_id = wnd.findById('ndreDrugRequestType_id').getValue();
		}

		var DrugRequest_id = wnd.findById('ndreDrugRequest_id').getValue();
		var DrugRequestPeriod_id = wnd.findById('ndreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = wnd.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
		var Person_id = mode == 'pacient' ? wnd.findById('ndrePerson_id').getValue() : null;
		var MorbusCombo = wnd.findById('ndrePersonRegisterType_id');
		var PersonRegisterType_id = MorbusCombo.getValue();
		var PersonRegisterType_SysNick = null;

		var idx = MorbusCombo.getStore().findBy(function(rec) { return rec.get('PersonRegisterType_id') == PersonRegisterType_id; });
		if (idx > -1) {
			PersonRegisterType_SysNick = MorbusCombo.getStore().getAt(idx).get('PersonRegisterType_SysNick');
		}

		params.allowAllDrugRequestType = true;
		if (mode == 'pacient') {
			record = wnd.PacientPanel.getGrid().getSelectionModel().getSelected();
			if (record.get('Person_IsFedLgotCurr') == 'false' || record.get('Person_IsRegLgotCurr') == 'false') {
				params.allowAllDrugRequestType = false;
			}
			if (wnd.DrugScheme == 'rls'/* && record.get('Person_IsFedLgotCurr') == 'false' && record.get('Person_IsRegLgotCurr') == 'false'*/) { //на данный момент для схемы rls, ограничение на выбор льготы снято
				params.allowAllDrugRequestType = true;
			}
		}

		params.DrugRequestPeriod_id = DrugRequestPeriod_id;
		params.PersonRegisterType_id = PersonRegisterType_id;
		params.PersonRegisterType_SysNick = PersonRegisterType_SysNick;
		params.action = action;
		params.owner = wnd.DrugPacientPanel;
		params.mode = mode;
		params.onSave = function(data) {
			var edit_wnd = this;
			var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestEditForm'), { msg: LOAD_WAIT });
			loadMask.show();
			Ext.Ajax.request({
				url: '/?c=DrugRequest&m=index&method=saveDrugRequestRow',
				params: {
					DrugRequestRow_id: data.DrugRequestRow_id || '',
					IsDrug: null, // Что сохраняем: торговое или МНН
					DrugRequest_id: DrugRequest_id,
					Person_id: Person_id,
					DrugComplexMnn_id: data.DrugComplexMnn_id,
					TRADENAMES_id: data.TRADENAMES_id,
					DrugRequestRow_Kolvo: data.DrugRequestRow_Kolvo,
					DrugRequestType_id: data.DrugRequestType_id,
					DrugRequestRow_DoseOnce: data.DrugRequestRow_DoseOnce,
					DrugRequestRow_DoseDay: data.DrugRequestRow_DoseDay,
					DrugRequestRow_DoseCource: data.DrugRequestRow_DoseCource,
					Okei_oid: data.Okei_oid,
					DrugRequestPeriod_id: DrugRequestPeriod_id,
					MedPersonal_id: MedPersonal_id,
					Merge: (Person_id <= 0)
				},
				callback: function(opt, success, resp) {
					loadMask.hide();
					if (mode == 'pacient') {
						panel.loadData({
							globalFilters:{Person_id: Person_id, DrugRequest_id: DrugRequest_id, DrugRequestPeriod_id: DrugRequestPeriod_id}, noFocusOnLoad:true
						});
					} else {
						panel.loadData({
							globalFilters:{DrugRequest_id: DrugRequest_id, DrugRequestPeriod_id: DrugRequestPeriod_id, MedPersonal_id: MedPersonal_id},
							noFocusOnLoad:true
						});
					}
					edit_wnd.hide();
					//win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').focus();
				}
			});
		}

		if (mode == 'reserve' || Person_id > 0)
			getWnd('swDrugRequestRowEditWindow').show(params);
	},
	show: function()
	{
		var wnd = this;
		var base_form = wnd.findById('ndreDrugRequestParamsPanel').getForm();

		sw.Promed.swNewDrugRequestEditForm.superclass.show.apply(this, arguments);
		var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestEditForm'), { msg: LOAD_WAIT });

		this.PersonTab.hideTabStripItem('tab_reserve');
		var form = this;
		this.insertPersonBottomBar();
		loadMask.show();

		if (!arguments[0])
		{
			Ext.Msg.alert(lang['oshibka'], lang['otsutstvuyut_neobhodimyie_parametryi']);
			this.hide();
			return false;
		}
		if (arguments[0].callback)
			this.returnFunc = arguments[0].callback;

		this.owner = arguments[0].owner || null;
		this.action = arguments[0].action ? arguments[0].action : 'add';
		this.DrugRequest_id = arguments[0].DrugRequest_id || null;
		this.Lpu_id = arguments[0].Lpu_id ? arguments[0].Lpu_id : getGlobalOptions().lpu_id;
		this.LpuSection_id = arguments[0].LpuSection_id || null;
		this.MedPersonal_id = arguments[0].MedPersonal_id || null;
		this.DrugRequestStatus_id = arguments[0].DrugRequestStatus_id || null;
		this.DrugRequestPeriod_id = arguments[0].DrugRequestPeriod_id || null;
		this.PersonRegisterType_id = arguments[0].PersonRegisterType_id || null;
		this.MoRequest_Data = arguments[0].MoRequest_Data || null;
		this.DrugRequest_SummaFedAll = arguments[0].DrugRequest_SummaFedAll || null;
		this.DrugRequest_SummaRegAll = arguments[0].DrugRequest_SummaRegAll || null;

		/*this.DrugPacientPanel.addActions({
			name:'action_dose_edit',
			text:lang['redaktirovanie_dozirovok'],
			tooltip: lang['redaktirovanie_dozirovok'],
			handler: function() {
				var view_frame = form.DrugPacientPanel;
				var selected_record = view_frame.getGrid().getSelectionModel().getSelected();
				if (selected_record.get('DrugRequestRow_id') > 0) {
					getWnd('swDrugRequestRowDoseEditWindow').show({
						callback: function() { view_frame.refreshRecords(null,0); this.hide(); },
						action: 'edit',
						DrugRequestRow_id: selected_record.get('DrugRequestRow_id'),
						DrugRequestRow_DoseOnce: selected_record.get('DrugRequestRow_DoseOnce'),
						DrugRequestRow_DoseDay: selected_record.get('DrugRequestRow_DoseDay'),
						DrugRequestRow_DoseCource: selected_record.get('DrugRequestRow_DoseCource'),
						Okei_oid: selected_record.get('Okei_oid')
					});
				}
			},
			iconCls: 'edit16'
		});*/

		this.setDrugScheme(arguments[0].DrugScheme);

		//в зависимости от DrugScheme, открываем тот или иной метод ввода медикаментов
		if (wnd.DrugScheme == 'rls') {
			wnd.Actions.action_DrugRequestPrint.setHidden(true);
			wnd.EditPanel.hide();
			wnd.EditReservePanel.hide();
			wnd.doLayout();
		}
		if (wnd.DrugScheme == 'dbo') {
			wnd.DrugPacientPanel.ViewActions.action_add.initialConfig.initialDisabled = true;
			wnd.DrugPacientPanel.ViewActions.action_add.setDisabled(true);
			wnd.DrugPacientPanel.ViewActions.action_add.hide();
			wnd.DrugPacientPanel.ViewActions.action_edit.initialConfig.initialDisabled = true;
			wnd.DrugPacientPanel.ViewActions.action_edit.setDisabled(true);
			wnd.DrugPacientPanel.ViewActions.action_edit.hide();

			wnd.DrugReservePanel.ViewActions.action_add.initialConfig.initialDisabled = true;
			wnd.DrugReservePanel.ViewActions.action_add.setDisabled(true);
			wnd.DrugReservePanel.ViewActions.action_add.hide();
			wnd.DrugReservePanel.ViewActions.action_edit.initialConfig.initialDisabled = true;
			wnd.DrugReservePanel.ViewActions.action_edit.setDisabled(true);
			wnd.DrugReservePanel.ViewActions.action_edit.hide();
		}

		wnd.PacientPanel.addActions({
			name:'action_ndre_actions',
			text:lang['deystviya'],
			menu: [{
				name: 'action_request_copy',
				text: lang['kopirovat_predyiduschuyu_zayavku'],
				tooltip: lang['kopirovat_predyiduschuyu_zayavku'],
				handler: function() {
					wnd.generateRequestData(this.name);
				},
				iconCls: 'view16'
			}, {
				name: 'action_create_person_list',
				text: lang['sozdat_spisok_lgotnikov_po_prikrepleniyu'],
				tooltip: lang['sozdat_spisok_lgotnikov_po_prikrepleniyu'],
				handler: function() {
					wnd.generateRequestData(this.name);
				},
				iconCls: 'view16'
			}, {
				name: 'action_drug_copy',
				text: lang['kopirovat_medikamentyi_iz_predyiduschey_zayavki'],
				tooltip: lang['kopirovat_medikamentyi_iz_predyiduschey_zayavki'],
				handler: function() {
					wnd.generateRequestData(this.name);
				},
				iconCls: 'view16'
			}],
			iconCls: 'actions16'
		});

		// Обнуление данных
		form.clearValues();
		
		// Установить первую закладку
		this.PersonTab.setActiveTab(0);
		if ((this.Lpu_id != getGlobalOptions().lpu_id) && getGlobalOptions().isMinZdrav) {
			if (this.DrugRequest_id > 0) {
				this.action = 'view';
				form.findById('ndreDrugRequestMedPersonalEditPanel').setVisible(true);
			} else {
				Ext.Msg.alert(lang['oshibka'], lang['dobavlenie_zayavki_ne_dostupno']);
				this.hide();
				return false;
			}
		}
		else 
		{
			form.findById('ndreDrugRequestMedPersonalEditPanel').setVisible(!getGlobalOptions().isMinZdrav);
			form.findById('ndreIsDrugPanel').setVisible(getGlobalOptions().isMinZdrav);
			form.findById('ndrerIsDrugPanel').setVisible(getGlobalOptions().isMinZdrav);
		}
		// Очистим все
		form.DRType_id = -1;
		form.findById('ndreDrugRequestParamsPanel').getForm().reset();
		form.DrugReservePanel.removeAll(true);
		form.PacientPanel.removeAll(true);
		form.DrugPacientPanel.removeAll(true);

		form.DrugReservePanel.load_complete = false;
		// На просмотр
		if (this.action!='add')
		{
			form.DrugRequestLoad();
			loadMask.hide();
			form.PersonTab.unhideTabStripItem('tab_reserve');
		}
		else 
		{
			form.setEnabled();
			form.findById('ndreDrugRequestPeriod_id').setValue(this.DrugRequestPeriod_id);
			form.findById('ndrePersonRegisterType_id').setValue(this.PersonRegisterType_id);
			form.findById('ndreMedPersonal_id').setValue(this.MedPersonal_id);
			form.findById('ndreLpuSection_id').setValue(this.LpuSection_id);
			form.findById('ndreDrugRequestStatus_id').setValue(1);
			form.findById('ndreDrugRequestPeriod_id').focus(true, 50);
			if (!getGlobalOptions().isMinZdrav)
			{
				form.loadSprData();
			}
            form.DrugRequestPersonLoad();
			loadMask.hide();
		}

		wnd.updatePersonInformationPanel(wnd.action == 'add' ? 'hide' : 'show');

		base_form.findField('RegionDrugRequest_id').getStore().load({
			callback: function(records, options, success) {
				var combo = base_form.findField('RegionDrugRequest_id');
				var id = null;

				if (wnd.PersonRegisterType_id > 0 && wnd.DrugRequestPeriod_id > 0) {
					combo.getStore().each(function(record){
						if (record.get('PersonRegisterType_id') == wnd.PersonRegisterType_id && record.get('DrugRequestPeriod_id') == wnd.DrugRequestPeriod_id) {
							id = record.get('DrugRequest_id');
							return false;
						}
					})
				}

				if (id > 0) {
					combo.setValue(id);
					combo.disable();
				} else {
					combo.enable();
				}
			}
		});

		/*
		if (!this.Tree.loader.baseParams.type)
		{
			this.Tree.loader.baseParams.type = 0;
			this.option_type = 0;
		}
		this.Tree.getRootNode().expand();
		//this.Tree.getRootNode().collapse();
		
		// Выбираем первую ноду и эмулируем клик 
		var node = this.Tree.getRootNode();
		if (node)
		{
			node.select();
			this.Tree.fireEvent('click', node);
		}
		//this.Tree.loader.load(this.Tree.root);
		*/
		//this.personSearchWindow = getWnd('swDrugRequestPersonFindForm');
		//this.drugSearchWindow = getWnd('swDrugRequestMedikamentSearchWindow');
	},
	/**
	* DrugRequestPersonLoad - функция для обновления списка пациентов, в зависимости от выбранных параментов ввода - фильтров.
	*
	*/
	DrugRequestPersonLoad: function (set_focus)  
	{
		var form = this;
		var PersonRegisterType_id = form.findById('ndrePersonRegisterType_id').getValue();
		var DrugRequestPeriod_id = form.DrugRequestPeriod_id || this.findById('ndreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = form.MedPersonal_id || this.findById('ndreMedPersonal_id').getValue();
		var DrugRequest_id = form.DrugRequest_id || this.findById('ndreDrugRequest_id').getValue();
		
		var findFamily = this.fieldFamily.getValue();
		
		var Lpu_id = form.Lpu_id || getGlobalOptions().lpu_id;
		if ((DrugRequestPeriod_id>0) && (MedPersonal_id>0))
		{
			// Сохранение формы 
			if ((!DrugRequest_id) || (DrugRequest_id==0))
				{
					// Попытка сохранить, а если данные уже присутствуют, то перечитать
					form.DrugRequestSave();
				}
			var no_set_focus = false; // !set_focus;
			form.PacientPanel.loadData({
				globalFilters: {
					start:0,
					limit: 50,
					Lpu_id: Lpu_id,
					MedPersonal_id: MedPersonal_id,
					PersonRegisterType_id: PersonRegisterType_id,
					DrugRequestPeriod_id: DrugRequestPeriod_id,
					Person_SurName: findFamily
				},
				noFocusOnLoad:no_set_focus
			});
		}
		else 
		{
			if ((DrugRequestPeriod_id>0) && (getGlobalOptions().isMinZdrav) && (Lpu_id == getGlobalOptions().lpu_id)) {
				form.PacientPanel.loadData({
					globalFilters: {
						start:0,
						limit: 50,
						Lpu_id:'',
						MedPersonal_id: '',
						PersonRegisterType_id: PersonRegisterType_id,
						DrugRequestPeriod_id: DrugRequestPeriod_id,
						Person_SurName: findFamily
					}
				});
			} else
				if (form.PacientPanel.getCount()>0) {
					form.PacientPanel.loadData({
						globalFilters: {
							start:0,
							limit: 50,
							Lpu_id:'',
							MedPersonal_id: '',
							PersonRegisterType_id: PersonRegisterType_id,
							DrugRequestPeriod_id: '',
							Person_SurName: findFamily
						},
						noFocusOnLoad:true
					});
				}
		}
	},
	DrugRequestPersonAdd: function (data)
	{
		// Здесь запись человека и обновление грида...
		// или запись человека только при сохранении? 
		var form = this;
		var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestEditForm'), { msg: LOAD_WAIT });
		loadMask.show();
		var PersonRegisterType_id = this.findById('ndrePersonRegisterType_id').getValue();
		var DrugRequestPeriod_id = this.findById('ndreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = this.findById('ndreMedPersonal_id').getValue();
		var Lpu_id = this.findById('ndreLpu_id').getValue();
		if ((!DrugRequestPeriod_id) || ((!MedPersonal_id) && (!getGlobalOptions().isMinZdrav)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_zapolnenyi_neobhodimyie_polya_proverte_zapolnenie_poley_period_i_vrach']);
			loadMask.hide();
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequestPerson',
			params: 
			{	
				DrugRequestPerson_id: '',
				Person_id: data.person_data.Person_id,
				PersonRegisterType_id: PersonRegisterType_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				MedPersonal_id: MedPersonal_id,
				Lpu_id: Lpu_id
			},
			callback: function(opt, success, resp) 
			{
				loadMask.hide();
				form.PacientPanel.loadData({
					globalFilters: {
						start:0,
						limit: 50,
						MedPersonal_id: MedPersonal_id,
						PersonRegisterType_id: PersonRegisterType_id,
						DrugRequestPeriod_id: DrugRequestPeriod_id
					}
				});
				// Обработка добавления 
			}
		});
	},
	DrugReserveLoad: function (set_focus)
	{
		var win = this;
		var DrugRequestPeriod_id = win.DrugRequestPeriod_id || this.findById('ndreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = win.MedPersonal_id || this.findById('ndreMedPersonal_id').getValue();
		var DrugRequest_id = win.DrugRequest_id || this.findById('ndreDrugRequest_id').getValue();

		if ((DrugRequestPeriod_id>0) && ((MedPersonal_id>0) || getGlobalOptions().isMinZdrav))
		{
			// Сохранение формы 
			if ((!DrugRequest_id) || (DrugRequest_id==0))
			{
				// Попытка сохранить, а если данные уже присутствуют, то перечитать
				win.DrugRequestSave();
			}
			win.DrugReservePanel.loadData({
				globalFilters: {DrugRequest_id: DrugRequest_id, MedPersonal_id: MedPersonal_id, DrugRequestPeriod_id: DrugRequestPeriod_id},
				noFocusOnLoad:!set_focus
			});
		}
		else 
		{
			win.DrugReservePanel.loadData({
				globalFilters: {DrugRequest_id: '', MedPersonal_id: '', DrugRequestPeriod_id: ''},
				noFocusOnLoad:true
			});
		}

		win.DrugReservePanel.load_complete = true;
	},
	DrugRequestLoad: function ()
	{
		// Чтение заявки
		form = this;
		var PersonRegisterType_id = form.PersonRegisterType_id || this.findById('ndrePersonRegisterType_id').getValue();
		var DrugRequestPeriod_id = form.DrugRequestPeriod_id || this.findById('ndreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = form.MedPersonal_id || this.findById('ndreMedPersonal_id').getValue();
		var DrugRequest_id = form.DrugRequest_id || this.findById('ndreDrugRequest_id').getValue();
		var Lpu_id = form.Lpu_id || this.findById('ndreLpu_id').getValue();
		
		
		var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestEditForm'), { msg: lang['sohranenie_zayavki'] });

		form.findById('ndreDrugRequestParamsPanel').getForm().load(
		{
			url: '/?c=DrugRequest&m=index&method=getDrugRequest',
			params:
			{
				Lpu_id: Lpu_id,
				DrugRequest_id: DrugRequest_id,
				MedPersonal_id: MedPersonal_id,
				PersonRegisterType_id: PersonRegisterType_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id
			},
			success: function (frm, action)
			{
				loadMask.hide();
				var result = Ext.util.JSON.decode(action.response.responseText);
				if (result && result[0] && result[0].DrugRequestStatus_Code != 1) {
					form.action='view';
				}
				form.setEnabled();
				if ((!getGlobalOptions().isMinZdrav) || ((this.Lpu_id != getGlobalOptions().lpu_id) && getGlobalOptions().isMinZdrav))
				{
					form.loadSprData();
				}
				form.DrugRequestPersonLoad(true);

				var result = Ext.util.JSON.decode(action.response.responseText);
				if (result[0]) {
					form.updatePersonInformationPanel('set_data', result[0]);
				}
				//form.findById('ndreDrugRequestPeriod_id').focus(true);
			},
			failure: function ()
			{
				loadMask.hide();
				form.returnFunc(form.owner, -1);
				Ext.Msg.alert(lang['oshibka'], lang['ne_udalos_poluchit_informatsiyu_o_zayavke']);
			}
		});
	},
	PersonDrugProtoMnnLoad: function (DrugRequestType_id, DrugProtoMnn_id, DrugProtoMnn_Name, query, focusOnKolvo)
	{
        var win = this;
		win.findById('ndreDrugProtoMnn_id').clearValue();
		win.findById('ndreDrugProtoMnn_id').getStore().removeAll();
		win.findById('ndreDrugProtoMnn_id').lastQuery = '';
		win.findById('ndreDrugProtoMnn_id').getStore().baseParams.ReceptFinance_id = DrugRequestType_id;
		if (DrugProtoMnn_Name.length==0)
			win.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = DrugProtoMnn_id;
		else 
			win.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
		win.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
		// Для торговых наименований
		win.findById('ndreDrugProtoMnn_id').getStore().baseParams.IsDrug = win.findById('ndreIsDrug').pressed?1:0;
		win.findById('ndreDrugProtoMnn_id').getStore().baseParams.MedPersonal_id = win.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
		win.findById('ndreDrugProtoMnn_id').getStore().baseParams.query = query;
		win.findById('ndreDrugProtoMnn_id').getStore().load({
			callback: function()
			{
				if (DrugProtoMnn_id!='')
					win.findById('ndreDrugProtoMnn_id').setValue(DrugProtoMnn_id);
				if (DrugProtoMnn_Name!='')
					win.findById('ndreDrugProtoMnn_id').setRawValue(DrugProtoMnn_Name);
				if (focusOnKolvo)
					win.findById('ndreDrugRequestRow_Kolvo').focus(true);
			}
		});
	},
	DrugRequestSave: function ()
	{
		// Запись заявки 
		var form = this;
		//var mode = arguments[0] && arguments[0].mode ? arguments[0].mode : null;
		var callback = arguments[0] && arguments[0].callback && typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext.emptyFn;
		var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestEditForm'), { msg: lang['sohranenie_zayavki'] });
		loadMask.show();
		var DrugRequest_id = this.findById('ndreDrugRequest_id').getValue();
		var DrugRequestStatus_id = this.findById('ndreDrugRequestStatus_id').getValue();
		var DrugRequestPeriod_id = this.findById('ndreDrugRequestPeriod_id').getValue();
		var MedPersonal_id = this.findById('ndreMedPersonal_id').getValue();
		var LpuSection_id = this.findById('ndreLpuSection_id').getValue();
		var DrugRequest_YCC = this.findById('ndreDrugRequest_YoungChildCount').getValue();
		var PersonRegisterType_id = this.findById('ndrePersonRegisterType_id').getValue();
		if (!getGlobalOptions().isMinZdrav)
			var DrugRequest_Name = lang['zayavka_vracha'];
		else 
			var DrugRequest_Name = lang['zayavka_ministerstva_zdravoohraneniya'];
		if ((!DrugRequestPeriod_id) || ((!MedPersonal_id) && (!getGlobalOptions().isMinZdrav)) || (!PersonRegisterType_id))
		{
			//Ext.Msg.alert('Ошибка', 'Не заполнены обязательные поля.<br/>Проверьте заполнение полей "Период", "Врач" и "Тип заявки".');
			Ext.Msg.alert(lang['oshibka'], lang['ne_zapolnenyi_obyazatelnyie_polya_proverte_zapolnenie_poley_vrach_i_zayavka']);
			loadMask.hide();
			return false;
		}
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequest',
			params: 
			{	
				DrugRequest_id: DrugRequest_id,
				DrugRequestStatus_id: DrugRequestStatus_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				MedPersonal_id: MedPersonal_id,
				LpuSection_id: LpuSection_id,
				DrugRequest_Name: DrugRequest_Name,
				DrugRequest_YoungChildCount: DrugRequest_YCC,
				PersonRegisterType_id: PersonRegisterType_id
			},
			callback: function(options, success, response) 
			{
				loadMask.hide();
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.DrugRequest_id)
					{
						form.findById('ndreDrugRequest_id').setValue(result.DrugRequest_id);
						if (form.findById('ndreDrugRequestStatus_id').getValue()!=result.DrugRequestStatus_id)
							form.findById('ndreDrugRequestStatus_id').setValue(result.DrugRequestStatus_id);
						if (form.findById('ndreDrugRequestTotalStatus_IsClose').getValue()!=result.DrugRequestTotalStatus_IsClose)
							form.findById('ndreDrugRequestTotalStatus_IsClose').setValue(result.DrugRequestTotalStatus_IsClose);
						form.findById('ndreLpu_id').setValue(getGlobalOptions().lpu_id);
						form.action = 'edit';
						if ((form.findById('ndreDrugRequestTotalStatus_IsClose').getValue()==2) || (form.findById('ndreDrugRequestStatus_id').getValue()!=1)) {
							form.action='view';
						}
						form.setEnabled();
                        form.findById('ndreDrugProtoMnn_id').getStore().baseParams.MedPersonal_id = form.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
                        form.findById('ndrerDrugProtoMnn_id').getStore().baseParams.MedPersonal_id = form.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
						callback();

						//Автоматическая смена статусов для заявок верхних уровней
						Ext.Ajax.request({
							params:{
								DrugRequest_id: result.DrugRequest_id
							},
							success: function (response) {},
							url:'/?c=MzDrugRequest&m=setAutoDrugRequestStatus'
						});
					}
				} else {
					form.hide();
				}
			}
		});
	},
	onSetStatus: function() {
		var status_combo = form.findById('ndreDrugRequestStatus_id');
		var status = status_combo.getValue();
		var request_id = form.findById('ndreDrugRequest_id').getValue();

		if (status == 1 && request_id > 0) { //Возвращение заявки врача на редактирование
			Ext.Ajax.request({
				params:{
					DrugRequest_id: request_id,
					event: 'mp_request_return_edit'
				},
				success: function (response) {
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && result[0].Message_id > 0) {
						getWnd('swMessagesViewWindow').show({
							mode: 'openMessage',
							message_data: result[0]
						});
					}
				},
				url:'/?c=MzDrugRequest&m=getNotice'
			});
		}

		//Автоматическая смена статусов для заявок верхних уровней
		Ext.Ajax.request({
			params:{
				DrugRequest_id: request_id
			},
			success: function (response) {},
			url:'/?c=MzDrugRequest&m=setAutoDrugRequestStatus'
		});

		var idx = status_combo.getStore().findBy(function(rec) { return rec.get('DrugRequestStatus_id') == status; });
		if (idx >= 0) {
			this.updatePersonInformationPanel('set_data', {DrugRequestStatus_Name: status_combo.getStore().getAt(idx).get('DrugRequestStatus_Name')});
		} else {
			this.updatePersonInformationPanel('set_data', {DrugRequestStatus_Name: lang['neizvvesten']});
		}


		this.owner.refreshRecords(null, 0);
	},
	checkPersonMedikamentKolvo: function()
	{
		var form = this;
		var DrugRequestRow_Kolvo = form.findById('ndreDrugRequestRow_Kolvo').getValue();
		if (DrugRequestRow_Kolvo>10)
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.YESNO,
				scope : form,
				fn: function(buttonId) 
				{
					if ( buttonId == 'yes' )
					{
						form.findById('ndreDrugRequestRow_Kolvo').focus();
					}
					else
					{
						form.checkPersonMedikamentAdd();
					}
				},
				icon: Ext.Msg.QUESTION,
				msg: lang['vyi_ukazali_medikament_v_kolichestve_bolee_10_izmenit_kolichestvo_zayavlyaemogo_preparata'],
				title: lang['vnimanie']
			});
		}
		else 
		{
			form.checkPersonMedikamentAdd();
		}
	},
	checkPersonMedikamentAdd: function()
	{
		var form = this;
		var Person_id = form.findById('ndrePerson_id').getValue();
		var DrugRequestPeriod_id = form.findById('ndreDrugRequestPeriod_id').getValue();
		var DrugProtoMnn_id = form.findById('ndreDrugProtoMnn_id').getValue();
		var DrugRequestRow_Kolvo = form.findById('ndreDrugRequestRow_Kolvo').getValue();
		var DrugRequestRow_id = form.findById('ndreDrugRequestRow_id').getValue();
		if (Person_id && DrugRequestPeriod_id && DrugProtoMnn_id)
		{
			Ext.Ajax.request(
			{
				url: '/?c=DrugRequest&m=checkUniAllLpuDrugRequestRow',
				params: 
				{	
					Person_id: Person_id,
					DrugRequestRow_id: DrugRequestRow_id,
					DrugRequestPeriod_id: DrugRequestPeriod_id,
					DrugProtoMnn_id: DrugProtoMnn_id
				},
				callback: function(options, success, response) 
				{
					if (success)
					{
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.count>0)
						{
							sw.swMsg.show(
							{
								buttons: Ext.Msg.YESNO,
								scope : form,
								fn: function(buttonId) 
								{
									if ( buttonId == 'yes' )
									{
										form.DrugRequestRowSave();
									}
								},
								icon: Ext.Msg.QUESTION,
								msg: lang['dannyiy_preparat_uje_byil_vklyuchen_v_personifitsirovannuyu_zayavku_dannogo_lgotopoluchatelya_vyi_hotite_dobavit_vyibrannyiy_medikament'],
								title: lang['vopros']
							});
						}
						else 
						{
							form.DrugRequestRowSave();
						}
					}
					else 
					{
						form.DrugRequestRowSave();
					}
				}
			});
		}
	},
	DrugRequestRowSave: function(isReserve) 
	{
		if (isReserve)
		{
			var prefix='ndrer';
			var prefixPanel='Reserve';
		}
		else 
		{
			var prefix='ndre';
			var prefixPanel='';
		}
		var win = Ext.getCmp('NewDrugRequestEditForm');
		
		// Добавление в грид - проверки 
		if (!win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getValue())
		//|| ((!win['Edit'+prefixPanel+'Panel'].findById(prefix+'Drug_id').getValue()) && (getGlobalOptions().isMinZdrav)))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_medikament']);
			win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').focus();
			return false;
		}
		if (!win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_Kolvo').getValue())
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_ukazano_kolichestvo']);
			win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_Kolvo').focus();
			return false;
		}
		if (!win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').getValue())
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_ukazan_tip']);
			win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').focus();
			return false;
		}
		var DrugRequest_id = win.findById('ndreDrugRequest_id').getValue();
		if (!isReserve)
			var Person_id = win.findById('ndrePerson_id').getValue();
		else 
			var Person_id = null;
		var DrugProtoMnn_id = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getValue();
		var DrugRequestRow_Kolvo = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_Kolvo').getValue();
		var DrugRequestRow_id = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_id').getValue();
		var DrugRequestType_id = win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').getValue();
		var DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
		// Если режим редактирования, тогда берем данные из грида
		var IsDrug = 0;
		if (win['Edit'+prefixPanel+'Panel'].findById(prefix+'IsDrugEdit').getValue()>=0)
			IsDrug = win['Edit'+prefixPanel+'Panel'].findById(prefix+'IsDrugEdit').getValue();
		else 
			IsDrug = win['Edit'+prefixPanel+'Panel'].findById(prefix+'IsDrug').pressed?1:0; //win.findById('ndreDrugProtoMnn_id').getStore().baseParams.IsDrug
		// Если заявка новая, то она должна сохраниться на моменте ввода людей
		if (DrugRequest_id==0)
		{
			// Тут заявку надо сохранить и дождаться сохранения  - а именно ID заявки 
			// Здесь поправить еще позже  
			win.DrugRequestSave();
			return false;
		}
		if ((!isReserve) && (!Person_id))
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_chelovek_vyiberite_cheloveka_na_kotorogo_neobhodimo_vvesti_medikament']);
			return false;
		}
		var MedPersonal_id = win.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
		var loadMask = new Ext.LoadMask(Ext.get('NewDrugRequestEditForm'), { msg: LOAD_WAIT });
		loadMask.show();
		
		Ext.Ajax.request(
		{
			url: '/?c=DrugRequest&m=index&method=saveDrugRequestRow',
			params: 
			{	
				DrugRequestRow_id: DrugRequestRow_id || '',
				IsDrug: IsDrug, // Что сохраняем: торговое или МНН
				DrugRequest_id: DrugRequest_id,
				Person_id: Person_id,
				DrugProtoMnn_id: DrugProtoMnn_id,
				DrugRequestRow_Kolvo: DrugRequestRow_Kolvo,
				DrugRequestType_id: DrugRequestType_id,
				DrugRequestPeriod_id: DrugRequestPeriod_id,
				MedPersonal_id: MedPersonal_id
			},
			callback: function(opt, success, resp) 
			{
				loadMask.hide();
				var PersonRegisterType_id = win.findById('ndrePersonRegisterType_id').getValue();
				var DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
				var MedPersonal_id = win.findById('ndreMedPersonal_id').getValue();
				if (!isReserve)
				{
					win.DrugPacientPanel.loadData(
					{
						globalFilters:{Person_id:Person_id, DrugRequest_id: DrugRequest_id, PersonRegisterType_id: PersonRegisterType_id, DrugRequestPeriod_id: DrugRequestPeriod_id}, noFocusOnLoad:true
					});
				}
				else 
				{
					win.DrugReservePanel.loadData({
						globalFilters:{DrugRequestPeriod_id: DrugRequestPeriod_id, MedPersonal_id: MedPersonal_id},
						noFocusOnLoad:true
					});
				}
				
				if (win.findById(prefix+'ButtonAdd').getText() == lang['izmenit'])
				{
					win.findById(prefix+'ButtonAdd').enable();
					win.findById(prefix+'ButtonAdd').setText(lang['dobavit']);
					win.findById(prefix+'IsDrug').enable();
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestRow_id').setValue('');
					win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').enable();
					
					if (isReserve)
					{
						//win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').enable();
						win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugRequestType_id').enable();
						win.findById(prefix+'ButtonEndEdit').setVisible(true);
						win['DrugReservePanel'].getGrid().getSelectionModel().clearSelections();
					}
					else 
					{
						win.findById(prefix+'ButtonEndEdit').setVisible(false);
						win['DrugPacientPanel'].getGrid().getSelectionModel().clearSelections();
					}
				}
				// Очистить поля для ввода и переставить фокус
				/*
				win.EditPanel.findById('ndreDrugProtoMnn_id').setValue('');
				win.EditPanel.findById('ndreDrugRequestRow_Kolvo').setValue('');
				win.EditPanel.findById('ndreDrugRequestType_id').setValue('');
				*/
				win['Edit'+prefixPanel+'Panel'].findById(prefix+'DrugProtoMnn_id').focus();
			}
		});
	},
	insertPersonBottomBar: function()
	{
		if (!this.fieldSetFamily)
		{
			this.fieldFamily = new Ext.form.TextField({
				allowBlank: true,
				enableKeyEvents: true,
				fieldLabel: '&nbsp;Фамилия',
				tooltip: lang['dlya_togo_chtobyi_otfiltrovat_dannyie_po_familii_vvedite_familiyu_ili_chast_i_najmite_[enter]'],
				name: 'fieldFindFamily',
				width: 180,
				listeners: {
					'keydown': function (inp, e) 
					{
						if (e.getKey() == Ext.EventObject.ENTER)
						{
                            this.DrugRequestPersonLoad(true);
						}
					}.createDelegate(this),
					'change': function(f,nv,ov)
					{
						this.PacientPanel.setParam('Person_SurName', nv, true);
					}.createDelegate(this)
				}
			});
			this.fieldSetFamily = new Ext.form.FieldSet(
			{
				border: false,
				autoHeight: true,
				style: 'padding:0px;margin:0px;',
				labelWidth: 50,
				items: [this.fieldFamily]
			});
			this.PacientPanel.getGrid().getBottomToolbar().addSeparator();
			this.PacientPanel.getGrid().getBottomToolbar().add(this.fieldSetFamily);
		}
	},
	generateRequestData: function(action) {
		var wnd = this;

		wnd.DrugRequestSave({callback: function(){ //предварительное сохранение заявки
			wnd.grdSetOptions(action, function(params) { //получение от пользователя входящих данных
				wnd.grdCheckExistsData(action, function(data_exists) { //проверка текущей заявки на наличие данных
					wnd.grdConfirm(data_exists, function() { //получение подтверждения пользователя на изменение данных
						wnd.grdExecute(action, params, function() { //изменение данных
							wnd.grdCallback(action); //отображение изменения данных на форме
						});
					});
				});
			});
		}});
	},
	grdSetOptions: function(action, callback) {
		var DrugRequest_id = this.findById('ndreDrugRequest_id').getValue();
		var params = {
			DrugRequest_id: DrugRequest_id
		};
		if (action != 'action_create_person_list') {
			getWnd('swMzDrugRequestCopyOptionsWindow').show({
				DrugRequest_id: DrugRequest_id,
				onSelect: function(prm) {
					if (prm.DrugRequest_id > 0) {
						params.SourceDrugRequest_id = prm.DrugRequest_id;
						callback(params);
					}
				}
			});
		} else {
			callback(params);
		}
	},
	grdCheckExistsData: function(action, callback) {
		var DrugRequest_id = this.findById('ndreDrugRequest_id').getValue();
		var pacient_store = this.PacientPanel.getGrid().getStore();

		if (DrugRequest_id <= 0) {
			return false;
		}

		if (pacient_store.getCount() > 0 && pacient_store.getAt(0).get('Person_id') > 0) { //если грид с пациентами не пуст значит данные есть
			callback(true);
		} else if (action == 'action_create_person_list') { //для создания списка льготников достаточным критерием отсутствия данных является пустой список пациентов
			callback(false);
		} else {
			Ext.Ajax.request({
				url: '/?c=MzDrugRequest&m=getDrugRequestRowCount',
				params: {
					DrugRequest_id: DrugRequest_id
				},
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						callback(result.cnt > 0);
					}
				}
			});
		}
	},
	grdConfirm: function(data_exists, callback) {
		if (data_exists) {
			sw.swMsg.show({
				icon: Ext.MessageBox.QUESTION,
				msg: lang['tekuschaya_zayavka_vracha_soderjit_dannyie_kotoryie_mogut_byit_izmenenyi_prodoljit_operatsiyu_kopirovaniya_dannyih'],
				title: lang['vopros'],
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ('yes' == buttonId) {
						callback();
					}
				}
			});
		} else {
			callback();
		}
	},
	grdExecute: function(action, params, callback) {
		var method = null;

		switch(action) {
			case 'action_request_copy':
				method = 'createDrugRequestCopy';
				break;
			case 'action_create_person_list':
				method = 'createDrugRequestPersonList';
				break;
			case 'action_drug_copy':
				method = 'createDrugRequestDrugCopy';
				break;
		}

		if (method) {
			Ext.Ajax.request({
				url: '/?c=MzDrugRequest&m='+method,
				params: params,
				callback: function(options, success, response) {
					if (success) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], result.Error_Msg);
						} else {
							callback();
						}
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_obrabotke_dannyih_proizoshla_oshibka']);
						return false;
					}
				}
			});
		}
	},
	grdCallback: function(action) {
		switch(action) {
			case 'action_request_copy':
				sw.swMsg.alert(lang['kopirovanie_zaversheno'], lang['kopirovanie_medikamentov_zaversheno_vnimanie_neobhodimo_otredaktirovat_kolichestvo_medikamentov_v_tekuschey_zayavke']);

				break;
			case 'action_create_person_list':
				sw.swMsg.alert(lang['formirovanie_zaversheno'], lang['spisok_lgotnikov_sformirovan_v_nego_vklyuchenyi_lgotniki_prikreplennyie_k_uchastku_vracha_i_obraschavshiesya_za_retseptami_v_poslednie_90_dney']);

				break;
			case 'action_drug_copy':
				sw.swMsg.alert(lang['kopirovanie_zaversheno'], lang['kopirovanie_medikamentov_zaversheno_vnimanie_neobhodimo_otredaktirovat_kolichestvo_medikamentov_v_tekuschey_zayavke']);
				break;
		}
		this.DrugRequestPersonLoad(true);
		//фиксируем необходимость перезагрузки грида с резервом
		this.DrugReservePanel.load_complete = false;

	},
	initComponent: function()
	{
		var form = this;

		this.InformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 0px',
			border: false,
			region: 'south',
			autoHeight: true,
			frame: true,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: this,
			setTpl: function(tpl) {
				this.html_tpl = tpl;
			},
			setData: function(name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},
			showData: function() {
				var html = this.html_tpl;
				if (this.data)
					this.data.each(function(item) {
						html = html.replace('{'+item.name+'}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function() {
				this.data = null;
			}
		});

		this.PersonInformationPanel = new Ext.Panel({
			bodyStyle: 'padding: 0px',
			border: false,
			columnWidth: .8,
			autoHeight: true,
			frame: false,
			labelAlign: 'right',
			title: null,
			collapsible: true,
			data: null,
			html_tpl: null,
			win: this,
			bodyStyle: 'background:#DFE8F6;',
			setTpl: function(tpl) {
				this.html_tpl = tpl;
			},
			setData: function(name, value) {
				if (!this.data)
					this.data = new Ext.util.MixedCollection();
				if (name && value) {
					var idx = this.data.findIndex('name', name);
					if (idx >= 0) {
						this.data.itemAt(idx).value = value;
					} else {
						this.data.add({
							name: name,
							value: value
						});
					}
				}
			},
			showData: function() {
				var html = this.html_tpl;
				if (this.data)
					this.data.each(function(item) {
						html = html.replace('{'+item.name+'}', item.value, 'gi');
					});
				html = html.replace(/{[a-zA-Z_0-9]+}/g, '');
				this.body.update(html);
				if (this.win) {
					this.win.syncSize();
					this.win.doLayout();
				}
			},
			clearData: function() {
				this.data = null;
			}
		});

		var tpl = "";

		tpl += "<table style='margin: 5px; float: left;'>";
		tpl += "<tr><td>Стоимость ЛС в резерве - {reserv_summ}</td></tr>";
		tpl += "<tr><td>"+(getRegionNick()=="perm"?"Норматив":"Лимит")+" (фед.) – {fed_limit_str}</td></tr>";
		tpl += "<tr><td>"+(getRegionNick()=="perm"?"Норматив":"Лимит")+" (рег.) – {reg_limit_str}</td></tr>";
		tpl += "<tr style=\"display:{overflow_display};\"><td>Превышение – {overflow}</td></tr>";
		tpl += "</table>";

		this.InformationPanel.setTpl(tpl);

		tpl  = "<table style='margin: 5px; float: left;'>";
		tpl += "<tr><td>Врач {medpersonal_fio}{work_place}</td></tr>";
		tpl += "<tr><td>Заявка на {drugrequest_period}{person_register_type}{drugrequest_kind}. Статус – {drugrequest_status}.</td></tr>";
		tpl += "<tr><td>Кол-во детей до 3-х лет: {child_count}</td></tr>";
		tpl += "</table>";

		this.PersonInformationPanel.setTpl(tpl);

		form.DrugRecord = Ext.data.Record.create(
		[
			{name: 'DrugRequestRow_id', mapping: 'DrugRequestRow_id'},
			{name: 'DrugRequest_id', mapping: 'DrugRequest_id', type: 'int'},
			{name: 'Person_id', mapping: 'Person_id', type: 'int'},
			{name: 'DrugProtoMnn_id', mapping: 'DrugProtoMnn_id', type: 'int'},
			{name: 'DrugRequestRow_Name', mapping: 'DrugRequestRow_Name', type: 'string'},
			{name: 'DrugRequestRow_Code', mapping: 'DrugRequestRow_Code', type: 'int'},
			{name: 'DrugRequestRow_Kolvo', mapping: 'DrugRequestRow_Kolvo', type: 'int'},
			{name: 'DrugRequestRow_Price', mapping: 'DrugRequestRow_Price', type: 'float'},
			{name: 'DrugRequestRow_Summa', mapping: 'DrugRequestRow_Summa', type: 'float'},
			{name: 'DrugRequestType_Name', mapping: 'DrugRequestType_Name', type: 'string'},
			{name: 'MedPersonal_FIO', mapping: 'MedPersonal_FIO', type: 'string'},
			{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
			{name: 'DrugRequestRow_insDT', mapping: 'DrugRequestRow_insDT', dateFormat: 'd.m.Y'},
			{name: 'DrugRequestRow_updDT', mapping: 'DrugRequestRow_updDT', dateFormat: 'd.m.Y'},
			{name: 'DrugRequestRow_delDT', mapping: 'DrugRequestRow_delDT', dateFormat: 'd.m.Y'}
		]);
		// События формы 
		this.Actions = new Array();
		this.Actions =
		{
			action_DrugAdd: new Ext.Action(
			{
				tooltip: lang['dobavlenie_redaktirovanie_medikamenta'],
				id: 'ndreButtonAdd',
				text: lang['dobavit'],
				icon: 'img/icons/add16.png', 
				iconCls : 'x-btn-text',
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('NewDrugRequestEditForm');
					win.checkPersonMedikamentKolvo();
				}
			}),
			action_DrugReserveAdd: new Ext.Action(
			{
				tooltip: lang['dobavlenie_redaktirovanie_medikamenta'],
				id: 'ndrerButtonAdd',
				text: lang['dobavit'],
				icon: 'img/icons/add16.png', 
				iconCls : 'x-btn-text',
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('NewDrugRequestEditForm');
					win.DrugRequestRowSave(true);
				}
			}),
			action_DrugEditEndEdit: new Ext.Action(
			{
				tooltip: lang['prodoljit_vvod_medikamentov'],
				id: 'ndreButtonEndEdit',
				text: lang['prodoljit_vvod'],
				iconCls : 'ok16',
				hidden: true,
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('NewDrugRequestEditForm');
					win.findById('ndreButtonEndEdit').setVisible(false);
					win.findById('ndreButtonAdd').enable();
					win.findById('ndreButtonAdd').setText(lang['dobavit']);
					win['EditPanel'].findById('ndreIsDrugEdit').setValue(-1);
					win['EditPanel'].findById('ndreIsDrug').enable();
					win['EditPanel'].findById('ndreDrugRequestRow_id').setValue('');
					win['EditPanel'].findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
					win['EditPanel'].findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
					
					win['EditPanel'].findById('ndreDrugProtoMnn_id').setValue('');
					win['EditPanel'].findById('ndreDrugRequestRow_Kolvo').setValue('');
					win['EditPanel'].findById('ndreDrugProtoMnn_id').enable();
					win['EditPanel'].findById('ndreDrugRequestRow_Kolvo').enable('');
					// Unselect grid
					win.DrugPacientPanel.getGrid().getSelectionModel().clearSelections();
					if (win.DRType_id !=0) 
					{
						if (win.findById('ndreDrugRequestType_id').getValue()!=win.DRType_id)
						{
							// Перегружаем справочник медикаментов
							win.PersonDrugProtoMnnLoad(win.DRType_id, '', '', '', false);
						}
						win['EditPanel'].findById('ndreDrugRequestType_id').setValue(win.DRType_id);
						win['EditPanel'].findById('ndreDrugRequestType_id').disable();
						win['EditPanel'].findById('ndreDrugProtoMnn_id').focus();
						
					}
					else 
					{
						win['EditPanel'].findById('ndreDrugRequestType_id').setValue('');
						win['EditPanel'].findById('ndreDrugRequestType_id').enable();
						win['EditPanel'].findById('ndreDrugRequestType_id').focus();
					}
				}
			}),
			action_DrugReserveEndEdit: new Ext.Action(
			{
				tooltip: lang['prodoljit_vvod_medikamentov'],
				id: 'ndrerButtonEndEdit',
				text: lang['prodoljit_vvod'],
				iconCls : 'ok16',
				hidden: true,
				disabled: false, 
				handler: function() 
				{
					var win = Ext.getCmp('NewDrugRequestEditForm');
					win.findById('ndrerButtonEndEdit').setVisible(false);
					win.findById('ndrerButtonAdd').enable();
					win.findById('ndrerButtonAdd').setText(lang['dobavit']);
					win['EditReservePanel'].findById('ndrerIsDrugEdit').setValue(-1);
					win['EditReservePanel'].findById('ndrerIsDrug').enable();
					win['EditReservePanel'].findById('ndrerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
					win['EditReservePanel'].findById('ndrerDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
					win['EditReservePanel'].findById('ndrerDrugRequestRow_id').setValue('');
					
					win['EditReservePanel'].findById('ndrerDrugProtoMnn_id').setValue('');
					win['EditReservePanel'].findById('ndrerDrugRequestRow_Kolvo').setValue('');
					win['EditReservePanel'].findById('ndrerDrugRequestType_id').setValue('');
					
					win['EditReservePanel'].findById('ndrerDrugProtoMnn_id').enable();
					win['EditReservePanel'].findById('ndrerDrugRequestRow_Kolvo').enable();
					win['EditReservePanel'].findById('ndrerDrugRequestType_id').enable();
					win['EditReservePanel'].findById('ndrerDrugRequestType_id').focus();
				}
			}),
			action_PersonAdd: new Ext.Action(
			{
				tooltip: lang['dobavlenie_patsienta'],
				text: lang['dobavit_patsienta'],
				icon: '', 
				iconCls : 'x-btn-text',
				disabled: true, 
				handler: function() 
				{
					var win = Ext.getCmp('NewDrugRequestEditForm');
					var params = new Object();

					if (win.findById('ndreDrugRequest_id').getValue()==0)
						win.DrugRequestSave();

					var DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
					var searchMode = 'attachrecipients';
					if (getGlobalOptions().lpu_sysnick == 'osindint') {
						searchMode = 'withlgotonly';
					}
					if (win.DrugScheme == 'rls') {
						var PersonRegisterType_id = win.findById('ndrePersonRegisterType_id').getValue();
						if (PersonRegisterType_id > 0 && PersonRegisterType_id != 1) { // 1 - Общетерапевтическая группа
							params.PersonRegisterType_id = PersonRegisterType_id;
						}
						searchMode = '';
					}

					params.onSelect = function(person_data) {
						win.DrugRequestPersonAdd({person_data: person_data});
					};
					params.searchMode = searchMode;
					params.DrugRequestPeriod_id = DrugRequestPeriod_id;
					params.PersonRefuse_IsRefuse = 1; //отказ от льготы: нет

					getWnd('swPersonSearchWindow').show(params);
				}
			}),
			action_DrugRequestPrint: new Ext.Action(
			{
				tooltip: lang['pechat_zayavki'],
				id: 'ndreDrugRequestPrint',
				text: lang['pechat_zayavki'],
				iconCls: 'print16',
				minWidth: 120,
				disabled: true,
				handler: function() {
					if ( getWnd('swDrugRequestPrintWindow').isVisible() ) {
						sw.swMsg.alert(lang['oshibka'], lang['okno_pechati_zayavki_uje_otkryito']);
						return false;
					}

					var win = Ext.getCmp('NewDrugRequestEditForm');

					getWnd('swDrugRequestPrintWindow').show({
						DrugRequestPeriod_id: win.findById('ndreDrugRequestPeriod_id').getValue(),
						LpuSection_id: win.findById('ndreLpuSection_id').getValue(),
						LpuUnit_id: win.findById('ndreLpuUnit_id').getValue(),
						MedPersonal_id: win.findById('ndreMedPersonal_id').getValue()
					});
				}
			}),
			action_DrugRequestSetStatus: new Ext.Action(
			{
				tooltip: lang['sformirovat_zayavku'],
				id: 'ndreDrugRequestSetStatus',
				text: lang['sformirovat'],
				iconCls: 'actions16',
				minWidth: 120,
				enableToggle: true,
				disabled: true, 
				handler: function() 
				{
					var win = Ext.getCmp('NewDrugRequestEditForm');
					var status = win.findById('ndreDrugRequestStatus_id').getValue();
					if (status == 2)
					{
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: lang['izmenit_status_zayavki_na_nachalnaya'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								if ('yes' == buttonId)
								{
									win.findById('ndreDrugRequestStatus_id').setValue(1);
									win.DrugRequestSave({callback: function(){
										win.onSetStatus();
									}});
								}
								else 
								{
									win.findById('ndreDrugRequestSetStatus').toggle();
								}
							}
						});
					}
					else 
					if (status == 1)
					{
						sw.swMsg.show(
						{
							icon: Ext.MessageBox.QUESTION,
							msg: lang['zayavka_so_statusom_sformirovannaya_dostupna_tolko_dlya_prosmotra_izmenit_status_zayavki_na_sformirovannaya'],
							title: lang['vopros'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj)
							{
								if ('yes' == buttonId)
								{
									win.findById('ndreDrugRequestStatus_id').setValue(2);
									win.DrugRequestSave({callback: function(){
										win.onSetStatus();
									}});
								}
								else 
								{
									win.findById('ndreDrugRequestSetStatus').toggle();
								}
							}
						});
					}
				}
			})
		};
		/*
		this.GroupActions = new Array();
		
		// Группа акшенов уровней
		this.GroupActions['actions'] = new Ext.Action(
		{
			text:lang['deystviya'], 
			menu: [
				form.Actions.action_New_EvnPL, 
				form.Actions.action_PersonAdd
			]
		});
		this.GroupActions['settings'] = new Ext.Action(
		{
			text:lang['nastroyki'], 
			menu: 
			{
				items: 
				[{
					text: lang['vyivodit_sobyitiya_po_date'],
					checked: true,
					group: 'group',
					handler: function ()
					{
						form.Tree.loader.baseParams.type = 0;
						form.option_type = 0;
						form.Tree.getRootNode().select()
						form.Tree.loader.load(form.Tree.root);
						form.Tree.getRootNode().expand();
					},
					checkHandler: function () 
					{
					}
				}, 
				{
					text: lang['gruppirovat_sobyitiya_po_tipam'],
					checked: false,
					group: 'group',
					handler: function ()
					{
						form.Tree.loader.baseParams.type = 1;
						form.option_type = 1;
						form.Tree.getRootNode().select()
						form.Tree.loader.load(form.Tree.root);
						form.Tree.getRootNode().expand();
					},
					checkHandler: function () 
					{
						
					}
				}]
			}
		});
		*/
		/*
		this.TreeToolbar = new Ext.Toolbar(
		{
			id : form.id+'Toolbar',
			items:
			[
				form.GroupActions.actions,
				{
					xtype : "tbseparator"
				},
				form.GroupActions.settings
			]
		});
		
		// Формируем меню по правой кнопке 
		this.ContextMenu = new Ext.menu.Menu();
		for (key in this.Actions)
		{
			this.ContextMenu.add(this.Actions[key]);
		}
		*/
		// Кнопка Печать 
		var btnDrugRequestPrint = new Ext.Button(this.Actions.action_DrugRequestPrint);
		var btnDrugRequestSetStatus = new Ext.Button(this.Actions.action_DrugRequestSetStatus);
		/*btnDrugRequestSetStatus.on('toggle', 
			function(btn, pressed)
			{
				if (pressed)
				{
					btn.setText(lang['redaktirovat']);
				}
				else
				{
					btn.setText(lang['sformirovat']);
				}
			});
		*/
		this.ParamsPanel = new Ext.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			//autoHeight: true,
			height: 76,
			region: 'north',
			//labelAlign: 'top',
			labelWidth: 110,
			layout: 'column',
			//title: 'Параметры',
			id: 'ndreDrugRequestParamsPanel',
			items: 
			[{
				// Левая часть параметров ввода
				layout: 'form',
				border: false,
				id: 'ndreDrugRequestMedPersonalEditPanelLeft',
				bodyStyle:'background:#DFE8F6;padding-right:5px;',
				columnWidth: .4,
				labelWidth: 100,
				items: 
				[{
					id: 'ndreDrugRequest_id',
					name: 'DrugRequest_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					id: 'ndreLpu_id',
					name: 'Lpu_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					id: 'ndreDrugRequestTotalStatus_IsClose',
					name: 'DrugRequestTotalStatus_IsClose',
					value: null,
					xtype: 'hidden'
				},
				{
					id: 'ndreRegionDrugRequest_id',
					fieldLabel: lang['zayavka'],
					hiddenName: 'RegionDrugRequest_id',
					xtype: 'swbaselocalcombo',
					valueField: 'DrugRequest_id',
					displayField: 'DrugRequest_Name',
					allowBlank: false,
					editable: false,
					lastQuery: '',
					validateOnBlur: true,
					anchor: '100%',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = form.findById('ndreDrugRequestParamsPanel').getForm();

							var person_register_id = null;
							var priod_id = null;

							var person_register_combo = base_form.findField('PersonRegisterType_id');
							var period_combo = base_form.findField('DrugRequestPeriod_id');

							var idx = combo.getStore().findBy(function(rec) { return rec.get('DrugRequest_id') == newValue; });
							if (idx > -1) {
								var record = combo.getStore().getAt(idx);
								person_register_id = record.get('PersonRegisterType_id');
								priod_id = record.get('DrugRequestPeriod_id');
							}

							if (person_register_id > 0) {
								person_register_combo.setValue(person_register_id);
							} else {
								person_register_combo.setValue(null);
							}

							if (priod_id > 0) {
								period_combo.setValue(priod_id);
							} else {
								period_combo.setValue(null);
							}
						}
					},
					store: new Ext.data.Store({
						autoLoad: false,
						reader: new Ext.data.JsonReader({
							id: 'DrugRequest_id'
						}, [
							{name: 'DrugRequest_id', mapping: 'DrugRequest_id'},
							{name: 'DrugRequest_Name', mapping: 'DrugRequest_Name'},
							{name: 'PersonRegisterType_id', mapping: 'PersonRegisterType_id'},
							{name: 'DrugRequestPeriod_id', mapping: 'DrugRequestPeriod_id'}
						]),
						url: '/?c=MzDrugRequest&m=loadRegionDrugRequestCombo'
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<table style="border: 0;"><tr><td>{DrugRequest_Name}</td></tr></table>',
						'</div></tpl>'
					)
				},
				{
					layout: 'form',
					hidden: true,
					items: [{
						allowBlank: false,
						fieldLabel: lang['tip_zayavki'],
						comboSubject: 'PersonRegisterType',
						name: 'PersonRegisterType_id',
						id: 'ndrePersonRegisterType_id',
						anchor: '100%',
						tabIndex: 4111,
						xtype: 'swcommonsprcombo',
						moreFields: [{name: 'PersonRegisterType_SysNick', mapping: 'PersonRegisterType_SysNick'}]
					}]
				},
				{
					layout: 'form',
					hidden: true,
					items: [{
						allowBlank: false,
						disabled: false,
						id: 'ndreDrugRequestPeriod_id',
						xtype: 'swdynamicdrugrequestperiodcombo',
						tabIndex:4111,
						listeners: {
							change: function(combo) {
								this.ownerCt.ownerCt.ownerCt.DrugRequestPersonLoad();
							}
						}
					}]
				},
				{
					allowBlank: false,
					disabled: true,
					id: 'ndreDrugRequestStatus_id',
					xtype: 'swdrugrequeststatuscombo',
					tabIndex:4112
				}]
			},
			{
				// Средняя часть параметров ввода
				layout: 'form',
				border: false,
				id: 'ndreDrugRequestMedPersonalEditPanel',
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .4,
				labelWidth: 110,
				items:
				[{
					xtype:'panel',
					layout: 'form',
					border: false,
					id: 'ndreLpuUnitPanel',
					bodyStyle:'background:#DFE8F6;padding-right:0px;',
					labelWidth: 110,
					items: 
					[{
						anchor: '100%',
						name: 'LpuUnit_id',
						tabIndex: 4113,
						disabled: false,
						xtype: 'swlpuunitcombo',
						topLevel: true,
						allowBlank:false, 
						id: 'ndreLpuUnit_id',
						listeners:
						{
							change:
								function(combo)
								{
									var tut = form;
									if (combo.getValue() > 0)
									{
										tut.findById('ndreLpuSection_id').getStore().load(
										{
											params:
											{
												Object: 'LpuSection',
												LpuUnit_id: combo.getValue()
											},
											callback: function()
											{
												tut.findById('ndreLpuSection_id').setValue('');
												tut.findById('ndreMedPersonal_id').setValue('');
												tut.DrugRequestPersonLoad();
											}
										});
									}
									else 
									{
										tut.findById('ndreLpuSection_id').setValue('');
										tut.findById('ndreMedPersonal_id').setValue('');
										tut.DrugRequestPersonLoad();
									}
								}
						}
					}]
				},
				{
					xtype: 'swlpusectioncombo',
					anchor: '100%',
					tabIndex:3,
					name: 'LpuSection_id',
					id: 'ndreLpuSection_id',
					allowBlank: false,
					/*width: 290,
					listWidth: 500,*/
					tabIndex:4114,
					listeners:
					{
						change:
							function(combo)
							{
								var tut = this.ownerCt.ownerCt.ownerCt; 
								if (combo.getValue() > 0)
								{
									tut.findById('ndreMedPersonal_id').getStore().load(
									{
										params:
										{
											LpuSection_id: combo.getValue(),
											IsDlo: (!getGlobalOptions().isOnko && !getGlobalOptions().isRA && tut.DrugScheme == 'dbo')?1:0
										},
										callback: function()
										{
											tut.findById('ndreMedPersonal_id').setValue('');
											//tut.DrugRequestPersonLoad();
										}
									});
									
									if (!tut.findById('ndreLpuUnit_id').getValue())
									{
										idx = combo.getStore().indexOfId(combo.getValue());
										if (idx<0)
											idx = combo.getStore().findBy(function(rec) { return rec.get('LpuSection_id') == combo.getValue(); });
										if (idx<0)
											return;
										var row = combo.getStore().getAt(idx);
										tut.findById('ndreLpuUnit_id').setValue(row.data.LpuUnit_id); 
									}
								}
								else 
								{
									tut.findById('ndreMedPersonal_id').setValue('');
									tut.DrugRequestPersonLoad();
								}
							}
					}
				},
				{
					xtype: 'swmedpersonalcombo',
					anchor: '100%',
					name: 'MedPersonal_id',
					id: 'ndreMedPersonal_id',
					loadingText: lang['idet_poisk'],
					minChars: 1,
					minLength: 1,
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					tabIndex:4115,
					listeners:
					{
						blur:
							function(combo)
							{
								var tut = this.ownerCt.ownerCt.ownerCt; 
								tut.DrugRequestPersonLoad();
							}
					}
				},
				{
					xtype:'panel',
					layout: 'form',
					border: false,
					id: 'ndreYoungChildCountPanel',
					bodyStyle:'background:#DFE8F6;padding-right:0px;',
					labelWidth: 180,
					items: 
					[{
						xtype: 'numberfield',
						maxValue: 500,
						minValue: 0,
						autoCreate: {tag: "input", size:5, maxLength: "3", autocomplete: "off"},
						fieldLabel: lang['kolichestvo_detey_do_3-h_let'],
						//anchor: '100%',
						name: 'DrugRequest_YoungChildCount',
						id: 'ndreDrugRequest_YoungChildCount',
						tabIndex:4116
					}]
				}]
			},
			form.PersonInformationPanel,
			{
				// Правая часть параметров ввода
				layout: 'form',
				border: false,
				bodyStyle:'background:#DFE8F6;padding-left:5px;',
				columnWidth: .2,
				labelWidth: 110,
				items: [btnDrugRequestPrint, btnDrugRequestSetStatus]
				
			}],
			reader: new Ext.data.JsonReader(
			{
				success: function() 
				{ 
				//
				}
			},
			[
				{ name: 'DrugRequest_id' },
				{ name: 'DrugRequestKind_id' },
				{ name: 'PersonRegisterType_id' },
				{ name: 'DrugRequestPeriod_id' },
				{ name: 'DrugRequestStatus_id' },
				{ name: 'Lpu_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'DrugRequestTotalStatus_IsClose' },
				{ name: 'DrugRequest_YoungChildCount'}
			])
		});
		
		// Пациенты
		this.PacientPanel = new sw.Promed.ViewFrame(
		{
			//title:'Пациенты',
			//id: 'DrugRequestPacientPanel',
			region: 'center',
			height: 303,
			minSize: 200,
			maxSize: 400,
			object: 'DrugRequestPerson',
			paging: true,
			pageSize: 50,
			root: 'data',
			totalProperty: 'totalCount',
			//editformclassname: 'swLpuSectionShiftEditForm',
			dataUrl: '/?c=DrugRequest&m=index&method=getPersonGrid',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DrugRequestPerson_id', type: 'int', header: 'ID', key: true},
				{name: 'Person_id', hidden: true, isparams: true},
				{name: 'Server_id', hidden: true, isparams: true},
				{name: 'PersonEvn_id', hidden: true, hideable: false},
				{name: 'DrugRequestRow_Count', hidden: true},
				{name: 'Person_SurName', width: 100, header: lang['familiya']},
				{name: 'Person_FirName', width: 100, header: lang['imya']},
				{name: 'Person_SecName', width: 100, header: lang['otchestvo']},
				//{name: 'Person_FIO', id: 'autoexpand', header: 'ФИО'},
				{name: 'Person_BirthDay', type: 'date', header: lang['data_rojdeniya'], width: 90},
				{name: 'Lpu_Nick', header: lang['lpu_prikrepleniya'], width: 150},
				{name: 'LpuRegion_Name', header: lang['uchastok'], width: 80},
				{name: 'Person_IsBDZ', type:'checkbox', header: lang['bdz'], width: 35},
				{name: 'Person_IsFedLgot', type:'checkbox', header: lang['fed_lg'], width: 50},
				
				{name: 'Person_IsFedLgotCurr', type:'checkbox', header: lang['fed_zayavka'], width: 70},
				
				{name: 'Person_IsRefuse', type:'checkbox', header: lang['otkaz'], width: 50},
				{name: 'Person_IsRefuseNext', type:'checkbox', header: lang['otk_na_sl_god'], width: 80},
				{name: 'Person_IsRefuseCurr', type:'checkbox', header: lang['otk_zayavka'], width: 70},
				{name: 'Person_IsRegLgot', type:'checkbox', header: lang['reg_lg'], width: 50},
				
				{name: 'Person_IsRegLgotCurr', type:'checkbox', header: lang['reg_zayavka'], width: 70},
				
				{name: 'Person_Is7Noz', type:'checkbox', header: lang['7_noz'], width: 50},
				{name: 'Person_IsDead', type:'checkbox', header: lang['umer'], width: 50},
				{name: 'DrugRequestPerson_insDT', type:'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestPerson_updDT', type:'date', header: lang['izmenen'], width: 70},
				{name: 'set', type:'int', hidden: true}
			],
			actions:
			[
				{name:'action_add', handler: function() { Ext.getCmp('NewDrugRequestEditForm').Actions.action_PersonAdd.execute();}},
				{name:'action_edit', disabled: true},
				{name:'action_view', disabled: true},
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequestPerson'}
			],
			onLoadData: function()
			{
				// TODO: Подумать над таким неверным использванием getWnd
				if (getWnd('swPersonSearchWindow') && getWnd('swPersonSearchWindow').isVisible() && getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().getSelected()) {
					var record = getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().getSelected();
					getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getView().focusRow(0);
					getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().selectRow(0);
				}
				// Синенькие пациенты и красненькие
			},
			loadDrugGrid: function (sm,index,record)
			{
				var win = Ext.getCmp('NewDrugRequestEditForm');
				var DrugRequest_id = win.findById('ndreDrugRequest_id').getValue();
				var PersonRegisterType_id = win.findById('ndrePersonRegisterType_id').getValue();
				var DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
				var MedPersonal_id = win.findById('ndreMedPersonal_id').getValue();
				//var DrugRequest_id = win.findById('ndreDrugRequest_id').getValue();
				if (this.getCount()>0)
				{
					win.DrugPacientPanel.loadData(
					{
						globalFilters:
						{
							Person_id:record.data['Person_id'], 
							DrugRequest_id: DrugRequest_id,
							PersonRegisterType_id: PersonRegisterType_id,
							DrugRequestPeriod_id: DrugRequestPeriod_id
							//MedPersonal_id: MedPersonal_id 
						}, 
						noFocusOnLoad:true
					});
				}
				else 
				{
					win.DrugPacientPanel.removeAll();
				}
				// ЗаBOLDим пациента
				win.PacientPanel.ViewGridStore.each(function(record) 
				{
					if (record.get('set')>0)
					{
						record.set('set', 0);
						record.commit();
					}
				});
				record.set('set', 1);
				record.commit();
				// Затычка на тыкание мышкой 
				// Условие убрать после добавления нормального поиска
				// TODO: Вообще ветку if можно убрать (и проверить)
				if (!(getWnd('swPersonSearchWindow') && getWnd('swPersonSearchWindow').isVisible()))
				{
					win.PacientPanel.ViewGridPanel.getView().focusRow(index);
					//С ExtJS 2.3.0 похоже неактуально
					//sm.selectRow(index);
				}
				else 
				{
					// TODO: Подумать над таким неверным использванием getWnd
					if (getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().getSelected())
					{
						var record = getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().getSelected();
						//getWnd('swPersonSearchWindow').findById('PersonSearchGrid').focus();
						getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getView().focusRow(0);
						getWnd('swPersonSearchWindow').findById('PersonSearchGrid').getSelectionModel().selectRow(0);
					}
				}
				/*
				sm.selectRow(index);*/
			},
			onRowSelect: function (sm,index,record)
			{
				var win = Ext.getCmp('NewDrugRequestEditForm');
				/*
				// ЗаBOLDим пациента
				win.PacientPanel.ViewGridStore.each(function(record) 
				{
					if (record.get('set')>0)
					{
						record.set('set', 0);
						record.commit();
					}
				});
				record.set('set', 1);
				record.commit();
				*/
				// ЗаTIMEим загрузку подчиненного грида 
				if (!win.delayRowSelect)
					win.delayRowSelect = new Ext.util.DelayedTask();
				win.delayRowSelect.delay(600, win.PacientPanel.loadDrugGrid, win.PacientPanel, [sm,index,record]);
				win.findById('ndrePerson_id').setValue(record.data['Person_id']);
				if ((((win.action!='view') && (win.findById('ndreDrugRequestStatus_id').getValue()!=2))
				|| (getGlobalOptions().isMinZdrav)) && (win.PacientPanel.getCount()>0))
				{
					win.findById('ndreIsDrugEdit').setValue(-1);
					// Проверяем льготы пациента
					win.findById('ndreDrugProtoMnn_id').enable();
					win.findById('ndreDrugRequestRow_Kolvo').enable();
					win.findById('ndreButtonAdd').enable();
					win.findById('ndreDrugRequestType_id').enable();
					if (win.findById('ndreButtonAdd').getText()==lang['izmenit'])
					{
						win.findById('ndreDrugRequestRow_id').setValue('');
						win.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = '';
						win.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
						win.findById('ndreButtonAdd').setText(lang['dobavit']);
						win.findById('ndreIsDrug').enable();
						win.findById('ndreButtonEndEdit').setVisible(false);
					}
					
					var OtkNextYear = (record.get('Person_IsRefuseCurr')=='true')?true:false;
					// Федеральная льгота 
					if ((record.get('Person_IsFedLgotCurr')=='true') && (record.get('Person_IsRegLgotCurr')!='true') && (record.get('Person_IsRegLgotCurr')!='gray') && (!OtkNextYear))
					{
						if (win.findById('ndreDrugRequestType_id').getValue()!=1)
						{
							// Перегружаем справочник медикаментов без сброса параметров
							win.PersonDrugProtoMnnLoad(1, '', '', win.findById('ndreDrugProtoMnn_id').getRawValue(), false);
						}
						if (getGlobalOptions().isMinZdrav)
						{
							win.findById('ndreDrugRequestType_id').enable();
							win.DRType_id = 0;
						}
						else 
						{
							win.findById('ndreDrugRequestType_id').disable();
							win.DRType_id = 1;
						}
						//form.MessageTip.showAt([100,100]);
						//form.MessageTip.show();
						
						win.findById('ndreDrugRequestType_id').setValue(1);
					}
					else if ((record.get('Person_IsFedLgotCurr')!='true') && ((record.get('Person_IsRegLgotCurr')=='true') || (record.get('Person_IsRegLgotCurr')=='gray')))
					{
						if (OtkNextYear)
						{
							// Если отказник , то никаких медикаментов 
							win.findById('ndreDrugRequestType_id').setValue('');
							win.findById('ndreDrugRequestType_id').disable();
							win.findById('ndreDrugProtoMnn_id').disable();
							win.findById('ndreDrugRequestRow_Kolvo').disable();
							win.findById('ndreButtonAdd').disable();
							win.DRType_id = -1;
						}
						else 
						{
							if (win.findById('ndreDrugRequestType_id').getValue()!=2)
							{
								// Перегружаем справочник медикаментов без сброса параметров
								win.PersonDrugProtoMnnLoad(2, '', '', win.findById('ndreDrugProtoMnn_id').getRawValue(), false);
							}
							win.findById('ndreDrugRequestType_id').disable();
							win.findById('ndreDrugRequestType_id').setValue(2);
							win.DRType_id = 2;
						}
					}
					else if ((record.get('Person_IsFedLgotCurr')=='true') && ((record.get('Person_IsRegLgotCurr')=='true') || (record.get('Person_IsRegLgotCurr')=='gray')))
					{
						if (OtkNextYear)
						{
							// Если отказник , то никаких медикаментов 
							win.findById('ndreDrugRequestType_id').setValue('');
							win.findById('ndreDrugRequestType_id').disable();
							win.findById('ndreDrugProtoMnn_id').disable();
							win.findById('ndreDrugRequestRow_Kolvo').disable();
							win.findById('ndreButtonAdd').disable();
							win.DRType_id = -1;
						}
						else 
						{
							if (!getGlobalOptions().isMinZdrav)
							{
								win.findById('ndreDrugRequestType_id').disable();
								win.findById('ndreDrugRequestType_id').setValue(1);
								win.PersonDrugProtoMnnLoad(1, '', '', win.findById('ndreDrugProtoMnn_id').getRawValue(), false);
								win.DRType_id = 1;
							}
							else 
							{
								win.findById('ndreDrugRequestType_id').enable();
								win.DRType_id = 0;
							}
						}
					}
					else
					{
					
					if ((win.action=='view') || (win.findById('ndreDrugRequestStatus_id').getValue()!=3))
					{
						win.findById('ndreDrugRequestType_id').setValue('');
						win.findById('ndreDrugRequestType_id').disable();
						win.findById('ndreDrugProtoMnn_id').disable();
						win.findById('ndreDrugRequestRow_Kolvo').disable();
						win.findById('ndreButtonAdd').disable();
						win.DRType_id = -1;
					}
					}
				}
			},
			focusOn: {name:'ndreDrugRequestType_id',type:'field'},
			focusPrev: {name:'ndreMedPersonal_id',type:'field'}
		});
		this.PacientPanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('set')>0)
					cls = cls+'x-grid-rowselect ';
				if (row.get('DrugRequestRow_Count')>0)
					cls = cls+'x-grid-rowblue ';
				/*
				if (row.get('Person_IsRefuseCurr')=='true')
					cls = cls+'x-grid-rowgray ';
				*/
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		//this.PacientPanel.ViewGridPanel.getSelectionModel().addListener('rowdeselect', function (sm, index, record) {alert(1);record.set('set', 0);record.commit()});
		
		// Кнопка "Добавить медикамент"
		var btnDrugAdd = new Ext.Button(this.Actions.action_DrugAdd);
		btnDrugAdd.tabIndex = 4120;
		//btnDrugAdd.id = 'ndreButtonAdd';
		var btnDrugEditEndEdit = new Ext.Button(this.Actions.action_DrugEditEndEdit);
		btnDrugEditEndEdit.tabIndex = 4121;
		
		// Панелька ввода
		this.EditPanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			region: 'south',
			height: 30,
			minSize: 30,
			maxSize: 30,
			layout: 'column',
			//title: 'Ввод',
			id: 'DrugRequestEditPanel',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .15,
				labelWidth: 30,
				items: 
				[{
					allowBlank: false,
					fieldLabel: lang['tip'],
					id: 'ndreDrugRequestType_id',
					name: 'DrugRequestType_id',
					xtype: 'swdrugrequesttypecombo',
					tabIndex:4117,
					listeners: 
					{
						'beforeselect': function() 
						{
							//this.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id =''; // :)
						},
						'change': function(combo, newValue, oldValue) 
						{
							var drugcombo = this.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id');
							var win = Ext.getCmp('NewDrugRequestEditForm');
							drugcombo.clearValue();
							drugcombo.getStore().removeAll();
							drugcombo.lastQuery = '';
							drugcombo.getStore().baseParams.ReceptFinance_id = newValue;
							drugcombo.getStore().baseParams.DrugProtoMnn_id = '';
							drugcombo.getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
							drugcombo.getStore().baseParams.MedPersonal_id = win.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
							drugcombo.getStore().baseParams.query = '';
							if (newValue > 0)
							{
								drugcombo.getStore().load();
							}
						}
					}
				}]
			},
			{
				layout: 'form',
				id: 'ndreIsDrugPanel',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .03,
				hidden: true,
				items: 
				[{
					id: 'ndreIsDrugEdit',
					name: 'IsDrugEdit',
					xtype: 'hidden',
					value: -1
				},
				{
					allowBlank: false,
					iconCls: 'checked-gray16',
					widht: 16,
					tooltip: lang['vyibirat_iz_torgovyih_naimenovaniy'],
					id: 'ndreIsDrug',
					name: 'IsDrug',
					xtype: 'button',
					enableToggle: true,
					tabIndex:4117,
					toggleHandler: function (btn, state)
					{
						var cls = (state)?'checked16':'checked-gray16';
						btn.setIconClass(cls);
						btn.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id').getStore().baseParams.IsDrug = state?1:0;
						btn.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id').getStore().removeAll();
						btn.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id').clearValue();
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .4,
				labelWidth: 80,
				items: 
				[{
					id: 'ndreDrugRequestRow_id',
					name: 'DrugRequestRow_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					id: 'ndrePerson_id',
					name: 'Person_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['medikament'],
					id: 'ndreDrugProtoMnn_id',
					name: 'DrugProtoMnn_id',
					xtype: 'swdrugprotomnncombo',
					tabIndex:4118,
					loadingText: lang['idet_poisk'],
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					//plugins: [new Ext.ux.translit(true)],
					queryDelay: 250,
					listeners: 
					{
						/*'beforeselect': function(combo, record, index) 
						{
							combo.setValue(record.get('Drug_id'));
							Ext.getCmp('EREF_Drug_Price').setValue(record.get('Drug_Price'));

							var drug_mnn_combo = Ext.getCmp('EREF_DrugMnnCombo');
							var drug_mnn_record = drug_mnn_combo.getStore().getById(record.get('DrugMnn_id'));
							var org_farmacy_combo = Ext.getCmp('EREF_OrgFarmacyCombo');

							drug_mnn_combo.lastQuery = '';

								if (drug_mnn_record)
								{
									drug_mnn_combo.setValue(record.get('DrugMnn_id'));
								}
								else
								{
									drug_mnn_combo.getStore().load({
										callback: function() {
											drug_mnn_combo.setValue(record.get('DrugMnn_id'));
										},
										params: {
											DrugMnn_id: record.get('DrugMnn_id')
										}
									})
								}

								if ( record.get('Drug_id') > 0 )
								{
									org_farmacy_combo.clearValue();
									org_farmacy_combo.getStore().removeAll();
									org_farmacy_combo.getStore().load({
										params: {
											Drug_id: record.get('Drug_id')
										}
									});
								}
							},*/
						'change': function() 
						{
							//this.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = ''; // :)
						},
						'keydown': function(inp, e) 
						{
							if (e.getKey() == e.DELETE || e.getKey() == e.F4)
							{
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
								{
									e.browserEvent.stopPropagation();
								}
								else
								{
									e.browserEvent.cancelBubble = true;
								}
								if (e.browserEvent.preventDefault)
								{
									e.browserEvent.preventDefault();
								}
								else
								{
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey())
								{
									case e.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('ndreDrugProtoMnn_id').setRawValue(null);
										break;
									case e.F4:
										inp.onTrigger2Click();
										break;
								}
							}
						}
					},
					onTrigger2Click: function() 
					{
						
						if (this.disabled)
							return false;
						var win = Ext.getCmp('NewDrugRequestEditForm');
						var combo = this;
						if (!this.formList)
						{
							this.formList = new sw.Promed.swListSearchWindow(
							{
								title: lang['poisk_medikamenta'],
								id: 'DrugProtoMnnSearch',
								object: 'DrugProtoMnn',
								//editformclassname: 'swEditForm',
								//dataUrl: '/?c=DrugRequest&m=index&method=getPersonGrid',
								//stringfields: 
								//[
								//	{name: 'DrugProtoMnn_id', key: true},
								//	{name: 'DrugProtoMnn_Name', id: 'autoexpand', header: 'Наименование'},
								//	{name: 'Lpu_Nick', hidden: false, header: 'ЛПУ прикрепления', width: 100}
								//],
								store: this.getStore()
							});
						}
						if (!win.EditPanel.findById('ndreDrugRequestType_id').getValue())
						{
							sw.swMsg.alert(lang['oshibka'], lang['nelzya_otkryit_formu_poiska_vyibora_poskolku_ne_ukazan_tip_zayavki'], function() {win.EditPanel.findById('ndreDrugRequestType_id').focus(true, 50);});
							return false;
						}
						this.formList.show(
						{
							onSelect: function(data) 
							{
								win.PersonDrugProtoMnnLoad(data['DrugRequestType_id'], data['DrugProtoMnn_id'], data['DrugProtoMnn_Name'], '', true);
							}, 
							onHide: function() 
							{
								//combo.focus(false);;
							}, 
							IsDrug: win.findById('ndreIsDrug').pressed?1:0, 
							ReceptFinance_id: win.EditPanel.findById('ndreDrugRequestType_id').getValue()
						});
						
						return false;
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .11,
				labelWidth: 50,
				items: 
				[{
					anchor: '100%',
					xtype: 'numberfield',
					name: 'DrugRequestRow_Kolvo',
					id:  'ndreDrugRequestRow_Kolvo',
					maxValue: 9999999,
					minValue: 0,
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					allowBlank: false,
					fieldLabel: lang['kol-vo'],
					tabIndex:4119
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .1,
				items: [btnDrugAdd]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .14,
				items: [btnDrugEditEndEdit]
			}]
		});
		/*
		this.MessageTip = new Ext.ToolTip(
		{
			target: form.EditPanel, 
			title: lang['vnimanie'],  
			html:lang['vyi_mojete_vyipisyivat_federalnomu_lgotniku_medikamentyi_po_regionalnoy_zayavke'], 
			hideDelay:1000, 
			showDelay: 1000, 
			autoHide: true,  
			dismissDelay: 1000
		});
		*/
		// Медикаменты по пациентам
		this.DrugPacientPanel = new sw.Promed.ViewFrame(
		{
			//title:'Пациенты',
			id: this.id+'PacientPanel',
			region: 'center',
			height: 200,
			minSize: 200,
			maxSize: 300,
			object: 'DrugRequestRow',
			editformclassname: '',
			dataUrl: '/?c=DrugRequest&m=index&method=getDrugRequestRow',
			toolbar: true,
			focusOnFirstLoad: true,
			autoLoadData: false,
			/*
			saveAtOnce: false,
			saveAllParams: true,
			*/
			params: {
				onSave: function(window, values) {
					//swalert(values);
					var win = Ext.getCmp('NewDrugRequestEditForm');
					win.checkPersonMedikamentKolvo();
					window.hide();
				}
			},
			stringfields:
			[
				{name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_id', hidden: true, isparams: true},
				{name: 'DrugProtoMnn_id', hidden: true, isparams: true},
				{name: 'DrugComplexMnn_id', hidden: true, isparams: true},
				{name: 'TRADENAMES_id', hidden: true, isparams: true},
				{name: 'Drug_id', hidden: true, isparams: true},
				{name: 'Person_id', hidden: true, isparams: true},
				{name: 'ATX_Code', type: 'string', header: lang['ath'], width: 100},
				{id: 'autoexpand', name: 'DrugRequestRow_Name', header: lang['mnn'], renderer: function(v, p, record) { return record.get('isProblem') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'ClsDrugForms_Name', type: 'string', header: lang['lekarstvennaya_forma'], width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: lang['dozirovka'], width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: lang['fasovka'], width: 100},
				{name: 'NTFR_Name', type: 'string', header: lang['klass_ntfr'], width: 100},
				{name: 'DrugRequestRow_Code', hidden: true, type: 'int', header: lang['kod'], width: 50},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo'], width: 80, isparams: true}, // , editor: new Ext.form.NumberField({allowBlank: false,allowNegative: false, minValue: 1, maxValue: 100000})
				{name: 'DrugRequestRow_Price', type: 'float', header: lang['tsena'], width: 80, type: 'money', align: 'right'},
				{name: 'DrugRequestRow_Summa', type: 'float', header: lang['summa'], width: 80, type: 'money', align: 'right'},
				{name: 'DrugRequestType_id', hidden: true, isparams: true},
				{name: 'DrugRequestType_Name', header: lang['tip'], width: 100},
				{name: 'MedPersonal_FIO', header: lang['vrach'], width: 200},
				{name: 'MedPersonal_id', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'Lpu_Nick', header: lang['lpu'], width: 200},
				{name: 'DrugRequestRow_insDT', type: 'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestRow_updDT', type: 'date', header: lang['izmenen'], width: 70},
				{name: 'DrugRequestRow_delDT', type: 'date', header: lang['udalen'], width: 70},
				{name: 'DrugRequestRow_DoseOnce', header: lang['razovaya_doza'], width: 100, renderer: function(v, p, r){
					return (r.get('DrugRequestRow_DoseOnce') != null) ? r.get('DrugRequestRow_DoseOnce') + ' ' + r.get('Okei_oid_NationSymbol') : '';
				}},
				{name: 'DrugRequestRow_DoseDay', header: lang['dnevnaya_doza'], width: 100, renderer: function(v, p, r){
					return (r.get('DrugRequestRow_DoseDay') != null) ? r.get('DrugRequestRow_DoseDay') + ' ' + r.get('Okei_oid_NationSymbol') : '';
				}},
				{name: 'DrugRequestRow_DoseCource', header: lang['kursovaya_doza'], width: 100, renderer: function(v, p, r){
					return (r.get('DrugRequestRow_DoseCource') != null) ? r.get('DrugRequestRow_DoseCource') + ' ' + r.get('Okei_oid_NationSymbol') : '';
				}},
				{name: 'Okei_oid', hidden: true, isparams: true},
				{name: 'Okei_oid_NationSymbol', hidden: true},
				{name: 'DrugRequestRow_Deleted', hidden: true},
				{name: 'isProblem', hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: false, handler: function(){ this.editDrugPanelRow('pacient', 'add');}.createDelegate(this)},
				{name:'action_edit', disabled: false, handler: function(){ this.editDrugPanelRow('pacient', 'edit');}.createDelegate(this)},
				{name:'action_view', hidden: true},
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequestRow'}/*,
				{name:'action_save', url: '/?c=DrugRequest&m=index&method=saveDrugRequestRow'}*/
			],
			focusPrev: {name:'ndreButtonAdd',type:'button'},
			focusOn: {name:'ndreButtonSave',type:'button'},
			onLoadData: function (result)
			{
				var win = Ext.getCmp('NewDrugRequestEditForm');

				var MainLpu_id = win.findById('ndreLpu_id').getValue();
				var MedPersonal_id = win.findById('ndreMedPersonal_id').getValue();

				if (win.DrugPacientPanel.ViewGridPanel.getSelectionModel().getSelected())
				{
					var record = win.DrugPacientPanel.ViewGridPanel.getSelectionModel().getSelected();
					if ((record.get('Lpu_id')!=MainLpu_id) || (record.get('MedPersonal_id')!=MedPersonal_id))
					{
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
						win.findById('ndreDrugRequestRow_Kolvo').disable();
						win.findById('ndreButtonAdd').disable();
					}
					else
					{
						if (win.action!='view')
						{
							win.findById('ndreDrugRequestRow_Kolvo').enable();
							win.findById('ndreDrugRequestRow_Kolvo').focus(true);
							win.DrugPacientPanel.ViewActions.action_delete.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
						}
					}
				}
				else 
				{
					win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
				}

				// Собираем суммы
				var sumFed = 0;
				var sumReg = 0;
				var sumFedAll = 0;
				var sumRegAll = 0;
				var sumFedLimit = 0;
				var sumRegLimit = 0;
				if (result)
				{
					win.DrugPacientPanel.ViewGridStore.each(function(record) 
					{
						if ((record.get('Lpu_id')==MainLpu_id) && ((record.get('MedPersonal_id')==MedPersonal_id) || ((record.get('MedPersonal_id')=='') && (getGlobalOptions().isMinZdrav))) && (record.get('DrugRequestRow_Deleted')!=2))
						{
							if (record.get('DrugRequestType_id')==1)
								sumFed = sumFed + record.get('DrugRequestRow_Summa')*1;
							if (record.get('DrugRequestType_id')==2)
								sumReg = sumReg + record.get('DrugRequestRow_Summa')*1;
						}
						if (record.get('Lpu_id')!=MainLpu_id)
						{
							if (record.get('DrugRequestType_id')==1)
								sumFedAll = sumFedAll + record.get('DrugRequestRow_Summa')*1;
							if (record.get('DrugRequestType_id')==2)
								sumRegAll = sumRegAll + record.get('DrugRequestRow_Summa')*1;
						}
					});
				}
				sumRegAll = sumRegAll + sumReg*1;
				sumFedAll = sumFedAll + sumFed*1;
				// Установить суммы по пациенту
				// Начало быдлоблока - удалить потом как будет реализована нормально периодичность лимитов
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 12010)
				{
					var normativ_fed_lgot = 400;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 75;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 22010)
				{
					var normativ_fed_lgot = 560;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 125;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 32010)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 190;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 42010)
				{
					var normativ_fed_lgot = 570;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 190;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 12011)
				{
					var normativ_fed_lgot = 600;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 100;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 22011)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 110;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 32011)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 130;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 42011)
				{
					var normativ_fed_lgot = 590;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 130;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 12012)
				{
					var normativ_fed_lgot = 630;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 140;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 22012)
				{
					var normativ_fed_lgot = 630;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 140;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 52012)
				{
					var normativ_fed_lgot = 630;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 140;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 32012)
				{
					var normativ_fed_lgot = 800;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 180;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 42012)
				{
					var normativ_fed_lgot = 800;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 180;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 12013)
				{
					var normativ_fed_lgot = 700;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 220;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 22013)
				{
					var normativ_fed_lgot = 650;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 250;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 32013)
				{
					var normativ_fed_lgot = 650;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 250;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 42013)
				{
					var normativ_fed_lgot = 650;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 250;
					var koef_reg_lgot = 1;
				}
				if (win.findById('ndreDrugRequestPeriod_id').getValue() == 62201)
				{
					var normativ_fed_lgot = 649;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = 210;
					var koef_reg_lgot = 1;
				}
				if (win.MoRequest_Data) {
					var normativ_fed_lgot = win.MoRequest_Data.FedDrugRequestQuota_Person;
					var koef_fed_lgot = 1;
					var normativ_reg_lgot = win.MoRequest_Data.RegDrugRequestQuota_Person;
					var koef_reg_lgot = 1;
				}
				var sumFedLimit = (normativ_fed_lgot ? normativ_fed_lgot : 0)*3*(koef_fed_lgot ? koef_fed_lgot : 0);
				var sumRegLimit = (normativ_reg_lgot ? normativ_reg_lgot : 0)*3*(koef_reg_lgot ? koef_reg_lgot : 0);
				// Конец быдлоблока
				/*
				var sumFedLimit = getGlobalOptions().normativ_fed_lgot*3*getGlobalOptions().koef_fed_lgot;
				var sumRegLimit = getGlobalOptions().normativ_reg_lgot*3*getGlobalOptions().koef_reg_lgot;
				*/
				/*
				if (sumFedK<sumFed)
					var sumFed_string = '<span style="color:red;">'+sw.Promed.Format.rurMoney(sumFed)+' ('+sw.Promed.Format.rurMoney(Math.round((sumFed-sumFedK)*100)/100)+')</span>';
				else
					var sumFed_string = sw.Promed.Format.rurMoney(sumFed);
				
				if (sumRegK<sumReg)
					var sumReg_string = '<span style="color:red;">'+sw.Promed.Format.rurMoney(sumReg)+' ('+sw.Promed.Format.rurMoney(Math.round((sumReg-sumRegK)*100)/100)+')</span>';
				else
					var sumReg_string = sw.Promed.Format.rurMoney(sumReg);
				*/
				sumFed = sw.Promed.Format.rurMoney(sumFed);
				sumFedAll = sw.Promed.Format.rurMoney(sumFedAll);
				sumFedLimit = sw.Promed.Format.rurMoney(sumFedLimit);
				sumReg = sw.Promed.Format.rurMoney(sumReg);
				sumRegAll = sw.Promed.Format.rurMoney(sumRegAll);
				sumRegLimit = sw.Promed.Format.rurMoney(sumRegLimit);
				
				win.SumDPTpl.overwrite(win.SumDPPanel.body, {sumReg:sumReg, sumFed:sumFed, sumFedAll:sumFedAll, sumFedLimit:sumFedLimit, sumRegAll:sumRegAll,sumRegLimit:sumRegLimit});
			},
			onRowSelect: function (sm,index,record)
			{
				this.setActionDisabled('action_edit', this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
				this.setActionDisabled('action_delete', this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));

				var win = Ext.getCmp('NewDrugRequestEditForm');
				if ((((win.action!='view') && (!win.findById('ndreDrugRequestStatus_id').getValue().inlist([2,3])))
				|| (getGlobalOptions().isMinZdrav)) && (win.DrugPacientPanel.getCount()>0))
				{
					win.findById('ndreDrugRequestRow_id').setValue(record.get('DrugRequestRow_id'));
					win.findById('ndreDrugRequestType_id').setValue(record.get('DrugRequestType_id'));
					win.findById('ndreDrugRequestRow_Kolvo').setValue(record.get('DrugRequestRow_Kolvo'));
					// Берем состояние из грида для редактирования 
					if (record.get('DrugProtoMnn_id')=='')
						win.findById('ndreIsDrugEdit').setValue(1);
					else 
						win.findById('ndreIsDrugEdit').setValue(0);
					win.findById('ndreDrugProtoMnn_id').setValue(record.get('DrugProtoMnn_id'));
					win.findById('ndreButtonAdd').setText(lang['izmenit']);
					win.findById('ndreButtonEndEdit').setVisible(true);
					win.findById('ndreDrugProtoMnn_id').clearValue();
					win.findById('ndreDrugProtoMnn_id').getStore().removeAll();
					win.findById('ndreDrugProtoMnn_id').lastQuery = '';
					var med = (record.get('DrugProtoMnn_id')=='')?record.get('Drug_id'):record.get('DrugProtoMnn_id');
					
					win.findById('ndreDrugProtoMnn_id').getStore().loadData([{
					 	'DrugProtoMnn_Name' : record.get('DrugProtoMnn_Name'),
						'DrugProtoMnn_Code' : record.get('DrugProtoMnn_Code'),
						'DrugProtoMnn_id' : med,
						'DrugMnn_id' : null,
						'ReceptFinance_id' : record.get('DrugRequestType_id'),
						'DrugProtoMnn_Price' : record.get('DrugRequestRow_Price')
					}]);
					
					win.findById('ndreDrugProtoMnn_id').setValue(med);
					win.findById('ndreDrugProtoMnn_id').setRawValue(record.get('DrugRequestRow_Name'));
					win.findById('ndreDrugRequestRow_Kolvo').focus(true);
					win.findById('ndreDrugRequestType_id').disable();
					win.findById('ndreDrugProtoMnn_id').disable();
					win.findById('ndreIsDrug').disable();
					if ((record.get('Lpu_id')==win.findById('ndreLpu_id').getValue()) || (getGlobalOptions().isMinZdrav))
					{
						win.findById('ndreDrugRequestRow_Kolvo').enable();
						win.findById('ndreDrugRequestRow_Kolvo').focus(true);
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
						win.findById('ndreButtonAdd').enable();
					}
					else 
					{
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
						win.findById('ndreDrugRequestRow_Kolvo').disable();
						win.findById('ndreButtonAdd').disable();
					}
				}
				else // Если не просмотр, то оставляем возможность удаления записей в случае, если заявка утвержденная 
					if ((win.action!='view') && (win.DrugPacientPanel.getCount()>0) && (win.findById('ndreDrugRequestStatus_id').getValue()==3))
					{
						win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
						if ((record.get('Lpu_id')==win.findById('ndreLpu_id').getValue()) || (getGlobalOptions().isMinZdrav))
						{
							if (record.get('DrugRequestRow_Deleted')!=2)
							{
								win.DrugPacientPanel.ViewActions.action_delete.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
							}
						}
					}
			}
		});
		
		this.DrugPacientPanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('DrugRequestRow_Deleted')>0)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		var sumTplMark = 
		[
			'<div style="height:32px;padding-top:0px;font-weight:bold;">'+
			'<span style="color:#444;">&nbsp;&nbsp;Суммы по выбранному пациенту: Федеральная заявка (свои/общ/'+(getRegionNick()=='perm'?'норматив':'лимит')+'):</span> {sumFed} / {sumFedAll} / {sumFedLimit}<br/>'+
			'<span style="color:#444;">&nbsp;&nbsp;Суммы по выбранному пациенту: Региональная заявка (свои/общ/'+(getRegionNick()=='perm'?'норматив':'лимит')+'):</span> {sumReg} / {sumRegAll} / {sumRegLimit}</div>'
			//'Product Group: {ProductGroup}<br/>'
		];
		this.SumDPTpl = new Ext.Template(sumTplMark);
		this.SumDPPanel = new Ext.Panel(
		{
			id: 'SumDPPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			region: 'south',
			border: true,
			frame: false,
			height: 32,
			maxSize: 32,
			html: ''
		});
		
		// Закладка #2 - Резерв 
		// Кнопка "Добавить медикамент" в резерве 
		var btnDrugReserveAdd = new Ext.Button(this.Actions.action_DrugReserveAdd);
		btnDrugReserveAdd.tabIndex = 4124;
		
		var btnDrugReserveEndEdit = new Ext.Button(this.Actions.action_DrugReserveEndEdit);
		btnDrugReserveEndEdit.tabIndex = 4125;
		
		
		//btnDrugAdd.id = 'ndreButtonAdd';

		// Панелька ввода для ввода резерва   
		this.EditReservePanel = new Ext.form.FormPanel(
		{
			bodyStyle:'width:100%;background:#DFE8F6;padding:4px;',
			border: false,
			collapsible: false,
			height: 30,
			minSize: 30,
			maxSize: 30,
			region: 'north',
			layout: 'column',
			//title: 'Ввод',
			id: 'DrugRequestEditReservePanel',
			items: 
			[{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .15,
				labelWidth: 30,
				items: 
				[{
					allowBlank: false,
					fieldLabel: lang['tip'],
					id: 'ndrerDrugRequestType_id',
					name: 'DrugRequestType_id',
					xtype: 'swdrugrequesttypecombo',
					tabIndex:4117,
					listeners: 
					{
						'beforeselect': function() 
						{
							//this.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id =''; // :)
						},
						'change': function(combo, newValue, oldValue) 
						{
							var drugcombo = this.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id');
							var win = Ext.getCmp('NewDrugRequestEditForm');
							drugcombo.clearValue();
							drugcombo.getStore().removeAll();
							drugcombo.lastQuery = '';
							drugcombo.getStore().baseParams.ReceptFinance_id = newValue;
							drugcombo.getStore().baseParams.DrugProtoMnn_id = '';
							drugcombo.getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
							drugcombo.getStore().baseParams.MedPersonal_id = win.findById('ndreDrugRequestParamsPanel').getForm().findField('MedPersonal_id').getValue();
							drugcombo.getStore().baseParams.query = '';
							if (newValue > 0)
							{
								drugcombo.getStore().load();
							}
						}
					}
				}]
			},
			{
				layout: 'form',
				id: 'ndrerIsDrugPanel',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .03,
				hidden: true,
				items: 
				[{
					id: 'ndrerIsDrugEdit',
					name: 'IsDrugReserveEdit',
					xtype: 'hidden',
					value: -1
				},
				{
					allowBlank: false,
					iconCls: 'checked-gray16',
					widht: 16,
					tooltip: lang['vyibirat_iz_torgovyih_naimenovaniy'],
					id: 'ndrerIsDrug',
					name: 'IsDrugReserve',
					xtype: 'button',
					enableToggle: true,
					tabIndex:4117,
					toggleHandler: function (btn, state)
					{
						var cls = (state)?'checked16':'checked-gray16';
						btn.setIconClass(cls);
						btn.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id').getStore().baseParams.IsDrug = state?1:0;
						btn.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id').getStore().removeAll();
						btn.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id').clearValue();
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .4,
				labelWidth: 80,
				items: 
				[{
					id: 'ndrerDrugRequestRow_id',
					name: 'DrugRequestRow_id',
					value: null,
					xtype: 'hidden'
				}, 
				{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: lang['medikament'],
					id: 'ndrerDrugProtoMnn_id',
					name: 'DrugProtoMnn_id',
					xtype: 'swdrugprotomnncombo',
					tabIndex:4118,
					loadingText: lang['idet_poisk'],
					minLengthText: lang['pole_doljno_byit_zapolneno'],
					queryDelay: 250,
					listeners: 
					{
						'change': function() 
						{
							this.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = ''; // :)
						},
						'keydown': function(inp, e) 
						{
							if (e.getKey() == Ext.EventObject.DELETE || e.getKey() == Ext.EventObject.F4)
							{
								e.stopEvent();
								if (e.browserEvent.stopPropagation)
								{
									e.browserEvent.stopPropagation();
								}
								else
								{
									e.browserEvent.cancelBubble = true;
								}
								if (e.browserEvent.preventDefault)
								{
									e.browserEvent.preventDefault();
								}
								else
								{
									e.browserEvent.returnValue = false;
								}

								e.returnValue = false;

								if (Ext.isIE)
								{
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}
								switch (e.getKey())
								{
									case Ext.EventObject.DELETE:
										inp.clearValue();
										inp.ownerCt.ownerCt.findById('ndrerDrugProtoMnn_id').setRawValue(null);
										break;
									case Ext.EventObject.F4:
										inp.onTrigger2Click();
										break;
								}
							}
						}
					},
					onTrigger2Click: function() 
					{
						return false;
					}
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .11,
				labelWidth: 50,
				items: 
				[{
					anchor: '100%',
					xtype: 'numberfield',
					name: 'DrugRequestRow_Kolvo',
					id:  'ndrerDrugRequestRow_Kolvo',
					maxValue: 9999999,
					minValue: 0,
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					allowBlank: false,
					fieldLabel: lang['kol-vo'],
					tabIndex:4119
				}]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .1,
				items: [btnDrugReserveAdd]
			},
			{
				layout: 'form',
				border: false,
				bodyStyle:'padding: 4px;background:#DFE8F6;',
				columnWidth: .15,
				items: [btnDrugReserveEndEdit]
			}]
		});
		
		// Медикаменты по резерву 
		this.DrugReservePanel = new sw.Promed.ViewFrame(
		{
			id: 'DrugRequestDrugReservePacientPanel',
			region: 'center',
			height: 303,
			minSize: 200,
			maxSize: 400,
			object: 'DrugRequestRow',
			editformclassname: '',
			dataUrl: '/?c=DrugRequest&m=index&method=getDrugRequestRow',
			toolbar: true,
			autoLoadData: false,
			stringfields:
			[
				{name: 'DrugRequestRow_id', type: 'int', header: 'ID', key: true},
				{name: 'DrugRequest_id', hidden: true, isparams: true},
				{name: 'DrugProtoMnn_id', hidden: true, isparams: true},
				{name: 'DrugComplexMnn_id', hidden: true, isparams: true},
				{name: 'TRADENAMES_id', hidden: true, isparams: true},
				{name: 'ATX_Code', type: 'string', header: lang['ath'], width: 100},
				{id: 'autoexpand', name: 'DrugRequestRow_Name', header: lang['mnn'], renderer: function(v, p, record) { return record.get('isProblem') == '1' ? '<div style="color: #ff0000">'+v+'</div>' : v; }},
				{name: 'ClsDrugForms_Name', type: 'string', header: lang['lekarstvennaya_forma'], width: 160},
				{name: 'DrugComplexMnnDose_Name', type: 'string', header: lang['dozirovka'], width: 100},
				{name: 'DrugComplexMnnFas_Name', type: 'string', header: lang['fasovka'], width: 100},
				{name: 'NTFR_Name', type: 'string', header: lang['klass_ntfr'], width: 100},
				{name: 'DrugRequestRow_Code', hidden: true, type: 'int', header: lang['kod'], width: 50},
				{name: 'DrugRequestRow_Kolvo', type: 'float', header: lang['kol-vo'], width: 80},
				{name: 'DrugRequestRow_Price', type: 'float', header: lang['tsena'], width: 80},
				{name: 'DrugRequestRow_Summa', type: 'money', align:'right', header: lang['summa'], width: 80},
				{name: 'DrugRequestType_id', hidden: true, isparams: true},
				{name: 'DrugRequestType_Name', header: lang['tip'], width: 100},
				{name: 'MedPersonal_FIO', header: lang['vrach'], width: 200},
				{name: 'MedPersonal_id', hidden: true},
				{name: 'Lpu_id', hidden: true},
				{name: 'Lpu_Nick', header: lang['lpu'], width: 200},
				{name: 'DrugRequestRow_insDT', type: 'date', header: lang['vnesen'], width: 70},
				{name: 'DrugRequestRow_updDT', type: 'date', header: lang['izmenen'], width: 70},
				{name: 'DrugRequestRow_delDT', type: 'date', header: lang['udalen'], width: 70},
				{name: 'DrugRequestRow_Deleted', hidden: true},
				{name: 'isProblem', hidden: true}
			],
			actions:
			[
				{name:'action_add', disabled: false, handler: function(){ this.editDrugPanelRow('reserve', 'add');}.createDelegate(this)},
				{name:'action_edit', disabled: false, handler: function(){ this.editDrugPanelRow('reserve', 'edit');}.createDelegate(this)},
				{name:'action_view', hidden: true},
				{name:'action_delete', url: '/?c=DrugRequest&m=index&method=deleteDrugRequestRow'},
				{name:'action_refresh'},
				{name:'action_print'}
			],
			focusPrev: {name:'ndrerButtonAdd',type:'button'},
			focusOn: {name:'ndreButtonSave',type:'button'},
			onLoadData: function ()
			{
				var win = Ext.getCmp('NewDrugRequestEditForm');
				if (win.DrugReservePanel.ViewGridPanel.getSelectionModel().getSelected())
				{
					var record = win.DrugReservePanel.ViewGridPanel.getSelectionModel().getSelected();
					if ((record.get('Lpu_id')==win.findById('ndreLpu_id').getValue()) && (record.get('MedPersonal_id')==win.findById('ndreMedPersonal_id').getValue()))
					{
						win.findById('ndrerDrugRequestRow_Kolvo').enable();
						win.findById('ndrerDrugRequestRow_Kolvo').focus(true);
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
					}
					else 
					{
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(true);
						win.findById('ndrerDrugRequestRow_Kolvo').disable();
						win.findById('ndrerButtonAdd').disable();
					}
				}
				else 
				{
					win.DrugPacientPanel.ViewActions.action_delete.setDisabled(true);
				}
				form.doCalculate();
			},
			onRowSelect: function (sm,index,record)
			{
				this.setActionDisabled('action_edit', this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
				this.setActionDisabled('action_delete', this.readOnly || !(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));

				var win = Ext.getCmp('NewDrugRequestEditForm');
				if ((((win.action!='view') && (!win.findById('ndreDrugRequestStatus_id').getValue().inlist([2,3])))
				|| (getGlobalOptions().isMinZdrav)) && (win.DrugReservePanel.getCount()>0))
				{
					win.findById('ndrerDrugRequestRow_id').setValue(record.get('DrugRequestRow_id'));
					win.findById('ndrerDrugRequestType_id').setValue(record.get('DrugRequestType_id'));
					win.findById('ndrerDrugRequestRow_Kolvo').setValue(record.get('DrugRequestRow_Kolvo'));
					//win.findById('drreDrugProtoMnn_id').setValue(record.get('DrugProtoMnn_id'));
					win.findById('ndrerButtonAdd').setText(lang['izmenit']);
					if (record.get('DrugProtoMnn_id')=='')
						win.findById('ndrerIsDrugEdit').setValue(1);
					else 
						win.findById('ndrerIsDrugEdit').setValue(0);

					win.findById('ndrerButtonEndEdit').setVisible(true);
					win.findById('ndrerDrugProtoMnn_id').clearValue();
					win.findById('ndrerDrugProtoMnn_id').getStore().removeAll();
					win.findById('ndrerDrugProtoMnn_id').lastQuery = '';
					win.findById('ndrerDrugProtoMnn_id').getStore().baseParams.ReceptFinance_id = record.get('DrugRequestType_id');
					win.findById('ndrerDrugProtoMnn_id').getStore().baseParams.DrugRequestPeriod_id = win.findById('ndreDrugRequestPeriod_id').getValue();
					win.findById('ndrerDrugProtoMnn_id').getStore().baseParams.DrugProtoMnn_id = record.get('DrugProtoMnn_id');
					win.findById('ndrerDrugProtoMnn_id').getStore().baseParams.query = '';
					var med = (record.get('DrugProtoMnn_id')=='')?record.get('Drug_id'):record.get('DrugProtoMnn_id');
						
						win.findById('ndrerDrugProtoMnn_id').getStore().loadData([{
							'DrugProtoMnn_Name' : record.get('DrugProtoMnn_Name'),
							'DrugProtoMnn_Code' : record.get('DrugProtoMnn_Code'),
							'DrugProtoMnn_id' : med,
							'DrugMnn_id' : null,
							'ReceptFinance_id' : record.get('DrugRequestType_id'),
							'DrugProtoMnn_Price' : record.get('DrugRequestRow_Price')
						}]);
						
						win.findById('ndrerDrugProtoMnn_id').setValue(med);
						win.findById('ndrerDrugProtoMnn_id').setRawValue(record.get('DrugRequestRow_Name'));
					/*
					win.findById('ndrerDrugProtoMnn_id').getStore().load(
					{
						callback: function()
						{
							//win.findById('ndreDrugProtoMnn_id').setValue(win.findById('ndreDrugProtoMnn_id').getValue());
							win.findById('ndrerDrugProtoMnn_id').setValue(record.get('DrugProtoMnn_id'));
							win.findById('ndrerDrugProtoMnn_id').setRawValue(record.get('DrugRequestRow_Name'));
							win.findById('ndrerDrugRequestRow_Kolvo').focus(true);
							//alert(win.findById('ndreDrugProtoMnn_id').getValue());
						}
					});
					*/
					win.findById('ndrerDrugRequestType_id').disable();
					win.findById('ndrerDrugProtoMnn_id').disable();
					win.findById('ndrerIsDrug').disable();
					if ((record.get('Lpu_id')==win.findById('ndreLpu_id').getValue()) && ((record.get('MedPersonal_id')==win.findById('ndreMedPersonal_id').getValue()) || (getGlobalOptions().isMinZdrav)))
					{
						win.findById('ndrerButtonAdd').enable();
						win.findById('ndrerDrugRequestRow_Kolvo').enable();
						win.findById('ndrerDrugRequestRow_Kolvo').focus(true);
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
					}
					else 
					{
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(true);
						win.findById('ndrerDrugRequestRow_Kolvo').disable();
						win.findById('ndrerButtonAdd').disable();
					}
				}
				else // Если не просмотр, то оставляем возможность удаления записей в случае, если заявка утвержденная 
					if ((win.action!='view') && (win.DrugReservePanel.getCount()>0) && (win.findById('ndreDrugRequestStatus_id').getValue()==3))
					{
						win.DrugReservePanel.ViewActions.action_delete.setDisabled(true);
						if ((record.get('Lpu_id')==win.findById('ndreLpu_id').getValue()) && ((record.get('MedPersonal_id')==win.findById('ndreMedPersonal_id').getValue()) || (getGlobalOptions().isMinZdrav)))
						{
							if (record.get('DrugRequestRow_Deleted')!=2)
							{
								win.DrugReservePanel.ViewActions.action_delete.setDisabled(!(isLpuAdmin() || (record.get('MedPersonal_id') && record.get('MedPersonal_id') == getGlobalOptions().medpersonal_id)));
							}
						}
					}
			}
		});
		
		this.DrugReservePanel.ViewGridPanel.view = new Ext.grid.GridView(
		{
			getRowClass : function (row, index)
			{
				var cls = '';
				if (row.get('DrugRequestRow_Deleted')>0)
					cls = cls+'x-grid-rowgray ';
				if (cls.length == 0)
					cls = 'x-grid-panel'; 
				return cls;
			}
		});
		
		this.PersonTab = new Ext.TabPanel(
		{
			resizeTabs:true,
			region: 'center',
			id: 'DrugRequestPersonTab',
			plain: true,
			activeTab:0,
			enableTabScroll:true,
			minTabWidth: 120,
			autoScroll: true,
			defaults: {bodyStyle:'background:#DFE8F6;width:100%;'},
			layoutOnTabChange: true,
			listeners:
			{
                tabchange:function (tab, panel) {
                    var els = '';
                    var type = 0;
                    if (els == '') {
                        els = panel.findByType('textfield', false);
                        type = 1;
                    }
                    if (els == '') {
                        els = panel.findByType('combo', false);
                        type = 1;
                    }
                    if (els == '') {
                        els = panel.findByType('grid', false);
                        type = 2;
                    }
                    if (els == '') {
                        type = 0;
                    }
                    var el;
                    if (type != 0)
                        el = els[0];
                    if (el != 'undefined' && el.focus && type == 1) {
                        el.focus(true, 100);
                    }
                    else if (el != 'undefined' && el.focus && type == 2) {
                        if (el.getStore().getCount() > 0) {
                            el.getView().focusRow(0);
                            el.getSelectionModel().selectFirstRow();
                        }
                    }
                    var win = Ext.getCmp('NewDrugRequestEditForm');
                    // Далее взависимости от таба
                    if (tab.getActiveTab().id == 'tab_person') {

                    }
                    else
                    if (tab.getActiveTab().id == 'tab_reserve') {
                        if (!win.DrugReservePanel.load_complete) {
                            win.DrugReserveLoad(false);
                        }
                    }
                }
			},
			items:
			[{
				title: lang['po_patsientam'],
				layout:'border',
				defaults: {split: true},
				id: 'tab_pacient',
				iconCls: 'info16',
				//header:false,
				border:false,
				items: 
				[
					{
						border: false,
						layout:'border',
						region: 'center',
						items: [form.PacientPanel, form.EditPanel]
					},
					{
						layout:'border',
						border: false,
						height: 230,
						region: 'south',
						//defaults: {split: true},
						items: [form.DrugPacientPanel, form.SumDPPanel]
					}
				]
			},
			{
				title: lang['rezerv'],
				layout:'border',
				id: 'tab_reserve',
				iconCls: 'info16',
				defaults: {split: true},
				border:false,
				items: 
				[
					form.EditReservePanel,
					form.DrugReservePanel,
					form.InformationPanel
				]
			}]
		});

		Ext.apply(this,
		{
			xtype: 'panel',
			region: 'center',
			items:
			[
				form.ParamsPanel,
				form.PersonTab
			]
		});
		sw.Promed.swNewDrugRequestEditForm.superclass.initComponent.apply(this, arguments);
		this.PacientPanel.addListenersFocusOnFields();
		this.DrugPacientPanel.addListenersFocusOnFields();
	}

});
