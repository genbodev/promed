/**
* swEvnStickWorkReleaseEditWindow - редактирование данных освобождений от работы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      17.08.2011
* @comment      Префикс для id компонентов EStWREF (EvnStickWorkReleaseEditForm)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swEvnStickWorkReleaseEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnStickWorkReleaseEditWindow',
	objectSrc: '/jscore/Forms/Stick/swEvnStickWorkReleaseEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
    Post_id: null,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object
		if (!options) {
			options = {};
		}

		this.checkVK();
		
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: this.FormPanel.getInvalidFieldsMessage(),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var data = new Object();
		var record = base_form.findField('MedStaffFact_id').getStore().getById(base_form.findField('MedStaffFact_id').getValue());
		var record_2 = base_form.findField('MedStaffFact2_id').getStore().getById(base_form.findField('MedStaffFact2_id').getValue());
		var record_3 = base_form.findField('MedStaffFact3_id').getStore().getById(base_form.findField('MedStaffFact3_id').getValue());
		var med_personal_fio = '';
		var med_personal_id = 0;
		var med_personal2_id = 0;
		var med_personal3_id = 0;

		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}
		if( record_2 ) {
			med_personal2_id = record_2.get('MedPersonal_id');
		}

		if( record_3 ) {
			med_personal3_id = record_3.get('MedPersonal_id');
		}
		base_form.findField('MedPersonal_id').setValue(med_personal_id);
		base_form.findField('MedPersonal2_id').setValue(med_personal2_id);
		base_form.findField('MedPersonal3_id').setValue(med_personal3_id);


		var newSumDate = this.sumDate;
		if ( !Ext.isEmpty(base_form.findField('EvnStickWorkRelease_begDate').getValue()) && !Ext.isEmpty(base_form.findField('EvnStickWorkRelease_endDate').getValue()) ) {
			var newSumDate = this.sumDate + Math.round((base_form.findField('EvnStickWorkRelease_endDate').getValue() - base_form.findField('EvnStickWorkRelease_begDate').getValue()) / 86400000) + 1;
		}

		if(
			base_form.findField('EvnStickWorkRelease_IsPredVK').getValue() == false
			&& base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code')
			&& base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code').inlist([115,117]) // фельдшер, зубной врач
			&& newSumDate >= 11
			&& !(
				win.StickCause_SysNick == 'karantin' // Карантин
				|| win.StickCause_SysNick == 'pregn' // Отпуск по беременности и родам
				|| ( getRegionNick().inlist(['kz', 'ekb']) && win.StickCause_SysNick == 'soczab' ) //Соц. значимое заболевание
				|| ( getRegionNick() == 'ekb' && win.StickCause_SysNick == 'deseaseP1' )//Заболевание, указанное в пункте 1 Перечня социально значимых заболеваний
				|| win.StickCause_SysNick == 'postvaccinal' // Поствакцинальное осложнение или злокачественное новообразование у ребенка
				|| win.StickCause_SysNick == 'vich' // ВИЧ-инфицированный ребенок
			)
		) {
			sw.swMsg.alert(langs('Ошибка'), langs('Фельдшер или зубной врач выдает и продляет ЛВН на срок до 10 дней включительно. Для сохранения внесите информацию о председателе ВК'));
			loadMask.hide();
			this.formStatus = 'edit';
			return false;
		}

		if (
			!options.ignoreCheckCarePersonAge
			&& getRegionNick() != 'kz'
			&& win.CarePerson_Age < 18
			&& win.StickCause_SysNick
			&& win.StickCause_SysNick.inlist(['uhodnoreb', 'uhodreb', 'zabrebmin', 'rebinv', 'postvaccinal', 'vich'])
		) {
			var getPrivilege = function(obj) {
				var params = {
					Person_id: win.CarePerson_id,
					PrivilegeTypeCodeList: Ext.util.JSON.encode(obj.PrivilegeList)
				}
				Ext.Ajax.request({
					url: '/?c=Privilege&m=CheckPersonHaveActiveFederalPrivilege',
					params: params,
					success: function(response, options) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result.success === false) {
							win.formStatus = 'edit';
							loadMask.hide();
							return false;
						}
						if (typeof obj.callback == 'function') {
							var response = {}
							if (result && result[0] && result[0].Privilege_Count) {
								response.Privilege_Count = result[0].Privilege_Count;
							} else {
								response.Privilege_Count = 0;
							}
							response.WorkReleaseSumm = obj.WorkReleaseSumm;

							obj.callback(response);
						}
					},
					failure: function() {
						win.formStatus = 'edit';
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), 'Ошибка при проверке льгот');
					}
				});
			};

			// получение суммарных периодов нетрудоспособности для предыдщих ЛВН 
			// формат ответа {WorkReleaseSumm: {EvnPS24: <число дней в кругл стац>, other: <количество дней в ост. уч док>}, Privilege_Count: <количество активных федеральных льгот из списка>}
			var getWorkReleaseSumPeriod = function (obj) {
				if (typeof obj.callback != 'function') {
					return false;
				}
				
				if (!win.EvnStick_prid || win.EvnStick_prid == 0) {
					obj.WorkReleaseSumm = {	EvnPS24: 0,	other: 0 }
					if (Array.isArray(obj.PrivilegeList) && obj.PrivilegeList.length > 0) {
						getPrivilege(obj);
					} else {
						obj.callback({WorkReleaseSumm:obj.WorkReleaseSumm, Privilege_Count: 0});
					}
					return false;
				}
				var params = {
					getEvnPS24: true,
					EvnStick_id: win.EvnStick_prid
				}
				Ext.Ajax.request({
					url: '/?c=Stick&m=getWorkReleaseSumPeriod',
					params, params,
					success: function(response, options) {
						var result = Ext.util.JSON.decode(response.responseText);

						var WorkReleaseSumm = {EvnPS24: 0, other: 0};
						if (result.success === false) {
							win.formStatus = 'edit';
							loadMask.hide();
							return false;
						}

						for (key in result) {
							if (result[key]['EvnStickParent_Type']) {
								WorkReleaseSumm[result[key]['EvnStickParent_Type']] = result[key]['WorkReleaseSumm'];
							}
						}
						if (Array.isArray(obj.PrivilegeList) && obj.PrivilegeList.length > 0) {
							obj.WorkReleaseSumm = WorkReleaseSumm;
							getPrivilege(obj);
						} else {
							obj.callback({WorkReleaseSumm: WorkReleaseSumm, Privilege_Count: 0});
						}	
					},
					failure: function() {
						win.formStatus = 'edit';
						loadMask.hide();
						sw.swMsg.alert(langs('Ошибка'), 'Ошибка при получении продолжительности периодов нетрудоспособности');
					}
				});
				return false;
			};

			var maxDays;
			var getAlertMsg = function (CarePerson_Fio, maxDays, period, msg) {
				var msg = "Превышение допустимого количества дней нетрудоспособности по уходу за " + CarePerson_Fio
					+ ": допускается " + maxDays + " дня(ей), текущее значение " + period + " дня(ей). В соответствии с приказом 624н от 26.06.2011"
					+ " листок нетрудоспособности выдается по уходу за больным членом семьи: " + msg + " Продолжить сохранение?";
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						win.formStatus = 'edit';
						if ( buttonId == 'yes' ) {
							options.ignoreCheckCarePersonAge = true;
							win.doSave(options);
						} else {
							loadMask.hide();
						}
					},
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: langs('Вопрос')
				});
				
			}

			var curSumDate = win.curSumDate + Math.round((base_form.findField('EvnStickWorkRelease_endDate').getValue() - base_form.findField('EvnStickWorkRelease_begDate').getValue()) / 86400000) + 1;
			var curSumDate24 = 0,
				curSumDateEPL = 0; 
			if (win.isHasDvijeniaInStac24) {
				curSumDate24 = curSumDate;
			} else {
				curSumDateEPL = curSumDate;
			}
			if (
				win.CarePerson_Age >= 7 
				&& win.CarePerson_Age <= 14 
				&& !base_form.findField('EvnStickWorkRelease_IsPredVK').getValue()
				&& win.StickCause_SysNick.inlist(['uhodnoreb', 'uhodreb', 'zabrebmin', 'vich'])
			) {
				maxDays = 15;
				var msg = "ребенком в возрасте от 7 до 15 лет: при амбулаторном лечении или совместном пребывании"
					+ " одного из членов семьи (опекуна, попечителя, иного родственника) с ребенком в стационарном"
					+ " лечебно-профилактическом учреждении - на срок до 15 дней по каждому случаю заболевания, если по"
					+ " заключению врачебной комиссии не требуется большего срока;";
				var PrivilegeList = [];
				if (win.StickCause_SysNick != 'vich') {
					PrivilegeList = ['99', '113', '129'];
				}

				getWorkReleaseSumPeriod({
					PrivilegeList: PrivilegeList,
					callback: function(result) {
						if (win.StickCause_SysNick != 'vich') {
							if (result.Privilege_Count == 0 && maxDays < curSumDate + result.WorkReleaseSumm.EvnPS24 + result.WorkReleaseSumm.other) {
								getAlertMsg(win.CarePerson_Fio, maxDays, curSumDate + result.WorkReleaseSumm.EvnPS24 + result.WorkReleaseSumm.other, msg);
								return false;
							}
						} else {
							if (maxDays < curSumDateEPL + result.WorkReleaseSumm.other) {
								getAlertMsg(win.CarePerson_Fio, maxDays, curSumDateEPL + result.WorkReleaseSumm.other, msg);
								return false;
							}
						}
						win.formStatus = 'edit';
						options.ignoreCheckCarePersonAge = true;
						win.doSave(options);
					}
				});
				return false;	
			}

			if (
				win.CarePerson_Age >= 15
				&& win.CarePerson_Age <= 17
				&& win.StickCause_SysNick.inlist(['uhodnoreb', 'uhodreb', 'zabrebmin', 'vich'])
			) {

				var maxDays24 = 0,	maxDays;
				var msg = "детьми старше 15 лет: при амбулаторном лечении - на срок до 3 дней,"
					+ " по решению врачебной комиссии - до 7 дней по каждому случаю заболевания.";
				if (base_form.findField('EvnStickWorkRelease_IsPredVK').getValue()) {
					maxDays = 7;
				} else {
					maxDays = 3;
				}

				getWorkReleaseSumPeriod({
					callback: function(result) {
						if (win.StickCause_SysNick != 'vich') {
							if (curSumDate24 + result.WorkReleaseSumm.EvnPS24 > maxDays24) {
								getAlertMsg(win.CarePerson_Fio, maxDays24, curSumDate24 + result.WorkReleaseSumm.EvnPS24, msg);
								return false;
							}
						}

						if (curSumDateEPL + result.WorkReleaseSumm.other > maxDays) {
							getAlertMsg(win.CarePerson_Fio, maxDays, curSumDateEPL + result.WorkReleaseSumm.other, msg);
							return false;
						}
						win.formStatus = 'edit';
						options.ignoreCheckCarePersonAge = true;
						win.doSave(options);
					}
				});
				return false;
			}
		}


		if ( ! base_form.findField('EvnStickWorkRelease_IsDraft').getValue()){


			// Правила заполнения врачей в периоде освобождения (переключатель в "Параметры системы" Options.php, блок evnstick)
			// 1 - "Разрешить выбирать в поле «Врач 1», «Врач 2», «Врач 3» одного сотрудника. Регион: Все, кроме Пермь, Хакасия. Установлено по умолчанию"
			// 2 - "Запретить выбирать в поле "Врач 3" (председатель ВК) сотрудника, указанного в поле "Врач 1" и/или "Врач 2".  Регион: Пермь, Хакасия"
			// 3 - "Запретить выбирать в поле «Врач 1», «Врач 2», «Врач 3» одного сотрудника."
			var rules_filling_doctors_workrelease = parseInt(getGlobalOptions().rules_filling_doctors_workrelease);



			if(rules_filling_doctors_workrelease == 1) {
				// Если на форме «Параметры системы» на уровне «ЛВН» установлено значение «Разрешить выбирать в поле «Врач 1»,
				// «Врач 2», «Врач 3» одного сотрудника», то значение полей «Врач 1», «Врач 2», «Врач 3» могут совпадать
				// (т.е. может быть указан один и тот же врач) или не совпадать, при этом форма сохраняется



			} else if(rules_filling_doctors_workrelease == 2){
				// Если на форме «Параметры системы» на уровне «ЛВН» установлено значение «Запретить выбирать в поле
				// "Врач 3" (председатель ВК) сотрудника, указанного в поле "Врач 1" и/или "Врач 2"» И  врач, установленный
				// в поле «Врач 3»  совпадает с врачом, установленным в поле «Врач 1» и/или «Врач 2», то отображается
				// сообщение об ошибке: «Сотрудник, указанный в поле "Врач 3", также указан в поле "Врач 1" и/или "Врач 2".
				// Выберите в поле "Врач 3" другого врача». При этом, врач выбранный в поле «Врач 1» и «Врач 2» может совпадать.
				if(med_personal3_id != 0){
					if(
						(med_personal_id != 0 && med_personal_id == med_personal3_id) ||
						(med_personal2_id != 0 && med_personal2_id == med_personal3_id)
					){
						sw.swMsg.alert(langs('Ошибка'), langs('Сотрудник, указанный в поле "Врач 3", также указан в поле "Врач 1" и/или "Врач 2". Выберите в поле "Врач 3" другого врача'));
						this.formStatus = 'edit';
						loadMask.hide();
						return false;
					}
				}



			} else if(rules_filling_doctors_workrelease == 3){
				// Если на форме «Параметры системы» на уровне «ЛВН» установлено значение «Запретить выбирать в поле
				// «Врач 1», «Врач 2», «Врач 3» одного сотрудника» И в поле «Врач 1» и/или «Врач 2» и/или «Врач 3» указан
				// один и тот же врач,  то при сохранении отображается сообщение об ошибке: «В полях «Врач 1», «Врач 2»,
				// «Врач 3» должны быть указаны разные сотрудники. Выберите разных врачей». Иными словами,  должны быть
				// указаны разные врачи в полях «Врач 1», «Врач 2», «Врач 3».

				if(
					(
						med_personal_id == med_personal2_id ||
						(med_personal2_id == med_personal3_id && med_personal3_id != 0) ||
						(med_personal_id == med_personal3_id && med_personal3_id != 0)
					)
				){
					sw.swMsg.alert(langs('Ошибка'), langs('В полях «Врач 1», «Врач 2», «Врач 3» должны быть указаны разные сотрудники. Выберите разных врачей'));
					this.formStatus = 'edit';
					loadMask.hide();
					return false;
				}

			} else {
				// На случай если еще не успели установить значение параметра "rules_filling_doctors_workrelease" (тогда он будет равен NaN), то мы оставляем старую проверку по умолчанию
				if(
					(
						med_personal_id == med_personal2_id ||
						(med_personal2_id == med_personal3_id && med_personal3_id != 0) ||
						(med_personal_id == med_personal3_id && med_personal3_id != 0)
					)
				){
					sw.swMsg.alert(langs('Ошибка'), langs('В полях «Врач 1», «Врач 2», «Врач 3» должны быть указаны разные сотрудники. Выберите разных врачей'));
					this.formStatus = 'edit';
					loadMask.hide();
					return false;
				}
			}



		}


		var EvnStickWorkRelease_IsPredVK = 0;
		
		if (base_form.findField('EvnStickWorkRelease_IsPredVK').getValue()) {
			EvnStickWorkRelease_IsPredVK = 1;
		}

		var EvnStickWorkRelease_IsDraft = 0;

		if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
			EvnStickWorkRelease_IsDraft = 1;
		}

		var EvnStickWorkRelease_IsSpecLpu = 0;

		if (base_form.findField('EvnStickWorkRelease_IsSpecLpu').getValue()) {
			EvnStickWorkRelease_IsSpecLpu = 1;
		}

		var Org_id = getGlobalOptions().org_id;
		var Org_Nick = getGlobalOptions().org_nick;
		if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
			Org_id = base_form.findField('Org_id').getValue();
			Org_Nick = base_form.findField('Org_id').getFieldValue('Org_Nick');
		}

		data.evnStickWorkReleaseData = {
			'accessType': 'edit',
			'signAccess': 'edit',
			'EvnStickBase_id': base_form.findField('EvnStickBase_id').getValue(),
			'EvnStickWorkRelease_begDate': base_form.findField('EvnStickWorkRelease_begDate').getValue(),
			'EvnStickWorkRelease_endDate': base_form.findField('EvnStickWorkRelease_endDate').getValue(),
			'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
			'LpuUnitType_SysNick': base_form.findField('LpuSection_id').getFieldValue('LpuUnitType_SysNick'),
			'MedPersonal_Fio': med_personal_fio,
			'MedPersonal_id': base_form.findField('MedPersonal_id').getValue(),
			'MedPersonal2_id': base_form.findField('MedPersonal2_id').getValue(),
			'MedPersonal3_id': base_form.findField('MedPersonal3_id').getValue(),
			'MedStaffFact_id': base_form.findField('MedStaffFact_id').getValue(),
			'MedStaffFact2_id': base_form.findField('MedStaffFact2_id').getValue(),
			'MedStaffFact3_id': base_form.findField('MedStaffFact3_id').getValue(),
			'Lpu_id': getGlobalOptions().lpu_id,
			'EvnStickWorkRelease_IsPredVK': EvnStickWorkRelease_IsPredVK,
			'EvnStickWorkRelease_IsDraft': EvnStickWorkRelease_IsDraft,
			'EvnStickWorkRelease_IsSpecLpu': EvnStickWorkRelease_IsSpecLpu,
			'Org_id': Org_id,
			'Post_id' : base_form.findField('Post_id').getValue(),
			'EvnVK_id' : base_form.findField('EvnVK_id').getValue(),
			'EvnVK_descr' : base_form.findField('EvnVK_descr').getValue(),
			'Org_Name': Org_Nick
		};

		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				data.evnStickWorkReleaseData.EvnStickWorkRelease_id = base_form.findField('EvnStickWorkRelease_id').getValue();

				this.callback(data);
				this.hide();
			break;

			case 'remote':
				base_form.submit({
                    params: {
                        'LpuSection_id': base_form.findField('LpuSection_id').getValue(),
                        'MedPersonal_id': base_form.findField('MedPersonal_id').getValue(),
                        'Post_id': base_form.findField('Post_id').getValue()
                    },
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.EvnStickWorkRelease_id > 0 ) {
							base_form.findField('EvnStickWorkRelease_id').setValue(action.result.EvnStickWorkRelease_id);

							data.evnStickWorkReleaseData.EvnStickWorkRelease_id = base_form.findField('EvnStickWorkRelease_id').getValue();

							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	formStatus: 'edit',
	checkVK: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var isPerm = (getRegionNick() == 'perm');
		var isKZ = (getRegionNick() == 'kz');

		var newSumDate = this.sumDate;
		
		if ( !Ext.isEmpty(base_form.findField('EvnStickWorkRelease_begDate').getValue()) && !Ext.isEmpty(base_form.findField('EvnStickWorkRelease_endDate').getValue()) ) {
			var newSumDate = this.sumDate + Math.round((base_form.findField('EvnStickWorkRelease_endDate').getValue() - base_form.findField('EvnStickWorkRelease_begDate').getValue()) / 86400000) + 1;
		}

		if (isKZ) {
			if (newSumDate >= 7) {
				base_form.findField('MedStaffFact2_id').allowBlank = false;
			} else {
				base_form.findField('MedStaffFact2_id').allowBlank = true;
			}
			if (newSumDate > 20) {
				// для черновика поле скрыто
				if (!base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
					base_form.findField('EvnStickWorkRelease_IsPredVK').setValue(1);
					base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('check', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
				}
				base_form.findField('EvnStickWorkRelease_IsPredVK').disable();
			} else {
				if (win.action != 'view') {
					base_form.findField('EvnStickWorkRelease_IsPredVK').enable();
				}
			}
			if (this.StickCause_SysNick == 'abort') {
				base_form.findField('MedStaffFact2_id').allowBlank = false;
			}
		} else {
			var maxDays = 15;
			// log(base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code'));
			// проверяем должность врача, для Фельдшер или зубной врач макс. продолжительность 10 дней, а не 15.
			// а также если был ЛВН закрытый в предыдущий день в стационаре.
			if (( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code') && base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code').inlist([115,117])) || this.MaxDaysLimitAfterStac) {
				maxDays = 10;
			}

			// все 3 врача должны быть заполнены если общий период более 15 дней. (refs #6511, #8492) если код нетрудоспособности Входит в перечень.
			// или если выбран дубликат ЛВН (refs #10085)
			if (
				this.EvnStick_IsOriginal == 2 
				|| (
					newSumDate > maxDays
					&& (getRegionNick().inlist(['vologda', 'kareliya']) || this.StickParentClass != 'EvnPS' || !this.isHasDvijeniaInStac24)
					&& (
						this.StickCause_SysNick == 'desease'
						|| this.StickCause_SysNick == 'trauma'
						|| this.StickCause_SysNick == 'accident'
						|| this.StickCause_SysNick == 'protstac'
						|| this.StickCause_SysNick == 'prof'
						|| this.StickCause_SysNick == 'dolsan'
						|| this.StickCause_SysNick == 'uhodnoreb'
						|| this.StickCause_SysNick == 'inoe'
						|| this.StickCause_SysNick == 'uhodreb'
						|| this.StickCause_SysNick == 'rebinv'
					)
				)
			) {
				// для черновика поле скрыто
				if (!base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
					base_form.findField('EvnStickWorkRelease_IsPredVK').setValue(1);
					base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('check', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
				}
				base_form.findField('EvnStickWorkRelease_IsPredVK').disable();
			} else {
				base_form.findField('MedStaffFact2_id').allowBlank = true;
				if (win.action != 'view') {
					base_form.findField('EvnStickWorkRelease_IsPredVK').enable();
				}
			}

			if (!getRegionNick().inlist(['kz','by']) && this.EvnStick_IsOriginal == 2) {
				base_form.findField('MedStaffFact2_id').allowBlank = true;
			}
		}

		// для черновика поля не обязательны и вообще скрыты
		if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue()) {
			base_form.findField('MedStaffFact2_id').allowBlank = true;
		}

		base_form.findField('MedStaffFact2_id').validate();
		base_form.findField('MedStaffFact3_id').validate();
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	refreshEndDateLimit: function() {
		var base_form = this.FormPanel.getForm();
		var minValue = base_form.findField('EvnStickWorkRelease_begDate').getValue();
		var maxValue;
		var win = this;

		if( minValue ) {
			base_form.findField('EvnStickWorkRelease_endDate').setMinValue(minValue);
			maxValue = new Date(minValue);
			maxValue.setDate(maxValue.getDate() + 14);
		}

		if (
			maxValue
			&& !(
				!(win.isELN || win.isFSS) // на бланке
				|| win.StickCause_SysNick == 'pregn'
				|| (win.StickParentClass == 'EvnPS' && win.isHasDvijeniaInStac24 == true)
				|| (win.EvnStick_stacBegDate && minValue && win.EvnStick_stacBegDate.getTime() == minValue.getTime() && win.EvnStick_stacEndDate)
				|| win.EvnStick_IsOriginal == 2
				|| (win.isELN && win.StickWorkType_Code && win.StickWorkType_Code == 2 && win.EvnStick_setDate > minValue)
			)
		) {
			base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(maxValue);
		}
	},
    setPostValue: function() {
		var base_form = this.FormPanel.getForm();
		var _this = this;

		var PostMed_id = base_form.findField('MedStaffFact_id').getFieldValue('PostMed_id');

		if (Ext.isEmpty(PostMed_id)) {
			base_form.findField('Post_id').setValue(null);
		} else {
			base_form.findField('Post_id').setValue(PostMed_id);
		}
    },
	id: 'EvnStickWorkReleaseEditWindow',
	initComponent: function() {
        var _this = this;

		this.FormPanel = new Ext.form.FormPanel({
			//height: 270,
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnStickWorkReleaseEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'accessType' },
				{ name: 'EvnStickBase_id' },
				{ name: 'EvnStickWorkRelease_begDate' },
				{ name: 'EvnStickWorkRelease_endDate' },
				{ name: 'EvnStickWorkRelease_id' },
				{ name: 'LpuSection_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedStaffFact2_id' },
				{ name: 'MedStaffFact3_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Post_id' },
				{ name: 'MedPersonal2_id' },
				{ name: 'MedPersonal3_id' },
				{ name: 'EvnStickWorkRelease_IsPredVK' },
				{ name: 'EvnVK_id' }
			]),
			url: '/?c=Stick&m=saveEvnStickWorkRelease',
			items: [
				{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},
				{
					name: 'EvnStickWorkRelease_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'EvnStickBase_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal2_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'MedPersonal3_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'Override30Day',
					value: 'false',
					xtype: 'hidden'
				},
				{
					fieldLabel: langs('Черновик за другую МО'),
					name: 'EvnStickWorkRelease_IsDraft',
					listeners: {
						'check': function(checkbox, checked) {
							var base_form = _this.FormPanel.getForm();
							// действия над полями..
							if (checked) {
								// показываем поле МО, скрываем отделение и врачей
								base_form.findField('Org_id').showContainer();
								base_form.findField('Org_id').setAllowBlank(false);
								base_form.findField('LpuSection_id').hideContainer();
								base_form.findField('LpuSection_id').clearValue();
								base_form.findField('LpuSection_id').setAllowBlank(true);
								base_form.findField('MedStaffFact_id').hideContainer();
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_id').setAllowBlank(true);
								base_form.findField('Post_id').hideContainer();
								base_form.findField('Post_id').clearValue();
								//base_form.findField('MedPersonal2_id').hideContainer();
								//base_form.findField('MedPersonal2_id').clearValue();
								//base_form.findField('MedPersonal3_id').hideContainer();
								//base_form.findField('MedPersonal3_id').clearValue();
								base_form.findField('MedStaffFact2_id').hideContainer();
								base_form.findField('MedStaffFact2_id').clearValue();
								base_form.findField('MedStaffFact3_id').hideContainer();
								base_form.findField('MedStaffFact3_id').clearValue();
								base_form.findField('EvnStickWorkRelease_IsPredVK').hideContainer();
								base_form.findField('EvnStickWorkRelease_IsPredVK').setValue(false);
								base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('check', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
								base_form.findField('EvnVK_descr').hideContainer();
								base_form.findField('EvnVK_descr').clearValue();
							} else {
								base_form.findField('Org_id').hideContainer();
								base_form.findField('Org_id').setAllowBlank(true);
								base_form.findField('Org_id').clearValue();
								base_form.findField('LpuSection_id').showContainer();
								base_form.findField('LpuSection_id').setAllowBlank(false);
								base_form.findField('MedStaffFact_id').showContainer();
								base_form.findField('MedStaffFact_id').setAllowBlank(false);
								base_form.findField('Post_id').showContainer();
								//base_form.findField('MedPersonal2_id').showContainer();
								//base_form.findField('MedPersonal3_id').showContainer();
								base_form.findField('MedStaffFact2_id').showContainer();
								base_form.findField('MedStaffFact3_id').showContainer();
								base_form.findField('EvnStickWorkRelease_IsPredVK').showContainer();
								base_form.findField('EvnVK_descr').showContainer();
							}

							_this.checkVK();
							_this.syncShadow();
						}
					},
					xtype: 'checkbox'
				},
				{
					fieldLabel: 'Специализированное МО',
					name: 'EvnStickWorkRelease_IsSpecLpu',
					xtype: 'checkbox'
				},
				{
					fieldLabel: langs('МО'),
					valueField: 'Org_id',
					hiddenName: 'Org_id',
					allowBlank: false,
					width: 300,
					xtype: 'sworgcombo',
					onTrigger1Click: function() {
						var combo = this;
						if (combo.disabled) {
							return false;
						}

						var base_form = _this.FormPanel.getForm();

						getWnd('swOrgSearchWindow').show({
							enableOrgType: false,
							object: 'lpu',
							//onlyFromDictionary: true,
							onSelect: function(lpuData) {
								if ( lpuData.Org_id > 0 )
								{
									combo.getStore().load({
										params: {
											OrgType: 'lpu',
											Org_id: lpuData.Org_id
										},
										callback: function()
										{
											combo.setValue(lpuData.Org_id);
											combo.focus(true, 500);
										}
									});
								}
								getWnd('swOrgSearchWindow').hide();
							},
							onClose: function() {combo.focus(true, 200)}
						});
					}
				},
				{
					allowBlank: false,
					fieldLabel: langs('С какого числа'),
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							if ( blockedDateAfterPersonDeath('personpanelid', 'EStWREF_PersonInformationFrame', field, newValue, oldValue) ) {
								return false;
							}
							this.refreshEndDateLimit();

							this.checkVK();
							this.refreshGlobalCombos();
						}.createDelegate(this)
					},
					name: 'EvnStickWorkRelease_begDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					tabIndex: TABINDEX_ESTWREF + 1,
					width: 100,
					xtype: 'swdatefield'
				},
				{
					allowBlank: false,
					fieldLabel: langs('По какое число'),
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							this.checkVK();
							this.refreshGlobalCombos();
						}.createDelegate(this)
					},
					name: 'EvnStickWorkRelease_endDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					tabIndex: TABINDEX_ESTWREF + 2,
					width: 100,
					xtype: 'swdatefield'
				},
				{
					allowBlank: false,
					hiddenName: 'LpuSection_id',
					id: 'EStWREF_LpuSectionCombo',
					fieldLabel: langs('Отделение (Врач 1)'),
					lastQuery: '',
					listWidth: 500,
					linkedElements: [
						'EStWREF_MedPersonalCombo'
					],
					tabIndex: TABINDEX_ESTWREF + 3,
					width: 350,
					xtype: 'swlpusectionglobalcombo'
				},
				{
					allowBlank: false,
					fieldLabel: langs('Врач 1'),
					hiddenName: 'MedStaffFact_id',
					id: 'EStWREF_MedPersonalCombo',
					lastQuery: '',
					listeners: {
						'change': function(field, newValue, oldValue) {
							_this.checkVK();

							var base_form = _this.FormPanel.getForm();

								// проставляем поле должность
							var PostMed_Code = base_form.findField('MedStaffFact_id').getFieldValue('PostMed_Code');
							if ( ! Ext.isEmpty(PostMed_Code) && PostMed_Code.inlist(['59','52','53','54','55','56','57','58','70','20','60','88','89'])){

								// Поле выбора «Должность» включает также дополнительные должности: Терапевт Зубной врач Фельдшер
								base_form.findField('Post_id').getStore().filterBy(function(rec) {
									return ( !Ext.isEmpty(rec.get('PostMed_Code')) && rec.get('PostMed_Code').inlist(['73','115','117', PostMed_Code]));
								});

								base_form.findField('Post_id').setDisabled(false);

								if(!base_form.findField('Post_id').getValue()) {
									_this.setPostValue();
								}

							} else {
								base_form.findField('Post_id').setDisabled(true);
								base_form.findField('Post_id').getStore().reload({callback: function() {
									_this.setPostValue();
								}});
							}
						}
					},
					listWidth: 600,
					parentElementId: 'EStWREF_LpuSectionCombo',
					tabIndex: TABINDEX_ESTWREF + 4,
					width: 600,
					xtype: 'swmedstafffactglobalcombo'
				},
				{
					xtype: 'swpostmedlocalcombo',
					tabIndex: TABINDEX_ESTWREF + 4,
					hiddenName: 'Post_id',
					width: 350,
					lastQuery: '',
					disabled: true,
					fieldLabel: langs('Должность (Врач 1)')
				},
				{
					allowBlank: false,
					fieldLabel: langs('Врач 2'),
					hiddenName: 'MedStaffFact2_id',
					id: 'EStWREF_MedPersonal2Combo',
					lastQuery: '',
					listWidth: 600,
					tabIndex: TABINDEX_ESTWREF + 4,
					width: 600,
					xtype: 'swmedstafffactglobalcombo'
				},
				{
					allowBlank: false,
					fieldLabel: langs('Врач 3'),
					hiddenName: 'MedStaffFact3_id',
					id: 'EStWREF_MedPersonal3Combo',
					lastQuery: '',
					listWidth: 600,
					tabIndex: TABINDEX_ESTWREF + 4,
					width: 600,
					xtype: 'swmedstafffactglobalcombo'
				},
				{
					xtype: 'checkbox',
					height:24,
					hideLabel: false,
					tabIndex: TABINDEX_ESTWREF + 7,
					name: 'EvnStickWorkRelease_IsPredVK',
					listeners: {
						'check': function(checkbox, value) {
							var base_form = _this.FormPanel.getForm();
							if (base_form.findField('EvnStickWorkRelease_IsPredVK').checked) {
								base_form.findField('MedStaffFact3_id').setAllowBlank(false);
							} else {
								base_form.findField('MedStaffFact3_id').setAllowBlank(true);
							}
						}
					},
					id: 'EvnStickWorkRelease_IsPredVK',
					fieldLabel: langs('Председатель ВК'),
					boxLabel: ''
				},
				{
					name: 'EvnVK_id',
					value: '',
					xtype: 'hidden'
				},
				{
					xtype: 'swevnvkcombo',

					// Доступно Пользователям с правами доступа «Супер админ», #128073 а также пользователям АРМ ВК
					disabled: ((isSuperAdmin() || haveArmType('vk')) ? false: true),

					tabIndex: TABINDEX_ESTWREF + 7,
					name: 'EvnVK_descr',
					width: 600,
					id: 'EvnVK_descr',
					fieldLabel: langs('Протокол ВК'),
					onTrigger1Click: function() {
						var base_form = this.FormPanel.getForm();
						var combo = base_form.findField('EvnVK_descr');
						if (combo.disabled) return false;


						var ExpertiseEventType = null;
						var startDate = this.EvnStick_setDate.add(Date.DAY, -14);
						var endDate = this.EvnStick_setDate.add(Date.DAY, 7);

						switch (this.StickCause_SysNick) {
							case 'desease':
								ExpertiseEventType = 1;
								break;
							case 'uhodreb':
								ExpertiseEventType = 2;
								break;
							case 'uhodnoreb':
								ExpertiseEventType = 3;
								break;
							case 'dolsan':
								ExpertiseEventType = 4;
								break;
							case 'pregn':
								ExpertiseEventType = 6;
								break;
							case 'karantin':
								ExpertiseEventType = 9;
								break;
							case 'protstac':
								ExpertiseEventType = 10;
								break;
							default:
								ExpertiseEventType = 11;
								break;
						}
						getWnd('swClinExWorkSearchWindow').show({
							params: {
								startDate: startDate,
								endDate: endDate,
								ExpertiseNameType : 1,
								ExpertiseEventType: ExpertiseEventType,
								Person_FirName : this.StickPerson_Firname,
								Person_SecName : this.StickPerson_Secname,
								Person_SurName : this.StickPerson_Surname,
								Person_BirthDay : this.StickPerson_Birthday
							},
							onHide: function() {
								combo.focus(false);
							},
							onSelect: function(clinExWorkData) {
								var base_form = this.FormPanel.getForm();
								base_form.findField('EvnVK_descr').setValue(langs('№') + clinExWorkData.num + ' ('+clinExWorkData.ExpertiseEventType_Name + ')' );
								base_form.findField('EvnVK_id').setValue(clinExWorkData.EvnVK_id);
								getWnd('swClinExWorkSearchWindow').hide();
							}.createDelegate(this)
						});
					}.createDelegate(this),
					onTrigger2Click: function() {
						var base_form = this.FormPanel.getForm();
						var combo = base_form.findField('EvnVK_descr');
						if (combo.disabled) return false;

						base_form.findField('EvnVK_descr').clearValue();
						base_form.findField('EvnVK_id').setValue(null);
					}.createDelegate(this)
				}
			]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EStWREF_PersonInformationFrame'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('MedStaffFact3_id').disabled ) {
						base_form.findField('MedStaffFact3_id').focus();
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ESTWREF + 8,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( !base_form.findField('EvnStickWorkRelease_begDate').disabled ) {
						base_form.findField('EvnStickWorkRelease_begDate').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ESTWREF + 9,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			]
		});

		sw.Promed.swEvnStickWorkReleaseEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnStickWorkReleaseEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: false,
	refreshGlobalCombos: function() {
		var base_form = this.FormPanel.getForm();
		var ls_params = new Object();
		var msf_params = new Object();
		var msf3_params = new Object();

		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_staff_fact2_id = base_form.findField('MedStaffFact2_id').getValue();
		var med_staff_fact3_id = base_form.findField('MedStaffFact3_id').getValue();
		var beg_date = base_form.findField('EvnStickWorkRelease_begDate').getValue();
		var end_date = base_form.findField('EvnStickWorkRelease_endDate').getValue();

		base_form.findField('LpuSection_id').clearValue();
		base_form.findField('MedStaffFact_id').clearValue();
		base_form.findField('MedStaffFact2_id').clearValue();

		base_form.findField('LpuSection_id').getStore().removeAll();
		base_form.findField('MedStaffFact_id').getStore().removeAll();
		base_form.findField('MedStaffFact2_id').getStore().removeAll();

		if (!Ext.isEmpty(lpu_section_id)) {
			setLpuSectionGlobalStoreFilter({id: lpu_section_id});
			base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		}

		if (!Ext.isEmpty(med_staff_fact_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact_id});
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}
		if (!Ext.isEmpty(med_staff_fact2_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact2_id});
			base_form.findField('MedStaffFact2_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}
		if (!Ext.isEmpty(med_staff_fact3_id)) {
			setMedStaffFactGlobalStoreFilter({id: med_staff_fact3_id});
			base_form.findField('MedStaffFact3_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
		}

		if(this.CurLpuSection_id != 0)
		{
			ls_params.LpuSection_id = this.CurLpuSection_id;
		}
		if(this.CurLpuUnit_id != 0)
		{
			ls_params.LpuUnit_id = this.CurLpuUnit_id;
		}
		if(this.CurLpuBuilding_id != 0)
		{
			ls_params.LpuBuilding_id = this.CurLpuBuilding_id;
		}
		ls_params.dateFrom = Ext.util.Format.date(beg_date, 'd.m.Y');
		ls_params.dateTo = Ext.util.Format.date(end_date, 'd.m.Y');

		msf_params.dateFrom = Ext.util.Format.date(beg_date, 'd.m.Y');
		msf_params.dateTo = Ext.util.Format.date(end_date, 'd.m.Y');

		msf3_params.dateFrom = Ext.util.Format.date(beg_date, 'd.m.Y');
		msf3_params.dateTo = Ext.util.Format.date(end_date, 'd.m.Y');

		if ( this.arrayLpuUnitType.length > 0 ) {
			ls_params.arrayLpuUnitType = this.arrayLpuUnitType;
			msf_params.arrayLpuUnitType = this.arrayLpuUnitType;
		}

		setLpuSectionGlobalStoreFilter(ls_params);
		setMedStaffFactGlobalStoreFilter(msf_params);

		base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore), true);

		msf_params.allowDuplacateMSF = true;
		if(this.IngoreMSFFilter == 0)
			setMedStaffFactGlobalStoreFilter(msf_params);

		msf_params = new Object();
		if(this.CurLpuSection_id != 0)
		{
			msf_params.LpuSection_id = this.CurLpuSection_id;
		}
		if(this.CurLpuUnit_id != 0)
		{
			msf_params.LpuUnit_id = this.CurLpuUnit_id;
		}
		if(this.CurLpuBuilding_id != 0)
		{
			msf_params.LpuBuilding_id = this.CurLpuBuilding_id;
		}
		msf_params.dateFrom = Ext.util.Format.date(beg_date, 'd.m.Y');
		msf_params.dateTo = Ext.util.Format.date(end_date, 'd.m.Y');

		if(this.CurLpuBuilding_id != 0 || this.CurLpuUnit_id != 0 || this.CurLpuSection_id != 0)
		{
			setMedStaffFactGlobalStoreFilter(msf_params);
		}


		base_form.findField('MedStaffFact2_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);
		if (getRegionNick() != 'kz' && !getGlobalOptions().isMedStatUser && this.StickReg == 0) {
			if ( this.userMedStaffFactId ) {
				msf_params.id = this.userMedStaffFactId;
			}
			else if ( this.userMedStaffFactList != null && typeof this.userMedStaffFactList == 'object' && this.userMedStaffFactList.length > 0 ) {
				msf_params.ids = this.userMedStaffFactList;
			}
		}
		if ( this.arrayLpuUnitType.length > 0 ) msf_params.arrayLpuUnitType = this.arrayLpuUnitType;
		setMedStaffFactGlobalStoreFilter(msf_params);
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);
		if (getRegionNick() == 'kareliya') {
			msf3_params.withoutLpuSection = true;
		} else {
			msf3_params.all = true;
		}
		if (getRegionNick() == 'penza') { //#192404
			msf3_params.all = false;
			msf3_params.isAliens = false;
		}
		setMedStaffFactGlobalStoreFilter(msf3_params);
		base_form.findField('MedStaffFact3_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);

		if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
			base_form.findField('LpuSection_id').setValue(lpu_section_id);
			base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
		}

		if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
			base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
			base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), med_staff_fact_id);
		}
		else if ( base_form.findField('MedStaffFact_id').getStore().getCount() == 1 ) {
			base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id'));
			base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id'));
		}
		if (base_form.findField('MedStaffFact2_id').getStore().getById(med_staff_fact2_id)) {
			base_form.findField('MedStaffFact2_id').setValue(med_staff_fact2_id);
			base_form.findField('MedStaffFact2_id').fireEvent('change', base_form.findField('MedStaffFact2_id'), med_staff_fact2_id);
		}
		if (base_form.findField('MedStaffFact3_id').getStore().getById(med_staff_fact3_id)) {
			base_form.findField('MedStaffFact3_id').setValue(med_staff_fact3_id);
			base_form.findField('MedStaffFact3_id').fireEvent('change', base_form.findField('MedStaffFact3_id'), med_staff_fact3_id);
		}
	},
	show: function() {
		sw.Promed.swEvnStickWorkReleaseEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm(),
            _this = this;
		base_form.reset();

		this.action = null;
		this.arrayLpuUnitType = new Array();
		this.callback = Ext.emptyFn;
		this.hideEvnStickWorkReleaseIsDraft = false;
		this.evnStickType = 0;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.disableBegDate = false;
		this.begDate = null;
		this.endDate = null;
		this.maxDate = null;
		this.sumDate = 0;
		this.StickCause_SysNick = '';
		this.EvnStick_IsOriginal = 1;
		this.StickOrder_Code = 0;
		this.StickPerson_Firname = '';
		this.StickPerson_Secname = '';
		this.StickPerson_Surname = '';
		this.StickPerson_Birthday = '';
		this.EvnStick_setDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		this.onHide = Ext.emptyFn;
		this.parentClass = null;
		this.UserLpuSection_id = null;
		this.UserLpuSectionList = new Array();
		this.userMedStaffFactId = null;
		this.userMedStaffFactList = new Array();
		this.MaxDaysLimitAfterStac = false;
		this.Post_id = null;
        this.StickReg = 0;
        this.CurLpuSection_id = 0;
        this.CurLpuUnit_id = 0;
        this.CurLpuBuilding_id = 0;
        this.IngoreMSFFilter = 0;
        this.isTubDiag = false;
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);
		this.Org_id = base_form.findField('Org_id').getValue();

		base_form.findField('EvnStickWorkRelease_IsDraft').fireEvent('check', base_form.findField('EvnStickWorkRelease_IsDraft'), base_form.findField('EvnStickWorkRelease_IsDraft').getValue());
		base_form.findField('EvnStickWorkRelease_IsPredVK').fireEvent('check', base_form.findField('EvnStickWorkRelease_IsPredVK'), base_form.findField('EvnStickWorkRelease_IsPredVK').getValue());
		if (!Ext.isEmpty(base_form.findField('Org_id').getValue())) {
			base_form.findField('Org_id').getStore().load({
				params: {
					OrgType: 'lpu',
					Org_id: base_form.findField('Org_id').getValue()
				},
				callback: function()
				{
					base_form.findField('Org_id').setValue(base_form.findField('Org_id').getValue());
				}
			});
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

        if( arguments[0].StickReg) {
            this.StickReg = arguments[0].StickReg;
        }

        if( arguments[0].CurLpuSection_id) {
            this.CurLpuSection_id = arguments[0].CurLpuSection_id;
        }
        if( arguments[0].CurLpuUnit_id) {
            this.CurLpuUnit_id = arguments[0].CurLpuUnit_id;
        }
        if( arguments[0].CurLpuBuilding_id) {
            this.CurLpuBuilding_id = arguments[0].CurLpuBuilding_id;
        }
        if( arguments[0].IngoreMSFFilter) {
            this.IngoreMSFFilter = arguments[0].IngoreMSFFilter;
        }

		if ( arguments[0].MaxDaysLimitAfterStac ) {
			this.MaxDaysLimitAfterStac = arguments[0].MaxDaysLimitAfterStac;
		}

		if ( arguments[0].hideEvnStickWorkReleaseIsDraft ) {
			this.hideEvnStickWorkReleaseIsDraft = true;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].evnStickType ) {
			this.evnStickType = arguments[0].evnStickType;
		}

		if ( arguments[0].EvnStick_IsOriginal ) {
			this.EvnStick_IsOriginal = arguments[0].EvnStick_IsOriginal;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( !Ext.isEmpty(arguments[0].disableBegDate) ) {
			this.disableBegDate = arguments[0].disableBegDate;
		}
		if ( arguments[0].begDate ) {
			this.begDate = arguments[0].begDate;
		}
		if ( arguments[0].endDate ) {
			this.endDate = arguments[0].endDate;
		}

		if ( arguments[0].maxDate ) {
			this.maxDate = arguments[0].maxDate;
		}

		if ( arguments[0].sumDate ) {
			this.sumDate = arguments[0].sumDate;
		}

		if ( arguments[0].curSumDate ) {
			this.curSumDate = arguments[0].curSumDate;
		} else {
			this.curSumDate = 0;
		}

		if ( arguments[0].StickCause_SysNick ) {
			this.StickCause_SysNick = arguments[0].StickCause_SysNick;
		}

		if ( arguments[0].StickOrder_Code ) {
			this.StickOrder_Code = arguments[0].StickOrder_Code;
		}

		if ( arguments[0].EvnStick_setDate ) {
			this.EvnStick_setDate = arguments[0].EvnStick_setDate;
		}

		if ( arguments[0].StickPerson_Birthday ) {
			this.StickPerson_Birthday = arguments[0].StickPerson_Birthday;
		}

		if ( arguments[0].StickPerson_Firname ) {
			this.StickPerson_Firname = arguments[0].StickPerson_Firname;
		}

		if ( arguments[0].StickPerson_Secname ) {
			this.StickPerson_Secname = arguments[0].StickPerson_Secname;
		}

		if ( arguments[0].StickPerson_Surname ) {
			this.StickPerson_Surname = arguments[0].StickPerson_Surname;
		}

		if ( arguments[0].formParams.Post_id ) {
			_this.Post_id = arguments[0].formParams.Post_id;
		}

		if ( arguments[0].isTubDiag ) {
			this.isTubDiag = true;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].parentClass ) {
			this.parentClass = arguments[0].parentClass;
		}

		if ( arguments[0].EvnStick_stacBegDate ) {
			this.EvnStick_stacBegDate = arguments[0].EvnStick_stacBegDate;
		}

		if ( arguments[0].EvnStick_stacEndDate ) {
			this.EvnStick_stacEndDate = arguments[0].EvnStick_stacEndDate;
		}

		if ( arguments[0].EvnStick_IsOriginal ) {
			this.EvnStick_IsOriginal = arguments[0].EvnStick_IsOriginal;
		}

		if ( arguments[0].isHasDvijeniaInStac24 ) {
			this.isHasDvijeniaInStac24 = arguments[0].isHasDvijeniaInStac24;
		} else {
			this.isHasDvijeniaInStac24 = false;
		}

		if (arguments[0].parentClass ) {
			this.StickParentClass = arguments[0].parentClass;
		}

		if ( arguments[0].CarePerson_id ) {
			this.CarePerson_id = arguments[0].CarePerson_id;
		}

		if ( arguments[0].CarePerson_Age ) {
			this.CarePerson_Age = arguments[0].CarePerson_Age;
		}

		if ( arguments[0].CarePerson_Fio ) {
			this.CarePerson_Fio = arguments[0].CarePerson_Fio;
		}

		if (arguments[0].EvnStick_prid) {
			this.EvnStick_prid = arguments[0].EvnStick_prid;
		} else {
			this.EvnStick_prid = null;
		}

		// признак, что в одном из освобождений от работы указано место работы из МО текущего пользователя #135678
		if( arguments[0].MedStaffFactInUserLpu ) {
			this.MedStaffFactInUserLpu = arguments[0].MedStaffFactInUserLpu;
		}

		// определенный медстафффакт
		if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 ) {
			this.userMedStaffFactId = arguments[0].UserMedStaffFact_id;
		}
		// если в настройках есть medstafffact, то имеем список мест работы
		else if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 ) {
			this.userMedStaffFactList = Ext.globalOptions.globals['medstafffact'];
		}

		// является ли ЛВН электронным
		if ( arguments[0].isELN || arguments[0].isELN === false) {
			this.isELN = arguments[0].isELN;
		}

		// ЛВН из ФСС
		if ( arguments[0].isFSS ) {
			this.isFSS = arguments[0].isFSS;
		} else {
			this.isFSS = false;
		}

		// определенный LpuSection
		if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 ) {
			this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		}
		// если в настройках есть lpusection, то имеем список мест работы
		else if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 ) {
			this.UserLpuSectionList = Ext.globalOptions.globals['lpusection'];
		}

		// тип занятости
		this.StickWorkType_Code = (arguments[0].StickWorkType_Code) ? arguments[0].StickWorkType_Code : null;

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnStickWorkRelease_begDate');
				clearDateAfterPersonDeath('personpanelid', 'EStWREF_PersonInformationFrame', field);
			}
		});

		this.getLoadMask().show();

		base_form.findField('EvnStickWorkRelease_IsDraft').setContainerVisible(this.hideEvnStickWorkReleaseIsDraft == false);
		base_form.findField('EvnStickWorkRelease_IsSpecLpu').setContainerVisible(getRegionNick() == 'kz' && this.isTubDiag == true);

		this.syncShadow();

		base_form.findField('LpuSection_id').getStore().removeAll();
		//base_form.findField('MedPersonal2_id').getStore().removeAll();
		//base_form.findField('MedPersonal3_id').getStore().removeAll();
		base_form.findField('MedStaffFact_id').getStore().removeAll();
		base_form.findField('MedStaffFact2_id').getStore().removeAll();
		base_form.findField('MedStaffFact3_id').getStore().removeAll();
		base_form.findField('Override30Day').setValue('false');

		base_form.findField('EvnStickWorkRelease_begDate').setMinValue(null);
		base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(null);
		base_form.findField('EvnStickWorkRelease_endDate').setMinValue(null);
		base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(null);

		base_form.findField('EvnStickWorkRelease_begDate').enable();

		if (this.parentClass=='EvnPL') {
			this.arrayLpuUnitType = [ '1', '7', '8', '11' ];
		} else if (this.parentClass=='EvnPS') {
			this.arrayLpuUnitType = [ '2', '3', '4', '5' ];
		}

		if (this.isELN) {
			base_form.findField('EvnStickWorkRelease_IsDraft').hideContainer();
		} else {
			base_form.findField('EvnStickWorkRelease_IsDraft').showContainer();
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(this.evnStickType == 3 ? WND_STICK_ESTSRADD : WND_STICK_ESTWRADD);
				this.enableEdit(true);

				this.checkVK();

				var setDateMaxValue = false;// #196519

				// if (this.StickOrder_Code == 2) {
				// 	setDateMaxValue = false;
				// }
				setCurrentDateTime({
					callback: function() {
						// Если максимальная дата задана, значит уже есть выписанные освобождения, отталкиваемся от них при выписке следующих
						if ( this.maxDate ) {
							base_form.findField('EvnStickWorkRelease_begDate').setMinValue(this.maxDate.add(Date.DAY, 1));
							//base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(this.maxDate.add(Date.DAY, 1));
							base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(null);
							base_form.findField('EvnStickWorkRelease_begDate').setValue(this.maxDate.add(Date.DAY, 1));
							base_form.findField('EvnStickWorkRelease_endDate').setMinValue(this.maxDate.add(Date.DAY, 1));
							base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(null);
						}
						if ( this.begDate ) {
							base_form.findField('EvnStickWorkRelease_begDate').setValue(this.begDate);
						}
						if ( this.endDate && (getRegionNick() != 'kz' || (getRegionNick() == 'kz' && this.StickCause_SysNick != 'pregn'))) {
							base_form.findField('EvnStickWorkRelease_endDate').setValue(this.endDate);
						}
						if ( this.disableBegDate ) {
							base_form.findField('EvnStickWorkRelease_begDate').disable();
						}

						base_form.findField('EvnStickWorkRelease_begDate').fireEvent('change', base_form.findField('EvnStickWorkRelease_begDate'), base_form.findField('EvnStickWorkRelease_begDate').getValue());

						this.getLoadMask().hide();

						//base_form.clearInvalid();

						base_form.findField('EvnStickWorkRelease_begDate').focus(true, 250);
					}.createDelegate(this),
					dateField: base_form.findField('EvnStickWorkRelease_begDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: setDateMaxValue,
					addMaxDateDays: 1,
					windowId: this.id
				});

				if(getRegionNick() != 'kz'){
					var MedStaffFactCombo = base_form.findField('MedStaffFact_id');
					MedStaffFactCombo.setValue(this.userMedStaffFactId);
				}
				break;
			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(this.evnStickType == 3 ? WND_STICK_ESTSREDIT : WND_STICK_ESTWREDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(this.evnStickType == 3 ? WND_STICK_ESTSRVIEW : WND_STICK_ESTWRVIEW);
					this.enableEdit(false);
				}

				if ( this.maxDate ) {
					base_form.findField('EvnStickWorkRelease_begDate').setMinValue(this.maxDate.add(Date.DAY, 1));
					base_form.findField('EvnStickWorkRelease_begDate').setMaxValue(null);
					base_form.findField('EvnStickWorkRelease_endDate').setMinValue(this.maxDate.add(Date.DAY, 1));
					base_form.findField('EvnStickWorkRelease_endDate').setMaxValue(null);
				}
				if ( this.disableBegDate ) {
					base_form.findField('EvnStickWorkRelease_begDate').disable();
				}

				var index;
				var lpu_section_id = base_form.findField('LpuSection_id').getValue();
				var med_personal_id = base_form.findField('MedPersonal_id').getValue();
				var med_personal2_id = base_form.findField('MedPersonal2_id').getValue();
				var med_personal3_id = base_form.findField('MedPersonal3_id').getValue();

				var
					MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
					MedStaffFact2_id = base_form.findField('MedStaffFact2_id').getValue(),
					MedStaffFact3_id = base_form.findField('MedStaffFact3_id').getValue();
				//	base_form.findField('MedStaffFact2_id').clearValue();
				//	base_form.findField('MedStaffFact3_id').clearValue();

				// Поля доступны в режиме редактирования если в освобождении указано рабочее место из МО пользователя #135678
				if(getRegionNick() != 'kz' && !(this.action == 'edit' && this.MedStaffFactInUserLpu)) {
					base_form.findField('LpuSection_id').disable();
					base_form.findField('MedStaffFact_id').disable();
				}
				base_form.findField('EvnStickWorkRelease_endDate').fireEvent('change', base_form.findField('EvnStickWorkRelease_endDate'), base_form.findField('EvnStickWorkRelease_endDate').getValue());

				if ( this.action == 'edit' ) {

                    setLpuSectionGlobalStoreFilter({id: lpu_section_id});
					base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

                    setMedStaffFactGlobalStoreFilter({id: MedStaffFact_id});
					base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
					//base_form.findField('MedStaffFact2_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
					//base_form.findField('MedStaffFact3_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

					var store_params = new Object();
                    if(this.CurLpuSection_id != 0)
                        store_params.LpuSection_id = this.CurLpuSection_id;
                    if(this.CurLpuUnit_id != 0)
                        store_params.LpuUnit_id = this.CurLpuUnit_id;
                    if(this.CurLpuBuilding_id != 0)
                        store_params.LpuBuilding_id = this.CurLpuBuilding_id;
                    store_params.dateFrom = Ext.util.Format.date(base_form.findField('EvnStickWorkRelease_begDate').getValue(), 'd.m.Y');
                    store_params.dateTo = Ext.util.Format.date(base_form.findField('EvnStickWorkRelease_endDate').getValue(), 'd.m.Y');
					if ( this.arrayLpuUnitType.length > 0 ) {
						store_params.arrayLpuUnitType = this.arrayLpuUnitType;
					}
                    setLpuSectionGlobalStoreFilter(store_params);
                    setMedStaffFactGlobalStoreFilter(store_params);

					base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore), true);
					/*base_form.findField('MedPersonal2_id').getStore().loadData(getMedPersonalListFromGlobal());
					base_form.findField('MedPersonal3_id').getStore().load({
						params: {
							onDate: Ext.util.Format.date(base_form.findField('EvnStickWorkRelease_begDate').getValue(), 'd.m.Y'),
							loadAdminPersonal: true
						},
						callback: function() {
							base_form.findField('MedPersonal3_id').setValue(med_personal3_id);
						}
					});*/
					base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);
					base_form.findField('MedStaffFact2_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);

					var msf3_params = {};
					msf3_params.dateFrom = Ext.util.Format.date(base_form.findField('EvnStickWorkRelease_begDate').getValue(), 'd.m.Y');
					msf3_params.dateTo = Ext.util.Format.date(base_form.findField('EvnStickWorkRelease_endDate').getValue(), 'd.m.Y');

					if (getRegionNick() == 'kareliya') {
						msf3_params.withoutLpuSection = true;
					} else {
						msf3_params.all = true;
					}

					setMedStaffFactGlobalStoreFilter(msf3_params);
					base_form.findField('MedStaffFact3_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore), true);

					index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
						return (rec.get('MedStaffFact_id') == MedStaffFact_id);
					});
					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
							return (rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id);
						});
					}
					record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
						base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), record.get('MedStaffFact_id'));
					}

					index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
						return (rec.get('MedStaffFact_id') == MedStaffFact2_id);
					});
					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
							return (rec.get('MedPersonal_id') == med_personal2_id);
						});
					}
					record = base_form.findField('MedStaffFact2_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('MedStaffFact2_id').setValue(record.get('MedStaffFact_id'));
						base_form.findField('MedStaffFact2_id').fireEvent('change', base_form.findField('MedStaffFact2_id'), record.get('MedStaffFact_id'));
					}

					index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
						return (rec.get('MedStaffFact_id') == MedStaffFact3_id);
					});
					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
							return (rec.get('MedPersonal_id') == med_personal3_id);
						});
					}
					record = base_form.findField('MedStaffFact3_id').getStore().getAt(index);

					if ( record ) {
						base_form.findField('MedStaffFact3_id').setValue(record.get('MedStaffFact_id'));
						base_form.findField('MedStaffFact3_id').fireEvent('change', base_form.findField('MedStaffFact3_id'), record.get('MedStaffFact_id'));
					}

					index = base_form.findField('LpuSection_id').getStore().findBy(function(rec, id) {
						return (rec.get('LpuSection_id') == lpu_section_id);
					}.createDelegate(this));
					var LSrecord = base_form.findField('LpuSection_id').getStore().getAt(index);
					if ( LSrecord ) {
						base_form.findField('LpuSection_id').setValue(lpu_section_id);
					}

					// если это черновик и за текущую МО, то делаем его не черновиком и дизаблим галочку
					if (base_form.findField('EvnStickWorkRelease_IsDraft').getValue() && base_form.findField('Org_id').getValue() == getGlobalOptions().org_id) {
						base_form.findField('EvnStickWorkRelease_IsDraft').setValue(false);
						base_form.findField('EvnStickWorkRelease_IsDraft').fireEvent('check', base_form.findField('EvnStickWorkRelease_IsDraft'), base_form.findField('EvnStickWorkRelease_IsDraft').getValue());
						base_form.findField('EvnStickWorkRelease_IsDraft').disable();
						base_form.findField('LpuSection_id').enable();
						base_form.findField('MedStaffFact_id').enable();
					}

					if (this.evnStickType == 2) {
						var form_fields = [
							'EvnStickWorkRelease_begDate',
							'EvnStickWorkRelease_endDate',
							'EvnStickWorkRelease_IsPredVK',
							'EvnVK_id',
							'MedStaffFact2_id',
							'MedStaffFact3_id'
							//'MedPersonal2_id',
							//'MedPersonal3_id'
						];
						if (!Ext.isEmpty(getGlobalOptions().medpersonal_id) && getGlobalOptions().medpersonal_id.inlist([med_personal_id,med_personal2_id,med_personal3_id])) {
							for (i = 0; i < form_fields.length; i++ ) {
								base_form.findField(form_fields[i]).enable();
							}
						} else {
							for (i = 0; i < form_fields.length; i++ ) {
								base_form.findField(form_fields[i]).disable();
							}
						}
						if (getGlobalOptions().org_id == this.Org_id) {
							base_form.findField('MedStaffFact3_id').enable();
							base_form.findField('EvnStickWorkRelease_IsPredVK').enable();
						}
					}
					if (this.evnStickType == 3 || this.EvnStick_IsOriginal == 2) {
						base_form.findField('LpuSection_id').enable();
						base_form.findField('MedStaffFact_id').enable();
					}
				}
				else {
					base_form.findField('LpuSection_id').getStore().load({
						callback: function() {
							index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
								return (rec.get('LpuSection_id') == lpu_section_id);
							});

							if ( index >= 0 ) {
								base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: lpu_section_id
						}
					});

					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
								return (rec.get('MedStaffFact_id') == MedStaffFact_id);
							});
							if ( index == -1 ) {
								index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec, id) {
									return (rec.get('LpuSection_id') == lpu_section_id && rec.get('MedPersonal_id') == med_personal_id);
								});
							}

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
								base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							LpuSection_id: lpu_section_id,
							MedPersonal_id: med_personal_id
						}
					});

					base_form.findField('MedStaffFact2_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
								return (rec.get('MedStaffFact_id') == MedStaffFact2_id);
							});
							if ( index == -1 ) {
								index = base_form.findField('MedStaffFact2_id').getStore().findBy(function(rec, id) {
									return (rec.get('MedPersonal_id') == med_personal2_id);
								});
							}
							if ( index >= 0 ) {
								base_form.findField('MedStaffFact2_id').setValue(base_form.findField('MedStaffFact2_id').getStore().getAt(index).get('MedStaffFact_id'));
								base_form.findField('MedStaffFact2_id').fireEvent('change', base_form.findField('MedStaffFact2_id'), base_form.findField('MedStaffFact2_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							MedPersonal_id: med_personal2_id
						}
					});

					base_form.findField('MedStaffFact3_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
								return (rec.get('MedStaffFact_id') == MedStaffFact3_id);
							});
							if ( index == -1 ) {
								index = base_form.findField('MedStaffFact3_id').getStore().findBy(function(rec, id) {
									return (rec.get('MedPersonal_id') == med_personal3_id);
								});
							}

							if ( index >= 0 ) {
								base_form.findField('MedStaffFact3_id').setValue(base_form.findField('MedStaffFact3_id').getStore().getAt(index).get('MedStaffFact_id'));
								base_form.findField('MedStaffFact3_id').fireEvent('change', base_form.findField('MedStaffFact3_id'), base_form.findField('MedStaffFact3_id').getStore().getAt(index).get('MedStaffFact_id'));
							}
						}.createDelegate(this),
						params: {
							MedPersonal_id: med_personal3_id
						}
					});

					/*base_form.findField('MedPersonal2_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedPersonal2_id').getStore().findBy(function(rec) {
								if ( rec.get('MedPersonal_id') == med_personal2_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index >= 0 ) {
								base_form.findField('MedPersonal2_id').setValue(base_form.findField('MedPersonal2_id').getStore().getAt(index).get('MedPersonal_id'));
							}
						}.createDelegate(this),
						params: {
							MedPersonal_id: med_personal2_id
						}
					});

					base_form.findField('MedPersonal3_id').getStore().load({
						callback: function() {
							index = base_form.findField('MedPersonal3_id').getStore().findBy(function(rec) {
								if ( rec.get('MedPersonal_id') == med_personal3_id ) {
									return true;
								}
								else {
									return false;
								}
							});

							if ( index >= 0 ) {
								base_form.findField('MedPersonal3_id').setValue(base_form.findField('MedPersonal3_id').getStore().getAt(index).get('MedPersonal_id'));
							}
						}.createDelegate(this),
						params: {
							MedPersonal_id: med_personal3_id,
							loadAdminPersonal: true
						}
					});*/
				}

                base_form.findField('Post_id').setValue(_this.Post_id);
				this.getLoadMask().hide();

				//base_form.clearInvalid();

				if ( !base_form.findField('EvnStickWorkRelease_begDate').disabled ) {
					base_form.findField('EvnStickWorkRelease_begDate').focus(true, 250);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}

				this.refreshEndDateLimit();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	},
	width: 800
});
