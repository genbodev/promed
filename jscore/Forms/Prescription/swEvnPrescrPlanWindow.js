/**
* swEvnPrescrPlanWindow - лист назначений.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      EvnPrescr
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      09.2013
* @comment      Префикс для id компонентов EPRPLF (EvnPrescrPlanForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnPrescrPlanWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrPlanWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrPlanWindow.js',

	buttonAlign: 'left',
    title: WND_PRESCR_PLAN,
    width: 850,
    plain: true,
    resizable: true,
    loadMask: null,
    maximizable: true,
    maximized: false,
    minHeight: 550,
    minWidth: 750,
    modal: true,
    isChange: false,
    callback: Ext.emptyFn,
    onHide: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	draggable: true,
	height: 550,
	id: 'EvnPrescrPlanWindow',
    listeners: {
        hide: function(win) {
            (win.isChange)?win.callback():win.onHide();
        }
    },
    show: function() {
        sw.Promed.swEvnPrescrPlanWindow.superclass.show.apply(this, arguments);

        this.restore();
        this.center();
        this.maximize();

        this.isChange = false;

        if ( !arguments[0] || !arguments[0].formParams ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
            return false;
        }

        if ( arguments[0] && arguments[0].LpuSection_Name ) {
            this.setTitle(WND_PRESCR_PLAN + ' - ' + arguments[0].LpuSection_Name);
        }
        else {
            this.setTitle(WND_PRESCR_PLAN);
        }

        var base_form = this.FormPanel.getForm();
        base_form.reset();

        base_form.setValues(arguments[0].formParams);

        this.callback = Ext.emptyFn;
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }

        this.onHide = Ext.emptyFn;
        if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
            this.onHide = arguments[0].onHide;
        }

        this.userMedStaffFact = null;
        if ( arguments[0].userMedStaffFact && typeof arguments[0].userMedStaffFact == 'object' ) {
            this.userMedStaffFact = arguments[0].userMedStaffFact;
        }

        this.PersonInfo.load({
            Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
            Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
            Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
            Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
            Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
        });

        this.ViewActions['action_openstandart'].setDisabled(!arguments[0].formParams.CureStandart_Count);
        this.loadDataViewStore();
        this.createPrescriptionTypeMenu();
    },
    /**
     * Загрузка данных имеющихся назначений в правую часть формы
     */
    loadDataViewStore:function(){
        var base_form = this.FormPanel.getForm();
        var thas = this;
        thas.CalendarDataView.reset();
        thas.getLoadMask(LOAD_WAIT).show();
        this.EvnPrescrListDataView.getStore().load({
            params: {
                Evn_rid: base_form.findField('EvnPrescr_rid').getValue(),
                Evn_pid: base_form.findField('EvnPrescr_pid').getValue()
            },
            callback: function(records){
                for (var i=0; i<records.length; i++) {
                    thas._proccesingRecord(records[i]);
                }
                thas.CalendarDataView.overwriteTpl();
                thas.getLoadMask().hide();
            }
        });
    },
    /**
     * Создание назначения клику по кнопке "Добавить" в строке типа назначения
     * или по двойному клику в ячейке пустой строки
     * @param key
     * @param cell_index
     */
    addPrescrByType: function(key, cell_index) {
        var rec = this.EvnPrescrListDataView.getStore().getById(key);
        if (!rec) {
            return false;
        }
        var thas = this, personFrame = this.PersonInfo;
        var set_date = this.CalendarDataView.tplData.date_list[cell_index] || null;
        var base_form = this.FormPanel.getForm();
        if (!set_date) {
            set_date = base_form.findField('EvnPrescr_begDate').getValue();
        }
        var option = {
            action: 'add',
            PrescriptionType_id: rec.get('PrescriptionType_id'),
            PrescriptionType_Code: rec.get('PrescriptionType_Code'),
            parentEvnClass_SysNick: 'EvnSection',
            userMedStaffFact: this.userMedStaffFact,
            data: {
                Person_Firname: personFrame.getFieldValue('Person_Firname'),
                Person_Surname: personFrame.getFieldValue('Person_Surname'),
                Person_Secname: personFrame.getFieldValue('Person_Secname'),
                Person_Birthday: personFrame.getFieldValue('Person_Birthday'),
                Person_Age: swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), base_form.findField('EvnPrescr_begDate').getValue()),
                Diag_Code: base_form.findField('Diag_Code').getValue(),
                Diag_Name: base_form.findField('Diag_Name').getValue(),
                Diag_id: base_form.findField('Diag_id').getValue(),
                Person_id: base_form.findField('Person_id').getValue(),
                PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
                Server_id: base_form.findField('Server_id').getValue(),
                Evn_pid: base_form.findField('EvnPrescr_pid').getValue(),
                begDate: set_date
            },
            onHideEditWindow: function() {
                //
            },
            callbackEditWindow: function() {
                thas.isChange = true;
                thas.loadDataViewStore();
            }
        };
        sw.Promed.EvnPrescr.openEditWindow(option);
        return true;
    },
    onCellClick: function(e, node, params) {
        e.stopEvent();
        var thas = this,
            base_form = this.FormPanel.getForm(),
            coords = e.getXY(),
            id_parts = params.element.id.split('_'),
            rec, data, key, index_cell;
        if (id_parts[0]=='EvnPrescrCell') {
            key = id_parts[1];
        }
        if (id_parts[0]=='EvnPrescrDayCell' || id_parts[0]=='EmptyDayCell') {
            var arr = this.CalendarDataView.tplData.rows;
            index_cell = id_parts[1];
            key = id_parts[2];
            for (var i=0; i<arr.length; i++) {
                if (arr[i].EvnPrescr_key == key) {
                    if (arr[i].cells[index_cell]) {
                        data = arr[i].cells[index_cell];
                    }
                    break;
                }
            }
        }
        rec = this.EvnPrescrListDataView.getStore().getById(key);
        /*
        log(params.element.id);
        log([key, index_cell]);
        log([rec, data]);
        return false;
        */
        if (!rec) {
            return false;
        }
        var allowActions = false;
        var allowEdit = false;
        var allowView = false;
        var allowDelete = false;
        var allowExec = false;
        var allowUnExec = false;
        var allowDayMove = false, day_dt, next_date, prev_date;
        var cur_dt = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
        if (data && data.set_date) {
            day_dt = Date.parseDate(data.set_date, 'd.m.Y');
        }
        if (id_parts[0]=='EvnPrescrCell') {
            //Клик в заголовок
            allowActions = rec.get('EvnPrescr_id');
            allowEdit =(allowActions && rec.get('EvnPrescr_IsExec') != 2);
            allowView = (allowActions);
            allowDelete = (allowEdit);
        }
        if (id_parts[0]=='EmptyDayCell') {
            //Клик в пустую ячейку пустой строки
            //клик должен вызывать форму назначения соответствующего типа
            //При этом плановая дата (либо дата начала) должна соответствовать ячейке
            this.addPrescrByType(key, index_cell);
            return true;
        }
        if (id_parts[0]=='EvnPrescrDayCell') {
            //Клик в пустую ячейку строки назначения
            allowActions = (rec.get('EvnPrescr_id') && day_dt);
            next_date = Ext.util.Format.date(day_dt.add(Date.DAY, 1), 'd.m.Y');
            prev_date = Ext.util.Format.date(day_dt.add(Date.DAY, -1), 'd.m.Y');
            if (day_dt.add(Date.DAY, -1) < this.CalendarDataView.params.Evn_setDate) {
                prev_date = '';
            }
            allowDayMove = (
                allowActions
                    && data.Day_IsExec == false
                    && rec.get('EvnPrescr_IsDir') != 2
                    && !rec.get('PrescriptionType_Code').toString().inlist(['1','2','10','5','6'])
                );
            allowEdit =(allowActions && rec.get('EvnPrescr_IsExec') != 2);
            allowView = (allowActions);
            allowDelete = (allowEdit);
            allowExec = (allowActions && !data.Day_IsExec);
            allowUnExec = (allowActions && data.Day_IsExec);
        }
        switch (rec.get('PrescriptionType_Code').toString()) {
            case '1':
            case '2':
            case '10':
                if (id_parts[0]=='EvnPrescrCell') {
                    //Клик в заголовок
                }
                if (id_parts[0]=='EmptyDayCell') {
                    //Клик в пустую ячейку пустой строки
                }
                if (id_parts[0]=='EvnPrescrDayCell') {
                    //Клик в ячейку строки назначения
                    if (data && data.text == '&nbsp;') {
                        //клик по пустой ячейке должен вызывать форму назначения соответствующего типа
                        //При этом плановая дата (либо дата начала) должна соответствовать ячейке
                        this.addPrescrByType(key, index_cell);
                        return true;
                    }
                }
                break;
            case '13':
            case '12':
            case '11':
            case '6':
            case '7':
                //log([id_parts[0], day_dt, cur_dt, data.text, allowView]);
                if (id_parts[0]=='EvnPrescrCell') {
                    //Клик в заголовок
                }
                if (id_parts[0]=='EmptyDayCell') {
                    //Клик в пустую ячейку пустой строки
                }
                if (id_parts[0]=='EvnPrescrDayCell') {
                    //Клик в ячейку строки назначения
                    if (day_dt && data.text == '&nbsp;' && day_dt < cur_dt) {
                        //Если в пустой ячейку дата меньше текущей, то никаких действий не производим
                        return false;
                    }
                    if (day_dt && data.text == '&nbsp;') {
                        // Клик в пустую ячейку
                        if (false && rec.get('EvnPrescr_IsDir') == 2) {
                            /* @todo Если в строке есть направления
                             Если дата ячейки равна или больше текущей, открывается расписание услуги-службы (а если отдельного расписания нет, то службы) из строки на дату из ячейки. Результатом должно явиться аналогичное созданное направление на новую дату-время.
                             Если бирок нет, ставить в очередь с плановой датой из ячейки.
                             */
                        } else {
                            // Если направлений в строке нет:
                            // Вызов формы редактирования назначения с подставленной услугой из строки.
                            if (allowView) {
                                this.ViewContextMenu.params = {
                                    EvnPrescrListRecord: rec,
                                    DayData: data
                                };
                                this.openEvnPrescrEditWindow(allowEdit?'edit':'view');
                                return true;
                            }
                        }
                    }
                    if (data.text != '&nbsp;') {
                        // Клик в заполненную ячейку
                        //Выполнить (только для назначений без направлений в службы): ставит отметку о выполнении назначения
                        allowExec = (allowActions && rec.get('EvnPrescr_IsDir') != 2 && !data.Day_IsExec);
                        //Отменить выполнение (только для выполненных без событий оказания услуг)
                        allowUnExec = (allowActions && rec.get('EvnPrescr_IsDir') != 2 && data.Day_IsExec);
                    }
                }
                break;
            case '5':
                if (id_parts[0]=='EvnPrescrCell') {
                    //Клик в заголовок
                }
                if (id_parts[0]=='EmptyDayCell') {
                    //Клик в пустую ячейку пустой строки
                }
                if (id_parts[0]=='EvnPrescrDayCell') {
                    //Клик в ячейку строки назначения
                }
                break;
        }

        this.ViewActions['action_nextDay'].setDisabled(!allowDayMove||rec.data.days[rec.get('EvnPrescr_id')+'-'+next_date]);
        this.ViewActions['action_prevDay'].setDisabled(!allowDayMove||rec.data.days[rec.get('EvnPrescr_id')+'-'+prev_date]);
        this.ViewActions['action_edit'].setDisabled(!allowEdit);
        this.ViewActions['action_exec'].setDisabled(!allowExec);
        this.ViewActions['action_unexec'].setDisabled(!allowUnExec);
        this.ViewActions['action_view'].setDisabled(!allowView);
        this.ViewActions['action_delete'].setDisabled(!allowDelete);
        this.ViewContextMenu.params = {
            EvnPrescrListRecord: rec,
            DayData: data
        };
        this.ViewContextMenu.showAt([coords[0], coords[1]]);
        return true;
    },
    createPrescriptionTypeMenu: function() {
        var base_form = this.FormPanel.getForm();
        var exceptionTypes = [];
        var thas = this, personFrame = this.PersonInfo;
        sw.Promed.EvnPrescr.createPrescriptionTypeMenu({
            //ownerWindow: this,
            id: 'ListMenuPrescriptionTypePlan',
            exceptionTypes: exceptionTypes,
            parentEvnClass_SysNick: 'EvnSection',
            userMedStaffFact: this.userMedStaffFact,
            getParams: function(){
                return {
                    Person_Firname: personFrame.getFieldValue('Person_Firname'),
                    Person_Surname: personFrame.getFieldValue('Person_Surname'),
                    Person_Secname: personFrame.getFieldValue('Person_Secname'),
                    Person_Age: swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), base_form.findField('EvnPrescr_begDate').getValue()),
                    Diag_Code: base_form.findField('Diag_Code').getValue(),
                    Diag_Name: base_form.findField('Diag_Name').getValue(),
                    Person_id: base_form.findField('Person_id').getValue(),
                    PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
                    Server_id: base_form.findField('Server_id').getValue(),
                    Evn_pid: base_form.findField('EvnPrescr_pid').getValue(),
                    begDate: base_form.findField('EvnPrescr_begDate').getValue()
                };
            },
            onHideEditWindow: function() {
                //
            },
            callbackEditWindow: function() {
                thas.isChange = true;
                thas.loadDataViewStore();
            },
            onCreate: function(menu){
                thas.menuPrescriptionType = menu;
                thas.ViewActions['action_add'].items[0].menu = thas.menuPrescriptionType;
            }
        });
    },
    /**
     * Загрузить план из шаблона
     */
    getEvnPrescrList: function()
    {
        var hasEvnPrescr = false;
        var store = this.EvnPrescrListDataView.getStore();
        store.each(function(rec){
            if (rec.get('EvnPrescr_id')>0) {
                hasEvnPrescr = true;
                return false;
            }
            return true;
        });
        this.getEvnPrescrListGo(hasEvnPrescr);
    },
    getEvnPrescrListGo: function(checkTemplateOnExist)
    {
        var thas = this,
            base_form = this.FormPanel.getForm(),
            lm = this.getLoadMask(lang['zagruzka']),
            personFrame = this.PersonInfo;
        var params = {
            EvnPrescr_pid: base_form.findField('EvnPrescr_pid').getValue(),
            PersonAgeGroup_id: (swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), base_form.findField('EvnPrescr_begDate').getValue()) > 14) ? 1 : 2,
            LpuSection_id: base_form.findField('LpuSection_id').getValue(),
            EvnPrescr_begDate: Ext.util.Format.date(base_form.findField('EvnPrescr_begDate').getValue(), 'd.m.Y'),
            Diag_id: base_form.findField('Diag_id').getValue(),
            PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
            Server_id: personFrame.getFieldValue('Server_id')
        };
        if(checkTemplateOnExist)
            params.checkTemplateOnExist = 1; // Тоесть предварительная проверка на наличие шаблона

        lm.show();
        Ext.Ajax.request({
            url: '/?c=EvnPrescr&m=getEvnPrescrList',
            params: params,
            callback: function(o, s, r){
                lm.hide();
                if(s){
                    var obj = Ext.util.JSON.decode(r.responseText);
                    if(obj.checkTemplateOnExist){
                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn: function(buttonId) {
                                if (buttonId == 'yes') {
                                    thas.getEvnPrescrListGo(false);
                                }
                            },
                            icon: Ext.MessageBox.QUESTION,
                            msg: lang['tekuschiy_plan_budet_udalen_prodoljit'],
                            title: lang['podtverjdenie']
                        });
                    } else {
                        thas.isChange = true;
                        thas.loadDataViewStore();
                    }
                }
            }
        });
    },
    /**
     * Сохранить план назначений как шаблон
     */
    saveEvnPrescrListAsXTemplate: function()
    {
        var base_form = this.FormPanel.getForm(),
            personFrame = this.PersonInfo;
        getWnd('swEvnPrescrSaveTemplateWindow').show({
            EvnPrescr_pid: base_form.findField('EvnPrescr_pid').getValue(),
            Person_id: base_form.findField('Person_id').getValue(),
            PersonAgeGroup_id: (swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), base_form.findField('EvnPrescr_begDate').getValue()) > 14) ? 1 : 2,
            EvnPrescr_begDate: Ext.util.Format.date(base_form.findField('EvnPrescr_begDate').getValue(), 'd.m.Y'),
            LpuSection_id: base_form.findField('LpuSection_id').getValue(),
            Diag_id: base_form.findField('Diag_id').getValue()
        });
    },
    /**
     * Открыть форму назначений по федеральному стандарту.
     * Отмеченные назначения добавляем в лист назначений без даты.
     */
    openStandartWindow: function()
    {
        var thas = this,
            base_form = this.FormPanel.getForm();
        sw.Promed.EvnPrescr.openCureStandartSelectWindow({
            isForPrint: false
            ,parentEvnClass_SysNick: 'EvnSection'
            ,Evn_rid: base_form.findField('EvnPrescr_rid').getValue()
            ,Evn_pid: base_form.findField('EvnPrescr_pid').getValue()
            ,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
            ,Server_id: base_form.findField('Server_id').getValue()
            ,ownerWindow: this
            ,callback: function(){
                thas.isChange = true;
                thas.loadDataViewStore();
            }
            ,onCreate: function(menu){
                menu.show(Ext.get('EvnPrescrPlanList_'+d.object_id+'_addwithtemplate'),'tr');
            }
        });
    },
    /**
     * Отмена назначения-направления
     * @return {Boolean}
     */
    cancelEvnPrescr: function() {
        var thas = this;
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord) {
            return false;
        }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        if ( !rec.get('EvnPrescr_id') || rec.get('EvnPrescr_IsExec')==2) {
            return false;
        }
        sw.Promed.EvnPrescr.cancel({
            ownerWindow: thas
            ,getParams: function(){
                return {
                    parentEvnClass_SysNick: 'EvnSection'
                    ,PrescriptionType_id: rec.get('PrescriptionType_id')
                    ,EvnPrescr_id: rec.get('EvnPrescr_id')
                };
            }
            ,callback: function(){
                thas.isChange = true;
                thas.loadPrescrPlanView();
            }
        });
        return true;
    },
    /**
     * Отменить выполнение назначения
     * @return {Boolean}
     */
    unExecEvnPrescr: function() {
        var thas = this;
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord) {
            return false;
        }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        var dayData = this.ViewContextMenu.params.DayData;
        if ( !rec || !rec.get('EvnPrescr_id') ) {
            return false;
        }
        if ((dayData && !dayData.Day_IsExec) || 2!=rec.get('EvnPrescr_IsExec')) {
            return false;
        }
        if (2 == rec.get('PrescriptionStatusType_id')) {
            return false;
        }
        if (rec.get('EvnDirection_id') && rec.get('PrescriptionType_id').toString().inlist(['6','7','11','12','13'])) {
            return false;
        }
        
        var EvnPrescrDay_id = rec.get('EvnPrescr_id');
        if (rec.get('PrescriptionType_id') == 1) {
            if (!dayData || !dayData.EvnPrescrRegime_id) {
                return false;
            }
            EvnPrescrDay_id = dayData.EvnPrescrRegime_id;
        }
        if (rec.get('PrescriptionType_id') == 2) {
            if (!dayData || !dayData.EvnPrescrDiet_id) {
                return false;
            }
            EvnPrescrDay_id = dayData.EvnPrescrDiet_id;
        }
        if (rec.get('PrescriptionType_id') == 10) {
            if (!dayData || !dayData.EvnPrescrObserv_id) {
                return false;
            }
            EvnPrescrDay_id = dayData.EvnPrescrObserv_id;
        }
        if (rec.get('PrescriptionType_id') == 5) {
            if (!dayData || !dayData.EvnPrescrTreatTimetable_id) {
                return false;
            }
            EvnPrescrDay_id = dayData.EvnPrescrTreatTimetable_id;
        }
        if (rec.get('PrescriptionType_id') == 6) {
            if (!dayData || !dayData.EvnPrescrProcTimetable_id) {
                return false;
            }
            EvnPrescrDay_id = dayData.EvnPrescrProcTimetable_id;
        }
        sw.swMsg.show({
            buttons: Ext.Msg.YESNO,
            fn: function(buttonId) {
                if ( buttonId == 'yes' ) {
                    thas.getLoadMask().show();
                    Ext.Ajax.request({
                        failure: function(response, options) {
                            thas.getLoadMask().hide();
                            sw.swMsg.alert(lang['oshibka'], (response.status ? response.status.toString() + ' ' + response.statusText : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']));
                        },
                        params: {
                            EvnPrescr_id: EvnPrescrDay_id,
                            PrescriptionType_id: rec.get('PrescriptionType_id')
                        },
                        success: function(response, options) {
                            thas.getLoadMask().hide();
                            var response_obj = Ext.util.JSON.decode(response.responseText);
                            if ( response_obj.success == false ) {
                                sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_vyipolnenii_zaprosa_k_serveru']);
                            } else {
                                thas.isChange = true;
                                thas.loadDataViewStore();
                            }
                        },
                        url: '/?c=EvnPrescr&m=rollbackEvnPrescrExecution'
                    });
                }
            },
            icon: Ext.MessageBox.QUESTION,
            msg: lang['otmenit_fakt_vyipolneniya_naznacheniya'],
            title: lang['vopros']
        });
        return true;
    },
    /**
     * Выполнение назначения
     * @return {Boolean}
     */
    execEvnPrescr: function() {
        var thas = this,
            base_form = this.FormPanel.getForm();
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord) {
            return false;
        }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        var dayData = this.ViewContextMenu.params.DayData;
        if ( !rec.get('EvnPrescr_id') ) {
            return false;
        }
        var conf = {
            ownerWindow: this
            //,btnId: 'EvnPrescrPlanWindowExecEvnPrescr'
            ,btnId: (dayData?('EvnPrescrDayCell_'+dayData.index):('EvnPrescrCell_'+rec.get('EvnPrescr_key')))
            ,allowChangeTime: !dayData
            ,EvnPrescr_setDate: (dayData&&dayData.set_date)||null
            ,Person_id: base_form.findField('Person_id').getValue()
            ,PersonEvn_id: base_form.findField('PersonEvn_id').getValue()
            ,Server_id: base_form.findField('Server_id').getValue()
        };
        conf.EvnPrescr_id = rec.get('EvnPrescr_id');
        conf.PrescriptionType_id = rec.get('PrescriptionType_id');
        conf.EvnPrescr_IsExec = (dayData)?(dayData.Day_IsExec?2:1):rec.get('EvnPrescr_IsExec');
        conf.PrescriptionStatusType_id = rec.get('PrescriptionStatusType_id');
        conf.onExecSuccess = function(){
            thas.isChange = true;
            thas.loadDataViewStore();
        };
        conf.onExecCancel = function(){
            //
        };
        //for 1
        if(dayData&&dayData.EvnPrescrRegime_id) {
            conf.EvnPrescr_id = dayData.EvnPrescrRegime_id;
        }
        //for 2
        if(dayData&&dayData.EvnPrescrDiet_id) {
            conf.EvnPrescr_id = dayData.EvnPrescrDiet_id;
        }
        //for 10 ObservTimeType_Names
        var ObservTimeType_List = rec.get('ObservTimeType_idList').split(',');
        if (ObservTimeType_List.length) {
            conf.ObservTimeType_id = ObservTimeType_List[0];
        }
        /*if(dayData&&dayData.EvnPrescrObserv_id) {
            conf.EvnPrescr_id = dayData.EvnPrescrObserv_id;
        }*/
        //for 5
        if(dayData&&dayData.EvnPrescrTreatTimetable_id) {
            conf.Timetable_id = dayData.EvnPrescrTreatTimetable_id;
        }
        //for 6
        if(dayData&&dayData.EvnPrescrProcTimetable_id) {
            conf.Timetable_id = dayData.EvnPrescrProcTimetable_id;
        }
        //for 6,7,11,12
        conf.EvnPrescr_rid  = base_form.findField('EvnPrescr_rid').getValue();
        conf.EvnPrescr_pid = base_form.findField('EvnPrescr_pid').getValue();
        conf.Diag_id = base_form.findField('Diag_id').getValue();
        conf.UslugaId_List = rec.get('UslugaId_List');
        conf.TableUsluga_id = rec.get('TableUsluga_id');
        conf.PrescriptionType_Code = rec.get('PrescriptionType_Code');
        conf.Person_Birthday = this.PersonInfo.getFieldValue('Person_Birthday');
        conf.Person_Surname = this.PersonInfo.getFieldValue('Person_Surname');
        conf.Person_Firname = this.PersonInfo.getFieldValue('Person_Firname');
        conf.Person_Secname = this.PersonInfo.getFieldValue('Person_Secname');
        conf.EvnDirection_id = rec.get('EvnDirection_id');
        //log([rec, dayData]);
        sw.Promed.EvnPrescr.exec(conf);
        return true;
    },
    /**
     * Открытие формы назначения на просмотр или редактирование
     * @return {Boolean}
     */
    openEvnPrescrEditWindow: function(action) {
        //log([action, this.ViewContextMenu.params, this.ViewContextMenu.params.EvnPrescrListRecord]);
        var thas = this,
            base_form = this.FormPanel.getForm(),
            personFrame = this.PersonInfo;
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord) {
            return false;
        }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        if ( !rec.get('EvnPrescr_id') || !action.inlist(['edit', 'view'])) {
            return false;
        }
        var set_date = base_form.findField('EvnPrescr_begDate').getValue();
        if (this.ViewContextMenu.params.DayData) {
            set_date = this.ViewContextMenu.params.DayData.set_date;
        }
        sw.Promed.EvnPrescr.openEditWindow({
            action: action,
            PrescriptionType_id: rec.get('PrescriptionType_id'),
            PrescriptionType_Code: rec.get('PrescriptionType_Code'),
            parentEvnClass_SysNick: 'EvnSection',
            userMedStaffFact: this.userMedStaffFact,
            data: {
                Person_Firname: personFrame.getFieldValue('Person_Firname'),
                Person_Surname: personFrame.getFieldValue('Person_Surname'),
                Person_Secname: personFrame.getFieldValue('Person_Secname'),
                Person_Birthday: personFrame.getFieldValue('Person_Birthday'),
                Person_Age: swGetPersonAge(personFrame.getFieldValue('Person_Birthday'), base_form.findField('EvnPrescr_begDate').getValue()),
                Diag_Code: base_form.findField('Diag_Code').getValue(),
                Diag_Name: base_form.findField('Diag_Name').getValue(),
                Diag_id: base_form.findField('Diag_id').getValue(),
                Person_id: base_form.findField('Person_id').getValue(),
                PersonEvn_id: base_form.findField('PersonEvn_id').getValue(),
                Server_id: base_form.findField('Server_id').getValue(),
                Evn_pid: base_form.findField('EvnPrescr_pid').getValue(),
                EvnPrescr_id: rec.get('EvnPrescr_id'),
                begDate: set_date
            }
            ,onHideEditWindow: function() {
                //
            }
            ,callbackEditWindow: function() {
                thas.isChange = true;
                thas.loadDataViewStore();
            }
        });
        return true;
    },
    /**
     * Обработка записи из хранилища
     * @param rec
     * @return {Boolean}
     */
    _proccesingRecord: function(rec) {
        //log(['_proccesingRecord', rec, this]);
        switch (true) {
            case (rec.id == 'CommonData'):
                this.CalendarDataView.applyCommonData(rec.data);
                break;
            case (rec.id.indexOf('EvnPrescrGroup') >=0 ):
                this.CalendarDataView.applyEvnPrescrGroupData(rec.data);
                break;
            case (rec.id.indexOf('EmptyRow') >=0 ):
                this.CalendarDataView.applyEmptyRowData(rec.data);
                break;
            default:
                this.CalendarDataView.applyEvnPrescrData(rec.data);
                break;
        }
        return true;
    },
    /**
     * Сдвиг отображение календаря на неделю вперед или назад или отображение на текущую дату
     * @param mode
     * @return {Boolean}
     */
    moveOnWeek: function(mode) {
        if (!mode||!mode.inlist(['today','back','forward'])) {
            return false;
        }
        this.CalendarDataView.reset();
        this.CalendarDataView.modeView = mode;
        this.EvnPrescrListDataView.getStore().each(this._proccesingRecord, this);
        this.CalendarDataView.overwriteTpl();
        return true;
    },
    /**
     * Перенос назначения на день вперед (next) или назад (prev)
     * @param whither
     * @return {Boolean}
     */
    moveInDay: function(whither) {
        var thas = this,
            lm = this.getLoadMask(lang['peremeschenie']);
        if ( typeof this.ViewContextMenu.params != 'object' || !this.ViewContextMenu.params.EvnPrescrListRecord || !this.ViewContextMenu.params.DayData) {
            return false;
        }
        var rec = this.ViewContextMenu.params.EvnPrescrListRecord;
        if ( !rec.get('EvnPrescr_id') || !whither.inlist(['next', 'prev'])) {
            return false;
        }

        lm.show();
        Ext.Ajax.request({
            url: '/?c=EvnPrescr&m=EvnPrescrMoveInDay',
            params: {
                EvnPrescr_id: rec.get('EvnPrescr_id'),
                PrescriptionType_id: rec.get('PrescriptionType_id'),
                whither: whither,
                EvnPrescr_setDate: this.ViewContextMenu.params.DayData.set_date
            },
            callback: function(o, s, r){
                lm.hide();
                if(s){
                    var obj = Ext.util.JSON.decode(r.responseText);
                    if(obj.success){
                        thas.isChange = true;
                        thas.loadDataViewStore();
                    }
                }
            }
        });
        return true;
    },

    initComponent: function() {
        var thas = this;

        this.ViewActions = {};
        var actions = [
            {
                name:'action_add',
                text:BTN_GRIDADD,
                tooltip: BTN_GRIDADD_TIP,
                icon: 'img/icons/add16.png',
                menu: new Ext.menu.Menu({id:'ListMenuPrescriptionTypePlan'}),
                handler: function() {}
            },
            {
                name:'action_exec',
                text: lang['vyipolnit'],
                tooltip: lang['otmetit_naznachenie_kak_vyipolnenoe'],
                iconCls: 'exec16',
                handler: function() {
                    thas.execEvnPrescr();
                },
                id: 'EvnPrescrPlanWindowExecEvnPrescr'
            },
            {
                name:'action_unexec',
                text: lang['otmenit_vyipolnenie'],
                tooltip: lang['snyat_otmetku_ob_ispolnenii_uslugi'],
                iconCls: 'unexec16',
                handler: function() {
                    thas.unExecEvnPrescr();
                },
                id: 'EvnPrescrPlanWindowUnExecEvnPrescr'
            },
            {name:'action_edit', text:BTN_GRIDEDIT, tooltip: BTN_GRIDEDIT_TIP, icon: 'img/icons/edit16.png', handler: function() {thas.openEvnPrescrEditWindow('edit');}},
            {name:'action_view', text:BTN_GRIDVIEW, tooltip: BTN_GRIDVIEW_TIP, icon: 'img/icons/view16.png', handler: function() {thas.openEvnPrescrEditWindow('view');}},
            {name:'action_delete', text:BTN_GRIDDEL, tooltip: BTN_GRIDDEL_TIP, icon: 'img/icons/delete16.png', handler: this.cancelEvnPrescr},
            {name:'action_refresh', text:BTN_GRIDREFR, tooltip: BTN_GRIDREFR_TIP, icon: 'img/icons/refresh16.png', handler: this.loadDataViewStore},
            {
                name:'action_prevDay',
                text:lang['na_den_nazad'],
                tooltip: lang['perenesti_na_den_nazad'],
                iconCls: 'arrow-previous16',
                handler: function() {thas.moveInDay('prev');}
            },
            {
                name:'action_nextDay',
                text:lang['na_den_vpered'],
                tooltip: lang['perenesti_na_den_vpered'],
                iconCls: 'arrow-next16',
                handler: function() {thas.moveInDay('next');}
            },
            {
                name:'action_moveOnWeekBack',
                text:lang['nazad'],
                tooltip: lang['otobrazit_na_nedelyu_nazad'],
                iconCls: 'cal-back16',
                handler: function() {thas.moveOnWeek('back');}
            },
            {
                name:'action_moveOnWeekForward',
                text:lang['vpered'],
                tooltip: lang['otobrazit_na_nedelyu_vpered'],
                iconCls: 'cal-forward16',
                handler: function() {thas.moveOnWeek('forward');}
            },
            {
                name:'action_moveOnToday',
                text:lang['segodnya'],
                tooltip: lang['otobrazit_na_tekuschuyu_datu'],
                iconCls: 'datepicker-day16',
                handler: function() {thas.moveOnWeek('today');}
            },
            {
                name: 'action_openstandart',
                text: lang['federalnyiy_standart'],
                tooltip: lang['otkryit_formu_naznacheniy_po_federalnomu_standartu'],
				iconCls:'template16',
                handler: this.openStandartWindow
            },
            {
                name: 'action_savetemplate',
                text: lang['sohranit_kak_shablon'],
                tooltip: lang['sohranit_plan_naznacheniy_kak_shablon'],
				iconCls: 'save16',
                handler: this.saveEvnPrescrListAsXTemplate
            },
            {
                name: 'action_gettemplate',
                text: lang['zagruzit_plan'],
                tooltip: lang['zagruzit_plan_iz_shablona'],
				iconCls:'adddrugs-icon16',
                handler: this.getEvnPrescrList
            }
        ];
        for (var i=0; i<actions.length; i++) {
            this.ViewActions[actions[i]['name']] = new Ext.Action( {
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
        this.ViewToolbar = new Ext.Toolbar({
            id : this.id+'Toolbar',
            items: [
                this.ViewActions['action_add'],
                {xtype : "tbseparator"},
                this.ViewActions['action_refresh'],
                {xtype : "tbseparator"},
                this.ViewActions['action_openstandart'],
                {xtype : "tbseparator"},
                this.ViewActions['action_savetemplate'],
                this.ViewActions['action_gettemplate'],
                {xtype : "tbseparator"},
                this.ViewActions['action_moveOnWeekBack'],
                this.ViewActions['action_moveOnWeekForward'],
                this.ViewActions['action_moveOnToday']
            ]
        });
        this.ViewContextMenu = new Ext.menu.Menu();
        this.ViewContextMenu.add(this.ViewActions['action_exec']);
        this.ViewContextMenu.add(this.ViewActions['action_unexec']);
        this.ViewContextMenu.add(this.ViewActions['action_edit']);
        this.ViewContextMenu.add(this.ViewActions['action_view']);
        this.ViewContextMenu.add(this.ViewActions['action_delete']);
        this.ViewContextMenu.add('-');
        this.ViewContextMenu.add(this.ViewActions['action_prevDay']);
        this.ViewContextMenu.add(this.ViewActions['action_nextDay']);

        this.keys = [{
            alt: true,
            fn: function(inp, e) {
                switch ( e.getKey() ) {
                    case Ext.EventObject.J:
                        thas.hide();
                        break;
                }
            },
            key: [
                Ext.EventObject.J
            ],
            scope: thas,
            stopEvent: false
        }];

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnPrescrPlanForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{name: 'EvnPrescr_begDate'},
                {name: 'EvnPrescr_rid'},
                {name: 'EvnPrescr_pid'},
                {name: 'LpuSection_id'},
                {name: 'Diag_id'},
                {name: 'Diag_Code'},
                {name: 'Diag_Name'},
                {name: 'CureStandart_Count'},
                {name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Server_id'}
			]),
			url: '',
			items: [{
                name: 'EvnPrescr_rid', // Идентификатор текущего случая лечения (КВС)
                xtype: 'hidden'
            }, {
                name: 'EvnPrescr_pid', // Идентификатор текущего движения в стационаре
                xtype: 'hidden'
            }, {
				name: 'Person_id', // Идентификатор человека
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id', // Отделение
				xtype: 'hidden'
			}, {
                name: 'Diag_id', // Диагноз
                xtype: 'hidden'
            }, {
                name: 'Diag_Code', // Диагноз
                xtype: 'hidden'
            }, {
                name: 'Diag_Name', // Диагноз
                xtype: 'hidden'
            }, {
				allowBlank: false,
				disabled: true,
				fieldLabel: lang['data_postupleniya'],
				format: 'd.m.Y',
				name: 'EvnPrescr_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EPRPLF_PersonInformationFrame'
		});

        var tplAddClick = '<a class="buttons add16" onclick="Ext.getCmp(\'EvnPrescrPlanWindow\').addPrescrByType(\'{EvnPrescr_key}\',-1)"></a>';
        var tplDblClick = '';
        var tplAttrEvnPrescrCell = 'class="s-cell caption" id="EvnPrescrCell_{EvnPrescr_key}"';
        var tplAttrEvnPrescrDayCell = 'id="EvnPrescrDayCell_{index}"';
        var tplAttrEmptyDayCell = 'id="EmptyDayCell_{index}"';
        var tplEvnPrescrSetDate = '<tpl if="this.hasValue(EvnPrescr_setDate)">{EvnPrescr_setDate}</tpl>';
        this.EvnPrescrListDataView = new Ext.DataView({
            id: "EPRPLF_EvnPrescrListDataView",
            store: new Ext.data.Store({
                autoLoad:false,
                reader:new Ext.data.JsonReader({
                    id:'EvnPrescr_key'
                }, [
                    {name: 'EvnPrescr_key', mapping: 'EvnPrescr_key',key:true},
                    {name: 'EvnPrescr_id', mapping: 'EvnPrescr_id'},
                    {name: 'EvnPrescr_IsExec', mapping: 'EvnPrescr_IsExec'},
                    {name: 'EvnPrescr_IsDir', mapping: 'EvnPrescr_IsDir'},
                    {name: 'PrescriptionStatusType_id', mapping: 'PrescriptionStatusType_id'},
                    {name: 'EvnDirection_id', mapping: 'EvnDirection_id'},
                    {name: 'timetable', mapping: 'timetable'},
                    {name: 'timetable_id', mapping: 'timetable_id'},
                    {name: 'PrescriptionType_id', mapping: 'PrescriptionType_id'},
                    {name: 'PrescriptionType_Code', mapping: 'PrescriptionType_Code'},
                    {name: 'EvnPrescr_pid', mapping: 'EvnPrescr_pid'},
                    {name: 'EvnPrescr_IsCito', mapping: 'EvnPrescr_IsCito'},
                    {name: 'UslugaId_List', mapping:'UslugaId_List'},
                    {name: 'EvnPrescr_cnt', mapping:'EvnPrescr_cnt'},

                    // ниже данные для отображения
                    {name: 'EvnPrescrGroup_Title', mapping: 'EvnPrescrGroup_Title'},
                    {name: 'EvnPrescr_setDate', mapping: 'EvnPrescr_setDate'},
                    {name: 'IsCito_Code', mapping: 'IsCito_Code'},
                    {name: 'IsCito_Name', mapping: 'IsCito_Name'},
                    {name: 'days', mapping: 'days'},
                    /*1,2,10*/
                    {name: 'EvnPrescr_DateInterval', mapping: 'EvnPrescr_DateInterval'},
                    /*1*/
                    {name: 'PrescriptionRegimeType_Image', mapping:'PrescriptionRegimeType_Image'},
                    {name: 'PrescriptionRegimeType_Name', mapping:'PrescriptionRegimeType_Name'},
                    {name: 'EvnPrescr_Descr', mapping:'EvnPrescr_Descr'},
                    /*2*/
                    {name: 'PrescriptionDietType_Image', mapping:'PrescriptionDietType_Image'},
                    {name: 'PrescriptionDietType_Name', mapping:'PrescriptionDietType_Name'},
                    /*10*/
                    {name: 'ObservTimeType_idList', mapping:'ObservTimeType_idList'},
                    {name: 'ObservParamType_Names', mapping:'ObservParamType_Names'},
                    {name: 'ObservTimeType_Names', mapping:'ObservTimeType_Names'},
                    /*5*/
                    {name: 'Drug_Info', mapping:'Drug_Info'},
                    {name: 'DrugForm_Name', mapping:'DrugForm_Name'},
                    {name: 'DrugForm_Nick', mapping:'DrugForm_Nick'},
                    {name: 'Okei_NationSymbol', mapping:'Okei_NationSymbol'},
                    {name: 'EvnPrescrTreatDrug_DoseDay', mapping:'EvnPrescrTreatDrug_DoseDay'},
                    {name: 'EvnPrescrTreatDrug_DoseCource', mapping:'EvnPrescrTreatDrug_DoseCource'},
                    {name: 'PrescriptionIntroType_Name', mapping:'PrescriptionIntroType_Name'},
                    {name: 'PerformanceType_Name', mapping:'PerformanceType_Name'},
                    {name: 'Drug_Name', mapping:'Drug_Name'},
                    {name: 'DrugTorg_Name', mapping:'DrugTorg_Name'},
                    {name: 'EvnPrescrTreatDrug_Kolvo', mapping:'EvnPrescrTreatDrug_Kolvo'},
                    {name: 'EvnPrescrTreatDrug_KolvoEd', mapping:'EvnPrescrTreatDrug_KolvoEd'},
                    /*5,6*/
                    {name: 'CountInDay', mapping: 'CountInDay'},
                    {name: 'CourseDuration', mapping: 'CourseDuration'},
                    {name: 'ContReception', mapping: 'ContReception'},
                    {name: 'Interval', mapping: 'Interval'},
                    {name: 'DurationTypeP_Nick', mapping: 'DurationTypeP_Nick'},
                    {name: 'DurationTypeN_Nick', mapping: 'DurationTypeN_Nick'},
                    {name: 'DurationTypeI_Nick', mapping: 'DurationTypeI_Nick'},
                    /*6,7,11,12,13*/
                    {name: 'TableUsluga_id', mapping:'TableUsluga_id'},
                    {name: 'Usluga_List', mapping:'Usluga_List'},
                    {name: 'EvnDirection_Num', mapping: 'EvnDirection_Num'},
                    {name: 'RecTo', mapping: 'RecTo'},
                    {name: 'RecDate', mapping: 'RecDate'}
                ]),
                url:'/?c=EvnPrescr&m=loadEvnPrescrList'
            }),
            //style: 'overflow-y: visible;',
            itemSelector: 'tr',
            //singleSelect: true,
            autoHeight: true,
            tpl : new Ext.XTemplate(
                '<table class="Calendar">',
                '<thead style="position:fixed"><tr><td class="header" style="background:white;width:390px;height:48px">Назначения</td></tr></thead></table>',
                '<table id="EPRPLF_EvnPrescrListDataViewTable" class="Calendar">',
				'<thead><tr><td class="header" style="background:white;width:390px;height:48px">Назначения</td></tr></thead>',
				'<tbody>',
                '<tpl for="."><tr id="EPRPLF_row-list_{EvnPrescr_key}">',
                '<tpl if="this.isGroupRow(EvnPrescr_key)"><td class="caption group"><span>{EvnPrescrGroup_Title}</span> </td></tpl>',
                '<tpl if="this.isEmptyRow(EvnPrescr_key)"><td><span>'+tplAddClick+'</span></td></tpl>',
                '<tpl if="this.isEvnPrescrRow(EvnPrescr_id)">',
                /*1*/
                '<tpl if="PrescriptionType_id==1">',
                '<td '+tplAttrEvnPrescrCell+'><b>{EvnPrescr_DateInterval}</b>&nbsp;{PrescriptionRegimeType_Name}',
                '<tpl if="this.hasValue(EvnPrescr_Descr)"><br />{EvnPrescr_Descr}</tpl></td>',
                '</tpl>',
                /*2*/
                '<tpl if="PrescriptionType_id==2">',
                '<td '+tplAttrEvnPrescrCell+'><b>{EvnPrescr_DateInterval}</b>&nbsp;{PrescriptionDietType_Name}',
                '<tpl if="this.hasValue(EvnPrescr_Descr)"><br />{EvnPrescr_Descr}</tpl></td>',
                '</tpl>',
                /*10
                * */
                '<tpl if="PrescriptionType_id==10">',
                '<td '+tplAttrEvnPrescrCell+'><b>{EvnPrescr_DateInterval}</b>&nbsp;{ObservTimeType_Names}&nbsp;Параметры наблюдения: {ObservParamType_Names}',
                '<tpl if="this.hasValue(EvnPrescr_Descr)"><br />{EvnPrescr_Descr}</tpl></td>',
                '</tpl>',
                /*5*/
                '<tpl if="PrescriptionType_id==5">',
                '<td '+tplAttrEvnPrescrCell+'><b>{Drug_Info}</b>&nbsp;'+tplEvnPrescrSetDate,
                '<tpl if="EvnPrescrTreatDrug_KolvoEd"> По {EvnPrescrTreatDrug_KolvoEd}</tpl>',
                '<tpl if="DrugForm_Nick"> {DrugForm_Nick}</tpl>',
                '<tpl if="!DrugForm_Nick"> ед.дозировки</tpl>',
                '<tpl if="EvnPrescrTreatDrug_Kolvo&&!EvnPrescrTreatDrug_KolvoEd"> {EvnPrescrTreatDrug_Kolvo}</tpl>',
                '<tpl if="Okei_NationSymbol&&!EvnPrescrTreatDrug_KolvoEd"> {Okei_NationSymbol}</tpl>',
                '<tpl if="CountInDay">, {CountInDay} <tpl if="CountInDay==2||CountInDay==3||CountInDay==4">раза</tpl><tpl if="CountInDay!=2&&CountInDay!=3&&CountInDay!=4">раз</tpl> в сутки</tpl>',
                '<tpl if="ContReception">, принимать {ContReception} {DurationTypeN_Nick}</tpl>',
                '<tpl if="Interval">, перерыв {Interval} {DurationTypeI_Nick}</tpl>',
                '<tpl if="CourseDuration&&CourseDuration!=ContReception">, в течение {CourseDuration} {DurationTypeP_Nick}</tpl>.',
                '<tpl if="EvnPrescrTreatDrug_Kolvo&&Okei_NationSymbol&&EvnPrescrTreatDrug_DoseDay&&EvnPrescrTreatDrug_DoseCource"><br />Доза разовая – {EvnPrescrTreatDrug_Kolvo} {Okei_NationSymbol}; дневная - {EvnPrescrTreatDrug_DoseDay};  курсовая – {EvnPrescrTreatDrug_DoseCource}.</tpl>',
                '<tpl if="PrescriptionIntroType_Name"><br />Метод введения: {PrescriptionIntroType_Name}</tpl>',
                '<tpl if="PerformanceType_Name"><br />Исполнение: {PerformanceType_Name}</tpl>',
                '<tpl if="IsCito_Code==1">&nbsp;<span style="color: red">Cito!</span></tpl>',
                '</td>',
                '</tpl>',
                /*6*/
                '<tpl if="PrescriptionType_id==6">',
                '<td '+tplAttrEvnPrescrCell+'><b>{Usluga_List}</b>&nbsp;'+tplEvnPrescrSetDate,
                '<tpl if="CountInDay">, {CountInDay} <tpl if="CountInDay==2||CountInDay==3||CountInDay==4">раза</tpl><tpl if="CountInDay!=2&&CountInDay!=3&&CountInDay!=4">раз</tpl> в сутки</tpl>',
                '<tpl if="ContReception">, повторять непрерывно {ContReception} {DurationTypeN_Nick}</tpl>',
                '<tpl if="Interval">, перерыв {Interval} {DurationTypeI_Nick}</tpl>',
                '<tpl if="CourseDuration&&CourseDuration!=ContReception">, всего {CourseDuration} {DurationTypeP_Nick}</tpl>.',
                '<tpl if="IsCito_Code==1">&nbsp;<span style="color: red">Cito!</span></tpl>',
                '<tpl if="EvnPrescr_IsDir==2"><p><span id="EvnPrescrList_{EvnPrescr_key}_viewdir" class="link" title="Просмотр направления">Записан</span> {RecTo} {RecDate} {EvnDirection_Num}</p></tpl></td>',
                '</tpl>',
                /*7*/
                '<tpl if="PrescriptionType_id==7">',
                '<td '+tplAttrEvnPrescrCell+'><b>{Usluga_List}</b>&nbsp;'+tplEvnPrescrSetDate+'<tpl if="IsCito_Code==1">&nbsp;<span style="color: red">Cito!</span></tpl>',
                '<tpl if="EvnPrescr_IsDir==2"><p><span id="EvnPrescrList_{EvnPrescr_key}_viewdir" class="link" title="Просмотр направления">Записан</span> {RecTo} {RecDate} {EvnDirection_Num}</p></tpl></td>',
                '</tpl>',
                /*11*/
                '<tpl if="PrescriptionType_id==11">',
                '<td '+tplAttrEvnPrescrCell+'><b>{Usluga_List}</b>&nbsp;'+tplEvnPrescrSetDate+'<tpl if="IsCito_Code==1">&nbsp;<span style="color: red">Cito!</span></tpl>',
                '<tpl if="EvnPrescr_IsDir==2"><p><span id="EvnPrescrList_{EvnPrescr_key}_viewdir" class="link" title="Просмотр направления">Записан</span> {RecTo} {RecDate} {EvnDirection_Num}</p></tpl></td>',
                '</tpl>',
                /*12*/
                '<tpl if="PrescriptionType_id==12">',
                '<td '+tplAttrEvnPrescrCell+'><b>{Usluga_List}</b>&nbsp;'+tplEvnPrescrSetDate+'<tpl if="IsCito_Code==1">&nbsp;<span style="color: red">Cito!</span></tpl>',
                '<tpl if="EvnPrescr_IsDir==2"><p><span id="EvnPrescrList_{EvnPrescr_key}_viewdir" class="link" title="Просмотр направления">Записан</span> {RecTo} {RecDate} {EvnDirection_Num}</p></tpl></td>',
                '</tpl>',
                /*13*/
                '<tpl if="PrescriptionType_id==13">',
                '<td '+tplAttrEvnPrescrCell+'><b>{Usluga_List}</b>&nbsp;'+tplEvnPrescrSetDate+'<tpl if="IsCito_Code==1">&nbsp;<span style="color: red">Cito!</span></tpl>',
                '<tpl if="EvnPrescr_IsDir==2"><p><span id="EvnPrescrList_{EvnPrescr_key}_viewdir" class="link" title="Просмотр направления">Записан</span> {RecTo} {RecDate} {EvnDirection_Num}</p></tpl></td>',
                '</tpl>',
                '</tpl>',
                '</tr></tpl>',
                '</tbody></table>', {
                    hasValue: function(value){
                        return !Ext.isEmpty(value);
                    },
                    isEvnPrescrRow: function(id){
                        return id > 0;
                    },
                    isGroupRow: function(key){
                        return key.indexOf('EvnPrescrGroup') >= 0;
                    },
                    isEmptyRow: function(key){
                        return key.indexOf('EmptyRow') >= 0;
                    }
                }
            )
        });

        this.CalendarDataView = new Ext.Panel({
            id: "EPRPLF_CalendarDataView",
            autoHeight: true,
            autoScroll: true,
            border: false,
			listeners:
			{
				resize: function (p,nW, nH, oW, oH)
				{
					//log(this)
				}
			},
            region: 'center',
            clsCellHeader: 'header',
            clsCellSummary: 'summary',
            clsCellDayOff: 'day-off',
            clsCellDayCur: 'day-cur',
            clsCellGraf: 'graf',
            dayLimit: 15,
            tpl : new Ext.XTemplate(
				 '<table id="EPRPLF_CalendarTable" class="Calendar" style="width:inherit">',
                '<thead style="position:fixed;width:inherit;background:white">',
                '<tr class="header-months" style="width: inherit;">',
                '<tpl for="months"><td colspan="{Month_CntDays}" class="{cls}">{Month_Text}</td></tpl>',
                '<td rowspan="2" class="header summary">Всего</td></tr>',
                '<tr class="header-days">',
                '<tpl for="days"><td class="{cls}">{Day_Text}</td></tpl></tr>',
                '</thead></table>',
                '<table id="EPRPLF_CalendarTable" class="Calendar" style="width:inherit">',
                '<thead style="width:inherit;background:white">',
                '<tr class="header-months" style="width: inherit;">',
                '<tpl for="months"><td colspan="{Month_CntDays}" class="{cls}">{Month_Text}</td></tpl>',
                '<td rowspan="2" class="header summary">Всего</td></tr>',
                '<tr class="header-days">',
                '<tpl for="days"><td class="{cls}">{Day_Text}</td></tpl></tr>',
                '</thead>',
                '<tbody>',
                '<tpl for="rows">',
                /*EvnPrescrGroup*/
                '<tpl if="this.isGroupRow(EvnPrescr_key)">',
                '<tr id="EPRPLF_row_{EvnPrescr_key}"><tpl for="cells"><td colspan="{colspan}" class="{cls}">{text}</td></tpl></tr>',
                '</tpl>',
                /*EmptyRow*/
                '<tpl if="this.isEmptyRow(EvnPrescr_key)">',
                '<tr id="EPRPLF_row_{EvnPrescr_key}"><tpl for="cells"><td class="{cls}" '+tplDblClick+' '+tplAttrEmptyDayCell+'>{text}</td></tpl></tr>',
                '</tpl>',
                /*EvnPrescrRow*/
                '<tpl if="this.isEvnPrescrRow(EvnPrescr_id)">',
                '<tr id="EPRPLF_row_{EvnPrescr_key}"><tpl for="cells"><td class="{cls}" '+tplDblClick+' '+tplAttrEvnPrescrDayCell+'>{text}</td></tpl></tr>',
                '</tpl>',
                '</tpl>',
                '</tbody>',
                '</table>', {
                    hasValue: function(value){
                        return !Ext.isEmpty(value);
                    },
                    isAllowMove: function(cell){
                        return !cell;
                    },
                    isEvnPrescrRow: function(id){
                        return id > 0;
                    },
                    isGroupRow: function(key){
                        return key.indexOf('EvnPrescrGroup') >= 0;
                    },
                    isEmptyRow: function(key){
                        return key.indexOf('EmptyRow') >= 0;
                    }
                }
            ),
            tplData: {
                date_list: [],
                months: [],
                days: [],
                rows: []
            },
            params: null,
            modeView: 'today',
            lastBegDate: null,
            applyCommonData: function(data) {
                this.params = {
                    days_diff: data.days.days_diff,
                    cur_date: Date.parseDate(data.days.cur_date, 'd.m.Y'),
                    EvnPS_setDate: Date.parseDate(data.days.EvnPS_setDate, 'd.m.Y'),
                    Evn_setDate: Date.parseDate(data.days.Evn_setDate, 'd.m.Y')
                };
                var disableBack = false;
                var disableForward = false;
                var disableToday = false;
                switch (this.modeView) {
                    case 'today':
                        disableToday = true;
                        if (this.params.days_diff > 8) {
                            //начиная с девятого дня текущая дата всегда попадает в центр
                            this.params.beg_date = this.params.cur_date.add(Date.DAY, -7);
                            disableBack = this.params.days_diff < 14;
                        } else {
                            //в первые восемь дней лечения в отделении календарь начинается с даты начала текущего движения
                            this.params.beg_date = this.params.Evn_setDate;
                            disableBack = true;
                        }
                        break;
                    case 'back':
                        this.params.beg_date = this.lastBegDate.add(Date.DAY, -7);
                        if (this.params.beg_date.format('Y-m-d') < this.params.Evn_setDate.format('Y-m-d')) {
                            this.params.beg_date = this.params.Evn_setDate;
                            disableBack = true;
                        }
                        break;
                    case 'forward':
                        this.params.beg_date = this.lastBegDate.add(Date.DAY, 7);
                        break;
                }
                thas.ViewActions['action_moveOnWeekBack'].setDisabled(disableBack);
                thas.ViewActions['action_moveOnWeekForward'].setDisabled(disableForward);
                thas.ViewActions['action_moveOnToday'].setDisabled(disableToday);
                this.lastBegDate = this.params.beg_date;
                var day_cnt = 0, ms = -1, day = this.params.beg_date;
                var day_week, cell_cls;
                while (day_cnt < this.dayLimit) {
                    cell_cls = this.clsCellHeader+ ' day';
                    day_week = day.getDay();
                    if (0==day_week||6==day_week) {
                        cell_cls = cell_cls +' '+ this.clsCellDayOff;
                    }
                    if (this.params.cur_date.getTime() == day.getTime()) {
                        cell_cls = cell_cls +' '+ this.clsCellDayCur;
                    }
                    this.tplData.date_list.push(day.format('d.m.Y'));
                    this.tplData.days.push({
                        cls: cell_cls,
                        Day_Date: day,
                        Day_Month: day.getMonth(),
                        Day_Text: day.format('d')
                    });
                    if (ms > -1 && this.tplData.months[ms].Month_Index == day.getMonth()) {
                        this.tplData.months[ms].Month_CntDays++;
                    } else {
                        this.tplData.months.push({
                            cls: this.clsCellHeader+ ' month',
                            Month_CntDays: 1,
                            Month_Index: day.getMonth(),
                            Month_Text: Date.monthNames[day.getMonth()].toLowerCase()
                        });
                        ms++;
                    }
                    day_cnt++;
                    day = day.add(Date.DAY, 1);
					//log(this.tplData);
					
                }
				//log(["date",this.tplData]);
            },
            applyEvnPrescrGroupData: function(data) {
                this.tplData.rows.push({
                    EvnPrescr_key: data.EvnPrescr_key,
                    EvnPrescr_id: 0,
                    cells: [{
                        colspan: this.dayLimit+1,
                        cls: 'group',
                        text: '&nbsp;'
                    }]
                });
            },
            applyEmptyRowData: function(data) {
                var row = {
                    EvnPrescr_key: data.EvnPrescr_key,
                    EvnPrescr_id: 0,
                    PrescriptionType_id: data.PrescriptionType_id,
                    cells: []
                };
                var day_week, cell_cls;
                for (var i=0; i<(this.dayLimit+1); i++) {
                    if (this.tplData.date_list[i]) {
                        cell_cls = this.clsCellGraf+ ' s-cell';
                        day_week = this.tplData.days[i].Day_Date.getDay();
                        if (0==day_week||6==day_week) {
                            cell_cls = cell_cls +' '+ this.clsCellDayOff;
                        }
                        if (this.params.cur_date.getTime() == this.tplData.days[i].Day_Date.getTime()) {
                            cell_cls = cell_cls +' '+ this.clsCellDayCur;
                        }
                        row.cells.push({
                            index: i +'_'+ data.EvnPrescr_key,
                            cls: cell_cls,
                            text: '&nbsp;'
                        });
                    } else {
                        row.cells.push({
                            index: this.dayLimit,
                            cls: this.clsCellSummary,
                            text: '&nbsp;'
                        });
                    }

                }
                this.tplData.rows.push(row);
            },
            applyEvnPrescrData: function(data) {
                var row = {
                    EvnPrescr_key: data.EvnPrescr_key,
                    EvnPrescr_id: data.EvnPrescr_id,
                    PrescriptionType_id: data.PrescriptionType_id,
                    cells: []
                };
                var day_key, day_week, cell_cls, text_image, text_title;
                for (var i=0; i<(this.dayLimit); i++) {
                    day_key = data.EvnPrescr_id +'-'+ this.tplData.date_list[i];
                    cell_cls = this.clsCellGraf+ ' s-cell';
                    day_week = this.tplData.days[i].Day_Date.getDay();
                    if (0==day_week||6==day_week) {
                        cell_cls = cell_cls +' '+ this.clsCellDayOff;
                    }
                    if (this.params.cur_date.getTime() == this.tplData.days[i].Day_Date.getTime()) {
                        cell_cls = cell_cls +' '+ this.clsCellDayCur;
                    }
                    if (data.days[day_key]) {
                        switch (true) {
                            case (data.days[day_key].Day_IsExec):
                                text_image = 'tick16.png';
                                //todo pmUserExec_Name
                                //text_title = 'Выполнено пользователем ';
                                text_title = lang['vyipolneno'];
                                break;
                            case (
                                !data.days[day_key].Day_IsExec
                                    && !data.PrescriptionType_id.toString().inlist(['1','2'])
                                    && this.params.cur_date.getTime() > this.tplData.days[i].Day_Date.getTime()
                                ):
                                text_image = 'warning16.png';
                                text_title = lang['prosrochennoe_neispolnennoe_naznachenie'];
                                break;
                            default:
                                text_image = 'settings16.png';
                                text_title = lang['rabochee'];
                                break;
                        }
                        var dayData = {
                            index: i +'_'+ data.EvnPrescr_key,
                            set_date: this.tplData.date_list[i],
                            cls: cell_cls,
                            Day_IsExec: data.days[day_key].Day_IsExec,
                            Day_IsSign: data.days[day_key].Day_IsSign,
                            text: '<img src="/img/icons/'+ text_image +'" title="'+ text_title +'" />'
                        };
                        if (data.days[day_key].EvnPrescrTreatTimetable_id) {
                            dayData.EvnPrescrTreatTimetable_id = data.days[day_key].EvnPrescrTreatTimetable_id
                        }
                        if (data.days[day_key].EvnPrescrProcTimetable_id) {
                            dayData.EvnPrescrProcTimetable_id = data.days[day_key].EvnPrescrProcTimetable_id
                        }
                        if (data.days[day_key].EvnPrescrObserv_id) {
                            dayData.EvnPrescrObserv_id = data.days[day_key].EvnPrescrObserv_id
                        }
                        if (data.days[day_key].EvnPrescrRegime_id) {
                            dayData.EvnPrescrRegime_id = data.days[day_key].EvnPrescrRegime_id
                        }
                        if (data.days[day_key].EvnPrescrDiet_id) {
                            dayData.EvnPrescrDiet_id = data.days[day_key].EvnPrescrDiet_id
                        }
                        row.cells.push(dayData);
                    } else {
                        row.cells.push({
                            index: i +'_'+ data.EvnPrescr_key,
                            set_date: this.tplData.date_list[i],
                            cls: cell_cls,
                            text: '&nbsp;'
                        });
                    }
                }
                row.cells.push({
                    index: this.dayLimit,
                    set_date: null,
                    cls: this.clsCellSummary,
                    text: data.EvnPrescr_cnt || '&nbsp;'
                });
                this.tplData.rows.push(row);
            },
            reset: function() {
                this.modeView = 'today';
                this.tplData = {
                    date_list: [],
                    months: [],
                    days: [],
                    rows: []
                };
                this.params = null;
                var tpl = new Ext.XTemplate('');
                tpl.overwrite(this.body, {});
            },
            overwriteTpl: function() {
                this.tpl.overwrite(this.body, this.tplData);
                var node_list = Ext.query("*[class*=s-cell]", Ext.getDom('EPRPLF_MainViewPanel'));
                var i, el;
                for(i=0; i < node_list.length; i++)
                {
                    el = new Ext.Element(node_list[i]);
                    el.on('click', thas.onCellClick, thas, {element: el});
                }
                var records = [];
                thas.EvnPrescrListDataView.getStore().each(function(rec){
                    records.push(rec);
                }, thas);
                //делаем перерасчет высоты строк для календаря
                var rowList, row;
                for (i=0; i<records.length; i++) {
                    if (records[i].id == 'CommonData') {
                        continue;
                    }
                    rowList = Ext.get('EPRPLF_row-list_'+ records[i].id);
                    row = Ext.get('EPRPLF_row_'+ records[i].id);
                    if (rowList && row) {
                        row.setHeight(rowList.getHeight());
                    }
                }
				
                this.syncSize();
                this.doLayout();
                thas.EvnPrescrListDataView.syncSize();
                thas.MainViewPanel.syncSize();
                thas.MainViewPanel.doLayout();
            }
        });

        this.MainViewPanel = new Ext.Panel({
            region: 'center',
            autoScroll: true,
            id: 'EPRPLF_MainViewPanel',
            layout:'border',
            border: false,
            frame: false,
            tbar: this.ViewToolbar,
            items: [{
                items: [thas.EvnPrescrListDataView],
                region: 'west',
                width: 400,
                //autoScroll: true,
                autoHeight: true,
                border: false,
                frame: false,
                xtype: 'panel'
            }, thas.CalendarDataView
            ]
        });

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					thas.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCLOSE
			}],
			items: [{
				autoHeight: true,
				region: 'north',
				xtype: 'panel',
				items: [
					this.PersonInfo,
					this.FormPanel
				]
            },
                this.MainViewPanel
            ],
			layout: 'border'
		});

		sw.Promed.swEvnPrescrPlanWindow.superclass.initComponent.apply(this, arguments);
	}
});