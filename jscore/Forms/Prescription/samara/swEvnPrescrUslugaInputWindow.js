/**
 * swEvnPrescrUslugaInputWindow - окно добавления назначений с услугой
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EvnPrescr
 * @access       public
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @version      09.2013
 */

sw.Promed.swEvnPrescrUslugaInputWindow = Ext.extend(sw.Promed.BaseForm, {
    codeRefresh: true,
    objectName: 'swEvnPrescrUslugaInputWindow',
    objectSrc: '/jscore/Forms/Prescription/swEvnPrescrUslugaInputWindow.js',
    collapsible: false,
    draggable: true,
    height: 550,
    id: 'EvnPrescrUslugaInputWindow',
    buttonAlign: 'left',
    closeAction: 'hide',
    maximized: true,
    minHeight: 550,
    minWidth: 800,
    modal: true,
    resizable: false,
    plain: true,
    width: 800,
    winTitle: 'Добавление назначения',
    listeners:
    {
        hide: function(win)
        {
            if (win.hasChange) {
                win.callback();
            }
            win.onCancel();
            win.onHide();
        }
    },

    callback: Ext.emptyFn,
    onHide: Ext.emptyFn,
    userMedStaffFact: {},
    hasChange: false,

    /**
     * Показываем окно
     * @return {Boolean}
     */
    show: function() {
        sw.Promed.swEvnPrescrUslugaInputWindow.superclass.show.apply(this, arguments);
        var thas = this;
        var base_form = thas.filterPanel.getForm();
        
        if (!arguments[0]
            || !arguments[0].PrescriptionType_Code
            || !arguments[0].Person_Surname
            || !arguments[0].userMedStaffFact
            || !arguments[0].formParams
            || !arguments[0].formParams.Evn_pid
            || !arguments[0].formParams.parentEvnClass_SysNick
            || !arguments[0].formParams.PrescriptionType_id
            || !arguments[0].formParams.PersonEvn_id
            ) {
            sw.swMsg.alert('Ошибка', 'Неверные параметры', function() {thas.hide();} );
            return false;
        }
        this.PrescriptionType_Code = arguments[0].PrescriptionType_Code;
        this.userMedStaffFact = arguments[0].userMedStaffFact;
        this.callback = Ext.emptyFn;
        this.hasChange = false;
        if (typeof arguments[0].callback == 'function') {
            this.callback = arguments[0].callback;
        }
        this.onHide = Ext.emptyFn;
        if (typeof arguments[0].onHide == 'function') {
            this.onHide = arguments[0].onHide;
        }

        base_form.reset();
        
        // ipavelpetrov
        var medService = base_form.findField('MedService_id');
        var ptc = arguments[0].PrescriptionType_Code
    	medService.hideContainer();
        if ( ptc === 11 || ptc === 12) { 
        	medService.showContainer();
        }
        
        medField = medService;
        
        var title = this.winTitle +'. '+ arguments[0].Person_Surname;
        this.Person_Surname = arguments[0].Person_Surname;
        this.Person_Firname = null;
        if (arguments[0].Person_Firname) {
            title += ' '+ arguments[0].Person_Firname;
            this.Person_Firname = arguments[0].Person_Firname;
        }
        this.Person_Secname = null;
        if (arguments[0].Person_Secname) {
            title += ' '+ arguments[0].Person_Secname;
            this.Person_Secname = arguments[0].Person_Secname;
        }
        this.Person_Birthday = arguments[0].Person_Birthday || null;
        this.Person_Age = arguments[0].Person_Age || null;
        if (false && this.Person_Age) {
            title += ', '+ this.Person_Age;
        }
        this.Diag_Code = null;
        if (arguments[0].Diag_Code ) {
            title += '. '+ arguments[0].Diag_Code;
            this.Diag_Code = arguments[0].Diag_Code;
        }
        this.Diag_Name = null;
        if (arguments[0].Diag_Name) {
            title += '. '+ arguments[0].Diag_Name;
            this.Diag_Name = arguments[0].Diag_Name;
        }
        this.formParams = arguments[0].formParams;
        this.setTitle(title);

        if (typeof arguments[0].formParams.EvnPrescr_setDate == 'object') {
            arguments[0].formParams.EvnPrescr_setDate = arguments[0].formParams.EvnPrescr_setDate.format('d.m.Y');
        }
        base_form.setValues(arguments[0].formParams);
		this._loadPrescriptionListByType(arguments[0].formParams.PrescriptionType_id);
        this.loadDataViewStore(base_form.findField('Evn_pid').getValue());
		base_form.findField('UslugaComplex_id').setDisallowedUslugaComplexAttributeList([ 'noprescr' ]);
		
        base_form.findField('Lpu_id').setValue(arguments[0].userMedStaffFact.Lpu_id);
        base_form.findField('Lpu_id').fireEvent('change');
		
        return true;
    },
    /**
     * Снимаем блокировку с бирок
     */
    onCancel: function()
    {
        var time_id, thas = this;
        for (var key in this.dataView.assocLockedTimetableMedServiceId) {
            time_id = this.dataView.assocLockedTimetableMedServiceId[key];
            sw.Promed.Direction.unlockTime(this, 'TimetableMedService', time_id, function(){
                delete thas.dataView.assocLockedTimetableMedServiceId[key];
            });
        }
    },
    /**
     * Сохраняем назначения, внесенные в DataView store
     * Каждое сохранение новой записи производим отдельным запросом в транзакции.
     * В случае успешного сохранения происходит возврат в ЭМК.
     * @return {Boolean}
     */
    doSave: function(new_records, has_error)
    {
        var thas = this;
        var store = this.dataView.getStore();
        if (!new_records) {
            if ( this.formStatus == 'save' ) {
                return false;
            }
            new_records = [];
            store.each(function(rec){
                if (rec.get('RecordStatus') == 'new') {
                    new_records.push(rec);
                }
                return true;
            }, this);
            if (new_records.length == 0) {
                this.hide();
                //sw.swMsg.alert('Сообщение', 'Новые назначения отсутсвуют!');
                return false;
            }
        }
        if (new_records.length == 0) {
            if (!has_error) {
                //все назначения сохранены успешно
                this.callback();
                this.hide();
            }
            this.formStatus = 'edit';
            return true;
        }
        this.formStatus = 'save';
        this._save(new_records[0], function(new_record, error_msg){
            if (!error_msg) {
                new_records.shift();
                thas.doSave(new_records, has_error);
            } else {
                //или вывести вопрос о продолжении сохранения или продолжить сохранение других записей
                sw.swMsg.show({
                    buttons: Ext.Msg.YESNO,
                    fn: function(buttonId)
                    {
                        if ('yes' == buttonId) {
                            new_records.shift();
                            thas.doSave(new_records, true);
                        } else {
                            thas.formStatus = 'edit';
                        }
                    },
                    icon: Ext.Msg.ERROR,
                    msg: 'При сохранении назначения услуги <br/>"'+ new_record.get('Usluga_List')
                        +'"<br/>произошла ошибка: '+ error_msg
                        +'. <br/>Продолжить сохранение?',
                    title: 'Вопрос'
                });
            }
        });
        return true;
    },
    /**
     * Сохраняем назначение-направление в транзакции
     */
    _save: function(new_record, callback)
    {
        var thas = this,
            formParams = this.filterPanel.getForm().getValues(),
            key = new_record.get('EvnPrescr_key'),
            uslugaFrameRec = this.dataView.assocUslugaFrameRec[key],
            direction = this.dataView.assocDirection[key],
            evnPrescrData = { //параметры для сохранения назначения
                PersonEvn_id: formParams.PersonEvn_id
                ,Server_id: formParams.Server_id
                ,parentEvnClass_SysNick: formParams.parentEvnClass_SysNick
            },
            params = { //параметры для функции создания направления
                person: {
                    Person_id: formParams.Person_id
                    ,PersonEvn_id: formParams.PersonEvn_id
                    ,Server_id: formParams.Server_id
                },
                needDirection: false,
                mode: 'nosave',
                loadMask: false,
                windowId: 'EvnPrescrUslugaInputWindow',
                callback: function(){
                    log(arguments);
                    thas.getLoadMask().hide();
                    //todo Если не удалось создать направление, то удалить назначение
                    //Устанавливаем признак того, что назначение сохранено в БД
                    thas.dataView.updateRec(new_record, {
                        RecordStatus: ''
                    });
                    delete thas.dataView.assocUslugaFrameRec[key];
                    delete thas.dataView.assocDirection[key];
                    delete thas.dataView.assocLockedTimetableMedServiceId[key];
                    callback(new_record, null);
                }
            },
            checked = [],//список услуг для заказа
            save_url,
            prescr_code;

        switch (parseInt(new_record.get('PrescriptionType_id'))) {
            case 6:
                save_url = '/?c=EvnPrescr&m=saveEvnCourseProc';
                prescr_code = 'EvnPrescrProc';
                evnPrescrData.EvnCourseProc_id = null;
                evnPrescrData.EvnCourseProc_pid = formParams.Evn_pid;
                evnPrescrData.Morbus_id = null;
                evnPrescrData.EvnCourseProc_MinCountDay = null;
                evnPrescrData.MedPersonal_id = thas.userMedStaffFact.MedPersonal_id;
                evnPrescrData.LpuSection_id = thas.userMedStaffFact.LpuSection_id;
                evnPrescrData.EvnCourseProc_setDate = new_record.get('EvnPrescr_setDate');
                evnPrescrData.DurationType_id = new_record.get('DurationType_id');
                evnPrescrData.DurationType_recid = new_record.get('DurationType_recid');
                evnPrescrData.DurationType_intid = new_record.get('DurationType_intid');
                evnPrescrData.EvnCourseProc_MaxCountDay = new_record.get('CountInDay');
                evnPrescrData.EvnCourseProc_Duration = new_record.get('CourseDuration');
                evnPrescrData.EvnCourseProc_ContReception = new_record.get('ContReception');
                evnPrescrData.EvnCourseProc_Interval = new_record.get('Interval');
                evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
            case 7:
                save_url = '/?c=EvnPrescr&m=savePolkaEvnPrescrOper';
                prescr_code = 'EvnPrescrOper';
                evnPrescrData.EvnPrescrOper_uslugaList = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
            case 11:
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrLabDiag';
                prescr_code = 'EvnPrescrLabDiag';
                if (uslugaFrameRec.compositionMenu) {
                    uslugaFrameRec.compositionMenu.items.each(function(item){
                        if (item.checked) {
                            checked.push(item.UslugaComplex_id);
                        }
                    });
                }
                evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
                evnPrescrData.EvnPrescrLabDiag_uslugaList = checked.toString();
                break;
            case 12:
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrFuncDiag';
                prescr_code = 'EvnPrescrFuncDiag';
                evnPrescrData.EvnPrescrFuncDiag_uslugaList = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
            case 13:
                save_url = '/?c=EvnPrescr&m=saveEvnPrescrConsUsluga';
                prescr_code = 'EvnPrescrConsUsluga';
                evnPrescrData.UslugaComplex_id = uslugaFrameRec.get('UslugaComplex_id');
                checked.push(uslugaFrameRec.get('UslugaComplex_id'));
                break;
        }
        if (!save_url) {
            callback(new_record, 'Назначение имеет неправильный тип');
            return false;
        }

        evnPrescrData[prescr_code +'_id'] = null;
        evnPrescrData[prescr_code +'_pid'] = formParams.Evn_pid;
        evnPrescrData[prescr_code +'_IsCito'] = (new_record.get('EvnPrescr_IsCito'))?'on':'off';
        evnPrescrData[prescr_code +'_setDate'] = new_record.get('EvnPrescr_setDate') || getGlobalOptions().date;
        evnPrescrData[prescr_code +'_Descr'] = new_record.get('EvnPrescr_Descr') || '';

        thas.getLoadMask('Пожалуйста, подождите!' +
            '<br/>Идет сохранение назначения услуги '+ new_record.get('Usluga_List')).show();
        Ext.Ajax.request({
            url: save_url,
            params: evnPrescrData,
            callback: function(o, s, r) {
                thas.getLoadMask().hide();
                if(s) {
                    var response_obj = Ext.util.JSON.decode(r.responseText);
                    if ( response_obj.success && response_obj.success === true) {
                        if (6 == new_record.get('PrescriptionType_id')) {
                            direction.EvnPrescr_id = response_obj[prescr_code +'_id0'];
                        } else {
                            direction.EvnPrescr_id = response_obj[prescr_code +'_id'];
                        }
                        //new_record.set('EvnPrescr_key', direction.EvnPrescr_id);
                        thas.dataView.updateRec(new_record, {
                            EvnPrescr_id: direction.EvnPrescr_id,
                            RecordStatus: 'inserted'
                        });
                        //log(direction);
                        if (!direction.Lpu_did) {
                            // создаем только назначение
                            params.callback();
                            return true;
                        }
                        params.direction = direction;
                        params.order = {
                            LpuSectionProfile_id: direction.LpuSectionProfile_id
                            ,UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id')
                            ,checked: Ext.util.JSON.encode(checked)
                            ,Usluga_isCito: (new_record.get('EvnPrescr_IsCito'))?2:1
                            ,UslugaComplex_Name: uslugaFrameRec.get('UslugaComplex_Name')
                            ,UslugaComplexMedService_id: uslugaFrameRec.get('UslugaComplexMedService_id')
                            ,MedService_id: uslugaFrameRec.get('MedService_id')
                            ,MedService_pzNick: uslugaFrameRec.get('pzm_MedService_Nick')
                            ,MedService_pzid: uslugaFrameRec.get('pzm_MedService_id')
                        };
                        direction['order'] = Ext.util.JSON.encode(params.order);
                        thas.getLoadMask('Пожалуйста, подождите!' +
                            '<br/>Идет сохранение направления на услугу '+ new_record.get('Usluga_List')).show();
                        if (uslugaFrameRec.get('TimetableMedService_id') > 0) {
                            params.Timetable_id = uslugaFrameRec.get('TimetableMedService_id');
                            params.order.TimetableMedService_id = uslugaFrameRec.get('TimetableMedService_id');
                            //sw.Promed.Direction.recordPerson(params);
                            direction['TimetableMedService_id'] = params.Timetable_id;
                            sw.Promed.Direction.requestRecord({
                                url: C_TTMS_APPLY,
                                loadMask: params.loadMask,
                                windowId: params.windowId,
                                params: direction,
                                //date: conf.date || null,
                                Timetable_id: params.Timetable_id,
                                fromEmk: false,
                                mode: 'nosave',
                                needDirection: false,
                                Unscheduled: false,
                                onHide: Ext.emptyFn,
                                onSaveRecord: Ext.emptyFn,
                                callback: params.callback
                            });
                        } else {
                            //sw.Promed.Direction.queuePerson(params);
                            direction.MedService_did = direction.MedService_id;
                            direction.LpuSectionProfile_did = direction.LpuSectionProfile_id;
                            direction.EvnQueue_pid = direction.EvnDirection_pid;
                            direction.MedStaffFact_id = null;
							direction.Prescr = "Prescr";
                            /*
                            direction.LpuSection_did = null;
                            direction.MedService_did = null;
                            direction.LpuUnit_did = null;
                            */
                            sw.Promed.Direction.requestQueue({
                                params: direction,
                                loadMask: params.loadMask,
                                windowId: params.windowId,
                                callback: params.callback
                            });
                        }
                    } else {
                        callback(new_record, response_obj.Error_Msg);
                    }
                } else {
                    callback(new_record, 'Ошибка сервера');
                }
                return true;
            }
        });
        return true;
    },
    /**
     * Проверки перед добавлением записи в правую часть
     * @param rec
     * @return {Boolean}
     */
    _validate: function(rec)
    {
        if (rec.get('ttms_MedService_id') && !rec.get('TimetableMedService_id')) {
            sw.swMsg.alert('Ошибка', 'Добавление невозможно, не выбрано время приема. <br>Выберите время записи на прием');
            return false;
        }
        return true;
    },
    /**
     * Асинхронная проверка времени записи перед добавлением в правую часть
     * @param {Ext.data.Record} rec
     * @param {function} callback
     * @return {Boolean}
     */
    _checkBeforeLock: function(rec, callback)
    {
        var thas = this,
            formParams = this.filterPanel.getForm().getValues();

        if (rec.get('EvnPrescr_IsCito') && rec.get('MedService_id')) {
            //надо создать доп.бирку и записывать на неё
            this.getLoadMask('Пожалуйста, подождите, идет создание дополнительной бирки!').show();
            Ext.Ajax.request({
                params: {
                    Day: null,
                    StartTime: null,
                    MedService_id: rec.get('MedService_id'),
                    UslugaComplexMedService_id: rec.get('UslugaComplexMedService_id'),
                    TimetableExtend_Descr: ''
                },
                callback: function(options, success, response) {
                    thas.getLoadMask().hide();
                    if ( success ) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        if (response_obj.Error_Msg) {
                            callback(false, response_obj.Error_Msg);
                        } else if (response_obj.TimetableMedService_id && response_obj.TimetableMedService_begTime) {
                            rec.set('TimetableMedService_id', response_obj.TimetableMedService_id);
                            var dt = Date.parseDate(response_obj.TimetableMedService_begTime, 'Y-m-d H:i:s');
                            rec.set('TimetableMedService_begTime', dt.format('d.m.Y H:i'));
                            rec.commit();
                            callback(true, null);
                        }
                        return true;
                    }
                    callback(false, 'Создание дополнительной бирки не удалось!');
                    return true;
                },
                url: '/?c=TimetableMedService&m=addTTMSDop'
            });
            return true;
        }

        if (!rec.get('TimetableMedService_id')) {
            callback(true, null);
            return true;
        }

        this.getLoadMask(LOAD_WAIT).show();
        Ext.Ajax.request({
            params: {
                Person_id: formParams.Person_id,
                TimetableMedService_id: rec.get('TimetableMedService_id')
            },
            callback: function(options, success, response) {
                thas.getLoadMask().hide();
                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.Error_Msg) {
                        callback(false, response_obj.Error_Msg);
                    } else if (response_obj.Alert_Msg) {
                        // Варианты действий: Записать/Выбрать другое время.
                        sw.swMsg.show({
                            buttons:Ext.Msg.YESNO,
                            fn:function (buttonId) {
                                if (buttonId == 'yes') {
                                    callback(true, null);
                                } else {
                                    //callback(false, 'Выберите другое время!');
                                }
                            },
                            icon: Ext.MessageBox.QUESTION,
                            msg: response_obj.Alert_Msg +'<br>Записать?',
                            title: 'Вопрос'
                        });
                    } else {
                        callback(true, null);
                    }
                    return true;
                }
                callback(false, 'Проверка времени записи перед добавлением не удалась!');
                return true;
            },
            url: '/?c=TimetableMedService&m=checkBeforeLock'
        });
        return true;
    },
    /**
     * Загрузка данных в грид услуг левой части формы
     */
    loadUslugaFrame:function(){
        this.uslugaFrame.removeAll();
        var base_form = this.filterPanel.getForm();
        if (!base_form.findField('UslugaComplex_id').PrescriptionType_Code) {
            sw.swMsg.alert('Сообщение', 'Выберите тип назначения!');
            return false;
        }
        var ucombo = base_form.findField('UslugaComplex_id');
        var lcombo = base_form.findField('Lpu_id');
        var mcombo = base_form.findField('MedService_id');
        
        var baseParams = ucombo.getStore().baseParams;
        baseParams.MedService_id = mcombo.getValue();
        
        baseParams.userLpuSection_id = this.userMedStaffFact.LpuSection_id;
        if (ucombo.getValue()) {
            baseParams.filterByUslugaComplex_id = ucombo.getValue();
			baseParams.filterByUslugaComplex_str = null;
        } else {
            baseParams.filterByUslugaComplex_str = ucombo.getRawValue()||null;
			baseParams.filterByUslugaComplex_id = null;
        }
        if (lcombo.getValue()) {
            baseParams.filterByLpu_id = lcombo.getValue();
        } else {
            baseParams.filterByLpu_str = lcombo.getRawValue()||null;
        }
        baseParams.start = 0;
        baseParams.limit = 100;
        this.uslugaFrame.loadData({
            globalFilters: baseParams
        });
        
        return true;
    },
    /**
     * Загрузка данных имеющихся назначений в правую часть формы
     * @param Evn_pid
     */
    loadDataViewStore:function(Evn_pid){
		var maskCfg = {
			msgCls:'hiddenMessageForLoadMask'
		};
		this.dataView.loadMask  = new Ext.LoadMask(Ext.get(this.dataView.id),maskCfg);
		this.dataView.loadMask.show();
        this.dataView.getStore().removeAll();
        this.dataView.resetNewIndex();
		var thas = this;
        this.dataView.getStore().load({
			callback: function(){
                thas.dataView.loadMask.hide();
			},
            params: {
                Evn_pid: Evn_pid,
                parentEvnClass_SysNick: this.filterPanel.getForm().getValues().parentEvnClass_SysNick
            }
        });
    },
    /**
     * Действия по нажатию кнопки "+" в левом гриде первой формы/по нажатию кнопки выбор
     * в форме выбора службы по известной услуге
     * В итоге в правой части в списке назначений появляется новое несохраненное направление-назначение
     */
    doInsert: function(key, ignoreCheckBeforeLock){
        var thas = this;
        var rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        if (!this._validate(rec)) {
            return false;
        }

        var PrescriptionType_id = thas.filterPanel.getForm().findField('UslugaComplex_id').PrescriptionType_Code;
        if ( PrescriptionType_id==11 && rec.get('isComposite') == 1 && !rec.compositionMenu) {
            this.loadCompositionMenu(function(){
                thas.doInsert(key);
            }, rec);
            return false;
        }
        if (!ignoreCheckBeforeLock) {
            this._checkBeforeLock(rec, function(result, msg) {
                if (result) {
                    thas.doInsert(key, true);
                } else {
                    sw.swMsg.alert('Сообщение', msg);
                }
            });
            return true;
        }

        var formParams = this.filterPanel.getForm().getValues();
        formParams.uslugaList = ''+rec.get('UslugaComplex_id');
        //log(rec.get('TimetableMedService_begTime'));
        if ( rec.get('TimetableMedService_id') > 0 && rec.get('TimetableMedService_begTime') ) {
            formParams.EvnPrescr_setDate = Date.parseDate(rec.get('TimetableMedService_begTime'), 'd.m.Y H:i').format('d.m.Y');
        }

        if (formParams.PrescriptionType_id == 6) {
            var win = getWnd('swPolkaEvnPrescrProcEditWindow');
            var params = {
                action: 'add',
                mode: 'nosave',
                parentEvnClass_SysNick: formParams.parentEvnClass_SysNick,
                callback: function(data) {
                    // log(data);
                    formParams.DurationTypeP_Nick = data.EvnPrescrProcData.DurationTypeP_Nick||"дн";
                    formParams.DurationType_id = data.EvnPrescrProcData.DurationType_id;
                    formParams.DurationTypeN_Nick = data.EvnPrescrProcData.DurationTypeN_Nick||"дн";
                    formParams.DurationType_recid = data.EvnPrescrProcData.DurationType_recid;
                    formParams.DurationTypeI_Nick = data.EvnPrescrProcData.DurationTypeI_Nick||"дн";
                    formParams.DurationType_intid = data.EvnPrescrProcData.DurationType_intid;
                    formParams.ContReception = data.EvnPrescrProcData.EvnCourseProc_ContReception;
                    formParams.CountInDay = data.EvnPrescrProcData.EvnCourseProc_MaxCountDay;
                    formParams.CourseDuration = data.EvnPrescrProcData.EvnCourseProc_Duration;
                    formParams.Interval = data.EvnPrescrProcData.EvnCourseProc_Interval;
                    formParams.EvnPrescr_setDate = data.EvnPrescrProcData.EvnCourseProc_setDate;
                    formParams.EvnPrescr_Descr = data.EvnPrescrProcData.EvnPrescrProc_Descr;
                    formParams.EvnPrescr_IsCito = data.EvnPrescrProcData.EvnPrescrProc_IsCito;
                    rec.set('EvnPrescr_IsCito',(data.EvnPrescrProcData.EvnPrescrProc_IsCito == 'on'));
                    rec.commit();

                    thas._createDirection(rec, formParams, function(direction){
                        thas.dataView.addRec(rec, formParams, direction);
                    });
                },
                onHide: function() {
                    //
                }
            };
            params.formParams = {
                EvnCourseProc_pid: formParams.Evn_pid
                ,EvnCourseProc_id: null
                ,Morbus_id: null
                ,MedPersonal_id: thas.userMedStaffFact.MedPersonal_id
                ,LpuSection_id: thas.userMedStaffFact.LpuSection_id
                ,EvnCourseProc_setDate: Date.parseDate((formParams.EvnPrescr_setDate || getGlobalOptions().date), 'd.m.Y')
                ,PersonEvn_id: formParams.PersonEvn_id
                ,Server_id: formParams.Server_id
                ,UslugaComplex_id: rec.get('UslugaComplex_id')
                ,EvnPrescrProc_IsCito: rec.get('EvnPrescr_IsCito')
            };
            if (win.isVisible()) {
                win.hide();
            }
            win.show(params);
        } else {
            this._createDirection(rec, formParams, function(direction){
                thas.dataView.addRec(rec, formParams, direction);
            });
        }
        return true;
    },
    /**
     * Открытие формы ввода данных направления
     */
    _createDirection: function(uslugaFrameRec, formParams, callback){
        var direction = {
            LpuUnitType_SysNick: 'parka'
            ,PrehospDirect_id: (uslugaFrameRec.get('Lpu_id') == getGlobalOptions().lpu_id)?1:2
            ,PrescriptionType_Code: formParams.PrescriptionType_id
            ,EvnDirection_pid: formParams.Evn_pid
            ,Evn_id: formParams.Evn_pid
            ,DirType_id: sw.Promed.Direction.defineDirTypeByPrescrType(formParams.PrescriptionType_id)
            ,Diag_id: formParams.Diag_id || null
            ,MedPersonal_id: this.userMedStaffFact.MedPersonal_id //ид медперсонала, который направляет
            ,Lpu_id: this.userMedStaffFact.Lpu_id
            ,LpuSection_id: this.userMedStaffFact.LpuSection_id
            ,From_MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id
            ,UslugaComplex_id: uslugaFrameRec.get('UslugaComplex_id')
            ,LpuSection_Name: uslugaFrameRec.get('LpuSection_Name')
            ,LpuSection_did: uslugaFrameRec.get('LpuSection_id')
            ,LpuSection_uid: uslugaFrameRec.get('LpuSection_id')
            ,LpuSectionProfile_id: uslugaFrameRec.get('LpuSectionProfile_id')
            ,EvnPrescr_id: null
            ,MedService_id: uslugaFrameRec.get('MedService_id')
            ,MedService_did: uslugaFrameRec.get('MedService_id')
            ,MedService_Nick: uslugaFrameRec.get('MedService_Nick')
            ,MedServiceType_SysNick: uslugaFrameRec.get('MedServiceType_SysNick')
            ,Lpu_did: uslugaFrameRec.get('Lpu_id')
            ,LpuUnit_did: uslugaFrameRec.get('LpuUnit_id')
            ,time: uslugaFrameRec.get('TimetableMedService_begTime')||null
            ,Server_id: formParams.Server_id
            ,Person_id: formParams.Person_id
            ,PersonEvn_id: formParams.PersonEvn_id
            ,MedStaffFact_id: this.userMedStaffFact.MedStaffFact_id //ид медперсонала, который направляет
            ,MedPersonal_did: null //ид медперсонала, куда направили
            ,timetable: 'TimetablePar'
            ,TimetableMedService_id: uslugaFrameRec.get('TimetableMedService_id')
            ,EvnQueue_id: null//
            ,QueueFailCause_id: null//
            ,EvnUsluga_id: null//Сохраненный заказ
            ,EvnDirection_id: null
        };
        // параметры для формы выписки эл.направления
        var form_params = direction;
        form_params.Person_Surname = this.Person_Surname;
        form_params.Person_Firname = this.Person_Firname;
        form_params.Person_Secname = this.Person_Secname;
        form_params.Person_Birthday = this.Person_Birthday;
        var params = {
            action: 'add',
            mode: 'nosave',
            callback: function(data){
                if (data && data.evnDirectionData) {
                    var o = data.evnDirectionData;
                    //принимаем только то, что могло измениться
                    direction.EvnDirection_Num = o.EvnDirection_Num;
                    direction.DirType_id = o.DirType_id;
                    direction.Diag_id = o.Diag_id;
                    direction.LpuSectionProfile_id = o.LpuSectionProfile_id;
                    direction.EvnDirection_Descr = o.EvnDirection_Descr;
                    direction.EvnDirection_setDate = o.EvnDirection_setDate;
                    direction.MedStaffFact_id = o.MedStaffFact_id;
                    direction.MedPersonal_id = o.MedPersonal_id;
                    direction.LpuSection_id = o.LpuSection_id;
                    direction.MedStaffFact_zid = o.MedStaffFact_zid;
                    direction.MedPersonal_zid = o.MedPersonal_zid;
                    direction.Lpu_did = o.Lpu_did;
                    direction.From_MedStaffFact_id = o.From_MedStaffFact_id;
                    callback(direction);
                }
            },
            params: form_params
        };

        if (!uslugaFrameRec.get('MedService_id') && !uslugaFrameRec.get('Lpu_id')) {
            // будем сохранять только назначение
            direction.Lpu_did = null;
            callback(direction);
            return true;
        }
        if (!uslugaFrameRec.get('MedService_id') && uslugaFrameRec.get('Lpu_id')) {
            sw.Promed.Direction.openDirectionEditWindow(params);
            return true;
        }
        if (getGlobalOptions().lpu_id == uslugaFrameRec.get('Lpu_id')) {
            //возвращаем параметры автоматического направления
            direction.EvnDirection_IsAuto = 2;
            direction.EvnDirection_setDate = getGlobalOptions().date;
            direction.EvnDirection_Num = '0';
            direction.MedPersonal_zid = '0';
            callback(direction);
        } else {
            //показать форму создания электронного направления без возможности отказаться от его создания
            //params.disableClose = true;
            // Разрешить отмену создания направления при создании назначения услуги другого ЛПУ.
            // В этом случае отменять направление и назначение.
            sw.Promed.Direction.openDirectionEditWindow(params);
        }
        return true;
    },
    /**
     * Отображение состава услуги для выбора
     */
    showComposition: function(key){
        var rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        if (rec.compositionMenu) {
            rec.compositionMenu.show(Ext.get('composition_'+ key),'tr');
        } else {
            this.loadCompositionMenu(function(menu){
                menu.show(Ext.get('composition_'+ key),'tr');
            }, rec);
        }
        return true;
    },
    /**
     * Открывает справочник ЛПУ для выбора.
     * Если выбрано ЛПУ, открывать создание направления,
     * если ЛПУ не указано - просто создавать назначение.
     */
    selectLpu: function(key){
        var rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        var win = getWnd('swLpuSelectWindow');
        if (win.isVisible()) {
            win.hide();
        }
        var thas = this;
        win.show({
            callback: function(sel_rec) {
                //открывать создание направления
                rec.set('Lpu_id', sel_rec.get('Lpu_id'));
                rec.set('Lpu_Nick', sel_rec.get('Lpu_Nick'));
                rec.commit();
                thas.doInsert(key);
            },
            onHide: function(hasSelect) {
                if (hasSelect == false) {
                    // просто создавать назначение
                    thas.doInsert(key);
                }
            }
        });
        return true;
    },
    /**
     * Вызывается форма выбора службы по известной услуге, отображающая все службы,
     * оказывающие данную услугу  (связь по ГОСТ)
     */
    showMedServiceAll: function(key){
        var thas = this;
        var rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
        var win = getWnd('swMedServiceSelectWindow');
        if (win.isVisible()) {
            win.hide();
        }
        win.show({
            UslugaComplex_id: rec.get('UslugaComplex_2011id'),
            userMedStaffFact: this.userMedStaffFact,
            callback: function(sel_rec) {
                var PrescriptionType_id = thas.filterPanel.getForm().findField('UslugaComplex_id').PrescriptionType_Code;
                // возможно была выбрана другая служба и услуга
                if (sel_rec.get('UslugaComplexMedService_id') != rec.get('UslugaComplexMedService_id')) {
                    rec.compositionMenu = null;
                    rec.set('isComposite', sel_rec.get('isComposite'));
                }
                rec.set('UslugaComplexMedService_id', sel_rec.get('UslugaComplexMedService_id'));
                rec.set('UslugaComplex_id', sel_rec.get('UslugaComplex_id'));
                rec.set('UslugaComplex_Code', sel_rec.get('UslugaComplex_Code'));
                rec.set('UslugaComplex_Name', sel_rec.get('UslugaComplex_Name'));
                rec.set('MedService_id', sel_rec.get('MedService_id'));
                rec.set('MedServiceType_id', sel_rec.get('MedServiceType_id'));
                rec.set('MedServiceType_SysNick', sel_rec.get('MedServiceType_SysNick'));
                rec.set('MedService_Nick', sel_rec.get('MedService_Nick'));
                rec.set('MedService_Name', sel_rec.get('MedService_Name'));
                rec.set('Lpu_id', sel_rec.get('Lpu_id'));
                rec.set('Lpu_Nick', sel_rec.get('Lpu_Nick'));
                rec.set('LpuBuilding_id', sel_rec.get('LpuBuilding_id'));
                rec.set('LpuBuilding_Name', sel_rec.get('LpuBuilding_Name'));
                rec.set('LpuUnit_id', sel_rec.get('LpuUnit_id'));
                rec.set('LpuUnit_Name', sel_rec.get('LpuUnit_Name'));
                rec.set('LpuUnitType_id', sel_rec.get('LpuUnitType_id'));
                rec.set('LpuUnitType_SysNick', sel_rec.get('LpuUnitType_SysNick'));
                rec.set('LpuSection_id', sel_rec.get('LpuSection_id'));
                rec.set('LpuSection_Name', sel_rec.get('LpuSection_Name'));
                rec.set('LpuSectionProfile_id', sel_rec.get('LpuSectionProfile_id'));
                rec.set('ttms_MedService_id', sel_rec.get('ttms_MedService_id'));
                rec.set('TimetableMedService_id', sel_rec.get('TimetableMedService_id'));
                rec.set('TimetableMedService_begTime', sel_rec.get('TimetableMedService_begTime'));
                if (PrescriptionType_id == 11) {
                    // возможно была выбрана другая лаборатория или другой пункт забора
                    if (sel_rec.get('ttms_MedService_id') == sel_rec.get('pzm_MedService_id')) {
                        rec.set('MedService_Nick', sel_rec.get('pzm_MedService_Nick'));
                        rec.set('MedService_Name', sel_rec.get('pzm_MedService_Name'));
                    }
                    rec.set('MedService_id', sel_rec.get('lab_MedService_id'));
                    rec.set('pzm_Lpu_id', sel_rec.get('pzm_Lpu_id'));
                    rec.set('pzm_MedService_id', sel_rec.get('pzm_MedService_id'));
                    rec.set('pzm_MedServiceType_id', sel_rec.get('pzm_MedServiceType_id'));
                    rec.set('pzm_MedServiceType_SysNick', sel_rec.get('pzm_MedServiceType_SysNick'));
                    rec.set('pzm_MedService_Nick', sel_rec.get('pzm_MedService_Nick'));
                    rec.set('pzm_MedService_Name', sel_rec.get('pzm_MedService_Name'));
                }
                rec.commit();
                thas.doInsert(key);
            }
        });
        return true;
    },
    /**
     * По гиперссылке открываем расписание, в котором можно выбрать другое время.
     * После закрытия формы выбора бирки, в графе "расписание" должно отобразиться новое время.
     */
    doApply: function(key){
        var thas = this,
            rec = this.uslugaFrame.getGrid().getStore().getById(key);
        if (!rec) {
            return false;
        }
		this._openTimetable(rec, function(ttms){
			if (!ttms.TimetableMedService_id) {
				rec.set('ttms_MedService_id', '');	//В очередь
			} else {
				rec.set('ttms_MedService_id', rec.get('MedService_id'));
			}
			rec.set('TimetableMedService_id', ttms.TimetableMedService_id);
			rec.set('TimetableMedService_begTime', ttms.TimetableMedService_begTime);
			rec.commit();
			thas.doInsert(key);
		});
        return true;
    },
    changeTime:function(key){
        var thas = this,
            record = this.dataView.getStore().getById(key),
            rec = this.dataView.assocUslugaFrameRec[key],
            direction = this.dataView.assocDirection[key];
        if (!record || !rec) {
            return false;
        }
        this._openTimetable(rec, function(ttms){
            if (!ttms || !ttms.TimetableMedService_id) {
                return false;
            }
            var old_time_id = rec.get('TimetableMedService_id');
            if (old_time_id > 0) {
                sw.Promed.Direction.unlockTime(thas, 'TimetableMedService', old_time_id, function(){
                    //delete thas.dataView.assocLockedTimetableMedServiceId[key];
                });
            }
            rec.set('TimetableMedService_id', ttms.TimetableMedService_id);
            rec.set('TimetableMedService_begTime', ttms.TimetableMedService_begTime);
            rec.commit();
            sw.Promed.Direction.lockTime(thas, 'TimetableMedService', ttms.TimetableMedService_id, function(time_id){
                thas.dataView.assocLockedTimetableMedServiceId[key] = time_id;
            });
            thas.dataView.updateRec(record,{RecDate: ttms.TimetableMedService_begTime});
            direction.time = ttms.TimetableMedService_begTime;
            direction.TimetableMedService_id = ttms.TimetableMedService_id;
            return true;
        });
        return true;
    },
    _openTimetable:function(rec, callback){
        var ms_data = {
            Lpu_id: rec.data.Lpu_id,
            MedService_id: rec.data.MedService_id,
            MedServiceType_id: rec.data.MedServiceType_id,
            MedService_Nick: rec.data.MedService_Nick,
            MedService_Name: rec.data.MedService_Name,
            MedServiceType_SysNick: rec.data.MedServiceType_SysNick
        };
        // если это назначение лабораторной диагностики
        // и есть пункт забора
        // и у пункта забора есть расписание
        if (rec.data.pzm_MedService_id && rec.data.pzm_MedService_id == rec.data.ttms_MedService_id) {
            //то будем записывать в пункт забора
            ms_data.Lpu_id = rec.data.pzm_Lpu_id;
            ms_data.MedService_id = rec.data.pzm_MedService_id;
            ms_data.MedServiceType_id = rec.data.pzm_MedServiceType_id;
            ms_data.MedServiceType_SysNick = rec.data.pzm_MedServiceType_SysNick;
            ms_data.MedService_Nick = rec.data.pzm_MedService_Nick;
            ms_data.MedService_Name = rec.data.pzm_MedService_Name;
        }
        sw.Promed.EvnPrescr.openTimetable({
            MedService: ms_data,
            callback: function(ttms){
                callback(ttms);
                getWnd('swTTMSScheduleRecordWindow').hide();
            }
            //,userClearTimeMS: function() {}
        });
    },
    /**
     * Создаем меню состава услуги
     * @param callback
     * @param rec
     * @return {Boolean}
     */
    loadCompositionMenu: function(callback, rec)
    {
        if (typeof callback != 'function') {
            return false;
        }
        if (!rec) {
            return false;
        }
        if (1 != rec.get('isComposite')) {
            return true;
        }
        if (rec.compositionMenu) {
            callback(rec.compositionMenu);
            return true;
        }
        var thas = this;
        this.getLoadMask(LOAD_WAIT).show();
        Ext.Ajax.request({
            params: {
                UslugaComplexMedService_pid: rec.get('UslugaComplexMedService_id'),
                UslugaComplex_pid: rec.get('UslugaComplex_id')
            },
            callback: function(options, success, response) {
                thas.getLoadMask().hide();
                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (Ext.isArray(response_obj) && response_obj.length > 0) {
                        rec.compositionMenu = new Ext.menu.Menu();
						rec.compositionMenu.addListener('show', function(m) { swSetMaxMenuHeight(m, 300); });
                        for (var i=0; i < response_obj.length; i++) {
                            rec.compositionMenu.add(new Ext.menu.CheckItem({
                                id: response_obj[i].UslugaComplex_id,
                                text: response_obj[i].UslugaComplex_Code+' '+response_obj[i].UslugaComplex_Name,
                                UslugaComplex_id: response_obj[i].UslugaComplex_id,
                                iconCls: "uslugacomplex-16",
                                rec: rec,
                                checked: true,
                                hideOnClick: false,
                                handler: function(item) {
                                    var cnt_checked = item.rec.get('compositionCntChecked');
                                    if (item.checked) {
                                        cnt_checked = cnt_checked - 1;
                                    } else {
                                        cnt_checked = cnt_checked + 1;
                                    }
                                    item.rec.set('compositionCntChecked', cnt_checked);
                                    item.rec.commit();
                                }
                            }));
                        }
                        rec.set('compositionCntChecked', response_obj.length);
                        rec.set('compositionCntAll', response_obj.length);
                        rec.commit();
                        callback(rec.compositionMenu);
                    }
                }
            },
            url: '/?c=MedService&m=loadCompositionMenu'
        });
        return true;
    },
	/**
	 * Загружает список услуг нужного типа
	 */
	_loadPrescriptionListByType: function(PrescriptionType_Code) {
		var base_form = this.filterPanel.getForm();
		if (jQuery.inArray(parseInt(PrescriptionType_Code), [6, 7, 11, 12, 13])<0) {
			return false;
		}
        this.PrescriptionType_Code = PrescriptionType_Code;
        base_form.findField('PrescriptionType_id').setValue(PrescriptionType_Code);
		var ucombo = base_form.findField('UslugaComplex_id');
		ucombo.clearValue();
		ucombo.getStore().removeAll();
		ucombo.setPrescriptionTypeCode(PrescriptionType_Code);
		// фильтрация осуществляется по совпадающим ГОСТ-11
		var uslugacategorylist = ['gost2011'];
		ucombo.setUslugaCategoryList(uslugacategorylist);
		this.uslugaFrame.removeAll();
		this.uslugaFrame.getGrid().getStore().removeAll();
		var colModel = this.uslugaFrame.getGrid().getColumnModel();
		colModel.setHidden(colModel.getIndexById('EPUIVF_composition'), PrescriptionType_Code!=11);
		this.loadUslugaFrame();
		return true;
	},
	/**
	 * Сворачивалка для групп в правом меню
	 */
	_toggleGroup: function(group_id) {
		var collapsedClass = 'collapsed',
		expandedClass = 'expanded';
		if (($('#EvnPrescrUslugaInputWindow_PrescriptionGroup_'+group_id)).hasClass(expandedClass)) {
			$('#EvnPrescrUslugaInputWindow_PrescriptionGroup_'+group_id).removeClass(expandedClass).addClass(collapsedClass);
		} else {
			$('#EvnPrescrUslugaInputWindow_PrescriptionGroup_'+group_id).removeClass(collapsedClass).addClass(expandedClass);
		}
	},
	_getActionMenu: function(e,el,EvnPrescr_id,EvnPrescr_IsDir,EvnPrescr_key,EvnDirection_id) {
		var actions = [];
		$(el).addClass('click');
		if (this.dataView.tpl.isHasDirection(EvnPrescr_IsDir)) {
			if (this.dataView.tpl.isHasEvnDirection(EvnDirection_id)) {
				actions.push({
					name:'action_viewDir',
					text:'Просмотр направления',
					tooltip: 'Открыть направление для просмотра',
					handler: function() {
						Ext.getCmp('EvnPrescrUslugaInputWindow').dataView.viewDirection(EvnPrescr_key);
					}
				});
			} else {
				actions.push({
					name:'action_createDir',
					text:'Создать направление',
					tooltip: 'Создание направления на услугу',
                    disabled: true,
                    hidden: true,//направление можно создать в форме swEvnPrescrUslugaEditWindow
					handler: function() {
						Ext.getCmp('EvnPrescrUslugaInputWindow').dataView.viewDirection(EvnPrescr_key);
					}
				});
			}
		}
		if (this.dataView.tpl.isHasEvnDirection(EvnDirection_id)) {
			actions.push({
				name:'action_printDir',
				text:'Печать направления',
				tooltip: 'Открыть печатную форму в новом окне',
				handler: function() {
					sw.Promed.Direction.print({
						EvnDirection_id: EvnDirection_id
					});
				}
			});
		}
		
		if (this.dataView.tpl.isAllowEdit(EvnDirection_id)) {
			actions.push({
				name:'action_editPrescr',
				text:'Редактировать',
				tooltip: 'Редактировать услугу',
                disabled: true,
                hidden: true,//нужно переделывать с использованием формы swEvnPrescrUslugaEditWindow
				handler: function() {
					Ext.getCmp('EvnPrescrUslugaInputWindow').dataView.editPrescr(EvnPrescr_key);
				}
			});
		}
		if (this.dataView.tpl.isAllowDelete(EvnDirection_id)) {
				actions.push({
				name:'action_delPrescr',
				text:'Удалить',
				tooltip: 'Удалить услугу',
				handler: function() {
					Ext.getCmp('EvnPrescrUslugaInputWindow').dataView.delPrescr(EvnPrescr_key);
				}
			});
		}
			
		
		if ((actions == {})||(!e||!e.clientX||!e.clientY)) {
			return false;
		}
		var PrescrPlanActions ={};
		for (var i=0; i<actions.length; i++) {
				PrescrPlanActions[actions[i]['name']] = new Ext.Action( {
				id: 'id_'+actions[i]['name'],
				text: actions[i]['text'],
				disabled: actions[i]['disabled'] || false,
				hidden: actions[i]['hidden'] || false,
				tooltip: actions[i]['tooltip'],
				iconCls : actions[i]['iconCls'] || 'x-btn-text',
				icon: actions[i]['icon'] || null,
				menu: actions[i]['menu'] || null,
				scope: this,
				handler: actions[i]['handler']
			});
		}

		this.PrescrPlanActionMenu = new Ext.menu.Menu();
		for (key in PrescrPlanActions) {
			if (PrescrPlanActions.hasOwnProperty(key)) {
				this.PrescrPlanActionMenu.add(PrescrPlanActions[key]);
			}
		}

		this.PrescrPlanActionMenu.on('beforehide',function(){
			$(el).removeClass('click');
		});
		this.PrescrPlanActionMenu.showAt([e.clientX, e.clientY]);
	},
    /**
     * Декларируем компоненты формы и создаем форму
     */
    initComponent: function() {
        var thas = this;
        this.filterPanel = new Ext.form.FormPanel({
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: false,
            height: 90,
            id: 'EvnPrescrUslugaInputForm',
            labelAlign: 'right',
            buttonAlign: 'left',
            labelWidth: 100,
            region: 'north',
            items: [{
                border: false,
                layout: 'form',
                items: [{
                    name: 'Evn_pid',
                    xtype: 'hidden'
                }, {
                    name: 'parentEvnClass_SysNick',
                    xtype: 'hidden'
                }, {
                    name: 'PrescriptionType_id',
                    xtype: 'hidden'
                }, {
                    name: 'PersonEvn_id',
                    xtype: 'hidden'
                }, {
                    name: 'Person_id',
                    xtype: 'hidden'
                }, {
                    name: 'Server_id',
                    xtype: 'hidden'
                }, {
                    name: 'EvnPrescr_setDate',
                    xtype: 'hidden'
                }, {
                    name: 'Diag_id',
                    xtype: 'hidden'
                }, {
					layout:'column',
					border:false,
					items:[{
                        layout:'form',
                        border:false,
                        items:[
//							{
//                            autoLoad: false,
//                            comboSubject: 'PrescriptionType',
//                            fieldLabel: 'Тип назначения',
//                            hiddenName: 'PrescriptionType_id',
//                            //editable: false,
//                            //codeField: '',
//                            tpl: new Ext.XTemplate(
//                                '<tpl for="."><div class="x-combo-list-item">',
//                                '{PrescriptionType_Name}',
//                                '</div></tpl>'
//                            ),
//                            listeners: {
//                                select: function(combo, record) {
//                                    var base_form = thas.filterPanel.getForm();
//                                    if ( record && record.get('PrescriptionType_Code') ) {
//                                        var ucombo = base_form.findField('UslugaComplex_id');
//                                        ucombo.clearValue();
//                                        ucombo.getStore().removeAll();
//                                        ucombo.setPrescriptionTypeCode(record.get('PrescriptionType_Code'));
//                                        // фильтрация осуществляется по совпадающим ГОСТ-11
//                                        var uslugacategorylist = ['gost2011'];
//                                        ucombo.setUslugaCategoryList(uslugacategorylist);
//                                        thas.uslugaFrame.removeAll();
//                                        thas.uslugaFrame.getGrid().getStore().removeAll();
//                                        var colModel = thas.uslugaFrame.getGrid().getColumnModel();
//                                        colModel.setHidden(colModel.getIndexById('EPUIVF_composition'), record.get('PrescriptionType_Code')!=11);
//                                    }
//                                },
//                                render: function(combo) {
//                                    combo.getStore().load({
//                                        params: {
//                                            where: 'where PrescriptionType_id in (6, 7, 11, 12, 13)'
//                                        }
//                                    });
//                                }
//                            },
//                            typeCode: 'int',
//                            width: 350,
//                            xtype: 'swcommonsprcombo'
//                        }, 
						{
                            allowBlank: true,
                            fieldLabel: 'Услуга',
                            hiddenName: 'UslugaComplex_id',
                            listWidth: 600,
                            width: 350,
                            listeners: {
                                change: function() {
                                    thas.uslugaFrame.removeAll();
                                    thas.uslugaFrame.getGrid().getStore().removeAll();
                                },
								'keydown': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{	
										thas.loadUslugaFrame();
									}
								}
                            },
                            xtype: 'swuslugacomplexevnprescrcombo'
                        }, {
                            allowBlank: true,
                            fieldLabel: 'МО',
                            hiddenName: 'Lpu_id',
                            listWidth: 400,
                            width: 350,
                            listeners: {
                                change: function() {
                                	var base_form =  thas.filterPanel.getForm(),
                                		medService = base_form.findField('MedService_id'),
                                		lpu_id = base_form.findField('Lpu_id').getValue();
                                		prescriptionType_id = base_form.findField('PrescriptionType_id').getValue();
                                			pti = prescriptionType_id;
                                			
                                	if (prescriptionType_id === '11' || prescriptionType_id === '12'){
                                		var mst;
                                		switch (prescriptionType_id) {
                                			case '11': mst = 6; break;
                                			case '12': mst = 8; break;
                                		}
                                		medService.getStore().load({params: {Lpu_id: lpu_id, MedServiceType_id: mst}});
                                	}
 
                                	thas.uslugaFrame.removeAll();
                                    thas.uslugaFrame.getGrid().getStore().removeAll();
                                },
								'keydown': function (inp, e)
								{
									if (e.getKey() == Ext.EventObject.ENTER)
									{	
										thas.loadUslugaFrame();
									}
								}
                            },
                            xtype: 'swlpucombo'
                        }, { // ipavelpetrov
                            allowBlank: true,
                            fieldLabel: 'Тип Услуги',
                            hiddenName: 'MedService_id',
                           //hideParent: true,
                            name: 'MedService_id',
                            displayField: 'MedService_Name',
                            valueField: 'MedService_id',   
                            queryMode: 'local',
                            mode: 'local',                            
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{ name: 'MedService_id', type: 'int' },
									{ name: 'MedService_Name', type: 'string' }
								],
								key: 'MedService_id',
								sortInfo: {
									field: 'MedService_Name'
								},
								url: '/?c=MedService&m=getLpuMedServiceTypes'
							}),
							triggerAction: 'all',
                            listWidth: 400,
                            width: 350,
                            xtype: 'combo'
                        }]
                    },{
                        layout:'form',
                        border:false,
                        style: 'padding-left: 5px;',
                        items:[{
                            xtype: 'button',
                            text: BTN_FRMSEARCH,
                            iconCls: 'search16',
                            style: 'padding-bottom: 3px',
                            handler: function() {
                                thas.loadUslugaFrame();
                            }
                        },{
                            xtype: 'button',
                            text: BTN_FRMRESET,
                            iconCls: 'resetsearch16',
                            handler: function() {
                                var base_form = thas.filterPanel.getForm();
                                base_form.reset();
                                base_form.setValues(thas.formParams);
                                thas.loadUslugaFrame();
                            }
                        }]
                    }]
				}]

            }],
            keys: [{
                fn: function() {
                    thas.loadUslugaFrame();
                },
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }]
        });
        var tplEvnPrescrGroupTitle = '<dt class="expanded" id="EvnPrescrUslugaInputWindow_PrescriptionGroup_{PrescriptionType_id}">'+
            '<span>'+
                '<em onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._toggleGroup({PrescriptionType_id});"></em>'+
                '<span onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._toggleGroup({PrescriptionType_id});">{EvnPrescrGroup_Title} <span class="count">' +
                '<tpl if="EvnCourse_Count">({EvnCourse_Count})</tpl>' +
                '<tpl if="!EvnCourse_Count">({cntInPrescriptionTypeGroup})</tpl>' +
                '</span></span>' +
                '<tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>' +
                '<tpl if="PrescriptionType_id==6||PrescriptionType_id==7||PrescriptionType_id==11||PrescriptionType_id==12||PrescriptionType_id==13"><a href="#" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._loadPrescriptionListByType({PrescriptionType_id});" ><img src="/img/EvnPrescrPlan/add.png" title="Добавить" /> Добавить</a></tpl>'+
            '</span>'+
        '</dt>';
        var tplEvnCourseTitle = '<li class="collapsed" id="EvnPrescrUslugaInputWindow_PrescriptionGroup_{EvnCourse_id}">' +
            '<span><em onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._toggleGroup({EvnCourse_id});"></em>'+
            '<span onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._toggleGroup({EvnCourse_id});">{EvnPrescrGroup_Title} ' +
            '</span></span>' +
            '<ul> <li>{EvnCourse_begDate} - {EvnCourse_endDate}. Общее кол-во: {EvnPrescrGroup_Count}. Кратность: ' +
            '<tpl if="MinCountInDay == MaxCountInDay">{MinCountInDay}</tpl>' +
            '<tpl if="MinCountInDay != MaxCountInDay">{MinCountInDay} – {MaxCountInDay}</tpl>' +
            ' в день.</li>'+
            '<tpl if="MedServices"><li><span style="font-weight: bold">Место выполнения:</span> {MedServices}.</li></tpl>'+
            '</ul></li>'+
        '</li>';
        /**
         * Представление списка назначений
         * Содержит как сохраненные, так и не сохраненные назначения.
         * Не сохраненные назначения можно или добавить в БД или удалить из store или отредактировать без сохранения
         * Сохраненные назначения можно или отменить или удалить из БД или отредактировать с сохранением
         * @type {Ext.DataView}
         */
        this.dataView = new Ext.DataView({
            id:"EvnPrescrUslugaDataView",
            store:new Ext.data.Store({
                autoLoad:false,
                reader:new Ext.data.JsonReader({
                    id:'EvnPrescr_key'
                }, [
                    // EvnPrescr_id нельзя использовать как ключ,
                    // т.к. в этом хранилище будут и несохраненные записи без EvnPrescr_id
                    {name: 'EvnPrescr_key', mapping: 'EvnPrescr_key',key:true},
                    {name: 'RecordStatus', mapping: 'RecordStatus'},// 'inserted', 'updated', 'new', ''
                    {name: 'EvnPrescr_id', mapping: 'EvnPrescr_id'},
                    {name: 'EvnPrescr_IsExec', mapping: 'EvnPrescr_IsExec'},
                    {name: 'EvnPrescr_IsDir', mapping: 'EvnPrescr_IsDir'},
                    {name: 'PrescriptionStatusType_id', mapping: 'PrescriptionStatusType_id'},
                    {name: 'EvnDirection_id', mapping: 'EvnDirection_id'},
                    {name: 'timetable', mapping: 'timetable'},
                    {name: 'timetable_id', mapping: 'timetable_id'},

                    // ниже данные для сохранения
					{name: 'EvnPrescr_uslugaList' ,mapping:'EvnPrescr_uslugaList'},
                    {name: 'PrescriptionType_id', mapping: 'PrescriptionType_id'},
                    {name: 'EvnPrescr_pid', mapping: 'EvnPrescr_pid'},
                    {name: 'PersonEvn_id', mapping: 'PersonEvn_id'},
                    {name: 'Server_id', mapping: 'Server_id'},
                    {name: 'EvnPrescr_setDate', mapping: 'EvnPrescr_setDate'},
                    {name: 'EvnPrescr_IsCito', mapping: 'EvnPrescr_IsCito'},
                    {name: 'EvnPrescr_Descr', mapping: 'EvnPrescr_Descr'},
                    {name: 'UslugaId_List', mapping:'UslugaId_List'},
                    {name: 'DurationType_id', mapping: 'DurationType_id'},
                    {name: 'DurationType_recid', mapping: 'DurationType_recid'},
                    {name: 'DurationType_intid', mapping: 'DurationType_intid'},

                    // ниже данные для отображения
                    {name: 'Usluga_List', mapping:'Usluga_List'},
                    {name: 'CountInDay', mapping: 'CountInDay'},
                    {name: 'CourseDuration', mapping: 'CourseDuration'},
                    {name: 'ContReception', mapping: 'ContReception'},
                    {name: 'Interval', mapping: 'Interval'},
                    {name: 'DurationTypeP_Nick', mapping: 'DurationTypeP_Nick'},
                    {name: 'DurationTypeN_Nick', mapping: 'DurationTypeN_Nick'},
                    {name: 'DurationTypeI_Nick', mapping: 'DurationTypeI_Nick'},
                    {name: 'IsCito_Name', mapping: 'IsCito_Name'},
                    {name: 'IsCito_Code', mapping: 'IsCito_Code'},
                    {name: 'EvnDirection_Num', mapping: 'EvnDirection_Num'},
                    {name: 'RecTo', mapping: 'RecTo'},
                    {name: 'RecDate', mapping: 'RecDate'},
                    {name: 'EvnPrescrGroup_Title', mapping: 'EvnPrescrGroup_Title'},
                    {name: 'EvnPrescrGroup_Count', mapping: 'EvnPrescrGroup_Count'},
                    {name: 'EvnCourse_Count', mapping: 'EvnCourse_Count'},
                    {name: 'cntInPrescriptionTypeGroup', mapping: 'cntInPrescriptionTypeGroup'},
					
					/*1*/
					{name: 'PrescriptionRegimeType_Name', mapping: 'PrescriptionRegimeType_Name'},
					/*2*/
					{name: 'PrescriptionDietType_Name', mapping: 'PrescriptionDietType_Name'},
					/*5*/
					//----Режим приёма
                    {name: 'EvnCourse_id', mapping: 'EvnCourse_id'},
                    {name: 'EvnCourse_begDate', mapping: 'EvnCourse_begDate'},
                    {name: 'EvnCourse_endDate', mapping: 'EvnCourse_endDate'},
                    {name: 'MinCountInDay', mapping: 'MinCountInDay'},
                    {name: 'MaxCountInDay', mapping: 'MaxCountInDay'},
                    {name: 'MedServices', mapping: 'MedServices'},


                    {name: 'Drug_Info', mapping: 'Drug_Info'},
                    {name: 'EvnPrescrTreatDrug_KolvoEd', mapping: 'EvnPrescrTreatDrug_KolvoEd'},
                    {name: 'DrugForm_Nick', mapping: 'DrugForm_Nick'},
                    {name: 'EvnPrescrTreatDrug_Kolvo', mapping: 'EvnPrescrTreatDrug_Kolvo'},
                    {name: 'Okei_NationSymbol', mapping: 'Okei_NationSymbol'},
					//----Доза
					{name: 'EvnPrescrTreatDrug_DoseDay', mapping: 'EvnPrescrTreatDrug_DoseDay'},
					{name: 'EvnPrescrTreatDrug_DoseCource', mapping: 'EvnPrescrTreatDrug_DoseCource'},
					//--етод введения
					{name: 'PrescriptionIntroType_Name', mapping: 'PrescriptionIntroType_Name'},
					//-Исполнение
					{name: 'PerformanceType_Name', mapping: 'PerformanceType_Name'},
					/*10*/
					{name: 'Params', mapping: 'Params'},
					{name: 'EvnPrescr_setTime', mapping: 'EvnPrescr_setTime'},
					/*6*/
					{name: 'MedicationRegime', mapping: 'MedicationRegime'}

                ]),
                url:'/?c=EvnPrescr&m=loadEvnPrescrUslugaDataView'
            }),
            itemSelector: 'ul',
            autoHeight: true,
            tpl : new Ext.XTemplate(
				'<div class="data-table">',
					'<div class="EvnPrescrUslugaInputWindowPrescrTable">',
						'<dl>',
							//1 Title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==1)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//1 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==1)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{PrescriptionRegimeType_Name}</strong>',
												'<ul>',
													'<li><strong>Период:</strong> {EvnPrescr_setDate}</li>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//2 Title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==2)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//2 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==2)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{PrescriptionDietType_Name}</strong>',
												'<ul>',
													'<li><strong>Период:</strong> {EvnPrescr_setDate}</li>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//5 Title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==5)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//5 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==5)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{Drug_Info}</strong><tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>',
												'<ul>',
													'<tpl if="EvnPrescr_setDate">',
														'<li><strong>Период:</strong> c {EvnPrescr_setDate}</li>',	
													'</tpl>',

													'<tpl if="((EvnPrescrTreatDrug_KolvoEd)||(EvnPrescrTreatDrug_Kolvo)||(Okei_NationSymbol)||(CountInDay)||(Interval)||((CourseDuration)&&(CourseDuration!=ContReception) ) )">',
														'<li><strong>Режим приёма:</strong> ',
															'<tpl if="EvnPrescrTreatDrug_KolvoEd">',
																'По {EvnPrescrTreatDrug_KolvoEd}',
																'<tpl if="!DrugForm_Nick">',
																	'ед.дозировки',
																'</tpl>',
																'<tpl if="DrugForm_Nick">',
																	'{DrugForm_Nick}',
																'</tpl>',
																'.',
															'</tpl>',
															'<tpl if="EvnPrescrTreatDrug_Kolvo&&!EvnPrescrTreatDrug_KolvoEd">',
																'{EvnPrescrTreatDrug_Kolvo}',
															'</tpl>',
															'<tpl if="Okei_NationSymbol&&!EvnPrescrTreatDrug_KolvoEd">',
																'{Okei_NationSymbol}',
															'</tpl>',
															'<tpl if="CountInDay">',
																'{CountInDay} ',
																'<tpl if="CountInDay==2||CountInDay==3||CountInDay==4">раза</tpl>',
																'<tpl if="CountInDay!=2&&CountInDay!=3&&CountInDay!=4">раз</tpl>',
																' в сутки',
															'</tpl>',
															'<tpl if="ContReception">',
																', принимать {ContReception} {DurationTypeN_Nick}',
															'</tpl>',
															'<tpl if="Interval">',
																', перерыв {Interval} {DurationTypeI_Nick}',
															'</tpl>',
															'<tpl if="CourseDuration && (CourseDuration != ContReception)">',
																', в течение {CourseDuration} {DurationTypeP_Nick}',
															'</tpl>',
														'</li>',	
													'</tpl>',
													'<tpl if="EvnPrescrTreatDrug_Kolvo&&Okei_NationSymbol&&EvnPrescrTreatDrug_DoseDay&&EvnPrescrTreatDrug_DoseCource">',
														'<li><strong>Доза:</strong> ',
														'разовая – {EvnPrescrTreatDrug_Kolvo} {Okei_NationSymbol}; дневная — {EvnPrescrTreatDrug_DoseDay};  курсовая – {EvnPrescrTreatDrug_DoseCource}.',
														'</li>',
													'</tpl>',
													'<tpl if="PrescriptionIntroType_Name">',
														'<li><strong>Метод введения:</strong> ',
														'{PrescriptionIntroType_Name}',
														'</li>',
													'</tpl>',
													'<tpl if="PerformanceType_Name">',
														'<li><strong>Исполнение:</strong> ',
														'{PerformanceType_Name}',
														'</li>',
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
	//												'<tpl if="">',
	//													'',
	//												'</tpl>',										
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//6 title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==6)&&EvnPrescrGroup_Title&&!EvnCourse_id)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//6 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
                                        '<tpl if="((PrescriptionType_id==6)&&EvnPrescrGroup_Title&&EvnCourse_id)">',
                                        tplEvnCourseTitle,
                                        '</tpl>',
										'<tpl if="((PrescriptionType_id==6)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{Usluga_List}</strong><tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>',
												'<ul>',
													'<tpl if="EvnPrescr_setDate">',
														'<li><strong>Плановая дата:</strong> {EvnPrescr_setDate}</li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==2">',
														'<li><strong><span class="link" title="Просмотр направления">Запись</span>:</strong> {RecTo} {RecDate} <tpl if="EvnDirection_Num && (EvnDirection_Num != 0)">{EvnDirection_Num}</tpl></li>',
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//10 Title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==10)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
	//						//10 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==10)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{Params}</strong>',
												'<ul>',
													'<li><strong>Период:</strong> {EvnPrescr_setDate}</li>',
													'<tpl if="EvnPrescr_setTime">',
														'<li><strong>Время наблюдения:</strong> {EvnPrescr_setTime}</li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//7 title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==7)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//7 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==7)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span><strong>{Usluga_List}</strong><tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>',
												'<ul>',
													'<tpl if="EvnPrescr_setDate">',
														'<li><strong>Плановая дата:</strong> {EvnPrescr_setDate}</li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==2">',
														'<li><strong><span class="link" title="Просмотр направления">Запись</span>:</strong> {RecTo} {RecDate} <tpl if="EvnDirection_Num && (EvnDirection_Num != 0)">{EvnDirection_Num}</tpl></li>',
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==1">',
														'<li><strong><span class="link" title="Требуется запись">Запись</span>:</strong> <span class="cito">Требуется запись</span></li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//11 title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==11)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//11 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==11)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{Usluga_List}</strong><tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>',
												'<ul>',
													'<tpl if="EvnPrescr_setDate">',
														'<li><strong>Плановая дата:</strong> {EvnPrescr_setDate}</li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==2">',
														'<li><strong><span class="link" title="Просмотр направления">Запись</span>:</strong> {RecTo} {RecDate} <tpl if="EvnDirection_Num && (EvnDirection_Num != 0)">{EvnDirection_Num}</tpl></li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==1">',
														'<li><strong><span class="link" title="Требуется запись">Запись</span>:</strong> <span class="cito">Требуется запись</span></li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//12 title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==12)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//12 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==12)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{Usluga_List}</strong><tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>',
												'<ul>',
													'<tpl if="EvnPrescr_setDate">',
														'<li><strong>Плановая дата:</strong> {EvnPrescr_setDate}</li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==2">',
														'<li><strong><span class="link" title="Просмотр направления">Запись</span>:</strong> {RecTo} {RecDate} <tpl if="EvnDirection_Num && (EvnDirection_Num != 0)">{EvnDirection_Num}</tpl></li>',
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==1">',
														'<li><strong><span class="link" title="Требуется запись">Запись</span>:</strong> <span class="cito">Требуется запись</span></li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//13 title
							'<tpl for=".">',
								'<tpl if="((PrescriptionType_id==13)&&EvnPrescrGroup_Title)">',
									tplEvnPrescrGroupTitle,
								'</tpl>',
							'</tpl>',
							//13 content
							'<dd>',
								'<ul>',
									'<tpl for=".">',
										'<tpl if="((PrescriptionType_id==13)&&!EvnPrescrGroup_Title)">',
											'<li><span class="button" onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\')._getActionMenu(event,this,\'{EvnPrescr_id}\',\'{EvnPrescr_IsDir}\',\'{EvnPrescr_key}\',\'EvnDirection_id\')"></span></span><strong>{Usluga_List}</strong><tpl if="IsCito_Code == 1"> <span class="cito">Cito!</span></tpl>',
												'<ul>',
													'<tpl if="EvnPrescr_setDate">',
														'<li><strong>Плановая дата:</strong> {EvnPrescr_setDate}</li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==2">',
														'<li><strong><span class="link" title="Просмотр направления">Запись</span>:</strong> {RecTo} {RecDate} <tpl if="EvnDirection_Num && (EvnDirection_Num != 0)">{EvnDirection_Num}</tpl></li>',
													'</tpl>',
													'<tpl if="EvnPrescr_IsDir==1">',
														'<li><strong><span class="link" title="Требуется запись">Запись</span>:</strong> <span class="cito">Требуется запись</span></li>',	
													'</tpl>',
													'<tpl if="EvnPrescr_Descr">',
														'<li><strong>Комментарий:</strong> {EvnPrescr_Descr}</li>',	
													'</tpl>',
												'</ul>',
											'</li>',					
										'</tpl>',
									'</tpl>',
								'</ul>',								
							'</dd>',
							//
						'</dl>',
					'</div>',
				'</div>',
				{
                    hasValue: function(value){
                        return !Ext.isEmpty(value);
                    },
                    isSaved: function(status){
                        return false;//(!status||status=='inserted');
                    },
                    isAllowDelete: function(EvnDirection_id){
                        return !(EvnDirection_id > 0);
                    },
                    isAllowEdit: function(EvnDirection_id){
                        return !(EvnDirection_id > 0);
                    },
                    isHasEvnDirectionNum: function(num){
                        return (num && num > 0);
                    },
                    isHasEvnDirection: function(EvnDirection_id){
                        return (EvnDirection_id > 0);
                    },
                    isHasDirection: function(EvnPrescr_IsDir){
                        return (EvnPrescr_IsDir == 2);
                    }
                }
            ),
			emptyText : '<div class="EvnPrescrUslugaInputWindowPrescrTable"><dl><dt class="collapsed"><span><em></em><span>Режим</span></span></dt><dt class="collapsed"><span><em></em><span>Диета</span></span></dt><dt class="collapsed"><span><em></em><span>Лекарственное лечение</span></span></dt><dt class="collapsed"><span><em></em><span>Манипуляции и процедуры</span></span></dt><dt class="collapsed"><span><em></em><span>Наблюдение</span></span></dt><dt class="collapsed"><span><em></em><span>Оперативное лечение</span></span></dt><dt class="collapsed"><span><em></em><span>Лабораторная диагностика</span></span></dt><dt id="" class="collapsed"><span><em></em><span>Функциональная диагностика</span></span></dt><dt class="collapsed"><span><em></em><span>Консультационная услуга</span></span></dt></dl></div>',
			editPrescr:function(key){
				var winName = '';
				var spec_name = "";
				var callback;
				var params={};
				var store =  this.getStore();
				var index = store.findBy(function(record){
					return record.get('EvnPrescr_key') == key;
				});
				var record = store.getAt(index);
                log('@todo use new edit form');
                return false;
				switch (parseInt(record.get('PrescriptionType_id'))) {
					case 6:
						spec_name="Proc";
						winName='swPolkaEvnPrescrProcEditWindow';
						if(record.get('EvnPrescr_id')!=null){
						params={
							EvnPrescrProc_id:record.get('EvnPrescr_id'),
							EvnPrescrProc_pid:record.get('EvnPrescr_pid'),
							EvnPrescrProc_setDate:record.get('EvnPrescr_setDate')
						};
                        }else{
							params={
								EvnPrescrProc_id:record.get('EvnPrescr_id'),
								EvnPrescrProc_pid:record.get('EvnPrescr_pid'),
								EvnPrescrProc_setDate:record.get('EvnPrescr_setDate'),
								EvnPrescrProc_uslugaList:record.get('UslugaId_List'),
								EvnPrescrProc_IsCito:(record.get('IsCito_Code')==1)?'on':'',
								EvnCourseProc_MaxCountDay:record.get('CountInDay'),
								EvnCourseProc_Duration:record.get('CourseDuration'),
								EvnCourseProc_ContReception:record.get('ContReception'),
								EvnPrescrProc_Descr:record.get('EvnPrescr_Descr'),
								EvnCourseProc_Interval:record.get('Interval'),
								mode:'nosave'
							};
						}
						callback = function(rec){
							var data={
								EvnPrescr_setDate:rec.EvnPrescrProc_setDate,
								UslugaId_List:rec.EvnPrescrProc_uslugaList,
								Usluga_List:rec.Usluga_List,
								IsCito_Code:(rec.EvnPrescrProc_IsCito=='on')?1:0,
								EvnPrescr_Descr:rec.EvnPrescrProc_Descr,
								CountInDay:rec.EvnCourseProc_MaxCountDay,
								CourseDuration:rec.EvnCourseProc_Duration,
								ContReception:rec.EvnCourseProc_ContReception,
								Interval:rec.EvnCourseProc_Interval,
								DurationTypeP_Nick:rec.DurationTypeP_Nick,
								DurationTypeI_Nick:rec.DurationTypeI_Nick,
								DurationTypeN_Nick:rec.DurationTypeN_Nick
							};
							thas.dataView.updateRec(record,data);
                            thas.hasChange = true;
						};
						break;
					case 7:
						spec_name="Oper";
						winName='swPolkaEvnPrescrOperEditWindow';
						if(record.get('EvnPrescr_id')!=null){
							params={
								EvnPrescrOper_id:record.get('EvnPrescr_id'),
								EvnPrescrOper_pid:record.get('EvnPrescr_pid'),
								EvnPrescrOper_setDate:record.get('EvnPrescr_setDate')
							};
						}else{
							params={
								EvnPrescrOper_id:record.get('EvnPrescr_id'),
								EvnPrescrOper_pid:record.get('EvnPrescr_pid'),
								EvnPrescrOper_setDate:record.get('EvnPrescr_setDate'),
								EvnPrescrOper_uslugaList:record.get('UslugaId_List'),
								EvnPrescrOper_Descr:record.get('EvnPrescr_Descr'),
								EvnPrescrOper_IsCito:(record.get('IsCito_Code')==1)?'on':'',
								mode:'nosave'
							};
						}
						callback = function(rec){
							var data={
								EvnPrescr_setDate:rec.EvnPrescrOper_setDate,
								UslugaId_List:rec.EvnPrescrOper_uslugaList,
								Usluga_List:rec.Usluga_List,
								IsCito_Code:(rec.EvnPrescrOper_IsCito=='on')?1:0,
								EvnPrescr_Descr:rec.EvnPrescrOper_Descr
							};
							thas.dataView.updateRec(record,data);
                            thas.hasChange = true;
						};
						break;
					case 11:
						spec_name="LabDiag";
						winName='swPolkaEvnPrescrLabDiagEditWindow';
						if(record.get('EvnPrescr_id')!=null){
							params={
								EvnPrescrLabDiag_id:record.get('EvnPrescr_id'),
								EvnPrescrLabDiag_pid:record.get('EvnPrescr_pid'),
								EvnPrescrLabDiag_setDate:record.get('EvnPrescr_setDate')
							};
						}else{
							params={
								EvnPrescrLabDiag_id:record.get('EvnPrescr_id'),
								EvnPrescrLabDiag_pid:record.get('EvnPrescr_pid'),
								EvnPrescrLabDiag_setDate:record.get('EvnPrescr_setDate'),
								UslugaComplex_id:record.get('UslugaId_List'),
								EvnPrescrLabDiag_Descr:record.get('EvnPrescr_Descr'),
								EvnPrescrLabDiag_uslugaList:record.get('EvnPrescr_uslugaList'),
								EvnPrescrLabDiag_IsCito:(record.get('IsCito_Code')==1)?'on':'',
								mode:'nosave'
							};
						}
						callback = function(rec){
							var data={
								EvnPrescr_setDate:rec.EvnPrescrLabDiag_setDate,
								EvnPrescr_uslugaList:rec.EvnPrescrLabDiag_uslugaList,
								UslugaId_List:rec.UslugaComplex_id,
								Usluga_List:rec.Usluga_List,
								IsCito_Code:(rec.EvnPrescrLabDiag_IsCito=='on')?1:0,
								EvnPrescr_Descr:rec.EvnPrescrLabDiag_Descr
							};
							thas.dataView.updateRec(record,data);
                            thas.hasChange = true;
						};
						break;
					case 12:
						spec_name="FuncDiag";
						winName='swPolkaEvnPrescrFunDiagEditWindow';
						if(record.get('EvnPrescr_id')!=null){
							params={
								 EvnPrescrFuncDiag_id:record.get('EvnPrescr_id'),
								 EvnPrescrFuncDiag_pid:record.get('EvnPrescr_pid'),
								 EvnPrescrFuncDiag_setDate:record.get('EvnPrescr_setDate')
							};
						}else{
							params={
								EvnPrescrFuncDiag_id:record.get('EvnPrescr_id'),
								EvnPrescrFuncDiag_pid:record.get('EvnPrescr_pid'),
								EvnPrescrFuncDiag_setDate:record.get('EvnPrescr_setDate'),
								EvnPrescrFuncDiag_uslugaList:record.get('UslugaId_List'),
								EvnPrescrFuncDiag_IsCito:(record.get('IsCito_Code')==1)?'on':'',
								EvnPrescrFuncDiag_Descr:record.get('EvnPrescr_Descr'),
								mode:'nosave'
							};
						}
						callback = function(rec){
							var data={
								EvnPrescr_setDate:rec.EvnPrescrFuncDiag_setDate,
								UslugaId_List:rec.EvnPrescrFuncDiag_uslugaList,
								Usluga_List:rec.Usluga_List,
								IsCito_Code:(rec.EvnPrescrFuncDiag_IsCito=='on')?1:0,
								EvnPrescr_Descr:rec.EvnPrescrFuncDiag_Descr
							};
							thas.dataView.updateRec(record,data);
                            thas.hasChange = true;
						};
						break;
					case 13:
						spec_name="ConsUsluga";
						winName='swPolkaEvnPrescrConsUslugaEditWindow';
						if(record.get('EvnPrescr_id')!=null){
							params={
								EvnPrescrConsUsluga_id:record.get('EvnPrescr_id'),
								EvnPrescrConsUsluga_pid:record.get('EvnPrescr_pid'),
								EvnPrescrConsUsluga_setDate:record.get('EvnPrescr_setDate')
							};
						}else{
							params={
								EvnPrescrConsUsluga_id:record.get('EvnPrescr_id'),
								EvnPrescrConsUsluga_pid:record.get('EvnPrescr_pid'),
								EvnPrescrConsUsluga_setDate:record.get('EvnPrescr_setDate'),
								UslugaComplex_id:record.get('UslugaId_List'),
								EvnPrescrConsUsluga_IsCito:(record.get('IsCito_Code')==1)?'on':'',
								EvnPrescrConsUsluga_Descr:record.get('EvnPrescr_Descr'),
								mode:'nosave'
							};
						}
						callback = function(rec){
							var data={
								EvnPrescr_setDate:rec.EvnPrescrConsUsluga_setDate,
								UslugaId_List:rec.UslugaComplex_id,
								IsCito_Code:(rec.EvnPrescrConsUsluga_IsCito=='on')?1:0,
								EvnPrescr_Descr:rec.EvnPrescrConsUsluga_Descr,
								Usluga_List:rec.Usluga_List
							};
							thas.dataView.updateRec(record,data);
                            thas.hasChange = true;
						};
						break;
					
				}
				var win = getWnd(winName);
				if (win.isVisible()) {
					win.hide();
				}
				win.show({
					callback: callback,
					action:'edit',
					winForm:'uslugaInput',
					parentEvnClass_SysNick:"EvnSection",
					formParams:params					
				});
			},
			viewDirection:function(key){
				var store =  this.getStore();
				var index = store.findBy(function(record){
					return record.get('EvnPrescr_key') == key;
				});
				var record = store.getAt(index);
				getWnd('swEvnDirectionEditWindow').show({action:"view",EvnDirection_id:record.get('EvnDirection_id'),ARMType:'common',formParams:record.data});
			},
            delPrescr:function(key){
				var params = {};
                var store =  this.getStore();
                var index = store.findBy(function(record){
					return record.get('EvnPrescr_key') == key;
                });
				
                var record = store.getAt(index);
                if (record) {
					params={
						EvnPrescr_id:record.get('EvnPrescr_id'),
						PrescriptionType_id:record.get('PrescriptionType_id'),
						parentEvnClass_SysNic:'EvnSection'
					};
                    if (record.get('RecordStatus')=='new') {
                        store.removeAt(index);
                        this.filterDS();
                    } else {
                        var loadMask = new Ext.LoadMask(thas.getEl(), {msg: "Отмена назначения..."});
                        loadMask.show();
                        Ext.Ajax.request({
                            failure:function () {
                                loadMask.hide();
                                sw.swMsg.alert('Ошибка', 'Ошибка при отмене назначения');
                            },
                            params:params,
                            success:function (response) {
                                if (response.responseText) {
                                    var answer = Ext.util.JSON.decode(response.responseText);
                                    if (answer.success) {
                                        loadMask.hide();
                                        store.removeAt(index);
                                        thas.dataView.filterDS();
                                        thas.hasChange = true;
                                    } else if (answer.Error_Message) {
                                        Ext.Msg.alert('Ошибка', answer.Error_Message);
                                    }
                                } else {
                                    Ext.Msg.alert('Ошибка', 'Ошибка при отмене назначения! Отсутствует ответ сервера.');
                                }
                            },
                            url:'?c=EvnPrescr&m=cancelEvnPrescr'
                        });
                    }
                }
            },
            updateRec: function(rec, data){
				
                var index = this.getStore().indexOf(rec);
                try {
                    for (var param in data) {
                        rec.set(param, data[param]);
						
                    }
                    rec.commit();
					this.refresh();
					
                   // this.refreshNode(index);
                } catch (e) {
                    //TypeError: d is undefined in d.parentNode.insertBefore(replacement, d);
                    //log([index, rec, data, e, this.getStore(), this]);
					log("OK")
                }
            },
            filterDS: function(){
                this.getStore().filterBy(function (rec) {
                    return rec.get('RecordStatus') != 'delete';
                });
            },
            assocLockedTimetableMedServiceId: {},
            assocUslugaFrameRec: {},
            assocDirection: {},
            lastNewIndex: 0,
            getNewEvnPrescrKey: function(prescr_type) {
                this.lastNewIndex++;
                return 'new_'+prescr_type+'_'+this.lastNewIndex;
            },
            resetNewIndex: function() {
                this.lastNewIndex = 0;
                this.assocLockedTimetableMedServiceId = {};
                this.assocUslugaFrameRec = {};
                this.assocDirection = {};
            },
            addRec: function(uslugaFrameRec, formParams, direction){
                var base_form = thas.filterPanel.getForm();
                var prescr_type = base_form.findField('UslugaComplex_id').PrescriptionType_Code;
                var key = this.getNewEvnPrescrKey(prescr_type);
				var uslugaList='';
                //блокировку бирки осуществляем при добавлении в правую часть
                if (uslugaFrameRec.get('TimetableMedService_id')) {
                    sw.Promed.Direction.lockTime(thas, 'TimetableMedService', uslugaFrameRec.get('TimetableMedService_id'), function(time_id){
                        thas.dataView.assocLockedTimetableMedServiceId[key] = time_id;
                    });
                }
                this.assocUslugaFrameRec[key] = uslugaFrameRec;
                this.assocDirection[key] = direction;
				if (uslugaFrameRec.compositionMenu) {
                    uslugaFrameRec.compositionMenu.items.each(function(item){
                        if (item.checked) {
                            uslugaList+=item.UslugaComplex_id+',';
                        }
                    });
				}
                var dds=[{
                    EvnPrescr_key: key,
                    EvnPrescr_id: null,
                    EvnPrescr_IsExec: null,
                    timetable: null,
                    timetable_id: null,
                    EvnDirection_id: null,
                    PrescriptionType_id: prescr_type,
                    PrescriptionStatusType_id: 1,
                    Usluga_List: uslugaFrameRec.get('UslugaComplex_Name'),
                    UslugaId_List: uslugaFrameRec.get('UslugaComplex_id'),
					EvnPrescr_uslugaList:uslugaList,
                    EvnPrescr_pid: base_form.findField('Evn_pid').getValue(),
                    PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
                    Server_id: base_form.findField('Server_id').getValue(),
                    EvnPrescr_setDate: formParams.EvnPrescr_setDate || base_form.findField('EvnPrescr_setDate').getValue(),
                    EvnPrescr_IsCito: uslugaFrameRec.get('EvnPrescr_IsCito'),
                    CountInDay: formParams.CountInDay || null,
                    CourseDuration: formParams.CourseDuration || null,
                    ContReception: formParams.ContReception || null,
                    Interval: formParams.Interval || null,
                    DurationTypeP_Nick: formParams.DurationTypeP_Nick || null,
                    DurationTypeN_Nick: formParams.DurationTypeN_Nick || null,
                    DurationTypeI_Nick: formParams.DurationTypeI_Nick || null,
                    DurationType_id: formParams.DurationType_id || null,
                    DurationType_recid: formParams.DurationType_recid || null,
                    DurationType_intid: formParams.DurationType_intid || null,
                    //IsCito_Name: null,
					EvnPrescr_IsDir:2,
					RecTo:uslugaFrameRec.get('MedService_Nick')+'/'+uslugaFrameRec.get('Lpu_Nick'),
					RecDate:uslugaFrameRec.get('TimetableMedService_begTime')||'В очередь',
                    IsCito_Code: (uslugaFrameRec.get('EvnPrescr_IsCito'))?1:0,
                    EvnDirection_Num: direction.EvnDirection_Num,
                    EvnPrescrGroup_Title: null,
                    RecordStatus:'new',
					EvnPrescrGroup_Count:null,
					PrescriptionDietType_Name:null,
					Drug_Info:null,
					EvnPrescrTreatDrug_KolvoEd:null,
					DrugForm_Nick:null,
					EvnPrescrTreatDrug_Kolvo:null,
					Okei_NationSymbol:null,
					EvnPrescrTreatDrug_DoseDay:null,
					EvnPrescrTreatDrug_DoseCource:null,
					PrescriptionIntroType_Name:null,
					PerformanceType_Name:null,
					Params:null,
					EvnPrescr_setTime:null,
					MedicationRegime:null
                }];
                this.getStore().loadData(dds,true);
                this.filterDS();

                var new_record;
                this.getStore().each(function(rec){
                    if (rec.get('EvnPrescrGroup_Count')&&(rec.get('PrescriptionType_id')==prescr_type)) {
                        rec.set('EvnPrescrGroup_Count',(rec.get('EvnPrescrGroup_Count')*1+1));
                        return false;
                    }
                    return true;
                }, this);
                this.getStore().each(function(rec){
                    if (rec.get('RecordStatus') == 'new' && rec.get('EvnPrescr_key') == key) {
                        new_record = rec;
                        return false;
                    }
                    return true;
                }, this);
                thas._save(new_record, function(rec, error_msg){
                    if (error_msg) {
                        sw.swMsg.alert('Ошибка', 'При сохранении назначения услуги <br/>"'+ rec.get('Usluga_List')
                            +'"<br/>произошла ошибка: '+ error_msg);
                    } else {
                        thas.hasChange = true;
                        thas.dataView.refresh();
                    }
                });
            }
        });

        this.dataViewPanel = new Ext.Panel({
            region: 'center',
            autoScroll: true,
            minSize: 400,
            //maxSize: 600,
            id: 'EvnPrescrUslugaInputDataViewPanel',
            layout:'fit',
            frame:true,
            bodyStyle: 'background-color: #fff;',
            width:400,
            //split: true,
            /*
             collapsed: false,
             animCollapse: false,
             floatable: false,
             collapsible: false,
             layoutConfig:
             {
             titleCollapse: true,
             animate: true,
             activeOnTop: false,
             style: 'border 0px'
             },
             */
            items: [
                this.dataView
            ]
        });

        var uslugaFrameKeyColumnName = 'UslugaComplex_2011id';
        this.uslugaFrame = new sw.Promed.ViewFrame({
            id: 'EvnPrescrUslugaInputViewFrame',
            keyColumnName: uslugaFrameKeyColumnName,
            actions: [
                {name:'action_add', hidden: true, disabled: true},
                {name:'action_edit', hidden: true, disabled: true},
                {name:'action_view', hidden: true, disabled: true},
                {name:'action_delete', hidden: true, disabled: true},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true},
                {name:'action_resetfilter', hidden: true, disabled: true},
                {name:'action_save', hidden: true, disabled: true}
            ],
            stringfields: [
                {name: uslugaFrameKeyColumnName, type: 'int', header: 'ID', key: true},
                {name: 'isComposite', type: 'int', hidden: true},
                {name: 'UslugaComplex_Code', type: 'string', hidden: true},
                {name: 'UslugaComplex_Name', type: 'string', hidden: true},
                {name: 'UslugaComplex_FullName', header: 'Услуга', sortable: false, autoexpand: true, autoExpandMin: 150, renderer: function(value, cellEl, rec){
                    if (!rec.get('UslugaComplex_Name')) return '';
                    return rec.get('UslugaComplex_Code') +" "+rec.get('UslugaComplex_Name');
                }},
                {name: 'compositionCntAll', type: 'int', hidden: true},
                {name: 'compositionCntChecked', type: 'int', hidden: true},
                {name: 'composition', id: 'EPUIVF_composition', header: 'Состав', width: 90, sortable: false, renderer: function(value, cellEl, rec){
                    var PrescriptionType_id = thas.filterPanel.getForm().findField('UslugaComplex_id').PrescriptionType_Code;
                    if (PrescriptionType_id==11 && 1 == rec.get('isComposite')) {
                        var text = 'Изменить';
                        if (rec.get('compositionCntAll') > 0) {
                            text += ' ('+rec.get('compositionCntChecked')+'/'+rec.get('compositionCntAll')+')';
                        }
                        return '<a href="#" ' +
                            'id="composition_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').showComposition('+
                            "'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
                            ')">'+ '<span class="button"><span>' +'</a>';
                    }
                    return '';
                }},
                {name: 'EvnPrescr_IsCito', header: 'Cito!', type: 'checkcolumnedit', sortable: false, width: 35},

                //ниже параметры службы, где оказывается услуга
                {name: 'MedService_cnt', type: 'int', hidden: true},
                {name: 'UslugaComplexMedService_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'Lpu_id', type: 'int', hidden: true},
                {name: 'Lpu_Nick', type: 'string', hidden: true},
                {name: 'LpuBuilding_id', type: 'int', hidden: true},
                {name: 'LpuBuilding_Name', type: 'string', hidden: true},
                {name: 'LpuUnit_id', type: 'int', hidden: true},
                {name: 'LpuUnit_Name', type: 'string', hidden: true},
                {name: 'LpuUnitType_id', type: 'int', hidden: true},
                {name: 'LpuUnitType_SysNick', type: 'string', hidden: true},
                {name: 'LpuSection_id', type: 'int', hidden: true},
                {name: 'LpuSection_Name', type: 'string', hidden: true},
                {name: 'LpuSectionProfile_id', type: 'int', hidden: true},
                {name: 'LpuUnit_Name', type: 'string', hidden: true},
                {name: 'LpuUnit_Address', type: 'string', hidden: true},
                {name: 'MedService_id', type: 'int', hidden: true},
                {name: 'MedService_Nick', type: 'string', hidden: true},
                {name: 'MedService_Name', type: 'string', hidden: true},
                {name: 'MedServiceType_id', type: 'int', hidden: true},
                {name: 'MedServiceType_SysNick', type: 'string', hidden: true},
                {name: 'location', header: 'Место оказания', width: 200, sortable: false, renderer: function(value, cellEl, rec){
                    if (!rec.get('MedService_id')) {
                        // Для прочих услуг из справочника, которые не оказывается даже в других ЛПУ.
                        return '<a href="#" title="Выбрать ЛПУ и создать направление" '+
                            'id="selectlpu_link_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').selectLpu('+
                            "'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
                            ')">Выбрать ЛПУ</a>';
                    }
                    var text = rec.get('MedService_Nick') +'<br>'+ rec.get('Lpu_Nick');
                    var hint = rec.get('MedService_Name') +' / '+ rec.get('Lpu_Nick') +' / '+
                        rec.get('LpuUnit_Name') +' / '+ rec.get('LpuUnit_Address');
                    if (rec.get('MedService_cnt') > 1) {
                        return '<a href="#" title="'+ hint +'" '+
                            'id="showmedserviceall_link_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').showMedServiceAll('+
                            "'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
                            ')">'+ text +'</a>';
                    } else {
                        return '<span title="'+ hint +'">'+ text +'</span>';
                    }
                }},

                // ниже параметры для записи
                {name: 'ttms_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_Lpu_id', type: 'int', hidden: true},
                {name: 'pzm_MedService_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_id', type: 'int', hidden: true},
                {name: 'pzm_MedServiceType_SysNick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Nick', type: 'string', hidden: true},
                {name: 'pzm_MedService_Name', type: 'string', hidden: true},
                {name: 'TimetableMedService_id', type: 'int', hidden: true},
                {name: 'TimetableMedService_begTime', type: 'string', hidden: true},
                {name: 'timetable', header: 'Запись', width: 100, sortable: false, renderer: function(value, cellEl, rec){
                    var text = 'Выбрать время';
                    if (rec.get('TimetableMedService_begTime')) {
                        var dt = Date.parseDate(rec.get('TimetableMedService_begTime'), 'd.m.Y H:i');
                        text = dt.format('j M H:i').toLowerCase();
                    }
                    if (rec.get('ttms_MedService_id')) {
                        return '<a href="#" ' +
                            'id="apply_link_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
                            'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').doApply('+
                            "'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
                            ')">'+ text +'</a>';
                    }
                    return 'В очередь';
                }},
				{
                    name: 'doInsert', header: ' ', width: 100, sortable: false, renderer: function(value, cellEl, rec){
						if (!rec.get('UslugaComplex_Name')) return '';
						return '<a class="addButton"' +
							'id="insert_btn_'+ rec.get(uslugaFrameKeyColumnName) +'" '+
							'onclick="Ext.getCmp(\'EvnPrescrUslugaInputWindow\').doInsert('+
							"'"+ rec.get(uslugaFrameKeyColumnName) +"'"+
							')"><img src="/img/EvnPrescrPlan/add.png" title="Назначить" /> Назначить</a>';//<a href="#" ><img src="/img/EvnPrescrPlan/add.png" title="Добавить" /> Добавить</a>
					}
                }
            ],
            autoLoadData: false,
            border: true,
            //dataUrl: '/?c=MedService&m=getUslugaComplexMedServiceList',
            dataUrl: '/?c=MedService&m=getUslugaComplexSelectList',
            object: 'UslugaComplex',
            layout: 'fit',
            height: 300,
            root: 'data',
            totalProperty: 'totalCount',
            paging: true,
            region: 'center',
            toolbar: false,
            editing: true,
            onAfterEditSelf: function(o) {
                o.record.commit();
            },
            onLoadData: function() {
                //this.getGrid().getStore()
            },
            onDblClick: function() {
                this.onEnter();
            },
            onEnter: function() {
                var rec = this.getGrid().getSelectionModel().getSelected();
                if (rec) {
                    thas.doInsert(rec.get(uslugaFrameKeyColumnName));
                }
            }
        });

        Ext.apply(this, {
//            buttonAlign: "right",
            buttons: [
			{
                handler: function() {
                    thas.doSave();
                },
                hidden: true,
                iconCls: 'ok16',
                text: "Сохранить"
            }, {
                text: '-'
            },
            {
                text: BTN_FRMHELP,
                iconCls: 'help16',
                handler: function() {
                    ShowHelp(thas.winTitle);
                }.createDelegate(self)
            },
            {
                handler: function() {
                    thas.hide();
                },
                iconCls: 'cancel16',
                text: BTN_FRMCLOSE
            }],
            border: false,
            layout: 'border',
            items: [
                new Ext.Panel({
                    region: 'west',
                    width: 400,
                    layout:'border',
                    listeners:
                    {
                        render: function(p) {
                            var body_width = Ext.getBody().getViewSize().width;
                            p.setWidth(body_width * (7/12));
                        }
                    },
                    items: [
                        this.filterPanel,
                        this.uslugaFrame
                    ]
                }),
                this.dataViewPanel
            ]
        });
        sw.Promed.swEvnPrescrUslugaInputWindow.superclass.initComponent.apply(this, arguments);
    }
});
