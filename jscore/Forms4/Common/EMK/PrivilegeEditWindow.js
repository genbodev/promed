/**
 * Форма льгот
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.PrivilegeEditWindow', {
	addCodeRefresh: Ext6.emptyFn,
	closeToolText: 'Закрыть',
	
	alias: 'widget.swPrivilegeEditWindowExt6',
	title: 'Льгота',
	extend: 'base.BaseForm',
	maximized: false,
	width: 548,
	height: 490,
	modal: true,
	
	findWindow: false,
	closable: true,
	cls: 'arm-window-new emk-forms-window privilege-window person-disp-diag-edit-window',
	renderTo: Ext6.getBody(), // main_center_panel.body.dom,
	layout: 'border',
	
	plain: true,
	resizable: false,
	
	doSave: function(ignore_age) {
		var current_window = this;

		var form = this.MainPanel;
		var checking_for_regional_benefits = (ignore_age && ignore_age.checking_for_regional_benefits) ? ignore_age.checking_for_regional_benefits : null;

		if ( !form.getForm().isValid() )
		{
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var privilege_type_code = current_window.queryById('PrivEF_PrivilegeTypeCombo').getFieldValue('PrivilegeType_Code');
		var privilege_type_sysnick = current_window.queryById('PrivEF_PrivilegeTypeCombo').getFieldValue('PrivilegeType_SysNick');
		var privilege_type_name = current_window.queryById('PrivEF_PrivilegeTypeCombo').getFieldValue('PrivilegeType_Name');

		var lgotEndDate = current_window.queryById('PrivEF_Privilege_endDate').getValue();
		var lgotStartDate = current_window.queryById('PrivEF_Privilege_begDate').getValue();
		var birth_date = current_window.Person_Birthday;
		var death_date = current_window.Person_deadDT;

		if (typeof birth_date == 'string') {
			birth_date = Date.parse(birth_date);
		}

		var third_birth_date = birth_date.add('Y', 3).add('D', -1);
		var sixth_birth_date = birth_date.add('Y', 6).add('D', -1);

		if (getRegionNick() != 'kz') {
			if ( Ext6.isEmpty(birth_date) || typeof birth_date != 'object' ) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					icon: Ext6.MessageBox.WARNING,
					msg: langs('У пациента не указана дата рождения'),
					title: langs('Ошибка')
				});
				return false;
			}

			// проверка даты рождения < даты начала льготы
			if ( birth_date > lgotStartDate ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Дата начала льготы не может быть раньше даты рождения.'), function() {
					current_window.queryById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}

			// проверка даты начала льготы < даты окончания льготы
			if ( !Ext6.isEmpty(lgotEndDate) && lgotStartDate >= lgotEndDate ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Дата окончания льготы не может быть раньше даты начала.'), function() {
					current_window.queryById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}

			if (
				!isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']) &&
				!privilege_type_sysnick.inlist(['child_und_three_year','deti_6_mnogod','infarkt']) &&
				!Ext6.isEmpty(lgotEndDate) && Ext6.isEmpty(death_date) && lgotEndDate < new Date()
			) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						current_window.queryById('PrivEF_Privilege_endDate').focus();
					},
					icon: Ext6.Msg.WARNING,
					msg: langs('Дата закрытия льготы не может быть меньше текущей даты'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (privilege_type_sysnick == 'child_und_three_year' && (Ext6.isEmpty(lgotEndDate) || !lgotEndDate.equals(third_birth_date))) {
				var privName = getRegionNick() == 'perm'?'253':'«Дети первых 3 лет»';
				Ext6.Msg.show({
					buttons: Ext6.Msg.YESNO,
					fn: function(buttonId) {
						if ('yes' == buttonId) {
							current_window.queryById('PrivEF_Privilege_endDate').setValue(third_birth_date);
						}
					},
					icon: Ext6.Msg.WARNING,
					msg: langs('Для добавления льготы '+privName+' необходимо указать дату окончания. Установить дату наступления трехлетнего возраста?'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (privilege_type_sysnick == 'deti_6_mnogod' && (Ext6.isEmpty(lgotEndDate) || !lgotEndDate.equals(sixth_birth_date))) {
				var privName = getRegionNick() == 'perm'? '258' : '«Дети из многодетных семей в возрасте до 6 лет»';
				Ext6.Msg.show({
					buttons: Ext6.Msg.YESNO,
					fn: function(buttonId) {
						if ('yes' == buttonId) {
							current_window.queryById('PrivEF_Privilege_endDate').setValue(sixth_birth_date);
						}
					},
					icon: Ext6.Msg.WARNING,
					msg: langs('Для добавления льготы '+privName+' необходимо указать дату окончания. Установить дату наступления шестилетнего возраста?'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			if (privilege_type_sysnick.inlist(['infarkt','infarkt_miok'])) {
				var infarktEndDate = infarktEndDate = new Date()
					.add(Date.MONTH, 6)
					.add(Date.DAY, 7*(getRegionNick()=='khak'?3:0))
					.add(Date.DAY, -1)
					.clearTime();

				if (Ext6.isEmpty(lgotEndDate) || !lgotEndDate.equals(infarktEndDate)) {
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId) {
							if ('yes' == buttonId) {
								current_window.queryById('PrivEF_Privilege_endDate').setValue(infarktEndDate);
							}
						},
						icon: Ext6.Msg.WARNING,
						msg: langs('Для льготной категории «Инфаркт миокарда (первые шесть месяцев)» должна быть указана дата окончания. Установить дату окончания  льготы  '+infarktEndDate.format('d.m.Y')+'?'),
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		} else {
			
			var age = swGetPersonAgeDay(birth_date, new Date());
			var privMinAgeDay = current_window.queryById('PrivEF_SubCategoryPrivTypeCombo').getFieldValue('SubCategoryPrivType_minAgeDay');
			var privMaxAgeDay = current_window.queryById('PrivEF_SubCategoryPrivTypeCombo').getFieldValue('SubCategoryPrivType_maxAgeDay');
			
			var minDate = birth_date;
			if (!!privMinAgeDay) {
				minDate.add(Date.DAY, privMinAgeDay);
			}
			
			// проверка даты рождения/минимальной даты < даты начала льготы
			if ( minDate > lgotStartDate ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Дата начала льготы указана не верно. Дата начала льготы не может быть меньше ' + minDate.format('d.m.Y')), function() {
					current_window.queryById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}
			
			// проверка даты начала льготы < даты окончания льготы
			if ( !Ext6.isEmpty(lgotEndDate) && lgotStartDate >= lgotEndDate ) {
				Ext6.Msg.alert(langs('Ошибка'), langs('Дата окончания льготы не может быть раньше даты начала.'), function() {
					current_window.queryById('PrivEF_Privilege_begDate').focus();
				});
				return false;
			}
			
			// проверка даты смерти > даты окончания льготы
			if (
				!Ext6.isEmpty(lgotEndDate) && !Ext6.isEmpty(death_date) && lgotEndDate < death_date
			) {
				Ext6.Msg.show({
					buttons: Ext6.Msg.OK,
					fn: function() {
						current_window.queryById('PrivEF_Privilege_endDate').focus();
					},
					icon: Ext6.Msg.WARNING,
					msg: langs('Дата окончания льготы не может быть позже даты смерти пациента'),
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
		}

		var post_data = new Object();

		if((getRegionNick() == 'msk')){
			var base_form = form.getForm();
			post_data.ReceptFinance_id = base_form.findField('PrivilegeType_id').getFieldValue('ReceptFinance_id');
			if(ignore_age && ignore_age.checking_for_regional_benefits){
				post_data.checking_for_regional_benefits = 1;
			}else{
				post_data.checking_for_regional_benefits = (post_data.ReceptFinance_id != 1) ? 1 : null;
			}
		}else{
			post_data.checking_for_regional_benefits = 1;
		}

		if (current_window.queryById('PrivEF_PrivilegeTypeCombo').disabled) {
			post_data.PrivilegeType_id = current_window.queryById('PrivEF_PrivilegeTypeCombo').getValue();
		}
		if (current_window.queryById('PrivEF_Privilege_begDate').disabled) {
			post_data.Privilege_begDate = !Ext6.isEmpty(current_window.queryById('PrivEF_Privilege_begDate').getValue()) ? current_window.queryById('PrivEF_Privilege_begDate').getValue().format('d.m.Y') : null;
		}
		
		var loadMask = new Ext6.LoadMask(current_window, { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		if(current_window.queryById('PrivEF_Privilege_begDate').disabled) {
			post_data.Privilege_begDate = Ext6.util.Format.date(current_window.queryById('PrivEF_Privilege_begDate').getValue(), 'd.m.Y');
		}

		form.getForm().submit({
			failure: function(form_temp, action) {
				loadMask.hide();

				if (action.result && action.result.Error_Msg == 'YesNo') {
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function (buttonId) {
							if ('yes' == buttonId) {
								if (action.result.Error_Code == 201 && action.result.maxEvnRecept_setDate) {
									current_window.queryById('PrivEF_Privilege_endDate').setValue(action.result.maxEvnRecept_setDate);
								}
							}
						},
						icon: Ext6.Msg.WARNING,
						msg: action.result.Alert_Msg,
						title: ERR_INVFIELDS_TIT
					});
					return false;
				} else if(getRegionNick() != 'kz' && action.result && action.result.nosnils) {
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId) {
							if ('yes' == buttonId) {
								//открыть форму «Человек: Редактирование» с установленным фокусом в поле СНИЛС
								getWnd("swPersonEditWindow").show({ action: "edit", Person_id: "" + current_window.queryById('PrivEF_Person_id').getValue() + "", focused: 'Person_SNILS'});
							}
						},
						icon: Ext6.Msg.WARNING,
						msg: langs('Создание льготы невозможно. У пациента отсутствует СНИЛС. Добавить СНИЛС?'),
						title: ERR_INVFIELDS_TIT
					});
				} else if (action.result && action.result.Error_Msg) {
					Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
				} else if (getRegionNick() == 'msk' && action.result && action.result.PrivilegeRegion_Count) {
					var win = current_window;
					Ext6.Msg.show({
						buttons: Ext6.Msg.YESNO,
						fn: function(buttonId) {
							if ('yes' == buttonId) {
								win.doSave({checking_for_regional_benefits: 1});
							}
						},
						icon: Ext6.Msg.WARNING,
						msg: langs('Добавить федеральную льготу и закрыть имеющиеся региональные льготы?'),
						title: ERR_INVFIELDS_TIT
					});
				} else {
					Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
				}
			},
			params: post_data,
			success: function(form_temp, action) {
				loadMask.hide();

				if (action.result)
				{
					if (action.result.PersonPrivilege_id && action.result.PersonPrivilege_id > 0)
					{
						var lpu_id = Ext.globalOptions.globals.lpu_id;
						var lpu_name = '';
						var person_privilege_id = action.result.PersonPrivilege_id;
						var privilege_type_code = null;
						var privilege_type_name = null;
						var response = new Object();
						var server_id = current_window.queryById('PrivEF_Server_id').getValue();

						var privilege_type_record = current_window.queryById('PrivEF_PrivilegeTypeCombo').getStore().getById(current_window.queryById('PrivEF_PrivilegeTypeCombo').getValue());
						if (privilege_type_record)
						{
							privilege_type_code = privilege_type_record.get('PrivilegeType_Code');
							privilege_type_name = privilege_type_record.get('PrivilegeType_Name');
						}

						response.Lpu_id = lpu_id;
						response.Lpu_Name = lpu_name;
						response.Person_Birthday = current_window.Person_Birthday;
						response.Person_Firname = current_window.Person_Firname;
						response.Person_id = current_window.queryById('PrivEF_Person_id').getValue();
						response.PersonEvn_id = current_window.PersonEvn_id;
						response.Person_Secname = current_window.Person_Secname;
						response.Person_Surname = current_window.Person_Surname;
						response.PersonPrivilege_id = person_privilege_id;
						response.Privilege_begDate = current_window.queryById('PrivEF_Privilege_begDate').getValue();
						response.Privilege_endDate = current_window.queryById('PrivEF_Privilege_endDate').getValue();
						response.Privilege_Refuse = '';
						response.PrivilegeType_Code = privilege_type_code;
						response.PrivilegeType_id = current_window.queryById('PrivEF_PrivilegeTypeCombo').getValue();
						response.PrivilegeType_Name = privilege_type_name;
						response.Server_id = server_id;
						response.ReceptFinance_id = current_window.queryById('PrivEF_PrivilegeTypeCombo').getFieldValue('ReceptFinance_id');

						current_window.callback({ PersonPrivilegeData: response });
						current_window.hide();
					}
					else
					{
						if (action.result.Error_Msg)
						{
							Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else
						{
							Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
						}
					}
				}
				else
				{
					if (action.result.Error_Msg)
					{
						Ext6.Msg.alert(langs('Ошибка'), action.result.Error_Msg);
					}
					else
					{
						Ext6.Msg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
					}
				}
			}
		});
	},
	
	enableEdit: function(enable) {
		var current_window = this;

		if (enable)
		{
			current_window.queryById('PrivEF_PrivilegeTypeCombo').enable();
			current_window.queryById('PrivEF_Privilege_begDate').enable();
			current_window.queryById('PrivEF_Privilege_endDate').enable();
			current_window.queryById('PrivEF_DocumentPrivilegeType_set').enable();
			current_window.queryById('PrivEF_PrivilegeCloseType_id').enable();
			current_window.queryById('DocumentPrivilegeType_AddBtn').show();
			current_window.queryById('button_save').enable();
		}
		else
		{
			current_window.queryById('PrivEF_PrivilegeTypeCombo').disable();
			current_window.queryById('PrivEF_Privilege_begDate').disable();
			current_window.queryById('PrivEF_Privilege_endDate').disable();
			current_window.queryById('PrivEF_DocumentPrivilegeType_set').disable();
			current_window.queryById('PrivEF_PrivilegeCloseType_id').disable();
			current_window.queryById('DocumentPrivilegeType_AddBtn').hide();
			current_window.queryById('button_save').disable();
		}
	},
			
	show: function() {
		this.callParent(arguments);
		var current_window = this;
		var win = this;
		var form = this.MainPanel;
		var base_form = form.getForm();
		win.taskButton.hide();
		current_window.action = null;
		current_window.callback = Ext6.emptyFn;
		//~ current_window.onHide = Ext6.emptyFn;
		current_window.ARMType = '';
		
		var loadMask = new Ext6.LoadMask(current_window, { msg: LOAD_WAIT });
		
		form.getForm().reset();
		
		if (arguments[0].action)
		{
			current_window.action = arguments[0].action;
		}

		if (arguments[0].callback)
		{
			current_window.callback = arguments[0].callback;
		}

		//~ if (arguments[0].onHide)
		//~ {
			//~ current_window.onHide = arguments[0].onHide;
		//~ }

		if (arguments[0].ARMType)
		{
			current_window.ARMType = arguments[0].ARMType;
		}
		
		if (arguments[0].Person_Birthday) {
			current_window.Person_Birthday = arguments[0].Person_Birthday;
		}
		if (arguments[0].Person_deadDT) {
			current_window.Person_deadDT = arguments[0].Person_deadDT;
		}

		form.getForm().setValues(arguments[0]);

		var person_id = current_window.queryById('PrivEF_Person_id').getValue();
		
		var person_privilege_id = current_window.queryById('PrivEF_PersonPrivilege_id').getValue();
		
		
		var privilege_type_id = current_window.queryById('PrivEF_PrivilegeTypeCombo').getValue();
		var server_id = current_window.queryById('PrivEF_Server_id').getValue();
		
		switch (current_window.action)
		{
			case 'add':

				//~ current_window.setTitle(langs('Льгота'));
				//~ current_window.queryById('button_save').setText(langs('Применить'));
				current_window.enableEdit(true);
				
				// надо установить 0, иначе, если в параметрах пришло undefined, то сервер ругается
				
				//добавлена фильтрация категорий льготы (региональные)
				//https://redmine.swan.perm.ru/issues/18058
				//Добавлена фильтрация льгот по дате
				//http://redmine.swan.perm.ru/issues/17500
				var globalOptions = getGlobalOptions();
				var cond = (
					globalOptions.region.nick == 'saratov'
					&& isUserGroup('OrgUser') && globalOptions.CurMedServiceType_SysNick
					&& globalOptions.CurMedServiceType_SysNick.inlist(['mekllo','minzdravdlo'])
				);

				if ( !isSuperAdmin() && !cond && getRegionNick() != 'kz' ){
					current_window.queryById('PrivEF_PrivilegeTypeCombo').getStore().filterBy(function (rec) {
						var nowDate = new Date();
						
						return ( ((rec.get('ReceptFinance_id') == 2) || (getRegionNick() == 'krym' && isUserGroup('ChiefLLO') && rec.get('ReceptFinance_id').inlist([1,2])))
						&& (Ext6.isEmpty(rec.get('PrivilegeType_begDate')) || rec.get('PrivilegeType_begDate') <= nowDate)
						&& (Ext6.isEmpty(rec.get('PrivilegeType_endDate')) || rec.get('PrivilegeType_endDate') >= nowDate)
						);
					});
				};

				current_window.queryById('PrivEF_Privilege_begDate').setValue(new Date());
				current_window.queryById('PrivEF_Privilege_begDate').setDisabled(getRegionNick() == 'perm' && !isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']));

				loadMask.hide();
				if (!Ext6.isEmpty(current_window.queryById('PrivEF_PrivilegeTypeCombo').getValue())) {
					current_window.queryById('PrivEF_PrivilegeTypeCombo').fireEvent('change', current_window.comboPrivelege, current_window.comboPrivelege.getValue());
				} else {
					current_window.queryById('PrivEF_PersonPrivilege_id').setValue(0);
				}
				if (getGlobalOptions().isMinZdrav || current_window.ARMType.inlist(['superadmin','minzdravdlo'])){
					base_form.findField('PersonPrivilege_IsAddMZ').setValue(2);
				}
				
				current_window.queryById('PrivEF_PrivilegeTypeCombo').focus(true, 250);
				break;

			case 'edit':
				//~ current_window.queryById('button_save').setText(langs('Сохранить'));
				current_window.enableEdit(true);
				if(getRegionNick() == 'perm'&&isSuperAdmin()){
					Ext6.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								if ( response.responseText )
								{

									var result  = Ext6.util.JSON.decode(response.responseText);
									if(result){
										if(result[0] && result[0].PersonRefuse_id){
											current_window.queryById('PersonRefuse_id').setValue(result[0].PersonRefuse_id);
										}
									}
								}
							}
						},
						params: {
							Person_id:person_id
						},
						url: "/?c=PersonRefuse&m=getPersonRefuseId"
					});
				}
					
				form.getForm().load({
					failure: function() {
						loadMask.hide();
						Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы с сервера'));
					},
					params: {
						Person_id: person_id,
						PersonPrivilege_id: person_privilege_id,
						PrivilegeType_id: privilege_type_id,
						Server_id: server_id
					},
					success: function() {
						loadMask.hide();
						
						var hasRecepts = (current_window.queryById('PrivEF_hasRecepts').getValue() == 1);

						current_window.queryById('PrivEF_PrivilegeTypeCombo').setDisabled(hasRecepts);
						current_window.queryById('PrivEF_Privilege_begDate').setDisabled(hasRecepts || !isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']));
						current_window.queryById('PrivEF_Privilege_endDate').setDisabled(!Ext6.isEmpty(current_window.queryById('PrivEF_Privilege_endDate').getValue()) && !isUserGroup(['SuperAdmin','ChiefLLO','minzdravdlo']));

						base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), base_form.findField('PrivilegeType_id').getValue());
						form.getForm().clearInvalid();
						
					},
					url: C_PRIV_LOAD_EDIT
				});
				break;

			case 'view':
				current_window.queryById('button_save').setText(langs('Сохранить'));
				current_window.enableEdit(false);
				if(getRegionNick() == 'perm'&&isSuperAdmin()){
					Ext6.Ajax.request({
						callback: function(options, success, response) {
							if (success)
							{
								if ( response.responseText )
								{

									var result  = Ext6.util.JSON.decode(response.responseText);
									if(result[0] && result[0].PersonRefuse_id){
										current_window.queryById('PersonRefuse_id').setValue(result[0].PersonRefuse_id);
									}
								}
							}
						},
						params: {
							Person_id:person_id
						},
						url: "/?c=PersonRefuse&m=getPersonRefuseId"
					});
				}
									
				form.getForm().load({
					failure: function() {
						loadMask.hide();
						Ext6.Msg.alert(langs('Ошибка'), langs('Ошибка при загрузке данных формы с сервера'));
					},
					params: {
						Person_id: person_id,
						PersonPrivilege_id: person_privilege_id,
						PrivilegeType_id: privilege_type_id,
						Server_id: server_id
					},
					success: function() {
						loadMask.hide();
						form.getForm().clearInvalid();
						base_form.findField('PrivilegeType_id').fireEvent('change', base_form.findField('PrivilegeType_id'), base_form.findField('PrivilegeType_id').getValue());
					},
					url: C_PRIV_LOAD_EDIT
				});
				break;
		}
	},
	initComponent: function() {
		 var win = this;

		win.DocumentPrivilegeType_AddBtn = new Ext6.create('Ext6.button.Button', {
			itemId: 'DocumentPrivilegeType_AddBtn',
			userCls: 'button-without-frame',
			iconCls: 'menu_dispadd',
			handler: function () {
				var combo = win.queryById('DocumentPrivilegeTypeCombo');
				if (!combo.disabled) {
					getWnd('swDocumentPrivilegeTypeAddWindow').show({
						callback: function(data) {
							if (!Ext6.isEmpty(data.DocumentPrivilegeType_id)) {
								combo.getStore().DocumentPrivilegeType_id = data.DocumentPrivilegeType_id;
								combo.getStore().load({
									callback: function(){
										combo.setValue(data.DocumentPrivilegeType_id);
									}
								});
							}
						}
					});
				}
			}.createDelegate(this)});

		win.MainPanel = new Ext6.form.FormPanel({
				cls: 'emk_forms',
				bodyStyle: 'padding: 15px 32px 0px 32px',
				border: false,
				frame: false,
				itemId: 'PrivilegeEditForm',
				labelAlign: 'right',
				items: [{
					itemId: 'PrivEF_Server_id',
					name: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					itemId: 'PrivEF_Person_id',
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					itemId: 'PersonRefuse_id',
					name: 'PersonRefuse_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					itemId: 'PersonPrivilege_IsAddMZ',
					name: 'PersonPrivilege_IsAddMZ',
					value: 0,
					xtype: 'hidden'
				},
				{
					itemId: 'PrivEF_PersonPrivilege_id',
					name: 'PersonPrivilege_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					itemId: 'PrivEF_hasRecepts',
					name: 'hasRecepts',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'DocumentPrivilege_id',
					xtype: 'hidden'
				},
				{
					xtype: 'swPrivilegeTypeCombo',
					listConfig: {
						//itemCls: 'my-item-cls',
						userCls: 'privilegeMenu'
					},
					width: 348+82+5,
					labelWidth: 82,
					allowBlank: false,
					anchor: '100%',
					fieldLabel: langs('Категория'),
					sortField: 'PrivilegeType_Code',
					name: 'PrivilegeType_id',
					itemId: 'PrivEF_PrivilegeTypeCombo',
					listeners:{
					//Добавлена проверка и ограничение на вводимую дату 
					//http://redmine.swan.perm.ru/issues/17500
						'change':function (combo, newValue, oldValue) {
							var index = combo.getStore().findBy(function (rec) {
								return (rec.get(combo.valueField) == newValue);
							});
							var record = combo.getStore().getAt(index);

							combo.fireEvent('select', combo, record);
						}.createDelegate(this),
						'select': function (combo, record, id) {
							var base_form = this.MainPanel.getForm();
							
							let privilege_data = record.data;
							
							base_form.findField('Privilege_begDate').setMaxValue(undefined);
							base_form.findField('Privilege_endDate').setMaxValue(undefined);
							base_form.findField('Privilege_begDate').setMinValue(undefined);
							base_form.findField('Privilege_endDate').setMinValue(undefined);
							
							if (!Ext6.isEmpty(record)) {
								if (!Ext6.isEmpty(record.get('PrivilegeType_begDate'))) {
									base_form.findField('Privilege_begDate').setMinValue(record.get('PrivilegeType_begDate'));
									base_form.findField('Privilege_endDate').setMinValue(record.get('PrivilegeType_begDate'));
								}
								
								if (!Ext6.isEmpty(record.get('PrivilegeType_endDate'))) {
									base_form.findField('Privilege_begDate').setMaxValue(record.get('PrivilegeType_endDate'));
									base_form.findField('Privilege_endDate').setMaxValue(record.get('PrivilegeType_endDate'));
								}
							}

							let doc_visible = false;
							
							if (!Ext6.isEmpty(privilege_data.PrivilegeType_id)) {
								//льгота явдялется федеральной или льгота является региональной и имеет признак «Документ на льготу»; настройка "Льготы социальные. Контроль на наличие данных документа, подтверждающего наличие льгот" включена
								doc_visible = ((privilege_data && privilege_data.ReceptFinance_id == 1) || (privilege_data && privilege_data.ReceptFinance_id == 2 && privilege_data.PrivilegeType_IsDoc == 2 && getGlobalOptions().social_privilege_document_available_checking));
							}

							base_form.findField('DocumentPrivilegeType_id').enable_blocked = !doc_visible;
							base_form.findField('DocumentPrivilege_Ser').enable_blocked = !doc_visible;
							base_form.findField('DocumentPrivilege_Num').enable_blocked = !doc_visible;
							base_form.findField('DocumentPrivilege_begDate').enable_blocked = !doc_visible;
							base_form.findField('DocumentPrivilege_Org').enable_blocked = !doc_visible;
							base_form.findField('DocumentPrivilegeType_id').allowBlank = !doc_visible;
							base_form.findField('DocumentPrivilege_Ser').allowBlank = !doc_visible;
							base_form.findField('DocumentPrivilege_Num').allowBlank = !doc_visible;
							base_form.findField('DocumentPrivilege_begDate').allowBlank = !doc_visible;
							base_form.findField('DocumentPrivilege_Org').allowBlank = !doc_visible;
							
							if (doc_visible) {
								win.queryById('PrivEF_DocumentPrivilegeType_set').show();
							} else {
								win.queryById('PrivEF_DocumentPrivilegeType_set').hide();
							}
							
						/*	if (getRegionNick() == 'kz' && (record.get('PrivilegeType_Code') == '18' || record.get('ReceptDiscount_id') != '3')) {
								var age = swGetPersonAgeDay(this.findById('PrivEF_PersonInformationFrame').getFieldValue('Person_Birthday'), new Date());
								base_form.findField('SubCategoryPrivType_id').showContainer();
								base_form.findField('SubCategoryPrivType_id').setAllowBlank(false);
								base_form.findField('SubCategoryPrivType_id').lastQuery = '';
								base_form.findField('SubCategoryPrivType_id').getStore().clearFilter();
								base_form.findField('SubCategoryPrivType_id').getStore().filterBy(function(rec) {
									var test = true;
									
									test = test && rec.get('SubCategoryPrivType_IsSocVulnGroup') == (record.get('PrivilegeType_Code') == '18' ? 2 : 1);
									
									if(!!rec.get('SubCategoryPrivType_minAgeDay')) {
										test = test && rec.get('SubCategoryPrivType_minAgeDay') <= age;
									}
									
									if(!!rec.get('SubCategoryPrivType_maxAgeDay')) {
										test = test && rec.get('SubCategoryPrivType_maxAgeDay') >= age;
									}
									
									return test;
								});
							} else {
								base_form.findField('SubCategoryPrivType_id').hideContainer();
								base_form.findField('SubCategoryPrivType_id').setAllowBlank(true);
							}
							this.syncShadow();*/
						}.createDelegate(this)
					}
				}, {
						xtype: 'fieldset',
						itemId: 'PrivEF_DocumentPrivilegeType_set',
						title: langs('Документ о праве на льготу'),
						autoHeight: true,
						style: 'padding: 3px; margin-bottom: 7px; display: block;',
						labelWidth: 210,
						items: [
							{
								layout: 'column',
								border: false,
								items: [{
									xtype: 'swDocumentPrivilegeType',
									width: 400,
									labelWidth: 100,
									allowBlank: false,
									name: 'DocumentPrivilegeType_id',
									itemId: 'DocumentPrivilegeTypeCombo'
								}, win.DocumentPrivilegeType_AddBtn
								]},
							{
								xtype: 'textfield',
								allowBlank: false,
								fieldLabel: langs('Серия документа'),
								name: 'DocumentPrivilege_Ser',
								width: 425
							}, {
								xtype: 'textfield',
								allowBlank: false,
								fieldLabel: langs('Номер документа'),
								name: 'DocumentPrivilege_Num',
								width: 425
							}, {
								width: 231,
								allowBlank: false,
								fieldLabel: langs('Дата выдачи документа'),
								format: 'd.m.Y',
								name: 'DocumentPrivilege_begDate',
								plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
								validateOnBlur: true,
								xtype: 'datefield'
							}, {
								xtype: 'textfield',
								allowBlank: false,
								fieldLabel: langs('Организация, выдавшая документ'),
								name: 'DocumentPrivilege_Org',
								width: 425
							}]
					}, new Ext6.Panel(
				{
					layout: 'column',
					border: false,
					items: [
					{
						width: 124+82+5,
						labelWidth: 82,
						allowBlank: false,
						fieldLabel: langs('Начало'),
						format: 'd.m.Y',
						itemId: 'PrivEF_Privilege_begDate',
						listeners: {
							'keydown': function (inp, e) {
								if (!e.shiftKey && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									win.queryById('PrivEF_Privilege_endDate').focus(true);
								}
							}, 
							'change': function(combo, newValue, oldValue) {
								blockedDateAfterPersonDeath('personpanelid', 'PrivEF_PersonInformationFrame', combo, newValue, oldValue);
							}.createDelegate(this)
						},
						name: 'Privilege_begDate',
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 456,
						validateOnBlur: true,
						xtype: 'datefield'
					}, {
						width: 124+97+3,
						labelAlign: 'right',
						labelWidth: 97,
						fieldLabel: langs('Окончание'),
						format: 'd.m.Y',
						itemId: 'PrivEF_Privilege_endDate',
						listeners: {
							'keydown': function (inp, e) {
								if (e.shiftKey && e.getKey() == Ext.EventObject.TAB)
								{
									e.stopEvent();
									win.queryById('PrivEF_Privilege_begDate').focus(true);
								}
							}
						},
						name: 'Privilege_endDate',
						plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 451,
						xtype: 'datefield'
					}, {
							xtype: 'commonSprCombo',
							comboSubject: 'PrivilegeCloseType',
							fieldLabel: 'Причина закрытия',
							itemId: 'PrivEF_PrivilegeCloseType_id',
							name: 'PrivilegeCloseType_id',
							width: 435,
							labelWidth:82
					}
					]
				}	
				)			
				],
				url: C_PRIV_SAVE,
				reader: Ext6.create('Ext6.data.reader.Json', {
					type: 'json',
					model: Ext6.create('Ext6.data.Model', {
						fields:[
							{ name: 'PersonPrivilege_id' },
							{ name: 'DocumentPrivilege_Ser'},
							{ name: 'DocumentPrivilege_Num'},
							{ name: 'DocumentPrivilege_begDate'},
							{ name: 'DocumentPrivilege_Org'},
							{ name: 'PrivilegeCloseType_id'},
							{ name: 'Privilege_begDate' },
							{ name: 'Privilege_endDate' },
							{ name: 'DocumentPrivilege_id' },
							{ name: 'PrivilegeType_id' },
							{ name: 'SubCategoryPrivType_id' },
							{ name:'PersonPrivilege_IsAddMZ'}
						]
					})
				})
			});
				
		Ext6.apply(win, {
			items: [
				win.MainPanel
			],
			border: false,
			buttons:
			[ '->'
			, {
				text: langs('Отмена'),
				userCls:'buttonPoupup buttonCancel',
				handler: function() {
					win.hide();
				}
			}, {
				text: langs('Применить'),
				itemId: 'button_save',
				userCls:'buttonPoupup buttonAccept',
				margin: '0 19 0 0',
				handler: function() {
					win.doSave();
				}
			}]
		});
		this.callParent(arguments);
	}
});