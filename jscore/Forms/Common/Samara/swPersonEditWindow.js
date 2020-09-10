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
	action: 'edit',
	title: WND_PERS_EDIT,
	codeRefresh: true,
	objectName: 'swPersonEditWindow',
	objectSrc: '/jscore/Forms/Commons/swPersonEditWindow.js',
	listeners: {
		'hide': function() {this.onClose()}
	},
	disableEdit: function(disable) {
		var form = this.findById('person_edit_form');
		if ( disable === false )
		{
			form.enable();
			this.buttons[0].enable();
		}
		else
		{
			var vals = form.getForm().getValues();
			for ( value in vals )
			{
				form.getForm().findField(value).disable();
				this.buttons[0].disable();
			}
		}
	},
	doPersonIdentRequest: function() {
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
		var sex_code = 0;
		var soc_status_code = 0;
/*
		if ( (polis_num.length > 0 && polis_num.length != 16) || (polis_num.length == 16 && polis_num.length >= 2 && polis_num.substr(0, 2) != '02') ) {
			sw.swMsg.alert('Ошибка', 'Идентификация недоступна! Причина: неверный номер полиса.', function() { form.buttons[2].focus(); }.createDelegate(this) );
			return false;
		}
*/
/*
		// https://redmine.swan.perm.ru/issues/11587
		// 1) Разрешить идентификацию в случае, если территория страхования НЕ Башкортостан. 
		record = base_form.findField('OMSSprTerr_id').getStore().getById(base_form.findField('OMSSprTerr_id').getValue());
		if ( record && record.get('OMSSprTerr_Code') != 61 ) {
			sw.swMsg.alert('Ошибка', 'Идентификация недоступна! Причина: иная территория страхования.', function() {this.buttons[2].focus();}.createDelegate(this) );
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
			sex_code = record.get('Sex_Code');
		}

		record = base_form.findField('SocStatus_id').getStore().getById(base_form.findField('SocStatus_id').getValue());
		if ( record ) {
			soc_status_code = record.get('SocStatus_Code');
		}

		var params = {
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
			Person_Snils: base_form.findField('Person_SNILS').getValue(),
			Polis_Ser: base_form.findField('Polis_Ser').getValue(),
			Polis_Num: polis_num,
			Sex_Code: sex_code,
			SocStatus_Code: soc_status_code,
			UAddress_Flat: base_form.findField('UAddress_Flat').getValue(),
			UAddress_House: base_form.findField('UAddress_House').getValue(),
			Person_IsBDZ: (base_form.findField('Server_pid').getValue() == 0)?1:0
		};

		win.getLoadMask('Выполняется запрос на идентификацию человека...').show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					// Если человек идентифицирован...
					if ( response_obj.PersonIdentState_id && response_obj.PersonIdentState_id.toString().inlist(['-1', '1', '2', '3']) ) {
						// Идентификация не требуется
						if ( response_obj.PersonIdentState_id == '-1' ) {
							return false;
						}

						// Полис пациента недействителен...
						if ( response_obj.PersonIdentState_id == '3' ) {
							// ... выводим сообщение
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								fn: function ( buttonId ) {
									// Если ответ "Да"...
									if ( buttonId == 'yes' ) {
										// ... полученные по идентификации данные подставляются в поля формы, заменяя предыдущие данные и
										// дополняя недостающие. Автоматически происходит изменение статуса пациента на «Не работает».
										// (Сделано для того, чтобы посещение попало в реестр по месту жительства пациента) 
										response_obj.CATEG = 5;
										this.setFieldsOnIdent(response_obj, false);
									}
								}.createDelegate(this),
								msg: 'У пациента нет действующего полиса. Заменить данные?',
								title: 'Предупреждение'
							});

							return false;
						}
						else {
							this.setFieldsOnIdent(response_obj, true);

							if ( response_obj.Alert_Msg && response_obj.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert('Ошибка', response_obj.Alert_Msg, function() {this.buttons[2].focus();}.createDelegate(this) );
							}
						}
					}
					// Если задано сообщение об ошибке...
					else if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
						// ... выводим сообщение об ошибке
						sw.swMsg.alert('Ошибка', response_obj.Error_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
					}
					// Иначе...
					else {
						// ... выводим сообщение об ошибке
						sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса на идентификацию человека', function() {this.buttons[2].focus();}.createDelegate(this) );
					}
				}
				else {
					sw.swMsg.alert('Ошибка', 'Ошибка при выполнении запроса на идентификацию человека', function() {this.buttons[2].focus();}.createDelegate(this) );
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=PersonIdentRequest&m=doPersonIdentRequest'
		});

		return true;
	},
	addToOldValuesForIdentification: '',
	setFieldsOnIdent: function(data, disableFields) {
		var base_form = this.findById('person_edit_form').getForm();
		// Устанавливаем дату актуальности данных в сводной базе застрахованных
		base_form.findField('Person_identDT').setValue(data.Person_identDT);
		base_form.findField('PersonIdentState_id').setValue(data.PersonIdentState_id);
		// Добавляем старые поля полиса и фио, если они были задисаблены.
		this.oldValues = this.oldValues + this.addToOldValuesForIdentification;
		
		if ( data.PersonIdentState_id != 2 ) {
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
			
			if (getRegionNick() == 'kareliya') {
				// Территория страхования - Карелия
				terr_combo.getStore().each(function(rec) {
					if ( rec.get('OMSSprTerr_Code') == 1 ) {
						terr_combo.setValue(rec.get('OMSSprTerr_id'));
						terr_combo.fireEvent('change', terr_combo, rec.get('OMSSprTerr_id'));
					}
				});
			}
			

			// Тип полиса - ОМС
			base_form.findField('PolisType_id').getStore().each(function(rec) {
				if ( rec.get('PolisType_Code') == 1 ) {
					base_form.findField('PolisType_id').setValue(rec.get('PolisType_id'));
					base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), rec.get('PolisType_id'));
				}
			});

			// Фамилия
			base_form.findField('Person_SurName').setValue(data.FAM ? data.FAM : '');

			// Имя
			base_form.findField('Person_FirName').setValue(data.NAM ? data.NAM : '');

			// Отчество
			base_form.findField('Person_SecName').setValue(data.FNAM ? data.FNAM : '');

			// Дата рождения
			base_form.findField('Person_BirthDay').setValue(data.BORN_DATE ? getValidDT(data.BORN_DATE, '') : '');

			// Серия полиса
			if ( data.POL_SER ) {
				base_form.findField('Polis_Ser').setValue(data.POL_SER);
				
				if (getRegionNick() == 'kareliya') {
					if ( data.POL_SER == 'ЕНП' ) {
						// ЕНП
						base_form.findField('Polis_Ser').setValue('');
						base_form.findField('Polis_Ser').setAllowBlank(true);
						base_form.findField('PolisType_id').setValue(4);
						base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
					} else if ( !isNaN(parseInt(data.POL_SER)) && isFinite(data.POL_SER) ) {
						// временное, серия - цифры
						base_form.findField('Polis_Ser').setAllowBlank(false);
						base_form.findField('PolisType_id').setValue(3);
						base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
					} else {
						// отсальные - ОМС старого образца
						base_form.findField('Polis_Ser').setAllowBlank(false);
						base_form.findField('PolisType_id').setValue(1);
						base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
					}
				}
			}
			
			// Номер полиса
			if ( data.POL_NUM_16 ) {
				if ( getRegionNick() == 'kareliya' && data.POL_SER && data.POL_SER == 'ЕНП' ) {
					base_form.findField('Polis_Num').setValue(data.POL_NUM_16);
					base_form.findField('Federal_Num').setValue(data.POL_NUM_16);
				} else if (getRegionNick() == 'kareliya') {
					base_form.findField('Polis_Num').setValue(data.POL_NUM_16);
					base_form.findField('Federal_Num').setValue('');
				} else {
					base_form.findField('Polis_Num').setValue(data.POL_NUM_16);
				}
				
				if (getRegionNick() == 'ufa') {
					if ( data.POL_NUM_16.length > 0 ) {
						var pbd = base_form.findField('Person_BirthDay').getValue();

						if ( data.POL_NUM_16.length == 9 ) {
							base_form.findField('PolisType_id').setValue(3);
							base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
						}
						else if ( data.POL_NUM_16.length == 16 && data.POL_NUM_16.substr(3, 6) == Ext.util.Format.date((typeof pbd == 'object' ? pbd : getValidDT(pbd, '')), 'Ym') ) {
							base_form.findField('PolisType_id').setValue(1);
							base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
						}
						else {
							base_form.findField('PolisType_id').setValue(4);
							base_form.findField('PolisType_id').fireEvent('change', base_form.findField('PolisType_id'), base_form.findField('PolisType_id').getValue());
						}
					}
				}
			}

			// Дата выдачи полиса
			if ( data.GIV_DATE ) {
				base_form.findField('Polis_begDate').setValue(data.GIV_DATE);
			}

			// Дата окончания действия полиса
			if ( data.ELIMIN_DATE ) {
				base_form.findField('Polis_endDate').setValue(data.ELIMIN_DATE);
			}

			// ИНН
			if ( data.INN ) {
				base_form.findField('PersonInn_Inn').setValue(data.INN);
			}

			// СНИЛС
			if ( data.SNILS ) {
				base_form.findField('Person_SNILS').setValue(data.SNILS);
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
			if ( data.CATEG ) {
				base_form.findField('SocStatus_id').getStore().each(function(rec) {
					if ( rec.get('SocStatus_Code') == data.CATEG ) {
						base_form.findField('SocStatus_id').setValue(rec.get('SocStatus_id'));
					}
				});
			}

			// СМО
			if ( data.OrgSmo_id ) {
				base_form.findField('OrgSMO_id').getStore().each(function(rec) {
					if ( rec.get('OrgSMO_id') == data.OrgSmo_id ) {
						base_form.findField('OrgSMO_id').setValue(rec.get('OrgSMO_id'));
					}
				});
			}

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
				sw.swMsg.alert('Предупреждение', data.Alert_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
			}

			// Блокируем поля от дальнейшего изменения данных пользователем
			if ( base_form.findField('PersonIdentState_id').getValue().toString().inlist(['1', '3']) && !getGlobalOptions().superadmin ) {
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
					base_form.findField('UAddress_AddressText').disable();
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
			base_form.findField('Polis_Num').enable();
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
			base_form.findField('Document_Ser').enable();
			base_form.findField('Document_Num').enable();
			base_form.findField('UAddress_AddressText').enable();
		}
		
		// надо закрыть поля полиса, если не выбрана территория! (refs #16852)
		if (Ext.isEmpty(base_form.findField('OMSSprTerr_id').getValue())) {
			this.disablePolisFields(true, true);
		}

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
		params.Person_IsUnknown = base_form.findField('Person_IsUnknown').checked ? 2 : 1;

		//var oms_spr_terr_record = base_form.findField('OMSSprTerr_id').getStore().getById(base_form.findField('OMSSprTerr_id').getValue());

		//if ( oms_spr_terr_record && Number(oms_spr_terr_record.get('OMSSprTerr_Code')) > 100 ) {
			params.OMSSprTerr_id = base_form.findField('OMSSprTerr_id').getValue();
	    //}

		win.getLoadMask('Подождите, идет проверка двойников...').show();

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
									title: 'Проверка дубля по серии и номеру полиса',
									msg: 'Серия и номер полиса совпадают с данными полиса другого человека. Открыть его на редактирование?',
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
									'Проверка ИНН',
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
									'Ошибка',
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
								this.doSubmit();
							}
						}
					}
				}
			}.createDelegate(this)
		});
	},
	periodicFields: [
		'Person_SurName',
		'Person_SecName',
		'Person_FirName',
		'Person_BirthDay',
		'PersonPhone_Phone',
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
		'Polis': [
			'OMSSprTerr_id',
			'PolisType_id',
			'Polis_Ser',
			'Polis_Num',			
			'OrgSMO_id',
			'Polis_begDate',
			'Polis_endDate'
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
			'NationalityStatus_IsTwoNation'
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
			'PAddress_Address'
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
			'Person_Comment'
		],
		'PersonChild': [
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
			switch (getGlobalOptions().region.nick)
			{
				case 'ufa':
					if (SecName.length = 0)
					{
						notice.push("<BR />- Вы не ввели Отчество человека");
						break;
					}
					//отключить проверку на пол, если отчество не задано или стоит НЕТ. (refs #7075)
					if ((SecName.length != 0) && (SecName != 'НЕТ')) {
					if ((SecName.substr(SecName.length-1,1).toLowerCase() == 'а') || (SecName.substr(SecName.length-4,4).toLowerCase() == 'кызы'))
						isWomen = true;
					else
						isMen = true;
					if (isWomen && Sex_id == 1)
						sex_error = true;
					if (isMen && Sex_id == 2)
						sex_error = true;
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
						if (SecName.substr(SecName.length-1,1).toLowerCase() == 'а')
							isWomen = true;
						else
							isMen = true;
					}
					else
					{
						if (SurName.substr(SurName.length-1,1).toLowerCase() == 'а')
							isWomen = true;
						else
							isMen = true;
					}
					if (isWomen && Sex_id == 1)
						sex_error = true;
					if (isMen && Sex_id == 2)
						sex_error = true;
					if (sex_error)
						notice.push("<BR />- Возможно, вы неправильно выбрали пол человека");
			}
		}
		// проверка Отсутствует территория страхования
		if ( is_ufa && !base_form.findField('OMSSprTerr_id').getValue())
		{
			notice.push("<BR />- Вы не ввели данные страхового полиса");
		}
		// проверка Не указан ни один из документов
		if ( is_ufa && !base_form.findField('DocumentType_id').getValue())
		{
			notice.push("<BR />- Вы не ввели документ");
		}
		// Не проставлена страховая организация
		if ( is_ufa && !base_form.findField('OrgSMO_id').getValue())
		{
			notice.push("<BR />- Не проставлена страховая организация");
		}
		return notice;
	},
	// Изменение условий проверки документа в зависимости от территории полиса
	'changeDocVerificationDependingOMSTerr': function(doc_id) {
		if (!doc_id)
			return false;
		// Для Перми при выборе типа документа "Свидетельство о рождении"
		// для всех у кого полис не Пермского Края устанавливаем особые правила проверки серии и номера св-ва о рождении
		if (getRegionNick() == 'perm' && doc_id == 3)
		{
			var base_form = this.findById('person_edit_form').getForm();
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
				Ext.getCmp('PEW_Document_Ser').allowBlank = false;
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
						title: 'Предупреждение',
						msg: 'Обнаружены возможные ошибки: ' + notice.toString() + '<BR />Подтверждаете сохранение?',
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
		
		if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
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
		}
		
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

			var params = saving_data;
			params.Person_id = this.personId;
			params.Server_id = this.serverId;
			params.PersonEvn_id = this.personEvnId;
			params.EvnType = changed_fields.filter(isPeriodicField).join('|');
			params.NotEvnType = changed_fields.filter(isNotPeriodicField).join('|');
			params.refresh = true;

			win.getLoadMask('Подождите, идёт сохранение...').show();
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
										UAddress_AddressText: form.findField('UAddress_AddressText').getValue(),
										PAddress_AddressText: form.findField('PAddress_AddressText').getValue(),
										Person_Age: swGetPersonAge(form.findField('Person_BirthDay').getValue(), new Date()),
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
			if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
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
			}
			var notice = this.validationFormWithRegion();
			if (notice)
			{
				if (notice.length > 0)
					sw.swMsg.show({
						title: 'Предупреждение',
						msg: 'Обнаружены возможные ошибки: ' + notice.toString() + '<BR />Подтверждаете сохранение?',
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
					if ( this.periodicSingleFields.in_array(changed_fields[0]) )
						params[changed_fields[0]] = base_form.getValues()[changed_fields[0]];
					else
					{
						for ( var i = 0; i < this.periodicStructFields[changed_fields[0]].length; i++ )
						{
							params[this.periodicStructFields[changed_fields[0]][i]] = base_form.getValues()[this.periodicStructFields[changed_fields[0]][i]];
						}
					}
					win.getLoadMask('Подождите, идёт сохранение...').show().show();
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
												'Ошибка',
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
					title: "Ошибка",
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
				if ( this.periodicSingleFields.in_array(attribute) )
					params[attribute] = base_form.getValues()[attribute];
				else
				{
					for ( var i = 0; i < this.periodicStructFields[attribute].length; i++ )
					{
						params[this.periodicStructFields[attribute][i]] = base_form.getValues()[this.periodicStructFields[attribute][i]];
					}
				}
				win.getLoadMask('Подождите, идёт сохранение...').show();
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
											'Ошибка',
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
	doSaveNewPeriodicsOnDate: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		
		if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
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
		}
		
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
			// проверка возраста и заполненности имени
			if ( base_form.findField('Person_BirthDay').getValue() && base_form.findField('Person_BirthDay').getValue().getMonthsBetween(new Date()) > 2 && base_form.findField('Person_FirName').getValue() == '' && !base_form.findField('Person_IsUnknown').getValue() )
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
			}
				
			var notice = this.validationFormWithRegion();
			if (notice)
			{
				if (notice.length > 0)
					sw.swMsg.show({
						title: 'Предупреждение',
						msg: 'Обнаружены возможные ошибки: ' + notice.toString() + '<BR />Подтверждаете сохранение?',
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
	checkPersonSnils: function()
	{
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
	doSubmit: function() {
		if ( this.readOnly )
			return;
		var base_form = this.findById('person_edit_form').getForm();
		var win = this;
		var oldValues = this.oldValues;
		var act = this.action;
		var post = {oldValues: oldValues, mode: act, Server_id: win.serverId};
		var form = this.findById('person_edit_form').getForm();
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
				post.PostNew = form.findField('Post_id').getRawValue().replace(/\-+|\++|\.+|\,+/ig,'').replace(/\s{2,}/ig,' '); //TODO: Filter Post_id  Уничтожаем ненужные символы
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
				post.OrgUnionNew = form.findField('OrgUnion_id').getRawValue().replace(/\-+|\++|\.+|\,+/ig,'').replace(/\s{2,}/ig,' '); //TODO: Filter OrgUnion
				form.findField('OrgUnion_id').setValue('');
			}
		}

		if ( base_form.findField('PersonIdentState_id').getValue().toString().inlist(['1', '3']) && !getGlobalOptions().superadmin ) {
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
			post.Document_Num = base_form.findField('KLCountry_id').getValue();
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
		
		win.getLoadMask('Подождите, идёт сохранение...').show();
		this.findById('person_edit_form').getForm().submit(
		{
			params: post,
			timeout: 1800000,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
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
						Person_Work: base_form.findField('Org_id').getFieldValue('Org_Nick')
					}
				});
			},
			failure: function (form, action)
			{
				win.getLoadMask().hide();
				//Ext.Msg.alert("Ошибка", action.result.msg);
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
	checkChildrenDuplicates:function(params){
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();
		var params = new Object();
		
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
									text: 'Отменить ввод',
									tooltip: 'Сброс данных и закрытые формы добавления человека'
								},
								no: {
									text: 'Продолжить ввод',
									tooltip: 'Возврат к добавлению человека'
								}
							};
							var msgbox = sw.swMsg.show({
								buttons: buttons,
								msg: answer[0].child.warning,
								title: 'Внимание!',
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
			base_form.findField('Polis_endDate').disable();
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
				base_form.findField('PolisType_id').setValue(1);
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
			form.getForm().findField('KLCountry_id').disable();
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
	show: function() {
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();
		var form = this.findById('person_edit_form');
		this.childAdd = false;
		this.personId = 0;
		this.readOnly = false;
		this.serverId = 0;

		base_form.findField('Person_IsUnknown').setContainerVisible(false);

		this.minBirtDay = null;

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

			if ( arguments[0].onClose && typeof arguments[0].onClose == 'function' )
				this.onClose = arguments[0].onClose;
			else if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' )
				this.onClose = arguments[0].onHide;

			if ( arguments[0].Person_id )
				this.personId = arguments[0].Person_id;
				
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

			if ( arguments[0].allowUnknownPerson ) {
				base_form.findField('Person_IsUnknown').setContainerVisible(true);
			}
		}

		if ( getGlobalOptions().region && (getGlobalOptions().region.nick == 'ufa' || getGlobalOptions().region.nick == 'kareliya') ) {
			this.buttons[2].show()
		}
		else {
			this.buttons[2].hide();
		}

		/*if (this.personId && this.PersonMeasureGrid)
			this.PersonMeasureGrid.setParam('Person_id', this.personId, false);*/
		if (!getGlobalOptions().region.nick.inlist(['samara'])) {
			this.findById('pacient_tab_panel').hideTabStripItem('lgoti_tab'); 
		} else {
			this.findById('pacient_tab_panel').unhideTabStripItem('lgoti_tab'); 
		}
					    
		sw.Promed.swPersonEditWindow.superclass.show.apply(this, arguments);

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
		
		if ( this.action != 'edit' )
		{
			//Ext.getCmp('PEW_SaveOnDateButton').disable();
			Ext.getCmp('PEW_PeriodicsButton').disable();
		}
		else
		{
			//Ext.getCmp('PEW_SaveOnDateButton').enable();
			Ext.getCmp('PEW_PeriodicsButton').enable();
		}
			
		if (this.action == 'edit' )
			if (!this.readOnly)
			{
				this.setTitle(WND_PERS_EDIT);
			}
			else
			{
				this.setTitle(WND_PERS_VIEW);
			}
			
		this.findById('pacient_tab_panel').setActiveTab(3);
		this.findById('pacient_tab_panel').setActiveTab(2);
		this.findById('pacient_tab_panel').setActiveTab(1);
		this.findById('pacient_tab_panel').setActiveTab(0);
		this.disablePolisFields(true);
		this.disableDocumentFields(true);
		
		if ( this.subaction == 'editperiodic' )
		{
			this.setTitle('Человек: редактирование периодики');
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
			win.getLoadMask('Пожалуйста, подождите, идет загрузка данных формы...').show();
		}

		base_form.reset();
		
		base_form.findField('Polis_CanAdded').setValue(0);
		var terr_combo = base_form.findField('OMSSprTerr_id');
		var terr_id = terr_combo.getValue();
		terr_combo.getStore().filterBy(function(record) {
				return true;			
		});
		terr_combo.baseFilterFn = function(record) {
				return true;
		}
		
		base_form.findField('PersonSex_id').setValue('');
		
		base_form.findField('Person_deadDT').container.up('div.x-form-item').hide();
		base_form.findField('Person_closeDT').container.up('div.x-form-item').hide();

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
				person_id: this.personId,
				server_id: this.serverId
			};
			if ( !this.personEvnId )
				var url = '/?c=Person&m=getPersonEditWindow';
			else
			{
				var url = '/?c=Person&m=getPersonEvnEditWindow';
				params.PersonEvn_id = this.personEvnId;
			}
			base_form.load({
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert('Ошибка', 'Не удалось загрузить данные с сервера', function() {this.hide();}.createDelegate(this));
				}.createDelegate(this),
				params: params,
				success: function(fm) {
					win.checkOkeiRequired();

					if (fm.findField('Person_IsUnknown').getValue()) {
						fm.findField('Person_IsUnknown').setContainerVisible(true);
					}
					fm.findField('Person_IsUnknown').fireEvent('check', fm.findField('Person_IsUnknown'), fm.findField('Person_IsUnknown').getValue());

					if (fm.findField('PersonFamilyStatus_IsMarried').getValue() != null )
						base_form.findField('PersonFamilyStatus_IsMarried').setAllowBlank(false);
						
					// показываем закрытие записи или смерть
					if ( fm.findField('Person_deadDT').getValue() != '' )
						base_form.findField('Person_deadDT').container.up('div.x-form-item').show();
					
					if ( fm.findField('Person_closeDT').getValue() != '' )
						base_form.findField('Person_closeDT').container.up('div.x-form-item').show();
					
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
					if ( inlist('SuperAdmin', servers_ids) )
						servers_mask_arr = Array("0", "0", "0");
					if ( inlist(3, servers_ids) )
						servers_mask_arr = Array("0", "0", "0");
					var mask = servers_mask_arr.join("");
					
					// Если федеральный льготник и уфа (или астра)
					if (getRegionNick().inlist(['ufa','astra','msk','samara']))
					{
						// Если Уфа (или астра, московская область, самара), то редактирование разрешено всем
						mask = "000";
						if (getGlobalOptions().region.nick.inlist(['ufa']) && (fm.findField('Person_IsFedLgot').getValue()==1) && (!isAdmin))
						{
							// кроме определенных полей, если это федеральный льготник (в уфе)
							mask = "ufafed";
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
						case "010":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
							fm.findField('Person_SNILS').disable();
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
							fm.findField('Person_SNILS').disable();
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
							fm.findField('Person_SNILS').disable();	
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
							//fm.findField('SocStatus_id').disable();
							fm.findField('Person_SNILS').disable();
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
					if ( base_form.findField('OMSSprTerr_id').getValue() > 0 && !inlist(mask, Array('100', '110', '101', '111'))  )
						form.ownerCt.disablePolisFields(false);
						
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
								base_form.findField('Polis_Num').minLength = 16;
								base_form.findField('Polis_Num').maxLength = 16;
								// return; - для чего это о_О убрал.
							}
							else
							{							
								base_form.findField('Polis_Num').clearInvalid();
								if ( getRegionNick() == 'ufa' && klrgn_id == 2 )
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
									base_form.findField('Polis_Num').minLength = 0;
									base_form.findField('Polis_Num').maxLength = 18;
								}
							}
							if ( code <= 61 ) 
							{
								base_form.findField('Polis_Ser').disableTransPlug = false;
								// Если не уфа и полис не нового образца 
								if ( !(getRegionNick() == 'ufa') &&  (base_form.findField('PolisType_id').getFieldValue('PolisType_Code')!=4) ) {
									// то серия полиса обязательна для ввода
									base_form.findField('Polis_Ser').setAllowBlank(false);
								}
								else
								{
									// иначе же нет
									base_form.findField('Polis_Ser').disableTransPlug = true;
									base_form.findField('Polis_Ser').setAllowBlank(true);
								}
							}
							else
							{
								base_form.findField('Polis_Ser').disableTransPlug = true;
								base_form.findField('Polis_Ser').setAllowBlank(true);
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
							OrgSMOCombo.setValue(OrgSMOCombo.getValue());
						}
					}
					
					// если есть признак того, что можем добавлять полис, то фильтруем список территорий другие регионы + текущая
					// но если это суперадмин, то нет
					if ( base_form.findField('Polis_CanAdded').getValue() == 1 && mask != '000' )
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

					if (base_form.findField('KLCountry_id').getFieldValue('KLCountry_Code') == 643) {
						base_form.findField('NationalityStatus_IsTwoNation').enable();
					} else {
						base_form.findField('NationalityStatus_IsTwoNation').disable();
						base_form.findField('NationalityStatus_IsTwoNation').setValue(false);
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
					if ( getGlobalOptions().region.nick == 'ufa' ){
						var doc_type_field = base_form.findField('DocumentType_id');
					 	if (!doc_type_field.getValue()){
					 		var doc_type_storage = new sw.Promed.LocalStorage({
					 		tableName: 'DocumentType',
					 		typeCode: 'int',
					 		loadParams: {params: {where: " where DocumentType_Code = 14"}},
					 		onLoadStore: function(){
					 			var doc_type_record = doc_type_storage.getFirstRecord();
					 			doc_type_field.fireEvent('select',doc_type_field, doc_type_record);
					 			doc_type_field.fireEvent('blur',doc_type_field);
					 		}
					 		});
					 		doc_type_storage.load();
					 	}
					}
					this.PersonPrivilegesGridReload();
				}.createDelegate(this),
				url: url
			});
			
		}

		if ( arguments[0].fields ) {
			
			var ss = arguments[0].fields.SocStatus;
			if(ss=='babyborn'){
				this.childAdd=true;
				this.minBirtDay = arguments[0].fields.Person_BirthDay;
				this.findById('person_edit_form').getForm().setValues(arguments[0].fields);
				base_form.findField('SocStatus_id').setFieldValue('SocStatus_SysNick','nrab')
				
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
		}

		//base_form.clearInvalid();
		base_form.findField('Polis_Ser').disableTransPlug = false;
		if ( !(getRegionNick() == 'ufa') ) {
			base_form.findField('Polis_Ser').setAllowBlank(false);
		}
		else
		{
			base_form.findField('Polis_Ser').disableTransPlug = true;
		}
		if ( getGlobalOptions().region.nick == 'ufa' )
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
			sex_store.remove( sex_store.getAt(2) );

			// при добавлении в поле Тип документа по умолчанию устанавливаем Паспорт гражданина РФ
			var doc_type_field = base_form.findField('DocumentType_id');
			if (this.action == 'add')
			{
				if(arguments[0].fields&&arguments[0].fields.SocStatus=="babyborn"){}else{
					var doc_type_storage = new sw.Promed.LocalStorage({
						tableName: 'DocumentType',
						typeCode: 'int',
						loadParams: {params: {where: " where DocumentType_Code = 14"}},
						onLoadStore: function(){
							var doc_type_record = doc_type_storage.getFirstRecord();
							doc_type_field.fireEvent('select',doc_type_field, doc_type_record);
							doc_type_field.fireEvent('blur',doc_type_field);
						}
					});
					doc_type_storage.load();
				}
			}
		}
        base_form.findField('PersonInfo_InternetPhone').disable();
	},
	initComponent: function() {
		var win = this;
		this.PersonEval = new sw.Promed.ViewFrame({
			id: 'PersonEval',			
			border: true,
			autoLoadData: false,
			height: 200,
			region: 'center',
			editformclassname: 'swPersonEvalEditWindow',
			dataUrl: '/?c=Person&m=loadPersonEval',
			actions:
			[
				{name:'action_add', handler: function() {getWnd('swPersonEvalEditWindow').show({Person_id:win.personId,action:'add'})}.createDelegate(this)},
				{name:'action_view',hidden:true},
				{name:'action_delete'} // Вроде никаких дополнительных действий не планируется 
			],
			stringfields:
			[
				{name: 'PersonEval_id', type: 'string', header: 'ID', key: true},
				{name: 'EvalType', type: 'string',  header: 'Показатель',width:105},
				{name: 'PersonEval_setDT', type: 'date', format: 'd.m.Y',width:125, header: 'Дата измерения'},
				{name: 'EvalMeasureType', type: 'string', header:'Вид замера',width:125},				
				{name: 'PersonEval_value', type: 'int', header: 'Значение',width:125},
				{name: 'PersonEval_isAbnorm', type: 'string', header:'Отклонение',width:100},
				{name: 'EvalAbnormType', type:'string',header:'Тип отклонения',width:150}
			],
			params: {
				callback: function(options, success, response) {
					if(success){log(success)}
					win.PersonEval.refreshRecords(null,0)
					return true;					
				}
			},
			deleteRecord: function() { // удаление измерения из таблицы
				sw.swMsg.show({
					buttons: sw.swMsg.YESNO,
					fn: function(buttonId, text, obj) {
						if ('yes' == buttonId) {
							var measure_grid = this.getGrid();
							var selected_record = measure_grid.getSelectionModel().getSelected();
							var params ={}
							params.PersonEval_id = selected_record.get('PersonEval_id').substr(6);
							params.EvalType = selected_record.get('PersonEval_id').substr(0,6);
							Ext.Ajax.request({callback: function(options, success, response) {
								if(success){	
		

							if (!measure_grid.getSelectionModel().getSelected()) {
								return false;
							}
							
							measure_grid.getStore().remove(selected_record);
							if (measure_grid.getStore().getCount() == 0) {
								measure_grid.getTopToolbar().items.items[1].disable();
								measure_grid.getTopToolbar().items.items[2].disable();
								measure_grid.getTopToolbar().items.items[3].disable();
							} else {
								measure_grid.getView().focusRow(0);
								measure_grid.getSelectionModel().selectFirstRow();
							}	
								}		
							}.createDelegate(this),
							params:params,
							url:'/?c=Person&m=deletePersonEval'
						})
			
							

						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: 'Удалить показатель измерения?',
					title: 'Вопрос'
				})
			}
		});
        this.PersonPrivilegesGrid = new sw.Promed.ViewFrame({
		    id: 'PEW_PersonPrivilegesGrid',
		    border: true,
		    autoLoadData: false,
		    //focusOnFirstLoad: false,
		    height: Ext.isIE ? 530 : 480,
		    region: 'center',
		    object: 'PersonPrivileges',
		    dataUrl: '/?c=Privilege&m=loadPersonPrivilegesList',
		    paging: false,
		    pageSize: 20,
		    stringfields:
		    [
		    	{ name: 'PersonPrivilege_id', type: 'int', header: '' },	//это поле не отображается и похоже что по нему идет группировка, хз почему
		    	{ name: 'PrivilegeType_Name', type: 'string', header: 'Льгота', width: 400 },
		    	{ name: 'PersonPrivilege_Serie', type: 'string', header: 'Серия', width: 150 },
		    	{ name: 'PersonPrivilege_Number', type: 'string', header: 'Номер', width: 150 }
		    ],
		    params: {
		        callback: function (data, add_flag) {
		            alert();
		        }
		    },
		    actions:
                [{
                    name: 'action_add',
                    handler: function () {
		    	        //this.PersonPrivilegesWindow.show();
                        getWnd('swPersonPrivilegesWindow').show({
                            action: 'add',
                            Person_id: win.findById('person_edit_form').getForm().findField('Person_id').getValue(),
                            callback: function () {
                                win.PersonPrivilegesGridReload();
                            }
                        });
                    }.createDelegate(this)
		        }, {
		            name: 'action_delete', handler: function () {
		    	        var selected_record = this.PersonPrivilegesGrid.getGrid().getSelectionModel().getSelected();
		    	        Ext.Ajax.request({
		    	            url: '/?c=Privilege&m=deletePrivilegeSamara',
		    	            params: { PersonPrivilege_id: selected_record.id },
		    	            success: function () {
		    	                this.PersonPrivilegesGridReload();
		    	            }.createDelegate(this)
		    	        });

		    	    }.createDelegate(this)
		    	}, {
		    	    name: 'action_edit',
		    	    handler: function () {
		    	        getWnd('swPersonPrivilegesWindow').show({
		    	            action: 'edit',
		    	            PersonPrivilege_id: win.findById('PEW_PersonPrivilegesGrid').ViewGridPanel.getSelectionModel().getSelected().id,
		    	            Person_id: win.findById('person_edit_form').getForm().findField('Person_id').getValue(),
		    	            callback: function () {
		    	                win.PersonPrivilegesGridReload();
		    	            }
		    	        });
		    	    }.createDelegate(this)
		    	},
		    	{ name: 'action_refresh', hidden: true },
		    	{ name: 'action_print', hidden: true },
		    	{ name: 'action_view', hidden: true }
		    ]
		});

        this.PersonPrivilegesGridReload = function () {
            this.PersonPrivilegesGrid.removeAll();
            this.PersonPrivilegesGrid.loadData({
                globalFilters: {
                    limit: 100,
                    start: 0,
                    person_id: this.personId
                }
            });
        }

		this.PersonPrivilegesGridReload();

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
				{name: 'date', type: 'date', format: 'd.m.Y', header: 'Дата проведения'},
				{name: 'lpusection_name', type: 'string', header: 'Отделение ЛПУ', width:300},				
				{name: 'medpersonal_fio', type: 'string', header: 'Врач', width:300},
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
					msg: 'Удалить измерение показателей здоровья?',
					title: 'Вопрос'
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
					{name: 'Person_SurName'},
					{name: 'Person_SecName'},
					{name: 'Person_FirName'},
					{name: 'Person_BirthDay'},
					{name: 'Person_SNILS'},
					{name: 'AttachLpu_id'}, // Petrov Pavel
					{name: 'Person_IsUnknown'},
					{name: 'PersonSex_id'},
					{name: 'PersonSocCardNum_SocCardNum'},
					{name: 'PersonPhone_Phone'},
					{name: 'PersonInfo_InternetPhone'},
					{name: 'FamilyStatus_id'},
					{name: 'PersonFamilyStatus_IsMarried'},
					{name: 'PersonInn_Inn'},
					//{name: 'PersonNationality_id'},
					{name: 'PersonRefuse_IsRefuse'},
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
					{name: 'Document_Ser'},
					{name: 'Document_Num'},
					{name: 'DocumentType_id'},
					{name: 'OrgDep_id'},
					{name: 'Document_begDate'},
					{name: 'KLCountry_id'},
					{name: 'NationalityStatus_IsTwoNation'},
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
					{name: 'Person_IsUnknown'}
					
				]),
				items: [{
					xtype: 'hidden',
					name: 'DeputyPerson_Fio'
				}, {
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					xtype: 'hidden',
					name: 'Server_pid',
					id: 'server_id'
				}, {
					xtype: 'hidden',
					name: 'action',
					value: 'save'
				}, {
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
					name: 'Person_IsFedLgot'
				},{
					xtype: 'hidden',
					name: 'Polis_CanAdded'
				},{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 70,
						items: [{
							allowBlank: false,
							fieldLabel: 'Фамилия',
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
							maskRe: /[a-zA-Zа-яА-ЯёЁ\-\s\,\[\]\;\']/,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							allowBlank: true,
							fieldLabel: 'Имя',
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
							maskRe: /[a-zA-Zа-яА-ЯёЁ\-\s\,\[\]\;\']/,
							width: 180,
							xtype: 'textfieldpmw'
						}, {
							xtype: 'textfieldpmw',
							fieldLabel: 'Отчество',
							listeners: {
								blur: function (inp) {
									inp.setValue(inp.getValue().trim());
								}
							},
							toUpperCase: true,
							maskRe: /[a-zA-Zа-яА-ЯёЁ\-\s\,\[\]\;\']/,
							width: 180,
							name: 'Person_SecName',
							tabIndex: TABINDEX_PEF + 2
						}]
					}, {
						layout: 'form',
						labelWidth: 180,
						items: [{
							allowBlank: false,
							fieldLabel: 'Дата рождения',
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
						}, {
							allowBlank: false,
							comboSubject: 'Sex',
							fieldLabel: 'Пол',
							hiddenName: 'PersonSex_id',
							tabIndex: TABINDEX_PEF + 4,
							width: 130,
							xtype: 'swcommonsprcombo'
						}, {
							allowBlank: true,
							fieldLabel: 'Номер телеф.',
							id: 'PEW_PersonPhone_Phone',
							name: 'PersonPhone_Phone',
							tabIndex: TABINDEX_PEF + 5,
							width: 180,
							xtype: 'textfield'
						}, {
                            fieldLabel: 'Номер телеф. с сайта записи',
                            id: 'PEW_PersonInfo_InternetPhone',
                            name: 'PersonInfo_InternetPhone',
                            width: 180,
                            xtype: 'textfield'
                        }, {
							layout: 'form',
							items: [{
								labelSeparator: '',
								boxLabel: 'Личность неизвестна',
								name: 'Person_IsUnknown',
								tabIndex: TABINDEX_PEF + 4,
								xtype: 'checkbox',
								listeners: {
									'check': function(field, value) {
										var base_form = this.findById('person_edit_form').getForm();

										base_form.findField('Person_BirthDay').setAllowBlank(value);
										base_form.findField('PersonSex_id').setAllowBlank(value);
										base_form.findField('SocStatus_id').setAllowBlank(value);

										var surname_field = base_form.findField('Person_SurName');
										if (value && Ext.isEmpty(surname_field.getValue())) {
											surname_field.setValue('НЕИЗВЕСТЕН');
										}
									}.createDelegate(this)
								}
							}]
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
						height: Ext.isIE ? 530 : 480,
						id: 'pacient_tab',
						layout:'form',
						title: '1. Пациент',
						items: [{
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							xtype: 'fieldset',
							items: [{
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										fieldLabel: 'СНИЛС',
										hiddenName: 'Person_SNILS',
										name: 'Person_SNILS',
										tabIndex: TABINDEX_PEF + 6,
										fieldWidth: 180,
										xtype: 'ocsnilsfield'
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items: [{
										allowBlank: false,
										autoLoad: false,
										codeField: 'SocStatus_Code',
										comboSubject: 'SocStatus',
										allowSysNick:true,
										editable: true,
										lastQuery: '',
										listeners: {
											'render': function(combo) {
												combo.getStore().load();
											}
										},
										fieldLabel: 'Соц. статус',
										tabIndex: TABINDEX_PEF + 7,
										width: 310,
										xtype: 'swcommonsprcombo'
				                    }, {
				                        fieldLabel: 'ЛПУ прикр.',
				                        hiddenName: 'AttachLpu_id',
				                        xtype: 'oclpusearch',
				                        tabIndex: TABINDEX_PEF + 8
									}]
								}]
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: 'Адрес',
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
							}, {
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
											fieldLabel: 'Адрес регистрации',
											id: 'PEW_UAddress_AddressText',
											name: 'UAddress_AddressText',
											readOnly: true,
											tabIndex: TABINDEX_PEF + 8,
											trigger1Class: 'x-form-search-trigger',
											trigger2Class: 'x-form-equil-trigger',
											trigger3Class: 'x-form-clear-trigger',
											width: 600,

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
															ownerForm.findById('PEW_UAddress_Address').setValue(values.Address_AddressEdit);
															ownerForm.findById('PEW_UAddress_AddressText').setValue(values.Address_AddressEdit);
															//ownerForm.findById('PEW_UAddress_begDate').setValue(Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y'));
															ownerForm.findById('PEW_UAddress_AddressText').focus(true, 500);
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
							}, {
								xtype: 'hidden',
								name: 'PAddress_Address',
								id: 'PEW_PAddress_Address'
							}, {
								layout: 'column',
								items: [{
										layout: 'form',
										items: [
											new sw.Promed.TripleTriggerField ({
												enableKeyEvents: true,
												fieldLabel: 'Адрес проживания',
												id: 'PEW_PAddress_AddressText',
												name: 'PAddress_AddressText',
												readOnly: true,
												tabIndex: TABINDEX_PEF + 9,
												trigger1Class: 'x-form-search-trigger',
												trigger2Class: 'x-form-equil-trigger',
												trigger3Class: 'x-form-clear-trigger',
												width: 600,

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
													ownerForm.findById('PEW_PAddress_Address').setValue(ownerForm.findById('PEW_UAddress_Address').getValue());
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
															ownerForm.findById('PEW_PAddress_Address').setValue(values.Address_AddressEdit);
															ownerForm.findById('PEW_PAddress_AddressText').setValue(values.Address_AddressEdit);
															//ownerForm.findById('PEW_PAddress_begDate').setValue(Ext.util.Format.date(values.Address_begDateEdit, 'd.m.Y'));
															ownerForm.findById('PEW_PAddress_AddressText').focus(true, 500);
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
							}, {
								xtype: 'hidden',
								name: 'BAddress_Address',
								id: 'PEW_BAddress_Address'
							},
							new sw.Promed.TripleTriggerField ({
								enableKeyEvents: true,
								fieldLabel: 'Адрес рождения',
								id: 'PEW_BAddress_AddressText',
								name: 'BAddress_AddressText',
								readOnly: true,
								tabIndex: TABINDEX_PEF + 10,
								trigger1Class: 'x-form-search-trigger',
								trigger2Class: 'x-form-equil-trigger',
								trigger3Class: 'x-form-clear-trigger',
								width: 595,

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
										ownerForm.findById('PEW_BAddress_Address').setValue(ownerForm.findById('PEW_PAddress_Address').getValue());
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
							title: 'Полис',
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
													base_form.findField('Polis_Num').minLength = 16;
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
														
													}
													else
													{
														base_form.findField('Polis_Num').minLength = 0;
														base_form.findField('Polis_Num').maxLength = 18;
													}
													
													if ( code <= 61 )  {
														base_form.findField('Polis_Ser').disableTransPlug = false;
														// Если не уфа и полис не нового образца 
														if ( !(getRegionNick() == 'ufa') &&  (base_form.findField('PolisType_id').getFieldValue('PolisType_Code')!=4) ) {
															// то серия полиса обязательна для ввода
															base_form.findField('Polis_Ser').setAllowBlank(false);
														}
														else
														{
															// иначе же нет
															base_form.findField('Polis_Ser').disableTransPlug = true;
															base_form.findField('Polis_Ser').setAllowBlank(true);
														}
													}
													else {
														base_form.findField('Polis_Ser').disableTransPlug = true;
														base_form.findField('Polis_Ser').setAllowBlank(true);
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
												}
											}.createDelegate(this)
										},
										onTrigger2Click: function() {
											this.findById('person_edit_form').getForm().findField('OMSSprTerr_id').clearValue();
											this.disablePolisFields(true);
										}.createDelegate(this),
										tabIndex: TABINDEX_PEF + 11,
										width: 300,
										xtype: 'swomssprterrcombo'
									}]
								}, {
									labelWidth: 109,
									layout: 'form',

									items: [{
										allowBlank: false,
										comboSubject: 'PolisType',
										fieldLabel: 'Тип',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												var base_form = this.findById('person_edit_form').getForm();
												
												if (newValue == 4 && getRegionNick() == 'perm') {
													base_form.findField('Federal_Num').setAllowBlank(false);
												} else {
													base_form.findField('Federal_Num').setAllowBlank(true);
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
										width: 180,
										xtype: 'swcommonsprcombo'
									}]
								}]
							}, {
								layout: 'column',
								items: [{
									layout: 'form',
									items: [{
										allowBlank: (getRegionNick() == 'ufa'),
										fieldLabel: 'Серия',
										maxLength: 10,
										name: 'Polis_Ser',
										plugins: [ new Ext.ux.translit(true, true) ],
										tabIndex: TABINDEX_PEF + 13,
										width: 100,
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									labelWidth: 68,
									items:[{
										allowBlank: true,
										xtype: 'textfield',
										//maskRe: (getRegionNick() == 'ufa') ? /\d/ : null,
										maskRe: /\d/,
										//allowNegative: false,
										//allowDecimals: false,
										maxLength:  (getRegionNick() == 'ufa') ? 16 : 18,
										minLength: (getRegionNick() == 'ufa') ? 16 : 0,
										autoCreate: (getRegionNick() == 'ufa') ? {tag: "input", type: "text", size: "16", maxLength: "16", autocomplete: "off"} : {tag: "input", type: "text", size: "20", autocomplete: "off"},
										width: 160,
										fieldLabel: 'Номер',
										name: 'Polis_Num',
										tabIndex: TABINDEX_PEF + 14
									}]
								}, {
									layout: 'form',
									labelWidth: 84,
									items: [{
										xtype: 'textfield',
										allowNegative: false,
										allowDecimals: false,
										maskRe: /\d/,
										maxLength: 16,
										minLength: 16,
										autoCreate: {tag: "input", type: "text", size: "16", maxLength: "16", autocomplete: "off"},
										width: 160,
										fieldLabel: 'Ед. номер',
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
									labelWidth: 112,
									items: [{
										allowBlank: false,
										fieldLabel: 'Дата выдачи',
										format: 'd.m.Y',
										name: 'Polis_begDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_PEF + 17,
										xtype: 'swdatefield'
									}]
								}, {
									layout: 'form',
									labelWidth: 111,
									items: [{
										allowBlank: true,
										fieldLabel: 'Дата закрытия',
										format: 'd.m.Y',
										name: 'Polis_endDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_PEF + 18,
										xtype: 'swdatefield'
									}]
								}]
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: 'Документ',
							xtype: 'fieldset',

							items: [{
								border: false,
								layout: 'column',

								items: [{
									layout: 'form',
									items: [{
										fieldLabel: 'Тип',
										listeners: {
											'select': function(combo, record, index) {
												var base_form = this.findById('person_edit_form').getForm();
												if(record != undefined){
													if (record.get('DocumentType_MaskSer')) {
														Ext.getCmp('PEW_Document_Ser').regex = new RegExp( record.get('DocumentType_MaskSer') );
													}
													if (record.get('DocumentType_MaskNum'))
													{
														Ext.getCmp('PEW_Document_Num').regex = new RegExp( record.get('DocumentType_MaskNum') );
													}

													this.changeDocVerificationDependingOMSTerr(record.get('DocumentType_id'));
												}
											}.createDelegate(this),
											'blur': function(combo) {
												var form = this.findById('person_edit_form');
												if ( !combo.getValue() )
												{
													this.disableDocumentFields(true);
													form.getForm().findField('Document_Ser').allowBlank = true;
													form.getForm().findField('Document_Num').allowBlank = true;
													form.getForm().findField('Document_Ser').clearInvalid();
													form.getForm().findField('Document_Num').clearInvalid();
												}
												else
												{
													this.disableDocumentFields(false);
													form.getForm().findField('Document_Ser').allowBlank = false;
													form.getForm().findField('Document_Num').allowBlank = false;
												}
												this.changeDocVerificationDependingOMSTerr(combo.getValue());
											}.createDelegate(this),
											'change': function(combo, newValue, oldValue) {
												var base_form = this.findById('person_edit_form').getForm();
												var c_combo = base_form.findField('KLCountry_id');
												if ( !combo.getValue() )
												{
													base_form.findField('KLCountry_id').setAllowBlank(true);
												}
												else
												{
													if (combo.getFieldValue('DocumentType_Code') == 22) {
														c_combo.clearValue();
														c_combo.disable();
														c_combo.setAllowBlank(true);
													} else {
														c_combo.enable();
														c_combo.setAllowBlank(false);

														if (Number(combo.getFieldValue('DocumentType_Code')).inlist([9,10,11,12,21,23,24,25,26])) {
															//c_combo.clearValue();
														} else {
															c_combo.setFieldValue('KLCountry_Code', 643);
														}
													}
												}
												var c_index = c_combo.getStore().indexOfId(c_combo.getValue());
												var c_record = c_combo.getStore().getAt(c_index);
												base_form.findField('KLCountry_id').fireEvent('select', c_combo, c_record, c_index);
											}.createDelegate(this)
										},
										onTrigger2Click: function() {
											this.findById('').getForm().findField('DocumentType_id').clearValue();
											this.disableDocumentFields(true);
										}.createDelegate(this),
										listWidth: 400,
										tabIndex: TABINDEX_PEF + 19,
										width: 177,
										xtype: 'swdocumenttypecombo'
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items:[{
										fieldLabel: 'Серия',
										maxLength: 10,
										name: 'Document_Ser',
										tabIndex: TABINDEX_PEF + 20,
										width: 94,
										xtype: 'textfield',
										id: 'PEW_Document_Ser'
									}]
								}, {
									layout: 'form',
									labelWidth: 79,
									items:[{
										fieldLabel: 'Номер',
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
//										validateOnBlur: false,
//										validationEvent: false,
										xtype: 'sworgdepcombo'
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items:[{
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										format: 'd.m.Y',
										fieldLabel: 'Дата выдачи',
										width: 94,
										name: 'Document_begDate'
									}]
								}]
							}]
						}, {
							autoHeight: true,
							labelWidth: 125,
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
							title: 'Гражданство',
							xtype: 'fieldset',

							items: [{
								border: false,
								layout: 'column',

								items: [{
									layout: 'form',
									items: [{
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'swklcountrycombo',
										fieldLabel: 'Гражданство',
										width: 180,
										id: 'label0001',
										hiddenName: 'KLCountry_id',
										listeners: {
											'select': function(combo, record, index) {
												var base_form = this.findById('person_edit_form').getForm();

												if (record && record.get('KLCountry_Code') == 643) {
													base_form.findField('NationalityStatus_IsTwoNation').enable();
												} else {
													base_form.findField('NationalityStatus_IsTwoNation').disable();
													base_form.findField('NationalityStatus_IsTwoNation').setValue(false);
												}
											}.createDelegate(this),
											'change': function(combo, newValue, oldValue) {
												var index = combo.getStore().indexOfId(newValue);
												var record = combo.getStore().getAt(index);
												combo.fireEvent('select', combo, record, index);
											}
										}
									}]
								}, {
									layout: 'form',
									autoHeight: true,
									labelWidth: 24,
									items: [{
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'checkbox',
										height: 35,
										style: 'overflow: hidden',
										boxLabel: 'Гражданин Российской Федерации и иностранного государства (двойное гражданство)',
										labelSeparator: '',
										name: 'NationalityStatus_IsTwoNation',
										width: 435
									}]
								}]
							}]
						}, {
							autoHeight: true,
							labelWidth: 125,
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px',
							title: 'Место работы',
							xtype: 'fieldset',

							items: [{
//								xtype: 'sworgcombo',
//								hiddenName: 'Org_id',
//								editable: false,
//								fieldLabel: 'Место работы/учебы',
//								triggerAction: 'none',
//								anchor: '95%',
//								tabIndex: TABINDEX_PEF + 24,
//								onTrigger1Click: function() {
//									var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
//									var combo = this;
//
//									getWnd('swOrgSearchWindow').show({
//										enableOrgType: true,
//										onSelect: function(orgData) {
//											if ( orgData.Org_id > 0 ) {
//												combo.getStore().load({
//													params: {
//														Object:'Org',
//														Org_id: orgData.Org_id,
//														Org_Name:''
//													},
//													callback: function() {
//														combo.setValue(orgData.Org_id);
//														combo.focus(true, 500);
//														combo.fireEvent('change', combo);
//													}
//												});
//											}
//
//											getWnd('swOrgSearchWindow').hide();
//										},
//										onClose: function() {combo.focus(true, 200)}
//									});
//								},
//								enableKeyEvents: true,
//								listeners: {
//									'change': function(combo) {
//										combo.ownerCt.findById('PEW_OrgUnion_id').clearValue();
//										combo.ownerCt.findById('PEW_OrgUnion_id').getStore().load({
//											params: {
//												Object:'OrgUnion',
//												OrgUnion_id:'',
//												OrgUnion_Name:'',
//												Org_id: combo.getValue()
//											}
//										});
//									},
//									'keydown': function( inp, e ) {
//										if ( e.F4 == e.getKey() ) {
//											if ( e.browserEvent.stopPropagation )
//												e.browserEvent.stopPropagation();
//											else
//												e.browserEvent.cancelBubble = true;
//
//											if ( e.browserEvent.preventDefault )
//												e.browserEvent.preventDefault();
//											else
//												e.browserEvent.returnValue = false;
//											e.browserEvent.returnValue = false;
//											e.returnValue = false;
//
//											if ( Ext.isIE ) {
//												e.browserEvent.keyCode = 0;
//												e.browserEvent.which = 0;
//											}
//											inp.onTrigger1Click();
//											return false;
//										}
//									},
//									'keyup': function(inp, e) {
//										if ( e.F4 == e.getKey() )
//										{
//											if ( e.browserEvent.stopPropagation )
//												e.browserEvent.stopPropagation();
//											else
//												e.browserEvent.cancelBubble = true;
//											if ( e.browserEvent.preventDefault )
//												e.browserEvent.preventDefault();
//											else
//												e.browserEvent.returnValue = false;
//											e.browserEvent.returnValue = false;
//											e.returnValue = false;
//											if ( Ext.isIE )
//											{
//												e.browserEvent.keyCode = 0;
//												e.browserEvent.which = 0;
//											}
//											return false;
//										}
//									}
//								}
//						    }/*, {
						    fieldLabel: 'Место работы/учебы',
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
							            var index = combo.getStore().findBy(function(rec) { return rec.get('Org_id') == orgData.Org_id; });

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
							})
						},{
								id: 'PEW_OrgUnion_id',
								hiddenName: 'OrgUnion_id',
								xtype: 'sworgunioncombo',
								minChars: 0,
								queryDelay: 1,
								tabIndex: TABINDEX_PEF + 25,
								selectOnFocus: true,
								anchor: '95%',
								forceSelection: false
							},  {
//								xtype: 'swpostcombo',
//								minChars: 0,
//								queryDelay: 1,
//								tabIndex: TABINDEX_PEF + 26,
//								hidden: false,
//								hideLabel: false,
//								hiddenName: 'Post_id',
//								fieldLabel: 'Должность',
//								selectOnFocus: true,
//								anchor: '95%',
//								forceSelection: false
//							}
						
						
                                fieldLabel: 'Должность',
	                            xtype: 'combo',
	                            anchor: '95%',
	                            editable: true,
	                            hiddenName: 'Post_id',
	                            displayField: 'Post_Name',
	                            valueField: 'Post_id',
	                            enableKeyEvents: true,
	                            minChars: 3,
	                            mode: 'remote',
	                            tabIndex: TABINDEX_PEF + 26,
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
	                            })
							}

						
						]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							xtype: 'fieldset',
							labelWidth: 250,
							items: [{
								comboSubject: 'OnkoOccupationClass',
								fieldLabel: 'Социально-профессиональная группа',
								anchor: '95%',
								typeCode: 'int',
								tabIndex: TABINDEX_PEF + 27,
								editable: true,
								hiddenName: 'OnkoOccupationClass_id',
								xtype: 'swcommonsprcombo'
							}]
						}, {
							allowBlank: true,
							readOnly: true,
							fieldLabel: 'Дата смерти',
							format: 'd.m.Y',
							name: 'Person_deadDT',
							width: 95,
							xtype: 'textfield'
						}, {
							allowBlank: true,
							readOnly: true,
							fieldLabel: 'Дата закрытия',
							format: 'd.m.Y',
							name: 'Person_closeDT',
							width: 95,
							xtype: 'textfield'
						}]
					}, {
						title: '2. Дополнительно',
						height: Ext.isIE ? 530 : 480,
						labelWidth: 160,
						id: 'additional_tab',
						layout:'form',
						items: [{
							xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: 'Представитель',
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px;',
							items: [{
								hiddenName: 'DeputyKind_id',
								tabIndex: TABINDEX_PEF + 28,
								xtype: 'swdeputykindcombo'
							}, {
								editable: false,
								hiddenName: 'DeputyPerson_id',
								tabIndex: TABINDEX_PEF + 29,
								width: 400,
								xtype: 'swpersoncombo',
								onTrigger1Click: function() {
									var ownerWindow = Ext.getCmp('PersonEditWindow');
									var combo = this;
									getWnd('swPersonSearchWindow').show({
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
										onClose: function() {combo.focus(true, 500)}
									});
								},
								enableKeyEvents: true,
								listeners: {
									'change': function(combo) {
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
						},
						{
							allowBlank: true,
							fieldLabel: 'Номер соц. карты',
							id: 'PEW_PersonSocCardNum_SocCardNum',
							maskRe: /\d/,
							name: 'PersonSocCardNum_SocCardNum',
							autoCreate: {tag: "input", type: "text", size: "30", maxLength: "30", autocomplete: "off"},
							tabIndex: TABINDEX_PEF + 30,
							width: 250,
							maxLength: 30,
							xtype: 'textfield'
						},
						{
							allowBlank: true,
							comboSubject: 'YesNo',
							id: 'PEW_PersonRefuse_IsRefuse',
							hiddenName: 'PersonRefuse_IsRefuse',
							fieldLabel: 'Отказ от льготы',
							tabIndex: TABINDEX_PEF + 31,
							xtype: 'swcommonsprcombo'
						},
						{
						    allowBlank: true,
							fieldLabel: 'ИНН',
							maskRe: /\d/,
							id: 'PEW_PersonInn_Inn',
							name: 'PersonInn_Inn',
							autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
							tabIndex: TABINDEX_PEF + 32,
							width: 150,
							maxLength: 12,
							minLength: 12,
							xtype: 'textfield'
						},/* {
						    hiddenName: 'PersonNationality_id',
							tabIndex: TABINDEX_PEF + 33,
							width: 250,
							xtype: 'swnationalitycombo'
						}, */{
						    xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: 'Семейное положение',
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px;',
							items: [{
								comboSubject: 'YesNo',
								allowBlank: true,
								tabIndex: TABINDEX_PEF + 34,
								hiddenName: 'PersonFamilyStatus_IsMarried',
								name: 'PersonFamilyStatus_IsMarried',
								fieldLabel: 'Состоит в зарегистрированном браке',
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
								tabIndex: TABINDEX_PEF + 35,
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
							fieldLabel: 'Есть дети до 16-ти',
							hiddenName: 'PersonChildExist_IsChild',
							tabIndex: TABINDEX_PEF + 36,
							xtype: 'swcommonsprcombo'
						},{
						    comboSubject: 'YesNo',
							fieldLabel: 'Есть автомобиль',
							hiddenName: 'PersonCarExist_IsCar',
							tabIndex: TABINDEX_PEF + 37,
							xtype: 'swcommonsprcombo'
						},{
						    comboSubject: 'Ethnos',
							fieldLabel: 'Этническая группа',
							hiddenName: 'Ethnos_id',
							editable: true,
							tabIndex: TABINDEX_PEF + 38,
							typeCode: 'int',
							xtype: 'swcommonsprcombo'
						}]
					}, {
						height: Ext.isIE ? 530 : 480,
						id: 'spec_tab',
						labelWidth: 180,
						layout:'form',
						autoScroll:true,
						title: '3. Специфика. Детство.',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'ResidPlace',
									fieldLabel: 'Место воспитания',
									listWidth: 350,
									tabIndex: TABINDEX_PEF + 39,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'PersonSprTerrDop',
									fieldLabel: 'Район города',
									hiddenName: 'PersonSprTerrDop_id',
									listWidth: 300,
									tabIndex: TABINDEX_PEF + 40,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}]
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							width:749,
							title: 'Семья',
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: 'Многодетная',
										hiddenName: 'PersonChild_IsManyChild',
										tabIndex: TABINDEX_PEF + 41,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									border: false,
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: 'Неблагополучная',
										hiddenName: 'PersonChild_IsBad',
										tabIndex: TABINDEX_PEF + 42,
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
										fieldLabel: 'Неполная',
										hiddenName: 'PersonChild_IsIncomplete',
										tabIndex: TABINDEX_PEF + 43,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: 'Опекаемая',
										hiddenName: 'PersonChild_IsTutor',
										tabIndex: TABINDEX_PEF + 44,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}]
							}, {
								comboSubject: 'YesNo',
								fieldLabel: 'Вынужденные переселенцы',
								hiddenName: 'PersonChild_IsMigrant',
								tabIndex: TABINDEX_PEF + 45,
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
									fieldLabel: 'Группа здоровья',
									tabIndex: TABINDEX_PEF + 46,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}, {
								layout: 'form',
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: 'Юная мать',
									hiddenName: 'PersonChild_IsYoungMother',
									tabIndex: TABINDEX_PEF + 47,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}]
						}, {
							comboSubject: 'FeedingType',
							fieldLabel: 'Способ вскармливания',
							tabIndex: TABINDEX_PEF + 55,
							xtype: 'swcommonsprcombo',
							width: 180
						}, {
							allowDecimals: false,
							allowNegative: false,
							name: 'PersonChild_CountChild',
							fieldLabel: lang['kotoryiy_po_schetu'],
							tabIndex: TABINDEX_PEF + 55,
							xtype: 'numberfield',
							width: 180
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							width:749,
							title: 'Инвалидность',
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: 'Инвалидность',
								hiddenName: 'PersonChild_IsInvalid',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var base_form = this.findById('person_edit_form').getForm();

										var record = combo.getStore().getById(newValue);

										if ( record && record.get('YesNo_Code') == 1 ) {
											base_form.findField('PersonChild_invDate').enable();
											base_form.findField('InvalidKind_id').enable();
										}
										else {
											base_form.findField('InvalidKind_id').clearValue();
											base_form.findField('InvalidKind_id').disable();
											base_form.findField('PersonChild_invDate').disable();
											base_form.findField('PersonChild_invDate').setRawValue('');
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
										fieldLabel: 'Категория',
										tabIndex: TABINDEX_PEF + 57,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									layout: 'form',
									items: [{
										fieldLabel: 'Дата установки',
										maxValue: new Date(),
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'PersonChild_invDate',
										tabIndex: TABINDEX_PEF + 58,
										xtype: 'swdatefield'
									}]
								}]
							}, {
								comboSubject: 'HealthAbnorm',
								fieldLabel: 'Главное нарушение здоровья',
								listWidth: 400,
								tabIndex: TABINDEX_PEF + 59,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								comboSubject: 'HealthAbnormVital',
								fieldLabel: 'Ведущее ограничение здоровья',
								listWidth: 400,
								tabIndex: TABINDEX_PEF + 60,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								fieldLabel: 'Диагноз',
								hiddenName: 'Diag_id',
								width: 350,
								tabIndex: TABINDEX_PEF + 61,
								xtype: 'swdiagcombo'
							}]
					},{
							
							title: 'Оценка физического развития',
							layout:'form',
							items: [this.PersonEval/*Здесь грид*/]
						}]
					}, /*{
						title: '4. Показатели состояния здоровья',
						height: Ext.isIE ? 500 : 450,
						labelWidth: 180,
						id: 'zdorov_tab',
						layout:'form',
						bodyStyle: 'padding: 0px',
						items: [this.PersonMeasureGrid]
					},*/ {
					    title: '4. Льготы',
						height: Ext.isIE ? 530 : 480,
					    labelWidth: 180,
					    id: 'lgoti_tab',
					    layout: 'form',
					    bodyStyle: 'padding: 0px',
						items: [this.PersonPrivilegesGrid] 
					}/*,
					{
						title: '2. Дополнительно',
						height: 350,
						id: 'additional_tab',
						layout:'form',
						items: [{
							xtype: 'fieldset',
							labelWidth: 100,
							autoHeight: true,
							title: 'Место работы',
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
								if ( this.subaction && (this.subaction == 'addperiodic' || this.subaction=='editperiodic') )
									this.doSaveNewPeriodicsOnDate()
								else {
									if ( this.personEvnId )
										this.doCheckAndSaveOnThePersonEvn();
									else
										this.doSave();
								}
							}.createDelegate(this)
						}/*,
						{
							text: 'Сохранить на дату',
							tabIndex: TABINDEX_PEF + 49,
							iconCls: 'save16',
							id: 'PEW_SaveOnDateButton',
							handler: function() {
								this.ownerCt.ownerCt.doSaveOnDate();
							}
						}*/,
						{
							text: 'Периодики',
							hidden: false,
							tabIndex: TABINDEX_PEF + 63,
							id: 'PEW_PeriodicsButton',
							handler: this.showPeriodicViewWindow.createDelegate(this)
						},
						{
							text: 'Идентификация',
							hidden: false,
							tabIndex: TABINDEX_PEF + 64,
							id: 'PEW_PersonIdentButton',
							handler: function() {
								this.doPersonIdentRequest();
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
							text: '<u>Н</u>азад',
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
							text: '<u>В</u>перед',
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
		mythis = this;
		sw.Promed.swPersonEditWindow.superclass.initComponent.apply(this, arguments);
	},
	showPeriodicViewWindow: function() {		
		getWnd('swPeriodicViewWindow').show({
			Person_id: this.personId,
			Server_id: this.serverId
		});
	}
});