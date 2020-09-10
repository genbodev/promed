/**
* swPersonPrivilegeReqEditWindow - окно редактирования запроса на включение в льготный регистр
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Dlo
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @author       Salakhov R.
* @version      09.2019
* @comment      
*/
sw.Promed.swPersonPrivilegeReqEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: false,
	title: 'Запрос на включение в льготный регистр',
	layout: 'border',
	id: 'PersonPrivilegeReqEditWindow',
	modal: true,
	shim: false,
	width: 400,
	resizable: false,
	maximizable: true,
	maximized: true,
	loadPersonSurNameAtBirth: function() {
		var wnd = this;
		var chk_field = this.form.findField('SurName_isChanged');
		var sur_field = this.form.findField('PersonSurNameAtBirth_SurName');

		Ext.Ajax.request({
			url: '/?c=Privilege&m=loadPersonSurNameAtBirth',
			params: {
				Person_id: this.Person_id
			},
			success: function(response, opts){
				var response_obj = Ext.util.JSON.decode(response.responseText);
				if(response_obj && !Ext.isEmpty(response_obj.PersonSurNameAtBirth_SurName)) {
					chk_field.setValue(true);
					sur_field.setValue(response_obj.PersonSurNameAtBirth_SurName);

					//если для пациента уже есть одобренные запросы то проверяем права на редактирование галочки
					if (response_obj.ApprovedReq_Cnt > 0) {
						wnd.checkAccessSurNameIsChanged();
					}
				}
			}
		});
	},
	checkAccessSurNameIsChanged: function() { // проверка, и при необходимости блокировка доступа к полю "Смена фамилии"
		if (!isSuperAdmin() && !havingGroup('LpuAdmin')) { //пользояатель не явялется администратором ЦОД и не является администратором МО
			this.form.findField('SurName_isChanged').enable_blocked = true;
			this.form.findField('PersonSurNameAtBirth_SurName').enable_blocked = true;
			this.setDisabled(this.action == 'view');
		}
	},
	resetAccessSurNameIsChanged: function() { //сброс блокировки доступа к полю "Смена фамилии"
		this.form.findField('SurName_isChanged').enable_blocked = false;
		this.form.findField('PersonSurNameAtBirth_SurName').enable_blocked = false;
		this.setDisabled(this.action == 'view');
	},
	doSave:  function(options) {
		var wnd = this;
		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.findById('PersonPrivilegeReqEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        this.checkPersonHaveActiveRegionalPrivilege(function(res){
            if(!res) this.win.submit(this.options);
        }.bind({win: this, options: options}));
        //this.submit(options);
		return true;		
	},
	submit: function(options) {
		var wnd = this;
		var params = new Object();
		var dt_range_field = this.form.findField('PersonPrivilegeReq_DateRange');
		var msf_field = this.form.findField('PersonPrivilegeReq_DateRange');

		if (options && options.send_to_expertise) {
			params.send_to_expertise = 1;
		}
        if (this.userMedStaffFact) {
			params.MedStaffFact_id = this.userMedStaffFact.MedStaffFact_id;
			params.Lpu_id = this.userMedStaffFact.Lpu_id;
			params.MedPersonal_id = this.userMedStaffFact.MedPersonal_id;
			params.LpuSection_id = this.userMedStaffFact.LpuSection_id;
			params.LpuUnit_id = this.userMedStaffFact.LpuUnit_id;
			params.PostMed_id = this.userMedStaffFact.PostMed_id;
		} else {
		    if (this.action == 'add' && msf_field.getValue() > 0) { //если форма в режиме добавления а место работы не передано, то можно выбрать место работы через комбобокс
                params.MedStaffFact_id = this.msf_field.getValue();
            }
        }
		params.Person_id = this.Person_id;
		params.PersonPrivilegeReqAns_DeclCause = this.res_form.findField('PersonPrivilegeReqAns_DeclCause').getValue();
        params.PersonPrivilegeReq_begDT = dt_range_field.getValue1() ? dt_range_field.getValue1().dateFormat('d.m.Y') : null;
        params.PersonPrivilegeReq_endDT = dt_range_field.getValue2() ? dt_range_field.getValue2().dateFormat('d.m.Y') : null;
        params.ReceptFinance_id = this.form.findField('PrivilegeType_id').getFieldValue('ReceptFinance_id');

		if (wnd.postmoderation_mode && wnd.action != 'add' && params.send_to_expertise != 1) { //в режиме постмодерации, если не передан признак отправки на экспертизу, при редактировании сохраняются только прикрепленные файлы
            var id = wnd.form.findField('PersonPrivilegeReq_id').getValue();
            if (id > 0) {
                wnd.FileUploadPanel.listParams = {
                    ObjectName: 'PersonPrivilegeReq',
                    ObjectID: id
                };
                wnd.FileUploadPanel.saveChanges();

                wnd.callback(wnd.owner, id);
                wnd.onSave({
                    PersonPrivilegeReq_id: id,
                    PrivilegeType_id: wnd.privilege_type_combo.getValue()
                });
                wnd.hide();
            }
        } else {
		    wnd.getLoadMask('Подождите, идет сохранение...').show();
            this.form.submit({
                params: params,
                failure: function(result_form, action) {
                    wnd.getLoadMask().hide();
                    if (action.result) {
                        if (action.result.Error_Code) {
                            Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
                        }
                    }
                },
                success: function(result_form, action) {
                    wnd.getLoadMask().hide();
                    if (action.result && action.result.PersonPrivilegeReq_id > 0) {
                        var id = action.result.PersonPrivilegeReq_id;
                        wnd.form.findField('PersonPrivilegeReq_id').setValue(id);

                        wnd.FileUploadPanel.listParams = {
                            ObjectName: 'PersonPrivilegeReq',
                            ObjectID: id
                        };
                        wnd.FileUploadPanel.saveChanges();
                        wnd.callback(wnd.owner, id);
                        wnd.onSave(action.result);
                        wnd.hide();
                    }
                }
            });
        }
	},
    checkPersonHaveActiveRegionalPrivilege: function(cb){
        var wnd = this;
        var cb = (cb && typeof cb == 'function') ? cb : false;
        if(getRegionNick() == 'msk'){
            var Privilege_begDate = this.form.findField('PersonPrivilegeReq_DateRange').getValue1().dateFormat('d.m.Y');
            var ReceptFinance_id = this.form.findField('PrivilegeType_id').getFieldValue('ReceptFinance_id');
            if(ReceptFinance_id != 1){
                if(cb) cb(false);
                return true;
            }
            var params = {
                Privilege_begDate: Privilege_begDate,
                Person_id: this.Person_id
            }
            Ext.Ajax.request({
                url: '/?c=Privilege&m=checkPersonHaveActiveRegionalPrivilege',
                params: params,
                success: function(response, opts){
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    var result = (response_obj && response_obj.check) ? true : false;
                    if(response_obj && response_obj.check){
                        Ext.Msg.show({
                            title: langs('Внимание'),
                            scope: this,
                            msg: langs('Добавить федеральную льготу и закрыть имеющиеся региональные льготы?'),
                            buttons: Ext.Msg.YESNO,
                            fn: function(btn) {
                                var res = (btn === 'yes') ? false : true;
                                if(cb) cb(res);
                            },
                            icon: Ext.MessageBox.QUESTION
                        });
                    }else{
                        if(cb) cb(false);
                    }
                },
                failure: function(response, opts){
                    Ext.Msg.alert('Ошибка','Ошибка при проверке наличия у пациента региональной льготы');
                }
            });
        }else{
            if(cb) cb(false);
        }
    },
    setDisabled: function(disable) {
        var field_arr = new Array(
            'PrivilegeType_id',
            'Diag_id',
            'DocumentPrivilegeType_id',
			'DocumentPrivilege_Ser',
			'DocumentPrivilege_Num',
			'DocumentPrivilege_begDate',
			'DocumentPrivilege_Org',
			'PersonPrivilegeReq_DateRange',
			'SurName_isChanged',
			'PersonSurNameAtBirth_SurName'
        );

        for (var i in field_arr) {
            if (this.form.findField(field_arr[i])) {
                var field = this.form.findField(field_arr[i]);
                if (disable || field.enable_blocked) {
                    field.disable();
                } else {
                    field.enable();
                }
            }
		}

        if (disable) {
            this.FileUploadPanel.disable();
            this.buttons[0].disable();
            this.buttons[1].disable();
        } else {
            this.FileUploadPanel.enable();
            if (this.buttons[0].enable_blocked) {
                this.buttons[0].disable();
                this.buttons[1].disable();
			} else {
                this.buttons[0].enable();
                this.buttons[1].enable();
			}
        }
    },
	setStatus: function(status) { //установка ограничений, связанных со статусом заявки
		if (!Ext.isEmpty(status)) {
            this.PersonPrivilegeReqStatus_id = status;
		} else {
            status = this.PersonPrivilegeReqStatus_id;
		}
        this.buttons[0].enable_blocked = !(this.PersonPrivilegeReqStatus_id == 1 || this.action != 'add'); //1 - Новый
        this.buttons[1].enable_blocked = !(this.PersonPrivilegeReqStatus_id == 1 || this.action != 'add'); //1 - Новый
	},
	setCommonInformationData: function(data) { //функция установки данных в блоке "Запрос подан"
        var now = new Date();

        //установка даты и очистка остальных полей
        this.form.findField('PersonPrivilegeReq_setDT').setValue(now.dateFormat('d.m.Y h:i'));
        this.form.findField('Msf_Lpu_Nick').setValue(!Ext.isEmpty(getGlobalOptions().lpu_nick) ? getGlobalOptions().lpu_nick : null);
        this.form.findField('Msf_FullName').setValue(null);

		if (!Ext.isEmpty(data)) { //если пришли данные, то просто оборажаем их
            this.form.findField('Msf_Lpu_Nick').setValue(!Ext.isEmpty(data.Msf_Lpu_Nick) ? data.Msf_Lpu_Nick : '');
            this.form.findField('Msf_FullName').setValue(!Ext.isEmpty(data.MedStaffFact_Name) ? data.MedStaffFact_Name : '');
		} else if (this.userMedStaffFact) { //иначе формируем данные на основе userMedStaffFact
            var msf_str = '';
		    msf_str += !Ext.isEmpty(this.userMedStaffFact.MedPersonal_FIO) ? this.userMedStaffFact.MedPersonal_FIO : '';
		    msf_str += !Ext.isEmpty(this.userMedStaffFact.PostMed_Name) ? ' '+this.userMedStaffFact.PostMed_Name : '';
		    msf_str += !Ext.isEmpty(this.userMedStaffFact.LpuSection_Nick) ? ' '+this.userMedStaffFact.LpuSection_Nick : '';
            this.form.findField('Msf_Lpu_Nick').setValue(!Ext.isEmpty(this.userMedStaffFact.Lpu_Nick) ? this.userMedStaffFact.Lpu_Nick : '');
            this.form.findField('Msf_FullName').setValue(msf_str);
		}
	},
	setResultFields: function() { //функция установки видимости полей в блоке "Результат"
		var res_field = this.res_form.findField('Result_Data');
		var rej_field = this.res_form.findField('PersonPrivilegeReqAns_DeclCause');

        if (this.PersonPrivilegeReqStatus_id == 3) { //3 - Ответ получен
            res_field.showContainer();
            if (this.PersonPrivilegeReqAns_IsInReg == 2) { //2 - Да
            	res_field.setValue("Включен в регистр");
                rej_field.hideContainer();
			} else {
                res_field.setValue("Отказ");
                rej_field.showContainer();
			}
		} else {
            res_field.hideContainer();
            rej_field.hideContainer();
		}
	},
	setPersonSurNameAtBirthFields: function() { //функция установки видимости полей в блоке "Фамилия при рождении", а также настройка блока обязательных документов
		var chk_field = this.form.findField('SurName_isChanged');
		var sur_field = this.form.findField('PersonSurNameAtBirth_SurName');
		var is_changed = chk_field.checked;

		if (is_changed) {
			sur_field.setAllowBlank(this.action == 'view');
			sur_field.showContainer();
		} else {
			sur_field.setAllowBlank(true);
			sur_field.setValue(null);
			sur_field.hideContainer();
		}

		this.FileUploadPanel.setNecessaryDocumentListBySurNameIsChanged();
		this.FileUploadPanel.createDefaultCombo();
		this.FilesInformationPanel.showDataBySurNameIsChanged();
	},
    setModerationMode: function() { //настройка формы в зависимости от установленного в настройках типа модерации
        this.form.findField('PrivilegeType_id').enable_blocked = (this.postmoderation_mode && this.action == 'edit');
        this.form.findField('PersonPrivilegeReq_DateRange').enable_blocked = (this.postmoderation_mode && this.action == 'edit');
        //остальные поля блокируются в setLinkedFields комбобокса для выбора льготы

	    if (this.postmoderation_mode) {
            this.form.url = '/?c=Privilege&m=savePersonPrivilegeReqPM';
        } else {
            this.form.url = '/?c=Privilege&m=savePersonPrivilegeReq';
        }
    },
	show: function() {
        var wnd = this;
		sw.Promed.swPersonPrivilegeReqEditWindow.superclass.show.apply(this, arguments);
		this.action = '';
		this.callback = Ext.emptyFn;
		this.onSave = Ext.emptyFn;
        this.userMedStaffFact = null;
		this.PersonPrivilegeReq_id = null;
		this.Person_id = null;
		this.PersonPrivilegeReqStatus_id = null;
		this.PersonPrivilegeReqAns_IsInReg = null;
		this.postmoderation_mode = !Ext.isEmpty(getGlobalOptions().person_privilege_add_request_postmoderation);

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].onSave && typeof arguments[0].onSave == 'function' ) {
			this.onSave = arguments[0].onSave;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].PersonPrivilegeReq_id ) {
			this.PersonPrivilegeReq_id = arguments[0].PersonPrivilegeReq_id;
		}
		if ( arguments[0].Person_id ) {
			this.Person_id = arguments[0].Person_id;
		}
		if ( arguments[0].userMedStaffFact && Object.keys(arguments[0].userMedStaffFact).length > 0 ) {
			this.userMedStaffFact = arguments[0].userMedStaffFact;
		}

		var g_options = getGlobalOptions();

		this.setTitle("Запрос на включение в льготный регистр");
		this.form.reset();
		this.res_form.reset();
		this.FileUploadPanel.reset();
        this.diag_combo.fullReset();
        this.document_type_combo.fullReset();
        this.msf_combo.fullReset();
        this.msf_combo.getStore().baseParams.MedPersonal_id = !Ext.isEmpty(g_options.medpersonal_id) ? g_options.medpersonal_id : null;
        this.msf_combo.getStore().baseParams.Lpu_id = !Ext.isEmpty(g_options.lpu_id) ? g_options.lpu_id : null;

        if (this.action == 'add' && !this.userMedStaffFact) {
            this.msf_combo.showContainer();
            this.msf_combo.setAllowBlank(false);
            this.form.findField('Msf_FullName').ownerCt.hide();
        } else {
            this.msf_combo.hideContainer();
            this.msf_combo.setAllowBlank(true);
            this.form.findField('Msf_FullName').ownerCt.show();
        }

        this.setModerationMode();
        this.setPersonSurNameAtBirthFields();
		this.resetAccessSurNameIsChanged();

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();
		switch (this.action) {
			case 'add':
				this.setTitle(this.title + ": Добавление");
				loadMask.hide();
				this.setStatus(1); //1 - Новый
                this.setCommonInformationData();
                this.setResultFields();
                this.setDisabled(false);

                this.FileUploadPanel.listParams = {
                    ObjectName: 'PersonPrivilegeReq',
                    ObjectID: null,
                    add_empty_combo: false
                };

                this.PersonPanel.load({
                    Person_id: wnd.Person_id,
                    callback: function() {
                        var birth_date = wnd.PersonPanel.getFieldValue('Person_Birthday');
                        var age = swGetPersonAge(birth_date, new Date());
                        wnd.form.findField('PersonPrivilegeReq_DateRange').setMinValue(birth_date);
                        wnd.FilesInformationPanel.showDataByBirthDate(birth_date);
                        wnd.FileUploadPanel.setNecessaryDocumentListByAge(age);
                        wnd.FileUploadPanel.createDefaultCombo();
                    }
                });

                //загрузка данных о фамилии при рождении
				this.loadPersonSurNameAtBirth();

                //установка значений по умолчанию
                var date_str = (new Date()).format('d.m.Y') + ' - __.__.____';
                this.form.findField('PersonPrivilegeReq_DateRange').setValue(date_str);
                if (!Ext.isEmpty(arguments[0].PrivilegeType_id)) {
                    this.privilege_type_combo.setValue(arguments[0].PrivilegeType_id);
                }

                this.privilege_type_combo.setLinkedFields();
				break;
			case 'edit':
			case 'view':
				this.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
                this.setDisabled(this.action != 'edit');
                this.FileUploadPanel.listParams = {
                    ObjectName: 'PersonPrivilegeReq',
                    ObjectID: wnd.PersonPrivilegeReq_id,
                    add_empty_combo: false,
                    callback: function() {
                        wnd.setDisabled(wnd.action != 'edit');
                    }
                };
                this.FileUploadPanel.loadData();
                Ext.Ajax.request({
                    params:{
                        PersonPrivilegeReq_id: wnd.PersonPrivilegeReq_id
                    },
                    url:'/?c=Privilege&m=loadPersonPrivilegeReq',
                    success: function (response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) {
                            return false
                        }
                        wnd.form.setValues(result[0]);
                        wnd.res_form.setValues(result[0]);

                        if (!Ext.isEmpty(result[0].Person_id)) {
                            wnd.Person_id = result[0].Person_id;
                            wnd.PersonPanel.load({
                                Person_id: result[0].Person_id,
                                callback: function() {
                                    var birth_date = wnd.PersonPanel.getFieldValue('Person_Birthday');
                                    var age = swGetPersonAge(birth_date, new Date());
                                    wnd.form.findField('PersonPrivilegeReq_DateRange').setMinValue(birth_date);
                                    wnd.FilesInformationPanel.showDataByBirthDate(birth_date);
                                    wnd.FileUploadPanel.setNecessaryDocumentListByAge(age);
                                    wnd.FileUploadPanel.createDefaultCombo();
                                }
                            });
						}
						if (!Ext.isEmpty(result[0].Diag_id)) {
                            wnd.diag_combo.setValueById(result[0].Diag_id);
						}
                        if (!Ext.isEmpty(result[0].DocumentPrivilegeType_id)) {
                            wnd.document_type_combo.setValueById(result[0].DocumentPrivilegeType_id);
                        }
						if (!Ext.isEmpty(result[0].PersonPrivilegeReq_begDT) || !Ext.isEmpty(result[0].PersonPrivilegeReq_endDT)) {
                            var dates_str = "";
							dates_str += !Ext.isEmpty(result[0].PersonPrivilegeReq_begDT) ? result[0].PersonPrivilegeReq_begDT : "__.__.____";
							dates_str += " - ";
							dates_str += !Ext.isEmpty(result[0].PersonPrivilegeReq_endDT) ? result[0].PersonPrivilegeReq_endDT : "__.__.____";
                            wnd.form.findField('PersonPrivilegeReq_DateRange').setValue(dates_str);
						}
						if (!Ext.isEmpty(result[0].PersonSurNameAtBirth_SurName) && result[0].ApprovedReq_Cnt > 0) {
							//проверяем права на редактирование галочки
							wnd.checkAccessSurNameIsChanged();
						}
						wnd.PersonPrivilegeReqAns_IsInReg = result[0].PersonPrivilegeReqAns_IsInReg;

                        wnd.setStatus(result[0].PersonPrivilegeReqStatus_id);

                        wnd.setResultFields();
                        wnd.setDisabled(wnd.action != 'edit');

                        wnd.privilege_type_combo.setLinkedFields('set_by_id');

                        loadMask.hide();
                    },
                    failure:function () {
                        sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
                        loadMask.hide();
                        wnd.hide();
                    }
                });
				break;
		}
	},
	initComponent: function() {
		var wnd = this;

		wnd.PersonPanel = new sw.Promed.PersonInformationPanel({
			id: 'ppre_PersonPanel',
			region: 'north',
			style: 'padding-left: 10px; padding-top: 5px;',
			button2Callback: function(callback_data) {
				wnd.PersonPanel.load({
					Person_id: callback_data.Person_id,
					Server_id: callback_data.Server_id
				});
			}
		});
		wnd.PersonPanel.ButtonPanel.items.items[0].hide();
		wnd.PersonPanel.ButtonPanel.items.items[2].hide();
		wnd.PersonPanel.ButtonPanel.items.items[3].hide();
		wnd.PersonPanel.ButtonPanel.items.items[4].hide();

        wnd.FilesInformationPanel = new sw.Promed.HtmlTemplatePanel({
            win: this,
            frame: true,
            style: 'margin-top: 5px; margin-bottom: 5px;',
            showDataByBirthDate: function(birth_date) { //фильтрация строк в информационной панели по возрасту
                var age = swGetPersonAge(birth_date, new Date());
                this.setData('age0', '');
                this.setData('age14', '');
                this.setData('age18', '');
                var age_name = 'age0';
                if (age >= 18) {
                    age_name = 'age18';
                } else if (age >= 14) {
                    age_name = 'age14';
                }
                this.setData(age_name, 'style="display: none;"');
                this.showData();
            },
			showDataBySurNameIsChanged: function(birth_date) { //фильтрация строк в информационной панели по состоянию флага "Смена фамилии"
				var chk_field = wnd.form.findField('SurName_isChanged');
                this.setData('surname_is_not_changed', chk_field.checked ? '' : 'style="display: none;"');
                this.showData();
            }
        });
        //в шаблоне в строках задаются категории возраста для которых строка будет скрыта
        var tpl = "";
        /*tpl += "<table style='margin: 5px; float: left;'>";
        tpl += "<tr><td>Необходимо сделать копии:</td></tr>";
        tpl += "<tr {age0}{age14}><td>- паспорт (3 стр, 4 стр, страница с пропиской и следующая страница)</td></tr>";
        tpl += "<tr {age0}{age18}><td>- паспорт (3 стр, 4 стр, страница с пропиской и следующая страница) или свидетельство о рождении</td></tr>";
        tpl += "<tr {age14}{age18}><td>- свидетельство о рождении</td></tr>";
        tpl += "<tr {age18}><td>- свидетельство о регистрации по месту жительства, Форма №8 (для ребенка)</td></tr>";
        tpl += "<tr {age0}{age14}><td>- СНИЛС</td></tr>";
        tpl += "<tr {age18}><td>- СНИЛС ребенка</td></tr>";
        tpl += "<tr><td>- согласие на обработку перс. данных</td></tr>";
        tpl += "<tr {age18}><td>- документ родителя (законного представителя), потверждающий полномочия представления интересов ребенка (например, паспорт)</td></tr>";
        tpl += "<tr><td>- документ, подтверждающий социальную льготу (только при социальной льготе)</td></tr>";
        tpl += "</table>";*/
        tpl += "<table style='margin: 5px; float: left;'>";
        tpl += "<tr><td>Необходимо сделать копии:</td></tr>";
        tpl += "<tr {age0}><td>- паспорт 3-4 стр.</td></tr>";
        tpl += "<tr {age0}><td>- паспорт страница с пропиской</td></tr>";
        tpl += "<tr {age0}><td>- паспорт страница следующая за пропиской</td></tr>";
        tpl += "<tr {age14}{age18}><td>- подтверждение регистрации (форма 8)</td></tr>";
		tpl += "<tr><td>- СНИЛС</td></tr>";
		tpl += "<tr><td>- согласие на обработку перс. данных</td></tr>";
		tpl += "<tr><td>- документ, подтверждающий льготу</td></tr>";
		tpl += "<tr {surname_is_not_changed}><td>- свидетельство о рождении</td></tr>";
        tpl += "</table>";
        wnd.FilesInformationPanel.setTemplate(tpl);

        wnd.FileUploadPanel = new sw.Promed.FileUploadPanel({
            win: this,
            width: 1000,
            buttonAlign: 'left',
            buttonLeftMargin: 100,
            labelWidth: 150,
            commentLabelWidth: 100,
            commentTextfieldWidth: 430,
            folder: 'pmmedia/',
            fieldsPrefix: 'pmMediaData',
            id: 'ppre_FileUploadPanel',
            style: 'background: transparent;',
            dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
            saveUrl: '/?c=PMMediaData&m=uploadFile',
            saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
            deleteUrl: '/?c=PMMediaData&m=deleteFile',
            limitCountCombo: 30,
            listParams: {
                ObjectName: 'PersonPrivilegeReq',
                ObjectID: null
            },
            necessary_doc_list: new Array(),
            description_store: new Ext.data.SimpleStore({
                id: 0,
                fields: [
                    'Description_id',
                    'Description_Name',
                    'Description_SysNick'
                ],
                data: [
                    ['1', langs('Паспорт 3-4 стр.'), 'passport_3_4_page'],
                    ['2', langs('Паспорт страница с пропиской'), 'passport_registration_page'],
                    ['3', langs('Паспорт страница следующая за пропиской'), 'passport_registration_next_page'],
                    ['4', langs('Паспорт родителя (законного представителя)'), 'parent_passport'],
                    ['5', langs('Свидетельство о рождении'), 'birth_certificate'],
                    ['6', langs('Подтверждение регистрации (форма 8)'), 'registration_confirmation'],
                    ['7', langs('СНИЛС'), 'snils'],
                    ['8', langs('Согласие на обработку перс. данных'), 'data_processing'],
                    ['9', langs('Подтверждение льготы'), 'privilege_confirmation'],
					['10', langs('Медицинская документация'), 'med_doc'],
                    ['11', langs('Иное'), 'other']
                ],
                getNameByNick: function(nick) {
                    var name = null;
                    this.each(function(record) {
                        if (record.get('Description_SysNick') == nick) {
                            name = record.get('Description_Name');
                            return false;
                        }
                    });
                    return name
                },
                getNickByName: function(name) {
                    var nick = null;
                    this.each(function(record) {
                        if (record.get('Description_Name') == name) {
                            nick = record.get('Description_SysNick');
                            return false;
                        }
                    });
                    return nick
                }
            }),
            addCombo: function(is_filled, data) {
                if (is_filled) {
                    var ItemsIndex = data[this.fieldsPrefix+'_id'];
                    var fileup = new Ext.form.FormPanel({
                        id: 'Upload' + ItemsIndex,
                        bodyBorder: false,
                        border: false,
                        columnWidth: this.uploadFieldColumnWidth,
                        html: '<p style="float: left; margin: 7px 10px 0 0; width: 100px; text-align: right; font-size: 12px;">Документ:</p><p style="float: left; margin-top: 7px; font-size: 12px;">'+data[this.fieldsPrefix+'_FileLink']+'</p>'
                    });
                    var descr = new Ext.form.FormPanel({
                        layout: 'form',
                        columnWidth: this.commentTextColumnWidth,
                        bodyBorder: false,
                        border: false,
                        html: '<p style="float: left; margin: 7px 20px 0 0; width:'+(this.commentLabelWidth)+'px; text-align: right; font-size: 12px;">Тип докуиента:</p><p style="float: left; margin-top: 7px; font-size: 12px;">'+data[this.fieldsPrefix+'_Comment']+'</p>'
                    });
                } else {
                    this.lastItemsIndex--;
                    var ItemsIndex = this.lastItemsIndex;
                    var fileup = new Ext.form.FormPanel({
                        id: 'Upload' + ItemsIndex,
                        layout: 'form',
                        fileUpload: true,
                        columnWidth: this.uploadFieldColumnWidth,
                        items: [{
                            allowBlank: true,
                            id: 'FileUpload' + ItemsIndex,
                            fieldLabel: langs('Документ'),
                            name: 'userfile',
                            buttonText: langs('Выбрать'),
                            xtype: 'fileuploadfield',
                            listeners: {
                                'fileselected': function(field, value){
                                    this.loadFile(ItemsIndex);
                                }.createDelegate(this)
                            },
                            width: 250
                        }]
                    });
                    var descr = {
                        layout: 'form',
                        columnWidth: this.commentTextColumnWidth,
                        labelWidth: this.commentLabelWidth,
                        items:[{
                            id: 'FileDescr' + ItemsIndex,
                            fieldLabel: langs('Тип документа'),
                            xtype: 'swbaselocalcombo',
                            hiddenName: 'FileDescrName' + ItemsIndex,
                            displayField: 'Description_Name',
                            valueField: 'Description_Name',
                            editable: true,
                            width: this.commentTextfieldWidth,
                            tpl: new Ext.XTemplate(
                                '<tpl for="."><div class="x-combo-list-item">',
                                '{Description_Name}&nbsp;',
                                '</div></tpl>'
                            ),
                            store: this.description_store
                        }]
                    };
                }

                var c = new Ext.Panel({
                    id: 'Uploader' + ItemsIndex,
                    layout: 'column',
                    height: 35,
                    border: false,
                    defaults: {
                        border: false,
                        bodyStyle: 'background: transparent; padding-top: 5px'
                    },
                    items: [fileup, descr, {
                        layout: 'form',
                        columnWidth: .05,
                        items:[{
                            handler: function() {
                                this.deleteCombo(ItemsIndex)
                            }.createDelegate(this),
                            xtype: 'tbbutton',
                            iconCls: 'delete16',
                            tooltip: langs('Удалить')
                        }]
                    }]
                });
                var cb = this.add(c);
                this.doLayout();
                this.syncSize();

                if(this.win) {
                    this.win.syncSize();
                }

                return cb;
            },
            getCurrentFileList: function() { //возвращает текущий список файлов (за исключением удаленных записей)
                var current_window = this;
                var data = new Array();
                this.FileStore.each(function(record) {
                    if ((record.data.state != 'delete')) {
                        if (record.data.state == 'add') {
                            record.data[current_window.fieldsPrefix+'_Comment'] = this.findById('FileDescr' + record.data.Store_id).getValue();
                        }
                        data.push(record.data);
                    }
                }.createDelegate(this));
                return data;
            },
            setNecessaryDocumentListByAge: function(age) {
                /*var doc_array = ['parent_passport', 'birth_certificate', 'snils', 'data_processing'];
                if (age >= 18) {
                    doc_array = ['passport', 'snils', 'data_processing'];
                } else if (age >= 14) {
                    doc_array = ['passport|birth_certificate', 'parent_passport', 'snils', 'data_processing'];
                }*/
                var doc_array = ['registration_confirmation', 'snils', 'privilege_confirmation', 'data_processing'];
                if (age >= 14) {
                    doc_array = ['passport_3_4_page', 'passport_registration_page', 'passport_registration_next_page', 'snils', 'privilege_confirmation', 'data_processing'];
                }
                this.necessary_doc_list = doc_array;
				this.setNecessaryDocumentListBySurNameIsChanged();
            },
			setNecessaryDocumentListBySurNameIsChanged: function() {
            	var doc_array = this.necessary_doc_list;
				var pos = doc_array.indexOf('birth_certificate'); //ищем свидетельство о рождении в списке обязательных документов
				if (wnd.form.findField('SurName_isChanged').checked) {
					if (pos < 0) {
						doc_array.push('birth_certificate');
					}
				} else {
					if (pos > -1) {
						doc_array.splice(pos, 1);
					}
				}
				this.necessary_doc_list = doc_array;
			},
            getMissingNecessaryDocumentList: function(mode) { //получение списка отсутствующих обязательных документов, в качестве параметров указывается формат возвращаемых данных (названия документов тдт ники)
                var file_list = new Array();
                var missing_list = new Array();
                var check_list = this.necessary_doc_list;

                //собираем список файлов
                var file_data = this.getCurrentFileList();
                for (var i = 0; i < file_data.length; i++) {
                    //получение ника по имени
                    var doc_nick = this.description_store.getNickByName(file_data[i].pmMediaData_Comment);
                    if (!Ext.isEmpty(doc_nick)) {
                        file_list.push(doc_nick);
                    }
                }

                //полученеи списка недостающих документов
                for (i = 0; i < check_list.length; i++) {
                    var is_missing = true;

                    if (check_list[i].indexOf('|')) { //в случае если тип составной
                        for (var j = 0; j < file_list.length; j++) {
                            if (('|'+check_list[i]+'|').indexOf('|'+file_list[j]+'|') > -1) {
                                is_missing = false;
                                break;
                            }
                        }

                        if (is_missing) {
                            if (mode == 'name') {
                                //сборка имени из частей
                                var sub_list = check_list[i].split('|');
                                var doc_name = '';
                                for (var j = 0; j < sub_list.length; j++) {
                                    //получение имени по нику
                                    var sub_name = this.description_store.getNameByNick(sub_list[j]);
                                    if (!Ext.isEmpty(sub_name)) {
                                        doc_name += (doc_name != '' ? ' или ' : '')+sub_name;
                                    }
                                }
                                if (!Ext.isEmpty(doc_name)) {
                                    missing_list.push(doc_name);
                                }
                            }
                            if (mode == 'nick') {
                                missing_list.push(check_list[i]);
                            }
                        }
                    } else {
                        for (var j = 0; j < file_list.length; j++) {
                            if (file_list[j] == check_list[i]) {
                                is_missing = false;
                                break;
                            }
                        }

                        if (is_missing) {
                            if (mode == 'name') {
                                //получение имени по нику
                                var doc_name = this.description_store.getNameByNick(check_list[i]);
                                if (!Ext.isEmpty(doc_name)) {
                                    missing_list.push(doc_name);
                                }
                            }
                            if (mode == 'nick') {
                                missing_list.push(check_list[i]);
                            }
                        }
                    }
                }

                return missing_list;
            },
            checkNecessaryDocumentList: function(callback) { //проверка списка необходимых документов
                var missing_list = this.getMissingNecessaryDocumentList('name');
				var check_data = new Object({
					check_result: false,
					check_msg: langs('При проверке списка документов возникла ошибка')
				});

                //формирование сообещния если необходимо
                if (missing_list.length > 0) {
                    check_data.check_result = false;
                    if (missing_list.length == 1) {
                        check_data.check_msg = 'К запросу необходимо приложить '+missing_list[0];
                    } else {
                        check_data.check_msg = 'К запросу необходимо приложить следующие документы: '+(missing_list.join(', '));
                    }
                } else {
                    check_data.check_result = true;
                }

                if (check_data.check_result) {
                    if (typeof callback == 'function') {
                        callback();
                    }
                } else {
                    sw.swMsg.alert('Ошибка', check_data.check_msg);
                }
            },
            createDefaultCombo: function() {
                var check_list = this.getMissingNecessaryDocumentList('nick');
                var doc_list = new Array();

                //если компонент уже заполнен, то предварительно нужно удалить не заполненные элементы
                if (this.items.getCount() > 0) {
					this.clearEmptyCombo();
				}

                //формируем список документов
                for (var i = 0; i < check_list.length; i++) {
                    if (!Ext.isEmpty(check_list[i])) {
                        if (check_list[i].indexOf('|') > -1) {
                            var sub_list = check_list[i].split('|');
                            for (var j = 0; j < sub_list.length; j++) {
                                doc_list.push(sub_list[j]);
                            }
                        } else {
                            doc_list.push(check_list[i]);
                        }
                    }
                }

                //создания списка комбобоксов по умолчанию
                for (var i = 0; i < doc_list.length; i++) {
                    this.addCombo(false);
                }

                //установка типов документов по умолчанию
                for (var i = 0; i < doc_list.length; i++) {
                    var doc_name = this.description_store.getNameByNick(doc_list[i]);
                    // установка описаний для сформированных элементов
					// doc_list.length+this.lastItemsIndex) - расчет стартового индекса именуемой группы;
					// (i+1)*(-1) - расчет смещения внутри группы;
					// отрицательные чцисла чтобы не пересечься с загруженными данными
                    this.findById('FileDescr' + ((doc_list.length+this.lastItemsIndex)+((i+1)*(-1)))).setValue(doc_name);
                }
            },
			clearEmptyCombo: function() { //удаление пустых записей
				var file_panel = this;
				var file_items = [];

				//формируем список компонентов, котоыре связаны с загруженнми файлами
				this.FileStore.each(function(rec) {
					if (!Ext.isEmpty(rec.get('Store_id'))) {
						file_items.push('Uploader'+rec.get('Store_id'));
					}
				});

				this.items.each(function(item) {
					if (item.id && item.id.indexOf('Uploader') > -1 && file_items.indexOf(item.id) < 0) {
						file_panel.remove(item, true);
					}
				});

				this.checkLimitCountCombo();
			}
        });

        this.privilege_type_combo = new sw.Promed.SwPrivilegeTypeCombo({
            xtype: 'swprivilegetypecombo',
            fieldLabel: 'Льготная категория',
            hiddenName: 'PrivilegeType_id',
            width: 300,
            allowBlank: false,
            listeners: {
                select: function() {
                    this.setLinkedFields();
                },
                blur: function() {
                    this.setLinkedFields();
                }
            },
            getSelectedRecordData: function() {
                var combo = this;
                var value = combo.getValue();
                var data = new Object();
                if (value > 0) {
                    var idx = this.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == value);
                    })
                    if (idx > -1) {
                        Ext.apply(data, this.getStore().getAt(idx).data);
                    }
                }
                return data;
            },
            setLinkedFields: function(event_name) {
                var doc_visible = false;
                var diag_visible = false;

                if (this.getValue() > 0) {
                    var privilege_data = this.getSelectedRecordData();

                    //льгота явдялется федеральной или льгота является региональной и имеет признак «Документ на льготу»; настройка "Льготы социальные. Контроль на наличие данных документа, подтверждающего наличие льгот" включена
                    doc_visible = ((privilege_data && privilege_data.ReceptFinance_id == 1) || (privilege_data && privilege_data.ReceptFinance_id == 2 && privilege_data.PrivilegeType_IsDoc == 2 && getGlobalOptions().social_privilege_document_available_checking));

                    //льгота является региональной и имеет признак «Нозология»; настройка "Льготы по нозологиям. Контроль на наличие диагноза" включена
                    diag_visible = (privilege_data && privilege_data.ReceptFinance_id == 2 && privilege_data.PrivilegeType_IsNoz == 2 && getGlobalOptions().vzn_privilege_diag_available_checking);
                }

                var doc_blocked = (!doc_visible || (wnd.postmoderation_mode && wnd.action == 'edit'));
                var diag_blocked = (!diag_visible || (wnd.postmoderation_mode && wnd.action == 'edit'));

                wnd.form.findField('DocumentPrivilegeType_id').enable_blocked = doc_blocked;
                wnd.form.findField('DocumentPrivilege_Ser').enable_blocked = doc_blocked;
                wnd.form.findField('DocumentPrivilege_Num').enable_blocked = doc_blocked;
                wnd.form.findField('DocumentPrivilege_begDate').enable_blocked = doc_blocked;
                wnd.form.findField('DocumentPrivilege_Org').enable_blocked = doc_blocked;
                wnd.form.findField('DocumentPrivilegeType_id').allowBlank = doc_blocked;
                wnd.form.findField('DocumentPrivilege_Ser').allowBlank = doc_blocked;
                wnd.form.findField('DocumentPrivilege_Num').allowBlank = doc_blocked;
                wnd.form.findField('DocumentPrivilege_begDate').allowBlank = doc_blocked;
                wnd.form.findField('DocumentPrivilege_Org').allowBlank = doc_blocked;
                if (doc_visible) {
                    wnd.form.findField('DocumentPrivilegeType_id').ownerCt.show();
                } else {
                    wnd.form.findField('DocumentPrivilegeType_id').ownerCt.hide();
                }

                if (wnd.form.findField('Diag_id').getStore().baseParams.PrivilegeType_id != this.getValue()) {
                    if (event_name != 'set_by_id') {
                        wnd.form.findField('Diag_id').fullReset();
                    }
                    wnd.form.findField('Diag_id').getStore().baseParams.PrivilegeType_id = this.getValue();
                }

                wnd.form.findField('Diag_id').enable_blocked = diag_blocked;
                wnd.form.findField('Diag_id').allowBlank = diag_blocked;
                if (diag_visible) {
                    wnd.form.findField('Diag_id').showContainer();
                } else {
                    wnd.form.findField('Diag_id').hideContainer();
                }

                //неиспользуемые поля нужно заблокировать, таким образом они не будут переданы при сохранении и будут автоматически очищены в данных запроса
                wnd.setDisabled(wnd.action == 'view');
            }
        });

        this.diag_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Диагноз'),
            hiddenName: 'Diag_id',
            displayField: 'Diag_Name',
            valueField: 'Diag_id',
            editable: true,
            allowBlank: false,
            width: 700,
            listWidth: 700,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'Diag_id'
                }, [
                    {name: 'Diag_id', mapping: 'Diag_id'},
                    {name: 'Diag_Code', mapping: 'Diag_Code'},
                    {name: 'Diag_Name', mapping: 'Diag_Name'}
                ]),
                url: '/?c=Privilege&m=loadDiagByPrivilegeTypeCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table><tr><td style="width: 40px;"><font color="red">{Diag_Code}</font>&nbsp;</td><td>{Diag_Name}&nbsp;</td></tr></table>',
                '</div></tpl>'
            )
        });

        this.msf_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Врач'),
            hiddenName: 'MedStaffFact_id',
            displayField: 'MedStaffFact_Name',
            valueField: 'MedStaffFact_id',
            editable: true,
            allowBlank: false,
            width: 700,
            listWidth: 700,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'MedStaffFact_id'
                }, [
                    {name: 'MedStaffFact_id', mapping: 'MedStaffFact_id'},
                    {name: 'Msf_Lpu_Nick', mapping: 'Msf_Lpu_Nick'},
                    {name: 'MedStaffFact_Name', mapping: 'MedStaffFact_Name'}
                ]),
                url: '/?c=Privilege&m=loadPersonPrivilegeReqMedStaffFactCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table><tr><td align="left">{MedStaffFact_Name}&nbsp;</td></tr></table>',
                '</div></tpl>'
            ),
            setLinkedFieldValues: function(event_name) {
                var msf_data = new Object();
                if (this.getValue() > 0) {
                    var msf_data = this.getSelectedRecordData();
                }
                wnd.setCommonInformationData(msf_data);
            }
        });

        this.document_type_combo = new sw.Promed.SwCustomRemoteCombo({
            fieldLabel: langs('Вид документа'),
            hiddenName: 'DocumentPrivilegeType_id',
            displayField: 'DocumentPrivilegeType_Name',
            valueField: 'DocumentPrivilegeType_id',
            editable: true,
            allowBlank: false,
            width: 300,
            listWidth: 300,
            triggerAction: 'all',
            store: new Ext.data.Store({
                autoLoad: false,
                reader: new Ext.data.JsonReader({
                    id: 'DocumentPrivilegeType_id'
                }, [
                    {name: 'DocumentPrivilegeType_id', mapping: 'DocumentPrivilegeType_id'},
                    {name: 'DocumentPrivilegeType_Code', mapping: 'DocumentPrivilegeType_Code'},
                    {name: 'DocumentPrivilegeType_Name', mapping: 'DocumentPrivilegeType_Name'}
                ]),
                url: '/?c=Privilege&m=loadDocumentPrivilegeTypeCombo'
            }),
            trigger2Class: 'x-form-plus-trigger',
            onTrigger2Click: function() {
                var combo = this;
                if (!combo.disabled) {
                    getWnd('swDocumentPrivilegeTypeAddWindow').show({
                        callback: function(data) {
                            if (!Ext.isEmpty(data.DocumentPrivilegeType_id)) {
                                combo.setValueById(data.DocumentPrivilegeType_id);
                            }
                        }
                    });
                }
            }
        });

        this.privilege_date_field = new Ext.form.DateRangeField({
            xtype: 'daterangefield',
            name: 'PersonPrivilegeReq_DateRange',
            fieldLabel: langs('Период действия льготы'),
            plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
            width: 170,
            allowBlank: false,
            validateValue: function (value) {
                var ar = value.split(' - ');
                if (ar[0] == '__.__.____'/* || ar[1] == '__.__.____'*/) {
                    this.markInvalid(langs('Введите первую дату'));
                    return false;
                }
                if (0 == value.length)
                {
                    if (this.allowBlank) {
                        return true;
                    } else {
                        this.markInvalid("Поле не может быть пустым");
                        return false;
                    }
                }
                ar[0] = this.formatDate(ar[0]);
                d1 = this.parseDate(ar[0]);
                if (!d1 && ar[0] != '__.__.____') {
                    this.markInvalid(String.format("Первая дата введена неправильно", ar[0], this.format));
                    return false;
                }
                if (d1) {
                    var time1 = d1.getTime();
                    if (this.minValue && time1 < this.minValue.getTime()) {
                        this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
                        return false;
                    }
                    if (this.maxValue && time1 > this.maxValue.getTime()) {
                        this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
                        return false;
                    }
                }

                ar[1] = this.formatDate(ar[1]);
                d2 = this.parseDate(ar[1]);
                if (!d2 && ar[1] != '__.__.____') {
                    this.markInvalid(String.format("Вторая дата введена неправильно", ar[1], this.format));
                    return false;
                }
                if (d2) {
                    var time2 = d2.getTime();
                    if (this.minValue && time2 < this.minValue.getTime()) {
                        this.markInvalid(String.format(this.minText, this.formatDate(this.minValue)));
                        return false;
                    }
                    if (this.maxValue && time2 > this.maxValue.getTime()) {
                        this.markInvalid(String.format(this.maxText, this.formatDate(this.maxValue)));
                        return false;
                    }
                }

                if (d1 && d2 && d1 > d2) {
                    this.markInvalid(langs('Дата начала должна быть меньше даты конца'));
                    return false;
                }

                return true;
            }
        });

        var form = new Ext.Panel({
            autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0;',
            height: 70,
            border: false,
            frame: true,
            region: 'center',
            labelAlign: 'right',
            items: [{
                xtype: 'form',
                autoHeight: true,
                id: 'PersonPrivilegeReqEditForm',
                url: '/?c=Privilege&m=savePersonPrivilegeReq',
                bodyStyle: 'background: #DFE8F6; padding: 0px;',
                border: true,
                labelWidth: 215,
                labelAlign: 'right',
                collapsible: true,
                items: [{
					xtype: 'hidden',
					name: 'PersonPrivilegeReq_id'
				}, {
					xtype: 'hidden',
					name: 'DocumentPrivilege_id'
				}, {
					xtype: 'fieldset',
					title: langs('Запрос подан'),
					autoHeight: true,
					style: 'padding: 3px; margin-bottom: 7px; display: block;',
					labelWidth: 210,
					items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: 'Дата и время подачи запроса ',
                            name: 'PersonPrivilegeReq_setDT',
                            width: 300,
                            disabled: true
                        }, {
                            xtype: 'textfield',
                            fieldLabel: 'МО',
                            name: 'Msf_Lpu_Nick',
                            width: 300,
                            disabled: true
                        }, {
					        layout: 'form',
                            items: [{
                                xtype: 'textfield',
                                fieldLabel: 'Врач',
                                name: 'Msf_FullName',
                                width: 700,
                                disabled: true
                            }]
                        },
                        this.msf_combo
                    ]
				},
                this.privilege_type_combo,
                this.diag_combo,
                {
					xtype: 'fieldset',
					title: langs('Документ о праве на льготу'),
					autoHeight: true,
					style: 'padding: 3px; margin-bottom: 7px; display: block;',
					labelWidth: 210,
					items: [
                    this.document_type_combo,
                    {
						xtype: 'textfield',
						fieldLabel: 'Серия документа',
						name: 'DocumentPrivilege_Ser',
						width: 300
					}, {
						xtype: 'textfield',
						fieldLabel: 'Номер документа',
						name: 'DocumentPrivilege_Num',
						width: 300
					}, {
						xtype: 'swdatefield',
						format: 'd.m.Y',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						fieldLabel: 'Дата выдачи документа',
						name: 'DocumentPrivilege_begDate'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Организация, выдавшая документ',
						name: 'DocumentPrivilege_Org',
						width: 300
					}]
				},
                this.privilege_date_field,
				{
					xtype: 'checkbox',
					fieldLabel: 'Смена фамилии',
					name: 'SurName_isChanged',
					width: 300,
					listeners: {
						check: function() {
							wnd.setPersonSurNameAtBirthFields();
						}
					}
				}, {
					layout: 'form',
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Фамилия при рождении',
						name: 'PersonSurNameAtBirth_SurName',
						width: 300
					}]
				}]
            },
            wnd.FilesInformationPanel,
            {
				xtype: 'fieldset',
				title: langs('Передаваемые документы'),
				autoHeight: true,
				style: 'padding: 3px; margin-bottom: 2px; display: block;',
				items: [
					wnd.FileUploadPanel
				]
			}, {
                xtype: 'form',
                autoHeight: true,
                id: 'PersonPrivilegeReqResultEditForm',
                bodyStyle: 'background: #DFE8F6; padding: 0px; margin-top: 5px;',
                border: true,
                labelWidth: 215,
                labelAlign: 'right',
                collapsible: true,
                items: [{
					xtype: 'textfield',
					fieldLabel: 'Результат',
					name: 'Result_Data',
                    disabled: true,
                    width: 300
				}, {
					xtype: 'textfield',
					fieldLabel: 'Причина отказа',
					name: 'PersonPrivilegeReqAns_DeclCause',
					disabled: true,
					width: 300
				}]
            }]
        });

		Ext.apply(this, {
			layout: 'border',
			buttons: [{
				handler: function() {
				    wnd.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				handler: function() {
                    wnd.FileUploadPanel.checkNecessaryDocumentList(function() { wnd.doSave({send_to_expertise: true}); });
				},
				iconCls: 'save16',
				text: langs('Сохранить и передать запрос')
			},
			{
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
                wnd.PersonPanel,
				form
			]
		});
		sw.Promed.swPersonPrivilegeReqEditWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findById('PersonPrivilegeReqEditForm').getForm();
        this.res_form = this.findById('PersonPrivilegeReqResultEditForm').getForm();
	}
});