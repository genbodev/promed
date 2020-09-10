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
			this.buttons[0].show();

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

				this.PersonFeedingType.setActionHidden('action_add',true);
				this.PersonFeedingType.setActionHidden('action_edit',true);
				this.PersonFeedingType.setActionHidden('action_delete',true);
			}
		}
	},
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
									text: langs('Отменить ввод'),
									tooltip: langs('Сброс данных и закрытые формы добавления человека')
								},
								no: {
									text: langs('Продолжить ввод'),
									tooltip: langs('Возврат к добавлению человека')
								}
							};
							var msgbox = sw.swMsg.show({
								buttons: buttons,
								msg: answer[0].child.warning,
								title: langs('Внимание'),
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
	doPersonIdentRequest: function(callback) {
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
		var sex_code = 0;
		var soc_status_code = 0;

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
			DocumentType_Code: document_type_code,
			KLArea_id: klarea_id,
			KLStreet_id: klstreet_id,
			Person_Birthday: Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y'),
			Person_Firname: base_form.findField('Person_FirName').getValue(),
			Person_id: person_id,
			Person_Inn: base_form.findField('PersonInn_Inn').getValue(),
			Person_Secname: base_form.findField('Person_SecName').getValue(),
			Person_Surname: base_form.findField('Person_SurName').getValue(),
			Sex_Code: sex_code,
			SocStatus_Code: soc_status_code,
			UAddress_Flat: base_form.findField('UAddress_Flat').getValue(),
			UAddress_House: base_form.findField('UAddress_House').getValue(),
			Person_IsBDZ: (base_form.findField('Server_pid').getValue() == 0)?1:0
		};

		win.getLoadMask(langs('Выполняется запрос на идентификацию человека...')).show();

		Ext.Ajax.request({
			callback: callback ? callback.createDelegate(this) : function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					// Если человек идентифицирован...
					if ( response_obj.Person_IsInErz || response_obj.Person_IsInErz === null ) {
						if ( response_obj.Person_IsInErz == 2 ) {
							this.setFieldsOnIdent(response_obj, true);

							if ( response_obj.Alert_Msg && response_obj.Alert_Msg.toString().length > 0 ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Alert_Msg, function() {this.buttons[2].focus();}.createDelegate(this) );
							}
							showSysMsg(langs('Пациент идентифицирован'));
						} else {
							showSysMsg(langs('Пациент не идентифицирован'));
						}
					}
					// Если задано сообщение об ошибке...
					else if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
						// ... выводим сообщение об ошибке
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
					}
					// Иначе...
					else {
						// ... выводим сообщение об ошибке
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса на идентификацию человека'), function() {this.buttons[2].focus();}.createDelegate(this) );
					}
					
					this.doFomsRequest(params);
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=PersonIdentRequest&m=doPersonIdentRequest'
		});

		return true;
	},
	doFomsRequest: function(params) {
		var win = this;
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();

		// может появиться или измениться после идентифкации
		params.Person_Inn = base_form.findField('PersonInn_Inn').getValue(); 
		
		win.getLoadMask(langs('Выполняется запрос о статусе застрахованности...')).show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Person_IsInFOMS || response_obj.Person_IsInFOMS === null ) {
						if ( response_obj.Person_IsInFOMS == 2 ) {
							showSysMsg(langs('Пациент застрахован'));
						} else {
							showSysMsg(langs('Пациент не застрахован'));
						}
					}
					// Если задано сообщение об ошибке...
					else if ( response_obj.Error_Msg && response_obj.Error_Msg.toString().length > 0 ) {
						// ... выводим сообщение об ошибке
						sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
					}
					// Иначе...
					else {
						// ... выводим сообщение об ошибке
						sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при выполнении запроса о статусе застрахованности'), function() {this.buttons[2].focus();}.createDelegate(this) );
					}
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=FOMS&m=doRequest'
		});
	},
	addToOldValuesForIdentification: '',
	setAddress: function(AddressData, prefix) {
		var base_form = this.findById('person_edit_form').getForm();
		var fields = [
			'KLCountry_id','KLRGN_id','KLSubRGN_id','KLCity_id','KLTown_id','KLStreet_id',
			'Address_House','Address_Corpus','Address_Flat','Address_Address','Address_AddressText',
			'KLRgnSocr_id','KLSubRgnSocr_id','KLCitySocr_id','KLTownSocr_id','KLStreetSocr_id'
		];

		for(var i=0; i<fields.length; i++) {
			var fieldName = prefix+fields[i];
			if (base_form.findField(fieldName)) {
				var value = AddressData[fieldName]?AddressData[fieldName]:'';
				base_form.findField(fieldName).setValue(value);
			}
		}
	},
	setFieldsOnIdent: function(data, disableFields) {
		var base_form = this.findById('person_edit_form').getForm();
		// Устанавливаем дату актуальности данных в сводной базе застрахованных
		base_form.findField('Person_identDT').setValue(data.Person_identDT);
		//base_form.findField('PersonIdentState_id').setValue(data.PersonIdentState_id);
		// Добавляем старые поля полиса и фио, если они были задисаблены.
		this.oldValues = this.oldValues + this.addToOldValuesForIdentification;

		base_form.findField('Person_IsInErz').setValue(data.Person_IsInErz);

		if ( data.Person_IsInErz == 2 ) {
			// https://redmine.swan.perm.ru/issues/11587

			// Идентификатор из сервиса идентификации
			if (data.BDZ_id) {
				base_form.findField('BDZ_id').setValue(data.BDZ_id);
			}

			// Фамилия
			base_form.findField('Person_SurName').setValue(data.Person_SurName ? data.Person_SurName : '');

			// Имя
			base_form.findField('Person_FirName').setValue(data.Person_FirName ? data.Person_FirName : '');

			// Отчество
			base_form.findField('Person_SecName').setValue(data.Person_SecName ? data.Person_SecName : '');

			// Дата рождения
			base_form.findField('Person_BirthDay').setValue(data.Person_BirthDay ? getValidDT(data.Person_BirthDay, '') : '');

			//Дата смерти
			base_form.findField('Person_deadDT').setValue(data.Person_deadDT ? data.Person_deadDT : '');
			if (!Ext.isEmpty(base_form.findField('Person_deadDT').getValue())) {
				base_form.findField('Person_deadDT').container.up('div.x-form-item').show();
			}

			// Телефон пациента
			// https://redmine.swan.perm.ru/issues/31518
			if ( !Ext.isEmpty(data.PersonPhone_Phone) && Ext.isEmpty(base_form.findField('PersonPhone_Phone').getValue()) ) {
				var tmp_phone = data.PersonPhone_Phone;
				var phone = '';
				var index = 0;
				if(tmp_phone[0]=='+')
					index = [2];
				else
					index = [1];
				tmp_phone = tmp_phone.replace('-','');
				for(var i=index; i<tmp_phone.length; i++)
				{
					phone = phone+tmp_phone[i];
				}
				//alert(phone.replace(/[^0-9]/gim,''));
				base_form.findField('PersonPhone_Phone').setValue(phone);
				//base_form.findField('PersonPhone_Phone').setValue(data.PersonPhone_Phone);
			}

			// ИИН
			if ( data.PersonInn_Inn ) {
				base_form.findField('PersonInn_Inn').setValue(data.PersonInn_Inn);
			}

			// Пол
			if ( data.Sex_Code ) {
				base_form.findField('PersonSex_id').getStore().each(function(rec) {
					if ( rec.get('Sex_Code') == data.Sex_Code ) {
						base_form.findField('PersonSex_id').setValue(rec.get('Sex_id'));
					}
				});
			} else if (data.PersonSex_id) {
				base_form.findField('PersonSex_id').setValue(data.PersonSex_id);
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

			// Номер документа
			if ( data.DOC_NUM ) {
				base_form.findField('Document_Num').setValue(data.DOC_NUM);
			}

			// Дата выдачи документа
			if ( data.Document_begDate ) {
				base_form.findField('Document_begDate').setValue(data.Document_begDate);
			}

			// Соц. статус
			if ( data.SocStatus_id ) {
				base_form.findField('SocStatus_id').setValue(data.SocStatus_id);
			}
			base_form.findField('SocStatus_id').onLoadStore(); //Выполняется фильтрация

			// Национальность
			if ( data.Ethnos_id ) {
				base_form.findField('Ethnos_id').setValue(data.Ethnos_id);
			}

			// Гражданство
			if ( data.KLCountry_id ) {
				base_form.findField('KLCountry_id').setValue(data.KLCountry_id);
			}

			if (data.UAddress) {
				this.setAddress(data.UAddress,'U');
			}
			if (data.PAddress) {
				this.setAddress(data.PAddress,'P');
			}
			if (data.BAddress) {
				this.setAddress(data.BAddress,'B');
			}


			// Если задано предупреждение...
			if ( data.Alert_Msg && data.Alert_Msg.toString().length > 0 ) {
				// ... выводим предупреждение
				sw.swMsg.alert(langs('Предупреждение'), data.Alert_Msg.toString(), function() {this.buttons[2].focus();}.createDelegate(this) );
			}

			// Блокируем поля от дальнейшего изменения данных пользователем
			if ( base_form.findField('PersonIdentState_id').getValue().toString().inlist(['1', '3']) && !getGlobalOptions().superadmin ) {
				if (disableFields) {
					// https://redmine.swan.perm.ru/issues/11587
					// 3) При идентификации снять блокировку со всех полей , кроме полей полисных данных , Фамилия , Имя, Отчество, Дата рождения.
					base_form.findField('Person_SurName').disable();
					base_form.findField('Person_FirName').disable();
					base_form.findField('Person_SecName').disable();
					base_form.findField('Person_BirthDay').disable();
					base_form.findField('UAddress_AddressText').disable();
				}
			}
		}
		else {
			base_form.findField('Person_SurName').enable();
			base_form.findField('Person_FirName').enable();
			base_form.findField('Person_SecName').enable();
			base_form.findField('Person_BirthDay').enable();
			base_form.findField('PersonInn_Inn').enable();
			base_form.findField('PersonSex_id').enable();
			base_form.findField('SocStatus_id').enable();
			base_form.findField('DocumentType_id').enable();
			base_form.findField('Document_Num').enable();
			base_form.findField('UAddress_AddressText').enable();
		}
		
		base_form.clearInvalid();

		return true;
	},
	checkPersonDoubles: function() {
		var win = this;
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
		params.Person_Inn = base_form.findField('PersonInn_Inn').getValue();
		params.Person_IsUnknown = base_form.findField('Person_IsUnknown').checked ? 2 : 1;

		win.getLoadMask(langs('Подождите, идет проверка двойников...')).show();

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
									title: langs('Проверка дубля по серии и номеру полиса'),
									msg: langs('Серия и номер полиса совпадают с данными полиса другого человека. Открыть его на редактирование?'),
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
									langs('Проверка ИНН'),
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
									langs('Ошибка'),
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
    askPrintNewslatterAccept: function(params) {
	
		if (!params || !params.NewslatterAccept_id) {
			return false;
		}
		
		var win = this;
		
		if (Ext.isEmpty(params.NewslatterAccept_endDate)) {	
			
			sw.swMsg.show({
				title: langs('Вопрос'),
				msg: langs('Распечатать документ?'),
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
				title: langs('Вопрос'),
				msg: langs('Распечатать документ?'),
				icon: Ext.MessageBox.QUESTION,
				buttons: {
					yes: langs('Печать Согласия'),
					no: langs('Печать отказа'),
					cancel: langs('Отмена')
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
		'FamilyStatus_id',
		'PersonFamilyStatus_IsMarried',
		'PersonInn_Inn',
		'PersonRefuse_IsRefuse',
		'PersonChildExist_IsChild',
		'PersonCarExist_IsCar',
		'PersonSex_id',
		'SocStatus_id',
		'Document_Num',
		'DocumentType_id',
		'OrgDep_id',
		'Document_begDate',
		'KLCountry_id',
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
		'PersonRefuse_IsRefuse',
		'PersonChildExist_IsChild',
		'PersonCarExist_IsCar',
		'Person_BirthDay',
		'PersonSex_id',
		'SocStatus_id'
	],
	periodicStructFields: {
		'Deputy': [
			'DeputyKind_id',
			'DeputyPerson_id'
		],
		'Document': [
			'Document_Num',
			'DocumentType_id',
			'OrgDep_id',
			'Document_begDate',
			'KLCountry_id'
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
		],
		'NationalityStatus': [
			'KLCountry_id'
		]
	},
	notPeriodicStructFields: {
		'Person': [
			'Person_Comment'
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
		var notice = [];

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
		return notice;
	},
	doCheckAndSaveOnThePersonEvn: function() {
		var form = this.findById('person_edit_form');
		var base_form = form.getForm();
		if ( this.readOnly )
			return;
		var oldValues = this.oldValues;
		var action = this.action;
		
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
			var notice = this.validationFormWithRegion();
			if (notice)
			{
				if (notice.length > 0)
					sw.swMsg.show({
						title: langs('Предупреждение'),
						msg: langs('Обнаружены возможные ошибки: ') + notice.toString() + langs('<BR />Подтверждаете сохранение?'),
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
										Evn_setDT: win.Evn_setDT,
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
		else {
			Ext.MessageBox.show({
				title: "Сохранение атрибутов",
				msg: "Вы не изменили ни одного атрибута.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			});
		}
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
		
		if ( !base_form.isValid() ) {
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			});
			return false;
		}

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
					title: langs('Предупреждение'),
					msg: langs('Обнаружены возможные ошибки: ') + notice.toString() + langs('<BR />Подтверждаете сохранение?'),
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
												langs('Ошибка'),
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
					title: langs('Ошибка'),
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
											langs('Ошибка'),
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
		
		var changed_fields = this.getChangedFields();
		// если поле одиночное, то тупо отправляем его значение
		if ( changed_fields.length > 0 )
		{
			// сохраняем
			var saving_data = {};

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
	},
	// проверка, прошел ли человек идентификацию
	checkIfPersonIsInErz: function()
	{
		var form = this.findById('person_edit_form'),
			base_form = form.getForm();

		return base_form.findField('Person_IsInErz').getValue() == 2;
	},
	// метод для сохранения после идентификации человека. Так как проверка асинхронна, то функция сохранения передается в callback-функции
	// Она передается в другую callback-функцию, которая отрабатывает после ajax запроса в методе doPersonIdentRequest после проверки идентификации
	checkIfPersonIsInErzAndSave: function (saveFunction)
	{
		// определяем callback для ajax запроса в методе doPersonIdentRequest
		var callbackAfterRequest = function (options, success, response)
		{
			var win = this;
			win.getLoadMask().hide();
			if ( success )
			{
				var response_obj = Ext.util.JSON.decode(response.responseText),
					person_IsInErz = null;

				if ( response_obj.Person_IsInErz == 2 )
				{
					this.setFieldsOnIdent(response_obj, true);
					person_IsInErz = true;
				}
			}

			// если человек не идентифицирован или сервис не доступен, то выводим сообщение и сохраняем по нажатию ОК #127314
			if (person_IsInErz !== true)
			{
				sw.swMsg.alert(langs('Внимание'), langs('Внимание. Человек не идентифицирован, повторите идентификацию позднее'), function () {/**СОХРАНЕНИЕ*/saveFunction()/**СОХРАНЕНИЕ*/ });
			} else
			{	// если идентифицирован, то просто сохраняем

				/**СОХРАНЕНИЕ*/saveFunction();/**СОХРАНЕНИЕ*/
			}

			return;
		};

		// выполняем проверку, затем сохраняем
		this.doPersonIdentRequest(callbackAfterRequest);

		return;
	},
	doSave: function() {
        var form = this.findById('person_edit_form');
        var base_form = form.getForm();

		if ( this.readOnly )
			return;
		var oldValues = this.oldValues;
		var action = this.action;
		
		if ( !base_form.isValid() ) {
			Ext.MessageBox.show({
				title: "Проверка данных формы",
				msg: "Не все поля формы заполнены корректно, проверьте введенные вами данные. Некорректно заполненные поля выделены особо.",
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				fn: function() {
					base_form.findField('Person_SurName').focus(true, 100);
				}
			});
			return false;
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
					title: langs('Предупреждение'),
					msg: langs('Обнаружены возможные ошибки: ') + notice.toString() + langs('<BR />Подтверждаете сохранение?'),
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
	},
	// метод для определения любого поля FieldName соответствующего текущей выбранной записи в комбо ComboName
	getSelectedValueCodeFromCombo: function (ComboName, FieldName)
	{
		var combo = this.findById('person_edit_form').getForm().findField(ComboName),
			combo_id = combo.getValue(),
			combo_idx = combo.getStore().find(ComboName, combo_id);

		if (combo_idx == -1)
		{
			return -1;
		}

		return combo.getStore().getAt(combo_idx).get(FieldName);


	},
	doSavePerson: function () // изменил логику сохранения в рамках #127314
	{
		// узнаем код из поля гражданство, нужен 398 казахстан
		var KLCountry_Code = this.getSelectedValueCodeFromCombo('KLCountry_id', 'KLCountry_Code'),
			// определяем анонимную функцию, которая будет просто выполнять переданную функцию, на случай если не надо будет делать идентификацию перед сохранением
			wrapperFunction = function (callback)
			{
				callback();
				return;
			};

		// если гражданство казахстан и человек не прошел идентификацию, то перед сохранением мы автоматически ее проведем
		if (KLCountry_Code == 398 && this.checkIfPersonIsInErz() !== true)
		{
			wrapperFunction = this.checkIfPersonIsInErzAndSave.createDelegate(this);
		}

		// старая логика
		if ( this.subaction && (this.subaction == 'addperiodic' || this.subaction=='editperiodic') )
		{
			wrapperFunction(this.doSaveNewPeriodicsOnDate.createDelegate(this));
		} else
		{
			if ( this.personEvnId )
			{
		 		wrapperFunction(this.doCheckAndSaveOnThePersonEvn.createDelegate(this));
			} else
			{
				wrapperFunction(this.doSave.createDelegate(this));
			}

		}

		return;
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
			post.Person_SurName = base_form.findField('Person_SurName').getValue();
			post.Person_FirName = base_form.findField('Person_FirName').getValue();
			post.Person_SecName = base_form.findField('Person_SecName').getValue();
			post.Person_BirthDay = Ext.util.Format.date(base_form.findField('Person_BirthDay').getValue(), 'd.m.Y');
			post.PersonInn_Inn = base_form.findField('PersonInn_Inn').getValue();
			post.PersonSex_id = base_form.findField('PersonSex_id').getValue();
			post.SocStatus_id = base_form.findField('SocStatus_id').getValue();
			post.DocumentType_id = base_form.findField('DocumentType_id').getValue();
			post.Document_Num = base_form.findField('Document_Num').getValue();
		}
		
		if(base_form.findField('Person_BirthDay').getValue()<this.minBirtDay&&this.minBirtDay!=null) {
			Ext.Msg.alert("Ошибка", "Дата рождения должна быть не меньше даты исхода беременности");
			return;
		}

		if ( base_form.findField('Document_Num').disabled && !post.Document_Num ) {
			post.Document_Num = base_form.findField('Document_Num').getValue();
		}
		if ( base_form.findField('KLCountry_id').disabled && !post.KLCountry_id ) {
			post.Document_Num = base_form.findField('KLCountry_id').getValue();
		}

		win.getLoadMask('Подождите, идёт сохранение...').show();
		this.findById('person_edit_form').getForm().submit(
		{
			params: post,
			timeout: 1800000,
			success: function(form, action) {
				win.getLoadMask().hide();
				win.hide();
				win.returnFunc({
					Person_id: action.result.Person_id,
					Server_id: action.result.Server_id,
					PersonEvn_id: action.result.PersonEvn_id,
					PersonData: {
						Person_id: action.result.Person_id,
						Server_id: action.result.Server_id,
						PersonEvn_id: action.result.PersonEvn_id,
						Lpu_Nick: action.result.Lpu_Nick?action.result.Lpu_Nick:null,
						Person_FirName: form.findField('Person_FirName').getValue(),
						Person_SurName: form.findField('Person_SurName').getValue(),
						Person_SecName: form.findField('Person_SecName').getValue(),
						Person_BirthDay: form.findField('Person_BirthDay').getValue(),
						//Person_Snils: form.findField('Person_SNILS').getValue(),
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
			}
		});
	},
	disableDocumentFields: function(disable, unclear)
	{
		if (this.readOnly)
			return;
		var form = this.findById('person_edit_form');
		if ( disable == true )
		{
			form.getForm().findField('OrgDep_id').disable();
			form.getForm().findField('Document_Num').disable();
			form.getForm().findField('Document_begDate').disable();
			//form.getForm().findField('KLCountry_id').disable();
			if (unclear != true)
			{
				form.getForm().findField('OrgDep_id').clearValue();
				form.getForm().findField('Document_Num').setRawValue('');
				form.getForm().findField('Document_begDate').setRawValue('');
				//form.getForm().findField('KLCountry_id').clearValue();
			}
		}
		else
		{
			form.getForm().findField('OrgDep_id').enable();
			form.getForm().findField('DocumentType_id').enable();
			form.getForm().findField('Document_Num').enable();
			form.getForm().findField('Document_begDate').enable();
			if (form.getForm().findField('DocumentType_id').getFieldValue('DocumentType_Code') != 22) {
				//form.getForm().findField('KLCountry_id').enable();
			} else {
				//form.getForm().findField('KLCountry_id').disable();
			}
		}
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
		sw.Promed.swPersonEditWindow.superclass.show.apply(this, arguments);
		
		this.childAdd = false;
		var win = this;
		var base_form = this.findById('person_edit_form').getForm();
		var form = this.findById('person_edit_form');
		this.personId = 0;
		this.readOnly = false;
		this.serverId = 0;

		if ( isSmoTfomsUser() ) {
			this.findById('pacient_tab_panel').hideTabStripItem('additional_tab');
			this.findById('pacient_tab_panel').hideTabStripItem('spec_tab');
		}

		base_form.findField('Person_IsUnknown').setContainerVisible(false);

		this.minBirtDay = null;

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
		
		//this.buttons[2].hide();
		
		if (this.action == 'view') {
			this.readOnly = true;
		}
		
		if (!this.readOnly) {
			this.disableEdit(false);
		}
		else{
			this.disableEdit(true);
		}
		if (this.action == 'add')
		{
			this.setTitle(WND_PERS_ADD);
			base_form.findField('PersonInn_Inn').setAllowBlank(true);
		}

			
		if ( this.subaction && this.subaction=='editperiodic' )
		{
			this.disableEdit(true);
			Ext.getCmp('PEW_SaveButton').enable();
		}
		
		if ( this.action != 'edit' || this.readOnly === true )
		{
			Ext.getCmp('PEW_PeriodicsButton').hide();
		}
		else
		{
			Ext.getCmp('PEW_PeriodicsButton').show();
		}
		
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
		
		this.findById('pacient_tab_panel').setActiveTab(3);
		this.findById('pacient_tab_panel').setActiveTab(2);
		this.findById('pacient_tab_panel').setActiveTab(1);
		this.findById('pacient_tab_panel').setActiveTab(0);
		this.disableDocumentFields(true);
		
		if ( this.subaction == 'editperiodic' )
		{
			this.setTitle(langs('Человек: редактирование периодики'));
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
			win.getLoadMask(langs('Пожалуйста, подождите, идет загрузка данных формы...')).show();
		}

		base_form.reset();

		this.PersonFeedingType.removeAll();
		
		base_form.findField('PersonSex_id').setValue('');
		
		base_form.findField('Person_deadDT').container.up('div.x-form-item').hide();
		base_form.findField('Person_closeDT').container.up('div.x-form-item').hide();

		this.NewslatterAcceptGrid.removeAll();
		this.oldValuesToRestore = null;

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
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось загрузить данные с сервера'), function() {this.hide();}.createDelegate(this));
				}.createDelegate(this),
				params: params,
				success: function(fm) {
					win.checkOkeiRequired();

					if (!Ext.isEmpty(fm.findField('SocStatus_id').getValue())) {
						fm.findField('SocStatus_id').onLoadStore(); //Выполняется фильтрация
						fm.findField('SocStatus_id').setValue(fm.findField('SocStatus_id').getValue());
					}

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

					if (this.action === 'edit' && this.getSelectedValueCodeFromCombo('DocumentType_id', 'DocumentType_Code').inlist([1, 2, 3]))
					{
						fm.findField('PersonInn_Inn').setAllowBlank(false);
					} else
					{
						fm.findField('PersonInn_Inn').setAllowBlank(true);
					}

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
					
					switch (mask)
					{
						// для суперадмина
						case "000":
						case "001":
							//
						break;
						case "100":
						case "010":
						case "110":
						case "101":
						case "011":
						case "111":
							fm.findField('Person_SurName').disable();
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
							fm.findField('PersonSex_id').disable();
						break;
						case "serv":
							fm.findField('Person_FirName').disable();
							fm.findField('Person_SecName').disable();
							fm.findField('Person_BirthDay').disable();
						break;
					}

					base_form.findField('PersonChild_IsInvalid').fireEvent('change', base_form.findField('PersonChild_IsInvalid'), base_form.findField('PersonChild_IsInvalid').getValue());

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
					else if ( !fm.findField('SocStatus_id').disabled )
						fm.findField('SocStatus_id').focus(true, 300);
					else
						fm.findField('UAddress_AddressText').focus(true, 300);
					
					this.oldValues = base_form.getValues(true);
					// фикс сохранения полей после идентификации (refs #15400)
					// (в oldValues при идентификации должны попасть задисабленные поля полиса и фио, чтобы сработало их сохранение)
					var list = ['Person_BirthDay', 'PersonSex_id', 'Person_SurName', 'Person_FirName', 'Person_SecName'];
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

					var doc_type_field = base_form.findField('DocumentType_id');
					if ( doc_type_field.getValue() > 0 ) {
						var doc_type_record = doc_type_field.getStore().getById(doc_type_field.getValue());
						if (doc_type_record) {
							doc_type_field.fireEvent('select',doc_type_field, doc_type_record);
							doc_type_field.fireEvent('blur',doc_type_field);
						}
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

					// #129470 блокировка элементов в режиме просмотра
					if(this.readOnly === true) {
						//console.log('---вкладка Пациент');
						this.disableFieldsInViewMode(Ext.getCmp('pacient_tab'));

						//console.log('---вкладка Дополнительно');
						this.disableFieldsInViewMode(Ext.getCmp('additional_tab'));

						//console.log('---вкладка Специфика. Детство');
						this.disableFieldsInViewMode(Ext.getCmp('spec_tab'));
					}

					//console.log('---Таблица: Оценка физического развития');
					var personEvalGrid = Ext.getCmp('PersonEval');
					personEvalGrid.setActionDisabled('action_add', this.readOnly);
					personEvalGrid.setActionDisabled('action_edit', this.readOnly);
					personEvalGrid.setActionDisabled('action_delete', this.readOnly);
					personEvalGrid.editformclassname = (this.readOnly)?'':'swPersonEvalEditWindow';// защита от клика по элементу таблицы

					//console.log('---Таблица: Способ вскармливания');
					var personFeedingType = Ext.getCmp('PersonFeedingType');
					personFeedingType.setActionDisabled('action_add', this.readOnly);
					personFeedingType.setActionDisabled('action_edit', this.readOnly);
					personFeedingType.setActionDisabled('action_delete', this.readOnly);
					personFeedingType.editformclassname = (this.readOnly)?'':'swPersonFeedingTypeEditWindow';// защита от клика по элементу таблицы

					//console.log('---Таблица: СМС/e-mail уведомления');
					var newsLetterAcceptGrid = Ext.getCmp('PEW_NewslatterAcceptGrid');
					newsLetterAcceptGrid.setActionDisabled('action_add', this.readOnly);
					newsLetterAcceptGrid.setActionDisabled('action_edit', this.readOnly);
					newsLetterAcceptGrid.setActionDisabled('action_delete', this.readOnly);
					newsLetterAcceptGrid.editformclassname = (this.readOnly)?'':'swNewslatterAcceptEditForm';// защита от клика по элементу таблицы

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
				switch(getRegionNick()){
					default:
						base_form.findField('SocStatus_id').setFieldValue('SocStatus_SysNick','nrab');
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
			}
			else
			{
				base_form.findField('PersonRefuse_IsRefuse').enable();
			}
			win.checkOkeiRequired();
		}

		base_form.findField('PersonInfo_InternetPhone').disable();
		
		if ( arguments[0].addMother ) {
			this.findById('pacient_tab_panel').setActiveTab(1);
			base_form.findField('DeputyKind_id').focus(true, 700);
		}
		
		this.NewslatterAcceptGrid.addActions({ name: 'action_print_menu', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls: 'x-btn-text', icon: 'img/icons/print16.png', menu: [
			win.printAccept = new Ext.Action({name:'print_accept', text: langs('Согласие на получение смс(e-mail) уведомлений'), handler: function() { this.printNewslatterAccept('printAccept'); }.createDelegate(this)}),
			win.printDenial = new Ext.Action({name:'print_denial', text: langs('Отказ от получения уведомлений'), handler: function() { this.printNewslatterAccept('printDenial'); }.createDelegate(this)})
		]});
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
				{name: 'EvalType', type: 'string',  header: langs('Показатель'),width:105},
				{name: 'PersonEval_setDT', type: 'date', format: 'd.m.Y',width:125, header: langs('Дата измерения')},
				{name: 'EvalMeasureType', type: 'string', header:langs('Вид замера'),width:125},				
				{name: 'PersonEval_value', type: 'int', header: langs('Значение'),width:125},
				{name: 'PersonEval_isAbnorm', type: 'string', header:langs('Отклонение'),width:100},
				{name: 'EvalAbnormType', type:'string',header:langs('Тип отклонения'),width:150}
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
					msg: langs('Удалить показатель измерения?'),
					title: langs('Вопрос')
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
				{name: 'Lpu_Nick', type: 'string', header: langs('МО')},
				{name: 'NewslatterAccept_begDate', type: 'date', format: 'd.m.Y', header: langs('Дата согласия')},
				{name: 'NewslatterAccept_IsSMS', type: 'checkbox', header: langs('Уведомления по СМС')},
				{name: 'NewslatterAccept_Phone', type: 'string', header: langs('Номер телефона')},
				{name: 'NewslatterAccept_IsEmail', type: 'checkbox', header: langs('Уведомления по e-mail')},
				{name: 'NewslatterAccept_Email', type: 'string', header: 'E-mail'},
				{name: 'NewslatterAccept_endDate', type: 'date', format: 'd.m.Y', header: langs('Дата отказа от рассылок')}
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
								params: {NewslatterAccept_id: selected_record.get('NewslatterAccept_id')},
								url:'/?c=NewslatterAccept&m=delete'
							})
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: langs('Удалить согласие на получение уведомлений?'),
					title: langs('Вопрос')
				})
			}
		});

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
					{name: 'BDZ_id'},
					{name: 'Server_pid'},
					{name: 'Person_SurName'},
					{name: 'Person_SecName'},
					{name: 'Person_FirName'},
					{name: 'Person_BirthDay'},
					{name: 'Person_IsUnknown'},
					{name: 'PersonSex_id'},
					{name: 'PersonPhone_Phone'},
					//{name: 'PersonPhone_Comment'},
					{name: 'Person_Comment'},
					{name: 'PersonInfo_InternetPhone'},
					{name: 'FamilyStatus_id'},
					{name: 'PersonFamilyStatus_IsMarried'},
					{name: 'PersonInn_Inn'},
					{name: 'PersonRefuse_IsRefuse'},
					{name: 'PersonChildExist_IsChild'},
					{name: 'PersonCarExist_IsCar'},
					{name: 'SocStatus_id'},
					{name: 'Document_Num'},
					{name: 'DocumentType_id'},
					{name: 'OrgDep_id'},
					{name: 'Document_begDate'},
					{name: 'KLCountry_id'},
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
					{name: 'Person_IsInErz'},
					{name: 'Diag_id'},
					{name: 'FeedingType_id'},
					{name: 'PersonChild_CountChild'},
					{name: 'HealthAbnormVital_id'},
					{name: 'HealthAbnorm_id'},
					{name: 'HealthKind_id'},
					{name: 'InvalidKind_id'},
					{name: 'Okei_id'},
					{name: 'PersonChild_id'},
					{name: 'PersonChild_IsBad'},
					{name: 'PersonChild_IsYoungMother'},
					{name: 'PersonChild_IsIncomplete'},
					{name: 'PersonChild_IsInvalid'},
					{name: 'PersonChild_IsManyChild'},
					{name: 'PersonChild_IsMigrant'},
					{name: 'PersonChild_IsTutor'},
					{name: 'PersonChild_invDate'},
					{name: 'ResidPlace_id'},
					{name: 'Person_deadDT'},
					{name: 'Person_closeDT'},
					{name: 'Person_IsFedLgot'},
					{name: 'Ethnos_id'},
					{name: 'Person_IsUnknown'}
					
				]),
				items: [{
					xtype: 'hidden',
					name: 'Person_IsInErz'
				}, {
					xtype: 'hidden',
					name: 'BDZ_id'
				}, {
					xtype: 'hidden',
					name: 'DeputyPerson_Fio'
				}, {
					xtype: 'hidden',
					name: 'Person_id'
				}, {
					xtype: 'hidden',
					name: 'Server_pid',
					id: 'server_id'
				},{
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
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 70,
						items: [{
							allowBlank: false,
							fieldLabel: langs('Фамилия'),
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
							width: 180,
							xtype: 'textfield'
						}, {
							allowBlank: true,
							fieldLabel: langs('Имя'),
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
							width: 180,
							xtype: 'textfield'
						}, {
							xtype: 'textfield',
							fieldLabel: langs('Отчество'),
							listeners: {
								blur: function (inp) {
									inp.setValue(inp.getValue().trim());
								}
							},
							toUpperCase: true,
							width: 180,
							name: 'Person_SecName',
							tabIndex: TABINDEX_PEF + 2
						}]
					}, {
						layout: 'form',
						labelWidth: 130,
						items: [{
							allowBlank: false,
							fieldLabel: langs('Дата рождения'),
							format: 'd.m.Y',
							maxValue: getGlobalOptions().date,
							minValue: getMinBirthDate(),
							name: 'Person_BirthDay',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							tabIndex: TABINDEX_PEF + 3,
							width: 95,
							xtype: 'swdatefield'
						}, /*{
							allowBlank: true,
							fieldLabel: langs('Телефон'),
							id: 'PEW_PersonPhone_Phone',
							name: 'PersonPhone_Phone',
							tabIndex: TABINDEX_PEF + 5,
							width: 180,
							xtype: 'textfield'
						}, */
						{
							fieldLabel: langs('Телефон')+'  +7',
							id: 'PEW_PersonPhone_Phone',
							name: 'PersonPhone_Phone',
							tabIndex: TABINDEX_PEF + 5,
							fieldWidth: 150,
							xtype: 'swphonefield'
						},
						{
							fieldLabel: langs('Тел. с сайта записи'),
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
							fieldLabel: langs('Пол'),
							hiddenName: 'PersonSex_id',
							tabIndex: TABINDEX_PEF + 4,
							width: 120,
							xtype: 'swcommonsprcombo'
                        },{
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
							},{
							layout: 'form',
							labelWidth:80,
							items: [{
								labelSeparator: '',
								boxLabel: langs('Личность неизвестна'),
								name: 'Person_IsUnknown',
								hidden: false,
								tabIndex: TABINDEX_PEF + 4,
								xtype: 'checkbox',
								listeners: {
									'check': function(field, value) {
										var base_form = this.findById('person_edit_form').getForm();

										base_form.findField('Person_BirthDay').setAllowBlank(value);
										base_form.findField('PersonSex_id').setAllowBlank(value);
										//base_form.findField('SocStatus_id').setAllowBlank(value);

										var surname_field = base_form.findField('Person_SurName');
										if (value && Ext.isEmpty(surname_field.getValue())) {
											surname_field.setValue(langs('НЕИЗВЕСТЕН'));
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
						height: Ext.isIE ? 450 : 430,
						id: 'pacient_tab',
						layout:'form',
						title: langs('1. Пациент'),
						items: [{
							autoHeight: true,
							border: false,
							layout: 'form',
							style: 'padding: 0; padding-top: 5px; margin: 0',
							items: [{
								//allowBlank: false,
								autoLoad: false,
								codeField: 'SocStatus_Code',
								typeCode: 'int',
								comboSubject: 'SocStatus',
								moreFields: [
									{name: 'SocStatus_begDT', type: 'date', dateFormat: 'd.m.Y H:i:s'},
									{name: 'SocStatus_endDT', type: 'date', dateFormat: 'd.m.Y H:i:s'}
								],
								allowSysNick:true,
								editable: true,
								lastQuery: '',
								fieldLabel: langs('Соц. статус'),
								tabIndex: TABINDEX_PEF + 7,
								width: 310,
								validator: function() {
									var combo = this;
									if (combo.getStore().indexOfId(combo.getValue()) < 0) {
										return true;
									}
									var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
									var begDT = combo.getFieldValue('SocStatus_begDT');
									var endDT = combo.getFieldValue('SocStatus_endDT');
									var inDate = (
										Ext.isEmpty(begDT) && Ext.isEmpty(endDT)
										|| Ext.isEmpty(begDT) && endDT >= date
										|| Ext.isEmpty(endDT) && begDT < date
										|| begDT < date && endDT >= date
									);
									if (!inDate) {
										return langs('Социальный статус закрыт на текущую дату');
									} else {
										return true;
									}
								},
								onLoadStore: function() {
									var combo = this;
									var store = combo.getStore();
									var value = combo.getValue();
									var date = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
									store.filterBy(function(rec){
										var id = rec.get('SocStatus_id');
										var begDT = rec.get('SocStatus_begDT');
										var endDT = rec.get('SocStatus_endDT');
										var inDate = (
											Ext.isEmpty(begDT) && Ext.isEmpty(endDT)
											|| Ext.isEmpty(begDT) && endDT >= date
											|| Ext.isEmpty(endDT) && begDT < date
											|| begDT < date && endDT >= date
										);
										return (value == id || inDate);
									});
									combo.setValue(value);
									combo.validate();
								},
								xtype: 'swcommonsprcombo'
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0',
							title: langs('Адрес'),
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
											fieldLabel: langs('Адрес регистрации'),
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
															KLTown_idEdit: ownerForm.findById('PEW_UKLTown_id').getValue(),
															KLStreet_idEdit: ownerForm.findById('PEW_UKLStreet_id').getValue(),
															Address_HouseEdit: ownerForm.findById('PEW_UAddress_House').getValue(),
															Address_CorpusEdit: ownerForm.findById('PEW_UAddress_Corpus').getValue(),
															Address_FlatEdit: ownerForm.findById('PEW_UAddress_Flat').getValue(),
															Address_AddressEdit: ownerForm.findById('PEW_UAddress_Address').getValue(),
															//Address_begDateEdit: ownerForm.findById('PEW_UAddress_begDate').getValue(),
															addressType: 1,
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
								xtype: 'hidden',
								name: 'PersonChild_id'
							},{
								layout: 'column',
								items: [{
										layout: 'form',
										items: [
											new sw.Promed.TripleTriggerField ({
												enableKeyEvents: true,
												fieldLabel: langs('Адрес проживания'),
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
															KLTown_idEdit: ownerForm.findById('PEW_PKLTown_id').getValue(),
															KLStreet_idEdit: ownerForm.findById('PEW_PKLStreet_id').getValue(),
															Address_HouseEdit: ownerForm.findById('PEW_PAddress_House').getValue(),
															Address_CorpusEdit: ownerForm.findById('PEW_PAddress_Corpus').getValue(),
															Address_FlatEdit: ownerForm.findById('PEW_PAddress_Flat').getValue(),
															Address_AddressEdit: ownerForm.findById('PEW_PAddress_Address').getValue(),
															//Address_begDateEdit: ownerForm.findById('PEW_PAddress_begDate').getValue(),
															addressType: 1,
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
								fieldLabel: langs('Адрес рождения'),
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
							style: 'padding: 0; padding-top: 5px; margin: 0;',
							title: langs('Документ'),
							xtype: 'fieldset',

							items: [{
								border: false,
								layout: 'column',

								items: [{
									layout: 'form',
									items: [{
										fieldLabel: langs('Тип'),
										listeners: {
											'select': function(combo, record, index) {
												var base_form = this.findById('person_edit_form').getForm();
												
												if ( typeof record == 'object' && !Ext.isEmpty(record.get('DocumentType_id')) ) {
													if(record.get('DocumentType_Code')&&record.get('DocumentType_Code')=='9'){
														base_form.findField('Document_begDate').setAllowBlank(true);
														base_form.findField('Document_Num').maxLength=undefined;
													}	else{
														base_form.findField('Document_Num').maxLength=20;
														base_form.findField('Document_begDate').setAllowBlank(false);
													}
													if ( record.get('DocumentType_MaskNum')&& record.get('DocumentType_MaskNum')!='') {
														base_form.findField('Document_Num').regex = new RegExp( record.get('DocumentType_MaskNum') );
														base_form.findField('Document_Num').invalidText=record.get('DocumentType_Mask');
														base_form.findField('Document_Num').setAllowBlank(false);
													}else{
														base_form.findField('Document_Num').setAllowBlank(true);
														base_form.findField('Document_Num').regex = undefined;
														base_form.findField('Document_Num').invalidText="Значение в этом поле неверное";
													}
													
													// if ( !Ext.isEmpty(record.get('DocumentType_MaskSer')) ){
													// 	base_form.findField('PersonInn_Inn').setAllowBlank(false);
													// }
													// else{
													// 	base_form.findField('PersonInn_Inn').setAllowBlank(true);
													// }
													
													this.disableDocumentFields(false);
												}
												else {
													base_form.findField('Document_Num').regex = undefined;
													base_form.findField('Document_Num').invalidText="Значение в этом поле неверное";
													this.disableDocumentFields(true);

													base_form.findField('Document_Num').setAllowBlank(true);
													base_form.findField('Document_Num').clearInvalid();
												}
											}.createDelegate(this),
											'change': function(combo, newValue, oldValue) {

												var DocumentType_Code = this.getSelectedValueCodeFromCombo(combo.hiddenName, 'DocumentType_Code'),
													PersonInn_Inn = this.findById('person_edit_form').getForm().findField('PersonInn_Inn');

												if (DocumentType_Code.inlist([1, 2, 3]))
												{
													PersonInn_Inn.setAllowBlank(false);
												} else
												{
													PersonInn_Inn.setAllowBlank(true);
												}

												return;

											}.createDelegate(this)
										},
										onTrigger2Click: function() {
											this.findById('').getForm().findField('DocumentType_id').clearValue();
											this.disableDocumentFields(true);
										}.createDelegate(this),
										listWidth: 400,
										tabIndex: TABINDEX_PEF + 19,
										width: 300,
										xtype: 'swdocumenttypecombo'
									}, {
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
										listWidth: 400,
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
										width: 300,
										xtype: 'sworgdepcombo'
									}]
								}, {
									layout: 'form',
									labelWidth: 100,
									items:[{
										fieldLabel: langs('Номер'),
										maxLength: 20,
										name: 'Document_Num',
										tabIndex: TABINDEX_PEF + 21,
										width: 130,
										xtype: 'textfield',
										id: 'PEW_Document_Num'
									}, {
										tabIndex: TABINDEX_PEF + 23,
										xtype: 'swdatefield',
										plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
										format: 'd.m.Y',
										fieldLabel: langs('Дата выдачи'),
										width: 94,
										name: 'Document_begDate'
									}]
								}]
							}]
						}, {
							autoHeight: true,
							style: 'padding: 0; padding-top: 5px; margin: 0;',
							title: langs('Гражданство'),
							xtype: 'fieldset',
							items: [{
								tabIndex: TABINDEX_PEF + 23,
								xtype: 'swklcountrycombo',
								fieldLabel: langs('Гражданство'),
								width: 300,
								hiddenName: 'KLCountry_id'
							}]
						},
							{
							border: false,
							layout: 'form',
							style: 'padding: 0; padding-top: 5px; margin: 0;',

							items: [{
								allowBlank: true,
								fieldLabel: langs('ИИН'),
								maskRe: /\d/,
								id: 'PEW_PersonInn_Inn',
								name: 'PersonInn_Inn',
								autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
								tabIndex: TABINDEX_PEF + 32,
								width: 150,
								maxLength: 12,
								minLength: 12,
								xtype: 'textfield'
							}]
						}, {
							autoHeight: true,
							labelWidth: 125,
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							title: langs('Место работы'),
							xtype: 'fieldset',

							items: [{
								xtype: 'sworgcombo',
								hiddenName: 'Org_id',
								editable: false,
								fieldLabel: langs('Место работы, учебы'),
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
							},{
								id: 'PEW_OrgUnion_id',
								hiddenName: 'OrgUnion_id',
								xtype: 'sworgunioncombo',
								minChars: 0,
								queryDelay: 1,
								tabIndex: TABINDEX_PEF + 25,
								selectOnFocus: true,
								width: 610,
								forceSelection: false
							},  {
								xtype: 'swpostcombo',
								minChars: 0,
								queryDelay: 1,
								tabIndex: TABINDEX_PEF + 26,
								hidden: false,
								hideLabel: false,
								hiddenName: 'Post_id',
								fieldLabel: langs('Должность'),
								selectOnFocus: true,
								width: 610,
								forceSelection: false
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
									readOnly: true,
									fieldLabel: langs('Дата смерти'),
									format: 'd.m.Y',
									name: 'Person_deadDT',
									width: 95,
									xtype: 'textfield'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									allowBlank: true,
									readOnly: true,
									fieldLabel: langs('Дата закрытия'),
									format: 'd.m.Y',
									name: 'Person_closeDT',
									width: 95,
									xtype: 'textfield'
								}]
							}]
						}]
					}, {
						title: langs('2. Дополнительно'),
						height: Ext.isIE ? 450 : 430,
						labelWidth: 160,
						id: 'additional_tab',
						layout:'form',
						items: [{
							xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: langs('Представитель'),
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
							comboSubject: 'YesNo',
							id: 'PEW_PersonRefuse_IsRefuse',
							hiddenName: 'PersonRefuse_IsRefuse',
							fieldLabel: langs('Отказ от льготы'),
							tabIndex: TABINDEX_PEF + 31,
							xtype: 'swcommonsprcombo'
						}, {
							xtype: 'fieldset',
							labelWidth: 160,
							autoHeight: true,
							title: langs('Семейное положение'),
							style: 'padding: 0; padding-top: 5px; margin: 0; margin-bottom: 5px;',
							items: [{
								comboSubject: 'YesNo',
								allowBlank: true,
								tabIndex: TABINDEX_PEF + 34,
								hiddenName: 'PersonFamilyStatus_IsMarried',
								name: 'PersonFamilyStatus_IsMarried',
								fieldLabel: langs('Состоит в зарегистрированном браке'),
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
							fieldLabel: langs('Есть дети до 16-ти'),
							hiddenName: 'PersonChildExist_IsChild',
							tabIndex: TABINDEX_PEF + 36,
							xtype: 'swcommonsprcombo'
						},{
						    comboSubject: 'YesNo',
							fieldLabel: langs('Есть автомобиль'),
							hiddenName: 'PersonCarExist_IsCar',
							tabIndex: TABINDEX_PEF + 37,
							xtype: 'swcommonsprcombo'
						},{
						    comboSubject: 'Ethnos',
							fieldLabel: langs('Национальность'),
							hiddenName: 'Ethnos_id',
							editable: true,
							tabIndex: TABINDEX_PEF + 38,
							typeCode: 'int',
							xtype: 'swcommonsprcombo'
						}]
					}, {
						height: Ext.isIE ? 450 : 430,
						id: 'spec_tab',
						labelWidth: 180,
						layout:'form',
						autoScroll:true,
						title: langs('3. Специфика. Детство.'),

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									comboSubject: 'ResidPlace',
									fieldLabel: langs('Место воспитания'),
									listWidth: 350,
									tabIndex: TABINDEX_PEF + 39,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}]
						}, {
							xtype: 'fieldset',
							autoHeight: true,
							width:749,
							title: langs('Семья'),
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: langs('Многодетная'),
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
										fieldLabel: langs('Неблагополучная'),
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
										fieldLabel: langs('Неполная'),
										hiddenName: 'PersonChild_IsIncomplete',
										tabIndex: TABINDEX_PEF + 43,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									layout: 'form',
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: langs('Опекаемая'),
										hiddenName: 'PersonChild_IsTutor',
										tabIndex: TABINDEX_PEF + 44,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}]
							}, {
								comboSubject: 'YesNo',
								fieldLabel: langs('Вынужденные переселенцы'),
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
									fieldLabel: langs('Группа здоровья'),
									tabIndex: TABINDEX_PEF + 46,
									xtype: 'swcommonsprcombo',
									width: 180
								}]
							}, {
								layout: 'form',
								items: [{
									comboSubject: 'YesNo',
									fieldLabel: langs('Юная мать'),
									hiddenName: 'PersonChild_IsYoungMother',
									tabIndex: TABINDEX_PEF + 47,
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
						}, 	{
							xtype: 'fieldset',
							autoHeight: true,
							width:749,
							title: langs('Инвалидность'),
							style: 'padding: 0; margin: 0; margin-bottom: 5px',
							items: [{
								comboSubject: 'YesNo',
								fieldLabel: langs('Инвалидность'),
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
										fieldLabel: langs('Категория'),
										tabIndex: TABINDEX_PEF + 57,
										xtype: 'swcommonsprcombo',
										width: 180
									}]
								}, {
									layout: 'form',
									items: [{
										fieldLabel: langs('Дата установки'),
										maxValue: new Date(),
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'PersonChild_invDate',
										tabIndex: TABINDEX_PEF + 58,
										xtype: 'swdatefield'
									}]
								}]
							}, {
								comboSubject: 'HealthAbnorm',
								fieldLabel: langs('Главное нарушение здоровья'),
								listWidth: 400,
								tabIndex: TABINDEX_PEF + 59,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								comboSubject: 'HealthAbnormVital',
								fieldLabel: langs('Ведущее ограничение здоровья'),
								listWidth: 400,
								tabIndex: TABINDEX_PEF + 60,
								xtype: 'swcommonsprcombo',
								width: 180
							}, {
								fieldLabel: langs('Диагноз'),
								hiddenName: 'Diag_id',
								width: 350,
								tabIndex: TABINDEX_PEF + 61,
								xtype: 'swdiagcombo'
							}]
						},{

							title: langs('Оценка физического развития'),
							layout:'form',
							items: [this.PersonEval/*Здесь грид*/]
						}]
					}, {
						id: 'newslatter_accept_tab',
						title: langs('4. СМС/e-mail уведомления'),
					 	height: Ext.isIE ? 450 : 430,
						labelWidth: 180,
						layout:'form',
						bodyStyle: 'padding: 0px',
						items: [this.NewslatterAcceptGrid]
					}/*,
					{
						title: langs('2. Дополнительно'),
						height: 350,
						id: 'additional_tab',
						layout:'form',
						items: [{
							xtype: 'fieldset',
							labelWidth: 100,
							autoHeight: true,
							title: langs('Место работы'),
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
							}*/

							if (e.getKey() == Ext.EventObject.Y)
							{
								Ext.getCmp('person_edit_form').buttons[2].handler();
								return false;
							}

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
								this.doSavePerson();
							}.createDelegate(this)
						}/*,
						{
							text: langs('Сохранить на дату'),
							tabIndex: TABINDEX_PEF + 49,
							iconCls: 'save16',
							id: 'PEW_SaveOnDateButton',
							handler: function() {
								this.ownerCt.ownerCt.doSaveOnDate();
							}
						}*/,
						{
							text: langs('Периодики'),
							hidden: false,
							tabIndex: TABINDEX_PEF + 63,
							id: 'PEW_PeriodicsButton',
							handler: this.showPeriodicViewWindow.createDelegate(this)
						},
						{
							text: (getRegionNick() == 'ekb')?langs('Проверка регистрационных данных'):langs('Идентификация'),
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
							text: langs('Назад'),
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
							text: langs('Вперед'),
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
	}
});