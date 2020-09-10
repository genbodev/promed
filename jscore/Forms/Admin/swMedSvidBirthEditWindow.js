/**
* swMedSvidBirthEditWindow - окно редактирования свидетельства о рождении.
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
* @comment      Префикс для id компонентов MSBEF (MedSvidBirthEditForm)
*
*/
/*NO PARSE JSON*/
sw.Promed.swMedSvidBirthEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	/* */
	codeRefresh: true,
	objectName: 'swMedSvidBirthEditWindow',
	objectSrc: '/jscore/Forms/Admin/swMedSvidBirthEditWindow.js',

	applyFilterToMedStaffFactCid: function() {
		//для Казахстана этого поля нет
		if ( getRegionNick() == 'kz' ) {
			return;
		}

		var base_form = this.findById('MedSvidBirthEditForm').getForm();

		Ext.Ajax.request({
			url: '/?c=MedSvid&m=getEmdSignatureRules',
			params: {
				'EMDPersonRole_id': 6,
				'EMDDocumentType_id': 11
			},
			callback: function(opt, success, response) {
				if (success) {

					if (!response || !response.responseText) {
						Ext.Msg.alert('Ошибка', 'Не удалось загрузить справочник правил подписания документа.');
					}

					var result = Ext.util.JSON.decode(response.responseText)[0];
					var emd_signature_rules = result['EMDSignatureRules_Post'].split(', ');

					var currentOrgHead;//активная запись руководителя
					//Узнаём индекс текущего руководителя в сторе, если поле было заполнено
					if ( base_form.findField('OrgHead_id').getValue() ) {
						var currentOrgHeadIdx = base_form.findField('OrgHead_id').getStore().findBy( function( record ) {
							if ( record.id == base_form.findField('OrgHead_id').getValue() ){
								return true;
							}
						} );
						//Если поле пустое, чистим место работы и выходим
						if ( currentOrgHeadIdx == -1 ) {
							base_form.findField('MedStaffFact_cid').getStore().removeAll();
							return;
						}
						//Получаем из сторе запись нашего руководителя
						currentOrgHead = base_form.findField('OrgHead_id').getStore().getAt( currentOrgHeadIdx );
					}

					//Получаем список мест работы
					var options = {
						'Lpu_id': getGlobalOptions().lpu_id,
						'onDate': base_form.findField('BirthSvid_GiveDate').getValue().format('d.m.Y'),
						'withoutLpuSection': true
					}

					if ( currentOrgHead ) {
						options.Person_id = currentOrgHead.data.Person_id.toString();
					}
					setMedStaffFactGlobalStoreFilter( options );

					//filter by 182221
					swMedStaffFactGlobalStore.filterBy(function(rec){
						return emd_signature_rules.indexOf(rec.get('MedPersonalPost_Code')) != -1;
					});

					base_form.findField('MedStaffFact_cid').getStore().loadData( getStoreRecords( swMedStaffFactGlobalStore ) );

					//Если место работы уже было, то проверяем есть ли оно в новом списке, если есть, оставляем, нет - сносим
					var currentMedStaffCidIdx = base_form.findField('MedStaffFact_cid').getStore().findBy( function( record ) {
						if ( record.data.MedStaffFact_id == base_form.findField('MedStaffFact_cid').getValue() ){
							return true;
						}
					} );

					//Если новый список и только одна запись, подставляем её
					if ( currentMedStaffCidIdx == -1 ) {
						if ( base_form.findField('MedStaffFact_cid').getStore().getCount() == 1 ) {
							var tmp = base_form.findField('MedStaffFact_cid').getStore().getAt( 0 );
							base_form.findField('MedStaffFact_cid').setValue( tmp.data.MedStaffFact_id );
						} else {
							base_form.findField('MedStaffFact_cid').clearValue();
						}
					} else {
						base_form.findField('MedStaffFact_cid').setValue( base_form.findField('MedStaffFact_cid').getValue() );
					}
				} else {
					Ext.Msg.alert('Ошибка', 'Не удалось загрузить справочник правил подписания документа.');
				}
			}
		});
	},

	sendToRPN: function() {
		var wnd = this;
		var base_form = wnd.findById('MedSvidBirthEditForm').getForm();

		if (getRegionNick() != 'kz') {
			return false;
		}

		var send = function() {
			wnd.getLoadMask('Отправка сидетельства о рождении в сервис РПН').show();
			var BirthSvid_id = base_form.findField('BirthSvid_id').getValue();
			Ext.Ajax.request({
				url: '/?c=ServiceRPN&m=sendBirthSvidToRPN',
				params: {BirthSvid_id: BirthSvid_id},
				success: function(response) {
					wnd.getLoadMask().hide();
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if (response_obj.BirthSvid_Num) {
						base_form.findField('BirthSvid_Num').setValue(response_obj.BirthSvid_Num);
						wnd.action = 'view';
						wnd.enableEdit(false);
						sw.swMsg.alert('Сообщение', 'Свидетельство передано в сервис РПН.');
					}
				},
				failure: function(response) {
					wnd.getLoadMask().hide();
				}
			});
		};

		sw.swMsg.show({
			title: 'Подтверждение',
			msg: 'Свидетельство будет не доступно для редактирования после отправки в РПН.',
			buttons: {ok: 'Продолжить', cancel: 'Отмена'},
			fn: function ( buttonId ) {
				if ( buttonId == 'ok' ) {
					if (wnd.action == 'add') {
						wnd.doSave({callback: send});
					} else {
						send();
					}
				}
			}
		});

		return true;
	},
	generateNewNumber: function(onlySer, afterCheckAnotherMO) {
		var win = this;

		if (win.action == 'view') {
			// генерировать новый номер надо только при добавлении
			return false;
		}

		if (getRegionNick() == 'kz') {
			//Для казахстана номер генерируется при отправке в сервис РПН
			return false;
		}

		var base_form = this.findById('MedSvidBirthEditForm').getForm();

		if (base_form.findField('ReceptType_id').getValue() != 2) {
			onlySer = true;
		}

		var LpuSection_id = this.findById('MSBEF_LpuSectionCombo').getValue();

		var params = {
			svid_type: 'birth'
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
		if (!Ext.isEmpty(base_form.findField('BirthSvid_GiveDate').getValue())) {
			params.onDate = base_form.findField('BirthSvid_GiveDate').getValue().format('d.m.Y');
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
		if (onlySer && win.lastParamsForNumGeneration == newParamsForNumGeneration && !afterCheckAnotherMO) {
			// ничего не грузим если параметры не изменились
			return false;
		}
		win.lastParamsForNumGeneration = newParamsForNumGeneration;

		if (getRegionNick() == 'kareliya' && base_form.findField('ReceptType_id').getValue() == 1) {
			// для Карелии в режиме на бланке серию подгружать не надо
			return false;
		}

		win.getLoadMask('Получение серии/номера свидетельства').show();
		Ext.Ajax.request({ //заполнение номера и серии
			callback: function (options, success, response) {
				win.getLoadMask().hide();
				if (success && response.responseText != '') {
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if (response_obj && response_obj.Error_Code && response_obj.Error_Code == 'numerator404') {
						if (getRegionNick() == 'ufa') {
							sw.swMsg.alert('Ошибка', 'Не задан активный нумератор для "Свидетельство о рождении". Обратитесь к администратору системы.');
							win.findById(win.id + 'gennewnumber').disable();
						} else {
							sw.swMsg.alert('Ошибка', 'Не задан активный нумератор для "Свидетельство о рождении", ввод свидетельств возможен в режиме "1. На бланке".');
							base_form.findField('ReceptType_id').setValue(1);
							base_form.findField('ReceptType_id').disable();
							base_form.findField('BirthSvid_Ser').setValue('');
							base_form.findField('BirthSvid_Num').setValue('');
							base_form.findField('BirthSvid_Num').enable();
							base_form.findField('BirthSvid_Ser').enable();
							win.findById(win.id + 'gennewnumber').disable();
						}
					} else {
						base_form.findField('BirthSvid_Ser').setValue('');
						base_form.findField('BirthSvid_Num').setValue('');
						base_form.findField('BirthSvid_Ser').setValue(response_obj.ser);
						if (!onlySer) {
							base_form.findField('BirthSvid_Num').setValue(response_obj.num);
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
	doSave: function(options) {
		if ( this.formStatus == 'save' || (this.action == 'view' && this.edit_poluchatel==0) ) return false;
		this.formStatus = 'save';
		var win = this;
		var this_form = this;
		var base_form = this.findById('MedSvidBirthEditForm').getForm();
		var person_frame = this.findById('MSBEF_PersonInformationFrame');
		var params = new Object();
		var callback = (options && options.callback)?options.callback:Ext.emptyFn;
		
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('MedSvidBirthEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (this.saveMode == 2) {
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение свидетельства..." });
			loadMask.show();
			// сохраняем нового получателя, остальное не меняется.
			win.getLoadMask(lang['sohranenie_dannyih_o_poluchatele']).show();
			Ext.Ajax.request({ //заполнение номера и серии
				callback: function (options, success, response) {
					this_form.formStatus = 'edit';
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if(result && result.success){
						var svid_grid = Ext.getCmp('MedSvidBirthStreamWindowSearchGrid');
						if (svid_grid && svid_grid.ViewGridStore) {
							svid_grid.ViewGridStore.reload();
						}

						Ext.getCmp('MedSvidBirthEditWindow').hide();
						var svid_id = base_form.findField('BirthSvid_id').getValue();
						sw.swMsg.show({
							title: lang['pechat_svidetelstva'],
							msg: lang['napechatat_svidetelstvo'],
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' ) {
									if(getRegionNick() == 'kz'){
										printBirt({
											'Report_FileName': 'BirthSvid_Print.rptdesign',// #133782 BirthSvid.rptdesign - BirthSvid_Print.rptdesign
											'Report_Params': '&paramBirthSvid=' + svid_id,
											'Report_Format': 'pdf'
										});
										printBirt({
											'Report_FileName': 'BirthSvid_Print_check.rptdesign',// #133782 BirthSvid_check.rptdesign - BirthSvid_Print_check.rptdesign
											'Report_Params': '&paramBirthSvid=' + svid_id,
											'Report_Format': 'pdf'
										});
									} else {
										/*var id_salt = Math.random();
										var win_id = 'print_svid' + Math.floor(id_salt * 10000);
										var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=birth', win_id);*/
										printBirt({
											'Report_FileName': 'BirthSvid_Print.rptdesign',
											'Report_Params': '&paramBirthSvid=' + svid_id,
											'Report_Format': 'pdf'
										});
									}
								}
								if (this_form.callbackAfterSave) {
									var BirthSvid_Num = base_form.findField('BirthSvid_Num').getValue();
									this_form.callbackAfterSave(svid_id, BirthSvid_Num);
								}
								callback();
							}
						});
					}else {
						if ( result.Error_Msg) {
							sw.swMsg.alert(lang['oshibka'], result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: {
					BirthSvid_id: base_form.findField('BirthSvid_id').getValue(),
					BirthSvid_Ser: base_form.findField('BirthSvid_Ser').getValue(),
					BirthSvid_Num: base_form.findField('BirthSvid_Num').getValue(),
					BirthSvid_BirthDT_Date: base_form.findField('BirthSvid_BirthDT_Date').getValue().format('d.m.Y'),
					BirthSvid_Mass: base_form.findField('BirthSvid_Mass').getValue(),
					BirthSvid_Height: base_form.findField('BirthSvid_Height').getValue(),
					Person_id: base_form.findField('Person_id').getValue(),
					Person_rid: base_form.findField('Person_rid').getValue(),
					BirthSvid_RcpDocument: base_form.findField('BirthSvid_RcpDocument').getValue(),
					DeputyKind_id: base_form.findField('DeputyKind_id').getValue(),
					BirthSvid_RcpDate: !Ext.isEmpty(base_form.findField('BirthSvid_RcpDate').getValue())?base_form.findField('BirthSvid_RcpDate').getValue().format('d.m.Y'):null
				},
				url: '/?c=MedSvid&m=saveBirthRecipient'
			});

			return true;
		}


		params.Person_id = person_frame.personId;		
		params.Person_rid = base_form.findField('Person_rid').getValue();
		params.BirthSvid_Ser = base_form.findField('BirthSvid_Ser').getValue();
		params.BirthSvid_Num = base_form.findField('BirthSvid_Num').getValue();		
		params.BirthMedPersonalType_id = base_form.findField('BirthMedPersonalType_id').getValue();
		params.ReceptType_id = base_form.findField('ReceptType_id').getValue();
		params.BirthEducation_id = base_form.findField('BirthEducation_id').getValue();
		params.BirthSvid_BirthDT_Date = base_form.findField('BirthSvid_BirthDT_Date').getValue();
		params.BirthSvid_BirthDT_Time = base_form.findField('BirthSvid_BirthDT_Time').getValue();
        params.LpuLicence_id = base_form.findField('LpuLicence_id').getValue();
		params.BirthPlace_id = base_form.findField('BirthPlace_id').getValue();
		params.Sex_id = base_form.findField('Sex_id').getValue();
		params.BirthSvid_Week = base_form.findField('BirthSvid_Week').getValue();
		params.BirthSvid_ChildCount = base_form.findField('BirthSvid_ChildCount').getValue();
		params.BirthFamilyStatus_id = base_form.findField('BirthFamilyStatus_id').getValue();
		params.BirthSvid_RcpDocument = base_form.findField('BirthSvid_RcpDocument').getValue();
		params.BirthSvid_RcpDate = base_form.findField('BirthSvid_RcpDate').getValue();
		params.BirthEmployment_id = base_form.findField('BirthEmployment_id').getValue();
		params.BirthSpecialist_id = base_form.findField('BirthSpecialist_id').getValue();		
		params.BirthSvid_ChildFamil = base_form.findField('BirthSvid_ChildFamil').getValue();
		params.BirthSvid_IsMnogoplod = base_form.findField('BirthSvid_IsMnogoplod').getValue();
		params.BirthSvid_PlodIndex = base_form.findField('BirthSvid_PlodIndex').getValue();
		params.BirthSvid_PlodCount = base_form.findField('BirthSvid_PlodCount').getValue();
		params.BirthSvid_IsFromMother = base_form.findField('BirthSvid_IsFromMother').getValue();
		params.BirthSvid_Height = base_form.findField('BirthSvid_Height').getValue();
		params.BirthSvid_GiveDate = base_form.findField('BirthSvid_GiveDate').getValue();
		params.BirthChildResult_id = base_form.findField('BirthChildResult_id').getValue();
		params.BirthSvid_Mass = base_form.findField('BirthSvid_Mass').getValue();
		params.Okei_mid = base_form.findField('Okei_mid').getValue();

		if (person_frame.getFieldValue('Sex_Code') != 2) {
			sw.swMsg.alert('Ошибка', 'Пол человека должен быть женский');
			this.formStatus = 'edit';
			return false;
		}
		
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_personal_fio = '';
		var med_personal_id = null;
		var record = null;
		
		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
			base_form.findField('MedPersonal_id').setValue(med_personal_id);
		}
		params.MedPersonal_id = med_personal_id;
		params.MedStaffFact_id = med_staff_fact_id;
		//проверки
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение свидетельства..." });
		loadMask.show();
		
		var errMsg = "";
		
		
		
		if (errMsg == "") {
			var submit_object = {
				failure: function(result_form, action) {
					this_form.formStatus = 'edit';
					loadMask.hide();

					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this_form),
				params: params,
				success: function(result_form, action) {
					this_form.formStatus = 'edit';
					loadMask.hide();
					
					var svid_grid = Ext.getCmp('MedSvidBirthStreamWindowSearchGrid');
					if (svid_grid && svid_grid.ViewGridStore) {
						svid_grid.ViewGridStore.reload();
					}
					
					Ext.getCmp('MedSvidBirthEditWindow').hide();
					var svid_id = action.result.svid_id;
					base_form.findField('BirthSvid_id').setValue(svid_id);
					sw.swMsg.show({
						title: lang['pechat_svidetelstva'],
						msg: lang['napechatat_svidetelstvo'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								if(getRegionNick() == 'kz'){
                                	printBirt({
										'Report_FileName': 'BirthSvid_Print.rptdesign',// #133782
										'Report_Params': '&paramBirthSvid=' + svid_id,
										'Report_Format': 'pdf'
									});
									printBirt({
										'Report_FileName': 'BirthSvid_Print_check.rptdesign',// #133782
										'Report_Params': '&paramBirthSvid=' + svid_id,
										'Report_Format': 'pdf'
									});
                                } else {
									/*var id_salt = Math.random();
									var win_id = 'print_svid' + Math.floor(id_salt * 10000);
									var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=birth', win_id);*/
									printBirt({
										'Report_FileName': 'BirthSvid_Print.rptdesign',
										'Report_Params': '&paramBirthSvid=' + svid_id,
										'Report_Format': 'pdf'
									});
								}
							}
							if (this_form.callbackAfterSave) {
								var BirthSvid_Num = base_form.findField('BirthSvid_Num').getValue();
								this_form.callbackAfterSave(svid_id, BirthSvid_Num);
							}
							callback();
						}
					});					
				}.createDelegate(this_form)
			};
		
			if(params.BirthSvid_IsMnogoplod != 2) { //если не многоплодные роды
				Ext.Ajax.request({ //проверка на наличие заявления
					callback: function(options, success, response) {
						loadMask.hide();
						if (success && response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if (response_obj.cnt > 0) {
								sw.swMsg.show({
									buttons: Ext.Msg.YESNO,
									fn: function(buttonId, text, obj) {
										if ( buttonId == 'yes' ) {
											this_form.formStatus = 'edit';
										}
										if ( buttonId == 'no' ) {
											base_form.submit(submit_object);
										}
									},
									icon: Ext.MessageBox.QUESTION,
									msg: lang['svidetelstvo_na_dannogo_cheloveka_zavedeno_eto_vozmojno_po_prichine_rojdeniya_dvoyni_ostanovit_sohranenie_i_vernutsya_k_redaktirovaniyu_svidetelstva'],
									title: lang['vopros']
								});
							} else {
								base_form.submit(submit_object);
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_cohranenii_svidetelstva_proizoshla_oshibka']);
						}
					},
					params: {
						BirthSvid_id: base_form.findField('BirthSvid_id').getValue(),
						Person_id: params.Person_id
					},
					url: '/?c=MedSvid&m=getDoubleBirthSvidCnt'
				});	
			} else {
				base_form.submit(submit_object);
			}
		
			/*base_form.submit({
				failure: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();
					Ext.getCmp('MedSvidBirthStreamWindowSearchGrid').ViewGridStore.reload();
					Ext.getCmp('MedSvidBirthEditWindow').hide();
					svid_id = action.result.svid_id;
					sw.swMsg.show({
						title: lang['pechat_svidetelstva'],
						msg: lang['napechatat_svidetelstvo'],
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' ) {
								//var id_salt = Math.random();
								//var win_id = 'print_svid' + Math.floor(id_salt * 10000);
								//var win = window.open('/?c=MedSvid&m=printMedSvid&svid_id=' + svid_id + '&svid_type=birth', win_id);
								printBirt({
									'Report_FileName': 'BirthSvid_Print.rptdesign',
									'Report_Params': '&paramBirthSvid=' + svid_id,
									'Report_Format': 'pdf'
								});
							}
						}
					});					
				}.createDelegate(this)
			});*/
		} else {
			sw.swMsg.alert(lang['oshibka'], errMsg);
		}
	},
	draggable: true,
	formStatus: 'edit',
	height: 500,
	id: 'MedSvidBirthEditWindow',
	initComponent: function() {
		var win = this;
		var label_mod_1 = -10; //страница 1, модификатор ширины названий полей		
		var field_mod_1 = -22; //страница 1, модификатор ширины полей
	
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave({
						checkDrugRequest: true,
						copy: false,
						print: false
					});
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EREF + 31,
				text: BTN_FRMSAVE,
				tooltip: lang['sohranit_vvedennyie_dannyie']
			}, {
				id: 'MSBEW_SendToRPNButton',
				handler: function() {
					this.sendToRPN();
				}.createDelegate(this),
				iconCls: '',
				tabIndex: TABINDEX_EREF + 31,
				text: 'Отправить в РПН'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',				
				tabIndex: TABINDEX_EREF + 32,
				text: BTN_FRMCANCEL,
				tooltip: lang['zakryit_okno']
			}],
			items: [ new sw.Promed.PersonInformationPanel({
				button2Callback: function(callback_data) {
					var base_form = this.findById('MedSvidBirthEditForm').getForm();

					/*base_form.findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
					base_form.findField('Server_id').setValue(callback_data.Server_id);*/

					this.findById('MSBEF_PersonInformationFrame').load({
						Person_id: callback_data.Person_id,
						Server_id: callback_data.Server_id
					});
				}.createDelegate(this),
				button1OnHide: function() {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					} else {
						this.findById('MedSvidBirthEditForm').getForm().findField('ReceptType_id').focus(true);
					}
				}.createDelegate(this),
				button2OnHide: function() {
					this.findById('MSBEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button3OnHide: function() {
					this.findById('MSBEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button4OnHide: function() {
					this.findById('MSBEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				button5OnHide: function() {
					this.findById('MSBEF_PersonInformationFrame').button1OnHide();
				}.createDelegate(this),
				id: 'MSBEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				//bodyStyle: 'padding: 0.5em;',
				frame: true,					
				labelAlign: 'right',					
				labelWidth: 150 + label_mod_1,
				bodyStyle:'background:#FFFFFF;padding:0px;',
				border: false,
				frame: false,
				layout: 'anchor',
				id: 'MedSvidBirthEditForm',
				items: [{
					name: 'BirthSvid_id',
					id: 'BirthSvid_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					id: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Person_cid',
					id: 'Person_cid',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					id: 'Server_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'BAddress_id',
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
					name: 'BirthSvid_IsBad',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'BirthSvid_isInRpn',
					value: 0,
					xtype: 'hidden'
				},
				new Ext.TabPanel({
					id: 'MedSvidBirthEditWindowTab',
					activeTab: 0,
					autoScroll: true,
					autoHeight: true,
					bodyStyle:'padding:5px;',
					layoutOnTabChange: true,
					border: false,				
					items: [{
						title: lang['0_svedeniya_o_materi_i_rebenke'],						
						labelWidth: 150 + label_mod_1,												
						border: false,
						autoHeight: true,
						items: [{ //Тип свидетельства;
								layout: 'column',
								border: false,
								autoHeight: true,								
								items: [{
									layout: 'form',
									border: false,
									items: [new sw.Promed.SwReceptTypeCombo({
										allowBlank: false,
										fieldLabel: lang['tip_svidetelstva'],
										id: 'SvidType_id',
										listWidth: 200 + field_mod_1,
										width: 200 + field_mod_1,
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
								},{
									layout: 'form',
									border: false,
									hidden: getRegionNick() != 'ufa' || !isUserGroup( 'MedSvidDeath' ),
									items: [ {
										fieldLabel: langs('Выписано за другую МО'),
										xtype: 'checkbox',
										name: 'BirthSvid_IsOtherMO',
										handler: function(){
											var our_form = win.findById('MedSvidBirthEditForm').getForm();
											var recipient = [ 'Person_rid', 'BirthSvid_RcpDocument', 'DeputyKind_id', 'BirthSvid_RcpDate' ];

											if ( this.checked && getRegionNick() == 'ufa' ){
												//МО выдачи  свидетельства о рождении
												win.findById( 'AnotherMOPlace_id' ).setContainerVisible( true );
												win.findById( 'AnotherMOPlace_id' ).setAllowBlank( false );

												//Тип свидетельства
												our_form.findField('SvidType_id').disable();
												our_form.findField('SvidType_id').setValue( 2 );

												//Серия
												our_form.findField('BirthSvid_Ser').enable();
												our_form.findField('BirthSvid_Ser').setValue( '' );
												our_form.findField('BirthSvid_Ser').setAllowBlank(false);

												//Номер
												our_form.findField('BirthSvid_Num').enable();
												our_form.findField('BirthSvid_Num').setValue( '' );
												our_form.findField('BirthSvid_Num').setAllowBlank(false);
												win.findById(win.id + 'gennewnumber').disable();

												//Записано со слов матери
												our_form.findField('BirthSvid_IsFromMother').disable();

												//Врач
												if ( sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.MedStaffFact_id ){
													our_form.findField('MSBEF_MedPersonalCombo').setValue( sw.Promed.MedStaffFactByUser.current.MedStaffFact_id );
												} else if ( sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.last.MedStaffFact_id ){
													our_form.findField('MSBEF_MedPersonalCombo').setValue( sw.Promed.MedStaffFactByUser.last.MedStaffFact_id );
												}

												//отделение
												if ( !our_form.findField('MSBEF_LpuSectionCombo').getValue() ){
													if ( sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.current.LpuSection_id ){
														our_form.findField('MSBEF_LpuSectionCombo').setValue( sw.Promed.MedStaffFactByUser.current.LpuSection_id );
													} else if ( sw.Promed.MedStaffFactByUser.current && sw.Promed.MedStaffFactByUser.last.LpuSection_id ){
														our_form.findField('MSBEF_LpuSectionCombo').setValue( sw.Promed.MedStaffFactByUser.last.LpuSection_id );
													}
												}

												//принял роды
												our_form.findField('BirthSpecialist_id').setValue( 1 );
												our_form.findField('BirthSpecialist_id').disable();

												//получатель
												recipient.forEach(function( item, i, recipient ) {
													our_form.findField( item ).disable();
													( item == 'BirthSvid_RcpDate' || 'DeputyKind_id' )?( our_form.findField( item ).setValue( '' ) ):( our_form.findField( item ).clearValue() );
												});
												our_form.findField( 'BirthSvid_RcpDate' ).setAllowBlank( true );

											} else {
												//МО выдачи  свидетельства о рождении
												win.findById( 'AnotherMOPlace_id' ).setContainerVisible( false );
												win.findById( 'AnotherMOPlace_id' ).setAllowBlank( true );
												//Тип свидетельства
												our_form.findField('SvidType_id').enable();

												//Серия
												win.getLoadMask(langs('Проверка наличия нумераторов на структуре МО')).show();
												Ext.Ajax.request({
													url: '/?c=Numerator&m=checkNumeratorOnDateWithStructure',
													params: {
														onDate: win.findById('MedSvidBirthEditForm').getForm().findField('BirthSvid_GiveDate').getValue().format('d.m.Y'),
														NumeratorObject_SysName: 'BirthSvid'
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

														win.generateNewNumber(true, true);
													}
												});
												our_form.findField('BirthSvid_Ser').disable();

												//Номер
												//энаблится в generateNewNumber

												//Записано со слов матери
												our_form.findField('BirthSvid_IsFromMother').enable();

												//Врач
												our_form.findField('MSBEF_MedPersonalCombo').clearValue();

												//отделение
												our_form.findField('MSBEF_LpuSectionCombo').clearValue();

												//принял роды
												our_form.findField('BirthSpecialist_id').enable();

												//получатель
												recipient.forEach(function( item, i, recipient ) {
													our_form.findField( item ).enable();
												});
												our_form.findField( 'BirthSvid_RcpDate' ).setAllowBlank( false );
											}
										},
										checked: 'false',
										id: 'BirthSvid_IsOtherMO',
									}]
								}]
							}, { //МО выдачи  свидетельства о рождении
							layout: 'column',
							border: false,
							items: [{
								layout: 'form',
								border: false,
								items: [
									{
										xtype: 'hidden',
										name: 'Org_id',
										id: 'Org_id_id'
									},
									new Ext.form.TwinTriggerField ({
										id: 'AnotherMOPlace_id',
										readOnly: true,
										width: 910 + (field_mod_1*3) + (label_mod_1*2),
										trigger1Class: 'x-form-search-trigger',
										trigger2Class: 'x-form-clear-trigger',
										fieldLabel: langs('МО выдачи  свидетельства о рождении'),
										tabIndex: TABINDEX_EREF + 25,
										enableKeyEvents: true,
										onTrigger2Click: Ext.EmptyFn,
										onTrigger1Click: function() {
											getWnd('swOrgSearchWindow').show({
												onSelect: function ( test ) {
													win.findById('AnotherMOPlace_id').setValue( test.Org_Name );
													win.findById('Org_id_id').setValue( test.Org_id );
												}
											});
										}
									})
								]
							}]}, { //Серия; Номер; Дата выдачи;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: true,
										fieldLabel: lang['seriya'],
										maxLength: 20,
										id: 'BirthSvid_Ser',
										name: 'BirthSvid_Ser',
										tabIndex: TABINDEX_EREF + 2,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										layout: 'column',
										border: false,
										autoHeight: true,
										items: [{
											layout: 'form',
											border: false,
											items: [{
												allowBlank: false,
												fieldLabel: lang['nomer'],
												maxLength: 20,
												id: 'BirthSvid_Num',
												name: 'BirthSvid_Num',
												tabIndex: TABINDEX_EREF + 3,
												width: 170 + field_mod_1,
												value: '', //default value
												xtype: 'textfield'
											}]
										}, {
											layout: 'form',
											border: false,
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
								}, {
									layout: 'form',
									border: false,
									//columnWidth: .33,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['data_vyidachi'],
										format: 'd.m.Y',
										name: 'BirthSvid_GiveDate',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_EREF + 4,
										width: 200 + field_mod_1,
										value: new Date(), //default value
										xtype: 'swdatefield',
										listeners: {
											'change': function(field, newValue, oldValue) {
												var base_form = this.findById('MedSvidBirthEditForm').getForm();

												if (!Ext.isEmpty(base_form.findField('BirthSvid_GiveDate').getValue())) {
													if (win.action != 'view' && getRegionNick() != 'kz') {
														// проверяем, есть ли нумераторы действующие на дату выдачи, у которых заполнена структура
														win.getLoadMask(lang['proverka_nalichiya_numeratorov_na_strukture_mo']).show();
														Ext.Ajax.request({
															url: '/?c=Numerator&m=checkNumeratorOnDateWithStructure',
															params: {
																onDate: base_form.findField('BirthSvid_GiveDate').getValue().format('d.m.Y'),
																NumeratorObject_SysName: 'BirthSvid'
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
												var birth_svid_rcp_date = base_form.findField('BirthSvid_RcpDate').getValue();
												
												var section_filter_params = {
													// TO-DO: ну это тоже не правильно, надо сделать правильную фильтрацию по нескольким признакам, хотя можно сделать и isPolkaandStac ))
													Lpu_id: getGlobalOptions().lpu_id
												};									
												var medstafffact_filter_params = {
													// TO-DO: ну это тоже не правильно, надо сделать правильную фильтрацию по нескольким признакам, хотя можно сделать и isPolkaandStac ))
													allowDuplacateMSF: true,
													Lpu_id: getGlobalOptions().lpu_id
												};
												
												if (newValue)  {
													section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
													if ( birth_svid_rcp_date == '' || Ext.util.Format.date(birth_svid_rcp_date, 'd.m.Y') == Ext.util.Format.date(oldValue, 'd.m.Y') ) {
														base_form.findField('BirthSvid_RcpDate').setValue(newValue);
													}

													win.applyFilterToMedStaffFactCid();
												}
												
												var user_med_staff_fact_id = null; //this.UserMedStaffFact_id;
												var user_lpu_section_id = null; //this.UserLpuSection_id;
												var user_med_staff_facts = !isSuperAdmin() && typeof getGlobalOptions().medstafffact == 'object' && getGlobalOptions().medstafffact.length > 0 ? getGlobalOptions().medstafffact : null; //this.UserMedStaffFacts;
												var user_lpu_sections = !isSuperAdmin() && typeof getGlobalOptions().lpusection == 'object' && getGlobalOptions().lpusection.length > 0 ? getGlobalOptions().lpusection : null; //this.UserLpuSections;
																														
												// фильтр или на конкретное место работы или на список мест работы
												if ( user_med_staff_fact_id && user_lpu_section_id && (this.action == 'add' || this.action == 'edit') ) {
													section_filter_params.id = user_lpu_section_id;
													medstafffact_filter_params.id = user_med_staff_fact_id;
												} else
													if ( user_med_staff_facts && user_lpu_sections && (this.action == 'add' || this.action == 'edit') ) {
														section_filter_params.ids = user_lpu_sections;
														medstafffact_filter_params.ids = user_med_staff_facts;
													}

												base_form.findField('LpuSection_id').clearValue();
												base_form.findField('MedStaffFact_id').clearValue();

												if (win.action != 'view') {
													if ((medstafffact_filter_params.id && medstafffact_filter_params.id > 0) || (medstafffact_filter_params.ids && medstafffact_filter_params.ids.length > 0)) {
														//
													} else {
														medstafffact_filter_params.id = null;
														medstafffact_filter_params.ids = null;
													}

													setLpuSectionGlobalStoreFilter(section_filter_params);
													setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
												} else {
													// если просмотр, то подгружаем всех врачей
													setLpuSectionGlobalStoreFilter();
													setMedStaffFactGlobalStoreFilter();
												}

												base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
												base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

												index = base_form.findField('MedStaffFact_id').getStore().findBy(function (record, id) {
													if ( !Ext.isEmpty(med_staff_fact_id) ) {
														return (record.get('MedStaffFact_id') == med_staff_fact_id);
													}
													else {
														return (record.get('LpuSection_id') == lpu_section_id && record.get('MedPersonal_id') == med_personal_id);
													}
												});

												if ( index >= 0 ) {
													med_staff_fact_id = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
												}
												
												if ( !Ext.isEmpty(lpu_section_id) ) {
													if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
														base_form.findField('LpuSection_id').setValue(lpu_section_id);
														base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
													}
													else
													{
														base_form.findField('LpuSection_id').getStore().load({
															callback: function() {
																index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
																	if ( rec.get('LpuSection_id') == lpu_section_id ) {
																		return true;
																	}
																	else {
																		return false;
																	}
																});

																if ( index >= 0 ) {
																	base_form.findField('LpuSection_id').setValue(base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
																	//base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id'));
																}
															}.createDelegate(this),
															params: {
																mode: 'combo',
																Lpu_id: getGlobalOptions().lpu_id,
																LpuSection_id: lpu_section_id,
																fromMZ: '2'
															}
														});
													}
												}

												if ( !Ext.isEmpty(med_staff_fact_id) ) {
													if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
														base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
													}
													else
													{
														base_form.findField('MedStaffFact_id').getStore().load({
															callback: function() {
																index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
																	if (med_staff_fact_id) {
																		return (rec.get('MedStaffFact_id') == med_staff_fact_id);
																	} else {
																		return (rec.get('MedPersonal_id') == med_personal_id && rec.get('LpuSection_id') == lpu_section_id);
																	}
																});

																if ( index >= 0 ) {
																	base_form.findField('MedStaffFact_id').setValue(base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id'));
																	base_form.findField('MedStaffFact_id').fireEvent('change', base_form.findField('MedStaffFact_id'), base_form.findField('MedStaffFact_id').getValue());
																	base_form.findField('MedStaffFact_id').validate();
																}
															}.createDelegate(this),
															params: {
																mode: 'combo',
																Lpu_id: getGlobalOptions().lpu_id,
																LpuSection_id: lpu_section_id,
																MedPersonal_id: med_personal_id
															}
														});
													}
												}

												/*
													если форма отурыта на добавление и задано отделение и 
													место работы, то устанавливаем их не даем редактировать вообще
												*/
												if ((this.action == 'add' || this.action == 'edit') && user_med_staff_fact_id && user_lpu_section_id) {
													base_form.findField('LpuSection_id').setValue(user_lpu_section_id);
													base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), user_lpu_section_id);
													base_form.findField('LpuSection_id').disable();
													base_form.findField('MedStaffFact_id').setValue(user_med_staff_fact_id);
													base_form.findField('MedStaffFact_id').disable();
													
												} else {
													/*
													 если форма открыта на добавление и задан список отделений и
													 мест работы, но он состоит из одного элемета,
													 то устанавливаем значение и не даем редактировать
													 */
													if ((this.action == 'add' || this.action == 'edit') && user_med_staff_facts && user_med_staff_facts.length == 1) {
														// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
														base_form.findField('LpuSection_id').setValue(user_lpu_sections[0]);
														base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), user_lpu_sections[0]);
														base_form.findField('LpuSection_id').disable();
														base_form.findField('MedStaffFact_id').setValue(user_med_staff_facts[0]);
														base_form.findField('MedStaffFact_id').disable();
													}
												}
											}.createDelegate(this)
										}
									}]
								}]
							}, { //Занятость; Образование; Семейное положение;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['zanyatost'],
										comboSubject: 'BirthEmployment',
										hiddenName: 'BirthEmployment_id',
										tabIndex: TABINDEX_EREF + 5,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swcustomsvidcombo'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['obrazovanie'],
										hiddenName: 'BirthEducation_id',
										comboSubject: 'BirthEducation',
										tabIndex: TABINDEX_EREF + 6,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swcustomsvidcombo'
									}]
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									allowBlank: getRegionNick() != 'ufa',
									comboSubject: 'BirthFamilyStatus',
									fieldLabel: lang['semeynoe_polojenie'],
									hiddenName: 'BirthFamilyStatus_id',
									listWidth: 400,
									tabIndex: TABINDEX_EREF + 7,
									width: 200 + field_mod_1,
									xtype: 'swcommonsprcombo'
								}]
							}, { //Отделение; Врач; Вид мед. перс.;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										hiddenName: 'LpuSection_id',
										id: 'MSBEF_LpuSectionCombo',
										changeDisabled: false,
										lastQuery: '',
										listWidth: 650,
										linkedElements: [
											'MSBEF_MedPersonalCombo'
										],
										listeners: {
											'change': function (combo, newValue, oldValue) {
												if (win.needLpuSectionForNumGeneration) {
													this.generateNewNumber(true);
												}
											}.createDelegate(this)
										},
										tabIndex: TABINDEX_EREF + 8,
										width: 200 + field_mod_1,
										xtype: 'swlpusectionglobalcombo'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										hiddenName: 'MedStaffFact_id',
										id: 'MSBEF_MedPersonalCombo',
										lastQuery: '',
										listWidth: 650,
										parentElementId: 'MSBEF_LpuSectionCombo',
										listeners: {
											'change': function (combo, newValue, oldValue) {
												if (win.needLpuSectionForNumGeneration) {
													this.generateNewNumber(true);
												}
											}.createDelegate(this),
										},
										tabIndex: TABINDEX_EREF + 9,
										width: 200 + field_mod_1,
										value: null,
										xtype: 'swmedstafffactglobalcombo'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['vid_med_personala'],
										hiddenName: 'BirthMedPersonalType_id',
										comboSubject: 'BirthMedPersonalType',
										tabIndex: TABINDEX_EREF + 10,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swcustomsvidcombo'
									}]
								}]
							}, { // Руководитель
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										hiddenName: 'OrgHead_id',
										id: 'MSBEF_OrgHeadCombo',
										lastQuery: '',
										listWidth: 650,
										tabIndex: TABINDEX_EREF + 9,
										width: 200 + field_mod_1,

										//parentElementId: 'MSBEF_MedStaffFactCidCombo',

										value: null,
										xtype: 'orgheadcombo',


										listeners: {
											'render': function(combo) {
												var params = new Object();
												params.fromMZ = '1';
												params.Lpu_id = getGlobalOptions().lpu_id;
												if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
												{
													params.fromMZ = '2';
												}
												combo.getStore().load({
													params: params
												})
											},
											'select': function(combo, record) {
												win.applyFilterToMedStaffFactCid();
											},
											'change': function ( combo, record ) {
												win.applyFilterToMedStaffFactCid();
											}
										}
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										hiddenName: 'MedStaffFact_cid',
										id: 'MSBEF_MedStaffFactCidCombo',
										fieldLabel: langs('Место работы врача («Руководитель»)'),
										lastQuery: '',
										listWidth: 650,
										codeField: null,
										displayField: 'MedStaffFactCidDispaly',
										tpl: new Ext.XTemplate(
											'<tpl for="."><div class="x-combo-list-item"><table>',
											'<tr>{MedPersonal_Fio} {[!Ext.isEmpty(values.LpuSection_Name) ? values.LpuSection_Name : ""]} {PostMed_Name}, ст. {MedStaffFact_Stavka}, Дата начала работы: {WorkData_begDate}</tr>',
											'</table></div></tpl>'
										),
										tabIndex: TABINDEX_EREF + 9,
										width: 200 + field_mod_1,
										value: null,
										xtype: 'swmedstafffactglobalcombo'
									}]
								}, {
                                    layout:'form',
                                    border: false,
                                    items:[{
                                        allowBlank: false,
                                        hiddenName: 'LpuLicence_id',
                                        displayField: 'LpuLicence_Num',
                                        fieldLabel: lang['litsenziya'],
                                        valueField: 'LpuLicence_id',
										width: 200 + field_mod_1,
                                        xtype: 'swbaselocalcombo',
                                        tpl: new Ext.XTemplate(
                                            '<tpl for="."><div class="x-combo-list-item">',
                                            '<table style="border: 0;">',
                                            '<td style="font-weight: bold;">&nbsp;{LpuLicence_Num}&nbsp;</td>',
                                            '</tr></table>',
                                            '</div></tpl>'
                                        ),
                                        store: new Ext.data.Store({
                                            autoLoad: false,
                                            reader: new Ext.data.JsonReader({
                                                id: 'LpuLicence_id'
                                            }, [
                                                { name: 'LpuLicence_id', mapping: 'LpuLicence_id' },
                                                { name: 'LpuLicence_Num', mapping: 'LpuLicence_Num' }
                                            ]),
                                            url: '/?c=MedSvid&m=getLpuLicence'
                                        }),
                                        listeners: {
                                        	'render': function(combo){
                                        		var params = new Object();
                                        		params.fromMZ = '1';
												if(getWnd('swWorkPlaceMZSpecWindow').isVisible())
												{
													params.fromMZ = '2';
												}
                                        		combo.getStore().load({
                                        			params: params
                                        		});
                                        	}
                                        }
                                    }]
                                }]
							}, { //Дата, время родов; Место родов; Принял роды; 
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['data_vremya_rodov'],
										format: 'd.m.Y',									
										name: 'BirthSvid_BirthDT_Date',
										plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
										tabIndex: TABINDEX_EREF + 11,
										width: 134 + field_mod_1,
										value: '', //default value
										xtype: 'swdatefield',
										listeners: {
											'change': function(combo, newValue, oldValue) {
												blockedDateAfterPersonDeath('personpanelid', 'MSBEF_PersonInformationFrame', combo, newValue, oldValue);
                                                var base_form = this.findById('MedSvidBirthEditForm').getForm();
                                                var lpu_licence_combo = base_form.findField('LpuLicence_id');
                                                lpu_licence_combo.getStore().load({
                                                    params: {
                                                        svidDate: newValue
                                                    }
                                                });
											}.createDelegate(this)
										}							
									}]
								}, {
									layout: 'form',
									border: false,
									labelWidth: 1,
									items: [{
										allowBlank: false,
										disabled: false,
										labelSeparator: '',
										format: 'H:i',
										name: 'BirthSvid_BirthDT_Time',
										//onTriggerClick: Ext.emptyFn,
										plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
										tabIndex: TABINDEX_EREF + 12,
										validateOnBlur: false,
										width: 60,
										value: '', //default value
										listeners: {
											'keydown': function (inp, e) {
												if ( e.getKey() == Ext.EventObject.F4 ) {
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
								//	labelWidth: 84 + label_mod_1,
									items: [new sw.Promed.SwCustomSvidCombo({
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['mesto_rodov'],
										comboSubject: 'BirthPlace',
										hiddenName: 'BirthPlace_id',
										tabIndex: TABINDEX_EREF + 13,
										width: 200 + field_mod_1,
										value: 1
									})]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['prinyal_rodyi'],
										comboSubject: 'BirthSpecialist',
										hiddenName: 'BirthSpecialist_id',
										tabIndex: TABINDEX_EREF + 14,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swcustomsvidcombo'
									}]
								}]
							}, { //Многоплодные роды; Который по счету ребенок; Всего плодов;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['mnogoplodnyie_rodyi'],
										maxLength: 20,
										hiddenName: 'BirthSvid_IsMnogoplod',
										tabIndex: TABINDEX_EREF + 15,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swyesnocombo',
										listeners: {
											'select': function(combo, record, index) {									
												if (record.get(combo.valueField)) {
													var mprtype = record.get(combo.valueField);
													Ext.getCmp('BirthSvid_PlodIndex').setAllowBlank(true);
													Ext.getCmp('BirthSvid_PlodCount').setAllowBlank(true);
													if (mprtype == 2) {
														Ext.getCmp('BirthSvid_PlodIndex').enable();
														Ext.getCmp('BirthSvid_PlodCount').enable();
														if (getRegionNick() == 'ufa') {
															Ext.getCmp('BirthSvid_PlodIndex').setAllowBlank(false);
															Ext.getCmp('BirthSvid_PlodCount').setAllowBlank(false);
															Ext.getCmp('BirthSvid_PlodIndex').setValue('');
															Ext.getCmp('BirthSvid_PlodCount').setValue('');
														}
													} else {
														Ext.getCmp('BirthSvid_PlodIndex').disable();
														Ext.getCmp('BirthSvid_PlodCount').disable();
														if (getRegionNick() == 'ufa') {
															Ext.getCmp('BirthSvid_PlodIndex').setValue(1);
															Ext.getCmp('BirthSvid_PlodCount').setValue(1);
														} else {
															Ext.getCmp('BirthSvid_PlodIndex').setValue('');
															Ext.getCmp('BirthSvid_PlodCount').setValue('');
														}
													}
												}
											}
										}
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: true,	
										disabled: true,
										fieldLabel: lang['kotoryiy_po_schetu'],
										maxLength: 20,
										id: 'BirthSvid_PlodIndex',
										name: 'BirthSvid_PlodIndex',
										tabIndex: TABINDEX_EREF + 16,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: true,	
										disabled: true,
										fieldLabel: lang['vsego_plodov'],
										maxLength: 20,
										id: 'BirthSvid_PlodCount',
										name: 'BirthSvid_PlodCount',
										tabIndex: TABINDEX_EREF + 17,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'textfield'
									}]
								}]
							}, { //Ребенок родился; Который ребенок; Первая явка, нед; 
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['rebenok_rodilsya'],
										comboSubject: 'BirthChildResult',
										hiddenName: 'BirthChildResult_id',
										tabIndex: TABINDEX_EREF + 18,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swcustomsvidcombo'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['kotoryiy_rebenok'],
										maxLength: 20,
										name: 'BirthSvid_ChildCount',
										tabIndex: TABINDEX_EREF + 19,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'textfield'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['pervaya_yavka_nedelya'],
										maxLength: 20,
										name: 'BirthSvid_Week',
										tabIndex: TABINDEX_EREF + 20,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'textfield'
									}]
								}]
							}, { //Масса (г); Рост (см); Пол;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [
									{
										layout: 'column',
										width: 350,
										border: false,
										items: [
											{
												labelAlign: 'right',
												layout: 'form',
												border: false,
												items: [
													{
														allowBlank: false,
														allowNegative: false,
														disabled: false,
														fieldLabel: lang['massa'],
														maxLength: 20,
														name: 'BirthSvid_Mass',
														tabIndex: TABINDEX_EREF + 21,
														width: 98,
														value: '', //default value
														sabelStyle: 'text-align: right',
														xtype: 'numberfield'
													}
												]
											},
											{
												layout: 'form',
												border: false,
												items: [
													{
														hideLabel: true,
														allowBlank: false,
														hiddenName: 'Okei_mid',
														width: 80,
														value: 37,
														tabIndex: TABINDEX_PWEF + 4,
														loadParams: {params: {where: ' where Okei_id in (36,37)'}},
														xtype: 'swokeicombo'
													}
												]
											}
										]
									},
									{
									layout: 'form',
									border: false,
									labelWidth: 123 + label_mod_1,
									items: [{
										allowBlank: false,
										allowDecimals: false,
										allowNegative: false,
										disabled: false,
										fieldLabel: lang['rost_sm'],
										maxLength: 20,
										name: 'BirthSvid_Height',
										tabIndex: TABINDEX_EREF + 22,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'numberfield'
									}]
								}, {
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										fieldLabel: lang['pol'],
										hiddenName: 'Sex_id',
										tabIndex: TABINDEX_EREF + 23,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swpersonsexcombo'
									}]
								}]
							}, { //Фамилия ребенка;
								layout: 'column',
								border: false,
								autoHeight: true,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['familiya_rebenka'],
										maxLength: 20,
										name: 'BirthSvid_ChildFamil',
										tabIndex: TABINDEX_EREF + 24,
										width: 200 + field_mod_1,
										value: '', //default value
										xtype: 'textfield'
									}]
								}]
							}, { //Место рождения;
								layout: 'column',
								border: false,
								items: [{
									layout: 'form',
									border: false,
									items: [
										{
											xtype: 'hidden',
											name: 'BAddress_Zip',
											id: 'MSBEW_BAddress_Zip'
										}, {
											xtype: 'hidden',
											name: 'BKLCountry_id',
											id: 'MSBEW_BKLCountry_id'
										}, {
											xtype: 'hidden',
											name: 'BKLRGN_id',
											id: 'MSBEW_BKLRGN_id'
										}, {
											xtype: 'hidden',
											name: 'BKLSubRGN_id',
											id: 'MSBEW_BKLSubRGN_id'
										}, {
											xtype: 'hidden',
											name: 'BKLCity_id',
											id: 'MSBEW_BKLCity_id'
										}, {
											xtype: 'hidden',
											name: 'BKLTown_id',
											id: 'MSBEW_BKLTown_id'
										}, {
											xtype: 'hidden',
											name: 'BKLStreet_id',
											id: 'MSBEW_BKLStreet_id'
										}, {
											xtype: 'hidden',
											name: 'BAddress_House',
											id: 'MSBEW_BAddress_House'
										}, {
											xtype: 'hidden',
											name: 'BAddress_Corpus',
											id: 'MSBEW_BAddress_Corpus'
										}, {
											xtype: 'hidden',
											name: 'BAddress_Flat',
											id: 'MSBEW_BAddress_Flat'
										}, {
											xtype: 'hidden',
											name: 'BAddress_Address',
											id: 'MSBEW_BAddress_Address'
										},
										new Ext.form.TwinTriggerField ({
											//xtype: 'trigger',
											name: 'BAddress_AddressText',
											id: 'MSBEW_BAddress_AddressText',
											allowBlank: false,
											readOnly: true,
											width: 910 + (field_mod_1*3) + (label_mod_1*2),
											trigger1Class: 'x-form-search-trigger',
											//trigger2Class: 'x-form-equil-trigger',
											trigger2Class: 'x-form-clear-trigger',
											fieldLabel: lang['mesto_rojdeniya'],
											tabIndex: TABINDEX_EREF + 25,
											enableKeyEvents: true,
											listeners: {
												'keydown': function(inp, e) {
													if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) )
													{
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
														if ( Ext.isIE )
														{
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												},
												'keyup': function( inp, e )
												{
													if ( e.F4 == e.getKey() || e.F2 == e.getKey() || ( e.DELETE == e.getKey() && e.altKey) )
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
											onTrigger2Click: function() {
												if (this.disabled) return false;
												var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
												ownerForm.findById('MSBEW_BAddress_Zip').setValue('');
												ownerForm.findById('MSBEW_BKLCountry_id').setValue('');
												ownerForm.findById('MSBEW_BKLRGN_id').setValue('');
												ownerForm.findById('MSBEW_BKLSubRGN_id').setValue('');
												ownerForm.findById('MSBEW_BKLCity_id').setValue('');
												ownerForm.findById('MSBEW_BKLTown_id').setValue('');
												ownerForm.findById('MSBEW_BKLStreet_id').setValue('');
												ownerForm.findById('MSBEW_BAddress_House').setValue('');
												ownerForm.findById('MSBEW_BAddress_Corpus').setValue('');
												ownerForm.findById('MSBEW_BAddress_Flat').setValue('');
												ownerForm.findById('MSBEW_BAddress_Address').setValue('');
												ownerForm.findById('MSBEW_BAddress_AddressText').setValue('');
											},
											/*onTrigger2Click: function() { //2-я кнопка
											},*/
											onTrigger1Click: function() {
												if (this.disabled) return false;
												var ownerWindow = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;
												var ownerForm = this.ownerCt.ownerCt.ownerCt.ownerCt.ownerCt;													
												getWnd('swAddressEditWindow').show({
													fields: {
														Address_ZipEdit: ownerForm.findById('MSBEW_BAddress_Zip').getValue(),
														KLCountry_idEdit: ownerForm.findById('MSBEW_BKLCountry_id').getValue(),
														KLRgn_idEdit: ownerForm.findById('MSBEW_BKLRGN_id').getValue(),
														KLSubRGN_idEdit: ownerForm.findById('MSBEW_BKLSubRGN_id').getValue(),
														KLCity_idEdit: ownerForm.findById('MSBEW_BKLCity_id').getValue(),
														KLTown_idEdit: ownerForm.findById('MSBEW_BKLTown_id').getValue(),
														KLStreet_idEdit: ownerForm.findById('MSBEW_BKLStreet_id').getValue(),
														Address_HouseEdit: ownerForm.findById('MSBEW_BAddress_House').getValue(),
														Address_CorpusEdit: ownerForm.findById('MSBEW_BAddress_Corpus').getValue(),
														Address_FlatEdit: ownerForm.findById('MSBEW_BAddress_Flat').getValue(),
														Address_AddressEdit: ownerForm.findById('MSBEW_BAddress_Address').getValue()
													},
													callback: function(values) {																			
														ownerForm.findById('MSBEW_BAddress_Zip').setValue(values.Address_ZipEdit);
														ownerForm.findById('MSBEW_BKLCountry_id').setValue(values.KLCountry_idEdit);
														ownerForm.findById('MSBEW_BKLRGN_id').setValue(values.KLRgn_idEdit);
														ownerForm.findById('MSBEW_BKLSubRGN_id').setValue(values.KLSubRGN_idEdit);
														ownerForm.findById('MSBEW_BKLCity_id').setValue(values.KLCity_idEdit);
														ownerForm.findById('MSBEW_BKLTown_id').setValue(values.KLTown_idEdit);
														ownerForm.findById('MSBEW_BKLStreet_id').setValue(values.KLStreet_idEdit);
														ownerForm.findById('MSBEW_BAddress_House').setValue(values.Address_HouseEdit);
														ownerForm.findById('MSBEW_BAddress_Corpus').setValue(values.Address_CorpusEdit);
														ownerForm.findById('MSBEW_BAddress_Flat').setValue(values.Address_FlatEdit);
														ownerForm.findById('MSBEW_BAddress_Address').setValue(values.Address_AddressEdit);
														ownerForm.findById('MSBEW_BAddress_AddressText').setValue(values.Address_AddressEdit);
														ownerForm.findById('MSBEW_BAddress_AddressText').focus(true, 500);
													},
													onClose: function() {
														ownerForm.findById('MSBEW_BAddress_AddressText').focus(true, 500);
													}
												})
											}
										})
									]
								}]
							}, { //Филдсет Получатель
								xtype: 'fieldset',
								autoHeight: true,
								title: lang['poluchatel'],
								style: 'padding: 5px; margin: 5px;',
								items: [{
									layout: 'column',
									border: false,
									labelWidth: 239 + label_mod_1,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											editable: false,
											fieldLabel: lang['fio'],
											hiddenName: 'Person_rid',
											tabIndex: TABINDEX_EREF + 26,
											width: 810 + (field_mod_1*3) + (label_mod_1*2),
											xtype: 'swpersoncombo',
											onTrigger1Click: function() {
												if (this.disabled) return false;
												var ownerWindow = Ext.getCmp('PersonEditWindow');
												var combo = this;
												getWnd('swPersonSearchWindow').show({
													onSelect: function(personData) {
														if ( personData.Person_id > 0 )
														{
															combo.getStore().loadData([{
																Person_id: personData.Person_id,
																Person_Fio: personData.PersonSurName_SurName + ' ' + personData.PersonFirName_FirName + ' ' + personData.PersonSecName_SecName
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
									}]
								}, {
									layout: 'column',
									border: false,
									labelWidth: 239 + label_mod_1,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											allowBlank: true,	
											disabled: false,
											fieldLabel: lang['dokument_seriya_nomer_kem_vyidan'],
											maxLength: 100,
											name: 'BirthSvid_RcpDocument',
											tabIndex: TABINDEX_EREF + 27,
											width: 810 + (field_mod_1*3) + (label_mod_1*2),
											value: '', //default value
											xtype: 'textfield'
										}]
									}]
								}, {
									layout: 'column',
									border: false,
									labelWidth: 239 + label_mod_1,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											allowBlank: true,	
											disabled: false,
											fieldLabel: 'Отношение к ребёнку',
											hiddenName: 'DeputyKind_id',
											tabIndex: TABINDEX_EREF + 28,
											width: 200 + field_mod_1,
											listeners: {
												'render': function(combo) {
													combo.getStore().load({
														params: { where: "where DeputyKind_Code = 1 or DeputyKind_Code = 2" },
														callback: function() { combo.setValue(combo.getValue()); }
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
										}]
									}]
								}, {
									layout: 'column',
									border: false,
									labelWidth: 239 + label_mod_1,
									items: [{
										layout: 'form',
										border: false,
										items: [{
											allowBlank: false,
											disabled: false,
											fieldLabel: lang['data_polucheniya_svid-va'],
											format: 'd.m.Y',
											name: 'BirthSvid_RcpDate',
											plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
											tabIndex: TABINDEX_EREF + 29,
											width: 200 + field_mod_1,
											value: '', //default value
											xtype: 'swdatefield'
										}]
									}]
								}]
							}, { //Записано со слов матери;
								layout: 'column',
								border: false,
								labelWidth: 250 + label_mod_1,
								items: [{
									layout: 'form',
									border: false,
									items: [{
										allowBlank: false,
										disabled: false,
										fieldLabel: lang['zapisano_so_slov_materi'],										
										hiddenName: 'BirthSvid_IsFromMother',
										maxLength: 100,										
										tabIndex: TABINDEX_EREF + 30,
										width: 200 + field_mod_1,
										value: 1, //default value
										xtype: 'swyesnocombo'
									}]
								}]
							}
						]						
					}]
				})
				],
				keys: [{
                    fn: function(inp, e) {
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
			            	Ext.getCmp('MSBEF_PersonInformationFrame').panelButtonClick(1);
			            	return false;
			            }

			            if (e.getKey() == Ext.EventObject.F10) {
			            	Ext.getCmp('MSBEF_PersonInformationFrame').panelButtonClick(2);
			            	return false;
			            }

			            if (e.getKey() == Ext.EventObject.F11) {
			            	Ext.getCmp('MSBEF_PersonInformationFrame').panelButtonClick(3);
			            	return false;
			            }

			            if (e.getKey() == Ext.EventObject.F12) {
			            	if (e.CtrlKey == true) {
				            	Ext.getCmp('MSBEF_PersonInformationFrame').panelButtonClick(5);
				            } else {
				            	Ext.getCmp('MSBEF_PersonInformationFrame').panelButtonClick(4);
							}
			            	return false;
			            }
                    },
                    key: [ Ext.EventObject.F6, Ext.EventObject.F10, Ext.EventObject.F11, Ext.EventObject.F12 ],
                    scope: this,
                    stopEvent: true
                }, {
                	alt: true,
                    fn: function(inp, e) {
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
                    key: [ Ext.EventObject.C, Ext.EventObject.G, Ext.EventObject.J ],
                    scope: this,
                    stopEvent: true
                }],
				labelAlign: 'right',
				labelWidth: 130 + label_mod_1,
				reader: new Ext.data.JsonReader({
							id: 'BirthSvid_id'
						}, [
							//MedStaffFact_cid
							{mapping:'MedStaffFact_cid', name:'MedStaffFact_cid', type:'int'},
							{mapping:'Org_id', name:'Org_id', type:'int'},
							{mapping:'BirthSvid_IsOtherMO', name:'BirthSvid_IsOtherMO', type:'string'},
							{mapping:'Server_id', name:'Server_id', type:'int'},
							{mapping:'Person_id', name:'Person_id', type:'int'},
							//{mapping:'MSBE_Person_id',name:'MSBE_Person_id',type:'int'},
							//{mapping:'MSBE_Server_id',name:'MSBE_Server_id',type:'int'},
							{mapping:'BirthSvid_id', name:'BirthSvid_id', type:'int'},
							{mapping:'Person_rid', name:'Person_rid', type:'int'},
							{mapping:'Person_r_FIO', name:'Person_r_FIO', type:'string'},
							{mapping:'BirthSvid_Ser', name:'BirthSvid_Ser', type:'string'},
							{mapping:'BirthSvid_Num', name:'BirthSvid_Num', type:'string'},
							{mapping:'MedPersonal_id', name:'MedPersonal_id', type:'int'},
							{mapping:'MedStaffFact_id', name:'MedStaffFact_id', type:'int'},
							{mapping:'BirthMedPersonalType_id', name:'BirthMedPersonalType_id', type:'int'},
							{mapping:'ReceptType_id', name:'ReceptType_id', type:'int'},
							{mapping:'BirthEducation_id', name:'BirthEducation_id', type:'int'},
							{mapping:'BirthPlace_id', name:'BirthPlace_id', type:'int'},
							{mapping:'Sex_id', name:'Sex_id', type:'int'},
							{mapping:'BirthSvid_Week', name:'BirthSvid_Week', type:'string'},
							{mapping:'BirthSvid_ChildCount', name:'BirthSvid_ChildCount', type:'string'},
							{mapping:'BirthFamilyStatus_id', name:'BirthFamilyStatus_id', type:'int'},
							{mapping:'BirthSvid_RcpDocument', name:'BirthSvid_RcpDocument', type:'string'},
							{mapping:'BirthEmployment_id', name:'BirthEmployment_id', type:'int'},
							{mapping:'BirthSpecialist_id', name:'BirthSpecialist_id', type:'int'},
							{mapping:'BirthSvid_ChildFamil', name:'BirthSvid_ChildFamil', type:'string'},
							{mapping:'BirthSvid_IsMnogoplod', name:'BirthSvid_IsMnogoplod', type:'int'},
							{mapping:'BirthSvid_PlodIndex', name:'BirthSvid_PlodIndex', type:'string'},
							{mapping:'BirthSvid_PlodCount', name:'BirthSvid_PlodCount', type:'string'},
							{mapping:'BirthSvid_IsFromMother', name:'BirthSvid_IsFromMother', type:'int'},
							{mapping:'BirthSvid_Height', name:'BirthSvid_Height', type:'string'},
							{mapping:'BirthSvid_Mass', name:'BirthSvid_Mass', type:'string'},
							{mapping:'Okei_mid', name:'Okei_mid', type:'string'},
							{mapping:'BirthChildResult_id', name:'BirthChildResult_id', type:'int'},
							{mapping:'Address_rid', name:'Address_rid', type:'int'},
							{mapping:'BAddress_Zip', name: 'BAddress_Zip', type:'string'},
							{mapping:'BKLCountry_id', name: 'BKLCountry_id', type:'string'},
							{mapping:'BKLRGN_id', name: 'BKLRGN_id', type:'string'},
							{mapping:'BKLSubRGN_id', name: 'BKLSubRGN_id', type:'string'},
							{mapping:'BKLCity_id', name: 'BKLCity_id', type:'string'},
							{mapping:'BKLTown_id', name: 'BKLTown_id', type:'string'},
							{mapping:'BKLStreet_id', name: 'BKLStreet_id', type:'string'},
							{mapping:'BAddress_House', name: 'BAddress_House', type:'string'},
							{mapping:'BAddress_Corpus', name: 'BAddress_Corpus', type:'string'},
							{mapping:'BAddress_Flat', name: 'BAddress_Flat', type:'string'},
							{mapping:'BAddress_Address', name: 'BAddress_Address', type:'string'},
							{mapping:'BAddress_AddressText', name: 'BAddress_AddressText', type:'string'},
							{mapping:'BirthSvid_BirthDT_Date', name:'BirthSvid_BirthDT_Date', type:'date', dateFormat: 'd.m.Y'},
							{mapping:'BirthSvid_BirthDT_Time', name:'BirthSvid_BirthDT_Time', type:'string'},
                            {mapping:'LpuLicence_id',name:'LpuLicence_id',type:'int'},
							{mapping:'BirthSvid_RcpDate', name:'BirthSvid_RcpDate', type:'date', dateFormat: 'd.m.Y'},
							{mapping:'BirthSvid_GiveDate', name:'BirthSvid_GiveDate', type:'date', dateFormat: 'd.m.Y'},
							{mapping:'MedStaffFact_id', name:'MedStaffFact_id', type:'int'},
							{mapping:'LpuSection_id', name:'LpuSection_id', type:'int'},
							{mapping:'DeputyKind_id', name:'DeputyKind_id', type:'int'},
							{mapping:'OrgHead_id', name:'OrgHead_id', type:'int'},
							{mapping:'BirthSvid_IsBad', name:'BirthSvid_IsBad', type:'int'},
							{mapping:'BirthSvid_isInRpn', name:'BirthSvid_isInRpn', type:'int'}

						]
				),
				region: 'center',
				trackResetOnLoad: true,
				url: '/?c=MedSvid&m=saveMedSvidBirth'
			})]
		});
		
		var mp_combo = Ext.getCmp('MSBEF_MedPersonalCombo');
		var ls_combo = Ext.getCmp('MSBEF_LpuSectionCombo');
		mp_combo.getStore().addListener('datachanged', function(store) {
			if(store.getCount() == 1) {
				//var mp_id = store.getAt(0).id;	
				var mp_id = store.getAt(0).data.MedStaffFact_id;
				mp_combo.setValue(mp_id);
			};
		});
		ls_combo.addListener('change', function(combo, newValue, oldValue) {
			if ( !(typeof combo.linkedElements == 'object') || combo.linkedElements.length == 0 || combo.linkedElementsDisabled == true ) {
				return true;
			}

			var altValue;

			if ( combo.valueFieldAdd ) {
				var r = combo.getStore().getById(newValue);

				if ( r ) {
					altValue = r.get(combo.valueFieldAdd);
				}
			}

			for ( var i = 0; i < combo.linkedElements.length; i++ ) {
				var linked_element = Ext.getCmp(combo.linkedElements[i]);

				if ( !linked_element ) {
					return true;
				}

				var linked_element_value = linked_element.getValue();

				if ( newValue > 0 ) {
					linked_element.clearValue();
					linked_element.setBaseFilter(function(record, id) {
						if ( record.get(combo.valueField) == newValue || (altValue && record.get(combo.valueField) == altValue) ) {
							return true;
						}
						else {
							return false;
						}
					}.createDelegate(combo), combo);

					if ( linked_element_value && linked_element.valueField ) {
						var index = linked_element.getStore().findBy(function(record) {
							if ( record.get(combo.valueField) == linked_element_value ) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(combo));

						var record = linked_element.getStore().getAt(index);

						if (linked_element.getStore().getCount() == 1)
							record = linked_element.getStore().getAt(0);

						if ( record ) {
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
		
		sw.Promed.swMedSvidBirthEditWindow.superclass.initComponent.apply(this, arguments);		
	},
	layout: 'border',
	maximizable: true,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	listeners: {
		hide: function (){
			if (this.callbackOnHide) {
				this.callbackOnHide();
			}
		}
	},
	clearValues: function() {
		var base_form = this.findById('MedSvidBirthEditForm').getForm();
		base_form.findField('BirthSvid_IsOtherMO').setValue( false );
		base_form.findField('Person_rid').setValue(null);
		base_form.findField('Person_r_FIO').setValue(null);													
		base_form.findField('BirthSvid_Ser').setValue(null);
		base_form.findField('BirthSvid_Num').setValue(null);		
		base_form.findField('BirthMedPersonalType_id').setValue(1);
		base_form.findField('ReceptType_id').setValue(2);
		base_form.findField('BirthEducation_id').setValue(1);
		base_form.findField('BirthPlace_id').setValue(1);
		base_form.findField('Sex_id').setValue(1);
		base_form.findField('BirthSvid_Week').setValue(null);
		base_form.findField('BirthSvid_ChildCount').setValue(null);
		base_form.findField('BirthFamilyStatus_id').clearValue();
		base_form.findField('BirthSvid_RcpDocument').setValue(null);
		base_form.findField('BirthEmployment_id').setValue(1);
		base_form.findField('BirthSpecialist_id').setValue(1);
		base_form.findField('BirthSvid_ChildFamil').setValue(null);
		base_form.findField('BirthSvid_IsMnogoplod').setValue(1);
		base_form.findField('BirthSvid_PlodIndex').setValue(null);
		base_form.findField('BirthSvid_PlodCount').setValue(null);
		base_form.findField('BirthSvid_IsFromMother').setValue(1);
		base_form.findField('BirthSvid_Height').setValue(null);
		base_form.findField('BirthSvid_Mass').setValue(null);
		base_form.findField('Okei_mid').setValue(36);
		base_form.findField('BirthChildResult_id').setValue(1);
		base_form.findField('BAddress_Zip').setValue(null);
		base_form.findField('BKLCountry_id').setValue(null);
		base_form.findField('BKLRGN_id').setValue(null);
		base_form.findField('BKLSubRGN_id').setValue(null);
		base_form.findField('BKLCity_id').setValue(null);
		base_form.findField('BKLTown_id').setValue(null);
		base_form.findField('BKLStreet_id').setValue(null);
		base_form.findField('BAddress_House').setValue(null);
		base_form.findField('BAddress_Corpus').setValue(null);
		base_form.findField('BAddress_Flat').setValue(null);
		base_form.findField('BAddress_Address').setValue(null);
		base_form.findField('BAddress_AddressText').setValue(null);
		base_form.findField('BirthSvid_BirthDT_Date').setValue(null);
		base_form.findField('BirthSvid_BirthDT_Time').setValue(null);
		base_form.findField('BirthSvid_RcpDate').setValue(null);
		base_form.findField('LpuSection_id').setValue(null);
		base_form.findField('MedStaffFact_id').setValue(null);
		base_form.findField('OrgHead_id').setValue(null);

		base_form.findField('MedStaffFact_cid').setValue(null);
		//base_form.findField('BirthSvid_GiveDate').setValue(new Date());
		
		Ext.getCmp('BirthSvid_PlodIndex').disable();
		Ext.getCmp('BirthSvid_PlodCount').disable();

		//AnotherMOPlace_id setContainerVisible
		//Ext.getCmp('AnotherMOPlace_id').setContainerVisible( false );

	},
	onChangeReceptType: function(rectype) {
		var win = this;
		var base_form = win.findById('MedSvidBirthEditForm').getForm();

		base_form.findField('BirthSvid_Ser').setValue('');
		base_form.findField('BirthSvid_Num').setValue('');

		if (rectype == 1) {
			base_form.findField('BirthSvid_Num').enable();
			if (getRegionNick() == 'ufa') {
				base_form.findField('BirthSvid_Ser').disable();
			} else {
				base_form.findField('BirthSvid_Ser').enable();
			}
			if (getRegionNick() == 'ufa') {
				win.generateNewNumber(true);
			} else {
				win.generateNewNumber(true);
			}
		} else if(rectype == 2) {
			base_form.findField('BirthSvid_Ser').disable();
			base_form.findField('BirthSvid_Num').disable();
			win.generateNewNumber(true);
		}
	},
	show: function() {
		sw.Promed.swMedSvidBirthEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		win.needLpuSectionForNumGeneration = true;
		win.lastParamsForNumGeneration = null;

		win.findById( 'AnotherMOPlace_id' ).setContainerVisible( false );

		var person_id = 0;
		var server_id = 0;
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		var form = this.findById('MedSvidBirthEditForm');
		var pers_form = this.findById('MSBEF_PersonInformationFrame');
		var base_form = form.getForm();

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callbackAfterSave) {
			this.callbackAfterSave = arguments[0].callbackAfterSave;
		}
		if (arguments[0].callbackOnHide) {
			this.callbackOnHide = arguments[0].callbackOnHide;
		}

		this.saveMode = 1;
		this.edit_poluchatel = 0;
		if(arguments[0].edit_poluchatel){
			this.edit_poluchatel = arguments[0].edit_poluchatel;
		}

		var focusOnField = null;
		if (arguments[0].focusOnfield) {
			focusOnField = arguments[0].focusOnfield;
		}
		var title = lang['svidetelstvo_o_rojdenii'];
		switch (this.action) {
			case 'add': title += lang['_dobavlenie']; break;
			case 'edit': title += lang['_dobavlenie']; break;
			case 'view': title += lang['_prosmotr']; break;
		}
		this.setTitle(title);

		base_form.reset();
		this.enableEdit(this.action != 'view');
		if (this.action == 'add') this.clearValues();

		if (getRegionNick() == 'kz') {
			base_form.findField('BirthSvid_Ser').setAllowBlank(true);
			base_form.findField('BirthSvid_Ser').hideContainer();
			base_form.findField('BirthEmployment_id').setAllowBlank(true);
			base_form.findField('BirthEmployment_id').hideContainer();
			base_form.findField('OrgHead_id').setAllowBlank(true);
			base_form.findField('OrgHead_id').hideContainer();
			base_form.findField('LpuLicence_id').setAllowBlank(true);
			base_form.findField('LpuLicence_id').hideContainer();
			base_form.findField('SvidType_id').hideContainer();
			Ext.getCmp(win.id + 'gennewnumber').hide();

			Ext.getCmp('MSBEW_SendToRPNButton').show();
			if (this.action == 'add') {
				Ext.getCmp('MSBEW_SendToRPNButton').setText('Сохранить и отправить в РПН');
			} else {
				Ext.getCmp('MSBEW_SendToRPNButton').setText('Отправить в РПН');
			}
		} else {
			Ext.getCmp('MSBEW_SendToRPNButton').hide();
		}
		
		this.restore();
		this.center();
		this.maximize();
		loadMask.show();
		this.findById('MedSvidBirthEditWindowTab').setActiveTab(0);
		this.findById('MedSvidBirthEditWindowTab').getActiveTab().doLayout();

		if (getRegionNick() != 'ufa') {
			base_form.findField('BirthFamilyStatus_id').setFieldValue('BirthFamilyStatus_Code', 1);
		}

		switch ( this.action ) {
			case 'add':
				if ( arguments[0].formParams ) {
					person_id = arguments[0].formParams.Person_id;
					server_id = arguments[0].formParams.Server_id;
					form.findById('Person_id').setValue(person_id);
					form.findById('Server_id').setValue(server_id);
					//gabdushev Заполняю некоторые поля автоматически, если их значения переданы в параметрах вызова формы
					var f = form.getForm();
					if (arguments[0].formParams.LpuSection_id){
						var lpuSectionCombo = form.findById('MSBEF_LpuSectionCombo');
						lpuSectionCombo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
						lpuSectionCombo.setValue(arguments[0].formParams.LpuSection_id);
					}
					if (arguments[0].formParams.Person_cid){
						var Person_cid = form.findById('Person_cid');
						Person_cid.setValue(arguments[0].formParams.Person_cid);
					}
					if (arguments[0].formParams.MedStaffFact_id){
						var medPersCombo = form.findById('MSBEF_MedPersonalCombo'),
							MedStaffFact_id = arguments[0].formParams.MedStaffFact_id;

						setMedStaffFactGlobalStoreFilter({allowDuplacateMSF:true, Lpu_id: getGlobalOptions().Lpu_id});
						medPersCombo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

						var record = medPersCombo.getStore().getById(MedStaffFact_id);

						if ( record !== undefined )
						{
							medPersCombo.setValue(MedStaffFact_id);
						}

						MedStaffFact_id = undefined;
						record = undefined;
					}
					//BirthSvid_BirthDT_Date - Дата исхода беременности
					if (arguments[0].formParams.BirthSvid_BirthDT_Date){
						f.findField('BirthSvid_BirthDT_Date').setValue(arguments[0].formParams.BirthSvid_BirthDT_Date);
					}
					//BirthSvid_BirthDT_Time - время родов
					if (arguments[0].formParams.BirthSvid_BirthDT_Time){
						f.findField('BirthSvid_BirthDT_Time').setValue(arguments[0].formParams.BirthSvid_BirthDT_Time);
					}
                    if (arguments[0].formParams.LpuLicence_id){
                        f.findField('LpuLicence_id').setValue(arguments[0].formParams.LpuLicence_id);
                    }
					//BirthPlace_id (BirthPlaceHidden_id?) - место родов
					if (arguments[0].formParams.BirthPlace_id){
						var BirthPlace_id = arguments[0].formParams.BirthPlace_id;
						//todo: избавиться от таймера
						setTimeout(function(){
							f.findField('BirthPlace_id').setValue(BirthPlace_id);
						},200);
					}
					//BirthSvid_IsMnogoplod - Многоплодные роды
					if (arguments[0].formParams.BirthSvid_IsMnogoplod){
						var BirthSvid_IsMnogoplod = arguments[0].formParams.BirthSvid_IsMnogoplod;
						//todo: избавиться от таймера
						setTimeout(function(){
							f.findField('BirthSvid_IsMnogoplod').setValue(BirthSvid_IsMnogoplod);
						},200);
					}
					//BirthSvid_PlodIndex - который по счету
					if (arguments[0].formParams.BirthSvid_PlodIndex){
						f.findField('BirthSvid_PlodIndex').setValue(arguments[0].formParams.BirthSvid_PlodIndex);
					}
					//Sex_id - пол
					if (arguments[0].formParams.Sex_id){
						var Sex_id = arguments[0].formParams.Sex_id;
						//todo: избавиться от таймера
						setTimeout(function(){
							f.findField('Sex_id').setValue(Sex_id);
						},200);
					}
					//BirthSvid_PlodCount - всего плодов
					if (arguments[0].formParams.BirthSvid_PlodCount){
						f.findField('BirthSvid_PlodCount').setValue(arguments[0].formParams.BirthSvid_PlodCount);
					}
					//BirthSvid_Mass - масса
					if (arguments[0].formParams.BirthSvid_Mass){
						f.findField('BirthSvid_Mass').setValue(arguments[0].formParams.BirthSvid_Mass);
					}
					if (arguments[0].formParams.Okei_mid){
						var Okei_mid = arguments[0].formParams.Okei_mid;
						setTimeout(function(){
							f.findField('Okei_mid').setValue(Okei_mid);
						},200);
					}
					//BirthSvid_Height - рост
					if (arguments[0].formParams.BirthSvid_Height){
						f.findField('BirthSvid_Height').setValue(arguments[0].formParams.BirthSvid_Height);
					}
					//BirthChildResult_id(BirthChildResultHidden_id?) - ребенок родился
					if (arguments[0].formParams.BirthChildResult_id){
						var bres_id = arguments[0].formParams.BirthChildResult_id;
						//todo: избавиться от таймера
						setTimeout(function(){
							f.findField('BirthChildResult_id').setValue(bres_id);
						},200);
					}
					//BirthSvid_ChildFamil - фамилия ребенка
					if (arguments[0].formParams.BirthSvid_ChildFamil){
						f.findField('BirthSvid_ChildFamil').setValue(arguments[0].formParams.BirthSvid_ChildFamil);
					}
					if (arguments[0].formParams.BirthSvid_Week){
						f.findField('BirthSvid_Week').setValue(arguments[0].formParams.BirthSvid_Week);
					}
				}

				if (getRegionNick() == 'ufa') {
					var mprtype = base_form.findField('BirthSvid_IsMnogoplod').getValue();
					form.findById('BirthSvid_PlodIndex').setAllowBlank(true);
					form.findById('BirthSvid_PlodCount').setAllowBlank(true);
					if (mprtype == 2) {
						form.findById('BirthSvid_PlodIndex').enable();
						form.findById('BirthSvid_PlodCount').enable();
						form.findById('BirthSvid_PlodIndex').setAllowBlank(false);
						form.findById('BirthSvid_PlodCount').setAllowBlank(false);
					} else {
						form.findById('BirthSvid_PlodIndex').disable();
						form.findById('BirthSvid_PlodCount').disable();
						form.findById('BirthSvid_PlodIndex').setValue(1);
						form.findById('BirthSvid_PlodCount').setValue(1);
					}
				}

				pers_form.load({ Person_id: person_id, Server_id: server_id });
				Ext.Ajax.request({ //устанавливаем по умолчанию место рождения
					callback: function(options, success, response) {
						if (success && response.responseText != '') {
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( typeof response_obj == 'object' && response_obj.length > 0 ) {
								response_obj = response_obj[0];
								Ext.getCmp('MSBEW_BAddress_Zip').setValue(response_obj.BAddress_Zip);
								Ext.getCmp('MSBEW_BKLCountry_id').setValue(response_obj.BKLCountry_id);
								Ext.getCmp('MSBEW_BKLRGN_id').setValue(response_obj.BKLRGN_id);
								Ext.getCmp('MSBEW_BKLSubRGN_id').setValue(response_obj.BKLSubRGN_id);
								Ext.getCmp('MSBEW_BKLCity_id').setValue(response_obj.BKLCity_id);
								Ext.getCmp('MSBEW_BKLTown_id').setValue(response_obj.BKLTown_id);
								Ext.getCmp('MSBEW_BKLStreet_id').setValue(response_obj.BKLStreet_id);
								Ext.getCmp('MSBEW_BAddress_House').setValue(response_obj.BAddress_House);
								Ext.getCmp('MSBEW_BAddress_Corpus').setValue(response_obj.BAddress_Corpus);
								Ext.getCmp('MSBEW_BAddress_Flat').setValue(response_obj.BAddress_Flat);
								Ext.getCmp('MSBEW_BAddress_Address').setValue(response_obj.BAddress_Address);
								Ext.getCmp('MSBEW_BAddress_AddressText').setValue(response_obj.BAddress_AddressText);
							}
						} else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_generatsii_mesta_rojdeniya_po_umolchaniyu_proizoshla_oshibka']);
						}
					},
					params: {
						Lpu_id: getGlobalOptions().Lpu_id
					},
					url: '/?c=MedSvid&m=getDefaultBirthAddress'
				});

				win.onChangeReceptType(base_form.findField('ReceptType_id').getValue());

				setCurrentDateTime({
					callback: function() 
					{
						base_form.findField('BirthSvid_GiveDate').fireEvent('change', base_form.findField('BirthSvid_GiveDate'), base_form.findField('BirthSvid_GiveDate').getValue());
					},
					dateField: base_form.findField('BirthSvid_GiveDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					timeField: base_form.findField('BirthSvid_GiveDate'),
					windowId: 'MedSvidBirthEditWindow'
				});
				if (focusOnField) {
					if (base_form.findField(focusOnField)) {
						setTimeout(function(){
							base_form.findField(focusOnField).focus();
						}, 3000);
					} else {
						base_form.findField('BirthSvid_GiveDate').focus(true, 0);
					}
				} else {
					base_form.findField('BirthSvid_GiveDate').focus(true, 0);
				}

				//Обрабатываем видимость поля руководитель при добавлении 161871 и обязательность заполнения
				if ( getRegionNick() != 'kz' ) {
					base_form.findField('OrgHead_id').setContainerVisible( false );
					base_form.findField('OrgHead_id').setAllowBlank( true );
				} else {
					//В казахстане обязателен к заполнению
					base_form.findField('OrgHead_id').setContainerVisible( true );
					base_form.findField('OrgHead_id').setAllowBlank( false );

					base_form.findField('MedStaffFact_cid').setContainerVisible( false );
					base_form.findField('MedStaffFact_cid').setAllowBlank( true );
				}
				break;
			case 'view':
			case 'edit':
				if ( arguments[0].formParams ) {
					var svid_id = arguments[0].formParams.BirthSvid_id;
				}
				base_form.load({
					failure: function() {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_svidetelstva'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						svid_id: svid_id,
						svid_type: 'birth'
					},
					success: function() {
						person_id = form.findById('Person_id').getValue();
						server_id = form.findById('Server_id').getValue();
						pers_form.load({ Person_id: person_id, Server_id: server_id });

						var is_bad = (base_form.findField('BirthSvid_IsBad').getValue()==2);

						base_form.findField('BirthSvid_Ser').disable();
						base_form.findField('BirthSvid_Num').disable();

						if ( isUserGroup( 'MedSvidDeath' ) && base_form.findField( 'BirthSvid_IsOtherMO' ).getValue() && getRegionNick() == 'ufa' ){
							//загрузим название другого МО
							Ext.Ajax.request({
								callback: function(options, success, response) {
									if (success && response.responseText != '') {
										var response_obj = Ext.util.JSON.decode(response.responseText);
										Ext.getCmp( 'AnotherMOPlace_id' ).setContainerVisible( true );
										Ext.getCmp('AnotherMOPlace_id').setValue( response_obj[0]['Org_name'] );
									} else {
										sw.swMsg.alert(langs('Ошибка'), langs('При загрузке названия другой МО произошла ошибка.'));
									}
								},
								params: {
									Org_id: base_form.findField('Org_id').getValue()
								},
								url: '/?c=MedSvid&m=getAnotherMOName'
							});
						}

						if (getRegionNick() == 'kz' && (base_form.findField('BirthSvid_isInRpn').getValue() == 1 || is_bad)) {
							/*win.action = 'view';
							win.enableEdit(false);
							win.edit_poluchatel = 0;*/
							Ext.getCmp('MSBEW_SendToRPNButton').hide();
						}
												
						var p_rid = base_form.findField('Person_rid').getValue(); //установка фамилия получателя
						if ( base_form.findField('Person_rid').getValue() > 0 ) {
							base_form.findField('Person_rid').getStore().loadData([{
								Person_id: p_rid,
								Person_Fio: base_form.findField('Person_r_FIO').getValue()
							}]);
							base_form.findField('Person_rid').setValue(p_rid);
						} else
							base_form.findField('Person_rid').getStore().removeAll();
						
						setCurrentDateTime({
							callback: function() 
							{
								base_form.findField('BirthSvid_GiveDate').fireEvent('change', base_form.findField('BirthSvid_GiveDate'), base_form.findField('BirthSvid_GiveDate').getValue());
								base_form.findField('BirthSvid_GiveDate').focus(true, 0);
							},
							dateField: base_form.findField('BirthSvid_GiveDate'),
							loadMask: false,
							setDate: true,
							setDateMaxValue: true,
							setDateMinValue: false,
							setTime: false,
							timeField: base_form.findField('BirthSvid_GiveDate'),
							windowId: 'MedSvidBirthEditWindow'
						});
						
						
						if (this.action == 'edit') {
							var mprtype = base_form.findField('BirthSvid_IsMnogoplod').getValue();
							form.findById('BirthSvid_PlodIndex').setAllowBlank(true);
							form.findById('BirthSvid_PlodCount').setAllowBlank(true);
							if (mprtype == 2) {
								form.findById('BirthSvid_PlodIndex').enable();
								form.findById('BirthSvid_PlodCount').enable();
								if (getRegionNick() == 'ufa') {
									form.findById('BirthSvid_PlodIndex').setAllowBlank(false);
									form.findById('BirthSvid_PlodCount').setAllowBlank(false);
									form.findById('BirthSvid_PlodIndex').setValue('');
									form.findById('BirthSvid_PlodCount').setValue('');
								}
							} else {
								form.findById('BirthSvid_PlodIndex').disable();
								form.findById('BirthSvid_PlodCount').disable();
								form.findById('BirthSvid_PlodIndex').setValue('');
								form.findById('BirthSvid_PlodCount').setValue('');
								if (getRegionNick() == 'ufa') {
									form.findById('BirthSvid_PlodIndex').setValue(1);
									form.findById('BirthSvid_PlodCount').setValue(1);
								} else {
									form.findById('BirthSvid_PlodIndex').setValue('');
									form.findById('BirthSvid_PlodCount').setValue('');
								}
							}
						}
						if(this.action == 'view' && this.edit_poluchatel==1){

							base_form.findField('Person_rid').enable();
							base_form.findField('BirthSvid_RcpDocument').enable();
							base_form.findField('DeputyKind_id').enable();
							base_form.findField('BirthSvid_RcpDate').enable();
							// сохраняем только получателя
							win.saveMode = 2;
							win.buttons[0].show();

						}

						//Обрабатываем видимость поля руководитель при редактировании 161871 и обязательность заполнения
						if ( getRegionNick() != 'kz' ) {
							var OrgHead_id = base_form.findField('OrgHead_id').getValue();
							var MedStaffFact_cid = base_form.findField('MedStaffFact_cid').getValue();
							if ( OrgHead_id && !MedStaffFact_cid ){
								base_form.findField('OrgHead_id').setContainerVisible( true );
							} else {
								base_form.findField('OrgHead_id').setContainerVisible( false );
							}

							base_form.findField('OrgHead_id').setAllowBlank( true );
						} else {
							//В казахстане обязателен к заполнению
							base_form.findField('OrgHead_id').setContainerVisible( true );
							base_form.findField('OrgHead_id').setAllowBlank( false );

							base_form.findField('MedStaffFact_cid').setContainerVisible( false );
							base_form.findField('MedStaffFact_cid').setAllowBlank( true );
						}
					}.createDelegate(this),
					url: '/?c=MedSvid&m=loadMedSvidEditForm'
				});
			break;
		}
				
		Ext.getCmp('MedSvidBirthEditWindowTab').ownerCt.doLayout();
		
		if (this.action != 'view') {
			base_form.findField('ReceptType_id').focus(true, 400);
		}

		//base_form.clearInvalid();
		loadMask.hide();
	},	
	title: WND_MSVID_RECADD,
	width: 700
});
