/**
* swPersonCardEditWindow - окно редактирования/добавления карты пациента.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      05.06.2009
* @comment      Префикс для id компонентов PCEF (PersonCardEditForm)
*               tabIndex: 2100
*
*
* @input data: action - действие (add, edit, view)
*              PersonCard_id - ID карты для редактирования или просмотра
*              Person_id - ID человека
*              Server_id - ?
*
*
*/

sw.Promed.swPersonCardEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: 'add',
    autoHeight: true,
    closable: true,
    closeAction: 'hide',
	draggable: true,

	PersonDeputyData: null,
	onCancelAction: function() {
		
		var PersonAmbulatCard_id = this.newPersonAmbulatCard_id

		if ( PersonAmbulatCard_id > 0 && this.action == 'add') {
			
			// удалить талон
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление АК..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении АК возникли ошибки'));
						return false;
					}
				}.createDelegate(this),
				params: {
					PersonAmbulatCard_id: PersonAmbulatCard_id
				},
				url: '/?c=PersonAmbulatCard&m=deletePersonAmbulatCard'
			});
		}
		else {
			this.hide();
		}
	},
	getPersonCardMedicalInterventData: function() {
		var data = [];
		var grid = this.MedicalInterventGrid.getGrid();
		var base_form = this.findById('PersonCardEditForm').getForm();
		var is_medical_intervent  = base_form.findField('PersonCardMedicalIntervent').getValue();

		grid.getStore().each(function(rec) {
			var id = rec.get('PersonCardMedicalIntervent_id');
			var type_id = rec.get('MedicalInterventType_id');
			var is_refuse = (rec.get('PersonMedicalIntervent_IsRefuse')) ? 1 : 0;

			if (is_medical_intervent == 0) {
				is_refuse = 0;
			}

			if (id > 0) {
				data.push({
					PersonCardMedicalIntervent_id: id,
					MedicalInterventType_id: type_id,
					PersonMedicalIntervent_IsRefuse: is_refuse
				});
			} else
			if (id <= 0 && is_refuse) {
				data.push({
					PersonCardMedicalIntervent_id: -1,
					MedicalInterventType_id: type_id,
					PersonMedicalIntervent_IsRefuse: is_refuse
				});
			}
		});

		return data;
	},
	getPersonDeputyData: function() {
		var form = this;
		var person_id = form.findById('PCEF_Person_id').getValue();
		Ext.Ajax.request({
			params: {Person_id: person_id},
			callback: function(options, success, response) {
				if (success && response.responseText != '')
				{
					var data =  Ext.util.JSON.decode(response.responseText);
					form.PersonDeputyData = data[0];
				}
				else
				{
					form.showMessage(langs('Ошибка'), langs('Ошибка получении данных законного представителя пациента'));
				}
			},
			url: '/?c=Mse&m=getDeputyKind'
		});
	},
	doSubmit: function(print, addParams) {
		if (this.formStatus != 'save') { return false; }
		var current_window = this;
		var form = this.findById('PersonCardEditForm');
		var loadMask = new Ext.LoadMask(Ext.get('PersonCardEditWindow'), {msg: "Подождите, идет сохранение данных формы..."});
   		loadMask.show();
		
		var params = getAllFormFieldValues(form);
		form.getForm().findField('PersonCard_Code').disable();
		params.PersonCard_Code = form.getForm().findField('PersonCard_Code').getRawValue();
		params['PersonCard_begDate'] = Ext.util.Format.date(params['PersonCard_begDate'], 'd.m.Y');
		params['PersonCard_endDate'] = Ext.util.Format.date(params['PersonCard_endDate'], 'd.m.Y');
		params['isPersonCardAttach'] = Number(form.getForm().findField('PersonCardAttach').getValue());
		if(getRegionNick()=='perm') params['isPersonCardAttach'] = 1;//#169501 при сохранении и флаг устанавливается, и заявление должно в БД сохраняться
		params['LpuRegionType_id'] = form.getForm().findField('LpuRegionType_id').getValue();
		params['PersonAge'] = this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_Age");
		params['Person_Birthday'] = Ext.util.Format.date(this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_Birthday"), 'd.m.Y');
		params['lastAttachIsNotInOurLpu'] = this.lastAttachIsNotInOurLpu;
		if(form.getForm().findField('Lpu_id').getValue() != getGlobalOptions().lpu_id)
			params['lastAttachIsNotInOurLpu'] = true;
		if(this.Agreed == 1)
			params['lastAttachIsNotInOurLpu'] = false;
		// Собираем атрибуты прикрепленных файлов (если есть)
		var files = [];
		this.FilesPanel.findBy(function(file) {
			files.push(file.settings.name+'::'+file.settings.tmp_name);
		}, this.FilesPanel);
		if(files.length > 0)
			params['files'] = files.join('|');

		params['isPersonCardMedicalIntervent'] = Number(form.getForm().findField('PersonCardMedicalIntervent').getValue());

		params['PersonCardMedicalInterventData'] = Ext.util.JSON.encode(this.getPersonCardMedicalInterventData());
		
		if ( this.isDMS )
		{
			params.isDMS = 2;
			params['PersonCard_DmsBegDate'] = Ext.util.Format.date(form.getForm().findField('PersonCard_DmsBegDate').getValue(), 'd.m.Y');
			params['PersonCard_DmsEndDate'] = Ext.util.Format.date(form.getForm().findField('PersonCard_DmsEndDate').getValue(), 'd.m.Y');
			params['PersonCard_begDate'] = Ext.util.Format.date(form.getForm().findField('PersonCard_begDate').getValue(), 'd.m.Y');
			params['PersonCard_endDate'] = Ext.util.Format.date(form.getForm().findField('PersonCard_endDate').getValue(), 'd.m.Y');
		}

		params.action = this.action;
		params.setIsAttachCondit = this.setIsAttachCondit;
        params.allowEditLpuRegion = this.allowEditLpuRegion;

        var checkbox = form.getForm().findField('PersonCardAttach');
        if(getRegionNick()=='pskov' && current_window.attachType == 1 && current_window.otherCardExists == 0 && form.getForm().findField('PersonCard_begDate').getValue().format('Y-m-d') <= '2015-12-31' && checkbox.getValue() == false)
        {
            params.setIsAttachCondit = 2;
        }

		if(this.cardClosed && Ext.isEmpty(params['PersonCard_endDate'])) //Если карта была закрыта, а потом убрали дату закрытия https://redmine.swan.perm.ru/issues/52919
		{
			this.tryOpen = 1;
		}
		if (addParams != undefined) {
			for(var par in addParams) {
				if (par != 'remove') {
					params[par] = addParams[par];
				}
			}
		}

		if ( this.isDMS )
		{
			// костылище!!!
			Ext.Ajax.request({
					params: params,
					callback: function(options, success, response) {
						current_window.formStatus = 'edit';
						loadMask.hide();
						if (success && response.responseText != '')
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);
							if ( response_obj.success == true)
							{
								this.hide();
								this.onHide();
								current_window.returnFunc();
							}	
							else
							{
								if (response_obj.Error_Msg)
									var msg = response_obj.Error_Msg;
								else
									var msg = langs('При сохранении возникла ошибка');
								form.ownerCt.showMessage(langs('Ошибка'), msg, function() {form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 100);});
							}
						}

						if (!success)
						{
							form.ownerCt.showMessage(langs('Ошибка'), langs('При сохранении возникла ошибка'), function() {form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 100);});
						}
					}.createDelegate(this),
					url: '/?c=PersonCard&m=savePersonCardDms'
				});								
		}
		else
		{
			if(this.tryOpen == 1 && !current_window.oneCard) //https://redmine.swan.perm.ru/issues/52919
			{
				params['tryOpen'] = this.tryOpen;
				form.getForm().submit({
					params: params,
					success: function(result_form, action){
						current_window.formStatus = 'edit';
						loadMask.hide();
						current_window.hide();
						current_window.returnFunc();
					},
					failure: function(result_form, action){
						current_window.formStatus = 'edit';
						loadMask.hide();
						current_window.showMessage(langs('Ошибка'), langs('При сохранении произошли ошибки'), function() {
							current_window.hide();
						});
					},
					url: C_PERSONCARD_DEL
				});
			}
			else
			{
				form.getForm().submit({
					params: params,
					success: function(result_form, action) {
						current_window.formStatus = 'edit';
						loadMask.hide();

						if (action.result.lastAttachIsNotInOurLpu) {
							sw.swMsg.show({
							title: langs('Подтверждение прикрепления'),
							msg: langs('Пациент будет откреплен от предыдущей ЛПУ и прикреплен к вашей ЛПУ. Необходимо наличие подтверждения пациента в форме заявления о выборе медицинского учреждения.'),
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {

								if ( 'yes' == buttonId ) {
									current_window.lastAttachIsNotInOurLpu = false;
									current_window.Agreed = 1;
									current_window.formStatus = 'save';
									current_window.doSubmit();
								}
							}
							});
							return false;
						}
						form.getForm().findField('PersonCard_id').setValue(action.result.PersonCard_id);
						if ( Ext.isEmpty(params.PersonCard_endDate)  && (current_window.action == 'add')) {
                            if(!(params.setIsAttachCondit==2 && getRegionNick()=='pskov') && action.result.disable_print == 0)  //по задаче https://redmine.swan-it.ru/issues/155920, пост 121
							   if (print) {
								   current_window.printAttachBlank();
							   }
						}
						if (action.result.Email_Error_Msg) {
							sw.swMsg.show({
								buttons: sw.swMsg.OK,
								title: langs('Предупреждение'),
								msg:action.result.Email_Error_Msg,
								icon: sw.swMsg.WARNING
							});
						}

						if (action.result.SMS_Error_Msg) {
							sw.swMsg.show({
								buttons: sw.swMsg.OK,
								title: langs('Предупреждение'),
								msg:action.result.SMS_Error_Msg,
								icon: sw.swMsg.WARNING
							});
						}

						current_window.hide();
						current_window.returnFunc();
					},
					failure: function(result_form, action) {

						current_window.formStatus = 'edit';
						loadMask.hide();
						if ( action.result )
						{
							if (action.result.Error_Msg)
							{
								if (action.result.Error_Code == '7') { //карта с таким номером уже есть в картотеке
									sw.swMsg.show({
										buttons: Ext.Msg.YESNO,
										fn: function ( buttonId ) {
											if ( buttonId == 'yes' )
											{
												var newParams = [];
												newParams.OverrideCardUniqueness = 1;
												current_window.formStatus = 'save';
												Ext.getCmp('PersonCardEditWindow').doSubmit(false, newParams);
											}
											else
											{
												form.getForm().findField('PersonCard_Code').enable();
											}
										},
										msg: langs('Карта с таким номером уже была заведена в вашем ЛПУ на другого человека. Все равно сохранить?'),
										title: langs('Проверка номера карты')
									});
								}
								else {
									sw.swMsg.show({
										buttons: sw.swMsg.OK,
										fn: function () {
											
											// номер карты оставляем для редактирования
											if ( current_window.action == 'add' )
											{
												form.getForm().findField('PersonCard_Code').enable();
											}
											
											// код, указывающий, что не надо закрывать форму после ошибки
											// необходимо стандартизировать коды ошибок
											if (!action.result.Error_Code || action.result.Error_Code != '333')
												current_window.hide();
										},
										title: langs('Ошибка'),
										msg:action.result.Error_Msg,
										icon: sw.swMsg.WARNING
									});
								}
							}
							else
							{
							   current_window.showMessage(langs('Ошибка'), langs('При сохранении произошли ошибки'), function() {
									current_window.hide();
								});
							}
					   }
					},
					url: C_PERSONCARD_SAVE
				});
			}
		}
	},
	closePersonCard: function() {
		if ( this.action == 'view' ) {
			return false;
		}

		var bf = this.findById('PersonCardEditForm').getForm();

		if ( bf.findField('CardCloseCause_id').disabled ) {
			bf.findField('CardCloseCause_id').enable();
		}

        bf.findField('CardCloseCause_id').setAllowBlank(false);
		bf.findField('PersonCard_endDate').setValue(Date.parseDate(getGlobalOptions().date, 'd.m.Y'));
	},
	doPreSave: function(print, options){
		var that = this;
		var form = this.findById('PersonCardEditForm');

		if(form.getForm().findField('PersonCard_id').getValue() < 0){
			// если мы вызывали форму swPersonCardHistoryWindow и вней удалили данное прикрепление
			// то всё просто закрываем, иначе выйдет ошибка т.к. обновлять нечего
			that.hide();
			that.returnFunc();
			return false;
		}


		var LpuAttachType_id = form.getForm().findField("LpuAttachType_id").getValue();

		//https://redmine.swan.perm.ru/issues/72339
		if(this.action == 'add' && getRegionNick() == 'perm' && LpuAttachType_id.inlist(['1','2'])){
			var LpuRegionType_Combo = form.getForm().findField('LpuRegionType_id');
			var LpuRegion_Combo = form.getForm().findField('LpuRegion_id');
			var LpuRegion_id = LpuRegion_Combo.getValue();
			var LpuRegion_Name = LpuRegion_Combo.getFieldValue('LpuRegion_Name');
			var LpuRegionType_Name = LpuRegionType_Combo.getFieldValue('LpuRegionType_Name');
			var request_params = {
				LpuRegion_id: LpuRegion_id
			};
			Ext.Ajax.request({
				url: '/?c=LpuStructure&m=loadLpuRegionInfo',
				params: request_params,
				success: function(response, opts) {
					var result = Ext.util.JSON.decode(response.responseText);
					var LpuSection_id = result[0].LpuSection_id;
					var LpuBuilding_id = result[0].LpuBuilding_id;
					var MedStaffRegion_id = result[0].MedStaffRegion_id;
					var Msg_text = "Внимание! Для участка " + LpuRegionType_Name + " № " + LpuRegion_Name + "</br> ";
					var Msg_text_add = "";
					if(LpuBuilding_id == 0)
						Msg_text_add += "Отсутствует информация о подразделении </br>";
					if(LpuSection_id == 0)
						Msg_text_add += "Отсутствует информация об отделении </br>";
					if(MedStaffRegion_id == 0)
						Msg_text_add += "Отсутствует врач на участке либо период работы врача на участке закрыт </br>";
					if(Msg_text_add.length > 0){
						Msg_text += Msg_text_add;
						sw.swMsg.show(
							{
								title: '',
								msg: Msg_text,
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								fn: function () {
									that.doSave(print, options);
								}
							})
					}
					else
						that.doSave(print, options);
				}
			});
		}
		else
			that.doSave(print, options);
	},
	doSave: function(print, options) {
	   if ( this.action == 'view'  || this.formStatus == 'save')
			return false;
		var form = this.findById('PersonCardEditForm');
	   
		if ( typeof options != 'object' ) {
			options = new Object();
		}
		this.formStatus = 'save';
		var that = this;
		// для ДМС более новый и корректный алгоритм, по ТЗ
		if ( this.isDMS )
		{
			if ( this.action == 'add' ) 
			{
				if (
					form.getForm().findField('PersonCard_DmsPolisNum').getValue() == ''
					|| !form.getForm().findField('PersonCard_endDate').isValid()
					|| !form.getForm().findField('PersonCard_DmsBegDate').isValid()
					|| !form.getForm().findField('PersonCard_DmsEndDate').isValid()
					|| !form.getForm().findField('OrgSMO_id').isValid()
				)
				{
					this.showMessage(langs('Сообщение'), langs('Не все поля формы заполнены корректно.'), function() {
						form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 100);
					});
					this.formStatus = 'edit';
					return false;
				}
				this.doSubmit();
				return true;
			}
			
			if ( this.action == 'edit' ) 
			{
				// если указана причина, то спрашиваем
				if ( form.getForm().findField('CardCloseCause_id').getValue() > 0 )
				{
					sw.swMsg.show({
						buttons: Ext.Msg.OKCANCEL,
						fn: function ( buttonId ) {
							if ( buttonId == 'ok' )
							{
								if ( 
									form.getForm().findField('PersonCard_DmsPolisNum').getValue() == ''
									|| !form.getForm().findField('PersonCard_endDate').isValid()
									|| !form.getForm().findField('PersonCard_DmsBegDate').isValid()
									|| !form.getForm().findField('PersonCard_DmsEndDate').isValid()
									|| !form.getForm().findField('OrgSMO_id').isValid()
								)
								{
									this.showMessage(langs('Сообщение'), langs('Не все поля формы заполнены корректно.'), function() {
										form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 100);
									});
									this.formStatus = 'edit';
									return false;
								}
								this.doSubmit();
							}
							else
							{
								this.formStatus = 'edit';
								form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 100);
							}
						}.createDelegate(this),
						msg: langs('Человек будет откреплен по ДМС.'),
						title: langs('Открепление по ДМС')
					});
					return false;
				}

				if ( 
					form.getForm().findField('PersonCard_DmsPolisNum').getValue() == ''
					|| !form.getForm().findField('PersonCard_endDate').isValid()
					|| !form.getForm().findField('PersonCard_DmsBegDate').isValid()
					|| !form.getForm().findField('PersonCard_DmsEndDate').isValid()
					|| !form.getForm().findField('OrgSMO_id').isValid()
				)
				{
					this.showMessage(langs('Сообщение'), langs('Не все поля формы заполнены корректно.'), function() {
						form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 100);
					});
					this.formStatus = 'edit';
					return false;
				}
				this.doSubmit();
				return true;
			}
		}

		var LpuAttachType_id = form.getForm().findField("LpuAttachType_id").getValue();
		var lpu_region_combo = form.getForm().findField("LpuRegion_id");
		var lpu_region_id = form.getForm().findField("LpuRegion_id").getValue();
		var lpu_region_type_id = 0;
		
		lpu_region_combo.getStore().each(
			function( record ) {
				if ( record.data.LpuRegion_id == lpu_region_id )
				{
					lpu_region_type_id = record.data.LpuRegionType_id;
					return true;
				}
			}
		);
		
		var valid = form.getForm().isValid();
		
		if ( this.action == 'edit' )
		{
			form.getForm().findField('LpuAttachType_id').disable();
			form.getForm().findField('PersonCard_begDate').disable();
			if ( form.getForm().findField("LpuAttachType_id").getValue() == 4 )
				form.getForm().findField('PersonCard_endDate').enable();
			else
				form.getForm().findField('PersonCard_endDate').disable();
			if ( form.getForm().findField("LpuAttachType_id").getValue() == 4 )
				form.getForm().findField('CardCloseCause_id').enable();
			else
				form.getForm().findField('CardCloseCause_id').setDisabled( form.getForm().findField('CardCloseCause_id').allowBlank );
			form.getForm().findField('Lpu_id').disable();	
		}
		else
		{
			form.getForm().findField('LpuAttachType_id').disable();
			form.getForm().findField('PersonCard_begDate').disable();
			form.getForm().findField('PersonCard_endDate').disable();
			form.getForm().findField('CardCloseCause_id').disable();
			form.getForm().findField('Lpu_id').disable();
		}

		// для суперадмина даем выбирать ЛПУ
		if ( this.action == 'add' && isSuperAdmin() )
		{
			form.getForm().findField('Lpu_id').enable();
		}
				
		if ( valid ){
			var person_inf_frame = this.findById('PCEF_PersonInformationFrame');
			if ( lpu_region_type_id == 3 && person_inf_frame.getFieldValue("Sex_id") == 1 )
			{
		        this.showMessage(langs('Сообщение'), langs('Пациент мужского пола не может быть прикреплен к гинекологическому участку.'), function() {
		        });
				this.formStatus = 'edit';
				return;
			}

			var person_age = this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_Age");
			if(this.action == 'add' && this.attachType == 2)	//https://redmine.swan.perm.ru/issues/72643
			{
				var params = new Object();
				params.Lpu_id = form.getForm().findField('Lpu_id').getValue();
				params.Person_id = form.findById('PCEF_Person_id').getValue();
				Ext.Ajax.request({
					url: '/?c=PersonCard&m=checkPersonDisp',
					params: params,
					callback: function (options, success, response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if(result[0].ctn>0){
							sw.swMsg.show({
								title: '',
								msg: 'Пациент состоит на диспансерном учете в других МО. Необходимо запросить устное согласие пациента с закрытием диспансерных карт в других МО. При получении положительного ответа нажмите "Продолжить", иначе - "Отказ"',
								icon: Ext.MessageBox.QUESTION,
								//buttons: Ext.Msg.YESNO,
								buttons: {yes: 'Продолжить', no: 'Отказ'},
								fn: function (buttonId) {
									if (buttonId == 'yes') {
										that.doSubmit();
									}
									else {
										that.formStatus = 'edit';
									}

								}

							})
						}else{
							that.doSubmit();
						}
					}
				});
			}
			else
			{
				if ( !options.ignoreControlPediatrAbove17Age ) {

					if ( lpu_region_type_id == 2 && person_age > 17 && form.getForm().findField('PersonCard_endDate').getValue().length < 1 )
					{
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								this.formStatus = 'edit';

								if ( 'yes' == buttonId ) {
									options.ignoreControlPediatrAbove17Age = true;
									this.doSave(print, options);
									this.formStatus = 'edit';
								}
								else {
									this.formStatus = 'edit';
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: langs('Тип участка "педиатрический", а человек старше 17ти лет. Продолжить?'),
							title: langs('Вопрос')
						});
						return false;
					}
				}

				this.doSubmit();
			}
		}
		else
		{
	        this.showMessage(langs('Сообщение'), langs('Не все поля формы заполнены корректно.'), function() {
				this.formStatus = 'edit';
	        }.createDelegate(this));
		}
    },
    enableEdit: function(enable) {
		var form = this.findById('PersonCardEditForm').getForm();
        //ФАПЫ!!!!!
        //alert(this.attachType);
        //if(this.attachType==1 && getRegionNick()=='perm')
		if(this.attachType==1 && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']))
        {
            form.findField('LpuRegion_Fapid').showContainer();
            form.findField('LpuRegionType_id').setFieldLabel(langs('Тип основного участка'));
            form.findField('LpuRegion_id').setFieldLabel(langs('Основной участок'));
        }
        else
        {
            form.findField('LpuRegion_Fapid').hideContainer();
            form.findField('LpuRegionType_id').setFieldLabel(langs('Тип участка'));
            form.findField('LpuRegion_id').setFieldLabel(langs('Участок'));
        }
        if(this.allowEditLpuRegion == 1)
        {
            Ext.getCmp('PCEF_PersonInformationFrame').hide();
            Ext.getCmp('PCEF_PersonCard_PAC').hide();
            Ext.getCmp('PCEF_PC_Period').hide();
            Ext.getCmp('LpuRegionFieldSet').setTitle('');
            form.findField('PersonCard_Code').hideContainer();
			form.findField('PersonCard_Code').setAllowBlank(true);
            form.findField('LpuAttachType_id').hideContainer();
            form.findField('PersonCardAttach').hideContainer();

            Ext.getCmp('PCEW_PersonCardAttachPanel').hide();
            form.findField('PersonCardMedicalIntervent').hideContainer();
            Ext.getCmp('PersonCardMedicalInterventPanel').hide();
            form.findField('Lpu_id').disable();
            form.findField('LpuRegionType_id').enable();
            form.findField('LpuRegion_id').enable();
			if(getRegionNick() != 'perm' && getRegionNick() != 'ufa' && getRegionNick() != 'penza')
            	form.findField('LpuRegion_Fapid').hideContainer();
			else
				form.findField('LpuRegion_Fapid').showContainer();
            this.buttons[1].hide();
            this.doLayout();
            this.syncSize();
        }
        else
        {
            Ext.getCmp('LpuRegionFieldSet').setTitle(langs('Прикрепление'));
            Ext.getCmp('PCEF_PersonInformationFrame').show();
            Ext.getCmp('PCEF_PersonCard_PAC').show();
            Ext.getCmp('PCEF_PC_Period').show();
            form.findField('PersonCard_Code').showContainer();
			form.findField('PersonCard_Code').setAllowBlank(false);
			if ( this.isDMS )
			{
				Ext.getCmp('PCEF_PersonCard_PAC').hide();
				form.findField('PersonCard_Code').hideContainer();
			}
            form.findField('LpuAttachType_id').showContainer();
            form.findField('PersonCardAttach').showContainer();
            Ext.getCmp('PCEW_PersonCardAttachPanel').show();
            form.findField('PersonCardMedicalIntervent').showContainer();
            Ext.getCmp('PersonCardMedicalInterventPanel').show();
            if(getRegionNick()!='astra')
            	this.buttons[1].show();
            this.doLayout();
            this.syncSize();
            if (enable === false)
            {
                Ext.getCmp('PCEF_PersonCard_PAC').disable();
                form.findField('PersonCard_Code').disable();
                form.findField('LpuAttachType_id').disable();
                form.findField('LpuRegionType_id').disable();
                form.findField('PersonCard_begDate').disable();
                form.findField('PersonCard_endDate').disable();
                form.findField('CardCloseCause_id').disable();
                form.findField('PersonCard_DmsPolisNum').disable();
                form.findField('PersonCard_DmsBegDate').disable();
                form.findField('PersonCard_DmsEndDate').disable();
                form.findField('OrgSMO_id').disable();
                form.findField('Lpu_id').disable();
                form.findField('LpuRegion_id').disable();
                form.findField('MedStaffFact_id').disable();
                form.findField('LpuRegion_Fapid').disable();
                this.buttons[0].disable();
				if(!Ext.isEmpty(form.findField('PersonCard_endDate').getValue())){
					form.findField('PersonCard_Code').disable();
				}
            }
            else
            {
                Ext.getCmp('PCEF_PersonCard_PAC').enable();
                form.findField('PersonCard_Code').enable();
                form.findField('LpuAttachType_id').enable();
                form.findField('PersonCard_begDate').enable();
                form.findField('PersonCard_endDate').enable();
                form.findField('CardCloseCause_id').enable();
                form.findField('PersonCard_DmsPolisNum').enable();
                form.findField('PersonCard_DmsBegDate').enable();
                form.findField('PersonCard_DmsEndDate').enable();
                form.findField('OrgSMO_id').enable();
                if(Ext.isEmpty(form.findField('PersonCard_endDate').getValue())) //Если прикрепление закрытое - менять участок нельзя!
                {
                    form.findField('LpuRegionType_id').enable();
                    form.findField('LpuRegion_id').enable();
                    if(getRegionNick().inlist(['astra','vologda']))
                    	form.findField('MedStaffFact_id').enable();
                    form.findField('LpuRegion_Fapid').enable();
                }
                else
                {
                    form.findField('LpuRegionType_id').disable();
                    form.findField('LpuRegion_id').disable();
                    form.findField('MedStaffFact_id').disable();
                    form.findField('LpuRegion_Fapid').disable();
					form.findField('PersonCard_Code').disable();
                }
                this.buttons[0].enable();
            }
			if(!Ext.isEmpty(form.findField('PersonCard_endDate').getValue())){
				Ext.getCmp('PCEF_PersonCard_PAC').disable();
				form.findField('PersonCard_Code').disable();
			}
			else
			{
				Ext.getCmp('PCEF_PersonCard_PAC').enable();
				form.findField('PersonCard_Code').enable();
			}
            if ( this.action == 'add' && isSuperAdmin() )
            {
                form.findField('Lpu_id').enable();
            }
        }
        if(Ext.isEmpty(form.findField('PersonCard_endDate').getValue() || form.findField('PersonCard_endDate').getValue() == '') && this.action == 'edit' && (getRegionNick() == 'ekb') && (!Ext.isEmpty(getGlobalOptions().allow_edit_attach_date) && getGlobalOptions().allow_edit_attach_date == 1) && isUserGroup('CardEditUser'))
        	form.findField('PersonCard_begDate').enable();

	},
	getPersonCardCode: function(lpu_id) {
		
		if ( this.isDMS )
			return true;
		var wnd = this;
		var form = this.findById('PersonCardEditForm');
		form.getForm().findField('PersonCard_Code').setValue('');
		form.getForm().findField('PersonAmbulatCard_id').setValue('');
		var params = {
			Person_id: Ext.getCmp('PCEF_Person_id').getValue(),
			Lpu_id : form.getForm().findField('Lpu_id').getValue(),
			AmbulatCardType_id : form.getForm().findField('LpuAttachType_id').getValue()
		};
		
		
		if ( this.action == 'add' && this.attachType == 1 )
		{
			params.CheckFond = 1;
		}
		form.getForm().findField('PersonCard_Code').getStore().load({params:params,baseParams:params})
	},
	showUploadDialog: function() {
		if(this.action != 'view'){
		this.UploadDialog.show();
		}
		else {
			alert(langs('Окно прикрепления документа недоступно в режиме просмотра.'));
		}
	},
	
	uploadSuccess: function(dialog, data) {
		this.addFileToFilesPanel(data);
	},
	
	getCountFiles: function() {
		return this.FilesPanel.items.items.length;
	},
	LpuRegionStoreFilter: function(){
		var form = this.findById('PersonCardEditForm'),
			lpu_region_combo = form.getForm().findField('LpuRegion_id'),
			LpuRegionType_SysNick = form.getForm().findField('LpuRegionType_id').getFieldValue('LpuRegionType_SysNick');
		if (!Ext.isEmpty(LpuRegionType_SysNick)){
			lpu_region_combo.getStore().filterBy(function(rec){
				return rec.get('LpuRegionType_SysNick') === LpuRegionType_SysNick;
			});
			checkValueInStore(form.getForm(), 'LpuRegion_id', 'LpuRegion_id', lpu_region_combo.getValue());
		}
	},
	setTitleFilesPanel: function() {
		var c = this.getCountFiles();
		if (c == 0) {
			var title = '<span style="color: gray;">нет приложенных документов</span>';
		} else {
			var tc = c.toString(), l = tc.length;
			var title = tc + ((tc.substring(l-1,1)=='1')?langs('Документ'):((tc.substring(l-1,1).inlist(['2','3','4']))?langs(' документа'): langs(' документов')));
		}
		this.FilesPanel.setTitle(langs('Список приложенных документов: ')+title);
	},

	addFileToFilesPanel: function(file) {
		if (file && file.name && file.size) {
			file.id = file.name.replace(/\./ig, '_');
			if(file.pmMediaData_id) file.id = file.id + '_' + file.pmMediaData_id; // file.name могут повторяться
			var html = '<div style="float:left;height:18px;">';
			// вот эта часть должна добавляться только к создаваемому письму
            var form = this.findById('PersonCardEditForm');
            if(this.action.inlist(['edit','view']) && !Ext.isEmpty(file.url))
            {
                html += '<a target="_blank" style="color: black; font-weight: bold;" href="'+file.url+'">'+file.name+'</a> ['+(file.size/1024).toFixed(2)+'Кб]';
            }
            else
            {
                html += '<b>'+file.name+'</b> ['+(file.size/1024).toFixed(2)+'Кб]';
            }
            if(this.action.inlist(['add','edit']) && Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue()))
            {
                html = html + ' <a href="#" onClick="Ext.getCmp(\''+this.id+'\').deleteFileToFilesPanel(\''+file.id+'\', \''+this.action+'\');">'+
                    '<img title="Удалить" style="height: 12px; width: 12px; vertical-align: bottom;" src="/img/icons/delete16.png" /></a>';
            }
			html = html + '</div>';
			if(this.FilesPanel.findById(file.id) != null) // Проверяем существует ли элемент с таким ид=)
				return false;
			this.FilesPanel.add({id: ''+file.id, border: false, html: html, settings: file});
			if(this.FilesPanel.collapsed)
				this.FilesPanel.expand();
			this.setTitleFilesPanel();
			this.FilesPanel.syncSize();
			this.FilesPanel.ownerCt.syncSize();
			this.doLayout();
		}
	},
	
	resetFilesPanel: function() {
		this.FilesPanel.removeAll();
		this.setTitleFilesPanel();
		this.FilesPanel.doLayout();
	},
	
	deleteFileToFilesPanel: function(id, action) {
		var win = this;
		var extItem = this.findById(''+id);
        var form = win.findById('PersonCardEditForm');
        var checkbox = form.getForm().findField('PersonCardAttach');
		if (extItem) {
            sw.swMsg.show({
                title: '',
                msg: langs('Вы действительно хотите удалить документ?'),
                buttons: Ext.Msg.YESNO,
                fn: function (buttonId) {
                    if (buttonId == 'yes') {
                        // фактическое удаление с диска (на стороне вебсервера надо проверять, может ли пользователь удалять эти файлы)
                        if(action == 'edit') {
                            /*if(!Ext.isEmpty(extItem.settings.url)) //https://redmine.swan.perm.ru/issues/63196
                            {
                                Ext.Ajax.request({
                                    url: '/?c=PersonCard&m=deleteFileFromPersonCard',
                                    params: extItem.settings,
                                    success: function(response, opts) {
                                        var obj = Ext.util.JSON.decode(response.responseText);
                                        if(!obj.success)
                                            return false;
                                        win.FilesPanel.remove(extItem, true);
                                        if (win.getCountFiles()==0) {
                                            //win.FilesPanel.collapse();
                                        }
                                        win.setTitleFilesPanel();
                                        win.FilesPanel.syncSize();
                                        win.FilesPanel.ownerCt.syncSize();
                                        win.doLayout();
                                    }
                                });
                            }
                            else
                            {
                                win.FilesPanel.remove(extItem, true);
                            }*/
							win.FilesPanel.remove(extItem, true);
                            if (win.getCountFiles()==0) {
                               // checkbox.enable();
                               //Ext.getCmp('uploadbutton').enable();
                                //checkbox.setValue(true);
                            }
							win.setTitleFilesPanel();
                            return false;
                        }

                        // а потом уже удаление из панели
                        win.FilesPanel.remove(extItem, true);
                        if (win.getCountFiles()==0) {
                            //checkbox.enable();
                            //Ext.getCmp('uploadbutton').enable();
                            //checkbox.setValue(true);
                        }
                        win.setTitleFilesPanel();
                        win.FilesPanel.syncSize();
                        win.FilesPanel.ownerCt.syncSize();
                        win.doLayout();
                    }

                }

            });
		}
	},
	
	id: 'PersonCardEditWindow',
	
    initComponent: function() {
		var win = this;
		this.UploadDialog = new Ext.ux.UploadDialog.Dialog({
			modal: true,
			title: langs('Прикрепление файлов'),
			url: '/?c=PersonCard&m=uploadFiles',
			reset_on_hide: true,
			allow_close_on_upload: true,
			listeners: {
				uploadsuccess: function(dialog, filename, data) {
					this.uploadSuccess(dialog, data);
				}.createDelegate(this)
			},
			upload_autostart: false
		});
		
		this.FilesPanel = new Ext.Panel({
			layout: 'form',
			//region: 'south',
			//plugins: [ Ext.ux.PanelCollapsedTitle ],
			title: 'Список приложенных документов: <span style="color: gray;">нет приложенных документов</span>',
			autoHeight: true,
			//frame: true,
            buttons: [
                {
                    handler: function() {
                        this.showUploadDialog();
                    }.createDelegate(this),
                    iconCls: 'add16',
                    id: 'uploadbutton',
					disabled: false,
                    tabIndex: 2107,
                    text: langs('Прикрепить документы'),
                    align: 'left'
                },
                '-'
            ],
			/*tbar: [{
				iconCls: 'add16',
				text: langs('Прикрепить документы'),
				handler: function() {
					this.showUploadDialog();
				}.createDelegate(this)
			}],*/
			animCollapse: false,
			listeners: {
				beforeexpand: function() {
					return this.getCountFiles() > 0;
				}.createDelegate(this),
				collapse: function() {
					this.syncSize();
				}.createDelegate(this),
				expand: function() {
					this.syncSize();
				}.createDelegate(this)
			},
			floatable: false,
			style: 'margin: 3px;',
			bodyStyle: 'padding: 5px;',
			titleCollapse: true,
			//collapsible: true,
			//collapsed: true,
			items: []
		});

		this.MedicalInterventGrid = new sw.Promed.ViewFrame({
			autoLoadData: false,
			border: false,
			id: 'MedicalInterventGrid',
			dataUrl: '/?c=PersonCard&m=loadPersonCardMedicalInterventGrid',
			region: 'center',
			height: 100,
			title: '',
			toolbar: false,
			saveAtOnce: false,
			saveAllParams: false,
			stringfields: [
				{ name: 'MedicalInterventType_id', type: 'int', header: 'ID', key: true },
				{ name: 'PersonCardMedicalIntervent_id', type: 'int', hidden: true },
				{ name: 'MedicalInterventType_Code', type: 'int', hidden: true },
				{ name: 'MedicalInterventType_Name', type: 'string', sortable: false, header: langs('Вид медицинского вмешательства'), id: 'autoexpand' },
				{ name: 'PersonMedicalIntervent_IsRefuse', sortable: false, type: 'checkcolumnedit', isparams: true, header: langs('Отказ'), width: 100 }
			],
			onLoadData: function() {
				var base_form = win.findById('PersonCardEditForm').getForm();
				var grid = win.MedicalInterventGrid.getGrid();
				var checkedCount = 0;
				grid.getStore().each(function(rec) {
					if (rec.get('PersonMedicalIntervent_IsRefuse') == 1) {
						checkedCount++;
					}
				});
				var medical_intervent_checkbox = base_form.findField('PersonCardMedicalIntervent');
				if (checkedCount > 0) {
					medical_intervent_checkbox.setValue(1);
				} else {
					medical_intervent_checkbox.setValue(0);
				}
				medical_intervent_checkbox.fireEvent('check', medical_intervent_checkbox, medical_intervent_checkbox.getValue());

				this.doLayout();
			},
			checkIsAgree: function() {

			},
			onAfterEdit: function(o) {
				o.record.commit();
			}
		});

        Ext.apply(this, {
            items: [ new sw.Promed.PersonInformationPanel({
            	button2Callback: function(callback_data) {
            		var current_window = Ext.getCmp('PersonCardEditWindow');

					current_window.findById('PCEF_Server_id').setValue(callback_data.Server_id);
	                current_window.findById('PCEF_PersonInformationFrame').load({Person_id: callback_data.Person_id, Server_id: callback_data.Server_id});
            	},
            	button1OnHide: function() {
            		var current_window = Ext.getCmp('PersonCardEditWindow');
					if (current_window.action == 'view')
					{
	    				current_window.buttons[5].focus();
	                }
            	},
            	button2OnHide: function() {
            		var current_window = Ext.getCmp('PersonCardEditWindow');
					if (current_window.action == 'view')
					{
	    				current_window.buttons[5].focus();
	                }
            	},
            	button3OnHide: function() {
            		var current_window = Ext.getCmp('PersonCardEditWindow');
					if (current_window.action == 'view')
					{
	    				current_window.buttons[5].focus();
	                }
            	},
            	button4OnHide: function() {
            		var current_window = Ext.getCmp('PersonCardEditWindow');
					if (current_window.action == 'view')
					{
	    				current_window.buttons[5].focus();
	                }
            	},
            	button5OnHide: function() {
            		var current_window = Ext.getCmp('PersonCardEditWindow');
					if (current_window.action == 'view')
					{
	    				current_window.buttons[5].focus();
	                }
            	},
            	id: 'PCEF_PersonInformationFrame'
            }),
            {layout:'fit',autoScroll:false,autoHeight: true,items:[
            new Ext.form.FormPanel({
                bodyBorder: false,
                bodyStyle: 'padding: 5px 5px 0',
                border: false,
                autoHeight: true,
                autoScroll:false,
                buttonAlign: 'left',
                frame: false,
				overflowY: 'scroll',
                id: 'PersonCardEditForm',
                labelAlign: 'right',
                labelWidth: 150,
                items: [{
					name: 'accessType',
					value: '',
					xtype: 'hidden'
				},
				{
                    id: 'PCEF_PersonCard_id',
                    name: 'PersonCard_id',
                    value: 0,
                    xtype: 'hidden'
                }, {
					id: 'PCEF_PersonCardEnd_insDT',
					name: 'PersonCardEnd_insDT',
					xtype: 'hidden'
				},
				{
					name: 'PersonCardAttach_id',
                    xtype: 'hidden'
				}, 
				{
					name: 'PersonAmbulatCard_id',
                    xtype: 'hidden'
				},{
                    name: 'PersonCard_IsAttachCondit',
					value: null,
                    xtype: 'hidden'
                }, {
                    id: 'PCEF_Person_id',
                    name: 'Person_id',
                    value: 0,
                    xtype: 'hidden'
                }, {
                    xtype: 'hidden',
                    id: 'PCEF_Server_id',
                    name: 'Server_id',
                    value: 0
                }, {
					xtype: 'hidden',
                    name: 'user_id'
				},  
				{
					layout:'column',
					border:false,
					items:[
						{
							layout:'form',
							border:false,
							items:[
								{
									allowBlank: false,
									fieldLabel: langs('№ амб. карты'),
									name: 'PersonCard_Code',
									listeners: {
										'change':function(c,n,o){
											var form = win.findById('PersonCardEditForm');
											form.getForm().findField('PersonAmbulatCard_id').setValue(n);
											
										}
									},
									hiddenName: 'PersonCard_Code',
									tabIndex: 2110,
									width: 200,
									xtype: 'swpersoncardcombo'
								}
							]
						},
						{
							layout:'form',
							border:false,
							items:[
								{
									disabled: false,
									handler: function () {
										var form = this.findById('PersonCardEditForm');
										var persinf = Ext.getCmp('PCEF_PersonInformationFrame');
										if(persinf.getFieldValue("Person_IsDead")==2)
										{
											sw.swMsg.show({
												buttons: sw.swMsg.OK,
												//title: 'Предупреждение',
												msg: 'Невозможно создать новую карту. Причина: смерть пациента',
												icon: sw.swMsg.WARNING
											});
											return false;
										}
										var params ={
											action:'add',
											Person_id: Ext.getCmp('PCEF_Person_id').getValue(),
											Server_id: Ext.getCmp('PCEF_Server_id').getValue(),
											AmbulatCardType_id:form.getForm().findField('LpuAttachType_id').getValue(),
											Lpu_id:form.getForm().findField('Lpu_id').getValue(),
											PersonFIO: persinf.getFieldValue('Person_Surname')+' '+persinf.getFieldValue('Person_Firname').substr(0,1)+' '+persinf.getFieldValue('Person_Secname').substr(0,1)
										};
										params.getCount = true;
										
										params.callback= function(data){
											form.getForm().findField('PersonAmbulatCard_id').setValue(data.PersonAmbulatCard_id);
											form.getForm().findField('PersonCard_Code').setValue(data.PersonAmbulatCard_Num);
										};
										Ext.Ajax.request({
											url: '/?c=PersonAmbulatCard&m=checkPersonAmbulatCard',
											params: params,
											callback: function (options, success, response) {
												var result = Ext.util.JSON.decode(response.responseText);
												 if(result[0].count>0){
													sw.swMsg.show({
														title: '',
														msg: langs('У пациента ')+params.PersonFIO+langs(' имеется ')+result[0].count+langs(' открытых амбулаторных карт. Вы действительно хотите продолжить создание новой карты?'),
														buttons: Ext.Msg.YESNO,
														fn: function (buttonId) {
															if (buttonId == 'yes') {
																getWnd('swPersonAmbulatCardEditWindow').show(params);
															}

														}

													})
												}else{
													getWnd('swPersonAmbulatCardEditWindow').show(params);
												}
											}
										});
										
										
									}.createDelegate(this),
									minWidth: 10,
									text: '<b>+</b>',
									id:'PCEF_PersonCard_PAC',
									topLevel: true,
									xtype: 'button'
								}
							]
						}
					]
				},{
					allowBlank: false,
					enableKeyEvents: true,
					hiddenName : "LpuAttachType_id",
					id: 'PCEW_LpuAttachTypeCombo',
					listeners: {
						'keydown': function (inp, e) {
                            if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
                            {
 								e.stopEvent();
							}
						},
						'change': function(combo, lpuAttachTypeId, oldValue) {
							var form = this.findById('PersonCardEditForm').getForm(),
								region_combo = form.findField('LpuRegion_id'),
								region_type_combo = form.findField('LpuRegionType_id');

							// если выбран тип участка стоматологический, то поле Участок необязательно для заполнения
							if ( lpuAttachTypeId == 3 || lpuAttachTypeId == 4 )
								region_combo.setAllowBlank(true);
							else
								region_combo.setAllowBlank(false);
							
							// чистим фильтр типов участков
							region_type_combo.getStore().clearFilter();

							// фильтруем список типов участков
							// для Казахстана отображать все типы участков https://redmine.swan.perm.ru/issues/70639#note-139
							if ( lpuAttachTypeId > 0 && getRegionNick() != 'kz' )
							{
								if ( lpuAttachTypeId == 4 ) {
									//region_type_combo.setFieldValue('LpuRegionType_SysNick', 'slug');
									//region_type_combo.fireEvent('change', region_type_combo, region_type_combo.getValue(), region_type_combo.getValue());
									//return true;
								}
								if(getRegionNick()=='ufa')
								{
									var options = getGlobalOptions();
									var date = Date.parseDate(options['date'], 'd.m.Y');
									var request_params = {
										HolderDate: Ext.isEmpty(form.findField('PersonCard_begDate').getValue())?date:form.findField('PersonCard_begDate').getValue(),
										Lpu_id: form.findField('Lpu_id').getValue()
									};
									win.LpuRegionsByDate = [];
									Ext.Ajax.request({
										url: '/?c=LpuPassport&m=loadLpuPeriodFondHolderGrid',
										params: request_params,
										callback: function(options,success,response){
											if(success) {
												var resp = Ext.util.JSON.decode(response.responseText);
												if(resp.length>0){
													var i = 0;
													for (i=0;i<resp.length;i++)
														win.LpuRegionsByDate.push(resp[i].LpuRegionType_SysNick);
													win.filterRegionType();
												}
											}
										}
									});
								}
								else
									win.filterRegionType();
								//win.filterRegionType();
							}
							else
							{
								region_type_combo.getStore().clearFilter();
							}
						}.createDelegate(this)
					},
					listWidth: 300,
					tabIndex: 2100,
					width: 200,
					xtype : "swlpuattachtypecombo"
                },
				{
					autoHeight: true,
					id: 'PCEW_DmsPolisFieldset',
					items: [{
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: langs('№ полиса'),
						name: 'PersonCard_DmsPolisNum',
						selectOnFocus: false,
						tabIndex: 2101,
						width: 200,
						autoCreate: {tag: "input", type: "text", size: "32", maxLength: "32", autocomplete: "off"},
						xtype: 'textfield'
					}, {
						allowBlank: false,
   						fieldLabel: langs('Дата начала'),
               			format: 'd.m.Y',
						name: 'PersonCard_DmsBegDate',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						tabIndex: 2102,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
   						fieldLabel: langs('Дата окончания'),
               			format: 'd.m.Y',
						name: 'PersonCard_DmsEndDate',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						listeners: {
							'change': function(inp, new_val) {
								Ext.getCmp('PCEW_PersonCard_endDate').setValue(new_val);
							}
						},
						tabIndex: 2102,
						xtype: 'swdatefield'
					}, {
						id: 'PCEW_OrgSMO_id',
						tabIndex: 2102,
						allowBlank: false,
						xtype: 'sworgsmocombo',
						minChars: 1,
						queryDelay: 1,
						fieldLabel: langs('СМО'),
						hiddenName: 'OrgSMO_id',
						lastQuery: '',
						listWidth: '500',
						onTrigger2Click: function() {
							if ( this.disabled )
								return;
							var combo = this;
							getWnd('swOrgSearchWindow').show({
								onSelect: function(orgData) {
									if ( orgData.Org_id > 0 )
									{
										combo.setValue(orgData.Org_id);
										combo.focus(true, 500);
										combo.fireEvent('change', combo);
									}

									getWnd('swOrgSearchWindow').hide();
								},
								onClose: function() {combo.focus(true, 200)},
								object: 'smodms'
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
						},
						width: 400
					}],
					style: 'padding: 0px;',
					title: langs('Полис ДМС'),
					xtype : 'fieldset'
				},
				{
                	autoHeight: true,
					items: [{
						allowBlank: false,
   						fieldLabel: langs('Дата прикрепления'),
               			format: 'd.m.Y',
						name: 'PersonCard_begDate',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						tabIndex: 2101,
						xtype: 'swdatefield',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var form = this.ownerCt.ownerCt;
                                if(getRegionNick()=='pskov' && win.AttachType==1)
                                {
                                    var checkbox = form.getForm().findField('PersonCardAttach');
                                    if(newValue.format('Y-m-d') <= '2015-12-31' && win.otherCardExists == 0)
                                    {
                                        checkbox.enable();
                                    }
                                    else
                                    {
                                        checkbox.disable();
                                    }
                                }
								if(getRegionNick()=='ufa')
								{
									var request_params = {
										HolderDate: newValue,
										Lpu_id: form.getForm().findField('Lpu_id').getValue()
									};
									win.LpuRegionsByDate = [];
									Ext.Ajax.request({
										url: '/?c=LpuPassport&m=loadLpuPeriodFondHolderGrid',
										params: request_params,
										callback: function(options,success,response){
											if(success) {
												var resp = Ext.util.JSON.decode(response.responseText);
												if(resp.length>0){
													var i = 0;
													for (i=0;i<resp.length;i++)
														win.LpuRegionsByDate.push(resp[i].LpuRegionType_SysNick);
													win.filterRegionType();
												}
											}
										}
									});
								}
								else
									win.filterRegionType();

								blockedDateAfterPersonDeath('personpanelid', 'PCEF_PersonInformationFrame', field, newValue, oldValue);
							}
						}
					}, {
						fieldLabel: langs('Дата открепления'),
               			format: 'd.m.Y',
						minValue: getGlobalOptions().date,
						name: 'PersonCard_endDate',
						id: 'PCEW_PersonCard_endDate',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						tabIndex: 2102,
						validateOnBlur: false,
						listeners: {
							'change': function(combo, new_value) {
								var form = this.ownerCt.ownerCt;
								if ( form.getForm().findField("LpuAttachType_id").getValue() == 4 || (((isUserGroup('CardCloseUser') || isSuperAdmin()) && getRegionNick() != 'perm') || (isUserGroup('CardCloseUser') &&isSuperAdmin() && getRegionNick() == 'perm')))
								{
									if ( !new_value || new_value == '' )
									{
										form.getForm().findField('CardCloseCause_id').setAllowBlank(true);
									}
									else
									{
										form.getForm().findField('CardCloseCause_id').setAllowBlank(false);
									}
                                    if(Ext.isEmpty(form.getForm().findField("LpuRegionType_id").getValue()))
                                        form.getForm().findField('LpuRegionType_id').enable();
                                    if(Ext.isEmpty(form.getForm().findField("LpuRegion_id").getValue()))
                                        form.getForm().findField('LpuRegion_id').enable();
								}
							}
						},
						xtype: 'swdatefield'
					}, {
						hiddenName: 'CardCloseCause_id',
						listWidth: 300,
						tabIndex: 2103,
						width: 200,
						xtype: 'swcardclosecausecombo',
						listeners: {
							'change': function(combo, new_value) {
								var form = this.ownerCt.ownerCt;
								if ( form.getForm().findField("LpuAttachType_id").getValue() == 4 || (((isUserGroup('CardCloseUser') || isSuperAdmin()) && getRegionNick() != 'perm') || (isUserGroup('CardCloseUser') && isSuperAdmin() && getRegionNick() == 'perm')))
								{
									if ( !new_value || new_value == '' )
									{
										form.getForm().findField('PersonCard_endDate').setAllowBlank(true);
									}
									else
									{
										form.getForm().findField('PersonCard_endDate').setAllowBlank(false);
									}
                                    if(Ext.isEmpty(form.getForm().findField("LpuRegionType_id").getValue()))
                                        form.getForm().findField('LpuRegionType_id').enable();
                                    if(Ext.isEmpty(form.getForm().findField("LpuRegion_id").getValue()))
                                        form.getForm().findField('LpuRegion_id').enable();
								}
							},
							'expand': function(combo){
								combo.getStore().filterBy(
									function(row) {
										if ( row.get('CardCloseCause_id') == 9 || row.get('CardCloseCause_id') == 10 )
											return false;
										return true;
									}
								);
							}
						}
					}],
					style: 'padding: 0px;',
					title: langs('Период прикрепления'),
                    id: 'PCEF_PC_Period',
					xtype : "fieldset"
                }, {
                	autoHeight: true,
					items: [
					new sw.Promed.SwLpuSearchCombo({
						fieldLabel: langs('ЛПУ'),
						allowBlank: false,
						id: 'PCEF_Lpu_id',
						hiddenName: 'Lpu_id',
						listeners: {
							'change': function(combo, lpuId) {
								/*
								 * так как у нас не происходит смена ЛПУ из формы, то и не надо
								 * надо только для админа
								 */
                                var form = this.ownerCt.ownerCt;
								if ( isSuperAdmin() )
								{
									var region_combo = form.getForm().findField('LpuRegion_id');
									var lpu_region_id = region_combo.getValue();
									form.getForm().findField('LpuRegion_id').getStore().removeAll();
									var params = {
										Object: 'LpuRegion'
									}
									params.LpuAttachType_id = form.getForm().findField('LpuAttachType_id').getValue();

									form.getForm().findField('LpuRegion_id').getStore().load({
										params: {
											Lpu_id: lpuId,
											Object: 'LpuRegion',
											showOpenerOnlyLpuRegions: 1
										}
									});

								}
                                var region_fap_combo = form.getForm().findField('LpuRegion_Fapid');
                                var params = new Object();
                                params.Lpu_id = form.getForm().findField('Lpu_id').getValue();
                                if(!Ext.isEmpty(form.getForm().findField('PersonCard_begDate').getValue()))
                                    params.LpuRegion_date = form.getForm().findField('PersonCard_begDate').getValue().format('Y-m-d');
                                region_fap_combo.getStore().load({
                                    params: params
                                });
							}
						},
						listWidth: 500,
						tabIndex: 2104,
						width: 400
					}),
					{
						allowBlank: false,
						enableKeyEvents: true,
						lastQuery: '',
						hiddenName : "LpuRegionType_id",
						id: 'PCEW_LpuRegionTypeCombo',
						listeners: {
							'change': function(combo, lpuRegionTypeId, oldLpuRegionTypeId) {
								var form = this.ownerCt.ownerCt;
								var region_combo = form.getForm().findField('LpuRegion_id'),
									LpuRegionType_SysNick = combo.getFieldValue('LpuRegionType_SysNick'),
									attach_type_combo = form.getForm().findField('LpuAttachType_id'),
									curLpu_id = form.getForm().findField('Lpu_id').getValue();

								if(win.action=='add')
									form.getForm().findField('LpuRegion_id').enable();
								if ( lpuRegionTypeId != oldLpuRegionTypeId || region_combo.getStore().getCount() == 0 ) {
									var lpu_region_id = region_combo.getValue();

									region_combo.getStore().load({
                                        params: {
                                            Lpu_id: form.getForm().findField('Lpu_id').getValue(),
                                            LpuAttachType_id: form.getForm().findField('LpuAttachType_id').getValue(),
                                            LpuRegionType_id: lpuRegionTypeId,
                                            Object: 'LpuRegion',
                                            showOpenerOnlyLpuRegions: 1 // отображать только открытые участки
                                        },
                                        callback: function(resp){
                                        	if(!Ext.isEmpty(resp) && resp.length == 1){ //Если вернулся всего один участок, то устанавливаем его.
												region_combo.setValue(resp[0].id);
												region_combo.fireEvent('change', region_combo, region_combo.getValue());
                                        	} else {
	                                        	if(win.action=='add'){
													var request_params = {
														Person_id: Ext.getCmp('PCEF_Person_id').getValue(),
														LpuRegionType_id: lpuRegionTypeId,
														Lpu_id: curLpu_id
													};
													Ext.Ajax.request({
														url: '/?c=PersonCard&m=getLpuRegionByAddress',
														params: request_params,
														callback: function(options,success,response) {
															if(success) {
																var resp = Ext.util.JSON.decode(response.responseText);
																if(resp && resp[0]){
																	var lpuRegion_id = resp[0].LpuRegion_id;
																	var lpuregioncombo = form.getForm().findField('LpuRegion_id');
																	var index = lpuregioncombo.getStore().findBy(function(rec) {
																		return (rec.get('LpuRegion_id') == lpuRegion_id);
																	});
																	if(index >= 0){
																		var lpuregioncombo_rec = lpuregioncombo.getStore().getAt(index);
																		form.getForm().findField('LpuRegion_id').setValue(lpuregioncombo_rec.id);
																		lpuregioncombo.fireEvent('change', lpuregioncombo, lpuregioncombo.getValue());
																	}
																}
															}
														}
													});
	                                        	}
                                        	}

                                        	/*var index = region_combo.getStore().findBy(function(record, id) {
												if ( record.data.LpuRegionType_id == lpuRegionTypeId && record.data.LpuRegion_id == lpu_region_id )
													return true;
												else
													return false;
											});
											alert(index);
											if ( index == -1 )
											{
												form.getForm().findField('LpuRegion_id').clearValue();
											}*/
											win.LpuRegionStoreFilter();
                                        }
                                    });
									/*var index = region_combo.getStore().findBy(function(record, id) {
										if ( record.data.LpuRegionType_id == lpuRegionTypeId && record.data.LpuRegion_id == lpu_region_id )
											return true;
										else
											return false;
									});
									if ( index == -1 )
									{
										form.getForm().findField('LpuRegion_id').clearValue();
									}*/
									/*var params = {
										Object: 'LpuRegion'
									}
									if ( lpuRegionTypeId > 0 ) 
									{
										params.LpuRegionType_id = lpuRegionTypeId;
										region_combo.enable();
									}
									else
									{
										params.LpuAttachType_id = form.getForm().findField('LpuAttachType_id').getValue();
										region_combo.disable();
									}*/
									if(form.getForm().findField('LpuAttachType_id').getValue()==4 && getRegionNick()=='ufa')
									{
										Ext.getCmp('PCEW_PrintAttachBlankButton').setText("<u>П</u>ечать инф. согласия/ отказа");
									}
									else
									{
										Ext.getCmp('PCEW_PrintAttachBlankButton').setText("<u>П</u>ечать заявления");
									}
									//win.LpuRegionStoreFilter();
								}
							}
						},
						tabIndex: 2104,
						width: 400,
						xtype : "swlpuregiontypecombo"
					},
					{
						allowBlank: false,
						displayField: 'LpuRegion_Name',
	                    fieldLabel: langs('Участок'),
						title: langs('Смена участка в карте возможна только в день прикрепления'),
						forceSelection: true,
                        id:'PCEW_LpuRegion_id',
	                    hiddenName: 'LpuRegion_id',
						listeners: {
                            'blur': function(combo) {
                                if (combo.getRawValue()=='')
									combo.clearValue();
							},
							'change': function(combo, lpuRegionId) {
								var lpu_region_type_id = 0;
								combo.getStore().each(
									function( record ) {
										if ( record.data.LpuRegion_id == lpuRegionId )
										{
											lpu_region_type_id = record.data.LpuRegionType_id;
											return true;
										}
									}
								);
								var lpuattachtype_combo = Ext.getCmp('PCEW_LpuAttachTypeCombo');
								if ( lpu_region_type_id > 0 && Ext.isEmpty(lpuattachtype_combo.getValue()))
								{
									if ( lpuattachtype_combo.getValue() == 4 )
										return true;
									lpuattachtype_combo.clearValue();
									switch(lpu_region_type_id)
									{
										case '1':
											lpuattachtype_combo.setValue('1');
										break;
										case '2':
											lpuattachtype_combo.setValue('1');
										break;
										case '4':
											lpuattachtype_combo.setValue('1');
										break;
										case '3':
											lpuattachtype_combo.setValue('2');
										break;
										case '5':
											lpuattachtype_combo.setValue('3');
										break;
										case '6':
											lpuattachtype_combo.setValue('4');
										break;
									}
								}						
								var form = this.ownerCt.ownerCt;

								if (getRegionNick().inlist(['astra','vologda'])) {
									var msf_combo = form.getForm().findField('MedStaffFact_id');
									msf_combo.getStore().load({
										params: {
											Lpu_id: getGlobalOptions().lpu_id,
											LpuRegion_id: lpuRegionId,
											showClosed: 1
										},
										callback: function(res,par) {
											var index = msf_combo.getStore().findBy(function(rec) {
												return (rec.get('MedStaffRegion_isMain') == '2');
											});
											if(index >= 0)
											{
												var msf_rec = msf_combo.getStore().getAt(index);
												msf_combo.setValue(msf_rec.get('MedStaffFact_id'));
											}
										}
									});
								}

							}
						},
						minChars: 1,
						mode: 'local',
						queryDelay: 1,
						setValue: function(v) {
							var text = v;
							if(this.valueField){
								var r = this.findRecord(this.valueField, v);
								if(r){								
									text = r.data[this.displayField];
									if ( !(String(r.data['LpuRegion_Descr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_Descr']) == "") )
									{
										if (r.data['LpuRegion_Descr']) {
											text = text + ' ( '+ r.data['LpuRegion_Descr'] + ' )';
										}
									}
								} else if(this.valueNotFoundText !== undefined){
									text = this.valueNotFoundText;
								}
							}
							this.lastSelectionText = text;
							if(this.hiddenField){
								this.hiddenField.value = v;
							}
							Ext.form.ComboBox.superclass.setValue.call(this, text);
							this.value = v;
						},
						lastQuery: '',
	                    store: new Ext.data.Store({
	                        autoLoad: false,
	                        reader: new Ext.data.JsonReader({
	                            id: 'LpuRegion_id'
	                        }, [
	                            {name: 'LpuRegion_Name', mapping: 'LpuRegion_Name'},
	                            {name: 'LpuRegion_id', mapping: 'LpuRegion_id'},
								{name: 'LpuRegion_Descr', mapping: 'LpuRegion_Descr'},
								{name: 'LpuRegionType_id', mapping: 'LpuRegionType_id'},
								{name: 'LpuRegionType_SysNick', mapping: 'LpuRegionType_SysNick'},
								{name: 'LpuRegionType_Name', mapping: 'LpuRegionType_Name'}
	                        ]),
							listeners: {
								'load': function(store) {
									win.LpuRegionStoreFilter();
								}.createDelegate(this)
							},
	                        url: C_LPUREGION_LIST
	                    }),
						tabIndex: 2106,
						tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_Name} {[ (!values.LpuRegion_Descr || String(values.LpuRegion_Descr).toUpperCase() == "NULL" || String(values.LpuRegion_Descr) == "") ? "" : "( "+ values.LpuRegion_Descr +" )"]}</div></tpl>',
	                    triggerAction: 'all',
						typeAhead: true,
						typeAheadDelay: 1,
	                    valueField: 'LpuRegion_id',
						width : 400,
						xtype: 'combo'
					},
						{
						allowBlank: !getRegionNick().inlist(['astra','vologda']),
						width: 400,
						hidden: !getRegionNick().inlist(['astra','vologda']),
						hideLabel: !getRegionNick().inlist(['astra','vologda']),
						displayField: 'MedPersonal_FIO',
						fieldLabel: langs('Врач'),
						hiddenName: 'MedStaffFact_id',
						id: 'PCMedStaffFact_id',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'MedStaffFact_id', type: 'int' },
								{ name: 'MedPersonal_FIO', type: 'string' },
								{ name: 'msr_descr', type: 'string'},
								{ name: 'MedStaffRegion_isMain', type: 'string'}
							],
							key: 'MedStaffFact_id',
							sortInfo: {
								field: 'MedStaffFact_id'
							},
							url: '/?c=LpuStructure&m=getMedStaffRegion'
						}),
						tabIndex: 2107,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{MedPersonal_FIO}&nbsp;<font color="red">{msr_descr}</font>',
							'</div></tpl>'
						),
						valueField: 'MedStaffFact_id',
						xtype: 'swbaselocalcombo'
					},
                        {
                            allowBlank: true,
                            displayField: 'LpuRegion_FapName',
                            fieldLabel: langs('ФАП Участок'),
                            title: langs('Смена ФАП участка в карте возможна только в день прикрепления'),
                            forceSelection: true,
                            hiddenName: 'LpuRegion_Fapid',
                            minChars: 1,
                            mode: 'local',
                            queryDelay: 1,
                            setValue: function(v) {
                                var text = v;
                                if(this.valueField){
                                    var r = this.findRecord(this.valueField, v);
                                    if(r){
                                        text = r.data[this.displayField];
                                        if ( !(String(r.data['LpuRegion_FapDescr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_FapDescr']) == "") )
                                        {
                                            if (r.data['LpuRegion_FapDescr']) {
                                                text = text + ' ( '+ r.data['LpuRegion_FapDescr'] + ' )';
                                            }
                                        }
                                    } else if(this.valueNotFoundText !== undefined){
                                        text = this.valueNotFoundText;
                                    }
                                }
                                this.lastSelectionText = text;
                                if(this.hiddenField){
                                    this.hiddenField.value = v;
                                }
                                Ext.form.ComboBox.superclass.setValue.call(this, text);
                                this.value = v;
                            },
                            lastQuery: '',
                            store: new Ext.data.Store({
                                autoLoad: true,
                                reader: new Ext.data.JsonReader({
                                    id: 'LpuRegion_Fapid'
                                }, [
                                    {name: 'LpuRegion_FapName', mapping: 'LpuRegion_FapName'},
                                    {name: 'LpuRegion_Fapid', mapping: 'LpuRegion_Fapid'},
                                    {name: 'LpuRegion_FapDescr', mapping: 'LpuRegion_FapDescr'}
                                ]),
                                /*listeners: {
                                    'load': function(store) {
                                        win.LpuRegionStoreFilter();
                                    }.createDelegate(this)
                                },*/
                                url: '/?c=LpuRegion&m=getLpuRegionListFeld'
                            }),
                            tabIndex: 2106,
                            tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_FapName}</div></tpl>',
                            triggerAction: 'all',
                            typeAhead: true,
                            typeAheadDelay: 1,
                            valueField: 'LpuRegion_Fapid',
                            width : 400,
                            xtype: 'combo'
                        }
                    ],
					style: 'padding: 5px;',
					title: langs('Прикрепление'),
                    id: 'LpuRegionFieldSet',
					xtype : "fieldset"
                },
					{
						//Добавил этот кусок в рамках задачи https://redmine.swan.perm.ru/issues/26087 - теперь "Заявление" отдельным чекбоксом! Ура!
						xtype:'checkbox',
						hideLabel:true,
						name:'PersonCardAttach',
						id: 'PersonCardAttach',
						boxLabel: langs('Заявление'),
						disabled: true,
						checked: false,
						listeners: {
							'check': function(c,v) {
								/*var w = Ext.getCmp(win.id);
								var ft = w.find("xtype", "fieldset")[3];
								if(v==true){
									ft.expand();
								}
								else{
									ft.collapse();
								}
								w.syncSize();*/
							}
						}
					},
					{
					style: 'padding: 0px;',

					xtype: 'fieldset',
					//collapsed: true,
					name: 'PersonCardAttachPanel',
                    id: 'PCEW_PersonCardAttachPanel',
					collapsed: false,
					autoHeight: true,
					layout: 'form',
					items: [
						this.FilesPanel
					]
				}, {
					xtype:'checkbox',
					hideLabel:true,
					name:'PersonCardMedicalIntervent',
					id: 'PersonCardMedicalIntervent',
					hidden: (getRegionNick()=='kz'),
					boxLabel: langs('Отказ от медицинских вмешательств'),
					//checked: true,
					listeners: {
						'check': function(c,v) {
							var w = Ext.getCmp(win.id);
							var ft = w.find("xtype", "fieldset")[4];
							if(v==true){
								ft.expand();
							}
							else{
								ft.collapse();
							}
							w.syncSize();
						}
					}
				}, {
					style: 'padding: 0px;',
					xtype: 'fieldset',
					name: 'PersonCardMedicalInterventPanel',
                    id: 'PersonCardMedicalInterventPanel',
					hidden: (getRegionNick()=='kz'),
					collapsed: false,
					autoHeight: true,
					layout: 'form',
					items: [win.MedicalInterventGrid]
				}],
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

                        if (Ext.isIE)
                        {
                        	e.browserEvent.keyCode = 0;
                        	e.browserEvent.which = 0;
                        }

			            if (e.getKey() == Ext.EventObject.F6)
			            {
			            	Ext.getCmp('PCEF_PersonInformationFrame').panelButtonClick(1);
			            	return false;
			            }

			            if (e.getKey() == Ext.EventObject.F10)
			            {
			            	Ext.getCmp('PCEF_PersonInformationFrame').panelButtonClick(2);
			            	return false;
			            }

			            if (e.getKey() == Ext.EventObject.F11)
			            {
			            	Ext.getCmp('PCEF_PersonInformationFrame').panelButtonClick(3);
			            	return false;
			            }

			            if (e.getKey() == Ext.EventObject.F12)
			            {
			            	if (e.CtrlKey == true)
			            	{
				            	Ext.getCmp('PCEF_PersonInformationFrame').panelButtonClick(5);
				            }
				            else
				            {
				            	Ext.getCmp('PCEF_PersonInformationFrame').panelButtonClick(4);
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
                    	switch (e.getKey())
                    	{
                    		case Ext.EventObject.C:
                    		    if (this.action != 'view')
                    		    {
                        		    //this.doSave(false);
									this.doPreSave(print, options);
                                }
                    		break;

                    		case Ext.EventObject.G:
								this.printAttachBlank();
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
                reader: new Ext.data.JsonReader({
                    success: function() { }
                }, [
					{name: 'accessType'},
                    {name: 'PersonCard_Code'},
					{name: 'PersonCard_DmsPolisNum'},
					{name: 'PersonCard_DmsBegDate'},
					{name: 'PersonCard_DmsEndDate'},
					{name: 'OrgSMO_id'},
                    {name: 'LpuAttachType_id'},
					{name: 'PersonCard_IsAttachCondit'},
                    {name: 'LpuRegionType_id'},
                    {name: 'PersonCard_begDate'},
                    {name: 'PersonCard_endDate'},
					{name: 'PersonCardEnd_insDT'},
                    {name: 'CardCloseCause_id'},
                    {name: 'Lpu_id'},
                    {name: 'LpuRegion_id'},
                    {name: 'MedStaffFact_id'},
                    {name: 'LpuRegion_Fapid'},
					{name: 'PersonAmbulatCard_id'},
                    {name: 'PersonCardAttach_id'}
                ]),
                url: '/?c=PersonCard&m=getPersonCard'
            })
		]}],
			buttons: [
				{
                    handler: this.doPreSave.createDelegate(this, [false]),//this.doSave.createDelegate(this, [false]),
					iconCls: 'save16',
					id: 'PCEW_SaveButton',
					tabIndex: 2108,
                    text: BTN_FRMSAVE
                }, {
					handler: this.printAttachBlank.createDelegate(this),
                    iconCls: 'print16',
					id: 'PCEW_PrintAttachBlankButton',
					tabIndex: 2109,
					hidden: (getRegionNick()=='astra'),
                    text: langs('<u>П</u>ечать заявления')
                 }, {
                    handler: this.closePersonCard.createDelegate(this),
        			iconCls: 'cancel16',
					id: 'PCEW_closeNotBdzButton',
					tabIndex: 2110,
					tooltip: langs('Снять с учета'),
                    text: langs('Снять с учета'),
					hidden: true
                },
				'-',
				HelpButton(this, -1),
				{
					handler: function() {
						this.onCancelAction();
					}.createDelegate(this),
        			iconCls: 'cancel16',
                    tabIndex: 2111,
                    text: BTN_FRMCANCEL,
					onTabAction: function( el ) {
						var form = Ext.getCmp('PersonCardEditForm');
						var person_card_code_field = form.getForm().findField('PersonCard_Code');
						var lpu_region_type_combo = form.getForm().findField('LpuRegionType_id');
						if ( person_card_code_field.disabled )
						{
							lpu_region_type_combo.focus(true, 100);
						}
					}
                }
			]
        });
    	sw.Promed.swPersonCardEditWindow.superclass.initComponent.apply(this, arguments);
    },
    layout: 'form',
    listeners: {
    	'hide': function() {
			Ext.getCmp('PCEW_LpuRegionTypeCombo').getStore().clearFilter();
			var bf = this.findById('PersonCardEditForm').getForm();
			bf.reset();
			this.resetFilesPanel();
			bf.findField('CardCloseCause_id').setAllowBlank(true);
    		this.onHide();
    	}
    },
	loadLpuRegionType: function(config) {
		var form = this.findById('PersonCardEditForm');
		var region_type_combo = form.getForm().findField('LpuRegionType_id');
		if (region_type_combo.getStore().getCount()==0) {
			region_type_combo.getStore().load((config && config.callback)?config:{});
			region_type_combo.getStore().clearFilter();
		} else {
			if (config && config.callback) {
				config.callback();
			}
		}
	},
	loadListsAndFormData: function(loadForm)
	{
		var current_window = this;
		var form = this.findById('PersonCardEditForm');
		var person_card_id = form.getForm().findField('PersonCard_id').getValue();
		form.getForm().findField('LpuRegion_id').clearValue();
		form.getForm().findField('LpuRegion_id').getStore().removeAll();
		form.getForm().findField('MedStaffFact_id').clearValue();
		form.getForm().findField('MedStaffFact_id').getStore().removeAll();
		if ( loadForm === true )
		{
	    	var loadMask = new Ext.LoadMask(Ext.get('PersonCardEditWindow'), {msg: LOAD_WAIT});
			loadMask.show();
			form.getForm().load({
				failure: function() {
    		    	loadMask.hide();
				    current_window.showMessage(langs('Сообщение'), langs('Произошла ошибка получения данных формы.'), function() {
		   				current_window.hide();
					});
		        },
   				params: {
        			PersonCard_id: person_card_id
		        },
   				success: function(f, a) {
					var response = Ext.util.JSON.decode(a.response.responseText)[0];
        			loadMask.hide();
					var lpu_id = form.getForm().findField('Lpu_id').getValue();
					var region_type_combo = form.getForm().findField('LpuRegionType_id');
					var lpu_attach_type_id = form.getForm().findField('LpuAttachType_id').getValue();
					var lpu_region_type_id = region_type_combo.getValue();
					// если не заполнен тип участка то заполним его согласно типу прикрепления. (refs #17403)
					//if (Ext.isEmpty(lpu_region_type_id)) {
					//	form.getForm().findField('LpuAttachType_id').fireEvent('change', form.getForm().findField('LpuAttachType_id'), form.getForm().findField('LpuAttachType_id').getValue());
					//}
					form.getForm().findField('LpuAttachType_id').fireEvent('change', form.getForm().findField('LpuAttachType_id'), form.getForm().findField('LpuAttachType_id').getValue());
					var person_isbdz = ( 1==Ext.getCmp('PCEF_PersonInformationFrame').getFieldValue('Person_IsBDZ') );
					var pc_isattachcondit = ( 2==form.getForm().findField('PersonCard_IsAttachCondit').getValue() );
					var is_curday = !Ext.isEmpty(response.PersonCardBeg_insDT)?(response.PersonCardBeg_insDT == getGlobalOptions().date):( form.getForm().findField('PersonCard_begDate').getValue().format('d.m.Y') == getGlobalOptions().date );
					
					// ставим ограничение на дату окончания
					form.getForm().findField('PersonCard_endDate').setMinValue(form.getForm().findField('PersonCard_begDate').getValue());
					form.getForm().findField('PersonCard_endDate').clearInvalid();
					if ( form.getForm().findField('accessType').getValue() == 'view' ) {
						current_window.action = 'view';
					}
					
					//Добавил этот кусок в рамках задачи https://redmine.swan.perm.ru/issues/26087 - переписал логику работы с чекбоксом "Заявление"
					var checkbox = form.getForm().findField('PersonCardAttach');
					var PersonCardAttach_id = form.getForm().findField('PersonCardAttach_id').getValue();
					var Lpu_Attach = form.getForm().findField('LpuAttachType_id').getValue();
					var action = current_window.action;
					var PersonCardBegDate = form.getForm().findField('PersonCard_begDate').getValue().format('Y-m-d');

					checkbox.disable();
                    Ext.getCmp('uploadbutton').enable();
					if(action == 'view'){
						checkbox.disable();
						checkbox.setValue(PersonCardAttach_id > 0);
                        Ext.getCmp('uploadbutton').disable();
                        if(Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue()))
                            Ext.getCmp('PCEW_PersonCardAttachPanel').expand();
					}
					if(action == 'edit'){
                        if(Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue()))
                            Ext.getCmp('PCEW_PersonCardAttachPanel').expand();
						if(!Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue())) //Если есть дата закрытия, значит карта закрыта https://redmine.swan.perm.ru/issues/52919
						{
							current_window.cardClosed = 1;
						}
						if(PersonCardAttach_id > 0){
							checkbox.disable();
							checkbox.setValue(true);
                            if(Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue()))
                                Ext.getCmp('uploadbutton').enable();
						}
						else{
							if((Lpu_Attach==1||Lpu_Attach==3)&&PersonCardBegDate>='2012-04-26'){
								checkbox.disable();
                                Ext.getCmp('uploadbutton').enable();
							}
						}
						var params = {
							Person_id: Ext.getCmp('PCEF_Person_id').getValue(),
							Lpu_id : form.getForm().findField('Lpu_id').getValue(),
							AmbulatCardType_id : form.getForm().findField('LpuAttachType_id').getValue()
						};

						if ( this.action == 'add' && this.attachType == 1 )
						{
							params.CheckFond = 1;
						}
						form.getForm().findField('PersonCard_Code').getStore().load({params:params,baseParams:params});
					}

					if(PersonCardAttach_id>0){
						Ext.getCmp('PCEW_PrintAttachBlankButton').enable();
					}
					else{
						Ext.getCmp('PCEW_PrintAttachBlankButton').disable();
					}
					var obj = Ext.util.JSON.decode(a.response.responseText)[0],
						files = obj.files;
					for(var j=0; j<files.length; j++) {
						var ms = files[j].sizeinfo.match(/(\d+)+/g);
						files[j].size = ms ? ms[0] : 0;
						current_window.addFileToFilesPanel(files[j]);
					}
					
					current_window.mode = current_window.action;
					if(current_window.action == 'edit') {
						if ( current_window.isDMS || is_curday) {
							current_window.mode == 'edit';
						}
						else if ( isSuperAdmin() || (getGlobalOptions().region.nick.inlist([ 'ufa' ]) && isLpuAdmin(lpu_id)) || (!person_isbdz && pc_isattachcondit)
							|| (isUserGroup('CardCloseUser') && getRegionNick() != 'perm')
						) {
							current_window.mode = 'edit_card_code_and_close';
						}
						else {
							current_window.mode = 'edit_card_code_only';
						}
					}
					switch (current_window.mode)
					{
						// закрываем все поля на редактирование, кроме участка и номера
                        case 'edit':
							current_window.enableEdit(true);
							form.getForm().findField('PersonCard_endDate').enable();
							form.getForm().findField('CardCloseCause_id').enable();
							Ext.getCmp('PCEW_PrintAttachBlankButton').enable();
							form.getForm().findField('PersonCard_Code').enable();
							form.getForm().findField('LpuAttachType_id').disable();
							form.getForm().findField('PersonCard_begDate').disable();
							if((!form.getForm().findField('PersonCard_endDate').getValue() || form.getForm().findField('PersonCard_endDate').getValue() == '') && current_window.action == 'edit' && (getRegionNick() == 'ekb') && (!Ext.isEmpty(getGlobalOptions().allow_edit_attach_date) && getGlobalOptions().allow_edit_attach_date == 1) && isUserGroup('CardEditUser'))
								form.getForm().findField('PersonCard_begDate').enable();
							if ( form.getForm().findField('LpuAttachType_id').getValue() == 4 )
								form.getForm().findField('PersonCard_endDate').enable();
							else
								form.getForm().findField('PersonCard_endDate').disable();
							if ( form.getForm().findField('LpuAttachType_id').getValue() == 4 )
								if ( !form.getForm().findField('PersonCard_endDate').getValue() || form.getForm().findField('PersonCard_endDate').getValue() == '' )
								{
									form.getForm().findField('CardCloseCause_id').disable();
									form.getForm().findField('CardCloseCause_id').setAllowBlank(true);
									form.getForm().findField('CardCloseCause_id').clearValue();
								}
								else
								{
									form.getForm().findField('CardCloseCause_id').enable();
									form.getForm().findField('CardCloseCause_id').setAllowBlank(false);
								}
							else
								form.getForm().findField('CardCloseCause_id').disable();
							form.getForm().findField('Lpu_id').disable();
							
							form.getForm().findField('CardCloseCause_id').getStore().clearFilter();
							form.getForm().findField('CardCloseCause_id').lastQuery = '';
							if ( current_window.action && current_window.action == 'edit' && current_window.isDMS )
							{
								form.getForm().findField('CardCloseCause_id').enable();
								form.getForm().findField('PersonCard_endDate').enable();
								form.getForm().findField('PersonCard_endDate').setAllowBlank(false);
								form.getForm().findField('CardCloseCause_id').getStore().filterBy(
									function(row) {
										if ( row.get('CardCloseCause_id') == 6 || row.get('CardCloseCause_id') == 7 )
											return true;
										return false;
									}.createDelegate(this)
								);
							}

						break;
						
						case 'edit_card_code_only':
							current_window.enableEdit(false);
							//form.getForm().findField('PersonCard_Code').enable();
							//Ext.getCmp('PCEF_PersonCard_PAC').enable();
							current_window.buttons[0].enable();
						break;
		
						case 'edit_card_code_and_close':
							current_window.enableEdit(false);
							//form.getForm().findField('PersonCard_Code').enable();
							//Ext.getCmp('PCEF_PersonCard_PAC').enable();
							current_window.buttons[0].enable();
						break;

					}
					if(
						current_window.action == 'edit' && 
						getRegionNick() == 'astra' && 
						(!form.getForm().findField('PersonCard_endDate').getValue() || form.getForm().findField('PersonCard_endDate').getValue() == '') && 
						response.PersonCard_IsAttachAuto == 2
					) //Открываем поля "Тип участка", "Участок" и "Врач"
					{
						form.getForm().findField('LpuRegionType_id').enable();
						form.getForm().findField('LpuRegion_id').enable();
						form.getForm().findField('MedStaffFact_id').enable();
					}
					if(current_window.action == 'view'){
						form.getForm().findField('PersonCard_Code').disable();
						Ext.getCmp('PCEF_PersonCard_PAC').disable();
					}
					if( current_window.action == 'edit' && (((isUserGroup('CardCloseUser')||isSuperAdmin())  && getRegionNick() != 'perm') || (isUserGroup('CardCloseUser') && isSuperAdmin() && getRegionNick() == 'perm' ) )) {
						//https://redmine.swan.perm.ru/issues/52919
						if(Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue()) || (form.getForm().findField('PersonCardEnd_insDT').getValue() == getGlobalOptions().date)){
							form.getForm().findField('PersonCard_endDate').enable();
							form.getForm().findField('CardCloseCause_id').enable();
                        }
						//AttachFilesPanel.enable();
                        if(Ext.isEmpty(form.getForm().findField('PersonCard_endDate').getValue()))
                            Ext.getCmp('uploadbutton').enable();
						current_window.buttons[0].enable();
					}
					if ( form.getForm().findField('LpuAttachType_id').getValue() == 3 || form.getForm().findField('LpuAttachType_id').getValue() == 4 )
						form.getForm().findField('LpuRegion_id').setAllowBlank(true);
					else
						form.getForm().findField('LpuRegion_id').setAllowBlank(false);

					// фильтруем типы участков
					// для Казахстана отображать все типы участков https://redmine.swan.perm.ru/issues/70639#note-139
					region_type_combo.getStore().clearFilter();
					if ( lpu_attach_type_id > 0 && getRegionNick() != 'kz' )
					{
						current_window.filterRegionType();
						/*region_type_combo.getStore().filterBy(
							function ( record ) {
								if ( lpu_attach_type_id == 4 )
									return true;
								switch ( lpu_attach_type_id )
								{
									case '1':
										if (
											region_type_combo.getValue() == null ||
											(
												Ext.isEmpty(region_type_combo.getFieldValue('LpuRegionType_SysNick')) ||
												(!region_type_combo.getFieldValue('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop', 'comp', 'prip']) && getRegionNick() == 'perm') ||
												(!region_type_combo.getFieldValue('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop']) && getRegionNick() != 'perm')
											)
										)
										{
											region_type_combo.clearValue()
										}
										if (
											(record.data.LpuRegionType_SysNick.inlist(['ter', 'ped', 'vop', 'comp', 'prip']) && getRegionNick() == 'perm') ||
											(record.data.LpuRegionType_SysNick.inlist(['ter', 'ped', 'vop']) && getRegionNick() != 'perm')
										) {
											if (region_type_combo.disabled)
												return true;
											var person_age = Ext.getCmp('PersonCardEditWindow').findById('PCEF_PersonInformationFrame').getFieldValue("Person_Age");
											if ( person_age < 18 && record.data.LpuRegionType_SysNick != 'ter')
												return true;
											if ( person_age >= 18 && record.data.LpuRegionType_SysNick != 'ped')
												return true;
										}
									break;
									case '2':
										if (  region_type_combo.getValue() == null || region_type_combo.getFieldValue('LpuRegionType_SysNick') != 'gin' )
										{
											region_type_combo.clearValue()
										}
										if ( record.data.LpuRegionType_SysNick == 'gin' )
											return true;
									break;
									case '3':
										if (  region_type_combo.getValue() == null || region_type_combo.getFieldValue('LpuRegionType_SysNick') != 'stom' )
										{
											region_type_combo.clearValue()
										}
										if ( record.data.LpuRegionType_SysNick == 'stom' )
											return true;
									break;
								}
								return false;
							}
						);*/
					}

					if (form.getForm().findField('LpuRegion_id').getStore().getCount() == 0) {
						form.getForm().findField('LpuRegion_id').getStore().load({
							params: {
								Lpu_id: lpu_id,
								LpuAttachType_id: form.getForm().findField('LpuAttachType_id').getValue(),
								LpuRegionType_id: lpu_region_type_id,
								Object: 'LpuRegion',
								showOpenerOnlyLpuRegions: 1 // отображать только открытые участки
							},
							callback: function(fm) {
                                if(current_window.allowEditLpuRegion)
                                {
                                    current_window.loadLpuRegionType({callback: function() {
                                        var combo = current_window.findById('PersonCardEditForm').getForm().findField('LpuRegionType_id');
										if(getRegionNick() != 'perm')
										{
											combo.getStore().findBy(function(rec){
												if (rec.get('LpuRegionType_SysNick') == 'ter') {
													combo.setValue(rec.get('LpuRegionType_id'));
													return true;
												}
												return false;
											});
											//current_window.findById('PersonCardEditForm').getForm().findField('LpuRegion_id').disable();
											current_window.filterRegionType();
											combo.fireEvent('change', combo, combo.getValue(), -1);
										}
										else{
											//current_window.findById('PersonCardEditForm').getForm().findField('LpuRegion_id').disable();
											current_window.filterRegionType();
											combo.fireEvent('change', combo, combo.getValue(), -1);
										}
                                    }.createDelegate(this)});
                                }

                                var lpu_region_combo = form.getForm().findField('LpuRegion_id');
                                lpu_region_combo.setValue(lpu_region_combo.getValue());

                                if (getRegionNick().inlist(['astra','vologda'])) {
	                                var msf_combo = form.getForm().findField('MedStaffFact_id');
	                                msf_combo.getStore().load({
										params: {
											Lpu_id: getGlobalOptions().lpu_id,
											LpuRegion_id: lpu_region_combo.getValue(),
											showClosed: 1
										},
										callback: function(res,par) {
											if(!Ext.isEmpty(msf_combo.getValue()))
												msf_combo.setValue(msf_combo.getValue());
										}
									});
								}
                                if ( current_window.action == 'edit' ) {
									form.getForm().findField('LpuRegion_id').focus(true, 200);
								}
							}
						});
					}
					else {

						region_type_combo.fireEvent('change', region_type_combo, region_type_combo.getValue());
						if ( current_window.action == 'edit' ) {
							form.getForm().findField('LpuRegion_id').focus(true, 200);
						}
					}
                    var region_fap_combo = form.getForm().findField('LpuRegion_Fapid');
                       var params = new Object();
                       params.Lpu_id = form.getForm().findField('Lpu_id').getValue();
                       params.LpuRegion_date = form.getForm().findField('PersonCard_begDate').getValue().format('Y-m-d');
                       region_fap_combo.getStore().load({
                           params: params
                       });
					// для суперадмина даем выбирать ЛПУ
					if ( this.action == 'add' && isSuperAdmin() )
					{
						form.getForm().findField('Lpu_id').enable();
					}
					if(getRegionNick().inlist(['astra','vologda']) && current_window.attachType == 1){
						checkbox.setValue(false);
						checkbox.hide();
						Ext.getCmp('uploadbutton').hide();
						Ext.getCmp('PCEW_PersonCardAttachPanel').hide();
					}
					else
					{
						if(getRegionNick().inlist(['buryatiya'])) {
							checkbox.setValue(false);
						}
						checkbox.show();
						Ext.getCmp('uploadbutton').show();
						Ext.getCmp('PCEW_PersonCardAttachPanel').show();
					}
					
					current_window.doLayout();
					current_window.syncSize();	
		        }
    	    });
		}
		else
		{
			if ( this.action == 'add' && this.attachType > 0 )
			{
				var attach_type_combo = form.getForm().findField('LpuAttachType_id');
				attach_type_combo.setValue(this.attachType);
				attach_type_combo.fireEvent('change', attach_type_combo, this.attachType, this.attachType);
				//Добавил этот кусок в рамках задачи https://redmine.swan.perm.ru/issues/26087
				var checkbox = form.getForm().findField('PersonCardAttach');
				checkbox.disable();
				
				if(getGlobalOptions().region.nick == 'khak'){
					if(this.AllowcheckAttach==1)
						checkbox.setValue(false);
					else
						checkbox.setValue(true);
				}
				else
					checkbox.setValue(true);

                Ext.getCmp('uploadbutton').enable();

                if(getRegionNick().inlist(['astra','vologda']) && this.attachType == 1){
					checkbox.setValue(false);
					checkbox.show();
					Ext.getCmp('uploadbutton').hide();
					Ext.getCmp('PCEW_PersonCardAttachPanel').hide();
					current_window.doLayout();
					current_window.syncSize();
				}
				else{
					checkbox.setValue(false);
					checkbox.show();
					Ext.getCmp('uploadbutton').show();
					Ext.getCmp('PCEW_PersonCardAttachPanel').show();
					current_window.doLayout();
					current_window.syncSize();	
				}
			}
			else
			{
				form.getForm().findField('LpuRegion_id').getStore().load({
					params: {
						LpuAttachType_id: 0,
						Object: 'LpuRegion',
						showOpenerOnlyLpuRegions: 1
					}
				});
			}
		}
		form.getForm().findField('CardCloseCause_id').getStore().clearFilter();
		if ( this.isDMS )
		{
			form.getForm().findField('PersonCard_DmsPolisNum').setAllowBlank(false);
			form.getForm().findField('PersonCard_DmsBegDate').setAllowBlank(false);
			form.getForm().findField('PersonCard_DmsEndDate').setAllowBlank(false);
			form.getForm().findField('OrgSMO_id').setAllowBlank(false);
			form.getForm().findField('PersonCard_DmsPolisNum').focus(true, 500);
			form.getForm().findField('PersonCard_endDate').enable();
			current_window.buttons[1].disable();
		}
		else
		{
			form.getForm().findField('PersonCard_DmsPolisNum').setAllowBlank(true);
			form.getForm().findField('PersonCard_DmsBegDate').setAllowBlank(true);
			form.getForm().findField('PersonCard_DmsEndDate').setAllowBlank(true);
			form.getForm().findField('OrgSMO_id').setAllowBlank(true);
			if ( form.getForm().findField('PersonCard_Code').disabled )
				if ( !form.getForm().findField('LpuAttachType_id').disabled )
					form.getForm().findField('LpuAttachType_id').focus(true, 500);
				else
					form.getForm().findField('PersonCard_begDate').focus(true, 500);
		}
	},
	filterRegionType: function(){
		log(this.LpuRegionsByDate);
		var win = this;
		var form = this.findById('PersonCardEditForm');
		var region_type_combo = form.getForm().findField('LpuRegionType_id');
		var region_combo = form.getForm().findField('LpuRegion_id');
		var lpu_attach_type_id = form.getForm().findField('LpuAttachType_id').getValue() || '';
		var person_age = Ext.getCmp('PersonCardEditWindow').findById('PCEF_PersonInformationFrame').getFieldValue("Person_Age");
		region_type_combo.getStore().filterBy(
				function ( record ) {
					if(win.action != 'add')
					{
						return true;
					}
					switch (lpu_attach_type_id.toString())
					{
						case '1':
							if ( region_type_combo.getValue() == null ||
									(
											Ext.isEmpty(region_type_combo.getFieldValue('LpuRegionType_SysNick')) ||
											((!region_type_combo.getFieldValue('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop', 'comp', 'prip']) && getRegionNick() == 'perm')) ||
											(!region_type_combo.getFieldValue('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop']) && getRegionNick() != 'perm')
									)
							)
							{
								region_type_combo.clearValue();
							}
							if (
									(getRegionNick() == 'perm' && record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop', 'comp', 'prip'])) ||
									(getRegionNick() != 'perm' && getRegionNick() != 'ufa' && record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop'])) ||
									(getRegionNick() == 'ufa' && record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'vop']) && record.get('LpuRegionType_SysNick').inlist(win.LpuRegionsByDate))
							)
							{
								if(record.get('LpuRegionType_SysNick') == 'ter')
								{
									if(person_age >= 18)
										return true;
								}
								else
								{
									if(record.get('LpuRegionType_SysNick') == 'ped')
									{
										if(person_age < 18)
											return true;
									}
									else
										return true
								}
							}
							break;
						case '2':
							//region_type_combo.setFieldValue('LpuRegionType_SysNick', 'gin');
							//region_type_combo.fireEvent('change', region_type_combo, region_type_combo.getValue(), region_type_combo.getValue());
							if (
									(getRegionNick() != 'ufa' && record.get('LpuRegionType_SysNick') == 'gin') ||
									(getRegionNick() == 'ufa' && record.get('LpuRegionType_SysNick') == 'gin' && record.get('LpuRegionType_SysNick').inlist(win.LpuRegionsByDate))
							)
								return true;
							break;
						case '3':
							//region_type_combo.setFieldValue('LpuRegionType_SysNick', 'stom');
							//region_type_combo.fireEvent('change', region_type_combo, region_type_combo.getValue(), region_type_combo.getValue());
							if (
									(getRegionNick() != 'ufa' && record.get('LpuRegionType_SysNick') == 'stom') ||
									(getRegionNick() == 'ufa' && record.get('LpuRegionType_SysNick') == 'stom' && record.get('LpuRegionType_SysNick').inlist(win.LpuRegionsByDate))
							)
								return true;
							break;
						case '4':
							if(
								(getRegionNick() != 'ufa' && !record.get('LpuRegionType_SysNick').inlist(['ter', 'gin','stom'])) ||
								(getRegionNick() == 'ufa' && !record.get('LpuRegionType_SysNick').inlist(['ter', 'ped', 'gin','stom']) && record.get('LpuRegionType_SysNick').inlist(win.LpuRegionsByDate))
							)
							{
								if(record.get('LpuRegionType_SysNick') == 'ped'){
									if(getRegionNick() == 'ekb' && person_age < 18)
									{
										return true;
									}
								}
								else
									return true;
							}
							break;
					}
					return false;
				}
		);
		if(win.action=='add' && (getRegionNick() == 'ufa' && lpu_attach_type_id.inlist([1,2,3,4]) && (Ext.isEmpty(region_type_combo.getFieldValue('LpuRegionType_SysNick')) || !region_type_combo.getFieldValue('LpuRegionType_SysNick').inlist(win.LpuRegionsByDate))))
		{
			region_type_combo.clearValue();
			region_combo.disable();
		}
	},
    modal: true,
    onHide: Ext.emptyFn,
	params: null,
	personEditWindow: null,
    plain: true,
	printAttachBlank: function() {
		var form = this;
		var person_card_id = form.findById('PCEF_PersonCard_id').getValue();
        if (this.action == 'add' && !(person_card_id > 0)) { //Добавил в рамках задачи http://redmine.swan.perm.ru/issues/33518, т.к. не сохранили, на печать не уходит PersonCard_id.
            this.formStatus = 'save';
			var print = true;
			this.doSubmit(print);
			return;
		}
        var bf = this.findById('PersonCardEditForm').getForm();
		if (
			(1 == bf.findField('LpuAttachType_id').getValue())||
			(
				bf.findField('LpuAttachType_id').getValue().inlist(['2','3','4']) &&
				/*(
					4 == bf.findField('LpuAttachType_id').getValue() || 
					3 == bf.findField('LpuAttachType_id').getValue() || 
					2 == bf.findField('LpuAttachType_id').getValue()
				) && */
					getGlobalOptions().region.nick.inlist(['perm','ufa'])
			)
		)
		{
			var params = new Object();
			params.mi_params = form.getMedicalInterventPrintParams();

            params.Person_id = Ext.getCmp('PCEF_Person_id').getValue();
            params.Server_id = Ext.getCmp('PCEF_Server_id').getValue();
            params.Lpu_id = Ext.getCmp('PCEF_Lpu_id').getValue();
            params.PersonCard_id = person_card_id;
			params.printAgreementOnly = 0;
			if((4 == bf.findField('LpuAttachType_id').getValue() && getGlobalOptions().region.nick=='ufa'))
				params.printAgreementOnly = 1;

			if (getRegionNick() == 'ekb')
				params.LpuRegion_id = this.findById('PersonCardEditForm').getForm().findField('LpuRegion_id').getValue();

			getWnd((getRegionNick()=='kz')?'swPersonCardPrintDialogWindowKz':'swPersonCardPrintDialogWindow').show({params: params});//https://redmine.swan.perm.ru/issues/56487
		}
	},
	getMedicalInterventPrintParams: function() {
		var base_form = this.findById('PersonCardEditForm').getForm();
		var grid = this.MedicalInterventGrid.getGrid();

		var params = new Object();

		params.person_card_id = base_form.findField('PersonCard_id').getValue();
		params.med_personal_id = (getGlobalOptions().medpersonal_id) ? getGlobalOptions().medpersonal_id : 0;

		params.is_refuse = base_form.findField('PersonCardMedicalIntervent').getValue() ? 1 : 0;
		params.total_count = 0;
		params.refuse_count = 0;

		grid.getStore().each(function(rec) {
			params.total_count++;
			if (rec.get('PersonMedicalIntervent_IsRefuse')) {
				params.refuse_count++;
			}
		});
		return params;
	},
	resizable: false,
    returnFunc: Ext.emptyFn,
	setLpuId: function() {
		var current_window = this;
		var lpu_combo = current_window.findById('PersonCardEditForm').getForm().findField('Lpu_id');
		lpu_combo.getStore().load({
			callback: function() {
				lpu_combo.setValue(getGlobalOptions().lpu_id);
				
				lpu_combo.getStore().clearFilter();
				lpu_combo.baseFilterFn = function(record) {
						return true;
				};

				if ( this.isDMS )
				{
					lpu_combo.getStore().filterBy(
						function(record) {
							if ( record.get('Lpu_isDMS') == 2 )
								return true;
							else
								return false;
						}
					);
					lpu_combo.baseFilterFn = function(record) {
						if ( record.get('Lpu_isDMS') == 2 )
							return true;
						else
							return false;
					};
				}
			}.createDelegate(this)
		});
		lpu_combo.setValue(getGlobalOptions().lpu_id);
		if ( Ext.getCmp('PersonCardEditWindow').action == 'add' && isSuperAdmin() && getGlobalOptions().lpu_id && getGlobalOptions().lpu_id > 0 ) {
			lpu_combo.fireEvent('change', lpu_combo, getGlobalOptions().lpu_id, 0);
		}
		lpu_combo.disable();
	},
    show: function() {
		sw.Promed.swPersonCardEditWindow.superclass.show.apply(this, arguments);
		// фильтруем список СМО ДМС
		Ext.getCmp('PCEW_OrgSMO_id').getStore().filterBy(
			function(record) {
				if ( record.get('OrgSMO_isDMS') == 2 )
					return true;
				else
					return false;
			}
		);
		Ext.getCmp('PCEW_OrgSMO_id').baseFilterFn = function(record) {
			if ( record.get('OrgSMO_isDMS') == 2 )
				return true;
			else
				return false;
		};
		//form.setTitle(langs('Подразделение: Добавление'));
		this.prev_beg_date = null;
		this.prev_end_date = null;
		this.isDMS = arguments[0].attachType == 'dms_region';
		this.cardClosed = 0; //https://redmine.swan.perm.ru/issues/52919
		this.tryOpen = 0;
        this.otherCardExists = 0;
		this.LpuRegionsByDate = new Array();
		this.oneCard = false;
		var arg = arguments[0];

		var current_window = this;
		var form = this.findById('PersonCardEditForm');

		// сбрасываем значения полей формы
		form.getForm().reset();

		// чистим фильтр списка участков
		form.getForm().findField('LpuRegionType_id').getStore().clearFilter();
		
		// TODO: Для асинхронной загрузки справочника. По идее надо переделать, чтобы остальные действия вызывались в каллбэке этой функции.
		this.loadLpuRegionType({callback: function() {
			var combo = this.findById('PersonCardEditForm').getForm().findField('LpuRegionType_id');
			combo.setValue(combo.getValue());
		}.createDelegate(this)});
		
        this.newPersonAmbulatCard_id = 0;
       	this.onHide = Ext.emptyFn;
		this.action = 'add';
		this.returnFunc	= Ext.emptyFn;
		this.attachType = 0;
        this.allowEditLpuRegion = 0;

        if ( !arg )
        {
        	this.showMessage(langs('Сообщение'), langs('Неверные параметры формы'));
			this.hide();
        	return false;
        }

        form.getForm().setValues( arg );

        if(arg.otherCardExists) {
            this.otherCardExists = arg.otherCardExists;
        }

        if(arg.allowEditLpuRegion){
            this.allowEditLpuRegion = arg.allowEditLpuRegion;
        }

		if(arg.newPersonAmbulatCard_id){
			this.newPersonAmbulatCard_id=arg.newPersonAmbulatCard_id
		}
        if ( arg.action )
           	this.action = arg.action;
        
        if (arg.oneCard)
        	this.oneCard = arg.oneCard;
        if(this.action == 'add' && getRegionNick().inlist(['astra','vologda']))
        {
        	var params = new Object();
        	params.person_id = arg.Person_id;
        	params.org_id = getGlobalOptions().org_id;
        	Ext.Ajax.request({
				url: '/?c=LpuPassport&m=checkOrgServiceTerr',
				params: params,
				callback: function(options,success,response){
					var resp = Ext.util.JSON.decode(response.responseText);
					if(!resp.length>0)
					{
						current_window.showMessage(langs('Ошибка'), 'Адрес пациента не входит в зону обслуживания МО. Прикрепить пациента к МО можно только по заявлению');
						current_window.hide();
			        	return false;
					}
				}
			});
        }
        		
        if ( arg.callback )
             this.returnFunc = arg.callback;
		form.getForm().findField('PersonCard_begDate').setMinValue(null);
		form.getForm().findField('PersonCard_begDate').setMaxValue(null);
		if(arg.prev_end_date){ //https://redmine.swan.perm.ru/issues/84790
			this.prev_end_date = new Date(arg.prev_end_date);
			form.getForm().findField('PersonCard_begDate').setMinValue(this.prev_end_date);
		}
		else if (arg.prev_beg_date){ //https://redmine.swan.perm.ru/issues/84790
			this.prev_beg_date = new Date(arg.prev_beg_date);
			this.prev_beg_date.setDate(this.prev_beg_date.getDate()+1);
			form.getForm().findField('PersonCard_begDate').setMinValue(this.prev_beg_date);
		}
		var options = getGlobalOptions();
		var date = options['date'];
		form.getForm().findField('PersonCard_begDate').setMaxValue(date); //https://redmine.swan.perm.ru/issues/84790
		this.lastAttachIsNotInOurLpu = false;
		this.Agreed = 0;
		this.lastAttach_IsAttachCondit = false; //https://redmine.swan.perm.ru/issues/29930

		if ( arg.lastAttachIsNotInOurLpu )
             this.lastAttachIsNotInOurLpu = arg.lastAttachIsNotInOurLpu;
		if ( arg.lastAttach_IsAttachCondit)
			this.lastAttach_IsAttachCondit = arg.lastAttach_IsAttachCondit; //https://redmine.swan.perm.ru/issues/29930
		if ( arg.setIsAttachCondit) {
			this.setIsAttachCondit = arg.setIsAttachCondit; //https://redmine.swan.perm.ru/issues/29930
		} else {
			this.setIsAttachCondit = null;
		}


		this.oldLpu_id = null;
		if ( arg.oldLpu_id )
			this.oldLpu_id = arg.oldLpu_id;
		if(getGlobalOptions().region.nick == 'khak'){
			this.AllowcheckAttach = 2;
			if( arg.AllowcheckAttach)
				this.AllowcheckAttach = arg.AllowcheckAttach;
		}
		if (arg.onHide)
           	this.onHide = arg.onHide;				
        		
		if ( arg.attachType )
        {
			switch ( arg.attachType )
			{
				case 'common_region':
					this.attachType = 1;
				break;
				case 'ginecol_region':
					this.attachType = 2;
				break;
				case 'stomat_region':
					this.attachType = 3;
				break;
				case 'service_region':
					this.attachType = 4;
				break;
				case 'dms_region':
					this.attachType = 5;					
				break;
				default:
					this.attachType = 0;
			}        	
        }
				
		
		if ( this.isDMS )
		{
			// скрываем			
			Ext.getCmp('PCEF_PersonCard_PAC').hide();
			form.getForm().findField('PersonCard_Code').hideContainer();
			form.getForm().findField('PersonCard_DmsPolisNum').showContainer();
			form.getForm().findField('PersonCard_DmsBegDate').showContainer();
			form.getForm().findField('PersonCard_DmsEndDate').showContainer();
			form.getForm().findField('OrgSMO_id').showContainer();
			Ext.getCmp('PCEW_DmsPolisFieldset').show();
			form.getForm().findField('LpuRegionType_id').hideContainer();
			form.getForm().findField('LpuRegion_id').hideContainer();
			form.getForm().findField('MedStaffFact_id').hideContainer();
		}
		else
		{
			// открывем
			Ext.getCmp('PCEF_PersonCard_PAC').show();
			form.getForm().findField('PersonCard_Code').showContainer();
			form.getForm().findField('PersonCard_DmsPolisNum').hideContainer();
			form.getForm().findField('PersonCard_DmsBegDate').hideContainer();
			form.getForm().findField('PersonCard_DmsEndDate').hideContainer();
			form.getForm().findField('OrgSMO_id').hideContainer();
			Ext.getCmp('PCEW_DmsPolisFieldset').hide();
			form.getForm().findField('LpuRegionType_id').showContainer();
			form.getForm().findField('LpuRegion_id').showContainer();
			if(getRegionNick().inlist(['astra','vologda']))
				form.getForm().findField('MedStaffFact_id').showContainer();			
		}
		this.setHeight(150);
		this.doLayout();
		this.syncSize();

        var person_id = form.findById('PCEF_Person_id').getValue();
        var person_card_id = form.findById('PCEF_PersonCard_id').getValue();
        var server_id = form.findById('PCEF_Server_id').getValue();
		var lpu_id = form.getForm().findField('Lpu_id').getValue();
		this.getPersonDeputyData();
		//Загрузка списка отказов от медицинских вмешательств
		this.MedicalInterventGrid.getGrid().getStore().load({params: {PersonCard_id: person_card_id}});

		// загрузка фрейма информации о человеке
		var loadMask = new Ext.LoadMask(Ext.get('PersonCardEditWindow'), {msg: LOAD_WAIT});
		loadMask.show();
		this.findById('PCEF_PersonInformationFrame').load({
			Person_id: person_id, 
			Server_id: server_id,
			callback: function() {
				loadMask.hide();
				// устанавливаем значения своего лпу ид в список выбора лпу
				this.setLpuId();
				switch (this.action) {
					case 'add':
						this.setTitle(WND_POL_PERSCARDADD);
						this.enableEdit(true);
						if(this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_IsDead")==2 || this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_deadDT") != '') {
							Ext.Msg.alert(langs('Сообщение'), langs('Пациент умер, прикрепление невозможно!'), function() {
								current_window.hide();	
							});
							return;
						}
						this.loadListsAndFormData();
						if(Ext.isEmpty(form.getForm().findField('PersonCard_Code').getValue())||Ext.isEmpty(form.getForm().findField('PersonAmbulatCard_id').getValue())){
							this.getPersonCardCode();
						}else{
							var params = {
								Person_id: Ext.getCmp('PCEF_Person_id').getValue(),
								Lpu_id : form.getForm().findField('Lpu_id').getValue(),
								AmbulatCardType_id : form.getForm().findField('LpuAttachType_id').getValue()
							};
							if ( this.action == 'add' && this.attachType == 1 )
							{
								params.CheckFond = 1;
							}
							form.getForm().findField('PersonCard_Code').getStore().load({params:params,baseParams:params})
						}
						form.getForm().findField('LpuAttachType_id').disable();
						form.getForm().findField('PersonCard_endDate').disable();
						form.getForm().findField('CardCloseCause_id').disable();
						
						var options = getGlobalOptions();
						var date = Date.parseDate(options['date'], 'd.m.Y');
						form.getForm().findField('PersonCard_begDate').setValue(date);
                        var checkbox = form.getForm().findField('PersonCardAttach');
						if (getRegionNick() == 'penza' && current_window.lastAttach_IsAttachCondit == true) {//#175771#7
							checkbox.enable();
							checkbox.setValue(false);
						}
                        if(getRegionNick()=='pskov' && current_window.attachType == 1)
                        {
                            if(date.format('Y-m-d') <= '2015-12-31' && current_window.otherCardExists == 0)
                            {
                                checkbox.enable();
                            }
                            else
                            {
                                checkbox.disable();
                            }
                        }
                        var region_fap_combo = form.getForm().findField('LpuRegion_Fapid');
                        var params = new Object();
                        params.Lpu_id = form.getForm().findField('Lpu_id').getValue();
                        params.LpuRegion_date = form.getForm().findField('PersonCard_begDate').getValue();
                        region_fap_combo.getStore().load({
                            params: params
                        });
						form.getForm().findField('PersonCard_begDate').setDisabled(Ext.isEmpty(getGlobalOptions().allow_edit_attach_date) || getGlobalOptions().allow_edit_attach_date != 1);

						// приходится здесь устанавливать тип участка пед/тер
						var region_type_combo = form.getForm().findField('LpuRegionType_id');
						var attach_type_combo = form.getForm().findField('LpuAttachType_id');
						var lpuAttachTypeId = attach_type_combo.getValue();
						var person_sex = Ext.getCmp('PersonCardEditWindow').findById('PCEF_PersonInformationFrame').getFieldValue("Sex_id");
						if ( lpuAttachTypeId == 2 && person_sex == 1 )
						{
							Ext.Msg.alert(langs('Ошибка'), langs('Пациент мужского пола не может быть прикреплен к гинекологическому участку.'), function() {
								current_window.hide();
							});
							return;
						}
						if ( lpuAttachTypeId == 1 )
						{
							var person_age = Ext.getCmp('PersonCardEditWindow').findById('PCEF_PersonInformationFrame').getFieldValue("Person_Age");
							if ( person_age < 18 )
							{
								region_type_combo.getStore().findBy(function(rec){
									if (rec.get('LpuRegionType_SysNick') == 'ped') {
										region_type_combo.setValue(rec.get('LpuRegionType_id'));
										return true;
									};
									return false;
								});
								region_type_combo.fireEvent('change', region_type_combo, region_type_combo.getValue(), -1);
								region_type_combo.getStore().filterBy(
									function ( record ) {
										if (
											(getRegionNick() == 'kz') ||
											(record.data.LpuRegionType_SysNick.inlist(['ped', 'vop', 'comp', 'prip']) && getRegionNick() == 'perm') ||
											(record.data.LpuRegionType_SysNick.inlist(['ped', 'vop']) && getRegionNick() != 'perm')
										)
										{
											return true;
										}	
									}
								);

							}
							else
							{
								region_type_combo.getStore().findBy(function(rec){
									if (rec.get('LpuRegionType_SysNick') == 'ter') {
										region_type_combo.setValue(rec.get('LpuRegionType_id'));
										return true;
									};
									return false;
								});
								region_type_combo.fireEvent('change', region_type_combo, region_type_combo.getValue(), -1);
								region_type_combo.getStore().filterBy(
									function ( record ) {
										if (
											(getRegionNick() == 'kz') ||
											(record.data.LpuRegionType_SysNick.inlist(['ter', 'vop', 'comp', 'prip']) && getRegionNick() == 'perm') ||
											(record.data.LpuRegionType_SysNick.inlist(['ter', 'vop']) && getRegionNick() != 'perm')
										)
										{
											return true;
										}	
									}
								);
							}
						}
						//Получаем номер участка по умолчанию (в рамках задачи 9295
						/*var request_params = {
							Person_id: person_id,
							LpuAttachType_id: attach_type_combo.getValue()
						};
						Ext.Ajax.request({
							url: '/?c=PersonCard&m=getLpuRegion',
							params: request_params,
							callback: function(options,success,response){
								if(success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									if(resp && resp[0]){
										var lpuRegion_id = resp[0].LpuRegion_id;
										var lpuregioncombo = form.getForm().findField('LpuRegion_id');
										var index = lpuregioncombo.getStore().findBy(function(rec) {
											return (rec.get('LpuRegion_id') == lpuRegion_id);
										});
										if(index >= 0){ //Если полученный номер участка принадлежит данному ЛПУ, то устанавливаем его в комбик
											var lpuregioncombo_rec = lpuregioncombo.getStore().getAt(index);
											form.getForm().findField('LpuRegion_id').setValue(lpuregioncombo_rec.id);
										}
									}
								}
						}
						});*/
					break;

					case 'edit':
						this.setTitle(WND_POL_PERSCARDEDIT);
						this.loadListsAndFormData(true);
					break;
					
					case 'view':
						this.setTitle(WND_POL_PERSCARDVIEW);
						this.enableEdit(false);
						this.loadListsAndFormData(true);
					break;
				}
				if((this.action == 'add' || this.action == 'edit') && (getRegionNick()=='astra')){

					//Проверяем наличие данных о документе удостоверяющем личность,
					//Только для Астрахани. https://redmine.swan.perm.ru/issues/65905
					var PersonFIO = this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_Surname") + " " + this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_Firname") + " " + this.findById('PCEF_PersonInformationFrame').getFieldValue("Person_Secname");
					var DocSer = this.findById('PCEF_PersonInformationFrame').getFieldValue("Document_Ser");
					var DocNum = this.findById('PCEF_PersonInformationFrame').getFieldValue("Document_Num");
                    if(this.allowEditLpuRegion == 0)
                    {
                        if(Ext.isEmpty(DocSer) || Ext.isEmpty(DocNum)){
                            Ext.Msg.alert("Сообщение","У пациента " + PersonFIO + " не указаны данные документа, удостоверяющего личность.");
                        }
                    }
				}
				if(this.allowEditLpuRegion == 1)
				{
					this.setTitle(langs('Добавление участка'));
				}
			}.createDelegate(this)
		});
    },
    showMessage: function(title, message, fn) {
    	if ( !fn )
			fn = function(){};
        Ext.MessageBox.show({
            buttons: Ext.Msg.OK,
            fn: fn,
            icon: Ext.Msg.WARNING,
            msg: message,
            title: title
        });
    },
    width: 700
});