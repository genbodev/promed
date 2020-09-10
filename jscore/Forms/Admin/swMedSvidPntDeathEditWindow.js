/**
 * swMedSvidPntDeathEditWindow - окно редактирования свидетельства о перинатальной смерти.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Salakhov Rustam
 * @version      22.04.2010
 * @comment      Префикс для id компонентов MSPDEF (MedSvidPntDeathEditForm)
 *
 */
/*NO PARSE JSON*/
sw.Promed.swMedSvidPntDeathEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swMedSvidPntDeathEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedSvidPntDeathEditWindow.js',
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	svid_id: null,
	closeAction: 'hide',
	collapsible: true,
	generateNewNumber: function(onlySer) {
		var win = this;

		if (win.isViewMode()) {
			// генерировать новый номер надо только при добавлении
			return false;
		}

		var base_form = this.findById('MedSvidPntDeathEditForm').getForm();

		if (base_form.findField('ReceptType_id').getValue() != 2) {
			onlySer = true;
		}

		var LpuSection_id = this.findById('MSPDEF_LpuSectionCombo').getValue();
		var params = {
			svid_type: 'pntdeath'
		};

		if (win.needLpuSectionForNumGeneration) {
			params.LpuSection_id = LpuSection_id;
		}

		if (!onlySer) {
			params.generateNew = 1;
		}

		if (getRegionNick() == 'ufa' && onlySer && base_form.findField('ReceptType_id').getValue() == 1) {
			params.ReceptType_id = 1;
		}

		// дата выдачи
		if (!Ext.isEmpty(base_form.findField('PntDeathSvid_GiveDate').getValue())) {
			params.onDate = base_form.findField('PntDeathSvid_GiveDate').getValue().format('d.m.Y');
		}

		win.findById(win.id + 'gennewnumber').disable();
		if (base_form.findField('ReceptType_id').getValue() == 2 && (!Ext.isEmpty(LpuSection_id) || win.needLpuSectionForNumGeneration == false)) {
			win.findById(win.id + 'gennewnumber').enable();
		}

		if (Ext.isEmpty(LpuSection_id) && win.needLpuSectionForNumGeneration) {
			// не определяем нумератор, если не задано отделение
			return false;
		}

		// значиемые параметры, от изменения которых зависит нужно ли вызывать заного загрузку
		var xparams = {
			svid_type: params.svid_type,
			onDate: params.onDate?params.onDate:null,
			LpuSection_id: params.LpuSection_id?params.LpuSection_id:null,
			ReceptType_id: base_form.findField('ReceptType_id').getValue()
		};
		var newParamsForNumGeneration = Ext.util.JSON.encode(xparams);
		if (onlySer && win.lastParamsForNumGeneration == newParamsForNumGeneration) {
			// ничего не грузим если параметры не изменились
			return false;
		}
		win.lastParamsForNumGeneration = newParamsForNumGeneration;

		if (getRegionNick() == 'kareliya' && base_form.findField('ReceptType_id').getValue() == 1) {
			// для Карелии в режиме на бланке серию подгружать не надо
			return false;
		}

		win.getLoadMask(lang['poluchenie_serii_nomera_svidetelstva']).show();
		Ext.Ajax.request({ //заполнение номера и серии
			callback: function (options, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && response_obj.Error_Code && response_obj.Error_Code == 'numerator404') {
						if (getRegionNick() == 'ufa') {
							sw.swMsg.alert(lang['oshibka'], lang['ne_zadan_aktivnyiy_numerator_dlya_svidetelstvo_o_perinatalnoy_smerti_obratites_k_administratoru_sistemyi']);
							win.findById(win.id + 'gennewnumber').disable();
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['ne_zadan_aktivnyiy_numerator_dlya_svidetelstvo_o_perinatalnoy_smerti_vvod_svidetelstv_vozmojen_v_rejime_1_na_blanke']);
							base_form.findField('ReceptType_id').setValue(1);
							base_form.findField('ReceptType_id').disable();
							base_form.findField('PntDeathSvid_Ser').setValue('');
							base_form.findField('PntDeathSvid_Num').setValue('');
							base_form.findField('PntDeathSvid_Num').enable();
							base_form.findField('PntDeathSvid_Ser').enable();
							win.findById(win.id + 'gennewnumber').disable();
						}
					} else {
						base_form.findField('PntDeathSvid_Ser').setValue('');
						base_form.findField('PntDeathSvid_Num').setValue('');
						base_form.findField('PntDeathSvid_Ser').setValue(response_obj.ser);
						if (!onlySer) {
							base_form.findField('PntDeathSvid_Num').setValue(response_obj.num);
						}
					}
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_generatsii_serii_i_nomera_svidetelstva_proizoshla_oshibka']);
				}
			},
			params: params,
			url: '/?c=MedSvid&m=getMedSvidSerNum'
		});
	},
	doSave: function (options) {
		if (this.formStatus == 'save' || this.action == 'view') return false;
		this.formStatus = 'save';
		var win = this;
		var base_form = this.findById('MedSvidPntDeathEditForm').getForm();
		var person_frame = this.findById('MSPDEF_PersonInformationFrame');
		var params = new Object();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function () {
					this.formStatus = 'edit';
					this.findById('MedSvidPntDeathEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (person_frame.getFieldValue('Sex_Code') != 2) {
			sw.swMsg.alert(lang['oshibka'], lang['pol_cheloveka_doljen_byit_jenskiy']);
			this.formStatus = 'edit';
			return false;
		}

		if (getRegionNick() == 'ufa' && !options.ignoreMass && base_form.findField('PntDeathSvid_Mass').getValue() > 7000) {
			this.formStatus = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						options.ignoreMass = true;
						this.doSave(options);
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: 'Внимание! Указан вес ребенка более 7 кг. Продолжить сохранение?',
				title: lang['prodoljit_sohranenie']
			});
			return false;
		}

		if(getRegionNick() == 'vologda' && !options.ignoreUnlikelyReason){
			var fieldsDiag = ['Diag_iid', 'Diag_tid'];
			var mkb = ['P','Q','R'];
			var unlikelyReason = [];
			for (var i = 0; i < fieldsDiag.length; i++) {
				var diagField = base_form.findField(fieldsDiag[i]);
				if(!diagField) continue;
				var code = diagField.getFieldValue('Diag_Code');
				if(code && !code.substr(0,1).inlist(mkb)) unlikelyReason.push(diagField.fieldLabel);
			}
			if(unlikelyReason.length > 0){
				this.formStatus = 'edit';
				var msg = (unlikelyReason.length > 1) ? 'В полях' : 'В поле';
				msg += ' ' + unlikelyReason.join(', ');
				msg += ' выбранный код маловероятен. Продолжить сохранение?';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreUnlikelyReason = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: msg,
					title: lang['prodoljit_sohranenie']
				});
				return false;
			}
		}

		if (this.saveMode == 2) {
			// сохраняем нового получателя, остальное не меняется.
			win.getLoadMask(lang['sohranenie_dannyih_o_poluchatele']).show();
			Ext.Ajax.request({ //заполнение номера и серии
				callback: function (options, success, response) {
					win.getLoadMask(lang['sohranenie_dannyih_o_poluchatele']).hide();
					this.formStatus = 'edit';
					var result = Ext.util.JSON.decode(response.responseText);
					if(result && result.success) {
						var svid_grid = Ext.getCmp('MedSvidPntDeathStreamWindowSearchGrid');
						if (svid_grid && svid_grid.ViewGridStore) {
							svid_grid.ViewGridStore.reload();
						}

						var svid_id = base_form.findField('PntDeathSvid_id').getValue();
						var svid_num = base_form.findField('PntDeathSvid_Num').getValue();

						Ext.getCmp('MedSvidPntDeathEditWindow').hide();

						win.printMedSvid(svid_id)
					}else {
						if (result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], result.Error_Msg);
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: {
					PntDeathSvid_id: base_form.findField('PntDeathSvid_id').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					Person_rid: base_form.findField('Person_rid').getValue(),
					PntDeathSvid_PolFio: base_form.findField('PntDeathSvid_PolFio').getValue(),
					PntDeathSvid_RcpDoc: base_form.findField('PntDeathSvid_RcpDoc').getValue(),
					PntDeathSvid_Ser: base_form.findField('PntDeathSvid_Ser').getValue(),
					PntDeathSvid_Num: base_form.findField('PntDeathSvid_Num').getValue(),
					DeathSvidType_id: base_form.findField('DeathSvidType_id').getValue(),
					PntDeathSvid_DeathDate_Date: !Ext.isEmpty(base_form.findField('PntDeathSvid_DeathDate_Date').getValue())?base_form.findField('PntDeathSvid_DeathDate_Date').getValue().format('d.m.Y'):null,
					PntDeathSvid_DeathDateStr: base_form.findField('PntDeathSvid_DeathDateStr').getValue(),
					PntDeathSvid_ChildBirthDT_Date: !Ext.isEmpty(base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue())?base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue().format('d.m.Y'):null,
					PntDeathSvid_BirthDateStr: base_form.findField('PntDeathSvid_BirthDateStr').getValue(),
					PntDeathSvid_Mass: base_form.findField('PntDeathSvid_Mass').getValue(),
					PntDeathSvid_Height: base_form.findField('PntDeathSvid_Height').getValue(),
					DeputyKind_id: base_form.findField('DeputyKind_id').getValue(),
					PntDeathSvid_RcpDate: !Ext.isEmpty(base_form.findField('PntDeathSvid_RcpDate').getValue())?base_form.findField('PntDeathSvid_RcpDate').getValue().format('d.m.Y'):null
				},
				url: '/?c=MedSvid&m=savePntDeathRecipient'
			});

			return true;
		}

		params.Person_id = person_frame.personId;

		params.PntDeathSvid_Ser = base_form.findField('PntDeathSvid_Ser').getValue();
		params.PntDeathSvid_Num = base_form.findField('PntDeathSvid_Num').getValue();
		params.PntDeathPeriod_id = base_form.findField('PntDeathPeriod_id').getValue();
		params.DeathSvidType_id = base_form.findField('DeathSvidType_id').getValue();
		params.PntDeathSvid_predid = base_form.findField('PntDeathSvid_predid').getValue();
		params.PntDeathSvid_IsDuplicate = base_form.findField('PntDeathSvid_IsDuplicate').getValue();
		params.PntDeathSvid_IsLose = base_form.findField('PntDeathSvid_IsLose').getValue();
		params.PntDeathSvid_OldSer = base_form.findField('PntDeathSvid_OldSer').getValue();
		params.PntDeathSvid_OldNum = base_form.findField('PntDeathSvid_OldNum').getValue();
		params.PntDeathSvid_DeathDate = base_form.findField('PntDeathSvid_DeathDate_Date').getValue();
		params.PntDeathSvid_DeathDate = base_form.findField('PntDeathSvid_DeathDate_Time').getValue();
		params.ReceptType_id = base_form.findField('ReceptType_id').getValue();
		params.PntDeathSvid_IsNoPlace = base_form.findField('PntDeathSvid_IsNoPlace').getValue();
		params.PntDeathSvid_ChildFio = base_form.findField('PntDeathSvid_ChildFio').getValue();
		params.PntDeathSvid_ChildBirthDT = base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue();
		params.PntDeathSvid_ChildBirthDT = base_form.findField('PntDeathSvid_ChildBirthDT_Time').getValue();
		params.PntDeathSvid_PlodIndex = base_form.findField('PntDeathSvid_PlodIndex').getValue();
		params.PntDeathSvid_PlodCount = base_form.findField('PntDeathSvid_PlodCount').getValue();
		params.PntDeathSvid_RcpDoc = base_form.findField('PntDeathSvid_RcpDoc').getValue();
		params.PntDeathSvid_RcpDate = base_form.findField('PntDeathSvid_RcpDate').getValue();
		params.PntDeathFamilyStatus_id = base_form.findField('PntDeathFamilyStatus_id').getValue();
		params.DeathEmployment_id = base_form.findField('DeathEmployment_id').getValue();
		params.PntDeathPlace_id = base_form.findField('PntDeathPlace_id').getValue();
		params.PntDeathEducation_id = base_form.findField('PntDeathEducation_id').getValue();
		params.Sex_id = base_form.findField('Sex_id').getValue();
		params.PntDeathSvid_ChildCount = base_form.findField('PntDeathSvid_ChildCount').getValue();
		params.PntDeathSvid_BirthCount = base_form.findField('PntDeathSvid_BirthCount').getValue();
		params.PntDeathGetBirth_id = base_form.findField('PntDeathGetBirth_id').getValue();
		params.PntDeathTime_id = base_form.findField('PntDeathTime_id').getValue();
		params.PntDeathCause_id = base_form.findField('PntDeathCause_id').getValue();
		params.PntDeathSetType_id = base_form.findField('PntDeathSetType_id').getValue();
		params.PntDeathSetCause_id = base_form.findField('PntDeathSetCause_id').getValue();
		params.Diag_iid = base_form.findField('Diag_iid').getValue();
		params.Diag_eid = base_form.findField('Diag_eid').getValue();
		params.Diag_mid = base_form.findField('Diag_mid').getValue();
		params.Diag_tid = base_form.findField('Diag_tid').getValue();
		params.Diag_oid = base_form.findField('Diag_oid').getValue();
		params.PntDeathSvid_Mass = base_form.findField('PntDeathSvid_Mass').getValue();
		params.PntDeathSvid_Height = base_form.findField('PntDeathSvid_Height').getValue();
		params.PntDeathSvid_IsMnogoplod = base_form.findField('PntDeathSvid_IsMnogoplod').getValue();
		params.OrgHeadPost_id = base_form.findField('OrgHeadPost_id').getValue();
		params.Person_hid = base_form.findField('Person_hid').getValue();

		var index = base_form.findField('OrgDep_id').getStore().findBy(function (rec) {
			return (rec.get('Org_id') == base_form.findField('OrgDep_id').getValue());
		});

		if (index >= 0) {
			base_form.findField('Org_id').setValue(base_form.findField('OrgDep_id').getStore().getAt(index).get('Org_pid'));
		}
		else {
			base_form.findField('Org_id').setValue(0);
		}

		// хотя бы один диагноз должен быть заполнен
		if (((params.Diag_iid === '') || (params.Diag_iid === null ))
			&& ((params.Diag_tid === '') || (params.Diag_tid === null ))
			&& ((params.Diag_mid === '') || (params.Diag_mid === null ))
			&& ((params.Diag_eid === '') || (params.Diag_eid === null ))
			&& ((params.Diag_oid === '') || (params.Diag_oid === null ))) {
			sw.swMsg.alert(lang['oshibka'], lang['hotya_byi_odin_iz_diagnozov_doljen_byit_zapolnen']);
			this.formStatus = 'edit';
			return false;
		}

		if (getRegionNick() == 'ekb') {
			// ошибки
			// При сохранении МС о смерти, если («Вид Свидетельства»: «Взамен предварительного» или «Взамен окончательного») И блок «Предыдущее свидетельство пустой, то ошибка: "Не заполнено ранее выданное свидетельство!"
			if (base_form.findField('DeathSvidType_id').getValue() && base_form.findField('DeathSvidType_id').getValue().toString().inlist(['3','4']) && Ext.isEmpty(base_form.findField('PntDeathSvid_OldSer').getValue()) && Ext.isEmpty(base_form.findField('PntDeathSvid_OldNum').getValue()) && Ext.isEmpty(base_form.findField('PntDeathSvid_OldGiveDate').getValue())) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Не заполнено ранее выданное свидетельство!');
				return false;
			}
			// Если период смерти = «Мертворожденный» (PntDeathPeriod_id=1 ) И дата родов меньше или равна 01.01.1900, то ошибка "Год рождения мертвым плодом не может быть <= '1900'!"
			if (base_form.findField('PntDeathPeriod_id').getValue() == 1 && !Ext.isEmpty(base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() <= Date.parseDate('01.01.1900', 'd.m.Y')) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Год рождения мертвым плодом не может быть <= 1900!');
				return false;
			}
			// Если период смерти=«Умер на первой неделе жизни» (PntDeathPeriod_id=2 ) И год родов меньше, чем текущий год, то ошибка "Год рождения ребенка живым не может быть <= '2016'!"\
			var Year = getGlobalOptions().date.substr(6, 4);
			if (base_form.findField('PntDeathPeriod_id').getValue() == 1 && !Ext.isEmpty(base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() <= Date.parseDate('01.01.' + Year, 'd.m.Y')) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Год рождения ребенка живым не может быть не может быть <= ' + Year + '!');
				return false;
			}
			// Если дата родов больше, чем текущая, то ошибка "Дата рождения мертвым плодом не может быть больше текущей даты!"
			if (!Ext.isEmpty(base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() > Date.parseDate(getGlobalOptions().date, 'd.m.Y')) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата рождения мертвым плодом не может быть больше текущей даты!');
				return false;
			}
			// Если дата рождения матери больше даты смерти, то ошибка "Дата рождения матери не может быть больше даты смерти!"
			var Person_Birthday = person_frame.getFieldValue('Person_Birthday');
			if (Person_Birthday && base_form.findField('PntDeathSvid_DeathDate_Date').getValue() && Person_Birthday > base_form.findField('PntDeathSvid_DeathDate_Date').getValue()) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата рождения матери не может быть больше даты смерти!');
				return false;
			}
			// Если дата рождения матери больше, чем дата смерти ребенка, то ошибка: "Дата рождения матери не может быть больше даты смерти!"
			if (Person_Birthday && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() && Person_Birthday > base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата рождения матери не может быть больше даты смерти!');
				return false;
			}
			// Если дата рождения матери больше даты выдачи МС о смерти, то ошибка "Дата рождения матери не может быть больше даты выдачи свидетельства!"
			if (Person_Birthday && base_form.findField('PntDeathSvid_GiveDate').getValue() && Person_Birthday > base_form.findField('PntDeathSvid_GiveDate').getValue()) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата рождения матери не может быть больше даты выдачи свидетельства!');
				return false;
			}
			// Если дата рождения матери больше, чем дата рождения ребенка, то ошибка "Дата рождения матери не может быть >= даты рождения ребенка!"
			if (Person_Birthday && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() && Person_Birthday > base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Дата рождения матери не может быть >= даты рождения ребенка!');
				return false;
			}
			// Если возраст матери меньше 10 лет, то ошибка: "Возраст матери не может быть меньше 10 лет."
			if (Person_Birthday && swGetPersonAge(Person_Birthday) < 10) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Возраст матери не может быть меньше 10 лет');
				return false;
			}
			// Если Масса при рождении не находится в пределах от 500г до 9000г, то ошибка «Масса ребенка должна находиться в пределах от 500г до 9000г»
			if (base_form.findField('PntDeathSvid_Mass').getValue() && !(base_form.findField('PntDeathSvid_Mass').getValue() >= 500 && base_form.findField('PntDeathSvid_Mass').getValue() <= 9000)) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Масса ребенка должна находиться в пределах от 500г до 9000г');
				return false;
			}
			// Если в поле «Который ребенок» значение  превышает 25, то ошибка "Число детей у матери не может быть больше 25."
			if (base_form.findField('PntDeathSvid_ChildCount').getValue() > 25) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Число детей у матери не может быть больше 25');
				return false;
			}
			// Если в поле «Которые роды» значение превышает 25, то ошибка "Число родов у матери не может быть больше 25.
			if (base_form.findField('PntDeathSvid_BirthCount').getValue() > 25) {
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], 'Число родов у матери не может быть больше 25');
				return false;
			}
			if (base_form.findField('PntDeathSvid_Ser').getValue() == '66728') {
				// Если значение серии 66728 и  длина номера меньше 6-ти цифр, то ошибка: "Для серии 66728 длина номера должна быть не меньше 6-ти цифр!"
				if (base_form.findField('PntDeathSvid_Num').getValue().length < 6) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'Для серии 66728 длина номера должна быть не меньше 6-ти цифр!');
					return false;
				}
			} else {
				// Если значение серии не равно 66728 и длина номера меньше 5 цифр, то ошибка: "С такой серией длина номера должна быть не меньше 5-ти цифр!"
				if (base_form.findField('PntDeathSvid_Num').getValue().length < 5) {
					this.formStatus = 'edit';
					sw.swMsg.alert(lang['oshibka'], 'С такой серией длина номера должна быть не меньше 5-ти цифр!');
					return false;
				}
			}

			var error_text = '';
			var death_set_value = base_form.findField('PntDeathSetType_id').getValue();
			var med_staff_fact_did = base_form.findField('MedStaffFact_did').getValue();
			if(med_staff_fact_did && base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did)){
				var post_name = base_form.findField('MedStaffFact_did').getStore().getById(med_staff_fact_did).get('PostMed_Name'); 
				post_name = post_name.toLowerCase();
				var post_error = false;
				switch(death_set_value){
					case 1:
					case '1':
					case 2:
					case '2':
					case 3:
					case '3':
						if(post_name.indexOf('врач') == -1){
							post_error = true;
						}
						break;
					case 4:
					case '4':
						if(post_name.indexOf('патологоанатом') == -1){
							post_error = true;
						}
						break;
					case 5:
					case '5':
						if(post_name.indexOf('судмедэксперт') == -1){
							post_error = true;
						}
						break;
					case 6:
					case '6':
					case 7:
					case '7':
						if((post_name.indexOf('фельдшер') == -1) && (post_name.indexOf('акушерка') == -1)){
							post_error = true;
						}
						break;
				}
				if(post_error){
					error_text = "Несоответствие должности в п.17 и п.18.";
				}
			}

			if(error_text.length > 0){
				this.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], error_text);
				return false;
			}

			// предупреждения
			var warnings = "";
			// При сохранении МС о перинатальной, если дата выдачи МС о смерти больше, чем текущая дата, то показывать предупреждение: «Дата выдачи свидетельства не может быть больше текущей даты!"
			if (base_form.findField('PntDeathSvid_GiveDate').getValue() && base_form.findField('PntDeathSvid_GiveDate').getValue() > Date.parseDate(getGlobalOptions().date, 'd.m.Y')) {
				warnings += "Дата выдачи свидетельства не может быть больше текущей даты!<br>";
			}
			// Если дата смерти не заполнена, то показывать предупреждение: "Дата смерти не может быть пустой!"
			if (Ext.isEmpty(base_form.findField('PntDeathSvid_DeathDate_Date').getValue()) && Ext.isEmpty(base_form.findField('PntDeathSvid_DeathDateStr').getValue())) {
				warnings += "Дата смерти не может быть пустой!<br>";
			}
			// Если разница дат: даты смерти и даты выдачи больше, чем 5 дней, то предупреждение: "Период между датой смерти и датой выдачи свидетельства должен быть меньше 5-ти дней!"
			if (base_form.findField('PntDeathSvid_DeathDate_Date').getValue() && base_form.findField('PntDeathSvid_GiveDate').getValue() && base_form.findField('PntDeathSvid_GiveDate').getValue() > base_form.findField('PntDeathSvid_DeathDate_Date').getValue().add(Date.DAY, 5)) {
				warnings += "Период между датой смерти и датой выдачи свидетельства должен быть меньше 5-ти дней!<br>";
			}
			// Если дата родов больше, чем дата выдачи МС о смерти, то предупреждение «Дата выдачи свидетельства  не может быть раньше даты рождения ребенка!»
			if (base_form.findField('PntDeathSvid_GiveDate').getValue() && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() && base_form.findField('PntDeathSvid_GiveDate').getValue() < base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) {
				warnings += "Дата выдачи свидетельства  не может быть раньше даты рождения ребенка!<br>";
			}
			// Если дата смерти больше, чем дата выдачи свидетельства о МС, то показывать предупреждение: "Дата смерти не может быть больше даты выписки свидетельства!"
			if (base_form.findField('PntDeathSvid_GiveDate').getValue() && base_form.findField('PntDeathSvid_DeathDate_Date').getValue() && base_form.findField('PntDeathSvid_GiveDate').getValue() < base_form.findField('PntDeathSvid_DeathDate_Date').getValue()) {
				warnings += "Дата смерти не может быть больше даты выписки свидетельства!<br>";
			}
			// Если период смерти = «Умер на первой неделе жизни» (PntDeathPeriod_id=2 ) И «Дата родов больше даты смерти, то показывать предупреждение: "Дата рождения ребенка не может быть больше даты смерти!"
			if (base_form.findField('PntDeathPeriod_id').getValue() == 2 && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() && base_form.findField('PntDeathSvid_DeathDate_Date').getValue() && base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue() > base_form.findField('PntDeathSvid_DeathDate_Date').getValue()) {
				warnings += "Дата рождения ребенка не может быть больше даты смерти!<br>";
			}
			// Если Дата рождения матери меньше или равно 01.01.1900, то предупреждение "Год рождения матери не может быть <= '1900'!"
			if (Person_Birthday && Person_Birthday <= Date.parseDate('01.01.1900', 'd.m.Y')) {
				warnings += "Год рождения матери не может быть <= 1900<br>";
			}
			// Если дата родов пустая, то предупреждение «Не указана дата родов»;
			if (Ext.isEmpty(base_form.findField('PntDeathSvid_ChildBirthDT_Date').getValue()) && Ext.isEmpty(base_form.findField('PntDeathSvid_BirthDateStr').getValue())) {
				warnings += "Не указана дата родов<br>";
			}
			// Если рост ребенка не находится в пределах от 20 см до 70 см.", то предупреждение «Рост ребенка не находится в пределах от 20 см до 70 см."»
			if (base_form.findField('PntDeathSvid_Height').getValue() && !(base_form.findField('PntDeathSvid_Height').getValue() >= 20 && base_form.findField('PntDeathSvid_Height').getValue() <= 70)) {
				warnings += "Рост ребенка не находится в пределах от 20 см. до 70 см.<br>";
			}
			// Адреса «Место смерти». Если ни одно из полей «Район» или «Город» не указаны, предупреждение Не заполнен 'Район/город пмж'.
			if (Ext.isEmpty(base_form.findField('DKLSubRGN_id').getValue()) && Ext.isEmpty(base_form.findField('DKLCity_id').getValue())) {
				warnings += "Не заполнен 'Район/город ПМЖ'<br>";
			}
			// Адреса «Место смерти». Если ни одно из полей не заполнено «Город» или «Нас. Пункт», то предупреждение: Не заполнен 'Насел. пункт ПМЖ'.
			if (Ext.isEmpty(base_form.findField('DKLCity_id').getValue()) && Ext.isEmpty(base_form.findField('DKLTown_id').getValue())) {
				warnings += "Не заполнен 'Насел. пункт ПМЖ'.<br>";
			}
			// Если в поле «Смерть наступила» значение «2 Дома» И адрес в поле «Место смерти (мертворождения)» не совпадает с адресом проживания/регистрации матери, то показывать предупреждение «Место смерти не совпадает с местом жительства!»
			if (base_form.findField('PntDeathPlace_id').getValue() == 2 && base_form.findField('DAddress_Address').getValue() != person_frame.getFieldValue('RAddress_Address') && base_form.findField('DAddress_Address').getValue() != person_frame.getFieldValue('PAddress_Address')) {
				warnings += "Место смерти не совпадает с местом жительства!<br>";
			}

			if (
				base_form.findField('Diag_iid').getValue() 
				&& base_form.findField('Diag_mid').getValue() 
				&& base_form.findField('Diag_oid').getValue()
				&& base_form.findField('Diag_oid').getStore().getById(base_form.findField('Diag_oid').getValue())
			) {
				var diag_o_code = base_form.findField('Diag_oid').getStore().getById(base_form.findField('Diag_oid').getValue()).get('Diag_Code');
				if (diag_o_code.substr(0,3) < 'V00' || diag_o_code.substr(0,3) > 'Y98') {
					warnings += "Основная причина смерти - травма, внешняя причина должна быть в интервале V00-Y98.<br>";
				}
			}

			if (!options.ignoreWarnings && warnings.length > 0) {
				this.formStatus = 'edit';
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						if ( buttonId == 'yes' ) {
							options.ignoreWarnings = true;
							this.doSave(options);
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Внимание!<br>' + warnings + 'Продолжить сохранение?',
					title: lang['prodoljit_sohranenie']
				});
				return false;
			}
		}

		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_personal_fio = '';
		var med_personal_id = null;
		var record = null;

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
			base_form.findField('MedPersonal_id').setValue(med_personal_id);
		}
		params.MedPersonal_id = med_personal_id;
		params.MedStaffFact_id = med_staff_fact_id;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение свидетельства..."});
		loadMask.show();

		var errMsg = "";

		if (errMsg == "") {
			base_form.submit({
				failure: function (result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if (action.result) {
						if (action.result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function (result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					var svid_grid = Ext.getCmp('MedSvidPntDeathStreamWindowSearchGrid');
					if (svid_grid && svid_grid.ViewGridStore) {
						svid_grid.ViewGridStore.reload();
					}

					//Так же перезагружаем грид АРМа патологоанатома если он открыт
					var pat_grid = Ext.getCmp('WorkPlacePathoMorphologyGridPanel');
					if (pat_grid && pat_grid.ViewGridStore) {
						pat_grid.ViewGridStore.reload();
					}

					var svid_id = action.result.svid_id;
					var svid_num = base_form.findField('PntDeathSvid_Num').getValue();
					if(win.callback&&typeof win.callback=='function'){
						win.callback(svid_id,svid_num)
					}
					Ext.getCmp('MedSvidPntDeathEditWindow').hide();
					
					win.printMedSvid(svid_id);

				}.createDelegate(this)
			});
		} else {
			sw.swMsg.alert(lang['oshibka'], errMsg);
		}
	},
	draggable: true,
	formStatus: 'edit',
	height: 500,
	id: 'MedSvidPntDeathEditWindow',
	printMedSvid: function (idSvid) {
		var base_form = this.findById('MedSvidPntDeathEditForm').getForm();
		var id_salt = Math.random();
		var win_id = 'print_svid' + Math.floor(id_salt * 10000);
		// if (getRegionNick().inlist(['ufa', 'khak'])) {

		var params = {};
		if (base_form.findField('PntDeathSvid_IsDuplicate').getValue() == 2) {
			params.DeathSvid_IsDuplicate = 2;
		}
		params.svid_id = idSvid;
		params.pnt = true;

		getWnd('swMedSvidDeathPrintWindow').show(params);

		/*}
		else {
			var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + idSvid + '&svid_type=pntdeath', win_id);
		}*/
	},

	initComponent: function () {
		var label_mod_1 = -10; //страница 1, модификатор ширины названий полей
		var label_mod_2 = -22; //страница 2, модификатор ширины названий полей
		var field_mod_1 = -15; //страница 1, модификатор ширины полей
		var field_mod_2 = -58; //страница 2, модификатор ширины полей
		var win = this;
		Ext.apply(this, {
			buttons: [{
				handler: function () {
					this.doSave({
						checkDrugRequest: true,
						copy: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EREF + 60,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
			}, {
				handler: function () {
					var base_form = win.findById('MedSvidPntDeathEditForm').getForm();
					var svid_id = base_form.findField('PntDeathSvid_id').getValue();

					if ( !Ext.isEmpty(svid_id) || this.action == 'view' ) {
						win.printMedSvid(svid_id);
					}
					else {
						this.doSave({
							checkDrugRequest: true,
							copy: false,
							print: true
						});
					}
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EREF + 61,
				text: lang['pechat'],
				tooltip: lang['pechat']
			}, {
				text: '-'
			},
				HelpButton(this),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					tabIndex: TABINDEX_EREF + 62,
					text: BTN_FRMCANCEL,
					tooltip: lang['zakryit_okno']
				}],
			items: [new sw.Promed.PersonInformationPanel({
				button2Callback: function (callback_data) {
					var base_form = this.findById('MedSvidPntDeathEditForm').getForm();

					/*base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					 base_form.findField('Server_id').setValue(callback_data.Server_id);*/

					this.findById('MSPDEF_PersonInformationFrame').load({
						Person_id: callback_data.Person_id,
						Server_id: callback_data.Server_id
					});
				}.createDelegate(this),
				button1OnHide: function () {
					if (this.action == 'view') {
						this.buttons[this.buttons.length - 1].focus();
					} else {
						this.findById('MedSvidPntDeathEditForm').getForm().findField('ReceptType_id').focus(true);
					}
				}.createDelegate(this),
				button2OnHide: function () {
					this.findById('MSPDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function () {
					this.findById('MSPDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function () {
					this.findById('MSPDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function () {
					this.findById('MSPDEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				id: 'MSPDEF_PersonInformationFrame',
				region: 'north'
			}),
				new Ext.form.FormPanel({
					//bodyStyle: 'padding: 0.5em;',
					frame: true,
					labelAlign: 'right',
					labelWidth: 170 + label_mod_1,
					bodyStyle: 'background:#FFFFFF;padding:0px;',
					border: false,
					frame: false,
					layout: 'border',
					id: 'MedSvidPntDeathEditForm',
					items: [new Ext.TabPanel({
						id: 'MedSvidPntDeathEditWindowTab',
						activeTab: 0,
						region: 'center',
						// bodyStyle:'padding:5px;',
						layoutOnTabChange: true,
						defaults: {autoScroll: true, bodyStyle: 'padding:5px;'},
						border: false,
						items: [{
							title: lang['0_dannyie_o_patsiente'],
							layout: 'form',
							labelWidth: 170 + label_mod_1,
							border: false,
							items: [{
								name: 'Person_id',
								value: 0,
								xtype: 'hidden'
							},{
								name: 'Person_cid',
								value: 0,
								xtype: 'hidden'
							},{
								name: 'PntDeathSvid_id',
								xtype: 'hidden'
							}, {
								name: 'PntDeathSvid_IsDuplicate',
								xtype: 'hidden'
							}, {
								name: 'PntDeathSvid_IsLose',
								xtype: 'hidden'
							}, {
								name: 'PntDeathSvid_predid',
								xtype: 'hidden'
							}, {
								name: 'Server_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Org_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'DAddress_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Person_r_FIO',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'MedPersonal_id',
								value: null,
								xtype: 'hidden'
							}, {
								name: 'Lpu_id',
								xtype: 'hidden'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Zip'
							}, {
								xtype: 'hidden',
								name: 'DKLCountry_id'
							}, {
								xtype: 'hidden',
								name: 'DKLRGN_id'
							}, {
								xtype: 'hidden',
								name: 'DKLSubRGN_id'
							}, {
								xtype: 'hidden',
								name: 'DKLCity_id'
							}, {
								xtype: 'hidden',
								name: 'DKLTown_id'
							}, {
								xtype: 'hidden',
								name: 'DKLStreet_id'
							}, {
								xtype: 'hidden',
								name: 'DAddress_House'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Corpus'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Flat'
							}, {
								xtype: 'hidden',
								name: 'DAddress_Address'
							}, { //Тип свидетельства; Серия; Номер;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									width: 330,
									border: false,
									items: [new sw.Promed.SwReceptTypeCombo({
										allowBlank: false,
										fieldLabel: lang['tip_svidetelstva'],
										id: 'SvidType_id',
										listWidth: 200 + field_mod_1,
										anchor: '100%',
										value: 2, //default value
										tabIndex: TABINDEX_EREF + 1,
										validateOnBlur: true,
										listeners: {
											'select': function (combo, record, index) {
												if (record && record.get('ReceptType_id')) {
													win.onChangeReceptType(record.get('ReceptType_id'));
												}
											},
											'expand': function () {
												this.setStoreFilter();
											}
										},
										setStoreFilter: function () {
											this.getStore().clearFilter();
											this.getStore().filterBy(function (rec) {
												return rec.get('ReceptType_Code') != 3;
											});
										}
									})]
								}, { //
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: false,
										disabled: true,
										fieldLabel: lang['seriya'],
										maxLength: 20,
										id: 'PntDeathSvid_Ser',
										name: 'PntDeathSvid_Ser',
										tabIndex: TABINDEX_EREF + 2,
										anchor: '100%',
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										layout: 'column',
										border: false,
										autoHeight: true,
										items: [{
											layout: 'form',
											width: 300,
											border: false,
											items: [{
												allowBlank: false,
												fieldLabel: lang['nomer'],
												maxLength: 20,
												id: 'PntDeathSvid_Num',
												name: 'PntDeathSvid_Num',
												tabIndex: TABINDEX_EREF + 3,
												anchor: '100%',
												value: '', //default value
												xtype: 'textfield'
											}]
										}, {
											layout: 'form',
											border: false,
											style: 'float: none',
											items: [{
												text: '+',
												id: win.id + 'gennewnumber',
												xtype: 'button',
												handler: function() {
													win.generateNewNumber();
												}
											}]
										}]
									}]
								}]
							}, { //Дата выдачи; Вид свидетельства;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['data_vyidachi'],
										format: 'd.m.Y',
										name: 'PntDeathSvid_GiveDate',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										tabIndex: TABINDEX_EREF + 4,
										width: 100,
										value: new Date(), //default value
										xtype: 'swdatefield',
										listeners: {
											'change': function (field, newValue, oldValue) {
												var base_form = this.findById('MedSvidPntDeathEditForm').getForm();

												if (!Ext.isEmpty(base_form.findField('PntDeathSvid_GiveDate').getValue())) {
													if (!win.isViewMode()) {
														// проверяем, есть ли нумераторы действующие на дату выдачи, у которых заполнена структура
														win.getLoadMask(lang['proverka_nalichiya_numeratorov_na_strukture_mo']).show();
														Ext.Ajax.request({
															url: '/?c=Numerator&m=checkNumeratorOnDateWithStructure',
															params: {
																onDate: base_form.findField('PntDeathSvid_GiveDate').getValue().format('d.m.Y'),
																NumeratorObject_SysName: 'PntDeathSvid'
															},
															callback: function (options, success, response) {
																win.getLoadMask().hide();
																if (success && response.responseText != '') {
																	var response_obj = Ext.util.JSON.decode(response.responseText);
																	if (response_obj.NumeratorExist) {
																		win.needLpuSectionForNumGeneration = true;
																	} else {
																		win.needLpuSectionForNumGeneration = false;
																	}
																}

																win.generateNewNumber(true);
															}
														});
													}
												}

												var lpu_section_id = base_form.findField('LpuSection_id').getValue();
												var med_personal_id = base_form.findField('MedPersonal_id').getValue();
												var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
												var pntdeath_svid_rcp_date = base_form.findField('PntDeathSvid_RcpDate').getValue();

												var section_filter_params = {
													// TO-DO: ну это тоже не правильно, надо сделать правильную фильтрацию по нескольким признакам, хотя можно сделать и isPolkaandStac ))
												};
												var medstafffact_filter_params = {
													// TO-DO: ну это тоже не правильно, надо сделать правильную фильтрацию по нескольким признакам, хотя можно сделать и isPolkaandStac ))
													allowDuplacateMSF: true
												};

												if (newValue) {
													section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													if (pntdeath_svid_rcp_date == '' || Ext.util.Format.date(pntdeath_svid_rcp_date, 'd.m.Y') == Ext.util.Format.date(oldValue, 'd.m.Y')) {
														//base_form.findField('PntDeathSvid_RcpDate').setValue(newValue);
													}
												}

												var user_med_staff_fact_id = null; //this.UserMedStaffFact_id;
												var user_lpu_section_id = null; //this.UserLpuSection_id;
												var user_med_staff_facts = (!isSuperAdmin() && !isMedStatUser()) ? getGlobalOptions().medstafffact : null; //this.UserMedStaffFacts;
												var user_lpu_sections = (!isSuperAdmin() && !isMedStatUser()) ? getGlobalOptions().lpusection : null; //this.UserLpuSections;

												// фильтр или на конкретное место работы или на список мест работы
												if (user_med_staff_fact_id && user_lpu_section_id && (this.action == 'add' || this.action == 'edit')) {
													section_filter_params.id = user_lpu_section_id;
													medstafffact_filter_params.id = user_med_staff_fact_id;
												} else if (user_med_staff_facts && user_lpu_sections && (this.action == 'add' || this.action == 'edit')) {
													section_filter_params.ids = user_lpu_sections;
													medstafffact_filter_params.ids = user_med_staff_facts;
												}

												base_form.findField('LpuSection_id').clearValue();
												base_form.findField('MedStaffFact_id').clearValue();

												if (!win.isViewMode()) {
													if ((section_filter_params.id && section_filter_params.id > 0) || (section_filter_params.ids && section_filter_params.ids.length > 0))
														setLpuSectionGlobalStoreFilter(section_filter_params);
													if ((medstafffact_filter_params.id && medstafffact_filter_params.id > 0) || (medstafffact_filter_params.ids && medstafffact_filter_params.ids.length > 0)) {
														setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
													} else {
														medstafffact_filter_params.id = null;
														medstafffact_filter_params.ids = null;
														setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
													}
												} else {
													// если просмотр, то подгружаем всех врачей
													setLpuSectionGlobalStoreFilter();
													setMedStaffFactGlobalStoreFilter();
												}

												base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
												base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

												index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
													if ( !Ext.isEmpty(med_staff_fact_id) ) {
														return (record.get('MedStaffFact_id_id') == med_staff_fact_id);
													}
													else {
														return (record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == med_personal_id);
													}
												});

												if ( index >= 0 ) {
													med_staff_fact_id = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
												}

												if (base_form.findField('LpuSection_id').getStore().getById(lpu_section_id)) {
													base_form.findField('LpuSection_id').setValue(lpu_section_id);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
												}

												if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
													base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
												}

												// если не нашли врача в локальном сторе, грузим с сервера
												if (win.isViewMode() && index < 0) {
													if ( !Ext.isEmpty(lpu_section_id) ) {
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
																Lpu_id: base_form.findField('Lpu_id').getValue(),
																LpuSection_id: lpu_section_id,
																mode: 'combo'
															}
														});
													}

													base_form.findField('MedStaffFact_id').getStore().load({
														callback: function() {
															index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
																if ( !Ext.isEmpty(med_staff_fact_id) ) {
																	return (rec.get('MedStaffFact_id') == med_staff_fact_id);
																}
																else {
																	return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
																}
															});

															if ( index >= 0 ) {
																//base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
															}
														}.createDelegate(this),
														params: {
															ignoreDisableInDocParam: 1,
															mode: 'combo',
															Lpu_id: base_form.findField('Lpu_id').getValue(),
															LpuSection_id: lpu_section_id,
															MedPersonal_id: med_personal_id,
															MedStaffFact_id: med_staff_fact_id
														}
													});
												}

												/*
												 если форма отурыта на добавление и задано отделение и
												 место работы, то устанавливаем их не даем редактировать вообще
												 */
												if (!win.isViewMode() && user_med_staff_fact_id && user_lpu_section_id) {
													base_form.findField('LpuSection_id').setValue(user_lpu_section_id);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), user_lpu_section_id);
													base_form.findField('LpuSection_id').disable();
													base_form.findField('MedStaffFact_id').setValue(user_med_staff_fact_id);
													base_form.findField('MedStaffFact_id').disable();

												} else
												/*
												 если форма открыта на добавление и задан список отделений и
												 мест работы, но он состоит из одного элемета,
												 то устанавливаем значение и не даем редактировать
												 */
												if (!win.isViewMode() && user_med_staff_facts && user_med_staff_facts.length == 1) {
													// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
													base_form.findField('LpuSection_id').setValue(user_lpu_sections[0]);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), user_lpu_sections[0]);
													base_form.findField('LpuSection_id').disable();
													base_form.findField('MedStaffFact_id').setValue(user_med_staff_facts[0]);
													base_form.findField('MedStaffFact_id').disable();
												}
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									width: 360,
									border: false,
									items: [{
										fieldLabel: lang['vid_svidetelstva'],
										comboSubject: 'DeathSvidType',
										allowBlank: false,
										disabled: false,
										tabIndex: TABINDEX_EREF + 5,
										anchor: '100%',
										xtype: 'swcommonsprcombo',
										listeners: {
											'select': function (combo, record, index) {
												if (record.get(combo.valueField)) {
													var dstype = record.get(combo.valueField);
													if (dstype == 3 || dstype == 4) {
														Ext.getCmp('PntDeathSvid_OldSer').enable();
														Ext.getCmp('PntDeathSvid_OldNum').enable();
														Ext.getCmp('PntDeathSvid_OldGiveDate').enable();
													} else {
														Ext.getCmp('PntDeathSvid_OldSer').disable();
														Ext.getCmp('PntDeathSvid_OldNum').disable();
														Ext.getCmp('PntDeathSvid_OldGiveDate').disable();
													}
												}
											}
										}
									}]
								}]
							}, {
								xtype: 'fieldset',
								labelWidth: 180,
								autoHeight: true,
								title: lang['predyiduschee_svidetelstvo'],
								width: 985,
								style: 'margin-left: 5px;',
								items: [{ //Пред. серия; Пред. номер; Пред. дата выдачи;
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										labelWidth: 145,
										border: false,
										items: [{
											allowBlank: true,
											disabled: true,
											fieldLabel: lang['seriya'],
											maxLength: 20,
											name: 'PntDeathSvid_OldSer',
											id: 'PntDeathSvid_OldSer',
											tabIndex: TABINDEX_EREF + 6,
											width: 200 + field_mod_1,
											maskRe: getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? new RegExp("^[0-9]*$") : null,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 135,
										border: false,
										items: [{
											allowBlank: true,
											disabled: true,
											fieldLabel: lang['nomer'],
											maxLength: 20,
											name: 'PntDeathSvid_OldNum',
											id: 'PntDeathSvid_OldNum',
											tabIndex: TABINDEX_EREF + 7,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										labelWidth: 135,
										border: false,
										items: [{
											allowBlank: true,
											disabled: true,
											fieldLabel: lang['data_vyidachi'],
											format: 'd.m.Y',
											name: 'PntDeathSvid_OldGiveDate',
											listeners: {
												'change': function(combo, newValue, oldValue) {

													// выпилено по https://redmine.swan.perm.ru/issues/105724

													//if (getRegionNick() == 'ekb') {
													//	var base_form = win.findById('MedSvidPntDeathEditForm').getForm();
                                                    //
													//	if (!Ext.isEmpty(newValue)) {
													//		base_form.findField('PntDeathSvid_GiveDate').setMinValue(newValue);
													//	} else {
													//		var Year = getGlobalOptions().date.substr(6, 4);
													//		base_form.findField('PntDeathSvid_GiveDate').setMinValue(Date.parseDate('01.01.' + Year, 'd.m.Y'));
													//	}
													//}
												}
											},
											id: 'PntDeathSvid_OldGiveDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: TABINDEX_EREF + 8,
											width: 100,
											value: '', //default value
											xtype: 'swdatefield'
										}]
									}]
								}]
							}, { //Дата, время смерти;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: !getRegionNick().inlist(['ekb', 'vologda']),
										disabled: false,
										fieldLabel: lang['data_vremya_smerti'],
										format: 'd.m.Y',
										name: 'PntDeathSvid_DeathDate_Date',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										tabIndex: TABINDEX_EREF + 10,
										width: 100,
										value: '', //default value
										xtype: 'swdatefield',
										listeners: {
											'select': function (th, dt) {
												/*var birthdt = ;
												 var pntdeathdt = dt;*/
											},
											'change': function (combo, newValue, oldValue) {
												var base_form = this.findById('MedSvidPntDeathEditForm').getForm();

												base_form.findField('Diag_oid').setFilterByDate(newValue);
												base_form.findField('Diag_eid').setFilterByDate(newValue);
												base_form.findField('Diag_mid').setFilterByDate(newValue);
												base_form.findField('Diag_tid').setFilterByDate(newValue);
												base_form.findField('Diag_iid').setFilterByDate(newValue);
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 1,
									items: [{
										allowBlank: getRegionNick() != 'vologda',
										disabled: false,
										labelSeparator: '',
										format: 'H:i',
										name: 'PntDeathSvid_DeathDate_Time',
										//onTriggerClick: Ext.emptyFn,
										plugins: [new Ext.ux.InputTextMask('99:99', true)],
										tabIndex: TABINDEX_EREF + 11,
										validateOnBlur: false,
										width: 60,
										value: '', //default value
										listeners: {
											'keydown': function (inp, e) {
												if (e.getKey() == Ext.EventObject.F4) {
													e.stopEvent();
													inp.onTriggerClick();
												}
											}
										},
										xtype: 'swtimefield'
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 140,
									items: [{
										name: 'PntDeathSvid_DeathDateStr',
										fieldLabel: lang['neutoch_data_smerti'],
										plugins: [new Ext.ux.InputTextMask('PP.PP.PPPP', true)],
										width: 100,
										listeners: {
											'change': function(field, newValue, oldValue) {
												if(getRegionNick() == 'vologda') {
													var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

													if(newValue.indexOf('_') < 0){
														base_form.findField('PntDeathSvid_DeathDate_Date').setAllowBlank(true);
														base_form.findField('PntDeathSvid_DeathDate_Time').setAllowBlank(true);
														base_form.findField('PntDeathSvid_DeathDate_Date').setValue('');
														base_form.findField('PntDeathSvid_DeathDate_Time').setValue('');
													} else {
														base_form.findField('PntDeathSvid_DeathDate_Date').setAllowBlank(false);
														base_form.findField('PntDeathSvid_DeathDate_Time').setAllowBlank(false);
													}
												}
											}
										},
										xtype: 'textfield'
									}]
								}]
							}, {
								name: 'Person_hid',
								xtype: 'hidden'
							}, {
								name: 'OrgHeadPost_id',
								xtype: 'hidden'
							}, {
								allowBlank: false,
								hiddenName: 'LpuSection_id',
								id: 'MSPDEF_LpuSectionCombo',
								changeDisabled: false,
								lastQuery: '',
								listWidth: 650,
								linkedElements: [
									'MSPDEF_MedPersonalCombo'
								],
								listeners: {
									'change': function (combo, newValue, oldValue) {
										if (win.needLpuSectionForNumGeneration) {
											this.generateNewNumber(true);
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EREF + 10,
								width: 500,
								xtype: 'swlpusectionglobalcombo'
							}, {
								allowBlank: false,
								hiddenName: 'MedStaffFact_id',
								id: 'MSPDEF_MedPersonalCombo',
								lastQuery: '',
								listWidth: 650,
								parentElementId: 'MSPDEF_LpuSectionCombo',
								listeners: {
									'change': function (combo, newValue, oldValue) {
										if (win.needLpuSectionForNumGeneration) {
											this.generateNewNumber(true);
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EREF + 9,
								width: 500,
								value: null,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								allowBlank: getRegionNick() == 'kz',
								fieldLabel: lang['rukovoditel'],
								hiddenName: 'OrgHead_id',
								lastQuery: '',
								xtype: 'orgheadcombo',
								width: 500,
								listeners: {
									'select': function (combo, newValue, oldValue) {
										var base_form = this.findById('MedSvidPntDeathEditForm').getForm();

										base_form.findField('Person_hid').setValue(combo.getFieldValue('Person_id'));
										base_form.findField('OrgHeadPost_id').setValue(combo.getFieldValue('OrgHeadPost_id'));
									}.createDelegate(this)
								},
								onLoadStore: function (store) {
									// https://redmine.swan.perm.ru/issues/37688 выключаю фильтр
									/*store.clearFilter();
									 store.filterBy(function(rec){
									 return (rec.get('OrgHeadPost_id').inlist([1,4]));
									 });*/
								}
							}, { //Дата, время родов;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: true,
										disabled: false,
										fieldLabel: lang['data_vremya_rodov'],
										format: 'd.m.Y',
										name: 'PntDeathSvid_ChildBirthDT_Date',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										tabIndex: TABINDEX_EREF + 15,
										width: 100,
										value: '', //default value
										xtype: 'swdatefield',
										listeners: {
											'change': function (combo, newValue, oldValue) {
												blockedDateAfterPersonDeath('personpanelid', 'MSPDEF_PersonInformationFrame', combo, newValue, oldValue);
											}.createDelegate(this)
										}
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 1,
									items: [{
										allowBlank: true,
										disabled: false,
										labelSeparator: '',
										format: 'H:i',
										name: 'PntDeathSvid_ChildBirthDT_Time',
										//onTriggerClick: Ext.emptyFn,
										plugins: [new Ext.ux.InputTextMask('99:99', true)],
										tabIndex: TABINDEX_EREF + 16,
										validateOnBlur: false,
										width: 60,
										value: '', //default value
										listeners: {
											'keydown': function (inp, e) {
												if (e.getKey() == Ext.EventObject.F4) {
													e.stopEvent();
													inp.onTriggerClick();
												}
											}
										},
										xtype: 'swtimefield'
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 140,
									items: [{
										name: 'PntDeathSvid_BirthDateStr',
										fieldLabel: lang['neutoch_data_rodov'],
										plugins: [new Ext.ux.InputTextMask('PP.PP.PPPP', false)],
										width: 100,
										xtype: 'textfield'
									}]
								}]
							}, {
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['period_smerti'],
								comboSubject: 'PntDeathPeriod',
								tabIndex: TABINDEX_EREF + 12,
								width: 250,
								value: 1, //default value
								xtype: 'swcommonsprcombo',
								listeners: {
									'change': function (field, newValue, oldValue) {
										var base_form = this.findById('MedSvidPntDeathEditForm').getForm();
										if (newValue == 1) {
											var birthDate = base_form.findField('PntDeathSvid_ChildBirthDT_Date');
											var birthTime = base_form.findField('PntDeathSvid_ChildBirthDT_Time');
											var deathDate = base_form.findField('PntDeathSvid_DeathDate_Date');
											var deathTime = base_form.findField('PntDeathSvid_DeathDate_Time');

											deathDate.setValue(birthDate.getValue());
											deathTime.setValue(birthTime.getValue());
										}

										win.filterPntDeathTime();
									}.createDelegate(this)
								}
							}, {
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['nastuplenie_smerti'],
								comboSubject: 'PntDeathTime',
								onLoadStore: function() {
									win.filterPntDeathTime();
								},
								maxLength: 100,
								tabIndex: TABINDEX_EREF + 25,
								width: 250,
								value: 1, //default value
								xtype: 'swcommonsprcombo'
							}, {
								xtype: 'fieldset',
								labelWidth: 145,
								autoHeight: true,
								title: lang['svedeniya_o_materi'],
								width: 985,
								style: 'margin-left: 5px;',
								items: [{
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										width: 660,
										border: false,
										items: [{
											allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
											disabled: false,
											fieldLabel: lang['zanyatost'],
											comboSubject: 'DeathEmployment',
											tabIndex: TABINDEX_EREF + 22,
											typeCode: 'int',
											anchor: '100%',
											xtype: 'swcommonsprcombo'
										}]
									}, {
										layout: 'form',
										width: 300,
										labelWidth: 115,
										border: false,
										items: [{
											allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
											disabled: false,
											fieldLabel: lang['obrazovanie'],
											comboSubject: 'PntDeathEducation',
											tabIndex: TABINDEX_EREF + 19,
											anchor: '100%',
											xtype: 'swcommonsprcombo'
										}]
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
										comboSubject: 'PntDeathFamilyStatus',
										fieldLabel: lang['semeynoe_polojenie'],
										hiddenName: 'PntDeathFamilyStatus_id',
										listWidth: 400,
										tabIndex: TABINDEX_EREF + 26,
										width: 350,
										xtype: 'swcommonsprcombo'
									}]
								}, {
									layout: 'column',
									border: false,
									items: [{
										layout: 'form',
										width: 330,
										border: false,
										items: [{
											allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
											disabled: false,
											fieldLabel: lang['kotoryie_rodyi'],
											maxLength: 100,
											name: 'PntDeathSvid_BirthCount',
											id: 'PntDeathSvid_BirthCount',
											tabIndex: TABINDEX_EREF + 28,
											anchor: '100%',
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										width: 330,
										labelWidth: 135,
										border: false,
										items: [{
											allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
											disabled: false,
											fieldLabel: lang['kotoryiy_rebenok'],
											maxLength: 100,
											name: 'PntDeathSvid_ChildCount',
											id: 'PntDeathSvid_ChildCount',
											tabIndex: TABINDEX_EREF + 27,
											anchor: '100%',
											value: '', //default value
											xtype: 'textfield'
										}]
									}]
								}]
							}, {
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['fio_rebenka'],
								maxLength: 100,
								name: 'PntDeathSvid_ChildFio',
								id: 'PntDeathSvid_ChildFio',
								tabIndex: TABINDEX_EREF + 13,
								width: 500,
								value: '', //default value
								xtype: 'textfield'
							}, {
								allowBlank: false,
								disabled: false,
								fieldLabel: lang['smert_nastupila'],
								comboSubject: 'PntDeathPlace',
								tabIndex: TABINDEX_EREF + 17,
								width: 300,
								xtype: 'swcommonsprcombo'
							}, {
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 760,
									border: false,
									items: [new sw.Promed.TripleTriggerField({
										//xtype: 'trigger',
										allowBlank: false,
										name: 'DAddress_AddressText',
										readOnly: true,
										anchor: '100%',
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-equil-trigger',
										trigger3Class: 'x-form-clear-trigger',
										fieldLabel: lang['mesto_smerti_mertvorojdeniya'],
										tabIndex: TABINDEX_EREF + 18,
										enableKeyEvents: true,
										listeners: {
											'keydown': function (inp, e) {
												if (e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
													if (e.F4 == e.getKey())
														inp.onTrigger1Click();
													if (e.F2 == e.getKey())
														inp.onTrigger2Click();
													if (e.DELETE == e.getKey() && e.altKey)
														inp.onTrigger3Click();
													if (e.browserEvent.stopPropagation)
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;
													if (e.browserEvent.preventDefault)
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;
													e.browserEvent.returnValue = false;
													e.returnValue = false;
													if (Ext.isIE) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											},
											'keyup': function (inp, e) {
												if (e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey)) {
													if (e.browserEvent.stopPropagation)
														e.browserEvent.stopPropagation();
													else
														e.browserEvent.cancelBubble = true;
													if (e.browserEvent.preventDefault)
														e.browserEvent.preventDefault();
													else
														e.browserEvent.returnValue = false;
													e.browserEvent.returnValue = false;
													e.returnValue = false;
													if (Ext.isIE) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										onTrigger3Click: function () {
											if (this.disabled) return false;
											var base_form = win.findById('MedSvidPntDeathEditForm').getForm();
											base_form.findField('DAddress_Zip').setValue('');
											base_form.findField('DKLCountry_id').setValue('');
											base_form.findField('DKLRGN_id').setValue('');
											base_form.findField('DKLSubRGN_id').setValue('');
											base_form.findField('DKLCity_id').setValue('');
											base_form.findField('DKLTown_id').setValue('');
											base_form.findField('DKLStreet_id').setValue('');
											base_form.findField('DAddress_House').setValue('');
											base_form.findField('DAddress_Corpus').setValue('');
											base_form.findField('DAddress_Flat').setValue('');
											base_form.findField('DAddress_Address').setValue('');
											base_form.findField('DAddress_AddressText').setValue('');
										},
										onTrigger2Click: function () {
											if (this.disabled) return false;
											var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

											if (base_form.findField('PntDeathPlace_id').getValue() == 2) {
												// если «Смерть наступила» = «Дома», подставлять в поле «Место смерти» адрес регистрации пациента
												win.getLoadMask(lang['poluchenie_adresa_registratsii_patsienta']).show();
												Ext.Ajax.request({
													callback: function (options, success, response) {
														win.getLoadMask().hide();
														if (success && response.responseText != '') {
															var response_obj = Ext.util.JSON.decode(response.responseText);
															if (response_obj.AddressFound) {
																base_form.findField('DAddress_Zip').setValue(response_obj.BAddress_Zip);
																base_form.findField('DKLCountry_id').setValue(response_obj.BKLCountry_id);
																base_form.findField('DKLRGN_id').setValue(response_obj.BKLRGN_id);
																base_form.findField('DKLSubRGN_id').setValue(response_obj.BKLSubRGN_id);
																base_form.findField('DKLCity_id').setValue(response_obj.BKLCity_id);
																base_form.findField('DKLTown_id').setValue(response_obj.BKLTown_id);
																base_form.findField('DKLStreet_id').setValue(response_obj.BKLStreet_id);
																base_form.findField('DAddress_House').setValue(response_obj.BAddress_House);
																base_form.findField('DAddress_Corpus').setValue(response_obj.BAddress_Corpus);
																base_form.findField('DAddress_Flat').setValue(response_obj.BAddress_Flat);
																base_form.findField('DAddress_Address').setValue(response_obj.BAddress_Address);
																base_form.findField('DAddress_AddressText').setValue(response_obj.BAddress_Address);
															}
														}
													},
													params: {
														Person_id: base_form.findField('Person_id').getValue()
													},
													url: '/?c=MedSvid&m=getPacientUAddress'
												});
											} else if (base_form.findField('PntDeathPlace_id').getValue() == 1) {
												// Если одно из значений «В стационаре», «В операционной», «В реанимации», «В приемном» - адрес группы отделений (или подразделения), в котором было КВС пациента с исходом смерть.
												win.getLoadMask(lang['poluchenie_adresa_gruppyi_otdeleniy_v_kotorom_byilo_kvs_patsienta_s_ishodom_smert']).show();
												Ext.Ajax.request({
													callback: function (options, success, response) {
														win.getLoadMask().hide();
														if (success && response.responseText != '') {
															var response_obj = Ext.util.JSON.decode(response.responseText);
															if (response_obj.AddressFound) {
																base_form.findField('DAddress_Zip').setValue(response_obj.BAddress_Zip);
																base_form.findField('DKLCountry_id').setValue(response_obj.BKLCountry_id);
																base_form.findField('DKLRGN_id').setValue(response_obj.BKLRGN_id);
																base_form.findField('DKLSubRGN_id').setValue(response_obj.BKLSubRGN_id);
																base_form.findField('DKLCity_id').setValue(response_obj.BKLCity_id);
																base_form.findField('DKLTown_id').setValue(response_obj.BKLTown_id);
																base_form.findField('DKLStreet_id').setValue(response_obj.BKLStreet_id);
																base_form.findField('DAddress_House').setValue(response_obj.BAddress_House);
																base_form.findField('DAddress_Corpus').setValue(response_obj.BAddress_Corpus);
																base_form.findField('DAddress_Flat').setValue(response_obj.BAddress_Flat);
																base_form.findField('DAddress_Address').setValue(response_obj.BAddress_Address);
																base_form.findField('DAddress_AddressText').setValue(response_obj.BAddress_Address);
															}
														}
													},
													params: {
														Person_id: base_form.findField('Person_id').getValue()
													},
													url: '/?c=MedSvid&m=getPacientDeathAddress'
												});
											}
										},
										onTrigger1Click: function () {
											if (this.disabled) return false;
											var base_form = win.findById('MedSvidPntDeathEditForm').getForm();
											getWnd('swAddressEditWindow').show({
												deathSvid: true,
												fields: {
													Address_ZipEdit: base_form.findField('DAddress_Zip').getValue(),
													KLCountry_idEdit: base_form.findField('DKLCountry_id').getValue(),
													KLRgn_idEdit: base_form.findField('DKLRGN_id').getValue(),
													KLSubRGN_idEdit: base_form.findField('DKLSubRGN_id').getValue(),
													KLCity_idEdit: base_form.findField('DKLCity_id').getValue(),
													KLTown_idEdit: base_form.findField('DKLTown_id').getValue(),
													KLStreet_idEdit: base_form.findField('DKLStreet_id').getValue(),
													Address_HouseEdit: base_form.findField('DAddress_House').getValue(),
													Address_CorpusEdit: base_form.findField('DAddress_Corpus').getValue(),
													Address_FlatEdit: base_form.findField('DAddress_Flat').getValue(),
													Address_AddressEdit: base_form.findField('DAddress_Address').getValue()
												},
												callback: function (values) {
													base_form.findField('DAddress_Zip').setValue(values.Address_ZipEdit);
													base_form.findField('DKLCountry_id').setValue(values.KLCountry_idEdit);
													base_form.findField('DKLRGN_id').setValue(values.KLRgn_idEdit);
													base_form.findField('DKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
													base_form.findField('DKLCity_id').setValue(values.KLCity_idEdit);
													base_form.findField('DKLTown_id').setValue(values.KLTown_idEdit);
													base_form.findField('DKLStreet_id').setValue(values.KLStreet_idEdit);
													base_form.findField('DAddress_House').setValue(values.Address_HouseEdit);
													base_form.findField('DAddress_Corpus').setValue(values.Address_CorpusEdit);
													base_form.findField('DAddress_Flat').setValue(values.Address_FlatEdit);
													base_form.findField('DAddress_Address').setValue(values.Address_AddressEdit);
													base_form.findField('DAddress_AddressText').setValue(values.Address_AddressEdit);
													base_form.findField('DAddress_AddressText').focus(true, 500);
												},
												onClose: function () {
													base_form.findField('DAddress_AddressText').focus(true, 500);
												}
											})
										}
									})]
								}, {
									layout: 'form',
									width: 210,
									style: 'padding-left: 20px;',
									border: false,
									items: [{
										hideLabel: true,
										name: 'PntDeathSvid_IsNoPlace',
										listeners: {
											'check': function() {
												win.checkIsNoPlace();
											}
										},
										boxLabel: lang['neizvestno'],
										tabIndex: TABINDEX_EREF + 14,
										anchor: '100%',
										xtype: 'checkbox'
									}]
								}]
							}, {
								allowBlank: false,
								fieldLabel: lang['pol_rebenka'],
								hiddenName: 'Sex_id',
								tabIndex: TABINDEX_EREF + 14,
								width: 200 + field_mod_1,
								value: 1, //default value
								xtype: 'swpersonsexcombo'
							}, {
								allowBlank: true,
								disabled: false,
								fieldLabel: lang['rodyi_prinyal'],
								comboSubject: 'PntDeathGetBirth',
								maxLength: 100,
								tabIndex: TABINDEX_EREF + 21,
								width: 200 + field_mod_1,
								value: 1, //default value
								xtype: 'swcommonsprcombo'
							}, {
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: true,
										disabled: false,
										maxLength: 100,
										fieldLabel: lang['massa_pri_rojdenii_g'],
										name: 'PntDeathSvid_Mass',
										id: 'PntDeathSvid_Mass',
										tabIndex: TABINDEX_EREF + 23,
										anchor: '100%',
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: true,
										disabled: false,
										maxLength: 100,
										fieldLabel: lang['rost_pri_rojdenii_sm'],
										name: 'PntDeathSvid_Height',
										id: 'PntDeathSvid_Height',
										tabIndex: TABINDEX_EREF + 24,
										anchor: '100%',
										value: '', //default value
										xtype: 'textfield'
									}]
								}]
							}, {
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: true,
										disabled: false,
										fieldLabel: lang['mnogoplodnyie_rodyi'],
										maxLength: 20,
										hiddenName: 'PntDeathSvid_IsMnogoplod',
										tabIndex: TABINDEX_EREF + 29,
										anchor: '100%',
										value: 1, //default value
										xtype: 'swyesnocombo',
										listeners: {
											'select': function (combo, record, index) {
												if (record.get(combo.valueField)) {
													var mprtype = record.get(combo.valueField);
													if (mprtype == 2) {
														Ext.getCmp('PntDeathSvid_PlodIndex').enable();
														Ext.getCmp('PntDeathSvid_PlodCount').enable();
													} else {
														Ext.getCmp('PntDeathSvid_PlodIndex').disable();
														Ext.getCmp('PntDeathSvid_PlodCount').disable();
														Ext.getCmp('PntDeathSvid_PlodIndex').setValue('');
														Ext.getCmp('PntDeathSvid_PlodCount').setValue('');
													}
												}
											}
										}
									}]
								}, {
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: true,
										disabled: true,
										fieldLabel: lang['kotoryiy_po_schetu'],
										maxLength: 20,
										id: 'PntDeathSvid_PlodIndex',
										name: 'PntDeathSvid_PlodIndex',
										tabIndex: TABINDEX_EREF + 30,
										anchor: '100%',
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									width: 330,
									border: false,
									items: [{
										allowBlank: true,
										disabled: true,
										fieldLabel: lang['vsego_plodov'],
										maxLength: 20,
										id: 'PntDeathSvid_PlodCount',
										name: 'PntDeathSvid_PlodCount',
										tabIndex: TABINDEX_EREF + 31,
										anchor: '100%',
										value: '', //default value
										xtype: 'textfield'
									}]
								}]
							}, {
								allowBlank: false,
								disabled: false,
								fieldLabel: lang['smert_proizoshla'],
								comboSubject: 'PntDeathCause',
								tabIndex: TABINDEX_EREF + 26,
								width: 300,
								xtype: 'swcommonsprcombo'
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								title: lang['akt_o_mertvorojdenii'],
								width: 985,
								style: 'margin-left: 5px;',
								items: [{
									layout: 'column',
									border: false,
									//labelWidth: 135 + label_mod_2,
									items: [{
										layout: 'form',
										border: false,
										labelWidth: 200 + label_mod_2,
										items: [{
											allowBlank: true,
											fieldLabel: lang['nomer_dokumenta'],
											name: 'PntDeathSvid_ActNumber',
											tabIndex: TABINDEX_EREF + 32,
											width: 200 + field_mod_2,
											maxLength: 200,
											value: '', //default value
											xtype: 'textfield'
										}]
									}, {
										layout: 'form',
										border: false,
										labelWidth: 135 + label_mod_2,
										items: [{
											allowBlank: true,
											fieldLabel: lang['data_zapisi_akta'],
											format: 'd.m.Y',
											name: 'PntDeathSvid_ActDT',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: TABINDEX_EREF + 33,
											width: 100,
											value: '', //default value
											xtype: 'swdatefield'
										}]
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 200 + label_mod_2,
									items: [{
										allowBlank: true,
										tabIndex: TABINDEX_EREF + 34,
										hiddenName: 'OrgDep_id',
										fieldLabel: lang['naimenovanie_organa_zags'],
										width: 780,
										xtype: 'sworgcombo',
										listeners: {
											'change': function () {
												//
											},
											keydown: function (inp, e) {
												if (e.getKey() == e.DELETE || e.getKey() == e.F4) {
													e.stopEvent();
													if (e.browserEvent.stopPropagation) {
														e.browserEvent.stopPropagation();
													}
													else {
														e.browserEvent.cancelBubble = true;
													}
													if (e.browserEvent.preventDefault) {
														e.browserEvent.preventDefault();
													}
													else {
														e.browserEvent.returnValue = false;
													}
													e.returnValue = false;

													if (Ext.isIE) {
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													switch (e.getKey()) {
														case e.DELETE:
															inp.clearValue();
															inp.ownerCt.ownerCt.findField('OrgDep_id').setRawValue(null);
															break;
														case e.F4:
															inp.onTrigger1Click();
															break;
													}
												}
											}
										},
										onTrigger1Click: function () {
											var combo = this;
											getWnd('swOrgSearchWindow').show({
												object: 'dep',
												onSelect: function (orgData) {
													if (!Ext.isEmpty(orgData.Org_id)) {
														combo.getStore().load({
															params: {
																OrgType: 'dep',
																Org_id: orgData.Org_id
															},
															callback: function () {
																combo.setValue(orgData.Org_id);
																combo.focus(true, 500);
																combo.fireEvent('change', combo);
															}
														});
													}
													getWnd('swOrgSearchWindow').hide();
												},
												onClose: function () {
													combo.focus(true, 200)
												}
											});
										}
									}]
								}, {
									layout: 'form',
									border: false,
									maxLength: 200,
									labelWidth: 200 + label_mod_2,
									items: [{
										name: 'PntDeathSvid_ZagsFIO',
										fieldLabel: lang['fio_rabotnika_organa_zags'],
										tabIndex: TABINDEX_EREF + 35,
										width: 780,
										xtype: 'textfield'
									}]
								}]
							}
							]
						}, {
							layout: 'form',
							title: lang['1_zaklyuchenie'],
							labelWidth: 240,
							items: [{
								allowBlank: true,
								fieldLabel: lang['prichinyi_smerti'],
								comboSubject: 'PntDeathSvidType',
								hiddenName: 'PntDeathSvidType_id',
								width: 500,
								tabIndex: TABINDEX_EREF + 36,
								xtype: 'swcommonsprcombo'
							}, {
								allowBlank: !getRegionNick().inlist( ['msk', 'vologda'] ),
								fieldLabel: lang['osnovnoe_zabolevanie_rebenka'],
								baseFilterFn: function(rec){
									if (getRegionNick() == 'ekb') {
										var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
										return (
											(Diag_Code.substr(0,3) >= 'P00' && Diag_Code.substr(0,3) <= 'P96') ||
											(Diag_Code.substr(0,3) >= 'Q00' && Diag_Code.substr(0,3) <= 'Q99') ||
											(Diag_Code.substr(0,3) >= 'E00' && Diag_Code.substr(0,3) <= 'E90') ||
											(Diag_Code.substr(0,3) >= 'S00' && Diag_Code.substr(0,3) <= 'T98') ||
											(Diag_Code.substr(0,3) >= 'C00' && Diag_Code.substr(0,3) <= 'D48') ||
											(Diag_Code.substr(0,3) == 'A33')
										);
									} else {
										return true;
									}
								},
								hiddenName: 'Diag_iid',
								width: 500,
								tabIndex: TABINDEX_EREF + 36,
								xtype: 'swdiagcombo'
							}, {
								allowBlank: true,
								fieldLabel: lang['drugie_zabolevaniya_rebenka'],
								baseFilterFn: function(rec){
									if (getRegionNick() == 'ekb') {
										var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
										return (
											(Diag_Code.substr(0,3) >= 'P00' && Diag_Code.substr(0,3) <= 'P96') ||
											(Diag_Code.substr(0,3) >= 'Q00' && Diag_Code.substr(0,3) <= 'Q99') ||
											(Diag_Code.substr(0,3) >= 'E00' && Diag_Code.substr(0,3) <= 'E90') ||
											(Diag_Code.substr(0,3) >= 'S00' && Diag_Code.substr(0,3) <= 'T98') ||
											(Diag_Code.substr(0,3) >= 'C00' && Diag_Code.substr(0,3) <= 'D48') ||
											(Diag_Code.substr(0,3) == 'A33')
										);
									} else {
										return true;
									}
								},
								hiddenName: 'Diag_tid',
								width: 500,
								tabIndex: TABINDEX_EREF + 37,
								xtype: 'swdiagcombo'
							}, {
								allowBlank: true,
								fieldLabel: lang['osnovnoe_zabolevanie_materi'],
								onChange: function() {
									var combo = this;
									if (getRegionNick() == 'ekb') {
										var base_form = win.findById('MedSvidPntDeathEditForm').getForm();
										if (combo.getValue()) {
											base_form.findField('Diag_eid').enable();
										} else {
											base_form.findField('Diag_eid').disable();
											base_form.findField('Diag_eid').clearValue();
										}
									}
								},
								baseFilterFn: function(rec){
									if (getRegionNick() == 'ekb') {
										var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
										return (Diag_Code.search(new RegExp("^P0[0-4]", "i")) >= 0);
									} else {
										return true;
									}
								},
								hiddenName: 'Diag_mid',
								width: 500,
								tabIndex: TABINDEX_EREF + 38,
								xtype: 'swdiagcombo'
							}, {
								allowBlank: true,
								fieldLabel: lang['drugie_zabolevaniya_materi'],
								baseFilterFn: function(rec){
									if (getRegionNick() == 'ekb') {
										var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
										return (Diag_Code.search(new RegExp("^P0[0-4]", "i")) >= 0);
									} else {
										return true;
									}
								},
								hiddenName: 'Diag_eid',
								width: 500,
								tabIndex: TABINDEX_EREF + 39,
								xtype: 'swdiagcombo'
							}, {
								allowBlank: true,
								fieldLabel: lang['drugie_obstoyatelstva'],
								baseFilterFn: function(rec){
									if (getRegionNick() == 'ekb') {
										var Diag_Code = rec.attributes ? rec.attributes.Diag_Code : rec.get('Diag_Code');
										return (Diag_Code.substr(0,3) >= 'V00' && Diag_Code.substr(0,3) <= 'Z99');
									} else {
										return true;
									}
								},
								hiddenName: 'Diag_oid',
								width: 500,
								tabIndex: TABINDEX_EREF + 40,
								xtype: 'swdiagcombo'
							}, {
								allowBlank: false,
								disabled: false,
								listeners: {
									'change': function (field, newValue, oldValue) {
										win.filterPntDeathSetCause();
									}
								},
								fieldLabel: lang['prichina_smerti_ustanovlena'],
								comboSubject: 'PntDeathSetType',
								tabIndex: TABINDEX_EREF + 41,
								width: 400,
								xtype: 'swcommonsprcombo'
							}, {
								allowBlank: (getRegionNick() != 'ekb'),
								hidden: (getRegionNick() != 'ekb'),
								hiddenName: 'LpuSection_did',
								id: 'MSDEF_DeathLpuSectionCombo',
								changeDisabled: false,
								lastQuery: '',
								listWidth: 650,
								linkedElements: [
									'MSDEF_DeathMedPersonalCombo'
								],
								width: 500 + field_mod_2,
								xtype: 'swlpusectionglobalcombo'
							}, {
								allowBlank: (getRegionNick() != 'ekb'),
								hidden: (getRegionNick() != 'ekb'),
								fieldLabel: 'Сотрудник, установивший причину смерти',
								hiddenName: 'MedStaffFact_did',
								id: 'MSDEF_DeathMedPersonalCombo',
								lastQuery: '',
								listWidth: 650,
								parentElementId: 'MSDEF_DeathLpuSectionCombo',
								width: 500 + field_mod_2,
								value: null,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								allowBlank: false,
								disabled: false,
								fieldLabel: lang['na_osnovanii'],
								comboSubject: 'PntDeathSetCause',
								onLoadStore: function() {
									win.filterPntDeathSetCause();
								},
								tabIndex: TABINDEX_EREF + 42,
								width: 300,
								xtype: 'swcommonsprcombo'
							}, {
								xtype: 'fieldset',
								autoHeight: true,
								title: lang['poluchatel'],
								width: 985,
								labelWidth: 220,
								style: 'margin-left: 5px;',
								items: [{
									editable: false,
									fieldLabel: lang['fio'],
									hiddenName: 'Person_rid',
									tabIndex: TABINDEX_EREF + 50,
									width: 500,
									xtype: 'swpersoncombo',
									onTrigger1Click: function () {
										var combo = this;

										if ( combo.disabled ) return false;
										if ( win.action == 'view' ) return false;

										var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

										getWnd('swPersonSearchWindow').show({
											onSelect: function (personData) {
												if (personData.Person_id > 0) {
													combo.getStore().loadData([{
														Person_id: personData.Person_id,
														Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
													}]);
													combo.setValue(personData.Person_id);
													combo.collapse();
													combo.focus(true, 500);
													combo.fireEvent('change', combo);
													base_form.findField('PntDeathSvid_PolFio').disable();

													// Тянем данные документа
													var loadMask = new Ext.LoadMask(win.getEl(), {msg: lang['poluchenie_dannyih_dokumenta']});
													loadMask.show();

													Ext.Ajax.request({
														callback: function (options, success, response) {
															loadMask.hide();

															if ( success && response.responseText != '' ) {
																var
																	documentData = new Array(),
																	documentSerNum = '',
																	response_obj = Ext.util.JSON.decode(response.responseText);

																if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
																	if ( !Ext.isEmpty(response_obj[0].DocumentType_Name) ) {
																		documentData.push(response_obj[0].DocumentType_Name);
																	}

																	if ( !Ext.isEmpty(response_obj[0].Document_Ser) ) {
																		documentSerNum = response_obj[0].Document_Ser;
																	}

																	if ( !Ext.isEmpty(response_obj[0].Document_Num) ) {
																		documentSerNum = documentSerNum + lang['№'] + response_obj[0].Document_Num;
																	}

																	if ( !Ext.isEmpty(documentSerNum) ) {
																		documentData.push(documentSerNum);
																	}

																	if ( !Ext.isEmpty(response_obj[0].OrgDep_Name) ) {
																		documentData.push(lang['vyidan'] + response_obj[0].OrgDep_Name);
																	}

																	if ( !Ext.isEmpty(response_obj[0].Document_begDate) ) {
																		documentData.push(lang['data_vyidachi'] + response_obj[0].Document_begDate);
																	}

																	base_form.findField('PntDeathSvid_RcpDoc').setValue(documentData.join(', '));
																}
															}
														},
														failure: function() {
															loadMask.hide();
															sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_dannyih_dokumenta']);
														},
														params: {
															mode: 'Document',
															Person_id: personData.Person_id
														},
														url: '/?c=Common&m=loadPersonData'
													});
												}
												getWnd('swPersonSearchWindow').hide();
											},
											onClose: function () {
												combo.focus(true, 500)
											}
										});
									},
									onTrigger2Click: function () {
										var combo = this;

										if ( combo.disabled ) return false;
										if ( win.action == 'view' ) return false;

										var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

										combo.clearValue();
										combo.getStore().removeAll();

										base_form.findField('PntDeathSvid_PolFio').enable();
									},
									enableKeyEvents: true,
									listeners: {
										'change': function (combo) {
										},
										'keydown': function (inp, e) {
											if (e.F4 == e.getKey()) {
												if (e.browserEvent.stopPropagation)
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;
												if (e.browserEvent.preventDefault)
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;
												e.browserEvent.returnValue = false;
												e.returnValue = false;
												if (Ext.isIE) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}
												inp.onTrigger1Click();
												return false;
											}
										},
										'keyup': function (inp, e) {
											if (e.F4 == e.getKey()) {
												if (e.browserEvent.stopPropagation)
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;
												if (e.browserEvent.preventDefault)
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;
												e.browserEvent.returnValue = false;
												e.returnValue = false;
												if (Ext.isIE) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}
												return false;
											}
										}
									}
								}, {
									allowBlank: true,
									disabled: false,
									fieldLabel: lang['fio_ruchnoy_vvod'],
									listeners: {
										'change': function(field, newValue, oldValue) {
											var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

											if ( !Ext.isEmpty(newValue) ) {
												base_form.findField('Person_rid').disable();
											}
											else {
												base_form.findField('Person_rid').enable();
											}
										}
									},
									maxLength: 100,
									name: 'PntDeathSvid_PolFio',
									tabIndex: TABINDEX_EREF + 54,
									width: 500,
									value: '', //default value
									xtype: 'textfield'
								}, {
									allowBlank: true,
									disabled: false,
									fieldLabel: lang['dokument_seriya_nomer_kem_vyidan'],
									maxLength: 100,
									name: 'PntDeathSvid_RcpDoc',
									tabIndex: TABINDEX_EREF + 51,
									width: 500,
									value: '', //default value
									xtype: 'textfield'
								}, {
									allowBlank: true,
									disabled: false,
									fieldLabel: lang['otnoshenie_k_rebenku'],
									hiddenName: 'DeputyKind_id',
									tabIndex: TABINDEX_EREF + 52,
									width: 200,
									listeners: {
										'render': function (combo) {
											combo.getStore().load({
												params: {where: "where DeputyKind_Code = 1 or DeputyKind_Code = 2"},
												callback: function () {
													combo.setValue(combo.getValue());
												}
											});
										}
									},
									store: new Ext.db.AdapterStore({
										autoLoad: false,
										dbFile: 'Promed.db',
										fields: [
											{name: 'DeputyKind_Name', mapping: 'DeputyKind_Name'},
											{name: 'DeputyKind_Code', mapping: 'DeputyKind_Code'},
											{name: 'DeputyKind_id', mapping: 'DeputyKind_id'}
										],
										key: 'DeputyKind_id',
										sortInfo: {field: 'DeputyKind_Code'},
										tableName: 'DeputyKind'
									}),
									xtype: 'swdeputykindcombo'
								}, {
									allowBlank: true,
									disabled: false,
									fieldLabel: lang['data_polucheniya_svid-va'],
									format: 'd.m.Y',
									name: 'PntDeathSvid_RcpDate',
									plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
									tabIndex: TABINDEX_EREF + 53,
									width: 100,
									value: '', //default value
									xtype: 'swdatefield'
								}]
							}, {
								allowBlank: false,
								disabled: false,
								fieldLabel: lang['zapisano_so_slov_materi'],
								hiddenName: 'PntDeathSvid_IsFromMother',
								tabIndex: TABINDEX_EREF + 54,
								width: 140 + field_mod_2,
								value: 1, //default value
								xtype: 'swyesnocombo'
							}
							]
						}]
					})
					],
					keys: [{
						fn: function (inp, e) {
							e.stopEvent();

							if (e.browserEvent.stopPropagation)
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if (e.browserEvent.preventDefault)
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE) {
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							if (e.getKey() == Ext.EventObject.F6) {
								Ext.getCmp('MSPDEF_PersonInformationFrame').panelButtonClick(1);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F10) {
								Ext.getCmp('MSPDEF_PersonInformationFrame').panelButtonClick(2);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F11) {
								Ext.getCmp('MSPDEF_PersonInformationFrame').panelButtonClick(3);
								return false;
							}

							if (e.getKey() == Ext.EventObject.F12) {
								if (e.CtrlKey == true) {
									Ext.getCmp('MSPDEF_PersonInformationFrame').panelButtonClick(5);
								} else {
									Ext.getCmp('MSPDEF_PersonInformationFrame').panelButtonClick(4);
								}
								return false;
							}
						},
						key: [Ext.EventObject.F6, Ext.EventObject.F10, Ext.EventObject.F11, Ext.EventObject.F12],
						scope: this,
						stopEvent: true
					}, {
						alt: true,
						fn: function (inp, e) {
							switch (e.getKey()) {
								case Ext.EventObject.C:
									if (this.action != 'view') {
										this.doSave(false);
									}
									break;

								case Ext.EventObject.J:
									this.hide();
									break;
							}
						},
						key: [Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J],
						scope: this,
						stopEvent: true
					}],
					labelAlign: 'right',
					labelWidth: 130 + label_mod_1,
					reader: new Ext.data.JsonReader({
							id: 'PntDeathSvid_id'
						}, [
							{mapping: 'PntDeathSvid_id', name: 'PntDeathSvid_id', type: 'int'},
							{mapping: 'Server_id', name: 'Server_id', type: 'int'},
							{mapping: 'Person_id', name: 'Person_id', type: 'int'},
							{mapping: 'Person_cid', name: 'Person_cid', type: 'int'},
							{mapping: 'Person_rid', name: 'Person_rid', type: 'int'},
							{mapping: 'Person_r_FIO', name: 'Person_r_FIO', type: 'string'},
							{mapping: 'PntDeathSvid_PolFio', name: 'PntDeathSvid_PolFio', type: 'string'},
							{mapping: 'PntDeathSvid_BirthDateStr', name: 'PntDeathSvid_BirthDateStr', type: 'string'},
							{mapping: 'PntDeathSvid_DeathDateStr', name: 'PntDeathSvid_DeathDateStr', type: 'string'},
							{mapping: 'PntDeathSvid_Ser', name: 'PntDeathSvid_Ser', type: 'string'},
							{mapping: 'PntDeathSvid_Num', name: 'PntDeathSvid_Num', type: 'string'},
							{mapping: 'PntDeathPeriod_id', name: 'PntDeathPeriod_id', type: 'int'},
							{mapping: 'DeathSvidType_id', name: 'DeathSvidType_id', type: 'int'},
							{mapping: 'PntDeathSvid_IsDuplicate', name: 'PntDeathSvid_IsDuplicate', type: 'int'},
							{mapping: 'PntDeathSvid_IsNoPlace', name: 'PntDeathSvid_IsNoPlace', type: 'int'},
							{mapping: 'PntDeathSvid_IsLose', name: 'PntDeathSvid_IsLose', type: 'int'},
							{mapping: 'PntDeathSvid_OldSer', name: 'PntDeathSvid_OldSer', type: 'string'},
							{mapping: 'PntDeathSvid_OldNum', name: 'PntDeathSvid_OldNum', type: 'string'},
							{mapping: 'MedPersonal_id', name: 'MedPersonal_id', type: 'int'},
							{mapping: 'MedStaffFact_id', name: 'MedStaffFact_id', type: 'int'},
							{
								mapping: 'PntDeathSvid_DeathDate_Date',
								name: 'PntDeathSvid_DeathDate_Date',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{
								mapping: 'PntDeathSvid_DeathDate_Time',
								name: 'PntDeathSvid_DeathDate_Time',
								type: 'string'
							},
							{mapping: 'ReceptType_id', name: 'ReceptType_id', type: 'int'},
							{mapping: 'PntDeathSvid_ChildFio', name: 'PntDeathSvid_ChildFio', type: 'string'},
							{
								mapping: 'PntDeathSvid_ChildBirthDT_Date',
								name: 'PntDeathSvid_ChildBirthDT_Date',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{
								mapping: 'PntDeathSvid_ChildBirthDT_Time',
								name: 'PntDeathSvid_ChildBirthDT_Time',
								type: 'string'
							},
							{mapping: 'PntDeathSvid_PlodIndex', name: 'PntDeathSvid_PlodIndex', type: 'string'},
							{mapping: 'PntDeathSvid_PlodCount', name: 'PntDeathSvid_PlodCount', type: 'string'},
							{mapping: 'PntDeathSvid_RcpDoc', name: 'PntDeathSvid_RcpDoc', type: 'string'},
							{
								mapping: 'PntDeathSvid_RcpDate',
								name: 'PntDeathSvid_RcpDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'PntDeathFamilyStatus_id', name: 'PntDeathFamilyStatus_id', type:'int'},
							{mapping: 'DeathEmployment_id', name: 'DeathEmployment_id', type: 'int'},
							{mapping: 'PntDeathPlace_id', name: 'PntDeathPlace_id', type: 'int'},
							{mapping: 'PntDeathEducation_id', name: 'PntDeathEducation_id', type: 'int'},
							{mapping: 'Sex_id', name: 'Sex_id', type: 'int'},
							{mapping: 'PntDeathSvid_ChildCount', name: 'PntDeathSvid_ChildCount', type: 'string'},
							{mapping: 'PntDeathSvid_BirthCount', name: 'PntDeathSvid_BirthCount', type: 'string'},
							{mapping: 'PntDeathGetBirth_id', name: 'PntDeathGetBirth_id', type: 'int'},
							{mapping: 'PntDeathTime_id', name: 'PntDeathTime_id', type: 'int'},
							{mapping: 'PntDeathCause_id', name: 'PntDeathCause_id', type: 'int'},
							{mapping: 'PntDeathSetType_id', name: 'PntDeathSetType_id', type: 'int'},
							{mapping: 'PntDeathSetCause_id', name: 'PntDeathSetCause_id', type: 'int'},
							{mapping: 'MedStaffFact_did', name: 'MedStaffFact_did', type: 'int'},
							{mapping: 'Diag_iid', name: 'Diag_iid', type: 'int'},
							{mapping: 'Diag_eid', name: 'Diag_eid', type: 'int'},
							{mapping: 'Diag_mid', name: 'Diag_mid', type: 'int'},
							{mapping: 'Diag_tid', name: 'Diag_tid', type: 'int'},
							{mapping: 'Diag_oid', name: 'Diag_oid', type: 'int'},
							{mapping: 'PntDeathSvid_Mass', name: 'PntDeathSvid_Mass', type: 'string'},
							{mapping: 'PntDeathSvid_Height', name: 'PntDeathSvid_Height', type: 'string'},
							{mapping: 'PntDeathSvid_IsMnogoplod', name: 'PntDeathSvid_IsMnogoplod', type: 'int'},
							{
								mapping: 'PntDeathSvid_GiveDate',
								name: 'PntDeathSvid_GiveDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{
								mapping: 'PntDeathSvid_OldGiveDate',
								name: 'PntDeathSvid_OldGiveDate',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'DAddress_Zip', name: 'DAddress_Zip', type: 'string'},
							{mapping: 'DKLCountry_id', name: 'DKLCountry_id', type: 'string'},
							{mapping: 'DKLRGN_id', name: 'DKLRGN_id', type: 'string'},
							{mapping: 'DKLSubRGN_id', name: 'DKLSubRGN_id', type: 'string'},
							{mapping: 'DKLCity_id', name: 'DKLCity_id', type: 'string'},
							{mapping: 'DKLTown_id', name: 'DKLTown_id', type: 'string'},
							{mapping: 'DKLStreet_id', name: 'DKLStreet_id', type: 'string'},
							{mapping: 'DAddress_House', name: 'DAddress_House', type: 'string'},
							{mapping: 'DAddress_Corpus', name: 'DAddress_Corpus', type: 'string'},
							{mapping: 'DAddress_Flat', name: 'DAddress_Flat', type: 'string'},
							{mapping: 'DAddress_Address', name: 'DAddress_Address', type: 'string'},
							{mapping: 'DAddress_AddressText', name: 'DAddress_AddressText', type: 'string'},
							{mapping: 'MedStaffFact_id', name: 'MedStaffFact_id', type: 'int'},
							{mapping: 'LpuSection_id', name: 'LpuSection_id', type: 'int'},
							{mapping: 'DeputyKind_id', name: 'DeputyKind_id', type: 'int'},
							{mapping: 'PntDeathSvid_ActNumber', name: 'PntDeathSvid_ActNumber', type: 'string'},
							{
								mapping: 'PntDeathSvid_ActDT',
								name: 'PntDeathSvid_ActDT',
								type: 'date',
								dateFormat: 'd.m.Y'
							},
							{mapping: 'Org_id', name: 'Org_id', type: 'int'},
							{mapping: 'OrgDep_id', name: 'OrgDep_id', type: 'int'},
							{mapping: 'PntDeathSvid_ZagsFIO', name: 'PntDeathSvid_ZagsFIO', type: 'string'},
							{mapping: 'PntDeathSvid_IsFromMother', name: 'PntDeathSvid_IsFromMother', type: 'int'},
							{mapping: 'OrgHead_id', name: 'OrgHead_id', type: 'int'},
							{mapping: 'OrgHeadPost_id', name: 'OrgHeadPost_id', type: 'int'},
							{mapping: 'Person_hid', name: 'Person_hid', type: 'int'},
							{mapping: 'PntDeathSvidType_id', name: 'PntDeathSvidType_id', type: 'int'}
						]
					),
					region: 'center',
					url: '/?c=MedSvid&m=saveMedSvidPntDeath'
				})]
		});

		var mp_combo = Ext.getCmp('MSPDEF_MedPersonalCombo');
		var ls_combo = Ext.getCmp('MSPDEF_LpuSectionCombo');
		mp_combo.getStore().addListener('datachanged', function (store) {
			if (store.getCount() == 1) {
				var mp_id = store.getAt(0).data.MedStaffFact_id;
				mp_combo.setValue(mp_id);
			}
		});
		ls_combo.addListener('change', function (combo, newValue, oldValue) {
			if (!(typeof combo.linkedElements == 'object') || combo.linkedElements.length == 0 || combo.linkedElementsDisabled == true) {
				return true;
			}

			var altValue;

			if (combo.valueFieldAdd) {
				var r = combo.getStore().getById(newValue);

				if (r) {
					altValue = r.get(combo.valueFieldAdd);
				}
			}

			for (var i = 0; i < combo.linkedElements.length; i++) {
				var linked_element = Ext.getCmp(combo.linkedElements[i]);

				if (!linked_element) {
					return true;
				}

				var linked_element_value = linked_element.getValue();

				if (newValue > 0) {
					linked_element.clearValue();
					linked_element.setBaseFilter(function (record, id) {
						if (record.get(combo.valueField) == newValue || (altValue && record.get(combo.valueField) == altValue)) {
							return true;
						}
						else {
							return false;
						}
					}.createDelegate(combo), combo);

					if (linked_element_value && linked_element.valueField) {
						var index = linked_element.getStore().findBy(function (record) {
							if (record.get(combo.valueField) == linked_element_value) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(combo));

						var record = linked_element.getStore().getAt(index);

						if (linked_element.getStore().getCount() == 1)
							record = linked_element.getStore().getAt(0);

						if (record) {
							linked_element.setValue(linked_element_value);
							linked_element.fireEvent('change', linked_element, linked_element_value, null);
						} else {
							linked_element.clearValue();
							linked_element.fireEvent('change', linked_element, null);
						}
					}
				}
				else {
					linked_element.clearBaseFilter();
					linked_element.getStore().clearFilter();
					linked_element.fireEvent('change', linked_element, null);
				}
			}
		});

		sw.Promed.swMedSvidPntDeathEditWindow.superclass.initComponent.apply(this, arguments);
	},
	checkIsNoPlace: function() {
		var base_form = this.findById('MedSvidPntDeathEditForm').getForm();
		if (base_form.findField('PntDeathSvid_IsNoPlace').checked) {
			base_form.findField('DAddress_Zip').setValue('');
			base_form.findField('DKLCountry_id').setValue('');
			base_form.findField('DKLRGN_id').setValue('');
			base_form.findField('DKLSubRGN_id').setValue('');
			base_form.findField('DKLCity_id').setValue('');
			base_form.findField('DKLTown_id').setValue('');
			base_form.findField('DKLStreet_id').setValue('');
			base_form.findField('DAddress_House').setValue('');
			base_form.findField('DAddress_Corpus').setValue('');
			base_form.findField('DAddress_Flat').setValue('');
			base_form.findField('DAddress_Address').setValue('');
			base_form.findField('DAddress_AddressText').setValue('');
			base_form.findField('DAddress_AddressText').disable();
			base_form.findField('DAddress_AddressText').setAllowBlank(true);
		} else {
			base_form.findField('DAddress_AddressText').enable();
			base_form.findField('DAddress_AddressText').setAllowBlank(false);
		}
	},
	layout: 'border',
	maximizable: true,
	minHeight: 500,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	clearValues: function () {
		var base_form = this.findById('MedSvidPntDeathEditForm').getForm();
		base_form.findField('Person_id').setValue(null);
		base_form.findField('Server_id').setValue(null);
		base_form.findField('Person_rid').setValue(null);
		base_form.findField('Person_r_FIO').setValue(null);
		base_form.findField('PntDeathSvid_Ser').setValue(null);
		base_form.findField('PntDeathSvid_Num').setValue(null);
		base_form.findField('PntDeathPeriod_id').setValue(1);
		base_form.findField('DeathSvidType_id').setValue(null);
		base_form.findField('PntDeathSvid_OldSer').setValue(null);
		base_form.findField('PntDeathSvid_OldNum').setValue(null);
		//base_form.findField('MedPersonal_id').setValue(getGlobalOptions().medpersonal_id ? getGlobalOptions().medpersonal_id : null); 
		base_form.findField('PntDeathSvid_DeathDate_Date').setValue(null);
		base_form.findField('PntDeathSvid_DeathDate_Time').setValue(null);
		base_form.findField('ReceptType_id').setValue(2);
		base_form.findField('PntDeathSvid_IsNoPlace').setValue(null);
		base_form.findField('PntDeathSvid_ChildFio').setValue(null);
		base_form.findField('PntDeathSvid_ChildBirthDT_Date').setValue(null);
		base_form.findField('PntDeathSvid_ChildBirthDT_Time').setValue(null);
		base_form.findField('PntDeathSvid_PlodIndex').setValue(null);
		base_form.findField('PntDeathSvid_PlodCount').setValue(null);
		base_form.findField('PntDeathSvid_RcpDoc').setValue(null);
		base_form.findField('PntDeathSvid_RcpDate').setValue(null);
		base_form.findField('PntDeathFamilyStatus_id').clearValue();
		base_form.findField('DeathEmployment_id').setValue(null);
		base_form.findField('PntDeathPlace_id').setValue(null);
		base_form.findField('PntDeathEducation_id').setValue(null);
		base_form.findField('Sex_id').setValue(null);
		base_form.findField('PntDeathSvid_ChildCount').setValue(null);
		base_form.findField('PntDeathSvid_BirthCount').setValue(null);
		base_form.findField('PntDeathGetBirth_id').setValue(null);
		base_form.findField('PntDeathTime_id').setValue(1);
		base_form.findField('PntDeathCause_id').setValue(null);
		base_form.findField('PntDeathSetType_id').setValue(null);
		base_form.findField('PntDeathSetCause_id').setValue(null);
		base_form.findField('Diag_iid').setValue(null);
		base_form.findField('Diag_eid').setValue(null);
		base_form.findField('Diag_mid').setValue(null);
		base_form.findField('Diag_mid').onChange();
		base_form.findField('Diag_tid').setValue(null);
		base_form.findField('Diag_oid').setValue(null);
		base_form.findField('PntDeathSvid_Mass').setValue(null);
		base_form.findField('PntDeathSvid_Height').setValue(null);
		base_form.findField('PntDeathSvid_IsMnogoplod').setValue(1);
		base_form.findField('PntDeathSvid_GiveDate').setValue(new Date());
		base_form.findField('PntDeathSvid_OldGiveDate').setValue(null);
		base_form.findField('PntDeathSvid_OldGiveDate').fireEvent('change', base_form.findField('PntDeathSvid_OldGiveDate'), base_form.findField('PntDeathSvid_OldGiveDate').getValue());
		base_form.findField('DAddress_Zip').setValue(null);
		base_form.findField('DKLCountry_id').setValue(null);
		base_form.findField('DKLRGN_id').setValue(null);
		base_form.findField('DKLSubRGN_id').setValue(null);
		base_form.findField('DKLCity_id').setValue(null);
		base_form.findField('DKLTown_id').setValue(null);
		base_form.findField('DKLStreet_id').setValue(null);
		base_form.findField('DAddress_House').setValue(null);
		base_form.findField('DAddress_Corpus').setValue(null);
		base_form.findField('DAddress_Flat').setValue(null);
		base_form.findField('DAddress_Address').setValue(null);
		base_form.findField('DAddress_AddressText').setValue(null);
		base_form.findField('LpuSection_id').setValue(null);
		base_form.findField('MedStaffFact_id').setValue(null);
		base_form.findField('OrgHead_id').setValue(null);
		base_form.findField('DeputyKind_id').setValue(null);
		base_form.findField('PntDeathSvid_ActNumber').setValue(null);
		base_form.findField('PntDeathSvid_ActDT').setValue(null);
		base_form.findField('Org_id').setValue(null);
		base_form.findField('OrgDep_id').setValue(null);
		base_form.findField('PntDeathSvid_ZagsFIO').setValue(null);
		base_form.findField('PntDeathSvid_IsFromMother').setValue(null);

		Ext.getCmp('PntDeathSvid_OldSer').disable();
		Ext.getCmp('PntDeathSvid_OldNum').disable();
		Ext.getCmp('PntDeathSvid_OldGiveDate').disable();
		Ext.getCmp('PntDeathSvid_PlodIndex').disable();
		Ext.getCmp('PntDeathSvid_PlodCount').disable();
	},
	onChangeReceptType: function(rectype) {
		var win = this;
		var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

		base_form.findField('PntDeathSvid_Ser').setValue('');
		base_form.findField('PntDeathSvid_Num').setValue('');

		if (rectype == 1) {
			base_form.findField('PntDeathSvid_Num').enable();
			if (getRegionNick() == 'ufa') {
				base_form.findField('PntDeathSvid_Ser').disable();
			} else {
				base_form.findField('PntDeathSvid_Ser').enable();
			}
			win.generateNewNumber(true);
		} else if(rectype == 2){
			base_form.findField('PntDeathSvid_Ser').disable();
			base_form.findField('PntDeathSvid_Num').disable();
			win.generateNewNumber(true);
		}
	},
	isViewMode: function() {
		if (this.action == 'add' || (this.action == 'edit' && this.modeNewSvid > 0)) {
			return false;
		}

		return true;
	},
	filterPntDeathTime: function() {
		if (getRegionNick() == 'ekb') {
			var win = this;
			var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

			if (base_form.findField('PntDeathPeriod_id').getValue() == 1) {
				base_form.findField('PntDeathTime_id').lastQuery = '';
				base_form.findField('PntDeathTime_id').getStore().filterBy(function (rec) {
					if (rec.get('PntDeathTime_id') == 3) {
						return false;
					} else {
						return true;
					}
				});

				if (base_form.findField('PntDeathTime_id').getValue() == 3) {
					base_form.findField('PntDeathTime_id').clearValue();
				}
			} else {
				base_form.findField('PntDeathTime_id').getStore().clearFilter();
			}
		}
	},
	filterPntDeathSetCause: function() {
		if (getRegionNick() == 'ekb') {
			var win = this;
			var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

			if (base_form.findField('PntDeathSetType_id').getValue() == 4 || base_form.findField('PntDeathSetType_id').getValue() == 5) {
				base_form.findField('PntDeathSetCause_id').lastQuery = '';
				base_form.findField('PntDeathSetCause_id').getStore().filterBy(function (rec) {
					if (rec.get('PntDeathSetCause_id') != 4) {
						return false;
					} else {
						return true;
					}
				});

				if (base_form.findField('PntDeathSetCause_id').getValue() != 4) {
					base_form.findField('PntDeathSetCause_id').clearValue();
				}
			} else {
				base_form.findField('PntDeathSetCause_id').lastQuery = '';
				base_form.findField('PntDeathSetCause_id').getStore().filterBy(function (rec) {
					if (rec.get('PntDeathSetCause_id') == 4) {
						return false;
					} else {
						return true;
					}
				});

				if (base_form.findField('PntDeathSetCause_id').getValue() == 4) {
					base_form.findField('PntDeathSetCause_id').clearValue();
				}
			}
		}
	},
	show: function () {
		sw.Promed.swMedSvidPntDeathEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		win.needLpuSectionForNumGeneration = true;
		win.lastParamsForNumGeneration = null;

		var person_id = 0;
		var server_id = 0;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		var form = this.findById('MedSvidPntDeathEditForm');
		var pers_form = this.findById('MSPDEF_PersonInformationFrame');
		var base_form = form.getForm();

		if (getRegionNick() == 'ekb') {
			base_form.findField('Diag_iid').tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<tpl if="this.shouldShowHeader(values.Diag_Code)">' +
				'<div style="padding: 12px 4px 4px; font-weight: bold; border-bottom: 1px solid #ddd; color: #3764a0;">{[this.showHeader(values.Diag_Code)]}</div>' +
				'</tpl>' +
				'<div class="x-combo-list-item"><table style="border: 0;"><td style="width: 45px;"><font color="red">{Diag_Code}</font></td><td><h3>{Diag_Name}</h3></td></tr></table></div>',
				'</tpl>', {
				shouldShowHeader: function(Diag_Code) {
					return this.currentKey != this.showHeader(Diag_Code);;
				},
				showHeader: function(Diag_Code) {
					var currentKey = '';

					if (Diag_Code && Diag_Code.substr(0,3) >= 'P00' && Diag_Code.substr(0,3) <= 'P96') {
						currentKey = 'Маловероятные причины смерти';
					}

					this.currentKey = currentKey;
					return currentKey;
				}
			});

			base_form.findField('Diag_tid').tpl = new Ext.XTemplate(
				'<tpl for=".">',
				'<tpl if="this.shouldShowHeader(values.Diag_Code)">' +
				'<div style="padding: 12px 4px 4px; font-weight: bold; border-bottom: 1px solid #ddd; color: #3764a0;">{[this.showHeader(values.Diag_Code)]}</div>' +
				'</tpl>' +
				'<div class="x-combo-list-item"><table style="border: 0;"><td style="width: 45px;"><font color="red">{Diag_Code}</font></td><td><h3>{Diag_Name}</h3></td></tr></table></div>',
				'</tpl>', {
				shouldShowHeader: function(Diag_Code) {
					return this.currentKey != this.showHeader(Diag_Code);
				},
				showHeader: function(Diag_Code) {
					var currentKey = '';

					if (Diag_Code && Diag_Code.substr(0,3) >= 'P00' && Diag_Code.substr(0,3) <= 'P96') {
						currentKey = 'Маловероятные причины смерти';
					}

					this.currentKey = currentKey;
					return currentKey;
				}
			});
		}

		this.action = 'edit';
		this.buttons[1].hide();
		if (arguments && arguments[0].action) {
			this.action = arguments[0].action;
		}

		this.modeNewSvid = 0;
		if (arguments && arguments[0].modeNewSvid) {
			this.modeNewSvid = arguments[0].modeNewSvid;
		}
		this.saveMode = 1;

		if (arguments && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments && arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		var title = lang['svidetelstvo_o_perinatalnoy_smerti'];
		switch (this.action) {
			case 'add':
				title += lang['_dobavlenie'];
				break;
			case 'edit':
				title += lang['_redaktirovanie'];
				break;
			case 'view':
				title += lang['_prosmotr'];
				break;
		}
		this.setTitle(title);

		base_form.reset();
		this.enableEdit(this.action != 'view');

		this.restore();
		this.center();
		this.maximize();
		loadMask.show();
		this.findById('MedSvidPntDeathEditWindowTab').setActiveTab(1);
		this.findById('MedSvidPntDeathEditWindowTab').setActiveTab(0);

		if (this.action == 'add') this.clearValues();

		base_form.findField('PntDeathSvid_DeathDate_Date').filterDate = null;

		if (getRegionNick() == 'ekb') {
			var Year = getGlobalOptions().date.substr(6, 4);

			//выпилено по по https://redmine.swan.perm.ru/issues/105724
			//base_form.findField('PntDeathSvid_GiveDate').setMinValue(Date.parseDate('01.01.' + Year, 'd.m.Y'));
			//base_form.findField('PntDeathSvid_ChildBirthDT_Date').setMinValue(Date.parseDate('01.01.' + Year, 'd.m.Y'));

			base_form.findField('PntDeathSvid_GiveDate').setMaxValue(Date.parseDate('31.12.' + Year, 'd.m.Y'));
			base_form.findField('PntDeathSvid_ChildBirthDT_Date').setMaxValue(Date.parseDate(getGlobalOptions().date, 'd.m.Y'));

			setLpuSectionGlobalStoreFilter();
			setMedStaffFactGlobalStoreFilter({disableInDoc:true});

			base_form.findField('LpuSection_did').showContainer();
			base_form.findField('MedStaffFact_did').showContainer();
			base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			var medPersonal = getGlobalOptions().medpersonal_id;
			var msf_index = base_form.findField('MedStaffFact_did').getStore().findBy(function(rec){
				return (rec.get('MedPersonal_id') == medPersonal);
			});
			if(msf_index != -1 && this.action == 'add'){
				var cur_msf = base_form.findField('MedStaffFact_did').getStore().getAt(msf_index).get('MedStaffFact_id');
				base_form.findField('MedStaffFact_did').setValue(cur_msf);
				base_form.findField('MedStaffFact_did').fireEvent('select',base_form.findField('MedStaffFact_did'),cur_msf);
			}
		} else {
			base_form.findField('LpuSection_did').hideContainer();
			base_form.findField('MedStaffFact_did').hideContainer();
		}

		if (getRegionNick() == 'kz') {
			base_form.findField('OrgHead_id').hideContainer();
		}


		switch (this.action) {
			case 'add':
				if (arguments[0].formParams) {
					person_id = arguments[0].formParams.Person_id;
					server_id = arguments[0].formParams.Server_id;
					base_form.findField('Person_id').setValue(person_id);
					base_form.findField('Server_id').setValue(server_id);
					form.getForm().setValues(arguments[0].formParams);
				}

				base_form.findField('OrgHead_id').getStore().load({
					params: {
						Lpu_id: getGlobalOptions().Lpu_id
					},
					callback: function() {
						var combo = base_form.findField('OrgHead_id');
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get('OrgHeadPost_id') == 1);
						});
						if (index >= 0) {
							combo.setValue(combo.getStore().getAt(index).get('OrgHead_id'));
							combo.fireEvent('select', combo, combo.getValue(), null);
						}
					}
				});

				pers_form.load({Person_id: person_id, Server_id: server_id});
				base_form.findField('PntDeathSvid_IsFromMother').setFieldValue('YesNo_Code', 0);
				if (arguments[0].formParams) {
					var dcombo_arr = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid', 'Diag_oid']; //инициализация диагнозов
					for (var i = 0; i < dcombo_arr.length; i++) {
						var diag_combo = base_form.findField(dcombo_arr[i]);
						var diag_id = diag_combo.getValue();
						if (diag_id != '') {
							diag_combo.getStore().combo_id = dcombo_arr[i];
							diag_combo.getStore().load({
								params: {where: "where Diag_id = " + diag_id},
								callback: function (data) {
									var combo_id = data[0] && data[0].store.combo_id ? data[0].store.combo_id : '';
									if (combo_id != '') {
										var diag_combo = base_form.findField(combo_id);
										diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
									}
								}
							});
						}
					}

					var p_rid = base_form.findField('Person_rid').getValue();
					if (p_rid > 0) {
						base_form.findField('Person_rid').getStore().loadData([{
							Person_id: p_rid,
							Person_Fio: base_form.findField('Person_r_FIO').getValue()
						}]);
						base_form.findField('Person_rid').setValue(p_rid);
						base_form.findField('Person_rid').collapse();
						base_form.findField('Person_rid').focus(true, 500);
						base_form.findField('Person_rid').fireEvent('change', base_form.findField('Person_rid'));

						base_form.findField('PntDeathSvid_PolFio').disable();
					}

					var death_period_combo = base_form.findField('PntDeathPeriod_id');
					death_period_combo.fireEvent('change', death_period_combo, death_period_combo.getValue());

					var lpu_id = getGlobalOptions().lpu_id;
					if (lpu_id) {
						Ext.Ajax.request({
							callback: function (options, success, response) {
								if (success && response.responseText != '') {
									var response_obj = Ext.util.JSON.decode(response.responseText);
									if (response_obj.length == 0) {
										return false;
									}
									base_form.findField('DAddress_Zip').setValue(response_obj[0].BAddress_Zip);
									base_form.findField('DKLCountry_id').setValue(response_obj[0].BKLCountry_id);
									base_form.findField('DKLRGN_id').setValue(response_obj[0].BKLRGN_id);
									base_form.findField('DKLSubRGN_id').setValue(response_obj[0].BKLSubRGN_id);
									base_form.findField('DKLCity_id').setValue(response_obj[0].BKLCity_id);
									base_form.findField('DKLTown_id').setValue(response_obj[0].BKLTown_id);
									base_form.findField('DKLStreet_id').setValue(response_obj[0].BKLStreet_id);
									base_form.findField('DAddress_House').setValue(response_obj[0].BAddress_House);
									base_form.findField('DAddress_Corpus').setValue(response_obj[0].BAddress_Corpus);
									base_form.findField('DAddress_Flat').setValue(response_obj[0].BAddress_Flat);
									base_form.findField('DAddress_Address').setValue(response_obj[0].BAddress_Address);
									base_form.findField('DAddress_AddressText').setValue(response_obj[0].BAddress_Address);
								}
							},
							params: {
								Lpu_id: lpu_id
							},
							url: '/?c=MedSvid&m=getDefaultBirthAddress'
						});
					}
				}

				win.onChangeReceptType(base_form.findField('ReceptType_id').getValue());

				setCurrentDateTime({
					callback: function () {
						base_form.findField('PntDeathSvid_GiveDate').fireEvent('change', base_form.findField('PntDeathSvid_GiveDate'), base_form.findField('PntDeathSvid_GiveDate').getValue());
						base_form.findField('PntDeathSvid_GiveDate').focus(true, 0);

					},
					dateField: base_form.findField('PntDeathSvid_GiveDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					timeField: base_form.findField('PntDeathSvid_GiveDate'),
					windowId: 'MedSvidPntDeathEditWindow'
				});
				break;
			case 'view':
			case 'edit':
				if (arguments[0].formParams) {
					var svid_id = arguments[0].formParams.PntDeathSvid_id;
				}
				this.buttons[1].show();
				win.getLoadMask(LOAD_WAIT).show();
				base_form.load({
					failure: function () {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_svidetelstva'], function () {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params: {
						svid_id: svid_id,
						svid_type: 'pntdeath'
					},
					success: function (store) {
						win.getLoadMask().hide();

						if (this.modeNewSvid > 0) {
							base_form.findField('PntDeathSvid_id').setValue(null);
							win.buttons[1].hide();
							base_form.findField('PntDeathSvid_OldSer').setValue(base_form.findField('PntDeathSvid_Ser').getValue());
							base_form.findField('PntDeathSvid_OldNum').setValue(base_form.findField('PntDeathSvid_Num').getValue());
							base_form.findField('PntDeathSvid_OldGiveDate').setValue(base_form.findField('PntDeathSvid_GiveDate').getValue().format('d.m.Y'));
							base_form.findField('PntDeathSvid_OldGiveDate').fireEvent('change', base_form.findField('PntDeathSvid_OldGiveDate'), base_form.findField('PntDeathSvid_OldGiveDate').getValue());
							base_form.findField('PntDeathSvid_GiveDate').setValue(null);
							base_form.findField('PntDeathSvid_RcpDate').setValue(null);
							win.onChangeReceptType(base_form.findField('ReceptType_id').getValue());
							base_form.findField('PntDeathSvid_predid').setValue(svid_id); // это при сохранении станет неактуальным

							switch (this.modeNewSvid) {
								case 1:
									// Новое м/с получает флаг "Дубликат" = 2, а старое становится утерянным.
									base_form.findField('PntDeathSvid_IsDuplicate').setValue(2);
									break;

								case 2:
									// вид становится «Взамен предварительного»
									base_form.findField('DeathSvidType_id').setValue(3);
									base_form.findField('PntDeathSvid_IsDuplicate').setValue(1);
									break;

								case 3:
									// вид становится «Окончательное»
									base_form.findField('DeathSvidType_id').setValue(1);
									base_form.findField('PntDeathSvid_IsDuplicate').setValue(1);
									break;

								case 4:
									// вид становится «Взамен окончательного»
									base_form.findField('DeathSvidType_id').setValue(4);
									base_form.findField('PntDeathSvid_IsDuplicate').setValue(1);
									break;
								case 5:
									// становится неутерянным
									base_form.findField('PntDeathSvid_IsLose').setValue(1);
									base_form.findField('PntDeathSvid_IsDuplicate').setValue(1);
									break;
							}

							base_form.findField('DeathSvidType_id').disable();
						} else {
							// иначе редактирование в части сведений о получателе без выписки нового свидетельства
							// дисаблим форму
							this.enableEdit(false);
							// раздисабливаем только поля получателя и кнопку сохранить
							if ( win.action == 'edit' ) {
								base_form.findField('Person_rid').enable();
								base_form.findField('PntDeathSvid_PolFio').enable();
								base_form.findField('PntDeathSvid_RcpDoc').enable();
								base_form.findField('DeputyKind_id').enable();
								base_form.findField('PntDeathSvid_RcpDate').enable();
								// сохраняем только получателя
								win.saveMode = 2;
								win.buttons[0].show();
							}
						}
						person_id = base_form.findField('Person_id').getValue();
						server_id = base_form.findField('Server_id').getValue();
						pers_form.load({Person_id: person_id, Server_id: server_id});

						setCurrentDateTime({
							callback: function () {
								base_form.findField('PntDeathSvid_GiveDate').fireEvent('change', base_form.findField('PntDeathSvid_GiveDate'), base_form.findField('PntDeathSvid_GiveDate').getValue());
								base_form.findField('PntDeathSvid_GiveDate').focus(true, 0);
							},
							dateField: base_form.findField('PntDeathSvid_GiveDate'),
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: false,
							timeField: base_form.findField('PntDeathSvid_GiveDate'),
							windowId: 'MedSvidPntDeathEditWindow'
						});

						var dcombo_arr = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid', 'Diag_oid']; //инициализация диагнозов
						for (var i = 0; i < dcombo_arr.length; i++) {
							var diag_combo = base_form.findField(dcombo_arr[i]);
							var diag_id = diag_combo.getValue();
							if (diag_id != '') {
								diag_combo.getStore().combo_id = dcombo_arr[i];
								diag_combo.getStore().load({
									params: {where: "where Diag_id = " + diag_id},
									callback: function (data) {
										//непростой способ добычи ид комбобокса
										var combo_id = data[0] && data[0].store.combo_id ? data[0].store.combo_id : '';
										if (combo_id != '') {
											var diag_combo = base_form.findField(combo_id);
											diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
										}
									}
								});
							}
						}

						var p_rid = base_form.findField('Person_rid').getValue();
						if (base_form.findField('Person_rid').getValue() > 0) {
							base_form.findField('Person_rid').getStore().loadData([{
								Person_id: p_rid,
								Person_Fio: base_form.findField('Person_r_FIO').getValue()
							}]);
							base_form.findField('Person_rid').setValue(p_rid);
							base_form.findField('PntDeathSvid_PolFio').disable();
						} else {
							base_form.findField('Person_rid').getStore().removeAll();

							if ( !Ext.isEmpty(base_form.findField('PntDeathSvid_PolFio').getValue()) ) {
								base_form.findField('Person_rid').disable();
							}
						}

						var org_id = base_form.findField('Org_id').getValue();

						if (!Ext.isEmpty(org_id)) {
							base_form.findField('OrgDep_id').getStore().removeAll();
							base_form.findField('OrgDep_id').getStore().load({
								callback: function (records, options, success) {
									if (base_form.findField('OrgDep_id').getStore().getCount() == 1) {
										base_form.findField('OrgDep_id').setValue(base_form.findField('OrgDep_id').getStore().getAt(0).get('Org_id'));
									}
								},
								params: {
									Org_pid: org_id,
									OrgType: 'dep'
								}
							});
						}

						if (this.action == 'edit') {
							var dstype = base_form.findField('DeathSvidType_id').getValue();
							if (dstype == 3 || dstype == 4) {
								base_form.findField('PntDeathSvid_OldSer').enable();
								base_form.findField('PntDeathSvid_OldNum').enable();
								base_form.findField('PntDeathSvid_OldGiveDate').enable();
							} else {
								base_form.findField('PntDeathSvid_OldSer').disable();
								base_form.findField('PntDeathSvid_OldNum').disable();
								base_form.findField('PntDeathSvid_OldGiveDate').disable();
							}

							var mprtype = base_form.findField('PntDeathSvid_IsMnogoplod').getValue();
							if (mprtype == 2) {
								base_form.findField('PntDeathSvid_PlodIndex').enable();
								base_form.findField('PntDeathSvid_PlodCount').enable();
							} else {
								base_form.findField('PntDeathSvid_PlodIndex').disable();
								base_form.findField('PntDeathSvid_PlodCount').disable();
								base_form.findField('PntDeathSvid_PlodIndex').setValue('');
								base_form.findField('PntDeathSvid_PlodCount').setValue('');
							}
						}

						if (base_form.findField('OrgHead_id').isVisible()) {
							base_form.findField('OrgHead_id').getStore().load({
								params: {
									Lpu_id: base_form.findField('Lpu_id').getValue(),
									OrgHead_id: base_form.findField('OrgHead_id').getValue()
								},
								callback: function () {
									var combo = base_form.findField('OrgHead_id');
									var jsonData = store.reader.jsonData[0];

									if (Ext.isEmpty(combo.getValue()) && jsonData.Person_hFIO) {
										combo.setRawValue(jsonData.Person_hFIO);
									} else if (Ext.isEmpty(combo.getValue())) {
										var index = combo.getStore().findBy(function (rec) {
											return (rec.get('OrgHeadPost_id') == 1);
										});
										if (index >= 0) {
											combo.setValue(combo.getStore().getAt(index).get('OrgHead_id'));
											combo.fireEvent('select', combo, combo.getValue(), null);
										}
									}
								}
							});
						}
					}.createDelegate(this),
					url: '/?c=MedSvid&m=loadMedSvidEditForm'
				});
				break;
		}

		if (this.action != 'view')
			base_form.findField('ReceptType_id').focus(true, 400);

		Ext.getCmp('MedSvidPntDeathEditWindowTab').ownerCt.doLayout();

		//base_form.clearInvalid();
		loadMask.hide();

		if(getRegionNick() == 'vologda') {
			var base_form = win.findById('MedSvidPntDeathEditForm').getForm();

			base_form.findField('PntDeathSvid_DeathDate_Date').setAllowBlank(false);
			base_form.findField('PntDeathSvid_DeathDate_Time').setAllowBlank(false);
		}
	},
	title: WND_MSVID_RECADD,
	width: 700
});

