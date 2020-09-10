/**
* swEvnDirectionEditWindow - окно редактирования/добавления направления.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-27.11.2009
* @comment      Префикс для id компонентов EDirEF (EvnDirectionEditForm)
*
*
* @input data: action - действие (add, edit, view, editpaytype)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnDirectionEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	frame: true,
	profileUslugaLinkStore: new Ext.data.JsonStore({
		fields: [
			{name: 'ProfileUslugaLink_id', type: 'int'},
			{name: 'LpuSectionProfile_id', type: 'int'},
			{name: 'UslugaComplex_id', type: 'int'},
			{name: 'UslugaComplex_AttributeList', type: 'string'}
		],
		key: 'ProfileUslugaLink_id',
		sortInfo: {
			field: 'ProfileUslugaLink_id'
		},
		url: '/?c=ExchangeBL&m=loadProfileUslugaLink'
	}),
	/**
	 * Возвращает данные введенные-выбранные в форме без сохранения объекта в БД
	 */
	returnData: function(params) {
		var f = this.findById('EvnDirectionEditForm');
		params = Ext.apply(f.getForm().getValues(), params);

		//добавление кем направлен... своя МО или другая
		if (params.Lpu_did) {
			if (params.Lpu_did === params.Lpu_sid)
				params.PrehospDirect_id = 1;
			else
				params.PrehospDirect_id = 2;
		}

		if (getRegionNick() == 'kz' && params.DirType_id && params.DirType_id.inlist(['1', '5', '4'])) {
			var changedData = this.FileUploadPanelKZ.getJSONChangedData(),
				url = this.FileUploadPanelKZ.saveChangesUrl;
			params.onDirSave = function (EvnDirection_id) {
				if (EvnDirection_id) {
					var params = new Object();
					params = {
						Evn_id: EvnDirection_id,
						changedData: changedData
					};

					Ext.Ajax.request({
						url: url,
						callback: function(options, success, response) {
							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
							}
						},
						method: 'post',
						params: params
					});
				}
			}.createDelegate(this);
		}

		if (getRegionNick() != 'krym') {
			this.callback({evnDirectionData: params});
		}
		if (getRegionNick() != 'kz') {
			if (getRegionNick() == 'krym'){
				Ext.Ajax.request({
					url: '/?c=EvnDirection&m=checkOnkoDiagforDiagnosisResult',
					params: {
						Diag_id: params.Diag_id,
						EvnDirection_setDate: params.EvnDirection_setDate
					},
					failure: function (result_form, action) {
						if (action.result) {
							if (action.result.Error_Msg) {
								sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
							}
						}
					},
					success: function(response, options)
					{
						if ( response && typeof response.responseText == 'string' && response.responseText.length > 0 ) {
							var result = Ext.util.JSON.decode(response.responseText);
							if (result.ho_warning) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function (buttonId, text, obj) {
										if (buttonId == 'yes') {
											params.checkDiagforDiagnosisResultDiagLink = 2;
											this.callback({evnDirectionData: params});
											this.saved = true;
											this.hide();
										}
									}.createDelegate(this),
									icon: Ext.MessageBox.QUESTION,
									msg: result.ho_warning,
									title: 'Предупреждение'
								});
								return false;
							} else if (result) {
								this.callback({evnDirectionData: params});
								this.saved = true;
								this.hide();
							}
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
						}
					}.createDelegate(this)
				});
			}else {
				this.saved = true;
				this.hide();
			}
		}
	},
	refreshFilesPanel: function(EvnDirection_id, dir_type_id){
		var win = this;
		var form = this.findById('EvnDirectionEditForm');
		var base_form = form.getForm();
		if(!dir_type_id)
			dir_type_id = base_form.findField('DirType_id').getValue();
		/*if(!EvnDirection_id)
			EvnDirection_id = base_form.findField('EvnDirection_id').getValue();*/
		if (getRegionNick() == 'kz' && dir_type_id && dir_type_id.inlist(['1', '5', '4'])) {

			if(this.action == 'add')
				this.FileUploadPanelKZ.enable();
			else this.FileUploadPanelKZ.disable();
			this.FilePanel.setVisible(true);
			this.FileUploadPanelKZ.reset();
			if (!Ext.isEmpty(EvnDirection_id) && EvnDirection_id) {
				this.FileUploadPanelKZ.listParams = {
					Evn_id: EvnDirection_id
					//saveOnce: true
				};
				this.FileUploadPanelKZ.loadData({
					Evn_id: EvnDirection_id,
					add_empty_combo: false
				});
			}
		} else {
			this.FilePanel.setVisible(false);
		}
	},
	doSave: function() {
		var win = this;
		var form = this.findById('EvnDirectionEditForm');
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					console.log(form.getFirstInvalidEl());
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		//#PROMEDWEB-110
		//Проверка на закрытость интерента
		if (getRegionNick() == 'krym'){
			var LpuSection_did = base_form.findField('LpuSection_did').getValue();
			if (LpuSection_did){
				var close_date = undefined;
				if(base_form.findField('LpuSection_did').getStore()
					&& base_form.findField('LpuSection_did').getStore().getById(LpuSection_did)
					&& base_form.findField('LpuSection_did').getStore().getById(LpuSection_did).data)
					close_date = base_form.findField('LpuSection_did').getStore().getById(LpuSection_did).data.LpuSection_disDate;
				if (close_date && Date.now() > close_date) {
					sw.swMsg.alert(langs('Ошибка'), langs('Отделение закрыто, запись запрещена.'));
					return false;
				}
			}
		}		

		var index;
		var params = new Object();
		var record;
		
		if (getRegionNick()=='kz' && this.FileUploadPanelKZ && this.FileUploadPanelKZ.getChangedData().length > 0) {
			params.Files = [];

			this.FileUploadPanelKZ.getChangedData().forEach(function(currentValue){
				params.Files.push({
					'EvnMediaData_FilePath': currentValue.EvnMediaData_FilePath,
					'EvnMediaData_FileName': currentValue.EvnMediaData_FileName
				});
			});

			params.Files = JSON.stringify(params.Files);
		}

		if (getRegionNick() == 'kz' && base_form.findField('DirType_id').getValue().inlist([1,5]) && !win.ignorePersonPrivilegeCheck) {
			var conf = {
				Person_id: base_form.findField('Person_id').getValue(),
				PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
				Server_id: base_form.findField('Server_id').getValue(),
				EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
				win: win,
				callback: function() {
					win.ignorePersonPrivilegeCheck = true;
					win.doSave();
				}
			};
			sw.Promed.PersonPrivilege.checkExists(conf);
			return false;
		}

		params.EvnDirection_Num = base_form.findField('EvnDirection_Num').getValue();
		/*log(base_form.findField('MedStaffFact_id'),'sssssss');
		return false;*/
		params.From_MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();
		base_form.findField('MedPersonal_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('MedPersonal_id')||0);
		if (base_form.findField('EvnDirection_IsReceive').getValue() !== 2 || this.mode === 'nosave') {
			base_form.findField('LpuSection_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id') || 0);
		}
		params.MedStaffFact_sid = this.From_MedStaffFact_id;
		base_form.findField('MedPersonal_zid').setValue(base_form.findField('MedStaffFact_zid').getFieldValue('MedPersonal_id')||0);

		params.payTypeKz = base_form.findField('PayType_id').getValue();

		if ( base_form.findField('PayType_id').disabled ) {
			params.PayType_id = base_form.findField('PayType_id').getValue();
		}
		if ( base_form.findField('DirType_id').disabled ) {
			params.DirType_id = base_form.findField('DirType_id').getValue();
		}
		if ( base_form.findField('Lpu_did').disabled ) {
			params.Lpu_did = base_form.findField('Lpu_did').getValue();
		}
		if ( base_form.findField('Org_oid').disabled ) {
			params.Org_oid = base_form.findField('Org_oid').getValue();
		}
		if ( base_form.findField('LpuUnitType_did').disabled ) {
			params.LpuUnitType_did = base_form.findField('LpuUnitType_did').getValue();
		}
		if ( base_form.findField('MedService_id').disabled ) {
			params.MedService_id = base_form.findField('MedService_id').getValue();
		}
		if ( base_form.findField('MedicalCareFormType_id').disabled ) {
			params.MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue();
		}
		if ( base_form.findField('StudyTarget_id').disabled ) {
			params.StudyTarget_id = base_form.findField('StudyTarget_id').getValue();
		}
		if ( base_form.findField('PayTypeKAZ_id').disabled ) {
			params.PayTypeKAZ_id = base_form.findField('PayTypeKAZ_id').getValue();
		}
		if ( base_form.findField('TreatmentClass_id').disabled ) {
			params.TreatmentClass_id = base_form.findField('TreatmentClass_id').getValue();
		}
		if ( base_form.findField('Diag_id').disabled ) {
			params.Diag_id = base_form.findField('Diag_id').getValue();
		}
		if ( base_form.findField('UslugaComplex_did').disabled ) {
			params.UslugaComplex_did = base_form.findField('UslugaComplex_did').getValue();
		}
		params.FSIDI_id = base_form.findField('FSIDI_id').getValue();

		if (getRegionNick() == 'msk') {
			params.CVIConsultRKC_id = win.CVIConsultRKC_id || null;
			params.RepositoryObserv_sid = win.RepositoryObserv_sid || null;
			params.isRKC = win.isRKC || null;
		}
		if (getRegionNick() == 'ufa') {
			var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
			var MedSpecOms_Code = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_Code');

			if (this.ZNOinfo == true && diag_code == 'Z03.1' && !MedSpecOms_Code.inlist(['17', '41', '73', '74', '82', '243', '265'])) {
				var date = new Date(base_form.findField('EvnDirection_setDateTime').getValue().replace(/(\d{2}).(\d{2}).(\d{4})/, "$2/$1/$3"));
				if (date > new Date().setDate(new Date().getDate() + 5)) {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						msg: langs('Запись пациента должна быть не позднее 5 дней от текущей даты'),
						fn: function() {
							base_form.findField('EvnDirection_setDateTime').focus(true);
						},
						icon: Ext.Msg.WARNING,
						title: ERR_INVFIELDS_TIT
					});
					return false;
				}
			}
		}

		this.closable = true;
		if (this.mode == 'nosave') { // если режим формы = без сохранения, то просто возвращаетм данные
			this.returnData(params);
			return true;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();
		if (base_form.findField('DirType_id').getValue() == 17 || base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
			params.toQueue = 1;
			
			if(base_form.findField('MedService_id').isVisible() && !base_form.findField('MedService_id').disabled)
				params.MedService_isEnabled = true;//в помощь серверной проверке на обязательность поля, иначе ложное срабатывание: https://redmine.swan.perm.ru/issues/114276#note-52
		}

		if (this.action == 'editpaytype') {
			// сохраняем только вид оплаты, т.к. направление может быть и автоматическим и на форме нет всех полей направления, то вызывать метод сохранения всей формы как то непрваильно
			Ext.Ajax.request({
				url: '/?c=EvnDirection&m=setPayType',
				params: {
					EvnDirection_id: base_form.findField('EvnDirection_id').getValue(),
					PayType_id: base_form.findField('PayType_id').getValue()
				},
				callback: function() {
					loadMask.hide();
					win.hide();
				}
			});
		} else {
			base_form.submit({
				failure: function (result_form, action) {
					loadMask.hide();

					if (action.result) {
						if (action.result.Error_Msg) {
							sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 3]'));
						}
					}
				},
				params: params,
				success: function (result_form, action) {
					loadMask.hide();

					if (action.result && action.result.EvnDirection_id > 0) {
						base_form.findField('EvnDirection_id').setValue(action.result.EvnDirection_id);

						var response = new Object();

						response.Diag_id = base_form.findField('Diag_id').getValue();
						response.PayType_id = base_form.findField('PayType_id').getValue();
						response.DirType_id = base_form.findField('DirType_id').getValue();
						response.DirType_Name = '';
						response.EvnDirection_Descr = base_form.findField('EvnDirection_Descr').getValue();
						response.EvnDirection_id = base_form.findField('EvnDirection_id').getValue();
						response.EvnDirection_Num = params.EvnDirection_Num;
						response.EvnDirection_pid = base_form.findField('EvnDirection_pid').getValue();
						response.DopDispInfoConsent_id = base_form.findField('DopDispInfoConsent_id').getValue();
						response.EvnDirection_IsCito = base_form.findField('EvnDirection_IsCito').getValue();
						response.MedService_id = base_form.findField('MedService_id').getValue();
						response.Resource_id = base_form.findField('Resource_id').getValue();
						response.EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
						response.LpuSectionProfile_Name = '';
						response.MedPersonal_id = params.MedPersonal_id;
						response.MedPersonal_zid = params.MedPersonal_zid;
						response.Person_id = base_form.findField('Person_id').getValue();
						response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
						response.Server_id = base_form.findField('Server_id').getValue();
						response.EvnXml_id = base_form.findField('EvnXml_id').getValue();
						response.EvnDirection_desDT = base_form.findField('EvnDirection_desDT').getValue();
						response.EvnDirectionOper_IsAgree = base_form.findField('EvnDirectionOper_IsAgree').getValue();

						record = base_form.findField('DirType_id').getStore().getById(base_form.findField('DirType_id').getValue());
						if (record) {
							response.DirType_Name = record.get('DirType_Name');
						}

						record = base_form.findField('LpuSectionProfile_id').getStore().getById(base_form.findField('LpuSectionProfile_id').getValue());
						if (record) {
							response.LpuSectionProfile_Name = record.get('LpuSectionProfile_Name');
						}
						if ( getRegionNick() == 'kz' && params.DirType_id && params.DirType_id.inlist(['1', '5', '4']) ) {
							this.FileUploadPanelKZ.listParams = {Evn_id: action.result.EvnDirection_id};
							this.FileUploadPanelKZ.saveChanges();
						}

						this.callback({evnDirectionData: response});
						this.saved = true;
						this.hide();
						this.action = 'view';
						if (getRegionNick() == 'ufa' && this.allowQuestionPrintEvnDirection) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								msg: langs('Распечатать стат. талон?'),
								title: langs('Вопрос'),
								icon: Ext.MessageBox.QUESTION,
								fn: function (buttonId) {
									if (buttonId === 'yes') {
										var params = new Object();
										params.type = 'EvnPL';
										printEvnPLBlank(params);
									}
									else {
										this.hide();
									}
								}.createDelegate(this)
							});
						} else if (this.allowQuestionPrintEvnDirection) {
							sw.swMsg.show({
								buttons: Ext.Msg.YESNO,
								msg: langs('Вывести направление на печать?'),
								title: langs('Вопрос'),
								icon: Ext.MessageBox.QUESTION,
								fn: function (buttonId) {
									if (buttonId === 'yes') {
										this.printEvnDirection();
									}
									else {
										this.hide();
									}
								}.createDelegate(this)
							});
						}
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
					}
				}.createDelegate(this)
			});
		}
	},
	enableEdit: function(enable) {
		if ( enable ) {
			// this.findById('EDirEF_').enable();

			this.buttons[0].enable();
		}
		else {
			// this.findById('EDirEF_').disable();

			this.buttons[0].disable();
		}
	},
	directionNumberParams: {
		DirType_id: null,
		EvnDirection_Num: null,
		processing: false,
		year: null
	},
	/**
	 * @task https://redmine.swan.perm.ru//issues/108892
	 */
	getEvnDirectionNumber: function() {
		var
			win = this,
			base_form = win.findById('EvnDirectionEditForm').getForm(),
			DirType_id = base_form.findField('DirType_id').getValue(),
			EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue(),
			loadMask,
			year = typeof EvnDirection_setDate == 'object' ? EvnDirection_setDate.format('Y') : new Date().format('Y');

		if (
			!Ext.isEmpty(EvnDirection_setDate)
			&& Ext.isEmpty(win.directionNumberParams.EvnDirection_Num)
			&& (
				(getRegionNick() == 'astra' && (win.directionNumberParams.year != year || win.directionNumberParams.DirType_id != DirType_id))
				|| (getRegionNick().inlist([ 'ekb', 'khak' ]) && win.directionNumberParams.year != year)
				|| (!getRegionNick().inlist([ 'astra', 'ekb', 'khak' ]) && Ext.isEmpty(base_form.findField('EvnDirection_Num').getValue()) && win.directionNumberParams.processing == false)
			)
		) {
			if ( 17 == DirType_id || base_form.findField('EvnDirection_IsReceive').getValue() != 2 ) {
				loadMask = new Ext.LoadMask(this.getEl(), { msg: "Генерация номера направления..." });
				loadMask.show();

				win.directionNumberParams.DirType_id = DirType_id;
				win.directionNumberParams.processing = true;
				win.directionNumberParams.year = year;

				Ext.Ajax.request({
					callback: function (options, success, response) {
						loadMask.hide();

						win.directionNumberParams.processing = false;

						if (success) {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							base_form.findField('EvnDirection_Num').setValue(response_obj.EvnDirection_Num);
							base_form.findField('EvnDirection_Num').disable();
						}
						else {
							sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении номера направления'), function () {
								base_form.findField('EvnDirection_setDate').focus(true);
							});
						}
					}.createDelegate(this),
					params: {
						DirType_id: DirType_id,
						year: year
					},
					url: '/?c=EvnDirection&m=getEvnDirectionNumber'
				});
			}
		}
	},
	openRecordWindow:function(){
		var win = this;
		var base_form = Ext.getCmp('EvnDirectionEditForm').getForm();
		var params = {
			Person_Firname:this.findById('EDirEF_PersonInformationFrame').getFieldValue('Person_Firname'),
			Person_Secname:this.findById('EDirEF_PersonInformationFrame').getFieldValue('Person_Secname'),
			Person_Surname:this.findById('EDirEF_PersonInformationFrame').getFieldValue('Person_Surname'),
			Person_id:base_form.findField('Person_id').getValue(),
			PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
			Server_id:base_form.findField('Server_id').getValue(),
			type:this.type,
			directionData: {
				DirType_id:base_form.findField('DirType_id').getValue(),
				MedStaffFact_id:base_form.findField('MedStaffFact_id').getValue(),
				Lpu_did:base_form.findField('Lpu_did').getValue(),
				MedPersonal_id:base_form.findField('MedPersonal_id').getValue(),
				LpuSection_id:base_form.findField('LpuSection_id').getValue(),
				LpuSectionProfile_id:base_form.findField('LpuSectionProfile_id').getValue(),
				Diag_id:base_form.findField('Diag_id').getValue(),
				EvnDirection_Num:base_form.findField('EvnDirection_Num').getValue(),
				Lpu_Nick:base_form.findField('Lpu_did').getRawValue()
			},
			personData:{
				Person_Firname:this.findById('EDirEF_PersonInformationFrame').getFieldValue('Person_Firname'),
				Person_Secname:this.findById('EDirEF_PersonInformationFrame').getFieldValue('Person_Secname'),
				Person_Surname:this.findById('EDirEF_PersonInformationFrame').getFieldValue('Person_Surname'),
				Person_id:base_form.findField('Person_id').getValue(),
				PersonEvn_id:base_form.findField('PersonEvn_id').getValue(),
				Server_id:base_form.findField('Server_id').getValue()
			},
			onDirection:win.actionEdit.createDelegate(this)
		};
		getWnd('swDirectionMasterWindow').show(params);
	},
	loadMedPersonalCombo: function() {
		var win = this,
			base_form = win.findById('EvnDirectionEditForm').getForm(),
			is_receive_field = base_form.findField('EvnDirection_IsReceive'),
			lpu_send_field = base_form.findField('Lpu_sid'),
			mp_field = base_form.findField('MedPersonal_id'),
			dlocode_field = base_form.findField('MedPersonal_Code'),
			ls_field = base_form.findField('LpuSection_id'),
			msf_field = base_form.findField('MedStaffFact_id'),
			mpz_field = base_form.findField('MedPersonal_zid'),
			msfz_field = base_form.findField('MedStaffFact_zid');

		var on_load_msf = function() {
			log('on_load_msf', msf_field.getStore().getCount(), msf_field.getValue(), mp_field.getValue());
			win.filterMedPersonalCombo();
			var rec, index = -1;
			if (msf_field.getValue()) {
				rec = msf_field.getStore().getById(msf_field.getValue());
			} else if (mp_field.getValue()) {
				index = msf_field.getStore().findBy(function(rec) {
					return (
						rec.get('MedPersonal_id') == mp_field.getValue()
						&& rec.get('LpuSection_id') == ls_field.getValue()
					);
				});
				if ( index >= 0) {
					rec = msf_field.getStore().getAt(index);
				} else {
					index = msf_field.getStore().findBy(function (rec) {
						return ( rec.get('MedPersonal_id') == mp_field.getValue() );
					});
					if (index >= 0) {
						rec = msf_field.getStore().getAt(index);
					}
				}
			}
			if (rec) {
				log('on_load_msf', rec, rec.get('MedStaffFact_id'));
				msf_field.setValue(rec.get('MedStaffFact_id'));
				dlocode_field.setValue( msf_field.getStore().getById(rec.get('MedStaffFact_id')).data.MedPersonal_DloCode );
			} else {
				log('on_load_msf not found rec', msf_field.getStore());
				msf_field.setValue(null);
				dlocode_field.setValue(null);
			}
		};

		var on_load_msfz = function() {
			log('on_load_msfz', msfz_field.getStore().getCount(), msfz_field.getValue(), mpz_field.getValue());
			win.filterMedPersonalCombo();
			var rec, index = -1;
			if (msfz_field.getValue()) {
				rec = msfz_field.getStore().getById(msfz_field.getValue());
			} else if (mpz_field.getValue()) {
				index = msfz_field.getStore().findBy(function(rec) {
					return ( rec.get('MedPersonal_id') == mpz_field.getValue() );
				});
				if ( index >= 0) {
					rec = msfz_field.getStore().getAt(index);
				}
			}
			if (rec) {
				log('on_load_msfz', rec, rec.get('MedStaffFact_id'));
				msfz_field.setValue(rec.get('MedStaffFact_id'));
			} else {
				log('on_load_msfz not found rec', msfz_field.getStore());
				msfz_field.setValue(null);
			}
			var lpu_sort = getGlobalOptions().CurLpuSection_id;
			msfz_field.getStore().each(function(s){
				if(s.get('LpuSection_id')===lpu_sort){s.set('SortVal',1);}
				else {s.set('SortVal',2);}
				s.commit();
			});
			msfz_field.getStore().sort('MedPersonal_Fio');
			msfz_field.getStore().sort('SortVal');
			msfz_field.getStore().applySort();
		};

		msf_field.getStore().removeAll();
		msf_field.lastQuery = '';
		msfz_field.getStore().removeAll();
		msfz_field.lastQuery = '';
		swMedStaffFactGlobalStore.clearFilter();
		var msf_load_params = {mode: 'combo'};
		var msfz_load_params = {mode: 'combo'};
		if (is_receive_field.getValue() == 2 || lpu_send_field.getValue()) {
			// если внешнее направление, то врач и заведующий зависят от данного комбо
			msf_load_params.Lpu_id = lpu_send_field.getValue();
			msfz_load_params.Lpu_id = lpu_send_field.getValue();
		} else {
			msf_load_params.Lpu_id = getGlobalOptions().lpu_id;
			msfz_load_params.Lpu_id = getGlobalOptions().lpu_id;
		}
		switch (true) {
			case (ls_field.getValue() > 0 && mp_field.getValue() > 0 && mpz_field.getValue() > 0):
				msf_load_params.LpuSection_id = ls_field.getValue();
				//msfz_load_params.LpuSection_id = ls_field.getValue();
				msf_load_params.MedPersonal_id = mp_field.getValue();
				msfz_load_params.MedPersonal_id = mpz_field.getValue();
				break;
			case (ls_field.getValue() > 0 && mp_field.getValue() > 0):
				msf_load_params.LpuSection_id = ls_field.getValue();
				//msfz_load_params.LpuSection_id = ls_field.getValue();
				msf_load_params.MedPersonal_id = mp_field.getValue();
				break;
			case (mp_field.getValue() > 0 && mpz_field.getValue() > 0):
				msf_load_params.MedPersonal_id = mp_field.getValue();
				msfz_load_params.MedPersonal_id = mpz_field.getValue();
				break;
			case (mp_field.getValue() > 0):
				msf_load_params.MedPersonal_id = mp_field.getValue();
				break;
			case (mpz_field.getValue() > 0):
				msfz_load_params.MedPersonal_id = mpz_field.getValue();
				break;
		}
		if (swMedStaffFactGlobalStore.getCount() > 0 && msf_load_params.Lpu_id == getGlobalOptions().lpu_id) {
			// используем swMedStaffFactGlobalStore
			msf_load_params = false;
		} 
		if (swMedStaffFactGlobalStore.getCount() > 0 && msfz_load_params.Lpu_id == getGlobalOptions().lpu_id) {
			// используем swMedStaffFactGlobalStore
			msfz_load_params = false;
		} 

		log('loadMedPersonalCombo', msf_load_params, msfz_load_params);
		if (msf_load_params) {
			log('load_msf', msf_load_params,1);

			if (msf_load_params.Lpu_id) {
				msf_field.getStore().load({
					params: msf_load_params,
					callback: on_load_msf
				});
			} else {
				msf_field.getStore().removeAll();
				on_load_msf();
			}
		} else {
			msf_field.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			on_load_msf();
		}
		if (msfz_load_params) {
			log('load_msfz', msfz_load_params,2);

			if (msfz_load_params.Lpu_id) {
				msfz_field.getStore().load({
					params: msfz_load_params,
					callback: on_load_msfz
				});
			} else {
				msfz_field.getStore().removeAll();
				on_load_msfz();
			}
		} else {
			msfz_field.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
			on_load_msfz();
		}
	},
	filterMedPersonalCombo: function() {
		var win = this;
		var base_form = win.findById('EvnDirectionEditForm').getForm();

		var
			curDate = base_form.findField('EvnDirection_setDate').getValue(),
			index,
			MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue(),
			MedStaffFact_zid = base_form.findField('MedStaffFact_zid').getValue();

		base_form.findField('MedStaffFact_zid').getStore().clearFilter();
		base_form.findField('MedStaffFact_id').getStore().clearFilter();
		base_form.findField('MedStaffFact_zid').lastQuery = '';
		base_form.findField('MedStaffFact_id').lastQuery = '';

		if (!Ext.isEmpty(curDate)) {
			base_form.findField('MedStaffFact_id').getStore().filterBy(function (rec) {
				return ( Date.parseDate(rec.get('WorkData_begDate'), 'd.m.Y') <= curDate && (Ext.isEmpty(rec.get('WorkData_endDate')) || (Date.parseDate(rec.get('WorkData_endDate'), 'd.m.Y') >= curDate )) );
			});

			base_form.findField('MedStaffFact_zid').getStore().filterBy(function (rec) {
				return ( Date.parseDate(rec.get('WorkData_begDate'), 'd.m.Y') <= curDate
					&& (Ext.isEmpty(rec.get('WorkData_endDate'))
						|| (Date.parseDate(rec.get('WorkData_endDate'), 'd.m.Y') >= curDate )) );
			});

			base_form.findField('MedStaffFact_id').setBaseFilter(function (rec) {
				return ( Date.parseDate(rec.get('WorkData_begDate'), 'd.m.Y') <= curDate && (Ext.isEmpty(rec.get('WorkData_endDate')) || (Date.parseDate(rec.get('WorkData_endDate'), 'd.m.Y') >= curDate )) );
			});

			base_form.findField('MedStaffFact_zid').setBaseFilter(function (rec) {
				return ( Date.parseDate(rec.get('WorkData_begDate'), 'd.m.Y') <= curDate
					&& (Ext.isEmpty(rec.get('WorkData_endDate'))
						|| (Date.parseDate(rec.get('WorkData_endDate'), 'd.m.Y') >= curDate )) );
			});
		}

		if ( !Ext.isEmpty(MedStaffFact_id) ) {
			index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});

			if ( index == -1 ) {
				base_form.findField('MedStaffFact_id').clearValue();
				base_form.findField('MedPersonal_Code').setValue('');
			}
		}

		if ( !Ext.isEmpty(MedStaffFact_zid) ) {
			index = base_form.findField('MedStaffFact_zid').getStore().findBy(function (rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_zid);
			});

			if ( index == -1 ) {
				base_form.findField('MedStaffFact_zid').clearValue();
			}
		}
	},
	id: 'EvnDirectionEditWindow',
	initComponent: function() {
		var win = this;

		this.FileUploadPanelKZ = new sw.Promed.FileUploadPanelKZ({
			id: this.id + '_FileUploadPanelKZ',
			win: this,
			add_empty_combo: true,
			buttonAlign: 'left',
			maxHeight: 150,
			//buttonLeftMargin: 0,
			labelWidth: 65,
			commentTextfieldWidth: 210,
			folder: 'evnmedia/',
			style: 'background: transparent',
			dataUrl: '/?c=EvnMediaFiles&m=loadEvnMediaFilesListGrid',
			saveUrl: '/?c=EvnMediaFiles&m=uploadFile',
			saveChangesUrl: '/?c=EvnMediaFiles&m=saveChanges',
			deleteUrl: '/?c=EvnMediaFiles&m=deleteFile'
		});

		this.FilePanel = new Ext.Panel({
			title: langs('Файлы'),
			id: 'EUFREF_FileTab',
			border: false,
			collapsible: true,
			autoHeight: true,
			items: [this.FileUploadPanelKZ],
			listeners: {
				'expand': function (panel) {
					//Приходится делать такую ерунду, чтобы cодержимое адекватно перерисовывалось
					//console.log(panel);
					//this.FileUploadPanel.setWidth(adjWidth);
					win.FilePanel.doLayout();
				}.createDelegate(this)
			}
		});

		win.EvnDirectionDetail = new Ext.form.FieldSet({
			id: win.id + 'EvnDirectionDetail',
			xtype: 'fieldset',
			autoHeight: true,
			title: 'Дополнительные сведения о пациенте',
			style: 'padding: 2; padding-left: 5px',
			items: [
				{
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента ВИЧ'),
					comboSubject: 'HIVContingentTypeFRMIS',
					hiddenName: 'HIVContingentTypeFRMIS_id',
					//allowBlank: false,
					editable: true,
					ctxSerach: true,
					disabled: true,
					loadParams: { params: { where: ' where HIVContingentTypeFRMIS_Code != 100' } },
					width: 445
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Код контингента COVID'),
					comboSubject: 'CovidContingentType',
					hiddenName: 'CovidContingentType_id',
					//allowBlank: false,
					editable: true,
					ctxSerach: true,
					disabled: true,
					width: 445
				}, {
					xtype: 'swcommonsprcombo',
					hiddenName: 'HormonalPhaseType_id',
					comboSubject: 'HormonalPhaseType',
					fieldLabel: langs('Фаза цикла'),
					disabled: true,
					width: 445
				}, {
					xtype: 'swcommonsprcombo',
					fieldLabel: langs('Раса'),
					comboSubject: 'RaceType',
					hiddenName: 'RaceType_id',
					width: 150,
					allowBlan: false
				},
				{
					id: this.id + 'PersonHeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Рост (см): ',
							name: 'PersonHeight_Height_label',
							layuot: 'anchor',
							width: 200,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
						}, {
							xtype: 'textfield',
							name: 'PersonHeight_Height',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma,arial,helvetica,sans-serif;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonHeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}
					]
				}, {
					id: this.id + 'PersonWeight_FS',
					xtype: 'fieldset',
					layout: 'column',
					border: false,
					autoHeight: true,
					labelWidth: 130,
					style: 'margin: 2px 0 0 0; padding: 0;',
					items: [
						{
							xtype: 'panel',
							html: 'Масса: ',
							layuot: 'anchor',
							width: 200,
							style: 'margin-right: 5px;',
							bodyStyle: 'text-align: right; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							xtype: 'textfield',
							name: 'PersonWeight_WeightText',
							disabled: true
						}, {
							xtype: 'panel',
							html: ' на дату: ',
							layuot: 'anchor',
							bodyStyle: 'padding: 1px 5px 0 5px; border: 0px; font: normal 12px tahoma, arial, helvetica, sans-serif;'
						}, {
							fieldLabel : lang['okonchanie'],
							name: 'PersonWeight_setDT',
							xtype: 'swdatefield',
							disabled: true,
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ]
						}
					]
				}
			]
		});

		this.formPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'EvnDirectionEditForm',
			labelAlign: 'right',
			labelWidth: 200,
			items: [{
				name: 'EvnDirection_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'bookingDateReserveId',
				xtype: 'hidden'
			},{
				name: 'pmUser_insID',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'EvnDirection_IsReceive',
				xtype: 'hidden'
			},{
				name: 'DirectType_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'DirectClass_id',
				value: 0,
				xtype: 'hidden'
			},{
				name: 'EvnDirection_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'DopDispInfoConsent_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuUnitType_SysNick',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'Post_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_zid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'TimetableMedService_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Resource_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'TimetableResource_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PrescriptionType_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescr_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'TimetableGraf_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'TimetableStac_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnUsluga_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnQueue_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'QueueFailCause_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Person_Surname',
				xtype: 'hidden'
			}, {
				name: 'Person_Firname',
				xtype: 'hidden'
			}, {
				name: 'Person_Secname',
				xtype: 'hidden'
			},{
				name: 'Person_Birthday',
				xtype: 'hidden'
			}, {
				name: 'ARMType_id',
				xtype: 'hidden'
			},{
				name: 'ToothNums',
				value: '',
				xtype: 'hidden'
			}, {
				allowDecimals: false,
				allowNegative: false,
				disabled: true,
				fieldLabel: langs('Номер'),
				name: 'EvnDirection_Num',
				tabIndex: TABINDEX_EDIREF + 1,
				width: 150,
				autoCreate: {tag: "input", type: "text", maxLength: "6",  autocomplete: "off"},
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				fieldLabel: langs('Дата'),
				format: 'd.m.Y',
				name: 'EvnDirection_setDate',
				id: 'EDEW_EvnDirection_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_EDIREF + 2,
				width: 100,
				xtype: 'swdatefield',
				listeners: {
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanelid', 'EDirEF_PersonInformationFrame', field, newValue, oldValue);
						var date = Ext.util.Format.date(newValue, 'd.m.Y'),
							base_form = win.findById('EvnDirectionEditForm').getForm();

						win.getEvnDirectionNumber();

						win.filterMedPersonalCombo();
						win.filterLpuSectionProfileCombo({
							EvnDirection_setDate: newValue
						});

						if (getRegionNick() == 'vologda') {
							win.filterDiagByLpuSectionProfileVolumes();
						}

						if (getRegionNick().inlist([ 'krym', 'penza' ])) {
							base_form.findField('EvnDirection_desDT').setMinValue(newValue);
						}

						win.refreshFieldsVisibility(['Lpu_did','Lpu_sid']);
					}
				}
			}, {
				allowBlank: true,
				fieldLabel: getRegionNick() == 'kz' ? 'Источник финансирования' : 'Вид оплаты',
				tabIndex: TABINDEX_EDIREF + 3,
				width: 450,
				useCommonFilter: true,
				xtype: 'swpaytypecombo'
			}, {
				fieldLabel: 'Тип оплаты',
				width: 150,
				disabled: true,
				comboSubject: 'PayTypeKAZ',
				prefix: 'r101_',
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'Вид скрининга',
				width: 450,
				comboSubject: 'ScreenType',
				prefix: 'r101_',
				xtype: 'swcommonsprcombo',
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.getFinanceSource();
					}
				}
			}, {
				fieldLabel: 'Повод обращения',
				width: 450,
				comboSubject: 'TreatmentClass',
				xtype: 'swcommonsprcombo',
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.refreshFieldsVisibility(['ScreenType_id']);
						win.getFinanceSource();
					}
				}
			}, {
				xtype: 'swconsultingformcombo',
				width: 150,
				hiddenName: 'ConsultingForm_id',
				name: 'ConsultingForm_id',
				allowBlank: true
			}, {
				allowBlank: false,
				hiddenName: 'DirType_id',
				tabIndex: TABINDEX_EDIREF + 3,
				width: 450,
				loadParams: {params: {where: ' where DirType_id not in (18, 19)'}},
				xtype: 'swdirtypebasejournalcombo',
				listeners:
					{
						change: function(combo,nv,ov)
						{
							var base_form = win.findById('EvnDirectionEditForm').getForm(),
								lpu_combo = base_form.findField('Lpu_did'),
								lpu_sid_combo = base_form.findField('Lpu_sid'),
								ms_combo = base_form.findField('MedService_id'),
								rcc_combo = base_form.findField('RemoteConsultCause_id'),
								descr_field = base_form.findField('EvnDirection_Descr'),
								cito_field = base_form.findField('EvnDirection_IsCito'),
								lsp_combo = base_form.findField('LpuSectionProfile_id'),
								studytarget_combo = base_form.findField('StudyTarget_id'),
								uslugacomplex_combo = base_form.findField('UslugaComplex_did'),
								MedicalCareFormType = base_form.findField('MedicalCareFormType_id'),
								consultationForm_combo = base_form.findField('ConsultationForm_id');

							if (getRegionNick() == 'astra' && nv == 17) {
								uslugacomplex_combo.setAllowedUslugaComplexAttributeList([ 'telemed' ]);
							}
							else {
								uslugacomplex_combo.setAllowedUslugaComplexAttributeList();
							}

							if (getRegionNick() == 'penza' && nv == 5)
							{
								MedicalCareFormType.setValue(2);
								MedicalCareFormType.setDisabled(true);
							} else if (getRegionNick() == 'penza')
							{
								MedicalCareFormType.setDisabled(false);
							}

							// Если госпитализация экстренная - узкое место
							if (lpu_combo.getValue() != getGlobalOptions().lpu_id) {
								//добавил условие в рамках задачи http://redmine.swan.perm.ru/issues/23877
								base_form.findField('MedStaffFact_zid').setAllowBlank((nv==5));
							}
							ms_combo.getStore().removeAll();
							if (17 == nv) {
								// направление в ЦУК
								ms_combo.setContainerVisible(true);
								rcc_combo.setContainerVisible(true);
								cito_field.setContainerVisible(true);
								consultationForm_combo.setContainerVisible(true);
								lpu_sid_combo.setContainerVisible(false);
								lpu_sid_combo.setAllowBlank(true);
								win.syncShadow();
								descr_field.setFieldLabel(langs('Вопросы:'));
								ms_combo.setAllowBlank(false);
								rcc_combo.setAllowBlank(false);
								cito_field.setAllowBlank(false);
								if(getRegionNick() != 'kz') {
									consultationForm_combo.setAllowBlank(false);
								}
								//нельзя изменить службу, если она была выбрана до открытия этой формы или форма открыта для просмотра
								ms_combo.setDisabled(win.isWasChosenRemoteConsultCenter || win.action.inlist(['view', 'editpaytype']));
								rcc_combo.setDisabled(win.action.inlist(['view', 'editpaytype']));
								cito_field.setDisabled(win.action.inlist(['view', 'editpaytype']));
								consultationForm_combo.setDisabled(win.action.inlist(['view', 'editpaytype']));
								ms_combo.getStore().baseParams = {
									isDirection: 1,
									setDate: base_form.findField('EvnDirection_setDate').getRawValue() || getGlobalOptions().date,
									Lpu_id: lpu_combo.getValue(),//must be == getGlobalOptions().lpu_id
									MedServiceType_SysNick: 'remoteconsultcenter'
								};
								if (!win.isWasChosenRemoteConsultCenter && win.action == 'add') {
									ms_combo.setValue(null);
									lsp_combo.setValue(null);
								} else {
									ms_combo.getStore().baseParams.MedService_id = ms_combo.getValue();
								}
								ms_combo.getStore().load({callback: function(){
										var old_value = ms_combo.getValue();
										if (ms_combo.getStore().getById(old_value)) {
											ms_combo.setValue(old_value);
										} else {
											ms_combo.setValue(null);
										}
										ms_combo.fireEvent('change', ms_combo, ms_combo.getValue(), old_value);
									}});
							} else {
								ms_combo.setContainerVisible(false);
								rcc_combo.setContainerVisible(false);
								cito_field.setContainerVisible(false);
								consultationForm_combo.setContainerVisible(false);
								win.syncShadow();
								descr_field.setFieldLabel(langs('Обоснование:'));
								ms_combo.setAllowBlank(true);
								rcc_combo.setAllowBlank(true);
								cito_field.setAllowBlank(true);
								consultationForm_combo.setAllowBlank(true);
								ms_combo.setDisabled(win.action.inlist(['view', 'editpaytype']));
								rcc_combo.setDisabled(true);
								cito_field.setDisabled(win.action.inlist(['view', 'editpaytype']));
								consultationForm_combo.setDisabled(true);
								win.filterLpuSectionProfileCombo({});
							}

							studytarget_combo.setContainerVisible(nv==10);
							studytarget_combo.setAllowBlank(nv!=10 || !uslugacomplex_combo.getFieldValue('UslugaComplex_AttributeList') || uslugacomplex_combo.getFieldValue('UslugaComplex_AttributeList').indexOf('func') < 0);
							if (win.action == 'add' && Ext.isEmpty(studytarget_combo.getValue()) && studytarget_combo.isVisible()) {
								studytarget_combo.setValue(2);
							}

							win.getEvnDirectionNumber();
							win.filterMedicalCareFormType();
							win.refreshFieldsVisibility([
								'Lpu_did', 'Org_oid', 'MedService_id', 'LpuUnitType_did', 'LpuSectionProfile_id', 'GetBed_id',
								'MedSpec_fid', 'UslugaCategory_did', 'UslugaComplex_did', 'MedicalCareFormType_id', 'EvnLinkAPP_StageRecovery'
							]);
							var EvnDirection_id = base_form.findField('EvnDirection_id').getValue();
							win.refreshFilesPanel(EvnDirection_id);
						}
					}
			}, {
				hiddenName: 'EvnLinkAPP_StageRecovery',
				fieldLabel: 'Этап восстановительного лечения',
				xtype: 'swbaselocalcombo',
				store: [
					[2, '2 этап'],
					[3, '3 этап'],
				],
				width: 150,
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.getFinanceSource();
					}
				}
			}, {
				fieldLabel: 'Цель госпитализации',
				comboSubject: 'PurposeHospital',
				width: 450,
				prefix: 'r101_',
				xtype: 'swcommonsprcombo',
				listeners: {
					'change': function(field, newValue, oldValue) {
						win.refreshFieldsVisibility(['ScreenType_id', 'UslugaComplex_did']);
						win.getFinanceSource();
					}
				}
			}, {
				comboSubject: 'MedicalCareFormType',
				hiddenName: 'MedicalCareFormType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						win.refreshFieldsVisibility(['LpuUnitType_did']);
					}
				},
				fieldLabel: 'Форма помощи',
				lastQuery: '',
				prefix: 'nsi_',
				xtype: 'swcommonsprcombo'
			}, {
				fieldLabel: 'Цель исследования',
				xtype: 'swcommonsprcombo',
				allowBlank: false,
				hiddenName: 'StudyTarget_id',
				value: 2,
				comboSubject: 'StudyTarget',
				width: 450
			},
				{
					ownerWindow: win,
					xtype: 'swduplicatedfieldpanel',
					fieldLbl: 'Номер зуба',
					fieldName: 'ToothNumEvnUsluga_ToothNum',
					id: win.id + '_' + 'ToothNumFieldsPanel',
					labelWidth: 170,
					viewMode: true
					//hidden: true
				},
				{
					disabled: true,
					fieldLabel: langs('МО направления'),
					hiddenName: 'Lpu_did',
					ctxSerach: true,
					tabIndex: TABINDEX_EDIREF + 5,
					width: 450,
					xtype: 'swlpulocalcombo',
					listeners:
						{
							change: function(combo,newvalue){ //http://redmine.swan.perm.ru/issues/23877
								var base_form = win.findById('EvnDirectionEditForm').getForm();
								if(newvalue == getGlobalOptions().lpu_id && newvalue == base_form.findField('Lpu_sid').getValue()){
									base_form.findField('MedStaffFact_zid').setAllowBlank(true);
								} else {
									base_form.findField('MedStaffFact_zid').setAllowBlank(false);
								}
								win.filterLpuSectionCombo();
								if (getRegionNick() == 'vologda') {
									win.filterDiagByLpuSectionProfileVolumes();
								}
								win.refreshFieldsVisibility([
									'MedService_id','LpuUnitType_did','LpuSectionProfile_id',
									'MedSpec_fid','UslugaCategory_did','UslugaComplex_did',
									'ScreenType_id', 'PayTypeKAZ_id', 'TreatmentClass_id'
								]);
							}
						}
				},
				{
					fieldLabel: langs('Организация направления'),
					hiddenName: 'Org_oid',
					tabIndex: TABINDEX_EDIREF + 5,
					width: 450,
					onTrigger1Click: function() {
						var combo = this;
						var base_form = win.findById('EvnDirectionEditForm').getForm();
						var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');

						if ( combo.disabled ) {
							return false;
						}

						getWnd('swOrgSearchWindow').show({
							object: 'org',
							isNotForSystem: DirType_Code != 26 ? win.isNotForSystem : null,
							defaultOrgType: DirType_Code != 26 ? 11 : null,
							onClose: function() {
								combo.focus(true, 200);
							},
							onSelect: function(org_data) {
								if ( !Ext.isEmpty(org_data.Org_id) ) {
									combo.getStore().loadData([{
										Org_id: org_data.Org_id,
										Org_Name: org_data.Org_Name
									}]);
									combo.setValue(org_data.Org_id);
									combo.fireEvent('change', combo, org_data.Org_id);
									getWnd('swOrgSearchWindow').hide();
									combo.collapse();
									win.refreshFieldsVisibility([
										'MedService_id', 'LpuUnitType_did', 'LpuSectionProfile_id',
										'MedSpec_fid', 'UslugaCategory_did', 'UslugaComplex_did',
										'ScreenType_id', 'PayTypeKAZ_id', 'TreatmentClass_id'
									]);
								}
							}
						});
					},
					listeners: {
						'change': function(combo, newValue, oldValue) {
							win.refreshFieldsVisibility([
								'MedService_id', 'LpuUnitType_did', 'LpuSectionProfile_id',
								'MedSpec_fid', 'UslugaCategory_did', 'UslugaComplex_did',
								'ScreenType_id', 'PayTypeKAZ_id', 'TreatmentClass_id'
							]);
						}
					},
					xtype: 'sworgcombo'
				}, {
					fieldLabel: 'Условия оказания медицинской помощи',
					hiddenName: 'LpuUnitType_did',
					tabIndex: TABINDEX_EDIREF + 5.5,
					width: 450,
					xtype: 'swlpuunittypecombo',
					listeners: {
						change: function(combo, newValue, oldValue) {
							win.refreshFieldsVisibility(['LpuSectionProfile_id','MedSpec_fid']);
							win.getFinanceSource();
						}
					}
				}, {
					disabled: true,
					allowBlank: true,
					hiddenName: 'MedService_id',
					tabIndex: TABINDEX_EDIREF + 6,
					width: 450,
					xtype: 'swmedservicecombo',
					value: null,
					listeners:{
						change: function(combo, newValue) {
							win.filterLpuSectionProfileCombo({MedService_id: newValue});
						}
					}
				}, {
					disabled: true,
					allowBlank: false,
					hiddenName: 'LpuSectionProfile_id',
					id: 'EDEW_LpuSectionProfile_id',
					tabIndex: TABINDEX_EDIREF + 6,
					width: 450,
					xtype: 'swlpusectionprofilecombo',
					listeners:{
						change: function(combo, newValue, oldValue){
							win.filterLpuSectionCombo();
							var base_form = win.findById('EvnDirectionEditForm').getForm(),
								ms_combo = base_form.findField('MedService_id'),
								old_value = ms_combo.getValue();
							if (base_form.findField('DirType_id').getValue() == 17 && !win.isWasChosenRemoteConsultCenter && win.action == 'add') {
								ms_combo.getStore().baseParams = {
									LpuSectionProfile_id: newValue,
									isDirection: 1,
									setDate: base_form.findField('EvnDirection_setDate').getRawValue() || getGlobalOptions().date,
									Lpu_id: base_form.findField('Lpu_did').getValue(),//must be == getGlobalOptions().lpu_id
									MedServiceType_SysNick: 'remoteconsultcenter'
								};
								ms_combo.clearValue();
								ms_combo.getStore().clearFilter();
								ms_combo.lastQuery = '';
								ms_combo.getStore().load({callback: function(){
										if (ms_combo.getStore().getById(old_value)) {
											ms_combo.setValue(old_value);
										} else {
											ms_combo.setValue(null);
										}
									}});
							}
							if (getRegionNick() == 'vologda') {
								win.filterDiagByLpuSectionProfileVolumes();
							}
							win.loadBedList();

							if (getRegionNick() == 'kz' && !base_form.findField('UslugaComplex_did').getValue()) {

								var index = win.profileUslugaLinkStore.findBy(function(rec) {
									return rec.get('LpuSectionProfile_id') == newValue;
								});
								if (index >= 0) {
									var rec = win.profileUslugaLinkStore.getAt(index);
									var pay_type_combo = base_form.findField('PayTypeKAZ_id');
									var usluga_complex = base_form.findField('UslugaComplex_did');
									var uslugacomplex_attributelist = rec.get('UslugaComplex_AttributeList');
									usluga_complex.getStore().load({
										params: {UslugaComplex_id: rec.get('UslugaComplex_id')},
										callback: function() {
											usluga_complex.setValue(rec.get('UslugaComplex_id'));
											usluga_complex.fireEvent('change', usluga_complex, rec.get('UslugaComplex_id'));
										}
									});
									if (uslugacomplex_attributelist && !!uslugacomplex_attributelist.split(',').find(function(el){return el == 'Kpn'})) {
										pay_type_combo.setValue(1);
									}
									else if (uslugacomplex_attributelist && uslugacomplex_attributelist.indexOf('IsNotKpn') >= 0) {
										pay_type_combo.setValue(2);
									}
									else {
										pay_type_combo.setValue('');
									}
									win.getFinanceSource();
								} else {
									win.getFinanceSource();
								}
							} else {
								win.getFinanceSource();
							}
						}
					}
				}, {
					hiddenName: 'GetBed_id',
					fieldLabel: 'Профиль койки',
					xtype: 'swbaselocalcombo',
					valueField: 'GetBed_id',
					codeField: 'BedProfile',
					displayField: 'BedProfileRuFull',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: '/?c=EvnPS&m=getBedList',
						fields: [
							{name: 'GetBed_id', type: 'int'},
							{name: 'BedProfile', type: 'int'},
							{name: 'BedProfileRu', type: 'string'},
							{name: 'TypeSrcFinRu', type: 'string'},
							{name: 'StacTypeRu', type: 'string'},
							{name: 'BedProfileRuFull', type: 'string'}
						],
						key: 'GetBed_id',
						sortInfo: {
							field: 'BedProfile'
						}
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'<font color="red">{BedProfile}</font>&nbsp;{BedProfileRuFull}',
						'</div></tpl>'
					),
					listeners: {
						change: function(combo, newValue, oldValue) {
							win.getFinanceSource();
						}
					},
					width: 450,
					listWidth: 800
				}, {
					comboSubject: 'TreatmentType',
					prefix: 'r58_',
					hiddenName: 'TreatmentType_id',
					fieldLabel: 'Тип предстоящего лечения',
					xtype: 'swcommonsprcombo'
				}, {
					editable: true,
					xtype: 'swmedspecfedcombo',
					hiddenName: 'MedSpec_fid',
					fieldLabel: 'Специальность врача',
					width: 450
				}, {
					xtype: 'swuslugacategorycombo',
					hiddenName: 'UslugaCategory_did',
					fieldLabel: 'Категория услуги',
					width: 450,
					listeners: {
						'select': function(combo, record, index) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();

							base_form.findField('UslugaComplex_did').clearValue();
							base_form.findField('UslugaComplex_did').getStore().removeAll();

							if (!record) {
								base_form.findField('UslugaComplex_did').setUslugaCategoryList();
							} else {
								base_form.findField('UslugaComplex_did').setUslugaCategoryList([record.get('UslugaCategory_SysNick')]);
							}
						}
					}
				}, {
					xtype: 'swuslugacomplexnewcombo',
					hiddenName: 'UslugaComplex_did',
					fieldLabel: 'Услуга',
					width: 450,
					listeners: {
						'change': function(field, newValue, oldValue) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();

							base_form.findField('FSIDI_id').checkVisibilityAndGost(newValue);

							if (getRegionNick() == 'kz') {
								var pay_type_combo = base_form.findField('PayTypeKAZ_id');
								var uslugacomplex_attributelist = field.getFieldValue('UslugaComplex_AttributeList');

								if (uslugacomplex_attributelist && !!uslugacomplex_attributelist.split(',').find(function(el){return el == 'Kpn'})) {
									pay_type_combo.setValue(1);
								}
								else if (uslugacomplex_attributelist && uslugacomplex_attributelist.indexOf('IsNotKpn') >= 0) {
									pay_type_combo.setValue(2);
								}
								else {
									pay_type_combo.setValue('');
								}
								win.getFinanceSource();
							}
						}
					}
				}, {
					xtype: 'swfsidicombo',
					width: 434,
					listWidth: 434,
					hideOnInit: true,
					hiddenName: 'FSIDI_id',
				}, {
					hiddenName: 'LpuSection_did',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							if (combo.isVisible()) {
								win.filterMedicalCareFormType();
							}
							win.refreshFieldsVisibility(['LpuUnitType_did']);
						},
						expand: function () {
							var base_form = win.findById('EvnDirectionEditForm').getForm();
							var CurEvnDirectionDate = base_form.findField('EDEW_EvnDirection_setDate').getValue();
							if(getRegionNick() == 'perm' && base_form.findField('DirType_id').getValue() == 5) {
								if(CurEvnDirectionDate != '') {
									base_form.findField('LpuSection_did').getStore().filterBy(function (val) {
										if(val.get('LpuSection_disDate') != '')
										{
											return val.get('LpuSection_disDate') > CurEvnDirectionDate;
										}
										return true;
									});
								}
							}
						}
					},
					width: 450,
					tabIndex: TABINDEX_EDIREF + 6,
					xtype: 'swlpusectionglobalcombo'
				}, {
					disabled: true,
					allowBlank: true,
					fieldLabel: langs('Цель консультации'),
					hiddenName: 'RemoteConsultCause_id',
					tabIndex: TABINDEX_EDIREF + 6,
					width: 450,
					xtype: 'swcommonsprcombo',
					comboSubject: 'RemoteConsultCause',
					autoLoad: false,
					value: null
				}, {
					fieldLabel: 'Форма оказания консультации',
					xtype: 'swcommonsprcombo',
					comboSubject: 'ConsultationForm',
					hiddenName: 'ConsultationForm_id',
					autoLoad: true,
					listeners: {
						change: function(combo,newValue) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();
							base_form.findField('EvnDirection_IsCito').setValue(newValue == 3 ? 2 : 1);
						}
					}
				}, {
					disabled: true,
					fieldLabel: 'Cito',
					hiddenName: 'EvnDirection_IsCito',
					tabIndex: TABINDEX_EDIREF + 6,
					width: 100,
					xtype: 'swcommonsprcombo',
					comboSubject: 'YesNo',
					autoLoad: false,
					value: 1,
					listeners: {
						change: function(combo,newValue) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();
							base_form.findField('ConsultationForm_id').setValue(newValue == 2 ? 3 : null);
						}
					}
				},{
					layout: 'column',
					items: [
						{
							layout:'form',
							items:[
								{
									fieldLabel: langs('Время записи'),
									//disabled: true,
									name: 'EvnDirection_noSetDateTime',
									value: langs('неизвестно (очередь)'),
									onTriggerClick: function() {
										var field = Ext.getCmp('EvnDirectionEditForm').getForm().findField('EvnDirection_noSetDateTime');
										if (!field.disabled)
											win.openRecordWindow();
									}.createDelegate(this),
									plugins: [ new Ext.ux.InputTextMask('99.99.9999 99:99', false) ],
									readOnly: true,
									stripCharsRe: new RegExp('__.__.____ __:__'),
									tabIndex: TABINDEX_EDIREF + 7,
									triggerClass: 'x-form-clock-trigger',
									validationEvent: 'blur',
									width: 150,
									xtype: 'trigger'
								}
							]
						},{
							layout:'form',
							items:[
								{
									fieldLabel: langs('Желаемая дата'),
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
									tabIndex: TABINDEX_EDIREF + 2,
									name: 'EvnDirection_desDT',
									width: 100,
									xtype: 'swdatefield',
									listeners: {
										'change': function(combo,newValue,oldValue) {
											if (getRegionNick() == 'kz') win.getFinanceSource();
										}
									}
								}
							]
						}
					]
				}, {
					fieldLabel: langs('Время записи'),
					disabled: true,
					name: 'EvnDirection_setDateTime',
					onTriggerClick: function() {
						var field = Ext.getCmp('EvnDirectionEditForm').getForm().findField('EvnDirection_setDateTime');
						if (!field.disabled)
							getWnd('swTagSelectWindow').show({
								callback: Ext.emptyFn,
								onHide: Ext.emptyFn
							});
					}.createDelegate(this),
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 99:99', false) ],
					readOnly: true,
					stripCharsRe: new RegExp('__.__.____ __:__'),
					tabIndex: TABINDEX_EDIREF + 7,
					triggerClass: 'x-form-clock-trigger',
					validationEvent: 'blur',
					width: 150,
					xtype: 'trigger'
				}, {
					checkAccessRights: true,
					allowBlank: false,
					name: 'Diag_id',
					tabIndex: TABINDEX_EDIREF + 11,
					width: 450,
					listeners: {
						'select': function (combo, newValue) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();
							var diag_code = combo.getFieldValue('Diag_Code');

							if(
								diag_code >= 'C00' && diag_code <= 'C97'
								|| diag_code >= 'D00' && diag_code <= 'D09'
							) {
								base_form.findField('TreatmentType_id').clearValue();
							} else {
								base_form.findField('TreatmentType_id').setValue(5);
							}
							win.getFinanceSource();
						},
						'change': function (combo, newValue, oldValue) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();
							var diag_code = combo.getFieldValue('Diag_Code');

							if(
								diag_code >= 'C00' && diag_code <= 'C97'
								|| diag_code >= 'D00' && diag_code <= 'D09'
							) {
								base_form.findField('TreatmentType_id').clearValue();
							} else {
								base_form.findField('TreatmentType_id').setValue(5);
							}


							win.getFinanceSource();
						}
					},
					xtype: 'swdiagcombo'
				}, /*{
					fieldLabel: 'Обоснование госпитализации',
					xtype: 'swcommonsprcombo',
					comboSubject: 'ReasonHospital',
					width: 450,
					prefix: 'r101_'
				},*/ {
					hiddenName: 'Diag_cid',
					fieldLabel: 'Уточняющий диагноз',
					width: 450,
					xtype: 'swdiagcombo',
					listeners: {
						'change': function(field, newValue, oldValue) {
							win.getFinanceSource();
						}
					}
				}, {
					checkAccessRights: true,
					allowBlank: true,
					hiddenName: 'EvnXml_id',
					tabIndex: TABINDEX_EDIREF + 11,
					width: 450,
					valueField: 'EvnXml_id',
					displayField: 'EvnXml_Name',
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'EvnXml_id', type: 'int' },
							{ name: 'EvnXml_Name', type: 'string' }
						],
						key: 'EvnXml_id',
						sortInfo: {
							field: 'EvnXml_Name'
						},
						url: '/?c=EvnXml&m=loadEvnXmlCombo'
					}),
					xtype: 'swbaselocalcombo',
					fieldLabel: 'Предоперационный эпикриз' // выбор предоперационного эпикриза из прикрепленных к случаю лечения эпикризов
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							xtype: 'swyesnocombo',
							hiddenName: 'EvnDirectionOper_IsAgree',
							value: 2,
							tabIndex: TABINDEX_EDIREF + 11,
							allowBlank: true,
							width: 100,
							fieldLabel: langs('Согласие пациента')
						}]
					}, {
						layout: 'form',
						bodyStyle: 'margin-left:5px;',
						items: [{
							xtype: 'button',
							id: 'EDEW_AnestButton',
							text: langs('на анестезиологическое обеспечение'),
							handler: function () {
								var base_form = win.findById('EvnDirectionEditForm').getForm();

								var paramPerson = base_form.findField('Person_id').getValue();
								var paramLpu = getGlobalOptions().lpu_id;

								if ( getRegionNick() == 'kz' ) {
									printBirt({
										'Report_FileName': 'Person_soglasie_stac_anst.rptdesign',
										'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu,
										'Report_Format': 'pdf'
									});
								} else {
									var reportParams = '&paramPerson=' + paramPerson;
									var dir_type_id = base_form.findField('DirType_id').getValue();
									if( dir_type_id == 20){
										//16. В операционный блок
										var evnId = base_form.findField('EvnDirection_pid').getValue();
										if(evnId) reportParams = reportParams + '&paramEvnSection=' + evnId;
									}
									printBirt({
										'Report_FileName': 'Person_soglasie_stac_anst.rptdesign',
										'Report_Params': reportParams,
										'Report_Format': 'pdf'
									});
								}
							}
						}]
					}, {
						layout: 'form',
						bodyStyle: 'margin-left:5px;',
						items: [{
							xtype: 'button',
							id: 'EDEW_OperButton',
							text: langs('на операцию'),
							handler: function () {
								var base_form = win.findById('EvnDirectionEditForm').getForm();

								var paramPerson = base_form.findField('Person_id').getValue();
								var paramLpu = getGlobalOptions().lpu_id;
								var evnId = base_form.findField('EvnDirection_pid').getValue();

								if ( getRegionNick() == 'kz' ) {
									printBirt({
										'Report_FileName': 'PersonInfoSoglasie_OperStac.rptdesign',
										'Report_Params': '&paramPerson=' + paramPerson + '&paramLpu=' + paramLpu,
										'Report_Format': 'pdf'
									});
								} else {
									var reportFileName = 'PersonInfoSoglasie_OperStac.rptdesign';
									var reportParams = '&paramPerson=' + paramPerson;
									var dir_type_id = base_form.findField('DirType_id').getValue();
									if( dir_type_id == 20){
										//16. В операционный блок
										var evnId = base_form.findField('EvnDirection_pid').getValue();
										var uslugaComplex_id = base_form.findField('UslugaComplex_did').getValue();
										if(evnId) reportParams = reportParams + '&paramEvnSection=' + evnId;
										if(uslugaComplex_id) reportParams = reportParams + '&paramUslugaComplex=' + uslugaComplex_id;
										reportFileName = 'PersonInfoSoglasie_OperWard.rptdesign';
									}
									printBirt({
										'Report_FileName': reportFileName,
										'Report_Params': reportParams,
										'Report_Format': 'pdf'
									});
								}
							}
						}]
					}]
				},
				{
					layout: 'form',
					style: "margin-left:156px",
					items: [{
						xtype: 'checkbox',
						hideLabel: true,
						name: 'EvnDirection_IsNeedOper',
						hiddenName: 'EvnDirection_IsNeedOper',
						hidden: false,
						boxLabel: langs('Необходимость операционного вмешательства')
					}]
				}, {
					fieldLabel: langs('Обоснование:'),
					height: 70,
					name: 'EvnDirection_Descr',
					tabIndex: TABINDEX_EDIREF + 12,
					width: 450,
					xtype: 'textarea'
				}, {
					fieldLabel: langs('Направившая МО'),
					hiddenName: 'Lpu_sid',
					ctxSerach: true,
					tabIndex: TABINDEX_EDIREF + 13,
					width: 450,
					xtype: 'swlpulocalcombo',
					listeners:
						{
							change: function(combo, newValue) {
								var base_form = win.findById('EvnDirectionEditForm').getForm(),
									dir_type_combo = base_form.findField('DirType_id'),
									dir_type_id = parseInt(dir_type_combo.getValue()),
									Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem') != 2;// 1 - не работает в системе, 2 - работает

								base_form.findField('MedPersonal_id').setValue(null);
								base_form.findField('LpuSection_id').setValue(null);
								base_form.findField('MedStaffFact_id').setValue(null);
								base_form.findField('MedPersonal_Code').setValue(null);
								base_form.findField('MedPersonal_zid').setValue(null);
								base_form.findField('MedStaffFact_zid').setValue(null);

								if (getRegionNick() === 'ekb')
								{
									if(base_form.findField('EvnDirection_IsReceive').getValue()==2 && newValue)
									{
										base_form.findField('MedPersonal_Code').setAllowBlank( ! dir_type_id.inlist([ '1','5','10']) );
										base_form.findField('MedStaffFact_id').setAllowBlank( true );
									} else
									{
										base_form.findField('MedPersonal_Code').setAllowBlank( ! dir_type_id.inlist([ '1','5','10']) );
										base_form.findField('MedStaffFact_id').setAllowBlank( ! dir_type_id.inlist([ '1','5']) );
									}
								} else if (getRegionNick() === 'buryatiya')
								{
									base_form.findField('MedStaffFact_id').setAllowBlank( true );
								}

								if (Lpu_IsNotForSystem) {
									base_form.findField('MedStaffFact_id').setAllowBlank( true );
								}

								win.loadMedPersonalCombo();

								if (getRegionNick() == 'vologda') {
									win.filterDiagByLpuSectionProfileVolumes();
								}
							}
						}
				}, {
					allowBlank: (getRegionNick() == 'buryatiya'),
					fieldLabel: langs('Врач'),
					hiddenName: 'MedStaffFact_id',
					listWidth: 670,
					lastQuery: '',
					tabIndex: TABINDEX_EDIREF + 13,
					width: 450,
					xtype: 'swmedstafffactglobalcombo',
					listeners: {
						'collapse' : function(combo) {
							if(getRegionNick()=='ekb') {
								var base_form = win.findById('EvnDirectionEditForm').getForm();
								var DloCode = base_form.findField('MedPersonal_Code');
								if(combo.getValue()) {
									DloCode.setValue( combo.getFieldValue('MedPersonal_DloCode') );
								} else DloCode.setValue('');
							}
						}
					}
				}, {
					allowBlank: true,
					fieldLabel: langs('Код врача'),
					hiddenName: 'MedPersonal_Code',
					name : 'MedPersonal_Code',
					xtype: 'textfield',
					width: 150,
					enableKeyEvents: true,
					listeners: {
						'keyup': function (inp, e) {
							var base_form = win.findById('EvnDirectionEditForm').getForm();
							var Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem')!=2;// 1 - не работает в системе, 2 - работает
							if(!Lpu_IsNotForSystem) {
								var DloCode = base_form.findField('MedPersonal_Code');
								var combo = base_form.findField('MedStaffFact_id');
								if(DloCode.getValue().length>0) {
									var doctor = {id:0, count:0};
									for(i=0; i<combo.getStore().getCount(); i++) {
										if(combo.getStore().getAt(i).data.MedPersonal_DloCode == DloCode.getValue()) {
											doctor.id = combo.getStore().getAt(i).data.MedStaffFact_id;
											doctor.count++;
										}
									}
									if(doctor.count==1) {
										base_form.findField('MedStaffFact_id').setValue( doctor.id );
									} else combo.clearValue();
								} else combo.clearValue();
							}
						}
					}
				}, {
					allowBlank: false,
					fieldLabel: langs('Зав. отделением'),
					hiddenName: 'MedStaffFact_zid',
					listWidth: 670,
					lastQuery: '',
					tabIndex: TABINDEX_EDIREF + 14,
					width: 450,
					xtype: 'swmedstafffactglobalcombo'
				},
				win.EvnDirectionDetail
				],
			keys: [{
				alt: true,
				fn: function(inp, e) {
					switch ( e.getKey() ) {
						case Ext.EventObject.C:
							if ( this.action != 'view' ) {
								this.doSave();
							}
							break;

						case Ext.EventObject.J:
							this.hide();
							break;
					}
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'EvnDirection_id' },
				{ name: 'EvnDirection_IsReceive' },
				{ name: 'Lpu_sid' },
				{ name: 'EvnDirection_pid' },
				{ name: 'ConsultingForm_id' },
				{ name: 'DopDispInfoConsent_id' },
				{ name: 'MedService_id' },
				{ name: 'RemoteConsultCause_id' },
				{ name: 'TimetableMedService_id' },
				{ name: 'Resource_id' },
				{ name: 'TimetableResource_id' },
				{ name: 'PrescriptionType_Code' },
				{ name: 'EvnPrescr_id' },
				{ name: 'TimetableGraf_id' },
				{ name: 'TimetableStac_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'EvnDirection_IsNeedOper' },
				{ name: 'Server_id' },
				{ name: 'Person_Surname' },
				{ name: 'Person_Firname' },
				{ name: 'Person_Secname' },
				{ name: 'Person_Birthday' },
				{ name: 'EvnDirection_Num' },
				{ name: 'EvnDirection_setDate' },
				{ name: 'EvnDirection_desDT' },
				{ name: 'PayType_id' },
				{ name: 'DirType_id' },
				{ name: 'MedicalCareFormType_id' },
				{ name: 'StudyTarget_id' },
				{ name: 'Lpu_did' },
				{ name: 'Org_oid'},
				{ name: 'LpuSectionProfile_id' },
				{ name: 'LpuSection_did' },
				{ name: 'LpuUnitType_did' },
				{ name: 'LpuUnitType_SysNick' },
				{ name: 'EvnDirection_setDateTime' },
				{ name: 'UslugaCategory_did' },
				{ name: 'UslugaComplex_did' },
				{ name: 'FSIDI_id' },
				{ name: 'MedSpec_fid' },
				{ name: 'Diag_id' },
				{ name: 'EvnDirection_Descr' },
				{ name: 'EvnDirection_IsCito' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'LpuSection_id' },
				{ name: 'Post_id' },
				{ name: 'TreatmentType_id' },
				{ name: 'MedPersonal_zid' },
				{ name: 'ARMType_id' },
				{ name: 'EvnXml_id' },
				{ name: 'EvnDirectionOper_IsAgree' },
				{ name: 'ToothNums' },
				{ name: 'GetBed_id' },
				{ name: 'PayTypeKAZ_id' },
				{ name: 'EvnLinkAPP_StageRecovery' },
				{ name: 'PurposeHospital_id' },
				{ name: 'Diag_cid' },
				{ name: 'ScreenType_id' },
				{ name: 'TreatmentClass_id' },
				{ name: 'ConsultationForm_id' },
				{ name: 'HIVContingentTypeFRMIS_id' },
				{ name: 'CovidContingentType_id' },
				{ name: 'HormonalPhaseType_id' },
				{ name: 'PersonHeight_Height' },
				{ name: 'PersonHeight_setDT' },
				{ name: 'PersonWeight_WeightText' },
				{ name: 'PersonWeight_setDT' },
				{ name: 'RaceType_id' },
				{ name: 'pmUser_insID' }
			]),
			url: '/?c=EvnDirection&m=saveEvnDirection'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					var setDate = win.findById('EDEW_EvnDirection_setDate').value,
						curDate = new Date();

					curDate.setHours(0,0,0,0);
					setDate = setDate.substr(3,2) +'/'+ setDate.substr(0,2) +'/'+ setDate.substr(6,4);
					setDate = new Date(setDate);

					if (curDate > setDate) {
						Ext.Msg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId) {
								if (buttonId == 'yes')
									win.doSave();
							},
							icon: Ext.Msg.WARNING,
							msg: "Проверьте дату выписки направления. При постановке в очередь в направлении должна быть указана текущая дата. Продолжить?",
							title: ERR_INVFIELDS_TIT
						});
					} else {
						win.doSave();
					}
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EDIREF + 15,
				text: BTN_FRMSAVE
			}, {
				handler: function() {
					this.printEvnDirection();
				}.createDelegate(this),
				iconCls: 'print16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[0].onShiftTabAction();
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EDIREF + 26,
				text: BTN_FRMPRINT
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_EDIREF + 17),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 2].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('EvnDirectionEditForm').getForm().findField('EvnDirection_setDate').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EDIREF + 18,
				text: BTN_FRMCANCEL
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort({
				id: 'EDirEF_PersonInformationFrame'
			})
				,win.formPanel
				,win.FilePanel
			]
		});
		sw.Promed.swEvnDirectionEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			if ( e.getKey() == Ext.EventObject.J ) {
				this.hide();
			}
			else if ( e.getKey() == Ext.EventObject.C ) {
				if ( 'view' != this.action ) {
					this.doSave();
				}
			}
		},
		key: [ Ext.EventObject.C, Ext.EventObject.J ],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		beforehide: function(win) {
			return win.closable;
		},
		hide: function(win) {
			win.onHide(win);
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	actionEdit:function(status,response){
		if(status&&response&&response.responseText){
		var response_obj = Ext.util.JSON.decode(response.responseText);
		}else{
			return false;
		}
		var base_form = this.findById('EvnDirectionEditForm').getForm();
		
		var EvnDirection_id = response_obj.EvnDirection_id;
		this.setTitle(WND_POL_EDIREDIT);
		base_form.findField('EvnDirection_id').setValue(EvnDirection_id);
		this.enableEdit(true);
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		if (EvnDirection_id) {
			this.loadRecord({
					EvnDirection_id: EvnDirection_id,
					action: 'edit'
				},
				loadMask
			);
		}
		loadMask.hide();
		//base_form.findField('EvnDirection_setDate').focus(true, 250);
		this.buttons[1].setVisible(true);
	},
	filterLpuSectionProfileCombo: function(options) {
		var win = this,
			base_form = win.findById('EvnDirectionEditForm').getForm(),
			dir_combo = base_form.findField('DirType_id'),
			lsp_combo = base_form.findField('LpuSectionProfile_id'),
			LpuSectionProfile_id = options.LpuSectionProfile_id || lsp_combo.getValue(),
			setDate = options.EvnDirection_setDate || base_form.findField('EvnDirection_setDate').getValue(),
			ms_combo = base_form.findField('MedService_id'),
			Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem') != 2,
			ms_rec = ms_combo.getStore().getById(options.MedService_id || ms_combo.getValue()),
			LpuSectionProfileIdList = null,
			index;

		var dirTypeCode = dir_combo.getFieldValue('DirType_Code');
			/*
				- На экстренную госпитализацию
				- В органы социальной защиты.
			Список доступных значений не ограничен
			*/
		if(!Ext.isEmpty(dirTypeCode) && dirTypeCode.inlist([2,4,3,12, 1,6]) && !Ext.isEmpty(win.LpuSectionLpuSectionProfileList)){
			/*
			-	На обследование;
			-	На восстановительное лечение;
			-	На консультацию;
			-	На поликлинический прием.

			-	На госпитализацию плановую;
			-	На осмотр с целью госпитализации;
			*/
			Lpu_IsNotForSystem = false;
			LpuSectionProfileIdList = win.LpuSectionLpuSectionProfileList.split(',');
		}else if(!Ext.isEmpty(dirTypeCode) && dirTypeCode.inlist([13]) && ms_rec){
			/*
			-	На удаленную консультацию. (13 - 17)
			*/
			Lpu_IsNotForSystem = false;
			if(ms_rec.get('LpuSectionProfile_id_List')) LpuSectionProfileIdList = ms_rec.get('LpuSectionProfile_id_List').split(',');
		}else if(!Ext.isEmpty(dirTypeCode) && dirTypeCode.inlist([9,10,11,25]) && ms_rec){
			/*
			–	На исследование;
			–	В консультационный кабинет;
			–	В процедурный кабинет;
			–	На проф. осмотр
			*/
			Lpu_IsNotForSystem = false;
			if(ms_rec.get('LpuSectionLpuSectionProfileList')) LpuSectionProfileIdList = ms_rec.get('LpuSectionLpuSectionProfileList').split(',');
		}

		/*
		if (17 == dir_combo.getValue() && ms_rec && ms_rec.get('LpuSectionProfile_id_List')) {
			LpuSectionProfileIdList = ms_rec.get('LpuSectionProfile_id_List').split(',');
		}
		else if ( !Ext.isEmpty(win.LpuSectionLpuSectionProfileList) ) {
			LpuSectionProfileIdList = win.LpuSectionLpuSectionProfileList.split(',');
		}
		*/

		if (win.action.inlist(['view', 'editpaytype']) && LpuSectionProfile_id) {
			LpuSectionProfileIdList = [LpuSectionProfile_id];
		}
		if (win.action.inlist(['view', 'editpaytype']) && setDate) {
			setDate = null;
		}
		if (Ext.isArray(LpuSectionProfileIdList) && 1 == LpuSectionProfileIdList.length && !LpuSectionProfile_id ) {
			LpuSectionProfile_id = LpuSectionProfileIdList[0];
		}
		// Фильтруем список профилей отделений
		lsp_combo.clearValue();
		lsp_combo.getStore().clearFilter();
		lsp_combo.lastQuery = '';

		var setDateInFormat = null;
		if (!Ext.isEmpty(setDate)) {
			setDateInFormat = new Date(setDate).toString('yyyy-MM-dd');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Получение профилей...'});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Common&m=loadLpuSectionProfileDopList',
			params: {
				LpuSection_id: base_form.findField('LpuSection_did').getValue(),
				onDate: setDateInFormat
			},
			success: function(response) {
				loadMask.hide();
				var list = Ext.util.JSON.decode(response.responseText);
				if (Ext.isArray(list) && list.length != 0) {
					var profileList = [];
					list.forEach(function(profile) {
						profileList.push(profile.LpuSectionProfile_id);
					});

					lsp_combo.setBaseFilter(function (rec) {
						if (Lpu_IsNotForSystem) {
							return true;
						}
						if (!rec.get('LpuSectionProfile_id').toString().inlist(profileList)) {
							return false;
						}
						if (!Ext.isEmpty(setDate)) {
							return (Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= setDate)
								&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= setDate);
						} else {
							return true;
						}
					});
				}
			},
			failure: function() {
				loadMask.hide();
			}
		});

		index = lsp_combo.getStore().findBy(function(rec) {
			return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
		});
		if ( index >= 0 ) {
			lsp_combo.setValue(LpuSectionProfile_id);
			lsp_combo.fireEvent('select', lsp_combo, lsp_combo.getStore().getAt(index));
		}
	},
	filterDiagByLpuSectionProfileVolumes: function() {
		var win = this;
		var base_form = win.findById('EvnDirectionEditForm').getForm();
		var diag_combo = base_form.findField('Diag_id');

		var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
		var Date = base_form.findField('EvnDirection_setDate').getValue();
		var Lpu_sid = base_form.findField('Lpu_sid').getValue();
		var Lpu_did = base_form.findField('Lpu_did').getValue();

		if (Ext.isEmpty(LpuSectionProfile_id) || Ext.isEmpty(Date) ||
			Ext.isEmpty(Lpu_sid) || Ext.isEmpty(Lpu_did) || Lpu_sid == Lpu_did ||
			(getRegionNick() == 'vologda' && Lpu_sid != Lpu_did)
		) {
			diag_combo.clearBaseFilter();
			return;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: 'Получение данных...'});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=TariffVolumes&m=getDiagListByLpuSectionProfile',
			params: {
				LpuSectionProfile_id: LpuSectionProfile_id,
				Date: Ext.util.Format.date(Date, 'Y-m-d')
			},
			success: function(response) {
				loadMask.hide();
				var list = Ext.util.JSON.decode(response.responseText);
				if (list.length == 0) {
					diag_combo.clearBaseFilter();
				} else {
					diag_combo.setBaseFilter(function(record) {
						var data = record.data || record.attributes || {};
						return data.Diag_id.toString().inlist(list);
					});
					if (!diag_combo.getStore().getById(diag_combo.getValue())) {
						diag_combo.setValue(null);
					}
				}
			},
			failure: function() {
				loadMask.hide();
			}
		});
	},
	checkEvnDirectionIsReceive: function() {
		var base_form = this.findById('EvnDirectionEditForm').getForm();
		var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');

		base_form.findField('Diag_id').setAllowBlank(DirType_Code == 11);
		if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
			base_form.findField('Diag_id').setAllowBlank(true);
		}

		base_form.findField('Lpu_sid').hideContainer();
		base_form.findField('Lpu_sid').setAllowBlank(true);
		base_form.findField('EvnDirection_Num').disable();
		base_form.findField('EvnDirection_Num').setAllowBlank(true);
		if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
			base_form.findField('Lpu_sid').showContainer();
			base_form.findField('Lpu_sid').setAllowBlank(false);
			base_form.findField('EvnDirection_Num').setDisabled(this.action.inlist(['view', 'editpaytype']));
			base_form.findField('EvnDirection_Num').setAllowBlank(false);
			this.refreshFieldsVisibility(['Lpu_sid']);
		}

		this.syncShadow();
	},
	filterMedicalCareFormType: function() {
		if (getRegionNick() == 'penza') {
			var base_form = this.findById('EvnDirectionEditForm').getForm();
			// Если выбрано отделение из группы отделений типа «3. Дневной стационар при стационаре» или «4. Стационар на дому» или «5. Дневной стационар при поликлинике», то значение «экстренная» не доступно для выбора.
			// Если тип направления «на плановую госпитализация», то для выбора доступны только «плановая», «неотложная».
			// Если тип направления на «на экстренную госпитализацию», то доступны для выбора: «неотложная», «экстренная».
			var LpuUnitType_SysNick = base_form.findField('LpuSection_did').getFieldValue('LpuUnitType_SysNick');
			var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');
			base_form.findField('MedicalCareFormType_id').getStore().clearFilter();
			base_form.findField('MedicalCareFormType_id').lastQuery = '';
			base_form.findField('MedicalCareFormType_id').getStore().filterBy(function(rec) {
				switch(rec.get('MedicalCareFormType_id')) {
					case 1: // Экстренная
						return false;
						break;
					case 2: // Неотложная
						return true;
						break;
					case 3: // Плановая
						return (!DirType_Code || !DirType_Code.toString().inlist(['5'])); // не на экстренную
						break;
				}
			});

			// если значения больше нет в сторе очищаем поле
			var MedicalCareFormType_id = base_form.findField('MedicalCareFormType_id').getValue();
			index = base_form.findField('MedicalCareFormType_id').getStore().findBy(function(rec) {
				if ( rec.get('MedicalCareFormType_id') == MedicalCareFormType_id ) {
					return true;
				}
				else {
					return false;
				}
			});
			if (index < 0) {
				base_form.findField('MedicalCareFormType_id').clearValue();
				base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
			}
		}
	},

	setUslugaComplex: function() {
		var win = this,
			base_form = this.findById('EvnDirectionEditForm').getForm();

		if (getRegionNick() != 'kz') return false;
		if (this.action != 'add') return false;
		if (!base_form.findField('DirType_id').getValue().inlist([2, 3, 10])) return false;
		if (!base_form.findField('LpuSectionProfile_id').getValue()) return false;
		if (!!base_form.findField('UslugaComplex_did').getValue()) return false;

		var index = win.profileUslugaLinkStore.findBy(function(rec) {
			return rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue();
		});

		if (index >= 0) {
			var rec = win.profileUslugaLinkStore.getAt(index);
			var pay_type_combo = base_form.findField('PayTypeKAZ_id');
			var usluga_complex = base_form.findField('UslugaComplex_did');
			var uslugacomplex_attributelist = rec.get('UslugaComplex_AttributeList');
			usluga_complex.getStore().load({
				params: {UslugaComplex_id: rec.get('UslugaComplex_id')},
				callback: function() {
					usluga_complex.setValue(rec.get('UslugaComplex_id'));
					usluga_complex.fireEvent('change', usluga_complex, rec.get('UslugaComplex_id'));
				}
			});

			if (uslugacomplex_attributelist && !!uslugacomplex_attributelist.split(',').find(function(el){return el == 'Kpn'})) {
				pay_type_combo.setValue(1);
			}
			else if (uslugacomplex_attributelist && uslugacomplex_attributelist.indexOf('IsNotKpn') >= 0) {
				pay_type_combo.setValue(2);
			}
			else {
				pay_type_combo.setValue('');
			}
			win.getFinanceSource();
		} else {
			win.getFinanceSource();

		}
	},

	getFinanceSource: function() {
		var win = this,
			base_form = this.findById('EvnDirectionEditForm').getForm(),
			DirType_id = base_form.findField('DirType_id').getValue();

		if (getRegionNick() != 'kz') return false;
		
		if (this.isPaidVisit) return false;

		if (this.action.inlist(['view', 'editpaytype'])) return false;

		if (!DirType_id.inlist([1, 2, 3, 4, 5, 10, 15])) return false;

		var params = {
			DirType_id: DirType_id,
			Person_id: base_form.findField('Person_id').getValue(),
			isStac: DirType_id.inlist([1, 4, 5]) ? 2 : null,
			EvnDirection_setDate: Ext.util.Format.date(base_form.findField('EvnDirection_setDate').getValue(), 'Y-m-d'),
			TreatmentClass_id: base_form.findField('TreatmentClass_id').getValue(),
			Lpu_id: getGlobalOptions().lpu_id,
			Org_oid: base_form.findField('Org_oid').getValue(),
			Lpu_did: base_form.findField('Lpu_did').getValue(),
			LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
			UslugaComplex_id: base_form.findField('UslugaComplex_did').getValue(),
			LpuUnitType_id: base_form.findField('LpuUnitType_did').getValue(),
			EvnLinkAPP_StageRecovery: base_form.findField('EvnLinkAPP_StageRecovery').getValue(),
			PurposeHospital_id: base_form.findField('PurposeHospital_id').getValue(),
			GetBed_id: base_form.findField('GetBed_id').getValue(),
			Diag_cid: base_form.findField('Diag_cid').getValue(),
			Diag_id: base_form.findField('Diag_id').getValue(),
			EvnDirection_desDT: Ext.util.Format.date(base_form.findField('EvnDirection_desDT').getValue(), 'Y-m-d'),
			bookingDateReserveId: base_form.findField('bookingDateReserveId').getValue()
		};

		if (!params.EvnDirection_setDate) return false;
		if ((!params.LpuSectionProfile_id || /*!params.Org_oid ||(#197880)*/ !params.Diag_id || !params.TreatmentClass_id) && !DirType_id.inlist([1, 4, 5])) return false;
		if ((!params.LpuUnitType_id || !params.Diag_id) && DirType_id.inlist([1, 5])) return false;
		if (!params.Diag_id && DirType_id.inlist([4])) return false;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Получение источника финансирования..." });
		loadMask.show();

		Ext.Ajax.request({
			callback: function (options, success, response) {
				loadMask.hide();

				if (success) {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					base_form.findField('PayType_id').setValue(response_obj.PayType_id);
					if (response_obj.alert) {
						sw.swMsg.alert(langs('Внимание'), response_obj.alert);
					}
					if (!!response_obj.date) {
						base_form.findField('EvnDirection_desDT').setValue(response_obj.date);
					}
					if (response_obj.bookingDateReserveId) {
						base_form.findField('bookingDateReserveId').setValue(response_obj.bookingDateReserveId);
					}
				}
				else {
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при определении источника финансирования'));
				}
			}.createDelegate(this),
			params: params,
			url: '/?c=ExchangeBL&m=getPayType'
		});
	},

	show: function() {
		sw.Promed.swEvnDirectionEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = this.findById('EvnDirectionEditForm').getForm();

		this.center();
		base_form.reset();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(langs('Сообщение'), langs('Неверные параметры'));
			return false;
		}
		if ( !Ext.isEmpty(arguments[0].formParams.PresciptionType_id)) {
			this.PresciptionType_id = arguments[0].formParams.PresciptionType_id;
		}
		var lpu_sort = arguments[0].formParams.LpuSection_did||0;
		this.action = arguments[0].action || null;
		this.TimetableGraf_id = arguments[0].TimetableGraf_id || null;
		this.type = arguments[0].type || null;
		this.TimetableStac_id = arguments[0].TimetableStac_id || null;
		this.TimetableMedService_id = arguments[0].TimetableMedService_id || null;
		this.TimetableResource_id = arguments[0].TimetableResource_id || null;
		this.EvnUsluga_id = arguments[0].EvnUsluga_id || null;
		this.timetable = arguments[0].formParams.timetable || '';
		this.From_MedStaffFact_id = arguments[0].formParams.From_MedStaffFact_id || null;
		this.LpuSectionLpuSectionProfileList = arguments[0].formParams.LpuSectionLpuSectionProfileList || null;
		this.callback = arguments[0].callback || Ext.emptyFn;
		this.onHide = arguments[0].onHide|| Ext.emptyFn;
		this.closable = (arguments[0].disableClose)?false:true;
		this.isWasChosenRemoteConsultCenter = (arguments[0].formParams.DirType_id
			&& 17 == arguments[0].formParams.DirType_id
			&& arguments[0].formParams.MedService_id) ? true : false;
		if (this.buttons[4] && this.buttons[4].iconCls == 'cancel16') {
			this.buttons[4].setVisible(this.closable);
		}
		this.allowQuestionPrintEvnDirection = arguments[0].disableQuestionPrintEvnDirection ? false : true;
		this.isNotForSystem = arguments[0].formParams.isNotForSystem || null;
		this.ZNOinfo = arguments[0].ZNOinfo || null;
		this.directionNumberParams = {
			DirType_id: null,
			EvnDirection_Num: null,
			processing: false,
			year: null
		};
		this.ignorePersonPrivilegeCheck = false;
		
		if (arguments[0].isPaidVisit) this.isPaidVisit = arguments[0].isPaidVisit;
		if (arguments[0].kzScreening) this.kzScreening = arguments[0].kzScreening;

		var personInfoFrame = this.findById('EDirEF_PersonInformationFrame');
		personInfoFrame.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnDirection_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EDirEF_PersonInformationFrame', field);

				// если не заполнены заполняем
				if (Ext.isEmpty(base_form.findField('PersonEvn_id').getValue())) {
					base_form.findField('Person_id').setValue(personInfoFrame.getFieldValue('Person_id'));
					base_form.findField('PersonEvn_id').setValue(personInfoFrame.getFieldValue('PersonEvn_id'));
					base_form.findField('Server_id').setValue(personInfoFrame.getFieldValue('Server_id'));
				}

				if( getRegionNick() == 'ufa' ) {
					form.processVisibleEvnDirectionPersonDetail();
				}
			}
		});
		// проверяем в каком режиме открыли форму
		this.mode = (arguments[0].mode && arguments[0].mode == 'nosave')?arguments[0].mode:'';

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if(arguments[0].personData){
			base_form.setValues(arguments[0].personData);
		}
		if (!Ext.isEmpty(arguments[0].formParams.DirType_Code) && arguments[0].formParams.DirType_Code == 9) {
			form.DirType_Code = arguments[0].formParams.DirType_Code;
		} else {
			form.DirType_Code = null;
		}
		
		form.CVIConsultRKC_id = arguments[0].formParams.CVIConsultRKC_id || null;
		form.RepositoryObserv_sid = arguments[0].formParams.RepositoryObserv_sid || null;
		form.isRKC = arguments[0].formParams.isRKC || null;

		base_form.setValues(arguments[0].formParams);
		if (Ext.isEmpty(base_form.findField('PayType_id').getValue()) && getRegionNick() != 'kz') {
			// по умолчанию ОМС
			base_form.findField('PayType_id').setFieldValue('PayType_SysNick', 'oms');
		}

		this.checkEvnDirectionIsReceive();
		this.filterLpuSectionProfileCombo({});

		var fparams = arguments[0].formParams,
			mscombo = base_form.findField('MedService_id');

		if(fparams.MedService_id && fparams.Lpu_did){
			mscombo.getStore().baseParams = {
				LpuSectionProfile_id: fparams.LpuSectionProfile_id,
				isDirection: 1,
				setDate: fparams.EvnDirection_setDate || getGlobalOptions().date,
				Lpu_id: fparams.Lpu_did,
				isLpuSectionLpuSectionProfileList: 1
			};
			mscombo.getStore().load({
				callback: function()
				{
					mscombo.setValue(fparams.MedService_id);
				}
			});
		} else {
			//fparams.MedService_id часто undefined, так что store остается пустой
			var data = arguments[0].userMedStaffFact,
				params = {};
			if (!Ext.isEmpty(data) && data.Lpu_id)
				params.Lpu_id = data.Lpu_id;
			else params.Lpu_id = getGlobalOptions().lpu_id;
			params.isLpuSectionLpuSectionProfileList = 1;
			mscombo.getStore().baseParams = params;
			mscombo.getStore().load();
		}

		var whereLpu = '';
		if(getRegionNick() == 'perm' && !getGlobalOptions().lpu_istest) whereLpu = ' WHERE and ISNULL(Lpu_IsTest, 1) <> 2 ';
		base_form.findField('Lpu_did').getStore().load(
		{
			params: {where: whereLpu},
			callback: function()
			{
				var lpu_did_combo = base_form.findField('Lpu_did');
				lpu_did_combo.setValue(base_form.findField('Lpu_did').getValue());
				lpu_did_combo.getStore().clearFilter();
				lpu_did_combo.lastQuery = '';
				if (getRegionNick().inlist(['astra', 'ekb'])) {
					form.refreshFieldsVisibility(['Lpu_did']);
				}
			}
		});
		if(this.type=='recwp'){
			base_form.findField('EvnDirection_noSetDateTime').setValue('');
			base_form.findField('EvnDirection_noSetDateTime').setDisabled(false);
			base_form.findField('EvnDirection_noSetDateTime').setAllowBlank(false);
		}else{
			base_form.findField('EvnDirection_noSetDateTime').setDisabled(true);
			base_form.findField('EvnDirection_noSetDateTime').setAllowBlank(true);
		}

		this.setDisabled(this.action.inlist(['view', 'editpaytype']));
		if (this.action == 'editpaytype') {
			base_form.findField('PayType_id').enable();
		}
		
		if (getRegionNick() == 'kz') {
			base_form.findField('PayType_id').disable();
		}

		if ( arguments[0].is_cito) {
			base_form.findField('DirType_id').setValue(5);
			base_form.findField('DirType_id').setDisabled(true);
		}
		if ( arguments[0].formParams.time ) {
			base_form.findField('EvnDirection_setDateTime').setValue(arguments[0].formParams.time);
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
			base_form.findField('EvnDirection_desDT').setAllowBlank(true);
		} else {
			// вообще скрываем это поле, если времени нет, значит направление в очередь
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(false);
			var DirType_Code = base_form.findField('DirType_id').getFieldValue('DirType_Code');
			if (DirType_Code == 26) {
				base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(false);
			} else {
				base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(true);
			}
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20 || getRegionNick().inlist(['astra','ekb','penza','kz']) || (getRegionNick() == 'krym' && !Ext.isEmpty(base_form.findField('DirType_id').getValue()) && base_form.findField('DirType_id').getValue().inlist([ 1, 5 ])));

			var allowBlank = true;

			switch ( getRegionNick() ) {
				case 'astra':
				case 'penza':
					allowBlank = (base_form.findField('DirType_id').getValue() != 1);
					break;

				case 'krym':
					allowBlank = (Ext.isEmpty(base_form.findField('DirType_id').getValue()) || !base_form.findField('DirType_id').getValue().inlist([ '1', '5' ]));
					break;
			}

			base_form.findField('EvnDirection_desDT').setAllowBlank(allowBlank);
		}
		
		if (arguments[0].formParams.StudyTarget_id) {
			base_form.findField('StudyTarget_id').disable();
		}
		
		if (!!arguments[0].formParams.TreatmentClass_id && getRegionNick() == 'kz') {
			base_form.findField('TreatmentClass_id').disable();
		}

		base_form.findField('EvnDirection_desDT').setMinValue(undefined);
		
		base_form.findField('StudyTarget_id').setContainerVisible(false);
		base_form.findField('StudyTarget_id').setAllowBlank(true);
		
		base_form.findField('GetBed_id').hideContainer();

		base_form.findField('ScreenType_id').hideContainer();
		base_form.findField('PayTypeKAZ_id').hideContainer();
		base_form.findField('TreatmentClass_id').hideContainer();
		if (getRegionNick() != 'kz') {
			base_form.findField('PurposeHospital_id').hideContainer();
			base_form.findField('EvnLinkAPP_StageRecovery').hideContainer();
			base_form.findField('Diag_cid').hideContainer();
		}

		form.EvnDirectionDetail.hide();

		this.syncSize();
		var lsp_combo = base_form.findField('LpuSectionProfile_id');
		if ( arguments[0].formParams.LpuSectionProfile_id ) {
			lsp_combo.setValue(arguments[0].formParams.LpuSectionProfile_id);
		}
		
		var diag_id = arguments[0].formParams.Diag_id;
		
		var dir_type_combo = base_form.findField('DirType_id');
		var dir_type_id = parseInt(dir_type_combo.getValue());

		if ( this.profileUslugaLinkStore.getCount() == 0 && getRegionNick() == 'kz' && !arguments[0].formParams.UslugaComplex_did ) {
			this.profileUslugaLinkStore.load({
				callback: function() {
					form.setUslugaComplex();
				}
			});
		} else if (getRegionNick() == 'kz' && !arguments[0].formParams.UslugaComplex_did) {
			form.setUslugaComplex();
		}

		if(!fparams.UslugaComplex_did){
			base_form.findField('FSIDI_id').setValue('');
			base_form.findField('FSIDI_id').setContainerVisible(false);
			base_form.findField('FSIDI_id').setAllowBlank(true);
		}

		if(this.action == 'add') {
			if(
				getRegionNick() == 'penza'
				&& base_form.findField('DirType_id').getValue().inlist(['1', '5'])//госпитализация плановая, экстренная
			) {
				base_form.findField('TreatmentType_id').showContainer();
				base_form.findField('TreatmentType_id').setAllowBlank(false);
				base_form.findField('TreatmentType_id').enable();
			} else {
				base_form.findField('TreatmentType_id').hideContainer();
				base_form.findField('TreatmentType_id').setAllowBlank(true);
			}
		}

		if (!Ext.isEmpty(dir_type_id)&&dir_type_id) {
			// если передан конкретный тип направления, его и устанавливаем
			dir_type_combo.getStore().clearFilter();
			dir_type_combo.lastQuery = '';
		} else if (this.action.inlist(['add', 'edit'])) {
			// иначе фильтруем
			dir_type_combo.getStore().clearFilter();
			dir_type_combo.lastQuery = '';
			dir_type_combo.getStore().filterBy(function(record) {
				if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
					// форма добавления внешнего направления, убрать направление на ВК или МСЭ
					if (record.get('DirType_id').toString().inlist(['9'])) {
						return false;
					}
				}
				if(record.get('DirType_id').toString().inlist(['15','20','21'])){
					return false;
				}
				var filter_flag = true;
				switch (form.timetable)
				{
					case 'TimetableGraf':
						filter_flag = record.get('DirType_id').toString().inlist(['2','3','16']);
						dir_type_id = 16;
						break;
					case 'TimetableStac':
						filter_flag = record.get('DirType_id').toString().inlist(['1','2','4','5','6']);
						break;
					case 'TimetableResource':
					case 'TimetableMedService':
						filter_flag = (10 == record.get('DirType_id'));
						dir_type_id = 10;
						break;
					default:
						filter_flag = true;
				}
				if ( !filter_flag ) {
					return false;
				}

				return true;
			});
		}

		dir_type_combo.setValue(dir_type_id);
		if (!dir_type_combo.getFieldValue('DirType_Code')) {
			dir_type_combo.setValue(null);
			dir_type_combo.setRawValue('');
		}

		if (dir_type_id > 0) {
			dir_type_combo.setDisabled(true);
			if(dir_type_id.inlist([1,5])&&getRegionNick() == 'ekb'){
				base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(true);
			}else{
				base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(false);
			}
		} else {
			base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(false);
		}
		
		var Lpu_IsNotForSystem = base_form.findField('Lpu_sid').getFieldValue('Lpu_IsNotForSystem') != 2;// 1 - не работает в системе, 2 - работает
		var medstaff_id = base_form.findField('MedStaffFact_id');

		if (getRegionNick() === 'ekb')
		{
			if(base_form.findField('EvnDirection_IsReceive').getValue()==2 && base_form.findField('Lpu_sid').getValue())
			{
				base_form.findField('MedPersonal_Code').setAllowBlank( ! dir_type_id.inlist([ '1','5','10']) );
				base_form.findField('MedStaffFact_id').setAllowBlank( true );
			} else
			{
				base_form.findField('MedPersonal_Code').setAllowBlank( ! dir_type_id.inlist([ '1','5','10']) );
				base_form.findField('MedStaffFact_id').setAllowBlank( ! dir_type_id.inlist([ '1','5']) );
			}
		} else if (getRegionNick() === 'buryatiya')
		{
			base_form.findField('MedStaffFact_id').setAllowBlank( true );
		}
		
		if(getRegionNick() == 'ekb') {
			var medstaff_id = base_form.findField('MedStaffFact_id');
			medstaff_id.codeField = 'MedPersonal_DloCode';
			medstaff_id.tpl = new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
				'<table style="border: 0;">',
				'<td style="width: 45px;">{MedPersonal_TabCode}&nbsp;</td>',
				'<td style="width: 45px;"><font color="red">{MedPersonal_DloCode}&nbsp;</font></td>',
				'<td>',
					'<div style="font-weight: bold;">{MedPersonal_Fio}&nbsp;{[Ext.isEmpty(values.LpuSection_Name)?"":values.LpuSection_Name]}</div>',
					'<div style="font-size: 10px;">{PostMed_Name}{[!Ext.isEmpty(values.MedStaffFact_Stavka) ? ", ст." : ""]} {MedStaffFact_Stavka}</div>',
					'<div style="font-size: 10px;">{[!Ext.isEmpty(values.WorkData_begDate) ? "Дата начала работы: " + values.WorkData_begDate:""]} {[!Ext.isEmpty(values.WorkData_endDate) ? "Дата увольнения: " + this.formatWorkDataEndDate(values.WorkData_endDate) :""]}</div>',
					'<div style="font-size: 10px;">{[!Ext.isEmpty(values.Lpu_id) && values.Lpu_id != getGlobalOptions().lpu_id?values.Lpu_Name:""]}</div>',
				'</td>',
				'</tr></table>',
				'</div></tpl>',
				{
					formatWorkDataEndDate: function(endDate) {
						var fixed = (typeof endDate == 'object' ? Ext.util.Format.date(endDate, 'd.m.Y') : endDate);
						return fixed;
					}
				}
			);
		} else 
			base_form.findField('MedPersonal_Code').setContainerVisible(false);

		//загружаем файлы
		var EvnDirection_id = false;
		if( arguments && arguments[0] && arguments[0].EvnDirection_id)
			EvnDirection_id
		this.refreshFilesPanel(EvnDirection_id, dir_type_id);

		switch ( this.action ) {
			case 'add':
				setCurrentDateTime({
					callback: function() {
						// В случае создания заявки на экстренную операцию из АРМ приемного поле «Желаемая дата» автоматически заполнять текущей датой
						if (base_form.findField('EvnDirection_IsCito').getValue() == 2 && base_form.findField('DirType_id').getValue() == 20) {
							base_form.findField('EvnDirection_desDT').setValue(base_form.findField('EvnDirection_setDate').getValue());
						}

						base_form.findField('EvnDirection_setDate').fireEvent('change', base_form.findField('EvnDirection_setDate'), base_form.findField('EvnDirection_setDate').getValue());
						base_form.findField('EvnDirection_setDate').focus(true, 0);
						dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
						base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
						this.loadMedPersonalCombo();
						this.loadBedList();
					}.createDelegate(this),
					dateField: base_form.findField('EvnDirection_setDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					windowId: 'EvnDirectionEditForm'
				});
				this.setTitle(WND_POL_EDIRADD);
				this.enableEdit(true);

				if (this.isNotForSystem) {
					base_form.findField('Lpu_did').clearValue();
				}

				base_form.findField('EvnDirection_desDT').setMinValue(getValidDT(getGlobalOptions().date, '')); // желаемая дата ограничена текущей датой

				if (base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
					// форма добавления внешнего направления
					base_form.findField('Lpu_did').setValue(getGlobalOptions().lpu_id);
					base_form.findField('Lpu_did').disable();
					if(17 != dir_type_id){
						base_form.findField('Lpu_sid').clearValue();
						base_form.findField('Lpu_sid').fireEvent('change', base_form.findField('Lpu_sid'), base_form.findField('Lpu_sid').getValue());
					}
				}
				if (17 == dir_type_id && base_form.findField('EvnDirection_IsReceive').getValue() == 2) {
					base_form.findField('Lpu_sid').setAllowBlank(true);
					base_form.findField('Diag_id').setAllowBlank(false);
					base_form.findField('MedStaffFact_id').setAllowBlank(true);
				}

				if (getRegionNick() != 'kz' && (17 == base_form.findField('DirType_id').getValue())) {
					base_form.findField('ConsultingForm_id').setAllowBlank(false);
				}

				base_form.findField('ConsultingForm_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 17);
				base_form.findField('LpuSection_did').setContainerVisible(base_form.findField('DirType_id').getValue() == 5);
				base_form.findField('LpuSection_did').setAllowBlank(base_form.findField('DirType_id').getValue() != 5 || getRegionNick() != 'kareliya');
				base_form.findField('EvnXml_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
				if (!getRegionNick().inlist([ 'astra', 'krym', 'penza','kz' ])) {
					base_form.findField('EvnDirection_desDT').setAllowBlank(base_form.findField('DirType_id').getValue() != 20);
				}
				base_form.findField('EvnDirectionOper_IsAgree').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
				form.findById('EDEW_AnestButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
				form.findById('EDEW_OperButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
				base_form.findField('EvnXml_id').getStore().removeAll();
				if (base_form.findField('DirType_id').getValue() == 20 && !Ext.isEmpty(base_form.findField('EvnDirection_pid').getValue())) {
					// грузим операционные эпикризы
					base_form.findField('EvnXml_id').getStore().load({
						params: {
							Evn_id: base_form.findField('EvnDirection_pid').getValue(),
							XmlType_ids: Ext.util.JSON.encode([3,10])
						}
					});
				}

				loadMask.hide();
				base_form.findField('EvnDirection_setDate').focus(true, 250);
				this.buttons[1].setVisible(false);
				
				//установка значений из входящих параметров
				if ( diag_id != null && diag_id.toString().length > 0 ) { //если во входящих параметрах есть диагноз - настраиваем комбо
					base_form.findField('Diag_id').getStore().load(
					{
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) 
							{
								if ( record.get('Diag_id') == diag_id ) 
								{
									base_form.findField('Diag_id').setValue(diag_id);
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
								}
							});
							var diag_code = base_form.findField('Diag_id').getFieldValue('Diag_Code');
							if(
								diag_code >= 'C00' && diag_code <= 'C97'
								|| diag_code >= 'D00' && diag_code <= 'D09'
							) {
								base_form.findField('TreatmentType_id').clearValue();
							} else {
								base_form.findField('TreatmentType_id').setValue(5);
							}
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}
				
				if (this.kzScreening) {
					
					base_form.findField('Diag_id').getStore().load({
						params: { where: "where Diag_Code = 'Z10.8'" },
						callback: function() {
							var diag_id = base_form.findField('Diag_id').getStore().getAt(0).get('Diag_id');
							base_form.findField('Diag_id').setValue(diag_id);
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), base_form.findField('Diag_id').getStore().getAt(0), 0);
							base_form.findField('Diag_id').setDisabled(true);
						}
					});
					
					base_form.findField('TreatmentClass_id').setValue(29);
					base_form.findField('TreatmentClass_id').fireEvent('change', base_form.findField('TreatmentClass_id'), 29, 0);
					base_form.findField('TreatmentClass_id').setDisabled(true);
					
					base_form.findField('UslugaComplex_did').setDisabled(true);
				}

				var usluga_category = base_form.findField('UslugaCategory_did');
				var usluga_complex = base_form.findField('UslugaComplex_did');
				if (getRegionNick() == 'kz') {
					usluga_category.setValue(95);
					if(base_form.findField('DirType_id').getValue().inlist([1, 4, 5])) {
						usluga_category.setValue(80);
					}

					usluga_category.disable();
					usluga_complex.setUslugaCategoryList([usluga_category.getFieldValue('UslugaCategory_SysNick')]);
				}

				if (!Ext.isEmpty(usluga_complex.getValue())) {
					usluga_complex.getStore().load({
						params: {UslugaComplex_id: usluga_complex.getValue()},
						callback: function() {
							usluga_complex.setValue(usluga_complex.getValue());
							usluga_complex.fireEvent('change',usluga_complex,usluga_complex.getValue());
						}
					});
				}
			break;

			case 'edit':
				this.setTitle(WND_POL_EDIREDIT);
				this.enableEdit(true);

				if ( arguments[0].EvnDirection_id ) {
					this.loadRecord({
							EvnDirection_id: arguments[0].EvnDirection_id,
							action: 'edit'
						},
						loadMask
					);
				} else {
					if ( diag_id != null && diag_id.toString().length > 0 ) 
					{
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_id ) {
										base_form.findField('Diag_id').setValue(diag_id);
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									}
								});
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}

					this.loadMedPersonalCombo();

					dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
					base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
				}
				loadMask.hide();
				base_form.findField('EvnDirection_setDate').focus(true, 250);
				this.buttons[1].setVisible(true);
			break;

			case 'view':
			case 'editpaytype':
				this.setTitle(WND_POL_EDIRVIEW);
				if (this.action == 'editpaytype') {
					this.enableEdit(true);
				} else {
					this.enableEdit(false);
					this.buttons[1].setVisible(true);
				}

				if ( arguments[0].EvnDirection_id ) {
					this.loadRecord({
							EvnDirection_id: arguments[0].EvnDirection_id,
							action: 'view'
						},
						loadMask
					);
				} else if ( arguments[0].EvnQueue_id ) {
					this.loadRecord({
							EvnQueue_id: arguments[0].EvnQueue_id,
							EvnDirection_id: null,
							action: 'view'
						},
						loadMask
					);
				} else {
					if ( diag_id != null && diag_id.toString().length > 0 ) 
					{
						base_form.findField('Diag_id').getStore().load({
							callback: function() {
								base_form.findField('Diag_id').getStore().each(function(record) {
									if ( record.get('Diag_id') == diag_id ) {
										base_form.findField('Diag_id').setValue(diag_id);
										base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
									}
								});
							},
							params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
						});
					}

					this.loadMedPersonalCombo();

					loadMask.hide();
					dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
					base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());
					this.buttons[1].setVisible(true);
					this.buttons[this.buttons.length - 1].focus();
				}
				
			break;
		}
		
		this.buttons[1].setDisabled(isMseDepers());

		var toothsPanel = form.findById(form.id + "_" + 'ToothNumFieldsPanel');
		toothsPanel.clearPanel();

		// скрываем зубы
		if (!arguments[0].formParams.PrescriptionType_Code || (arguments[0].formParams.PrescriptionType_Code && parseInt(arguments[0].formParams.PrescriptionType_Code) != 12)
			|| !arguments[0].parentEvnClass_SysNick || (arguments[0].parentEvnClass_SysNick && arguments[0].parentEvnClass_SysNick != "EvnVizitPLStom")
		) {
			toothsPanel.hide();
		}

		base_form.findField('Lpu_did').fireEvent('change',base_form.findField('Lpu_did'),base_form.findField('Lpu_did').getValue()); //http://redmine.swan.perm.ru/issues/23877
		if (getRegionNick() == 'kz') {
			this.FileUploadPanelKZ.setDisabled(false);
		}
		if ( diag_id != null && diag_id.toString().length > 0 )
		{
			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					base_form.findField('Diag_id').getStore().each(function(record) {
						if ( record.get('Diag_id') == diag_id ) {
							base_form.findField('Diag_id').setValue(diag_id);
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
						}
					});
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
			});
		}
		form.syncShadow();
	},

	setVisibleField: function(field, visibled) {
		var win = this;
		field.setContainerVisible(visibled);
		field.setDisabled(!visibled || !win.action.inlist(['edit']));
	},
	/* Чтение записи EvnDirection */
	loadRecord: function(params, loadMask)
	{
		var win = this;
		var bform = this.findById('EvnDirectionEditForm');
		var base_form = bform.getForm();
		var url = '/?c=EvnDirection&m=loadEvnDirectionEditForm',
			prms = { EvnDirection_id: params.EvnDirection_id };
		if (win.DirType_Code == 9) {
			prms.DirType_id = 10;
		}
		if (params.EvnQueue_id)
		{
			url = '/?c=Queue&m=loadEvnDirectionEditForm';
			prms = { EvnQueue_id: params.EvnQueue_id };
		}
		if (params.action != 'add')
		{
			bform.load(
			{
				params: prms,
				failure: function() 
				{
					loadMask.hide();
					sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						fn: function() 
						{
							form.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: langs('Ошибка запроса к серверу. Попробуйте повторить операцию.'),
						title: langs('Ошибка')
					});
				},
				success: function(record)
				{
					win.directionNumberParams.EvnDirection_Num = bform.getForm().findField('EvnDirection_Num').getValue();

					// Здесь в обратке выполняем все необходимые операции после загрузки данных
					params.EvnDirection_id = bform.getForm().findField('EvnDirection_id').getValue();
					params.ConsultingForm_id = bform.getForm().findField('ConsultingForm_id').getValue();
					params.LpuSectionProfile_id = bform.getForm().findField('LpuSectionProfile_id').getValue();
					params.Lpu_did = bform.getForm().findField('Lpu_did').getValue();
					params.LpuUnitType_did = bform.getForm().findField('LpuUnitType_did').getValue();
					params.EvnDirection_Num = bform.getForm().findField('EvnDirection_Num').getValue();
					params.Diag_id = bform.getForm().findField('Diag_id').getValue();
					params.MedPersonal_id = bform.getForm().findField('MedPersonal_id').getValue();
					params.MedStaffFact_id = bform.getForm().findField('MedStaffFact_id').getValue();
					params.LpuSection_id = bform.getForm().findField('LpuSection_id').getValue();
					params.Post_id = bform.getForm().findField('Post_id').getValue();
					params.MedPersonal_zid = bform.getForm().findField('MedPersonal_zid').getValue();
					params.EvnDirection_setDate = bform.getForm().findField('EvnDirection_setDate').getValue();
					params.EvnDirection_Descr = bform.getForm().findField('EvnDirection_Descr').getValue();
					params.EvnDirection_IsCito = bform.getForm().findField('EvnDirection_IsCito').getValue();
					params.MedService_id = bform.getForm().findField('MedService_id').getValue();
					params.Resource_id = bform.getForm().findField('Resource_id').getValue();
					params.Person_Surname = bform.getForm().findField('Person_Surname').getValue();
					params.Person_Firname = bform.getForm().findField('Person_Firname').getValue();
					params.Person_Secname = bform.getForm().findField('Person_Secname').getValue();
					params.Person_Birthday = bform.getForm().findField('Person_Birthday').getValue();
					params.ConsultationForm_id = bform.getForm().findField('ConsultationForm_id').getValue();
					params.Org_oid = bform.getForm().findField('Org_oid').getValue();

					if(
						getRegionNick() == 'penza'
						&& base_form.findField('DirType_id').getValue().inlist(['1', '5'])//госпитализация плановая, экстренная
					) {
						base_form.findField('TreatmentType_id').showContainer();
						base_form.findField('TreatmentType_id').setAllowBlank(false);

						if(win.action.inlist(['add', 'edit'])) {
							base_form.findField('TreatmentType_id').enable();
						} else {
							base_form.findField('TreatmentType_id').disable();
						}
					} else {
						base_form.findField('TreatmentType_id').hideContainer();
						base_form.findField('TreatmentType_id').setAllowBlank(true);
					}


					// если есть зубы
					var ToothNums = bform.getForm().findField('ToothNums').getValue();
					if (ToothNums) {

						var toothsPanel = bform.findById(win.id + "_" + 'ToothNumFieldsPanel');
						toothsPanel.fillPanelByData({panelValues: ToothNums});
					}

					bform.getForm().findField('Lpu_did').fireEvent('change',bform.getForm().findField('Lpu_did'),bform.getForm().findField('Lpu_did').getValue());
					bform.getForm().findField('MedicalCareFormType_id').fireEvent('change', bform.getForm().findField('MedicalCareFormType_id'), bform.getForm().findField('MedicalCareFormType_id').getValue());

					this.loadParams(params, loadMask);

				}.createDelegate(this),
				url: url
			});
		}
		else 
		{
			this.loadParams(params, loadMask);
		}
	},
	/* Установка полей EvnDirection */
	loadParams: function(params, loadMask)
	{
		var win = this;
		var base_form = this.findById('EvnDirectionEditForm').getForm();
		if ( params.Diag_id != null && params.Diag_id.toString().length > 0 ) 
		{
			base_form.findField('Diag_id').getStore().load({
				callback: function() {
					base_form.findField('Diag_id').getStore().each(function(record) {
						if ( record.get('Diag_id') == params.Diag_id ) {
							base_form.findField('Diag_id').setValue(params.Diag_id);
							base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
						}
					});
				},
				params: { where: "where DiagLevel_id = 4 and Diag_id = " + params.Diag_id }
			});
		}

		if ( base_form.findField('EvnDirection_setDateTime').getValue() ) {
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
			base_form.findField('EvnDirection_desDT').setAllowBlank(true);
		} else {
			// вообще скрываем это поле, если времени нет, значит направление в очередь
			base_form.findField('EvnDirection_setDateTime').setContainerVisible(false);
			base_form.findField('EvnDirection_noSetDateTime').setContainerVisible(true);
			base_form.findField('EvnDirection_desDT').setContainerVisible(base_form.findField('DirType_id').getValue() == 20 || getRegionNick().inlist(['astra','ekb','penza', 'kz']) || (getRegionNick() == 'krym' && !Ext.isEmpty(base_form.findField('DirType_id').getValue()) && base_form.findField('DirType_id').getValue().inlist([ 1, 5 ])));

			var allowBlank = true;

			switch ( getRegionNick() ) {
				case 'astra':
				case 'penza':
					allowBlank = (base_form.findField('DirType_id').getValue() != 1);
					break;

				case 'krym':
					allowBlank = (Ext.isEmpty(base_form.findField('DirType_id').getValue()) || !base_form.findField('DirType_id').getValue().inlist([ '1', '5' ]));
					break;
			}

			base_form.findField('EvnDirection_desDT').setAllowBlank(allowBlank);
		}

		if (getRegionNick() != 'kz' && (17 == base_form.findField('DirType_id').getValue())) {
			base_form.findField('ConsultingForm_id').setAllowBlank(false);
		}

		base_form.findField('ConsultingForm_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 17);
		base_form.findField('LpuSection_did').setContainerVisible(base_form.findField('DirType_id').getValue() == 5);
		base_form.findField('LpuSection_did').setAllowBlank(base_form.findField('DirType_id').getValue() != 5 || getRegionNick() != 'kareliya');
		base_form.findField('EvnXml_id').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
		base_form.findField('EvnDirectionOper_IsAgree').setContainerVisible(base_form.findField('DirType_id').getValue() == 20);
		win.findById('EDEW_AnestButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
		win.findById('EDEW_OperButton').setVisible(base_form.findField('DirType_id').getValue() == 20);
		base_form.findField('EvnXml_id').getStore().removeAll();
		if (base_form.findField('DirType_id').getValue() == 20 && !Ext.isEmpty(base_form.findField('EvnDirection_pid').getValue())) {
			// грузим операционные эпикризы
			base_form.findField('EvnXml_id').getStore().load({
				params: {
					Evn_id: base_form.findField('EvnDirection_pid').getValue(),
					XmlType_ids: Ext.util.JSON.encode([3,10])
				},
				callback: function() {
					base_form.findField('EvnXml_id').setValue(base_form.findField('EvnXml_id').getValue()); // а могли и удалить уже эпикриз то..
				}
			});
		}

		var usluga_category = base_form.findField('UslugaCategory_did');
		var usluga_complex = base_form.findField('UslugaComplex_did');

		if (getRegionNick() == 'kz') {
			usluga_category.setValue(95);
			usluga_category.disable();
		}

		if (!Ext.isEmpty(usluga_category.getValue())) {
			usluga_complex.setUslugaCategoryList([usluga_category.getFieldValue('UslugaCategory_SysNick')]);
		}

		if (getRegionNick() == 'ufa') {
			win.processVisibleEvnDirectionPersonDetail();
		}

		if (!Ext.isEmpty(usluga_complex.getValue())) {
			usluga_complex.getStore().load({
				params: {UslugaComplex_id: usluga_complex.getValue()},
				callback: function() {
					usluga_complex.setValue(usluga_complex.getValue());
					if( getRegionNick() == 'ufa' ) {
						win.processVisibleEvnDirectionPersonDetail();
					}
				}
			});
		}

		if(usluga_complex.getValue()){
			base_form.findField('FSIDI_id').checkVisibilityAndGost(usluga_complex.getValue());
		}

		if(params.Org_oid){
			var Org_oidCombo = base_form.findField('Org_oid');
			var Lpu_didCombo = base_form.findField('Lpu_did');
			//isNotForSystem
			var Org_oid = Org_oidCombo.getValue();
			if(Org_oid){
				Org_oidCombo.getStore().load({
					callback: function(records, options, success) {
						Org_oidCombo.clearValue();
						if ( success ) {
							Org_oidCombo.setValue(Org_oid);
							if(!Lpu_didCombo.getValue()) Lpu_didCombo.setContainerVisible(false);
							Org_oidCombo.setContainerVisible(true);
						}
					},
					params: {
						Org_id: Org_oid
					}
				});
			}
		}

		this.checkEvnDirectionIsReceive();

		win.syncShadow();

		this.loadMedPersonalCombo();
		this.loadBedList();
					
		loadMask.hide();
		var dir_type_combo = base_form.findField('DirType_id');
		dir_type_combo.fireEvent('change', dir_type_combo, dir_type_combo.getValue());
		base_form.findField('MedicalCareFormType_id').fireEvent('change', base_form.findField('MedicalCareFormType_id'), base_form.findField('MedicalCareFormType_id').getValue());

		if(dir_type_combo.getValue().inlist([1,5])&&getRegionNick() == 'ekb'){
			base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(true);
		}else{
			base_form.findField('EvnDirection_IsNeedOper').setContainerVisible(false);
		}

		this.buttons[this.buttons.length - 1].focus();
	},
	processVisibleEvnDirectionPersonDetail: function () {
		var win = this;
		var baseForm = win.formPanel.getForm();
		var contingentField = baseForm.findField('HIVContingentTypeFRMIS_id');
		var covidContingentField = baseForm.findField('CovidContingentType_id');
		var personPanel = win.findById('EDirEF_PersonInformationFrame');
		var hormonalPhaseField = baseForm.findField('HormonalPhaseType_id');
		var uslugaField = baseForm.findField('UslugaComplex_did');
		var heightField = baseForm.findField('PersonHeight_Height');
		var weightField = baseForm.findField('PersonWeight_WeightText');
		var raceField = baseForm.findField('RaceType_id');
		var isLab = uslugaField.isAttribute('lab');

		var isVisiblePanel = !!contingentField.getValue() || !!covidContingentField.getValue() || !!hormonalPhaseField.getValue() || !!heightField.getValue() || !!weightField.getValue() || !!raceField.getValue();
		isVisiblePanel &= win.action.inlist(['view','editpaytype']);

		win.EvnDirectionDetail.setVisible(isLab || isVisiblePanel);

		var isContingent = uslugaField.isAttribute('contingent') || !!contingentField.getValue();
		var isContingentCovid = uslugaField.isAttribute('contingent_covid') || !!covidContingentField.getValue();

		if (this.action != 'add') {
			isContingent &= baseForm.findField('pmUser_insID').getValue() == getGlobalOptions().pmuser_id || isUserGroup('hivresearch');
		}

		//contingentField.setDisabled(!isContingent || this.action == 'view');
		contingentField.setContainerVisible(isContingent);


		//covidContingentField.setDisabled(!isContingentCovid || this.action == 'view');
		covidContingentField.setContainerVisible(isContingentCovid);

		hormonalPhaseField.setContainerVisible(personPanel.getFieldValue('Sex_Code') == 2);
		win.syncShadow();
	},

    /** #refs #121771
	 * Изменяет обязательность поля LpuSection_did. Поле обязательно если МО работает в системе и для ее заведена структура
	 *
     * @param Lpu_IsNotForSystem - работает ли МО в системе. 1 - работает в системе, 2 - не работает в системе
     * @param countLpuSectionCombo - Количество отделений в структуре
     */
    changeRequireField_LpuSection_did: function (Lpu_IsNotForSystem, countLpuSectionCombo) {

        var base_form = this.findById('EvnDirectionEditForm').getForm();

        base_form.findField('LpuSection_did').setAllowBlank(true);
        if (countLpuSectionCombo != 0 && Lpu_IsNotForSystem == 1) {
            base_form.findField('LpuSection_did').setAllowBlank(false);
        }

    },

	filterLpuSectionCombo: function () {
        var win = this;

		var base_form = this.findById('EvnDirectionEditForm').getForm(),
			LpuSectionCombo = base_form.findField('LpuSection_did'),
			LpuSection_id = base_form.findField('LpuSection_did').getValue();
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
			Lpu_IsNotForSystem = base_form.findField('Lpu_did').getFieldValue('Lpu_IsNotForSystem'),
			LpuSectionProfile_Code = base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Code');


		/*if (!LpuSectionCombo.isVisible()) {
			return false;
		}*/

		LpuSectionCombo.getStore().clearFilter();
		LpuSectionCombo.lastQuery = '';

		var setComboValue = function(combo, id) {
			if ( Ext.isEmpty(id) ) {
				return false;
			}

			var index = combo.getStore().findBy(function(rec) {
				return (rec.get('LpuSection_id') == id);
			});

			if ( index == -1 && combo.isVisible() ) {
				combo.clearValue();
			}
			else {
				combo.setValue(id);
			}

			combo.fireEvent('change', combo, combo.getValue());

			return true;
		};

		if (
			!Ext.isEmpty(base_form.findField('Lpu_did').getValue())
			&& (
				LpuSectionCombo.getStore().getCount() == 0
				|| LpuSectionCombo.getStore().getAt(0).get('Lpu_id') != base_form.findField('Lpu_did').getValue()
			)
		) {
			if ( base_form.findField('Lpu_did').getValue() == getGlobalOptions().lpu_id ) {


				setLpuSectionGlobalStoreFilter({
					isOnlyStac: true,
					lpuSectionProfileCode: LpuSectionProfile_Code
				});
				LpuSectionCombo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

				if(getRegionNick() == 'penza'){
					win.changeRequireField_LpuSection_did(Lpu_IsNotForSystem, LpuSectionCombo.getStore().getCount());
				}



				setComboValue(LpuSectionCombo, LpuSection_id);
			}
			else {
				LpuSectionCombo.getStore().load({
					params: {
						mode: 'combo',
						Lpu_id: base_form.findField('Lpu_did').getValue()
					}, 
					callback: function () {

						if(getRegionNick() == 'penza') {
							// ВАЖНО: функция должна выполнять до фильтра, иначе она не видит структуру
							win.changeRequireField_LpuSection_did(Lpu_IsNotForSystem, LpuSectionCombo.getStore().getCount());
						}

                        // Фильтр
						LpuSectionCombo.getStore().filterBy(function (rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id && rec.get('LpuUnitType_SysNick') == 'stac');
						});

						setComboValue(LpuSectionCombo, LpuSection_id);

						win.refreshFieldsVisibility(['LpuUnitType_did']);
					}
				});
			}
		} else {
			LpuSectionCombo.getStore().filterBy(function (rec) {
				return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id && rec.get('LpuUnitType_SysNick') == 'stac');
			});

			setComboValue(LpuSectionCombo, LpuSection_id);
		}
	},

	refreshFieldsVisibility: function(fieldNames) {
		var win = this;
		var base_form = win.findById('EvnDirectionEditForm').getForm();
		if (typeof fieldNames == 'string') fieldNames = [fieldNames];

		var action = win.action;
		var Region_Nick = getRegionNick();

		base_form.items.each(function(field){
			if (!Ext.isEmpty(fieldNames) && !field.getName().inlist(fieldNames)) return;

			var value = field.getValue();
			var allowBlank = null;
			var enable = null;
			var visible = null;
			var filter = null;

			var EvnDirection_setDate = base_form.findField('EvnDirection_setDate').getValue();
			var EvnDirection_IsReceive = base_form.findField('EvnDirection_IsReceive').getValue();
			var Lpu_IsNotForSystem = base_form.findField('Lpu_did').getFieldValue('Lpu_IsNotForSystem');
			var DirType_Code = Number(base_form.findField('DirType_id').getFieldValue('DirType_Code'));
			var LpuUnitType_SysNick = String(base_form.findField('LpuUnitType_did').getFieldValue('LpuUnitType_SysNick'));
			var Lpu_did = base_form.findField('Lpu_did').getValue();
			var Org_oid = base_form.findField('Org_oid').getValue();
			var PurposeHospital_Code = base_form.findField('PurposeHospital_id').getFieldValue('PurposeHospital_Code');
			var MedicalCareFormType_Code = Number(base_form.findField('MedicalCareFormType_id').getFieldValue('MedicalCareFormType_Code'))

			if (!Ext.isEmpty(Org_oid)) Lpu_IsNotForSystem = 2;

			var set_or_cur_date = !Ext.isEmpty(EvnDirection_setDate)?EvnDirection_setDate:new Date(new Date().format('Y-m-d')+' 00:00');

			switch(field.getName()) {
				case 'Lpu_did':
					enable = (
						!!win.isNotForSystem ||
						!(DirType_Code.inlist([6, 13]) || EvnDirection_IsReceive == 2 || action.inlist(['editpaytype']))
					);
					if (Region_Nick.inlist(['penza'])) {
						filter = function(record) {
							var beg_date = !Ext.isEmpty(record.get('Lpu_BegDate'))?record.get('Lpu_BegDate'):null;
							if (beg_date && !(beg_date instanceof Date)) {
								beg_date = Date.parseDate(beg_date, 'd.m.Y');
							}
							var end_date = !Ext.isEmpty(record.get('Lpu_EndDate'))?record.get('Lpu_EndDate'):null;
							if (end_date && !(end_date instanceof Date)) {
								end_date = Date.parseDate(end_date, 'd.m.Y');
							}
							return (
								(!beg_date || beg_date <= set_or_cur_date) &&
								(!end_date || end_date > set_or_cur_date)
							);
						};
					}
					filter = function(record) {
						var end_date = !Ext.isEmpty(record.get('Lpu_EndDate'))?record.get('Lpu_EndDate'):null;
						if (end_date && !(end_date instanceof Date)) {
							end_date = Date.parseDate(end_date, 'd.m.Y');
						}
						return (!win.isNotForSystem || record.get('Lpu_IsNotForSystem') == 2) && (!end_date || end_date > set_or_cur_date);
					};
					visible = (DirType_Code != 26 && !win.isNotForSystem) || !Ext.isEmpty(Lpu_did);
					allowBlank = !visible;
					break;
				case 'Org_oid':
					visible = (DirType_Code == 26 || !!win.isNotForSystem) || !Ext.isEmpty(Org_oid);
					allowBlank = !visible;
					break;
				case 'Lpu_sid':
					if (EvnDirection_IsReceive == 2 && Region_Nick.inlist(['astra','ekb'])) {
						filter = function(record) {
							var end_date = !Ext.isEmpty(record.get('Lpu_EndDate'))?record.get('Lpu_EndDate'):null;
							if (end_date && !(end_date instanceof Date)) {
								end_date = Date.parseDate(end_date, 'd.m.Y');
							}
							return !end_date || end_date > set_or_cur_date;
						};
					}
					break;
				case 'MedService_id':
					visible = Ext.isEmpty(Org_oid) && !win.isNotForSystem && Lpu_IsNotForSystem != 2;
					allowBlank = !visible || !DirType_Code.inlist([16]);
					enable = !DirType_Code.inlist([6]);
					break;
				case 'LpuUnitType_did':
					visible = false;

					var LpuSectionCount = base_form.findField('LpuSection_did').getStore().getCount();

					visible = (
						(DirType_Code.inlist([1,5]) || (DirType_Code == 4 && Region_Nick == 'kz')) &&
						(Lpu_IsNotForSystem == 2 || LpuSectionCount == 0 || Region_Nick.inlist(['kz']))
					);
					if (visible) {

						filter = function(record){
							return String(record.get('LpuUnitType_SysNick')).inlist(['stac','dstac','hstac','pstac']);
						};

					}

					allowBlank = !visible;
					break;
				case 'LpuSectionProfile_id':
					allowBlank = DirType_Code == 26;
					if (Region_Nick.inlist(['astra']) && (Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid))) {
						allowBlank = !DirType_Code.inlist([13]);
					}
					else if (Region_Nick.inlist(['ekb']) && (Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid))) {
						allowBlank = !DirType_Code.inlist([1,5]) || !LpuUnitType_SysNick.inlist(['stac']);
					}
					break;
				case 'MedSpec_fid':
					if ( Region_Nick.inlist(['astra', 'msk']) ) {
						visible = (Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid)) && DirType_Code.inlist([2,3,10,13]);
						allowBlank = !(visible && DirType_Code == 13);
					}
					else if ( Region_Nick.inlist(['ekb']) ) {
						visible = (Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid)) && (
							DirType_Code.inlist([2,3,4,6,10,12,13]) ||
							DirType_Code.inlist([1]) && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])
						);
						allowBlank = !(visible && (
							DirType_Code.inlist([12,13]) ||
							DirType_Code.inlist([1]) && LpuUnitType_SysNick.inlist(['dstac','hstac','pstac'])
						));
					} else if (getRegionNick() == 'kz') {
						visible = false;
						allowBlank = true;
					}
					else {
						visible = (Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid)) && DirType_Code.inlist([2,3,10]);
						allowBlank = !visible;
					}
					break;
				case 'UslugaCategory_did':
					visible = !Ext.isEmpty(value)
						|| (
							(Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid) || Region_Nick.inlist(['kz']))
							&& (
								DirType_Code.inlist([2,9])
								|| (DirType_Code == 11 && Region_Nick.inlist(['ekb']))
								|| (DirType_Code == 13 && Region_Nick.inlist(['astra']))
							)
						);
					allowBlank = !(visible && ((getRegionNick() == 'ekb' && DirType_Code == 9) || (getRegionNick() == 'astra' && DirType_Code == 13)));
					break;
				case 'UslugaComplex_did':
					visible = !Ext.isEmpty(value)
						|| (
							(Lpu_IsNotForSystem == 2 || !Ext.isEmpty(Org_oid) || Region_Nick.inlist(['kz']))
							&& (
								DirType_Code.inlist([2,9])
								|| (DirType_Code == 11 && Region_Nick.inlist(['ekb']))
								|| (DirType_Code == 13 && Region_Nick.inlist(['astra', 'msk']))
								|| (DirType_Code.inlist([1, 3, 4, 5]) && Region_Nick.inlist(['kz']))
							)
						);
					allowBlank = !(
						visible
						&& (
							(getRegionNick() == 'ekb' && DirType_Code == 9)
							|| (getRegionNick() == 'astra' && DirType_Code == 13)
							|| (getRegionNick() == 'kz' && PurposeHospital_Code == 200)
						)
					);
					break;
				case 'MedicalCareFormType_id':
					visible = Region_Nick.inlist(['penza']) && DirType_Code.inlist([1,5]);
					allowBlank = !visible;
					break;
				case 'GetBed_id':
					visible = Region_Nick.inlist(['kz']) && DirType_Code.inlist([1,4,5]);
					allowBlank = !visible;
					break;
				case 'PayTypeKAZ_id':
				case 'TreatmentClass_id':
					visible = Region_Nick.inlist(['kz']) && !DirType_Code.inlist([1,4,5]);
					allowBlank = !visible;
					break;
				case 'EvnLinkAPP_StageRecovery':
					visible = Region_Nick.inlist(['kz']) && DirType_Code.inlist([4]);
					allowBlank = !visible;
					break;
				case 'PurposeHospital_id':
					visible = Region_Nick.inlist(['kz']) && DirType_Code.inlist([1,5]);
					allowBlank = true;
					break;
				case 'Diag_cid':
					visible = Region_Nick.inlist(['kz']) && DirType_Code.inlist([1,4,5]);
					allowBlank = true;
					break;
				case 'ScreenType_id':
					visible = Region_Nick.inlist(['kz']) && Lpu_IsNotForSystem == 2 && !this.kzScreening &&
						(base_form.findField('PayTypeKAZ_id').getValue() == 3 || base_form.findField('TreatmentClass_id').getValue() == 29);
					allowBlank = !visible;
					break;
			}

			if (visible === false) {
				value = null;
			}
			if (typeof filter == 'function' && field.store) {
				field.lastQuery = '';
				if (typeof field.setBaseFilter == 'function') {
					field.setBaseFilter(filter);
				} else {
					field.store.filterBy(filter);
				}
				if (!Ext.isEmpty(value) && field.store.find(field.store.key, value) == -1) {
					value = null;
				}
			}
			if (value != field.getValue()) {
				field.setValue(value);
				field.fireEvent('change', field, value);
			}
			if (allowBlank !== null) {
				field.setAllowBlank(allowBlank);
			}
			if (visible !== null) {
				field.setContainerVisible(visible);
			}
			if (enable !== null) {
				field.setDisabled(!enable || action == 'view');
			}
		}.createDelegate(win));

		win.syncShadow();
		win.center();
	},
	
	loadBedList: function() {
		if (getRegionNick() != 'kz') return false;
		var win = this,
			base_form = this.findById('EvnDirectionEditForm').getForm(),
			getbed_field = base_form.findField('GetBed_id');
		
		getbed_field.lastQuery = '';
		getbed_field.getStore().load({
			params: {
				Lpu_id: base_form.findField('Lpu_did').getValue() || getGlobalOptions().lpu_id,
				LpuSection_id: base_form.findField('LpuSection_did').getValue(),
				Person_id: base_form.findField('Person_id').getValue(),
				GetBed_id: win.action == 'view' ? getbed_field.getValue() : null
			},
			callback: function() {
				getbed_field.setValue(getbed_field.getValue());
			}		
		});
	},

	setDisabled: function (fl)
	{
		var bform = this.findById('EvnDirectionEditForm').getForm(),
			dirTypeField = bform.findField('DirType_id'),
			isConsultation = dirTypeField.getFieldValue('DirType_Code') == '13';
		bform.findField('EvnDirection_id').setDisabled(fl);
		bform.findField('ConsultingForm_id').setDisabled(fl);
		bform.findField('LpuSectionProfile_id').setDisabled(fl);
		bform.findField('LpuSection_did').setDisabled(fl);
		bform.findField('MedicalCareFormType_id').setDisabled(fl);
		bform.findField('StudyTarget_id').setDisabled(fl);
		bform.findField('Lpu_did').setDisabled(fl);
		bform.findField('Org_oid').setDisabled(fl);
		bform.findField('LpuUnitType_did').setDisabled(fl);
		bform.findField('UslugaCategory_did').setDisabled(fl);
		bform.findField('UslugaComplex_did').setDisabled(fl);
		bform.findField('FSIDI_id').setDisabled(fl);
		bform.findField('MedSpec_fid').setDisabled(fl);
		bform.findField('Lpu_sid').setDisabled(fl);
		bform.findField('EvnDirection_Num').setDisabled(fl);
		bform.findField('Diag_id').setDisabled(fl);
		bform.findField('EvnXml_id').setDisabled(fl);
		bform.findField('EvnDirectionOper_IsAgree').setDisabled(fl);
		bform.findField('MedStaffFact_id').setDisabled(fl);
		bform.findField('MedPersonal_Code').setDisabled(fl);
		bform.findField('MedStaffFact_zid').setDisabled(fl);
		bform.findField('EvnDirection_IsNeedOper').setDisabled(fl);
		bform.findField('EvnDirection_setDate').setDisabled(fl);
		bform.findField('EvnDirection_desDT').setDisabled(fl);
		bform.findField('EvnDirection_Descr').setDisabled(fl);
		bform.findField('EvnDirection_IsCito').setDisabled(fl);
		bform.findField('MedService_id').setDisabled(fl);
		bform.findField('Person_Surname').setDisabled(fl);
		bform.findField('Person_Firname').setDisabled(fl);
		bform.findField('Person_Secname').setDisabled(fl);
		bform.findField('Person_Birthday').setDisabled(fl);
		//bform.findField('EvnDirection_setDateTime').setDisabled(fl);
		bform.findField('DirType_id').setDisabled(fl);
		bform.findField('PayType_id').setDisabled(fl);
		bform.findField('GetBed_id').setDisabled(fl);
		//bform.findField('PayTypeKAZ_id').setDisabled(fl);
		//bform.findField('PayTypeKAZ_id').setDisabled(fl);
		bform.findField('EvnLinkAPP_StageRecovery').setDisabled(fl);
		bform.findField('PurposeHospital_id').setDisabled(fl);
		//bform.findField('ReasonHospital_id').setDisabled(fl);
		bform.findField('Diag_cid').setDisabled(fl);
		bform.findField('ScreenType_id').setDisabled(fl);
		bform.findField('TreatmentClass_id').setDisabled(fl);
		bform.findField('ConsultationForm_id').setDisabled(fl || !isConsultation);
		bform.findField('HIVContingentTypeFRMIS_id').setDisabled(true);
		bform.findField('CovidContingentType_id').setDisabled(true);
		bform.findField('HormonalPhaseType_id').setDisabled(true);
		bform.findField('RaceType_id').setDisabled(true);
	},
	printEvnDirection: function() {
		if ( this.action.inlist(['view', 'edit', 'editpaytype']) ) {
			var EvnDirection_id = this.findById('EvnDirectionEditForm').getForm().findField('EvnDirection_id').getValue();
			var DirType_Code = this.findById('EvnDirectionEditForm').getForm().findField('DirType_id').getFieldValue('DirType_Code');
			var params = {
				EvnDirection_id: EvnDirection_id
			};
			if (!Ext.isEmpty(this.PresciptionType_id)) {
				params.PresciptionType_id = this.PresciptionType_id;
			}
			if (
				getRegionNick() == 'perm' &&
				DirType_Code == 9 &&
				!Ext.isEmpty(Ext.globalOptions.lis.direction_print_form) &&
				Ext.globalOptions.lis.direction_print_form == 2
			) {
				Ext6.Ajax.request({
					url: '/?c=EvnDirection&m=getEvnDirectionForPrint',
					params: {
						EvnDirection_id: params.EvnDirection_id
					},
					callback: function (options, success, response) {
						if (success) {
							var result = Ext6.util.JSON.decode(response.responseText);
							if (!Ext.isEmpty(result.MedServiceType_SysNick) && result.MedServiceType_SysNick != 'func') {
								var birtParams = {
									'Report_FileName': 'printEvnDirectionCKDL.rptdesign',
									'Report_Params': '&paramEvnDirection=' + EvnDirection_id,
									'Report_Format': 'pdf'
								};
								printBirt(birtParams);
							} else {
								sw.Promed.Direction.print(params);
							}
						}
					}
				});
			} else {
				sw.Promed.Direction.print(params);
			}
		}
	},
	width: 700
});

