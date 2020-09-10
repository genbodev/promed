/**
* swEvnUslugaParEditWindow - окно редактирования/добавления выполнения параклинической услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Parka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-13.05.2010
* @comment      Префикс для id компонентов EUParEF (EvnUslugaParEditForm)
*
*
* @input data: action - действие (add, edit, view)
*
*
* Использует: окно поиска организации (swOrgSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnUslugaParEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaParEditWindow',
	objectSrc: '/jscore/Forms/Parka/swEvnUslugaParEditWindow.js',
	action: null,
	//autoScroll: true,
	autoHeight: false,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	id: 'EvnUslugaParEditWindow',
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaParEditWindow');

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
	maximizable: true,
	height: 550,
	width: 800,
	minHeight: 550,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	getEvnDirectionNumber: function() {
		if ( this.action == 'view' ) {
			return false;
		}
		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Получение номера направления..."});
		loadMask.show();

		Ext.Ajax.request({
			params: {
				year: (typeof base_form.findField('EvnDirection_setDate').getValue() == 'object' ? base_form.findField('EvnDirection_setDate').getValue().format('Y') : getGlobalOptions().date.substr(6, 4))
			},
			url: '/?c=EvnDirection&m=getEvnDirectionNumber',
			callback: function(options, success, response) {
				loadMask.hide();
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					base_form.findField('EvnDirection_Num').setValue(response_obj.EvnDirection_Num);
				} else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_napravleniya']);
				}
			}.createDelegate(this)
		});
	},
	callbackOnCopyWithoutPerson: function(formData) {
		if ( typeof formData != 'object' ) {
			formData = new Object();
		}

		// Если окно поиска человека уже открыто
		// TODO: Продумать использование getWnd в таких случаях
		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			if ( getWnd('swPersonSearchWindow').searchWindowOpenMode == 'EvnUslugaPar' ) {
				this.hide();
				getWnd('swPersonSearchWindow').formParams = formData;
			}
			else {
				sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
				return false;
			}
		}
		// Окно поиска человека закрыто
		else {
			var base_form = this.findById('EvnUslugaParEditForm').getForm();

			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					this.action = 'add';
					this.setTitle(WND_PARKA_EUPEFADD);

					this.findById('EUParEF_PersonInformationFrame').load({
						Person_Birthday: person_data.Person_Birthday,
						Person_Firname: person_data.Person_Firname,
						Person_Secname: person_data.Person_Secname,
						Person_Surname: person_data.Person_Surname
					});

					base_form.findField('EvnDirection_setDate').setRawValue(null);
					base_form.findField('EvnDirection_Num').setRawValue('');
					base_form.findField('EvnUslugaPar_id').setValue(0);
					base_form.findField('Person_id').setValue(person_data.Person_id);
					base_form.findField('PersonEvn_id').setValue(person_data.PersonEvn_id);
					base_form.findField('Server_id').setValue(person_data.Server_id);

					// Убираем электронное направление, если оно выбрано
					if ( base_form.findField('PrehospDirect_id').getValue() == 2 ) {
						base_form.findField('EvnDirection_id').setValue(0);
						base_form.findField('Org_did').clearValue();
					}

					getWnd('swPersonSearchWindow').hide();

					if ( base_form.findField('PrehospDirect_id').getValue() == 2 ) {
						this.findById('EUParEF_EvnDirectionSelectButton').focus();
					}
					else {
						this.buttons[2].focus();
					}
				}.createDelegate(this),
				searchMode: 'all',
				searchWindowOpenMode: 'EvnUslugaPar'
			});
		}
	},
	doCopy: function(options) {
		// options @Object
		// options.mode @String Режим создания копии выполняемой параклинической услуги
		// options.noSave @Boolean Флаг отказа от сохранения параклинической услуги

		if ( !this.is_operator || !options || typeof options != 'object' ) {
			return false;
		}

		if ( !options.mode || typeof options.mode != 'string' ) {
			return false;
		}

		if ( options.noSave == true || this.action == 'view' ) {
			// Выполняем действия по открытию полей формы в зависимости от options.mode

			var base_form = this.findById('EvnUslugaParEditForm').getForm();

			switch ( options.mode ) {
				case 'all':
					// Все поля должны быть по умолчанию заполнены аналогично предыдущее услуге с привязкой к тому же персону
					if ( this.action == 'view' ) {
						setCurrentDateTime({
							dateField: base_form.findField('EvnUslugaPar_setDate'),
							loadMask: true,
							setDate: false,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: false,
							timeField: base_form.findField('EvnUslugaPar_setTime'),
							windowId: this.id
						});
					}

					this.action = 'add';
					this.setTitle(WND_PARKA_EUPEFADD);
					this.enableEdit(true);

					base_form.findField('EvnUslugaPar_id').setValue(0);
					base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue(), base_form.findField('PrehospDirect_id').getValue());
					base_form.findField('UslugaComplex_id').clearValue();
					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
					base_form.findField('UslugaComplex_id').focus();
				break;

				case 'data':
					this.callbackOnCopyWithoutPerson({
						EvnUslugaPar_Kolvo: base_form.findField('EvnUslugaPar_Kolvo').getValue(),
						EvnUslugaPar_setDate: base_form.findField('EvnUslugaPar_setDate').getValue(),
						EvnUslugaPar_setTime: base_form.findField('EvnUslugaPar_setTime').getRawValue(),
						LpuSection_did: base_form.findField('LpuSection_did').getValue(),
						LpuSection_uid: base_form.findField('LpuSection_uid').getValue(),
						MedPersonal_did: base_form.findField('MedPersonal_did').getValue(),
						MedStaffFact_uid: base_form.findField('MedStaffFact_uid').getValue(),
						MedStaffFact_sid: base_form.findField('MedStaffFact_sid').getValue(),
						Org_did: base_form.findField('Org_did').getValue(),
						PayType_id: base_form.findField('PayType_id').getValue(),
						PrehospDirect_id: base_form.findField('PrehospDirect_id').getValue(),
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue()
					});
				break;
			}
		}
		else {
			// Сначала сохраняем
			this.doSave({
				copyMode: options.mode
			});
		}
	},
	doSaveDirectionFields: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnUslugaParEditForm').getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('MedStaffFact_did').getValue())) {
			base_form.findField('MedPersonal_did').setValue(base_form.findField('MedStaffFact_did').getFieldValue('MedPersonal_id'));
		} else {
			base_form.findField('MedPersonal_did').setValue(null);
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		Ext.Ajax.request({
			callback: function(options, success, response) {
				win.getLoadMask().hide();
				win.formStatus = 'edit';

				if (success && response.responseText != '') {
					var result  = Ext.util.JSON.decode(response.responseText);
					if (result.success) {
						win.hide();
					}
				}
			},
			params: {
				EvnUslugaPar_id: base_form.findField('EvnUslugaPar_id').getValue(),
				PrehospDirect_id: base_form.findField('PrehospDirect_id').getValue(),
				Org_did: base_form.findField('Org_did').getValue(),
				LpuSection_did: base_form.findField('LpuSection_did').getValue(),
				MedPersonal_did: base_form.findField('MedPersonal_did').getValue(),
				EvnDirection_Num: base_form.findField('EvnDirection_Num').getValue(),
				EvnDirection_setDate: base_form.findField('EvnDirection_setDate').getValue().format('d.m.Y'),
				EvnUslugaPar_IndexRep: base_form.findField('EvnUslugaPar_IndexRep').getValue(),
				EvnUslugaPar_IndexRepInReg: base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()
			},
			url: '/?c=EvnUslugaPar&m=updateEvnDirectionFields'
		});
	},
	doSave: function(options) {
		// options @Object
		// options.copyMode @String Режим создания копии выполняемой параклинической услуги

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		if (this.action == 'editEvnDirectionOnly') {
			// сохраняем только поля направления
			this.doSaveDirectionFields();
			return;
		}

		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			files_list = this.findById('EUParEF_FileList'),
			loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });

		if (this.is_protocol_edit)
		{
			var params = new Object();
			params.XmlTemplate_id = this.EvnXmlPanel.getXmlTemplateId();
			params.EvnUslugaPar_id = base_form.findField('EvnUslugaPar_id').getValue();
		}
		else
		{
			if ( !base_form.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						//log(base_form);
						//log(this.findById('EvnUslugaParEditForm'));
						//log(this.findById('EvnUslugaParEditForm').getFirstInvalidEl());
						this.findById('EvnUslugaParEditForm').getFirstInvalidEl().focus(true);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
			var evn_usluga_set_time = base_form.findField('EvnUslugaPar_setTime').getValue();

			if (!Ext.isEmpty(base_form.findField('MedStaffFact_did').getValue())) {
				base_form.findField('MedPersonal_did').setValue(base_form.findField('MedStaffFact_did').getFieldValue('MedPersonal_id'));
			} else {
				base_form.findField('MedPersonal_did').setValue(null);
			}

			if (!Ext.isEmpty(base_form.findField('MedStaffFact_uid').getValue())) {
				base_form.findField('MedPersonal_uid').setValue(base_form.findField('MedStaffFact_uid').getFieldValue('MedPersonal_id'));
			} else {
				base_form.findField('MedPersonal_uid').setValue(null);
			}

			if (!Ext.isEmpty(base_form.findField('MedStaffFact_sid').getValue())) {
				base_form.findField('MedPersonal_sid').setValue(base_form.findField('MedStaffFact_sid').getFieldValue('MedPersonal_id'));
			} else {
				base_form.findField('MedPersonal_sid').setValue(null);
			}

			var record = null;
			var params = new Object();

			if ( base_form.findField('Org_did').disabled ) {
				params.Org_did = base_form.findField('Org_did').getValue();
			}
			
			if ( base_form.findField('Diag_id').disabled ) {
				params.Diag_id = base_form.findField('Diag_id').getValue();
			}

			if ( base_form.findField('EvnDirection_Num').disabled ) {
				params.EvnDirection_Num = base_form.findField('EvnDirection_Num').getRawValue();
			}

			var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue(),
				EvnUslugaPar_setDate = base_form.findField('EvnUslugaPar_setDate').getValue();

			if ( getRegionNick() === 'ekb' && getOthersOptions().checkEvnDirectionDate && EvnUslugaPar_setDate instanceof Date &&  EvnDirection_setDate instanceof Date && EvnDirection_setDate.getTime() > EvnUslugaPar_setDate.getTime() ) {
				this.formStatus = 'edit';
				Ext.Msg.alert(langs('Ошибка'), langs('Дата выписки направления позже даты начала случая. Дата направления должна быть раньше или совпадать с датой начала случая. Проверьте дату выписки направления'));
				return false;
			}


			params.EvnDirection_setDate = Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'd.m.Y');
			params.PrehospDirect_id = base_form.findField('PrehospDirect_id').getValue();
			params.Lpu_did = base_form.findField('Lpu_did').getValue();
			params.LpuSection_did = base_form.findField('LpuSection_did').getValue();
			params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();

			loadMask.show();

			base_form.submit({
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
				params: params,
				success: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if ( action.result && action.result.EvnUslugaPar_id > 0 ) {
						base_form.findField('EvnUslugaPar_id').setValue(action.result.EvnUslugaPar_id);

						//сохраняем вкладку с файлами
						if (!files_list.disabled)
						{
							files_list.listParams = {Evn_id: action.result.EvnUslugaPar_id};
							files_list.saveChanges();
						}
						var data = new Object();
						var person_information = this.findById('EUParEF_PersonInformationFrame');

						var lpu_section_name = '';
						var med_personal_fio = '';
						var pay_type_name = '';
						var usluga_complex_code = '';
						var usluga_complex_name = '';

						record = base_form.findField('LpuSection_uid').getStore().getById(base_form.findField('LpuSection_uid').getValue());
						if ( record ) {
							lpu_section_name = record.get('LpuSection_Name');
						}

						record = base_form.findField('MedStaffFact_uid').getStore().getById(base_form.findField('MedStaffFact_uid').getValue());
						if ( record ) {
							med_personal_fio = record.get('MedPersonal_Fio');
						}

						// подставить данные комлпексной услуги
						var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue()
						base_form.findField('UslugaComplex_id').getStore().each(function(record) {
							if (record.get('UslugaComplex_id') == UslugaComplex_id){
								usluga_complex_code = record.get('UslugaComplex_Code');
								usluga_complex_name = record.get('UslugaComplex_Name');
							}
						});

						record = base_form.findField('PayType_id').getStore().getById(base_form.findField('PayType_id').getValue());
						if ( record ) {
							pay_type_name = record.get('PayType_Name');
						}

						data.evnUslugaData = {
							'accessType': 'edit',
							'archiveRecord': 0,
							'EvnUslugaPar_id': base_form.findField('EvnUslugaPar_id').getValue(),
							'EvnUslugaPar_setDate': base_form.findField('EvnUslugaPar_setDate').getValue(),
							'EvnUslugaPar_setTime': base_form.findField('EvnUslugaPar_setTime').getValue(),
							'EvnDirection_id': base_form.findField('EvnDirection_id').getValue(),
							'EvnDirection_Num': base_form.findField('EvnDirection_Num').getValue(),
							'EvnDirection_setDate': base_form.findField('EvnDirection_setDate').getValue(),
							'PrehospDirect_id': base_form.findField('PrehospDirect_id').getValue(),
							'LpuSection_did': base_form.findField('LpuSection_did').getValue(),
							'MedPersonal_did': base_form.findField('MedPersonal_did').getValue(),
							'Org_did': base_form.findField('Org_did').getValue(),
							'LpuSection_uid': base_form.findField('LpuSection_uid').getValue(),
							'MedPersonal_uid': base_form.findField('MedPersonal_uid').getValue(),
							'MedPersonal_sid': base_form.findField('MedPersonal_sid').getValue(),
							'PayType_id': base_form.findField('PayType_id').getValue(),
							'EvnUslugaPar_Kolvo': base_form.findField('EvnUslugaPar_Kolvo').getValue(),
							'TimetablePar_id': base_form.findField('TimetablePar_id').getValue(),
							'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
							'EvnUslugaPar_isCito': base_form.findField('EvnUslugaPar_isCito').getValue(),
							'LpuSection_Name': lpu_section_name,
							'MedPersonal_Fio': med_personal_fio,
							'PayType_Name': pay_type_name,
							'Person_Birthday': person_information.getFieldValue('Person_Birthday'),
							'Person_Firname': person_information.getFieldValue('Person_Firname'),
							'Person_id': base_form.findField('Person_id').getValue(),
							'Person_Secname': person_information.getFieldValue('Person_Secname'),
							'Person_Surname': person_information.getFieldValue('Person_Surname'),
							'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
							'Server_id': base_form.findField('Server_id').getValue(),
							'Usluga_Code': usluga_complex_code,
							'Usluga_Name': usluga_complex_name
						};

						if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 2) {
							data.evnUslugaData.EvnCostPrint_IsNoPrintText = lang['otkaz_ot_spravki'];
						} else if (base_form.findField('EvnCostPrint_IsNoPrint').getValue() == 1) {
							data.evnUslugaData.EvnCostPrint_IsNoPrintText = lang['spravka_vyidana'];
						} else {
							data.evnUslugaData.EvnCostPrint_IsNoPrintText = '';
						}
						data.evnUslugaData.EvnCostPrint_setDT = base_form.findField('EvnCostPrint_setDT').getValue();

						this.callback(data);
						this.onSaveUsluga(data);
						if (this.editProtocolAfterSaveUsluga || this.addProtocolAfterSaveUsluga)
						{
							//переключаем форму для редактирования протокола
							var form_panel = this.findById('EvnUslugaParEditForm');
							var tab_main = this.findById('EUParEF_MainTab');
							this.is_protocol_edit = true;
							this.action = 'editProtocol';
							this.maximize();
							tab_main.setTitle(lang['protokol_uslugi']);
							//this.enableEdit(false);
							//this.buttons[2].show();
							form_panel.hide();
							this.EvnXmlPanel.show();
							//### doSave
							var XmlTemplate_id = base_form.findField('XmlTemplate_id').getValue();
							if ( this.editProtocolAfterSaveUsluga )
							{
								this.action == 'editProtocol'
								this.setTitle(lang['paraklinicheskaya_usluga_redaktirovanie_protokola']);
								this.EvnXmlPanel.setReadOnly(false);
								this.EvnXmlPanel.setBaseParams({
									userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
									UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
									Server_id: base_form.findField('Server_id').getValue(),
									Evn_id: base_form.findField('EvnUslugaPar_id').getValue()
								});
								this.EvnXmlPanel.doLoadData();
							}
							else
							{
								this.action == 'addProtocol'
								this.setTitle(lang['paraklinicheskaya_usluga_dobavlenie_protokola']);
								this.EvnXmlPanel.doReset();
								this.EvnXmlPanel.setReadOnly(false);
								this.EvnXmlPanel.setBaseParams({
									userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
									UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
									Server_id: base_form.findField('Server_id').getValue(),
									Evn_id: base_form.findField('EvnUslugaPar_id').getValue()
								});
							}
							this.syncSize();
						}
						else if ( typeof options == 'object' && this.is_operator ) {
							this.doCopy({
								mode: options.copyMode,
								noSave: true
							});
						}
						else {
							this.hide();
						}
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}.createDelegate(this)
			});
		}
	},
	loadStoreSectionMedstafffact: function(options) {
		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_staff_fact_combo = base_form.findField('MedStaffFact_uid');
		var med_staff_fact_combo2 = base_form.findField('MedStaffFact_sid');
		// фильтры
		var section_filter_params = {};
		var medstafffact_filter_params = {};
		// на дату оказания услуги
		if (options.onDate)
		{
			section_filter_params.onDate = options.onDate;
			medstafffact_filter_params.onDate = options.onDate;
		}
		//или на конкретное место работы или на список мест работы
		if ( options.LpuSection_id && options.MedStaffFact_id )
		{
			section_filter_params.id = options.LpuSection_id;
			medstafffact_filter_params.id = options.MedStaffFact_id;
		}
		else if ( options.UserMedStaffFacts && options.UserLpuSections && options.action == 'add' )
		{
			section_filter_params.ids = options.UserLpuSections;
			medstafffact_filter_params.ids = options.UserMedStaffFacts;
		}
		setLpuSectionGlobalStoreFilter(section_filter_params);
		setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
		lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		med_staff_fact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		setLpuSectionGlobalStoreFilter();
		setMedStaffFactGlobalStoreFilter();
		med_staff_fact_combo2.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
	},
	enableEdit: function(enable) {
		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		var form_fields = new Array(
			'EvnUslugaPar_IsWithoutDirection',
			'EvnDirection_setDate',
			'EvnDirection_Num',
			'EvnUslugaPar_Kolvo',
			'EvnUslugaPar_setDate',
			'EvnUslugaPar_setTime',
			'UslugaPlace_id',
			'Lpu_uid',
			'LpuSectionProfile_id',
			'MedSpecOms_id',
			'LpuSection_did',
			'LpuSection_uid',
			'MedStaffFact_did',
			'MedStaffFact_uid',
			'MedStaffFact_sid',
			'Org_did',
			'PayType_id',
			'PrehospDirect_id',
			'UslugaCategory_id',
			'UslugaCategory_Name',
			'UslugaComplex_id',
			'FSIDI_id',
			'Diag_id',
			'DeseaseType_id',
			'TumorStage_id',
			'Mes_id',
			'UslugaComplexTariff_id',
			'MedProductCard_id'
		);
		var i;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[2].show();
			this.findById('EUParEF_EvnDirectionSelectButton').show();
			this.findById('EUParEF_ToggleVisibleDisDTBtn').show();
		}
		else {
			this.buttons[2].hide();
			this.findById('EUParEF_EvnDirectionSelectButton').hide();
			this.findById('EUParEF_ToggleVisibleDisDTBtn').hide();
		}
	},
	// тут вся логика по доступу к файлам
	enableFiles: function() {
		var files_tab = this.findById('EUParEF_FileTab'),
			files_grid = this.findById('EUParEF_FileList').FileGrid,
			tab_panel = this.findById('EUParEF_Tab');

		files_grid.removeAll();

		if (this.is_operator)
		{
			files_tab.hide();
		}
		else
		{
			files_tab.show();
		}

		if (this.action.inlist(['view', 'editEvnDirectionOnly']))
		{
			files_grid.setReadOnly(true);
		}
		else
		{
			files_grid.setReadOnly(false);
		}
		tab_panel.setActiveTab(0);
		tab_panel.getActiveTab().doLayout();
	},
	openEvnDirectionSelectWindow: function() {
		if ( this.action == 'view') {
			return false;
		}

		if ( getWnd('swEvnDirectionSelectWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_vyibora_napravleniya_uje_otkryito']);
			return false;
		}

		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		getWnd('swEvnDirectionSelectWindow').show({
			DirType_id: 10, // На исследование
			formType: 'par',
			callback: this.setDirection.createDelegate(this),
			onHide: function() {
				this.findById('EUParEF_EvnDirectionSelectButton').focus();
			}.createDelegate(this),
			Person_Birthday: this.findById('EUParEF_PersonInformationFrame').getFieldValue('Person_Birthday'),
			Person_Firname: this.findById('EUParEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Secname: this.findById('EUParEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname: this.findById('EUParEF_PersonInformationFrame').getFieldValue('Person_Surname')
		});
	},
	setDirection: function(data) {
		var win = this;
		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		var iswd_combo = base_form.findField('EvnUslugaPar_IsWithoutDirection');
		var PrehospDirect_id = (data.PrehospDirect_id || (data.Lpu_id != getGlobalOptions().lpu_id ? 2 : 1));

		base_form.findField('EvnDirection_Num').setValue('');
		base_form.findField('EvnDirection_setDate').setValue('');
		base_form.findField('LpuSection_did').clearValue();
		base_form.findField('MedStaffFact_did').clearValue();
		base_form.findField('Org_did').clearValue();

		base_form.findField('PrehospDirect_id').setValue(PrehospDirect_id);
		iswd_combo.setValue(2);
		var iswd_index = iswd_combo.getStore().find(iswd_combo.valueField, iswd_combo.getValue());
		var iswd_record = iswd_combo.getStore().getAt(iswd_index);
		iswd_combo.fireEvent('select', iswd_combo, iswd_record, iswd_index);

		if (!Ext.isEmpty(data.Diag_did)) {
			base_form.findField('DirectionDiag_id').setValue(data.Diag_did);

			if (getRegionNick().inlist(['buryatiya','kareliya', 'adygeya', 'yakutiya']) && iswd_combo.getValue() == 2) {
				base_form.findField('Diag_id').getStore().load({
					callback: function() {
						console.log(data.Diag_did);
						if (base_form.findField('Diag_id').getStore().getCount() > 0) {
							base_form.findField('Diag_id').setValue(data.Diag_did)
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
							base_form.findField('Diag_id').onChange();	
						}
					},
					params: {where: "where Diag_id = " + data.Diag_did}
				});
			}

		} else {
			base_form.findField('DirectionDiag_id').setValue(null);
		}

		if (!Ext.isEmpty(data.EvnDirection_id)) {
			base_form.findField('EvnDirection_id').setValue(data.EvnDirection_id);
		} else {
			base_form.findField('EvnDirection_id').setValue(null);
		}

		if ( !Ext.isEmpty(data.EvnDirection_id) ) {
			base_form.findField('EvnDirection_Num').setDisabled(true);
			base_form.findField('EvnDirection_setDate').setDisabled(true);
			base_form.findField('LpuSection_did').setDisabled(true);
			base_form.findField('MedStaffFact_did').setDisabled(true);
			base_form.findField('Org_did').setDisabled(true);
			base_form.findField('Diag_id').setDisabled(true);
		}
		else {
			base_form.findField('EvnDirection_Num').setDisabled(this.action == 'view');
			base_form.findField('EvnDirection_setDate').setDisabled(this.action == 'view');
			base_form.findField('LpuSection_did').setDisabled(this.action == 'view');
			base_form.findField('MedStaffFact_did').setDisabled(this.action == 'view');
			base_form.findField('Org_did').setDisabled(this.action == 'view');
			base_form.findField('Diag_id').setDisabled(this.action == 'view');
		}

		switch ( parseInt(PrehospDirect_id) ) {
			case 1:
				if ( !Ext.isEmpty(data.LpuSection_id) ) {
					//устанавливаем отделение и дизаблим поле
					base_form.findField('LpuSection_did').setValue(data.LpuSection_id);
				}
				break;

			case 2:
				if (!Ext.isEmpty(data.Org_did)) {
					win.getLoadMask(LOAD_WAIT).show();
					base_form.findField('Org_did').setValue(data.Org_did);
					base_form.findField('Org_did').getStore().load({
						callback: function (records, options, success) {
							win.getLoadMask(LOAD_WAIT).hide();
							if (success) {
								base_form.findField('Org_did').setValue(data.Org_did);
								win.setLpuSectionAndMedStaffFactFilter({
									LpuSection_id: data.LpuSection_id,
									MedPersonal_id: data.MedPersonal_id
								});
							}
						}.createDelegate(this),
						params: {
							Org_id: data.Org_did,
							OrgType: 'lpu'
						}
					});
				}
				break;
		}

		if ( !Ext.isEmpty(data.EvnDirection_Num) ) {
			base_form.findField('EvnDirection_Num').setValue(data.EvnDirection_Num);
		}

		if ( !Ext.isEmpty(data.EvnDirection_setDate) ) {
			base_form.findField('EvnDirection_setDate').setValue(data.EvnDirection_setDate);
		}

		if ( !Ext.isEmpty(data.UslugaComplex_id) ) {
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function() {
					base_form.findField('UslugaComplex_id').setValue(data.UslugaComplex_id);
					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				},
				params: {UslugaComplex_id: data.UslugaComplex_id}
			});
		}
	},
	setDirectionHistologicOrCytologic: function(data) {
		var protocol = null; var params = {};
		if(!data || !parseInt(data.EvnDirection_id)) return false;
		if(data.EvnUslugaPar_IsHistologic && data.EvnUslugaPar_IsHistologic == 2) {
			protocol = 'Histologic';
			params = {EvnDirectionHistologic_id: data.EvnDirection_id};
		}else if(data.EvnUslugaPar_IsCytologic && data.EvnUslugaPar_IsCytologic == 2){
			protocol = 'Cytologic';
			params = {EvnDirectionCytologic_id: data.EvnDirection_id};
		}else{
			return false;
		}

		// *** NGS - FILL SERVICE PLACE FIELDS FUNCTION - START ***
		function fillServicePlaceFields(servicePlaceIsDefined, servicePlaceID, checkboxServicePlace, currentLpuID, currentLpuSectionID, currentPersonID, currentLpuSectionProfileID) {
			// set actual value to the service place combobox
			servicePlaceIsDefined = servicePlaceID ? true : false;
			checkboxServicePlace.setValue(servicePlaceIsDefined ? servicePlaceID : 1);
			checkboxServicePlace.fireEvent('change', checkboxServicePlace, checkboxServicePlace.getValue());

			// *** NGS - FILL DATA FOR DIFFERENT SERVICE PLACES - START ***
			if (servicePlaceID && servicePlaceIsDefined) {
				// *** "ОТДЕЛЕНИЕ ЛПУ" ***
				if ([1].includes(servicePlaceID)) {
					// Место выполнения - Отделение
					!currentLpuSectionID || base_form.findField('LpuSection_uid').getStore().findBy(function (rec) {
						if (rec.json.LpuSection_id == currentLpuSectionID) {
							base_form.findField('LpuSection_uid').setValue(currentLpuSectionID);
							base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
						}
					});

					// Место выполнения - Профиль
					!currentLpuSectionProfileID || base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: (records, operation, success) => {
							if(success) {
								base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
									if (rec.json.LpuSectionProfile_id == currentLpuSectionProfileID) {
										base_form.findField('LpuSectionProfile_id').setValue(currentLpuSectionProfileID);
										base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									}
								});
							}
						}
					})

					// Место выполнения - Врач
					base_form.findField('MedStaffFact_uid').getStore().load({
						callback: (records, operation, success) => {
							if (success) {
								var isFound = false; // in order to stop finding person if it's already found (there are repeated persons by person_id - copied users)
								!currentPersonID || base_form.findField('MedStaffFact_uid').getStore().findBy(function (rec) {
									if (rec.json.Person_id == currentPersonID && !isFound) {
										isFound = true;
										base_form.findField('MedStaffFact_uid').setValue(rec.json.MedStaffFact_id);
										base_form.findField('MedStaffFact_uid').fireEvent('change', base_form.findField('MedStaffFact_uid'), base_form.findField('MedStaffFact_uid').getValue());
									}
								});
							}
						}
					});
				}

				// *** "ДРУГАЯ ОРГАНИЗАЦИЯ"  ИЛИ "ДРУГОЕ ЛПУ" *** 
				if ([2,3].includes(servicePlaceID)) {
					// Место выполнения - МО
					servicePlaceID === 2 && base_form.findField('Lpu_uid').getStore().findBy(function (rec) {
						if (rec.get('Lpu_id') == currentLpuID) {
							base_form.findField('Lpu_uid').setValue(rec.get('Lpu_id'));
							base_form.findField('Lpu_uid').fireEvent('change', base_form.findField('Lpu_uid'), base_form.findField('Lpu_uid').getValue());
						}
					});

					// Место выполнения - Другая организация
					servicePlaceID === 3 && base_form.findField('Org_uid').getStore().findBy(function (rec) {
						if (rec.json.Lpu_id == currentLpuID) {
							base_form.findField('Org_uid').setValue(rec.json.Org_id);
							base_form.findField('Org_uid').fireEvent('change', base_form.findField('Org_uid'), base_form.findField('Org_uid').getValue());
						}
					});

					// Место выполнения - Отделение
					!currentLpuSectionID || base_form.findField('LpuSection_uid').getStore().findBy(function (rec) {
						if (rec.json.LpuSection_id == currentLpuSectionID) {
							base_form.findField('LpuSection_uid').setValue(currentLpuSectionID);
							base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), base_form.findField('LpuSection_uid').getValue());
						}
					});

					// Место выполнения - Профиль
					!currentLpuSectionProfileID || base_form.findField('LpuSectionProfile_id').getStore().load({
						callback: (records, operation, success) => {
							if(success) {
								base_form.findField('LpuSectionProfile_id').getStore().findBy(function (rec) {
									if (rec.json.LpuSectionProfile_id == currentLpuSectionProfileID) {
										base_form.findField('LpuSectionProfile_id').setValue(currentLpuSectionProfileID);
										base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									}
								});
							}
						}
					})

					// Место выполнения - Врач
					base_form.findField('MedStaffFact_uid').getStore().load({
						callback: (records, operation, success) => {
							if (success) {
								var isFound = false; // in order to stop finding person if it's already found (there are repeated persons by person_id - copied users)
								!currentPersonID || base_form.findField('MedStaffFact_uid').getStore().findBy(function (rec) {
									if (rec.json.Person_id == currentPersonID && !isFound) {
										isFound = true;
										base_form.findField('MedStaffFact_uid').setValue(rec.json.MedStaffFact_id);
										base_form.findField('MedStaffFact_uid').fireEvent('change', base_form.findField('MedStaffFact_uid'), base_form.findField('MedStaffFact_uid').getValue());

										// Место выполнения - Специальность
										var currentPersonSpeciality = rec.json.MedSpecOms_id;
										!currentPersonSpeciality || base_form.findField('MedSpecOms_id').getStore().load({
											callback: (records, operation, success) => {
												if (success) {
													base_form.findField('MedSpecOms_id').getStore().findBy(function (rec) {
														if (rec.json.MedSpecOms_id == currentPersonSpeciality) {
															base_form.findField('MedSpecOms_id').setValue(rec.json.MedSpecOms_id);
															base_form.findField('MedSpecOms_id').fireEvent('change', base_form.findField('MedSpecOms_id'), base_form.findField('MedSpecOms_id').getValue());
														}
													});
												}
											}
										});
									}
								});
							}
						}
					});
				}
			}
			// *** NGS - FILL DATA FOR DIFFERENT SERVICE PLACES - END ***
		}
		// *** NGS - FILL SERVICE PLACE FIELDS FUNCTION - END ***

		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		base_form.findField('EvnDirection_id').setValue(data.EvnDirection_id);
		Ext.Ajax.request({
			failure: function(response, options) {
				sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {this.hide();}.createDelegate(this) );
			},
			success: function(response, options) {
				if (!Ext.isEmpty(response.responseText)) {

					var response_obj = Ext.util.JSON.decode(response.responseText);
					var PrehospDirect_id = (data.PrehospDirect_id || (response_obj[0].Lpu_id != getGlobalOptions().lpu_id ? 2 : 1));
					base_form.findField('PrehospDirect_id').setValue(PrehospDirect_id);	
					//base_form.findField('EvnDirection_setDate').setValue(response_obj[0].EvnDirectionHistologic_setDate);
					base_form.findField('EvnDirection_setDate').setValue(response_obj[0]['EvnDirection' + protocol + '_setDate']);
					//base_form.findField('EvnDirection_Num').setValue(response_obj[0].EvnDirectionHistologic_Ser + ' ' + response_obj[0].EvnDirectionHistologic_Num);
					base_form.findField('EvnDirection_Num').setValue(response_obj[0]['EvnDirection' + protocol + '_Ser'] + ' ' + response_obj[0]['EvnDirection' + protocol + '_Num']);

					// *** NGS: DEFINE SERVICE PLACE - START ***
					// auto filling of service place fields is available only for MSK region
					if (['msk'].includes(getGlobalOptions().region.nick)) {
						var currentLpuID = getGlobalOptions().lpu_id,
							currentLpuSectionID = getGlobalOptions().CurLpuSection_id,
							currentPersonID = getGlobalOptions().person_id,
							currentLpuSectionProfileID = getGlobalOptions().CurLpuSectionProfile_id,
							respLpuAID = response_obj[0].Lpu_aid,
							checkboxLpuUID = base_form.findField('Lpu_uid'),
							checkboxOrgUID = base_form.findField('Org_uid'),
							checkboxServicePlace = base_form.findField('UslugaPlace_id'),
							servicePlaceIsDefined = false;

						servicePlaceID = respLpuAID && currentLpuID && respLpuAID == currentLpuID ? 1 : null;
						!servicePlaceID || fillServicePlaceFields(servicePlaceIsDefined, servicePlaceID, checkboxServicePlace, currentLpuID, currentLpuSectionID, currentPersonID, currentLpuSectionProfileID);

						// definition of the service place
						if (!servicePlaceID) {
							// *** CALLBACK 1
							checkboxLpuUID.getStore().load({
								params: {
									Lpu_id: currentLpuID,
									mode: 'combo'
								},
								callback: function (records, options, success) {

									if (success && !servicePlaceID) {
										checkboxLpuUID.getStore().findBy(function (rec) {
											if (rec.get('Lpu_id') == currentLpuID) {
												servicePlaceID = 2;
												fillServicePlaceFields(servicePlaceIsDefined, servicePlaceID, checkboxServicePlace, currentLpuID, currentLpuSectionID, currentPersonID, currentLpuSectionProfileID);
											}
										});
									}

								}
							});
							// CALLBACK 1 ***

							// *** CALLBACK 2
							checkboxOrgUID.getStore().load({
								params: {
									OrgType: 'lpu'
								},
								callback: function (records, options, success) {
									if (success && !servicePlaceID) {
										checkboxOrgUID.getStore().reader.jsonData.forEach(element => {
											if (element.Lpu_id == currentLpuID) {
												servicePlaceID = 3;
												fillServicePlaceFields(servicePlaceIsDefined, servicePlaceID, checkboxServicePlace, currentLpuID, currentLpuSectionID, currentPersonID, currentLpuSectionProfileID);
											}
										}
										);
									}
								}
							});
							// CALLBACK 2 ***
						}
					}
					// *** NGS: DEFINE SERVICE PLACE - END ***


					// *** NGS DEFINE DIRECTION PLACE - START *** 
					base_form.findField('Org_did').getStore().load({
						callback: function(records, options, success) {
							if ( success ) {								
								base_form.findField('Org_did').getStore().findBy(function(rec) {
									if(base_form.findField('PrehospDirect_id').getValue() == 2){
										if(rec.get('Lpu_id') == response_obj[0].Lpu_id){
											base_form.findField('Org_did').setValue(rec.get('Org_id'));
											return true;
										}
									}
									else{
										if ( rec.get('Lpu_id') == response_obj[0].Lpu_aid ) {
											base_form.findField('Org_did').setValue(rec.get('Org_id'));
											return true;
										}
									}
								});
							}


							base_form.findField('LpuSection_did').getStore().load({
								params: {
									Lpu_id: response_obj[0].Lpu_id,
									mode: 'combo'
								},
								callback: function() {
									base_form.findField('LpuSection_did').setValue(response_obj[0].LpuSection_did);
								}
							});



							base_form.findField('MedStaffFact_did').getStore().load({
								params: {
									Lpu_id: response_obj[0].Lpu_id,
									mode: 'combo'
								},
								callback: function() {
									base_form.findField('MedStaffFact_did').getStore().findBy(function(rec) {
										if (rec.get('MedPersonal_id') == response_obj[0].MedPersonal_id && rec.get('LpuSection_id') == response_obj[0].LpuSection_did) {
											base_form.findField('MedStaffFact_did').setValue(rec.get('MedStaffFact_id'));
										}
									});
								}
							});
						},
						params: {
							Lpu_id: response_obj[0].Lpu_id,
							OrgType: 'lpu'
						}
					});
					// *** NGS DEFINE DIRECTION PLACE - START *** 
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=EvnDirection' + protocol + '&m=loadEvnDirection' + protocol + 'EditForm'
		});
		this.disableAllDirectionFields();
	},
	disableAllDirectionFields: function() {
		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		base_form.findField('EvnUslugaPar_IsWithoutDirection').disable();
		base_form.findField('PrehospDirect_id').disable();
		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_setDate').disable();
		base_form.findField('LpuSection_did').disable();
		base_form.findField('MedStaffFact_did').disable();
		base_form.findField('Org_did').disable();
		base_form.findField('Diag_id').disable();
		this.findById('EUParEF_EvnDirectionSelectButton').disable();		
	},
	filterLpuCombo: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaParEditForm').getForm();
		// фильтр на МО (отображать только открытые действующие)
		var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if ( !Ext.isEmpty(base_form.findField('EvnUslugaPar_setDate').getValue()) ) {
			curDate = base_form.findField('EvnUslugaPar_setDate').getValue();
		}
		base_form.findField('Lpu_uid').lastQuery = '';
		base_form.findField('Lpu_uid').getStore().clearFilter();
		base_form.findField('Lpu_uid').setBaseFilter(function(rec, id) {
			if (!Ext.isEmpty(rec.get('Lpu_EndDate'))) {
				var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');
				if (lpuEndDate < curDate) {
					return false;
				}
			}
			if (win.action && win.action.inlist(['add', 'edit']) && !Ext.isEmpty(getGlobalOptions().lpu_id) && rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}
			return true;
		});
	},
	loadLpuData: function(data) {
		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			_this = this,
			Lpu_id = base_form.findField('Lpu_uid').getValue(),
			UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue(),
			MedStaffFact_uid = base_form.findField('MedStaffFact_uid').getValue(),
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
			
		base_form.findField('MedProductCard_id').getStore().load({
			params: {Lpu_id: (!Ext.isEmpty(Lpu_id) && !Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id.inlist([2, 3])) ? base_form.findField('Lpu_uid').getValue() : getGlobalOptions().lpu_id},
			callback: function() {
				_this.filterMedProductCard();
			}
		});

		//Фильтруем поле отделение по профилю и МО
		if (!Ext.isEmpty(Lpu_id) && !Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id.inlist([2, 3])) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
			loadMask.show();
			base_form.findField('LpuSection_uid').getStore().baseParams.Lpu_id = Lpu_id;
			base_form.findField('LpuSection_uid').getStore().baseParams.mode = 'addSubProfile';
			base_form.findField('LpuSection_uid').getStore().removeAll();
			base_form.findField('LpuSection_uid').lastQuery = 'The string that will never appear';
			base_form.findField('LpuSection_uid').getStore().load({
                callback: function() {
					//грузим данные по врачам
					base_form.findField('MedStaffFact_uid').getStore().baseParams.Lpu_id = Lpu_id;
					base_form.findField('MedStaffFact_uid').getStore().baseParams.mode = 'addSubProfile';
					base_form.findField('MedStaffFact_uid').getStore().baseParams.isDoctor = 1;
					base_form.findField('MedStaffFact_uid').getStore().baseParams.andWithoutLpuSection = 3;
					base_form.findField('MedStaffFact_uid').getStore().removeAll();
					base_form.findField('MedStaffFact_uid').lastQuery = 'The string that will never appear';
					base_form.findField('MedStaffFact_uid').getStore().load({
						callback: function() {
							base_form.findField('MedStaffFact_sid').getStore().baseParams.Lpu_id = Lpu_id;
							base_form.findField('MedStaffFact_sid').getStore().baseParams.mode = 'addSubProfile';
							if (_this.action != 'view') {
								base_form.findField('MedStaffFact_sid').getStore().baseParams.isMidMedPersonal = 1;
							}
							base_form.findField('MedStaffFact_sid').getStore().baseParams.andWithoutLpuSection = 3;
							base_form.findField('MedStaffFact_sid').getStore().removeAll();
							base_form.findField('MedStaffFact_sid').lastQuery = 'The string that will never appear';
							base_form.findField('MedStaffFact_sid').getStore().load({
								callback: function() {
									loadMask.hide();
									if (data && !Ext.isEmpty(data.LpuSection_uid)) {
										base_form.findField('LpuSection_uid').setValue(data.LpuSection_uid)
									}
									if (data && !Ext.isEmpty(data.MedPersonal_uid)) {
										base_form.findField('MedStaffFact_uid').getStore().findBy(function(rec) {
											if ( rec.get('MedPersonal_id') == data.MedPersonal_uid ) {
												base_form.findField('MedStaffFact_uid').setValue(rec.get('MedStaffFact_id'));
												return true;
											}
											return false;
										});
									} else if (data && !Ext.isEmpty(data.MedStaffFact_uid)) {
										base_form.findField('MedStaffFact_uid').setValue(data.MedStaffFact_uid);
									}
									if (data && !Ext.isEmpty(data.MedPersonal_sid)) {
										base_form.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
											if ( rec.get('MedPersonal_id') == data.MedPersonal_sid ) {
												base_form.findField('MedStaffFact_sid').setValue(rec.get('MedStaffFact_id'));
												return true;
											}
											return false;
										});
									} else if (data && !Ext.isEmpty(data.MedStaffFact_sid)) {
										base_form.findField('MedStaffFact_sid').setValue(data.MedStaffFact_sid);
									}
									_this.filterLpuSection();
								}
							});
						}
					});
				}
			});
		} else {
			base_form.findField('LpuSection_uid').clearValue();
			base_form.findField('LpuSection_uid').getStore().removeAll();
			base_form.findField('MedStaffFact_uid').clearValue();
			base_form.findField('MedStaffFact_uid').getStore().removeAll();
			base_form.findField('MedStaffFact_sid').clearValue();
			base_form.findField('MedStaffFact_sid').getStore().removeAll();
		}
	},
	filterLpuSection: function() {
		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			Lpu_id = base_form.findField('Lpu_uid').getValue(),
			MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue(),
			UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue(),
			LpuSection_uid_field = base_form.findField('LpuSection_uid'),
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		//Фильтруем отделения и врачей
		if (!Ext.isEmpty(Lpu_id) && !Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id.inlist([2, 3])) {

			LpuSection_uid_field.getStore().clearFilter();
			var LpuSection_uid = LpuSection_uid_field.getValue();

			if (!Ext.isEmpty(LpuSectionProfile_id)){
				LpuSection_uid_field.setBaseFilter(function(rec){
					return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id || (!Ext.isEmpty(rec.get('LpuSectionLpuSectionProfileList')) && LpuSectionProfile_id.inlist(rec.get('LpuSectionLpuSectionProfileList').split(','))))
				});

				checkValueInStore(base_form, 'LpuSection_uid', 'LpuSection_id', LpuSection_uid);
			}
			//Фильтруем записи по врачам
			this.filterMedStaff();
		}
	},
	filterMedStaff: function() {
		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue(),
			UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue(),
			Lpu_id = base_form.findField('Lpu_uid').getValue(),
			MedStaffFact_uid_field = base_form.findField('MedStaffFact_uid'),
			MedStaffFact_uid = MedStaffFact_uid_field.getValue(),
			MedPersonal_uid = MedStaffFact_uid_field.getFieldValue('MedPersonal_id'),
			MedStaffFact_sid_field = base_form.findField('MedStaffFact_sid'),
			MedStaffFact_sid = MedStaffFact_sid_field.getValue(),
			MedPersonal_sid = MedStaffFact_sid_field.getFieldValue('MedPersonal_id'),
			LpuSection_uid_field = base_form.findField('LpuSection_uid'),
			LpuSection_uid = base_form.findField('LpuSection_uid').getValue(),
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

		if (!Ext.isEmpty(UslugaPlace_id) && UslugaPlace_id.inlist([2, 3])) {
			if (!Ext.isEmpty(Lpu_id)) {
				/*MedStaffFact_uid_field.getStore().clearFilter();
				MedStaffFact_sid_field.getStore().clearFilter();*/

				var LpuSectionRecords = [];
				LpuSection_uid_field.getStore().each(function (rec) {
					LpuSectionRecords.push(rec.get('LpuSection_id'));
				});

				MedStaffFact_uid_field.setBaseFilter(function (rec) {
					if (!Ext.isEmpty(LpuSection_uid) && !Ext.isEmpty(MedSpecOms_id)) {
						log((rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id') == LpuSection_uid));
						return (rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id') == LpuSection_uid);
					} else if (!Ext.isEmpty(LpuSection_uid)) {
						return ( rec.get('LpuSection_id') == LpuSection_uid);
					} else if (!Ext.isEmpty(MedSpecOms_id)) {
						log(( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id').inlist(LpuSectionRecords)));
						return ( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id').inlist(LpuSectionRecords));
					} else {
						return (rec.get('LpuSection_id').inlist(LpuSectionRecords));
					}
				});

				checkValueInStore(base_form, 'MedStaffFact_uid', 'MedStaffFact_id', MedStaffFact_uid);
				if (Ext.isEmpty(MedStaffFact_uid_field.getValue())) {
					checkValueInStore(base_form, 'MedStaffFact_uid', 'MedPersonal_id', MedPersonal_uid);
				}

				MedStaffFact_sid_field.setBaseFilter(function (rec) {
					if (!Ext.isEmpty(LpuSection_uid) && !Ext.isEmpty(MedSpecOms_id)) {
						return ( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id') == LpuSection_uid);
					} else if (!Ext.isEmpty(LpuSection_uid)) {
						return ( rec.get('LpuSection_id') == LpuSection_uid);
					} else if (!Ext.isEmpty(MedSpecOms_id)) {
						return ( rec.get('MedSpecOms_id') == MedSpecOms_id && rec.get('LpuSection_id').inlist(LpuSectionRecords));
					} else {
						return ( rec.get('LpuSection_id').inlist(LpuSectionRecords));
					}
				});

				checkValueInStore(base_form, 'MedStaffFact_sid', 'MedStaffFact_id', MedStaffFact_sid);
				if (Ext.isEmpty(MedStaffFact_sid_field.getValue())) {
					checkValueInStore(base_form, 'MedStaffFact_sid', 'MedPersonal_id', MedPersonal_sid);
				}
			} else {
				base_form.findField('MedStaffFact_uid').getStore().removeAll();
				base_form.findField('MedStaffFact_sid').getStore().removeAll();
			}
		}
	},
	filterMedProductCard: function() {
		
		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			MedProductCard = base_form.findField('MedProductCard_id'),
			LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		
		MedProductCard.clearValue();
		MedProductCard.getStore().clearFilter();
		if (!Ext.isEmpty(LpuSection_uid)) {
			MedProductCard.getStore().filterBy(function(rec) {
				return (rec.get('LpuSection_id') == LpuSection_uid);
			});
		}
	},
	filterProfile: function() {
		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			Lpu = base_form.findField('LpuSection_uid'),
			Profile = base_form.findField('LpuSectionProfile_id'),
			Profile_id = Lpu.getFieldValue('LpuSectionProfile_id'),
			Profile_list = Lpu.getFieldValue('LpuSectionLpuSectionProfileList');

		//сначало чистим
		Profile.setValue(null);

		if (!Ext.isEmpty(Profile_id)) {
			Profile.getStore().findBy(function(rec) {
				return (rec.get('LpuSectionProfile_id') === Profile_id);
			});

			Profile.getStore().findBy(function(rec) {
				if ( rec.get('LpuSectionProfile_id') === Profile_id ) {
					Profile.setValue(Profile_id);
					return true;
				}
				return false;
			});

			if (Ext.isEmpty(Profile.getValue())) {
				Profile.getStore().findBy(function(rec) {
					if (!Ext.isEmpty(Profile_list)
						&& rec.get('LpuSectionProfile_id').inlist(Profile_list.split(','))) {
						Profile.setValue(rec.get('LpuSectionProfile_id'));
						return true;
					}
					return false;
				});
			}
		}
	},
	initComponent: function() {
		var cur_wnd = this;
		
		this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			//style: "margin: 10px 0 10px 0",
			bodyStyle: 'padding-top: 0.5em;',
			border: true,
			collapsible: false,
			id: 'EUParEF_TemplPanel',
			layout: 'form',
			style: 'margin-bottom: 0.5em;',
			title: 'Протокол параклинической услуги',
			loadMask: {},
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
				EvnClass_id: 47 // документы и шаблоны только категории параклинические услуги
			},
			onAfterLoadData: function(panel){
				var bf = this.findById('EvnUslugaParEditForm').getForm();
				bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
				panel.expand();
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			onAfterClearViewForm: function(panel){
				var bf = this.findById('EvnUslugaParEditForm').getForm();
				bf.findField('XmlTemplate_id').setValue(null);
			}.createDelegate(this),
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = this.findById('EvnUslugaParEditForm').getForm();
				var evn_id_field = base_form.findField('EvnUslugaPar_id');
				var evn_id = evn_id_field.getValue();
				if (evn_id && evn_id > 0) {
					// услуга была создана ранее
					// все базовые параметры уже должно быть установлены
					panel[method](params);
				} else {
					this.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: evn_id_field.getValue()
							});
							panel[method](params);
						}.createDelegate(this)
					});
				}
				return true;
			}.createDelegate(this)
		});
		
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doCopy({
						mode: 'all'
					});
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnUslugaParEditForm').getForm().findField('EvnUslugaPar_Kolvo').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUPAREF + 95,
				text: 'Копия все'
			}, {
				handler: function() {
					this.doCopy({
						mode: 'data'
					});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EUPAREF + 96,
				text: 'Копия без пациента'
			},{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EUPAREF + 97,
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
						this.buttons[2].focus();
					}
					else {
						this.buttons[1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnUslugaParEditForm').getForm().findField('PrehospDirect_id').focus(true);
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUPAREF + 98,
				text: BTN_FRMCANCEL
			}],
			layout: 'border',
			items: [
				new sw.Promed.PersonInfoPanel({
					button1OnHide: function() {
						if ( this.action == 'view' ) {
							this.buttons[this.buttons.length - 1].focus();
						}
						else {
							this.findById('EvnUslugaParEditForm').getForm().findField('EvnUslugaPar_IsWithoutDirection').focus(true);
						}
					}.createDelegate(this),
					button2Callback: function(callback_data) {
						var form = this.findById('EvnUslugaParEditForm');
						var EvnUslugaPar_id = form.getForm().findField('EvnUslugaPar_id').getValue();
						var p = {};
						if(EvnUslugaPar_id > 0 && form.getForm().findField('Person_id').getValue()==callback_data.Person_id) {
							Ext.Ajax.request({
								 failure: function(response, options) {
									sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {this.hide();}.createDelegate(this) );
								},
								success: function(response, options) {
									if (!Ext.isEmpty(response.responseText)) {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										if ( response_obj.success == false ) {
											form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
											form.getForm().findField('Server_id').setValue(callback_data.Server_id);
											p = {Person_id: callback_data.Person_id, Server_id: callback_data.Server_id};
											if (callback_data.PersonEvn_id>0)
												p.PersonEvn_id = callback_data.PersonEvn_id;
											if (callback_data.Server_id>=0)
												p.Server_id =callback_data.Server_id;
											this.findById('EUParEF_PersonInformationFrame').load(p);
										} else if (response_obj[0].PersonEvn_id > 0) {
											form.getForm().findField('PersonEvn_id').setValue(response_obj[0].PersonEvn_id);
											form.getForm().findField('Server_id').setValue(response_obj[0].Server_id);
											p = {
												Person_id: callback_data.Person_id,
												Server_id: response_obj[0].Server_id,
												PersonEvn_id:response_obj[0].PersonEvn_id,
												Evn_setDT:form.getForm().findField('EvnUslugaPar_setDate').getValue()
											};
											this.findById('EUParEF_PersonInformationFrame').load(p);
										}
									}
								}.createDelegate(this),
								params: {
									Evn_id: evn_pl_id
								},
								url: '/?c=Person&m=getPersonEvnIdByEvnId'
							});
						} else {
							p.Person_id = form.getForm().findField('Person_id').getValue();
							this.findById('EUParEF_PersonInformationFrame').load(p);
						}
						 //или прямо form.PersonEvn_id
					}.createDelegate(this),
					button2OnHide: function() {
						this.findById('EUParEF_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					button3OnHide: function() {
						this.findById('EUParEF_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					button4OnHide: function() {
						this.findById('EUParEF_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					button5OnHide: function() {
						this.findById('EUParEF_PersonInformationFrame').button1OnHide();
					}.createDelegate(this),
					collapsible: true,
					collapsed: true,
					collectAdditionalParams: function(winType) {
						var params = new Object();

						/*switch ( winType ) {
							case 5:
								params.Diag_id = null;
								params.LpuSection_id = null;
								params.MedPersonal_id = null;

								var evn_vizit_pl_set_date = null;

								this.findById('EPLEF_EvnVizitPLGrid').getStore().each(function(rec) {
									if ( evn_vizit_pl_set_date == null || evn_vizit_pl_set_date < getValidDT(Ext.util.Format.date(rec.get('EvnVizitPL_setDate'), 'd.m.Y'), rec.get('EvnVizitPL_setTime')) ) {
										evn_vizit_pl_set_date = getValidDT(Ext.util.Format.date(rec.get('EvnVizitPL_setDate'), 'd.m.Y'), rec.get('EvnVizitPL_setTime'));

										params.Diag_id = rec.get('Diag_id');
										params.LpuSection_id = rec.get('LpuSection_id');
										params.MedPersonal_id = rec.get('MedPersonal_id');
									}
								}.createDelegate(this));
							break;
						}*/

						return params;
					}.createDelegate(this),
					floatable: false,
					id: 'EUParEF_PersonInformationFrame',
					plugins: [ Ext.ux.PanelCollapsedTitle ],
					region: 'north',
					title: '<div>Загрузка...</div>',
					titleCollapse: true
				}),
			new Ext.TabPanel({
				id: 'EUParEF_Tab',
				activeTab: 0,
				//autoScroll: true,
				//autoHeight: true,
				//bodyStyle:'padding:0px;',
				layoutOnTabChange: true,
				listeners: {
					'tabchange': function(panel, tab) {
						var Evn_id = cur_wnd.findById('EvnUslugaParEditForm').getForm().findField('EvnUslugaPar_id').getValue();
						//log('tabchange '+ Evn_id + ' ' + tab.id);
						//log(cur_wnd.show_complete);
						//log(cur_wnd.files_was_loaded);
						if (!Ext.isEmpty(Evn_id) && Evn_id > 0 && tab.id == 'EUParEF_FileTab' && cur_wnd.show_complete && !cur_wnd.files_was_loaded)
						{
							//загружаем файлы при открытии руками вкладки файлы и список не был загружен
							var files_cmp = cur_wnd.findById('EUParEF_FileList');
							files_cmp.listParams = { Evn_id: Evn_id };
							files_cmp.loadData({ Evn_id: Evn_id });
							cur_wnd.files_was_loaded = true;
						}
					}
				},
				border: false,
				region: 'center',
				items: [{
					title: 'Информация об услуге',
					id: 'EUParEF_MainTab',
					border: false,
					//autoHeight: true,
					autoScroll: true,
					items: [
						new Ext.form.FormPanel({
							bodyBorder: false,
							bodyStyle: 'padding: 5px 5px 0',
							border: false,
							frame: false,
							id: 'EvnUslugaParEditForm',
							labelAlign: 'right',
							labelWidth: 200,
							layout: 'form',
							reader: new Ext.data.JsonReader({
								success: Ext.emptyFn
							}, [
								{ name: 'accessType' },
								{ name: 'fromMedService' },
								{ name: 'EvnUslugaPar_IsPaid' },
								{ name: 'EvnUslugaPar_IndexRep' },
								{ name: 'EvnUslugaPar_IndexRepInReg' },
								{ name: 'EvnDirection_id' },
								{ name: 'EvnDirectionHistologic_id' },
								{ name: 'EvnDirection_Num' },
								{ name: 'EvnDirection_setDate' },
								{ name: 'UslugaCategory_Name' },
								{ name: 'UslugaComplex_id' },
								{ name: 'FSIDI_id'},
								{ name: 'DirectionDiag_id' },
								{ name: 'Diag_id' },
								{ name: 'DeseaseType_id' },
								{ name: 'TumorStage_id' },
								{ name: 'Mes_id' },
								{ name: 'UslugaComplexTariff_id' },
								{ name: 'UslugaCategory_id' },
								{ name: 'XmlTemplate_id' },
								{ name: 'EvnUslugaPar_id' },
								{ name: 'EvnUslugaPar_isCito' },
								{ name: 'TimetablePar_id' },
								{ name: 'EvnUslugaPar_Kolvo' },
								{ name: 'EvnUslugaPar_setDate' },
								{ name: 'EvnUslugaPar_setTime' },
								{ name: 'EvnUslugaPar_disDate' },
								{ name: 'EvnUslugaPar_disTime' },
								{ name: 'UslugaPlace_id' },
								{ name: 'Lpu_uid' },
								{ name: 'LpuSectionProfile_id' },
								{ name: 'MedSpecOms_id' },
								{ name: 'LpuSection_did' },
								{ name: 'LpuSection_uid' },
								{ name: 'MedPersonal_did' },
								{ name: 'MedPersonal_uid' },
								{ name: 'MedPersonal_sid' },
								{ name: 'Lpu_did' },
								{ name: 'Org_did' },
								{ name: 'Org_uid' },
								{ name: 'PayType_id' },
								{ name: 'Person_id' },
								{ name: 'PersonEvn_id' },
								{ name: 'PrehospDirect_id' },
								{ name: 'EvnUslugaPar_IsWithoutDirection' },
								{ name: 'Server_id' },
								{ name: 'EvnCostPrint_setDT' },
								{ name: 'EvnCostPrint_IsNoPrint' },
								{ name: 'MedProductCard_id' },
								{ name: 'MedStaffFact_uid'},
								{ name: 'EvnUslugaPar_MedPersonalCode'}
							]),
							region: 'center',
							url: '/?c=EvnUslugaPar&m=saveEvnUslugaPar',
							items: [{
								name: 'accessType',
								value: '',
								xtype: 'hidden'
							}, {
								name: 'fromMedService',
								value: '',
								xtype: 'hidden'
							}, {
								name:'EvnUslugaPar_IsPaid',
								xtype:'hidden'
							}, {
								name:'DirectionDiag_id',
								xtype:'hidden'
							}, {
								name:'EvnUslugaPar_IndexRep',
								xtype:'hidden'
							}, {
								name:'EvnUslugaPar_IndexRepInReg',
								xtype:'hidden'
							}, {
								name: 'EvnUslugaPar_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_pid',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'XmlTemplate_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnUslugaPar_isCito',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'TimetablePar_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'EvnDirection_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Lpu_did',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Person_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'PersonEvn_id',
								value: 0,
								xtype: 'hidden'
							}, {
								name: 'Server_id',
								value: 0,
								xtype: 'hidden'
							},
							new sw.Promed.Panel({
								autoHeight: true,
								bodyStyle: 'padding-top: 0.5em;',
								border: true,
								collapsible: true,
								id: 'EUParEF_EvnDirectionPanel',
								layout: 'form',
								style: 'margin-bottom: 0.5em;',
								title: '1. Направление',
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										width: 300,
										items: [ new sw.Promed.SwYesNoCombo({
											fieldLabel: 'С электронным направлением',
											hiddenName: 'EvnUslugaPar_IsWithoutDirection',
											value: 1,
											allowBlank: false,
											tabIndex: TABINDEX_EUPAREF + 12,
											//tabIndex: this.tabindex + 5,
											width: 60,
											listeners:
											{
												'select': function (combo, record, index) {
													var base_form = this.findById('EvnUslugaParEditForm').getForm();
													if ( record.get(combo.valueField) == 2 ) {
														// поля заполняются из эл.направления
														base_form.findField('PrehospDirect_id').disable();
														base_form.findField('EvnDirection_Num').setAllowBlank(true);
														base_form.findField('EvnDirection_setDate').setAllowBlank(true);
														base_form.findField('EvnDirection_Num').disable();
														base_form.findField('EvnDirection_setDate').disable();
														base_form.findField('LpuSection_did').disable();
														base_form.findField('MedStaffFact_did').disable();
														base_form.findField('Org_did').disable();
														base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
														if (getRegionNick().inlist(['buryatiya','kareliya', 'adygeya', 'yakutiya'])) {
															base_form.findField('Diag_id').clearValue();
															base_form.findField('Diag_id').disable();
														}
													} else {
														base_form.findField('PrehospDirect_id').setDisabled( this.action == 'view' );
														if (getRegionNick() == 'ekb') {
															base_form.findField('EvnDirection_Num').setAllowBlank(false);
															base_form.findField('EvnDirection_setDate').setAllowBlank(false);
															base_form.findField('EvnDirection_Num').setDisabled( this.action == 'view' );
															base_form.findField('EvnDirection_setDate').setDisabled( this.action == 'view' );
															base_form.findField('EvnDirection_setDate').setValue(new Date());
															this.getEvnDirectionNumber();
														} else {
															base_form.findField('EvnDirection_Num').setDisabled( this.action == 'view' );
															base_form.findField('EvnDirection_setDate').setDisabled( this.action == 'view' );
														}
														base_form.findField('LpuSection_did').setDisabled( this.action == 'view' );
														base_form.findField('MedStaffFact_did').setDisabled( this.action == 'view' );
														base_form.findField('Org_did').setDisabled( this.action == 'view' );
														base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), base_form.findField('PrehospDirect_id').getValue());
														if (getRegionNick().inlist(['buryatiya','kareliya', 'adygeya', 'yakutiya'])) {
															base_form.findField('Diag_id').setDisabled( this.action == 'view' );
														}
													}
												}.createDelegate(this)
											}
										})]
									}, {
										border: false,
										layout: 'form',
										width: 200,
										items: [{
											handler: function() {
												this.openEvnDirectionSelectWindow();
											}.createDelegate(this),
											iconCls: 'add16',
											id: 'EUParEF_EvnDirectionSelectButton',
											tabIndex: TABINDEX_EUPAREF + 15,
											text: 'Выбрать направление',
											tooltip: 'Выбор направления',
											xtype: 'button'
										}]
									}]
								}, {
									hiddenName: 'PrehospDirect_id',
									lastQuery: '',
									listeners: {
										'change': function(combo, newValue, oldValue) {

											var base_form = this.findById('EvnUslugaParEditForm').getForm();

											var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
											var evn_direction_num_field = base_form.findField('EvnDirection_Num');
											var lpu_section_combo = base_form.findField('LpuSection_did');
											var medstafffact_combo = base_form.findField('MedStaffFact_did');
											var org_combo = base_form.findField('Org_did');
											var iswd_combo = base_form.findField('EvnUslugaPar_IsWithoutDirection');
											var diag_combo = base_form.findField('Diag_id');
											var medPersonalCode_combo = base_form.findField('EvnUslugaPar_MedPersonalCode');

											evn_direction_set_date_field.setAllowBlank(true);
											evn_direction_num_field.setAllowBlank(true);
											medPersonalCode_combo.setAllowBlank(true);

											var lpu_section_id = lpu_section_combo.getValue();

											if (this.action != 'editEvnDirectionOnly' && newValue != oldValue) {
												base_form.findField('EvnDirection_id').setValue(0);
												log('set evndirection');
											}

											lpu_section_combo.clearValue();
											medstafffact_combo.clearValue();
											org_combo.clearValue();

											var record = combo.getStore().getById(newValue);

											var prehosp_direct_code = (record && record.get('PrehospDirect_Code')) || null;

											if ( Ext.isEmpty(prehosp_direct_code) ) {
												if (getRegionNick() != 'ekb') {
													evn_direction_set_date_field.disable();
													evn_direction_num_field.disable();
													evn_direction_set_date_field.setValue(null);
													evn_direction_num_field.setValue(null);
												}
												lpu_section_combo.disable();
												medstafffact_combo.disable();
												org_combo.disable();

												return false;
											}

											/*prehosp_direct_code
											 1	Отделение ЛПУ
											 2	Другое ЛПУ
											 3	Другая организация
											 4	Военкомат
											 5	Скорая помощь
											 6	Администрация
											 7	Пункт помощи на дому
											 */
											switch ( Number(prehosp_direct_code) ) {
												case 1:
													if ( lpu_section_id ) {
														lpu_section_combo.setValue(lpu_section_id);
													}
													combo.setDisabled( this.action == 'view' );
													if (getRegionNick() != 'ekb') {
														evn_direction_set_date_field.setDisabled( this.action == 'view' );
														evn_direction_num_field.setDisabled( this.action == 'view' );
													}
													lpu_section_combo.setDisabled( this.action == 'view' );
													medstafffact_combo.setDisabled( this.action == 'view' );

													lpu_section_combo.setAllowBlank( !getRegionNick().inlist(['ekb']) );
													medstafffact_combo.setAllowBlank( !getRegionNick().inlist(['ekb']) );

													//lpu_section_combo.setAllowBlank(false);
													org_combo.disable();
													org_combo.setAllowBlank(true);

													var org_did = getGlobalOptions().org_id;
													org_combo.getStore().load({
														callback: function(records, options, success) {
															if ( success ) {
																org_combo.setValue(org_did);
																org_combo.fireEvent('change', org_combo, org_combo.getValue())
															}
														},
														params: {
															Org_id: org_did,
															OrgType: 'lpu'
														}
													});

													break;

												case 2:
													combo.setDisabled( this.action == 'view' );
													if (getRegionNick() != 'ekb') {
														evn_direction_set_date_field.setDisabled( this.action == 'view' );
														evn_direction_num_field.setDisabled( this.action == 'view' );
													}

													lpu_section_combo.setDisabled( this.action == 'view' );
													medstafffact_combo.setDisabled( this.action == 'view' );
													lpu_section_combo.setAllowBlank(true);
													org_combo.setDisabled( this.action == 'view' );
													org_combo.setAllowBlank( !getRegionNick().inlist(['ekb']) );

													evn_direction_set_date_field.setAllowBlank(false);
													evn_direction_num_field.setAllowBlank(false);
													medPersonalCode_combo.setAllowBlank( !getRegionNick().inlist(['ekb']) );
													break;

												case 3:
												case 4:
												case 5:
												case 6:
													combo.setDisabled( this.action == 'view' );
													if (getRegionNick() != 'ekb') {
														evn_direction_set_date_field.setDisabled( this.action == 'view' );
														evn_direction_num_field.setDisabled( this.action == 'view' );
													}
													lpu_section_combo.disable();
													medstafffact_combo.disable();
													lpu_section_combo.setAllowBlank(true);
													org_combo.setDisabled( this.action == 'view' );
													org_combo.setAllowBlank(true);
													break;

												default:
													combo.disable();
													if (getRegionNick() != 'ekb') {
														evn_direction_set_date_field.disable();
														evn_direction_num_field.disable();
														evn_direction_set_date_field.setValue(null);
														evn_direction_num_field.setValue(null);
													}
													lpu_section_combo.disable();
													medstafffact_combo.disable();
													lpu_section_combo.setAllowBlank(true);
													org_combo.disable();
													org_combo.setAllowBlank(true);
													break;
											}
											if (this.action != 'editEvnDirectionOnly' && 2 == Number(iswd_combo.getValue())) {
												combo.disable();
												if (getRegionNick() != 'ekb') {
													evn_direction_set_date_field.disable();
													evn_direction_num_field.disable();
													evn_direction_set_date_field.setValue(null);
													evn_direction_num_field.setValue(null);
												}
												diag_combo.disable();
												lpu_section_combo.disable();
												medstafffact_combo.disable();
												lpu_section_combo.setAllowBlank(true);
												org_combo.disable();
												org_combo.setAllowBlank(true);
											}

											cur_wnd.setLpuSectionAndMedStaffFactFilter();
										}.createDelegate(this),
										'select': function(combo, record, index) {
											combo.fireEvent('change', combo, record.get(combo.valueField));
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EUPAREF + 18,
									width: 300,
									xtype: 'swprehospdirectcombo'
								}, {
									displayField: 'Org_Name',
									editable: false,
									enableKeyEvents: true,
									fieldLabel: 'Организация',
									hiddenName: 'Org_did',
									listeners: {
										'keydown': function( inp, e ) {
											if ( inp.disabled )
												return;

											if ( e.F4 == e.getKey() ) {
												if ( e.browserEvent.stopPropagation )
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;

												if ( e.browserEvent.preventDefault )
													e.browserEvent.preventDefault();
												else
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
											if ( e.F4 == e.getKey() ) {
												if ( e.browserEvent.stopPropagation )
													e.browserEvent.stopPropagation();
												else
													e.browserEvent.cancelBubble = true;

												if ( e.browserEvent.preventDefault )
													e.browserEvent.preventDefault();
												else
													e.browserEvent.returnValue = false;

												e.returnValue = false;

												if ( Ext.isIE ) {
													e.browserEvent.keyCode = 0;
													e.browserEvent.which = 0;
												}

												return false;
											}
										},
										'change': function(combo, newValue) {
											cur_wnd.setLpuSectionAndMedStaffFactFilter();
										}
									},
									mode: 'local',
									onTrigger1Click: function() {
										var base_form = this.findById('EvnUslugaParEditForm').getForm();
										var combo = base_form.findField('Org_did');

										if ( combo.disabled ) {
											return false;
										}

										var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
										var prehosp_direct_id = prehosp_direct_combo.getValue();
										var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

										if ( !record ) {
											return false;
										}

										var prehosp_direct_code = record.get('PrehospDirect_Code');
										var org_type = '';
										switch ( prehosp_direct_code ) {
											case 2:
											case 5:
												org_type = 'lpu';
												break;

											case 4:
												org_type = 'military';
												break;

											case 3:
											case 6:
												org_type = 'org';
												break;

											default:
												return false;
												break;
										}

										getWnd('swOrgSearchWindow').show({
											object: org_type,
											onClose: function() {
												combo.focus(true, 200)
											},
											onSelect: function(org_data) {
												if ( org_data.Org_id > 0 ) {
													combo.getStore().loadData([{
														Org_id: org_data.Org_id,
														Lpu_id: org_data.Lpu_id,
														Org_Name: org_data.Org_Name
													}]);

													combo.setValue(org_data.Org_id);
													combo.fireEvent('change', combo, combo.getValue())

													getWnd('swOrgSearchWindow').hide();
													combo.collapse();
												}
											}
										});
									}.createDelegate(this),
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'Org_id', type: 'int'},
											{name: 'Lpu_id', type: 'int'},
											{name: 'Org_Name', type: 'string'}
										],
										key: 'Org_id',
										sortInfo: {
											field: 'Org_Name'
										},
										url: C_ORG_LIST
									}),
									tabIndex: TABINDEX_EUPAREF + 21,
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'{Org_Name}',
										'</div></tpl>'
									),
									trigger1Class: 'x-form-search-trigger',
									triggerAction: 'none',
									valueField: 'Org_id',
									width: 500,
									xtype: 'swbaseremotecombo'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											xtype: 'trigger',
											name: 'EvnDirection_Num',
											fieldLabel: '№ направления',
											tabIndex: TABINDEX_EUPAREF + 24,
											listeners: {
												'keydown': function(inp, e) {
													switch ( e.getKey() ) {
														case Ext.EventObject.F2:
															e.stopEvent();
															this.getEvnDirectionNumber();
															break;
													}
												}.createDelegate(this)
											},
											onTriggerClick: function() {
												var base_form = this.findById('EvnUslugaParEditForm').getForm();
												if (!base_form.findField('EvnDirection_Num').disabled) {
													this.getEvnDirectionNumber();
												}
											}.createDelegate(this),
											triggerClass: 'x-form-plus-trigger',
											hideTrigger: getRegionNick() != 'ekb',
											validateOnBlur: false,
											maskRe: /[0-9]/,
											width: 150,
											autoCreate: {tag: "input", type: "text", maxLength: "16", autocomplete: "off"}
										}]
									}, {
										border: false,
										labelWidth: 200,
										layout: 'form',
										items: [{
											fieldLabel: 'Дата направления',
											format: 'd.m.Y',
											name: 'EvnDirection_setDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: TABINDEX_EUPAREF + 27,
											width: 100,
											xtype: 'swdatefield'
										}]
									}]
								}, {
									hiddenName: 'LpuSection_did',
									id: 'EUParEF_LpuSectionDirCombo',
									lastQuery: '',
									linkedElements: [
										'EUParEF_MedStaffFactDirCombo'
									],
									tabIndex: TABINDEX_EUPAREF + 30,
									width: 500,
									xtype: 'swlpusectionglobalcombo'
								}, {
									name: 'MedPersonal_did',
									xtype: 'hidden'
								}, {
									fieldLabel: 'Врач',
									hiddenName: 'MedStaffFact_did',
									id: 'EUParEF_MedStaffFactDirCombo',
									lastQuery: '',
									listWidth: 750,
									parentElementId: 'EUParEF_LpuSectionDirCombo',
									tabIndex: TABINDEX_EUPAREF + 33,
									width: 500,
									xtype: 'swmedstafffactglobalcombo',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											if(newValue){
												var base_form = this.findById('EvnUslugaParEditForm').getForm();
												var fieldDoctorCode = base_form.findField('EvnUslugaPar_MedPersonalCode');
												if(fieldDoctorCode.isVisible()){
													var rec = combo.findRecord('MedStaffFact_id', newValue);
													var code = rec.get('MedPersonal_DloCode');
													if(code){
														fieldDoctorCode.setValue(code);
													}else{
														fieldDoctorCode.setValue();
													}
												}
											}
										}.createDelegate(this)
									}
								}, {
									id: 'EUParEF_MedPersonalCode',
									fieldLabel: 'Код врача',
									//hidden: (getRegionNick() != 'ekb'),
									maxLength: 14,
									maskRe: /\d/,
									autoCreate: {tag: "input", size:14, maxLength: "14", autocomplete: "off"},
									name: 'EvnUslugaPar_MedPersonalCode',
									//tabIndex: this.tabindex + 13,
									width: 150,
									xtype: 'numberfield'
								}
								]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								bodyStyle: 'padding-top: 0.5em;',
								border: true,
								collapsible: true,
								id: 'EUParEF_UslugaPlacePanel',
								layout: 'form',
								style: 'margin-bottom: 0.5em;',
								title: '2. Место выполнения',
								items: [{
									enableKeyEvents: true,
									listeners: {
										'change':function(combo, newValue, oldValue) {

											var index = combo.getStore().findBy(function (rec) {
												return (rec.get(combo.valueField) == newValue);
											});

											combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
										},
										'select': function(combo, record, idx) {

											var base_form = cur_wnd.findById('EvnUslugaParEditForm').getForm();
											if ( typeof record == 'object' && !Ext.isEmpty(record.get('UslugaPlace_id')) && record.get('UslugaPlace_id').inlist([2, 3])) {
												base_form.findField('LpuSection_uid').disableLinkedElements();
												base_form.findField('MedStaffFact_uid').disableParentElement();
												base_form.findField('MedStaffFact_sid').disableParentElement();
												base_form.findField('Lpu_uid').showContainer();
												base_form.findField('Org_uid').showContainer();
												if (record.get('UslugaPlace_id') == 3) {
													base_form.findField('Org_uid').setAllowBlank(false);
													base_form.findField('Org_uid').setDisabled(false);
													base_form.findField('Lpu_uid').setAllowBlank(true);
													base_form.findField('Lpu_uid').setDisabled(true);
												} else {
													base_form.findField('Lpu_uid').setAllowBlank(false);
													base_form.findField('Lpu_uid').setDisabled(false);
													base_form.findField('Org_uid').setAllowBlank(true);
													base_form.findField('Org_uid').setDisabled(true);
												}

												base_form.findField('LpuSection_uid').setAllowBlank(true);
												base_form.findField('MedStaffFact_uid').setAllowBlank(true);
												base_form.findField('MedStaffFact_sid').setAllowBlank(true);
											} else {
												base_form.findField('LpuSection_uid').enableLinkedElements();
												base_form.findField('MedStaffFact_uid').enableParentElement();
												base_form.findField('MedStaffFact_sid').enableParentElement();
												base_form.findField('LpuSection_uid').setAllowBlank(false);
												base_form.findField('Lpu_uid').hideContainer();
												base_form.findField('Org_uid').hideContainer();
												base_form.findField('Lpu_uid').clearValue();
												base_form.findField('Lpu_uid').setAllowBlank(true);
												base_form.findField('Org_uid').setAllowBlank(true);
												base_form.findField('MedStaffFact_uid').enable();
												base_form.findField('MedStaffFact_uid').setAllowBlank(false);

												cur_wnd.loadStoreSectionMedstafffact({ onDate: Ext.util.Format.date(base_form.findField('EvnUslugaPar_setDate').getValue(), 'd.m.Y') });
											}

											checkValueInStore(base_form, 'LpuSection_uid', 'LpuSection_id', base_form.findField('LpuSection_uid').getValue());
											checkValueInStore(base_form, 'MedStaffFact_uid', 'MedStaffFact_id', base_form.findField('MedStaffFact_uid').getValue());
											checkValueInStore(base_form, 'MedStaffFact_sid', 'MedStaffFact_id', base_form.findField('MedStaffFact_sid').getValue());

											if (typeof record == 'object' && !Ext.isEmpty(record.get('UslugaPlace_id'))) {
												base_form.findField('LpuSectionProfile_id').onChangeUslugaPlaceField(combo, record.get('UslugaPlace_Code'));
												base_form.findField('MedSpecOms_id').onChangeUslugaPlaceField(combo, record.get('UslugaPlace_Code'));
											}

											if ( typeof record == 'object' && !Ext.isEmpty(record.get('UslugaPlace_id')) && record.get('UslugaPlace_id') == 2 ) {
												base_form.findField('LpuSectionProfile_id').setAllowBlank(false);
												//base_form.findField('MedSpecOms_id').setAllowBlank(false);
											}
										}
									},
									hiddenName: 'UslugaPlace_id',
									allowBlank: false,
									tabIndex: TABINDEX_EUPAREF + 36,
									validateOnBlur: false,
									width: 350,
									fieldLabel: 'Место выполнения',
									comboSubject: 'UslugaPlace',
									xtype: 'swcommonsprcombo',
									value: 1
								}, {
									comboSubject: 'Lpu',
									fieldLabel: 'МО',
									xtype: 'swcommonsprcombo',
									editable: true,
									listeners: {
										'change':function(combo, newValue, oldValue) {
											//Грузим данные по отделениям и врачам с базы при выборе МО
											cur_wnd.loadLpuData();
										}
									},
									forceSelection: true,
									displayField: 'Lpu_Nick',
									codeField: 'Lpu_Code',
									orderBy: 'Nick',
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'{Lpu_Nick}',
										'</div></tpl>'
									),
									moreFields: [
										{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
										{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
									],
									tabIndex: TABINDEX_EUPAREF + 39,
									width: 350,
									hiddenName: 'Lpu_uid',
									id: 'EUParEF_Lpu_uid',
									onLoadStore: function() {
										cur_wnd.filterLpuCombo();
									}
								}, {
									displayField: 'Org_Name',
									editable: false,
									enableKeyEvents: true,
									fieldLabel: 'Другая организация',
									hiddenName: 'Org_uid',
									mode: 'local',
									listeners: {
										'change': function(combo, newValue) {
											cur_wnd.setLpuSectionAndMedStaffFactFilter();
										}
									},
									onTrigger1Click: function() {
										var base_form = this.findById('EvnUslugaParEditForm').getForm();
										var combo = base_form.findField('Org_uid');
										if (combo.disabled) {
											return;
										}
										var usluga_place_combo = base_form.findField('UslugaPlace_id');
										var usluga_place_id = usluga_place_combo.getValue();
										var record = usluga_place_combo.getStore().getById(usluga_place_id);
										if (!record) {
											return false;
										}
										var org_type = 'org';
										getWnd('swOrgSearchWindow').show({
											onSelect: function(org_data) {
												if (org_data.Org_id > 0) {
													combo.getStore().loadData([
														{
															Org_id: org_data.Org_id,
															Org_Name: org_data.Org_Name
														}
													]);
													combo.setValue(org_data.Org_id);
													getWnd('swOrgSearchWindow').hide();
												}
											},
											onClose: function() {
												combo.focus(true, 200)
											},
											object: org_type
										});
									}.createDelegate(this),
									store: new Ext.data.JsonStore({
										autoLoad: false,
										fields: [
											{name: 'Org_id', type: 'int'},
											{name: 'Org_Name', type: 'string'}
										],
										key: 'Org_id',
										sortInfo: {
											field: 'Org_Name'
										},
										url: C_ORG_LIST
									}),
									tabIndex: TABINDEX_EUPAREF + 39,
									tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Org_Name}', '</div></tpl>'),
									trigger1Class: 'x-form-search-trigger',
									triggerAction: 'none',
									valueField: 'Org_id',
									width: 500,
									xtype: 'swbaseremotecombo'
								}, {
                                    hidden: true,
                                    lastQuery: '',
									xtype: 'swmedspecomswithfedcombo',
									tabIndex: TABINDEX_EUPAREF + 45,
									width: 350,
									onTrigger2Click: function(){
										cur_wnd.findById('EvnUslugaParEditForm').getForm().findField('MedSpecOms_id').clearValue();
										cur_wnd.filterMedStaff();
									},
									listeners: {
										'change':function(combo, newValue, oldValue) {
											//Фильтруем список врачей
											cur_wnd.filterMedStaff();
										},
										'select': function (combo, record) {
											combo.fireEvent('change', combo, -1, combo.getValue());
										}
									},
									hiddenName: 'MedSpecOms_id'
								}, {
									allowBlank: false,
									hiddenName: 'LpuSection_uid',
									id: 'EUParEF_LpuSectionCombo',
									lastQuery: '',
									listeners: {
										'change':function(combo, newValue, oldValue) {
											//Фильтруем список врачей
											cur_wnd.filterMedStaff();
																													
											// Фильтруем мед.изделия
											cur_wnd.filterMedProductCard();											
											
											//обновляем профиль если поставили отделение
											cur_wnd.filterProfile();
										},
										'select': function (combo, record) {
											combo.fireEvent('change', combo, -1, combo.getValue());
										}
									},
									linkedElements: [
										'EUParEF_MedStaffFactCombo',
										'EUParEF_MedStaffFactCombo2'
									],
									tabIndex: TABINDEX_EUPAREF + 48,
									width: 500,
									xtype: 'swlpusectionglobalcombo'
								}, {
                                    hidden: true,
                                    lastQuery: '',
									xtype: 'swlpusectionprofilewithfedcombo',
									tabIndex: TABINDEX_EUPAREF + 49,
									width: 350,
									onTrigger2Click: function(){
										cur_wnd.findById('EvnUslugaParEditForm').getForm().findField('LpuSectionProfile_id').clearValue();
										cur_wnd.filterLpuSection();
									},
									listeners: {
										'change':function(combo, newValue, oldValue) {
											cur_wnd.filterLpuSection();
										},
										'select': function (combo, record) {
											combo.fireEvent('change', combo, -1, combo.getValue());
										}
									},
									hiddenName: 'LpuSectionProfile_id'
								}, {
									name: 'MedPersonal_uid',
									xtype: 'hidden'
								}, {
									name: 'MedPersonal_sid',
									xtype: 'hidden'
								}, {
									allowBlank: false,
									fieldLabel: 'Врач',
									hiddenName: 'MedStaffFact_uid',
									id: 'EUParEF_MedStaffFactCombo',
									lastQuery: '',
									ignoreDisableInDoc: true,
									listeners: {
										'change':function(combo, newValue, oldValue) {
											//обновляем профиль если поставили врача
											cur_wnd.filterProfile();

											var base_form = cur_wnd.findById('EvnUslugaParEditForm').getForm();
											if (base_form.findField('UslugaPlace_id').getValue() == 2 && !Ext.isEmpty(combo.getFieldValue('MedSpecOms_id'))) {

												var index = base_form.findField('MedSpecOms_id').getStore().findBy(function(rec) {
													return (rec.get('MedSpecOms_id') == combo.getFieldValue('MedSpecOms_id'));
												});

												if (index >= 0) {
													base_form.findField('MedSpecOms_id').setValue(combo.getFieldValue('MedSpecOms_id'));
												} else {
													base_form.findField('MedSpecOms_id').clearValue();
												}
											}
										},
										'select': function (combo, record) {
											combo.fireEvent('change', combo, -1, combo.getValue());
										}
									},
									listWidth: 750,
									parentElementId: 'EUParEF_LpuSectionCombo',
									tabIndex: TABINDEX_EUPAREF + 51,
									width: 500,
									xtype: 'swmedstafffactglobalcombo'
								}, {
									//allowBlank: false,
									fieldLabel: 'Средний мед.персонал',
									hiddenName: 'MedStaffFact_sid',
									id: 'EUParEF_MedStaffFactCombo2',
									lastQuery: '',
									listWidth: 750,
									parentElementId: 'EUParEF_LpuSectionCombo',
									tabIndex: TABINDEX_EUPAREF + 54,
									width: 500,
									xtype: 'swmedstafffactglobalcombo'
								}]
							}),
							new sw.Promed.Panel({
								autoHeight: true,
								bodyStyle: 'padding-top: 0.5em;',
								border: true,
								collapsible: true,
								id: 'EUParEF_EvnUslugaParPanel',
								layout: 'form',
								style: 'margin-bottom: 0.5em;',
								title: '3. Услуга',
								items: [{
									fieldLabel: 'Повторная подача',
									listeners: {
										'check': function(checkbox, value) {
											if ( getRegionNick() != 'perm' ) {
												return false;
											}

											var base_form = this.findById('EvnUslugaParEditForm').getForm();

											var
												EvnUslugaPar_IndexRep = parseInt(base_form.findField('EvnUslugaPar_IndexRep').getValue()),
												EvnUslugaPar_IndexRepInReg = parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()),
												EvnUslugaPar_IsPaid = parseInt(base_form.findField('EvnUslugaPar_IsPaid').getValue());

											var diff = EvnUslugaPar_IndexRepInReg - EvnUslugaPar_IndexRep;

											if ( EvnUslugaPar_IsPaid != 2 || EvnUslugaPar_IndexRepInReg == 0 ) {
												return false;
											}

											if ( value == true ) {
												if ( diff == 1 || diff == 2 ) {
													EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 2;
												}
												else if ( diff == 3 ) {
													EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep + 4;
												}
											}
											else if ( value == false ) {
												if ( diff <= 0 ) {
													EvnUslugaPar_IndexRep = EvnUslugaPar_IndexRep - 2;
												}
											}

											base_form.findField('EvnUslugaPar_IndexRep').setValue(EvnUslugaPar_IndexRep);

										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EUPAREF + 57,
									name: 'EvnUslugaPar_RepFlag',
									xtype: 'checkbox'
								}, {
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											allowBlank: false,
											fieldLabel: 'Дата начала выполнения',
											format: 'd.m.Y',
											listeners: {
												'change': function(field, newValue, oldValue) {
													if ( blockedDateAfterPersonDeath('personpanelid', 'EUParEF_PersonInformationFrame', field, newValue, oldValue) ) {
														return false;
													}
													var base_form = this.findById('EvnUslugaParEditForm').getForm();
													if ( newValue ) {
														this.loadStoreSectionMedstafffact({ onDate: Ext.util.Format.date(newValue, 'd.m.Y') });
													}
													this.filterLpuCombo();
                                                    base_form.findField('LpuSectionProfile_id').onChangeDateField(field, newValue);
                                                    base_form.findField('MedSpecOms_id').onChangeDateField(field, newValue);
													this.setDisDT();
													this.setTumorStageVisibility();
												}.createDelegate(this)
											},
											name: 'EvnUslugaPar_setDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: TABINDEX_EUPAREF + 60,
											width: 100,
											xtype: 'swdatefield'
										}]
									}, {
										border: false,
										labelWidth: 50,
										layout: 'form',
										items: [{
											fieldLabel: 'Время',
											allowBlank: false,
											listeners: {
												'change': function() {
													this.setDisDT();
												}.createDelegate(this),
												'keydown': function (inp, e) {
													if ( e.getKey() == Ext.EventObject.F4 ) {
														e.stopEvent();
														inp.onTriggerClick();
													}
												}
											},
											name: 'EvnUslugaPar_setTime',
											onTriggerClick: function() {
												var base_form = this.findById('EvnUslugaParEditForm').getForm();
												var time_field = base_form.findField('EvnUslugaPar_setTime');

												if ( time_field.disabled ) {
													return false;
												}

												setCurrentDateTime({
													callback: function() {
														this.setDisDT();
													}.createDelegate(this),
													dateField: base_form.findField('EvnUslugaPar_setDate'),
													loadMask: true,
													setDate: true,
													setDateMaxValue: true,
													setDateMinValue: false,
													setTime: true,
													timeField: time_field,
													windowId: this.id
												});
											}.createDelegate(this),
											plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
											tabIndex: TABINDEX_EUPAREF + 63,
											validateOnBlur: false,
											width: 60,
											xtype: 'swtimefield'
										}]
									}, {
										layout: 'form',
										style: 'padding-left: 45px',
										border: false,
										items: [{
											xtype: 'button',
											tabIndex: TABINDEX_EUPAREF + 66,
											id: 'EUParEF_ToggleVisibleDisDTBtn',
											text: 'Уточнить период выполнения',
											handler: function() {
												this.toggleVisibleDisDTPanel();
											}.createDelegate(this)
										}]
									}]
								},{
									border: false,
									layout: 'column',
									id: 'EUParEF_EvnUslugaDisDTPanel',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: 'Дата окончания выполнения',
											format: 'd.m.Y',
											name: 'EvnUslugaPar_disDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: TABINDEX_EUPAREF + 69,
											width: 100,
											xtype: 'swdatefield'
										}]
									}, {
										border: false,
										labelWidth: 50,
										layout: 'form',
										items: [{
											fieldLabel: 'Время',
											listeners: {
												'keydown': function (inp, e) {
													if ( e.getKey() == Ext.EventObject.F4 ) {
														e.stopEvent();
														inp.onTriggerClick();
													}
												}
											},
											name: 'EvnUslugaPar_disTime',
											onTriggerClick: function() {
												var base_form = this.findById('EvnUslugaParEditForm').getForm();
												var time_field = base_form.findField('EvnUslugaPar_disTime');

												if ( time_field.disabled ) {
													return false;
												}

												setCurrentDateTime({
													dateField: base_form.findField('EvnUslugaPar_disDate'),
													loadMask: true,
													setDate: true,
													setDateMaxValue: true,
													setDateMinValue: false,
													setTime: true,
													timeField: time_field,
													windowId: this.id
												});
											}.createDelegate(this),
											plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
											tabIndex: TABINDEX_EUPAREF + 72,
											validateOnBlur: false,
											width: 60,
											xtype: 'swtimefield'
										}]
									}, {
										layout: 'form',
										border: false,
										items: [{
											xtype: 'button',
											id: 'EUParEF_DTCopyBtn',
											text: '=',
											handler: function() {
												var base_form = this.findById('EvnUslugaParEditForm').getForm();

												base_form.findField('EvnUslugaPar_disDate').setValue(base_form.findField('EvnUslugaPar_setDate').getValue());
												base_form.findField('EvnUslugaPar_disTime').setValue(base_form.findField('EvnUslugaPar_setTime').getValue());
											}.createDelegate(this)
										}]
									}]
								},
								{
									allowBlank: false,
									fieldLabel: 'Категория услуги',
									hiddenName: 'UslugaCategory_id',
									listeners: {
										'select': function (combo, record) {
											var base_form = this.findById('EvnUslugaParEditForm').getForm();

											base_form.findField('UslugaComplex_id').clearValue();
											base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
											base_form.findField('UslugaComplex_id').getStore().removeAll();

											if ( !record ) {
												base_form.findField('UslugaComplex_id').setUslugaCategoryList();
												return false;
											}

											base_form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

											return true;
										}.createDelegate(this)
									},
									listWidth: 400,
									loadParams: (getRegionNick() == 'kz' ? {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}} : null),
									tabIndex: TABINDEX_EUPAREF + 75,
									width: 250,
									xtype: 'swuslugacategorycombo'
								}, {
									fieldLabel: 'Категория услуги',
									name: 'UslugaCategory_Name',
									tabIndex: TABINDEX_EUPAREF + 78,
									width: 250,
									xtype: 'textfield'
								}, {
									allowBlank: false,
									fieldLabel: 'Услуга',
									listeners: {
										'change': function (combo, newValue, oldValue) {
                                            var Diag_AllowBlank = true;

											this.loadUslugaComplexTariffCombo();

											if (getRegionNick() == 'ekb') {
												var base_form = cur_wnd.findById('EvnUslugaParEditForm').getForm();
                                                var Diag_Code = null;
                                                var UslugaComplex_Code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
                                                switch (UslugaComplex_Code) {
                                                    case 'A06.09.006':
                                                    case 'A06.09.006.888':
                                                        Diag_Code = 'Z11.1';
                                                        Diag_AllowBlank = false;
                                                        break;
                                                    case 'A06.20.004':
                                                    case 'A06.20.004.888':
                                                        Diag_Code = 'Z01.8';
                                                        Diag_AllowBlank = false;
                                                        break;
                                                    case 'A06.30.003.001':
                                                    case 'A06.30.003.002':
                                                        Diag_Code = 'Z03.8';
                                                        break;
                                                }

                                                base_form.findField('Diag_id').setAllowBlank(Diag_AllowBlank);

												if ( this.doNotSetDeafultDiagOnEdit == false ) {
                                                    if (!Ext.isEmpty(base_form.findField('DirectionDiag_id').getValue())) {
                                                        var diag_id = base_form.findField('DirectionDiag_id').getValue();
                                                        base_form.findField('Diag_id').getStore().load({
                                                            params: {where: "where Diag_id = " + diag_id},
                                                            callback: function () {
                                                                if (base_form.findField('Diag_id').getStore().getCount() > 0) {
                                                                    base_form.findField('Diag_id').setValue(diag_id);
                                                                    base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
                                                                    base_form.findField('Diag_id').onChange();
                                                                }
                                                            }
                                                        });
                                                    } else if (Diag_Code) {
														base_form.findField('Diag_id').getStore().load({
															params: {where: "where Diag_Code = '" + Diag_Code + "'"},
															callback: function () {
																if (base_form.findField('Diag_id').getStore().getCount() > 0) {
																	var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
																	base_form.findField('Diag_id').setValue(diag_id);
																	base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
																	base_form.findField('Diag_id').onChange();
																}
															}
														});
													}
												}

												base_form.findField('Mes_id').lastQuery = 'This query sample that is not will never appear';
												base_form.findField('Mes_id').getStore().removeAll();
												base_form.findField('Mes_id').getStore().baseParams.UslugaComplex_id = newValue;
												base_form.findField('Mes_id').getStore().baseParams.query = '';
											}
											if (cur_wnd.action != 'view') {
												cur_wnd.findById('EvnUslugaParEditForm').getForm().findField('FSIDI_id').checkVisibilityAndGost(combo.value);
											}

											return true;
										}.createDelegate(this)
									},
									hiddenName: 'UslugaComplex_id',
									listWidth: 700,
									tabIndex: TABINDEX_EUPAREF + 81,
									width: 500,
									xtype: 'swuslugacomplexnewcombo'
								}, {
										xtype: 'swfsidicombo',
										hiddenName: 'FSIDI_id',
										width: 480,
										listWidth: 500,
										labelWidth: 250,
										hideOnInit: true
								}, {
									hiddenName: 'Diag_id',
									tabIndex: TABINDEX_EUPAREF + 82,
									onChange: function() {
										var diag_code = this.getFieldValue('Diag_Code');
										var base_form = cur_wnd.findById('EvnUslugaParEditForm').getForm();
										if (!getRegionNick().inlist(['ekb','kareliya','buryatiya', 'adygeya', 'yakutiya']) || !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
											base_form.findField('DeseaseType_id').setAllowBlank(true);
										} else {
											base_form.findField('DeseaseType_id').setAllowBlank(false);
										}
										cur_wnd.setTumorStageVisibility();
									},
									fieldLabel: 'Диагноз',
									width: 450,
									xtype: 'swdiagcombo'
								}, {
									hiddenName: 'DeseaseType_id',
									tabIndex: TABINDEX_EUPAREF + 82,
									fieldLabel: 'Характер',
									width: 450,
									xtype: 'swdeseasetypecombo'
								}, 
								{
									xtype: 'swcommonsprcombo',
									comboSubject: 'TumorStage',
									hiddenName: 'TumorStage_id',
									fieldLabel: 'Стадия выявленного ЗНО',
									tabIndex: TABINDEX_EUPAREF + 82,
									width: 450
								},
								{
									fieldLabel: 'МЭС',
									hiddenName: 'Mes_id',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = cur_wnd.findById('EvnUslugaParEditForm').getForm();

											base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
											base_form.findField('UslugaComplex_id').getStore().removeAll();
											base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = newValue;
											base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';
										}
									},
									tabIndex: TABINDEX_EUPAREF + 82,
									width: 450,
									forceSelection: true,
									xtype: 'swmesekbcombo'
								}, {
									allowBlank: false,
									useCommonFilter: true,
									listeners: {
										'change': function (combo, newValue, oldValue) {
											this.loadUslugaComplexTariffCombo();
                                            var base_form = this.findById('EvnUslugaParEditForm').getForm();
                                            base_form.findField('LpuSectionProfile_id').onChangePayTypeField(combo, combo.getFieldValue('PayType_SysNick'));
                                            base_form.findField('MedSpecOms_id').onChangePayTypeField(combo, combo.getFieldValue('PayType_SysNick'));
											return true;
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EUPAREF + 84,
									width: 250,
									xtype: 'swpaytypecombo'
								}, {
									allowBlank: true,
									displayField: 'UslugaComplexTariff_Tariff',
									hiddenName: 'UslugaComplexTariff_id',
									listWidth: 600,
									tabIndex: TABINDEX_EUPAREF + 87,
									width: 500,
									xtype: 'swuslugacomplextariffcombo'
								}, {
									allowBlank: false,
									allowNegative: false,
									enableKeyEvents: true,
									fieldLabel: 'Количество',
									name: 'EvnUslugaPar_Kolvo',
									tabIndex: TABINDEX_EUPAREF + 90,
									width: 100,
									/*onTabAction: function () {
										if ( this.action != 'view' ) {
											this.findById('EvnUslugaParEditForm').getForm().findField('PrehospDirect_id').focus(true);
										}
										else {
											this.buttons[0].focus();
										}
									}.createDelegate(this),*/
									onTabAction: function(){
										log('tabtab');
									},
									xtype: 'numberfield',
									minValue: 1
								}, {
									allowBlank: true,
									editable: true,
									codeField: 'AccountingData_InventNumber',
									displayField: 'MedProductClass_Name',
									fieldLabel: 'Медицинское изделие',
									hiddenName: 'MedProductCard_id',
									store: new Ext.data.Store({
										autoLoad: false,
										reader: new Ext.data.JsonReader({
											id: 'MedProductCard_id'
										}, [
											{ name: 'MedProductCard_id', mapping: 'MedProductCard_id', type: 'int' },
											{ name: 'LpuSection_id', mapping: 'LpuSection_id', type: 'int' },
											{ name: 'AccountingData_InventNumber', mapping: 'AccountingData_InventNumber', type: 'string' },
											{ name: 'MedProductClass_Name', mapping: 'MedProductClass_Name', type: 'string' },
											{ name: 'MedProductClass_Model', mapping: 'MedProductClass_Model', type: 'string' }
										]),
										url: '/?c=LpuPassport&m=loadMedProductCard'
									}),
									tpl: new Ext.XTemplate(
										'<tpl for="."><div class="x-combo-list-item">',
										'<table style="border: 0;"><td style="width: 70px"><font color="red">{AccountingData_InventNumber}</font></td><td><h3>{MedProductClass_Name}</h3>{MedProductClass_Model}</td></tr></table>',
										'</div></tpl>'
									),
									triggerAction: 'all',
									valueField: 'MedProductCard_id',
									lastQuery: '',
									width: 500,
									listWidth: 600,
									xtype: 'swbaselocalcombo'
								}]
							}),
							new sw.Promed.Panel({
								border: true,
								collapsible: true,
								height: 100,
								id: 'EUParEF_CostPrintPanel',
								layout: 'border',
								listeners: {
									'expand': function(panel) {
										panel.doLayout();
									}.createDelegate(this)
								},
								style: 'margin-bottom: 0.5em;',
								title: '4. Справка о стоимости лечения',
								hidden: ! getRegionNick().inlist(['perm']),
								items: [{
									bodyStyle: 'padding-top: 0.5em;',
									border: false,
									height: 90,
									layout: 'form',
									region: 'center',
									items: [{
										fieldLabel: 'Дата выдачи справки/отказа',
										tabIndex: TABINDEX_EUPAREF + 92,
										width: 100,
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										name: 'EvnCostPrint_setDT',
										xtype: 'swdatefield'
									},{
										fieldLabel: 'Отказ',
										tabIndex: TABINDEX_EUPAREF + 94,
										hiddenName: 'EvnCostPrint_IsNoPrint',
										width: 60,
										xtype: 'swyesnocombo'
									}]
								}]
							})]
						}),
						this.EvnXmlPanel
					]
				}, {
					title: 'Файлы',
					id: 'EUParEF_FileTab',
					border: false,
					//autoHeight: true,
					region: 'center',
					layout: 'border',
					items: [
						new sw.Promed.FileList({
							region: 'center',
							saveOnce: false,
							id: 'EUParEF_FileList',
							dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
							saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
							saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
							deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
						})
					]
				}]
			})
			]
		});
		sw.Promed.swEvnUslugaParEditWindow.superclass.initComponent.apply(this, arguments);


		this.findById('EUParEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaParEditForm').getForm();

			if (newValue > 0) base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = newValue;
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
			base_form.findField('UslugaComplex_id').getStore().removeAll();
			base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';

			this.loadUslugaComplexTariffCombo();
		}.createDelegate(this));

		this.findById('EUParEF_MedStaffFactCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaParEditForm').getForm();

			var index = combo.getStore().findBy(function(rec) {
				return (rec.get(combo.valueField) == newValue);
			});

			if ( index >= 0 ) {
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = combo.getStore().getAt(index).get('LpuSection_id');
				base_form.findField('UslugaComplex_id').clearValue();
				base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				base_form.findField('UslugaComplex_id').getStore().removeAll();
				base_form.findField('UslugaComplex_id').lastQuery = 'The string that will never appear';
			}
			else {
				base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = null;
			}

			this.loadUslugaComplexTariffCombo();
		}.createDelegate(this));
	},
	setLpuSectionAndMedStaffFactFilter: function(options) {
		var win = this;
		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		var LpuSection_did = base_form.findField('LpuSection_did').getValue();
		var MedPersonal_did = base_form.findField('MedPersonal_did').getValue();

		if (options && options.LpuSection_id) {
			LpuSection_did = options.LpuSection_id;
		}
		if (options && options.MedPersonal_id) {
			MedPersonal_did = options.MedPersonal_id;
		}

		base_form.findField('LpuSection_did').getStore().removeAll();
		base_form.findField('LpuSection_did').clearValue();
		base_form.findField('MedStaffFact_did').getStore().removeAll();
		base_form.findField('MedStaffFact_did').clearValue();


		if (!Ext.isEmpty(base_form.findField('Org_did').getFieldValue('Lpu_id')) || !Ext.isEmpty(base_form.findField('Org_uid').getValue())) {
			var Lpu_id = !Ext.isEmpty(base_form.findField('Org_did').getFieldValue('Lpu_id'))?base_form.findField('Org_did').getFieldValue('Lpu_id'):(!Ext.isEmpty(base_form.findField('Org_uid').getFieldValue('Lpu_id'))?base_form.findField('Org_uid').getFieldValue('Lpu_id'):null);

			if (getGlobalOptions().lpu_id != Lpu_id) {
				base_form.findField('LpuSection_did').getStore().load({
					params: {
						Lpu_id: Lpu_id,
						mode: 'combo'
					},
					callback: function() {
						var index = base_form.findField('LpuSection_did').getStore().findBy(function(rec) {
							return (rec.get('LpuSection_id') == LpuSection_did);
						});

						if (index > -1) {
							ucid = base_form.findField('LpuSection_did').getStore().getAt(index).get('LpuSection_id');
							base_form.findField('LpuSection_did').setValue(ucid);
						}
					}
				});

				base_form.findField('MedStaffFact_did').getStore().load({
					params: {
						Lpu_id: Lpu_id,
						mode: 'combo'
					},
					callback: function() {
						var index = base_form.findField('MedStaffFact_did').getStore().findBy(function(rec) {
							return (rec.get('MedPersonal_id') == MedPersonal_did && rec.get('LpuSection_id') == LpuSection_did);
						});

						if (index > -1) {
							ucid = base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id');
							base_form.findField('MedStaffFact_did').setValue(ucid);
						}
					}
				});
			} else {
				setLpuSectionGlobalStoreFilter();
				setMedStaffFactGlobalStoreFilter();

				base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
				base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

				var index = base_form.findField('LpuSection_did').getStore().findBy(function(rec) {
					return (rec.get('LpuSection_id') == LpuSection_did);
				});

				if (index > -1) {
					ucid = base_form.findField('LpuSection_did').getStore().getAt(index).get('LpuSection_id');
					base_form.findField('LpuSection_did').setValue(ucid);
				}

				var index = base_form.findField('MedStaffFact_did').getStore().findBy(function(rec) {
					return (rec.get('MedPersonal_id') == MedPersonal_did && rec.get('LpuSection_id') == LpuSection_did);
				});

				if (index > -1) {
					ucid = base_form.findField('MedStaffFact_did').getStore().getAt(index).get('MedStaffFact_id');
					base_form.findField('MedStaffFact_did').setValue(ucid);
				}
			}
		}
	},
	checkForCostPrintPanel: function() {
		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		this.findById('EUParEF_CostPrintPanel').hide();
		base_form.findField('EvnCostPrint_setDT').setAllowBlank(true);
		base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(true);

		// если справка уже печаталась и случай закрыт, отображаем раздел с данными справки
		if (!Ext.isEmpty(base_form.findField('EvnCostPrint_setDT').getValue()) && getRegionNick().inlist(['perm'])) {
			this.findById('EUParEF_CostPrintPanel').show();
			// поля обязтаельные
			base_form.findField('EvnCostPrint_setDT').setAllowBlank(false);
			base_form.findField('EvnCostPrint_IsNoPrint').setAllowBlank(false);
		}
	},
	loadUslugaComplexTariffCombo: function () {
		var base_form = this.findById('EvnUslugaParEditForm').getForm(),
			combo = base_form.findField('UslugaComplexTariff_id'),
			params = {
				LpuSection_id: base_form.findField('LpuSection_uid').getValue()
				,PayType_id: base_form.findField('PayType_id').getValue()
				,Person_id: base_form.findField('Person_id').getValue()
				,UslugaComplexTariff_Date: base_form.findField('EvnUslugaPar_setDate').getValue()
				,UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue()
			};
		combo.setParams(params);
		combo.isAllowSetFirstValue = true;
		combo.loadUslugaComplexTariffList();
		return true;
	},
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		base_form.findField('EvnUslugaPar_disDate').setValue(base_form.findField('EvnUslugaPar_setDate').getValue());
		base_form.findField('EvnUslugaPar_disTime').setValue(base_form.findField('EvnUslugaPar_setTime').getValue());
	},
	setTumorStageVisibility: function() {
		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		var
			dateX20180601 = new Date(2018, 5, 1),
			Diag_Code = base_form.findField('Diag_id').getFieldValue('Diag_Code'),
			EvnUslugaPar_setDate = base_form.findField('EvnUslugaPar_setDate').getValue();

		if (
			getRegionNick() == 'ekb'
			&& !Ext.isEmpty(Diag_Code) && ((Diag_Code.slice(0, 3) >= 'C00' && Diag_Code.slice(0, 5) <= 'C80.9') || Diag_Code.slice(0, 3) == 'C97')
			&& typeof EvnUslugaPar_setDate == 'object' && EvnUslugaPar_setDate < dateX20180601
		) {
			base_form.findField('TumorStage_id').setContainerVisible(true);
			base_form.findField('TumorStage_id').setAllowBlank(false);
		}
		else {
			base_form.findField('TumorStage_id').setContainerVisible(false);
			base_form.findField('TumorStage_id').setAllowBlank(true);
			base_form.findField('TumorStage_id').setValue(null);
		}
	},
	toggleVisibleDisDTPanel: function(action) {
		var base_form = this.findById('EvnUslugaParEditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EUParEF_EvnUslugaDisDTPanel').hide();
			this.findById('EUParEF_ToggleVisibleDisDTBtn').setText('Уточнить период выполнения');
			base_form.findField('EvnUslugaPar_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaPar_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaPar_disDate').setValue(null);
			base_form.findField('EvnUslugaPar_disTime').setValue(null);
			base_form.findField('EvnUslugaPar_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EUParEF_EvnUslugaDisDTPanel').show();
			this.findById('EUParEF_ToggleVisibleDisDTBtn').setText('Скрыть поля');
			base_form.findField('EvnUslugaPar_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaPar_disTime').setAllowBlank(false);
			base_form.findField('EvnUslugaPar_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
	show: function() {
		sw.Promed.swEvnUslugaParEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.restore();
		this.center();

		if ( !arguments[0] || !arguments[0].action)
		{
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		this.formStatus = 'edit'; // или 'save'
		this.files_was_loaded = false;
		this.show_complete = false;
		this.onHide = arguments[0].onHide || Ext.emptyFn;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onSaveUsluga = arguments[0].onSaveUsluga || Ext.emptyFn;
		this.onSaveProtocol = arguments[0].onSaveProtocol || Ext.emptyFn;
		this.addProtocolAfterSaveUsluga = arguments[0].addProtocolAfterSaveUsluga || false;
		this.editProtocolAfterSaveUsluga = arguments[0].editProtocolAfterSaveUsluga || false;
		// определяем параметры, влияющие на внешний вид.
		this.action = arguments[0].action;
		this.ARMType = arguments[0].ARMType || '';
		this.face = ( arguments[0].face ) ? arguments[0].face : '';
		this.is_UslugaComplex = false; // обычная или комплексная услуга. Можно определить только после загрузки формы
		this.is_doctorpar = false; // Врач параклиники или др. пользователь
		this.is_operator = false; // Оператор или работающий врач (есть список мест работы)
		this.is_protocol_edit = (this.action.inlist(['addProtocol','editProtocol'])); // Редактирование протокола или самой услуги this.ARMType == 'par' &&
		// параметры, влияющие на свободный выбор врача и отделения, а также внешний вид
		this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id || null;
		this.UserLpuSection_id = arguments[0].UserLpuSection_id || null;
		this.LpuSection_did = arguments[0].LpuSection_did || null;

		this.UserMedStaffFacts = null;
		this.UserLpuSections = null;

		this.viewOnly = false;
		if(arguments[0] && arguments[0].viewOnly)
			this.viewOnly = arguments[0].viewOnly;

		if ( !arguments[0].EvnUslugaPar_id && this.action != 'add')
		{
			sw.swMsg.alert('Сообщение', 'Отсутствует идентификатор параклинической услуги!', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		if ( this.ARMType == 'par' && !(this.UserMedStaffFact_id > 0 && this.UserLpuSection_id > 0) )
		{
			sw.swMsg.alert('Сообщение', 'Отсутствуют параметры пользователя АРМа параклиники!', function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		if ( this.ARMType == 'par' && (this.UserMedStaffFact_id > 0 && this.UserLpuSection_id > 0) )
		{
			this.is_doctorpar = true;
		}

		if ( this.is_doctorpar && this.action == 'add')
		{
			// добавление обычной паракл.услуги врачом из АРМа парки
			this.is_UslugaComplex = true;
			this.is_protocol_edit = false;
		}

		// если в настройках есть medstafffact, то имеем список мест работы
		if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
		{
			this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
		}
		// если в настройках есть lpusection, то имеем список мест работы
		if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
		{
			this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
		}

		this.is_operator = (!this.UserMedStaffFacts || !this.UserLpuSections);
		this.isVisibleDisDTPanel = false;

		var form_panel = this.findById('EvnUslugaParEditForm');
		var base_form = form_panel.getForm();
		var evn_direction_panel =this.findById('EUParEF_EvnDirectionPanel');
		var usluga_panel =this.findById('EUParEF_EvnUslugaParPanel');
		var usluga_place_panel =this.findById('EUParEF_UslugaPlacePanel');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_staff_fact_combo = base_form.findField('MedStaffFact_uid');
		var med_staff_fact_combo2 = base_form.findField('MedStaffFact_sid');
		var evndirection_setDate_field = base_form.findField('EvnDirection_setDate');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		var med_staff_fact_dir_combo = base_form.findField('MedStaffFact_did');
		var org_combo = base_form.findField('Org_did');
		var org_uid_combo = base_form.findField('Org_uid');
		var iswd_combo = base_form.findField('EvnUslugaPar_IsWithoutDirection');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var usluga_category_combo = base_form.findField('UslugaCategory_id');
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');
		var usluga_kolvo_field = base_form.findField('EvnUslugaPar_Kolvo');
		var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');
		var usluga_settime = base_form.findField('EvnUslugaPar_setTime');
		var usluga_disdate = base_form.findField('EvnUslugaPar_disDate');
		var usluga_distime = base_form.findField('EvnUslugaPar_disTime');
		var diag_code =  base_form.findField('Diag_id').getFieldValue('Diag_Code');
		var tab_main = this.findById('EUParEF_MainTab');
		var copy_all_btn = this.buttons[0];
		var copy_data_btn = this.buttons[1];
		var save_btn = this.buttons[2];

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();
		base_form.reset();
		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.hide();
		this.EvnXmlPanel.setReadOnly(true);
		this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
		this.EvnXmlPanel.MedStaffFactField = med_staff_fact_combo;
		this.toggleVisibleDisDTPanel('hide');
		this.findById('EUParEF_EvnDirectionSelectButton').enable();

		this.checkForCostPrintPanel();

		base_form.findField('Diag_id').setContainerVisible(getRegionNick().inlist(['ekb','kareliya','buryatiya', 'adygeya', 'yakutiya']));
		base_form.findField('DeseaseType_id').setContainerVisible(getRegionNick().inlist(['ekb','kareliya','buryatiya', 'adygeya', 'yakutiya']));
		base_form.findField('Mes_id').setContainerVisible(getRegionNick().inlist(['ekb','kareliya','buryatiya']));

		if (getRegionNick().inlist(['kareliya','buryatiya', 'adygeya', 'yakutiya'])){
			base_form.findField('Diag_id').setAllowBlank(false);
		}else{
			base_form.findField('Diag_id').setAllowBlank(true);
		}

		if (!getRegionNick().inlist(['ekb','kareliya','buryatiya', 'adygeya', 'yakutiya']) || !Ext.isEmpty(diag_code) && diag_code.substr(0, 1).toUpperCase() == 'Z') {
			base_form.findField('DeseaseType_id').setAllowBlank(true);
		} else {
			base_form.findField('DeseaseType_id').setAllowBlank(false);
		}

		base_form.findField('EvnUslugaPar_MedPersonalCode').setContainerVisible(getRegionNick() == 'ekb');

		base_form.findField('UslugaPlace_id').lastQuery = '';
		/*base_form.findField('UslugaPlace_id').getStore().filterBy(function(rec) {
			return rec.get('UslugaPlace_Code').toString().inlist([ '1', '2' ]);
		});*/
		
		base_form.setValues(arguments[0]);
        //base_form.findField('LpuSectionProfile_id').onShowWindow(this);
        //base_form.findField('MedSpecOms_id').onShowWindow(this);
		//base_form.clearInvalid();
		this.findById('EUParEF_PersonInformationFrame').setTitle('...');
		this.findById('EUParEF_PersonInformationFrame').clearPersonChangeParams();
		this.findById('EUParEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				this.findById('EUParEF_PersonInformationFrame').setPersonTitle();
				clearDateAfterPersonDeath('personpanelid', 'EUParEF_PersonInformationFrame', usluga_setdate);
			}.createDelegate(this)
		});

		base_form.findField('UslugaCategory_Name').hideContainer();
		base_form.findField('UslugaCategory_id').showContainer();

		usluga_place_panel.expand(false);
		evn_direction_panel.expand(false);
		// Делаем что-то в зависимости от входящих параметров

		if(this.is_operator && this.viewOnly == false)
		{
			copy_all_btn.show();
			copy_data_btn.show();
		}
		else
		{
			copy_all_btn.hide();
			copy_data_btn.hide();
		}

		var option_loadStoreSectionMedstafffact = {
			action: this.action,
			LpuSection_id: this.UserLpuSection_id,
			MedStaffFact_id: this.UserMedStaffFact_id,
			UserMedStaffFacts: this.UserMedStaffFacts,
			UserLpuSections: this.UserLpuSections
		};

		if (this.is_protocol_edit)
		{
			this.maximize();
			tab_main.setTitle('Протокол услуги');
			form_panel.hide();
			this.EvnXmlPanel.show();
		}
		else
		{
			this.minimize();
			tab_main.setTitle('Информация об услуге');
			this.EvnXmlPanel.hide();
			form_panel.show();
		}

		base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), base_form.findField('UslugaPlace_id').getValue());
		base_form.findField('EvnUslugaPar_RepFlag').hideContainer();
		base_form.findField('TumorStage_id').setContainerVisible(false);
		base_form.findField('TumorStage_id').setAllowBlank(true);
		
		if (this.action == 'add')
		{
			// или из АРМа парки - создание простой паракл.услуги или из поточн.ввода или поиска
			this.setTitle(WND_PARKA_EUPEFADD);
			this.doNotSetDeafultDiagOnEdit = false;
			setCurrentDateTime({
				callback: function() {
					usluga_setdate.fireEvent('change', usluga_setdate, usluga_setdate.getValue());
				},
				dateField: usluga_setdate,
				loadMask: false,
				setDate: true,
				setDateMaxValue: true,
				setDateMinValue: false,
				setTime: true,
				timeField: base_form.findField('EvnUslugaPar_setTime'),
				windowId: this.id
			});
			this.enableEdit(true);
			this.enableFiles();
			base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), base_form.findField('UslugaPlace_id').getValue());

			this.face = 'complex';
			//log(["sdfsd",this.findById('EUParEF_FileList').FileGrid])
			this.findById('EUParEF_FileList').FileGrid.ViewActions.action_refresh.disable();
			this.loadStoreSectionMedstafffact(option_loadStoreSectionMedstafffact);
			if ( this.is_doctorpar )
			{
				lpu_section_combo.setValue(this.UserLpuSection_id);
				lpu_section_combo.disable();
				med_staff_fact_combo.setValue(this.UserMedStaffFact_id);
				med_staff_fact_combo.disable();
			}
			/*else if ( this.UserMedStaffFacts && this.UserMedStaffFacts.length == 1 )
			{
				// список состоит из одного элемента (устанавливаем значение и не даем редактировать) > 0 &&
				lpu_section_combo.setValue(this.UserLpuSections[0]);
				lpu_section_combo.disable();
				med_staff_fact_combo.setValue(this.UserMedStaffFacts[0]);
				med_staff_fact_combo.disable();
			}*/
			else
			{
				var lpu_section_uid = lpu_section_combo.getValue();
				if ( lpu_section_uid && lpu_section_combo.getStore().getById(lpu_section_uid) ) {
					lpu_section_combo.setValue(lpu_section_uid);
				}

				var med_staff_fact_uid = med_staff_fact_combo.getValue();
				if ( med_staff_fact_uid && med_staff_fact_combo.getStore().getById(med_staff_fact_uid) ) {
					med_staff_fact_combo.setValue(med_staff_fact_uid);
				}
			}

			var med_staff_fact_sid = med_staff_fact_combo2.getValue();
			if ( med_staff_fact_sid && med_staff_fact_combo2.getStore().getById(med_staff_fact_sid) ) {
				med_staff_fact_combo2.setValue(med_staff_fact_sid);
			}

			if (getRegionNick() == 'ekb' && iswd_combo.getValue() == 1) {
				base_form.findField('EvnDirection_Num').setAllowBlank(false);
				base_form.findField('EvnDirection_setDate').setAllowBlank(false);
				base_form.findField('EvnDirection_Num').setDisabled( this.action == 'view' );
				base_form.findField('EvnDirection_setDate').setDisabled( this.action == 'view' );
				base_form.findField('EvnDirection_setDate').setValue(new Date());
				this.getEvnDirectionNumber();
			}

			var prehosp_direct_id = prehosp_direct_combo.getValue();
			if ( prehosp_direct_id )
			{
				//заполнение данных о направлении из входящих данных
				prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_id);

				var evn_direction_num = base_form.findField('EvnDirection_Num').getValue();
				var evn_direction_set_date = base_form.findField('EvnDirection_setDate').getValue();

				if ( evn_direction_num ) {
					base_form.findField('EvnDirection_Num').setValue(evn_direction_num);
				}

				if ( evn_direction_set_date ) {
					base_form.findField('EvnDirection_setDate').setValue(evn_direction_set_date);
				}

				var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);

				if ( record )
				{
					var prehosp_direct_code = record.get('PrehospDirect_Code');
					var org_type = '';

					switch ( prehosp_direct_code ) {
						case 1:
							org_type = 'lpusection';
						break;

						case 2:
							org_type = 'lpu';
						break;

						case 3:
						case 4:
						case 5:
						case 6:
							org_type = 'org';
						break;
					}

					if ( org_type == 'lpusection' ) {
						var med_staff_fact_did = med_staff_fact_dir_combo.getValue();
						var lpu_section_did = this.LpuSection_did;
						if ( lpu_section_did && lpu_section_dir_combo.getStore().getById(lpu_section_did) ) {
							lpu_section_dir_combo.setValue(lpu_section_did);
						}

						if ( med_staff_fact_did && med_staff_fact_dir_combo.getStore().getById(med_staff_fact_did) ) {
							med_staff_fact_dir_combo.setValue(med_staff_fact_did);
						}
						org_type = 'lpu';
					}

					var org_did = org_combo.getValue();
					if (org_did && org_type.length > 0)
					{
						org_combo.getStore().load({
							callback: function(records, options, success) {
								if ( success ) {
									org_combo.setValue(org_did);
									org_combo.fireEvent('change', org_combo, org_combo.getValue())
								}
							},
							params: {
								Org_id: org_did,
								OrgType: org_type
							}
						});
					}
				}
			}
			else {
				prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, null);
				if(arguments[0].EvnDirection_setDate)
				{
					var EvnDirection_setDate = Ext.util.Format.date(arguments[0].EvnDirection_setDate, 'd.m.Y');
					evndirection_setDate_field.setValue(EvnDirection_setDate);
				}
			}
			
			base_form.findField('MedProductCard_id').getStore().load({
				params: {Lpu_id: (!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && !Ext.isEmpty(base_form.findField('UslugaPlace_id').getValue()) && base_form.findField('UslugaPlace_id').getValue().inlist([2, 3])) ? base_form.findField('Lpu_uid').getValue() : getGlobalOptions().lpu_id},
				callback: function(records, options, success) {
					win.filterMedProductCard();
				}
			});

			var usluga_category_id = usluga_category_combo.getValue();
			var ucat_rec;

			if ( !usluga_category_id ) {
				if ( usluga_category_combo.getStore().getCount() == 1 ) {
					usluga_category_combo.disable();
					ucat_rec = usluga_category_combo.getStore().getAt(0);
					usluga_category_combo.setValue(ucat_rec.get('UslugaCategory_id'));
				}
				else {
					if ( getRegionNick().inlist([ 'ekb' ]) ) {
						var index = usluga_category_combo.getStore().findBy(function(rec) {
							if(rec.get('UslugaCategory_SysNick') == 'tfoms')return true;
						});
						ucat_rec = usluga_category_combo.getStore().getAt(index);
					} else if ( getRegionNick().inlist([ 'perm' ]) ) {
						var index = usluga_category_combo.getStore().findBy(function(rec) {
							if(rec.get('UslugaCategory_SysNick') == 'gost2011')return true;
						});
						ucat_rec = usluga_category_combo.getStore().getAt(index);
					} else {
						ucat_rec = usluga_category_combo.getStore().getById(usluga_category_id);
					}
				}

				if ( ucat_rec ) {
					usluga_category_combo.fireEvent('select', usluga_category_combo, ucat_rec);
				}
			}

			usluga_category_id = usluga_category_combo.getValue();
			if ( usluga_category_id ) {
				base_form.findField('UslugaComplex_id').getStore().removeAll();
				base_form.findField('UslugaComplex_id').setUslugaCategoryList([ usluga_category_combo.getFieldValue('UslugaCategory_SysNick') ]);
			}

			var usluga_complex_id = usluga_complex_combo.getValue();
			if ( usluga_complex_id ) {
				usluga_complex_combo.clearValue();
				usluga_complex_combo.fireEvent('change', usluga_complex_combo, usluga_complex_combo.getValue());

				usluga_complex_combo.getStore().load({
					callback: function() {
						usluga_complex_combo.getStore().each(function(record) {
							if ( record.get('UslugaComplex_id') == usluga_complex_id ) {
								usluga_complex_combo.setValue(record.get('UslugaComplex_id'));
								usluga_complex_combo.fireEvent('select', usluga_complex_combo, record, 0);
								usluga_complex_combo.fireEvent('change', usluga_complex_combo, usluga_complex_combo.getValue());
							}
						});
					},
					params: { UslugaComplex_id: usluga_complex_id }
				});
			}

			if (!Ext.isEmpty(base_form.findField('Diag_id').getValue())) {
				var diag_id = base_form.findField('Diag_id').getValue();
				base_form.findField('Diag_id').clearValue();
				base_form.findField('Diag_id').getStore().load({
					params: {where: "where Diag_id = " + diag_id},
					callback: function () {
						if (base_form.findField('Diag_id').getStore().getCount() > 0) {
							base_form.findField('Diag_id').setValue(diag_id);
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
							base_form.findField('Diag_id').onChange();
						}
					}
				});
			}

			if (!Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) {
				win.loadLpuData({
					LpuSection_uid: arguments[0].LpuSection_uid,
					MedStaffFact_uid: arguments[0].MedStaffFact_uid,
					MedStaffFact_sid: arguments[0].MedStaffFact_sid
				});
			}
			
			if (
				(arguments[0].EvnUslugaPar_IsHistologic && arguments[0].EvnUslugaPar_IsHistologic == 2)
				||
				(arguments[0].EvnUslugaPar_IsCytologic && arguments[0].EvnUslugaPar_IsCytologic == 2)
			) {
				//this.setDirectionHistologic(arguments[0]);
				this.setDirectionHistologicOrCytologic(arguments[0]);
			}

			if (arguments[0].FSIDI_id) {
				base_form.findField('FSIDI_id').showContainer();
				base_form.findField('FSIDI_id').setValue(arguments[0].FSIDI_id);
			}

			loadMask.hide();

			base_form.findField('PrehospDirect_id').focus(true, 250);
			this.show_complete = true;
			this.syncSize();
			this.doLayout();
		}
		else
		{
			this.doNotSetDeafultDiagOnEdit = true;

			var evn_usluga_par_id = arguments[0].EvnUslugaPar_id;
			base_form.load({
				failure: function() {
					loadMask.hide();
					sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { this.hide(); }.createDelegate(this) );
				}.createDelegate(this),
				params: {
					'EvnUslugaPar_id': evn_usluga_par_id,
					archiveRecord: win.archiveRecord
				},
				success: function(form, action, c) {
					loadMask.hide();

					var usluga_place_combo = base_form.findField('UslugaPlace_id');
					if (
						(Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && Ext.isEmpty(base_form.findField('Org_uid').getValue())) // нет другой МО
						|| (!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && base_form.findField('Lpu_uid').getValue() == getGlobalOptions().lpu_id) // или другая МО является текущей
					) {
						//указываем место выполнение Отделение ЛПУ
						usluga_place_combo.setValue(1);
						usluga_place_combo.fireEvent('change', usluga_place_combo, 1);
					} else if (Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && !Ext.isEmpty(base_form.findField('Org_uid').getValue())) { // нет другой МО и есть другая организация
						//указываем место выполнение Другая организация
						usluga_place_combo.setValue(3);
						usluga_place_combo.fireEvent('change', usluga_place_combo, 3);
					} else {
						//указываем место выполнение Другое ЛПУ
						usluga_place_combo.setValue(2);
						usluga_place_combo.fireEvent('change', usluga_place_combo, 2);
					}

					var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id');
					LpuSectionProfile_id.onChangeDateField(base_form.findField('EvnUslugaPar_setDate') , base_form.findField('EvnUslugaPar_setDate').getValue());
					LpuSectionProfile_id.getStore().on('load', function(combo, records) {
						if (!LpuSectionProfile_id.getRawValue() || Number.isInteger(LpuSectionProfile_id.getRawValue())) {
							base_form.findField('LpuSection_uid').fireEvent('select', base_form.findField('LpuSection_uid'), '');
						}
					});

					win.checkForCostPrintPanel();
					this.is_UslugaComplex = (this.face == 'complex' || usluga_complex_combo.getValue()>0);
					this.face = (this.is_UslugaComplex)?'complex':'';

					// продолжение show вынес в функцию continuationShow
					this.actionObj = action;
					this.evn_usluga_par_id = evn_usluga_par_id;

					if( Ext.isEmpty(usluga_kolvo_field.getValue()) ) usluga_kolvo_field.setValue(1);

					//Проверяем возможность редактирования документа
					if (this.action === 'edit') {
						Ext.Ajax.request({
							failure: function (response, options) {
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {
									this.hide();
								}.createDelegate(this));
							},
							params: {
								Evn_id: base_form.findField('EvnUslugaPar_id').getValue(),
								EvnUslugaPar_id: base_form.findField('EvnUslugaPar_id').getValue(),
								isForm: 'EvnUslugaParEditForm',
								MedStaffFact_id: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.MedStaffFact_id)) ? sw.Promed.MedStaffFactByUser.current.MedStaffFact_id : null,
								ArmType: (!Ext.isEmpty(sw.Promed.MedStaffFactByUser) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current) && !Ext.isEmpty(sw.Promed.MedStaffFactByUser.current.ARMType)) ? sw.Promed.MedStaffFactByUser.current.ARMType : null
							},
							success: function (response, options) {
								if (!Ext.isEmpty(response.responseText)) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if (response_obj.success == false) {
										sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_zagruzke_dannyih_formyi']);
										this.action = 'view';
									}
								}

								// продолжение show вынес в функцию continuationShow
								this.continuationShow();
							}.createDelegate(this),
							url: '/?c=Evn&m=CommonChecksForEdit'
						});
					} else {
						this.continuationShow();
					}

					if (!Ext.isEmpty(base_form.findField('FSIDI_id').getValue())) {
						base_form.findField('FSIDI_id').showContainer();
					} else {
						base_form.findField('FSIDI_id').hideContainer();
					}
				
				}.createDelegate(this),
				url: '/?c=EvnUslugaPar&m=loadEvnUslugaParEditForm'
			});
		}
	},
	continuationShow: function(){
		// продолжение функции show()
		var win = this;
		var form_panel = this.findById('EvnUslugaParEditForm');
		var base_form = form_panel.getForm();
		var evn_direction_panel =this.findById('EUParEF_EvnDirectionPanel');
		var usluga_panel =this.findById('EUParEF_EvnUslugaParPanel');
		var usluga_place_panel =this.findById('EUParEF_UslugaPlacePanel');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_staff_fact_combo = base_form.findField('MedStaffFact_uid');
		var med_staff_fact_combo2 = base_form.findField('MedStaffFact_sid');
		var evndirection_setDate_field = base_form.findField('EvnDirection_setDate');
		var lpu_section_dir_combo = base_form.findField('LpuSection_did');
		var med_staff_fact_dir_combo = base_form.findField('MedStaffFact_did');
		var org_combo = base_form.findField('Org_did');
		var org_uid_combo = base_form.findField('Org_uid');
		var iswd_combo = base_form.findField('EvnUslugaPar_IsWithoutDirection');
		var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
		var usluga_category_combo = base_form.findField('UslugaCategory_id');
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');
		var usluga_kolvo_field = base_form.findField('EvnUslugaPar_Kolvo');
		var usluga_setdate = base_form.findField('EvnUslugaPar_setDate');
		var usluga_settime = base_form.findField('EvnUslugaPar_setTime');
		var usluga_disdate = base_form.findField('EvnUslugaPar_disDate');
		var usluga_distime = base_form.findField('EvnUslugaPar_disTime');
		var tab_main = this.findById('EUParEF_MainTab');
		var copy_all_btn = this.buttons[0];
		var copy_data_btn = this.buttons[1];
		var save_btn = this.buttons[2];
		var evn_usluga_par_id = this.evn_usluga_par_id;
		var action = this.actionObj;
		
		// В зависимости от accessType переопределяем this.action
		if ( base_form.findField('accessType').getValue() == 'view' ) {
			this.action = 'view';
		}

		if ( this.action == 'edit' && base_form.findField('fromMedService').getValue() == '1' ) { // если из службы, то разрешаем редактировать только данные направления (refs #86488)
			this.action = 'editEvnDirectionOnly';
		}

		if (this.action.inlist(['view', 'editEvnDirectionOnly'])) {
			base_form.findField('UslugaCategory_id').hideContainer();
			base_form.findField('UslugaCategory_Name').showContainer();
		}

		if ( getRegionNick() == 'perm' && base_form.findField('EvnUslugaPar_IsPaid').getValue() == 2 && parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()) > 0 ) {
			base_form.findField('EvnUslugaPar_RepFlag').showContainer();

			if ( parseInt(base_form.findField('EvnUslugaPar_IndexRep').getValue()) >= parseInt(base_form.findField('EvnUslugaPar_IndexRepInReg').getValue()) ) {
				base_form.findField('EvnUslugaPar_RepFlag').setValue(true);
			}
			else {
				base_form.findField('EvnUslugaPar_RepFlag').setValue(false);
			}

		 	base_form.findField('EvnUslugaPar_RepFlag').setDisabled(this.action.inlist(['view', 'editEvnDirectionOnly']));
		}

		var setDate = usluga_setdate.getValue();
		var setTime = usluga_settime.getValue();
		var disDate = usluga_disdate.getValue();
		var disTime = usluga_distime.getValue();

		if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
			this.toggleVisibleDisDTPanel('show');
		}

		var index;
		var lpu_section_did = lpu_section_dir_combo.getValue();
		var lpu_section_uid = lpu_section_combo.getValue();
		var med_personal_uid = base_form.findField('MedPersonal_uid').getValue();
		var med_staff_fact_uid = base_form.findField('MedStaffFact_uid').getValue();
		var med_personal_sid = base_form.findField('MedPersonal_sid').getValue();
		var org_did = org_combo.getValue();
		var org_uid = org_uid_combo.getValue();
		var prehosp_direct_id = prehosp_direct_combo.getValue();
		var record;
		var usluga_complex_id = usluga_complex_combo.getValue();
		var UslugaComplexTariff_id = base_form.findField('UslugaComplexTariff_id').getValue();
		var Diag_id = base_form.findField('Diag_id').getValue();

		var evn_direction_field = base_form.findField('EvnDirection_id');
		var evn_direction_set_date_field = base_form.findField('EvnDirection_setDate');
		var evn_direction_num_field = base_form.findField('EvnDirection_Num');
		var evn_direction_id = evn_direction_field.getValue() || null;
		var evn_direction_set_date = evn_direction_set_date_field.getValue() || null;
		var evn_direction_num = evn_direction_num_field.getValue() || null;
		/*
		option_loadStoreSectionMedstafffact.action = this.action;
		option_loadStoreSectionMedstafffact.LpuSection_id = lpu_section_uid;
		option_loadStoreSectionMedstafffact.MedStaffFact_id = med_personal_uid;
		option_loadStoreSectionMedstafffact.onDate = Ext.util.Format.date(usluga_setdate.getValue(), 'd.m.Y');
		*/
		var params_for_debug = {
			action: this.action,
			ARMType: this.ARMType,
			face: this.face,
			is_protocol_edit: this.is_protocol_edit,
			is_UslugaComplex: this.is_UslugaComplex,
			is_doctorpar: this.is_doctorpar,
			is_operator: this.is_operator,
			lpu_section_uid: lpu_section_uid,
			med_personal_uid: med_personal_uid,
			med_personal_sid: med_personal_sid,
			prehosp_direct_id: prehosp_direct_id,
			evn_direction_id: evn_direction_id,
			lpu_section_did: lpu_section_did,
			org_did: org_did,
			UserMedStaffFact_id: this.UserMedStaffFact_id,
			UserMedStaffFacts: this.UserMedStaffFacts,
			UserLpuSection_id: this.UserLpuSection_id,
			UserLpuSections: this.UserLpuSections
		};

		if (!Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) {
			win.loadLpuData({
				LpuSection_uid: !Ext.isEmpty(action.result.data.LpuSection_uid)?action.result.data.LpuSection_uid:null,
				MedPersonal_uid: !Ext.isEmpty(action.result.data.MedPersonal_uid)?action.result.data.MedPersonal_uid:null,
				MedPersonal_sid: !Ext.isEmpty(action.result.data.MedPersonal_sid)?action.result.data.MedPersonal_sid:null
			});
		}
		//base_form.clearInvalid();
		this.enableFiles();
		this.findById('EUParEF_FileList').FileGrid.ViewActions.action_refresh.enable();
		switch ( this.action ) {
			case 'edit':
			case 'view':
			case 'editEvnDirectionOnly':
				this.loadStoreSectionMedstafffact({ onDate: Ext.util.Format.date(usluga_setdate.getValue(), 'd.m.Y') });
				
				base_form.findField('MedProductCard_id').getStore().load({
					params: {Lpu_id: (!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && !Ext.isEmpty(base_form.findField('UslugaPlace_id').getValue()) && base_form.findField('UslugaPlace_id').getValue().inlist([2, 3])) ? base_form.findField('Lpu_uid').getValue() : getGlobalOptions().lpu_id},
					callback: function() {
						if (!Ext.isEmpty(base_form.findField('LpuSection_uid').getValue())) {
							base_form.findField('MedProductCard_id').getStore().filterBy(function(rec) {
								return (rec.get('LpuSection_id') == base_form.findField('LpuSection_uid').getValue());
							});
						}
						if ( base_form.findField('MedProductCard_id').getStore().getById(base_form.findField('MedProductCard_id').getValue()) ) {	
							base_form.findField('MedProductCard_id').setValue(base_form.findField('MedProductCard_id').getValue());
						}
					}
				});

                if (usluga_complex_id > 0) {
                    usluga_complex_combo.getStore().removeAll();
					usluga_complex_combo.getStore().load({
						callback: function() {
							if ( usluga_complex_combo.getStore().getCount() > 0 ) {
								usluga_complex_combo.setValue(usluga_complex_id);

								var usluga_category_id = usluga_complex_combo.getStore().getAt(0).get('UslugaCategory_id');

								index = usluga_category_combo.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_id') == usluga_category_id);
								});

								if ( index >= 0 ) {
									usluga_category_combo.setValue(usluga_category_id);
								}
							}
							else {
								usluga_complex_combo.clearValue();
							}
							usluga_complex_combo.fireEvent('change', usluga_complex_combo, usluga_complex_combo.getValue());
						}.createDelegate(this),
						params: {
							UslugaComplex_id: usluga_complex_id
						}
					});

					base_form.findField('UslugaComplexTariff_id').setParams({
						LpuSection_id: lpu_section_uid
						,PayType_id: base_form.findField('PayType_id').getValue()
						,Person_id: base_form.findField('Person_id').getValue()
						,UslugaComplex_id: usluga_complex_id
						,UslugaComplexTariff_Date: base_form.findField('EvnUslugaPar_setDate').getValue()
					});

					if ( !Ext.isEmpty(UslugaComplexTariff_id) ) {
						base_form.findField('UslugaComplexTariff_id').getStore().load({
							callback: function() {
								if ( base_form.findField('UslugaComplexTariff_id').getStore().getCount() > 0 ) {
									base_form.findField('UslugaComplexTariff_id').setValue(UslugaComplexTariff_id);
									base_form.findField('UslugaComplexTariff_id').fireEvent('change', base_form.findField('UslugaComplexTariff_id'), UslugaComplexTariff_id);
								}
								else {
									base_form.findField('UslugaComplexTariff_id').clearValue();
								}
							}.createDelegate(this),
							params: {
								UslugaComplexTariff_id: UslugaComplexTariff_id
							}
						});
					}
                } else {
                    lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_combo.getValue());
                }

				base_form.findField('Diag_id').clearValue();

				if (!Ext.isEmpty(Diag_id)) {
					base_form.findField('Diag_id').getStore().load({
						params: {where: "where Diag_id = " + Diag_id},
						callback: function () {
							if (base_form.findField('Diag_id').getStore().getCount() > 0) {
								base_form.findField('Diag_id').setValue(Diag_id);
								base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
								base_form.findField('Diag_id').onChange();
							}
						}
					});
				}

				if (!Ext.isEmpty(base_form.findField('Mes_id').getValue())) {
					var mes_id = base_form.findField('Mes_id').getValue();
					base_form.findField('Mes_id').clearValue();
					base_form.findField('Mes_id').getStore().load({
						params: {
							Mes_id: mes_id
						},
						callback: function () {
							if (base_form.findField('Mes_id').getStore().getCount() > 0) {
								base_form.findField('Mes_id').setValue(mes_id);
								base_form.findField('Mes_id').fireEvent('change', base_form.findField('Mes_id'), base_form.findField('Mes_id').getValue());
							}
						}
					});
				}

				if(this.action == 'edit')
				{
					this.setTitle(WND_PARKA_EUPEFEDIT);
					this.enableEdit(true);

					base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), base_form.findField('UslugaPlace_id').getValue());
					// Если услуга комплексная, и еще не заполнена (только заказана, определяем по отсутствию даты),
					// то имеет смысл заполнить сразу дату и время (текущей)
					// и врача (тем под которым открыли услугу)
					var isSetDate = false;
					if (this.is_UslugaComplex && !usluga_setdate.getValue())
					{
						isSetDate = true;
					}
					setCurrentDateTime({
						dateField: usluga_setdate,
						timeField: base_form.findField('EvnUslugaPar_setTime'),
						loadMask: false,
						setDate: isSetDate,
						setTime: isSetDate,
						setDateMaxValue: true,
						windowId: this.id
					});
					prehosp_direct_combo.focus(true, 250);

					this.findById('EUParEF_PersonInformationFrame').setPersonChangeParams({
						callback: function(data) {
							this.hide();
						}.createDelegate(this)
						,Evn_id: evn_usluga_par_id
					});
					this.findById('EUParEF_PersonInformationFrame').setPersonTitle();
				}
				else {
					this.setTitle(WND_PARKA_EUPEFVIEW);
					this.enableEdit(false);
					this.buttons[this.buttons.length - 1].focus();

					this.findById('EUParEF_PersonInformationFrame').clearPersonChangeParams();
				}
				var lpu_section_record = lpu_section_combo.getStore().getById(lpu_section_uid);
				if ( lpu_section_record ) {
					lpu_section_combo.setValue(lpu_section_record.get('LpuSection_id'));
				}
				else {
					lpu_section_combo.clearValue();
				}

				////////
				if(lpu_section_uid)
				{
					setMedStaffFactGlobalStoreFilter({
						LpuSection_id: lpu_section_uid
					});
				}

				// комбобокс средний медперсонал
				med_staff_fact_combo2.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
				index = med_staff_fact_combo2.getStore().findBy(function(r) {
					if( r.get('MedPersonal_id') == med_personal_sid )
					{
						med_staff_fact_combo2.setValue(r.get('MedStaffFact_id'));
						return true;
					}
					return false;
				}.createDelegate(this));
				////////

				index = med_staff_fact_combo.getStore().findBy(function(rec) {
					// Если отделение и врач выбраны, любо если выбрано отделение, врач не заполнен, и форма открыта из места работы врача
					if ( rec.get('LpuSection_id') == lpu_section_uid && ( ( rec.get('MedPersonal_id') == med_personal_uid && med_staff_fact_uid == rec.get('MedStaffFact_id') ) || (med_personal_uid==null && rec.get('MedStaffFact_id') == this.UserMedStaffFact_id) ) ) {
						return true;
					} else {
						return false;
					}
				}.createDelegate(this));
				var med_staff_fact_record = med_staff_fact_combo.getStore().getAt(index);
				if ( med_staff_fact_record ) {
					med_staff_fact_combo.setValue(med_staff_fact_record.get('MedStaffFact_id'));
				}
				else {
					med_staff_fact_combo.clearValue();
				}

				prehosp_direct_combo.fireEvent('change', prehosp_direct_combo, prehosp_direct_combo.getValue());

				evn_direction_set_date_field.setValue(evn_direction_set_date);
				evn_direction_num_field.setValue(evn_direction_num);

				var prehosp_direct_code = prehosp_direct_combo.getFieldValue('PrehospDirect_Code');
				
				var org_type = 'org';
				switch ( prehosp_direct_code ) {
					case 1:
					case 2:
						org_type = 'lpu';
						break;

					case 3:
					case 4:
					case 5:
					case 6:
						org_type = 'org';
						break;
				}

				if (org_did)
				{
					org_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								if (org_type == 'lpu') {
									if (lpu_section_did) {
										lpu_section_dir_combo.setValue(lpu_section_did);
									}
								}
								org_combo.setValue(org_did);
								org_combo.fireEvent('change', org_combo, org_combo.getValue())
							}
						},
						params: {
							Org_id: org_did,
							OrgType: org_type
						}
					});
				}

				if (org_uid)
				{
					org_uid_combo.getStore().load({
						callback: function(records, options, success) {
							if ( success ) {
								if (org_type == 'lpu') {
									if (lpu_section_did) {
										lpu_section_dir_combo.setValue(lpu_section_did);
									}
								}
								org_uid_combo.setValue(org_uid);
								org_uid_combo.fireEvent('change', org_uid_combo, org_uid_combo.getValue())
							}
						},
						params: {
							Org_id: org_uid,
							OrgType: org_type
						}
					});
				}

				if (this.action == 'editEvnDirectionOnly') {
					this.setTitle(WND_PARKA_EUPEFEDIT);
					// можно редактировать только данные направления
					base_form.findField('PrehospDirect_id').enable();
					base_form.findField('Org_did').enable();
					base_form.findField('EvnDirection_Num').enable();
					base_form.findField('EvnDirection_setDate').enable();
					base_form.findField('LpuSection_did').enable();
					base_form.findField('MedStaffFact_did').enable();
					base_form.findField('EvnDirection_id').setValue(action.result.data.EvnDirection_id);
					this.buttons[2].show();

					this.buttons[this.buttons.length - 1].focus();

					this.findById('EUParEF_PersonInformationFrame').clearPersonChangeParams();
				}
			break;

			case 'addProtocol': case 'editProtocol':
				this.enableEdit(false);
				save_btn.show();
				//### show
				var XmlTemplate_id = base_form.findField('XmlTemplate_id').getValue();
				if ( this.action == 'editProtocol' )
				{
					this.setTitle('Параклиническая услуга: Редактирование протокола');
					this.EvnXmlPanel.setReadOnly(false);
					this.EvnXmlPanel.setBaseParams({
						userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Evn_id: evn_usluga_par_id
					});
					this.EvnXmlPanel.doLoadData();
				}
				else
				{
					this.setTitle('Параклиническая услуга: Добавление протокола');
					this.EvnXmlPanel.doReset();
					this.EvnXmlPanel.setReadOnly(false);
					this.EvnXmlPanel.setBaseParams({
						userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
						UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
						Server_id: base_form.findField('Server_id').getValue(),
						Evn_id: base_form.findField('EvnUslugaPar_id').getValue()
					});
				}
			break;
			default:
				this.hide();
			break;
		}

		if (action.result.data.EvnDirectionHistologic_id) { // если услуга связана с направлением на патологогистологическое исследование, то поля заблокированы
			this.disableAllDirectionFields();
			base_form.findField('EvnDirection_id').setValue(action.result.data.EvnDirection_id);
		}
		
		this.show_complete = true;
		this.syncSize();
		this.doLayout();
		//log('show_complete');
	}
});
