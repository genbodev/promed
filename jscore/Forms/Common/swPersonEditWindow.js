/**
* swPersonEditWindow - окно редактирования персональных данных.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      24.02.2009
*/
sw.Promed.swPersonEditWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'fit',
	width: 800,
	modal: true,
	resizable: false,
	draggable: false,
	autoHeight: true,
	closeAction :'hide',
	plain: true,
	minBirtDay:null,
	id: 'PersonEditWindow',
	onClose: Ext.emptyFn,
	returnFunc: Ext.emptyFn,
	personId: 0,
	rz:null,
	action: 'edit',
	title: WND_PERS_EDIT,
	codeRefresh: true,
	objectName: 'swPersonEditWindow',
	objectSrc: '/jscore/Forms/Commons/swPersonEditWindow.js',
	defaultFocusField: null,
	listeners: {
		'hide': function() {this.onClose()},
		'show': function() {

    		var tooltipElements = [
				'Person_SurName',
				'Person_FirName',
				'Person_SecName'
			];

			Ext.each(tooltipElements,function(){

				Ext.select("[name="+this+"]").on('keydown',function(e,el) {
					var keyCode = e.getCharCode();
					if(keyCode == 173 || keyCode == 189) {
						$(this).hint({
							title : 'возможен ввод символов на доп. клавиатуре',
							text  : 'Тире (длинное) —	&mdash;	Alt + 0151 Короткое (среднее) тире - &ndash; Alt + 0150',
							delay : 2000
						});
					}
				});
			});
		}
	},

	// #182939
	// Кнопка "Добавить" на панели инструментов в разделе "Оценка
	// физического развития" на вкладке "3. Специфика. Детство.":
	_personEvalBtnAdd: undefined,

	disableEdit: function(disable) {
		var form = this.findById('person_edit_form');
		if ( disable === false )
		{
			form.enable();
			this.buttons[0].show();
// 			this.PersonEval.setActionHidden('action_add',false);
			this.PersonEval.setActionHidden('action_edit',false);
			this.PersonEval.setActionHidden('action_delete',false);

			this.PersonFeedingType.setActionHidden('action_add',false);
			this.PersonFeedingType.setActionHidden('action_edit',false);
			this.PersonFeedingType.setActionHidden('action_delete',false);
		}
		else
		{
			var vals = form.getForm().getValues();
			for ( value in vals )
			{
				form.getForm().findField(value).disable();
				this.buttons[0].hide();
			}
// 			this.PersonEval.setActionHidden('action_add',true);
			this.PersonEval.setActionHidden('action_edit',true);
			this.PersonEval.setActionHidden('action_delete',true);

			this.PersonFeedingType.setActionHidden('action_add',true);
			this.PersonFeedingType.setActionHidden('action_edit',true);
			this.PersonFeedingType.setActionHidden('action_delete',true);
		}
	},
	//блокировка полей незадействованных в апи #84868
	disableEditNotApiFields: function() {
		var form = this.findById('person_edit_form'),
			vals = form.getForm().getValues(),
			apiFields = [
				"Person_SurName",
				"Person_FirName",
				"Person_SecName",
				"PersonPhone_Phone",
				//"PersonPhone_Comment",
				"Person_Comment",
				"Person_BirthDay",
				"Person_SNILS",
				"PersonSex_id",
				"UAddress_AddressText",
				"UKLRGN_id",
				"UKLSubRGN_id",
				"UKLCity_id",
				"UKLTown_id",
				"UKLStreet_id",
				"UAddress_Zip",
				"UAddress_House",
				"UAddress_Corpus",
				"UAddress_Flat",
				"PAddress_AddressText",
				"PKLRGN_id",
				"PKLSubRGN_id",
				"PKLCity_id",
				"PKLTown_id",
				"PKLStreet_id",
				"PAddress_Zip",
				"PAddress_House",
				"PAddress_Corpus",
				"PAddress_Flat",
				"Polis_Ser",
				"Polis_Num",
				"OrgSMO_id",
				"PolisType_id",
				"Polis_begDate",
				"Polis_endDate",
				"Document_Ser",
				"Document_Num",
				"DocumentType_id",
				"Document_begDate",
				"BAddress_AddressText",
				"BKLRGN_id",
				"BKLSubRGN_id",
				"BKLCity_id",
				"BKLTown_id",
				"BKLStreet_id",
				"BAddress_Zip",
				"BAddress_House",
				"BAddress_Corpus",
				"BAddress_Flat",
				"PersonNationality_id",
				"PolisFormType_id",
				"OMSSprTerr_id",
				"SocStatus_id"
			];

		for ( value in vals )
		{
			if(apiFields.indexOf(value) == -1)
				form.getForm().findField(value).disable();
			else
				form.getForm().findField(value).enable();
			//this.buttons[0].hide();
		}
		form.getForm().findField('Polis_begDate').enable();

	},
	checkPersonCard:function(resp){
		if(getRegionNick() != 'kareliya'||Ext.isEmpty(resp.LPU_CODE)||resp.LPU_CODE==''){
			return false;
		}
		var params = {
			Person_id : this.personId,
			LPU_CODE : resp.LPU_CODE,
			LPUDX : resp.LPUDX
		};
		if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
			params.RegistryType_id = g_RegistryType_id;
		var win = this;
		Ext.Ajax.request({
			url: '/?c=PersonCard&m=checkPersonCard',
			params: params,
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					if ( response.responseText.length > 0 ) {
						var resp_obj = Ext.util.JSON.decode(response.responseText);

						if ( !Ext.isEmpty(resp_obj[0])&&!resp_obj[0].PersonCard_id&&resp_obj[0].Lpu_Nick) {
							sw.swMsg.alert(lang['oshibka'], lang['informatsiya_po_osnovnomu_prikrepleniyu_v_tfoms_otlichaetsya_ot_dannyih_v_sisteme_promed']+' '+resp_obj[0].Lpu_Nick+', '+Ext.util.Format.date(resp.LPUDT)+' '+lang['po_mestu_registratsii'], function() {this.buttons[2].focus();}.createDelegate(this) );
						}
					}
				}
			}.createDelegate(this)
		});

	},
	checkChildrenDuplicates:function(params){
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();
		var params = new Object();
		if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
			params.RegistryType_id = g_RegistryType_id;
		params.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
		params.Person_FirName = base_form.findField('Person_FirName').getValue();
		params.Person_SecName = base_form.findField('Person_SecName').getValue();
		params.Person_SurName = base_form.findField('Person_SurName').getValue();
		params.Person_pid = base_form.findField('DeputyPerson_id').getValue();
		params.DeputyKind_id = base_form.findField('DeputyKind_id').getValue();
		params.Sex_id = base_form.findField('PersonSex_id').getValue();

		Ext.Ajax.request({
			url: '/?c=Person&m=checkChildrenDuplicates',
			params: params,
			failure: function(response, options)
			{
				log(1);
				return false;
			},
			success: function(response, action)
			{log(response);
				if (response.responseText) {
					var answer = Ext.util.JSON.decode(response.responseText);

					if (answer[0].success) {
						win.doSubmit();

					} else {
						if (answer[0].child) {
							var buttons = {
								yes: {
									text: lang['otmenit_vvod'],
									tooltip: lang['sbros_dannyih_i_zakryityie_formyi_dobavleniya_cheloveka']
								},
								no: {
									text: lang['prodoljit_vvod'],
									tooltip: lang['vozvrat_k_dobavleniyu_cheloveka']
								}
							};
							var msgbox = sw.swMsg.show({
								buttons: buttons,
								msg: answer[0].child.warning,
								title: lang['vnimanie'],
								icon: Ext.MessageBox.WARNING,
								fn: function(buttonId){
									if (buttonId === 'yes') {
										win.hide();
									} else if (buttonId === 'no') {
										win.doSubmit();
									}
								}.createDelegate(this)
							});
							msgbox.getDialog().setWidth(640);
						}
					}
				} else {
					win.hide();
				}
			}.createDelegate(this)
		});
	},
	replaceOldValue: function(field, value) {
		this.oldValuesToRestore[field] = value;

		var regexp = new RegExp('('+field+'=)([^\\&]*)');
		this.oldValues = this.oldValues.replace(regexp, function(str, match1, match2) {
			return match1+(value===null?'':value);
		});
	},
	verifyPersonPhone: function() {
		var base_form = this.findById('person_edit_form').getForm();

		var Person_id = base_form.findField('Person_id').getValue();
		var PersonPhone_Phone = base_form.findField('PersonPhone_Phone').getValue();

		if (Ext.isEmpty(Person_id)) {
			sw.swMsg.show({
				title: langs('Ошибка'),
				msg: langs('Необходимо сперва сохранить данные человека'),
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}
		if (Ext.isEmpty(PersonPhone_Phone)) {
			sw.swMsg.show({
				title: langs('Ошибка'),
				msg: langs('Не введен номер телефона'),
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}

		var callback = function(data) {
			if (data && data.PersonPhoneStatus_id == 3) {
				base_form.findField('PersonPhone_VerifiedPhone').setValue(PersonPhone_Phone);
				this.replaceOldValue('PersonPhone_Phone', PersonPhone_Phone);
			}
			this.refreshPersonPhoneVerificationButton();
		}.createDelegate(this);

		var lastMedStaffFact = sw.Promed.MedStaffFactByUser.last || {};

		var phone = PersonPhone_Phone.replace(/([-\(\)])/g,'');
		if (phone[0] != 9) {
			//Номер стационарного телефона. Подтверждаем.
			var params = {
				Person_id: Person_id,
				PersonPhone_Phone: PersonPhone_Phone,
				MedStaffFact_id: lastMedStaffFact.MedStaffFact_id || null,
				PersonPhoneStatus_id: 3
			};

			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
			loadMask.show();

			Ext.Ajax.request({
				url: '/?c=Person&m=addPersonPhoneHist',
				params: params,
				success: function(response) {
					loadMask.hide();

					var answer = Ext.util.JSON.decode(response.responseText);

					if (answer.success) {
						callback(params);
					}
				}.createDelegate(this),
				failure: function() {
					loadMask.hide();
				}.createDelegate(this)
			});
		} else {
			//Номер мобильного телефона. Открывем окно для ввода кода подтверждения.
			var params = {};
			params.formParams = {
				Person_id: Person_id,
				PersonPhone_Phone: PersonPhone_Phone,
				MedStaffFact_id: lastMedStaffFact.MedStaffFact_id || null
			};
			params.onHide = callback;

			getWnd('swPersonPhoneVerificationCodeWindow').show(params);
		}
	},
	verifyPersonSnils: function() {
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();

		var Person_id = base_form.findField('Person_id').getValue();
		var Person_SNILS = base_form.findField('Person_SNILS').getValue();

		if (Ext.isEmpty(Person_id)) {
			sw.swMsg.show({
				title: langs('Ошибка'),
				msg: langs('Необходимо сперва сохранить данные человека'),
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}
		if (Ext.isEmpty(Person_SNILS)) {
			sw.swMsg.show({
				title: langs('Ошибка'),
				msg: langs('Не введен СНИЛС'),
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING
			});
			return;
		}

		win.getLoadMask('Постановка человека в очередь на валидацию СНИЛС').show();
		Ext.Ajax.request({
			url: '/?c=Person&m=verifyPersonSnils',
			params: {
				Person_id: Person_id
			},
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj.success) {
						sw.swMsg.alert(langs('Внимание'), 'Человек поставлен в очередь на валидацию СНИЛС');
					}
				}
			}
		});
	},
	refreshPersonPhoneVerificationButton: function() {
		var base_form = this.findById('person_edit_form').getForm();
		var verifed_button = this.findById('PEW_PersonPhoneVerifiedButton');
		var verify_button = this.findById('PEW_PersonPhoneVerifyButton');

		var phone = base_form.findField('PersonPhone_Phone').getValue();
		var verified_phone = base_form.findField('PersonPhone_VerifiedPhone').getValue();

		var isVerified = (!Ext.isEmpty(verified_phone) && verified_phone == phone);

		verifed_button.setVisible(isVerified);
		verify_button.setVisible(!isVerified);
	},
	refreshPersonSnilsVerificationButton: function() {
		var base_form = this.findById('person_edit_form').getForm();
		var verifed_button = this.findById('PEW_PersonSnilsVerifiedButton');
		var verify_button = this.findById('PEW_PersonSnilsVerifyButton');

		var PersonState_IsSnils = base_form.findField('PersonState_IsSnils').getValue();

		verifed_button.setVisible(PersonState_IsSnils == 2);
		verify_button.setVisible(PersonState_IsSnils != 2);
		if (PersonState_IsSnils == 1) {
			verify_button.getEl().set({
				'data-qtip': 'Ошибка валидации СНИЛС'
			});
		} else {
			verify_button.getEl().set({
				'data-qtip': 'Произвести валидацию СНИЛС'
			});
		}
	},
	doPersonIdentRequest: function(full) {
		var win = this;
		var form = this.findById('person_edit_form');

		if ( this.buttons[2].hidden || this.readOnly === true ) {
			return false;
		}

		var base_form = form.getForm();
		var person_id = this.personId;
		var record;

		if ( !person_id || Number(person_id) <= 0 ) {
			person_id = 0;
		}

		var document_type_code = 0;
		var klarea_id = 0;
		var klstreet_id = base_form.findField('UKLStreet_id').getValue();
		var polis_num = base_form.findField('Polis_Num').getValue().toString();
		var sex_id = 0;
		var sex_code = 0;
		var soc_status_code = 0;

		if ( base_form.findField('PolisType_id').getValue() == 4 ) {
			polis_num = base_form.findField('Federal_Num').getValue().toString();
		}
/*
		if ( (polis_num.length > 0 && polis_num.length != 16) || (polis_num.length == 16 && polis_num.length >= 2 && polis_num.substr(0, 2) != '02') ) {
			sw.swMsg.alert(lang['oshibka'], lang['identifikatsiya_nedostupna_prichina_nevernyiy_nomer_polisa'], function() { form.buttons[2].focus(); }.createDelegate(this) );
			return false;
		}
*/
/*
		// https://redmine.swan.perm.ru/issues/11587
		// 1) Разрешить идентификацию в случае, если территория страхования НЕ Башкортостан.
		record = base_form.findField('OMSSprTerr_id').getStore().getById(base_form.findField('OMSSprTerr_id').getValue());
		if ( record && record.get('OMSSprTerr_Code') != 61 ) {
			sw.swMsg.alert(lang['oshibka'], lang['identifikatsiya_nedostupna_prichina_inaya_territoriya_strahovaniya'], function() {this.buttons[2].focus();}.createDelegate(this) );
			return false;
		}
*/
		if ( base_form.findField('UKLTown_id').getValue() ) {
			klarea_id = base_form.findField('UKLTown_id').getValue();
		}
		else if ( base_form.findField('UKLCity_id').getValue() ) {
			klarea_id = base_form.findField('UKLCity_id').getValue();
		}
		else if ( base_form.findField('UKLSubRGN_id').getValue() ) {
			klarea_id = base_form.findField('UKLSubRGN_id').getValue();
		}
		else if ( base_form.findField('UKLRGN_id').getValue() ) {
			klarea_id = base_form.findField('UKLRGN_id').getValue();
		}

		record = base_form.findField('DocumentType_id').getStore().getById(base_form.findField('DocumentType_id').getValue());
		if ( record ) {
			document_type_code = record.get('DocumentType_Code');
		}

		record = base_form.findField('PersonSex_id').getStore().getById(base_form.findField('PersonSex_id').getValue());
		if ( record ) {
			sex_id = record.get('Sex_id');
			sex_code = record.get('Sex_Code');
		}

		record = base_form.findField('SocStatus_id').getStore().getById(base_form.findField('SocStatus_id').getValue());
		if ( record ) {
			soc_status_code = record.get('SocStatus_Code');
		}

		var params = {
			fromClient: true,
			Document_Num: base_form.findField('Document_Num').getValue(),
			Document_Ser: base_form.findField('Document_Ser').getValue(),
			DocumentType_Code: document_type_code,
			OrgSmo_id: base_form.findField('OrgSMO_id').getValue(),
			KLArea_id: klarea_id,
			KLStreet_id: klstreet_id,
			Person_Birthday: Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y'),
			Person_Firname: base_form.findField('Person_FirName').getValue(),
			Person_id: person_id,
			Person_Inn: base_form.findField('PersonInn_Inn').getValue(),
			Person_Secname: base_form.findField('Person_SecName').getValue(),
			Person_Surname: base_form.findField('Person_SurName').getValue(),
			Person_SNILS: base_form.findField('Person_SNILS').getValue(),
			PolisType_id: base_form.findField('PolisType_id').getValue(),
			Polis_Ser: base_form.findField('Polis_Ser').getValue(),
			Polis_Num: polis_num,
			Sex_id: sex_id,
			Sex_Code: sex_code,
			SocStatus_Code: soc_status_code,
			UAddress_Flat: base_form.findField('UAddress_Flat').getValue(),
			UAddress_House: base_form.findField('UAddress_House').getValue(),
			BAddress_Address: base_form.findField('BAddress_Address').getValue(),
			Person_IsBDZ: (base_form.findField('Server_pid').getValue() == 0)?1:0
		};
		if (getRegionNick() == 'astra'&& full) {
			params.full = full;
		}
		if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
			params.RegistryType_id = g_RegistryType_id;
		//Если редактирование периодики для случая,
		//то идентификация на дату случая
		if (this.personEvnId && this.Evn_setDT) {
			params.PersonIdentOnDate = this.Evn_setDT;
		}

		win.getLoadMask(lang['vyipolnyaetsya_zapros_na_identifikatsiyu_cheloveka']).show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (getRegionNick().inlist(['kareliya','astra'])) {
						if (response_obj && response_obj.Person_IsInErz) {
							var pzActual = (Ext.isEmpty(response_obj.pz_actual)) ? false : true;
							if(getRegionNick().inlist(['astra']) && !pzActual){
								var snPolMsg = (!pzActual && response_obj && !Ext.isEmpty(response_obj.sn_pol)) ? response_obj.sn_pol : '';
								sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса на идентификацию человека. ') + snPolMsg, function() {this.buttons[2].focus();}.createDelegate(this) );
								return false;
							}
							var actText=null;
							if(getRegionNick() == 'astra'&&response_obj.pz_actual==0){
								actText=lang['polis_patsienta_na_dannyiy_moment_ne_deystvitelen_esli_vyi_hotite_otkazatsya_ot_obnovleniya_dannyih_najmite_na_forme_knopku_otmena_pri_etom_forma_zakroetsya_bez_sohraneniya']
							}
							this.setFieldsOnIdent(response_obj, true);
							this.fullIdent(response_obj,actText);
							this.checkPersonCard(response_obj);
							if ( response_obj.Alert_Msg && response_obj.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Alert_Msg, function() {this.buttons[2].focus();}.createDelegate(this) );
							}
							if(getRegionNick() == 'astra'){
								this.rz = response_obj.rz;
							}
						}
						// Если задано сообщение об ошибке...
						else if (response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0) {
							// ... выводим сообщение об ошибке
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
						}
						// Иначе...
						else {
							// ... выводим сообщение об ошибке
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_na_identifikatsiyu_cheloveka'], function() {this.buttons[2].focus();}.createDelegate(this) );
						}
					} else if (getRegionNick() == 'ekb') {
						if (response_obj && !response_obj.Error_Msg) {
							response_obj.callback = function(data) {
								if (data.OrgSMO_id) {
									base_form.findField('OMSSprTerr_id').setValue(data.OMSSprTerr_id);
									base_form.findField('PolisType_id').setValue(data.PolisType_id);
									base_form.findField('PolisFormType_id').setValue(data.PolisFormType_id);
									base_form.findField('Polis_Ser').setValue(data.Polis_Ser);
									base_form.findField('Polis_Num').setValue(data.Polis_Num);
									base_form.findField('Federal_Num').setValue(data.Federal_Num);
									base_form.findField('OrgSMO_id').setValue(data.OrgSMO_id);
									base_form.findField('Polis_begDate').setValue(data.Polis_begDate);
									base_form.findField('Polis_endDate').setValue(data.Polis_endDate);
									base_form.findField('Person_identDT').setValue(data.Person_identDT);
									base_form.findField('PersonIdentState_id').setValue(data.PersonIdentState_id);
									base_form.findField('BDZ_id').setValue(data.BDZ_id);
								}
							};
							getWnd('swBDZAnswerWindow').show(response_obj);
						}
						/*if (response_obj && response_obj.OrgSMO_id) {
							// Территория страхования - Екатеринбург
							var terr_combo = base_form.findField('OMSSprTerr_id');
							terr_combo.getStore().each(function(rec) {
								if ( rec.get('OMSSprTerr_Code') == 1165 ) {
									terr_combo.setValue(rec.get('OMSSprTerr_id'));
									terr_combo.fireEvent('change', terr_combo, rec.get('OMSSprTerr_id'));
								}
							});
							base_form.findField('OrgSMO_id').getStore().each(function(rec) {
								if ( rec.get('OrgSMO_id') == response_obj.OrgSMO_id ) {
									base_form.findField('OrgSMO_id').setValue(rec.get('OrgSMO_id'));
								}
							});

							base_form.findField('Person_identDT').setValue(response_obj.Person_identDT);
							base_form.findField('PersonIdentState_id').setValue(response_obj.PersonIdentState_id);

							showSysMsg(lang['chelovek_identifitsirovan']);
						}*/
						// Если задано сообщение об ошибке...
						else if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							// ... выводим сообщение об ошибке
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
						}
						// Иначе...
						else {
							// ... выводим сообщение об ошибке
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa'], function() {this.buttons[2].focus();}.createDelegate(this) );
						}
					} else {
						// Если человек идентифицирован...
						if ( response_obj.PersonIdentState_id && response_obj.PersonIdentState_id.toString().inlist(['-1', '1', '2', '3']) ) {
							// Идентификация не требуется
							if ( response_obj.PersonIdentState_id == '-1' ) {
								return false;
							}

							// Полис пациента недействителен...
							if ( response_obj.PersonIdentState_id == '3' ) {
								// ... выводим сообщение
								if(getRegionNick() == 'astra'){
									var actText = lang['polis_patsienta_na_dannyiy_moment_ne_deystvitelen_esli_vyi_hotite_otkazatsya_ot_obnovleniya_dannyih_najmite_na_forme_knopku_otmena_pri_etom_forma_zakroetsya_bez_sohraneniya'];
									this.rz = response_obj.rz;
									this.setFieldsOnIdent(response_obj, false);
									this.fullIdent(response_obj,actText);
									this.checkPersonCard(response_obj);
								}else{
									if(!response_obj.ELIMIN_DATE
										||(response_obj.ELIMIN_DATE&&Date.parseDate(response_obj.ELIMIN_DATE,'d.m.Y').getTime()>=new Date().getTime())){
											if (getRegionNick() == 'ufa') {
												response_obj.CATEG = 'nrab';
											}
											/* не уверен, что это нужно для Карелии
											if (getRegionNick() == 'kareliya') {
												response_obj.CATEG = 'nrab';
											}*/
											this.setFieldsOnIdent(response_obj, false);
											this.fullIdent(response_obj);
											this.checkPersonCard(response_obj);
										}else{
										sw.swMsg.show({
											buttons: Ext.Msg.YESNO,
											fn: function ( buttonId ) {
												// Если ответ "Да"...
												if ( buttonId == 'yes' ) {
													// ... полученные по идентификации данные подставляются в поля формы, заменяя предыдущие данные и
													// дополняя недостающие. Автоматически происходит изменение статуса пациента на «Не работает».
													// (Сделано для того, чтобы посещение попало в реестр по месту жительства пациента)
													if (getRegionNick() == 'ufa') {
														response_obj.CATEG = 'nrab';
													}
													/* не уверен, что это нужно для Карелии
													if (getRegionNick() == 'kareliya') {
														response_obj.CATEG = 'nrab';
													}*/
													this.setFieldsOnIdent(response_obj, false);
													this.fullIdent(response_obj);
													this.checkPersonCard(response_obj);
												}
											}.createDelegate(this),
											msg: lang['u_patsienta_net_deystvuyuschego_polisa_zamenit_dannyie'],
											title: lang['preduprejdenie']
										});
									}
								}
								return false;
							}
							else {
								var actText=null;
								if(getRegionNick() == 'astra'&&response_obj.pz_actual==0){
									actText=lang['polis_patsienta_na_dannyiy_moment_ne_deystvitelen_esli_vyi_hotite_otkazatsya_ot_obnovleniya_dannyih_najmite_na_forme_knopku_otmena_pri_etom_forma_zakroetsya_bez_sohraneniya']
								}
								this.setFieldsOnIdent(response_obj, true);
								this.fullIdent(response_obj,actText);
								this.checkPersonCard(response_obj);
								if ( response_obj.Alert_Msg && response_obj.Alert_Msg.toString().length > 0 ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Alert_Msg, function() {this.buttons[2].focus();}.createDelegate(this) );
								}
							}
							if(getRegionNick() == 'astra'){
								this.rz = response_obj.rz;
							}
						}
						// Если задано сообщение об ошибке...
						else if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
							// ... выводим сообщение об ошибке
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
						}
						// Иначе...
						else {
							// ... выводим сообщение об ошибке
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_na_identifikatsiyu_cheloveka'], function() {this.buttons[2].focus();}.createDelegate(this) );
						}
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_vyipolnenii_zaprosa_na_identifikatsiyu_cheloveka'], function() {this.buttons[2].focus();}.createDelegate(this) );
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=PersonIdentRequest&m=doPersonIdentRequest'
		});

		return true;
	},
	fullIdent:function(response_obj,text){
		var win = this;
		if (getRegionNick() != 'astra') {
			return false;
		}

		if(!response_obj.sity&&!response_obj.DOC_TYPE&&!response_obj.lpu_Nick){
			if(text){
				showSysMsg(
					'',
					text,
					null,
					{animateTarget:win.id,closable: true, delay: 15000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'}
				);
			}
			return false;
		}
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();
		var Text = (text)?text+'<br><br>':'';
		Text += ((response_obj.f)?response_obj.f+' ':'')+((response_obj.i)?response_obj.i+' ':'')+((response_obj.o)?response_obj.o+' ':'')+((response_obj.dr)?"<br>Д.р.: "+response_obj.dr+' ':'<br>')+((response_obj.ss)?"<br>СНИЛС: "+response_obj.ss+' ':'<br>');


		if(response_obj.sity){
			Text+='Адрес: ';
			var address = '';

			address+=/*'г.'+*/response_obj.sity;
			address+=(response_obj.rayon)?', район '+response_obj.rayon:'';
			address+=(response_obj.street)?', ул. '+response_obj.street:'';
			address+=(response_obj.house)?', д. '+response_obj.house:'';
			address+=(response_obj.apartment)?', кв. '+response_obj.apartment:'';
			Text+=address+'<br><br>';
		}
		if(response_obj.DOC_TYPE){
			Text+='Документ:';
			Text+=(response_obj.DOC_SER)?' '+response_obj.DOC_SER+',':'';
			Text+=(response_obj.DOC_NUM)?' '+response_obj.DOC_NUM+',':'';
			base_form.findField('DocumentType_id').getStore().each(function(rec) {
				if ( rec.get('DocumentType_Code') == response_obj.DOC_TYPE ) {
					Text+=' '+rec.get('DocumentType_Name')+',';
				}
			});
			Text+=' '+response_obj.doc_v;
			Text+='<br><br>';

		}
		if(response_obj.lpu_Nick){
			Text+='Прикрепление: '+response_obj.lpu_Nick+' '+response_obj.date_prik;
		}
		if(this.IdentMsg!=null&&typeof this.IdentMsg.close=='function'){
			this.IdentMsg.close();
		}
		this.IdentMsg = showSysMsg('', Text, null, {animateTarget:win.id,isReturn:true,closable: true, delay: 100000000000000, bodyStyle: 'text-align:left; margin-left:7px; padding: 0px 0px 20px 20px;background:transparent'});
		log(this.IdentMsg);
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ('yes' == buttonId) {
					var params = {
						KLStreet_Name:response_obj.street,
						KLAdr_Ocatd:response_obj.region,
						Org_Name:response_obj.doc_v

					}
					if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
						params.RegistryType_id = g_RegistryType_id;
					Ext.Ajax.request({callback: function(options, success, response) {
							if(success){
								if ( response.responseText.length > 0 ) {
									var resp_obj = Ext.util.JSON.decode(response.responseText);
									if(resp_obj[0].KLCountry_id){
										base_form.findField('UKLCountry_id').setValue(resp_obj[0].KLCountry_id);
										base_form.findField('UKLRGN_id').setValue(resp_obj[0].KLRegion_id);
										base_form.findField('UKLSubRGN_id').setValue(resp_obj[0].KLSubRegion_id);
										base_form.findField('UKLCity_id').setValue(resp_obj[0].KLCity_id);
										base_form.findField('UKLTown_id').setValue(resp_obj[0].KLTown_id);
										base_form.findField('UKLStreet_id').setValue(resp_obj[0].KLStreet_id);
										base_form.findField('UAddress_House').setValue(response_obj.house);
										base_form.findField('UAddress_Flat').setValue(response_obj.apartment);
										base_form.findField('UAddress_Address').setValue('');
										base_form.findField('UAddress_AddressText').setValue(address);
									}/*else{
										Ext.MessageBox.show({
											title: lang['oshibka'],
											msg: "Не удалось разобрать адрес.",
											buttons: Ext.Msg.OK,
											icon: Ext.Msg.WARNING
										})
									}*/
									// Тип документа
									if ( response_obj.DOC_TYPE ) {
										base_form.findField('DocumentType_id').getStore().each(function(rec) {
											if ( rec.get('DocumentType_Code') == response_obj.DOC_TYPE ) {
												base_form.findField('DocumentType_id').setValue(rec.get('DocumentType_id'));
												base_form.findField('DocumentType_id').fireEvent('select', base_form.findField('DocumentType_id'), rec, rec.get('DocumentType_id'));
												base_form.findField('DocumentType_id').fireEvent('blur', base_form.findField('DocumentType_id'));
											}
										});
									}

									// Серия документа
									if ( response_obj.DOC_SER ) {
										base_form.findField('Document_Ser').setValue(response_obj.DOC_SER);
									}

									// Номер документа
									if ( response_obj.DOC_NUM ) {
										base_form.findField('Document_Num').setValue(response_obj.DOC_NUM);
									}

									// Дата выдачи документа
									if ( response_obj.Document_begDate ) {
										base_form.findField('Document_begDate').setValue(response_obj.Document_begDate);
									}
									if(resp_obj[0].OrgDep_id&&resp_obj[0].OrgDep_id>0){

										base_form.findField('OrgDep_id').getStore().load({
											params: {
												Object:'OrgDep',
												OrgDep_id: resp_obj[0].OrgDep_id,
												OrgDep_Name: ''
											},
											callback: function() {
												base_form.findField('OrgDep_id').setValue(resp_obj[0].OrgDep_id);
											}
										});
									}
								}

							}
						}.createDelegate(this),
						params:params,
						url:'/?c=Person&m=getPersonIdentData'
					})
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['obnovit_dannyie_dokumenta'],
			title: lang['vopros']
		})
	},
	/**
	 * Добавление человека в пакет для идентификации ЕРЗ
	 */
	identInErz: function() {
		var base_form = this.findById('person_edit_form').getForm();

		var identDate = Ext.util.Format.date(base_form.findField('Person_identInErzDT').getValue(), 'd.m.Y');

		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			Person_identDT: identDate + ' 00:00',
			fromClient: 1,
			identImmediately: (getRegionNick() == 'vologda')?1:0,
			Person_Surname: base_form.findField('Person_SurName').getValue(),
			Person_Firname: base_form.findField('Person_FirName').getValue(),
			Person_Secname: base_form.findField('Person_SecName').getValue(),
			Person_Sex: base_form.findField('PersonSex_id').getValue(),
			Person_Birthday: Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y'),
			Person_ENP: base_form.findField('Federal_Num').getValue(),
			Person_Snils: base_form.findField('Person_SNILS').getValue(),
			DocumType_Code: base_form.findField('DocumentType_id').getFieldValue('DocumentType_Code'),
			Docum_Ser: base_form.findField('Document_Ser').getValue(),
			Docum_Num: base_form.findField('Document_Num').getValue(),
			PolisType_id: base_form.findField('PolisType_id').getValue(),
			Polis_Ser: base_form.findField('Polis_Ser').getValue(),
			Polis_Num: base_form.findField('Polis_Num').getValue(),
			PersonRequestSourceType_id: 2	//Принудительная идентификация через кнопку
		};

		if (Ext.isEmpty(params.Person_id)) {
			sw.swMsg.alert(lang['soobschenie'], 'Для идентификации нужно сохранить человека');
			return false;
		}

		this.getLoadMask('Формирование данных для идентификации').show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=Person&m=addPersonRequestData',
			success: function(response) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.PersonIdentState_id) {
					if (params.identImmediately && response_obj.Person_IsInErz == 2) {
                        var changedFields = '';
                        var fieldsetTitle = '';

						for (fieldName in response_obj) {
                            var field = base_form.findField(fieldName);
							if (field) {
                                var newValue = response_obj[fieldName];
                                var oldValue = field.getRawValue();
                                var oldIdValue = field.getValue();

                                var valueChanged;
                                if (Ext6.Array.contains(['sworgsmocombo', 'swomssprterrcombo', 'swpolisformtypecombo', 'swcommonsprcombo'], field.getXType())) {
                                    valueChanged = (newValue != oldIdValue);
                                } else if (field.getXType() == 'swdatefield') {
                                    valueChanged = (Ext.util.Format.date(newValue, 'd.m.Y') != oldValue);
                                } else {
                                    newValue = (newValue === null) ? '' : newValue;
                                    valueChanged = (newValue != oldValue);
                                }

                                if (valueChanged) {
                                    if (field.findParentByType('fieldset')
                                            && fieldsetTitle !== field.findParentByType('fieldset').title) {
                                        fieldsetTitle = field.findParentByType('fieldset').title;

                                        changedFields += '<b>' + fieldsetTitle + ':</b><br />';
                                    }

                                    oldValue = Ext6.isEmpty(oldValue) ? '(не заполнено)' : oldValue;
                                    if (typeof field.fieldLabel !== 'undefined') {
                                        changedFields += '<i>' + field.fieldLabel + ': ' + oldValue + '</i><br />';
                                    }
                                }

								field.setValue(response_obj[fieldName]);
								this.replaceOldValue(fieldName, response_obj[fieldName]);
							}
						}

                        if (changedFields == '') {
                            var msgText = 'Данные актуальны, изменение не требуется.';
                        } else {
                            var msgText = 'Следующие данные изменены:<br /><br />' + changedFields;
                        }

                        var identDate = Ext.util.Format.date(response_obj.Person_identDT, 'd.m.Y');
                        var personFio = Ext6.String.capitalize(base_form.findField('Person_SurName').getValue().toLowerCase())
                                + ' ' + Ext6.String.capitalize(base_form.findField('Person_FirName').getValue().toLowerCase())
                                + ' ' + Ext6.String.capitalize(base_form.findField('Person_SecName').getValue().toLowerCase());

                        showSysMsg('Человек идентифицирован.<br />' + msgText + '<br />Дата идентификации: ' + identDate, personFio, 'info', {delay: 100500, closable: true});
					}

					base_form.findField('PersonIdentState_id').setValue(response_obj.PersonIdentState_id);
					base_form.findField('Person_IsInErz').setValue(response_obj.Person_IsInErz);
					base_form.findField('Person_identDT').setValue(new Date(response_obj.Person_identDT).getTime());
					this.refreshIdentInErzStatus();
				}
			}.createDelegate(this),
			failure: function(response) {
				this.getLoadMask().hide();

			}.createDelegate(this)
		});
	},
	openIdentInErzHistoryWindow: function() {
		getWnd('swPersonRequestDataViewWindow').show({Person_id: this.personId});
	},
	refreshIdentInErzStatus: function() {
		var base_form = this.findById('person_edit_form').getForm();
		var statusPanel = this.findById('PEW_IdentInErzStatus');

		var Person_IsInErz = base_form.findField('Person_IsInErz').getValue();
		var PersonIdentState_id = base_form.findField('PersonIdentState_id').getValue();

		var IdentStatus = '';
		if (!Ext.isEmpty(PersonIdentState_id)) {
			switch(Number(PersonIdentState_id)) {
				case 4: IdentStatus = 'человек отправлен на идентификацию в '+this.identERZService;break;
				case 5: IdentStatus = 'не все необходимые данные заполнены';break;
			}
		}
		if (Ext.isEmpty(IdentStatus)) {
			switch(Number(Person_IsInErz)) {
				case 1: IdentStatus = 'получен отрицательный ответ из '+this.identERZService;break;
				case 2: IdentStatus = 'получен положительный ответ из '+this.identERZService;break;
			}
		}
		if (Ext.isEmpty(IdentStatus)) {
			IdentStatus = 'не было запроса в '+this.identERZService;
		}
		statusPanel.tpl.overwrite(statusPanel.body, {IdentStatus: IdentStatus});
	},
	refreshIdentInErzFieldSet: function() {
		var base_form = this.findById('person_edit_form').getForm();
		var fieldSet = this.findById('PEW_IdentInErzFieldSet');

		if (!getRegionNick().inlist(['msk','perm','penza','vologda'])) {
			fieldSet.hide();
			base_form.findField('Person_identInErzDT').setAllowBlank(true);
		} else {
			var Server_pid = base_form.findField('Server_pid').getValue();

			var begDate = base_form.findField('Polis_begDate').getValue();
			var endDate = base_form.findField('Polis_endDate').getValue();
			var currDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');

			var hasPolis = (!Ext.isEmpty(begDate) && begDate <= currDate && (Ext.isEmpty(endDate) || endDate > currDate));

			base_form.findField('Person_identInErzDT').setAllowBlank(false);
			base_form.findField('Person_identInErzDT').setValue(currDate);

			//#91747 разрешено отправлять в ЦС ЕРЗ людей с действующим полисом из РС ЕРЗ
			if (/*Server_pid != 0 || (Server_pid == 0 && !hasPolis)*/true) {
				fieldSet.show();
				this.refreshIdentInErzStatus();
			} else {
				fieldSet.hide();
			}

		}
	},
	/**
	 * Добавление человека в пакет для идентификации ТФОМС
	 */
	identInTfoms: function() {
		var base_form = this.findById('person_edit_form').getForm();

		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			PersonIdentPackageTool_id: 2	//Принудительная идентификация через кнопку
		};

		if (Ext.isEmpty(params.Person_id)) {
			sw.swMsg.alert(langs('Сообщение'), 'Для идентификации нужно сохранить человека');
			return false;
		}

		this.getLoadMask('Добавление данных для идентификации').show();

		Ext.Ajax.request({
			params: params,
			url: '/?c=PersonIdentPackage&m=addPersonIdentPackagePos',
			success: function(response) {
				this.getLoadMask().hide();

				var response_obj = Ext.util.JSON.decode(response.responseText);
				if (response_obj.success) {
					base_form.findField('Person_IsInErz').setValue(null);
					this.refreshIdentInTfomsStatus();
				}
			}.createDelegate(this),
			failure: function(response) {
				this.getLoadMask().hide();

			}.createDelegate(this)
		});
	},
	openIdentInTfomsHistoryWindow: function() {
		getWnd('swPersonIdentPackagePosHistoryWindow').show({Person_id: this.personId});
	},
	refreshIdentInTfomsStatus: function() {
		var base_form = this.findById('person_edit_form').getForm();
		var statusPanel = this.findById('PEW_IdentInTfomsStatus');

		var Person_IsInErz = base_form.findField('Person_IsInErz').getValue();

		var IdentStatus = (Person_IsInErz == 2)?'Идентифицирован':'Не идентифицирован';

		statusPanel.tpl.overwrite(statusPanel.body, {IdentStatus: IdentStatus});
	},
	refreshIdentInTfomsFieldSet: function() {
		var base_form = this.findById('person_edit_form').getForm();
		var fieldSet = this.findById('PEW_IdentInTfomsFieldSet');

		if (!getRegionNick().inlist(['krym'])) {
			fieldSet.hide();
		} else {
			fieldSet.show();
			this.refreshIdentInErzStatus();
		}
	},
	addToOldValuesForIdentification: '',
	setFieldsOnIdent: function(data, disableFields) {
		var base_form = this.findById('person_edit_form').getForm();
		Ext.select('.change').removeClass('change');
		// Устанавливаем дату актуальности данных в сводной базе застрахованных
		base_form.findField('Person_identDT').setValue(data.Person_identDT);
		// Добавляем старые поля полиса и фио, если они были задисаблены.
		this.oldValues = this.oldValues + this.addToOldValuesForIdentification;

		var identified = false;
		if (getRegionNick().inlist(['kareliya','astra'])) {
			identified = (data.Person_IsInErz == 2);
			base_form.findField('Person_IsInErz').setValue(data.Person_IsInErz);

			if (identified) {
				base_form.findField('PersonIdentState_id').setValue(1); // Идентифицирован
			}
		} else {
			identified = (data.PersonIdentState_id != 2);
			base_form.findField('PersonIdentState_id').setValue(data.PersonIdentState_id);
		}

		if ( identified ) {
			// https://redmine.swan.perm.ru/issues/11587
			// 2) При идентификации необходимо очищать поля серии полиса и даты закрытия полиса, если возвращается действующий полис.
			base_form.findField('Polis_endDate').setRawValue('');
			base_form.findField('Polis_Ser').setRawValue('');

			// Убираем фильтрацию территорий
			var terr_combo = base_form.findField('OMSSprTerr_id');
			terr_combo.getStore().filterBy(function(record) {
					return true;
			});
			terr_combo.baseFilterFn = function(record) {
				return true;
			}

			if (getRegionNick() == 'ufa') {
				// Территория страхования - Башкортостан
				terr_combo.getStore().each(function(rec) {
					if ( rec.get('OMSSprTerr_Code') == 61 ) {
						terr_combo.setValue(rec.get('OMSSprTerr_id'));
						terr_combo.fireEvent('change', terr_combo, rec.get('OMSSprTerr_id'));
					}
				});
			}
			var polSer = base_form.findField('Polis_Ser').getValue();
			var polType = base_form.findField('PolisType_id').getValue();
			var polNum = base_form.findField('Polis_Num').getValue();
			var edNum = base_form.findField('Federal_Num').getValue();
			var polTer = terr_combo.getValue();
			var polDB =  base_form.findField('Polis_begDate').getValue();
			var polDE =  base_form.findField('Polis_endDate').getValue();

			if (getRegionNick().inlist(['kareliya','astra', 'buryatiya']) ) {
				// Территория страхования - Карелия, Астрахань, Бурятия
				terr_combo.getStore().each(function(rec) {
					if ( rec.get('OMSSprTerr_Code') == 1 ) {
						if (getRegionNick().inlist(['buryatiya']) ) {
							if ( rec.get('OMSSprTerr_Name') == 'БУРЯТИЯ РЕСПУБЛИКА' ) { // костыль для бурятии потому что у них повторяются два кода!
								terr_combo.setValue(rec.get('OMSSprTerr_id'));
							terr_combo.fireEvent('change', terr_combo, rec.get('OMSSprTerr_id'));
							}
						}else {
							terr_combo.setValue(rec.get('OMSSprTerr_id'));
							terr_combo.fireEvent('change', terr_combo, rec.get('OMSSprTerr_id'));
						}
					}
				});
			}else if(getRegionNick()=='pskov'){
				terr_combo.getStore().each(function(rec) {
					if ( rec.get('OMSSprTerr_Code') == 26 ) {
						terr_combo.setValue(rec.get('OMSSprTerr_id'));
						terr_combo.fireEvent('change', terr_combo, rec.get('OMSSprTerr_id'));
					}
				})
			}
			// Тип полиса - ОМС
			base_form.findField('PolisType_id').getStore().each(function(rec) {
				if ( rec.get('PolisType_Code') == 1 ) {
					base_form.findField('PolisType_id').setValue(rec.get('PolisType_id'));
					base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), rec.get('PolisType_id'));
				}
			});

			// Фамилия
			if (data.FAM) {
				base_form.findField('Person_SurName').setValue(data.FAM);
			}
			// Имя
			if (data.NAM) {
				base_form.findField('Person_FirName').setValue(data.NAM);
			}
			// Отчество
			if (data.FNAM) {
				base_form.findField('Person_SecName').setValue(data.FNAM);
			}
			// Дата рождения
			if (data.BORN_DATE) {
				base_form.findField('Person_BirthDay').setValue(getValidDT(data.BORN_DATE, ''));
			}
			// Телефон пациента
			// https://redmine.swan.perm.ru/issues/31518
			if ( !!data.PersonPhone_Phone && !Ext.isEmpty(data.PersonPhone_Phone) && (getRegionNick() != 'kareliya' || Ext.isEmpty(base_form.findField('PersonPhone_Phone').getValue())) ) {
				var tmp_phone = data.PersonPhone_Phone;
				var phone = '';
				var index = 0;
				if(tmp_phone[0]=='+')
					index = 2;
				else
					index = 1;

				if ( getRegionNick() == 'kareliya' && index > 0 ) {
					index = index - 1;
				}

				tmp_phone = tmp_phone.replace('-','');
				for(var i=index; i<tmp_phone.length; i++)
				{
					phone = phone+tmp_phone[i];
				}
				//alert(phone.replace(/[^0-9]/gim,''));
				base_form.findField('PersonPhone_Phone').setValue(phone);
				//base_form.findField('PersonPhone_Phone').setValue(data.PersonPhone_Phone);
			}

			// Если с сервера пришел тип полиса, то мы его и проставим
			// Пока только для Карелии, Астрахани, Бурятии
			if (getRegionNick().inlist(['kareliya', 'astra', 'buryatiya', 'pskov']) && data.PolisType_id ) {
				base_form.findField('PolisType_id').setValue(data.PolisType_id);
				base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
			}

			// Серия полиса
			if ( data.POL_SER ) {
				base_form.findField('Polis_Ser').setValue(data.POL_SER);

				if (getRegionNick().inlist(['kareliya','astra','buryatiya','pskov'])) {
					if (!data.PolisType_id) {
						if ( data.POL_SER == 'ЕНП' ) {
							// ЕНП
							base_form.findField('Polis_Ser').setValue('');
							base_form.findField('Polis_Ser').disable();
							base_form.findField('PolisType_id').setValue(4);
						} else if ( !isNaN(parseInt(data.POL_SER)) && isFinite(data.POL_SER) ) {
							if(getRegionNick()=='kareliya'){
								base_form.findField('Polis_Ser').setValue('');
								base_form.findField('Polis_Ser').disable();
								base_form.findField('PolisType_id').setValue(3);
							}else{
								// временное, серия - цифры
								base_form.findField('Polis_Ser').enable();
								base_form.findField('PolisType_id').setValue(3);
							}
						} else {
							// отсальные - ОМС старого образца
							base_form.findField('Polis_Ser').enable();
							base_form.findField('PolisType_id').setValue(1);
						}
						base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
					} else {
						if ( data.POL_SER == 'ЕНП' || data.PolisType_id == 4 ) { // ЕНП: первое условие для старого варианта идентификации, второе для нового
							base_form.findField('Polis_Ser').setValue('');
							base_form.findField('Polis_Ser').disable();
						} else {
							base_form.findField('Polis_Ser').enable();
						}
					}
				}
			}

			// Номер полиса
			if ( data.POL_NUM_16 ) {


				if ( getRegionNick().inlist(['kareliya','astra','buryatiya','pskov']) ) { // для карелии проставляем Federal_Num, если есть либо тип полиса, либо в серии написано ЕНП (старый способ идентификации)
					if ((data.POL_SER && data.POL_SER == lang['enp']) || (data.PolisType_id && data.PolisType_id == 4)) {
						base_form.findField('Federal_Num').setValue(data.POL_NUM_16);
						base_form.findField('Polis_Ser').setValue('');
						base_form.findField('Polis_Ser').disable();
						base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
					} else {
						if ( getRegionNick() == 'buryatiya' && data.ENP ) {
							base_form.findField('Federal_Num').setValue(data.ENP);
						}
						base_form.findField('Polis_Ser').enable();
						base_form.findField('Polis_Num').setValue(data.POL_NUM_16);
						base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
					}
				}

				if (getRegionNick() == 'ufa') {
					if ( data.POL_NUM_16.length > 0 ) {
						var pbd = base_form.findField('Person_BirthDay').getValue();

						if ( data.POL_NUM_16.length == 9 ) {
							base_form.findField('Polis_Num').setValue(data.POL_NUM_16);
							base_form.findField('PolisType_id').setValue(3);
							base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
						}
						else if ( data.POL_NUM_16.length == 16 && data.POL_NUM_16.substr(3, 6) == Ext.util.Format.date((typeof pbd == 'object' ? pbd : getValidDT(pbd, '')), 'Ym') ) {
							base_form.findField('Polis_Num').setValue(data.POL_NUM_16);
							base_form.findField('PolisType_id').setValue(1);
							base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
						}
						else {
							base_form.findField('PolisType_id').setValue(4);
							base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
							base_form.findField('Federal_Num').setValue(data.POL_NUM_16);
						}


					}
				}
			}
			if(getRegionNick() == 'ufa'&&data.BDZGUID){
				base_form.findField('BDZ_Guid').setValue(data.BDZGUID)
			}
			// Дата выдачи полиса
			if ( data.GIV_DATE ) {
				base_form.findField('Polis_begDate').setValue(data.GIV_DATE);
				base_form.findField('Polis_endDate').setMinValue(data.GIV_DATE);
				if(getRegionNick() == 'ufa'&&data.POLISGUID){
					base_form.findField('Polis_Guid').setValue(data.POLISGUID)
				}
			}

			// Дата окончания действия полиса
			if ( data.ELIMIN_DATE ) {
				base_form.findField('Polis_endDate').setValue(data.ELIMIN_DATE);

				if (!Ext.isEmpty(data.ELIMIN_DATE) && getRegionNick().inlist(['kareliya'])) {
					var msg = "Полис пациента на данный момент не действителен. " +
						"Если вы хотите отказаться от обновления данных, нажмите на форме кнопку отмена. " +
						"При этом форма закроется без сохранения.";
					showSysMsg(msg, null, 'info', {delay: 15000});
				}
			}

			// ИНН
			if ( data.INN ) {
				base_form.findField('PersonInn_Inn').setValue(data.INN);
			}

			// СНИЛС
			if ( data.SNILS ) {
				base_form.findField('Person_SNILS').setValue(data.SNILS);
			}
			else if ( data.ss ) {
				base_form.findField('Person_SNILS').setValue(data.ss);
			}

			// Пол
			if ( data.Sex_Code ) {
				base_form.findField('PersonSex_id').getStore().each(function(rec) {
					if ( rec.get('Sex_Code') == data.Sex_Code ) {
						base_form.findField('PersonSex_id').setValue(rec.get('Sex_id'));
					}
				});
			}

			// Соц. статус
			if ( !Ext.isEmpty(data.CATEG) ) {
				base_form.findField('SocStatus_id').getStore().each(function(rec) {
					if (
						rec.get('SocStatus_SysNick') == data.CATEG
						&& (getRegionNick() != 'kareliya' || Ext.isEmpty(base_form.findField('SocStatus_id').getValue()))
					) {
						base_form.findField('SocStatus_id').setValue(rec.get('SocStatus_id'));
					}
				});
			}

			if(
				getRegionNick() == 'pskov'&&
				polSer == base_form.findField('Polis_Ser').getValue()&&
				//polType == base_form.findField('PolisType_id').getValue()&&
				polNum == base_form.findField('Polis_Num').getValue()&&
				edNum == base_form.findField('Federal_Num').getValue()&&
				//data.OrgSmo_id == base_form.findField('OrgSMO_id').getValue()&&
				//polDB ==  base_form.findField('Polis_begDate').getValue()&&
				//polDE ==  base_form.findField('Polis_endDate').getValue()&&
				polTer != terr_combo.getValue()
			){
				terr_combo.setValue(polTer);
				terr_combo.fireEvent('change', terr_combo, polTer);
			}
			// СМО
			if ( data.OrgSmo_id ) {
				base_form.findField('OrgSMO_id').getStore().each(function(rec) {
					if ( rec.get('OrgSMO_id') == data.OrgSmo_id ) {
						base_form.findField('OrgSMO_id').setValue(rec.get('OrgSMO_id'));
					}
				});
			}
			if(getRegionNick() != 'astra'){
				// Тип документа
				if ( data.DOC_TYPE ) {
					base_form.findField('DocumentType_id').getStore().each(function(rec) {
						if ( rec.get('DocumentType_Code') == data.DOC_TYPE ) {
							base_form.findField('DocumentType_id').setValue(rec.get('DocumentType_id'));
							base_form.findField('DocumentType_id').fireEvent('select', base_form.findField('DocumentType_id'), rec, rec.get('DocumentType_id'));
							base_form.findField('DocumentType_id').fireEvent('blur', base_form.findField('DocumentType_id'));
						}
					});
				}

				// Серия документа
				if ( data.DOC_SER ) {
					base_form.findField('Document_Ser').setValue(data.DOC_SER);
				}

				// Номер документа
				if ( data.DOC_NUM ) {
					base_form.findField('Document_Num').setValue(data.DOC_NUM);
				}

				// Дата выдачи документа
				if ( data.Document_begDate ) {
					base_form.findField('Document_begDate').setValue(data.Document_begDate);
				}
			}
			if ( data.KLRgn_rid || data.KLSubRgn_rid || data.KLCity_rid || data.KLTown_rid || data.PersonSprTerrDop_rid ||
				data.KLStreet_rid || data.HOUSE || data.CORP || data.FLAT
			) {
				base_form.findField('UKLCountry_id').setValue('');
				base_form.findField('UKLRGN_id').setValue('');
				base_form.findField('UKLSubRGN_id').setValue('');
				base_form.findField('UKLCity_id').setValue('');
				base_form.findField('UKLTown_id').setValue('');
				base_form.findField('UPersonSprTerrDop_id').setValue('');
				base_form.findField('UKLStreet_id').setValue('');
				base_form.findField('UAddress_House').setValue('');
				base_form.findField('UAddress_Corpus').setValue('');
				base_form.findField('UAddress_Flat').setValue('');
				base_form.findField('UAddress_Address').setValue('');
				base_form.findField('UAddress_AddressText').setValue('');
			}

			// Страна
			if ( data.KLCountry_rid ) {
				base_form.findField('UKLCountry_id').setValue(data.KLCountry_rid);
			}

			// Индекс
			if ( !Ext.isEmpty(data.KLAdr_Index) ) {
				base_form.findField('UAddress_Zip').setValue(data.KLAdr_Index);
			}

			// Регион
			if ( data.KLRgn_rid ) {
				base_form.findField('UKLRGN_id').setValue(data.KLRgn_rid);
			}

			// Район
			if ( data.KLSubRgn_rid ) {
				base_form.findField('UKLSubRGN_id').setValue(data.KLSubRgn_rid);
			}

			// Город
			if ( data.KLCity_rid ) {
				base_form.findField('UKLCity_id').setValue(data.KLCity_rid);
			}

			// Населенный пункт
			if ( data.KLTown_rid ) {
				base_form.findField('UKLTown_id').setValue(data.KLTown_rid);
			}

			// Район Уфы
			if ( data.PersonSprTerrDop_rid ) {
				base_form.findField('UPersonSprTerrDop_id').setValue(data.PersonSprTerrDop_rid);
			}

			// Улица
			if ( data.KLStreet_rid ) {
				base_form.findField('UKLStreet_id').setValue(data.KLStreet_rid);
			}

			// Дом
			if ( data.HOUSE ) {
				base_form.findField('UAddress_House').setValue(data.HOUSE);
			}

			// Корпус
			if ( data.CORP ) {
				base_form.findField('UAddress_Corpus').setValue(data.CORP);
			}

			// Квартира
			if ( data.FLAT ) {
				base_form.findField('UAddress_Flat').setValue(data.FLAT);
			}

			// Текстовое значение адреса
			if ( data.RAddress_Name ) {
				base_form.findField('UAddress_Address').setValue(data.RAddress_Name);
				base_form.findField('UAddress_AddressText').setValue(data.RAddress_Name);
			}

			// Если задано предупреждение...
			if ( data.Alert_Msg && data.Alert_Msg.toString().length > 0 ) {
				// ... выводим предупреждение
				sw.swMsg.alert(lang['preduprejdenie'], data.Alert_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
			}

			var allowDisableFields = false;
			if (getRegionNick().inlist(['kareliya','astra'])) {
				allowDisableFields = base_form.findField('Person_IsInErz').getValue().toString().inlist(['2']);
			} else {
				allowDisableFields = base_form.findField('PersonIdentState_id').getValue().toString().inlist(['1', '3']);
			}

			// Блокируем поля от дальнейшего изменения данных пользователем
			if ( allowDisableFields && !isSuperAdmin() ) {
				if (disableFields) {
					// https://redmine.swan.perm.ru/issues/11587
					// 3) При идентификации снять блокировку со всех полей , кроме полей полисных данных , Фамилия , Имя, Отчество, Дата рождения.
					base_form.findField('OMSSprTerr_id').disable();
					base_form.findField('PolisType_id').disable();
					base_form.findField('Person_SurName').disable();
					base_form.findField('Person_FirName').disable();
					base_form.findField('Person_SecName').disable();
					base_form.findField('Person_BirthDay').disable();
					base_form.findField('Polis_Ser').disable();
					base_form.findField('Polis_Num').disable();
					base_form.findField('Federal_Num').disable();
					base_form.findField('Polis_begDate').disable();
					base_form.findField('Polis_endDate').disable();
					// base_form.findField('PersonInn_Inn').disable();
					// base_form.findField('PersonNationality_id').disable();
					// base_form.findField('Person_SNILS').disable();
					// base_form.findField('PersonSex_id').disable();
					// base_form.findField('SocStatus_id').disable();
					base_form.findField('OrgSMO_id').disable();
					// base_form.findField('DocumentType_id').disable();
					// base_form.findField('Document_Ser').disable();
					// base_form.findField('Document_Num').disable();
					// base_form.findField('UAddress_AddressText').disable();
				}
			}
		}
		else {
			base_form.findField('OMSSprTerr_id').enable();
			base_form.findField('PolisType_id').enable();
			base_form.findField('Person_SurName').enable();
			base_form.findField('Person_FirName').enable();
			base_form.findField('Person_SecName').enable();
			base_form.findField('Person_BirthDay').enable();
			base_form.findField('Polis_Ser').enable();
			if (base_form.findField('PolisType_id').getValue() != 4) { // для ЕНП поле задисаблено должно оставаться
				base_form.findField('Polis_Num').enable();
			}
			base_form.findField('Federal_Num').enable();
			base_form.findField('Polis_begDate').enable();
			base_form.findField('Polis_endDate').enable();
			base_form.findField('PersonInn_Inn').enable();
			//base_form.findField('PersonNationality_id').enable();
			base_form.findField('Person_SNILS').enable();
			base_form.findField('PersonSex_id').enable();
			base_form.findField('SocStatus_id').enable();
			base_form.findField('OrgSMO_id').enable();
			base_form.findField('DocumentType_id').enable();
			if (!Ext.isEmpty(base_form.findField('DocumentType_id').getValue())) {
				// данные поля доступными должны быть только при заполненном типе документа
				base_form.findField('Document_Ser').enable();
				base_form.findField('Document_Num').enable();
			}
			base_form.findField('UAddress_AddressText').enable();
		}

		// надо закрыть поля полиса, если не выбрана территория! (refs #16852)
		if (Ext.isEmpty(base_form.findField('OMSSprTerr_id').getValue())) {
			this.disablePolisFields(true, true);
		}
		this.visibleChangeFields();
		base_form.clearInvalid();

		return true;
	},
	checkPersonDoubles: function() {
		var win = this;
/*
		if (this.action != 'add')
		{
			this.doSubmit();
			return;
		}
*/
		var base_form = this.findById('person_edit_form').getForm();
		var params = new Object();

		if ( base_form.findField('Person_id').getValue() > 0 && base_form.findField('PersonIdentState_id').getValue() == '1' ) {
			this.doSubmit();
			return true;
		}

		params.Person_id = this.personId;
		params.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
		params.Person_FirName = base_form.findField('Person_FirName').getValue();
		params.Person_SecName = base_form.findField('Person_SecName').getValue();
		params.Person_SurName = base_form.findField('Person_SurName').getValue();
		params.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		params.Polis_Num = base_form.findField('Polis_Num').getValue();
		params.Person_Inn = base_form.findField('PersonInn_Inn').getValue();
		params.Federal_Num = base_form.findField('Federal_Num').getValue();
		params.Person_IsUnknown = base_form.findField('Person_IsUnknown').checked ? 2 : 1;
		//var oms_spr_terr_record = base_form.findField('OMSSprTerr_id').getStore().getById(base_form.findField('OMSSprTerr_id').getValue());

		//if ( oms_spr_terr_record && Number(oms_spr_terr_record.get('OMSSprTerr_Code')) > 100 ) {
			params.OMSSprTerr_id = base_form.findField('OMSSprTerr_id').getValue();
	    //}
		if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
			params.RegistryType_id = g_RegistryType_id;
		win.getLoadMask(lang['podojdite_idet_proverka_dvoynikov']).show();

		Ext.Ajax.request({
			url: '/?c=Person&m=checkPersonDoubles',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response) {
				win.getLoadMask().hide();

				if ( success ) {
					if ( response.responseText.length > 0 ) {
						var resp_obj = Ext.util.JSON.decode(response.responseText);

						if ( resp_obj.success == false ) {
							if ( resp_obj.Error_Code && resp_obj.Error_Code == 666 && resp_obj.Person_id && resp_obj.Server_id ) {
								sw.swMsg.show({
									title: lang['proverka_dublya_po_serii_i_nomeru_polisa'],
									msg: lang['seriya_i_nomer_polisa_sovpadayut_s_dannyimi_polisa_drugogo_cheloveka_otkryit_ego_na_redaktirovanie'],
									buttons: Ext.Msg.YESNO,
									fn: function ( buttonId ) {
										if ( buttonId == 'yes' ) {
											this.show({
												action: 'edit',
												Person_id: resp_obj.Person_id,
												Server_id: resp_obj.Server_id,
												callback: this.returnFunc,
												onClose: this.onClose
											});
										}
										else {
											base_form.findField('Person_SurName').focus(true, 100);
										}
									}.createDelegate(this)
								});
							}
							else
							if ( resp_obj.Error_Code && resp_obj.Error_Code == 444 ) {
								Ext.Msg.alert(
									lang['proverka_inn'],
									resp_obj.Error_Msg,
									function() {
										base_form.findField('Person_SurName').focus(true, 100);
										return;
									}
								);
							}
							else
							{
								Ext.Msg.alert(
									lang['oshibka'],
									resp_obj.Error_Msg,
									function() {
										base_form.findField('Person_SurName').focus(true, 100);
										return;
									}
								);
							}
						}
						else {
							if(this.childAdd){
								this.checkChildrenDuplicates();
							}else{
								if(getRegionNick() == 'kz'
									|| getGlobalOptions().snils_double_control==1 //1 == Отключен контроль дублирования СНИЛС
									|| base_form.findField('Person_SNILS').getValue()=='')
									this.doSubmit();
								else
									this.checkSnilsDoubles();
							}
						}
					}
				}
			}.createDelegate(this)
		});
	},
	checkSnilsDoubles: function() {
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();
		var params = new Object();
		params.Person_SNILS = base_form.findField('Person_SNILS').getValue();
		params.Person_id = base_form.findField('Person_id').getValue();
		Ext.Ajax.request({
			url: '/?c=Person&m=checkSnilsDoubles',
			params: params,
			timeout: 1800000,
			callback: function (options, success, response) {
				win.getLoadMask().hide();
				if (success) {
					if (response.responseText.length > 0) {
						var resp = Ext.util.JSON.decode(response.responseText);
						if ( resp.success == false ) {
							if(getGlobalOptions().snils_double_control==2) { //2 == Предупреждение о дублировании СНИЛС
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId) {
										if ('yes' == buttonId) {
											win.doSubmit();
										} else {
											base_form.findField('Person_SNILS').focus(true, 100);
										}
									},
									icon: Ext.Msg.WARNING,
									msg: langs('Введенный Вами СНИЛС указан у пациента '+resp.Surname+' '+resp.Name+' '+resp.SecName+', '+resp.BirthDay+' рождения. Продолжить сохранение?'),
									title: langs('Внимание!')
								});
								return;
							} else
							if(getGlobalOptions().snils_double_control==3) { //3 == Запрет на сохранение
								Ext.Msg.alert(
									langs('Ошибка'),
									langs('Введенный Вами СНИЛС указан у пациента '+resp.Surname+' '+resp.Name+' '+resp.SecName+', '+resp.BirthDay+' рождения. Сохранение невозможно.'),
									function() {
										base_form.findField('Person_SNILS').focus(true, 100);
									}
								);
								return;
							}
						}
						win.doSubmit();
					}
				}
			}
		});
	},
    askPrintNewslatterAccept: function(params) {

		if (!params || !params.NewslatterAccept_id) {
			return false;
		}

		var win = this;

		if (Ext.isEmpty(params.NewslatterAccept_endDate)) {

			sw.swMsg.show({
				title: lang['vopros'],
				msg: lang['raspechatat_dokument'],
				icon: Ext.MessageBox.QUESTION,
				buttons: Ext.Msg.YESNO,
				fn: function ( buttonId ) {
					if ( buttonId == 'yes' ) {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					}
				}
			});
		} else {

			sw.swMsg.show({
				title: lang['vopros'],
				msg: lang['raspechatat_dokument'],
				icon: Ext.MessageBox.QUESTION,
				buttons: {
					yes: lang['pechat_soglasiya'],
					no: lang['pechat_otkaza'],
					cancel: lang['otmena']
				},
				fn: function( buttonId ) {
					if ( buttonId == 'yes') {
						win.printNewslatterAccept('printAccept', params.NewslatterAccept_id);
					} else if ( buttonId == 'no') {
						win.printNewslatterAccept('printDenial', params.NewslatterAccept_id);
					}
				}
			});
		}
    },
    printNewslatterAccept: function(method) {

		if (!method.inlist(['printAccept', 'printDenial'])) {
			return false;
		}

		var grid = this.NewslatterAcceptGrid.getGrid();
		var record = grid.getSelectionModel().getSelected();

		if (!record || Ext.isEmpty(record.get('NewslatterAccept_id'))) {
			return false;
		}

		window.open('/?c=NewslatterAccept&m=' + method + '&NewslatterAccept_id=' + record.get('NewslatterAccept_id'), '_blank');
		return true;
    },

	periodicFields: [
		'Person_SurName',
		'Person_SecName',
		'Person_FirName',
		'Person_BirthDay',
		'PersonPhone_Phone',
		//'PersonPhone_Comment',
		'FamilyStatus_id',
		'PersonFamilyStatus_IsMarried',
		'PersonInn_Inn',
		//'PersonNationality_id',
		'PersonSocCardNum_SocCardNum',
		'PersonRefuse_IsRefuse',
		'PersonChildExist_IsChild',
		'PersonCarExist_IsCar',
		'Person_SNILS',
		'PersonSex_id',
		'SocStatus_id',
		'OMSSprTerr_id',
		'PolisType_id',
		'Polis_Ser',
		'Polis_Num',
		'Federal_Num',
		'OrgSMO_id',
		'Polis_begDate',
		'Polis_endDate',
		'Document_Ser',
		'Document_Num',
		'DocumentType_id',
		'OrgDep_id',
		'Document_begDate',
		'KLCountry_id',
		'NationalityStatus_IsTwoNation',
		'LegalStatusVZN_id',
		'UAddress_Zip',
		'UKLCountry_id',
		'UKLRGN_id',
		'UKLSubRGN_id',
		'UKLCity_id',
		'UKLTown_id',
		'UKLStreet_id',
		'UAddress_House',
		'UAddress_Corpus',
		'UAddress_Flat',
		'UAddressSpecObject_id',
		'UAddressSpecObject_Value',
		'PAddress_Zip',
		'PKLCountry_id',
		'PKLRGN_id',
		'PKLSubRGN_id',
		'PKLCity_id',
		'PKLTown_id',
		'PKLStreet_id',
		'PAddress_House',
		'PAddress_Corpus',
		'PAddress_Flat',
		'PAddressSpecObject_id',
		'PAddressSpecObject_Value',
		'BAddress_Zip',
		'BKLCountry_id',
		'BKLRGN_id',
		'BKLSubRGN_id',
		'BKLCity_id',
		'BKLTown_id',
		'BKLStreet_id',
		'BAddress_House',
		'BAddress_Corpus',
		'BAddress_Flat',
		'BAddressSpecObject_id',
		'BAddressSpecObject_Value',
		'Org_id',
		'OrgUnion_id',
		'Post_id',
		'DeputyKind_id',
		'DeputyPerson_id'
	],
	periodicSingleFields: [
		'Person_SurName',
		'Person_SecName',
		'Person_FirName',
		'PersonPhone_Phone',
		//'PersonPhone_Comment',
		'FamilyStatus_id',
		'PersonFamilyStatus_IsMarried',
		'PersonInn_Inn',
		//'PersonNationality_id',
		'PersonSocCardNum_SocCardNum',
		'PersonRefuse_IsRefuse',
		'PersonChildExist_IsChild',
		'PersonCarExist_IsCar',
		'Person_BirthDay',
		'Person_SNILS',
		'PersonSex_id',
		'SocStatus_id',
		'Federal_Num'
	],
	periodicStructFields: {
		'Deputy': [
			'DeputyKind_id',
			'DeputyPerson_id'
		],
		'BDZ': [
			'BDZ_Guid'
		],
		'Polis': [
			'OMSSprTerr_id',
			'PolisType_id',
			'Polis_Ser',
			'Polis_Num',
			'PolisFormType_id',
			'OrgSMO_id',
			'Polis_begDate',
			'Polis_endDate',
			'Federal_Num',
			'Polis_Guid'
		],
		'Document': [
			'Document_Ser',
			'Document_Num',
			'DocumentType_id',
			'OrgDep_id',
			'Document_begDate'
		],
		'NationalityStatus': [
			'KLCountry_id',
			'NationalityStatus_IsTwoNation',
			'LegalStatusVZN_id'
		],
		'UAddress': [
			'UAddress_Zip',
			'UKLCountry_id',
			'UKLRGN_id',
			'UKLSubRGN_id',
			'UKLCity_id',
			'UKLTown_id',
			'UKLStreet_id',
			'UAddress_House',
			'UAddress_Corpus',
			'UAddress_Flat',
			'UAddressSpecObject_id',
			'UAddressSpecObject_Value',
			'UAddress_Address'
			//'UAddress_begDate'
		],
		'BAddress': [
			'BAddress_Zip',
			'BKLCountry_id',
			'BKLRGN_id',
			'BKLSubRGN_id',
			'BKLCity_id',
			'BKLTown_id',
			'BKLStreet_id',
			'BAddress_House',
			'BAddress_Corpus',
			'BAddress_Flat',
			'BAddressSpecObject_id',
			'BAddressSpecObject_Value',
			'BAddress_Address'
		],
		'PAddress': [
			'PAddress_Zip',
			'PKLCountry_id',
			'PKLRGN_id',
			'PKLSubRGN_id',
			'PKLCity_id',
			'PKLTown_id',
			'PKLStreet_id',
			'PAddress_House',
			'PAddress_Corpus',
			'PAddress_Flat',
			'PAddressSpecObject_id',
			'PAddressSpecObject_Value',
			'PAddress_Address',
			//'PAddress_begDate'
		],
		'Job': [
			'Org_id',
			'OrgUnion_id',
			'Post_id'
		]
	},
	notPeriodicStructFields: {
		'Person': [
			'Person_Comment',
			'Person_deadDT',
			'Person_IsUnknown'
		],
		'PersonChild': [
			'PersonChild_id',
			'ResidPlace_id',
			'PersonChild_IsManyChild',
			'PersonChild_IsBad',
			'PersonChild_IsYoungMother',
			'PersonChild_IsIncomplete',
			'PersonChild_IsTutor',
			'PersonChild_IsMigrant',
			'HealthKind_id',
			'FeedingType_id',
			'PersonChild_CountChild',
			'PersonChild_IsInvalid',
			'InvalidKind_id',
			'PersonChild_invDate',
			'HealthAbnorm_id',
			'HealthAbnormVital_id',
			'Diag_id',
			'PersonSprTerrDop_id'
		]
	},
	getChangedFields: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		var changed_fields = [];
		if(getRegionNick()=='ufa'){
			this.periodicFields.push('UPersonSprTerrDop_id');
			this.periodicStructFields.UAddress.push('UPersonSprTerrDop_id');
			base_form.findField('UPersonSprTerrDop_id').enable();
			this.periodicFields.push('PPersonSprTerrDop_id');
			this.periodicStructFields.PAddress.push('PPersonSprTerrDop_id');
			base_form.findField('PPersonSprTerrDop_id').enable();
			this.periodicFields.push('BPersonSprTerrDop_id');
			this.periodicStructFields.BAddress.push('BPersonSprTerrDop_id');
			base_form.findField('BPersonSprTerrDop_id').enable();
		}
		for (var key in this.oldValuesToRestore)
		{
			var field = base_form.findField(key);
			// если проидентифицирован, то тоже можно эти поля сохранять
			if ( !base_form.findField(key).disabled || (getRegionNick().inlist(['astra','ufa','kareliya','pskov']) && base_form.findField('PersonIdentState_id').getValue() == '1') )
			{
				if (
					(this.oldValuesToRestore != null &&
					(field &&
					!((field && ( field.getValue() == null || field.getValue() == '' )) &&
					(this.oldValuesToRestore[key] == null || this.oldValuesToRestore[key] == '')) &&
					(( field.getXType() == 'swdatefield' &&
					Ext.util.Format.date(field.getValue(), 'd.m.Y') != this.oldValuesToRestore[key] ) ||
					(field.getXType() != 'swdatefield' && field.getValue() != this.oldValuesToRestore[key])))) ||
					(this.oldValuesToRestore == null && !(field.getValue() == null || field.getValue() == ''))
				) {
					// проверяем в каких списках находится измененное поле (в одиночных периодиках или в структурных)
					var isStructField = false;
					for (var per_field in this.periodicStructFields) {
						if ( this.periodicStructFields[per_field].in_array(key) && !changed_fields.in_array(per_field) ) {
							changed_fields.push(per_field);
							isStructField = true;
						}
					}
					for (var per_field in this.notPeriodicStructFields) {
						if ( this.notPeriodicStructFields[per_field].in_array(key) && !changed_fields.in_array(per_field) ) {
							changed_fields.push(per_field);
							isStructField = true;
						}
					}
					if (!isStructField && !changed_fields.in_array(key)) {
						changed_fields.push(key);
					}
				}
			}
		}
		return changed_fields;
	},
	visibleChangeFields:function(){
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		//log('this.periodicFields',this.periodicFields)
		for (var i = 0; i < this.periodicFields.length; i++)
		{
			var key = this.periodicFields[i];
			var field = base_form.findField(key);
			// если проидентифицирован, то тоже можно эти поля сохранять
			if ( (getRegionNick() == 'astra' ) )
			{
				if (
					(this.oldValuesToRestore != null &&
					(field &&
					!((field.getValue() == null && this.oldValuesToRestore[key] == '')||(field.getValue() == '' && this.oldValuesToRestore[key] == null) )&&
					(( field.getXType() == 'swdatefield' &&
					Ext.util.Format.date(field.getValue(), 'd.m.Y') != this.oldValuesToRestore[key] ) ||
					(field.getXType() != 'swdatefield' && field.getValue() != this.oldValuesToRestore[key])))) ||
					(this.oldValuesToRestore == null && !(field.getValue() == null || field.getValue() == ''))
				)
				{
					log(base_form.findField(key))
					base_form.findField(key).addClass('change');
				}
			}
		}
	},
	setMaskPolisNum: function(){
		var base_form = this.findById('person_edit_form').getForm();
		var polis_region = base_form.findField('OMSSprTerr_id').getFieldValue('KLRgn_id');
		var system_region = getRegionNumber();
		var polisTypeID = base_form.findField('PolisType_id').getValue();
		var polisNumCombo = base_form.findField('Polis_Num');

		//Если тип полиса = «1. ОМС (старого образца)» и регион системы <> территории страхования, то для ввода доступны следующие символы: цифры (0-9), точка
		if(polisTypeID == 1 && getRegionNick() != 'kz' && polis_region != system_region){
			polisNumCombo.maskRe =/[0-9\.\/]/;
		}else{
			polisNumCombo.maskRe =/\d/;
		}
	},
	validationFormWithRegion: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		var is_ufa = (getRegionNick() == 'ufa');
		var notice = [];
		// проверка снилс
		if (!this.checkPersonSnils())
		{
			sw.swMsg.show({
				title: "Проверка поля СНИЛС",
				msg: "СНИЛС человека введен неверно! (не удовлетворяет правилам формирования СНИЛС)",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function () {
					base_form.findField('Person_SNILS').focus(true, 100);
				}.createDelegate(this)
			});
			return false;
		}

		if (is_ufa && base_form.findField('Polis_Num').getValue() != '' && !this.checkPolisNum())
		{
			switch (Number(base_form.findField('PolisType_id').getValue())) {
				case 1://ОМС старого образца - выводим только лишь предупреждение
					notice.push("<BR />- Номер полиса заполнен неверно, проверьте правильность заполнения");
					break;
				case 2://ДМС - проверка была отключена
				case 4://ОМС единого образца - проверка была отключена
					break;
				default:
					sw.swMsg.show({
						title: "Проверка номера полиса",
						msg: "Номер полиса заполнен неверно, проверьте правильность заполнения.",
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.WARNING,
						fn: function () {
							base_form.findField('Polis_Num').focus(true, 100);
						}.createDelegate(this)
					});
					return false;
					break;
			}
		}
		// Проверка пола
		var SexField = base_form.findField('PersonSex_id');
		if ( SexField.getValue() != '' )
		{
			var Sex_id = SexField.getValue();
			var SecName = new String(base_form.findField('Person_SecName').getValue());
			var SurName = new String(base_form.findField('Person_SurName').getValue());
			var isMen = false;
			var isWomen = false;
			var sex_error = false;
			switch (getRegionNick())
			{
				case 'ufa':
					if (SecName.length = 0)
					{
						notice.push("<BR />- Вы не ввели Отчество человека");
						break;
					}
					//отключить проверку на пол, если отчество не задано или стоит НЕТ. (refs #7075)
					if ((SecName.length != 0) && (SecName != lang['net'])) {
						//Изменить проверку на пол в соответствии с корректировкой ТЗ (refs #89010)
						var SecNameEnd = SecName.substr(SecName.length-3,3).toLowerCase();
						if (SecNameEnd == 'вна')
							isWomen = true;
						else if(SecNameEnd == 'вич')
							isMen = true;

						if (isWomen && Sex_id == 1)
							sex_error = true;
						if (isMen && Sex_id == 2)
							sex_error = true;
						if (sex_error)
							notice.push("<BR />- Возможно, вы неправильно выбрали пол человека");
					}
					if (sex_error)
					{
						// https://redmine.swan.perm.ru/issues/11587
						// 5) Убрать "жесткий" контроль при проверке на соответствие пола отчеству. Не для всех работат корректно, например,
						// для китайцев.
						notice.push("<BR />- Возможно, вы неправильно выбрали пол человека");
/*
						sw.swMsg.show({
							title: "Проверка ввода пола человека",
							msg: "Возможно вы неправильно выбрали пол человека, проверьте правильность заполнения.",
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							fn: function () {
								SexField.focus(true, 100);
							}.createDelegate(this)
						});
						return false;
*/
					}
					//if (Sex_id == 3)
						//notice.push("<BR />- Вы выбрали, что пол человека не определен");
				break;
				default:
					if (SecName != '')
					{
						//Изменить проверку на пол в соответствии с корректировкой ТЗ (refs #89010)
						var SecNameEnd = SecName.substr(SecName.length-3,3).toLowerCase();
						if (SecNameEnd == 'вна')
							isWomen = true;
						else if(SecNameEnd == 'вич')
							isMen = true;

						if (isWomen && Sex_id == 1)
							sex_error = true;
						if (isMen && Sex_id == 2)
							sex_error = true;
						if (sex_error)
							notice.push("<BR />- Возможно, вы неправильно выбрали пол человека");
					}


			}
		}

		// для неизвестного данные проверки не нужны.
		if (!base_form.findField('Person_IsUnknown').checked) {
			// проверка Отсутствует территория страхования
			if (is_ufa && !base_form.findField('OMSSprTerr_id').getValue()) {
				notice.push("<BR />- Вы не ввели данные страхового полиса");
			}
			// проверка Не указан ни один из документов
			if (is_ufa && !base_form.findField('DocumentType_id').getValue()) {
				notice.push("<BR />- Вы не ввели документ");
			}
			// Не проставлена страховая организация
			if (is_ufa && !base_form.findField('OrgSMO_id').getValue()) {
				notice.push("<BR />- Не проставлена страховая организация");
			}
		}
		return notice;
	},
	// Изменение условий проверки документа в зависимости от территории полиса
	'changeDocVerificationDependingOMSTerr': function(doc_id) {
		if (!doc_id)
			return false;
		// Для Перми при выборе типа документа "Свидетельство о рождении"
		// для всех у кого полис не Пермского Края устанавливаем особые правила проверки серии и номера св-ва о рождении
		var base_form = this.findById('person_edit_form').getForm();
		if (getRegionNick() == 'perm' && doc_id == 3)
		{
			//var OMSSprTerr_view = this.findById('person_edit_form').getForm().findField('OMSSprTerr_id').view;
			//var OMSSprTerrSelected = (OMSSprTerr_view)?OMSSprTerr_view.getSelectedRecords():[];
			var record = base_form.findField('OMSSprTerr_id').getStore().getById(base_form.findField('OMSSprTerr_id').getValue());
			if(record && record.get('KLRgn_id') != 59)
			{
				Ext.getCmp('PEW_Document_Ser').allowBlank = true;
				Ext.getCmp('PEW_Document_Ser').clearInvalid();
				Ext.getCmp('PEW_Document_Ser').regex = new RegExp('^[1-9A-Z\-А-Я]{0,10}$');
				Ext.getCmp('PEW_Document_Num').regex = new RegExp('^[0-9]{1,20}$');
			}
			else
			{
				if (base_form.findField('Person_IsUnknown').checked) {
					Ext.getCmp('PEW_Document_Ser').allowBlank = true;
				} else {
					Ext.getCmp('PEW_Document_Ser').allowBlank = false;
				}
				Ext.getCmp('PEW_Document_Ser').regex = new RegExp('^[IVXLC1УХЛС]{1,}\-[А-Я]{2}$');
				Ext.getCmp('PEW_Document_Num').regex = new RegExp('^[0-9]{6}$');
			}
		}
	},
	doCheckAndSaveOnThePersonEvn: function(options) {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		if ( this.readOnly )
			return;
		var oldValues = this.oldValues;
		var action = this.action;
		options = options || {};

		if ( getRegionNick() == 'ufa' && base_form.findField('PersonInn_Inn').getValue().length == 11 ) {
			base_form.findField('PersonInn_Inn').setValue('0' + base_form.findField('PersonInn_Inn').getValue());
		}

		if ( !base_form.isValid() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			}
			);
		}
		else
		{
			// проверка номера полиса
			if (base_form.findField('Federal_Num').getValue() != '' && Number(base_form.findField('PolisType_id').getValue()) == 4){
				var polis_num = String(base_form.findField('Federal_Num').getValue());
				if (!checkEdNumFedSignature(polis_num) && getRegionNick() != 'kz' && !options.ignoreENPValidationControl) {
					switch (getGlobalOptions().enp_validation_control) {
						case 'warning':		// Выводим предупреждение с возможностью продолжения
							sw.swMsg.show({
								buttons: sw.swMsg.YESNO,
								fn: function(buttonId, text, obj) {
									if ('yes' == buttonId) {
										var options = {};
										options.ignoreENPValidationControl = 1;
										this.doCheckAndSaveOnThePersonEvn(options);
									} else {
										base_form.findField('Federal_Num').focus(true, 100);
									}
								}.createDelegate(this),
								icon: Ext.MessageBox.QUESTION,
								msg: "Единый номер полиса не соответствует формату. Продолжить сохранение?",
								title: lang['vopros']
							});
							return false;
						case 'deny':		// Выводим сообщение об ошибке
							sw.swMsg.show({
								title: "Проверка номера полиса",
								msg: "Единый номер полиса не соответствует формату",
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								fn: function () {
									base_form.findField('Federal_Num').focus(true, 100);
								}
							});
							return false;
					}
				}
			}
			var notice = this.validationFormWithRegion();
			if (notice)
			{
				if (notice.length > 0)
					sw.swMsg.show({
						title: lang['preduprejdenie'],
						msg: lang['obnarujenyi_vozmojnyie_oshibki'] + notice.toString() + lang['podtverjdaete_sohranenie'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
								this.doSaveOnThePersonEvn();
						}.createDelegate(this)
					});
				else
				{
					this.doSaveOnThePersonEvn();
				}
			}
		}
	},
	doSaveOnThePersonEvn: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();

		/*if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Человек старше двух месяцев. Имя должно быть заполнено.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_FirName').focus(true, 100);
				}
			});
			return false;
		}*/

		var win = this;
		var isPeriodicField = function(field) {
			return win.periodicSingleFields.in_array(field) || win.periodicStructFields[field];
		};
		var isNotPeriodicField = function(field) {
			return win.notPeriodicStructFields[field];
		};
		var changed_fields = this.getChangedFields();
		// если поле одиночное, то тупо отправляем его значение
		if ( changed_fields.length > 0 ) {
			var saving_data = {};
			for ( var j = 0; j < changed_fields.length; j++ ) {
				var attribute = changed_fields[j];
				if ( this.periodicStructFields[attribute] ) {
					for ( var i = 0; i < this.periodicStructFields[attribute].length; i++ )
					{
						if ( base_form.findField(this.periodicStructFields[attribute][i]).getValue() instanceof Date ) {
							saving_data[this.periodicStructFields[attribute][i]] = Ext.util.Format.date(base_form.findField(this.periodicStructFields[attribute][i]).getValue(), 'd.m.Y');
						}
						else {
							saving_data[this.periodicStructFields[attribute][i]] = base_form.findField(this.periodicStructFields[attribute][i]).getValue();
						}
					}
				} else if ( this.notPeriodicStructFields[attribute] ) {
					for ( var i = 0; i < this.notPeriodicStructFields[attribute].length; i++ )
					{
						if ( base_form.findField(this.notPeriodicStructFields[attribute][i]).getValue() instanceof Date ) {
							saving_data[this.notPeriodicStructFields[attribute][i]] = Ext.util.Format.date(base_form.findField(this.notPeriodicStructFields[attribute][i]).getValue(), 'd.m.Y');
						}
						else {
							saving_data[this.notPeriodicStructFields[attribute][i]] = base_form.findField(this.notPeriodicStructFields[attribute][i]).getValue();
						}
					}
				} else {
					if ( base_form.findField(attribute).getValue() instanceof Date ) {
						saving_data[attribute] = Ext.util.Format.date(base_form.findField(attribute).getValue(), 'd.m.Y');
					}
					else {
						saving_data[attribute] = base_form.findField(attribute).getValue();
					}
				}
			}

			// сохраняем
			if ( getRegionNick() == 'ufa' ) {
				saving_data.PersonIdentState_id = base_form.findField('PersonIdentState_id').getValue();
			}

			var params = saving_data;
			params.Person_id = this.personId;
			params.Server_id = this.serverId;
			params.PersonEvn_id = this.personEvnId;
			params.Evn_setDT = this.Evn_setDT;
			params.EvnType = changed_fields.filter(isPeriodicField).join('|');
			params.NotEvnType = changed_fields.filter(isNotPeriodicField).join('|');
			params.refresh = true;
			if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
				params.RegistryType_id = g_RegistryType_id;
			if ( getRegionNick().inlist(['kareliya', 'astra', 'ufa', 'buryatiya']) ){

				params.PersonIdentState_id = base_form.findField('PersonIdentState_id').getValue();
			}
			win.getLoadMask(lang['podojdite_idet_sohranenie']).show();
			// отправляем запрос на сохранение
			Ext.Ajax.request({
				url: '/?c=Person&m=editPersonEvnAttributeNew',
				params: params,
				callback: function(options, success, response) {
					win.getLoadMask().hide();
					if ( success ) {
						if ( response.responseText.length > 0 ) {
							var resp_obj = Ext.util.JSON.decode(response.responseText);

							if ( resp_obj.success == false ) {
								if ( resp_obj.Error_Code && resp_obj.Error_Code == 666 && resp_obj.Person_id && resp_obj.Server_id ) {

								}
							} else {
								//this.findById('PVW_PeriodicViewGrid').loadData();
								win.returnFunc({
									Person_id: win.personId,
									Server_id: win.serverId,
									PersonEvn_id: win.personEvnId,
									PersonData: {
										Person_id: win.personId,
										Server_id: win.serverId,
										PersonEvn_id: win.personEvnId,
										Evn_setDT:win.Evn_setDT,
										Person_FirName: base_form.findField('Person_FirName').getValue(),
										Person_SurName: base_form.findField('Person_SurName').getValue(),
										Person_SecName: base_form.findField('Person_SecName').getValue(),
										Person_BirthDay: base_form.findField('Person_BirthDay').getValue(),
										PersonSex_id: base_form.findField('PersonSex_id').getValue(),
										UAddress_AddressText: base_form.findField('UAddress_AddressText').getValue(),
										PAddress_AddressText: base_form.findField('PAddress_AddressText').getValue(),
										Person_Age: swGetPersonAge(base_form.findField('Person_BirthDay').getValue(), new Date()),
										Person_Phone: win.findById('PEW_PersonPhone_Phone').getValue(),
										Person_Work_id: base_form.findField('Org_id').getValue(),
										Person_Work: base_form.findField('Org_id').getFieldValue('Org_Nick')
									}
								});
								getWnd('swPersonEditWindow').hide();
							}
						}
					}
				}.createDelegate(this)
			});
		}
		else
			Ext.MessageBox.show({
				title: "Сохранение атрибутов",
				msg: "Вы не изменили ни одного атрибута.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			});
	},
	doSaveOnDate: function() {
		if ( this.action != 'edit' )
			return;
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		if ( this.readOnly )
			return;
		var oldValues = this.oldValues;
		var action = this.action;

		if ( getRegionNick() == 'ufa' && base_form.findField('PersonInn_Inn').getValue().length == 11 ) {
			base_form.findField('PersonInn_Inn').setValue('0' + base_form.findField('PersonInn_Inn').getValue());
		}

		// @task https://redmine.swan-it.ru/issues/163758
		if (getRegionNick() == 'vologda') {
			var
				DocumentType_id = base_form.findField('DocumentType_id').getValue(),
				Document_begDate = base_form.findField('Document_begDate').getValue(),
				Person_BirthDay = base_form.findField('Person_BirthDay').getValue();

			if (
				!Ext.isEmpty(DocumentType_id) && !DocumentType_id.toString().inlist(['3','9','17','19','22'])
				&& !Ext.isEmpty(Document_begDate) && typeof Document_begDate == 'object'
				&& !Ext.isEmpty(Person_BirthDay) && typeof Person_BirthDay == 'object'
				&& swGetPersonAge(Person_BirthDay, Document_begDate) < 14
			) {
				sw.swMsg.show({
					title: "Проверка документа",
					msg: "Дата выдачи документа должна соответствовать дате 14-летия пациента или должна быть позже. Укажите корректную дату выдачи и тип документа.",
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					fn: function () {
						base_form.findField('DocumentType_id').focus(true, 100);
					}
				});
				return false;
			}
		}
		if ( !base_form.isValid() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			}
			);
		}
		else
		{
			/*if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
			{
				Ext.MessageBox.show({
					title: "Проверка данных формы",
					msg: "Человек старше двух месяцев. Имя должно быть заполнено.",
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					fn: function() {
						base_form.findField('Person_FirName').focus(true, 100);
					}
				});
				return false;
			}*/
			var notice = this.validationFormWithRegion();
			if (notice)
			{
				if (notice.length > 0)
					sw.swMsg.show({
						title: lang['preduprejdenie'],
						msg: lang['obnarujenyi_vozmojnyie_oshibki'] + notice.toString() + lang['podtverjdaete_sohranenie'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
								this.doSubmitOnDate();
						}.createDelegate(this)
					});
				else
				{
					this.doSubmitOnDate();
				}
			}
		}
	},
	doSubmitOnDate: function(date) {
		var win = this;
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		var changed_fields = this.getChangedFields();
		// если поле одиночное, то тупо отправляем его значение
		if ( changed_fields.length > 0 )
		{
			// сохраняем
			if ( changed_fields.length == 1 )
			{
				// даем выбрать дату, время
				getWnd('swDateTimeSelectWindow').show({selectedAttribute: changed_fields[0], onSelect: function(date_time) {
					var date = date_time.Date;
					var time = date_time.Time;
					var params = {
						Person_id: base_form.findField('Person_id').getValue(),
						Date: date,
						Time: time,
						EvnType: changed_fields[0]
					};
					if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
						params.RegistryType_id = g_RegistryType_id;
					if ( this.periodicSingleFields.in_array(changed_fields[0]) )
						params[changed_fields[0]] = base_form.getValues()[changed_fields[0]];
					else
					{
						for ( var i = 0; i < this.periodicStructFields[changed_fields[0]].length; i++ )
						{
							params[this.periodicStructFields[changed_fields[0]][i]] = base_form.getValues()[this.periodicStructFields[changed_fields[0]][i]];
						}
					}
					win.getLoadMask().show(lang['podojdite_idet_sohranenie']).show();
					Ext.Ajax.request({
						url: '/?c=Person&m=saveAttributeOnDate',
						params: params,
						callback: function(options, success, response) {
							win.getLoadMask().hide();
							if ( success ) {
								if ( response.responseText.length > 0 ) {
									var resp_obj = Ext.util.JSON.decode(response.responseText);

									if ( resp_obj.success == false ) {
										if ( resp_obj.Error_Code && resp_obj.Error_Code == 666 && resp_obj.Person_id && resp_obj.Server_id ) {

										}
										else {
											Ext.Msg.alert(
												lang['oshibka'],
												resp_obj.Error_Msg,
												function() {
													base_form.findField('Person_SurName').focus(true, 100);
													return;
												}
											);
										}
									}
									else {
										this.hide();
									}
								}
							}
						}.createDelegate(this)
					});
				}.createDelegate(this)});
			}
			else
			{
				Ext.MessageBox.show({
					title: lang['oshibka'],
					msg: "Вы изменили несколько атрибутов. А сохранение на определенную дату предполагает изменение только одного атрибута.",
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING
				});
			}
		}
		// даем выбрать изменившийся атрибут
		else
		{
			// даем выбрать дату, время
			getWnd('swDateTimeSelectWindow').show({selectAttribute: true, onSelect: function(date_time, attribute) {
				if ( !attribute )
					return;
				var date = date_time.Date;
				var time = date_time.Time;
				var params = {
					Person_id: base_form.findField('Person_id').getValue(),
					Date: date,
					Time: time,
					EvnType: attribute
				};
				if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
					params.RegistryType_id = g_RegistryType_id;
				if ( this.periodicSingleFields.in_array(attribute) )
					params[attribute] = base_form.getValues()[attribute];
				else
				{
					for ( var i = 0; i < this.periodicStructFields[attribute].length; i++ )
					{
						params[this.periodicStructFields[attribute][i]] = base_form.getValues()[this.periodicStructFields[attribute][i]];
					}
				}
				win.getLoadMask().show(lang['podojdite_idet_sohranenie']);
				Ext.Ajax.request({
					url: '/?c=Person&m=saveAttributeOnDate',
					params: params,
					callback: function(options, success, response) {
						win.getLoadMask().hide();
						if ( success ) {
							if ( response.responseText.length > 0 ) {
								var resp_obj = Ext.util.JSON.decode(response.responseText);

								if ( resp_obj.success == false ) {
									if ( resp_obj.Error_Code && resp_obj.Error_Code == 666 && resp_obj.Person_id && resp_obj.Server_id ) {

									}
									else {
										Ext.Msg.alert(
											lang['oshibka'],
											resp_obj.Error_Msg,
											function() {
												base_form.findField('Person_SurName').focus(true, 100);
												return;
											}
										);
									}
								}
								else {
									this.hide();
								}
							}
						}
					}.createDelegate(this)
				});
			}.createDelegate(this)});
		}
	},
	checkAdreesAdd: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		if (
			(
				(getRegionNick() == 'ekb' || this.checkAdress) &&
				base_form.findField('UAddress_Address').getValue().inlist(['', null, 'д. , корп. , кв. '])
				&& base_form.findField('PAddress_Address').getValue().inlist(['', null, 'д. , корп. , кв. '])
				&& (this.action == 'add' || this.action == 'edit')
			) || (
				getRegionNick() == 'vologda'
				&& base_form.findField('Person_IsAnonym').checked
			) || (
                getRegionNick() == 'buryatiya'
                && base_form.findField('Person_IsAnonym').checked
            )
		) {
			base_form.findField('UAddress_AddressText').setAllowBlank(false);
			base_form.findField('PAddress_AddressText').setAllowBlank(false);
			base_form.isValid();
		} else {
			base_form.findField('UAddress_AddressText').setAllowBlank(true);
			base_form.findField('PAddress_AddressText').setAllowBlank(true);
			base_form.findField('UAddress_AddressText').clearInvalid();
			base_form.findField('PAddress_AddressText').clearInvalid();
		}
	},
	doSaveNewPeriodicsOnDate: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();

		/*if (  base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Человек старше двух месяцев. Имя должно быть заполнено.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_FirName').focus(true, 100);
				}
			});
			return false;
		}*/

		var changed_fields = this.getChangedFields();
		// если поле одиночное, то тупо отправляем его значение
		if ( changed_fields.length > 0 )
		{
			// сохраняем
			var saving_data = {};
			if ( getRegionNick() == 'ufa' )
			{
				for ( var j = 0; j < changed_fields.length; j++ )
				{
					var attribute = changed_fields[j];
					if ( this.periodicSingleFields.in_array(attribute) )
						saving_data[attribute] = base_form.findField(attribute).getValue();
					else
					{
						for ( var i = 0; i < this.periodicStructFields[attribute].length; i++ )
						{
							saving_data[this.periodicStructFields[attribute][i]] = base_form.findField(this.periodicStructFields[attribute][i]).getValue();
						}
					}
				}
			}
			else
				for ( var j = 0; j < changed_fields.length; j++ )
				{
					var attribute = changed_fields[j];
					if ( this.periodicSingleFields.in_array(attribute) )
						saving_data[attribute] = base_form.getValues()[attribute];
					else
					{
						for ( var i = 0; i < this.periodicStructFields[attribute].length; i++ )
						{
							saving_data[this.periodicStructFields[attribute][i]] = base_form.getValues()[this.periodicStructFields[attribute][i]];
						}
					}
				}

			this.returnFunc({
				changedFields: changed_fields,
				savingData: saving_data
			});
		}
		else
			Ext.MessageBox.show({
				title: "Сохранение атрибутов",
				msg: "Вы не заполнили ни одного периодичного атрибута.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			});
	},
	checkOkeiRequired: function()
	{
		var base_form = this.findById('person_edit_form').getForm();
		/*if (Ext.isEmpty(base_form.findField('PersonWeight_Weight').getValue())) {
			base_form.findField('Okei_id').setAllowBlank(true);
		} else {
			base_form.findField('Okei_id').setAllowBlank(false);
		}*/
	},
	doSave: function() {
        var form = this.findById('person_edit_form');
        var base_form = form.getForm();

		if ( this.readOnly )
			return;
		var oldValues = this.oldValues;
		var action = this.action;

		if ( getRegionNick() == 'ufa' && base_form.findField('PersonInn_Inn').getValue().length == 11 ) {
			base_form.findField('PersonInn_Inn').setValue('0' + base_form.findField('PersonInn_Inn').getValue());
		}

		if ( !base_form.isValid() )
		{
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			}
			);
		}
		else
		{
			if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ekb' ) {
				//проверка на заполненость 1 из адресов не включая адрес рождения
				if(base_form.findField('UAddress_Address').getValue().inlist(['',null,'д. , корп. , кв. '])
					&&base_form.findField('PAddress_Address').getValue().inlist(['',null,'д. , корп. , кв. '])){
						Ext.MessageBox.show({
							title: "Проверка данных формы",
							msg: "Одно из полей «Адрес регистрации» и «Адрес проживания» должно быть обязательно заполнено.",
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							fn: function() {
								base_form.findField('UAddress_Address').focus(true, 100);
							}
						});
						return false;
					}

			}

			if ( getGlobalOptions().region && getGlobalOptions().region.nick != 'kz') {
				if (base_form.findField('Person_SNILS').getValue() == '' && this.doSNILScheck) {
					if (getGlobalOptions().snils_control==2) {
						sw.swMsg.show({
							title: langs("Предупреждение"),
							msg: langs("Не заполнено поле СНИЛС. <BR />Продолжить?"),
							buttons: Ext.Msg.YESNO,
							fn: function (buttonId) {
								if (buttonId == 'no') {
									base_form.findField('Person_SNILS').focus(true, 100);
								} else {
									this.doSNILScheck = false;
									this.doSave();
									return false;
								}
							}.createDelegate(this)
						});
						return false;
					}
					if(getGlobalOptions().snils_control==3) {
						Ext.MessageBox.show({
							title: langs("Проверка данных формы"),
							msg: langs("Поле СНИЛС обязательное для заполнения."),
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							fn: function () {
								base_form.findField('Person_SNILS').focus(true, 100);
							}
						});

						return false;
					}
				} else this.doSNILScheck = true;

				/*
				** переделал проверку на сервере
				if(this.do_not_ignore_inn_correctness_control){
					var inn_correctness_control = (getGlobalOptions().inn_correctness_control) ? parseInt(getGlobalOptions().inn_correctness_control, 10) : 1;
					this.do_not_ignore_inn_correctness_control = (inn_correctness_control != 1) ? true : false;
					switch(inn_correctness_control){
						case 2:
							if( !this.checkPersonINN() ){
								sw.swMsg.show({
									title: 'Внимание!',
									msg: 'Ошибка проверки контрольной суммы в ИНН. Убедитесь, что ИНН указан верно.',
									buttons: {yes: 'Продолжить', no: 'Отмена'},
									closable: false,
									fn: function(butn){
										if (butn == 'no'){
											base_form.findField('PersonInn_Inn').focus(true, 100);
											return false;
										}else{
											this.do_not_ignore_inn_correctness_control = false;
											this.doSave();
										}
									}.createDelegate(this)
								});

								return false;
							}
							break;
						case 3:
							if( !this.checkPersonINN() ){
								Ext.MessageBox.show({
									title: langs("Проверка данных формы"),
									msg: langs("ИНН введен с ошибкой. Проверьте корректность введенных данных."),
									buttons: Ext.Msg.OK,
									icon: Ext.Msg.WARNING,
									fn: function () {
										base_form.findField('PersonInn_Inn').focus(true, 100);
									}
								});

								return false;
							}
							break;
					}
				}
				*/
			}
			// проверка возраста и заполненности имени
			/*if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
			{
				Ext.MessageBox.show({
					title: "Проверка данных формы",
					msg: "Человек старше двух месяцев. Имя должно быть заполнено.",
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					fn: function() {
						base_form.findField('Person_FirName').focus(true, 100);
					}
				});
				return false;
			}*/

			var notice = this.validationFormWithRegion();
			if (notice)
			{
				if (notice.length > 0)
					sw.swMsg.show({
						title: lang['preduprejdenie'],
						msg: lang['obnarujenyi_vozmojnyie_oshibki'] + notice.toString() + lang['podtverjdaete_sohranenie'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
								this.checkPersonDoubles();
						}.createDelegate(this)
					});
				else
				{
					this.checkPersonDoubles();
				}
			}
		}
	},
	checkPolisNum: function()
	{
		var base_form = this.findById('person_edit_form').getForm();
		// если временное свид-во, то отменяем проверку
		if ( base_form.findField('PolisType_id').getValue() == 3 )
			return true;
		var polis_region = base_form.findField('OMSSprTerr_id').getFieldValue('KLRgn_id');
		// если не башкирия, то все нормально в любом случае
		if ( polis_region != 2 )
			return true;
		var polis_num = String(this.findById('person_edit_form').getForm().findField('Polis_Num').getValue());
		if ( checkEdNumSignature(polis_num) )
		{
			var year = polis_num.substr(3, 4);
			var month = polis_num.substr(7, 2);
			var day = polis_num.substr(9, 2);
			var sex = day > 50 ? 1 : 2;
			day = day > 50 ? day - 50 : day;
			if ( String(day).length == 1 ) day = '0' + String(day);
			var birthday = day + '.' + month + '.' + year;
			var region = polis_num.substr(0, 2);
			var person_sex = base_form.findField('PersonSex_id').getValue();
			var person_dirthday = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
			if ( person_dirthday != birthday || person_sex != sex || Number(polis_region) != Number(region) ){
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	},
	checkPersonINN: function() {
		var result = false;
		var base_form = this.findById('person_edit_form').getForm();
		var inn = base_form.findField('PersonInn_Inn').getValue(); //631405980490
		var errorMsg = 'Ошибка проверки контрольной суммы в ИНН. ';
		if (typeof inn === 'number') inn = inn.toString();

		var checkDigit = function (inn, coefficients) {
			var n = 0;
			coefficients.forEach(function(item, i, coefficients){
				n += coefficients[i] * inn[i];
			});
			return parseInt(n % 11 % 10);
		};

		if (!inn.length) {
			console.warn(errorMsg + 'ИНН пуст');
		} else if (/[^0-9]/.test(inn)) {
			console.warn(errorMsg + 'ИНН может состоять только из цифр');
		} else if ([12].indexOf(inn.length) === -1) {
			console.warn(errorMsg + 'ИНН может состоять только из 12 цифр');
		} else {
			var n11 = checkDigit(inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
			var n12 = checkDigit(inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
			if ((n11 === parseInt(inn[10])) && (n12 === parseInt(inn[11]))) {
				result = true;
			}
		}

		return result;
	},
	checkPersonSnils: function() {
		var snils = String(this.findById('person_edit_form').getForm().findField('Person_SNILS').getValue()).replace(/\-/g, '').replace(/ /g, '');

		if ( snils.length == 0 ) {
			return true;
		}

		var reg = /^\d{11}$/;

		if ( !reg.test(snils) ) {
			return false;
		}

		var
			psk = snils.substr(9, 2),
			ps = snils.substr(0, 9),
			arr = new Array(),
			z = 9,
			sum = 0,
			i;

		for ( i = 0; i < 9; i++ ) {
			arr[i] = ps.substr(i, 1);
			sum += arr[i]*z;
			z--;
		}

		while ( sum > 101 ) {
			sum = sum % 101;
		}

		if ( ((sum < 100) && (sum != psk)) || (((sum == 100) || (sum == 101)) && (psk != '00')) ) {
			return false;
		}

		return true;
	},
	doSubmit: function(options) {
		if ( this.readOnly )
			return;
		var base_form = this.findById('person_edit_form').getForm();
		var win = this;
		var oldValues = this.oldValues;
		var act = this.action;
		var post = {oldValues: oldValues, mode: act, Server_id: win.serverId};
		var form = this.findById('person_edit_form').getForm();
		options = options || {};
		if ( form.findField('Post_id').getValue() == '' )
		{
			post.PostNew = form.findField('Post_id').getRawValue();
		}
		else
		{
			// ищем уже существующее значение
			var id = -1;
			form.findField('Post_id').getStore().findBy(function(record) {
				if ( record.get('Post_Name') == form.findField('Post_id').getRawValue())
				{
					id = record.get('Post_id');
					return true;
				}
			});

			if ( id != -1 )
			{
				post.PostNew = '';
			}
			else
			{
				post.PostNew = form.findField('Post_id').getRawValue();
				form.findField('Post_id').setValue('');
			}
		}

		if ( form.findField('OrgUnion_id').getValue() == '' )
		{
			post.OrgUnionNew = form.findField('OrgUnion_id').getRawValue();
		}
		else
		{
			if (form.findField('OrgUnion_id').getStore().findBy(function(rec) { return rec.get('OrgUnion_Name') == form.findField('OrgUnion_id').getRawValue(); }) >= 0)
			{
			    post.OrgUnionNew = '';
			}
			else
			{
				post.OrgUnionNew = form.findField('OrgUnion_id').getRawValue();
				form.findField('OrgUnion_id').setValue('');
			}
		}

		if ( (
				base_form.findField('Person_IsInErz').getValue().toString().inlist(['1', '2'])
				|| base_form.findField('PersonIdentState_id').getValue().toString().inlist(['1', '3'])
			)
			&& !getGlobalOptions().superadmin
		) {
			post.OMSSprTerr_id = base_form.findField('OMSSprTerr_id').getValue();
			post.PolisType_id = base_form.findField('PolisType_id').getValue();
			post.Person_SurName = base_form.findField('Person_SurName').getValue();
			post.Person_FirName = base_form.findField('Person_FirName').getValue();
			post.Person_SecName = base_form.findField('Person_SecName').getValue();
			post.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
			post.Polis_Num = base_form.findField('Polis_Num').getValue();
			post.Polis_Ser = base_form.findField('Polis_Ser').getValue();
			post.Federal_Num = base_form.findField('Federal_Num').getValue();
			post.Polis_begDate = Ext.util.Format.date(base_form.findField('Polis_begDate').getValue(), 'd.m.Y');
			post.Polis_endDate = Ext.util.Format.date(base_form.findField('Polis_endDate').getValue(), 'd.m.Y');
			post.PersonInn_Inn = base_form.findField('PersonInn_Inn').getValue();
			//post.PersonNationality_id = base_form.findField('PersonNationality_id').getValue();
			post.Person_SNILS = base_form.findField('Person_SNILS').getValue();
			post.PersonSex_id = base_form.findField('PersonSex_id').getValue();
			post.SocStatus_id = base_form.findField('SocStatus_id').getValue();
			post.OrgSMO_id = base_form.findField('OrgSMO_id').getValue();
			post.DocumentType_id = base_form.findField('DocumentType_id').getValue();
			post.Document_Ser = base_form.findField('Document_Ser').getValue();
			post.Document_Num = base_form.findField('Document_Num').getValue();
		}

		if(base_form.findField('Person_BirthDay').getValue()<this.minBirtDay&&this.minBirtDay!=null)
			{
				Ext.Msg.alert("Ошибка", "Дата рождения должна быть не меньше даты исхода беременности");
				return;
			}
		if ( base_form.findField('Document_Ser').disabled && !post.Document_Ser ) {
			post.Document_Ser = base_form.findField('Document_Ser').getValue();
		}

		if ( base_form.findField('Document_Num').disabled && !post.Document_Num ) {
			post.Document_Num = base_form.findField('Document_Num').getValue();
		}
		if ( base_form.findField('KLCountry_id').disabled && !post.KLCountry_id ) {
			post.KLCountry_id = base_form.findField('KLCountry_id').getValue();
		}
		if ( base_form.findField('NationalityStatus_IsTwoNation').disabled && !post.NationalityStatus_IsTwoNation ) {
			post.NationalityStatus_IsTwoNation = base_form.findField('NationalityStatus_IsTwoNation').getValue();
		}

		if ( base_form.findField('OMSSprTerr_id').disabled && !post.OMSSprTerr_id ) {
			post.OMSSprTerr_id = base_form.findField('OMSSprTerr_id').getValue();
		}
		if ( base_form.findField('PolisType_id').disabled && !post.PolisType_id ) {
			post.PolisType_id = base_form.findField('PolisType_id').getValue();
		}
		if ( base_form.findField('OrgSMO_id').disabled && !post.OrgSMO_id ) {
			post.OrgSMO_id = base_form.findField('OrgSMO_id').getValue();
		}
		if ( base_form.findField('Polis_Ser').disabled && !post.Polis_Ser ) {
			post.Polis_Ser = base_form.findField('Polis_Ser').getValue();
		}
		if(base_form.findField('Person_BirthDay').disabled && !post.Person_BirthDay){
			post.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
		}
		if ( base_form.findField('Polis_Num').disabled && !post.Polis_Num ) {
			post.Polis_Num = base_form.findField('Polis_Num').getValue();
		}
		if ( base_form.findField('Federal_Num').disabled && !post.Federal_Num ) {
			post.Federal_Num = base_form.findField('Federal_Num').getValue();
		}
		if ( base_form.findField('Polis_endDate').disabled && !post.Polis_endDate ) {
			post.Polis_endDate = Ext.util.Format.date(base_form.findField('Polis_endDate').getValue(), 'd.m.Y');
		}
		if ( base_form.findField('Polis_begDate').disabled && !post.Polis_begDate ) {
			post.Polis_begDate = Ext.util.Format.date(base_form.findField('Polis_begDate').getValue(), 'd.m.Y');
		}
		if ( base_form.findField('Person_SNILS').disabled && !post.Person_SNILS ) {
			post.Person_SNILS = base_form.findField('Person_SNILS').getValue();
		}
		if ( base_form.findField('DeputyKind_id').disabled && !post.DeputyKind_id ) {
			post.DeputyKind_id = base_form.findField('DeputyKind_id').getValue();
		}
		if ( base_form.findField('DeputyPerson_id').disabled && !post.DeputyPerson_id ) {
			post.DeputyPerson_id = base_form.findField('DeputyPerson_id').getValue();
		}
		if(win.missSocStatus==1){
			post.missSocStatus=1;
		}
		if(win.rz){
			post.rz = win.rz;
		}
		if(win.personId){
			post.Person_id = win.personId;
		}
		if(getRegionNick() == 'ufa'){
			if(!this.findById('person_edit_form').getForm().findField('UAddress_AddressText').disabled){
				post.UKLCountry_id = this.findById('person_edit_form').getForm().findField('UKLCountry_id').getValue();
				post.UAddress_Address = this.findById('person_edit_form').getForm().findField('UAddress_Address').getValue();
			}
			if(!this.findById('person_edit_form').getForm().findField('BAddress_AddressText').disabled){
				post.BKLCountry_id = this.findById('person_edit_form').getForm().findField('BKLCountry_id').getValue();
				post.BAddress_Address = this.findById('person_edit_form').getForm().findField('BAddress_Address').getValue();
			}
			if(!this.findById('person_edit_form').getForm().findField('PAddress_AddressText').disabled){
				post.PKLCountry_id = this.findById('person_edit_form').getForm().findField('PKLCountry_id').getValue();
				post.PAddress_Address = this.findById('person_edit_form').getForm().findField('PAddress_Address').getValue();
			}
		}
		if(win.ignoreOMSSprTerrDateCheck){
			post.ignoreOMSSprTerrDateCheck = win.ignoreOMSSprTerrDateCheck;
		}
		if(win.ignoreСhecksumINN){
			post.ignoreСhecksumINN = win.ignoreСhecksumINN;
		}
		if(win.ignoreOMSSprTerrPolis){
			post.ignoreOMSSprTerrPolis = win.ignoreOMSSprTerrPolis;
		}
		if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
			post.RegistryType_id = g_RegistryType_id;

		// проверка номера полиса
		if (base_form.findField('Federal_Num').getValue() != '' && Number(base_form.findField('PolisType_id').getValue()) == 4){
			var polis_num = String(base_form.findField('Federal_Num').getValue());
			if (!checkEdNumFedSignature(polis_num) && getRegionNick() != 'kz' && !options.ignoreENPValidationControl) {
				switch (getGlobalOptions().enp_validation_control) {
					case 'warning':		// Выводим предупреждение с возможностью продолжения
						sw.swMsg.show({
							buttons: sw.swMsg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									var options = {};
									options.ignoreENPValidationControl = 1;
									this.doSubmit(options);
								} else {
									base_form.findField('Federal_Num').focus(true, 100);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: "Единый номер полиса не соответствует формату. Продолжить сохранение?",
							title: lang['vopros']
						});
						return false;
						break;
					case 'deny':		// Выводим сообщение об ошибке
						sw.swMsg.show({
							title: "Проверка номера полиса",
							msg: "Единый номер полиса не соответствует формату",
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.WARNING,
							fn: function () {
								base_form.findField('Federal_Num').focus(true, 100);
							}
						});
						return false;
						break;
				}
			}
		}

		// @task https://redmine.swan-it.ru/issues/163758
		if (getRegionNick() == 'vologda') {
			var
				DocumentType_id = base_form.findField('DocumentType_id').getValue(),
				Document_begDate = base_form.findField('Document_begDate').getValue(),
				Person_BirthDay = base_form.findField('Person_BirthDay').getValue();

			if (
				!Ext.isEmpty(DocumentType_id) && !DocumentType_id.toString().inlist(['3','9','17','19','22'])
				&& !Ext.isEmpty(Document_begDate) && typeof Document_begDate == 'object'
				&& !Ext.isEmpty(Person_BirthDay) && typeof Person_BirthDay == 'object'
				&& swGetPersonAge(Person_BirthDay, Document_begDate) < 14
			) {
				sw.swMsg.show({
					title: "Проверка документа",
					msg: "Дата выдачи документа должна соответствовать дате 14-летия пациента или должна быть позже. Укажите корректную дату выдачи и тип документа.",
					buttons: Ext.Msg.OK,
					icon: Ext.Msg.WARNING,
					fn: function () {
						base_form.findField('DocumentType_id').focus(true, 100);
					}
				});
				return false;
			}
		}

		win.getLoadMask(langs('Подождите, идет сохранение...')).show();
		this.findById('person_edit_form').getForm().submit(
		{
			params: post,
			timeout: 1800000,
			success: function(form, action) {
				win.getLoadMask().hide();

				var resp_obj = Ext.util.JSON.decode(action.response.responseText);
				if (resp_obj.Alert_Code && resp_obj.Alert_Code) {
					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function ( buttonId ) {
							if ( buttonId == 'ok' ) {
								win.ignoreOMSSprTerrDateCheck = 2;
								win.doSubmit();
							}
						},
						msg: resp_obj.Alert_Msg,
						title: lang['vopros']
					});
					return false;
				}

				if (resp_obj.ignoreСhecksumINN && resp_obj.ignoreСhecksumINN == 2) {
					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function ( buttonId ) {
							if ( buttonId == 'ok' ) {
								win.ignoreСhecksumINN = 2;
								win.doSubmit();
							}
						},
						msg: resp_obj.Alert_Msg,
						title: langs('Вопрос')
					});
					return false;
				}

				if (resp_obj.ignoreOMSSprTerrPolis && resp_obj.ignoreOMSSprTerrPolis == 2) {
					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function ( buttonId ) {
							if ( buttonId == 'ok' ) {
								win.ignoreOMSSprTerrPolis = 2;
								win.doSubmit();
							}
						},
						msg: resp_obj.Alert_Msg,
						title: langs('Вопрос')
					});
					return false;
				}

				if(resp_obj.Info_Msg && !Ext.isEmpty(resp_obj.Info_Msg))
				{
					var Info_Msg = resp_obj.Info_Msg;
					var Info_Msg_Str = '';
					for (var i = 0; i < Info_Msg.length; i++)
					{
						Info_Msg_Str += Info_Msg[i]+'<br>';
					}
					if(Info_Msg.length > 0)
					{
						sw.swMsg.alert('Сообщение', Info_Msg_Str);
					}
				}
				win.hide();

				//https://redmine.swan.perm.ru/issues/51707
				if(getGlobalOptions().region.nick == 'khak' && win.action == 'add'){
					if (getWnd('swPersonCardViewAllWindow').isVisible())
					{
						var params = new Object();
						if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
							params.RegistryType_id = g_RegistryType_id;
						params.Person_id = action.result.Person_id;
						params.PersonEvn_id = action.result.PersonEvn_id;
						params.Server_id = action.result.Server_id;
						params.action = 'add';
						params.attachType = "common_region";
						params.setIsAttachCondit = 2;
						params.lastAttachIsNotInOurLpu = false;
						//params.lastAttach_IsAttachCondit = true;
						params.oldLpu_id = null;
						params.callback = function(){
							Ext.getCmp('PCVAW_regions_tab_panel').setActiveTab('common_region');
						};
						params.AllowcheckAttach = isSuperAdmin()?1:2;
						getWnd('swPersonCardEditWindow').show(params);
					}
					else
					{
						getWnd('swPersonCardViewAllWindow').show({
							showPersonCardAdd: 2,
							Person_id: action.result.Person_id,
							Server_id: action.result.Server_id,
							PersonEvn_id: action.result.PersonEvn_id,
							setIsAttachCondit: 2,
							Person_FirName: form.findField('Person_FirName').getValue(),
							Person_SurName: form.findField('Person_SurName').getValue(),
							Person_SecName: form.findField('Person_SecName').getValue(),
							Person_BirthDay: form.findField('Person_BirthDay').getValue()
						});
					}
				}
				/*win.saveIzmer({
					Person_id: action.result.Person_id,
					Server_id: action.result.Server_id
				});*/
				win.returnFunc({
					Person_id: action.result.Person_id,
					Server_id: action.result.Server_id,
					PersonEvn_id: action.result.PersonEvn_id,
					PersonData: {
						Person_id: action.result.Person_id,
						Server_id: action.result.Server_id,
						PersonEvn_id: action.result.PersonEvn_id,
						Person_FirName: form.findField('Person_FirName').getValue(),
						Person_SurName: form.findField('Person_SurName').getValue(),
						Person_SecName: form.findField('Person_SecName').getValue(),
						Person_BirthDay: form.findField('Person_BirthDay').getValue(),
						Person_Snils: form.findField('Person_SNILS').getValue(),
						PersonSex_id: form.findField('PersonSex_id').getValue(),
						UAddress_AddressText: form.findField('UAddress_AddressText').getValue(),
						PAddress_AddressText: form.findField('PAddress_AddressText').getValue(),
						Person_Age: swGetPersonAge(form.findField('Person_BirthDay').getValue(), new Date()),
						Person_Phone: win.findById('PEW_PersonPhone_Phone').getValue(),
						Person_Work_id: base_form.findField('Org_id').getValue(),
						Person_Work: base_form.findField('Org_id').getFieldValue('Org_Nick'),
						Document_Ser: base_form.findField('Document_Ser').getValue('Document_Ser'),
						Document_Num: base_form.findField('Document_Num').getValue('Document_Num'),
						Polis_Ser: base_form.findField('Polis_Ser').getValue('Polis_Ser'),
						Polis_Num: base_form.findField('Polis_Num').getValue('Polis_Num'),
						Polis_EdNum: base_form.findField('Federal_Num').getValue('Federal_Num')
					}
				});
				if (win.action == 'add') {
					win.afterTryAdd(form, resp_obj);
				}
			},
			failure: function (form, action)
			{
				var resp_obj = Ext.util.JSON.decode(action.response.responseText);
				if(resp_obj.type=='SocStatus'){

                    if (getRegionNick() == 'astra') {
                        sw.swMsg.show({
                            buttons: sw.swMsg.YESNO,
                            fn: function(buttonId, text, obj) {
                                if ('yes' == buttonId) {
                                    win.missSocStatus = 1;
                                    win.doSubmit();
                                }else{
                                    win.getLoadMask().hide();
                                    //Ext.Msg.alert("Ошибка", action.result.msg);
                                    win.hide();
                                }
                            }.createDelegate(this),
                            icon: Ext.MessageBox.QUESTION,
                            msg: lang['vyibrannyiy_sotsialnyiy_status_ne_sootvetstvuet_vozrastu_patsienta_prodoljit_sohranenie'],
                            title: lang['vopros']
                        })
                    } else {
					    win.getLoadMask().hide();
                        sw.swMsg.alert(lang['oshibka'], lang['vyibrannyiy_sotsialnyiy_status_ne_sootvetstvuet_vozrastu_patsienta']);
                        return false;
                    }

				}else{
					win.getLoadMask().hide();
					//Ext.Msg.alert("Ошибка", action.result.msg);
					//win.hide();
				}

				if(resp_obj.Person_AnonymCode && resp_obj.Person_IsAnonym){// вернулся новый код анонимного пациента
					form.findField('Person_SurName').setValue(resp_obj.Person_AnonymCode);
					win.afterTryAdd(form, resp_obj);
				}

				if (win.action == 'add') {
					win.afterTryAdd(form, resp_obj);
				}
				if (resp_obj.Error_Msg == 'db_unable_to_connect') {
					var msg = 'Нет связи с основным сервером.';
					if (win.forObject == 'CmpCallCard' && win.action == 'add') {
						msg += ' Человек будет добавлен в систему после восстановления связи.';
					} else if (win.action == 'add') {
						msg += ' Добавление человека невозможно.';
					} else {
						msg += ' Редактирование человека невозможно.';
					}
					sw.swMsg.alert(lang['oshibka'], msg);
					return false;
				}
			}
		});
	},
	/*saveIzmer: function(params) { //сохранение показателей здоровья для человека
		var mes_array = new Array();
		var store = this.PersonMeasureGrid.getGrid().getStore();

		store.clearFilter(); //снимаем фильтр для полного сбора данных
		store.each(function(record) {
			if (record.data.Record_Status != 1 && record.data.PersonMeasure_id > 0)
				mes_array.push({
					PersonMeasure_id: record.data.PersonMeasure_id,
					PersonMeasure_setDT_Date: record.data.PersonMeasure_setDT_Date,
					PersonMeasure_setDT_Time: record.data.PersonMeasure_setDT_Time,
					LpuSection_id: record.data.LpuSection_id,
					MedPersonal_id: record.data.MedPersonal_id,
					Record_Status: record.data.Record_Status,
					RateGrid_Data: record.data.RateGrid_Data
				});
		});

		store.filterBy(function(record) { // возвращаем фильтр на место
			if (record.get('Record_Status') != 3) {
				return true;
			}
		});

		if (mes_array.length > 0) {
			var saveObj = new Object();
			saveObj['Person_id'] = params.Person_id;
			saveObj['data'] = Ext.util.JSON.encode(mes_array);

			Ext.Ajax.request({
				url: '/?c=Rate&m=savePersonMeasures',
				params: saveObj,
				callback: function(options, success, response) {
					if (success) { }
				}.createDelegate(this)
			});
		}
	},*/

	disablePolisFields: function(disable, unclear)
	{
		if (this.readOnly)
			return;
		var base_form = this.findById('person_edit_form').getForm();
		if ( disable == true )
		{
			base_form.findField('OrgSMO_id').disable();
			base_form.findField('Polis_Ser').disable();
			base_form.findField('Polis_Num').disable();
			base_form.findField('Polis_begDate').disable();
			if(getRegionNick()=='ufa'){
				base_form.findField('Polis_endDate').enable();
			}else{
				base_form.findField('Polis_endDate').disable();
			}
			base_form.findField('PolisType_id').disable();
			base_form.findField('Federal_Num').disable();
			if (unclear != true)
			{
				base_form.findField('OrgSMO_id').clearValue();
				base_form.findField('Polis_Ser').setRawValue('');
				base_form.findField('Polis_Num').setRawValue('');
				base_form.findField('Federal_Num').setRawValue('');
				base_form.findField('Polis_begDate').setRawValue('');
				base_form.findField('Polis_endDate').setRawValue('');
				base_form.findField('PolisType_id').clearValue();
			}
		}
		else
		{
			base_form.findField('OMSSprTerr_id').enable();
			base_form.findField('OrgSMO_id').enable();
			base_form.findField('Polis_Ser').enable();
			base_form.findField('Polis_Num').enable();
			base_form.findField('Federal_Num').enable();
			base_form.findField('Polis_begDate').enable();
			base_form.findField('Polis_endDate').enable();
			base_form.findField('PolisType_id').enable();
			if ( base_form.findField('PolisType_id').getValue() > 0 )
				base_form.findField('PolisType_id').setValue(base_form.findField('PolisType_id').getValue());
			else
				base_form.findField('PolisType_id').setValue(4);
			base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
		}
	},
	disableDocumentFields: function(disable, unclear)
	{
		if (this.readOnly)
			return;
		var form = this.findById('person_edit_form');
		if ( disable == true )
		{
			form.getForm().findField('OrgDep_id').disable();
			form.getForm().findField('Document_Ser').disable();
			form.getForm().findField('Document_Num').disable();
			form.getForm().findField('Document_begDate').disable();
			//form.getForm().findField('KLCountry_id').disable();
			form.getForm().findField('KLCountry_id').enable();
			form.getForm().findField('NationalityStatus_IsTwoNation').disable();
			if (unclear != true)
			{
				form.getForm().findField('OrgDep_id').clearValue();
				form.getForm().findField('Document_Ser').setRawValue('');
				form.getForm().findField('Document_Num').setRawValue('');
				form.getForm().findField('Document_begDate').setRawValue('');
				//form.getForm().findField('KLCountry_id').clearValue();
				//form.getForm().findField('NationalityStatus_IsTwoNation').setValue(false);
			}
		}
		else
		{
			form.getForm().findField('OrgDep_id').enable();
			form.getForm().findField('DocumentType_id').enable();
			form.getForm().findField('Document_Ser').enable();
			form.getForm().findField('Document_Num').enable();
			form.getForm().findField('Document_begDate').enable();
			if (form.getForm().findField('DocumentType_id').getFieldValue('DocumentType_Code') != 22) {
				form.getForm().findField('KLCountry_id').enable();
			} else {
				form.getForm().findField('KLCountry_id').disable();
			}
		}
	},
	getAnonymCode: function(options) {
		options = Ext.applyIf(options || {}, {callback: Ext.emptyFn});
		var win = this;
		var params = {
			Person_id: this.personId
		};
		if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
			params.RegistryType_id = g_RegistryType_id;
		var url = '/?c=Person&m=getPersonAnonymCode';
		if (options.mode == 'data') {
			url = '/?c=Person&m=getPersonAnonymData';
		}
		win.getLoadMask('Получение кода анонимного пациента').show();
		Ext.Ajax.request({
			url: url,
			params: params,
			success: function(response) {
				win.getLoadMask().hide();
				var response_obj = Ext.util.JSON.decode(response.responseText);

				options.callback(response_obj);
			}.createDelegate(this),
			failure: function() {
				win.getLoadMask().hide();
			}
		});
	},
	/**
	 * Блокировка от правки элементов формы (для режима просмотра) #129470
	 * @param el Элемент, у которого могут быть потомки items
	 */
	disableFieldsInViewMode: function(el){
		if(! this.readOnly) return;
		//console.log('---disableFieldsInViewMode()');
		var _this = this;
		if((typeof el.items) === 'object' /*&& (typeof el.getRange) === 'function'*/) {
			Ext.each(el.items.getRange(), function (item) {
				if((typeof item.xtype) === 'string'){
					if((new Array('swcommonsprcombo', 'numberfield', 'swdatefield', 'swdiagcombo', 'swdeputykindcombo', 'swpersoncombo', 'swfamilystatuscombo', 'textfield', 'checkbox')).indexOf(item.xtype) !== -1){
						item.disable();
					}
					/*else{// просмотр типов не из списка
						if((typeof item.xtype) !== 'undefined'){
							console.log('xtype:', item.xtype);
						}
					}*/
				}
				_this.disableFieldsInViewMode(item);
			});
		}
	},
	show: function() {
		var v;

		sw.Promed.swPersonEditWindow.superclass.show.apply(this, arguments);
		Ext.select('.change').removeClass('change');
		var win = this;
		this.rz = null;
		this.ignoreOMSSprTerrDateCheck = null;
		this.ignoreСhecksumINN = null;
		this.ignoreOMSSprTerrPolis = null;
		this.childAdd = false;
		var base_form = this.findById('person_edit_form').getForm();
		base_form.findField('Polis_endDate').setMinValue(undefined);
		var form = this.findById('person_edit_form');
		this.personId = 0;
		this.readOnly = false;
		this.IdentMsg=null;
		this.ekbIdentInoter=true;
		this.serverId = 0;
		this.checkAdress = false;
		this.returnFunc = Ext.emptyFn;
		this.afterTryAdd = Ext.emptyFn;
		this.forObject = null;
		this.missSocStatus = 0;
		this.doSNILScheck = true;
		this.do_not_ignore_inn_correctness_control = (getRegionNick() != 'kz' && getGlobalOptions().inn_correctness_control != 1) ? true : false;
		if ( isSmoTfomsUser() ) {
			this.findById('pacient_tab_panel').hideTabStripItem('additional_tab');
			this.findById('pacient_tab_panel').hideTabStripItem('spec_tab');
			//this.findById('pacient_tab_panel').hideTabStripItem('zdorov_tab');
		}

		if (!isUserGroup('Newslatter')) {
			this.findById('pacient_tab_panel').hideTabStripItem('newslatter_accept_tab');
		}

		base_form.findField('Person_IsUnknown').setContainerVisible(false);
		base_form.findField('Person_IsAnonym').setContainerVisible(getRegionNick().inlist(['ekb', 'vologda', 'buryatiya']));
		base_form.findField('Person_IsNotINN').setContainerVisible(getRegionNick() != 'kz');

		/*if ( getRegionNick() == 'kz' ) {
			this.findById('pacient_tab_panel').hideTabStripItem('zdorov_tab');
		}*/

		this.minBirtDay = null;
		this.removeUnknown = false;

		/*base_form.findField('PersonNationality_id').lastQuery = '';
		base_form.findField('PersonNationality_id').getStore().filterBy(function(rec) {
			if ( getRegionNick() == 'kz' ) {
				return (!Ext.isEmpty(rec.get('Nationality_Code')) && rec.get('Nationality_Code').toString().inlist([ '5', '6', '7', '8' ]));
			}
			else {
				return (!Ext.isEmpty(rec.get('Nationality_Code')) && !rec.get('Nationality_Code').toString().inlist([ '5', '6', '7', '8' ]));
			}
		});*/

		if ( arguments[0] )
		{
			if ( arguments[0].action )
				this.action = arguments[0].action;
			else
				if ( arguments[0].Person_id && arguments[0].Person_id > 0 )
					this.action = 'edit';
				
			
			if ( arguments[0].subaction )
				this.subaction = arguments[0].subaction;
			else
				this.subaction = null;


			if ( arguments[0].PeriodicEvnClass )
				this.PeriodicEvnClass = arguments[0].PeriodicEvnClass;
			else
				this.PeriodicEvnClass = null;

			if ( arguments[0].callback && typeof arguments[0].callback == 'function' )
				this.returnFunc = arguments[0].callback;

			if ( arguments[0].fields ) {
				base_form.setValues(arguments[0].fields);
				var dpid = base_form.findField('DeputyPerson_id').getValue();
				if ( base_form.findField('DeputyPerson_id').getValue() > 0 )
				{
					base_form.findField('DeputyPerson_id').getStore().removeAll();
					base_form.findField('DeputyPerson_id').getStore().loadData([{
						Person_id: dpid,
						Person_Fio: base_form.findField('DeputyPerson_Fio').getValue()
					}]);
					base_form.findField('DeputyPerson_id').setValue(dpid);
				}
			}

			if (arguments[0].afterTryAdd) {
				this.afterTryAdd = arguments[0].afterTryAdd;
			}

			if (arguments[0].removeUnknown) {
				this.removeUnknown = arguments[0].removeUnknown;
			}

			if (arguments[0].forObject) {
				this.forObject = arguments[0].forObject;
			}

			if ( arguments[0].onClose && typeof arguments[0].onClose == 'function' )
				this.onClose = arguments[0].onClose;
			else if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' )
				this.onClose = arguments[0].onHide;
			this.on('hide',function(){if(win.IdentMsg!=null&&typeof win.IdentMsg.close=='function'){win.IdentMsg.close()}})
			if ( arguments[0].Person_id )
				this.personId = arguments[0].Person_id;
			if ( arguments[0].RegistryType_id )
				this.RegistryType_id = arguments[0].RegistryType_id;
			if ( arguments[0].Evn_setDT ){
					this.Evn_setDT = arguments[0].Evn_setDT
				}else{
					this.Evn_setDT = null
				}
			if ( arguments[0].PersonEvn_id )
			{
				this.personEvnId = arguments[0].PersonEvn_id;
			}
			else
				this.personEvnId = null;

			if ( arguments[0].readOnly )
				this.readOnly = arguments[0].readOnly;

			if ( arguments[0].Server_id )
				this.serverId = arguments[0].Server_id;

			if ( arguments[0].checkAdress )
				this.checkAdress = arguments[0].checkAdress;

			if ( arguments[0].allowUnknownPerson ) {
				base_form.findField('Person_IsUnknown').setContainerVisible(true);
			}
		}

		if ( getRegionNick().inlist(['ufa', 'kareliya', 'ekb', 'astra', 'buryatiya', 'pskov']) ) {
			this.buttons[2].show();
		}
		else {
			this.buttons[2].hide();
		}

		/*if (this.personId && this.PersonMeasureGrid)
			this.PersonMeasureGrid.setParam('Person_id', this.personId, false);*/

		if (this.action == 'view') {
			this.readOnly = true;
		}

		if (!this.readOnly)	{
			this.disableEdit(false);
		}
		else{
			this.disableEdit(true);
		}
		if (this.action == 'add')
			this.setTitle(WND_PERS_ADD);

		if ( this.subaction && this.subaction=='editperiodic' )
		{
			this.disableEdit(true);
			Ext.getCmp('PEW_SaveButton').enable();
		}
		
		if ( this.action != 'edit' || this.readOnly === true )
		{
			//Ext.getCmp('PEW_SaveOnDateButton').disable();
			Ext.getCmp('PEW_PeriodicsButton').hide();
		}
		else
		{
			//Ext.getCmp('PEW_SaveOnDateButton').enable();
			Ext.getCmp('PEW_PeriodicsButton').show();
		}

		this.checkAdreesAdd();
		if (this.action == 'edit' ) {
			if (!this.readOnly)
			{
				this.setTitle(WND_PERS_EDIT);
			}
			else
			{
				this.setTitle(WND_PERS_VIEW);
			}
		}
		if (this.action == 'view') {
			this.setTitle(WND_PERS_VIEW);
		}

		this.findById('pacient_tab_panel').setActiveTab(3);
		this.findById('pacient_tab_panel').setActiveTab(2);
		this.findById('pacient_tab_panel').setActiveTab(1);
		this.findById('pacient_tab_panel').setActiveTab(0);
		this.disablePolisFields(true);
		this.disableDocumentFields(true);

		this.refreshPersonPhoneVerificationButton();
		this.refreshPersonSnilsVerificationButton();

		if ( this.subaction == 'editperiodic' )
		{
			this.setTitle(lang['chelovek_redaktirovanie_periodiki']);
		}
		else
		{
		}

		base_form.findField('PersonFamilyStatus_IsMarried').setAllowBlank(true);

		base_form.findField('Post_id').getStore().load({
				params: {
					Object:'Post',
					Post_id:'',
					Post_Name:''
				},
				callback: function() {
				}
		});

		if ( this.action != 'add' ) {
			win.getLoadMask(lang['pojaluysta_podojdite_idet_zagruzka_dannyih_formyi']).show();

		}

		base_form.reset();
		this.PersonEval.removeAll();
		this.PersonFeedingType.removeAll();
		this.NewslatterAcceptGrid.removeAll();
		this.FamilyRelationGrid.removeAll();

		base_form.findField('Polis_CanAdded').setValue(0);
		var terr_combo = base_form.findField('OMSSprTerr_id');
		var terr_id = terr_combo.getValue();
		terr_combo.getStore().filterBy(function(record) {
				return true;
		});
		terr_combo.baseFilterFn = function(record) {
				return true;
		};

		base_form.findField('PersonSex_id').setValue('');

		if (getRegionNick() == 'ekb') {
			base_form.findField('Person_deadDT').setDisabled(!isSuperAdmin() && !isLpuAdmin());
		} else {
			base_form.findField('Person_deadDT').setContainerVisible(false);
		}
		base_form.findField('Person_closeDT').setContainerVisible(false);

		this.findById('PEW_IdentInErzFieldSet').hide();
		if (this.readOnly) {
			this.findById('PEW_IdentInErzButton').hide();
			//this.FamilyRelationGrid.disable();// уже не актуально, и было не красиво
		} else {
			this.findById('PEW_IdentInErzButton').show();
			//this.FamilyRelationGrid.enable();// уже не актуально, и было не красиво
		}
		base_form.findField('Person_identInErzDT').setAllowBlank(true);

		this.oldValuesToRestore = null;

		/*base_form.findField('WeightAbnormType_id').disable();
		base_form.findField('WeightAbnormType_id').clearValue();
		base_form.findField('HeightAbnormType_id').disable();
		base_form.findField('HeightAbnormType_id').clearValue();*/

		// устанавливаем одно редактируемое поле
		if ( this.subaction == 'editperiodic' )
		{
			if ( this.periodicSingleFields.in_array(this.PeriodicEvnClass) )
			{
				base_form.findField(this.PeriodicEvnClass).enable();
				base_form.findField(this.PeriodicEvnClass).focus(true, 500);
			}
			else
			{
				switch ( this.PeriodicEvnClass )
				{
					case 'Polis':
						this.disablePolisFields(false);
						base_form.findField('OMSSprTerr_id').focus(true, 500);
					break;
					case 'Document':
						this.disableDocumentFields(false);
						base_form.findField('DocumentType_id').focus(true, 500);
					break;
					case 'UAddress':
						base_form.findField('UAddress_AddressText').enable();
						base_form.findField('UAddress_Zip').enable();
						base_form.findField('UKLCountry_id').enable();
						base_form.findField('UKLRGN_id').enable();
						base_form.findField('UKLSubRGN_id').enable();
						base_form.findField('UPersonSprTerrDop_id').enable();
						base_form.findField('UKLCity_id').enable();
						base_form.findField('UKLTown_id').enable();
						base_form.findField('UKLStreet_id').enable();
						base_form.findField('UAddress_House').enable();
						base_form.findField('UAddress_Corpus').enable();
						base_form.findField('UAddress_Flat').enable();
						base_form.findField('UAddress_Address').enable();
						base_form.findField('UAddress_AddressText').focus(true, 500);
					break;
					case 'PAddress':
						base_form.findField('PAddress_AddressText').enable();
						base_form.findField('PAddress_Zip').enable();
						base_form.findField('PKLCountry_id').enable();
						base_form.findField('PKLRGN_id').enable();
						base_form.findField('PKLSubRGN_id').enable();
						base_form.findField('PPersonSprTerrDop_id').enable();
						base_form.findField('PKLCity_id').enable();
						base_form.findField('PKLTown_id').enable();
						base_form.findField('PKLStreet_id').enable();
						base_form.findField('PAddress_House').enable();
						base_form.findField('PAddress_Corpus').enable();
						base_form.findField('PAddress_Flat').enable();
						base_form.findField('PAddress_Address').enable();
						base_form.findField('PAddress_AddressText').focus(true, 500);
					break;
					case 'Job':
						base_form.findField('Org_id').enable();
						base_form.findField('OrgUnion_id').enable();
						base_form.findField('Org_id').focus(true, 500);
					break;
				}
			}
		}

		if ( this.action != 'add' ) {
			this.disablePolisFields(false);
			this.disableDocumentFields(false);
			params = {
				RegistryType_id: this.RegistryType_id,
				person_id: this.personId,
				server_id: this.serverId
			};
			if ( !this.personEvnId )
				var url = '/?c=Person&m=getPersonEditWindow';
			else
			{
				var url = '/?c=Person&m=getPersonEvnEditWindow';
				params.PersonEvn_id = this.personEvnId;
				params.Evn_setDT = this.Evn_setDT;
			}
			base_form.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_zagruzit_dannyie_s_servera'], function() {this.hide();}.createDelegate(this));
				}.createDelegate(this),
				params: params,
				success: function(fm, action) {
					var obj_responseText = Ext.util.JSON.decode(action.response.responseText);
					if (!Ext.isEmpty(obj_responseText) && !Ext.isEmpty(obj_responseText[0].Error_Msg)) {
						win.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], obj_responseText[0].Error_Msg, function() {this.hide();}.createDelegate(this));
					}

					var KLCountry_id = obj_responseText[0].KLCountry_id;

					win.checkOkeiRequired();
					win.refreshPersonPhoneVerificationButton();
					win.refreshPersonSnilsVerificationButton();

					if (fm.findField('Person_IsUnknown').getValue()) {
						fm.findField('Person_IsUnknown').setContainerVisible(true);
					}
					fm.findField('Person_IsUnknown').fireEvent('check', fm.findField('Person_IsUnknown'), fm.findField('Person_IsUnknown').getValue());

					var beg_dt = fm.findField('Polis_begDate').getValue();
					if(beg_dt){
						fm.findField('Polis_endDate').setMinValue(beg_dt);
					}
					if (fm.findField('Person_IsAnonym').checked) {
						//this.isAnonym = true;// не используется
						fm.findField('Person_IsAnonym').setContainerVisible(true);
					}

					if (fm.findField('PersonFamilyStatus_IsMarried').getValue() != null )
						base_form.findField('PersonFamilyStatus_IsMarried').setAllowBlank(false);

					// показываем закрытие записи или смерть
					if ( !Ext.isEmpty(fm.findField('Person_deadDT').getValue()) ) {
						base_form.findField('Person_deadDT').setContainerVisible(true);
					}

					if ( !Ext.isEmpty(fm.findField('Person_closeDT').getValue()) )
						base_form.findField('Person_closeDT').setContainerVisible(true);
					var polisCloseCause = fm.findField('polisCloseCause').getValue();
					var server_pid = fm.findField('Server_pid').getValue();
					var servers_ids = Ext.util.JSON.decode(fm.findField('Servers_ids').getValue());
					var servers_mask_arr = Array("0", "0", "0");
					// права на редактирование полей
					if ( inlist(Ext.globalOptions.globals.lpu_id, servers_ids) )
					{
						servers_mask_arr[2] = 1;
					}
					if ( inlist(0, servers_ids) )
					{
						servers_mask_arr[0] = 1;
					}
					if ( inlist(1, servers_ids) )
					{
						servers_mask_arr[1] = 1;
					}
					//PROMEDWEB-9667
					//Разрешаем редактировать форму паиета для Красноярска
					if (getRegionNick() == 'krasnoyarsk') {
						servers_mask_arr = Array("0", "0", "1");
					}
					
					if ( inlist('SuperAdmin', servers_ids) )
						servers_mask_arr = Array("0", "0", "0");
					if ( inlist(3, servers_ids) )
						servers_mask_arr = Array("0", "0", "0");
					if ( getRegionNick() == 'ekb' && (isStacReceptionVrach() || isMedStatUser() || isPolkaRegistrator()) )
						servers_mask_arr = Array("0", "0", "0");
					var mask = servers_mask_arr.join("");
					/*if(getRegionNick().inlist(['ufa'])){
						if(server_pid==0){
							this.buttons[2].disable();
						}else{
							this.buttons[2].enable();
						}
					}*/

					this.refreshIdentInErzFieldSet();
					this.refreshIdentInTfomsFieldSet();

					if (getRegionNick().inlist(['ufa'])&&server_pid==0&&(polisCloseCause.inlist([1,9,5,6])||polisCloseCause=='')&&!isSuperAdmin())
					{
						mask = "serv";

					}else
					// Если федеральный льготник и уфа, москва или астрахань
					if (getRegionNick().inlist(['ufa','astra','msk','adygeya']))
					{
						// Если Уфа (или астра, московская область, адыгея), то редактирование разрешено всем
						mask = "000";
						if (getRegionNick().inlist(['ufa']) && (fm.findField('Person_IsFedLgot').getValue()==1) && (!isAdmin))
							{
								// кроме определенных полей, если это федеральный льготник (в уфе)
								mask = "ufafed";
							}

					}

					if (getRegionNick().inlist(['buryatiya','vologda'])) {
						if ((server_pid == 0 || fm.findField('Person_IsFedLgot').getValue() == 1) && !isSuperAdmin() && !isUserGroup('editorperiodics')) {
							mask = "111";
						} else {
							mask = "000";
						}

						if (!isSuperAdmin() && !isUserGroup('editorperiodics')) {
                            fm.findField('PersonRefuse_IsRefuse').disable();
                            fm.findField('PersonSocCardNum_SocCardNum').disable();
                        } else {
						    fm.findField('PersonRefuse_IsRefuse').enable();
						    fm.findField('PersonSocCardNum_SocCardNum').enable();
                        }
					}

					// эти поля только для суперадмина
					//if ( mask != "000" )
					//{
					//	fm.findField('PersonRefuse_IsRefuse').disable();
					//	fm.findField('PersonSocCardNum_SocCardNum').disable();
					//}
					//else
					//{
					//	fm.findField('PersonRefuse_IsRefuse').enable();
					//	fm.findField('PersonSocCardNum_SocCardNum').enable();
					//}

					switch (mask)
					{
						// для суперадмина
						case "000":
						break;
						// для уфы
						case "ufafed":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							fm.findField('Person_SNILS').disable();
							// https://redmine.swan.perm.ru/issues/23197
							//if(isLpuAdmin()){
							    fm.findField('UAddress_AddressText').enable();
							/*}else{
							    fm.findField('UAddress_AddressText').disable();
							}*/
						break;
						case "100":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							if ( fm.findField('Polis_CanAdded').getValue() != 1 )
							{
								fm.findField('OMSSprTerr_id').disable();
								fm.findField('OrgSMO_id').disable();
								fm.findField('Polis_Ser').disable();
								fm.findField('Polis_Num').disable();
								fm.findField('Federal_Num').disable();
								fm.findField('Polis_begDate').disable();
								fm.findField('Polis_endDate').disable();
								fm.findField('PolisType_id').disable();
							}
							//fm.findField('SocStatus_id').disable();
						break;
						case "serv":
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							if ( fm.findField('Polis_CanAdded').getValue() != 1 )
							{
								fm.findField('OMSSprTerr_id').disable();
								fm.findField('OrgSMO_id').disable();
								fm.findField('Polis_Ser').disable();
								fm.findField('Polis_Num').disable();
								fm.findField('Federal_Num').disable();
								fm.findField('Polis_begDate').disable();
								//fm.findField('Polis_endDate').disable();
								fm.findField('PolisType_id').disable();
							}
							//fm.findField('SocStatus_id').disable();
						break;
						case "010":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							//fm.findField('Person_SNILS').disable();
						break;
						case "001":
						break;
						case "110":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							if ( fm.findField('Polis_CanAdded').getValue() != 1 )
							{
								fm.findField('OMSSprTerr_id').disable();
								fm.findField('OrgSMO_id').disable();
								fm.findField('Polis_Ser').disable();
								fm.findField('Polis_Num').disable();
								fm.findField('Federal_Num').disable();
								fm.findField('Polis_begDate').disable();
								fm.findField('Polis_endDate').disable();
								fm.findField('PolisType_id').disable();
							}
							//fm.findField('SocStatus_id').disable();
							//fm.findField('Person_SNILS').disable();
						break;
						case "101":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							if ( fm.findField('Polis_CanAdded').getValue() != 1 )
							{
								fm.findField('OMSSprTerr_id').disable();
								fm.findField('OrgSMO_id').disable();
								fm.findField('Polis_Ser').disable();
								fm.findField('Polis_Num').disable();
								fm.findField('Federal_Num').disable();
								fm.findField('Polis_begDate').disable();
								fm.findField('Polis_endDate').disable();
								fm.findField('PolisType_id').disable();
							}
							//fm.findField('SocStatus_id').disable();
						break;
						case "011":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							//fm.findField('Person_SNILS').disable();
						break;
						case "111":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							if ( fm.findField('Polis_CanAdded').getValue() != 1 )
							{
								fm.findField('OMSSprTerr_id').disable();
								fm.findField('OrgSMO_id').disable();
								fm.findField('Polis_Ser').disable();
								fm.findField('Polis_Num').disable();
								fm.findField('Federal_Num').disable();
								fm.findField('Polis_begDate').disable();
								fm.findField('Polis_endDate').disable();
								fm.findField('PolisType_id').disable();
							}
							if (getRegionNick().inlist(['buryatiya','vologda'])) {
								if (server_pid == 0) {
									fm.findField('SocStatus_id').disable();
								} else if (!!fm.findField('Person_SNILS').getValue()) {
									fm.findField('Person_SNILS').disable();
								}
							}
						break;
					}
					/*if ( fm.findField('PersonHeight_IsAbnorm').getValue() == 2 )
						fm.findField('HeightAbnormType_id').enable();
					if ( fm.findField('PersonWeight_IsAbnorm').getValue() == 2 )
						fm.findField('WeightAbnormType_id').enable();*/

					base_form.findField('PersonChild_IsInvalid').fireEvent('change', base_form.findField('PersonChild_IsInvalid'), base_form.findField('PersonChild_IsInvalid').getValue());
					base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());

					var diag_combo = fm.findField('Diag_id');
					var where = "where Diag_id = " + diag_combo.getValue();
					if ( diag_combo.getValue() > 0 )
					{
						diag_combo.getStore().load({
							params: {where: where},
							callback: function() {
								diag_combo.setValue(diag_combo.getValue());
								diag_combo.getStore().each(function(record) {
									if (record.data.Diag_id == diag_combo.getValue())
									{
										   diag_combo.fireEvent('select', diag_combo, record, 0);
									}
								});
							}
						});
					}

					var dpid = fm.findField('DeputyPerson_id').getValue();
					if ( fm.findField('DeputyPerson_id').getValue() > 0 )
					{
						fm.findField('DeputyPerson_id').getStore().loadData([{
							Person_id: dpid,
							Person_Fio: fm.findField('DeputyPerson_Fio').getValue()
						}]);
						fm.findField('DeputyPerson_id').setValue(dpid);
					}
					else
						fm.findField('DeputyPerson_id').getStore().removeAll();

					if(this.defaultFocusField!=null) {
						base_form.findField(this.defaultFocusField).focus(true, 300);
					} else
						if ( !form.findById('PEW_Person_SurName').disabled )
							form.findById('PEW_Person_SurName').focus(true, 300);
						else
							if ( !fm.findField('Person_SNILS').disabled )
									fm.findField('Person_SNILS').focus(true, 300);
							else
								if ( !fm.findField('SocStatus_id').disabled )
									fm.findField('SocStatus_id').focus(true, 300);
								else
									fm.findField('UAddress_AddressText').focus(true, 300);

					this.oldValues = base_form.getValues(true);
					this.oldValues += '&NationalityStatus_IsTwoNation' + '=' + encodeURIComponent(base_form.findField('NationalityStatus_IsTwoNation').getValue());
					this.oldValues += '&Person_IsUnknown' + '=' + encodeURIComponent(base_form.findField('Person_IsUnknown').getValue());
					this.oldValues += '&Person_IsAnonym' + '=' + encodeURIComponent(base_form.findField('Person_IsAnonym').getValue());
					if (base_form.findField('Person_IsAnonym').checked) {
						this.oldValues += '&Person_FirName' + '=' + encodeURIComponent(base_form.findField('Person_FirName').getValue());
						this.oldValues += '&Person_SecName' + '=' + encodeURIComponent(base_form.findField('Person_SecName').getValue());
					}
					if(getRegionNick() == 'ufa'){
						if(!this.findById('person_edit_form').getForm().findField('UAddress_AddressText').disabled){
							this.oldValues += '&UKLCountry_id' + '=' + encodeURIComponent(base_form.findField('UKLCountry_id').getValue());
							this.oldValues += '&UAddress_Address' + '=' + encodeURIComponent(base_form.findField('UAddress_Address').getValue());
						}
						if(!this.findById('person_edit_form').getForm().findField('BAddress_AddressText').disabled){
							this.oldValues += '&BKLCountry_id' + '=' + encodeURIComponent(base_form.findField('BKLCountry_id').getValue());
							this.oldValues += '&BAddress_Address' + '=' + encodeURIComponent(base_form.findField('BAddress_Address').getValue());
						}
						if(!this.findById('person_edit_form').getForm().findField('PAddress_AddressText').disabled){
							this.oldValues += '&PKLCountry_id' + '=' + encodeURIComponent(base_form.findField('PKLCountry_id').getValue());
							this.oldValues += '&PAddress_Address' + '=' + encodeURIComponent(base_form.findField('PAddress_Address').getValue());
						}
					}

					if (win.removeUnknown) {
						fm.findField('Person_IsUnknown').setValue(false);
					}

					// фикс сохранения полей после идентификации (refs #15400)
					// (в oldValues при идентификации должны попасть задисабленные поля полиса и фио, чтобы сработало их сохранение)
					var list = ['Person_BirthDay', 'PersonSex_id', 'OMSSprTerr_id', 'OrgSMO_id', 'Polis_Ser', 'Polis_Num', 'Federal_Num', 'Polis_begDate', 'Polis_endDate', 'PolisType_id', 'Person_SurName', 'Person_FirName', 'Person_SecName'];
					// открываем
					this.addToOldValuesForIdentification = '';
					for(var key in list) {
						if (typeof list[key] != 'function') {
							if (base_form.findField(list[key]) && base_form.findField(list[key]).disabled) {
								if ( base_form.findField(list[key]).getValue() instanceof Date ) {
									this.addToOldValuesForIdentification = this.addToOldValuesForIdentification + '&' + list[key] + '=' + encodeURIComponent(Ext.util.Format.date(base_form.findField(list[key]).getValue(), 'd.m.Y'));
								}
								else {
									this.addToOldValuesForIdentification = this.addToOldValuesForIdentification + '&' + list[key] + '=' + encodeURIComponent(base_form.findField(list[key]).getValue());
								}
							}
						}
					}
					this.oldValuesToRestore = base_form.getValues();
					this.disablePolisFields(true, true);
					this.disableDocumentFields(true, true);
					win.getLoadMask().hide();

					if ( base_form.findField('OrgDep_id').getValue() > 0 ) {
							base_form.findField('OrgDep_id').getStore().load({
							params: {
								Object:'OrgDep',
								OrgDep_id: base_form.findField('OrgDep_id').getValue(),
								OrgDep_Name: ''
							},
							callback: function() {
								base_form.findField('OrgDep_id').setValue(base_form.findField('OrgDep_id').getValue());
							}
						});
					}
					if ( base_form.findField('OMSSprTerr_id').getValue() > 0 && !inlist(mask, Array('100','serv', '110', '101', '111'))  )
						form.ownerCt.disablePolisFields(false);

					if (
						getRegionNick().inlist(['buryatiya','vologda']) &&
						mask == '111' &&
						base_form.findField('OMSSprTerr_id').getValue() > 0 &&
						server_pid != 0
					) {
						form.ownerCt.disablePolisFields(false);
					}
						
					if ( base_form.findField('OMSSprTerr_id').getValue() > 0 )
					{
						var combo = base_form.findField('OMSSprTerr_id');
						var OrgSMOCombo	= base_form.findField('OrgSMO_id');
						OrgSMOCombo.lastQuery = '';

						this.changeDocVerificationDependingOMSTerr(this.findById('person_edit_form').getForm().findField('DocumentType_id').getValue());

						//var idx = combo.getStore().find('OMSSprTerr_id', combo.getValue());
						var number = combo.getValue();
						var idx = -1;
						var findIndex = 0;
						combo.getStore().findBy(function(r) {
							if ( r.data['OMSSprTerr_id'] == number )
							{
								idx = findIndex;
								return true;
							}
							findIndex++;
						});
						if ( idx >= 0 )
						{
							var code = combo.getStore().getAt(idx).data.OMSSprTerr_Code;
							var klrgn_id = combo.getStore().getAt(idx).data.KLRgn_id;

							// если регион сервера Уфа и выбран уфимский, то устанавливаем соответствующее правило
							// для проверки единого номера полиса на уфимском сервере
							// TODO: Скорее всего это условие на регион надо будет убрать, потому что для перми сейчас также
							if ( getRegionNick() == 'ufa' && base_form.findField('PolisType_id').getValue() == 4 )
							{
								base_form.findField('Polis_Num').minLength = 0;
								base_form.findField('Polis_Num').maxLength = 16;

								// return; - для чего это о_О убрал.
							}
							else
							{
								base_form.findField('Polis_Num').clearInvalid();
								if ( getRegionNick() == 'ufa' && klrgn_id==2)
								{
									if ( base_form.findField('PolisType_id').getValue() != 3  ) {
										base_form.findField('Polis_Num').minLength = 16;
										base_form.findField('Polis_Num').maxLength = 16;
									}
									else {
										base_form.findField('Polis_Num').minLength = 9;
										base_form.findField('Polis_Num').maxLength = 9;
									}
								}
								else
								{
									if ( base_form.findField('PolisType_id').getValue() == 3 ) {
										if(getRegionNick()=='perm'){
											base_form.findField('Polis_Num').minLength = 5;
											base_form.findField('Polis_Num').maxLength = 99;
										}else{
											base_form.findField('Polis_Num').minLength = (getRegionNick().inlist([ 'astra']) ? 6 : 9);
											base_form.findField('Polis_Num').maxLength = 9;
										}
										base_form.findField('Polis_Ser').minLength = (getRegionNick().inlist([ 'astra' ]) ? 3 : 0);
										//base_form.findField('Polis_Ser').maxLength = (getRegionNick().inlist([ 'kareliya' ]) ? 3 : undefined);
									}else{
										base_form.findField('Polis_Num').minLength = 0;
										base_form.findField('Polis_Num').maxLength = 18;
									}
								}
							}
							if ( code <= 61 )
							{
								base_form.findField('Polis_Ser').disableTransPlug = false;
								// Если не уфа и полис не нового образца
								if ( !(getRegionNick() == 'ufa') &&  (base_form.findField('PolisType_id').getFieldValue('PolisType_Code')!=4) ) {
									// то серия полиса обязательна для ввода
									base_form.findField('Polis_Num').setAllowBlank(false);
								}
								else
								{
									// иначе же нет
									base_form.findField('Polis_Ser').disableTransPlug = true;
									base_form.findField('Polis_Num').setAllowBlank(getRegionNick() != 'ufa');
								}
							}
							else
							{
								base_form.findField('Polis_Ser').disableTransPlug = true;
								base_form.findField('Polis_Num').setAllowBlank(true);
							}
							var cur_reg = getGlobalOptions().region ? getGlobalOptions().region['number'] : 59;
							//if ( /*( code < 100 && cur_reg == 59 ) ||*/ ( cur_reg == klrgn_id ) )
							if ( cur_reg == 59 &&  cur_reg == klrgn_id )
							{
								OrgSMOCombo.baseFilterFn = function(record) {
									if ( /.+/.test(record.get('OrgSMO_RegNomC')) && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue()) )
										return true;
									else
										return false;
								}
								OrgSMOCombo.getStore().filterBy(function(record) {
									if ( /.+/.test(record.get('OrgSMO_RegNomC')) && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue()) )
										return true;
									else
										return false;
								})
							}
							else
							{
								OrgSMOCombo.baseFilterFn = function(record) {
									if ( klrgn_id == record.get('KLRgn_id') && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue())  )
										return true;
									else
										return false;
								}
								OrgSMOCombo.getStore().filterBy(function(record) {
									if ( klrgn_id == record.get('KLRgn_id') && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue())  )
										return true;
									else
										return false;
								});
								/*OrgSMOCombo.baseFilterFn = null;
								OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', '');*/
							}
							if ( cur_reg == 19 ) {
								base_form.findField('Polis_Ser').disableTransPlug = true;
								if ( code == 1 ) {
									base_form.findField('Polis_Ser').maskRe = /[smSMа-яА-ЯёЁ\d\s\-]/;
								} else {
									base_form.findField('Polis_Ser').maskRe = /[a-zA-Zа-яА-ЯёЁ\d\s\-]/;
								}
							}
							OrgSMOCombo.setValue(OrgSMOCombo.getValue());
						}
					}

					// если есть признак того, что можем добавлять полис, то фильтруем список территорий другие регионы + текущая
					// но если это суперадмин, то нет
					if ( base_form.findField('Polis_CanAdded').getValue() == 1 && mask != '000' && !getRegionNick().inlist([ 'krym', 'penza' ]) )
					{
						var terr_combo = base_form.findField('OMSSprTerr_id');
						var terr_id = terr_combo.getValue();
						terr_combo.getStore().filterBy(function(record) {
							if ( record.get('KLRgn_id') != getGlobalOptions().region['number'] )
								return true;
							else
								return false;
						});
						terr_combo.baseFilterFn = function(record) {
							if ( record.get('KLRgn_id') != getGlobalOptions().region['number'] )
								return true;
							else
								return false;
						}
					}
					else
					{
						var terr_combo = base_form.findField('OMSSprTerr_id');
						var terr_id = terr_combo.getValue();
						terr_combo.getStore().filterBy(function(record) {
								return true;
						});
						terr_combo.baseFilterFn = function(record) {
							return true;
						}
					}

					var doc_type_field = base_form.findField('DocumentType_id');
					if ( doc_type_field.getValue() > 0 ) {
						var doc_type_record = doc_type_field.getStore().getById(doc_type_field.getValue());
						if (doc_type_record) {
							doc_type_field.fireEvent('select',doc_type_field, doc_type_record);
							doc_type_field.fireEvent('blur',doc_type_field);
						}
					}

					if ( !Ext.isEmpty(KLCountry_id) ) {
						base_form.findField('KLCountry_id').setValue(KLCountry_id);
					}

					if (base_form.findField('KLCountry_id').getFieldValue('KLCountry_Code') == 643) {
						base_form.findField('NationalityStatus_IsTwoNation').enable();
						base_form.findField('LegalStatusVZN_id').hideContainer();
						base_form.findField('LegalStatusVZN_id').setValue(null);
					} else {
						base_form.findField('NationalityStatus_IsTwoNation').disable();
						base_form.findField('NationalityStatus_IsTwoNation').setValue(false);
						base_form.findField('LegalStatusVZN_id').showContainer();
					}

					if ( base_form.findField('Org_id').getValue() > 0 )
							base_form.findField('Org_id').getStore().load({
								params: {
									Object:'Org',
									Org_id: base_form.findField('Org_id').getValue(),
									Org_Name:''
								},
								callback: function()
								{
									base_form.findField('Org_id').setValue(base_form.findField('Org_id').getValue());
								}
							});
					if ( base_form.findField('Org_id').getValue() > 0 )
					{
						var Org_id = base_form.findField('Org_id').getValue();
						form.findById('PEW_OrgUnion_id').getStore().load({
							params: {
								Object:'OrgUnion',
								OrgUnion_id:'',
								OrgUnion_Name:'',
								Org_id: Org_id
							},
							callback: function()
							{
								base_form.findField('OrgUnion_id').setValue(base_form.findField('OrgUnion_id').getValue());
							}
						});
					}
					if ( base_form.findField('Post_id').getValue() > 0 )
					base_form.findField('Post_id').getStore().load({
							params: {
								Object:'Post',
								Post_id:'',
								Post_Name:'',
								Post_curid: base_form.findField('Post_id').getValue()
							},
							callback: function() {
								base_form.findField('Post_id').setValue(base_form.findField('Post_id').getValue());
							}
					});
					fm.clearInvalid();
					this.PersonEval.removeAll();
					this.PersonEval.loadData({
						globalFilters: {

							Person_id: this.personId
						}
					});
					this.PersonFeedingType.removeAll();
					this.PersonFeedingType.loadData({
						globalFilters: {

							Person_id: this.personId,
							PersonChild_id: base_form.findField('PersonChild_id').getValue()
						}
					});
					this.NewslatterAcceptGrid.removeAll();
					this.NewslatterAcceptGrid.loadData({globalFilters: {Person_id: this.personId}});
					this.FamilyRelationGrid.removeAll();
					this.FamilyRelationGrid.loadData({globalFilters: {Person_id: this.personId}});
					//загрузка данных по измерениям
					/*this.PersonMeasureGrid.removeAll();
					this.PersonMeasureGrid.loadData({
						globalFilters: {
							limit: 100,
							start: 0,
							person_id: this.personId
						}
					});*/
					// в поле Тип документа по умолчанию устанавливаем Паспорт гражданина РФ, если никакого нет
					if ( getRegionNick() == 'ufa' ){
						var doc_type_field = base_form.findField('DocumentType_id');
					 	if (!doc_type_field.getValue()){
							doc_type_field.getStore().load({
							callback: function(cb)
							{
								doc_type_field.getStore().each(function(rec){
									if(rec.get('DocumentType_Code')=='14'){
										doc_type_field.fireEvent('select',doc_type_field, rec);
										doc_type_field.fireEvent('blur',doc_type_field);
									}
								})

							}
							})

					 	}
					}

					// #129470 блокировка элементов в режиме просмотра
					if(this.readOnly === true) {
						//console.log('---вкладка Пациент');
						this.disableFieldsInViewMode(Ext.getCmp('pacient_tab'));

						//console.log('---вкладка Дополнительно');
						this.disableFieldsInViewMode(Ext.getCmp('additional_tab'));

						//console.log('---вкладка Специфика. Детство');
						this.disableFieldsInViewMode(Ext.getCmp('spec_tab'));
					}

					//console.log('---Таблица: Родственные связи');
					var personFamilyRelationGrid = Ext.getCmp('PEW_FamilyRelationGrid');
					personFamilyRelationGrid.setActionDisabled('action_add', this.readOnly);
					personFamilyRelationGrid.setActionDisabled('action_edit', this.readOnly);
					personFamilyRelationGrid.setActionDisabled('action_delete', this.readOnly);
					personFamilyRelationGrid.editformclassname = (this.readOnly)?'':'swFamilyRelationEditWindow';// защита от клика по элементу таблицы

					//console.log('---Таблица: Оценка физического развития');
					var personEvalGrid = Ext.getCmp('PersonEval');
// 					personEvalGrid.setActionDisabled('action_add', this.readOnly);
					personEvalGrid.setActionDisabled('action_edit', this.readOnly);
					personEvalGrid.setActionDisabled('action_delete', this.readOnly);
					personEvalGrid.editformclassname = (this.readOnly)?'':'swPersonEvalEditWindow';// защита от клика по элементу таблицы

					//console.log('---Таблица: Способ вскармливания');
					var personFeedingType = Ext.getCmp('PersonFeedingType');
					personFeedingType.setActionDisabled('action_add', this.readOnly);
					personFeedingType.setActionDisabled('action_edit', this.readOnly);
					personFeedingType.setActionDisabled('action_delete', this.readOnly);
					personFeedingType.editformclassname = (this.readOnly)?'':'swPersonFeedingTypeEditWindow';// защита от клика по элементу таблицы

					//проверка заполненности адреса
					win.checkAdreesAdd();

					this.findById('DocumentDeputy').setVisible(!Ext.isEmpty(base_form.findField('DeputyPerson_id').getValue()) && getRegionNick()!='kz');
				}.createDelegate(this),
				url: url
			});

		}

		if(arguments[0] && arguments[0].focused) {
			this.defaultFocusField = arguments[0].focused;
		}
		if ( arguments[0].fields ) {

			var ss = arguments[0].fields.SocStatus;
			if(ss=='babyborn'){
				this.childAdd=true;
				this.minBirtDay = arguments[0].fields.Person_BirthDay;
				this.findById('person_edit_form').getForm().setValues(arguments[0].fields);
				switch(getRegionNick()){
					default:
						base_form.findField('SocStatus_id').setFieldValue('SocStatus_SysNick','child_doma');
						break;
				}
			}else{
				this.findById('person_edit_form').getForm().setValues(arguments[0].fields);
			}

		}

		if ( this.action == 'add' ) {
			base_form.findField('Person_SurName').focus(true, 500);
			base_form.findField('PersonChild_IsInvalid').fireEvent('change', base_form.findField('PersonChild_IsInvalid'), null);
			base_form.findField('Person_IsUnknown').fireEvent('check', base_form.findField('Person_IsUnknown'), base_form.findField('Person_IsUnknown').getValue());
			if ( !getGlobalOptions().superadmin )
			{
				base_form.findField('PersonRefuse_IsRefuse').disable();
				base_form.findField('PersonSocCardNum_SocCardNum').disable();
			}
			else
			{
				base_form.findField('PersonRefuse_IsRefuse').enable();
				base_form.findField('PersonSocCardNum_SocCardNum').enable();
			}
			win.checkOkeiRequired();
			win.refreshIdentInErzFieldSet();
			win.refreshIdentInTfomsFieldSet();
		}

		//base_form.clearInvalid();
		base_form.findField('Polis_Ser').disableTransPlug = false;
		if ( !getRegionNick().inlist(['ufa', 'khak']) ) {
			//base_form.findField('Polis_Ser').setAllowBlank(false);
		}
		else
		{
			base_form.findField('Polis_Ser').disableTransPlug = true;
		}
		if ( getRegionNick() == 'ufa' )
		{
			// фильтруем соц. статусы
			/*base_form.findField('SocStatus_id').getStore().filterBy(function(record) {
				if ( ['1', '2', '3', '4', '5'].in_array(record.get('SocStatus_Code')) )
					return true;
			});
			base_form.findField('SocStatus_id').getStore().baseFilterFn = function(record) {
				if ( ['1', '2', '3', '4', '5'].in_array(record.get('SocStatus_Code')) )
					return true;
			}*/
			// удаляем неопределнный пол для Уфы
			var sex_store = base_form.findField('PersonSex_id').getStore();
			sex_store.filterBy(function(rec) {
				return (rec.get('Sex_Code') != 3);
			});

			// при добавлении в поле Тип документа по умолчанию устанавливаем Паспорт гражданина РФ
			var doc_type_field = base_form.findField('DocumentType_id');
			if (this.action == 'add')
			{
				if(arguments[0].fields&&arguments[0].fields.SocStatus=="babyborn"){}else{
					 	if (!doc_type_field.getValue()){
							doc_type_field.getStore().load({
							callback: function(cb)
							{
								doc_type_field.getStore().each(function(rec){
									if(rec.get('DocumentType_Code')=='14'){
										doc_type_field.fireEvent('select',doc_type_field, rec);
										doc_type_field.fireEvent('blur',doc_type_field);
									}
								})

							}
							})

					 	}
				}
			}
		}
        base_form.findField('PersonInfo_InternetPhone').disable();

		//Обязательность заполнения поля «Гражданство»
		var citizenship_control = Ext.globalOptions.globals.citizenship_control;
		var KLCountry = base_form.findField('KLCountry_id');
		if(citizenship_control == 3){
			KLCountry.setAllowBlank(false);
		}else{
			KLCountry.setAllowBlank(true);
		}

		this.NewslatterAcceptGrid.addActions({ name: 'action_print_menu', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls: 'x-btn-text', icon: 'img/icons/print16.png', menu: [
			win.printAccept = new Ext.Action({name:'print_accept', text: lang['soglasie_na_poluchenie_sms_e-mail_uvedomleniy'], handler: function() { this.printNewslatterAccept('printAccept'); }.createDelegate(this)}),
			win.printDenial = new Ext.Action({name:'print_denial', text: lang['otkaz_ot_polucheniya_uvedomleniy'], handler: function() { this.printNewslatterAccept('printDenial'); }.createDelegate(this)})
		]});

		v = {
			action: 'add',

			formParams:
				{
					Person_id: win.personId,
					Server_id: win.serverId
				},

			callback: function (options, success, response) {
				if (success)
					log(success);

				win.PersonEval.refreshRecords(null, 0)
				return true;
			}
		};

		this.PersonEval.remove(this._personEvalBtnAdd, true);
		this._personEvalBtnAdd =
			this.PersonEval.ViewToolbar.insertButton(0,
				{
					name: 'add',
					key: 'add',
					text: BTN_GRIDADD,
					tooltip: BTN_GRIDADD_TIP,
					iconCls: 'x-btn-text',
					icon: 'img/icons/add16.png',

					menu:
						[
							{
								name: 'addWeight',
								text: langs('ves'),

								handler: function () {
									if (win.personId > 0)
										getWnd('swPersonWeightEditWindow')
											.show(v);
								}
							},
							{
								name: 'addHeight',
								text: langs('rost'),

								handler: function () {
									if (win.personId > 0)
										getWnd('swPersonHeightEditWindow')
											.show(v);
								}
							},
							{
								name: 'addHeadCircumference',
								text: langs('Окружность головы'),

								handler: function () {
									if (win.personId > 0)
										getWnd('swHeadCircumferenceEditWindow')
											.show(v);
								}
							},
							{
								name: 'addChestCircumference',
								text: langs('Окружность груди'),

								handler: function () {
									if (win.personId > 0)
										getWnd('swChestCircumferenceEditWindow')
											.show(v);
								}
							}
						]
				});

		if(false) this.disableEditNotApiFields();
	},
	initComponent: function() {
		var win = this;
		win.identERZService = (getRegionNick() == 'perm' ? 'ЦС' : (getRegionNick() == 'msk' ? 'АС' : 'РС'));

		// Раздел "Оценка физического развития" на вкладке "3. Специфика. Детство.":
		this.PersonEval = new sw.Promed.ViewFrame({
			auditOptions: {
				key: 'PersonEvalClass_id'
			},
			id: 'PersonEval',
			border: true,
			autoLoadData: false,
			height: 200,
			region: 'center',
			editformclassname: 'swPersonEvalEditWindow',
			dataUrl: '/?c=Person&m=loadPersonEval',
			actions:
			[
				{name: 'action_add', hidden: true},
				{name: 'action_view', hidden: true},
				{name: 'action_delete'} // Вроде никаких дополнительных действий не планируется
			],
			stringfields:
			[
				{name: 'PersonEval_id', type: 'string', header: 'ID', key: true},
				{name: 'PersonEvalClass', type: 'string', hidden: true},
				{name: 'PersonEvalClass_id', type: 'int', hidden: true},
				{name: 'EvalType', type: 'string',  header: lang['pokazatel'],width:105},
				{name: 'PersonEval_setDT', type: 'date', format: 'd.m.Y',width:125, header: lang['data_izmereniya']},
				{name: 'EvalMeasureType', type: 'string', header:lang['vid_zamera'],width:125},
				{name: 'EvalMeasureTypeCode', type: 'string', header:langs('Код вида замера'),hidden:true},
				{name: 'PersonEval_value', type: 'string', header: lang['znachenie'],width:125},
				{name: 'PersonEval_isAbnorm', type: 'string', header:lang['otklonenie'],width:100},
				{name: 'EvalAbnormType', type:'string',header:lang['tip_otkloneniya'],width:150}
			],
			params: {
				callback: function(options, success, response) {
					if(success){log(success)}
					win.PersonEval.refreshRecords(null,0)
					return true;
				}
			},

			getMoreParamsForEdit: function()
			{
				var v,
					pars = {};

				if (v = this.auditOptions.field)
					pars[v] = this.auditOptions.id;

				pars.Person_id = win.personId;

				return ({ formParams: pars });
			},

			// Выбор измерения в таблице:
			// При выборе измерений окружности головы и груди, сделанных при рождении,
			// кнопки "Редактировать" и "Удалить" становятся недоступными.
			onRowSelect: function()
			{
				var grid = this.getGrid(),
					record = grid.getSelectionModel().getSelected(),
					mtCode = record.get('EvalMeasureTypeCode'),
					itemsMap = this.ViewToolbar.items.map,

					// блокировать ли кнопки "Редактировать" и "Удалить":
					flag = false;

				this.auditOptions.field = record.get('PersonEvalClass');
				this.auditOptions.id = record.get('PersonEvalClass_id');

				switch (this.auditOptions.field)
				{
					// Окружность головы:
					case 'HeadCircumference_id':
						this.editformclassname = 'swHeadCircumferenceEditWindow';
						flag = (mtCode == '1');
						break;

					// Окружность груди:
					case 'ChestCircumference_id':
						this.editformclassname = 'swChestCircumferenceEditWindow';
						flag = (mtCode == '1');
						break;

					// Остальные (рост или вес):
					default:
						this.editformclassname = 'swPersonEvalEditWindow';
						break;
				}

				if (flag)
				{
					itemsMap.id_action_edit.disable();
					itemsMap.id_action_delete.disable();
				}
				else
				{
					itemsMap.id_action_edit.enable();
					itemsMap.id_action_delete.enable();
				}
			},

			// Удаление измерения из таблицы:
			deleteRecord: function()
			{
				sw.swMsg.show(
					{
						icon: Ext.MessageBox.QUESTION,
						title: lang['vopros'],
						msg: lang['udalit_pokazatel_izmereniya'],
						buttons: sw.swMsg.YESNO,

						fn: function(buttonId, text, obj)
						{
							var grid,
								record,
								params = {},
								evalClass,
								evalType;

							if (buttonId != 'yes')
								return;

							grid = this.getGrid();
							record = grid.getSelectionModel().getSelected();

							if (typeof g_RegistryType_id !== 'undefined' &&
									'ufa' ==  getRegionNick())
								params.RegistryType_id = g_RegistryType_id;

							evalClass = record.get('PersonEvalClass');
							params.EvalType = evalClass.substr(0, evalClass.length - 3);

							params.PersonEval_id =
								record.get('PersonEval_id').substr(params.EvalType.length);

							Ext.Ajax.request(
								{
									url:'/?c=Person&m=deletePersonEval',
									params:params,

									callback: function(options, success, response)
									{
										if (success)
										{
											if (!grid.getSelectionModel().getSelected())
												return false;

											grid.getStore().remove(record);

											if (grid.getStore().getCount() == 0)
											{
												grid.getTopToolbar().items.items[1].disable();
												grid.getTopToolbar().items.items[2].disable();
												grid.getTopToolbar().items.items[3].disable();
											}
											else
											{
												grid.getView().focusRow(0);
												grid.getSelectionModel().selectFirstRow();
											}
										}
									}.createDelegate(this)
								})
						}.createDelegate(this)
					})
			}
		});

		this.PersonFeedingType = new sw.Promed.ViewFrame({
			auditOptions: {
				key: 'FeedingTypeAge_id'
			},
			id: 'PersonFeedingType',
			border: true,
			autoLoadData: false,
			height: 200,
			region: 'center',
			editformclassname: 'swPersonFeedingTypeEditWindow',
			dataUrl: '/?c=PersonFeedingType&m=loadPersonFeedingType',
			actions:
				[
					{name:'action_add', handler: function() {
						var formParams = new Object();
						if(win.personId>0){
							formParams.Server_id = this.findById('person_edit_form').getForm().findField('Server_pid').getValue();
							formParams.FeedingTypeAge_id = 0;
						formParams.Person_id = win.personId;
						formParams.PersonChild_id = this.findById('person_edit_form').getForm().findField('PersonChild_id').getValue();
							getWnd('swPersonFeedingTypeEditWindow').show({formParams: formParams, action:'add'})
						}}.createDelegate(this)},
					{name:'action_edit', handler: function() {
						var grid = Ext.getCmp('PersonFeedingType').getGrid();
						var selected_record = grid.getSelectionModel().getSelected();
						var formParams = new Object();
						if(win.personId>0){
							formParams.FeedingTypeAge_id = selected_record.get('FeedingTypeAge_id');
							formParams.Person_id = win.personId;
							formParams.PersonChild_id = this.findById('person_edit_form').getForm().findField('PersonChild_id').getValue();
							getWnd('swPersonFeedingTypeEditWindow').show({formParams: formParams, action:'edit'})
						}}.createDelegate(this)},
					{name:'action_view',hidden:true},
					{name:'action_delete'} // Вроде никаких дополнительных действий не планируется
				],
			stringfields:
				[

					{name: 'FeedingTypeAge_id', type: 'string', header: 'ID', key: true},
					{name: 'FeedingTypeAge_Age', type: 'string',  header: langs('Возраст (мес)'),width:130},
					{name: 'FeedingType_Name', type: 'string', header:langs('Вид вскармливания'),width:600}
				],
			params: {
				callback: function(options, success, response) {
					if(success){log(success)}
					win.PersonFeedingType.refreshRecords(null,0)
					return true;
				}
			},
			onRowSelect: function() {
				var grid = this.getGrid();
				var selected_record = grid.getSelectionModel().getSelected();
				this.auditOptions.field = selected_record.get('PersonFeedingTypeClass');
			},
			deleteRecord: function() { // удаление измерения из таблицы
				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							var grid = this.getGrid();
							var selected_record = grid.getSelectionModel().getSelected();
							var params ={FeedingTypeAge_id: selected_record.get('FeedingTypeAge_id') }


							Ext.Ajax.request({callback: function(options, success, response) {
								if(success){
									if (!grid.getSelectionModel().getSelected()) {
										return false;
									}

									grid.getStore().remove(selected_record);
									if (grid.getStore().getCount() == 0) {
										grid.getTopToolbar().items.items[1].disable();
										grid.getTopToolbar().items.items[2].disable();
										grid.getTopToolbar().items.items[3].disable();
									} else {
										grid.getView().focusRow(0);
										grid.getSelectionModel().selectFirstRow();
									}
								}
							}.createDelegate(this),
								params:params,
								url:'/?c=PersonFeedingType&m=deletePersonFeedingType'
							})



						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Удалить запись?'),
					title: langs('Вопрос')
				})
			}
		});

		this.NewslatterAcceptGrid = new sw.Promed.ViewFrame({
			id: 'PEW_NewslatterAcceptGrid',
			border: true,
			autoLoadData: false,
		 	height: Ext.isIE ? 450 : 430,
			region: 'center',
			object: 'NewslatterAccept',
			editformclassname: 'swNewslatterAcceptEditForm',
			dataUrl: '/?c=NewslatterAccept&m=loadList',
            actions: [
                { name: 'action_add', handler: function() {
						if(win.personId>0) getWnd('swNewslatterAcceptEditForm').show({
							Person_id: win.personId,
							action:'add',
							callback: function(options, success, response) {
								win.NewslatterAcceptGrid.refreshRecords(null,0);
								if (success == true && response) {
									win.askPrintNewslatterAccept(response);
								}
								return true;
							}
						})
					}.createDelegate(this)
				},
                { name: 'action_edit' },
                { name: 'action_view', hidden: true, disabled: true },
                { name: 'action_delete' },
                { name: 'action_refresh' },
                { name: 'action_print', hidden: true, disabled: true }
            ],
			stringfields:
			[
				{name: 'NewslatterAccept_id', type: 'int', key: true},
				{name: 'Lpu_id', type: 'int', hidden: true},
				{name: 'Lpu_Nick', type: 'string', header: lang['mo']},
				{name: 'NewslatterAccept_begDate', type: 'date', format: 'd.m.Y', header: lang['data_soglasiya']},
				{name: 'NewslatterAccept_IsSMS', type: 'checkbox', header: lang['uvedomleniya_po_sms']},
				{name: 'NewslatterAccept_Phone', type: 'string', header: lang['nomer_telefona']},
				{name: 'NewslatterAccept_IsEmail', type: 'checkbox', header: lang['uvedomleniya_po_e-mail']},
				{name: 'NewslatterAccept_Email', type: 'string', header: 'E-mail'},
				{name: 'NewslatterAccept_endDate', type: 'date', format: 'd.m.Y', header: lang['data_otkaza_ot_rassyilok']}
			],
			params: {
				callback: function(options, success, response) {
					win.NewslatterAcceptGrid.refreshRecords(null,0);
					if (success == true && response) {
						win.askPrintNewslatterAccept(response);
					}
					return true;
				}
			},
			onRowSelect: function(sm, rowIdx, record) {
				if ( !record || !record.get('NewslatterAccept_id') ) {
					return false;
				}

				if ( record.get('Lpu_id') != getGlobalOptions().lpu_id ) {
					this.setActionDisabled('action_edit', true);
					this.setActionDisabled('action_delete', true);
				} else {
					this.setActionDisabled('action_edit', false);
					this.setActionDisabled('action_delete', false);
				}

				win.printDenial.setDisabled(Ext.isEmpty(record.get('NewslatterAccept_endDate')));
			},
			deleteRecord: function() {
				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							var grid = this.getGrid();
							var selected_record = grid.getSelectionModel().getSelected();
							var params = {NewslatterAccept_id: selected_record.get('NewslatterAccept_id')};
							if(typeof g_RegistryType_id !== 'undefined' && 'ufa' ==  getRegionNick())
								params.RegistryType_id = g_RegistryType_id;
							Ext.Ajax.request({
								callback: function(options, success, response) {
									if(success){
										if (!grid.getSelectionModel().getSelected()) {
											return false;
										}
										grid.getStore().remove(selected_record);
										if (grid.getStore().getCount() == 0) {
											grid.getTopToolbar().items.items[1].disable();
											grid.getTopToolbar().items.items[2].disable();
										} else {
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}
								}.createDelegate(this),
								params: params,
								url:'/?c=NewslatterAccept&m=delete'
							})
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['udalit_soglasie_na_poluchenie_uvedomleniy'],
					title: lang['vopros']
				})
			}
		});

		this.FamilyRelationGrid = new sw.Promed.ViewFrame({
			id: 'PEW_FamilyRelationGrid',
			border: true,
			frame: true,
			bodyStyle: 'margin: -5px',
			style: 'margin-top: 7px',
			autoLoadData: false,
		 	height: 200,
			object: 'FamilyRelation',
			editformclassname: 'swFamilyRelationEditWindow',
			dataUrl: '/?c=FamilyRelation&m=loadList',
            actions: [
                { name: 'action_add', handler: function() {
						if(win.personId>0) getWnd('swFamilyRelationEditWindow').show({
							Person_id: win.personId,
							action:'add',
							callback: function(options, success, response) {
								win.FamilyRelationGrid.refreshRecords(null,0);
								return true;
							}
						})
					}.createDelegate(this)
				},
                { name: 'action_edit' },
                { name: 'action_view' },
                { name: 'action_delete' },
                { name: 'action_refresh' },
                { name: 'action_print', hidden: true, disabled: true }
            ],
			stringfields: [
				{name: 'FamilyRelation_id', type: 'int', key: true},
				{name: 'Person_id', type: 'int', hidden: true},
				{name: 'FamilyRelationType_id', type: 'int', hidden: true},
				{name: 'FamilyRelationType_Name', type: 'string', header: 'Тип'},
				{name: 'FamilyRelation_SurName', type: 'string', header: 'Фамилия'},
				{name: 'FamilyRelation_FirName', type: 'string', header: 'Имя'},
				{name: 'FamilyRelation_SecName', type: 'string', header: 'Отчество'},
				{name: 'FamilyRelation_BirthDay', type: 'date', format: 'd.m.Y', header: 'Дата рождения'},
				{name: 'FamilyRelation_begDate', type: 'date', format: 'd.m.Y', header: 'Дата начала'},
				{name: 'FamilyRelation_endDate', type: 'date', format: 'd.m.Y', header: 'Дата окончания'}
			],
			params: {
				callback: function(options, success, response) {
					win.FamilyRelationGrid.refreshRecords(null,0);
					return true;
				}
			},
			deleteRecord: function() {
				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							var grid = this.getGrid();
							var selected_record = grid.getSelectionModel().getSelected();
							var params = {FamilyRelation_id: selected_record.get('FamilyRelation_id')};
							Ext.Ajax.request({
								callback: function(options, success, response) {
									if(success){
										if (!grid.getSelectionModel().getSelected()) {
											return false;
										}
										grid.getStore().remove(selected_record);
										if (grid.getStore().getCount() == 0) {
											grid.getTopToolbar().items.items[1].disable();
											grid.getTopToolbar().items.items[2].disable();
										} else {
											grid.getView().focusRow(0);
											grid.getSelectionModel().selectFirstRow();
										}
									}
								}.createDelegate(this),
								params: params,
								url:'/?c=FamilyRelation&m=delete'
							})
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Удалить выбранную связь?',
					title: lang['vopros']
				})
			},
			title: 'Родственные связи'
		});

		/*this.PersonMeasureGrid = new sw.Promed.ViewFrame({
			id: 'PEW_PersonMeasureGrid',
			border: true,
			autoLoadData: false,
			//focusOnFirstLoad: false,
		 	height: Ext.isIE ? 530 : 480,
			region: 'center',
			object: 'PersonMeasure',
			editformclassname: 'swPersonMeasureEditWindow',
			dataUrl: '/?c=Rate&m=loadPersonMeasureList',
			root: 'data',
			totalProperty: 'totalCount',
			paging: true,
			stringfields:
			[
				{name: 'PersonMeasure_id', type: 'int', header: 'ID', key: true},
				{name: 'date', type: 'date', format: 'd.m.Y', header: lang['data_provedeniya']},
				{name: 'lpusection_name', type: 'string', header: lang['otdelenie_lpu'], width:300},
				{name: 'medpersonal_fio', type: 'string', header: lang['vrach'], width:300},
				{name: 'LpuSection_id', type: 'int', hidden: true, isparams: true},
				{name: 'MedPersonal_id', type: 'int', hidden: true, isparams: true},
				{name: 'PersonMeasure_setDT_Date', type: 'date', format: 'd.m.Y', hidden: true, isparams: true},
				{name: 'PersonMeasure_setDT_Time', type: 'string', hidden: true, isparams: true},
				{name: 'Record_Status', type: 'int', hidden: true, isparams: true},
				{name: 'RateGrid_Data', type: 'string', hidden: true, isparams: true},
				{name: 'RateGrid_DataNumber', type: 'string', hidden: true, isparams: true}
			],
			params: {
				callback: function(data, add_flag) {
					var i;
					var measure_fields = new Array();
					var current_window = Ext.getCmp('PersonEditWindow');
					var grid = current_window.findById('PEW_PersonMeasureGrid').getGrid();

					grid.getStore().fields.eachKey(function(key, item) {
						measure_fields.push(key);
					});
					if (add_flag == true) {
						// удаляем пустую строку если она есть
						if (grid.getStore().getCount() == 1) {
							var selected_record = grid.getStore().getAt(0);

							if (selected_record.data.PersonMeasure_id == null || selected_record.data.PersonMeasure_id == '') {
								grid.getStore().removeAll();
							}
						}
						grid.getStore().clearFilter();
						grid.getStore().loadData({data: data}, add_flag);
						grid.getStore().filterBy(function(record) {
							if (record.data.Record_Status != 3) {
								return true;
							}
						});
					} else {
						index = grid.getStore().find('PersonMeasure_id', data[0].PersonMeasure_id);
						if (index == -1) {
							return false;
						}
						var record = grid.getStore().getAt(index);
						for (i = 0; i < measure_fields.length; i++) {
							record.set(measure_fields[i], data[0][measure_fields[i]]);
						}
						record.commit();
					}
					return true;
				}
			},
			deleteRecord: function() { // удаление измерения из таблицы
				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							var measure_grid = this.getGrid();

							if (!measure_grid.getSelectionModel().getSelected()) {
								return false;
							}

							var selected_record = measure_grid.getSelectionModel().getSelected();
							if (selected_record.data.Record_Status == 0) {
								measure_grid.getStore().remove(selected_record);
							} else {
								selected_record.set('Record_Status', 3);
								selected_record.commit();
								measure_grid.getStore().filterBy(function(record) {
									if (record.data.Record_Status != 3) {
										return true;
									}
								});
							}


							if (measure_grid.getStore().getCount() == 0) {
								measure_grid.getTopToolbar().items.items[1].disable();
								measure_grid.getTopToolbar().items.items[2].disable();
								measure_grid.getTopToolbar().items.items[3].disable();
							} else {
								measure_grid.getView().focusRow(0);
								measure_grid.getSelectionModel().selectFirstRow();
							}

							//if ( measure_grid.getStore().getCount() == 0 )
							//	LoadEmptyRow(measure_grid, 'data');
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['udalit_izmerenie_pokazateley_zdorovya'],
					title: lang['vopros']
				})
			}
		});*/

		Ext.apply(this, {
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyStyle: 'padding:2px',
				buttonAlign: 'left',
				frame: true,
				id: 'person_edit_form',
				labelAlign: 'right',
				labelWidth: 125,
				url: C_PERSON_SAVE,
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{name: 'Person_id'},
					{name: 'Server_pid'},
					{name: 'polisCloseCause'},
					{name: 'Person_SurName'},
					{name: 'Person_SecName'},
					{name: 'Person_FirName'},
					{name: 'Person_BirthDay'},
					{name: 'PersonState_IsSnils'},
					{name: 'Person_SNILS'},
					{name: 'Person_IsUnknown'},
					{name: 'Person_IsAnonym'},
					{name: 'PersonSex_id'},
					{name: 'PersonSocCardNum_SocCardNum'},
					{name: 'PersonPhone_Phone'},
					{name: 'PersonPhone_VerifiedPhone'},
					//{name: 'PersonPhone_Comment'},
					{name: 'Person_Comment'},
					{name: 'PersonInfo_InternetPhone'},
					{name: 'FamilyStatus_id'},
					{name: 'PersonFamilyStatus_IsMarried'},
					{name: 'PersonInn_Inn'},
					//{name: 'PersonNationality_id'},
					{name: 'PersonRefuse_IsRefuse'},
					{name: 'Person_IsNotINN'},
					{name: 'PersonChildExist_IsChild'},
					{name: 'PersonCarExist_IsCar'},
					{name: 'SocStatus_id'},
					{name: 'OMSSprTerr_id'},
					{name: 'PolisType_id'},
					{name: 'Polis_Ser'},
					{name: 'Polis_Num'},
					{name: 'Federal_Num'},
					{name: 'OrgSMO_id'},
					{name: 'Polis_begDate'},
					{name: 'Polis_endDate'},
					{name: 'PolisFormType_id'},
					{name: 'Document_Ser'},
					{name: 'Document_Num'},
					{name: 'DocumentType_id'},
					{name: 'OrgDep_id'},
					{name: 'Document_begDate'},
					{name: 'KLCountry_id'},
					{name: 'NationalityStatus_IsTwoNation'},
					{name: 'LegalStatusVZN_id'},
					{name: 'Nation_id'},
					{name: 'DouType_id'},
					{name: 'StudyPlace_id'},
					{name: 'WorklessType_id'},
					{name: 'Person_Phone'},
					{name: 'UAddress_Zip'},
					{name: 'UKLCountry_id'},
					{name: 'UKLRGN_id'},
					{name: 'UKLSubRGN_id'},
					{name: 'UKLCity_id'},
					{name: 'UPersonSprTerrDop_id'},
					{name: 'UKLTown_id'},
					{name: 'UKLStreet_id'},
					{name: 'UAddress_House'},
					{name: 'UAddress_Corpus'},
					{name: 'UAddress_Flat'},
					{name: 'UAddressSpecObject_id'},
					{name: 'UAddressSpecObject_Value'},
					{name: 'UAddress_Address'},
					//{name: 'UAddress_begDate'},
					{name: 'UAddress_AddressText'},
					{name: 'BAddress_Zip'},
					{name: 'BKLCountry_id'},
					{name: 'BKLRGN_id'},
					{name: 'BKLSubRGN_id'},
					{name: 'BKLCity_id'},
					{name: 'BPersonSprTerrDop_id'},
					{name: 'BKLTown_id'},
					{name: 'BKLStreet_id'},
					{name: 'BAddress_House'},
					{name: 'BAddress_Corpus'},
					{name: 'BAddress_Flat'},
					{name: 'BAddressSpecObject_id'},
					{name: 'BAddressSpecObject_Value'},
					{name: 'BAddress_Address'},
					{name: 'BAddress_AddressText'},

					{name: 'PAddress_Zip'},
					{name: 'PKLCountry_id'},
					{name: 'PKLRGN_id'},
					{name: 'PKLSubRGN_id'},
					{name: 'PKLCity_id'},
					{name: 'PPersonSprTerrDop_id'},
					{name: 'PKLTown_id'},
					{name: 'PKLStreet_id'},
					{name: 'PAddress_House'},
					{name: 'PAddress_Corpus'},
					{name: 'PAddress_Flat'},
					{name: 'PAddressSpecObject_id'},
					{name: 'PAddressSpecObject_Value'},
					{name: 'PAddress_Address'},
					//{name: 'PAddress_begDate'},
					{name: 'PAddress_AddressText'},
					{name: 'Org_id'},
					{name: 'OrgUnion_id'},
					{name: 'Post_id'},
					{name: 'okved_id'},
					{name: 'Person_Parent'},
					{name: 'Servers_ids'},
					{name: 'DeputyKind_id'},
					{name: 'DeputyPerson_id'},
					{name: 'DeputyPerson_Fio'},
					{name: 'DocumentAuthority_id'},
					{name: 'DocumentDeputy_Ser'},
					{name: 'DocumentDeputy_Num'},
					{name: 'DocumentDeputy_Issue'},
					{name: 'DocumentDeputy_begDate'},
					{name: 'BDZ_Guid'},
					{name: 'Person_IsInErz'},
					{name: 'PersonIdentState_id'},
					{name: 'BDZ_id'},
					{name: 'Polis_Guid'},
					{name: 'Diag_id'},
					{name: 'FeedingType_id'},
					{name: 'PersonChild_CountChild'},
					{name: 'HealthAbnormVital_id'},
					{name: 'HealthAbnorm_id'},
					{name: 'HealthKind_id'},
					//{name: 'HeightAbnormType_id'},
					{name: 'InvalidKind_id'},
					//{name: 'PersonHeight_Height'},
					//{name: 'PersonWeight_Weight'},
					{name: 'Okei_id'},
					{name: 'PersonChild_id'},
					{name: 'PersonChild_IsBad'},
					{name: 'PersonChild_IsYoungMother'},
					//{name: 'PersonHeight_IsAbnorm'},
					{name: 'PersonChild_IsIncomplete'},
					{name: 'PersonChild_IsInvalid'},
					{name: 'PersonChild_IsManyChild'},
					{name: 'PersonChild_IsMigrant'},
					{name: 'PersonChild_IsTutor'},
					//{name: 'PersonWeight_IsAbnorm'},
					{name: 'PersonChild_invDate'},
					{name: 'PersonSprTerrDop_id'},
					{name: 'ResidPlace_id'},
					{name: 'Person_deadDT'},
					{name: 'Person_closeDT'},
					//{name: 'WeightAbnormType_id'},
					{name: 'Person_IsFedLgot'},
					{name: 'Polis_CanAdded'},

					{name: 'OnkoOccupationClass_id'},
					{name: 'Ethnos_id'},
					{name: 'PersonEmployment_id'},
					{name: 'Employment_id'},
					{name: 'PersonEduLevel_id'},
					{name: 'EducationLevel_id'}

				]),
				items: [{
					xtype: 'hidden',
					name: 'DeputyPerson_Fio'
				},{
					xtype: 'hidden',
					name: 'BDZ_Guid'
				},{
					xtype: 'hidden',
					name: 'Polis_Guid'
				},{
					xtype: 'hidden',
					name: 'Person_IsInErz'
				},{
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					xtype: 'hidden',
					name: 'Server_pid',
					id: 'server_id'
				},{
					xtype: 'hidden',
					name: 'polisCloseCause',
					id: 'polisCloseCause'
				},{
					xtype: 'hidden',
					name: 'action',
					value: 'save'
				},{
					xtype: 'hidden',
					name: 'Servers_ids',
					value: ''
				},{
					xtype: 'hidden',
					name: 'Person_identDT'
				},{
					xtype: 'hidden',
					name: 'PersonIdentState_id'
				},{
					xtype: 'hidden',
					name: 'BDZ_id'
				},{
					xtype: 'hidden',
					name: 'Person_IsFedLgot'
				},{
					xtype: 'hidden',
					name: 'Polis_CanAdded'
				},
				{
					xtype: 'hidden',
					name: 'PersonEmployment_id'
				},
				{
					xtype: 'hidden',
					name: 'PersonEduLevel_id'
				},{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 70,
						items: [{
							allowBlank: false,
							fieldLabel: lang['familiya'],
							id: 'PEW_Person_SurName',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										this.findById('person_edit_form').getForm().findField("Person_FirName").focus();
									}
								}.createDelegate(this),
								blur: function (inp) {
									inp.setValue(inp.getValue().trim());
								}
							},
							name: 'Person_SurName',
							tabIndex: TABINDEX_PEF + 0,
							toUpperCase: true,
//							validateOnBlur: false,
//							validationEvent: false,
							maskRe: (getRegionNick()== 'kz')?null:/[a-zA-Zа-яА-ЯёЁ\—\–\-\s\,\[\]\;\'\`]/,
							width: 180,
							xtype: (getRegionNick() == 'kz')?'textfield':'swtranslatedtextfieldwithapostrophe'
						}, {
							allowBlank: true,
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': function (inp, e) {
									if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
										e.stopEvent();
										this.findById('person_edit_form').getForm().findField("Person_SurName").focus();
									}
								}.createDelegate(this),
								blur: function (inp) {
									inp.setValue(inp.getValue().trim());
								}
							},
							name: 'Person_FirName',
							tabIndex: TABINDEX_PEF + 1,
							toUpperCase: true,
//							validateOnBlur: false,
//							validationEvent: false,
							maskRe: (getRegionNick() == 'kz')?null:/[a-zA-Zа-яА-ЯёЁ\—\–\-\s\,\[\]\;\'\`]/,
							width: 180,
							xtype: (getRegionNick() == 'kz')?'textfield':'swtranslatedtextfieldwithapostrophe'
						}, {
							xtype: (getRegionNick() == 'kz')?'textfield':'swtranslatedtextfieldwithapostrophe',
							fieldLabel: lang['otchestvo'],
							listeners: {
								blur: function (inp) {
									inp.setValue(inp.getValue().trim());
								}
							},
							toUpperCase: true,
							maskRe: (getRegionNick() == 'kz')?null:/[a-zA-Zа-яА-ЯёЁ\—\–\-\s\,\[\]\;\'\`\.]/,
							width: 180,
							name: 'Person_SecName',
							tabIndex: TABINDEX_PEF + 2
						}]
					}, {
						layout: 'form',
						labelWidth: 130,
						items: [{
							allowBlank: false,
							fieldLabel: lang['data_rojdeniya'],
							format: 'd.m.Y',
							maxValue: getGlobalOptions().date,
							minValue: getMinBirthDate(),
							name: 'Person_BirthDay',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_PEF + 3,
//							validateOnBlur: false,
//							validationEvent: false,
							width: 95,
							xtype: 'swdatefield'
						}, /*{
							allowBlank: true,
							fieldLabel: lang['telefon']+'  +7',
							id: 'PEW_PersonPhone_Phone',
							name: 'PersonPhone_Phone',
							maxLength:(getRegionNick() != 'ekb')?undefined:11,
							tabIndex: TABINDEX_PEF + 5,
							//maskRe: (getRegionNick() != 'ekb')?null: new RegExp('[0-9]'),
							plugins: [ new Ext.ux.InputTextMask('(999)999-99-99', false) ],
							width: 180,
							xtype: 'textfield'
						}, */
						{
							layout: 'column',
							border: false,
							items: [{
								xtype: 'hidden',
								name: 'PersonPhone_VerifiedPhone'
							}, {
								layout: 'form',
								border: false,
								items: [{
									fieldLabel: langs('Телефон')+'  +7',
									id: 'PEW_PersonPhone_Phone',
									name: 'PersonPhone_Phone',
									tabIndex: TABINDEX_PEF + 5,
									fieldWidth: 120,
									xtype: 'swphonefield',
									onChange: function() {
										this.refreshPersonPhoneVerificationButton();
									}.createDelegate(this)
								}]
							}, {
								layout: 'form',
								border: false,
								style: 'margin-left: 5px;',
								items: [{
									id: 'PEW_PersonPhoneVerifiedButton',
									xtype: 'button',
									iconCls: 'ok16',
									tooltip: 'Подтвержден',
									disabled: true
								}, {
									id: 'PEW_PersonPhoneVerifyButton',
									xtype: 'button',
									iconCls: 'info16',
									tooltip: 'Подтвердить',
									handler: function() {
										this.verifyPersonPhone();
									}.createDelegate(this)
								}]
							}]
						},
						{
							fieldLabel: lang['tel_s_sayta_zapisi'],
							id: 'PEW_PersonInfo_InternetPhone',
							name: 'PersonInfo_InternetPhone',
							width: 150,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 100,
						items: [{
							allowBlank: false,
							comboSubject: 'Sex',
							fieldLabel: lang['pol'],
							hiddenName: 'PersonSex_id',
							lastQuery: '',
							tabIndex: TABINDEX_PEF + 4,
							width: 120,
							xtype: 'swcommonsprcombo'
						},
						{
							fieldLabel: 'Комментарий',
							//id: 'PEW_PersonPhone_Comment',
							//name: 'PersonPhone_Comment',
							id: 'PEW_Person_Comment',
							name: 'Person_Comment',
							width: 120,
							xtype: 'textfield'/*,
							listeners:
							{
								'change': function(field,newValue,oldValue){
									if(newValue=='')
										this.findById('person_edit_form').getForm().findField("PersonPhone_Phone").setAllowBlank(true);
									else
										this.findById('person_edit_form').getForm().findField("PersonPhone_Phone").setAllowBlank(false);
								}.createDelegate(this)
							}*/
						}]
					}, {
						layout: 'form',
						labelWidth: 20,
						items: [{
							labelSeparator: '',
							boxLabel: lang['lichnost_neizvestna'],
							name: 'Person_IsUnknown',
							tabIndex: TABINDEX_PEF + 4,
							xtype: 'checkbox',
							listeners: {
								'check': function(field, value) {
									var base_form = this.findById('person_edit_form').getForm();

									base_form.findField('Person_BirthDay').setAllowBlank(value);
									base_form.findField('PersonSex_id').setAllowBlank(value);
									base_form.findField('SocStatus_id').setAllowBlank(value);
									base_form.findField('Document_Ser').setAllowBlank(value);
									base_form.findField('Document_Num').setAllowBlank(value);

									var surname_field = base_form.findField('Person_SurName');
									if (value && Ext.isEmpty(surname_field.getValue())) {
										surname_field.setValue(lang['neizvesten']);
									}

									if (getRegionNick() != 'kz') {
										if (value) {
											base_form.findField('Person_FirName').maskRe = /[a-zA-Zа-яА-ЯёЁ\—\–\-\s\,\[\]\;\']/;
										} else {
											base_form.findField('Person_FirName').maskRe = /[a-zA-Zа-яА-ЯёЁ\—\–\-\s\,\[\]\;\']/;
										}
									}
								}.createDelegate(this)
							}
						}, {
							labelSeparator: '',
							boxLabel: lang['anonim'],
							id: 'PEW_Person_IsAnonym',
							name: 'Person_IsAnonym',
							tabIndex: TABINDEX_PEF + 4,
							xtype: 'checkbox',
							listeners: {
								'check': function(field, value) {
									var base_form = this.findById('person_edit_form').getForm();
									var isAnonym = false;
									var regex = /^[0-9]{9}$/;
									if (getRegionNick().inlist(['ekb', 'vologda', 'buryatiya'])) {
										if (this.action === 'add') {
											regex = /^[0-9]{11}$/;// #140580 новый формат ККККГГННННH для Свердловской области
										} else {
											regex = /^[0-9]{9}$|^[0-9]{11}$/;// 9 символов для поддержки старого формата в режиме правки
										}
									}

									if (value && regex.test(base_form.findField('Person_SurName').getValue())) {
										isAnonym = true;
									}

									if (value) {
										base_form.findField('Person_FirName').disable();
										base_form.findField('Person_SecName').disable();

										base_form.findField('KLCountry_id').setAllowBlank(true);

										if (!isAnonym) {
											base_form.findField('Person_SurName').setValue('');
											base_form.findField('Person_FirName').setValue('');
											base_form.findField('Person_SecName').setValue('');

											this.getAnonymCode({callback: function(result) {
												if (result && result.Person_AnonymCode) {
													base_form.findField('Person_SurName').setValue(result.Person_AnonymCode);
												}
											}});
										}
									} else {
										base_form.findField('Person_FirName').enable();
										base_form.findField('Person_SecName').enable();

										if (Ext.isEmpty(base_form.findField('DocumentType_id').getValue()) || base_form.findField('DocumentType_id').getFieldValue('DocumentType_Code') != 22) {
											base_form.findField('KLCountry_id').setAllowBlank(false);
										}

										if (isAnonym) {
											this.getAnonymCode({mode: 'data', callback: function(result) {
												if (result && result.PersonAnonymData) {
													base_form.findField('Person_SurName').setValue(result.PersonAnonymData.Person_SurName);
													base_form.findField('Person_FirName').setValue(result.PersonAnonymData.Person_FirName);
													base_form.findField('Person_SecName').setValue(result.PersonAnonymData.Person_SecName);
												}
											}});
										}
									}

									if (value) {
										base_form.findField('Person_SurName').maskRe = /[0-9]/;
										base_form.findField('Person_SurName').regex = regex;
									} else {
										base_form.findField('Person_SurName').maskRe = (getRegionNick() == 'kz')?null:/[a-zA-Zа-яА-ЯёЁ\—\–\-\s\,\[\]\;\'\`]/;
										base_form.findField('Person_SurName').regex = null;
									}

									win.checkAdreesAdd();
								}.createDelegate(this)
							}
						}]
					}]
				},
				new Ext.TabPanel({
					activeTab: 0,
					id: 'pacient_tab_panel',
					layoutOnTabChange: true,
					plain: true,
					//autoScroll:true,
					defaults: {bodyStyle: 'padding:2px'},
					items: [{
						height: Ext.isIE ? 450 : 430,
						autoScroll:true,
						id: 'pacient_tab',
						layout:'form',
						title: lang['1_patsient'],
						items: [{
							border: false,
							layout: 'column',
							style: 'padding: 0; padding-top: 5px; margin: 0',
							items: [{
								layout: 'form',
								items: [{
									layout: 'column',
									border: false,
									items: [{
										xtype: 'hidden',
										name: 'PersonState_IsSnils'
									}, {
										layout: 'form',
										border: false,
										items: [{
											fieldLabel: langs('СНИЛС'),
											hidden: getRegionNick() == 'kz',
											name: 'Person_SNILS',
											tabIndex: TABINDEX_PEF + 6,
											fieldWidth: 150,
											xtype: 'swsnilsfield',
											onChange: function() {
												this.refreshPersonSnilsVerificationButton();
											}.createDelegate(this)
										}]
									}, {
										layout: 'form',
										border: false,
										style: 'margin-left: 5px;',
										items: [{
											id: 'PEW_PersonSnilsVerifiedButton',
											xtype: 'button',
											iconCls: 'ok16',
											tooltip: 'Подтвержден',
											disabled: true
										}, {
											id: 'PEW_PersonSnilsVerifyButton',
											xtype: 'button',
											iconCls: 'info16',
											handler: function() {
												this.verifyPersonSnils();
											}.createDelegate(this)
										}]
									}]
								}]
							}, {
								layout: 'form',
								labelWidth: (getRegionNick() == 'kz'?125:100),
								items: [{
									allowBlank: false,
									autoLoad: false,
									codeField: 'SocStatus_Code',
									comboSubject: 'SocStatus',
									allowSysNick:true,
									editable: true,
									lastQuery: '',
									fieldLabel: lang['sots_status'],
									moreFields: [
										{name: 'SocStatus_begDT', mapping: 'SocStatus_begDT'},
										{name: 'SocStatus_endDT', mapping: 'SocStatus_endDT'}
									],
									onLoadStore: function(store) {
										store.filterBy(function(rec) {
											return (
												Ext.isEmpty(rec.get('SocStatus_endDT'))
												|| (typeof rec.get('SocStatus_endDT') == 'object' ? rec.get('SocStatus_endDT') : getValidDT(rec.get('SocStatus_endDT'), '')) >= getValidDT(getGlobalOptions().date, '')
											);
										});

										this.baseFilterFn = function(rec) {
											return (
												Ext.isEmpty(rec.get('SocStatus_endDT'))
												|| (typeof rec.get('SocStatus_endDT') == 'object' ? rec.get('SocStatus_endDT') : getValidDT(rec.get('SocStatus_endDT'), '')) >= getValidDT(getGlobalOptions().date, '')
											);
										};
									},
									tabIndex: TABINDEX_PEF + 7,
									width: 327,
									xtype: 'swcommonsprcombo'
								}]
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: lang['adres'],
							xtype: 'fieldset',
							items: [{
								xtype: 'hidden',
								name: 'UAddress_Zip',
								id: 'PEW_UAddress_Zip'
							}, {
								xtype: 'hidden',
								name: 'UKLCountry_id',
								id: 'PEW_UKLCountry_id'
							}, {
								xtype: 'hidden',
								name: 'UKLRGN_id',
								id: 'PEW_UKLRGN_id'
							}, {
								xtype: 'hidden',
								name: 'UKLRGNSocr_id',
								id: 'PEW_UKLRGNSocr_id'
							}, {
								xtype: 'hidden',
								name: 'UKLSubRGN_id',
								id: 'PEW_UKLSubRGN_id'
							}, {
								xtype: 'hidden',
								name: 'UKLSubRGNSocr_id',
								id: 'PEW_UKLSubRGNSocr_id'
							}, {
								xtype: 'hidden',
								name: 'UKLCity_id',
								id: 'PEW_UKLCity_id'
							}, {
								xtype: 'hidden',
								name: 'UKLCitySocr_id',
								id: 'PEW_UKLCitySocr_id'
							}, {
								xtype: 'hidden',
								name: 'UPersonSprTerrDop_id',
								id: 'PEW_UPersonSprTerrDop_id'
							}, {
								xtype: 'hidden',
								name: 'UKLTown_id',
								id: 'PEW_UKLTown_id'
							}, {
								xtype: 'hidden',
								name: 'UKLTownSocr_id',
								id: 'PEW_UKLTownSocr_id'
							}, {
								xtype: 'hidden',
								name: 'UKLStreet_id',
								id: 'PEW_UKLStreet_id'
							}, {
								xtype: 'hidden',
								name: 'UKLStreetSocr_id',
								id: 'PEW_UKLStreetSocr_id'
							},  {
								xtype: 'hidden',
								name: 'UAddress_House',
								id: 'PEW_UAddress_House'
							}, {
								xtype: 'hidden',
								name: 'UAddress_Corpus',
								id: 'PEW_UAddress_Corpus'
							}, {
								xtype: 'hidden',
								name: 'UAddress_Flat',
								id: 'PEW_UAddress_Flat'
							},{
								xtype: 'hidden',
								name: 'UAddressSpecObject_id',
								id: 'PEW_UAddressSpecObject_id'
							},{
								xtype: 'hidden',
								name: 'UAddressSpecObject_Value',
								id: 'PEW_UAddressSpecObject_Value'
							},{
								xtype: 'hidden',
								name: 'UAddress_Address',
								id: 'PEW_UAddress_Address'
							}, {
								layout: 'column',
								items: [{
										layout: 'form',
										items: [
										new sw.Promed.TripleTriggerField ({
											enableKeyEvents: true,
											fieldLabel: lang['adres_registratsii'],
											id: 'PEW_UAddress_AddressText',
											name: 'UAddress_AddressText',
											readOnly: true,
											tabIndex: TABINDEX_PEF + 8,
											trigger1Class: 'x-form-search-trigger',
											trigger2Class: 'x-form-equil-trigger',
											trigger3Class: 'x-form-clear-trigger',
											width: 610,

											listeners: {
												'keydown': function(inp, e) {
													if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
														if ( e.F4 == e.getKey() )
															inp.onTrigger1Click();

														if ( e.F2 == e.getKey() )
															inp.onTrigger2Click();

														if ( e.DELETE == e.getKey() && e.altKey)
															inp.onTrigger3Click();

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

														if ( Ext.isIE ) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}

														return false;
													}
												},
												'keyup': function( inp, e ) {
													if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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

														if ( Ext.isIE ) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}

														return false;
													}
												}
											},
											onTrigger3Click: function() {
												var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
												if (!ownerForm.findById('PEW_UAddress_AddressText').disabled)
												{
													ownerForm.findById('PEW_UAddress_Zip').setValue('');
													ownerForm.findById('PEW_UKLCountry_id').setValue('');
													ownerForm.findById('PEW_UKLRGN_id').setValue('');
													ownerForm.findById('PEW_UKLRGNSocr_id').setValue('');
													ownerForm.findById('PEW_UKLSubRGN_id').setValue('');
													ownerForm.findById('PEW_UKLSubRGNSocr_id').setValue('');
													ownerForm.findById('PEW_UKLCity_id').setValue('');
													ownerForm.findById('PEW_UKLCitySocr_id').setValue('');
													ownerForm.findById('PEW_UPersonSprTerrDop_id').setValue('');
													ownerForm.findById('PEW_UKLTown_id').setValue('');
													ownerForm.findById('PEW_UKLTownSocr_id').setValue('');
													ownerForm.findById('PEW_UKLStreet_id').setValue('');
													ownerForm.findById('PEW_UKLStreetSocr_id').setValue('');
													ownerForm.findById('PEW_UAddress_House').setValue('');
													ownerForm.findById('PEW_UAddress_Corpus').setValue('');
													ownerForm.findById('PEW_UAddress_Flat').setValue('');
													ownerForm.findById('PEW_UAddress_Address').setValue('');
													ownerForm.findById('PEW_UAddress_AddressText').setValue('');
													ownerForm.findById('PEW_UAddressSpecObject_id').setValue('');
													ownerForm.findById('PEW_UAddressSpecObject_Value').setValue('');
													win.checkAdreesAdd();
												}
											},
											onTrigger2Click: function() {
												var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
												if (!ownerForm.findById('PEW_UAddress_AddressText').disabled)
												{
													ownerForm.findById('PEW_UAddress_Zip').setValue(ownerForm.findById('PEW_PAddress_Zip').getValue());
													ownerForm.findById('PEW_UKLCountry_id').setValue(ownerForm.findById('PEW_PKLCountry_id').getValue());
													ownerForm.findById('PEW_UKLRGN_id').setValue(ownerForm.findById('PEW_PKLRGN_id').getValue());
													ownerForm.findById('PEW_UKLRGNSocr_id').setValue(ownerForm.findById('PEW_PKLRGNSocr_id').getValue());
													ownerForm.findById('PEW_UKLSubRGN_id').setValue(ownerForm.findById('PEW_PKLSubRGN_id').getValue());
													ownerForm.findById('PEW_UKLSubRGNSocr_id').setValue(ownerForm.findById('PEW_PKLSubRGNSocr_id').getValue());
													ownerForm.findById('PEW_UKLCity_id').setValue(ownerForm.findById('PEW_PKLCity_id').getValue());
													ownerForm.findById('PEW_UKLCitySocr_id').setValue(ownerForm.findById('PEW_PKLCitySocr_id').getValue());
													ownerForm.findById('PEW_UPersonSprTerrDop_id').setValue(ownerForm.findById('PEW_PPersonSprTerrDop_id').getValue());
													ownerForm.findById('PEW_UKLTown_id').setValue(ownerForm.findById('PEW_PKLTown_id').getValue());
													ownerForm.findById('PEW_UKLTownSocr_id').setValue(ownerForm.findById('PEW_PKLTownSocr_id').getValue());
													ownerForm.findById('PEW_UKLStreet_id').setValue(ownerForm.findById('PEW_PKLStreet_id').getValue());
													ownerForm.findById('PEW_UKLStreetSocr_id').setValue(ownerForm.findById('PEW_PKLStreetSocr_id').getValue());
													ownerForm.findById('PEW_UAddress_House').setValue(ownerForm.findById('PEW_PAddress_House').getValue());
													ownerForm.findById('PEW_UAddress_Corpus').setValue(ownerForm.findById('PEW_PAddress_Corpus').getValue());
													ownerForm.findById('PEW_UAddress_Flat').setValue(ownerForm.findById('PEW_PAddress_Flat').getValue());
													ownerForm.findById('PEW_UAddressSpecObject_id').setValue(ownerForm.findById('PEW_PAddressSpecObject_id').getValue());
													ownerForm.findById('PEW_UAddressSpecObject_Value').setValue(ownerForm.findById('PEW_PAddressSpecObject_Value').getValue());
													ownerForm.findById('PEW_UAddress_Address').setValue(ownerForm.findById('PEW_PAddress_Address').getValue());
													ownerForm.findById('PEW_UAddress_AddressText').setValue(ownerForm.findById('PEW_PAddress_AddressText').getValue());
												}
											},
											onTrigger1Click: function() {
												var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
												var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
												if (!ownerForm.findById('PEW_UAddress_AddressText').disabled)
												{
													getWnd('swAddressEditWindow').show({
														fields: {
															Address_ZipEdit: ownerForm.findById('PEW_UAddress_Zip').getValue(),
															KLCountry_idEdit: ownerForm.findById('PEW_UKLCountry_id').getValue(),
															KLRgn_idEdit: ownerForm.findById('PEW_UKLRGN_id').getValue(),
															KLSubRGN_idEdit: ownerForm.findById('PEW_UKLSubRGN_id').getValue(),
															KLCity_idEdit: ownerForm.findById('PEW_UKLCity_id').getValue(),
															PersonSprTerrDop_idEdit: ownerForm.findById('PEW_UPersonSprTerrDop_id').getValue(),
															KLTown_idEdit: ownerForm.findById('PEW_UKLTown_id').getValue(),
															KLStreet_idEdit: ownerForm.findById('PEW_UKLStreet_id').getValue(),
															Address_HouseEdit: ownerForm.findById('PEW_UAddress_House').getValue(),
															Address_CorpusEdit: ownerForm.findById('PEW_UAddress_Corpus').getValue(),
															Address_FlatEdit: ownerForm.findById('PEW_UAddress_Flat').getValue(),
															Address_AddressEdit: ownerForm.findById('PEW_UAddress_Address').getValue(),
															//Address_begDateEdit: ownerForm.findById('PEW_UAddress_begDate').getValue(),
															AddressSpecObject_idEdit:ownerForm.findById('PEW_UAddressSpecObject_Value').getValue(),
															AddressSpecObject_id:ownerForm.findById('PEW_UAddressSpecObject_id').getValue(),
															allowBlankStreet:(Ext.getCmp('PEW_Person_IsAnonym').checked),
															allowBlankHouse:(Ext.getCmp('PEW_Person_IsAnonym').checked),
															addressType: 0,
															showDate: true
														},
														callback: function(values) {
															ownerForm.findById('PEW_UAddress_Zip').setValue(values.Address_ZipEdit);
															ownerForm.findById('PEW_UKLCountry_id').setValue(values.KLCountry_idEdit);
															ownerForm.findById('PEW_UKLRGN_id').setValue(values.KLRgn_idEdit);
															ownerForm.findById('PEW_UKLRGNSocr_id').setValue(values.KLRGN_Socr);
															ownerForm.findById('PEW_UKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
															ownerForm.findById('PEW_UKLSubRGNSocr_id').setValue(values.KLSubRGN_Socr);
															ownerForm.findById('PEW_UKLCity_id').setValue(values.KLCity_idEdit);
															ownerForm.findById('PEW_UKLCitySocr_id').setValue(values.KLCity_Socr);
															ownerForm.findById('PEW_UPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
															ownerForm.findById('PEW_UKLTown_id').setValue(values.KLTown_idEdit);
															ownerForm.findById('PEW_UKLTownSocr_id').setValue(values.KLTown_Socr);
															ownerForm.findById('PEW_UKLStreet_id').setValue(values.KLStreet_idEdit);
															ownerForm.findById('PEW_UKLStreetSocr_id').setValue(values.KLStreet_Socr);
															ownerForm.findById('PEW_UAddress_House').setValue(values.Address_HouseEdit);
															ownerForm.findById('PEW_UAddress_Corpus').setValue(values.Address_CorpusEdit);
															ownerForm.findById('PEW_UAddress_Flat').setValue(values.Address_FlatEdit);
															ownerForm.findById('PEW_UAddressSpecObject_id').setValue(values.AddressSpecObject_id);
															ownerForm.findById('PEW_UAddressSpecObject_Value').setValue(values.AddressSpecObject_idEdit);
															ownerForm.findById('PEW_UAddress_Address').setValue(values.Address_AddressEdit);
															ownerForm.findById('PEW_UAddress_AddressText').setValue(values.Address_AddressEdit);
															//ownerForm.findById('PEW_UAddress_begDate').setValue(Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y'));
															ownerForm.findById('PEW_UAddress_AddressText').focus(true, 500);
															win.checkAdreesAdd();
														},
														onClose: function() {
															ownerForm.findById('PEW_UAddress_AddressText').focus(true, 500);
														}
													})
												}
											}
										})]
									}]
							},/*{
							name: 'UAddress_begDate',
							xtype: 'hidden',
							id: 'PEW_UAddress_begDate'
							},*/{
								xtype: 'hidden',
								name: 'PAddress_Zip',
								id: 'PEW_PAddress_Zip'
							}, {
								xtype: 'hidden',
								name: 'PKLCountry_id',
								id: 'PEW_PKLCountry_id'
							}, {
								xtype: 'hidden',
								name: 'PKLRGN_id',
								id: 'PEW_PKLRGN_id'
							}, {
								xtype: 'hidden',
								name: 'PKLRGNSocr_id',
								id: 'PEW_PKLRGNSocr_id'
							}, {
								xtype: 'hidden',
								name: 'PKLSubRGN_id',
								id: 'PEW_PKLSubRGN_id'
							}, {
								xtype: 'hidden',
								name: 'PKLSubRGNSocr_id',
								id: 'PEW_PKLSubRGNSocr_id'
							}, {
								xtype: 'hidden',
								name: 'PKLCity_id',
								id: 'PEW_PKLCity_id'
							}, {
								xtype: 'hidden',
								name: 'PKLCitySocr_id',
								id: 'PEW_PKLCitySocr_id'
							}, {
								xtype: 'hidden',
								name: 'PPersonSprTerrDop_id',
								id: 'PEW_PPersonSprTerrDop_id'
							}, {
								xtype: 'hidden',
								name: 'PKLTown_id',
								id: 'PEW_PKLTown_id'
							}, {
								xtype: 'hidden',
								name: 'PKLTownSocr_id',
								id: 'PEW_PKLTownSocr_id'
							}, {
								xtype: 'hidden',
								name: 'PKLStreet_id',
								id: 'PEW_PKLStreet_id'
							}, {
								xtype: 'hidden',
								name: 'PKLStreetSocr_id',
								id: 'PEW_PKLStreetSocr_id'
							}, {
								xtype: 'hidden',
								name: 'PAddress_House',
								id: 'PEW_PAddress_House'
							}, {
								xtype: 'hidden',
								name: 'PAddress_Corpus',
								id: 'PEW_PAddress_Corpus'
							}, {
								xtype: 'hidden',
								name: 'PAddress_Flat',
								id: 'PEW_PAddress_Flat'
							},{
								xtype: 'hidden',
								name: 'PAddressSpecObject_id',
								id: 'PEW_PAddressSpecObject_id'
							},{
								xtype: 'hidden',
								name: 'PAddressSpecObject_Value',
								id: 'PEW_PAddressSpecObject_Value'
							}, {
								xtype: 'hidden',
								name: 'PAddress_Address',
								id: 'PEW_PAddress_Address'
							}, {
								xtype: 'hidden',
								name: 'PersonChild_id'
							}, {
								layout: 'column',
								items: [{
										layout: 'form',
										items: [
											new sw.Promed.TripleTriggerField ({
												enableKeyEvents: true,
												fieldLabel: lang['adres_projivaniya'],
												id: 'PEW_PAddress_AddressText',
												name: 'PAddress_AddressText',
												readOnly: true,
												tabIndex: TABINDEX_PEF + 9,
												trigger1Class: 'x-form-search-trigger',
												trigger2Class: 'x-form-equil-trigger',
												trigger3Class: 'x-form-clear-trigger',
												width: 610,

												listeners: {
													'keydown': function(inp, e) {
														if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
															if ( e.F4 == e.getKey() )
																inp.onTrigger1Click();

															if ( e.F2 == e.getKey() )
																inp.onTrigger2Click();

															if ( e.DELETE == e.getKey() && e.altKey)
																inp.onTrigger3Click();

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

															if ( Ext.isIE ) {
																e.browserEvent.keyCode = 0;
																e.browserEvent.which = 0;
															}

															return false;
														}
													},
													'keyup': function( inp, e ) {
														if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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

															if ( Ext.isIE ) {
																e.browserEvent.keyCode = 0;
																e.browserEvent.which = 0;
															}

															return false;
														}
													}
												},
												onTrigger3Click: function() {
													var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
													ownerForm.findById('PEW_PAddress_Zip').setValue('');
													ownerForm.findById('PEW_PKLCountry_id').setValue('');
													ownerForm.findById('PEW_PKLRGN_id').setValue('');
													ownerForm.findById('PEW_PKLRGNSocr_id').setValue('');
													ownerForm.findById('PEW_PKLSubRGN_id').setValue('');
													ownerForm.findById('PEW_PKLSubRGNSocr_id').setValue('');
													ownerForm.findById('PEW_PKLCity_id').setValue('');
													ownerForm.findById('PEW_PKLCitySocr_id').setValue('');
													ownerForm.findById('PEW_PPersonSprTerrDop_id').setValue('');
													ownerForm.findById('PEW_PKLTown_id').setValue('');
													ownerForm.findById('PEW_PKLTownSocr_id').setValue('');
													ownerForm.findById('PEW_PKLStreet_id').setValue('');
													ownerForm.findById('PEW_PKLStreetSocr_id').setValue('');
													ownerForm.findById('PEW_PAddress_House').setValue('');
													ownerForm.findById('PEW_PAddress_Corpus').setValue('');
													ownerForm.findById('PEW_PAddress_Flat').setValue('');
													ownerForm.findById('PEW_PAddress_Address').setValue('');
													ownerForm.findById('PEW_PAddress_AddressText').setValue('');

													ownerForm.findById('PEW_PAddressSpecObject_id').setValue('');
													ownerForm.findById('PEW_PAddressSpecObject_Value').setValue('');
													win.checkAdreesAdd();
												},
												onTrigger2Click: function() {
													var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
													ownerForm.findById('PEW_PAddress_Zip').setValue(ownerForm.findById('PEW_UAddress_Zip').getValue());
													ownerForm.findById('PEW_PKLCountry_id').setValue(ownerForm.findById('PEW_UKLCountry_id').getValue());
													ownerForm.findById('PEW_PKLRGN_id').setValue(ownerForm.findById('PEW_UKLRGN_id').getValue());
													ownerForm.findById('PEW_PKLRGNSocr_id').setValue(ownerForm.findById('PEW_UKLRGNSocr_id').getValue());
													ownerForm.findById('PEW_PKLSubRGN_id').setValue(ownerForm.findById('PEW_UKLSubRGN_id').getValue());
													ownerForm.findById('PEW_PKLSubRGNSocr_id').setValue(ownerForm.findById('PEW_UKLSubRGNSocr_id').getValue());
													ownerForm.findById('PEW_PKLCity_id').setValue(ownerForm.findById('PEW_UKLCity_id').getValue());
													ownerForm.findById('PEW_PKLCitySocr_id').setValue(ownerForm.findById('PEW_UKLCitySocr_id').getValue());
													ownerForm.findById('PEW_PPersonSprTerrDop_id').setValue(ownerForm.findById('PEW_UPersonSprTerrDop_id').getValue());
													ownerForm.findById('PEW_PKLTown_id').setValue(ownerForm.findById('PEW_UKLTown_id').getValue());
													ownerForm.findById('PEW_PKLTownSocr_id').setValue(ownerForm.findById('PEW_UKLTownSocr_id').getValue());
													ownerForm.findById('PEW_PKLStreet_id').setValue(ownerForm.findById('PEW_UKLStreet_id').getValue());
													ownerForm.findById('PEW_PKLStreetSocr_id').setValue(ownerForm.findById('PEW_UKLStreetSocr_id').getValue());
													ownerForm.findById('PEW_PAddress_House').setValue(ownerForm.findById('PEW_UAddress_House').getValue());
													ownerForm.findById('PEW_PAddress_Corpus').setValue(ownerForm.findById('PEW_UAddress_Corpus').getValue());
													ownerForm.findById('PEW_PAddress_Flat').setValue(ownerForm.findById('PEW_UAddress_Flat').getValue());
													ownerForm.findById('PEW_PAddressSpecObject_id').setValue(ownerForm.findById('PEW_UAddressSpecObject_id').getValue());
													ownerForm.findById('PEW_PAddressSpecObject_Value').setValue(ownerForm.findById('PEW_UAddressSpecObject_Value').getValue());
													if (!Ext.isEmpty(ownerForm.findById('PEW_UAddress_Address').getValue())) {
														ownerForm.findById('PEW_PAddress_Address').setValue(ownerForm.findById('PEW_UAddress_Address').getValue());
													} else {
														ownerForm.findById('PEW_PAddress_Address').setValue(ownerForm.findById('PEW_UAddress_AddressText').getValue());
													}
													ownerForm.findById('PEW_PAddress_AddressText').setValue(ownerForm.findById('PEW_UAddress_AddressText').getValue());
												},
												onTrigger1Click: function() {
													var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
													var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
													getWnd('swAddressEditWindow').show({
														fields: {
															Address_ZipEdit: ownerForm.findById('PEW_PAddress_Zip').getValue(),
															KLCountry_idEdit: ownerForm.findById('PEW_PKLCountry_id').getValue(),
															KLRgn_idEdit: ownerForm.findById('PEW_PKLRGN_id').getValue(),
															KLSubRGN_idEdit: ownerForm.findById('PEW_PKLSubRGN_id').getValue(),
															KLCity_idEdit: ownerForm.findById('PEW_PKLCity_id').getValue(),
															PersonSprTerrDop_idEdit: ownerForm.findById('PEW_PPersonSprTerrDop_id').getValue(),
															KLTown_idEdit: ownerForm.findById('PEW_PKLTown_id').getValue(),
															KLStreet_idEdit: ownerForm.findById('PEW_PKLStreet_id').getValue(),
															Address_HouseEdit: ownerForm.findById('PEW_PAddress_House').getValue(),
															Address_CorpusEdit: ownerForm.findById('PEW_PAddress_Corpus').getValue(),
															Address_FlatEdit: ownerForm.findById('PEW_PAddress_Flat').getValue(),
															Address_AddressEdit: ownerForm.findById('PEW_PAddress_Address').getValue(),
															//Address_begDateEdit: ownerForm.findById('PEW_PAddress_begDate').getValue(),
															AddressSpecObject_idEdit:ownerForm.findById('PEW_PAddressSpecObject_Value').getValue(),
															AddressSpecObject_id:ownerForm.findById('PEW_PAddressSpecObject_id').getValue(),
															allowBlankStreet:(Ext.getCmp('PEW_Person_IsAnonym').checked),
															allowBlankHouse:(Ext.getCmp('PEW_Person_IsAnonym').checked),
															addressType: 0,
															showDate: true
														},
														callback: function(values) {
															ownerForm.findById('PEW_PAddress_Zip').setValue(values.Address_ZipEdit);
															ownerForm.findById('PEW_PKLCountry_id').setValue(values.KLCountry_idEdit);
															ownerForm.findById('PEW_PKLRGN_id').setValue(values.KLRgn_idEdit);
															ownerForm.findById('PEW_PKLRGNSocr_id').setValue(values.KLRGN_Socr);
															ownerForm.findById('PEW_PKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
															ownerForm.findById('PEW_PKLSubRGNSocr_id').setValue(values.KLSubRGN_Socr);
															ownerForm.findById('PEW_PKLCity_id').setValue(values.KLCity_idEdit);
															ownerForm.findById('PEW_PKLCitySocr_id').setValue(values.KLCity_Socr);
															ownerForm.findById('PEW_PPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
															ownerForm.findById('PEW_PKLTown_id').setValue(values.KLTown_idEdit);
															ownerForm.findById('PEW_PKLTownSocr_id').setValue(values.KLTown_Socr);
															ownerForm.findById('PEW_PKLStreet_id').setValue(values.KLStreet_idEdit);
															ownerForm.findById('PEW_PKLStreetSocr_id').setValue(values.KLStreet_Socr);
															ownerForm.findById('PEW_PAddress_House').setValue(values.Address_HouseEdit);
															ownerForm.findById('PEW_PAddress_Corpus').setValue(values.Address_CorpusEdit);
															ownerForm.findById('PEW_PAddress_Flat').setValue(values.Address_FlatEdit);
															ownerForm.findById('PEW_PAddressSpecObject_id').setValue(values.AddressSpecObject_id);
															ownerForm.findById('PEW_PAddressSpecObject_Value').setValue(values.AddressSpecObject_idEdit);
															ownerForm.findById('PEW_PAddress_Address').setValue(values.Address_AddressEdit);
															ownerForm.findById('PEW_PAddress_AddressText').setValue(values.Address_AddressEdit);
															//ownerForm.findById('PEW_PAddress_begDate').setValue(Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y'));
															ownerForm.findById('PEW_PAddress_AddressText').focus(true, 500);
															win.checkAdreesAdd();
														},
														onClose: function() {
															ownerForm.findById('PEW_PAddress_AddressText').focus(true, 500);
														}
													})
												}
											})]
										}]
							},

							//TODO: Место рождения
							/*{
							name: 'PAddress_begDate',
							xtype: 'hidden',
							id: 'PEW_PAddress_begDate'
							},*/{
								xtype: 'hidden',
								name: 'BAddress_Zip',
								id: 'PEW_BAddress_Zip'
							}, {
								xtype: 'hidden',
								name: 'BKLCountry_id',
								id: 'PEW_BKLCountry_id'
							}, {
								xtype: 'hidden',
								name: 'BKLRGN_id',
								id: 'PEW_BKLRGN_id'
							}, {
								xtype: 'hidden',
								name: 'BKLRGNSocr_id',
								id: 'PEW_BKLRGNSocr_id'
							}, {
								xtype: 'hidden',
								name: 'BKLSubRGN_id',
								id: 'PEW_BKLSubRGN_id'
							}, {
								xtype: 'hidden',
								name: 'BKLSubRGNSocr_id',
								id: 'PEW_BKLSubRGNSocr_id'
							}, {
								xtype: 'hidden',
								name: 'BKLCity_id',
								id: 'PEW_BKLCity_id'
							}, {
								xtype: 'hidden',
								name: 'BKLCitySocr_id',
								id: 'PEW_BKLCitySocr_id'
							}, {
								xtype: 'hidden',
								name: 'BPersonSprTerrDop_id',
								id: 'PEW_BPersonSprTerrDop_id'
							}, {
								xtype: 'hidden',
								name: 'BKLTown_id',
								id: 'PEW_BKLTown_id'
							}, {
								xtype: 'hidden',
								name: 'BKLTownSocr_id',
								id: 'PEW_BKLTownSocr_id'
							}, {
								xtype: 'hidden',
								name: 'BKLStreet_id',
								id: 'PEW_BKLStreet_id'
							}, {
								xtype: 'hidden',
								name: 'BKLStreetSocr_id',
								id: 'PEW_BKLStreetSocr_id'
							},  {
								xtype: 'hidden',
								name: 'BAddress_House',
								id: 'PEW_BAddress_House'
							}, {
								xtype: 'hidden',
								name: 'BAddress_Corpus',
								id: 'PEW_BAddress_Corpus'
							}, {
								xtype: 'hidden',
								name: 'BAddress_Flat',
								id: 'PEW_BAddress_Flat'
							},{
								xtype: 'hidden',
								name: 'BAddressSpecObject_id',
								id: 'PEW_BAddressSpecObject_id'
							},{
								xtype: 'hidden',
								name: 'BAddressSpecObject_Value',
								id: 'PEW_BAddressSpecObject_Value'
							}, {
								xtype: 'hidden',
								name: 'BAddress_Address',
								id: 'PEW_BAddress_Address'
							},
							new sw.Promed.TripleTriggerField ({
								enableKeyEvents: true,
								fieldLabel: lang['adres_rojdeniya'],
								id: 'PEW_BAddress_AddressText',
								name: 'BAddress_AddressText',
								readOnly: true,
								tabIndex: TABINDEX_PEF + 10,
								trigger1Class: 'x-form-search-trigger',
								trigger2Class: 'x-form-equil-trigger',
								trigger3Class: 'x-form-clear-trigger',
								width: 610,

								listeners: {
									'keydown': function(inp, e) {
										if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
											if ( e.F4 == e.getKey() )
												inp.onTrigger1Click();

											if ( e.F2 == e.getKey() )
												inp.onTrigger2Click();

											if ( e.DELETE == e.getKey() && e.altKey)
												inp.onTrigger3Click();

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

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											return false;
										}
									},
									'keyup': function( inp, e ) {
										if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) ) {
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

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}

											return false;
										}
									}
								},
								onTrigger3Click: function() {
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									if (!ownerForm.findById('PEW_BAddress_AddressText').disabled)
									{
										ownerForm.findById('PEW_BAddress_Zip').setValue('');
										ownerForm.findById('PEW_BKLCountry_id').setValue('');
										ownerForm.findById('PEW_BKLRGN_id').setValue('');
										ownerForm.findById('PEW_BKLRGNSocr_id').setValue('');
										ownerForm.findById('PEW_BKLSubRGN_id').setValue('');
										ownerForm.findById('PEW_BKLSubRGNSocr_id').setValue('');
										ownerForm.findById('PEW_BKLCity_id').setValue('');
										ownerForm.findById('PEW_BKLCitySocr_id').setValue('');
										ownerForm.findById('PEW_BPersonSprTerrDop_id').setValue('');
										ownerForm.findById('PEW_BKLTown_id').setValue('');
										ownerForm.findById('PEW_BKLTownSocr_id').setValue('');
										ownerForm.findById('PEW_BKLStreet_id').setValue('');
										ownerForm.findById('PEW_BKLStreetSocr_id').setValue('');
										ownerForm.findById('PEW_BAddress_House').setValue('');
										ownerForm.findById('PEW_BAddress_Corpus').setValue('');
										ownerForm.findById('PEW_BAddress_Flat').setValue('');
										ownerForm.findById('PEW_BAddress_Address').setValue('');
										ownerForm.findById('PEW_BAddress_AddressText').setValue('');
										ownerForm.findById('PEW_BAddressSpecObject_id').setValue('');
										ownerForm.findById('PEW_BAddressSpecObject_Value').setValue('');

									}
								},
								onTrigger2Click: function() {
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									if (!ownerForm.findById('PEW_BAddress_AddressText').disabled)
									{
										ownerForm.findById('PEW_BAddress_Zip').setValue(ownerForm.findById('PEW_PAddress_Zip').getValue());
										ownerForm.findById('PEW_BKLCountry_id').setValue(ownerForm.findById('PEW_PKLCountry_id').getValue());
										ownerForm.findById('PEW_BKLRGN_id').setValue(ownerForm.findById('PEW_PKLRGN_id').getValue());
										ownerForm.findById('PEW_BKLRGNSocr_id').setValue(ownerForm.findById('PEW_PKLRGNSocr_id').getValue());
										ownerForm.findById('PEW_BKLSubRGN_id').setValue(ownerForm.findById('PEW_PKLSubRGN_id').getValue());
										ownerForm.findById('PEW_BKLSubRGNSocr_id').setValue(ownerForm.findById('PEW_PKLSubRGNSocr_id').getValue());
										ownerForm.findById('PEW_BKLCity_id').setValue(ownerForm.findById('PEW_PKLCity_id').getValue());
										ownerForm.findById('PEW_BKLCitySocr_id').setValue(ownerForm.findById('PEW_PKLCitySocr_id').getValue());
										ownerForm.findById('PEW_BPersonSprTerrDop_id').setValue(ownerForm.findById('PEW_PPersonSprTerrDop_id').getValue());
										ownerForm.findById('PEW_BKLTown_id').setValue(ownerForm.findById('PEW_PKLTown_id').getValue());
										ownerForm.findById('PEW_BKLTownSocr_id').setValue(ownerForm.findById('PEW_PKLTownSocr_id').getValue());
										ownerForm.findById('PEW_BKLStreet_id').setValue(ownerForm.findById('PEW_PKLStreet_id').getValue());
										ownerForm.findById('PEW_BKLStreetSocr_id').setValue(ownerForm.findById('PEW_PKLStreetSocr_id').getValue());
										ownerForm.findById('PEW_BAddress_House').setValue(ownerForm.findById('PEW_PAddress_House').getValue());
										ownerForm.findById('PEW_BAddress_Corpus').setValue(ownerForm.findById('PEW_PAddress_Corpus').getValue());
										ownerForm.findById('PEW_BAddress_Flat').setValue(ownerForm.findById('PEW_PAddress_Flat').getValue());
										ownerForm.findById('PEW_BAddressSpecObject_id').setValue(ownerForm.findById('PEW_PAddressSpecObject_id').getValue());
										ownerForm.findById('PEW_BAddressSpecObject_Value').setValue(ownerForm.findById('PEW_PAddressSpecObject_Value').getValue());
										if (!Ext.isEmpty(ownerForm.findById('PEW_PAddress_Address').getValue())) {
											ownerForm.findById('PEW_BAddress_Address').setValue(ownerForm.findById('PEW_PAddress_Address').getValue());
										} else {
											ownerForm.findById('PEW_BAddress_Address').setValue(ownerForm.findById('PEW_PAddress_AddressText').getValue());
										}
										ownerForm.findById('PEW_BAddress_AddressText').setValue(ownerForm.findById('PEW_PAddress_AddressText').getValue());
									}
								},
								onTrigger1Click: function() {
									var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
									var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt;
									if (!ownerForm.findById('PEW_BAddress_AddressText').disabled)
									{
										getWnd('swAddressEditWindow').show({
											fields: {
												Address_ZipEdit: ownerForm.findById('PEW_BAddress_Zip').getValue(),
												KLCountry_idEdit: ownerForm.findById('PEW_BKLCountry_id').getValue(),
												KLRgn_idEdit: ownerForm.findById('PEW_BKLRGN_id').getValue(),
												KLSubRGN_idEdit: ownerForm.findById('PEW_BKLSubRGN_id').getValue(),
												KLCity_idEdit: ownerForm.findById('PEW_BKLCity_id').getValue(),
												PersonSprTerrDop_idEdit: ownerForm.findById('PEW_BPersonSprTerrDop_id').getValue(),
												KLTown_idEdit: ownerForm.findById('PEW_BKLTown_id').getValue(),
												KLStreet_idEdit: ownerForm.findById('PEW_BKLStreet_id').getValue(),
												Address_HouseEdit: ownerForm.findById('PEW_BAddress_House').getValue(),
												Address_CorpusEdit: ownerForm.findById('PEW_BAddress_Corpus').getValue(),
												Address_FlatEdit: ownerForm.findById('PEW_BAddress_Flat').getValue(),
												Address_AddressEdit: ownerForm.findById('PEW_BAddress_AddressText').getValue(),
												AddressSpecObject_idEdit:ownerForm.findById('PEW_BAddressSpecObject_Value').getValue(),
												AddressSpecObject_id:ownerForm.findById('PEW_BAddressSpecObject_id').getValue(),
												addressType: 1,
												bdz: ownerForm.findById('server_id').getValue()
											},
											callback: function(values) {
												ownerForm.findById('PEW_BAddress_Zip').setValue(values.Address_ZipEdit);
												ownerForm.findById('PEW_BKLCountry_id').setValue(values.KLCountry_idEdit);
												ownerForm.findById('PEW_BKLRGN_id').setValue(values.KLRgn_idEdit);
												ownerForm.findById('PEW_BKLRGNSocr_id').setValue(values.KLRGN_Socr);
												ownerForm.findById('PEW_BKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
												ownerForm.findById('PEW_BKLSubRGNSocr_id').setValue(values.KLSubRGN_Socr);
												ownerForm.findById('PEW_BKLCity_id').setValue(values.KLCity_idEdit);
												ownerForm.findById('PEW_BKLCitySocr_id').setValue(values.KLCity_Socr);
												ownerForm.findById('PEW_BPersonSprTerrDop_id').setValue(values.PersonSprTerrDop_idEdit);
												ownerForm.findById('PEW_BKLTown_id').setValue(values.KLTown_idEdit);
												ownerForm.findById('PEW_BKLTownSocr_id').setValue(values.KLTown_Socr);
												ownerForm.findById('PEW_BKLStreet_id').setValue(values.KLStreet_idEdit);
												ownerForm.findById('PEW_BKLStreetSocr_id').setValue(values.KLStreet_Socr);
												ownerForm.findById('PEW_BAddress_House').setValue(values.Address_HouseEdit);
												ownerForm.findById('PEW_BAddress_Corpus').setValue(values.Address_CorpusEdit);
												ownerForm.findById('PEW_BAddress_Flat').setValue(values.Address_FlatEdit);
												ownerForm.findById('PEW_BAddressSpecObject_id').setValue(values.AddressSpecObject_id);
												ownerForm.findById('PEW_BAddressSpecObject_Value').setValue(values.AddressSpecObject_idEdit);
												ownerForm.findById('PEW_BAddress_Address').setValue(values.Address_AddressEdit);
												ownerForm.findById('PEW_BAddress_AddressText').setValue(values.Address_AddressEdit);
												ownerForm.findById('PEW_BAddress_AddressText').focus(true, 500);

											},
											onClose: function() {
												ownerForm.findById('PEW_BAddress_AddressText').focus(true, 500);
											}
										})
									}
								}
							})]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: lang['polis'],
							xtype: 'fieldset',

							items: [{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										codeField: 'OMSSprTerr_Code',
										editable: true,
										forceSelection: true,
										hiddenName: 'OMSSprTerr_id',
										listeners: {
											'select': function(combo) {
												this.disablePolisFields(false);
											}.createDelegate(this),
											'change': function(combo) {
												this.changeDocVerificationDependingOMSTerr(this.findById('person_edit_form').getForm().findField('DocumentType_id').getValue());
												if ( !combo.getValue() ) {
													this.disablePolisFields(true);
													return false;
												}

												this.disablePolisFields(false);

												var base_form = this.findById('person_edit_form').getForm();

												if ( getRegionNick() == 'ufa' && base_form.findField('PolisType_id').getValue() == 4 )
												{
													base_form.findField('Polis_Num').minLength = 0;
													base_form.findField('Polis_Num').maxLength = 16;
													//return;
												}
												else
												{
													base_form.findField('Polis_Num').clearInvalid();
												}

												var OrgSMOCombo = base_form.findField('OrgSMO_id');

												OrgSMOCombo.clearValue();
												OrgSMOCombo.lastQuery = '';

												// var idx = combo.getStore().find('OMSSprTerr_id', combo.getValue());
												var number = combo.getValue();
												var idx = -1;
												var findIndex = 0;

												combo.getStore().findBy(function(r) {
													if ( r.get('OMSSprTerr_id') == number ) {
														idx = findIndex;
														return true;
													}

													findIndex++;
												});

												if ( idx >= 0 ) {
													var code = combo.getStore().getAt(idx).get('OMSSprTerr_Code');
													var klrgn_id = combo.getStore().getAt(idx).get('KLRgn_id');

													// если регион сервера Уфа и выбран уфимский, то устанавливаем соответствующее правило
													// для проверки единого номера полиса на уфимском сервере
													if ( getRegionNick() == 'ufa' && klrgn_id == 2 )
													{
														if ( base_form.findField('PolisType_id').getValue() != 3 ) {
															base_form.findField('Polis_Num').minLength = 16;
															base_form.findField('Polis_Num').maxLength = 16;
														}
														else {
															base_form.findField('Polis_Num').minLength = 9;
															base_form.findField('Polis_Num').maxLength = 9;
															base_form.findField('Polis_Num').clearInvalid();
														}
														//base_form.findField('Polis_Num').maskRe =/\d/;
														this.setMaskPolisNum();
													}
													else
													{
														if ( base_form.findField('PolisType_id').getValue() == 2&&getRegionNick()=='astra' ) {
															base_form.findField('Polis_Num').maskRe = /[a-zA-Zа-яА-ЯёЁ\d\/]/;
														}else{
															//base_form.findField('Polis_Num').maskRe =/\d/;
															this.setMaskPolisNum();
															//base_form.findField('Polis_Ser').setValue('');
														}
														base_form.findField('Polis_Num').minLength = 0;
														base_form.findField('Polis_Num').maxLength = 18;
													}

													if ( code <= 61 )  {
														base_form.findField('Polis_Ser').disableTransPlug = false;
														// Если не уфа и полис не нового образца
														if ( !(getRegionNick() == 'ufa') &&  (base_form.findField('PolisType_id').getFieldValue('PolisType_Code')!=4) ) {
															// то серия полиса обязательна для ввода
															//base_form.findField('Polis_Ser').setAllowBlank(false);
														}
														else
														{
															// иначе же нет
															base_form.findField('Polis_Ser').disableTransPlug = true;
															//base_form.findField('Polis_Ser').setAllowBlank(true);
														}
													}
													else {
														base_form.findField('Polis_Ser').disableTransPlug = true;
														//base_form.findField('Polis_Ser').setAllowBlank(true);
													}

													var cur_reg = getGlobalOptions().region ? getGlobalOptions().region['number'] : 59;
													//if ( /*( code < 100 && cur_reg == 59 ) ||*/ ( cur_reg == klrgn_id ) )
													if ( cur_reg == 59 &&  cur_reg == klrgn_id )
													{
														OrgSMOCombo.baseFilterFn = function(record) {
															if ( /.+/.test(record.get('OrgSMO_RegNomC')) && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue()) )
																return true;
															else
																return false;
														}
														OrgSMOCombo.getStore().filterBy(function(record) {
															if ( /.+/.test(record.get('OrgSMO_RegNomC')) && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue()) )
																return true;
															else
																return false;
														});
													}
													else {
														OrgSMOCombo.baseFilterFn = function(record) {
															if ( klrgn_id == record.get('KLRgn_id') && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue())  )
																return true;
															else
																return false;
														}
														OrgSMOCombo.getStore().filterBy(function(record) {
															if ( klrgn_id == record.get('KLRgn_id') && (record.get('OrgSMO_endDate') == '' || record.get('OrgSMO_id') == OrgSMOCombo.getValue())  )
																return true;
															else
																return false;
														});
														/*OrgSMOCombo.baseFilterFn = null;
														OrgSMOCombo.getStore().filter('OrgSMO_RegNomC', '');*/
													}
													if ( cur_reg == 19 ) {
														base_form.findField('Polis_Ser').disableTransPlug = true;
														if ( code == 1 ) {
															base_form.findField('Polis_Ser').maskRe = /^[smSMа-яА-ЯёЁ\d\s\-]+$/;
														} else {
															base_form.findField('Polis_Ser').maskRe = /^[a-zA-Zа-яА-ЯёЁ\d\s\-]+$/;
														}
														if(!Ext.isEmpty(base_form.findField('Polis_Ser').getValue()) && !base_form.findField('Polis_Ser').maskRe.exec(base_form.findField('Polis_Ser').getValue())) {
															base_form.findField('Polis_Ser').setValue(null);
														}
													}
												}
											}.createDelegate(this)
										},
										onTrigger2Click: function() {
											this.findById('person_edit_form').getForm().findField('OMSSprTerr_id').clearValue();
											this.disablePolisFields(true);
										}.createDelegate(this),
										tabIndex: TABINDEX_PEF + 11,
										width: 170,
										xtype: 'swomssprterrcombo'
									}]
								}, {
									labelWidth: 40,
									layout: 'form',

									items: [{
										allowBlank: false,
										comboSubject: 'PolisType',
										fieldLabel: lang['tip'],
										listeners: {
											'change': function(combo, newValue, oldValue) {
												var base_form = this.findById('person_edit_form').getForm();
												var OMSSprTerr_id = base_form.findField('OMSSprTerr_id').getValue();

                                                base_form.findField('Polis_begDate').setAllowBlank(false);

												if (newValue == 4) {
													base_form.findField('Federal_Num').setAllowBlank(false);
													base_form.findField('Polis_Num').setAllowBlank(true);
													base_form.findField('Polis_Ser').setAllowBlank(true);
													base_form.findField('Polis_Num').setValue('');
													base_form.findField('Polis_Num').disable();
													base_form.findField('Polis_Ser').setValue('');
													base_form.findField('Polis_Ser').disable();
													base_form.findField('Polis_Ser').minLength = 0;
													base_form.findField('Polis_Num').minLength = 0;
													base_form.findField('Polis_Num').maskRe =/\d/;
												} else {
													base_form.findField('Federal_Num').setAllowBlank(true);
													base_form.findField('Polis_Num').setAllowBlank(false);
													base_form.findField('Polis_Ser').setAllowBlank(true);
													if(getRegionNick()=='kareliya'){
														base_form.findField('Polis_Ser').setDisabled(newValue==3);
													}else{
														base_form.findField('Polis_Ser').enable();
													}
													base_form.findField('Polis_Num').enable();

													if ( newValue == 2&&getRegionNick()=='astra' ) {
														base_form.findField('Polis_Num').maskRe = /[a-zA-Zа-яА-ЯёЁ\d\/]/;
													}else{
														//base_form.findField('Polis_Num').maskRe =/\d/;
														this.setMaskPolisNum();
														if(oldValue==2)
														base_form.findField('Polis_Num').setValue('');
													}
													if ( newValue == 3 ) {
														if(getRegionNick()=='perm'){
															base_form.findField('Polis_Num').minLength = 5;
															base_form.findField('Polis_Num').maxLength = 99;
														}else{
															base_form.findField('Polis_Num').minLength = (getRegionNick().inlist([ 'astra' ]) ? 6 : 9);
															base_form.findField('Polis_Num').maxLength =9;
														}
														base_form.findField('Polis_Ser').minLength = (getRegionNick().inlist([ 'astra' ]) ? 3 : 0);
														//base_form.findField('Polis_Ser').maxLength = (getRegionNick().inlist([ 'kareliya' ]) ? 3 : undefined);
													}
													else {
														base_form.findField('Polis_Ser').minLength = 0;
														base_form.findField('Polis_Ser').maxLength = undefined;
														base_form.findField('Polis_Num').minLength = 0;
														base_form.findField('Polis_Num').maxLength = 18;
													}
												}
											}.createDelegate(this),
											'select': function(combo, record, index) {
												this.findById('person_edit_form').getForm().findField('PEW_OrgSMO_id').clearValue();
												this.findById('person_edit_form').getForm().findField('OMSSprTerr_id').fireEvent('change', this.findById('person_edit_form').getForm().findField('OMSSprTerr_id'));
													/*if ( 1 == record.get('PolisType_Code') ) {
														this.findById('person_edit_form').getForm().findField('OrgSMO_id').getStore().filterBy(function(rec) {
															if ( rec.get('OrgSMO_RegNomC') == '' && rec.get('OrgSMO_RegNomN') == '' ) {
																return false;
															}
															else {
																return true;
															}
														});
													}
													else {
														this.findById('person_edit_form').getForm().findField('OrgSMO_id').getStore().clearFilter();
													}*/
											}.createDelegate(this)
										},
										tabIndex: TABINDEX_PEF + 12,
										validateOnBlur: false,
										validationEvent: false,
										width: 120,
										xtype: 'swcommonsprcombo'
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items: [{
										tabIndex: TABINDEX_PEF + 12,
										fieldLabel: lang['forma_polisa'],
										name: 'PolisFormType_id',
										hiddenName: 'PolisFormType_id',
										width: 170,
										xtype: 'swpolisformtypecombo'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['seriya'],
										maxLength: 10,
										name: 'Polis_Ser',
										plugins: [ new Ext.ux.translit(true, true) ],
										maskRe: /./,
										tabIndex: TABINDEX_PEF + 13,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									labelWidth: 50,
									items:[{
										allowBlank: false,
										xtype: 'textfield',
										//maskRe: (getRegionNick() == 'ufa') ? /\d/ : null,
										maskRe: /\d/,
										//allowNegative: false,
										//allowDecimals: false,
										maxLength:  (getRegionNick() == 'ufa') ? 16 : 18,
										minLength: (getRegionNick() == 'ufa') ? 16 : 0,
										//autoCreate: (getRegionNick() == 'ufa') ? {tag: "input", type: "text", size: "16", maxLength: "16", autocomplete: "off"} : {tag: "input", type: "text", size: "20", autocomplete: "off"},
										width: 180,
										fieldLabel: lang['nomer'],
										name: 'Polis_Num',
										tabIndex: TABINDEX_PEF + 14
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items: [{
										xtype: 'textfield',
										maskRe: /\d/,
										maxLength: 16,
										minLength: 16,
										//autoCreate: {tag: "input", type: "text", size: "16", maxLength: "16", autocomplete: "off"},
										width: 170,
										fieldLabel: lang['ed_nomer'],
										name: 'Federal_Num',
										tabIndex: TABINDEX_PEF + 15
									}]
								}]
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										id: 'PEW_OrgSMO_id',
										tabIndex: TABINDEX_PEF + 16,
										allowBlank: false,
										xtype: 'sworgsmocombo',
										minChars: 1,
										queryDelay: 1,
										hiddenName: 'OrgSMO_id',
										lastQuery: '',
										listWidth: '300',
										onTrigger2Click: function() {
											if ( this.disabled )
												return;

											var base_form = win.findById('person_edit_form').getForm();
											var combo = this;
											var idx = base_form.findField('OMSSprTerr_id').getStore().findBy(function(rec) { return rec.get('OMSSprTerr_id') == base_form.findField('OMSSprTerr_id').getValue(); });

											if ( idx >= 0 ) {
												var omsterrcode = base_form.findField('OMSSprTerr_id').getStore().getAt(idx).get('OMSSprTerr_Code');
												var klrgn_id = base_form.findField('OMSSprTerr_id').getStore().getAt(idx).get('KLRgn_id');
											} else {
												var omsterrcode = -1;
												var klrgn_id = -1;
											}

											getWnd('swOrgSearchWindow').show({
												enableOrgType: true,
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														var index = combo.getStore().findBy(function(rec) { return rec.get('Org_id') == orgData.Org_id; });
														if (index >= 0) {
															var record = combo.getStore().getAt(index);
															combo.setValue(record.get('OrgSMO_id'));
															combo.focus(true, 500);
															combo.fireEvent('change', combo);
														}
													}

													getWnd('swOrgSearchWindow').hide();
												},
												onClose: function() {combo.focus(true, 200)},
												object: 'smo',
												KLRgn_id: klrgn_id,
												OMSSprTerr_Code: omsterrcode
											});
										},
										enableKeyEvents: true,
										forceSelection: false,
										typeAhead: true,
										typeAheadDelay: 1,
										width: 191,
										listeners: {
											'blur': function(combo) {
												if (combo.getRawValue()=='')
													combo.clearValue();

												if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 )
													combo.clearValue();
											},
											'keydown': function( inp, e ) {
												if ( e.F4 == e.getKey() )
												{
													if ( inp.disabled )
														return;

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

													if ( Ext.isIE )
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}

													inp.onTrigger2Click();
													inp.collapse();

													return false;
												}
											},
											'keyup': function(inp, e) {
												if ( e.F4 == e.getKey() )
												{
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

													if ( Ext.isIE )
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}

													return false;
												}
											}
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 85,
									items: [{
										//allowBlank: false,
										fieldLabel: lang['data_vyidachi'],
										format: 'd.m.Y',
										name: 'Polis_begDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_PEF + 17,
										width: 94,
										xtype: 'swdatefield',
										listeners: {
											'change': function(combo) {
												var base_form = win.findById('person_edit_form').getForm();
												base_form.findField('Polis_endDate').setMinValue(combo.getValue());

											}
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 136,
									items: [{
										allowBlank: true,
										fieldLabel: lang['data_zakryitiya'],
										format: 'd.m.Y',
										name: 'Polis_endDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_PEF + 18,
										width: 94,
										xtype: 'swdatefield',
										listeners: {
											'change': function(combo) {
												var base_form = win.findById('person_edit_form').getForm();
												var beg_dt = base_form.findField('Polis_begDate').getValue();
												if(beg_dt){
													base_form.findField('Polis_endDate').setMinValue(beg_dt);
												}

											}
										}
									}]
								}]
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: lang['dokument'],
							xtype: 'fieldset',

							items: [{
								border: false,
								layout: 'column',

								items: [{
									layout: 'form',
									items: [{
										fieldLabel: lang['tip'],
										listeners: {
											'change': function(combo, newValue, oldValue) {
												var index = combo.getStore().findBy(function(rec) {
													return (rec.get(combo.valueField) == newValue);
												});
												combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
											},
											'select': function(combo, record, index) {
												var
													base_form = this.findById('person_edit_form').getForm(),
													c_combo = base_form.findField('KLCountry_id'),
													mask;

												var citizenship_control = Ext.globalOptions.globals.citizenship_control;
												if ( record && typeof record == 'object' && !Ext.isEmpty(record.get('DocumentType_id')) ) {
													if ( !Ext.isEmpty(record.get('DocumentType_MaskSer')) ) {
														mask = new RegExp(record.get('DocumentType_MaskSer'));
														base_form.findField('Document_Ser').regex = mask;
													}

													if ( !Ext.isEmpty(record.get('DocumentType_MaskNum')) ) {
														base_form.findField('Document_Num').regex = new RegExp( record.get('DocumentType_MaskNum') );
													}

													this.disableDocumentFields(false);

													if ( Ext.isEmpty(mask) || mask.test('') ) {
														base_form.findField('Document_Ser').setAllowBlank(true);
														base_form.findField('Document_Ser').regex = undefined;
														base_form.findField('Document_Ser').clearInvalid();
													}
													else {
														if ( base_form.findField('Person_IsUnknown').checked ) {
															base_form.findField('Document_Ser').setAllowBlank(true);
														}
														else {
															base_form.findField('Document_Ser').setAllowBlank(false);
														}
													}

													if ( base_form.findField('Person_IsUnknown').checked ) {
														base_form.findField('Document_Num').setAllowBlank(true);
													}
													else {
														base_form.findField('Document_Num').setAllowBlank(false);
													}
													/* отключение маски для поля номера документа, если выбрано 24.Свидетельство о рождении не гражданина РФ */
													if ( record.get('DocumentType_Code') == 24 ) {
														base_form.findField('Document_Num').regex = undefined;
													}

													if ( record.get('DocumentType_Code') == 22 && getRegionNick() != 'kz') {
														c_combo.clearValue();
														c_combo.disable();
														c_combo.setAllowBlank(true);
													}
													else {
														c_combo.enable();
														// if(getRegionNick() != 'ufa'){
														// 	c_combo.setAllowBlank(false);
														// }
														if( citizenship_control == 3 ){
															c_combo.setAllowBlank(false);
														}else{
															c_combo.setAllowBlank(true);
														}

														if ( Number(combo.getFieldValue('DocumentType_Code')).inlist([9,10,11,12,18,21,23,24,25,26]) ) {
															c_combo.clearValue();
														}
														else if(getRegionNick() != 'kz'){
															c_combo.setFieldValue('KLCountry_Code', 643);
														}
													}

													this.changeDocVerificationDependingOMSTerr(record.get('DocumentType_id'));
												}
												else {
													//c_combo.setAllowBlank(getRegionNick().inlist(['ufa','kz']));
													if(getRegionNick() != 'kz'){
														c_combo.setFieldValue('KLCountry_Code', 643);
													}
													if( citizenship_control == 3 ){
														c_combo.setAllowBlank(false);
													}else{
														c_combo.setAllowBlank(true);
													}

													base_form.findField('Document_Ser').regex = undefined;
													base_form.findField('Document_Num').regex = undefined;

													this.disableDocumentFields(true);

													base_form.findField('Document_Num').setAllowBlank(true);
													base_form.findField('Document_Ser').clearInvalid();
													base_form.findField('Document_Num').clearInvalid();
												}

												if ( base_form.findField('Person_IsAnonym').checked ) {
													c_combo.setAllowBlank(true);
												}
												var c_index = c_combo.getStore().indexOfId(c_combo.getValue());
												var c_record = c_combo.getStore().getAt(c_index);
												base_form.findField('KLCountry_id').fireEvent('select', c_combo, c_record, c_index);
												
												if (getRegionNick() == 'ekb') {//#8359
													var value = true;
														if (combo.getValue().inlist(['3', '13'])) {
															value = false;
														}
													base_form.findField('OrgDep_id').setAllowBlank(value);
													base_form.findField('Document_begDate').setAllowBlank(value);
												}
											}.createDelegate(this)
										},
										onTrigger2Click: function() {
											this.findById('').getForm().findField('DocumentType_id').clearValue();
											this.disableDocumentFields(true);
										}.createDelegate(this),
										listWidth: 400,
										tabIndex: TABINDEX_PEF + 19,
										width: 191,
										xtype: 'swdocumenttypecombo'
									}]
								}, {
									layout: 'form',
									labelWidth: 85,
									items:[{
										fieldLabel: lang['seriya'],
										maxLength: 10,
										name: 'Document_Ser',
										tabIndex: TABINDEX_PEF + 20,
										width: 94,
										xtype: 'textfield',
										id: 'PEW_Document_Ser'
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items:[{
										fieldLabel: lang['nomer'],
										maxLength: 20,
										name: 'Document_Num',
										tabIndex: TABINDEX_PEF + 21,
										width: 130,
										xtype: 'textfield',
										id: 'PEW_Document_Num'
									}]
								}]
							}, {
								border: false,
								layout: 'column',

								items:[{
									layout: 'form',
									items:[{
										allowBlank: true,
										editable: false,
										enableKeyEvents: true,
										hiddenName: 'OrgDep_id',
										listeners: {
											'keydown': function( inp, e ) {
												if ( inp.disabled )
													return;
												if ( e.F4 == e.getKey() )
												{
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
													if ( Ext.isIE )
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													inp.onTrigger1Click();
													return false;
												}
											},
											'keyup': function(inp, e) {
												if ( e.F4 == e.getKey() )
												{
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
													if ( Ext.isIE )
													{
														e.browserEvent.keyCode = 0;
														e.browserEvent.which = 0;
													}
													return false;
												}
											}
										},
										listWidth: 300,
										onTrigger1Click: function() {
											if ( this.disabled )
												return;
											var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
											var combo = this;
											getWnd('swOrgSearchWindow').show({
												enableOrgType: true,
												onSelect: function(orgData) {
													if ( orgData.Org_id > 0 )
													{
														combo.getStore().load({
															params: {
																Object:'OrgDep',
																OrgDep_id: orgData.Org_id,
																OrgDep_Name: ''
															},
															callback: function()
															{
																combo.setValue(orgData.Org_id);
																combo.focus(true, 500);
																combo.fireEvent('change', combo);
															}
														});
													}
													getWnd('swOrgSearchWindow').hide();
												},
												onClose: function() {combo.focus(true, 200)},
												object: 'dep'
											});
										},
										tabIndex: TABINDEX_PEF + 22,
										triggerAction: 'none',
										width: 191,
										xtype: 'sworgdepcombo'
									}]
								}, {
									layout: 'form',
									labelWidth: 85,
									items:[{
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										format: 'd.m.Y',
										fieldLabel: lang['data_vyidachi'],
										width: 94,
										name: 'Document_begDate'
									}]
								}]
							}]
						},
							{
							autoHeight: true,
							labelWidth: 125,
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
							title: lang['grajdanstvo'],
							xtype: 'fieldset',

							items: [{
								border: false,
								layout: 'column',

								items: [{
									layout: 'form',
									items: [{
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'swklcountrycombo',
										fieldLabel: lang['grajdanstvo'],
										allowBlank: getRegionNick().inlist(['ufa','kz']),
										width: 191,
										id: 'label0001',
										hiddenName: 'KLCountry_id',
										listeners: {
											'select': function(combo, record, index) {
												var base_form = this.findById('person_edit_form').getForm();
												if (record && record.get('KLCountry_Code') == 643) {
													base_form.findField('NationalityStatus_IsTwoNation').enable();
													base_form.findField('LegalStatusVZN_id').hideContainer();
													base_form.findField('LegalStatusVZN_id').setValue(null);
												} else {
													base_form.findField('NationalityStatus_IsTwoNation').disable();
													base_form.findField('NationalityStatus_IsTwoNation').setValue(false);
													base_form.findField('LegalStatusVZN_id').showContainer();
												}
											}.createDelegate(this),
											'change': function(combo, newValue, oldValue) {
												var index = combo.getStore().indexOfId(newValue);
												var record = combo.getStore().getAt(index);
												combo.fireEvent('select', combo, record, index);
											},
                                            'expand': function (combo) {
												combo.getStore().clearFilter();
											}
										}
									}]
								}, {
									layout: 'form',
									labelWidth: 5,

									items: [{
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'checkbox',
										boxLabel: lang['dvoynoe_grajdanstvo_rf_i_inostrannoe_gosudarstvo'],
										labelSeparator: '',
										name: 'NationalityStatus_IsTwoNation'
									}]
								}]
							}, {
								xtype: 'swcommonsprcombo',
								comboSubject: 'LegalStatusVZN',
								hiddenName: 'LegalStatusVZN_id',
								fieldLabel: langs('Правовой статус нерезидента'),
								width: 375
							}]
						}, {
							autoHeight: true,
							labelWidth: 125,
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
							title: lang['mesto_rabotyi'],
							xtype: 'fieldset',

							items: [{
								xtype: 'sworgcombo',
								hiddenName: 'Org_id',
								editable: false,
								fieldLabel: lang['mesto_rabotyi_uchebyi'],
								triggerAction: 'none',
								width: 610,
								tabIndex: TABINDEX_PEF + 24,
								onTrigger1Click: function() {
									var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
									var combo = this;

									getWnd('swOrgSearchWindow').show({
										enableOrgType: true,
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 ) {
												combo.getStore().load({
													params: {
														Object:'Org',
														Org_id: orgData.Org_id,
														Org_Name:''
													},
													callback: function() {
														combo.setValue(orgData.Org_id);
														combo.focus(true, 500);
														combo.fireEvent('change', combo);
													}
												});
											}

											getWnd('swOrgSearchWindow').hide();
										},
										onClose: function() {combo.focus(true, 200)}
									});
								},
								enableKeyEvents: true,
								listeners: {
									'change': function(combo) {
										combo.ownerCt.findById('PEW_OrgUnion_id').clearValue();
										combo.ownerCt.findById('PEW_OrgUnion_id').getStore().load({
											params: {
												Object:'OrgUnion',
												OrgUnion_id:'',
												OrgUnion_Name:'',
												Org_id: combo.getValue()
											}
										});
									},
									'keydown': function( inp, e ) {
										if ( e.F4 == e.getKey() ) {
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

											if ( Ext.isIE ) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											inp.onTrigger1Click();
											return false;
										}
									},
									'keyup': function(inp, e) {
										if ( e.F4 == e.getKey() )
										{
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
											if ( Ext.isIE )
											{
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											return false;
										}
									}
								}
						    }/*, {
						    fieldLabel: lang['mesto_rabotyi_uchebyi'],
							xtype: 'combo',
							anchor: '95%',
							editable: true,
							hiddenName: 'Org_id',
							displayField: 'Org_Name',
							valueField: 'Org_id',
							enableKeyEvents: true,
							minChars: 3,
							mode: 'remote',
							tabIndex: TABINDEX_PEF + 24,
							triggerAction: 'query',
							triggerConfig: {
							    tag: 'span',
							    cls: 'x-form-twin-triggers',
							    cn: [
                                    { tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-search-trigger" },
                                    { tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger" }
							    ]
							},
						    //tpl: '<tpl for="."><div class="x-combo-list-item">{Org_ColoredName}</div></tpl>',
							initComponent: function () {
							    Ext.form.ComboBox.superclass.initComponent.apply(this, arguments);
							},
							initTrigger: function () {
							    var ts = this.trigger.select('.x-form-trigger', true);
							    this.wrap.setStyle('overflow', 'hidden');
							    var triggerField = this;
							    ts.each(function (t, all, index) {
							        t.hide = function () {
							            var w = triggerField.wrap.getWidth();
							            this.dom.style.display = 'none';
							            triggerField.el.setWidth(w - triggerField.trigger.getWidth());
							        };
							        t.show = function () {
							            var w = triggerField.wrap.getWidth();
							            this.dom.style.display = '';
							            triggerField.el.setWidth(w - triggerField.trigger.getWidth());
							        };
							        var triggerIndex = 'Trigger' + (index + 1);

							        if (this['hide' + triggerIndex]) {
							            t.dom.style.display = 'none';
							        }
							        t.on("click", this['on' + triggerIndex + 'Click'], this, { preventDefault: true });
							        t.addClassOnOver('x-form-trigger-over');
							        t.addClassOnClick('x-form-trigger-click');
							    }, this);
							    this.triggers = ts.elements;
							},
							hasStorageValue: function () {
							    var combo = this,
                                    i = combo.getStore().indexOf(combo.findRecord(combo.valueField || combo.displayField, combo.getValue()));
							    return (i !== -1); // индексы: 0 - первый пустой,  -1 - не выбран
							},
							listeners: {
							    'change': function (combo, record, index) {
							        combo.setEditable((!combo.hasStorageValue() || combo.getValue() === ''));
							    },
							    'select': function (combo, record, index) {
							        combo.setEditable((!combo.hasStorageValue() || combo.getValue() === ''));
							    },
							    'keydown': function (inp, e) {
							        var combo = this;
							        if (e.getKey() == e.DELETE) {
							            combo.onTrigger2Click();
							            return true;
							        }
							        if (e.getKey() == e.F4) {
							            combo.onTriggerClick();
							        }
							    }
							},

							onTrigger1Click: function () {
							    var combo = this;

							    if (combo.disabled) return false;
							    combo.collapse();

							    getWnd('swOrgSearchWindow').show({
							        onHide: function () {
							            combo.focus(false);
							        },
							        onSelect: function (orgData) {
							            combo.getStore().removeAll();
							            combo.getStore().loadData([
                                            { Org_id: orgData.Org_id, Org_Name: orgData.Org_Name, Org_ColoredName: '' }
							            ]);

							            combo.setValue(orgData.Org_id);
							            var index = combo.getStore().find('Org_id', orgData.Org_id);

							            if (index == -1) {
							                return false;
							            }

							            var record = combo.getStore().getAt(index);
							            combo.fireEvent('select', combo, record, 0)

							            getWnd('swOrgSearchWindow').hide();

							            //getWnd('swOrgSearchWindow').hide();

							            //combo.getStore().load({
							            //    params: { Org_id: orgData.Org_id },
							            //    callback: function (records, options, success) {
							            //        combo.setValue(orgData.Org_id);
							            //        combo.focus(true, 500);
							            //        combo.fireEvent('change', combo)
							            //    }
							            //});
							        }
							    });
							},
							onTrigger2Click: function () {
							    var combo = this,
                                    oldValue = combo.getValue();
							    combo.collapse();
							    combo.reset();
							    combo.getStore().removeAll();
							    combo.fireEvent('change', combo, combo.getValue(), oldValue);
							    combo.focus();
							},
							store: new Ext.data.JsonStore({
							    autoLoad: false,
							    url: '/?c=Org&m=getOrgColoredList',
							    key: 'Org_id',
							    fields: [
                                    { name: 'Org_id', type: 'int' },
                                    { name: 'Org_Name', type: 'string' }//,
                                    //{ name: 'Org_ColoredName', type: 'string' }
							    ],
							    sortInfo: {
							        field: 'Org_Name'
							    }
							}),
						}/*/,{
								border: false,
								layout: 'column',

								items: [{
									layout: 'form',
									items: [{
										id: 'PEW_OrgUnion_id',
										hiddenName: 'OrgUnion_id',
										xtype: 'sworgunioncombo',
										minChars: 0,
										queryDelay: 1,
										tabIndex: TABINDEX_PEF + 25,
										selectOnFocus: true,
										width: 260,
										forceSelection: false,
										maskRe : /[0-9a-zA-Zа-яА-ЯёЁ\-\s\,\[\]\;\']/
									}]
								},{
									layout: 'form',
									labelWidth: 85,

									items: [{
										xtype: 'swpostcombo',
										minChars: 0,
										queryDelay: 1,
										tabIndex: TABINDEX_PEF + 26,
										hidden: false,
										hideLabel: false,
										hiddenName: 'Post_id',
										fieldLabel: lang['doljnost'],
										selectOnFocus: true,
										width: 260,
										forceSelection: false,
										maskRe : /[0-9a-zA-Zа-яА-ЯёЁ\-\s\,\[\]\;\']/
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								hidden: getRegionNick() == 'kz',
								items: [
									{	
										layout: 'form',
										items: [{
											fieldLabel: 'Уровень образования',
											hiddenName: 'EducationLevel_id',
											listWidth: 400,
											tabIndex: TABINDEX_PEF + 27,
											width: 191,
											xtype: 'sweducationlevelcombo'
										}]
									},
									{
										layout: 'form',
										items: [{
											fieldLabel: lang['zanyatost'],
											hiddenName: 'Employment_id',
											listWidth: 400,
											tabIndex: TABINDEX_PEF + 28,
											width: 191,
											xtype: 'swemploymentcombo'
										}]
									}]
							}
								/*
		
										fieldLabel: lang['doljnost'],
										xtype: 'combo',
										anchor: '95%',
										editable: true,
										hiddenName: 'Post_id',
										displayField: 'Post_Name',
										valueField: 'Post_id',
										enableKeyEvents: true,
										minChars: 3,
										mode: 'remote',
										tabIndex: TABINDEX_PEF + 29,
										triggerAction: 'query',
										triggerConfig: {
											tag: 'span',
											cls: 'x-form-twin-triggers',
											cn: [
												{ tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger" },
												{ tag: "img", src: Ext.BLANK_IMAGE_URL, cls: "x-form-trigger x-form-clear-trigger" }
											]
										},
										//tpl: '<tpl for="."><div class="x-combo-list-item">{Post_ColoredName}</div></tpl>',
										initComponent: function () {
											Ext.form.ComboBox.superclass.initComponent.apply(this, arguments);
										},
										initTrigger: function () {
											var ts = this.trigger.select('.x-form-trigger', true);
											this.wrap.setStyle('overflow', 'hidden');
											var triggerField = this;
											ts.each(function (t, all, index) {
												t.hide = function () {
													var w = triggerField.wrap.getWidth();
													this.dom.style.display = 'none';
													triggerField.el.setWidth(w - triggerField.trigger.getWidth());
												};
												t.show = function () {
													var w = triggerField.wrap.getWidth();
													this.dom.style.display = '';
													triggerField.el.setWidth(w - triggerField.trigger.getWidth());
												};
												var triggerIndex = 'Trigger' + (index + 1);
		
												if (this['hide' + triggerIndex]) {
													t.dom.style.display = 'none';
												}
												t.on("click", this['on' + triggerIndex + 'Click'], this, { preventDefault: true });
												t.addClassOnOver('x-form-trigger-over');
												t.addClassOnClick('x-form-trigger-click');
											}, this);
											this.triggers = ts.elements;
										},
										hasStorageValue: function () {
											var combo = this,
												i = combo.getStore().indexOf(combo.findRecord(combo.valueField || combo.displayField, combo.getValue()));
											return (i !== -1); // индексы: 0 - первый пустой,  -1 - не выбран
										},
										listeners: {
											'change': function (combo, record, index) {
												combo.setEditable((!combo.hasStorageValue() || combo.getValue() === ''));
											},
											'select': function (combo, record, index) {
												combo.setEditable((!combo.hasStorageValue() || combo.getValue() === ''));
											},
											'keydown': function (inp, e) {
												var combo = this;
												if (e.getKey() == e.DELETE) {
													combo.onTrigger2Click();
													return true;
												}
												if (e.getKey() == e.F4) {
													combo.onTriggerClick();
												}
											}
										},
		
										onTrigger1Click: function () {
											var combo = this;
											combo.expand();
										},
										onTrigger2Click: function () {
											var combo = this,
												oldValue = combo.getValue();
											combo.collapse();
											combo.reset();
											combo.getStore().removeAll();
											combo.fireEvent('change', combo, combo.getValue(), oldValue);
											combo.focus();
										},
										store: new Ext.data.JsonStore({
											autoLoad: false,
											url: '/?c=Person&m=getPostColoredList',
											key: 'Post_id',
											fields: [
												{ name: 'Post_id', type: 'int' },
												{ name: 'Post_Name', type: 'string' }//,
												//{ name: 'Post_ColoredName', type: 'string' }
											],
											sortInfo: {
												field: 'Post_Name'
											}
										}),
										}
		
								*/
						]
						}, {
							autoHeight: true,
							border: false,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							xtype: 'fieldset',
							labelWidth: 250,
							items: [{
								comboSubject: 'OnkoOccupationClass',
								fieldLabel: lang['sotsialno-professionalnaya_gruppa'],
								width: 485,
								typeCode: 'int',
								tabIndex: TABINDEX_PEF + 30,
								editable: true,
								hiddenName: 'OnkoOccupationClass_id',
								xtype: 'swcommonsprcombo'
							}]
						}, {
							id: 'PEW_IdentInErzFieldSet',
							xtype: 'fieldset',
							title: 'Идентификация в '+win.identERZService+' ЕРЗ',
							style: 'padding: 5px; padding-top: 5px; margin: 0; margin-bottom: 5px',
							autoHeight: true,
							items: [{
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										xtype: 'swdatefield',
										name: 'Person_identInErzDT',
										hideLabel: true,
										width: 90
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										id: 'PEW_IdentInErzButton',
										xtype: 'button',
										text: 'Идентифицировать',
										style: 'margin-left: 10px;',
										handler: function() {
											this.identInErz();
										}.createDelegate(this)
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										id: 'PEW_IdentInErzStatus',
										style: 'margin-left: 20px; margin-top: 3px; font-size: 12px;',
										html: 'Статус идентификации: не было запроса в '+win.identERZService,
										tpl: new Ext.XTemplate('Статус идентификации: {IdentStatus}')
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										id: 'PEW_IdentInErzStatusHistoryLink',
										style: 'font-size: 12px;margin-left: 20px;margin-top: 3px;color: #000079;text-decoration: underline;cursor: pointer;',
										html: '<span onclick="getWnd(\'swPersonEditWindow\').openIdentInErzHistoryWindow();">История</span>'
									}]
								}]
							}]
						}, {
							id: 'PEW_IdentInTfomsFieldSet',
							xtype: 'fieldset',
							title: 'Идентификация в ТФОМС',
							style: 'padding: 5px; padding-top: 5px; margin: 0; margin-bottom: 5px',
							autoHeight: true,
							items: [{
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										id: 'PEW_IdentInTfomsButton',
										xtype: 'button',
										text: 'Идентифицировать',
										style: 'margin-left: 10px;',
										handler: function() {
											this.identInTfoms();
										}.createDelegate(this)
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										id: 'PEW_IdentInTfomsStatus',
										style: 'margin-left: 20px; margin-top: 3px; font-size: 12px;',
										html: 'Статус: Не идентифицирован',
										tpl: new Ext.XTemplate('Статус идентификации: {IdentStatus}')
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										id: 'PEW_IdentInTfomsStatusHistoryLink',
										style: 'font-size: 12px;margin-left: 20px;margin-top: 3px;color: #000079;text-decoration: underline;cursor: pointer;',
										html: '<span onclick="getWnd(\'swPersonEditWindow\').openIdentInTfomsHistoryWindow();">История</span>'
									}]
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							style: 'padding: 0; padding-top: 5px; margin: 0',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									readOnly: !getRegionNick().inlist(['ekb']),
									fieldLabel: lang['data_smerti'],
									format: 'd.m.Y',
									name: 'Person_deadDT',
									width: 95,
									xtype: getRegionNick().inlist(['ekb'])?'swdatefield':'textfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									readOnly: true,
									fieldLabel: lang['data_zakryitiya'],
									format: 'd.m.Y',
									name: 'Person_closeDT',
									width: 95,
									xtype: 'textfield'
								}]
							}]
						}]
					}, {
						title: lang['2_dopolnitelno'],
						height: Ext.isIE ? 450 : 430,
						autoScroll:true,
						labelWidth: 160,
						id: 'additional_tab',
						layout:'form',
						items: [{
							xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: lang['predstavitel'],
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px;',
							items: [{
								hiddenName: 'DeputyKind_id',
								tabIndex: TABINDEX_PEF + 31,
								xtype: 'swdeputykindcombo'
							}, {
								editable: false,
								hiddenName: 'DeputyPerson_id',
								tabIndex: TABINDEX_PEF + 32,
								width: 400,
								xtype: 'swpersoncombo',
								onTrigger1Click: function() {
									var ownerWindow = Ext.getCmp('PersonEditWindow');
									if(ownerWindow.readOnly) return;// в режиме просмотра поиск не нужен
									var combo = this;

									var
										autoSearch = false,
										fio = new Array();

									if ( !Ext.isEmpty(combo.getRawValue()) ) {
										fio = combo.getRawValue().split(' ');

										// Запускать поиск автоматически, если заданы хотя бы фамилия и имя
										if ( !Ext.isEmpty(fio[0]) && !Ext.isEmpty(fio[1]) ) {
											autoSearch = true;
										}
									}

									getWnd('swPersonSearchWindow').show({
										autoSearch: autoSearch,
										onSelect: function(personData) {
											if ( personData.Person_id > 0 )
											{
												PersonSurName_SurName = Ext.isEmpty(personData.PersonSurName_SurName)?'':personData.PersonSurName_SurName;
												PersonFirName_FirName = Ext.isEmpty(personData.PersonFirName_FirName)?'':personData.PersonFirName_FirName;
												PersonSecName_SecName = Ext.isEmpty(personData.PersonSecName_SecName)?'':personData.PersonSecName_SecName;

												combo.getStore().loadData([{
													Person_id: personData.Person_id,
													Person_Fio: PersonSurName_SurName + ' ' + PersonFirName_FirName + ' ' + PersonSecName_SecName
												}]);
												combo.setValue(personData.Person_id);
												combo.collapse();
												combo.focus(true, 500);
												combo.fireEvent('change', combo);
											}
											getWnd('swPersonSearchWindow').hide();
										},
										onClose: function() {combo.focus(true, 500)},
										personSurname: !Ext.isEmpty(fio[0]) ? fio[0] : '',
										personFirname: !Ext.isEmpty(fio[1]) ? fio[1] : '',
										personSecname: !Ext.isEmpty(fio[2]) ? fio[2] : ''
									});
								},
								onTrigger2Click: function() {
									this.clearValue();
									win.findById('DocumentDeputy').setVisible(false);
								},
								enableKeyEvents: true,
								listeners: {
									'change': function(combo) {
										win.findById('DocumentDeputy').setVisible(!Ext.isEmpty(combo.getValue()) && getRegionNick() !='kz');
									},
									'keydown': function( inp, e ) {
										if ( e.F4 == e.getKey() )
										{
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
											if ( Ext.isIE )
											{
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											inp.onTrigger1Click();
											return false;
										}
									},
									'keyup': function(inp, e) {
										if ( e.F4 == e.getKey() )
										{
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
											if ( Ext.isIE )
											{
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											return false;
										}
									}
								}
							}]
						},{
							xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: "Документ представителя",
							id: "DocumentDeputy",
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px;',
							items: [{
								xtype: 'swcommonsprcombo',
								comboSubject: 'DocumentAuthority',
								width: 300,
								listWidth: 300,
								tabIndex: TABINDEX_PEF + 30,
								hiddenName: 'DocumentAuthority_id',
								fieldLabel: langs('Тип документа')
							},{
								fieldLabel: lang['seriya'],
								maxLength: 10,
								name: 'DocumentDeputy_Ser',
								tabIndex: TABINDEX_PEF + 31,
								maskRe: new RegExp('[0-9]'),
								width: 130,
								xtype: 'textfield'
							}, {
								fieldLabel: lang['nomer'],
								maxLength: 20,
								name: 'DocumentDeputy_Num',
								tabIndex: TABINDEX_PEF + 32,
								maskRe: new RegExp('[0-9]'),
								width: 130,
								xtype: 'textfield'
							}, {
								fieldLabel: lang['kem_vyidan'],
								name: 'DocumentDeputy_Issue',
								tabIndex: TABINDEX_PEF + 33,
								width: 300,
								xtype: 'textfield'
							}, {
								plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
								format: 'd.m.Y',
								fieldLabel: lang['data_vyidachi'],
								maxValue: getGlobalOptions().date,
								width: 94,
								name: 'DocumentDeputy_begDate',
								tabIndex: TABINDEX_PEF + 34,
								xtype: 'swdatefield'
							}]
						},{
							allowBlank: true,
							fieldLabel: lang['nomer_sots_kartyi'],
							id: 'PEW_PersonSocCardNum_SocCardNum',
							maskRe: /\d/,
							name: 'PersonSocCardNum_SocCardNum',
							autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
							tabIndex: TABINDEX_PEF + 35,
							width: 250,
							maxLength: 30,
							xtype: 'textfield'
						},
						{
							allowBlank: true,
							comboSubject: 'YesNo',
							id: 'PEW_PersonRefuse_IsRefuse',
							hiddenName: 'PersonRefuse_IsRefuse',
							fieldLabel: lang['otkaz_ot_lgotyi'],
							tabIndex: TABINDEX_PEF + 36,
							xtype: 'swcommonsprcombo'
						},
						{
							allowBlank: true,
							comboSubject: 'YesNo',
							id: 'PEW_Person_IsNotINN',
							value: 1,
							hiddenName: 'Person_IsNotINN',
							fieldLabel: langs('Отказ от получения ИНН'),
							tabIndex: TABINDEX_PEF + 37,
							xtype: 'swcommonsprcombo'
						},
						{
						    allowBlank: true,
							fieldLabel: (getRegionNick() == 'kz' ? lang['iin'] : lang['inn']),
							maskRe: /\d/,
							id: 'PEW_PersonInn_Inn',
							name: 'PersonInn_Inn',
							autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
							tabIndex: TABINDEX_PEF + 38,
							width: 150,
							maxLength: 12,
							minLength: 12,
							xtype: 'textfield'
						},/* {
						    hiddenName: 'PersonNationality_id',
							tabIndex: TABINDEX_PEF + 39,
							width: 250,
							xtype: 'swnationalitycombo'
						},*/ {
						    xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: lang['semeynoe_polojenie'],
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px;',
							items: [{
								comboSubject: 'YesNo',
								allowBlank: true,
								tabIndex: TABINDEX_PEF + 40,
								hiddenName: 'PersonFamilyStatus_IsMarried',
								name: 'PersonFamilyStatus_IsMarried',
								fieldLabel: lang['sostoit_v_zaregistrirovannom_brake'],
								xtype: 'swcommonsprcombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.findById('person_edit_form').getForm();
										if ( newValue != 1) {
											base_form.findField('FamilyStatus_id').clearValue();
										}
									}.createDelegate(this)
								}
							}, {
							    hiddenName: 'FamilyStatus_id',
								tabIndex: TABINDEX_PEF + 41,
								width: 250,
								xtype: 'swfamilystatuscombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.findById('person_edit_form').getForm();
										var record = combo.getStore().getById(newValue);

										if ( record ) {
											base_form.findField('PersonFamilyStatus_IsMarried').setValue(1);
										}
									}.createDelegate(this)
								}
							}]
						}, {
						    comboSubject: 'YesNo',
							fieldLabel: lang['est_deti_do_16-ti'],
							hiddenName: 'PersonChildExist_IsChild',
							tabIndex: TABINDEX_PEF + 42,
							xtype: 'swcommonsprcombo'
						},{
						    comboSubject: 'YesNo',
							fieldLabel: lang['est_avtomobil'],
							hiddenName: 'PersonCarExist_IsCar',
							tabIndex: TABINDEX_PEF + 43,
							xtype: 'swcommonsprcombo'
						},{
						    comboSubject: 'Ethnos',
							fieldLabel: lang['etnicheskaya_gruppa'],
							hiddenName: 'Ethnos_id',
							editable: true,
							tabIndex: TABINDEX_PEF + 44,
							typeCode: 'int',
							xtype: 'swcommonsprcombo'
						},
						this.FamilyRelationGrid
						]
					}, {
						height: Ext.isIE ? 450 : 430,
						id: 'spec_tab',
						labelWidth: 180,
						layout:'form',
						autoScroll:true,
						title: lang['3_spetsifika_detstvo'],

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'ResidPlace',
									fieldLabel: lang['mesto_vospitaniya'],
									listWidth: 350,
									tabIndex: TABINDEX_PEF + 45,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}, {
								border: false,
								layout: 'form',
                                hidden: !getRegionNick().inlist(['ufa', 'perm', 'ekb']),
								items: [{
									comboSubject: 'PersonSprTerrDop',
									fieldLabel: lang['rayon_goroda'],
									hiddenName: 'PersonSprTerrDop_id',
									listWidth: 300,
									tabIndex: TABINDEX_PEF + 46,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}]
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							width:749,
							title: lang['semya'],
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: lang['mnogodetnaya'],
										hiddenName: 'PersonChild_IsManyChild',
										tabIndex: TABINDEX_PEF + 47,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									border: false,
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: lang['neblagopoluchnaya'],
										hiddenName: 'PersonChild_IsBad',
										tabIndex: TABINDEX_PEF + 48,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}]
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: lang['nepolnaya'],
										hiddenName: 'PersonChild_IsIncomplete',
										tabIndex: TABINDEX_PEF + 49,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: lang['opekaemaya'],
										hiddenName: 'PersonChild_IsTutor',
										tabIndex: TABINDEX_PEF + 50,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}]
							}, {
								comboSubject: 'YesNo',
								fieldLabel: lang['vyinujdennyie_pereselentsyi'],
								hiddenName: 'PersonChild_IsMigrant',
								tabIndex: TABINDEX_PEF + 51,
								xtype: 'swcommonsprcombo',
								width: 180
							}]
						},
						{
							layout: 'column',
							items: [{
								layout: 'form',
								items: [{
									comboSubject: 'HealthKind',
									fieldLabel: lang['gruppa_zdorovya'],
									tabIndex: TABINDEX_PEF + 52,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}, {
								layout: 'form',
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: lang['yunaya_mat'],
									hiddenName: 'PersonChild_IsYoungMother',
									tabIndex: TABINDEX_PEF + 53,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}]
						}, {
								allowDecimals: false,
								allowNegative: false,
								name: 'PersonChild_CountChild',
								fieldLabel: langs('Который по счету'),
								tabIndex: TABINDEX_PEF + 55,
								xtype: 'numberfield',
								width: 180
							}, {
								title: langs('Способ вскармливания'),
								layout:'form',
								items: [this.PersonFeedingType/*Здесь грид*/]
							}, {
							xtype: 'fieldset',
							autoHeight: true,
							width:749,
							title: lang['invalidnost'],
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: lang['invalidnost'],
								hiddenName: 'PersonChild_IsInvalid',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == newValue);
										});
										combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
									},
									'select': function(combo, record, idx) {
										var base_form = this.findById('person_edit_form').getForm();

										if ( typeof record == 'object' && record.get('YesNo_Code') == 1 ) {
											base_form.findField('PersonChild_invDate').enable();
											base_form.findField('InvalidKind_id').enable();

											if ( getRegionNick() == 'perm' ) {
												base_form.findField('PersonChild_invDate').setAllowBlank(false);
												base_form.findField('InvalidKind_id').setAllowBlank(false);
												base_form.findField('HealthAbnorm_id').setAllowBlank(false);
												base_form.findField('HealthAbnormVital_id').setAllowBlank(false);
												base_form.findField('Diag_id').setAllowBlank(false);
												base_form.findField('ResidPlace_id').setAllowBlank(false);
											}
										}
										else {
											base_form.findField('InvalidKind_id').clearValue();
											base_form.findField('InvalidKind_id').disable();
											base_form.findField('PersonChild_invDate').disable();
											base_form.findField('PersonChild_invDate').setRawValue('');

											if ( getRegionNick() == 'perm' ) {
												base_form.findField('PersonChild_invDate').setAllowBlank(true);
												base_form.findField('InvalidKind_id').setAllowBlank(true);
												base_form.findField('HealthAbnorm_id').setAllowBlank(true);
												base_form.findField('HealthAbnormVital_id').setAllowBlank(true);
												base_form.findField('Diag_id').setAllowBlank(true);
												base_form.findField('ResidPlace_id').setAllowBlank(true);
											}
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_PEF + 56,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										comboSubject: 'InvalidKind',
										fieldLabel: lang['kategoriya'],
										tabIndex: TABINDEX_PEF + 57,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									layout: 'form',
									items: [{
										fieldLabel: lang['data_ustanovki'],
										maxValue: new Date(),
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'PersonChild_invDate',
										tabIndex: TABINDEX_PEF + 58,
										xtype: 'swdatefield'
									}]
								}]
							}, {
								comboSubject: 'HealthAbnorm',
								fieldLabel: lang['glavnoe_narushenie_zdorovya'],
								listWidth: 400,
								tabIndex: TABINDEX_PEF + 59,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								comboSubject: 'HealthAbnormVital',
								fieldLabel: lang['veduschee_ogranichenie_zdorovya'],
								listWidth: 400,
								tabIndex: TABINDEX_PEF + 60,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								fieldLabel: lang['diagnoz'],
								hiddenName: 'Diag_id',
								width: 350,
								tabIndex: TABINDEX_PEF + 61,
								xtype: 'swdiagcombo'
							}]
					},{

							title: lang['otsenka_fizicheskogo_razvitiya'],
							layout:'form',
							items: [this.PersonEval/*Здесь грид*/]
						}]
					}, {
						id: 'newslatter_accept_tab',
						title: lang['4_sms_e-mail_uvedomleniya'],
					 	height: Ext.isIE ? 450 : 430,
						labelWidth: 180,
						layout:'form',
						bodyStyle: 'padding: 0px',
						items: [this.NewslatterAcceptGrid]
					}/*, {
						title: lang['4_pokazateli_sostoyaniya_zdorovya'],
					 	height: Ext.isIE ? 530 : 480,
						labelWidth: 180,
						id: 'zdorov_tab',
						layout:'form',
						bodyStyle: 'padding: 0px',
						items: [this.PersonMeasureGrid]
					}*//*,
					{
						title: lang['2_dopolnitelno'],
						height: 350,
						id: 'additional_tab',
						layout:'form',
						items: [{
							xtype: 'fieldset',
							labelWidth: 100,
							autoHeight: true,
							title: lang['mesto_rabotyi'],
							style: 'padding: 0; padding-top: 5px; margin: 0',
							items: [{
								xtype: 'sworgcombo',
								hiddenName: 'Org_id',
								editable: false,
								triggerAction: 'none',
								anchor: '95%',
								tabIndex: 1021,
								onTrigger1Click: function() {
									var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
									var combo = this;
									getWnd('swOrgSearchWindow').show({
										enableOrgType: true,
										onSelect: function(orgData) {
											if ( orgData.Org_id > 0 )
											{
												combo.getStore().load({
													params: {
														Object:'Org',
														Org_id: orgData.Org_id,
														Org_Name:''
													},
													callback: function()
													{
														combo.setValue(orgData.Org_id);
														combo.focus(true, 500);
														combo.fireEvent('change', combo);
													}
												});
											}
											getWnd('swOrgSearchWindow').hide();
										},
										onClose: function() {combo.focus(true, 200)}
									});
								},
								enableKeyEvents: true,
								listeners: {
									'change': function(combo) {
										combo.ownerCt.findById('PEW_OrgUnion_id').getStore().load({
											params: {
												Object:'OrgUnion',
												OrgUnion_id:'',
												OrgUnion_Name:'',
												Org_id: combo.getValue()
											}
										});
									},
									'keydown': function( inp, e ) {
										if ( e.F4 == e.getKey() )
										{
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
											if ( Ext.isIE )
											{
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											inp.onTrigger1Click();
											return false;
										}
									},
									'keyup': function(inp, e) {
										if ( e.F4 == e.getKey() )
										{
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
											if ( Ext.isIE )
											{
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											return false;
										}
									}
								}
							},
							{
								xtype: 'sworgunioncombo',
								tabIndex: 1022,
								editable: false,
								id: 'PEW_OrgUnion_id',
								hiddenName: 'OrgUnion_id',
								autoLoad: false,
								anchor: '95%'
							},
							{
								xtype: 'swpostcombo',
								minChars: 0,
								queryDelay: 1,
								tabIndex: 1023,
								hiddenName: 'Post_id',
								selectOnFocus: true,
								anchor: '95%',
								forceSelection: false
							}]
						}]
					}*/
				]
				})
				]

					})
					],
					keys: [{
						key: "0123456789",
						alt: true,
						fn: function(e) {Ext.getCmp("pacient_tab_panel").setActiveTab(Ext.getCmp("pacient_tab_panel").items.items[ e - 49 ]);},
						stopEvent: true
					}, {
						alt: true,
						fn: function(inp, e) {
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

							if (e.getKey() == Ext.EventObject.J)
							{
								Ext.getCmp('PersonEditWindow').hide();
								return false;
							}
							if (e.getKey() == Ext.EventObject.C)
							{
								Ext.getCmp('PersonEditWindow').buttons[0].handler();
								return false;
							}

/*							if (e.getKey() == Ext.EventObject.D)
							{
								Ext.getCmp('person_edit_form').buttons[3].handler();
								return false;
							}

							if (e.getKey() == Ext.EventObject.Y)
							{
								Ext.getCmp('person_edit_form').buttons[2].handler();
								return false;
							}
*/
						},
						key: [ Ext.EventObject.C, Ext.EventObject.J, Ext.EventObject.D, Ext.EventObject.Y ],
						scope: this,
						stopEvent: false
					}],
					buttons: [
						{
							text: BTN_FRMSAVE,
							tabIndex: TABINDEX_PEF + 62,
							iconCls: 'save16',
							id: 'PEW_SaveButton',
							handler: function() {
								var me=this;
								var base_form = this.findById('person_edit_form').getForm();
								if (getRegionNick() == 'ekb' && base_form.findField('OMSSprTerr_id').getFieldValue('OMSSprTerr_Code') != '1165' && Ext.isEmpty(base_form.findField('DocumentType_id').getValue())){
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function ( buttonId ) {
											if ( buttonId == 'yes' ) {
												if ( this.subaction && (this.subaction == 'addperiodic' || this.subaction=='editperiodic') )
													this.doSaveNewPeriodicsOnDate();
												else {
													if ( this.personEvnId ){
														this.doCheckAndSaveOnThePersonEvn();
													}else{
														this.citizenshipControl(function(){
															me.doSave();
														});
													}

												}
											}
										}.createDelegate(this),
										msg: lang['ne_ukazana_informatsiya_po_dokumentu_udostoveryayuschemu_lichnost_patsient_mojet_ne_byit_identifitsirovan_prodoljit_sohranenie'],
										title: lang['preduprejdenie']
									});
									return false;
								} else {
									if ( this.subaction && (this.subaction == 'addperiodic' || this.subaction=='editperiodic') )
										this.doSaveNewPeriodicsOnDate();
									else {
										if ( this.personEvnId ){
											this.doCheckAndSaveOnThePersonEvn();
										}
										else{
											this.citizenshipControl(function(){
												me.doSave();
											});
										}
									}
								}
							}.createDelegate(this)
						}/*,
						{
							text: lang['sohranit_na_datu'],
							tabIndex: TABINDEX_PEF + 49,
							iconCls: 'save16',
							id: 'PEW_SaveOnDateButton',
							handler: function() {
								this.ownerCt.ownerCt.doSaveOnDate();
							}
						}*/,
						{
							text: lang['periodiki'],
							hidden: false,
							tabIndex: TABINDEX_PEF + 63,
							id: 'PEW_PeriodicsButton',
							handler: this.showPeriodicViewWindow.createDelegate(this)
						},
						{
							text: (getRegionNick() == 'ekb')?lang['proverka_registratsionnyih_dannyih']:lang['identifikatsiya'],
							hidden: false,
							tabIndex: TABINDEX_PEF + 64,
							id: 'PEW_PersonIdentButton',
							menu:(getRegionNick() != 'astra')?false:[
								{
									name:'identPolis',
									disabled: false,
									text:lang['identifikatsiya_polisnyih_dannyih'],
									tooltip: lang['identifikatsiya_polisnyih_dannyih'],
									/*iconCls : 'update-ward16',*/
									handler: function() {
										this.doPersonIdentRequest();
									}.createDelegate(this)
								},
								{
									name:'identFull',
									disabled: false,
									text:lang['polnaya_identifikatsiya'],
									tooltip: lang['polnaya_identifikatsiya'],
									/*iconCls : 'edit16',*/
									handler: function() {
										this.doPersonIdentRequest(true);
									}.createDelegate(this)
								}
							],
							handler: function() {
								//base_form.findField('PersonPhone_Phone').setValue(data.PersonPhone_Phone);
								(getRegionNick() != 'astra')?this.doPersonIdentRequest():null;
							}.createDelegate(this)
						},
						{
							text: '-'
						},
							HelpButton(this, -1),
						{
							text: BTN_FRMCANCEL,
							tabIndex: TABINDEX_PEF + 65,
							iconCls: 'cancel16',
							handler: this.hide.createDelegate(this, [])
						}
						/*,
						{
							text: lang['nazad'],
							tabIndex: 1026,
							icon: 'extjs/resources/images/default/button/left-arrow.png',
							iconCls: 'x-btn-text-icon',
							handler: function() {
								for (i=0; i<=Ext.getCmp("pacient_tab_panel").items.items.length-1; i++)
									if ( Ext.getCmp("pacient_tab_panel").items.items[i].title == Ext.getCmp("pacient_tab_panel").getActiveTab().title )
										if ( i != 0 )
											Ext.getCmp("pacient_tab_panel").setActiveTab(Ext.getCmp("pacient_tab_panel").items.items[i-1]);
							}
						},
						{
							text: lang['vpered'],
							tabIndex: 1027,
							icon: 'extjs/resources/images/default/button/right-arrow.png',
							iconCls: 'x-btn-text-icon',
							handler: function() {
								for (i=0; i <= Ext.getCmp("pacient_tab_panel").items.items.length - 1; i++)
									if ( Ext.getCmp("pacient_tab_panel").items.items[ i ].title == Ext.getCmp("pacient_tab_panel").getActiveTab().title )
										if ( i != (Ext.getCmp("pacient_tab_panel").items.items.length - 1) )
										{
											Ext.getCmp("pacient_tab_panel").setActiveTab(Ext.getCmp("pacient_tab_panel").items.items[ i + 1 ]);
											return;
										}
							}
						}*/
					]
		});

		sw.Promed.swPersonEditWindow.superclass.initComponent.apply(this, arguments);
	},
	showPeriodicViewWindow: function() {
		getWnd('swPeriodicViewWindow').show({
			Person_id: this.personId,
			Server_id: this.serverId
		});
	},
	citizenshipControl: function(callback){
		//Предупреждение на обязательность заполнения поля «Гражданство»
		var base_form = this.findById('person_edit_form').getForm();
		var citizenship_control = Ext.globalOptions.globals.citizenship_control;
		var KLCountry = base_form.findField('KLCountry_id');
		if(citizenship_control == 2 && KLCountry && !KLCountry.getValue()){
			Ext.MessageBox.show({
				title: 'Внимание!',
				msg: 'Не указано гражданство. Продолжить?',
				buttons: Ext.Msg.YESNO,
				buttonText :
					{
						yes : 'Да',
						no : 'Нет'
					},
				fn: function(butn){
					if (butn == 'yes'){
						callback();
					}else{
						return false;
					}
				}
			});
		}else{
			callback();
		}
	}
});

